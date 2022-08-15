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

$doc_title = 'Comptes';

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

		<div class="d-lg-flex flex-lg-row justify-content-between align-items-center mb-3 mt-3">
			<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
			<a class="btn btn-outline-secondary" href="account_edit.php">Déclarer un nouveau compte</a>
		</div>
		
		<?php
		$accounts = $system->getAccounts ();
		echo '<div class="list-group">';
		foreach ( $accounts as $a ) {
			echo '<div>';
			echo '<a href="account.php?id=' . $a->id . '" class="list-group-item list-group-item-action">'.ToolBox::toHtml($a->description).'</a>';
			echo '</div>';
		}
		echo '</div>';
		?>
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>