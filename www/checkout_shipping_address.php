<?php
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  $content = FILENAME_CHECKOUT_SHIPPING_ADDRESS;
  $javascript = 'checkout_shipping_address.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual' || ALLOW_CHECKOUT_SHIPPING=='false') {
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    $shipping = false;
    if (!tep_session_is_registered('sendto')) tep_session_register('sendto');
    $sendto = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $error = false;
  $process = false;
  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'submit')) {
// process a new shipping address
    if (tep_not_null($HTTP_POST_VARS['firstname']) && tep_not_null($HTTP_POST_VARS['lastname']) && tep_not_null($HTTP_POST_VARS['street_address'])) {
      $process = true;

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
      if (ACCOUNT_STATE == 'true') {
        if (isset($HTTP_POST_VARS['zone_id'])) {
          $zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
        } else {
          $zone_id = false;
        }
        $state = tep_db_prepare_input($HTTP_POST_VARS['state']);
      }
	  $telephone = tep_db_prepare_input($HTTP_POST_VARS['telephone']);
	  $fax = tep_db_prepare_input($HTTP_POST_VARS['fax']);
	  if ($is_dummy_account) $temp_email_address = tep_db_prepare_input($HTTP_POST_VARS['temp_email_address']);

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
		  if ($postcode_check['total'] < 1 && ENTRY_POSTCODE_CHECK=='true') {
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

	  if ($is_dummy_account) {
		if (empty($telephone) && empty($temp_email_address)) {
//		  $error = true;

//		  $messageStack->add('header', ENTRY_TELEPHONE_NUMBER_OR_EMAIL_ADDRESS_ERROR);
		}
	  }

      if ($error == false) {
        if (!tep_session_is_registered('sendto')) tep_session_register('sendto');

		tep_db_query("insert into " . TABLE_ADDRESS_BOOK . " (customers_id, entry_firstname, entry_lastname, entry_street_address, entry_telephone, entry_city, entry_country_id, entry_zone_id, entry_state, entry_gender, entry_suburb, entry_postcode) values ('" . (int)$customer_id . "', '" . trim(tep_db_input($firstname . ' ' . $middlename)) . "', '" . tep_db_input($lastname) . "', '" . tep_db_input($street_address) . "', '" . tep_db_input($telephone) . "', '" . tep_db_input($city) . "', '" . tep_db_input($country) . "', '" . (int)$zone_id . "', '" . tep_db_input($state) . "', '" . tep_db_input($gender) . "', '" . tep_db_input($suburb) . "', '" . tep_db_input($postcode) . "')");

        $sendto = tep_db_insert_id();

        if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');

		if (tep_count_customer_address_book_entries() <= 1 && tep_not_null($telephone)) tep_db_query("update " . TABLE_CUSTOMERS . " set customers_telephone = '" . tep_db_input($telephone) . "', customers_default_address_id = '" . (int)$sendto . "' where customers_id = '" . (int)$customer_id . "'");

		if ($is_dummy_account) {
		  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_gender = '" . tep_db_input($gender) . "', customers_firstname = '" . trim(tep_db_input($firstname . ' ' . $middlename)) . "', customers_lastname = '" . tep_db_input($lastname) . "', customers_email_address = '" . tep_db_input($temp_email_address) . "', customers_telephone = '" . tep_db_input($telephone) . "', customers_fax = '" . tep_db_input($fax) . "' where customers_id = '" . (int)$customer_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      }
// process the selected shipping destination
    } elseif (isset($HTTP_POST_VARS['address'])) {
      $reset_shipping = false;
      if (tep_session_is_registered('sendto')) {
        if ($sendto != $HTTP_POST_VARS['address']) {
          if (tep_session_is_registered('shipping')) {
            $reset_shipping = true;
          }
        }
      } else {
        tep_session_register('sendto');
      }

      $sendto = $HTTP_POST_VARS['address'];

      $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
      $check_address = tep_db_fetch_array($check_address_query);

      if ($check_address['total'] == '1') {
        if ($reset_shipping == true) tep_session_unregister('shipping');
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      } else {
        tep_session_unregister('sendto');
      }
    } else {
      if (!tep_session_is_registered('sendto')) tep_session_register('sendto');
      $sendto = $customer_default_address_id;

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// if no shipping destination address was selected, use their own address as default
  if (!tep_session_is_registered('sendto')) {
    $sendto = $customer_default_address_id;
  }

  $addresses_count = tep_count_customer_address_book_entries();

  $shopping_cart_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_SHOPPING_CART) . "' and language_id = '" . (int)$languages_id . "'");
  $shopping_cart_page = tep_db_fetch_array($shopping_cart_page_query);
  $breadcrumb->add($shopping_cart_page['pages_name'], tep_href_link(FILENAME_SHOPPING_CART));

  $checkout_shipping_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_CHECKOUT_SHIPPING) . "' and language_id = '" . (int)$languages_id . "'");
  $checkout_shipping_page = tep_db_fetch_array($checkout_shipping_page_query);

  $breadcrumb->add($checkout_shipping_page['pages_name'], tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>