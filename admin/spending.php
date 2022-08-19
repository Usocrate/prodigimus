<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit ();
}

$doc_title = 'Dépenses';

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

		<header>
			<div class="d-lg-flex flex-lg-row justify-content-between align-items-center mb-3 mt-3">
				<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
			</div>
		</header>
		
		<div class="row">
		<?php 
			$stats = $system->getYearToDateSpendingAmount();
			//print_r($stats);
			$now = new DateTime();
			$currentYear = $now->format('Y');
			$previousYear = $currentYear-1;
			foreach ($stats as $tag=>$amounts) {
				echo '<div class="col-lg-2">';
				echo '<div class="card text-center mb-2">';
				echo '<div class="card-body">';
				echo '<a href="tag.php?label='.urlencode($tag).'"><h5 class="card-title">'.ToolBox::toHtml($tag).'</h5></a>';
				echo '<div><big>'.$system->formatAmountToDisplay($amounts[$currentYear]).'</big></div>';
				if ($amounts[$previousYear]!=0) {
					echo '<div><small>('.round(((($amounts[$currentYear]-$amounts[$previousYear])/$amounts[$previousYear])*100),1).'%)</small></div>';
				}
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}
		?>
		</div>
		
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>