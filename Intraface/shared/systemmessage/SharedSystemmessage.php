<?php
/**
 *
 * @package <SystemMessage>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0 
 *
 */
 

 
class SharedSystemmessage Extends Shared {

	function SharedSystemmessage() {
		$this->shared_name = "systemmessage"; // Navn p� p� mappen med modullet
		$this->active = 1; // Er shared aktivt
		
		// Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
		// $this->addSetting("payment_method", array("Dankort", "Kontant");
	
		// Filer der skal inkluderes ved opstart af modul. 
		$this->addPreloadFile("IntranetNews.php");
		
		// Fil til med indstillinger man kan s�tte i modullet
		// $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');	
		
		// Fil der inkluderes p� forsiden.
		// $this->addFrontpageFile('include_front.php'); 
		
		// Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["shared_navn.setting"] = "V�rdi";
		// $this->includeSettingFile("settings.php");
		
		
	}
}

?>