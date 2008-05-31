<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/XMLRPC/Contact/Server.php';

class ContactXMLRPCServerIntranet
{
    function get()
    {
        return 1;
    }
}

class ContactXMLRPCServerKernel
{
    public $intranet;
    function __construct()
    {
        $this->intranet = new ContactXMLRPCServerIntranet;
    }
}

class ContactXMLRPCTest extends PHPUnit_Framework_TestCase
{
    private $server;

    function setUp()
    {
        $this->server = new Intraface_XMLRPC_Contact_Server;
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE contact');
        $db->exec('TRUNCATE address');

    }

    function tearDown()
    {
		unset($this->server);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->server));
    }

    function testEmptyCredentialsThrowsException()
    {
        $credentials = array();
        $data = array();

        try {
            $this->server->saveContact($credentials, $data);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    function testInvalidKeyThrowsException()
    {
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');
        $data = array();
        try {
            $this->server->saveContact($credentials, $data);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    function getClient()
    {
        require_once PATH_ROOT . 'install/Install.php';

        if (!defined('SERVER_STATUS')) {
            define('SERVER_STATUS', 'TEST');
        }
        $install = new Intraface_Install;
        $install->resetServer();
        $install->grantModuleAccess('administration', 'contact');

        require_once 'XML/RPC2/Client.php';
        $options = array('prefix' => 'contact.');
        return XML_RPC2_Client::create(XMLRPC_SERVER_URL.'contact/server.php', $options);;
    }

    function testGetContactWithDanishCharactersIsReturnedInUTF8FromTheClient()
    {
        $client = $this->getClient();
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');

        $contact = new Contact(new ContactXMLRPCServerKernel);
        $data = array('name' => 'Tester ���');
        $contact->save($data);

        $retrieved = $client->getContact($credentials, $contact->getId());

        $this->assertEquals('Tester ���', utf8_decode($retrieved['name']));

    }

    function testSaveContactWorksWithDanishCharacters()
    {
        $client = $this->getClient();
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');

        $contact = new Contact(new ContactXMLRPCServerKernel);
        $data = array('name' => 'Tester');
        $contact->save($data);

        $new_name = 'Tester ���';
        $data = array('id' => $contact->getId(), 'name' => $new_name);
        $this->assertTrue($client->saveContact($credentials, $data));

        $saved_contact = new Contact(new ContactXMLRPCServerKernel, $contact->getId());
        $this->assertEquals($new_name, $saved_contact->get('name'));

    }

}
