<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = FILENAME_ADDRESS_BOOK_PROCESS;
  if (!isset($HTTP_GET_VARS['delete'])) {
	$javascript = 'form_check.js.php';
  }

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'deleteconfirm') && isset($HTTP_GET_VARS['delete']) && is_numeric($HTTP_GET_VARS['delete'])) {
    tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$HTTP_GET_VARS['delete'] . "' and customers_id = '" . (int)$customer_id . "'");

    $messageStack->add_session('header', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

    tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }

// error checking when updating or adding an entry
  $process = false;
  if (isset($HTTP_POST_VARS['action']) && (($HTTP_POST_VARS['action'] == 'process') || ($HTTP_POST_VARS['action'] == 'update'))) {
    $process = true;
    $error = false;

    if (ACCOUNT_GENDER == 'true') $gender = tep_db_prepare_input($HTTP_POST_VARS['gender']);
    if (ACCOUNT_COMPANY == 'true') $company = tep_db_prepare_input($HTTP_POST_VARS['company']);
    $firstname = tep_db_prepare_input($HTTP_POST_VARS['firstname']);
    $middlename = tep_db_prepare_input($HTTP_POST_VARS['middlename']);
    $lastname = tep_db_prepare_input($HTTP_POST_VARS['lastname']);
    $street_address = tep_db_prepare_input($HTTP_POST_VARS['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = tep_db_prepare_input($HTTP_POST_VARS['suburb']);
    if (ACCOUNT_POSTCODE == 'true') $postcode = tep_db_prepare_input($HTTP_POST_VARS['postcode']);
    $city = tep_db_prepare_input($HTTP_POST_VARS['city']);
    $country = tep_db_prepare_input($HTTP_POST_VARS['country']);
    if (ACCOUNT_STATE == 'true' && ENTRY_STATE_MIN_LENGTH == 'true') {
      if (isset($HTTP_POST_VARS['zone_id'])) {
        $zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
      } else {
        $zone_id = false;
      }
      $state = tep_db_prepare_input($HTTP_POST_VARS['state']);
    }
    $telephone = tep_db_prepare_input($HTTP_POST_VARS['telephone']);
    $fax = tep_db_prepare_input($HTTP_POST_VARS['fax']);

    if (ACCOUNT_GENDER == 'true' && ENTRY_GENDER_MIN_LENGTH == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('header', ENTRY_GENDER_ERROR);
      }
    }

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

    if (is_numeric($country) == false && ENTRY_COUNTRY_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_COUNTRY_ERROR);
    }

    if (ACCOUNT_POSTCODE == 'true' && (tep_not_null($postcode) || ENTRY_POSTCODE_MIN_LENGTH == 'true') ) {
	  $total_cities_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$country . "'");
	  $total_cities = tep_db_fetch_array($total_cities_query);
	  $check_city_exists = false;
	  if ($total_cities['total'] > 0 && ENTRY_POSTCODE_CHECK=='true') {
		$check_city_exists = true;
	  }
	  if (empty($postcode)) {
		$error = true;

		$messageStack->add('header', ENTRY_POSTCODE_ERROR);
	  } elseif ($check_city_exists == true) {
		$postcode_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$country . "' and (city_id = '" . tep_db_input($postcode) . "'" . ((int)$postcode>0 ? " or old_id = '" . (int)$postcode . "'" : "") . ")");
		$postcode_check = tep_db_fetch_array($postcode_check_query);
		if ($postcode_check['total'] < 1) {
		  $error = true;

		  $messageStack->add('header', ENTRY_POSTCODE_ERROR_1);
		}
	  }
	}

    if (ACCOUNT_STATE == 'true' && ENTRY_STATE_MIN_LENGTH == 'true') {
      $zone_id = 0;
      $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
      $check = tep_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
        $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and (zone_name like '" . tep_db_input($state) . "%' or zone_code like '%" . tep_db_input($state) . "%') order by zone_name limit 1");
        if (tep_db_num_rows($zone_query) > 0) {
          $zone = tep_db_fetch_array($zone_query);
          $zone_id = $zone['zone_id'];
        } else {
          $error = true;

          $messageStack->add('header', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (empty($state)) {
          $error = true;

          $messageStack->add('header', ENTRY_STATE_ERROR);
        }
      }
    }

    if (empty($city) && ENTRY_CITY_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_CITY_ERROR);
    }

    if (ACCOUNT_SUBURB == 'true' && empty($suburb) && ENTRY_SUBURB_MIN_LENGTH == 'true') {
	  $error = true;

	  $messageStack->add('header', ENTRY_SUBURB_ERROR);
	}

    if (empty($street_address) && ENTRY_STREET_ADDRESS_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (empty($telephone) && ENTRY_TELEPHONE_NUMBER_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if (empty($fax) && ENTRY_FAX_NUMBER_MIN_LENGTH == 'true') {
      $error = true;

      $messageStack->add('header', ENTRY_FAX_NUMBER_ERROR);
    }

    if ($error == false) {
      $sql_data_array = array('entry_firstname' => trim($firstname . ' ' . $middlename),
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_city' => $city,
                              'entry_country_id' => (int)$country,
                              'entry_telephone' => $telephone,
                              'entry_fax' => $fax);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
      if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
      if (ACCOUNT_POSTCODE == 'true') $sql_data_array['entry_postcode'] = $postcode;
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = (int)$zone_id;
          $sql_data_array['entry_state'] = $state;
        } else {
          $sql_data_array['entry_zone_id'] = (int)$zone_id;
          $sql_data_array['entry_state'] = $state;
        }
      }

      if ($HTTP_POST_VARS['action'] == 'update') {
        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int)$HTTP_GET_VARS['edit'] . "' and customers_id ='" . (int)$customer_id . "'");

// re-register session variables
        if ( (isset($HTTP_POST_VARS['primary']) && ($HTTP_POST_VARS['primary'] == 'on')) || ($HTTP_GET_VARS['edit'] == $customer_default_address_id) ) {
          $customer_first_name = $firstname;
          $customer_middle_name = $middlename;
          $customer_country_id = $country_id;
          $customer_zone_id = (($zone_id > 0) ? (int)$zone_id : '0');
          $customer_default_address_id = (int)$HTTP_GET_VARS['edit'];

          $sql_data_array = array('customers_firstname' => trim($firstname . ' ' . $middlename),
                                  'customers_lastname' => $lastname,
                                  'customers_default_address_id' => (int)$HTTP_GET_VARS['edit'],
								  'customers_telephone' => $telephone,
								  'customers_fax' => $fax);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;

          tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");
        }
      } else {
        $sql_data_array['customers_id'] = (int)$customer_id;
        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

        $new_address_book_id = tep_db_insert_id();

// re-register session variables
        if (isset($HTTP_POST_VARS['primary']) && ($HTTP_POST_VARS['primary'] == 'on')) {
          $customer_first_name = $firstname;
          $customer_middle_name = $middlename;
          $customer_country_id = $country_id;
          $customer_zone_id = (($zone_id > 0) ? (int)$zone_id : '0');
          $customer_default_address_id = $new_address_book_id;

          $sql_data_array = array('customers_firstname' => trim($firstname . ' ' . $middlename),
                                  'customers_lastname' => $lastname,
								  'customers_telephone' => $telephone,
								  'customers_fax' => $fax);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          $sql_data_array['customers_default_address_id'] = $new_address_book_id;

          tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");
        }
      }

      $messageStack->add_session('header', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');

      tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  if (isset($HTTP_GET_VARS['edit']) && is_numeric($HTTP_GET_VARS['edit'])) {
    $entry_query = tep_db_query("select entry_gender, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id, entry_telephone, entry_fax from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$HTTP_GET_VARS['edit'] . "'");

    if (!tep_db_num_rows($entry_query)) {
      $messageStack->add_session('header', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

      tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }

    $entry = tep_db_fetch_array($entry_query);

	if (ACCOUNT_MIDDLE_NAME=='true') {
	  list($entry['entry_firstname'], $entry['entry_middlename']) = explode(' ', $entry['entry_firstname']);
	}
  } elseif (isset($HTTP_GET_VARS['delete']) && is_numeric($HTTP_GET_VARS['delete'])) {
    if ($HTTP_GET_VARS['delete'] == $customer_default_address_id) {
      $messageStack->add_session('header', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

      tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    } else {
      $check_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$HTTP_GET_VARS['delete'] . "' and customers_id = '" . (int)$customer_id . "'");
      $check = tep_db_fetch_array($check_query);

      if ($check['total'] < 1) {
        $messageStack->add_session('header', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
      }
    }
  } else {
    $entry = array();
  }

  if (!isset($HTTP_GET_VARS['delete']) && !isset($HTTP_GET_VARS['edit'])) {
    if (tep_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add_session('header', ERROR_ADDRESS_BOOK_FULL);

      tep_redirect(tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $address_book_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ADDRESS_BOOK) . "'");
  $address_book_page = tep_db_fetch_array($address_book_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($address_book_page['pages_name'], tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, tep_get_all_get_params(), 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>