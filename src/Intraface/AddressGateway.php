<?php
class Intraface_AddressGateway
{
    protected $db;

    function __construct(DB_Sql $db)
    {
        $this->db = $db;
    }

    /**
     * Factory
     *
     * Returns an instace of Address from belong_to and belong_to_id
     *
     * @param string  $belong_to    What the address belongs to, corresponding to the ones in Address::getBelongToTypes()
     * @param integer $belong_to_id From belong_to. NB not id on the address
     *
     * @return object Address
     */
    function factory($belong_to, $belong_to_id)
    {
        $belong_to_types = Intraface_Address::getBelongToTypes();

        $belong_to_key = array_search($belong_to, $belong_to_types);
        if ($belong_to_key === false) {
            trigger_error("Invalid address type '".$belong_to."' in Address::factory", E_USER_ERROR);
        }

        settype($belong_to_id, 'integer');
        if ($belong_to_id == 0) {
            trigger_error("Invalid belong_to_id in Address::factory", E_USER_ERROR);
        }

        $this->db->query("SELECT id FROM address WHERE type = ".$belong_to_key." AND belong_to_id = ".$belong_to_id." AND active = 1");
        if ($this->db->numRows() > 1) {
            trigger_error('There is more than one active address for '.$belong_to.':'.$belong_to_id.' in Address::facotory', E_USER_ERROR);
        }
        if ($this->db->nextRecord()) {
            return new Intraface_Address($this->db->f('id'));
        } else {
            $address = new Intraface_Address(0);
            $address->setBelongTo($belong_to, $belong_to_id);
            return $address;
        }
    }
}