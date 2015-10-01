<?php
  if ($session_started==true && $product_check > 0 && is_array($navigation->path)) {
	$navigation_path_array = array_reverse($navigation->path);
	$viewed_products = array();
	reset($navigation_path_array);
	while (list($i, $navigation_path_row) = each($navigation_path_array)) {
	  $products_id = $navigation_path_row['real_get']['products_id'];
	  if (basename($navigation_path_row['real_page'])==FILENAME_PRODUCT_INFO && tep_not_null($products_id)) {
		if ( ($i==0 && $products_id==$HTTP_GET_VARS['products_id']) || in_array($products_id, array_keys($viewed_products))) {
		} else {
		  $product_author_info_query = tep_db_query("select authors_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		  $product_author_info = tep_db_fetch_array($product_author_info_query);
		  $products_authors_name = tep_get_authors_info($product_author_info['authors_id'], DEFAULT_LANGUAGE_ID);
		  $products_name = tep_get_products_info($products_id, DEFAULT_LANGUAGE_ID);
		  $products_full_name = (tep_not_null($products_authors_name) ? $products_authors_name . ': ' : '') . $products_name;
		  $viewed_products[$products_id] = array('name' => $products_name, 'full_name' => $products_full_name);
		}
	  }
	}
	$navigation_path_string = '';
	if (sizeof($viewed_products) > 2) {
//	  asort($viewed_products);
	  reset($viewed_products);
	  $i = 0;
	  while (list($products_id, $products_info) = each($viewed_products)) {
		if ($i==0 && $products_id==$HTTP_GET_VARS['products_id']) {
		} else {
		  $navigation_path_string .= '<div class="li' . ($i==0 ? '_first' : '') . '"><div class="level_0"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_id) . '"' . ($HTTP_GET_VARS['products_id']==$products_id ? ' class="active"' : '') . ' title="' . $products_info['full_name'] . '">' . $products_info['name'] . '</a></div></div>' . "\n";
		  $i ++;
		}
	  }
	  if (tep_not_null($navigation_path_string)) {
//		$boxID = 'viewed_products';
		$boxHeading = 'Просмотренные товары';
		$boxContent = '<div style="max-height: 200px; overflow: auto;">' . $navigation_path_string . '</div>';
		include(DIR_WS_TEMPLATES_BOXES . 'box.php');
	  }
	}
  }
?>