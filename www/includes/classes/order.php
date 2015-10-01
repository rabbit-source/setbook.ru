<?php
  class order {
    var $info, $totals, $products, $customer, $delivery, $content_type;

    function order($order_id = '') {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      if (tep_not_null($order_id)) {
        $this->query($order_id);
      } else {
        $this->cart();
      }
    }

    function query($order_id) {
      global $languages_id;

      $order_id = tep_db_prepare_input($order_id);

      $order_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      $order = tep_db_fetch_array($order_query);

	  $company = array();
	  reset($order);
	  while (list($k, $v) = each($order)) {
		if (strpos($k, 'customers_company_')!==false) {
		  unset($order[$k]);
		  $k = str_replace('customers_company_', 'company_', $k);
		  $k = str_replace('company_full_name', 'company_full', $k);
		  $k = str_replace('company_name', 'company', $k);
		  $company[$k] = $v;
		}
	  }
	  $is_corporate_query = tep_db_query("select companies_corporate as company_corporate from " . TABLE_COMPANIES . " where customers_id = '" . (int)$order['customers_id'] . "'");
	  $is_corporate = tep_db_fetch_array($is_corporate_query);
	  if (!is_array($is_corporate)) $is_corporate = array('company_corporate' => '0');
	  $company = array_merge($company, $is_corporate);

      $totals_query = tep_db_query("select title, text, class, value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
      while ($totals = tep_db_fetch_array($totals_query)) {
        $this->totals[] = array('title' => $totals['title'],
                                'text' => $totals['text'],
                                'class' => $totals['class'],
                                'value' => $totals['value']);
      }

      $order_total_query = tep_db_query("select text, value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' and class = 'ot_total'");
      $order_total = tep_db_fetch_array($order_total_query);
	  $order_total['text'] = strip_tags($order_total['text']);

      $shipping_method_query = tep_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' and class = 'ot_shipping'");
      $shipping_method = tep_db_fetch_array($shipping_method_query);
	  $shipping_method['title'] = strip_tags($shipping_method['title']);
	  if (strpos($shipping_method['title'], '(')!==false) $shipping_method['title'] = substr($shipping_method['title'], 0, strpos($shipping_method['title'], '('));
	  if (strpos($shipping_method['title'], ':')!==false) $shipping_method['title'] = substr($shipping_method['title'], 0, strpos($shipping_method['title'], ':'));

      $order_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . $order['orders_status'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
      $order_status = tep_db_fetch_array($order_status_query);

      $order_comment_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "' order by orders_status_history_id limit 1");
      $order_comment = tep_db_fetch_array($order_comment_query);

      $this->info = array('id' => $order['orders_id'],
                          'code' => $order['orders_code'],
                          'currency' => $order['currency'],
                          'currency_value' => $order['currency_value'],
                          'payment_method' => $order['payment_method'],
                          'payment_method_class' => $order['payment_method_class'],
                          'cc_type' => $order['cc_type'],
                          'cc_owner' => $order['cc_owner'],
                          'cc_number' => $order['cc_number'],
                          'cc_expires' => $order['cc_expires'],
						  'is_paid' => $order['orders_is_paid'],
                          'check_account_type' => $order['check_account_type'],
                          'check_bank_name' => $order['check_bank_name'],
                          'check_routing_number' => $order['check_routing_number'],
                          'check_account_number' => $order['check_account_number'],
                          'date_purchased' => $order['date_purchased'],
                          'orders_status' => $order_status['orders_status_name'],
                          'last_modified' => $order['last_modified'],
                          'total' => $order_total['text'],
                          'total_value' => $order_total['value'],
                          'shipping_method' => $shipping_method['title'],
						  'comments' => $order_comment['comments'],
						  'delivery_transfer' => $order['delivery_transfer'],
						  'shops_id' => $order['shops_id']);

      $this->customer = array('id' => $order['customers_id'],
							  'name' => $order['customers_name'],
                              'street_address' => $order['customers_street_address'],
                              'suburb' => $order['customers_suburb'],
                              'city' => $order['customers_city'],
                              'postcode' => $order['customers_postcode'],
                              'state' => $order['customers_state'],
                              'country' => $order['customers_country'],
                              'format_id' => $order['customers_address_format_id'],
                              'telephone' => $order['customers_telephone'],
                              'email_address' => $order['customers_email_address']);

	  $this->customer = array_merge($this->customer, $company);

      $this->delivery = array('name' => $order['delivery_name'],
                              'street_address' => $order['delivery_street_address'],
                              'suburb' => $order['delivery_suburb'],
                              'city' => $order['delivery_city'],
                              'postcode' => $order['delivery_postcode'],
                              'state' => $order['delivery_state'],
                              'country' => $order['delivery_country'],
                              'telephone' => $order['delivery_telephone'],
                              'format_id' => $order['delivery_address_format_id'],
							  'tracking_number' => '',
                              'date' => ($order['delivery_date']=='0000-00-00' ? '' : $order['delivery_date']),
                              'time' => $order['delivery_time'],
							  'delivery_method' => $order['delivery_method'],
							  'delivery_method_class' => $order['delivery_method_class'],
							  'delivery_self_address' => $order['delivery_self_address'],
							  'delivery_self_address_id' => $order['delivery_self_address_id']);

      if (trim($this->delivery['name'])=='' && trim($this->delivery['street_address'])=='') {
        $this->delivery = false;
      }

      $this->billing = array('name' => $order['billing_name'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $order['billing_country'],
                             'telephone' => $order['billing_telephone'],
                             'format_id' => $order['billing_address_format_id']);

      if (empty($this->billing['name']) && empty($this->billing['street_address'])) {
        $this->billing = false;
      }

	  $tracking_numbers = array();
      $index = 0;
      $orders_products_query = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],
										'id' => $orders_products['products_id'],
										'name' => $orders_products['products_name'],
										'model' => $orders_products['products_model'],
										'code' => $orders_products['products_code'],
										'manufacturer' => $orders_products['manufacturers_name'],
										'year' => $orders_products['products_year'],
										'type' => $orders_products['products_types_id'],
										'tax' => $orders_products['products_tax'],
										'price' => $orders_products['products_price'],
										'final_price' => $orders_products['final_price'],
										'warranty' => $orders_products['products_warranty'],
										'tracking_number' => $orders_products['tracking_number']);
		if (tep_not_null($orders_products['tracking_number']) && !in_array($orders_products['tracking_number'], $tracking_numbers)) {
		  $tracking_numbers[] = $orders_products['tracking_number'];
		}

        $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

        $index++;
      }

	  if ($this->delivery!=false) $this->delivery['tracking_number'] = implode("\n", $tracking_numbers);
    }

    function cart() {
      global $customer_id, $sendto, $billto, $cart, $languages_id, $currency, $currencies, $shipping, $payment;

      $this->content_type = $cart->get_content_type();
	  $shop_countries = tep_get_shops_countries();

      $customer_address_query = tep_db_query("select c.customers_firstname, c.customers_lastname, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, ab.entry_country_id, ab.entry_telephone, ab.entry_state, c.customers_email_address, c.customers_telephone from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab where c.customers_id = '" . (int)$customer_id . "' and ab.customers_id = '" . (int)$customer_id . "' and c.customers_default_address_id = ab.address_book_id");
      $customer_address = tep_db_fetch_array($customer_address_query);
	  if (!is_array($customer_address)) $customer_address = array();
	  $country_info_query = tep_db_query("select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$customer_address['entry_country_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  $country_info = tep_db_fetch_array($country_info_query);
	  if (!is_array($country_info)) {
		$country_info = array();
		reset($shop_countries);
		while (list(, $shop_country) = each($shop_countries)) {
		  if ($shop_country['country_id']==$customer_address['entry_country_id']) {
			$country_info = array('countries_id' => $shop_country['country_id'], 'countries_name' => $shop_country['country_name'], 'countries_iso_code_2' => $shop_country['country_code'], 'countries_iso_code_3' => $shop_country['country_code_3'], 'address_format_id' => $shop_country['address_format_id']);
			break;
		  }
		}
	  }
	  $zone_info_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$customer_address['entry_zone_id'] . "'");
	  $zone_info = tep_db_fetch_array($zone_info_query);
	  if (!is_array($zone_info)) $zone_info = array();
	  $customer_address = array_merge($customer_address, $country_info, $zone_info);

	  $company = array();
	  $company_info_query = tep_db_query("select * from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_id . "'");
	  $company_info = tep_db_fetch_array($company_info_query);
	  if (!is_array($company_info)) $company_info = array();
	  reset($company_info);
	  while (list($k, $v) = each($company_info)) {
		$k = str_replace('companies_', 'company_', $k);
		$k = str_replace('company_full_name', 'company_full', $k);
		$k = str_replace('company_name', 'company', $k);
		$company[$k] = $v;
	  }

      $shipping_address_query = tep_db_query("select entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_zone_id, entry_country_id, entry_telephone, entry_state from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
      $shipping_address = tep_db_fetch_array($shipping_address_query);
	  if (!is_array($shipping_address)) $shipping_address = array();
	  $country_info_query = tep_db_query("select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$shipping_address['entry_country_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  $country_info = tep_db_fetch_array($country_info_query);
	  if (!is_array($country_info)) {
		$country_info = array();
		reset($shop_countries);
		while (list(, $shop_country) = each($shop_countries)) {
		  if ($shop_country['country_id']==$shipping_address['entry_country_id']) {
			$country_info = array('countries_id' => $shop_country['country_id'], 'countries_name' => $shop_country['country_name'], 'countries_iso_code_2' => $shop_country['country_code'], 'countries_iso_code_3' => $shop_country['country_code_3'], 'address_format_id' => $shop_country['address_format_id']);
			break;
		  }
		}
	  }
	  $zone_info_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$shipping_address['entry_zone_id'] . "'");
	  $zone_info = tep_db_fetch_array($zone_info_query);
	  if (!is_array($zone_info)) $zone_info = array();
	  $shipping_address = array_merge($shipping_address, $country_info, $zone_info);

      $billing_address_query = tep_db_query("select entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_zone_id, entry_country_id, entry_telephone, entry_state from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
      $billing_address = tep_db_fetch_array($billing_address_query);
	  if (!is_array($billing_address)) $billing_address = array();
	  $country_info_query = tep_db_query("select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$billing_address['entry_country_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  $country_info = tep_db_fetch_array($country_info_query);
	  if (!is_array($country_info)) {
		$country_info = array();
		reset($shop_countries);
		while (list(, $shop_country) = each($shop_countries)) {
		  if ($shop_country['country_id']==$billing_address['entry_country_id']) {
			$country_info = array('countries_id' => $shop_country['country_id'], 'countries_name' => $shop_country['country_name'], 'countries_iso_code_2' => $shop_country['country_code'], 'countries_iso_code_3' => $shop_country['country_code_3'], 'address_format_id' => $shop_country['address_format_id']);
			break;
		  }
		}
	  }
	  $zone_info_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$billing_address['entry_zone_id'] . "'");
	  $zone_info = tep_db_fetch_array($zone_info_query);
	  if (!is_array($zone_info)) $zone_info = array();
	  $billing_address = array_merge($billing_address, $country_info, $zone_info);

      $tax_address_query = tep_db_query("select ab.entry_country_id, ab.entry_zone_id from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) where ab.customers_id = '" . (int)$customer_id . "' and ab.address_book_id = '" . (int)($this->content_type == 'virtual' ? $billto : $sendto) . "'");
      $tax_address = tep_db_fetch_array($tax_address_query);

      $this->info = array('code' => '',
						  'order_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => $currency,
                          'currency_value' => $currencies->currencies[$currency]['value'],
                          'is_paid' => '0',
                          'payment_method' => $payment,
                          'cc_type' => (isset($GLOBALS['cc_type']) ? $GLOBALS['cc_type'] : ''),
                          'cc_owner' => (isset($GLOBALS['cc_owner']) ? $GLOBALS['cc_owner'] : ''),
                          'cc_number' => (isset($GLOBALS['cc_number']) ? $GLOBALS['cc_number'] : ''),
                          'cc_expires' => (isset($GLOBALS['cc_expires']) ? $GLOBALS['cc_expires'] : ''),
                          'check_account_type' => (isset($GLOBALS['check_account_type']) ? $GLOBALS['check_account_type'] : ''),
                          'check_bank_name' => (isset($GLOBALS['check_bank_name']) ? $GLOBALS['check_bank_name'] : ''),
                          'check_routing_number' => (isset($GLOBALS['check_routing_number']) ? $GLOBALS['check_routing_number'] : ''),
                          'check_account_number' => (isset($GLOBALS['check_account_number']) ? $GLOBALS['check_account_number'] : ''),
                          'shipping_method' => $shipping['title'],
                          'shipping_cost' => $shipping['cost'],
                          'subtotal' => 0,
                          'tax' => 0,
                          'tax_groups' => array(),
                          'comments' => (isset($GLOBALS['comments']) ? $GLOBALS['comments'] : ''),
						  'delivery_transfer' => $cart->info['delivery_transfer'],
						  'shops_id' => SHOP_ID);

      if (isset($GLOBALS[$payment]) && is_object($GLOBALS[$payment])) {
        $this->info['payment_method'] = $GLOBALS[$payment]->title;

        if ( isset($GLOBALS[$payment]->order_status) && is_numeric($GLOBALS[$payment]->order_status) && ($GLOBALS[$payment]->order_status > 0) ) {
          $this->info['order_status'] = $GLOBALS[$payment]->order_status;
        }
      }

      $this->customer = array('id' => $customer_id,
							  'firstname' => $customer_address['customers_firstname'],
                              'lastname' => $customer_address['customers_lastname'],
                              'street_address' => $customer_address['entry_street_address'],
                              'suburb' => $customer_address['entry_suburb'],
                              'city' => $customer_address['entry_city'],
                              'postcode' => $customer_address['entry_postcode'],
                              'state' => ((tep_not_null($customer_address['entry_state'])) ? $customer_address['entry_state'] : $customer_address['zone_name']),
                              'zone_id' => $customer_address['entry_zone_id'],
                              'country' => array('id' => $customer_address['countries_id'], 'title' => $customer_address['countries_name'], 'iso_code_2' => $customer_address['countries_iso_code_2'], 'iso_code_3' => $customer_address['countries_iso_code_3']),
                              'format_id' => $customer_address['address_format_id'],
                              'telephone' => $customer_address['entry_telephone'],
                              'email_address' => $customer_address['customers_email_address']);

	  $this->customer = array_merge($this->customer, $company);

      $this->delivery = array('firstname' => $shipping_address['entry_firstname'],
                              'lastname' => $shipping_address['entry_lastname'],
                              'street_address' => $shipping_address['entry_street_address'],
                              'suburb' => $shipping_address['entry_suburb'],
                              'city' => $shipping_address['entry_city'],
                              'postcode' => $shipping_address['entry_postcode'],
                              'state' => ((tep_not_null($shipping_address['entry_state'])) ? $shipping_address['entry_state'] : $shipping_address['zone_name']),
                              'zone_id' => $shipping_address['entry_zone_id'],
                              'country' => array('id' => $shipping_address['countries_id'], 'title' => $shipping_address['countries_name'], 'iso_code_2' => $shipping_address['countries_iso_code_2'], 'iso_code_3' => $shipping_address['countries_iso_code_3']),
                              'country_id' => $shipping_address['entry_country_id'],
                              'telephone' => $shipping_address['entry_telephone'],
                              'format_id' => $shipping_address['address_format_id']);

      $this->billing = array('firstname' => $billing_address['entry_firstname'],
                             'lastname' => $billing_address['entry_lastname'],
                             'street_address' => $billing_address['entry_street_address'],
                             'suburb' => $billing_address['entry_suburb'],
                             'city' => $billing_address['entry_city'],
                             'postcode' => $billing_address['entry_postcode'],
                             'state' => ((tep_not_null($billing_address['entry_state'])) ? $billing_address['entry_state'] : $billing_address['zone_name']),
                             'zone_id' => $billing_address['entry_zone_id'],
                             'country' => array('id' => $billing_address['countries_id'], 'title' => $billing_address['countries_name'], 'iso_code_2' => $billing_address['countries_iso_code_2'], 'iso_code_3' => $billing_address['countries_iso_code_3']),
                             'country_id' => $billing_address['entry_country_id'],
                             'telephone' => $billing_address['entry_telephone'],
                             'format_id' => $billing_address['address_format_id']);

      $index = 0;
      $products = $cart->get_products();
	  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $this->products[$index] = array('qty' => $products[$i]['quantity'],
                                        'name' => $products[$i]['name'],
                                        'model' => $products[$i]['model'],
                                        'code' => $products[$i]['code'],
                                        'manufacturer' => $products[$i]['manufacturer'],
                                        'year' => $products[$i]['year'],
                                        'type' => $products[$i]['type'],
										'periodicity' => $products[$i]['periodicity'],
										'periodicity_min' => $products[$i]['periodicity_min'],
                                        'price' => $products[$i]['price'],
                                        'final_price' => $products[$i]['price'],
                                        'tax' => tep_get_tax_rate($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                                        'tax_description' => tep_get_tax_description($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                                        'weight' => $products[$i]['weight'],
                                        'warranty' => $products[$i]['warranty'],
                                        'id' => $products[$i]['id']);

		if (tep_not_null($products[$i]['filename'])) {
		  $this->products[$index]['filename'] = $products[$i]['filename'];
		  $this->products[$index]['download_maxdays'] = DOWNLOAD_MAX_DAYS;
		  $this->products[$index]['download_count'] = DOWNLOAD_MAX_COUNT;
		}

        $shown_price = tep_add_tax($this->products[$index]['final_price'], $this->products[$index]['tax']) * $this->products[$index]['qty'];
        $this->info['subtotal'] += $shown_price;

        $products_tax = $this->products[$index]['tax'];
        $products_tax_description = $this->products[$index]['tax_description'];
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
          $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          if (isset($this->info['tax_groups']["$products_tax_description"])) {
            $this->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          } else {
            $this->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          }
        } else {
          $this->info['tax'] += ($products_tax / 100) * $shown_price;
          if (isset($this->info['tax_groups']["$products_tax_description"])) {
            $this->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
          } else {
            $this->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
          }
        }

        $index++;
      }

      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
      } else {
        $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
      }
    }
  }
?>