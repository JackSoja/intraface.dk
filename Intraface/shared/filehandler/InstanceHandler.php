<?php
/**
 * Instance handler. Klarer h�ndtering af billeder forskellige instancer af billeder.
 *
 * @todo		Der mangler noget der hurtigt kan returnere billedst�rrelsen.
 *				Det skal fx bruges i cms, hvor man vil knytte width og height til
 *				billedet, men hvor man ogs� gerne vil have mulighed for at bestemme
 *				hvor bred billedteksten skal v�re!
 *
 * @author		Sune
 * @version	1.0
 *
 */

class InstanceHandler extends Standard {

	var $file_handler;
	var $instance_path;
	var $instance_types;
	var $allowed_transform_image = array('jpg', 'jpeg', 'gif', 'png');
	var $id;

	function InstanceHandler(&$file_handler, $id = 0) {

		if(!is_object($file_handler)) {
			trigger_error("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (1)", E_USER_ERROR);
		}

		if(strtolower(get_class($file_handler)) == 'filehandler' || strtolower(get_class($file_handler)) == 'filemanager') {
			// HJ�LP MIG, jeg kan ikke vende denne if-s�tning rigtigt.
			// Men her er det ok.
		}
		else {
			trigger_error("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (2)", E_USER_ERROR);
		}

		$this->file_handler = &$file_handler;
		$this->id = (int)$id;
		$this->instance_path = PATH_UPLOAD.$this->file_handler->kernel->intranet->get('id').'/'.'instance/';

		$this->instance_types = $this->_loadTypes();

		if($this->file_handler->get('is_image') == 0) {
			// trigger_error("InstanceHandler kan kun startes, hvis filen er et billede i IntanceHandler->InstanceHandler", E_USER_ERROR);
			$this->id = 0;
		}

		if($this->id > 0) {
			$this->load();
		}
	}


	function factory(&$file_handler, $type, $param = array()) {
		if(!is_object($file_handler)) {
			trigger_error("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->factory (1)", E_USER_ERROR);
		}

		if(strtolower(get_class($file_handler)) != 'filehandler' AND strtolower(get_class($file_handler)) != 'filemanager') {
		/*
			// TODO HJ�LP MIG, jeg kan ikke vende denne if-s�tning rigtigt.
			// Men her er det ok.
		}
		else {
		*/
			trigger_error("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->factory (2)", E_USER_ERROR);
		}
		/*
		print_r($file_handler->get());
		exit;
		*/
		if((int)$file_handler->get('id') == 0) {
			trigger_error("Der kan kun laves instance ud en loaded fil i Instance->factory", E_USER_ERROR);
		}

		if($file_handler->get('is_image') == 0) {
			trigger_error("Filen skal v�re et billede i IntanceHandler->factory", E_USER_ERROR);
		}

		// Vi skal lige benytte lidt funktion fra klassen.
		$instancehandler = new InstanceHandler($file_handler);

		$types = $instancehandler->_loadTypes();
		$type_key = $instancehandler->_checkType($type);

		if($type_key === false) {
			trigger_error("Ugyldig type '".$type."' i InstanceHandler->factory", E_USER_ERROR);
		}


		$db = new DB_sql;
		$db->query("SELECT id FROM file_handler_instance WHERE intranet_id = ".$file_handler->kernel->intranet->get('id')." AND active = 1 AND file_handler_id = ".$file_handler->get('id')." AND type = ".$type_key);
		if($db->nextRecord()) {
			return new InstanceHandler($file_handler, $db->f('id'));
		}
		else {

			if($type_key == 1) { // square
				$resize_type = 'strict';
			}
			else {
				$resize_type = 'relative';
			}

			$file_handler->loadImage();
			$file = $file_handler->image->resize($types[$type_key]['max_width'], $types[$type_key]['max_height'], $resize_type);

			if(!is_file($file)) {
				trigger_error("Filen blev ikke opretett i InstanceHandler->factory", E_USER_ERROR);
			}

			$file_size = filesize($file);
			$imagesize = getimagesize($file);
			$width = $imagesize[0]; // imagesx($file);
			$height = $imagesize[1]; // imagesy($file);

			$type_key = $instancehandler->_checkType($type, 'mime_type');
			if($type_key === false) {
				trigger_error("Ugyldig type i Instancehandler->factory", E_USER_ERROR);
			}

			$db->query("INSERT INTO file_handler_instance SET
				intranet_id = ".$file_handler->kernel->intranet->get('id').",
				file_handler_id = ".$file_handler->get('id').",
				date_created = NOW(),
				date_changed = NOW(),
				type = ".$type_key.",
				file_size = ".(int)$file_size.",
				width = ".(int)$width.",
				height = ".(int)$height);

			$id = $db->insertedId();

			$mime_type = $file_handler->get('file_type');
			$server_file_name = $id.'.'.$mime_type['extension'];

			if(!is_dir($instancehandler->instance_path)) {
				if(!mkdir($instancehandler->instance_path)) {
					$this->delete();
					trigger_error("Kunne ikke oprette mappe i InstanceHandler->factory", E_USER_ERROR);
				}
			}

			if(!rename($file, $instancehandler->instance_path.$server_file_name)) {
				trigger_error("Det var ikke muligt at flytte fil i InstanceHandler->factory", E_USER_ERROR);
			}

			$db->query("UPDATE file_handler_instance SET server_file_name = \"".$server_file_name."\" WHERE intranet_id = ".$file_handler->kernel->intranet->get('id')." AND id = ".$id);

			return New InstanceHandler($file_handler, $id);
		}
	}


	/**
	 * Henter en instance af et billede.
	 *
	 */

	function load() {

		$db = new DB_sql;
		$db->query("SELECT * FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND active = 1 AND id = ".$this->id);
		if(!$db->nextRecord()) {
			$this->id = 0;
			$this->value['id'] = 0;
			return false;
		}

		$this->id = $db->f('id');
		$this->value['id'] = $db->f('id');
		$this->value['date_created'] = $db->f('date_created');
		$this->value['date_changed'] = $db->f('date_changed');
		$this->value['type'] = $this->instance_types[$db->f('type')]['name'];
		$this->value['instance_properties'] = $this->instance_types[$db->f('type')];

		//$this->value['predefined_size'] = $db->f('predefined_size');
		$this->value['server_file_name'] = $db->f('server_file_name');
		$this->value['file_size'] = $db->f('file_size');
		$this->value['file_path'] = $this->instance_path . $db->f('server_file_name');

		$this->value['last_modified'] = filemtime($this->get('file_path'));
		// $this->value['file_uri'] = FILE_VIEWER.'?id='.$this->get('id').'&type='.$this->get('type').'&name=/'.urlencode($this->file_handler->get('file_name'));
		$this->value['file_uri'] = FILE_VIEWER.'?/'.$this->file_handler->kernel->intranet->get('public_key').'/'.$this->file_handler->get('access_key').'/'.$this->get('type').'/'.urlencode($this->file_handler->get('file_name'));

		// dette er vel kun i en overgangsperiode? LO
		if($db->f('width') == 0) {
			$imagesize = getimagesize($this->get('file_path'));
			$this->value['width'] = $imagesize[0]; // imagesx($this->get('file_uri'));
			$db2 = new DB_sql;
			$db2->query("UPDATE file_handler_instance SET width = ".$this->value['width']." WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
		}
		else {
			$this->value['width'] = $db->f('width');
		}

		if($db->f('height') == 0) {
			$imagesize = getimagesize($this->get('file_path'));
			$this->value['height'] = $imagesize[1]; //imagesy($this->get('file_uri'));
			$db2 = new DB_sql;
			$db2->query("UPDATE file_handler_instance SET height = ".$this->value['height']." WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
		}
		else {
			$this->value['height'] = $db->f('height');
		}

		return true;
	}

	/**
	 * Hvad g�r denne her egentlig?
	 *
	 */

	function getTypes() {
		$db = new DB_Sql;

		$shared_filehandler = $this->file_handler->kernel->useShared('filehandler');
		$types = $shared_filehandler->getSetting('instance_types');

		if($this->file_handler->get('id') != 0) {
			$db->query("SELECT id, width, height, type, file_size FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND file_handler_id = ".$this->file_handler->get('id')." AND active = 1 ORDER BY type");
		 	$is_saved = false;
			if($db->nextRecord()){
				$is_saved = true;
			}

			$this->file_handler->loadImage();

			for($i = 0, $max = count($types); $i < $max; $i++) {

				$types[$i]['width'] = '';
				$types[$i]['height'] = '';
				$types[$i]['file_size'] = '-';
				$types[$i]['file_uri'] = FILE_VIEWER.'?/'.$this->file_handler->kernel->intranet->get('public_key').'/'.$this->file_handler->get('access_key').'/'.$types[$i]['name'].'/'.urlencode($this->file_handler->get('file_name'));


				if($is_saved && $db->f('type') == $i) {
					$types[$i]['width'] = $db->f('width');
					$types[$i]['height'] = $db->f('height');
					$types[$i]['file_size'] = $db->f('file_size');

					if(!$db->nextRecord()) {
						$is_saved = false;
					}
				}
				elseif(isset($types[$i]['max_width']) && isset($types[$i]['max_height'])) {
					$tmp_size = $this->file_handler->image->getRelativeSize($types[$i]['max_width'], $types[$i]['max_height']);
					$types[$i]['width'] = $tmp_size['width'];
					$types[$i]['height'] = $tmp_size['height'];

				}

			}
		}

		return $types;
	}

	function _loadTypes() {
		// Det her skal der laves om p�, s� det fungere i indstillingerne i stedet.
		// Kan ikke lige gennemskue hvordan.
		// I filen main/file/index.php er disse ligeledes skrevet ind SJ
		// Det skal da bare g�res i Shared ligesom vi har for modulerne LO
		return array(
			0 => array('name' => 'manual', 'max_width' => 3456, 'max_height' => 2304), // Manuelt st�rrelse
			1 => array('name' => 'square', 'max_width' => 75, 'max_height' => 75),
			2 => array('name' => 'thumbnail', 'max_width' => 100, 'max_height' => 67),
			3 => array('name' => 'small', 'max_width' => 240, 'max_height' => 160),
			4 => array('name' => 'medium', 'max_width' => 500, 'max_height' => 333),
			5 => array('name' => 'large', 'max_width' => 1024, 'max_height' => 683),
			6 => array('name' => 'website', 'max_width' => 780, 'max_height' => 550));
		// Original (3456 x 2304)
	}

	function _checkType($type) {

		for($i = 0, $max = count($this->instance_types); $i < $max; $i++) {
			if(isset($this->instance_types[$i]['name']) && $this->instance_types[$i]['name'] == $type) {
				return $i;
				exit;
			}
		}
		return false;
	}


	/*
	function create($type, $param = array()) {






		$type_key = $this->_checkType($type);
		if($type_key === false) {
			trigger_error("Ugyldig type i FileHandler->createInstance", E_USER_ERROR);
		}

		if((int)$this->file_handler->get('id') == 0) {
			trigger_error("Der kan kun laves instance ud en loaded fil i Instance->create", E_USER_ERROR);
		}



		$image = Image_Transform::factory(IMAGE_LIBRARY);
		$error = $image->load($this->file_handler->get('file_path'));

		if($error !== true) {
			trigger_error("Kunne ikke �bne fil i FileHandler->createInstance. ".$error->getMessage(), E_USER_ERROR);
		}

		if($type_key == 1) { // square
			// skal lige resizes f�rst!
			if ($image->img_x > $image->img_y) {
				$image->scaleByY($this->instance_types[$type_key]['max_height']);
			}
			else {
				$image->scaleByX($this->instance_types[$type_key]['max_width']);
			}
			if ($image->crop($this->instance_types[$type_key]['max_width'], $this->instance_types[$type_key]['max_height']) !== true){
				trigger_error("Der opstod en fejl under formatering (crop) af billedet i FileHandler->createInstance", E_USER_ERROR);
			}
		}
		elseif($type_key > 0) {
			if($image->fit($this->instance_types[$type_key]['max_width'], $this->instance_types[$type_key]['max_height']) !== true) {
				trigger_error("Der opstod en fejl under formatering (fit) af billedet i FileHandler->createInstance", E_USER_ERROR);
			}
		}

		if(!$this->update(array('type' => $type))) {
			$this->delete();
			trigger_error("Kunne ikke gemme data i FileHandler->create", E_USER_ERROR);
		}
		$server_file_name = $this->id.'.'.$file_type['extension'];

		if(!is_dir($this->instance_path)) {
			if(!mkdir($this->instance_path)) {
				$this->delete();
				trigger_error("Kunne ikke oprette mappe i FileHandler->create", E_USER_ERROR);
			}
		}

		if($image->save($this->instance_path.$server_file_name) !== true) {
			$this->delete();
			trigger_error("Kunne ikke gemme billedet i FileHandler->create", E_USER_ERROR);
		}

		$size = filesize($this->instance_path.$server_file_name);

		$save_array = array(
			'server_file_name' => $server_file_name,
			'file_size' => $size,
			'width' => $image->new_x,
			'height' => $image->new_y
		);

		// den henter vist ikke den rigtige bredde og h�jde ud af billedet?
		if($this->update($save_array)) {
			$this->load();
			return $this->id;
		}
		else {
			$this->delete();
			trigger_error("Kunne ikke gemme de sidste data i FileHandler->create", E_USER_ERROR);
		}
	}
	*/

	/**
	 * @todo - B�r denne metode hedde saveInstance()
	 */
	function update($input) {
		trigger_error("Ingen funktionalitet i InstanceHandler->update", E_USER_ERROR);
	}

	function delete() {
		if($this->id == 0) {
			return false;
		}

		$db = new DB_Sql;

		if(file_exists($this->get('file_path'))) {
			if(!rename($this->get('file_path'), $this->instance_path.'_deleted_'.$this->get('server_file_name'))) {
				trigger_error("Kunne ikke omd�be filen i InstanceHandler->delete()", E_USER_ERROR);
			}
		}

		$db->query("UPDATE file_handler_instance SET active = 0 WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
		return true;
	}
}
?>