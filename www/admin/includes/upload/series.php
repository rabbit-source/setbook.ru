<?php
  $updated = 0;
  $added = 0;
  $not_added = 0;
  $total = 0;

  $begin_time = time();

  $products_types_names = array();
  $series_manufacturers = array();

  $fp = fopen($filename, 'r');
  tep_set_time_limit(3600);
  $cells_count = 0;
  while (($cell = fgetcsv($fp, 1000, ";")) !== FALSE) {
	$series_code = (int)trim($cell[0]);
	if ($series_code > 0 && strlen($cell[1]) > 2) {
	  $series_code = 'bsr' . sprintf('%010d', $series_code);
	  $series_name = preg_replace('/(&#\d+)\./', '$1;', $cell[1]);
	  $series_name = html_entity_decode(preg_replace('/\s{2,}/', ' ', tep_db_prepare_input($series_name)), ENT_QUOTES);
	  $series_name = htmlspecialchars(stripslashes($series_name), ENT_QUOTES);

	  $products_types_id = (int)trim($cell[2]);
	  if ($products_types_id <= 0) $products_types_id = 1;

	  if (!isset($products_types_names[$products_types_id])) {
		$product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "'");
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$products_types_name = $product_type_info['products_types_name'];
		$products_types_names[$products_types_id] = $products_types_name;
	  } else {
		$products_types_name = $products_types_names[$products_types_id];
	  }

	  if (empty($series_code)) {
		$series_check_query = tep_db_query("select series_id, series_name, manufacturers_id from " . TABLE_SERIES . " where series_name = '" . $series_name . "' and products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "' limit 1");
	  } else {
		$series_check_query = tep_db_query("select series_id, series_name, manufacturers_id from " . TABLE_SERIES . " where series_code = '" . tep_db_input($series_code) . "' and products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "' limit 1");
	  }
	  $series_check = tep_db_fetch_array($series_check_query);
	  $series_id = (int)$series_check['series_id'];
	  $manufacturers_id = (int)$series_check['manufacturers_id'];

	  if ($manufacturers_id==0 && $series_id > 0) {
		if (isset($series_manufacturers[$series_id]['id'])) {
		  $manufacturers_id = $series_manufacturers[$series_id]['id'];
		} else {
		  $manufacturer_check_query = tep_db_query("select manufacturers_id from " . TABLE_PRODUCTS . " where series_id = '" . (int)$series_id . "' and manufacturers_id > '0' limit 1");
		  $manufacturer_check = tep_db_fetch_array($manufacturer_check_query);
		  $manufacturers_id = (int)$manufacturer_check['manufacturers_id'];
		  $series_manufacturers[$series_id]['id'] = $manufacturers_id;
		}
	  }

	  if ($manufacturers_id > 0) {
		if (isset($series_manufacturers[$series_id]['name'])) {
		  $manufacturers_name = $series_manufacturers[$series_id]['name'];
		} else {
		  $manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$languages_id . "'");
		  $manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
		  $manufacturers_name = $manufacturer_info['manufacturers_name'];
		  $series_manufacturers[$series_id]['name'] = $manufacturers_name;
		}
	  }

	  $sql_data_array = array('series_code' => tep_db_input($series_code),
							  'series_name' => tep_db_input($series_name),
							  'manufacturers_id' => (int)$manufacturers_id,
							  'products_types_id' => $products_types_id,
							  'language_id' => (int)$languages_id,
							  'sort_order' => '0');
	  if ((int)$series_id < 1) {
		$new_id_query = tep_db_query("select max(series_id) as max_id from " . TABLE_SERIES . "");
		$new_id = tep_db_fetch_array($new_id_query);
		$series_id = (int)$new_id['max_id'] + 1;

		$sql_data_array['date_added'] = 'now()';
		$sql_data_array['series_id'] = (int)$series_id;
		$sql_data_array['series_path'] = 'serie' . (int)$series_id;
		tep_db_perform(TABLE_SERIES, $sql_data_array);

		$added ++;
	  } else {
		$sql_data_array['last_modified'] = 'now()';
		tep_db_perform(TABLE_SERIES, $sql_data_array, 'update', "series_id = '" . (int)$series_id . "' and language_id = '" . (int)$languages_id . "'");

		$updated ++;
	  }

	  $content_type = 'serie';
	  $content_id = $series_id;

	  $metatags_page_title = $products_types_name . '. Серия ' . $series_name . (substr($series_name, -1)!='.' ? '.' : '') . (mb_strlen($manufacturers_name, 'CP1251')>2 ? ' Издательство ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '') . ' Интернет-магазин SetBook.';
	  $metatags_title = $series_name;
	  $metatags_keywords = $series_name . (mb_strlen($manufacturers_name, 'CP1251')>2 ? ' Издательство ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '');
	  $metatags_description = $products_types_name . '. Серия ' . $series_name . (substr($series_name, -1)!='.' ? '.' : '') . (mb_strlen($manufacturers_name, 'CP1251')>2 ? ' Издательство ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '');
	  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

	  $total ++;
	}
	$cells_count ++;
  }
  fclose($fp);

  tep_db_query("update " . TABLE_SERIES . " set series_name = replace(series_name, '\\\"', '\"'), series_status = '0'");
  tep_db_query("update " . TABLE_SERIES . " set series_status = '1' where series_id in (select distinct series_id from " . TABLE_PRODUCTS . " where products_status = '1')");
  tep_db_query("update " . TABLE_SERIES . " set series_letter = lower(substring(series_name, 1, 1)) where series_name <> ''");
  tep_db_query("update " . TABLE_SERIES . " set series_letter = '#' where series_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

  $common_array = array();
  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	$common_array[$lang_code]['series'] = tep_get_translation('Series', 'en', $lang_code);
	$common_array[$lang_code]['publisher'] = tep_get_translation('Publisher', 'en', $lang_code);
	$common_array[$lang_code]['bookshop'] = tep_get_translation('Книжный интернет-магазин', 'ru', $lang_code);
  }

  $not_translated_series_count = 0;
  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	tep_db_query("delete from " . TABLE_SERIES . " where language_id = '" . (int)$lang_id . "' and series_name = ''");
	$fields = array('series_name', 'series_description');
	$series_query = tep_db_query("select series_id, series_name, series_description, products_types_id from " . TABLE_SERIES . " where series_status = '1' and series_id not in (select series_id from " . TABLE_SERIES . " where language_id = '" . (int)$lang_id . "')");
	while ($series = tep_db_fetch_array($series_query)) {
	  $series_id = $series['series_id'];
	  $check_query = tep_db_query("select count(*) as total from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "' and language_id = '" . (int)$lang_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  $series_name = '';
	  if ($check['total']==0) {
		$series_description = '';
		reset($fields);
		while (list(, $field) = each($fields)) {
		  if (tep_not_null($series[$field])) {
			${$field} = tep_get_translation($series[$field], 'ru', $lang_code);
		  } else {
			${$field} = '';
		  }
		}

		$sql = "insert ignore into " . TABLE_SERIES . " (series_id, series_code, series_name, series_description, language_id, manufacturers_id, products_types_id, series_image, series_status, series_path, sort_order, date_added, last_modified) select series_id, series_code, '" . tep_db_input($series_name) . "', '" . tep_db_input($series_description) . "', '" . (int)$lang_id . "', manufacturers_id, products_types_id, series_image, series_status, series_path, sort_order, date_added, last_modified from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "' and language_id = '" . (int)$languages_id . "'";
		tep_db_query($sql);
	  }

	  $content_type = 'serie';
	  $content_id = $series_id;

	  $manufacturers_name = '';
	  if ($series['products_types_id']==1) {
		$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$lang_id . "'");
		$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
		$manufacturers_name = $manufacturer_info['manufacturers_name'];
	  }

	  $check_query = tep_db_query("select count(*) as total from " . TABLE_METATAGS . " where language_id = '" . (int)$lang_id . "' and content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  if ($check['total']==0) {

		if (empty($series_name)) $series_name = tep_get_translation($series['series_name'], 'ru', $lang_code);

		if (empty($common_array[$lang_code]['type'][$series['products_types_id']])) {
		  $product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$series['products_types_id'] . "' and language_id = '" . (int)$languages_id . "'");
		  $product_type_info = tep_db_fetch_array($product_type_info_query);
		  $products_types_name = $product_type_info['products_types_name'];

		  $common_array[$lang_code]['type'][$series['products_types_id']] = tep_get_translation($products_types_name, 'ru', $lang_code);
		}

		$metatags_page_title = $common_array[$lang_code]['type'][$series['products_types_id']] . '. ' . $common_array[$lang_code]['series'] . '. ' . $series_name . (substr($series_name, -1)!='.' ? '.' : '') . (strlen($manufacturers_name)>2 ? ' ' . $common_array[$lang_code]['publisher'] . ' ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '') . ' ' . $common_array[$lang_code]['bookshop'] . ' Setbook.';
		$metatags_title = $series_name;
		$metatags_keywords = $series_name . (strlen($manufacturers_name)>2 ? ' ' . $common_array[$lang_code]['publisher'] . '  ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '');
		$metatags_description = $common_array[$lang_code]['type'][$series['products_types_id']] . '. ' . $common_array[$lang_code]['series'] . '. ' . $series_name . (substr($series_name, -1)!='.' ? '.' : '') . (strlen($manufacturers_name)>2 ? ' ' . $common_array[$lang_code]['publisher'] . ' ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') : '');
		tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$lang_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");
	  }
	}
	if ($series_name=='') $not_translated_series_count ++;
	else $not_translated_series_count = 0;
	if ($not_translated_series_count > 10) break;
  }

  tep_db_query("update " . TABLE_SERIES . " set series_letter = lower(substring(series_name, 1, 1)) where series_name <> ''");
  tep_db_query("update " . TABLE_SERIES . " set series_letter = '#' where series_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

  $config_key = 'CONFIGURATION_LAST_UPDATE_SERIES_DATE';
  $config_title = 'Дата последнего обновления серий';
  $config_check_query = tep_db_query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($config_key) . "'");
  if (tep_db_num_rows($config_check_query) > 0) {
	$config_check = tep_db_fetch_array($config_check_query);
	tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . date('Y-m-d H:i:s') . "', last_modified = now() where configuration_id = '" . (int)$config_check['configuration_id'] . "'");
  } else {
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('" . tep_db_input($config_title) . "', '" . tep_db_input($config_key) . "', '" . date('Y-m-d H:i:s') . "', '6', now())");
  }

  echo sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added);
?>