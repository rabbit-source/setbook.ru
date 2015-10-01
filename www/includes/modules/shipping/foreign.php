<?php
  class foreign {
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function foreign() {
      global $order;

      $this->code = 'foreign';
      $this->title = MODULE_SHIPPING_FOREIGN_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FOREIGN_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_FOREIGN_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_FOREIGN_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;
    }

// class methods
    function quote($method = '') {
      global $order, $cart, $currencies, $shipping_weight, $customer_type, $customer_id;

	  $currency_value = $currencies->currencies[MODULE_SHIPPING_FOREIGN_CURRENCY]['value'];

	  $calculate_by_weight = false;
	  $calculate_by_qty = false;

	  if (tep_not_null(MODULE_SHIPPING_FOREIGN_HANDLING_WEIGHT)) {
		$calculate_by_weight = true;
		$base_shipping = str_replace(',', '.', MODULE_SHIPPING_FOREIGN_HANDLING_WEIGHT);
	  } else {
		$calculate_by_qty = true;
		$base_shipping = str_replace(',', '.', MODULE_SHIPPING_FOREIGN_HANDLING);
	  }

	  $countries_cost = array_map('trim', explode(',' , MODULE_SHIPPING_FOREIGN_COEFFICIENTS));
	  $size = sizeof($countries_cost);
	  for ($i=0, $n=$size; $i<$n; $i++) {
		list($countries_iso_code_2, $countries_additional_shipping_cost) = explode(':', $countries_cost[$i]);
		if ($order->delivery['country']['iso_code_2'] == $countries_iso_code_2) {
		  $base_shipping += $countries_additional_shipping_cost;
		  break;
		}
	  }

	  $shipping_1 = 0;
	  $shipping_2 = 0;
	  $sum_1 = 0;
	  $sum_2 = 0;
	  $total_qty = 0;
	  $total_weight = 0;
	  $periodicals_weights = array();
	  $periodicals_qtys = array();
	  if (is_object($cart)) {
		$products_in_cart = $cart->get_products();
		reset($products_in_cart);
		while (list($i, $product_in_cart) = each($products_in_cart)) {
		  if ($product_in_cart['periodicity'] > 0) {
			if ($calculate_by_weight) $shipping_2 += ($base_shipping + (ceil($product_in_cart['weight'])-1) * str_replace(',', '.', MODULE_SHIPPING_FOREIGN_COST_WEIGHT)) * $product_in_cart['quantity'];
			elseif ($product_in_cart['weight'] > 0) $shipping_2 += $base_shipping * $product_in_cart['quantity'];
			$sum_2 += $product_in_cart['final_price'] * $product_in_cart['quantity'];
			$periodicals_weights[] = $product_in_cart['weight'];
			$periodicals_qtys[] = $product_in_cart['quantity'];
		  } else {
			$total_qty += $product_in_cart['quantity'];
			$total_weight += $product_in_cart['weight'] * $product_in_cart['quantity'];
			$sum_1 += $product_in_cart['final_price'] * $product_in_cart['quantity'];
		  }
		}
	  }
	  $shipping_2 = 0;

	  if ($total_qty > 0) {
		if ($calculate_by_weight) $shipping_1 = $base_shipping + (ceil($total_weight)-1) * str_replace(',', '.', MODULE_SHIPPING_FOREIGN_COST_WEIGHT);
		else $shipping_1 = $base_shipping + ($total_qty-1) * str_replace(',', '.', MODULE_SHIPPING_FOREIGN_COST);
	  }

	  $free_shipping = str_replace(',', '.', MODULE_SHIPPING_FOREIGN_FREE);
	  if (MODULE_SHIPPING_FOREIGN_FREE_SPECIFY=='False' && $customer_type=='corporate') {
		$free_shipping = 0;
	  }
	  if ($free_shipping>0 && defined('MODULE_SHIPPING_FOREIGN_FREE_ONLY')) {
		if (tep_not_null(MODULE_SHIPPING_FOREIGN_FREE_ONLY)) {
		  $only_countries = array_map('trim', explode(',', MODULE_SHIPPING_FOREIGN_FREE_ONLY));
		  if (!in_array($order->delivery['country']['iso_code_2'], $only_countries)) $free_shipping = 0;
		}
	  }
	  if ($free_shipping>0 && defined('MODULE_SHIPPING_FOREIGN_FREE_NOT_ONLY')) {
		if (tep_not_null(MODULE_SHIPPING_FOREIGN_FREE_NOT_ONLY)) {
		  $only_countries = array_map('trim', explode(',', MODULE_SHIPPING_FOREIGN_FREE_NOT_ONLY));
		  if (in_array($order->delivery['country']['iso_code_2'], $only_countries)) $free_shipping = 0;
		}
	  }
	  if ($free_shipping > 0) {
		if ($sum_1*$currency_value >= $free_shipping) {
		  $shipping_1 = 0;
		}
		if ($shipping_1==0 && $total_qty>0) {
		  $this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING, $currencies->format($free_shipping, false, MODULE_SHIPPING_FOREIGN_CURRENCY)) . '</span>)';
		}
	  }
	  if ($currency_value > 0) $shipping_cost = str_replace(',', '.', ($shipping_1 + $shipping_2)/$currency_value);
	  $shipping_cost = tep_round($shipping_cost, $currencies->get_decimal_places(MODULE_SHIPPING_FOREIGN_CURRENCY));

	  $methods = array();
	  if ( ($customer_id==2 || $customer_id==37056) && (SHOP_ID==14 || SHOP_ID==16)) {
		include(DIR_WS_CLASSES . 'excel.php');
		$data = new excel;
		$data->setOutputEncoding('cp1251');
		$data->read(DIR_WS_MODULES . 'shipping/foreign.xls');

		$cells = $data->sheets[0]['cells'];
		$cells_1 = $data->sheets[1]['cells'];
//		echo '<pre>' . print_r($cells_1, true) . '</pre>'; die();

		$country_found = false;
		$fc_zone = 0;
		$pr_zone = 0;
		$eu_zone = 0;
		$is_us = false;
		$is_eu = false;
		$fc_shipping_cost = 0;
		$pr_shipping_cost = 0;
		$eu_shipping_cost = 0;
		$us_shipping_cost = 0;
		reset($cells);
		while (list(, $cell) = each($cells)) {
		  if ($cell[1]==$order->delivery['country']['iso_code_2']) {
			$fc_zone = (int)$cell[2];
			$pr_zone = (int)$cell[3];
			if ($fc_zone=='0') {
			  $eu_zone = 0;
			  $fc_zone = 0;
			  $pr_zone = 0;
			  $is_us = true;
			}
			if ($cell[4] > 0) {
			  $eu_zone = (int)$cell[4];
			  $fc_zone = 0;
			  $pr_zone = 0;
			  $is_eu = true;
			}
			$country_found = true;
			break;
		  }
		}

		if ($country_found) {
		  $fc_shipping_title = $cells_1[1][1];
		  $pr_shipping_title = $cells_1[1][6];
		  $us_shipping_title = $cells_1[1][11];
		  $eu_shipping_title = $cells_1[1][16];
		  if ($is_us) {
			$pounds_total_weight = ceil($total_weight/0.4536)*0.4536;
			$us_shipping_cost = ($pounds_total_weight * $cells_1[2][13] + $cells_1[2][14]) / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
			if ($free_shipping > 0 && $sum_1*$currency_value >= $free_shipping) {
			  $us_shipping_cost = 0;
			  $this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING, $currencies->format($free_shipping, false, MODULE_SHIPPING_FOREIGN_CURRENCY)) . '</span>)';
			}
			reset($periodicals_weights);
			while (list($i, $periodicals_weight) = each($periodicals_weights)) {
			  $pounds_periodicals_weight = ceil($periodicals_weight/0.4536)*0.4536;
			  $us_shipping_cost += ($pounds_periodicals_weight * $cells_1[2][13] + $cells_1[2][14]) * $periodicals_qtys[$i] / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
			}
			$methods[] = array('id' => $this->code . '_media',
							   'title' => $us_shipping_title,
							   'cost' => $us_shipping_cost);
		  } elseif ($is_eu) {
			reset($cells_1);
			while (list(, $cell) = each($cells_1)) {
			  if ($cell[1]==$eu_zone) {
				$cell_18 = $cell[18];
				$cell_19 = $cell[19];
				if (MODULE_SHIPPING_FOREIGN_CURRENCY!='EUR') {
				  $cell_18 *= $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY) / $currencies->get_value('EUR');
				  $cell_19 *= $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY) / $currencies->get_value('EUR');
				}
				$pounds_total_weight = ceil($total_weight/2)*2;
				$eu_shipping_cost = ($total_weight * $cell_18 + $cell_19) / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				if ($free_shipping > 0 && $sum_1*$currency_value >= $free_shipping) {
				  $eu_shipping_cost = 0;
				  $this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING, $currencies->format($free_shipping, false, MODULE_SHIPPING_FOREIGN_CURRENCY)) . '</span>)';
				}
				reset($periodicals_weights);
				while (list($i, $periodicals_weight) = each($periodicals_weights)) {
				  $pounds_periodicals_weight = ceil($periodicals_weight/2)*2;
				  $eu_shipping_cost += ($pounds_periodicals_weight * $cell_18 + $cell_19) * $periodicals_qtys[$i] / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				}
				$methods[] = array('id' => $this->code . '_dpd',
								   'title' => $eu_shipping_title,
								   'cost' => $eu_shipping_cost);
			  }
			}
		  } else {
			while (list(, $cell) = each($cells_1)) {
			  if ($cell[1]==$fc_zone) {
				$use_this_method = true;
				if ($cell[2] > 0) {
				  if ($total_weight > $cell[2]) {
					$use_this_method = false;
				  } else {
					reset($periodicals_weights);
					while (list(, $periodicals_weight) = each($periodicals_weights)) {
					  if ($periodicals_weight > $cell[2]) {
						$use_this_method = false;
					  }
					}
				  }
				}
				if ($use_this_method) {
				  $pounds_total_weight = ceil($total_weight*4/0.4536)*0.4536/4;
				  $fc_shipping_cost = ($pounds_total_weight * $cell[3] + $cell[4]) / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				  if ($free_shipping > 0 && $sum_1*$currency_value >= $free_shipping) {
					$fc_shipping_cost = 0;
					$this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING, $currencies->format($free_shipping, false, MODULE_SHIPPING_FOREIGN_CURRENCY)) . '</span>)';
				  }
				  reset($periodicals_weights);
				  while (list($i, $periodicals_weight) = each($periodicals_weights)) {
					$pounds_periodicals_weight = ceil($periodicals_weight*4/0.4536)*0.4536/4;
					$fc_shipping_cost += ($pounds_periodicals_weight * $cell[3] + $cell[4]) * $periodicals_qtys[$i] / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				  }
				  $methods[] = array('id' => $this->code . '_firstclass',
									 'title' => $fc_shipping_title,
									 'cost' => $fc_shipping_cost);
				}
			  }
			  if ($cell[6]==$pr_zone) {
				$use_this_method = true;
				if ($cell[7] > 0) {
				  if ($total_weight > $cell[7]) {
					$use_this_method = false;
				  } else {
					reset($periodicals_weights);
					while (list(, $periodicals_weight) = each($periodicals_weights)) {
					  if ($periodicals_weight > $cell[7]) {
						$use_this_method = false;
					  }
					}
				  }
				}
				if ($use_this_method) {
				  $pounds_total_weight = ceil($total_weight/0.4536)*0.4536;
				  $pr_shipping_cost = ($pounds_total_weight * $cell[8] + $cell[9]) / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				  if ($free_shipping > 0 && $sum_1*$currency_value >= $free_shipping) {
					$pr_shipping_cost = 0;
					$this->icon = ' (<span class="errorText">' . sprintf(MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING, $currencies->format($free_shipping, false, MODULE_SHIPPING_FOREIGN_CURRENCY)) . '</span>)';
				  }
				  if ($free_shipping > 0 && $sum_1*$currency_value >= $free_shipping) $pr_shipping_cost = 0;
				  reset($periodicals_weights);
				  while (list($i, $periodicals_weight) = each($periodicals_weights)) {
					$pounds_periodicals_weight = ceil($periodicals_weight/0.4536)*0.4536;
					$pr_shipping_cost += ($pounds_periodicals_weight * $cell[3] + $cell[4]) * $periodicals_qtys[$i] / $currencies->get_value(MODULE_SHIPPING_FOREIGN_CURRENCY);
				  }
				  $methods[] = array('id' => $this->code . '_priority',
									 'title' => $pr_shipping_title,
									 'cost' => $pr_shipping_cost);
				}
			  }
			}
		  }
		}
	  }

	  if (sizeof($methods)==0) {
		$methods = array(array('id' => $this->code,
							   'title' => MODULE_SHIPPING_FOREIGN_WAY,
							   'cost' => $shipping_cost));
	  }

	  $this->quotes = array('id' => $this->code,
							'module' => MODULE_SHIPPING_FOREIGN_TITLE,
							'methods' => $methods);

	  if ($this->tax_class > 0) {
		$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	  }

	  if (tep_not_null($this->icon)) $this->quotes['icon'] = $this->icon;

	  return $this->quotes;
	}

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FOREIGN_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Разрешить этот способ доставки', 'MODULE_SHIPPING_FOREIGN_STATUS', 'True', 'Вы действительно хотите разрешить доставку этим способом?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Название', 'MODULE_SHIPPING_FOREIGN_TITLE', 'US Postal service', 'Введите название, которое будет отображаться на сайте.', '6', '20', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Описание', 'MODULE_SHIPPING_FOREIGN_WAY', 'Доставка почтовой службой США', 'Введите краткое описание, которое будет отображаться на сайте.', '6', '30', 'tep_cfg_textarea(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Валюта', 'MODULE_SHIPPING_FOREIGN_CURRENCY', 'USD', 'Выберите валюту, в которой будут указываться цены.', '6', '40', 'tep_cfg_pull_down_currencies(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Базовая стоимость доставки 1 шт.', 'MODULE_SHIPPING_FOREIGN_HANDLING', '5', 'Укажите (в выбранной валюте) базовую стоимость доставки', '6', '50', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Превышение базового количества', 'MODULE_SHIPPING_FOREIGN_COST', '0.5', 'укажите (в выбранной валюте) значение надбавки при превышении базового количества.', '6', '60', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Базовая стоимость доставки 1кг', 'MODULE_SHIPPING_FOREIGN_HANDLING_WEIGHT', '5', 'Укажите (в выбранной валюте) базовую стоимость доставки 1кг товара', '6', '70', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Превышение базового веса', 'MODULE_SHIPPING_FOREIGN_COST_WEIGHT', '0.5', 'укажите (в выбранной валюте) значение надбавки при превышении базового веса.', '6', '80', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Коэффициенты', 'MODULE_SHIPPING_FOREIGN_COEFFICIENTS', 'DE:-5,FI:5', 'укажите коды стран и (в выбранной валюте) корректирующие коэффициенты базового веса.', '6', '90', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Бесплатная доставка', 'MODULE_SHIPPING_FOREIGN_FREE', '', 'Укажите сумму заказа (в выбранной валюте), при которой доставка будет бесплатной', '6', '100', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Бесплатная доставка только в', 'MODULE_SHIPPING_FOREIGN_FREE_ONLY', '', 'Укажите коды стран (пример: DE, US, BR), и бесплатная доставка будет действовать только для этих стран', '6', '110', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Бесплатная доставка везде кроме', 'MODULE_SHIPPING_FOREIGN_FREE_NOT_ONLY', '', 'Укажите коды стран (пример: DE, US, BR), и бесплатная доставка не будет действовать для этих стран', '6', '120', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Бесплатная доставка для организаций', 'MODULE_SHIPPING_FOREIGN_FREE_SPECIFY', '', 'Будет ли действовать условие бесплатной доставки для организаций (True - да, False - нет)', '6', '130', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Порядок вывода', 'MODULE_SHIPPING_FOREIGN_SORT_ORDER', '0', 'Порядок вывода на сайте.', '6', '140', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_FOREIGN_STATUS', 'MODULE_SHIPPING_FOREIGN_TITLE', 'MODULE_SHIPPING_FOREIGN_WAY', 'MODULE_SHIPPING_FOREIGN_CURRENCY', 'MODULE_SHIPPING_FOREIGN_HANDLING', 'MODULE_SHIPPING_FOREIGN_COST', 'MODULE_SHIPPING_FOREIGN_HANDLING_WEIGHT', 'MODULE_SHIPPING_FOREIGN_COST_WEIGHT', 'MODULE_SHIPPING_FOREIGN_COEFFICIENTS', 'MODULE_SHIPPING_FOREIGN_FREE', 'MODULE_SHIPPING_FOREIGN_FREE_ONLY', 'MODULE_SHIPPING_FOREIGN_FREE_NOT_ONLY', 'MODULE_SHIPPING_FOREIGN_FREE_SPECIFY', 'MODULE_SHIPPING_FOREIGN_SORT_ORDER');
    }
  }
?>