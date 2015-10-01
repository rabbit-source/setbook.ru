<?php
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the shopping cart page
  if (!tep_session_is_registered('customer_id')) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  $content = FILENAME_CHECKOUT_SUCCESS;

// Get last order id for checkout_success
  $orders_query = tep_db_query("select orders_id, payment_method from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by orders_id desc limit 1");
  $orders = tep_db_fetch_array($orders_query);
  $last_order = $orders['orders_id'];
  $order_total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders['orders_id'] . "' and class = 'ot_total'");
  $order_total = tep_db_fetch_array($order_total_query);
  $order_total_sum = $order_total['value'];
  $payment_method = $orders['payment_method'];

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', sprintf($page['pages_additional_description'], $last_order));
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name']);

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
