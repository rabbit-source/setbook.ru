<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$limit = 16;
	$linked_products_array = array();
	$products_id = (int)$HTTP_GET_VARS['products_id'];

	$linked_query = tep_db_query("select linked_id from " . TABLE_PRODUCTS_LINKED . " where 1 and products_id = '" . (int)$products_id . "'");
	while ($linked = tep_db_fetch_array($linked_query)) {
	  $product_check_status_query = tep_db_query("select products_status from " . TABLE_PRODUCTS . " where products_id = '" . (int)$linked['linked_id'] . "'");
	  $product_check_status = tep_db_fetch_array($product_check_status_query);
	  if ($product_check_status['products_status']=='1') $linked_products_array[] = $linked['linked_id'];
	}

	$linked_categories = array();
	$parent_categories = array();
	$product_categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "'");
	while ($product_categories = tep_db_fetch_array($product_categories_query)) {
	  $parent_categories[] = $product_categories['categories_id'];
	  tep_get_parents($parent_categories, $product_categories['categories_id']);
	}

	$linked_query = tep_db_query("select cl.linked_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_LINKED . " cl where 1 and c.categories_status = '1' and c.categories_id = cl.linked_id and cl.categories_id in ('" . implode("', '", $parent_categories) . "')");
	if (tep_db_num_rows($linked_query) > 0 || sizeof($linked_products_array) > 0) {
	  while ($linked = tep_db_fetch_array($linked_query)) {
		$linked_categories[] = $linked['linked_id'];
		tep_get_subcategories($linked_categories, $linked['linked_id']);
	  }

	  reset($linked_categories);
	  shuffle($linked_categories);
	  while (list(, $linked_categories_id) = each($linked_categories)) {
		$linked_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$linked_categories_id . "'");
		while ($linked_products = tep_db_fetch_array($linked_products_query)) {
		  $product_check_status_query = tep_db_query("select products_status from " . TABLE_PRODUCTS . " where products_id = '" . (int)$linked_products['products_id'] . "'");
		  $product_check_status = tep_db_fetch_array($product_check_status_query);
		  if ($product_check_status['products_status']=='1' && $linked_products['products_id']!=$products_id) {
			$linked_products_array[] = $linked_products['products_id'];
			if (sizeof($linked_products_array) >= $limit) break;
		  }
		}
	  }
//	  $linked_products_array = array_rand(array_flip($linked_products_array), (sizeof($linked_products_array)<$limit ? sizeof($linked_products_array) : $limit));
	  if (sizeof($linked_products_array) > 0) {
		$boxContent = tep_show_products_carousel($linked_products_array, 'linked_carousel', array(), 'js', true);

		$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = $box_info['blocks_name'];

		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>