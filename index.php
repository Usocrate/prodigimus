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
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl(); ?>/theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
	<?php include 'navbar.inc.php'; ?>
	<div class="container-fluid">
		<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<?php
			$amounts = $system->getAmounts();
			echo '<ol>';
			foreach($amounts as $a) {
				echo '<li><a href="amount_edit.php?id='.$a->id.'">'.ToolBox::toHtml($a->title).'</a>: '.$a->value.' <small>'.$a->currency.'</small></li>';
			}
			echo '<li><a href="amount_edit.php">Nouveau montant</a></li>';
			echo '</ol>';
		?>
	</div>
</body>
</html>