<?php
if(!isset($_SESSION))	session_start();

//--- 讀入基本設定
include "function/function.php";
require 'libs/Smarty.class.php';
include "base_config.php";

//--- 需求單內容
if(!$cate||!$uid){
	echo "<script>location.href='".$member_page."';</script>";
	exit;
}

if(!isset($setFastService)){
	checkPageAuthentication("car_yes_list");
}

// var_dump($_SESSION[FastService]);

if(isset($_SESSION[FastService])){

	$jsFast = array();
	$SESSION = $_SESSION[FastService];

	// $jsFast['firstname'] = "$('#firstname').val('".$SESSION['firstname']."');";
	// $jsFast['ccNum'] = "$('#ccNum').attr('value','".$SESSION['ccNum']."');";	
	// $jsFast['sex'] = "$('input[name=sex1][value=".$SESSION[sex]."]').attr('checked',true);";
	
	// foreach($SESSION['propose'] as $val){
	// 	$SESSION['propCheck'.$val]=" checked ";
	// }
	
	// $jsFast['buyPlace'] = "$('#buyPlace').attr('value','".$SESSION['buyPlace']."');";
	// $jsFast['bought'] = "$('input[name=bought]').eq(".($SESSION['bought']-1).").attr('checked',true);";
	// $jsFast['buyTime'] = "$('#buyTime').attr('value','".$SESSION['buyTime']."');";

	$jsFast['put_city'] = "$('#put_city').attr('value','".$SESSION['put_city']."');";
	// $jsFast['put_city2'] = "Buildkey(".$SESSION['put_city'].");";
	// $jsFast['put_area'] = "$('#put_area').attr('value','".$SESSION['put_area']."');";
	// // $jsFast['put_area2'] = "var put_area_text = document.getElementById('put_area').options[document.getElementById('put_area').options.selectedIndex].text;";
	// // $jsFast['put_area3'] = "checkArea(put_area_text);";
	$jsFast['isSeen'] = "$('input[name=isSeen][value=".$SESSION[isSeen]."]').attr('checked',true);";
	$jsFast['buySelf'] = "$('input[name=buySelf][value=".$SESSION[buySelf]."]').attr('checked',true);";
	$jsFast['SheetBuyPlace'] = "$('#SheetBuyPlace').attr('value','".$SESSION['SheetBuyPlace']."');";

	$needsContent = getNeedsContent($cate,$uid);
	$need[serial] = $needsContent[serial];
	$need[brand] = $needsContent[brand];
	$need[model] = $needsContent[model];
	$need[id] = $needsContent[id];

	$accountdata[firstname] = $SESSION[firstname];
	$accountdata[firstname] = $SESSION[firstname];


	if($SESSION[sex]==1)	$accountdata[sexCheck1]=" checked ";
	if($SESSION[sex]==2)	$accountdata[sexCheck2]=" checked ";	
			
	// foreach($SESSION[contactable] as $val){
	// 	$accountdata['conCheck'.$val]=" checked ";
	// }
	$contactableArr = explode(",",$SESSION['contactable']);
	for($i=1;$i<=6;$i++){
		if(in_array($i, $contactableArr)){
			$confirmImg[$i] = array("src"=>"images/tick_02.png", "chk"=>1);
		}
		else{
			$confirmImg[$i] = array("src"=>"images/tick_01.png", "chk"=>0);
		}
	}
	
	if($SESSION['mobile']){
		$mobileArr = explode("-", $SESSION['mobile']);
		$accountdata['mobile_1st'] = $mobileArr[0];
		$accountdata['mobile_2nd'] = $mobileArr[1];
		$accountdata['moblie'] = $SESSION['mobile'];
	}
}
else{

	$needsContent = getNeedsContent($cate,$uid);

	if($needsContent){

		$need = $needsContent;		
		$need['uid'] = $uid;
		$need['cateid'] = $cate;
		
		if($cate==1){

			$need['budget2'] = $budgetLabel[$need['budget']];
			$need['from2'] = $oriplaceLabel[$need['ori_place']];	//"進口":"國產";
	// 		$need['from2'] = ($need['from']==1)? "進口":"國產";
			
			$needPropose = explode(",", $need['propose']);
			foreach($needPropose as $val){
				$need['propose2'] .= ($need['propose2']=="")? $proposeLable[$val]:", ".$proposeLable[$val];
				$need['propose3'] .= ($need['propose3']=="")? $val:",".$val;
			}
			
			$need['ccNum2'] = $ccNumLabel[$need['ccNum']];
			$need['buyPlace2'] = $placeToBuy[$need['buyPlace']];
			$need['buyTime2'] = $timeToBuy[$need['buyTime']];
			$need['bought2'] = ($need['bought']==1)? "是":"否";
			$need['requirement1'] = $need['requirement']? nl2br($need['requirement']):"無";
			$hasRequirement = $need['requirement']? 1:0;
			$need['requirement2'] = htmlspecialchars($need['requirement'],ENT_QUOTES);
		}
		else if($cate==2){

			$need['serialno'] = $needsContent['serial'];
			$need['title'] = $needsContent['brand']." ".$needsContent['model'];
			$need['title'] = isset($needsContent['title'])?$needsContent['title']:"無";
			$need['brand'] = isset($needsContent['brand'])?$needsContent['brand']:"無";
			$need['model'] = isset($needsContent['model'])?$needsContent['model']:"無";
			$need['color'] = isset($needsContent['color'])?$needsContent['color']:"無";
			//$need['category']="汽車/已選定";
			$ct = explode(" ", $needsContent['createdTime']);
			$need['createdtime'] = $ct[0];
			$dt = explode(" ", $needsContent['deadline']);
			$need['deadline'] = $dt[0];
			
			if(isset($needsContent['sex']))	$need['sexage'] = (($needsContent['sex']==1)? "男性":"女性");
			if($needsContent['age']!=""){
				$need['sexage'] .= $need['sexage']!=""?", ":"";
				$need['sexage'] .= $needsContent['age']."歲";
			}
		
			//------ 付款方式: 貸款，自備20萬，貸款36期，每月還款10000元
			$pays = explode("@@@###$$$%%%", $needsContent['payment_desc']);
			$need['payment'] = $paymentLabel[$needsContent['payment']];
			
			if($needsContent['payment']!="cash"){
				$need['payment'] = $need['payment']."，自備".$pays[0]."萬，貸款".$pays[1]."期，每月還款".$pays[2]."元";
			}
		
			//------ 6.保險需求: 	業務人員協助投保(實報實銷)
			$insurance_arr = explode("@@@###$$$%%%", $needsContent['insurance']);
			$need['insuranceTypeVal'] = $insuranceTypeLabel[$insurance_arr['0']];
			
			//------ 6.(1):強制險
			$need['insuranceContentVal'] = $insuranceContentLabel[$needs['insuranceContent']];
			
			if($insurance_arr['1']==1){
				$need['insuranceContentVal'] = $insuranceContentLabel[$insurance_arr['1']];
			} else {
				$need['insuranceContentVal'] = $insuranceContentLabel[$insurance_arr['1']]."(".$needsContent['insuranceContentOther'].")";
			}
			
			//------ 6.(2):車體險
			if($insurance_arr['2']!=4){
				$need['insuranceContent2Val'] = $insuranceContent2Label[$insurance_arr['2']]."(".$insuranceContent2SubLabel[$insurance_arr[3]].")";
			} else {
				$need['insuranceContent2Val'] = $insuranceContent2Label[$insurance_arr['2']];
			}

			//------ 6.(3):任意險 ------
			// 人身：300萬/1200萬
			// 財損：30萬
			// 駕駛人：200萬
			// 乘客險：100萬/400萬
			//-------------------------
			if($insurance_arr['5']==2)
				$need['insuranceContent3Val'] .=  ($insurance_arr['5']?"人身":"").": ".$insurance3select2Label[$insurance_arr['12']];
			
			if($insurance_arr['6']==3){
				$need['insuranceContent3Val'] .=  $need['insuranceContent3Val']? "<br>":"";
				$need['insuranceContent3Val'] .=  ($insurance_arr['6']?"財損":"").": ".$insurance3select3Label[$insurance_arr['13']];
			}
				
			if($insurance_arr['7']==4){
				$need['insuranceContent3Val'] .=  $need['insuranceContent3Val']? "<br>":"";
				$need['insuranceContent3Val'] .=  ($insurance_arr['7']?"駕駛人":"").": ".$insurance3select5Label[$insurance_arr['15']];
			}
			if($insurance_arr['8']==5){
				$need['insuranceContent3Val'] .=  $need['insuranceContent3Val']? "<br>":"";
				$need['insuranceContent3Val'] .=  ($insurance_arr['8']?"乘客險":"").": ".$insurance3select4Label[$insurance_arr['14']];
			}
			
			//------ 6.(4):竊盜險
			if($insurance_arr['4']==1){
				$need['insuranceContent3Val2'] = ($insurance_arr['4']?"竊盜險":"");
				$need['insuranceContent3Val2'] .= ": ".$insurance3select1Label[$insurance_arr['11']];
			}
			if($insurance_arr['9']==1){
				$need['insuranceContent3Val2'] .= (($need['insuranceContent3Val2']!=""&& $insurance_arr['9']==1)?"+":"")
				.($insurance_arr['9']==1?"竊盜車體免折舊":"");
			}
			if($insurance_arr['10']==1){
				$need['insuranceContent3Val2'] .= (($need['insuranceContent3Val2']!=""&& $insurance_arr['10']==1)?"+":"")
				.($insurance_arr['10']==1?"竊盜代步險":"");
			}
			
		
			$need['buyPlace'] = $placeToBuy[$needsContent['buyPlace']];
			$jsFast['initial']="$('#SheetBuyPlace').val('".$needsContent['buyPlace']."');";
			$need['delivery_date'] = $needsContent['delivery_date'];
			$need['delivery_date_chk2'] = $needsContent['delivery_date_chk'] ? "( 可配合交車日期 )":"";
		
			
			$equipment_arr = explode("@@@###$$$%%%", $needsContent['equipment']);

			foreach($equipment_arr as $idx => $fvalue)
			{
				if($fvalue!=""){
					$need['equipment1'] .= ($need['equipment1']!="")?"<br>":"";
					if($idx<11){
						$need['equipment1'] .= ($idx+1).". ".$fvalue;
					}
					else{
						$need['equipment1'] .=  nl2br($fvalue);
					}
				}
			}
			$need['equipment1'] = $need['equipment1']?  $need['equipment1']:"無";
			$hasRequirement = $needsContent['equipment']? 1:0;
			$need['other_need'] = $needsContent['other_need']?$needsContent['other_need']:"無";
		}
	//$_SESSION['needs02'] = $need;
		// echo "<br>need[applier]=".$need['applier'];
		$accountdata = getAccountData($need['applier']);
		$office_addr_arr = explode(" ", $accountdata['office_addr']);
		$office_zone = $office_addr_arr[1];
		// $myService = $accountdata['my_service']? nl2br($accountdata['my_service']):"無";

		//--- 個人帳號資料
		if(isset($accountdata)){
		// var_dump($accountdata);
			// if($accountdata['sex']==1)	$accountdata['sexCheck1']=" checked ";
			// if($accountdata['sex']==2)	$accountdata['sexCheck2']=" checked ";
			
			// $contactableArr = explode(",", $accountdata['contactable']);
			// foreach($contactableArr as $val){
			// 	$accountdata['conCheck'.$val]=" checked ";
			// }

			if(isset($accountdata['moblie'])){
				$mobileArr = explode("-", $accountdata['moblie']);

				$accountdata['mobile_1st'] = $mobileArr[0];
				$accountdata['mobile_2nd'] = $mobileArr[1];
			}
		}

		$contactableArr = explode(",",$accountdata['contactable']);
		for($i=1;$i<=6;$i++){
			if(in_array($i, $contactableArr)){
				$confirmImg[$i] = array("src"=>"images/tick_02.png", "chk"=>1);
			}
			else{
				$confirmImg[$i] = array("src"=>"images/tick_01.png", "chk"=>0);
			}
		}

		for($i=1;$i<=2;$i++){
			if($accountdata['sex']==$i){
				$sexImg[$i] = array("src"=>"images/tick_02.png", "chk"=>1);
			}
			else{
				$sexImg[$i] = array("src"=>"images/tick_01.png", "chk"=>0);	
			}
		}

	}
	else{
		$tool->showmessage("無此懸賞單，請確認後再進行回覆！");
		//$tool->goURL("member_vip00.php");
		$tool->goBack();
	}
}
// var_dump($need);

//--------- assign 所需要的 data ---------
$tpl->assign("cate",$cate);
$tpl->assign("uid",$uid);
$tpl->assign("qid",$qid);
$tpl->assign("need",$need);
$tpl->assign("jsFast",$jsFast);
$tpl->assign("accountdata",$accountdata);
$tpl->assign('office_zone',$office_zone);
$tpl->assign('myService',$myService);
$tpl->assign("hasRequirement",$hasRequirement);
$tpl->assign('sweetNotification',$sweetNotification1);
$tpl->assign("insuranceType",$insurance_arr[0]);
$tpl->assign("header_class7","active");
$tpl->assign("header2_class1","active");
$tpl->assign("confirmImg",$confirmImg);
$tpl->assign("sexImg",$sexImg);

//--- 讀入Templates
$htmlfilename_array = explode(".",basename($_SERVER['SCRIPT_NAME']));
$htmlfilename = $theme_path.$htmlfilename_array[0].".html";
$tpl->display($htmlfilename);
?>
