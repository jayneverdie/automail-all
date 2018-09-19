<?php
// my function
require_once 'sqlsrv.php';

use Wattanar\Sqlsrv;

class AutoMail
{
	public function connect()
	{
		return Sqlsrv::connect('pentos\develop', 'sa', 'c,]\'4^j', 'AX_Customize');
	}

	public function connectMormont()
	{
		return Sqlsrv::connect('192.168.90.30\develop', 'sa', 'c,]\'4^j', 'AUTOMAIL_EXPORT');
	}

	public function CheckCombine_Trucking()
	{
		$conn = self::connect();
		return Sqlsrv::Rows(
			$conn,
			"SELECT 
					CSL.SALESID [SO]
			FROM SALESTABLE S
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
				  FROM CUSTCONFIRMSALESLINK SL
				  GROUP BY DATAAREAID,ORIGSALESID
				 )SL
				 ON SL.DATAAREAID = S.DATAAREAID
				 AND SL.ORIGSALESID = S.SALESID
				 JOIN CUSTCONFIRMSALESLINK CSL
				 ON CSL.DATAAREAID = S.DATAAREAID
				 AND CSL.ORIGSALESID = S.SALESID
				 AND CSL.CONFIRMID = SL.CONFIRMID
			WHERE S.DSG_AutoMailStatusVendor=1 AND S.DATAAREAID = 'dsc'
			GROUP BY CSL.SALESID"
		); 
	}

	public function arrToString($arr)
	{
		if (count($arr) === 0) {
			return '';
		}

		$str = '';
		foreach ($arr as $data) {
			$str .= "'".$data ."'". ',';
		}
		return trim($str, ',');
	}

	public function GetDataCombine_Trucking($so)
	{
		$conn = self::connect();
		return Sqlsrv::Rows(
			$conn,
			"SELECT 
					GETDATE() AS DATENOW
					,S.SALESID
					,V.AccountNum
					,S.DSG_ShippingLineDescription
					,S.DSG_PrimaryAgentId
					,A.DSG_DESCRIPTION AS PrimaryAgent
					,S.DSG_BookingNumber
					,CASE WHEN S.DSG_CY IS NULL THEN ''
						  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
					,CASE WHEN S.DSG_RTN IS NULL THEN ''
						  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
					,CASE WHEN S.DSG_ClosingDate IS NULL THEN ''
						  WHEN S.DSG_ClosingDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ClosingDate)+' '+DATENAME(month,S.DSG_ClosingDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ClosingDate)) END [DSG_ClosingDate]
					,S.DSG_ClosingTime
					,S.DSG_Feeder
					,S.DSG_VoyFeeder
					,S.DSG_Vessel
					,S.DSG_VoyVessel
					,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
						  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
					,CASE WHEN S.DSG_ETADate IS NULL THEN ''
						  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
					,S.DSG_PortOfLoadingDesc
					,S.DSG_ToPortDesc
					,S.DSG_Transhipment_Port
					,S.DSG_CustPaperlessCode
					,S.DSG_LoadingPlant
					,P.NAME AS DSG_LoadingPlant_Name
					,S.DSG_ATCY
					,T.DSG_TERMINALNAME
					,T.DSG_CONTACTPERSON
					,T.DSG_TEL
					,S.DSG_ATRTN
					,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
					,TT.DSG_CONTACTPERSON AS Return_CONTACTPERSON
					,TT.DSG_TEL AS Return_TEL
					,CJJ.DSG_TOTALGROSSWEIGHT
					,CJJ.DSG_VoucherSeries
					,CJJ.DSG_VOUCHERNO
					,V.NAME AS NameTruckingAgent
					,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
						  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
					,S.DSG_TruckingRemark
					,CASE WHEN S.DSG_CutoffVGMDate IS NULL THEN ''
						  WHEN S.DSG_CutoffVGMDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CutoffVGMDate)+' '+DATENAME(month,S.DSG_CutoffVGMDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CutoffVGMDate)) END [DSG_CutoffVGMDate]
					,DSG_CutoffVGMTime
					,DSG_PrimaryContactID
			FROM SALESTABLE S 
			LEFT JOIN INVENTPICKINGLISTJOUR INVP ON INVP.ORDERID = S.SALESID 
			AND INVP.DATAAREAID = S.DATAAREAID
			AND INVP.NOYESID_DISABLED=0
			LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1 AND V.DATAAREAID='dsc'
			LEFT JOIN DSG_TerminalTrucking T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
			LEFT JOIN DSG_TerminalTrucking TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
			LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			LEFT JOIN DATAAREA P ON S.DSG_LoadingPlant = P.ID  
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
				  FROM CustConfirmJour CJ
				  GROUP BY DATAAREAID,SALESID
				 )CJ
				 ON CJ.DATAAREAID = S.DATAAREAID
				 AND CJ.SALESID = S.SALESID
				 JOIN CustConfirmJour CJJ
				 ON CJJ.DATAAREAID = S.DATAAREAID
				 AND CJJ.SALESID = S.SALESID
				 AND CJJ.CONFIRMID = CJ.CONFIRMID
			WHERE S.SALESID IN ($so)
			AND S.DATAAREAID = 'dsc'"
		); 
	}

	public function GetContainerCombine_Trucking($so)
	{
		$conn = self::connect();
		return Sqlsrv::Rows(
			$conn,
			"SELECT 
					SUM(S.DSG_CONTAINER1X20) [STD20]
					,SUM(S.DSG_CONTAINER1X40) [STD40]
					,SUM(S.DSG_CONTAINER1X40HC) [HC40]
					,SUM(S.DSG_CONTAINER1X45HC) [HC45]
					,S.DSG_ContainerLCL [LCL]
			FROM SALESTABLE S
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
				  FROM CUSTCONFIRMSALESLINK SL
				  GROUP BY DATAAREAID,ORIGSALESID
				 )SL
				 ON SL.DATAAREAID = S.DATAAREAID
				 AND SL.ORIGSALESID = S.SALESID
				 JOIN CUSTCONFIRMSALESLINK CSL
				 ON CSL.DATAAREAID = S.DATAAREAID
				 AND CSL.ORIGSALESID = S.SALESID
				 AND CSL.CONFIRMID = SL.CONFIRMID
			WHERE CSL.SALESID = ? AND S.DATAAREAID = 'dsc'
			GROUP BY S.DSG_ContainerLCL",[$so]
		); 
	}

	public function getmail_TruckingShipment($customer)
	{
		$conn = self::connect();

		$listsTo = [];
		$listsCC = [];
		$listsBCC = [];
		$listsSender = [];

		$_to = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=0 
			AND VENDACCOUNT=?",[$customer]
		);

		$_cc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=1
			AND VENDACCOUNT=?",[$customer]
		);

		$_bcc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=2
			AND VENDACCOUNT=?",[$customer]
		);

		$_sender = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=3
			AND VENDACCOUNT=?",[$customer]
		);

		foreach($_to as $t) {
			$listsTo[] = $t['DSG_EMAIL'];
		}

		foreach($_cc as $c) {
			$listsCC[] = $c['DSG_EMAIL'];
		}

		foreach($_bcc as $b) {
			$listsBCC[] = $b['DSG_EMAIL'];
		}

		foreach($_sender as $b) {
			$listsSender[] = $b['DSG_EMAIL'];
		}

		return [
			'to' 		=> $listsTo,
			'cc' 		=> $listsCC,
			'bcc'		=> $listsBCC,
			'sender'	=> $listsSender
		];
	}

	public function getGrossweight_Trucking($so)
	{
		$conn = self::connect();

		$grossweight = Sqlsrv::rows(
			$conn, 
			"SELECT SUM(ROUND((ISNULL(CT.QTY/PU.FACTOR,0)*IPM.PACKINGUNITWEIGHT)+(SOL.DSG_ITEMNETWEIGHT*CT.QTY/1000),2)) AS TOTALGROSSWEIGHT
					FROM SALESTABLE SO JOIN 
					     (
					      SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
					      FROM CUSTCONFIRMSALESLINK SL
					      GROUP BY DATAAREAID,ORIGSALESID
					     )SL
					     ON SL.DATAAREAID = SO.DATAAREAID
					     AND SL.ORIGSALESID = SO.SALESID
					     JOIN CUSTCONFIRMSALESLINK CSL
					     ON CSL.DATAAREAID = SO.DATAAREAID
					     AND CSL.ORIGSALESID = SO.SALESID
					     AND CSL.CONFIRMID = SL.CONFIRMID
					     JOIN CUSTCONFIRMTRANS CT
					     ON CT.DATAAREAID = CSL.DATAAREAID
					     AND CT.CONFIRMID = CSL.CONFIRMID
					     AND CT.ORIGSALESID = CSL.ORIGSALESID
					     JOIN INVENTTABLE IT
					     ON IT.ITEMID = CT.ITEMID
					     AND IT.DATAAREAID = 'DV'
					     JOIN INVENTPACKAGINGGROUP PG
					     ON PG.DATAAREAID = 'DV'
					     AND PG.PACKAGINGGROUPID = IT.PACKAGINGGROUPID
					     JOIN INVENTPACKAGINGUNIT PU
					     ON PU.DATAAREAID = 'DV'
					     AND PU.ITEMRELATION = PG.PACKAGINGGROUPID
					     JOIN INVENTPACKAGINGUNITMATERIAL IPM
					     ON IPM.DATAAREAID = 'DV'
					     AND IPM.PACKINGUNITRECID = PU.RECID
					     JOIN SALESLINE SOL
					     ON SOL.DATAAREAID = CT.DATAAREAID
					     AND SOL.SALESID = CT.ORIGSALESID
					     AND SOL.ITEMID = CT.ITEMID
					     
					WHERE SO.DATAAREAID = 'DSC'
					AND CSL.SALESID=?",[$so]
		);

		return $grossweight;
	}

	public function CheckSOlog_Trucking($so)
	{
		$conn = self::connectMormont();
		return Sqlsrv::Rows(
			$conn,
			"SELECT *
			FROM TRUCKING
			WHERE SO=?",
			[$so]
		);
	}

	public function sendMail($mailTo = [], $mailCC = [], $file = [], $subject = '', $body = '', $pathToFile = '', $BCC = [], $sender = '')
	{
		if ($sender === '') {
			$sender_mail = 'rattana_c@deestone.com';
		} else {
			$sender_mail = $sender;
		}

		$mail = new PHPMailer();

		$mail->isSMTP();
		$mail->Host = "idc.deestone.com";
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "ssl";
		$mail->Username = "ea_webmaster@deestone.com";
		$mail->Password = "c,]'4^j";
		$mail->CharSet = "utf-8";
		$mail->Port = 465;
		$mail->From = $sender_mail;
		$mail->FromName = $sender_mail;

		
		if (count($mailTo) > 0) {
			foreach ($mailTo as $customerMailTo) {
				$mail->addAddress($customerMailTo);
			}
		} else {
			return ['result' => false, 'message' => 'No recipients mail.'];
		}
		
		if (count($mailCC) > 0) {
			foreach ($mailCC as $customerMailCC) {
				$mail->addCC($customerMailCC);
			}
		}

		if (count($BCC) > 0) {
			foreach ($BCC as $MAIL_BCC) {
				$mail->addBCC($MAIL_BCC);
			}
		}

		if (count($file) > 0) {
			foreach ($file as $fileDestination) {
				$mail->addAttachment($pathToFile . $fileDestination);
			}
		}

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;

		if($mail->send()) {
			return ['result' => true, 'message' => 'Message has been sent'];
		} else {
			return ['result' => false, 'message' => $mail->ErrorInfo];
		}
	}

	public function getSubject_Trucking($invoiceno){
		$txt = '';
		$txt .= "TEST Deestone Corp. : ใบสั่งงานหัวลาก - ".$invoiceno;
		return $txt;
	}

	public function getBody_Trucking($so,$datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark)
	{
		$txt = '';
		$container = [];

			if ($std20!='') {
	        	array_push($container, $std20);
	        }
	        if($std40!=''){
	        	array_push($container, $std40);
	        }
	        if($hc40!=''){
	        	array_push($container, $hc40);
	        }
	        if($lcl!=''){
	        	array_push($container, $lcl);
	        }
	        if($hc45!=''){
	        	array_push($container, $hc45);
	        }

	        $container_str = (implode(",",$container));

	        if ($closetime=='00:00') {
	        	$closetime = '24:00';
	        }
	 
			$txt .= "<table>";
			$txt .= "<tr><td>เรียนผู้ให้บริการหัวลาก</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td colspan=2>แจ้งข้อมูล ลาก/คืน ตู้ ตามรายละเอียดด้านล่าง หากไม่สามารถ ลาก/คืน ตู้ได้ตามเวลาที่กำหนดรบกวนแจ้งกลับคะ</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>บริษัท ดีสโตน คอร์ปอเรชั่น จำกัด </b></td></tr>";
			$txt .= "<tr><td><b>Trucking Report </b></td></tr>";
			$txt .= "<tr><td><b>Date </b></td><td> : ". $datenow."</td></tr>";
			$txt .= "<tr><td><b>Invoice No. </b></td><td> : ". $invoiceno."</td></tr>";
			$txt .= "<tr><td><b>Volumn </b></td><td> : ". $container_str."</td></tr>";
			$txt .= "<tr><td><b>Agent </b></td><td> : ". $shippingline."</td></tr>";
			$txt .= "<tr><td><b>Forwarder Name </b></td><td> : ". $forwarder."</td></tr>";
			$txt .= "<tr><td><b>Booking No. </b></td><td> : ". $bookingno."</td></tr>";
			$txt .= "<tr><td><b>CY date(วันที่ลากตู้เปล่า) </b></td><td> : ". $cy."</td></tr>";
			$txt .= "<tr><td><b>CY Epmty container at </b></td><td> : ".$cyterminalname."</td></tr>";
			$txt .= "<tr><td><b>PIC </b></td><td> : ".$cycontact." Tel. ".$cytel."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>Loading date  (วันที่ตู้เข้าโรงงาน)</b></td><td> : ".$loadingdate."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>Return date(วันที่คืนตู้หนัก) </b></td><td> : ".$rtn."</td></tr>";
			$txt .= "<tr><td><b>Return Full container at </b></td><td> : ".$rtterminalname."</td></tr>";
			$txt .= "<tr><td><b>PIC </b></td><td> : ".$rtcontact." Tel. ".$rttel."</td></tr>";
			$txt .= "<tr><td><b>Closing date </b></td><td> : ".$closedate."</td></tr>";
			$txt .= "<tr><td><b>Closing Time </b></td><td> : ".$closetime."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>Feeder Vessel & Voyage </b></td><td> : ".$feeder." V. ".$feedervoy."</td></tr>";
			$txt .= "<tr><td><b>Mother Vessel & Voyage </b></td><td> : ".$vessel." V. ".$vesselvoy."</td></tr>";
			$txt .= "<tr><td><b>ETD </b></td><td> : ".$etd."</td></tr>";
			$txt .= "<tr><td><b>ETA </b></td><td> : ".$eta."</td></tr>";
			$txt .= "<tr><td><b>Port of Loading </b></td><td> : ".$port."</td></tr>";
			$txt .= "<tr><td><b>Place of Delivery </b></td><td> : ".$place."</td></tr>";
			$txt .= "<tr><td><b>Transhipment Port </b></td><td> : ".$transshipment."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>Paperless code </b></td><td> : ".$paperless."</td></tr>";
			$txt .= "<tr><td><b>Gross Weight </b></td><td> : ".number_format($grossweight, 2)."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td><b>Truck Agent </b></td><td> : ".$truckagent."</td></tr>";

			$lp = self::getLoadingPlant($so);
			$txt .= "<tr><td valign='top'><b>Loading Plant </b></td><td> : ".$lp."</td></tr>";
			
			$txt .= "<tr><td><b>Remark </b></td><td> : ".$truckremark."</td></tr>";
			$txt .= "<tr><td><br></td></tr>";
			$txt .= "<tr><td colspan=2><b><font color='red'>หมายเหตุ : ตารางการแจ้ง คลังสินค้าที่โหลด และ รอบเวลาเข้าโรงงานจะแจ้งอีกครั้งคะ</font></b></td></tr>";

			$signature = self::getsignatureTrucking($customer);
			
			$txt .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>".$signature[0]['DSG_ADDRESS']."</font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font> </td></tr>";

			$txt .= "</table>";

		return $txt;
	}

	public function getLoadingPlant($so)
	{
		$conn = self::connect();
		$res = Sqlsrv::rows(
			$conn,
			"SELECT SO.DSG_LoadingPlant,DC.NAME
					FROM SALESTABLE SO JOIN 
					     (
					      SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
					      FROM CUSTCONFIRMSALESLINK SL
					      GROUP BY DATAAREAID,ORIGSALESID
					     )SL
					     ON SL.DATAAREAID = SO.DATAAREAID
					     AND SL.ORIGSALESID = SO.SALESID
					     JOIN CUSTCONFIRMSALESLINK CSL
					     ON CSL.DATAAREAID = SO.DATAAREAID
					     AND CSL.ORIGSALESID = SO.SALESID
					     AND CSL.CONFIRMID = SL.CONFIRMID
					     JOIN CUSTCONFIRMTRANS CT
					     ON CT.DATAAREAID = CSL.DATAAREAID
					     AND CT.CONFIRMID = CSL.CONFIRMID
					     AND CT.ORIGSALESID = CSL.ORIGSALESID
					     JOIN INVENTTABLE IT
					     ON IT.ITEMID = CT.ITEMID
					     AND IT.DATAAREAID = 'DV'
					     JOIN INVENTPACKAGINGGROUP PG
					     ON PG.DATAAREAID = 'DV'
					     AND PG.PACKAGINGGROUPID = IT.PACKAGINGGROUPID
					     JOIN INVENTPACKAGINGUNIT PU
					     ON PU.DATAAREAID = 'DV'
					     AND PU.ITEMRELATION = PG.PACKAGINGGROUPID
					     JOIN INVENTPACKAGINGUNITMATERIAL IPM
					     ON IPM.DATAAREAID = 'DV'
					     AND IPM.PACKINGUNITRECID = PU.RECID
					     JOIN SALESLINE SOL
					     ON SOL.DATAAREAID = CT.DATAAREAID
					     AND SOL.SALESID = CT.ORIGSALESID
					     AND SOL.ITEMID = CT.ITEMID
					LEFT JOIN DATAAREA DC ON SO.DSG_LoadingPlant = DC.ID
					WHERE SO.DATAAREAID = 'DSC'
					AND CSL.SALESID = ?
					GROUP BY SO.DSG_LoadingPlant,DC.NAME",[$so]
		);

		if (count($res)>1) {

			$str = '';
			foreach ($res as $data) {

				if ($data['DSG_LoadingPlant']!="") {

				  	if ($data['DSG_LoadingPlant']=='DSR') {
			        	$data['NAME'] = 'SVIZZ-ONE CORPORATION LTD.';
			        	$data['DSG_LoadingPlant'] = 'SVO';
			        }
					$str .= $data['NAME']. " (".$data['DSG_LoadingPlant'].'),<br>';

				}
			}
			return trim($str, ',<br>');

		}else{

			if ($res[0]['DSG_LoadingPlant']!="") {

				if ($res[0]['DSG_LoadingPlant']=='DSR') {
		        	return "SVIZZ-ONE CORPORATION LTD. (SVO)";
		        }else{
					return $res[0]['NAME'].  "(".$res[0]['DSG_LoadingPlant'].")";
				}
			}
			
		}

	}

	public function getsignatureTrucking($customer)
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn, 
			"SELECT TOP 1 * FROM DSG_VendorEmailList E
			LEFT JOIN DSG_EditVendorEmailList S ON E.EMAILID = S.DSG_EmailID 
			WHERE E.DSG_INACTIVE=0 AND E.DSG_SENDTYPE=3 AND E.VENDACCOUNT=? ",[$customer]
		);
	}

	public function update_Trucking($so)
	{
		$conn = self::connect();

		$update = sqlsrv_query(
			$conn,
			"UPDATE SALESTABLE SET DSG_AutoMailStatusVendor=?
			WHERE SALESID=? AND DATAAREAID=?",
			[
				2,$so,'dsc'
			]
		);

		if ($update) {
			return true;
		} else {
			return false;
		}
	}

	public function insert_Trucking($so,$cy,$rtn,$cyterminalname,$rtterminalname,$paperless,$shippingline,$feeder,$feedervoy,$vessel,$vesselvoy,$loadingplant,$closedate,$closetime,$cutoffvgmdate,$cutoffvgmtime,$bookingno,$loadingdate,$primarycontactid)
	{
		$conn = self::connectMormont();

		$insert = sqlsrv_query(
			$conn,
			"INSERT INTO TRUCKING 
			(
				SO,
				DSG_CY,
				DSG_RTN,
				DSG_ATCY,
				DSG_ATRTN,
				DSG_CustPaperlessCode,
				DSG_ShippingLine,
				DSG_Feeder,
				DSG_VoyFeeder,
				DSG_Vessel,
				DSG_VoyVessel,
				DSG_LoadingPlant,
				DSG_ClosingDate,
				DSG_ClosingTime,
				DSG_CutoffVGMDate,
				DSG_CutoffVGMTime,
				DSG_BookingNumber,
				DSG_EDDDate,
				DSG_PrimaryContactID
			) 
			VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
			[
				$so,$cy,$rtn,$cyterminalname,$rtterminalname,$paperless,$shippingline,$feeder,$feedervoy,$vessel,$vesselvoy,$loadingplant,$closedate,$closetime,$cutoffvgmdate,$cutoffvgmtime,$bookingno,$loadingdate,$primarycontactid
			]
		);

		if ($insert) {
			return true;
		} else {
			return false;
		}
	}

	// // ############# TRUCKING Revised #####################
	public function CheckRevised_Trucking()
	{
		$conn = self::connect();
		return Sqlsrv::Rows(
			$conn,
			"SELECT 
					CSL.SALESID [SO]
					,CASE WHEN S.DSG_CY IS NULL THEN ''
						  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
					,CASE WHEN S.DSG_RTN IS NULL THEN ''
						  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
					,S.DSG_ATCY
					,T.DSG_TERMINALNAME
					,T.DSG_CONTACTPERSON
					,T.DSG_TEL
					,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
					,TT.DSG_CONTACTPERSON AS Return_CONTACTPERSON
					,TT.DSG_TEL AS Return_TEL
					,S.DSG_CustPaperlessCode
					,S.DSG_ShippingLineDescription
					,S.DSG_Feeder
					,S.DSG_VoyFeeder
					,S.DSG_Vessel
					,S.DSG_VoyVessel
					,S.DSG_LoadingPlant
					,CASE WHEN S.DSG_ClosingDate IS NULL THEN ''
						  WHEN S.DSG_ClosingDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ClosingDate)+' '+DATENAME(month,S.DSG_ClosingDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ClosingDate)) END [DSG_ClosingDate]
					,S.DSG_ClosingTime
					,CASE WHEN S.DSG_CutoffVGMDate IS NULL THEN ''
						  WHEN S.DSG_CutoffVGMDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CutoffVGMDate)+' '+DATENAME(month,S.DSG_CutoffVGMDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CutoffVGMDate)) END [DSG_CutoffVGMDate]
					,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
						  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
					,CASE WHEN S.DSG_ETADate IS NULL THEN ''
						  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
					,S.DSG_CutoffVGMTime
					,S.DSG_BookingNumber
					,S.DSG_PrimaryContactID
					,S.DSG_PortOfLoadingDesc
					,S.DSG_ToPortDesc
					,S.DSG_Transhipment_Port
					,S.DSG_TruckingRemark
					,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
						  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
					,V.AccountNum
					,V.NAME AS NameTruckingAgent
					,A.DSG_DESCRIPTION AS PrimaryAgent
					,CJJ.DSG_VoucherSeries
					,CJJ.DSG_VOUCHERNO
			FROM SALESTABLE S
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
				  FROM CUSTCONFIRMSALESLINK SL
				  GROUP BY DATAAREAID,ORIGSALESID
				 )SL
				 ON SL.DATAAREAID = S.DATAAREAID
				 AND SL.ORIGSALESID = S.SALESID
				 JOIN CUSTCONFIRMSALESLINK CSL
				 ON CSL.DATAAREAID = S.DATAAREAID
				 AND CSL.ORIGSALESID = S.SALESID
				 AND CSL.CONFIRMID = SL.CONFIRMID
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
				  FROM CustConfirmJour CJ
				  GROUP BY DATAAREAID,SALESID
				 )CJ
				 ON CJ.DATAAREAID = S.DATAAREAID
				 AND CJ.SALESID = S.SALESID
				 JOIN CustConfirmJour CJJ
				 ON CJJ.DATAAREAID = S.DATAAREAID
				 AND CJJ.SALESID = S.SALESID
				 AND CJJ.CONFIRMID = CJ.CONFIRMID
			LEFT JOIN DSG_TerminalTrucking T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
			LEFT JOIN DSG_TerminalTrucking TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
			LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1
			LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			WHERE S.DSG_AutoMailStatusVendor=3 AND S.DATAAREAID = 'dsc'"
		); 
	}

	public function CheckLog_Trucking($so)
	{
		$conn = self::connectMormont();
		return Sqlsrv::Rows(
			$conn,
			"SELECT *
			FROM TRUCKING
			WHERE SO = ?",[$so]
		); 
	}

	public function update_TruckingRevised($DSG_CY,$DSG_RTN,$DSG_TERMINALNAME,$Return_TERMINALNAME,$DSG_CustPaperlessCode,$DSG_ShippingLineDescription,$DSG_Feeder,$DSG_VoyFeeder,$DSG_Vessel,$DSG_VoyVessel,$DSG_LoadingPlant,$DSG_ClosingDate,$closetime,$DSG_CutoffVGMDate,$cutoffVGMtime,$DSG_BookingNumber,$DSG_PrimaryContactID,$DSG_EDDDate,$SO)
	{
		$connmormont = self::connectMormont();
		$conn 		 = self::connect();

		$update = sqlsrv_query(
			$connmormont,
			"UPDATE TRUCKING SET DSG_CY = ?
			      ,DSG_RTN = ?
			      ,DSG_ATCY = ?
			      ,DSG_ATRTN = ?
			      ,DSG_CustPaperlessCode = ?
			      ,DSG_ShippingLine = ?
			      ,DSG_Feeder = ?
			      ,DSG_VoyFeeder = ?
			      ,DSG_Vessel = ?
			      ,DSG_VoyVessel = ?
			      ,DSG_LoadingPlant = ?
			      ,DSG_ClosingDate = ?
			      ,DSG_ClosingTime = ?
			      ,DSG_CutoffVGMDate = ?
			      ,DSG_CutoffVGMTime = ?
			      ,DSG_BookingNumber = ?
			      ,DSG_PrimaryContactID = ?
			      ,DSG_EDDDate = ?
			WHERE SO = ? ",			
			[
				$DSG_CY,
				$DSG_RTN,
				$DSG_TERMINALNAME,
				$Return_TERMINALNAME,
				$DSG_CustPaperlessCode,
				$DSG_ShippingLineDescription,
				$DSG_Feeder,
				$DSG_VoyFeeder,
				$DSG_Vessel,
				$DSG_VoyVessel,
				$DSG_LoadingPlant,
				$DSG_ClosingDate,
				$closetime,
				$DSG_CutoffVGMDate,
				$cutoffVGMtime,
				$DSG_BookingNumber,
				$DSG_PrimaryContactID,
				$DSG_EDDDate,
				$SO
			]
		);

		$updatestatus = sqlsrv_query(
			$conn,
			"UPDATE SALESTABLE SET DSG_AutoMailStatusVendor=?
			WHERE SALESID=? AND DATAAREAID=?",
			[
				4,$SO,'dsc'
			]
		);

		if ($update) {
			return true;
		} else {
			return false;
		}
	}


}