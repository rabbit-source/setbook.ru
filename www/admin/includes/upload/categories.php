<?php
  $updated = 0;
  $added = 0;
  $not_added = 0;
  $total = 0;

  $begin_time = time();

  $products_types_names = array();

  $fp = fopen($filename, 'r');
  tep_set_time_limit(3600);
  $cells_count = 0;
  $all_categories = array();
  while (($cell = fgetcsv($fp, 1000, ";")) !== FALSE) {
	if ((int)$cell[0]>0) {
	  $categories_code = 'bfd' . sprintf('%010d', (int)trim($cell[0]));
	  $parent_code = 'bfd' . sprintf('%010d', (int)trim($cell[2]));
	  $categories_name = html_entity_decode(preg_replace('/\s{2,}/', ' ', tep_db_prepare_input($cell[1])), ENT_QUOTES);

	  $products_types_id = (int)trim($cell[3]);
	  if ($products_types_id <= 0) $products_types_id = 1;

	  $categories_check_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_code = '" . tep_db_input($categories_code) . "' and products_types_id = '" . (int)$products_types_id . "'");
	  $categories_check = tep_db_fetch_array($categories_check_query);
	  $categories_id = $categories_check['categories_id'];

	  $parent_check_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_code = '" . tep_db_input($parent_code) . "' and products_types_id = '" . (int)$products_types_id . "'");
	  $parent_check = tep_db_fetch_array($parent_check_query);
	  if (!is_array($parent_check)) $parent_check = array();
	  $parent_id = (int)$parent_check['categories_id'];
	  if ((int)trim($cell[2])==0) $parent_id = 0;

	  $sql_data_array = array('categories_code' => $categories_code,
							  'parent_id' => (int)$parent_id,
							  'products_types_id' => (int)$products_types_id,
							  'products_listing' => '2',
//							  'categories_status' => '0',
//							  'categories_listing_status' => '0',
//							  'categories_xml_status' => '0',
							  );
	  $insert_sql_data = array('categories_name' => $categories_name,
							   'language_id' => (int)$languages_id);
	  if ((int)$categories_id < 1) {
		$sql_data_array['date_added'] = 'now()';
		$sql_data_array['sort_order'] = '0';
		$shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
		while ($shops = tep_db_fetch_array($shops_query)) {
		  tep_db_select_db($shops['shops_database']);
		  if ($shops['shops_default_status']=='0') $sql_data_array['categories_id'] = $categories_id;
		  tep_db_perform(TABLE_CATEGORIES, $sql_data_array);
		  if ($shops['shops_default_status']=='1') $categories_id = tep_db_insert_id();

		  tep_db_query("update " . TABLE_CATEGORIES . " set categories_path = 'section" . (int)$categories_id . "' where categories_id = '" . (int)$categories_id . "'");
		}
		tep_db_select_db(DB_DATABASE);

		$insert_sql_data['categories_id'] = (int)$categories_id;
		tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $insert_sql_data);

		$added ++;
	  } else {
		$sql_data_array['last_modified'] = 'now()';
		$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
		while ($shops = tep_db_fetch_array($shops_query)) {
		  tep_db_select_db($shops['shops_database']);
		  tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
		}
		tep_db_select_db(DB_DATABASE);

		$insert_sql_data['categories_id'] = (int)$categories_id;
		tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $insert_sql_data, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages_id . "'");

		$updated ++;
	  }
	  $all_categories[] = $categories_id;
//	  echo print_r($sql_data_array, true) . "<br>\n";

	  $parent_categories = array($categories_id);
	  tep_get_parents($parent_categories, $categories_id);
	  $parent_categories = array_reverse($parent_categories);

	  if (!isset($products_types_names[$products_types_id])) {
		$product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "'");
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$products_types_name = $product_type_info['products_types_name'];
		$products_types_names[$products_types_id] = $products_types_name;
	  } else {
		$products_types_name = $products_types_names[$products_types_id];
	  }

	  $metatags_page_title = $products_types_name . '.';
	  $metatags_keywords = $products_types_name . '.';
	  $metatags_description = $products_types_name . '.';
	  reset($parent_categories);
	  while (list($i, $parent_id) = each($parent_categories)) {
		$category_name = tep_get_category_name($parent_id, $languages_id);
		if ($i < 3) {
		  $metatags_page_title .= ' ' . $category_name . '.';
		  $metatags_keywords .= ' ' . $category_name . '.';
		}
		$metatags_description .= ' ' . $category_name . '.';
	  }
	  $metatags_page_title .= ' Интернет-магазин SetBook.';
	  $metatags_title = $categories_name;
	  $content_type = 'category';
	  $content_id = $categories_id;
	  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

	  $total ++;

	  $cells_count ++;
	}
  }
  fclose($fp);

  if ($action=='upload_categories') {
	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id = '1' and categories_id not in ('" . implode("', '", $all_categories) . "')");
	  while ($categories = tep_db_fetch_array($categories_query)) {
		tep_remove_category($categories['categories_id'], false);
	  }
	  tep_db_query("update " . TABLE_CATEGORIES_DESCRIPTION . " set categories_name = replace(categories_name, '\\\"', '\"')");

//	  tep_db_query("update " . $shop_db . "." . TABLE_CATEGORIES . " set categories_status = '0' where products_types_id in (select products_types_id from " . TABLE_PRODUCTS_TYPES . " where products_types_status = '0')");
	}
	tep_db_select_db(DB_DATABASE);
  }

  $products_types_names = array();

  tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '1' and categories_name = ''");
  $fields = array('categories_name', 'categories_description');
  $categories_query = tep_db_query("select categories_id, categories_name, categories_description from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id not in (select categories_id from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '1') order by rand()");
  while ($categories = tep_db_fetch_array($categories_query)) {
	$categories_id = $categories['categories_id'];

	$check_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$categories_id . "' and language_id = '1'");
	$check = tep_db_fetch_array($check_query);
	if ($check['total']==0) {
	  $categories_name = '';
	  $categories_description = '';
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		if (tep_not_null($categories[$field])) {
		  ${$field} = tep_get_translation($categories[$field]);
		} else {
		  ${$field} = '';
		}
	  }

	  $sql = "replace into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, categories_name, categories_description, language_id) select categories_id, '" . tep_db_input($categories_name) . "', '" . tep_db_input($categories_description) . "', '1' from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages_id . "'";
	  tep_db_query($sql);

	  $parent_categories = array($categories_id);
	  tep_get_parents($parent_categories, $categories_id);
	  $parent_categories = array_reverse($parent_categories);

	  if (!isset($products_types_names[$products_types_id])) {
		$category_type_info_query = tep_db_query("select products_types_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
		$category_type_info = tep_db_fetch_array($category_type_info_query);
		$products_types_id = $category_type_info['products_types_id'];

		$product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "' and language_id = '1'");
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$products_types_name = $product_type_info['products_types_name'];
		$products_types_names[$products_types_id] = $products_types_name;
	  } else {
		$products_types_name = $products_types_names[$products_types_id];
	  }

	  $metatags_page_title = $products_types_name . '.';
	  $metatags_keywords = $products_types_name . '.';
	  $metatags_description = $products_types_name . '.';
	  reset($parent_categories);
	  while (list($i, $parent_id) = each($parent_categories)) {
		$category_name = tep_get_category_name($parent_id, 1);
		if ($i < 3) {
		  $metatags_page_title .= ' ' . $category_name . '.';
		  $metatags_keywords .= ' ' . $category_name . '.';
		}
		$metatags_description .= ' ' . $category_name . '.';
	  }
	  $metatags_page_title .= ' Online store Setbook.';
	  $metatags_title = $categories_name;
	  $content_type = 'category';
	  $content_id = $categories_id;
	  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '1', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");
	}
  }

  $config_key = 'CONFIGURATION_LAST_UPDATE_CATEGORIES_DATE';
  $config_title = 'Дата последнего обновления рубрикатора';
  $config_check_query = tep_db_query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($config_key) . "'");
  if (tep_db_num_rows($config_check_query) > 0) {
	$config_check = tep_db_fetch_array($config_check_query);
	tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . date('Y-m-d H:i:s') . "', last_modified = now() where configuration_id = '" . (int)$config_check['configuration_id'] . "'");
  } else {
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('" . tep_db_input($config_title) . "', '" . tep_db_input($config_key) . "', '" . date('Y-m-d H:i:s') . "', '6', now())");
  }

  echo sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added);
?>