<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$limit = 16;
	$products_id = (int)$HTTP_GET_VARS['products_id'];
	$product_additional_info_query = tep_db_query("select authors_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$product_additional_info = tep_db_fetch_array($product_additional_info_query);
	if ($product_additional_info['authors_id'] > 0) {
	  $author_products_array = array();
	  $author_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where authors_id = '" . (int)$product_additional_info['authors_id'] . "' and products_id <> '" . (int)$products_id . "' and products_status = '1' order by rand() limit $limit");
	  if (tep_db_num_rows($author_products_query) > 0) {
		while ($author_products = tep_db_fetch_array($author_products_query)) {
		  $author_products_array[] = $author_products['products_id'];
		}
		$boxContent = tep_show_products_carousel($author_products_array, 'author_carousel');

		$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
		$box_info = tep_db_fetch_array($box_info_query);
		$boxHeading = '<a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $product_additional_info['authors_id']) . '">' . sprintf($box_info['blocks_name'], tep_get_authors_info($product_additional_info['authors_id'], DEFAULT_LANGUAGE_ID)) . '</a>';

		include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	  }
	}
  }
?>