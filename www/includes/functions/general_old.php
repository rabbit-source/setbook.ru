<?php
////
// Stop from parsing any further PHP code
  function tep_exit() {
   tep_session_close();
   tep_db_close();
   die();
  }

////
// Redirect to another page or site
  function tep_redirect($url, $response_code = 0) {
	$url = str_replace('&amp;', '&', $url);

	if (basename($url)==FILENAME_ERROR_404) $response_code = '301';

	if ($response_code > 0) {
	  header('location: ' . $url, true, $response_code);
	} else {
	  header('location: ' . $url);
	}
	
    tep_exit();
  }

  function tep_redirect_to_shop($shop_id='', $country_code='') {
	global $request_type, $_SERVER;

	$shop_url = '';
	if ($shop_id > 0) {
	  $shop_info_query = tep_db_query("select shops_url, shops_ssl from " . TABLE_SHOPS . " where shops_id = '" . (int)$shop_id . "'");
	  $shop_info = tep_db_fetch_array($shop_info_query);
	  if ($shop_id!=SHOP_ID) $shop_url = (($request_type=='SSL' && tep_not_null($shop_info['shops_ssl'])) ? $shop_info['shops_ssl'] : $shop_info['shops_url']);
	} elseif (tep_not_null($country_code)) {
	   $all_countries = tep_get_shops_countries(0, 1);
	   if ($all_countries[$country_code]['shop_id']!=SHOP_ID) $shop_url = (($request_type=='SSL' && tep_not_null($all_countries[$country_code]['shop_ssl'])) ? $all_countries[$country_code]['shop_ssl'] : $all_countries[$country_code]['shop_url']);
	}

	if (tep_not_null($shop_url)) tep_redirect($shop_url . $_SERVER['REQUEST_URI'], '301');
  }

////
// Parse the data used in the html tags to ensure the tags will not break
  function tep_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

  function tep_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return str_replace("'", '&#039;', htmlspecialchars(stripslashes(strip_tags(trim($string)))));
    } else {
      if ($translate == false) {
        return tep_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return tep_parse_input_field_data($string, $translate);
      }
    }
  }

  function tep_output_string_protected($string) {
    return tep_output_string($string, false, true);
  }

  function tep_sanitize_string($string) {
    $string = preg_replace('/\s{2,}/', ' ', trim($string));

    return preg_replace("/[<>]/", '_', $string);
  }

  function tep_calculate_date_available($days, $include_disabled_days = true) {
	$date_available = '0000-00-00';
	$disabled_days = array('01.01', '02.01', '03.01', '04.01', '05.01', '06.01', '07.01', '08.01', '09.01', '10.01', '23.02', '07.03', '08.03', '01.05', '02.05', '09.05', '12.06', '04.11', '31.12');
	$available_days = array();
	if (ALLOW_SHOW_AVAILABLE_IN=='true') {
	  $i = time();
	  if ($include_disabled_days==true) {
		$k = 0;
		while ($k<$days) {
		  if (in_array(date('d.m', $i), $available_days) || (date('w', $i)!=0 && date('w', $i)!=6 && !in_array(date('d.m', $i), $disabled_days)) ) $k ++;
		  $i += 60*60*24;
		}
	  } else {
		$i += $days*60*60*24;
	  }
	  $date_available = date('Y-m-d H:i:s', $i);
	}
	return $date_available;
  }

////
// Return a random row from a database query
  function tep_random_select($query) {
    $random_product = '';
    $random_query = tep_db_query($query);
    $num_rows = tep_db_num_rows($random_query);
    if ($num_rows > 0) {
      $random_row = tep_rand(0, ($num_rows - 1));
      tep_db_data_seek($random_query, $random_row);
      $random_product = tep_db_fetch_array($random_query);
    }

    return $random_product;
  }

////
// Return a product's name
// TABLES: products
  function tep_get_products_info($product_id, $language = '', $field = '') {
	global $languages_id;

	if (empty($language)) $language = $languages_id;
	if (empty($field)) $field = 'products_name';

	$product_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language . "'");
	$product = tep_db_fetch_array($product_query);

	return $product[$field];
  }

////
// Return a author's name
// TABLES: authors
  function tep_get_authors_info($author_id, $language = '', $field = '') {
	global $languages_id;

	if (empty($language)) $language = $languages_id;
	if (empty($field)) $field = 'authors_name';

	$author_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_AUTHORS . " where authors_id = '" . (int)$author_id . "' and language_id = '" . (int)$language . "'");
	$author = tep_db_fetch_array($author_query);

	return $author[$field];
  }

////
// Return a author's name
// TABLES: authors
  function tep_get_series_info($serie_id, $language = '', $field = '') {
	global $languages_id;

	if (empty($language)) $language = $languages_id;
	if (empty($field)) $field = 'series_name';

	$serie_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_SERIES . " where series_id = '" . (int)$serie_id . "' and language_id = '" . (int)$language . "'");
	$serie = tep_db_fetch_array($serie_query);

	return $serie[$field];
  }

////
// Return a product's special price (returns nothing if there is no offer)
// TABLES: products
  function tep_get_products_special_price($product_id) {
    $product_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status = '1' and specials_new_products_price > '0' order by specials_date_added desc limit 1");
    $product = tep_db_fetch_array($product_query);
	if ($product['specials_new_products_price'] > 0) return $product['specials_new_products_price'];

    return false;
  }

////
// Return a product's stock
// TABLES: products
  function tep_get_products_stock($products_id) {
    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
    $stock_values = tep_db_fetch_array($stock_query);

    return $stock_values['products_quantity'];
  }

////
// Check if the required stock is available
// If insufficent stock is available return an out of stock message
  function tep_check_stock($products_id, $products_quantity) {
    $stock_left = tep_get_products_stock($products_id) - $products_quantity;
    $out_of_stock = '';

    if ($stock_left < 0) {
      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }

    return $out_of_stock;
  }

////
// Break a word in a string if it is longer than a specified length ($len)
  function tep_break_string($string, $len, $break_char = '-') {
    $l = 0;
    $output = '';
    for ($i=0, $n=strlen($string); $i<$n; $i++) {
      $char = substr($string, $i, 1);
      if ($char != ' ') {
        $l++;
      } else {
        $l = 0;
      }
      if ($l > $len) {
        $l = 1;
        $output .= $break_char;
      }
      $output .= $char;
    }

    return $output;
  }

  function tep_html_entity_decode($string) {
	// replace numeric entities
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
	// replace literal entities
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
  }

////
// Break a string if it is longer than a specified length ($len)
  function tep_cut_string($string, $len, $break_chars = '.,!?') {
    $output = '';

	$string = strip_tags(tep_html_entity_decode($string));
	$short_string = substr($string, 0, $len);

	if (strlen($string) > $len) {
	  $substr_to = 0;
	  for ($i=0; $i<strlen($break_chars); $i++) {
		$pp = strrpos($short_string, $break_chars[$i]);
		if ($pp > $substr_to) $substr_to = $pp;
	  }
	  $output = substr($short_string, 0, $substr_to);
	} else {
	  $output = $string;
	}

    return $output;
  }

  function tep_output_csv($csv_data, $separator = ';', $enclosure = '"', $enclosure_required = false) {
	if (!is_array($csv_data)) $csv_data_array = array($csv_data);
	else $csv_data_array = $csv_data;

	if (empty($separator)) $separator = ';';
	if (empty($enclosure)) $enclosure = '"';

	ob_start();
	$out = fopen('php://output', 'w');
	fputcsv($out, $csv_data_array, $separator, $enclosure);
	fclose($out);
	$csv_string = preg_replace("/[\r\n]$/", '', ob_get_clean());
	if (!is_array($csv_data) && $enclosure_required == true) {
	  if (substr($csv_string, 0, 1)!='"') $csv_string = '"' . $csv_string . '"';
	}
	if ($enclosure_required == false) $csv_string .= "\n";
	return $csv_string;
  }

  function fputcsvsafe(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"', $escape_fields = true) {
	$str = '';
    $escape_char = '\\';
    while (list(, $value) = each($fields)) {
	  $value = preg_replace('/\s+/', ' ', $value);
      if (strpos($value, $delimiter) !== false || strpos($value, $enclosure) !== false || strpos($value, "\n") !== false || strpos($value, "\r") !== false || strpos($value, "\t") !== false || strpos($value, ' ') !== false || $escape_fields == true) {
		$str2 = $enclosure;
		$escaped = 0;
		$len = strlen($value);
		for ($i=0; $i<$len; $i++) {
		  if ($value[$i] == $escape_char) {
			$escaped = 1;
		  } elseif (!$escaped && $value[$i] == $enclosure) {
			$str2 .= $enclosure;
		  } else {
			$escaped = 0;
		  }
		  $str2 .= $value[$i];
		}
		$str2 .= $enclosure;
		$str .= $str2 . $delimiter;
	  } else {
		$str .= $value . $delimiter;
	  }
	}
	$str = substr($str, 0, -1);
	$str .= "\n";
	return fwrite($handle, $str);
  }

  function print_var($var_name, $var_value) {
	$ret_str = '';
	if (is_array($var_value)) {
	  while (list($key, $val) = each($var_value)) {
		if (is_array($val)) {
		  $ret_str .= print_var($var_name . '[' . $key . ']', $val);
		} else {
		  $ret_str .= $var_name . '[' . $key . ']=' . urlencode(stripslashes($val)) . '&';
		}
	  }
	} else {
	  $ret_str .= $var_name . '=' . urlencode(stripslashes($var_value)) . '&';
	}
	return $ret_str;
  }

////
// Return all HTTP GET variables, except those passed as a parameter
  function tep_get_all_get_params($exclude_array = '') {
	global $HTTP_GET_VARS;

	if (!is_array($exclude_array)) {
	  if (!empty($exclude_array)) $exclude_array = array($exclude_array);
	  else $exclude_array = array();
	}

	$get_url = '';
	if (is_array($HTTP_GET_VARS) && (sizeof($HTTP_GET_VARS) > 0)) {
	  reset($HTTP_GET_VARS);
	  while (list($key, $value) = each($HTTP_GET_VARS)) {
		$value = urldecode($value);
		if ( (strlen($value) > 0) && ($key != tep_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {
		  $get_url .= print_var($key, $value);
		}
	  }
	}

	return $get_url;
  }

////
// Returns an array with countries
// TABLES: countries
  function tep_get_countries($countries_id = '', $with_iso_codes = false, $countries_iso_code_2 = '') {
	global $languages_id;
    $countries_array = array();
	$all_countries = tep_get_shops_countries();
	reset($all_countries);
	while (list(, $shops_country) = each($all_countries)) {
	  $add_country = true;
	  if ( (tep_not_null($countries_id) && $shops_country['country_id']!=$countries_id) || (tep_not_null($countries_iso_code_2) && $shops_country['country_code']!=$countries_iso_code_2) ) $add_country = false;
	  if ($add_country) {
		if (tep_not_null($countries_id) || tep_not_null($countries_iso_code_2)) {
		  $countries_array = array('countries_id' => $shops_country['country_id'],
								   'countries_name' => $shops_country['country_name'],
								   'countries_iso_code_2' => $shops_country['country_code'],
								   'countries_iso_code_3' => $shops_country['country_code_3']);
		} else {
		  $countries_array[] = array('countries_id' => $shops_country['country_id'],
									 'countries_name' => $shops_country['country_name'],
									 'countries_iso_code_2' => $shops_country['country_code'],
									 'countries_iso_code_3' => $shops_country['country_code_3']);
		}
	  }
    }

    return $countries_array;
  }

////
// Alias function to tep_get_countries, which also returns the countries iso codes
  function tep_get_countries_with_iso_codes($countries_id) {
    return tep_get_countries($countries_id, true);
  }

////
// Generate a path to categories
  function tep_get_path($current_category_id = '') {
	$parents_array = array();
	if (tep_not_null($current_category_id)) $parents_array[] = $current_category_id;
	tep_get_parents($parents_array, $current_category_id);
	$parents_array = array_reverse($parents_array);

    return 'cPath=' . implode('_', $parents_array);
  }

////
// Returns the clients browser
  function tep_browser_detect($component) {
    global $HTTP_USER_AGENT;

    return stristr($HTTP_USER_AGENT, $component);
  }

////
// get category name
  function tep_get_category_name($category_id, $language_id = '') {
	global $languages_id;

	if (empty($language_id)) $language_id = $languages_id;

	$category_name_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$category_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$category_name = tep_db_fetch_array($category_name_query);

    return $category_name['categories_name'];
  }

////
// Alias function to tep_get_countries()
  function tep_get_country_name($country_id) {
    $country_array = tep_get_countries($country_id);

    return $country_array['countries_name'];
  }

////
// Returns the zone (State/Province) name
// TABLES: zones
  function tep_get_zone_name($country_id, $zone_id, $default_zone) {
	$return_zone = $default_zone;
	$all_countries = tep_get_shops_countries();
	reset($all_countries);
	while (list(, $country_info) = each($all_countries)) {
	  if ($country_info['country_id']==$country_id) {
		tep_db_select_db($country_info['shops_db']);
		break;
	  }
	}
    $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
    if (tep_db_num_rows($zone_query)) {
      $zone = tep_db_fetch_array($zone_query);
      $return_zone = $zone['zone_name'];
    }
	tep_db_select_db(DB_DATABASE);

    return $return_zone;
  }

////
// Returns the zone (State/Province) code
// TABLES: zones
  function tep_get_zone_code($country_id, $zone_id, $default_zone) {
	$return_zone = $default_zone;
	$all_countries = tep_get_shops_countries();
	reset($all_countries);
	while (list(, $country_info) = each($all_countries)) {
	  if ($country_info['country_id']==$country_id) {
		tep_db_select_db($country_info['shops_db']);
		break;
	  }
	}
    $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
    if (tep_db_num_rows($zone_query)) {
      $zone = tep_db_fetch_array($zone_query);
      $return_zone = $zone['zone_code'];
    }
	tep_db_select_db(DB_DATABASE);

    return $return_zone;
  }

////
// Wrapper function for round()
  function tep_round($number, $precision) {
    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {
      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

      if (substr($number, -1) >= 5) {
        if ($precision > 1) {
          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
        } elseif ($precision == 1) {
          $number = substr($number, 0, -1) + 0.1;
        } else {
          $number = substr($number, 0, -1) + 1;
        }
      } else {
        $number = substr($number, 0, -1);
      }
    }

    return $number;
  }

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
  function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $customer_zone_id, $customer_country_id;

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!tep_session_is_registered('customer_id')) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $customer_country_id;
        $zone_id = $customer_zone_id;
      }
    }

    $tax_query = tep_db_query("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' group by tr.tax_priority");
    if (tep_db_num_rows($tax_query)) {
      $tax_multiplier = 1.0;
      while ($tax = tep_db_fetch_array($tax_query)) {
        $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
      }
      return ($tax_multiplier - 1.0) * 100;
    } else {
      return 0;
    }
  }

////
// Return the tax description for a zone / class
// TABLES: tax_rates;
  function tep_get_tax_description($class_id, $country_id, $zone_id) {
    $tax_query = tep_db_query("select tax_description from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' order by tr.tax_priority");
    if (tep_db_num_rows($tax_query)) {
      $tax_description = '';
      while ($tax = tep_db_fetch_array($tax_query)) {
        $tax_description .= $tax['tax_description'] . ' + ';
      }
      $tax_description = substr($tax_description, 0, -3);

      return $tax_description;
    } else {
      return TEXT_UNKNOWN_TAX_RATE;
    }
  }

////
// Add tax to a products price
  function tep_add_tax($price, $tax) {
    global $currencies;

    if ( (DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0) ) {
      return $price + tep_calculate_tax($price, $tax);
    } else {
      return $price;
    }
  }

// Calculates Tax rounding the result
  function tep_calculate_tax($price, $tax) {
    global $currencies, $currency;

    return tep_round($price * $tax / 100, $currencies->currencies[$currency]['decimal_places']);
  }

////
// Return true if the category has subcategories
// TABLES: categories
  function tep_has_category_subcategories($category_id) {
    $child_category_query = tep_db_query("select count(*) as count from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$category_id . "'");
    $child_category = tep_db_fetch_array($child_category_query);

    if ($child_category['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }

////
// Returns the address_format_id for the given country
// TABLES: countries;
  function tep_get_address_format_id($country_id) {
    $address_format_query = tep_db_query("select address_format_id as format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "' limit 1");
    if (tep_db_num_rows($address_format_query)) {
      $address_format = tep_db_fetch_array($address_format_query);
      return $address_format['format_id'];
    } else {
      return '1';
    }
  }

////
// Return a formatted address
// TABLES: address_format
  function tep_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . (int)$address_format_id . "'");
    $address_format = tep_db_fetch_array($address_format_query);

    $company = tep_output_string_protected($address['company']);
    if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
      $firstname = tep_output_string_protected($address['firstname']);
      $lastname = tep_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && tep_not_null($address['name'])) {
      $firstname = tep_output_string_protected($address['name']);
      $lastname = '';
    } else {
      $firstname = '';
      $lastname = '';
    }
    $street = tep_html_entity_decode(tep_output_string_protected($address['street_address']));
    $suburb = tep_html_entity_decode(tep_output_string_protected($address['suburb']));
    $city = tep_html_entity_decode(tep_output_string_protected($address['city']));
    $state = tep_html_entity_decode(tep_output_string_protected($address['state']));
    $telephone = tep_html_entity_decode($address['telephone']);
    if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
      $country = tep_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
//		$state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);
		$state = tep_get_zone_name($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && tep_not_null($address['country'])) {
      $country = tep_output_string_protected($address['country']);
    } else {
      $country = '';
    }
	if ($state==$city) $city = '';
    if (tep_not_null($address['postcode'])) {
	  $postcode = tep_output_string_protected($address['postcode']) . ', ';
	  $zip = $postcode;
	}

    if ($html) {
// HTML Mode
      $HR = '<hr size="1" />';
      $hr = '<hr size="1" />';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
// Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $statecomma = '';
    $streets = $street;
//    if ($suburb != '') $state .= $street . $cr . $suburb;
    if ($country == '') $country = tep_output_string_protected($address['country']);
    if ($state != '' && $state != $city) $statecomma = $state . ', ';

    $fmt = $address_format['format'];
    eval("\$address = \"$fmt\";");

    if ( (ACCOUNT_COMPANY == 'true') && (tep_not_null($company)) ) {
      $address = $company . $cr . $address;
    }
	while (substr(trim($address), 0, 1)==',') $address = substr(trim($address), 1);
	while (preg_match('/,\s?,/', $address)) $address = preg_replace('/,\s?,/', ',', $address);
	while (strpos($address, ' ,')!==false) $address = str_replace(' ,', ',', $address);
	if (substr(trim($address), -1)==',') $address = substr(trim($address), 0, -1);

    return trim($address);
  }

////
// Return a formatted address
// TABLES: customers, address_book
  function tep_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    $address_query = tep_db_query("select entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone as telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$address_id . "'");
    $address = tep_db_fetch_array($address_query);

    $format_id = tep_get_address_format_id($address['country_id']);

    return tep_address_format($format_id, $address, $html, $boln, $eoln);
  }

  function tep_row_number_format($number) {
    if ( ($number < 10) && (substr($number, 0, 1) != '0') ) $number = '0' . $number;

    return $number;
  }

  function tep_get_categories($categories_array = '', $parent_id = 0, $level = 0, $products_types_id = 0) {
    global $languages_id, $active_products_types_array;

    if (!is_array($categories_array)) $categories_array = array();

	if ($products_types_id==0 && $parent_id==0 && func_num_args()<4) {
	  $products_types_query = tep_db_query("select products_types_id, products_types_name, products_types_default_status from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, products_types_name");
	  while ($products_types = tep_db_fetch_array($products_types_query)) {
		if ($products_types['products_types_default_status']==1) {
		  $category_check = true;
		} else {
		  $categories_check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$products_types['products_types_id'] . "' and products_status = '1' limit 1");
		  $categories_check_row = tep_db_fetch_array($categories_check_query);
		  $category_check = ($categories_check_row['products_id'] > 0);
		}
		if ($category_check) {
		  if (sizeof($active_products_types_array) > 1) {
			$categories_array[] = array('id' => $products_types['products_types_id'], 'text' => $products_types['products_types_name'], 'active' => false);
		  } else {
			$level = -1;
		  }
		  $categories_array = tep_get_categories($categories_array, $parent_id, ($level+1), $products_types['products_types_id']);
		}
	  }
	} elseif ($level <= 2) {
	  $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id = '" . (int)$parent_id . "'" . (strlen($products_types_id)>0 ? " and c.products_types_id = '" . (int)$products_types_id . "'" : "") . " and c.categories_id = cd.categories_id and c.categories_status = '1' and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, cd.categories_name");
	  if (tep_db_num_rows($categories_query) > 0) {
		while ($categories = tep_db_fetch_array($categories_query)) {
		  $categories_array[] = array('id' => $categories['categories_id'],
									  'text' => str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $categories['categories_name']);

		  if ($categories['categories_id'] != $parent_id) {
			$categories_array = tep_get_categories($categories_array, $categories['categories_id'], ($level+1), $products_types_id);
		  }
		}
	  }
	}

    return $categories_array;
  }

  function tep_get_category_level($parent_id = 0, $level = 0, $products_types_id = '1', $opened_categories = array(), $in_form = true) {
	global $languages_id;

	$parent_categories = array();
	if (sizeof($opened_categories) > 0) {
	  reset($opened_categories);
	  while (list(, $opened_category_id) = each($opened_categories)) {
		$parent_categories[] = $opened_category_id;
		tep_get_parents($parent_categories, $opened_category_id);
	  }
	}

	$categories_string = '';
	$categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '" . (int)$products_types_id . "' and c.parent_id = '" . (int)$parent_id . "'" . (!$in_form ? " and c.categories_id in ('" . implode("', '", $parent_categories) . "')" : "") . " and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and c.categories_status = '1' order by c.sort_order, cd.categories_name");
	if (tep_db_num_rows($categories_query) > 0) {
	  while ($categories = tep_db_fetch_array($categories_query)) {
		for ($i=0; $i<$level; $i++) {
		  $categories_string .= tep_draw_separator('pixel_trans.gif', 20, 1);
		}

		$show_sublevel = false;
		if (sizeof($opened_categories) > 0) {
		  $subcategories = array();
		  tep_get_subcategories($subcategories, $categories['categories_id']);
		  reset($subcategories);
		  while (list(, $subcategory_id) = each($subcategories)) {
			if (in_array($subcategory_id, $opened_categories)) {
			  $show_sublevel = true;
			  break;
			}
		  }
		}

		if (!$in_form) $categories_string .= '';
		elseif (tep_has_category_subcategories($categories['categories_id'])) $categories_string .= '<a href="#" id="wlh' . $categories['categories_id'] . '" onclick="loadLevel(\'' . tep_href_link(FILENAME_LOADER, 'action=load_category_level&parent=' . $categories['categories_id'] . '&level=' . ($level+1), 'SSL') . '\', \'' . $categories['categories_id'] . '\', \'' . ($level+1) . '\'); return false;" style="text-decoration: none; display: inline-block; width: 15px; text-align: center; font-size: 14px; height: 12px; vertical-align: top; padding-top: 2px;">&nbsp;' . ($show_sublevel ? '&ndash;' : '+') . '&nbsp;</a>';
		else $categories_string .= tep_draw_separator('pixel_trans.gif', 15, 1);
		if ($in_form) {
		  $categories_string .= tep_draw_checkbox_field('categories_' . $parent_id . '_' . $categories['categories_id'], $categories['categories_id'], (in_array($categories['categories_id'], $opened_categories) ? true : false), 'id="wlc' . $categories['categories_id'] . '" onclick="checkChilds(\'' . $categories['categories_id'] . '\'); if (this.checked==false && document.getElementById(\'wlc' . $parent_id . '\')) document.getElementById(\'wlc' . $parent_id . '\').checked = false;"');
		  $categories_string .= '<label for="wlc' . $categories['categories_id'] . '">' . $categories['categories_name'] . '</label><br />' . "\n";
		} else {
		  if (in_array($categories['categories_id'], $opened_categories)) {
			$categories_string .= '<strong>' . $categories['categories_name'] . '</strong><br />' . "\n";
		  } else {
			$categories_string .= $categories['categories_name'] . '<br />' . "\n";
		  }
		}
		if ($show_sublevel) {
		  $categories_string .= '		<div id="wls' . $categories['categories_id'] . '" style="padding: 0; margin: 0; display: block;">' . tep_get_category_level($categories['categories_id'], ($level+1), $products_types_id, $opened_categories, $in_form) . '</div>' . "\n";
		} else {
		  $categories_string .= '		<div id="wls' . $categories['categories_id'] . '" style="padding: 0; margin: 0; display: none;"></div>' . "\n";
		}
	  }
	}

	return $categories_string;
  }

  function tep_show_category($parent_id = 0, $level = 0, $only_categories = '', $products_types_id = '1', $include_all_products_link = false) {
	global $languages_id, $cPath_array, $show_product_type, $HTTP_GET_VARS;

	if (tep_not_null($HTTP_GET_VARS['products_id']) && basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO) {
	  $category_info_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' order by categories_id desc limit 1");
	  $category_info = tep_db_fetch_array($category_info_query);
	  $categories_array = array($category_info['categories_id']);
	  tep_get_parents($categories_array, $category_info['categories_id']);
	} else {
	  $categories_array = $cPath_array;
	}
	if (!is_array($categories_array)) $categories_array = array();

	$categories_string = '';

	$categories_query = tep_db_query("select c.categories_id, c.products_types_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = '1' and categories_listing_status = '1' and c.parent_id = '" . (int)$parent_id . "'" . (strlen($products_types_id)>0 ? " and c.products_types_id = '" . (int)$products_types_id . "'" : "") . " and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'" . (is_array($only_categories) ? " and c.categories_id in ('" . implode("', '", array_map('tep_string_to_int', $only_categories)) . "')" : "") . " order by c.sort_order, cd.categories_name");
	$categories_count = tep_db_num_rows($categories_query);
	if ($categories_count > 0) {
	  $i = 0;
	  $categories_string = '';
	  while ($categories = tep_db_fetch_array($categories_query)) {
		$active = false;
		if (in_array($categories['categories_id'], $categories_array)) $active = true;
		$link = tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $categories['products_types_id'] . '&cPath=' . $categories['categories_id']);
		$next_level = '';
		if (in_array($categories['categories_id'], $categories_array)) {
		  $next_level = tep_show_category($categories['categories_id'], $level+1, $only_categories, $products_types_id);
		}
		if (tep_not_null($categories['categories_name'])) $categories_string .= '		<div class="li' . (($i==0 && $level==0) ? '_first' : '') . '"><div class="level_' . $level . '"><a href="' . $link . '"' . ($active ? ' class="active"' : '') . (!empty($next_level11) ? ' onclick="if (document.getElementById(\'loader\')) { getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'categories_id=' . $categories['categories_id']) . '\', \'loader\'); return false; }"' : '') . '>' . $categories['categories_name'] . '</a></div></div>' . "\n";
		$categories_string .= $next_level;
		$i ++;
	  }
	}

	return $categories_string;
  }

  function tep_get_manufacturers($manufacturers_array = '') {
	global $languages_id;

    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
    }

    return $manufacturers_array;
  }

  function tep_get_authors($authors_array = '') {
	global $languages_id;

    if (!is_array($authors_array)) $authors_array = array();

    $authors_query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " where languages_id = '" . (int)$languages_id . "' order by authors_name");
    while ($authors = tep_db_fetch_array($authors_query)) {
      $authors_array[] = array('id' => $authors['authors_id'], 'text' => $authors['authors_name']);
    }

    return $authors_array;
  }

  function tep_get_series($series_array = '') {
	global $languages_id;

    if (!is_array($series_array)) $series_array = array();

    $series_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where languages_id = '" . (int)$languages_id . "' order by series_name");
    while ($series = tep_db_fetch_array($series_query)) {
      $series_array[] = array('id' => $series['series_id'], 'text' => $series['series_name']);
    }

    return $series_array;
  }

  function tep_get_products_types($products_types_array = '') {
	global $languages_id, $active_products_types_array;

    if (!is_array($products_types_array)) $products_types_array = array();

    $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
    while ($products_types = tep_db_fetch_array($products_types_query)) {
      $products_types_array[] = array('id' => $products_types['products_types_id'], 'text' => $products_types['products_types_name']);
    }

    return $products_types_array;
  }

////
// Return all subcategory IDs
// TABLES: categories
  function tep_get_subcategories(&$subcategories_array, $parent_id = 0) {
    $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$parent_id . "' and categories_status = '1' group by categories_id");
    while ($subcategories = tep_db_fetch_array($subcategories_query)) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];
      if ($subcategories['categories_id'] != $parent_id) {
        tep_get_subcategories($subcategories_array, $subcategories['categories_id']);
      }
    }
  }

////
// Return all subcity IDs
// TABLES: cities
  function tep_get_subcities(&$cities_array, $parent_id = 0) {
	$cities_query = tep_db_query("select city_id, old_id from " . TABLE_CITIES . " where parent_id = '" . (int)$parent_id . "'");
	while ($cities = tep_db_fetch_array($cities_query)) {
	  $cities_array[sizeof($cities_array)] = $cities['city_id'];
	  if (tep_not_null($cities['old_id']) && $cities['old_id']!=$cities['city_id']) $cities_array[sizeof($cities_array)] = $cities['old_id'];
	  if ($cities['city_id'] != $parent_id) {
		tep_get_subcities($cities_array, $cities['city_id']);
	  }
	}
  }

////
// Recursively go through the information sections and retreive all parent sectionss IDs
// TABLES: sections
  function tep_get_parent_sections(&$sections, $sections_id) {
	$parent_section_query = tep_db_query("select parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sections_id . "' limit 1");
	$parent_section = tep_db_fetch_array($parent_section_query);
	if ($parent_section['parent_id'] == 0) return true;
	$sections[sizeof($sections)] = $parent_section['parent_id'];
	if ($parent_section['parent_id'] != $sections_id) {
	  tep_get_parent_sections($sections, $parent_section['parent_id']);
	}
  }

////
// Return all subsection IDs
// TABLES: information_sections
  function tep_get_subsections(&$subsections_array, $parent_id = 0) {
    $subsections_query = tep_db_query("select sections_id from " . TABLE_SECTIONS . " where parent_id = '" . (int)$parent_id . "' group by sections_id order by sort_order");
    while ($subsections = tep_db_fetch_array($subsections_query)) {
      $subsections_array[sizeof($subsections_array)] = $subsections['sections_id'];
      if ($subsections['sections_id'] != $parent_id) {
        tep_get_subsections($subsections_array, $subsections['sections_id']);
      }
    }
  }

  function tep_show_products_carousel($products_array, $carousel_id, $exclude_array = array(), $carousel_type = 'js', $show_category_name = false) {
	global $languages_id, $currencies, $cart, $active_products_types_array;

	$customer_discount = $cart->get_customer_discount();

	if (!is_array($products_array)) $products_array = explode(',', $products_array);
	$products_array = array_map('tep_string_to_int', $products_array);

	ob_start();
	if (tep_not_null($exclude_array)) {
	  if (!is_array($exclude_array)) $exclude_array = explode(',', $exclude_array);
	  $exclude_array = array_map('tep_string_to_int', $exclude_array);
	}

	$ul_string = '';
	$ul_string_1 = '';

	$products_query = tep_db_query("select products_id, authors_id, products_price, products_purchase_cost, products_image, products_tax_class_id, products_listing_status, products_date_available, products_types_id, products_periodicity from " . TABLE_PRODUCTS . " where products_id in ('" . implode("', '", $products_array) . "') and products_status = '1' and products_types_id in ('" . implode("', '", $active_products_types_array) . "')" . (tep_not_null($exclude_array) ? " and products_id not in ('" . implode("', '", $exclude_array) . "')" : ""));
	$products_count = tep_db_num_rows($products_query);
	if ($products_count > 0) {
	  if ($carousel_type!='table') {
?>
  <script type="text/javascript" language="javascript"><!--
<?php
	  }
	  $show_products_info = array();
	  $counter = 0;
	  $i = 0;
	  while ($product_info = tep_db_fetch_array($products_query)) {
		$special_info_query = tep_db_query("select if((status and specials_new_products_price > 0), specials_new_products_price, null) as specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and specials_new_products_price > 0 order by specials_date_added desc limit 1");
		$special_info = tep_db_fetch_array($special_info_query);
		if (!is_array($special_info)) $special_info = array();
		$product_description_info_query = tep_db_query("select products_name, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$product_description_info = tep_db_fetch_array($product_description_info_query);
		if (!is_array($product_description_info)) $product_description_info = array();
		$author_info_query = tep_db_query("select authors_name from authors where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$author_info = tep_db_fetch_array($author_info_query);
		if (!is_array($author_info)) $author_info = array();
		$category_info = array('category' => '');
		if ($show_category_name) {
		 $category_info_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_info['products_id'] . "' order by categories_id limit 1");
		  $category_info_row = tep_db_fetch_array($category_info_query);
		  $category_info['category'] = '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $category_info_row['categories_id']) . '">' . tep_get_category_name($category_info_row['categories_id'], DEFAULT_LANGUAGE_ID) . '</a>';
		}
		$product_info = array_merge($product_info, $special_info, $product_description_info, $author_info, $category_info);
		reset($product_info);
		while (list($k, $v) = each($product_info)) {
		  while (strpos($v, "\'")!==false) $v = str_replace("\'", "'", $v);
		  while (strpos($v, '\"')!==false) $v = str_replace('\"', '"', $v);
		  $v = str_replace("'", '&#039;', $v);
		  $show_products_info[$counter][$k] = $v;
		}

		$show_products_info[$counter]['products_url'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id']);

		$product_image_link = '';
		if (tep_not_null($product_info['products_image'])) {
//		  $product_image_link = DIR_WS_IMAGES . 'thumbs/' . $product_info['products_image'];
		  $product_image_link = 'http://149.126.96.163/thumbs/' . $product_info['products_image'];
		} else {
		  $product_image_link = DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif';
		}
		$show_products_info[$counter]['products_image'] = tep_image($product_image_link, $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);

		if ($product_info['products_periodicity'] > '0') {
		  $product_price = $currencies->display_price($product_info['products_price']*$product_info['products_periodicity'], tep_get_tax_rate($product_info['products_tax_class_id'])) . ' / ' . TEXT_SUBSCRIBE_TO_YEAR;
		} else {
		  list($available_year, $available_month, $available_day) = explode('-', preg_replace('/^([^\s]+)\s/', '$1', $product_info['products_date_available']));
		  if ($product_info['products_listing_status']=='0') {
			$available_soon_check_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and specials_types_id = '4'");
			$available_soon_check = tep_db_fetch_array($available_soon_check_query);
			if ($product_info['products_date_available']>date('Y-m-d')) {
			  $product_price = sprintf(TEXT_PRODUCT_NOT_AVAILABLE, $monthes_array[(int)$available_month] . ' ' . $available_year);
			} elseif ($available_soon_check['total'] > 0) {
			  $product_price = TEXT_PRODUCT_NOT_AVAILABLE_2;
			} else {
			  $product_price = TEXT_PRODUCT_NOT_AVAILABLE_SHORT;
			  $product_price = '';
			}
		  } else {
			if ($product_info['specials_new_products_price'] > 0 && $product_info['specials_new_products_price'] < $product_info['products_price']) $product_price = '<div class="row_product_price_old" style="display: inline; font-weight: normal; padding-right: 5px;">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '';
			elseif ($customer_discount['type']=='purchase' && $product_info['products_purchase_cost'] > 0) $product_price = $currencies->display_price($product_info['products_purchase_cost'] * (1 + $customer_discount['value']/100), tep_get_tax_rate($product_info['products_tax_class_id']));
			else $product_price = $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

//			$product_price = $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
		  }
		}
		$show_products_info[$counter]['products_price'] = $product_price;

		$products_short_name = $product_info['products_name'];
		if (mb_strlen($products_short_name, 'CP1251') > 40) {
		  $products_short_name = mb_substr($products_short_name, 0, 45, 'CP1251');
		  $products_short_name_parts = explode(' ', $products_short_name);
		  unset($products_short_name_parts[sizeof($products_short_name_parts)-1]);
		  $products_short_name = trim(implode(' ', $products_short_name_parts));
		  $last_letter = mb_substr($products_short_name, -1, mb_strlen($products_short_name, 'CP1251'), 'CP1251');
		  if (!in_array($last_letter, array(':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) {
			$products_short_name .= ' '; $last_letter = ' ';
		  }
		  if (in_array($last_letter, array(' ', ':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) $products_short_name = mb_substr($products_short_name, 0, -1, 'CP1251') . '...';
		}
		$show_products_info[$counter]['products_short_name'] = $products_short_name;

		$show_products_info[$counter]['products_author'] = ($show_products_info[$counter]['products_types_id']=='1' ? '<a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $show_products_info[$counter]['authors_id']) . '">' . $show_products_info[$counter]['authors_name'] . '</a>' : '' . $show_products_info[$counter]['manufacturers_name']);

		$counter ++;
	  }

	  $carousel_string = '';
	  if ($carousel_type=='table') {
		reset($show_products_info);
		while (list($counter, $product_info) = each($show_products_info)) {
		  if ($counter > 0) {
			$ul_string .= '    <td rowspan="2">' . tep_draw_separator('pixel_trans.gif', 11, 1) . '</td>' . "\n";
		  }
		  $ul_string .= 
		  '    <td valign="top" align="center" width="132" class="jcarousel-list">' . "\n" .
		  '<div class="row_product_img"><a href="' . $product_info['products_url'] . '">' .  $product_info['products_image'] . '</a></div>' . "\n" .
		  (tep_not_null($product_info['category']) ? '		<div class="row_product_name">' . $product_info['category'] . ':</div>' . "\n" : '') .
		  '		<div class="row_product_name"><a href="' . $product_info['products_url'] . '" title="' . $product_info['products_name'] . '">' . $product_info['products_short_name'] . '</a></div></td>' . "\n";
		  $ul_string_1 .= 
		  '    <td valign="bottom" align="center" width="132" class="jcarousel-list"><div class="row_product_price">' . $product_info['products_price'] . '</div></td>' . "\n";
		  if ($counter >= 3) break;
		}
	  } elseif ($carousel_type=='html') {
		reset($show_products_info);
		while (list($counter, $product_info) = each($show_products_info)) {
		  $ul_string .= 
		  '	  <li><div class="row_product_carousel">' . "\n" .
		  '		<div class="row_product_img"><a href="' . $product_info['products_url'] . '">' .  $product_info['products_image'] . '</a></div>' . "\n" .
		  '		<div class="row_product_top">' . "\n" .
		  (tep_not_null($product_info['category']) ? '		<div class="row_product_name">' . $product_info['category'] . ':</div>' . "\n" : '') .
		  '		  <div class="row_product_name"><a href="' . $product_info['products_url'] . '" title="' . $product_info['products_name'] . '">' . $product_info['products_short_name'] . '</a></div>' . "\n" .
		  '		  <div class="row_product_author">' . $product_info['products_author'] . '</div>' . "\n" .
		  '		</div>' . "\n" .
		  '		<div class="row_product_price">' . $product_info['products_price'] . '</div>' . "\n" .
		  '	  </div></li>' . "\n";
		}
	  } else {
?>
	var <?php echo $carousel_id; ?>_itemList = [
<?php
		reset($show_products_info);
		while (list($counter, $product_info) = each($show_products_info)) {
?>
	  {url: '<?php echo $product_info['products_url']; ?>', category: '<?php echo $product_info['category']; ?>', image: '<?php echo $product_info['products_image']; ?>', title: '<?php echo $product_info['products_name']; ?>', short_title: '<?php echo $product_info['products_short_name']; ?>', author: '<?php echo $product_info['products_author']; ?>', price: '<?php echo $product_info['products_price']; ?>'}<?php echo $counter<($products_count-1) ? ',' : ''; ?>

<?php
		}
?>
	];

	function <?php echo $carousel_id; ?>_itemLoadCallback(carousel, state) {
	  for (var i = carousel.first; i <= carousel.last; i++) {
		if (carousel.has(i)) {
		  continue;
		}

		if (i > <?php echo $carousel_id; ?>_itemList.length) {
		  break;
		}
//		alert(<?php echo $carousel_id; ?>_itemList[i-1].url);

		carousel.add(i, <?php echo $carousel_id; ?>_getItemHTML(<?php echo $carousel_id; ?>_itemList[i-1]));
	  }
	};

	function <?php echo $carousel_id; ?>_getItemHTML(item) {
//	  return '<img src="' + item.url + '" width="75" height="75" alt="' + item.url + '" />';
	  return '' +
		'	  <li><div class="row_product_carousel">' + "\n" +
		'		<div class="row_product_img"><a href="' + item.url + '">' + item.image + '</a></div>' + "\n" +
		'		<div class="row_product_top">' + "\n" +
		(item.category ? '		<div class="row_product_name">' + item.category + ':</div>' + "\n" : '') +
		'		  <div class="row_product_name"><a href="' + item.url + '" title="' + item.title + '">' + item.short_title + '</a></div>' + "\n" +
		'		  <div class="row_product_author">' + item.author + '</div>' + "\n" +
		'		</div>' + "\n" +
		'		<div class="row_product_price">' + item.price + '</div>' + "\n" +
		'	  </div></li>' + "\n";
	};
<?php
	  }
	  if ($carousel_type!='table') {
?>

	jQuery(document).ready(function() {
	  jQuery('#<?php echo $carousel_id; ?>').jcarousel(<?php if ($carousel_type!='html') { ?>{
		size: <?php echo $carousel_id; ?>_itemList.length,
		itemLoadCallback: {onBeforeAnimation: <?php echo $carousel_id; ?>_itemLoadCallback}
	  }<?php } ?>);
	});
  //--></script>
  <div id="<?php echo 'c_' . $carousel_id; ?>">
	<ul id="<?php echo $carousel_id; ?>" class="jcarousel-skin-tango"><?php echo $ul_string; ?></ul>
  </div><br />
<?php
		if ($products_count <= 4) {
?>
  <style type="text/css">
	<?php echo '#c_' . $carousel_id; ?> .jcarousel-next-horizontal, <?php echo '#c_' . $carousel_id; ?> .jcarousel-prev-horizontal {
	  background-image: none;
	  }
  </style>
<?php
		}
	  } else {
?>
<table border="0" cellspacing="0" cellpadding="0" style="padding-bottom: 1.4em;" class="jcarousel-list-table">
  <tr>
<?php echo $ul_string; ?>
  </tr>
  <tr>
<?php echo $ul_string_1; ?>
  </tr>
</table>
<?php
	  }
	  $carousel_string = ob_get_clean();
	}
	return $carousel_string;
  }

  function tep_show_images_carousel($images, $carousel_id) {
	ob_start();

	$carousel_string = '';

	if (sizeof($images) > 0) {
?>
  <script type="text/javascript" language="javascript"><!--
	jQuery(document).ready(function() {
	  jQuery('#<?php echo $carousel_id; ?>').jcarousel();
	});
  //--></script>
<?php
	  $swfobject_string = '';
	  $js_to_load = array();
	  reset($images);
	  $video_width = 470;
	  $video_height = 320;
	  while (list($i, $image_info) = each($images)) {
		$image_ext = strtolower(substr($image_info['image_link'], -4));
		$image_small_ext = strtolower(substr($image_info['image_small'], -4));
		if (!in_array($image_small_ext, array('.gif', '.jpg', 'jpeg', '.png'))) $image_info['image_small'] = '';
		if (in_array($image_ext, array('.mp3', '.asf', '.wma', '.flv', '.wmv', '.mp4', '.mpg', '.avi'))) {
		  $var_name = 'so_' . $i;
		  if (in_array($image_ext, array('.wmv', '.mp4', '.mpg', '.avi'))) {
			if (!in_array('wmvplayer.js', $js_to_load)) $js_to_load[] = 'wmvplayer.js';
			if (!in_array('silverlight.js', $js_to_load)) $js_to_load[] = 'silverlight.js';
			$swfobject_string .= '	var src = \'' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_JAVASCRIPT . 'wmvplayer.xaml\';' . "\n";
			$images[$i]['image_onclick'] = $var_name . ' = new jeroenwijering.Player(document.getElementById(\'player_' . $carousel_id . '\'),src,{overstretch:\'true\',volume:\'70\',autostart:\'true\',file:\'' . $image_info['image_link'] . '\',height:\'' . $video_height . '\',width:\'' . $video_width . '\'}); document.getElementById(\'player_' . $carousel_id . '\').style.display = \'block\'; return false;';
			if (empty($image_info['image_small'])) $images[$i]['image_small'] = DIR_WS_ICONS . 'icon_video.gif';
		  } else {
			if (in_array($image_ext, array('.mp3', '.asf', '.wma'))) {
			  $video_height = 24;
			  if (empty($image_info['image_small'])) $images[$i]['image_small'] = DIR_WS_ICONS . 'icon_music.gif';
			} else {
			  if (empty($image_info['image_small'])) $images[$i]['image_small'] = DIR_WS_ICONS . 'icon_video.gif';
			}
			if (!in_array('swfobject.js', $js_to_load)) $js_to_load[] = 'swfobject.js';
			$swfobject_string .= '	var ' . $var_name . ' = new SWFObject(\'' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_JAVASCRIPT . 'player.swf\',\'ply\',\'' . $video_width . '\',\'' . $video_height . '\',\'9\',\'#000000\');' . "\n" .
				'	' . $var_name . '.addParam(\'allowfullscreen\',\'true\');' . "\n" .
				'	' . $var_name . '.addParam(\'allowscriptaccess\',\'always\');' . "\n" .
				'	' . $var_name . '.addParam(\'wmode\',\'opaque\');' . "\n" .
				'	' . $var_name . '.addVariable(\'file\',\'' . HTTP_SERVER . $image_info['image_link'] . '\');' . "\n" .
				'	' . $var_name . '.addVariable(\'autostart\',\'true\');' . "\n" .
				'	' . $var_name . '.addVariable(\'volume\',\'70\');' . "\n" .
//				($image_ext=='.mp3' ? '	' . $var_name . '.addVariable(\'duration\',\'33\');' . "\n" : '') .
				'';
			$images[$i]['image_onclick'] = $var_name . '.write(\'player_' . $carousel_id . '\'); document.getElementById(\'player_' . $carousel_id . '\').style.display = \'block\'; return false;';
		  }
		} elseif (in_array($image_ext, array('.gif', '.jpg', 'jpeg'))) {
		  $images[$i]['image_onclick'] = 'document.getElementById(\'player_' . $carousel_id . '\').innerHTML = \'\'; document.getElementById(\'player_' . $carousel_id . '\').style.display = \'none\'; popupImage(\'' . $image_info['image_link'] . '\', \'' . $image_info['image_title'] . '\'); ' . (tep_not_null($images[$i]['image_onclick']) ? $images[$i]['image_onclick'] : 'return false;');
		  $images[$i]['image_target'] = '_blank';
		} elseif (in_array($image_ext, array('.pdf', '.doc'))) {
		  if (empty($image_info['image_small'])) $images[$i]['image_small'] = DIR_WS_ICONS . 'icon_' . str_replace('.', '', $image_ext) . '.gif';
		  $images[$i]['image_onclick'] = 'document.getElementById(\'player_' . $carousel_id . '\').innerHTML = \'\'; document.getElementById(\'player_' . $carousel_id . '\').style.display = \'none\'; window.open(\'' . $image_info['image_link'] . '\'); ' . (tep_not_null($images[$i]['image_onclick']) ? $images[$i]['image_onclick'] : 'return false;');
		  $images[$i]['image_target'] = '_blank';
		} else {
		  $images[$i]['image_onclick'] = 'document.getElementById(\'player_' . $carousel_id . '\').innerHTML = \'\'; document.getElementById(\'player_' . $carousel_id . '\').style.display = \'none\'; ' . (tep_not_null($images[$i]['image_onclick']) ? $images[$i]['image_onclick'] : 'return false;');
		}
	  }

	  if (tep_not_null($swfobject_string)) {
		reset($js_to_load);
		while (list(, $js_to_load_file) = each($js_to_load)) {
?>
  <script language="javascript" src="<?php echo DIR_WS_CATALOG . DIR_WS_JAVASCRIPT . $js_to_load_file; ?>" type="text/javascript"></script>
<?php
		}
?>
  <script type="text/javascript" language="javascript"><!--
<?php
		echo $swfobject_string;
?>
  //--></script>
<?php
	  }
?>
  <div id="<?php echo 'c_' . $carousel_id; ?>">
	<ul id="<?php echo $carousel_id; ?>" class="jcarousel-skin-tango">
<?php
	  reset($images);
	  while (list(, $image_info) = each($images)) {
		$image_link = '<a href="' . $image_info['image_link'] . '"' . (tep_not_null($image_info['image_onclick']) ? ' onclick="' . $image_info['image_onclick'] . '"' : '') . (tep_not_null($image_info['image_target']) ? ' target="' . $image_info['image_target'] . '"' : '') . '>';
?>
	  <li><div class="row_product_carousel">
		<div class="row_product_img"><?php echo $image_link . tep_image($image_info['image_small'], $image_info['image_title'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></div>
<?php
		if (tep_not_null($image_info['image_link'])) {
?>
		<div class="row_product_name"><?php echo $image_link . $image_info['image_title'] . '</a>'; ?></div>
<?php
		}
?>
	  </div></li>
<?php
	  }
?>
	</ul>
  </div><div id="player_<?php echo $carousel_id; ?>" style="text-align: center; display: none; padding-top: 1.5em;"></div><br />
<?php
	  if (sizeof($images) <= 4) {
?>
  <style type="text/css">
	  <?php echo '#c_' . $carousel_id; ?> .jcarousel-next-horizontal, <?php echo '#c_' . $carousel_id; ?> .jcarousel-prev-horizontal {
	  background-image: none;
	  }
  </style>
<?php
	  }
	  $carousel_string = ob_get_clean();
	}

	return $carousel_string;
  }

// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
  function tep_date_long($raw_date, $include_year = true) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

	$monthes_searched = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
	$monthes_replace_by = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

	if ($include_year==false) $date_format_long = trim(str_replace('%Y', '', DATE_FORMAT_LONG));
	else $date_format_long = DATE_FORMAT_LONG;

    return str_replace($monthes_searched, $monthes_replace_by, strftime($date_format_long, mktime($hour, $minute, $second, $month, $day, $year)));
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '0000-00-00') || empty($raw_date) ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return str_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

////
// Parse search string into indivual objects
  function tep_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(tep_strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
	$pieces = array();
    $temp_pieces = explode(' ', $search_str);
	reset($temp_pieces);
	while (list(, $temp_piece) = each($temp_pieces)) {
	  $temp_piece = trim($temp_pieces);
	  if (tep_not_null($temp_piece)) $pieces[] = $temp_piece;
	}
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words

      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);

        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring
        $tmpstring = trim(str_replace('"', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          $objects[] = trim($pieces[$k]);

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(str_replace('"', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = 'and';
      }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
  }

////
// Check date
  function tep_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
      return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($date_to_check, $separators[$i]);
      if ($pos_separator != false) {
        $date_separator_idx = $i;
        break;
      }
    }

    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($format_string, $separators[$i]);
      if ($pos_separator != false) {
        $format_separator_idx = $i;
        break;
      }
    }

    if ($date_separator_idx != $format_separator_idx) {
      return false;
    }

    if ($date_separator_idx != -1) {
      $format_string_array = explode( $separators[$date_separator_idx], $format_string );
      if (sizeof($format_string_array) != 3) {
        return false;
      }

      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );
      if (sizeof($date_to_check_array) != 3) {
        return false;
      }

      $size = sizeof($format_string_array);
      for ($i=0; $i<$size; $i++) {
        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];
      }
    } else {
      if (strlen($format_string) == 8 || strlen($format_string) == 9) {
        $pos_month = strpos($format_string, 'mmm');
        if ($pos_month != false) {
          $month = substr( $date_to_check, $pos_month, 3 );
          $size = sizeof($month_abbr);
          for ($i=0; $i<$size; $i++) {
            if ($month == $month_abbr[$i]) {
              $month = $i;
              break;
            }
          }
        } else {
          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
        }
      } else {
        return false;
      }

      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
      return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
      return false;
    }

    if ($month > 12 || $month < 1) {
      return false;
    }

    if ($day < 1) {
      return false;
    }

    if (tep_is_leap_year($year)) {
      $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
      return false;
    }

    $date_array = array($year, $month, $day);

    return true;
  }

////
// Check if year is a leap year
  function tep_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
  }

////
// Return table heading with sorting capabilities
  function tep_create_sort_heading($sortby, $colnum, $heading) {
    $sort_prefix = '';
    $sort_suffix = '';

    if ($sortby) {
      $sort_prefix = '<a href="#" onmouseover="this.href=\'' . tep_href_link(PHP_SELF, tep_get_all_get_params(array('page', 'info', 'sort')) . 'sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '\';" title="' . tep_output_string(TEXT_SORT_PRODUCTS . ($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? TEXT_ASCENDINGLY : TEXT_DESCENDINGLY) . TEXT_BY . $heading) . '"' . (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? ' class="sorted_asc"' : ' class="sorted_desc"') : '') . '>' ;
      $sort_suffix = '</a>';
    }

    return $sort_prefix . $heading . $sort_suffix;
  }

////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
  function tep_get_parents(&$categories, $categories_id, $table = '') {
	if ($table == TABLE_SECTIONS) {
	  $field = 'sections_id';
	} elseif ($table == TABLE_CITIES) {
	  $field = 'city_id';
	} else {
	  $table = TABLE_CATEGORIES;
	  $field = 'categories_id';
	}
	$parent_query = tep_db_query("select parent_id from " . tep_db_input($table) . " where " . $field . " = '" . tep_db_input($categories_id) . "' limit 1");
	$parent = tep_db_fetch_array($parent_query);
	if ($parent['parent_id'] == 0) return true;
	$categories[sizeof($categories)] = $parent['parent_id'];
	if ($parent['parent_id'] != $categories_id) {
	  tep_get_parents($categories, $parent['parent_id'], $table);
	}
  }

////
// Construct a category path to the product
// TABLES: products_to_categories
  function tep_get_product_path($products_id) {
    $cPath = '';

    $category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' order by categories_id desc limit 1");
    if (tep_db_num_rows($category_query)) {
      $category = tep_db_fetch_array($category_query);

      $categories = array($category['categories_id']);
      tep_get_parents($categories, $category['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);
    }

    return $cPath;
  }

////
// Construct a section path to the information
// TABLES: information_to_sections
  function tep_get_information_path($information_id) {
    $sPath = '';

    $section_query = tep_db_query("select i2s.sections_id from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = '" . (int)$information_id . "' and i.information_id = i2s.information_id limit 1");
    if (tep_db_num_rows($section_query)) {
      $section = tep_db_fetch_array($section_query);

      $sections = array($section['sections_id']);
      tep_get_parents($sections, $section['sections_id'], TABLE_SECTIONS);

      $sections = array_reverse($sections);

      $sPath = implode('_', $sections);
    }

    return $sPath;
  }

  function tep_show_sections_tree($parent_id = 0, $show_all = false, $level = 0) {
	global $languages_id, $current_information_id, $sPath_array;

	if (!is_array($sPath_array)) $sPath_array = array();
	$sections_string = '';
	$menu = tep_get_sections_menu($parent_id, $show_all);
	reset($menu);
	while (list($i, $menu_item) = each($menu)) {
	  if (tep_not_null($menu_item['link'])) {
		$active = $menu_item['active'];
		$sections_string .= '		<div class="li' . (($level==0 && $i==0) ? '_first' : '') . '"><div class="level_' . $level . '"><a href="' . $menu_item['link'] . '"' . ($active ? ' class="active"' : '') . '>' . $menu_item['title'] . '</a></div></div>' . "\n";
		if ($menu_item['type']=='section' && $menu_item['active']==true) {
		  $sections_string .= tep_show_sections_tree($menu_item['id'], $show_all, $level+1);
		}
	  }
	}

	return $sections_string;
  }

////
//! Send email (text/html) using MIME
// This is the central mail function. The SMTP Server should be configured
// correct in php.ini
// Parameters:
// $to_name           The name of the recipient, e.g. "Jan Wildeboer"
// $to_email_address  The eMail address of the recipient,
//                    e.g. jan.wildeboer@gmx.de
// $email_subject     The subject of the eMail
// $email_text        The text of the eMail, may contain HTML entities
// $from_email_name   The name of the sender, e.g. Shop Administration
// $from_email_adress The eMail address of the sender,
//                    e.g. info@mytepshop.com

//echo function_exists('iconv') ? '1' : '0';
  function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
		$from_email_address = trim(implode('', array_map('trim', explode("\n", $from_email_address))));
		$from_email_name = trim(implode('', array_map('trim', explode("\n", $from_email_name))));
		$email_text = tep_html_entity_decode($email_text);
		$recipients = explode(',', $to_email_address);
		reset($recipients);
		while (list(, $to_email_address) = each($recipients)) {
		  $to_email_address = trim($to_email_address);
		  if (tep_not_null($to_email_address)) {
	
		  	// Instantiate a new PHPMailer object
		  	$mail = new PHPMailer();
		  	$mail->CharSet = 'windows-1251';
		  	
		  	// Build the text version
	      $text = strip_tags($email_text);
	      
	      $mail->SetFrom($from_email_address, $from_email_name);
	      
	      $mail->AddAddress($to_email_address, $to_name);
	      
	      $mail->Subject = $email_subject;
	      
	      $mail->AddReplyTo($from_email_address, $from_email_name);
			
			  if (EMAIL_USE_HTML == 'true' && $from_email_address==STORE_OWNER_EMAIL_ADDRESS) {
			    ob_start();
			    include(DIR_FS_CATALOG . 'images/mail/email_header.php');
			    echo preg_replace('~[\n\r][\n\r]+~', '<br/><br/>', $email_text);
			    include(DIR_FS_CATALOG . 'images/mail/email_footer.php');
			    $email_text_html = ob_get_clean();
			    $email_text_html = str_replace('<title></title>', '<title>' . $email_subject . '</title>', $email_text_html);
			    $mail->MsgHTML($email_text_html);
			  } else {
			    $mail->Body = $text;
			  }
			  
			  $mail->IsSMTP();
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        //$mail->SMTPSecure = "ssl";               // sets the prefix to the servier
        //$mail->Host       = "ex.setbook.ru";       // sets the SMTP server
        $mail->Host       = "mail.setbook.ru";       // sets the SMTP server
        $mail->Port       = 587;                   // set the SMTP port
	//$mail->Port       = 25;                   // set the SMTP port

        //$mail->Username   = "shop@ex.setbook.ru";  // username
	$mail->Username   = "shop";  // username
        $mail->Password   = "Alexander2010";       // password
			  
        if(!$mail->Send()) {
			  	tep_log("Mailer Error: " . $mail->ErrorInfo, $mail, 'mail.errors');
	      }
		  }
		}
  }

////
// Get the number of times a word/character is present in a string
  function tep_word_count($string, $needle) {
    $temp_array = explode($needle, $string);

    return sizeof($temp_array);
  }

  function tep_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = explode(';', $modules);

    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

      if (is_object($GLOBALS[$class])) {
        if ($GLOBALS[$class]->enabled) {
          $count++;
        }
      }
    }

    return $count;
  }

  function tep_count_payment_modules() {
	global $shipping;

	list($shipping_code) = explode('_', $shipping['id']);

	$shipping_to_payment_query = tep_db_query("select payments from " . TABLE_SHIPPING_TO_PAYMENT . " where shipping = '" . tep_db_input($shipping_code) . "' and status = '1'");
	$shipping_to_payment = tep_db_fetch_array($shipping_to_payment_query);
	if (tep_not_null($shipping_to_payment['payments'])){
	  return tep_count_modules($shipping_to_payment['payments']);
	} else{
	  return tep_count_modules(MODULE_PAYMENT_INSTALLED);
	}
  }

  function tep_count_shipping_modules() {
    return tep_count_modules(MODULE_SHIPPING_INSTALLED);
  }

  function tep_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = tep_rand(0,9);
      } else {
        $char = chr(tep_rand(0,255));
      }
      if ($type == 'mixed') {
        if (preg_match('/^[a-z0-9]$/', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (preg_match('/^[0-9]$/', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
  }

  function tep_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();

    $get_string = '';
    if (sizeof($array) > 0) {
      while (list($key, $value) = each($array)) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }


  function tep_strtolower($string) {
	if (function_exists('mb_convert_case')) {
	  return mb_convert_case($string, MB_CASE_LOWER, CHARSET);;
	} else {
	  $find_array = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я');
	  $replace_array = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');

	  return strtolower(str_replace($find_array, $replace_array, $string));
	}
  }

////
// Output the tax percentage with optional padded decimals
  function tep_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

////
// Checks to see if the currency code exists as a currency
// TABLES: currencies
  function tep_currency_exists($code) {
    $code = tep_db_prepare_input($code);

    $currency_code = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "'");
    if (tep_db_num_rows($currency_code)) {
      return $code;
    } else {
      return false;
    }
  }

  function tep_string_to_int($string) {
    return (int)$string;
  }

////
// Parse and secure the cPath parameter values
  function tep_parse_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('tep_string_to_int', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

  function tep_check_blacklist($ip = '') {
	global $customer_id;

	if ($ip=='') $ip = tep_get_ip_address();
	$blacklist_check_query = tep_db_query("select 1 from " . TABLE_BLACKLIST . " where blacklist_ip = '" . tep_db_input($ip) . "'" . ($customer_id>0 ? " or customers_id = '" . (int)$customer_id . "'" : "") . "");
	if (tep_db_num_rows($blacklist_check_query) < 1) return false;

	return true;
  }

////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  function tep_setcookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
    setcookie($name, $value, $expire, $path, (tep_not_null($domain) ? $domain : ''), $secure, $httponly);
  }

  function tep_get_ip_address() {
    if (isset($_SERVER)) {
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
    } else {
      if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
      } elseif (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
      } else {
        $ip = getenv('REMOTE_ADDR');
      }
    }

    return $ip;
  }

  function tep_ip_to_int($ip) {
	$ip_to_int = sprintf('%u', ip2long($ip));

//	$numbers = explode('.', $ip);
//	$ip_to_int = ($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);

	return $ip_to_int;
  }

  function tep_get_ip_info($ip='') {
	if (empty($ip)) $ip = tep_get_ip_address();
	$country_code = '';

	if (tep_not_null($ip)) {
	  $ip_to_int = tep_ip_to_int($ip);
	  $ip_check_query = tep_db_query("select country_code from " . TABLE_IPS . " where " . $ip_to_int . ">= ip_from and " . $ip_to_int . "<= ip_to");
	  $ip_check = tep_db_fetch_array($ip_check_query);
	  if (!is_array($ip_check)) $ip_check = array();
	  $country_code = $ip_check['country_code'];
	}

	return $country_code;
  }

  // функция по выводу данных об $ip
  function tep_get_whois($ip, $server = '') {
	$country_code = '';
	$fp = @fopen('http://api.hostip.info/country.php?ip=' . $ip, 'r');
	if ($fp) {
	  $text = '';
	  while (!feof($fp)) {
		$text .= fgets($fp, 4096) . "\n";
	  }
	  fclose($fp);
	  $country_code = trim($text);
	}
	if ($country_code=='XX') $country_code = '';
	if (empty($country_code) || tep_not_null($server)) {
	  if (empty($server)) $server = 'whois.ripe.net';
	  $fp = @fsockopen($server, 43, $errno, $errstr, 30);
	  if (!$fp) {
		return false;
	  } else {
		fputs($fp, $ip . "\n");
		$text = '';
		while (!feof($fp)) {
		  $text .= fgets($fp, 4096) . "\n";
		}
		fclose($fp);
		if (preg_match('/country\:\s*(\w+)/i', $text, $regs)) $country_code = $regs[1];
		preg_match('/ReferralServer\:\s*whois\:\/\/([^\n\:]+)/i', $text, $out);
		if (!empty($out[1])) {
		  return tep_get_whois($ip, $out[1]);
		}
	  }
	}
	return $country_code;
  }

////
// Returns an array with countries
// TABLES: countries
  function tep_get_shops_countries($shop_id = 0, $listing_status = '') {
	global $languages_id;

	$countries = array();
	$shops_query = tep_db_query("select shops_id, shops_database, shops_url, shops_ssl from " . TABLE_SHOPS . " where shops_database <> ''" . ($shop_id>0 ? " and shops_id = '" . (int)$shop_id . "'" : "") . (strlen($listing_status)>0 ? " and shops_listing_status = '" . (int)$listing_status . "'" : "") . " order by sort_order");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $countries_query = tep_db_query("select countries_id, countries_iso_code_2, countries_iso_code_3, countries_name, countries_ru_name, address_format_id, countries_phone_code from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' order by sort_order, countries_ru_name");
	  while ($countries_row = tep_db_fetch_array($countries_query)) {
		$countries_row['countries_ru_name'] = ucwords(strtolower($countries_row['countries_ru_name']));
		$countries[$countries_row['countries_iso_code_2']] = array('country_id' => $countries_row['countries_id'], 'country_name' => $countries_row['countries_name'], 'country_ru_name' => $countries_row['countries_ru_name'], 'country_code' => $countries_row['countries_iso_code_2'], 'country_code_3' => $countries_row['countries_iso_code_3'], 'address_format_id' => $countries_row['address_format_id'], 'country_code' => $countries_row['countries_iso_code_2'], 'shop_id' => $shops['shops_id'], 'shop_url' => $shops['shops_url'], 'shop_ssl' => $shops['shops_ssl'], 'shop_db' => $shops['shops_database'], 'phone_code' => $countries_row['countries_phone_code'], 'flag' => tep_image(DIR_WS_ICONS . 'flags/' . strtolower($countries_row['countries_iso_code_2']) . '.gif'));
		if ($countries_row['countries_iso_code_2']=='DE') $countries['EU'] = array('country_id' => '0', 'country_name' => 'European Union', 'country_ru_name' => 'Европейский Союз', 'country_code' => 'EU', 'country_code_3' => 'EUR', 'address_format_id' => $countries_row['address_format_id'], 'shop_id' => $shops['shops_id'], 'shop_url' => $shops['shops_url'], 'shop_db' => $shops['shops_database'], 'phone_code' => '', 'flag' => tep_image(DIR_WS_ICONS . 'flags/' . strtolower('EU') . '.gif'));
	  }
	}
	tep_db_select_db(DB_DATABASE);

    return $countries;
  }

  function tep_count_customer_orders($id = '', $check_session = true) {
    global $customer_id;

    if (is_numeric($id) == false) {
      if (tep_session_is_registered('customer_id')) {
        $id = $customer_id;
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {
        return 0;
      }
    }

    $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where customers_id = '" . (int)$id . "'");
    $orders_check = tep_db_fetch_array($orders_check_query);

    return $orders_check['total'];
  }

  function tep_count_customer_address_book_entries($id = '', $check_session = true) {
    global $customer_id;

    if (is_numeric($id) == false) {
      if (tep_session_is_registered('customer_id')) {
        $id = $customer_id;
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {
        return 0;
      }
    }

    $addresses_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$id . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ")");
    $addresses = tep_db_fetch_array($addresses_query);

    return $addresses['total'];
  }

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    if ((PHP_VERSION < "4.0.5") && is_array($from)) {
      return str_replace('(' . implode('|', $from) . ')', $to, $string);
    } else {
      return str_replace($from, $to, $string);
    }
  }

  function tep_get_template_id($content_id = '', $content_type = '') {
	$template_info_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES_TO_CONTENT . " where content_id = '" . (int)$content_id . "' and content_type = '" . tep_db_input($content_type) . "'");
	 if (tep_db_num_rows($template_info_query) == 0) {
	  $template_info_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES . " where default_status = '1' limit 1");
	  if (tep_db_num_rows($template_info_query) == 0) {
		$template_info_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES . " order by templates_id limit 1");
	  }
	}
	$template_info = tep_db_fetch_array($template_info_query);

	return $template_info['templates_id'];
  }

////
// Retreive all sectionss tree
// TABLES: sections, information
  function tep_get_sections_menu($parent_id = 0, $show_all = false) {
	global $languages_id, $sPath_array, $current_information_id;

	if (!is_array($sPath_array)) $sPath_array = array();

	$sections_array = array();
	$sort_array = array();
	$sections_query = tep_db_query("select sections_id, sort_order from " . TABLE_SECTIONS . " where sections_status = '1' and sections_listing_status = '1' and parent_id = '" . (int)$parent_id . "' and language_id = '" . (int)$languages_id . "' order by sort_order, sections_name");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  $sort_array['section:' . $sections['sections_id']] = $sections['sort_order'];
	}
	$information_query = tep_db_query("select i.information_id, i.sort_order, i2s.information_default_status from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_listing_status = '1' and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$parent_id . "' and i.language_id = '" . (int)$languages_id . "' order by i2s.information_default_status desc, i.sort_order, i.information_name");
	while ($information = tep_db_fetch_array($information_query)) {
	  if ($information['information_default_status']==1) $information['sort_order'] = -1;
	  $sort_array['information:' . $information['information_id']] = $information['sort_order'];
	}
	asort($sort_array);
	reset($sort_array);
	while (list($array) = each($sort_array)) {
	  list($type, $id) = explode(':', $array);
	  if ($type=='section') {
		$information_query = tep_db_query("select i.information_id, i2s.information_default_status, i.information_name, i.information_redirect from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$id . "' and i.language_id = '" . (int)$languages_id . "' order by i2s.information_default_status desc, i.sort_order limit 1");
		if (tep_db_num_rows($information_query) > 0) {
		  $information = tep_db_fetch_array($information_query);
		  if ($information['information_default_status']==1) $link = tep_href_link(FILENAME_DEFAULT, 'sPath=' . $id);
		  else $link = '#';
		  $section_query = tep_db_query("select sections_name from " . TABLE_SECTIONS . " where sections_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
		  $section = tep_db_fetch_array($section_query);
		  $active = false;
		  if (in_array($id, $sPath_array)) $active = true;
		  if (tep_not_null($information['information_redirect']) && $information['information_redirect']==REQUEST_URI) $active = true;

		  $sections_array[] = array('type' => 'section',
									'id' => $id,
									'link' => $link,
									'title' => $section['sections_name'],
									'active' => $active);
		}
	  } else {
		$information_query = tep_db_query("select information_name, information_redirect from " . TABLE_INFORMATION . " where information_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
		$information = tep_db_fetch_array($information_query);
		$link = tep_href_link(FILENAME_DEFAULT, 'sPath=' . $parent_id . '&info_id=' . $id);
		$active = false;
		if ($current_information_id==$id) $active = true;
		if (tep_not_null($information['information_redirect']) && basename($information['information_redirect'])==basename(PHP_SELF)) $active = true;
		$sections_array[] = array('type' => 'information',
								  'id' => $id,
								  'link' => $link,
								  'title' => $information['information_name'],
								  'active' => $active);
	  }
	}

	return $sections_array;
  }

  function tep_number_to_string($number, $currency = DEFAULT_CURRENCY) {
	global $currencies;

	$number = round($number, $currencies->get_decimal_places[$currency]);

	$dot_pos = (strrpos($number, '.')!==false ? strrpos($number, '.') : false);
	$comma_pos = (strrpos($number, ',')!==false ? strrpos($number, ',') : false);

	if ($dot_pos && $comma_pos) $substr_to = ($dot_pos>$comma_pos ? $dot_pos : $comma_pos);
	elseif ($dot_pos) $substr_to = $dot_pos;
	elseif ($comma_pos) $substr_to = $comma_pos;
	else $substr_to = 0;

	$parts = array();

	if ($substr_to >  0) {
	  $all_r = substr($number, 0, $substr_to);
	  $all_k = substr($number, $substr_to+1);
	} else {
	  $all_r = $number;
	  $all_k = 0;
	}
	if (strlen($all_k)==1) $all_k .= '0';
	elseif (strlen($all_k)>2) $all_k = substr($all_k, 0, 2);

	$parts['solid']['value'] = $all_r;
	$parts['decimal']['value'] = $all_k;

	$sto = array ('', 'сто' , 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
	$ten = array ('', 'десять' , 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
	$kop = array ('', 'один' , 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять');
	$first = array ('', 'одиннадцать' , 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
	$tys = array ('', 'одна' , 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять');

	$m = ''; // миллионы
	$t = ''; // тысячи
	$r = ''; // рубли
	if (strlen($all_r)>6) {
	  $m = substr($all_r, 0, -6);
	  $t = substr($all_r, -6, 3);
	  $r = substr($all_r, -3, 3);
	} elseif (strlen($all_r)>3) {
	  $t = substr($all_r, 0, -3);
	  $r = substr($all_r, -3, 3);
	} elseif (strlen($all_r)>0) {
	  $r = $all_r;
	}
	if ($m) {
	  if (strlen($m)==3) {
		$m1 = (int)$m[0];
		$m2 = (int)$m[1];
		$m3 = (int)$m[2];
	  } elseif (strlen($m)==2) {
		$m1 = 0;
		$m2 = (int)$m[0];
		$m3 = (int)$m[1];
	  } else {
		$m1 = 0;
		$m2 = 0;
		$m3 = (int)$m;
	  }

	  $mil_text = ' миллионов ';
	  if ($m1) $parts['solid']['text'] .= $sto[$m1] . ' ';
	  if ($m2==1) $parts['solid']['text'] .= $first[$m3];
	  elseif ($m2) $parts['solid']['text'] .= $ten[$m2] . ' ' . $kop[$m3];
	  else $parts['solid']['text'] .= $kop[$m3];

	  if ($m3==1 && $m2!=1) $mil_text = ' миллион ';
	  elseif ($m3>1 && $m3<5 && $m2!=1) $mil_text = ' миллиона ';

	  $parts['solid']['text'] .= $mil_text;
	}
	if ($t) {
	  if (strlen($t)==3) {
		$t1 = (int)$t[0];
		$t2 = (int)$t[1];
		$t3 = (int)$t[2];
	  } elseif (strlen($t)==2) {
		$t1 = 0;
		$t2 = (int)$t[0];
		$t3 = (int)$t[1];
	  } else {
		$t1 = 0;
		$t2 = 0;
		$t3 = (int)$t;
	  }

	  $tys_text = ' тысяч ';
	  if ($t1) $parts['solid']['text'] .= $sto[$t1] . ' ';
	  if ($t2==1) $parts['solid']['text'] .= $first[$t3];
	  elseif ($t2) $parts['solid']['text'] .= $ten[$t2] . ' ' . $tys[$t3];
	  else $parts['solid']['text'] .= $tys[$t3];

	  if ($t3==1 && $t2!=1) $tys_text = ' тысяча ';
	  elseif ($t3>1 && $t3<5 && $t2!=1) $tys_text = ' тысячи ';

	  $parts['solid']['text'] .= $tys_text;
	}
	if (strlen($r)==3) {
	  $r1 = (int)substr($r, 0, 1);
	  $r2 = (int)substr($r, 1, 1);
	  $r3 = (int)substr($r, 2, 1);
	} elseif (strlen($r)==2) {
	  $r1 = 0;
	  $r2 = (int)substr($r, 1, 1);
	  $r3 = (int)substr($r, 2, 1);
	} else {
	  $r1 = 0;
	  $r2 = 0;
	  $r3 = (int)substr($r, 2, 1);
	}

	$rub_text = 'рублей';
	$parts['solid']['text'] .= $sto[$r1] . ' ';
	if ($r2==1) $parts['solid']['text'] .= $first[$r3] . ' ';
	else $parts['solid']['text'] .= $ten[$r2] . ' ' . $kop[$r3];

	if ($r3==1 && $r2!=1) $rub_text = 'рубль';
	elseif ($r3>1 && $r3<5 && $r2!=1) $rub_text = 'рубля';

	$parts['solid']['currency'] = $rub_text;
	$parts['decimal']['text'] = $all_k;
	$parts['decimal']['currency'] = 'коп.';

	return $parts;
  }

  function tep_transliterate($text) {
	$cyr = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
	$lat = array('A', 'B', 'V', 'G', 'D', 'E', '&#203;', 'Zh', 'Z', 'I', '&#463;', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'KH', 'T&#865;S', 'Ch', 'Sh', 'Shch', '"', 'Y', "'", '&#278;', 'I&#865;U', 'I&#865;A', 'a', 'b', 'v', 'g', 'd', 'e', '&#235;', 'zh', 'z', 'i', '&#301;', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 't&#865;s', 'ch', 'sh', 'shch', '"', 'y', "'", '&#279;', 'i&#865;u', 'i&#865;a');
	$text = str_replace($cyr, $lat, $text);

	return $text;
  }

  function tep_request_html($url, $method='POST', $data_array='') {
	// building POST-request:
	$URL_Info = parse_url($url);
	if ((int)$URL_Info['port']==0) $URL_Info['port'] = '80';
	$data_string = '';
	if ($method=='GET') {
	  $data_string = $URL_Info["query"];
	}
//	 else {
	  $cookie = '';
	  if (!is_array($data_array)) $data_array = array();
	  reset($data_array);
	  while (list($k, $v) = each($data_array)) {
		if ($k=='cookie') $cookie = $v;
		else $data_string .= (tep_not_null($data_string) ? '&' : '') . $k . '=' . urlencode($v);
	  }
//	}
	$request .= $method . " " . $URL_Info["path"] . ($method=='POST' ? '' : (tep_not_null($data_string) ? '?' . $data_string : '') . (tep_not_null($URL_Info["fragment"]) ? '#' . $URL_Info["fragment"] : '')) . " HTTP/1.1\r\n";
	$request .= "Host: " . $URL_Info["host"] . "\r\n";
	$request .= "Referer: " . $URL_Info["scheme"] . '://' . $URL_Info["host"] . $URL_Info["path"] . "\r\n";
	$request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30618)\r\n";
	$request .= "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1\r\n";
	$request .= "Accept-Language: en-us, en;q=0.50\r\n";
//	$request .= "Accept-Encoding: gzip, deflate, compress;q=0.9\r\n";
	$request .= "Keep-Alive: 300\r\n";
	$request .= "Connection: keep-alive\r\n";
	$request .= "Cache-Control: max-age=0\r\n";
	if ($cookie) $request .= "Cookie: " . $cookie . "\r\n";
	if ($method=='POST')  {
	  $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	  $request .= "Content-length: " . strlen($data_string) . "\r\n";
	} else {
	  $request .= "Content-Type: text/html; charset=windows-1251\r\n";
	}
	$request .= "Connection: close\r\n";
	$request .= "\r\n";
	if ($method=='POST') $request .= $data_string . "\r\n";

	$result = false;
	$fp = fsockopen($URL_Info['host'], $URL_Info['port']);
	if ($fp) {
	  fputs($fp, $request);
	  stream_set_timeout($fp, 2);
	  while(!feof($fp)) {
		$result .= fgets($fp, 128);
	  }
	  fclose($fp);
	}
	return $result;
  }

  function tep_get_translation($string) {
	$string = stripslashes(strip_tags(tep_html_entity_decode(trim($string), ENT_QUOTES)));
	if (empty($string)) return;
	$max_length = 100;
	$pieces = array();
	if (strlen($string) > $max_length) {
	  for ($i=0, $j=0; $i<strlen($string); $i++) {
		$pieces[$j] .= $string[$i];
		if (in_array($string[$i], array('.', '!', '?', ',')) && strlen($pieces[$j]) > $max_length) $j ++;
	  }
	} else {
	  $pieces[] = $string;
	}
	$tpieces = array();
	reset($pieces);
	while (list(, $piece) = each($pieces)) {
	  $url = 'http://translate.google.ru/?hl=ru&layout=2&eotf=0&sl=ru&tl=en&q=' . urlencode($piece);
	  $url = 'http://www.webproxyonline.info/browse.php?u=' . urlencode($url);
//	  $url = 'http://www.nedproxy.com/browse.php?u=' . urlencode($url);
	  $result = tep_request_html($url, 'GET');
	  $result = mb_convert_encoding($result, 'CP1251', 'UTF-8');
	  preg_match('/<span id=result_box[^>]+>(.+)<div id="translit"/', $result, $regs);
	  $tpieces[] = tep_db_prepare_input(strip_tags($regs[1]));
	}
	$translation = implode(' ', $tpieces);

	return array('page' => $result, 'translation' => $translation);
  }

  function tep_crop_thumb($src_img, $thumbnail_width, $thumbnail_height, $destination='', $quality='85') {
	list($width_orig, $height_orig, $img_type) = @getimagesize($src_img);

	if ($img_type==1) $myImage = @imagecreatefromgif($src_img);
	elseif ($img_type==2) $myImage = @imagecreatefromjpeg($src_img);
	elseif ($img_type==3) $myImage = @imagecreatefrompng($src_img);

	if (!$myImage) return false;

	$ratio_orig = $width_orig/$height_orig;

	if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
	  $new_height = $thumbnail_width / $ratio_orig;
	  $new_width = $thumbnail_width;
	} else {
	  $new_width = $thumbnail_height * $ratio_orig;
	  $new_height = $thumbnail_height;
	}

	$x_mid = $new_width / 2;  //horizontal middle
	$y_mid = $new_height / 2; //vertical middle

	$process = imagecreatetruecolor(round($new_width), round($new_height)); 

	imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
	$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height); 
	imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

	imagedestroy($process);
	imagedestroy($myImage);

	if (tep_not_null($destination)) {
	  if ($img_type==1 || $img_type==3) {
		if (function_exists('imagegif') && $img_type==1) {
		  $bool = imagegif($thumb, $destination);
		} else {
		  $bool = imagepng($thumb, $destination);
		}
	  } else {
		$bool = imagejpeg($thumb, $destination, $quality);
	  }

	  imagedestroy($thumb);

	  return $bool;
	}

	return $thumb;
  }

  function tep_create_thumb($source, $destination = '', $new_width = '', $new_height = '', $quality = '85', $action = '') {
	$bool = false;
	$new_width = (int)$new_width;
	$new_height = (int)$new_height;

	if (empty($destination)) $destination = $source;
	if (empty($quality)) $quality = '85';

	$src_width = 0;
	$src_height = 0;
	$src_type = 0;
	list($src_width, $src_height, $src_type) = @getimagesize($source);

	if (empty($source)) {
	  return false;
//	  die('Fatal error: Image source not set!');
	} elseif ((int)$src_width==0 && (int)$src_height==0) {
	  return false;
//	  die('Fatal error: File "' . $source . '" is not an image!');
	} elseif (!in_array($src_type, array('1', '2', '3'))) {
	  return false;
//	  die('Fatal error: Unsupported file format (.GIF, .JPG and .PNG files are only allowed)!');
	} else {
	  if ($new_width==0 && $new_height==0) {
		$new_width = $src_width;
		$new_height = $src_height;
	  }

	  $reduce_only = false;
	  $new_image_width = $new_width;
	  $new_image_height = $new_height;
	  if ((int)$new_width == 0 && (int)$new_height > 0) {
		$new_image_width = round(($new_height / $src_height) * $src_width);
		if ($src_height < $new_height && $action=='reduce_only') $reduce_only = true;
	  } elseif ((int)$new_height == 0 && (int)$new_width > 0) {
		$new_image_height = round(($new_width / $src_width) * $src_height);
		if ($src_width < $new_width && $action=='reduce_only') $reduce_only = true;
	  } elseif ((int)$new_height > 0 && (int)$new_width > 0) {
		if ($src_height > $new_height && $src_width > $new_width) {
		  return tep_crop_thumb($source, $new_width, $new_height, $destination, $quality);
		}
		if ( ($src_height < $new_height || $src_width < $new_width) && $action=='reduce_only') $reduce_only = true;
	  }
	  if ($new_image_width > $new_width || $new_image_height > $new_height) {
		if ($new_image_width > $new_width) {
		  $new_image_width = round(($new_height / $new_image_height) * $new_image_width);
		} else {
		  $new_image_height = round(($new_width / $new_image_width) * $new_image_height);
		}
	  }
#1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8
	  $type = '';
	  if ($src_type) {
		switch ($src_type) {
		  case '1':
			$type = 'gif';
			break;
		  case '2':
			$type = 'jpeg';
			break;
		  case '3':
			$type = 'png';
			break;
		}
		if ($reduce_only==true) {
		  $bool = copy($source, $destination);
		} else {
		  if (tep_not_null($type)) {
			if ($type=='jpeg') {
			  $src_image = @imagecreatefromjpeg($source);
			} elseif ($type=='gif') {
			  if (function_exists('imagecreatefromgif')) {
				$src_image = @imagecreatefromgif($source);
			  } else {
				$src_image = @imagecreatefrompng($source);
			  }
			} elseif ($type=='png') {
			  $src_image = @imagecreatefrompng($source);
			}
			if (!$src_image) {
			  return false;
			} else {
			  $canvas_width = $new_image_width;
			  $canvas_height = $new_image_height;
			  if ($new_image_width > $new_image_height*1.3) {
				$new_image_width = $new_image_height*1.3;
				$canvas_width = $new_image_width;
				$canvas_height = $new_image_height;
				$new_image_height = round(($new_image_width / $src_width) * $src_height);
			  }

			  if (function_exists('imagecreatetruecolor')) {
				$dst_image = imagecreatetruecolor($canvas_width, $canvas_height);
			  } else {
				$dst_image = imagecreate($canvas_width, $canvas_height);
			  }
			  $color = imagecolorallocate($dst_image, 255, 255, 255);
			  imagefill($dst_image, 0, 0, $color);
			  imagecopyresampled($dst_image, $src_image, ($canvas_width-$new_image_width)/2, ($canvas_height-$new_image_height)/2, 0, 0, $new_image_width, $new_image_height, $src_width, $src_height);

			  if ($type=='jpeg') {
				$bool = imagejpeg($dst_image, $destination, $quality);
			  } elseif ($type=='gif') {
				if (function_exists('imagegif')) {
				  $bool = imagegif($dst_image, $destination);
				} else {
				  $bool = imagepng($dst_image, $destination);
				}
			  } elseif ($type=='png') {
				$bool = imagepng($dst_image, $destination);
			  }
			  imagedestroy($src_image);
			  imagedestroy($dst_image);
			}
		  }
		}
	  }
	}

	return $bool;
  }
  
  // Puts a message to a log file
  // Log file name is constructed from the первого параметра and extension ".log"
  // If no param передан, используется default.
  // Если передан массив или объект, он расписывается в строчку
  // $data - некие данные, прицепленные к сообщению. Выводятся на следующей строке. 
  function tep_log($message = '', $data = null, $log_name = 'default') {
  	$time = date('Y-m-d h:i:s');
  	$out = $time . ' ' . $message ."\n";
  	if (!empty($data)) {
      $out .= var_export($data, true) . "\n";  		
  	} 
  	// Путь берем как у сессий
  	$log_filename = SESSION_WRITE_DIRECTORY . '/' . $log_name . '.log';
  	file_put_contents($log_filename, $out, FILE_APPEND);
  }
  
?>