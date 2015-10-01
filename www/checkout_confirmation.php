<?php
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    if (is_object($navigation)) $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  } elseif (empty($shipping) && ALLOW_CHECKOUT_SHIPPING=='true' && $cart->get_content_type()!='virtual') {
	$messageStack->add_session('header', ERROR_NO_SHIPPING_MODULE_SELECTED);
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if ( (!tep_session_is_registered('sendto') || (int)$sendto==0) && ALLOW_CHECKOUT_SHIPPING=='true' && $cart->get_content_type()!='virtual') {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  $content = FILENAME_CHECKOUT_CONFIRMATION;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (!tep_session_is_registered('payment')) tep_session_register('payment');
  if (isset($HTTP_POST_VARS['payment'])) $payment = $HTTP_POST_VARS['payment'];

  if (empty($payment) && ALLOW_CHECKOUT_PAYMENT=='true') {
	$messageStack->add_session('header', ERROR_NO_PAYMENT_MODULE_SELECTED);
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (isset($HTTP_POST_VARS['comments'])) $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) && ALLOW_CHECKOUT_PAYMENT=='true' ) {
	$messageStack->add_session('header', ERROR_NO_PAYMENT_MODULE_SELECTED);
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
  }

  $shopping_cart_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_SHOPPING_CART) . "' and language_id = '" . (int)$languages_id . "'");
  $shopping_cart_page = tep_db_fetch_array($shopping_cart_page_query);
  $breadcrumb->add($shopping_cart_page['pages_name'], tep_href_link(FILENAME_SHOPPING_CART));

  if (ALLOW_CHECKOUT_SHIPPING=='true') {
	$checkout_shipping_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_CHECKOUT_SHIPPING) . "' and language_id = '" . (int)$languages_id . "'");
	$checkout_shipping_page = tep_db_fetch_array($checkout_shipping_page_query);
	$breadcrumb->add($checkout_shipping_page['pages_name'], tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (ALLOW_CHECKOUT_PAYMENT=='true') {
	$checkout_payment_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_CHECKOUT_PAYMENT) . "' and language_id = '" . (int)$languages_id . "'");
	$checkout_payment_page = tep_db_fetch_array($checkout_payment_page_query);
	$breadcrumb->add($checkout_payment_page['pages_name'], tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>