<?php
if(!isset($_SESSION)) session_start();

include "function/function.php";
require 'libs/Smarty.class.php';
include "base_config.php";

if($act=="getCarHistory"){
	if(isset($_SESSION['car_exhibiton_history']) && count($_SESSION['car_exhibiton_history'])>0){
		krsort($_SESSION['car_exhibiton_history']);
		$car_exhibiton_history = array_slice($_SESSION['car_exhibiton_history'], ($page-1)*3, 3);
		
		$HTML="";
		foreach($car_exhibiton_history as $k => $history){
			$sql = "SELECT * FROM `wwd_exhibition_item` WHERE id='".$history."' ";
			$row = load_data_row2($sql);

			$sql = "SELECT * FROM `wwd_exhibition_item_pic` WHERE exhibtion_id='".$history."' AND category='1' ORDER BY sort Limit 1 ";
			$pic = load_data_row2($sql);

			$HTML .= '<div class="item"><div class="caritemtop" style="position:relative;">';
			if($row['soldout']=="1"){
				$HTML.='<div class="warning_red" style="left:-8px;">售出</div>';
			}
			$HTML.='<div class="caritem" style="margin-left:0px;">
                      <img src="uploads/'.$pic['filename'].'" style="display:inline-block;">
                      <div class="caritem_title" style="margin-top:0px;padding:0px 5px;">
                        <div style="float:left;width:190px;height:30px;overflow:hidden;">'.$row['brand']." ".$row['model'].'</div>
                        <ul style="width:200px;">
                          <a href="car_exhibition.php?id='.$history.'"><li class="select_detal" style="margin:2px">細節</li></a>
                          <a href="car_tryout.php?fid='.$history.'"><li class="select_detal" style="margin:2px">我要試乘</li></a>
                          <li class="view" style="margin-left:6px">人氣：'.$row['views'].'</li>
                        </ul>
                      </div></div></div></div>';
		}
		echo $HTML;
	}
	exit;
}

if($act=="setCarItem"){
	if($carItem!=""){
		$msql = new phpMysql_h;

		if($cate=="hot"){
			$column = "is_hot";
		}
		elseif($cate=="new"){
			$column = "is_new";
		}
		$sql = "UPDATE `wwd_exhibition_item` SET ".$column."='0' ";
		$msql->init();
		$msql->query($sql);

		$carItem_arr = explode(";", $carItem);
		$carItem_arr2 = array_reverse($carItem_arr);
		$err_cnt = 0;
		foreach($carItem_arr2 as $k => $item){
			$sql = "UPDATE `wwd_exhibition_item` SET ".$column."='".($k+1)."', modifiedTime=NOW(), modifier='".$_SESSION['MM_Username1']."' WHERE id='".$item."' ";
			$msql->init();
			$msql->query($sql);
// error_log($sql."\r\n\r\n",3,"log_ajax_docheck_setCarItem_sql.log");
			if(!(mysql_affected_rows()>0)){
				$err_cnt++;
			}
		}

		if($err_cnt==0){
			echo "OK";		//--- 已存在
		}
		else{
			echo $err_cnt;	//--- 錯誤筆數
		}
	}
	else{
		echo "EMPTY";	//--- 空值
	}
	exit;
}

//------------ 好康商品功能 ------------//
elseif($act=="goodthingWanted"){
	$msql = new phpMysql_h;

	$ps=" WHERE user_id='".$userid."' AND goodthing_id='".$goodid."'";
	$row = loaddata_row("wwd_goodthing_want",$ps);

	if($row){
		echo "EXIST";		//早已互通
	}
	else{
		$ps=" WHERE goodthing_id='".$goodid."'";
		$rows = loaddata_array("wwd_goodthing_want",$ps);

		if(count($rows) >= GOODTHING_SOLD_OUT_NUM){
			echo "SOLD_OUT";
			exit;
		}
		else{
			$sql = "INSERT INTO `wwd_goodthing_want` (`user_id`, `goodthing_id`, `read`, `createdtime`)
				VALUES ('".$userid."', '".$goodid."', '1', NOW());";
			$msql->init();			
			$msql->query($sql);

			if(mysql_affected_rows()>0){
				echo "OK";	//已互通
			}
			else{
				echo "NOT";	//互通不成功
			}
		}	
	}
	//echo "EXIST";		//早已互通
	//echo "SOLD_OUT";	//已售完
	//echo "OK";		//已互通
	//echo "NOT";		//互通不成功
	exit;
}
elseif ($act=="checkAcc") {
	$ps=" WHERE account='".$account."' ";
	$row = loaddata_row("wwd_accounts",$ps);

	if($row){
		echo "1";	//帳號已存在
	}
	else{
		echo "2";	//帳號不存在
	}
	exit;
}
elseif($act=="checkEmail"){
	$ps=" WHERE email='".$email."' ";
	$row = loaddata_row("wwd_accounts",$ps);
	
	if($row){
		echo "1";	//已存在
	}
	else{
		echo "2";	//不存在
	}
	exit;
}
else if ($act=="chkverify"){
// 	$type = $_GET['mode'];
	$sName = 'vCode';
	if($type!="")	$sName = 'vCode_'.$type;
	
	$ara = explode("|", $_SESSION[$sName]);
// 	$ara = explode("|", $_SESSION['vCode']);

// error_log(print_r($_SESSION,true)."\r\n\r\n",3,"log_ajax_docheck_chkverify_SESSION.log");
// error_log("\r\n\r\n".$sName."\r\n".print_r($ara,true)."\r\nGET[verify_code]=".$verify_code,3,"log_ajax_docheck_chkverify.log");

	if($ara[0] != $verify_code){
		echo $verify_code;
	}
	else{
		echo "OK";
	}
}
else if ($act=="chkverifyLogin"){
	$sName = 'vCode_login';
	$ara = explode("|", $_SESSION[$sName]);
// error_log("@@@@@@\r\n".$sName."\r\n".print_r($ara,true)."\r\n\r\nverify_code_login=".$verify_code_login,3,"log_ajax_docheck_chkverifyLogin.log");
	if($ara[0] != $verify_code_login){
		echo $verify_code_login;
	}else{
		echo "OK";
	}
}
elseif ($act=="doChgEmail"){
	$msql = new phpMysql_h;
	$msql->init();
	
	$sql = "Update wwd_accounts set email='$newEmail' WHERE uid=$_COOKIE[user_id]";
	$msql->query($sql);
	
	$ps=" WHERE email='".$newEmail."' ";
	$row = loaddata_row("wwd_accounts",$ps);
	
	if($row){
		$confirmTimeReg = date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m"), date("d")+7, date("Y")));
		$confirmCodeReg = uniqid(mt_srand((double)microtime() * 1000000));
		
		$sql = "Update wwd_accounts set confirmCodeReg='$confirmCodeReg', confirmTimeReg='$confirmTimeReg', confirmTimeReg2=NULL WHERE uid=".$_COOKIE[user_id];
		$msql->init();
		$msql->query($sql);
		
		$ps2=" WHERE uid='".$_COOKIE[user_id]."' ";
// echo "____ps2=".$ps2;
		$row2 = loaddata_row("wwd_accounts",$ps2);
		
		if($row2['confirmCodeReg']==$confirmCodeReg){
			$email = $row2['email'];
			if(send_confirm_mail($email,$confirmCodeReg,true)){
				echo "OK";
				exit;
			}
			else{
				echo "mail_Fail";
				exit;
			}
		}
		else{
			echo "NoWork";
			exit;
		}
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif ($act=="doChgMobile"){
	$msql = new phpMysql_h;
	$msql->init();

	$sql = "Update wwd_accounts set moblie='$newMobile' WHERE uid=$_COOKIE[user_id]";
	$msql->query($sql);

	$ps=" WHERE moblie='".$newMobile."' ";
	$row = loaddata_row("wwd_accounts",$ps);

	if($row){
		echo "OK";	//已存在
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif ($act=="reAuthenEmail"){
	$s = " WHERE uid=".$uid;
	$r = loaddata_row("wwd_accounts",$s);
	
	$msql = new phpMysql_h;

	$confirmTimeReg = date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m"), date("d")+7, date("Y")));
	$confirmCodeReg = uniqid(mt_srand((double)microtime() * 1000000));
	
	if($r){
		$sql = "Update `wwd_accounts` set `confirmCodeReg`='$confirmCodeReg', `confirmTimeReg`='$confirmTimeReg', `confirmTimeReg2`=NULL WHERE uid=$uid";
// echo "<br>sql=".$sql;		
// error_log("\r\n------".date("YmdHis")."sql=".$sql,3,"log_ajax_docheck_reAuthenEmail_sql.log");
		$msql->init();
		$msql->query($sql);
		
		$ps=" WHERE uid='".$uid."' ";
		$row = loaddata_row("wwd_accounts",$ps);
		
		if($row['confirmCodeReg']==$confirmCodeReg){
			global $firstname,$lastname,$sex;
			$firstname = $row['firstname'];
			$lastname = $row['lastname'];
			$sex = $row['sex'];
			$email = $row['email'];
			
			if(send_confirm_mail($email,$confirmCodeReg,true)){
// 				$_SESSION['regClientEmail'] = $email;
// 				unset($_SESSION['POST']);
// 				$tool->goURL("register_client_ok.php");
				echo "OK";
				exit;
			}
		}
		else{
			echo "NoWork";
			exit;
		}
	}
	else{
		echo "NoUser";
		exit;
	}
}
elseif ($act=="chkOldPassword"){
	$ps=" WHERE password='".$oldpassword."' AND uid=$_COOKIE[user_id]";
	$row = loaddata_row("wwd_accounts",$ps);
// error_log($ps."\r\n\r\n",3,"log_ajax_docheck_chkOldPassword_ps.log");	
	if($row){
		echo "1";	//已存在
	}
	else{
		echo "2";	//不存在
	}
	exit;
}
elseif($act=="doAppExtend"){ 	
	$ps = " WHERE id=$aid ";
	$row = loaddata_row($dbTable['application'][$cateid],$ps);

	$year = substr($row['deadline'],0,4);
	$month = substr($row['deadline'],5,2);
	$day = substr($row['deadline'],8,2);
	$hour = substr($row['deadline'],11,2);
	$min = substr($row['deadline'],14,2);
	$sec = substr($row['deadline'],17,2);
	$deadline2 = date("Y-m-d H:i:s",mktime($hour, $min, $sec, $month, $day+7, $year));
	
	$msql = new phpMysql_h;
	$msql->init();
	$sql = "UPDATE ".$dbTable['application'][$cateid]." SET isExtended='1' , deadline='$deadline2' WHERE id=$aid ";
// error_log("\r\n".$sql."\r\n",3,"log_ajax_docheck_doAppExtend_sql.log");
	$msql->query($sql);
	
	$ps=" WHERE isExtended=1 AND deadline='$deadline2' AND id=".$aid;
	$row = loaddata_row($dbTable['application'][$cateid],$ps);
	
	if($row){
		echo "OK";	//已完成延長截止日期
	}
	else{
		echo "NOT";	//延長截止日期失敗
	}
	exit;
	
}
elseif($act=="removeNeed"){
	$msql = new phpMysql_h;
	$msql->init();

	$sql = "INSERT INTO `wwd_application_trash` (`catelog_id`,`apply_id`,`user_id`,`createdtime` )
			VALUES ('".$cateid."','".$needid."','".$_COOKIE[user_id]."', NOW());";
	$msql->query($sql);

	$ps=" WHERE catelog_id='".$cateid."' AND apply_id='".$needid."' AND user_id='".$_COOKIE[user_id]."'";
	$row = loaddata_row("wwd_application_trash",$ps);

	if($row){
		echo "OK";	//已存在
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif($act=="removeNeedClient"){
	$msql = new phpMysql_h;
	$msql->init();

	$sql = "INSERT INTO `wwd_application_trash` (`catelog_id`,`apply_id`,`user_id`,`createdtime` )
			VALUES ('".$cateid."','".$needid."','".$_COOKIE[user_id]."', NOW());";
	$msql->query($sql);

	$ps=" WHERE catelog_id='".$cateid."' AND apply_id='".$needid."' AND user_id='".$_COOKIE[user_id]."'";
	$row = loaddata_row("wwd_application_trash",$ps);

	if($row){
		echo "OK";		//--- 已存在
		$sql = "UPDATE ".$dbTable['application'][$cateid]." SET status='9' , modifiedTime=NOW() WHERE id=$needid ";
// error_log($sql."\r\n",3,"log_ajaxdocheck_removeNeedClient_sql.log");
		$msql->init();
		$msql->query($sql);
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif($act=="cancelQuota"){
	$msql = new phpMysql_h;
	
	// $sql = "INSERT INTO ".$dbTable['quota'][$cateid]." (`catelog_id`,`apply_id`,`user_id`,`createdtime` )
	// 		VALUES ('".$cateid."','".$needid."','".$_COOKIE[user_id]."', NOW());";
	// $msql->init();
	// $msql->query($sql);

	$ps=" WHERE id='".$qid."' AND apply_id='".$applyid."' AND creator='".$_COOKIE['user_id']."'";
// error_log($ps."\r\n",3,"log_ajaxdocheck_cancelQuota_ps.log");
	$row = loaddata_row($dbTable['quota'][$cateid],$ps);

	if($row){
		echo "OK";	//已存在
		$sql = "UPDATE ".$dbTable['quota'][$cateid]." SET disabled='1', modifier=".$_COOKIE['user_id'].", modifiedTime=NOW() WHERE id=".$qid;
// error_log($sql."\r\n",3,"log_ajaxdocheck_cancelQuota_sql.log");
		$msql->init();
		$msql->query($sql);
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif($act=="removeQuota"){
	$msql = new phpMysql_h;

	$sql = "DELETE FROM ".$dbTable['quota'][$cateid]." WHERE id=".$qid;
error_log($sql."\r\n",3,"log_ajaxdocheck_removeQuota_sql.log");
	$msql->init();
	$msql->query($sql);
	
	$ps=" WHERE id='".$qid."' ";
error_log($ps."\r\n",3,"log_ajaxdocheck_removeQuota_ps.log");
	$row = loaddata_row($dbTable['quota'][$cateid],$ps);
	
	if(!$row){
		echo "OK";	//已存在
	}
	else{
		echo "NOT";	//不存在
	}
	exit;
}
elseif($act=="doSpeedMatch"){
	$ps = " WHERE id=$aid ";
	$row = loaddata_row($dbTable['application'][$cateid],$ps);

// 	$year = substr($row['deadline'],0,4);
// 	$month = substr($row['deadline'],5,2);
// 	$day = substr($row['deadline'],8,2);
// 	$hour = substr($row['deadline'],11,2);
// 	$min = substr($row['deadline'],14,2);
// 	$sec = substr($row['deadline'],17,2);
// 	$deadline2 = date("Y-m-d H:i:s",mktime($hour, $min, $sec, $month, $day+7, $year));

	$msql = new phpMysql_h;
	$msql->init();
	$sql = "UPDATE ".$dbTable['application'][$cateid]." SET status='2' , isSpeedMatch='1' , speedmatchTime=NOW() WHERE id=$aid ";
// 	$sql = "UPDATE ".$dbTable['application'][$cateid]." SET isSpeedMatch='1' , speedmatchTime=NOW() WHERE id=$aid ";
	$msql->query($sql);

	$ps=" WHERE isSpeedMatch='1' AND speedmatchTime!='' AND id=".$aid;
	$row = loaddata_row($dbTable['application'][$cateid],$ps);

	if($row){
		echo "OK";	//已完成快速服務設定
	}
	else{
		echo "NOT";	//快速服務設定失敗
	}
	exit;
}
elseif($act=="getLastDesc"){
	$ps = " WHERE id=$aid ";
	$row = loaddata_row($dbTable['application'][$cateid],$ps);
}
elseif($act=="setSession"){
	if(!$_SESSION[$name]){
		$_SESSION[$name]=$val;
	}
	echo "OK";
	exit;
}

/*
function send_confirm_mail($email,$confirmCodeReg,$do_sendmail=true){
	global $dbname,$firstname,$lastname,$sex;
	//====== 寄確認email給使用者 ======//
	$do_sendmail = true;
	if($do_sendmail){
		$tool = new tools_h;
		// 建立 PHPMailer 物件及設定 SMTP 登入資訊
		require "phpmailer/class.phpmailer.php";
		$mail = new PHPMailer();
		$mail->IsSMTP();

		//====== 透過 Gmail 來寄信 ======//
		//$mail->SMTPAuth = true; //設定SMTP需要驗證
		//$mail->SMTPSecure = "ssl"; // Gmail的SMTP主機需要使用SSL連線
		//$mail->Host = "msa.hinet.net"; //Hinet的SMTP主機
		//$mail->Host = "smtp.gmail.com"; //Gamil的SMTP主機
		//$mail->Port = 465;  //Gamil的SMTP主機的SMTP埠位為465埠。
		//$mail->CharSet = "big5"; //設定郵件編碼

		$mail->Host = "localhost"; 										//hankyu.mooo.com的SMTP主機
		// $mail->Host = "msa.hinet.net";
		$mail->CharSet = "UTF-8";
		$mail->Encoding = "base64";
		$mail->From = "service@wewanted.com.tw"; 						//設定寄件者信箱
		$mail->FromName = "Wewanted!就是要你"; 									//設定寄件者姓名
		//$mail->From = $setting_r['admin_email']; 							//設定寄件者信箱
		//$mail->FromName = $StoreName; 									//設定寄件者姓名
		$mail->Subject = "【啟用信】wewanted.com會員帳號啟用確認";	 			//設定郵件標題
		$mail->IsHTML(true); 												//設定郵件內容為HTML
		$mail->AddAddress($email,$firstname.($sex==1?"先生":"小姐")); 		//設定收件者郵件及名稱
		// $mail->AddAddress("hankyu1012@gmail.com","先生"); 					//設定收件者郵件及名稱
		
// 		$mail_content = "您會員帳號已註冊，請按下列網址前往進行帳號確認。usertype=".$_COOKIE['user_type']."<br><br>
// 		<a href='http://".$_SERVER['HTTP_HOST']."/regCheck.php?code=".$confirmCodeReg."'>http://".$_SERVER['HTTP_HOST']."/regCheck.php?code=".$confirmCodeReg."</a>";
// error_log("\r\n------".date("YmdHis")."confirmCodeReg=".$confirmCodeReg,3,"log_ajax_docheck_reAuthenEmail_confirmCodeReg.log");
		$sexLabel = ($sex==1)?"先生":"小姐";
		
		$mail_content[1] = $firstname.$sexLabel."您好！歡迎加入成為WeWanted網站消費者會員！<br>
		此封信是由WeWanted網站所發送，我們需要對您的電子信箱有效進行驗證以避免垃圾郵件或地址被濫用，透過帳號驗證將能確保您與WeWanted網站的聯絡管道保持暢通。<br>
		<br>
		為了確認您的電子信箱正確無誤，請點擊以下連結來啟用您的帳號：<br><br>
		<a href='http://".$_SERVER['HTTP_HOST']."/regCheck.php?code=".$confirmCodeReg."'>http://".$_SERVER['HTTP_HOST']."/regCheck.php?code=".$confirmCodeReg."</a>
		<br>(若以上不是連結形式，請將連結手動複製到瀏覽器地址欄再訪問)<br>
		<br>
		請注意：此啟用連結將於三日後失效，屆時煩請至【會員管理】→【帳號管理】，點擊個人帳號資訊欄位下的【重寄啟用信】，重新申請即可。<br>
		<br>
		如您有任何問題及意見，請聯絡我們：<a href='mailto:service@wewanted.com.tw'>service@wewanted.com.tw</a><br>
		我們將竭誠為您服務。<br>
		<br>
		最後，感謝您的加入，<a href='http://www.wewanted.com.tw' target='_blank'>WeWanted</a>祝您使用愉快。<br>
		<br>
		<br>
		※此封信為系統自動發信，請勿直接回覆信件。<br>
		<br>
		<br>
		此致<br>
		WeWanted 管理團隊<br>
		<a href=http://www.wewanted.com.tw target='_blank'>http://www.wewanted.com.tw</a>";
		
		
		$mail_content[2] = $firstname.$sexLabel."您好！歡迎加入成為WeWanted網站廠商／業務會員！<br>
		此封信是由WeWanted網站所發送，我們需要對您的電子信箱有效進行驗證以避免垃圾郵件或地址被濫用，透過帳號驗證將能確保您與WeWanted網站的聯絡管道保持暢通。<br>
		<br>
		首先，為了確認您的電子信箱正確無誤，請點擊以下連結來啟用您的帳號：<br><br>
		<a href='http://".$_SERVER['HTTP_HOST'].WEB_ROOT_DIR."/regCheck.php?code=".$confirmCodeReg."'>http://".$_SERVER['HTTP_HOST'].WEB_ROOT_DIR."/regCheck.php?code=".$confirmCodeReg."</a>
		<br>(若以上不是連結形式，請將連結手動複製到瀏覽器地址欄再訪問)<br>
		<br>
		請注意：此啟用連結將於三日後失效，屆時煩請至【會員管理】→【帳號管理】，點擊個人帳號資訊欄位下的【重寄啟用信】，重新申請即可。<br>
		<br>
		接著，啟用帳號後請您至首頁依序點選【會員管理】→【帳號管理】，並完成以下兩件事，方完成申請手續：<br><br>
		
		1、請至【會員身分驗證】欄位中下載會員申請書，並填寫完成。<br>
		2、申請書填寫完成後依申請書說明須於一個月內回傳至WeWanted，否則貴帳戶將被停用，待完成申請後始重新開放。<br>
		<br>
		＊提醒您：帳戶申請完成後，請記得登錄WeWanted網站，並點選【會員管理】→【帳號管理】→【修改資料】，將服務簡介內容填寫完成，讓消費者更了解您，提高交易意願。
		<br>
		<br>
		<br>
		如您有任何問題及意見，請聯絡我們：<a href='mailto:service@wewanted.com.tw'>service@wewanted.com.tw</a><br>
		我們將竭誠為您服務。<br>
		<br>
		最後，感謝您的加入，<a href='http://www.wewanted.com.tw' target='_blank'>WeWanted</a>祝您使用愉快。<br>
		<br>
		<br>
		※此封信為系統自動發信，請勿直接回覆信件。<br>
		<br>
		<br>
		此致<br>
		WeWanted 管理團隊<br>
		<a href=http://www.wewanted.com.tw target='_blank'>http://www.wewanted.com.tw</a>";
		
		$mail_content[3] = $mail_content[2];
 		
		$mail->Body = $mail_content[$_COOKIE['user_type']];									//設定郵件內容

		if($mail->Send()){
// 			$tool->showmessage('註冊申請已完成，請至email進行後續帳號確認事宜。');
// echo "<br>yes";
// exit;
			return true;
		}
		else{
// echo "<br>no";
	
// 			$tool->showmessage('註冊申請已完成，但email寄件不成功，將改以電話進行帳號確認作業。');
			$msql2 = new phpMysql_h;
			$msql2->init();
			$sql = "Update wwd_accounts SET `confirmTimeReg`=NOW() WHERE email='".$email."' ";
// echo "<br>sql=".$sql;			
// exit;			
// error_log("(".date('Ymd-His').")\r\nsql=".$sql."\r\n\r\n","log_ajax_docheck_send_confirm_mail_sql.log");
			$msql2->query($sql);
			return false;
		}
	}
}*/
?>
