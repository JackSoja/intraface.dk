<?php
/**
 * Account
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
class MainAccounting Extends Main {

    function MainAccounting() {
        $this->module_name = 'accounting'; // Navnet der vil st� i menuen
        $this->menu_label = 'Regnskab'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 40;
        $this->frontpage_index = 10;

        // Tilf�j undermenu punkter.
        $this->addSubMenuItem('accounting year', 'years.php');
        $this->addSubMenuItem('daybook', 'daybook.php');
        //$this->addSubMenuItem('state', 'state.php');
        $this->addSubMenuItem('accounts', 'accounts.php');
        $this->addSubMenuItem('vouchers', 'vouchers.php');
        $this->addSubMenuItem('vat', 'vat_period.php', 'sub_access:vat_report');
        $this->addSubMenuItem('end year', 'end.php', 'sub_access:endyear');
        $this->addSubMenuItem('search', 'search.php');
        //$this->addSubMenuItem('settings', 'setting.php', 'sub_access:setting');
        //$this->addSubMenuItem('Hj�lp', 'help.php');

        // Tilf�j subaccess punkter
        $this->addSubAccessItem('endyear', '�rsafslutning');
        $this->addSubAccessItem('vat_report', 'Momsopgivelse');
        $this->addSubAccessItem('setting', 'Indstillinger');


        $this->addControlPanelFile('accounting settings', 'modules/accounting/setting.php');

        $this->addFrontpageFile('include_frontpage.php');
        /*
        $this->addSetting('types', array('Headline', 'Drift', 'Status', 'Sum'));
        $this->addSetting('vat_options', array(
                                        0 => array('id' =>0, 'label' => 'Ingen moms'),
                                        1 => array('id' => 1, 'label' => 'Indg�ende moms'),
                                        2 => array('id' => 2, 'label' => 'Udg�ende moms')
                                )
        );
        */

        $this->addSetting('vat_periods',
            array(
                // halv�rlig
                0 => array(
                    'name' => 'Halv�rlig',
                    'periods' => array(
                        // 1. halv�r
                        1 => array(
                            'name' => '1. halv�r',
                            'date_from' => '01-01',
                            'date_to' => '06-30'
                        ),
                        // 2. halv�r
                        2 => array(
                            'name' => '2. halv�r',
                            'date_from' => '07-01',
                            'date_to' => '12-31'
                        )
                    )
                ),
                // kvartalsvis
                1 => array(
                    'name' => 'Kvartalsvis',
                    'periods' => array(
                        // januarkvartal
                        1 => array(
                            'name' => '1. kvartal',
                            'date_from' => '01-01',
                            'date_to' => '03-31'
                        ),
                        // februarkvartal
                        2 => array(
                            'name' => '2. kvartal',
                            'date_from' => '04-01',
                            'date_to' => '06-30'
                        ),
                        // februarkvartal
                        3 => array(
                            'name' => '3. kvartal',
                            'date_from' => '07-01',
                            'date_to' => '09-30'
                        ),
                        // februarkvartal
                        4 => array(
                            'name' => '4. kvartal',
                            'date_from' => '10-01',
                            'date_to' => '12-31'
                        )
                    )
                )
            )
        );

        $this->includeSettingFile('settings.php');

        $this->addPreloadFile('Account.php');
        $this->addPreloadFile('Year.php');
        $this->addPreloadFile('Post.php');
        //$this->addPreloadFile('PostDraft.php');
        $this->addPreloadFile('Voucher.php');
        $this->addPreloadFile('VoucherFile.php');
        $this->addPreloadFile('VatPeriod.php');

    }

}

?>