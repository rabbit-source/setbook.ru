<?php
set_time_limit(0);
chdir('/var/www/2009/');
if(count($argv) == 0) exit;
require('includes/application_top.php');

require('includes/mailru_api.php');
require('includes/subscribe.php');

$subscribe = new subscribe;
$mailAPI = new Mailru_API;
$mailAPI->status_loger = 0;
$mailAPI->log_path = 'logs/mail_cron.log';

  //1: Тематики и разделы
	$categories_query = tep_db_query("SELECT s.category_id as id, c.categories_name as name FROM subscribe s
	LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." AS c ON c.categories_id = s.category_id
	WHERE s.type_id = 1
	AND c.language_id = 2
	GROUP BY id;");
	$subscribe->type = 1;
	$subscribe->get_new_products_list($categories_query, &$mailAPI);
	//2: Серии
	$categories_query = tep_db_query("SELECT s.category_id as id, c.series_name as name FROM subscribe s
	LEFT JOIN ".TABLE_SERIES." AS c ON c.series_id = s.category_id
	WHERE s.type_id = 2
	AND c.language_id = 2
	GROUP BY id;");
	$subscribe->type = 2;
	$subscribe->get_new_products_list($categories_query, &$mailAPI);
	//3: Авторы
	$categories_query = tep_db_query("SELECT s.category_id as id, c.authors_name as name FROM subscribe s
	LEFT JOIN ".TABLE_AUTHORS." AS c ON c.authors_id = s.category_id
	WHERE s.type_id = 3
	AND c.language_id = 2
	GROUP BY id;");
	$subscribe->type = 3;
	$subscribe->get_new_products_list($categories_query, &$mailAPI);
	//4: Издательство
	$categories_query = tep_db_query("SELECT s.category_id as id, c.manufacturers_name as name FROM subscribe s
	LEFT JOIN ".TABLE_MANUFACTURERS_INFO." AS c ON c.manufacturers_id = s.category_id
	WHERE s.type_id = 4
	AND c.languages_id = 2
	GROUP BY id;");
	$subscribe->type = 4;
	$subscribe->get_new_products_list($categories_query, &$mailAPI);
	
	
?>














