<?php
/**
 * FileViewer
 *
 * @todo - how to get the filehandler coming into the class
 * so I can fake it - and how to put in the authentication when
 * it is only needed sometimes?
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Intraface/Weblogin.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';

class FileViewer {

	private $filehandler;
	public $public_key;
	public $file_key;
	public $file_type;

	function __construct() {
	}

	function parseQueryString($querystring) {
		$query_parts = explode('/', $querystring);
		$this->public_key = addslashes($query_parts[1]);
		$this->file_key = addslashes($query_parts[2]);
		$this->file_type = addslashes($query_parts[3]);
	}

	function fetch($querystring) {
		$this->parseQueryString($querystring);

		$weblogin = new Weblogin();
		if (!$intranet_id = $weblogin->auth('public', $this->public_key)) {
			die('FEJL I L�SNING AF BILLEDE (0)');
		}
		if($intranet_id == false) {
			trigger_error("FEJL I L�SNING AF BILLEDE (1)", E_USER_ERROR);
		}

		$kernel = new Kernel;
		$kernel->intranet = new Intranet($intranet_id);
		$filehandler_shared = $kernel->useShared('filehandler');

		$filehandler = FileHandler::factory($kernel, $this->file_key);
		if(!is_object($filehandler)) {
			trigger_error("FEJL I L�SNING AF BILLEDE (2)", E_USER_ERROR);
		}

		switch($filehandler->get('accessibility')) {
			case 'personal':
				// Not implemented - continue to next
			case 'intranet':
				// You have to be logged in to access this file
				session_start();
				$auth = new Auth(session_id());

				if (!$user_id = $auth->isLoggedIn()) {
					die("FEJL I L�SNING AF BILLEDE (4)");
				}

				$user = new User($user_id);
				$intranet = new Intranet($user->getActiveIntranetId());

				if($intranet->get('id') != $intranet_id) {
					die("FEJL I L�SNING AF BILLEDE (4)");
				}

				break;
			case 'public':
				// public alle m� se den
				break;
			default:
				// Dette er en ugyldig type
				trigger_error("FEJL I L�SNING AF BILLEDE (5)", E_USER_ERROR);
			break;
		}

		$file_id = $filehandler->get('id');
		$file_name = $filehandler->get('file_name');
		$mime_type = $_file_type[$filehandler->get('file_type_key')]['mime_type'];
		$file_path = $filehandler->get('file_path');

		$filehandler_shared->includeFile('InstanceHandler.php');
		$instancehandler = new InstanceHandler($filehandler);

		if($instancehandler->_checkType($this->file_type) !== false) {
			$filehandler->loadInstance($this->file_type);
			$file_path = $filehandler->instance->get('file_path');
		}

		$last_modified = filemtime($file_path);

		header('Content-Type: '.$mime_type);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified).' GMT');
		header('Cache-Control:');
		header('Content-Disposition: inline; filename='.$file_name);
		header('Pragma:');

		readfile($file_path);
		exit;
	}
}
?>