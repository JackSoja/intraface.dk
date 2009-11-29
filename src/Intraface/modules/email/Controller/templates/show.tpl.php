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
  <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit', 'common')); ?></a></li>
</ul>
<?php } ?>

<?php echo $email->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">
	<input type="hidden" value="put" name="_method" />

	<fieldset>
		<pre><?php e(t('To', 'common')); ?>: <?php
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
		<pre><?php e(t('From', 'common')); ?>: <?php if (isset($value['from_email']) && $value['from_email'] != ''): e($value['from_name']." <".$value['from_email'].">"); else: e($kernel->intranet->address->get('name')." <".$kernel->intranet->address->get('email').">"); endif; ?></pre>

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
		<input type="submit" name="submit" value="<?php e(t('Send', 'common')); ?>" class="confirm" />
		<a href="<?php e($redirect->getRedirect($this->url())); ?>"><?php e(t('Cancel', 'common')); ?></a>
	<?php endif; ?>
</form>
