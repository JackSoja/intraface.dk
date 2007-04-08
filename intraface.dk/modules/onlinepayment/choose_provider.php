<?php
/**
 * Kan kun v�lge gyldige providers
 * Svare p� sp�rgsm�l om pbsadgangen
 *
 */
require('../../include_first.php');

$onlinepayment_module = $kernel->module('onlinepayment');
$implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

if (!empty($_POST)) {

	$onlinepayment = new OnlinePayment($kernel);
	if ($onlinepayment->setProvider($_POST)) {
		header('Location: settings.php');
		exit;
	}
	else {
		$value = $_POST;
	}

}
else {
	$onlinepayment = new OnlinePayment($kernel);
	$value = $onlinepayment->getProvider();
}

$page = new Page($kernel);
$page->start('Onlinebetalinger');
?>

<h1>V�lg udbyder</h1>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>
		<legend>Udbyder</legend>
		<div class="formrow">
			<label for="provider">Udbyder</label>
			<select name="provider_key" id="provider">
				<option value="">V�lg</option>
				<?php
					foreach($implemented_providers AS $key => $provider):
						if ($provider == '_invalid_') continue;
						echo '<option value="'.$key.'"';
						if ($value['provider_key'] == $key):
							echo ' selected="selected"';
						endif;
						echo '>'.$provider.'</option>';
					endforeach;
				?>
			</select>
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Gem" />
	</div>

<form>

<?php

$page->end();

?>