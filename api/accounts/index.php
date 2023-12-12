<?php
include_once '../../classes/System.php';
$system = new System ( '../../config/host.json' );

header("Content-type: text/plain");

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET' :
		exit;
		
	case 'POST' :
		ToolBox::formatUserPost($_POST);
		$fb = new Feedback();

		switch($_POST['task']) {
			case 'deletion':
				if (isset($_POST['id'])) {
					if ($system->deleteAccount($_POST['id'])) {
						$fb->setMessage('C\'est oublié.');
						$fb->setType('success');
						$fb->addDatum('location', $system->getAppliUrl().'/admin/accounts.php');
					} else {
						$fb->setMessage('Mince, problème !');
						$fb->setType('error');
					}
				}
				break;
		}
		echo $fb->toJson();
		exit;
		
	case 'DELETE' :
		exit;
}
