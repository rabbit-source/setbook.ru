<?php
  class forward {
    var $code, $title, $description, $enabled;

// class constructor
    function forward() {
      global $order;

      $this->code = 'forward';
      $this->title = MODULE_PAYMENT_FORWARD_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_FORWARD_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_FORWARD_SORT_ORDER;
      $this->email_footer = MODULE_PAYMENT_FORWARD_TEXT_EMAIL_FOOTER;
      $this->enabled = ((MODULE_PAYMENT_FORWARD_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_FORWARD_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_FORWARD_ORDER_STATUS_ID;
      }

	  if ($order->content_type == 'virtual') $this->enabled = false;

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_FORWARD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_FORWARD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {
        if ($order->content_type == 'virtual') {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
	  global $order, $currencies, $currency;

	  $selection = array('id' => $this->code,
						 'module' => $this->title,
						 'description' => $this->description);

	  if ((float)MODULE_PAYMENT_FORWARD_MAX_SUM > 0) {
		if ($order->info['subtotal']*$currencies->currencies[$currency]['value'] > MODULE_PAYMENT_FORWARD_MAX_SUM) {
		  $selection['error'] = sprintf(MODULE_PAYMENT_FORWARD_MAX_SUM_ERROR, $currencies->format(MODULE_PAYMENT_FORWARD_MAX_SUM, false));
		}
	  }

      return $selection;
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
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

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_FORWARD_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Позволить оплачивать заказы наложенным платежом', 'MODULE_PAYMENT_FORWARD_STATUS', 'True', 'Вы хотите принимать оплату за заказы наложенным платежом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Регион', 'MODULE_PAYMENT_FORWARD_ZONE', '0', 'Если выбран регион, позволять оплату наложенным платежом только для этого региона.', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Название оплаты', 'MODULE_PAYMENT_FORWARD_TEXT_TITLE', 'Наложенным платежом', 'Название метода оплаты, которое пользователь увидит на сайте', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Описание оплаты', 'MODULE_PAYMENT_FORWARD_TEXT_DESCRIPTION', 'Оплата заказа при получении на почте', 'Краткое описание метода оплаты, которое пользователь увидит на сайте', '6', '40', 'tep_cfg_textarea(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Максимальная сумма заказа', 'MODULE_PAYMENT_FORWARD_MAX_SUM', '0', 'Укажите максимальную сумму заказа, до которой возможна доставка наложенным платежом (0 если без ограничений)', '6', '20', 'currencies->format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_FORWARD_ORDER_STATUS_ID', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '60', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода.', 'MODULE_PAYMENT_FORWARD_SORT_ORDER', '0', 'Порядок вывода. Наименьшие значения показываются первыми.', '6', '70', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_FORWARD_STATUS', 'MODULE_PAYMENT_FORWARD_ZONE', 'MODULE_PAYMENT_FORWARD_TEXT_TITLE', 'MODULE_PAYMENT_FORWARD_TEXT_DESCRIPTION', 'MODULE_PAYMENT_FORWARD_MAX_SUM', 'MODULE_PAYMENT_FORWARD_ORDER_STATUS_ID', 'MODULE_PAYMENT_FORWARD_SORT_ORDER');
    }
  }
?>