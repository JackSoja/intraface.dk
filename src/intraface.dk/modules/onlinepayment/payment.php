<?php
require('../../include_first.php');

$module = $kernel->module("onlinepayment");
$translation = $kernel->getTranslation('onlinepayment');

if (isset($_POST['submit'])) {

	// $onlinepayment = new OnlinePayment($kernel, $_POST['id']);
	// $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');
	// $implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]
	$onlinepayment = OnlinePayment::factory($kernel, 'id',  $_POST['id']);


	if ($onlinepayment->update($_POST)) {
		$onlinepayment->load();
		$value['dk_amount'] = $onlinepayment->get('dk_amount');
		//header("Location: index.php?from_id=".$onlinepayment->get("id"));
		//exit;
	}
	else {
		$value = $_POST;
	}
} elseif (!empty($_GET["id"])) {
	$onlinepayment = OnlinePayment::factory($kernel, 'id',  $_GET['id']);

	if ($onlinepayment->get('id') == 0) {
		trigger_error("Ugyldig onlinebetaling");
	}
	$value['dk_amount'] = $onlinepayment->get('dk_amount');

} else {
	trigger_error("Der er ikke angivet et betalingsid", ERROR);
}

$page = new Intraface_Page($kernel);
$page->start("Onlinebetaling");

?>

<div id="colOne">

<h1><?php e(t('Online payment')); ?></h1>

<ul class="options">
	<li><a href="index.php?from_id=<?php e($onlinepayment->get('id')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<?php echo $onlinepayment->error->view(); ?>

<table>
	<caption><?php e(t('Payment information')); ?></caption>
	<tbody>
		<tr>
			<th><?php e(t('Date', 'common')); ?></th>
			<td><?php e($onlinepayment->get("dk_date_created")); ?></td>
		</tr>
		<tr>
			<th><?php e(t('Related to')); ?></th>
			<td>
				<?php
				switch($onlinepayment->get('belong_to')) {
					case "invoice":
						if ($kernel->user->hasModuleAccess('invoice')) {
							$debtor_module = $kernel->useModule('debtor');
							print("<a href=\"".$debtor_module->getPath()."view.php?id=".$onlinepayment->get('belong_to_id')."\">Faktura</a>");
						} else {
							e("Faktura");
						}
					break;
					case "order":
						if ($kernel->user->hasModuleAccess('order')) {
							$debtor_module = $kernel->useModule('debtor');
							print("<a href=\"".$debtor_module->getPath()."view.php?id=".$onlinepayment->get('belong_to_id')."\">Ordre</a>");
						} else {
							e("Ordre");
						}
					break;
					default:
						e("Ingen");
				}
				?>
			</td>
		</tr>
		<tr>
			<th><?php e(t('Status', 'common')); ?></th>
			<td>
				<?php
				e(__($onlinepayment->get("status")));

				if ($onlinepayment->get('status') == 'authorized') {
					print(" (Ikke <acronym title=\"Betaling kan f�rst h�ves n�r faktura er sendt\">h�vet</acronym>)");
				}
				?>
			</td>
		</tr>
		<?php
		if ($onlinepayment->get('status') == 'captured') {
			?>
			<tr>
				<th><?php e(t('Date captured')); ?></th>
				<td><?php e($onlinepayment->get("dk_date_captured")); ?></td>
			</tr>
			<?php
		}
		?>
		<?php
		if ($onlinepayment->get('status') == 'reversed') {
			?>
			<tr>
				<th><?php e(t('Date reversed')); ?></th>
				<td><?php e($onlinepayment->get("dk_date_reversed")); ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<th><?php e(t('Transaction number')); ?></th>
			<td><?php e($onlinepayment->get("transaction_number")); ?></td>
		</tr>
		<tr>
			<th><?php e(t('Transaction status')); ?></th>
			<td><?php e($onlinepayment->get("transaction_status_translated")); ?></td>
		</tr>
        <tr>
            <th><?php e(t('PBS status')); ?></th>
            <td><?php e($onlinepayment->get("pbs_status")); ?></td>
        </tr>
		<tr>
			<th><?php e(t('Amount', 'common')); ?></th>
			<td>
                <?php
                if(false !== ($currency = $onlinepayment->getCurrency())) {
                    e($currency->getType()->getIsoCode().' ');
                } elseif($kernel->intranet->hasModuleAccess('currency')) {
                    e('DKK ');
                }
                e($onlinepayment->get("dk_amount"));
                ?>
            </td>
		</tr>
		<?php
		if ($onlinepayment->get('amount') != $onlinepayment->get('original_amount')) {
			?>
			<tr>
				<th><?php e(t('Original amount')); ?></th>
				<td>
                    <?php
                    if(false !== ($currency = $onlinepayment->getCurrency())) {
                        e($currency->getType()->getIsoCode().' ');
                    } elseif($kernel->intranet->hasModuleAccess('currency')) {
                        e('DKK ');
                    }
                    e($onlinepayment->get("dk_original_amount"));
                    ?>
                </td>
			</tr>
			<?php
		}
		?>

		<?php
		if ($onlinepayment->get('text') != "") {
			?>
			<tr>
				<th><?php e(t('Description')); ?></th>
				<td><?php autohtml($onlinepayment->get("text")); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

</div>

<div id="colTwo">

<?php
if ($onlinepayment->get('status') == "authorized") {
	?>
	<fieldset>
		<legend><?php e(t('Change amount')); ?></legend>

		<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

		<p<?php e(t('You can make the amount you withdraw smaller.')); ?>></p>

		<div class="formrow">
			<label for="dk_amount" class="tight"><?php e(t('Amount', 'common')); ?></label>
	    <input type="text" name="dk_amount" id="dk_amount" value="<?php e($value["dk_amount"]); ?>" />
		</div>

		<input type="submit" class="save" name="submit" value="<?php e(t('Save', 'common')); ?>" />
		<input type="hidden" name="id" value="<?php e($onlinepayment->get("id")); ?>" />
		</form>

	</fieldset>
	<?php
}
?>

<?php
$page->end();
?>