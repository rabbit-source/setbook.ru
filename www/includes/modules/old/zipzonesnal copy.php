<?php
  class zipzonesnal {
    var $code, $title, $description, $enabled, $is_avia;

// class constructor
    function zipzonesnal() {
	  global $order, $customer_id;

      $this->code = 'zipzonesnal';
      $this->title = MODULE_SHIPPING_ZIPZONESNAL_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_ZIPZONESNAL_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_ZIPZONESNAL_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_ZIPZONESNAL_STATUS == 'True') ? true : false);
	  $this->is_avia = false;

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  if ($this->enabled) {
		if (is_object($order)) {
		  reset($order->products);
		  while (list(, $order_product) = each($order->products)) {
			if ($order_product['type'] > 1) {
			  $this->enabled = false;
			  break;
			}
		  }
		}
	  }

	  $customer_status_check_query = tep_db_query("select customers_status from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_status_check = tep_db_fetch_array($customer_status_check_query);
	  if ($customer_status_check['customers_status']==0) $this->enabled = false;

	  if ($this->enabled && (int)MODULE_SHIPPING_ZIPZONESNAL_ZONE_1 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZIPZONESNAL_ZONE_1 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_ZIPZONESNAL_ZONE_2 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZIPZONESNAL_ZONE_2 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_ZIPZONESNAL_ZONE_3 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZIPZONESNAL_ZONE_3 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }
	  if ($this->enabled && (int)MODULE_SHIPPING_ZIPZONESNAL_ZONE_4 > 0) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZIPZONESNAL_ZONE_4 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ((int)$geozones_check['total'] > 0) $this->enabled = false;
	  }

	  if (defined('MODULE_SHIPPING_ZIPZONESNAL_AVIA_ZONES') && tep_not_null(MODULE_SHIPPING_ZIPZONESNAL_AVIA_ZONES)) {
		$avia_zones = array_map('trim', explode(',', MODULE_SHIPPING_ZIPZONESNAL_AVIA_ZONES));
		if (in_array($order->delivery['zone_id'], $avia_zones)) $this->is_avia = true;
	  }

	  if ($this->enabled && $this->is_avia) {
//		$this->enabled = false;
	  }

	  if (ALLOW_SHOW_RECEIVE_IN=='true' && $this->enabled) {
		$city_delivery_days_info_query = tep_db_query("select city_delivery_days from " . TABLE_CITIES . " where city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		if (tep_db_num_rows($city_delivery_days_info_query) > 0) {
		  $city_delivery_days_info = tep_db_fetch_array($city_delivery_days_info_query);
		  $order->info['city_delivery_days'] = (int)$city_delivery_days_info['city_delivery_days'];
		}
	  }
    }

// class methods
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes, $currencies, $currency;

	  $postcode_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
	  $postcode_check = tep_db_fetch_array($postcode_check_query);
	  $shipping_cost = 0;
	  $default_shipping_cost = str_replace(',', '.', MODULE_SHIPPING_ZIPZONESNAL_COST);
	  $additional_shipping_cost = str_replace(',', '.', MODULE_SHIPPING_ZIPZONESNAL_ADDITIONAL_COST);
	  if ($shipping_weight == 0) {
		$this->quotes['error'] = MODULE_SHIPPING_ZIPZONESNAL_UNDEFINED_RATE;
	  } elseif (empty($order->delivery['postcode']) || $postcode_check['total'] < 1) {
		$this->quotes['error'] = MODULE_SHIPPING_ZIPZONESNAL_NO_ZIPCODE_FOUND;
	  } else {
	  list($s, $m) = explode(' ', microtime());
		$link = 'http://www.russianpost.ru/autotarif/Autotarif.aspx?countryCode=643&typePost=1' . ($shipping_weight>2 ? '&viewPost=36&viewPostName=%D0%A6%D0%B5%D0%BD%D0%BD%D0%B0%D1%8F%20%D0%BF%D0%BE%D1%81%D1%8B%D0%BB%D0%BA%D0%B0' : '&viewPost=26&viewPostName=%D0%A6%D0%B5%D0%BD%D0%BD%D0%B0%D1%8F%20%D0%B1%D0%B0%D0%BD%D0%B4%D0%B5%D1%80%D0%BE%D0%BB%D1%8C') . '&countryCodeName=%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%B0%D1%8F%20%D0%A4%D0%B5%D0%B4%D0%B5%D1%80%D0%B0%D1%86%D0%B8%D1%8F&typePostName=%D0%9D%D0%90%D0%97%D0%95%D0%9C%D0%9D.&weight=' . ($shipping_weight*1000) . '&value1=' . round($order->info['subtotal']) . '&postOfficeId=' . $order->delivery['postcode'];
		//echo $link;
		$page_content = file_get_contents($link);
		/*$page_content = '';
		if ($fp = @fopen($link, 'r')) {
		  stream_set_timeout($fp, 3);
		  while (!feof($fp)) {
			$page_content .= fgets($fp, 1024);
		  }
		  fclose($fp);
		}*/
		if (preg_match('/"TarifValue">([^<]+)</', $page_content, $regs)) {
		  $shipping_cost = str_replace(',', '.', $regs[1]);
		  $shipping_method = sprintf(MODULE_SHIPPING_ZIPZONESNAL_TEXT_WEIGHT, $shipping_weight);
		} elseif ($default_shipping_cost > 0) {
		  $shipping_cost = $default_shipping_cost * $shipping_weight;
		  $shipping_method = sprintf(MODULE_SHIPPING_ZIPZONESNAL_TEXT_WEIGHT, $shipping_weight);
		} else {
		  $this->quotes['error'] = MODULE_SHIPPING_ZIPZONESNAL_NO_ZIPCODE_FOUND;
		}
		if ($shipping_method=='') {
		  $this->quotes['error'] = MODULE_SHIPPING_ZIPZONESNAL_UNDEFINED_RATE;
//		  $shipping_method = MODULE_SHIPPING_ZIPZONESNAL_UNDEFINED_RATE;
		} else {
		if (MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM > 0) {
			if (MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM=='1') {
			  $this->quotes['error'] = MODULE_SHIPPING_ZIPZONESNAL_DISABLED;
			} else {
			  if ($order->info['subtotal']/$currencies->currencies[$currency]['value'] > MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM) {
				$this->quotes['error'] = sprintf(MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM_ERROR, $currencies->format(MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM/$currencies->currencies[$currency]['value']));
			  }
			}
		  }

		  if ($shipping_cost > 0) {
			if (tep_not_null(MODULE_SHIPPING_ZIPZONESNAL_ADD)) {
			  if (substr(MODULE_SHIPPING_ZIPZONESNAL_ADD, -1)=='%') {
				$add = substr(MODULE_SHIPPING_ZIPZONESNAL_ADD, 0, -1);
				if ($add > 0) $shipping_cost += $shipping_cost*$add/100;
			  } else {
				$shipping_cost += MODULE_SHIPPING_ZIPZONESNAL_ADD;
			  }
			}
			if (tep_not_null(MODULE_SHIPPING_ZIPZONESNAL_ADD_1)) {
			  if (substr(MODULE_SHIPPING_ZIPZONESNAL_ADD_1, -1)=='%') {
				$add = substr(MODULE_SHIPPING_ZIPZONESNAL_ADD_1, 0, -1);
				if ($add > 0) $shipping_cost += $order->info['subtotal']*$add/100;
			  } else {
				$shipping_cost += MODULE_SHIPPING_ZIPZONESNAL_ADD_1;
			  }
			}
			$shipping_cost += $additional_shipping_cost;
		  }
		  $shipping_cost = str_replace(',', '.', round($shipping_cost));
		}
	  }

      $this->quotes['id'] = $this->code;
	  $this->quotes['module'] = MODULE_SHIPPING_ZIPZONESNAL_TEXT_TITLE;
	  $this->quotes['methods'] = array(array('id' => $this->code,
											 'title' => $shipping_method,
											 'cost' => $shipping_cost));
$start = $m + $s;
  list($s, $m) = explode(' ', microtime());
  //echo Round(($m + $s)-$start, 5).", ";
      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ZIPZONESNAL_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('–азрешить этот способ доставки', 'MODULE_SHIPPING_ZIPZONESNAL_STATUS', 'True', '¬ы хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('1-€ зона невозможности доставки', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_1', '', '”кажите 1-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('2-€ зона невозможности доставки', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_2', '', '”кажите 2-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '30', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('3-€ зона невозможности доставки', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_3', '', '”кажите 3-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '40', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('4-€ зона невозможности доставки', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_4', '', '”кажите 4-ю географическую зону, в которой <strong>не будет действовать</strong> данный вид доставки', '6', '50', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('«оны авиадоставки', 'MODULE_SHIPPING_ZIPZONESNAL_AVIA_ZONES', '', 'ѕеречислите через зап€тую ID зон, доставка в которые осуществл€етс€ только авиатранспортом', '6', '60', 'tep_cfg_get_zone_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('—тоимость доставки', 'MODULE_SHIPPING_ZIPZONESNAL_COST', '0', '—тоимость доставки (за кг) при невозможности ее вычислени€ в автоматическом режиме', '6', '70', 'currencies->format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ќаложенный платеж', 'MODULE_SHIPPING_ZIPZONESNAL_ADD', '0', '”кажите стоимость (в процентах или в рубл€х) наложенного платежа по отношению к базовой стоимости доставки', '6', '80', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('—траховка', 'MODULE_SHIPPING_ZIPZONESNAL_ADD_1', '0', '”кажите стоимость (в процентах или в рубл€х) сумму страховки по отношению к стоимости заказа', '6', '90', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('—тоимость упаковки', 'MODULE_SHIPPING_ZIPZONESNAL_ADDITIONAL_COST', '0', 'ƒобавл€ть указанную сумму за обработку/упаковку к стоимости доставки', '6', '100', 'currencies->format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('ћаксимальна€ сумма заказа', 'MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM', '0', '”кажите максимальную сумму заказа, до которой возможна доставка наложенным платежом (0 если без ограничений)', '6', '110', 'currencies->format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ѕор€док вывода', 'MODULE_SHIPPING_ZIPZONESNAL_SORT_ORDER', '0', 'ѕор€док вывода доставки на сайте.', '6', '120', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_ZIPZONESNAL_STATUS', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_1', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_2', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_3', 'MODULE_SHIPPING_ZIPZONESNAL_ZONE_4', 'MODULE_SHIPPING_ZIPZONESNAL_AVIA_ZONES', 'MODULE_SHIPPING_ZIPZONESNAL_COST', 'MODULE_SHIPPING_ZIPZONESNAL_ADD', 'MODULE_SHIPPING_ZIPZONESNAL_ADD_1', 'MODULE_SHIPPING_ZIPZONESNAL_ADDITIONAL_COST', 'MODULE_SHIPPING_ZIPZONESNAL_MAX_SUM', 'MODULE_SHIPPING_ZIPZONESNAL_SORT_ORDER');

      return $keys;
    }
  }
?>