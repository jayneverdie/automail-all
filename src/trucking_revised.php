<?php 

date_default_timezone_set("Asia/Bangkok");

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

echo "Automail (Trucking Revised)<br><br>";

$sender = 'ea_webmaster@deestone.com';

$emaillist = [
	'to' => [
		'harit_j@deestone.com'
		// 'rattana_c@deestone.com',
		// 'numpheong_p@deestone.com',
		// 'nuchjaree_o@deestone.com'
	],
	'cc' => [
		// 'wattana_r@deestone.com'
	],
	'bcc' => [
		
	]
];

$getrevised = $automail->CheckRevised_Trucking();
$datenow 	= date("d F Y");

if (count($getrevised)==0) {
	echo "nodata Revised";
	exit();
}

foreach ($getrevised as $so) {

	$checkLog	=	$automail->CheckLog_Trucking($so['SO']);

	$str = '';
	foreach ($checkLog as $log) {

		if ($so['SO']==$log['SO']) {

			$closetime = $so['DSG_ClosingTime'];
			$closetime = new DateTime("@$closetime");  
	 		$closetime = $closetime->format('H:i'); 

	 		if ($closetime=='00:00') {
	        	$closetime = '24:00';
	        }
	        
	 		$cutoffVGMtime = $so['DSG_CutoffVGMTime'];
			$cutoffVGMtime = new DateTime("@$cutoffVGMtime");  
	 		$cutoffVGMtime = $cutoffVGMtime->format('H:i'); 

	 		if ($cutoffVGMtime=='00:00') {
	        	$cutoffVGMtime = '24:00';
	        }

	        $cutoffVGMtimeL = $log['DSG_CutoffVGMTime'];
	 		$cutoffVGMtimeL = new DateTime("@$cutoffVGMtimeL");  
	 		$cutoffVGMtimeL = $cutoffVGMtimeL->format('H:i'); 

	 		if ($cutoffVGMtimeL=='00:00') {
	        	$cutoffVGMtimeL = '24:00';
	        }

	        $getcontainer = $automail->GetContainerCombine_Trucking($so['SO']);

			foreach ($getcontainer as $container) {
				$std20 			= number_format($container['STD20'],0);
				$std40 			= number_format($container['STD40'],0);
				$hc40 			= number_format($container['HC40'],0);
				$hc45			= number_format($container['HC45'],0);
				$lcl			= $container['LCL'];
			}

				$container = [];
				
				if ($std20!=0) {
					$std20  = (string)$std20;
					$std20_ = $std20."x20''STD";
					$std20  = $std20.'x20\'STD';
					array_push($container, $std20);
				}else{
					$std20  = '';
					$std20_ = 'NULL';
				}

				if ($std40!=0) {
					$std40  = (string)$std40;
					$std40_ = $std40."x40''STD";
					$std40  = $std40.'x40\'STD';
					array_push($container, $std40);
				}else{
					$std40  = '';
					$std40_ = 'NULL';
				}

				if ($hc40!=0) {
					$hc40  = (string)$hc40;
					$hc40_ = $hc40."x40''HC";
					$hc40  = $hc40.'x40\'HC';
					array_push($container, $hc40);
				}else{
					$hc40  = '';
					$hc40_ = 'NULL';
				}

				if ($hc45!=0) {
					$hc45  = (string)$hc45;
					$hc45_ = $hc45."x45''HC";
					$hc45  = $hc45.'x45\'HC';
					array_push($container, $hc45);
				}else{
					$hc45  = '';
					$hc45_ = 'NULL';
				}

				if ($lcl!='') {
					$lcl  = (string)$lcl;
					$lcl_ = $lcl.'xLCL';
					$lcl  = $lcl."xLCL";
					array_push($container, $lcl);
				}else{
					$lcl  = '';
					$lcl_ = 'NULL';
				}

				$container_str = (implode(",",$container));

			// Let's Go !
	 		if ($so['DSG_CY']						!=	$log['DSG_CY'] || 
	 			$so['DSG_RTN']						!=	$log['DSG_RTN'] || 
	 			$so['DSG_TERMINALNAME']				!=	$log['DSG_ATCY'] || 
	 			$so['Return_TERMINALNAME']			!=	$log['DSG_ATRTN'] || 
	 			$so['DSG_CustPaperlessCode']		!=	$log['DSG_CustPaperlessCode'] || 
	 			$so['DSG_ShippingLineDescription']	!=	$log['DSG_ShippingLine'] || 
	 			$so['DSG_Feeder']					!=	$log['DSG_Feeder'] || 
	 			$so['DSG_VoyFeeder']				!=	$log['DSG_VoyFeeder'] || 
	 			$so['DSG_Vessel']					!=	$log['DSG_Vessel'] || 
	 			$so['DSG_VoyVessel']				!=	$log['DSG_VoyVessel'] ||
	 			$closetime							!=	$log['DSG_ClosingTime'] ||
	 			$so['DSG_BookingNumber']			!=	$log['DSG_BookingNumber'] || 
	 			$so['DSG_EDDDate']					!=	$log['DSG_EDDDate'] ||
	 			$so['DSG_ClosingDate']				!=	$log['DSG_ClosingDate'] ||
	 			$so['PrimaryAgent']					!=	$log['PrimaryAgent'] ||
	 			$so['DSG_ETDDate']					!=	$log['DSG_ETDDate'] ||
	 			$so['DSG_ETADate']					!=	$log['DSG_ETADate'] ||
	 			$so['DSG_PortOfLoadingDesc']		!=	$log['DSG_PortOfLoadingDesc'] ||
	 			$so['DSG_ToPortDesc']				!=	$log['DSG_ToPortDesc'] ||
	 			$so['DSG_Transhipment_Port']		!=	$log['DSG_Transhipment_Port'] ||
	 			$so['Return_CONTACTPERSON']." Tel. ".$so['Return_TEL']!=$log['PIC2'] ||
	 			$so['DSG_CONTACTPERSON']." Tel. ".$so['DSG_TEL'] != $log['PIC1'] ||
	 			$so['DSG_LoadingPlant']				!=	$log['DSG_LoadingPlant'] ||
	 			$so['DSG_TruckingRemark']			!=	$log['DSG_TruckingRemark'] ||
	 			$so['DSG_DischargePort'] 			!=  $log['DSG_DischargePort'] ||
	 			// $std20								!=	$log['STD20'] ||
	 			// $std40								!=	$log['STD40'] ||
	 			// $hc40								!=	$log['HC40'] ||
	 			// $hc45								!=	$log['HC45'] ||
	 			// $lcl								!=	$log['LCL'] 
	 			$so['DSG_CutoffVGMDate']			!=	$log['DSG_CutoffVGMDate'] ||
	 			$so['DSG_CutoffVGMTime']			!=	$log['DSG_CutoffVGMTime']

	 		) {
	 			
				$str .= "เรียนผู้ให้บริการหัวลาก". '<br><br>';

				$str .= "อ้างถึงอินวอยซ์ # DSC/".$so['DSG_VoucherSeries']."/".$so['DSG_VOUCHERNO'].'<br>';

				$str .= "แก้ไขข้อมูลตามรายละเอียดตัวหนังสือสีแดง".'<br><br>';

				$str .= "<table>";

				$str .= "<tr>
						<td><b> Date </b></td>
						<td> : " .$datenow. "</td>
					</tr>";

				$str .= "<tr>
						<td><b> Invoice No.  </b></td>
						<td> : DSC/" .$so['DSG_VoucherSeries']."/".$so['DSG_VOUCHERNO']. "</td>
					</tr>";

				// if ($std20 != $log['STD20'] ||
		 	// 		$std40 != $log['STD40'] ||
		 	// 		$hc40  != $log['HC40'] ||
		 	// 		$hc45  != $log['HC45'] ||
		 	// 		$lcl   != $log['LCL']) {

				// 	$str .= "<tr>
				// 		<td><b> Volumn </b></td>
				// 		<td> : <font color='red'>" .$container_str. "</font></td>
				// 	</tr>";

				// }else{
					$str .= "<tr>
						<td><b> Volumn </b></td>
						<td> : " .$container_str. "</td>
					</tr>";
				// }

				if ($so['DSG_ShippingLineDescription']!=$log['DSG_ShippingLine']) {
					$str .= "<tr>
						<td><b> Agent </b></td>
						<td> : <font color='red'>" .$so['DSG_ShippingLineDescription']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Agent </b></td>
						<td> : " .$so['DSG_ShippingLineDescription']. "</td>
					</tr>";
				}

				if ($so['PrimaryAgent']!=$log['PrimaryAgent']) {
					$str .= "<tr>
						<td><b> Forwarder Name  </b></td>
						<td> : <font color='red'>" .$so['PrimaryAgent']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Forwarder Name   </b></td>
						<td> : " .$so['PrimaryAgent']. "</td>
					</tr>";
				}

				if ($so['DSG_BookingNumber']!=$log['DSG_BookingNumber']) {
					$str .= "<tr>
						<td><b> Booking No. </b></td>
						<td> : <font color='red'>" .$so['DSG_BookingNumber']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Booking No. </b></td>
						<td> : " .$so['DSG_BookingNumber']. "</td>
					</tr>";
				}
			
				if ($so['DSG_CY']!=$log['DSG_CY']) {
					$str .= "<tr>
						<td><b> CY date (วันที่ลากตู้เปล่า) </b></td>
						<td> : <font color='red'>" .$so['DSG_CY']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> CY date (วันที่ลากตู้เปล่า) </b></td>
						<td> : " .$so['DSG_CY']. "</td>
					</tr>";
				}
				
				if ($so['DSG_TERMINALNAME']!=$log['DSG_ATCY']) {
					$str .= "<tr>
						<td><b> CY EMPTY container at </b></td>
						<td> : <font color='red'>". $so['DSG_TERMINALNAME']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> CY EMPTY container at </b></td>
						<td> : " .$so['DSG_TERMINALNAME']. "</td>
					</tr>";
				}

				if ($so['DSG_CONTACTPERSON']." Tel. ".$so['DSG_TEL'] != $log['PIC1']) {
					$str .= "<tr>
						<td><b> PIC </b></td>
						<td> : <font color='red'>". $so['DSG_CONTACTPERSON']." Tel.".$so['DSG_TEL']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> PIC </b></td>
						<td> : " .$so['DSG_CONTACTPERSON']." Tel. ".$so['DSG_TEL']."</td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";

				if ($so['DSG_EDDDate']!=$log['DSG_EDDDate']) {
					$str .= "<tr>
						<td><b> Loading date (วันที่ตู้เข้าโรงงาน) </b></td>
						<td> : <font color='red'>" .$so['DSG_EDDDate']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Loading date (วันที่ตู้เข้าโรงงาน) </b></td>
						<td> : " .$so['DSG_EDDDate']. "</td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";

				if ($so['DSG_RTN']!=$log['DSG_RTN']) {
					$str .= "<tr>
						<td><b> Return date(วันที่คืนตู้หนัก)  </b></td>
						<td> : <font color='red'>" .$so['DSG_RTN']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Return date(วันที่คืนตู้หนัก) </b></td>
						<td> : " .$so['DSG_RTN']. "</td>
					</tr>";
				}

				if ($so['Return_TERMINALNAME']!=$log['DSG_ATRTN']) {
					$str .= "<tr>
						<td><b> Return Full container at </b></td>
						<td> : <font color='red'>" .$so['Return_TERMINALNAME']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Return Full container at </b></td>
						<td> : " .$so['Return_TERMINALNAME']. "</td>
					</tr>";
				}

				if ($so['Return_CONTACTPERSON']." Tel. ".$so['Return_TEL']!=$log['PIC2']) {
					$str .= "<tr>
						<td><b> PIC </b></td>
						<td> : <font color='red'>" .$so['Return_CONTACTPERSON']." Tel.".$so['Return_TEL']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> PIC </b></td>
						<td> : " .$so['Return_CONTACTPERSON']." Tel. ".$so['Return_TEL']."</td>
					</tr>";
				}

				if ($so['DSG_ClosingDate']!=$log['DSG_ClosingDate']) {
					$str .= "<tr>
						<td><b> Closing date </b></td>
						<td> : <font color='red'>" .$so['DSG_ClosingDate']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Closing date </b></td>
						<td> : " .$so['DSG_ClosingDate']. "</td>
					</tr>";
				}

		 		if ($closetime!=$log['DSG_ClosingTime']) {
					$str .= "<tr>
						<td><b> Closing time </b></td>
						<td> : <font color='red'>" .$closetime. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Closing time </b></td>
						<td> : " .$closetime. "</td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";


				if ($so['DSG_VoyFeeder']!="") {
					$v = " V. ";
				}else{
					$v = " ";
				}

				if ($so['DSG_Feeder']!=$log['DSG_Feeder']) {
					$Feeder = "<font color='red'>".$so['DSG_Feeder']."</font>";
				}else{
					$Feeder = "<font color='black'>".$so['DSG_Feeder']."</font>";
				}

				if ($so['DSG_VoyFeeder']!=$log['DSG_VoyFeeder']) {
					$VFeeder = "<font color='red'> ".$v.$so['DSG_VoyFeeder']."</font>";
				}else{
					$VFeeder = "<font color='black'> ".$v.$so['DSG_VoyFeeder']."</font>";
				}

				if ($so['DSG_Feeder']!=$log['DSG_Feeder'] || $so['DSG_VoyFeeder']!=$log['DSG_VoyFeeder']) {
					$str .= "<tr>
						<td><b> Fedder Vessel & Voyage </b></td>
						<td> : ".$Feeder." ".$VFeeder."</td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Feeder Vessel & Voyage  </b></td>
						<td> : " .$so['DSG_Feeder'].$v.$so['DSG_VoyFeeder']. "</td>
					</tr>";
				}

				if ($so['DSG_VoyVessel']!="") {
					$v = " V. ";
				}else{
					$v = " ";
				}

				if ($so['DSG_Vessel']!=$log['DSG_Vessel']) {
					$Vessel = "<font color='red'>".$so['DSG_Vessel']."</font>";
				}else{
					$Vessel = "<font color='black'>".$so['DSG_Vessel']."</font>";
				}

				if ($so['DSG_VoyVessel']!=$log['DSG_VoyVessel']) {
					$VVessel = "<font color='red'> ".$v.$so['DSG_VoyVessel']."</font>";
				}else{
					$VVessel = "<font color='black'> ".$v.$so['DSG_VoyVessel']."</font>";
				}

				if ($so['DSG_Vessel']!=$log['DSG_Vessel'] || $so['DSG_VoyVessel']!=$log['DSG_VoyVessel']) {
					$str .= "<tr>
						<td><b> Mother Vessel & Voyage </b></td>
						<td> : ".$Vessel. " ".$VVessel."</td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Mother Vessel & Voyage </b></td>
						<td> : " .$so['DSG_Vessel'].$v.$so['DSG_VoyVessel']. "</td>
					</tr>";
				}

				if ($so['DSG_ETDDate']!=$log['DSG_ETDDate']) {
					$str .= "<tr>
						<td><b> ETD </b></td>
						<td> : <font color='red'>" .$so['DSG_ETDDate']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> ETD </b></td>
						<td> : " .$so['DSG_ETDDate']. "</td>
					</tr>";
				}

				if ($so['DSG_ETADate']!=$log['DSG_ETADate']) {
					$str .= "<tr>
						<td><b> ETA </b></td>
						<td> : <font color='red'>" .$so['DSG_ETADate']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> ETA </b></td>
						<td> : " .$so['DSG_ETADate']. "</td>
					</tr>";
				}

				if ($so['DSG_PortOfLoadingDesc']!=$log['DSG_PortOfLoadingDesc']) {
					$str .= "<tr>
						<td><b> Port of Loading </b></td>
						<td> : <font color='red'>" .$so['DSG_PortOfLoadingDesc']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Port of Loading </b></td>
						<td> : " .$so['DSG_PortOfLoadingDesc']. "</td>
					</tr>";
				}

				if ($so['DSG_ToPortDesc']!=$log['DSG_ToPortDesc']) {
					$str .= "<tr>
						<td><b> Place of Delivery </b></td>
						<td> : <font color='red'>" .$so['DSG_ToPortDesc']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Place of Delivery </b></td>
						<td> : " .$so['DSG_ToPortDesc']. "</td>
					</tr>";
				}

				if ($so['DSG_Transhipment_Port']!=$log['DSG_Transhipment_Port']) {
					$str .= "<tr>
						<td><b> Transhipment Port </b></td>
						<td> : <font color='red'>" .$so['DSG_Transhipment_Port']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Transhipment Port </b></td>
						<td> : " .$so['DSG_Transhipment_Port']. "</td>
					</tr>";
				}

				if ($so['DSG_DischargePort']!=$log['DSG_DischargePort']) {
					$str .= "<tr>
						<td><b> Port of discharge </b></td>
						<td> : <font color='red'>" .$so['DSG_DischargePort']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Port of discharge </b></td>
						<td> : " .$so['DSG_DischargePort']. "</td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";

				if ($so['DSG_CustPaperlessCode']!=$log['DSG_CustPaperlessCode']) {
					$str .= "<tr>
						<td><b> Paperless code </b></td>
						<td> : <font color='red'>" .$so['DSG_CustPaperlessCode']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Paperless code </b></td>
						<td> : " .$so['DSG_CustPaperlessCode']. "</td>
					</tr>";
				}

				$getweight 		= $automail->getGrossweight_Trucking($so['SO']);
 				$grossweight 	= $getweight[0]['TOTALGROSSWEIGHT'];

 				if ($grossweight!=$log['grossweight']) {
					$str .= "<tr>
						<td><b> Gross Weight  </b></td>
						<td> : <font color='red'>" .number_format($grossweight, 2). "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Gross Weight </b></td>
						<td> : " .number_format($grossweight, 2). "</td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";

				if ($so['NameTruckingAgent']!=$log['NameTruckingAgent']) {
					$str .= "<tr>
						<td><b> Truck Agent </b></td>
						<td> : <font color='red'>" .$so['NameTruckingAgent']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Truck Agent </b></td>
						<td> : " .$so['NameTruckingAgent']. "</td>
					</tr>";
				}

				$lp = $automail->getLoadingPlant($so['SO']);
				
				if ($so['DSG_LoadingPlant']!=$log['DSG_LoadingPlant']) {
					$str .= "<tr>
						<td><b> Loading plant </b></td>
						<td> : <font color='red'>" .$lp. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Loading plant </b></td>
						<td> : " .$lp. "</td>
					</tr>";
				}

				if ($so['DSG_TruckingRemark']!=$log['DSG_TruckingRemark']) {
					$str .= "<tr>
						<td><b> Remark </b></td>
						<td> : <font color='red'>" .$so['DSG_TruckingRemark']. "</font></td>
						<td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Remark </b></td>
						<td> : " .$so['DSG_TruckingRemark']. "</td>
						<td>
					</tr>";
				}

				$str .= "<tr><td><br></td></tr>";

				if ($so['DSG_CutoffVGMDate']!=$log['DSG_CutoffVGMDate']) {
					$str .= "<tr>
						<td><b> Cut off VGM Date </b></td>
						<td> : <font color='red'>" .$so['DSG_CutoffVGMDate']. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Cut off VGM Date </b></td>
						<td> : " .$so['DSG_CutoffVGMDate']. "</td>
					</tr>";
				}

		 		if ($cutoffVGMtime!=$cutoffVGMtimeL) {
					$str .= "<tr>
						<td><b> Cut off VGM time </b></td>
						<td> : <font color='red'>" .$cutoffVGMtime. "</font></td>
					</tr>";
				}else{
					$str .= "<tr>
						<td><b> Cut off VGM time </b></td>
						<td> : " .$cutoffVGMtime. "</td>
					</tr>";
				}

				// if ($so['DSG_PrimaryContactID']!=$log['DSG_PrimaryContactID']) {
				// 	$str .= "<tr>
				// 		<td><b> Primary contact ID </b></td>
				// 		<td> : <font color='red'>" .$so['DSG_PrimaryContactID']. "</font></td>
				// 	</tr>";
				// }else{
				// 	$str .= "<tr>
				// 		<td><b> Primary contact ID </b></td>
				// 		<td> : " .$so['DSG_PrimaryContactID']. "</td>
				// 	</tr>";
				// }

				$str .= "</table>";

				$str .= "<br>จึงเรียนมาเพื่อทราบ<br><br>";

				$str .= "ขอแสดงความนับถือ";

				$signature = $automail->getsignatureTrucking($so['AccountNum']);

				$str .= "<table>";
				$str .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr>";
				$str .= "<tr><td colspan=2><font size='2px' color='#696969'>".$signature[0]['DSG_ADDRESS']."</font> </td></tr>";
				$str .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr>";
				$str .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font> </td></tr>";
				$str .= "</table>";
				
				$customer = $so['AccountNum']; 
				$email_To = $automail->getmail_TruckingShipment($customer);

				if (!isset($email_To['sender'][0])) {
					$writefile 	= fopen("../logs/trucking_revised.txt", "a") or die("Unable to open file!");
						$txt 		= "Automail Trucking Revised => ".$data['SALESID']." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ไม่มีผู้ส่ง Sender ".$customer. "\n";
						fwrite($writefile, $txt);
						fclose($writefile);
				}else{

					$mail = $automail->sendMail(
						$email_To['to'], 
						$email_To['cc'], 
						[],
						"Revised Deestone Corp. : ใบสั่งงานหัวลาก - DSC/".$so['DSG_VoucherSeries']."/".$so['DSG_VOUCHERNO'],
						$str,
						'',
						$email_To['bcc'],
						$email_To['sender'][0]
					);

					if ($mail['result'] === true) {	
						
						$automail->update_TruckingRevised(
							$so['DSG_CY'],
							$so['DSG_RTN'],
							$so['DSG_TERMINALNAME'],
							$so['Return_TERMINALNAME'],
							$so['DSG_CustPaperlessCode'],
							$so['DSG_ShippingLineDescription'],
							$so['DSG_Feeder'],
							$so['DSG_VoyFeeder'],
							$so['DSG_Vessel'],
							$so['DSG_VoyVessel'],
							$so['DSG_LoadingPlant'],
							$so['DSG_ClosingDate'],
							$closetime,
							$so['DSG_CutoffVGMDate'],
							$so['DSG_CutoffVGMTime'],
							$so['DSG_BookingNumber'],
							$so['DSG_PrimaryContactID'],
							$so['DSG_EDDDate'],
							$so['SO'],
							$so['PrimaryAgent'],
							$so['DSG_CONTACTPERSON']." Tel. ".$so['DSG_TEL'],
							$so['Return_CONTACTPERSON']." Tel. ".$so['Return_TEL'],
							$so['DSG_ETDDate'],
							$so['DSG_ETADate'],
							$so['DSG_PortOfLoadingDesc'],
							$so['DSG_ToPortDesc'],
							$so['DSG_Transhipment_Port'],
							$grossweight,
							$so['NameTruckingAgent'],
							$so['DSG_TruckingRemark'],
							$so['DSG_DischargePort'],
							$std20_,$std40_,$hc40_,$lcl_,$hc45_

						);

						$writefile 	= fopen("../logs/trucking_revised.txt", "a") or die("Unable to open file!");
						$txt 		= "Automail Trucking Revised => ".$so['SO']." - ส่งสำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
						fwrite($writefile, $txt);
						fclose($writefile);
						echo $mail['message'] . PHP_EOL."<br>";

					} else {

						$writefile 	= fopen("../logs/trucking_revised.txt", "a") or die("Unable to open file!");
						$txt 		= "Automail Trucking Revised => ".$so['SO']." - ส่งไม่สำเร็จ => [".date("d-m-Y H:i")."] ".$mail['message'] . PHP_EOL. "\n";
						fwrite($writefile, $txt);
						fclose($writefile);
						echo $mail['message'] . PHP_EOL."<br>";
						
					}

				}

				// echo $str;
			}else{
				echo "no send mail<br>";

			}

		}

	}
}
