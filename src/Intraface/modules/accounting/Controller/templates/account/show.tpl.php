<h1><?php e($account->get('number')); ?>: <?php e($account->get('name')); ?></h1>

<ul class="options">
	<li><a href="account_edit.php?id=<?php e($account->get('id')); ?>">Ret</a></li>
	<li><a href="accounts.php?from_account_id=<?php e($account->get('id')); ?>">Luk</a></li>
</ul>

<!-- F�lgende b�r vises her, men kunne skjules med en indstilling
<table>
	<tr>
		<th rowspan="2">Beskrivelse</th>
		<td rowspan="2"><?php e($account->get('comment')); ?></td>
	</tr>
	<tr>
		<th>Type</th>
		<td><?php e($account->get('type')); ?></td>	</tr>
	<tr>
		<th>Moms</th>
		<td><?php e($account->get('vat')); ?></td>
	</tr>
</table>
-->

<p><?php e(t('vat')); ?>: <?php e(t($account->get('vat'))); ?> <?php if ($account->get('vat') != 'none'): ?><?php e(number_format($account->get('vat_percent'), 2, ',', '.').'%'); ?><?php endif; ?></p>

<?php if (!empty($posts) AND is_array($posts) AND count($posts) > 0) { ?>
	<table>
		<caption>Konti</caption>
		<thead>
			<tr>
					<th>Dato</th>
					<th>Bilag</th>
					<th>Tekst</th>
					<th>Debet</th>
					<th>Kredit</th>
					<th>Saldo</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($posts AS $post) { $saldo = $saldo + $post['debet'] - $post['credit']; ?>
			<tr>
				<td><?php if (isset($post['dk_date'])) e($post['dk_date']); ?></td>
				<td><?php if (isset($post['voucher_id'])): ?><a href="voucher.php?id=<?php e($post['voucher_id']); ?>"><?php e($post['voucher_number']); ?></a><?php endif; ?></td>
				<td><?php e($post['text']); ?></td>
				<td class="amount"><?php e(amountToOutput($post['debet'])); ?></td>
				<td class="amount"><?php e(amountToOutput($post['credit'])); ?></td>
				<td class="amount"><?php e(amountToOutput($saldo)); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

<?php } else { ?>
	<p>Der er endnu ikke bogf�rt nogle poster p� denne konto.</p>
<?php } // else ?>