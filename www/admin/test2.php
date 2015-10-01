<?php
	exec('php /var/www/2009/cron/yandex_xml.php  > /dev/null &');
	exec('php /var/www/2009/cron/yandex_xml_ua.php  > /dev/null &');
	exec('php /var/www/2009/cron/export_yandex_xml.php  > /dev/null &');
	exec('php /var/www/2009/cron/export_yandex_xml_com.ua.php  > /dev/null &');
//	exec('php /var/www/2009/cron/export_yandex_xml_by.php  > /dev/null &');
	exec('php /var/www/2009/cron/export_yandex_xml_us.php  > /dev/null &');
//	exec('php /var/www/2009/cron/export_yandex_xml_eu.php  > /dev/null &');
	exec('php /var/www/2009/cron/export_yandex_xml_net.php  > /dev/null &');
//	exec('php /var/www/2009/cron/export_yandex_xml_kz.php  > /dev/null &');
?>
