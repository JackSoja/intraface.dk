<?php
require('../../include_first.php');

$accounting_module = $kernel->module('accounting');
$accounting_module->includeFile('YearEnd.php');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

// disse g�r det muligt let at skifte mellem trinene
if (!empty($_POST['previous'])) {
	$year_end = new YearEnd($year);
	$year_end->setStep($_POST['step'] - 2);

}
elseif (!empty($_POST['next'])) {
	$year_end = new YearEnd($year);
	$year_end->setStep($_POST['step']);
}

// her reagerer vi p� de forskellige trin
if (!empty($_POST['step_save_result'])) {
	$year_end = new YearEnd($year);

	if (!$year_end->saveStatement('operating')) {
		trigger_error('Kunne ikke gemme resultatopg�relsen', E_USER_ERROR);
	}
	// her skal den s� gemme resultatopg�relsen.

	$year_end->setStep($_POST['step']);
}

elseif (!empty($_POST['step_save_balance'])) {
	$year_end = new YearEnd($year);

	if (!$year_end->saveStatement('balance')) {
		trigger_error('Kunne ikke gemme balancen', E_USER_ERROR);
	}

	$year_end->setStep($_POST['step']);
}
elseif (!empty($_POST['step_transfer_result'])) {
	$year_end = new YearEnd($year);

	if (!$year_end->resetYearResult()) {
		trigger_error('Kunne ikke nulstille �rets resultat', E_USER_ERROR);
	}

	$year_end->setStep($_POST['step']);
}
elseif (!empty($_POST['step_reverse_result_account_reset'])) {
	$year_end = new YearEnd($year);
	if (!$year_end->resetYearResult('reverse')) {
		$year_end->error->view();
		trigger_error('Kunne ikke tilbagef�re �rets resultat �rets resultat', E_USER_ERROR);
	}
	$year_end->setStep($_POST['step'] - 1);
}


// step 1
elseif (!empty($_POST['step_things_stated'])) {
	$year_end = new YearEnd($year);
	$year_end->setStep($_POST['step']);
}

// overf�rsel af �rsopg�relsen
elseif (!empty($_POST['step_result'])) {
	$year_end = new YearEnd($year);
	$account = new Account($year);
	$year->setSetting('result_account_id', $_POST['result_account_id']);

	if ($year_end->resetOperatingAccounts()) {
		$year_end->setStep($_POST['step']);
	}

	else {
		$year_end->error->view();
	}


}
elseif (!empty($_POST['step_lock_year'])) {
	if (!empty($_POST['lock']) AND $_POST['lock'] == '1') {
		$year->lock();
	}
	$year_end = new YearEnd($year);
	$year_end->setStep($_POST['step']);
}
elseif (!empty($_POST['step_reverse_result_reset'])) {
	$year_end = new YearEnd($year);
	$year_end->resetOperatingAccounts('reverse');
	$year_end->setStep($_POST['step'] - 1);
}

$account = new Account($year);
$year_end = new YearEnd($year);
$post = new Post(new Voucher($year));
$vat_period = new VatPeriod($year);

$page = new Page($kernel);
$page->start('�rsafslutning');
?>

<h1>�rsafslutning</h1>

<?php /*if (!$year->get('locked') == 1): ?>
	<p class="warning">�ret er lukket for bogf�ring. Du kan l�se det op under <a href="year_edit.php<?php echo $year->get('id'); ?>">�ret</a>.</p>
<?php */ if (!$year->isSettingsSet()): ?>
	<p class="error">Kontoplanen er ikke delt op i resultatopg�relse og balance, eller der er ikke valgt en kapitalkonto. <a href="setting.php">G� til indstillingerne</a>.</p>
<?php elseif (count($post->getList('draft')) > 0): ?>
	<p class="warning">Der er stadig poster i kassekladden. De skal bogf�res, f�r du kan afslutte �ret. <a href="daybook.php">G� til kassekladden</a>.</p>
<?php elseif($year->get('vat') == 1 AND count($vat_period->getList()) == 0): ?>
	<p class="warning">Du har ikke oprettet nogen momsperioder. <a href="vat_period.php">Opret perioder</a>.</p>
<?php elseif (!$year->isBalanced()): ?>
	<p class="error">Balancen for �ret er <?php echo amountToOutput($year->get('year_saldo')); ?>. I et dobbelt bogholderi skal saldoen altid v�re 0, for ellers er der ikke er bogf�rt lige meget p� debet og credit. Du kan f�rst lave �rsafslutning n�r regnskabet stemmer. <a href="daybook.php">G� til kassekladden</a>.</p>
<?php else: ?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="step" value="<?php echo $year_end->get('step') + 1; ?>" />

<?php
switch($year_end->get('step') + 1):

	case 1:
		?>

	<div class="message">
		<p><strong>�rsafslutning</strong>. Her kan du f�lge en guide til at afslutte dit �rsregnskab.</p>
	</div>
		<fieldset>
			<legend>Trin 1: Sikre sig at alle poster er bogf�rt</legend>
			<p>Det f�rste du skal g�re, er at kigge en ekstra gang p� alle dine bilag.</p>
			<ul>
				<li>Er alle bilag bogf�rt - fakturaer, indk�b, kreditnotaer og rykkere?</li>
				<li>Har du bogf�rt alle afskrivninger?</li>
				<li>Har du afstemt banken og kassen?</li>
			</ul>


		<?php if (!$year->isStated('invoice', $year->get('from_date'), $year->get('to_date'))): ?>
			<p class="warning">Alle fakturaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=invoice&amp;status=-1&amp;not_stated=true&amp;from_date=<?php echo $year->get('from_date_dk') ?>&amp;to_date=<?php echo $year->get('to_date_dk') ?>">G� til ikke bogf�rte fakturaer</a>.</p>
		<?php endif; ?>

		<?php if (!$year->isStated('credit_note', $year->get('from_date'), $year->get('to_date'))): ?>
			<p class="warning">Alle kreditnotaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=credit_note&amp;status=-1&amp;not_stated=true&amp;from_date=<?php echo $year->get('from_date_dk') ?>&amp;to_date=<?php echo $year->get('to_date_dk') ?>">G� til ikke bogf�rte kreditnotaer</a>.</p>
		<?php endif; ?>

		<?php
			$vat_period = new VatPeriod($year);
			$vat_periods = $vat_period->getList();

			foreach ($vat_periods AS $period) {
				$vat_period = new VatPeriod($year, $period['id']);
				if (!$vat_period->get('voucher_id')) {
					echo '<p class="warning">Momsperiode ' . $vat_period->get('label') . ' er ikke bogf�rt. <a href="vat_view.php?id='.$vat_period->get('id').'">G� til momsperioden</a>.</p>';
				}
				elseif (!$vat_period->compareAmounts()) {
					echo '<p class="warning">Momsperiode ' . $vat_period->get('label') . ' stemmer ikke. <a href="vat_view.php?id='.$vat_period->get('id').'">G� til momsperioden</a>.</p>';
				}
			}
		?>

			<input type="submit" value="N�ste" name="step_things_stated" />
		</fieldset>
		<?php
	break;
	case 2:
		// her skal resultatopg�relsen gemmes.
		?>
		<fieldset>
			<legend>Trin 2: Gem resultatopg�relsen</legend>
			<?php if (count($year_end->getStatedActions('operating_reset')) == 0): ?>
			<input type="submit" name="previous" value="Forrige" />
			<input type="submit" name="step_save_result" value="Gem resultatopg�relsen" class="confirm" />
			<?php else: ?>
				<p>Resultatopg�relsen er allerede gemt. Du kan f�re posterne tilbage, hvis du vil gemme igen.</p>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_reverse_result_reset" value="Tilbagef�r posterne" class="confirm" />
				<input type="submit" name="next" value="N�ste" class="confirm" />
			<?php endif; ?>
		</fieldset>


		<?php
	break;
	case 3:
		// her skal resultatopg�relsen overf�res til statuskonti - og �rets resultat
		?>

		<?php if (count($year_end->getStatement('operating')) == 0): ?>
			<fieldset>
			<legend>Trin 3: Poster overf�res til resultatopg�relseskontoen</legend>
			<p class="warning">Du er endnu ikke helt klar til dette trin, for resultatopg�relsen er ikke gemt.</p>
			<input type="submit" value="Forrige" name="previous" />
			</fieldset>
		<?php else: ?>
<?php
$status_accounts = $account->getList('status');
?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<fieldset>
			<legend>Trin 3: Poster overf�res til resultatopg�relseskontoen</legend>
			<p>Her kan du automatisk overf�re alle poster fra driftskonti til resultatopg�relsen. Derved nulstilles alle driftskonti. Lad v�re at trykke p� knappen, hvis du ikke er helt sikker p�, hvad du g�r.</p>
			<div class="formrow">
			<label for="result_account">Poster overf�res til</label>
			<select id="result_account" name="result_account_id">
				<option value="">V�lg</option>
				<?php foreach ($status_accounts AS $a) { ?>
					<option value="<?php echo $a['id']; ?>"<?php if($year->getSetting('result_account_id')==$a['id']) { echo ' selected="selected"'; } ?>><?php echo safeToHtml($a['number']); ?> <?php echo safeToHtml($a['name']); ?></option>
				<?php } ?>
			</select>
			</div>

			<div>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_result" value="Overf�r poster" class="confirm" />
			</div>
		</fieldset>
	</form>

	<table>
		<caption>Driftskonti</caption>
		<thead>
		<tr>
			<th>Nummer</th>
			<th>Navn</th>
			<th>Debet</th>
			<th>Kredit</th>
			<th>Saldo</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($account->getList('drift', true) AS $a): ?>
			<tr>
				<td><a href="account.php?id=<?php echo $a['id']; ?>"><?php echo safeToHtml($a['number']); ?></a></td>
				<td><?php echo safeToHtml($a['name']); ?></td>
				<td><?php echo amountToOutput($a['debet']); ?></td>
				<td><?php echo amountToOutput($a['credit']); ?></td>
				<td><?php echo amountToOutput($a['saldo']); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

		<?php
		endif;
	break;
	case 4:
		// her skal statusopg�relsen gemmes.
		?>
		<fieldset>
			<legend>Trin 4: Gem statusopg�relsen</legend>
			<input type="submit" name="previous" value="Forrige" />
			<input type="submit" name="step_save_balance" value="Gem balancen" class="confirm" />
		</fieldset>

		<?php
	break;

	case 5:
		// her skal man s� kunne afl�se resultatopg�relsen og balancen p� samme side
		$result_statements = $year_end->getStatement('operating');
		$balance_statements = $year_end->getStatement('balance');

		if (count($result_statements) == 0 OR count($balance_statements) == 0):
			?>
			<fieldset>
			<legend>Trin 5: �rsregnskabet</legend>
			<p class="warning">Du er ikke helt klar til dette trin endnu, for �rsregnskabet er endnu ikke gemt.</p>
			<input name="previous" type="submit" value="Forrige" />
			</fieldset>

		<?php
		else:
		?>
		<fieldset>
			<legend>Trin 5: �rsregnskabet</legend>
			<p>�rsregnskabet er f�rdig. Du kan se det nedenunder - og du kan skrive det ud som et excel-ark. God forn�jelse.</p>
			<input name="previous" type="submit" value="Forrige" />
			<input name="next" type="submit" value="N�ste" />
		</fieldset>

		<ul class="options">
			<li><a class="excel" href="end_excel.php">Excel</a></li>
		</ul>
		<table>
			<caption>Resultatopg�relse</caption>
			<thead>
				<tr>
					<th>Kontonummer</th>
					<th>Konto</th>
					<th>Bel�b</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($result_statements AS $statement): ?>
			<tr<?php if ($statement['type'] == 'headline') { echo ' class="headline"'; } elseif ($statement['type'] == 'sum') { echo ' class="sum"';} ?>>

				<td><?php echo $statement['number']; ?></td>
				<td><?php echo $statement['name']; ?></td>
				<td><?php if ($statement['type'] != 'headline') echo amountToOutput(abs($statement['saldo'])); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>


		<table>
			<caption>Status</caption>
			<thead>
				<tr>
					<th>Kontonummer</th>
					<th>Konto</th>
					<th>Bel�b</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($balance_statements AS $statement): ?>
			<tr<?php if ($statement['type'] == 'headline') { echo ' class="headline"'; } elseif ($statement['type'] == 'sum') { echo ' class="sum"';} ?>>

				<td><?php echo $statement['number']; ?></td>
				<td><?php echo $statement['name']; ?></td>
				<td><?php if ($statement['type'] != 'headline') echo amountToOutput(abs($statement['saldo'])); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		endif;

		/*
		?>

			<table>

				<caption>Resultatopg�relse</caption>
				<thead>
				<tr>
					<th>Tekst</th>
					<th>Saldo</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan="2"><strong>Indt�gter</strong></td>
				</tr>
			<?php

				$db = new DB_Sql;
				$db->query("SELECT * FROM accounting_year_end_action INNER JOIN accounting_account ON accounting_year_end_action.debet_account_id = accounting_account.id WHERE use_key = 2"); // indkomst
				while ($db->nextRecord()) {
					?>
						<tr>
							<td><?php echo $db->f('number') . ' ' . $db->f('name'); ?></td>
							<td><?php echo amountToOutput($db->f('amount')); ?></td>
						</tr>
					<?php
				}

				?>
				<tr>
					<td colspan="2"><strong>Udgifter</strong></td>
				</tr>

				<?php
				$db->query("SELECT * FROM accounting_year_end_action INNER JOIN accounting_account ON accounting_year_end_action.debet_account_id = accounting_account.id WHERE use_key = 3"); // udgifter
				while ($db->nextRecord()) {
					?>
						<tr>
							<td><?php echo $db->f('number') . ' ' . $db->f('name'); ?></td>
							<td><?php echo amountToOutput($db->f('amount')); ?></td>
						</tr>
					<?php
				}
				// lave en sammenregning
			?>
				</tbody>
			</table>
		<?php
		*/
	break;

	case 6:
		// hvad skal vi g�re med �rets resultat?
		?>

			<fieldset>
				<legend>Trin 6: �rets resultat</legend>
			<?php if (count($year_end->getStatedActions('result_account_reset')) == 0): ?>
				<p>�rets resultat skal overf�res til kapitalkontoen, s� dine konti er klar til n�ste �rs regnskab.</p>
				<input type="submit" value="Forrige" name="previous" />
				<input type="submit" value="Gem" name="step_transfer_result" class="confirm" />
			<?php else: ?>
				<p>�rets resultat er allerede nulstillet. Du kan f�re posterne tilbage, hvis du vil gemme igen.</p>
				<input type="submit" value="Forrige" name="previous" />
				<input type="submit" name="step_reverse_result_account_reset" value="Tilbagef�r posterne" />

				<input type="submit" name="next" value="N�ste" />
			<?php endif; ?>

			</fieldset>
		<?php
	break;
	case 7:
		?>
			<fieldset>
			<legend>Trin 7: L�s �ret?</legend>
				<p>Efter en �rsafslutning kan det v�re en god ide at l�se �ret, s� der ikke l�ngere kan bogf�res i det.</p>
				<div>
					<label><input type="radio" name="lock" value="1" <?php if ($year->get('locked') == 1) echo ' checked="checked"'; ?>/> L�s</label>
					<label><input type="radio" name="lock" value="0"<?php if ($year->get('locked') == 0) echo ' checked="checked"'; ?> /> L�s ikke</label>
				</div>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_lock_year" value="N�ste" />
			</fieldset>
		<?php
	break;


	case 8:
		?>
			<fieldset>
				<legend>Trin 8: Fyraften</legend>
				<p>Det er godt arbejde. Nu har du fortjent en pause. H�ber ikke det var for vanskeligt. Vi h�rer naturligvis altid gerne om dine oplevelser med programmet, s� vi kan forbedre det mest muligt.</p>
				<p><a class="excel" href="end_excel.php">Hent �rsregnskabet i et regneark</a></p>
				<input type="submit" value="Forrige" name="previous" />
			</fieldset>
		<?php
	break;
	default:
		trigger_error('Ugyldigt trin');
	break;

endswitch;
?>
</form>
<!--
<ol>
	<li>Hvis man har v�ret gennem hele guiden og lavet det hele, skal der bare v�re et link til en rapport - hvor man har mulighed for at �ndre noget tekst i</li>
	<li>Tjekker om bogf�ringen stemmer</li>
	<li>Tjekker om momskonti er t�mte</li>

	<li>Vi laver det som en KLIK-GUIDE med f�lgende sp�rgsm�l:
		<ul style="margin: 2em;">
			<li>Er alle poster fra i �r indtastet?</li>
			<li>Er momsregnskabet lavet og er posterne registreret rigtigt?</li>
		</ul>
	</li>
	<li>Overf�r poster til resultatkontoen. Der skal nok laves en tabel til det - s� kan det evt. ogs� fortrydes igen.</li>
	<li>Viser resultatkontoen.</li>
	<li>Der sp�rges om hvilken konto resultatet skal overf�res til - hvilket ofte vil v�re �rets resultat.</li>
	<li>Det er det �rsafslutningen skal kunne, men n�ste �r skal s� kunne starte med f�lgende:
		<ul>
			<li>Et nyt regnskab med samme kontoplan og indstillinger oprettes.</li>
			<li>Der sp�rges om statuskonti skal overf�res til nye regnskab som primosaldo.</li>
		</ul>
	</li>
</ol>
-->

<?php endif; ?>

<?php
$page->end();
?>