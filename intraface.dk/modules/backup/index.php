<?php
/**
 * Backup
 *
 * Laves efter f�lgende anvisninger: http://wiki.dreamhost.com/index.php/Automatic_Backup
 *
 * @author Lars Olesen <lars@legestue.net>
 * @update October, 24th 2006
 */
require('../../include_first.php');

$module = $kernel->module('backup');
$translation = $kernel->getTranslation('administration');


if (!empty($_POST['mysql'])) {
	exec('bash /home/intraface/backup/mysql.sh');
}
elseif (!empty($_POST['domain'])) {
	exec('bash /home/intraface/backup/domain.sh');
}

$page = new Page($kernel);
$page->start('Backup');
?>

<h1>Backup af database</h1>

<form action="index.php" method="post">
	<fieldset>
		<legend>Backup</legend>
		<p><strong>Backup</strong>. P� denne side kan du lave en backup af enten filerne i dom�net eller systemets databaser. </p>
		<input type="submit" name="mysql" value="Database" />
		<input type="submit" name="domain" value="Filer" />
	</fieldset>
</form>
<?php
/*
<table>
	<caption>Backups</caption>
	<thead>
		<tr>
			<th>Tid</th>
			<th>Filnavn</th>
			<th>Download</th>
		</tr>
	</thead>
	<tbody>
	<?php
		if (is_dir(BACKUP_PATH) AND ($dh = opendir(BACKUP_PATH))) {
			while (($filename = readdir($dh)) !== false) {
				if (!isset($filename)) continue;
				if ($filename == '.' OR $filename=='..') continue;
	?>
		<tr>
			<td><?php echo date('d-m-Y H:i:s', filemtime(BACKUP_PATH . '/' . $filename)); ?></td>
			<td><?php echo $filename; ?></td>
			<td><a href="file.php?file=<?php echo $filename; ?>">Download</a></td>
		</tr>
	<?php
			}
			closedir($dh);
		}
	?>
	</tbody>
</table>
*/?>
<?php
$page->end();
?>
