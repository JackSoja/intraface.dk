<?php
class Intraface_modules_debtor_Controller_Show extends k_Component
{
    public $email_send_with_success;
    public $onlinepayment_show_cancel_option;
    public $onlinepayment;
    protected $debtor;
    protected $translation;
    protected $template;
    protected $mdb2;
    protected $doctrine;

    function __construct(k_TemplateFactory $template, Translation2 $translation, MDB2_Driver_Common $mdb2, Doctrine_Connection_Common $doctrine)
    {
        $this->translation = $translation;
        $this->template = $template;
        $this->mdb2 = $mdb2;
        $this->doctrine = $doctrine;
    }

    function dispatch()
    {
        if ($this->getDebtor()->getId() == 0) {
            throw new k_PageNotFound();
        }
        if ($this->context->getType() != $this->getType()) {
            return new k_SeeOther($this->url('../../../' . $this->getType() . '/list/' . $this->getDebtor()->getId()));
        }

        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'contact') {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        } elseif ($name == 'selectproduct') {
            return 'Intraface_modules_product_Controller_Selectproduct';
        } elseif ($name == 'selectmultipleproductwithquantity') {
            return 'Intraface_modules_product_Controller_Selectproduct';
        } elseif ($name == 'selectproductvariation') {
            return 'Intraface_modules_product_Controller_Selectproductvariation';
        } elseif ($name == 'payment') {
            return 'Intraface_modules_debtor_Controller_Payments';
        } elseif ($name == 'depreciation') {
            return 'Intraface_modules_debtor_Controller_Depreciations';
        } elseif ($name == 'state') {
            if ($this->getType() == 'credit_note') {
                return 'Intraface_modules_accounting_Controller_State_Creditnote';
            } elseif ($this->getType() == 'invoice') {
                return 'Intraface_modules_accounting_Controller_State_Invoice';
            } else {
                throw new Exception('Cannot state type ' . $this->getType());
            }
        } elseif ($name == 'item') {
            return 'Intraface_modules_debtor_Controller_Items';
        } elseif ($name == 'onlinepayment') {
            return 'Intraface_modules_onlinepayment_Controller_Index';
        } elseif ($name == 'send') {
            return 'Intraface_modules_debtor_Controller_Send';
        }
    }

    function GET()
    {
        if ($this->query("action") == "send_onlinepaymentlink") {
            $shared_email = $this->getKernel()->useShared('email');
            $shared_filehandler = $this->getKernel()->useModule('filemanager');
            if ($this->getDebtor()->getPaymentMethodKey() == 5 and $this->getDebtor()->getWhereToId() == 0) {
                try {
                    // echo $this->getDebtor()->getWhereFromId();
                    // @todo We should use a shop gateway here instead
                    $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($this->getDebtor()->getWhereFromId());
                    if ($shop) {
                        $payment_url = $this->getDebtor()->getPaymentLink($shop->getPaymentUrl());
                    }
                } catch (Doctrine_Record_Exeption $e) {
                    throw new Exception('Could not send an e-mail with onlinepayment-link');
                }
            }

            if ($this->getKernel()->intranet->get("pdf_header_file_id") != 0) {
                $file = new FileHandler($this->getKernel(), $this->getKernel()->intranet->get("pdf_header_file_id"));
            } else {
                $file = null;
            }

            $body = 'Tak for din bestilling i vores onlineshop. Vi har ikke registreret nogen onlinebetaling sammen med bestillingen, hvilket kan skyldes flere ting.

    1) Du fortrudt bestillingen, da du skulle til at betale. I så fald må du meget gerne skrive tilbage og annullere din bestilling.
    2) Der er sket en fejl under betalingen. I det tilfælde må du gerne betale ved at gå ind på nedenstående link:

    ' .  $payment_url;
            $subject = 'Betaling ikke modtaget';

            // gem debtoren som en fil i filsystemet
            $filehandler = new FileHandler($this->getKernel());
            $tmp_file = $filehandler->createTemporaryFile($this->t($this->getDebtor()->get("type")).$this->getDebtor()->get('number').'.pdf');

            if (($this->getDebtor()->get("type") == "order" || $this->getDebtor()->get("type") == "invoice") && $this->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
                $this->getKernel()->useModule('onlinepayment', true); // true: ignore_user_access
                $onlinepayment = OnlinePayment::factory($this->getKernel());
            } else {
                $onlinepayment = null;
            }

            // @todo the language on an invoice should be decided by the contacts preference
            $translation = $this->translation;
            $translation->setLang('dk');

            // Her gemmes filen
            $report = new Intraface_modules_debtor_Visitor_Pdf($translation, $file);
            $report->visit($this->getDebtor(), $onlinepayment);

            $report->output('file', $tmp_file->getFilePath());


            // gem filen med filehandleren
            $filehandler = new FileHandler($this->getKernel());
            if (!$file_id = $filehandler->save($tmp_file->getFilePath(), $tmp_file->getFileName(), 'hidden', 'application/pdf')) {
                echo $filehandler->error->view();
                throw new Exception('Filen kunne ikke gemmes');
            }

            $input['accessibility'] = 'intranet';
            if (!$file_id = $filehandler->update($input)) {
                echo $filehandler->error->view();
                throw new Exception('Oplysninger om filen kunne ikke opdateres');
            }

            switch ($this->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
                case 'intranet':
                    $from_email = '';
                    $from_name = '';
                    break;
                case 'user':
                    $from_email = $this->getKernel()->user->getAddress()->get('email');
                    $from_name = $this->getKernel()->user->getAddress()->get('name');
                    break;
                case 'defined':
                    $from_email = $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.email');
                    $from_name = $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.name');
                    break;
                default:
                    throw new Exception("Invalid sender!");
            }
            $contact = new Contact($this->getKernel(), $this->getDebtor()->get('contact_id'));
            $signature = new Intraface_shared_email_Signature($this->context->getKernel()->user, $this->context->getKernel()->intranet, $this->context->getKernel()->getSetting());

            // opret e-mailen
            $email = new Email($this->getKernel());
            if (!$email->save(array(
                    'contact_id' => $contact->get('id'),
                    'subject' => $subject,
                    'body' => $body . "\n\n" . $signature->getAsText(),
                    'from_email' => $from_email,
                    'from_name' => $from_name,
                    'type_id' => 10, // electronic invoice
                    'belong_to' => $this->getDebtor()->get('id')
            ))) {
                echo $email->error->view();
                throw new Exception('E-mailen kunne ikke gemmes');
            }

            // tilknyt fil
            if (!$email->attachFile($file_id, $filehandler->get('file_name'))) {
                echo $email->error->view();
                throw new Exception('Filen kunne ikke vedhæftes');
            }

            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $shared_email = $this->getKernel()->useModule('email');

            // First vi set the last, because we need this id to the first.
            $url = $redirect->setDestination($shared_email->getPath().$email->get('id') . '?edit', NET_SCHEME . NET_HOST . $this->url());
            $redirect->setIdentifier('send_onlinepaymentlink');
            $redirect->askParameter('send_onlinepaymentlink_status');

            return new k_SeeOther($url);
        }


        // delete item
        if ($this->query("action") == "delete_item") {
            $this->getDebtor()->loadItem(intval($_GET["item_id"]));
            $this->getDebtor()->item->delete();
            return new k_SeeOther($this->url(null, array('flare' => 'Item has been deleted')));
        }
        // move item
        if ($this->query("action") == "moveup") {
            $this->getDebtor()->loadItem(intval($_GET['item_id']));
            $this->getDebtor()->item->getPosition($this->mdb2)->moveUp();
        }

        // move item
        if ($this->query("action") == "movedown") {
            $this->getDebtor()->loadItem(intval($_GET['item_id']));
            $this->getDebtor()->item->getPosition($this->mdb2)->moveDown();
        }

        // registrere onlinepayment
        if ($this->getKernel()->user->hasModuleAccess('onlinepayment') && isset($_GET['onlinepayment_action']) && $_GET['onlinepayment_action'] != "") {
            if ($_GET['onlinepayment_action'] != 'capture' || ($this->getDebtor()->get("type") == "invoice" && $this->getDebtor()->get("status") == "sent")) {
                $onlinepayment_module = $this->getKernel()->useModule('onlinepayment'); // true: ignore user permisssion
                $this->onlinepayment = OnlinePayment::factory($this->getKernel(), 'id', intval($_GET['onlinepayment_id']));

                if (!$this->onlinepayment->transactionAction($_GET['onlinepayment_action'])) {
                    $this->onlinepayment_show_cancel_option = true;
                }

                $this->getDebtor()->load();

                // @todo vi skulle faktisk kun videre, hvis det ikke er en tilbagebetaling eller hvad?
                if ($this->getDebtor()->get("type") == "invoice" && $this->getDebtor()->get("status") == "sent" and !$this->onlinepayment->error->isError()) {
                    if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                        return new k_SeeOther($this->url('payment/' . $this->onlinepayment->get('create_payment_id') . '/state'));
                    }
                }
            }
        }

        // edit contact
        if ($this->query('edit_contact')) {
            $debtor_module = $this->getKernel()->module('debtor');
            $contact_module = $this->getKernel()->getModule('contact');
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination($contact_module->getPath().intval($this->getDebtor()->contact->get('id') . '&edit'), NET_SCHEME . NET_HOST . $this->url());
            return new k_SeeOther($url . '&edit');
        }

        // Redirect til tilføj produkt
        if ($this->query('add_item')) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $product_module = $this->getKernel()->useModule('product');
            $redirect->setIdentifier('add_item');

            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('selectproduct', array('set_quantity' => true)), NET_SCHEME . NET_HOST . $this->url());

            $redirect->askParameter('product_id', 'multiple');

            return new k_SeeOther($url);
        }


        // Returns from add product and send mail
        if ($this->query('return_redirect_id')) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');

            if ($return_redirect->get('identifier') == 'add_item') {
                $selected_products = $return_redirect->getParameter('product_id', 'with_extra_value');
                foreach ($selected_products as $product) {
                    $this->getDebtor()->loadItem();
                    $product['value'] = unserialize($product['value']);
                    $this->getDebtor()->item->save(array('product_id' => $product['value']['product_id'], 'product_variation_id' => $product['value']['product_variation_id'], 'quantity' => $product['extra_value'], 'description' => ''));
                }
                $return_redirect->delete();
                $this->getDebtor()->load();
            } elseif ($return_redirect->get('identifier') == 'send_email') {
                if ($return_redirect->getParameter('send_email_status') == 'sent' or $return_redirect->getParameter('send_email_status') == 'outbox') {
                    $this->email_send_with_success = true;
                    // if invoice has been resent the status should not be set again
                    if ($this->getDebtor()->get('status') != 'sent' && $this->getDebtor()->get('status') != 'executed') {
                        $this->getDebtor()->setStatus('sent');
                    }
                    $return_redirect->delete();

                    if (($this->getDebtor()->get("type") == 'credit_note' || $this->getDebtor()->get("type") == 'invoice') and !$this->getDebtor()->isStated() and $this->getKernel()->user->hasModuleAccess('accounting')) {
                        return new k_SeeOther($this->url('state'));
                    }
                }
            }
        }

        return parent::GET();
    }

    function renderHtml()
    {
        if ($this->getKernel()->user->hasModuleAccess('onlinepayment')) {
            $online_module = $this->getKernel()->useModule('onlinepayment');
        }
        if ($this->getKernel()->user->hasModuleAccess('administration')) {
            $module_administration = $this->getKernel()->useModule('administration');
        }
        $contact_module = $this->getKernel()->getModule('contact');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $tpl->render($this);
    }

    function renderHtmlEdit()
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $tpl->render($this);
    }

    function postForm()
    {
        // delete the debtor
        if ($this->body('delete')) {
            $type = $this->getDebtor()->get("type");
            $this->getDebtor()->delete();
            return new k_SeeOther($this->url('../', array('use_stored' => 'true')));
        } elseif ($this->body('send_electronic_invoice')) {
            return new k_SeeOther($this->url('send', array('send' => 'electronic_email')));
        } elseif ($this->body('send_email')) {
            return new k_SeeOther($this->url('send', array('send' => 'email')));
        } // cancel debtor
        elseif ($this->body('cancel') and ($this->getDebtor()->get("type") == "quotation" || $this->getDebtor()->get("type") == "order") && ($this->getDebtor()->get('status') == "created" || $this->getDebtor()->get('status') == "sent")) {
            $this->getDebtor()->setStatus('cancelled');
        } // sets status to sent
        elseif ($this->body('sent')) {
            $this->getDebtor()->setStatus('sent');

            if (($this->getDebtor()->get("type") == 'credit_note' || $this->getDebtor()->get("type") == 'invoice') and $this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            }
            return new k_SeeOther($this->url());
        } // transfer quotation to order
        elseif ($this->body('order')) {
            if ($this->getKernel()->user->hasModuleAccess('order') && $this->getDebtor()->get("type") == "quotation") {
                $this->getKernel()->useModule("order");
                $order = new Order($this->getKernel());
                if ($id = $order->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../'.$id));
                }
            }
        } // transfer forder to invoice
        elseif ($this->body('invoice')) {
            if ($this->getKernel()->user->hasModuleAccess('invoice') && ($this->getDebtor()->get("type") == "quotation" || $this->getDebtor()->get("type") == "order")) {
                $this->getKernel()->useModule("invoice");
                $invoice = new Invoice($this->getKernel());
                if ($id = $invoice->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../' . $id));
                }
            }
        } // Quick process order
        elseif ($this->body('quickprocess_order')) {
            if ($this->getKernel()->user->hasModuleAccess('invoice') && ($this->getDebtor()->get("type") == "quotation" || $this->getDebtor()->get("type") == "order")) {
                $this->getKernel()->useModule("invoice");
                $invoice = new Invoice($this->getKernel());
                if ($id = $invoice->create($this->getDebtor())) {
                }
            }

            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule("invoice");
                $invoice->setStatus('sent');

                if ($this->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
                    $onlinepayment_module = $this->getKernel()->useModule('onlinepayment', true); // true: ignore user permisssion
                    $onlinepayment = OnlinePayment::factory($this->getKernel());
                    $onlinepayment->getDBQuery()->setFilter('belong_to', $invoice->get("type"));
                    $onlinepayment->getDBQuery()->setFilter('belong_to_id', $invoice->get('id'));
                    $actions = $onlinepayment->getTransactionActions();
                    $payment_list = $onlinepayment->getlist();

                    $payment_gateway = new Intraface_modules_onlinepayment_OnlinePaymentGateway($this->getKernel());

                    $success_on_all_payments = true;

                    foreach ($payment_list as $payment) {
                        $onlinepayment = $payment_gateway->findById($payment['id']);
                        try {
                            if (!$onlinepayment->transactionAction('capture')) {
                                $success_on_all_payments = true;
                            }
                        } catch (Exception $e) {
                            $success_on_all_payments = true;
                        }
                    }
                }

                if ($success_on_all_payments === true) {
                    $invoice->setStatus('executed');
                }

                return new k_SeeOther($this->url('../' . $invoice->get('id')));
            }
        } // Execute invoice
        elseif ($this->body('quickprocess_invoice')) {
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule("invoice");
                $this->getDebtor()->setStatus('sent');

                if ($this->getKernel()->user->hasModuleAccess('onlinepayment')) {
                    $onlinepayment_module = $this->getKernel()->useModule('onlinepayment', true); // true: ignore user permisssion
                    $onlinepayment = OnlinePayment::factory($this->getKernel());
                    $onlinepayment->getDBQuery()->setFilter('belong_to', $this->getDebtor()->get("type"));
                    $onlinepayment->getDBQuery()->setFilter('belong_to_id', $this->getDebtor()->get('id'));
                    $actions = $onlinepayment->getTransactionActions();
                    $payment_list = $onlinepayment->getlist();

                    foreach ($payment_list as $payment) {
                        $onlinepayment = OnlinePayment::factory($this->getKernel(), 'id', $payment['id']);
                        try {
                            if (!$onlinepayment->transactionAction('capture')) {
                            }
                        } catch (Exception $e) {
                        }
                    }
                }

                $this->getDebtor()->setStatus('executed');
                return new k_SeeOther($this->url(null . '.pdf'));
            }
        } // create credit note
        elseif ($this->body('credit_note')) {
            if ($this->getKernel()->user->hasModuleAccess('invoice') && $this->getDebtor()->get("type") == "invoice") {
                $credit_note = new CreditNote($this->getKernel());

                if ($id = $credit_note->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../'.$id));
                }
            }
        } // cancel onlinepayment
        elseif ($this->body('onlinepayment_cancel') && $this->getKernel()->user->hasModuleAccess('onlinepayment')) {
            $onlinepayment_module = $this->getKernel()->useModule('onlinepayment');
            $this->onlinepayment = OnlinePayment::factory($this->getKernel(), 'id', intval($_POST['onlinepayment_id']));

            $this->onlinepayment->setStatus('cancelled');
            $this->getDebtor()->load();
        } else {
            $debtor = $this->getDebtor();
            $contact = new Contact($this->getKernel(), $_POST["contact_id"]);

            if ($this->body("contact_person_id") == "-1") {
                $contact_person = new ContactPerson($contact);
                $person["name"] = $_POST['contact_person_name'];
                $person["email"] = $_POST['contact_person_email'];
                $contact_person->save($person);
                $contact_person->load();
                $_POST["contact_person_id"] = $contact_person->get("id");
            }

            if ($this->getKernel()->intranet->hasModuleAccess('currency') && $this->body('currency_id')) {
                $currency_module = $this->getKernel()->useModule('currency', false); // false = ignore user access
                $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
                $currency = $gateway->findById($_POST['currency_id']);
                if ($currency == false) {
                    throw new Exception('Invalid currency');
                }

                $_POST['currency'] = $currency;
            }

            if ($debtor->update($_POST)) {
                return new k_SeeOther($this->url(null));
            }
        }

        return new k_SeeOther($this->url());
    }

    function getValues()
    {
        return $this->getDebtor()->get();
    }

    function getAction()
    {
        return 'Update';
    }

    function getContact()
    {
        return $this->getDebtor()->getContact();
    }

    function getModel()
    {
        return $this->getDebtor();
    }

    function getObject()
    {
        return $this->getDebtor();
    }

    function getType()
    {
        return $this->getDebtor()->get('type');
    }

    function isValidSender()
    {
        switch ($this->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
            case 'intranet':
                if ($this->getKernel()->intranet->address->get('name') == '' || $this->getKernel()->intranet->address->get('email') == '') {
                    return false;
                }
                break;
            case 'user':
                if ($this->getKernel()->user->getAddress()->get('name') == '' || $this->getKernel()->user->getAddress()->get('email') == '') {
                    return false;
                }
                break;
            case 'defined':
                if ($this->getKernel()->getSetting()->get('intranet', 'debtor.sender.name') == '' || $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.email') == '') {
                    return false;
                }
                break;
        }
        return true;
    }

    function isValidScanInContact()
    {
        $scan_in_contact_id = $this->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact');
        $scan_in_contact = new Contact($this->getKernel(), $scan_in_contact_id);
        if ($scan_in_contact->get('id') == 0) {
            return false;
        } elseif (!$scan_in_contact->address->get('email')) {
            return false;
        }
        return true;
    }

    function getMessageAboutEmail()
    {
        $msg = '';
        $contact_module = $this->getKernel()->getModule('contact');
        $debtor_module = $this->getKernel()->getModule('debtor');

        switch ($this->getDebtor()->contact->get('preferred_invoice')) {
            case 2: // if the customer prefers e-mail
                switch ($this->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
                    case 'intranet':
                        if ($this->getKernel()->intranet->address->get('name') == '' || $this->getKernel()->intranet->address->get('email') == '') {
                            if ($this->getKernel()->user->hasModuleAccess('administration')) {
                                $msg = '<div class="message-dependent"><p>'.$this->t('You need to fill in an e-mail address to send e-mail').'. <a href="'.url('../../../../administration/intranet', array('edit')) . '">'.t('do it now').'</a>.</p></div>';
                            } else {
                                $msg = '<div class="message-dependent"><p>'.$this->t('You need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';
                            }
                        }
                        break;
                    case 'user':
                        if ($this->getKernel()->user->getAddress()->get('name') == '' || $this->getKernel()->user->getAddress()->get('email') == '') {
                            $msg = '<div class="message-dependent"><p>'.$this->t('You need to fill in an e-mail address to send e-mail').'. <a href="'.url('../../../../controlpanel/user', array('edit')).'">'.t('do it now').'</a>.</p></div>';
                        }
                        break;
                    case 'defined':
                        if ($this->getKernel()->getSetting()->get('intranet', 'debtor.sender.name') == '' || $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.email') == '') {
                            if ($this->getKernel()->user->hasModuleAccess('administration')) {
                                $msg = '<div class="message-dependent"><p>'.$this->t('You need to fill in an e-mail address to send e-mail').'. <a href="'.$module_debtor->getPath().'settings">'.t('do it now').'</a>.</p></div>';
                            } else {
                                $msg = '<div class="message-dependent"><p>'.$this->t('You need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';
                            }
                        }
                        break;
                    default:
                        throw new Exception("Invalid sender!");
                }

                if ($this->getDebtor()->contact->address->get('email') == '') {
                    $msg = '<div class="message-dependent"><p>'.$this->t('You need to register an e-mail to the contact, so you can send e-mails').'</p></div>';
                }

                break;

            case 3: // electronic email, we make check that everything is as it should be
                if ($this->getDebtor()->contact->address->get('ean') == '') {
                    $msg = '<div class="message-dependent"><p>'.$this->t('To be able to send electronic e-mails you need to fill out the EAN location number for the contact').'</p></div>';
                }

                $scan_in_contact_id = $this->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact');
                $valid_scan_in_contact = true;

                $scan_in_contact = new Contact($this->getKernel(), $scan_in_contact_id);
                if ($scan_in_contact->get('id') == 0) {
                    $valid_scan_in_contact = false;
                    $msg = '<div class="message-dependent"><p>';
                    $msg .= $this->t('A contact for the scan in bureau is needed to send electronic invoices').'. ';
                    if ($this->getKernel()->user->hasModuleAccess('administration')) {
                        $msg .= '<a href="'.$debtor_module->getPath().'settings">'.$this->t('Add it now').'</a>.';
                    }
                    $msg .= '</p></div>';
                } elseif (!$scan_in_contact->address->get('email')) {
                    $valid_scan_in_contact = false;
                    $msg = '<div class="message-dependent"><p>';
                    $msg .= $this->t('You need to provide a valid e-mail address to the contact for the scan in bureau').'.';
                    $msg .= ' <a href="'.$contact_module->getPath().$scan_in_contact->get('id').'">'.t('Add it now').'</a>.';
                    $msg .= '</p></div>';
                }
                break;
        }

        return $msg;
    }

    function addItem($product, $quantity = 1)
    {
        $this->getDebtor()->loadItem();
        $this->getDebtor()->item->save(array('product_id' => $product['product_id'], 'product_variation_id' => $product['product_variation_id'], 'quantity' => $quantity, 'description' => ''));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDebtor()
    {
        $contact_module = $this->getKernel()->getModule('contact');

        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        return ($this->debtor = $this->context->getGateway()->findById(intval($this->name())));
    }

    function renderPdf()
    {
        if (($this->getDebtor()->get("type") == "order" || $this->getDebtor()->get("type") == "invoice") && $this->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
            $this->getKernel()->useModule('onlinepayment', true); // true: ignore_user_access
            $onlinepayment = OnlinePayment::factory($this->getKernel());
        } else {
            $onlinepayment = null;
        }

        if ($this->getKernel()->intranet->get("pdf_header_file_id") != 0) {
            $this->getKernel()->useModule('filemanager');
            $filehandler = new FileHandler($this->getKernel(), $this->getKernel()->intranet->get("pdf_header_file_id"));
        } else {
            $filehandler = null;
        }

        $report = new Intraface_modules_debtor_Visitor_Pdf($this->translator(), $filehandler);
        $report->visit($this->getDebtor(), $onlinepayment);

        return $report->output('stream');
    }

    function renderHtmlDelete()
    {
        $this->getDebtor()->delete();
        return new k_SeeOther($this->url('../', array('use_stored' => true)));
    }

    function renderOioxml()
    {
        require_once dirname(__FILE__) . '/../Visitor/OIOXML.php';
        $render = new Debtor_Report_OIOXML;
        return $render->output($this->getDebtor());
    }

    function renderTxt()
    {
        require_once dirname(__FILE__) . '/../Visitor/Text.php';
        $render = new Debtor_Report_Text;
        return $render->output($this->getDebtor());
    }
}
