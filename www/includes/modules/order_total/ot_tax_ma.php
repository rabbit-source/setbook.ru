<?php
  class ot_tax_ma {
    var $title, $output;

    function ot_tax_ma() {
	  global $order, $customer_id;

      $this->code = 'ot_tax_ma';
      $this->title = MODULE_ORDER_TOTAL_TAX_MA_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_MA_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_TAX_MA_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_TAX_MA_SORT_ORDER;
	  $this->defined_handling = '';

      $this->output = array();

	  if ($this->enabled) {
		list($postcode) = explode('-', $order->delivery['postcode']);
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_ORDER_TOTAL_TAX_MA_ZONE . "' and city_id = '" . tep_db_input($postcode) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ($geozones_check['total'] > 0) {
		  $company_info_query = tep_db_query("select companies_name, companies_tax_exempt_number from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customer_id . "'");
		  $company_info = tep_db_fetch_array($company_info_query);
		  if (strpos(strtolower($company_info['companies_name']), 'library')!==false || $company_info['companies_tax_exempt_number'] > 0) $this->enabled = false;
		  else $this->defined_handling = str_replace(',', '.', MODULE_ORDER_TOTAL_TAX_MA_HANDLING);
		} else {
		  $this->enabled = false;
		}
	  }
    }

    function process() {
      global $order, $currencies;

	  $subtotal = 0;
	  reset($order->products);
	  while (list(, $product) = each($order->products)) {
		$subtotal += $product['final_price'] * $product['qty'];
	  }
//	  $tax_value = str_replace(',', '.', $subtotal * $this->defined_handling / 100);
	 // $tax_value = tep_round(str_replace(',', '.', round($subtotal * $this->defined_handling / 100)), $currencies->get_decimal_places($order->info['currency']));
	  $tax_value = $subtotal * $this->defined_handling / 100;

	  $this->output[] = array('title' => sprintf(MODULE_ORDER_TOTAL_TAX_MA_TITLE_DEFINED, $this->defined_handling) . ':',
							  'text' => $currencies->format($tax_value, true, $order->info['currency'], $order->info['currency_value']),
							  'value' => $tax_value);

	  $order->info['total'] += $tax_value;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_MA_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TAX_MA_STATUS', 'MODULE_ORDER_TOTAL_TAX_MA_ZONE', 'MODULE_ORDER_TOTAL_TAX_MA_TITLE_DEFINED', 'MODULE_ORDER_TOTAL_TAX_MA_HANDLING', 'MODULE_ORDER_TOTAL_TAX_MA_SORT_ORDER');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Tax', 'MODULE_ORDER_TOTAL_TAX_MA_STATUS', 'false', '¬ы действительно хотите включить налоги?', '6', '10','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('«она действи€ налога', 'MODULE_ORDER_TOTAL_TAX_MA_ZONE', '', '”кажите зону, в которой будет действовать данный налог', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ќаименование налога', 'MODULE_ORDER_TOTAL_TAX_MA_TITLE_DEFINED', '', '¬ведите наименование налога в том виде, в котором оно будет указано на сайте, &quot;%s&quot; будет заменено на ставку налога.', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('–азмер налога', 'MODULE_ORDER_TOTAL_TAX_MA_HANDLING', '0', '”кажите размер (в процентах) налога, который будет действовать в данной зоне.', '6', '40', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_MA_SORT_ORDER', '3', 'Sort order of display.', '6', '50', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>