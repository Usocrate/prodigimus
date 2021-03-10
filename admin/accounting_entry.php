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

if (! empty ( $_REQUEST ['id'] )) {
	$accounting_entry = $system->getAccountingEntry ( $_REQUEST ['id'] );
	$account = $system->getAccount($accounting_entry->getAccountId());
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
		  		<li class="breadcrumb-item"><a href="accounts.php">Les comptes</a></li>
				<li class="breadcrumb-item"><a href="account.php?id=<?php echo $account->getId() ?>"><?php echo ToolBox::toHtml($account->description) ?></a></li>
				<li  class="breadcrumb-item active"><?php echo ToolBox::toHtml($doc_title) ?></li>
		  </ol>
		</nav>
		
		<h1><?php echo $accounting_entry->getHtmlDescription() ?> <small><?php echo $accounting_entry->getDateToDisplay() ?></small></h1>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}
		
		$tags = $system->getAccountingEntryTags($accounting_entry);
		if (count($tags>0)) {
			echo '<div>';
			foreach ($tags as $t) {
				echo '<span>'.ToolBox::toHtml($t).'</span> ';
			}
			echo '</div>';
		}
		
		echo "<div>";
		echo '<p>';
		switch ($accounting_entry->getType()) {
			case 'spending' :
				echo 'Une dépense de <strong>'.$accounting_entry->getAmountToDisplay().'</strong>.';
				break;
			case 'earning' :
				echo 'Un revenu de <strong>'.$accounting_entry->getAmountToDisplay().'</strong>.';
				break;
			default :
				echo $accounting_entry->getAmountToDisplay();
		}
		echo '</p>';
		echo "</div>";
		
		$similarAccountingEntries = $system->getSimilarAccountingEntries($accounting_entry);
		if (count($similarAccountingEntries)>0) {
			echo '<h2>Opérations similaires</h2>';
			echo AccountingEntry::collectionToHtml($similarAccountingEntries);
		}
		
		?>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"	enctype="multipart/form-data" style="display:none">
			<div>
				<label for="newtag_i"">nouvelle étiquette</label><input id="newtag_i" type="text"></input>
			</div>
			<input name="cmd" type="hidden" value="tag"></input>
			<input name="id" type="hidden" value="<?php echo $accounting_entry->getId() ?>"></input>
			<button>Coller</button>
		</form>
	 </div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>