<?php
  class paypal_standard {
    var $code, $title, $description, $enabled;

// class constructor
    function paypal_standard() {
      global $order;

      $this->signature = 'paypal|paypal_standard|1.0|2.2';

      $this->code = 'paypal_standard';
      $this->title = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
        $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_STANDARD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_STANDARD_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      global $cart_PayPal_Standard_ID;

      if (tep_session_is_registered('cart_PayPal_Standard_ID')) {
        $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
//		  tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
//		  tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
//		  tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
//		  tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
//		  tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

//		  @unlink(UPLOAD_DIR . 'orders/' . SHOP_PREFIX . 'im' . $order_id . '.csv');

		  tep_session_unregister('cart_PayPal_Standard_ID');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title,
				   'description' => $this->description);
    }

    function pre_confirmation_check() {
      global $cartID, $cart;

      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!tep_session_is_registered('cartID')) {
        tep_session_register('cartID');
      }
    }

    function confirmation() {
      global $cartID, $cart_PayPal_Standard_ID, $customer_id, $languages_id, $order, $order_total_modules, $currencies;

      if (tep_session_is_registered('cartID')) {
        $insert_order = false;

        if (tep_session_is_registered('cart_PayPal_Standard_ID')) {
          $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

          $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
          $curr = tep_db_fetch_array($curr_check);

          if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_PayPal_Standard_ID, 0, strlen($cartID))) ) {
            $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

            if (tep_db_num_rows($check_query) < 1) {
              tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

			  @unlink(UPLOAD_DIR . 'orders/' . SHOP_PREFIX . 'im' . $order_id . '.csv');
            }

            $insert_order = true;
          }
        } else {
          $insert_order = true;
        }

        if ($insert_order == true) {
          $order_totals = array();
          if (is_array($order_total_modules->modules)) {
            reset($order_total_modules->modules);
            while (list(, $value) = each($order_total_modules->modules)) {
              $class = substr($value, 0, strrpos($value, '.'));
              if ($GLOBALS[$class]->enabled) {
                for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                  if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                    $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                            'title' => $GLOBALS[$class]->output[$i]['title'],
                                            'text' => $GLOBALS[$class]->output[$i]['text'],
                                            'value' => $GLOBALS[$class]->output[$i]['value'],
                                            'sort_order' => $GLOBALS[$class]->sort_order);
                  }
                }
              }
            }
          }

          $sql_data_array = array('customers_id' => $customer_id,
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
                                  'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                  'delivery_company' => $order->delivery['company'],
                                  'delivery_street_address' => $order->delivery['street_address'],
                                  'delivery_suburb' => $order->delivery['suburb'],
                                  'delivery_city' => $order->delivery['city'],
                                  'delivery_postcode' => $order->delivery['postcode'],
                                  'delivery_state' => $order->delivery['state'],
                                  'delivery_country' => $order->delivery['country']['title'],
                                  'delivery_address_format_id' => $order->delivery['format_id'],
                                  'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                  'billing_company' => $order->billing['company'],
                                  'billing_street_address' => $order->billing['street_address'],
                                  'billing_suburb' => $order->billing['suburb'],
                                  'billing_city' => $order->billing['city'],
                                  'billing_postcode' => $order->billing['postcode'],
                                  'billing_state' => $order->billing['state'],
                                  'billing_country' => $order->billing['country']['title'],
                                  'billing_address_format_id' => $order->billing['format_id'],
                                  'payment_method' => $order->info['payment_method'],
                                  'cc_type' => $order->info['cc_type'],
                                  'cc_owner' => $order->info['cc_owner'],
                                  'cc_number' => $order->info['cc_number'],
                                  'cc_expires' => $order->info['cc_expires'],
                                  'date_purchased' => 'now()',
                                  'orders_status' => $order->info['order_status'],
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);

          tep_db_perform(TABLE_ORDERS, $sql_data_array);

          $insert_id = tep_db_insert_id();

		  $order_products_sum = 0;
		  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
			$total_title = $order_totals[$i]['title'];
			list($total_title) = explode('(', $order_totals[$i]['title']);
		//	if (preg_match('/^([^\(]+)\(.*$/i', $total_title, $regs)) $total_title = $regs[1];
			$total_title = trim($total_title);
			tep_db_query("insert into " . TABLE_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . (int)$insert_id . "', '" . tep_db_input($total_title) . "', '" . tep_db_input($order_totals[$i]['text']) . "', '" . tep_db_input($order_totals[$i]['value']) . "', '" . tep_db_input($order_totals[$i]['code']) . "', '" . tep_db_input($order_totals[$i]['sort_order']) . "')");
			if ($order_totals[$i]['code']=='ot_subtotal') $order_products_sum = $order_totals[$i]['value'];
		  }

		  if (isset($_COOKIE[STORE_NAME . '_partner'])) {
			$partner_info_query = tep_db_query("select partners_id, partners_comission from " . TABLE_PARTNERS . " where partners_id = '" . (int)$_COOKIE[STORE_NAME . '_partner'] . "' and partners_status = '1'");
			if (tep_db_num_rows($partner_info_query) > 0) {
			  $partner_info = tep_db_fetch_array($partner_info_query);
			  tep_db_query("update " . TABLE_ORDERS . " set partners_id = '" . (int)$partner_info['partners_id'] . "', partners_comission = '" . tep_db_input($partner_info['partners_comission']) . "' where orders_id = '" . (int)$insert_id . "'");
			}
		  }

// initialized for the email confirmation
		  $products_ordered = '';
		  $subtotal = 0;
		  $total_tax = 0;

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

		    $sql_data_array = array('orders_id' => $insert_id, 
		                            'products_id' => $order->products[$i]['id'], 
		                            'products_model' => $order->products[$i]['model'], 
		                            'products_code' => $order->products[$i]['code'], 
		                            'products_weight' => $order->products[$i]['weight'], 
		                            'products_name' => $order->products[$i]['name'], 
		                            'products_price' => $order->products[$i]['price'], 
		                            'final_price' => $order->products[$i]['final_price'], 
		                            'products_tax' => $order->products[$i]['tax'], 
		                            'products_quantity' => $order->products[$i]['qty']);
		    tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
		    $order_products_id = tep_db_insert_id();

		    $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
		    $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
		    $total_cost += $total_products_price;

		    $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . "\n";
		  }

          $cart_PayPal_Standard_ID = $cartID . '-' . $insert_id;
          tep_session_register('cart_PayPal_Standard_ID');


		  $order = new order($insert_id);

		  $order_total_sum = 0;
		  $order_shipping_sum = 0;
		  reset($order->totals);
		  while (list(, $order_total) = each($order->totals)) {
			if ($order_total['class']=='ot_total') {
			  $order_total_sum = $order_total['value'];
			} elseif ($order_total['class']=='ot_shipping') {
			  $order_shipping_sum = $order_total['value'];
			}
		  }

		  $order_shipping_title = strtolower($order->info['shipping_method']);
		  if (mb_strpos($order_shipping_title, 'самовывоз', 0, 'CP1251')!==false) {
			$order_shipping_id = 1;
		  } elseif (mb_strpos($order_shipping_title, 'подмосковье', 0, 'CP1251')!==false) {
			if ($order_shipping_sum==0) $order_shipping_id = 5;
			else $order_shipping_id = 4;
		  } elseif (mb_strpos($order_shipping_title, 'курьером', 0, 'CP1251')!==false) {
			if ($order_shipping_sum==0) $order_shipping_id = 3;
			else $order_shipping_id = 2;
		  } elseif (mb_strpos($order_shipping_title, 'почт', 0, 'CP1251')!==false) {
			if (DOMAIN_ZONE=='ru') $order_shipping_id = 6;
			elseif (DOMAIN_ZONE=='ua') $order_shipping_id = 8;
			elseif (DOMAIN_ZONE=='by') $order_shipping_id = 9;
		  } elseif (strpos($order_shipping_title, 'postal')!==false) {
			$order_shipping_id = 7;
		  } elseif (strpos($order_shipping_title, 'deutsche')!==false) {
			$order_shipping_id = 10;
		  } else {
			$order_shipping_id = 0;
		  }

		  $order_payment_id = 0;
		  $order_payment_title = strtolower($order->info['payment_method']);
		  if (mb_strpos($order_payment_title, 'налич', 0, 'CP1251')!==false) $order_payment_id = 1;
		  elseif (strpos($order_payment_title, 'order')!==false) $order_payment_id = 6;
		  elseif (strpos($order_payment_title, 'check')!==false) $order_payment_id = 7;
		  elseif (strpos($order_payment_title, 'pal')!==false) $order_payment_id = 8;
		  elseif (mb_strpos($order_payment_title, 'налож', 0, 'CP1251')!==false) $order_payment_id = 2;
		  elseif (mb_strpos($order_payment_title, 'банк', 0, 'CP1251')!==false) $order_payment_id = 3;
		  elseif (mb_strpos($order_payment_title, 'безнал', 0, 'CP1251')!==false) $order_payment_id = 4;
		  elseif (mb_strpos($order_payment_title, 'почтовым', 0, 'CP1251')!==false) $order_payment_id = 5;

		  $date_purchased = preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '$3-$2-$1 $4:$5:$6', $order->info['date_purchased']);
		  $order_file = UPLOAD_DIR. 'orders/' . SHOP_PREFIX . 'im' . $insert_id . '.csv';
		  $fp = fopen($order_file, 'w');
		  reset($order->products);
		  $common_data = array('ORDER_ID', 'ORDER_DATE_INSERT', 'USER_ID', 'PAY_SYSTEM_ID', 'DELIVERY_ID', 'PRICE_DELIVERY', 'ORDER_PRICE', 'REGION', 'RAYON', 'CITY', 'INDEX', 'ADDRESS', 'PHONE', 'COMMENTS', 'PRODUCT_ID', 'PRODUCT_QUANTITY', 'PRODUCT_PRICE');
		  fputcsvsafe($fp, $common_data, ';');
		  while (list(, $product) = each($order->products)) {
			$common_data = array();
			$common_data[] = $server_prefix . 'im' . $insert_id;
			$common_data[] = $date_purchased;
			$common_data[] = $order->customer['id'];
			$common_data[] = $order_payment_id; // тип оплаты
			$common_data[] = $order_shipping_id; // тип доставки
			$common_data[] = $order_shipping_sum; // стоимость доставки
			$common_data[] = $order_total_sum; // сумма заказа
			$common_data[] = $order->delivery['state'];
			$common_data[] = $order->delivery['suburb'];
			$common_data[] = $order->delivery['city'];
			$common_data[] = $order->delivery['postcode'];
			$common_data[] = $order->delivery['street_address'];
			$common_data[] = $order->customer['telephone'];
			$common_data[] = $order->info['comments'];
			$common_data[] = $product['code'];
			$common_data[] = $product['qty'];
			$common_data[] = $product['final_price'];
			fputcsvsafe($fp, $common_data, ';');
		  }
		  fclose($fp);
        }
      }

      return false;
    }

    function process_button() {
      global $customer_id, $order, $sendto, $currency, $cart_PayPal_Standard_ID, $shipping;

      $process_button_string = '';
      $parameters = array('cmd' => '_xclick',
                          'item_name' => STORE_NAME,
                          'shipping' => $this->format_raw($order->info['shipping_cost']),
                          'tax' => $this->format_raw($order->info['tax']),
                          'business' => MODULE_PAYMENT_PAYPAL_STANDARD_ID,
                          'amount' => $this->format_raw($order->info['total'] - $order->info['shipping_cost'] - $order->info['tax']),
                          'currency_code' => $currency,
                          'invoice' => substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1),
                          'custom' => $customer_id,
                          'no_note' => '1',
                          'notify_url' => tep_href_link('ext/modules/payment/paypal/standard_ipn.php', '', 'SSL', false, false),
                          'return' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
                          'cancel_return' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                          'bn' => 'osCommerce22_Default_ST',
                          'paymentaction' => ((MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? 'sale' : 'authorization'));

      if (is_numeric($sendto) && ($sendto > 0)) {
        $parameters['address_override'] = '1';
        $parameters['first_name'] = $order->delivery['firstname'];
        $parameters['last_name'] = $order->delivery['lastname'];
        $parameters['address1'] = $order->delivery['street_address'];
        $parameters['city'] = $order->delivery['city'];
        $parameters['state'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $parameters['zip'] = $order->delivery['postcode'];
        $parameters['country'] = $order->delivery['country']['iso_code_2'];
      } else {
        $parameters['no_shipping'] = '1';
        $parameters['first_name'] = $order->billing['firstname'];
        $parameters['last_name'] = $order->billing['lastname'];
        $parameters['address1'] = $order->billing['street_address'];
        $parameters['city'] = $order->billing['city'];
        $parameters['state'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']);
        $parameters['zip'] = $order->billing['postcode'];
        $parameters['country'] = $order->billing['country']['iso_code_2'];
      }

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE)) {
        $parameters['page_style'] = MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE;
      }

      if (MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS == 'True') {
        $parameters['cert_id'] = MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID;

        $random_string = rand(100000, 999999) . '-' . $customer_id . '-';

        $data = '';
        reset($parameters);
        while (list($key, $value) = each($parameters)) {
          $data .= $key . '=' . $value . "\n";
        }

        $fp = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);

        unset($data);

        if (function_exists('openssl_pkcs7_sign') && function_exists('openssl_pkcs7_encrypt')) {
          openssl_pkcs7_sign(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY), file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

// remove headers from the signature
          $signed = file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          $signed = explode("\n\n", $signed);
          $signed = base64_decode($signed[1]);

          $fp = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', 'w');
          fwrite($fp, $signed);
          fclose($fp);

          unset($signed);

          openssl_pkcs7_encrypt(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

// remove headers from the encrypted result
          $data = file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          $data = explode("\n\n", $data);
          $data = '-----BEGIN PKCS7-----' . "\n" . $data[1] . "\n" . '-----END PKCS7-----';

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        } else {
          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -sign -in ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt -signer ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY . ' -inkey ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY . ' -outform der -nodetach -binary > ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -encrypt -des3 -binary -outform pem ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY . ' < ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt > ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

          $fh = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', 'rb');
          $data = fread($fh, filesize(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt'));
          fclose($fh);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        }

        $process_button_string = tep_draw_hidden_field('cmd', '_s-xclick') .
                                 tep_draw_hidden_field('encrypted', $data);

        unset($data);
      } else {
        reset($parameters);
        while (list($key, $value) = each($parameters)) {
          $process_button_string .= tep_draw_hidden_field($key, $value);
        }
      }

      return $process_button_string;
    }

    function before_process() {
      global $customer_id, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_PayPal_Standard_ID;

      $insert_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$insert_id . "'");
      if (tep_db_num_rows($check_query)) {
        $check = tep_db_fetch_array($check_query);

        if ($check['orders_status'] == MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => '');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }

      tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';
      $subtotal = 0;
      $total_tax = 0;

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Update products_ordered (for bestsellers list)
        tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

//------insert customer choosen option eof ----
        $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
        $total_cost += $total_products_price;

		$products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= tep_db_output($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        tep_address_label($customer_id, $sendto, false) . "\n";
      }

	  if ($billto != false) {
//		$email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
//						EMAIL_SEPARATOR . "\n" .
//						tep_address_label($customer_id, $billto, false) . "\n\n";
	  }

      if (is_object($$payment)) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = $$payment;
        $email_order .= $payment_class->title . "\n\n";
        if ($payment_class->email_footer) {
          $email_order .= strip_tags($payment_class->email_footer) . "\n\n";
        }
      }

	  $email_subject = STORE_NAME . ' - ' . sprintf(EMAIL_TEXT_SUBJECT, $insert_id);
      tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], $email_subject, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

// load the after_process function from the payment modules
      $this->after_process();

      $cart->reset(true);

	  $order = new order($insert_id);

	  $order_total_sum = 0;
	  $order_shipping_sum = 0;
	  reset($order->totals);
	  while (list(, $order_total) = each($order->totals)) {
		if ($order_total['class']=='ot_total') {
		  $order_total_sum = $order_total['value'];
		} elseif ($order_total['class']=='ot_shipping') {
		  $order_shipping_sum = $order_total['value'];
		}
	  }

	  $order_shipping_title = strtolower($order->info['shipping_method']);
	  if (mb_strpos($order_shipping_title, 'самовывоз', 0, 'CP1251')!==false) {
		$order_shipping_id = 1;
	  } elseif (mb_strpos($order_shipping_title, 'подмосковье', 0, 'CP1251')!==false) {
		if ($order_shipping_sum==0) $order_shipping_id = 5;
		else $order_shipping_id = 4;
	  } elseif (mb_strpos($order_shipping_title, 'курьером', 0, 'CP1251')!==false) {
		if ($order_shipping_sum==0) $order_shipping_id = 3;
		else $order_shipping_id = 2;
	  } elseif (mb_strpos($order_shipping_title, 'почт', 0, 'CP1251')!==false) {
		if (DOMAIN_ZONE=='ru') $order_shipping_id = 6;
		elseif (DOMAIN_ZONE=='ua') $order_shipping_id = 8;
		elseif (DOMAIN_ZONE=='by') $order_shipping_id = 9;
	  } elseif (strpos($order_shipping_title, 'postal')!==false) {
		$order_shipping_id = 7;
	  } elseif (strpos($order_shipping_title, 'deutsche')!==false) {
		$order_shipping_id = 10;
	  } else {
		$order_shipping_id = 0;
	  }

	  $order_payment_id = 0;
	  $order_payment_title = strtolower($order->info['payment_method']);
	  if (mb_strpos($order_payment_title, 'налич', 0, 'CP1251')!==false) $order_payment_id = 1;
	  elseif (strpos($order_payment_title, 'order')!==false) $order_payment_id = 6;
	  elseif (strpos($order_payment_title, 'check')!==false) $order_payment_id = 7;
	  elseif (strpos($order_payment_title, 'pal')!==false) $order_payment_id = 8;
	  elseif (mb_strpos($order_payment_title, 'налож', 0, 'CP1251')!==false) $order_payment_id = 2;
	  elseif (mb_strpos($order_payment_title, 'банк', 0, 'CP1251')!==false) $order_payment_id = 3;
	  elseif (mb_strpos($order_payment_title, 'безнал', 0, 'CP1251')!==false) $order_payment_id = 4;
	  elseif (mb_strpos($order_payment_title, 'почтовым', 0, 'CP1251')!==false) $order_payment_id = 5;

	  $date_purchased = preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '$3-$2-$1 $4:$5:$6', $order->info['date_purchased']);
	  $order_file = UPLOAD_DIR. 'orders/' . SHOP_PREFIX . 'im' . $insert_id . '.csv';
	  $fp = fopen($order_file, 'w');
	  reset($order->products);
	  $common_data = array('ORDER_ID', 'ORDER_DATE_INSERT', 'USER_ID', 'PAY_SYSTEM_ID', 'DELIVERY_ID', 'PRICE_DELIVERY', 'ORDER_PRICE', 'REGION', 'RAYON', 'CITY', 'INDEX', 'ADDRESS', 'PHONE', 'COMMENTS', 'PRODUCT_ID', 'PRODUCT_QUANTITY', 'PRODUCT_PRICE');
	  fputcsvsafe($fp, $common_data, ';');
	  while (list(, $product) = each($order->products)) {
		$common_data = array();
		$common_data[] = $server_prefix . 'im' . $insert_id;
		$common_data[] = $date_purchased;
		$common_data[] = $order->customer['id'];
		$common_data[] = $order_payment_id; // тип оплаты
		$common_data[] = $order_shipping_id; // тип доставки
		$common_data[] = $order_shipping_sum; // стоимость доставки
		$common_data[] = $order_total_sum; // сумма заказа
		$common_data[] = $order->delivery['state'];
		$common_data[] = $order->delivery['suburb'];
		$common_data[] = $order->delivery['city'];
		$common_data[] = $order->delivery['postcode'];
		$common_data[] = $order->delivery['street_address'];
		$common_data[] = $order->customer['telephone'];
		$common_data[] = $order->info['comments'];
		$common_data[] = $product['code'];
		$common_data[] = $product['qty'];
		$common_data[] = $product['final_price'];
		fputcsvsafe($fp, $common_data, ';');
	  }
	  fclose($fp);

// unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      tep_session_unregister('cart_PayPal_Standard_ID');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_STANDARD_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [PayPal Standard]' limit 1");

      if (tep_db_num_rows($check_query) < 1) {
        $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
        $status = tep_db_fetch_array($status_query);

        $status_id = $status['status_id']+1;

        $languages = tep_get_languages();

        foreach ($languages as $lang) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Preparing [PayPal Standard]')");
        }
      } else {
        $check = tep_db_fetch_array($check_query);

        $status_id = $check['orders_status_id'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('–азрешить оплату заказов через PayPal', 'MODULE_PAYMENT_PAYPAL_STANDARD_STATUS', 'False', '¬ы хотите принимать оплату за заказы посредством PayPal?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-Mail Address', 'MODULE_PAYMENT_PAYPAL_STANDARD_ID', '', 'The PayPal seller e-mail address to accept payments for', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ѕор€док вывода.', 'MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER', '0', 'ѕор€док показа. Ќаменьшие показываютс€ первыми.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('«она', 'MODULE_PAYMENT_PAYPAL_STANDARD_ZONE', '0', '≈сли выбрана зона, позвол€ть оплату через PayPal только дл€ этой зоны.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID', '" . $status_id . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set PayPal Acknowledged Order Status', 'MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Gateway Server', 'MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER', 'Live', 'Use the testing (sandbox) or live gateway server for transactions?', '6', '6', 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Page Style', 'MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE', '', 'The page style to use for the transaction procedure (defined at your PayPal Profile page)', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail Address', 'MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL', '', 'All parameters of an Invalid IPN notification will be sent to this email address if one is entered.', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Encrypted Web Payments', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS', 'False', 'Do you want to enable Encrypted Web Payments?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Your Private Key', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY', '', 'The location of your Private Key to use for signing the data. (*.pem)', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Your Public Certificate', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY', '', 'The location of your Public Certificate to use for signing the data. (*.pem)', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PayPals Public Certificate', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY', '', 'The location of the PayPal Public Certificate for encrypting the data.', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Your PayPal Public Certificate ID', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID', '', 'The Certificate ID to use from your PayPal Encrypted Payment Settings Profile.', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Working Directory', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY', '', 'The working directory to use for temporary files. (trailing slash needed)', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('OpenSSL Location', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL', '/usr/bin/openssl', 'The location of the openssl binary file.', '6', '4', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS', 'MODULE_PAYMENT_PAYPAL_STANDARD_ID', 'MODULE_PAYMENT_PAYPAL_STANDARD_ZONE', 'MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER', 'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD', 'MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE', 'MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL', 'MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY', 'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL');
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }
?>