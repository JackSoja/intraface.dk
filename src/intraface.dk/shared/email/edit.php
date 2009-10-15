<?php
require('../../include_first.php');

$kernel->useShared('email');
$translation = $kernel->getTranslation('email');
$redirect = Intraface_Redirect::factory($kernel, 'receive');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


	$email = new Email($kernel, $_POST['id']);

    if ($kernel->user->hasModuleAccess('email')) {
        $email_module = $kernel->useModule('email');
        $standard_location = $email_module->getPath();
    }
    else {
        $standard_location = '/main/index.php';
    }

	if (isset($_POST['save']) || isset($_POST['send'])) {

        if (isset($_POST['add_contact_login_url'])) {
            $contact = $email->getContact();
            $_POST['body'] .= "\n\nLogin: ".$contact->getLoginUrl();
        }

        if ($id = $email->save($_POST)) {



            if (isset($_POST['send']) && $_POST['send'] != '' && $email->isReadyToSend()) {
                $email->send(Intraface_Mail::factory());
                $email->load();
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('send_email_status', $email->get('status'));
                }
                header('Location: '.$redirect->getRedirect($standard_location));
                exit;
            }

            header('Location: '.$redirect->getRedirect($standard_location));
			exit;
		}
		else {
			$value = $_POST;
		}
	}
    elseif (isset($_POST['delete'])) {
        $email->delete();
        // hmm maybe not the best redirect, but what else?
        header('Location: '.$redirect->getRedirect($standard_location));
        exit;
    }
	else {
        trigger_error("Invalid action to perform on email", E_USER_ERROR);
        exit;
    }
}
else {
	$email = new Email($kernel, $_GET['id']);
	$value = $email->get();
}

$page = new Intraface_Page($kernel);
$page->start('Skriv e-mail');

?>

<h1>Skriv e-mail</h1>

<?php echo $email->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<input type="hidden" name="id" value="<?php e($value['id']); ?>" />
	<input type="hidden" name="contact_id" value="<?php e($value['contact_id']); ?>" />
	<input type="hidden" name="type_id" value="<?php e($value['type_id']); ?>" />

	<fieldset>
		<legend><?php e(t('Recipient')); ?></legend>

		<div class="formrow">
			<label for="contact_person_id"><?php e(t('To', 'common')); ?></label>
			<?php
			$email->getContact();
			if (isset($email->contact->contactperson)) {
			    $contactpersons = $email->contact->contactperson->getList();
			}
			if ($email->contact->get('type') == 'corporation' && isset($contactpersons) && count($contactpersons) > 0) {
			    echo '<select name="contact_person_id" id="contact_person_id">';
			    echo '<option value="0">'.$email->contact->address->get('name').' &#60'.$email->contact->address->get('email').'&#62</option>';
			    foreach ($contactpersons AS $contactperson) {
                    echo '<option value="'.$contactperson['id'].'"';
			       	if ($value['contact_person_id'] == $contactperson['id']) {
			       	    echo ' selected="selected"';
			       	}
				    echo '>'.$contactperson['name'].' &#60'.$contactperson['email'].'&#62</option>';
			    }
			    echo '</select>';
			}
			else {
			    echo '<span id="contact_person_id">'.$email->contact->address->get('name').' &#60'.$email->contact->address->get('email').'&#62</span>';
			}
			?>
		</div>
		<div class="formrow">
			<label for="bcc_to_user"><?php e(t('BCC')); ?></label>
			<input type="checkbox" name="bcc_to_user" id="bcc_to_user" value="1" <?php if (isset($value['bcc_to_user']) && intval($value['bcc_to_user']) == 1) echo 'checked="checked"'; ?> /> <?php echo $kernel->user->getAddress()->get('name').' &#60'.$kernel->user->getAddress()->get('email').'&#62'; ?>
		</div>
		<div class="formrow">
			<label for="from"><?php e(t('From', 'common')); ?></label>
			<span id="from">
				<?php
				if ($email->get('from_email')) {
					e($email->get('from_name').' <'.$email->get('from_email').'>');
				} else {
				   e($kernel->intranet->address->get('email').' <'.$kernel->intranet->address->get('name').'>');
				}
				?>
			</span>
		</div>

	</fieldset>

	<fieldset>
		<legend><?php e(t('Subject')); ?></legend>
		<input size="80" type="text" name="subject" value="<?php e($value['subject']); ?>" />
	</fieldset>
	<fieldset>
		<legend><?php e(t('Body text')); ?></legend>
		<textarea cols="80" rows="12" class="resizable" name="body"><?php e(wordwrap($value['body'], 75)); ?></textarea>
		<br /><input type="checkbox" name="add_contact_login_url" value="1" /> <label for="add_customer_login_link"><?php e(t('Add login information')); ?> <?php echo $kernel->setting->get('intranet', 'contact.login_url'); ?></label>
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
				foreach ($attachments AS $attachment) {
				    $file = new FileHandler($kernel, $attachment['id']);
				    echo '<li><a href="'.$file->get('file_uri').'" target="_blank">'.$attachment['filename'].'</a></li>';
				}
				?>
			</ul>
		</fieldset>
	    <?php
	}
	?>
	<p>
		<input type="submit" class="confirm" name="send" value="<?php e(t('Send', 'common')); ?>"  />
        <?php if ($kernel->user->hasModuleAccess('email')): ?>
            <input type="submit" class="save" name="save" value="<?php e(t('Save in drafts')); ?>" />
        <?php endif; ?>
        <input type="submit" class="save" name="delete" value="<?php e(t('Delete', 'common')); ?>" />
		<a href="<?php e($redirect->getRedirect('email.php?id='.intval($value['id']))); ?>"><?php e(t('Cancel', 'common')); ?></a>
	</p>
</form>

<?php
$page->end();
?>
