<?php
/**
 *
 * @package <modul_navn>
 * @author	<name>
 * @since	1.0
 * @version	1.0 
 *
 */
class MainProcurement Extends Main {

	function mainProcurement() {
		$this->module_name = 'procurement'; // Navn p� p� mappen med modullet
		$this->menu_label = 'procurement'; // Navn er det skal st� i menuen
		$this->show_menu = 1; // Skal modullet v�re vist i menuen
		$this->active = 1; // Er modullet aktivt
		$this->menu_index = 90;
		$this->frontpage_index = 60;
		
		// Tilf�jer et undermenupunkt
		// $this->addSubMenuItem("Underside", "underside.php"); 
		// Tilf�jer undermenupunkt, der kun vises n�r hvis man har sub_acces'en vat_report
		// $this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate"); 
		// Tilf�jer undermenupunkt, der kun vises n�r hvis man har adgang til modullet backup
		// $this->addSubMenuItem("�rsafslutning", "end.php", "module:backup");
		
		// Tilf�jer en subaccess
		// $this->addSubAccessItem("canCreate", "Rettighed til at oprette");
		
		// Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
		// Status med f�lgende flow: bestilt (ordered), modtaget (recieved), annulleret (canceled)
		$this->addSetting('status', array(
											0=>'ordered',
											1=>'recieved',
											2=>'canceled')
		);
		
		$this->addSetting('from_region', array(
											0=>'denmark', 
											1=>'eu', 
											2=>'eu_vat_registered', 
											3=>'outside_eu')
		);
		
	
	
		// Filer der skal inkluderes ved opstart af modul. 
		$this->addPreloadFile('Procurement.php');
		$this->addPreloadFile('ProcurementItem.php');
		
		$this->addDependentModule('product');
		
		// Fil til med indstillinger man kan s�tte i modullet
		// $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');	
		
		// Fil der inkluderes p� forsiden.
		$this->addFrontpageFile('include_frontpage.php'); 
		
		// Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["modul_navn.setting"] = "V�rdi";
		// $this->includeSettingFile("settings.php");
		
		// Dependent module vil automatisk blive inkluderet p� siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
		// $this->addDependentModule("pdf");
	
		
		
		
		
	}
}
?>