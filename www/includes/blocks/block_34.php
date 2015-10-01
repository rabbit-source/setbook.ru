<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) {
	$page_link = FILENAME_SPECIALS;
	$table_name = TABLE_SPECIALS;
	$type_id = 1;

	$type_count = 0;
	if ($table_name == TABLE_SPECIALS) {
	  if (sizeof($active_specials_types_array) > 0) {
		$type_info_query = tep_db_query("select specials_last_modified as last_modified from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$type_id . "' and specials_types_id in ('" . implode("', '", $active_specials_types_array) . "')");
		$type_count = tep_db_num_rows($type_info_query);
	  }
	} elseif ($table_name == TABLE_PRODUCTS) {
	  if (sizeof($active_products_types_array) > 0) {
		$type_info_query = tep_db_query("select products_last_modified as last_modified from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$type_id . "' and products_types_id in ('" . implode("', '", $active_products_types_array) . "')");
		$type_count = tep_db_num_rows($type_info_query);
	  }
	}
	if ($type_count > 0) {
	  $type_info = tep_db_fetch_array($type_info_query);
	  clearstatcache();
	  $cache_dir = DIR_FS_CATALOG . 'cache/first_page/';
	  if (!is_dir($cache_dir)) mkdir($cache_dir, 0777);
	  $cache_dir .= $table_name . '/';
	  if (!is_dir($cache_dir)) mkdir($cache_dir, 0777);
	  $cache_filename = $cache_dir . $type_id . '.html';
	  $include_cache_filename = false;
	  if (file_exists($cache_filename)) {
		if (date('Y-m-d H:i:s', filemtime($cache_filename)) > $type_info['last_modified'] && $type_info['last_modified'] > 0) {
		  $include_cache_filename = true;
		}
	  }

	  $random_products = array();
	  if ($include_cache_filename==false) {
		if ($table_name == TABLE_SPECIALS) {
		  $products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$type_id . "' and specials_first_page = '1' and status = '1' and specials_date_added >= '" . date('Y-m-d', time()-60*60*24*3) . " 00:00:00' order by rand() limit 1000");
		  if (tep_db_num_rows($products_query)==0) {
			$products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$type_id . "' and specials_first_page = '1' and status = '1' order by rand() limit 1000");
		  }
		} else {
		  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$type_id . "' and products_status = '1' and products_listing_status = '1' and products_image_exists = '1'" . ((int)$type_id>2 ? " and products_quantity > '0'" : "") . " order by rand() limit 1000");
		  if ($type_id > 2 && tep_db_num_rows($products_query)==0) {
			$products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$type_id . "' and products_status = '1' and products_listing_status = '1' and products_image_exists = '1' order by rand() limit 1000");
		  }
		}
		while ($products = tep_db_fetch_array($products_query)) {
		  $random_products[] = $products['products_id'];
		}
		$fp = fopen($cache_filename, 'w');
		fwrite($fp, implode("\n", $random_products));
		fclose($fp);
	  } else {
		$fp = fopen($cache_filename, 'r');
		while (!feof($fp)) {
		  $random_products[] = trim(fgets($fp, 16));
		}
		fclose($fp);
	  }

	  $box_info_query = tep_db_query("select blocks_id, blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	  $box_info = tep_db_fetch_array($box_info_query);
	  $boxHeading = '<a href="' . tep_href_link($page_link, 'tPath=' . $type_id) . '">' . $box_info['blocks_name'] . '</a>';

	  srand((double)microtime() * 1000000);
	  if (($random_products_count = sizeof(array_flip($random_products))) > 3) {
		$carousel_products = array_rand(array_flip($random_products), ($random_products_count<24 ? $random_products_count : 24));
		$boxContent = tep_show_products_carousel($carousel_products, 'carousel_' . $table_name . '_' . $type_id, '', 'table');
		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>