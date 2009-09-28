<?php
require '../../include_first.php';

$kernel->useShared('email');
$redirect = Intraface_Redirect::factory($kernel, 'receive');

// hvordan skal denne laves helt pr�cist?
if (!empty($_POST)) {
	$email = new Email($kernel, $_POST['id']);

	if (!empty($_POST['submit'])) {
		if ($email->send(Intraface_Mail::factory())) {

			$email->load();
			// This status can be used to change status where the email is coming from.
			if ($redirect->get('id') != 0) {
				$redirect->setParameter('send_email_status', $email->get('status'));
			}

			/*
			// Moved to reminder.php triggered on return_redirect_id
			switch($email->get('type_id')) {
				case 5: // rykkere
					if (!$kernel->user->hasModuleAccess('debtor') OR !$kernel->user->hasModuleAccess('invoice')) {
						break;
					}

					$kernel->useModule('debtor');
					$kernel->useModule('invoice');
					$reminder = new Reminder($kernel, $email->get('belong_to_id'));
					$reminder->setStatus('sent');

					break;

				default:
					break;
			}
			*/
			header('Location: ' . $redirect->getRedirect('email.php?id='.$email->get('id')));
			exit;

		}
	}
}
else {
  $email = new Email($kernel, (int)$_GET['id']);
  $value = $email->get();
  $contact = $email->getContact();
}

$page = new Intraface_Page($kernel);
$page->start('Email');
?>
<h1><?php e(t('Email')); ?></h1>

<?php
if ($email->get('status') == 'sent') {
	echo '<p class="message">E-mailen er sendt.';
	if ($kernel->user->hasModuleAccess('email')) {
		$email_module = $kernel->useModule('email');
		echo ' <a href="'.$email_module->getPath().'">G� til e-mails</a>.';
	}
	echo '</p>';
}
else { ?>
<ul class="options">
  <li><a href="edit.php?id=<?php e($email->get('id')); ?>"><?php e(t('Edit')); ?></a></li>
</ul>
<?php } ?>

<?php echo $email->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<input type="hidden" value="<?php e($value['id']); ?>" name="id" />

	<fieldset>
		<pre><?php e(t('To')); ?><?php
            if ($contact->get('type') == 'corporation' && $email->get('contact_person_id') != 0) {

                $contact->loadContactPerson($email->get('contact_person_id'));
                if ($contact->contactperson->get('email') != '') {
                    e($contact->contactperson->get('name')." <".$contact->contactperson->get('email').">");
                }
                else {
                    e($contact->address->get('name')." <".$contact->address->get('email').">");
                }
            }
            else {
               e($contact->address->get('name')." <".$contact->address->get('email').">");
            }
            ?></pre>
		<pre><?php e(t('From')); ?><?php if (isset($value['from_email']) && $value['from_email'] != ''): e($value['from_name']." <".$value['from_email'].">"); else: e($kernel->intranet->address->get('name')." <".$kernel->intranet->address->get('email').">"); endif; ?></pre>

		<pre><?php e($value['subject']); ?></pre>
	</fieldset>

	<fieldset>
		<pre><?php e(wordwrap($value['body'], 75)); ?></pre>
	</fieldset>

	<?php
	$attachments = $email->getAttachments();

	if (count($attachments) > 0) {
	    ?>
	    <fieldset>
			<legend><?php e(t('Attachments')); ?></legend>
			<ul>
				<?php
				$kernel->useShared('filehandler');
				foreach ($attachments as $attachment) {
				    $file = new FileHandler($kernel, $attachment['id']);
				    echo '<li><a href="'.$file->get('file_uri').'" target="_blank">'.$attachment['filename'].'</a></li>';
				}
				?>
			</ul>
		</fieldset>
	    <?php
	}
	?>

	<?php if (!$email->isReadyToSend()): ?>
		<?php echo $email->error->view(); /* errors is first set in isReadyToSend, therefor we show the errors here */  ?>
	<?php elseif ($email->get('status') != 'sent'): ?>
		<input type="submit" name="submit" value="<?php e(t('Send')); ?>" class="save" />
		<a href="<?php e($redirect->getRedirect('email.php?id='.$email->get('id'))); ?>"><?php e(t('Cancel')); ?></a>
	<?php endif; ?>
</form>

<?php
$page->end();
?>