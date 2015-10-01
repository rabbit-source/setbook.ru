<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$limit = 16;
	$products_id = (int)$HTTP_GET_VARS['products_id'];
	$product_additional_info_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$product_additional_info = tep_db_fetch_array($product_additional_info_query);
	if ($product_additional_info['products_types_id']!='2') {
	  $orders_products_array = array();
	  $orders_products_query = tep_db_query("select opb.products_id from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb where opa.products_id = '" . (int)$products_id . "' and opa.orders_id = opb.orders_id and opb.products_id != '" . (int)$products_id . "' and opb.products_types_id <> '2' group by opb.products_id order by opb.orders_products_id desc limit $limit");
	  if (tep_db_num_rows($orders_products_query) > 0) {
		while ($orders_products = tep_db_fetch_array($orders_products_query)) {
		  $orders_products_array[] = $orders_products['products_id'];
		}
		$boxContent = tep_show_products_carousel($orders_products_array, 'order_carousel');

		$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = $box_info['blocks_name'];

		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>