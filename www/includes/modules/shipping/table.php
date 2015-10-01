<?php
  class table {
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function table() {
      global $order;

      $this->code = 'table';
      $this->title = MODULE_SHIPPING_TABLE_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_TABLE_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_TABLE_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  $this->order_type = 'common'; // common - без периодики, periodical - периодика, mixed - смешанный
	  if ($this->enabled) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ($geozones_check['total'] < 1) {
		  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "' and city_id like '" . tep_db_input(substr($order->delivery['postcode'], 0, -1)) . "%'");
		  $geozones_check = tep_db_fetch_array($geozones_check_query);
		  if ($geozones_check['total'] < 1) {
			$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "' and city_id like '" . tep_db_input(substr($order->delivery['postcode'], 0, -2)) . "%'");
			$geozones_check = tep_db_fetch_array($geozones_check_query);
		  }
		}
		if ($geozones_check['total'] < 1) $this->enabled = false;
	  }
    }

// class methods
    function quote($method = '') {
      global $order, $currencies, $cart, $currency;

	  $shipping_cost = 0;

	  list($table_weight, $table_cost) = explode(":" , MODULE_SHIPPING_TABLE_HANDLING);
	  $base_shipping = str_replace(',', '.', $table_cost);
	  $table_weight = str_replace(',', '.', $table_weight);

	  $total_weight = 0;
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
			$total_weight += $order_product['weight']*$order_product['qty'];
			$total_sum += $order_product['final_price'] * $order_product['qty'];
		  }
		}
	  }

	  if ($common_qty > 0) {
		$shipping_cost += $base_shipping;
	  }

	  if (MODULE_SHIPPING_TABLE_COST && $total_weight > $table_weight) {
		list($upper_weight, $upper_cost) = explode(":" , MODULE_SHIPPING_TABLE_COST);
		$shipping_cost += ceil($total_weight-$table_weight)*$upper_cost/$upper_weight;
	  }

	  $currency_value = $currencies->get_value($currency);
	  $currency_decimal_places = $currencies->get_decimal_places($currency);

	  if (MODULE_SHIPPING_TABLE_FREE > 0 && $common_qty > 0) {
		if ($currency_value > 0) $free_shipping = MODULE_SHIPPING_TABLE_FREE / $currency_value;
		$this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_TABLE_TEXT_FREE_SHIPPING, $currencies->format($free_shipping)) . '</span>)';
		if ($total_sum >= str_replace(',', '.', $free_shipping)) $shipping_cost = 0;
	  }

	  if ($periodicals_qty > 0) {
		if (SHOP_ID==1) $shipping_cost += $base_shipping * $periodicals_qty;
		else $shipping_cost += $base_shipping * $periodicals_qty;
	  }

	  if ($shipping_cost > 0) {
		$shipping_cost = str_replace(',', '.', $shipping_cost/$currency_value);
	  }

	  $this->quotes = array('id' => $this->code,
							'module' => MODULE_SHIPPING_TABLE_TEXT_TITLE,
							'methods' => array(array('id' => $this->code,
													 'title' => MODULE_SHIPPING_TABLE_TEXT_WAY,
													 'cost' => $shipping_cost)));

	  if ($this->tax_class > 0) {
		$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	  }

	  if (tep_not_null($this->icon)) $this->quotes['icon'] = $this->icon;

	  return $this->quotes;
	}

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_TABLE_STATUS', 'True', 'Вы действительно хотите разрешить доставку этим способом?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Зона действия доставки', 'MODULE_SHIPPING_TABLE_ZONE', '', 'Укажите географическую зону, в которой будет действовать данный вид доставки', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость доставки', 'MODULE_SHIPPING_TABLE_HANDLING', '2:150', 'Укажите базовую стоимость доставки и вес до которого она будет действовать. Например: 2:150 (до 2кг стоит 150).', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Превышение базового веса', 'MODULE_SHIPPING_TABLE_COST', '1:15', 'Стоимость превышения базового веса. Например: 1:15 (то есть каждый последуюший 1кг свыше базового веса добавляет 15 к базовой стоимости доставки).', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Бесплатная доставка', 'MODULE_SHIPPING_TABLE_FREE', '', 'Укажите сумму заказа, при которой доставка будет бесплатной', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_TABLE_SORT_ORDER', '0', 'Порядок вывода на сайте.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_TABLE_STATUS', 'MODULE_SHIPPING_TABLE_ZONE', 'MODULE_SHIPPING_TABLE_HANDLING', 'MODULE_SHIPPING_TABLE_COST', 'MODULE_SHIPPING_TABLE_FREE', 'MODULE_SHIPPING_TABLE_SORT_ORDER');
    }
  }
?>