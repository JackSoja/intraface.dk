<?php
require('../../include_first.php');
$module = $kernel->module('newsletter');
$translation = $kernel->getTranslation('newsletter');

if (!$kernel->user->hasModuleAccess('contact')) {
    trigger_error("Du skal have adgang til kontakt-modullet for at se denne side");
}

$list = new NewsletterList($kernel, (int)$_GET['list_id']);
$subscriber = new NewsletterSubscriber($list);


if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
    if ($kernel->user->hasModuleAccess('contact')) {
        $contact_module = $kernel->useModule('contact');

        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module->getPath()."subscribers.php?list_id=".$list->get('id'));
        $redirect->askParameter('contact_id');
        $redirect->setIdentifier('contact');

        header("Location: ".$url);
        exit;
    } else {
        trigger_error("Du har ikke adgang til modulet contact", ERROR);
    }

} elseif (isset($_GET['remind']) AND $_GET['remind'] == 'true') {
    $subscriber = new NewsletterSubscriber($list, intval($_GET['id']));
    if (!$subscriber->sendOptInEmail(Intraface_Mail::factory())) {
    	trigger_error('Could not send the optin e-mail');
    }
} elseif (isset($_GET['optin'])) {
    $subscriber->getDBQuery()->setFilter('optin', intval($_GET['optin']));
}

if (isset($_GET['return_redirect_id'])) {
    $redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($redirect->get('identifier') == 'contact') {
        $subscriber->addContact(new Contact($kernel, $redirect->getParameter('contact_id')));
    }

}
//
if (isset($_GET['delete']) AND intval($_GET['delete']) != 0) {

    $subscriber = new NewsletterSubscriber($list, $_GET['delete']);
    $subscriber->delete();
}

$subscriber->getDBQuery()->useCharacter();
$subscriber->getDBQuery()->defineCharacter('character', 'newsletter_subscriber.id');
$subscriber->getDBQuery()->usePaging('paging');
$subscriber->getDBQuery()->setExtraUri('&amp;list_id='.$list->get('id'));
$subscriber->getDBQuery()->storeResult("use_stored", 'newsletter_subscribers_'.$list->get("id"), "toplevel");
$subscribers = $subscriber->getList();


$page = new Intraface_Page($kernel);
$page->start('Modtagere');
?>

<h1>Modtagere p� listen <?php e($list->get('title')); ?></h1>

<ul class="options">

    <li><a href="subscribers.php?list_id=<?php e($list->get('id')); ?>&amp;add_contact=1">Tilf�j kontakt</a></li>
    <li><a href="list.php?id=<?php e($list->get('id')); ?>">Luk</a></li>

</ul>

<?php echo $subscriber->error->view(); ?>

<form action="subscribers.php?" method="get" class="search-filter">
    <input type="hidden" name="list_id" value="<?php e($list->get("id")); ?>" />
    <fieldset>
        <legend><?php e(t('search', 'common')); ?></legend>
        
        <label for="optin"><?php e(t('Filter', 'common')); ?>: 
            <select name="optin" id="optin">
                <option value="1" <?php if($subscriber->getDBQuery()->getFilter('optin') == 1) echo 'selected="selected"'; ?> ><?php e(t('Opted in')); ?></option>
                <option value="0" <?php if($subscriber->getDBQuery()->getFilter('optin') == 0) echo 'selected="selected"'; ?> ><?php e(t('Not opted in')); ?></option>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('go', 'common')); ?>" /> 
        </span>
    </fieldset>
</form>

<?php if (count($subscribers) == 0): ?>
    <p>Der er ikke tilf�jet nogen modtager endnu.</p>
<?php else: ?>



    <?php echo $subscriber->getDBQuery()->display('character'); ?>
<table class="stripe">
    <caption>Breve</caption>
    <thead>
    <tr>
        <th>Navn</th>
        <th>E-mail</th>
        <th>Tilmeldt</th>
        <th>Optin</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($subscribers AS $s): ?>
    <tr>
        <td><?php e($s['contact_name']); ?></td>
        <td><?php e($s['contact_email']); ?></td>
        <td><?php e($s['dk_date_submitted']); ?></td>
        <td>
            <?php if ($s['optin'] == 0 and $s['date_optin_email_sent'] < date('Y-m-d', time() - 60 * 60 * 24 * 3)): ?>
                <a href="<?php e($_SERVER['PHP_SELF'] . '?list_id='.$list->get('id')); ?>&amp;id=<?php e($s['id']); ?>&amp;remind=true&amp;use_stored=true"><?php e(t('Remind')); ?></a>
            <?php elseif ($s['optin'] == 0): ?>
                <?php e(t('Not opted in')); ?>
            <?php elseif ($s['optin'] == 1): ?>
                <?php e(t('Opted in')); ?>
            <?php endif; ?>
        </td>
        <td>
            <a class="delete" href="subscribers.php?delete=<?php e($s['id']); ?>&amp;list_id=<?php e($list->get('id')); ?>" title="Dette sletter modtageren">Slet</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <?php echo $subscriber->getDBQuery()->display('paging'); ?></td>
<?php endif; ?>

<?php
$page->end();
?>