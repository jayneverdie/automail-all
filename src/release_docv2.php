<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

echo "Release Doc V2 <br>";
// exit();
$sender = 'pramate_p@deestone.com';

$emaillist = [
	'to' => [
		'fn_export@deestone.com'
	],
	'cc' => [
		'jeamjit_p@deestone.com',
		'harit_j@deestone.com'
	],
	'bcc' => [
		
	]
];

$getdata = $automail->getData_Releasedocv2();

// echo "<pre>".print_r($getdata,true)."</pre>";
// exit;
	foreach ($getdata as $s) {
		$get_customer = $automail->getDataReleasedocv2Customer($s['CUSTACCOUNT']);
		foreach ($get_customer as $c) {
			// echo $c['ACCOUNTNUM']."=>". $c['AR'] ."=>".$c['TOTALBALANCE']."<br>";

			$totalbalance2 = $c['TOTALBALANCE']/2;
			if ($c['AR'] > $totalbalance2) {
				//sendmail
				$mail = $automail->sendMail(
					$emaillist['to'], 
					$emaillist['cc'], 
					[],
					$automail->getReleasedocv2Subject($c['ACCOUNTNUM'],$s['SERIES'],$s['VOUCHER_NO']),
					$automail->getReleasedocv2Body(),
					'',
					[],
					$sender
				);
				if ($mail['result'] === true) {	
					echo $mail['message'] . PHP_EOL."<br>";
					$automail->update_Releasedocv2_uncheck($s['SALESID'],$s['DATAAREAID'],$s['DSG_PACKINGSLIPID']);
				} else {
					echo $mail['message'] . PHP_EOL."<br>";
				}	
				// echo $totalbalance2."<br>";
				

			}else{
				//update
				// echo "Update=>" $s['SALESID'].$c['ACCOUNTNUM']."<br>";
				$query = $automail->update_Releasedocv2_Date($s['SALESID'],$s['DATAAREAID'],$s['DSG_PACKINGSLIPID']);
				echo $query;
			}

		}
	}
