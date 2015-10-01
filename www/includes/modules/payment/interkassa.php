<?php
  class interkassa {
	var $code, $title, $description, $enabled, $email_footer;

	function interkassa() {
	  $this->code = 'interkassa';
	  $this->title = MODULE_PAYMENT_INTERKASSA_TEXT_TITLE;
	  $this->description = MODULE_PAYMENT_INTERKASSA_TEXT_DESCRIPTION;
	  $this->sort_order = MODULE_PAYMENT_INTERKASSA_SORT_ORDER;
	  $this->enabled = ((MODULE_PAYMENT_INTERKASSA_STATUS == 'True') ? true : false);

	  if ((int)MODULE_PAYMENT_INTERKASSA_ORDER_STATUS > 0) {
		$this->order_status = MODULE_PAYMENT_INTERKASSA_ORDER_STATUS;
	  }

	  $this->email_footer = $this->get_email_footer();
	}

	function update_status() {
	}

	function javascript_validation() {
	  return false;
	}

	function selection() {
	  global $customer_id;

	  $is_dummy_account = false;
	  $is_dummy_account_check_query = tep_db_query("select customers_is_dummy_account from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $is_dummy_account_check = tep_db_fetch_array($is_dummy_account_check_query);
	  if ($is_dummy_account_check['customers_is_dummy_account']=='1' || $customer_id==0) $is_dummy_account = true;

	  $selection = array('id' => $this->code,
						 'module' => $this->title,
						 'description' => $this->description);
	  if ($is_dummy_account) $selection['error'] = MODULE_PAYMENT_INTERKASSA_TEXT_DISABLED_ERROR;

	  return $selection;
	}

	function pre_confirmation_check() {
	  return false;
	}

	function confirmation() {
	  global $customer_id;

	  $customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_info = tep_db_fetch_array($customer_info_query);

	  return array('title' => sprintf(MODULE_PAYMENT_INTERKASSA_TEXT_DESCRIPTION_1, $customer_info['customers_email_address']));
	}

	function process_button() {
	  return false;
	}

	function get_email_footer() {
	  global $order, $customer_id, $currencies;

	  $email_footer = '';
	  if (is_object($order) && $this->enabled) {
		$this->update_status();

		$ot_total_value = 0;
		reset($order->totals);
		while (list(, $ot) = each($order->totals)) {
		  if ($ot['class']=='ot_total') {
			$ot_total_value = $ot['value'];
			break;
		  }
		}
		$ot_total_value = round($ot_total_value * $currencies->get_value(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY), $currencies->get_decimal_places(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY));
		$ot_total_value = str_replace(',', '.', $ot_total_value);

		$insert_id = $order->info['id'];

		$sign = md5(MODULE_PAYMENT_INTERKASSA_LOGIN . ':' . $ot_total_value . ':' . $insert_id . ':' . '' . ':' . tep_session_id() . ':' . MODULE_PAYMENT_INTERKASSA_PASSWORD);
		$payment_url = (MODULE_PAYMENT_INTERKASSA_MODE=='Test' ? 'https://test.interkassa.com/lib/payment.php' : 'https://interkassa.com/lib/payment.php') . '?ik_shop_id=' . urlencode(MODULE_PAYMENT_INTERKASSA_LOGIN) . '&ik_payment_amount=' . urlencode($ot_total_value) . '&ik_payment_id=' . $insert_id . '&ik_payment_desc=' . urlencode('Оплата заказа #' . $insert_id . ' в магазине ' . STORE_NAME) . '&ik_baggage_fields=' . tep_session_id() . '&ik_sign_hash=' . urlencode($sign);
		if (basename(SCRIPT_FILENAME)==FILENAME_ACCOUNT_HISTORY_INFO) {
		  $email_footer = str_replace('>[link]<', '>' . substr($payment_url, 0, 33) . '...' . substr($payment_url, -10) . '<', MODULE_PAYMENT_INTERKASSA_TEXT_EMAIL_FOOTER);
		  $email_footer = str_replace('[link]', $payment_url, $email_footer);
//		  $email_footer = str_replace(' target="_blank"', '', $email_footer);
		} else {
		  $email_footer = str_replace('[link]', $payment_url, MODULE_PAYMENT_INTERKASSA_TEXT_EMAIL_FOOTER);
		}
	  }

	  return $email_footer;
	}

	function before_process() {
//	  $this->email_footer = $this->get_email_footer();
	  return true;
	}

	function after_process() {
	  global $customer_id, $insert_id, $order_totals, $ik_code, $currency, $currencies;

	  if (tep_session_is_registered('ik_code')) tep_session_unregister('ik_code');

	  tep_session_register('ik_code');

	  $ot_total_value = 0;
	  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
		if ($order_totals[$i]['code']=='ot_total') {
		  $ot_total_value = $order_totals[$i]['value'];
		  break;
		}
	  }
	  $ot_total_value = round($ot_total_value * $currencies->get_value(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY), $currencies->get_decimal_places(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY));
	  $ot_total_value = str_replace(',', '.', $ot_total_value);

	  $code = md5(md5(microtime()) . md5(rand(0, 100000)));
	  $ik_code = base64_encode($code . '-' . $customer_id . '-' . $insert_id . '-' . $ot_total_value);

	  return false;
	}

	function output_error() {
	  return false;
	}

	function check() {
	  if (!isset($this->_check)) {
		$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_INTERKASSA_STATUS'");
		$this->_check = tep_db_num_rows($check_query);
	  }
	  return $this->_check;
	}

	function install() {
	  $this->remove();

	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Включить модуль INTERKASSA?', 'MODULE_PAYMENT_INTERKASSA_STATUS', 'False', 'Вы действительно хотите принимать платежи с помощью платежной системы INTERKASSA?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Тестовый режим', 'MODULE_PAYMENT_INTERKASSA_MODE', 'Test', 'Запустить модуль в реальном (Live) или тестовом (Test) режиме?', '6', '20', 'tep_cfg_select_option(array(\'Live\', \'Test\'), ', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Идентификатор магазина', 'MODULE_PAYMENT_INTERKASSA_LOGIN', '', 'Логин магазина в системе INTERKASSA.', '6', '30', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Пароль', 'MODULE_PAYMENT_INTERKASSA_PASSWORD', '', 'Используется интерфейсом инициализации оплаты', '6', '40', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Валюта системы', 'MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY', 'USD', 'Выберите валюту, которая используется в системе INTERKASSA по умолчанию', '6', '50', 'tep_cfg_pull_down_currencies(', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Валюта магазина', 'MODULE_PAYMENT_INTERKASSA_CURRENCY', 'UAH', 'Выберите валюту, которая установлена для магазина в системе INTERKASSA', '6', '60', 'tep_cfg_pull_down_currencies(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_INTERKASSA_ORDER_STATUS', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '70', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_PAYMENT_INTERKASSA_SORT_ORDER', '0', 'Порядок показа. Наменьшие показываются первыми.', '6', '80', now())");
	}

	function remove() {
	  tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
	  return array('MODULE_PAYMENT_INTERKASSA_STATUS', 'MODULE_PAYMENT_INTERKASSA_MODE', 'MODULE_PAYMENT_INTERKASSA_LOGIN', 'MODULE_PAYMENT_INTERKASSA_PASSWORD', 'MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY', 'MODULE_PAYMENT_INTERKASSA_CURRENCY', 'MODULE_PAYMENT_INTERKASSA_ORDER_STATUS', 'MODULE_PAYMENT_INTERKASSA_SORT_ORDER');
	}
  }
?>