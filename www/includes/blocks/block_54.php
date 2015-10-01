<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$limit = 16;
	$products_id = (int)$HTTP_GET_VARS['products_id'];
	$product_additional_info_query = tep_db_query("select series_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$product_additional_info = tep_db_fetch_array($product_additional_info_query);
	if ($product_additional_info['series_id'] > 0) {
	  $serie_products_array = array();
	  $serie_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where series_id = '" . (int)$product_additional_info['series_id'] . "' and products_id <> '" . (int)$products_id . "' and products_status = '1' order by rand() limit $limit");
	  if (tep_db_num_rows($serie_products_query) > 0) {
		while ($serie_products = tep_db_fetch_array($serie_products_query)) {
		  $serie_products_array[] = $serie_products['products_id'];
		}
		$boxContent = tep_show_products_carousel($serie_products_array, 'serie_carousel');

		$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = '<a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $product_additional_info['series_id']) . '">' . sprintf($box_info['blocks_name'], tep_get_series_info($product_additional_info['series_id'], DEFAULT_LANGUAGE_ID)) . '</a>';

		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>