<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
require_once 'Intraface/functions.php';
require_once 'tests/unit/stubs/Kernel.php';
require_once 'tests/unit/stubs/Intranet.php';
require_once 'tests/unit/stubs/Setting.php';

class OnlinePaymentTest extends PHPUnit_Framework_TestCase
{
    private $kernel;

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE onlinepayment');
    }

    function createKernel()
    {
        $kernel = new FakeKernel;
        $kernel->setting = new FakeSetting;
        $kernel->setting->set('intranet', 'onlinepayment.provider_key', 1);
        $kernel->setting->set('intranet', 'onlinepayment.quickpay.md5_secret', 'abc');
        $kernel->setting->set('intranet', 'onlinepayment.quickpay.merchant_id', 12345678);
        $kernel->intranet = new FakeIntranet();
        return $kernel;
    }

    function testConstruct()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $this->assertEquals('OnlinePayment', get_class($onlinepayment));
    }

    function testFactoryWithTypeProvider()
    {
        $onlinepayment = OnlinePayment::factory($this->createKernel(), 'provider', 'quickpay');
        $this->assertEquals('OnlinePaymentQuickPay', get_class($onlinepayment));
    }

    function testSaveWithEmptyArray()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $this->assertEquals(0, $onlinepayment->save(array()));
        $this->assertEquals(4, $onlinepayment->error->count());

    }

    function testSaveWithValidDataReturnsInteger()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $this->assertTrue($onlinepayment->save(array(
            'belong_to' => 'invoice',
            'belong_to_id' => 1,
            'transaction_number' => 1,
            'transaction_status' => '000',
            'amount' => 100)) > 0);
    }

    function testUpdateWithValidDataReturnsInteger()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $data = array(
            'belong_to' => 'invoice',
            'belong_to_id' => 1,
            'transaction_number' => 1,
            'transaction_status' => '000',
            'amount' => 100,
            'original_amount' => 100,
            'dk_original_amount' => 100,
            'dk_amount' => 100);
        $this->assertTrue($onlinepayment->save($data) > 0);
        // $this->assertTrue($onlinepayment->setStatus('authorized'));
        $id = $onlinepayment->update($data);
        $this->assertTrue($id > 0);
    }

    function testLoad()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $onlinepayment->save(array(
            'belong_to' => 'invoice',
            'belong_to_id' => 1,
            'transaction_number' => 1,
            'transaction_status' => '000',
            'amount' => 100));

        $onlinepayment = new OnlinePayment($this->createKernel(), 1);

        $this->assertEquals(1, $onlinepayment->get('id'));
        $this->assertEquals(date('d-m-Y'), $onlinepayment->get('dk_date_created'));
        $this->assertEquals(2, $onlinepayment->get('belong_to_key'));
        $this->assertEquals('invoice', $onlinepayment->get('belong_to'));
        $this->assertEquals(1, $onlinepayment->get('belong_to_id'));
        $this->assertEquals('authorized', $onlinepayment->get('status'));
        $this->assertEquals(100, $onlinepayment->get('amount'));
        $this->assertEquals('100,00', $onlinepayment->get('dk_amount'));
    }

    function testCreateReturnsAPaymentIdLargerThanZero()
    {
        $onlinepayment = new OnlinePayment($this->createKernel());
        $id = $onlinepayment->create();
        $this->assertTrue($id > 0);
    }
}
