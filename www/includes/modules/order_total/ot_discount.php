<?php
  class ot_discount {
    var $title, $output;

    function ot_discount() {
	  global $customer_id;

      $this->code = 'ot_discount';
      $this->title = MODULE_ORDER_TOTAL_DISCOUNT_TITLE;
      $this->description = MODULE_ORDER_TOTAL_DISCOUNT_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_DISCOUNT_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER;

      $this->output = array();

	  $customer_discount_info_query = tep_db_query("select customers_discount_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_discount_info = tep_db_fetch_array($customer_discount_info_query);
	  if ($customer_discount_info['customers_discount_type']=='purchase') $this->enabled = false;

	  if (defined('MODULE_ORDER_TOTAL_DISCOUNT_RESTRICTED_EMAILS') && tep_not_null(MODULE_ORDER_TOTAL_DISCOUNT_RESTRICTED_EMAILS)) {
		$emails = array();
		$rows = explode("\n", MODULE_ORDER_TOTAL_DISCOUNT_RESTRICTED_EMAILS);
		reset($rows);
		while (list(, $row) = each($rows)) {
		  if (tep_not_null(trim($row))) $emails[] = trim($row);
		}
		if (sizeof($emails) > 0) {
		  $customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		  $customer_info = tep_db_fetch_array($customer_info_query);
		  reset($emails);
		  while (list(, $email) = each($emails)) {
			$email_to_compare = str_replace('\*', '.*', preg_quote($email));
			if (preg_match('/^' . $email_to_compare . '$/i', $customer_info['customers_email_address'])) {
			  $this->enabled = false;
			  break;
			}
		  }
		}
	  }
    }

    function process() {
      global $order, $customer_id, $currencies;

	  $available_products_types = array();
	  $available_products_types_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS_TYPES . " where products_types_discounts = '1'");
	  while ($available_products_types_row = tep_db_fetch_array($available_products_types_query)) {
		$available_products_types[] = $available_products_types_row['products_types_id'];
	  }

	  $order_subtotal = 0;
	  reset($order->products);
	  while (list(, $order_product) = each($order->products)) {
		if (in_array($order_product['type'], $available_products_types)) $order_subtotal += $order_product['final_price'] * $order_product['qty'];
	  }

	  $is_corporate = false;
	  $corporate_check_query = tep_db_query("select companies_corporate from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_id . "'");
	  if (tep_db_num_rows($corporate_check_query) > 0) {
		$corporate_check = tep_db_fetch_array($corporate_check_query);
		$is_corporate = ($corporate_check['companies_corporate']=='1');
	  }

	  $customer_discount = 0;
	  $customer_current_discount = 0;
	  $customer_discount_1 = 0;
	  $customer_discount_2 = 0;
	  $customer_discount_3 = 0;

	  if (tep_session_is_registered('customer_id') && tep_session_is_registered('customer_first_name') && $customer_id > 0) {
		$customer_current_discount_check_query = tep_db_query("select customers_discount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_current_discount_check = tep_db_fetch_array($customer_current_discount_check_query);
		$customer_current_discount = $customer_current_discount_check['customers_discount'];

		unset($available_orders);
		$monthes_count = (defined('MODULE_ORDER_TOTAL_DISCOUNT_MONTHES') ? (int)MODULE_ORDER_TOTAL_DISCOUNT_MONTHES : 0);
		if (tep_not_null(MODULE_ORDER_TOTAL_DISCOUNT_TABLE_1) && !$is_corporate) {
		  if ($monthes_count > 0) {
			tep_db_query("update " . TABLE_CUSTOMERS . " set customers_discount = '0' where customers_id = '" . (int)$customer_id . "'");
			$available_orders = array();
			$last_date = time();
			$last_orders_check_query = tep_db_query("select orders_id, date_purchased from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' and orders_status = '" . (int)MODULE_ORDER_TOTAL_DISCOUNT_ORDER_STATUS_ID . "' order by orders_id desc");
			while ($last_orders_check = tep_db_fetch_array($last_orders_check_query)) {
			  $order_time = strtotime($last_orders_check['date_purchased']);
			  if (($last_date-$order_time) < 60*60*24*30*$monthes_count) {
				$available_orders[] = $last_orders_check['orders_id'];
				$last_date = $order_time;
			  } else {
				break;
			  }
			}
		  }

		  $customer_orders_sum = 0;
		  $customer_orders_total_query = tep_db_query("select sum(op.final_price * op.products_quantity * o.currency_value) as total_sum from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS . " o where o.orders_id = op.orders_id and o.customers_id = '" . (int)$customer_id . "' and op.products_types_id in ('" . implode("', '", $available_products_types) . "') and o.orders_status = '" . (int)MODULE_ORDER_TOTAL_DISCOUNT_ORDER_STATUS_ID . "'" . (isset($available_orders) ? " and o.orders_id in ('" . implode("', '", $available_orders) . "')" : "") . "");
		  $customer_orders_total = tep_db_fetch_array($customer_orders_total_query);
		  $customer_orders_sum = $customer_orders_total['total_sum'];

		  $table_cost = array_map('trim', explode(',' , MODULE_ORDER_TOTAL_DISCOUNT_TABLE_1));
		  $size = sizeof($table_cost);
		  for ($i=0, $n=$size; $i<$n; $i++) {
			list($checked_value, $temp_discount) = explode(':', $table_cost[$i]);
			if ($customer_orders_sum >= $checked_value) {
			  $customer_discount_1 = $temp_discount;
			}
		  }

		  if ($customer_discount_1 > $customer_current_discount) {
			tep_db_query("update " . TABLE_CUSTOMERS . " set customers_discount = '" . $customer_discount_1 . "' where customers_id = '" . (int)$customer_id . "'");
		  } else {
			$customer_discount_1 = (float)$customer_current_discount;
		  }
		}
	  }

	  if (tep_not_null(MODULE_ORDER_TOTAL_DISCOUNT_TABLE_2) && !$is_corporate) {
		$table_cost = array_map('trim', explode(',' , MODULE_ORDER_TOTAL_DISCOUNT_TABLE_2));
		$size = sizeof($table_cost);
		for ($i=0, $n=$size; $i<$n; $i++) {
		  list($checked_value, $temp_discount) = explode(':', $table_cost[$i]);
		  $checked_value = $checked_value / $order->info['currency_value'];
		  if ($order_subtotal >= $checked_value) {
			$customer_discount_2 = $temp_discount;
		  }
		}
	  }

	  if (tep_not_null(MODULE_ORDER_TOTAL_DISCOUNT_TABLE_3) && !$is_corporate) {
		$product_min_price = (float)str_replace(',', '.', MODULE_ORDER_TOTAL_DISCOUNT_MIN_PRODUCT_PRICE);
		$product_min_price = $product_min_price / $order->info['currency_value'];
		$table_cost = array_map('trim', explode(',' , MODULE_ORDER_TOTAL_DISCOUNT_TABLE_3));
		$size = sizeof($table_cost);
		for ($i=0, $n=$size; $i<$n; $i++) {
		  list($checked_value, $temp_discount) = explode(':', $table_cost[$i]);
		  reset($order->products);
		  while (list(, $product_info) = each($order->products)) {
			if (in_array($product_info['type'], $available_products_types)) {
			  if ($product_info['qty'] >= $checked_value && $product_info['final_price'] >= $product_min_price) {
				if ($customer_discount_3 < $temp_discount) $customer_discount_3 = $temp_discount;
			  }
			}
		  }
		}
	  }

	  if ($customer_discount_1 > 0 && $customer_discount_1 >= $customer_discount_2 && $customer_discount_1 >= $customer_discount_3) {
		$customer_discount = $customer_discount_1;
		$this->title = MODULE_ORDER_TOTAL_DISCOUNT_TITLE_1;
	  } elseif ($customer_discount_2 > 0 && $customer_discount_2 >= $customer_discount_1 && $customer_discount_2 >= $customer_discount_3) {
		$customer_discount = $customer_discount_2;
		$this->title = MODULE_ORDER_TOTAL_DISCOUNT_TITLE_2;
	  } elseif ($customer_discount_3 > 0 && $customer_discount_3 >= $customer_discount_1 && $customer_discount_3 >= $customer_discount_2) {
		$customer_discount = $customer_discount_3;
		$this->title = MODULE_ORDER_TOTAL_DISCOUNT_TITLE_3;
	  } else {
		$customer_discount = 0;
	  }

	  if ($customer_current_discount > 0 && $customer_current_discount > $customer_discount) {
		$customer_discount = $customer_current_discount;
		$this->title = MODULE_ORDER_TOTAL_DISCOUNT_TITLE;
	  }

	  if ($customer_discount > 0 && $order_subtotal > 0) {
		$customer_discount_value = -tep_round(str_replace(',', '.', round($order_subtotal * $customer_discount / 100)), $currencies->get_decimal_places($order->info['currency']));
		$this->output[] = array('title' => $this->title . ' (' . $customer_discount . '%):',
								'text' => $currencies->format($customer_discount_value, true, $order->info['currency'], $order->info['currency_value']),
								'value' => $customer_discount_value);
		$order->info['total'] += $customer_discount_value;
	  }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_DISCOUNT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_DISCOUNT_STATUS', 'MODULE_ORDER_TOTAL_DISCOUNT_ORDER_STATUS_ID', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_1', 'MODULE_ORDER_TOTAL_DISCOUNT_MONTHES', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_2', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_3', 'MODULE_ORDER_TOTAL_DISCOUNT_MIN_PRODUCT_PRICE', 'MODULE_ORDER_TOTAL_DISCOUNT_RESTRICTED_EMAILS', 'MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('ѕерсональна€ скидка', 'MODULE_ORDER_TOTAL_DISCOUNT_STATUS', 'false', 'ѕоказывать персональную скидку клиента?', '6', '10','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('—татус выполненных заказов', 'MODULE_ORDER_TOTAL_DISCOUNT_ORDER_STATUS_ID', '0', '”кажите статус, при котором заказ считаетс€ выполненным (дл€ расчета накопительной скидки', '6', '20', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('“аблица скидок 1 - накопительна€ скидка', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_1', '1000:3,10000:6,40000:9,100000:12', '«адайте (в валюте магазина) размер скидки в зависимости от суммы выполненных заказов. Ќапример: 1000:3,10000:6,40000:9,100000:12, и т.д. (т.е. при сумме свыше 1000 клиент получит скидку 3%, от 1000 до 40000 - скидку 6%, от 40000 до 100000 - скидку в 9% и т.д.)', '6', '30', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ќбнуление накопительной скидки', 'MODULE_ORDER_TOTAL_DISCOUNT_MONTHES', '0', '”кажите врем€ (в мес€цах), в течение которого при отсутствии заказов накопительна€ скидка клиента будет обнулена (0 - без ограничений)', '6', '40', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('“аблица скидок 2 - оптова€ скидка', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_2', '1000:3,10000:6,40000:9,100000:12', '«адайте размер скидки в зависимости от суммы заказанных товаров.)', '6', '50', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('“аблица скидок 3 - оптова€ скидка', 'MODULE_ORDER_TOTAL_DISCOUNT_TABLE_3', '15:10,50:12,100:15', '«адайте размер скидки в зависимости от количества заказанных позиций одного наименовани€. Ќапример: 15:10,50:12,100:15, и т.д. (т.е. при заказе одного наименовани€ от 15шт. до 50шт. клиент получит скидку 10%, от 50шт. до 100шт. - скидку 12%, от 100шт. и выше - скидку в 15%)', '6', '60', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ќптова€ скидка - минимальна€ цена товара', 'MODULE_ORDER_TOTAL_DISCOUNT_MIN_PRODUCT_PRICE', '15', '«адайте (в валюте магазина) минимальную цену товара, на который распростран€етс€ 3-й вид скидки', '6', '70', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Ќе давать скидку', 'MODULE_ORDER_TOTAL_DISCOUNT_RESTRICTED_EMAILS', '', 'ѕеречислите (по одному в строке) email-адреса, обладатели которых не должны получать скидку (символ &quot;*&quot; замен€ет любую последовательность символов.', '6', '80', 'tep_cfg_textarea(', 'nl2br', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ѕор€док вывода', 'MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER', '1', 'ѕор€док вывода на сайте.', '6', '90', now())");
	  if (!tep_db_field_exists(TABLE_CUSTOMERS, 'customers_discount')) tep_db_query("alter table " . TABLE_CUSTOMERS . " add customers_discount decimal(5,2) default '0'");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>