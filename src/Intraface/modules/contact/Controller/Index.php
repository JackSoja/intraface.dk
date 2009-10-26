<?php
class Intraface_modules_contact_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        return new k_SeeOther(PATH_WWW."modules/contact/index_old.php");
        /*
        $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/lists.tpl.php');
        return $smarty->render($this);
        */
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

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        }

    }
}