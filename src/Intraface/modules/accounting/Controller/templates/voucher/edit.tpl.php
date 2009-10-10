<?php
$value = $context->getValues();
?>

<h1>Rediger bilag #<?php e($context->getVoucher()->get('number')); ?> p� <?php e($context->getYear()->get('label')); ?></h1>

<?php echo $context->getVoucher()->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

	<fieldset>
		<input type="hidden" value="<?php e($value['id']); ?>" name="id" />
		<div class="formrow">
			<label for="date">Dato</label>
			<input type="text" value="<?php if (!empty($value['date'])) e($value['date']); ?>" name="date" />
		</div>
		<div class="formrow">
			<label for="number">Nummer</label>
			<input type="text" value="<?php if (!empty($value['number'])) e($value['number']); ?>" name="voucher_number" />
		</div>
		<div class="formrow">
			<label for="text">Tekst</label>
			<input type="text" value="<?php if (!empty($value['text'])) e($value['text']); ?>" name="text" />
		</div>
		<div class="formrow">
			<label for="reference">Reference</label>
			<input type="text" value="<?php if (!empty($value['reference'])) e($value['reference']); ?>" name="reference" />
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Gem" />
		<a href="<?php e(url('../')); ?>">fortryd</a>
	</div>

</form>