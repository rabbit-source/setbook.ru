<?php
  class cod {
    var $code, $title, $description, $enabled;

// class constructor
    function cod() {
      global $order, $customer_id;

      $this->code = 'cod';
      $this->title = MODULE_PAYMENT_COD_TEXT_TITLE;
      $this->description = (mb_strpos($order->info['shipping_method'], 'Самовывоз', 0, 'CP1251')!==false ? MODULE_PAYMENT_COD_TEXT_DESCRIPTION_1 : MODULE_PAYMENT_COD_TEXT_DESCRIPTION);
      $this->sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_COD_STATUS == 'True') ? true : false);

	  $customer_status_check_query = tep_db_query("select customers_status from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_status_check = tep_db_fetch_array($customer_status_check_query);
	  if ($customer_status_check['customers_status'] < 1) $this->enabled = false;

      if ((int)MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

	  if (is_object($order)) {
		reset($order->products);
		while (list(, $order_product) = each($order->products)) {
		  if ($order_product['periodicity'] > 0) {
			$this->enabled = false;
			break;
		  }
		}
	  }

	  if ($order->content_type == 'virtual') $this->enabled = false;
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_COD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      return array('id' => $this->code,
                   'module' => $this->title,
				   'description' => $this->description);
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
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_COD_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Позволить оплачивать заказы наличными', 'MODULE_PAYMENT_COD_STATUS', 'True', 'Вы хотите принимать оплату за заказы наличными?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Регион', 'MODULE_PAYMENT_COD_ZONE', '0', 'Если выбран регион, позволять оплату наличными только для этого региона.', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Название оплаты', 'MODULE_PAYMENT_COD_TEXT_TITLE', 'Наличными курьеру', 'Название метода оплаты, которое пользователь увидит на сайте', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Послесловие в письмо-подтверждение заказа', 'MODULE_PAYMENT_COD_TEXT_EMAIL_FOOTER', '', 'Введенный текст будет добавлен в конец письма-подтверждения заказа', '6', '50', 'tep_cfg_textarea(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Статус заказа', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Всем заказам с этим типом оплаты устанавливать статус', '6', '60', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода.', 'MODULE_PAYMENT_COD_SORT_ORDER', '0', 'Порядок вывода. Наименьшие значения показываются первыми.', '6', '70', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_COD_STATUS', 'MODULE_PAYMENT_COD_ZONE', 'MODULE_PAYMENT_COD_TEXT_TITLE', 'MODULE_PAYMENT_COD_TEXT_EMAIL_FOOTER', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', 'MODULE_PAYMENT_COD_SORT_ORDER');
    }
  }
?>