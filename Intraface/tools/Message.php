<?php
/**
 * M�ske kunne klassen ogs� indeholde oplysninger om at oplyse (fade) en side,
 * s� vi f�r en standard for det?
 *
 * Skal startes op i kernel, n�r det er intranetlogin.
 */

class Core_Message {

	var $types = array('confirmation', 'message', 'warning', 'error');

	function Core_Message() {
	}

	function set($type, $message, $identifier) {
	}


}

?>