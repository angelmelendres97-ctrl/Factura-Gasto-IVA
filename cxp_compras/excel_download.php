<?php
session_start();

$sHtml_cab=$_SESSION['Html_rep_excel'];
//$sHtml_det=$_SESSION['sHtml_det'];


/*
header("Pragma: public");
header("Expires: 0");
$filename = "excel.xls";
header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
*/

$file="excel.xls";
header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Disposition: attachment; filename=$file");

echo $sHtml_cab;

//unset($_SESSION['sHtml_cab']);
// echo $sHtml_det;
//echo 'ruben santacruz';
//unset($_SESSION['sHtml_det']);
?>
