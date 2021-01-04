<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.php' )) {
		include_once $path . $class_name . '.php';
	}
}

if (file_exists ( './config/host.json' )) {
	$system = new System ( './config/host.json' );
} else {
	header ( 'Location:./admin/config.php' );
	exit();
}

session_start();

$fb = new UserFeedBack();

// demande d'anonymat
if (isset($_REQUEST['anonymat_submission'])) {
	unset($_SESSION['user_id']);
}
// authentification de l'utilisateur
if (empty($_SESSION['user_id'])) {
	if (isset($_POST['login_submission'])) {
		$user = new User();
		if ($user->identification($_POST['user_name'], $_POST['user_password'])) {
			// authentification suite à soumission du formulaire d'identification
			$_SESSION['user_id'] = $user->getId();
			header('Location:index.php');
			exit;
		} else {
		    $fb->addWarningMessage("Pas d'utilisateur reconnu !");
		}
	}
} else {
	header('Location:index.php');
	exit;
}
$doc_title = $system->getAppliName();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo ToolBox::toHtml($system->getAppliName()).' : identification utilisateur'; ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>/theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body id="loginDoc" >
<div class="container">	
	<h1 class="brand"><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<div>
		<p><strong><?php echo ToolBox::toHtml($system->getAppliName()); ?> </strong> est l'outil de contrôle budgétaire <a href="https://www.usocrate.fr" title="Lien vers maison-mère">Usocrate.fr</a>.</p>
		<?php echo $fb->toHtml() ?>
	</div>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<div class="form-group">
    		<label for="user_name_i">Identifiant</label>
    		<input id="user_name_i" name="user_name" type="text" class="form-control" />
		</div>
		<div class="form-group">
    		<label for="user_password_i">Mot de passe</label>
    		<input id="user_password_i" name="user_password" type="password" class="form-control" />
		</div>
		<button name="login_submission" type="submit" value="1" class="btn btn-primary">Pousser</button>
	</form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
	$(document).ready(function() {
		$('#user_name_i').focus();
	});
</script>
</body>
</html>
