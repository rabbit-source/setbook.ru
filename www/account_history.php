<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = FILENAME_ACCOUNT_HISTORY;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "' and language_id = '" . (int)$languages_id . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>