<?php
class Intraface_modules_product_Controller_Selectproduct extends Intraface_modules_product_Controller_Index
{
    protected $product;
    public $multiple = false;
    public $quantity = false;
    public $selected_products;
    protected $init_url;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        $this->quantity = $this->query('set_quantity');
        $this->multiple = $this->query('multiple');
        $this->url_state->set('set_quantity', $this->query('set_quantity'));
        $this->url_state->set('multiple', $this->query('multiple'));
        $this->url_state->set('use_stored', 'true');
        return parent::dispatch();
    }

    function renderHtml()
    {
        $product_module = $this->getKernel()->module("product");

        if ($this->query('add_new')) {
            $add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $add_redirect->setIdentifier('add_new');
            $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$this->getRedirect()->get('redirect_query_string').'&set_quantity='.$this->quantity);
            $add_redirect->askParameter('product_id');
            return new k_SeeOther($url);
        }

        if ($this->query('select_variation')) {
            $variation_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $variation_redirect->setIdentifier('select_variation');
            $url = $variation_redirect->setDestination($product_module->getPath().'select_product_variation.php?product_id='.intval($_GET['select_variation']).'&set_quantity='.$this->quantity, $product_module->getPath().'select_product.php?'.$this->getRedirect()->get('redirect_query_string').'&set_quantity='.$this->quantity);
            $type = ($this->multiple) ? 'multiple' : 'single';
            $variation_redirect->askParameter('product_variation_id', $type);
            return new k_SeeOther($url);
        }

        if ($this->query('return_redirect_id')) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($return_redirect->getIdentifier() == 'add_new' && $return_redirect->getParameter('product_id') != 0) {
                $redirect->setParameter('product_id', serialize(array('product_id' => intval($return_redirect->getParameter('product_id')), 'product_variation_id' => 0)), 1);
            } elseif ($return_redirect->getIdentifier() == 'select_variation') {
                // Returning from variations page and add the variations to the product_id parameter.
                $product_variations = $return_redirect->getParameter('product_variation_id', 'with_extra_value');
                if ($this->multiple) {
                    foreach ($product_variations as $product_variation) {
                        $redirect->removeParameter('product_id', $product_variation['value']);
                        if ($this->quantity) {
                            $redirect->setParameter('product_id', $product_variation['value'], $product_variation['extra_value']);
                        } else {
                            $redirect->setParameter('product_id', $product_variation['value']);
                        }
                    }
                } else {
                    $redirect->removeParameter('product_id', $product_variations['value']);
                    if ($this->quantity) {
                        $redirect->setParameter('product_id', $product_variations['value'], $product_variations['extra_value']);
                    } else {
                        $redirect->setParameter('product_id', $product_variations['value']);
                    }
                }
            }
        }

        $product = new Product($this->getKernel());
        $keywords = $product->getKeywordAppender();

        $list = $this->getProducts();

        // @todo where should this get its values from
        $product_values = array();
        $this->selected_products = array();
        if (!empty($product_values) and is_array($product_values)) {
            if ($this->multiple) {
                foreach ($product_values as $selection) {
                    $selection['value'] = unserialize($selection['value']);
                    $this->selected_products[$selection['value']['product_id']] = $selection['extra_value'];
                }
            } else {
                $this->selected_products[$product_values['value']['product_id']] = $product_values['extra_value'];
            }
        }

        $data = array('products' => $list);

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/selectproduct');
        return $smarty->render($this, $data);
    }

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProducts()
    {
        if ($this->query("search") || $this->query("keyword_id")) {
            if ($this->query("search")) {
                $this->getProduct()->getDBQuery()->setFilter("search", $this->query("search"));
            }

            if ($this->query("keyword_id")) {
                $this->getProduct()->getDBQuery()->setKeyword($this->query("keyword_id"));
            }
        } else {
            $this->getProduct()->getDBQuery()->useCharacter();
        }

        $this->getProduct()->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $this->getProduct()->getDBQuery()->usePaging("paging");
        $this->getProduct()->getDBQuery()->storeResult("use_stored", "select_product", "sublevel");
        $this->getProduct()->getDBQuery()->setExtraUri('set_quantity='.$this->quantity);
        $this->getProduct()->getDBQuery()->setUri($this->url());
        return  $list = $this->getProduct()->getList();
    }

    function getProduct()
    {
        if (is_object($this->product)) {
            return $this->product;
        }
        return $this->product = new Product($this->getKernel());
    }

    function getKeywords()
    {
        return $keywords = $this->getProduct()->getKeywordAppender();
    }

    function addItem(array $product, $quantity = 1)
    {
        $this->context->addItem($product, $quantity);
    }

    function putForm()
    {
        if ($this->body('submit') || $this->body('submit_close')) {
            if ($this->multiple) {
                if (is_array($this->body('selected'))) {
                    foreach ($this->body('selected') as $selected_id => $selected_value) {
                        if ($selected_value != '' && $selected_value != '0') {
                            $product = array(
                                'product_id' => $selected_id,
                                'product_variation_id' => 0);
                            $this->addItem($product, $selected_value);
                        }
                    }
                }
            } else {
                if ((int)$this->body('selected') != 0) {
                    $product = array(
                        'product_id' => (int)$this->body('selected'),
                        'product_variation_id' => 0);
                    $this->addItem($product, (int)$this->body('quantity'));
                }
            }

            if ($this->body('submit_close')) {
                return new k_SeeOther($this->url('../', array('from' => 'select_product')));
            }

            return new k_SeeOther($this->url(null, array('use_stored' => true, 'set_quantity' => $this->quantity, 'multiple' => $this->multiple)));
        }
    }

    function getPostRedirectUrl()
    {
        return $this->url();
    }
}
