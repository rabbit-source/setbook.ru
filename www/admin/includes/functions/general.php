<?php
////
// Redirect to another page or site
  function tep_redirect($url) {
    header('Location: ' . $url);

    die();
  }

////
// Parse the data used in the html tags to ensure the tags will not break
  function tep_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

  function tep_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return str_replace("'", '&#039;', htmlspecialchars(stripslashes($string)));
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
    $string = preg_replace('/\s+/', ' ', $string);

    return strip_tags($string);
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

  function tep_customers_name($customers_id) {
    $customers = tep_db_query("select customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customers_id . "'");
    $customers_values = tep_db_fetch_array($customers);

    return $customers_values['customers_firstname'] . ' ' . $customers_values['customers_lastname'];
  }

  function tep_get_path($current_category_id = '') {
    global $cPath_array;

    if ($current_category_id == '') {
      $cPath_new = implode('_', $cPath_array);
    } else {
      if (sizeof($cPath_array) == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';
        $last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$cPath_array[(sizeof($cPath_array)-1)] . "'");
        $last_category = tep_db_fetch_array($last_category_query);

        $current_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
        $current_category = tep_db_fetch_array($current_category_query);

        if ($last_category['parent_id'] == $current_category['parent_id']) {
          for ($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }

        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    }

    return 'cPath=' . $cPath_new;
  }

  function print_var($var_name, $var_value) {
	$ret_str = '';
	if (is_array($var_value)) {
	  while (list($key, $val) = each($var_value)) {
		if (is_array($val)) {
		  $ret_str .= print_var($var_name . "[" . $key . "]", $val);
		} else {
		  $ret_str .= $var_name . "[" . $key . "]=" . urlencode(stripslashes($val)) . "&";
		}
	  }
	} else {
	  $ret_str .= $var_name . "=" . urlencode(stripslashes($var_value)) . "&";
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
		if ( (strlen($value) > 0) && ($key != tep_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {
		  $get_url .= print_var($key, $value);
		}
	  }
	}

	return $get_url;
  }

  function tep_date_long($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

	$monthes_searched = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
	$monthes_replace_by = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

    return str_replace($monthes_searched, $monthes_replace_by, strftime(DATE_FORMAT_LONG, mktime($hour, $minute, $second, $month, $day, $year)));
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') || ($raw_date == '0000-00-00') ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return ereg_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }

  }

  function tep_datetime_short($raw_datetime) {
    if ( ($raw_datetime == '0000-00-00 00:00:00') || ($raw_datetime == '') ) return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }

  function tep_get_metatags_info($metatags_content, $content_type, $content_id, $language_id = '') {
	global $languages_id;
	if (empty($language_id)) $language_id = $languages_id;
	if (!tep_db_field_exists(TABLE_METATAGS, $metatags_content)) return false;
	$query = tep_db_query("select " . tep_db_input($metatags_content) . " as value from " . TABLE_METATAGS . " where content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "' and language_id = '" . (int)$language_id . "'");
	$row = tep_db_fetch_array($query);
	return $row['value'];
  }

  function tep_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $products_types_id = 0) {
	global $languages_id;

	if (!is_array($category_tree_array)) $category_tree_array = array();
	if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

	if ($products_types_id==0 && $parent_id==0 && func_num_args()<6) {
	  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
	  while ($products_types = tep_db_fetch_array($products_types_query)) {
		$category_tree_array[] = array('id' => $products_types['products_types_id'], 'text' => $products_types['products_types_name'], 'active' => false);
		$category_tree_array = tep_get_category_tree($parent_id, $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, $include_itself, $products_types['products_types_id']);
	  }
	} else {
	  if ($include_itself) {
		$category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd where cd.language_id = '" . (int)$languages_id . "' and cd.categories_id = '" . (int)$parent_id . "'");
		$category = tep_db_fetch_array($category_query);
		$category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name']);
	  }

	  $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where 1" . ($products_types_id>0 ? " and c.products_types_id = '" . (int)$products_types_id . "'" : "") . " and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");
	  while ($categories = tep_db_fetch_array($categories_query)) {
		if ($exclude != $categories['categories_id']) $category_tree_array[] = array('id' => $categories['categories_id'], 'text' => $spacing . $categories['categories_name']);
		$category_tree_array = tep_get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, $include_itself, $products_types_id);
	  }
	}

    return $category_tree_array;
  }

  function tep_get_cities_tree($zone_id, $parent_id = '0', $spacing = '', $exclude = '', $cities_tree_array = '', $include_itself = false) {
	if (!is_array($cities_tree_array)) $cities_tree_array = array();
	if ( (sizeof($cities_tree_array) < 1) && ($exclude != '0') ) $cities_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

	if ($include_itself) {
	  $city_query = tep_db_query("select city_name from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "' and city_id = '" . tep_db_input($parent_id) . "'");
	  $city = tep_db_fetch_array($city_query);
	  $cities_tree_array[] = array('id' => $parent_id, 'text' => $city['city_name']);
	}

	$cities_query = tep_db_query("select city_id, if(suburb_name='',concat_ws('', '[', city_id, '] ', city_name),concat_ws('', '[', city_id, '] ', city_name, ' (', suburb_name, ')')) as city_full_name, parent_id from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "' and parent_id = '" . (int)$parent_id . "' order by city_name");
	while ($cities = tep_db_fetch_array($cities_query)) {
	  if ($exclude != $cities['city_id']) $cities_tree_array[] = array('id' => $cities['city_id'], 'text' => $spacing . $cities['city_full_name']);
	  $cities_tree_array = tep_get_cities_tree($zone_id, $cities['city_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $cities_tree_array, $include_itself);
	}

    return $cities_tree_array;
  }

  function tep_get_information_list($parent_id, $disable_sections = true) {
	global $languages_id;

	$information_array = array();

	$sort_array = array();
	$sections_query = tep_db_query("select sections_id, sort_order from " . TABLE_SECTIONS . " where parent_id = '" . (int)$parent_id . "' and language_id = '" . (int)$languages_id . "' order by sort_order, sections_name");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  $sort_array['section:' . $sections['sections_id']] = $sections['sort_order'];
	}
    $informations_query = tep_db_query("select i.information_id, (i.sort_order - i2s.information_default_status) as sort_order from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "' and i2s.sections_id = '" . (int)$parent_id . "' order by i2s.information_default_status desc, i.sort_order, i.information_name");
    while ($informations = tep_db_fetch_array($informations_query)) {
	  $sort_array['information:' . $informations['information_id']] = $informations['sort_order'];
	}
	asort($sort_array);
	reset($sort_array);
	while (list($s) = each($sort_array)) {
	  list($type, $id) = explode(':', $s);
	  if ($type=='section') {
		$query = tep_db_query("select sections_id as id, sections_name as text from " . TABLE_SECTIONS . " where sections_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
		$row = tep_db_fetch_array($query);
	  } else {
		$query = tep_db_query("select i.information_id as id, i.information_name as text from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = '" . (int)$id . "' and i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "'");
		$row = tep_db_fetch_array($query);
		if ($parent_id > 0) $row['text'] = '&nbsp;&nbsp;&nbsp;' . $row['text'];
	  }
	  $ar = array('id' => $row['id'], 'text' => $row['text'], 'type' => $type);
	  if ($disable_sections && $type=='section') $ar['active'] = '0';
	  $information_array[] = $ar;
	}

	return $information_array;
  }

  function tep_get_information_tree($parent_id = '0', $spacing = '', $section_tree_array = '', $disable_sections = true) {
    global $languages_id;

    if (!is_array($section_tree_array)) $section_tree_array = array();

	$information_list = tep_get_information_list($parent_id, $disable_sections);
	reset($information_list);
	while (list(, $item) = each($information_list)) {
	  $ar = array('id' => $item['id'], 'text' => $item['text']);
	  if (isset($item['active'])) $ar['active'] = $item['active'];
	  $section_tree_array[] = $ar;
	  if ($item['type']=='section') {
    	$section_tree_array = tep_get_information_tree($item['id'], $spacing . '&nbsp;&nbsp;&nbsp;', $section_tree_array, $disable_sections);
	  }
	}

    return $section_tree_array;
  }

  function tep_draw_products_pull_down($name, $parameters = '', $exclude = '') {
    global $currencies, $languages_id;

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by products_name");
    while ($products = tep_db_fetch_array($products_query)) {
      if (!in_array($products['products_id'], $exclude)) {
        $select_string .= '<option value="' . $products['products_id'] . '">' . $products['products_name'] . ' (' . $currencies->format($products['products_price']) . ')</option>';
      }
    }

    $select_string .= '</select>';

    return $select_string;
  }

  function tep_info_image($image, $alt, $width = '', $height = '') {
    if (tep_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image)) ) {
      $image = tep_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
    } else {
      $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
  }

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

  function tep_get_country_name($country_id, $language_id = '') {
	global $languages_id;

	$country_name = '';
	if (empty($language_id)) $language_id = $languages_id;

	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);

	  $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "' and language_id = '" . (int)$language_id . "'");
	  if (tep_db_num_rows($country_query) > 0) {
		$country = tep_db_fetch_array($country_query);
		$country_name = $country['countries_name'];
		break;
	  }
	}
	tep_db_select_db(DB_DATABASE);

	return $country_name;
  }

  function tep_get_zone_name($country_id, $zone_id, $default_zone) {
	$zone_name = $default_zone;

	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
	  if (tep_db_num_rows($zone_query) > 0) {
		$zone = tep_db_fetch_array($zone_query);
		$zone_name = $zone['zone_name'];
		break;
	  }
	}
	tep_db_select_db(DB_DATABASE);

    return $zone_name;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != NULL) && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
  function tep_get_parents(&$categories, $categories_id, $table = '') {
	if ($table == TABLE_SECTIONS) {
	  $field = 'sections_id';
	} else {
	  $table = TABLE_CATEGORIES;
	  $field = 'categories_id';
	}
	$parent_query = tep_db_query("select parent_id from " . tep_db_input($table) . " where " . $field . " = '" . (int)$categories_id . "' limit 1");
	$parent = tep_db_fetch_array($parent_query);
	if ($parent['parent_id'] == 0) return true;
	$categories[sizeof($categories)] = $parent['parent_id'];
	if ($parent['parent_id'] != $categories_id) {
	  tep_get_parents($categories, $parent['parent_id'], $table);
	}
  }

////
// Return all subcategory IDs
// TABLES: categories
  function tep_get_subcategories(&$subcategories_array, $parent_id = 0, $table = TABLE_CATEGORIES) {
	if ($table == TABLE_CATEGORIES) $subcategories_query = tep_db_query("select boards_categories_id as categories_id from " . TABLE_BOARDS_CATEGORIES . " where parent_id = '" . (int)$parent_id . "'");
	else $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$parent_id . "'");
    while ($subcategories = tep_db_fetch_array($subcategories_query)) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];
      if ($subcategories['categories_id'] != $parent_id) {
        tep_get_subcategories($subcategories_array, $subcategories['categories_id'], $table);
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

  function tep_browser_detect($component) {
    global $HTTP_USER_AGENT;

    return stristr($HTTP_USER_AGENT, $component);
  }

  function tep_tax_classes_pull_down($parameters, $selected = '') {
    $select_string = '<select ' . $parameters . '>';
    $classes_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($classes = tep_db_fetch_array($classes_query)) {
      $select_string .= '<option value="' . $classes['tax_class_id'] . '"';
      if ($selected == $classes['tax_class_id']) $select_string .= ' selected="selected"';
      $select_string .= '>' . $classes['tax_class_title'] . '</option>';
    }
    $select_string .= '</select>';

    return $select_string;
  }

  function tep_geo_zones_pull_down($parameters, $selected = '') {
    $select_string = '<select ' . $parameters . '>';
    $zones_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zones = tep_db_fetch_array($zones_query)) {
      $select_string .= '<option value="' . $zones['geo_zone_id'] . '"';
      if ($selected == $zones['geo_zone_id']) $select_string .= ' selected="selected"';
      $select_string .= '>' . $zones['geo_zone_name'] . '</option>';
    }
    $select_string .= '</select>';

    return $select_string;
  }

  function tep_get_geo_zone_name($geo_zone_id) {
    $zones_query = tep_db_query("select geo_zone_name from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int)$geo_zone_id . "'");

    if (!tep_db_num_rows($zones_query)) {
      $geo_zone_name = $geo_zone_id;
    } else {
      $zones = tep_db_fetch_array($zones_query);
      $geo_zone_name = $zones['geo_zone_name'];
    }

    return $geo_zone_name;
  }

  function tep_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . (int)$address_format_id . "'");
    $address_format = tep_db_fetch_array($address_format_query);

    $company = tep_output_string_protected($address['company']);
    if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
//      $firstname = tep_output_string_protected($address['firstname']);
//      $lastname = tep_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && tep_not_null($address['name'])) {
//      $firstname = tep_output_string_protected($address['name']);
//      $lastname = '';
    } else {
//      $firstname = '';
//      $lastname = '';
    }
    $street = $address['street_address'];
    $suburb = $address['suburb'];
    $city = $address['city'];
    $state = $address['state'];
    $telephone = $address['telephone'];
    if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
      $country = tep_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
//		$state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);
		$state = tep_get_zone_name($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && tep_not_null($address['country'])) {
      $country = $address['country'];
    } else {
      $country = '';
    }
	if ($state==$city) $city = '';
    if (tep_not_null($address['postcode'])) {
	  $postcode = $address['postcode'] . ', ';
	  $zip = $postcode;
	}

    if ($html) {
// HTML Mode
      $HR = '<hr>';
      $hr = '<hr>';
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
//    if ($suburb != '') $streets = $street . $cr . $suburb;
    if ($country == '') $country = $address['country'];
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

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_get_zone_code
  //
  // Arguments   : country           country code string
  //               zone              state/province zone_id
  //               def_state         default string if zone==0
  //
  // Return      : state_prov_code   state/province code
  //
  // Description : Function to retrieve the state/province code (as in FL for Florida etc)
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function tep_get_zone_code($country, $zone, $def_state) {

    $state_prov_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and zone_id = '" . (int)$zone . "'");

    if (!tep_db_num_rows($state_prov_query)) {
      $state_prov_code = $def_state;
    }
    else {
      $state_prov_values = tep_db_fetch_array($state_prov_query);
      $state_prov_code = $state_prov_values['zone_code'];
    }

    return $state_prov_code;
  }

  function tep_get_languages() {
    $languages_query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " order by sort_order");
    while ($languages = tep_db_fetch_array($languages_query)) {
      $languages_array[] = array('id' => $languages['languages_id'],
                                 'name' => $languages['name'],
                                 'code' => $languages['code'],
                                 'image' => $languages['image'],
                                 'directory' => $languages['directory']);
    }

    return $languages_array;
  }

  function tep_get_category_name($category_id, $language_id = 0) {
	global $languages_id;

	if ($language_id==0) $language_id = $languages_id;
    $category_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$category_id . "' and language_id = '" . (int)$language_id . "'");
    $category = tep_db_fetch_array($category_query);

    return $category['categories_name'];
  }

  function tep_get_category_description($category_id, $language_id = 0) {
	global $languages_id;

	if ($language_id==0) $language_id = $languages_id;
    $category_query = tep_db_query("select categories_description from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$category_id . "' and language_id = '" . (int)$language_id . "'");
    $category = tep_db_fetch_array($category_query);

    return $category['categories_description'];
  }

  function tep_get_orders_status_name($orders_status_id, $language_id = '') {
    global $languages_id;

    if (!$language_id) $language_id = $languages_id;
    $orders_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$orders_status_id . "' and language_id = '" . (int)$language_id . "'");
    $orders_status = tep_db_fetch_array($orders_status_query);

    return $orders_status['orders_status_name'];
  }

  function tep_get_subject_name($subject_id, $language_id = '') {
    global $languages_id;

    if (!$language_id) $language_id = $languages_id;
    $subject_query = tep_db_query("select subjects_name from " . TABLE_SUBJECTS . " where subjects_id = '" . (int)$subject_id . "' and language_id = '" . (int)$language_id . "'");
    $subject = tep_db_fetch_array($subject_query);

    return $subject['subjects_name'];
  }

  function tep_get_orders_status() {
    global $languages_id;

    $orders_status_array = array();
    $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by sort_order");
    while ($orders_status = tep_db_fetch_array($orders_status_query)) {
      $orders_status_array[] = array('id' => $orders_status['orders_status_id'],
                                     'text' => $orders_status['orders_status_name']);
    }

    return $orders_status_array;
  }

  function tep_get_products_name($product_id, $language_id = 0) {
    global $languages_id;

    if ($language_id == 0) $language_id = $languages_id;
    $product_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language_id . "'");
    $product = tep_db_fetch_array($product_query);

    return $product['products_name'];
  }

  function tep_get_products_description($product_id, $language_id = 0) {
    global $languages_id;

    if ($language_id == 0) $language_id = $languages_id;
    $product_query = tep_db_query("select products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language_id . "'");
    $product = tep_db_fetch_array($product_query);

    return $product['products_description'];
  }

  function tep_get_products_url($product_id, $language_id, $field = 'products_url') {
    $product_query = tep_db_query("select " . tep_db_input($field) . " as text from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language_id . "'");
    $product = tep_db_fetch_array($product_query);

    return $product['text'];
  }

////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
  function tep_get_manufacturer_url($manufacturer_id, $language_id) {
    $manufacturer_query = tep_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturer_id . "' and languages_id = '" . (int)$language_id . "'");
    $manufacturer = tep_db_fetch_array($manufacturer_query);

    return $manufacturer['manufacturers_url'];
  }

  function tep_get_manufacturer_name($manufacturer_id, $language_id) {
    $manufacturer_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturer_id . "' and languages_id = '" . (int)$language_id . "'");
    $manufacturer = tep_db_fetch_array($manufacturer_query);

    return $manufacturer['manufacturers_name'];
  }

////
// Wrapper for class_exists() function
// This function is not available in all PHP versions so we test it before using it.
  function tep_class_exists($class_name) {
    if (function_exists('class_exists')) {
      return class_exists($class_name);
    } else {
      return true;
    }
  }

////
// Count how many products exist in a category
// TABLES: products, products_to_categories, categories
  function tep_products_in_category_count($categories_id, $include_deactivated = false) {
    $products_count = 0;

    if ($include_deactivated) {
      $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$categories_id . "'");
    } else {
      $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' and p2c.categories_id = '" . (int)$categories_id . "'");
    }

    $products = tep_db_fetch_array($products_query);

    $products_count += $products['total'];

    $childs_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$categories_id . "'");
    if (tep_db_num_rows($childs_query)) {
      while ($childs = tep_db_fetch_array($childs_query)) {
        $products_count += tep_products_in_category_count($childs['categories_id'], $include_deactivated);
      }
    }

    return $products_count;
  }

////
// Count how many subcategories exist in a category
// TABLES: categories
  function tep_childs_in_category_count($categories_id) {
    $categories_count = 0;

    $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$categories_id . "'");
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_count++;
      $categories_count += tep_childs_in_category_count($categories['categories_id']);
    }

    return $categories_count;
  }

////
// Returns an array with countries
// TABLES: countries
  function tep_get_countries($default = '', $only_shop_countries = false) {
	global $languages_id;

    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }

	$countries = array();
	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''" . ($only_shop_countries ? " and shops_id = '" . (int)SHOP_ID . "'" : ""));
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $countries_query = tep_db_query("select countries_id, countries_ru_name from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' order by sort_order, countries_name");
	  while ($countries_row = tep_db_fetch_array($countries_query)) {
		$countries[$countries_row['countries_id']] = $countries_row['countries_ru_name'];
	  }
	}
	tep_db_select_db(DB_DATABASE);

	asort($countries);
	reset($countries);
	while (list($country_id, $country_name) = each($countries)) {
	  $countries_array[] = array('id' => $country_id,
								 'text' => ucwords(strtolower($country_name)));
	}

    return $countries_array;
  }

////
// return an array with country zones
  function tep_get_country_zones($country_id) {
    $zones_array = array();
    $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' order by zone_name");
    while ($zones = tep_db_fetch_array($zones_query)) {
      $zones_array[] = array('id' => $zones['zone_id'],
                             'text' => $zones['zone_name']);
    }

    return $zones_array;
  }

  function tep_prepare_country_zones_pull_down($country_id = '') {
// preset the width of the drop-down for Netscape
    $pre = '';
    if ( (!tep_browser_detect('MSIE')) && (tep_browser_detect('Mozilla/4')) ) {
      for ($i=0; $i<45; $i++) $pre .= '&nbsp;';
    }

    $zones = tep_get_country_zones($country_id);

    if (sizeof($zones) > 0) {
      $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
      $zones = array_merge($zones_select, $zones);
    } else {
      $zones = array(array('id' => '', 'text' => TYPE_BELOW));
// create dummy options for Netscape to preset the height of the drop-down
      if ( (!tep_browser_detect('MSIE')) && (tep_browser_detect('Mozilla/4')) ) {
        for ($i=0; $i<9; $i++) {
          $zones[] = array('id' => '', 'text' => $pre);
        }
      }
    }

    return $zones;
  }

////
// Get list of address_format_id's
  function tep_get_address_formats() {
    $address_format_query = tep_db_query("select address_format_id from " . TABLE_ADDRESS_FORMAT . " order by address_format_id");
    $address_format_array = array();
    while ($address_format_values = tep_db_fetch_array($address_format_query)) {
      $address_format_array[] = array('id' => $address_format_values['address_format_id'],
                                      'text' => $address_format_values['address_format_id']);
    }
    return $address_format_array;
  }

////
// Alias function for Store configuration values in the Administration Tool
  function tep_cfg_pull_down_country_list($country_id) {
    return tep_draw_pull_down_menu('configuration_value', tep_get_countries(), $country_id);
  }

  function tep_cfg_pull_down_zone_list($zone_id) {
    return tep_draw_pull_down_menu('configuration_value', tep_get_country_zones(STORE_COUNTRY), $zone_id);
  }

  function tep_cfg_pull_down_tax_classes($tax_class_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }

    return tep_draw_pull_down_menu($name, $tax_class_array, $tax_class_id);
  }

////
// Function to read in text area in admin
  function tep_cfg_textarea($text, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return tep_draw_textarea_field($name, false, 35, 5, $text);
  }

  function tep_cfg_get_zone_name($zone_id) {
	$zones = array_map('trim', explode(',', $zone_id));
	$zones_names = array();
	reset($zones);
	while (list(, $zone_id) = each($zones)) {
	  $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");

	  if (!tep_db_num_rows($zone_query)) {
		$zones_names[] = $zone_id;
	  } else {
		$zone = tep_db_fetch_array($zone_query);
		$zones_names[] = $zone['zone_name'];
	  }
	}
	sort($zones_names);
	return implode(', ', $zones_names);
  }

////
// Sets the status of a banner
  function tep_set_banner_status($banners_id, $status) {
    if ($status == '1') {
      return tep_db_query("update " . TABLE_BANNERS . " set status = '1', expires_impressions = NULL, expires_date = NULL, date_status_change = NULL where banners_id = '" . $banners_id . "'");
    } elseif ($status == '0') {
      return tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_status_change = now() where banners_id = '" . $banners_id . "'");
    } else {
      return -1;
    }
  }

////
// Sets the status of a product on special
  function tep_set_specials_status($specials_id, $status) {
    if ($status == '1') {
      return tep_db_query("update " . TABLE_SPECIALS . " set status = '1', expires_date = NULL, date_status_change = NULL where specials_id = '" . (int)$specials_id . "'");
    } elseif ($status == '0') {
      return tep_db_query("update " . TABLE_SPECIALS . " set status = '0', date_status_change = now() where specials_id = '" . (int)$specials_id . "'");
    } else {
      return -1;
    }
  }

////
// Sets timeout for the current script.
// Cant be used in safe mode.
  function tep_set_time_limit($limit) {
	ini_set('max_execution_time', (string)$limit);
	ini_set('max_input_time', (string)$limit);
//	if (!get_cfg_var('safe_mode')) {
	  set_time_limit($limit);
//	}
  }

  function tep_get_payment_modules($shop_id) {
	global $language;

	$payments_array = array();

	$shop_db_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$shop_id . "'");
	$shop_db = tep_db_fetch_array($shop_db_query);
	tep_db_select_db($shop_db['shops_database']);

	$payment_config_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_INSTALLED'");
	$payment_config = tep_db_fetch_array($payment_config_query);
	$available_payments = explode(';', $payment_config['configuration_value']);

	$module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
	$file_extension = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '.'));
	$directory_array = array();
	if ($dir = @dir($module_directory)) {
	  while ($file = $dir->read()) {
		if (!is_dir($module_directory . $file)) {
		  if (substr($file, strrpos($file, '.')) == $file_extension) {
			if (in_array($file, $available_payments)) $directory_array[] = $file;
		  }
		}
	  }
	  sort($directory_array);
	  $dir->close();
	}

	for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	  $file = $directory_array[$i];

	  include_once(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $file);
	  include_once($module_directory . $file);

	  $class = substr($file, 0, strrpos($file, '.'));
	  if (tep_class_exists($class)) {
		$module = new $class;
		$config_check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id = '6' and (configuration_key = '" . strtoupper('MODULE_PAYMENT_' . $module->code . '_TEXT_TITLE') . "' or configuration_key = '" . strtoupper('MODULE_PAYMENT_' . $module->code . '_TITLE') . "'" . (tep_not_null($module->title) ? " or configuration_key = '" . strtoupper($module->title) . "'" : "") . ")");
		if (tep_db_num_rows($config_check_query) > 0) {
		  $config_check = tep_db_fetch_array($config_check_query);
		  $module_title = tep_html_entity_decode($config_check['configuration_value']);
		} else {
		  $module_title = $module->title;
		}
		$payments_array[$class] = $module_title;
	  }
	}
	tep_db_select_db(DB_DATABASE);

	return $payments_array;
  }

  function tep_get_shipping_modules($shop_id) {
	global $language;

	$shippings_array = array();

	$shop_db_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$shop_id . "'");
	$shop_db = tep_db_fetch_array($shop_db_query);
	tep_db_select_db($shop_db['shops_database']);

	$shipping_config_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_INSTALLED'");
	$shipping_config = tep_db_fetch_array($shipping_config_query);
	$available_shippings = explode(';', $shipping_config['configuration_value']);

	$module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
	$module_key = 'MODULE_SHIPPING_INSTALLED';
	$file_extension = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '.'));
	$directory_array = array();
	if ($dir = @dir($module_directory)) {
	  while ($file = $dir->read()) {
		if (!is_dir($module_directory . $file)) {
		  if (substr($file, strrpos($file, '.')) == $file_extension) {
			if (in_array($file, $available_shippings)) $directory_array[] = $file;
		  }
		}
	  }
	  sort($directory_array);
	  $dir->close();
	}

	for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	  $file = $directory_array[$i];

	  include_once(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/shipping/' . $file);
	  include_once($module_directory . $file);

	  $class = substr($file, 0, strrpos($file, '.'));
	  if (tep_class_exists($class)) {
		$module = new $class;
		$config_check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id = '6' and configuration_key = '" . strtoupper('MODULE_SHIPPING_' . $module->code . '_TITLE') . "'");
		if (tep_db_num_rows($config_check_query) < 1)
		  $config_check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id = '6' and configuration_key = '" . strtoupper('MODULE_SHIPPING_' . $module->code . '_TEXT_TITLE') . "'");
		if (tep_db_num_rows($config_check_query) > 0) {
		  $config_check = tep_db_fetch_array($config_check_query);
		  $module_title = tep_html_entity_decode($config_check['configuration_value']);
		} else {
		  $module_title = $module->title;
		}
		$shippings_array[$class] = $module_title;
	  }
	}
	tep_db_select_db(DB_DATABASE);

	return $shippings_array;
  }

////
// Alias function for Store configuration values in the Administration Tool
  function tep_cfg_select_option($select_array, $key_value, $key = '') {
    $string = '';

    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '<br><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' CHECKED';

      $string .= '> ' . $select_array[$i];
    }

    return $string;
  }

////
// Alias function for module configuration keys
  function tep_mod_select_option($select_array, $key_name, $key_value) {
    reset($select_array);
    while (list($key, $value) = each($select_array)) {
      if (is_int($key)) $key = $value;
      $string .= '<br><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' CHECKED';
      $string .= '> ' . $value;
    }

    return $string;
  }

////
// Retreive server information
  function tep_get_system_information() {
    global $_SERVER;

    $db_query = tep_db_query("select now() as datetime");
    $db = tep_db_fetch_array($db_query);

    list($system, $host, $kernel) = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    return array('date' => tep_datetime_short(date('Y-m-d H:i:s')),
                 'system' => $system,
                 'kernel' => $kernel,
                 'host' => $host,
                 'ip' => gethostbyname($host),
                 'uptime' => @exec('uptime'),
                 'http_server' => $_SERVER['SERVER_SOFTWARE'],
                 'php' => PHP_VERSION,
                 'zend' => (function_exists('zend_version') ? zend_version() : ''),
                 'db_server' => DB_SERVER,
                 'db_ip' => gethostbyname(DB_SERVER),
                 'db_version' => 'MySQL ' . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : ''),
                 'db_date' => tep_datetime_short($db['datetime']));
  }

  function tep_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $languages_id;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
      $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$id . "'");
      while ($categories = tep_db_fetch_array($categories_query)) {
        if ($categories['categories_id'] == '0') {
          $categories_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
        } else {
          $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$categories['categories_id'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
          $category = tep_db_fetch_array($category_query);
          $categories_array[$index][] = array('id' => $categories['categories_id'], 'text' => $category['categories_name']);
          if ( (tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0') ) $categories_array = tep_generate_category_path($category['parent_id'], 'category', $categories_array, $index);
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
      }
    } elseif ($from == 'category') {
      $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);
      $categories_array[$index][] = array('id' => $id, 'text' => $category['categories_name']);
      if ( (tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0') ) $categories_array = tep_generate_category_path($category['parent_id'], 'category', $categories_array, $index);
    }

    return $categories_array;
  }

  function tep_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = tep_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function tep_get_generated_category_path_ids($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = tep_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

    return $calculated_category_path_string;
  }

  function tep_remove_category($categories_ids, $delete_products=true) {
	if (!is_array($categories_ids)) $categories = array($categories_ids);
	else $categories = $categories_ids;

	$categories_string = '';
	reset($categories);
	while (list(, $categories_id) = each($categories)) {
	  if ((int)$categories_id > 0) $categories_string .= (tep_not_null($categories_string) ? "', '" : '') . (int)$categories_id;
	}

	$shops_query = tep_db_query("select shops_database, shops_fs_dir from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $prev_file_query = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id in ('" . $categories_string . "') and categories_image <> ''");
	  while ($prev_file = tep_db_fetch_array($prev_file_query)) {
		$dir_fs_catalog_images = str_replace(DIR_FS_CATALOG, $shops['shops_fs_dir'], DIR_FS_CATALOG_IMAGES);
		@unlink($dir_fs_catalog_images . $prev_file['categories_image']);
	  }
      tep_db_query("delete from " . TABLE_CATEGORIES . " where categories_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_SPECIALS_CATEGORIES . " where categories_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_CATEGORIES_LINKED . " where categories_id in ('" . $categories_string . "') or linked_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_style = 'category' and content_id in ('" . $categories_string . "')");
      tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'category' and content_id in ('" . $categories_string . "')");
	}
	tep_db_select_db(DB_DATABASE);
  }

  function tep_remove_product($products_ids) {
	if (!is_array($products_ids)) $products = array($products_ids);
	else $products = $products_ids;

	$products_string = '';
	reset($products);
	while (list(, $products_id) = each($products)) {
	  if ((int)$products_id > 0) $products_string .= (tep_not_null($products_string) ? "', '" : '') . (int)$products_id;
	}

    $product_image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id in ('" . $products_string . "') and products_image <> ''");
    while ($product_image = tep_db_fetch_array($product_image_query)) {
//	  $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image = '" . tep_db_input($product_image['products_image']) . "' and products_id not in ('" . $products_string . "')");
//	  $duplicate_image = tep_db_fetch_array($duplicate_image_query);

//	  if ($duplicate_image['total'] < 2) {
		@unlink(DIR_FS_CATALOG_IMAGES_BIG . $product_image['products_image']);
		@unlink(DIR_FS_CATALOG_IMAGES_MIDDLE . $product_image['products_image']);
		@unlink(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $product_image['products_image']);
//	  }
	}

    $product_images_query = tep_db_query("select products_images_image from " . TABLE_PRODUCTS_IMAGES . " where products_id in ('" . $products_string . "')");
    while ($product_images = tep_db_fetch_array($product_images_query)) {
	  if (tep_not_null($product_images['products_images_image'])) {
		@unlink(DIR_FS_CATALOG_IMAGES_BIG . $product_images['products_images_image']);
		@unlink(DIR_FS_CATALOG_IMAGES . $product_images['products_images_image']);
	  }
	}

    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_LINKED . " where products_id in ('" . $products_string . "') or linked_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_TO_INFORMATION . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_TO_MODELS . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_TO_SHOPS . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_PRODUCTS_VIEWED . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_REVIEWS . " where products_id in ('" . $products_string . "')");
    tep_db_query("delete from " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " where products_id in ('" . $products_string . "')");

	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_style = 'product' and content_id in ('" . $products_string . "')");
	  tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'product' and content_id in ('" . $products_string . "')");
	  tep_db_query("delete from " . TABLE_PRODUCTS . " where products_id in ('" . $products_string . "')");
	  tep_db_query("delete from " . TABLE_PRODUCTS_INFO . " where products_id in ('" . $products_string . "')");
	  tep_db_query("delete from " . TABLE_SPECIALS . " where products_id in ('" . $products_string . "')");
	  if (tep_db_table_exists($shops['shops_database'], TABLE_TEMP_PRODUCTS)) {
		tep_db_query("delete from " . TABLE_TEMP_PRODUCTS . " where products_id in ('" . $products_string . "')");
	  }
	  if (tep_db_table_exists($shops['shops_database'], TABLE_TEMP_PRODUCTS_INFO)) {
		tep_db_query("delete from " . TABLE_TEMP_PRODUCTS_INFO . " where products_id in ('" . $products_string . "')");
	  }
	}
	tep_db_select_db(DB_DATABASE);
  }

  function tep_remove_manufacturer($manufacturers_id) {
	tep_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
	tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
	tep_db_query("delete from " . TABLE_BLOCKS . " where content_id = '" . (int)$manufacturers_id . "' and blocks_style = 'manufacturer'");
	tep_db_query("delete from " . TABLE_METATAGS . " where content_id = '" . (int)$manufacturers_id . "' and content_type = 'manufacturer'");
  }

  function tep_remove_serie($series_id) {
	tep_db_query("delete from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "'");
	tep_db_query("delete from " . TABLE_BLOCKS . " where content_id = '" . (int)$series_id . "' and blocks_style = 'serie'");
	tep_db_query("delete from " . TABLE_METATAGS . " where content_id = '" . (int)$series_id . "' and content_type = 'serie'");
  }

  function tep_remove_product_type($products_types_id) {
	tep_db_query("delete from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "'");
	tep_db_query("delete from " . TABLE_BLOCKS . " where content_id = '" . (int)$products_types_id . "' and blocks_style = 'type'");
	tep_db_query("delete from " . TABLE_METATAGS . " where content_id = '" . (int)$products_types_id . "' and content_type = 'type'");
  }

  function tep_remove_board_category($boards_categories_ids) {
	if (!is_array($boards_categories_ids)) $boards_categories = array($boards_categories_ids);
	else $boards_categories = $boards_categories_ids;

	$boards_categories_string = '';
	reset($boards_categories);
	while (list(, $boards_categories_id) = each($boards_categories)) {
	  if ((int)$boards_categories_id > 0) $boards_categories_string .= (tep_not_null($boards_categories_string) ? "', '" : '') . (int)$boards_categories_id;
	}

	$prev_file_query = tep_db_query("select image from " . TABLE_BOARDS_CATEGORIES . " where boards_categories_id in ('" . $boards_categories_string . "') and image <> ''");
	while ($prev_file = tep_db_fetch_array($prev_file_query)) {
	  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['image']);
	}
    tep_db_query("delete from " . TABLE_BOARDS_CATEGORIES . " where boards_categories_id in ('" . $boards_categories_string . "')");
    tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_style = 'boards_category' and content_id in ('" . $boards_categories_string . "')");
    tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'boards_category' and content_id in ('" . $boards_categories_string . "')");
    tep_db_query("update " . TABLE_BOARDS . " set boards_categories_id = '0' where boards_categories_id in ('" . $boards_categories_string . "')");
  }

  function tep_remove_board($boards_id) {
	$img_info_query = tep_db_query("select boards_image from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
	$img_info = tep_db_fetch_array($img_info_query);
	if (tep_not_null($img_info['boards_image'])) {
	  $image_dir = DIR_FS_CATALOG_IMAGES . 'boards/' . substr(sprintf('%09d', (int)$boards_id), 0, 6) . '/';
	  $boards_images = explode("\n", $img_info['boards_image']);
	  reset($boards_images);
	  while (list(, $image) = each($boards_images)) {
		if (tep_not_null($image)) {
		  @unlink($image_dir . $image);
		  @unlink($image_dir . 'thumbs/' . $image);
		  @unlink($image_dir . 'big/' . $image);
		}
	  }
	}

	tep_db_query("delete from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
	tep_db_query("delete from " . TABLE_BOARDS . " where parent_id = '" . (int)$boards_id . "'");
  }

  function tep_remove_order($order_id, $restock = false) {
    if ($restock == 'on') {
      $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($order = tep_db_fetch_array($order_query)) {
        tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int)$order['products_id'] . "'");
      }
    }

    tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_VIEWED . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "'");

    tep_db_query("delete from " . TABLE_ARCHIVE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ARCHIVE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ARCHIVE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ARCHIVE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "'");
    tep_db_query("delete from " . TABLE_ARCHIVE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "'");
  }

  function tep_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

  function tep_remove($source) {
    global $messageStack, $tep_remove_error;

    if (isset($tep_remove_error)) $tep_remove_error = false;

    if (is_dir($source)) {
      $dir = dir($source);
      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            tep_remove($source . '/' . $file);
          } else {
            $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
            $tep_remove_error = true;
          }
        }
      }
      $dir->close();

      if (is_writeable($source)) {
        rmdir($source);
      } else {
        $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
        $tep_remove_error = true;
      }
    } else {
      if (is_writeable($source)) {
        unlink($source);
      } else {
        $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
        $tep_remove_error = true;
      }
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

  function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
    // Instantiate a new mail object
    $message = new email(array('X-Mailer: ' . STORE_NAME));

    // Build the text version
    $text = strip_tags($email_text);
    if (EMAIL_USE_HTML == 'true') {
	  ob_start();
	  include(DIR_FS_CATALOG . 'images/mail/email_header.php');
	  echo trim($email_text);
	  include(DIR_FS_CATALOG . 'images/mail/email_footer.php');
	  $email_text_html = ob_get_clean();
	  $email_text_html = str_replace('<title></title>', '<title>' . $email_subject . '</title>', $email_text_html);
	  $message->add_html($email_text_html, $text);
    } else {
      $message->add_text($text);
    }

    // Send message
    $message->build_message();
    $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
  }

  function tep_get_tax_class_title($tax_class_id) {
    if ($tax_class_id == '0') {
      return TEXT_NONE;
    } else {
      $classes_query = tep_db_query("select tax_class_title from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_class_id . "'");
      $classes = tep_db_fetch_array($classes_query);

      return $classes['tax_class_title'];
    }
  }

  function tep_banner_image_extension() {
    if (function_exists('imagetypes')) {
      if (imagetypes() & IMG_PNG) {
        return 'png';
      } elseif (imagetypes() & IMG_JPG) {
        return 'jpg';
      } elseif (imagetypes() & IMG_GIF) {
        return 'gif';
      }
    } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
      return 'png';
    } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
      return 'jpg';
    } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
      return 'gif';
    }

    return false;
  }

////
// Wrapper function for round() for php3 compatibility
  function tep_round($value, $precision) {
    if (PHP_VERSION < 4) {
      $exp = pow(10, $precision);
      return round($value * $exp) / $exp;
    } else {
      return round($value, $precision);
    }
  }

////
// Add tax to a products price
  function tep_add_tax($price, $tax) {
    global $currencies;

    if (DISPLAY_PRICE_WITH_TAX == 'true') {
      return $price + tep_calculate_tax($price, $tax);
    } else {
      return $price;
    }
  }

// Calculates Tax rounding the result
  function tep_calculate_tax($price, $tax) {
    global $currencies;

    return tep_round($price * $tax / 100, 2);
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

    $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za ON tr.tax_zone_id = za.geo_zone_id left join " . TABLE_GEO_ZONES . " tz ON tz.geo_zone_id = tr.tax_zone_id where (za.zone_country_id IS NULL OR za.zone_country_id = '0' OR za.zone_country_id = '" . (int)$country_id . "') AND (za.zone_id IS NULL OR za.zone_id = '0' OR za.zone_id = '" . (int)$zone_id . "') AND tr.tax_class_id = '" . (int)$class_id . "' GROUP BY tr.tax_priority");
    if (tep_db_num_rows($tax_query)) {
      $tax_multiplier = 0;
      while ($tax = tep_db_fetch_array($tax_query)) {
        $tax_multiplier += $tax['tax_rate'];
      }
      return $tax_multiplier;
    } else {
      return 0;
    }
  }

////
// Returns the tax rate for a tax class
// TABLES: tax_rates
  function tep_get_tax_rate_value($class_id) {
    $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '" . (int)$class_id . "' group by tax_priority");
    if (tep_db_num_rows($tax_query)) {
      $tax_multiplier = 0;
      while ($tax = tep_db_fetch_array($tax_query)) {
        $tax_multiplier += $tax['tax_rate'];
      }
      return $tax_multiplier;
    } else {
      return 0;
    }
  }

  function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func($function, $parameter);
    } elseif (PHP_VERSION < 4) {
      return call_user_method($function, $object, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }

  function tep_get_zone_class_title($zone_class_id) {
    if ($zone_class_id == '0') {
      return TEXT_NONE;
    } else {
      $classes_query = tep_db_query("select geo_zone_name from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int)$zone_class_id . "'");
      $classes = tep_db_fetch_array($classes_query);

      return $classes['geo_zone_name'];
    }
  }

  function tep_cfg_pull_down_zone_classes($zone_class_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zone_class = tep_db_fetch_array($zone_class_query)) {
      $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
                                  'text' => $zone_class['geo_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }

  function tep_cfg_pull_down_order_statuses($order_status_id, $key = '') {
    global $languages_id;

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by sort_order");
    while ($statuses = tep_db_fetch_array($statuses_query)) {
      $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                'text' => $statuses['orders_status_name']);
    }

    return tep_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }

  function tep_cfg_pull_down_currencies($currency_code, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $currencies_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $currencies_query = tep_db_query("select code, title from " . TABLE_CURRENCIES . " where 1 order by title");
    while ($currencies = tep_db_fetch_array($currencies_query)) {
      $currencies_array[] = array('id' => $currencies['code'],
								  'text' => $currencies['title']);
    }

    return tep_draw_pull_down_menu($name, $currencies_array, $currency_code);
  }

  function tep_get_order_status_name($order_status_id, $language_id = '') {
    global $languages_id;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $languages_id;

    $status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int)$order_status_id . "' and language_id = '" . (int)$language_id . "'");
    $status = tep_db_fetch_array($status_query);

    return $status['orders_status_name'];
  }

////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!$seeded) {
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

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    if ((PHP_VERSION < "4.0.5") && is_array($from)) {
      return ereg_replace('(' . implode('|', $from) . ')', $to, $string);
    } else {
      return str_replace($from, $to, $string);
    }
  }

  function tep_string_to_int($string) {
    return (int)$string;
  }

////
// Parse and secure the cPath parameter values
  function tep_parse_category_path($cPath) {
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

  function tep_load_blocks($content_id, $content_type) {
	global $languages_id, $HTTP_POST_VARS, $PHP_SELF, $HTTP_GET_VARS;

	$templates_id = '';
	if ($content_type=='page') {
	  $templates = array(array('id' => '', 'text' => TEXT_CHOOSE));
	  $templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by sort_order, default_status desc, templates_id");
	  while ($templates_array = tep_db_fetch_array($templates_query)) {
		$templates[] = array('id' => $templates_array['templates_id'], 'text' => $templates_array['templates_name']);
	  }

	  $template_info_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES_TO_CONTENT . " where content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "'");
	  $template_info = tep_db_fetch_array($template_info_query);

	  $templates_id = isset($HTTP_POST_VARS['templates_id']) ? $HTTP_POST_VARS['templates_id'] : $template_info['templates_id'];
	}

	$languages = tep_get_languages();

	$metatags = array('metatags_page_title' => 'Заголовок окна', 'metatags_title' => 'Заголовок страницы', 'metatags_keywords' => 'Ключевые слова (meta-keywords)', 'metatags_description' => 'Описание (meta-description)');
?>
		<div id="blocks">
		<table border="0" width="100%" cellspacing="0" cellpadding="1">
<?php
	if ($content_type=='page') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main" style="width: 250px;"><?php echo TEXT_CHOOSE_TEMPLATE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&#160;' . tep_draw_pull_down_menu('templates_id', $templates, $templates_id); ?></td>
          </tr>
<?php
	}
	reset($metatags);
	while (list($metatag, $title) = each($metatags)) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		$value_query = tep_db_query("select " . tep_db_input($metatag) . " as value from " . TABLE_METATAGS . " where content_id = '" . (int)$content_id . "' and content_type = '" . tep_db_input($content_type) . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		$value = tep_db_fetch_array($value_query);
?>
          <tr valign="top">
            <td class="main" style="width: 250px;"><?php
		if ($i == 0) {
		  echo $title . ':';
		}
?></td>
            <td class="main"><?php
		echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
		$field_name = $metatag . '[' . $languages[$i]['id'] . ']';
		$field_value = isset($HTTP_POST_VARS[$metatag][$languages[$i]['id']]) ? $HTTP_POST_VARS[$metatag][$languages[$i]['id']] : $value['value'];
		$field_value = str_replace('\\\"', '"', $field_value);
		$field_value = str_replace('\"', '"', $field_value);
		$field_value = str_replace("\\\'", "\'", $field_value);
		$field_value = str_replace('src="/', 'src="' . HTTP_SERVER . '/', $field_value);
		if ($metatag=='metatags_description') {
		  echo tep_draw_textarea_field($field_name, 'soft', '55', '5', $field_value);
		} else {
		  echo tep_draw_input_field($field_name, $field_value, 'size="55"');
		}
?></td>
          </tr>
<?php
	  }
	}
	echo '</table></div>';
  }

  function tep_update_blocks($content_id, $content_type, $database = '') {
	global $HTTP_POST_VARS;

	if ($database=='') $database = DB_DATABASE;

	$metatags = array('metatags_page_title' => 'Заголовок окна', 'metatags_title' => 'Заголовок страницы', 'metatags_keywords' => 'Ключевые слова (meta-keywords)', 'metatags_description' => 'Описание (meta-description)');

	if (isset($HTTP_POST_VARS['templates_id'])) {
	  tep_db_query("replace into " . $database . "." . TABLE_TEMPLATES_TO_CONTENT . " (templates_id, content_type, content_id) values ('" . (int)$HTTP_POST_VARS['templates_id'] . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");
	}

	reset($metatags);
	while (list($metatag) = each($metatags)) {
	  reset($HTTP_POST_VARS['metatags_page_title']);
	  while (list($lang_id) = each($HTTP_POST_VARS['metatags_page_title'])) {
		$metatags_id = 0;
		$check_query = tep_db_query("select metatags_id from " . $database . "." . TABLE_METATAGS . " where content_id = '" . (int)$content_id . "' and content_type = '" . tep_db_input($content_type) . "' and language_id = '" . (int)$lang_id . "' limit 1");
		$check = tep_db_fetch_array($check_query);
		$metatags_id = $check['metatags_id'];

		$description = $HTTP_POST_VARS[$metatag][$lang_id];
		$description = str_replace('\\\"', '"', $description);
		$description = str_replace('\"', '"', $description);
		$description = str_replace("\\\'", "\'", $description);
		$description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		$description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		$description = tep_db_input($description);
		if ((int)$metatags_id > 0) {
		  $lang_check_query = tep_db_query("select count(*) as total from " . $database . "." . TABLE_METATAGS . " where metatags_id = '" . (int)$metatags_id . "' and language_id = '" . (int)$lang_id . "'");
		  $lang_check = tep_db_fetch_array($lang_check_query);
		  if ($lang_check['total'] > 0) {
			$sql = "update " . $database . "." . TABLE_METATAGS . " set " . tep_db_input($metatag) . " = '" . $description . "' where metatags_id = '" . (int)$metatags_id . "' and language_id = '" . (int)$lang_id . "'";
		  } else {
			$sql = "insert into " . $database . "." . TABLE_METATAGS . " (content_id, content_type, " . tep_db_input($metatag) . ", language_id) values ('" . (int)$content_id . "', '" . tep_db_input($content_type) . "', '" . $description . "', '" . (int)$lang_id . "')";
		  }
		} else {
		  $sql = "insert into " . $database . "." . TABLE_METATAGS . " (content_id, content_type, " . tep_db_input($metatag) . ", language_id) values ('" . (int)$content_id . "', '" . tep_db_input($content_type) . "', '" . $description . "', '" . (int)$lang_id . "')";
		}
		tep_db_query($sql);
	  }
	}
//	tep_db_query("delete from " . $database . "." . TABLE_METATAGS . " where (" . implode(" = '' and ", array_keys($metatags)) . " = '')");
  }

  function tep_get_files($dir, $extensions = '') {
	if (substr($dir, -1)!='/') $dir .= '/';
	$exts = array();
	if (!is_array($extensions)) {
	  if (tep_not_null($extensions)) $exts[] = $extensions;
	} else {
	  $exts = $extensions;
	}
    $exts = array_map('strtolower', $exts);
	$files = array();
	if (is_dir($dir)) {
	  $h = opendir($dir);
	  while ($file = readdir($h)) {
		if (!is_dir($dir . $file)) {
		  if (sizeof($exts) > 0) {
			$ext = strtolower(substr($file, strrpos($file, '.')));
			if (in_array($ext, $exts)) {
			  $files[] = $file;
			}
		  } else {
			$files[] = $file;
		  }
		}
	  }
	  closedir($h);
	}
	sort($files);
	return $files;
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

  function tep_update_shops_prices($shop_id = '', $new_currency_code = '', $table = 'real') {
	global $currencies;

	$temp_currencies = array();
	$filename_currencies_gz = UPLOAD_DIR . 'CSV/kurs.csv.gz';
	$filename_currencies = str_replace('.gz', '', $filename_currencies_gz);
	if (file_exists($filename_currencies_gz)) {
	  $gz = @gzopen($filename_currencies_gz, 'r');
	  $ff = @fopen($filename_currencies, 'w');
	  if ($gz && $ff) {
		while ($string = gzgets($gz, 1024)) {
		  fwrite($ff, $string);
		}
		fclose($ff);
		gzclose($gz);
	  } elseif (file_exists($filename_currencies)) {
		@unlink($filename_currencies);
	  }
	}
	if (file_exists($filename_currencies)) {
	  $fp = fopen($filename_currencies, 'r');
	  while ((list($currency_code, $currency_value) = fgetcsv($fp, 64, ';')) !== FALSE) {
		if ((float)$currency_value > 0) {
		  $temp_currencies[$currency_code] = str_replace(',', '.', trim($currency_value));
		}
	  }
	  fclose($fp);
	  unlink($filename_currencies);
	}

	if ($table=='real') $temp_currencies = array();

	if (sizeof($temp_currencies)==0) {
	  reset($currencies);
	  while (list($currency_code, $currency_info) = each($currencies)) {
		$temp_currencies[$currency_code] = $currency_info['value'];
	  }
	}

	$table_products = ($table=='temp' ? TABLE_TEMP_PRODUCTS : TABLE_PRODUCTS);
	$table_products_info = ($table=='temp' ? TABLE_TEMP_PRODUCTS_INFO : TABLE_PRODUCTS_INFO);
	$table_specials = ($table=='temp' ? TABLE_TEMP_SPECIALS : TABLE_SPECIALS);

	$shops_query = tep_db_query("select shops_id, shops_database, shops_price_equation, shops_shipping_days, shops_currency, shops_default_status from " . TABLE_SHOPS . " where shops_database <> ''" . ($shop_id>0 ? " and shops_id = '" . (int)$shop_id . "'" : "") . (tep_not_null($new_currency_code) ? " and shops_currency like '" . tep_db_input($new_currency_code) . "%'" : ""));
	while ($shops = tep_db_fetch_array($shops_query)) {
	  list($shop_currency) = explode(',', $shops['shops_currency']);

	  $shop_equation = $shops['shops_price_equation'];
	  $database = $shops['shops_database'];
	  tep_db_select_db($database);

	  if (tep_not_null($shop_equation)) {
		if ($database=='setbook_by') {
		  $new_value = "round((" . sprintf(str_replace('%s', '%s * ' . $currencies->get_value($shop_currency), $shop_equation), 'if(products_another_cost>0, products_another_cost, products_cost)') . ") / 50, " . (int)$currencies->get_decimal_places($shop_currency) . ") * 50";
		} elseif ($database=='setbook_ru' || $database=='setbook_ua') {
		  $new_value = "round((" . sprintf(str_replace('%s', '%s * ' . $currencies->get_value($shop_currency), $shop_equation), 'products_cost') . "), " . (int)$currencies->get_decimal_places($shop_currency) . ")";
		} else {
		  $new_value = "round((" . sprintf(str_replace('%s', '%s * ' . $currencies->get_value($shop_currency), $shop_equation), 'if(products_another_cost>0, products_another_cost, products_cost)') . "), " . (int)$currencies->get_decimal_places($shop_currency) . ")";
		}
		if ($table=='temp') {
		  if (in_array($shop_currency, array('USD', 'EUR'))) {
			tep_db_query("update " . $table_products . " set products_cost = products_cost * 1.5 + 3 / " . $currencies->get_value($shop_currency) . " where products_types_id = '2' and products_periodicity > '0'");
		  }
		}
		tep_db_query("update " . $table_products . " set products_price = if((products_filename is not null), products_cost, (" . $new_value . " / " . $currencies->get_value($shop_currency) . ")), products_last_modified = now() where products_price > '0'");
	  }

	  if ($shops['shops_shipping_days'] > 0 && $shops['shops_default_status']==0) {
		if (tep_db_table_exists(DB_DATABASE, $table_products)) {
		  tep_db_query("update " . $database . "." . $table_products . " p1, " . DB_DATABASE . "." . $table_products . " p2 set p1.products_available_in = (p2.products_available_in + " . $shops['shops_shipping_days'] . ") where p1.products_id = p2.products_id");
		} else {
		  tep_db_query("update " . $database . "." . $table_products . " p1, " . DB_DATABASE . "." . str_replace('temp_', '', $table_products) . " p2 set p1.products_available_in = (p2.products_available_in + " . $shops['shops_shipping_days'] . ") where p1.products_id = p2.products_id");
		}
	  }

	  if ($database=='setbook_ua' && file_exists(UPLOAD_DIR . 'CSV/' . $shops['shops_id'] . '_Price.csv.gz')) {
		tep_db_query("update " . $table_products . " set products_status = '0' where manufacturers_id in (select distinct manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_name like '%эксмо%' or manufacturers_name like '%яуза%' or manufacturers_name like '%астрель%' or manufacturers_name like '%аст,%' or manufacturers_name like '%аст %' or manufacturers_name like '%аст.%' or manufacturers_name like '%аст-%' or manufacturers_name like '%аст&quot;%' or manufacturers_name like '%аст/%' or manufacturers_name like '%аст:%' or manufacturers_name like '% аст' or manufacturers_name like '%:аст' or manufacturers_name like '%-аст' or manufacturers_name = 'аст' or manufacturers_name like '%рипол%' or manufacturers_name like '%азбук%' or manufacturers_name like '%эгмонт%' or manufacturers_name like '%махао%' or manufacturers_name like '%ттик%' or manufacturers_name like '%ладис%') or products_id in (select products_id from " . $table_products_info . " where products_types_id = '1' and products_status = '1' and (products_name like 'CD%' or products_name like 'DVD%' or products_name like '%Электронный%учебник%' or products_model like '978-5-699-%' or products_model like '5-699-%'))");

		$filename_gz = UPLOAD_DIR . 'CSV/' . $shops['shops_id'] . '_Price.csv.gz';
		$filename = str_replace('.gz', '', $filename_gz);
		if (file_exists($filename_gz)) {
		  $gz = @gzopen($filename_gz, 'r');
		  $ff = @fopen($filename, 'w');
		  if ($gz && $ff) {
			while ($string = gzgets($gz, 1024)) {
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  } elseif (file_exists($filename)) {
			@unlink($filename);
		  }
		}
		if (file_exists($filename)) {
		  $fp = fopen($filename, 'r');
		  while (($cell = fgetcsv($fp, 64, ';')) !== FALSE) {
			list($products_code, $products_types_id, $products_price, $another_price, $specials_price, $purchase_price, $products_available_in) = $cell;
//			IDTovar	TypeTovar	Cena	CenaRekom	Rasprodaga	CenaZakup	SrokPostavki

			if ((int)$products_code > 0) {
			  if ($products_price=='-1') {
				$products_price = 0;
				$another_price = 0;
				$specials_price = 0;
				$purchase_price = 0;
				$status = 0;
			  } else {
				$products_price = str_replace(',', '.', $products_price/$temp_currencies[$shop_currency]);
				$another_price = str_replace(',', '.', $another_price/$temp_currencies[$shop_currency]);
				$specials_price = str_replace(',', '.', $specials_price/$temp_currencies[$shop_currency]);
				$purchase_price = str_replace(',', '.', $purchase_price/$temp_currencies[$shop_currency]);
				$status = 1;
			  }

			  $product_info_query = tep_db_query("select products_id from " . $table_products . " where products_code = 'bbk" . sprintf('%010d', (int)$products_code) . "' and products_types_id = '" . (int)$products_types_id . "'");
			  $product_info = tep_db_fetch_array($product_info_query);

			  tep_db_query("update " . $table_products . " set products_cost = '" . tep_db_input($products_price) . "', products_another_cost = '" . tep_db_input($another_price) . "', products_purchase_cost = '" . tep_db_input($purchase_price) . "', products_price = '" . tep_db_input($products_price) . "', products_available_in = '" . (int)$products_available_in . "', products_last_modified = now(), products_status = '" . (int)$status . "' where products_id = '" . (int)$product_info['products_id'] . "'");
			  if ($table=='real') {
				tep_db_query("update " . $table_products_info . " set products_price = '" . tep_db_input($products_price) . "', products_available_in = '" . (int)$products_available_in . "', products_last_modified = now(), products_status = '" . (int)$status . "' where products_id = '" . (int)$product_info['products_id'] . "'");
			  }
			  if ($specials_price>0 && $products_price>0 && $specials_price<$products_price) {
				tep_db_query("insert into " . $table_specials . " (specials_id, specials_types_id, language_id, products_id, specials_date_added, specials_new_products_price) select max(specials_id)+1, '5', '" . (int)$languages_id . "', '" . (int)$product_info['products_id'] . "', now(), '" . $specials_price . "' from " . $table_specials . "");
			  }
			}
		  }
		  fclose($fp);
		  unlink($filename);
		}

		tep_db_query("update " . $table_products . " p1, " . DB_DATABASE . "." . $table_products . " p2 set p1.products_status = '0' where p1.products_id = p2.products_id and p2.products_status = '0'");
	  }
	}

	tep_db_select_db(DB_DATABASE);
  }

  function tep_update_all_shops($products_types_id = '') {
	global $temp_tables, $in_shops, $currencies, $languages_id;

	$products_types_default_status = ($products_types_id==1 ? 1 : 0);

	if (!is_array($in_shops)) $in_shops = array();

	$temp_currencies = array();
	$filename_currencies_gz = UPLOAD_DIR . 'CSV/kurs.csv.gz';
	$filename_currencies = str_replace('.gz', '', $filename_currencies_gz);
	if (file_exists($filename_currencies_gz)) {
	  $gz = @gzopen($filename_currencies_gz, 'r');
	  $ff = @fopen($filename_currencies, 'w');
	  if ($gz && $ff) {
		while ($string = gzgets($gz, 1024)) {
		  fwrite($ff, $string);
		}
		fclose($ff);
		gzclose($gz);
	  } elseif (file_exists($filename_currencies)) {
		@unlink($filename_currencies);
	  }
	}
	if (file_exists($filename_currencies)) {
	  $fp = fopen($filename_currencies, 'r');
	  while ((list($currency_code, $currency_value) = fgetcsv($fp, 64, ';')) !== FALSE) {
		if ((float)$currency_value > 0) {
		  $temp_currencies[$currency_code] = str_replace(',', '.', trim($currency_value));
		}
	  }
	  fclose($fp);
	  unlink($filename_currencies);
	}
	if (sizeof($temp_currencies)==0) {
	  reset($currencies);
	  while (list($currency_code, $currency_info) = each($currencies)) {
		$temp_currencies[$currency_code] = $currency_info['value'];
	  }
	}

	$deleted_products = array();
	$filename_deleted_gz = UPLOAD_DIR . 'CSV/Deleted.csv.gz';
	$filename_deleted = str_replace('.gz', '', $filename_deleted_gz);
	if (file_exists($filename_deleted_gz)) {
	  $gz = @gzopen($filename_deleted_gz, 'r');
	  $ff = @fopen($filename_deleted, 'w');
	  if ($gz && $ff) {
		while ($string = gzgets($gz, 1024)) {
		  fwrite($ff, $string);
		}
		fclose($ff);
		gzclose($gz);
	  } elseif (file_exists($filename_deleted)) {
		@unlink($filename_deleted);
	  }
	}
	if (file_exists($filename_deleted)) {
	  $fp = fopen($filename_deleted, 'r');
	  while ((list($deleted_code, $deleted_type_id) = fgetcsv($fp, 64, ';')) !== FALSE) {
		if ((int)$deleted_code > 0) {
		  $deleted_product_info_query = tep_db_query("select products_id from " . TABLE_TEMP_PRODUCTS . " where products_code = 'bbk" . sprintf('%010d', $deleted_code) . "' and products_types_id = '" . (int)$deleted_type_id . "'");
		  $deleted_product_info = tep_db_fetch_array($deleted_product_info_query);
		  $deleted_products[] = $deleted_product_info['products_id'];
		}
	  }
	  fclose($fp);
	  unlink($filename_deleted);
	}

	$shops_query = tep_db_query("select * from " . TABLE_SHOPS . " where shops_database <> ''" . (sizeof($in_shops)>0 ? " and (shops_default_status = '1' or shops_id in ('" . implode("', '", $in_shops) . "'))" : "") . " order by shops_default_status");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  list($shop_currency) = explode(',', $shops['shops_currency']);
	  $shop_db = tep_db_input($shops['shops_database']);
	  if (tep_not_null($shop_db)) {
		tep_db_select_db($shop_db);
		reset($temp_tables);
		while (list($step, $temp_table) = each($temp_tables)) {
		  if ($shops['shops_default_status']=='0') {
			tep_db_query("drop table if exists " . $shop_db . ".temp_" . $temp_table);
			if (tep_db_table_exists(DB_DATABASE, 'temp_' . $temp_table)) {
			  tep_db_query("create table " . $shop_db . ".temp_" . $temp_table . " like " . DB_DATABASE  . ".temp_" . $temp_table);
			  tep_db_query("insert into " . $shop_db . ".temp_" . $temp_table . " select * from " . DB_DATABASE  . ".temp_" . $temp_table);
			} else {
			  tep_db_query("create table " . $shop_db . "." . $temp_table . " like " . DB_DATABASE  . "." . $temp_table);
			  tep_db_query("insert into " . $shop_db . "." . $temp_table . " select * from " . DB_DATABASE  . "." . $temp_table);
			}
		  }
		}

		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_status = '1' where products_types_id > '1' and products_id in (select products_id from " . DB_DATABASE . "." . TABLE_PRODUCTS_TO_SHOPS . " where shops_id = '" . (int)$shops['shops_id'] . "' and products_status = '1')");
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_status = '0' where products_id in (select products_id from " . DB_DATABASE . "." . TABLE_PRODUCTS_TO_SHOPS . " where shops_id = '" . (int)$shops['shops_id'] . "' and products_status = '0')");

/*
		if ((int)$products_types_default_status==0) {
		  $unused_categories_array = array();
		  $unused_categories_query = tep_db_query("select categories_id from " . $shop_db . "." . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types_id . "' and categories_status = '1'");
	 	  while ($unused_categories = tep_db_fetch_array($unused_categories_query)) {
			$subcategories_array = $unused_categories['categories_id'];
			tep_get_subcategories($subcategories_array, $unused_categories['categories_id']);
			$products_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " p where p.products_types_id = '" . (int)$products_types_id . "' and p2c.products_id = p.products_id and p.products_status = '1'");
			$products_check = tep_db_fetch_array($products_check_query);
			if ($products_check['total']==0) $unused_categories_array[] = (int)$unused_categories['categories_id'];
		  }
		  if (sizeof($unused_categories_array) > 0) tep_db_query("update " . $shop_db . "." . TABLE_CATEGORIES . " set categories_status = '0' where categories_id in ('" . implode("', '", $unused_categories_array) . "'))");
		}
*/

		$unused_categories_array = array();
		$unused_categories_query = tep_db_query("select categories_id from " . $shop_db . "." . TABLE_CATEGORIES . " where categories_status = '0' and products_types_id in (select products_types_id from " . $shop_db . "." . TABLE_PRODUCTS_TYPES . " where products_types_status = '1')");
	 	while ($unused_categories = tep_db_fetch_array($unused_categories_query)) {
		  $unused_categories_array[] = $unused_categories['categories_id'];
		  tep_get_subcategories($unused_categories_array, $unused_categories['categories_id']);
		}
		if (sizeof($unused_categories_array) > 0) tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_status = '0' where products_id in (select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $unused_categories_array) . "'))");

		tep_update_shops_prices($shops['shops_id'], '', 'temp');

		reset($deleted_products);
		while (list(, $deleted_product_id) = each($deleted_products)) {
		  tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_status = '0' where products_id = '" . (int)$deleted_product_id . "'");
		}

		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_listing_status = '0', products_xml_status = '0' where products_price = '0'");
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_price = '0' where products_listing_status = '0'");

		if ($shops['shops_default_status']=='0') {
		  tep_db_query("delete from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_types_id = '5'");
		} else {
		  tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_available_in = '1' where products_id in (select products_id from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_types_id = '5' and status = '1')");
		}
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " set products_available_in = '0' where products_filename is not null");
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_SPECIALS . " s, " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " p set s.status = p.products_status, s.products_image_exists = p.products_image_exists, s.specials_first_page = if((p.products_image_exists and p.products_listing_status), '1', '0') where s.products_id = p.products_id");
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_SPECIALS . " set specials_first_page = if((products_image_exists and status), '1', '0') where specials_types_id = '4'");

		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " p, " . $shop_db . "." . TABLE_TEMP_PRODUCTS_INFO . " pi set pi.products_code = p.products_code, pi.products_model = p.products_model, pi.products_image = p.products_image, pi.products_filename = p.products_filename, pi.products_price = p.products_price, pi.products_last_modified = p.products_last_modified, pi.products_available_in = p.products_available_in, pi.products_weight = p.products_weight, pi.products_year = p.products_year, pi.products_pages_count = p.products_pages_count, pi.products_copies = p.products_copies, pi.products_status = p.products_status, pi.products_listing_status = p.products_listing_status, pi.products_types_id = p.products_types_id where pi.products_id = p.products_id");

//		tep_db_query("delete from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where status = '1' and now() >= expires_date and expires_date > 0");

		$specials_query = tep_db_query("select specials_id, products_id, specials_new_products_price from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_new_products_price > '0'");
		while ($specials = tep_db_fetch_array($specials_query)) {
		  $product_info_query = tep_db_query("select products_price, products_status from " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if ($product_info['products_price'] <= $specials['specials_new_products_price'] || $product_info['products_status']=='0') {
			tep_db_query("delete from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_id = '" . (int)$specials['specials_id'] . "'");
		  }
		}

		$specials_query = tep_db_query("select specials_id, products_id from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_types_id = '4'");
		while ($specials = tep_db_fetch_array($specials_query)) {
		  $product_info_query = tep_db_query("select products_listing_status, products_price from " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if ($product_info['products_price'] > '0' && $product_info['products_listing_status'] == '1') {
			tep_db_query("delete from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where specials_id = '" . (int)$specials['specials_id'] . "'");
		  }
		}

		// сортировка по умолчанию (сначала спецпредложения с картинками, потом спецпредложения без картинок, потом книги с картинками, потом все остальное)
		$max_specials_date_query = tep_db_query("select max(specials_date_added) as specials_date_added from " . $shop_db . "." . TABLE_TEMP_SPECIALS . " where status = '1'");
		$max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
		$max_specials_date = strtotime($max_specials_date_row['specials_date_added']);
		$min_specials_date_added = date('Y-m-d', $max_specials_date-60*60*24*7);
		tep_db_query("update " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " p left join " . $shop_db . "." . TABLE_TEMP_SPECIALS . " s on (s.products_id = p.products_id and s.specials_types_id in ('1', '2') and s.specials_date_added >= '" . tep_db_input($min_specials_date_added) . "') set p.sort_order = (if(p.products_listing_status=1, 8, 0) + if(s.specials_types_id, if(s.specials_types_id=1, 4, if(s.specials_types_id=2, 3, 0)), 0) + if(p.products_image_exists=1, 2, 0))");

		reset($temp_tables);
		while (list($step, $temp_table) = each($temp_tables)) {
		  if (tep_db_table_exists($shop_db, 'temp_' . $temp_table)) {
			if ($temp_table==TABLE_PRODUCTS && $products_types_default_status==1) {
/*
			  $basket_products_query = tep_db_query("select products_id from " . TABLE_CUSTOMERS_BASKET . " where shops_id = '" . (int)$shops['shops_id'] . "'");
			  while ($basket_products = tep_db_fetch_array($basket_products_query)) {
				$check_old_status_query = tep_db_query("slect products_status, products_listing_status from " . $shop_db . "." . TABLE_PRODUCTS . " where products_id = '" . (int)$basket_products['products_id'] . "'");
				$check_old_status = tep_db_fetch_array($check_old_status_query);

				$check_new_status_query = tep_db_query("slect products_status, products_listing_status from " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " where products_id = '" . (int)$basket_products['products_id'] . "'");
				$check_new_status = tep_db_fetch_array($check_new_status_query);

				if ($check_new_status['products_status'] == '0') {
				  // удаляем из корзин товары, которых нет на сайте
				  tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where shops_id = '" . (int)$shops['shops_id'] . "' and products_id = '" . (int)$basket_products['products_id'] . "'");
				} elseif ($check_old_status['products_listing_status'] == '1' && $check_new_status['products_listing_status'] == '0') {
				  // переносим из корзин в отложенные товары
				  tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '1', customers_basket_type = 'postpone' where shops_id = '" . (int)$shops['shops_id'] . "' and products_id = '" . (int)$basket_products['products_id'] . "'");
				}
			  }
*/

			  $lang_id = $languages_id;
			  if ($shop_db=='setbook_org' || $shop_db=='easternowl' || $shop_db=='insellbooks') $lang_id = 1;

			  $shop_name_info_query = tep_db_query("select configuration_value from " . $shop_db . "." . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
			  $shop_name_info = tep_db_fetch_array($shop_name_info_query);
			  $shop_name = $shop_name_info['configuration_value'];

			  $shop_email_info_query = tep_db_query("select configuration_value from " . $shop_db . "." . TABLE_CONFIGURATION . " where configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
			  $shop_email_info = tep_db_fetch_array($shop_email_info_query);
			  $shop_email = $shop_email_info['configuration_value'];

			  $shop_phone_info_query = tep_db_query("select configuration_value from " . $shop_db . "." . TABLE_CONFIGURATION . " where configuration_key = 'STORE_OWNER_PHONE_NUMBER'");
			  $shop_phone_info = tep_db_fetch_array($shop_phone_info_query);
			  $shop_phone = $shop_phone_info['configuration_value'];

			  $notify_products_query = tep_db_query("select customers_basket_id, products_id, customers_id, customers_basket_notify_url, customers_basket_notify from " . TABLE_CUSTOMERS_BASKET . " where shops_id = '" . (int)$shops['shops_id'] . "' and customers_basket_notify > '0'");
			  while ($notify_products = tep_db_fetch_array($notify_products_query)) {
				$new_info_query = tep_db_query("select products_status, products_listing_status, products_price, authors_id from " . $shop_db . "." . TABLE_TEMP_PRODUCTS . " where products_id = '" . (int)$notify_products['products_id'] . "'");
				$new_info = tep_db_fetch_array($new_info_query);

				$old_info_query = tep_db_query("select products_status, products_listing_status, products_price, authors_id from " . $shop_db . "." . TABLE_PRODUCTS . " where products_id = '" . (int)$notify_products['products_id'] . "'");
				$old_info = tep_db_fetch_array($old_info_query);

				$product_name_info_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$notify_products['products_id'] . "' and language_id = '" . (int)$lang_id . "'");
				$product_name_info = tep_db_fetch_array($product_name_info_query);
				if (!is_array($product_name_info)) $product_name_info = array();

				$author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$new_info['authors_id'] . "' and language_id = '" . (int)$lang_id . "'");
				$author_info = tep_db_fetch_array($author_info_query);
				if (!is_array($author_info)) $author_info = array();

				if ($lang_id==1) {
				  $product_email_name = $product_name_info['products_name'] . (tep_not_null($author_info['authors_name']) ? ' by ' . $author_info['authors_name'] : '');
				} else {
				  $product_email_name = (tep_not_null($author_info['authors_name']) ? $author_info['authors_name'] . ': ' : '') . $product_name_info['products_name'];
				}

				$new_status = $new_info['products_listing_status'];
				$new_price = $new_info['products_price'];

				$old_status = $old_info['products_listing_status'];
				$old_price = $old_info['products_price'];

				$customer_info_query = tep_db_query("select customers_firstname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$notify_products['customers_id'] . "'");
				$customer_info = tep_db_fetch_array($customer_info_query);

				$notification_body = '';
				$notification_subject = '';
				$notification_warning = '';
				if ($notify_products['customers_basket_notify']=='1') {
				  // о появлении в продаже
				  if ($new_status == '1') {
					if ($lang_id==1) {
					  $notification_body = EMAIL_NOTIFICATION_BODY_EN_1;
					  $notification_subject = EMAIL_NOTIFICATION_SUBJECT_EN_1;
					  $notification_warning = EMAIL_NOTIFICATION_WARNING_EN_1;
					} else {
					  $notification_body = EMAIL_NOTIFICATION_BODY_1;
					  $notification_subject = EMAIL_NOTIFICATION_SUBJECT_1;
					  $notification_warning = EMAIL_NOTIFICATION_WARNING_1;
					}
				  }
				} elseif ($notify_products['customers_basket_notify']=='2') {
				  // о снижении цены
				  if ($new_price > 0 && $new_price < $old_price) {
					if ($lang_id==1) {
					  $notification_body = EMAIL_NOTIFICATION_BODY_EN_2;
					  $notification_subject = EMAIL_NOTIFICATION_SUBJECT_EN_2;
					  $notification_warning = EMAIL_NOTIFICATION_WARNING_EN_2;
					} else {
					  $notification_body = EMAIL_NOTIFICATION_BODY_2;
					  $notification_subject = EMAIL_NOTIFICATION_SUBJECT_2;
					  $notification_warning = EMAIL_NOTIFICATION_WARNING_2;
					}
				  }
				}
				if (tep_not_null($notification_body)) {
				  $email_notification_body = str_replace('{{product_link}}', $notify_products['customers_basket_notify_url'], sprintf($notification_body, $customer_info['customers_firstname'], $product_email_name)) . "\n\n" . EMAIL_NOTIFICATION_SEPARATOR . "\n" .  sprintf($notification_warning, $shop_name);
				  $message = new email(array('X-Mailer: ' . $shop_name));
				  $text = strip_tags($email_notification_body);
				  if ($shops['shops_email_use_html'] < 1) {
					$message->add_text($text);
				  } else {
					ob_start();
					include(DIR_FS_CATALOG . 'images/mail/email_header_1.php');
					echo trim($email_notification_body);
					include(DIR_FS_CATALOG . 'images/mail/email_footer_1.php');
					$email_text_html = ob_get_clean();
					$email_text_html = str_replace(array('<title></title>', '{{HTTP_SERVER}}', '{{STORE_NAME}}', '{{STORE_OWNER_PHONE_NUMBER}}'), array('<title>' . sprintf($notification_subject, $shop_name) . '</title>', $shops['shops_url'], $shop_name, $shop_phone), $email_text_html);
					$message->add_html($email_text_html, $text);
				  }
				  $message->build_message();
				  $message->send($customer_info['customers_firstname'], $customer_info['customers_email_address'], $shop_name, $shop_email, sprintf($notification_subject, $shop_name));
				  tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_notify = '0', customers_basket_notify_url = null where customers_basket_id = '" . (int)$notify_products['customers_basket_id'] . "'");
				}
			  }
			}

			tep_db_query("drop table " . $shop_db . "." . $temp_table . "");
			tep_db_query("alter table " . $shop_db . ".temp_" . $temp_table . " rename as " . $shop_db . "." . $temp_table . "");

			if ($shop_db=='setbook_ua') {
			  tep_db_query("update " . $shop_db . "." . TABLE_CURRENCIES . " set value = '" . tep_db_input($temp_currencies[$shop_currency]) . "', last_updated = now() where code = '" . tep_db_input($shop_currency) . "'");
			}
		  }
		}

		if ($shops['shops_default_status']=='1') {
		  tep_db_query("delete from " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . "");
		  tep_db_query("delete from " . TABLE_SEARCH_KEYWORDS . "");

		  tep_db_query("update " . TABLE_SPECIALS_TYPES . " set specials_last_modified = now()");
		}

		if ($products_types_default_status==1) tep_db_query("update " . $shop_db . "." . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '1'");
		else tep_db_query("update " . $shop_db . "." . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id > '1'");

		if ($shop_db=='setbook_us' || $shop_db=='setbook_biz') {
		  tep_db_query("replace into " . $shop_db . "." . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) select concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_page_title, ' Russian books.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_title, '. Russian books.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_keywords, ' Russian books.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_description, ' Russian books.'), " . DB_DATABASE . "." . TABLE_METATAGS . ".language_id, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_id from " . DB_DATABASE . "." . TABLE_METATAGS . " where " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type in ('author', 'category', 'manufacturer',  'product', 'serie', 'type');");
		  tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books', 'Russian magazines'), mt.metatags_title = replace(mt.metatags_title, 'Russian books', 'Russian magazines'), mt.metatags_keywords = replace(mt.metatags_keywords, 'Russian books', 'Russian magazines'), mt.metatags_description = replace(mt.metatags_description, 'Russian books', 'Russian magazines') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '2'");
		  tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books', 'Russian magazines'), mt.metatags_title = replace(mt.metatags_title, 'Russian books', 'Russian souvenirs. Matreshka'), mt.metatags_keywords = replace(mt.metatags_keywords, 'Russian books', 'Russian magazines'), mt.metatags_description = replace(mt.metatags_description, 'Russian books', 'Russian souvenirs') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '5'");
		} elseif ($shop_db=='setbook_eu' || $shop_db=='setbook_net') {
		  tep_db_query("replace into " . $shop_db . "." . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) select concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_page_title, ' Russian books. Russische b&uuml;cher'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_title, '. Russian books.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_keywords, ' Russian books. Russische b&uuml;cher'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_description, ' Russian books. Russische b&uuml;cher'), " . DB_DATABASE . "." . TABLE_METATAGS . ".language_id, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_id from " . DB_DATABASE . "." . TABLE_METATAGS . " where " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type in ('author', 'category', 'manufacturer',  'product', 'serie', 'type');");
		  tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books. Russische b&uuml;cher', 'Russian magazines. Russische Zeitschriften'), mt.metatags_title = replace(mt.metatags_title, 'Russian books', 'Russian magazines'), mt.metatags_keywords = replace(mt.metatags_keywords, 'Russian books', 'Russian magazines'), mt.metatags_description = replace(mt.metatags_description, 'Russian books', 'Russian magazines') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '2'");
		} elseif ($shop_db=='setbook_ua') {
		  tep_db_query("replace into " . $shop_db . "." . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) select replace(" . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_page_title, 'Интернет-магазин Setbook', 'Книжный интернет-магазин в Украине Setbook'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_title, '. Книжный интернет-магазин в Украине Setbook.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_keywords, ' Украина, книги в Киеве.'),
concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_description, ' Украина, книги в Киеве.'), " . DB_DATABASE . "." . TABLE_METATAGS . ".language_id, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_id from " . DB_DATABASE . "." . TABLE_METATAGS . " where " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type in ('author', 'category', 'manufacturer',  'product', 'serie', 'type');");
//		  tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books', ''), mt.metatags_title = replace(mt.metatags_title, '', ''), mt.metatags_keywords = replace(mt.metatags_keywords, '', ''), mt.metatags_description = replace(mt.metatags_description, '', '') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '2'");
		} elseif ($shop_db=='setbook_kz') {
		  tep_db_query("replace into " . $shop_db . "." . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) select replace(" . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_page_title, 'Интернет-магазин Setbook', 'Книжный интернет-магазин в Казахстане Setbook'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_title, '. Книжный интернет-магазин в Казахстане Setbook.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_keywords, ' Казахстан, книги в Алматы, Астане, Караганде.'),
concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_description, ' Казахстан, книги в Алматы, Астане, Караганде.'), " . DB_DATABASE . "." . TABLE_METATAGS . ".language_id, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_id from " . DB_DATABASE . "." . TABLE_METATAGS . " where " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type in ('author', 'category', 'manufacturer',  'product', 'serie', 'type');");
//		  tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books', ''), mt.metatags_title = replace(mt.metatags_title, '', ''), mt.metatags_keywords = replace(mt.metatags_keywords, '', ''), mt.metatags_description = replace(mt.metatags_description, '', '') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '2'");
		} elseif ($shop_db=='setbook_by' || $shop_db=='bookva_by') {
		  if ($shop_db=='setbook_by') {
			tep_db_query("replace into " . $shop_db . "." . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) select replace(" . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_page_title, 'Интернет-магазин Setbook', 'Интернет-магазин книг в Белоруссии Setbook'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_title, '. Интернет-магазин книг Белоруссии Setbook.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_keywords, ' Белоруссия, книги в Минске.'), concat_ws('', " . DB_DATABASE . "." . TABLE_METATAGS . ".metatags_description, ' Белоруссия, книги в Минске.'), " . DB_DATABASE . "." . TABLE_METATAGS . ".language_id, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type, " . DB_DATABASE . "." . TABLE_METATAGS . ".content_id from " . DB_DATABASE . "." . TABLE_METATAGS . " where " . DB_DATABASE . "." . TABLE_METATAGS . ".content_type in ('author', 'category', 'manufacturer',  'product', 'serie', 'type');");
//			tep_db_query("update " . $shop_db . "." . TABLE_METATAGS . " mt, " . $shop_db . "." . TABLE_PRODUCTS . " p set mt.metatags_page_title = replace(mt.metatags_page_title, 'Russian books', ''), mt.metatags_title = replace(mt.metatags_title, '', ''), mt.metatags_keywords = replace(mt.metatags_keywords, '', ''), mt.metatags_description = replace(mt.metatags_description, '', '') where mt.content_type = 'product' and mt.content_id = p.products_id and p.products_types_id = '2'");
		  }
		}
	  }
	}
	tep_db_select_db(DB_DATABASE);
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
	  while($string = fgets($fp, 128)) {
		$result .= $string;
	  }
	  fclose($fp);
	}
	return $result;
  }

  function tep_get_translation($string, $from_language = 'ru', $to_language = 'en') {
	$string = stripslashes(strip_tags(tep_html_entity_decode($string)));
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
	  $url = 'http://translate.google.ru/?hl=ru&layout=2&eotf=0&sl=' . $from_language . '&tl=' . $to_language . '&q=' . urlencode($piece);

//	  $url = 'http://www.webproxyonline.info/browse.php?u=' . urlencode($url);
	  $url = 'http://4.hidemyass.com/browse.php?u=' . urlencode($url);
//	  $url = 'http://www.bind2.com/browse.php?u=' . rawurlencode($url);

	  $result = tep_request_html($url, 'GET');
	  $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'UTF-8');
	  preg_match('/<span id=result_box[^>]+>(.+)<div id=res-translit/', $result, $regs);
	  $tpieces[] = tep_db_prepare_input(strip_tags($regs[1]));
	}
	$translation = implode(' ', $tpieces);

	return $translation;
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

  function tep_upload_order($order_id, $delimiter = ',', $upload_dir = '') {
	if (empty($upload_dir)) $upload_dir = UPLOAD_DIR . 'orders1/';

//	$order_info_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
//	if (tep_db_num_rows($order_info_query) < 1) return false;

	$order_info = tep_db_fetch_array($order_info_query);
	$insert_id = $order_info['orders_id'];
	$insert_id = $order_id;
	$order = new order($insert_id);

	$shop_info_query = tep_db_query("select shops_url, shops_ssl, shops_prefix, shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$order->info['shops_id'] . "'");
	$shop_info = tep_db_fetch_array($shop_info_query);
	$domain_zone = $shop_info['shops_prefix'];

	$payment_modules = tep_get_payment_modules($order->info['shops_id']);
	$shipping_modules = tep_get_shipping_modules($order->info['shops_id']);

	$order_file = $upload_dir . $domain_zone . $insert_id . '.csv';
	if (!file_exists($order_file)) {
	  $fp = fopen($order_file, 'w');

	  $order_history_link = (($order->info['enabled_ssl']=='1' && tep_not_null($shop_info['shops_ssl'])) ? $shop_info['shops_ssl'] : $shop_info['shops_url']) . '/account_history_info.php?order_id=' . $insert_id;

	  $order_total_sum = 0;
	  $order_shipping_sum = 0;
	  $order_discount_sum = 0;
	  reset($order->totals);
	  while (list(, $order_total) = each($order->totals)) {
		if ($order_total['class']=='ot_total') {
		  $order_total_sum = $order_total['value'];
		} elseif ($order_total['class']=='ot_shipping') {
		  $order_shipping_sum = $order_total['value'];
		  $order_shipping_title = $order_total['title'];
		} elseif ( ($order_total['class']=='ot_discount' || $order_total['class']=='ot_custom') && $order_total['value'] < 0) {
		  $order_discount_sum = $order_total['value'];
		}
	  }

	  $order_payment_id = $order->info['payment_method_class'];
	  if (empty($order_payment_id)) {
		reset($payment_modules);
		$payment_found = false;
		while (list($k, $v) = each($payment_modules)) {
		  if (strpos($v, $order->info['payment_method'])!==false) {
			$order_payment_id = $k;
			break;
		  }
		}
		if (empty($order_payment_id)) $order_payment_id = $order->info['payment_method'];
	  }

	  $order_shipping_id = $order->delivery['delivery_method_class'];
	  if (empty($order_shipping_id)) {
		reset($shipping_modules);
		$shipping_found = false;
		while (list($k, $v) = each($shipping_modules)) {
		  if (strpos($v, $order_shipping_title)!==false) {
			$order_shipping_id = $k;
			break;
		  }
		}
		if (empty($order_shipping_id)) $order_shipping_id = $order_shipping_title;
	  }

	  $self_delivery_id = $order->delivery['delivery_self_address_id'];
	  if (tep_not_null($order->delivery['delivery_self_address']) && (int)$self_delivery_id <= 0) {
		$shop_info_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$order->info['shops_id'] . "'");
		$shop_info = tep_db_fetch_array($shop_info_query);
		tep_db_select_db($shop_info['shops_database']);

		$self_delivery_query = tep_db_query("select self_delivery_id, self_delivery_cost, self_delivery_free, entry_suburb as suburb, entry_city as city, entry_street_address as street_address, entry_telephone as telephone, self_delivery_description from " . TABLE_SELF_DELIVERY . " where 1 order by city, street_address");
		while ($self_delivery = tep_db_fetch_array($self_delivery_query)) {
		  $self_delivery_address = tep_address_format($order->delivery['format_id'], $self_delivery, 1, '', ', ');
		  if (strpos($order->delivery['delivery_self_address'], $self_delivery_address)!==false) {
			$self_delivery_id = $self_delivery['self_delivery_id'];
			break;
		  }
		}

		tep_db_select_db(DB_DATABASE);
		if ($self_delivery_id==0) $self_delivery_id = $order->info['self_delivery'];
	  }

	  $date_purchased = preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '$3.$2.$1 $4:$5:$6', $order->info['date_purchased']);
	  $date_purchased = preg_replace('/\s{2,}/', ' ', $date_purchased);

	  $is_europe = '';
	  $europe_check_query = tep_db_query("select count(*) as total from setbook_eu." . TABLE_COUNTRIES . " where countries_name like '" . tep_db_input($order->delivery['country']) . "' or countries_ru_name like '" . tep_db_input($order->delivery['country']) . "' or countries_iso_code_2 like '" . tep_db_input($order->delivery['country']) . "' or countries_iso_code_3 like '" . tep_db_input($order->delivery['country']) . "'");
	  $europe_check = tep_db_fetch_array($europe_check_query);
	  if ($europe_check['total'] > 0) $is_europe = 'e';

	  $order_delivery_country_code = '';
	  $country_code_info_query = tep_db_query("select countries_iso_code_2 from " . $shop_info['shops_database'] . "." . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($order->delivery['country']) . "' or countries_ru_name = '" . tep_db_input($order->delivery['country']) . "'");
	  $country_code_info = tep_db_fetch_array($country_code_info_query);
	  $order_delivery_country_code = $country_code_info['countries_iso_code_2'];

	  if ($order_delivery_country_code=='') {
		$order_delivery_country = strtolower($order->delivery['country']);
		$all_countries_file = UPLOAD_DIR . 'csv/all_countries.csv';
		$fc = fopen($all_countries_file, 'r');
		while ((list($country_name, $country_ru_name, $country_iso_code_2, $country_iso_code_3) = fgetcsv($fc, 40000, ";")) !== FALSE) {
		  $country_name = strtolower($country_name);
		  $country_ru_name = strtolower($country_ru_name);
		  $country_iso_code_3 = strtolower($country_iso_code_3);
		  if ($order_delivery_country==$country_name || $order_delivery_country==$country_ru_name || $order_delivery_country==$country_iso_code_3) {
			$order_delivery_country_code = $country_iso_code_2;
			break;
		  }
		}
		fclose($fc);
	  }

	  if ($order_delivery_country_code=='') {
		$fc = fopen($all_countries_file, 'r');
		while ((list($country_name, $country_ru_name, $country_iso_code_2, $country_iso_code_3) = fgetcsv($fc, 40000, ";")) !== FALSE) {
		  $country_name = strtolower($country_name);
		  $country_ru_name = strtolower($country_ru_name);
		  if (strpos($country_name, $order_delivery_country)!==false || strpos($country_ru_name, $order_delivery_country)!==false || strpos($country_iso_code_3, $order_delivery_country)!==false) {
			$order_delivery_country_code = $country_iso_code_2;
			break;
		  }
		}
		fclose($fc);
	  }

	  $common_data = array($insert_id, #номер заказа без префиксов
						   $date_purchased, #Дата заказа в формате 02.04.2010
						   $order->info['shops_id'], #ID сайта
						   $order->customer['id'], #ID пользователя без префикса
						   $order->customer['email_address'], #EMAIL
						   $order->delivery['name'], #имя
						   '', #фамилия
						   '', #отчество
						   $order_payment_id, #Тип оплаты
						   $order_shipping_id, #Тип доставки
						   str_replace(',', '.', $order_shipping_sum), #Стоимость доставки
						   $order->info['currency'], #Код валюты заказа
						   str_replace(',', '.', $order->info['currency_value']), #Курс валюты заказа
						   tep_html_entity_decode($order->delivery['state']), #Регион, строка
						   tep_html_entity_decode($order->delivery['suburb']), #Район, строка
						   tep_html_entity_decode($order->delivery['city']), #Город, строка
						   $order->delivery['postcode'], #Почтовый индекс, строка
						   tep_html_entity_decode($order->delivery['street_address']), #Почтовый адрес, строка
						   $order->delivery['telephone'], #телефон, строка
						   tep_html_entity_decode($order->info['comments']), #Коментарий клиента, строка
						   $self_delivery_id, #ID пункта самовывоза
						   $order_history_link, #Ссылка на страницу
						   str_replace(',', '.', $order_total_sum), #Стоимость заказа в рублях
						   str_replace(',', '.', abs($order_discount_sum)), #Скидка в рублях
						   tep_html_entity_decode($order->customer['company']), #Полное наименование ЮР лица
						   $order->customer['company_inn'], #Инн
						   $order->customer['company_kpp'], #Кпп
						   $order->customer['company_address_corporate'], #ЮрАдрес
						   $is_europe, #проверка того, что доставка по Европе
						   $order->info['delivery_transfer_days'],
						   $order->info['code'],
						   $order->delivery['country'],
						   tep_html_entity_decode($order_delivery_country_code), #Страна доставки, код
						   $order->customer['company_corporate'], #Корпоративный клиент
						   );
	  fputcsvsafe($fp, $common_data, $delimiter);

//	  tep_db_query("update " . TABLE_ORDERS . " set payment_method_class = '" . tep_db_input($order_payment_id) . "', delivery_method_class = '" . tep_db_input($order_shipping_id) . "', delivery_self_address_id = '" . (int)$self_delivery_id . "' where orders_id = '" . (int)$insert_id . "'");

	  reset($order->products);
	  while (list(, $product) = each($order->products)) {
		$product_code = (int)str_replace('bbk', '', $product['code']);
		$common_data = array($product['type'],
							 $product_code,
							 $product['qty'],
							 str_replace(',', '.', $product['final_price']),
							 $product['id'],
							 $product['seller_code'],
							 $product['name'],
							 tep_get_products_name($product['id']),
							 $product['code'],
							 $product['warranty']);
		fputcsvsafe($fp, $common_data, $delimiter);
	  }
	  fclose($fp);
	}
  }
?>