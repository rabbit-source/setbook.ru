<?php
  require('includes/application_top.php');

  $shops_array = array(array('id' => '', 'text' => TEXT_ALL_SHOPS));
  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " where 1" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
  while ($shops = tep_db_fetch_array($shops_query)) {
	$shops_array[] = array('id' => $shops['shops_id'], 'text' => str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])));
  }

  if (!function_exists("tep_get_categories_name")) {
	function tep_get_categories_name($categories_id, $language = '') {
	  global $languages_id;
	  if (empty($language)) $language = $languages_id;
	  $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$language . "'");
	  $categories = tep_db_fetch_array($categories_query);
	  return $categories['categories_name'];
	}
  }

  if (!function_exists("tep_get_subcategories")) {
	function tep_get_subcategories(&$subcategories_array, $parent_id = 0) {
	  $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_status = '1' and parent_id = '" . (int)$parent_id . "'");
	  while ($subcategories = tep_db_fetch_array($subcategories_query)) {
		$subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];
		if ($subcategories['categories_id'] != $parent_id) {
		  tep_get_subcategories($subcategories_array, $subcategories['categories_id']);
		}
	  }
	}
  }

  if (!function_exists("tep_get_parent_categories")) {
	function tep_get_parent_categories(&$categories, $categories_id) {
	  $parent_categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
	  while ($parent_categories = tep_db_fetch_array($parent_categories_query)) {
		if ($parent_categories['parent_id'] == 0) return true;
		$categories[sizeof($categories)] = $parent_categories['parent_id'];
		if ($parent_categories['parent_id'] != $categories_id) {
		  tep_get_parent_categories($categories, $parent_categories['parent_id']);
		}
	  }
	}
  }

  function tep_get_country_id($country_name) {
	$country_id_query = tep_db_query("select countries_id from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "' limit 1");
	if (tep_db_num_rows($country_id_query) > 0) {
	  $country_id_row = tep_db_fetch_array($country_id_query);
	  return $country_id_row['countries_id'];
	}

	return 0;
  }

  function tep_get_country_iso_code_2($country_id) {
	$country_iso_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "' limit 1");
	if (tep_db_num_rows($country_iso_query) > 0) {
	  $country_iso_row = tep_db_fetch_array($country_iso_query);
	  return $country_iso_row['countries_iso_code_2'];
	}

	return 0;
  }

  function tep_get_zone_id($country_id, $zone_name) {
	$zone_id_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_name = '" . tep_db_input($zone_name) . "'");

	if (tep_db_num_rows($zone_id_query) > 0) {
	  $zone_id_row = tep_db_fetch_array($zone_id_query);
	  return $zone_id_row['zone_id'];
    }
	return 0;
  }

  function tep_html_quotes($string) {
	return str_replace("'", "&#039;", $string);
  }

  function tep_html_unquote($string) {
	return str_replace("&#039;", "'", $string);
  }

  function tep_order_products_updated($orders_id, $products = array(), $new_date = '') {
	if (!is_array($products)) $products = array();
	$email_entry = '';
	if (tep_not_null($new_date)) {
	  $email_entry .= sprintf(EMAIL_TEXT_DATE_UPDATED, $new_date) . EMAIL_SEPARATOR . EMAIL_SEPARATOR;
	}
	if (sizeof($products) > 0) {
	  while (list(, $product) = each($products)) {
		$products_query = tep_db_query("select products_id, products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product['products_id'] . "'");
		if (tep_db_num_rows($products_query) > 0) {
		  $products_array = tep_db_fetch_array($products_query);
		  $products_array['products_name'] = html_entity_decode($products_array['products_name'], ENT_COMPAT);
		  if ($product['action'] == 'add') {
			$email_entry .= sprintf(EMAIL_TEXT_PRODUCTS_ADDED, $products_array['products_name'], $product['quantity']) . EMAIL_SEPARATOR . EMAIL_SEPARATOR;
		  } elseif ($product['action'] == 'delete') {
			$email_entry .= sprintf(EMAIL_TEXT_PRODUCTS_DELETED, $products_array['products_name']) . EMAIL_SEPARATOR . EMAIL_SEPARATOR;
		  } elseif ($product['action'] == 'update') {
			$email_entry .= sprintf(EMAIL_TEXT_PRODUCTS_UPDATED, $products_array['products_name'], $product['quantity']) . EMAIL_SEPARATOR . EMAIL_SEPARATOR;
		  }
		}
	  }
	}
	if (tep_not_null($email_entry)) {
	  $email_entry = sprintf(EMAIL_TEXT_SUBJECT_ORDER_UPDATED, $orders_id) . EMAIL_SEPARATOR . EMAIL_SEPARATOR . $email_entry;
	  tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, sprintf(EMAIL_TEXT_SUBJECT_ORDER_UPDATED, $orders_id), $email_entry, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	}
  }

  reset($HTTP_GET_VARS);
  while (list($k, $v) = each($HTTP_GET_VARS)) {
	if (empty($v)) unset($HTTP_GET_VARS[$k]);
  }

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
	if (preg_match('/^\d+$/', $oID)) $search_order_by = 'orders_id';
	else $search_order_by = 'orders_code';

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where " . $search_order_by . " = '" . tep_db_input($oID) . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
    $order_exists = true;
    if (tep_db_num_rows($orders_query) < 1) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	} else {
	  $orders = tep_db_fetch_array($orders_query);
	  $HTTP_GET_VARS['oID'] = $orders['orders_id'];
	}
  }

  include(DIR_WS_CLASSES . 'order.php');

  if (isset($HTTP_GET_VARS['oID'])) {
	if ($order_exists) {
	  $order = new order($HTTP_GET_VARS['oID']);
	  if ($order->info['currency_value'] > 10) $round_to = 3;
	  elseif ($order->info['currency_value'] < 0.1) $round_to = 0;
	  else $round_to = 1;

	  $shop_db_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$order->info['shops_id'] . "'");
	  $shop_db = tep_db_fetch_array($shop_db_query);
	  if (tep_not_null($shop_db['shops_database'])) {
		tep_db_select_db($shop_db['shops_database']);

		$self_delivery_addresses = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
		$self_delivery_addresses_array = array();
		$self_delivery_query = tep_db_query("select self_delivery_id, entry_city as city, entry_street_address as street_address from " . TABLE_SELF_DELIVERY . " where 1 order by city, street_address");
		while ($self_delivery = tep_db_fetch_array($self_delivery_query)) {
		  $self_delivery_address = tep_address_format($order->delivery['format_id'], $self_delivery, 1, '', ', ');
		  $self_delivery_addresses_array[$self_delivery['self_delivery_id']] = $self_delivery_address;
		  $self_delivery_addresses[] = array('id' => $self_delivery['self_delivery_id'],
											 'text' => $self_delivery_address);
		}
		tep_db_select_db(DB_DATABASE);
	  }

	  $installed_payment = tep_get_payment_modules($order->info['shops_id']);
	  $payments_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
	  reset($installed_payment);
	  while (list($code, $title) = each($installed_payment)) {
		$payments_array[] = array('id' => $code, 'text' => $title);
	  }

	  $installed_shipping = tep_get_shipping_modules($order->info['shops_id']);
	  $shipping_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
	  reset($installed_shipping);
	  while (list($code, $title) = each($installed_shipping)) {
		$shipping_array[] = array('id' => $code, 'text' => $title);
	  }
	}
  }

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_descriptions_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name, orders_status_description from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by sort_order");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
    $orders_status_descriptions_array[$orders_status['orders_status_id']] = $orders_status['orders_status_description'];
  }

  $products_types = array();
  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order");
  while ($products_type = tep_db_fetch_array($products_types_query)) {
    $products_types[] = array('id' => $products_type['products_types_id'],
                              'text' => $products_type['products_types_name']);
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'download_bn_file':
		$host = 'sftp.barnesandnoble.com';
		$port = '21';
		$user = 'BNA0194750';
		$pwd = 'UU7l%jE';

		$opt_array = array(CURLOPT_URL => 'sftp://' . $host . '/Orders/Orders_to_pickup/',
						   CURLOPT_USERPWD => $user . ':' . $pwd,
						   CURLOPT_FTPPORT => $port,
						   CURLOPT_RETURNTRANSFER => 1);

		tep_set_time_limit(60);
		$ch = curl_init();
		curl_setopt_array($ch, ($opt_array + array(CURLOPT_FTPLISTONLY => 1)));
		$filenames = trim(curl_exec($ch));
		$error = curl_error($ch);
		curl_close($ch);
		$all_files = explode("\n", $filenames);
		$file_to_download = $all_files[0];
		$latest_date = 0;
		reset($all_files);
		while (list(, $filename) = each($all_files)) {
		  list($filename_date) = explode('_', $filename);
		  if ($filename_date > $latest_date) {
			$latest_date = $filename_date;
			$file_to_download = $filename;
		  }
		}

		if ($error) {
		  $messageStack->add_session($error, 'error');
		  tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))));
		} else {
		  $ch = curl_init();
		  $opt_array[CURLOPT_URL] .= $file_to_download;
		  curl_setopt_array($ch, $opt_array);
		  $result = curl_exec($ch);
		  $error = curl_error($ch);
		  if ($error) {
			$messageStack->add_session($error, 'error');
			tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))));
		  } else {
			header('Expires: Mon, 26 Nov 1962 00:00:00 GMT');
			header('Last-Modified: ' . gmdate('D,d M Y H:i:s') . ' GMT');
			header('Pragma: no-cache');
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-disposition: attachment; filename=' . basename($file_to_download));
			echo $result;
			die;
		  }
		}
		break;
	  case 'process_orders':
		// автоматическое обновление заказов
		$is_default_shop_query = tep_db_query("select shops_default_status from " . TABLE_SHOPS . " where shops_id = '" . (int)SHOP_ID . "'");
		$is_default_shop = tep_db_fetch_array($is_default_shop_query);

		$rows = 0;
		$absent_products = array();
		$files = tep_get_files(UPLOAD_DIR . 'changed_orders/', '.csv');
		$new_files = array();
		reset($files);
		while (list($i, $file) = each($files)) {
		  if (substr($file, 0, 2)!='aa') $new_files[] = $file;
		}

		if (sizeof($new_files) > 0) {
		  tep_set_time_limit(300);

		  $module_directory = DIR_FS_CATALOG_MODULES . 'order_total/';
		  $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
		  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
		  $directory_array = array();
		  if ($dir = @dir($module_directory)) {
			while ($file = $dir->read()) {
			  if (!is_dir($module_directory . $file)) {
				if (substr($file, strrpos($file, '.')) == $file_extension) {
				  $directory_array[] = $file;
				}
			  }
			}
			sort($directory_array);
			$dir->close();
		  }

		  $statuses_asc = array();
		  $statuses_query = tep_db_query("select orders_status_id, sort_order from " . TABLE_ORDERS_STATUS . " order by sort_order");
		  while ($statuses = tep_db_fetch_array($statuses_query)) {
			$statuses_asc[$statuses['orders_status_id']] = $statuses['sort_order'];
		  }

		  $order_total_modules = array();
		  $modules = array();
		  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
			$file = $directory_array[$i];

			include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/order_total/' . $file);
			include($module_directory . $file);

			$class = substr($file, 0, strrpos($file, '.'));
			if (tep_class_exists($class)) {
			  $module = new $class;
			  $order_total_modules[$class] = trim($module->title) . (substr(trim($module->title), -1)!=':' ? ':' : '');
			}
		  }

		  $operator = 'robot';

		  $all_payment_modules = array();
		  $all_shipping_modules = array();
		  $shops_query = tep_db_query("select shops_id from " . TABLE_SHOPS . " where 1");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			$all_payment_modules[$shops['shops_id']] = tep_get_payment_modules($shops['shops_id']);
			$all_shipping_modules[$shops['shops_id']] = tep_get_shipping_modules($shops['shops_id']);
		  }
		}

		reset($new_files);
		while (list($i, $file) = each($new_files)) {
		  if ($fp = @fopen(UPLOAD_DIR . 'changed_orders/' . $file, 'r')) {
			list($update_order_id, $payment_class, $shipping_class, $ot_shipping_value, $delivery_state, $delivery_suburb, $delivery_city, $delivery_postcode, $delivery_street_address, $delivery_telephone, $delivery_self_address_id, $ot_total_value, $ot_discount_value, $update_order_status, $comments, $update_customer_id, $delivery_name, $update_company_name, $update_company_inn, $update_company_kpp, $update_company_address_corporate, $order_paid_sum, $update_order_code) = fgetcsv($fp, '10000', ';');
			$order_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where orders_id = '" . (int)$update_order_id . "'");
			$order_check = tep_db_fetch_array($order_check_query);

			if ($order_check['total'] > 0 && $update_order_status > 0) {
			  $order = new order($update_order_id);

			  $shop_info_query = tep_db_query("select shops_database, shops_currency from " . TABLE_SHOPS . " where shops_id = '" . (int)$order->info['shops_id'] . "'");
			  $shop_info = tep_db_fetch_array($shop_info_query);

			  if (tep_not_null($order->info['payment_method']) && $payment_class==$order->info['payment_method_class']) {
				$payment_method = $order->info['payment_method'];
			  } else {
				$payment_modules = $all_payment_modules[$order->info['shops_id']];
				$payment_method = $payment_modules[$payment_class];
			  }

			  if (tep_not_null($order->delivery['delivery_method']) && $shipping_class==$order->info['delivery_method_class']) {
				$shipping_method = $order->delivery['delivery_method'];
			  } else {
				$shipping_modules = $all_shipping_modules[$order->info['shops_id']];
				$shipping_method = $shipping_modules[$shipping_class];
			  }

			  $delivery_self_address = '';
			  if ($delivery_self_address_id > 0) {
				tep_db_select_db($shop_info['shops_database']);
				$delivery_self_query = tep_db_query("select self_delivery_id, entry_city as city, entry_street_address as street_address from " . TABLE_SELF_DELIVERY . " where self_delivery_id = '" . (int)$delivery_self_address_id . "'");
				if (tep_db_num_rows($delivery_self_query) > 0) {
				  $delivery_self = tep_db_fetch_array($delivery_self_query);
				  $delivery_self_address = tep_address_format($order->delivery['format_id'], $delivery_self, 1, '', ', ');
				} else {
				  $delivery_self_address_id = 0;
				}
				tep_db_select_db(DB_DATABASE);
			  }

			  $sql_data_array = array('last_modified' => 'now()',
									  'payment_method' => $payment_method,
									  'payment_method_class' => $payment_class,
									  'delivery_name' => tep_db_prepare_input($delivery_name),
									  'delivery_method' => $shipping_method,
									  'delivery_method_class' => $shipping_class,
									  'delivery_state' => tep_db_prepare_input($delivery_state),
									  'delivery_suburb' => tep_db_prepare_input($delivery_suburb),
									  'delivery_city' => tep_db_prepare_input($delivery_city),
									  'delivery_postcode' => tep_db_prepare_input($delivery_postcode),
									  'delivery_street_address' => tep_db_prepare_input($delivery_street_address),
									  'delivery_telephone' => tep_db_prepare_input($delivery_telephone),
									  'delivery_self_address' => tep_db_prepare_input($delivery_self_address),
									  'delivery_self_address_id' => $delivery_self_address_id);
			  if (tep_not_null($update_order_code)) {
				$sql_data_array['orders_code'] = $update_order_code;
			  }
			  if (tep_not_null($update_company_name)) {
				$sql_data_array['customers_company'] = tep_db_prepare_input($update_company_name);
				$sql_data_array['customers_company_name'] = tep_db_prepare_input($update_company_name);
				$sql_data_array['customers_company_inn'] = tep_db_prepare_input($update_company_inn);
				$sql_data_array['customers_company_kpp'] = tep_db_prepare_input($update_company_kpp);
				$sql_data_array['customers_company_address_corporate'] = tep_db_prepare_input($update_company_address_corporate);
			  } else {
				$sql_data_array['customers_company'] = '';
				$sql_data_array['customers_company_name'] = '';
				$sql_data_array['customers_company_inn'] = '';
				$sql_data_array['customers_company_kpp'] = '';
				$sql_data_array['customers_company_address_corporate'] = '';
				$sql_data_array['customers_company_full_name'] = '';
				$sql_data_array['customers_company_ogrn'] = '';
				$sql_data_array['customers_company_okpo'] = '';
				$sql_data_array['customers_company_okogu'] = '';
				$sql_data_array['customers_company_okato'] = '';
				$sql_data_array['customers_company_okved'] = '';
				$sql_data_array['customers_company_okfs'] = '';
				$sql_data_array['customers_company_okopf'] = '';
				$sql_data_array['customers_company_address_post'] = '';
				$sql_data_array['customers_company_telephone'] = '';
				$sql_data_array['customers_company_fax'] = '';
				$sql_data_array['customers_company_bank'] = '';
				$sql_data_array['customers_company_rs'] = '';
				$sql_data_array['customers_company_ks'] = '';
				$sql_data_array['customers_company_bik'] = '';
				$sql_data_array['customers_company_general'] = '';
				$sql_data_array['customers_company_financial'] = '';
			  }
			  tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . (int)$update_order_id . "'");

			  $old_products = array();
			  reset($order->products);
			  while (list(, $product) = each($order->products)) {
				$old_products[$product['id']] = array('id' => $product['id'],
													  'name' => $product['name'],
													  'qty' => $product['qty'],
													  'type' => $product['type'],
													  'price' => $product['price'],
													  'final_price' => $product['final_price']);
			  }

			  $products = array();
			  $ot_subtotal = 0;
			  $sql_queries = array();
			  while ( (list($products_type_id, $products_code, $products_qty, $products_price, $tracking_number) = fgetcsv($fp, '10000', ';')) !== false) {
				if ((int)$products_code > 0 && (int)$products_qty > 0) {
				  $products_code = 'bbk' . sprintf('%010d', $products_code);
				  $products_price = str_replace(',', '.', (float)$products_price);
				  $products_qty = (int)$products_qty;
				  $product_found = false;
				  $product_info_query = tep_db_query("select products_id, products_name, authors_name, manufacturers_name, products_model, products_weight from " . TABLE_PRODUCTS_INFO . " where products_code = '" . tep_db_input($products_code) . "' and products_types_id = '" . (int)$products_type_id . "'");
				  if (tep_db_num_rows($product_info_query) > 0) {
					$product_info = tep_db_fetch_array($product_info_query);
				  } else {
					$absent_products[$update_order_id][] = array('code' => $products_code, 'file' => $file);
				  }
				  if (!is_array($product_info)) $product_info = array();

				  $products_name = '';
				  if (tep_not_null($product_info['authors_name'])) $products_name .= $product_info['authors_name'] . ': ';
				  $products_name .= $product_info['products_name'];
				  $products[$product_info['products_id']] = array('qty' => $products_qty,
																  'id' => $product_info['products_id'],
																  'name' => $products_name,
																  'model' => $product_info['products_model'],
																  'code' => $products_code,
																  'weight' => $product_info['products_weight'],
																  'type' => $products_type_id,
																  'tax' => 0,
																  'price' => $products_price,
																  'final_price' => $products_price,
																  'tracking_number' => $tracking_number,
																  );
				}
			  }

			  $email_text = '';

			  $temp_string = '';
			  reset($products);
			  while (list($product_id, $product) = each($products)) { #проверяем на добавление новых товаров
				if (!in_array($product_id, array_keys($old_products))) {
				  $temp_string .= sprintf(EMAIL_CRON_TEXT_PRODUCTS_ADDED, tep_html_entity_decode($product['name']), $product['qty']) . "\n";

				  $sql_queries[] = "insert into " . TABLE_ORDERS_PRODUCTS . " (orders_id, products_id, products_model, products_code, products_weight, products_name, products_price, final_price, products_quantity, products_year, products_types_id, manufacturers_name, tracking_number) select '" . (int)$update_order_id . "', products_id, products_model, products_code, products_weight, if (authors_name<>'', concat_ws(': ', authors_name, products_name), products_name), '" . tep_db_input($product['price']) . "', '" . tep_db_input($product['final_price']) . "', '" . (int)$product['qty'] . "', products_year, products_types_id, manufacturers_name, '" . tep_db_input($product['tracking_number']) . "' from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$product_id . "';\n";
				  $old_products[$product_id] = array('id' => $product_id,
													 'name' => $product['name'],
													 'qty' => $product['qty'],
													 'type' => $product['type'],
													 'price' => $product['price'],
													 'final_price' => $product['final_price'],
													 'tracking_number' => $product['tracking_number']);
				}
			  }

			  reset($old_products);
			  while (list($product_id, $product) = each($old_products)) {
	//		  проверяем на удаление заказанных товаров
				if (!in_array($product_id, array_keys($products))) {
				  $temp_string .= sprintf(EMAIL_CRON_TEXT_PRODUCTS_DELETED, $product['name']) . "\n";

				  $sql_queries[] = "delete from " . TABLE_ORDERS_PRODUCTS . " where products_id = '" . (int)$product_id . "' and orders_id = '" . (int)$update_order_id . "';\n";
				  unset($old_products[$product_id]);
				} elseif ($product['qty']!=$products[$product_id]['qty'] || $product['price']!=$products[$product_id]['price'] || $product['tracking_number']!=$products[$product_id]['tracking_number']) {
	//			проверяем на изменение кол-ва/цены заказанных товаров
				  if ($product['qty']!=$products[$product_id]['qty'] || $product['price']!=$products[$product_id]['price']) {
					$temp_string .= sprintf(EMAIL_CRON_TEXT_PRODUCTS_UPDATED, $product['name'], $products[$product_id]['qty']) . "\n";
				  }
				  if (in_array($product_id, array_keys($products))) {
					$sql_queries[] = "update " . TABLE_ORDERS_PRODUCTS . " set products_price = '" . $products[$product_id]['price'] . "', final_price = '" . $products[$product_id]['final_price'] . "', products_tax = '" . $products[$product_id]['tax'] . "', products_quantity = '" . $products[$product_id]['qty'] . "', tracking_number = '" . $products[$product_id]['tracking_number'] . "' where products_id = '" . (int)$product_id . "' and orders_id = '" . (int)$update_order_id . "';\n";
					$old_products[$product_id] = $products[$product_id];
				  }
				}
			  }
			  $email_text .= (tep_not_null($temp_string) ? trim($temp_string) . "\n\n" : '');
	//		  if ($REMOTE_USER=='setbook') echo '<pre>' . print_r($old_products, true) . '</pre>';

			  $ot_subtotal_value = 0;
			  reset($old_products);
			  while (list(, $product) = each($old_products)) {
				$ot_subtotal_value += $product['final_price'] * $product['qty'];
			  }

			  $temp_string = '';
	//		  $comments = tep_html_entity_decode($comments);
//			  if ($update_order_status==0 || ($update_order_status > 0 && $statuses_asc[$update_order_status] < $statuses_asc[$order->info['orders_status']]) ) {
//				$update_order_status = $order->info['orders_status'];
//			  }

			  if (tep_not_null($comments)) {
	//			$comments = str_replace('<br>', "\n", $comments);
	//			$comments = str_replace('<br />', "\n", $comments);
	//			$comments = preg_replace("/\n{2,}/", "\n\n", $comments);
	//			$comments = trim(preg_replace("/Дата заказа: \n\n(.*)\n\nИнформация о заказе/i", '$1', $comments));
				$history_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$update_order_id . "' and comments = '" . tep_db_input($comments) . "'");
				$history_check = tep_db_fetch_array($history_check_query);
				if ($history_check['total'] > 0) $comments = '';
			  }

			  if ($order->info['orders_status'] != $update_order_status && $update_order_status == PARTNERS_ORDERS_STATUS) {
				$partner_info_query = tep_db_query("select partners_id, partners_comission from " . TABLE_ORDERS . " where orders_id = '" . (int)$update_order_id . "'");
				$partner_info = tep_db_fetch_array($partner_info_query);
				if ((int)$partner_info['partners_id'] > 0) {
				  $partners_balance_check_query = tep_db_query("select count(*) as total from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$partner_info['partners_id'] . "' and orders_id = '" . (int)$update_order_id . "'");
				  $partners_balance_check = tep_db_fetch_array($partners_balance_check_query);
				  if ($partners_balance_check['total'] < 1) {
					$order_subtotal_info_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$update_order_id . "' and class = 'ot_subtotal'");
					$order_subtotal_info = tep_db_fetch_array($order_subtotal_info_query);
					tep_db_query("insert into " . TABLE_PARTNERS_BALANCE . " (date_added, partners_id, partners_balance_sum, orders_id, partners_balance_comments) values (now(), '" . (int)$partner_info['partners_id'] . "', '" . str_replace(',', '.', $partner_info['partners_comission']*$order_subtotal_info['value']) . "', '" . (int)$update_order_id . "', '" . (float)($partner_info['partners_comission']*100) . "% от суммы заказа #" . (int)$update_order_id . "')");
				  }
				}
			  }

			  if ($order->info['orders_status']!=$update_order_status || tep_not_null($comments) || tep_not_null($admin_comments)) {
				$sql_queries[] = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_comments, operator) values ('" . (int)$update_order_id . "', '" . (int)$update_order_status . "', now(), '0', '" . tep_db_input($comments)  . "', '" . tep_db_input($admin_comments)  . "', '" . tep_db_input($operator) . "');\n";
				if ($order->info['orders_status']!=$update_order_status) {
				  $status_info_query = tep_db_query("select orders_status_name, orders_status_description from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$update_order_status . "' and language_id = '" . (int)$languages_id . "'");
				  $status_info = tep_db_fetch_array($status_info_query);
				  $temp_string .= sprintf(EMAIL_CRON_TEXT_STATUS_UPDATE, '«' . $status_info['orders_status_name'] . '»') . "\n\n";
				  $sql_queries[] = "update " . TABLE_ORDERS . " set orders_status = '" . (int)$update_order_status . "' where orders_id = '" . (int)$update_order_id . "';\n";
				}
				if (tep_not_null($comments)) {
				  $temp_string .= EMAIL_CRON_TEXT_COMMENTS_UPDATE . "\n" .EMAIL_SEPARATOR . "\n" . $comments . "\n\n";
				}
			  }
			  $email_text .= (tep_not_null($temp_string) ? trim($temp_string) . "\n\n" : '');

			  $order_total_titles = array();
			  reset($order->totals);
			  while (list(, $order_total) = each($order->totals)) {
				$order_total_titles[$order_total['class']] = $order_total['title'];
			  }

			  $order_totals = array();

			  $order_totals[] = array('class' => 'ot_subtotal',
									  'title' => (in_array('ot_subtotal', array_keys($order_total_titles)) ? $order_total_titles['ot_subtotal'] : $order_total_modules['ot_subtotal']),
									  'text' => $currencies->format($ot_subtotal_value, true, $order->info['currency'], $order->info['currency_value']),
									  'value' => $ot_subtotal_value,
									  'sort_order' => 10);

			  $ot_discount_value = str_replace(',', '.', -abs($ot_discount_value));
			  if ($ot_discount_value < 0) {
				$order_totals[] = array('class' => 'ot_discount',
										'title' => (in_array('ot_discount', array_keys($order_total_titles)) ? $order_total_titles['ot_discount'] : $order_total_modules['ot_discount']),
										'text' => $currencies->format($ot_discount_value, true, $order->info['currency'], $order->info['currency_value']),
										'value' => $ot_discount_value,
										'sort_order' => 20);
			  } else {
				$sql_queries[] = "delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$update_order_id . "' and class = 'ot_discount';\n";
			  }

			  $order_totals[] = array('class' => 'ot_shipping',
									  'title' => $shipping_method,
									  'text' => $currencies->format($ot_shipping_value, true, $order->info['currency'], $order->info['currency_value']),
									  'value' => $ot_shipping_value,
									  'sort_order' => 30);

			  $ot_total_value = str_replace(',', '.', ($ot_subtotal_value + $ot_discount_value + $ot_shipping_value));

			  $other_total_check_query = tep_db_query("select sum(value) as other_value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$update_order_id . "' and class not in ('ot_subtotal', 'ot_shipping', 'ot_total', 'ot_discount')");
			  $other_total_check = tep_db_fetch_array($other_total_check_query);
			  $ot_total_value += $other_total_check['other_value'];

			  $order_totals[] = array('class' => 'ot_total',
									  'title' => (in_array('ot_total', array_keys($order_total_titles)) ? $order_total_titles['ot_total'] : $order_total_modules['ot_total']),
									  'text' => '<strong>' . $currencies->format($ot_total_value, true, $order->info['currency'], $order->info['currency_value']) . '</strong>',
									  'value' => $ot_total_value,
									  'sort_order' => 40);

//			  echo $update_order_id . '<pre>' . print_r($order_totals, true) . '</pre>';
			  reset($order_totals);
			  while (list(, $ot_total) = each($order_totals)) {
				$total_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$update_order_id . "' and class = '" . tep_db_input($ot_total['class']) . "'");
				$total_check = tep_db_fetch_array($total_check_query);
				if ($total_check['total'] < 1) {
				  $sql_queries[] = "insert into " . TABLE_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . (int)$update_order_id . "', '" . tep_db_input($ot_total['title']) . "', '" . tep_db_input($ot_total['text']) . "', '" . $ot_total['value'] . "', '" . tep_db_input($ot_total['class']) . "', '" . (int)$ot_total['sort_order'] . "');\n";
				} else {
				  $sql_queries[] = "update " . TABLE_ORDERS_TOTAL . " set title = '" . tep_db_input($ot_total['title']) . "', text = '" . tep_db_input($ot_total['text']) . "', value = '" . $ot_total['value'] . "', sort_order = '" . (int)$ot_total['sort_order'] . "' where orders_id = '" . (int)$update_order_id . "' and class = '" . tep_db_input($ot_total['class']) . "';\n";
				}
				if ($ot_total['class']=='ot_total') {
				  $order_paid_sum = str_replace(',', '.', (float)$order_paid_sum);
				  $sql_queries[] = "update " . TABLE_ORDERS . " set orders_total = '" . $ot_total['value'] . "'" . (round($order_paid_sum)>=round($ot_total['value']) ? ", orders_is_paid = '1'" : "") . " where orders_id = '" . (int)$update_order_id . "';\n";
				}
			  }

			  reset($sql_queries);
			  while (list(, $sql_query) = each($sql_queries)) {
				$sql_query = trim($sql_query);
				if (tep_not_null($sql_query)) tep_db_query($sql_query);
			  }
			  $order_info_query = tep_db_query("select customers_id, shops_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$update_order_id . "'");
			  $order_info = tep_db_fetch_array($order_info_query);
			  $customer_info_query = tep_db_query("select customers_firstname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$order_info['customers_id'] . "'");
			  $customer_info = tep_db_fetch_array($customer_info_query);
			  if (!is_array($customer_info)) $customer_info = array();
			  $shop_info_query = tep_db_query("select if((shops_ssl<>shops_url and shops_ssl<>''), shops_ssl, shops_url) as orders_domain, shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$order_info['shops_id'] . "'");
			  $shop_info = tep_db_fetch_array($shop_info_query);
			  if (!is_array($shop_info)) $shop_info = array();
			  $customer_info = array_merge($customer_info, $shop_info);

			  if (empty($email_text)) {
				unset($absent_products[$update_order_id]);
			  } else {
				$rows ++;

				$customer_name = tep_not_null($order->customer['name']) ? $order->customer['name'] : $customer_info['customers_firstname'];

				$email_subject = sprintf(EMAIL_CRON_TEXT_SUBJECT_ORDER_UPDATED, $update_order_id, tep_date_short($order->info['date_purchased']));

				$order_info_link = tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $update_order_id, 'SSL');
				$order_info_link = preg_replace('/^https?:\/\/[^\/]+\/(.*)$/i', '$1', $order_info_link);
				$order_info_link = $customer_info['orders_domain'] . '/' . $order_info_link;
				if ($order->info['enabled_ssl']=='0') $order_info_link = str_replace('https://', 'http://', $order_info_link);
				$email = sprintf(EMAIL_CRON_TEXT_GREETS, $customer_name) . "\n\n" .
				sprintf(EMAIL_CRON_TEXT_ENTRY_ORDER_UPDATED, $update_order_id, tep_date_short($order->info['date_purchased'])) . "\n\n" .
				trim($email_text) . "\n\n" .
				sprintf(EMAIL_CRON_TEXT_INVOICE_URL, $order_info_link, $order_info_link) . "\n\n" .
				EMAIL_CRON_TEXT_PS;

				$from_name = STORE_NAME;
				$from_email = STORE_OWNER_EMAIL_ADDRESS;
				if (tep_not_null($shop_info['shops_database'])) {
				  tep_db_select_db($shop_info['shops_database']);
				  $store_name_info_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
				  $store_name_info = tep_db_fetch_array($store_name_info_query);
				  if (tep_not_null($store_name_info['configuration_value'])) $from_name = $store_name_info['configuration_value'];
				  $store_email_info_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
				  $store_email_info = tep_db_fetch_array($store_email_info_query);
				  if (tep_not_null($store_email_info['configuration_value'])) $from_email = $store_email_info['configuration_value'];
				  tep_db_select_db(DB_DATABASE);
				}
	//			tep_mail($order->customer['name'], $order->customer['email_address'], $email_subject, $email, $from_name, $from_email);
			  }

			}
			fclose($fp);
			@unlink(UPLOAD_DIR . 'changed_orders/' . $file);
		  }
		}
		break;
	  case 'upload':
		if (is_uploaded_file($_FILES['amazon_file']['tmp_name']) && $HTTP_POST_VARS['amazon_shop_id'] > 0) {
		  $upload_to_shop = (int)$HTTP_POST_VARS['amazon_shop_id'];

		  if ($HTTP_POST_VARS['upload_file_back']=='1') {
			$host = 'sftp.barnesandnoble.com';
			$port = '21';
			$user = 'BNA0194750';
			$pwd = 'UU7l%jE';

			$fp = fopen($_FILES['amazon_file']['tmp_name'], 'r');
			$opt_array = array(CURLOPT_URL => 'sftp://' . $host . '/Orders/Orders_To_Drop_off/' . $_FILES['amazon_file']['name'],
							   CURLOPT_USERPWD => $user . ':' . $pwd,
							   CURLOPT_FTPPORT => $port,
							   CURLOPT_PUT => true,
							   CURLOPT_INFILE => $fp,
							   CURLOPT_INFILESIZE => filesize($_FILES['amazon_file']['tmp_name']));

			$ch = curl_init();
			curl_setopt_array($ch, $opt_array);
			$result = curl_exec($ch);
			$error = curl_error($ch);
			fclose($fp);
			curl_close($ch);

			if ($error) $messageStack->add_session($error, 'error');

			tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))));
			die;
		  }
/*
		  $old_order_id = '';
		  $fp = fopen($_FILES['amazon_file']['tmp_name'], 'r');
		  while ((list($order_code, $order_item_id, $purchase_date, $payments_date, $buyer_email, $buyer_name, $buyer_phone_number, $sku, $product_name, $quantity_purchased, $curr, $item_price, $item_tax, $shipping_price, $shipping_tax, $ship_service_level, $recipient_name, $ship_address_1, $ship_address_2, $ship_address_3, $ship_city, $ship_state, $ship_postal_code, $ship_country, $ship_phone_number) = fgetcsv($fp, 10000, "\t")) !== FALSE) {
			if ((int)$sku > 0) {
			  $order_info_query = tep_db_query("select orders_id, currency_value from " . TABLE_ORDERS . " where orders_code = '" . tep_db_input($order_code) . "'");
			  if (tep_db_num_rows($order_info_query) > 0) {
				$order_info = tep_db_fetch_array($order_info_query);
				$order_id = $order_info['orders_id'];
				$currency_value = $order_info['currency_value'];
				if ($old_order_id != $order_id && $old_order_id > 0) echo '</blockquote><br>' . "\n";
				if ($old_order_id != $order_id) echo $order_id . ':<blockquote>' . "\n";
				$order_product_info_query = tep_db_query("select orders_products_id, products_id, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "' and products_seller_code = '" . tep_db_input($order_item_id) . "'");
				$order_product_info = tep_db_fetch_array($order_product_info_query);
				if (round($order_product_info['final_price'], 2)!=round($item_price/$currency_value, 2)) {
				  echo $order_product_info['products_id'] . ' - ' . round($order_product_info['final_price'], 2) . ' - ' . round($item_price/$currency_value, 2) . '<br>' . "\n";
				  tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set products_price = '" . str_replace(',', '.', round($item_price/$currency_value, 2)) . "', final_price = '" . str_replace(',', '.', round($item_price/$currency_value, 2)) . "' where orders_products_id = '" . (int)$order_product_info['orders_products_id'] . "'");
				}
				$old_order_id = $order_id;
			  }
			}
		  }
//		  die;

		  $orders_query = tep_db_query("select orders_id, currency, currency_value from " . TABLE_ORDERS . " where shops_id = '" . (int)$upload_to_shop . "'");
		  while ($orders = tep_db_fetch_array($orders_query)) {
			$subtotal_query = tep_db_query("select sum(final_price * products_quantity) as subtotal from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			$subtotal = tep_db_fetch_array($subtotal_query);
			tep_db_query("update " . TABLE_ORDERS_TOTAL . " set value = '" . str_replace(',', '.', $subtotal['subtotal']) . "', text = '" . $currencies->format($subtotal['subtotal'], true, $orders['currency'], $orders['currency_value']) . "' where orders_id = '" . (int)$orders['orders_id'] . "' and class = 'ot_subtotal'");
			$total_query = tep_db_query("select sum(value) as total from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders['orders_id'] . "' and class in ('ot_subtotal', 'ot_shipping')");
			$total = tep_db_fetch_array($total_query);
			tep_db_query("update " . TABLE_ORDERS_TOTAL . " set value = '" . str_replace(',', '.', $total['total']) . "', text = '<strong>" . $currencies->format($total['total'], true, $orders['currency'], $orders['currency_value']) . "</strong>' where orders_id = '" . (int)$orders['orders_id'] . "' and class = 'ot_total'");
			tep_db_query("update " . TABLE_ORDERS . " set orders_total = '" . str_replace(',', '.', $total['total']) . "' where orders_id = '" . (int)$orders['orders_id'] . "'");
			tep_upload_order($orders['orders_id']);
		  }
		  die;
*/

		  $orders_to_upload = array();
		  $fp = fopen($_FILES['amazon_file']['tmp_name'], 'r');
		  while (($cell = fgetcsv($fp, 10000, "\t")) !== FALSE) {
			if ($upload_to_shop==15) {
			  list($order_code, , , , , , , , , , , , , , , , $sku) = $cell;
			  //$order_code = substr($order_code, 0, -3);
			} else {
			  list($order_code, , , , , , , $sku) = $cell;
			}
			if ((int)$sku > 0) {
			  $order_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where orders_code = '" . tep_db_input($order_code) . "' and shops_id = '" . (int)$upload_to_shop . "'");
			  $order_check = tep_db_fetch_array($order_check_query);
			  if ($order_check['total'] < 1 && !in_array($order_code, $orders_to_upload)) $orders_to_upload[] = $order_code;
			}
		  }
		  fclose($fp);
//		  print_r($orders_to_upload); die;

		  $ot_subtotal = 0;
		  $ot_shipping = 0;
		  $order_old_code = '';
		  $global_queries = array();
		  $uploaded_orders = array();

		  $fp = fopen($_FILES['amazon_file']['tmp_name'], 'r');
		  while (($cell = fgetcsv($fp, 10000, "\t")) !== FALSE) {
			if ($upload_to_shop==15) {
//			Order_id	order_date	acceptby_date	shipby_date	buyer_name	buyer_email	shipto_name	shipto_company	shipto_add_line1	shipto_add_line2	shipto_add_line3	shipto_city	shipto_stateorregion	shipto_postalcode	shipto_country	shipto_countrycode	sku	ListingNumber	title	author	ListingPrice	netprice	shippingallowance	commission	amountdue	BuyerPaid	shippingmethod	order_source	order_fulfill	carrier_name	tracking_number
			  list($order_item_id, $purchase_date, , , $buyer_name, $buyer_email, $recipient_name, $recipient_company, $ship_address_1, $ship_address_2, $ship_address_3, $ship_city, $ship_state, $ship_postal_code, $ship_country, , $sku, , $product_name, $author_name, $item_price, , , , , $total_price) = $cell;
			 // $order_code = substr($order_item_id, 0, -3);
			  $order_code = $order_item_id;
			  if (tep_not_null($author_name)) $product_name .= ' by ' . $author_name;
			  $buyer_phone_number = '';
			  $quantity_purchased = 1;
			  $curr = 'USD';
			  $item_tax = 0;
			  $shipping_tax = 0;
			  $shipping_price = $total_price - $item_price;
			  $ship_phone_number = '';
			} else {
			  list($order_code, $order_item_id, $purchase_date, $payments_date, $buyer_email, $buyer_name, $buyer_phone_number, $sku, $product_name, $quantity_purchased, $curr, $item_price, $item_tax, $shipping_price, $shipping_tax, $ship_service_level, $recipient_name, $ship_address_1, $ship_address_2, $ship_address_3, $ship_city, $ship_state, $ship_postal_code, $ship_country, $ship_phone_number) = $cell;
			}
//			if ((int)$sku > 0) { echo 'order_code - ' . $order_code . '; order_item_id = ' . $order_item_id . '; purchase_date = ' . $purchase_date . '; payments_date = ' . $payments_date . '; buyer_email = ' . $buyer_email . '; buyer_name = ' . $buyer_name . '; buyer_phone_number = ' . $buyer_phone_number . '; sku = ' . $sku . '; product_name = ' . $product_name . '; quantity_purchased = ' . $quantity_purchased . '; curr = ' . $curr . '; item_price = ' . $item_price . '; item_tax = ' . $item_tax . '; shipping_price = ' . $shipping_price . '; shipping_tax = ' . $shipping_tax . '; ship_service_level = ' . $ship_service_level . '; recipient_name = ' . $recipient_name . '; ship_address_1 = ' . $ship_address_1 . '; ship_address_2 = ' . $ship_address_2 . '; ship_address_3 = ' . $ship_address_3 . '; ship_city= ' . $ship_city . '; ship_date = ' . $ship_state . '; ship_postal_code = ' . $ship_postal_code . '; ship_country = ' . $ship_country . '; ship_phone_number = ' . $ship_phone_number . '<br>'; die; }
			if ((int)$sku > 0 && in_array($order_code, $orders_to_upload)) {
			  $currency_value = $currencies->get_value($curr);
			  if ($order_code!=$order_old_code) {
				if (tep_not_null($order_old_code)) {
				  $global_queries = array_merge($global_queries, $sql_queries);
				}

				$ship_address = trim($ship_address_1 . ' ' . $ship_address_2 . '  ' . $ship_address_3);
				if (strpos($purchase_date, '+')!==false) $purchase_date = str_replace('T', ' ', substr($purchase_date, 0, strrpos($purchase_date, '+')));
				elseif (strpos($purchase_date, 'T')!==false) $purchase_date = str_replace('T', ' ', substr($purchase_date, 0, strrpos($purchase_date, '-')));

				$sql_data_array = array(
						  'orders_code' => $order_code,
						  'customers_id' => 0,
                          'customers_name' => $buyer_name,
                          'customers_street_address' => $ship_address,
                          'customers_suburb' => '',
                          'customers_city' => $ship_city,
                          'customers_postcode' => $ship_postal_code,
                          'customers_state' => $ship_state,
                          'customers_country' => $ship_country,
                          'customers_telephone' => $buyer_phone_number,
                          'customers_email_address' => $buyer_email,
                          'customers_address_format_id' => 5,
                          'delivery_name' => $recipient_name,
                          'delivery_company' => '',
                          'delivery_street_address' => $ship_address,
                          'delivery_suburb' => '',
                          'delivery_city' => $ship_city,
                          'delivery_postcode' => $ship_postal_code,
                          'delivery_state' => $ship_state,
                          'delivery_country' => $ship_country,
                          'delivery_telephone' => $ship_phone_number,
                          'delivery_address_format_id' => 5,
                          'billing_name' => $buyer_name,
                          'billing_company' => '',
                          'billing_street_address' => $ship_address,
                          'billing_suburb' => '',
                          'billing_city' => $ship_city,
                          'billing_postcode' => $ship_postal_code,
                          'billing_state' => $ship_state,
                          'billing_country' => $ship_country,
                          'billing_telephone' => $buyer_phone_number,
                          'billing_address_format_id' => 5,
                          'payment_method' => 'Bank card',
                          'date_purchased' => $purchase_date,
                          'orders_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => $curr,
                          'currency_value' => $currency_value,
                          'delivery_transfer' => '',
						  'orders_ssl_enabled' => 1,
                          'shops_id' => $upload_to_shop);
//				echo '<pre>' . print_r($sql_data_array, true); die;
				tep_db_perform(TABLE_ORDERS, $sql_data_array);
				$insert_id = tep_db_insert_id();

				$uploaded_orders[] = $insert_id;

				tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$insert_id . "', '" . DEFAULT_ORDERS_STATUS_ID . "', '" . tep_db_input($purchase_date) . "', '0', '')");
				$ot_subtotal = 0;
				$ot_shipping = 0;
			  }

			  $products_price = ($item_price + $item_tax) / $currency_value;
			  $products_price = str_replace(',', '.', $products_price);
			  if ($upload_to_shop==15) $product_info_query = tep_db_query("select products_id, products_model, products_code, products_weight, manufacturers_id, products_year, products_types_id from " . TABLE_PRODUCTS . " where products_code = 'bbk" . sprintf('%010d', (int)$sku) . "' and products_types_id = '1'");
			  else $product_info_query = tep_db_query("select products_id, products_model, products_code, products_weight, manufacturers_id, products_year, products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$sku . "'");
			  $product_info = tep_db_fetch_array($product_info_query);
			  $manufacturers_name = tep_get_manufacturer_name($product_info['manufacturers_id'], 1);
			  $sql_data_array = array('orders_id' => $insert_id,
									  'products_id' => $product_info['products_id'],
									  'products_model' => $product_info['products_model'],
									  'products_code' => $product_info['products_code'],
									  'products_seller_code' => $order_item_id,
									  'products_weight' => $product_info['products_weight'],
									  'manufacturers_name' => $manufacturers_name,
									  'products_year' => $product_info['products_year'],
									  'products_types_id' => $product_info['products_types_id'],
									  'products_name' => $product_name,
									  'products_price' => $products_price,
									  'final_price' => $products_price,
									  'products_tax' => 0,
									  'products_quantity' => $quantity_purchased);
			  tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

			  $ot_subtotal += $products_price * $quantity_purchased;
			  $ot_shipping += ($shipping_price + $shipping_tax) / $currency_value;

			  $ot_total = $ot_subtotal + $ot_shipping;
			  $order_totals = array();
			  $order_totals[] = array('title' => 'Subtotal:',
									  'text' => $currencies->format($ot_subtotal, true, $curr, $currency_value),
									  'value' => str_replace(',', '.', $ot_subtotal),
									  'class' => 'ot_subtotal',
									  'sort_order' => '1');
			  $order_totals[] = array('title' => 'Delivery by postal service',
									  'text' => $currencies->format($ot_shipping, true, $curr, $currency_value),
									  'value' => str_replace(',', '.', $ot_shipping),
									  'class' => 'ot_shipping',
									  'sort_order' => '3');
			  $order_totals[] = array('title' => 'Total:',
									  'text' => $currencies->format($ot_total, true, $curr, $currency_value),
									  'value' => str_replace(',', '.', $ot_total),
									  'class' => 'ot_total',
									  'sort_order' => '4');

			  $sql_queries = array();
			  reset($order_totals);
			  while (list(, $order_total) = each($order_totals)) {
				$sql_queries[] = "insert into " . TABLE_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . (int)$insert_id . "', '" . tep_db_input($order_total['title']) . "', '" . tep_db_input($order_total['text']) . "', '" . tep_db_input($order_total['value']) . "', '" . tep_db_input($order_total['class']) . "', '" . tep_db_input($order_total['sort_order']) . "')";
				if ($order_total['class']=='ot_shipping') {
				  $sql_queries[] = "update " . TABLE_ORDERS . " set payment_method_class = 'paypal_direct', delivery_method = '" . tep_db_input($order_total['title']) . "', delivery_method_class = 'foreign' where orders_id = '" . (int)$insert_id . "'";
				} elseif ($order_total['class']=='ot_total') {
				  $sql_queries[] = "update " . TABLE_ORDERS . " set orders_total = '" . tep_db_input($order_total['value']) . "' where orders_id = '" . (int)$insert_id . "'";
				}
			  }
			  $order_old_code = $order_code;
			}
		  }
		  $global_queries = array_merge($global_queries, $sql_queries);
		  reset($global_queries);
		  while (list(, $sql_query) = each($global_queries)) {
			if (tep_not_null($sql_query)) tep_db_query($sql_query);
		  }
		  fclose($fp);

		  if (sizeof($uploaded_orders) > 0) {
			$orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id in ('" . implode("', '", $uploaded_orders) . "')");
			while ($orders = tep_db_fetch_array($orders_query)) {
			  tep_upload_order($orders['orders_id']);
			  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS . " select * from " . TABLE_ORDERS . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_TOTAL . " select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_STATUS_HISTORY . " select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			  tep_db_query("insert into " . TABLE_ARCHIVE_ORDERS_PRODUCTS . " select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			}
		  }
		}
		tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))));
		break;
	  case 'download':
		tep_set_time_limit(600);

		$status = tep_db_prepare_input($HTTP_POST_VARS['status']);
		$order_string = '';
		$orders_query_raw = "select distinct o.orders_id from " . TABLE_ORDERS . " o where 1";
	    if (tep_not_null($HTTP_GET_VARS['oID'])) {
	      $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
	      $orders_query_raw .= " and o.orders_id = '" . (int)$oID . "'";
	    }
	    if (tep_not_null($HTTP_GET_VARS['cID'])) {
	      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
	      $orders_query_raw .= " and o.customers_id = '" . (int)$cID . "'";
	    }
		if (tep_not_null($HTTP_GET_VARS['status'])) {
	      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
	      $orders_query_raw .= " and o.orders_status = '" . (int)$status . "'";
	    }
		if (tep_not_null($HTTP_GET_VARS['shop'])) {
	      $shop = tep_db_prepare_input($HTTP_GET_VARS['shop']);
	      $orders_query_raw .= " and o.shops_id = '" . (int)$shop . "'";
	    }
		if (tep_not_null($HTTP_GET_VARS['date'])) {
	      $date_purchased = tep_db_prepare_input($HTTP_GET_VARS['date']);
		  if (preg_match('/^(\d{1,2})\.(\d{1,2})\.?(\d*)$/', $date_purchased, $regs)) {
			if (empty($regs[3])) $regs[3] = date('Y');
			$date_purchased = $regs[3] . '-' . sprintf('%02d', $regs[2]) . '-' . sprintf('%02d', $regs[1]);
	    	$orders_query_raw .= " and date_format(o.date_purchased, '%Y-%m-%d') = '" . tep_db_input($date_purchased) . "'";
		  }
	    }
		if (tep_not_null($HTTP_GET_VARS['type'])) {
	      $type = tep_db_prepare_input($HTTP_GET_VARS['type']);
	      $orders_query_raw .= " and op.products_types_id = '" . (int)$type . "' and op.orders_id = o.orders_id";
		  $orders_query_raw = str_replace("from " . TABLE_ORDERS . " o", "from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op", $orders_query_raw);
	    }
		if (tep_not_null($HTTP_GET_VARS['search'])) {
	      $search = tep_db_prepare_input($HTTP_GET_VARS['search']);
		  $fields = array('o.customers_name', 'o.customers_company', 'o.customers_company', 'o.customers_street_address', 'o.customers_suburb', 'o.customers_city', 'o.customers_postcode', 'o.customers_state', 'o.customers_country', 'o.customers_telephone', 'o.customers_email_address', 'o.delivery_name', 'o.delivery_company', 'o.delivery_street_address', 'o.delivery_suburb', 'o.delivery_city', 'o.delivery_postcode', 'o.delivery_state', 'o.delivery_country', 'o.delivery_telephone', 'o.billing_name', 'o.billing_company', 'o.billing_street_address', 'o.billing_suburb', 'o.billing_city', 'o.billing_postcode', 'o.billing_state', 'o.billing_country', 'o.billing_telephone');
		  $orders_query_array = array();
		  reset($fields);
		  while (list(, $field) = each($fields)) {
			$orders_query_array[] = $field . " like '%" . tep_db_input(str_replace(' ', "%' and " . $field . " like '%", $search)) . "%'";
		  }
		  $orders_query_raw .= " and (" . implode(" or ", $orders_query_array) . ")";
	    }
		if (sizeof($allowed_shops_array) > 0) {
		  $orders_query_raw .= " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')";
		}
		if ((int)$HTTP_POST_VARS['days'] > 0) {
		  $days = (int)$HTTP_POST_VARS['days'];
		  $orders_query_raw .= " and date_format(o.date_purchased, '%Y-%m-%d') >= '" . date('Y-m-d', time()-60*60*24*$days) . "'";
		}
		$orders_query_raw .= " order by o.orders_id desc";
		$orders_query = tep_db_query($orders_query_raw);
		if (tep_db_num_rows($orders_query) > 0) {
		  header('Expires: Mon, 26 Nov 1962 00:00:00 GMT');
		  header('Last-Modified: ' . gmdate('D,d M Y H:i:s') . ' GMT');
		  header('Pragma: no-cache');
		  header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		  header('Content-Description: File Transfer');
		  header('Content-Type: application/octet-stream');
		  header('Content-Disposition: attachment; filename=' . (tep_not_null($HTTP_GET_VARS['oID']) ? 'order_' . $HTTP_GET_VARS['oID'] : 'orders') . '.csv');

		  $out = fopen('php://output', 'w');

		  if (DOMAIN_ZONE=='ua') $order_csv_array = array('№ заказа', 'Дата заказа', 'Код заказчика', 'ФИО заказчика', 'Адрес заказчика', 'Телефонный код города', 'Телефон заказчика', 'Книга', 'Количество', 'Способ доставки', 'Город Доставки', 'Комментарии заказчика', 'Способ оплаты', 'Ваша Стоимость закупки книги у Ваших поставщиков, рос.руб.', 'в т.ч.НДС, рос.руб.', 'в т.ч. НДС, %', 'Цена книги для ЗАКАЗЧИКА, грн.', 'Цена книги для ЗАКАЗЧИКА со СКИДКОЙ, грн.', 'Цена книги для ЗАКАЗЧИКА, рос.руб.', 'Цена книги для ЗАКАЗЧИКА со СКИДКОЙ, рос.руб.', 'Курс рубля к гривне', 'Стоимость доставки, грн.', 'Дата связи с клиентом', 'Дата отправки заказа из Москвы', 'Дата прихода заказа в Донецк', 'Дата отправки заказа клиенту', '№ квитанции при отправке заказа почтой', 'Дата получения денег (налож. платеж)', 'Тип товара', 'ISBN', 'Наименование', 'Автор', 'Издательство', 'Серия', 'Год издания', 'Обложка', 'Формат', 'Кол-во страниц', 'Вес', 'Идентификатор клиента', 'E-mail клиента', 'Улица/дом/квартира', 'Населенный пункт', 'Район', 'Область', 'Индекс');
		  else $order_csv_array = array('Заказ', 'Клиент', 'Телефон', 'Страна', 'Индекс', 'Регион', 'Город', 'Адрес доставки', 'Способ доставки', 'Цена доставки', 'Способ оплаты', 'Статус', 'Наименование', 'ISBN', 'Издательство', 'Кол-во', 'Цена', 'Цена, руб.', 'Заказ итого', 'Валюта', 'Курс');
		  fputcsv($out, $order_csv_array, ';', '"');
		  while ($orders = tep_db_fetch_array($orders_query)) {
			$order = new order($orders['orders_id']);

			if ($order->info['currency_value'] > 10) $round_to = 3;
			elseif ($order->info['currency_value'] < 0.1) $round_to = 0;
			else $round_to = 1;

			$delivery_method = '';
			$delivery_price = 0;
			$order_total_price = 0;
			reset($order->totals);
			$order_discount = 0;
			$order_discount_value = 0;
			while (list(, $total_info) = each($order->totals)) {
			  if ($total_info['class']=='ot_shipping') {
				$delivery_price = tep_round($total_info['value'] * $order->info['currency_value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
				$delivery_price = str_replace('.', ',', $delivery_price);
				$delivery_method = trim($total_info['title']);
				if (substr($delivery_method, -1)==':') $delivery_method = substr($delivery_method, 0, -1);
			  } elseif ($total_info['class']=='ot_total') {
				$order_total_price = tep_round($total_info['value'] * $order->info['currency_value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
			  } elseif ($total_info['value'] < 0) {
				$order_discount_value = tep_round(abs($total_info['value']) * $order->info['currency_value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
			  }
			}
			if ($order_discount_value > 0) {
			  $order_discount = tep_round($order_discount_value/$order_total_price, 2);
			}

			$comments_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$orders['orders_id'] . "' order by orders_status_history_id limit 1");
			$comments_array = tep_db_fetch_array($comments_query);
			if (!is_array($comments_array)) $comments_array = array();
			$comments = $comments_array['comments'];

			$status_info_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$order->info['orders_status'] . "' and language_id = '" . (int)$languages_id . "'");
			$status_info = tep_db_fetch_array($status_info_query);

			// Улица, Дом, Квартира, Населенный пункт, Район, Область, Индекс
			$delivery_address = '';
			if (tep_not_null($order->delivery['street_address'])) $delivery_address .= $order->delivery['street_address'] . ', ';
			if (tep_not_null($order->delivery['city'])) $delivery_address .= $order->delivery['city'] . ', ';
			if (tep_not_null($order->delivery['suburb'])) $delivery_address .= $order->delivery['suburb'] . ', ';
			if (tep_not_null($order->delivery['state']) && $order->delivery['state']!=$order->delivery['city']) $delivery_address .= $order->delivery['state'] . ', ';
			if (tep_not_null($order->delivery['postcode'])) $delivery_address .= $order->delivery['postcode'] . ', ';
			if (tep_not_null($delivery_address)) $delivery_address = substr($delivery_address, 0, -2);

			$currency_value = str_replace('.', ',', $order->info['currency_value']);

			$total_qty_query = tep_db_query("select sum(products_quantity) as total_qty from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "'");
			$total_qty_array = tep_db_fetch_array($total_qty_query);
			$total_qty = $total_qty_array['total_qty'];

			reset($order->products);
			while (list(, $product_info) = each($order->products)) {
			  $product_array_query = tep_db_query("select products_model, products_name, authors_name, manufacturers_name, series_name, products_covers_name, products_formats_name, products_pages_count, products_year, products_weight from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$product_info['id'] . "'");
			  $product_array = tep_db_fetch_array($product_array_query);

			  $product_price = tep_round($product_info['final_price'] * $order->info['currency_value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
			  $product_price = str_replace('.', ',', $product_price);
			  $product_price_discount = tep_round($product_price * (1 - $order_discount), $currencies->currencies[$order->info['currency']]['decimal_places']);
			  $product_price_discount = str_replace('.', ',', $product_price_discount);
			  $original_price = tep_round($product_info['final_price'], $currencies->currencies[$order->info['currency']]['decimal_places']);
			  $original_price = str_replace('.', ',', $original_price);
			  $original_price_discount = tep_round($original_price * (1 - $order_discount), $currencies->currencies[$order->info['currency']]['decimal_places']);
			  $original_price_discount = str_replace('.', ',', $original_price_discount);

			  if (DOMAIN_ZONE=='ua') {
				$product_delivery_price = tep_round($product_info['qty']*$delivery_price/$total_qty, 2);
				$product_delivery_price = str_replace('.', ',', $product_delivery_price);
				$delivery_name = preg_replace('/\s+/', ' ', trim($order->delivery['name']));
//				$delivery_name = preg_replace('/([^\s]+)\s([^\s]+)\s?(.*)/', '$3 $1 $2', $delivery_name);
				list($fname, $mname, $lname) = explode(' ', $delivery_name);
				if ($lname=='') {
				  $lname = $mname;
				  $mname = '';
				}
				$delivery_name = preg_replace('/\s+/', ' ', trim($lname . ' ' . $fname . ' ' . $mname));

				if ($product_info['type']=='1') {
				  $product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$product_info['type'] . "' and language_id = '" . (int)$languages_id . "'");
				  $product_type_info = tep_db_fetch_array($product_type_info_query);
				  $product_type_name = $product_type_info['products_types_name'];
				} else {
				  $category_info_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_info['id'] . "' order by categories_id desc limit 1");
				  $category_info = tep_db_fetch_array($category_info_query);
				  $product_type_name = tep_get_category_name($category_info['categories_id'], $languages_id);
				}

				$order_csv_array = array($orders['orders_id'], tep_date_short($order->info['date_purchased']), $order->customer['id'], $delivery_name, $delivery_address, '', '&nbsp;' . (tep_not_null($order->delivery['telephone']) ? $order->delivery['telephone'] : $order->customer['telephone']) . '&nbsp;', $product_info['name'], $product_info['qty'] . '&nbsp;', $delivery_method, $order->delivery['city'], $comments, $order->info['payment_method'], '', '', '', $product_price, $product_price_discount, $original_price, $original_price_discount, $currency_value, $product_delivery_price, '', '', '', '', '', '', $product_type_name, $product_array['products_model'], $product_array['products_name'], $product_array['authors_name'], $product_array['manufacturers_name'], $product_array['series_name'], $product_array['products_year'], $product_array['products_covers_name'], $product_array['products_formats_name'], $product_array['products_pages_count'], str_replace('.', ',', $product_array['products_weight']), $order->customer['id'], $order->customer['email_address'], $order->delivery['street_address'], $order->delivery['city'], $order->delivery['suburb'], $order->delivery['state'], '&nbsp;' . $order->delivery['postcode'] . '&nbsp;');
			  } else {
				$order_csv_array = array($orders['orders_id'], $order->delivery['name'], '&nbsp;' . (tep_not_null($order->delivery['telephone']) ? $order->delivery['telephone'] : $order->customer['telephone']) . '&nbsp;', $order->delivery['country'], '&nbsp;' . $order->delivery['postcode'] . '&nbsp;', $order->delivery['state'], $order->delivery['city'], $order->delivery['street_address'], $delivery_method, $delivery_price, $order->info['payment_method'], $status_info['orders_status_name'], $product_info['name'], $product_info['model'], $product_array['manufacturers_name'], $product_info['qty'] . '&nbsp;', $product_price, tep_round($product_info['final_price'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']), $order_total_price, $currencies->currencies[$order->info['currency']]['title'], tep_round(1/$order->info['currency_value'], 4));
			  }
			  reset($order_csv_array);
			  while (list($k, $order_csv) = each($order_csv_array)) {
				$order_csv_array[$k] = html_entity_decode($order_csv, ENT_QUOTES);
			  }
			  fputcsv($out, $order_csv_array, ';', '"');
			}

//			if (DOMAIN_ZONE!='ua') {
//			  $order_csv_array = array('');
//			  fputcsv($out, $order_csv_array, ';');
//			}
		  }
		  fclose($out);
		  die();
		} else {
		  $messageStack->add_session(ERROR_NO_ORDERS_FOUND, 'error');
		  tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))));
		}
		break;
	  case 'update_order':
		$oID = tep_db_prepare_input($HTTP_POST_VARS['orders_id']);
		$order = new order($oID);
//		echo '<pre>' . print_r($order, true) . '</pre>';
//		die();
		$status = tep_db_prepare_input($HTTP_POST_VARS['status']);
        $delivery_date = tep_db_prepare_input($HTTP_POST_VARS['delivery_date']);
		if (tep_not_null($delivery_date)) {
		  preg_match_all('/\w+/', DATE_FORMAT, $regs1);
		  $regs = array_map("strtolower", $regs1[0]);
		  if ($regs[0]=='d') $day = preg_replace('/^(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[1]=='d') $day = preg_replace('/^\d+\D(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[2]=='d') $day = preg_replace('/.*\D(\d+)$/', '$1', $delivery_date);
		  if ($regs[0]=='m') $month = preg_replace('/^(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[1]=='m') $month = preg_replace('/^\d+\D(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[2]=='m') $month = preg_replace('/.*\D(\d+)$/', '$1', $delivery_date);
		  if ($regs[0]=='y') $year = preg_replace('/^(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[1]=='y') $year = preg_replace('/^\d+\D(\d+)\D.*/', '$1', $delivery_date);
		  if ($regs[2]=='y') $year = preg_replace('/.*\D(\d+)$/', '$1', $delivery_date);

		  $delivery_date = $year . '-' . $month . '-' . $day;
		}

        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased, shops_id" . (tep_db_field_exists(TABLE_ORDERS, 'delivery_date') ? ", date_format(delivery_date, '%Y-%m-%d') as delivery_date" : "") . " from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);
		$shop_info_query = tep_db_query("select if(shops_ssl='', shops_url, shops_ssl) as orders_domain, shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$check_status['shops_id'] . "'");
		$shop_info = tep_db_fetch_array($shop_info_query);
		if (!is_array($shop_info)) $shop_info = array();
		$check_status = array_merge($check_status, $shop_info);

		if (tep_not_null($order->info['payment_method']) && $update_info_payment_method==$order->info['payment_method_class']) {
		  $payment_method = $order->info['payment_method'];
		} else {
		  $payment_method = $installed_payment[$update_info_payment_method];
		}

		$old_shipping_method_class = $order->delivery['delivery_method_class'];
		$old_shipping_method = $order->delivery['delivery_method'];

		$sql_data_array = array('customers_name' => tep_db_prepare_input(stripslashes($update_customer_name)),
								'customers_company' => tep_db_prepare_input(stripslashes($update_customer_company)),
								'customers_company_full_name' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_full'])),
								'customers_company_name' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['update_customer_company'])),
								'customers_company_inn' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_inn'])),
								'customers_company_kpp' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_kpp'])),
								'customers_company_ogrn' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_ogrn'])),
								'customers_company_okpo' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okpo'])),
								'customers_company_okogu' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okogu'])),
								'customers_company_okato' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okato'])),
								'customers_company_okved' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okved'])),
								'customers_company_okfs' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okfs'])),
								'customers_company_okopf' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_okopf'])),
								'customers_company_address_corporate' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_address_corporate'])),
								'customers_company_address_post' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_address_post'])),
								'customers_company_telephone' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_telephone'])),
								'customers_company_fax' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_fax'])),
								'customers_company_bank' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_bank'])),
								'customers_company_rs' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_rs'])),
								'customers_company_ks' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_ks'])),
								'customers_company_bik' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_bik'])),
								'customers_company_general' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_general'])),
								'customers_company_financial' => tep_db_prepare_input(stripslashes($HTTP_POST_VARS['company_financial'])),
'customers_street_address' => tep_db_prepare_input(stripslashes($update_customer_street_address)),
								'customers_suburb' => tep_db_prepare_input(stripslashes($update_customer_suburb)),
								'customers_city' => tep_db_prepare_input(stripslashes($update_customer_city)),
								'customers_state' => tep_db_prepare_input(stripslashes($update_customer_state)),
								'customers_postcode' => tep_db_prepare_input($update_customer_postcode),
								'customers_country' => tep_db_prepare_input(stripslashes($update_customer_country)),
								'customers_telephone' => tep_db_prepare_input($update_customer_telephone),
								'customers_email_address' => tep_db_prepare_input($update_customer_email_address),
								'billing_name' => tep_db_prepare_input(stripslashes($update_billing_name)),
								'billing_company' => tep_db_prepare_input(stripslashes($update_billing_company)),
								'billing_street_address' => tep_db_prepare_input(stripslashes($update_billing_street_address)),
								'billing_suburb' => tep_db_prepare_input(stripslashes($update_billing_suburb)),
								'billing_city' => tep_db_prepare_input(stripslashes($update_billing_city)),
								'billing_state' => tep_db_prepare_input(stripslashes($update_billing_state)),
								'billing_postcode' => tep_db_prepare_input($update_billing_postcode),
								'billing_country' => tep_db_input(stripslashes($update_billing_country)),
								'billing_telephone' => tep_db_prepare_input($update_billing_telephone),
								'delivery_name' => tep_db_prepare_input(stripslashes($update_delivery_name)),
								'delivery_company' => tep_db_prepare_input(stripslashes($update_delivery_company)),
								'delivery_street_address' => tep_db_prepare_input(stripslashes($update_delivery_street_address)),
								'delivery_suburb' => tep_db_prepare_input(stripslashes($update_delivery_suburb)),
								'delivery_city' => tep_db_prepare_input(stripslashes($update_delivery_city)),
								'delivery_state' => tep_db_prepare_input(stripslashes($update_delivery_state)),
								'delivery_postcode' => tep_db_prepare_input($update_delivery_postcode),
								'delivery_country' => tep_db_prepare_input(stripslashes($update_delivery_country)),
								'delivery_telephone' => tep_db_prepare_input($update_delivery_telephone),
								'delivery_self_address' => tep_db_prepare_input($self_delivery_addresses_array[$update_delivery_self_address_id]),
								'delivery_self_address_id' => (int)$update_delivery_self_address_id,
								'payment_method' => tep_db_prepare_input($payment_method),
								'payment_method_class' => tep_db_prepare_input($update_info_payment_method),
								'cc_type' => tep_db_prepare_input($update_info_cc_type),
								'cc_owner' => tep_db_prepare_input($update_info_cc_owner),
								'cc_expires' => tep_db_prepare_input($update_info_cc_expires),
								'orders_status' => tep_db_prepare_input($status),
								'last_modified' => 'now()');

		if (tep_db_field_exists(TABLE_ORDERS, 'customers_referer')) {
		  $sql_data_array['customers_referer'] = tep_db_prepare_input($HTTP_POST_VARS['referer']);
		}
		if (tep_db_field_exists(TABLE_ORDERS, 'payer_requisites')) {
		  $sql_data_array['payer_requisites'] = tep_db_prepare_input($HTTP_POST_VARS['payer_requisites']);
		}
		if (tep_db_field_exists(TABLE_ORDERS, 'delivery_date')) {
		  $sql_data_array['delivery_date'] = tep_db_prepare_input($delivery_date);
		}
		if (tep_db_field_exists(TABLE_ORDERS, 'delivery_time')) {
		  $sql_data_array['delivery_time'] = tep_db_prepare_input($delivery_time);
		}
		if (tep_db_field_exists(TABLE_ORDERS, 'comments')) {
		  $sql_data_array['comments'] = tep_db_prepare_input($comments);
		}
		// Update Order Info
		if (substr($update_info_cc_number, 0, 8) != '(Last 4)') {
		  $sql_data_array['cc_number'] = tep_db_prepare_input($update_info_cc_number);
		}

		tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . (int)$oID . "'");
		$order_updated = true;

		$customer_notified = '0';
		// Update Status History & Email Customer if Necessary
		if ($check_status['orders_status'] != $status || tep_not_null($comments)) {
		  // Notify Customer
		  if (isset($HTTP_POST_VARS['notify']) && ($HTTP_POST_VARS['notify'] == 'on')) {
			$notify_comments = '';
			if (isset($HTTP_POST_VARS['notify_comments']) && ($HTTP_POST_VARS['notify_comments'] == 'on') && tep_not_null($comments)) {
			  $notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . "\n" . EMAIL_SEPARATOR . "\n" . $comments . "\n\n";
			}

			tep_db_select_db($check_status['shops_database']);
			$store_name_info_query = tep_db_query("select configuration_value as store_name from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
			$store_name_info = tep_db_fetch_array($store_name_info_query);
			tep_db_select_db(DB_DATABASE);

			$order_info_link = tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL');
			$order_info_link = preg_replace('/^https?:\/\/[^\/]+\/(.*)$/i', '$1', $order_info_link);
			$order_info_link = $check_status['orders_domain'] . '/' . $order_info_link;
			if ($order->info['enabled_ssl']=='0') $order_info_link = str_replace('https://', 'http://', $order_info_link);
//			$email = EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_short($check_status['date_purchased']) . "\n\n" . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]) . (tep_not_null($orders_status_descriptions_array[$status]) ? ' (' . $orders_status_descriptions_array[$status] . ')' : '') . "\n\n" . $notify_comments . "\n\n" . EMAIL_TEXT_INVOICE_URL . ' ' . $order_info_link . "\n\n" . EMAIL_TEXT_PS;
			$email = $store_name_info['store_name'] . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . sprintf(EMAIL_TEXT_INVOICE_URL, $order_info_link, $order_info_link) . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . EMAIL_TEXT_ORDER_CHANGED . "\n" . EMAIL_SEPARATOR . "\n" . ($check_status['orders_status']!=$status ? sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]) . "\n\n" : '') . $notify_comments . "\n" . EMAIL_TEXT_PS;

			tep_mail($check_status['customers_name'], $check_status['customers_email_address'], sprintf(EMAIL_TEXT_SUBJECT, $store_name_info['store_name'], $oID), $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

			$customer_notified = '1';
		  }
		}

        $operator = tep_db_prepare_input($REMOTE_USER);
		if ($check_status['orders_status'] != $status || tep_not_null($comments) || tep_not_null($admin_comments)) {
		  tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_comments, operator) values ('" . tep_db_input($oID) . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_prepare_input($comments)  . "', '" . tep_db_prepare_input($admin_comments)  . "', '" . tep_db_prepare_input($operator) . "')");
		}

		// Update Products
		$RunningSubTotal = 0;
		$RunningTax = 0;

        // CWS EDIT (start) -- Check for existence of subtotals...
        // Do pre-check for subtotal field existence
		$ot_subtotal_found = false;
    	foreach($update_totals as $total_details) {
		  extract($total_details, EXTR_PREFIX_ALL, "ot");
		  if ($ot_class == "ot_subtotal") {
			$ot_subtotal_found = true;
    		break;
		  }
		}
		// CWS EDIT (end) -- Check for existence of subtotals...

		foreach($update_products as $orders_products_id => $products_details) {
		  // Update orders_products Table

		  if ($products_details['qty'] > 0) {
			$orders_products_query = tep_db_query("select products_id, products_tax, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
			$orders_products_array = tep_db_fetch_array($orders_products_query);
			$products_details['price'] = str_replace(',', '.', $products_details['price']);
//			if ($order->info['currency']!=DEFAULT_CURRENCY) {
			  $products_details['price'] = str_replace(',', '.', ($products_details['price'] / $order->info['currency_value']));
//			}
			$final_price = str_replace(',', '.', tep_add_tax($products_details['price'], $orders_products_array['products_tax']));
			tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set products_price = '" . $products_details['price'] . "', final_price = '" . $final_price . "', products_quantity = '" . $products_details['qty'] . "' where orders_products_id = '" . (int)$orders_products_id . "'");
			if ($orders_products_array['products_quantity'] != $products_details['qty']) {
			  $order_products_array[] = array('products_id' => $orders_products_array['products_id'], 'action' => 'update', 'quantity' => $products_details['qty']);
			}

			// Update Tax and Subtotals
//			$orders_products_query = tep_db_query("select products_id, products_tax, products_quantity, products_price, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
//			$orders_products_array = tep_db_fetch_array($orders_products_query);
//			$RunningSubTotal += $orders_products_array["final_price"] * $products_details["qty"];
			$RunningSubTotal += $final_price * $products_details["qty"];
			$RunningTax += (($orders_products_array["products_tax"]/100) * ($products_details["qty"] * $orders_products_array["final_price"]));
		  } else {
			// 0 Quantity = Delete
			$orders_products_query = tep_db_query("select products_id from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
			$orders_products_array = tep_db_fetch_array($orders_products_query);
			tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
			$order_products_array[] = array('products_id' => $orders_products_array['products_id'], 'action' => 'delete');
		  }
		}

		// Shipping Tax
		foreach($update_totals as $total_index => $total_details) {
		  extract($total_details, EXTR_PREFIX_ALL, 'ot');
		  if ($ot_class == 'ot_shipping') {
			$RunningTax += (($AddShippingTax / 100) * $ot_value);
			if (tep_not_null($old_shipping_method) && $total_details['title']==$old_shipping_method_class) {
			  $shipping_method = $old_shipping_method;
			} else {
			  $shipping_method = $installed_shipping[$total_details['title']];
			}
			$update_totals[$total_index]['title'] = $shipping_method;
			tep_db_query("update " . TABLE_ORDERS . " set delivery_method = '" . tep_db_prepare_input($shipping_method) . "', delivery_method_class = '" . tep_db_prepare_input($total_details['title']) . "' where orders_id = '" . (int)$oID . "'");
		  }
		}

		// Update Totals
		$RunningTotal = 0;
		$sort_order = 0;

		// Do pre-check for Tax field existence
		$ot_tax_found = 0;
		foreach($update_totals as $total_details) {
		  extract($total_details, EXTR_PREFIX_ALL, "ot");
		  if ($ot_class == "ot_tax") {
			$ot_tax_found = 1;
			break;
		  }
		}

		foreach($update_totals as $total_index => $total_details) {
		  extract($total_details, EXTR_PREFIX_ALL, "ot");

		  if ( trim(strtolower($ot_title)) == "tax" || trim(strtolower($ot_title)) == "tax:" ) {
			if ($ot_class != "ot_tax" && $ot_tax_found == 0) {
			  // Inserting Tax
			  $ot_class = "ot_tax";
			  $ot_value = "x"; // This gets updated in the next step
			  $ot_tax_found = 1;
			}
		  }

		  if ( trim($ot_title) ) {
			$sort_order ++;

			$ot_value = str_replace(',', '.', $ot_value);

			// Update ot_subtotal, ot_tax, and ot_total classes
			if ($ot_class == "ot_subtotal") {
			  $ot_value = $RunningSubTotal;
			} elseif ($ot_class == "ot_tax") {
			  $ot_value = $RunningTax;
			  // echo "ot_value = $ot_value<br>\n";
			} elseif ($ot_class == "ot_total") {
			  $ot_value = $RunningTotal;
			  if ( !$ot_subtotal_found ) {
				// There was no subtotal on this order, lets add the running subtotal in.
				$ot_value = $ot_value + $RunningSubTotal;
			  }
			} else {
			  $ot_value = $ot_value / $order->info['currency_value'];
			}
			$ot_value = str_replace(',', '.', $ot_value);

//			$order = new order($oID);
			$ot_text = $currencies->format($ot_value, true, $order->info['currency'], $order->info['currency_value']);

			if ($ot_class == "ot_total") {
			  $current_sort_order = sizeof($update_totals);
			  $ot_text = '<strong>' . $ot_text . '</strong>';
			} else {
			  $current_sort_order = $sort_order;
			}

			if ($ot_total_id > 0) {
			  // In Database Already - Update
			  if (trim($ot_title)=='' && trim($ot_value)=='') {
				tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_total_id = '$ot_total_id'");
			  } else {
				tep_db_query("update " . TABLE_ORDERS_TOTAL . " set title = '$ot_title', text = '$ot_text', value = '$ot_value', sort_order = '$current_sort_order' where orders_total_id = '$ot_total_id'");
			  }
			} else {
			  // New Insert
			  tep_db_query("insert into " . TABLE_ORDERS_TOTAL . " set orders_id = '$oID', title = '$ot_title', text = '$ot_text', value = '$ot_value', class = '$ot_class', sort_order = '$current_sort_order'");
			  $ot_total_id = tep_db_insert_id();
			}
			if ($ot_class=='ot_custom' && $ot_value < 0) tep_db_query("update " . TABLE_ORDERS_TOTAL . " set class = 'ot_discount' where orders_id = '" . (int)$oID . "' and orders_total_id = '" . (int)$ot_total_id . "'");
			if ($ot_class=='ot_total') tep_db_query("update " . TABLE_ORDERS . " set orders_total = '" . tep_db_input($ot_value) . "' where orders_id = '" . (int)$oID . "'");

			$RunningTotal += $ot_value;
		  } elseif ($ot_total_id > 0) {
			// Delete Total Piece
			$Query = "delete from " . TABLE_ORDERS_TOTAL . " where orders_total_id = '$ot_total_id'";
			tep_db_query($Query);
		  }
		}

		if ($check_status['orders_status'] != $status && $status == PARTNERS_ORDERS_STATUS) {
		  $partner_info_query = tep_db_query("select partners_id, partners_comission from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
		  $partner_info = tep_db_fetch_array($partner_info_query);
		  if ((int)$partner_info['partners_id'] > 0) {
			$partners_balance_check_query = tep_db_query("select count(*) as total from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$partner_info['partners_id'] . "' and orders_id = '" . (int)$oID . "'");
			$partners_balance_check = tep_db_fetch_array($partners_balance_check_query);
			if ($partners_balance_check['total'] < 1) {
			  $order_subtotal_info_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = 'ot_subtotal'");
			  $order_subtotal_info = tep_db_fetch_array($order_subtotal_info_query);
			  tep_db_query("insert into " . TABLE_PARTNERS_BALANCE . " (date_added, partners_id, partners_balance_sum, orders_id, partners_balance_comments) values (now(), '" . (int)$partner_info['partners_id'] . "', '" . str_replace(',', '.', $partner_info['partners_comission']*$order_subtotal_info['value']) . "', '" . (int)$oID . "', '" . (float)($partner_info['partners_comission']*100) . "% от суммы заказа #" . (int)$oID . "')");
			}
		  }
		}

		if ($order_updated) {
		  $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
		}

		$new_date = '';
		if ($check_status['delivery_date']!=$delivery_date) {
		  $new_date = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '$3.$2.$1', $delivery_date);
		}

#		tep_order_products_updated($oID, $order_products_array, $new_date);

		tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=view'));
		break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        tep_remove_order($oID, $HTTP_POST_VARS['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
	  case 'add_products_confirm':
		$products = $HTTP_POST_VARS['qty'];
		$prices = $HTTP_POST_VARS['price'];
		$j = 0;
		$sum = 0;
		while (list($products_id, $products_qty) = each($products)) {
		  $prices[$products_id] = trim(str_replace(',', '.', $prices[$products_id]));
		  if ((int)$products_qty > 0 && (double)$prices[$products_id] > 0 && (int)$products_id > 0) {
			$prices[$products_id] = $prices[$products_id] / $order->info['currency_value'];
			$j ++;
			$sum += $prices[$products_id];
//			echo '<br>' . "\n$j. ",
			$sql = "insert into " . TABLE_ORDERS_PRODUCTS . " (orders_id, products_id, products_model, products_code, products_weight, products_name, products_price, final_price, products_quantity, products_year, products_types_id, manufacturers_name) select '" . (int)$oID . "', products_id, products_model, products_code, products_weight, if (authors_name<>'', concat_ws(': ', authors_name, products_name), products_name), '" . tep_db_input($prices[$products_id]) . "', '" . tep_db_input($prices[$products_id]) . "', '" . (int)$products_qty . "', products_year, products_types_id, manufacturers_name from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$products_id . "'";
			tep_db_query($sql);
			$orders_products_id = tep_db_insert_id();
		  }
		}

		// Calculate Tax and Sub-Totals
		$order = new order($oID);
		$RunningSubTotal = 0;
		$RunningTax = 0;

		for ($i=0; $i<sizeof($order->products); $i++) {
		  $RunningSubTotal += ($order->products[$i]['qty'] * $order->products[$i]['final_price']);
		  $RunningTax += (($order->products[$i]['tax'] / 100) * ($order->products[$i]['qty'] * $order->products[$i]['final_price']));
		}

		// Tax
		$Query = "update " . TABLE_ORDERS_TOTAL . " set text = '" . number_format($RunningTax, 2, '.', ',') . $order->info['currency'] . "', value = '" . str_replace(',', '.', $RunningTax) . "' where class='ot_tax' and orders_id = '" . (int)$oID . "'";
		tep_db_query($Query);

		// Sub-Total
		$Query = "update " . TABLE_ORDERS_TOTAL . " set text = '" . $currencies->format($RunningSubTotal + $RunningTax, true, $order->info['currency'], $order->info['currency_value']) . "', value = '" . str_replace(',', '.', $RunningSubTotal) . "' where class = 'ot_subtotal' and orders_id = '" . (int)$oID . "'";
		tep_db_query($Query);

		// Total
		$Query = "select sum(value) as total_value from " . TABLE_ORDERS_TOTAL . " where class <> 'ot_total' and orders_id = '" . (int)$oID . "'";
		$result = tep_db_query($Query);
		$row = tep_db_fetch_array($result);
		$Total = $row["total_value"];

		$Query = "update " . TABLE_ORDERS_TOTAL . " set text = '<strong>" . $currencies->format($Total, true, $order->info['currency'], $order->info['currency_value']) . "</strong>', value = '" . str_replace(',', '.', $Total) . "' where class = 'ot_total' and orders_id = '" . (int)$oID . "'";
		tep_db_query($Query);

		tep_db_query("update " . TABLE_ORDERS . " set orders_total = '" . tep_db_input(str_replace(',', '.', $Total)) . "' where orders_id = '" . (int)$oID . "'");

		$messageStack->add_session($j . ' products were added (' . $currencies->format($sum, true, $order->info['currency'], $order->info['currency_value']) . ')', 'success');

		tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit'));
		break;
    }
  }

  if (tep_not_null($HTTP_GET_VARS['oID']) && ($action=='view' || $action=='edit' || $action=='add_products') ) {
    $order = new order($HTTP_GET_VARS['oID']);

	if (is_array($installed_payment)) if (!in_array($order->info['payment_method_class'], array_keys($installed_payment))) $payments_array[] = array('id' => $order->info['payment_method_class'], 'text' => $order->info['payment_method']);

	reset($order->totals);
	while (list(, $order_total_info) = each($order->totals)) {
	  if ($order_total_info['class']=='ot_shipping') {
		$shipping_title = strip_tags($order_total_info['title']);
		if (!in_array($shipping_title, $installed_shipping)) $shipping_array[] = array('id' => $shipping_title, 'text' => $shipping_title);
		break;
	  }
	}

	$shop_info_query = tep_db_query("select if((s.shops_ssl<>'' and s.shops_ssl<>s.shops_url), s.shops_ssl, s.shops_url) as orders_domain from " . TABLE_SHOPS . " s, " . TABLE_ORDERS . " o where o.shops_id = s.shops_id and o.orders_id = '" . (int)$oID . "'");
	$shop_info = tep_db_fetch_array($shop_info_query);
	if (!is_array($shop_info)) $shop_info = array();

	$payment_link = '';
	$payment_email_footer = '';

	$payment_module_file = $order->info['payment_method_class'] . substr($PHP_SELF, strrpos($PHP_SELF, '.'));
	if (file_exists(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $payment_module_file)) {
	  include_once(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $payment_module_file);
	  include_once(DIR_FS_CATALOG_MODULES . 'payment/' . $payment_module_file);
	  $module = new $order->info['payment_method_class'];
	  $payment_email_footer = strip_tags($module->email_footer, '<a>');
	}
	if (strpos($payment_email_footer, 'advice.php')!==false || $order->customer['type']=='corporate') {
	  $payment_link = $shop_info['orders_domain'] . '/advice.php?order_id=' . $oID;
	  $customer_password_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$order->customer['id'] . "'");
	  $customer_password = tep_db_fetch_array($customer_password_query);
	  $payment_link .= (strpos($payment_link, '?')!==false ? '&' : '?') . 'email_address=' . $order->customer['email_address'] . '&password=' . $customer_password['customers_password'];
	  $payment_link = '<a href="' . $payment_link . '" target="_blank"><u>' . ENTRY_PRINTABLE . '</u></a>';
	}
  }

  if ($order_exists) {
	$order = new order($HTTP_GET_VARS['oID']);
	if ($order->info['currency_value'] > 10) $round_to = 3;
	elseif ($order->info['currency_value'] < 0.1) $round_to = 0;
	else $round_to = 1;
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  $oID = (int)$HTTP_GET_VARS['oID'];
  if (($action == 'view') && ($order_exists == true)) {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(HEADING_TITLE_1, $oID); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
	  <tr>
		<td><?php echo tep_draw_separator(); ?></td>
	  </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr valign="top">
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="100"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php $order_customer_array = $order->customer; unset($order_customer_array['telephone']); echo $order->customer['name'] . ', ' . tep_address_format($order->customer['format_id'], $order_customer_array, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
				<td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
<?php
	if ($order->customer['type']=='corporate') {
?>
              <tr valign="top">
                <td class="main" width="100"><strong><?php echo ENTRY_CUSTOMER_COMPANY; ?></strong><div id="customer_company" style="background: #EFEFEF; border: 1px solid #CCCCCC; position: absolute; display: none;">
			<div style="text-align: right;"><a href="#" onclick="document.getElementById('customer_company').style.display = 'none'; return false;"><?php echo tep_image(DIR_WS_IMAGES . 'cal_close_small.gif', ''); ?></a></div>
			<div style="width: 400px; margin: 5px 15px 0 15px;">
<?php
echo (tep_not_null($order->customer['company_full']) ? ENTRY_COMPANY_FULL . ' ' . $order->customer['company_full'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okved']) ? ENTRY_COMPANY_OKVED . ' ' . $order->customer['company_okved'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_inn']) ? ENTRY_COMPANY_INN . ' ' . $order->customer['company_inn'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_kpp']) ? ENTRY_COMPANY_KPP . ' ' . $order->customer['company_kpp'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okpo']) ? ENTRY_COMPANY_OKPO . ' ' . $order->customer['company_okpo'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okogu']) ? ENTRY_COMPANY_OKOGU . ' ' . $order->customer['company_okogu'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okato']) ? ENTRY_COMPANY_OKATO . ' ' . $order->customer['company_okato'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_ogrn']) ? ENTRY_COMPANY_OGRN . ' ' . $order->customer['company_ogrn'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okfs']) ? ENTRY_COMPANY_OKFS . ' ' . $order->customer['company_okfs'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okopf']) ? ENTRY_COMPANY_OKOPF . ' ' . $order->customer['company_okopf'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_address_corporate']) ? ENTRY_COMPANY_ADDRESS_CORPORATE . ' ' . $order->customer['company_address_corporate'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_address_post']) ? ENTRY_COMPANY_ADDRESS_POST . ' ' . $order->customer['company_address_post'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_telephone']) ? ENTRY_COMPANY_TELEPHONE . ' ' . $order->customer['company_telephone'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_fax']) ? ENTRY_COMPANY_FAX . ' ' . $order->customer['company_fax'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_bank']) ? ENTRY_COMPANY_BANK . ' ' . $order->customer['company_bank'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_bik']) ? ENTRY_COMPANY_BIK . ' ' . $order->customer['company_bik'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_ks']) ? ENTRY_COMPANY_KS . ' ' . $order->customer['company_ks'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_rs']) ? ENTRY_COMPANY_RS . ' ' . $order->customer['company_rs'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_general']) ? ENTRY_COMPANY_GENERAL . ' ' . $order->customer['company_general'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_financial']) ? ENTRY_COMPANY_FINANCIAL . ' ' . $order->customer['company_financial'] . '<br><br>' . "\n" : '');
?>
			  </div></div></td>
                <td class="main"><?php echo '<a href="#" onclick="document.getElementById(\'customer_company\').style.display = (document.getElementById(\'customer_company\').style.display==\'none\' ? \'block\' : \'none\'); return false;" title="' . ENTRY_COMPANY_DETAILS . '"><u>' . $order->customer['company'] . '</u></a>'; ?></td>
              </tr>
<?php
	}
?>
              <tr>
                <td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
              </tr>
<?php
	if (tep_not_null($order->customer['ip'])) {
?>
              <tr>
                <td class="main"><strong><?php echo ENTRY_IP_ADDRESS; ?></strong></td>
                <td class="main"><?php echo $order->customer['ip']; ?></td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
            </table></td>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="120"><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
                <td class="main"><?php $order_delivery_array = $order->delivery; unset($order_delivery_array['telephone']); echo $order->delivery['name'] . ', ' . tep_address_format($order->delivery['format_id'], $order_delivery_array, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
				<td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->delivery['telephone']; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_SHIPPING_DATE; ?></strong></td>
                <td class="main"><?php echo tep_not_null($order->delivery['date']) ? tep_date_short($order->delivery['date']) : TEXT_NOT_SET; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_SHIPPING_TIME; ?></strong></td>
                <td class="main"><?php echo tep_not_null($order->delivery['time']) ? $order->delivery['time'] : TEXT_NOT_SET; ?></td>
              </tr>
            </table></td>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="100"><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
                <td class="main"><?php $order_billing_array = $order->billing; unset($order_billing_array['telephone']); echo $order->billing['name'] . ', ' . tep_address_format($order->billing['format_id'], $order_billing_array, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
				<td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->billing['telephone']; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
		  <tr>
			<td class="main"><strong><?php echo ENTRY_ORDER_SHOP; ?></strong></td>
			<td class="main"><?php echo '<a href="' . $order->info['shops_url'] . '" target="_blank"><u>' . str_replace('http://', '', $order->info['shops_url']) . '</u></a>'; ?></td>
		  </tr>
<?php
	if (tep_not_null($order->info['code']) && $order->info['code']!=$order->info['id']) {
?>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_ORDER_CODE; ?></strong></td>
			<td class="main"><?php echo $order->info['code']; ?></td>
		  </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
            <td class="main"><?php echo $order->info['payment_method'] . (tep_not_null($payment_link) ? ' (' . $payment_link . ')' : ''); ?></td>
          </tr>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    }
    if (tep_not_null($order->info['check_account_type']) || tep_not_null($order->info['check_bank_name']) || tep_not_null($order->info['check_routing_number']) || tep_not_null($order->info['check_account_number'])) {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ACCOUNT_TYPE; ?></td>
            <td class="main"><?php echo $order->info['check_account_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_BANK_NAME; ?></td>
            <td class="main"><?php echo $order->info['check_bank_name']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ROUTING_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['check_routing_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ACCOUNT_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['check_account_number']; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><strong><?php echo ENTRY_SELF_DELIVERY; ?></strong></td>
            <td class="main"><?php echo (tep_not_null($order->delivery['delivery_self_address']) ? $order->delivery['delivery_self_address'] : TEXT_NOT_SET); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo ENTRY_DELIVERY_TRANSFER; ?></strong> <?php echo ((tep_not_null($order->info['delivery_transfer']) && $order->info['delivery_transfer']!='0000-00-00') ? tep_date_short($order->info['delivery_transfer']) : TEXT_NOT_SET); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2">
          <tr class="dataTableHeadingRow" align="center">
            <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MANUFACTURER; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_CODE; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_WEIGHT; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_QUANTITY; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UNIT_PRICE; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TOTAL; ?></td>
          </tr>
<?php
    for ($i=0, $total_weight=0, $n=sizeof($order->products); $i<$n; $i++) {
	  $manufacturer_string = $order->products[$i]['manufacturer'];
	  if ($order->products[$i]['year'] > 0) $manufacturer_string .= (tep_not_null($manufacturer_string) ? ', ' : '') . $order->products[$i]['year'];
      echo '          <tr class="dataTableRow" align="center">' . "\n" .
           '            <td class="dataTableContent" align="left">' . ($i+1) . '.&nbsp;<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']) . '" target="_blank"><u>' . $order->products[$i]['name'] . '</u></a></td>' . "\n" .
           '            <td class="dataTableContent">' . $manufacturer_string . '</td>' . "\n" .
           '            <td class="dataTableContent"><nobr>' . $order->products[$i]['model'] . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['code'] . '</td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['weight'] . '</td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['qty'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></nobr></td>' . "\n" .
      '          </tr>' . "\n";
	  $total_weight += $order->products[$i]['weight'] * $order->products[$i]['qty'];
    }
?>
          <tr>
            <td align="right" colspan="9"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td class="smallText" align="right"><?php echo ENTRY_TOTAL_WEIGHT; ?></td>
				<td class="smallText" align="center"><?php echo $total_weight . ENTRY_TOTAL_WEIGHT_UNITS; ?></td>
			  </tr>
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['title'] . (substr($order->totals[$i]['title'], -1)!=':' ? ':' : '') . '</td>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
<?php
	$total_sum=0;
	$subtotal_sum=0;
?>

		  
          <tr valign="top">
            <td colspan="2" class="main"><br><a href="#" onclick="document.getElementById('payment_table').style.display = (document.getElementById('payment_table').style.display=='none' ? 'table' : 'none'); return false;"><strong><?php echo 'Сформировать ссылку на оплату заявки электронным платежом'; ?></strong></a><br><br>
			<?php echo tep_draw_form('payment', '#'); ?><table border="0" cellspacing="0" cellpadding="2" id="payment_table" style="display: none;">
			  <tr>
				<td><?php echo 'Сумма платежа:'; ?></td>
				<td><?php echo tep_draw_input_field('payment_sum', (string)round($total_sum, $currencies->get_decimal_places($order_info['currency'])), 'size="4" style="text-align: right;"') . 'руб.'; ?></td>
			  </tr>
			  <tr>
				<td><?php echo 'Описание платежа:'; ?></td>
				<td><?php echo tep_draw_textarea_field('payment_description', 'soft', '55', '4', 'Предоплата за заказ #' . $oID); ?></td>
			  </tr>
			  <tr id="payment_link_div" style="display: none;">
				<td><?php echo 'Ссылка на оплату:'; ?></td>
				<td><div id="payment_link"></div></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action'))) . '#">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW, 'onclick="getXMLDOM(\'' . tep_href_link(FILENAME_ADVANCE_ORDERS, 'action=create_payment_link&oID=' . $oID . '') . '&payment_sum=\'+encodeURL(document.payment.payment_sum.value)+\'&payment_description=\'+encodeURL(document.payment.payment_description.value), \'payment_link\'); document.getElementById(\'payment_link_div\').style.display = \'\'; return false;"') . '</a>'; ?></td>
			  </tr>
            </table></form></td>
 
            </table></td>
          </tr>		  
		  
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
	  
	  
	  
	  
      <tr>
        <td class="main"><table border="0" cellspacing="1" cellpadding="5">
          <tr align="center" class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_OPERATOR; ?></strong></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
		$users_query = tep_db_query("select users_name from " . TABLE_USERS . " where users_id = '" . tep_db_input($orders_history['operator']) . "'");
		$users = tep_db_fetch_array($users_query);
        echo '		  <tr class="dataTableRow" align="center">' . "\n" .
             '			<td class="dataTableContent">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '			<td class="dataTableContent">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '			<td class="dataTableContent">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '			<td class="dataTableContent" align="left">' . nl2br($orders_history['comments']) . '&nbsp;</td>' . "\n" .
             '			<td class="dataTableContent" align="left">' . nl2br($orders_history['admin_comments']) . '&nbsp;</td>' . "\n" .
			 '			<td class="dataTableContent">' . $users['users_name'] . '&nbsp;</td>' . "\n" .
             '		  </tr>' . "\n";
      }
    } else {
        echo '		  <tr class="dataTableRow">' . "\n" .
             '			<td class="dataTableContent" colspan="6">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '		  </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>'; ?></td>
      </tr>
<?php
  } elseif (($action == 'add_products') && ($order_exists == true)) {
	$shop_info_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . $order->info['shops_id'] . "'");
	$shop_info = tep_db_fetch_array($shop_info_query);
	tep_db_select_db($shop_info['shops_database']);
?>
	  <tr>
		<td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="1">
		  <tr>
			<td class="pageHeading"><?php echo sprintf(HEADING_TITLE_3, $oID); ?></td>
			<td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
			<td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=edit') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator(); ?></td>
	  </tr>
<?php
	echo tep_draw_form('order_products', FILENAME_ORDERS, tep_get_all_get_params());
?>
	  <tr>
		<td class="main"><?php echo TEXT_CHOOSE_CATEGORIES; ?></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_pull_down_menu('categories[]', tep_get_category_tree('', '&nbsp; '), $HTTP_POST_VARS['categories'], 'size="10" multiple="true"'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><?php echo TEXT_SEARCH_PRODUCTS; ?></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_input_field('keywords', $HTTP_POST_VARS['keywords'], 'size="35"'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><?php echo tep_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
	  </tr>
	  </form>
<?php
	if (isset($HTTP_POST_VARS['categories']) || isset($HTTP_POST_VARS['keywords'])) {
?>
	  <tr>
		<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><?php echo TEXT_CHOOSE_PRODUCTS; ?></td>
	  </tr>
	  <tr>
		<td>
		<table border="0" width="100%" cellspacing="1" cellpadding="2">
<?php
	echo tep_draw_form('order_products', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=add_products_confirm');
?>
		  <tr class="dataTableHeadingRow" align="center">
			<td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
			<td class="dataTableHeadingContent" width="100"><?php echo TABLE_HEADING_PRODUCTS_CODE; ?></td>
			<td class="dataTableHeadingContent" width="150"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
			<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_QUANTITY; ?></td>
			<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_UNIT_PRICE; ?></td>
		  </tr>
<!-- Begin Products Listings Block -->
<?php
	  $old_category_id = '';
	  $categories = $HTTP_POST_VARS['categories'];
	  if (!is_array($categories)) $categories = array();
	  $products_to_search = array();
	  if (sizeof($categories) > 0) {
		reset($categories);
		while (list(, $category_id) = each($categories)) {
		  $subcategories = array($category_id);
		  tep_get_subcategories($subcategories, $category_id);
		  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories) . "')");
		  while ($products = tep_db_fetch_array($products_query)) {
			$products_to_search[] = $products['products_id'];
		  }
		}
	  }
	  $keywords = tep_db_prepare_input(stripslashes($HTTP_POST_VARS['keywords']));
	  if (tep_not_null($keywords)) {
		$products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_INFO . " where (products_name like '%" . str_replace(' ', "%' and products_name like '%", $keywords) . "%') or (products_description like '%" . str_replace(' ', "%' and products_description like '%", $keywords) . "%') or (products_code like '%" . str_replace(' ', "%' and products_code like '%", $keywords) . "%') or (products_model like '%" . str_replace(' ', "%' and products_model like '%", $keywords) . "%')");
		while ($products = tep_db_fetch_array($products_query)) {
		  $products_to_search[] = $products['products_id'];
		}
	  }

	  if (sizeof($products_to_search) > 0) {
		reset($products_to_search);
		while (list(, $products_id) = each($products_to_search)) {
		  $products_query = tep_db_query("select * from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$products_id . "'");
		  $products = tep_db_fetch_array($products_query);
		  $author_name = $products['authors_name'];
		  $manufacturer_name = $products['manufacturers_name'];
		  $temp_string = '';
		  if (tep_not_null($author_name)) $temp_string .= $author_name;
		  if (tep_not_null($manufacturer_name)) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $manufacturer_name;
		  if ((int)$products['products_year'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $products['products_year'];
		  if (tep_not_null($temp_string)) $temp_string = ' (' . $temp_string . ')';
		  $products['products_name'] .= $temp_string;
		  $product_price = tep_round($products['products_price'] * $currencies->currencies[$order->info['currency']]['value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
?>
		  <tr class="dataTableRow" align="center">
			<td class="dataTableContent" align="left"><?php echo $products['products_name']; ?></td>
			<td class="dataTableContent" width="100"><?php echo $products['products_code']; ?></td>
			<td class="dataTableContent" width="150"><?php echo $products['products_model']; ?></td>
			<td class="dataTableContent"><?php echo tep_draw_input_field('qty[' . $products['products_id'] . ']', '', 'size="3" onFocus="this.value = \'\';"'); ?></td>
			<td class="dataTableContent" align="right"><?php echo $currencies->currencies[$order->info['currency']]['symbol_left'] . tep_draw_input_field('price[' . $products['products_id'] . ']', (string)$product_price, 'size="6" style="text-align: right;"') . $currencies->currencies[$order->info['currency']]['symbol_right']; ?></td>
		  </tr>
<?php
		}
?>
<!-- End Products Listings Block -->
		  <tr>
			<td colspan="6"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
    	  <tr>
			<td colspan="6" align="right" valign="top"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=edit') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_insert.gif', IMAGE_INSERT); ?></td>
	      </tr>
<?php
	  } else {
?>
		  <tr>
			<td colspan="6"><strong><?php echo TEXT_NO_PRODUCTS_FOUND; ?></strong></td>
		  </tr>
<?php
	  }
?>
		  </form>
		</table></td>
	  </tr>
<?php
	}
	tep_db_select_db(DB_DATABASE);
  } elseif (($action == 'edit') && ($order_exists == true)) {
?>
	  <tr>
		<td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="pageHeading"><?php echo sprintf(HEADING_TITLE_2, $oID); ?></td>
			<td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
			<td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=view') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator(); ?></td>
	  </tr>
<!-- Begin Addresses Block -->
	  <tr><?php echo tep_draw_form('edit_order', FILENAME_ORDERS, tep_get_all_get_params(array('action','paycc')) . 'action=update_order') . tep_draw_hidden_field('orders_id', $order->info['id']); ?>
		<td><table border="0" cellspacing="0" cellpadding="2">
<!--DWLayoutTable-->
		  <tr valign="top">
			<td width="150">&nbsp;</td>
			<td class="main"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
			<td rowspan="10"><!--DWLayoutEmptyCell-->&nbsp;</td>
			<td valign="top" class="main"><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
			<td rowspan="10"><!--DWLayoutEmptyCell-->&nbsp;</td>
			<td valign="top" class="main"><strong> <?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_NAME; ?></td>
			<td class="main"><?php echo tep_draw_input_field('update_customer_name', tep_html_quotes($order->customer['name']), 'size="31"'); ?></td>
			<td class="main"><?php echo tep_draw_input_field('update_delivery_name', tep_html_quotes($order->delivery['name']), 'size="31"'); ?></td>
			<td class="main"><?php echo tep_draw_input_field('update_billing_name', tep_html_quotes($order->billing['name']), 'size="31"'); ?></td>
		  </tr>
<?php
//	if ($order->customer['type']=='corporate') {
?>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_COMPANY; ?>
			<br>
			<small><a href="#" onclick="document.getElementById('customer_company').style.display = (document.getElementById('customer_company').style.display=='none' ? 'block' : 'none'); return false;"><u><?php echo ENTRY_COMPANY_DETAILS; ?></u></a></small>
			<div id="customer_company" style="background: #EFEFEF; border: 1px solid #CCCCCC; position: absolute; display: none;">
			<div style="text-align: right;"><a href="#" onclick="document.getElementById('customer_company').style.display = 'none'; return false;"><?php echo tep_image(DIR_WS_IMAGES . 'cal_close_small.gif', ''); ?></a></div>
			<table border="0" cellspacing="0" cellpadding="2" width="400" style="margin: 5px 15px 15px 15px;">
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_FULL; ?></td>
				<td width="33%"><?php echo tep_draw_textarea_field('company_full', 'soft', '31', '4', $order->customer['company_full']); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_OKVED; ?></td>
				<td width="33%"><?php echo tep_draw_textarea_field('company_okved', 'soft', '31', '4', $order->customer['company_okved']); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_INN; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_inn', $order->customer['company_inn'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_KPP; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_kpp', $order->customer['company_kpp'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_OKPO; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_okpo', $order->customer['company_okpo'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_OKOGU; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_okogu', $order->customer['company_okogu'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_OKATO; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_okato', $order->customer['company_okato'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_OGRN; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_ogrn', $order->customer['company_ogrn'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_OKFS; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_okfs', $order->customer['company_okfs'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_OKOPF; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_okopf', $order->customer['company_okopf'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_ADDRESS_CORPORATE; ?></td>
				<td width="33%"><?php echo tep_draw_textarea_field('company_address_corporate', 'soft', '31', '3', $order->customer['company_address_corporate']); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_ADDRESS_POST; ?></td>
				<td width="33%"><?php echo tep_draw_textarea_field('company_address_post', 'soft', '31', '3', $order->customer['company_address_post']); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_TELEPHONE; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_telephone', $order->customer['company_telephone'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_FAX; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_fax', $order->customer['company_fax'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_BANK; ?></td>
				<td width="33%"><?php echo tep_draw_textarea_field('company_bank', 'soft', '31', '3', $order->customer['company_bank']); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_BIK; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_bik', $order->customer['company_bik'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_KS; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_ks', $order->customer['company_ks'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_RS; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_rs', $order->customer['company_rs'], 'size="31"'); ?></td>
			  </tr>
			  <tr>
				<td width="17%"><?php echo ENTRY_COMPANY_GENERAL; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_general', $order->customer['company_general'], 'size="31"'); ?></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td width="17%"><?php echo ENTRY_COMPANY_FINANCIAL; ?></td>
				<td width="33%"><?php echo tep_draw_input_field('company_financial', $order->customer['company_financial'], 'size="31"'); ?></td>
			  </tr>
			</table></div>
			</td>
			<td><?php echo tep_draw_textarea_field('update_customer_company', 'soft', '31', '3', tep_html_quotes($order->customer['company'])); ?></td>
			<td>&nbsp;<?php #echo tep_draw_textarea_field('update_delivery_company', 'soft', '31', '3', tep_html_quotes($order->delivery['company'])); ?></td>
			<td>&nbsp;<?php #echo tep_draw_textarea_field('update_billing_company', 'soft', '31', '3', tep_html_quotes($order->billing['company'])); ?></td>
		  </tr>
<?php
//	}
?>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_ADDRESS; ?></td>
			<td><?php echo tep_draw_textarea_field('update_customer_street_address', 'soft', '31', '3', tep_html_quotes($order->customer['street_address'])); ?></td>
			<td><?php echo tep_draw_textarea_field('update_delivery_street_address', 'soft', '31', '3', tep_html_quotes($order->delivery['street_address'])); ?></td>
			<td><?php echo tep_draw_textarea_field('update_billing_street_address', 'soft', '31', '3', tep_html_quotes($order->billing['street_address'])); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_SUBURB; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_suburb', tep_html_quotes($order->customer['suburb']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_suburb', tep_html_quotes($order->delivery['suburb']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_suburb', tep_html_quotes($order->billing['suburb']), 'size="31"'); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_CITY; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_city', tep_html_quotes($order->customer['city']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_city', tep_html_quotes($order->delivery['city']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_city', tep_html_quotes($order->billing['city']), 'size="31"'); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_STATE; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_state', tep_html_quotes($order->customer['state']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_state', tep_html_quotes($order->delivery['state']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_state', tep_html_quotes($order->billing['state']), 'size="31"'); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_POSTCODE; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_postcode', $order->customer['postcode'], 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_postcode', $order->delivery['postcode'], 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_postcode', $order->billing['postcode'], 'size="31"'); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_CUSTOMER_COUNTRY; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_country', tep_html_quotes($order->customer['country']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_country', tep_html_quotes($order->delivery['country']), 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_country', tep_html_quotes($order->billing['country']), 'size="31"'); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
			<td><?php echo tep_draw_input_field('update_customer_telephone', $order->customer['telephone'], 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_delivery_telephone', $order->delivery['telephone'], 'size="31"'); ?></td>
			<td><?php echo tep_draw_input_field('update_billing_telephone', $order->billing['telephone'], 'size="31"'); ?></td>
		  </tr>
		</table></td>
	  </tr>
<!-- End Addresses Block -->
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
	  </tr>
<!-- Begin Phone/Email Block -->
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var dateDelivery = new ctlSpiffyCalendarBox("dateDelivery", "edit_order", "delivery_date", "btnDate1", "<?php echo ($order->delivery['date']!='0000-00-00' ? tep_date_short($order->delivery['date']) : ''); ?>", scBTNMODE_CUSTOMBLUE);
</script>
	  <tr>
		<td><table border="0" cellspacing="0" cellpadding="2">
		  <tr>
			<td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
			<td class="main"><?php echo tep_draw_input_field('update_customer_email_address', $order->customer['email_address'], 'size="31"'); ?></td>
		  </tr>
<?php
	if (tep_not_null($order->customer['ip'])) {
?>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
		  </tr>
          <tr>
            <td class="main"><strong><?php echo ENTRY_IP_ADDRESS; ?></strong></td>
            <td class="main"><?php echo $order->customer['ip']; ?></td>
          </tr>
<?php
	}
?>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
		  </tr>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_ORDER_SHOP; ?></strong></td>
			<td class="main"><?php echo '<a href="' . $order->info['shops_url'] . '" target="_blank"><u>' . str_replace('http://', '', $order->info['shops_url']) . '</u></a>'; ?></td>
		  </tr>
<?php
	if (tep_not_null($order->info['code']) && $order->info['code']!=$order->info['id']) {
?>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_ORDER_CODE; ?></strong></td>
			<td class="main"><?php echo $order->info['code']; ?></td>
		  </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_SHIPPING_DATE; ?></strong><br><small>(<?php echo strtoupper(CALENDAR_DATE_FORMAT); ?>)</small></td>
			<td valign="top" class="main"><script language="javascript">dateDelivery.writeControl(); dateDelivery.dateFormat="<?php echo CALENDAR_DATE_FORMAT; ?>";</script></td>
		  </tr>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_SHIPPING_TIME; ?></strong></td>
			<td class="main"><?php echo tep_draw_input_field('delivery_time', $order->delivery['time'], 'size="31"'); ?></td>
		  </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo ENTRY_DELIVERY_TRANSFER; ?></strong> <?php echo ((tep_not_null($order->info['delivery_transfer']) && $order->info['delivery_transfer']!='0000-00-00') ? tep_date_short($order->info['delivery_transfer']) : TEXT_NOT_SET); ?></td>
          </tr>
<!-- End Phone/Email Block -->
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
<!-- Begin Payment Block -->
		  <tr>
			<td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
			<td class="main"><?php echo tep_draw_pull_down_menu('update_info_payment_method', $payments_array, $order->info['payment_method_class']) . (tep_not_null($payment_link) ? ' (' . $payment_link . ')' : ''); ?></td>
		  </tr>
<?php
	if ($order->info['cc_type'] || $order->info['cc_owner'] || $order->info['payment_method'] == "Credit Card" || $order->info['cc_number']) { ?>
<!-- Begin Credit Card Info Block -->
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
		  <tr>
			<td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
			<td><?php echo tep_draw_input_field('update_info_cc_type', $order->info['cc_type'], 'size="20"'); ?></td>
		  </tr>
		  <tr>
			<td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
			<td><?php echo tep_draw_input_field('update_info_cc_owner', $order->info['cc_owner'], 'size="20"'); ?></td>
		  </tr>
		  <tr>
			<td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
			<td><?php echo tep_draw_input_field('update_info_cc_number', $order->info['cc_number'], 'size="20"'); ?></td>
		  </tr>
		  <tr>
			<td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
			<td><?php echo tep_draw_input_field('update_info_cc_expires', $order->info['cc_expires'], 'size="10"'); ?></td>
		  </tr>
	  <!-- End Credit Card Info Block -->
<?php
	}
?>
		</table></td>
	  </tr>
<!-- End Payment Block -->
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
<!-- Begin Products Listing Block -->
	  <tr>
		<td><table border="0" width="100%" cellspacing="1" cellpadding="2">
		  <tr class="dataTableHeadingRow" align="center">
		    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MANUFACTURER; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_CODE; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_WEIGHT; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_QUANTITY; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UNIT_PRICE; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX; ?></td>
	    	<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TOTAL; ?></td>
		  </tr>
<!-- Begin Products Listings Block -->
<?php
	$index = 0;
	$order->products = array();
	$orders_products_query = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$oID . "'");
	while ($orders_products = tep_db_fetch_array($orders_products_query)) {
	  $manufacturer_string = $orders_products['manufacturers_name'];
	  if ($orders_products['products_year'] > 0) $manufacturer_string .= (tep_not_null($manufacturer_string) ? ', ' : '') . $orders_products['products_year'];
	  $order->products[$index] = array('id' => $orders_products['products_id'],
									   'qty' => $orders_products['products_quantity'],
									   'name' => str_replace("'", "&#039;", stripslashes($orders_products['products_name'])),
									   'manufacturer' => $manufacturer_string,
									   'model' => $orders_products['products_model'],
									   'code' => $orders_products['products_code'],
									   'weight' => $orders_products['products_weight'],
									   'tax' => $orders_products['products_tax'],
									   'price' => $orders_products['products_price'],
									   'final_price' => $orders_products['final_price'],
									   'orders_products_id' => $orders_products['orders_products_id']);
	  $index++;
	}

	for ($i=0, $total_weight=0, $subtotal_sum=0; $i<sizeof($order->products); $i++) {
	  $RowStyle = "dataTableContent";
	  $products_price = tep_round($order->products[$i]['price']*$order->info['currency_value'], $currencies->currencies[$order->info['currency']]['decimal_places']);
	  $final_price = $order->products[$i]['final_price'] * $order->products[$i]['qty'];

	  echo '		  <tr class="dataTableRow" align="center">' . "\n" .
		   '			<td class="dataTableContent" align="left">' . ($i+1) . '.&nbsp;<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']) . '" target="_blank"><u>' . $order->products[$i]['name'] . '</u></a></td>' . "\n" .
	  '			<td class="dataTableContent">' . $order->products[$i]['manufacturer'] . '</td>' . "\n" .
	  '			<td class="dataTableContent" nowrap="nowrap">' . $order->products[$i]['model'] . '</td>' . "\n" .
	  '			<td class="dataTableContent">' . $order->products[$i]['code'] . '</td>' . "\n" .
	  '			<td class="dataTableContent">' . $order->products[$i]['weight'] . '</td>' . "\n" .
	  '			<td class="dataTableContent" align="center">' . tep_draw_input_field('update_products[' . $order->products[$i]['orders_products_id'] . '][qty]', $order->products[$i]['qty'], 'size="2" style="text-align: center;"') . '</td>' . "\n" .
	  '			<td class="dataTableContent" align="right">' . $currencies->currencies[$order->info['currency']]['symbol_left'] . tep_draw_input_field('update_products[' . $order->products[$i]['orders_products_id'] . '][price]', (string)$products_price, 'size="6" style="text-align: right;"') . $currencies->currencies[$order->info['currency']]['symbol_right'] . '</td>' . "\n" .
	  '			<td class="dataTableContent" align="center">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
	  '			<td class="dataTableContent" align="right">' . $currencies->format($final_price, true, $order->info['currency'], $order->info['currency_value']) . ($order->info['currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($final_price) . ')' : '') . '</td>' . "\n" .
	  '		  </tr>' . "\n";
	  $total_weight += $order->products[$i]['weight'] * $order->products[$i]['qty'];
	  $subtotal_sum += round($final_price, $currencies->get_decimal_places(DEFAULT_CURRENCY));
	}
?>
		</table>
<!-- End Products Listings Block -->
<!-- Begin Order Total Block -->
		<table border="0" cellspacing="0" cellpadding="2" width="100%">
		  <tr>
			<td valign="bottom"><a href="<?php echo tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=add_products'); ?>"><u><strong><font size="3"><?php echo TEXT_DATE_ORDER_ADDNEW; ?></font></strong></u></a></td>
			<td align="right"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td class="smallText" align="right"><?php echo ENTRY_TOTAL_WEIGHT; ?></td>
				<td class="smallText" align="center"><?php echo $total_weight . ENTRY_TOTAL_WEIGHT_UNITS; ?></td>
			  </tr>
<?php
	// Override order.php Class's Field Limitations
	$totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' order by sort_order");
	$order->totals = array();
	while ($totals = tep_db_fetch_array($totals_query)) {
	  $order->totals[] = array('title' => $totals['title'],
							   'text' => $totals['text'],
							   'class' => $totals['class'],
							   'value' => $totals['value'],
							   'orders_total_id' => $totals['orders_total_id']);
	}

	$TotalsArray = array();
	for ($i=0; $i<sizeof($order->totals); $i++) {
	  if ($order->totals[$i]['class']=='ot_total') $TotalsArray[] = array("Name" => "          ", "Price" => "", "Class" => "ot_custom", "TotalID" => "0");
	  $value = tep_round($order->totals[$i]['value'] * $order->info['currency_value'], $currencies->get_decimal_places($order->info['currency']));
	  $TotalsArray[] = array("Name" => $order->totals[$i]['title'], "Price" => (string)$value, "Class" => $order->totals[$i]['class'], "TotalID" => $order->totals[$i]['orders_total_id'], "Text" => $order->totals[$i]['text']);
	}
//	echo '<pre>' . print_r($TotalsArray, true) . '</pre>';

	reset($TotalsArray);
	foreach($TotalsArray as $TotalIndex => $TotalDetails) {
	  $TotalStyle = "smallText";
	  $TotalDetails["Price"] = str_replace(',', '.', $TotalDetails["Price"]);
	  if ( ($TotalDetails["Class"] == "ot_subtotal") || ($TotalDetails["Class"] == "ot_total") ) {
		echo '			  <tr>' . "\n" .
			 '				<td class="smallText" align="right">' . $TotalDetails["Name"] . '</td>' . "\n" .
			 '				<td class="smallText" align="right">' . $TotalDetails["Text"] . ($order->info['currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($subtotal_sum) . ')' : '') .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][title]', trim($TotalDetails["Name"])) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][value]', $TotalDetails["Price"]*$order->info['currency_value']) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][class]', $TotalDetails["Class"]) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][total_id]', $TotalDetails["TotalID"]) .
			 '</td>' . "\n" .
			 '			  </tr>' . "\n";
	  } elseif($TotalDetails["Class"] == "ot_tax") {
		echo '			  <tr>' . "\n" .
			 '				<td align="right" class="smallText">' . tep_draw_input_field('update_totals[' . $TotalIndex . '][title]', trim($TotalDetails["Name"]), 'size="' . strlen(trim($TotalDetails['Name'])) . '" style="text-align: right;"') . '</td>' . "\n" .
			 '				<td class="smallText"><strong>' . $TotalDetails["Text"] .  tep_draw_hidden_field('update_totals[' . $TotalIndex . '][value]', $TotalDetails['Price'], 'size="6" style="text-align: right;"') .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][class]', $TotalDetails['Class']) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][total_id]', $TotalDetails['TotalID']) .
			 '</strong></td>' . "\n" .
			 '			  </tr>' . "\n";
	  } elseif($TotalDetails["Class"] == "ot_shipping") {
//		$shipping_field = tep_draw_input_field('update_totals[' . $TotalIndex . '][title]', trim($TotalDetails['Name']), 'size="' . (strlen(trim($TotalDetails['Name']))+2) . '" style="text-align: right;"');
		$shipping_field = tep_draw_pull_down_menu('update_totals[' . $TotalIndex . '][title]', $shipping_array, $order->delivery['delivery_method_class']);
		echo '			  <tr>' . "\n" .
			 '				<td align="right" class="smallText">' . $shipping_field . '</td>' . "\n" .
			 '				<td class="smallText">' . tep_draw_input_field('update_totals[' . $TotalIndex . '][value]', $TotalDetails['Price'], 'size="6" style="text-align: right;"') . ($order->info['currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($TotalDetails['Price']/$order->info['currency_value']) . ')' : '') .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][class]', $TotalDetails['Class']) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][total_id]', $TotalDetails['TotalID']) .
			 '</td>' . "\n" .
			 '			  </tr>' . "\n";
		if ($order->delivery['delivery_method_class']=='slf') {
		  echo '			  <tr>' . "\n" .
			   '				<td align="right" class="smallText">' . tep_draw_pull_down_menu('update_delivery_self_address_id', $self_delivery_addresses, $order->delivery['delivery_self_address_id']) . '</td>' . "\n" .
			   '				<td class="smallText">&nbsp;</td>' . "\n" .
			   '			  </tr>' . "\n";
		}
	  } else {
		echo '			  <tr>' . "\n" .
			 '				<td align="right" class="smallText">' . tep_draw_input_field('update_totals[' . $TotalIndex . '][title]', trim($TotalDetails['Name']), 'size="' . strlen(trim($TotalDetails['Name'])) . '" style="text-align: right;"') . '</td>' . "\n" .
			 '				<td class="smallText">' . tep_draw_input_field('update_totals[' . $TotalIndex . '][value]', $TotalDetails['Price'], 'size="6" style="text-align: right;"') . (($order->info['currency']!=DEFAULT_CURRENCY && tep_not_null($TotalDetails['Price'])) ? ' (' . $currencies->format($TotalDetails['Price']/$order->info['currency_value']) . ')' : '') .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][class]', $TotalDetails['Class']) .
			 tep_draw_hidden_field('update_totals[' . $TotalIndex . '][total_id]', $TotalDetails['TotalID']) .
			 '</td>' . "\n" .
			 '			  </tr>' . "\n";
	  }
	}
	if ($order->info['currency']!=DEFAULT_CURRENCY) {
	  echo '			  <tr>' . "\n" .
		   '				<td colspan="2">' . tep_draw_separator('pixel_trans.gif', '1', '1') . '</td>' . "\n" .
		   '			  </tr>' . "\n" .
		   '			  <tr>' . "\n" .
		   '				<td align="right" class="smallText"><strong>' . ENTRY_CURRENCY_EXCHANGE_RATE . '</strong></td>' . "\n" .
		   '				<td class="smallText"><strong>' . tep_round($order->info['currency_value'], 4) . '</strong></td>' . "\n" .
		   '			  </tr>' . "\n";
	}
?>
			</table></td>
		  </tr>
	<!-- End Order Total Block -->
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="main"><table border="0" cellspacing="1" cellpadding="5">
		  <tr class="dataTableHeadingRow" align="center">
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_OPERATOR; ?></strong></td>
		  </tr>
<?php
	$orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
	if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
		$users_query = tep_db_query("select users_name from " . TABLE_USERS . " where users_id = '" . tep_db_input($orders_history['operator']) . "'");
		$users = tep_db_fetch_array($users_query);
        echo '		  <tr class="dataTableRow" align="center">' . "\n" .
             '			<td class="dataTableContent">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '			<td class="dataTableContent">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '			<td class="dataTableContent">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
			 '			<td class="dataTableContent" align="left">' . nl2br($orders_history['comments']) . '&nbsp;</td>' . "\n" .
			 '			<td class="dataTableContent" align="left">' . nl2br($orders_history['admin_comments']) . '&nbsp;</td>' .
			 '			<td class="dataTableContent">' . $users['users_name'] . '&nbsp;</td>' . "\n" . "\n" .
			 '		  </tr>' . "\n";
      }
    } else {
        echo '		  <tr class="dataTableRow">' . "\n" .
             '			<td class="dataTableContent" colspan="6">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '		  </tr>' . "\n";
    }
?>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="main"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
			<td rowspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
			<td class="main"><strong><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></strong></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php
	if ($CommentsWithStatus) {
	  echo tep_draw_textarea_field('comments', 'soft', '50', '5');
	} else {
	  echo tep_draw_textarea_field('comments', 'soft', '50', '5', $order->info['comments']);
	}
?></td>
			<td class="main"><?php echo tep_draw_textarea_field('admin_comments', 'soft', '50', '5'); ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
		  <tr>
			<td width="170" class="main"><nobr><strong><?php echo ENTRY_STATUS; ?></strong></td>
			<td>&nbsp;<?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></nobr></td>
			<td rowspan="3" align="right" valign="bottom"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . '&action=view') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;</td>
		  </tr>
		  <tr>
			<td class="main"><nobr><strong><?php echo ENTRY_NOTIFY_CUSTOMER; ?></strong></td>
			<td><?php echo tep_draw_checkbox_field('notify', 'on', false); ?></nobr></td>
		  </tr>
		  <tr>
			<td class="main"><strong><?php echo ENTRY_NOTIFY_COMMENTS; ?></strong></td>
			<td><?php echo tep_draw_checkbox_field('notify_comments', 'on', true); ?></td>
		  </tr>
		</table></td>
	  </tr>
      </form>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" cellspacing="0" cellpadding="0">
              <?php echo tep_draw_form('orders', FILENAME_ORDERS, '', 'get', 'onkeypress="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; this.submit(); }" onkeydown="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; this.submit(); }" onkeyup="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; this.submit(); }"'); ?><tr>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12" onkeypress="if (event.which==13) { this.form.elements[\'action\'].value = \'view\'; this.form.submit(); }"') . tep_draw_hidden_field('action', ''); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SHOP . ' ' . tep_draw_pull_down_menu('shop', $shops_array, '', 'onChange="this.form.submit();"'); ?></td>
              </tr>
              <tr>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_CUSTOMER . ' ' . tep_draw_input_field('search', '', 'size="12" onkeypress="if (event.which==13) this.form.submit();"'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_TYPE . ' ' . tep_draw_pull_down_menu('type', array_merge(array(array('id' => '', 'text' => TEXT_ALL_TYPES)), $products_types), '', 'onChange="this.form.submit();"'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_DATE . ' ' . tep_draw_input_field('date', (isset($HTTP_GET_VARS['date']) ? $HTTP_GET_VARS['date'] : TEXT_DATE_FORMAT), 'size="12" onkeypress="if (event.which==13) this.form.submit();" onfocus="if (this.value==\'' . TEXT_DATE_FORMAT . '\') this.value = \'\';" onblur="if (this.value==\'\') this.value = \'' . TEXT_DATE_FORMAT . '\';"'); ?></td>
              </tr></form>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr valign="top">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr valign="top">
			<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_METHODS; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
			  </tr>
<?php
	$orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.delivery_date, o.delivery_time, o.last_modified, o.currency, o.currency_value, o.orders_total as order_total, o.orders_status, o.shops_id, o.delivery_method as shipping_method from " . TABLE_ORDERS . " o where 1";
    if (tep_not_null($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw .= " and o.customers_id = '" . (int)$cID . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['status'])) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw .= " and o.orders_status = '" . (int)$status . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['shop'])) {
      $shop = tep_db_prepare_input($HTTP_GET_VARS['shop']);
      $orders_query_raw .= " and o.shops_id = '" . (int)$shop . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['date'])) {
      $date_purchased = tep_db_prepare_input($HTTP_GET_VARS['date']);
	  if (preg_match('/^(\d{1,2})\.(\d{1,2})\.?(\d*)$/', $date_purchased, $regs)) {
		if (empty($regs[3])) $regs[3] = date('Y');
		$date_purchased = $regs[3] . '-' . sprintf('%02d', $regs[2]) . '-' . sprintf('%02d', $regs[1]);
    	$orders_query_raw .= " and date_format(o.date_purchased, '%Y-%m-%d') = '" . tep_db_input($date_purchased) . "'";
	  }
    }
	if (tep_not_null($HTTP_GET_VARS['type'])) {
      $type = tep_db_prepare_input($HTTP_GET_VARS['type']);
      $orders_query_raw .= " and op.products_types_id = '" . (int)$type . "' and op.orders_id = o.orders_id";
	  $orders_query_raw = str_replace("from " . TABLE_ORDERS . " o", "from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op", $orders_query_raw);
    }
	if (tep_not_null($HTTP_GET_VARS['search'])) {
      $search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	  $fields = array('o.customers_name', 'o.customers_company', 'o.customers_company', 'o.customers_street_address', 'o.customers_suburb', 'o.customers_city', 'o.customers_postcode', 'o.customers_state', 'o.customers_country', 'o.customers_telephone', 'o.customers_email_address', 'o.delivery_name', 'o.delivery_company', 'o.delivery_street_address', 'o.delivery_suburb', 'o.delivery_city', 'o.delivery_postcode', 'o.delivery_state', 'o.delivery_country', 'o.billing_name', 'o.billing_company', 'o.billing_street_address', 'o.billing_suburb', 'o.billing_city', 'o.billing_postcode', 'o.billing_state', 'o.billing_country');
	  $orders_query_array = array();
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		$orders_query_array[] = $field . " like '%" . tep_db_input(str_replace(' ', "%' and " . $field . " like '%", $search)) . "%'";
	  }
	  if (preg_match('/^[a-z0-9]+$/', $search)) {
		$partner_check_query = tep_db_query("select partners_id from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input($search) . "'");
		if (tep_db_num_rows($partner_check_query)==1) {
		  $partner_check = tep_db_fetch_array($partner_check_query);
		  $orders_query_array[] = " partners_id = '" . (int)$partner_check['partners_id'] . "'";
		}
	  }
	  $orders_query_raw .= " and (" . implode(" or ", $orders_query_array) . ")";
    }
	if (sizeof($allowed_shops_array) > 0) $orders_query_raw .= " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')";
	$orders_query_raw .= " group by o.orders_id order by o.orders_id desc";

    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($order_info = tep_db_fetch_array($orders_query)) {
	  $status_info_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$order_info['orders_status'] . "' and language_id = '" . (int)$languages_id . "'");
	  $status_info = tep_db_fetch_array($status_info_query);
	  if (!is_array($status_info)) $status_info = array();
	  $shop_info_query = tep_db_query("select if(shops_id='" . (int)SHOP_ID . "', '', shops_url) as shops_url from " . TABLE_SHOPS . " where shops_id = '" . (int)$order_info['shops_id'] . "'");
	  $shop_info = tep_db_fetch_array($shop_info_query);
	  if (!is_array($shop_info)) $shop_info = array();
	  $comments_info_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_info['orders_id'] . "' order by date_added limit 1");
	  $comments_info = tep_db_fetch_array($comments_info_query);
	  if (!is_array($comments_info)) $comments_info = array();
	  $order_info = array_merge($order_info, $status_info, $shop_info, $comments_info);
	  if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $order_info['orders_id']))) && !isset($oInfo)) {
		$oInfo = new objectInfo($order_info);
	  }

      if (isset($oInfo) && is_object($oInfo) && ($order_info['orders_id'] == $oInfo->orders_id)) {
        echo '			  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '			  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['orders_id']) . '\'">' . "\n";
      }
?>
				<td class="dataTableContent" nowrap="nowrap"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $order_info['orders_id'] . '&action=view') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;[' . $order_info['orders_id'] . '] ' . $order_info['customers_name']; ?></td>
				<td class="dataTableContent" align="right" nowrap="nowrap"><?php echo $currencies->format($order_info['order_total'], true, $order_info['currency'], $order_info['currency_value']); ?></td>
				<td class="dataTableContent" align="center" nowrap="nowrap"><?php echo substr(tep_datetime_short($order_info['date_purchased']), 0, -3) . (tep_not_null($order_info['last_modified']) ? '<br>' . substr(tep_datetime_short($order_info['last_modified']), 0, -3) : ''); ?></td>
				<td class="dataTableContent"><?php echo ((sizeof($allowed_shops_array)!=1 && tep_not_null($order_info['shops_url'])) ? '[' . str_replace('http://www.', '', $order_info['shops_url']) . ']<br />' . "\n" : '') . $order_info['payment_method'] . (tep_not_null($order_info['shipping_method']) ? '<br />' . "\n" . $order_info['shipping_method'] : '') . (tep_not_null($order_info['comments']) ? '<br />' . "\n" . '<strong>(' . nl2br($order_info['comments']) . ')</strong>' : ''); ?></td>
				<td class="dataTableContent" align="center"><?php echo $order_info['orders_status_name']; ?></td>
				<td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($order_info['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
    }
?>
			  <tr>
				<td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
					<td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
				  </tr>
				</table></td>
			  </tr>
			  <tr>
				<td colspan="6"><table width="100%" border="0" cellspacing="0" cellpadding="2">
				  <tr>
<?php
	if (SHOP_ID==1) {
	  echo tep_draw_form('download', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=upload', 'post', 'enctype="multipart/form-data"');
	  $amazon_shops = array(array('id' => '', 'text' => TEXT_UPLOAD_ORDERS_CHOOSE_SHOP));
	  $amazon_shops_query = tep_db_query("select shops_id, shops_name from " . TABLE_SHOPS . " where shops_name like '%amazon%' or shops_name like '%barnes%' order by sort_order, shops_name");
	  while ($amazon_shops_row = tep_db_fetch_array($amazon_shops_query)) {
		$amazon_shops[] = array('id' => $amazon_shops_row['shops_id'], 'text' => $amazon_shops_row['shops_name']);
	  }
?>
					<td class="smallText"><?php echo tep_draw_file_field('amazon_file', false, 'size="8"'); ?><br /><?php echo tep_draw_checkbox_field('upload_file_back', '1', false); ?>загрузка файла-подтверждения</td>
					<td class="smallText"><?php echo tep_draw_pull_down_menu('amazon_shop_id', $amazon_shops); ?><br /><a href="<?php echo tep_href_link(FILENAME_ORDERS, 'action=download_bn_file'); ?>">Скачать файл заказов B&N</a></td>
					<td class="smallText"><?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD) ; ?></td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			  </form>
<?php
	}
?>
<?php echo tep_draw_form('download', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=download'); ?>
					<td class="smallText"><?php echo sprintf(TEXT_DOWNLOAD_ORDERS, tep_draw_input_field('days', '7', 'size="1"')); ?></td>
					<td class="smallText"><?php echo tep_image_submit('button_download.gif', IMAGE_DOWNLOAD) ; ?></td>
			  </form>
				  </tr>
				</table></td>
			  </tr>
			</table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=view') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=download') . '">' . tep_image_button('button_download.gif', IMAGE_DOWNLOAD) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>