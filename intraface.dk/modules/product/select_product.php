<?php
require('../../include_first.php');

$product_module = $kernel->module("product");
$translation = $kernel->getTranslation('product');

// hente liste med produkter - b�r hentes med getList!

$redirect = Redirect::factory($kernel, 'receive');

if($redirect->get('id') != 0) {
    $multiple = $redirect->isMultipleParameter('product_id');
    if(isset($_GET['set_quantity']) && (int)$_GET['set_quantity'] == 1) {
        $quantity = 1;
    } else {
        $quantity = 0;
    }
} else {
    trigger_error("Der mangler en gyldig redirect", E_USER_ERROR);
}

if(isset($_GET['add_new'])) {
    $add_redirect = Redirect::factory($kernel, 'go');
    $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$quantity);
    $add_redirect->askParameter('product_id');
    header('location: '.$url);
    exit;
}

if(isset($_GET['return_redirect_id'])) {
    $add_redirect = Redirect::factory($kernel, 'return');
    if($add_redirect->getParameter('product_id') != 0) {
        $redirect->setParameter('product_id', $add_redirect->getParameter('product_id'), 1);
    }
    // $product_id[] = $add_redirect->getParameter('product_id');
}

if(isset($_POST['submit']) || isset($_POST['submit_close'])) {
    if($multiple && is_array($_POST['selected'])) {
        foreach($_POST['selected'] AS $selected_id => $selected_value) {
            if((int)$selected_value > 0) {
                // Hvis der allerede er gemt en v�rdi, s� starter vi med at fjerne den, s� der ikke kommer flere p�.
                $redirect->removeParameter('product_id', $selected_id);
                if($quantity) {
                    $redirect->setParameter('product_id', $selected_id, $selected_value);
                } else {
                    $redirect->setParameter('product_id', $selected_id);
                }
            }
        }
    } elseif(!$multiple && (int)$_POST['selected'] != 0) {
        if($quantity) {
            $redirect->setParameter('product_id', (int)$_POST['selected'], (int)$_POST['quantity']);
        } else {
            $redirect->setParameter('product_id', (int)$_POST['selected']);
        }
    }

    if(isset($_POST['submit_close'])) {
        header('location: '.$redirect->getRedirect('index.php')); // index.php, ja hvor skal man ellers hen hvis der er fejl i redirect
        exit;
    }
}

$product = new Product($kernel);
$keywords = $product->getKeywordAppender();

if(isset($_GET["search"]) || isset($_GET["keyword_id"])) {

    if(isset($_GET["search"])) {
        $product->getDBQuery()->setFilter("search", $_GET["search"]);
    }

    if(isset($_GET["keyword_id"])) {
        $product->getDBQuery()->setKeyword($_GET["keyword_id"]);
    }
} else {
    $product->getDBQuery()->useCharacter();
}

$product->getDBQuery()->defineCharacter("character", "detail.name");
$product->getDBQuery()->usePaging("paging");
$product->getDBQuery()->storeResult("use_stored", "select_product", "sublevel");
$product->getDBQuery()->setExtraUri('set_quantity='.$quantity);

$list = $product->getList();
$product_values = $redirect->getParameter('product_id', 'with_extra_value');
$selected_products = array();
if(is_array($product_values)) {
    foreach($product_values AS $selection) {
        $selected_products[$selection['value']] = $selection['extra_value'];
    }
}

$page = new Page($kernel);
//$page->includeJavascript('module', 'add_related.js');
$page->start(t('select product'));
?>
<h1><?php e(t('select product')); ?></h1>

<?php if ($product->isFilledIn() == 0): ?>
    <p><?php e(t('no products to select.')); ?> <a href="select_product.php?add_new=true&amp;set_quantity=<?php print(intval($quantity)); ?>"><?php e(t('create product')); ?></a>.</p>
<?php else: ?>

    <ul class="options">
        <li><a href="select_product.php?add_new=true&amp;set_quantity=<?php print(intval($quantity)); ?>">Opret produkt</a></li>
    </ul>

    <form action="<?php echo basename(__FILE__); ?>" method="get">
        <fieldset>
            <legend><?php e(t('search', 'common')); ?></legend>
            <label><?php e(t('search for')); ?>
            <input type="text" value="<?php e($product->getDBQuery()->getFilter("search")); ?>" name="search" id="search" />
        </label>
        <label>
            <?php e(t('show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('none', 'common')); ?></option>
                <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
                <option value="<?php e($k['id']); ?>" <?php if($k['id'] == $product->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span><input type="submit" value="<?php e(t('search', 'common')); ?>" class="search" /><input type="hidden" name="set_quantity" value="<?php e($quantity); ?>" /></span>
        </fieldset>
        <br style="clear: both;" />
    </form>

    <?php
    echo $product->getDBQuery()->display('character');
    ?>
    <form action="<?php e($_SERVER['PHP_SELF']); ?>?set_quantity=<?php e($quantity); ?>" method="post">
        <table summary="Produkter" class="stripe">
            <caption><?php e(t('products')); ?></caption>
            <thead>
                <tr>
                    <th><?php if($multiple && $quantity): e(t('Quantity')); else: echo e(t('Choose')); endif; ?></th>
                    <th><?php e(t('Product number')); ?></th>
                    <th><?php e(t('Name')); ?></th>
                    <th><?php e(t('Unit type')); ?></th>
                    <?php if($kernel->user->hasModuleAccess('stock')): ?>
                    <th><?php e(t('Stock')); ?>r</th>
                    <?php endif; ?>
                    <th><?php e(t('Vat')); ?></th>
                    <th><?php e(t('Price')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list AS $p): ?>
                <tr>
                    <td>
                        <?php if($multiple && $quantity): ?>
                            <input id="<?php e($p['id']); ?>" type="text" name="selected[<?php echo intval($p['id']); ?>]" value="<?php if(isset($selected_products[$p['id']])): print(intval($selected_products[$p['id']])); else: print('0'); endif; ?>" size="2" />
                        <?php elseif($multiple && !$quantity): ?>
                            <input id="<?php e($p['id']); ?>" type="checkbox" name="selected[<?php echo intval($p['id']); ?>]" value="1" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php elseif(!$multiple): ?>
                            <input id="<?php e($p['id']); ?>" type="radio" name="selected" value="<?php echo intval($p['id']); ?>" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php endif; ?>
                    </td>
                    <td><?php e($p['number']); ?></td>
                    <td><?php e($p['name']); ?></td>
                    <td><?php if(!empty($p['unit']['combined'])) e(t($p['unit']['combined'])); ?></td>
                    <?php if($kernel->user->hasModuleAccess('stock')): ?>
                        <td><?php if($p['stock'] == 0): e("-"); elseif(isset($p['stock_status']['for_sale'])): e($p['stock_status']['for_sale']); else: echo 0; endif; ?></td>
                    <?php endif; ?>
                    <td><?php if ($p['vat'] == 1) e('yes'); else e('no'); ?></td>
                  <td class="amount"><?php e(number_format($p['price'], 2, ",", ".")); ?></td>
                </tr>
                <?php  endforeach; ?>
            </tbody>
        </table>
      <p>
        <?php if(!$multiple && $quantity): ?>
            <?php e(t('quantity')); ?>: <input type="text" name="quantity" value="1" />
        <?php endif; ?>
        <?php if($multiple): ?>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
        <?php endif; ?>
        <input type="submit" name="submit_close" value="<?php e(t('save and close', 'common')); ?>" /></p>

      <?php echo $product->getDBQuery()->display('paging'); ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>