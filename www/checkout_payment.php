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

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  } elseif (empty($shipping) && ALLOW_CHECKOUT_SHIPPING=='true' && $cart->get_content_type()!='virtual') {
	$messageStack->add_session('header', ERROR_NO_SHIPPING_MODULE_SELECTED);
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  tep_log_ex('shipping');
  
// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

  $content = FILENAME_CHECKOUT_PAYMENT;
  $javascript = 'checkout_payment.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (tep_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }

// if no billing destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('billto')) {
    tep_session_register('billto');
    $billto = $customer_default_address_id;
  } else {
// verify the selected billing address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $billto = $customer_default_address_id;
      if (tep_session_is_registered('payment')) tep_session_unregister('payment');
    }
  }
  $billto = $sendto;

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (isset($HTTP_POST_VARS['comments'])) $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);

  if (ALLOW_CHECKOUT_PAYMENT=='false') {
    if (!tep_session_is_registered('payment')) tep_session_register('payment');
    $payment = false;
    if (!tep_session_is_registered('billto')) tep_session_register('billto');
    $billto = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
  }

  if (ACCOUNT_POSTCODE == 'true' && (tep_not_null($order->billing['postcode']) || ENTRY_POSTCODE_MIN_LENGTH == 'true') ) {
	$total_cities_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$order->billing['country_id'] . "'");
	$total_cities = tep_db_fetch_array($total_cities_query);
	$check_city_exists = false;
	if ($total_cities['total'] > 0) {
	  $check_city_exists = true;
	  $postcode_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$order->billing['country_id'] . "' and (city_id = '" . tep_db_input($order->billing['postcode']) . "'" . ((int)$order->billing['postcode']>0 ? " or old_id = '" . (int)$order->billing['postcode'] . "'" : "") . ")");
	  $postcode_check = tep_db_fetch_array($postcode_check_query);
	  if ($postcode_check['total'] < 1 && ENTRY_POSTCODE_CHECK=='true') {
		$error = true;

		tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
	  }
	}
  }

  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();

// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  $shopping_cart_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_SHOPPING_CART) . "' and language_id = '" . (int)$languages_id . "'");
  $shopping_cart_page = tep_db_fetch_array($shopping_cart_page_query);
  $breadcrumb->add($shopping_cart_page['pages_name'], tep_href_link(FILENAME_SHOPPING_CART));

  if (ALLOW_CHECKOUT_SHIPPING=='true') {
	$checkout_shipping_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_CHECKOUT_SHIPPING) . "' and language_id = '" . (int)$languages_id . "'");
	$checkout_shipping_page = tep_db_fetch_array($checkout_shipping_page_query);
	$breadcrumb->add($checkout_shipping_page['pages_name'], tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>