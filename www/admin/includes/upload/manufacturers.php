<?php
  $updated = 0;
  $added = 0;
  $not_added = 0;
  $total = 0;

  $begin_time = time();

  $fp = fopen($filename, 'r');
  tep_set_time_limit(3600);
  $cells_count = 0;
  while (($cell = fgetcsv($fp, 1000, ";")) !== FALSE) {
	$manufacturers_code = (int)tep_db_prepare_input($cell[0]);
	if ($manufacturers_code > 0 && strlen($cell[1]) > 2) {
	  $manufacturers_code = 'bpb' . sprintf('%010d', (int)$manufacturers_code);
	  $manufacturers_name = html_entity_decode(preg_replace('/\s{2,}/', ' ', tep_db_prepare_input($cell[1])), ENT_QUOTES);
	  $manufacturers_name = htmlspecialchars(stripslashes($manufacturers_name), ENT_QUOTES);
	  $manufacturers_check_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_code = '" . tep_db_input($manufacturers_code) . "'");
	  if (tep_db_num_rows($manufacturers_check_query) < 1) {
		$manufacturers_check_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_name = '" . tep_db_input($manufacturers_name) . "' and languages_id = '" . (int)$languages_id . "'");
	  }
	  $manufacturers_check = tep_db_fetch_array($manufacturers_check_query);
	  $manufacturers_id = (int)$manufacturers_check['manufacturers_id'];

	  $sql_data_array = array('manufacturers_code' => $manufacturers_code,
							  'sort_order' => 0);
	  $insert_sql_data = array('manufacturers_name' => $manufacturers_name,
							   'languages_id' => (int)$languages_id);

	  $manufacturers_number = str_replace('bpb', '', $manufacturers_code);/*
	  $manufacturers_file = UPLOAD_DIR . 'Publishers/' . $manufacturers_number . '.htm';
	  $manufacturers_description = '';

	  if (file_exists($manufacturers_file)) {
		$manufacturers_description = implode('', file($manufacturers_file));
		$manufacturers_description = preg_replace('/^.*<body[^>]*>(.+)<\\/body>.*$/si', '$1', $manufacturers_description);
		$manufacturers_description = str_replace('&nbsp;', ' ', $manufacturers_description);
		$manufacturers_description = trim(strip_tags($manufacturers_description, '<p><a><strong><b><i><em>'));
		$manufacturers_description = preg_replace('/<p [^>]*>/i', '<p>', $manufacturers_description);
		$manufacturers_description = preg_replace('/<p>\s*<\\/p>/i', '', $manufacturers_description);
		$manufacturers_description = preg_replace('/<a href="?([^"|>]+)"?[^>*]>([^<]+)</ie', "'<a href=' . ((strpos('$1', 'setbook.')===false && strpos('$1', 'redirect.php')===false) ? '\"' . DIR_WS_CATALOG . 'redirect.php?goto=' . urlencode(trim(str_replace('http://', '', '$1'))) . '\" target=\"_blank\">' . (strpos('$2', 'http://')!==false ? substr(str_replace('http://', '', '$2'), 0, (strpos(str_replace('http://', '', '$2'), '/')>0 ? strpos(str_replace('http://', '', '$2'), '/') : strlen(str_replace('http://', '', '$2')))) : '$2') : '\"' . substr(str_replace('http://', '', '$1'), strpos(str_replace('http://', '', '$1'), '/')) . '\"') . '<'", $manufacturers_description);
		$manufacturers_description = str_replace(HTTP_SERVER . DIR_WS_CATALOG, DIR_WS_CATALOG, $manufacturers_description);
		$manufacturers_description = preg_replace('/\s+&quot;/i', ' &laquo;', $manufacturers_description);
		$manufacturers_description = preg_replace('/&quot;\s+/i', '&raquo; ', $manufacturers_description);
		$manufacturers_description = preg_replace('/[\s ]{2,}/i', ' ', $manufacturers_description);
		$manufacturers_description = preg_replace('/>[\s ]+([^\s ])/i', '>$1', $manufacturers_description);
		$manufacturers_description = str_replace('</p><p>', "</p>\n\n<p>", $manufacturers_description);
		if (tep_not_null($manufacturers_description)) {
		  $insert_sql_data['manufacturers_description'] = $manufacturers_description;
		}
		@unlink($manufacturers_file);
	  }*/

	  if ((int)$manufacturers_id < 1) {
		$sql_data_array['date_added'] = 'now()';
		tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
		$manufacturers_id = tep_db_insert_id();

		$insert_sql_data['manufacturers_id'] = (int)$manufacturers_id;
		tep_db_perform(TABLE_MANUFACTURERS_INFO, $insert_sql_data);

		$added ++;
	  } else {
		$sql_data_array['last_modified'] = 'now()';
		tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");

		$insert_sql_data['manufacturers_id'] = (int)$manufacturers_id;
		tep_db_perform(TABLE_MANUFACTURERS_INFO, $insert_sql_data, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$languages_id . "'");

		$updated ++;
	  }

	  $manufacturers_path = 'publisher' . (int)$manufacturers_id;/*
	  $image_dirname = (int)substr($manufacturers_number, 0, 6) . '/';
//			  $manufacturers_file = UPLOAD_DIR . 'images_i/' . $image_dirname . $manufacturers_number . '.jpg';
	  $manufacturers_image = '';
	  if (file_exists($manufacturers_file)) {
		$size = @getimagesize($manufacturers_file);
		if ($size[2]=='3') $ext = '.png';
		elseif ($size[2]=='2') $ext = '.jpg';
		elseif ($size[2]=='1') $ext = '.gif';
		else $ext = '';
		if (tep_not_null($ext)) {
		  $manufacturers_image = 'manufacturers/' . preg_replace('/[^\d\w]/i', '', strtolower($manufacturers_path)) . $ext;
		  $prev_file_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
		  $prev_file = tep_db_fetch_array($prev_file_query);
		  if ($prev_file['manufacturers_image']!=$manufacturers_image) {
        	if (copy($manufacturers_file, DIR_FS_CATALOG_IMAGES . $manufacturers_image)) {
			  if (tep_not_null($prev_file['manufacturers_image'])) @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['manufacturers_image']);
			  if ((int)MANUFACTURER_IMAGE_WIDTH > 0 || (int)MANUFACTURER_IMAGE_HEIGHT > 0) {
				tep_create_thumb(DIR_FS_CATALOG_IMAGES . $manufacturers_image, '', MANUFACTURER_IMAGE_WIDTH, MANUFACTURER_IMAGE_HEIGHT);
				if (!is_dir(DIR_FS_CATALOG_IMAGES . 'manufacturers/thumbs')) mkdir(DIR_FS_CATALOG_IMAGES . 'manufacturers/thumbs', 0777);
				tep_create_thumb(DIR_FS_CATALOG_IMAGES . $manufacturers_image, DIR_FS_CATALOG_IMAGES . str_replace('manufacturers/', 'manufacturers/thumbs/', $manufacturers_image), XSMALL_IMAGE_WIDTH, XSMALL_IMAGE_HEIGHT);
			  }
			  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_image = '" . $manufacturers_image . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");
			}
		  }
		}
	  }*/

	  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_path = '" . $manufacturers_path . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");

	  $content_type = 'manufacturer';
	  $content_id = $manufacturers_id;

	  $metatags_page_title = 'Издательства. ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') . ' Интернет-магазин Setbook.';
	  $metatags_title = $manufacturers_name;
	  $metatags_keywords = $manufacturers_name;
	  $metatags_description = 'Издательства. ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '');
	  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

	  $total ++;
	}
	$cells_count ++;
  }
  fclose($fp);

  tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_name = replace(manufacturers_name, '\\\"', '\"')");

  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_status = '0'");
  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_status = '1' where manufacturers_id in (select distinct manufacturers_id from " . TABLE_PRODUCTS . " where products_status = '1')");
  tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_letter = lower(substring(manufacturers_name, 1, 1)) where manufacturers_name <> ''");
  tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_letter = '#' where manufacturers_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

  $common_array = array();
  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	$common_array[$lang_code]['publishers'] = tep_get_translation('Publishers', 'en', $lang_code);
	$common_array[$lang_code]['bookshop'] = tep_get_translation('Книжный интернет-магазин', 'ru', $lang_code);
  }

  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$lang_id . "' and manufacturers_name = ''");
	$fields = array('manufacturers_description');
	$manufacturers_query = tep_db_query("select mi.manufacturers_id, mi.manufacturers_name, mi.manufacturers_description from " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_MANUFACTURERS . " m where m.manufacturers_status = '1' and m.manufacturers_id = mi.manufacturers_id and mi.manufacturers_id not in (select manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$lang_id . "')");
	while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
	  $manufacturers_id = $manufacturers['manufacturers_id'];
	  $manufacturers_name = tep_transliterate($manufacturers['manufacturers_name']);

	  $check_query = tep_db_query("select count(*) as total from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$lang_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  if ($check['total']==0) {
		$manufacturers_description = '';
		reset($fields);
		while (list(, $field) = each($fields)) {
		  if (tep_not_null($manufacturers[$field])) {
			${$field} = tep_get_translation($manufacturers[$field], 'ru', $lang_code);
		  } else {
			${$field} = '';
		  }
		}

		$sql = "insert ignore into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, manufacturers_name, manufacturers_description, languages_id, manufacturers_url, url_clicked, date_last_click) select manufacturers_id, '" . tep_db_input($manufacturers_name) . "', '" . tep_db_input($manufacturers_description) . "', '" . (int)$lang_id . "', manufacturers_url, url_clicked, date_last_click from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$languages_id . "'";
		tep_db_query($sql);
	  }

	  $content_type = 'manufacturer';
	  $content_id = $manufacturers_id;

	  $check_query = tep_db_query("select count(*) as total from " . TABLE_METATAGS . " where language_id = '" . (int)$lang_id . "' and content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  if ($check['total']==0) {
		$metatags_page_title = $common_array[$lang_code]['publishers'] . '. ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '') . ' ' . $common_array[$lang_code]['bookshop'] . ' Setbook.';
		$metatags_title = $manufacturers_name;
		$metatags_keywords = $manufacturers_name;
		$metatags_description = $common_array[$lang_code]['publishers'] . '. ' . $manufacturers_name . (substr($manufacturers_name, -1)!='.' ? '.' : '');
		tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$lang_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");
	  }
	}
  }

  tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_letter = lower(substring(manufacturers_name, 1, 1)) where manufacturers_name <> ''");
  tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_letter = '#' where manufacturers_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

  $config_key = 'CONFIGURATION_LAST_UPDATE_MANUFACTURERS_DATE';
  $config_title = 'Дата последнего обновления издательств/производителей';
  $config_check_query = tep_db_query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($config_key) . "'");
  if (tep_db_num_rows($config_check_query) > 0) {
	$config_check = tep_db_fetch_array($config_check_query);
	tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . date('Y-m-d H:i:s') . "', last_modified = now() where configuration_id = '" . (int)$config_check['configuration_id'] . "'");
  } else {
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('" . tep_db_input($config_title) . "', '" . tep_db_input($config_key) . "', '" . date('Y-m-d H:i:s') . "', '6', now())");
  }

  echo sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added);
?>