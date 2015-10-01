<?php
  include('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    if (is_object($navigation)) $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if ( (!tep_session_is_registered('sendto') || (int)$sendto==0) && ALLOW_CHECKOUT_SHIPPING=='true' && $cart->get_content_type()!='virtual') {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if ( (tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!tep_session_is_registered('payment')) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
 }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

  if (TERMS_OF_AGREEMENT=='confirmation' || TERMS_OF_AGREEMENT=='both') {
	if ($HTTP_POST_VARS['agreement']!='1') {
	  $messageStack->add_session('header', ENTRY_AGREEMENT_ERROR);
	  tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
	}
  }

  $content = FILENAME_CHECKOUT_PROCESS;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

// load selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  if (sizeof($order->products)==0) {
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
  }

  if ($customer_id=='58543') {
	$order->info['subtotal'] = 0;
	reset($order->products);
	while (list($i) = each($order->products)) {
	  $order->products[$i]['price'] = round($order->products[$i]['price'] * 1.15);
	  $order->products[$i]['final_price'] = round($order->products[$i]['final_price'] * 1.15);
      $order->info['subtotal'] += tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'];
      $order->info['total'] = $order->info['subtotal'];
	}

	$shipping = array('id' => 'slf_0', 'title' => 'Самовывоз', 'cost' => 0);
  }

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

  $order_totals = $order_total_modules->process();

  if ($customer_id=='58543') {
	reset($order_totals);
	while (list($i) = each($order_totals)) {
	  if ($order_totals[$i]['code']=='ot_shipping') {
		$order_totals[$i]['title'] = 'Самовывоз';
		$order_totals[$i]['text'] = $currencies->format(0);
		$order_totals[$i]['value'] = '0';
	  }
	}

	$shipping = array('id' => 'slf_0', 'title' => 'Самовывоз', 'cost' => 0);
  }

// load the before_process function from the payment modules
  $payment_modules->before_process();

  $sql_data_array = array('orders_code' => $order->info['code'],
						  'customers_id' => $customer_id,
                          'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                          'customers_company' => $order->customer['company'],
						  'customers_company_full_name' => $order->customer['company_full'],
						  'customers_company_name' => $order->customer['company'],
						  'customers_company_inn' => $order->customer['company_inn'],
						  'customers_company_kpp' => $order->customer['company_kpp'],
						  'customers_company_ogrn' => $order->customer['company_ogrn'],
						  'customers_company_okpo' => $order->customer['company_okpo'],
						  'customers_company_okogu' => $order->customer['company_okogu'],
						  'customers_company_okato' => $order->customer['company_okato'],
						  'customers_company_okved' => $order->customer['company_okved'],
						  'customers_company_okfs' => $order->customer['company_okfs'],
						  'customers_company_okopf' => $order->customer['company_okopf'],
						  'customers_company_address_corporate' => $order->customer['company_address_corporate'],
						  'customers_company_address_post' => $order->customer['company_address_post'],
						  'customers_company_telephone' => $order->customer['company_telephone'],
						  'customers_company_fax' => $order->customer['company_fax'],
						  'customers_company_bank' => $order->customer['company_bank'],
						  'customers_company_rs' => $order->customer['company_rs'],
						  'customers_company_ks' => $order->customer['company_ks'],
						  'customers_company_bik' => $order->customer['company_bik'],
						  'customers_company_general' => $order->customer['company_general'],
						  'customers_company_financial' => $order->customer['company_financial'],
                          'customers_street_address' => $order->customer['street_address'],
                          'customers_suburb' => $order->customer['suburb'],
                          'customers_city' => $order->customer['city'],
                          'customers_postcode' => $order->customer['postcode'], 
                          'customers_state' => $order->customer['state'], 
                          'customers_country' => $order->customer['country']['title'], 
                          'customers_telephone' => $order->customer['telephone'], 
                          'customers_email_address' => $order->customer['email_address'], 
                          'customers_address_format_id' => $order->customer['format_id'], 
                          'customers_ip' => tep_get_ip_address(), 
                          'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'], 
                          'delivery_company' => $order->delivery['company'],
                          'delivery_street_address' => $order->delivery['street_address'], 
                          'delivery_suburb' => $order->delivery['suburb'], 
                          'delivery_city' => $order->delivery['city'], 
                          'delivery_postcode' => $order->delivery['postcode'], 
                          'delivery_state' => $order->delivery['state'], 
                          'delivery_country' => $order->delivery['country']['title'], 
                          'delivery_telephone' => $order->delivery['telephone'], 
                          'delivery_address_format_id' => $order->delivery['format_id'], 
                          'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'], 
                          'billing_company' => $order->billing['company'],
                          'billing_street_address' => $order->billing['street_address'], 
                          'billing_suburb' => $order->billing['suburb'], 
                          'billing_city' => $order->billing['city'], 
                          'billing_postcode' => $order->billing['postcode'], 
                          'billing_state' => $order->billing['state'], 
                          'billing_country' => $order->billing['country']['title'], 
                          'billing_telephone' => $order->billing['telephone'], 
                          'billing_address_format_id' => $order->billing['format_id'], 
                          'payment_method' => (strpos($order->info['payment_method'], '(')!==false ? trim(substr($order->info['payment_method'], 0, strpos($order->info['payment_method'], '('))) : $order->info['payment_method']), 
                          'cc_type' => $order->info['cc_type'], 
                          'cc_owner' => $order->info['cc_owner'], 
                          'cc_number' => (tep_not_null($order->info['cc_number']) ? substr($order->info['cc_number'], 0, 4) . str_repeat('X', strlen($order->info['cc_number']) - 8) . substr($order->info['cc_number'], -4) : ''), 
                          'cc_expires' => $order->info['cc_expires'], 
                          'check_account_type' => $order->info['check_account_type'], 
                          'check_bank_name' => $order->info['check_bank_name'], 
                          'check_routing_number' => $order->info['check_routing_number'], 
                          'check_account_number' => $order->info['check_account_number'], 
                          'date_purchased' => 'now()', 
                          'orders_status' => $order->info['order_status'], 
                          'currency' => $order->info['currency'], 
                          'currency_value' => $order->info['currency_value'], 
                          'orders_is_paid' => $order->info['is_paid'], 
                          'delivery_transfer' => tep_calculate_date_available($order->info['delivery_transfer']), 
						  'orders_ssl_enabled' => (ENABLE_SSL==true ? '1' : '0'), 
                          'shops_id' => (int)SHOP_ID);
  tep_db_perform(TABLE_ORDERS, $sql_data_array);
  $insert_id = tep_db_insert_id();
  tep_order_log($insert_id, '*** Order #' . $insert_id . ' created (pid=' . getmypid() . ', memory_peak=' . memory_get_peak_usage(true) . ')');
  tep_order_log($insert_id, tep_get_memory());
  
  $order_delivery_transfer = $order->info['delivery_transfer'];
  $order_delivery_country_code = $order->delivery['country']['iso_code_2'];

  $order_products_sum = 0;
  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
	$total_title = $order_totals[$i]['title'];
	if ($order_totals[$i]['code']!='ot_discount' && strpos($order_totals[$i]['code'], 'tax')===false) list($total_title) = explode('(', $order_totals[$i]['title']);
//	if (preg_match('/^([^\(]+)\(.*$/i', $total_title, $regs)) $total_title = $regs[1];
	$total_title = trim($total_title);
	tep_db_query("insert into " . TABLE_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . (int)$insert_id . "', '" . tep_db_input($total_title) . "', '" . tep_db_input($order_totals[$i]['text']) . "', '" . str_replace(',', '.', tep_db_input($order_totals[$i]['value'])) . "', '" . tep_db_input($order_totals[$i]['code']) . "', '" . tep_db_input($order_totals[$i]['sort_order']) . "')");
	if ($order_totals[$i]['code']=='ot_subtotal') $order_products_sum = $order_totals[$i]['value'];
	if ($order_totals[$i]['code']=='ot_total') tep_db_query("update " . TABLE_ORDERS . " set orders_total = '" . tep_db_input($order_totals[$i]['value']) . "' where orders_id = '" . (int)$insert_id . "'");
  }

  tep_order_log($insert_id, 'Totals calculated');
  
  if (isset($_COOKIE[str_replace('.', '_', STORE_NAME) . '_partner'])) $partner_login = $_COOKIE[str_replace('.', '_', STORE_NAME) . '_partner'];
  elseif (isset($_COOKIE[substr(STORE_NAME, 0, strpos(STORE_NAME, '.')) . '_partner'])) $partner_login = $_COOKIE[substr(STORE_NAME, 0, strpos(STORE_NAME, '.')) . '_partner'];
  else $partner_login = '';
  if (tep_not_null($partner_login)) {
	$partner_info_query = tep_db_query("select partners_id, partners_comission from " . TABLE_PARTNERS . " where partners_id = '" . (int)$partner_login . "' and partners_status = '1'");
	if (tep_db_num_rows($partner_info_query) > 0) {
	  $partner_info = tep_db_fetch_array($partner_info_query);
	  tep_db_query("update " . TABLE_ORDERS . " set partners_id = '" . (int)$partner_info['partners_id'] . "', partners_comission = '" . tep_db_input($partner_info['partners_comission']) . "' where orders_id = '" . (int)$insert_id . "'");
	}
  }

  tep_order_log($insert_id, 'Partners updated');
    
  $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
  $sql_data_array = array('orders_id' => $insert_id, 
                          'orders_status_id' => $order->info['order_status'], 
                          'date_added' => 'now()', 
                          'customer_notified' => $customer_notification,
                          'comments' => $order->info['comments']);
  tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$insert_id . "', '" . (int)$order->info['order_status'] . "', now(), '" . tep_db_input($customer_notification) . "', '" . tep_db_input($order->info['comments']) . "')");

  tep_order_log($insert_id, 'Orders status history created');
  
// initialized for the email confirmation
  $products_ordered = '';
  $subtotal = 0;
  $total_tax = 0;

  tep_order_log($insert_id, 'Orders product count ' . sizeof($order->products));
  
  $order_products = array();
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    if (STOCK_LIMITED == 'true') {
	  $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$order->products[$i]['id'] . "'");
      if (tep_db_num_rows($stock_query) > 0) {
        $stock_values = tep_db_fetch_array($stock_query);
        $stock_left = $stock_values['products_quantity'];
        tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . (int)$order->products[$i]['id'] . "'");
        if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
          tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . (int)$order->products[$i]['id'] . "'");
        }
      }
    }

	tep_order_log($insert_id, 'Order product #' . $i);
    
// Update sales
    tep_db_query("delete from " . TABLE_SPECIALS . " where products_id = '" . (int)$order->products[$i]['id'] . "' and specials_auto_delete = '1'");

// Update products_ordered (for bestsellers list)
    tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . (int)$order->products[$i]['id'] . "'");

    tep_order_log($insert_id, 'Updated products ordered');
    
	if ($order->products[$i]['periodicity'] > 0) {
	  $periodicity_array = array();
	  $periodicity_array['3'] = TEXT_SUBSCRIBE_TO_3_MONTHES;
	  $periodicity_array[$order->products[$i]['periodicity']/2] = TEXT_SUBSCRIBE_TO_HALF_A_YEAR;
	  $periodicity_array[$order->products[$i]['periodicity']] = TEXT_SUBSCRIBE_TO_YEAR;

	  $order->products[$i]['name'] .= ' (' . TEXT_SUBSCRIBE_TO . ' ' . $periodicity_array[$order->products[$i]['qty']] . ')';
	}

    $sql_data_array = array('orders_id' => $insert_id, 
							'products_id' => $order->products[$i]['id'], 
							'products_model' => $order->products[$i]['model'], 
							'products_code' => $order->products[$i]['code'], 
							'products_weight' => $order->products[$i]['weight'], 
							'manufacturers_name' => $order->products[$i]['manufacturer'], 
							'products_year' => $order->products[$i]['year'], 
							'products_types_id' => $order->products[$i]['type'], 
							'products_name' => $order->products[$i]['name'], 
							'products_price' => str_replace(',', '.', $order->products[$i]['price']), 
							'final_price' => $order->products[$i]['final_price'], 
							'products_tax' => $order->products[$i]['tax'], 
							'products_warranty' => $order->products[$i]['warranty'], 
							'products_quantity' => $order->products[$i]['qty']);
    tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
    $order_products_id = tep_db_insert_id();

    tep_order_log($insert_id, 'Inserted order product (order_product_id=' . $order_products_id . ')');
    
	$order_products[] = $order->products[$i]['id'];

	if (tep_not_null($order->products[$i]['filename'])) {
	  $download_filenames = explode("\n", trim($order->products[$i]['filename']));
	  reset($download_filenames);
	  while (list(, $download_filename) = each($download_filenames)) {
		if (tep_not_null(trim($download_filename))) {
		  $sql_data_array = array('orders_id' => $insert_id, 
								  'orders_products_id' => $order_products_id, 
								  'orders_products_filename' => trim($download_filename), 
								  'download_maxdays' => DOWNLOAD_MAX_DAYS, 
								  'download_count' => DOWNLOAD_MAX_COUNT);
		  tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
		}
	  }
	}

    $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
    $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
    $total_cost += $total_products_price;

	$additional_string = '';
	if (tep_not_null($order->products[$i]['model']) && $order->products[$i]['type']==1) $additional_string .= EMAIL_TEXT_MODEL . ' ' . $order->products[$i]['model'];
	if (tep_not_null($order->products[$i]['manufacturer'])) $additional_string .= (tep_not_null($additional_string) ? ', ' : '') . EMAIL_TEXT_MANUFACTURER . ' ' . $order->products[$i]['manufacturer'];
	if (tep_not_null($additional_string)) $additional_string = ' (' . $additional_string . ')';
    $products_ordered .= $order->products[$i]['qty'] . ' x <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id'], 'NONSSL', false) . '" target="_blank">' . $order->products[$i]['name'] . '</a>' . $additional_string . ' = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty'], true, $order->info['currency']) . "\n";
  }

  tep_order_log($insert_id, 'Order products finished');
  
  if (is_object($navigation)) {
	$navigation_path_array = array_reverse($navigation->path);
	reset($navigation_path_array);
	while (list($i, $navigation_path_row) = each($navigation_path_array)) {
	  $order_products_id = $navigation_path_row['real_get']['products_id'];
	  if (basename($navigation_path_row['real_page'])==FILENAME_PRODUCT_INFO && tep_not_null($order_products_id) && !in_array($order_products_id, $order_products)) {
  		tep_order_log($insert_id, 'About to insert into products_viewed');
	  	tep_db_query("insert into " . TABLE_ORDERS_PRODUCTS_VIEWED . " (orders_id, products_id) values ('" . (int)$insert_id . "', '" . (int)$order_products_id . "')");
  		tep_order_log($insert_id, 'After inserted into products_viewed');
	  }
	}
  }

  tep_order_log($insert_id, 'Insert into products_viewed finished');
  
// lets start with the email confirmation
  $email_order = ((defined('EMAIL_TEXT_WELCOME') && tep_not_null(EMAIL_TEXT_WELCOME)) ? sprintf(EMAIL_TEXT_WELCOME, STORE_OWNER) : STORE_NAME) . "\n" . 
                 EMAIL_SEPARATOR . "\n" . 
                 EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                 EMAIL_TEXT_INVOICE_URL . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . '" target="_blank">' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . '</a>' . "\n" .
                 EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long(date('Y-m-d')) . "\n";
  if ($order->info['comments']) {
    $email_order .= "\n" . EMAIL_TEXT_COMMENTS . "\n" . 
					EMAIL_SEPARATOR . "\n" . 
					tep_db_output($order->info['comments']) . "\n";
  }
  $email_order .= "\n" . EMAIL_TEXT_PRODUCTS . "\n" . 
                  EMAIL_SEPARATOR . "\n" . 
                  $products_ordered . 
                  EMAIL_SEPARATOR . "\n";

  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
	$order_totals_title = trim(strip_tags($order_totals[$i]['title']));
	if (substr($order_totals_title, -1)!=':') $order_totals_title .= ':';
    $email_order .= $order_totals_title . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
  }

  if ($order->content_type != 'virtual') {
    $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . 
                    EMAIL_SEPARATOR . "\n" .
                    tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
	if (ALLOW_SHOW_AVAILABLE_IN=='true' && $order->content_type!='virtual') {
	  $transfer_to_delivery_date = tep_calculate_date_available($order->info['delivery_transfer']);
//	  $delivery_to_city_date = date('Y-m-d', strtotime($transfer_to_delivery_date) + $order->info['city_delivery_days']*60*60*24);
	  $delivery_to_city_date = tep_calculate_date_available($order->info['delivery_transfer'] + $order->info['city_delivery_days']);
	  $email_order .= "\n" . sprintf(MAX_AVAILABLE_IN, tep_date_long($transfer_to_delivery_date));
	  if (ALLOW_SHOW_RECEIVE_IN=='true' && isset($order->info['city_delivery_days'])) $email_order .= ' ' . sprintf(MAX_RECEIVE_IN, tep_date_long($delivery_to_city_date));
	  $email_order .= "\n";
	}
  }

  if ($billto != false) {
#	$email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
#					EMAIL_SEPARATOR . "\n" .
#					tep_address_label($customer_id, $billto, 0, '', "\n") . "\n";
  }

  if (is_object($$payment)) {
    $email_order .= "\n" . EMAIL_TEXT_PAYMENT_METHOD . "\n" . 
                    EMAIL_SEPARATOR . "\n";
    $payment_class = $$payment;
    $email_order .= $payment_class->title . "\n\n";
    if ($payment_class->email_footer) {
	  $payment_class->email_footer = str_replace('[order_id]', $insert_id, $payment_class->email_footer);
//	  $email_order .= strip_tags($payment_class->email_footer) . "\n\n";
	  $email_order .= trim($payment_class->email_footer) . "\n\n";
    }
  }

  $email_text_footer = '';

  if ($order->content_type == 'virtual' || $order->content_type == 'mixed') {
	$email_text_footer .= sprintf(EMAIL_TEXT_DOWNLOAD_INFO, tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false)) . "\n\n";
  }

/*
  if (defined('EMAIL_TEXT_FOOTER')) {
	if (tep_not_null(EMAIL_TEXT_FOOTER))
	  $shipping_days_query = tep_db_query("select shops_shipping_days from " . TABLE_SHOPS . " where shops_id = '" . (int)SHOP_ID . "'");
	  $shipping_days = tep_db_fetch_array($shipping_days_query);
	  $week_from = round($shipping_days['shops_shipping_days']/7);
	  $week_to = $week_from * 2;
	  if ($week_from > 0) {
		$email_text_footer .= trim(sprintf(EMAIL_TEXT_FOOTER_TEXT, $week_from, $week_to));
	  }
	} else {
	  $email_text_footer .= trim(EMAIL_TEXT_FOOTER_TEXT);
	}
  }

  if (tep_not_null($email_text_footer)) {
	if (defined('EMAIL_TEXT_FOOTER') && tep_not_null(EMAIL_TEXT_FOOTER)) {
	  $email_order .= "\n" . EMAIL_TEXT_FOOTER;
	}
	$email_order .= "\n" . EMAIL_SEPARATOR . "\n" .
					$email_text_footer . "\n\n";
  }
*/

  if (defined('EMAIL_TEXT_FOOTER') && tep_not_null(EMAIL_TEXT_FOOTER)) {
	$shipping_days_query = tep_db_query("select shops_shipping_days from " . TABLE_SHOPS . " where shops_id = '" . (int)SHOP_ID . "'");
	$shipping_days = tep_db_fetch_array($shipping_days_query);
	$week_from = round($shipping_days['shops_shipping_days']/7);
	$week_to = $week_from * 2;
	if ($week_from > 0) {
	  $email_text_footer .= trim(sprintf(EMAIL_TEXT_FOOTER_TEXT, $week_from, $week_to));
	}
  }

  if (tep_not_null($email_text_footer)) {
	$email_order .= "\n" . EMAIL_TEXT_FOOTER . "\n" . 
					EMAIL_SEPARATOR . "\n" .
					$email_text_footer . "\n\n";
  }

  tep_order_log($insert_id, 'Sending email about order');
  
  $email_subject = STORE_NAME . ' - ' . sprintf(EMAIL_TEXT_SUBJECT, $insert_id);
  if (tep_not_null($order->customer['email_address'])) tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_order_log($insert_id, 'Email is sent');
  
// send emails to other people
  // [2013-02-27] Evgeniy Spashko: Temporary commented because it takes too
  // much time to send an email currently
/*  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
    tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
  }*/
  if (HTTP_SERVER === 'http://www.knizhnik.eu')
  {
	tep_mail('', 'da@knizhnik.de', $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	tep_mail('', 'da@anzupow.de', $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
  }

  if (HTTP_SERVER === 'http://www.insellbooks.com')
  {
  	tep_mail('', 'orders@insellbooks.com', $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
  }
  
  
  tep_order_log($insert_id, 'Email to others is sent');
  
// load the after_process function from the payment modules
  $payment_modules->after_process();

  $order = new order($insert_id);

  tep_order_log($insert_id, 'Order readed from database');
  
  $order_total_sum = 0;
  $order_shipping_sum = 0;
  $order_shipping_method = '';
  $order_discount_sum = 0;
  reset($order->totals);
  while (list(, $order_total) = each($order->totals)) {
	if ($order_total['class']=='ot_total') {
	  $order_total_sum = $order_total['value'];
	} elseif ($order_total['class']=='ot_shipping') {
	  $order_shipping_sum = $order_total['value'];
	  $order_shipping_method = $order_total['title'];
	} elseif ($order_total['class']=='ot_discount') {
	  $order_discount_sum = $order_total['value'];
	}
  }

  $self_delivery_address = '';
  $self_delivery_id = '';
  list($order_shipping_title, $order_shipping_value) = explode('_', $shipping['id']);
  $self_delivery_id = (int)$order_shipping_value;
  if ($order_shipping_title=='slf') {
	$order_shipping_id = 1;
	if ($self_delivery_id > 0) {
	  $self_delivery_query = tep_db_query("select * from " . TABLE_SELF_DELIVERY . " where self_delivery_id = '" . (int)$self_delivery_id . "'");
	  $self_delivery = tep_db_fetch_array($self_delivery_query);
	  if (!is_array($self_delivery)) $self_delivery = array();
	  reset($self_delivery);
	  while (list($k, $v) = each($self_delivery)) {
		$k = str_replace('entry_', '', $k);
		$self_delivery[$k] = $v;
	  }
	  $self_delivery_address = tep_address_format($order->delivery['format_id'], $self_delivery, 1, '', ', ');
	  tep_db_query("update " . TABLE_ORDERS . " set delivery_self_address = '" . tep_db_input($self_delivery_address) . "' where orders_id = '" . (int)$insert_id . "'");
	}
  }

  tep_order_log($insert_id, 'Order self_delivery updated');
  
  $is_europe = '';
  if (SHOP_ID==9) {
	$delivery_country_info_query = tep_db_query("select entry_country_id from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$sendto . "'");
	$delivery_country_info = tep_db_fetch_array($delivery_country_info_query);
	$delivery_country_id = $delivery_country_info['entry_country_id'];
	$europe_country_check_query = tep_db_query("select count(*) as total from setbook_net." . TABLE_COUNTRIES . " where countries_id = '" . (int)$delivery_country_id . "'");
	$europe_country_check = tep_db_fetch_array($europe_country_check_query);
	if ($europe_country_check['total'] > 0) $is_europe = 'e';
  }

  tep_order_log($insert_id, 'Creating csv file for order');
  
  $delimiter = ',';
  $date_purchased = preg_replace('/\s+/', ' ', preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '$3.$2.$1 $4:$5:$6', $order->info['date_purchased']));
  $order_file = UPLOAD_DIR. 'temp_orders/' . SHOP_PREFIX . $insert_id . '.csv';
  $fp = fopen($order_file, 'w');
  $common_data = array($insert_id, #номер заказа без префиксов
					   $date_purchased, #Дата заказа в формате 02.04.2010
					   SHOP_ID, #ID сайта
					   ($is_dummy_account==true ? '0' : $customer_id), #ID пользователя без префикса
					   $order->customer['email_address'], #EMAIL
					   $order->delivery['name'], #имя
					   '', #фамилия
					   '', #отчество
					   $payment, #Тип оплаты
					   $order_shipping_title, #Тип доставки
					   str_replace(',', '.', $order_shipping_sum), #Стоимость доставки
					   $order->info['currency'], #Код валюты заказа
					   str_replace(',', '.', $order->info['currency_value']), #Курс валюты заказа
					   tep_html_entity_decode($order->delivery['state']), #Регион, строка
					   tep_html_entity_decode($order->delivery['suburb']), #Район, строка
					   tep_html_entity_decode($order->delivery['city']), #Город, строка
					   $order->delivery['postcode'], #Почтовый индекс, строка
					   tep_html_entity_decode($order->delivery['street_address']), #Почтовый адрес, строка
					   $order->delivery['telephone'], #телефон, строка
					   tep_html_entity_decode($order->info['comments']), #Коментарий клиента, строка
					   $self_delivery_id, #ID пункта самовывоза
					   tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false), #Ссылка на страницу
					   str_replace(',', '.', $order_total_sum), #Стоимость заказа в рублях
					   str_replace(',', '.', abs($order_discount_sum)), #Скидка в рублях
					   (tep_not_null($order->customer['company_full']) ? tep_html_entity_decode($order->customer['company_full']) : tep_html_entity_decode($order->customer['company'])), #Полное наименование ЮР лица
					   $order->customer['company_inn'], #Инн
					   $order->customer['company_kpp'], #Кпп
					   $order->customer['company_address_corporate'], #ЮрАдрес
					   $is_europe, #проверка того, что доставка по Европе
					   $order_delivery_transfer,
					   $order->info['code'], #код заказа
					   tep_html_entity_decode($order->delivery['country']), #Страна доставки, наименование
					   tep_html_entity_decode($order_delivery_country_code), #Страна доставки, код
					   $order->customer['company_corporate'], #Корпоративный клиент
					   );
  fputcsvsafe($fp, $common_data, $delimiter);

  tep_order_log($insert_id, 'Order is saved to CSV file');
  
  tep_db_query("update " . TABLE_ORDERS . " set " . (empty($order->info['code']) ? "orders_code = orders_id, " : "") . "payment_method_class = '" . tep_db_input($payment) . "', delivery_method = '" . tep_db_input($order_shipping_method) . "', delivery_method_class = '" . tep_db_input($order_shipping_title) . "', delivery_self_address = '" . tep_db_input($self_delivery_address) . "', delivery_self_address_id = '" . (int)$self_delivery_id . "' where orders_id = '" . (int)$insert_id . "'");
  reset($order->products);
  while (list(, $product) = each($order->products)) {
	$product_code = (int)str_replace('bbk', '', $product['code']);
	$common_data = array($product['type'], #Тип товара
						 $product_code, #ID товара
						 $product['qty'], #Количество
						 str_replace(',', '.', $product['final_price']), #Цена в рублях
						 $product['id'],
						 '',
						 $product['name'],
						 tep_get_products_info($product['id']),
						 $product['code'],
						 $product['warranty'],
						 );
	fputcsvsafe($fp, $common_data, $delimiter);
  }
  fclose($fp);

  tep_order_log($insert_id, 'Order products are saved and file is closed');
  
  copy($order_file, str_replace('temp_orders/', 'orders1/', $order_file));
  unlink($order_file);

  tep_order_log($insert_id, 'Creating archive order');
  
  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS . " select * from " . TABLE_ORDERS . " where orders_id = '" . (int)$insert_id . "'");
  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_TOTAL . " select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$insert_id . "'");
  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_STATUS_HISTORY . " select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$insert_id . "'");
  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_PRODUCTS . " select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$insert_id . "'");
  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_PRODUCTS_DOWNLOAD . " select * from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int)$insert_id . "'");

  tep_order_log($insert_id, 'Archive order is created');
  
  $cart->reset(true);

// unregister session variables used during checkout
  tep_session_unregister('sendto');
  tep_session_unregister('billto');
  tep_session_unregister('shipping');
  tep_session_unregister('payment');
  tep_session_unregister('comments');

  tep_order_log($insert_id, '*** Order is finished');
  
  tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
 
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>