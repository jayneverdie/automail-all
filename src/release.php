<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

$emailFailed = [
	'to' => [
		'pramate_p@deestone.com'
	],
	'cc' => [
		'FN_EXPORT@DEESTONE.COM'
	],
	'bcc' => [
		'jeamjit_p@deestone.com'
	]
];


// echo "<pre>".print_r($test_mail,true)."</pre>";

//$files = '';
//foreach ($test_mail['cc'] as $name) {
	// $files .= explode('.', $name)[0] . ', ';
	//$files .= $name . ', ';

//}
//echo $files = trim($files, ', ');
//echo $test_mail['to'][0];
//exit();
$sender = 'pramate_p@deestone.com';
// $sender = 'pramate_p@deestone.com';
// $email_To = $automail->getmail_release('dsl');
// echo "<pre>".print_r($email_To,true)."</pre>";
// exit;

	date_default_timezone_set("Asia/Bangkok");
	$checkDate = date("h:i");
	$conn = $automail->connect();
	
	if ($checkDate=='08:00') {	
		if (!$conn) {
			//echo "connected failed";

			$mail = $automail->sendMail(
				$emailFailed['to'], 
				$emailFailed['cc'], 
				[],
				$automail->getReleaseSubjectConnectFailed(),
				$automail->getReleaseBodyConnectFailed(),
				'',
				[],
				$sender
			);
			if ($mail['result'] === true) {	
					echo $mail['message'] . PHP_EOL;
			} else {
					echo $mail['message'] . PHP_EOL;
			}	
			exit();
		}
	}

	$getdata = $automail->getData_Release();
	
	if (count($getdata)==0) {
		echo "nodata";
		exit();
	}

	foreach ($getdata as $value) {
		$salesid 		= $value['DSG_SALESID'];
		$custaccount 	= $value['OrderAccount'];
		$company	 	= $value['DSG_DATAAREAID'];
		$series	 		= $value['Series'];
		$voucher_no	 	= $value['Voucher_no'];
		$custname	 	= $value['NAME'];
		$releasedate	= $value['DSG_AfterValue'];
		$fullname 		= $value['FULLNAME'];
		$createdate		= $value['createdDate'];
		$createdtime 	= $value['createdTime'];
		$packingslip 	= $value['DSG_PACKINGSLIPID'];
		$elby			= $value['DSG_EL_by'];
		$email_To = $automail->getmail_release($company);
		
		$check_hasmail = array_filter($email_To);

		if (!empty($check_hasmail)) {

		 	$mail = $automail->sendMail(
				$email_To['to'], 
				$email_To['cc'], 
				[],
				$automail->getReleaseSubject($custaccount,$company,$series,$voucher_no),
				$automail->getReleaseBody($custname,$releasedate,$fullname,$createdate,$createdtime,$elby),
				'',
				$email_To['bcc'],
				$sender
			);

			if ($mail['result'] === true) {
				$automail->update_Release($salesid,$company,$packingslip);
				
				$writefile 	= fopen("../logs/release_doc.txt", "a") or die("Unable to open file!");
				$txt 		= "Automail Release doc => ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. " => ".$custname."/".$releasedate."/".$fullname."/".$createdate."/".$createdtime."/".$elby."\n";
				fwrite($writefile, $txt);
				fclose($writefile);

				echo $mail['message'] . PHP_EOL."<br>";

			} else {

				$writefile 	= fopen("../logs/release_doc.txt", "a") or die("Unable to open file!");
				$txt 		= "Automail Release doc => ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. " => ".$custname."/".$releasedate."/".$fullname."/".$createdate."/".$createdtime."/".$elby."\n";
				fwrite($writefile, $txt);
				fclose($writefile);
				
				echo $mail['message'] . PHP_EOL."<br>";

				$note = $mail['message'] . PHP_EOL;
				$mail = $automail->sendMail(
					$emailFailed['to'], 
					$emailFailed['cc'], 
					[],
					$automail->getReleaseSubjectFailed($custaccount,$company,$series,$voucher_no),
					$automail->getReleaseBodyFailed($custaccount,$company,$series,$voucher_no,$note,$email_To),
					'',
					$email_To['bcc'],
					$sender
				);
			}
		
		}
		echo "<br>";
		$email_To ='';
	}

