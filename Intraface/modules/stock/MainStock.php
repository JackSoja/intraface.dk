<?php
/**
 *
 * @package Intraface_Stock
 * @author	Sune Jensen
 * @since	1.0
 * @version	1.0
 *
 */
class MainStock extends Main
{
    function __construct()
    {
        $this->module_name     = 'stock'; // Navn p� p� mappen med modullet
        $this->menu_label      = 'stock'; // Navn er det skal st� i menuen
        $this->show_menu       = 0; // Skal modullet v�re vist i menuen
        $this->active          = 1; // Er modullet aktivt
        $this->menu_index      = 155;
        $this->frontpage_index = 35;

        $this->addPreloadFile('Stock.php');
    }
}