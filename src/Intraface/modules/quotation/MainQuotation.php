<?php
/**
 * @package Intraface_Quotation
 */
class MainQuotation extends Intraface_Main
{
    function __construct()
    {
        $this->module_name     = 'quotation'; // Navnet der vil st� i menuen
        $this->menu_label      = 'quotation'; // Navnet der vil st� i menuen
        $this->show_menu       = 0; // Skal modulet vises i menuen.
        $this->active          = 1; // Er modulet aktivt.
        $this->menu_index      = 62;
        $this->frontpage_index = 51;

        $this->addPreloadFile('Quotation.php');

        // $this->addFrontpageFile('include_front.php');

        // Tilf�j undermenu punkter.
        // $this->addSubMenuItem("�rsafslutning", "end.php");

        // Tilf�j subaccess punkter
        // opretkunde: et kort navn der er sigende
        // Opret ny kunde: En beskrivelse af subaccess.
        //$this->addSubAccessItem("opretkunde", "Opret ny kunde");

        // hvilke units kan man v�lge imellem?
        //$this->addSetting("unit", array(1=>"kr.", 2=>"stk."));
    }
}
