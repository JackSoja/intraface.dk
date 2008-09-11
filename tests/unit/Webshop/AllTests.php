<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

error_reporting(E_ALL);

class Webshop_AllTests
{
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Webshop');

        $tests = array('Basket', 'BasketEvaluation', 'Webshop', 'FeaturedProducts');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>