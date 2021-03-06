<?php
class Intraface_modules_procurement_Controller_Index extends k_Component
{
    private $gateway;
    private $error;
    public $method = 'post';
    protected $template;
    protected $procurement;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_procurement_Controller_Show';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle($this->t('Procurement'));

        $module = $this->getKernel()->module('procurement');
        $module = $this->getKernel()->useModule('contact');

        if (intval($this->query("contact_id")) != 0 && $this->getKernel()->user->hasModuleAccess('contact')) {
            // @todo We need some way to identify this controller i used from contact? /Sune 29-11-2009
            $contact_module = $this->getKernel()->useModule('contact');
            $contact = new Contact($this->getKernel(), $this->query('contact_id'));
            $this->getProcurementGateway()->getDBQuery()->setFilter("contact_id", $this->query("contact_id"));
        }

        if ($this->query("text") != "") {
            $this->getProcurementGateway()->getDBQuery()->setFilter("text", $this->query("text"));
        }

        if ($this->query("from_date") != "") {
            $this->getProcurementGateway()->getDBQuery()->setFilter("from_date", $this->query("from_date"));
        }

        if ($this->query("to_date") != "") {
            $this->getProcurementGateway()->getDBQuery()->setFilter("to_date", $this->query("to_date"));
        }

        if ($this->query("status")) {
            $this->getProcurementGateway()->getDBQuery()->setFilter("status", $this->query("status"));
        } else {
            $this->getProcurementGateway()->getDBQuery()->setFilter("status", "-2");
        }
        if ($this->query('not_stated')) {
            $this->getProcurementGateway()->getDBQuery()->setFilter("not_stated", "1");
        }
        if ($this->getProcurementGateway()->getDBQuery()->checkFilter("contact_id")) {
            $this->getProcurementGateway()->getDBQuery()->setFilter("status", "-1");
        }

        $this->getProcurementGateway()->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $this->getProcurementGateway()->getDBQuery()->storeResult("use_stored", "procurement", "toplevel");
        $this->getProcurementGateway()->getDBQuery()->setUri($this->url(null, array('use_stored' => 'true')));
        $procurements = $this->getProcurementGateway()->find();

        $data = array(
            'gateway' => $this->getProcurementGateway(),
            'procurements' => $procurements
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, $data);
    }

    public function renderHtmlCreate()
    {
        $this->document->setTitle($this->t("Create procurement"));
        $this->document->addScript('procurement/edit.js');

        $data = array(
            'values' => $this->getValues(),
            'title' => $this->t('Create procurement'),
            'gateway' => $this->getProcurementGateway()
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/procurement-edit');
        return $smarty->render($this, $data);
    }

    public function postForm()
    {
        if ($this->getProcurement()->update($this->body())) {
            if ($this->body("recieved") == "1") {
                $this->getProcurement()->setStatus("recieved");
            }
            return new k_SeeOther($this->url($this->getProcurement()->get("id")));
        }

        return $this->render();
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        $values = array(
            'number' => $this->getProcurementGateway()->getMaxNumber() + 1
        );
        return $values;
    }

    function getProcurement()
    {
        if (is_object($this->procurement)) {
            return $this->procurement;
        }

        return $this->procurement = new Procurement($this->getKernel());
    }

    public function getKernel()
    {
        return $this->context->getKernel();
    }

    function getError()
    {
        return $this->getProcurement()->error;
    }

    public function getProcurementGateway()
    {
        if (!is_object($this->gateway)) {
            $this->gateway = new Intraface_modules_procurement_ProcurementGateway($this->getKernel());
        }

        return $this->gateway;
    }
}
