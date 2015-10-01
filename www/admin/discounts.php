<?php
  require('includes/application_top.php');

  die();
  tep_set_time_limit(6000);
  $k = 0;
  $products_query = tep_db_query("select products_id, categories_id from " . TABLE_TEMP_PRODUCTS_INFO . " where 1");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_id = $products['products_id'];
	$categories_id = $products['categories_id'];
	$category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id > '0' order by categories_id limit 1");
	$category = tep_db_fetch_array($category_query);
	if (!is_array($category)) $category = array();
	if ($category['categories_id']!=$categories_id && (int)$category['categories_id']>0) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS_INFO . " set categories_name = '" . tep_db_input(tep_get_category_name($category['categories_id'], $languages_id)) . "', categories_id = '" . (int)$category['categories_id'] . "' where products_id = '" . (int)$products_id . "'");
	  $k ++;
	}
  }
  echo 'updated: ' . $k . "\n";


  die;
  tep_set_time_limit(36000);

  tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1' and products_name = ''");
  $fields = array('products_name', 'products_description');
  $products_query = tep_db_query("select products_id, products_name, products_description, products_model, authors_name, categories_name, manufacturers_name, series_name from " . TABLE_PRODUCTS_INFO . " where products_id not in (select products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1') order by rand()");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_id = $products['products_id'];
	$check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '1'");
	$check = tep_db_fetch_array($check_query);
	if ($check['total']==0) {
	  reset($fields);
	  $products_name = '';
	  $products_description = '';
	  while (list(, $field) = each($fields)) {
		if (tep_not_null($products[$field])) {
		  ${$field} = tep_get_translation($products[$field]);
		} else {
		  ${$field} = '';
		}
	  }

	  $products_text = $products_name;
	  $products_model = $products['products_model'];
	  $authors_name = tep_transliterate($products['authors_name']);
	  $manufacturers_name = tep_transliterate($products['manufacturers_name']);
	  $product_serie_info_query = tep_db_query("select series_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	  $product_serie_info = tep_db_fetch_array($product_serie_info_query);
	  $serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$product_serie_info['series_id'] . "' and language_id = '1'");
	  $serie_info = tep_db_fetch_array($serie_info_query);
	  $series_name = $serie_info['series_name'];

	  if (strlen($authors_name) > 2) $products_text .= ' by ' . $authors_name;
	  if (strlen($manufacturers_name) > 2) $products_text .= ' publisher ' . $manufacturers_name;
	  if (strlen($series_name) > 2) $products_text .= ' serie ' . $series_name;
	  $products_text = strip_tags(strtolower(html_entity_decode($products_text)));
	  $products_text = str_replace(array('«', '»', '+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—', '_', '-', ':', '#', '\\', '|', '`', '~', '$', '^'), ' ', $products_text);
	  $products_text = trim(preg_replace('/\s{2,}/', ' ', $products_text));
	  if (tep_not_null($products_model)) $products_text .= ' ISBN ' . $products_model;

	  $sql = "insert ignore into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, products_name, products_description, products_text, language_id) values ('" . (int)$products_id . "', '" . tep_db_input($products_name) . "', '" . tep_db_input($products_description) . "', ' " . tep_db_input(trim($products_text)) . " ', '1')";
	  tep_db_query($sql);
	}
  }

  die;
  $mailed = mail('dmitry@easternowl.com', 'test subject', 'test message', '', '');echo ($mailed ? 'ok' : 'failed');
  die;
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  include(DIR_WS_CLASSES . 'order.php');
  tep_upload_order(86322, ',', UPLOAD_DIR . 'temp_orders/');

  die;
  tep_set_time_limit(300);
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

	$temp_currencies = array();
	$filename_currencies_gz = UPLOAD_DIR . 'CSV/kurs.csv.gz';
	$filename_currencies = str_replace('.gz', '', $filename_currencies_gz);
	if (file_exists($filename_currencies_gz)) {
	  $gz = @gzopen($filename_currencies_gz, 'r');
	  $ff = @fopen($filename_currencies, 'w');
	  if ($gz && $ff) {
		while ($string = gzgets($gz, 1024)) {
		  fwrite($ff, $string);
		}
		fclose($ff);
		gzclose($gz);
	  } elseif (file_exists($filename_currencies)) {
		@unlink($filename_currencies);
	  }
	}
	if (file_exists($filename_currencies)) {
	  $fp = fopen($filename_currencies, 'r');
	  while ((list($currency_code, $currency_value) = fgetcsv($fp, 64, ';')) !== FALSE) {
		if ((float)$currency_value > 0) {
		  $temp_currencies[$currency_code] = str_replace(',', '.', trim($currency_value));
		}
	  }
	  fclose($fp);
	  unlink($filename_currencies);
	}
	if (sizeof($temp_currencies)==0) {
	  reset($currencies);
	  while (list($currency_code, $currency_info) = each($currencies)) {
		$temp_currencies[$currency_code] = $currency_info['value'];
	  }
	}

  tep_update_shops_prices(4, '', $table = 'temp');

  die;
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  tep_set_time_limit(300);
  tep_update_shops_prices(4, '', 'temp');

  die;
  tep_set_time_limit(300);
	// сортировка по умолчанию (сначала спецпредложения с картинками, потом новинки, потом книги с картинками, потом все остальное)
	$max_specials_date_query = tep_db_query("select max(specials_date_added) as specials_date_added from " . TABLE_SPECIALS . " where status = '1'");
	$max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
	$max_specials_date = strtotime($max_specials_date_row['specials_date_added']);
	$min_specials_date_added = date('Y-m-d H:i:s', $max_specials_date-60*60*24*7);
	$query = tep_db_query("select p.products_id, (if(p.products_listing_status=1, 8, 0) + if(s.specials_types_id, if(s.specials_types_id=1, 4, if(s.specials_types_id=2, 3, 0)), 0) + if(p.products_image_exists=1, 2, 0)) as new_sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on (s.products_id = p.products_id and s.specials_types_id in ('1', '2') and s.specials_date_added >= '" . tep_db_input($min_specials_date_added) . "') where 1 order by new_sort_order desc");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
//	  tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
	  $s ++;
//	  echo $row['products_id'] . ' - ' . $row['new_sort_order'] . ' - ' . tep_get_products_name($row['products_id']) . '<br>' . "\n";
	}


  die;
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  include(DIR_WS_CLASSES . 'order.php');

  tep_set_time_limit(36000);
  $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where date_purchased >= '2011-05-17 00:00:00'");
  while ($orders = tep_db_fetch_array($orders_query)) {
	tep_upload_order($orders['orders_id']);
  }



  die;
  tep_db_select_db('setbook_ua');

  $geozone_id = 1;
  $zone_country_id = 178;
  $zone_factor = '';
  $zone_delivery_time = '';
  $new_geozone_id = 2;

  tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$geozone_id . "'");
  tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$geozone_id . "'");

  $zones_query = tep_db_query("select * from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$new_geozone_id . "'");
  while ($zones = tep_db_fetch_array($zones_query)) {
	tep_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, geo_zone_id, zone_factor, zone_delivery_time, date_added) values ('" . (int)$zone_country_id . "', '" . (int)$zones['zone_id'] . "', '" . (int)$geozone_id . "', '" . (double)$zone_factor . "', '" . tep_db_prepare_input($zone_delivery_time) . "', now())");
	$new_subzone_id = tep_db_insert_id();

	tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, geo_zone_id, date_added) select city_id, '" . (int)$new_subzone_id . "', '" . (int)$geozone_id . "', now() from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$new_geozone_id . "' and association_id = '" . (int)$zones['association_id'] . "'");
  }



  die;
  tep_db_select_db('setbook_ua');
  tep_set_time_limit(300);
  $fp = fopen(UPLOAD_DIR . 'csv/ua_postcodes.csv', 'r');
  while ((list($city_id, $city_name, $suburb_name, , $parent_id, $old_id, $zone_id, $zone_name, $city_country_id, $city_delivery_days) = fgetcsv($fp, 1024, ';')) !== FALSE) {
	$city_id = sprintf('%05d', trim($city_id));
	$city_check_query = tep_db_query("select 1 from " . TABLE_CITIES . " where city_id = '" . tep_db_input($city_id) . "'");
	if (tep_db_num_rows($city_check_query) < 1 && tep_not_null($city_name)) {
	  $sql_data_array = array('city_id' => $city_id,
							  'city_name' => $city_name,
							  'suburb_name' => $suburb_name,
							  'parent_id' => $parent_id,
							  'old_id' => $old_id,
							  'zone_id' => $zone_id,
							  'zone_name' => $zone_name,
							  'city_country_id' => $city_country_id,
							  'city_delivery_days' => $city_delivery_days,
							  );
	  tep_db_perform(TABLE_CITIES, $sql_data_array);
	}
  }
  fclose($fp);

  die;
	tep_db_select_db('setbook_ua');
	$cities_query = tep_db_query("select city_id, count(*) as total from cities group by city_id having total > 1 order by total desc");
	while ($cities = tep_db_fetch_array($cities_query)) {
	  $k = 0;
	  $duplicate_cities_query = tep_db_query("select * from cities where city_id = '" . tep_db_input($cities['city_id']) . "' order by suburb_name, city_name");
	  while ($duplicate_cities = tep_db_fetch_array($duplicate_cities_query)) {
		if ($k > 0) {
		  echo "<br>" , $sql = "delete from cities where city_id = '" . tep_db_input($duplicate_cities['city_id']) . "' and city_name = '" . tep_db_input($duplicate_cities['city_name']) . "' and suburb_name = '" . tep_db_input($duplicate_cities['suburb_name']) . "' and parent_id = '" . tep_db_input($duplicate_cities['']) . "' and zone_id = '" . tep_db_input($duplicate_cities['zone_id']) . "' and zone_name = '" . tep_db_input($duplicate_cities['zone_name']) . "'";
		  tep_db_query($sql);
		}
		$k ++;
	  }
	}

  die;
  tep_db_query("select * from customers into outfile '/var/www/2009/images/customers.sql'");

  die;
	tep_db_select_db('setbook_net');
	tep_set_time_limit(300);
	// сортировка по умолчанию (сначала спецпредложения с картинками, потом новинки, потом книги с картинками, потом все остальное)
	$max_specials_types_id_query = tep_db_query("select max(specials_types_id) as max_specials_types_id from " . TABLE_SPECIALS_TYPES . "");
	$max_specials_types_id_row = tep_db_fetch_array($max_specials_types_id_query);
	$max_specials_types_id = $max_specials_types_id_row['max_specials_types_id'];
	$max_specials_date_query = tep_db_query("select max(specials_date_added) as specials_date_added from " . TABLE_SPECIALS . " where status = '1'");
	$max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
	$max_specials_date = strtotime($max_specials_date_row['specials_date_added']);
	$min_specials_date_added = date('Y-m-d', $max_specials_date-60*60*24*7);
	$query = tep_db_query("select p.products_id, (p.products_listing_status*" . (int)$max_specials_types_id . "*2 + p.products_image_exists + if((s.specials_types_id > 0), (" . (int)$max_specials_types_id . " + 2 - s.specials_types_id), 0)) as new_sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on (s.products_id = p.products_id and date_format(s.specials_date_added, '%Y-%m-%d') >= '" . tep_db_input($min_specials_date_added) . "') where 1 order by new_sort_order desc, products_price");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
	  $s ++;
	}
	echo "\nOK\n\n";


  die;
	tep_db_select_db('setbook_org');
	$delivery_country_id = '228';
	$shipping_weight = '2.0';
	$country_check_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$delivery_country_id . "'");
	$country_check = tep_db_fetch_array($country_check_query);
	$delivery_country_name = strtolower(trim($country_check['countries_name']));
	$country_found = false;
	$usps_country_id = 0;
	$fp = fopen(UPLOAD_DIR . 'csv/usps_countries.csv', 'r');
	while ((list($u_countries_id, $u_countries_name) = fgetcsv($fp, 100, ';')) !== FALSE) {
	  $u_countries_name = str_replace(array('united states', 'bosnia-herzegovina', 'laos', 'vietnam'), array('usa', 'bosnia and herzegowina', 'lao people\'s democratic republic', 'viet nam'), strtolower(trim($u_countries_name)));
	  if (strpos($u_countries_name, $delivery_country_name)!==false) {
		$country_found = true;
		$usps_country_id = trim($u_countries_id);
		break;
	  }
	}
	fclose($fp);

	$weight_ounces = round($shipping_weight * 1000 * 453.7 * 16);
	$url = 'http://ircalc.usps.gov/MailServices.aspx?country=' . $usps_country_id . '&m=6&p=0&o=' . $weight_ounces;
	$data_array = array('ctl00_ToolkitScriptManager1_HiddenField' => 'ctl00_ToolkitScriptManager1_HiddenField',
						'ScriptMailProperties' => substr($url, strpos($url, '?')+1),
						'__LASTFOCUS' => '',
						'__VIEWSTATE' => '',
						'__EVENTTARGET' => 'ctl00$ContentPlaceHolder1$CheckBoxDisplayAllOptions',
						'__EVENTARGUMENT' => '');
	echo $content = tep_request_html($url, 'GET');
	preg_match('/<table class="GridViewTable"[^>]*>(.*)<\/table>/i', $content, $regs);
	echo $regs[1];

	tep_db_select_db(DDB_DATABASE);
	echo 'OK';


  die;
	tep_set_time_limit(300);
	tep_db_select_db('setbook_us');
	$max_specials_types_id_query = tep_db_query("select max(specials_types_id) as max_specials_types_id from " . TABLE_SPECIALS_TYPES . "");
	$max_specials_types_id_row = tep_db_fetch_array($max_specials_types_id_query);
	$max_specials_types_id = $max_specials_types_id_row['max_specials_types_id'];
	$max_specials_date_query = tep_db_query("select max(specials_date_added) as specials_date_added from " . TABLE_SPECIALS . " where status = '1'");
	$max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
	$max_specials_date = strtotime($max_specials_date_row['specials_date_added']);
	$min_specials_date_added = date('Y-m-d', $max_specials_date-60*60*24*7);
	$query = tep_db_query("select p.products_id, (p.products_listing_status*5 + p.products_image_exists*3 + if((s.specials_types_id > 0), (" . (int)$max_specials_types_id . " - s.specials_types_id), 0)) as new_sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on (s.products_id = p.products_id and date_format(s.specials_date_added, '%Y-%m-%d') >= '" . tep_db_input($min_specials_date_added) . "') where 1 order by new_sort_order desc, products_price");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
	  $s ++;
	}
	tep_db_select_db(DDB_DATABASE);
	echo 'OK';

  die;
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  tep_set_time_limit(36000);
  $temp_tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_INFO, TABLE_SPECIALS);
  $in_shops = array(9, 10, 11, 14, 1);
  tep_update_all_shops(1);


  die;
  tep_set_time_limit(600);
  $products_invalid_images = array();
  $products_query = tep_db_query("select products_id, products_code, products_image from " . TABLE_PRODUCTS . " where products_types_id = '1' and products_image_exists = '1'");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_code = str_replace('bbk', '', $products['products_code']);
	$products_original_image = UPLOAD_DIR . 'books/' . substr($products_code, 0, -2) . '/' . $products_code . '.jpg';
	if (!file_exists($products_original_image)) {
	  $products_invalid_images[] = $products['products_id'];
	  unlink(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $products['products_image']);
	  unlink(DIR_FS_CATALOG_IMAGES_BIG . $products['products_image']);
	}
  }
  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
  while ($shops = tep_db_fetch_array($shops_query)) {
	tep_db_select_db($shops['shops_database']);
	reset($products_invalid_images);
	while (list($i, $products_invalid_image) = each($products_invalid_images)) {
	  tep_db_query("update " . TABLE_PRODUCTS . " set products_image = '', products_image_exists = '0' where products_id = '" . (int)$products_invalid_image . "'");
	  tep_db_query("update " . TABLE_PRODUCTS_INFO . " set products_image = '' where products_id = '" . (int)$products_invalid_image . "'");
	}
	tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '1'");
  }
  tep_db_select_db(DB_DATABASE);
  print_r($products_invalid_images);

  die;
  tep_set_time_limit(600);
  $maxlength = 0;
  $filename = UPLOAD_DIR . 'CSV/Books.csv';
  $fp = fopen($filename, 'r');
  while (($cell = fgetcsv($fp, 40000, ';')) !== FALSE) {
	$string_length = strlen(implode(';', $cell)) + 50;
	if ($string_length > $maxlength) $maxlength = $string_length;
  }
  fclose($fp);
  echo $maxlength;

  die;
  tep_set_time_limit(600);
  $filename = UPLOAD_DIR . 'CSV/Books.csv';
  $fp = fopen($filename, 'r');
  while (($cell = fgetcsv($fp, 32, ';')) !== FALSE) {
	list($original_products_code) = $cell;

	if ($original_products_code > 0) {
	  $products_md5_sum = md5(serialize($cell));

	  $products_code = 'bbk' . sprintf('%010d', (int)$original_products_code);
	  $products_check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_code = '" . tep_db_input($products_code) . "' and products_types_id = '1'");
	  $products_check = tep_db_fetch_array($products_check_query);

	  tep_db_query("update " . TABLE_PRODUCTS . " set products_md5_sum = '" . tep_db_input($products_md5_sum) . "' where products_id = '" . $products_check['products_id'] . "'");
	}
  }
  fclose($fp);

  $cell_1 = array('1', '100 любимых маленьких сказок', '978-5-17-043539-5', '324', '', '160', '2007', 'Твердый переплет', '265x410', 'Расскажите малышу сказку, и вы станете для него лучшим другом. А по просьбе лучшего друга малыш утром доест всю кашу, днём перестанет плакать и капризничать, а вечером без лишних уговоров отправится в постель. Выбирайте в нашей книге из множества сказок любую – все они одна лучше другой!', '752,00', '15000', '1258', '186440', '12201', '70', '', '', '', '', '', '', '1', '', '5', '', '324', '216', '');
  $cell_2 = array('1', '100 любимых маленьких сказок', '978-5-17-043539-5', '324', '', '160', '2007', 'Твердый переплет', '265x410', 'Расскажите малышу сказку, и вы станете для него лучшим другом. А по просьбе лучшего друга малыш утром доест всю кашу, днём перестанет плакать и капризничать, а вечером без лишних уговоров отправится в постель. Выбирайте в нашей книге из множества сказок любую – все они одна лучше другой!', '752,00', '15000', '1258', '186440', '12201', '70', '', '', '', '', '', '', '1', '', '5', '', '324', '216', '');
  $s_1 = md5(serialize($cell_1));
  $s_2 = md5(serialize($cell_2));
  echo 'md5_1 = "' . $s_1 . '"; md5_2 = "' . $s_2 . '"; equal = "' . ($s_1===$s_2 ? 'yes' : 'no') . '";';

  die;
  $host = 'sftp.barnesandnoble.com';
  $port = '21';
  $user = 'BNA0194750';
  $pwd = 'UU7l%jE';
/*
  $ssh = ssh2_connect($host) or die('cannt connect to ' . $host . ' on port ' . $port);
  ssh2_auth_password($ssh, $user, $pwd) or die('cannt connect using ' . $user);
  $sftp = ssh2_sftp($ssh);
  $ff = fopen('ssh2.sftp://' . $sftp . '/Orders/Orders_to_pickup', 'r');
  fclose($ff);

  $fp = ftp_ssl_connect($host, $port, 30) or die('cannt connect to ' . $host . ' on port ' . $port);
  $login_result = ftp_login($fp, $user, $pwd) or die('cannt connect using ' . $user);
  echo ftp_pwd($fp);
  ftp_close($fp);
*/

  $opt_array = array(CURLOPT_URL => 'sftp://' . $host . '/Orders/Orders_to_pickup/',
					 CURLOPT_USERPWD => $user . ':' . $pwd,
					 CURLOPT_FTPPORT => $port,
					 CURLOPT_RETURNTRANSFER => 1);

  $ch = curl_init();
  curl_setopt_array($ch, ($opt_array + array(CURLOPT_FTPLISTONLY => 1)));
  $result = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);
  if (!$error) {
	$ch = curl_init();
	$opt_array[CURLOPT_URL] .= trim($result);
	curl_setopt_array($ch, $opt_array);
	$result = curl_exec($ch);
	$error = curl_error($ch);
	echo '<pre>' . $result;
  } else {
	echo $error;
  }

  die;

  $product_info_query = tep_db_query("select products_id, products_filename from " . TABLE_PRODUCTS . " where products_id = '997715'");
  $product_info = tep_db_fetch_array($product_info_query);
  $product_file_path = DIR_FS_DOWNLOAD . substr(sprintf('%010d', $product_info['products_id']), 0, 6) . '/' . substr(sprintf('%010d', $product_info['products_id']), 0, 8) . '/' . trim($product_info['products_filename']);
  if (file_exists($product_file_path)) {
	$image_path = DIR_FS_CATALOG . 'images/news/test.jpg';
	$file_content = implode('', file($product_file_path));
	if (preg_match('/<binary[^>]*>([^<]+)<\/binary>/i', $file_content, $regs)) {
//	echo $regs[1]; die;
	  $fp = fopen($image_path, 'w');
	  fwrite($fp, base64_decode($regs[1]));
	  fclose($fp);
	  header('Content-type: image/jpeg');
	  readfile($image_path);
//	  unlink($image_path);
	}
  }

	die;
	$max_country_id = 0;
	$all_countries = array();
	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $countries_query = tep_db_query("select countries_id, countries_ru_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "'");
	  while ($countries = tep_db_fetch_array($countries_query)) {
		$all_countries[$countries['countries_iso_code_2']] = $countries['countries_ru_name'];
		if ($countries['countries_id'] > $max_country_id) $max_country_id = $countries['countries_id'];
	  }
	}
	tep_db_select_db(DB_DATABASE);
	echo $max_country_id;
	echo '<pre>' . print_r($all_countries, true) . '</pre>';

	die;
  tep_set_time_limit(6000);
	$letter_subject = 'Книжный магазин %s поздравляет Вас с Новым годом!';
	$letter_text = implode('', file('../new_year.html'));

	tep_set_time_limit(36000);

	$shops_array = array();
	$shops_query = tep_db_query("select shops_id, shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $shop_name_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
	  $shop_name = tep_db_fetch_array($shop_name_query);
	  $shop_email_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
	  $shop_email = tep_db_fetch_array($shop_email_query);
	  $shops_array[$shops['shops_id']] = array('title' => $shop_name['configuration_value'], 'email' => $shop_email['configuration_value']);
	}
	tep_db_select_db(DB_DATABASE);

	$query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname, shops_id from " . TABLE_CUSTOMERS . " where 1 order by customers_id");
	while ($rows = tep_db_fetch_array($query)) {
	  $to_name = $rows['customers_firstname'];
	  $to_full_name = trim($rows['customers_firstname'] . ' ' . $rows['customers_lastname']);
	  $to_email = $rows['customers_email_address'];

	  $from_name = $shops_array[$rows['shops_id']]['title'];
	  $from_email = $shops_array[$rows['shops_id']]['email'];

	  $email_subject = sprintf($letter_subject, $from_name);
	  $email_text = sprintf($letter_text, $to_name, $from_name, $from_name);

	  $message = new email(array('X-Mailer: ' . $from_name));
	  $text = strip_tags($email_text);
      $message->add_html($email_text, $text, DIR_FS_CATALOG_IMAGES . 'Image/');
//	  print_r($message->html_images);

	  $message->build_message();
	  $message->send($to_full_name, $to_email, $from_name, $from_email, $email_subject);
	}
	echo '<script>alert("Готово!");</script>';

  die;
  $products_array = '';
  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '10' and (products_filename = '' or products_filename is null)");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_array[] = $products['products_id'];
  }
  print_r($products_array);
//  tep_remove_product($products_array);

  die('ready');

  tep_set_time_limit(600);
  $dir = DIR_FS_CATALOG_IMAGES . 'prints';
  $dir_objects = scandir($dir);
  while (list(, $dir_object) = each($dir_objects)) {
	if ($dir_object!='.' && $dir_object!='..' && is_dir($dir . '/' . $dir_object)) {
	  $dir1 = $dir . '/' . $dir_object;
	  $dir1_objects = scandir($dir1);
	  if (sizeof($dir1_objects) > 2) {
		while (list(, $dir1_object) = each($dir1_objects)) {
		  if ($dir1_object!='.' && $dir1_object!='..' && is_dir($dir1 . '/' . $dir1_object)) {
			$dir2 = $dir1 . '/' . $dir1_object;
			$dir2_objects = scandir($dir2);
			if (sizeof($dir2_objects) > 3) {
//			  echo '<br>' . sizeof($dir2_objects);
			  while (list(, $dir2_object) = each($dir2_objects)) {
				if ($dir2_object!='.' && $dir2_object!='..') {
				  if (!is_dir($dir2 . '/' . $dir2_object)) {
					$product_image_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where products_images_image = '" . tep_db_input($dir2_object) . "'");
					$product_image_check = tep_db_fetch_array($product_image_check_query);
					if ($product_image_check['total'] < 1) {
					  echo '<br>' . $dir2 . '/' . $dir2_object;
					  unlink($dir2 . '/' . $dir2_object);
					  unlink($dir2 . '/thumbs/' . $dir2_object);
					}
				  }
				}
			  }
//			  die;
			} else {
			  rmdir($dir2 . '/thumbs');
			  rmdir($dir2);
			}
		  }
		}
	  } else {
		echo '<br>' . $dir1;
		rmdir($dir1);
	  }
	}
  }
//  rmdir(DIR_FS_CATALOG_IMAGES . 'prints/00/000015/thumbs');
//  rmdir(DIR_FS_CATALOG_IMAGES . 'prints/00/000015');
  die;

  $original_products_code = "13";
  $original_products_code = sprintf('%010d', (int)$original_products_code);
  $products_files_dir = UPLOAD_DIR . 'ElKnigi/' . substr($original_products_code, 0, 8) . '/';
  if (file_exists($products_files_dir . $original_products_code . '_1.jpg')) {
	$products_images = array();
	$j = 1;
	while (file_exists($products_files_dir . $original_products_code . '_' . $j . '.jpg')) {
	  $products_images[] = $original_products_code . '_' . $j . '.jpg';
	  $j ++;
	}
  }
  print_r($products_images);
  die;

  tep_set_time_limit(600);
  $orders_products_query = tep_db_query("select orders_products_id, products_id from " . TABLE_ORDERS_PRODUCTS . " where products_types_id > '1'");
  while ($products = tep_db_fetch_array($orders_products_query)) {
	$product_info_query = tep_db_query("select products_code, products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products['products_id'] . "'");
	if (tep_db_num_rows($product_info_query) > 0) {
	  $product_info = tep_db_fetch_array($product_info_query);
//	  die("update " . TABLE_ORDERS_PRODUCTS . " set products_code = '" . tep_db_input($product_info['products_code']) . "', products_model = '" . tep_db_input($product_info['products_model']) . "' where orders_products_id = '" . (int)$products['orders_products_id'] . "'");
	  tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set products_code = '" . tep_db_input($product_info['products_code']) . "', products_model = '" . tep_db_input($product_info['products_model']) . "' where orders_products_id = '" . (int)$products['orders_products_id'] . "'");
	}
  }
  die('done');

  tep_set_time_limit(600);
  $shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
  while ($shops = tep_db_fetch_array($shops_query)) {
	tep_db_select_db($shops['shops_database']);
	$products_query = tep_db_query("select products_id, products_model from " . TABLE_PRODUCTS . " where products_types_id > '1'");
	while ($products = tep_db_fetch_array($products_query)) {
	  $products_code = 'bbk' . sprintf('%010d', (int)$products['products_id']);
	  $products_model_1 = (int)preg_replace('/[^\d]/', '', $products['products_model']);
	  tep_db_query("update " . TABLE_PRODUCTS . " set products_code = '" . tep_db_input($products_code) . "', products_model_1 = '" . tep_db_input($products_model_1) . "' where products_id = '" . (int)$products['products_id'] . "'");
	  tep_db_query("update " . TABLE_PRODUCTS_INFO . " set products_code = '" . tep_db_input($products_code) . "' where products_id = '" . (int)$products['products_id'] . "'");
	}
  }
  tep_db_select_db(DB_DATABASE);
  die('done');

  tep_set_time_limit(600);
  $products_to_remove = array();
  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '0'");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_to_remove[] = $products['products_id'];
  }
  tep_remove_product($products_to_remove);
  die('done');

  $shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
  while ($shops = tep_db_fetch_array($shops_query)) {
	tep_db_select_db($shops['shops_database']);
	$categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id > '1'");
	while ($categories = tep_db_fetch_array($categories_query)) {
	  $categories_code = 'bfd' . sprintf('%010d', (int)$categories['categories_id']);
	  tep_db_query("update " . TABLE_CATEGORIES . " set categories_code = '" . tep_db_input($categories_code) . "' where categories_id = '" . (int)$categories['categories_id'] . "'");
	}
  }
  tep_db_select_db(DB_DATABASE);
  die('done');

  $series_query = tep_db_query("select series_id from " . TABLE_SERIES . " where products_types_id > '1'");
  while ($series = tep_db_fetch_array($series_query)) {
	$series_code = 'bsr' . sprintf('%010d', (int)$series['series_id']);
	tep_db_query("update " . TABLE_SERIES . " set series_code = '" . tep_db_input($series_code) . "' where series_id = '" . (int)$series['series_id'] . "'");
  }
  die('done');

  $order_currencies = array('RUR' => 4, 'USD' => 3, 'EUR' => 3);
  arsort($order_currencies);
  list($order_currency1) = each($order_currencies);
  list($order_currency2) = each($order_currencies);
  list($order_currency3) = each($order_currencies);
  echo $order_currency1 . ' - ' . $order_currency2 . ' - ' . $order_currency3;

  die;

  tep_set_time_limit(300);
  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
  while ($shops = tep_db_fetch_array($shops_query)) {
	tep_db_select_db($shops['shops_database']);
	$fp = fopen(UPLOAD_DIR . 'csv/countries_phone_codes.csv', 'r');
	while ((list($country_code, $phone_code) = fgetcsv($fp, 16, ";")) !== FALSE) {
	  if (tep_not_null($country_code)) {
		tep_db_query("update " . TABLE_COUNTRIES . " set countries_phone_code = '" . (int)$phone_code . "' where countries_iso_code_2 = '" . tep_db_input($country_code) . "'");
	  }
	}
	fclose($fp);
  }

  die;

  tep_set_time_limit(300);
  $fp = fopen(UPLOAD_DIR . 'csv/cities.csv', 'r');
  while ((list($city_id, $delivery_days) = fgetcsv($fp, 16, ";")) !== FALSE) {
	if (tep_not_null($city_id)) {
	  tep_db_query("update " . TABLE_CITIES . " set city_delivery_days = '" . (int)$delivery_days . "' where city_id = '" . tep_db_input($city_id) . "'");
	}
  }
  fclose($fp);

  die;

  tep_db_query("delete from " . TABLE_AUTHORS . " where language_id > '2'");
  $authors_query = tep_db_query("select authors_id, authors_letter, authors_name, authors_description from " . TABLE_AUTHORS . " where language_id = '2'");
  while ($authors = tep_db_fetch_array($authors_query)) {
	tep_db_query("update " . TABLE_AUTHORS . " set authors_letter = '" . tep_db_input(tep_transliterate($authors['authors_letter'])) . "', authors_name = '" . tep_db_input(tep_transliterate($authors['authors_name'])) . "' where authors_id = '" . (int)$authors['authors_id'] . "' and language_id = '1'");
//	if ($authors['authors_id']==261397) die("update " . TABLE_AUTHORS . " set authors_letter = '" . tep_db_input(tep_transliterate($authors['authors_letter'])) . "', authors_name = '" . tep_db_input(tep_transliterate($authors['authors_name'])) . "' where authors_id = '" . (int)$authors['authors_id'] . "' and language_id = '1'");
  }

  die;

  tep_set_time_limit(600);

  tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1' and products_name = ''");
//  echo mysql_affected_rows();
//  die;
  $fields = array('products_name', 'products_description');
  $products_query = tep_db_query("select products_id, products_name, products_description, products_model, authors_name, categories_name, manufacturers_name, series_name from " . TABLE_PRODUCTS_INFO . " where products_id not in (select products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1') order by rand()");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_id = $products['products_id'];
	$check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '1'");
	$check = tep_db_fetch_array($check_query);
	if ($check['total']==0) {
	  reset($fields);
	  $products_name = '';
	  $products_description = '';
	  while (list(, $field) = each($fields)) {
		if (tep_not_null($products[$field])) {
		  ${$field} = tep_get_translation($products[$field]);
		} else {
		  ${$field} = '';
		}
	  }

	  $products_text = $products_name;
	  $products_model = $products['products_model'];
	  $authors_name = tep_transliterate($products['authors_name']);
	  $manufacturers_name = tep_transliterate($products['manufacturers_name']);
	  $product_serie_info_query = tep_db_query("select series_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	  $product_serie_info = tep_db_fetch_array($product_serie_info_query);
	  $serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$product_serie_info['series_id'] . "' and language_id = '1'");
	  $serie_info = tep_db_fetch_array($serie_info_query);
	  $series_name = $serie_info['series_name'];

	  if (tep_not_null($products_model)) $products_text .= ' ISBN ' . $products_model;
	  if (strlen($authors_name) > 2) $products_text .= ' by ' . $authors_name;
	  if (strlen($manufacturers_name) > 2) $products_text .= ' publisher ' . $manufacturers_name;
	  if (strlen($series_name) > 2) $products_text .= ' serie ' . $series_name;
	  $products_text = strip_tags(strtolower(html_entity_decode($products_text)));
	  $products_text = str_replace(array('«', '»', '+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—', '_', '-', ':', '#', '\\', '|', '`', '~', '$', '^'), ' ', $products_text);
	  $products_text = trim(preg_replace('/\s{2,}/', ' ', $products_text));

	  $sql = "insert ignore into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, products_name, products_description, products_text, language_id) values ('" . (int)$products_id . "', '" . tep_db_input($products_name) . "', '" . tep_db_input($products_description) . "', ' " . tep_db_input($products_text) . " ', '1')";
	  tep_db_query($sql);
	}
  }
  die;

  if (tep_not_null($action)) {
	switch ($action) {
	  case 'insert_sub':
		$dID = tep_db_prepare_input($HTTP_GET_VARS['dID']);
		$zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
		$zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
        $zone_factor = tep_db_prepare_input($HTTP_POST_VARS['zone_factor']);
        $zone_delivery_time = tep_db_prepare_input($HTTP_POST_VARS['zone_delivery_time']);

		tep_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, discounts_id, zone_factor, zone_delivery_time, date_added) values ('" . (int)$zone_country_id . "', '" . (int)$zone_id . "', '" . (int)$dID . "', '" . (double)$zone_factor . "', '" . tep_db_prepare_input($zone_delivery_time) . "', now())");
		$new_subzone_id = tep_db_insert_id();

		if (is_array($HTTP_POST_VARS['city_id'])) {
		  while (list(, $city) = each($HTTP_POST_VARS['city_id'])) {
			$subcities = array();
			$subcities[] = $city;
			tep_get_subcities($subcities, $city);
			while (list(, $city_id) = each($subcities)) {
			  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, discounts_id, date_added) values ('" . (int)$city_id . "', '" . (int)$new_subzone_id . "', '" . (int)$dID . "', now())");
			}
		  }
		} else {
		  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, discounts_id, date_added) select city_id, '" . (int)$new_subzone_id . "', '" . (int)$dID . "', now() from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $new_subzone_id));
		break;
	  case 'save_sub':
		$sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);
		$dID = tep_db_prepare_input($HTTP_GET_VARS['dID']);
		$zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
		$zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
        $zone_factor = tep_db_prepare_input($HTTP_POST_VARS['zone_factor']);
        $zone_delivery_time = tep_db_prepare_input($HTTP_POST_VARS['zone_delivery_time']);

		tep_db_query("update " . TABLE_ZONES_TO_GEO_ZONES . " set discounts_id = '" . (int)$dID . "', zone_country_id = '" . (int)$zone_country_id . "', zone_id = " . (tep_not_null($zone_id) ? "'" . (int)$zone_id . "'" : 'null') . ", zone_factor = '" . (double)$zone_factor . "', zone_delivery_time = '" . tep_db_prepare_input($zone_delivery_time) . "', last_modified = now() where association_id = '" . (int)$sID . "'");

		tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");
		if (is_array($HTTP_POST_VARS['city_id'])) {
		  while (list(, $city) = each($HTTP_POST_VARS['city_id'])) {
			$subcities = array();
			$subcities[] = $city;
			tep_get_subcities($subcities, $city);
			while (list(, $city_id) = each($subcities)) {
			  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, discounts_id, date_added) values ('" . (int)$city_id . "', '" . (int)$sID . "', '" . (int)$dID . "', now())");
			}
		  }
		} else {
		  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, discounts_id, date_added) select city_id, '" . (int)$sID . "', '" . (int)$dID . "', now() from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $HTTP_GET_VARS['sID']));
		break;
	  case 'deleteconfirm_sub':
		$sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);

		tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");
		tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");

		tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage']));
		break;
      case 'insert_zone':
        $discounts_name = tep_db_prepare_input($HTTP_POST_VARS['discounts_name']);
        $discounts_description = tep_db_prepare_input($HTTP_POST_VARS['discounts_description']);

        tep_db_query("insert into " . TABLE_GEO_ZONES . " (discounts_name, discounts_description, date_added) values ('" . tep_db_input($discounts_name) . "', '" . tep_db_input($discounts_description) . "', now())");
        $new_zone_id = tep_db_insert_id();

        tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $new_zone_id));
        break;
      case 'save_zone':
        $dID = tep_db_prepare_input($HTTP_GET_VARS['dID']);
        $discounts_name = tep_db_prepare_input($HTTP_POST_VARS['discounts_name']);
        $discounts_description = tep_db_prepare_input($HTTP_POST_VARS['discounts_description']);

        tep_db_query("update " . TABLE_GEO_ZONES . " set discounts_name = '" . tep_db_input($discounts_name) . "', discounts_description = '" . tep_db_input($discounts_description) . "', last_modified = now() where discounts_id = '" . (int)$dID . "'");

        tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID']));
        break;
      case 'deleteconfirm_zone':
        $dID = tep_db_prepare_input($HTTP_GET_VARS['dID']);

        tep_db_query("delete from " . TABLE_GEO_ZONES . " where discounts_id = '" . (int)$dID . "'");
        tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where discounts_id = '" . (int)$dID . "'");
        tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where discounts_id = '" . (int)$dID . "'");

        tep_redirect(tep_href_link(FILENAME_DISCOUNTS, 'zpage=' . $HTTP_GET_VARS['zpage']));
        break;
    }
  }

/*
  tep_set_time_limit(6000);
  $fp = fopen(UPLOAD_DIR . 'csv/ipm.csv', 'a+');
  fputcsv($fp, array('ID', 'Наименование', 'Описание', 'Артикул', 'Цена', 'Предметов', 'Фарфор', 'Форма', 'Рисунок', 'Декорирование', 'Вес', 'Комплектация'), ';');
  for ($i=1, $j=0; $i<20000; $i++) {
	$name = '';
	$regs1 = array();
	$compl = '';
	$descr = '';
	$price = '';
	$content = implode('', file('http://www.ipm.ru/itempage/product_' . $i . '.aspx'));
	if (preg_match('/<h2 class="gold">([^<]+)<\/h2>/', $content, $regs)) {
	  $name = $regs[1];
	  list(, $params) = explode('<div class="item_box">', $content);
	  list($params) = explode('</table><div style="float:right;margin-top:10px;">', $params);
	  $regs1 = array();
	  preg_match('/Артикул[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[1] = $r[1];
	  preg_match('/Предметов[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[2] = $r[1];
	  preg_match('/Фарфор[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[3] = $r[1];
	  preg_match('/Форма[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[4] = $r[1];
	  preg_match('/Рисунок[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[5] = $r[1];
	  preg_match('/Декорирование[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[6] = $r[1];
	  preg_match('/Вес, г[^<]*<\/td><td[^>]*>([^<]*)<\/td>/', $params, $r);
	  $regs1[7] = $r[1];
	  if ($regs1[7] > 0) $regs1[7] = $regs1[7] / 1000;
	  $regs1 = array_map('trim', $regs1);
	  if (preg_match('/img src="\/default\.aspx\?mode=image&amp;id=(\d+)"/', $params, $img)) {
//		$image = implode('', file('http://www.ipm.ru/default.aspx?mode=image&id=' . $img[1]));
//		$ff = fopen(UPLOAD_DIR . 'other_images/ipm/' . str_replace('.', '', $regs1[1]) . '.jpg', 'w');
//		fwrite($ff, $image);
//		fclose($ff);
		copy('http://www.ipm.ru/default.aspx?mode=image&id=' . $img[1], UPLOAD_DIR . 'other_images/ipm/' . str_replace('.', '', $regs1[1]) . '.jpg');
	  }
	  if (strpos($content, 'cellpadding="0" cellspacing="0" border="0" width="100%" class="t3" id="Compl" style="display:none;">')) {
		list(, $comp) = explode('cellpadding="0" cellspacing="0" border="0" width="100%" class="t3" id="Compl" style="display:none;">', $content);
		list($compl) = explode('</table>', $comp);
		$compl = '<table border="0" cellspacing="0" summary="" cellpadding="2" width="80%" class="bordered">' . $compl . '</table>';
		$compl = str_replace(">\n", '>', $compl);
		$compl = preg_replace('/\s+/', ' ', $compl);
		$compl = str_replace('<tr>', '<tr align="center">', $compl);
		$compl = str_replace('style="text-align:left"', 'align="left"', $compl);
	  }
	  if (strpos($content, '<div class="legend">')) {
		list(, $descr) = explode('<div class="legend">', $content);
		list($descr) = explode('</div></td></tr></table><div style="margin-top:40px">', $descr);
		$descr = preg_replace('/<a [^>]+>/i', '', $descr);
		$descr = str_ireplace(array('<b>', '</b>', '</a>', '<br>'), array('<strong>', '</strong>', '', '<br />'), $descr);
	  }
	  if (preg_match('/<p class="item_price"[^>]*>Цена: ([\s\.\d]+) р.[^<]*<\/p>/', $content, $regs)) $price = str_replace(',', '.', (float)preg_replace('/\s+/', '', $regs[1]));
	  $j ++;
//	  if ($j > 10) die();
	}
	fputcsv($fp, array($i, $name, $descr, $regs1[1], $price, $regs1[2], $regs1[3], $regs1[4], $regs1[5], $regs1[6], $regs1[7], $compl), ';');
  }
  fclose($fp);
  die();
*/

  tep_set_time_limit(6000);
  $listed_products = array();
  $fp = fopen(UPLOAD_DIR . 'csv/forum.csv', 'r');
  while (list($products_id) = fgetcsv($fp, 16, ';')) {
	$listed_products[] = $products_id;
  }
  fclose($fp);

  $fp = fopen(UPLOAD_DIR . 'csv/forum.csv', 'a+');
//  $fp = fopen('electronics/forum.csv', 'a+');
//  fputcsv($fp, array('артикул', 'наименование', 'описание', 'картинка', 'вес', 'производитель', 'Раздел'), ';');
  for ($i=55493; $i<81500; $i++) {
	if (!in_array($i, $listed_products)) {
//	  echo $content = tep_request_html('http://forum3.ru/descr.aspx?code=' . $i);
	  $content = implode('', file('http://forum3.ru/descr.aspx?code=' . $i));
	  if (!strpos($content, '<title>404')) {
		preg_match("/<td class=catalogue>\s+([^\n]+)\s+<br>/i", $content, $regs);
		$regs[1] = trim(strip_tags($regs[1]));
		$a = explode(' / ', $regs[1]);
		$sections = array();
		$name = '';
		reset($a);
		while (list($k, $item) = each($a)) {
		  if ($k > 0) {
			if ($k==sizeof($a)-1) $name = $item;
			else $sections[] = str_replace('3 D', '3D', $item);
		  }
		}
		$section = implode(' / ', $sections);

		if (file_exists(UPLOAD_DIR . 'other_images/electronics/' . $i . '.jpg')) {
		  $image = $i . '.jpg';
		} else {
		  $image = '';
		  if (preg_match('/href="javascript:big_image\((\d+)\)"/', $content, $regs)) {
			$img = 'http://forum3.ru/pick_image.aspx?&code=' . $regs[1];
			if (copy($img, UPLOAD_DIR . 'other_images/electronics/' . $i . '.jpg')) $image = $i . '.jpg';
//			if (copy($img, 'electronics/' . $i . '.jpg')) $image = $i . '.jpg';
		  }
		}

		$weight = '';
		if (preg_match("/<td class=catalogue bgcolor=\"#FFFFFF\" width=\"20%\">Вес<\/td>[^<]*<td class=catalogue bgcolor=\"#FFFFFF\"><li>([^<]+)</", $content, $regs)) {
		  $weight = trim($regs[1]);
		  if (substr($weight, -2)=='кг' || substr($weight, -2)=='kg') $weight = trim(substr($weight, 0, -2));
		  elseif (substr($weight, -1)=='г' || substr($weight, -1)=='g') $weight = trim(substr($weight, 0, -1))/1000;
		  else $weight = preg_replace('/[^\d\.]/', '', $weight);
		}

		$manufacturer = '';
		if (preg_match("/<td class=catalogue bgcolor=\"#FFFFFF\" width=\"20%\">Производитель<\/td>[^<]*<td class=catalogue bgcolor=\"#FFFFFF\"><a target=\"[^\"]*\" href=\"[^\"]*\"><img[^>]+ alt=\"([^\"]+)\"/", $content, $regs)) $manufacturer = $regs[1];

		$description = '';
		if (preg_match("/<td class=catalogue style=\"border: 1 solid #CCCCCC\">\s+([^\n]+)\s+<\/td>/", $content, $regs)) {
		  $description = trim($regs[1]);
		  if (strpos($description, '<a ')!==false) {
			preg_match("/href=\"([^\"]+)\"/i", $description, $regs);
			$description = str_replace($regs[0], 'href="/redirect.php?goto=' . urlencode(str_replace('http://', '', $regs[1])) . '"', strip_tags($description, '<a>'));
		  }
		}
		$d = '';
		if (strpos($content, '<td colspan="2" class=catalogue bgcolor="#FFFFFF">')!==false) {
		  list(, $d) = explode('<td colspan="2" class=catalogue bgcolor="#FFFFFF">', $content);
		  list($d) = explode('</table>', $d);
		  $d = preg_replace('/<\/td>\s*<\/tr>\s*<tr>\s*<td class=catalogue bgcolor="#FFFFFF" width="20%">/', "\n<strong>", $d);
		  $d = str_replace('<li>', '', preg_replace('/<\/td>\s*<td class=catalogue bgcolor="#FFFFFF">/', ':</strong> ', $d));
		  $d = preg_replace('/\s*<BR>\s*/', "</li>", $d);
		  $d = preg_replace('/<\/td>\s*<\/tr>/', '', $d);
		  $d = trim(strip_tags($d, '<strong><li>'));
		  $d = trim(preg_replace('/\n<strong>[^:]+:<\/strong>[ ]+\n/', "\n\n", $d));
		  $d = str_replace("<strong>", "<li><strong>", $d);
		  $d = preg_replace('/<li>(<strong>[^:]+<\/strong>)\n/', "$1\n", $d);
		}
		if ($d) $description = trim($description . "\n\n" . $d);

		$common_array = array($i,
							  $name,
							  $description,
							  $image,
							  $weight,
							  $manufacturer,
							  $section);
//		echo '<pre>' . print_r($common_array, true) . '</pre>';
//		die();
		fputcsv($fp, $common_array, ';');
	  }
	}
  }
  fclose($fp);
  die();
?>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>"/>
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; if (isset($HTTP_GET_VARS['zone'])) echo '<br><span class="smallText">' . tep_get_discounts_name($HTTP_GET_VARS['zone']) . '</span>'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if (tep_not_null($dPath)) {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_FACTOR; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_DELIVERY_TIME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $rows = 0;
    $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.discounts_id, a.zone_factor, a.zone_delivery_time, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_GEO_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.discounts_id = " . $HTTP_GET_VARS['dID'] . " order by z.zone_name";
    $zones_query = tep_db_query($zones_query_raw);
    while ($zones = tep_db_fetch_array($zones_query)) {
      $rows++;
      if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $discounts['association_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
		$num_cities_query = tep_db_query("select count(*) as num_cities from " . TABLE_CITIES . " c, " . TABLE_CITIES_TO_GEO_ZONES . " c2gz where c.city_id = c2gz.city_id and c.parent_id = '0' and c2gz.association_id = '" . $discounts['association_id'] . "'");
		$num_cities = tep_db_fetch_array($num_cities_query);
		$discounts['num_cities'] = $num_cities['num_cities'];
		$zone_localities = array();
		$localities_query = tep_db_query("select city_id from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . $discounts['association_id'] . "'");
		while ($localities = tep_db_fetch_array($localities_query)) {
		  $zone_localities[] = $localities['city_id'];
		}
		$discounts['zone_localities'] = $zone_localities;
		$discounts['num_localities'] = tep_db_num_rows($localities_query);
        $sInfo = new objectInfo($zones);
      }
      if (isset($sInfo) && is_object($sInfo) && ($discounts['association_id'] == $sInfo->association_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $discounts['association_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo (($discounts['countries_name']) ? $discounts['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $discounts['discounts_id'] . '&action=cities&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $discounts['association_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;' . (($discounts['zone_id']) ? $discounts['zone_name'] : PLEASE_SELECT); ?></td>
                <td class="dataTableContent" align="center"><?php echo $discounts['zone_factor']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $discounts['zone_delivery_time']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($discounts['association_id'] == $sInfo->association_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $discounts['association_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td align="right" colspan="5"><?php if (empty($saction)) echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($sInfo) ? 'sID=' . $sInfo->association_id . '&' : '') . 'saction=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  } else {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DISCOUNTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
    $discounts_query_raw = "select * from " . TABLE_DISCOUNTS . " order by date_added desc";
    $discounts_query = tep_db_query($discounts_query_raw);
    while ($discounts = tep_db_fetch_array($discounts_query)) {
      if ((!isset($HTTP_GET_VARS['dID']) || (isset($HTTP_GET_VARS['dID']) && ($HTTP_GET_VARS['dID'] == $discounts['discounts_id']))) && !isset($dInfo) && (substr($action, 0, 3) != 'new')) {
        $num_customers_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_GEO_ZONES . " where discounts_id = '" . (int)$discounts['discounts_id'] . "' group by discounts_id");
        $num_customers = tep_db_fetch_array($num_customers_query);

        if ($num_customers['num_customers'] > 0) {
          $discounts['num_customers'] = $num_customers['num_customers'];
        } else {
          $discounts['num_customers'] = 0;
        }

        $dInfo = new objectInfo($zones);
      }
      if (isset($dInfo) && is_object($dInfo) && ($discounts['discounts_id'] == $dInfo->discounts_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id . '&action=edit_discount') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $discounts['discounts_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dPath=' . $discounts['discounts_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;' . $discounts['discounts_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($dInfo) && is_object($dInfo) && ($discounts['discounts_id'] == $dInfo->discounts_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $discounts['discounts_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td align="right" colspan="2"><?php if (!$action) echo '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'action=new_discount') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  }
?>
            </td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
	case 'new_discount':
	case 'edit_discount':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</strong>');

	  $contents = array('form' => tep_draw_form('zones', FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub'));
	  $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
	  $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . '<br>' . tep_draw_hidden_field('zone_country_id', $sInfo->zone_country_id) . $sInfo->countries_name);
	  $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_ZONE . '<br>' . tep_draw_hidden_field('zone_id', $sInfo->zone_id) . $sInfo->zone_name);
	  $cities = array();
	  $zone_cities = tep_get_zone_cities($sInfo->zone_id);
	  while (list($cities_id, $cities_name) = each($zone_cities)) {
		$cities[] = array('id' => $cities_id, 'text' => $cities_name);
	  }
	  $contents[] = array('text' => '<br>' . TEXT_INFO_CITY_NAME . '<br>' . tep_draw_pull_down_menu('city_id[]', $cities, $sInfo->zone_localities, 'size="15" style="width: 100%;" multiple="multiple"'));
	  $contents[] = array('text' => '<br>' . TEXT_INFO_FACTOR . '<br>' . tep_draw_input_field('zone_factor', $sInfo->zone_factor, 'size="4"'));
	  $contents[] = array('text' => '<br>' . TEXT_INFO_DELIVERY_TIME . '<br>' . tep_draw_input_field('zone_delivery_time', $sInfo->zone_delivery_time));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	   break;
	case 'delete_discount':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</strong>');

	  $contents = array('form' => tep_draw_form('zones', FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub'));
	  $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $sInfo->zone_name . '</strong>');
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	default:
	  if (isset($sInfo) && is_object($sInfo)) {
		$heading[] = array('text' => '<strong>' . $sInfo->zone_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=move') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $HTTP_GET_VARS['dID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		$contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_CITIES . ' ' . $sInfo->num_cities);
		$contents[] = array('text' => TEXT_INFO_NUMBER_LOCALITIES . ' ' . $sInfo->num_localities);
		$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added));
		if (tep_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified));
	  }
      break;
	case 'new_zone':
	case 'edit_zone':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_ZONE . '</strong>');

	  $contents = array('form' => tep_draw_form('zones', FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id . '&action=save_zone'));
	  $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
	  $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . tep_draw_input_field('discounts_name', $dInfo->discounts_name, 'size="32"'));
	  $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . tep_draw_input_field('discounts_description', $dInfo->discounts_description, 'size="32"'));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'delete_zone':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ZONE . '</strong>');

	  $contents = array('form' => tep_draw_form('zones', FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id . '&action=deleteconfirm_zone'));
	  $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $dInfo->discounts_name . '</strong>');
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	default:
	  if (isset($dInfo) && is_object($dInfo)) {
		$heading[] = array('text' => '<strong>' . $dInfo->discounts_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id . '&action=edit_zone') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id . '&action=delete_zone') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' . ' <a href="' . tep_href_link(FILENAME_DISCOUNTS, 'dID=' . $dInfo->discounts_id) . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
		$contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_ZONES . ' ' . $dInfo->num_zones);
		$contents[] = array('text' => TEXT_INFO_NUMBER_CITIES . ' ' . $dInfo->num_cities);
		$contents[] = array('text' => TEXT_INFO_NUMBER_LOCALITIES . ' ' . $dInfo->num_localities);
		$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($dInfo->date_added));
		if (tep_not_null($dInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($dInfo->last_modified));
		$contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . $dInfo->discounts_description);
	  }
	  break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>