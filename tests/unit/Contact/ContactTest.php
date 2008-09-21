<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/contact/Contact.php';
require_once 'ContactStubs.php';
require_once 'tests/unit/stubs/Address.php';
require_once 'tests/unit/stubs/User.php';
require_once 'tests/unit/stubs/PhpMailer.php';

class ContactTest extends PHPUnit_Framework_TestCase
{

    private $kernel;

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE address');
        $db->query('TRUNCATE contact');
    }

    function getKernel()
    {
        $kernel = new FakeContactKernel;
        $kernel->intranet = new FakeContactIntranet;
        $kernel->intranet->address = new FakeAddress;
        $kernel->user = new FakeUser;
        $kernel->setting = new FakeContactSetting;
        return $kernel;
    }

    /////////////////////////////////////////////////////////

    function testConstruction()
    {
        $contact = new Contact($this->getKernel());
        $this->assertTrue(is_object($contact));
    }

    function testNeedOptin()
    {
        $contact = new Contact($this->getKernel(), 7);
        $array = $contact->needNewsletterOptin();
        $this->assertTrue(is_array($array));
    }

    function testSave()
    {
        $contact = new Contact($this->getKernel(), 7);
        $data = array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269');
        $this->assertTrue($contact->save($data) > 0);
    }

    function testGetSimilarContacts()
    {
        $contact = new Contact($this->getKernel());
        $data = array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269');
        $contact->save($data);

        $contact = new Contact($this->getKernel());
        $data = array('name' => 'Tester 1', 'email' => 'lars@legestue.net', 'phone' => '26176860');
        $contact->save($data);

        $this->assertTrue($contact->hasSimilarContacts());

        $similar_contacts = $contact->getSimilarContacts();

        $this->assertEquals(1, count($similar_contacts));
    }
    
    function testSendLoginEmail() 
    {
        $contact = new Contact($this->getKernel());
        $data = array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269');
        $contact->save($data);
        
        /*
        This could be good, but unable to create 
        $phpmailer = $this->getMock('Phpmailer', array('AddAddress', 'send', '__get'));
        $phpmailer->expects($this->atLeastOnce())->method('AddAddress')->with($this->equalTo('lars@legestue.net'), $this->equalTo('Test'));
        $phpmailer->expects($this->once())->method('AddAddress');
        $phpmailer->expects($this->once())->method('__get')->with($this->equalTo('ErrorInfo'));
        */
        $mailer = new FakePhpMailer;
        $this->assertTrue($contact->sendLoginEmail($mailer));
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
        
    }
}
?>