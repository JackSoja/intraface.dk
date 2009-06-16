<?php
/**
 * keywords.php
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require '../../include_first.php';

$kernel->useShared('keyword');
$translation = $kernel->getTranslation('keyword');

if (!empty($_REQUEST['product_id']) AND is_numeric($_REQUEST['product_id'])) {
    $object_name = 'Product';
    $module = $kernel->module('product');
    $id = (int)$_REQUEST['product_id'];
    $id_name = 'product_id';
    $redirect = 'product/product';
    $object = new $object_name($kernel, $id);

} elseif (!empty($_REQUEST['contact_id']) AND is_numeric($_REQUEST['contact_id'])) {
    $object_name = 'Contact';
    $module = $kernel->module('contact');
    $id = (int)$_REQUEST['contact_id'];
    $id_name = 'contact_id';
    $redirect = 'contact/contact';
    $object = new $object_name($kernel, $id);

} elseif (!empty($_REQUEST['page_id']) AND is_numeric($_REQUEST['page_id'])) {
    $object_name = 'CMS_Page';
    $module = $kernel->module('cms');
    $id = (int)$_REQUEST['page_id'];
    $id_name = 'page_id';
    $redirect = 'cms/page';
    $object = CMS_Page::factory($kernel, 'id', $id);

} elseif (!empty($_REQUEST['template_id']) AND is_numeric($_REQUEST['template_id'])) {
    $object_name = 'CMS_template';
    $module = $kernel->module('cms');
    $id = (int)$_REQUEST['template_id'];
    $id_name = 'template_id';
    $redirect = 'cms/template';
    $object = CMS_Template::factory($kernel, 'id', $id);

} elseif (!empty($_REQUEST['filemanager_id']) AND is_numeric($_REQUEST['filemanager_id'])) {
    $object_name = 'FileManager';
    $module = $kernel->module('filemanager');
    $id = (int)$_REQUEST['filemanager_id'];
    $id_name = 'filemanager_id';
    $redirect = 'filemanager/file';
    $object = new $object_name($kernel, $id);
} else {
    trigger_error('Der er ikke angivet noget objekt i /shared/keyword/connect.php', E_USER_ERROR);
}

if (!empty($_POST)) {

    $keyword = $object->getKeywordAppender(); // starter keyword objektet

    if (!$keyword->deleteConnectedKeywords()) {
        $keyword->error->set('Kunne ikke slette keywords.');
    }

    // strengen med keywords
    if (!empty($_POST['keywords'])) {
        $appender = new Intraface_Keyword_StringAppender(new Keyword($object), $keyword);
        $appender->addKeywordsByString($_POST['keywords']);
    }

    // listen med keywords
    if (!empty($_POST['keyword']) AND is_array($_POST['keyword']) AND count($_POST['keyword']) > 0) {
        for ($i=0, $max = count($_POST['keyword']); $i < $max; $i++) {
            $keyword->addKeyword(new Keyword($object, $_POST['keyword'][$i]));
        }
    }

    if (!empty($_POST['close'])) {
        header('Location: '.url('/modules/'.$redirect.'.php', array('id' => $id, 'from' => 'keywords#keywords')));
        exit;
    }
      if (!$keyword->error->isError()) {
        //header('Location: connect.php?'.$id_name.'='.$object->get('id'));
        //exit;
    }
}

$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
$redirect = Ilib_Redirect::receive($kernel->getSessionId(), MDB2::singleton(DB_DSN), $options);
$redirect->setDestination(url('/shared/keyword/edit.php'), url('/shared/keyword/connect.php', array($id_name => $object->get('id'))));

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $keyword = new Keyword($object, $_GET['delete']);
    $keyword->delete();
}

$keyword = $object->getKeywordAppender(); // starter objektet
$keywords = $keyword->getAllKeywords(); // henter alle keywords
$keyword_string = $keyword->getConnectedKeywordsAsString();

// finder dem der er valgt
$checked = array();
foreach ($keyword->getConnectedKeywords() AS $key) {
    $checked[] = $key['id'];
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('add keywords to') . ' ' . $object->get('name'));

?>
<h1><?php e($translation->get('add keywords to') . ' ' . $object->get('name')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <?php if (is_array($keywords) AND count($keywords) > 0): ?>
    <fieldset>
        <legend><?php e($translation->get('choose keywords')); ?></legend>
        <input type="hidden" name="<?php e($id_name); ?>" value="<?php e($object->get('id')); ?>" />
        <?php
            $i = 0;
            foreach ($keywords AS $k) { ?>
                <input type="checkbox" name="keyword[]" id="k<?php e($k['id']); ?>" value="<?php e($k['id']); ?>"
                <?php
                if (in_array($k['id'], $checked)) {
                    print ' checked="checked" ';
                } ?>
                />
                <label for="k<?php e($k["id"]); ?>"><a href="edit.php?<?php e($id_name); ?>=<?php e($object->get('id')); ?>&amp;id=<?php e($k['id']); ?>"><?php e($k['keyword']); ?> (#<?php e($k["id"]); ?>)</a></label> - <a href="<?php e($_SERVER['PHP_SELF']); ?>?<?php e($id_name); ?>=<?php e($object->get('id')); ?>&amp;delete=<?php e($k["id"]); ?>" class="confirm"><?php e($translation->get('delete', 'common')); ?></a><br />
        <?php }
        ?>
    </fieldset>
        <div style="clear: both; margin-top: 1em; width:100%;">
            <input type="submit" value="<?php e(t('choose')); ?>" name="submit" class="save" id="submit-save" />
            <input type="submit" value="<?php e(t('choose and close')); ?>" name="close" class="save" id="submit-close" />
        </div>

    <?php endif; ?>
    <fieldset>
        <legend><?php e(t('create keyword')); ?></legend>
        <p><?php e(t('separate keywords by comma')); ?></p>
        <input type="hidden" name="<?php e($id_name); ?>" value="<?php e($object->get('id')); ?>" />
        <label for="keyword"><?php e(t('keywords')); ?></label>
        <input type="text" name="keywords" id="keyword" value="<?php //e($keyword_string); ?>" />
        <input type="submit" value="<?php e(t('save', 'common')); ?>" name="submit" id="submit-save-new" />
    </fieldset>
</form>



<?php
$page->end();
?>
