<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');
date_default_timezone_set("Asia/Bangkok");

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

echo "Automail (Trucking Combine Report)<br><br>";

$sender = 'ea_webmaster@deestone.com';

$emaillist = [
	'to' => [
		'harit_j@deestone.com'
	],
	'cc' => [
		// 'wattana_r@deestone.com'
	],
	'bcc' => [
		
	]
];

$getso = $automail->CheckCombine_Trucking_TEST();
$checkcombine_truck = count($getso);

$temp_so 	= [];

if ($checkcombine_truck!=0) {
	foreach ($getso as $value) {
		array_push($temp_so, $value['SO']);
	}
}else{
	echo "nodata";
	exit();
}

$so = $automail->arrToString($temp_so);
$getdata = $automail->GetDataCombine_Trucking($so);

// echo "<pre>".print_r($getdata,true)."</pre>";

foreach ($getdata as $data) {
	$getcontainer = $automail->GetContainerCombine_Trucking($data['SALESID']);

	foreach ($getcontainer as $container) {
		$std20 			= number_format($container['STD20'],0);
		$std40 			= number_format($container['STD40'],0);
		$hc40 			= number_format($container['HC40'],0);
		$hc45			= number_format($container['HC45'],0);
		$lcl			= $container['LCL'];
	}	

	if ($std20!=0) {
		$std20  = (string)$std20;
		$std20_ = $std20."x20''STD";
		$std20  = $std20.'x20\'STD';
	}else{
		$std20  = '';
		$std20_ = 'NULL';
	}

	if ($std40!=0) {
		$std40  = (string)$std40;
		$std40_ = $std40."x40''STD";
		$std40  = $std40.'x40\'STD';
	}else{
		$std40  = '';
		$std40_ = 'NULL';
	}

	if ($hc40!=0) {
		$hc40  = (string)$hc40;
		$hc40_ = $hc40."x40''HC";
		$hc40  = $hc40.'x40\'HC';
	}else{
		$hc40  = '';
		$hc40_ = 'NULL';
	}

	if ($hc45!=0) {
		$hc45  = (string)$hc45;
		$hc45_ = $hc45."x45''HC";
		$hc45  = $hc45.'x45\'HC';
	}else{
		$hc45  = '';
		$hc45_ = 'NULL';
	}

	if ($lcl!='') {
		$lcl  = (string)$lcl;
		$lcl_ = $lcl.'xLCL';
		$lcl  = $lcl.'xLCL';
	}else{
		$lcl  = '';
		$lcl_ = 'NULL';
	}

	$datenow 			= date("d F Y",strtotime($data['DATENOW']));
	$shippingline  		= $data['DSG_ShippingLineDescription'];
	$forwarder 			= $data['PrimaryAgent'];
	$bookingno 			= $data['DSG_BookingNumber'];
	$cy 				= $data['DSG_CY'];
	$rtn 				= $data['DSG_RTN'];
	$closedate 			= $data['DSG_ClosingDate'];
	$closetime 			= $data['DSG_ClosingTime'];
	$feeder 			= $data['DSG_Feeder'];
	$feedervoy 			= $data['DSG_VoyFeeder'];
	$vessel 			= $data['DSG_Vessel'];
	$vesselvoy			= $data['DSG_VoyVessel'];
	$etd 				= $data['DSG_ETDDate'];
	$eta 				= $data['DSG_ETADate'];
	$port 				= $data['DSG_PortOfLoadingDesc'];
	$place 				= $data['DSG_ToPortDesc'];
	$transshipment 		= $data['DSG_Transhipment_Port'];
	$loadingplant 		= $data['DSG_LoadingPlant'];
	$loadingplantname 	= $data['DSG_LoadingPlant_Name'];
	$loadingdate 		= $data['DSG_EDDDate'];
	$paperless 			= $data['DSG_CustPaperlessCode'];
	$cyterminalid		= $data['DSG_ATCY'];
	$cyterminalname		= $data['DSG_TERMINALNAME'];
	$cycontact			= $data['DSG_CONTACTPERSON'];
	$cytel 				= $data['DSG_TEL'];
	$rtterminalid		= $data['DSG_ATRTN'];
	$rtterminalname		= $data['Return_TERMINALNAME'];
	$rtcontact			= $data['Return_CONTACTPERSON'];
	$rttel 				= $data['Return_TEL'];
	$truckagent 		= $data['NameTruckingAgent'];
	$truckremark 		= $data['DSG_TruckingRemark'];
	$cutoffvgmdate		= $data['DSG_CutoffVGMDate'];
	$cutoffvgmtime		= $data['DSG_CutoffVGMTime'];
	$primarycontactid   = $data['DSG_PrimaryContactID'];

	if ($data['DSG_VoucherSeries']!='') {
		$invoiceno 	= "DSC/".$data['DSG_VoucherSeries']."/".$data['DSG_VOUCHERNO'];
	}else{
		$invoiceno	= "";
	}

	$closetime = new DateTime("@$closetime");  
 	$closetime = $closetime->format('H:i'); 

 	$cutoffvgmtime 	= new DateTime("@$cutoffvgmtime");  
 	$cutoffvgmtime 		= $cutoffvgmtime->format('H:i'); 

 	$customer = $data['AccountNum']; 

 	$email_To = $automail->getmail_TruckingShipment($customer);

 	$getweight 		= $automail->getGrossweight_Trucking($data['SALESID']);
 	$grossweight 	= $getweight[0]['TOTALGROSSWEIGHT'];
 	
 	// echo $automail->getSubject_Trucking($invoiceno);
	echo $automail->getBody_Trucking($data['SALESID'],$datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark);
	$automail->insert_TruckingLog_Test($data['SALESID'],$closetime,$grossweight,$std20_,$std40_,$hc40_,$lcl_,$hc45_);

 	exit();

 	$writefile 	= fopen("../logs/trucking.txt", "a") or die("Unable to open file!");
	$txt 		= "Automail Trucking => ".$data['SALESID']." - ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
	fwrite($writefile, $txt);
	fclose($writefile);
					
 	// $checkSOlog = $automail->CheckSOlog_Trucking($data['SALESID']);
	// $CountcheckSOlog = count($checkSOlog);

	// if ($CountcheckSOlog==0) {
	// 	// echo "is sent mail";
	//  	if (!isset($email_To['sender'][0])) {
	// 		$writefile 	= fopen("../logs/trucking.txt", "a") or die("Unable to open file!");
	// 			$txt 		= "Automail Trucking => ".$data['SALESID']." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ไม่มีผู้ส่ง Sender ".$customer. "\n";
	// 			echo "<br>";
	// 			fwrite($writefile, $txt);
	// 			fclose($writefile);
	// 	}else{

	// 		$mail = $automail->sendMail(
	// 			$email_To['to'], 
	// 			$email_To['cc'], 
	// 			[],
	// 			$automail->getSubject_Trucking($invoiceno),
	// 			$automail->getBody_Trucking($data['SALESID'],$datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark),
	// 			'',
	// 			$email_To['bcc'],
	// 			$email_To['sender'][0]
	// 		);

	// 		if ($mail['result'] === true) {	
				
	// 			if($automail->update_Trucking($data['SALESID']) === true){
	// 				$writefile 	= fopen("../logs/trucking.txt", "a") or die("Unable to open file!");
	// 				$txt 		= "Automail Trucking => ".$data['SALESID']." - ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
	// 				fwrite($writefile, $txt);
	// 				fclose($writefile);
	// 			}else{
	// 				$writefile 	= fopen("../logs/trucking.txt", "a") or die("Unable to open file!");
	// 				$txt 		= "Automail Trucking => ".$data['SALESID']." - ส่งสำเร็จ(Update Status ไม่ผ่าน) => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
	// 				fwrite($writefile, $txt);
	// 				fclose($writefile);
	// 			}
			
	// 			$automail->insert_TruckingLog($data['SALESID'],$closetime,$grossweight);

	// 			echo $mail['message'] . PHP_EOL."<br>";

	// 		} else {

	// 			$writefile 	= fopen("../logs/trucking.txt", "a") or die("Unable to open file!");
	// 			$txt 		= "Automail Trucking => ".$data['SALESID']." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
	// 			fwrite($writefile, $txt);
	// 			fclose($writefile);

	// 			echo $mail['message'] . PHP_EOL."<br>";
				
	// 		}

	// 	}

	// }

}
