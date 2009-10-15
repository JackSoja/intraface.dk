<?php
require('../../include_first.php');

$module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // det kunne godt v�re, at der skulle laves noget s� hvis det er f�rste gang
    // man gemmer et template, s� ryger man p� template.php

    $cmssite = new CMS_Site($kernel, $_POST['site_id']);
    $template = new CMS_Template($cmssite, $_POST['id']);

    if ($template->save($_POST)) {
        if (!empty($_POST['close'])) {
            header('Location: template.php?id='.$template->get('id'));
            exit;
        } else {
            header('Location: template_edit.php?id='.$template->get('id'));
            exit;
        }
    } else {
        $value = $_POST;
        $value['for_page_type'] = array_sum($_POST['for_page_type']);
    }
} elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $template = CMS_Template::factory($kernel, 'id', $_GET['id']);
    $value = $template->get();

} elseif (!empty($_GET['site_id']) AND is_numeric($_GET['site_id'])) {
    $cmssite = new CMS_Site($kernel, $_GET['site_id']);
    $template = new CMS_Template($cmssite);
    $value['site_id'] = $_GET['site_id'];
    $value['for_page_type'] = 7; // all types;
} else {
    trigger_error(__('Not allowed', 'common'), E_USER_ERROR);
}

$page = new Intraface_Page($kernel);
$page->start(__('Edit template'));
?>

<h1><?php e(__('Edit template')); ?></h1>

<?php if (!empty($value['id'])): ?>
<ul class="options">
    <li><a href="template.php?id=<?php e($value['id']); ?>"><?php e(__('view template')); ?></a></li>
</ul>
<?php endif; ?>

<?php
    echo $template->error->view($translation);
?>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>">
    <input name="id" type="hidden" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
    <input name="site_id" type="hidden" value="<?php if (!empty($value['site_id'])) e($value['site_id']); ?>" />

    <fieldset>

        <legend><?php e(__('Template')); ?></legend>

        <div class="formrow" id="titlerow">
            <label for="name"><?php e(__('Template name')); ?></label>
            <input name="name" type="text" id="name" value="<?php if (!empty($value['name'])) e($value['name']); ?>" size="50" maxlength="255" />
        </div>
        <div class="formrow" id="titlerow">
            <label for="identifier"><?php e(__('Identifier', 'common')); ?></label>
            <input name="identifier" type="text" id="name" value="<?php if (!empty($value['identifier'])) e($value['identifier']); ?>" size="50" maxlength="255" />
        </div>

        <div class="formrow" id="titlerow">
            <label><?php e(__('For page type')); ?></label>
            <?php
            require_once 'Intraface/modules/cms/Page.php';
            $page_types = CMS_Page::getTypesWithBinaryIndex();
            foreach ($page_types AS $key => $page_type): ?>
                <label for="for_page_type_<?php e($key); ?>"><input name="for_page_type[]" type="checkbox" id="for_page_type_<?php e($key); ?>" value="<?php e($key); ?>" <?php if (!empty($value['for_page_type']) && $value['for_page_type'] & $key) echo 'checked="checked"'; ?> /><?php e(__($page_type)); ?></label>
            <?php endforeach; ?>
        </div>

    </fieldset>

    <div style="clear: both;">
        <input type="submit" value="<?php e(__('Save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php e(__('Save and close', 'common')); ?>" />
        <?php if (!empty($value['id'])): ?>
            <a href="template.php?id=<?php e($value['id']); ?>"><?php e(__('Cancel', 'common')); ?></a>
        <?php else: ?>
            <a href="templates.php?site_id=<?php e($value['site_id']); ?>"><?php e(__('Cancel', 'common')); ?></a>
        <?php endif; ?>
    </div>
</form>

<?php
$page->end();
?>