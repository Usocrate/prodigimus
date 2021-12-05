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

if (! empty ( $_REQUEST ['label'] )) {
	
} else {
	header ( 'Location:index.php' );
	exit ();
}

$messages = array ();

$doc_title = $_REQUEST ['label'];
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
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="accounts.php">Comptes</a></li>
				<li class="breadcrumb-item active"><?php echo ToolBox::toHtml($doc_title) ?></li>
			</ol>
		</nav>	
		<h1><?php echo ucfirst(ToolBox::toHtml($doc_title)); ?></h1>
		<?php 
			$rows = $system->getTagSpendingStats($_REQUEST ['label']);
			
			$datatable = array();
			foreach ($rows as $row ) {
				$datatable[$row['year']][$row['month']] = $row['amount'];
			}
			//var_dump($datatable);
			
			//*
			echo '<table class="table">';
			echo '<tr><th>Année</th><th>Jan.</th><th>Fév.</th><th>Mars.</th><th>Avr.</th><th>Mai</th><th>Juin</th><th>Juil.</th><th>Août</th><th>Sept.</th><th>Oct.</th><th>Nov.</th><th>Déc.</th></tr>';
			
			$nf = new NumberFormatter ( 'fr_FR', NumberFormatter::CURRENCY );
			
			foreach ($datatable as $year=>$months) {
				echo '<tr>';
				echo '<th>'.$year.'</th>';
				for ($i=1; $i<13; $i++) {
					echo isset($months[$i]) ? '<td>'.$nf->formatCurrency ( $months[$i], 'EUR' ).'</td>' : '<td></td>';
				}
				echo '</tr>';
			}
			echo '</table>';
			//*/
		?>
		<h2>Les dépenses</h2>
		<?php 
			$entries = $system->getTagSpendingAccountingEntries($_REQUEST ['label']);
			
			if (count($entries)>0) {
				$html = '';
				
				foreach ( $entries as $e ) {
					
					$date = $e->getDate ();
					$month = $date->format ( 'M Y' );
					// var_dump($month);
					
					if (! isset ( $lastDisplayedMonth ) || strcmp ( $month, $lastDisplayedMonth ) != 0) {
						if (isset ( $lastDisplayedMonth )) {
							echo '</ul>';
						}
						echo  '<h3 class="mt-3">' . $e->getMonthToDisplay () . '</h3>';
						echo '<ul class="list-group">';
						$lastDisplayedMonth = $month;
					}
					
					echo '<li class="list-group-item">';
					echo '<div class="d-flex w-100 justify-content-between">';
					echo '<div>';
					echo '<small>'.$date->format ( 'd' ) . ' ' . $e->getMonthToDisplay ().'</small></br>';
					echo '<a href="' . $system->getAccountingEntryAdminUrl ( $e ) . '">' . ToolBox::toHtml ( $e->description ) . '</a>';
					
					if ($e->isTagged()) {
						echo '<div>' . $e->getHtmlTags () . '</div>';
					}
					echo '</div>';
					
					echo '<div>';
					switch ($e->type) {
						case 'earning' :
							echo '<small>Revenu</small><br/>';
							echo $e->getAmountToDisplay ();
							break;
						case 'spending' :
							echo '<small>Dépense</small><br/>';
							echo $e->getAmountToDisplay ();
							break;
						default :
							echo $e->getAmountToDisplay ();
					}
					echo '</div>';
					echo '</div>';
					echo '</li>';
				}
			} else {
				echo '<p>Pas d\'opération enregistrée.</p>';
			}
		?>
	</div>
</body>
</html>