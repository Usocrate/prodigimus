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

header ( 'charset=utf-8' );
session_start ();

$doc_title = 'Importer de nouvelles opérations';

$ary [] = 'UTF-8';
$ary [] = 'ISO-8859-1';
$ary [] = 'ASCII';
mb_detect_order ( $ary );

if (! isset ( $_SESSION ['csvFileImportToComplete'] )) {
	$_SESSION ['csvFileImportToComplete'] = array ();

	// le fichier csv
	$_SESSION ['csvFileImportToComplete'] ['file'] = null;

	// le compte concerné par l'importation
	if (isset ( $_REQUEST ['account_id'] )) {
		$_SESSION ['csvFileImportToComplete'] ['account_id'] = $_REQUEST ['account_id'];
	} else {
		header ( 'location:accounts.php' );
		exit ();
	}

	// l'étape du processus d'importation dans lequel on se trouve
	$_SESSION ['csvFileImportToComplete'] ['taskToFullfill'] = 'upload';
} else {
	// si le script est lancé sur un autre compte, l'importation en cours est abandonnée
	if (isset ( $_REQUEST ['account_id'] ) && strcmp ( $_SESSION ['csvFileImportToComplete'] ['account_id'], $_REQUEST ['account_id'] ) != 0) {
		unlink ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'] );
		$_SESSION ['csvFileImportToComplete'] ['file'] = null;
		$_SESSION ['csvFileImportToComplete'] ['account_id'] = $_REQUEST ['account_id'];
	}
	$_SESSION ['csvFileImportToComplete'] ['taskToFullfill'] = 'upload';
}

$account = $system->getAccount ( $_SESSION ['csvFileImportToComplete'] ['account_id'] );
$lastEntryDate = $system->getLastAccountingEntryDate ( $account );

if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'Abandonner' :
			unlink ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'] );
			unset ( $_SESSION ['csvFileImportToComplete'] );
			header ( 'location:' . $system->getAppliUrl () );
			exit ();
	}
}

if (isset ( $_POST ['task_id'] )) {
	switch ($_POST ['task_id']) {
		case 'upload' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Envoyer' :
						if (isset ( $_FILES ['accounting_entries_csv_file'] )) {
							$uploadedFile = $_FILES ['accounting_entries_csv_file'];
							$a = explode ( '.', $uploadedFile ['name'] );
							$ext = end ( $a );
							$filepath = $system->getUploadDirectoryPath () . '/' . $account->getId () . '-' . date ( 'YY-MM-DD' ) . '.' . $ext;

							if (move_uploaded_file ( $uploadedFile ['tmp_name'], $filepath )) {
								$_SESSION ['csvFileImportToComplete'] ['file'] ['path'] = $filepath;

								if (($handle = fopen ( $filepath, 'r' )) !== FALSE) {

									// tentative de détection du type de fichier .csv fourni
									// format anglais : séparateur de colonnes : virgule ; séparateur de décimales : point
									// format français : séparateur de colonnes : point-virgule ; séparateur de décimales : virgule
									$header_en_parsing = fgetcsv ( $handle, null, "," );
									$firstItem_en_parsing = fgetcsv ( $handle, null, "," );
									rewind ( $handle );
									$header_fr_parsing = fgetcsv ( $handle, null, ";" );
									$firstItem_fr_parsing = fgetcsv ( $handle, null, ";" );
									echo '<input type="hidden" name="task_id" value="mapping" />';
									$_SESSION ['csvFileImportToComplete'] ['file'] ['delimiter'] = count ( $header_fr_parsing ) == count ( $firstItem_fr_parsing ) && count ( $header_fr_parsing ) > count ( $header_en_parsing ) ? ';' : ',';
								}
							}
						}
						$_SESSION ['csvFileImportToComplete'] ['taskToFullfill'] = 'mapping';
						break;
				}
			} // fin if
			break;

		case 'mapping' :
			// le fichier csv a déjà été publié
			$filepath = $_SESSION ['csvFileImportToComplete'] ['file'] ['path'];
			$file_encoding = mb_detect_encoding ( file_get_contents ( $filepath ) );
			$delimiter = $_SESSION ['csvFileImportToComplete'] ['file'] ['delimiter'];

			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Enregistrer' :
						if (isset ( $_POST ['mapping'] )) {
							$_SESSION ['csvFileImportToComplete'] ['file'] ['mapping'] = $_POST ['mapping'];
						}
						break;
				}
			}
			$_SESSION ['csvFileImportToComplete'] ['taskToFullfill'] = 'checking';
			break;

		case 'saving' :
			// le fichier csv a déjà été publié
			$filepath = $_SESSION ['csvFileImportToComplete'] ['file'] ['path'];
			$file_encoding = mb_detect_encoding ( file_get_contents ( $filepath ) );
			$delimiter = $_SESSION ['csvFileImportToComplete'] ['file'] ['delimiter'];
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Confirmer' :
						// les correspondances en index de la colonne dans le fichier source et caractéristiques du mouvement à enregistrer
						if ($_SESSION ['csvFileImportToComplete'] ['file'] ['mapping']) {
							if (isset ( $filepath ) && ($handle = fopen ( $filepath, 'r' )) !== FALSE) {
								$header = fgetcsv ( $handle, null, $delimiter );

								if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
									$header = array_map ( 'utf8_encode', $header );
								}

								while ( $data = fgetcsv ( $handle, null, $delimiter ) ) {
									$e = new AccountingEntry ();
									$e->setAccountId ( $account->id );

									if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
										$data = array_map ( 'utf8_encode', $data );
									}

									for($i = 0; $i < count ( $data ); $i ++) {
										if (! isset ( $data [$i] )) {
											continue;
										}
										switch ($_SESSION ['csvFileImportToComplete'] ['file'] ['mapping'] [$i]) {
											case 'Date' :
												$e->setDateFromCsv ( $data [$i] );
												break;
											case 'Date de valeur' :
												$e->setValueDateFromCsv ( $data [$i] );
												break;
											case 'Description' :
												$e->setDescription ( $data [$i] );
												break;
											case 'Montant' :
												if (! empty ( $data [$i] )) {
													$e->setAmountAndTypeFromCsv ( $data [$i] );
												}
												break;
										}
									}
									// var_dump($e);
									if ((isset ( $lastEntryDate ) && $e->getDate () > $lastEntryDate) || is_null( $lastEntryDate )) {
										$system->put ( $e );
									}
								}
							}
							unlink ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'] );
							unset ( $_SESSION ['csvFileImportToComplete'] );

							$_SESSION ['csvFileImportToComplete'] ['taskToFullfill'] = 'confirmation';
						}
						break;
				} // fin switch
			} // fin if
			break;
	}
}
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="description"
	content="<?php echo ToolBox::toHtml($system->getAppliDescription()) ?>" />
<meta charset="UTF-8">
<meta name="viewport"
	content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo ToolBox::toHtml($system->getAppliName().' : '.$doc_title); ?></title>
<link type="text/css" rel="stylesheet"
	href="<?php echo $system->getSkinUrl(); ?>/theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
	<?php include 'navbar.inc.php'; ?>
	<div class="container-fluid">
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="accounts.php">Les comptes</a></li>
				<li class="breadcrumb-item"><a
					href="account.php?id=<?php echo $account->id ?>"><?php echo ToolBox::toHtml($account->description) ?></a></li>
				<li class="breadcrumb-item active"><?php echo ToolBox::toHtml($doc_title) ?></li>
			</ol>
		</nav>
		<header>
			<h1><?php echo ToolBox::toHtml($doc_title) ?></h1>
		</header>
		<div class="row">
			<div class="col-md-12">
		<?php

		switch ($_SESSION ['csvFileImportToComplete'] ['taskToFullfill']) {
			case 'upload' :
				// on doit fournir un fichier csva
				echo '<form enctype="multipart/form-data" action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $system->getUploadMaxFileSize () . '" />';
				echo '<input type="hidden" name="account_id" value="' . $account->id . '" />';
				echo '<input type="hidden" name="task_id" value="upload" />';
				echo '<div class="form-group">';
				echo '<label>Envoyez ce fichier</label>';
				echo '<input name="accounting_entries_csv_file" type="file" />';
				echo '</div>';
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Abandonner" class="btn btn-default" />';
				echo '<input type="submit" name="cmd" value="Envoyer" class="btn btn-default btn-primary" />';
				echo '</div>';
				echo '</form>';
				break;

			case 'mapping' :
				if (is_file ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'] )) {
					// le fichier csv a déjà été publié
					$filepath = $_SESSION ['csvFileImportToComplete'] ['file'] ['path'];
					$file_encoding = mb_detect_encoding ( file_get_contents ( $filepath ) );
					$delimiter = $_SESSION ['csvFileImportToComplete'] ['file'] ['delimiter'];
				} else {
					// on recommence le processus depuis le début
					unset ( $_SESSION ['csvFileImportToComplete'] );
					header ( 'location:account.php?id=' . $account->id );
					exit ();
				}

				// aperçu du fichier et association des colonnes du fichier aux attributs des mouvements à enregistrer
				if (($handle = fopen ( $filepath, 'r' )) !== FALSE) {

					$header = fgetcsv ( $handle, null, $delimiter );
					if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
						$header = array_map ( 'utf8_encode', $header );
					}

					$fields = array ();
					foreach ( $header as $h ) {
						$fields [] = array (
								'header' => $h,
								'values' => array ()
						);
					}

					while ( $data = fgetcsv ( $handle, null, $delimiter ) ) {
						if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
							$data = array_map ( 'utf8_encode', $data );
						}

						for($i = 0; $i < count ( $data ); $i ++) {
							if (! empty ( $data [$i] ) && ! in_array ( $data [$i], $fields [$i] ['values'] )) {
								array_push ( $fields [$i] ['values'], $data [$i] );
							}
						}
					}

					fclose ( $handle );

					echo '<p>' . Toolbox::toHtml ( $filepath ) . '</p>';
					echo '<p>Le fichier source (' . $file_encoding . ') semble utiliser le caractère "' . $delimiter . '" pour séparer les données.</p>';
					// echo '<p>'.implode(", ", mb_detect_order()).'</p>';

					echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
					echo '<input type="hidden" name="task_id" value="mapping" />';

					for($i = 0; $i < count ( $header ); $i ++) {
						if (empty ( $header [$i] ) || count ( $fields [$i] ['values'] ) == 0) {
							continue;
						}
						echo '<div class="form-group">';
						$id = 'target_i' . $i;
						echo '<label for="' . $id . '">' . Toolbox::toHtml ( $header [$i] ) . '</label>';
						shuffle ( $fields [$i] ['values'] );
						echo '<select id="' . $id . '" name="mapping[' . $i . ']" class="form-control">';
						$options = array (
								'Aucune correspondance',
								'Date',
								'Date de valeur',
								'Description',
								'Montant'
						);
						foreach ( $options as $o ) {
							echo '<option value="' . Toolbox::toHtml ( $o ) . '">' . Toolbox::toHtml ( $o ) . '</option>';
						}
						echo '</select>';
						echo '<small class="form-text text-muted">ex. : ' . Toolbox::toHtml ( $fields [$i] ['values'] [0] ) . '</small>';
						echo '</div>';
					}
					echo '</div>';
					echo '<div class="btn-group">';
					echo '<input type="submit" name="cmd" value="Abandonner" class="btn btn-default" />';
					echo '<input type="submit" name="cmd" value="Enregistrer" class="btn btn-default btn-primary" />';
					echo '</div>';
					echo '</form>';
				}
				break;

			case 'checking' :
				If (isset ( $lastEntryDate )) {
					echo '<p>On veut enregistrer toutes les opérations effectuées au delà de la date du ' . $lastEntryDate->format ( 'd M Y' ) . '</p>';
				}

				// aperçu des données qui vont être importées.

				$file_encoding = mb_detect_encoding ( file_get_contents ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'] ) );
				$delimiter = $_SESSION ['csvFileImportToComplete'] ['file'] ['delimiter'];

				if ($_SESSION ['csvFileImportToComplete'] ['file'] ['mapping']) {
					if ($handle = fopen ( $_SESSION ['csvFileImportToComplete'] ['file'] ['path'], 'r' )) {
						$header = fgetcsv ( $handle, null, $delimiter );

						if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
							$header = array_map ( 'utf8_encode', $header );
						}

						$entries = array ();
						$entries ['toImport'] = array ();
						$entries ['others'] = array ();

						while ( $data = fgetcsv ( $handle, null, $delimiter ) ) {
							$e = new AccountingEntry ();
							$e->setAccountId ( $account->id );

							if (strcmp ( $file_encoding, 'ISO-8859-1' ) == 0) {
								$data = array_map ( 'utf8_encode', $data );
							}

							for($i = 0; $i < count ( $data ); $i ++) {
								if (! isset ( $data [$i] )) {
									continue;
								}
								switch ($_SESSION ['csvFileImportToComplete'] ['file'] ['mapping'] [$i]) {
									case 'Date' :
										$e->setDateFromCsv ( $data [$i] );
										break;
									case 'Date de valeur' :
										$e->setValueDateFromCsv ( $data [$i] );
										break;
									case 'Description' :
										$e->setDescription ( $data [$i] );
										break;
									case 'Montant' :
										if (! empty ( $data [$i] )) {
											$e->setAmountAndTypeFromCsv ( $data [$i] );
										}
										break;
								}
							}
							if ((isset ( $lastEntryDate ) && $e->getDate () > $lastEntryDate) || is_null( $lastEntryDate )) {
								$entries ['toImport'] [] = clone $e;
							} else {
								$entries ['others'] [] = clone $e;
							}
						}
					}
					// var_dump($entries);
				}
				if (count ( $entries ['toImport'] ) > 0) {
					echo '<h2>Les opérations à importer</h2>';
<<<<<<< HEAD
					echo '<table class="table table-sm">';
					echo '<thead><tr><th>Désignation</th><th>Montant</th></tr></thead>';
					echo '<tbody>';
					foreach($entries ['toImport'] as $e) {
						echo '<tr>';
						echo '<td>';
						echo '<small>'.$e->getDateToDisplay().'</small><br />';
						echo  ToolBox::toHtml ($e->description);
						echo  '</td>';
						echo '<td>';
						switch ($e->type){
							case 'earning' :
								echo '<small>Revenu</small><br/>';
								echo $e->amount.' €';
								break;
							case 'spending':
								echo '<small>Dépense</small><br/>';
								echo $e->amount.' €';
								break;
							default :
								echo $e->amount.' €';
						}
						echo '</td>';
						echo '</tr>';
					}
					echo '</tbody>';
					echo '</table>';
					
					
					if (count ( $entries ['others'] ) > 0) {
						echo '<h2>Les opérations qui seront exclues</h2>';
						echo '<table class="table table-sm">';
						echo '<thead><tr><th>Désignation</th><th>Montant</th></tr></thead>';
						echo '<tbody>';
						foreach($entries ['others'] as $e) {
							echo '<tr>';
							echo '<td>';
							echo '<small>'.$e->getDateToDisplay().'</small><br />';
							echo  ToolBox::toHtml ($e->description);
							echo  '</td>';
							echo '<td>';
							switch ($e->type){
								case 'earning' :
									echo '<small>Revenu</small><br/>';
									echo $e->amount.' €';
									break;
								case 'spending':
									echo '<small>Dépense</small><br/>';
									echo $e->amount.' €';
									break;
								default :
									echo $e->amount.' €';
							}
							echo '</td>';
							echo '</tr>';
						}
						echo '</tbody>';
						echo '</table>';
=======
					echo AccountingEntry::collectionToHtml($entries['toImport'], 'Les opérations à importer');
					if (count($entries['others'])>0) {
						echo '<h2>Les opérations qui seront exclues</h2>';
						echo AccountingEntry::collectionToHtml($entries['others'], 'Les opérations qui ne seront pas enregistrées');
>>>>>>> branch 'main' of git@github.com:Usocrate/prodigimus.git
					}
					echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
					echo '<input type="hidden" name="task_id" value="saving" />';
					echo '<div class="btn-group">';
					echo '<input type="submit" name="cmd" value="Abandonner" class="btn btn-default" />';
					echo '<input type="submit" name="cmd" value="Confirmer" class="btn btn-default btn-primary" />';
					echo '</div>';
					echo '</form>';
				} else {
					echo '<p>Aucune opération à importer.</p>';
					echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
					echo '<input type="hidden" name="task_id" value="saving" />';
					echo '<div class="btn-group">';
					echo '<input type="submit" name="cmd" value="Abandonner" class="btn btn-primary" />';
					echo '</div>';
					echo '</form>';
				}
				break;

			case 'confirmation' :
				echo '<p>Tout s\'est bien déroulé. Retrouver la liste des opérations importées sur <a href="account.php?id=' . $account->getId () . '">l\'écran dédié au compte.</a></p>';
				break;
		}
		?>
		</div>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
		integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
		crossorigin="anonymous"></script>
	<script type="text/javascript"
		src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>