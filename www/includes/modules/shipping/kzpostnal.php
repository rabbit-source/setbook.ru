<?php
  class kzpostnal {
    var $code, $title, $description, $enabled, $shipping_table, $shipping_zone, $quotes;

// class constructor
    function kzpostnal() {
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

      $this->code = 'kzpostnal';
	  $this->quotes = array();
      $this->title = MODULE_SHIPPING_KZPOSTNAL_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_KZPOSTNAL_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_KZPOSTNAL_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_KZPOSTNAL_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

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
      global $order, $shipping_weight, $currencies, $currency;

	  $method = (int)$method;

	  $shipping_cost = 0;
	  if (empty($order->delivery['postcode'])) {
		$methods = array(array('id' => $this->code,
							   'title' => MODULE_SHIPPING_KZPOSTNAL_ERROR_NO_ZIPCODE_FOUND,
							   'cost' => 0));
	  } elseif ($order->info['subtotal'] * $currencies->get_value($currency) > MODULE_SHIPPING_KZPOSTNAL_MAX_COST) {
		$this->quotes['error'] = sprintf(MODULE_SHIPPING_KZPOSTNAL_MAX_COST_ERROR, $currencies->format(MODULE_SHIPPING_KZPOSTNAL_MAX_COST/$currencies->get_value($currency)));
	  } else {
		$calc_shipping_weight = ceil($shipping_weight*2)/2;

		$ground_table = array_map('trim', explode(',', MODULE_SHIPPING_KZPOSTNAL_BASE_WEIGHT));
		for ($i=0, $n=sizeof($ground_table); $i<$n; $i++) {
		  list($ground_table_zone, $ground_table_cost) = explode(':', $ground_table[$i]);
		  if (trim($ground_table_zone) == trim($this->shipping_zone)) {
			$shipping_cost_1 += $ground_table_cost;
			break;
		  }
		}
		list($upper_weight, $upper_cost) = explode(':', MODULE_SHIPPING_KZPOSTNAL_COST_UPPER);
		$upper_weight = str_replace(',', '.', $upper_weight);
		if ($calc_shipping_weight > 1) {
		  if ($upper_weight > 0) $shipping_cost_1 += ($calc_shipping_weight - 1) / $upper_weight * $upper_cost;
		}

		$ins_table = array_map('trim', explode(',', MODULE_SHIPPING_KZPOSTNAL_COST_INSURANCE));
		for ($i=sizeof($ins_table)-1, $n=0; $i>$n; $i--) {
		  list($ins_table_cost, $ins_table_percent) = explode(':', $ground_table[$i]);
		  if ($order->info['subtotal']*$currencies->get_value($currency) > $ins_table_cost) {
//			echo '<br>' . $ins_table[$i] . ' - ' . ($order->info['subtotal']*$currencies->get_value($currency));
			$shipping_cost_1 += $order->info['subtotal'] * $currencies->get_value($currency) * $ins_table_percent / 100;
			break;
		  }
		}

		if (MODULE_SHIPPING_KZPOSTNAL_COST_POST > 0) {
		  $shipping_cost_1 += $order->info['subtotal'] * $currencies->get_value($currency) * MODULE_SHIPPING_KZPOSTNAL_COST_POST / 100;
		}

		$currency_value = $currencies->get_value($currency);
		$currency_decimal_places = $currencies->get_decimal_places($currency);

		if ($currency_value > 0) $shipping_cost_1 = $shipping_cost_1/$currency_value;

		$add_cost = str_replace(',', '.', MODULE_SHIPPING_KZPOSTNAL_ADDITIONAL_COST);
		if ((float)$add_cost > 0) {
		  $shipping_cost_1 += $add_cost;
		}

		$methods = array(array('id' => '1',
							   'title' => sprintf(MODULE_SHIPPING_KZPOSTNAL_TEXT_WEIGHT, $shipping_weight),
							   'cost' => $shipping_cost_1));
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_KZPOSTNAL_TEXT_TITLE;
	  $this->quotes['methods'] = $methods;

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_KZPOSTNAL_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_KZPOSTNAL_STATUS', 'True', 'Вы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость базовой доставки 1кг', 'MODULE_SHIPPING_KZPOSTNAL_BASE_WEIGHT', '', 'Стоимость доставки 1кг груза по соответствующим тарифным поясам, через запятую', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Превышение веса', 'MODULE_SHIPPING_KZPOSTNAL_COST_UPPER', '', 'Стоимость превышения базового веса. Например: 1:100 (то есть каждый последуюший 1кг свыше базового веса добавляет 100 к базовой стоимости доставки).', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Страховка', 'MODULE_SHIPPING_KZPOSTNAL_COST_INSURANCE', '', 'Стоимость страховки в зависимости от стоимости заказа. Например: 1:10,501:7,1001:5 (т.е. при стоимости заказа до 500 страховка составит 10%, от 500 до 1000 - 7%, свыше 1000 - 5%).', '6', '40', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Почтовый перевод', 'MODULE_SHIPPING_KZPOSTNAL_COST_POST', '0', 'Стоимость почтового перевода (в процентах от стоимости заказа', '6', '50', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость упаковки', 'MODULE_SHIPPING_KZPOSTNAL_ADDITIONAL_COST', '0', 'Указанная цифра (в каз. тенге) будет добавлена к рассчитанной стоимости доставки', '6', '60', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Сумма заказа', 'MODULE_SHIPPING_KZPOSTNAL_MAX_COST', '0', 'Укажите максимальную сумму заказа, до которой возможна доставка наложенным платежом (0 если без ограничений)', '6', '70', 'currencies->format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_KZPOSTNAL_SORT_ORDER', '0', 'Порядок вывода этого вида доставки на сайте.', '6', '80', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_KZPOSTNAL_STATUS', 'MODULE_SHIPPING_KZPOSTNAL_BASE_WEIGHT', 'MODULE_SHIPPING_KZPOSTNAL_COST_UPPER', 'MODULE_SHIPPING_KZPOSTNAL_COST_INSURANCE', 'MODULE_SHIPPING_KZPOSTNAL_COST_POST', 'MODULE_SHIPPING_KZPOSTNAL_ADDITIONAL_COST', 'MODULE_SHIPPING_KZPOSTNAL_MAX_COST', 'MODULE_SHIPPING_KZPOSTNAL_SORT_ORDER');

      return $keys;
    }
  }
?>