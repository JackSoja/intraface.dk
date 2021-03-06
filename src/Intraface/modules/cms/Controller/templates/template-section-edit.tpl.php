<h1><?php e(t('Edit template section')); ?></h1>

<?php echo $section->error->view(array($context, 't')); ?>

<form method="post" action="<?php e(url()); ?>"  enctype="multipart/form-data">
    <input name="id" type="hidden" value="<?php e($section->get('id')); ?>" />
    <input name="template_id" type="hidden" value="<?php e($section->template->get('id')); ?>" />
    <input name="type" type="hidden" value="<?php e($section->get('type')); ?>" />
    <fieldset>
        <legend><?php e(t('Information about section')); ?></legend>
        <div class="formrow">
            <label for=""><?php e(t('Template section name')); ?></label>
            <input type="text" name="name" value="<?php if (!empty($value['name'])) {
                e($value['name']);
} ?>" />
        </div>
        <div class="formrow">
            <label for=""><?php e(t('Identifier')); ?></label>
            <input type="text" name="identifier" value="<?php  if (!empty($value['identifier'])) {
                e($value['identifier']);
} ?>" />
        </div>
    </fieldset>

<?php

// disse elementtyper skal svare til en elementtype i en eller anden fil.
switch ($value['type']) {
    case 'shorttext':
?>
        <fieldset>
            <legend><?php e(t('Information about shorttext')); ?></legend>
            <div class="formrow">
                <label><?php e(t('number of allowed characters - max 255')); ?></label>
                <input name="size" type="text" value="<?php  if (!empty($value['size'])) {
                    e($value['size']);
} ?>" />
            </div>
        </fieldset>
        <?php

        break;

    case 'longtext':
        if (empty($value['html_format'])) {
            $value['html_format'] = array ();
        }
?>
        <fieldset>
            <legend><?php e(t('Information about longtext')); ?></legend>
            <div class="formrow">
                <label><?php e(t('Number of allowed characters')); ?></label>
                <input name="size" type="text" value="<?php if (!empty($value['size'])) {
                    e($value['size']);
} ?>" />
            </div>
        </fieldset>
        <fieldset>
            <legend><?php e(t('Allowed html tags')); ?></legend>
            <?php foreach ($section->getAllowedHTMLOptions() as $html) : ?>
                <input id="html-format-<?php e($html); ?>" type="checkbox" value="<?php e($html); ?>" name="html_format[]" <?php if (in_array($html, $value['html_format'])) {
                    echo ' checked="checked"';
} ?> />
                <label for="<?php e($html); ?>"><<?php e($html); ?>><?php e(t($html)); ?></<?php e($html); ?>></label>
            <?php endforeach; ?>
        </fieldset>
        <?php

        break;

    case 'picture':
        $kernel->useModule('filemanager');
        require_once 'Intraface/modules/filemanager/InstanceManager.php';
        $instancemanager = new InstanceManager($kernel);
        $instances = $instancemanager->getList();
?>
        <fieldset>
            <legend><?php e(t('Information about picture')); ?></legend>
            <div class="formrow">
                <label for="pic_size"><?php e(t('Picture size')); ?></label>
                <select name="pic_size">
                    <option value="original"<?php if (!empty($value['pic_size']) and $value['pic_size'] == 'original') {
                        echo ' selected="selected"';
} ?>>original</option>
                    <?php foreach ($instances as $instance) : ?>
                    <option value="<?php e($instance['name']); ?>"<?php if (!empty($value['pic_size']) and $value['pic_size'] == $instance['name']) {
                        echo ' selected="selected"';
} ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
        <?php

        break;
    case 'mixed':
?>
        <fieldset>
            <legend><?php e(t('Mixed allowed elements')); ?></legend>
                <?php

                $element_types = $cms_module->getSetting('element_types');
                foreach ($element_types as $key => $v) : ?>
                        <div class="radio">
                                <input name="allowed_element[]" type="checkbox" id="allowed_element_<?php e($key); ?>" value="<?php e($key); ?>"

                            <?php
                            if (isset($value['allowed_element']) && is_array($value['allowed_element']) and in_array($key, $value['allowed_element'])) {
                                echo ' checked="checked"';
                            }
                            ?>
                    />
                            <label for="allowed_element_<?php e($key); ?>"><?php e(t($v)); ?></label>
            </div>
                        <?php                                                                                                                                                                                                                                                                                                                                                                                                                                         endforeach; ?>

        </fieldset>
        <?php

        break;

    default:
        throw new Exception('"'.$value['type'].'" not allowed');
        break;
}
?>

    <div class="">
        <input type="submit" value="<?php e(t('Save')); ?>" />
        <input type="submit" name="close" value="<?php e(t('Save and close')); ?>" />
        <a href="<?php e(url('../../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>

</form>
