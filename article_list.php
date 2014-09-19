<?php
if(!isset($_SESSION))	session_start();

//--- 讀入基本設定
include "function/function.php";
require 'libs/Smarty.class.php';
include "base_config.php";

//--- 所屬功能
//--- WeWanted 文章
if(isset($t) && $t!=''){
	$sql_WHERE = " AND typeid = '".$t."' ";
	$urlpara['t'] = "&t=".$t;
}

if(isset($z) && $z!=''){
	switch ($z) {
		case '1':
			$sql_WHERE = " AND ( typeid >= '1' AND typeid <= '3' ) ";
			break;
		case '2':
			$sql_WHERE = " AND ( typeid >= '4' AND typeid <= '7' ) ";
			break;
		default:
			$sql_WHERE = " AND ( typeid >= '1' AND typeid <= '3' ) ";
			break;
	}
	$urlpara['z'] = "&z=".$z;
}


$sql = "SELECT * FROM `article` WHERE published='1' ". $sql_WHERE;
$allcs = loadCount2($sql);
$limit = 8;
require_once('function/page.class.php');
$page=new page(array('total'=>$allcs,'perpage'=>$limit));
if (!isset($PB_page)) $PB_page=1;
if($page->totalpage>1){
	$pagingDiv = $page->show(10);	
}
$tpl->assign('pagingDiv',$pagingDiv);

// $sql.= " ORDER BY sort DESC, date DESC, createdtime DESC LIMIT ".$page->offset.",".$page->limit;
$sql.= " ORDER BY set_top DESC, date DESC, createdtime DESC LIMIT ".$page->offset.",".$page->limit;
$datas = load_data($sql);
$datasCnt = count($datas);

foreach ($datas as $idx => $data){
	$datas[$idx]['type_name'] = $article_categ_disc[$data['typeid']];
	if($data['photo']!=""){
		$ext = substr($data['photo'], strrpos($data['photo'], '.') + 1);
		$filename = substr($data['photo'], 0, strrpos($data['photo'], '.'));
		$photo_thum = $filename."_thum.".$ext;
		$datas[$idx]['photo_src'] = "upload/article/".$photo_thum;

		$imagesize = getimagesize($datas[$idx]['photo_src']);
		$imgWidth = $imagesize[0];
		$imgHeight = $imagesize[1];
		
		$widthRatio = $imgWidth/235;
		$heightRatio = $imgHeight/177;
		if($widthRatio<$heightRatio){
			$datas[$idx]['newWidth'] = 235;	//$imgWidth*$heightRatio;
			$datas[$idx]['newHeight'] = $imgHeight/$widthRatio;
		}
		else{
			$datas[$idx]['newHeight'] = 177;	
			$datas[$idx]['newWidth'] = $imgWidth/$heightRatio;			
		}
	}
	if($datas[$idx]['date']!=""){
		// $createdtime_arr = explode(" ", $data['createdtime']);
		$date_arr = explode("-", $datas[$idx]['date']);
		$datas[$idx]['date2'] = sprintf("%s月%s, %s", $cnumber[$date_arr[1]*1], $date_arr[2], $date_arr[0]);	//str_replace("-", " / ", $createdtime_arr[0]);
	}

	if($datas[$idx]['content']!=""){
		$datas[$idx]['content2'] = strip_tags($datas[$idx]['content']) ;
	}
	// if($datas[$idx]['createdtime']!=""){
	// 	$createdtime_arr = explode(" ", $data['createdtime']);
	// 	$createddate_arr = explode("-", $createdtime_arr[0]);
	// 	$datas[$idx]['createddate'] = sprintf("%s月%s, %s", $cnumber[$createddate_arr[1]*1], $createddate_arr[2], $createddate_arr[0]);	//str_replace("-", " / ", $createdtime_arr[0]);
	// 	// echo date('Y年n月d日',strtotime('2012-11-12'));
	// }

	$datas[$idx]['views2'] = number_format($datas[$idx]['views']);
}
// $articles1 = load_data($sql1);
// foreach ($articles1 as $idx => $article) {
// 	$articles1[$idx][type_name] = $article_categ_disc[$article['typeid']];
// 	if($article['photo']!=""){
// 		$articles1[$idx][photoUrl] = "upload/article/".$article['photo'];
// 	}
// 	$articles1[$idx][subTitle] = strip_tags($article['content']);
// }
$tpl->assign("datas",$datas);
$tpl->assign("datasCnt",$datasCnt);
$tpl->assign("get",$_GET);
$tpl->assign("urlpara",$urlpara);
$tpl->assign("PB_page",$PB_page);
unset($datas);

if($_REQUEST[t]!=''){
	$section_display = $article_categ_disc[$_REQUEST[t]];
	$article_type_class[$_REQUEST[t]] = " class='active' ";

	$tpl->assign("section_display",$section_display);
	$tpl->assign("article_type_class",$article_type_class);
}

//--- 讀入Templates
$htmlfilename_array = explode(".",basename($_SERVER['SCRIPT_NAME']));
$_SESSION['now_page'] = $htmlfilename_array[0].".".$htmlfilename_array[1];
$htmlfilename = $theme_path.$htmlfilename_array[0].".html";
$tpl->display($htmlfilename);
?>