<?php
  class shoppingCart {
    var $contents, $total, $weight, $quantity, $cartID, $content_type, $basket_type, $customer_discount;

    function shoppingCart($cart_type='') {
	  $this->basket_type = (empty($cart_type) ? 'cart': $cart_type);
      $this->reset();
	  $this->customer_discount = $this->get_customer_discount();
    }

	function get_customer_discount() {
      global $customer_id, $customer_corporate;

	  $this->customer_discount = array();
	  if ($customer_corporate==1) {
		$customer_discount_info_query = tep_db_query("select customers_discount, customers_discount_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_discount_info = tep_db_fetch_array($customer_discount_info_query);
		$this->customer_discount = array('value' => $customer_discount_info['customers_discount'], 'type' => $customer_discount_info['customers_discount_type']);
	  }

	  return $this->customer_discount;
	}

    function restore_contents() {
      global $customer_id;

      if (!tep_session_is_registered('customer_id')) return false;

// insert current cart contents in database
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $qty = $this->contents[$products_id]['qty'];
          $product_query = tep_db_query("select products_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and shops_id = '" . (int)SHOP_ID . "' and products_id = '" . tep_db_input($products_id) . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
          if (!tep_db_num_rows($product_query)) {
            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added, customers_basket_type, shops_id) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "', '" . tep_db_input($this->basket_type) . "', '" . (int)SHOP_ID . "')");
          } else {
            tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . $qty . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
          }
        }
      }

// reset per-session cart contents, but not the database contents
      $this->reset(false);

      $products_query = tep_db_query("select products_id, customers_basket_quantity from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
      while ($products = tep_db_fetch_array($products_query)) {
		$product_exists = true;
		if ($this->basket_type!='foreign') {
		  $product_check_query = tep_db_query("select products_status from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products['products_id'] . "'");
		  $product_check = tep_db_fetch_array($product_check_query);
		  if ($product_check['products_status'] < 1) $product_exists = false;
		}
		if ($product_exists) $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity']);
      }

      $this->cleanup();
    }

    function reset($reset_database = false) {
      global $customer_id;

      $this->contents = array();
      $this->total = 0;
      $this->weight = 0;
	  $this->quantity = 0;
      $this->content_type = false;
//	  $this->basket_type = 'cart';

      if (tep_session_is_registered('customer_id') && ($reset_database == true)) {
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
      }

      unset($this->cartID);
      if (tep_session_is_registered('cartID')) tep_session_unregister('cartID');
    }

    function add_cart($products_id, $qty = '1', $notify = true) {
      global $new_products_id_in_cart, $customer_id;

      if ($notify == true) {
        $new_products_id_in_cart = $products_id;
        tep_session_register('new_products_id_in_cart');
      }

      if ($this->in_cart($products_id)) {
        $this->update_quantity($products_id, $qty);
      } else {
//		$this->contents[] = array($products_id);
        $this->contents[$products_id] = array('qty' => $qty);
// insert into database
        if (tep_session_is_registered('customer_id')) tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added, customers_basket_type, shops_id) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id) . "', '" . $qty . "', '" . date('Ymd') . "', '" . tep_db_input($this->basket_type) . "', '" . (int)SHOP_ID . "')");
      }
      $this->cleanup();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function update_quantity($products_id, $quantity = '') {
      global $customer_id;

      if (empty($quantity)) return true; // nothing needs to be updated if theres no quantity, so we return true..

      $this->contents[$products_id] = array('qty' => $quantity);
// update database
      if (tep_session_is_registered('customer_id')) tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . $quantity . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
    }

    function cleanup() {
      global $customer_id;

      reset($this->contents);
      while (list($key,) = each($this->contents)) {
        if ($this->contents[$key]['qty'] < 1) {
          unset($this->contents[$key]);
// remove from database
          if (tep_session_is_registered('customer_id')) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($key) . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
          }
        }
      }
    }

    function count_contents() {  // get total number of items in cart 
      $total_items = 0;
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $total_items += $this->get_quantity($products_id);
        }
      }

      return $total_items;
    }

    function get_quantity($products_id) {
      if (isset($this->contents[$products_id])) {
        return $this->contents[$products_id]['qty'];
      } else {
        return 0;
      }
    }

    function in_cart($products_id) {
      if (isset($this->contents[$products_id])) {
        return true;
      } else {
        return false;
      }
    }

    function remove($products_id) {
      global $customer_id;

      unset($this->contents[$products_id]);
// remove from database
      if (tep_session_is_registered('customer_id')) {
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
      }

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function remove_all() {
      $this->reset();
    }

    function get_product_id_list() {
      $product_id_list = '';
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $product_id_list .= ', ' . $products_id;
        }
      }

      return substr($product_id_list, 2);
    }

    function calculate() {
	  $this->get_customer_discount();

      $this->total = 0;
      $this->weight = 0;
	  $this->quantity = 0;
      if (!is_array($this->contents)) return 0;

      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $qty = $this->contents[$products_id]['qty'];

// products price
		if ($this->basket_type=='foreign') {
		  $product_query = tep_db_query("select products_id, products_price from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		} else {
		  $product_query = tep_db_query("select products_id, products_price, products_purchase_cost, products_tax_class_id, products_weight from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "' and products_status = '1'");
		}
        if (tep_db_num_rows($product_query) > 0) {
		  $product = tep_db_fetch_array($product_query);
          $prid = $product['products_id'];
          $products_tax = tep_get_tax_rate($product['products_tax_class_id']);
          $products_price = $product['products_price'];
          $products_weight = $product['products_weight'];

		  if ($this->basket_type=='foreign') {
		  } else {
			$specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$prid . "' and status = '1' and specials_new_products_price > '0' order by specials_date_added desc limit 1");
			if (tep_db_num_rows($specials_query)) {
			  $specials = tep_db_fetch_array($specials_query);
			  $products_price = $specials['specials_new_products_price'];
			} elseif ($this->customer_discount['type']=='purchase' && $product['products_purchase_cost'] > 0) {
			  $products_price = $product['products_purchase_cost'] * (1 + $this->customer_discount['value']/100);
			}
		  }

		  $this->total += tep_add_tax($products_price, $products_tax) * $qty;
		  $this->weight += ($qty * $products_weight);
		  $this->quantity += $qty;
        }
      }
    }

    function get_products() {
      global $languages_id;

	  $this->get_customer_discount();

      if (!is_array($this->contents)) return false;

	  $max_available_in = 0;
      $products_array = array();
      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $products_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
        if ($products = tep_db_fetch_array($products_query)) {
		  $product_description_info_query = tep_db_query("select products_name, products_description, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages_id . "'");
		  $product_description_info = tep_db_fetch_array($product_description_info_query);
		  if (!is_array($product_description_info)) $product_description_info = array();
		  if (DEFAULT_LANGUAGE_ID==1) {
			$product_description_en_info_query = tep_db_query("select products_name, products_description, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and products_name <> ''");
			$product_description_en_info = tep_db_fetch_array($product_description_en_info_query);
			if (!is_array($product_description_en_info)) $product_description_en_info = array();
			if (tep_not_null($product_description_en_info['products_name'])) $product_description_info['products_name'] = $product_description_en_info['products_name'];
			else $product_description_info['products_name'] = tep_transliterate($product_description_info['products_name']);
			if (tep_not_null($product_description_en_info['products_description'])) $product_description_info['products_description'] = $product_description_en_info['products_description'];
			else $product_description_info['products_description'] = tep_transliterate($product_description_info['products_description']);
		  }

		  $manufacturers_name = $product_description_info['manufacturers_name'];
		  if ($products['products_types_id'] == 1) {
			$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$products['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
			$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
			$manufacturers_name = $manufacturer_info['manufacturers_name'];
		  }

		  $author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$products['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		  $author_info = tep_db_fetch_array($author_info_query);
		  if (!is_array($author_info)) $author_info = array();

		  $products = array_merge($products, $product_description_info, $author_info);

          $prid = $products['products_id'];
          $products_price = $products['products_price'];

          $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$prid . "' and status = '1' and specials_new_products_price > '0' order by specials_date_added desc limit 1");
          if (tep_db_num_rows($specials_query)) {
            $specials = tep_db_fetch_array($specials_query);
            $products_price = $specials['specials_new_products_price'];
          } elseif ($this->customer_discount['type']=='purchase' && $products['products_purchase_cost'] > 0) {
			$products_price = $products['products_purchase_cost'] * (1 + $this->customer_discount['value']/100);
		  }

		  $products_tax = tep_get_tax_rate($products['products_tax_class_id']);

		  $products_array[] = array('id' => $products_id,
									'name' => (tep_not_null($products['authors_name']) ? $products['authors_name'] . ': ' : '') . $products['products_name'],
									'description' => $products['products_description'],
									'model' => $products['products_model'],
									'code' => $products['products_code'],
									'image' => $products['products_image'],
									'manufacturer' => $manufacturers_name,
									'year' => $products['products_year'],
									'type' => $products['products_types_id'],
									'periodicity' => $products['products_periodicity'],
									'periodicity_min' => $products['products_periodicity_min'],
									'available_in' => $products['products_available_in'],
									'pack' => $products['products_pack'],
									'price' => $products_price,
									'filename' => $products['products_filename'],
									'quantity' => $this->contents[$products_id]['qty'],
									'weight' => $products['products_weight'],
									'warranty' => $products['products_warranty'],
									'final_price' => tep_add_tax($products_price, $products_tax),
									'tax_class_id' => $products['products_tax_class_id']);
		  if ($products['products_available_in'] > 0 && $products['products_available_in'] > $max_available_in) $max_available_in = $products['products_available_in'];
        }
      }
	  $this->info['delivery_transfer'] = $max_available_in;

      return $products_array;
    }

    function show_total() {
      $this->calculate();

      return $this->total;
    }

    function show_weight() {
      $this->calculate();

      return $this->weight;
    }

    function generate_cart_id($length = 5) {
      return tep_create_random_value($length, 'digits');
    }

	function get_content_type() {
	  $this->content_type = false;

	  if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
		reset($this->contents);
		while (list($products_id, ) = each($this->contents)) {
		  $virtual_check_query = tep_db_query("select products_filename, products_periodicity, products_weight from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		  $virtual_check = tep_db_fetch_array($virtual_check_query);

		  if (tep_not_null($virtual_check['products_filename'])) {
			switch ($this->content_type) {
			  case 'physical':
				$this->content_type = 'mixed';

				return $this->content_type;
				break;
			  default:
				$this->content_type = 'virtual';
				break;
			}
		  } elseif ($virtual_check['products_periodicity'] > 0 && $virtual_check['products_weight'] == 0) {
			switch ($this->content_type) {
			  case 'physical':
				$this->content_type = 'mixed';

				return $this->content_type;
				break;
			  default:
				$this->content_type = 'virtual';
				break;
			}
		  } else {
			switch ($this->content_type) {
			  case 'virtual':
				$this->content_type = 'mixed';

				return $this->content_type;
				break;
			  default:
				$this->content_type = 'physical';
				break;
			}
		  }
		}
	  } else {
		$this->content_type = 'physical';
	  }

	  return $this->content_type;
	}

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }

	function change_notification($products_id, $notify=1) {
	  global $customer_id;

	  //notify = 0 - нет уведомления
	  //notify = 1 - уведомление о появлении в продаже
	  //notify = 2 - уведомление о снижении цены
	  if ($notify!=0 && $notify!=1 && $notify!=2) $notify = 0;

	  tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_notify = '" . (int)$notify . "', customers_basket_notify_url = " . ($notify>0 ? "'" . tep_db_input(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_id, 'NONSSL', false)) . "'" : "null") . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . (int)$products_id . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
	}

	function check_notification($products_id) {
	  global $customer_id;

	  $notification_info_query = tep_db_query("select customers_basket_notify from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . (int)$products_id . "' and customers_basket_type = '" . tep_db_input($this->basket_type) . "'");
	  $notification_info = tep_db_fetch_array($notification_info_query);

	  return $notification_info['customers_basket_notify'];
	}

  }
?>