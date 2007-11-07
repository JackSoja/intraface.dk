<?php
/**
 * H�ndterer Settings i systemet
 *
 * Tabelfelter: id, intranet_id, user_id, setting, value, sub_id
 * Settingniveauer: System, Intranet, User
 *
 * @author Sune Jensen <sj@sunet.dk>
 */

//require_once 'Intraface/config/setting_kernel.php';
require_once 'DB/Sql.php';
require_once 'Intraface/functions/functions.php';

class Setting {

    var $db;
    var $system;
    var $user_id;
    var $intranet_id;
    protected $settings;

    function __construct($intranet_id, $user_id = 0) {
        global $_setting;

        require_once 'Intraface/config/setting_kernel.php';

        // Init
        $this->db = new DB_Sql;
        $this->system = &$_setting; // don't remove the & - otherwise it will not work

        $this->user_id = (int)$user_id;
        $this->intranet_id = (int)$intranet_id;
    }

    /**
     * @param string $setting to test
     */
    private function checkSystem($setting) {

        if(!empty($setting) && is_array($this->system) && isset($this->system[$setting])) {
            return true;
        }
        else {
            trigger_error('Setting "'.$setting.'" is not defined', E_USER_ERROR);
        }
    }

    /**
     * @param string $type to test
     */
    private function checkType($type) {
        if($type == 'system' || $type == 'intranet' || $type == 'user') {
            return true;
        }
        else {
            trigger_error('Ugyldig type setting "'.$type.'"', E_USER_ERROR);
        }
    }

    /**
     * @return boolean
     */
    function checkLogin() {
        if($this->user_id != 0) {
            return true;
        }
        else {
            trigger_error('Du kan ikke udf�re denne handling fra et weblogin', E_USER_ERROR);
        }
    }

    function set($type, $setting, $value, $sub_id = 0) {

        if($this->checkSystem($setting) && $this->checkType($type) && $this->checkLogin()) {

            switch($type) {
                case 'system':
                    trigger_error('Du kan ikke �ndre p� systemsetting', E_USER_ERROR);
                    break;
                case 'intranet':
                    $this->db->query("SELECT id FROM setting WHERE setting = ".$this->db->quote($setting, 'text')." AND intranet_id = ".$this->intranet_id." AND user_id = 0 AND sub_id = ".intval($sub_id));
                    if($this->db->nextRecord()) {
                        $this->db->query("UPDATE setting SET value = ".$this->db->quote($value, 'text')." WHERE id = ".$this->db->quote($this->db->f("id"), 'integer'));
                    }
                    else {
                        $this->db->query("INSERT INTO setting SET value = ".$this->db->quote($value, 'text').", setting = ".$this->db->quote($setting, 'text').", intranet_id = ".$this->db->quote($this->intranet_id, 'integer').", user_id = 0, sub_id = ".intval($sub_id));
                    }
                    $this->settings['intranet'][$setting][$sub_id] = $value;
                break;
                case 'user':
                    if($this->checkSystem($setting)) {
                        $this->db->query("SELECT id FROM setting WHERE setting = ".$this->db->quote($setting, 'text')." AND intranet_id = ".$this->db->quote($this->intranet_id, 'integer')." AND user_id = ".$this->db->quote($this->user_id, 'integer')." AND sub_id = ".intval($sub_id));
                        if($this->db->nextRecord()) {
                            $this->db->query("UPDATE setting SET value = ".$this->db->quote($value, 'text')." WHERE id = ".$this->db->quote($this->db->f("id"), 'integer'));
                        }
                        else {
                            $this->db->query("INSERT INTO setting SET value = ".$this->db->quote($value, 'text').", setting = ".$this->db->quote($setting, 'text').", intranet_id = ".$this->intranet_id.", user_id = ".$this->db->quote($this->user_id, 'integer').", sub_id = ".intval($sub_id));
                        }
                    }
                    $this->settings['user'][$setting][$sub_id] = $value;
                    break;
            }
            return 1;
        }
        return 0;
    }

    function getSettings() {
        $this->db->query("SELECT setting, value, sub_id, user_id FROM setting WHERE intranet_id = " . $this->db->quote($this->intranet_id, 'integer')." AND (user_id = ".$this->db->quote($this->user_id, 'integer')." OR user_id = 0)");
        while($this->db->nextRecord()) {
            if ($this->db->f('user_id') == 0) {
                $this->settings['intranet'][$this->db->f('setting')][$this->db->f('sub_id')] = $this->db->f('value');
            }
            else {
                $this->settings['user'][$this->db->f('setting')][$this->db->f('sub_id')] = $this->db->f('value');
            }
        }

    }

    function get($type, $setting, $sub_id = 0) {

        $this->getSettings();

        if($this->checkSystem($setting) && $this->checkType($type)) {
            switch($type) {
                case 'user':
                    if($this->checkLogin()) {
                        // hvis der ikke er nogen intranet-indstillinger p� posten vil den stadig
                        // blive ved med at lave opslaget. Hvordan undg�r vi lige det p� en god og sikker m�de?
                        /*
                        if (!isset($this->settings['user'])) {
                            $this->settings['user'] = array();
                            $this->db->query("SELECT setting, value, sub_id FROM setting WHERE intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id);
                            while($this->db->nextRecord()) {
                                $this->settings['user'][$this->db->f('setting')][$this->db->f('sub_id')] = $this->db->f('value');
                            }

                        }
                        */
                        if (!empty($this->settings['user'][$setting][intval($sub_id)])) {
                            return $this->settings['user'][$setting][intval($sub_id)];
                        }


                    }
                    // no break because it has to fall through if user is not set
                case 'intranet':
                    // hvis der ikke er nogen intranet-indstillinger p� posten vil den stadig
                    // blive ved med at lave opslaget. Hvordan undg�r vi lige det.
                    /*
                    if (!isset($this->settings['intranet'])) {
                        $this->settings['intranet'] = array();
                        $this->db->query("SELECT setting, value, sub_id FROM setting WHERE intranet_id = ".$this->intranet_id." AND user_id = 0");

                        while($this->db->nextRecord()) {
                            $this->settings['intranet'][$this->db->f('setting')][intval($this->db->f('sub_id'))] = $this->db->f('value');
                        }

                    }
                    */
                    if (!empty($this->settings['intranet'][$setting][intval($sub_id)])) {
                        return $this->settings['intranet'][$setting][intval($sub_id)];
                    }
                    // no break because it has to fall through if intranet is not set
                default:
                    return $this->system[$setting];
                    break;
            }
        }
    }
    function isSettingSet($type, $setting, $sub_id = 0) {
        if($this->checkSystem($setting) && $this->checkType($type)) {
            switch($type) {
                case 'user':
                    if($this->checkLogin()) {
                        $this->db->query("SELECT value FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id." AND sub_id = ".intval($sub_id));
                        return $this->db->nextRecord();
                    }
                    break;

                case 'intranet':
                    $this->db->query("SELECT value FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = 0 AND sub_id = ".intval($sub_id));
                    return $this->db->nextRecord();
                    break;

                default:
                    return true;
                    break;
            }
        }
    }

    function delete($type, $setting, $sub_id = 0) {

        if($this->checkSystem($setting) && $this->checkType($type) && $this->checkLogin()) {

            if($sub_id == 'ALL') {
              $sql_sub = '';
          }
          else {
              $sql_sub = "AND sub_id = ".intval($sub_id);
          }

            switch($type) {
                case 'user':
                    $this->db->query("DELETE FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id." ".$sql_sub);
                    return true;
                    break;

                case 'intranet':
                    $this->db->query("DELETE FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." ".$sql_sub);
                    return true;
                    break;

                default:
                    trigger_error('Du kan ikke slette en system setting', E_USER_ERROR);
                    return false;
            }
        }
    }
}

?>