<?php
if(!isset($_SESSION))	session_start();

//--- 讀入基本設定
include "function/function.php";
require 'libs/Smarty.class.php';
include "base_config.php";
$msql = new phpMysql_h;
$tool = new tools_h;

//--- 所屬功能
if($id){
	$sqlWhere = " WHERE published='1' AND id=".$id;
	$row = loaddata_row("article", $sqlWhere);

	if($row){
		$sql = "UPDATE `article` SET views=views+1 WHERE id=".$row['id'];
		$msql->init();
		$msql->query($sql);
	}
	else{
		// echo "<script>alert('請指定菜單');</script>";
		$tool->showmessage('無此文章!');
		$tool->goBack();
		exit;
	}	
}
else{
	// echo "<script>alert('請指定菜單');</script>";
	$tool->showmessage('無此文章!');
	$tool->goBack();
	exit;
}

if(isset($row)){
	$data = $row;
	$data['type_name'] = $article_categ_disc[$data['typeid']];
	if($data['photo']!=""){
		// $ext = substr($data['photo'], strrpos($data['photo'], '.') + 1);
		// $filename = substr($data['photo'], 0, strrpos($data['photo'], '.'));
		// $photo_thum = $filename."_thum.".$ext;
		// $data['photo_src'] = "upload/article/".$photo_thum;
		$data['photo_src'] = "upload/article/".$data['photo'];
	}
	if($data['date']!=""){
		// $createdtime_arr = explode(" ", $data['createdtime']);
		// $createddate_arr = explode("-", $createdtime_arr[0]);
		$date_arr = explode("-", $data['date']);
		$data['date2'] = sprintf("%s月%s, %s", $cnumber[$date_arr[1]*1], $date_arr[2], $date_arr[0]);		//str_replace("-", " / ", $createdtime_arr[0]);
	}

	$data['views2'] = number_format($data['views']);
}

// var_dump($_REQUEST);
// exit;

$PB_page = $PB_page==""?1:$PB_page;
$article_url = 'article_list.php?PB_page='.$PB_page.($_REQUEST[z]!=''?'&z='.$_REQUEST[z]:'').($_REQUEST[t]!=''?'&t='.$_REQUEST[t]:'');
$tpl->assign("data",$data);
$tpl->assign("get",$_GET);
$tpl->assign("article_url",$article_url);
$tpl->assign("PB_page",$PB_page);


if($_REQUEST[t]!=''){
	$section_display = $article_categ_disc[$_REQUEST[t]];
	$article_type_class[$_REQUEST[t]] = " class='active' ";
	$urlpara['t'] = "&t=".$t;

	$tpl->assign("section_display",$section_display);
	$tpl->assign("article_type_class",$article_type_class);
}
if($_REQUEST[z]!=''){
	$urlpara['z'] = "&z=".$z;
}
$tpl->assign("urlpara",$urlpara);

//--- 讀入Templates
// $htmlfilename_array = explode(".",basename($_SERVER['SCRIPT_NAME']));
$htmlfilename = $theme_path.$htmlfilename_array[0].".html";
$htmlfilename_array = explode(".",basename($_SERVER['SCRIPT_NAME']));
$_SESSION['now_page'] = $htmlfilename_array[0].".".$htmlfilename_array[1];
$tpl->display($htmlfilename);
?>