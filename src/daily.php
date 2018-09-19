<?php 

set_time_limit(0);
ob_implicit_flush(true);
ini_set('memory_limit','512M');

require_once '../PHPMailer-5.2.23/PHPMailerAutoload.php';
require_once '../functions.php';

$automail = new AutoMail;

$runDaily = $automail->getDataDaily();
// echo date("Y-m-d");
// echo "<br>";
// echo date('02 M Y');