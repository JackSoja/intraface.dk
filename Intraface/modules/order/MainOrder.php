<?php
/**
 * @package Intraface_Order
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainOrder extends Main
{
    function __construct()
    {
        $this->module_name     = 'order'; // Navnet der vil st� i menuen
        $this->menu_label      = 'order'; // Navnet der vil st� i menuen
        $this->show_menu       = 0; // Skal modulet vises i menuen.
        $this->active          = 1; // Er modulet aktivt.
        $this->menu_index      = 64;
        $this->frontpage_index = 52;

        $this->addFrontpageFile('include_front.php');

        $this->addPreloadFile('Order.php');

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