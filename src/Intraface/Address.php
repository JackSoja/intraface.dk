<?php
/**
 * Styrer adresser til intranet, bruger, kunde og kontaktperson
 *
 * Klassen kan styrer flere forskellige typer af adresser. B�de for intranettet, brugere, kunder og kontaktpersoner.
 * Beskrivelsen af hvilke og med hvilket navn er beskrevet l�ngere nede.
 *
 * @todo Skal vi programmere intranet_id ind i klassen? Det kr�ver at den f�r Kernel.
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 */
require_once 'Intraface/functions.php';

class Intraface_Address extends Intraface_Standard
{
    /**
     * @var integer
     */
    protected $belong_to_key;

    /**
     * @var integer
     */
    protected $belong_to_id;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    public $value = array();

    /**
     * @var array
     */
    public $fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

    /**
     * @var object error
     */
    public $error;

    protected $db;

    /**
     * Init: loader klassen
     *
     * Her er angivet de typer af adresser den kan h�ndtere med arrayet address_type[].
     * $this-fields er felter i tabellen (db) som overf�res til array og omvendt. M�ske disse
     * engang skal differencieres, s� man angvier hvad feltet i tabellen skal svare til navnet i arrayet.
     * Klassen loader ogs� adressens felter
     *
     * @param integer $id Id on address.
     *
     * @return void
     */
    function __construct($id)
    {
        $this->id = $id;
        $this->error = new Intraface_Error;
        $this->db = MDB2::singleton(DB_DSN);

        $this->load();

        $this->belong_to_types = $this->getBelongToTypes();


        if (PEAR::isError($this->db)) {
            throw new Exception("Error db singleton: ".$this->db->getUserInfo());
        }
        $this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);
    }

    /**
     * Returns an instance of Address from belong_to and belong_to_id
     *
     * @deprecated
     *
     * @param string  $belong_to    What the address belongs to, corresponding to the ones in Address::getBelongToTypes()
     * @param integer $belong_to_id From belong_to. NB not id on the address
     *
     * @return object Address
     */
    function factory($belong_to, $belong_to_id)
    {
        $gateway = new Intraface_AddressGateway(new DB_Sql);
        return $gateway->findByBelongToAndId($belong_to, $belong_to_id);
    }

    /**
     * Returns possible belong to types
     *
     * @return array
     */
    public static function getBelongToTypes()
    {
        return array(1 => 'intranet',
                     2 => 'user',
                     3 => 'contact',
                     4 => 'contact_delivery',
                     5 => 'contact_invoice',
                     6 => 'contactperson');
    }

    /**
     * Sets belong to @todo used for what?
     *
     * @param string  $belong_to    Which type the address belongs to
     * @param integer $belong_to_id Which id for the type the address belongs to
     *
     * @return void
     */
    function setBelongTo($belong_to, $belong_to_id)
    {
        if ($this->id != 0) {
            // is id already set, then you can not change belong_to
            return;
        }

        $belong_to_types = $this->getBelongToTypes();
        $this->belong_to_key = array_search($belong_to, $belong_to_types);
        if ($this->belong_to_key === false) {
            throw new Exception("Invalid address type ".$belong_to." in Address::setBelongTo()");
        }

        $this->belong_to_id = (int)$belong_to_id;
        if ($this->belong_to_id == 0) {
            throw new Exception("Invalid belong_to_id in Address::setBelongTo()");
        }
    }

    /**
     * Loads data to array
     *
     * @return integer
     */
    protected function load()
    {
        if ($this->id == 0) {
            return 0;
        }

        $result = $this->db->query("SELECT id, type, belong_to_id, ".implode(', ', $this->fields)." FROM address WHERE id = ".(int)$this->id);

        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($result->numRows() > 1) {
            throw new Exception('There is more than one active address');
        }

        if ($result->numRows() == 0) {
            $this->id = 0;
            $this->value['id'] = 0;

            return 0;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

        $this->value = $row;
        $this->value['id'] = $row['id'];
        $this->value['address_id'] = $row['id'];
        $this->belong_to_key = $row['type'];
        $this->belong_to_id = $row['belong_to_id'];

        return $this->id;
    }

    /**
     * Validates
     *
     * @param array $array_var Values
     *
     * @return boolean
     */
    function validate($array_var)
    {
        $validator = new Intraface_Validator($this->error);
        if (empty($array_var)) {
            $this->error->set('array cannot be empty');
        }

        // public $fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

        settype($array_var['name'], 'string');
        $validator->isString($array_var['name'], 'there was an error in name', '');
        settype($array_var['address'], 'string');
        $validator->isString($array_var['address'], 'there was an error in address', '');
        settype($array_var['postcode'], 'string');
        $validator->isNumeric($array_var['postcode'], 'there was an error in postcode', 'greater_than_zero');
        settype($array_var['city'], 'string');
        $validator->isString($array_var['city'], 'there was an error in city', '');
        settype($array_var['country'], 'string');
        $validator->isString($array_var['country'], 'there was an error in country', '', 'allow_empty');
        settype($array_var['cvr'], 'string');
        $validator->isString($array_var['cvr'], 'there was an error in cvr', '', 'allow_empty');
        // E-mail is not allowed to be empty do you need that. You should probably consider some places there this is needed before you set it (eg. intranet and user address) maybe make a param more to the function determine that: 'email:allow_empty'
        settype($array_var['email'], 'string');
        $validator->isEmail($array_var['email'], 'not a valid e-mail');
        settype($array_var['website'], 'string');
        $validator->isUrl($array_var['website'], 'website is not valid', '', 'allow_empty');
        settype($array_var['phone'], 'string');
        $validator->isString($array_var['phone'], 'not a valid phone number', '', 'allow_empty');
        settype($array_var['ean'], 'string');
        $validator->isString($array_var['ean'], 'ean location number is not valid', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Public: Denne funktion gemmer data. At gemme data vil sige, at den gamle adresse gemmes, men den nye aktiveres.
     *
     * @param array $array_var et array med felter med adressen. Se felterne i init funktionen: $this->fields
     *
     * @return bolean   true or false
     */
    function save($array_var)
    {
        // @todo validate should probably be called. Selenium debtor:testChangeContactPersonAndSender fails.
        if ($this->belong_to_key == 0 || $this->belong_to_id == 0) {
            throw new Exception("belong_to or belong_to_id was not set. Maybe because the provided address id was not valid. In Address::save");
        }

        $sql = '';

        if (count($array_var) > 0) {
            if ($this->id != 0) {
                $do_update = 0;
                foreach ($this->fields as $i => $field) {
                    if (array_key_exists($field, $array_var) and isset($array_var[$field])) {
                        $sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
                        if ($this->get($field) != $array_var[$field]) {
                            $do_update = 1;
                        }
                    }
                }
            } else {
                // Kun hvis der rent faktisk gemmes nogle v�rdier opdaterer vi. hvis count($arra_var) > 0 s� m� der ogs� v�re noget at opdatere?
                $do_update = 0;
                foreach ($this->fields as $i => $field) {
                    if (array_key_exists($field, $array_var) and isset($array_var[$field])) {
                        $sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
                        $do_update = 1;
                    }
                }
            }

            if ($do_update == 0) {
                // There is nothing to save, but that is OK, so we just return 1
                return true;
            } else {
                $result = $this->db->exec("UPDATE address SET active = 0 WHERE type = ".$this->belong_to_key." AND belong_to_id = ".$this->belong_to_id);
                if (PEAR::isError($result)) {
                    throw new Exception("Error in exec: ".$result->getUserInfo());
                }

                $result = $this->db->exec("INSERT INTO address SET ".$sql." type = ".$this->belong_to_key.", belong_to_id = ".$this->belong_to_id.", active = 1, changed_date = NOW()");
                if (PEAR::isError($result)) {
                    throw new Exception("Error in exec: ".$result->getUserInfo());
                }
                $this->id = $this->db->lastInsertId('address', 'id');
                $this->load();
                return true;
            }
        } else {
            // Der var slet ikke noget indhold i arrayet, s� vi lader v�re at opdatere, men siger, at vi gjorde.
            return true;
        }
    }
}
