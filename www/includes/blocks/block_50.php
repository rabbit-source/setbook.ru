<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	$products_id = (int)$HTTP_GET_VARS['products_id'];
	$other_images_query = tep_db_query("select products_images_image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages_id . "' order by products_images_id");
	if (tep_db_num_rows($other_images_query) > 0) {
	  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	  $box_info = tep_db_fetch_array($box_info_query);
	  $author_info_query = tep_db_query("select authors_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	  $author_info = tep_db_fetch_array($author_info_query);
	  $block_authors_name = tep_get_authors_info($author_info['authors_id'], DEFAULT_LANGUAGE_ID);
	  $block_product_name = (tep_not_null($block_authors_name) ? $block_authors_name . ': ' : '') . tep_get_products_info($products_id, DEFAULT_LANGUAGE_ID);
	  $boxHeading = sprintf($box_info['blocks_name'], $block_product_name);

	  $pieces = 0;
	  $products_images = array();
	  $products_images_dir = DIR_WS_IMAGES . 'prints/' . substr(sprintf("%06d", $products_id), 0, -4) . '/' . sprintf("%06d", $products_id) . '/';
	  while ($other_images = tep_db_fetch_array($other_images_query)) {
		$pieces ++;
		$image_title = str_replace("'", '&#039;', 'Фрагмент ' . $pieces);
		$products_images[] = array(
			'image_small' => $products_images_dir . 'thumbs/' . $other_images['products_images_image'],
			'image_link' => $products_images_dir . $other_images['products_images_image'],
			'image_title' => $image_title);
	  }
	  $boxContent = tep_show_images_carousel($products_images, 'pic' . $product_info['products_id']);

	  if (tep_not_null($boxContent)) include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	}
  }
?>