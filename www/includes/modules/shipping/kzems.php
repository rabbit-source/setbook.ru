<?php
  class kzems {
    var $code, $title, $description, $enabled, $available_cities, $from_city;

// class constructor
    function kzems() {
	  global $order;

	  $this->available_cities = array('Астана'				=> 1,
									  'Актюбинск'			=> 2,
									  'Актау'				=> 3,
									  'Алматы'				=> 4,
									  'Атырау'				=> 5,
									  'Караганда'			=> 6,
									  'Кызылорда'			=> 7,
									  'Костанай'			=> 8,
									  'Павлодар'			=> 9,
									  'Петропавловск'		=> 10,
									  'Тараз'				=> 11,
									  'Усть-Каменогорск'	=> 12,
									  'Уральск'				=> 13,
									  'Шымкент'				=> 14,
									  'Кокшетау'			=> 15,
									  'Талдыкорган'			=> 16,
									 );

	  $from_city_info_query = tep_db_query("select city_name from " . TABLE_CITIES . " where zone_id = '" . (int)STORE_ZONE . "' order by city_id limit 1");
	  $from_city_info = tep_db_fetch_array($from_city_info_query);
	  $this->from_city = $from_city_info['city_name'];

      $this->code = 'kzems';
      $this->title = MODULE_SHIPPING_KZEMS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_KZEMS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_KZEMS_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
	  $this->enabled = ((MODULE_SHIPPING_KZEMS_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  if ($this->enabled && (int)MODULE_SHIPPING_KZEMS_ZONE_1 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_KZEMS_ZONE_1 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_KZEMS_ZONE_2 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_KZEMS_ZONE_2 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_KZEMS_ZONE_3 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_KZEMS_ZONE_3 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_KZEMS_ZONE_4 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_KZEMS_ZONE_4 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled) {
		if (!in_array($order->delivery['city'], array_keys($this->available_cities))) {
		  $this->enabled = false;
		}
	  }
    }

// class methods
    function quote($method = '') {
      global $order;

	  $method = (int)$method;

	  $currency = $order->info['currency'];
	  $currency_value = $order->info['currency_value'];
	  $add_cost = str_replace(',', '.', (float)MODULE_SHIPPING_KZEMS_ADDITIONAL_COST);

	  $shipping_cost_1 = 0;
	  $shipping_cost_2 = 0;
	  $shipping_weight_1 = 0;

	  if (empty($order->delivery['postcode'])) {
		$this->quotes['error'] = MODULE_SHIPPING_KZEMS_ERROR_NO_ZIPCODE_FOUND;
		$methods = array(array('id' => $this->code,
							   'title' => MODULE_SHIPPING_KZEMS_NO_ZIPCODE_FOUND,
							   'cost' => 0));
	  } else {
		$from_city_info_query = tep_db_query("select city_name from " . TABLE_CITIES . " where zone_id = '" . (int)STORE_ZONE . "' order by city_id limit 1");
		$from_city_info = tep_db_fetch_array($from_city_info_query);
		$from_city = $from_city_info['city_name'];

		$error = false;
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['weight'] > 0) {
			  if ($order_product['periodicity'] > 0) {
				$data = array('tsend' => '1',
							  'from_city' => $from_city_info['city_name'],
							  'to_city' => $order->delivery['city'],
							  'weight' => $order_product['weight']);

				if (($shipping_cost_2 = $this->post_data($data)) !== false) {
				  if ($shipping_cost_2 > 0) {
					$shipping_cost_2 += $add_cost;
					list($notify_cost) = explode(';', MODULE_SHIPPING_KZEMS_NOTIFY_COST);
					$shipping_cost_2 += $notify_cost;

					$shipping_cost_2 = $shipping_cost_2 * $order_product['qty'];

					if ($currency_value > 0) $shipping_cost_2 = $shipping_cost_2 / $currency_value;
				  } else {
					$error = true;
					break;
				  }
				} else {
				  $error = true;
				  break;
				}
			  } else {
				$shipping_weight_1 += $order_product['weight'] * $order_product['qty'];
			  }
			}
		  }
		}

		if ($shipping_weight_1 > 0 && !$error) {
		  $data = array('tsend' => '2',
						'from_city' => $from_city_info['city_name'],
						'to_city' => $order->delivery['city'],
						'weight' => $shipping_weight_1);
		  if (($shipping_cost_1 = $this->post_data($data)) !== false) {
			if ($shipping_cost_1 > 0) {
			  $shipping_cost_1 += $add_cost;
			  list($notify_cost) = explode(';', MODULE_SHIPPING_KZEMS_NOTIFY_COST);
			  if ($currency_value > 0) {
				$shipping_cost_1 = ($shipping_cost_1 + $notify_cost) / $currency_value;
			  }
			} else {
			  $error = true;
			}
		  } else {
			$error = true;
		  }
		}

		if (!$error) {
		  $shipping_cost = $shipping_cost_1 + $shipping_cost_2;
		  $methods = array();
		  $methods[] = array('id' => '1',
							 'title' => MODULE_SHIPPING_KZEMS_TEXT_TITLE_1,
							 'cost' => $shipping_cost);
		} else {
		  $this->quotes['error'] = MODULE_SHIPPING_KZEMS_ERROR_CALC;
		  $methods = array(array('id' => $this->code,
								 'title' => MODULE_SHIPPING_KZEMS_ERROR_CALC,
								 'cost' => 0));
		}
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_KZEMS_TEXT_TITLE;
	  $this->quotes['methods'] = $methods;

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_KZEMS_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_KZEMS_STATUS', 'True', 'Вы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('1-я зона невозможности доставки', 'MODULE_SHIPPING_KZEMS_ZONE_1', '', 'Укажите 1-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('2-я зона невозможности доставки', 'MODULE_SHIPPING_KZEMS_ZONE_2', '', 'Укажите 2-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '30', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('3-я зона невозможности доставки', 'MODULE_SHIPPING_KZEMS_ZONE_3', '', 'Укажите 3-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '40', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('4-я зона невозможности доставки', 'MODULE_SHIPPING_KZEMS_ZONE_4', '', 'Укажите 4-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '50', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Стоимость упаковки', 'MODULE_SHIPPING_KZEMS_ADDITIONAL_COST', '0', 'Указанная цифра (в каз. тенге) будет добавлена к рассчитанной стоимости доставки', '6', '60', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Доставка уведомления', 'MODULE_SHIPPING_KZEMS_NOTIFY_COST', '300;150;80', 'Перечислите (через запятую, в каз. тенге) стоимость доставки уведомления курьером, почтой и по телефону соответственно', '6', '70', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_KZEMS_SORT_ORDER', '0', 'Порядок вывода этого вида доставки на сайте.', '6', '80', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_KZEMS_STATUS', 'MODULE_SHIPPING_KZEMS_ZONE_1', 'MODULE_SHIPPING_KZEMS_ZONE_2', 'MODULE_SHIPPING_KZEMS_ZONE_3', 'MODULE_SHIPPING_KZEMS_ZONE_4', 'MODULE_SHIPPING_KZEMS_ADDITIONAL_COST', 'MODULE_SHIPPING_KZEMS_NOTIFY_COST', 'MODULE_SHIPPING_KZEMS_SORT_ORDER');

      return $keys;
    }

	function post_data($data) {
	  $URL = 'http://emscal.kazpost.kz/inc/result_kz.php';

	  $kz_weights = array('0.3'		=> 1,
						  '0.5'		=> 2,
						  '1.0'		=> 3,
						  '1.5'		=> 4,
						  '2.0'		=> 5,
						  '2.5'		=> 6,
						  '3.0'		=> 7,
						  '3.5'		=> 8,
						  '4.0'		=> 9,
						  '4.5'		=> 10,
						  '5.0'		=> 11,
						  '5.5'		=> 12,
						  '6.0'		=> 13,
						  '6.5'		=> 14,
						  '7.0'		=> 15,
						  '7.5'		=> 16,
						  '8.0'		=> 17,
						  '8.5'		=> 18,
						  '9.0'		=> 19,
						  '9.5'		=> 20,
						  '10.0'	=> 21,
						 );

	  $URL_Info = parse_url($URL);

	  reset($data);
	  while (list($key, $value) = each($data)) {
		if (strpos($key, 'city')!==false) {
		  $value = $this->available_cities[$value];
		} elseif (strpos($key, 'weight')!==false) {
		  $value = str_replace(',', '.', sprintf('%01.1f', str_replace(',', '.', ceil($value*2)/2)));
		  if (isset($kz_weights[$value])) {
			$values[] = 'wID=' . urlencode($kz_weights[$value]);
		  } else {
			$values[] = 'wID=' . urlencode('22');
			$values[] = 'ves=' .  urlencode($value);
			$value = 'Больше 10.0 килограмма';
		  }
		}
		$values[] = $key . '=' . urlencode($value);
	  }
	  $data_string = implode('&', $values);

	  if(!isset($URL_Info['port'])) $URL_Info['port'] = 80;

	  // building POST-request:
	  $request .= "POST " . $URL_Info["path"] . " HTTP/1.1\n";
	  $request .= "Host: " . $URL_Info["host"] . "\n";
	  $request .= "Referer: " . $URL_Info["host"] . $URL_Info["path"] . "\n";
	  $request .= "Content-type: application/x-www-form-urlencoded\n";
	  $request .= "Content-length: " . strlen($data_string) . "\n";
	  $request .= "Connection: close\n";
	  $request .= "\n";
	  $request .= $data_string . "\n";

	  if ($fp = @fsockopen($URL_Info['host'], $URL_Info['port'])) {
		fputs($fp, $request);
		while(!feof($fp)) {
		  $result .= fgets($fp, 128);
		}
		fclose($fp);

		if (preg_match('/<td[^>]*>([\d]+)<\/td>/i', $result, $regs)) return $regs[1];
	  }

	  return false;
	}
  }
?>