<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

// $files	=	$automail->getDataShipment();

// echo "shipment status_";
// echo date('dmY');
// exit;
$root = "D:\webapp\Share\shipment plan\\";
// $root = 'D:\automail\shipment plan\\';
$name = 'shipment status_'.date('dmY').'.xls';
$sender = 'acharapron_i@deestone.com';

$reportfailed = [
	'to' => [
		'harit_j@deestone.com'
	],
	'cc' => [		
		'ariyanut_j@deestone.com',
		'rattana_c@deestone.com'
	]
];

// $customer_email = [
// 	'to' => [
// 		'harit_j@deestone.com'
// 	],
// 	'cc' => [
// 		'ariyanut_j@deestone.com'
// 	]
// ];

$customer_email = [
	'to' => [
		'Sawitri@american-omni.com',
		'syoungman@american-omni.com',
		'Jeab@american-omni.com',
		'arin@american-omni.com',
		'warat@american-omni.com'
	],
	'cc' => [
		'rattana_c@deestone.com',
		'nutsinee_p@deestone.com',
		'pichet@deestone.com',
		'rudeerat_h@deestone.com',
		'Khanittha_p@deestone.com',
		'rodjanun_d@deestone.com',
		'acharapron_i@deestone.com'
	]
];

$files = $automail->getDirRoot($root);

foreach ($files as $f) {
	rename($root.$f, $root.$name);
}

$files = $automail->getDirRoot($root);

// echo "<pre>".print_r($files,true)."</pre>";  exit;
// exit;
$date_folder = date('Y') . date('m');

if (!file_exists($root . '\\failed\\'. $date_folder)) {
    mkdir($root . '\\failed\\'. $date_folder, 0777, true);
}

if (!file_exists($root . '\\temp\\'. $date_folder)) {
    mkdir($root . '\\temp\\'. $date_folder, 0777, true);
}

if (count($files) !== 0) {

	foreach ($files as $key => $value) {
		$mail = $automail->sendMailDaily(
			$customer_email['to'], 
			$customer_email['cc'], 
			$value, 
			$automail->getShipmentSubject(),
			$automail->getShipmentBody(), 
			$root,
			[],
			$sender
		);

		if ($mail['result'] === true) {

			$writefile 	= fopen("../logs/shipment.txt", "a") or die("Unable to open file!");
			$txt 		= "Automail ShipmentPlan => ".$name." - ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
			fwrite($writefile, $txt);
			fclose($writefile);

			sleep(1);
				$file_ = explode('.', $value);
				rename(
					$root .'\\' . $value, 
					$root . '\\temp\\'. $date_folder . '\\' . $file_[0] . '_' . date('Ymd-His') . '.' .$file_[1]
				);
			echo $mail['message'] . PHP_EOL;

		} else {

			$subjectFailed = 'Shipment status AOT / DNK (Daily report) Failed !';
			$bodyFailed = $mail['message'] . PHP_EOL;

			$mail = $automail->sendMailDaily(
				$reportfailed['to'], 
				$reportfailed['cc'], 
				'', 
				$subjectFailed,
				$bodyFailed, 
				'',
				[],
				$sender
			);

			$writefile 	= fopen("../logs/shipment.txt", "a") or die("Unable to open file!");
			$txt 		= "Automail ShipmentPlan => ".$name." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
			fwrite($writefile, $txt);
			fclose($writefile);

			sleep(1);
				$file_ = explode('.', $value);
				rename(
					$root .'\\' . $value, 
					$root . '\\failed\\'. $date_folder . '\\' . $file_[0] . '_' . date('Ymd-His') . '.' .$file_[1]
				);
			echo $mail['message'] . PHP_EOL;

		}

	}

}else{
	$subjectFailed = 'Shipment status AOT / DNK (Daily report) Failed !';
	$bodyFailed = 'Excelfile Data not found!';

	$mail = $automail->sendMailDaily(
		$reportfailed['to'], 
		$reportfailed['cc'], 
		'', 
		$subjectFailed,
		$bodyFailed, 
		'',
		[],
		$sender
	);

	if ($mail['result'] === true) {
		echo $mail['message'] . PHP_EOL;
	}else{
		echo $mail['message'] . PHP_EOL;
	}
}