<?php
  require('includes/application_top.php');

  $content = FILENAME_CREATE_ACCOUNT_SUCCESS;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (is_object($navigation)) {
	if (sizeof($navigation->snapshot) > 0) {
	  $origin_href = ($navigation->snapshot['mode']=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . $navigation->snapshot['page'] . (tep_not_null(tep_array_to_string($navigation->snapshot['get'])) ? '?' . tep_array_to_string($navigation->snapshot['get']) : '');
	  $navigation->clear_snapshot();
	} else {
	  $origin_href = tep_href_link(FILENAME_DEFAULT);
	}
  }

  $create_account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_CREATE_ACCOUNT) . "' and language_id = '" . (int)$languages_id . "'");
  $create_account_page = tep_db_fetch_array($create_account_page_query);

  $breadcrumb->add($create_account_page['pages_name'], tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));

  if (isset($HTTP_GET_VARS['email']) && isset($HTTP_GET_VARS['key'])) {
	$email_address = tep_sanitize_string(urldecode($HTTP_GET_VARS['email']));
	$customer_check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
	if (tep_db_num_rows($customer_check_query) > 0) {
	  $customer_check = tep_db_fetch_array($customer_check_query);
	  if ($customer_check['customers_email_address_confirmed']=='1') {
		$messageStack->add('header', ENTRY_REGISTRATION_ALREADY_VERIFIED, 'success');
	  } else {
		list($check_password_sum) = explode(':', $customer_check['customers_password']);
		if ($check_password_sum==trim($HTTP_GET_VARS['key'])) {
		  $customer_id = $customer_check['customers_id'];
		  if (ACCOUNT_MIDDLE_NAME == 'true') {
			list($customer_first_name, $customer_middle_name) = explode(' ', $customer_check['customers_firstname']);
		  } else {
			$customer_first_name = $customer_check['customers_firstname'];
			$customer_middle_name = '';
		  }
		  $customer_last_name = $customer_check['customers_lastname'];
		  $customer_status = $customer_check['customers_status'];
		  $customer_type = $customer_check['customers_type'];
		  tep_session_register('customer_id');
		  tep_session_register('customer_first_name');
		  tep_session_register('customer_middle_name');
		  tep_session_register('customer_last_name');
		  tep_session_register('customer_status');
		  tep_session_register('customer_type');
		  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_email_address_confirmed = '1' where customers_id = '" . (int)$customer_id . "'");

// restore cart contents
		  $cart->restore_contents();

		  $email_subject = sprintf(EMAIL_SUBJECT, STORE_NAME);
		  $email_text = sprintf(EMAIL_GREET_NONE, trim($customer_check['customers_firstname'])) . "\n\n" . sprintf(EMAIL_WELCOME, STORE_NAME) . "\n\n" . EMAIL_TEXT . "\n\n\n" . sprintf(EMAIL_CONTACT, STORE_OWNER_EMAIL_ADDRESS);
		  tep_mail($name, $email_address, $email_subject, $email_text, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

		  $messageStack->add('header', ENTRY_REGISTRATION_SUCCCESS, 'success');
		} else {
		  $messageStack->add('header', ENTRY_INVALID_KEY_ERROR);
		}
	  }
	} else {
	  $messageStack->add('header', sprintf(ENTRY_CUSTOMER_DOESNT_EXIST_ERROR, $email_address));
	}

  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>