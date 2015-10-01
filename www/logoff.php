<?php
  require('includes/application_top.php');

  tep_session_unregister('customer_id');
  tep_session_unregister('customer_default_address_id');
  tep_session_unregister('customer_first_name');
  tep_session_unregister('customer_middle_name');
  tep_session_unregister('customer_last_name');
  tep_session_unregister('customer_country_id');
  tep_session_unregister('customer_zone_id');
  tep_session_unregister('comments');
  tep_session_unregister('customer_status');
  tep_session_unregister('customer_company');
  tep_session_unregister('customer_corporate');
  tep_session_unregister('customer_type');
  tep_session_unregister('shipping');
  tep_session_unregister('sendto');
  tep_session_unregister('payment');
  tep_session_unregister('billto');
  tep_session_unregister('is_dummy_account');

  unset($_COOKIE['remember_customer']);
  tep_setcookie('remember_customer', '', time()-3600);

  $cart->reset();
  $postpone_cart->reset();
  $foreign_cart->reset();

  $content = FILENAME_LOGOFF;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name']);

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>