<?php
  class subtable {
    var $code, $title, $description, $icon, $enabled, $defined_handling;

// class constructor
    function subtable() {
      global $order;

      $this->code = 'subtable';
      $this->title = MODULE_SHIPPING_SUBTABLE_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_SUBTABLE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_SUBTABLE_SORT_ORDER;
      $this->icon = '';
	  $this->defined_handling = '';
      $this->tax_class = 0;
      $this->enabled = ((MODULE_SHIPPING_SUBTABLE_STATUS == 'True') ? true : false);

	  if ($order->content_type == 'virtual') $this->enabled = false;

	  if ($this->enabled) {
		$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SUBTABLE_ZONE_1 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		$geozones_check = tep_db_fetch_array($geozones_check_query);
		if ($geozones_check['total'] > 0) {
		  $this->defined_handling = MODULE_SHIPPING_SUBTABLE_HANDLING_1;
		} else {
		  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SUBTABLE_ZONE_2 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
		  $geozones_check = tep_db_fetch_array($geozones_check_query);
		  if ($geozones_check['total'] > 0) {
			$this->defined_handling = MODULE_SHIPPING_SUBTABLE_HANDLING_2;
		  } else {
			$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SUBTABLE_ZONE_3 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
			$geozones_check = tep_db_fetch_array($geozones_check_query);
			if ($geozones_check['total'] > 0) {
			  $this->defined_handling = MODULE_SHIPPING_SUBTABLE_HANDLING_3;
			} else {
			  $geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SUBTABLE_ZONE_4 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
			  $geozones_check = tep_db_fetch_array($geozones_check_query);
			  if ($geozones_check['total'] > 0) {
				$this->defined_handling = MODULE_SHIPPING_SUBTABLE_HANDLING_4;
			  } else {
				$geozones_check_query = tep_db_query("select count(*) as total from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SUBTABLE_ZONE_5 . "' and city_id = '" . tep_db_input($order->delivery['postcode']) . "'");
				$geozones_check = tep_db_fetch_array($geozones_check_query);
				if ($geozones_check['total'] > 0) {
				  $this->defined_handling = MODULE_SHIPPING_SUBTABLE_HANDLING_5;
				}
			  }
			}
		  }
		}

		if (empty($this->defined_handling)) $this->enabled = false;
	  }

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

// class methods
    function quote($method = '') {
      global $order, $cart, $shipping_weight, $shipping_num_boxes;

	  $total_weight = 0;
	  if (is_object($cart)) {
		$total_weight = $cart->show_weight();
	  } elseif (is_object($order)) {
		reset($order->products);
		while (list(, $order_product) = each($order->products)) {
		  $total_weight += $order_product['weight'];
		}
	  }

	  list($table_weight, $table_cost) = explode(":" , $this->defined_handling);
	  $shipping_cost = str_replace(',', '.', $table_cost);
	  $table_weight = str_replace(',', '.', $table_weight);

	  if (MODULE_SHIPPING_SUBTABLE_COST && $total_weight > $table_weight) {
		list($upper_weight, $upper_cost) = explode(":" , MODULE_SHIPPING_SUBTABLE_COST);
		$shipping_cost += round($total_weight-$table_weight)*$upper_cost/$upper_weight;
	  }

	  $this->quotes = array('id' => $this->code,
							'module' => MODULE_SHIPPING_SUBTABLE_TEXT_TITLE,
							'methods' => array(array('id' => $this->code,
													 'title' => MODULE_SHIPPING_SUBTABLE_TEXT_WAY,
													 'cost' => $shipping_cost)));

	  if ($this->tax_class > 0) {
		$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	  }

	  if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

	  return $this->quotes;
	}

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_SUBTABLE_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('��������� ���� ������ ��������', 'MODULE_SHIPPING_SUBTABLE_STATUS', 'True', '�� ������������� ������ ��������� �������� ���� ��������?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('����������� - ���� 1 (�� 10��)', 'MODULE_SHIPPING_SUBTABLE_ZONE_1', '', '������� ����, � ������� ������ ���������� ������, ��������� �� ����� ��� �� 10�� �� ����', '6', '20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('��������� ��������', 'MODULE_SHIPPING_SUBTABLE_HANDLING_1', '2:250', '������� ������� ��������� �������� � 1-� ���� � ��� �� �������� ��� ����� �����������. ��������: 2:250 (�� 2�� ����� 250).', '6', '30', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('����������� - ���� 2 (�� 10�� �� 20��)', 'MODULE_SHIPPING_SUBTABLE_ZONE_2', '', '������� ����, � ������� ������ ���������� ������, ��������� �� ����� ��� �� 10�� � �� ����� ��� �� 20�� �� ����', '6', '40', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('��������� ��������', 'MODULE_SHIPPING_SUBTABLE_HANDLING_2', '2:350', '������� ������� ��������� �������� �� 2-� ���� � ��� �� �������� ��� ����� �����������. ��������: 2:350 (�� 2�� ����� 350).', '6', '50', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('����������� - ���� 3 (�� 20�� �� 30��)', 'MODULE_SHIPPING_SUBTABLE_ZONE_3', '', '������� ����, � ������� ������ ���������� ������, ��������� �� ����� ��� �� 20�� � �� ����� ��� �� 30�� �� ����', '6', '60', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('��������� ��������', 'MODULE_SHIPPING_SUBTABLE_HANDLING_3', '2:450', '������� ������� ��������� �������� � 3-� ���� � ��� �� �������� ��� ����� �����������. ��������: 2:450 (�� 2�� ����� 450).', '6', '70', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('����������� - ���� 3 (�� 30�� �� 40��)', 'MODULE_SHIPPING_SUBTABLE_ZONE_4', '', '������� ����, � ������� ������ ���������� ������, ��������� �� ����� ��� �� 30�� � �� ����� ��� �� 40�� �� ����', '6', '80', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('��������� ��������', 'MODULE_SHIPPING_SUBTABLE_HANDLING_4', '2:550', '������� ������� ��������� �������� � 4-� ���� � ��� �� �������� ��� ����� �����������. ��������: 2:550 (�� 2�� ����� 550).', '6', '90', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('����������� - ���� 3 (�� 40�� �� 50��)', 'MODULE_SHIPPING_SUBTABLE_ZONE_5', '', '������� ����, � ������� ������ ���������� ������, ��������� �� ����� ��� �� 40�� � �� ����� ��� �� 50�� �� ����', '6', '100', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('��������� ��������', 'MODULE_SHIPPING_SUBTABLE_HANDLING_5', '2:650', '������� ������� ��������� �������� � 5-� ���� � ��� �� �������� ��� ����� �����������. ��������: 2:650 (�� 2�� ����� 650).', '6', '110', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('���������� �������� ����', 'MODULE_SHIPPING_SUBTABLE_COST', '1:15', '��������� ���������� �������� ����. ��������: 1:15 (�� ���� ������ ����������� 1�� ����� �������� ���� ��������� 15 � ������� ��������� ��������).', '6', '120', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('������� ������', 'MODULE_SHIPPING_SUBTABLE_SORT_ORDER', '0', '������� ������ �� �����.', '6', '130', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_SUBTABLE_STATUS', 'MODULE_SHIPPING_SUBTABLE_ZONE_1', 'MODULE_SHIPPING_SUBTABLE_HANDLING_1', 'MODULE_SHIPPING_SUBTABLE_ZONE_2', 'MODULE_SHIPPING_SUBTABLE_HANDLING_2', 'MODULE_SHIPPING_SUBTABLE_ZONE_3', 'MODULE_SHIPPING_SUBTABLE_HANDLING_3', 'MODULE_SHIPPING_SUBTABLE_ZONE_4', 'MODULE_SHIPPING_SUBTABLE_HANDLING_4', 'MODULE_SHIPPING_SUBTABLE_ZONE_5', 'MODULE_SHIPPING_SUBTABLE_HANDLING_5', 'MODULE_SHIPPING_SUBTABLE_COST', 'MODULE_SHIPPING_SUBTABLE_SORT_ORDER');
    }
  }
?>