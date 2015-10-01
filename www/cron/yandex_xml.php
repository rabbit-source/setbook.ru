<?php
////////////////////////////////////////////////////
//Внимание скрипт это скрипт выгрузки только для .RU
////////////////////////////////////////////////////
set_time_limit(0);
chdir('/var/www/2009/');
require('includes/application_top.php');
include('cron/include/logs.class.php');
include('cron/include/yandex.php');
$logs = new logs('cron/logs/export_yandex_xml.log');

$GLOBAL_CRON = true;

$logs->write("------------- START --------------");


echo "RUN!!!!!";

/*
	Общая выгрузка
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_ru_all.xml

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?$HTTP_GET_VARS['type']:'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

/*
	Выгрузка электронных книг
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта, жеско прописаны и выгрузка по типу
	unset($HTTP_GET_VARS);
	$HTTP_GET_VARS['type'] = "ebooks";
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru_ebooks.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_ru_ebooks.xml

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?strtoupper($HTTP_GET_VARS['type']):'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

/*
	Выгрузка электроника
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта, жеско прописаны и выгрузка по типу
	unset($HTTP_GET_VARS);
	$HTTP_GET_VARS['type'] = "electronics";
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru_electronics.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_ru_electronics.xml

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?strtoupper($HTTP_GET_VARS['type']):'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

/*
	Выгрузка детская литература
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта, жеско прописаны и выгрузка по типу
	unset($HTTP_GET_VARS);
	$HTTP_GET_VARS['type'] = "for_children";
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru_for_children.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_ru_for_children.xml

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?strtoupper($HTTP_GET_VARS['type']):'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

/*
	Выгрузка сувениры
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта, жеско прописаны и выгрузка по типу
	unset($HTTP_GET_VARS);
	$HTTP_GET_VARS['type'] = "souvenirs";
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru_souvenirs.xml";
	//http://www.setbook.ru/prices/yandex_xml/yandex_ru_souvenirs.xml

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?strtoupper($HTTP_GET_VARS['type']):'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);

/*
	Выгрузка периодика
*/
	//из-за того что не выгружается файл по крон, прописываем жестко пути для русского сайта, жеско прописаны и выгрузка по типу
	unset($HTTP_GET_VARS);
	$HTTP_GET_VARS['type'] = "periodicals";
	$HTTP_GET_VARS['file'] = "/var/www/2009/prices/yandex_xml/yandex_ru_periodicals.xml";

	$logs->write("START [RU, ".(isset($HTTP_GET_VARS['type'])?strtoupper($HTTP_GET_VARS['type']):'ALL')."] ".$HTTP_GET_VARS['file']);

	$res = generate($HTTP_GET_VARS);
	$logs->write("STAT Categories: ".$res['Categories'].", Offers: ".$res['Offers'].", Filesize: ".Round(filesize($HTTP_GET_VARS['file'])/1024/1024, 2)."Mb");
	$logs->write("END ".$HTTP_GET_VARS['file']);


unset($logs);



?>