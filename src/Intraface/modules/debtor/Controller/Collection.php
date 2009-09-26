<?php
class Intraface_modules_debtor_Controller_Collection extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getDebtor()
    {
        return $debtor = Debtor::factory($this->getKernel(), intval($_GET["id"]), $_GET["type"]);
    }

    function getPosts()
    {
        return $this->getDebtor()->getList();
    }

    function renderExcel()
    {
        $translation = $kernel->getTranslation('debtor');
        $debtor_module = $kernel->module('debtor');

        if (empty($_GET['id'])) $_GET['id'] = '';
        if (empty($_GET['type'])) $_GET['type'] = '';

        $debtor = Debtor::factory($kernel, intval($_GET["id"]), $_GET["type"]);
        $debtor->getDbQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");

        $posts = $debtor->getList();

        // spreadsheet
        $workbook = new Spreadsheet_Excel_Writer();

        $workbook->send('debtor.xls');

        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_bold->setSize(8);

        $format_italic = $workbook->addFormat();
        $format_italic->setItalic();
        $format_italic->setSize(8);

        $format = $workbook->addFormat();
        $format->setSize(8);

        // Creating a worksheet
        $worksheet = $workbook->addWorksheet(ucfirst(__('title')));

        $i = 1;
        $worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);
        $i++;

        $status_types = array(
            -3 => 'Afskrevet',
            -2 => '�bne',
            -1 => 'Alle',
            0 => 'Oprettet',
            1 => 'Sendt',
            2 => 'Afsluttet',
            3 => 'Annulleret');

        $worksheet->write($i, 0, 'Status', $format_italic);
        $worksheet->write($i, 1, $status_types[$debtor->getDbQuery()->getFilter('status')], $format_italic);
        $i++;

        $worksheet->write($i, 0, 'S�getekst', $format_italic);
        $worksheet->write($i, 1, $debtor->getDbQuery()->getFilter('text'), $format_italic);
        $i++;

        if ($debtor->getDbQuery()->checkFilter('product_id')) {
            $product = new Product($kernel, $debtor->getDbQuery()->getFilter('product_id'));

            $worksheet->write($i, 0, 'Produkt', $format_italic);
            $worksheet->write($i, 1, $product->get('name'), $format_italic);
            $i++;
        }

        if ($debtor->getDbQuery()->checkFilter('contact_id')) {
            $contact = new Contact($kernel, $debtor->getDbQuery()->getFilter('contact_id'));

            $worksheet->write($i, 0, 'Kontakt', $format_italic);
            $worksheet->write($i, 1, $contact->address->get('name'), $format_italic);
            $i++;
        }

        $worksheet->write($i, 0, "Antal i s�gningen", $format_italic);
        $worksheet->write($i, 1, count($posts), $format_italic);
        $i++;

        $i++;
        $worksheet->write($i, 0, 'Nummer', $format_bold);
        $worksheet->write($i, 1, 'Kontakt nummer', $format_bold);
        $worksheet->write($i, 2, 'Kontakt navn', $format_bold);
        $worksheet->write($i, 3, 'Beskrivelse', $format_bold);
        $worksheet->write($i, 4, 'Bel�b', $format_bold);
        $worksheet->write($i, 5, 'Oprettet', $format_bold);
        $worksheet->write($i, 6, 'Sendt', $format_bold);
        //$worksheet->write($i, 7, __("due_date"), $format_bold);
        $c = 8;
        if ($debtor->get('type') == 'invoice') {
            $worksheet->write($i, $c, 'Forfaldsbel�b', $format_bold);
            $c++;
        }
        $worksheet->write($i, $c, 'Kontaktn�gleord', $format_bold);
        $c++;

        if (!empty($product) && is_object($product) && get_class($product) == 'product') {
            $worksheet->write($i, $c, 'Antal valgte produkt', $format_bold);
            $c++;
        }

        // HACK unsetting debtor which is actually ok to avoid memory problems //
        $type = $debtor->get('type');
        unset($debtor);
        // HACK end //

        $i++;

        $due_total = 0;
        $sent_total = 0;
        $total = 0;

        if (count($posts) > 0) {
            for ($j = 0, $max = count($posts); $j < $max; $j++) {

                if ($posts[$j]["due_date"] < date("Y-m-d") && ($posts[$j]["status"] == "created" OR $posts[$j]["status"] == "sent")) {
                    $due_total += $posts[$i]["total"];
                }
                if ($posts[$j]["status"] == "sent") {
                    $sent_total += $posts[$j]["total"];
                }
                $total += $posts[$j]["total"];

                $worksheet->write($i, 0, $posts[$j]["number"]);
                $worksheet->write($i, 1, $posts[$j]['contact']['number']);
                $worksheet->write($i, 2, $posts[$j]["name"]);
                $worksheet->write($i, 3, $posts[$j]["description"]);
                $worksheet->writeNumber($i, 4, $posts[$j]["total"]);
                $worksheet->write($i, 5, $posts[$j]["dk_this_date"]);

                if ($posts[$j]["status"] != "created") {
                    $worksheet->write($i, 6, $posts[$j]["dk_date_sent"]);
                } else {
                    $worksheet->write($i, 6, "Nej");
                }

                if ($posts[$j]["status"] == "executed" || $posts[$j]["status"] == "canceled") {
                    $worksheet->write($i, 7, __($posts[$j]["status"], 'debtor'));
                } else {
                    $worksheet->write($i, 7, $posts[$j]["dk_due_date"]);
                }
                $c = 8;
                if ($type == 'invoice') {
                    $worksheet->write($i, $c, $posts[$j]['arrears']);
                    $c++;
                }

                $keywords = array();
                $contact = new Contact($kernel, $posts[$j]['contact']['id']);
                $appender = $contact->getKeywordAppender();
                $keyword_ids = $appender->getConnectedKeywords();
                if (count($keyword_ids) > 0) {
                    foreach ($keyword_ids AS $keyword_id) {
                        $keyword = new Keyword($contact, $keyword_id);
                        $keywords[] = $keyword->getKeyword();
                    }
                    $worksheet->write($i, $c, implode(', ', $keywords));
                    $c++;
                }

                if (!empty($product) && is_object($product) && get_class($product) == 'product') {
                    $quantity_product = 0;
                    if (count($posts[$j]['items']) > 0) {
                        foreach ($posts[$j]['items'] AS $item) {
                            if ($item['product_id'] == $product->get('id')) {
                                $quantity_product += $item['quantity'];
                            }
                        }
                    }
                    $worksheet->write($i, $c, $quantity_product);
                    $c++;
                }

                $i++;

            }
        }


        $i++;
        $i++;

        $worksheet->write($i, 0, 'Forfaldne', $format_italic);
        $worksheet->write($i, 1, number_format($due_total, 2, ",","."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Udest�ende (sendt):', $format_italic);
        $worksheet->write($i, 1, number_format($sent_total, 2, ",","."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Total:', $format_italic);
        $worksheet->write($i, 1, number_format($total, 2, ",","."), $format_italic);
        $i++;


        $worksheet->hideGridLines();

        $workbook->close();

        exit;
    }

    function renderHtml()
    {
        $translation = $this->getKernel()->getTranslation('debtor');

        $mDebtor = $this->getKernel()->module('debtor');
        $contact_module = $this->getKernel()->useModule('contact');
        $product_module = $this->getKernel()->useModule('product');

        if (empty($_GET['id'])) $_GET['id'] = '';
        if (empty($_GET['type'])) $_GET['type'] = '';
        if (empty($_GET["contact_id"])) $_GET['contact_id'] = '';
        if (empty($_GET["status"])) $_GET['status'] = '';

        $debtor = Debtor::factory($this->getKernel(), intval($_GET["id"]), $_GET["type"]);

        if (isset($_GET["action"]) && $_GET["action"] == "delete") {
            // $debtor = new CreditNote($this->getKernel(), (int)$_GET["delete"]);
            $debtor->delete();
        }

        if (isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0) {
            $debtor->getDBQuery()->setFilter("contact_id", $_GET["contact_id"]);
        }

        if (isset($_GET["product_id"]) && intval($_GET["product_id"]) != 0) {
            $debtor->getDBQuery()->setFilter("product_id", $_GET["product_id"]);
            if (isset($_GET['product_variation_id'])) {
                $debtor->getDBQuery()->setFilter("product_variation_id", $_GET["product_variation_id"]);
            }
        }

        // s�gning
            // if (isset($_POST['submit'])
            if (isset($_GET["text"]) && $_GET["text"] != "") {
                $debtor->getDBQuery()->setFilter("text", $_GET["text"]);
            }

            if (isset($_GET["date_field"]) && $_GET["date_field"] != "") {
                $debtor->getDBQuery()->setFilter("date_field", $_GET["date_field"]);
            }

            if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
                $debtor->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
            }

            if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
                $debtor->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
            }

            if ($debtor->getDBQuery()->checkFilter("contact_id")) {
                $debtor->getDBQuery()->setFilter("status", "-1");
            } elseif (isset($_GET["status"]) && $_GET['status'] != '') {
                $debtor->getDBQuery()->setFilter("status", $_GET["status"]);
            } else {
                $debtor->getDBQuery()->setFilter("status", "-2");
            }

            if (!empty($_GET['not_stated']) AND $_GET['not_stated'] == 'true') {
                $debtor->getDBQuery()->setFilter("not_stated", true);
            }

        // er der ikke noget galt herunder (LO) - brude det ikke v�re order der bliver sat?
        if (isset($_GET['sorting']) && $_GET['sorting'] != 0) {
            $debtor->getDBQuery()->setFilter("sorting", $_GET['sorting']);
        }

        $debtor->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $debtor->getDBQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");
        $debtor->getDBQuery()->setExtraUri('&amp;type='.$debtor->get("type"));

        $posts = $debtor->getList();

        if (intval($debtor->getDBQuery()->getFilter('product_id')) != 0) {
            $product = new Product($this->getKernel(), $debtor->getDBQuery()->getFilter('product_id'));
            if (intval($debtor->getDBQuery()->getFilter('product_variation_id')) != 0) {
                $variation = $product->getVariation($debtor->getDBQuery()->getFilter('product_variation_id'));
            }
        }

        if (intval($debtor->getDBQuery()->getFilter('contact_id')) != 0) {
            $contact = new Contact($this->getKernel(), $debtor->getDBQuery()->getFilter('contact_id'));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/collection.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }

    function t($phrase)
    {
         return $phrase;
    }
}