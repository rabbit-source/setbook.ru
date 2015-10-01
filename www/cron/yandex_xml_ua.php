<?php
/////////////////////////////////////////////////////
//Внимание скрипт это скрипт выгрузки только для .COM.UA
/////////////////////////////////////////////////////
set_time_limit(0);

define("HTTP_SERVER", "http://www.setbook.com.ua");

chdir('/var/www/2009/');
require('includes/application_top.php');
include('cron/include/logs.class.php');
include('cron/include/yandex.php');
$logs = new logs('cron/logs/export_yandex_xml.log');

$GLOBAL_CRON = true;

$logs->write("------------- START --------------");


/*
	Общая выгрузка
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ua.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_com.ua.xml
	
	$logs->write("START [COM.UA, ".(isset($HTTP_GET_VARS['type'])?$HTTP_GET_VARS['type']:'ALL')."] ".$HTTP_GET_VARS['file']);
	
	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

unset($logs);


?>