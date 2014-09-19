<?php
if(!$_SESSION)	session_start();
// ini_set('default_charset', 'utf-8');

//--- 讀入基本設定
include "function/function.php";
require 'libs/Smarty.class.php';
include "base_config.php";
// include ADMINROOT."/getfile.php";

$msql = new phpMysql_h;
$tool = new tools_h;

$sql = "SELECT id FROM `wwd_application_speedmatch` WHERE applyid='".$aid."' AND cateid='".$cateid."' ";
// echo "<br>sql=".$sql; exit;
$msql->init();
$msql->query($sql);
if(list($id)=mysql_fetch_row($msql->listmysql)){
	$tool->showmessage("此需求單已送過快速服務申請，請勿重複操作或聯繫客服人員確認！");
	$tool->goURL2("parent","member_client_myneeds01.php");
	exit;
}

$hash = uniqid(mt_srand((double)microtime() * 1000000));
$sql = "INSERT INTO `wwd_application_speedmatch` (`hash`,`cateid` ,`applyid` ,`serial` ,`firstname` ,`sex` ,`mobile` ,`contactable` ,`office_city` ,
		`office_area` ,`isSeen` ,`SheetBuyPlace` ,`buySelf` ,`creator` ,`createdtime`) VALUES ('$hash', '$cateid',  '$aid',  '$serial',  '$firstname',  
		'$sex',  '$mobile',  '$contactable',  '$office_city',  '$office_area',  '$isSeen',  '$SheetBuyPlace',  '$buySelf',  '".$_COOKIE[user_id]."',  
		NOW());";
// error_log($sql."\r\n\r\n",3,"log_fast_do_sql.log");
$msql->init();
$msql->query($sql);

$sql2 = "SELECT id FROM `wwd_application_speedmatch` WHERE hash='".$hash."' ";
$msql->init();
$msql->query($sql2);
if(list($id2)=mysql_fetch_row($msql->listmysql)) {
	if($_COOKIE[user_email]!="hankyu1012@gmail.com"){
		$msql->init();
		$sql3 = "UPDATE ".$dbTable['application'][$cateid]." SET status='2', isSpeedMatch='1', speedmatchTime=NOW() WHERE id=$aid ";
		$msql->query($sql3);
	}
	$tool->showmessage("懸賞單快速服務申請完成！");
	unset($_POST);
	unset($_SESSION['FastService']);
	$tool->goURL2("parent","member_client_myneeds01.php");
}
else{
	$tool->showmessage("懸賞單快速服務申請失敗，請重新操作或聯繫客服人員確認！");
	$tool->goURL2("parent","member_client_myneeds01.php");
}
exit;

// $ps = " WHERE id=$aid ";
// $row = loaddata_row($dbTable['application'][$cateid],$ps);
// 	$year = substr($row['deadline'],0,4);
// 	$month = substr($row['deadline'],5,2);
// 	$day = substr($row['deadline'],8,2);
// 	$hour = substr($row['deadline'],11,2);
// 	$min = substr($row['deadline'],14,2);
// 	$sec = substr($row['deadline'],17,2);
// 	$deadline2 = date("Y-m-d H:i:s",mktime($hour, $min, $sec, $month, $day+7, $year));

$msql->init();
$sql = "UPDATE ".$dbTable['application'][$cateid]." SET status='2', isSpeedMatch='1', speedmatchTime=NOW() WHERE id=$aid ";
// 	$sql = "UPDATE ".$dbTable['application'][$cateid]." SET isSpeedMatch='1' , speedmatchTime=NOW() WHERE id=$aid ";
$msql->query($sql);
$tool->goURL2("parent","member_client_myneeds01.php");
exit;


//----------------------------------------------------------------------------------------------------------------------
/*
$deadline = date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m"), date("d")+10, date("Y")));

$today = date("Y-m-d");
$today1 = date("Ymd")-19110000;
$sql2 = "SELECT max(serial) FROM $dbname WHERE createdTime>='".$today." 00:00:00' AND createdTime<='".$today." 23:59:59' ";
$msql->query($sql2);
list($serial)=mysql_fetch_row($msql->listmysql);
if ($serial){
	$serial_arr = explode("CY", $serial);
	$serial = $serial_arr[0]."CY".sprintf("%04d",$serial_arr[1]+1);
}
else{
	$serial = $today1."CY0001";
}

$sql = "INSERT INTO ". $dbname ." (hash, serial, applier, brand, otherBrand, model, color, payment, payment_desc, sex, age, 
		insurance, insuranceContentOther, insuranceContent2other, insuranceContent3other, buyPlace, delivery_date, delivery_date_chk, equipment, 
		other_need, deadline,createdTime) VALUES ('$hash','$serial','$_COOKIE[user_id]','$brand', '$otherBrand', '$model', '$color', 
		'$payment', '$payment_desc', '$sex', '$age', '$insurance', '$insuranceContentOther', '$insuranceContent2other', 
		'$insuranceContent3other', '$buyPlace', '$delivery_date', '$delivery_date_chk', '".turn_sql($equipment)."','$other_need','$deadline',NOW())";
$msql->init();
$msql->query($sql);

$sql2 = "SELECT id FROM $dbname WHERE hash='".$hash."' ";
$msql->query($sql2);
if (list($id)=mysql_fetch_row($msql->listmysql)){
	unset($_POST);
	unset($_SESSION['carYes']);
//	sendmail_needs_occur($cateid=2,$brand);
	$tool->goURL2("parent","car_sent.php");
	exit;
}
else{
	$tool->showmessage("需求單送出失敗，請重新操作或電洽客服人員！");
	$tool->goURL2("parent","car_yes_list.php");
	exit;
}*/
exit;
?>
