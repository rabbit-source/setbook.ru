<?php
  class paypal_direct {
    var $code, $title, $description, $enabled, $email_footer;

// class constructor
    function paypal_direct() {
      global $order, $currencies;

      $this->signature = 'paypal|paypal_direct|1.1|2.2';

      $this->code = 'paypal_direct';
      $this->title = MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPAL_DIRECT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPAL_DIRECT_STATUS == 'True') ? true : false);
      $this->email_footer = trim(MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_EMAIL_FOOTER);

      if ((int)MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->cc_types = array('VISA' => 'Visa',
                              'MASTERCARD' => 'MasterCard',
                              'DISCOVER' => 'Discover',
                              'AMEX' => 'American Express',
                              'SWITCH' => 'Maestro',
                              'SOLO' => 'Solo');

	  $this->cc_currencies = array();
	  $shop_currency_query = tep_db_query("select shops_currency from " . TABLE_SHOPS . " where shops_id = '" . (int)SHOP_ID . "'");
	  $shop_currency = tep_db_fetch_array($shop_currency_query);
	  $shop_currencies = explode(',', $shop_currency['shops_currency']);
	  reset($shop_currencies);
	  while (list(, $shop_currency) = each($shop_currencies)) {
		if (SHOP_ID==9 || SHOP_ID==14 || SHOP_ID==16) {
		  $this->cc_currencies[$shop_currency] = trim($currencies->currencies[$shop_currency]['symbol_left'] . ' ' . $currencies->currencies[$shop_currency]['symbol_right']) . ' ' . $shop_currency;
		} else {
		  $this->cc_currencies[$shop_currency] = $currencies->currencies[$shop_currency]['title'];
		}
	  }
	  asort($this->cc_currencies);
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_DIRECT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_DIRECT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
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
	  global $customer_id, $billto, $currency;

      $selection = array('id' => $this->code,
                         'module' => $this->title,
						 'description' => $this->description);

      if (MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE == 'Payment') {
        global $order;

        $types_array = array();
		reset($this->cc_types);
        while (list($key, $value) = each($this->cc_types)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }

        $currencies_array = array();
		reset($this->cc_currencies);
        while (list($key, $value) = each($this->cc_currencies)) {
          $currencies_array[] = array('id' => $key,
									  'text' => $value);
        }

        $today = getdate();

        $months_array = array();
        for ($i=1; $i<13; $i++) {
          $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
          $year_valid_from_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $year_expires_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $year_expires_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

		$address_query = tep_db_query("select entry_street_address, entry_city, entry_postcode, entry_state, entry_zone_id, entry_country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
		$address = tep_db_fetch_array($address_query);

        $selection['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_OWNER,
                                           'field' => tep_draw_input_field('cc_owner', $order->delivery['firstname'] . ' ' . $order->delivery['lastname'])),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_TYPE,
                                           'field' => tep_draw_pull_down_menu('cc_type', $types_array)),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_NUMBER,
                                           'field' => tep_draw_input_field('cc_number_nh-dns')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_EXPIRES,
                                           'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC,
                                           'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"') . ' ' . MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC_INFO),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER,
                                           'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'size="5" maxlength="2"') . ' ' . MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER_INFO),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CURRENCY,
                                           'field' => (sizeof($currencies_array)>1 ? tep_draw_pull_down_menu('cc_currency', $currencies_array, $currency) : tep_draw_hidden_field('cc_currency', $currency) . $this->cc_currencies[$currency])),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STREET,
                                           'field' => tep_draw_textarea_field('cc_billing_street', 'soft', '40', '4', $address['entry_street_address'])),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_CITY,
                                           'field' => tep_draw_input_field('cc_billing_city', $address['entry_city'], 'size="40"')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STATE,
                                           'field' => tep_draw_input_field('cc_billing_state', tep_get_zone_code($address['entry_country_id'], $address['entry_zone_id'], $address['entry_state']), 'size="20"')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_POSTCODE,
                                           'field' => tep_draw_input_field('cc_billing_postcode', $address['entry_postcode'], 'size="20"')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_COUNTRY,
                                           'field' => $this->get_country_string()));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      if (MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS, $messageStack;

        if (empty($HTTP_POST_VARS['cc_owner']) || (strlen($HTTP_POST_VARS['cc_owner']) < CC_OWNER_MIN_LENGTH) || empty($HTTP_POST_VARS['cc_number_nh-dns']) || (strlen($HTTP_POST_VARS['cc_number_nh-dns']) < CC_NUMBER_MIN_LENGTH) || empty($HTTP_POST_VARS['cc_billing_postcode']) || empty($HTTP_POST_VARS['cc_billing_state']) || empty($HTTP_POST_VARS['cc_billing_city']) || empty($HTTP_POST_VARS['cc_billing_street'])) {
          $payment_error_return = 'cc_owner=' . urlencode($HTTP_POST_VARS['cc_owner']) . '&cc_type=' . $HTTP_POST_VARS['cc_type'] . '&cc_expires_month=' . $HTTP_POST_VARS['cc_expires_month'] . '&cc_expires_year=' . $HTTP_POST_VARS['cc_expires_year'] . '&cc_currency=' . $HTTP_POST_VARS['cc_currency'] . '&cc_billing_country=' . urlencode($HTTP_POST_VARS['cc_billing_country']) . '&cc_billing_postcode=' . urlencode($HTTP_POST_VARS['cc_billing_postcode']) . '&cc_billing_state=' . urlencode($HTTP_POST_VARS['cc_billing_state']) . '&cc_billing_city=' . urlencode($HTTP_POST_VARS['cc_billing_city']) . '&cc_billing_street=' . urlencode($HTTP_POST_VARS['cc_billing_street']) . '';

		  $messageStack->add_session('header', MODULE_PAYMENT_PAYPAL_DIRECT_ERROR_ALL_FIELDS_REQUIRED);

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
      }

      return false;
    }

    function confirmation() {
	  global $currency;

      $confirmation = array();

      if (MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS;

		$HTTP_POST_VARS['cc_number_nh-dns'] = preg_replace('/[^\d]/', '', $HTTP_POST_VARS['cc_number_nh-dns']);
        $confirmation['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_OWNER,
                                              'field' => $HTTP_POST_VARS['cc_owner']),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_TYPE,
                                              'field' => $this->cc_types[$HTTP_POST_VARS['cc_type']]),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_NUMBER,
                                              'field' => substr($HTTP_POST_VARS['cc_number_nh-dns'], 0, 4) . str_repeat('X', strlen($HTTP_POST_VARS['cc_number_nh-dns']) - 8) . substr($HTTP_POST_VARS['cc_number_nh-dns'], -4)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_EXPIRES,
                                              'field' => $HTTP_POST_VARS['cc_expires_month'] . '/' . $HTTP_POST_VARS['cc_expires_year']),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC,
                                              'field' => $HTTP_POST_VARS['cc_cvc_nh-dns']));

        if (isset($HTTP_POST_VARS['cc_issue_nh-dns']) && !empty($HTTP_POST_VARS['cc_issue_nh-dns'])) {
          $confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER,
                                            'field' => $HTTP_POST_VARS['cc_issue_nh-dns']);
        }
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CURRENCY,
                                          'field' => $this->cc_currencies[$HTTP_POST_VARS['cc_currency']]);

		$billing_country_info = tep_get_countries('', true, $HTTP_POST_VARS['cc_billing_country']);
		if (sizeof($billing_country_info)==0) {
		  $countries = file(DIR_WS_MODULES . 'payment/all_countries.csv');
		  reset($countries);
		  while (list(, $country_info) = each($countries)) {
			list($country_code, $country_name, $country_iso_code_3) = explode(';', $country_info);
			if ($country_code==$HTTP_POST_VARS['cc_billing_country']) {
			  $billing_country_info = array('countries_id' => '', 'countries_name' => $country_name, 'countries_iso_code_2' => $country_code, 'countries_iso_code_3' => $country_iso_code_3);
			  break;
			}
		  }
		}

		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_SHORT);
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STREET,
                                          'field' => $HTTP_POST_VARS['cc_billing_street']);
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_CITY,
                                          'field' => $HTTP_POST_VARS['cc_billing_city']);
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STATE,
                                          'field' => $HTTP_POST_VARS['cc_billing_state']);
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_POSTCODE,
                                          'field' => $HTTP_POST_VARS['cc_billing_postcode']);
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_COUNTRY,
                                          'field' => $billing_country_info['countries_name']);
      } else {
        global $order;

        $types_array = array();
        while (list($key, $value) = each($this->cc_types)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }

        $currencies_array = array();
        while (list($key, $value) = each($this->cc_currencies)) {
          $currencies_array[] = array('id' => $key,
									  'text' => $value);
        }

        $today = getdate();

        $months_array = array();
        for ($i=1; $i<13; $i++) {
          $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
          $year_valid_from_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $year_expires_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $year_expires_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $confirmation['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_OWNER,
                                              'field' => tep_draw_input_field('cc_owner', $order->delivery['firstname'] . ' ' . $order->delivery['lastname'])),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_TYPE,
                                              'field' => tep_draw_pull_down_menu('cc_type', $types_array)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_NUMBER,
                                              'field' => tep_draw_input_field('cc_number_nh-dns')),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_EXPIRES,
                                              'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC,
                                              'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"')),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER,
                                              'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . ' ' . MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER_INFO),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CURRENCY,
                                              'field' => (sizeof($currencies_array)>1 ? tep_draw_pull_down_menu('cc_currency', $currencies_array, $currency) : tep_draw_hidden_field('cc_currency', $currency) . $this->cc_currencies[$currency])),
                                        array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STREET,
											  'field' => tep_draw_textarea_field('cc_billing_street', 'soft', '40', '4')),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_CITY,
											  'field' => tep_draw_input_field('cc_billing_city', '', 'size="40"')),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STATE,
											  'field' => tep_draw_input_field('cc_billing_state', '', 'size="20"')),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_POSTCODE,
											  'field' => tep_draw_input_field('cc_billing_postcode', '', 'size="20"')),
										array('title' => MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_COUNTRY,
											  'field' => $this->get_country_string()));
      }

      return $confirmation;
    }

    function process_button() {
      if (MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS;

        $process_button_string = tep_draw_hidden_field('cc_owner', $HTTP_POST_VARS['cc_owner']) .
                                 tep_draw_hidden_field('cc_type', $HTTP_POST_VARS['cc_type']) .
                                 tep_draw_hidden_field('cc_number_nh-dns', preg_replace('/[^\d]/', '', $HTTP_POST_VARS['cc_number_nh-dns'])) .
                                 tep_draw_hidden_field('cc_expires_month', $HTTP_POST_VARS['cc_expires_month']) .
                                 tep_draw_hidden_field('cc_expires_year', $HTTP_POST_VARS['cc_expires_year']) .
                                 tep_draw_hidden_field('cc_cvc_nh-dns', $HTTP_POST_VARS['cc_cvc_nh-dns']) .
                                 tep_draw_hidden_field('cc_issue_nh-dns', $HTTP_POST_VARS['cc_issue_nh-dns']) .
                                 tep_draw_hidden_field('cc_currency', $HTTP_POST_VARS['cc_currency']) .
								 tep_draw_hidden_field('cc_billing_country', $HTTP_POST_VARS['cc_billing_country']) .
								 tep_draw_hidden_field('cc_billing_postcode', $HTTP_POST_VARS['cc_billing_postcode']) .
								 tep_draw_hidden_field('cc_billing_state', $HTTP_POST_VARS['cc_billing_state']) .
								 tep_draw_hidden_field('cc_billing_city', $HTTP_POST_VARS['cc_billing_city']) .
								 tep_draw_hidden_field('cc_billing_street', $HTTP_POST_VARS['cc_billing_street']);

        return $process_button_string;
      }

      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $order, $sendto, $messageStack, $currencies;

      if (isset($HTTP_POST_VARS['cc_owner']) && !empty($HTTP_POST_VARS['cc_owner']) && isset($HTTP_POST_VARS['cc_type']) && isset($this->cc_types[$HTTP_POST_VARS['cc_type']]) && isset($HTTP_POST_VARS['cc_number_nh-dns']) && !empty($HTTP_POST_VARS['cc_number_nh-dns'])) {
        if (MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_SERVER == 'Live') {
          $api_url = 'https://api-3t.paypal.com/nvp';
        } else {
          $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
        }
		$new_order_number_query = tep_db_query("show table status like '" . TABLE_ORDERS . "'");
		$new_order_number_row = tep_db_fetch_array($new_order_number_query);
		$new_order_number = (int)$new_order_number_row['Auto_increment'];

		$params = array('METHOD' => 'DoDirectPayment',
						'VERSION' => '51.0',
						'PWD' => MODULE_PAYMENT_PAYPAL_DIRECT_API_PASSWORD,
						'USER' => MODULE_PAYMENT_PAYPAL_DIRECT_API_USERNAME,
						'SIGNATURE' => MODULE_PAYMENT_PAYPAL_DIRECT_API_SIGNATURE,
						'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                        'IPADDRESS' => tep_get_ip_address(),
						'DESC' => 'Payment against an invoice #' . $new_order_number,
						'CUSTOM' => '',
						'INVNUM' => $new_order_number,
						'NOTIFYURL' => HTTPS_SERVER . DIR_WS_CATALOG . 'ext/modules/payment/paypal/index.php',

						'AMT' => $this->format_raw($order->info['total'], $HTTP_POST_VARS['cc_currency']),
						'CREDITCARDTYPE' => $HTTP_POST_VARS['cc_type'],
						'ACCT' => $HTTP_POST_VARS['cc_number_nh-dns'],
						'EXPDATE' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
						'CVV2' => $HTTP_POST_VARS['cc_cvc_nh-dns'],
						'FIRSTNAME' => substr($HTTP_POST_VARS['cc_owner'], 0, strpos($HTTP_POST_VARS['cc_owner'], ' ')),
						'LASTNAME' => substr($HTTP_POST_VARS['cc_owner'], strpos($HTTP_POST_VARS['cc_owner'], ' ')+1),
						'STREET' => $HTTP_POST_VARS['cc_billing_street'],
						'CITY' => $HTTP_POST_VARS['cc_billing_city'],
						'STATE' => $HTTP_POST_VARS['cc_billing_state'],
						'ZIP' => $HTTP_POST_VARS['cc_billing_postcode'],
						'COUNTRYCODE' => $HTTP_POST_VARS['cc_billing_country'],
						'CURRENCYCODE' => $HTTP_POST_VARS['cc_currency'],
                        'EMAIL' => $order->customer['email_address'],
                        'PHONENUM' => $order->delivery['telephone'],
						);

        if ( ($HTTP_POST_VARS['cc_type'] == 'SWITCH') || ($HTTP_POST_VARS['cc_type'] == 'SOLO') ) {
          $params['ISSUENUMBER'] = $HTTP_POST_VARS['cc_issue_nh-dns'];
        }

        $post_string = '';

		reset($params);
        while (list($key, $value) = each($params)) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $response = $this->sendTransactionToGateway($api_url, $post_string);
        $response_array = array();
        parse_str($response, $response_array);

        if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
		  $messageStack->add_session('header', $response_array['L_LONGMESSAGE0']);
          $payment_error_return = 'cc_owner=' . urlencode($HTTP_POST_VARS['cc_owner']) . '&cc_type=' . $HTTP_POST_VARS['cc_type'] . '&cc_expires_month=' . $HTTP_POST_VARS['cc_expires_month'] . '&cc_expires_year=' . $HTTP_POST_VARS['cc_expires_year'] . '&cc_currency=' . $HTTP_POST_VARS['cc_currency'] . '&cc_billing_country=' . urlencode($HTTP_POST_VARS['cc_billing_country']) . '&cc_billing_postcode=' . urlencode($HTTP_POST_VARS['cc_billing_postcode']) . '&cc_billing_state=' . urlencode($HTTP_POST_VARS['cc_billing_state']) . '&cc_billing_city=' . urlencode($HTTP_POST_VARS['cc_billing_city']) . '&cc_billing_street=' . urlencode($HTTP_POST_VARS['cc_billing_street']) . '';

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL'));
        } else {
		  $billing_country_info = tep_get_countries('', true, $HTTP_POST_VARS['cc_billing_country']);
		  if (sizeof($billing_country_info)==0) {
			$countries = file(DIR_WS_MODULES . 'payment/all_countries.csv');
			reset($countries);
			while (list(, $country_info) = each($countries)) {
			  list($country_code, $country_name, $country_iso_code_3) = explode(';', $country_info);
			  if ($country_code==$HTTP_POST_VARS['cc_billing_country']) {
				$billing_country_info = array('countries_id' => '', 'countries_name' => $country_name, 'countries_iso_code_2' => $country_code, 'countries_iso_code_3' => $country_iso_code_3);
			  }
			}
		  }
		  $order->info['cc_type'] = $HTTP_POST_VARS['cc_type'];
		  $order->info['cc_owner'] = $HTTP_POST_VARS['cc_owner'];
		  $order->info['cc_number'] = $HTTP_POST_VARS['cc_number_nh-dns'];
		  $order->info['cc_expires'] = $HTTP_POST_VARS['cc_expires_month'] . '/' . $HTTP_POST_VARS['cc_expires_year'];
		  list($billing_firstname, $billing_lastname) = explode(' ', $HTTP_POST_VARS['cc_owner']);
		  $order->billing['firstname'] = $billing_firstname;
		  $order->billing['lastname'] = $billing_lastname;
		  $order->billing['street_address'] = $HTTP_POST_VARS['cc_billing_street'];
		  $order->billing['city'] = $HTTP_POST_VARS['cc_billing_city'];
		  $order->billing['country'] = array('id' => $billing_country_info['countries_id'], 'title' => $billing_country_info['countries_name'], 'iso_code_2' => $billing_country_info['countries_iso_code_2'], 'iso_code_3' => $billing_country_info['countries_iso_code_3']);
		  $order->billing['postcode'] = $HTTP_POST_VARS['cc_billing_postcode'];
		  $order->billing['state'] = $HTTP_POST_VARS['cc_billing_state'];
		  $order->info['currency'] = $HTTP_POST_VARS['cc_currency'];
		  $order->info['currency_value'] = $currencies->currencies[$HTTP_POST_VARS['cc_currency']]['value'];
		  $order->info['is_paid'] = '1';
		}
      } else {
		$messageStack->add_session('header', MODULE_PAYMENT_PAYPAL_DIRECT_ERROR_ALL_FIELDS_REQUIRED);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
      }
    }

    function after_process() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_DIRECT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Разрешить оплату через PayPal', 'MODULE_PAYMENT_PAYPAL_DIRECT_STATUS', 'False', 'Do you want to accept PayPal Direct payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Username', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_USERNAME', '', 'The username to use for the PayPal API service.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Password', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_PASSWORD', '', 'The password to use for the PayPal API service.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Signature', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_SIGNATURE', '', 'The signature to use for the PayPal API service.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_SERVER', 'Live', 'Use the live or testing (sandbox) gateway server to process transactions?', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Card Acceptance Page', 'MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE', 'Confirmation', 'The location to accept card information. Either on the Checkout Confirmation page or the Checkout Payment page.', '6', '0', 'tep_cfg_select_option(array(\'Confirmation\', \'Payment\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_DIRECT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_PAYMENT_PAYPAL_DIRECT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYPAL_DIRECT_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYPAL_DIRECT_STATUS', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_USERNAME', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_PASSWORD', 'MODULE_PAYMENT_PAYPAL_DIRECT_API_SIGNATURE', 'MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_SERVER', 'MODULE_PAYMENT_PAYPAL_DIRECT_TRANSACTION_METHOD', 'MODULE_PAYMENT_PAYPAL_DIRECT_CARD_INPUT_PAGE', 'MODULE_PAYMENT_PAYPAL_DIRECT_ZONE', 'MODULE_PAYMENT_PAYPAL_DIRECT_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_DIRECT_SORT_ORDER', 'MODULE_PAYMENT_PAYPAL_DIRECT_CURL');
    }

    function sendTransactionToGateway($url, $nvpStr_) {
	  // Set the curl parameters.
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_VERBOSE, 1);

	  // Turn off the server and peer verification (TrustManager Concept).
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_POST, 1);

	  // Set the request as a POST FIELD for curl.
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpStr_);

	  // Get response from the server.
	  $httpResponse = curl_exec($ch);

	  if (!$httpResponse) {
		die("$methodName_ failed: " . curl_error($ch) . ' (' . curl_errno($ch) . ')');
	  }

	  // Extract the response details.
	  $httpResponseAr = explode("&", $httpResponse);

	  $httpParsedResponseAr = array();
	  foreach($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if (sizeof($tmpAr) > 1) {
		  $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	  }

	  if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit('Invalid HTTP Response for POST request(' . $nvpStr_ . ') to ' . $url . '.');
	  }

//	  tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'credit card transaction', str_replace('=', ' = ', str_replace('&', "\n", urldecode($nvpStr_))) . "\n\n" . str_replace('=', ' = ', str_replace('&', "\n", urldecode($httpResponse))), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	  return $httpResponse;
	}

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

	function get_country_string() {
	  global $order;

	  $countries = file(DIR_WS_MODULES . 'payment/all_countries.csv');
	  $countries_array = array(array('id' => '', 'text' => '- - - - - - - - - -'));
	  if (sizeof($countries)==1) {
		list($country_code, $country_name) = explode(';', $countries[0]);
		$countries_string = $country_name . tep_draw_hidden_field('cc_billing_country', $country_code);
	  } else {
		reset($countries);
		while (list(, $country_info) = each($countries)) {
		  list($country_code, $country_name) = explode(';', $country_info);
		  $countries_array[] = array('id' => $country_code, 'text' => $country_name);
		}
		$countries_string = tep_draw_pull_down_menu('cc_billing_country', $countries_array, $order->delivery['country']['iso_code_2']);
	  }
	  return $countries_string;
	}
  }
?>