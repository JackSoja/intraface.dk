<?php
/**
 * Momsafregning
 *
 * Denne side skal v�re en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anf�rt p� momskonti.
 *
 * N�r man klikker p� angiv moms skal tallene gemmes i en database. *
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
 * @todo Der kunne skrives en advarsel, hvis man ikke har sat eu-konti mv.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$error = new Intraface_Error;

$year = new Year($kernel);
$year->checkYear();

$voucher = new Voucher($year);

#
# Gemme
#
/*
if (!empty($_POST['get_amounts']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
	$vat_period = new VatPeriod($year, $_POST['id']);
	$vat_period->loadAmounts();
	$account_vat_in = $vat_period->get('account_vat_in');
	$account_vat_out = $vat_period->get('account_vat_out');
	$account_vat_abroad = $vat_period->get('account_vat_abroad');
	//$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
	$saldo_total = $vat_period->get('saldo_total');

	$amount = array(
		'vat_out' => $account_vat_out->get('saldo'),
		'vat_abroad' => $account_vat_abroad->get('saldo'),
		'vat_in' => $account_vat_in->get('saldo')
	);

	//$vat_period->saveAmounts($amount);
	header('Location: vat_view.php?id='.$vat_period->get('id'));
	exit;
}
*/
if (!empty($_POST['state']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
	$vat_period = new VatPeriod($year, $_POST['id']);

	if (!$vat_period->state($_POST['date'], $_POST['voucher_number'])) {
		trigger_error('Kunne ikke bogf�re bel�bene', E_USER_ERROR);
	}

	header('Location: vat_view.php?id='.$vat_period->get('id'));
	exit;

}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$vat_period = new VatPeriod($year, $_GET['id']);
	$vat_period->loadAmounts();
	$account_vat_in = $vat_period->get('account_vat_in');
	$account_vat_out = $vat_period->get('account_vat_out');
	$account_vat_abroad = $vat_period->get('account_vat_abroad');
	$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
	$saldo_total = $vat_period->get('saldo_total');
}
else {
	trigger_error('vat_view.php kr�ver et periode-id', E_USER_ERROR);
}


$page = new Intraface_Page($kernel);
$page->start('Momsoversigt');
?>
<h1>Moms <?php echo $year->get('label'); ?></h1>

<ul class="options">
	<li><a href="vat_period.php">Luk</a></li>
</ul>

<?php if (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php else: ?>

	<?php echo $error->view(); ?>

	<?php if ($vat_period->get('status') == 'stated'): ?>
		<p class="message">Denne momsopgivelse er bogf�rt. <a href="<?php echo $module->getPath(); ?>voucher.php?id=<?php echo $vat_period->get('voucher_id'); ?>">Se bilag</a></p>
	<?php endif; ?>

	<?php if (!$vat_period->compareAmounts() AND $vat_period->get('status_key') > 0): // bel�b skal v�re gemt ?>
		<p class="warning">Det ser ud til, at du ikke har f�et bogf�rt alle momsbel�bene korrekt. Denne momsangivelse burde v�re 0, n�r den er bogf�rt.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('invoice', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle fakturaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=invoice&amp;status=-1&amp;not_stated=true&amp;from_date=<?php echo $vat_period->get('date_start_dk') ?>&amp;to_date=<?php echo $vat_period->get('date_end_dk') ?>">G� til fakturaer</a>.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('credit_note', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle kreditnotaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=credit_note&amp;status=-1&amp;not_stated=true&amp;from_date=<?php echo $vat_period->get('date_start_dk') ?>&amp;to_date=<?php echo $vat_period->get('date_end_dk') ?>">G� til kreditnotaer</a>.</p>
	<?php endif; ?>

	<table id="accounting-vat">
	<caption>Momsopg�relse for perioden <?php echo $vat_period->get('date_start_dk'); ?> til <?php echo $vat_period->get('date_end_dk'); ?></caption>
	<thead>
		<tr>
			<th>Kontonummer</th>
			<th>Kontobeskrivelse</th>
			<th colspan="2">Bel�b fra regnskabet</th>
		</tr>
	</thead>
	<tbody>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php echo $account_vat_out->get('id'); ?>"><?php echo $account_vat_out->get('number'); ?></a></td>
			<td><?php echo safeToHtml($account_vat_out->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php echo amountToOutput($account_vat_out->get('saldo') * -1); ?></td>
		</tr>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php echo $account_vat_abroad->get('id'); ?>"><?php echo $account_vat_abroad->get('number'); ?></a></td>
			<td><?php echo $account_vat_abroad->get('name'); ?></td>
			<td></td>
			<td class="amount debet"><?php echo amountToOutput($account_vat_abroad->get('saldo') * -1); ?></td>
		</tr>
		<tr class="headline">
			<td colspan="6"><h3>Fradrag</h3></td>
		</tr>
		<tr class="vat-buy">
			<td><a href="account.php?id=<?php echo $account_vat_in->get('id'); ?>"><?php echo $account_vat_in->get('number'); ?></a></td>
			<td><?php echo $account_vat_in->get('name'); ?></td>
			<td class="amount debet"><?php echo amountToOutput($account_vat_in->get('saldo')); ?></td>
			<td></td>
		</tr>
		<tr class="vat-amount">
			<th colspan="2">Afgiftsbel�b i alt</th>
			<td></td>
			<td class="amount debet"><?php echo amountToOutput($saldo_total, 0); ?></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik A. V�rdien uden moms af varek�b i andre <acronym title="Europ�iske Union">EU</acronym>-lande</td>
			<!--<td class="amount credit"><?php echo $saldo_rubrik_a; ?></td>-->
			<td class="amount debet"><?php echo $saldo_rubrik_a; ?></td>
			<td></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik B. V�rdien af varesalg uden moms til andre <acronym title="Europ�iske Union">EU</acronym>-lande (EU-leverancer). Udfyldes rubrik B, skal der indsendes en liste</td>
			<td class="amount debet">Ikke underst�ttet</td>
			<td></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik C. V�rdien af varer og ydelser, som s�lges momsfrit til udlandet efter lovens �14-21 og 34, bortset fra varesalg til andre EU-lande, jf. rubrik B.</td>
			<td class="amount debet">Ikke underst�ttet</td>
			<td></td>
		</tr>
	</tbody>
	</table>

	<?php if ($kernel->user->hasSubaccess('accounting', 'vat_report')): ?>
		<?php if ($vat_period->get('date_end') > date('Y-m-d')): ?>
			<p class="warning">Du er endnu ikke ude af perioden for momsafregningen, s� det er en god ide at vente med at bogf�re til du er sikker p� alle bel�bene.</p>
		<?php endif; ?>

		<?php if ($vat_period->get('status') != 'stated' OR !$vat_period->compareAmounts()): ?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

				<input type="hidden" name="id" value="<?php echo $vat_period->get('id'); ?>" />
			<fieldset>
				<legend>Bogf�r momsen</legend>
				<p>Du kan overf�re bel�bene til kassekladden ved at trykke p� knappen nedenunder. Du b�r f�rst trykke p� knappen, n�r du har opgivet bel�bene hos Skat.</p>
				<div class="formrow">
					<label for="date">Dato</label> <input type="text" name="date" id="date" value="<?php echo $vat_period->get('date_end_dk'); ?>" />
				</div>
				<?php if ($vat_period->get('status') == 'stated'): ?>
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php echo $vat_period->get('voucher_number'); ?>" /> Perioden er tidligere bogf�rt p� dette bilag
				</div>

				<?php else: ?>
				<div class="formrow">
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php echo $voucher->getMaxNumber() + 1; ?>" />
				</div>
				<?php endif; ?>
				<div style="clear:both;">
					<input type="submit" name="state" value="Bogf�r moms til momsafregning" />
				</div>
			</fieldset>
			</form>
		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>


<?php
$page->end();
?>