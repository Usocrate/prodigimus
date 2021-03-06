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
} else {
	$account = new Account ();
}

$messages = array ();

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'register' :
			$account->description = $_POST ['description'];
			if ($system->put ( $account )) {
				$messages [] = 'compte enregistré (' . $account->id . ')';
			}
			break;
		case 'cancel' :
			header ( 'Location:index.php' );
			exit ();
	}
}

$doc_title = isset ( $account->id ) ? 'Un compte' : 'Nouveau compte';
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
			if (isset ( $account->id )) {
				echo '<input name="id" type="hidden" value="' . $account->id . '" />';
			}
			?>
			<div class="form-group">
				<label for="description_i">Description</label>
				<textarea id="description_i" name="description" class="form-control"><?php echo $account->description ?></textarea>
			</div>			
			<button name="cmd" type="submit" value="register" class="btn btn-primary">Enregistrer</button>
			<button name="cmd" type="submit" value="cancel"	class="btn btn-secondary">Abandonner</button>
		</form>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>