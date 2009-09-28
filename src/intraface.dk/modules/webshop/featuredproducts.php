<?php
require('../../include_first.php');
require_once 'Intraface/shared/keyword/Keyword.php';
require_once 'Intraface/modules/product/Product.php';

$webshop_module = $kernel->module('webshop');
$translation = $kernel->getTranslation('webshop');

$webshop_module->includeFile('FeaturedProducts.php');

if (!empty($_POST)) {
    $featured = new Intraface_Webshop_FeaturedProducts($kernel->intranet, $db);
    if ($featured->add($_POST['headline'], new Keyword(new Product($kernel), $_POST['keyword_id']))) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
} elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $featured = new Intraface_Webshop_FeaturedProducts($kernel->intranet, $db);
    if ($featured->delete($_GET['delete'])) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$featured = new Intraface_Webshop_FeaturedProducts($kernel->intranet, $db);
$all = $featured->getAll();

$page = new Intraface_Page($kernel);
$page->start(__('featured products'));

?>
<h1><?php e(__('featured products')); ?></h1>

<table>
    <caption><?php e(__('featured products')); ?></caption>
    <thead>
    <tr>
        <th>Overskrift</th>
        <th>N�gleord</th>
        <th></th>
    </tr>
    </thead>
<?php foreach ($all as $feature): ?>
    <tr>
        <td><?php e($feature['headline']); ?></td>
        <td>
        <?php
            $keyword = new Keyword(new Product($kernel), $feature['keyword_id']);
            e($keyword->getKeyword());
        ?>
        </td>
        <td><a href="<?php e($_SERVER['PHP_SELF']); ?>?delete=<?php e($feature['id']); ?>" class="delete">Slet</a></td>
    </tr>
<?php endforeach; ?>
</table>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="POST">
    <label for="headline">Headline</label> <input id="headline" type="text" name="headline" />
    <label for="keyword_id">Keyword</label>
        <?php
        $keyword_object = new Intraface_Keyword_Appender(new Product($kernel));
        $keywords = $keyword_object->getAllKeywords();
        ?>

    <select id="keyword_id" name="keyword_id">
        <option value="">V�lg...</option>
        <?php foreach ($keywords as $keyword): ?>
        <option value="<?php e($keyword['id']); ?>"><?php e($keyword['keyword']); ?></option>
        <?php endforeach; ?>

    </select>
    <input type="submit" class="save" name="submit" value="<?php e(__('save', 'common')); ?>" /> <?php e(__('or', 'common')); ?> <a href="./"><?php e(__('cancel', 'common')); ?></a>
</form>

<?php
$page->end();
?>