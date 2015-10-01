<?php
  class ukrpost {
    var $code, $title, $description, $enabled;

// class constructor
    function ukrpost() {
	  global $order;

      $this->code = 'ukrpost';
      $this->title = MODULE_SHIPPING_UKRPOST_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_UKRPOST_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_UKRPOST_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_UKRPOST_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  if ($this->enabled) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '20' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_UKRPOST_ZONE_1 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UKRPOST_ZONE_1 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_UKRPOST_ZONE_2 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UKRPOST_ZONE_2 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_UKRPOST_ZONE_3 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UKRPOST_ZONE_3 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_UKRPOST_ZONE_4 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UKRPOST_ZONE_4 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
    }

// class methods
    function quote($method = '') {
      global $order, $cart, $shipping_weight, $currencies, $currency;

	  $postcode_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_id = '" . tep_db_input(tep_db_prepare_input($order->delivery['postcode'])) . "'");
	  $postcode_check = tep_db_fetch_array($postcode_check_query);
	  $shipping_cost = 0;
//	  if (empty($order->delivery['postcode'])) {
//		$this->quotes['error'] = MODULE_SHIPPING_UKRPOST_NO_ZIPCODE_FOUND;
//	  } elseif ($postcode_check['total'] < 1) {
//		$this->quotes['error'] = MODULE_SHIPPING_UKRPOST_NO_ZIPCODE_EXISTS;
//	  } else {
		$order_total_sum = 0;
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['weight'] > 0 && $order_product['periodicity'] < 1) $order_total_sum += $order_product['final_price'] * $order_product['qty'];
		  }
		}
		$persentage = str_replace(',', '.', MODULE_SHIPPING_UKRPOST_COST);
		$min_cost = str_replace(',', '.', MODULE_SHIPPING_UKRPOST_MIN_COST);
		$add_cost = str_replace(',', '.', MODULE_SHIPPING_UKRPOST_ADDITIONAL_COST);
		if ($order_total_sum > 0) {
		  $total_sum = str_replace(',', '.', round($order_total_sum*$currencies->get_value($currency), $currencies->get_decimal_places($currency)));
		  if ((float)$persentage > 0) $shipping_cost = $total_sum*$persentage/100;
		  if ((float)$min_cost > 0 && $shipping_cost < $min_cost) $shipping_cost = $min_cost;
		  if ((float)$add_cost > 0) $shipping_cost += $add_cost;
		}
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			$temp_shipping_cost = 0;
			if ($order_product['weight'] > 0 && $order_product['periodicity'] > 0) {
			  $total_sum = str_replace(',', '.', round($order_product['final_price']*$currencies->get_value($currency), $currencies->get_decimal_places($currency)));
			  if ((float)$persentage > 0) $temp_shipping_cost = $total_sum*$persentage/100;
			  if ((float)$min_cost > 0 && $temp_shipping_cost < $min_cost) $temp_shipping_cost = $min_cost;
			  if ((float)$add_cost > 0) $temp_shipping_cost += $add_cost;
			}
			$shipping_cost += $temp_shipping_cost * $order_product['qty'];
		  }
		}
		$shipping_method = sprintf(MODULE_SHIPPING_UKRPOST_TEXT_WEIGHT, $shipping_weight);
//	  }

	  if ($shipping_cost > 0) {
		$shipping_cost = $shipping_cost/$currencies->get_value($currency);
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_UKRPOST_TEXT_TITLE;
	  $this->quotes['methods'] = array(array('id' => $this->code,
											 'title' => $shipping_method,
											 'cost' => $shipping_cost));

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_UKRPOST_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('–азрешить этот способ доставки', 'MODULE_SHIPPING_UKRPOST_STATUS', 'True', '¬ы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('1-€ зона невозможности доставки', 'MODULE_SHIPPING_UKRPOST_ZONE_1', '', '”кажите 1-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('2-€ зона невозможности доставки', 'MODULE_SHIPPING_UKRPOST_ZONE_2', '', '”кажите 2-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('3-€ зона невозможности доставки', 'MODULE_SHIPPING_UKRPOST_ZONE_3', '', '”кажите 3-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('4-€ зона невозможности доставки', 'MODULE_SHIPPING_UKRPOST_ZONE_4', '', '”кажите 4-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('—тоимость доставки', 'MODULE_SHIPPING_UKRPOST_COST', '20', '”кажите (в процентах) стоимость доставки от стоимости заказа', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ћинимальна€ стоимость доставки', 'MODULE_SHIPPING_UKRPOST_MIN_COST', '20', '”кажите (в гривнах) минимальную стоимость доставки', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ƒобавочна€ сумма', 'MODULE_SHIPPING_UKRPOST_ADDITIONAL_COST', '0', '”казанна€ цифра (в гривнах) будет добавлена к расчитанной стоимости доставки', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ѕор€док вывода', 'MODULE_SHIPPING_UKRPOST_SORT_ORDER', '0', 'ѕор€док вывода этого вида доставки на сайте.', '6', '30', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_UKRPOST_STATUS', 'MODULE_SHIPPING_UKRPOST_ZONE_1', 'MODULE_SHIPPING_UKRPOST_ZONE_2', 'MODULE_SHIPPING_UKRPOST_ZONE_3', 'MODULE_SHIPPING_UKRPOST_ZONE_4', 'MODULE_SHIPPING_UKRPOST_COST', 'MODULE_SHIPPING_UKRPOST_MIN_COST', 'MODULE_SHIPPING_UKRPOST_ADDITIONAL_COST', 'MODULE_SHIPPING_UKRPOST_SORT_ORDER');

      return $keys;
    }
  }
?>
