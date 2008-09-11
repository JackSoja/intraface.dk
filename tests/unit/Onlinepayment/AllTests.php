<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'OnlinePaymentTest.php';

class OnlinePayment_AllTests
{

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Onlinepayment');

        $suite->addTestSuite('OnlinePaymentTest');
        return $suite;
    }
}
?>