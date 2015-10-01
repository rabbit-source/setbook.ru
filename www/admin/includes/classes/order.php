<?php
  class order {
    var $info, $totals, $products, $customer, $delivery;

    function order($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
	  global $languages_id;
      $order_query = tep_db_query("select *, if(delivery_transfer>0, datediff(delivery_transfer, date_purchased), 0) as delivery_transfer_days from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      $order = tep_db_fetch_array($order_query);
	  if (!is_array($order)) $order = array();

	  $shop_info_query = tep_db_query("select shops_url from " . TABLE_SHOPS . " where shops_id = '" . (int)$order['shops_id'] . "'");
	  $shop_info = tep_db_fetch_array($shop_info_query);

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

	  $comments_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$insert_id . "' order by orders_status_history_id limit 1");
	  $comments = tep_db_fetch_array($comments_query);

      $totals_query = tep_db_query("select class, title, text, value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
      while ($totals = tep_db_fetch_array($totals_query)) {
        $this->totals[] = array('class' => $totals['class'],
								'title' => $totals['title'],
                                'text' => $totals['text'],
								'value' => $totals['value']);
      }

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
                          'check_account_type' => $order['check_account_type'],
                          'check_bank_name' => $order['check_bank_name'],
                          'check_routing_number' => $order['check_routing_number'],
                          'check_account_number' => $order['check_account_number'],
                          'date_purchased' => $order['date_purchased'],
                          'orders_status' => $order['orders_status'],
                          'last_modified' => $order['last_modified'],
						  'payer_requisites' => $order['payer_requisites'],
						  'self_delivery' => $order['delivery_self_address'],
						  'delivery_transfer' => $order['delivery_transfer'],
						  'delivery_transfer_days' => $order['delivery_transfer_days'],
						  'comments' => $comments['comments'],
						  'enabled_ssl' => $order['orders_ssl_enabled'],
						  'shops_id' => $order['shops_id'],
						  'shops_url' => $shop_info['shops_url']);

      $this->customer = array('type' => (tep_not_null($order['customers_company']) ? 'corporate' : 'private'),
							  'name' => $order['customers_name'],
                              'street_address' => $order['customers_street_address'],
                              'suburb' => $order['customers_suburb'],
                              'city' => $order['customers_city'],
                              'postcode' => $order['customers_postcode'],
                              'state' => $order['customers_state'],
                              'country' => $order['customers_country'],
                              'format_id' => $order['customers_address_format_id'],
                              'telephone' => $order['customers_telephone'],
                              'email_address' => $order['customers_email_address'],
                              'id' => $order['customers_id'],
                              'ip' => $order['customers_ip'],
                              'referer' => $order['customers_referer']);

	  $this->customer = array_merge($this->customer, $company);

      $this->delivery = array('name' => $order['delivery_name'],
                              'company' => $order['delivery_company'],
                              'street_address' => $order['delivery_street_address'],
                              'suburb' => $order['delivery_suburb'],
                              'city' => $order['delivery_city'],
                              'postcode' => $order['delivery_postcode'],
                              'state' => $order['delivery_state'],
                              'country' => $order['delivery_country'],
                              'format_id' => $order['delivery_address_format_id'],
                              'telephone' => $order['delivery_telephone'],
                              'date' => ($order['delivery_date']=='0000-00-00' ? '' : $order['delivery_date']),
                              'time' => $order['delivery_time'],
							  'delivery_method' => $order['delivery_method'],
							  'delivery_method_class' => $order['delivery_method_class'],
							  'delivery_self_address' => $order['delivery_self_address'],
							  'delivery_self_address_id' => $order['delivery_self_address_id']);

      $this->billing = array('name' => $order['billing_name'],
                             'company' => $order['billing_company'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $order['billing_country'],
                             'format_id' => $order['billing_address_format_id'],
                             'telephone' => $order['billing_telephone']);

      $index = 0;
      $orders_products_query = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                                        'id' => $orders_products['products_id'],
                                        'name' => $orders_products['products_name'],
                                        'model' => $orders_products['products_model'],
                                        'manufacturer' => $orders_products['manufacturers_name'],
                                        'year' => $orders_products['products_year'],
                                        'type' => $orders_products['products_types_id'],
                                        'code' => $orders_products['products_code'],
                                        'seller_code' => $orders_products['products_seller_code'],
                                        'weight' => $orders_products['products_weight'],
										'warranty' => $orders_products['products_warranty'],
                                        'tax' => $orders_products['products_tax'],
                                        'price' => $orders_products['products_price'],
                                        'final_price' => $orders_products['final_price']);
        $index++;
      }
    }
  }
?>