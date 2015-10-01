<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = FILENAME_ACCOUNT_WISHLIST;
  $javascript = 'account_wishlist.js';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (isset($HTTP_POST_VARS['action']) && (($HTTP_POST_VARS['action'] == 'process') || ($HTTP_POST_VARS['action'] == 'update'))) {
	$wl_categories = array();
	reset($HTTP_POST_VARS);
	while (list($k, $v) = each($HTTP_POST_VARS)) {
	  if (substr($k, 0, 11)=='categories_' && (int)$v > 0) $wl_categories[] = (int)$v;
	}
//	if (sizeof($wl_categories) > 0) {
	  $wl_parameters = array('categories' => $wl_categories);
	  $wl_categories_string = serialize($wl_parameters);
	  $wishlist_check_query = tep_db_query("select wishlists_id from " . TABLE_WISHLISTS . " where customers_id = '" . (int)$customer_id . "'");
	  if (tep_db_num_rows($wishlist_check_query) > 0) {
    	tep_db_query("update " . TABLE_WISHLISTS . " set wishlists_search_params = '" . tep_db_input($wl_categories_string) . "' where customers_id = '" . (int)$customer_id . "'");
	  } else {
    	tep_db_query("insert into " . TABLE_WISHLISTS . " (customers_id, wishlists_search_params) values ('" . (int)$customer_id . "', '" . tep_db_input($wl_categories_string) . "')");
	  }

	  $customers_name = trim($customer_first_name . ' ' . $customer_last_name);
	  $email_subject = STORE_NAME . ' - ' . sprintf(EMAIL_WISHLIST_SUBJECT, $customers_name);
	  $customer_company_info_query = tep_db_query("select companies_name from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_company_info = tep_db_fetch_array($customer_company_info_query);
	  $customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_info = tep_db_fetch_array($customer_info_query);
	  $customers_full_data = $customers_name . ' (' . (tep_not_null($customer_company_info['companies_name']) ? $customer_company_info['companies_name'] . ', ' : '') . $customer_info['customers_email_address'] . ')';

	  $wls_tree = tep_get_category_level(0, 0, 1, $wl_categories, false);
	  $wls_tree = strip_tags(preg_replace('/<img[^>]+>/', '  ', str_replace("\t", '', $wls_tree)));
	  while (strpos($wls_tree, "\n\n")!==false) $wls_tree = str_replace("\n\n", "\n", $wls_tree);

	  if (sizeof($wl_categories) > 0) $email_text = sprintf(EMAIL_WISHLIST_TEXT_1, $customers_full_data, $wls_tree);
	  else $email_text = sprintf(EMAIL_WISHLIST_TEXT_2, $customers_full_data);

	  if (defined('SEND_WISHLISTS_EMAILS_TO') && tep_not_null(SEND_WISHLISTS_EMAILS_TO)) {
		tep_mail('', SEND_WISHLISTS_EMAILS_TO, $email_subject, $email_text, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	  }

	  $messageStack->add_session('header', SUCCESS_WISHLIST_UPDATED, 'success');

	  tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
//	} else {
//	  $messageStack->add_session('header', ERROR_WISHLIST_UPDATED, 'warning');

//	  tep_redirect(tep_href_link(FILENAME_ACCOUNT_WISHLIST, '', 'SSL'));
//	}
  }

  $wls_search_parameters = array();
  $wls_query = tep_db_query("select * from " . TABLE_WISHLISTS . " where customers_id = '" . (int)$customer_id . "'");
  $wls_check = tep_db_num_rows($wls_query);
  $wls = tep_db_fetch_array($wls_query);
  if (tep_not_null($wls['wishlists_search_params'])) $wls_search_parameters = unserialize($wls['wishlists_search_params']);
  $wls_categories = $wls_search_parameters['categories'];
  if (!is_array($wls_categories)) $wls_categories = array();

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ACCOUNT_WISHLIST, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>