<?php
/**
 * @todo Funktionen kr�ver stadig noget arbejde. Fx skal der v�re et tjek p�,
 *       om primobalancen stemmer, inden man kan k�re primobalancen.
 */
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

if (!empty($_POST)) {
	$year = new Year($kernel, $_POST['year_id']);
	foreach ($_POST['id'] AS $key=>$values) {
		$account = new Account($year, $_POST['id'][$key]);
		$account->savePrimosaldo($_POST['debet'][$key], $_POST['credit'][$key]);
	}
	if (!$account->error->isError()) {
		header('Location: primosaldo.php?php='.$_POST['year_id']);
		exit;
	}
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$year = new Year($kernel, $_GET['id']);
}
else {
	$year = new Year($kernel);
}

$account = new Account($year);
$accounts = $account->getList('balance');


$total_debet = 0;
$total_credit = 0;

$page = new Page($kernel);

$page->start('Primosaldo');
?>

<h1>Rediger primosaldo <?php echo $year->get('label'); ?></h1>

<ul class="options">
	<li><a href="year_edit.php?id=<?php echo $year->get('id'); ?>">G� tilbage til regnskabs�ret</a></li>
	<li><a href="primosaldo.php?id=<?php echo $year->get('id'); ?>">Luk</a></li>
</ul>

<?php echo $account->error->view(); ?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="year_id" value="<?php echo $year->get('id'); ?>" />
	<fieldset>
		<legend>Oplysninger til primosaldo</legend>
		<table>
			<thead>
			<tr>
				<th>Kontonummer</th>
				<th>Kontonavn</th>
				<th>Debet</th>
				<th>Credit</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($accounts AS $account): ?>
				<tr>
					<td>
						<input type="hidden" name="id[]" id="id<?php echo $account['id']; ?>" value="<?php echo $account['id']; ?>" />
						<?php echo $account['number']; ?>
					</td>
					<td><?php echo $account['name']; ?></td>
					<td>
						<input type="text" name="debet[]" id="debet<?php echo $account['id']; ?>" value="<?php echo amountToForm($account['primosaldo_debet']); ?>" />
					</td>
					<td>
						<input type="text" name="credit[]" id="credit<?php echo $account['id']; ?>" value="<?php echo amountToForm($account['primosaldo_credit']); ?>" />
					</td>
				</tr>
				<?php
					$total_debet += $account['primosaldo_debet'];
					$total_credit += $account['primosaldo_credit'];
				?>

			<?php endforeach; ?>
				<tr>
					<td></td>
					<td>
						<strong>Balance</strong>
						<?php
							if ($total_debet != $total_credit) {
								echo '<strong style="color: red;">Balancen stemmer ikke</strong>';
							}
						?>
					</td>
					<td><strong><?php echo amountToOutput($total_debet); ?></strong></td>
					<td><strong><?php echo amountToOutput($total_credit); ?></strong></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<div>
		<input type="submit" name="submit" value="Opdater primosaldo" class="confirm" />
		eller
		<a href="primosaldo.php?id=<?php echo $year->get('id'); ?>">Fortryd</a>
	</div>
</form>

<?php
$page->end();
?>