<?php
  require('includes/application_top.php');

  $content = FILENAME_PRODUCT_INFO;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $product_check_query_raw = "select products_id, products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'";
  $product_check_query = tep_db_query($product_check_query_raw);
  $product_check = tep_db_num_rows($product_check_query);
  if ($product_check > 0) {
	$product_check_info = tep_db_fetch_array($product_check_query);
	$show_product_type = $product_check_info['products_types_id'];
	if ($session_started==true && $spider_flag==false) {
	  tep_db_query("delete from " . TABLE_PRODUCTS_VIEWED . " where date_viewed <= '" . date('Y-m-d', time()-60*60*24*30) . "'");
	  $products_viewed_check_query = tep_db_query("select 1 from " . TABLE_PRODUCTS_VIEWED . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "' and date_viewed = '" . date('Y-m-d') . "'");
	  if (tep_db_num_rows($products_viewed_check_query) < 1) {
		tep_db_query("insert into " . TABLE_PRODUCTS_VIEWED . " (products_id, language_id, date_viewed, products_viewed) values ('" . (int)$HTTP_GET_VARS['products_id'] . "', '" . (int)$languages_id . "', '" . date('Y-m-d') . "', '1')");
	  } else {
		$products_viewed_check = tep_db_fetch_array($products_viewed_check_query);
		tep_db_query("update " . TABLE_PRODUCTS_VIEWED . " set products_viewed = products_viewed + 1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "' and date_viewed = '" . date('Y-m-d') . "'");
	  }
	  // [2013-01-15] Evgeniy Spashko: OPTIM Temporary disabled products_viewed value update
	  //tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed + 1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  //tep_db_query("update " . TABLE_PRODUCTS . " set products_viewed = products_viewed + 1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
	}
	$content_id = $HTTP_GET_VARS['products_id'];
	$content_type = 'product';

	$breadcrumb->add('<h1>' . tep_get_products_info($HTTP_GET_VARS['products_id'], DEFAULT_LANGUAGE_ID) . '</h1>', tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id']));
  } else {
	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>