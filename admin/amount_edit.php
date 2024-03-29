<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
	$system = new System ( '../config/host.json' );
} else {
	header ( 'Location:config.php' );
	exit();
}

if (! empty ( $_REQUEST ['id'] )) {
	$amount = $system->getAmount ( $_REQUEST ['id'] );
} else {
	$amount = new Amount ();
}

$messages = array ();

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'register' :
			$amount->title = $_POST ['title'];
			$amount->description = $_POST ['description'];
			$amount->value = floatval($_POST ['value']);
			$amount->source = $_POST ['source'];
			$amount->source_url = $_POST ['source_url'];
			
			if ($system->put ( $amount )) {
				$messages [] = 'Montant enregistré (' . $amount->id . ')';
			}
			break;
		case 'cancel' :
			header ( 'Location:index.php' );
			exit ();
	}
}

$doc_title = isset ( $amount->id ) ? 'Un montant' : 'Nouveau montant';
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
			<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
			<?php
			if (count ( $messages ) > 0) {
				echo '<div class="alert alert-info" role="alert">';
				foreach ( $messages as $m ) {
					echo '<p>' . ToolBox::toHtml ( $m ) . '</p>';
				}
				echo '</div>';
			}
			?>
		  
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post"	enctype="multipart/form-data">
				<?php
				if (isset ( $amount->id )) {
					echo '<input name="id" type="hidden" value="' . $amount->id . '" />';
				}
				?>
				<div class="form-group">
					<label for="title_i">Titre</label>
					<input id="title_i" type="text" name="title" value="<?php echo ToolBox::toHtml($amount->title) ?>" size="25" class="form-control" />
				</div>
				<div class="form-group">
					<label for="description_i">Description</label>
					<textarea id="description_i" name="description" class="form-control"><?php echo $amount->description ?></textarea>
				</div>			
				<div class="form-group">
					<label for="value_i">Montant</label>
					<input id="value_i" type="text" name="value" value="<?php echo ToolBox::toHtml($amount->value) ?>"	size="25" class="form-control" />
				</div>
				<div class="form-group">
					<label for="source_i">Source</label>
					<input id="source_i" type="text" name="source" value="<?php echo ToolBox::toHtml($amount->source) ?>" size="25" class="form-control" />
				</div>
				<div class="form-group">
					<label for="source_url_i">Url de la source</label>
					<input id="source_url_i" type="text" name="source_url" value="<?php echo ToolBox::toHtml($amount->source_url) ?>" size="25" class="form-control" />
				</div>			
				<button name="cmd" type="submit" value="register" class="btn btn-primary">Enregistrer</button>
				<button name="cmd" type="submit" value="cancel"	class="btn btn-secondary">Abandonner</button>
			</form>
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>