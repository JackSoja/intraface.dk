<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/newsletter/Observer/OptinMail.php';
require_once 'NewsletterStubs.php';

class NewsletterObserverTest extends PHPUnit_Framework_TestCase
{
    function createObserver()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeKernel;
        $list->kernel->intranet = new FakeIntranet;
        return new Intraface_Module_Newsletter_Observer_OptinMail($list);
    }

    function testCreateObserver()
    {
        $observer = $this->createObserver();
        $this->assertTrue(is_object($observer));
    }

    function testAddObserver()
    {
        $observer = $this->createObserver();
        $this->assertTrue($observer->update(new FakeSubscriber));
    }
}
?>