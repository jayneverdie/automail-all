<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

// $files	=	$automail->getDataShipment();

// echo "ShipmentPlan";
// exit;
$root = "\\\\arryn\shipment plan\\";

$reportfailed = [
	'harit_j@deestone.com'
];

$test_email = [
	'to' => [
		'harit_j@deestone.com'
	],
	'cc' => [
		'kamol_p@deestone.com',
		'wattana_r@deestone.com'
	]
];

$files = $automail->getDirRoot($root);
