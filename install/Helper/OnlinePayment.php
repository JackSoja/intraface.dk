<?php
class Install_Helper_OnlinePayment {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function setProvider() 
    {
        require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
        $onlinepayment = new OnlinePayment($this->kernel);
        $onlinepayment->setProvider(array('provider_key' => 2));
        $onlinepayment = OnlinePayment::factory($this->kernel);
        $onlinepayment->setSettings(array('merchant_id' => '12345678', 'md5_secret' => 'qqqaaasss'));
    }
    
    
    public function createAndAttachToOrder() {
        
        require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
        $onlinepayment = new OnlinePayment($this->kernel);
        if (!$onlinepayment->save(array('belong_to' => 'order', 'belong_to_id' => 1, 'transaction_number' => 111, 'transaction_status' => '000', 'amount' => 200))) {
            echo $onlinepayment->error->view();
            die;
        }
    }
    
    public function createInEurAndAttachToInvoice() {
        
        require_once 'Intraface/modules/currency/Currency/Gateway.php';
        $doctrine = Doctrine_Manager::connection(DB_DSN);
        $gateway = new Intraface_modules_currency_Currency_Gateway($doctrine);
        $currency = $gateway->findByIsoCode('Eur');
        
        require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
        $onlinepayment = new OnlinePayment($this->kernel);
        if (!$onlinepayment->save(array('belong_to' => 'invoice', 'belong_to_id' => 1, 'transaction_number' => 111, 'transaction_status' => '000', 'amount' => 100, 'currency' => $currency))) {
            echo $onlinepayment->error->view();
            die;
        }
    }
    
}
?>
