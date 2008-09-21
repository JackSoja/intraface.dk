<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'tests/unit/stubs/Kernel.php';
require_once 'tests/unit/stubs/Intranet.php';
require_once 'tests/unit/stubs/Address.php';
require_once 'tests/unit/stubs/User.php';
require_once 'tests/unit/stubs/Setting.php';
require_once 'tests/unit/stubs/Translation.php';
require_once 'Intraface/modules/invoice/Depreciation.php';

class DepreciationTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    
    function setUp() {
        
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE invoice_payment');
        $db->exec('TRUNCATE debtor');
        $db->exec('TRUNCATE contact');
        $db->exec('TRUNCATE address');
        $db->exec('TRUNCATE accounting_account');
        $db->exec('TRUNCATE accounting_post');
        $db->exec('TRUNCATE accounting_year');
        $db->exec('TRUNCATE accounting_voucher');
        
    }
    
    function createKernel() {
        $kernel = new FakeKernel;
        $kernel->user = new FakeUser;
        $kernel->intranet = new FakeIntranet;
        $kernel->intranet->address = new FakeAddress;
        $kernel->setting = new FakeSetting;
        // $kernel->setting->set('intranet', 'onlinepayment.provider_key', 1);
        $kernel->setting->set('user', 'accounting.active_year', 1);
        $kernel->setting->set('intranet', 'vatpercent', 25);
        
        return $kernel;
    }
    
    function createDebtor()
    {
        require_once 'Intraface/modules/invoice/Invoice.php';
        $debtor = new Invoice($this->createKernel());
        $debtor->update(
            array(
                'contact_id' => $this->createContact()->get('id'), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')
            )
        );
        
        return $debtor;
    }
    
    function createContact() {
        
        require_once 'Intraface/modules/contact/Contact.php';
        $contact = new Contact($this->createKernel());
        $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
        return $contact;
    }
    
    function createAccountingYear() {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0));
        $year->createAccounts('standard');
        return $year;
    }
    
    function testConstruct() {
        
        $depreciation = new Depreciation($this->createDebtor());
        $this->assertEquals('Depreciation', get_class($depreciation));
    }
    
    function testUpdateWithEmptyArray() {
        $depreciation = new Depreciation($this->createDebtor());
        
        $this->assertFalse($depreciation->update(array()));
        $this->assertEquals(1, $depreciation->error->count(), $depreciation->error->view());
        
    }
    
    function testUpdateWithValidInput() {
        $depreciation = new Depreciation($this->createDebtor());
        $this->assertTrue($depreciation->update(array('payment_date' => '01-01-2007', 'amount' => 100)));
    }
    
    function testLoad() {
        $debtor = $this->createDebtor();
        $depreciation = new Depreciation($debtor);
        $this->assertTrue($depreciation->update(array('payment_date' => '01-01-2007', 'amount' => 100)));
    
        $depreciation = new Depreciation($debtor, 1);
        $expected = array(
            'id' => 1,
            'amount' => '100.00',
            'type' => 'depreciation',
            'description' => '',
            'payment_date' => '2007-01-01',
            'payment_for_id' => 1,
            'dk_payment_date' => '01-01-2007', 
            'date_stated' => '0000-00-00', 
            'voucher_id' => 0,
            'type_key' => -1
        );
        
        $this->assertEquals($expected, $depreciation->get());
        
    }
    
    function testReadyForStateBeforeSaved() {
        $depreciation = new Depreciation($this->createDebtor());
        $this->assertFalse($depreciation->readyForState());
    }
    
    function testReadyForStateWhenReady() {
        $depreciation = new Depreciation($this->createDebtor());
        $depreciation->update(array('payment_date' => '01-01-2007', 'amount' => 100));
        $this->assertTrue($depreciation->readyForState());
    }
    
    function testIsStateBeforeStated() {
        $depreciation = new Depreciation($this->createDebtor());
        $this->assertFalse($depreciation->isStated());
    }
    
    function testState() {
        $depreciation = new Depreciation($this->createDebtor());
        $depreciation->update(array('payment_date' => '01-01-'.date('Y'), 'amount' => 100));
        $year = $this->createAccountingYear();
        $this->assertTrue($depreciation->state($year, 1, date('d-m-Y'), 7900, new FakeTranslation));
        
        $voucher = Voucher::factory($year, 1);
        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => date('d-m-Y'),
                'date' => date('Y-m-d'),
                'text' => 'depreciation for invoice #1',
                'debet' => '100.00',
                'credit' => '0.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 16,
                'stated' => 1,
                'account_number' => 7900,
                'account_name' => 'Diverse excl. moms'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => date('d-m-Y'),
                'date' => date('Y-m-d'),
                'text' => 'depreciation for invoice #1',
                'debet' => '0.00',
                'credit' => '100.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            )
        );
        
        $this->assertEquals($expected, $voucher->getPosts());
        
        $this->assertTrue($depreciation->isStated());
        $this->assertFalse($depreciation->readyForState());
    }
    
}
?>