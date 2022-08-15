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
	$account = $system->getAccount ( $_REQUEST ['id'] );
	$criteria = array();
	if (isset($_POST['descriptionSubstr'])) {
		//$criteria['descriptionSubstr'] = ToolBox::formatUserPost($_POST['descriptionSubstr']);
		$criteria['descriptionSubstr'] = $_POST['descriptionSubstr'];
	}
	if (isset($_REQUEST['tagLessSpendingOnly']) && $_REQUEST['tagLessSpendingOnly']==1) {
		$criteria['tagLessSpendingOnly'] = true;
	}
	$entries = $system->getAccountingEntries($account, $criteria);
} else {
	header ( 'Location:accounts.php' );
	exit ();
}

$messages = array ();

$doc_title = $account->getDescription();
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
				<li class="breadcrumb-item active">Compte</li>
			</ol>
		</nav>
		
		<main class="px-lg-5">
	
		<div class="d-lg-flex flex-lg-row justify-content-between align-items-center mb-3 mt-3">
			<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
			<div>
				<a class="btn btn-outline-secondary" href="accounting_entry_import.php?account_id=<?php echo $account->getId(); ?>">Importer des opérations</a>
				<a class="btn btn-outline-secondary" href="account_edit.php?id=<?php echo $account->getId(); ?>" >Modifier</a>
			</div>
		</div>
		
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="alert alert-info" role="alert">';
			foreach ( $messages as $m ) {
				echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
			}
			echo '</div>';
		}
		?>
		
		<div class="d-lg-flex flex-mg-row align-items-center mb-3 mt-3">
			<h2 class="mt-2">Dernières opérations</h2>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" class="d-inline-flex ml-4 mb-3 mt-3">
				<input name="id" type="hidden" value="<?php echo $account->getId() ?>">
				
				<?php
				if (isset($criteria['descriptionSubstr'])) {
					echo '<input name="descriptionSubstr" type="text" class="form-control" value="'.$criteria['descriptionSubstr'].'">';
				} else {
					echo '<input name="descriptionSubstr" type="text" class="form-control">';
				}
				?>

				<div class="form-check form-check-inline ml-4 mr-4">
				<?php
				if (isset($criteria['tagLessSpendingOnly']) && $criteria['tagLessSpendingOnly']===true) {
					echo '<input name="tagLessSpendingOnly" id="tagLessSpendingOnly_i" class="form-check-input" type="checkbox" value="1" checked>';
				} else {
					echo '<input name="tagLessSpendingOnly" id="tagLessSpendingOnly_i" class="form-check-input" type="checkbox" value="1">';
				}
				?>
				<label class="form-check-label" for="tagLessSpendingOnly_i">à&nbsp;catégoriser</label>
				</div>				

				<button class="btn btn-secondary ml-3">Chercher</button>
			</form>
		</div>
		
		<?php
		if (count($entries)>0) {
			echo AccountingEntry::collectionToHtml($entries);
		} else {
			echo '<p>Pas d\'opération enregistrée.</p>';
		}
		?>
		</main>
	</div>
</body>
</html>