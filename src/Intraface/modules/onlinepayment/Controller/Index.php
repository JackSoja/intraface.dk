<?php
class Intraface_modules_onlinepayment_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'settings') {
            return 'Intraface_modules_onlinepayment_Controller_Settings';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_onlinepayment_Controller_Payment';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $module = $this->context->getKernel()->module('onlinepayment');
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');

        $onlinepayment = OnlinePayment::factory($this->context->getKernel());

        if (isset($_GET['status'])) {
            $onlinepayment->getDBQuery()->setFilter('status', $_GET['status']);
        } else {
            $onlinepayment->getDBQuery()->setFilter('status', 2);
        }
        if (isset($_GET['text'])) {
            $onlinepayment->getDBQuery()->setFilter('text', $_GET['text']);
        }
        if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
            $onlinepayment->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
        }
        if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
            $onlinepayment->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
        }

        $payments = $onlinepayment->getList();

        $data = array(
            'kernel' => $this->getKernel(),
            'payments' => $payments,
            'onlinepayment' => $onlinepayment);

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, $data);
    }
}
