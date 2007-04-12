<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
	define('PHPUNIT_MAIN_METHOD', 'NewsletterTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'NewsletterTest.php';

class NewsletterTests {
	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Newsletters');

		$suite->addTestSuite('NewsletterTest');
		return $suite;
	}
}

if (PHPUNIT_MAIN_METHOD == 'NewsletterTests::main') {
	NewsletterTests::main();
}
?>