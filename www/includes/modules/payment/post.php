<?php
  class post {
    var $code, $title, $description, $enabled;

// class constructor
    function post() {
	  global $customer_id;

      $this->code = 'post';
      $this->title = MODULE_PAYMENT_POST_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_POST_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_POST_SORT_ORDER;
      $this->email_footer = MODULE_PAYMENT_POST_TEXT_EMAIL_FOOTER;
      $this->enabled = ((MODULE_PAYMENT_POST_STATUS == 'True') ? true : false);
	  if ($this->enabled) {
		$customer_type_query = tep_db_query("select customers_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_type = tep_db_fetch_array($customer_type_query);
		if ($customer_type['customers_type']!='private') $this->enabled = false;
	  }

      if ((int)MODULE_PAYMENT_POST_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_POST_ORDER_STATUS_ID;
      }
    }

// class methods
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
      $confirmation = array('title' => MODULE_PAYMENT_POST_TEXT_DESCRIPTION);
      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
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
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_POST_STATUS'");
        $this->check = tep_db_num_rows($check_query);
      }
      return $this->check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Позволить оплачивать заказ банковским переводом', 'MODULE_PAYMENT_POST_STATUS', 'True', 'Вы хотите принимать оплату за заказы посредством почтового перевода?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Название оплаты', 'MODULE_PAYMENT_POST_TEXT_TITLE', 'Почтовым переводом', 'Название метода оплаты, которое пользователь увидит на сайте', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Описание оплаты', 'MODULE_PAYMENT_POST_TEXT_DESCRIPTION', 'После завершения оформления заказа Вы сможете распечатать заполненный бланк квитанции.', 'Краткое описание метода оплаты, которое пользователь увидит на сайте', '6', '30', 'tep_cfg_textarea(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_POST_ORDER_STATUS_ID', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '60', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_PAYMENT_POST_SORT_ORDER', '0', 'Порядок показа. Наменьшие показываются первыми.', '6', '70', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_PAYMENT_POST_STATUS', 'MODULE_PAYMENT_POST_TEXT_TITLE', 'MODULE_PAYMENT_POST_TEXT_DESCRIPTION', 'MODULE_PAYMENT_POST_ORDER_STATUS_ID', 'MODULE_PAYMENT_POST_SORT_ORDER');

      return $keys;
    }
  }
?>