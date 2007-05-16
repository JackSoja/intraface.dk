<?php
/**
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */
require('../../include_first.php');
require_once('Intraface/tools/Position.php');

$debtor_module = $kernel->module('debtor');
$translation = $kernel->getTranslation('debtor');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$debtor = Debtor::factory($kernel, intval($_POST['id']));

	// opdatere payment
	if($debtor->get("type") == "invoice") {
		$payment = new Payment($debtor);

		if(isset($_POST["payment"])) {
			$payment->update($_POST);
			$debtor->load();
		}
	}

	// slet debtoren
	if(!empty($_POST['delete'])) {

		$type = $debtor->get("type");
		$debtor->delete();
		header("Location: list.php?type=".$type."&amp;use_stored=true");
		exit;
	}


	elseif (!empty($_POST['send_electronic_invoice'])) {
		header('Location: send.php?send=electronic_email&id=' . intval($debtor->get('id')));
		exit;
	}
	elseif (!empty($_POST['send_email'])) {
		header('Location: send.php?send=email&id=' . intval($debtor->get('id')));
		exit;

	}

	// annuller ordre tilbud eller order
	elseif(!empty($_POST['cancel']) AND ($debtor->get("type") == "quotation" || $debtor->get("type") == "order") && $debtor->get('status') == "sent") {
		$debtor->setStatus('cancelled');
	}

	// s�t status til sendt
	elseif(!empty($_POST['sent'])) {
		$debtor->setStatus('sent');
	}


	// Overf�re tilbud til ordre
	elseif(!empty($_POST['order'])) {
		if($kernel->user->hasModuleAccess('order') && $debtor->get("type") == "quotation") {
			$kernel->useModule("order");
			$order = new Order($kernel);
			if($id = $order->create($debtor)) {
				header('Location: view.php?id='.$id);
				exit;
			}
		}
	}

	// Overf�re ordre til faktura
	elseif(!empty($_POST['invoice'])) {
		if($kernel->user->hasModuleAccess('invoice') && ($debtor->get("type") == "quotation" || $debtor->get("type") == "order")) {
			$kernel->useModule("invoice");
			$invoice = new Invoice($kernel);
			if($id = $invoice->create($debtor)) {
				header('Location: view.php?id='.$id);
				exit;
			}
		}
	}

	// Overf�r til kreditnota
	elseif(!empty($_POST['credit_note'])) {
		if($kernel->user->hasModuleAccess('invoice') && $debtor->get("type") == "invoice") {
			$credit_note = new CreditNote($kernel);

			if($id = $credit_note->create($debtor)) {
				header('Location: view.php?id='.$id);
				exit;
			}
		}
	}
}

elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

	$debtor = Debtor::factory($kernel, intval($_GET["id"]));


	// payment
	if($debtor->get("type") == "invoice") {
		$payment = new Payment($debtor);
	}

	// delete item
	if(isset($_GET["action"]) && $_GET["action"] == "delete_item") {
		$debtor->loadItem(intval($_GET["item_id"]));
		$debtor->item->delete();
	}

	// move item
	if(isset($_GET['action']) && $_GET['action'] == "moveup") {
		$debtor->loadItem(intval($_GET['item_id']));
		$debtor->item->moveUp();
	}

	// move item
	if(isset($_GET['action']) && $_GET['action'] == "movedown") {
		$debtor->loadItem(intval($_GET['item_id']));
		$debtor->item->moveDown();
	}

	// registrere onlinepayment
	if($debtor->get("type") == "invoice" && $kernel->user->hasModuleAccess('onlinepayment') && isset($_GET['onlinepayment_action']) && $_GET['onlinepayment_action'] != "" && $debtor->get("status") == "sent") {
		$onlinepayment_module = $kernel->useModule('onlinepayment'); // true: ignore user permisssion
		$onlinepayment = OnlinePayment::factory($kernel, 'id', intval($_GET['onlinepayment_id']));

		$onlinepayment->transactionAction($_GET['onlinepayment_action']);

		$debtor->load();
	}

	if(isset($_GET['edit_contact'])) {
		$contact_module = $kernel->getModule('contact');
		$redirect = Redirect::factory($kernel, 'go');
		$url = $redirect->setDestination($contact_module->getPath().'contact_edit.php?id='.intval($debtor->contact->get('id')), $debtor_module->getPath().'view.php?id='.$debtor->get('id'));
		header('location: '.$url);
		exit;
	}

	// Redirect til tilf�j produkt
	if(isset($_GET['add_item'])) {
		$redirect = Redirect::factory($kernel, 'go');
		$product_module = $kernel->useModule('product');
		$redirect->setIdentifier('add_item');
		$url = $redirect->setDestination($product_module->getPath().'select_product.php?set_quantity=1', $debtor_module->getPath().'view.php?id='.$debtor->get('id'));
		$redirect->askParameter('product_id', 'multiple');
		header('Location: '.$url);
		exit;
	}


	// Return fra tilf�j produkt
	if(isset($_GET['return_redirect_id'])) {
		$return_redirect = Redirect::factory($kernel, 'return');

		if($return_redirect->get('identifier') == 'add_item') {
			$selected_products = $return_redirect->getParameter('product_id', 'with_extra_value');
			foreach($selected_products AS $product) {
				$debtor->loadItem();
				$debtor->item->save(array('product_id' => $product['value'], 'quantity' => $product['extra_value'], 'description' => ''));
			}
			$return_redirect->delete();
			$debtor->load();
		}
		if($return_redirect->get('identifier') == 'send_email') {


			if($return_redirect->getParameter('send_email_status') == 'sent' OR $return_redirect->getParameter('send_email_status') == 'outbox') {

				// hvis faktura er genfremsendt skal den ikke s�tte status igen
				if ($debtor->get('status') != 'sent') {
					$debtor->setStatus('sent');
				}
				$return_redirect->delete();
			}

		}
	}
}

$page = new Page($kernel);
$page->includeJavascript('module', 'view.js');
$page->start(safeToHtml($translation->get($debtor->get('type'))));

?>

<div id="colOne"> <!-- style="float: left; width: 45%;" -->
<div class="box">
	<h1><?php print(safeToHtml($translation->get($debtor->get("type")))); ?> #<?php print(safeToHtml($debtor->get("number"))); ?></h1>

	<?php echo $debtor->error->view(); ?>

	<ul class="options">
		<?php if($debtor->get("locked") == false): ?>
			<li><a href="edit.php?id=<?php print(intval($debtor->get("id"))); ?>">Ret</a></li>
		<?php endif; ?>

		<li><a class="pdf" href="pdf_viewer.php?id=<?php print(intval($debtor->get("id"))); ?>" target="_blank">Udskriv PDF</a></li>

		<li><a href="list.php?id=<?php print(intval($debtor->get("id"))); ?>&amp;type=<?php echo safeToHtml($debtor->get("type")); ?>&amp;use_stored=true">Luk</a></li>

	</ul>

	<p><?php print(safeToHtml($debtor->get('description'))); ?></p>
</div>

<?php if($kernel->intranet->get("pdf_header_file_id") == 0 && $kernel->user->hasModuleAccess('administration')): ?>
	<div class="message-dependent">
		<p><a href="<?php echo PATH_WWW; ?>/main/controlpanel/intranet.php">Upload et logo</a> til dine pdf'er.</p>
	</div>
<?php endif; ?>

<?php if($debtor->contact->get('preferred_invoice') == 2): /* if the customer prefers e-mail */ ?>
	<?php 
	
	if($kernel->user->hasModuleAccess('administration')) {
		$module_administration = $kernel->useModule('administration');
	}
	$error_in_sender = false;
	
	switch($kernel->setting->get('intranet', 'debtor.sender')) {
		case 'intranet':
			if($kernel->intranet->address->get('name') == '' || $kernel->intranet->address->get('email') == '') {
				$error_in_sender = true;
				if($kernel->user->hasModuleAccess('administration')) {
					echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.$module_administration->getPath().'intranet_edit.php">'.$translation->get('do it now').'</a>.</p></div>';
				}
				else {
					echo '<div class="message-dependent"><p>'.$translation->get('you need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';
		
				}		
			}
			break;
		case 'user':
			if($kernel->user->address->get('name') == '' || $kernel->user->address->get('email') == '') {
				$error_in_sender = true;
				echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.PATH_WWW.'/main/controlpanel/user_edit.php">'.$translation->get('do it now').'</a>.</p></div>';		
			}
			break;
		case 'defined':
			if($kernel->setting->get('intranet', 'debtor.sender.name') == '' || $kernel->setting->get('intranet', 'debtor.sender.email') == '') {
				$error_in_sender = true;
				if($kernel->user->hasModuleAccess('administration')) {
					echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.$module_debtor->getPath().'settings.php">'.$translation->get('do it now').'</a>.</p></div>';
				}
				else {
					echo '<div class="message-dependent"><p>'.$translation->get('you need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';
				}
					
			}
			break;
		default:
			$error_in_sender = true;
			trigger_error("Invalid sender!", E_USER_ERROR);
			exit;
			
	}
	
	if($debtor->contact->address->get('email') == '') {
		$error_in_sender = true;
		echo '<div class="message-dependent"><p>'.$translation->get('you need to register an e-mail to the contact, so you can send e-mails').'</p></div>';		
			
	}
	?>
<?php endif; ?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
	<input type="hidden" name="id" value="<?php echo $debtor->get('id'); ?>" />
	<?php if ($debtor->contact->get('preferred_invoice') == 2 AND  $debtor->get('status') == 'created' AND !$error_in_sender): ?>
		<input type="submit" value="Send p� e-mail" name="send_email" class="confirm" title="Dette vil sende e-mail til kontakten" />
	<?php elseif ($debtor->contact->get('preferred_invoice') == 2 AND $debtor->get('status') == 'sent'): ?>
		<input type="submit" value="Genfremsend p� e-mail" name="send_email" class="confirm" title="Dette vil sende fakturaen igen" />
	<?php elseif ($debtor->get("type") == 'invoice' AND $debtor->contact->get('preferred_invoice') == 3 AND $debtor->contact->address->get('ean') AND $debtor->get('status') == 'created'): ?>
		<input type="submit" value="Send elektronisk faktura" name="send_electronic_invoice" class="confirm" title="Dette vil sende den elektroniske faktura til L�s-ind bureauet" />
	<?php elseif ($debtor->get("type") == 'invoice' AND $debtor->contact->get('preferred_invoice') == 3 AND $debtor->contact->address->get('ean') AND $debtor->get('status') == 'sent'): ?>
		<input type="submit" value="Genfremsend elektronisk faktura" name="send_electronic_invoice" class="confirm" title="Dette vil sende den elektroniske faktura igen" />
	<?php endif; ?>
	<?php if($debtor->get("status") == "created"): // make sure we can always mark as sent	?>
		<input type="submit" value="Marker som sendt" name="sent" />
	<?php endif; ?>

	<?php if(($debtor->get("type") == "invoice" && $debtor->get("status") == "created") || ($debtor->get("type") != "invoice" && $debtor->get("locked") == false)): ?>
		<input type="submit" value="Slet" class="confirm" title="Er du sikker p� du vil slette denne <?php print(safeToHtml($translation->get($debtor->get('type').' title'))); ?>?" name="delete" />
	<?php endif; ?>

	<?php if(($debtor->get("type") == "quotation" || $debtor->get("type") == "order") && $debtor->get('status') == "sent"): ?>
		<input type="submit" value="Annuller" name="cancel" class="confirm" title="Er du sikker p�, at du vil annullere?" />
	<?php endif; ?>

	<?php if($debtor->get("type") == "quotation" && $debtor->get('status') == "sent" && $kernel->user->hasModuleAccess('order')): ?>
		<input type="submit" value="L�g ind som ordre" name="order" class="confirm" value="Er du sikker p�, at du vil l�gge tilbuddet ind som ordre?" />
	<?php endif; ?>
	<?php if($debtor->get("type") == "quotation" && $debtor->get("status") == "sent" && $kernel->user->hasModuleAccess('invoice')): ?>
		<input type="submit" class="confirm" title="Er du sikker p�, at du vil fakturere dette tilbud?" name="invoice" value="Fakturer tilbuddet" />
	<?php endif; ?>
	<?php if($debtor->get("type") == "order" && $debtor->get("status") == "sent" && $kernel->user->hasModuleAccess('invoice')): ?>
		<input type="submit" class="confirm" title="Er du sikker p�, at du vil fakturere denne ordre?" name="invoice" value="Fakturer ordre" />
	<?php endif; ?>
	<?php if($debtor->get("type") == "invoice" && ($debtor->get("status") == "sent" OR $debtor->get("status") == 'executed')): // Opret kreditnota fra faktura ?>
		<input type="submit" class="confirm" title="Er du sikker p�, at du vil kreditere denne faktura?" name="credit_note" value="Krediter faktura" />

	<?php endif; ?>

</form>

<?php /* ?>
	<?php if(count($debtor->contact->compare()) > 0 && $debtor->get('locked') == false) {	?>
		<div style="border: 2px orange solid; padding: 1.5em; margin: 1em 0;">
		<h2 style="margin-top: 0; border-left: 10px solid green; padding-left: 0.5em; font-size: 1em; font-weight: strong;">Kunden eksisterer m�ske allerede i databasen?</h2>
		<p>Kunden ligner nogle af de andre kunder i kundekartoteket (baseret p� e-mail og postnummer). Du kan �ndre kunde p� ordren ved at v�lge en i listen nedenunder.</p>
		<table>
			<thead>
		  	<tr>
		    	<th>Navn</th>
		    	<th>Adresse</th>
		    	<th>Postby</th>
		     <th>Telefon</th>
		     <th>E-mail</th>
		     <th></th>
		    </tr>
		  </thead>
		  <tbody>
			<?php
				foreach ($debtor->contact->compare() AS $value=>$key) {
				$contact = new Contact($kernel, $key);
				?>
				<tr>
					<td><?php echo $contact->address->get('name'); ?></td>
					<td><?php echo $contact->address->get('address'); ?></td>
					<td><?php echo $contact->address->get('postcode'); ?> <?php echo $contact->address->get('city'); ?></td>
					<td><?php echo $contact->address->get('phone'); ?></td>
					<td><?php echo $contact->address->get('email'); ?></td>
					<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=changecontact&amp;new_id=<?php echo $contact->get('id'); ?>&amp;id=<?php echo $debtor->get('id'); ?>" onclick="return confirm('Er du sikker p� at du vil erstatte den nuv�rende kunde med den der er fundet i det eksisterende adressekartotek?');">[V�lg]</a></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		</div>
	<?php } ?>

<?php */ ?>
	<table>
		<caption><?php echo safeToHtml($translation->get($debtor->get('type').' title')); ?> information</caption>
		<tbody>
			<tr>
				<th>Dato</th>
				<td><?php print(safeToHtml($debtor->get("dk_this_date"))); ?></td>
			</tr>
			<?php if($debtor->get("type") != "credit_note") { ?>
			<tr>
				<th><?php print(safeToHtml($translation->get($debtor->get('type').' due date'))); ?>:</th>
				<td>
					<?php print(safeToHtml($debtor->get("dk_due_date"))); ?>
					<?php if ($debtor->get('type')=='invoice' && $debtor->anyDue($debtor->contact->get('id')) && $debtor->get("status") != 'executed') echo '<a href="reminder_edit.php?contact_id='.intval($debtor->contact->get('id')).'">Opret rykker</a>'; ?>
				</td>
			</tr>
			<?php } ?>

			

			<?php if ($kernel->setting->get('intranet', 'debtor.sender') == 'user' || $kernel->setting->get('intranet', 'debtor.sender') == 'defined'): ?>
				<tr>
					<th>Vores ref.</th>
						<td>
							<?php
							switch($kernel->setting->get('intranet', 'debtor.sender')) {
								case 'user':
									echo $kernel->user->address->get('name'). ' &lt;'.$kernel->user->address->get('email').'&gt;';
									break;
								case 'defined':
									echo $kernel->setting->get('intranet', 'debtor.sender.name').' &lt;'.$kernel->setting->get('intranet', 'debtor.sender.email').'&gt;';
									break;
							}
							
							if($kernel->user->hasModuleAccess('administration')) {
								echo ' <a href="'.$debtor_module->getPath().'setting.php" class="edit">'.safeToHtml($translation->get('change')).'</a></p>';	
							} 
							?>
						</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th>Status</th>
				<td>
					<?php
						echo safeToHtml($translation->get($debtor->get("status")));

					?>
				</td>
			</tr>
			<?php	if($debtor->get("type") == "invoice") {	?>
				<tr>
					<th>Betalingsmetode</th>
					<td><?php echo safeToHtml($debtor->get("translated_payment_method")); ?></td>
				</tr>
				<?php if($debtor->get("payment_method") == 3) { ?>
					<tr>
						<th>Girolinje</th>
						<td>+71&lt;<?php echo str_repeat("0", 15 - strlen($debtor->get("girocode"))).safeToHtml($debtor->get("girocode")); ?> +<?php print(safeToHtml($kernel->setting->get("intranet", "giro_account_number"))); ?>&lt;</td>
					</tr>
				<?php } ?>

				<?php if($debtor->get("status") == "executed") { ?>
					<tr>
						<th>Afsluttet dato:</th>
						<td><?php print(safeToHtml($debtor->get("dk_date_executed"))); ?></td>
					</tr>
				<?php } ?>
			<?php } ?>
			<tr>
				<th>Hvorfra</th>
				<td>
					<?php if($debtor->get("where_from_id") > 0) { ?>
						<a href="view.php?id=<?php print(intval($debtor->get("where_from_id"))); ?>"><?php echo safeToHtml($translation->get($debtor->get("where_from"))); ?></a>
       		<?php } else { ?>
						<?php echo safeToHtml($translation->get($debtor->get('where_from'))); ?>
					<?php } ?>
				</td>
			</tr>
			<?php if ($debtor->get('where_to') AND $debtor->get('where_to_id')): ?>
			<tr>
				<th>Hvortil</th>
				<td><a href="view.php?id=<?php echo intval($debtor->get('where_to_id')); ?>"><?php echo safeToHtml($translation->get($debtor->get('where_to'))); ?></a></td>
			</tr>
			<?php endif; ?>
			<?php if ($debtor->get("type") == 'invoice' AND $kernel->user->hasModuleAccess('accounting')): ?>
			<tr>
				<th>Bogf�rt</th>
				<td>
					<?php
						if ($debtor->get('dk_date_stated') != '00-00-0000') {
							echo safeToHtml($debtor->get('dk_date_stated')) . ' <a href="/modules/accounting/voucher.php?id='.$debtor->get('voucher_id').'">Se bilag</a>';
						}
						else {
							echo 'Ikke bogf�rt <a href="state.php?id=' . intval($debtor->get("id")) . '">Bogf�r faktura</a>';
						}
					?>
				</td>
			</tr>
			<?php elseif ($debtor->get("type") == 'credit_note' AND $kernel->user->hasModuleAccess('accounting')):?>
			<tr>
				<th>Bogf�rt</th>
				<td>
					<?php
						if ($debtor->get('dk_date_stated') != '00-00-0000') {
							echo safeToHtml($debtor->get('dk_date_stated')) . ' <a href="/modules/accounting/voucher.php?id='.$debtor->get('voucher_id').'">Se bilag</a>';
						}
						else {
							echo 'Ikke bogf�rt <a href="state_creditnote.php?id=' . intval($debtor->get("id")) . '">Bogf�r kreditnota</a>';
						}
					?>
				</td>
			</tr>

			<?php endif; ?>
		</tbody>
	</table>

	<?php if($debtor->get("message") != ''): ?>
		<fieldset>
			<legend>Tekst</legend>
			<p><?php print(nl2br(safeToHtml($debtor->get("message")))); ?></p>
		</fieldset>
	<?php endif; ?>
	
	<?php if($debtor->get("internal_note") != ''): ?>
		<fieldset>
			<legend>Intern note</legend>
			<?php
			$internal_note = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\" target=\"_blank\">\\0</a>", safeToHtml($debtor->get("internal_note")));
			?>
			<p><?php print(nl2br($internal_note)); ?></p>
		</fieldset>
	<?php endif; ?>

</div>

<div id="colTwo">	<!-- style="float: right; width: 45%;" -->
	<div class="box">
	<table>
		<caption>Kontaktoplysninger</caption>
		<tbody>
			<?php
			$contact_module = $kernel->getModule('contact');
			?>
			<tr>
				<th>Nummer</th>

				<td><?php print(safeToHtml($debtor->contact->get("number"))); ?> <a href="view.php?id=<?php print(intval($debtor->get('id'))); ?>&amp;edit_contact=<?php print(intval($debtor->contact->get('id'))); ?>" class="edit">Ret</a></td>
			</tr>
			<tr>
				<th>Kontakt</th>
				<td><a href="<?php print($contact_module->getPath()); ?>contact.php?id=<?php echo intval($debtor->contact->get('id')); ?>"><?php echo safeToHtml($debtor->contact->address->get("name")); ?></a></td>
			</tr>
			<?php if(get_class($debtor->contact_person) == "contactperson") { ?>
				<tr>
					<th>Kontaktperson</th>
					<td><?php echo safeToHtml($debtor->contact_person->get("name")); ?></td>
				</tr>
			<?php } ?>
			<tr>
				<th>Adresse</th>
				<td class="adr">
					<div class="adr">
						<div class="street-address"><?php print(nl2br(safeToHtml($debtor->contact->address->get("address")))); ?></div>
						<span class="postal-code"><?php print safeToHtml($debtor->contact->address->get('postcode')); ?></span>  <span class="location"><?php echo safeToHtml($debtor->contact->address->get('city')); ?></span>
						<div class="country"><?php echo safeToHtml($debtor->contact->address->get('country')); ?></div>
					</div>
				</td>
			</tr>
			<?php if($debtor->contact->address->get("cvr") != '' && $debtor->contact->address->get("cvr") != 0) { ?>
				<tr>
					<th>CVR</th>
					<td><?php echo safeToHtml($debtor->contact->address->get("cvr")); ?></td>
				</tr>
			<?php } ?>
			<?php if(isset($debtor->contact_person) && strtolower(get_class($debtor->contact_person)) == "contactperson"): ?>
				<tr>
					<th>Att.</th>
					<td><?php echo safeToHtml($debtor->contact_person->get("name")); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	</div>

	<?php
	if($debtor->get("type") == "invoice" && $debtor->get("status") == "sent") {
		?>
		<div class="box">
			<h2>Registr�r betaling</h2>
				<form method="post" action="view.php">
					<input type="hidden" value="<?php echo $debtor->get('id'); ?>" name="id" />
					<div class="formrow">
						<label for="payment_date" class="tight">Dato</label>
						<input type="text" name="payment_date" id="payment_date" value="<?php print(safeToHtml(date("d-m-Y"))); ?>" />
					</div>

					<div class="formrow">
						<label for="type" class="tight">Type</label>
						<select name="type" id="type">
							<?php
							$invoice_module = $kernel->getModule('invoice');
							$types = $invoice_module->getSetting('payment_type');
							foreach($types AS $key => $value) {
								?>
								<option value="<?php print(safeToHtml($key)); ?>" <?php if($key == 0) print("selected='selected'"); ?> ><?php echo safeToHtml($translation->get($value)); ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="formrow">
						<label for="amount" class="tight">Bel�b</label>
						<input type="text" name="amount" id="amount" value="<?php print(number_format($debtor->get("arrears"), 2, ",", ".")); ?>" /> <!-- $debtor->get("total") - $debtor->get('payment_total') - $debtor->get('payment_online') -->
					</div>

					<div style="clear: both;">
						<input class="confirm" type="submit" name="payment" value="Registr�r" title="Dette vil registrere betalingen" />
					</div>
				</form>
		</div>
		<?php
	}
	?>

</div>

<div style="clear: both">

	<?php
	if($debtor->get("type") == "invoice") {
		$payments = $payment->getList();
		$payment_total = 0;
		if(count($payments) > 0) {
			?>
				<table class="stripe">
					<caption>Betalinger</caption>
					<thead>
						<tr>
							<th>Dato</th>
							<th>Type</th>
							<th>Beskrivelse</th>
							<th>Bel�b</th>
						</tr>
					</thead>
					<tbody>
					<?php
					for($i = 0, $max = count($payments); $i < $max; $i++) {
						$payment_total += $payments[$i]["amount"];
						?>
						<tr>
							<td><?php print(safeToHtml($payments[$i]["dk_date"])); ?></td>
							<td><?php echo safeToHtml($translation->get($payments[$i]['type'])); ?></td>
							<td>
								<?php
								if($payments[$i]["type"] == "credit_note") {
									?>
									<a href="view.php?id=<?php print(intval($payments[$i]["id"])); ?>"><?php print(safeToHtml($payments[$i]["description"])); ?></a>
									<?php
								}
								else {
									echo safeToHtml($payments[$i]['description']);
								}
								?>
							</td>
							<td><?php print(number_format($payments[$i]["amount"], 2, ",", ".")); ?></td>
						</tr>
						<?php
					}
					?>
					</tbody>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><strong>I alt</strong></td>
						<td><?php print(number_format($payment_total, 2, ",", ".")); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<th>Manglende betaling</th>
						<td><?php echo number_format($debtor->get("total") - $payment_total, 2, ",", "."); ?></td>
					</tr>
				</table>
			<?php
		}
	}
	?>

	<?php

	if(($debtor->get("type") == "order" || $debtor->get("type") == "invoice") && $kernel->intranet->hasModuleAccess('onlinepayment')) {

		$onlinepayment_module = $kernel->useModule('onlinepayment', true); // true: ignore user permisssion
		$onlinepayment = new OnlinePayment($kernel);

		$onlinepayment->dbquery->setFilter('belong_to', $debtor->get("type"));
		$onlinepayment->dbquery->setFilter('belong_to_id', $debtor->get('id'));
		$actions = $onlinepayment->getTransactionActions();

		$payment_list = $onlinepayment->getlist();

		if(count($payment_list) > 0) {
			?>
			<div class="box">
				<h2>Onlinebetaling</h2>

				<table class="stribe">
					<thead>
						<tr>
							<th>Dato</th>
							<th>Transactionsnummer</th>
							<th>Status</th>
							<th>Bel�b</th>
							<th>&nbsp;</th>

						</tr>
					</thead>
					<tbody>
						<?php
						foreach($payment_list AS $p) {
							?>
							<tr>
								<td><?php print(safeToHtml($p['dk_date_created'])); ?></td>
								<td><?php print(safeToHtml($p['transaction_number'])); ?></td>
								<td>
									<?php
									// print($p['status']);
									print(safeToHtml($p['dk_status']));
									if($p['user_transaction_status_translated'] != "") {
										print(" (".safeToHtml($p['user_transaction_status_translated']).")");
									}
									elseif($p['status'] == 'authorized') {
										print(" (Ikke <acronym title=\"Betaling kan f�rst h�ves n�r faktura er sendt\">h�vet</acronym>)");
									}
									?>
								</td>
								<td><?php print(safeToHtml($p['dk_amount'])); ?></td>
								<td class="buttons">

									<?php if($debtor->get("type") == "invoice" && $kernel->user->hasModuleAccess('onlinepayment') && count($actions) > 0 && $debtor->get("status") == "sent" && $p['status'] == "authorized"): //   ?>
										<?php

										foreach($actions AS $a) {
											?>
											<a href="view.php?id=<?php print(intval($debtor->get('id'))); ?>&amp;onlinepayment_id=<?php print(intval($p['id'])); ?>&amp;onlinepayment_action=<?php print(safeToHtml($a['action'])); ?>" class="confirm"><?php print(safeToHtml($a['label'])); ?></a>
											<?php
										}
										?>
									<?php endif; ?>
									<?php if($p['status'] == 'authorized'): ?>
										<a href="<?php print($onlinepayment_module->getPath()); ?>payment.php?id=<?php print(intval($p['id'])); ?>" class="edit">Ret</a>
									<?php endif; ?>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}
	?>
<div style="clear:both;">

	<?php if($debtor->get("locked") == false) { ?>
		<ul class="options" style="clear: both;">
			<li><a href="view.php?id=<?php print(intval($debtor->get("id"))); ?>&amp;add_item=true">Tilf�j vare</a></li>
		</ul>
	<?php } ?>

	<table class="stripe" style="clear:both;">
		<caption>Varer</caption>
		<thead>
			<tr>
				<th>Varenr.</th>
				<th>Beskrivelse</th>
				<th colspan="2">Antal</th>
				<th>Pris</th>
				<th>Bel�b</th>
				<th>&nbsp;</th>
			</tr>
		</thead>


		<tbody>
			<?php
			$debtor->loadItem();
			$items = $debtor->item->getList();
			$total = 0;
			if(isset($items[0]["vat"])) {
				$vat = $items[0]["vat"]; // Er der moms p� det f�rste produkt
			}
			else {
				$vat = 0;
			}

			for($i = 0, $max = count($items); $i<$max; $i++) {
				$total += $items[$i]["quantity"] * $items[$i]["price"];
				$vat = $items[$i]["vat"];
				?>
				<tr id="i<?php echo intval($items[$i]["id"]); ?>" <?php if(isset($_GET['item_id']) && $_GET['item_id'] == $items[$i]['id']) print(' class="fade"'); ?>>
					<td><?php print(safeToHtml($items[$i]["number"])); ?></td>
					<td><?php print(safeToHtml($items[$i]["name"])); ?>
						<?php
						if($items[$i]["description"] != "") {
							print("<br />".nl2br(safeToHtml($items[$i]["description"])));
							if($debtor->get("locked") == false) {
								echo '<br /> <a href="item_edit.php?debtor_id='.intval($debtor->get('id')).'&amp;id='.intval($items[$i]["id"]).'">Ret tekst</a>';
							}
						}
						elseif($debtor->get("locked") == false) {
							echo ' <a href="item_edit.php?debtor_id='.intval($debtor->get('id')).'&amp;id='.intval($items[$i]["id"]).'">Tilf�j tekst</a>';
						}

						?>
					</td>
					<?php
					if($items[$i]["unit"] != "") {
						?>
						<td><?php echo number_format($items[$i]["quantity"], 2, ",", "."); ?></td>
						<td><?php echo safeToHtml($items[$i]["unit"]); ?></td>
						<td class="amount"><?php print(number_format($items[$i]["price"], 2, ",", ".")); ?></td>
						<?php
					}
					else {
						?>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<?php
					}
					?>
					<td><?php print(number_format($items[$i]["quantity"]*$items[$i]["price"], 2, ",", ".")); ?></td>
					<td class="options">
						<?php
						if($debtor->get("locked") == false) {
							?>
							<a class="moveup" href="view.php?id=<?php print(intval($debtor->get("id"))); ?>&amp;action=moveup&amp;item_id=<?php print(intval($items[$i]["id"])); ?>">Op</a>
							<a class="movedown" href="view.php?id=<?php print(intval($debtor->get("id"))); ?>&amp;action=movedown&amp;item_id=<?php print(intval($items[$i]["id"])); ?>">Ned</a>
							<a class="edit" href="item_edit.php?debtor_id=<?php echo intval($debtor->get('id')); ?>&amp;id=<?php print(intval($items[$i]["id"])); ?>">Ret</a>
							<a class="delete" title="Dette vil slette varen!" href="view.php?id=<?php print(intval($debtor->get("id"))); ?>&amp;action=delete_item&amp;item_id=<?php print(intval($items[$i]["id"])); ?>">Slet</a>
							<?php
						}
						?>&nbsp;
					</td>
				</tr>
				<?php

				if(($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
					// Hvis der er moms p� nuv�rende produkt, men n�ste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt
					?>
					<tr>
						<td>&nbsp;</td>
						<td><b>25% moms af <?php print(number_format($total, 2, ",", ".")); ?></b></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><b><?php print(number_format($total * 0.25, 2, ",", ".")); ?></b></td>
						<td>&nbsp;</td>
					</tr>
					<?php
					$total = $total * 1.25;
				}
			}
			?>
		</tbody>
		<?php if($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) { ?>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td colspan="3">I alt:</td>
				<td><?php print(number_format($total, 2, ",", ".")); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php } ?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="3"><b>Total<?php if($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) print("&nbsp;afrundet"); ?>:</b></td>
			<td><strong><?php print(number_format($debtor->get("total"), 2, ",", ".")); ?></strong></td>
			<td>&nbsp;</td>
		</tr>
	</table>
</div>
</div>

<?php
$page->end();
?>
