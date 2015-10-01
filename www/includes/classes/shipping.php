<?php
  class shipping {
    var $modules;

// class constructor
    function shipping($module = '') {
      global $order, $language, $PHP_SELF, $customer_id, $sendto;

      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

		$delivery_zone_query = tep_db_query("select entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
		$delivery_zone = tep_db_fetch_array($delivery_zone_query);

		$shipping_modules = array();
		reset($this->modules);
		while (list(, $shipping_module) = each($this->modules)) {
		  $geo_zone_check_query = tep_db_query("select count(*) as total from " . TABLE_SHIPPING_TO_GEO_ZONES . " where shipping = '" . tep_db_input($shipping_module) . "'");
		  $geo_zone_check = tep_db_fetch_array($geo_zone_check_query);
		  if ($geo_zone_check['total'] < 1) {
			$shipping_modules[] = $shipping_module;
		  } else {
			$shipping_to_zones_check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES_TO_GEO_ZONES . " z2gz, " . TABLE_SHIPPING_TO_GEO_ZONES . " s2gz where s2gz.status = '1' and z2gz.zone_id = '" . (int)$delivery_zone['entry_zone_id'] . "' and z2gz.geo_zone_id = s2gz.geo_zone_id and s2gz.shipping = '" . tep_db_input($shipping_module) . "'");
			$shipping_to_zones_check = tep_db_fetch_array($shipping_to_zones_check_query);
			if ($shipping_to_zones_check['total'] > 0) {
			  $shipping_modules[] = $shipping_module;
			}
		  }
		}
		if (sizeof($shipping_modules) > 0) {
		  $this->modules = $shipping_modules;
		}

        $include_modules = array();

        if ( (tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
          include(DIR_WS_LANGUAGES . $language . '/modules/shipping/' . $include_modules[$i]['file']);
          include(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);

          $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }
      }
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

      $quotes_array = array();

      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (tep_not_null($module)) {
        	tep_log_ex('$value='.$value.', $class='.$class.', $module='.$module.', enabled='.$GLOBALS[$class]->enabled);
          	if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
            $include_quotes[] = $class;
          }
        }

        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) $quotes_array[] = $quotes;
        }
        
      }



      return $quotes_array;
    }

    function cheapest() {
      if (is_array($this->modules)) {
        $rates = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
//			  if (isset($quotes['methods'][$i]['cost']) && tep_not_null($quotes['methods'][$i]['cost'])) {
			  if (isset($quotes['methods'][$i]['cost'])) {
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'],# . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
              }
            }
          }
        }

        $cheapest = false;
        for ($i=0, $n=sizeof($rates); $i<$n; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }

        return $cheapest;
      }
    }
  }
?>