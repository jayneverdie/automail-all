<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';
// require_once '../excel-reader/SpreadsheetReader_XLSX.php';
require_once '../PHPExcel-1.8/Classes/PHPExcel.php';
include '../PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

$automail = new AutoMail;

// echo "shipment status_";
// echo date('dmY');
// exit;
$root = "D:\webapp\Share\commercial\\";
$nameold = "commercial.xls";
$name = 'commercial invoice_'.date('dmY').'.xls';
// $f = new SpreadsheetReader_XLSX($root.$nameold);  

$inputFileName = $root.$nameold;  
$inputFileType = PHPExcel_IOFactory::identify($inputFileName);  
$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
$objReader->setReadDataOnly(true);  
$objPHPExcel = $objReader->load($inputFileName);  

$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
$highestRow = $objWorksheet->getHighestRow();
$highestColumn = $objWorksheet->getHighestColumn();

$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
$headingsArray = $headingsArray[1];

$r = -1;
$chk_data = false;
$namedDataArray = array();
for ($row = 1; $row <= $highestRow; ++$row) {
    $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
        ++$r;
        foreach($headingsArray as $columnKey => $columnHeading) {
            // $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
            // echo $row.'-'.$dataRow[$row][$columnKey]."<br>";
            if ($row>=2) {
            	$chk_data = true;
            }
        }
    }
}
// exit();
if ($chk_data==false) {
	echo "data notcorrect";
	exit();
}else{
	echo "data incorrect"."<br>";
}

$sender = 'acharapron_i@deestone.com';
// $sender = 'ea_webmaster@deestone.com';

$reportfailed = [
	'to' => [
		'harit_j@deestone.com'
	],
	'cc' => [		
		'ariyanut_j@deestone.com'
	]
];

// $customer_email = [
// 	'to' => [
// 		'harit_j@deestone.com'
// 	],
// 	'cc' => [
// 		'ariyanut_j@deestone.com',
// 		'surapon_o@deestone.com',
// 		'rattana_c@deestone.com'
// 	]
// ];

$customer_email = [
	'to' => [
		'syoungman@american-omni.com'
	],
	'cc' => [
		'rattana_c@deestone.com',
		'khanittha_p@deestone.com',
		'sakunee_b@deestone.com',
		'acharapron_i@deestone.com'
	]
];

$files = $automail->getDirRoot($root);

foreach ($files as $f) {
	rename($root.$f, $root.$name);
}

$files = $automail->getDirRoot($root);

// echo "<pre>".print_r($files,true)."</pre>";  exit;
// exit();
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
			$automail->getCommercialSubject(),
			$automail->getCommercialBody(), 
			$root,
			[],
			$sender
		);

		if ($mail['result'] === true) {

			sleep(1);
				$file_ = explode('.', $value);
				rename(
					$root .'\\' . $value, 
					$root . '\\temp\\'. $date_folder . '\\' . $file_[0] . '_' . date('Ymd-His') . '.' .$file_[1]
				);
			echo $mail['message'] . PHP_EOL;

		} else {

			$subjectFailed = 'Commercial invoice Failed';
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
	$subjectFailed = 'Commercial invoice Failed';
	$bodyFailed = 'No Excel File !';

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