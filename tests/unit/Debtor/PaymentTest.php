<?php
require_once 'Intraface/modules/invoice/Payment.php';
require_once 'Intraface/functions.php';

class PaymentTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE invoice_payment');
        $this->db->exec('TRUNCATE debtor');
        $this->db->exec('TRUNCATE contact');
        $this->db->exec('TRUNCATE address');
        $this->db->exec('TRUNCATE accounting_account');
        $this->db->exec('TRUNCATE accounting_post');
        $this->db->exec('TRUNCATE accounting_year');
        $this->db->exec('TRUNCATE accounting_voucher');
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        $kernel->setting->set('intranet', 'onlinepayment.provider_key', 1);
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

    function createContact()
    {
        require_once 'Intraface/modules/contact/Contact.php';
        $contact = new Contact($this->createKernel());
        $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
        return $contact;
    }

    function createAccountingYear()
    {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0));
        $year->createAccounts('standard');
        return $year;
    }

    function testConstruct()
    {
        $payment = new Payment($this->createDebtor());
        $this->assertEquals('Payment', get_class($payment));
    }

    function testUpdateWithEmptyArrayReturnsFalse()
    {
        $payment = new Payment($this->createDebtor());

        $this->assertFalse($payment->update(array()));
        $this->assertEquals(3, $payment->error->count());
    }

    function testUpdateWithValidInputReturnsId()
    {
        $payment = new Payment($this->createDebtor());
        $expected_id = 1;
        $this->assertEquals($expected_id, $payment->update(array('payment_date' => '01-01-2007', 'amount' => 100, 'type' => 1)));
    }

    function testLoad()
    {
        $debtor = $this->createDebtor();
        $payment = new Payment($debtor);
        $payment->update(array('payment_date' => '01-01-2007', 'amount' => 100, 'type' => 1));

        $payment = new Payment($debtor, 1);
        $expected = array(
            'id' => 1,
            'amount' => '100.00',
            'type' => 'giro_transfer',
            'description' => '',
            'payment_date' => '2007-01-01',
            'payment_for_id' => 1,
            'dk_payment_date' => '01-01-2007',
            'date_stated' => '0000-00-00',
            'voucher_id' => 0,
            'type_key' => 1,
            'this_date' => '2007-01-01'
        );

        $this->assertEquals($expected, $payment->get());
    }

    function testReadyForStateBeforeSaved()
    {
        $payment = new Payment($this->createDebtor());
        $this->assertFalse($payment->readyForState());
    }

    function testReadyForStateWhenReady()
    {
        $payment = new Payment($this->createDebtor());
        $payment->update(array('payment_date' => '01-01-2007', 'amount' => 100, 'type' => 1));
        $this->assertTrue($payment->readyForState());
    }

    function testIsStateBeforeStated()
    {
        $payment = new Payment($this->createDebtor());
        $this->assertFalse($payment->isStated());
    }

    function testState()
    {
        $payment = new Payment($this->createDebtor());
        $payment->update(array('payment_date' => '01-01-'.date('Y'), 'amount' => 100, 'type' => 0));
        $year = $this->createAccountingYear();
        $this->assertTrue($payment->state($year, 1, date('d-m-Y'), 58000, new Stub_Translation));

        $voucher = Voucher::factory($year, 1);
        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => date('d-m-Y'),
                'date' => date('Y-m-d'),
                'text' => 'Payment  (invoice #1)',
                'debet' => '100.00',
                'credit' => '0.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 33,
                'stated' => 1,
                'account_number' => 58000,
                'account_name' => 'Bank, folio'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => date('d-m-Y'),
                'date' => date('Y-m-d'),
                'text' => 'Payment  (invoice #1)',
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

        $this->assertTrue($payment->isStated());
        $this->assertFalse($payment->readyForState());
    }
}
