<?php
  require('includes/application_top.php');
  require('includes/classes/http_client.php');

  if (tep_session_is_registered('customer_id')) {
	$customer_check_query = tep_db_query("select 1 from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	if (tep_db_num_rows($customer_check_query) < 1) tep_session_unregister('customer_id');
  }

  if ($HTTP_GET_VARS['registration']=='off' && !tep_session_is_registered('customer_id') && ALLOW_CHECKOUT_FOR_UNREGISTERED=='true') {
	$is_dummy_account = true;
	tep_session_register('is_dummy_account');
	tep_db_query("insert into " . TABLE_CUSTOMERS . " (customers_status, customers_is_dummy_account, shops_id) values ('" . (tep_check_blacklist() ? '0' : '1') . "', '1', '" . (int)SHOP_ID . "')");
	$customer_id = tep_db_insert_id();
	tep_session_register('customer_id');
	tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");
  }

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  $content = FILENAME_CHECKOUT_SHIPPING;
  $javascript = 'checkout_shipping.js';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

// if there is min order sum less then cart products sum, redirect them to the shopping cart page
  if ((int)MIN_ORDER_SUM > 0) {
	$products_sum = $cart->show_total();
	if ($products_sum < MIN_ORDER_SUM) {
	  $messageStack->add_session('header', sprintf(ERROR_MIN_ORDER_SUM, $currencies->format(MIN_ORDER_SUM)));
	  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
	}
  }

  if ( isset($HTTP_POST_VARS['self_delivery']) && ($HTTP_POST_VARS['self_delivery'] == 'true') ) {
    $delivery_code = tep_db_prepare_input($HTTP_POST_VARS['shipping_code']);
    $delivery_to_self_firstname = tep_db_prepare_input($HTTP_POST_VARS['delivery_to_self_firstname']);
    $delivery_to_self_lastname = tep_db_prepare_input($HTTP_POST_VARS['delivery_to_self_lastname']);
	$delivery_to_self_telephone = tep_db_prepare_input($HTTP_POST_VARS['delivery_to_self_telephone']);
	$delivery_to_self_email = tep_db_prepare_input($HTTP_POST_VARS['delivery_to_self_email']);

	list(, $delivery_code_id) = explode('_', $delivery_code);

	$error = false;

	if (empty($delivery_code)) {
	  $error = true;

	  $messageStack->add_session('header', ENTRY_SELF_DELIVERY_ADDRESS_ERROR);
	}

	if (empty($delivery_to_self_firstname) && ENTRY_FIRST_NAME_MIN_LENGTH == 'true') {
	  $error = true;

	  $messageStack->add_session('header', ENTRY_FIRST_NAME_ERROR);
	}

	if (empty($delivery_to_self_lastname) && ENTRY_LAST_NAME_MIN_LENGTH == 'true') {
	  $error = true;

	  $messageStack->add_session('header', ENTRY_LAST_NAME_ERROR);
	}

	if (empty($delivery_to_self_telephone) && ENTRY_TELEPHONE_NUMBER_MIN_LENGTH == 'true') {
	  $error = true;

	  $messageStack->add_session('header', ENTRY_TELEPHONE_NUMBER_ERROR);
	}

    if ($error == true) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
	} else {
	  if (!tep_session_is_registered('sendto')) tep_session_register('sendto');

	  if ($is_dummy_account==true) {
		tep_db_query("update " . TABLE_CUSTOMERS . " set customers_gender = '" . tep_db_input($gender) . "', customers_firstname = '" . tep_db_input($delivery_to_self_firstname) . "', customers_lastname = '" . tep_db_input($delivery_to_self_lastname) . "', customers_email_address = '" . tep_db_input($delivery_to_self_email) . "', customers_telephone = '" . tep_db_input($delivery_to_self_telephone) . "' where customers_id = '" . (int)$customer_id . "'");
	  }

	  tep_db_query("insert into " . TABLE_ADDRESS_BOOK . " (customers_id, entry_firstname, entry_lastname, entry_street_address, entry_telephone, entry_city, entry_country_id, entry_zone_id, entry_state, entry_gender, entry_suburb, entry_postcode) select '" . (int)$customer_id . "', '" . tep_db_input($delivery_to_self_firstname) . "', '" . tep_db_input($delivery_to_self_lastname) . "', sd.entry_street_address, '" . tep_db_input($delivery_to_self_telephone) . "', sd.entry_city, sd.entry_country_id, sd.entry_zone_id, z.zone_name, '', sd.entry_suburb, sd.entry_postcode from " . TABLE_SELF_DELIVERY . " sd, " . TABLE_ZONES . " z where sd.self_delivery_id = '" . (int)$delivery_code_id . "' and z.zone_id = sd.entry_zone_id and z.zone_country_id = sd.entry_country_id");

      $sendto = tep_db_insert_id();

      if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');

	  if (tep_count_customer_address_book_entries() <= 1 && tep_not_null($delivery_to_self_telephone)) tep_db_query("update " . TABLE_CUSTOMERS . " set customers_telephone = '" . tep_db_input($delivery_to_self_telephone) . "', customers_default_address_id = '" . (int)$sendto . "' where customers_id = '" . (int)$customer_id . "'");
    }
  }

  if (tep_count_customer_address_book_entries()==0) {
	$messageStack->add_session('header', ERROR_ADDRESS_BOOK_EMPTY);
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
  }

// if no shipping destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('sendto')) {
  	tep_session_register('sendto');
    $sendto = $customer_default_address_id;
  } else {
// verify the selected shipping address
  	$check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $sendto = $customer_default_address_id;
      if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
    }
  }
 
  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
  $cartID = $cart->cartID;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual' || ALLOW_CHECKOUT_SHIPPING=='false') {
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    $shipping = false;
    $sendto = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    $free_shipping = false;
    if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;

      include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }

  if (ACCOUNT_POSTCODE == 'true' && (tep_not_null($order->delivery['postcode']) || ENTRY_POSTCODE_MIN_LENGTH == 'true') ) {
	$total_cities_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$order->delivery['country_id'] . "'");
	$total_cities = tep_db_fetch_array($total_cities_query);
	$check_city_exists = false;
	if ($total_cities['total'] > 0) {
	  $check_city_exists = true;
	  $postcode_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$order->delivery['country_id'] . "' and (city_id = '" . tep_db_input($order->delivery['postcode']) . "'" . ((int)$order->delivery['postcode']>0 ? " or old_id = '" . (int)$order->delivery['postcode'] . "'" : "") . ")");
	  $postcode_check = tep_db_fetch_array($postcode_check_query);
	  if ($postcode_check['total'] < 1 && ENTRY_POSTCODE_CHECK=='true') {
		$error = true;

		$messageStack->add('header', ENTRY_POSTCODE_ERROR_1);
	  }
	}
  }

// process the selected shipping method
  if ( isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process') ) {
    if (!tep_session_is_registered('comments')) tep_session_register('comments');
    if (tep_not_null($HTTP_POST_VARS['comments'])) {
      $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
    }

    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');

//		if ($customer_id==2) { echo '<pre>' . print_r($HTTP_POST_VARS, true) . '</pre>' . tep_count_shipping_modules(); die; }

    if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($HTTP_POST_VARS['shipping_code'])) && (strpos($HTTP_POST_VARS['shipping_code'], '_')) ) {
        $shipping_module = $HTTP_POST_VARS['shipping_code'];

        list($module, $method) = explode('_', $shipping_module);
//		if ($customer_id==2) { echo $shipping_module; die; }
        if ( is_object($$module) || ($shipping_module == 'free_free') ) {
          if ($shipping_module == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote['error'])) {
            tep_session_unregister('shipping');
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $shipping = array('id' => $shipping_module,
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . (tep_not_null($quote[0]['methods'][0]['title']) ? ' (' . $quote[0]['methods'][0]['title'] . ')' : '')),
                                'cost' => $quote[0]['methods'][0]['cost']);

              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          tep_session_unregister('shipping');
        }
      }
    } else {
      $shipping = false;

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }

// get all available shipping quotes
  $quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  
  if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();

  $shopping_cart_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_SHOPPING_CART) . "' and language_id = '" . (int)$languages_id . "'");
  $shopping_cart_page = tep_db_fetch_array($shopping_cart_page_query);
  $breadcrumb->add($shopping_cart_page['pages_name'], tep_href_link(FILENAME_SHOPPING_CART));

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
  
  
  
  
  
  
?>