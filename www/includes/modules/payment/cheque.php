<?php
  class cheque {
    var $code, $title, $description, $sort_order, $email_footer, $enabled;

// class constructor
    function cheque() {
	  global $customer_id;

      $this->code = 'cheque';
      $this->title = MODULE_PAYMENT_CHEQUE_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_CHEQUE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_CHEQUE_SORT_ORDER;
      $this->email_footer = trim(MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER);
      $this->enabled = ((MODULE_PAYMENT_CHEQUE_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID;
      }
    }

// class methods
    function javascript_validation() {
      return false;
    }

    function selection() {
      $selection = array('id' => $this->code,
                         'module' => $this->title,
						 'description' => $this->description);

      $selection['fields'] = array();
	  if (tep_not_null(MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE)) {
	    $selection['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE,
									   'field' => tep_draw_radio_field('check_account_type', 'Checking', true) . MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_CHECKING . '<br />' . "\n" . tep_draw_radio_field('check_account_type', 'Savings') . MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_SAVINGS);
	  }
	  $selection['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_BANK_NAME,
                                     'field' => tep_draw_input_field('check_bank_name'));
	  $selection['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER,
                                     'field' => tep_draw_input_field('check_routing_number') . ' ' . MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER_TEXT);
	  $selection['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER,
                                     'field' => tep_draw_input_field('check_account_number') . ' ' . MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER_TEXT);

      return $selection;
    }

    function pre_confirmation_check() {
	  global $HTTP_POST_VARS, $messageStack;

	  $account_number = preg_replace('/[^\d]/', '', $HTTP_POST_VARS['check_account_number']);
	  $routing_number = preg_replace('/[^\d]/', '', $HTTP_POST_VARS['check_routing_number']);

	  if ( (empty($HTTP_POST_VARS['check_account_type']) && tep_not_null(MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE)) || empty($HTTP_POST_VARS['check_bank_name']) || empty($HTTP_POST_VARS['check_routing_number']) || empty($HTTP_POST_VARS['check_account_number'])) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ALL_FIELDS_REQUIRED;
      } elseif (strlen($account_number) < 7) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ACCOUNT_NUMBER_ERROR;
	  } elseif (strlen($routing_number) < 5) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_ERROR;
	  } elseif (strlen($routing_number) == 9) {
		$check_number = (3 * ($routing_number[0] + $routing_number[3] + $routing_number[6]) + 7 * ($routing_number[1] + $routing_number[4] + $routing_number[7]) + 1 * ($routing_number[2] + $routing_number[5] + $routing_number[8]));
//		if (substr($check_number, -1)!='0') $error = MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_CHECK_ERROR;
//		else $error = '';
	  }

	  if (tep_not_null($error)) {
		$messageStack->add_session('header', $error);

		tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'check_account_type=' . urlencode($HTTP_POST_VARS['check_account_type']) . '&check_bank_name=' . $HTTP_POST_VARS['check_bank_name'] . '&check_routing_number=' . $HTTP_POST_VARS['check_routing_number'] . '&check_account_number=' . $HTTP_POST_VARS['check_account_number'], 'SSL', true, false));
	  }

      return false;
    }

    function confirmation() {
	  global $HTTP_POST_VARS;

	  $confirmation['fields'] = array();
	  if (tep_not_null(MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE)) {
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE,
										  'field' => $HTTP_POST_VARS['check_account_type']);
	  }
	  $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_BANK_NAME,
										'field' => $HTTP_POST_VARS['check_bank_name']);
	  $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER,
										'field' => $HTTP_POST_VARS['check_routing_number']);
	  $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER,
										'field' => $HTTP_POST_VARS['check_account_number']);
	  $confirmation['fields'][] = array('title' => '<br /><span class="errorText">' . $this->description . '</span>');

      return $confirmation;
    }

    function process_button() {
	  global $HTTP_POST_VARS;

	  $process_button_string = tep_draw_hidden_field('check_account_type', $HTTP_POST_VARS['check_account_type']) .
							   tep_draw_hidden_field('check_bank_name', $HTTP_POST_VARS['check_bank_name']) .
							   tep_draw_hidden_field('check_routing_number', $HTTP_POST_VARS['check_routing_number']) .
							   tep_draw_hidden_field('check_account_number', $HTTP_POST_VARS['check_account_number']);

	  return $process_button_string;
    }

    function before_process() {
	  global $HTTP_POST_VARS, $messageStack;

	  $account_number = preg_replace('/[^\d]/', '', $HTTP_POST_VARS['check_account_number']);
	  $routing_number = preg_replace('/[^\d]/', '', $HTTP_POST_VARS['check_routing_number']);

	  if ( (empty($HTTP_POST_VARS['check_account_type']) && tep_not_null(MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE)) || empty($HTTP_POST_VARS['check_bank_name']) || empty($HTTP_POST_VARS['check_routing_number']) || empty($HTTP_POST_VARS['check_account_number'])) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ALL_FIELDS_REQUIRED;
      } elseif (strlen($account_number) < 7) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ACCOUNT_NUMBER_ERROR;
	  } elseif (strlen($routing_number) < 5) {
		$error = MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_ERROR;
	  } else {
		$check_number = (3 * ($routing_number[0] + $routing_number[3] + $routing_number[6]) + 7 * ($routing_number[1] + $routing_number[4] + $routing_number[7]) + 1 * ($routing_number[2] + $routing_number[5] + $routing_number[8]));
//		if (substr($check_number, -1)!='0') $error = MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_CHECK_ERROR;
//		else $error = '';
	  }

	  if (tep_not_null($error)) {
		$messageStack->add_session('header', $error);

		tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'check_account_type=' . urlencode($HTTP_POST_VARS['check_account_type']) . '&check_bank_name=' . $HTTP_POST_VARS['check_bank_name'] . '&check_routing_number=' . $HTTP_POST_VARS['check_routing_number'] . '&check_account_number=' . $HTTP_POST_VARS['check_account_number'], 'SSL', true, false));
	  } else {
		$order->info['check_account_type'] = $HTTP_POST_VARS['check_account_type'];
		$order->info['check_bank_name'] = $HTTP_POST_VARS['check_bank_name'];
		$order->info['check_routing_number'] = $HTTP_POST_VARS['check_routing_number'];
		$order->info['check_account_number'] = $HTTP_POST_VARS['check_account_number'];
	  }
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CHEQUE_STATUS'");
        $this->check = tep_db_num_rows($check_query);
      }
      return $this->check;
    }

    function install() {
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Позволить оплачивать заказ чеком?', 'MODULE_PAYMENT_CHEQUE_STATUS', 'True', 'Вы хотите принимать оплату за заказы чеками пользователей?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '60', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_PAYMENT_CHEQUE_SORT_ORDER', '0', 'Порядок показа. Наменьшие показываются первыми.', '6', '70', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_PAYMENT_CHEQUE_STATUS', 'MODULE_PAYMENT_CHEQUE_ORDER_STATUS_ID', 'MODULE_PAYMENT_CHEQUE_SORT_ORDER');

      return $keys;
    }
  }
?>