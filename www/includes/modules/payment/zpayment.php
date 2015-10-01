<?php
  class zpayment {
	var $code, $title, $description, $enabled;

	function zpayment() {
	  global $order, $customer_id;

	  $this->code = 'zpayment';
	  $this->title = MODULE_PAYMENT_ZPAYMENT_TEXT_TITLE;
	  $this->description = MODULE_PAYMENT_ZPAYMENT_TEXT_DESCRIPTION;
	  $this->sort_order = MODULE_PAYMENT_ZPAYMENT_SORT_ORDER;
	  $this->enabled = ((MODULE_PAYMENT_ZPAYMENT_STATUS == 'True') ? true : false);

	  if ((int)MODULE_PAYMENT_ZPAYMENT_ORDER_STATUS > 0) {
		$this->order_status = MODULE_PAYMENT_ZPAYMENT_ORDER_STATUS;
	  }

	  if (is_object($order)) $this->update_status();

//	  $this->form_action_url = 'https://z-payment.ru/merchant.php';
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

	  return array('title' => sprintf(MODULE_PAYMENT_ZPAYMENT_TEXT_DESCRIPTION_1, $customer_info['customers_email_address']));
	}

	function process_button() {
	  return false;
	}

	function before_process() {
	  return false;
	}

	function after_process() {
	  global $customer_id, $insert_id, $zp_code, $order;

	  if (tep_session_is_registered('zp_code')) tep_session_unregister('zp_code');

	  tep_session_register('zp_code');

	  $code = md5(md5(microtime()) . md5(rand(0, 100000)));
	  $zp_code = base64_encode($code . '-' . $customer_id . '-' . $insert_id . '-' . $order->info['total']);

//	  echo $zp_code;
//	  die();

	  return false;
	}

	function output_error() {
	  return false;
	}

	function check() {
	  if (!isset($this->_check)) {
		$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ZPAYMENT_STATUS'");
		$this->_check = tep_db_num_rows($check_query);
	  }
	  return $this->_check;
	}

	function install() {
	  $this->remove();

	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Включить модуль Z-PAYMENT?', 'MODULE_PAYMENT_ZPAYMENT_STATUS', 'False', 'Вы действительно хотите принимать платежи с помощью платежной системы Z-PAYMENT?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Идентификатор магазина', 'MODULE_PAYMENT_ZPAYMENT_LMI_PAYEE_PURSE', '0', 'Целое число - идентификатор магазина в системе Z-PAYMENT Merchant. Назначается автоматически сервисом при создании нового магазина.', '6', '20', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ключ магазина Merchant Key', 'MODULE_PAYMENT_ZPAYMENT_MERCHANT_KEY', '0', 'Строка символов, добавляемая к реквизитам платежа, высылаемым продавцу вместе с оповещением. Эта строка используется для повышения надежности идентификации высылаемого оповещения. Содержание строки известно только сервису Z-PAYMENT Merchant и продавцу!', '6', '30', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Пароль инициализации магазина', 'MODULE_PAYMENT_ZPAYMENT_MERCHANT_PASS', '0', 'Строка символов, добавляемая к реквизитам платежа, отправляемым платежной системе. Эта строка используется для повышения надежности идентификации высылаемого оповещения. Содержание строки известно только сервису Z-PAYMENT Merchant и продавцу!', '6', '40', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_ZPAYMENT_ORDER_STATUS', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '50', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_PAYMENT_ZPAYMENT_SORT_ORDER', '0', 'Порядок показа. Наменьшие показываются первыми.', '6', '60', now())");
	}

	function remove() {
	  tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
	  return array('MODULE_PAYMENT_ZPAYMENT_STATUS', 'MODULE_PAYMENT_ZPAYMENT_LMI_PAYEE_PURSE', 'MODULE_PAYMENT_ZPAYMENT_MERCHANT_KEY', 'MODULE_PAYMENT_ZPAYMENT_MERCHANT_PASS', 'MODULE_PAYMENT_ZPAYMENT_ORDER_STATUS', 'MODULE_PAYMENT_ZPAYMENT_SORT_ORDER');
	}
  }
?>