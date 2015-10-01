<?php
  $type_id = 3;

  $cache_filename = DIR_FS_CATALOG . 'cache/first_page/specials/' . $type_id . '.html';

  $random_products = array();
  if (file_exists($cache_filename)) {
	$fp = fopen($cache_filename, 'r');
	while (!feof($fp)) {
	  $random_products[] = trim(fgets($fp, 16));
	}
	fclose($fp);

	if (sizeof($random_products) > 10) {
	  $boxContent = '';

	  srand((double)microtime() * 1000000);
	  if (($random_products_count = sizeof(array_flip($random_products))) > 10) {
		$box_info_query = tep_db_query("select blocks_id, blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = '<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $type_id) . '">' . $box_info['blocks_name'] . '</a>';

		$i = 0;
		$carousel_products = array_rand(array_flip($random_products), ($random_products_count<100 ? $random_products_count : 100));
		reset($carousel_products);
		while (list(, $p_id) = each($carousel_products)) {
		  $products_short_name = tep_get_products_info($p_id, DEFAULT_LANGUAGE_ID);
		  if (mb_strlen($products_short_name, 'CP1251') > 40) {
			$products_short_name = mb_substr($products_short_name, 0, 45, 'CP1251');
			$products_short_name_parts = explode(' ', $products_short_name);
			unset($products_short_name_parts[sizeof($products_short_name_parts)-1]);
			$products_short_name = trim(implode(' ', $products_short_name_parts));
			$last_letter = mb_substr($products_short_name, -1, mb_strlen($products_short_name, 'CP1251'), 'CP1251');
			if (!in_array($last_letter, array(':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) {
			  $products_short_name .= ' '; $last_letter = ' ';
			}
			if (in_array($last_letter, array(' ', ':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) $products_short_name = mb_substr($products_short_name, 0, -1, 'CP1251') . '...';
		  }
		  if (tep_not_null($products_short_name)) {
			$boxContent .= '<div class="li' . ($i==0 ? '_first' : '') . '"><div class="level">' . sprintf('%02d', ($i+1)) . '.&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $p_id) . '">' . $products_short_name . '</a></div></div>' . "\n";
			if ($i>=6) break;
			$i ++;
		  }
		}
		include(DIR_WS_TEMPLATES_BOXES . 'box.php');
	  }
	}
  }
?>