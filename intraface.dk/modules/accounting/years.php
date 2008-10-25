<?php
require '../../include_first.php';

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

// her s�tter vi et �r
if (!empty($_POST['id']) AND is_numeric($_POST['id'])) {
	$year = new Year($kernel, $_POST['id']);
	if (!$year->setYear()) {
		trigger_error('Kunne ikke s�tte �ret', E_USER_ERROR);
	}

	header('Location: daybook.php');
	exit;

}

$year = new Year($kernel);
$years = $year->getList();

$page = new Intraface_Page($kernel);

$page->start('V�lg regnskab');
?>

<h1>Regnskabs�r</h1>

<div class="message">
	<p><strong>Regnskabs�r</strong>. P� denne side kan du enten oprette et nyt regnskab eller v�lge hvilket regnskab, du vil begynde at indtaste poster i. Du v�lger regnskabet p� listen nedenunder.</p>
</div>

<ul class="options">
	<li><a class="new" href="year_edit.php">Opret regnskabs�r</a></li>
</ul>

<?php if (empty($years)): ?>
	<p>Der er ikke oprettet nogen regnskabs�r. Du kan oprette et ved at klikke p� knappen ovenover.</p>
<?php else: ?>
	<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<table>
		<caption>Regnskabs�r</caption>
		<thead>
			<tr>
				<th></th>
				<th>�r</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($years AS $y): ?>
		<tr>
			<td><input type="radio" name="id" value="<?php e($y['id']); ?>" <?php if ($year->loadActiveYear() == $y['id']) { echo ' checked="checked"'; } ?>/></td>
			<td><a href="year.php?id=<?php e($y['id']); ?>"><?php e($y['label']); ?></a></td>
			<td class="options">
				<a class="edit" href="year_edit.php?id=<?php e($y['id']); ?>">Ret</a>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="submit" value="V�lg" />
	</form>
<?php endif; ?>

<?php
$page->end();
?>
