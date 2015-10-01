<?php
  require('includes/application_top.php');

  if ($REMOTE_USER=='setbook') {
	tep_set_time_limit(300);

	die();

	$i = 0;
	$history_query = tep_db_query("select orders_status_history_id, orders_id from " . TABLE_ORDERS_STATUS_HISTORY . " where operator in ('setbook', 'alenasetbook') and date_added > '2010-01-20 11:20:00' and comments = '' order by orders_id");
	while ($history = tep_db_fetch_array($history_query)) {
//	  echo '<br>', 
	  $sql = "delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_status_history_id = '" . (int)$history['orders_status_history_id'] . "'";
	  tep_db_query($sql);
	  $info_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$history['orders_id'] . "' order by date_added desc limit 1");
	  $info = tep_db_fetch_array($info_query);
//	  echo '<br>', 
	  $sql = "update " . TABLE_ORDERS . " set orders_status = '" . (int)$info['orders_status_id'] . "' where orders_id = '" . (int)$history['orders_id'] . "'";
	  tep_db_query($sql);
	  $i ++;
	}
	echo 'ready! ' . $i;
  }

  define('TABLE_TEMP_ORDERS', 'temp_orders');
  define('TABLE_TEMP_ORDERS_PRODUCTS', 'temp_orders_products');
  define('TABLE_TEMP_ORDERS_STATUS_HISTORY', 'temp_orders_status_history');
  define('TABLE_TEMP_ORDERS_TOTAL', 'temp_orders_total');

  tep_db_query("drop table if exists " . TABLE_TEMP_ORDERS . "");
  tep_db_query("create table " . TABLE_TEMP_ORDERS . " like " . TABLE_ORDERS . "");
  tep_db_query("insert into " . TABLE_TEMP_ORDERS . " select * from " . TABLE_ORDERS . "");
  tep_db_query("drop table if exists " . TABLE_TEMP_ORDERS_PRODUCTS . "");
  tep_db_query("create table " . TABLE_TEMP_ORDERS_PRODUCTS . " like " . TABLE_ORDERS_PRODUCTS . "");
  tep_db_query("insert into " . TABLE_TEMP_ORDERS_PRODUCTS . " select * from " . TABLE_ORDERS_PRODUCTS . "");
  tep_db_query("drop table if exists " . TABLE_TEMP_ORDERS_STATUS_HISTORY . "");
  tep_db_query("create table " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " like " . TABLE_ORDERS_STATUS_HISTORY . "");
  tep_db_query("insert into " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " select * from " . TABLE_ORDERS_STATUS_HISTORY . "");
  tep_db_query("drop table if exists " . TABLE_TEMP_ORDERS_TOTAL . "");
  tep_db_query("create table " . TABLE_TEMP_ORDERS_TOTAL . " like " . TABLE_ORDERS_TOTAL . "");
  tep_db_query("insert into " . TABLE_TEMP_ORDERS_TOTAL . " select * from " . TABLE_ORDERS_TOTAL . "");

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

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  include(DIR_WS_CLASSES . 'order.php');

  class temp_order {
    var $info, $totals, $products, $customer, $delivery;

    function temp_order($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
	  global $languages_id;
      $order_query = tep_db_query("select * from " . TABLE_TEMP_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      $order = tep_db_fetch_array($order_query);
	  if (!is_array($order)) $order = array();

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

      $totals_query = tep_db_query("select class, title, text, value from " . TABLE_TEMP_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
      while ($totals = tep_db_fetch_array($totals_query)) {
        $this->totals[] = array('class' => $totals['class'],
								'title' => $totals['title'],
                                'text' => $totals['text'],
								'value' => $totals['value']);
      }

      $this->info = array('currency' => $order['currency'],
                          'currency_value' => $order['currency_value'],
                          'payment_method' => $order['payment_method'],
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
						  'delivery_transfer' => $order['delivery_transfer']);

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
                              'date' => ($order['delivery_date']=='0000-00-00' ? '' : $order['delivery_date']),
                              'time' => $order['delivery_time']);

      $this->billing = array('name' => $order['billing_name'],
                             'company' => $order['billing_company'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $order['billing_country'],
                             'format_id' => $order['billing_address_format_id']);

      $index = 0;
      $orders_products_query = tep_db_query("select orders_products_id, products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price, products_code, products_weight from " . TABLE_TEMP_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                                        'id' => $orders_products['products_id'],
                                        'name' => $orders_products['products_name'],
                                        'model' => $orders_products['products_model'],
                                        'code' => $orders_products['products_code'],
                                        'weight' => $orders_products['products_weight'],
                                        'tax' => $orders_products['products_tax'],
                                        'price' => $orders_products['products_price'],
                                        'final_price' => $orders_products['final_price']);
        $index++;
      }
    }
  }

  if (!function_exists('tep_get_files')) {
	function tep_get_files($dir, $extensions = '') {
	  if (substr($dir, -1)!='/') $dir .= '/';
	  $exts = array();
	  if (!is_array($extensions)) {
		if (tep_not_null($extensions)) $exts[] = $extensions;
	  } else {
		$exts = $extensions;
	  }
	  $files = array();
	  if (is_dir($dir)) {
		$h = opendir($dir);
		while ($file = readdir($h)) {
		  if (!is_dir($dir . $file)) {
			if (sizeof($exts) > 0) {
			  $ext = substr($file, strrpos($file, '.'));
			  if (in_array($ext, $exts)) {
				$files[] = $file;
			  }
			} else {
			  $files[] = $file;
			}
		  }
		}
		closedir($h);
	  }
	  sort($files);
	  return $files;
	}
  }

  $rows = 0;
  $absent_products = array();
  $files = tep_get_files(UPLOAD_DIR . 'b_changed_orders/', '.csv');
  $new_files = array();
  reset($files);
  while (list($i, $file) = each($files)) {
	if (!preg_match('/\wa{2,}\d+/i', str_replace('', '', $file))) $new_files[] = $file;
  }
  reset($new_files);
  while (list($i, $file) = each($new_files)) {
	$oID = (int)preg_replace('/\D/', '', $file);
	$order_check_query = tep_db_query("select count(*) as total from " . TABLE_TEMP_ORDERS . " where orders_id = '" . (int)$oID . "'");
	$order_check = tep_db_fetch_array($order_check_query);

	if ($order_check['total'] > 0) {
	  $old_products = array();
	  $order = new temp_order($oID);
	  reset($order->products);
	  while (list(, $product) = each($order->products)) {
		$old_products[$product['id']] = array('id' => $product['id'],
											  'name' => $product['name'],
											  'qty' => $product['qty'],
											  'price' => $product['price']);
	  }
	  unset($payment_id);
	  unset($shipping_id);
	  unset($ot_shipping);
	  unset($ot_total_sum);
	  $comments_array = array();
	  $admin_comments_array = array();
	  $comments = '';
	  $admin_comments = '';
	  $products = array();
	  $ot_subtotal = 0;
	  unset($status);
	  $sql = '';
	  $fp = fopen(UPLOAD_DIR . 'b_changed_orders/' . $file, 'r');
	  while (!feof($fp)) {
		$data = fgetcsv($fp, '10000', ';');
		if ( (preg_match('/\w+\d+/i', trim($data[0])) || trim($data[0])==(int)trim($data[0])) && !preg_match('/\wa{2,}\d+/i', trim($data[0])) && trim($data[0])!='ORDER_ID' && is_array($data) && tep_not_null(trim($data[0]))) {
		  reset($data);
		  while (list($k, $v) = each($data)) {
			$data[$k] = trim($v);
		  }

		  $product_info = array();
		  if (!isset($payment_id)) $payment_id = $data[1];
		  if (!isset($shipping_id)) $shipping_id = $data[2];
		  if (!isset($ot_shipping)) $ot_shipping = $data[3];
		  if (!isset($ot_total_sum)) $ot_total_sum = $data[4];
		  if (tep_not_null($data[5]) && !in_array($data[5], $admin_comments_array)) $admin_comments_array[] = $data[5];
//		  if (tep_not_null($data[5]) && !in_array($data[5], $comments_array)) $comments_array[] = $data[5];
		  if (!isset($status)) $status = (int)$data[9];
		  $product_found = false;
		  $product_info_query = tep_db_query("select products_id, authors_id, products_model, products_weight, products_code from " . TABLE_PRODUCTS . " where products_code = '" . tep_db_input($data[6]) . "'");
		  if (tep_db_num_rows($product_info_query) > 0) {
			$product_found = true;
		  } else {
			if (tep_not_null(preg_replace('/[^\d]/', '', $data[10]))) {
			  $product_info_query = tep_db_query("select products_id, authors_id, products_model, products_weight, products_code from " . TABLE_PRODUCTS . " where products_model_1 = '" . tep_db_input(preg_replace('/[^\d]/', '', $data[10])) . "'");
			  if (tep_db_num_rows($product_info_query) > 0) {
				$product_found = true;
			  }
			}
		  }
		  if ($product_found) $product_info = tep_db_fetch_array($product_info_query);
		  else $absent_products[$oID][] = array('code' => $data[6], 'ISBN' => $data[10], 'file' => $file);
		  if (!is_array($product_info)) $product_info = array();
		  $product_name_info_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)$languages_id . "' limit 1");
		  $product_name_info = tep_db_fetch_array($product_name_info_query);
		  if (!is_array($product_name_info)) $product_name_info = array();
		  $author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)$languages_id . "' limit 1");
		  $author_info = tep_db_fetch_array($author_info_query);
		  if (!is_array($author_info)) $author_info = array();

		  $products_price = str_replace(',', '.', (float)$data[8]);
		  $products_name = '';
		  if (tep_not_null($author_info['authors_name'])) $products_name .= $author_info['authors_name'] . ': ';
		  $products_name .= $product_name_info['products_name'];
		  $products[$product_info['products_id']] = array('qty' => (int)$data[7],
														  'id' => $product_info['products_id'],
														  'name' => $products_name,
														  'model' => $product_info['products_model'],
														  'code' => $product_info['products_code'],
														  'weight' => $product_info['products_weight'],
														  'tax' => 0,
														  'price' => $products_price,
														  'final_price' => $products_price);
		  $ot_subtotal += $products_price * (int)$data[7];
		}
	  }

	  $email_text = '';

	  $temp_string = '';
	  reset($products);
	  while (list($product_id, $product) = each($products)) {
		// проверяем на добавление новых товаров
		if (!in_array($product_id, array_keys($old_products))) {
		  $temp_string .= sprintf(EMAIL_TEXT_PRODUCTS_ADDED, $product['name'], $product['qty']) . "\n";
		  $sql .= "insert into " . TABLE_TEMP_ORDERS_PRODUCTS . " (orders_id, products_id, products_model, products_code, products_weight, products_name, products_price, final_price, products_tax, products_quantity) values ('" . (int)$oID . "', '" . (int)$product_id . "', '" . tep_db_input($product['model']) . "', '" . tep_db_input($product['code']) . "', '" . tep_db_input($product['weight']) . "', '" . tep_db_input($product['name']) . "', '" . tep_db_input($product['price']) . "', '" . tep_db_input($product['final_price']) . "', '" . tep_db_input($product['tax']) . "', '" . tep_db_input($product['qty']) . "');\n";
		}
	  }
	  reset($old_products);
	  while (list($product_id, $product) = each($old_products)) {
		// проверяем на удаление заказанных товаров
		if (!in_array($product_id, array_keys($products))) {
		  $temp_string .= sprintf(EMAIL_TEXT_PRODUCTS_DELETED, $product['name']) . "\n";
		  $sql .= "delete from " . TABLE_TEMP_ORDERS_PRODUCTS . " where products_id = '" . (int)$product_id . "' and orders_id = '" . (int)$oID . "';\n";
		// проверяем на изменение кол-ва заказанных товаров
		} elseif ($product['qty']!=$products[$product['id']]['qty']) {
		  $temp_string .= sprintf(EMAIL_TEXT_PRODUCTS_UPDATED, $product['name'], $products[$product['id']]['qty']) . "\n";
		  $sql = "update " . TABLE_TEMP_ORDERS_PRODUCTS . " set products_price = '" . $product['price'] . "', final_price = '" . $product['final_price'] . "', products_tax = '" . $product['tax'] . "', products_quantity = '" . $product['qty'] . "' where products_id = '" . (int)$product_id . "' and orders_id = '" . (int)$oID . "';\n";
		}
	  }
	  $email_text .= (tep_not_null($temp_string) ? trim($temp_string) . "\n\n" : '');

	  $temp_string = '';
	  $operator = tep_db_prepare_input($REMOTE_USER);
	  $comments = trim(implode("\n", $comments_array));
	  $admin_comments = trim(implode("\n", $admin_comments_array));
	  if ($status==0 || ($status > 0 && $statuses_asc[$status] < $statuses_asc[$order->info['orders_status']]) ) {
		$status = $order->info['orders_status'];
	  }
	  if ($order->info['orders_status']!=$status || tep_not_null($comments) || tep_not_null($admin_comments)) {
		$sql .= "insert into " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_comments, operator) values ('" . tep_db_input($oID) . "', '" . (int)$status . "', now(), '0', '" . tep_db_input($comments)  . "', '" . tep_db_input($admin_comments)  . "', '" . tep_db_input($operator) . "');\n";
		if ($order->info['orders_status']!=$status) {
		  $status_info_query = tep_db_query("select orders_status_name, orders_status_description from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$status . "' and language_id = '" . (int)$languages_id . "'");
		  $status_info = tep_db_fetch_array($status_info_query);
		  $temp_string .= sprintf(EMAIL_TEXT_STATUS_UPDATE, '«' . $status_info['orders_status_name'] . '»') . "\n\n";
//		  $temp_string .= sprintf(EMAIL_TEXT_STATUS_UPDATE, '«' . $status_info['orders_status_name'] . '»' . (tep_not_null($status_info['orders_status_description']) ? ' (' . $status_info['orders_status_description'] . ')' : '')) . "\n\n";
		  $sql .= "update " . TABLE_TEMP_ORDERS . " set orders_status = '" . (int)$status . "' where orders_id = '" . (int)$oID . "';\n";
		}
		if (tep_not_null($comments)) {
		  $temp_string .= sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
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
							  'text' => $currencies->format($ot_subtotal, true, $order->info['currency'], $order->info['currency_value']),
							  'value' => $ot_subtotal,
							  'sort_order' => 1);

	  $ot_total_sum = str_replace(',', '.', $ot_total_sum);
	  $ot_subtotal = str_replace(',', '.', $ot_subtotal);
	  $ot_shipping = str_replace(',', '.', $ot_shipping);
	  if ($ot_total_sum==0) $ot_total_sum = str_replace(',', '.', ($ot_subtotal + $ot_shipping));
	  $ot_discount = str_replace(',', '.', ($ot_total_sum - ($ot_subtotal + $ot_shipping)));
	  if ($ot_discount < 0) {
		$order_totals[] = array('class' => 'ot_discount',
								'title' => (in_array('ot_discount', array_keys($order_total_titles)) ? $order_total_titles['ot_discount'] : $order_total_modules['ot_discount']),
								'text' => $currencies->format($ot_discount, true, $order->info['currency'], $order->info['currency_value']),
								'value' => $ot_discount,
								'sort_order' => 2);
	  } else {
		$sql .= "delete from " . TABLE_TEMP_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = 'ot_discount';\n";
	  }

	  $order_totals[] = array('class' => 'ot_shipping',
							  'title' => (in_array('ot_shipping', array_keys($order_total_titles)) ? $order_total_titles['ot_shipping'] : $order_total_modules['ot_shipping']),
							  'text' => $currencies->format($ot_shipping, true, $order->info['currency'], $order->info['currency_value']),
							  'value' => $ot_shipping,
							  'sort_order' => 3);

	  $order_totals[] = array('class' => 'ot_total',
							  'title' => (in_array('ot_total', array_keys($order_total_titles)) ? $order_total_titles['ot_total'] : $order_total_modules['ot_total']),
							  'text' => '<strong>' . $currencies->format($ot_total_sum, true, $order->info['currency'], $order->info['currency_value']) . '</strong>',
							  'value' => $ot_total_sum,
							  'sort_order' => 4);

	  reset($order_totals);
	  while (list(, $ot_total) = each($order_totals)) {
		$total_check_query = tep_db_query("select count(*) as total from " . TABLE_TEMP_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = '" . tep_db_input($ot_total['class']) . "'");
		$total_check = tep_db_fetch_array($total_check_query);
		if ($total_check['total'] < 1) {
		  $sql .= "insert into " . TABLE_TEMP_ORDERS_TOTAL . " (orders_id, title, text, value, class, sort_order) values ('" . (int)$oID . "', '" . tep_db_input($ot_total['title']) . "', '" . tep_db_input($ot_total['text']) . "', '" . $ot_total['value'] . "', '" . tep_db_input($ot_total['class']) . "', '" . (int)$ot_total['sort_order'] . "');\n";
		} else {
		  $sql .= "update " . TABLE_TEMP_ORDERS_TOTAL . " set title = '" . tep_db_input($ot_total['title']) . "', text = '" . tep_db_input($ot_total['text']) . "', value = '" . $ot_total['value'] . "', sort_order = '" . (int)$ot_total['sort_order'] . "' where orders_id = '" . (int)$oID . "' and class = '" . tep_db_input($ot_total['class']) . "';\n";
		}
	  }

	  $queries = explode(";\n", $sql);
	  reset($queries);
	  while (list(, $sql_query) = each($queries)) {
		$sql_query = trim($sql_query);
		if (tep_not_null($sql_query)) tep_db_query($sql_query);
	  }
	  $order_info_query = tep_db_query("select customers_id, shops_id from " . TABLE_TEMP_ORDERS . " where orders_id = '" . (int)$oID . "'");
	  $order_info = tep_db_fetch_array($order_info_query);
	  $customer_info_query = tep_db_query("select customers_firstname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$order_info['customers_id'] . "'");
	  $customer_info = tep_db_fetch_array($customer_info_query);
	  if (!is_array($customer_info)) $customer_info = array();
	  $shop_info_query = tep_db_query("select if(shops_ssl='', shops_url, shops_ssl) as orders_domain, shops_database from " . TABLE_SHOPS . " where shops_database <> '' and shops_id = '" . (int)$order_info['shops_id'] . "'");
	  $shop_info = tep_db_fetch_array($shop_info_query);
	  if (!is_array($shop_info)) $shop_info = array();
	  $customer_info = array_merge($customer_info, $shop_info);

	  if (empty($email_text)) {
		unset($absent_products[$oID]);
	  } else {
		$rows ++;

		$customer_name = tep_not_null($customer_info['customers_firstname']) ? $customer_info['customers_firstname'] : $order->customer['name'];

		$email_subject = sprintf(EMAIL_TEXT_SUBJECT_ORDER_UPDATED, $oID, tep_date_short($order->info['date_purchased']));

		$order_info_link = tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL');
		$order_info_link = preg_replace('/^https?:\/\/[^\/]+\/(.*)$/i', '$1', $order_info_link);
		$order_info_link = $customer_info['orders_domain'] . '/' . $order_info_link;
		$email = sprintf(EMAIL_TEXT_GREETS, $customer_name) . "\n\n" .
		sprintf(EMAIL_TEXT_ENTRY_ORDER_UPDATED, $oID, tep_date_short($order->info['date_purchased'])) . "\n\n" .
		trim($email_text) . "\n\n" .
		sprintf(EMAIL_TEXT_INVOICE_URL, $order_info_link) . "\n\n" .
		EMAIL_TEXT_PS;

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
//		tep_mail($order->customer['name'], $order->customer['email_address'], $email_subject, $email, $from_name, $from_email);
		if (tep_not_null($comments)) tep_mail($order->customer['name'], 'sivkov@setbook.ru', $email_subject, $email, $from_name, $from_email);
	  }
	}

//	if (!isset($absent_products[$oID])) unlink(UPLOAD_DIR . 'b_changed_orders/' . $file);
  }
  echo '<pre>' . print_r($absent_products, true) . '</pre>';
  echo $rows;

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>