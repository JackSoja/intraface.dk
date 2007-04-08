<?php
require('../../include_first.php');

$debtor_module = $kernel->module('debtor');
$accounting_module = $kernel->useModule('accounting');
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation();


/*
Der burde v�re en redirect hvis man skal redigere �ret


Hvad g�r vi med rabat til kunder? Den skal jo bogf�res bagvendt som en udgift.

Det er ogs� us�dvanligt vigtigt at rabatten ikke l�ngere skal v�re et produkt,
men konverteres til en samlet rabat p� fakturaen, som er selvst�ndigt punkt.

Filen b�r tage h�jde for betalingsm�den. Hvis det er kontant, skal den naturligvis
smide pengene p� kontant-kontoen.

Hvis der er betalt med visa/paypal, skal pengene smides direkte p� bankkontoen.
*/

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {
	#
	# Vi skal have lavet noget bogf�ring af payment
	# og credit_notes
	# og reminders
	#

	$debtor = Debtor::factory($kernel, intval($_POST["id"]));
	if (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'])) {
		$debtor->error->set('Kunne ikke bogf�re posten');
		$debtor->loadItem();
	}
	else {
		header('Location: state.php?id='.$debtor->get('id'));
		exit;
	}
}
else {
	$debtor = Debtor::factory($kernel, intval($_GET["id"]));
	$debtor->loadItem();
}

$items = $debtor->item->getList();
$value = $debtor->get();

$page = new Page($kernel);
$page->start($translation->get('State invoice'));

?>
<h1>Bogf�r faktura #<?php echo safeToHtml($debtor->get('number')); ?></h1>

<ul class="options">
	<li><a href="view.php?id=<?php print(intval($debtor->get("id"))); ?>">Luk</a></li>
	<li><a href="list.php?type=invoice&amp;id=<?php print(intval($debtor->get("id"))); ?>&amp;use_stored=true">Tilbage til fakturaoversigten</a></li>
</ul>


<?php if (!$debtor->readyForState()): ?>

<?php echo $debtor->error->view(); ?>


<?php else: ?>

	<?php $debtor->invoiceReadyForState(); ?>

	<?php echo $debtor->error->view(); ?>

<p class="warning">
	Du skal v�re opm�rksom p� at denne funktion altid s�tter fakturaerne p� debitorkontoen. Hvis de er betalt kontant, skal du alts� huske at bogf�re den kontante betaling.
</p>



<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<input type="hidden" value="<?php echo intval($value['id']); ?>" name="id" />
<fieldset>
	<legend>Oplysninger der bogf�res</legend>

		<table>
					<tr>
						<th>Bilagsnummer</th>
						<td>
							<?php if (!$debtor->isStated()): ?>
							<input type="text" name="voucher_number" value="<?php echo safeToHtml($voucher->getMaxNumber() + 1); ?>" />
							<?php else: ?>
							<a href="<?php echo $accounting_module->getPath(); ?>voucher.php?id=<?php echo intval($debtor->get("voucher_id")); ?>">Se bilag</a>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php print(safeToHtml($translation->get("invoice number"))); ?></th>
						<td><?php print(safeToHtml($debtor->get("number"))); ?></td>
					</tr>
					<tr>
						<th>Dato</th>
						<td><?php print(safeToHtml($debtor->get("dk_this_date"))); ?></td>
					</tr>
					<?php if ($debtor->isStated()): ?>
					<tr>
						<th>Bogf�rt</th>
						<td>
								<?php echo safeToHtml($debtor->get("dk_date_stated")); ?>
						</td>
					</tr>
					<?php else: ?>
					<tr>
						<th>Bogf�r p� dato</th>
						<td>
							<input type="text" name="date_state" value="<?php echo safeToHtml($debtor->get("dk_this_date")); ?>" />
						</td>
					</tr>
					<?php endif; ?>
				</table>

</fieldset>



<table class="stripe">
	<thead>
		<tr>
			<th>Varenr.</th>
			<th>Beskrivelse</th>
			<th>Bel�b</th>
			<th>Bogf�res p�</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$total = 0;
		if(isset($items[0]["vat"])) {
			$vat = $items[0]["vat"]; // Er der moms p� det f�rste produkt
		}
		else {
			$vat = 0;
		}

		for($i = 0, $max = count($items); $i<$max; $i++) {
			$product = new Product($kernel, $items[$i]['product_id']);
			$account = Account::factory($year, $product->get('state_account_id'));

			$total += $items[$i]["quantity"] * $items[$i]["price"];
			$vat = $items[$i]["vat"];
			?>
			<tr>
				<td><?php print(safeToHtml($items[$i]["number"])); ?></td>
				<td><?php print(safeToHtml($items[$i]["name"])); ?></td>
				<td><?php print(amountToOutput($items[$i]["quantity"]*$items[$i]["price"])); ?></td>
				<td>
					<?php if ($account->get('id')): ?>
						<?php echo safeToHtml($account->get('number') . ' ' . $account->get('name')); ?>
					<?php else: ?>
					<?php
						$redirect = new Redirect($kernel);
						$url = $redirect->setDestination($product_module->getPath() . 'product_edit.php?id=' .$items[$i]['product_id'], $debtor_module->getPath() . 'state.php?id='.$debtor->get('id'));
					?>
						<a href="<?php echo safeToHtml($url); ?>">Rediger produktet</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php

			if($vat == 1 && (!isset($items[$i+1]["vat"]) || $items[$i+1]["vat"] == 0)) {
				?>
				<tr>
					<td>&nbsp;</td>
					<td><b>25% moms af <?php print(amountToOutput($total)); ?></b></td>
					<td><b><?php print(amountToOutput($total * 0.25, 2)); ?></b></td>
					<td>
						<?php
							$account = new Account($year, $year->getSetting('vat_out_account_id'));
							echo safeToHtml($account->get('number') . ' ' . $account->get('name'));
						?>
					</td>
				</tr>
				<?php
				$total = $total * 1.25;
			}
		}

		?>
		</tbody>
   </table>

	 <?php if ($debtor->invoiceReadyForState() AND !$debtor->isStated()): ?>
	 <div>
	 	<input type="submit" value="Bogf�r" /> eller
		<a href="view.php?id=<?php echo intval($value['id']); ?>">fortryd</a>
	</div>
	<?php else: ?>
	<p><a href="<?php echo $accounting_module->getPath(); ?>daybook.php">G� til kassekladden</a></p>
	<?php endif; ?>
</form>
<?php endif; ?>
<?php
$page->end();
?>