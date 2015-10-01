<?php
  class onpay {
	var $code, $title, $description, $enabled, $email_footer;

	function onpay() {
	  global $customer_id, $currencies, $order;

	  $this->code = 'onpay';
	  $this->title = MODULE_PAYMENT_ONPAY_TEXT_TITLE;
	  $this->description = MODULE_PAYMENT_ONPAY_TEXT_DESCRIPTION;
	  $this->sort_order = MODULE_PAYMENT_ONPAY_SORT_ORDER;
	  $this->enabled = ((MODULE_PAYMENT_ONPAY_STATUS == 'Да') ? true : ($customer_id==2 ? true : false));

	  if ((int)MODULE_PAYMENT_ONPAY_ORDER_STATUS > 0) {
		$this->order_status = MODULE_PAYMENT_ONPAY_ORDER_STATUS;
	  }

	  if (is_object($order)) {
		$this->update_status();

		$ot_total_value = 0;
		reset($order->totals);
		while (list(, $ot) = each($order->totals)) {
		  if ($ot['class']=='ot_total') {
			$ot_total_value = str_replace(',', '.', sprintf("%01.1f", round($ot['value'], $currencies->get_decimal_places($order->info['currency']))));
			break;
		  }
		}

		$insert_id = $order->info['id'];
		$sign = strtoupper(md5('fix;' . $ot_total_value . ';' . $order->info['currency'] . ';' . $insert_id . ';yes;' . MODULE_PAYMENT_ONPAY_PASSWORD1));

		$user_phone = $order->customer['telephone'];
		$user_phone_1 = preg_replace('/[^\d]/', '', $user_phone);
		if (strlen($user_phone_1) == 7) $user_phone = '+7495' . $user_phone_1;
		elseif (strlen($user_phone_1) == 11 && substr($user_phone_1, 0, 1)=='8') $user_phone = '+7' . substr($user_phone_1, 1);
		elseif (strlen($user_phone_1) == 11 && substr($user_phone_1, 0, 1)=='7') $user_phone = '+' . $user_phone_1;
		elseif (strlen($user_phone_1) == 10) $user_phone = '+7' . $user_phone_1;

		$payment_url = 'https://secure.onpay.ru/pay/' . MODULE_PAYMENT_ONPAY_LOGIN . '?pay_mode=fix&pay_for=' . $insert_id . '&price=' . urlencode($ot_total_value) . '&currency=' . urlencode($order->info['currency']) . '&convert=yes&md5=' . $sign . '&user_email=' . urlencode($order->customer['email_address']) . '&user_phone=' . urlencode($user_phone) . '&url_success=' . MODULE_PAYMENT_ONPAY_SUCCESS . '&url_fail=' . MODULE_PAYMENT_ONPAY_FAIL . '&note=' . urlencode('Оплата заказа #' . $insert_id . ' в магазине ' . STORE_NAME);
		if (basename(SCRIPT_FILENAME)==FILENAME_ACCOUNT_HISTORY_INFO) {
		  $this->email_footer = str_replace('>[link]<', '>' . substr($payment_url, 0, 33) . '...' . substr($payment_url, -10) . '<', MODULE_PAYMENT_ROBOX_TEXT_EMAIL_FOOTER);
		  $this->email_footer = str_replace('[link]', $payment_url, $this->email_footer);
//		  $this->email_footer = str_replace(' target="_blank"', '', $this->email_footer);
		} else {
		  $this->email_footer = str_replace('[link]', $payment_url, MODULE_PAYMENT_ROBOX_TEXT_EMAIL_FOOTER);
		}
	  }
    }

	function update_status() {
	}

	function javascript_validation() {
	  return false;
	}

	function selection() {
	  return array('id' => $this->code,
				   'module' => $this->title,
				   'description' => $this->description);
	}

	function pre_confirmation_check() {
	  return false;
	}

	function confirmation() {
	  global $customer_id;

	  $customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_info = tep_db_fetch_array($customer_info_query);

	  return array('title' => sprintf(MODULE_PAYMENT_ONPAY_TEXT_DESCRIPTION_1, $customer_info['customers_email_address']));
	}

	function process_button() {
	  return false;
	}

	function before_process() {
	  return false;
	}

	function after_process() {
	  global $customer_id, $insert_id, $order_totals, $rx_code, $currency, $currencies;

	  if (tep_session_is_registered('op_code')) tep_session_unregister('op_code');

	  tep_session_register('op_code');

	  $ot_total_value = 0;
	  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
		if ($order_totals[$i]['code']=='ot_total') {
		  $ot_total_value = str_replace(',', '.', round($order_totals[$i]['value'], $currencies->get_decimal_places($currency)));
		  break;
		}
	  }

	  $code = md5(md5(microtime()) . md5(rand(0, 100000)));
	  $op_code = base64_encode($code . '-' . $customer_id . '-' . $insert_id . '-' . $ot_total_value);

	  return false;
	}

	function output_error() {
	  return false;
	}

	function check() {
	  if (!isset($this->_check)) {
		$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ONPAY_STATUS'");
		$this->_check = tep_db_num_rows($check_query);
	  }
	  return $this->_check;
	}

	function install() {
	  $this->remove();

	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Включить модуль', 'MODULE_PAYMENT_ONPAY_STATUS', 'Да', 'Активировать прием платежей через систему OnPay.ru', '6', '3', 'tep_cfg_select_option(array(\'Да\', \'Нет\'), ', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Логин в системе OnPay.ru', 'MODULE_PAYMENT_ONPAY_LOGIN', '', 'Ваше Имя пользователя в системе OnPay.ru', '6', '4', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ключ API IN', 'MODULE_PAYMENT_ONPAY_PASSWORD1', '', 'Секретный ключ API IN, указанный в личном кабинете OnPay.ru', '6', '5', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Курс валюты сайта', 'MODULE_PAYMENT_ONPAY_KURS', '1', 'Отношение валюты, используемой на сайте, к валюте приема платежей через OnPay.ru (рублю)', '6', '6', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_ONPAY_ORDER_STATUS', '0', 'Статус заказа после успешной оплаты', '6', '7', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Очередность при сортировке', 'MODULE_PAYMENT_ONPAY_SORT_ORDER', '0', 'Порядок вывода. Чем число меньше, тем больше приоритет.', '6', '8', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('URL скрипта для API-запросов', 'MODULE_PAYMENT_ONPAY_RESULT', '" . HTTP_SERVER . DIR_WS_CATALOG . "onpay.php', 'Параметр \"URL API\" в личном кабинете OnPay.ru', '6', '9', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Адрес при успешной оплате', 'MODULE_PAYMENT_ONPAY_SUCCESS', '" . HTTP_SERVER . DIR_WS_CATALOG . "checkout_success.php', 'URL страницы для перехода после успешной оплаты', '6', '10', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Адрес при ошибке оплаты', 'MODULE_PAYMENT_ONPAY_FAIL', '" . HTTP_SERVER . DIR_WS_CATALOG . "checkout_payment.php', 'URL страницы для перехода при неуспешной оплате', '6', '11', now())");
	}

	function remove() {
	  tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
	  return array('MODULE_PAYMENT_ONPAY_STATUS', 'MODULE_PAYMENT_ONPAY_LOGIN', 'MODULE_PAYMENT_ONPAY_PASSWORD1', 'MODULE_PAYMENT_ONPAY_KURS', 'MODULE_PAYMENT_ONPAY_ORDER_STATUS', 'MODULE_PAYMENT_ONPAY_SORT_ORDER', 'MODULE_PAYMENT_ONPAY_RESULT', 'MODULE_PAYMENT_ONPAY_SUCCESS', 'MODULE_PAYMENT_ONPAY_FAIL');
	}
  }
?>