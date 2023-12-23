<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit ();
}

if (! empty ( $_REQUEST ['id'] )) {
	$accounting_entry = $system->getAccountingEntry ( $_REQUEST ['id'] );
	$account = $system->getAccount ( $accounting_entry->getAccountId () );
} else {
	header ( 'Location:accounts.php' );
	exit ();
}

$messages = array ();

$doc_title = 'Opération';
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
				<li class="breadcrumb-item"><a href="account.php?id=<?php echo $account->getId() ?>">Compte</a></li>
				<li class="breadcrumb-item active"><?php echo ToolBox::toHtml($doc_title) ?></li>
			</ol>
		</nav>
		
		<main class="px-lg-5">

		<header>
			<div class="d-lg-flex flex-lg-row justify-content-between align-items-center mb-3 mt-3">
				<h1><?php echo $accounting_entry->getHtmlDescription() ?></h1>
				<a class="btn btn-outline-secondary" href="accounting_entry_tag.php?id=<?php echo $accounting_entry->getId(); ?>">Catégoriser</a>
			</div>
			<p><a href="account.php?id=<?php echo $account->id ?>"><?php echo ToolBox::toHtml($account->getDescription()) ?></a></p>
		</header>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}

		echo "<div>";
	
		echo '<p>';
		
		$df = new IntlDateFormatter(Locale::getDefault(),IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
		
		switch ($accounting_entry->getType ()) {
			case 'spending' :
				echo 'Une dépense de <strong>' . $accounting_entry->getAmountToDisplay () . '</strong> enregistrée le '.$accounting_entry->getDateToDisplay($df).'.';
				break;
			case 'earning' :
				echo 'Un revenu de <strong>' . $accounting_entry->getAmountToDisplay () . '</strong> enregistré le '.$accounting_entry->getDateToDisplay($df).'.';
				break;
			default :
				echo $accounting_entry->getAmountToDisplay ();
		}
		echo '</p>';
		echo "</div>";
		
		$tags = $system->getAccountingEntryTags ( $accounting_entry ); 
		
		 if (count ( $tags ) > 0) {
			 echo '<div><p>' . $system->getHtmlTagList($tags) . '</p></div>';
		 }
	
		$similarAccountingEntries = $system->getSimilarAccountingEntries ( $accounting_entry );
		if (count ( $similarAccountingEntries ) > 0) {
			echo '<h2>Opérations similaires</h2>';
			
			echo '<div class="container-fluid">';
			echo '<div class="row">';
			
			$i=0; // nombre d'items traités
			$m=0; // nombre de mois traités
			
			foreach ( $similarAccountingEntries as $e ) {
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
					echo '<h3 class="mt-3">' . $e->getMonthToDisplay () . '</h3>';
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
					$l = array_diff($e->getTags(), $tags);
					echo '<div>' . $system->getHtmlTagList($l) . '</div>';
				} else {
					if (strcmp($e->type, 'spending')==0) {
						echo '<div><a href="accounting_entry_tag.php?id='.$e->getId().'" class="btn btn-outline-secondary btn-sm mt-1">Catégoriser</a></div>';
					}
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
		}
		?>
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>