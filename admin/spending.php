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
			
			$progress_rate = array();
			$progress_amount = array();
			
			
			foreach ($stats as $tag=>$amounts) {
				if ($amounts[$previousYear]!=0) {
					$progress_rate [$tag] = round(((($amounts[$currentYear]-$amounts[$previousYear])/$amounts[$previousYear]))*100,1);
					$progress_amount [$tag] = round($amounts[$currentYear]-$amounts[$previousYear],2);
				}
			}
			arsort($progress_amount);
			echo '<div class="col-lg-6">';
			echo '<h2>En augmentation</h2>';
			echo '<p><small>Par rapport à '.$previousYear.', à la même date</small></p>';
			echo '<div class="row">';
			foreach ($progress_amount as $tag=>$value) {
				if ($value<=0) {
					break;
				}
				echo '<div class="col-lg-4">';
				echo '<div class="card text-center mb-2">';
				echo '<div class="card-body">';
				echo '<a href="tag.php?label='.urlencode($tag).'"><h5 class="card-title">'.ToolBox::toHtml($tag).'</h5></a>';
				echo '<div><big>+'.$system->formatAmountToDisplay($progress_amount[$tag]).'</big></div>';
				echo '<div><small>(soit +'.$progress_rate[$tag].'%)</small></div>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
			
			asort($progress_amount);
			echo '<div class="col-lg-6">';
			echo '<h3>En diminution</h3>';
			echo '<p><small>Par rapport à '.$previousYear.', à la même date</small></p>';
			echo '<div class="row">';
			foreach ($progress_amount as $tag=>$value) {
				if ($value>=0) {
					break;
				}
				echo '<div class="col-lg-4">';
				echo '<div class="card text-center mb-2">';
				echo '<div class="card-body">';
				echo '<a href="tag.php?label='.urlencode($tag).'"><h5 class="card-title">'.ToolBox::toHtml($tag).'</h5></a>';
				echo '<div><big>'.$system->formatAmountToDisplay($progress_amount[$tag]).'</big></div>';
				echo '<div><small>(soit '.$progress_rate[$tag].'%)</small></div>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		?>
		</div>
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>	
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>