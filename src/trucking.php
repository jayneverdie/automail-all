<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

echo "Automail (Trucking Report)<br><br>";

$emaillist = [
	'to' => [
		// 'rattana_c@deestone.com'
		'harit_j@deestone.com'
	],
	'cc' => [
		// 'harit_j@deestone.com'
	],
	'bcc' => [
		
	]
];

$getdata = $automail->getData_Trucking();

if (count($getdata)==0) {
	echo "Data Not found!";
	exit;
}

foreach ($getdata as $key => $value) {
	$datenow 			= date("d F Y",strtotime($value['DATENOW']));
	$std20 				= $value['STD20'];
	$std40 				= $value['STD40'];
	$hc40 				= $value['HC40'];
	$lcl				= $value['LCL'];
	$hc45				= $value['HC45'];
	$shippingline  		= $value['DSG_ShippingLineDescription'];
	$forwarder 			= $value['PrimaryAgent'];
	$bookingno 			= $value['DSG_BookingNumber'];
	$cy 				= $value['DSG_CY'];
	$rtn 				= $value['DSG_RTN'];
	$closedate 			= $value['DSG_ClosingDate'];
	$closetime 			= $value['DSG_ClosingTime'];
	$feeder 			= $value['DSG_Feeder'];
	$feedervoy 			= $value['DSG_VoyFeeder'];
	$vessel 			= $value['DSG_Vessel'];
	$vesselvoy			= $value['DSG_VoyVessel'];
	$etd 				= $value['DSG_ETDDate'];
	$eta 				= $value['DSG_ETADate'];
	$port 				= $value['DSG_PortOfLoadingDesc'];
	$place 				= $value['DSG_ToPortDesc'];
	$transshipment 		= $value['DSG_Transhipment_Port'];
	$loadingplant 		= $value['DSG_LoadingPlant'];
	$loadingplantname 	= $value['DSG_LoadingPlant_Name'];
	$loadingdate 		= $value['DSG_EDDDate'];
	$paperless 			= $value['DSG_CustPaperlessCode'];
	$cyterminalid		= $value['DSG_ATCY'];
	$cyterminalname		= $value['DSG_TERMINALNAME'];
	$cycontact			= $value['DSG_CONTACTPERSON'];
	$cytel 				= $value['DSG_TEL'];
	$rtterminalid		= $value['DSG_ATRTN'];
	$rtterminalname		= $value['Return_TERMINALNAME'];
	$rtcontact			= $value['Return_CONTACTPERSON'];
	$rttel 				= $value['Return_TEL'];
	$truckagent 		= $value['NameTruckingAgent'];
	$truckremark 		= $value['DSG_TruckingRemark'];

	if ($value['DSG_VoucherSeries']!='') {
		$invoiceno 	= "DSC/".$value['DSG_VoucherSeries']."/".$value['DSG_VOUCHERNO'];
	}else{
		$invoiceno	= "";
	}
	

 	$closetime = new DateTime("@$closetime");  
 	$closetime = $closetime->format('H:i'); 

 	$customer = $value['AccountNum']; 
 	$so 	  = $value['SALESID'];

 	$email_To = $automail->getmail_TruckingShipment($customer);
 	$getweight = $automail->getGrossweight_Trucking($so);
 	$grossweight = $getweight[0]['TOTALGROSSWEIGHT'];
 	
 	if (!isset($email_To['sender'][0])) {
		echo " Email sender not found! Customer=".$customer;
	}else{
		// echo $automail->getSubject_Trucking($invoiceno);
		// echo $automail->getBody_Trucking($datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark);
		$mail = $automail->sendMail(
			$email_To['to'], 
			$email_To['cc'], 
			[],
			$automail->getSubject_Trucking($invoiceno),
			$automail->getBody_Trucking($datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark),
			'',
			$email_To['bcc'],
			$email_To['sender'][0]
		);

		if ($mail['result'] === true) {	
			$automail->update_Trucking($so);
			echo $mail['message'] . PHP_EOL."<br>";
		} else {
			echo $mail['message'] . PHP_EOL;
		}
	}
}