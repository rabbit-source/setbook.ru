<?php
  require('includes/application_top.php');

  $content = FILENAME_FOREIGN;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  if (empty($HTTP_GET_VARS['pName'])) define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_FOREIGN));

  $products_id = 0;
  if (isset($HTTP_GET_VARS['pName'])) {
	$pname = $HTTP_GET_VARS['pName'];
	if (substr($pname, -1)=='/') $pname = substr($pname, 0, -1);
	$product_info_query = tep_db_query("select products_id, products_name from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$pname . "'");
	$product_info = tep_db_fetch_array($product_info_query);
	$products_id = $product_info['products_id'];
	if ($products_id > 0) {
	  $breadcrumb->add($product_info['products_name'], tep_href_link(FILENAME_FOREIGN, 'products_id=' . $products_id));
	  $content_id = $products_id;
	  $content_type = 'foreign';
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['pName']);
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>