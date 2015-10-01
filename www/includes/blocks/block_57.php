<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$limit = 16;
	$products_id = (int)$HTTP_GET_VARS['products_id'];
	$product_additional_info_query = tep_db_query("select products_periodicity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$product_additional_info = tep_db_fetch_array($product_additional_info_query);
	if ($product_additional_info['products_periodicity']=='0') {
	  $orders_products_array = array();
	  $orders_products_query = tep_db_query("select op.products_id from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_VIEWED . " opv where opv.products_id = '" . (int)$products_id . "' and op.orders_id = opv.orders_id order by opv.orders_products_viewed_id desc limit $limit");
	  if (tep_db_num_rows($orders_products_query) > 0) {
		while ($orders_products = tep_db_fetch_array($orders_products_query)) {
		  $orders_products_array[] = $orders_products['products_id'];
		}
		$boxContent = tep_show_products_carousel($orders_products_array, 'viewed_carousel');

		$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = $box_info['blocks_name'];

		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>