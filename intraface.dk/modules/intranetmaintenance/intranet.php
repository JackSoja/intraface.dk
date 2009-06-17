<?php
require('../../include_first.php');

// This is not a really good way to do it...
require('Intraface/modules/modulepackage/ModulePackage.php');
require('Intraface/modules/modulepackage/Manager.php');

$modul = $kernel->module("intranetmaintenance");
if ($kernel->user->hasModuleAccess('contact')) {
	$contact_module = $kernel->useModule('contact');
}
$translation = $kernel->getTranslation('intranetmaintenance');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $intranet = new IntranetMaintenance(intval($_POST["id"]));

    if (isset($_POST['add_module_package']) && $_POST['add_module_package'] != '') {

        $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
        $modulepackagemanager->save($_POST['module_package_id'], $_POST['start_date'], $_POST['duration_month'].' month');

    }

    # Update permission
    if (isset($_POST["change_permission"])) {

        $modules = array();
        $modules = $_POST["module"];

        $intranet->flushAccess();

        // Hvis man er i det samme intranet som man redigere
        if ($kernel->intranet->get("id") == $intranet->get("id")) {
            // Finder det aktive modul
            $active_module = $kernel->getPrimaryModule();
            // Giver adgang til det
            $intranet->setModuleAccess($active_module->getId());
        }

        for ($i = 0, $max = count($modules); $i < $max; $i++) {
            $intranet->setModuleAccess($modules[$i]);
        }

        header('Location: intranet.php?id='.$intranet->get('id'));
        exit;
    }

}
else {

    $intranet = new IntranetMaintenance($_GET['id']);

    # add contact
    if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
        if ($kernel->user->hasModuleAccess('contact')) {
            $contact_module = $kernel->useModule('contact');

            $redirect = Intraface_Redirect::factory($kernel, 'go');
            $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $modul->getPath()."intranet.php?id=".$intranet->get('id'));
            $redirect->askParameter('contact_id');
            $redirect->setIdentifier('contact');

            header("location: ".$url);
            exit;
        }
        else {
            trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
        }
    }

    # add existing user
    if (isset($_GET['add_user']) && $_GET['add_user'] == 1) {
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($modul->getPath()."users.php", $modul->getPath()."user.php?intranet_id=".$intranet->get('id'));
        $redirect->askParameter('user_id');
        $redirect->setIdentifier('add_user');
        header("location: ".$url);
        exit;
    }

    #return
    if (isset($_GET['return_redirect_id'])) {
        $redirect = Intraface_Redirect::factory($kernel, 'return');
        if ($redirect->get('identifier') == 'contact') {
            $intranet->setContact($redirect->getParameter('contact_id'));
        }
    }

    if (isset($_GET['delete_intranet_module_package_id']) && (int)$_GET['delete_intranet_module_package_id'] != 0) {

        $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
        $modulepackagemanager->delete((int)$_GET['delete_intranet_module_package_id']);
    }
}

$value = $intranet->get();
if (isset($intranet->address)) {
	$address_value = $intranet->address->get();
}
else {
	$address_value = array();
}

$user = new UserMaintenance();
$user->setIntranetId($intranet->get('id'));

$page = new Intraface_Page($kernel);
$page->start($translation->get('Intranet'));
?>

<div id="colOne">

<h1><?php e($translation->get('Intranet')); ?>: <?php e($intranet->get('name')); ?></h1>

<ul class="options">
	<li><a href="intranet_edit.php?id=<?php e($intranet->get('id')); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>
	<li><a href="index.php?use_stored=true"><?php e($translation->get('close', 'common')); ?></a></li>
</ul>

<?php echo $intranet->error->view(); ?>
<?php if (isset($modulepackagemanager)) echo $modulepackagemanager->error->view(); ?>

<table>
	<tr>
		<th><?php e($translation->get('name', 'address')); ?></th>
		<td>
			<?php if (isset($value['name'])) e($value["name"]); ?>
			<?php if (!empty($value['contact_id']) AND $intranet->get('id') > 0 && isset($contact_module)): ?>
				<?php
					$contact = new Contact($kernel, $value['contact_id']);
					echo '<a href="'.$contact_module->getPath() .'contact.php?id='.$contact->get('id').'">'.$contact->get('name').'</a>';
					echo ' <a href="'.basename($_SERVER['PHP_SELF']).'?id='.$intranet->get('id').'&amp;add_contact=1">'.$translation->get('change contact').'</a>';
				?>
			<?php elseif (isset($contact_module)): ?>
				<a href="<?php e($_SERVER['PHP_SELF']); ?>?id=<?php e($intranet->get('id')); ?>&amp;add_contact=1"><?php e($translation->get('add contact')); ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<!--
	<tr>
		<th><?php e($translation->get('maintained by')); ?></th>
		<td></td>
	</tr>
	-->


	<tr>
		<th><?php e($translation->get('name', 'address')); ?></th>
		<td><?php if (isset($address_value["name"])) e($address_value["name"]); ?></td>
	</tr>

	<tr>
		<th><?php e($translation->get('address', 'address')); ?></th>
		<td><?php if (isset($address_value["address"])) e($address_value["address"]); ?></td>
	</tr>

	<tr>
		<th><?php e($translation->get('postal code and city', 'address')); ?></th>
		<td><?php if (isset($address_value["postcode"])) e($address_value["postcode"]); ?> <?php if (isset($address_value["city"])) e($address_value["city"]); ?></td>
	</tr>
	<tr>
		<th><?php e($translation->get('country', 'address')); ?></th>
		<td><?php if (isset($address_value["country"])) e($address_value["country"]); ?></td>
	</tr>
	<tr>
		<th><?php e($translation->get('cvr number', 'address')); ?></th>
		<td><?php if (isset($address_value["cvr"])) e($address_value["cvr"]); ?></td>
	</tr>
	<tr>
		<th><?php e($translation->get('e-mail', 'address')); ?></th>
		<td><?php if (isset($address_value["email"])) e($address_value["email"]); ?></td>
	</tr>

	<tr>
		<th><?php e($translation->get('website', 'address')); ?></th>
		<td><?php if (isset($address_value["website"])) e($address_value["website"]); ?></td>
	</tr>

	<tr>
		<th><?php e($translation->get('phone', 'address')); ?></th>
		<td><?php if (isset($address_value["phone"])) e($address_value["phone"]); ?></td>
	</tr>

		<tr>
		<th><?php e($translation->get('private key')); ?></th>
		<td><?php e($intranet->get("private_key")); ?></td>
	</tr>

	<tr>
		<th><?php e($translation->get('public key')); ?></th>
		<td><?php e($intranet->get("public_key")); ?></td>
	</tr>

</table>

<form action="intranet.php" method="post">

<input type="hidden" name="id" value="<?php e($intranet->get("id")); ?>" />


    <?php
    $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
    $modulepackagemanager->getDBQuery($kernel);
    $packages = $modulepackagemanager->getList();

    if (count($packages) > 0) {
        ?>
        <table class="stribe">
            <caption>Modulpakker</caption>
            <thead>
                <tr>
                    <th>Modulpakke</th>
                    <th>Startdato</th>
                    <th>Slutdato</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php e($package['plan'].' '.$package['group']); ?></td>
                    <td><?php e($package['start_date']); ?></td>
                    <td><?php e($package['end_date']); ?></td>
                    <td><?php e($translation->get($package['status'])); ?></td>
                    <td><a href="edit_module_package.php?id=<?php e($package['id']); ?>" class="edit">Ret</a> <a href="intranet.php?id=<?php e($intranet->get('id')); ?>&amp;delete_intranet_module_package_id=<?php e($package['id']); ?>" class="delete">Slet</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    ?>



<fieldset>
    <legend>Tilf�j modulpakke</legend>
    <?php if (!$kernel->intranet->hasModuleAccess('shop')): ?>
        This intranet needs to have access to shop for modulepackage to work!
    <?php else: ?>

        <?php
        $modulepackage = new Intraface_modules_modulepackage_ModulePackage;
        $modulepackage->getDBQuery($kernel);
        $packages = $modulepackage->getList();
        ?>
        <div class="formrow">
            <label for="module_package_id">V�lg pakke</label>
            <select name="module_package_id" id="module_package_id">
                <?php

                foreach ($packages AS $package) { ?>
                    <option value="<?php e($package['id']); ?>"><?php e($package['plan'].' '.$package['group']); ?></option>
                <?php }
                ?>
            </select>
        </div>


        <div class="formrow">
            <label for="start_date">Start dato</label>
            <input type="text" name="start_date" id="start_date" value="<?php e(date('d-m-Y')); ?>" />
        </div>

        <div class="formrow">
            <label for="duration_month">Varighed i m�neder</label>
            <select name="duration_month" id="duration_month">
                <?php
                for ($i = 1; $i < 25; $i++) {
                    echo '<option value="'.intval($i).'">'.intval($i).'</option>';
                }
                ?>
            </select>
        </div>
        <input type="submit" name="add_module_package" value="Tilf�j" class="save" />
    <?php endif; ?>

</fieldset>


<fieldset>
	<legend>Adgang til moduler</legend>
	<div>
    <?php

	$module = new ModuleMaintenance;
	$modules = $module->getList();

	for ($i = 0; $i < count($modules); $i++) {
		?>
		<div style="float: left; width: 210px; ">
			<input type="checkbox" name="module[]" id="module_<?php e($modules[$i]["name"]); ?>" value="<?php e($modules[$i]["name"]); ?>"<?php if ($intranet->hasModuleAccess(intval($modules[$i]["id"]))) print("checked=\"checked\""); ?> />
			<label for="module_<?php e($modules[$i]["name"]); ?>"><?php e($modules[$i]["menu_label"]); ?></label>
		</div>
		<?php
	}
	?>
    </div>
    <div style="clear:both;">
        <input type="submit" name="change_permission" value="Gem" />
    </div>
</fieldset>

</form>


</div>

<div id="colTwo">

<table class="stribe">
	<caption>Users</caption>
	<thead>
	<tr>
		<th>Navn</th>
		<th>E-mail</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$users = $user->getList($kernel);

	foreach ($users AS $user_list) {
		?>
		<tr>
			<?php
			if ($user_list['name'] == '') $user_list['name'] = '[not filled in]';
			?>
			<td><a href="user.php?id=<?php e($user_list['id']); ?>&amp;intranet_id=<?php e($intranet->get('id')); ?>"><?php e($user_list['name']); ?></a></td>
			<td><?php e($user_list['email']); ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<p><a href="user_edit.php?intranet_id=<?php e($intranet->get('id')); ?>">Create new user</a></p>

<p><a href="intranet.php?id=<?php e($intranet->get('id')); ?>&amp;add_user=1">Add existing user</a></p>



</div>

<?php
$page->end();
?>
