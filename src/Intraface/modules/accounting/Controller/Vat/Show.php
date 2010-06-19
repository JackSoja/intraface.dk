<?php
/**
 * Momsafregning
 *
 * Denne side skal v�re en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anf�rt p� momskonti.
 *
 * N�r man klikker p� angiv moms skal tallene gemmes i en database. *
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
 * @todo Der kunne skrives en advarsel, hvis man ikke har sat eu-konti mv.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
class Intraface_modules_accounting_Controller_Vat_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getVatPeriod()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/vat/show');
        return $smarty->render($this);
    }

    function GET()
    {
     	$vat_period = new VatPeriod($this->getYear(), $this->name());
       	$vat_period->loadAmounts();
       	$account_vat_in = $vat_period->get('account_vat_in');
       	$account_vat_out = $vat_period->get('account_vat_out');
       	$account_vat_abroad = $vat_period->get('account_vat_abroad');
       	$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
       	$saldo_total = $vat_period->get('saldo_total');

        return parent::GET();
    }

    function postForm()
    {
        /*
        if (!empty($_POST['get_amounts']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        	$vat_period = new VatPeriod($year, $_POST['id']);
        	$vat_period->loadAmounts();
        	$account_vat_in = $vat_period->get('account_vat_in');
        	$account_vat_out = $vat_period->get('account_vat_out');
        	$account_vat_abroad = $vat_period->get('account_vat_abroad');
        	//$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
        	$saldo_total = $vat_period->get('saldo_total');

        	$amount = array(
        		'vat_out' => $account_vat_out->get('saldo'),
        		'vat_abroad' => $account_vat_abroad->get('saldo'),
        		'vat_in' => $account_vat_in->get('saldo')
        	);

        	//$vat_period->saveAmounts($amount);
        	header('Location: vat_view.php?id='.$vat_period->get('id'));
        	exit;
        }
        */
        if (!empty($_POST['state']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        	$vat_period = new VatPeriod($this->getYear(), $_POST['id']);

        	if (!$vat_period->state($_POST['date'], $_POST['voucher_number'])) {
        		throw new Exception('Could not state');
        	}

        	return new k_SeeOther($this->url());
        }
    }

    function getError()
    {
        return $error = new Intraface_Error;
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function getVoucher()
    {
        return new Voucher($this->getYear());
    }

    function getVatPeriod()
    {
        return $vat_period = new VatPeriod($this->getYear(), $this->name());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}