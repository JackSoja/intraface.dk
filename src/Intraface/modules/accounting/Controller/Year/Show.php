<?php
class Intraface_modules_accounting_Controller_Year_Show extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Year_Edit';
        } elseif ($name == 'daybook') {
            return 'Intraface_modules_accounting_Controller_Daybook';
        } elseif ($name == 'settings') {
            return 'Intraface_modules_accounting_Controller_Settings';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        if (!$this->getYear()->isValid()) {
            trigger_error('�ret er ikke gyldigt', E_USER_ERROR);
        }

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/show.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel(), $this->name());
    }

    function POST()
    {
        if (!empty($_POST['start']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('daybook'));
        } elseif (!empty($_POST['primobalance']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('daybook'));
        } elseif (!empty($_POST['manual_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('account'));
        } elseif (!empty($_POST['standard_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $this->getYear()->setYear();
            if (!$this->getYear()->createAccounts('standard')) {
                throw new Exception('Kunne ikke oprette standardkontoplanen');
            }

            $values = $this->getYear()->get();
            return new k_SeeOther($this->url());

        } elseif (!empty($_POST['transfer_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            // kontoplanen fra sidste �r hentes
            $year = $this->getYear();
            $year->setYear();
            if (empty($_POST['accountplan_year']) OR !is_numeric($_POST['accountplan_year'])) {
                $year->error->set('Du kan ikke oprette kontoplanen, for du har ikke valgt et �r at g�re det fra');
            } else {
                if (!$year->createAccounts('transfer_from_last_year', $_POST['accountplan_year'])) {
                    throw new Exception('Kunne ikke oprette standardkontoplanen');
                }
            }
            $values = $year->get();
        }
    }

    function getYears()
    {
    	return $this->getYear()->getList();
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getVatPeriod()
    {
    	return new VatPeriod($this->getYear());
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }
}