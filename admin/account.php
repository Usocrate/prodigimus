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
	exit();
}

if (! empty ( $_REQUEST ['id'] )) {
	$account = $system->getAccount ( $_REQUEST ['id'] );
} else {
	header ( 'Location:accounts.php' );
	exit ();
}

$messages = array ();

$doc_title = $account->description;
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
		<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}
		?>
		
		<div class="d-flex flex-row justify-content-between mb-3 mt-3">
			<h2>Dernières opérations</h2>
			<div>
				<a class="btn btn-outline-primary" href="accounting_entry_import.php?account_id=<?php echo $account->id; ?>">Importer de nouvelles opérations</a>
			</div>
		</div>
		
		<?php
		
		$entries = $system->getAccountingEntries($account);
		
		if (count($entries)>0) {
			echo AccountingEntry::collectionToHtml($entries);
		} else {
			echo '<p>Pas d\'opération enregistrée.</p>';
		}
		?>

	 </div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>