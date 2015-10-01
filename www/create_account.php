<?php
  require('includes/application_top.php');

  $content = FILENAME_CREATE_ACCOUNT;
  $javascript = 'form_check.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $process = false;
  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process')) {
    $process = true;

    if (ACCOUNT_GENDER == 'true') {
      if (isset($HTTP_POST_VARS['gender'])) {
        $gender = tep_db_prepare_input($HTTP_POST_VARS['gender']);
      } else {
        $gender = false;
      }
    }
    $customer_type = tep_db_prepare_input($HTTP_POST_VARS['customer_type']);
	if ($customer_type!='private' && $customer_type!='corporate') $customer_type = 'private';

    $firstname = tep_db_prepare_input($HTTP_POST_VARS['firstname']);
    $middlename = tep_db_prepare_input($HTTP_POST_VARS['middlename']);
    $lastname = tep_db_prepare_input($HTTP_POST_VARS['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($HTTP_POST_VARS['dob']);
    $telephone = tep_db_prepare_input($HTTP_POST_VARS['telephone']);
    $fax = tep_db_prepare_input($HTTP_POST_VARS['fax']);
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);
    if (isset($HTTP_POST_VARS['newsletter'])) {
      $newsletter = tep_db_prepare_input($HTTP_POST_VARS['newsletter']);
    } else {
      $newsletter = false;
    }
    $password = tep_db_prepare_input($HTTP_POST_VARS['password']);
    $confirmation = tep_db_prepare_input($HTTP_POST_VARS['confirmation']);
	$card_series = tep_db_prepare_input($HTTP_POST_VARS['card_series']);
	$card_number = tep_db_prepare_input($HTTP_POST_VARS['card_number']);

    $error = false;

    if (empty($firstname) && ENTRY_FIRST_NAME_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_FIRST_NAME_ERROR);
    }

	if (ACCOUNT_MIDDLE_NAME == 'true' && empty($middlename) && ENTRY_MIDDLE_NAME_MIN_LENGTH == 'true') {
	  $error = true;

	  $messageStack->add('header', ENTRY_MIDDLE_NAME_ERROR);
	}

    if (empty($lastname) && ENTRY_LAST_NAME_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_GENDER == 'true' && ENTRY_GENDER_MIN_LENGTH == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('header', ENTRY_GENDER_ERROR);
      }
    }

    if (ACCOUNT_DOB == 'true' && (tep_not_null($dob) || ENTRY_DOB_MIN_LENGTH == 'true') ) {
	  if (empty($dob)) {
        $error = true;

        $messageStack->add('header', ENTRY_DOB_ERROR);
	  } elseif (checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4)) == false) {
        $error = true;

        $messageStack->add('header', ENTRY_DOB_CHECK_ERROR);
      }
    }

    if (empty($email_address)) {
      $error = true;

      $messageStack->add('header', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (tep_validate_email($email_address) == false) {
      $error = true;

      $messageStack->add('header', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    } else {
      $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
      $check_email = tep_db_fetch_array($check_email_query);
      if ($check_email['total'] > 0) {
        $error = true;

        $messageStack->add('header', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
      }
    }

	if ($customer_type=='corporate') {
	  $company = tep_db_prepare_input($HTTP_POST_VARS['company']);

	  $company_type_name = tep_db_prepare_input($HTTP_POST_VARS['company_type_name']);
	  if ($company_type_name==ENTRY_COMPANY_TYPE_NAME_OTHER) $company_type_name = tep_db_prepare_input($HTTP_POST_VARS['company_type_name_other']);
	  $company_tax_exempt = tep_db_prepare_input($HTTP_POST_VARS['company_tax_exempt']);
	  $company_tax_exempt_number = tep_db_prepare_input($HTTP_POST_VARS['company_tax_exempt_number']);

	  $company_full = tep_db_prepare_input($HTTP_POST_VARS['company_full']);
	  $company_inn = tep_db_prepare_input($HTTP_POST_VARS['company_inn']);
	  $company_kpp = tep_db_prepare_input($HTTP_POST_VARS['company_kpp']);
	  $company_ogrn = tep_db_prepare_input($HTTP_POST_VARS['company_ogrn']);
	  $company_okpo = tep_db_prepare_input($HTTP_POST_VARS['company_okpo']);
	  $company_okogu = tep_db_prepare_input($HTTP_POST_VARS['company_okogu']);
	  $company_okato = tep_db_prepare_input($HTTP_POST_VARS['company_okato']);
	  $company_okved = tep_db_prepare_input($HTTP_POST_VARS['company_okved']);
	  $company_okfs = tep_db_prepare_input($HTTP_POST_VARS['company_okfs']);
	  $company_okopf = tep_db_prepare_input($HTTP_POST_VARS['company_okopf']);
	  $company_address_corporate = tep_db_prepare_input($HTTP_POST_VARS['company_address_corporate']);
	  $company_address_post = tep_db_prepare_input($HTTP_POST_VARS['company_address_post']);
	  $company_telephone = tep_db_prepare_input($HTTP_POST_VARS['company_telephone']);
	  $company_fax = tep_db_prepare_input($HTTP_POST_VARS['company_fax']);
	  $company_bank = tep_db_prepare_input($HTTP_POST_VARS['company_bank']);
	  $company_bik = tep_db_prepare_input($HTTP_POST_VARS['company_bik']);
	  $company_ks = tep_db_prepare_input($HTTP_POST_VARS['company_ks']);
	  $company_rs = tep_db_prepare_input($HTTP_POST_VARS['company_rs']);
	  $company_general = tep_db_prepare_input($HTTP_POST_VARS['company_general']);
	  $company_financial = tep_db_prepare_input($HTTP_POST_VARS['company_financial']);

	  if (empty($company) && ENTRY_COMPANY_MIN_LENGTH == 'true') {
		$error = true;

		$messageStack->add('header', ENTRY_COMPANY_ERROR);
	  }
	  if (empty($company_inn) && ENTRY_COMPANY_INN_MIN_LENGTH == 'true') {
		$error = true;

		$messageStack->add('header', ENTRY_COMPANY_INN_ERROR);
	  }
	  if (empty($company_kpp) && ENTRY_COMPANY_KPP_MIN_LENGTH == 'true') {
		$error = true;

		$messageStack->add('header', ENTRY_COMPANY_KPP_ERROR);
	  }
	}

    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('header', ENTRY_PASSWORD_ERROR);
    } elseif ($password != $confirmation) {
      $error = true;

      $messageStack->add('header', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

	if (basename(PHP_SELF)==FILENAME_CREATE_ACCOUNT && (TERMS_OF_AGREEMENT=='registration' || TERMS_OF_AGREEMENT=='both') ) {
	  if ($HTTP_POST_VARS['agreement']!='1') {
		$error = true;

		$messageStack->add('header', ENTRY_AGREEMENT_ERROR);
	  }
	}

    if ($error == false) {
	  $customer_status = (tep_check_blacklist() ? '0' : '1');
	  $encrypted_password = tep_encrypt_password($password);
      $sql_data_array = array('customers_type' => $customer_type,
							  'customers_status' => $customer_status,
							  'customers_firstname' => trim($firstname . ' ' . $middlename),
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
							  'customers_email_address_confirmed' => (REGISTER_WITH_CONFIRMATION=='true' ? '0' : '1'),
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax,
                              'customers_newsletter' => $newsletter,
                              'customers_password' => $encrypted_password,
                              'shops_id' => (int)SHOP_ID);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

      tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

      $customer_id = tep_db_insert_id();

	  if ($customer_type=='corporate') {
		$sql_data_array = array('customers_id' => $customer_id,
								'companies_name' => $company,
								'companies_type_name' => $company_type_name,
								'companies_tax_exempt' => $company_tax_exempt,
								'companies_tax_exempt_number' => $company_tax_exempt_number,
								'companies_full_name' => $company_full,
								'companies_inn' => $company_inn,
								'companies_kpp' => $company_kpp,
								'companies_ogrn' => $company_ogrn,
								'companies_okpo' => $company_okpo,
								'companies_okogu' => $company_okogu,
								'companies_okato' => $company_okato,
								'companies_okved' => $company_okved,
								'companies_okfs' => $company_okfs,
								'companies_okopf' => $company_okopf,
								'companies_address_corporate' => $company_address_corporate,
								'companies_address_post' => $company_address_post,
								'companies_telephone' => $company_telephone,
								'companies_fax' => $company_fax,
								'companies_bank' => $company_bank,
								'companies_bik' => $company_bik,
								'companies_ks' => $company_ks,
								'companies_rs' => $company_rs,
								'companies_general' => $company_general,
								'companies_financial' => $company_financial);

		tep_db_perform(TABLE_COMPANIES, $sql_data_array);
	  }

	  $customer_info_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_info = tep_db_fetch_array($customer_info_query);
	  $company_info_query = tep_db_query("select * from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_info['customers_id'] . "'");
	  $company_info = tep_db_fetch_array($company_info_query);
	  if (!is_array($company_info)) $company_info = array();
	  $customer_info = array_merge($customer_info, $company_info);

      tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

// build the message content
      $name = $firstname . ' ' . $lastname;

      if (ACCOUNT_GENDER == 'true') {
         if ($gender == 'm') {
           $email_text = sprintf(EMAIL_GREET_MR, $lastname);
         } else {
           $email_text = sprintf(EMAIL_GREET_MS, $lastname);
         }
      } else {
        $email_text = sprintf(EMAIL_GREET_NONE, trim($firstname . ' ' . $middlename));
      }

	  if (REGISTER_WITH_CONFIRMATION=='true') {
		list($activation_key) = explode(':', $encrypted_password);
		$email_subject = sprintf(EMAIL_SUBJECT_BEFORE, STORE_NAME);
		$email_text .= "\n\n" . sprintf(EMAIL_WELCOME_BEFORE, STORE_NAME) . "\n\n" . EMAIL_TEXT_BEFORE . "\n\n\n" . sprintf(EMAIL_CONTACT, STORE_OWNER_EMAIL_ADDRESS);
		$email_text = str_replace(array('{{store_name}}', '{{email_address}}', '{{confirmation_link}}', '{{password}}'), array(STORE_NAME, $email_address, tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, 'email=' . urlencode($email_address) . '&key=' . urlencode($activation_key), 'SSL', false), $password), $email_text);
	  } else {
		$customer_first_name = $firstname;
		$customer_middle_name = $middlename;
		$customer_last_name = $lastname;
		tep_session_register('customer_id');
		tep_session_register('customer_first_name');
		tep_session_register('customer_middle_name');
		tep_session_register('customer_last_name');
		tep_session_register('customer_status');
		tep_session_register('customer_type');

// restore cart contents
		$cart->restore_contents();

		$email_subject = sprintf(EMAIL_SUBJECT, STORE_NAME);
		$email_text .= "\n\n" . sprintf(EMAIL_WELCOME, STORE_NAME) . "\n\n" . EMAIL_TEXT . "\n\n\n" . sprintf(EMAIL_CONTACT, STORE_OWNER_EMAIL_ADDRESS) . "\n\n\n" . sprintf(EMAIL_WARNING, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	  }
	  tep_mail($name, $email_address, $email_subject, $email_text, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	  $is_blacklisted = tep_check_blacklist();
	  $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
	  if (tep_not_null($comments) && !$is_blacklisted) {
		$search_array = array('content-transfer-encoding:', 'content-type:', 'to:', 'from:', 'bcc:', 'cc:', 'subject:');
		$name = trim($customer_first_name . ' ' . $customer_middle_name) . ' ' . $customer_last_name;
		reset($search_array);
		while (list(, $search_word) = each($search_array)) {
		  $email_address = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $email_address);
		  $name = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $name);
		}
		$email_address = substr(preg_replace('/[^-@_a-z0-9\.]/i', '', $email_address), 0, 64);
		$name = substr(preg_replace('/[^-\sa-z0-9\.àáâãäå¸æçèéêëìíîïðñòóôõö÷øùúûüýþÿÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß]/i', '', $name), 0, 32);
		$email_enquiry = ENTRY_CONTACT_US_ENQUIRY . ' ' . $comments . "\n\n" . 
						 ENTRY_CONTACT_US_NAME . ' ' . $name . "\n\n" . 
						 ENTRY_CONTACT_US_EMAIL . ' ' . $email_address;
		if (tep_not_null($telephone)) $email_enquiry .= "\n\n" . ENTRY_CONTACT_US_PHONE_NUMBER . ' ' . $telephone;
		$email_enquiry .= "\n\n" . ENTRY_CONTACT_US_IP_ADDRESS . ' ' . tep_get_ip_address();

		$contact_us_subject = STORE_NAME . ' - ' . ENTRY_CONTACT_US_FEEDBACK_EMAIL_SUBJECT;
		tep_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $contact_us_subject, $email_enquiry, $name, $email_address);
	  }

	  if (REGISTER_WITH_CONFIRMATION=='true') {
		$confirm_registration = sprintf(TEXT_CONFIRM_REGISTRATION, $email_address);
		tep_session_register('confirm_registration');
		tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
	  } else {
		tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
	  }
    }
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>