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

$messages = array ();

if (! empty ( $_REQUEST ['id'] )) {
	$accounting_entry = $system->getAccountingEntry ( $_REQUEST ['id'] );
	$account = $system->getAccount ( $accounting_entry->getAccountId () );
	$tags = $system->getAccountingEntryTags ( $accounting_entry );
	
	if (isset($_POST['tagsToKeep'])) {
		
		$tagsToRemove = array_diff($tags, $_POST['tagsToKeep']);

		$tagsToAdd = array_diff($_POST['tagsToKeep'], $tags);
		
		foreach ($tagsToRemove as $t) {
			$system->untagAccountingEntry($accounting_entry, $t);
		}

		foreach ($tagsToAdd as $t) {
			$system->tagAccountingEntry($accounting_entry, $t);
		}
		header ( 'Location:accounting_entry.php?id='.$accounting_entry->getId() );
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

		<h1><?php echo ToolBox::toHtml($doc_title)?></h1>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}

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
		<h2 class="mt-2">Catégories</h2>
				
		<?php
		$i = 0;
		
		$knownTags = $system->getTags();
		$suggestedTags = $system->getSimilarAccountingEntriesTags ( $accounting_entry );
				
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
		echo '<input name="id" type="hidden" value="'.$accounting_entry->getId().'">';
		foreach ($knownTags as $t) {
			echo '<div class="form-group">';
			echo '<div class="form-check">';
			if (in_array($t, $tags)) {
				echo '<input id="i'.$i.'" name="tagsToKeep[]" class="form-check-input" type="checkbox" value="'.ToolBox::toHtml ($t).'" checked>';
			} else {
				echo '<input id="i'.$i.'" name="tagsToKeep[]" class="form-check-input" type="checkbox" value="'.ToolBox::toHtml ($t).'">';
			}
			echo '<label class="form-check-label" for="i'.$i.'"><a href="tag.php?label='.ToolBox::toHtml ( $t).'">'.ToolBox::toHtml ( $t).'</a>';
			if (in_array($t, $suggestedTags)) {
				echo ' <span class="badge badge-info">suggestion</span>';
			}
			echo '</label>';
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '<button type="submit" class="btn btn-secondary">enregistrer</button>';
		echo '</form>';
		?>
	</div>
</body>
</html>