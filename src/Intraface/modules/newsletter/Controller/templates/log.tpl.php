<?php
$logs = $context->getLog();
?>

<h1><?php e(t('Log')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close'))?></a></li>
</ul>

<table class="stripe">
    <caption><?php e(t('Unsubscribed')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('Date')); ?></th>
        <th><?php e(t('Contact #id')); ?></th>
        <th><?php e(t('Comment')); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php while ($logs->nextRecord()) : ?>
    <tr>
        <td><?php e($logs->f('dk_date_unsubscribe')); ?></td>
        <td><a href="<?php e(url('../../../../contact/contact.php', array('id' => $logs->f('contact_id')))); ?>"><?php e($logs->f('name')); ?></a></td>
        <td><?php e($logs->f('unsubscribe_comment')); ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
