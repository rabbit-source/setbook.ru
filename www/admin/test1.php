<?php
	chdir('/var/www/2009/');
	require('includes/application_top.php');
	include('cron/include/logs.class.php');
	include('cron/include/yandex.functions.php');


	include('/var/www/2009/cron/export_yandex_xml.php');
	include('/var/www/2009/cron/export_yandex_xml_com.ua.php');
	include('/var/www/2009/cron/export_yandex_xml_by.php');
	include('/var/www/2009/cron/export_yandex_xml_eu.php');
	include('/var/www/2009/cron/export_yandex_xml_us.php');
	include('/var/www/2009/cron/export_yandex_xml_net.php');
?>
