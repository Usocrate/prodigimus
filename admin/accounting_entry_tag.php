<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit ();
}

//print_r($_POST);

$messages = array ();

if (! empty ( $_REQUEST ['id'] )) {
	$accounting_entry = $system->getAccountingEntry ( $_REQUEST ['id'] );
	$account = $system->getAccount ( $accounting_entry->getAccountId () );
	$tags = $system->getAccountingEntryTags ( $accounting_entry );
	$tagLessSimilarSpendings = $system->getSimilarAccountingEntries($accounting_entry, array('tagLessSpendingOnly'=>true));
	
	if (isset($_POST['tagsToKeep'])) {
		
		$tagsToRemove = array_diff($tags, $_POST['tagsToKeep']);
		$tagsToAdd = array_diff($_POST['tagsToKeep'], $tags);
		
		foreach ($tagsToRemove as $t) {
			$system->untagAccountingEntry($accounting_entry, $t);
		}

		foreach ($tagsToAdd as $t) {
			if (!empty($t)) {
				$system->tagAccountingEntry($accounting_entry, $t);
			}
		}
		
		if (isset($_POST['spreadTagsToTagLessSimilarSpendings']) && $_POST['spreadTagsToTagLessSimilarSpendings']==1) {
			foreach ($tagLessSimilarSpendings as $s) {
				foreach ($tagsToAdd as $t) {
					if (!empty($t)) {
						$system->tagAccountingEntry($s, $t);
					}
				}
			}
		}
		
		header ( 'Location:account.php?id='.$account->getId().'&tagLessSpendingOnly=1' );
		exit ();
	}
	
} else {
	header ( 'Location:accounts.php' );
	exit ();
}

$doc_title = 'Catégoriser une opération';
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo ToolBox::toHtml($doc_title) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
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
		
		<h1><?php echo ToolBox::toHtml($doc_title)?></h1>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}
		?>
			
		<?php
		echo '<div>';
		echo '<p><a href="accounting_entry.php?id=' . $accounting_entry->getId () . '">' . $accounting_entry->getHtmlDescription () . '</a></p>';
		echo '<p>';
		switch ($accounting_entry->getType ()) {
			case 'spending' :
				echo 'Une dépense de <strong>' . $accounting_entry->getAmountToDisplay () . '</strong>.';
				break;
			case 'earning' :
				echo 'Un revenu de <strong>' . $accounting_entry->getAmountToDisplay () . '</strong>.';
				break;
			default :
				echo $accounting_entry->getAmountToDisplay ();
		}
		echo '</p>';
		echo "</div>";
		?>

		<?php
		$i = 0;
		
		$knownTags = $system->getTags();
		$suggestedTags = $system->getSimilarAccountingEntriesTags ( $accounting_entry );
				
		echo '<div class="row">';
		
		echo '<section class="mt-2 col-lg-8">';
		echo '<h2>Catégories</h2>';
		
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" class="mt-3 mb-3">';
		echo '<input name="id" type="hidden" value="'.$accounting_entry->getId().'">';
		
		echo '<div class="d-flex flex-wrap">';
		foreach ($knownTags as $t) {
			echo '<div class="form-group mr-4">';
			echo '<div class="form-check">';
			if (in_array($t, $tags) || in_array($t, $suggestedTags)) {
				echo '<input id="i'.$i.'" name="tagsToKeep[]" class="form-check-input" type="checkbox" value="'.ToolBox::toHtml ($t).'" checked>';
			} else {
				echo '<input id="i'.$i.'" name="tagsToKeep[]" class="form-check-input" type="checkbox" value="'.ToolBox::toHtml ($t).'">';
			}
			echo '<label class="form-check-label" for="i'.$i.'"><a href="tag.php?label='.ToolBox::toHtml ( $t).'">'.ToolBox::toHtml ( $t).'</a>';
			if (in_array($t, $suggestedTags) && !in_array($t, $tags)) {
				echo ' <span class="badge badge-warning">suggestion</span>';
			}
			echo '</label>';
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>';
		echo '<div class="form-group"><label for="newtag_i">Nouvelle catégorie</label><input name="tagsToKeep[]" id="newtag_i" type="text" class="form-control"></input></div>';
		if (count($tagLessSimilarSpendings)>0) {
			echo '<div class="form-group"><div class="form-check"><input id="spreadTagsToTagLessSimilarSpendings_i" name="spreadTagsToTagLessSimilarSpendings" class="form-check-input" type="checkbox" value="1" checked></input><label for="spreadTagsToTagLessSimilarSpendings_i">Appliquer aux opérations similaires à catégoriser</label></div></div>';
		}
		echo '<button type="submit" class="btn btn-secondary">enregistrer</button>';
		echo '</form>';
		echo '</section>';
		
		if (count($tagLessSimilarSpendings)>0) {
			echo '<section class="mt-2 col-lg-4">';
			echo '<h2>Opérations similaires à catégoriser</h2>';
			echo '<ul class="list-group">';
			foreach ($tagLessSimilarSpendings as $s) {
				echo '<li class="list-group-item">';
				echo '<small>'.$s->getDateToDisplay().'</small></br>';
				echo '<h4><a href="' . $system->getAccountingEntryAdminUrl ( $s ) . '">' . $s->getHtmlDescription(). '</a></h4>';
				echo $s->getAmountToDisplay ();
				echo '</li>';
			}
			echo '</ul>';
			echo '</section>';
		}
		
		echo '</div>';
		?>
		</main>		
	</div>
</body>
</html>