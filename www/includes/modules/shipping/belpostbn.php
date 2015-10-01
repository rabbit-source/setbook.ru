<?php
  class belpostbn {
    var $code, $title, $description, $enabled;

// class constructor
    function belpostbn() {
	  global $order, $customer_id;

      $this->code = 'belpostbn';
      $this->title = MODULE_SHIPPING_BELPOSTBN_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_BELPOSTBN_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_BELPOSTBN_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_BELPOSTBN_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  $customer_status_check_query = tep_db_query("select customers_status from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_status_check = tep_db_fetch_array($customer_status_check_query);
	  if ($customer_status_check['customers_status']==0) $this->enabled = false;

	  if ($this->enabled) {
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['periodicity'] > 0) {
			  $this->enabled = false;
			  break;
			}
		  }
		}
	  }

	  if ($this->enabled) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '20' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_BELPOSTBN_ZONE_1 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_BELPOSTBN_ZONE_1 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_BELPOSTBN_ZONE_2 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_BELPOSTBN_ZONE_2 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_BELPOSTBN_ZONE_3 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_BELPOSTBN_ZONE_3 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_BELPOSTBN_ZONE_4 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_BELPOSTBN_ZONE_4 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
    }

// class methods
    function quote($method = '') {
      global $order, $cart, $shipping_weight, $currencies, $currency;

	  $shipping_cost = 0;
	  if (empty($order->delivery['postcode'])) {
		$this->quotes['error'] = MODULE_SHIPPING_BELPOSTBN_ERROR_NO_ZIPCODE_FOUND;
//		$shipping_method = MODULE_SHIPPING_BELPOSTBN_ERROR_NO_ZIPCODE_FOUND;
	  } else {
		$total_sum = tep_round($cart->total*$currencies->currencies[$currency]['value'], $currencies->currencies[$currency]['decimal_places']);
		$base_shipping = str_replace(',', '.', MODULE_SHIPPING_BELPOSTBN_COST);
		$shipping_cost = ceil($shipping_weight*1000/500) * $base_shipping;
		$eval_cost = str_replace(',', '.', MODULE_SHIPPING_BELPOSTBN_EVAL_COST);
		$add_cost = str_replace(',', '.', MODULE_SHIPPING_BELPOSTBN_ADDITIONAL_COST);
		$risk_cost = trim(str_replace('%', '', str_replace(',', '.', MODULE_SHIPPING_BELPOSTBN_RISK_COST)));
		$transfer_cost = trim(str_replace('%', '', str_replace(',', '.', MODULE_SHIPPING_BELPOSTBN_TRANSFER_COST)));
		if ((float)$eval_cost > 0) $shipping_cost += $total_sum*$eval_cost;
		if ((float)$add_cost > 0) $shipping_cost += $add_cost;
		if ((float)$risk_cost > 0) $shipping_cost += $total_sum*$risk_cost/100;
		if ((float)$transfer_cost > 0) $shipping_cost += $total_sum*$transfer_cost/100;
		$shipping_method = sprintf(MODULE_SHIPPING_BELPOSTBN_TEXT_WEIGHT, $shipping_weight);
	  }

	  if ($shipping_cost > 0) {
		$shipping_cost = round($shipping_cost/50)*50;
		$shipping_cost = $shipping_cost/$currencies->currencies[$currency]['value'];
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_BELPOSTBN_TEXT_TITLE;
	  $this->quotes['methods'] = array(array('id' => $this->code,
											 'title' => $shipping_method,
											 'cost' => $shipping_cost));

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_BELPOSTBN_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_BELPOSTBN_STATUS', 'True', 'Вы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('1-я зона невозможности доставки', 'MODULE_SHIPPING_BELPOSTBN_ZONE_1', '', 'Укажите 1-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('2-я зона невозможности доставки', 'MODULE_SHIPPING_BELPOSTBN_ZONE_2', '', 'Укажите 2-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '30', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('3-я зона невозможности доставки', 'MODULE_SHIPPING_BELPOSTBN_ZONE_3', '', 'Укажите 3-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '40', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('4-я зона невозможности доставки', 'MODULE_SHIPPING_BELPOSTBN_ZONE_4', '', 'Укажите 4-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '50', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость доставки', 'MODULE_SHIPPING_BELPOSTBN_COST', '1750', 'Укажите (в бел. рублях) стоимость доставки базового веса (500г)', '6', '60', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Оценочная стоимость', 'MODULE_SHIPPING_BELPOSTBN_EVAL_COST', '0.03', 'Укажите (в бел. рублях) оценочную стоимость (в расчете на 1 руб. стоимости заказа)', '6', '70', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Комиссия за риски', 'MODULE_SHIPPING_BELPOSTBN_RISK_COST', '7', 'Укажите (в процентах) размер комиссии за риски', '6', '80', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Комиссия за перевод', 'MODULE_SHIPPING_BELPOSTBN_TRANSFER_COST', '5', 'Укажите (в процентах) размер комиссии за обратный перевод', '6', '90', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость упаковки', 'MODULE_SHIPPING_BELPOSTBN_ADDITIONAL_COST', '0', 'Указанная цифра (в бел. рублях) будет добавлена к рассчитанной стоимости доставки', '6', '100', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_BELPOSTBN_SORT_ORDER', '0', 'Порядок вывода этого вида доставки на сайте.', '6', '110', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_BELPOSTBN_STATUS', 'MODULE_SHIPPING_BELPOSTBN_ZONE_1', 'MODULE_SHIPPING_BELPOSTBN_ZONE_2', 'MODULE_SHIPPING_BELPOSTBN_ZONE_3', 'MODULE_SHIPPING_BELPOSTBN_ZONE_4', 'MODULE_SHIPPING_BELPOSTBN_COST', 'MODULE_SHIPPING_BELPOSTBN_EVAL_COST', 'MODULE_SHIPPING_BELPOSTBN_RISK_COST', 'MODULE_SHIPPING_BELPOSTBN_TRANSFER_COST', 'MODULE_SHIPPING_BELPOSTBN_ADDITIONAL_COST', 'MODULE_SHIPPING_BELPOSTBN_SORT_ORDER');

      return $keys;
    }
  }
?>