<?php
  require('includes/application_top.php');

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
  if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  $content = FILENAME_PARTNER;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $action = isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '';

  $error = false;
  $error_text = '';
  switch ($action) {
	case 'login_process':
	  $partners_login = tep_db_prepare_input($HTTP_POST_VARS['partners_login']);
	  $partners_password = tep_db_prepare_input($HTTP_POST_VARS['partners_password']);

// Check if login exists
	  $check_partner_query = tep_db_query("select partners_id, partners_name, partners_password, partners_register_type, partners_status from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input($partners_login) . "'");
	  if (tep_db_num_rows($check_partner_query) < 1) {
		$error = true;
		$error_text = TEXT_NO_PARTNER_LOGIN_FOUND;
	  } else {
		$check_partner = tep_db_fetch_array($check_partner_query);
		if ($check_partner['partners_register_type']=='auto') {
		  $error = true;
		  $error_text = TEXT_NO_PARTNER_LOGIN_FOUND;
		} elseif ($check_partner['partners_status'] < '1') {
		  $error = true;
		  $error_text = TEXT_NO_PARTNER_LOGIN_FOUND;
		} else {
// Check that password is good
		  if (tep_validate_password($partners_password, $check_partner['partners_password']) || (tep_not_null(ACCOUNT_UNIVERSAL_PASSWORD) && $partners_password==ACCOUNT_UNIVERSAL_PASSWORD) ) {
			$partner_id = $check_partner['partners_id'];
			$partner_name = $check_customer['partners_name'];
			tep_session_register('partner_id');
			tep_session_register('partner_name');

			tep_db_query("update " . TABLE_PARTNERS . " set date_of_last_logon = now(), number_of_logons = number_of_logons + 1 where partners_id = '" . (int)$partner_id . "'");

			tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
		  } else {
			$error = true;
			$error_text = TEXT_PARTNER_LOGIN_ERROR;
		  }
		}
	  }

	  if ($error) {
		$messageStack->add('header', $error_text);
	  }
	  break;
    case 'register_process':
	  $process = true;

	  $partners_login = tep_db_prepare_input($HTTP_POST_VARS['partners_login']);
	  $partners_name = tep_db_prepare_input($HTTP_POST_VARS['partners_name']);
	  $partners_email_address = tep_db_prepare_input($HTTP_POST_VARS['partners_email_address']);
	  $partners_url = tep_db_prepare_input($HTTP_POST_VARS['partners_url']);
	  $partners_url = str_replace('http://', '', $partners_url);
	  $partners_comments = tep_db_prepare_input($HTTP_POST_VARS['partners_comments']);
	  $partners_bank = tep_db_prepare_input($HTTP_POST_VARS['partners_bank']);
	  $partners_telephone = tep_db_prepare_input($HTTP_POST_VARS['partners_telephone']);
	  $partners_password = tep_db_prepare_input($HTTP_POST_VARS['partners_password']);
	  $partners_confirmation = tep_db_prepare_input($HTTP_POST_VARS['partners_confirmation']);

	  $error = false;

	  $check_login_query = tep_db_query("select count(*) as total from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input($partners_login) . "' and partners_register_type = 'manual'");
	  $check_login = tep_db_fetch_array($check_login_query);
	  if ($check_login['total'] > 0) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_LOGIN_ERROR_EXISTS);
	  }

	  if (empty($partners_password)) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_PASSWORD_ERROR);
	  } elseif ($partners_password != $partners_confirmation) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_PASSWORD_ERROR_NOT_MATCHING);
	  }

	  if (empty($partners_name)) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_NAME_ERROR);
	  }

	  if (empty($partners_email_address)) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_EMAIL_ADDRESS_ERROR);
	  } elseif (tep_validate_email($partners_email_address) == false) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_EMAIL_ADDRESS_CHECK_ERROR);
	  }

	  if ($HTTP_POST_VARS['agreement']!='1') {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_AGREEMENT_ERROR);
	  }

	  if ($error == false) {
		$sql_data_array = array('partners_name' => $partners_name,
								'partners_email_address' => $partners_email_address,
                            	'partners_url' => $partners_url,
                            	'partners_bank' => $partners_bank,
                            	'partners_telephone' => $partners_telephone,
                            	'partners_login' => $partners_login,
                            	'partners_comments' => $partners_comments,
                            	'partners_password' => tep_encrypt_password($partners_password),
								'date_of_last_logon' => 'now()',
								'partners_register_type' => 'manual',
								'partners_comission' => str_replace(',', '.', PARTNERS_COMISSION_DEFAULT/100),
								'shops_id' => (int)SHOP_ID);

		$check_login_query = tep_db_query("select partners_id from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input($partners_login) . "' and partners_register_type = 'auto'");
		$check_login = tep_db_fetch_array($check_login_query);
		$partners_id = $check_login['partners_id'];

		if ((int)$partners_id > 0) {
		  $sql_data_array['last_modified'] = 'now()';
		  tep_db_perform(TABLE_PARTNERS, $sql_data_array, 'update', "partners_id = '" . (int)$partners_id . "'");
		} else {
		  $sql_data_array['date_added'] = 'now()';
		  tep_db_perform(TABLE_PARTNERS, $sql_data_array);
		  $partners_id = tep_db_insert_id();
		}

		$email_text = sprintf(EMAIL_PARTNER_GREET, $partners_name) . "\n\n" . sprintf(EMAIL_PARTNER_WELCOME, STORE_NAME) . "\n\n" . sprintf(EMAIL_PARTNER_TEXT, $partners_login, $partners_password) . "\n\n" . sprintf(EMAIL_PARTNER_CONTACT, STORE_OWNER_EMAIL_ADDRESS) . "\n\n" . sprintf(EMAIL_PARTNER_WARNING, STORE_OWNER_EMAIL_ADDRESS);
		tep_mail($partners_name, $partners_email_address, sprintf(EMAIL_PARTNER_SUBJECT, STORE_NAME), $email_text, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

		$partner_id = $partners_id;
		$partner_name = $partners_name;
		tep_session_register('partner_id');
		tep_session_register('partner_name');

		tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
	  }
	  break;
    case 'edit_process':
	  $process = true;

	  $partners_name = tep_db_prepare_input($HTTP_POST_VARS['partners_name']);
	  $partners_email_address = tep_db_prepare_input($HTTP_POST_VARS['partners_email_address']);
	  $partners_url = tep_db_prepare_input($HTTP_POST_VARS['partners_url']);
	  $partners_url = str_replace('http://', '', $partners_url);
	  $partners_comments = tep_db_prepare_input($HTTP_POST_VARS['partners_comments']);
	  $partners_bank = tep_db_prepare_input($HTTP_POST_VARS['partners_bank']);
	  $partners_telephone = tep_db_prepare_input($HTTP_POST_VARS['partners_telephone']);

	  $error = false;

	  if (empty($partners_name)) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_NAME_ERROR);
	  }

	  if (empty($partners_email_address)) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_EMAIL_ADDRESS_ERROR);
	  } elseif (tep_validate_email($partners_email_address) == false) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_EMAIL_ADDRESS_CHECK_ERROR);
	  }

	  if ($error == false) {
		$sql_data_array = array('partners_name' => $partners_name,
								'partners_email_address' => $partners_email_address,
                            	'partners_url' => $partners_url,
                            	'partners_bank' => $partners_bank,
                            	'partners_telephone' => $partners_telephone,
                            	'partners_comments' => $partners_comments,
								'date_of_last_logon' => 'now()',
								'partners_register_type' => 'manual');

		$sql_data_array['last_modified'] = 'now()';
		tep_db_perform(TABLE_PARTNERS, $sql_data_array, 'update', "partners_id = '" . (int)$partner_id . "'");

		$partner_name = $partners_name;

		$messageStack->add_session('header', SUCCESS_PARTNER_ACCOUNT_UPDATED, 'success');

		tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
	  }
	  break;
	case 'remind_password_process':
	  $partners_login = tep_db_prepare_input($HTTP_POST_VARS['partners_login']);

	  $check_partner_query = tep_db_query("select partners_id, partners_name, partners_password, partners_email_address from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input($partners_login) . "'");
	  if (tep_db_num_rows($check_partner_query)) {
		$check_partner = tep_db_fetch_array($check_partner_query);

		$partners_new_password = tep_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
		$partners_crypted_password = tep_encrypt_password($partners_new_password);

		tep_db_query("update " . TABLE_PARTNERS . " set partners_password = '" . tep_db_input($partners_crypted_password) . "' where partners_id = '" . (int)$check_partner['partners_id'] . "'");

		tep_mail($check_partner['partners_name'], $check_partner['partners_email_address'], sprintf(EMAIL_PARTNER_PASSWORD_REMINDER_SUBJECT, STORE_NAME), sprintf(EMAIL_PARTNER_PASSWORD_REMINDER_BODY, $REMOTE_ADDR, STORE_NAME, $partners_new_password), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

		$messageStack->add_session('header', SUCCESS_PARTNER_PASSWORD_SENT, 'success');

		tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
	  } else {
		$messageStack->add('header', TEXT_NO_PARTNER_LOGIN_FOUND);
	  }
	  break;
	case 'change_password_process':
	  $partners_password_current = tep_db_prepare_input($HTTP_POST_VARS['partners_password_current']);
	  $partners_password_new = tep_db_prepare_input($HTTP_POST_VARS['partners_password_new']);
	  $partners_password_confirmation = tep_db_prepare_input($HTTP_POST_VARS['partners_password_confirmation']);

	  $error = false;

	  if (strlen($partners_password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_PASSWORD_CURRENT_ERROR);
	  } elseif (strlen($partners_password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_PASSWORD_NEW_ERROR);
	  } elseif ($partners_password_new != $partners_password_confirmation) {
		$error = true;

		$messageStack->add('header', ENTRY_PARTNER_PASSWORD_NEW_ERROR_NOT_MATCHING);
	  }

	  if ($error == false) {
		$check_partner_query = tep_db_query("select partners_password from " . TABLE_PARTNERS . " where partners_id = '" . (int)$partner_id . "'");
		$check_partner = tep_db_fetch_array($check_partner_query);

		if (tep_validate_password($partners_password_current, $check_partner['partners_password']) || (tep_not_null(ACCOUNT_UNIVERSAL_PASSWORD) && $partners_password_current==ACCOUNT_UNIVERSAL_PASSWORD) ) {
		  tep_db_query("update " . TABLE_PARTNERS . " set partners_password = '" . tep_encrypt_password($partners_password_new) . "', last_modified = now() where partners_id = '" . (int)$partner_id . "'");

		  $messageStack->add_session('header', SUCCESS_PARTNER_PASSWORD_UPDATED, 'success');

		  tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
		} else {
		  $error = true;

		  $messageStack->add('header', ERROR_PARTNER_CURRENT_PASSWORD_NOT_MATCHING);
		}
	  }
	  break;
	case 'logoff':
	  tep_session_unregister('partner_id');
	  tep_session_unregister('partner_name');

	  $messageStack->add_session('header', SUCCESS_PARTNER_LOGOFF, 'success');

	  tep_redirect(tep_href_link(FILENAME_PARTNER, '', 'SSL'));
	  break;
  }

  $breadcrumb->add($page['pages_name'], tep_href_link('/partners/', '', 'SSL'));

  if (tep_session_is_registered('partner_id')) {
	if ($action=='show_statistics') {
	  $breadcrumb->add(PAGE_TITLE_PARTNER_SHOW_STATISTICS, tep_href_link(FILENAME_PARTNER, 'action=' . $action, 'SSL'));
	} elseif ($action=='edit' || $action=='edit_process') {
	  $breadcrumb->add(PAGE_TITLE_PARTNER_EDIT, tep_href_link(FILENAME_PARTNER, 'action=' . $action, 'SSL'));
	} elseif ($action=='change_password' || $action=='change_password_process') {
	  $breadcrumb->add(PAGE_TITLE_PARTNER_CHANGE_PASSWORD, tep_href_link(FILENAME_PARTNER, 'action=' . $action, 'SSL'));
	} else {
	  $breadcrumb->add(PAGE_TITLE_PARTNER_ACCOUNT, tep_href_link(FILENAME_PARTNER, '', 'SSL'));
	}
  } elseif ($action=='remind_password' || $action=='remind_password_process') {
	$breadcrumb->add(PAGE_TITLE_PARTNER_REMIND_PASSWORD, tep_href_link(FILENAME_PARTNER, 'action=' . $action, 'SSL'));
  } elseif ($action=='register' || $action=='register_process') {
	$breadcrumb->add(PAGE_TITLE_PARTNER_REGISTER, tep_href_link(FILENAME_PARTNER, 'action=' . $action, 'SSL'));
  } else {
	$breadcrumb->add(PAGE_TITLE_PARTNER_LOGIN, tep_href_link(FILENAME_PARTNER, '', 'SSL'));
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>