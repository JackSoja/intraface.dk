<?php
class Intraface_modules_newsletter_Controller_Lists extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_List';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = new k_Template(dirname(__FILE__) . '/templates/lists.tpl.php');
        return $smarty->render($this);
    }

    function renderHtmlCreate()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/list-edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $list = new NewsletterList($this->getKernel());
        if ($id = $list->save($_POST)) {
            return new k_SeeOther($this->url($id));
        }
        return $this->render();
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

    function getList()
    {
        $module = $this->getKernel()->module("newsletter");
        return $list = new NewsletterList($this->getKernel());
    }

    function t($phrase)
    {
         return $phrase;
    }

    function getValues()
    {
        return array();
    }
}