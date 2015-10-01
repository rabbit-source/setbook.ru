<?php
  class slf {
    var $code, $title, $description, $icon, $enabled, $is_periodical;

// class constructor
    function slf() {
      global $order, $shipping;

      $this->code = 'slf';
      $this->title = MODULE_SHIPPING_SELF_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_SELF_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_SELF_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_SELF_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_SELF_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  //tep_log_ex('$order->delivery[\'postcode\']: '.$order->delivery['postcode']);
	   
	  if ($this->enabled && (int)MODULE_SHIPPING_SELF_ZONE > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SELF_ZONE . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ($geozones_check['total'] < 1) {
		  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SELF_ZONE . "' and city_id like '" . tep_db_input(substr($order->delivery['postcode'], 0, -1)) . "%'");
		  $geozones_check = tep_db_fetch_array($geozones_check_query);
		  if ($geozones_check['total'] < 1) {
			$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SELF_ZONE . "' and city_id like '" . tep_db_input(substr($order->delivery['postcode'], 0, -2)) . "%'");
			$geozones_check = tep_db_fetch_array($geozones_check_query);
		  }
		}
		if ($geozones_check['total'] < 1) $this->enabled = false;
	  }

	  $this->is_periodical = false;
	  if ($this->enabled) {
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['periodicity'] > 0) {
			  $this->is_periodical = true;
			  break;
			}
		  }
		}
	  }

	  if (is_array($shipping)) {
		list(, $self_delivery_id) = explode('_', $shipping['id']);
	  } elseif (isset($order->info['id'])) {
		$self_delivery_id = $order->delivery['delivery_self_address_id'];
	  } else {
		$self_delivery_id = 0;
	  }

	  if ($self_delivery_id > 0 && ALLOW_SHOW_RECEIVE_IN=='true' && $this->enabled) {
		$self_delivery_days_info_query = tep_db_query("select self_delivery_days from " . TABLE_SELF_DELIVERY . " where self_delivery_id = '" . (int)$self_delivery_id . "'");
		if (tep_db_num_rows($self_delivery_days_info_query) > 0) {
		  $self_delivery_days_info = tep_db_fetch_array($self_delivery_days_info_query);
		  $order->info['city_delivery_days'] = (int)$self_delivery_days_info['self_delivery_days'];
		}
	  }
    }

// class methods
    function quote($method = '') {
	  global $order, $customer_id;

	  $method = (int)$method;

	  $zones_check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$order->delivery['country_id'] . "'");
	  $zones_check = tep_db_fetch_array($zones_check_query);
	  $specify_zone = ($zones_check['total']>0 ? true : false);

	  $common_qty = 0;
	  $periodicals_qty = 0;
	  $total_sum = 0;
	  if (is_object($order)) {
		reset($order->products);
		while (list(, $order_product) = each($order->products)) {
		  if ($order_product['periodicity'] > 0) {
			$periodicals_qty += $order_product['qty'];
		  } else {
			$common_qty += $order_product['qty'];
			$total_sum += $order_product['final_price'] * $order_product['qty'];
		  }
		}
	  }

	  $points = array();
	  $zone_id = $order->delivery['zone_id'];
	  $self_delivery_check_query = tep_db_query("select count(*) as total from " . TABLE_SELF_DELIVERY . " where entry_country_id = '" . (int)$order->delivery['country_id'] . "'" . ($specify_zone ? " and entry_zone_id = '" . (int)$zone_id . "'" : ""));
	  $self_delivery_check = tep_db_fetch_array($self_delivery_check_query);
	  if ($self_delivery_check['total'] < 1) {
		$parent_cities = array($order->delivery['postcode']);
		tep_get_parents($parent_cities, $order->delivery['postcode'], TABLE_CITIES);
		$parent_cities = array_reverse($parent_cities);
		$parent_city_info_query = tep_db_query("select zone_id from " . TABLE_CITIES . " where city_country_id = '" . (int)$order->delivery['country_id'] . "' and city_id = '" . tep_db_input($parent_cities[0]) . "'");
		$parent_city_info = tep_db_fetch_array($parent_city_info_query);
		$zone_id = $parent_city_info['zone_id'];
		$self_delivery_check_query = tep_db_query("select count(*) as total from " . TABLE_SELF_DELIVERY . " where entry_country_id = '" . (int)$order->delivery['country_id'] . "'" . ($specify_zone ? " and entry_zone_id = '" . (int)$zone_id . "'" : ""));
		$self_delivery_check = tep_db_fetch_array($self_delivery_check_query);
		if ($self_delivery_check['total'] < 1) {
		  $self_delivery_check_query = tep_db_query("select min(entry_zone_id-" . (int)$zone_id . ") as min_zone_id from " . TABLE_SELF_DELIVERY . " where entry_country_id = '" . (int)$order->delivery['country_id'] . "' order by min_zone_id limit 1");
		  $self_delivery_check = tep_db_fetch_array($self_delivery_check_query);
		  $zone_id = $self_delivery_check['min_zone_id'] + $zone_id;
		}
	  }
	  $self_delivery_check_query = tep_db_query("select count(*) as total from " . TABLE_SELF_DELIVERY . " where" . ($method>0 ? " self_delivery_id = '" . (int)$method . "'" : " entry_country_id = '" . (int)$order->delivery['country_id'] . "'" . ($specify_zone ? " and entry_zone_id = '" . (int)$zone_id . "'" : "")));
	  $self_delivery_check = tep_db_fetch_array($self_delivery_check_query);
	  $self_delivery_query = tep_db_query("select self_delivery_id, self_delivery_cost, self_delivery_free, entry_country_id, entry_zone_id, entry_suburb as suburb, entry_city as city, entry_street_address as street_address, entry_telephone as telephone, self_delivery_description from " . TABLE_SELF_DELIVERY . " where self_delivery_status = '1'" . ($method>0 ? " and self_delivery_id = '" . (int)$method . "'" : ($this->is_periodical ? "" : " and (self_delivery_only_periodicals = '0' or self_delivery_only_periodicals is null)") . " and entry_country_id = '" . (int)$order->delivery['country_id'] . "'" . ($specify_zone ? " and entry_zone_id = '" . (int)$zone_id . "'" : "") . " order by city, street_address") . "");
	  while ($self_delivery = tep_db_fetch_array($self_delivery_query)) {
		$shipping_cost = 0;
		$self_delivery['self_delivery_cost'] = str_replace(',', '.', ($self_delivery['self_delivery_cost'] / $order->info['currency_value']));
		$self_delivery['self_delivery_free'] = str_replace(',', '.', ($self_delivery['self_delivery_free'] / $order->info['currency_value']));
		if ($self_delivery['self_delivery_cost'] > 0) {
		  if ($common_qty > 0) {
			if ( ($self_delivery['self_delivery_free'] > 0) && ($total_sum > $self_delivery['self_delivery_free']) ) $shipping_cost = 0;
			else $shipping_cost = $self_delivery['self_delivery_cost'];
		  }
		  if ($periodicals_qty > 0) {
			$shipping_cost += $periodicals_qty * $self_delivery['self_delivery_cost'];
		  }
		} else {
		  $shipping_cost = 0;
		}
		$region_info_query = tep_db_query("select zone_name as state from " . TABLE_ZONES . " where zone_id = '" . (int)$self_delivery['entry_zone_id'] . "' and zone_country_id = '" . (int)$self_delivery['entry_country_id'] . "'");
		if (tep_db_num_rows($region_info_query) > 0) {
		  $region_info = tep_db_fetch_array($region_info_query);
		  $self_delivery = array_merge($self_delivery, $region_info);
		}
		$points[] = array('id' => $self_delivery['self_delivery_id'],
						  'title' => tep_address_format($order->delivery['format_id'], $self_delivery, 1, '', ', '),
						  'cost' => $shipping_cost);
	  }
	  if (sizeof($points)==0 && $self_delivery_check['total']==0) {
//		$points[] = array('id' => $this->code,
//						  'title' => MODULE_SHIPPING_SELF_TEXT_WAY,
//						  'cost' => $shipping_cost);
	  }

	  if (sizeof($points)>0) {
    	$this->quotes = array('id' => $this->code,
							  'module' => MODULE_SHIPPING_SELF_TEXT_TITLE,
							  'methods' => $points);
	  }

      return $this->quotes;
    }

    function check() {
	  global $order;

      if (!isset($this->_check)) {
      	$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_SELF_STATUS'");
      	$this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить доставку самовывозом', 'MODULE_SHIPPING_SELF_STATUS', 'True', 'Разрешить клиенту самому приезжать за заказом?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Зона действия доставки', 'MODULE_SHIPPING_SELF_ZONE', '', 'Укажите географическую зону, в которой будет действовать данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок сортировки', 'MODULE_SHIPPING_SELF_SORT_ORDER', '0', 'Порядок вывода', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_SELF_STATUS', 'MODULE_SHIPPING_SELF_ZONE', 'MODULE_SHIPPING_SELF_SORT_ORDER');
    }
  }
?>
