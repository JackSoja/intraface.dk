<?php
require('../../include_first.php');

$debtor_module = $kernel->module('debtor');
$accounting_module = $kernel->useModule('accounting');
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('debtor');

/*
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
	if (!$debtor->state($year, $_POST['voucher_number'])) {
		$debtor->error->set('Kunne ikke bogf�re posten');
	}
	$debtor->loadItem();
}
else {
	$debtor = Debtor::factory($kernel, intval($_GET["id"]));
	$debtor->loadItem();

}

$items = $debtor->item->getList();
$value = $debtor->get();

$page = new Page($kernel);
$page->start(ucfirst($translation->get("title")));

?>
<h1>Bogf�r Kreditnota #<?php echo safeToHtml($debtor->get('number')); ?></h1>

<ul class="options">
	<li><a href="view.php?id=<?php print(intval($debtor->get("id"))); ?>">Luk</a></li>
	<li><a href="list.php?type=credit_note&amp;id=<?php print(intval($debtor->get("id"))); ?>&amp;use_stored=true">Tilbage til Kreditnotaerne</a></li>
</ul>



<?php if (!$debtor->readyForState()): ?>

<?php echo $debtor->error->view(); ?>

<?php else: ?>

<?php 	$debtor->creditnoteReadyForState(); ?>
<?php echo $debtor->error->view(); ?>
<p class="warning">
	Du skal v�re opm�rksom p� at denne funktion altid s�tter tilbagef�rer kreditnotaerne til debitorkontoen. Hvis der er tilbagebetalt penge, skal du alts� huske at bogf�re det ogs�.
</p>



<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" value="<?php echo intval($value['id']); ?>" name="id" />
<fieldset>
	<legend>Oplysninger der bogf�res</legend>

		<table>
					<tr>
						<th>Bilagsnummer</th>
						<td>
							<?php if (!$debtor->isStated()): ?>
							<input type="text" name="voucher_number" value="<?php echo safeToForm($voucher->getMaxNumber() + 1); ?>" />
							<?php else: ?>
							<a href="/modules/accounting/voucher.php?id=<?php echo intval($debtor->get("voucher_id")); ?>">Se bilag</a>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php print(safeToHtml($translation->get($debtor->get('type').' number'))); ?>:</th>
						<td><?php print(safeToHtml($debtor->get("number"))); ?></td>
					</tr>
					<tr>
						<th>Dato:</th>
						<td><?php print(safeToHtml($debtor->get("dk_this_date"))); ?></td>
					</tr>
					<?php if ($debtor->isStated()): ?>
					<tr>
						<th>Bogf�rt:</th>
						<td><?php echo safeToHtml($debtor->get("dk_date_stated")); ?></td>
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
		$vat = $items[0]["vat"]; // Er der moms p� det f�rste produkt

		for($i = 0, $max = count($items); $i<$max; $i++) {
			$product = new Product($kernel, $items[$i]['product_id']);
			$account = Account::factory($year, $product->get('state_account_id'));

			$total += $items[$i]["quantity"] * $items[$i]["price"];
			$vat = $items[$i]["vat"];
			?>
			<tr id="i<?php echo intval($items[$i]["id"]); ?>" <?php if(isset($_GET['item_id']) && $_GET['item_id'] == $items[$i]['id']) print(' class="fade"'); ?>>
				<td><?php print(safeToHtml($items[$i]["number"])); ?></td>
				<td><?php print(safeToHtml($items[$i]["name"])); ?></td>
				<td><?php print(number_format($items[$i]["quantity"]*$items[$i]["price"], 2, ",", ".")); ?></td>
				<td>
					<?php
						if ($account->get('id')) {
							echo safeToHtml($account->get('number') . ' ' . $account->get('name'));
						}
						else {
						$redirect = new Redirect($kernel);
						$url = $redirect->setDestination($product_module->getPath() . 'product_edit.php?id=' .$items[$i]['product_id'], $debtor_module->getPath() . 'state_creditnote.php?id='.$debtor->get('id'));

							echo '<a href="'.safeToHtml($url).'">Rediger produktet</a>';
					 	}
					?>
				</td>
			</tr>
			<?php

			if($vat == 1 && $items[$i+1]["vat"] == 0) {
				?>
				<tr>
					<td>&nbsp;</td>
					<td><b>25% moms af <?php print(number_format($total, 2, ",", ".")); ?></b></td>
					<td><b><?php print(number_format($total * 0.25, 2, ",", ".")); ?></b></td>
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

	 <?php if ($debtor->creditnoteReadyForState() AND !$debtor->isStated()): ?>
	 <div>
	 	<input type="submit" value="Bogf�r" /> eller
		<a href="view.php?id=<?php echo intval($value['id']); ?>">fortryd</a>
	</div>
	<?php else: ?>
	<p><a href="/modules/accounting/daybook.php">G� til kassekladden</a></p>
	<?php endif; ?>
</form>
<?php endif; ?>
<?php
$page->end();
?>
