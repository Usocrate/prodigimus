<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit ();
}

if (isset ( $_REQUEST ['cmd'] )) {
	$fb = new UserFeedBack();
	
	switch ($_REQUEST ['cmd']) {
		case 'create' :
			$system->createDatabase();
			break;
		case 'reinitAccountingEntries' :
			$pdo = $system->getPdo();
			$pdo->exec('DELETE FROM accounting_entry');
			$pdo->exec('ALTER TABLE accounting_entry AUTO_INCREMENT = 1');
			break;
		case 'revertTodayImportation' :
			$pdo = $system->getPdo();
			$result = $pdo->exec('DELETE FROM accounting_entry WHERE DATEDIFF(timestamp,CURDATE())=0');
			if ($result !== false) {
				if ($result > 1) {
					$fb->addSuccessMessage($result.' opérations ont été effacées.');
				} else if ($result == 1) {
					$fb->addSuccessMessage(' Une opération a été effacée.');
				} else {
					$fb->addSuccessMessage(' Aucune opération n\'aavit été importée aujourd\'hui.');
				}
			}
			break;
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
		<main class="px-lg-5">
			<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
			<?php if(isset($fb)) {
				echo $fb->toHtml();
			}
			?>
			<div class="list-group">
				<a href="database.php?cmd=create" class="list-group-item list-group-item-action">Recréer la base de données</a>
				<a href="database.php?cmd=reinitAccountingEntries" class="list-group-item list-group-item-action">Réinitialiser le stockage des opérations de compte</a>
				<a href="database.php?cmd=revertTodayImportation" class="list-group-item list-group-item-action">Annuler les importations d'opérations du jour</a>
			</div>
		</main>
	</div>
</body>
</html>