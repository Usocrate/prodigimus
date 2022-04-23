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
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
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
		
		<canvas id="chartCanvas" style="margin:2em 0; max-height:320px; min-height:200px"></canvas>
		
		<?php 
			$spendings = $system->getTagSpendingStats($_REQUEST ['label']);
			$cumulativeSpendings = array();
			
			foreach ($spendings as $year => $monthAmounts ) {
				foreach ($monthAmounts as $month => $amount ) {
					$cumulativeSpendings[$year][$month] = $month > 1 ? $amount + $cumulativeSpendings[$year][$month-1] : $amount;
				}
			}
		
			echo '<div class="table-responsive">';
			echo '<table class="table">';
			echo '<tr><th>Année</th><th>Jan.</th><th>Fév.</th><th>Mars.</th><th>Avr.</th><th>Mai</th><th>Juin</th><th>Juil.</th><th>Août</th><th>Sept.</th><th>Oct.</th><th>Nov.</th><th>Déc.</th></tr>';
			
			$nf = new NumberFormatter ( 'fr_FR', NumberFormatter::CURRENCY );
			
			foreach ($spendings as $year=>$months) {
				echo '<tr>';
				echo '<th>'.$year.'</th>';
				for ($i=1; $i<13; $i++) {
					echo isset($months[$i]) ? '<td><a href="#m'.$year.$i.'">'.$nf->formatCurrency ( $months[$i], 'EUR' ).'</a></td>' : '<td></td>';
				}
				echo '</tr>';
			}
			echo '</table>';
			echo '</div>';
		?>
		<h2>Toutes les dépenses</h2>
		<?php 
			$entries = $system->getTagSpendingAccountingEntries($_REQUEST ['label']);
			
			if (count($entries)>0) {
				
				echo '<div class="container-fluid">';
				echo '<div class="row">';
				
				$i=0; // nombre d'items traités
				$m=0; // nombre de mois traités
				
				foreach ( $entries as $e ) {
					$i++;
					
					$date = $e->getDate ();
					$month = $date->format ( 'M Y' );
					// var_dump($month);
					
					if (! isset ( $lastDisplayedMonth ) || strcmp ( $month, $lastDisplayedMonth ) != 0) {
						if (isset ( $lastDisplayedMonth )) {
							echo '</ul></div>';  // fermeture de la colonne et bloc
						}

						//if ($m % 3 == 0 && $m>0) echo '</div><div class="row">';
						
						echo '<div class="col-lg-6 col-xl-4">';
						echo '<h3 id="m'.$date->format('Yn').'" class="mt-3">' . $e->getMonthToDisplay () . '</h3>';
						echo '<ul class="list-group">';
						$m++;
						$lastDisplayedMonth = $month;
					}
					
					echo '<li class="list-group-item">';
					echo '<div class="d-flex w-100 justify-content-between">';
					echo '<div>';
					echo '<small>'.$date->format ( 'd' ) . ' ' . $e->getMonthToDisplay ().'</small></br>';
					echo '<h4><a href="' . $system->getAccountingEntryAdminUrl ( $e ) . '">' . ToolBox::toHtml ( $e->description ) . '</a></h4>';
					
					echo $e->getAmountToDisplay ();
					
					if ($e->isTagged()) {
						echo '<div>' . $e->getHtmlTags () . '</div>';
					}
					echo '</div>';
					
					echo '<div>';
					echo '</div>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul></div>'; // fermeture du dernier bloc
				
				echo '</div>'; // fermeture de la dernière ligne
				echo '</div>'; // fermeture du container
				
			} else {
				echo '<p>Pas d\'opération enregistrée.</p>';
			}
		?>
	</div>
	<script>
	const ctx = document.getElementById('chartCanvas');
	const myChart = new Chart(ctx, {
	    type: 'line',
	    data: {
	        labels: ['Jan.', 'Fév.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
	        datasets: [
		    {
	            label: 'Dépenses cumulées cette année',
	            data: [<?php echo implode(',', $cumulativeSpendings[date('Y')]) ?>],
	            backgroundColor: '<?php echo ToolBox::hex2rgba($system->getAppliThemeColor(),0.7) ?>',
	            fill:true
	        },
		    {
	            label: 'L\'année dernière',
	            data: [<?php echo implode(',', $cumulativeSpendings[date('Y')-1]) ?>],
	            fill:true
            },
	        ]
	    },
	    options: {
	        scales: {
	            y: {
	                beginAtZero: true
	            }
	        }
	    }
	});
	</script>
</body>
</html>