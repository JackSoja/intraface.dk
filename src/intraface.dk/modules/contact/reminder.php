<?php
require '../../include_first.php';

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');
$contact_module->includeFile('ContactReminder.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$reminder = ContactReminder::factory($kernel, (int)$_POST['id']);
	if ($reminder->get('id') == 0) {
		trigger_error('Invalid reminder id', E_USER_ERROR);
	}

	if (isset($_POST['mark_as_seen'])) {
		$reminder->setStatus('seen');
	}
	elseif (isset($_POST['cancel'])) {
		$reminder->setStatus('cancelled');
	}
	elseif (isset($_POST['postpone_1_day'])) {
		$date = new Date($reminder->get('reminder_date'));
		$next_day = $date->getNextDay();
		$reminder->postponeUntil($next_day->getDate());
	}
    elseif (isset($_POST['postpone_1_week'])) {
		$date = new Date($reminder->get('reminder_date'));
		$date_span = new Date_Span();
		$date_span->setFromDays(7);
		$date->addSpan($date_span);
		$reminder->postponeUntil($date->getDate());
	}
	elseif (isset($_POST['postpone_1_month'])) {
		$date = new Date($reminder->get('reminder_date'));
		$date_span = new Date_Span();
        $date_calc = new Date_Calc();
		$date_parts = explode('-', $reminder->get('reminder_date'));
        $date_span->setFromDays($date_calc->daysInMonth($date_parts[1], $date_parts[0]));
		$date->addSpan($date_span);
		$reminder->postponeUntil($date->getDate());
	}
	elseif (isset($_POST['postpone_1_year'])) {
		$date = new Date($reminder->get('reminder_date'));
		$date_span = new Date_Span();
		$date_span->setFromDays(365); // does not take account of leap year
		$date->addSpan($date_span);
		$reminder->postponeUntil($date->getDate());
	}
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$reminder = ContactReminder::factory($kernel, (int)$_GET['id']);
	if ($reminder->get('id') == 0) {
		trigger_error('Invalid reminder id', E_USER_ERROR);
	}

}

if ($reminder->get('id') == 0) {
	trigger_error('Invalid reminder id', E_USER_ERROR);
}
$contact = $reminder->contact;

$page = new Intraface_Page($kernel);
$page->start(__('reminder'));
?>

<div id="colOne">

<div class="box">

	<h1><?php e(__('reminder')); ?>: <?php e($reminder->get('subject')); ?></h1>

	<ul class="options">
		<li><a href="reminder_edit.php?id=<?php e($reminder->get('id')); ?>"><?php e(__('edit', 'common')); ?></a></li>
		<li><a href="contact.php?id=<?php e($contact->get('id')); ?>"><?php e(__('close', 'common')); ?></a></li>
	</ul>

</div>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>">
	<input type="hidden" name="id" value="<?php e($reminder->get('id')); ?>" />
	<?php if ($reminder->get('status') == 'created'): ?>

			<input type="submit" value="<?php e(__('mark as seen')); ?>" name="mark_as_seen" class="confirm" title="<?php e(__('This will mark the reminder as seen')); ?>" />
			<input type="submit" value="<?php e(__('cancel', 'common')); ?>" name="cancel" class="confirm" title="<?php e(__('This will cancel the reminder')); ?>" />

			<?php e(__('postpone')); ?>:
			<input type="submit" value="<?php e(__('1 day')); ?>" name="postpone_1_day" class="confirm" title="<?php e(__('This will postpone the reminder with 1 day')); ?>" />
			<input type="submit" value="<?php e(__('1 week')); ?>" name="postpone_1_week" class="confirm" title="<?php e(__('This will postpone the reminder with 1 week')); ?>" />
			<input type="submit" value="<?php e(__('1 month')); ?>" name="postpone_1_month" class="confirm" title="<?php e(__('This will postpone the reminder with 1 month')); ?>" />
			<input type="submit" value="<?php e(__('1 year')); ?>" name="postpone_1_year" class="confirm" title="<?php e(__('This will postpone the reminder with 1 year')); ?>" />
			<a href="reminder_edit.php?id=<?php e($reminder->get('id')); ?>"><?php e(__('other')); ?></a>

	<?php endif; ?>
</form>

<?php echo $reminder->error->view(); ?>

<p><?php autohtml($reminder->get('description')); ?></p>

<table>
	<caption><?php e(__('reminder information')); ?></caption>
	<tbody>
	<tr>
		<th><?php e(__('reminder date')); ?></th>
		<td class="date"><?php e($reminder->get('dk_reminder_date')); ?></td>
	</tr>

	<tr>
		<th><?php e(__('status')); ?></th>
		<td><?php e($reminder->get('status')); ?></td>
	</tr>

	<tr>
		<th><?php e(__('created date')); ?></th>
		<td class="date"><?php e($reminder->get('dk_date_created')); ?></td>
	</tr>
    </tbody>
</table>
</div>
<div id="colTwo">


</div>

<?php
$page->end();
?>