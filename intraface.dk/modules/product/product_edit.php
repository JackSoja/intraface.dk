<?php
/**
 * @author Lars Olesen <lars@legestue.net>
 */

require '../../include_first.php';

$module = $kernel->module('product');

$redirect = Intraface_Redirect::factory($kernel, 'receive');
$translation = $kernel->getTranslation('product');

if ($kernel->user->hasModuleAccess('accounting')) {
    $mainAccounting = $kernel->useModule('accounting');
}

/*
if (isset($_GET['lock']) AND is_numeric($_GET['lock'])) {
    $product = new Product($kernel, $_GET['lock']);
    $product->lock();
}
elseif (isset($_GET['unlock']) AND is_numeric($_GET['unlock'])) {
    $product = new Product($kernel, $_GET['unlock']);
    $product->unlock();
}
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $var = $_POST;

    $product = new Product($kernel, $_POST['id']);
    if ($id = $product->save($var)) {

        if ($redirect->get('id') != 0) {
            $redirect->setParameter('product_id', $id);
        }

        header('Location: ' . $redirect->getRedirect('product.php?id='.$id));
        exit;

    } else {
        $value = $_POST;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
        $product = new Product($kernel, intval($_GET['id']));
        $value = $product->get();
    } else {
        $product = new Product($kernel);
        $value['number'] = $product->getMaxNumber() + 1;
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('edit product'));
?>

<h1><?php e(t('edit product')); ?></h1>

<?php if ($product->get('locked') == 1) { ?>
    <ul class="formerrors">
      <li>Produktet er l�st og kan ikke opdateres. <a href="edit_product.php?unlock=<?php e($product->get('id')); ?>&amp;id=<?php e($product->get('id')); ?>">L�s op</a>.</li>
   </ul>
<?php } ?>

<?php echo $product->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
    <legend><?php e(t('product information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($value['id'])) e($value['id']); ?>" />
        <div class="formrow">
            <label for="number"><?php e(t('product number')); ?></label>
            <input type="text" name="number" id="number" value="<?php if (!empty($value['number'])) e($value['number']); ?>" />
        </div>
        <div class="formrow">
            <label for="name"><?php e(t('name')); ?></label>
            <input type="text" size="50" name="name" id="name" value="<?php if (!empty($value['name'])) e($value['name']); ?>" />
        </div>
        <div class="formrow">
            <label for="description"><?php e(t('description')); ?></label>
            <textarea class="resizable" rows="8" cols="60" name="description" id="description"><?php if (!empty($value['description'])) e($value['description']); ?></textarea>
        </div>

        <div class="formrow">
            <label for="unit"><?php e(t('unit type')); ?></label>
            <select name="unit" id="unit">
            <?php
                // getting settings
                $unit_options = '';
                $unit_choises  = Product::getUnits();

                foreach ($unit_choises AS $key=>$v) {
                    $unit_options .= '<option value="' . $key . '"';
                    if (!empty($value['unit_id']) AND $value['unit_id'] == $key) { $unit_options .= ' selected="selected"'; }

                    // to avoid trying to translate empty string.
                    if (!empty($v['combined'])) {
                        $unit_options .= '>' . htmlentities(t($v['combined'])) . '</option>';
                    } else {
                        $unit_options .= '></option>';
                    }
                }
                echo $unit_options;

            ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('price information')); ?></legend>
        <div class="formrow">
            <label for="price"><?php e(t('price')); ?></label>
            <input type="text" name="price" id="price" value="<?php if (!empty($value['price'])) e(amountToForm($value['price'])); ?>" /> <?php e(t('excl. vat')); ?>
        </div>

        <div class="formrow">
            <label for="vat"><?php e(t('vat')); ?></label>
            <select name="vat" id="vat">
            <?php
                $vat_choises = array(0 => t('no', 'common'), 1 => t('yes', 'common'));
                $vat_options = '';
                foreach ($vat_choises AS $key=>$v) { ?>
                    <option value="<?php e($key); ?>"
                    <?php if (!empty($value['vat']) AND $value['vat'] == $key) { echo ' selected="selected"'; } ?>
                    ><?php e($v); ?></option>
                <?php } ?>
            </select>
        </div>

    </fieldset>

    <?php if ($kernel->user->hasModuleAccess('webshop') || $kernel->user->hasModuleAccess('shop')): ?>
    <fieldset>
        <legend><?php e(t('Information for shop')); ?></legend>

        <div class="formrow">
            <label for="weight"><?php e(t('weight')); ?></label>
            <input type="text" name="weight" id="weight" value="<?php if (!empty($value['weight'])) e($value['weight']); ?>" /> <?php e(t('grams')); ?>
        </div>
        
        <div class="formrow">
            <label for="before_price"><?php e(t('Before price')); ?></label>
            <input type="text" name="before_price" id="before_price" value="<?php if (!empty($value['before_price']) && $value['before_price'] != 0.00) e(amountToForm($value['before_price'])); ?>" />
        </div>

        <?php if ($kernel->user->hasModuleAccess('shop')): ?>
            <?php if (!isset($value['has_variation'])): ?>
                <div class="formrow">
                    <label for="has_variation"><?php e(t('Product has variations')); ?></label>
                    <select name="has_variation" id="has_variation">
                        <?php
                        foreach (array(0 => t('no', 'common'), 1 => t('yes', 'common')) AS $key=>$v) {
                            ?>
                            <option value="<?php e($key); ?>"
                            <?php if (!empty($value['has_variation']) AND $value['has_variation'] == $key) { echo ' selected="selected"'; } ?>
                            ><?php e($v); ?></option>
                        <?php }
                        ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="formrow">
                    <label for="has_variation"><?php e(t('Product has variations')); ?></label>
                    <input type="hidden" name="has_variation" value="<?php e($value['has_variation']); ?>" />
                    <span id="has_variation">
                        <?php
                        if ($value['has_variation'] == 1) {
                            e('Yes', 'common');
                        } else {
                            e('No', 'common');
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="formrow">
            <label for="do_show"><?php e(t('show in webshop')); ?></label>
            <select name="do_show" id="do_show">
            <?php
                $show_options = '';
                $show_choises = array(0 => t('no', 'common'), 1 => t('yes', 'common'));

                foreach ($show_choises AS $key=>$v) { ?>
                    <option value="<?php e($key); ?>"
                    <?php if (!empty($value['do_show']) AND $value['do_show'] == $key) { echo ' selected="selected"'; } ?>
                    ><?php e($v); ?></option>
                <?php }
            ?>
            </select>
        </div>
        </fieldset>

        <!-- her b�r v�re en tidsangivelse -->

        <?php endif; ?>

        <?php if ($kernel->user->hasModuleAccess('stock')): ?>
        <fieldset>
        <legend><?php e(t('stock')); ?></legend>
        <div class="formrow">
            <label for="stock"><?php e(t('stock product')); ?></label>
            <select name="stock" id="stock">
            <?php
                $stock_options = '';
                $stock_choises = array(0 => t('no', 'common'), 1 => t('yes', 'common'));
                foreach ($stock_choises AS $key=>$v) { ?>
                    <option value="<?php e($key); ?>"
                    <?php if (!empty($value['stock']) AND $value['stock'] == $key) { $stock_options .= ' selected="selected"'; } ?>
                    ><?php e($v); ?></option>
                <?php }
            ?>
            </select>
        </div>
    </fieldset>
    <?php endif; ?>

    <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
    <?php
        $x = 0;
        $year = new Year($kernel);
        $year->loadActiveYear();

        $account = new Account($year);
        $accounts =  $account->getList('sale');

    ?>
    <fieldset>
        <legend><?php e(t('accounting')); ?></legend>

        <?php if (count($accounts) == 0): ?>
            <p><?php e($translation->get('You will need to create an accounting year and create accounts for that year, to be able to set the account for which this product will be stated.')); ?> <a href="<?php e($mainAccounting->getPath()); ?>"><?php e($translation->get('Create accounting year and accounts')); ?></a></p>
        <?php else: ?>

        <div class="formrow">
            <label for="state_account"><?php e(t('state on account')); ?></label>
            <select if="state_account" name="state_account_id">
                <option value=""><?php e(t('choose...', 'common')); ?></option>
                <?php
                    $x = 0;
                    $optgroup = 1;
                    foreach ($accounts AS $a):
                        if (strtolower($a['type']) == 'sum') continue;

                        if (strtolower($a['type']) == 'headline') {
                            continue;
                            /*
                            // det er lidt sv�rt at f� optgroupperne til at passe, hvis man har flere overskrifter i tr�k
                            if ($optgroup == 0) echo '</optgroup>';
                            echo '<optgroup label="'.$a['name'].'">';
                            $x = strtolower($a['type']);
                            $optgroup = 1;
                            continue;
                            */
                        }
                        ?>
                        <option value="<?php e($a['number']); ?>"
                        <?php
                        // @todo er det korrekt at det er number? og m�ske skal et produkt i virkeligheden snarere
                        // gemmes med nummeret en med id - for s� er det noget lettere at opdatere fra �r til �r
                        if (!empty($value['state_account_id']) AND $value['state_account_id'] == $a['number']) echo ' selected="selected"';
                        ?>
                        ><?php e($a['name']); ?></option>
                        <?php
                        $optgroup = 0;
                    endforeach;
                ?>
                <!--</optgroup>-->
            </select>
        </div>
        <?php endif; ?>
    </fieldset>
    <?php endif; ?>

    <div>
        <?php if ($product->get('locked') == 0):  ?>

            <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
    <?php endif; ?>
        <a href="<?php $return = 'index.php'; if (isset($product) && $product->get('id') != 0) $return = 'product.php?id='.intval($product->get('id')); e($redirect->getRedirect($return)); ?>"><?php e(t('Cancel', 'common')); ?></a>

    </div>

</form>

<?php
$page->end();
?>
