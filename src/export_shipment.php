<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

$customerTest = [
	'to' => [
		// 'rattana_c@deestone.com'
		'harit_j@deestone.com'
	],
	'cc' => [		
		'jeamjit_p@deestone.com',
		'wattana_r@deestone.com'
	],
	'bcc' => [		
		
	],
	'sender' => [
		'ea_webmaster@deestone.com'
	]
];

echo "Automail (Export Shipment)<br><br>";
// exit();
$getdata = $automail->getData_ExportShipment();
// echo "<pre>".print_r($getdata,true)."</pre>";
if (count($getdata)==0) {
	echo "Data Not found!";
	exit;
}


foreach ($getdata as $value) {

	$customer 			= $value['CUSTACCOUNT'];
	$customer_name 		= $value['NAME'];
	$std20 				= $value['STD20'];
	$std40 				= $value['STD40'];
	$hc40 				= $value['HC40'];
	$lcl				= $value['LCL'];
	$hc45				= $value['HC45'];
	$quotationid		= $value['QUOTATIONID'];
	// $po 				= $value['DSG_RefCreatePurchId'];
	$po 				= $value['CustomerRef'];
	$salesid 			= $value['SALESID'];
	$port 				= $value['DSG_TOPORTDESC'];
	$avaliable      	= $value['DSG_AVAILABLEDATE'];
	$paymdaterequire 	= $value['DSG_PaymDateRequire'];
	$lastshipment 		= $value['Lastshipment'];
	$expirydate 		= $value['Expirydate'];
	$currency 			= $value['CURRENCYCODEISO'];
	$amount 			= number_format($value['DSG_AMOUNTREQUIRE'],2);
	$agent 				= $value['DSG_AGENT'];
	$requestshipdate    = $value['DSG_REQUESTSHIPDATE'];
	$noted 				= $value['DSG_NoteAutoMail'];
	$paymenttype 		= $value['PaymentType'];
	$email_To = $automail->getmail_ExportShipment($customer);
	// echo "<pre>".print_r($email_To,true)."</pre>";
	// echo $automail->getBodyExportShipment($customer_name,$std20,$std40,$hc40,$lcl,$hc45,$quotationid,$po,$salesid,$port,$avaliable,$paymdaterequire,$lastshipment,$expirydate,$currency,$amount,$agent,$requestshipdate,$noted,$paymenttype);
	// exit;
	if (!isset($email_To['sender'][0])) {
		echo " Email sender not found! Customer=".$customer;
	}else{
		// echo $automail->getSubjectExportShipment($customer_name,$customer,$quotationid,$po);
		// echo "<br>";
		// echo $automail->getBodyExportShipment($customer_name,$std20,$std40,$hc40,$lcl,$hc45,$quotationid,$po,$salesid,$port,$avaliable,$paymdaterequire,$lastshipment,$expirydate,$currency,$amount,$agent,$requestshipdate,$noted,$paymenttype,$customer);
		$mail = $automail->sendMail(
			$email_To['to'], 
			$email_To['cc'], 
			[],
			$automail->getSubjectExportShipment($customer_name,$customer,$quotationid,$po),
			$automail->getBodyExportShipment($customer_name,$std20,$std40,$hc40,$lcl,$hc45,$quotationid,$po,$salesid,$port,$avaliable,$paymdaterequire,$lastshipment,$expirydate,$currency,$amount,$agent,$requestshipdate,$noted,$paymenttype,$customer),
			'',
			$email_To['bcc'],
			$email_To['sender'][0]
		);
		if ($mail['result'] === true) {	

			$automail->updateExportShipment($customer,$salesid);
			// echo $mail['message'] . PHP_EOL."<br>";
			$writefile 	= fopen("../logfile.txt", "a") or die("Unable to open file!");
			$txt 		= $salesid." - ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
			fwrite($writefile, $txt);
			fclose($writefile);

		} else {

			// echo $mail['message'] . PHP_EOL;
			$writefile 	= fopen("../logfile.txt", "a") or die("Unable to open file!");
			$txt 		= $salesid." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
			fwrite($writefile, $txt);
			fclose($writefile);
			
		}
	}
}
