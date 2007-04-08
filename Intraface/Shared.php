<?php
/**
 * Shared components
 *
 * Usage:
 * <code>

class SharedExample Extends Shard {

	function SharedExample() {
		$this->shared_name = "example"; // Navn p� p� mappen med modullet
		$this->active = 1; // Er shared aktivt

		// Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
		$this->addSetting("payment_method", array("Dankort", "Kontant");

		// Filer der skal inkluderes ved opstart af modul.
		$this->addPreloadFile("fil.php");

		// Fil til med indstillinger man kan s�tte i modullet
		$this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

		// Fil der inkluderes p� forsiden.
		$this->addFrontpageFile('include_front.php');

		// Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["shared_navn.setting"] = "V�rdi";
		$this->includeSettingFile("settings.php");


	}
}

SETTING:
Setting kan bruges til at s�tte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hj�lp af $module_object->getSetting("payment_method")


 * </code>
 *
 * @author Sune Jensen <sj@sunet.dk>
 */

class Shared {

	var $active;
	var $preload_file = array();
	var $shared_name;
	var $setting;
	var $controlpanel_files;
	var $frontpage_files; // til brug p� forsiden
	var $translation;


	function Shared() {
		// init
		$this->shared_name = '';
		$this->active = 0;
	}

	/**
	 * Denne funktion k�res af kernel, n�r man loader shared
	 *
	 */

	function load() {
		// Inkluder preload filerne

		for($i = 0, $max = count($this->preload_file); $i<$max; $i++) {
			$this->includeFile($this->preload_file[$i]);
		}
	}

	/**
	 * Denne funktion bruges af SharedNavn.php til at fort�lle, hvor includefilen til
   * det enkelte modul ligger.
	 */
	function addFrontpageFile($filename) {
		$this->frontpage_files[] = $filename;
	}

	function getFrontpageFiles() {
		return $this->frontpage_files;
	}

	function addControlpanelFile($title, $url) {
		$this->controlpanel_files[] = array(
			'title' => $title,
			'url' => $url
		);
	}

	function getControlpanelFiles() {
		return $this->controlpanel_files;
	}

	function addPreloadFile($file) {
		$this->preload_file[] = $file;
	}

	/**
   * Bruges til at inkludere fil
   *
   * �ndret med at tjekke om filen eksisterer.
   */
	function includeFile($file) {
		$file = PATH_INCLUDE_SHARED.$this->shared_name."/".$file;
		if (!file_exists($file)) {
			return 0;
		}
		require_once($file);
		return 1;
	}

	/*
	// virker det her endnu? // lars
	function addDependentModule($module) {
		$this->dependent_module[] = $module;
	}

	function getDependentModules() {
		return $this->dependent_module;
	}
	*/

	function includeSettingFile($file) {
		global $_setting; // den globaliseres ogs� andre steder?
		require(PATH_INCLUDE_SHARED.$this->shared_name."/".$file);
	}

	function getPath() {
		return(PATH_WWW_SHARED.$this->shared_name."/");
	}

	/**
   * Bruges til at tilf�je en setting til et modul, som skal v�re hardcoded ind i Main[Modulnavn]
   */
	function addSetting($key, $value) {
		$this->setting[$key] = $value;
	}

	function getSetting($key) {
		if(isset($this->setting[$key])) {
			return($this->setting[$key]);
		}
	}
/*
  function addTranslation($shortterm, $translation) {
    $this->translation[$shortterm] = $translation;
  }

  function getTranslation($shortterm) {
    if (!empty($this->translation[$shortterm])) {
      return $this->translation[$shortterm];
    }
    return $shortterm;
  }
*/
}

?>
