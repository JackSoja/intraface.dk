<h1>Moms <?php e($year->get('label')); ?></h1>

<ul class="options">
	<li><a href="vat_period.php">Luk</a></li>
</ul>

<?php if (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php else: ?>

	<?php echo $error->view(); ?>

	<?php if ($vat_period->get('status') == 'stated'): ?>
		<p class="message">Denne momsopgivelse er bogf�rt. <a href="<?php e($module->getPath()); ?>voucher.php?id=<?php e($vat_period->get('voucher_id')); ?>">Se bilag</a></p>
	<?php endif; ?>

	<?php if (!$vat_period->compareAmounts() AND $vat_period->get('status_key') > 0): // bel�b skal v�re gemt ?>
		<p class="warning">Det ser ud til, at du ikke har f�et bogf�rt alle momsbel�bene korrekt. Denne momsangivelse burde v�re 0, n�r den er bogf�rt.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('invoice', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle fakturaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=invoice&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($vat_period->get('date_start_dk')); ?>&amp;to_date=<?php e($vat_period->get('date_end_dk')); ?>">G� til fakturaer</a>.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('credit_note', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle kreditnotaer i perioden er ikke bogf�rt. <a href="/modules/debtor/list.php?type=credit_note&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($vat_period->get('date_start_dk')); ?>&amp;to_date=<?php e($vat_period->get('date_end_dk')); ?>">G� til kreditnotaer</a>.</p>
	<?php endif; ?>

	<table id="accounting-vat">
	<caption>Momsopg�relse for perioden <?php e($vat_period->get('date_start_dk')); ?> til <?php e($vat_period->get('date_end_dk')); ?></caption>
	<thead>
		<tr>
			<th>Kontonummer</th>
			<th>Kontobeskrivelse</th>
			<th colspan="2">Bel�b fra regnskabet</th>
		</tr>
	</thead>
	<tbody>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php e($account_vat_out->get('id')); ?>"><?php e($account_vat_out->get('number')); ?></a></td>
			<td><?php e($account_vat_out->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_out->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php e($account_vat_abroad->get('id')); ?>"><?php e($account_vat_abroad->get('number')); ?></a></td>
			<td><?php e($account_vat_abroad->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_abroad->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="headline">
			<td colspan="6"><h3>Fradrag</h3></td>
		</tr>
		<tr class="vat-buy">
			<td><a href="account.php?id=<?php e($account_vat_in->get('id')); ?>"><?php e($account_vat_in->get('number')); ?></a></td>
			<td><?php e($account_vat_in->get('name')); ?></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_in->get('saldo'))); ?></td>
			<td></td>
		</tr>
		<tr class="vat-amount">
			<th colspan="2">Afgiftsbel�b i alt</th>
			<td></td>
			<td class="amount debet"><?php echo amountToOutput($saldo_total, 0); ?></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik A. V�rdien uden moms af varek�b i andre <acronym title="Europ�iske Union">EU</acronym>-lande</td>
			<!--<td class="amount credit"><?php e($saldo_rubrik_a); ?></td>-->
			<td class="amount debet"><?php e($saldo_rubrik_a); ?></td>
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
			<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

				<input type="hidden" name="id" value="<?php e($vat_period->get('id')); ?>" />
			<fieldset>
				<legend>Bogf�r momsen</legend>
				<p>Du kan overf�re bel�bene til kassekladden ved at trykke p� knappen nedenunder. Du b�r f�rst trykke p� knappen, n�r du har opgivet bel�bene hos Skat.</p>
				<div class="formrow">
					<label for="date">Dato</label> <input type="text" name="date" id="date" value="<?php e($vat_period->get('date_end_dk')); ?>" />
				</div>
				<?php if ($vat_period->get('status') == 'stated'): ?>
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($vat_period->get('voucher_number')); ?>" /> Perioden er tidligere bogf�rt p� dette bilag
				</div>

				<?php else: ?>
				<div class="formrow">
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
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
