<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = FILENAME_ACCOUNT_EDIT;
  $javascript = 'form_check.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process')) {
    $firstname = tep_db_prepare_input($HTTP_POST_VARS['firstname']);
    $middlename = tep_db_prepare_input($HTTP_POST_VARS['middlename']);
    $lastname = tep_db_prepare_input($HTTP_POST_VARS['lastname']);
    if (ACCOUNT_GENDER == 'true') $gender = tep_db_prepare_input($HTTP_POST_VARS['gender']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($HTTP_POST_VARS['dob']);
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);
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
    }

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('header', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_id != '" . (int)$customer_id . "'");
    $check_email = tep_db_fetch_array($check_email_query);
    if ($check_email['total'] > 0) {
      $error = true;

      $messageStack->add('header', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }

    $customer_type = tep_db_prepare_input($HTTP_POST_VARS['customer_type']);
	if ($customer_type!='private' && $customer_type!='corporate') $customer_type = 'private';

	if ($customer_type=='corporate') {
	  $company = tep_db_prepare_input($HTTP_POST_VARS['company']);
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

    if ($error == false) {
	  $sql = "update " . TABLE_CUSTOMERS . " set customers_firstname = '" . trim(tep_db_input($firstname . ' ' . $middlename)) . "', customers_lastname = '" . tep_db_input($lastname) . "', customers_email_address = '" . tep_db_input($email_address) . "'";

      if (ACCOUNT_GENDER == 'true') $sql .= ", customers_gender = '" . tep_db_input($gender) . "'";
      if (ACCOUNT_DOB == 'true') $sql .= ", customers_dob = '" . tep_db_input(tep_date_raw($dob)) . "'";

	  $sql .= " where customers_id = '" . (int)$customer_id . "'";

      tep_db_query($sql);

	  $customer_first_name = $firstname;
	  $customer_middle_name = $middlename;
	  $customer_last_name = $lastname;

      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");

	  tep_db_query("update " . TABLE_ADDRESS_BOOK . " set entry_lastname = '" . tep_db_input($lastname) . "', entry_firstname = '" . trim(tep_db_input($firstname . ' ' . $middlename)) . "' where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "'");

	  if ($customer_type=='corporate') {
		$sql_data_array = array('customers_id' => $customer_id,
								'companies_name' => $company,
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

		tep_db_perform(TABLE_COMPANIES, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");
	  }

// reset the session variables
      $customer_first_name = $firstname;
      $customer_middle_name = $middlename;
      $customer_last_name = $lastname;

      $messageStack->add_session('header', SUCCESS_ACCOUNT_UPDATED, 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  }

  $account_query = tep_db_query("select c.customers_type, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, c.customers_telephone, c.customers_fax from " . TABLE_CUSTOMERS . " c where c.customers_id = '" . (int)$customer_id . "'");
  $account = tep_db_fetch_array($account_query);

  $company_info = array();
  $company_array_query = tep_db_query("select * from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_id . "'");
  $company_array = tep_db_fetch_array($company_array_query);
  if (!is_array($company_array)) $company_array = array();
  reset($company_array);
  while (list($k, $v) = each($company_array)) {
	$k = str_replace('companies_', 'company_', $k);
	$k = str_replace('company_full_name', 'company_full', $k);
	$k = str_replace('company_name', 'company', $k);
	$company_info[$k] = $v;
  }

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "' and language_id = '" . (int)$languages_id . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>