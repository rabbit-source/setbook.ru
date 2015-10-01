<?php
  $check_products_types_id = 4;
  $type_info_query = tep_db_query("select products_last_modified, products_types_path from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$check_products_types_id . "' and products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)$languages_id . "'");
  if (tep_db_num_rows($type_info_query) > 0) {
	$type_info = tep_db_fetch_array($type_info_query);
	clearstatcache();
	$categories_cache_dir = DIR_FS_CATALOG . 'cache/categories/';
	if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
	$categories_cache_dir .= $check_products_types_id . '/';
	if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
	$categories_cache_filename = $categories_cache_dir . 'tree_' . $current_category_id . '.html';
	$include_categories_cache_filename = false;
	if (file_exists($categories_cache_filename)) {
	  if (date('Y-m-d H:i:s', filemtime($categories_cache_filename)) > $type_info['products_last_modified']) {
		$include_categories_cache_filename = true;
	  }
	}
	$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	$box_info = tep_db_fetch_array($box_info_query);
	$boxHeading = '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $check_products_types_id) . '">' . $box_info['blocks_name'] . '</a>';
	$boxID = $type_info['products_types_path'];
	if ($include_categories_cache_filename==false) {
	  $boxContent = tep_show_category(0, 0, '', $check_products_types_id);
	  $boxContent = str_replace('?' . tep_session_name() . '=' . tep_session_id(), '', $boxContent);
	  $fp = fopen($categories_cache_filename, 'w');
	  fwrite($fp, $boxContent);
	  fclose($fp);
	} else {
	  $boxContent = '';
	  $fp = fopen($categories_cache_filename, 'r');
	  while (!feof($fp)) {
		$boxContent .= fgets($fp, 400);
	  }
	  fclose($fp);
	}
	if (tep_not_null($boxContent)) include(DIR_WS_TEMPLATES_BOXES . 'box.php');
  }
?>