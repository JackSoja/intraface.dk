<?php
/**
 * keywords.php
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../include_first.php');

$kernel->useShared('keyword');

if (!empty($_GET['id'])) {
	$keyword = Keyword::factory($kernel, $_GET['id']);
}
else {
	trigger_error('Der er ikke angivet noget objekt i /shared/keyword/connect.php', FATAL);
}

$keywords = $keyword->getList($keyword->get('id'));

$page = new Page($kernel);
$page->start('Rediger n�gleord til produkt');

?>
<h1>N�gleord: <?php echo $keyword->get('keyword'); ?></h1>

<?php foreach ($keywords AS $key=>$value): ?>
	<?php echo $value; ?> er id i et objekt. Jeg skal bare lige finde ud af, hvordan jeg f�r knyttet objekterne til getList();
<?php endforeach; ?>

<?php
$page->end();
?>
