<?php
// my function
require_once 'sqlsrv.php';

use Wattanar\Sqlsrv;

class AutoMail
{
	public function connect()
	{
		// return Sqlsrv::connect('pentos\develop', 'sa', 'c,]\'4^j', 'AX_Customize');
		return Sqlsrv::connect('frey\live', 'sa', 'c,]\'4^j', 'DSL_AX40_SP1_LIVE');
	}

	public function connectCustomize()
	{
		return Sqlsrv::connect('pentos\develop', 'sa', 'c,]\'4^j', 'AX_Customize');
	}

	public function connectMormont()
	{
		return Sqlsrv::connect('192.168.90.30\develop', 'sa', 'c,]\'4^j', 'AUTOMAIL_EXPORT');
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

	public function logs($strTO, $strCC, $strFileLists, $strCustomer, $logsStatus)
	{
		$date = date('Y-m-d H:i:s');
		$conn = self::connectMormont();
		$logs = sqlsrv_query(
			$conn,
			"INSERT INTO LOGS(MAIL_TO, MAIL_CC, CREATEDATE, FILES, CUSTOMER, RESULT)
			VALUES(?, ?, ?, ?, ?, ?)",
			[
				$strTO,
				$strCC,
				$date,
				$strFileLists,
				$strCustomer,
				$logsStatus
			]
		);

		if ($logs) {
			return true;
		} else {
			return false;
		}
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

	public function getAOTSubject(array $fileName = [])
	{
		$files = '';
		foreach ($fileName as $name) {
			$files .= str_replace('DOCS', '', strtoupper(explode('.', $name)[0])) . ', ';
		}
		$files = trim($files, ', ');
		return 'NEW AOTC DOCS : ' . $files;
	}

	public function getDNKSubject(array $fileName = [])
	{
		$files = '';
		foreach ($fileName as $name) {
			$files .= str_replace('DOCS', '', strtoupper(explode('.', $name)[0])) . ', ';
		}
		$files = trim($files, ', ');
		return 'NEW DNK DOCS : ' . $files;
	}

	public function getShippingSubject(array $fileName = [])
	{
		$files = '';
		foreach ($fileName as $name) {
			$files .= explode('.', $name)[0] . ', ';
		}
		$files = trim($files, ', ');
		return 'Shipping document : ' . $files;
	}

	public function getShippingBody()
	{
		$text = '';
		$text .= 'Dear Sir / Madam,<br/><br/>';
		$text .= 'Please see shipping document as the attached. <br/><br/>';
		$text .= 'This e-mail is automatically generated. <br/> The information contained in this message is privileged and intended only for the recipients named. If the reader is not a representative of the intended recipient, any review, dissemination or copying of this message or the information it contains is prohibited. If you have received this message in error, please immediately notify the sender, and delete the original message and attachments.';
		return $text;
	}

	public function getBookingSubject(array $fileName = [])
	{
		$files = '';
		foreach ($fileName as $name) {
			$files .= explode('.', $name)[0] . ', ';
		}
		$files = trim($files, ', ');
		return 'Booking confirmation : ' . str_replace('-BKG', '', $files);
	}

	public function getBookingBody()
	{
		$text = '';
		$text .= 'Dear Sir / Madam,<br/><br/>';
		$text .= 'Please see booking confirmation as the attached. <br/><br/>';
		$text .= 'This e-mail is automatically generated. <br/><br/>';
		// $text .= 'Enclose booking confirmation. Please see the attached for your reference. The information contained in this message is privileged and intended only for the recipients named. If the reader is not a representative of the intended recipient, any review, dissemination or copying of this message or the information it contains is prohibited. If you have received this message in error, please immediately notify the sender, and delete the original message and attachments. Please consider the environment before printing this email.';

		$text .= "Best Regards,";
		return $text;
	}

	public function getDir($path)
	{
		$dir = scandir($path);
		return array_diff($dir, ['.', '..']);
	}

	public function dirToArray($dir) 
	{ 
		$result = array(); 
		$cdir = scandir($dir); 
		foreach ($cdir as $key => $value) { 
		  if (!in_array($value,array(".",".."))) { 
		     if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) { 
					$result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
		     } else { 
		      $result[] = $value; 
		     } 
		  } 
		} 
		return $result; 
	} 

	public function getCustomerCode($file)
	{
		// $customerCode = explode('_', $file)[0];
		$customerCode = explode('-', $file)[0];
		return substr_replace($customerCode, '-', 1, 0);
	}

	public function getCustomerQuantation($file)
	{
		// C0414-QA17-000207.docx
		// or C0414-QA17-000207#2.docx, C0414-QA17-000207#3.docx
		$q = explode('.', $file)[0]; // C0414-QA17-000207
		$_qa = explode('-', $q); // ['C0414', 'QA17', '000207']
		if (count($_qa) < 3) { return 'FORMAT_INCORRECT';}
		$_qa_2 = $_qa[1] . '-' . $_qa[2]; // QA17-000207 or QA17-000207#2
		$_qa_final = explode('#', $_qa_2)[0]; // remove #
		return $_qa_final; // return QA17-000207
	}

	public function mapQuantation($customerCode, $quantation)
	{
		$conn = self::connect();
		return Sqlsrv::hasRows(
			$conn,
			"SELECT Q.custaccount, Q.quotationid, Q.DATAAREAID, Q.CREATEDDATE
			from SalesQuotationTable Q
			where Q.DATAAREAID = 'dsc'
			and Q.quotationid = ?
			and Q.custaccount = ?
			order by Q.CREATEDDATE desc",
			[$quantation, $customerCode]
		);
	}

	public function getCustomerMail($customerCode)
	{
		$conn = self::connect();

		$listsTo = [];
		$listsCC = [];

		$_to = Sqlsrv::rows(
			$conn, 
			'SELECT DSG_Email FROM DSG_CustomerEmailList 
			WHERE DSG_ACCOUNTNUM = ? 
			AND DSG_SENDTYPE = 0 
			AND DSG_INACTIVE = 0
			GROUP BY DSG_Email',
			[
				$customerCode
			]
		);

		$_cc = Sqlsrv::rows(
			$conn, 
			'SELECT DSG_Email FROM DSG_CustomerEmailList 
			WHERE DSG_ACCOUNTNUM = ? 
			AND DSG_SENDTYPE = 1 
			AND DSG_INACTIVE = 0
			GROUP BY DSG_Email',
			[
				$customerCode
			]
		);

		foreach($_to as $t) {
			$listsTo[] = $t['DSG_Email'];
		}

		foreach($_cc as $c) {
			$listsCC[] = $c['DSG_Email'];
		}

		return [
			'to' => $listsTo,
			'cc' => $listsCC
		];
	}

	public function Size($path)
	{
    $bytes = sprintf('%u', filesize($path));
    if ($bytes > 0)
    {
      $unit = intval(log($bytes, 1024));
      $units = array('B', 'KB', 'MB', 'GB');

      if (array_key_exists($unit, $units) === true)
      {
        return sprintf('%.2f %s', $bytes / pow(1024, $unit), $units[$unit]);
      }
    }
    return $bytes;
	}

	public function test($customerCode) 
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn, 
			'SELECT DSG_Email 
			FROM DSG_CustomerEmailList 
			WHERE DSG_ACCOUNTNUM = ? AND DSG_SENDTYPE = 0 AND DSG_INACTIVE = 0',
			[
				$customerCode
			]
		);
	}

	public function getSubjectReportFailed()
	{
		return 'Automail: รายชื่อไฟล์ที่ไม่สามารถส่งหาลูกค้าได้';
	}

	public function getBodyReportFailed($file, $name = '', $note = '')
	{
		$txt = '';
		$txt .= 'รายชื่อไฟล์ที่ ' . $name . ' ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ<br><br>';

		if ($note !== '') {
			$txt .= 'สาเหตุ : ' . $note . '<br>';
		}

		$txt .= '<ul>';

		foreach($file as $f) {
			$txt .= '<li>' . $f . '</li>';
		}
		$txt .= '</ul><br>';
		$txt .= '';
		return $txt;
	}

	public function getShippingDOCSInName($files = [])
	{
		$filesWithDocs = [];
		foreach($files as $file) {
			if (preg_match("/-DOCS/i", $file)) {
				$filesWithDocs[] = $file;
			}
		}
		return $filesWithDocs;
	}

	public function getDirRoot($path)
	{
		$rootDir = self::dirToArray($path);
		
		$files = [];
		
		foreach ($rootDir as $f) {
			if (gettype($f) !== 'array') {
				if ($f !== 'Thumbs.db') {
					$files[] = $f;
				}
			}
		}
		return $files;
	}

	public function getCustomerSubjectCosmo($file)
	{
		// C0414-QA17-000207.docx
		// or C0414-QA17-000207#2.docx, C0414-QA17-000207#3.docx
		$q = explode('.', $file)[0]; // C0414-QA17-000207
		$_qa = explode('-', $q); // ['C0414', 'QA17', '000207']
		if (count($_qa) < 3) { 
			return 'FORMAT_INCORRECT';
		}
		$_qa_2 = $_qa[1] . '-' . $_qa[2]; // QA17-000207 or QA17-000207#2
		$_qa_final = explode('#', $_qa_2)[0]; // remove #
		return $_qa_final; // return QA17-000207
	}

	public function getCamsoBodyData($customerCode, $quantation)
	{
		$conn = self::connect();
		$query = Sqlsrv::rows(
			$conn,
			"SELECT 
			S.CustomerRef, 
			S.SALESIDREF, 
			J.REF_NO, 
			S.DATAAREAID, 
			J.SERIES,
			J.Voucher_No,
			J.LC_No,
			J.REMARKS,
			I.DSG_ISSUEDBANK
			FROM SalesQuotationTable S
			LEFT JOIN InventPickingListJour J ON S.SALESIDREF=J.ORDERID AND S.DATAAREAID=J.DATAAREAID
			LEFT JOIN DSG_LCTable I ON J.LC_NO=I.DSG_LC_NO1 AND J.DATAAREAID=I.DATAAREAID
			WHERE S.QUOTATIONID=?
			AND S.CUSTACCOUNT=?",
			[$quantation, $customerCode]
		);

		if ($query) {
			return $query;
		} else {
			return [];
		}
	}

	public function getCamsoSubject($ref)
	{
		return 'COPY OF EACH OF THE DOCUMENT:CAMSO LOADSTAR (PVT) LTD (PV3541):P/I NO. '.$ref;
	}

	public function getCamsoBody($po, $ref, $inv_no, $lc_no, $remark, $issue)
	{
		$text = '';
		$text .= 'Dear Sir / Madam,<br/><br/>';
		$text .= 'COPY OF EACH OF THE DOCUMENT: CAMSO LOADSTAR (PVT) LTD (PV3541):P/I NO. '.$ref.'<br/>';
		$text .= 'PO # '.$po.'<br/>';
		$text .= '<b>INV.NO.'.$inv_no.' </b><br/><br/>';
		$text .= 'L/C NO. '.$lc_no.'<br/>';
		$text .= 'ISSUING BANK '.$issue.'<br/><br/>';
		
		if ($remark !== '') {
			$text .= 'SHIPPING MARKS AND NOS. AS : '.$remark;
		}else{
			$text .= '';
		}

		return $text;
	}

	// Release

	// Release

	public function getmail_release($company)
	{
		$conn = self::connect();

		if ($company=='SVO') {
			$company='dsr';
		}

		$listsTo = [];
		$listsCC = [];
		$listsBCC = [];

		$_to = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_Email FROM DSG_EmailReleasedoc WHERE DSG_ACTIVE=1 AND DSG_SENDTYPE=0 AND DATAAREAID=?",[$company]
		);

		$_cc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_Email FROM DSG_EmailReleasedoc WHERE DSG_ACTIVE=1 AND DSG_SENDTYPE=1 AND DATAAREAID=?",[$company]
		);

		$_bcc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_Email FROM DSG_EmailReleasedoc WHERE DSG_ACTIVE=1 AND DSG_SENDTYPE=2 AND DATAAREAID=?",[$company]
		);

		foreach($_to as $t) {
			$listsTo[] = $t['DSG_Email'];
		}

		foreach($_cc as $c) {
			$listsCC[] = $c['DSG_Email'];
		}

		foreach($_bcc as $b) {
			$listsBCC[] = $b['DSG_Email'];
		}

		return [
			'to' 		=> $listsTo,
			'cc' 		=> $listsCC,
			'bcc'		=> $listsBCC
		];
	}

	public function getData_Release()
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn,
			"SELECT L.DSG_SALESID,J.OrderAccount,C.NAME,J.Series,J.Voucher_no,
			CASE 
				WHEN L.CREATEDBY = 'auto' THEN 'Auto under Credit Control'
			ELSE U.NAME END [FULLNAME],
			L.DSG_AfterValue,L.createdDate,L.createdTime,L.DSG_PACKINGSLIPID,
			CASE 
				WHEN L.DSG_DATAAREAID = 'dsr' THEN 'SVO'
			ELSE UPPER(L.DSG_DATAAREAID) END [DSG_DATAAREAID] ,J.DSG_EL_by
			FROM  DSG_SalesLog L
			LEFT JOIN CustPackingSlipJour J ON L.DSG_SALESID=J.SALESID AND L.DSG_DATAAREAID=J.DATAAREAID AND L.DSG_PACKINGSLIPID=J.LEDGERVOUCHER
			LEFT JOIN CustTable C ON J.OrderAccount=C.ACCOUNTNUM AND L.DATAAREAID=C.DATAAREAID
			LEFT JOIN USERINFO U ON L.CREATEDBY=U.ID AND U.ENABLE=1
			WHERE L.DSG_SentEmail=1 AND L.DSG_SALESLOGCATEGORY=11 AND L.DSG_DATAAREAID='DSC'"
		); 
	}

	public function update_Release($salesid,$company,$packingslip)
	{
		$conn = self::connect();

		if ($company=='SVO') {
			$company='dsr';
		}

		$logs = sqlsrv_query(
			$conn,
			"UPDATE DSG_SalesLog SET DSG_SENTEMAIL=?
			WHERE DSG_SALESID=? AND  DSG_DATAAREAID=? AND DSG_PACKINGSLIPID=? AND DSG_SENTEMAIL=?",
			[
				0,$salesid,$company,$packingslip,1
			]
		);

		if ($logs) {
			return true;
		} else {
			return false;
		}
	}

	public function getReleaseSubjectConnectFailed()
	{
		return 'Release original document ไม่สามารถส่งได้';
	}

	public function getReleaseBodyConnectFailed()
	{
		$text	=	'';

		$text	.=	'Release original document  '.'<font color="red">'.'ไม่สามารถส่งได้'.'</font>'.'<br/><br/>';
		$text	.=	'สาเหตุ : ไม่สามารถเชื่อมต่อฐานข้อมูล AX ได้';
		return $text;
	}

	public function getReleaseSubject($custaccount,$company,$series,$voucher_no)
	{
		return 'Release original document '.$custaccount.' Invoice No. '.$company.'/'.$series.'/'.$voucher_no;
	}

	public function getReleaseSubjectFailed($custaccount,$company,$series,$voucher_no)
	{
		return 'Release original document '.$custaccount.' Invoice No. '.$company.'/'.$series.'/'.$voucher_no.' ไม่สามารถส่งได้';
	}

	public function getReleaseBodyFailed($custaccount,$company,$series,$voucher_no,$note,$email_To=[])
	{
		$text	=	'';
		$mailto = 	'';
		$mailcc = 	'';
		$mailbcc= 	'';

		$text	=	'Release original document '.$custaccount.' Invoice No. '.$company.'/'.$series.'/'.$voucher_no.'<font color="red">'.' ไม่สามารถส่งได้'.'</font>'.'<br/><br/>';

		if ($note !== '') {

			$findme   = 'connect() failed';
			$pos = strpos($note, $findme);

			if ($pos === false) {
			    $note = $note;
			} else {
			    $note = "ระบบไม่สามารถเชื่อมต่อเมลล์เซิฟเวอร์ได้ เนื่องจากเซิฟเวอร์ E-Mail มีปัญหา";
			}

			$text .= 'สาเหตุ : ' . $note . '<br>';
			// To
			foreach ($email_To['to'] as $name) {
				$mailto .= $name . ', ';

			}
			$mailto = trim($mailto, ', ');
			
			if ($mailto !== '') {
				$text .= 'รายชื่อเมลล์ To : ' . $mailto . '<br>';
			}
			// CC
			foreach ($email_To['cc'] as $name) {
				$mailcc .= $name . ', ';

			}
			$mailcc = trim($mailcc, ', ');
			
			if ($mailcc !== '') {
				$text .= 'รายชื่อเมลล์ CC : ' . $mailcc . '<br>';
			}
			// BCC
			foreach ($email_To['bcc'] as $name) {
				$mailbcc .= $name . ', ';

			}
			$mailbcc = trim($mailbcc, ', ');
			
			if ($mailbcc !== '') {
				$text .= 'รายชื่อเมลล์ BCC : ' . $mailbcc . '<br>';
			}
		}

		return $text;

	}

	public function getReleaseBody($custname,$releasedate,$fullname,$createdate,$createdtime,$elby)
	{	
		$time 		 = $createdtime;
		$create_time = new DateTime("@$time");  
		$create_time = $create_time->format('H:i:s'); 
		
		// $daterelease  =	date_create($releasedate);
		// $release_date = date_format($daterelease,"d/m/Y");
		if ($releasedate=='1/1/1900') {
			$releasedate = '';
		}

		$date 		 =	date_create($createdate);
		$create_date = 	date_format($date,"d/m/Y");

		$text = '';
		$text .= 'Customer name : '.$custname.'<br/><br/>';
		$text .= 'Release doc &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : '.$releasedate.'<br/><br/>';
		$text .= 'Approve By &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : '.$fullname.'<br/><br/>';
		$text .= 'Approve date&nbsp;&nbsp;&nbsp;&nbsp; : '.$create_date.' '.$create_time.'<br/><br/>';
		$text .= 'El by&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : '.$elby.'<br/><br/>';
		return $text;
	}

	// Daily
	public function getDailySubject()
	{
		return 'New booking : DNK / Daily report';
	}

	public function getDailyBody()
	{
		return 'Please see new booking confirmation as attached. This e-mail is automatically generated.';
	}

	public function sendMailDaily($mailTo = [], $mailCC = [], $filename, $subject = '', $body = '', $pathToFile = '', $BCC = [], $sender = '')
	{
		// $sender_mail = 'Khanittha_p@deestone.com';
		if ($sender === '') {
			$sender_mail = 'acharapron_i@deestone.com';
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

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;
		// $mail->AddAttachment($filename);

		if ($pathToFile === '') {
			$mail->AddAttachment($filename);
		} else {
			$mail->AddAttachment($pathToFile . $filename);
		}
		
		if($mail->send()) {
			return ['result' => true, 'message' => 'Message has been sent'];
		} else {
			return ['result' => false, 'message' => $mail->ErrorInfo];
		}
	}

	// Shipment
	public function getShipmentSubject()
	{
		return 'Shipment status AOT / DNK (Daily report)';
	}

	public function getShipmentBody()
	{
		$txt = '';
		$txt .= 'Dear Sir / Madam,<br><br>';
		$txt .= 'Please see shipment status as attached. Highly appreciate you pay attention about shipment that awaiting for release booking.  Actually we have to receive booking confirmation at latest 5 working day before loading date. Please expedite to release booking to load on plan.<br><br>';
		$txt .= 'Best regards,';
		return $txt;
	}

	public function getDataShipment()
	{
		$conn = self::connect();
		$Listmail = [
			'to' => [
				'harit_j@deestone.com'
			],
			'cc' => [
				// 'wattana_r@deestone.com'
				// 'jeamjit_p@deestone.com'
			]
		];
		$datenow = date("Y-m-d");
		// $datenow = '2017-12-02';
		header('Content-Type: text/html; charset=utf-8');
		$data = sqlsrv::rows($conn,"SELECT 
			DATA.PO
			,DATA.CUSTNAME
			,DATA.SHIPTOID
			,DATA.DESCPORT
			,CASE WHEN DATA.COMPANY = 'DSR' THEN 'SVO' ELSE DATA.COMPANY END[COMPANY]
			,DATA.ENQUIRY
			,DATA.QUOTATIONID
			,DATA.SALESID
			,CASE WHEN DATA.AVAILABLEDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.AVAILABLEDATE,105) END AVAILABLEDATE
			,CASE WHEN DATA.EDDDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.EDDDATE,105) END EDDDATE
			,CASE WHEN DATA.ETDDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.ETDDATE,105) END ETDDATE
			,CASE WHEN DATA.ETADATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.ETADATE,105) END ETADATE
			,DATA.FWPORTID
			,ISNULL(AG.DSG_DESCRIPTION,'')[FWPORTDESC]
			,CASE WHEN  DATA.[40STD] = 0 OR ISNULL(DATA.[40STD],0)= 0 THEN null ELSE CAST(DATA.[40STD] AS INT) END [40STD]
			,CASE WHEN DATA.[40HC] = 0 OR ISNULL(DATA.[40HC],0)=0 THEN null ELSE CAST(DATA.[40HC] AS INT) END [40HC]
			,CASE WHEN DATA.PODATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.PODATE,105) END PODATE
			,CASE WHEN DATA.BOOKDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.BOOKDATE,105) END BOOKDATE
			,CASE WHEN DATA.BOOKRECDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.BOOKRECDATE,105) END BOOKRECDATE
			,CASE WHEN DATA.PICONFIRMDATE = '1900-01-01 00:00:00.000' THEN NULL ELSE CONVERT(VARCHAR(10),DATA.PICONFIRMDATE,105) END PICONFIRMDATE
			,CASE WHEN DATA.ALLOCATEPERIOD = '1900-01-01 00:00:00.000' THEN NULL ELSE DATA.ALLOCATEPERIOD END ALLOCATEPERIOD

			,DATA.AMOUNT
			,DATA.ORDERTYPE
			,DATA.ORDERSTATUS
			,DATA.INVOICENO
			FROM
			(
				SELECT 
				CASE 
					WHEN  ET.DATAAREAID = 'DSC' THEN 'DSC'
					ELSE 'DV'
				END DVCOM
				,ET.DSG_CUSTACCOUNT[CUSTACCOUNT] 
				,ET.DSG_CUSTREF[PO]
				,ET.DSG_CUSTNAME[CUSTNAME]
				,ET.DSG_SHIPTOID[SHIPTOID]
				,ET.DSG_TOPORTNAME[DESCPORT]
				,ET.DSG_TARGETCOMPANY[COMPANY]
				,ET.DSG_ENQUIRYID[ENQUIRY]
				,ET.DSG_QUOTATIONID[QUOTATIONID]
				,''[SALESID]
				,ET.DSG_AVAILABLEDATE[AVAILABLEDATE]
				,''[EDDDATE]
				,''[ETDDATE]
				,''[ETADATE]
				,ET.DSG_PRIMARYAGENTID[FWPORTID]
				,ET.DSG_CONTAINER1X40[40STD]
				,ET.DSG_CONTAINER1X40HC[40HC]
				,ET.DSG_CUSTPODATE[PODATE]
				,''[BOOKDATE]
				,''[BOOKRECDATE]
				,''[PICONFIRMDATE]
				--,ET.DSG_ALLOCATEPERIOD[ALLOCATEPERIOD]
				,CASE
					WHEN ET.DSG_ALLOCATEPERIOD != '1900-01-01 00:00:00.000'
				    THEN DATENAME(month,ET.DSG_ALLOCATEPERIOD)+' '+CONVERT(VARCHAR,YEAR(ET.DSG_ALLOCATEPERIOD))
				 END [ALLOCATEPERIOD]
				,0[AMOUNT]
				,'Enquiry'[ORDERTYPE]
				,CASE
					WHEN ET.DSG_ENQUIRYSTATUS = 0 THEN 'open'
					WHEN ET.DSG_ENQUIRYSTATUS = 1 THEN 'transfer' 
					WHEN ET.DSG_ENQUIRYSTATUS = 2 THEN 'cencelled' 
					WHEN ET.DSG_ENQUIRYSTATUS = 3 THEN 'sent'
				END [ORDERSTATUS]
				,''[INVOICENO]
				FROM DSG_ENQUIRYTABLE ET
				WHERE ET.DSG_ENQUIRYSTATUS !=1
				AND ET.DSG_ENQUIRYSTATUS !=2
				AND (ET.DSG_CUSTACCOUNT = 'C-0447' OR ET.DSG_CUSTACCOUNT = 'C-0512') 
				AND ET.DSG_ENTYPE !=2
				-- PI
				UNION

				SELECT 
				CASE 
					WHEN  QT.DATAAREAID = 'DSC' THEN 'DSC'
					ELSE 'DV'
				END DVCOM
				,QT.CUSTACCOUNT[CUSTACCOUNT]
				,QT.CUSTOMERREF[PO]
				,QT.QUOTATIONNAME[CUSTNAME]
				,QT.DSG_SHIPTOID[SHIPTOID]
				,QT.TOPORTNAME[DESCPORT]
				,UPPER(QT.DATAAREAID)[COMPANY]
				,QT.DSG_ENQUIRYID[ENQUIRY]
				,QT.QUOTATIONID[QUOTATIONID]
				,QT.SALESIDREF[SALESID]
				,QT.DSG_AVAILABLESHIP[AVAILABLEDATE]
				,QT.DSG_EDDDATE[EDDDATE]
				,''[ETDDATE]
				,''[ETADATE]
				,QT.DSG_PRIMARYAGENTID[FWPORTID]
				,QT.DSG_CONTAINER1X40[40STD]
				,QT.DSG_CONTAINER1X40HC[40HC]
				,QT.DSG_CUSTPODATE[PODATE]
				,QT.DSG_BOOKEDDATE[BOOKDATE]
				,CASE
					WHEN QT.DSG_ADDBOOKINGRECEIVEDDATE != '1900-01-01 00:00:00.000' THEN QT.DSG_ADDBOOKINGRECEIVEDDATE
					ELSE NULL
				END [BOOKRECDATE]
				,QT.CONFIRMDATE[PICONFIRMDATE]
				,CASE
					WHEN ET.DSG_ALLOCATEPERIOD != '1900-01-01 00:00:00.000'
				    THEN DATENAME(month,ET.DSG_ALLOCATEPERIOD)+' '+CONVERT(VARCHAR,YEAR(ET.DSG_ALLOCATEPERIOD))
				 END [ALLOCATEPERIOD]
				,LA.LINEAMOUNT + ISNULL(VALUE_HEAD,0)[AMOUNT]
				,'PI'[ORDERTYPE]
				,CASE
					WHEN QT.QUOTATIONSTATUS		= 0 THEN 'created' 
					WHEN QT.QUOTATIONSTATUS		= 1 THEN 'sent'
					WHEN QT.QUOTATIONSTATUS		= 2 THEN 'confirmed' 
					WHEN QT.QUOTATIONSTATUS		= 3 THEN 'lost'
					WHEN QT.QUOTATIONSTATUS		= 4 THEN 'cencelled'
				END [ORDERSTATUS]
				,''[INVOICENO]
				FROM SALESQUOTATIONTABLE QT LEFT JOIN DSG_ENQUIRYTABLE ET
											ON ET.DSG_TARGETCOMPANY = QT.DATAAREAID
											AND ET.DSG_ENQUIRYID = QT.DSG_ENQUIRYID
											LEFT JOIN
											(SELECT QL.QUOTATIONID,QL.DATAAREAID,SUM(((QL.SALESPRICE+ISNULL(MT.VALUE,0)+ISNULL(MT1.VALUE,0))*QL.SALESQTY))LINEAMOUNT
											 FROM SALESQUOTATIONLINE QL LEFT JOIN 
																	   (
																		SELECT MT.DATAAREAID,MT.TRANSRECID,SUM(MT.VALUE)VALUE
																		FROM MARKUPTRANS MT
																		WHERE MT.TRANSTABLEID = 1962
																		AND MT.MODULETYPE = 1
																		GROUP BY MT.DATAAREAID,MT.TRANSRECID
																	   )MT
																	   ON MT.DATAAREAID = QL.DATAAREAID
																	   AND MT.TRANSRECID = QL.RECID
																	   LEFT JOIN (SELECT MT.DATAAREAID,MT.TRANSRECID,SUM(MT.VALUE)VALUE
																		FROM DSG_MARKUPTRANS MT
																		WHERE MT.TRANSTABLEID = 1962
																		AND MT.MODULETYPE = 1
																		GROUP BY MT.DATAAREAID,MT.TRANSRECID
																	   )MT1
																	   ON MT1.DATAAREAID = QL.DATAAREAID
																	   AND MT1.TRANSRECID = QL.RECID
												GROUP BY QL.QUOTATIONID,QL.DATAAREAID
											)LA
											ON LA.DATAAREAID = QT.DATAAREAID
											AND LA.QUOTATIONID = QT.QUOTATIONID
											LEFT JOIN 
											   (
												SELECT MT.DATAAREAID,MT.TRANSRECID,SUM(MT.VALUE)VALUE_HEAD
												FROM MARKUPTRANS MT
												WHERE MT.TRANSTABLEID = 1967
												AND MT.MODULETYPE = 1
												GROUP BY MT.DATAAREAID,MT.TRANSRECID
											   )MH
											  ON MH.TRANSRECID = QT.RECID
											  AND MH.DATAAREAID = QT.DATAAREAID
									
											--LEFT JOIN(
											--	SELECT MAX(SL.CREATEDDATE)[CREATEDDATE] ,SL.DSG_QUOTATIONID,SL.DSG_DATAAREAID
											--	FROM DSG_SalesLog SL 
											--	WHERE SL.DSG_SALESLOGCATEGORY = 1
											--	AND SL.DATAAREAID = 'DV'
											--	GROUP BY  SL.DSG_QUOTATIONID,SL.DSG_DATAAREAID
											--)SOL ON SOL.DSG_DATAAREAID = QT.DATAAREAID
											--AND SOL.DSG_QUOTATIONID = QT.QUOTATIONID
										   
											
				WHERE 
				QT.QUOTATIONSTATUS != 2
				AND QT.QUOTATIONSTATUS != 4
				AND (QT.CUSTACCOUNT = 'C-0447' OR QT.CUSTACCOUNT = 'C-0512') 
				AND QT.DSG_QUOTATIONTYPE = 1
				AND QT.DSG_PITYPE !=2
				
				UNION
				--SALESORDER

			SELECT 
				CASE 
					WHEN  SO.DATAAREAID = 'DSC' THEN 'DSC'
					ELSE 'DV'
				END DVCOM
				,SO.CUSTACCOUNT
				,SO.CUSTOMERREF[PO]
				,SO.SALESNAME[CUSTNAME]
				,SO.DSG_SHIPTOID[SHIPTOID]
				,SO.DSG_TOPORTDESC[DESCPORT]
				,UPPER(SO.DATAAREAID)[COMPANY]
				,QT.DSG_ENQUIRYID[ENQUIRY]
				,SO.QUOTATIONID[QUOTATIONID]
				,SO.SALESID[SALESID]
				,SO.DSG_AVAILABLEDATE[AVAILABLEDATE]
				,SO.DSG_EDDDATE[EDDDATE]
				--,SO.DSG_ETDDATE[ETDDATE]
				--,SO.DSG_ETADATE[ETADATE]
				,CASE
					WHEN (CPJ.DSG_ETD IS NOT NULL OR CPJ.DSG_ETD != '1900-01-01 00:00:00.000') AND SO.SALESSTATUS !=1 THEN CPJ.DSG_ETD
					ELSE SO.DSG_ETDDATE
				 END [ETDDATE]
				,CASE
					WHEN (CPJ.DSG_ETA IS NOT NULL OR CPJ.DSG_ETA != '1900-01-01 00:00:00.000') AND SO.SALESSTATUS !=1 THEN CPJ.DSG_ETA
					ELSE SO.DSG_ETADATE
				 END [ETADATE]
				,SO.DSG_PRIMARYAGENTID[FWPORTID]
				,SO.DSG_CONTAINER1X40[40STD]
				,SO.DSG_CONTAINER1X40HC[40HC]
				,CASE WHEN SO.DSG_BOOKEDDATE = '1900-01-01 00:00:00.000' THEN  NULL ELSE SO.DSG_BOOKEDDATE END [BOOKDATE]
				,CASE WHEN QT.DSG_CUSTPODATE = '1900-01-01 00:00:00.000' THEN NULL ELSE QT.DSG_CUSTPODATE END [PODATE]
				--,ISNULL(SOL.CREATEDDATE,'')[BOOKRECDATE]
				,CASE
					WHEN SO.DSG_ADDBOOKINGRECEIVEDDATE != '1900-01-01 00:00:00.000' THEN SO.DSG_ADDBOOKINGRECEIVEDDATE
					ELSE SOL.CREATEDDATE		
				 END[BOOKRECDATE]
				,QT.CONFIRMDATE[PICONFIRMDATE]
				,CASE
					WHEN ET.DSG_ALLOCATEPERIOD != '1900-01-01 00:00:00.000'
				    THEN DATENAME(month,ET.DSG_ALLOCATEPERIOD)+' '+CONVERT(VARCHAR,YEAR(ET.DSG_ALLOCATEPERIOD))
				 END [ALLOCATEPERIOD]
				--,CASE 
				--	WHEN (CPT.LINEAMOUNT+MISCPACK.MARKUPVALUE)> 0 THEN (CPT.LINEAMOUNT+MISCPACK.MARKUPVALUE)
				--	WHEN (BIS_ITEM.BIS_AMOUNT+ch.DSG_AMOUNT) > 0 THEN (BIS_ITEM.BIS_AMOUNT+ch.DSG_AMOUNT)
				--	WHEN CJJ.CONFIRMAMOUNT > 0 THEN CJJ.CONFIRMAMOUNT
				--	ELSE 0
				--END[AMOUNT]
				,CASE WHEN SO.SALESSTATUS = 2  OR SO.SALESSTATUS = 3
					  THEN (CPT.LINEAMOUNT+ISNULL(MISCPACK.MARKUPVALUE,0))
					  WHEN SO.SALESSTATUS = 1 AND (SO.DOCUMENTSTATUS = 3  OR SO.DOCUMENTSTATUS = 4)
					  THEN CJJ.CONFIRMAMOUNT
					  ELSE SL.AMOUNT + ISNULL(M.VALUE,0)
				END [AMOUNT]
				,'Sales order'[ORDERTYPE]
				,CASE 
					WHEN SO.SALESSTATUS = 1 THEN 'Open order'
					WHEN SO.SALESSTATUS = 2 THEN 'Delivered'
					WHEN SO.SALESSTATUS = 3 THEN 'Invoiced'
				END[SALESSSTATUS]
				,CASE 
					WHEN CJJ.DSG_VOUCHERNO > 0 THEN 
						CASE WHEN CJJ.DATAAREAID = 'DSR' 
						THEN 'SVO'+'/'+CONVERT(NVARCHAR,CJJ.DSG_VOUCHERSERIES)+'/'+CONVERT(NVARCHAR,CJJ.DSG_VOUCHERNO)
						ELSE 
						UPPER(QT.DATAAREAID)+'/'+CONVERT(NVARCHAR,CJJ.DSG_VOUCHERSERIES)+'/'+CONVERT(NVARCHAR,CJJ.DSG_VOUCHERNO)
						END
					ELSE ''
				END [INVOICENO]
				FROM SALESTABLE SO LEFT JOIN SALESQUOTATIONTABLE QT
								   ON QT.DATAAREAID = SO.DATAAREAID
								   AND QT.QUOTATIONID = SO.QUOTATIONID
								   LEFT JOIN(
										SELECT MAX(SL.CREATEDDATE)[CREATEDDATE] ,SL.DSG_SALESID,SL.DSG_DATAAREAID
										FROM DSG_SalesLog SL 
										WHERE SL.DSG_SALESLOGCATEGORY = 1
										AND SL.DATAAREAID = 'DV'
										GROUP BY  SL.DSG_SALESID,SL.DSG_DATAAREAID
									)SOL ON SOL.DSG_DATAAREAID = SO.DATAAREAID
									AND SOL.DSG_SALESID = SO.SALESID
								   LEFT JOIN DSG_ENQUIRYTABLE ET
								   ON ET.DSG_TARGETCOMPANY = QT.DATAAREAID
								   AND ET.DSG_ENQUIRYID = QT.DSG_ENQUIRYID
								   LEFT JOIN (
											  SELECT  MAX(CTL.CONFIRMID)CONFIRMID,CTL.ORIGSALESID,CTL.DATAAREAID
											  FROM CUSTCONFIRMSALESLINK CTL 
											  GROUP BY CTL.ORIGSALESID,CTL.DATAAREAID
								   )LASTCOMFRIM ON LASTCOMFRIM.ORIGSALESID= SO.SALESID
								   AND LASTCOMFRIM.DATAAREAID = SO.DATAAREAID
								   LEFT JOIN  CUSTCONFIRMJOUR CJJ ON CJJ.CONFIRMID = LASTCOMFRIM.CONFIRMID
								   AND CJJ.DATAAREAID = LASTCOMFRIM.DATAAREAID 
								   LEFT JOIN
								   (
										SELECT CFT.DATAAREAID,CFJ.CONFIRMID,CFJ.SALESID,CFJ.CONFIRMDATE,SUM(CFT.DSG_Custom_QTY*CTB.DSG_ITEMPRICE)BIS_AMOUNT
										FROM CUSTCONFIRMJOUR  CFJ JOIN CUSTCONFIRMTRANS CFT
										   ON CFT.CONFIRMID = CFJ.CONFIRMID
										   AND CFT.DATAAREAID = CFJ.DATAAREAID
										   AND CFT.SALESID    = CFJ.SALESID
										   AND CFT.CONFIRMDATE= CFJ.CONFIRMDATE
										 JOIN DSG_CUSTOMBISITEM CTB
										 ON CTB.LINENUM = CFT.LINENUM
										 AND CTB.PICKINGLISTID = CFT.CONFIRMID
										 AND CTB.DATAAREAID = CFT.DATAAREAID
									 
									GROUP BY CFT.DATAAREAID,CFJ.CONFIRMID,CFJ.SALESID,CFJ.CONFIRMDATE
									)BIS_ITEM
									ON BIS_ITEM.DATAAREAID = LASTCOMFRIM.DATAAREAID
									AND BIS_ITEM.CONFIRMID = LASTCOMFRIM.CONFIRMID
									AND BIS_ITEM.SALESID = LASTCOMFRIM.ORIGSALESID
									LEFT JOIN 
									( 
										SELECT CH.DATAAREAID,CH.SALESID,CH.PICKINGLISTDATE,CH.PICKINGLISTID ,SUM(CH.DSG_AMOUNT)DSG_AMOUNT
										FROM DSG_CHARGE CH
										GROUP BY CH.DATAAREAID,CH.SALESID,CH.PICKINGLISTDATE,CH.PICKINGLISTID
									)CH
									ON CH.DATAAREAID = BIS_ITEM.DATAAREAID
									AND CH.PICKINGLISTDATE = CJJ.CONFIRMDATE
									AND CH.PICKINGLISTID = BIS_ITEM.CONFIRMID
									LEFT JOIN
									(
										SELECT MAX(CPJ.RECID)[RECID],CPJ.DATAAREAID,CPJ.SALESID
										FROM CUSTPACKINGSLIPJOUR CPJ
										WHERE CPJ.QTY >0
										GROUP BY CPJ.DATAAREAID,CPJ.SALESID
									)LCPJ 
									ON LCPJ.DATAAREAID = SO.DATAAREAID 
									AND LCPJ.SALESID = SO.SALESID
									LEFT JOIN CUSTPACKINGSLIPJOUR CPJ
									ON CPJ.DATAAREAID = LCPJ.DATAAREAID
									AND CPJ.SALESID = LCPJ.SALESID
									AND CPJ.RECID = LCPJ.RECID
									LEFT JOIN
									(SELECT CPJ.PACKINGSLIPID,CPJ.DATAAREAID,CPJ.SALESID,CPJ.DELIVERYDATE,SUM((CPT.DSG_SALESPRICE+ISNULL(MT.MARKUPVALUE,0))*CPT.QTY)LINEAMOUNT
									FROM CUSTPACKINGSLIPJOUR CPJ JOIN CUSTPACKINGSLIPTRANS CPT
																 ON CPT.DATAAREAID = CPJ.DATAAREAID
																 AND CPT.SALESID = CPJ.SALESID
																 AND CPT.PACKINGSLIPID = CPJ.PACKINGSLIPID
																 AND CPT.DELIVERYDATE = CPJ.DELIVERYDATE
																 LEFT JOIN (SELECT MT.DATAAREAID,MT.REFTABLEID,MT.REFRECID,SUM(MT.MARKUPVALUE)MARKUPVALUE
																			FROM DSG_MISCCHARGES MT
																			WHERE MT.REFTABLEID = 72
																			GROUP BY MT.DATAAREAID,MT.REFTABLEID,MT.REFRECID
																			)MT
																 ON MT.DATAAREAID = CPT.DATAAREAID
																 AND MT.REFRECID = CPT.RECID

									GROUP BY CPJ.PACKINGSLIPID,CPJ.DATAAREAID,CPJ.SALESID,CPJ.DELIVERYDATE
									)CPT ON CPT.DATAAREAID  = CPJ.DATAAREAID
									AND CPT.DELIVERYDATE = CPJ.DELIVERYDATE
									AND CPT.PACKINGSLIPID = CPJ.PACKINGSLIPID
									AND CPT.SALESID = CPJ.SALESID
									LEFT JOIN 
									(
										SELECT MISC.REFRECID,MISC.DATAAREAID,SUM(MISC.MARKUPVALUE)MARKUPVALUE
										FROM DSG_MISCCHARGES MISC
										WHERE MISC.REFTABLEID = 71
										GROUP BY MISC.REFRECID,MISC.DATAAREAID
									)MISCPACK
									ON MISCPACK.DATAAREAID = CPJ.DATAAREAID
									AND MISCPACK.REFRECID = CPJ.RECID
									LEFT JOIN 
									(
										SELECT SL.SALESID,SL.DATAAREAID , SUM(SL.AMOUNT)[AMOUNT]
										FROM (
										SELECT SL.SALESID,SL.DATAAREAID,SL.SALESQTY*(SL.SALESPRICE + ISNULL(M.VALUE,0))[AMOUNT]
										FROM SALESLINE SL 
										LEFT JOIN(
										SELECT T.TRANSRECID,T.DATAAREAID,SUM(T.VALUE)[VALUE]
										FROM MARKUPTRANS T 
										WHERE T.TRANSTABLEID = 359
										GROUP BY  T.TRANSRECID,T.DATAAREAID
										)M ON SL.DATAAREAID = M.DATAAREAID
										AND SL.RECID = M.TRANSRECID
										)SL 
										GROUP BY SL.SALESID,SL.DATAAREAID

									)SL
									ON SL.DATAAREAID = SO.DATAAREAID
									AND SL.SALESID = SO.SALESID
									LEFT JOIN
									(
										SELECT T.TRANSRECID,T.DATAAREAID,SUM(T.VALUE)[VALUE]
										FROM MARKUPTRANS T
										WHERE T.TRANSTABLEID = 366
										GROUP BY  T.TRANSRECID,T.DATAAREAID

									)M ON M.DATAAREAID = SO.DATAAREAID
									AND M.TRANSRECID = SO.RECID
									
									
				WHERE 
				(SO.CUSTACCOUNT = 'C-0447' OR SO.CUSTACCOUNT = 'C-0512') 
				AND (
				SO.SALESSTATUS  = 1 
				OR (YEAR(SO.DSG_EDDDATE)= YEAR(GETDATE()) AND SO.SALESSTATUS =2) 
				OR (YEAR(SO.DSG_EDDDATE)= YEAR(GETDATE())  AND SO.SALESSTATUS =3) 
				)
				AND SO.SALESSTATUS != 4
				AND SO.SALESTYPE =3
				AND NOT EXISTS (SELECT * FROM SALESLINE SL 
								WHERE SL.DATAAREAID = SO.DATAAREAID 
								AND SL.SALESID = SL.SALESID 
								AND SL.InterCompanyReturnActionId != '')
				AND  EXISTS (SELECT *
								FROM SALESTABLE ST JOIN SALESLINE SL
								ON ST.DATAAREAID = SL.DATAAREAID
								AND ST.SALESID = SL.SALESID
								WHERE SL.TAXGROUP != 'SAM'
								AND ST.DATAAREAID = SO.DATAAREAID
								AND ST.SALESID = SO.SALESID)

								
			)DATA LEFT JOIN DSG_AGENTTABLE AG
				  ON AG.DATAAREAID = DATA.DVCOM
				  AND AG.DSG_AGENTID = DATA.FWPORTID
		ORDER BY DATA.PO ASC");

		if (count($data) === 0) {
			echo 'no data';
			exit();
		}

		$name = 'Booking confirmation ShipmentPlan report '.date('d M Y');
		$filename = $name.'.csv';
		$path = $filename;

		$list = [
			'PO#',
			'Customer name',
			'Ship To',
			'Destination port',
			'COMPANY',
			'ENQUIRYID',
			'QUOTATIONID',
			'SALESID',
			'Available Ship Date',
			'Loading date',
			'ETD',
			'ETA',
			'Forwarder',
			"1x40'STD",
			"1x40'HC",
			'PO Date',
			'Booking Date',
			'Booking Received Date',
			'PI Confirmed date',
			'Allocate Period',
			'Amount',
			'Oder Type',
			'Oder Status',
			'Invoice no'
		];

		$a[] = $list;

		foreach ($data as $key => $value) {

			$a[] =  [
				$value['PO'],
				$value['CUSTNAME'],
				$value['SHIPTOID'],
				$value['DESCPORT'],
				$value['COMPANY'],
				$value['ENQUIRY'],
				$value['QUOTATIONID'],
				$value['SALESID'],
				$value['AVAILABLEDATE'],
				$value['EDDDATE'],
				$value['ETDDATE'],
				$value['ETADATE'],
				$value['FWPORTDESC'],
				$value['40STD'],
				$value['40HC'],
				$value['PODATE'],
				$value['BOOKDATE'],
				$value['BOOKRECDATE'],
				$value['PICONFIRMDATE'],
				$value['ALLOCATEPERIOD'],
				$value['AMOUNT'],
				$value['ORDERTYPE'],
				$value['ORDERSTATUS'],
				$value['INVOICENO']
			];
		}

		// echo "<pre>".print_r($a,true)."</pre>";
		// exit;
		$fp = fopen($filename, 'w');
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		foreach ($a as $fields) 
		{
		    fputcsv($fp, $fields);
		}
		fclose($fp);

		if(is_file($path))
		{
			$sendmail = self::sendMailDaily(	$Listmail['to'], 
												$Listmail['cc'], 
												$filename,
												self::getShipmentSubject(),
												self::getShipmentBody(),
												'',
												[],
												''
											); 	
			unlink($filename);
			if($sendmail == true)
			{
				echo "SendSuccess";
			}
			else
			{
				echo "SendFailed";
			}
			
		}
		else
		{
			echo "No Path";
		}

	}

	// Export Shipment
	public function getData_ExportShipment()
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn,
			"SELECT	DSG_AMOUNTREQUIRE
					,CUSTACCOUNT
					,NAME
					,SALESID
					,QUOTATIONID
					,DSG_TOPORTID
					,DSG_TOPORTDESC
					,STD20
					,STD40
					,HC40
					,LCL
					,HC45
					,DSG_AVAILABLEDATE
					,DSG_PaymDateRequire
					,DSG_AGENT
					,DSG_RefCreatePurchId 
					,DSG_REQUESTSHIPDATE
					,DSG_NoteAutoMail
					,CurrencyCode
					,CURRENCYCODEISO
					,Payment
					,DSG_TERMGROUPID
					,PaymentType
					,CustomerRef
					,CASE WHEN Lastshipment IS NULL THEN ''
						  WHEN Lastshipment ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,Lastshipment)+' '+DATENAME(month,Lastshipment)+' '+CONVERT(VARCHAR,YEAR(Lastshipment)) END [Lastshipment]
					,CASE WHEN Expirydate IS NULL THEN ''
						  WHEN Expirydate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,Expirydate)+' '+DATENAME(month,Expirydate)+' '+CONVERT(VARCHAR,YEAR(Expirydate)) END [Expirydate]
			FROM (
				SELECT 
					S.CUSTACCOUNT
					,C.NAME
					,S.SALESID
					,S.QUOTATIONID
					,S.DSG_TOPORTID
					,S.DSG_TOPORTDESC
					,CASE 
					WHEN S.DSG_CONTAINER1X20!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X20),0)+' x20'+'''STD'
					ELSE '' END [STD20]
					,CASE 
					WHEN S.DSG_CONTAINER1X40!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40),0)+' x40'+'''STD'
					ELSE '' END [STD40]
					,CASE 
					WHEN S.DSG_CONTAINER1X40HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40HC),0)+' x40'+'''HC'
					ELSE '' END [HC40]
					,CASE 
					WHEN S.DSG_ContainerLCL!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_ContainerLCL),0)+' LCL'
					ELSE '' END [LCL]
					,CASE 
					WHEN S.DSG_CONTAINER1X45HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X45HC),0)+' x45'+'''HC'
					ELSE '' END [HC45]
					,CASE WHEN S.DSG_AVAILABLEDATE IS NULL THEN ''
						  WHEN S.DSG_AVAILABLEDATE ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_AVAILABLEDATE)+' '+DATENAME(month,S.DSG_AVAILABLEDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_AVAILABLEDATE)) END [DSG_AVAILABLEDATE]
					,CASE WHEN S.DSG_PaymDateRequire IS NULL THEN ''
						  WHEN S.DSG_PaymDateRequire ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_PaymDateRequire)+' '+DATENAME(month,S.DSG_PaymDateRequire)+' '+CONVERT(VARCHAR,YEAR(S.DSG_PaymDateRequire)) END [DSG_PaymDateRequire]
					,ISNULL(S.DSG_AMOUNTREQUIRE,'')[DSG_AMOUNTREQUIRE]
					,CASE WHEN S.DSG_PRIMARYAGENTID = '' THEN 'Please advise agent for arrange booking as soon as possible.'
					ELSE A.DSG_DESCRIPTION END [DSG_AGENT]
					,S.DSG_RefCreatePurchId 
					,S.DSG_REQUESTSHIPDATE
					,S.DSG_NoteAutoMail
					,S.CurrencyCode
					,Y.CURRENCYCODEISO
					,S.Payment
					,PT.DSG_TERMGROUPID
					,SUBSTRING(PT.DSG_TERMGROUPID,1,2)[PaymentType]
					,S.CustomerRef
					,DATEADD(day, 20, S.DSG_AVAILABLEDATE) [Lastshipment]
					,DATEADD(day, 30, S.DSG_AVAILABLEDATE) [Expirydate]
				FROM SALESTABLE S 
				LEFT JOIN CUSTTABLE C ON S.CUSTACCOUNT = C. ACCOUNTNUM AND C.DATAAREAID = 'DSC'
				LEFT JOIN DSG_AgentTable A ON S.DSG_PRIMARYAGENTID = A.DSG_AGENTID AND A.DATAAREAID = 'DSC'
				LEFT JOIN CURRENCY Y ON S.CurrencyCode=Y.CURRENCYCODE AND Y.DATAAREAID = 'DSC'
				LEFT JOIN PaymTerm PT ON C.PAYMTERMID=PT.PAYMTERMID AND PT.DATAAREAID='DSC'
				WHERE S.DSG_AutoMailStatus = 1
				AND S.DATAAREAID = 'dsc'
			) Z
			GROUP BY
			Z.DSG_AmountRequire
			,Z.CUSTACCOUNT
			,Z.NAME
			,Z.SALESID
			,Z.QUOTATIONID
			,Z.DSG_TOPORTID
			,Z.DSG_TOPORTDESC
			,Z.STD20
			,Z.STD40
			,Z.HC40
			,Z.LCL
			,Z.HC45
			,Z.DSG_AVAILABLEDATE
			,Z.DSG_PaymDateRequire
			,Z.DSG_AGENT
			,Z.DSG_RefCreatePurchId 
			,Z.DSG_REQUESTSHIPDATE
			,Z.DSG_NoteAutoMail
			,Z.CurrencyCode
			,Z.CURRENCYCODEISO
			,Z.Payment
			,Z.DSG_TERMGROUPID
			,Z.PaymentType
			,Z.CustomerRef
			,Z.Lastshipment
			,Z.Expirydate"
		); 
	}

	public function getmail_ExportShipment($customer)
	{
		$conn = self::connect();

		$listsTo = [];
		$listsCC = [];
		$listsBCC = [];
		$listsSender = [];

		$_to = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_CustomerEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=0 
			AND DSG_ACCOUNTNUM=? AND DATAAREAID = 'dsc'",[$customer]
		);

		$_cc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_CustomerEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=1
			AND DSG_ACCOUNTNUM=?  AND DATAAREAID = 'dsc'",[$customer]
		);

		$_bcc = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_CustomerEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=2
			AND DSG_ACCOUNTNUM=?  AND DATAAREAID = 'dsc'",[$customer]
		);

		$_sender = Sqlsrv::rows(
			$conn, 
			"SELECT DSG_EMAIL FROM DSG_CustomerEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=3
			AND DSG_ACCOUNTNUM=?  AND DATAAREAID = 'dsc'",[$customer]
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

	public function getSubjectExportShipment($customer,$customercode,$quotationid,$po)
	{
		// return "Loading plan : QA ID / ".$customer;
		if ($quotationid!='') {
			$dsc = 'DSC-';
		}else{
			$dsc = '';
		}
		
		$txt = '';
		// $txt .= "[Test] Auto email เพื่อแจ้งแผนการจัดส่งให้ลูกค้าต่างประเทศ Customer : ".$customercode;
		$txt .= "Loading plan : QA ID : ".$dsc.$quotationid." / PO : ".$po." / ".$customer." ";
		return $txt;
	}

	public function getBodyExportShipment($customer_name,$std20,$std40,$hc40,$lcl,$hc45,$quotationid,$po,$salesid,$port,$avaliable,$paymdaterequire,$lastshipment,$expirydate,$currency,$amount,$agent,$requestshipdate,$noted,$paymenttype,$customer)
	{
		if ($quotationid!='') {
			$dsc = 'DSC-';
		}else{
			$dsc = '';
		}

		$txt = '';
		$container = [];
		//foreach ($getdata as $value) {

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

			$txt .= "<table>";

			if ($paymdaterequire!='') {

				$txt .= "<tr><td>Dear Sir/ Madam,</td></tr>";

				if ($paymenttype=='LC') {
					$txt .= "<tr><td colspan=2><br> Please inform available ship date as below information. Please give us draft LC  for checking not latest than ".$paymdaterequire."</td></tr> ";
				}else{
					$txt .= "<tr><td colspan=2><br> Please inform available ship date as below information. Please give us TT copy  for approval not latest than ".$paymdaterequire.".</td></tr> ";
				}

				$txt .= "<tr><td colspan=2>If you have any question please do not hesitate to contact us.</td></tr>";

			}else{

				$txt .= "<tr><td>Dear Sir/ Madam,</td></tr>";
				$txt .= "<tr><td><br></td></tr>";
				$txt .= "<tr><td colspan=2>Please inform available ship date as below information. If you have any question please do not hesitate to contact us.</td></tr>";

			}

			$txt .= "<tr><td><br></td></tr>";

			$txt .= "<tr><td>Customer name </td><td> : ". $customer_name."</td></tr>";
			
			if ($quotationid!='') {
				$txt .= "<tr><td>QA ID </td><td> : ".$dsc. $quotationid."</td></tr>";
			}
			
			if ($po!='') {
				$txt .= "<tr><td>PO </td><td> : ". $po."</td></tr>";
			}

			if ($salesid!='') {
				$txt .= "<tr><td>SO ID </td><td> : ". $salesid."</td></tr>";
			}

			if ($port!='') {
				$txt .= "<tr><td>PORT </td><td> : ". $port."</td></tr>";
			}

			if ($container_str!='') {
				$txt .= "<tr><td>Container </td><td> : ". $container_str."</td></tr>";
			}

			if ($avaliable!='') {
				$txt .= "<tr><td>Available ship date </td><td> : ". $avaliable."</td></tr>";
			}

			// if ($paymdaterequire!='') {
				// $txt .= "<tr><td>Payment date require </td><td> : ". $paymdaterequire."</td></tr>";
			// }

			if ($paymenttype=='LC') {
				$txt .= "<tr><td>Last Shipment </td><td> : ". $lastshipment."</td></tr>";
				$txt .= "<tr><td>Expiry date </td><td> : ". $expirydate."</td></tr>";
			}

			if ($amount!=0) {
				$txt .= "<tr><td>Amount require </td><td> : ".$currency. $amount."</td></tr>";
			}
			$txt .= "<tr><td>Agent </td><td> : ". $agent."</td></tr>";

			if ($requestshipdate!='') {

				if(strpos($requestshipdate, "\n") !== FALSE) {
				  $txt .= "<tr><td>Request ship date </td><td> : ". substr($requestshipdate, 0 ,strpos($requestshipdate, "\n"))."</td></tr>";
				}else{
				  $txt .= "<tr><td>Request ship date </td><td> : ".$requestshipdate." </td></tr>";
				}
				
			}

			if ($noted!='') {
				$noted = nl2br($noted);
				$txt .= "<tr><td valign='top'>Noted </td><td> : ". $noted."</td></tr>";
			}

			$signature = self::getsignatureExportShipment($customer);

			$txt .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>".$signature[0]['DSG_ADDRESS']."</font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr>";
			$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font> </td></tr>";

			$txt .= "</table>";
		//}
		return $txt;
	}

	public function getsignatureExportShipment($customer)
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn, 
			"SELECT TOP 1 DSG_ADDRESS,DSG_PHONE FROM DSG_CustomerEmailList E
			LEFT JOIN DSG_EditCustEmailList S ON E.EMAILID = S.DSG_EMAILID AND S.DATAAREAID = 'dsc'
			WHERE E.DSG_INACTIVE=0 AND E.DSG_SENDTYPE=3 AND E.DSG_ACCOUNTNUM=? AND E.DATAAREAID='dsc'",[$customer]
		);
	}

	public function updateExportShipment($customer_name,$salesid)
	{
		$conn = self::connect();

		$logs = sqlsrv_query(
			$conn,
			"UPDATE SalesTable SET DSG_AutoMailStatus=?
			WHERE CUSTACCOUNT=? AND  SALESID=? AND DSG_AutoMailStatus=?",
			[
				2,$customer_name,$salesid,1
			]
		);

		if ($logs) {
			return true;
		} else {
			return false;
		}
	}

	// Commercial
	public function getCommercialSubject()
	{
		return 'Commercial invoice (Daily report)';
	}

	public function getCommercialBody()
	{
		$txt = '';
		$txt .= 'Dear Sir / Madam,<br><br>';
		$txt .= 'Please see commercial invoice report as the attached. This is auto email generate by system.<br><br>';
		$txt .= 'Best regards,';
		return $txt;
	}

	// Release doc V2
	public function getData_Releasedocv2()
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn,
			"SELECT S.SALESID,S.CUSTACCOUNT,S.DATAAREAID,L.DSG_PACKINGSLIPID,CJ.ORDERACCOUNT,CJ.SERIES,CJ.VOUCHER_NO
			FROM DSG_SALESLOG L
			LEFT JOIN SALESTABLE S
				ON	S.DATAAREAID	= L.DSG_DATAAREAID
				AND S.SALESID		= L.DSG_SALESID
			LEFT JOIN CUSTPACKINGSLIPJOUR CJ 
				ON S.SALESID = CJ.SALESID 
				AND S.DATAAREAID = CJ.DATAAREAID 
				AND L.DSG_PACKINGSLIPID = CJ.PACKINGSLIPID
			WHERE L.DSG_SENTEMAIL = 1
			AND L.DSG_SALESLOGCATEGORY = 1 --ETA
			AND L.DSG_FOLLOWDATE <= CONVERT(DATE, GETDATE())
			AND S.DATAAREAID='DSC'"
		); 
	}

	public function getDataReleasedocv2Customer($custcode)
	{
		$conn = self::connect();
		return Sqlsrv::rows(
			$conn,
			"SELECT  A.ACCOUNTNUM, ISNULL(A.[A/R BALANCE], 0) [AR], ISNULL(B.CONFIRMAMOUNT, 0) [CONFIRMAMOUNT]
			,ISNULL(A.[A/R BALANCE], 0)+ISNULL(B.CONFIRMAMOUNT, 0)[TOTALBALANCE]
			FROM
			(
				SELECT  C.ACCOUNTNUM, 
						SUM(ISNULL(CT.AMOUNTCUR, 0)) [A/R BALANCE]
				FROM CUSTTABLE C 
				LEFT JOIN CUSTTRANS CT
					ON	CT.ACCOUNTNUM	= C.ACCOUNTNUM
					AND CT.DATAAREAID	= C.DATAAREAID
				WHERE C.DATAAREAID	= 'dsc'
				--AND (C.ACCOUNTNUM IN ('C-0412','C-2301') OR C.INVOICEACCOUNT IN ('C-0412','C-2301'))
				AND C.ACCOUNTNUM = '$custcode'
				AND C.DSG_AUTOMAILALLBALANCE = 1
				GROUP BY C.ACCOUNTNUM
			)	A
			LEFT JOIN
			(
				SELECT  SUM(J.CONFIRMAMOUNT - ISNULL(I.INVOICEAMOUNT, 0)) [CONFIRMAMOUNT],
						S.DATAAREAID,
						S.CUSTACCOUNT
				FROM SALESTABLE S
				JOIN (
						SELECT  L.SALESID, L.DATAAREAID
						FROM SALESLINE L
						GROUP BY L.SALESID, L.DATAAREAID
						HAVING	SUM(ISNULL(L.REMAINSALESFINANCIAL, 0)) + SUM(ISNULL(L.REMAINSALESPHYSICAL, 0)) <> 0
					  )	L	ON	L.DATAAREAID	= S.DATAAREAID
								AND L.SALESID		= S.SALESID
				LEFT JOIN (
							SELECT CS.ORIGSALESID, CS.CONFIRMID, CS.CONFIRMDATE, CS.DATAAREAID
							FROM CUSTCONFIRMSALESLINK CS
							JOIN (
									SELECT  CS.DATAAREAID, CS.ORIGSALESID, MAX(CS.RECID) [RECID]
									FROM CUSTCONFIRMSALESLINK CS
									GROUP BY CS.DATAAREAID, CS.ORIGSALESID
								 )	CS2	ON	CS2.DATAAREAID	= CS.DATAAREAID
										AND CS2.ORIGSALESID	= CS.ORIGSALESID
										AND CS2.RECID		= CS.RECID
						  )	CS	ON	CS.ORIGSALESID	= S.SALESID
								AND	CS.DATAAREAID	= S.DATAAREAID
				LEFT JOIN CUSTCONFIRMJOUR J
					ON	J.DATAAREAID	= CS.DATAAREAID
					AND J.CONFIRMID		= CS.CONFIRMID
					AND J.CONFIRMDATE	= CS.CONFIRMDATE
				LEFT JOIN (
							SELECT I.DATAAREAID, I.SALESID, SUM(I.INVOICEAMOUNT)[INVOICEAMOUNT]
							FROM CUSTINVOICEJOUR I
							GROUP BY I.DATAAREAID, I.SALESID
						  ) I	ON	I.DATAAREAID	= S.DATAAREAID
								AND I.SALESID		= S.SALESID
				WHERE S.SALESSTATUS	<> 4
				AND S.SALESTYPE		<> 5
				AND S.DATAAREAID = 'dsc'
				GROUP BY S.DATAAREAID, S.CUSTACCOUNT
			)	B	ON B.CUSTACCOUNT	=	A.ACCOUNTNUM"
		); 
	}

	public function getReleasedocv2Subject($custcode,$series,$voucher)
	{	
		return "Error Release Doc ".$custcode." Invoice No. DSC/".$series."/".$voucher;
	}

	public function getReleasedocv2Body()
	{
		return "A/R balance Over Total balance 50%";
	}

	public function update_Releasedocv2_uncheck($salesid,$company,$packingslip)
	{
		$conn = self::connect();

		if ($company=='SVO') {
			$company='dsr';
		}

		$logs = sqlsrv_query(
			$conn,
			"UPDATE DSG_SalesLog SET DSG_SENTEMAIL=?
			WHERE DSG_SALESID=? AND  DSG_DATAAREAID=? AND DSG_PACKINGSLIPID=? AND DSG_SENTEMAIL=?",
			[
				0,$salesid,$company,$packingslip,1
			]
		);

		if ($logs) {
			return true;
		} else {
			return false;
		}
	}

	public function update_Releasedocv2_Date($salesid,$company,$packingslip)
	{
		$conn = self::connect();
		date_default_timezone_set("Asia/Bangkok");
		$gettime = date("h:i:sa");
		$nowtime = strtotime("1970-01-01 $gettime UTC");
		// echo $nowtime;

		if ($company=='SVO') {
			$company='dsr';
		}

		$update_date_releasedoc = sqlsrv_query(
			$conn,
			"UPDATE CUSTPACKINGSLIPJOUR SET DSG_ReleaseDocDate=GETDATE()
			WHERE SALESID=? AND  DATAAREAID=? AND PACKINGSLIPID=?",
			[
				$salesid,$company,$packingslip
			]
		);

		$update_saleslog = sqlsrv_query(
			$conn,
			"UPDATE L
			SET L.DSG_SENTEMAIL = 0
			FROM DSG_SALESLOG L
			WHERE L.DSG_DATAAREAID = ?
			AND L.DSG_PACKINGSLIPID = ?
			AND L.DSG_SALESID = ?
			AND L.DSG_SALESLOGCATEGORY = 10",
			[
				$company,$packingslip,$salesid
			]
		);

		if ($update_saleslog) {

			$sequence = Sqlsrv::rows(
				$conn,
				"SELECT MAX(NEXTVAL)
				FROM SYSTEMSEQUENCES
				INNER JOIN SQLDICTIONARY
				ON SQLDICTIONARY.FIELDID = 0
				AND SQLDICTIONARY.name = 'DSG_SALESLOG'
				AND SQLDICTIONARY.TABLEID = SYSTEMSEQUENCES.TABID"
			);
			$number_sequence 	= $sequence[0][0];
			$next_sequence 		= $sequence[0][0]+1;
			
			$insert = sqlsrv_query(
				$conn,
				"INSERT INTO DSG_SALESLOG
				(		
					DSG_SALESID
           			,DSG_SALESLOGCATEGORY
           			,DSG_BEFOREVALUE
           			,DSG_AFTERVALUE
           			,DSG_DATAAREAID
           			,DSG_ENQUIRYID
           			,DSG_QUOTATIONID
           			,DSG_PACKINGSLIPID
           			,CREATEDDATE
           			,CREATEDTIME
           			,CREATEDBY
           			,DATAAREAID
           			,RECVERSION
           			,RECID
           			,DSG_SENTEMAIL
           		)
				SELECT  P.SALESID,
						11,
						'',
						convert(varchar, GETDATE(), 103),
						P.DATAAREAID,
						'',
						'',
						P.PACKINGSLIPID,
						GETDATE(),
						?,
						'auto',
						'dsc',
						'1',
						?,
						'1'
				FROM CUSTPACKINGSLIPJOUR P
				WHERE  P.SALESID= ?
				AND P.DATAAREAID= ?
				AND P.PACKINGSLIPID = ? ",[$nowtime,$number_sequence,$salesid,$company,$packingslip]
			);

			if ($insert) {

				$update_sequence = sqlsrv_query(
					$conn,
					"UPDATE SYSTEMSEQUENCES
					SET NEXTVAL = ?
					FROM SYSTEMSEQUENCES
					INNER JOIN SQLDICTIONARY
					ON SQLDICTIONARY.FIELDID = 0
					AND SQLDICTIONARY.name = 'DSG_SALESLOG'
					AND SQLDICTIONARY.TABLEID = SYSTEMSEQUENCES.TABID",[$next_sequence]
				);

				return "Insert DSG_SALESLOG Successful ! => ".$salesid."<br>";

			}else{
				return "Insert DSG_SALESLOG Failed ! <br>";
			}

		} else {
			return "Update Saleslog Failed ! <br>";
		}
	}

	// ############# TRUCKING #####################
	// public function getData_Trucking()
	// {
	// 	$conn = self::connect();
	// 	return Sqlsrv::rows(
	// 		$conn,
	// 		"SELECT 
	// 				GETDATE() AS DATENOW
	// 				,S.SALESID
	// 				,CASE WHEN S.DATAAREAID = 'DSR' THEN 'SVO' 
	// 				ELSE UPPER(S.DATAAREAID) 
	// 				END + '/' + CONVERT(NVARCHAR,INVP.SERIES) + '/' + CONVERT(NVARCHAR,INVP.VOUCHER_NO)[VOUCHER_NO]
	// 				,S.DSG_TruckingAgent
	// 				,V.AccountNum
	// 				,CASE 
	// 				WHEN S.DSG_CONTAINER1X20!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X20),0)+' x20'+'''STD'
	// 				ELSE '' END [STD20]
	// 				,CASE 
	// 				WHEN S.DSG_CONTAINER1X40!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40),0)+' x40'+'''STD'
	// 				ELSE '' END [STD40]
	// 				,CASE 
	// 				WHEN S.DSG_CONTAINER1X40HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40HC),0)+' x40'+'''HC'
	// 				ELSE '' END [HC40]
	// 				,CASE 
	// 				WHEN S.DSG_ContainerLCL!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_ContainerLCL),0)+' LCL'
	// 				ELSE '' END [LCL]
	// 				,CASE 
	// 				WHEN S.DSG_CONTAINER1X45HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X45HC),0)+' x45'+'''HC'
	// 				ELSE '' END [HC45]
	// 				,S.DSG_ShippingLineDescription
	// 				,S.DSG_PrimaryAgentId
	// 				,A.DSG_DESCRIPTION AS PrimaryAgent
	// 				-- ,P.DSG_CONTACTPERSON
	// 				,S.DSG_BookingNumber
	// 				,CASE WHEN S.DSG_CY IS NULL THEN ''
	// 					  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
	// 				,CASE WHEN S.DSG_RTN IS NULL THEN ''
	// 					  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
	// 				,CASE WHEN S.DSG_ClosingDate IS NULL THEN ''
	// 					  WHEN S.DSG_ClosingDate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ClosingDate)+' '+DATENAME(month,S.DSG_ClosingDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ClosingDate)) END [DSG_ClosingDate]
	// 				,S.DSG_ClosingTime
	// 				,S.DSG_Feeder
	// 				,S.DSG_VoyFeeder
	// 				,S.DSG_Vessel
	// 				,S.DSG_VoyVessel
	// 				,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
	// 					  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
	// 				,CASE WHEN S.DSG_ETADate IS NULL THEN ''
	// 					  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
	// 				,S.DSG_PortOfLoadingDesc
	// 				,S.DSG_ToPortDesc
	// 				,S.DSG_Transhipment_Port
	// 				,S.DSG_CustPaperlessCode
	// 				,S.DSG_LoadingPlant
	// 				,P.NAME AS DSG_LoadingPlant_Name
	// 				,S.DSG_ATCY
	// 				,T.DSG_TERMINALNAME
	// 				,T.DSG_CONTACTPERSON
	// 				,T.DSG_TEL
	// 				,S.DSG_ATRTN
	// 				,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
	// 				,TT.DSG_CONTACTPERSON AS Return_CONTACTPERSON
	// 				,TT.DSG_TEL AS Return_TEL
	// 				,CJJ.DSG_TOTALGROSSWEIGHT
	// 				,CJJ.DSG_VoucherSeries
	// 				,CJJ.DSG_VOUCHERNO
	// 				,V.NAME AS NameTruckingAgent
	// 				,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
	// 					  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
	// 				,S.DSG_TruckingRemark
	// 		FROM SALESTABLE S 
	// 		LEFT JOIN INVENTPICKINGLISTJOUR INVP ON INVP.ORDERID = S.SALESID 
	// 		AND INVP.DATAAREAID = S.DATAAREAID
	// 		AND INVP.NOYESID_DISABLED=0
	// 		LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1
	// 		AND V.DATAAREAID = 'dsc'
	// 		-- LEFT JOIN DSG_ContactPersonTable P ON S.DSG_PrimaryAgentId = P.DSG_AGENTID AND  P.DATAAREAID = 'dsc'
	// 		LEFT JOIN DSG_TerminalTrucking T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
	// 		LEFT JOIN DSG_TerminalTrucking TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
	// 		LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
	// 		LEFT JOIN DATAAREA P ON S.DSG_LoadingPlant = P.ID  
	// 		JOIN 
	// 			 (
	// 			  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
	// 			  FROM CustConfirmJour CJ
	// 			  GROUP BY DATAAREAID,SALESID
	// 			 )CJ
	// 			 ON CJ.DATAAREAID = S.DATAAREAID
	// 			 AND CJ.SALESID = S.SALESID
	// 			 JOIN CustConfirmJour CJJ
	// 			 ON CJJ.DATAAREAID = S.DATAAREAID
	// 			 AND CJJ.SALESID = S.SALESID
	// 			 AND CJJ.CONFIRMID = CJ.CONFIRMID
	// 		WHERE S.DSG_AutoMailStatusVendor=1 
	// 		AND S.DATAAREAID = 'dsc'"

	// 	); 
	// }

	// public function update_Trucking($so)
	// {
	// 	$conn = self::connect();

	// 	$update = sqlsrv_query(
	// 		$conn,
	// 		"UPDATE SALESTABLE SET DSG_AutoMailStatusVendor=?
	// 		WHERE SALESID=? AND  DATAAREAID=? AND DSG_AutoMailStatusVendor=?",
	// 		[
	// 			2,$so,'dsc',1
	// 		]
	// 	);

	// 	if ($update) {
	// 		return true;
	// 	} else {
	// 		return false;
	// 	}
	// }

	// public function getmail_TruckingShipment($customer)
	// {
	// 	$conn = self::connect();

	// 	$listsTo = [];
	// 	$listsCC = [];
	// 	$listsBCC = [];
	// 	$listsSender = [];

	// 	$_to = Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=0 
	// 		AND VENDACCOUNT=?",[$customer]
	// 	);

	// 	$_cc = Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=1
	// 		AND VENDACCOUNT=?",[$customer]
	// 	);

	// 	$_bcc = Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=2
	// 		AND VENDACCOUNT=?",[$customer]
	// 	);

	// 	$_sender = Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT DSG_EMAIL FROM DSG_VendorEmailList WHERE DSG_INACTIVE=0 AND DSG_SENDTYPE=3
	// 		AND VENDACCOUNT=?",[$customer]
	// 	);

	// 	foreach($_to as $t) {
	// 		$listsTo[] = $t['DSG_EMAIL'];
	// 	}

	// 	foreach($_cc as $c) {
	// 		$listsCC[] = $c['DSG_EMAIL'];
	// 	}

	// 	foreach($_bcc as $b) {
	// 		$listsBCC[] = $b['DSG_EMAIL'];
	// 	}

	// 	foreach($_sender as $b) {
	// 		$listsSender[] = $b['DSG_EMAIL'];
	// 	}

	// 	return [
	// 		'to' 		=> $listsTo,
	// 		'cc' 		=> $listsCC,
	// 		'bcc'		=> $listsBCC,
	// 		'sender'	=> $listsSender
	// 	];
	// }

	// public function getGrossweight_Trucking($so)
	// {
	// 	$conn = self::connect($so);

	// 	$grossweight = Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT SUM(ROUND((ISNULL(CT.QTY/PU.FACTOR,0)*IPM.PACKINGUNITWEIGHT)+(SOL.DSG_ITEMNETWEIGHT*CT.QTY/1000),2))TOTALGROSSWEIGHT
	// 				FROM SALESTABLE SO JOIN 
	// 				     (
	// 				      SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
	// 				      FROM CUSTCONFIRMSALESLINK SL
	// 				      GROUP BY DATAAREAID,ORIGSALESID
	// 				     )SL
	// 				     ON SL.DATAAREAID = SO.DATAAREAID
	// 				     AND SL.ORIGSALESID = SO.SALESID
	// 				     JOIN CUSTCONFIRMSALESLINK CSL
	// 				     ON CSL.DATAAREAID = SO.DATAAREAID
	// 				     AND CSL.ORIGSALESID = SO.SALESID
	// 				     AND CSL.CONFIRMID = SL.CONFIRMID
	// 				     JOIN CUSTCONFIRMTRANS CT
	// 				     ON CT.DATAAREAID = CSL.DATAAREAID
	// 				     AND CT.CONFIRMID = CSL.CONFIRMID
	// 				     AND CT.ORIGSALESID = CSL.ORIGSALESID
	// 				     JOIN INVENTTABLE IT
	// 				     ON IT.ITEMID = CT.ITEMID
	// 				     AND IT.DATAAREAID = 'DV'
	// 				     JOIN INVENTPACKAGINGGROUP PG
	// 				     ON PG.DATAAREAID = 'DV'
	// 				     AND PG.PACKAGINGGROUPID = IT.PACKAGINGGROUPID
	// 				     JOIN INVENTPACKAGINGUNIT PU
	// 				     ON PU.DATAAREAID = 'DV'
	// 				     AND PU.ITEMRELATION = PG.PACKAGINGGROUPID
	// 				     JOIN INVENTPACKAGINGUNITMATERIAL IPM
	// 				     ON IPM.DATAAREAID = 'DV'
	// 				     AND IPM.PACKINGUNITRECID = PU.RECID
	// 				     JOIN SALESLINE SOL
	// 				     ON SOL.DATAAREAID = CT.DATAAREAID
	// 				     AND SOL.SALESID = CT.ORIGSALESID
	// 				     AND SOL.ITEMID = CT.ITEMID
					     
	// 				WHERE SO.DATAAREAID = 'DSC'
	// 				AND SO.SALESID = ? ",[$so]
	// 	);

	// 	return $grossweight;
	// }

	// public function getSubject_Trucking($invoiceno){
	// 	$txt = '';
	// 	$txt .= "Deestone Corp. : ใบสั่งงานหัวลาก - ".$invoiceno;
	// 	return $txt;
	// }

	// public function getBody_Trucking($datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark)
	// {
	// 	$txt = '';
	// 	$container = [];

	// 		if ($std20!='') {
	//         	array_push($container, $std20);
	//         }
	//         if($std40!=''){
	//         	array_push($container, $std40);
	//         }
	//         if($hc40!=''){
	//         	array_push($container, $hc40);
	//         }
	//         if($lcl!=''){
	//         	array_push($container, $lcl);
	//         }
	//         if($hc45!=''){
	//         	array_push($container, $hc45);
	//         }

	//         $container_str = (implode(",",$container));

	//         if ($closetime=='00:00') {
	//         	$closetime = '24:00';
	//         }
	        
	//         if ($loadingplant=='DSR') {
	//         	$loadingplantname = 'SVIZZ-ONE CORPORATION LTD.';
	//         	$loadingplant = 'SVO';
	//         }
	// 		$txt .= "<table>";
	// 		$txt .= "<tr><td>เรียนผู้ให้บริการหัวลาก</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td colspan=2>แจ้งข้อมูล ลาก/คืน ตู้ ตามรายละเอียดด้านล่าง หากไม่สามารถ ลาก/คืน ตู้ได้ตามเวลาที่กำหนดรบกวนแจ้งกลับคะ</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>บริษัท ดีสโตน คอร์ปอเรชั่น จำกัด </b></td></tr>";
	// 		$txt .= "<tr><td><b>Trucking Report </b></td></tr>";
	// 		$txt .= "<tr><td><b>Date </b></td><td> : ". $datenow."</td></tr>";
	// 		$txt .= "<tr><td><b>Invoice No. </b></td><td> : ". $invoiceno."</td></tr>";
	// 		$txt .= "<tr><td><b>Volumn </b></td><td> : ". $container_str."</td></tr>";
	// 		$txt .= "<tr><td><b>Agent </b></td><td> : ". $shippingline."</td></tr>";
	// 		$txt .= "<tr><td><b>Forwarder Name </b></td><td> : ". $forwarder."</td></tr>";
	// 		$txt .= "<tr><td><b>Booking No. </b></td><td> : ". $bookingno."</td></tr>";
	// 		$txt .= "<tr><td><b>CY date(วันที่ลากตู้เปล่า) </b></td><td> : ". $cy."</td></tr>";
	// 		$txt .= "<tr><td><b>CY Epmty container at </b></td><td> : ".$cyterminalname."</td></tr>";
	// 		$txt .= "<tr><td><b>PIC </b></td><td> : ".$cycontact." Tel. ".$cytel."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>Loading date  (วันที่ตู้เข้าโรงงาน)</b></td><td> : ".$loadingdate."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>Return date(วันที่คืนตู้หนัก) </b></td><td> : ".$rtn."</td></tr>";
	// 		$txt .= "<tr><td><b>Return Full container at </b></td><td> : ".$rtterminalname."</td></tr>";
	// 		$txt .= "<tr><td><b>PIC </b></td><td> : ".$rtcontact." Tel. ".$rttel."</td></tr>";
	// 		$txt .= "<tr><td><b>Closing date </b></td><td> : ".$closedate."</td></tr>";
	// 		$txt .= "<tr><td><b>Closing Time </b></td><td> : ".$closetime."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>Feeder Vessel & Voyage </b></td><td> : ".$feeder." V. ".$feedervoy."</td></tr>";
	// 		$txt .= "<tr><td><b>Mother Vesse & Voyage </b></td><td> : ".$vessel." V. ".$vesselvoy."</td></tr>";
	// 		$txt .= "<tr><td><b>ETD </b></td><td> : ".$etd."</td></tr>";
	// 		$txt .= "<tr><td><b>ETA </b></td><td> : ".$eta."</td></tr>";
	// 		$txt .= "<tr><td><b>Port of Loading </b></td><td> : ".$port."</td></tr>";
	// 		$txt .= "<tr><td><b>Place of Delivery </b></td><td> : ".$place."</td></tr>";
	// 		$txt .= "<tr><td><b>Transhipment Port </b></td><td> : ".$transshipment."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>Paperless code </b></td><td> : ".$paperless."</td></tr>";
	// 		$txt .= "<tr><td><b>Gross Weight </b></td><td> : ".number_format($grossweight, 2)."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td><b>Truck Agent </b></td><td> : ".$truckagent."</td></tr>";
	// 		$txt .= "<tr><td><b>Loading Plant </b></td><td> : ".$loadingplantname.' ('.$loadingplant.")</td></tr>";
	// 		$txt .= "<tr><td><b>Remark </b></td><td> : ".$truckremark."</td></tr>";
	// 		$txt .= "<tr><td><br></td></tr>";
	// 		$txt .= "<tr><td colspan=2><b><font color='red'>หมายเหตุ : ตารางการแจ้ง คลังสินค้าที่โหลด และ รอบเวลาเข้าโรงงานจะแจ้งอีกครั้งคะ</font></b></td></tr>";

	// 		$signature = self::getsignatureTrucking($customer);
			
	// 		$txt .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr>";
	// 		$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>".$signature[0]['DSG_ADDRESS']."</font> </td></tr>";
	// 		$txt .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr>";
	// 		$txt .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font> </td></tr>";

	// 		$txt .= "</table>";

	// 	return $txt;
	// }

	// public function getsignatureTrucking($customer)
	// {
	// 	$conn = self::connect();
	// 	return Sqlsrv::rows(
	// 		$conn, 
	// 		"SELECT TOP 1 * FROM DSG_VendorEmailList E
	// 		LEFT JOIN DSG_EditVendorEmailList S ON E.EMAILID = S.DSG_EmailID 
	// 		WHERE E.DSG_INACTIVE=0 AND E.DSG_SENDTYPE=3 AND E.VENDACCOUNT=? ",[$customer]
	// 	);
	// }

	// ############# TRUCKING Combine #####################
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
					,DP.DESCRIPTION[DischargePort]
			FROM SALESTABLE S 
			LEFT JOIN INVENTPICKINGLISTJOUR INVP ON INVP.ORDERID = S.SALESID 
			AND INVP.DATAAREAID = S.DATAAREAID
			AND INVP.NOYESID_DISABLED=0
			LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1 AND V.DATAAREAID='dsc'
			LEFT JOIN DSG_TerminalTrucking T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
			LEFT JOIN DSG_TerminalTrucking TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
			LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			LEFT JOIN DATAAREA P ON S.DSG_LoadingPlant = P.ID  
			LEFT JOIN DSG_DischargePorts DP ON S.DSG_DischargePort=DP.DSG_DISCHARGEPORT AND DP.DATAAREAID='dsc'
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

	public function getSubject_Trucking($invoiceno){
		$txt = '';
		$txt .= "Deestone Corp. : ใบสั่งงานหัวลาก - ".$invoiceno;
		return $txt;
	}

	public function getBody_Trucking($so,$datenow,$invoiceno,$std20,$std40,$hc40,$lcl,$hc45,$shippingline,$forwarder,$bookingno,$cy,$rtn,$closedate,$closetime,$feeder,$feedervoy,$vessel,$vesselvoy,$etd,$eta,$port,$place,$transshipment,$loadingplant,$loadingplantname,$loadingdate,$paperless,$cyterminalid,$cyterminalname,$cycontact,$cytel,$rtterminalid,$rtterminalname,$rtcontact,$rttel,$grossweight,$truckagent,$customer,$truckremark,$dischargeport)
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
			
			if ($feedervoy!="") {
				$v = " V. ";
			}else{
				$v = " ";
			}

			if ($vesselvoy!="") {
				$vv = " V. ";
			}else{
				$vv = " ";
			}

			$txt .= "<tr><td><b>Feeder Vessel & Voyage </b></td><td> : ".$feeder.$v.$feedervoy."</td></tr>";
			$txt .= "<tr><td><b>Mother Vessel & Voyage </b></td><td> : ".$vessel.$vv.$vesselvoy."</td></tr>";
			$txt .= "<tr><td><b>ETD </b></td><td> : ".$etd."</td></tr>";
			$txt .= "<tr><td><b>ETA </b></td><td> : ".$eta."</td></tr>";
			$txt .= "<tr><td><b>Port of Loading </b></td><td> : ".$port."</td></tr>";
			$txt .= "<tr><td><b>Place of Delivery </b></td><td> : ".$place."</td></tr>";
			$txt .= "<tr><td><b>Transhipment Port </b></td><td> : ".$transshipment."</td></tr>";
			if ($dischargeport=="") {
				$dischargeport = "-";
			}
			$txt .= "<tr><td><b>Port of discharge </b></td><td> : ".$dischargeport."</td></tr>";
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

	public function insert_TruckingLog($so,$closetime,$grossweight,$std20_,$std40_,$hc40_,$lcl_,$hc45_)
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
				DSG_PrimaryContactID,
		      	PrimaryAgent,
				InvoiceNo,
		      	PIC1,
		      	PIC2,
		      	DSG_ETDDate,
		      	DSG_ETADate,
		      	DSG_PortOfLoadingDesc,
		      	DSG_ToPortDesc,
		      	DSG_Transhipment_Port,
		      	grossweight,
		      	NameTruckingAgent,
		      	DSG_TruckingRemark,
		      	DSG_DischargePort,
		      	SentDate,
		      	STD20,
		        STD40,
		        HC40,
		        HC45,
		        LCL
			) 
				SELECT 
					S.SALESID
					,CASE WHEN S.DSG_CY IS NULL THEN ''
						  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
					,CASE WHEN S.DSG_RTN IS NULL THEN ''
						  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
					,T.DSG_TERMINALNAME
					,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
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
					,'$closetime'
					,CASE WHEN S.DSG_CutoffVGMDate IS NULL THEN ''
						  WHEN S.DSG_CutoffVGMDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CutoffVGMDate)+' '+DATENAME(month,S.DSG_CutoffVGMDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CutoffVGMDate)) END [DSG_CutoffVGMDate]
					,DSG_CutoffVGMTime
					,S.DSG_BookingNumber
					,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
						  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
					,S.DSG_PrimaryContactID
					,A.DSG_DESCRIPTION AS PrimaryAgent
					,'DSC/'+CONVERT(VARCHAR,(CJJ.DSG_VoucherSeries)) +'/'+CONVERT(VARCHAR,(CJJ.DSG_VOUCHERNO))
					,T.DSG_CONTACTPERSON+' Tel. '+T.DSG_TEL
					,TT.DSG_CONTACTPERSON+' Tel. '+TT.DSG_TEL
					,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
						  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
					,CASE WHEN S.DSG_ETADate IS NULL THEN ''
						  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
					,S.DSG_PortOfLoadingDesc
					,S.DSG_ToPortDesc
					,S.DSG_Transhipment_Port
					,'$grossweight'
					,V.NAME AS NameTruckingAgent
					,S.DSG_TruckingRemark
					,DP.DESCRIPTION[DischargePort]
					,GETDATE()
					,'$std20_'
					,'$std40_'
					,'$hc40_'
					,'$hc45_'
					,'$lcl_'
					
			FROM [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[SALESTABLE] S 
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[INVENTPICKINGLISTJOUR] INVP ON INVP.ORDERID = S.SALESID 
			AND INVP.DATAAREAID = S.DATAAREAID
			AND INVP.NOYESID_DISABLED=0
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[VendTable] V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1 AND V.DATAAREAID='dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_TerminalTrucking] T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_TerminalTrucking] TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_AGENTTABLE] A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DATAAREA] P ON S.DSG_LoadingPlant = P.ID 
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_DischargePorts] DP ON S.DSG_DischargePort=DP.DSG_DISCHARGEPORT AND DP.DATAAREAID='dsc'
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
				  FROM [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[CustConfirmJour] CJ
				  GROUP BY DATAAREAID,SALESID
				 )CJ
				 ON CJ.DATAAREAID = S.DATAAREAID
				 AND CJ.SALESID = S.SALESID
				 JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[CustConfirmJour] CJJ
				 ON CJJ.DATAAREAID = S.DATAAREAID
				 AND CJJ.SALESID = S.SALESID
				 AND CJJ.CONFIRMID = CJ.CONFIRMID
			WHERE S.SALESID =?
			AND S.DATAAREAID = 'dsc' ",[$so]
		);

		if ($insert) {
			return true;
		} else {
			return false;
		}
	}

	public function insert_Trucking($so,$cy,$rtn,$cyterminalname,$rtterminalname,$paperless,$shippingline,$feeder,$feedervoy,$vessel,$vesselvoy,$loadingplant,$closedate,$closetime,$cutoffvgmdate,$cutoffvgmtime,$bookingno,$loadingdate,$primarycontactid)
	{
		$conn = self::connectMormont();
		$pic1 = $cycontact." Tel. ".$cytel;
		$pic2 = $rtcontact." Tel. ".$rttel;
		$grossweight = number_format($grossweight, 2);
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
		      	-- PrimaryAgent,
		      	-- PIC1,
		      	-- PIC2,
		      	-- DSG_ETDDate,
		      	-- DSG_ETADate,
		      	-- DSG_PortOfLoadingDesc,
		      	-- DSG_ToPortDesc,
		      	-- DSG_Transhipment_Port,
		      	-- grossweight,
		      	-- NameTruckingAgent,
		      	-- DSG_TruckingRemark
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
					,DP.DESCRIPTION[DSG_DischargePort]
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
			LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1 AND V.DATAAREAID = 'dsc'
			LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			LEFT JOIN DSG_DischargePorts DP ON S.DSG_DischargePort=DP.DSG_DISCHARGEPORT AND DP.DATAAREAID='dsc'
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
			WHERE SO = ?
			AND SentDate >= '2018-06-19'",[$so]
		); 
	}

	public function update_TruckingRevised($DSG_CY,$DSG_RTN,$DSG_TERMINALNAME,$Return_TERMINALNAME,$DSG_CustPaperlessCode,$DSG_ShippingLineDescription,$DSG_Feeder,$DSG_VoyFeeder,$DSG_Vessel,$DSG_VoyVessel,$DSG_LoadingPlant,$DSG_ClosingDate,$closetime,$DSG_CutoffVGMDate,$cutoffVGMtime,$DSG_BookingNumber,$DSG_PrimaryContactID,$DSG_EDDDate,$SO,$PrimaryAgent,$PIC1,$PIC2,$DSG_ETDDate,$DSG_ETADate,$DSG_PortOfLoadingDesc,$DSG_ToPortDesc,$DSG_Transhipment_Port,$grossweight,$NameTruckingAgent,$DSG_TruckingRemark,$DSG_DischargePort,$std20_,$std40_,$hc40_,$lcl_,$hc45_)
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
			      ,PrimaryAgent = ?
			      ,PIC1 = ?
			      ,PIC2 = ?
			      ,DSG_ETDDate = ?
			      ,DSG_ETADate = ?
			      ,DSG_PortOfLoadingDesc = ?
			      ,DSG_ToPortDesc = ?
			      ,DSG_Transhipment_Port = ?
			      ,grossweight = ?
			      ,NameTruckingAgent = ?
			      ,DSG_TruckingRemark = ?
			      ,DSG_DischargePort = ?
			      ,STD20 = ?
			      ,STD40 = ?
			      ,HC40 = ? 
			      ,HC45 = ? 
			      ,LCL = ?
			      
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
				$PrimaryAgent,
				$PIC1,
				$PIC2,
				$DSG_ETDDate,
				$DSG_ETADate,
				$DSG_PortOfLoadingDesc,
				$DSG_ToPortDesc,
				$DSG_Transhipment_Port,
				$grossweight,
				$NameTruckingAgent,
				$DSG_TruckingRemark,
				$DSG_DischargePort,
				$std20_,$std40_,$hc40_,$lcl_,$hc45_,
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
	// TRUCKING TEST
	public function CheckCombine_Trucking_TEST()
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
			-- WHERE S.DSG_AutoMailStatusVendor=1 AND S.DATAAREAID = 'dsc'
			WHERE CSL.SALESID IN ('SO18-038345')
			AND S.DATAAREAID = 'dsc'
			GROUP BY CSL.SALESID"
		); 
	}

	public function insert_TruckingLog_Test($so,$closetime,$grossweight,$std20_,$std40_,$hc40_,$lcl_,$hc45_)
	{
		$conn = self::connectMormont();

		$insert = sqlsrv_query(
			$conn,
			"INSERT INTO TRUCKING_TEST 
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
				DSG_PrimaryContactID,
		      	PrimaryAgent,
				InvoiceNo,
		      	PIC1,
		      	PIC2,
		      	DSG_ETDDate,
		      	DSG_ETADate,
		      	DSG_PortOfLoadingDesc,
		      	DSG_ToPortDesc,
		      	DSG_Transhipment_Port,
		      	grossweight,
		      	NameTruckingAgent,
		      	DSG_TruckingRemark,
		      	SentDate,
		      	STD20,
		        STD40,
		        HC40,
		        HC45,
		        LCL
			) 
				SELECT 
					S.SALESID
					,CASE WHEN S.DSG_CY IS NULL THEN ''
						  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
					,CASE WHEN S.DSG_RTN IS NULL THEN ''
						  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
					,T.DSG_TERMINALNAME
					,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
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
					,'$closetime'
					,CASE WHEN S.DSG_CutoffVGMDate IS NULL THEN ''
						  WHEN S.DSG_CutoffVGMDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_CutoffVGMDate)+' '+DATENAME(month,S.DSG_CutoffVGMDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CutoffVGMDate)) END [DSG_CutoffVGMDate]
					,DSG_CutoffVGMTime
					,S.DSG_BookingNumber
					,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
						  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
					,S.DSG_PrimaryContactID
					,A.DSG_DESCRIPTION AS PrimaryAgent
					,'DSC/'+CONVERT(VARCHAR,(CJJ.DSG_VoucherSeries)) +'/'+CONVERT(VARCHAR,(CJJ.DSG_VOUCHERNO))
					,T.DSG_CONTACTPERSON+' Tel. '+T.DSG_TEL
					,TT.DSG_CONTACTPERSON+' Tel. '+TT.DSG_TEL
					,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
						  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
					,CASE WHEN S.DSG_ETADate IS NULL THEN ''
						  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
					ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
					,S.DSG_PortOfLoadingDesc
					,S.DSG_ToPortDesc
					,S.DSG_Transhipment_Port
					,'$grossweight'
					,V.NAME AS NameTruckingAgent
					,S.DSG_TruckingRemark
					,GETDATE()
					,'$std20_'
					,'$std40_'
					,'$hc40_'
					,'$hc45_'
					,'$lcl_'
					
			FROM [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[SALESTABLE] S 
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[INVENTPICKINGLISTJOUR] INVP ON INVP.ORDERID = S.SALESID 
			AND INVP.DATAAREAID = S.DATAAREAID
			AND INVP.NOYESID_DISABLED=0
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[VendTable] V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1 AND V.DATAAREAID='dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_TerminalTrucking] T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_TerminalTrucking] TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DSG_AGENTTABLE] A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
			LEFT JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[DATAAREA] P ON S.DSG_LoadingPlant = P.ID  
			JOIN 
				 (
				  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
				  FROM [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[CustConfirmJour] CJ
				  GROUP BY DATAAREAID,SALESID
				 )CJ
				 ON CJ.DATAAREAID = S.DATAAREAID
				 AND CJ.SALESID = S.SALESID
				 JOIN [FREY\LIVE].[DSL_AX40_SP1_LIVE].[dbo].[CustConfirmJour] CJJ
				 ON CJJ.DATAAREAID = S.DATAAREAID
				 AND CJJ.SALESID = S.SALESID
				 AND CJJ.CONFIRMID = CJ.CONFIRMID
			WHERE S.SALESID =?
			AND S.DATAAREAID = 'dsc' ",[$so]
		);

		if ($insert) {
			return true;
		} else {
			return false;
		}
	}
	// // ############# TRUCKING Revised #####################
	// public function CheckRevised_Trucking()
	// {
	// 	$conn = self::connect();
	// 	return Sqlsrv::Rows(
	// 		$conn,
	// 		"SELECT 
	// 				CSL.SALESID [SO]
	// 				,CASE WHEN S.DSG_CY IS NULL THEN ''
	// 					  WHEN S.DSG_CY ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_CY)+' '+DATENAME(month,S.DSG_CY)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CY)) END [DSG_CY]
	// 				,CASE WHEN S.DSG_RTN IS NULL THEN ''
	// 					  WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN]
	// 				,S.DSG_ATCY
	// 				,T.DSG_TERMINALNAME
	// 				,T.DSG_CONTACTPERSON
	// 				,T.DSG_TEL
	// 				,TT.DSG_TERMINALNAME AS Return_TERMINALNAME
	// 				,TT.DSG_CONTACTPERSON AS Return_CONTACTPERSON
	// 				,TT.DSG_TEL AS Return_TEL
	// 				,S.DSG_CustPaperlessCode
	// 				,S.DSG_ShippingLineDescription
	// 				,S.DSG_Feeder
	// 				,S.DSG_VoyFeeder
	// 				,S.DSG_Vessel
	// 				,S.DSG_VoyVessel
	// 				,S.DSG_LoadingPlant
	// 				,CASE WHEN S.DSG_ClosingDate IS NULL THEN ''
	// 					  WHEN S.DSG_ClosingDate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ClosingDate)+' '+DATENAME(month,S.DSG_ClosingDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ClosingDate)) END [DSG_ClosingDate]
	// 				,S.DSG_ClosingTime
	// 				,CASE WHEN S.DSG_CutoffVGMDate IS NULL THEN ''
	// 					  WHEN S.DSG_CutoffVGMDate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_CutoffVGMDate)+' '+DATENAME(month,S.DSG_CutoffVGMDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_CutoffVGMDate)) END [DSG_CutoffVGMDate]
	// 				,CASE WHEN S.DSG_ETDDate IS NULL THEN ''
	// 					  WHEN S.DSG_ETDDate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ETDDate)+' '+DATENAME(month,S.DSG_ETDDate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDate)) END [DSG_ETDDate]
	// 				,CASE WHEN S.DSG_ETADate IS NULL THEN ''
	// 					  WHEN S.DSG_ETADate ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_ETADate)+' '+DATENAME(month,S.DSG_ETADate)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADate)) END [DSG_ETADate]
	// 				,S.DSG_CutoffVGMTime
	// 				,S.DSG_BookingNumber
	// 				,S.DSG_PrimaryContactID
	// 				,S.DSG_PortOfLoadingDesc
	// 				,S.DSG_ToPortDesc
	// 				,S.DSG_Transhipment_Port
	// 				,S.DSG_TruckingRemark
	// 				,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
	// 					  WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
	// 				ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDate]
	// 				,V.AccountNum
	// 				,V.NAME AS NameTruckingAgent
	// 				,A.DSG_DESCRIPTION AS PrimaryAgent
	// 				,CJJ.DSG_VoucherSeries
	// 				,CJJ.DSG_VOUCHERNO
	// 		FROM SALESTABLE S
	// 		JOIN 
	// 			 (
	// 			  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
	// 			  FROM CUSTCONFIRMSALESLINK SL
	// 			  GROUP BY DATAAREAID,ORIGSALESID
	// 			 )SL
	// 			 ON SL.DATAAREAID = S.DATAAREAID
	// 			 AND SL.ORIGSALESID = S.SALESID
	// 			 JOIN CUSTCONFIRMSALESLINK CSL
	// 			 ON CSL.DATAAREAID = S.DATAAREAID
	// 			 AND CSL.ORIGSALESID = S.SALESID
	// 			 AND CSL.CONFIRMID = SL.CONFIRMID
	// 		JOIN 
	// 			 (
	// 			  SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,SALESID
	// 			  FROM CustConfirmJour CJ
	// 			  GROUP BY DATAAREAID,SALESID
	// 			 )CJ
	// 			 ON CJ.DATAAREAID = S.DATAAREAID
	// 			 AND CJ.SALESID = S.SALESID
	// 			 JOIN CustConfirmJour CJJ
	// 			 ON CJJ.DATAAREAID = S.DATAAREAID
	// 			 AND CJJ.SALESID = S.SALESID
	// 			 AND CJJ.CONFIRMID = CJ.CONFIRMID
	// 		LEFT JOIN DSG_TerminalTrucking T ON S.DSG_ATCY = T.DSG_TERMINALID AND T.DATAAREAID = 'dsc'
	// 		LEFT JOIN DSG_TerminalTrucking TT ON S.DSG_ATRTN = TT.DSG_TERMINALID AND TT.DATAAREAID = 'dsc'
	// 		LEFT JOIN VendTable V ON V.NAMEALIAS = S.DSG_TRUCKINGAGENT AND V.DSG_ActiveTrucking = 1
	// 		LEFT JOIN DSG_AGENTTABLE A ON S.DSG_PrimaryAgentId = A.DSG_AGENTID AND A.DATAAREAID = 'dsc'
	// 		WHERE S.DSG_AutoMailStatusVendor=2 AND S.DATAAREAID = 'dsc'"
	// 	); 
	// }

	// public function CheckLog_Trucking()
	// {
	// 	$conn = self::connectMormont();
	// 	return Sqlsrv::Rows(
	// 		$conn,
	// 		"SELECT *
	// 		FROM TRUCKING"
	// 	); 
	// }

	// public function update_TruckingRevised($DSG_CY,$DSG_RTN,$DSG_TERMINALNAME,$Return_TERMINALNAME,$DSG_CustPaperlessCode,$DSG_ShippingLineDescription,$DSG_Feeder,$DSG_VoyFeeder,$DSG_Vessel,$DSG_VoyVessel,$DSG_LoadingPlant,$DSG_ClosingDate,$closetime,$DSG_CutoffVGMDate,$cutoffVGMtime,$DSG_BookingNumber,$DSG_PrimaryContactID,$DSG_EDDDate,$SO)
	// {
	// 	$conn = self::connectMormont();

	// 	$update = sqlsrv_query(
	// 		$conn,
	// 		"UPDATE TRUCKING SET DSG_CY = ?
	// 		      ,DSG_RTN = ?
	// 		      ,DSG_ATCY = ?
	// 		      ,DSG_ATRTN = ?
	// 		      ,DSG_CustPaperlessCode = ?
	// 		      ,DSG_ShippingLine = ?
	// 		      ,DSG_Feeder = ?
	// 		      ,DSG_VoyFeeder = ?
	// 		      ,DSG_Vessel = ?
	// 		      ,DSG_VoyVessel = ?
	// 		      ,DSG_LoadingPlant = ?
	// 		      ,DSG_ClosingDate = ?
	// 		      ,DSG_ClosingTime = ?
	// 		      ,DSG_CutoffVGMDate = ?
	// 		      ,DSG_CutoffVGMTime = ?
	// 		      ,DSG_BookingNumber = ?
	// 		      ,DSG_PrimaryContactID = ?
	// 		      ,DSG_EDDDate = ?
	// 		WHERE SO = ? ",			
	// 		[
	// 			$DSG_CY,
	// 			$DSG_RTN,
	// 			$DSG_TERMINALNAME,
	// 			$Return_TERMINALNAME,
	// 			$DSG_CustPaperlessCode,
	// 			$DSG_ShippingLineDescription,
	// 			$DSG_Feeder,
	// 			$DSG_VoyFeeder,
	// 			$DSG_Vessel,
	// 			$DSG_VoyVessel,
	// 			$DSG_LoadingPlant,
	// 			$DSG_ClosingDate,
	// 			$closetime,
	// 			$DSG_CutoffVGMDate,
	// 			$cutoffVGMtime,
	// 			$DSG_BookingNumber,
	// 			$DSG_PrimaryContactID,
	// 			$DSG_EDDDate,
	// 			$SO
	// 		]
	// 	);

	// 	if ($update) {
	// 		return true;
	// 	} else {
	// 		return false;
	// 	}
	// }

}