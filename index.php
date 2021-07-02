<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.php' )) {
		include_once $path . $class_name . '.php';
	}
}

if (file_exists ( './config/host.json' )) {
	$system = new System ( './config/host.json' );
} else {
	header ( 'Location:./admin/config.php' );
	exit();
}

$doc_title = 'Montants';

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
		<?php
			$amounts = $system->getAmounts();
			$nf = new NumberFormatter('fr_FR', NumberFormatter::CURRENCY);
			if (count($amounts)>0) {
				echo '<ol>';
				foreach($amounts as $a) {
					echo '<li>'.ToolBox::toHtml($a->getTitle()).' : '.$a->getValueToDisplay($nf).'</li>';
				}
				echo '</ol>';
			} else {
				echo '<div class="alert alert-info" role="alert">Aucun montant enregistré pour le moment.</div>';
			}
		?>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script type="text/javascript" src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>