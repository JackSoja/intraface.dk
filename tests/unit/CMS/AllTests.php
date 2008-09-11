<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class CMS_AllTests {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_CMS');

        $tests = array('Template', 'Site', 'Page', 'Parameter', 'Section', 'PageElement', 'GalleryElement', 'SectionLongText', 'TemplateSection');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>