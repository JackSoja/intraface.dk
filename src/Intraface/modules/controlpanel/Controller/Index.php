<?php
class Intraface_modules_controlpanel_Controller_Index extends k_Component
{
    protected $intranetmaintenance;

    protected function map($name)
    {
        if ($name == 'intranet') {
            return 'Intraface_modules_controlpanel_Controller_Intranet';
        } elseif ($name == 'user') {
            return 'Intraface_modules_controlpanel_Controller_User';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('controlpanel');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }
}