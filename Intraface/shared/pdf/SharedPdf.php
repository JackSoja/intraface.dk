<?php
/**
 *
 * @package <Pdf>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0
 *
 */
class SharedPdf extends Shared
{
    function __construct()
    {
        $this->shared_name = "pdf"; // Navn p� p� mappen med modullet
        $this->active = 1; // Er shared aktivt

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile("PdfMaker.php");

        // Fil til med indstillinger man kan s�tte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes p� forsiden.
        // $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["shared_navn.setting"] = "V�rdi";
        // $this->includeSettingFile("settings.php");


    }
}