<?php
/**
 * ShopServer version 0.4.0
 * Class named with version XXYY from version numbering XX.YY.ZZ
 *
 * @todo we need to move kernel out of Product.
 * @todo we need to move kernel out of DBQuery.
 * @todo we need to find out what to do with hasIntranetAccess and stock
 * @todo we need to work out with getPictures() and Kernel->useModule
 *
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC_Shop
 * @author   Lars Olesen <lars@legestue.net>
 * @version  @package-version@
 */
class Intraface_XMLRPC_Shop_Server0100 extends Intraface_XMLRPC_Server0100
{
    protected $webshop;
    protected $basket;
    protected $product;
    protected $doctrine;

    /**
     * Constructor
     *
     * @param $encoding the encoding used for the XML_RPC2 backend
     *
     * @return unknown_type
     */
    public function __construct(Doctrine_Connection_Common $doctrine, $encoding = 'utf-8')
    {
        $this->doctrine = $doctrine;
        parent::__construct($encoding);
    }

    /**
     * Gets a list with products
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param array  $search      Optional search array
     *
     * @return array
     */
    public function getProducts($credentials, $shop_id, $search = array())
    {
        $this->checkCredentials($credentials);

        $offset = 0;

        $search = $this->processRequestData($search);

        $mixed = array();
        if (!empty($search)) {
            $mixed = $search;
        }

        $search = '';

        $this->_factoryWebshop($shop_id);

        $products = array();

        $area = '';

        if (!empty($mixed['area'])) {
            $area = $mixed['area'];
        }

        $product = new Intraface_modules_product_Gateway($this->webshop->kernel);

        if (!isset($mixed['use_paging']) || $mixed['use_paging'] == 'true') {
            $product->getDBQuery()->usePaging('paging');
        }

        // sublevel has to be used so other searches are not overwritten
        $product->getDBQuery()->storeResult('use_stored', 'webshop_' . $area . '_' .  md5($this->credentials['session_id']), 'sublevel');
        $debug2 = serialize($mixed);
        if (isset($mixed['offset']) and is_numeric($mixed['offset']) and $mixed['offset'] > 0) {
            $product->getDBQuery()->useStored(true);
            $product->getDBQuery()->setPagingOffset((int)$mixed['offset']);
            $debug2 .= 'offset ' . $mixed['offset'];
        } elseif (isset($mixed['use_stored']) and array_key_exists('use_stored', $mixed) and $mixed['use_stored'] == 'true') {
            $product->getDBQuery()->useStored(true);
            $debug2 .= 'use_stored true';
        } else {
            if (array_key_exists('search', $mixed) and !empty($mixed['search'])) {
                $product->getDBQuery()->setFilter('search', $mixed['search']);
                $debug2 .= 'search ' . $mixed['search'];
            }

            if (array_key_exists('keywords', $mixed) and !empty($mixed['keywords'])) {
                $product->getDBQuery()->setFilter('keywords', $mixed['keywords']);
                $debug2 .= 'keyword ' . $mixed['keywords'];
            }

            if (array_key_exists('category', $mixed) and !empty($mixed['category'])) {
                $product->getDBQuery()->setFilter('shop_id', $shop_id);
                $product->getDBQuery()->setFilter('category', $mixed['category']);
                $debug2 .= 'category ' . $mixed['category'];
            }

            if (isset($mixed['ids']) and array_key_exists('ids', $mixed) and is_array($mixed['ids'])) {
                $product->getDBQuery()->setFilter('ids', $mixed['ids']);
                $debug2 .= 'ids ' . implode(', ', $mixed['ids']);
            }

            if (array_key_exists('sorting', $mixed) and !empty($mixed['sorting'])) {
                $product->getDBQuery()->setFilter('sorting', $mixed['sorting']);
                $debug2 .= 'sorting ' . $mixed['sorting'];
            }
        }

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            $currencies = $currency_gateway->findAllWithExchangeRate();
        } else {
            $currencies = false;
        }

        return $this->prepareResponseData(array(
            'parameter' => $mixed,
            //'debug2' => $debug2,
            'products' => $this->cleanUpProductList($product->getAllProducts('webshop', $currencies)),
            'paging' => $product->getDBQuery()->getPaging(),
            'search' => array(),
        ));
    }

    /**
     * Gets a list with products in given category
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $category_id Id of category
     * @param integer $results_per_page Optional returned products per page
     * @param integer $pagging_offset Otional offset for pagging.
     *
     * @return array
     */
    public function getProductsInCategoryId($credentials, $shop_id, $category_id, $results_per_page = 0, $pagging_offset = 0)
    {
        $search = array();
        $search['area'] = 'category_'.$category_id;
        if ($results_per_page > 0) {
            $search['use_paging'] = 'true';
        } else {
            $search['use_paging'] = 'false';
        }

        $search['category'] = $category_id;
        $search['offset'] = $pagging_offset;

        return $this->getProducts($credentials, $shop_id, $search);
    }

    /**
     * Gets a list with products with a given keyword or with given keywords
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param mixed  $keyword      Integer with keyword id or array with keyword ids.
     * @param integer $results_per_page optional returned products per page
     * @param integer $pagging_offset optional offset for pagging.
     *
     * @return array
     */
    public function getProductsWithKeywordId($credentials, $shop_id, $keyword, $results_per_page = 0, $pagging_offset = 0)
    {

        $search = array();
        if (is_array($keyword)) {
            $search['area'] = 'keyword_'.implode('-', $keyword);
        } else {
            $search['area'] = 'keyword_'.$keyword;
        }
        if ($results_per_page > 0) {
            $search['use_paging'] = 'true';
        } else {
            $search['use_paging'] = 'false';
        }
        $search['keywords'] = $keyword;
        $search['offset'] = $pagging_offset;

        return $this->getProducts($credentials, $shop_id, $search);
    }

    /**
     * Returns product ids with keyword id
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id Id for the shop
     * @param mixed $keyword Integer with keyword id or array with keyword ids.
     * @return array
     */
    public function getProductIdsWithKeywordId($credentials, $shop_id, $keyword)
    {
        $this->checkCredentials($credentials);
        $this->_factoryWebshop($shop_id);

        $gateway = new Intraface_modules_product_Gateway($this->webshop->kernel);

        return $this->prepareResponseData(
            $gateway->getProductIdsWithKeywordForShop($this->processRequestData($keyword))
        );
    }

    /**
     * Gets a list with products with a given keyword or with given keywords
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $attribute_id Integer with attribute id.
     * @param integer $results_per_page optional returned products per page
     * @param integer $pagging_offset optional offset for pagging.
     *
     * @return array
     */
    public function getProductsWithVariationAttributeId($credentials, $shop_id, $attribute_id, $results_per_page = 0, $pagging_offset = 0)
    {
        $this->checkCredentials($credentials);
        $this->_factoryWebshop($shop_id);


        $gateway = new Intraface_modules_product_Attribute_Group_Gateway($this->getDoctrine());
        try {
            $attribute_group = $gateway->findByAttributeId($attribute_id);
        } catch (Intraface_Gateway_Exception $e) {
            return $this->prepareResponseData(
                array(
                    'http_header_status' => 'HTTP/1.0 404 Not Found',
                    'products' => array(),
                    'attribute_group' => array()
                )
            );
        }

        $gateway = new Intraface_modules_product_ProductDoctrineGateway($this->getDoctrine(), null);
        $doctrine_products = $gateway->findByVariationAttributeId($this->processRequestData($attribute_id));

        return $this->prepareResponseData(
            array(
                'http_header_status' => 'HTTP/1.0 200 OK',
                'products' => $this->createDoctrineProductsListArray($doctrine_products, $attribute_id),
                'attribute_group' => $this->createAttributeGroupArray($attribute_group)
            )
        );
    }

    /**
     * Formats array to return from product list from Doctrine
     *
     * @param object $doctrine_products Doctrine_Collection
     * @return array with products
     */
    private function createDoctrineProductsListArray($doctrine_products, $attribute_id = null)
    {
        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            $currencies = $currency_gateway->findAllWithExchangeRate();
        } else {
            $currencies = false;
        }

        $shared_filehandler = $this->kernel->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $products = array();
        $key = 0;
        foreach ($doctrine_products as $p) {
            $products[$key]['id'] = $p->getId();
            $products[$key]['number'] = $p->getDetails()->getNumber();
            $products[$key]['name'] = $p->getDetails()->getTranslation('da')->name;
            $products[$key]['unit'] = $p->getDetails()->getUnit();
            $products[$key]['detail_id'] = $p->getDetails()->getId();
            $products[$key]['vat_percent'] = $p->getDetails()->getVatPercent()->getAsIso(2);
            $products[$key]['stock'] = $p['stock'];
            $products[$key]['has_variation'] = $p->hasVariation();

            // $products[$key]['stock_status'] = $p['stock_status'];

            $products[$key]['currency']['DKK']['price'] = $p->getDetails()->getPrice()->getAsIso(2);
            $products[$key]['currency']['DKK']['price_incl_vat'] = $p->getDetails()->getPriceIncludingVat()->getAsIso(2);
            $products[$key]['currency']['DKK']['before_price'] = $p->getDetails()->getBeforePrice()->getAsIso(2);
            $products[$key]['currency']['DKK']['before_price_incl_vat'] = $p->getDetails()->getBeforePriceIncludingVat()->getAsIso(2);

            if ($currencies && $currencies->count() > 0) {
                foreach ($currencies as $currency) {
                    $products[$key]['currency'][$currency->getType()->getIsoCode()]['price'] = $p->getDetails()->getPriceInCurrency($currency);
                    $products[$key]['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'] = $p->getDetails()->getPriceIncludingVatInCurrency($currency);
                    $products[$key]['currency'][$currency->getType()->getIsoCode()]['before_price'] = $p->getDetails()->getBeforePriceInCurrency($currency);
                    $products[$key]['currency'][$currency->getType()->getIsoCode()]['before_price_incl_vat'] = $p->getDetails()->getBeforePriceIncludingVatInCurrency($currency);
                }
            }

            $products[$key]['pictures'] = $this->getProductPictures($p);

            if ($p->hasVariation() && $p->hasStock() && $attribute_id != null) {
                try {
                    $variaton_gateway = new Intraface_modules_product_Variation_Gateway($p);
                    $variations = $variaton_gateway->findWithAttributeId($attribute_id);
                } catch (Intraface_Gateway_Exception $e) {
                    $variations = array();
                }

                $stub_product = new Intraface_XMLRPC_Shop_Server0100_Product($p, $this->webshop->kernel);
                $products[$key]['attribute_stock_for_sale'] = 0;

                foreach ($variations as $variation) {
                    $stock = $variation->getStock($stub_product)->get();
                    $products[$key]['attribute_stock_for_sale'] += $stock['for_sale'];
                }


                /*$return['variations'][] = array(
                    'variation' => array(
                        'id' => $variation->getId(),
                        'detail_id' => $detail->getId(),
                        'number' => $variation->getNumber(),
                        'name' => $variation->getName(),
                        'attributes' => $attributes_array,
                        'identifier' => $attribute_string,
                        'price_incl_vat' => $detail->getPriceIncludingVat($product)->getAsIso(2),
                        'weight' => $product->get('weight') + $detail->getWeightDifference(2),
                        'currency' => $variation_currency
                    ),
                    'stock' => $stock
                );
                */
            }
            $key++;
        }

        return $products;
    }

    private function getProductPictures($product)
    {
        $append_file = new AppendFile($this->kernel, 'product', $product->getId());
        $appendix_list = $append_file->getList();

        $pictures = array();

        if (count($appendix_list) > 0) {
            foreach ($appendix_list as $key => $appendix) {
                $tmp_filehandler = new FileHandler($this->kernel, $appendix['file_handler_id']);
                $pictures[$key]['id']                   = $appendix['file_handler_id'];
                $pictures[$key]['original']['icon_uri'] = $tmp_filehandler->get('icon_uri');
                $pictures[$key]['original']['name']     = $tmp_filehandler->get('file_name');
                $pictures[$key]['original']['width']    = $tmp_filehandler->get('width');
                $pictures[$key]['original']['height']   = $tmp_filehandler->get('height');
                $pictures[$key]['original']['file_uri'] = $tmp_filehandler->get('file_uri');
                $pictures[$key]['appended_file_id']     = $appendix['id'];

                if ($tmp_filehandler->get('is_image')) {
                    $tmp_filehandler->createInstance();
                    $instances = $tmp_filehandler->instance->getList('include_hidden');
                    foreach ($instances as $instance) {
                        $pictures[$key][$instance['name']]['file_uri'] = $instance['file_uri'];
                        $pictures[$key][$instance['name']]['name']     = $instance['name'];
                        $pictures[$key][$instance['name']]['width']    = $instance['width'];
                        $pictures[$key][$instance['name']]['height']   = $instance['height'];
                    }
                }
                $tmp_filehandler->__destruct();
                unset($tmp_filehandler);
            }
        }

        return $pictures;
    }

    private function createAttributeGroupArray($attribute_group)
    {
        foreach ($attribute_group->attribute as $attribute) {
            $attributes[] = array(
                'id' => $attribute->getId(),
                'name' => $attribute->getName());
        }

        $attribute = $attribute_group->attribute->getFirst();
        return array(
            'id' => $attribute_group->getId(),
            'name' => $attribute_group->getName(),
            'attributes' => $attributes);
    }

    /**
     * Make sure we only include necessary data. Several things more might
     * be left out. Mostly we remove description.
     *
     * @param array products
     * @return array cleaned up products
     */
    private function cleanUpProductList($products)
    {
        $return = array();
        foreach ($products as $key => $p) {
            $return[$key]['id'] = $p['id'];
            $return[$key]['number'] = $p['number'];
            $return[$key]['name'] = $p['name'];
            $return[$key]['price'] = $p['price'];
            $return[$key]['unit'] = $p['unit'];
            $return[$key]['vat'] = $p['vat'];
            $return[$key]['weight'] = $p['weight'];
            $return[$key]['detail_id'] = $p['detail_id'];
            $return[$key]['vat_percent'] = $p['vat_percent'];
            $return[$key]['price_incl_vat'] = $p['price_incl_vat'];
            $return[$key]['stock'] = $p['stock'];
            $return[$key]['has_variation'] = $p['has_variation'];
            $return[$key]['stock_status'] = $p['stock_status'];

            foreach ($p['currency'] as $k => $c) {
                $return[$key]['currency'][$k]['price'] = $c['price']->getAsIso(2);
                $return[$key]['currency'][$k]['price_incl_vat'] = $c['price_incl_vat']->getAsIso(2);
                $return[$key]['currency'][$k]['before_price'] = $c['before_price']->getAsIso(2);
                $return[$key]['currency'][$k]['before_price_incl_vat'] = $c['before_price_incl_vat']->getAsIso(2);
            }

            if (isset($p['pictures'])) {
                $return[$key]['pictures'] = $p['pictures'];
            }
        }

        return $return;
    }

    /**
     * Gets one product
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getProduct($credentials, $shop_id, $id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $id = $this->processRequestData(intval($id));

        $return = array();

        $product = new Product($this->kernel, $id);
        if ($product->get('id') == 0 || $product->get('do_show') == 0 || $product->get('active') == 0) {
            return array('product' => array('id' => 0));
        }

        $product->getPictures();
        $return['product'] = $product->get();
        $return['product']['currency']['DKK']['price'] = $product->getDetails()->getPrice()->getAsIso(2);
        $return['product']['currency']['DKK']['price_incl_vat'] = $product->getDetails()->getPriceIncludingVat()->getAsIso(2);
        $return['product']['currency']['DKK']['before_price'] = $product->getDetails()->getBeforePrice()->getAsIso(2);
        $return['product']['currency']['DKK']['before_price_incl_vat'] = $product->getDetails()->getBeforePriceIncludingVat()->getAsIso(2);

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            foreach ($currency_gateway->findAllWithExchangeRate() as $currency) {
                $return['product']['currency'][$currency->getType()->getIsoCode()]['price'] = $product->getDetails()->getPriceInCurrency($currency)->getAsIso(2);
                $return['product']['currency'][$currency->getType()->getIsoCode()]['price_incl_vat'] = $product->getDetails()->getPriceIncludingVatInCurrency($currency)->getAsIso(2);
                $return['product']['currency'][$currency->getType()->getIsoCode()]['before_price'] = $product->getDetails()->getBeforePriceInCurrency($currency)->getAsIso(2);
                $return['product']['currency'][$currency->getType()->getIsoCode()]['before_price_incl_vat'] = $product->getDetails()->getBeforePriceIncludingVatInCurrency($currency)->getAsIso(2);
            }
        }

        if (!$product->hasVariation() && $product->get('stock')) {
            $return['stock'] = $product->getStock()->get();
        }

        if ($product->get('has_variation')) {
            $variations = $product->getVariations();
            foreach ($variations as $variation) {
                if ($product->get('stock')) {
                    $stock = $variation->getStock($product)->get();
                } else {
                    $stock = false;
                }

                $detail = $variation->getDetail();
                $attribute_string = '';
                $attributes_array = $variation->getAttributesAsArray();

                foreach ($attributes_array as $attribute) {
                    if ($attribute_string != '') {
                        $attribute_string .= '-';
                    }
                    $attribute_string .= $attribute['id'];

                    // We calculate all products which is on stock with this attribute to be able to mark unused attributes in list.
                    if (!isset($attribute_for_sale[$attribute['id']])) {
                        $attribute_for_sale[$attribute['id']] = 0;
                    }
                    if ($stock !== false) {
                        // If for_sale is less than zero we add zero.
                        $attribute_for_sale[$attribute['id']] += (($stock['for_sale'] < 0) ? 0 : $stock['for_sale']);
                    } else {
                        // If product does not use stock, then we calculate one up, as the attribute is always in use.
                        $attribute_for_sale[$attribute['id']] += 1;
                    }
                }

                $variation_currency['DKK']['price'] = $detail->getPrice($product)->getAsIso(2);
                $variation_currency['DKK']['price_incl_vat'] = $detail->getPriceIncludingVat($product)->getAsIso(2);

                if (isset($currency_gateway) && is_object($currency_gateway)) {
                    foreach ($currency_gateway->findAllWithExchangeRate() as $currency) {
                        $variation_currency[$currency->getType()->getIsoCode()]['price'] = $detail->getPriceInCurrency($currency, 0, $product)->getAsIso(2);
                        $variation_currency[$currency->getType()->getIsoCode()]['price_incl_vat'] = $detail->getPriceIncludingVatInCurrency($currency, 0, $product)->getAsIso(2);
                    }
                }

                $return['variations'][] = array(
                    'variation' => array(
                        'id' => $variation->getId(),
                        'detail_id' => $detail->getId(),
                        'number' => $variation->getNumber(),
                        'name' => $variation->getName(),
                        'attributes' => $attributes_array,
                        'identifier' => $attribute_string,
                        'price_incl_vat' => $detail->getPriceIncludingVat($product)->getAsIso(2),
                        'weight' => $product->get('weight') + $detail->getWeightDifference(2),
                        'currency' => $variation_currency
                    ),
                    'stock' => $stock
                );
            }

            // We should make a Doctrine Product_X_AttributeGroup class and get all the groups i one sql
            $groups = $product->getAttributeGroups();
            $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway;
            foreach ($groups as $key => $group) {
                // Make sure we only include necessary data
                $return['attribute_groups'][$key]['id'] = $group['id'];
                $return['attribute_groups'][$key]['name'] = $group['name'];
                $attributes = $group_gateway->findById($group['id'])->getAttributesUsedByProduct($product);
                foreach ($attributes as $attribute) {
                    // No products has attribute on stock we remove it from the list.
                    if (isset($attribute_for_sale[$attribute->getId()]) && $attribute_for_sale[$attribute->getId()] == 0) {
                        $is_used = 0;
                    } else {
                        $is_used = 1;
                    }

                    $return['attribute_groups'][$key]['attributes'][] = array(
                        'id' => $attribute->getId(),
                        'name' => $attribute->getName(),
                        'is_used' => $is_used
                    );
                }
            }
        }

        return $this->prepareResponseData($return);
    }

    /**
     * Gets related products
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $id          Product id
     *
     * @return array
     */
    public function getRelatedProducts($credentials, $shop_id, $product_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product_id = $this->processRequestData(intval($product_id));

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            $currencies = $currency_gateway->findAllWithExchangeRate();
        } else {
            $currencies = false;
        }

        $product = new Product($this->kernel, $product_id);
        return $this->prepareResponseData($this->cleanUpProductList($product->getRelatedProducts($currencies, 'webshop')));
    }

   /**
     * Gets featured products
     *
     * Method is experimental and only used by discimport.dk. If you need to use it
     * as well, please contact lars@intraface.dk.
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getFeaturedProducts($credentials, $shop_id)
    {
        $related_products = array();

        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($db)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException($db->getMessage() . $db->getUserInfo(), -1);
        }

        $featured = new Intraface_modules_shop_FeaturedProducts($this->kernel->intranet, $this->webshop->getShop(), $db);
        $all = $featured->getAll();

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            $currencies = $currency_gateway->findAllWithExchangeRate();
        } else {
            $currencies = false;
        }

        $related_products = array();

        foreach ($all as $row) {
            $product_gateway = new Intraface_modules_product_Gateway($this->kernel);
            $product_gateway->getDBQuery()->setFilter('keywords', array($row['keyword_id']));

            $related_products[] = array(
                'title' => $row['headline'],
                'products' => $this->cleanUpProductList($product_gateway->getAllProducts('webshop', $currencies))
            );
        }

        return $this->prepareResponseData($related_products);
    }

   /**
     * Gets product keywords which can be used to sort ones webshop
     *
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array with id and keywords
     */
    function getProductKeywords($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);
        $this->_factoryWebshop($shop_id);

        $product = new Product($this->kernel);
        $keywords = $product->getKeywordAppender();
        return $this->prepareResponseData($keywords->getUsedKeywords());
    }

    /**
     * Returns the categories for the shop
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array with categories
     *
     */
    public function getProductCategories($credentials, $shop_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop($shop_id);
        $category = new Intraface_Category(
            $this->kernel,
            MDB2::singleton(DB_DSN),
            new Intraface_Category_Type('shop', $shop_id)
        );

        return $this->prepareResponseData($category->getAllCategories());
    }

    /**
     * Returns the pictures for the product categories for the shop
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id     Id for the shop
     * @param integer $category_id Categori id
     *
     * @return array with pictures for categories
     */
    public function getProductCategoryPicture($credentials, $shop_id, $category_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop($shop_id);
        $module = $this->kernel->useModule('filemanager');
        require_once 'Intraface/shared/filehandler/AppendFile.php';

        $pictures = array();

            $append_file = new AppendFile($this->kernel, 'category', $category_id);
            $appendix_list = $append_file->getList();
        foreach ($appendix_list as $key => $appendix) {
            $tmp_filehandler = new FileHandler($this->kernel, $appendix['file_handler_id']);
            $pictures[$key]['id']                   = $appendix['file_handler_id'];
            $pictures[$key]['original']['icon_uri'] = $tmp_filehandler->get('icon_uri');
            $pictures[$key]['original']['name']     = $tmp_filehandler->get('file_name');
            $pictures[$key]['original']['width']    = $tmp_filehandler->get('width');
            $pictures[$key]['original']['height']   = $tmp_filehandler->get('height');
            $pictures[$key]['original']['file_uri'] = $tmp_filehandler->get('file_uri');
            $pictures[$key]['appended_file_id']     = $appendix['id'];

            if ($tmp_filehandler->get('is_image')) {
                $tmp_filehandler->createInstance();
                $instances = $tmp_filehandler->instance->getList('include_hidden');
                foreach ($instances as $instance) {
                    $pictures[$key][$instance['name']]['file_uri'] = $instance['file_uri'];
                    $pictures[$key][$instance['name']]['name']     = $instance['name'];
                    $pictures[$key][$instance['name']]['width']    = $instance['width'];
                    $pictures[$key][$instance['name']]['height']   = $instance['height'];
                }
            }
            //$tmp_filehandler->__destruct();
            unset($tmp_filehandler);
        }

        return $this->prepareResponseData($pictures);
    }

    /**
     * Add product to basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $produt_id         Product id to add
     * @param integer $product_variation_id Product variation id to change
     * @param integer $quantity          Optional quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return boolean
     */
    public function addProductToBasket($credentials, $shop_id, $product_id, $product_variation_id, $quantity = 1, $text = '', $product_detail_id = 0)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($product_id)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id must be an integer', -5);
        }

        $product_id = $this->processRequestData(intval($product_id));
        $product_variation_id = $this->processRequestData(intval($product_variation_id));
        $quantity = $this->processRequestData(intval($quantity));
        $text = $this->processRequestData($text);
        $product_detail_id = $this->processRequestData(intval($product_detail_id));

        return $this->prepareResponseData(
            $this->webshop->getBasket()->add($product_id, $product_variation_id, $quantity, $text, $product_detail_id)
        );
    }

    /**
     * Change the quantity of one product in basket
     *
     * @param struct  $credentials       Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param integer $product_id        Product id to change
     * @param integer $product_variation_id Product_variation_id to change
     * @param integer $quantity          New quantity
     * @param string  $text              Extra text to the itemline
     * @param integer $product_detail_id Product detail id
     *
     * @return mixed
     */
    public function changeProductInBasket($credentials, $shop_id, $product_id, $product_variation_id, $quantity, $text = '', $product_detail_id = 0)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_numeric($product_id) and !is_numeric($quantity)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('product id and quantity must be integers', -5);
        }

        $product_id = intval($product_id);
        $product_variation_id = intval($product_variation_id);
        $quantity = intval($quantity);
        $text = $this->processRequestData($text);
        $product_detail_id = intval($product_detail_id);

        if (!$this->webshop->getBasket()->change($product_id, $product_variation_id, $quantity, $text, $product_detail_id)) {
            return false;
        }

        return true;
    }

    /**
     * Gets an array with the current basket
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id     Id for the shop
     * @param struct  $customer    customer values
     *
     * @return array
     */
    public function getBasket($credentials, $shop_id, $customer = array())
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer = $this->processRequestData($customer);

        // we put the possibility for BasketEvaluation not to be run.
        if (is_string($customer) && $customer == 'no_evaluation') {
            // nothing happens
        } elseif (is_array($customer)) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation(MDB2::singleton(DB_DSN), $this->webshop->kernel->intranet, $this->webshop->shop);
            if (!$basketevaluation->run($this->webshop->getBasket(), $customer)) {
                // We should see to return the result in some way.
            }
        }

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            $currencies = $currency_gateway->findAllWithExchangeRate();
        } else {
            $currencies = false;
        }

        return $this->prepareResponseData(array(
            'items' => $this->webshop->getBasket()->getItems($currencies),
            'total_price' => $this->webshop->getBasket()->getTotalPriceInCurrencies($currencies),
            'weight' => $this->webshop->getBasket()->getTotalWeight()
        ));
    }

    /**
     * Places an order in Intraface based on the current basket
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param struct $values      Values to save
     *
     * @return integer $order_id
     */
    public function placeOrder($credentials, $shop_id, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $values = $this->processRequestData($values);

        if (!is_array($this->webshop->getBasket()->getItems()) or count($this->webshop->getBasket()->getItems()) <= 0) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be sent - cart is empty', -4);
        }

        if (empty($values['description'])) {
            $values['description'] = $this->webshop->getShop()->getName();
        }

        if (!$order_id = $this->webshop->placeOrder($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('order could not be placed. It returned the following error: ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData($this->webshop->getOrderIdentifierKey());
    }

    /**
     * Saves buyer details
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param struct $values      Values to save
     *
     * @return boolean true or false
     */
    public function saveAddress($credentials, $shop_id, $values)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        if (!is_array($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('details could not be saved - nothing to save', -4);
        }

        $values = $this->processRequestData($values);

        if (!$this->webshop->getBasket()->saveAddress($values)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }

    /**
     * Get buyer details
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getAddress($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getAddress());
    }

    /**
     * Saves customer coupon
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_coupon Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerCoupon($credentials, $shop_id, $customer_coupon)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_coupon = $this->processRequestData($customer_coupon);
        if (!$this->webshop->getBasket()->saveCustomerCoupon($customer_coupon)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer coupon
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerCoupon($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerCoupon());
    }

    /**
     * Saves customer EAN location number
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_ean Customer EAN to save
     *
     * @return boolean true or false
     */
    public function saveCustomerEan($credentials, $shop_id, $customer_ean)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_ean = $this->processRequestData($customer_ean);
        if (!$this->webshop->getBasket()->saveCustomerEan($customer_ean)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('ean could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer EAN location number
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerEan($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerEan());
    }

    /**
     * Saves customer comment
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $customer_comment Customer coupon to save
     *
     * @return boolean true or false
     */
    public function saveCustomerComment($credentials, $shop_id, $customer_comment)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $customer_comment = $this->processRequestData($customer_comment);
        if (!$this->webshop->getBasket()->saveCustomerComment($customer_comment)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }


    /**
     * Get customer comment
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getCustomerComment($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getCustomerComment());
    }

    /**
     * Get possible payment methods
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getPaymentMethods($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getPaymentMethods());
    }

    /**
     * Saves payment method
     *
     * @param struct $credentials     Credentials to use the server
     * @param integer $shop_id    Id for the shop
     * @param string $payment_method Payment method to save
     *
     * @return boolean true or false
     */
    public function savePaymentMethod($credentials, $shop_id, $payment_method)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        $payment_method = $this->processRequestData($payment_method);
        if (!$this->webshop->getBasket()->savePaymentMethod($payment_method)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('datails could not be saved ' . strtolower(implode(', ', $this->webshop->error->getMessage())), -4);
        }

        return $this->prepareResponseData(true);
    }

    /**
     * Returns selected payment method
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getPaymentMethod($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getBasket()->getPaymentMethod());
    }

    /**
     * Get receipt text
     *
     * @param struct $credentials Credentials to use the server
     * @param integer $shop_id    Id for the shop
     *
     * @return array
     */
    public function getReceiptText($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);
        return $this->prepareResponseData($this->webshop->getReceiptText());
    }

    /**
     * Get url for terms of trade
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id     Id for the shop
     *
     * @return string
     */
    public function getTermsOfTradeUrl($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getShop()->getTermsOfTradeUrl());
    }

    /**
     * Get default currency.
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id     Id for the shop
     *
     * @return string
     */
    public function getCurrency($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->getCurrencyArray());
    }

    /**
     * Returns currency gateway if there is access to it
     */
    private function getCurrencyGateway()
    {
        if ($this->webshop->kernel->intranet->hasModuleAccess('currency')) {
            $this->webshop->kernel->useModule('currency', true); // true: ignore intranet access
            return new Intraface_modules_currency_Currency_Gateway($this->doctrine);
        }
        return false;
    }

    /**
     * Returns an array with currency information.
     *
     * @return array default currency and valid currencies
     */
    private function getCurrencyArray()
    {
        $currency['default'] = 'DKK';
        $currency['currencies']['DKK'] = 'Danish Krone';

        if (false !== ($currency_gateway = $this->getCurrencyGateway())) {
            foreach ($currency_gateway->findAllWithExchangeRate() as $c) {
                $currency['currencies'][$c->getType()->getIsoCode()] = $c->getType()->getDescription();
            }
        }

        if (false !== ($default_currency = $this->webshop->getShop()->getDefaultCurrency($currency_gateway))) {
            $currency['default'] = $default_currency->getType()->getIsoCode();
        }

        return $currency;
    }

    /**
     * Get identifier
     *
     * @param struct  $credentials Credentials to use the server
     * @param integer $shop_id     Id for the shop
     *
     * @return string
     */
    public function getIdentifier($credentials, $shop_id)
    {
        $this->checkCredentials($credentials);

        $this->_factoryWebshop($shop_id);

        return $this->prepareResponseData($this->webshop->getShop()->getIdentifier());
    }

    /**
     * Gets information about the company
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    public function getCompanyInformation($credentials)
    {
        $this->checkCredentials($credentials);
        $address = array();
        $address['name'] = $this->kernel->getIntranet()->getAddress()->get('name');
        $address['address'] = $this->kernel->getIntranet()->getAddress()->get('address');
        $address['postcode'] = $this->kernel->getIntranet()->getAddress()->get('postcode');
        $address['city'] = $this->kernel->getIntranet()->getAddress()->get('city');
        $address['cvr'] = $this->kernel->getIntranet()->getAddress()->get('cvr');
        return  $this->prepareResponseData($address);
    }

    /**
     * Initialize the webshop
     *
     * @return void
     */
    private function _factoryWebshop($shop_id)
    {
        if (!$this->kernel->intranet->hasModuleAccess('shop')) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet does not have access to the module webshop', -2);
        }
        $this->kernel->module('shop');

        $gateway = new Intraface_modules_shop_ShopGateway($this->doctrine);
        $shop = $gateway->findById($shop_id);
        if ($shop === false) {
            throw new XML_RPC2_FaultException('Could not find shop', 1);
        }
        $this->webshop = new Intraface_modules_shop_Coordinator($this->kernel, $shop, $this->credentials['session_id']);
    }

    private function getDoctrine()
    {
        return $this->doctrine;
    }
}

/**
 * Stub class used to serve Stock which expects old implementation of product.
 * @author sune
 *
 */
class Intraface_XMLRPC_Shop_Server0100_Product
{

    public $kernel;
    private $product;

    public function __construct($product, $kernel)
    {
        $this->kernel = $kernel;
        $this->product = $product;
    }

    public function get($value)
    {
        if ($value == 'id') {
            return $this->getId();
        }

        throw new Exception('Value '.$value.' not implemented!');
    }

    public function getId()
    {
        return $this->product->getId();
    }
}
