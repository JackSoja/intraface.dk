<?php
require('../../include_first.php');

$product_module = $kernel->module("product");

// hente liste med produkter - b�r hentes med getList!

$redirect = Redirect::factory($kernel, 'receive');

if($redirect->get('id') != 0) {
    $multiple = $redirect->isMultipleParameter('product_id');
    if(isset($_GET['set_quantity']) && (int)$_GET['set_quantity'] == 1) {
        $quantity = 1;
    }
    else {
        $quantity = 0;
    }
}
else {
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
                }
                else {
                    $redirect->setParameter('product_id', $selected_id);
                }
            }
        }
    }
    elseif(!$multiple && (int)$_POST['selected'] != 0) {
        if($quantity) {
            $redirect->setParameter('product_id', (int)$_POST['selected'], (int)$_POST['quantity']);
        }
        else {
            $redirect->setParameter('product_id', (int)$_POST['selected']);
        }
    }

    if(isset($_POST['submit_close'])) {
        header('location: '.$redirect->getRedirect('index.php')); // index.php, ja hvor skal man ellers hen hvis der er fejl i redirect
        exit;
    }
}



$product = new Product($kernel);
$product->createDBQuery();
$keywords = $product->getKeywords();

if(isset($_GET["search"]) || isset($_GET["keyword_id"])) {

    if(isset($_GET["search"])) {
        $product->dbquery->setFilter("search", $_GET["search"]);
    }

    if(isset($_GET["keyword_id"])) {
        $product->dbquery->setKeyword($_GET["keyword_id"]);
    }
}
else {
    $product->dbquery->useCharacter();
}

$product->dbquery->defineCharacter("character", "detail.name");
$product->dbquery->usePaging("paging");
$product->dbquery->storeResult("use_stored", "select_product", "sublevel");
$product->dbquery->setExtraUri('set_quantity='.$quantity);

$list = $product->getList();
$product_values = $redirect->getParameter('product_id', 'with_extra_value');
$selected_products = array();
foreach($product_values AS $selection) {
    $selected_products[$selection['value']] = $selection['extra_value'];
}

$page = new Page($kernel);
//$page->includeJavascript('module', 'add_related.js');
$page->start("V�lg produkt");
?>
<h1>V�lg produkt</h1>

<?php if ($product->isFilledIn() == 0): ?>
    <p>Der er ikke oprettet nogen produkter. <a href="select_product.php?add_new=true&amp;set_quantity=<?php print(intval($quantity)); ?>">Opret produkt</a>.</p>
<?php else: ?>

    <ul class="options">
        <li><a href="select_product.php?add_new=true&amp;set_quantity=<?php print(intval($quantity)); ?>">Opret produkt</a></li>
    </ul>

    <form action="<?php echo basename(__FILE__); ?>" method="get">
        <fieldset>
            <legend>S�gning</legend>
            <label>S�g efter
            <input type="text" value="<?php print(safeToForm($product->dbquery->getFilter("search"))); ?>" name="search" id="search" />
        </label>
        <label>
            Vis med n�gleord
            <select name="keyword_id" id="keyword_id">
                <option value="">Ingen</option>
                <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
                <option value="<?php echo intval($k['id']); ?>" <?php if($k['id'] == $product->dbquery->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php echo safeToForm($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span><input type="submit" value="S�g" class="search" /><input type="hidden" name="set_quantity" value="<?php echo intval($quantity); ?>" /></span>
        </fieldset>
        <br style="clear: both;" />
    </form>

    <?php
    echo $product->dbquery->display('character');
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?set_quantity=<?php echo intval($quantity); ?>" method="post">
        <table summary="Produkter" class="stripe">
            <caption>Produkter</caption>
            <thead>
                <tr>
                    <th><?php if($multiple && $quantity): echo 'Antal'; else: echo 'V�lg'; endif; ?></th>
                    <th>Varenummer</th>
                    <th>Navn</th>
                      <th>Enhed</th>
                    <?php if($kernel->user->hasModuleAccess('stock')): ?>
                        <th>Lager</th>
                    <?php endif; ?>
                    <th>Moms</th>
                  <th>Pris</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($list AS $p): ?>
                <tr>
                    <td>
                        <?php if($multiple && $quantity): ?>
                            <input id="<?php echo intval($p['id']); ?>" type="text" name="selected[<?php echo intval($p['id']); ?>]" value="<?php if(isset($selected_products[$p['id']])): print(intval($selected_products[$p['id']])); else: print('0'); endif; ?>" size="2" />
                        <?php elseif($multiple && !$quantity): ?>
                            <input id="<?php echo intval($p['id']); ?>" type="checkbox" name="selected[<?php echo intval($p['id']); ?>]" value="1" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php elseif(!$multiple): ?>
                            <input id="<?php echo intval($p['id']); ?>" type="radio" name="selected" value="<?php echo intval($p['id']); ?>" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php endif; ?>
                    </td>
                    <td><?php echo safeToHtml($p['number']); ?></td>
                    <td><?php echo safeToHtml($p['name']); ?></td>
                    <td><?php echo safeToHtml($p['unit']); ?></td>
                    <?php if($kernel->user->hasModuleAccess('stock')): ?>
                        <td><?php if($p['stock'] == 0): print("-"); elseif(isset($p['stock_status']['for_sale'])): echo safeToHtml($p['stock_status']['for_sale']); else: echo 0; endif; ?></td>
                    <?php endif; ?>
                    <td><?php if ($p['vat'] == 1) echo 'Ja'; else echo 'Nej'; ?></td>
                  <td class="amount"><?php echo number_format($p['price'], 2, ",", "."); ?></td>
                </tr>
                <?php  endforeach; ?>
            </tbody>
        </table>
      <p>
        <?php if(!$multiple && $quantity): ?>
            Antal: <input type="text" name="quantity" value="1" />
        <?php endif; ?>
        <?php if($multiple): ?>
        <input type="submit" name="submit" value="Gem" />
        <?php endif; ?>
        <input type="submit" name="submit_close" value="Gem og Luk" /></p>

      <?php echo $product->dbquery->display('paging'); ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>