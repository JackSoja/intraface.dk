<?php
class Intraface_Tools_Controller_Log extends k_Controller
{
    function GET()
    {
        $db = $this->registry->get('database');
        $res = &$db->query("SELECT logtime, ident, message FROM log_table ORDER BY logtime DESC");
        return $this->render('Intraface/Tools/templates/log-tpl.php', array('res' => $res));
    }

}