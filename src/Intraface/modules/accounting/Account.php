<?php
/**
 * Represents an account
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version     1.0
 */
require_once 'Intraface/functions.php';

class Account extends Intraface_Standard
{
    /**
     * Account id
     *
     * @var integer
     */
    protected $id;

    /**
     * Year object
     *
     * @var object
     */
    public $year;

    /**
     * Account values
     *
     * @var array
     */
    public $value;

    /**
     * Error object
     *
     * @var object
     */
    public $error;

    /**
     * Vat percent
     *
     * @var float
     */
    public $vat_percent;

    /**
     * Direction of vat
     *
     * @var array
     */
    public $vat = array(
        0 => 'none',
        1 => 'in',
        2 => 'out'
    );

    // Disse b�r laves om til engelske termer med sm�t og s� overs�ttes
    // husk at �ndre tilsvarende i validForState() - Status b�r
    // splittes op i to konti (aktiver og passiver)
    // husk at opdatere databasen til alle sum-konti skal have nummer 5 i stedet

    public $types = array(
        1 => 'headline',
        2 => 'operating', // drift
        3 => 'balance, asset', // aktiv
        4 => 'balance, liability', // passiv
        5 => 'sum'
    );

    public $use = array(
        1 => 'none',
        2 => 'income',
        3 => 'expenses',
        4 => 'finance'
    );

    protected $db;
    protected $mdb2;

    /**
     * Constructor
     *
     * @param object  $year
     * @param integer $account_id
     *
     * @return void
     */
    function __construct($year, $account_id = 0)
    {
        $this->db = new DB_Sql;
        $this->mdb2 = MDB2::singleton(DB_DSN);
        if (PEAR::isError($this->mdb2)) {
            throw new Exception($this->mdb2->getMessage() . $this->mdb2->getUserInfo());
        }

        $this->error = new Intraface_Error;
        $this->year = $year;
        $this->id = (int)$account_id;

        $this->vatpercent = $this->year->kernel->getSetting()->get('intranet', 'vatpercent');

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Finds an account from number
     *
     * @deprecated
     * @param integer $account_number
     *
     * @return object
     */
    public static function factory($year, $account_number)
    {
        $gateway = new Intraface_modules_accounting_AccountGateway($year);
        return $gateway->findFromNumber($account_number);
    }

    /**
     * Loads details about an account
     *
     * @return integer id
     */
    private function load()
    {
        if ($this->year->get('id') == 0 || $this->id == 0) {
            $this->value['id'] = 0;
            $this->id = 0;
            return 0;
        }

        $sql = "SELECT
                account.id,
                account.name,
                account.type_key,
                account.use_key,
                account.number,
                account.sum_from_account_number,
                account.sum_to_account_number,
                account.vat_key,
                account.vat_percent,
                account.primosaldo_debet,
                account.primosaldo_credit,
                account.active
            FROM
                accounting_account account
            WHERE account.id = " . $this->id . "
                AND account.intranet_id = ".$this->year->kernel->intranet->get('id'). "
                AND year_id = ".$this->year->get('id')."
            LIMIT 1";

        $this->db->query($sql);

        if ($this->db->nextRecord()) {
            $this->value['id'] = $this->db->f('id');
            $this->value['name'] = $this->db->f('name');
            //$this->value['comment'] = $this->db->f('comment');
            $this->value['number'] = $this->db->f('number');
            $this->value['type_key'] = $this->db->f('type_key');
            $this->value['type'] = $this->types[$this->value['type_key']];
            $this->value['sum_from'] = $this->db->f('sum_from_account_number');
            $this->value['sum_to'] = $this->db->f('sum_to_account_number');
            $this->value['use_key'] = $this->db->f('use_key');
            $this->value['use'] = $this->use[$this->value['use_key']];
            $this->value['primosaldo_debet'] = $this->db->f('primosaldo_debet');
            $this->value['primosaldo_credit'] = $this->db->f('primosaldo_credit');
            $this->value['vat_key'] = $this->db->f('vat_key');
            $this->value['active'] = $this->db->f('active');

            // hvis der ikke er moms p� �ret skal alle momsindstillinger nulstilles
            if ($this->year->get('vat') == 0) {
                $this->value['vat_key'] = 0;
                $this->value['vat'] = $this->vat[$this->db->f('vat_key')];
                $this->value['vat_percent'] = 0;
                $this->value['vat_shorthand'] = 'ingen';
            } else { // hvis der er moms p� �ret
                $this->value['vat_key'] = $this->db->f('vat_key');
                $this->value['vat'] = $this->vat[$this->db->f('vat_key')];
                if ($this->value['vat'] == 'none') {
                    $this->value['vat_percent'] = 0;
                } else {
                    $this->value['vat_percent'] = $this->db->f('vat_percent');
                }

                if ($this->value['vat'] == 'in') {
                    $this->value['vat_account_id'] = $this->year->getSetting('vat_in_account_id');
                } elseif ($this->value['vat'] == 'out') {
                    $this->value['vat_account_id'] = $this->year->getSetting('vat_out_account_id');
                } else {
                    $this->value['vat_account_id'] = 0;
                }
                $this->value['vat_shorthand'] = $this->value['vat'];
            }
        }

        return $this->get('id');
    }

    /**
     * Updateds account
     *
     * @param array $var info about account
     *
     * @return integer
     */
    public function save($var)
    {
        $var = safeToDb($var);

        if (empty($var['sum_to'])) {
            $var['sum_to'] = '';
        }
        if (empty($var['sum_from'])) {
            $var['sum_from'] = '';
        }
        if (empty($var['vat_percent'])) {
            $var['vat_percent'] = 0;
        }
        if (!$this->isNumberFree($var['number'])) {
            $this->error->set('Du kan ikke bruge det samme kontonummer flere gange');
        }

        $validator = new Intraface_Validator($this->error);
        $validator->isNumeric($var['number'], 'Kontonummeret er ikke et tal');

        $validator->isNumeric($var['type_key'], 'Kontotypen er ikke rigtig');

        if (!array_key_exists($var['type_key'], $this->types)) {
            $this->error->set('Ikke en tilladt type');
        }

         $validator->isNumeric($var['use_key'], 'Det kan en konto ikke bruges til');

        if (!array_key_exists($var['use_key'], $this->use)) {
            $this->error->set('Ikke en tilladt brug af kontoen');
        }

        $validator->isString($var['name'], 'Kontonavnet kan kune v�re en tekststreng.');
        $validator->isNumeric($var['vat_key'], 'Ugyldig moms', 'allow_empty');
        $validator->isNumeric($var['sum_to'], 'sum_to', 'allow_empty');
        $validator->isNumeric($var['sum_from'], 'sum_from', 'allow_empty');

        settype($var['comment'], 'integer');
        $validator->isString($var['comment'], 'Error in comment', '', 'allow_empty');


        if ($this->error->isError()) {
            return false;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE accounting_account ";
            $sql_end = " WHERE id = " . $this->id;
        } else {
            $sql_type = "INSERT INTO accounting_account ";
            $sql_end = ", date_created=NOW()";
        }

        $sql = $sql_type . "SET
            number = '".(int)$var['number']."',
            intranet_id = " . $this->year->kernel->intranet->get('id') . ",
            user_id = " . $this->year->kernel->user->get("id") . ",
            type_key='" . $var['type_key']."',
            year_id = " . $this->year->get('id').",
            use_key = '" . $var['use_key']."',
            name = '" . $var['name']."',
            comment = '" . $var['comment']."',
            vat_percent = '" . $var['vat_percent'] . "',
            sum_to_account_number = " . (int)$var['sum_to'] . ",
            sum_from_account_number = " . (int)$var['sum_from'] . ",
            date_changed = NOW(),
            vat_key=" . (int)$var['vat_key'] . " " . $sql_end;

        $this->db->query($sql);

        if ($this->id == 0) {
            $this->id = $this->db->insertedId();
        }

        if (!empty($var['created_from_id']) and is_numeric($var['created_from_id'])) {
            $this->db->query("UPDATE accounting_account SET created_from_id = ".$var['created_from_id']." WHERE id = " . $this->id);
        }

        $this->load();

        return $this->id;
    }

    /**
     * Saves the primo saldo
     *
     * @param float $debet
     * @param float $credit
     *
     * @return boolean
     */
    public function savePrimosaldo($debet, $credit)
    {
        if ($this->id == 0) {
            return false;
        }

        $amount = new Intraface_Amount($debet);
        if (!$amount->convert2db()) {
            $this->error->set('Amount could not be converted');
        }
        $debet = $amount->get();

        $amount = new Intraface_Amount($credit);
        if (!$amount->convert2db()) {
            $this->error->set('Amount could not be converted');
        }
        $credit = $amount->get();


        $debet = (double)$debet;
        $credit = (double)$credit;

        $this->db->query("UPDATE accounting_account
            SET
                primosaldo_debet = '".$debet."',
                primosaldo_credit = '".$credit."'
                WHERE id = " . $this->id);
        return true;
    }

    /**
     * Deletes an account
     *
     * @todo Skal tjekke om der er poster i �ret p� kontoen.
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->anyPosts()) {
            $this->error->set('Der er poster på kontoen for dette år, så du kan ikke slette den. Næste år kan du lade være med at bogføre på kontoen, og så kan du slette den.');
            return false;
        }
        $this->getSaldo();
        if ($this->get('saldo') != 0) {
            $this->error->set('Der er registreret noget på primosaldoen på kontoen, så du kan ikke slette den. Du kan slette kontoen, hvis du nulstiller primosaldoen.');
            return false;
        }

        $this->db->query("UPDATE accounting_account SET active = 0, date_changed=NOW() WHERE intranet_id = " . $this->year->kernel->intranet->get('id') . " AND year_id = ".$this->year->get('id')." AND id = " . $this->id);
        $this->value['active'] = 0;
        return true;
    }

    /*************************************************************************************
     * VALIDERINGSFUNKTIONER
     ************************************************************************************/

    /**
     * Metoden tjekker om kontoen har den rigtige type, s� vi m� bogf�re p� den.
     *
     * @return boolean
     */
    public function validForState()
    {
        if ($this->id > 0) {
            if ($this->get('type_key') == array_search('operating', $this->types) or $this->get('type_key') == array_search('balance, asset', $this->types) or $this->get('type_key') == array_search('balance, liability', $this->types)) {
                return true;
            }
        }
        return false;
    }

    public function getType()
    {
        return $this->types[$this->get('type_key')];
    }

    /**
     * Metode til at tjekke om kontonummeret er fri.
     *
     * @see save()
     */
    private function isNumberFree($account_number)
    {
        $account_number = (int)$account_number;

        $sql = "SELECT
                id
            FROM accounting_account
            WHERE number = " . $account_number . "
                AND intranet_id = " . $this->year->kernel->intranet->get('id') . "
                AND year_id = " .$this->year->get('id'). "
                AND id <> " . $this->id . " AND active = 1";
        $result = $this->mdb2->query($sql);
        if (PEAR::isError($result)) {
            throw new Exception('Error in query: '.$result->getUserInfo());
        }

        if ($result->numRows() == 0) {
            return true;
        }
        return false;
    }

    /*************************************************************************************
     * SALDOFUNKTIONER
     ************************************************************************************/

    /**
     * Public: Metoden returnerer primosaldoen for en konto
     *
     * @return (array) med debet, credit og total saldo
     */
    function getPrimoSaldo()
    {
        $sql = "SELECT primosaldo_debet, primosaldo_credit
            FROM accounting_account
            WHERE year_id = " . $this->year->get('id') . "
                AND id = ".$this->id . "
                AND active = 1
                AND intranet_id = ".$this->year->kernel->intranet->get('id');

        $this->db->query($sql);

        if (!$this->db->nextRecord()) {
            return array('debet' => 0, 'credit' => 0, 'saldo' => 0);
        }

        $primo['debet'] = $this->db->f('primosaldo_debet');
        $primo['credit'] = $this->db->f('primosaldo_credit');
        $primo['saldo'] = $primo['debet'] - $primo['credit'];

        return $primo;
    }
    /**
     * Public: Metoden returnerer en saldo for en konto
     *
     * Klassen tager h�jde for primobalancen og den skal ogs� tage h�jde for
     * sumkonti, se i f�rste omgang getSaldoList().
     *
     * Det vil v�re for voldsomt at putte
     * den her under get, for s� skal saldoen
     * udregnes hver gang jeg skal have fat i
     * et eller andet ved en konto!
     *
     * @param $date_from (date) yyyy-mm-dd Der s�ges jo kun i indev�rende �r
     * @param $date_to (date) yyyy-mm-dd   Der s�ges kun i indev�rende �r
     *
     * @return (array) med debet, credit og total saldo
     *
     *
     *
     */
    function getSaldo($type = 'stated', $date_from = '', $date_to = '')
    {
        if (empty($date_from)) {
            $date_from = $this->year->get('from_date');
        }
        if (empty($date_to)) {
            $date_to = $this->year->get('to_date');
        }

        $total_saldo = 0;

        $primo = array(
            'debet' => '',
            'credit' => '',
            'saldo' => ''
        );

        // Tjekker p� om datoerne er i indev�rende �r

            // henter primosaldoen for kontoen
            $primo = $this->getPrimoSaldo();
            $sql = "SELECT
                    SUM(post.debet) AS debet_total,
                    SUM(post.credit) AS credit_total
                FROM accounting_post post
                INNER JOIN accounting_account account
                    ON account.id = post.account_id
                WHERE account.id = ".$this->id."
                    AND post.year_id = ".$this->year->get('id')."
                    AND post.intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') >= '".$date_from."'
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') <= '".$date_to."'
                    AND account.year_id = ".$this->year->get('id');
        if ($type == 'stated') {
            $sql .= ' AND post.stated = 1';
        } elseif ($type == 'draft') {
            $sql .= ' AND post.stated = 0';
        }

            $sql .= " GROUP BY post.account_id";

        if ($this->get('type_key') == array_search('sum', $this->types)) {
            $db2 = new DB_Sql;
            $sql = "SELECT id FROM accounting_account
                    WHERE number >= " . $this->get('sum_from') . "
                        AND type_key != ".array_search('sum', $this->types)."
                        AND number <= " . $this->get('sum_to') . "
                        AND year_id = ".$this->year->get('id')."
                        AND intranet_id = " . $this->year->kernel->intranet->get('id');
            $db2->query($sql);
            $total = 0;
            while ($db2->nextRecord()) {
                // $sub = 0;
                $sAccount = new Account($this->year, $db2->f('id'));
                $sAccount->getSaldo();
                $total = $total + $sAccount->get('saldo');
            }
            $this->value['saldo'] = $total;
            $total_saldo = $total_saldo + $total;
        } else {
            $this->db->query($sql);
            if (!$this->db->nextRecord()) {
                $this->value['debet'] = $primo['debet'];
                $this->value['credit'] = $primo['credit'];
                $this->value['saldo'] = $this->value['debet'] - $this->value['credit'];
            } else {
                if ($type == 'draft') {
                    $this->value['debet_draft'] = $this->db->f('debet_total');
                    $this->value['credit_draft'] = $this->db->f('credit_total');
                    $this->value['saldo_draft'] = $this->value['debet_draft'] - $this->value['credit_draft'];
                } else {
                    $this->value['debet'] = $primo['debet'] + $this->db->f('debet_total');
                    $this->value['credit'] = $primo['credit'] + $this->db->f('credit_total');
                    $this->value['saldo'] = $this->value['debet'] - $this->value['credit'];
                }
            }
            // Det her kan sikkert laves lidt smartere. Den skal egentlig laves inden
            // alt det ovenover tror jeg - alst� if-s�tningen
        }

            return true;
    }

    /***************************************************************************
     * �VRIGE METODER
     **************************************************************************/

    /**
     * Returnerer liste med alle kontoerne
     *
     * @param string  $type  Typen af konto, kig i Account::type;
     * @param boolean $saldo Whether to return the saldo
     *
     * @return array
     */
    public function getList($type = '', $saldo = false)
    {
        $gateway = new Intraface_modules_accounting_AccountGateway($this->year);
        return $gateway->getList($type, $saldo);
    }

    public function anyAccounts()
    {
        $gateway = new Intraface_modules_accounting_AccountGateway($this->year);
        return $gateway->anyAccounts();
    }

    public function anyPosts()
    {
        $this->db->query("SELECT
                id
            FROM accounting_post post
            WHERE (post.account_id = ". $this->id . ")
                AND intranet_id = ".$this->year->kernel->intranet->get('id')."
                AND year_id = " . $this->year->get('id') . "
                LIMIT 1");
        return $this->db->numRows();
    }

    public function getPosts()
    {
        $posts = array();

        if ($this->id == 0) {
            return $posts;
        }
        $this->db->query("SELECT
                    id,
                    date,
                    DATE_FORMAT(date, '%d-%m-%Y') AS dk_date,
                    voucher_id,
                    text,
                    debet,
                    credit
                FROM accounting_post post
                WHERE (post.account_id = ". $this->get('id') . ")
                    AND intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND year_id = " . $this->year->get('id') . "
                    AND stated = 1
                    ORDER BY date ASC, id ASC");
        $i = 1;
        while ($this->db->nextRecord()) {
            $posts[$i]['id'] = $this->db->f('id');
            $posts[$i]['dk_date'] = $this->db->f('dk_date');
            $posts[$i]['date'] = $this->db->f('date');
            $posts[$i]['voucher_id'] = $this->db->f('voucher_id');
            $voucher = new Voucher($this->year, $this->db->f('voucher_id'));
            $posts[$i]['voucher_number'] = $voucher->get('number');
            $posts[$i]['text'] = $this->db->f('text');
            $posts[$i]['debet'] = $this->db->f('debet');
            $posts[$i]['credit'] = $this->db->f('credit');
            //$posts[$i]['stated'] = $db2->f('stated');
            //$posts[$i]['account_id'] = $db2->f('account_id');
            $i++;
        } // while
        return $posts;
    }

    /**
     * Calculates the vat amount
     *
     * @link http://eforum.idg.se/viewmsg.asp?EntriesId=831525
     *
     * @param float $amount      Amount
     * @param float $vat_percent Vat percent
     *
     * @return float Vat amount
     */
    public function calculateVat($amount, $vat_percent)
    {
        $amount = (float)$amount;
        $vat_percent = (float)$vat_percent / 100;

        return $amount * ($vat_percent / (1 + $vat_percent));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNumber()
    {
        return $this->get('number');
    }
}
