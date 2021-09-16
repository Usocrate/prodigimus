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

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET' :
		break;
	case 'POST' :
		$ae = new AccountingEntry();
		$ae->setId($_POST['accounting_entry_id']);
		$system->tagAccountingEntry($ae, $_POST['label']);
		//header('Content-type: text/plain; charset=UTF-8');
		break;
	case 'DELETE' :
		break;
}
