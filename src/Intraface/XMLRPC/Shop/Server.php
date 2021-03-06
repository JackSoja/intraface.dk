<?php
/**
 * WebshopServer
 *
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC_Shop
 * @author   Lars Olesen <lars@legestue.net>
 * @version  @package-version@
 */
require_once 'Intraface/modules/webshop/Webshop.php';
require_once 'Intraface/modules/webshop/FeaturedProducts.php';

class Intraface_XMLRPC_Shop_Server
{
    private $kernel;
    private $webshop;
    private $basket;
    private $product;
    private $credentials;

    /**
     * Gets a list with products
     *
     * @param struct $credentials Credentials to use the server
     * @param array  $search      Optional search array
     *
     * @return array
     */
    public function getProducts($credentials, $search = array())
    {
        $this->checkCredentials($credentials);

        $offset = 0;

        $mixed = array();
        if (!empty($search)) {
            $mixed = $search;
        }

        $search = '';

        $this->_factoryWebshop();

        $products = array();

        $area = '';

        if (!empty($mixed['area'])) {
            $area = $mixed['area'];
        }

        $product = new Product($this->webshop->kernel);

        if (!isset($mixed['use_paging']) || $mixed['use_paging'] == 'true') {
            $product->getDBQuery()->usePaging('paging');
        }


        // sublevel has to be used so other searches are not overwritten
        $product->getDBQuery()->storeResult('use_stored', 'webshop_' . $area . '_' .  md5($this->credentials['session_id']), 'sublevel');
        $debug2 = serialize($mixed);
        if (isset($mixed['offset']) and array_key_exists('offset', $mixed) and is_numeric($mixed['offset'])) {
            $product->getDBQuery()->useStored(true);
            $product->getDBQuery()->setPagingOffset((int)$mixed['offset']);
            $debug2 .= 'offset ' . $mixed['offset'];
        } elseif (isset($mixed['use_stored']) and array_key_exists('use_stored', $mixed) and $mixed['use_stored'] == 'true') {
            $product->getDBQuery()->useStored(true);
            $debug2 .= 'use_stored true';
        } else {
            if (isset($mixed['search']) and array_key_exists('search', $mixed) and !empty($mixed['search'])) {
                $product->getDBQuery()->setFilter('search', $mixed['search']);
                $debug2 .= 'search ' . $mixed['search'];
            }

            if (isset($mixed['keywords']) and array_key_exists('keywords', $mixed) and !empty($mixed['keywords'])) {
                $product->getDBQuery()->setFilter('keywords', $mixed['keywords']);
                $debug2 .= 'keyword ' . $mixed['keywords'];
            }

            if (isset($mixed['ids']) and array_key_exists('ids', $mixed) and is_array($mixed['ids'])) {
                $product->getDBQuery()->setFilter('ids', $mixed['ids']);
                $debug2 .= 'ids ' . implode(', ', $mixed['ids']);
            }

            if (isset($mixed['sorting']) and array_key_exists('sorting', $mixed) and !empty($mixed['sorting'])) {
                $product->getDBQuery()->setFilter('sorting', $mixed['sorting']);
                $debug2 .= 'sorting ' . $mixed['sorting'];
            }
        }

        return array(
            'parameter' => $mixed,
            'debug2' => $debug2,
            'products' => $product->getList('webshop'),
            'paging' => $product->getDBQuery()->getPaging(),
            'search' => array(),
        );
    }


    /**
     * Gets one product
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getProduct($credentials, $id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $id = intval($id);

        if (!is_numeric($id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product = new Product($this->kernel, $id);
        $product->getPictures();
        if (!$product->get('has_variation')) {
            // loads stock information to array;
            $product->getStock();
        }

        return $product->get();
    }

    /**
     * Gets related products
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getRelatedProducts($credentials, $id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $product_id = intval($id);

        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product = new Product($this->kernel, $product_id);
        return $product->getRelatedProducts();
    }

   /**
     * Gets featured products
     *
     * Method is experimental and only used by discimport.dk. If you need to use it
     * as well, please contact lars@intraface.dk.
     *
     * @param struct  $credentials Credentials to use the server
     *
     * @return array
     */
    public function getFeaturedProducts($credentials)
    {
        $related_products = array();

        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($db)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException($db->getMessage() . $db->getUserInfo(), -1);
        }

        $featured = new Intraface_Webshop_FeaturedProducts($this->kernel->intranet, $db);
        $all = $featured->getAll();

        $related_products = array();

        foreach ($all as $row) {
            $product = new Product($this->kernel);
            // 265
            $product->getDBQuery()->setFilter('keywords', array($row['keyword_id']));

            $related_products[] = array(
                'title' => $row['headline'],
                'products' => $product->getList()
            );
        }

        return $related_products;
    }

   /**
     * Gets product keywords which can be used to sort ones webshop
     *
     * Method is experimental and only used by nylivsstil.dk. If you need to use it
     * as well, please contact lars@intraface.dk.
     *
     * @param struct  $credentials Credentials to use the server
     *
     * @return array with id and keywords
     */
    function getProductKeywords($credentials)
    {

        $this->checkCredentials($credentials);
        $this->_factoryWebshop();

        $product = new Product($this->kernel);
        $keywords = $product->getKeywordAppender();
        return $keywords->getUsedKeywords();
    }

    /**
     * Add product to basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $id                Product id to add
     * @param integer $quantity          Optional quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return boolean
     */
    public function addProductToBasket($credentials, $id, $quantity = 1, $text = '', $product_detail_id = 0)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop();

        $product_id = intval($id);

        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $text = $this->utf8Decode($text);
        return $this->webshop->basket->add(intval($product_id), intval($quantity), $text, $product_detail_id);
    }

    /**
     * Change the quantity of one product in basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $product_id        Product id to change
     * @param integer $quantity          New quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return mixed
     */
    public function changeProductInBasket($credentials, $product_id, $quantity, $text = '', $product_detail_id = 0)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $product_id = intval($product_id);
        $quantity = intval($quantity);

        if (!is_numeric($product_id) and !is_numeric($quantity)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id and quantity must be integers', -5);
        }

        $text = $this->utf8Decode($text);
        if (!$this->webshop->basket->change($product_id, $quantity, $text, $product_detail_id)) {
            return false;
        }

        return true;
    }

    /**
     * Gets an array with the current basket
     *
     * @param struct $credentials Credentials to use the server
     * @param struct $customer customer values
     *
     * @return array
     */
    public function getBasket($credentials, $customer = array())
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        // we put the possibility for BasketEvaluation not to be run.
        if (is_string($customer) && $customer == 'no_evaluation') {
            // nothing happens
        } elseif (is_array($customer)) {
            require_once 'Intraface/modules/webshop/BasketEvaluation.php';
            $basketevaluation = new BasketEvaluation($this->webshop->kernel);
            if (!$basketevaluation->run($this->webshop->basket, $customer)) {
                // We should see to return the result in some way.
            }
        }

        return array(
            'items' => $this->webshop->basket->getItems(),
            'price_total' => $this->webshop->basket->getTotalPrice(),
            'weight' => $this->webshop->basket->getTotalWeight()
        );
    }

    /**
     * Places an order in Intraface based on the current basket
     *
     * <code>
     *
     * </code>
     *
     * @param struct $credentials Credentials to use the server
     * @param struct $values      Values to save
     *
     * @return integer $order_id
     */
    public function placeOrder($credentials, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        if (!is_array($this->webshop->basket->getItems()) or count($this->webshop->basket->getItems()) <= 0) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be sent - cart is empty', -4);
        }

        if (empty($values['description'])) {
            $values['description'] = 'Onlineshop';
        }

        $values = $this->utf8Decode($values);

        if (!$order_id = $this->webshop->placeOrder($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be placed. It returned the following error: ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $order_id;
    }


    /**
     * Saves details for a processed onlineoayment
     *
     *
     * @param struct $credentials Credentials to use the server
     * @param struct $values      Values to save
     *
     * @return integer $payment_id
     */
    public function saveOnlinePayment($credentials, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        if (!$this->kernel->intranet->hasModuleAccess('onlinepayment')) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet did not have access to OnlinePayment', -4);
        }

        $this->kernel->useModule('onlinepayment', true); // true: ignores user access;

        if (isset($values['payment_id']) && intval($values['payment_id']) > 0) {
            $onlinepayment = OnlinePayment::factory($this->kernel, 'id', intval($values['payment_id']));
        } else {
            $onlinepayment = OnlinePayment::factory($this->kernel);
        }

        if (!$payment_id = $onlinepayment->save($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('Onlinebetaling kunne ikke blive gemt' . strtolower(implode(', ', $onlinepayment->error->getMessage())), -4);
        }

        return $payment_id;
    }


    /**
     * Returns an onlinepayment id to be processed to the id can be used in payment
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return integer $payment_id
     */
    public function createOnlinePayment($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        if (!$this->kernel->intranet->hasModuleAccess('onlinepayment')) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet did not have access to OnlinePayment', -4);
        }

        $this->kernel->useModule('onlinepayment', true); // true: ignores user access;

        $onlinepayment = OnlinePayment::factory($this->kernel);

        if (!$payment_id = $onlinepayment->create()) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('onlinepayment could not be created' . strtolower(implode(', ', $onlinepayment->error->getMessage())), -4);
        }

        return $payment_id;
    }

    /**
     * Saves buyer details
     *
     * @param struct $credentials Credentials to use the server
     * @param struct $values      Values to save
     *
     * @return boolean true or false
     */
    public function saveAddress($credentials, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        if (!is_array($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('details could not be saved - nothing to save', -4);
        }

        $values = $this->utf8Decode($values);

        if (!$this->webshop->basket->saveAddress($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return true;
    }

    /**
     * Get buyer details
     *
     * @param struct  $credentials Credentials to use the server
     *
     * @return array
     */
    public function getAddress($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        return $this->webshop->basket->getAddress();
    }

    /**
     * Saves customer coupon
     *
     * @param struct $credentials     Credentials to use the server
     * @param string $customer_coupon Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerCoupon($credentials, $customer_coupon)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $customer_coupon = $this->utf8Decode($customer_coupon);
        if (!$this->webshop->basket->saveCustomerCoupon($customer_coupon)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return true;
    }


    /**
     * Get customer coupon
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getCustomerCoupon($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        return $this->webshop->basket->getCustomerCoupon();
    }

    /**
     * Saves customer EAN location number
     *
     * @param struct $credentials     Credentials to use the server
     * @param string $customer_ean Customer EAN to save
     *
     * @return boolean true or false
     */
    public function saveCustomerEan($credentials, $customer_ean)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $customer_ean = $this->utf8Decode($customer_ean);
        if (!$this->webshop->basket->saveCustomerEan($customer_ean)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('ean could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return true;
    }


    /**
     * Get customer EAN location number
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getCustomerEan($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        return $this->webshop->basket->getCustomerEan();
    }

    /**
     * Saves customer comment
     *
     * @param struct $credentials     Credentials to use the server
     * @param string $customer_comment Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerComment($credentials, $customer_comment)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        $customer_comment = $this->utf8Decode($customer_comment);
        if (!$this->webshop->basket->saveCustomerComment($customer_comment)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return true;
    }


    /**
     * Get customer comment
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getCustomerComment($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        return $this->webshop->basket->getCustomerComment();
    }

    /**
     * Get receipt text
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getReceiptText($credentials)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop();

        return $this->webshop->getReceiptText();
    }

    /**
     * Checks credentials
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    private function checkCredentials($credentials)
    {
        $this->credentials = $credentials;

        if (count($credentials) != 2) { // -4
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $credentials['session_id'], $credentials['private_key']);
        $weblogin = $auth_adapter->auth();

        if (!$weblogin) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
        }

        $this->kernel = new Intraface_Kernel($credentials['session_id']);
        $this->kernel->weblogin = $weblogin;
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        return true;
    }

    /**
     * Initialize the webshop
     *
     * @return void
     */
    private function _factoryWebshop()
    {
        if (!$this->kernel->intranet->hasModuleAccess('webshop')) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet does not have access to the module webshop', -2);
        }
        $this->kernel->module('webshop');

        $this->webshop = new Webshop($this->kernel, $this->credentials['session_id']);
    }

    private function utf8Decode($values)
    {
        if (is_array($values)) {
            return array_map('utf8_decode', $values);
        } elseif (is_string($values)) {
            return utf8_decode($values);
        } else {
            return $values;
        }
    }
}
