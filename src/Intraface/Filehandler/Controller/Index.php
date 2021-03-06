<?php
class Intraface_Filehandler_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'batchedit') {
            return 'Intraface_Filehandler_Controller_Batchedit';
        } elseif ($name == 'uploadmultiple') {
            return 'Intraface_Filehandler_Controller_UploadMultiple';
        } elseif ($name == 'uploadscript') {
            return 'Intraface_Filehandler_Controller_UploadScript';
        } elseif ($name == 'upload') {
            return 'Intraface_Filehandler_Controller_Upload';
        } elseif ($name == 'sizes') {
            return 'Intraface_Filehandler_Controller_Sizes';
        } elseif ($name == 'selectfile') {
            return 'Intraface_Filehandler_Controller_SelectFile';
        } elseif ($name == 'ckeditor') {
            return 'Intraface_Filehandler_Controller_CKEditor';
        }
        return 'Intraface_Filehandler_Controller_Show';
    }

    public function renderHtml()
    {
        $this->document->setTitle('File manager');

        $gateway = $this->getGateway();

        if ($this->query('search')) {
            if ($this->query('text') != '') {
                $gateway->getDBQuery()->setFilter('text', $this->query('text'));
            }

            if (intval($this->query('filtration')) != 0) {
                $gateway->getDBQuery()->setFilter('filtration', $this->query('filtration'));

                switch ($this->query('filtration')) {
                    case 1:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y').' 00:00');
                        break;
                    case 2:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y', time()-60*60*24).' 00:00');
                        $gateway->getDBQuery()->setFilter('uploaded_to_date', date('d-m-Y', time()-60*60*24).' 23:59');
                        break;
                    case 3:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y', time()-60*60*24*7).' 00:00');
                        break;
                    case 4:
                        $gateway->getDBQuery()->setFilter('edited_from_date', date('d-m-Y').' 00:00');
                        break;
                    case 5:
                        $gateway->getDBQuery()->setFilter('edited_from_date', date('d-m-Y', time()-60*60*24).' 00:00');
                        $gateway->getDBQuery()->setFilter('edited_to_date', date('d-m-Y', time()-60*60*24).' 23:59');
                        break;
                    case 6:
                        $gateway->getDBQuery()->setFilter('accessibility', 'public');
                        break;
                    case 7:
                        $gateway->getDBQuery()->setFilter('accessibility', 'intranet');
                        break;
                    default:
                        // Probably 0, so nothing happens
                        break;
                }
            }

            if (is_array($this->query('keyword')) && count($this->query('keyword')) > 0) {
                $gateway->getDBQuery()->setKeyword($this->query('keyword'));
            }
        } elseif ($this->query('character')) {
            $gateway->getDBQuery()->useCharacter();
        } else {
            $gateway->getDBQuery()->setSorting('file_handler.date_created DESC');
        }
        $gateway->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
        $gateway->getDBQuery()->usePaging('paging', $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $gateway->getDBQuery()->storeResult('use_stored', 'filemanager', 'toplevel');
        $gateway->getDBQuery()->setUri($this->url());

        $files = $gateway->getList();

        $selected_keywords = $gateway->getDBQuery()->getKeyword();
        $keyword = new Intraface_Keyword_Appender($gateway);
        $keywords = $keyword->getUsedKeywords();

        $data = array(
            'files' => $files,
            'filemanager' => $gateway,
            'selected_keywords' => $selected_keywords,
            'keywords' => $keywords);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getGateway()
    {
        return new Ilib_Filehandler_Gateway($this->getKernel());
    }
}
