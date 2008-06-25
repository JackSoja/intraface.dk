<?php
/**
 * @package Intraface_OnlinePayment
 */
/**
 * Onlinebetaling
 *
 * Onlinebetalingerne skal kunne knytte betalinger til debtor. M�ske
 * kan den fungere lidt ligesom elementer i CMS, og s� kan vi skrive nogle enkelte
 * udbydere, hvor vi starter med QuickPay - hvis det hele da skal k�re over vores
 * system. Det kan ogs� v�re, at kodningen skal foretages hos den enkelte?
 *
 * S� det grundl�ggende sp�rgsm�l er om selve betalingsl�sningen skal programmeres p�
 * klienten eller i systemet.
 */
class OnlinePayment extends Intraface_Standard
{
    public $id;
    public $kernel;

    // Standard udbyder-transactionsstatus. Er lavet ud fra QuickPay
    public $transaction_status_types = array(
        '' => 'Ingen kontakt til udbyder - mangler $eval',
        '000' => '', // Betalingsoplysninger godkendt
        '001' => 'Afvist af PBS',
        '002' => 'Kommunikationsfejl',
        '003' => 'Kort udl�bet',
        '004' => 'Status er forkert (Ikke autoriseret)',
        '005' => 'Autorisation er for�ldet',
        '006' => 'Fejl hos PBS',
        '007' => 'Fejl hos udbyder',
        '008' => 'Fejl i parameter sendt til udbyder'
    );

    public $transaction_status_authorized = "000";

    function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Debtor requires Kernel, got:'.get_class($kernel), E_USER_ERROR);
        }

        $this->kernel = $kernel;
        $this->id = $id;
        $this->error = new Intraface_Error;

        // lidt usikker p� om det her er det smarteste sted at have den, men den skal v�re til stede, n�r der skal gemmes
        $this->provider_key = $this->kernel->setting->get('intranet', 'onlinepayment.provider_key');

        $this->dbquery = new Intraface_DBQuery($this->kernel, "onlinepayment", "intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);

        if ($this->id > 0) {
            $this->load();
        }
        else {
            $this->value['id'] = 0;
        }
    }

    function factory($kernel, $type = 'settings', $value = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Onlinepayment kr�ver Kernel som objekt', E_USER_ERROR);
        }

        $implemented_providers = OnlinePayment::getImplementedProviders();
        // we set the fallback from settings
        if (!isset($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')])) {
            trigger_error('Ikke en gyldig provider fra settings i OnlinePayment->factory', E_USER_ERROR);
        }
        $provider = $implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')];

        switch($type) {
            case 'settings':
                // We accept it, but do nothing as we just use fallback provider
                break;
            case 'id':
                $db = new DB_Sql;
                $db->query("SELECT provider_key FROM onlinepayment WHERE id = ".(int)$value. " AND intranet_id = " . $kernel->intranet->get('id'));
                if (!$db->nextRecord()) {
                    trigger_error('OnlinePayment::factory: Ikke et gyldigt id', E_USER_ERROR);
                }
                $provider = $implemented_providers[$db->f('provider_key')];
                break;
            case 'provider':
                if (!in_array($value, $implemented_providers)) {
                    trigger_error('Ikke en gyldig provider i OnlinePayment->factory case: provider', E_USER_ERROR);
                    exit;
                }
                $provider = $value;
                break;
            case 'transactionnumber':
                $db = new DB_Sql;
                $db->query("SELECT provider_key FROM onlinepayment WHERE transaction_number = '".$value."' AND intranet_id = " . $kernel->intranet->get('id'));
                if (!$db->nextRecord()) {
                    trigger_error('OnlinePayment::factory: Ikke et gyldigt transactionnumber', E_USER_ERROR);
                    exit;
                }
                $provider = $implemented_providers[$db->f('provider_key')];
                break;
            default:
                trigger_error('Ikke gyldig type i Onlinebetaling', E_USER_ERROR);
                break;
        }

        switch(strtolower($provider)) {
            case 'default':
                require_once 'Intraface/modules/onlinepayment/provider/Default.php';
                return new OnlinePaymentDefault($kernel, $value);
                break;
            case 'quickpay':
                require_once 'Intraface/modules/onlinepayment/provider/QuickPay.php';
                return new OnlinePaymentQuickPay($kernel, $value);
                break;
            case 'dandomain':
                require_once 'Intraface/modules/onlinepayment/provider/DanDomain.php';
                return new OnlinePaymentDanDomain($kernel, $value);
                break;

            default:
                trigger_error("Ugyldig onlinebetalingsudbyder", E_USER_ERROR);
        }
    }

    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, date_created, date_authorized, date_captured, date_reversed, belong_to_key, belong_to_id, text, status_key, amount, original_amount, transaction_number, transaction_status,
                DATE_FORMAT(date_created, '%d-%m-%Y') AS dk_date_created,
                DATE_FORMAT(date_authorized, '%d-%m-%Y') AS dk_date_authorized,
                DATE_FORMAT(date_captured, '%d-%m-%Y') AS dk_date_captured,
                DATE_FORMAT(date_reversed, '%d-%m-%Y') AS dk_date_reversed
            FROM onlinepayment WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        if ($db->nextRecord()) {

            $this->value['id'] = $db->f('id');
            $this->value['dk_date_created'] = $db->f('dk_date_created');
            $this->value['date_created'] = $db->f('date_created');

            $this->value['dk_date_authorized'] = $db->f('dk_date_authorized');
            $this->value['date_authorized'] = $db->f('date_authorized');

            $this->value['dk_date_captured'] = $db->f('dk_date_captured');
            $this->value['date_captured'] = $db->f('date_captured');

            $this->value['dk_date_reversed'] = $db->f('dk_date_reversed');
            $this->value['date_reversed'] = $db->f('date_reversed');

            $this->value['belong_to_key'] = $db->f('belong_to_key');
            $belong_to_types = $this->getBelongToTypes();
            $this->value['belong_to'] = $belong_to_types[$db->f('belong_to_key')];
            $this->value['belong_to_id'] = $db->f('belong_to_id');
            $this->value['text'] = $db->f('text');
            $this->value['status_key'] = $db->f('status_key');
            $status_types = OnlinePayment::getStatusTypes();
            $this->value['status'] = $status_types[$db->f('status_key')];
            $this->value['amount'] = $db->f('amount');
            $this->value['dk_amount'] = number_format($db->f('amount'), 2, ",", ".");

            $this->value['original_amount'] = $db->f('original_amount');
            $this->value['dk_original_amount'] = number_format($db->f('original_amount'), 2, ",", ".");


            $this->value['transaction_number'] = $db->f('transaction_number');
            $this->value['transaction_status'] = $db->f('transaction_status');
            $this->value['transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            if ($db->f('transaction_status') != $this->transaction_status_authorized) {
                $this->value['user_transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $this->value['user_transaction_status_translated'] = "";
            }
            return $this->id;
        } else {
            $this->id = 0;
            $this->value['id'] = 0;
            return 0;
        }
    }

    /**
     * Funktion der gemmer onlinebetaling gennem xml-rpc-serveren
     *
     * @input: array(belong_to, belong_to_id, transaction_number, transaction_status, amount);
     *
     * @return integer
      */
    function save($input)
    {
        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        if (!isset($input['belong_to'])) {
            $input['belong_to'] = 0;
        }
        $belong_to_key = array_search($input['belong_to'], $this->getBelongToTypes());
        if ($input['belong_to'] == '' || $belong_to_key === false) {
            $this->error->set("Ugyldig belong_to");
        }

        if (!isset($input['belong_to_id'])) {
            $input['belong_to_id'] = 0;
        }
        $validator->isNumeric($input['belong_to_id'], 'belong_to_id er ikke et tal');
        if (!isset($input['transaction_number'])) {
            $input['transaction_number'] = 0;
        }
        $validator->isString($input['transaction_number'], 'transaction_number er ikke gyldig');
        if (!isset($input['transaction_status'])) {
            $input['transaction_status'] = '';
        }
        $validator->isString($input['transaction_status'], 'transaction_status er ikke udfyldt');
        if (!isset($this->transaction_status_types[$input['transaction_status']])) {
            $this->error->set("transaction_status '".$input['transaction_status']."' er ikke en gyldig status");
        }

        // V�R LIGE OPM�RKSOM HER: INDTIL VIDERE KAN KUN ACCEPTEREDE TRANSAKTIONER GEMMES
        if ($input['transaction_status'] != $this->transaction_status_authorized) {
            $this->error->set("Transactionen er ikke godkendt, s� den kan ikke gemmes");
        }

        if (!isset($input['amount'])) {
            $input['amount'] = 0;
        }
        if ($validator->isDouble($input['amount'], 'amount er ikke et gyldigt bel�b')) {
            $amount = new Intraface_Amount($input['amount']);
            if ($amount->convert2db()) {
                $input['amount'] = $amount->get();
            } else {
                $this->error->set("Kunne ikke konvertere amount til databasen!");
            }
        }

        if (array_key_exists('text', $input)) {
            $validator->isString($input['text'], 'text er ikke en gyldig streng', '', 'allow_empty');
        } else {
            $input['text'] = '';
        }

        if ($this->error->isError()) {
            return 0;
        }

        $sql = "date_changed = NOW(),
            status_key = 2,
            belong_to_key = ".$belong_to_key.",
            belong_to_id = ".$input['belong_to_id'].",
            text = \"".$input['text']."\",
            transaction_number = ".$input['transaction_number'].",
            transaction_status = \"".$input['transaction_status']."\",
            amount = ".$input['amount'].",
            provider_key = ".$this->provider_key.",
            original_amount = ".$input['amount'];

        $db = new DB_Sql;

        if ($this->id > 0) {
            $db->query("UPDATE onlinepayment SET ".$sql." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        } else {

            $db->query("INSERT INTO onlinepayment SET ".$sql.",
                intranet_id = ".$this->kernel->intranet->get('id').",
                date_created = NOW()");
            $this->id = $db->insertedId();
        }
        $this->load();

        return $this->id;

    }


    /**
     * Creates an onlinepayment to be processed
     *
     * @return integer payment_id
     */
    function create()
    {
        $provider_key = $this->kernel->setting->get('intranet', 'onlinepayment.provider_key');
        $db = new DB_Sql;

        $db->query("INSERT INTO onlinepayment SET
            status_key = 1,
            intranet_id = ".$this->kernel->intranet->get('id').",
            date_created = NOW(),
            provider_key = ".$provider_key);
        return $db->insertedId();
    }


    /**
     * Funktion til at opdatere betaling inden fra intranettet
     *
     */

    function update($input)
    {
        if ($this->id == 0) {
            trigger_error("OnlinePayment->update kan kun k�res p� en allerede oprettet betaling", E_USER_ERROR);
        }

        if ($this->getStatus() != 'authorized') {
            trigger_error("OnlinePayment->update kan kun k�res p� betaling der er authorized", E_USER_ERROR);
        }

        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        if ($validator->isDouble($input['dk_amount'], 'Bel�b er ikke et gyldigt bel�b', 'greater_than_zero')) {
            $amount = new Intraface_Amount($input['dk_amount']);
            if ($amount->convert2db()) {
                $input['amount'] = $amount->get();
            } else {
                $this->error->set("Kunne ikke konvertere amount til databasen!");
            }
        }

        if ($input['amount'] > $this->get('original_amount')) {
            $this->error->set("Du kan ikke s�tte bel�bet h�jere end hvad kunden har godkendt: ".$this->get('dk_original_amount'));
        }

        if ($this->error->isError()) {
            return 0;
        }

        $db = new DB_Sql;
        $db->query("UPDATE onlinepayment SET amount = ".$input['amount'].", date_changed = NOW() WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return $this->id;
    }

    function changeBelongTo($belong_to, $belong_to_id)
    {
        if ($this->id == 0) {
            trigger_error("OnlinePayment->setBelongTo kan kun �ndre eksisterende betalinger", FATAL);
        }

        $belong_to = safeToDb($belong_to);

        $belong_to_key = array_search($belong_to, $this->getBelongToTypes());
        if ($belong_to == '' || $belong_to_key === false) {
            trigger_error("Ugyldig belong_to i OnlinePayment->changeBelongTo()", FATAL);
        }

        if (!is_int($belong_to_id)) {
            trigger_error("Belong_to_id er ikke et tal i OnlinePayment->changeBelongTo()", FATAL);
        }

        $db = new DB_Sql;
        $db->query("UPDATE onlinepayment SET belong_to_key = ".$belong_to_key.", belong_to_id = ".$belong_to_id." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return $this->id;

    }

    function setStatus($status)
    {
        if ($this->id == 0) {
            trigger_error("OnlinePayment->setStatus kan kun �ndre eksisterende betalinger", E_USER_ERROR);
        }
        $status = safeToDb($status);


        $status_key = array_search($status, OnlinePayment::getStatusTypes());
        if ($status == "" || $status_key === false) {
            trigger_error("Ugyldig status i OnlinePayment->setStatus()", E_USER_ERROR);
        }

        if ($status_key <= $this->get('status_key')) {
            trigger_error("Kan ikke skifte til lavere eller samme status i OnlinePayment->setStatus()", E_USER_ERROR);
        }

        switch($status) {
            case "authorized":
                $date_field = "date_authorized";
                break;
            case "captured":
                $date_field = "date_captured";
                break;
            case "reversed":
                $date_field = "date_reversed";
                break;
            case "cancelled":
                $date_field = "date_cancelled";
                break;
        }

        $db = new DB_Sql;

        $db->query("UPDATE onlinepayment SET status_key = ".$status_key.", ".$date_field." = NOW() WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);

        $this->value['status_key'] = $status_key;

        return true;
    }

    /**
     * Tilf�jer en onlinebetaling som betaling til faktura
     *
     */
    function addAsPayment()
    {
        if ($this->get('status') != 'authorized') {
            $this->error->set("Der kan kun udf�res handlinger p� betalinger der er godkendt");
            return 0;
        }

        if ($this->get('belong_to') != 'invoice') {
            $this->error->set("Der kan kun udf�res handlinger p� betalinger der er tilknyttet en faktura");
            return 0;
        }

        if (!$this->kernel->intranet->hasModuleAccess('invoice')) {
            return 0;
        }

        $invoice_module = $this->kernel->getModule('debtor', true); // true: tjekker kun intranet adgang

        $invoice = Debtor::factory($this->kernel, (int)$this->get('belong_to_id'));

        if ($invoice->get('id') == 0) {
            $this->error->set("Ugyldig faktura");
            return 0;
        }

        $payment = new Payment($invoice);

        $input = array(
            "payment_date" => date("d-m-Y"),
            "amount" => $this->get("dk_amount"),
            "description" => "Transaction ".$this->get('transaction_number'),
            "type" => 2);
        // type = 2: credit_card


        if ($payment->update($input)) {
            $this->value['create_payment_id'] = $payment->get('id');
            return true;

        } else {
            $this->error->merge($payment->error->getMessage());
            return false;
        }
    }

    /**
     * Returns the possible actions to perform on an onlinepayment.
     * These are defined individually to all providers. The actual action is executed in OnlinePayment->transactionAction()
     *
     * Nb. the action 'capture' is not shown in debtor (view.php) before it is an sent invoice.
     * 
     * @todo better description of this, what is it used for. I think that the label
     *       has to go by the way.
     *
     * @return array	with actions to perform on onlinepayment.
     */
    function getTransactionActions()
    {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'H�v')
        );
        /*
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'H�v'),
            1 => array(
                'action' => 'reverse',
                'label' => 'Tilbagebetal')
        );
        */
    }

    /**
     * @todo remove this or is it just an abstract method which the providers must have?
     */
    function transactionAction($action)
    {
        return false;
    }

    function getList()
    {
        if ($this->dbquery->getFilter('belong_to') != '') {
            if ($this->dbquery->getFilter('belong_to_id') == 0) {
                trigger_error("belong_to_id er nul i OnlinePayment->getList()", FATAL);
            }
            $belong_to_key = array_search($this->dbquery->getFilter('belong_to'), $this->getBelongToTypes());
            if ($this->dbquery->getFilter('belong_to') == '' || $belong_to_key === false) {
                trigger_error("belong_to_key er ikke gyldig i OnlinePayment->getList()", FATAL);
            }
            $this->dbquery->setCondition("belong_to_key = ".$belong_to_key." AND belong_to_id = ".$this->dbquery->getFilter('belong_to_id'));

            // $this->dbquery->setFilter('status', -1);

        }

        /*
        if ($this->dbquery->getFilter('status') == 0) {
            $this->dbquery->setFilter('status', 2);
        }
        */

        if ($this->dbquery->getFilter('status') > 0) {
            $this->dbquery->setCondition("status_key = ".intval($this->dbquery->getFilter('status')));
        }

        if ($this->dbquery->getFilter('text') != "") {
            $this->dbquery->setCondition("transaction_number LIKE \"%".$this->dbquery->getFilter('text')."%\" OR text LIKE \"%".$this->dbquery->getFilter('text')."%\"");
        }

        $this->dbquery->setSorting("date_created DESC");
        $db = $this->dbquery->getRecordset("id, date_created, belong_to_key, belong_to_id, text, status_key, amount, provider_key, transaction_number, transaction_status, DATE_FORMAT(date_created, '%d-%m-%Y %H:%i') AS dk_date_created", "", false);
        $i = 0;
        $list = array();

        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['dk_date_created'] = $db->f('dk_date_created');
            $list[$i]['date_created'] = $db->f('date_created');
            $list[$i]['belong_to_key'] = $db->f('belong_to_key');
            $belong_to_types = $this->getBelongToTypes();
            $list[$i]['belong_to'] = $belong_to_types[$db->f('belong_to_key')];
            $list[$i]['belong_to_id'] = $db->f('belong_to_id');
            $list[$i]['text'] = $db->f('text');
            $list[$i]['status_key'] = $db->f('status_key');
            $status_types = OnlinePayment::getStatusTypes();
            $list[$i]['status'] = $status_types[$db->f('status_key')];
            $list[$i]['amount'] = $db->f('amount');
            $list[$i]['provider_key'] = $db->f('provider_key');
            $list[$i]['dk_amount'] = number_format($db->f('amount'), 2, ",", ".");
            $list[$i]['transaction_number'] = $db->f('transaction_number');
            $list[$i]['transaction_status'] = $db->f('transaction_status');
            if (in_array($list[$i]['transaction_status'], $this->transaction_status_types)) {
                $list[$i]['transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $list[$i]['transaction_status_translated'] = 'invalid status';
            }

            // Don't really know want this is for? /Sune(19-05-2007)
            if (in_array($list[$i]['transaction_status'], $this->transaction_status_types) && $db->f('transaction_status') != $this->transaction_status_authorized) {
                $list[$i]['user_transaction_status_translated'] = $this->transaction_status_types[$db->f('transaction_status')];
            } else {
                $list[$i]['user_transaction_status_translated'] = "";
            }

            $i++;
        }
        return $list;

    }

    /**
     * returns the possible status types
     *
     * @return array with status types
     */
    static function getStatusTypes()
    {
        return array(
            0 => '',
            1 => 'created',
            2 => 'authorized',
            3 => 'captured',
            4 => 'reversed',
            5 => 'cancelled');
    }

    /**
     * returns possible belong to types
     *
     * @return array with belong to types
     */
    private function getBelongToTypes()
    {
        return array(
            0 => '',
            1 => 'order',
            2 => 'invoice');
    }

    /**
     * returns the implemented providers
     *
     * @return array with providers
     */
    static function getImplementedProviders()
    {
        return array(
            0 => '_invalid_',
            1 => 'default', // reserveret for a custom provider, where everythinh runs outside the system.
            2 => 'quickpay',
            3 => 'dandomain'
        );
    }

    /**
     * @todo remove this?
     */
    function isFilledIn()
    {
        return true; // Onlinepyment kan ikke udfyldes.
    }

    /**
     * @todo remove this?
     */
    function isSettingsSet()
    {
        return true;
    }

    function isProviderSet()
    {
        return $this->kernel->setting->get('intranet', 'onlinepayment.provider_key');
    }

    function setProvider($input)
    {
        // der skal nok laves et tjek p� om alle poster er f�rdigbehandlet inden man kan skifte
        // udbyder
        $this->kernel->setting->set('intranet', 'onlinepayment.provider_key', $input['provider_key']);
        return 1;
    }

    function getProvider()
    {
        return array('provider_key' => $this->kernel->setting->get('intranet', 'onlinepayment.provider_key'));
    }

    function getStatus()
    {
        $status =  $this->getStatusTypes();
        return $status[$this->value['status_key']];
    }

}