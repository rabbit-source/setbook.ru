<?php
  require('includes/application_top.php');

  $content = FILENAME_HOLIDAY;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_HOLIDAY));

  $holiday_products = '';
  $holiday_categories = '';
  $hname = '';
  if (tep_not_null($HTTP_GET_VARS['hName'])) {
	$hname = $HTTP_GET_VARS['hName'];
	if (substr($hname, -1)=='/') $hname = substr($hname, 0, -1);
	if (isset($holiday_products_array[$hname])) {
	  $breadcrumb->add($holiday_products_array[$hname]['title'], tep_href_link(FILENAME_HOLIDAYS, 'hPath=' . $hname));
	  $holiday_products = $holiday_products_array[$hname]['products'];
	  $holiday_categories = $holiday_products_array[$hname]['categories'];
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['hName']);
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>