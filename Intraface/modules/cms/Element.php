<?php
require_once(PATH_INCLUDE_COMMON . 'tools'.DIRECTORY_SEPARATOR.'Position.php');
require('element/Htmltext.php');
require('element/Flickr.php');
require('element/Delicious.php');
require('element/Picture.php');
require('element/PageList.php');
require('element/FileList.php');
require('element/Gallery.php');
require('element/Video.php');
require('element/Map.php');
// require('element/ShortText.php'); // The file does not exist
// require('element/LongText.php'); // The file does not exist

class CMS_Element extends Standard {

	var $id;
	var $section;
	var $kernel;
	var $parameter;
	var $element_types;
	var $value;
	var $extra_css;
	var $extra_style;
	var $error;
	var $position;

	var $properties = array(
		'none' => 'none',
		'newline' => 'break before element',
		'float' => 'floating'
	);

	var $alignment = array(
		'left' => 'left',
		'center' => 'center',
		'right' => 'right'
	);

	/**
	 * Constructor:
	 * Construktor skal enten have cmspage eller en kernel.
	 * Hvis den f�r kernel skal den have et id.
	 * Fordelen er, at man ikke beh�ver at vide hvilken side elementet h�rer til,
	 * men blot beh�ver, at have elementid.
	 */

	function __construct(& $section, $id = 0) {
		if (!is_object($section)) {
			trigger_error('CMS_Element::CMS_Element needs CMS_Section - got ' . get_class($section), E_USER_ERROR);
		}
 		$this->value['identify_as'] = 'cms_element';  // bruges af parameter

		$this->id = (int) $id;
		$this->kernel = & $section->kernel;
		$this->section = & $section;
		$this->error = new Error;

		$this->parameter = $this->createParameter();



		$cms_module = $this->section->kernel->module('cms');
		$this->element_types = $cms_module->getSetting('element_types');
		$this->position = new Position("cms_element", "section_id=".$this->section->get('id')." AND active = 1 AND intranet_id = " . $this->kernel->intranet->get('id'), "position", "id");

		if (is_string($this->value['type']) AND in_array($this->value['type'], $this->element_types)) {
			$this->value['type_key'] = array_search($this->value['type'], $this->element_types);
		}

		if ($this->id > 0) {
			$this->load();
		}

	}

	function createParameter() {
		return new CMS_Parameter($this);
	}

	function factory(& $object, $type, $value) {
		$class_prefix = 'CMS_';
		switch ($type) {
			case 'type':
				// validering p� value // kun v�re gyldige elementtyper
				// object skal v�re section
				$class = $class_prefix . $value;
				return new $class($object);
				break;
			case 'id':
				// skal bruge kernel og numerisk value
				$cms_module = $object->getModule('cms');
				$element_types = $cms_module->getSetting('element_types');

				$db = new DB_Sql;

				$db->query("SELECT id, section_id, type_key FROM cms_element WHERE id = " . $value . " AND intranet_id = " . $object->intranet->get('id'));
				if (!$db->nextRecord()) {
					return false;
				}
				$class = $class_prefix . $element_types[$db->f('type_key')];
				if (!class_exists($class)) {
					return false;
				}
				return new $class(CMS_Section::factory($object, 'id', $db->f('section_id')), $db->f('id'));

				break;
			case 'section_and_id':
				// FIXME - jeg tror den her kan skabe en del
				// af problemerne med mange kald
				// skal bruge cmspage-object og numerisk value id
				$cms_module = $object->kernel->getModule('cms');
				$element_types = $cms_module->getSetting('element_types');

				$db = new DB_Sql;
				$db->query("SELECT id, section_id, type_key FROM cms_element WHERE id = " . $value . " AND intranet_id = " . $object->kernel->intranet->get('id'));
				if (!$db->nextRecord()) {
					return false;
				}

				$class = $class_prefix . $element_types[$db->f('type_key')];
				if (!class_exists($class)) {
					return false;
				}
				return new $class($object, $db->f('id'));


				break;

			default:
				trigger_error('Element::factory:: Invalid type', E_USER_ERROR);
				break;
		}
	}

	function load() {

		if ($this->id == 0) {
			return 0;
		}

		$db = new DB_Sql;
		$db->query("SELECT id, section_id, date_expire, date_publish, type_key, position FROM cms_element WHERE intranet_id = ".$this->section->kernel->intranet->get('id')." AND id = " . $this->id);
		if (!$db->nextRecord()) {
			return 0;
		}
		$this->value = array(); // Vi nulstiller f�rst alle oplysninger, hvis de tidligere har v�ret loadet.
		$this->value['id'] = $db->f('id');
		$this->value['section_id'] = $db->f('section_id');
		$this->value['date_expire'] = $db->f('date_expire');
		$this->value['date_publish'] = $db->f('date_publish');
		$this->value['type_key'] = $db->f('type_key');
		$this->value['position'] = $db->f('position');
		$this->value['type'] = $this->element_types[$this->value['type_key']];

		$this->value['elm_width'] = $this->parameter->get('elm_width');
		$this->value['elm_box'] = $this->parameter->get('elm_box');
		$this->value['elm_properties'] = $this->parameter->get('elm_properties');
		$this->value['elm_adjust'] = $this->parameter->get('elm_adjust');

		$this->value['extra_style'] = '';
		$this->value['extra_class'] = '';

		if ($this->get('elm_width')) {
			$this->value['extra_style'] .= 'width: ' . $this->get('elm_width') . ';';
		}

		if ($this->get('elm_properties') == 'float') {
			$this->value['extra_class'] .= ' cms-float-'.$this->get('elm_adjust');
			/*
			if ($this->get('type') == 'picture') {
				$this->extra_style .= ' width: ' . $this->get('width') . 'px';
			}
			*/
		}
		elseif ($this->get('elm_properties') == 'newline') {
			$this->value['extra_style'] .= ' clear: both;';

		}
		if ($this->get('elm_adjust')) {
			$this->value['extra_class'] .= ' cms-align-' . $this->get('elm_adjust');
		}

		if ($this->get('elm_box') == 'box') {
			$this->value['extra_class'] .= ' cms-box';
		}

		if (method_exists($this, 'load_element')) {
			$this->load_element();
		}

		return $this->id;
	}

	function validate($var) {
		// validere om section overhovedet findes
		// validere type
		if (!empty($var['elm_box']) AND $var['elm_box'] != 'box') {
			$this->error->set('error in elm_box');
		}
		if (!array_key_exists($var['elm_properties'], $this->properties)) {
			$this->error->set('error in elm_properties');
		}
		if (!array_key_exists($var['elm_adjust'], $this->alignment)) {
			$this->error->set('error in elm_adjust');
		}
		if (!empty($var['elm_width']) AND !strstr($var['elm_width'], '%') AND !strstr($var['elm_width'], 'em') AND !strstr($var['elm_width'], 'px')) {
			$this->error->set('error in elm_width - use %, em or px');
		}

		if ($this->error->isError()) {
			return 0;
		}

		return 1;
	}

	/**
	 *
	 * Her m� ikke bruges safeToDb p� hele arrayet, for ellers kommer vi til at behandle
	 * elementerne flere gange - og de bliver jo behandlet i parameter.
	 */

	function save($var) {

		if (!$this->validate($var)) {
			return 0;
		}

		if (empty($var['date_publish']) OR $var['date_publish'] == '0000-00-00 00:00:00') {
			$date_publish = 'NOW()';
		}
		else {
			$date_publish = '"'.safeToDb($var['date_publish']).'"';
		}

		$db = new DB_Sql;

		if ($this->id == 0) {
			$sql_type = "INSERT INTO ";
			$sql_end = ", date_created = NOW()";
		}
		else {
			$sql_type = "UPDATE ";
			$sql_end = " WHERE id = " . $this->id;
		}
		$sql = $sql_type . " cms_element SET
				intranet_id = ".$this->section->kernel->intranet->get('id').",
				section_id=". (int)$this->section->get('id') . ",
				type_key = ".safeToDb($this->value['type_key']).",
				date_changed = NOW(),
				date_publish = ".$date_publish.",
				date_expire = '".safeToDb($var['date_expire'])."'
			" . $sql_end;

		$db->query($sql);

		if ($this->id == 0) {
			$this->id = $db->insertedId();

			// s�t position for nye elementer
			$next_pos = $this->position->maxpos() + 1;
			$db->query("UPDATE cms_element SET position = " . $next_pos . " WHERE id = " . $this->id);


		}

		$this->load();


		// af en eller anden grund er paramterobjektet ikke en ordentlig referencd
		// derfor loader jeg lige objektet med det rigtige id
		// HACK det er naturligvis et hack, men vi m� kunne finde ud af hvad der er galt
		// det er kun et problem for nye elementer - dem der starter med id = 0
		$this->parameter->object->id = $this->id;
		$this->parameter->object->load();

		$this->parameter->save('elm_width', $var['elm_width']);
		if (isset($var['elm_box'])) $this->parameter->save('elm_box', intval($var['elm_box']));
		$this->parameter->save('elm_properties', $var['elm_properties']);
		$this->parameter->save('elm_adjust', $var['elm_adjust']);

		if (!$this->validate_element($var)) {
			return 0;
		}

		$this->save_element($var);

		return $this->id;
  }

  /**
   *
   * Funktionen skal kun deaktivere, s� intet nogensinde bliver slettet fra databasen.
	*/
	function delete(){
		// Husk kun at deaktivere
		$db = new DB_Sql;
		$db->query("UPDATE cms_element SET active = 0 WHERE id = " . $this->id);
		return 1;
	}

	function undelete() {
		// Husk kun at deaktivere
		$db = new DB_Sql;
		$db->query("UPDATE cms_element SET active = 1 WHERE id = " . $this->id);
		return 1;
	}

	function moveUp() {
		$this->position->moveUp($this->id);
	}

	function moveDown() {
		$this->position->moveDown($this->id);
	}

	function moveTo($position) {
		$this->position->moveTo($this->id, $position);
	}
}
?>