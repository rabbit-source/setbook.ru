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
	$authors_code = (int)tep_db_prepare_input($cell[0]);
	if ($authors_code > 0 && strlen($cell[1]) > 2) {
	  $authors_code = 'bat' . sprintf('%010d', (int)$authors_code);
	  $authors_name = trim(html_entity_decode(preg_replace('/\s{2,}/', ' ', tep_db_prepare_input($cell[1])), ENT_QUOTES));

	  if (tep_not_null($authors_name)) {
		$i = 0;
		do {
		  $letter = strtoupper(substr($authors_name, $i, 1));
		  $i ++;
		  if ($i>=strlen($authors_name)) break;
		} while (!preg_match('/^[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9].*/i', $letter));
	  }

	  $authors_name = trim(str_replace('&quot;', '', str_replace('"', '', $authors_name)));
	  $authors_name = htmlspecialchars(stripslashes($authors_name), ENT_QUOTES);
	  $authors_check_query = tep_db_query("select authors_id from " . TABLE_AUTHORS . " where authors_code = '" . tep_db_input($authors_code) . "'");
	  if (tep_db_num_rows($authors_check_query) < 1) {
		$authors_check_query = tep_db_query("select authors_id, authors_code from " . TABLE_AUTHORS . " where authors_name = '" . tep_db_input($authors_name) . "' and language_id = '" . (int)$languages_id . "'");
	  }
	  $authors_check = tep_db_fetch_array($authors_check_query);
	  $authors_id = (int)$authors_check['authors_id'];

	  $sql_data_array = array('authors_code' => $authors_code,
							  'authors_letter' => $letter,
							  'authors_name' => $authors_name,
							  'language_id' => (int)$languages_id,
							  'sort_order' => '0');

	  $authors_number = str_replace('bat', '', $authors_code);/*
	  $authors_file = UPLOAD_DIR . 'Authors/' . $authors_number . '.htm';
	  $authors_file = '';
	  $authors_description = '';

	  if (file_exists($authors_file)) {
		$authors_description = implode('', file($authors_file));
		$authors_description = preg_replace('/^.*<body[^>]*>(.+)<\\/body>.*$/si', '$1', $authors_description);
		$authors_description = str_replace('&nbsp;', ' ', $authors_description);
		$authors_description = trim(strip_tags($authors_description, '<p><a><strong><b><i><em>'));
		$authors_description = preg_replace('/<p [^>]*>/i', '<p>', $authors_description);
		$authors_description = preg_replace('/<p>\s*<\\/p>/i', '', $authors_description);
		$authors_description = preg_replace('/<a href="?([^"|>]+)"?[^>*]>([^<]+)</ie', "'<a href=' . ((strpos('$1', 'setbook.')===false && strpos('$1', 'redirect.php')===false) ? '\"' . DIR_WS_CATALOG . 'redirect.php?goto=' . urlencode(trim(str_replace('http://', '', '$1'))) . '\" target=\"_blank\">' . (strpos('$2', 'http://')!==false ? substr(str_replace('http://', '', '$2'), 0, (strpos(str_replace('http://', '', '$2'), '/')>0 ? strpos(str_replace('http://', '', '$2'), '/') : strlen(str_replace('http://', '', '$2')))) : '$2') : '\"' . substr(str_replace('http://', '', '$1'), strpos(str_replace('http://', '', '$1'), '/')) . '\"') . '<'", $authors_description);
		$authors_description = str_replace(HTTP_SERVER . DIR_WS_CATALOG, DIR_WS_CATALOG, $authors_description);
		$authors_description = preg_replace('/\s+&quot;/i', ' &laquo;', $authors_description);
		$authors_description = preg_replace('/&quot;\s+/i', '&raquo; ', $authors_description);
		$authors_description = preg_replace('/[\s ]{2,}/i', ' ', $authors_description);
		$authors_description = preg_replace('/>[\s ]+([^\s ])/i', '>$1', $authors_description);
		$authors_description = str_replace('</p><p>', "</p>\n\n<p>", $authors_description);
		if (tep_not_null($authors_description)) {
		  $sql_data_array['authors_description'] = $authors_description;
		}
		@unlink($authors_file);
	  }*/

	  if ((int)$authors_id < 1) {
		$new_id_query = tep_db_query("select max(authors_id) as max_id from " . TABLE_AUTHORS . "");
		$new_id = tep_db_fetch_array($new_id_query);
		$authors_id = (int)$new_id['max_id'] + 1;

		$sql_data_array['authors_id'] = (int)$authors_id;
		$sql_data_array['date_added'] = 'now()';
		tep_db_perform(TABLE_AUTHORS, $sql_data_array);

		$added ++;
	  } else {
		$sql_data_array['last_modified'] = 'now()';
		tep_db_perform(TABLE_AUTHORS, $sql_data_array, 'update', "authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)$languages_id . "'");

		$updated ++;
	  }

	  $authors_path = 'author' . (int)$authors_id; /*
	  $image_dirname = (int)substr($authors_number, 0, 6) . '/';
	  $authors_file = UPLOAD_DIR . 'images_a/' . $image_dirname . $authors_number . '.jpg';
	  $authors_image = '';
	  if (file_exists($authors_file)) {
		$size = @getimagesize($authors_file);
		if ($size[2]=='3') $ext = '.png';
		elseif ($size[2]=='2') $ext = '.jpg';
		elseif ($size[2]=='1') $ext = '.gif';
		else $ext = '';
		if (tep_not_null($ext)) {
		  $authors_image = 'authors/' . preg_replace('/[^\d\w]/i', '', strtolower($authors_path)) . $ext;
		  $prev_file_query = tep_db_query("select authors_image from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "'");
		  $prev_file = tep_db_fetch_array($prev_file_query);
		  if ($prev_file['authors_image']!=$authors_image) {
        	if (copy($authors_file, DIR_FS_CATALOG_IMAGES . $authors_image)) {
			  if (tep_not_null($prev_file['authors_image'])) @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['authors_image']);
			  if ((int)AUTHOR_IMAGE_WIDTH > 0 || (int)AUTHOR_IMAGE_HEIGHT > 0) {
				tep_create_thumb(DIR_FS_CATALOG_IMAGES . $authors_image, '', AUTHOR_IMAGE_WIDTH, AUTHOR_IMAGE_HEIGHT);
				if (!is_dir(DIR_FS_CATALOG_IMAGES . 'authors/thumbs')) mkdir(DIR_FS_CATALOG_IMAGES . 'authors/thumbs', 0777);
				tep_create_thumb(DIR_FS_CATALOG_IMAGES . $authors_image, DIR_FS_CATALOG_IMAGES . str_replace('authors/', 'authors/thumbs/', $authors_image), XSMALL_IMAGE_WIDTH, XSMALL_IMAGE_HEIGHT);
			  }
			  tep_db_query("update " . TABLE_AUTHORS . " set authors_image = '" . $authors_image . "' where authors_id = '" . (int)$authors_id . "'");
			}
		  }
		}
	  }*/

	  tep_db_query("update " . TABLE_AUTHORS . " set authors_path = '" . tep_db_input($authors_path) . "' where authors_id = '" . (int)$authors_id . "'");

	  $content_type = 'author';
	  $content_id = $authors_id;

	  $metatags_page_title = 'Авторы. ' . $authors_name . ' Интернет-магазин Setbook.';
	  $metatags_title = $authors_name;
	  $metatags_keywords = $authors_name;
	  $metatags_description = 'Авторы. ' . $authors_name;
	  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

	  $total ++;
	}
	$cells_count ++;
  }
  fclose($fp);

  tep_db_query("update " . TABLE_AUTHORS . " set authors_name = replace(authors_name, '\\\"', '\"'), authors_status = '0'");
  tep_db_query("update " . TABLE_AUTHORS . " set authors_status = '1' where authors_id in (select distinct authors_id from " . TABLE_PRODUCTS . " where products_status = '1')");
#  tep_db_query("update " . TABLE_AUTHORS . " set authors_letter = lower(substring(authors_name, 1, 1)) where authors_name <> ''");
#  tep_db_query("update " . TABLE_AUTHORS . " set authors_letter = '#' where authors_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

  $common_array = array();
  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	$common_array[$lang_code]['authors'] = tep_get_translation('Authors', 'en', $lang_code);
	$common_array[$lang_code]['bookshop'] = tep_get_translation('Книжный интернет-магазин', 'ru', $lang_code);
  }

  reset($languages_array);
  while (list($lang_code, $lang_id) = each($languages_array)) {
	tep_db_query("delete from " . TABLE_AUTHORS . " where language_id = '" . (int)$lang_id . "' and authors_name = ''");
	$fields = array('authors_description');
	$authors_query = tep_db_query("select authors_id, authors_name, authors_description from " . TABLE_AUTHORS . " where authors_status = '1' and authors_id not in (select authors_id from " . TABLE_AUTHORS . " where language_id = '" . (int)$lang_id . "')");
	while ($authors = tep_db_fetch_array($authors_query)) {
	  $authors_id = $authors['authors_id'];
	  $authors_name = tep_transliterate($authors['authors_name']);

	  $check_query = tep_db_query("select count(*) as total from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)$lang_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  if ($check['total']==0) {
		$authors_description = '';
		reset($fields);
		while (list(, $field) = each($fields)) {
		  if (tep_not_null($authors[$field])) {
			${$field} = tep_get_translation($authors[$field]);
		  } else {
			${$field} = '';
		  }
		}

		$letter_query = tep_db_query("select authors_letter from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)$languages_id . "'");
		$letter = tep_db_fetch_array($letter_query);

		$sql = "replace into " . TABLE_AUTHORS . " (authors_id, authors_code, authors_letter, authors_name, authors_description, language_id, authors_image, authors_status, authors_path, sort_order, date_added, last_modified) select authors_id, authors_code, '" . tep_db_input(tep_transliterate($letter['authors_letter'])) . "', '" . tep_db_input($authors_name) . "', '" . tep_db_input($authors_description) . "', '" . (int)$lang_id . "', authors_image, authors_status, authors_path, sort_order, date_added, last_modified from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)$languages_id . "'";
		tep_db_query($sql);
	  }

	  $content_type = 'author';
	  $content_id = $authors_id;

	  $check_query = tep_db_query("select count(*) as total from " . TABLE_METATAGS . " where language_id = '" . (int)$lang_id . "' and content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "'");
	  $check = tep_db_fetch_array($check_query);
	  if ($check['total']==0) {
		$metatags_page_title = $common_array[$lang_code]['authors'] . '. ' . $authors_name . (substr($authors_name, -1)!='.' ? '.' : '') . ' ' . $common_array[$lang_code]['bookshop'] . ' Setbook.';
		$metatags_title = $authors_name;
		$metatags_keywords = $authors_name;
		$metatags_description = $common_array[$lang_code]['authors'] . '. ' . $authors_name . (substr($authors_name, -1)!='.' ? '.' : '');
		tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$lang_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");
	  }
	}
  }

//  tep_db_query("update " . TABLE_AUTHORS . " set authors_letter = lower(substring(authors_name, 1, 1)) where authors_name <> '' and (language_id = '" . (int)$languages_id . "' or authors_letter = '')");
//  tep_db_query("update " . TABLE_AUTHORS . " set authors_letter = '#' where authors_letter not rlike '[абвгдеёжзийклмнопрстуфхцчшщъыьэюяa-z0-9]'");

//  tep_db_query("optimize table " . TABLE_AUTHORS . "");

  $config_key = 'CONFIGURATION_LAST_UPDATE_AUTHORS_DATE';
  $config_title = 'Дата последнего обновления авторов';
  $config_check_query = tep_db_query("select configuration_id from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($config_key) . "'");
  if (tep_db_num_rows($config_check_query) > 0) {
	$config_check = tep_db_fetch_array($config_check_query);
	tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . date('Y-m-d H:i:s') . "', last_modified = now() where configuration_id = '" . (int)$config_check['configuration_id'] . "'");
  } else {
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('" . tep_db_input($config_title) . "', '" . tep_db_input($config_key) . "', '" . date('Y-m-d H:i:s') . "', '6', now())");
  }

  echo sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added);
?>