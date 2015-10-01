<?php
  class asdprepay {
	var $code, $title, $description, $icon, $enabled, $pickup, $courier_enabled, $pickup_enabled;

// class constructor
	function asdprepay() {
	  global $order, $customer_id;

	  $this->code = 'asdprepay';
	  $this->title = MODULE_SHIPPING_ASDPREPAY_TEXT_TITLE;
	  $this->description = MODULE_SHIPPING_ASDPREPAY_TEXT_DESCRIPTION;
	  $this->sort_order = MODULE_SHIPPING_ASDPREPAY_SORT_ORDER;
	  $this->icon = '';
	  $this->tax_class = 0;
	  $this->enabled = ((MODULE_SHIPPING_ASDPREPAY_STATUS == 'True') ? true : false);
	  $this->courier_enabled = false;
	  $this->pickup_enabled = false;
	  $this->pickup = '';

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

	  if ($customer_id==25627 || $customer_id==2) $this->enabled = true;

	  if ($this->enabled) {
		if ((int)MODULE_SHIPPING_ASDPREPAY_COURIER_ZONE > 0) {
		  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)MODULE_SHIPPING_ASDPREPAY_COURIER_ZONE . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		  $geozones_check = tep_db_fetch_array($geozones_check_query);
		  if ($geozones_check['total'] > 0) $this->courier_enabled = true;
		}

		if ((int)MODULE_SHIPPING_ASDPREPAY_PICKUP_ZONE > 0) {
		  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)MODULE_SHIPPING_ASDPREPAY_PICKUP_ZONE . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		  $geozones_check = tep_db_fetch_array($geozones_check_query);
		  if ($geozones_check['total'] > 0) {
			$this->pickup_enabled = true;

			// ищем пункт самовывоза
			$filename = MODULE_SHIPPING_ASDPREPAY_TABLE_FILE_NAME;
			if (file_exists($filename)) {
			  $fp = fopen($filename, 'r');
			  while (list($city, $state, $street_address, $postcode, $telephone) = fgetcsv($fp, 128, ';')) {
				if (tep_not_null($postcode)) {
				  while (strlen($postcode)<5) $postcode = '0' . $postcode;
				  $postcode = substr($postcode, 0, 3);
				  if (preg_match('/^' . $postcode . '.*$/', $order->delivery['postcode'])) {
					$telephone = trim($telephone);
					if (tep_not_null($telephone)) $telephone = MODULE_SHIPPING_ASDPREPAY_TEXT_TELEPHONE . $telephone;
					$this->pickup = tep_address_format(1, array('city' => trim($city), 'state' => trim($state), 'street_address' => trim($street_address), 'telephone' => $telephone), true, '', "\n");
					break;
				  }
				}
			  }
			  fclose($fp);
			}
		  }
		  if ($this->pickup == '') $this->pickup_enabled = false;
		}
	  }
	  if (!$this->courier_enabled && !$this->pickup_enabled) $this->enabled = false;
	}

	function coast($total_coast, $total_weight, $coast_one_kg, $coast_next_kg, $premium_percent, $cod_percent) {
	  global $currencies, $currency;

	  $cost = $coast_one_kg;
	  if ($total_weight > 1) $cost += $coast_next_kg * ceil($total_weight-1);
	  if ($premium_percent > 0) $cost += round($total_coast * ($premium_percent/100));
	  if ($cod_percent > 0) $cost += round($total_coast * ($cod_percent/100));
	  $cost /= $currencies->get_value($currency);

	  return $cost;
	}

// class methods
	function quote($method = '') {
	  global $order, $currencies, $currency;

	  if (is_object($order)) {
		for ($i = 0;$i < count($order->products); $i++) {
		  $total['weight'] += $order->products[$i]['weight'] * $order->products[$i]['qty'];
		  $total['sum'] += $order->products[$i]['final_price'] * $order->products[$i]['qty'];
		}
	  }

	  $total['sum'] = $total['sum'] * $currencies->get_value($currency); //Переводим в гривны

	  list($asd_standart_kg, $asd_standart_next_kg) = explode(':' , MODULE_SHIPPING_ASDPREPAY_STANDART_COAST);
	  list($asd_pickup_kg, $asd_pickup_next_kg) = explode(':' , MODULE_SHIPPING_ASDPREPAY_PICKUP_COAST);

	  $methods = array();

	  if ($method!='pickup') {
		$detail['id'] = 'courier';
		//АСД-Мегаполис-Стандарт (курьерская) по предоплате
		$detail['title'] = (tep_not_null($method) ? '' : MODULE_SHIPPING_ASDPREPAY_TEXT_SIPPING_TYPE_EXPRESS);
		$detail['cost'] = $this->coast($total['sum'], $total['weight'], $asd_standart_kg, $asd_standart_next_kg, MODULE_SHIPPING_ASDPREPAY_PREMIUM, 0);
		$methods[] = $detail;
	  }

	  if (tep_not_null($this->pickup) && $method!='courier') {
		//АСД-Мегаполис-Эконом (самовывоз) по предоплате
		$detail['id'] = 'pickup';
		$detail['title'] = (tep_not_null($method) ? trim($this->pickup) : MODULE_SHIPPING_ASDPREPAY_TEXT_SIPPING_TYPE_PICKUP . ': ' . trim($this->pickup));
		$detail['cost'] = $this->coast($total['sum'], $total['weight'], $asd_pickup_kg, $asd_pickup_next_kg, MODULE_SHIPPING_ASDPREPAY_PREMIUM, 0);
		$methods[] = $detail;
	  }

	  $this->quotes = array('id' => $this->code,
							'module' => (tep_not_null($method) ? ($method=='pickup' ? MODULE_SHIPPING_ASDPREPAY_TEXT_TITLE_PICKUP : MODULE_SHIPPING_ASDPREPAY_TEXT_TITLE_COURIER) : MODULE_SHIPPING_ASDPREPAY_TEXT_TITLE),
							'methods' => $methods);

	  if ($this->tax_class > 0) {
		$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	  }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

	  return $this->quotes;
	}

	function check() {
	  if (!isset($this->_check)) {
		$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ASDPREPAY_STATUS'");
		$this->_check = tep_db_num_rows($check_query);
	  }
	  return $this->_check;
	}

	function params() {
	  $install_params = array();
	  $install_params[] = array(
			'configuration_title' => 'Разрешить этот способ доставки',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_STATUS',
			'configuration_value' => 'False',
			'configuration_description' => 'Вы действительно хотите разрешить доставку этим способом?',
			'configuration_group_id' => '6',
			'sort_order' => '10',
			'set_function' => "tep_cfg_select_option(array(\'True\', \'False\'), ",
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Стоимость 1кг и +1кг (доставка курьером)',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_STANDART_COAST',
			'configuration_value' => '25:5',
			'configuration_description' => 'Укажите базовую стоимость доставки за 1кг и стоимость за каждый следующий кг. Например: 25:5 (до 1кг стоит 25, каждый следующий кг 5).',
			'configuration_group_id' => '6',
			'sort_order' => '20',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Стоимость 1кг и +1кг (самовывоз)',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_PICKUP_COAST',
			'configuration_value' => '20:5',
			'configuration_description' => 'Укажите базовую стоимость доставки за 1кг и стоимость за каждый следующий кг. Например: 20:5 (до 1кг стоит 20, каждый следующий кг 5).',
			'configuration_group_id' => '6',
			'sort_order' => '30',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Путь к файлу адресов доставки АСД на ftp (ASD_SHIPPING_POSTCODES.csv)',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_TABLE_FILE_NAME',
			'configuration_value' => DIR_FS_CATALOG . 'includes/modules/shipping/ASD_SHIPPING_POSTCODES.csv',
			'configuration_description' => 'Укажите путь к файлу таблицы адресов доставки. Например: /inlude/ASD_SHIPPING_POSTCODES.csv',
			'configuration_group_id' => '6',
			'sort_order' => '20',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Зона действия курьерской доставки',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_COURIER_ZONE',
			'configuration_value' => '',
			'configuration_description' => 'Укажите географическую зону, в которой будет действовать курьерская доставка',
			'configuration_group_id' => '6',
			'sort_order' => '40',
			'use_function' => 'tep_get_zone_class_title',
			'set_function' => 'tep_cfg_pull_down_zone_classes(',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Зона действия самовывоза',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_PICKUP_ZONE',
			'configuration_value' => '',
			'configuration_description' => 'Укажите географическую зону, в которой будет действовать самовывоз',
			'configuration_group_id' => '6',
			'sort_order' => '50',
			'use_function' => 'tep_get_zone_class_title',
			'set_function' => 'tep_cfg_pull_down_zone_classes(',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Страховой взнос',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_PREMIUM',
			'configuration_value' => '1',
			'configuration_description' => 'Укажите (в процентах) стоимость страхового взноса для этого вида доставки',
			'configuration_group_id' => '6',
			'sort_order' => '60',
			'date_added' => 'now()',
			);
	  $install_params[] = array(
			'configuration_title' => 'Порядок сортировки',
			'configuration_key' => 'MODULE_SHIPPING_ASDPREPAY_SORT_ORDER',
			'configuration_value' => '0',
			'configuration_description' => 'Порядок вывода',
			'configuration_group_id' => '6',
			'sort_order' => '70',
			'date_added' => 'now()',
			);
	  return $install_params;
	}

	function install() {
	  $install_params = $this->params();

	  for ($i = 0, $n = sizeof($install_params); $i < $n; $i ++) {
		tep_db_perform(TABLE_CONFIGURATION, $install_params[$i]);
	  }
	}

	function remove() {
	  tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
	  $install_params = $this->params();
	  $result = array();
	  for($i = 0, $n = sizeof($install_params); $i < $n; $i ++) {
		$result[] = $install_params[$i]['configuration_key'];
	  }
	  return $result;
	}
  }
?>