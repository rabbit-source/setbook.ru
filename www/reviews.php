<?php
  require('includes/application_top.php');

  $content = FILENAME_REVIEWS;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_REVIEWS));

  $reviews_id = isset($HTTP_GET_VARS['reviews_id']) ? $HTTP_GET_VARS['reviews_id'] : 0;
  $reviews_types_id = '';
  if (isset($HTTP_GET_VARS['tName'])) {
	$tname = $HTTP_GET_VARS['tName'];
	if (substr($tname, -1)=='/') $tname = substr($tname, 0, -1);
	list($type_name) = explode('/', $tname);
	$type_info_query = tep_db_query("select * from " . TABLE_REVIEWS_TYPES . " where reviews_types_path = '" . tep_db_input(tep_db_prepare_input($type_name)) . "' and language_id = '" . (int)$languages_id . "'");
	$type_info = tep_db_fetch_array($type_info_query);
	$reviews_types_id = $type_info['reviews_types_id'];
	if ($reviews_types_id > 0) {
	  $breadcrumb->add($type_info['reviews_types_name'], tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types_id));
	  if ($reviews_id > 0) {
		$review_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "' and reviews_types_id = '" . (int)$reviews_types_id . "'");
		$review_check = tep_db_fetch_array($review_check_query);
		if ($review_check['total'] < 1) {
		  tep_redirect(tep_href_link(FILENAME_ERROR_404));
		}
	  }
	} elseif (trim($HTTP_GET_VARS['tName'])!='') {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['tName']);
  }

  if (tep_not_null($HTTP_GET_VARS['products_id'])) {
	$breadcrumb->add(tep_get_products_info($HTTP_GET_VARS['products_id']), tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types_id . '&products_id=' . $HTTP_GET_VARS['products_id']));
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>