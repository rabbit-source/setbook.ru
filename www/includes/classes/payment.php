<?php
  class payment {
    var $modules, $selected_module;

// class constructor
    function payment($module = '') {
      global $order, $payment, $language, $PHP_SELF, $shipping, $customer_id, $sendto;

      if (defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED)) {

		$shipping_codes = explode('_', $shipping['id']);
		if (sizeof($shipping_codes) > 4) $shipping_code = $shipping_codes[0] . '_' . $shipping_codes[1] . '_' . $shipping_codes[2];
		elseif (sizeof($shipping_codes) > 2) $shipping_code = $shipping_codes[0] . '_' . $shipping_codes[1];
		else $shipping_code = $shipping_codes[0];

		$shipping_to_payment_query = tep_db_query("select payments from " . TABLE_SHIPPING_TO_PAYMENT . " where shipping = '" . tep_db_input($shipping_code) . "' and status = '1'");
		$shipping_to_payment = tep_db_fetch_array($shipping_to_payment_query);
		if (tep_not_null($shipping_to_payment['payments'])){
		  $this->modules = explode(';', $shipping_to_payment['payments']);
		} else{
		  $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);
		}

		$delivery_zone_query = tep_db_query("select entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
		$delivery_zone = tep_db_fetch_array($delivery_zone_query);

		$payment_modules = array();
		reset($this->modules);
		while (list(, $payment_module) = each($this->modules)) {
		  $geo_zone_check_query = tep_db_query("select count(*) as total from " . TABLE_PAYMENT_TO_GEO_ZONES . " where payment = '" . tep_db_input($payment_module) . "'");
		  $geo_zone_check = tep_db_fetch_array($geo_zone_check_query);
		  if ($geo_zone_check['total'] < 1) {
			$payment_modules[] = $payment_module;
		  } else {
			$payment_to_zones_check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES_TO_GEO_ZONES . " z2gz, " . TABLE_PAYMENT_TO_GEO_ZONES . " p2gz where p2gz.status = '1' and z2gz.zone_id = '" . (int)$delivery_zone['entry_zone_id'] . "' and z2gz.geo_zone_id = p2gz.geo_zone_id and p2gz.payment = '" . tep_db_input($payment_module) . "'");
			$payment_to_zones_check = tep_db_fetch_array($payment_to_zones_check_query);
			if ($payment_to_zones_check['total'] > 0) {
			  $payment_modules[] = $payment_module;
			}
		  }
		}
		if (sizeof($payment_modules) > 0) {
		  $this->modules = $payment_modules;
		}

        $include_modules = array();

        if ( (tep_not_null($module)) && (in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $this->selected_module = $module;

          $include_modules[] = array('class' => $module, 'file' => $module . '.php');
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
          include(DIR_WS_LANGUAGES . $language . '/modules/payment/' . $include_modules[$i]['file']);
          include(DIR_WS_MODULES . 'payment/' . $include_modules[$i]['file']);

          $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }

// if there is only one payment method, select it as default because in
// checkout_confirmation.php the $payment variable is being assigned the
// $HTTP_POST_VARS['payment'] value which will be empty (no radio button selection possible)
        if ( (tep_count_payment_modules() == 1) && (!isset($GLOBALS[$payment]) || (isset($GLOBALS[$payment]) && !is_object($GLOBALS[$payment]))) ) {
          $payment = $include_modules[0]['class'];
        }

        if ( (tep_not_null($module)) && (in_array($module, $this->modules)) && (isset($GLOBALS[$module]->form_action_url)) ) {
          $this->form_action_url = $GLOBALS[$module]->form_action_url;
        }
      }
    }

// class methods
/* The following method is needed in the checkout_confirmation.php page
   due to a chicken and egg problem with the payment class and order class.
   The payment modules needs the order destination data for the dynamic status
   feature, and the order class needs the payment module title.
   The following method is a work-around to implementing the method in all
   payment modules available which would break the modules in the contributions
   section. This should be looked into again post 2.2.
*/   
    function update_status() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module])) {
          if (function_exists('method_exists')) {
            if (method_exists($GLOBALS[$this->selected_module], 'update_status')) {
              $GLOBALS[$this->selected_module]->update_status();
            }
          } else { // PHP3 compatibility
            @call_user_method('update_status', $GLOBALS[$this->selected_module]);
          }
        }
      }
    }

    function javascript_validation() {
      $js = '';
      if (is_array($this->modules)) {
        $js = '<script language="javascript" type="text/javascript"><!-- ' . "\n" .
              'function check_form() {' . "\n" .
              '  var error = 0;' . "\n" .
              '  var error_message = "' . JS_ERROR . '";' . "\n" .
              '  var payment_value = null;' . "\n" .
              '  if (document.checkout_payment.payment.length) {' . "\n" .
              '    for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
              '      if (document.checkout_payment.payment[i].checked) {' . "\n" .
              '        payment_value = document.checkout_payment.payment[i].value;' . "\n" .
              '      }' . "\n" .
              '    }' . "\n" .
              '  } else if (document.checkout_payment.payment.checked) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  } else if (document.checkout_payment.payment.value) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  }' . "\n\n";

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $js .= $GLOBALS[$class]->javascript_validation();
          }
        }

        $js .= "\n" . '  if (payment_value == null) {' . "\n" .
               '    error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n" .
               '  if (error == 1) {' . "\n" .
               '    alert(error_message);' . "\n" .
               '    return false;' . "\n" .
               '  } else {' . "\n" .
               '    return true;' . "\n" .
               '  }' . "\n" .
               '}' . "\n" .
               '//--></script>' . "\n";
      }

      return $js;
    }

    function selection() {
      $selection_array = array();

      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $selection = $GLOBALS[$class]->selection();
            if (is_array($selection)) $selection_array[] = $selection;
          }
        }
      }

      return $selection_array;
    }

    function pre_confirmation_check() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          $GLOBALS[$this->selected_module]->pre_confirmation_check();
        }
      }
    }

    function confirmation() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->confirmation();
        }
      }
    }

    function process_button() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->process_button();
        }
      }
    }

    function before_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->before_process();
        }
      }
    }

    function after_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->after_process();
        }
      }
    }

    function get_error() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->get_error();
        }
      }
    }
  }
?>