<?php
  class kzpost {
    var $code, $title, $description, $enabled, $shipping_table, $shipping_zone;

// class constructor
    function kzpost() {
	  global $order;

	  $this->shipping_table = '
	01	03	13	05	06	10	12	11	14	15	08	07	09	16	02	04
01		4	5	3	5	1	4	2	1	1	3	4	3	3	1	3
03	3		3	4	2	4	3	4	4	4	3	5	1	3	4	3
13	5	3		5	2	5	4	5	5	5	5	5	3	4	5	5
05	3	4	5		4	3	3	4	3	3	2	3	4	2	3	1
06	4	2	2	4		5	3	4	5	5	4	5	3	4	5	5
10	1	4	5	3	5		3	2	2	2	3	4	3	3	2	3
12	3	3	4	3	3	3		4	4	4	2	4	3	1	4	3
11	2	5	5	4	5	2	4		3	2	3	5	3	4	1	4
14	1	5	5	3	5	2	4	3		2	3	5	4	3	2	4
15	1	4	5	3	5	2	4	2	2		3	5	4	3	1	4
08	3	3	5	2	4	3	2	3	3	3		3	4	1	3	2
07	4	5	5	3	5	4	4	5	5	5	3		5	4	5	3
09	3	2	3	4	3	3	3	3	4	4	4	5		3	4	4
16	3	3	4	2	4	3	1	4	3	3	1	4	3		3	2
02	1	4	5	3	5	2	4	2	2	1	3	5	4	3		3
04	3	4	5	1	5	3	3	4	4	4	2	3	4	2	3	
';

      $this->code = 'kzpost';
      $this->title = MODULE_SHIPPING_KZPOST_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_KZPOST_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_KZPOST_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_KZPOST_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  if ($this->enabled) {
		$shipping_rows = explode("\n", $this->shipping_table);

		if (tep_not_null($shipping_rows[0])) $to_regions = explode("\t", $shipping_rows[0]);
		elseif (tep_not_null($shipping_rows[1])) $to_regions = explode("\t", $shipping_rows[1]);
		else $to_regions = array();

		reset($to_regions);
		while (list($k, $v) = each($to_regions)) {
		  $to_regions[$k] = trim($v);
		}

		$to_regions_codes = array_flip($to_regions);
		$from_region_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_id = '" . (int)STORE_ZONE . "'");
		$from_region = tep_db_fetch_array($from_region_query);
		$from_region_code = $from_region['zone_code'];

		$to_region_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_id = '" . (int)$order->delivery['zone_id'] . "'");
		$to_region = tep_db_fetch_array($to_region_query);
		$to_region_code = $to_region['zone_code'];

		$check_column_no = $to_regions_codes[$to_region_code];
		$this->shipping_zone = 0;
		reset($shipping_rows);
		while (list($i, $shipping_row) = each($shipping_rows)) {
		  $shipping_cells = explode("\t", $shipping_row);
		  if ($shipping_cells[0]==$from_region_code) {
			$this->shipping_zone = $shipping_cells[$check_column_no];
			break;
		  }
		}
		if ($this->shipping_zone == 0) {
		  if ($from_region_code==$to_region_code) $this->shipping_zone = 1;
		  else $this->enabled = false;
		}
	  }
    }

// class methods
    function quote($method = '') {
      global $order, $shipping_weight;

	  $method = (int)$method;

	  $shipping_cost_1 = 0;
	  $shipping_cost_2 = 0;
	  $base_shipping_cost = 0;
	  $shipping_weight_1 = 0;
	  $shipping_weight_2 = 0;

	  if (empty($order->delivery['postcode'])) {
		$methods = array(array('id' => $this->code,
							   'title' => MODULE_SHIPPING_KZPOST_ERROR_NO_ZIPCODE_FOUND,
							   'cost' => 0));
	  } else {
		$ground_table = array_map('trim', explode(',' , MODULE_SHIPPING_KZPOST_BASE_WEIGHT));
		for ($i=0, $n=sizeof($ground_table); $i<$n; $i++) {
		  list($ground_table_zone, $ground_table_cost) = explode(':', $ground_table[$i]);
		  if (trim($ground_table_zone) == trim($this->shipping_zone)) {
			$base_shipping_cost += $ground_table_cost;
			break;
		  }
		}

		list($upper_weight, $upper_cost) = explode(':', MODULE_SHIPPING_KZPOST_COST_UPPER);
		$upper_weight = str_replace(',', '.', $upper_weight);
		$upper_cost = str_replace(',', '.', $upper_cost);

		$add_cost = str_replace(',', '.', (float)MODULE_SHIPPING_KZPOST_ADDITIONAL_COST);

		$currency = $order->info['currency'];
		$currency_value = $order->info['currency_value'];

		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['weight'] > 0) {
			  if ($order_product['periodicity'] > 0) {
				$shipping_cost_2 += $base_shipping_cost;

				$shipping_weight_2 = ceil($order_product['weight'] * 2) / 2;

				if ($shipping_weight_2 > 1 && $upper_weight > 0 && $upper_weight > 0) {
				  $shipping_cost_2 += ($shipping_weight_2 - 1) / $upper_weight * $upper_cost;
				}

				$shipping_cost_2 += $add_cost;

				$shipping_cost_2 = $shipping_cost_2 * $order_product['qty'];

				if ($currency_value > 0) $shipping_cost_2 = $shipping_cost_2 / $currency_value;
			  } else {
				$shipping_weight_1 += $order_product['weight'] * $order_product['qty'];
			  }
			}
		  }
		}

		if ($shipping_weight_1 > 0) {
		  $shipping_cost_1 += $base_shipping_cost;

		  $shipping_weight_1 = ceil($shipping_weight_1 * 2) / 2;

		  if ($shipping_weight_1 > 1 && $upper_weight > 0 && $upper_cost > 0) {
			$shipping_cost_1 += ceil($shipping_weight_1 - 1)  * $upper_cost;
		  }

		  $shipping_cost_1 += $add_cost;

		  if ($currency_value > 0) $shipping_cost_1 = $shipping_cost_1 / $currency_value;
		}

		$shipping_cost = $shipping_cost_1 + $shipping_cost_2;

		$methods = array(array('id' => '1',
							   'title' => sprintf(MODULE_SHIPPING_KZPOST_TEXT_WEIGHT, $shipping_weight),
							   'cost' => $shipping_cost));
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_KZPOST_TEXT_TITLE;
	  $this->quotes['methods'] = $methods;

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_KZPOST_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_KZPOST_STATUS', 'True', 'Вы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость базовой доставки 1кг', 'MODULE_SHIPPING_KZPOST_BASE_WEIGHT', '', 'Стоимость доставки 1кг груза по соответствующим тарифным поясам, через запятую', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Превышение веса', 'MODULE_SHIPPING_KZPOST_COST_UPPER', '', 'Стоимость превышения базового веса. Например: 1:100 (то есть каждый последуюший 1кг свыше базового веса добавляет 100 к базовой стоимости доставки).', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость упаковки', 'MODULE_SHIPPING_KZPOST_ADDITIONAL_COST', '0', 'Указанная цифра (в каз. тенге) будет добавлена к рассчитанной стоимости доставки', '6', '40', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_KZPOST_SORT_ORDER', '0', 'Порядок вывода этого вида доставки на сайте.', '6', '50', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_KZPOST_STATUS', 'MODULE_SHIPPING_KZPOST_BASE_WEIGHT', 'MODULE_SHIPPING_KZPOST_COST_UPPER', 'MODULE_SHIPPING_KZPOST_ADDITIONAL_COST', 'MODULE_SHIPPING_KZPOST_SORT_ORDER');

      return $keys;
    }
  }
?>