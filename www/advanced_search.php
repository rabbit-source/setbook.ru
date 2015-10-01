<?php
  require('includes/application_top.php');

  $content = FILENAME_ADVANCED_SEARCH;
  $javascript = 'advanced_search.js.php';

  if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
  $qvars = explode('&', $_SERVER['QUERY_STRING']);
  reset($qvars);
  while (list(, $qvar) = each($qvars)) {
	list($qvar_key, $qvar_value) = explode('=', $qvar);
	$HTTP_GET_VARS[$qvar_key] = $qvar_value;
  }

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ADVANCED_SEARCH));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>