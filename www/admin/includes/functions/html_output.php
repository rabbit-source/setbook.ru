<?php
////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'SSL') {
    if ($page == '') {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><strong>Error!</strong></font><br><br><strong>Unable to determine the page link!<br><br>Function used:<br><br>tep_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</strong>');
    }
    if ($connection == 'SSL' && ENABLE_SSL==true) {
	  $link = HTTPS_SERVER . DIR_WS_ADMIN;
    } else {
      $link = HTTP_SERVER . DIR_WS_ADMIN;
    }
    if ($parameters == '') {
      $link = $link . $page . '?' . SID;
    } else {
      $link = $link . $page . '?' . $parameters . '&' . SID;
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    return $link;
  }

  function tep_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL') {
    global $request_type, $session_started, $SID, $spider_flag;

    if (!tep_not_null($page)) $page = FILENAME_DEFAULT;

	$link = '';

	$redirect = false;
	$redirect_url = '';
	$categories_id = '';
	$products_id = '';
	$categories_parameters = '';
	$sections_id = '';
	$information_id = '';
	$sections_parameters = '';
	$manufacturers_id = '';
	$manufacturers_parameters = '';
	$news_id = '';
	$news_parameters = '';
	$products_types_id = 0;
	if (basename($page)==FILENAME_CATALOG_CATEGORIES || basename($page)==FILENAME_CATALOG_PRODUCT_INFO) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'cPath') {
		  $categories_id = $param_value;
		} elseif ($param_name == 'products_id') {
		  $products_id = $param_value;
		} else {
		  $categories_parameters .= (tep_not_null($categories_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'cName') {
		  $cname = $param_value;
#		  if (substr($cname, -1)=='/') $cname = substr($cname, 0, -1);
#		  if (substr($cname, 0, 1)=='/') $cname = substr($cname, 1);
		}
	  }

	  $current_category_id = 0;
	  if (tep_not_null($categories_id)) {
		$last_id = end(explode('_', $categories_id));
		$parent_categories = array($last_id);
		tep_get_parents($parent_categories, $last_id);
		$parent_categories = array_reverse($parent_categories);
		reset($parent_categories);
		while (list(, $category_id) = each($parent_categories)) {
		  $categories_path_query = tep_db_query("select categories_path, products_types_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$category_id . "' limit 1");
		  if (tep_db_num_rows($categories_path_query) > 0) {
			$categories_path = tep_db_fetch_array($categories_path_query);
			if (tep_not_null($categories_path['categories_path'])) $link .= $categories_path['categories_path'] . '/';
			else $link .= $category_id . '/';
			$current_category_id = $category_id;
			if ($products_types_id==0) $products_types_id = $categories_path['products_types_id'];
		  }
		}
	  }
	  if (tep_not_null($products_id)) {
		$products_path_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		$products_path = tep_db_fetch_array($products_path_query);
		$link = $products_id . '.html';
		if ($products_types_id==0) $products_types_id = $products_path['products_types_id'];
	  }
	  $parameters = $categories_parameters;
	} elseif (basename($page)==FILENAME_SPECIALS) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'tPath') {
		  $tpath = $param_value;
		} else {
		  $types_parameters .= (tep_not_null($types_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'specials/';
	  if (tep_not_null($tpath)) {
		$types_path_query = tep_db_query("select specials_types_path from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$tpath . "' limit 1");
		$types_path = tep_db_fetch_array($types_path_query);
		if (tep_not_null($types_path['specials_types_path'])) $link .= $types_path['specials_types_path'] . '/';
	  }
	  $parameters = $types_parameters;
	} elseif (basename($page)==FILENAME_CATALOG_MANUFACTURERS) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'manufacturers_id') {
		  $manufacturers_id = $param_value;
		} else {
		  $manufacturers_parameters .= (tep_not_null($manufacturers_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'manufacturers/';
	  if (tep_not_null($manufacturers_id)) {
		$manufacturers_path_query = tep_db_query("select manufacturers_id, manufacturers_path from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "' limit 1");
		$manufacturers_path = tep_db_fetch_array($manufacturers_path_query);
		$link .= (tep_not_null($manufacturers_path['manufacturers_path']) ? $manufacturers_path['manufacturers_path'] : $manufacturers_path['manufacturers_id']) . '/';
	  }
	  $parameters = $manufacturers_parameters;
	} elseif (basename($page)==FILENAME_CATALOG_SERIES) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'series_id') {
		  $series_id = $param_value;
		} else {
		  $series_parameters .= (tep_not_null($series_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'series/';
	  if (tep_not_null($series_id)) {
		$series_path_query = tep_db_query("select series_id, series_path, products_types_id from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "' limit 1");
		$series_path = tep_db_fetch_array($series_path_query);
		$link .= (tep_not_null($series_path['series_path']) ? $series_path['series_path'] : $series_path['series_id']) . '/';
		if ($products_types_id==0) $products_types_id = $series_path['products_types_id'];
	  }
	  $parameters = $series_parameters;
	} elseif (basename($page)==FILENAME_CATALOG_DEFAULT) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'sPath') {
		  $sections_id = $param_value;
		} elseif ($param_name == 'info_id') {
		  $information_id = $param_value;
		} else {
		  $sections_parameters .= (tep_not_null($sections_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'sName') {
		  $sname = $param_value;
#		  if (substr($cname, -1)=='/') $cname = substr($cname, 0, -1);
#		  if (substr($cname, 0, 1)=='/') $cname = substr($cname, 1);
		}
	  }

	  $current_section_id = 0;
	  if (tep_not_null($sections_id)) {
		$sections_array = explode('_', $sections_id);
		if (sizeof($sections_array)==1) {
		  $last_id = end($sections_array);
		  $parent_sections = array($last_id);
		  tep_get_parents($parent_sections, $last_id, TABLE_SECTIONS);
		  $parent_sections = array_reverse($parent_sections);
		} else {
		  $parent_sections = $sections_array;
		}
		reset($parent_sections);
		while (list(, $section_id) = each($parent_sections)) {
		  if ($section_id > 0) {
			$sections_path_query = tep_db_query("select sections_path from " . TABLE_SECTIONS . " where sections_id = '" . (int)$section_id . "' limit 1");
			$sections_path = tep_db_fetch_array($sections_path_query);
			if (tep_not_null($sections_path['sections_path'])) $link .= $sections_path['sections_path'] . '/';
			$current_section_id = $section_id;
		  }
		}
	  }
	  if (sizeof($sections_array)>1 && tep_not_null($information_id)) {
		$information_path_query = tep_db_query("select i2s.information_default_status, i.information_path, i.information_redirect from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = '" . (int)$information_id . "' and i.information_id = i2s.information_id limit 1");
	  } else {
		$information_path_query = tep_db_query("select i2s.information_default_status, i.information_path, i.information_redirect from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where" . (tep_not_null($information_id) ? " i.information_id = '" . (int)$information_id . "'" : " i2s.information_default_status = '1'") . " and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$current_section_id . "' limit 1");
	  }
	  $information_path = tep_db_fetch_array($information_path_query);
	  if (tep_not_null($information_path['information_redirect'])) {
		$redirect_url = $information_path['information_redirect'];
	  }
	  if ($information_path['information_default_status'] != '1' && tep_not_null($information_path['information_path'])) {
		$link .= $information_path['information_path'] . '.html';
	  } elseif (empty($sections_id) && empty($information_path) && tep_not_null($sname)) {
		$link .= $sname;
	  }
	  $parameters = $sections_parameters;
	} elseif (basename($page)==FILENAME_CATALOG_NEWS) {
	  $link .= 'news/';
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'news_id') {
		  $news_id = $param_value;
		} elseif ($param_name == 'year') {
		  $news_year = $param_value;
		} elseif ($param_name == 'month') {
		  $news_month = $param_value;
		} else {
		  $news_parameters .= (tep_not_null($news_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'nName') {
		  $nname = $param_value;
#		  if (substr($cname, -1)=='/') $cname = substr($cname, 0, -1);
#		  if (substr($cname, 0, 1)=='/') $cname = substr($cname, 1);
		}
		$parameters = $news_parameters;
	  }

	  if (tep_not_null($news_id)) {
		$news_query = tep_db_query("select date_format(date_added, '%Y/%m/') as news_date from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "' limit 1");
		$news = tep_db_fetch_array($news_query);
		$link .= $news['news_date'] . $news_id . '.html';
		$parameters = $news_parameters;
	  } else {
		if (tep_not_null($news_year)) {
		  $link .= $news_year . '/';
		  if (tep_not_null($news_month)) {
			$link .= $news_month . '/';
		  }
		}
	  }
	  if (empty($news_id) && empty($news_year) && empty($news_month) && tep_not_null($nname)) {
		$link .= $nname;
	  }
	}

	if ($page!=FILENAME_DEFAULT && $page!='/' && $page!=DIR_WS_ONLINE_STORE) {
	  $params = explode('&', $parameters);
	  $temp_parameters = '';
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name) = explode('=', $param);
		if (!in_array($param_name, array('cPath', 'sPath', 'nName', 'cName', 'sName', 'info_id', 'iName', 'tName', 'mName', 'rName'))) {
		  if ( (basename($page)==FILENAME_PRODUCT_INFO && $param_name=='products_id') || (basename($page)==FILENAME_MANUFACTURERS && $manufacturers_id > 0 && $param_name=='manufacturers_id') ) {
		  } else {
			$temp_parameters .= (tep_not_null($temp_parameters) ? '&' : '') . $param;
		  }
		}
	  }
#	  if (tep_not_null($temp_parameters)) 
	  $parameters = $temp_parameters;
	}

	if (in_array(basename($page), array(FILENAME_CATALOG_CATEGORIES, FILENAME_CATALOG_PRODUCT_INFO, FILENAME_CATALOG_SPECIALS, FILENAME_CATALOG_MANUFACTURERS, FILENAME_CATALOG_DEFAULT, FILENAME_CATALOG_NEWS, FILENAME_CATALOG_SERIES))) {
	  if (!in_array(basename($page), array(FILENAME_CATALOG_DEFAULT, FILENAME_CATALOG_NEWS))) {
		$product_type_info_query = tep_db_query("select products_types_path from " . TABLE_PRODUCTS_TYPES . " where 1" . ($products_types_id>0 ? " and products_types_id = '" . (int)$products_types_id . "'" : " and products_types_default_status = '1'"));
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$link = $product_type_info['products_types_path'] . '/' . $link;
	  }
	  $page = '';
	}

	$page = preg_replace('/^index\.[a-z0-9]{3,5}/i', '', $page);

    if (tep_not_null($parameters)) {
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
    while (strpos($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strpos($link, '//')) $link = str_replace('//', '/', $link);

	$other_server_check = substr($link, 0, 4) == 'http';
    if ($connection == 'NONSSL' && $other_server_check == false) {
      $link = HTTP_SERVER . DIR_WS_CATALOG . $link;
    } elseif ($connection == 'SSL' && $other_server_check == false) {
	  $link = HTTP_SERVER . DIR_WS_CATALOG . $link;
      if (ENABLE_SSL == true) {
        $link = preg_replace('/^http:\/\//', 'https://', $link);
      }
	} elseif ($connection != 'NONSSL' && $connection != 'SSL') {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><strong>Error!</strong></font><br><br><strong>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</strong><br><br>');
    }

    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $image = '<img src="' . $src . '" border="0" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title=" ' . $alt . ' "';
    }
    if ($width) {
      $image .= ' width="' . $width . '"';
    }
    if ($height) {
      $image .= ' height="' . $height . '"';
    }
    if ($params) {
      $image .= ' ' . $params;
    }
    $image .= ' />';

    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_LANGUAGES . 'lang/images/buttons/' . $image) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
  }

////
// Draw a 1 pixel black line
  function tep_black_line() {
    return tep_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $params = '') {
    global $language;

    return tep_image(DIR_WS_LANGUAGES . 'lang/images/buttons/' . $image, $alt, '', '', $params);
  }

////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function tep_js_zone_list($country, $form, $field) {
    $countries_query = tep_db_query("select distinct zone_country_id from " . TABLE_ZONES . " order by zone_country_id");
    $num_country = 1;
    $output_string = '';
    while ($countries = tep_db_fetch_array($countries_query)) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      }

      $states_query = tep_db_query("select zone_name, zone_id from " . TABLE_ZONES . " where zone_country_id = '" . $countries['zone_country_id'] . "' order by zone_name");

      $num_state = 1;
      while ($states = tep_db_fetch_array($states_query)) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states['zone_name'] . '", "' . $states['zone_id'] . '");' . "\n";
        $num_state++;
      }
      $num_country++;
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '  }' . "\n";

    return $output_string;
  }

////
// Output a form
  function tep_draw_form($name, $action, $parameters = '', $method = 'post', $params = '') {
    $form = '<form name="' . tep_output_string($name) . '" action="';
    if (tep_not_null($parameters)) {
      $form .= tep_href_link($action, $parameters);
    } else {
      $form .= tep_href_link($action);
    }
    $form .= '" method="' . tep_output_string($method) . '"';
    if (tep_not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (isset($GLOBALS[$name]) && ($reinsert_value == true) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $required = false) {
    $field = tep_draw_input_field($name, $value, 'maxlength="40"', $required, 'password', false);

    return $field;
  }

////
// Output a form filefield
  function tep_draw_file_field($name, $required = false, $parameters = '') {
    $field = tep_draw_input_field($name, '', $parameters, $required, 'file');

    return $field;
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $compare = '', $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ($GLOBALS[$name] == 'on')) || (isset($value) && isset($GLOBALS[$name]) && (stripslashes($GLOBALS[$name]) == $value)) || (tep_not_null($value) && tep_not_null($compare) && ($value == $compare)) ) {
      $selection .= ' checked="true"';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $compare, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $compare, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . tep_output_string($name) . '" wrap="' . tep_output_string($wrap) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $value = $GLOBALS[$name];
    } elseif (tep_not_null($text)) {
      $value = $text;
    }
	$value = str_replace('>', '&gt;', $value);
	$value = str_replace('<', '&lt;', $value);
	$field .= stripslashes($value);

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
	$field = '';

	if (is_array($value)) {
	  reset($value);
	  while (list($var_key, $var_value) = each($value)) {
		$field .= tep_draw_hidden_field($name . '[' . $var_key . ']', (is_array($var_value) ? $var_value : htmlspecialchars(stripslashes($var_value))), $parameters);
	  }
	} else {
	  $field .= '<input type="hidden" name="' . tep_output_string($name) . '"';
	  if (tep_not_null($value)) {
		$field .= ' value="' . tep_output_string($value) . '"';
	  } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
		$field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
	  }
	  if (tep_not_null($parameters)) $field .= ' ' . $parameters;
	  $field .= ' />' . "\n";
	}

    return $field;
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $def = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '"';
	$default = array();

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($def) && isset($GLOBALS[$name])) $def = stripslashes($GLOBALS[$name]);

	if (!is_array($def)) $default[0] = $def;
	else $default = $def;
    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
	  if (!isset($values[$i]['active'])) $values[$i]['active'] = '1';
      if (tep_not_null($values[$i]['id']) || tep_not_null($values[$i]['text'])) {
		if ($values[$i]['active']=='0') {
		  $field .= '<optgroup label="' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '"' . (tep_not_null($values[$i]['params']) ? ' ' . $values[$i]['params'] : '') . '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</optgroup>' . "\n";
		} else {
		  $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
		  if (in_array($values[$i]['id'], $default)) {
			$field .= ' selected="true"';
		  }
		  $field .= (tep_not_null($values[$i]['params']) ? ' ' . $values[$i]['params'] : '') . '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
		}

	  }
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
?>
