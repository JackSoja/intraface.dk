<?php
/**
 * Momsafregning
 *
 * Denne side skal v�re en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anf�rt p� momskonti.
 *
 * N�r man klikker p� angiv moms skal tallene gemmes i en database.
 *
 * Hvis man vil redigere tallene, klikker man sig hen til vat_edit.php
 *
 * Siden skal regne ud, om der er forskel p� de tal, der er blevet
 * opgivet og det der rent faktisk skulle v�re opgivet, s� man kan fange
 * evt. fejl n�ste gang man skal opgive moms.
 *
 * Primosaldoer skal naturligvis fremg� af momsopg�relsen.
 *
 * Der skal v�re en liste med momsangivelsesperioder for �ret,
 * og s� skal der ud for hver momssopgivelse v�re et link enten til
 * den tidligere opgivne moms eller til at oprette en momsangivelse.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
class Intraface_modules_accounting_Controller_Account_Show extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $kernel->module('accounting');
        $translation = $kernel->getTranslation('accounting');

        $allowed_periods = $module->getSetting('vat_periods');

        $year = new Year($kernel);
        $year->checkYear();

        $vat_period = new VatPeriod($year);

        $periods = $vat_period->getList();
        $post = new Post(new Voucher($year));

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/vat/period.tpl.php');
        return $smarty->render($this);
    }

    function renderForm()
    {
    	if (isset($_POST['vat_period_key'])) {
    		$year->setSetting('vat_period', $_POST['vat_period_key']);
    	}
    	$vat_period = new VatPeriod($year);
    	$vat_period->createPeriods();
    	header('Location: vat_period.php');
    	exit;
    }

    function GET()
    {
        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $vat_period = new VatPeriod($year, $_GET['delete']);
            $vat_period->delete();
        }

        parent::GET();
    }
}