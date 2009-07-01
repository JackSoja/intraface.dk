<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
	$kernel->setting->set('user', 'accounting.state.message', 'hide');
}
elseif (!empty($_GET['message2']) AND in_array($_GET['message2'], array('hide'))) {
	$kernel->setting->set('user', 'accounting.state.message2', 'hide');
}

// bogf�re poster i kassekladden
if (!empty($_POST['state'])) {
	// hvordan skal dette laves?

	$voucher = new Voucher($year);
	// denne funktion v�lger automatisk alle poster i kassekladden
	if (!$voucher->stateDraft()) {
		// $post->error->set('Posterne kunne ikke bogf�res');
	}
	/*
	$post = new Post($voucher);
	$posts = $post->getList();
	*/
	header('Location: state.php');
	exit;

}
else {
	$voucher = new Voucher($year);
	$post = new Post($voucher);
}

$posts = $post->getList('draft');
$accounts = $year->getBalanceAccounts();


// starting page
$page = new Intraface_Page($kernel);
$page->start('Bogf�r');
?>

<h1>Bogf�r <?php e($year->get('label')); ?></h1>

<?php if ($kernel->setting->get('user', 'accounting.state.message') == 'view'): ?>
<div class="message">
	<p><strong>Bogf�r</strong>. P� denne side bogf�rer du posterne fra kassekladden. N�r du har bogf�rt bel�bene, kan du ikke l�ngere redigere i posterne.</p>
	<p><strong>Hvis du laver fejl</strong>. Hvis du har bogf�rt noget forkert, skal du lave et bilag med en rettelsespost, som du s� bogf�rer, s� dine konti kommer til at stemme.</p>
	<p><a href="<?php e($_SERVER['PHP_SELF']); ?>?message=hide">Skjul</a></p>
</div>
<?php endif; ?>


<h2>Afstemningskonti</h2>

<?php if ($kernel->setting->get('user', 'accounting.state.message2') == 'view'): ?>
<div class="message">
	<p><strong>Afstemning</strong>. Du b�r afstemme dine konti, inden du bogf�rer. Det betyder, at du fx b�r tjekke om bel�bene p� dit kontoudtog er magen til det bel�b, der bliver bogf�rt.</p>
	<p><a href="<?php e($_SERVER['PHP_SELF']); ?>?message2=hide">Skjul</a></p>
</div>
<?php endif; ?>

<?php if (!empty($accounts) AND count($accounts) > 0) { ?>

<table class="stripe">
<caption>Afstemningskonti (<a href="setting.php">skift konti</a>)</caption>
<thead>
	<tr>
		<th scope="col">Kontonummer</th>
		<th scope="col">Kontonavn</th>
		<th scope="col">Startsaldo</th>
		<th scope="col">Bev�gelse</th>
		<th scope="col">Slutsaldo</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($accounts AS $account) { ?>
	<tr>
		<td><a href="account.php?id=<?php e($account['id']); ?>"><?php e($account['number']); ?></a></td>
		<td><?php e($account['name']); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_primo'])); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_draft'])); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_ultimo'])); ?></td>
	</tr>
	<?php  } ?>
</tbody>
</table>

<?php } else { ?>

	<p class="message-dependent">Der er ikke angivet nogen afstemningskonti. Du kan angive afstemningskonti under <a href="setting.php">indstillingerne</a>.</p>

<?php } ?>

<h2>Bogf�r</h2>

<?php echo $voucher->error->view(); ?>

<?php if (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Du skal f�rst <a href="setting.php">s�tte momskonti</a>, inden du kan bogf�re.</p>
<?php elseif ($voucher->get('list_saldo') > 0): ?>
	<p class="error">Kassekladden balancerer ikke. Du kan ikke bogf�re, f�r den balancerer.</p>
<?php elseif (!empty($posts) AND count($posts) > 0): // der skal kun kunne bogf�res, hvis der er nogle poster ?>
<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<fieldset>
		<p>Bogf�r posterne og t�m kassekladden. Husk, at du ikke l�ngere kan redigere i posterne, n�r du har klikket p� knappen. Bev�gelserne kan derefter ses i regnskabet.</p>
		<div><input type="submit" value="Bogf�r" name="state" onclick="return confirm('Er du sikker p�, at du vil bogf�re?');" /></div>
	</fieldset>
</form>
<?php else: ?>
	<p class="message-dependent">Der er ingen poster i kassekladden. Du skal <a href="daybook.php">indtaste poster i kassekladden</a>, inden du kan bogf�re.</p>
<?php endif; ?>


<?php
$page->end();
?>
