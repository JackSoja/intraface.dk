<?php
require('../../include_first.php');

$module = $kernel->module("filemanager");

$file_dir = $kernel->intranet->get('id').'/import/';

if(isset($_POST["submit"])) {

	$filemanager = new FileManager($kernel);
	$filemanager->createUpload();

	$filemanager->upload->setSetting('file_accessibility', $_POST['accessibility']);
	$filemanager->upload->setSetting('max_file_size', 800000);
	$filemanager->upload->setSetting('add_keyword', $_POST['keyword']);

	if($filemanager->upload->import(UPLOAD_PATH.$file_dir)) {
		// header("location: file.php?id=".$id);
		// her burde den g� til en batchedit af de uploadede filer!
		die("F�RDIG");
		exit;
	}
}
else {
	$filemanager = new FileManager($kernel);
}


$page = new Page($kernel);
$page->start();
?>

<h1>Importer filer</h1>

<?php $filemanager->error->view(); ?>

<p>Importere fra <?php print($file_dir); ?></p>

<form action="import.php" method="POST">
<fieldset>
	<legend>Oplysninger</legend>

	<div class="formrow">
		<label for="accessibility">Tilg�ngelighed</label>
		<select name="accessibility">
			<option value="intranet">Kun inden for intranettet</option>
			<option value="public">B�de inden og uden for intranettet</option>
		</select>
	</div>

	<div class="formrow">
		<label for="keyword">N�gleord</label>
		<input type="text" name="keyword" id="keyword" value="" />
	</div>

</fieldset>

<input type="submit" class="save" name="submit" value="Start import" />
eller
<a href="index.php">Fortryd</a>

</form>

<?php
$page->end();
?>