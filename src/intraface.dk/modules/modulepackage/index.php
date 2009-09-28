<?php
require '../../include_first.php';

$module = $kernel->module('modulepackage');
$module->includeFile('Manager.php');

// temp test
// require('Intraface/ModulePackage/AccessUpdate.php');
// $access_update = new Intraface_modules_modulepackage_AccessUpdate();
// $access_update->run($kernel->intranet->get('id'));

if (isset($_GET['unsubscribe_id']) && intval($_GET['unsubscribe_id']) != 0) {
    $modulepackagemanager = new Intraface_modules_modulepackage_Manager($kernel->intranet, (int)$_GET['unsubscribe_id']);
    if ($modulepackagemanager->get('id') != 0) {
        if ($modulepackagemanager->get('status') == 'created') {
            $modulepackagemanager->delete();
        } elseif ($modulepackagemanager->get('status') == 'active') {
            $modulepackagemanager->terminate();

            $module->includeFile('AccessUpdate.php');
            $access_update = new Intraface_modules_modulepackage_AccessUpdate();
            $access_update->run($kernel->intranet->get('id'));
            $kernel->user->clearCachedPermission();

        } else {
            $modulepackagemanager->error->set('it is not possible to unsubscribe module packages which is not either created or active');
        }
    }
}

$translation = $kernel->getTranslation('modulepackage');

$page = new Intraface_Page($kernel);
$page->start($translation->get('your account'));
?>
<h1><?php e($translation->get('your account')); ?></h1>

<?php if (isset($modulepackagemanager)) echo $modulepackagemanager->error->view(); ?>

<div class="message">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <?php
        // TODO: This is not really a good text
        ?>
        <h3><?php e($translation->get('success!')); ?></h3>
        <p><?php e($translation->get('if everything went as it should, you can see your packages below, and you should be able to use them now.')); ?></p>
    <?php else: ?>
        <p><?php e($translation->get('on this page you have an overview of your intraface account')); ?></p>
    <?php endif; ?>
</div>

<?php
$modulepackagemanager = new Intraface_modules_modulepackage_Manager($kernel->intranet);
$modulepackagemanager->getDBQuery($kernel)->setFilter('status', 'created_and_active');
$packages = $modulepackagemanager->getList();

if (count($packages) > 0) {
    ?>
    <h2><?php e($translation->get('your subscription')); ?></h2>
    <table class="stribe">
        <caption><?php e($translation->get('modulepackages')); ?></caption>
        <thead>
            <tr>
                <th><?php e($translation->get('modulepackage')); ?></th>
                <th><?php e($translation->get('start date')); ?></th>
                <th><?php e($translation->get('end date')); ?></th>
                <th><?php e($translation->get('status')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($packages AS $package): ?>
            <tr>
                <td><?php e($translation->get($package['plan']).' '.$translation->get($package['group'])); ?></td>
                <td><?php e($package['dk_start_date']); ?></td>
                <td><?php e($package['dk_end_date']); ?></td>
                <td><?php e($translation->get($package['status'])); ?></td>
                <td><a href="index.php?unsubscribe_id=<?php e($package['id']); ?>" class="delete"><?php e($translation->get('unsubscribe')); ?></a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}
?>

<h2><?php e($translation->get('subscribe to new package')); ?></h2>

<?php
$modulepackage = new Intraface_modules_modulepackage_ModulePackage;
$plans = $modulepackage->getPlans();
$groups = $modulepackage->getGroups();
$modulepackage->getDBQuery($kernel);
$packages = $modulepackage->getList('matrix');
?>

<table class="stribe">
    <thead>
        <tr>
            <th><?php e($translation->get('select your package')); ?></th>
            <?php foreach ($plans AS $plan): ?>
                <th style="width: <?php echo floor(100/(2 + count($plans))); ?>%;"><?php e($translation->get($plan['plan'])); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // we make sure it is arrays to avoid errors.
        settype($groups, 'array');
        settype($plans, 'array');

        foreach ($groups AS $group) { ?>


            <tr>
            <th style="vertical-align: top;">
            <strong><?php e($translation->get($group['group'])); ?></strong>
            <?php
            if (isset($plans[0]['id']) && isset($packages[$group['id']][$plans[0]['id']]) && is_array($packages[$group['id']][$plans[0]['id']])) {
                $modules = $packages[$group['id']][$plans[0]['id']]['modules'];
            } else {
                $modules = array();
            }
            $row_modules = array();
            if (is_array($modules) && count($modules) > 0) { ?>
                <div>
                <?php
                echo $translation->get('gives you access to: <br /> - ');
                for ($j = 0, $max = count($modules); $j < $max; $j++) {
                    if ($j != 0) {
                        echo ', ';
                    }
                    e($translation->get($modules[$j]['module']));
                    $row_modules[] = $modules[$j]['module'];
                } ?>
                </div>
                <?php
            }
            ?>
            </th>
            <?php
            foreach ($plans AS $plan) { ?>
                <td style="vertical-align: bottom;">
                <?php if (isset($packages[$group['id']][$plan['id']]) && is_array($packages[$group['id']][$plan['id']])) {

                    $modules = array();
                    $limiters = array();
                    if (isset($packages[$group['id']][$plan['id']]['modules']) && is_array($packages[$group['id']][$plan['id']]['modules'])) {
                        foreach ($packages[$group['id']][$plan['id']]['modules'] AS $module) {
                            $modules[] = $module['module'];
                            if (is_array($module['limiters']) && count($module['limiters']) > 0) {
                                $limiters = array_merge($limiters, $module['limiters']);
                            }
                        }
                    }

                    $display_modules = array_diff($modules, $row_modules);
                    if (is_array($display_modules) && count($display_modules) > 0) { ?>
                        <p><?php e($translation->get('plus the modules')); ?>: <br />
                        <?php echo implode(', ', $display_modules); ?>
                        </p>
                    <?php
                    }

                    if (is_array($limiters) && count($limiters) > 0) { ?>
                        <p><?php e($translation->get('gives you')); ?>:

                        <?php foreach ($limiters AS $limiter) { ?>
                            <br /><?php e($translation->get($limiter['description']).' ');
                            if (isset($limiter['limit_readable'])) {
                                e($limiter['limit_readable']);
                            } else {
                                e($limiter['limit']);
                            }
                        } ?>
                        </p>
                    <?php }

                    if (is_array($packages[$group['id']][$plan['id']]['product']) && count($packages[$group['id']][$plan['id']]['product']) > 0) { ?>
                        <p> DKK <?php e($packages[$group['id']][$plan['id']]['product']['price_incl_vat'].' '.$translation->get('per').' '.$translation->get($packages[$group['id']][$plan['id']]['product']['unit']['singular'])); ?></p>
                    <?php } ?>

                    <a href="add_package.php?id=<?php e($packages[$group['id']][$plan['id']]['id']); ?>"><?php e($translation->get('choose', 'common')); ?></a>

                <?php } ?>
                </td>
                <?php
            }
        }
        ?>
    </tbody>
</table>

<?php
$page->end();
?>