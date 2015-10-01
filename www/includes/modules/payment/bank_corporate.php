<?php
  class bank_corporate {
    var $code, $title, $description, $enabled, $email_footer;

// class constructor
    function bank_corporate() {
	  global $customer_id;

      $this->code = 'bank_corporate';
      $this->title = MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_BANK_CORPORATE_SORT_ORDER;
      $this->email_footer = MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER;
      $this->enabled = ((MODULE_PAYMENT_BANK_CORPORATE_STATUS == 'True') ? true : false);
	  if ($this->enabled) {
		$customer_type_query = tep_db_query("select customers_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_type = tep_db_fetch_array($customer_type_query);
		if ($customer_type['customers_type']!='corporate') $this->enabled = false;
	  }

      if ((int)MODULE_PAYMENT_BANK_CORPORATE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_BANK_CORPORATE_ORDER_STATUS_ID;
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

	  if (SHOP_ID==14 || SHOP_ID==16) {
        $selection['fields'] = array(array('title' => MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER,
                                           'field' => tep_draw_input_field('purchase_order', '', 'size="10" maxlength="20"')));
	  }

	  return $selection;
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
	  global $HTTP_POST_VARS;

	  $confirmation = array();
	  if (defined('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION_1')) $confirmation['fields'][] = array('title' => MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION_1);
      elseif (tep_not_null(MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION)) $confirmation['fields'][] = array('title' => MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION);
      else $confirmation = false;

	  if ((SHOP_ID==14 || SHOP_ID==16) && $confirmation) {
		$confirmation['fields'][] = array('title' => MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER,
                                          'field' => $HTTP_POST_VARS['purchase_order']);
	  }

	  return $confirmation;
    }

    function process_button() {
      if (SHOP_ID==14 || SHOP_ID==16) {
        global $HTTP_POST_VARS;

        $process_button_string = tep_draw_hidden_field('purchase_order', tep_sanitize_string($HTTP_POST_VARS['purchase_order']));

        return $process_button_string;
      }

      return false;
    }

    function before_process() {
      if (SHOP_ID==14 || SHOP_ID==16) {
        global $HTTP_POST_VARS, $order;

		$order->info['code'] = tep_sanitize_string($HTTP_POST_VARS['purchase_order']);
		$this->email_footer = MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER . ' ' . $order->info['code'];

        return $process_button_string;
      }

      return false;
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_BANK_CORPORATE_STATUS'");
        $this->check = tep_db_num_rows($check_query);
      }
      return $this->check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('ѕозволить оплачивать заказ банковским переводом', 'MODULE_PAYMENT_BANK_CORPORATE_STATUS', 'True', '¬ы хотите принимать оплату за заказы посредством банковского перевода?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ќазвание оплаты', 'MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', 'Ѕезналичный расчет', 'Ќазвание метода оплаты, которое пользователь увидит на сайте', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('ќписание оплаты', 'MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', 'ѕосле завершени€ оформлени€ заказа ¬ы сможете распечатать заполненный бланк платежного поручени€.', ' раткое описание метода оплаты, которое пользователь увидит на сайте', '6', '30', 'tep_cfg_textarea(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('—татус заказа', 'MODULE_PAYMENT_BANK_CORPORATE_ORDER_STATUS_ID', '0', '¬сем заказам с этим типом оплаты устанавливать статус', '6', '60', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ѕор€док вывода', 'MODULE_PAYMENT_BANK_CORPORATE_SORT_ORDER', '0', 'ѕор€док показа. Ќаменьшие показываютс€ первыми.', '6', '70', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_PAYMENT_BANK_CORPORATE_STATUS', 'MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', 'MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', 'MODULE_PAYMENT_BANK_CORPORATE_ORDER_STATUS_ID', 'MODULE_PAYMENT_BANK_CORPORATE_SORT_ORDER');

      return $keys;
    }
  }
?>