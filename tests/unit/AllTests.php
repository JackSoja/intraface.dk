<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class AllTests
{
    public static function suite()
    {
        PHPUnit_Util_Filter::addDirectoryToWhitelist(dirname(__FILE__) . '/../../Intraface/', '.php');
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__) . '/../../Intraface/certificates');
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__) . '/../../Intraface/3Party');
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__) . '/../../Intraface/config');
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__) . '/../../Intraface/ihtml');
        //PHPUnit_Util_Filter::addDirectoryToWhitelist('c:\wamp\cruisecontrol\projects\intraface\source\\', '.php');

        $suite = new PHPUnit_Framework_TestSuite('Intraface');

        $tests = array('Product',
                       'Email',
                       'Webshop',
                       'IntranetMaintenance',
                       'FileHandler',
                       'FileManager',
                       'Contact',
                       'Common',
                       'Accounting',
                       'CMS',
                       'Debtor',
                       'Shared',
                       'Newsletter',
                       'Keyword',
                       'Stock'
        );

        foreach ($tests AS $test) {
            require_once strtolower($test) . '/AllTests.php';
        }
        /*
        $suite->addTest(Accounting_AllTests::suite());
        $suite->addTest(CMS_AllTests::suite());
        $suite->addTest(Common_AllTests::suite());
        */
        $suite->addTest(Contact_AllTests::suite());
        /*
        $suite->addTest(Email_AllTests::suite());
        $suite->addTest(Filehandler_AllTests::suite());
        $suite->addTest(IntranetMaintenance_AllTests::suite());
        $suite->addTest(Newsletter_AllTests::suite());
        $suite->addTest(Product_AllTests::suite());
        $suite->addTest(Webshop_AllTests::suite());
        $suite->addTest(Debtor_AllTests::suite());
        $suite->addTest(Shared_AllTests::suite());
        $suite->addTest(Keyword_AllTests::suite());
        $suite->addTest(Stock_AllTests::suite());
        */
        return $suite;
    }
}