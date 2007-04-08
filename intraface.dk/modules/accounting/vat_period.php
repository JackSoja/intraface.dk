<?php
/**
 * Momsafregning
 *
 * Denne side skal v�re en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anf�rt p� momskonti.
 *
 * N�r man klikker p� angiv moms skal tallene gemmes i en database.
 *
 * Hvis man vil redigere tallene, klikker man sig hen til vat_edit.php
 *
 * Siden skal regne ud, om der er forskel p� de tal, der er blevet
 * opgivet og det der rent faktisk skulle v�re opgivet, s� man kan fange
 * evt. fejl n�ste gang man skal opgive moms.
 *
 * Primosaldoer skal naturligvis fremg� af momsopg�relsen.
 *
 * Der skal v�re en liste med momsangivelsesperioder for �ret,
 * og s� skal der ud for hver momssopgivelse v�re et link enten til
 * den tidligere opgivne moms eller til at oprette en momsangivelse.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$allowed_periods = $module->getSetting('vat_periods');

$year = new Year($kernel);
$year->checkYear();

if (!empty($_POST['create_periods'])) {
	if (isset($_POST['vat_period_key'])) {
		$year->setSetting('vat_period', $_POST['vat_period_key']);
	}
	$vat_period = new VatPeriod($year);
	$vat_period->createPeriods();
	header('Location: vat_period.php');
	exit;
}
elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$vat_period = new VatPeriod($year, $_GET['delete']);
	$vat_period->delete();
}
else {
	$vat_period = new VatPeriod($year);
}

$periods = $vat_period->getList();
$post = new Post(new Voucher($year));

$page = new Page($kernel);
$page->start('Momsoversigt');
?>

<h1>Moms <?php echo $year->get('label'); ?></h1>

<?php echo $vat_period->error->view(); ?>

<?php if ($year->get('vat') == 0): ?>
	<p class="message">Dit regnskab bruger ikke moms, s� du kan ikke se momsangivelserne.</p>
<?php elseif (count($post->getList('draft')) > 0): ?>
	<p class="warning">Der er stadig poster i kassekladden. De b�r bogf�res, inden du opg�r momsen. <a href="daybook.php">G� til kassekladden</a>.</p>
<?php elseif (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php elseif (!$vat_period->periodsCreated()): ?>
	<div class="message">
		<p><strong>Moms</strong>. P� denne side kan du f� hj�lp til at afregne moms. Inden du g�r noget, skal du s�rge for at alle bel�bene for den p�g�ldende periode, er tastet ind i systemet.</p>
	</div>

	<p class="message-dependent">Der er ikke oprettet nogen momsperioder for dette �r.</p>
	<form action="<?php basename($_SERVER['PHP_SELF']); ?>" method="post">
		<fieldset>
			<label for="vat_period_key">Hvor ofte skal du afregne moms</label>
			<select name="vat_period_key" id="vat_period_key">
			<option value="">V�lg</option>
			<?php foreach ($allowed_periods AS $key=>$value): ?>
				<option value="<?php echo $key; ?>"<?php if ($key == $year->getSetting('vat_period')) echo ' selected="selected"'; ?>><?php echo safeToHtml($value['name']); ?></option>
			<?php endforeach; ?>
			</select>
			<input type="submit" value="Opret perioder" name="create_periods" />
		</fieldset>
	</form>
<?php else: ?>
	<table>
	<caption>Momsperioder i perioden <?php echo safeToHtml($year->get('from_date_dk')); ?> til <?php echo safeToHtml($year->get('to_date_dk')); ?></caption>
	<thead>
		<tr>
			<th>Periode</th>
			<th>F�rste dato</th>
			<th>Sidste dato</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($periods AS $period): ?>
		<tr>
			<td><a href="vat_view.php?id=<?php echo intval($period['id']); ?>"><?php echo safeToHtml($period['label']); ?></a></td>
			<td><?php echo safeToHtml($period['date_start_dk']); ?></td>
			<td><?php echo safeToHtml($period['date_end_dk']); ?></td>
			<td class="options"><a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $period['id']; ?>">Slet</a></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

<?php endif; ?>

<?php
$page->end();
?>