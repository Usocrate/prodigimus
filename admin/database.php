<?php
function __autoload($class_name) {
	$path = '../classes/';
	if (is_file ( $path . $class_name . '.php' )) {
		include_once $path . $class_name . '.php';
	}
}

if (file_exists ( '../config/host.json' )) {
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit ();
}

if (isset ( $_REQUEST ['cmd'] )) {
	switch ($_REQUEST ['cmd']) {
		case 'create' :
			$system->createDatabase();
			break;
		case 'reinitAccountingEntries' :
			$pdo = $system->getPdo();
			$pdo->beginTransaction();
			$pdo->exec('DELETE FROM accounting_entry');
			$pdo->exec('ALTER TABLE accounting_entry AUTO_INCREMENT = 1');
			$pdo->commit();
	}
}

$doc_title = 'La base de données ('.$system->getDbName().')';

?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo ToolBox::toHtml($doc_title) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl(); ?>/theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
	<?php include 'navbar.inc.php'; ?>
	<div class="container-fluid">
		<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<div class="list-group">
			<a href="database.php?cmd=create" class="list-group-item list-group-item-action">Recréer la base de données</a>
			<a href="database.php?cmd=reinitAccountingEntries" class="list-group-item list-group-item-action">Réinitialiser le stockage des opérations de compte</a>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>