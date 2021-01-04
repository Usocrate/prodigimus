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
		$amounts = $system->getAmounts ();
		echo '<div class="list-group">';
		foreach ( $amounts as $a ) {
			echo '<a href="amount_edit.php?id=' . $a->id . '" class="list-group-item list-group-item-action">';
			echo '<div class="d-flex w-100 justify-content-between">';
			echo '<h5 class="mb-1">'.ToolBox::toHtml($a->title).'</h5>';
			echo '<small>'.ToolBox::toHtml($a->value).' '.ToolBox::toHtml($a->currency).'</small>';
			echo '</div>';
			echo '<p class="mb-1">'.ToolBox::toHtml($a->description).'</p>';
			//echo '<small><a href="'.ToolBox::toHtml($a->source_url).'">'.ToolBox::toHtml($a->source).'</a></small>';
			echo '<small>Source : <strong>'.ToolBox::toHtml($a->source).'</strong> ('.$a->source_url.')</small>';
			echo '</a>';
		}
		echo '</div>';
		?>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>