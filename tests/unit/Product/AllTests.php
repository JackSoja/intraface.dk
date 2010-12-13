<?php
require_once 'PHPUnit/TextUI/TestRunner.php';

class Product_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Product');

        $tests = array('Product', 'VariationDetail', 'VariationGateway', 'VariationOneAttributeGroup', 'VariationTwoAttributeGroups', 'ProductDoctrine');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
