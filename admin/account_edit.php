<?php
if (file_exists ( '../config/host.json' )) {
	include_once '../classes/System.php';
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

$doc_title = isset ( $account->id ) ? 'Edition d\'un compte' : 'Déclaration d\'un nouveau compte';
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
				<?php
				if (isset($account->id)) {
					echo '<li class="breadcrumb-item"><a href="account.php?id='.$account->getId().'">Compte</a></li>';
				}
				?>
				<li class="breadcrumb-item active"><?php echo ToolBox::toHtml($doc_title) ?></li>
			</ol>
		</nav>
		
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
			if (isset ( $account->id )) {
				echo '<input name="id" type="hidden" value="' . $account->id . '" />';
			}
			?>
			<div class="form-group">
				<label for="description_i">Description</label>
				<textarea id="description_i" name="description" class="form-control"><?php echo $account->description ?></textarea>
			</div>			
			<a class="btn btn-default" href="account.php?id=<?php echo $account->id ?>">Abandonner</a>
			<button name="cmd" type="submit" value="register" class="btn btn-primary">Enregistrer</button>
		</form>
		</main>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>