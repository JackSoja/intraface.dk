<?php
/**
 * Maintain users and user rights
 * Please read in User.php for description of relations
 *
 * @package Intraface_IntranetMaintenance
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 *
 */
class UserMaintenance extends Intraface_User
{
    private $dbquery;
    
    /**
     * constructor
     *
     * @param integer $user_id id of the user to be maintained
     *
     * @return void
     */
    public function __construct($user_id = 0)
    {
        parent::__construct($user_id);
    }

    /**
     * create DBQuery object
     *
     * @param object kernel
     *
     * @return void
     */
    public function getDBQuery($kernel = null)
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        if ($kernel == null) {
            throw new Exception('getDBQuery requires kernel first time called');
        }
        $this->dbquery = new Intraface_DBQuery($kernel, 'user');
        $this->dbquery->setJoin('LEFT', 'address', 'user.id = address.belong_to_id AND address.type = 2', 'address.active = 1 OR address.active IS NULL');
        return $this->dbquery;
    }

    /**
     * This function makes it possible to create a new User as User.php do not allow that.
     *
     * @param array paramname description
     *
     * @return boolean true or false
     */
    public function update($input)
    {
        $this->validate($input);
        $validator = new Intraface_Validator($this->error);

        if ($this->id == 0) {
            $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal");
        } else {
            $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal", "allow_empty");
        }

        $sql = "email = \"".$input["email"]."\",
            disabled = ".$input["disabled"]."";

        if (!empty($input["password"])) {
            if ($input["password"] === $input["confirm_password"]) {
                $sql .= ", password = \"".md5($input["password"])."\"";
            } else {
                $this->error->set("De to adgangskoder er ikke ens!");
            }
        }

        if ($this->error->isError()) {
            return false;
        }

        if ($this->id) {
            $this->db->exec("UPDATE user SET ".$sql." WHERE id = ".$this->id);
            $this->load();
            return $this->id;
        } else {
            $this->db->exec("INSERT INTO user SET ".$sql);
            $this->id = $this->db->lastInsertId();
            $this->load();
            return $this->id;
        }
    }

    /**
     * Fjerner alle access til denne bruger for det satte intranet
     *
     */
    function flushAccess()
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM permission WHERE user_id = ".$this->id." AND intranet_id = ".$this->intranet_id);
    }

    /**
     * Sets access to an intranet
     *
     * @param integer intranet_id
     * @return boolean true on success, false on error.
     *
     */
    function setIntranetAccess($intranet_id = 0)
    {
        $db = new DB_Sql;
        settype($intranet_id, "integer");
        if ($intranet_id == 0) {
            if ($this->intranet_id == 0) {
                throw new Exception("Der er ikke angivet et intranet id");
            } else {
                $intranet_id = $this->intranet_id;
            }
        }

        $db->query("SELECT id FROM intranet WHERE id = ".$intranet_id);
        if ($db->nextRecord()) {
            $db->query("SELECT id FROM permission WHERE intranet_id = ".$intranet_id." AND user_id = ".$this->id." AND module_id = 0 AND module_sub_access_id = 0");
            if ($db->nextRecord()) {
                return $db->f("id");
            } else {
                $db->query("INSERT INTO permission SET intranet_id = ".$intranet_id.", user_id = ".$this->id);
                return $db->insertedId();
            }
        } else {
            throw new Exception("Ugyldig intranet id");
        }

        return true;
    }

    /**
     * Sets access to a module
     *
     * @param mixed module_id either name or id
     *
     * @return boolean true on success
     */
    function setModuleAccess($module_id, $intranet_id = 0)
    {
        $db = new DB_Sql;
        settype($intranet_id, "integer");

        if ($intranet_id == 0) {
            if ($this->intranet_id == 0) {
                throw new Exception("Der er ikke angivet et intranet id");
            } else {
                $intranet_id = $this->intranet_id;
            }
        }

        $module_name = $module_id;

        if (!is_numeric($module_id)) {
            $db->query("SELECT id FROM module WHERE name =  '".$module_id."'");
            if (!$db->nextRecord()) {
                throw new Exception("Ugyldig module_id");
            }
            $module_id = $db->f('id');
        }

        $module_id = intval($module_id);

        $db->query("SELECT id FROM module WHERE id = ".$module_id);
        if ($db->nextRecord()) {
            $db->query("SELECT id FROM permission WHERE intranet_id = ".$intranet_id." AND user_id = ".$this->id." AND module_id = ".$module_id." AND module_sub_access_id = 0");
            if ($db->nextRecord()) {
                return $db->f("id");
            } else {
                $id = $this->setIntranetAccess($intranet_id);
                $db->query("UPDATE permission SET module_id = ".$module_id." WHERE id = ".$id);
                return $id;
            }
        } else {
            throw new Exception("Ugyldig module_id '".$module_id."/".$module_name."'");
        }

        return true;
    }

    /**
     * Sets sub access in module
     *
     * @param mixed module_id either id or name of module
     * @param mixed sub_access_id either id or name of sub_access
     * @param integer intranet_id id of intranet to give access
     *
     * @return void
     */
    function setSubAccess($module_id, $sub_access_id, $intranet_id = 0)
    {
        $db = new DB_Sql;

        if (!is_numeric($module_id)) {
            $db->query("SELECT id FROM module WHERE name =  '".$module_id."'");
            if (!$db->nextRecord()) {
                throw new Exception("Ugyldig module_id");
            }
            $module_id = $db->f('id');
        }

        if (!is_numeric($sub_access_id)) {
            $db->query("SELECT id FROM module_sub_access WHERE name =  '".$sub_access_id."'");
            if (!$db->nextRecord()) {
                throw new Exception("Ugyldig module_id");
            }
            $sub_access_id = $db->f('id');
        }

        settype($intranet_id, "integer");
        settype($module_id, "integer");
        settype($sub_access_id, "integer");

        if ($intranet_id == 0) {
            if ($this->intranet_id == 0) {
                throw new Exception("Der er ikke angivet et intranet id");
            } else {
                $intranet_id = $this->intranet_id;
            }
        }

        $db->query("SELECT id FROM module_sub_access WHERE module_id = ".$module_id." AND id = ".$sub_access_id);
        if ($db->nextRecord()) {
            $id = $this->setModuleAccess($module_id, $intranet_id);
            $db->query("UPDATE permission SET module_sub_access_id = ".$sub_access_id." WHERE id = ".$id);
            return $id;
        } else {
            throw new Exception("Ugyldig sub_access_id i useradmin->setSubAccess()");
        }
    }

    /**
     * returns list of users
     *
     * @return array list of users
     */
    function getList($kernel = null)
    {
        if ($this->intranet_id != 0) {
            return Intraface_User::getList();
        }

        
        $this->dbquery = $this->getDBQuery($kernel);

        if ($this->dbquery->checkFilter('text')) {
            $this->dbquery->setCondition('address.name LIKE "%'.safeToDB($this->dbquery->getFilter('text')).'%" OR user.email LIKE "%'.safeToDB($this->dbquery->getFilter('text')).'%"');
        }

        $this->dbquery->setSorting('address.name');

        $db = $this->dbquery->getRecordset('address.name, user.id, user.email', '', false);
        $i = 0;
        $user = array();
        while ($db->nextRecord()) {
            $user[$i]["id"] = $db->f("id");
            $user[$i]["email"] = $db->f("email");
            $user[$i]["name"] = $db->f("name");
            $i++;
        }

        return $user;
    }
}
