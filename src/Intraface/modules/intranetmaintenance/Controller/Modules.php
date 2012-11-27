<?php
class Intraface_modules_intranetmaintenance_Controller_Modules extends k_Component
{
    protected $module_msg = array();
    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function renderHtml()
    {
        $module = $this->getModule();

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/modules');
        return $smarty->render($this);
    }

    function putForm()
    {
        $this->module_msg = $this->getModuleMaintenance()->registerAll();
        $this->getKernel()->user->clearCachedPermission(); // reloads all permissions

        // using $this->render() to be able to show the message
        return $this->render();
    }

    function getModule()
    {
        return$this->getKernel()->module("intranetmaintenance");
    }

    function getModuleMsg()
    {
        return $this->module_msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModuleMaintenance()
    {
        $primary_module = $this->getKernel()->module("intranetmaintenance");

        return new Intraface_ModuleGateway($this->mdb2);
    }

    function getModules()
    {
        return $this->getModuleMaintenance()->getList();
    }
}