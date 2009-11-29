<?php
class Intraface_modules_onlinepayment_Controller_Settings extends k_Component
{
    protected $doctrine;

    function __construct(Doctrine_Connection_Common $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function map($name)
    {
        return 'Intraface_modules_onlinepayment_Controller_ChooseProvider';
    }

    function renderHtml()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');
        $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

        $onlinepayment = OnlinePayment::factory($this->context->getKernel());
        $language = new Intraface_modules_language_Languages;
        // @todo der skal laves en gateway, der bruger dql.
        $settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($this->context->getKernel()->intranet->getId());

        if (!$settings) {
        	$settings = new Intraface_modules_onlinepayment_Language;
            $settings->save();
        }
        $value = $onlinepayment->getSettings();

        $smarty = new k_Template(dirname(__FILE__) . '/templates/settings.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');
        $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

        $onlinepayment = OnlinePayment::factory($this->context->getKernel());
        $language = new Intraface_modules_language_Languages;
        $settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($this->context->getKernel()->intranet->getId());
        if (!$settings) {
        	$settings = new Intraface_modules_onlinepayment_Language;
            $settings->save();
        }
        $settings->Translation['da']->email = $_POST['email']['da'];
        $settings->Translation['da']->subject = $_POST['subject']['da'];

        foreach ($language->getChosenAsArray() as $lang) {
            $settings->Translation[$lang->getIsoCode()]->email = $_POST['email'][$lang->getIsoCode()];
            $settings->Translation[$lang->getIsoCode()]->subject = $_POST['subject'][$lang->getIsoCode()];
        }

        $settings->save();

    	if ($onlinepayment->setSettings($_POST)) {
    		return new k_SeeOther($this->url());
    	} else {
    		$value = $_POST;
    	}
        return $this->render();
    }
}