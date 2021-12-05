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
	
	if (isset($_POST['tagsToAdd'])) {
		foreach ($_POST['tagsToAdd'] as $t) {
			$system->tagAccountingEntry($accounting_entry, $t);
		}
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

		$tags = $system->getAccountingEntryTags ( $accounting_entry );
		?>
		<h2 class="mt-2">Catégories</h2>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"	enctype="multipart/form-data">
			<input id="tag_i" type="text" value="<?php echo implode(',',$tags) ?>"></input>
		</form>
				
		<?php 
		/*
		if (count ( $tags ) > 0) {
			echo '<p>';
			foreach ( $tags as $t ) {
				echo '<a href="' . $system->getAppliUrl () . '/admin/tag.php?label=' . urlencode ( $t ) . '"><span class="badge badge bg-light text-dark">' . ToolBox::toHtml ( $t ) . '</span></a> ';
			}
			echo '</p>';
		}
		*/

		$suggestedTags = $system->getSimilarAccountingEntriesTags ( $accounting_entry );
		$tagsToDisplay = array_diff ( $suggestedTags, $tags );

		if (count ( $tagsToDisplay ) > 0) {
			echo '<h2 class="mt-2">Suggestions</h2>';
			echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
			echo '<input name="id" type="hidden" value="'.$accounting_entry->getId().'">';
			$i = 0;
			foreach ( $tagsToDisplay as $t ) {
				echo '<div class="form-group">';
				echo '<div class="form-check">';
				echo '<input id="i'.$i.'" name="tagsToAdd[]" class="form-check-input" type="checkbox" value="'.ToolBox::toHtml ( $t ).'" checked>';
				echo '<label class="form-check-label" for="i'.$i.'">'.ToolBox::toHtml ( $t ).'</label>';
				echo '</div>';
				echo '</div>';
				$i++;
			}
			echo '<button type="submit" class="btn btn-secondary">ajouter</button>';
			echo '</form>';
		}

		?>
	</div>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://unpkg.com/@yaireo/tagify"></script>
	<script	src="https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
	<script>
	window.onload = function() {
		var input = document.getElementById('tag_i');
		var t = new Tagify(input);
		t.on('change', function(e){
	
			const xhr = new XMLHttpRequest();
			var url = '<?php echo $system->getAppliUrl() ?>/api/tags.php?accounting_entry_id=<?php echo $accounting_entry->getId() ?>';
			xhr.open('DELETE',url);
			xhr.send();

			var url = '<?php echo $system->getAppliUrl() ?>/api/tags.php?';
			if (e.detail.value!==null && e.detail.value.length>0) {
				var tags = JSON.parse(e.detail.value);
				for (i=0; i<tags.length; i++) {
					const xhr2 = new XMLHttpRequest();
					xhr2.open('POST',url);
					xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xhr2.send('accounting_entry_id=<?php echo $accounting_entry->getId() ?>&label='+tags[i].value);
				}
			}
		});
	};
	</script>
</body>
</html>