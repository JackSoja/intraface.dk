<?php
/**
 *
 * @package <FileManager>
 * @author	<name>
 * @since	1.0
 * @version	1.0 
 *
 */
class MainFileManager Extends Main {

	function MainFileManager() {
		$this->module_name = 'filemanager'; // Navn p� p� mappen med modullet
		$this->menu_label = 'Filer'; // Navn er det skal st� i menuen
		$this->show_menu = 1; // Skal modullet v�re vist i menuen
		$this->active = 1; // Er modullet aktivt
		$this->menu_index = 20;
		$this->frontpage_index = 80;
		
		// Tilf�jer et undermenupunkt
		// $this->addSubMenuItem("Underside", "underside.php"); 
		// Tilf�jer undermenupunkt, der kun vises n�r hvis man har sub_acces'en vat_report
		// $this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate"); 
		// Tilf�jer undermenupunkt, der kun vises n�r hvis man har adgang til modullet backup
		// $this->addSubMenuItem("�rsafslutning", "end.php", "module:backup");
		
		// Tilf�jer en subaccess
		// $this->addSubAccessItem("canCreate", "Rettighed til at oprette");
		
		// Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
		// $this->addSetting("payment_method", array("Dankort", "Kontant");
	
		// Filer der skal inkluderes ved opstart af modul. 
		$this->addPreloadFile('FileManager.php');
		
		// Fil til med indstillinger man kan s�tte i modullet
		// $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');	
		
		// Fil der inkluderes p� forsiden.
		// $this->addFrontpageFile('include_front.php'); 
		
		// Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["modul_navn.setting"] = "V�rdi";
		// $this->includeSettingFile("settings.php");
		
		// Dependent module vil automatisk blive inkluderet p� siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
		// $this->addDependentModule("pdf");
		
		// Inkludere et shared i modullet.
		$this->addRequiredShared("filehandler");
		$this->addRequiredShared("keyword");		
	}
}


/*
SETTING:
Setting kan bruges til at s�tte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hj�lp af $module_object->getSetting("payment_method")



*/
?>