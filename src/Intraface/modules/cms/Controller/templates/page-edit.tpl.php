<h1><?php e(t('Edit '.$type)); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../../', array('type' => $type))); ?>"><?php e(t('Close')); ?></a></li>
    <?php if ($cmspage->get('id') > 0) : ?>
    <li><a href="<?php e(url('.')); ?>"><?php e(t('View page')); ?></a></li>
    <?php endif; ?>
</ul>

<?php echo $cmspage->error->view(array($context, 't')); ?>

<?php if (!is_array($templates) or count($templates) == 0) : ?>

    <p class="message-dependent">
        <?php e(t('you have to create a template for this page type')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')) : ?>
            <a href="<?php e(url('../../template/create')); ?>"><?php e(t('create template')); ?></a>.
        <?php else : ?>
            <strong><?php e(t('please ask your administrator to do create a template')); ?></strong>
        <?php endif; ?>
    </p>

<?php else : ?>

    <form method="post" action="<?php e(url(null, array($context->subview(), 'type' => $type))); ?>">

        <fieldset>
            <legend><?php e(t('about the behavior of the page')); ?></legend>

            <div class="formrow">
                <label for="page-type"><?php e(t('type')); ?></label>
                <div id="static-cms-page-type" style="display: none;"><?php e(t($type)); ?> <?php if (!empty($value['id'])) :
?><a href="#" onClick="page_edit.show_select();" class="edit"><?php e(t('Change type')); ?></a><?php
endif; ?></div>
                <select name="page_type" id="cms-page-type">
                    <?php foreach ($cmspage->getTypes() as $key => $type) : ?>
                    <option value="<?php e($type); ?>"<?php if (!empty($value['type']) and $value['type'] == $type) {
                        echo ' selected="selected"';
} ?>><?php e(t($type)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <?php if (!empty($value['template_id'])) : ?>
                <input type="hidden" name="template_id" value="<?php  if (!empty($value['template_id'])) {
                    e($value['template_id']);
} ?>" />
            <?php elseif (is_array($templates) and count($templates) > 1) : ?>
                <div class="formrow">
                    <label><?php e(t('Choose template')); ?></label>
                    <select name="template_id">
                    <?php foreach ($templates as $template) : ?>
                        <option value="<?php e($template['id']); ?>"><?php e($template['name']); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
            <?php else : ?>
                <input type="hidden" name="template_id" value="<?php e($templates[0]['id']); ?>" />
            <?php endif; ?>
        </fieldset>

        <fieldset>

            <legend><?php e(t('Page information')); ?></legend>

            <div class="formrow" id="titlerow">
                <label for="title"><?php e(t('Title')); ?></label>
                <input name="title" type="text" id="title" value="<?php if (!empty($value['title'])) {
                    e($value['title']);
} ?>" size="50" maxlength="50" onBlur="page_edit.fill_shortlink();" />
            </div>

            <div class="formrow">
                <label for="shortlink"><?php e(t('Unique page address')); ?></label>
                <?php e($cmssite->get('url')); ?><input name="identifier" type="text" id="shortlink" value="<?php if (!empty($value['identifier'])) {
                    e($value['identifier']);
} ?>" size="35" maxlength="50" /> (<?php e(t('only the characters').': a-z 0-9 _ -'); ?>)
                <div class="formrow-description"></div>

            </div>

        </fieldset>

        <?php if (empty($value['type']) or $value['type'] == 'page') : ?>
        <fieldset id="cms-page-info">
            <legend><?php e(t('menu information')); ?></legend>
            <div class="formrow">
                <label for="navigation-name"><?php e(t('name in the navigation')); ?></label>
                <input name="navigation_name" type="text" id="navigation-name" value="<?php if (!empty($value['navigation_name'])) {
                    e($value['navigation_name']);
} ?>" size="50" maxlength="50" />
            </div>

            <?php if (is_array($cmspages) and count($cmspages) > 0) : ?>

            <div class="formrow" id="childof">
                <label for="child_of_id"><?php e(t('choose page is child of')); ?></label>
                <select name="child_of_id" id="child_of_id">
                    <option value="0"><?php e(t('None')); ?></option>
                    <?php foreach ($cmspages as $p) { ?>
                        <?php if (!empty($value['id']) and $p['id'] == $value['id']) {
                            continue;
} ?>
                        <option value="<?php e($p['id']); ?>"
                            <?php if (!empty($value['child_of_id']) and $value['child_of_id'] == $p['id']) {
                                echo ' selected="selected"';
} ?>
                            ><?php e($p['title']); ?></option>
                        <?php } ?>
                </select>
            </div>
            <?php endif; ?>
        </fieldset>
        <?php endif; ?>

        <fieldset>
                <legend><?php e(t('choose picture')); ?></legend>
                <?php
                if (empty($value['pic_id'])) {
                    $value['pic_id'] = 0;
                }
                    $context->getKernel()->useModule('filemanager');
                    $filehandler = new FileHandler($kernel, $value['pic_id']);
                    e('file_id ' . $filehandler->get('id') . ' chosen');
                    //$filehandler_html = new FileHandlerHTML($filehandler);
                    //$filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));
                ?>
                <input type="submit" value="<?php e(t('Choose file')); ?>" name="choose_file"  />
            </fieldset>

        <fieldset id="searchengine-info">
            <legend><?php e(t('metatags for the search engines')); ?></legend>
            <p><?php e(t('this info is directed towards the search engines')); ?></p>
            <div class="formrow">
                <label for="description"><?php e(t('search engine description')); ?></label>
                <textarea name="description" id="description" cols="50" rows="3"><?php  if (!empty($value['description'])) {
                    e($value['description']);
} ?></textarea>
            </div>

            <div class="formrow">
                <label for="keywords"><?php e(t('search engine keywords')); ?></label>
                <input name="keywords" id="keywords" type="text" value="<?php if (!empty($value['keywords'])) {
                    e($value['keywords']);
} ?>" size="50" maxlength="225" /> <?php e(t('separated by comma')); ?>
            </div>
        </fieldset>

        <?php if (false && $kernel->intranet->hasModuleAccess('comment')) : ?>
        <fieldset>
            <legend><?php e(t('comments')); ?></legend>
                <div class="radiorow">
                <label><input type="checkbox" value="1" name="allow_comments"<?php if (!empty($value['allow_comments']) and $value['allow_comments'] == 1) {
                    echo ' checked="checked"';
} ?> /> <?php e(t('users can comment page')); ?></label>
            </div>
        </fieldset>
        <?php endif; ?>

        <fieldset id="date-settings">
            <legend><?php e(t('publish properties')); ?></legend>

            <div class="formrow">
                <label for="date-publish"><?php e(t('publish date')); ?></label>
                <input name="date_publish" id="date-publish" type="text" value="<?php if (!empty($value['date_publish'])) {
                    e($value['date_publish']);
} ?>" size="30" maxlength="225" /> <span id="dateFieldMsg1"><?php e(t('empty is today')); ?></span>
            </div>

            <div class="formrow">
                <label for="date-expire"><?php e(t('expire date')); ?></label>
                <input name="date_expire" id="date-expire" type="text" value="<?php if (!empty($value['date_expire'])) {
                    e($value['date_expire']);
} ?>" size="30" maxlength="225" /> <span id="dateFieldMsg2"><?php e(t('empty never expires')); ?></span>
            </div>

            <div class="radiorow">
                <label><input type="checkbox" value="1" name="hidden" <?php if (!empty($value['hidden']) and $value['hidden'] == 1) {
                    echo ' checked="checked"';
} ?> /> <?php e(t('hide page')); ?></label>
            </div>

            <!--
            <div class="formrow">
                <label for="password"><?php e(t('Password')); ?></label>
                <input type="text" value="<?php if (!empty($value['password'])) {
                    e($value['password']);
} ?>" name="password" />
            </div>
            -->


        </fieldset>

        <div style="clear: both;">
            <input type="submit" value="<?php e(t('Save')); ?>" />
            <input type="submit" name="close" value="<?php e(t('Save and close')); ?>" />
            <input type="submit" name="add_keywords" value="<?php e(t('Save and add keywords')); ?>" />
        </div>
    </form>
<?php endif; ?>
