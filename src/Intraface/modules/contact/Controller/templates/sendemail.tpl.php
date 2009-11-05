<h1><?php e(t('Send email')); ?></h1>

<?php if ($context->getMessage()): ?>

<p><?php echo $context->getMessage(); ?></p>

<?php else: ?>

	<?php echo $context->getContact()->error->view(); ?>

<p class="message">Du er ved at sende en e-mail til <?php e(count($context->getContacts())); ?> kontakter. Vi sender naturligvis kun til de kontakter, der har en e-mail-adresse.</p>

<form action="<?php e(url()); ?>" method="post">
	<fieldset>

	<div class="formrow">
		<label for="title"><?php e(t('Subject')); ?></label>
		<input type="text" name="subject" size="60" value="<?php if (!empty($value['subject'])) e($value['subject']); ?>" />
	</div>
	<div class="formrow">
		<label for=""><?php e(t('Body text')); ?></label>
		<textarea name="text" cols="90" rows="20"><?php if (!empty($value['subject'])) e($value['text']); ?></textarea>
	</div>
	<div>
		<input type="submit" name="submit" value="<?php e(t('Send')); ?>" class="save" />
		<a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Cancel')); ?></a>
	</div>
	</fieldset>
</form>
<?php endif; ?>