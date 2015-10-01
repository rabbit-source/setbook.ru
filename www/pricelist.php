<?php
  require('includes/application_top.php');
  set_time_limit(0);

  if (isset($argv)) {
  	$GLOBAL_CRON = true;
	reset($argv);
	while (list($i, $arg) = each($argv)) {
	  if ($i > 0) {
		list($arg_key, $arg_value) = explode('=', $arg);
		$HTTP_GET_VARS[$arg_key] = urldecode($arg_value);
	  }
	}
  }
  
  $for = (tep_not_null($HTTP_GET_VARS['for']) ? tep_db_prepare_input($HTTP_GET_VARS['for']) : '');

  function write_to_file($filename, $stream_id, $content) {
	global $GLOBAL_CRON;
	$file_ext = substr($filename, strrpos($filename, '.')+1);
	switch ($file_ext) {
	  case 'gz':
		$string = gzencode($content, 9);
		break;
	  case 'bz2':
		$string = bzcompress($content, 9);
		break;
	  default:
		$string = $content;
		break;
	}
	flush();
	if(!$GLOBAL_CRON) echo $string;
	else echo "file ".$filename." ".strlen($string)."\n";
	if ($stream_id) return fwrite($stream_id, $string);
  }

  function tep_get_csv_string($csv_data_array, $separator = ";") {
	ob_start();
	$out = fopen('php://output', 'w');
	fputcsv($out, $csv_data_array, $separator);
	fclose($out);
	return ob_get_clean();
  }

  $customer_discount = $cart->get_customer_discount();
  if (!is_array($customer_discount)) $customer_discount = array();

  function tep_get_full_product_info($products_id, $pricelist_type = 'csv') {
	global $languages_id, $all_categories, $currency, $currencies, $HTTP_GET_VARS, $categories_audio, $for, $customer_discount;

	$products_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$products = tep_db_fetch_array($products_query);

	if (DEFAULT_LANGUAGE_ID==$languages_id) {
	  $product_info_query = tep_db_query("select * from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$products['products_id'] . "'");
	  $product_info = tep_db_fetch_array($product_info_query);

	  $product_info = array_merge($product_info, $products);

	  $product_info['products_url'] = HTTP_SERVER . $product_info['products_url'];
	} else {
	  $product_info_query = tep_db_query("select products_name, products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $product_info = tep_db_fetch_array($product_info_query);
	  if (DEFAULT_LANGUAGE_ID==1) {
		$product_ru_info_query = tep_db_query("select products_name, products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
		$product_ru_info = tep_db_fetch_array($product_ru_info_query);
		$product_ru_name = tep_transliterate($product_ru_info['products_name']);
		if ($product_info['products_name']!=$product_ru_info['products_name'] && $product_info['products_name']!=$product_ru_name) $product_info['products_name'] .= (tep_not_null($product_info['products_name']) ? ' / ' : '') . $product_ru_name;
	  }

	  $author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$products['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $author_info = tep_db_fetch_array($author_info_query);
	  if (!is_array($author_info)) $author_info = array();

	  $serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$products['series_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $serie_info = tep_db_fetch_array($serie_info_query);
	  if (!is_array($serie_info)) $serie_info = array();

	  $manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$products['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
	  if (!is_array($manufacturer_info)) $manufacturer_info = array();

	  $product_info['products_width'] = '';
	  $product_info['products_height'] = '';
	  $product_info['products_width_height_measure'] = '';
	  $product_format_info_query = tep_db_query("select products_formats_name from " . TABLE_PRODUCTS_FORMATS . " where products_formats_id = '" . (int)$products['products_formats_id'] . "'");
	  $product_format_info = tep_db_fetch_array($product_format_info_query);
	  if (!is_array($product_format_info)) $product_format_info = array();
	  if (tep_not_null($product_format_info['products_formats_name'])) {
		$product_format = $product_format_info['products_formats_name'];
		list($product_format) = explode(' ', $product_format);
		list($product_format) = explode('/', $product_format);
		if (preg_match('/^(\d+)x(\d+)$/i', $product_format, $regs)) {
		  $product_info['products_width'] = $regs[1];
		  $product_info['products_height'] = $regs[1];
		  $product_info['products_width_height_measure'] = 'mm';
		}
	  }

	  $product_cover_info_query = tep_db_query("select products_covers_name from " . TABLE_PRODUCTS_COVERS . " where products_covers_id = '" . (int)$products['products_covers_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $product_cover_info = tep_db_fetch_array($product_cover_info_query);
	  if (!is_array($product_cover_info)) $product_cover_info = array();

	  $product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products['products_types_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $product_type_info = tep_db_fetch_array($product_type_info_query);
	  if (!is_array($product_type_info)) $product_type_info = array();

	  $category_info_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products['products_id'] . "' order by categories_id desc limit 1");
	  $category_info = tep_db_fetch_array($category_info_query);
	  if (!is_array($category_info)) $category_info = array();

	  $product_info = array_merge($product_info, $products, $author_info, $serie_info, $manufacturer_info, $product_format_info, $product_cover_info, $category_info);

	  $product_info['products_url'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id'], 'NONSSL', false);
	}

	if ($customer_discount['type']=='purchase' && $products['products_purchase_cost'] > 0) {
	  $product_info['products_price'] = $products['products_purchase_cost'] * (1 + $customer_discount['value']/100);
	}

	if (mb_strpos($product_info['products_description'], '<table', 0, 'CP1251')!==false) $product_info['products_description'] = mb_substr($product_info['products_description'], 0, mb_strpos($product_info['products_description'], '<table', 0, 'CP1251'), 'CP1251');
	$short_description = trim(preg_replace('/\s+/', ' ', preg_replace('/<\/?[^>]+>/', ' ', $product_info['products_description'])));
	$product_info['products_description'] = $short_description;

	$product_info['products_description_short'] = $short_description;

	if ($pricelist_type=='csv') {
	  if ($for=='shopmania' || $for=='nur_kz') {
		if ($for=='nur_kz') $categories_delimiter = ' | ';
		else $categories_delimiter = ' > ';
		if (!in_array($product_info['categories_id'], array_keys($all_categories))) {
		  $parent_categories = array($product_info['categories_id']);
		  tep_get_parents($parent_categories, $product_info['categories_id']);
		  $parent_categories = array_reverse($parent_categories);
		  $categories_names = array_map('tep_get_category_name', $parent_categories);
		  $category_tree_string = implode($categories_delimiter, $categories_names);
		  $all_categories[$category_info['categories_id']] = $category_tree_string;
		} else {
		  $category_tree_string = $all_categories[$category_info['categories_id']];
		}
//		list($product_info['categories_name']) = explode(' > ', $category_tree_string);
		$product_info['categories_name'] = $product_info['products_types_name'] . $categories_delimiter . $category_tree_string;
	  }

	  if ($for=='amazon_uk') $product_info['products_currency'] = 'GBP';
	  else $product_info['products_currency'] = DEFAULT_CURRENCY;

	  if (strpos($for, 'amazon')!==false) {
		$product_info['products_price'] = str_replace(',', '.', sprintf("%01.2f", round($product_info['products_cost'] * 1.8 * $currencies->get_value($product_info['products_currency']), 1) + 1.2));
		$product_info['products_weight'] = str_replace(',', '.', round($product_info['products_weight'], 2));
		$product_info['products_model'] = preg_replace('/[^\dX]/', '', $product_info['products_model']);
		if (strlen($product_info['products_name']) > 500) $product_info['products_name'] = substr($product_info['products_name'], 0, 500);
		if ($product_info['authors_name']=='') $product_info['authors_name'] = 'unknown';
	  } elseif ($for=='ebay') {
		if (tep_not_null($product_info['authors_name'])) $product_info['products_name'] .= ' by ' . $product_info['authors_name'];
		$product_info['products_price'] = str_replace(',', '.', round($product_info['products_price'] * $currencies->get_value($product_info['products_currency']), $currencies->get_decimal_places($product_info['products_currency'])));
		$product_info['products_model'] = preg_replace('/[^\dX]/', '', $product_info['products_model']);
	  } else {
		$product_info['products_price'] = str_replace('.', ',', round($product_info['products_price'] * $currencies->get_value($product_info['products_currency']), $currencies->get_decimal_places($product_info['products_currency'])));
	  }
	} else {
	  if (!in_array($currency, array('RUR', 'EUR', 'USD', 'UAH'))) {
		$product_info['products_currency'] = 'RUR';
		$product_info['products_price'] = str_replace(',', '.', round($product_info['products_price'], $currencies->get_decimal_places($product_info['products_currency'])));
	  } else {
		$product_info['products_currency'] = str_replace('RUB', 'RUR', DEFAULT_CURRENCY);
		$product_info['products_price'] = str_replace(',', '.', round($product_info['products_price'] * $currencies->get_value($product_info['products_currency']), $currencies->get_decimal_places($product_info['products_currency'])));
	  }
	}

	if (tep_not_null($product_info['products_image'])) {
//	  $product_info['products_image_big'] = tep_href_link(DIR_WS_IMAGES . 'big/' . $product_info['products_image'], '', 'NONSSL', false);
	  $product_info['products_image_big'] = 'http://85.236.24.26/big/' . $product_info['products_image'];
	  if (strpos($for, 'amazon')!==false || $for=='ebay') {
		$product_info['products_image'] = $product_info['products_image_big'];
	  } else {
//		$product_info['products_image'] = tep_href_link(DIR_WS_IMAGES . 'thumbs/' . $product_info['products_image'], '', 'NONSSL', false);
		$product_info['products_image'] = 'http://85.236.24.26/thumbs/' . $product_info['products_image'];
//		$product_info['products_image'] = str_replace(HTTP_SERVER, 'http://images.setbook.ru', $product_info['products_image']);
	  }
	}

	$product_info['products_buy'] = tep_href_link(FILENAME_SHOPPING_CART, 'action=buy_now&product_id=' . $product_info['products_id'], 'NONSSL', false);

	$product_info['products_quantity'] = '';

	$product_info['is_audio'] = false;
	if (in_array($product_info['categories_id'], $categories_audio)) $product_info['is_audio'] = true;

	if ( (ALLOW_SHOW_AVAILABLE_IN=='true' && tep_not_null($HTTP_GET_VARS['limit'])) || (SHOP_ID==4) ) {
	} elseif ($product_info['products_listing_status']==1) {
	  $product_info['products_available_in'] = 1;
	} else {
	  $product_info['products_available_in'] = 10;
	}

	reset($product_info);
	while (list($k, $v) = each($product_info)) {
	  $v = str_replace($from1, $to, str_replace($from, $to, $v));
	  if (in_array($k, array('products_name', 'products_description'))) $v = preg_replace('/\s{2,}/', ' ', preg_replace('/[^_\/\w\d\#\&(\)\-\[\]\.",;]/', ' ', $v));
	  if ($pricelist_type=='csv') $product_info[$k] = strip_tags(tep_html_entity_decode($v));
	  else $product_info[$k] = htmlspecialchars(strip_tags(tep_html_entity_decode($v)), ENT_QUOTES);
	}

//	print_r($product_info); die;
	return $product_info;
  }

  $content = FILENAME_PRICELIST;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_PRICELIST));

  $fields_array = array();
  $fields_array['products_model'] = TEXT_CHOOSE_MODEL;
  $fields_array['products_name'] = TEXT_CHOOSE_NAME;
  $fields_array['categories_name'] = TEXT_CHOOSE_CATEGORY;
  $fields_array['authors_name'] = TEXT_CHOOSE_AUTHOR;
  $fields_array['manufacturers_name'] = TEXT_CHOOSE_MANUFACTURER;
  $fields_array['series_name'] = TEXT_CHOOSE_SERIE;
  $fields_array['products_description'] = TEXT_CHOOSE_DESCRIPTION;
  $fields_array['products_price'] = TEXT_CHOOSE_PRICE;
  $fields_array['products_year'] = TEXT_CHOOSE_YEAR;
  $fields_array['products_pages_count'] = TEXT_CHOOSE_PAGES_COUNT;
  $fields_array['products_copies'] = TEXT_CHOOSE_COPIES;
  $fields_array['products_covers_name'] = TEXT_CHOOSE_COVER;
  $fields_array['products_formats_name'] = TEXT_CHOOSE_FORMAT;
  $fields_array['products_image'] = TEXT_CHOOSE_IMAGE;
  $fields_array['products_url'] = TEXT_CHOOSE_URL;

  $fileds_required = array('products_model', 'products_name', 'authors_name', 'products_price', 'manufacturers_name');

  $specials_array = array();
  $specials_types_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_path <> '' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	$specials_type_check_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types['specials_types_id'] . "' and status = '1'");
	$specials_type_check = tep_db_fetch_array($specials_type_check_query);
	if ($specials_type_check['total'] > 0) $specials_array[$specials_types['specials_types_id']] = $specials_types['specials_types_name'];
  }

  $specials_periods_array = array(array('id' => 'w', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_WEEK),
								  array('id' => '2w', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_2_WEEK),
								  array('id' => 'm', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_MONTH),
								  array('id' => 'h', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_HALF_YEAR),
								  );

  $comp_methods = array();
  $comp_methods['none'] = ENTRY_PRICELIST_COMPRESSION_NONE;
  if (class_exists("ZipArchive")) {
//	$comp_methods['zip'] = ENTRY_PRICELIST_COMPRESSION_ZIP;
  }
  if (function_exists("gzopen")) {
	$comp_methods['gz'] = ENTRY_PRICELIST_COMPRESSION_GZIP;
  }
  if (function_exists("bzopen")) {
	$comp_methods['bz2'] = ENTRY_PRICELIST_COMPRESSION_BZIP2;
  }

  if ($HTTP_GET_VARS['action']=='download_pricelist') {
	$msg = 'REMOTE_ADDR: ' . getenv('REMOTE_ADDR') . "\n" .
	'REQUEST_URI: ' . getenv('REQUEST_URI') . "\n" .
	'GEOIP_COUNTRY_NAME: ' . getenv('GEOIP_COUNTRY_NAME') . "\n" .
	'HTTP_USER_AGENT: ' . getenv('HTTP_USER_AGENT');
//	tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Загрузка прайса', $msg, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	$fields = array();
	for ($i=0; $i<sizeof($fields_array); $i++) {
	  if (tep_not_null($HTTP_GET_VARS['f_' . $i])) $fields[] = tep_db_prepare_input(urldecode($HTTP_GET_VARS['f_' . $i]));
	}

	$categories = array();
	$categories_count_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '0'");
	$categories_count = tep_db_fetch_array($categories_count_query);
	for ($i=0; $i<$categories_count['total']; $i++) {
	  if (tep_not_null($HTTP_GET_VARS['c_' . $i])) $categories[] = tep_string_to_int(urldecode($HTTP_GET_VARS['c_' . $i]));
	}

	$manufacturers = (tep_not_null($HTTP_GET_VARS['m_0']) ? array_map('tep_db_prepare_input', explode("\n", trim(urldecode($HTTP_GET_VARS['m_0'])))) : array());

	$specials = array();
	$specials_periods = array();
	$specials_count_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_path <> ''");
	$specials_count = tep_db_fetch_array($specials_count_query);
	for ($i=0; $i<$specials_count['total']; $i++) {
	  if (tep_not_null($HTTP_GET_VARS['s_' . $i])) {
		$specials[] = tep_string_to_int(urldecode($HTTP_GET_VARS['s_' . $i]));
		$specials_period = urldecode($HTTP_GET_VARS['sp_' . $i]);
		if (!in_array($specials_period, array('w', '2w', 'm', 'h'))) $specials_period = 'w';
		$specials_periods[] = $specials_period;
	  }
	}

	$ff = urldecode($HTTP_GET_VARS['ff']);
	if ($ff!='xml' && $ff!='csv') $ff = 'xml';

	$status = urldecode($HTTP_GET_VARS['st']);
	if ($status!='all' && $status!='active') $status = 'active';

	$price_from = urldecode($HTTP_GET_VARS['pf']);
	$price_from = abs((float)str_replace(',', '.', $price_from));
	$price_from = $price_from/$currencies->currencies[$currency]['value'];

	$price_to = urldecode($HTTP_GET_VARS['pt']);
	$price_to = abs((float)str_replace(',', '.', $price_to));
	$price_to = $price_to/$currencies->currencies[$currency]['value'];

	$year_from = urldecode($HTTP_GET_VARS['yf']);
	$year_from = abs((int)$year_from);

	$year_to = urldecode($HTTP_GET_VARS['yt']);
	$year_to = abs((int)$year_to);

	$compression_method = urldecode($HTTP_GET_VARS['cm']);
	if ($compression_method=='none' || !in_array($compression_method, array_keys($comp_methods))) $compression_method = '';

	$type_info_query = tep_db_query("select products_types_id, products_last_modified from " . TABLE_PRODUCTS_TYPES . " where products_types_status = '1'" . (tep_not_null($HTTP_GET_VARS['type']) ? " and products_types_path = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['type'])) . "'" : " and products_types_default_status = '1'") . " limit 1");
	if (tep_db_num_rows($type_info_query) < 1) {
	  tep_exit();
	} else {
	  $type_info = tep_db_fetch_array($type_info_query);
	  $products_types_id = $type_info['products_types_id'];
	  $products_last_modified = strtotime($type_info['products_last_modified']);
	}

	$select_string_select = "select distinct p.products_id";
	$select_string_from = " from " . TABLE_PRODUCTS_INFO . " p";
	$select_string_where = " where p.products_types_id = '" . (int)$products_types_id . "' and p.categories_id <> '4990' and p.products_status = '1'" . ($status=='active' ? " and p.products_listing_status = '1'" : "") . ($price_from>0 ? " and p.products_price >= '" . $price_from . "'" : " and p.products_price > '0'") . ($price_to>0 ? " and p.products_price <= '" . $price_to . "'" : "") . ($year_from>0 ? " and p.products_year >= '" . $year_from . "'" : "") . ($year_to>0 ? " and p.products_year <= '" . $year_to . "'" : "");

	if (sizeof($categories) > 0) {
	  $subcategories_array = array();
	  reset($categories);
	  while (list(, $category_id) = each($categories)) {
		$subcategories_array[] = $category_id;
		tep_get_subcategories($subcategories_array, $category_id);
	  }
	  $select_string_where .= " and p.categories_id in ('" . implode("', '", $subcategories_array) . "')";
	} else {
	  $disabled_categories = array();
	  $type_categories_check_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types_id . "'");
	  $type_categories_check = tep_db_fetch_array($type_categories_check_query);
	  if ($type_categories_check['categories_id'] > 0) $active_categories = array();
	  else $active_categories = array('0');
	  $categories_query = tep_db_query("select categories_id, categories_xml_status from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types_id . "' and categories_status = '1' order by parent_id");
	  while ($categories = tep_db_fetch_array($categories_query)) {
		if ($categories['categories_xml_status'] < 1 && !in_array($categories['categories_id'], $disabled_categories)) {
		  $disabled_categories[] = $categories['categories_id'];
		  tep_get_subcategories($disabled_categories, $categories['categories_id']);
		} elseif (!in_array($categories['categories_id'], $disabled_categories)) {
		  if (!in_array($categories['categories_id'], $active_categories)) $active_categories[] = $categories['categories_id'];
		}
	  }
	  $select_string_where .= " and p.categories_id > '0'";
	  if (sizeof($disabled_categories) > 0) {
		$select_string_where .= " and p.categories_id not in ('" . implode("', '", $disabled_categories) . "')";
	  }
	}
//	if ($customer_id==2) { echo $select_string_where; die; }

	$manufacturers_array = array();
	unset($manufacturers_string);
	if (sizeof($manufacturers) > 0) {
	  $manufacturers_string = '';
	  reset($manufacturers);
	  while (list(, $manufacturer_name) = each($manufacturers)) {
		$manufacturer_name = tep_db_prepare_input(preg_replace('/\s+/', ' ', $manufacturer_name));
		if (tep_not_null($manufacturer_name)) {
		  $manufacturers_string .= (tep_not_null($manufacturers_string) ? " or " : "") . "(manufacturers_name like '%" . str_replace(' ', "%' and manufacturers_name like '%", $manufacturer_name) . "%')";
		}
	  }
	  unset($manufacturers_products);
	  if ($products_types_id==1) {
		$manufacturers_query = tep_db_query("select distinct manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where " . $manufacturers_string . "");
		while ($manufacturers_row = tep_db_fetch_array($manufacturers_query)) {
		  $manufacturers_array[] = $manufacturers_row['manufacturers_id'];
		}
//		$select_string_where .= " and p.manufacturers_id in ('" . implode("', '", $manufacturers_array) . "')";
		$manufacturers_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_status = '1' and manufacturers_id in ('" . implode("', '", $manufacturers_array) . "')");
		while ($manufacturers_products_array = tep_db_fetch_array($manufacturers_products_query)) {
		  $manufacturers_products[] = $manufacturers_products_array['products_id'];
		}
		$select_string_where .= " and p.products_id in ('" . implode("', '", $manufacturers_products) . "')";
	  } else {
		$select_string_where .= " and (" . str_replace('manufacturers_name', 'p.manufacturers_name', $manufacturers_string) . ")";
	  }
	}

	if (sizeof($specials) > 0) {
	  $specials_products = array();
	  reset($specials);
	  while (list($j, $specials_type_id) = each($specials)) {
		if ($specials_periods[$j]=='h') $period = time() - 60*60*24*183;
		elseif ($specials_periods[$j]=='m') $period = time() - 60*60*24*30;
		elseif ($specials_periods[$j]=='2w') $period = time() - 60*60*24*14;
		else $period = time() - 60*60*24*7;
//		$select_string_from .= ", " . TABLE_SPECIALS . " s" . $j . "";
//		$select_string_where .= " and p.products_id = s" . $j . ".products_id and s" . $j . ".status = '1' and s" . $j . ".specials_types_id = '" . $specials_type_id . "' and date_format(s" . $j . ".specials_date_added, '%Y-%m-%d') >= '" . date('Y-m-d', $period) . "'";
		$specials_products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where status = '1' and specials_types_id = '" . $specials_type_id . "' and date_format(specials_date_added, '%Y-%m-%d') >= '" . date('Y-m-d', $period) . "'");
		while ($specials_products_array = tep_db_fetch_array($specials_products_query)) {
		  $specials_products[] = $specials_products_array['products_id'];
		}
		$select_string_where .= " and p.products_id in ('" . implode("', '", $specials_products) . "')";
	  }
	}

	if (strpos($for, 'amazon')!==false) {
	  $select_string_where .= " and p.products_covers_id > '0' and p.products_year > '0' and p.products_image_exists = '1' and p.products_formats_id > '0'";
	} elseif ($for=='ebay' || $for=='nur_kz') {
	  $select_string_where .= " and p.products_image_exists = '1'";
	}

	$all_categories = array();
	if ($for=='shopmania') $separator = '|';
	elseif (strpos($for, 'amazon')!==false) $separator = "\t";
	elseif ($for=='ebay') $separator = ',';
	else $separator = ';';

	$limit_string = "";
//	$select_string = "select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$products_types_id . "' and products_price > '0' and products_status = '1'" . ($status=='all' ? "" : " and products_listing_status = '1'") . ($price_from>0 ? " and products_price >= '" . $price_from . "'" : "") . ($price_to>0 ? " and products_price <= '" . $price_to . "'" : "") . (isset($products_to_load)>0 ? " and products_id in ('" . implode("', '", $products_to_load) . "')" : "") . (isset($manufacturers_string) ? " and manufacturers_id in ('" . implode("', '", $manufacturers_array) . "')" : "") . "";
	$select_string = $select_string_select . $select_string_from . $select_string_where;
	if (tep_not_null($HTTP_GET_VARS['limit'])) {
	  $limit_query = urldecode($HTTP_GET_VARS['limit']);
	  list($limit_from, $limit_to) = explode(',', $limit_query);
	  $limit_from = (float)trim($limit_from);
	  $limit_to = (float)trim($limit_to);
	  if ($limit_from>=0 && $limit_from<1 && $limit_to>0 && $limit_to<=1) {
		$products_count_query_raw = str_replace('select distinct p.products_id from', 'select count(distinct p.products_id) as total from', $select_string);
		$products_count_query_raw = str_replace("from " . TABLE_PRODUCTS . " p", "from " . TABLE_PRODUCTS_INFO . " p", $select_string);
		$products_count_query = tep_db_query($products_count_query_raw);
//		$products_count_row = tep_db_fetch_array($products_count_query);
		$products_count = tep_db_num_rows($products_count_query);
		$limit_from = ceil($products_count * $limit_from);
		$limit_to = ceil($products_count * $limit_to);
		$limit_string = " limit " . $limit_from . ", " . ($limit_to - $limit_from);
		$select_string .= " group by p.products_id" . $limit_string;
	  } else {
		$select_string .= " group by p.products_id";
	  }
	} else {
	  $select_string .= " group by p.products_id";
	}

//	if ( (tep_not_null($eval_string) && sizeof($products_to_load)==0) || $products_query_numrows==0) {
//	  $messageStack->add('header', ENTRY_CORPORATE_FORM_PRODUCTS_FOUND_ERROR);
//	} else {
	  set_time_limit(0);
	  $pricelist_link = tep_href_link(FILENAME_PRICELIST, tep_get_all_get_params(array('result_uri', 'overwrite_existing_file')), 'NONSSL', false);
	  if (substr($pricelist_link, -5)=='&amp;') {
		do {
		  $pricelist_link = substr($pricelist_link, 0, -5);
		} while (substr($pricelist_link, -5)!='&amp;');
	  }
	  if (strpos($pricelist_link, '&amp;&amp;')!==false) {
		do {
		  $pricelist_link = str_replace('&amp;&amp;', '&amp;;', $pricelist_link);
		} while (strpos($pricelist_link, '&amp;&amp;')==false);
	  }
	  $pricelist_check_query = tep_db_query("select pricelists_id, pricelists_filename from " . TABLE_PRICELISTS . " where pricelists_url = '" . tep_db_input($pricelist_link) . "'");
	  if (tep_db_num_rows($pricelist_check_query) > 0) {
		$pricelist_check = tep_db_fetch_array($pricelist_check_query);
		$pricelist_filename = $pricelist_check['pricelists_filename'];
	  } else {
		tep_db_query("insert into " . TABLE_PRICELISTS . " (pricelists_url, date_added) values ('" . tep_db_input($pricelist_link) . "', now())");
		$pricelist_id = tep_db_insert_id();
		$pricelist_filename = 'prices/price' . $pricelist_id . '.' . (($ff=='csv' && strpos($for, 'amazon')!==false) ? 'txt' : $ff) . (tep_not_null($compression_method) ? '.' . $compression_method : '');
		tep_db_query("update " . TABLE_PRICELISTS . " set pricelists_filename = '" . tep_db_input($pricelist_filename) . "' where pricelists_id = '" . (int)$pricelist_id . "'");
	  }
	  if($HTTP_GET_VARS['file'] !== '') $pricelist_filename = $HTTP_GET_VARS['file'];

	  $name = basename($pricelist_filename);
	  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')!==false) $name = str_replace('.', '%2e', $name);

//	  tep_session_close();
//	  ob_end_clean();
	  header('HTTP/1.1 200 OK');
	  header('Expires: Mon, 26 Nov 1962 00:00:00 GMT');
	  header('Last-Modified: ' . gmdate('D,d M Y H:i:s', $products_last_modified) . ' GMT');
	  if ($_SERVER['REQUEST_METHOD'] =='HEAD') { tep_exit(); }
	  header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
	  header('Pragma: no-cache');
	  header('Content-Description: File Transfer');
	  header('Content-Type: application/force-download');
	  header('Content-Type: application/octet-stream');
	  header('Content-Type: application/download');
	  header('Content-Disposition: attachment; filename="' . $name . '"');
	  header('Content-Transfer-Encoding: binary');
	  header('Connection: Keep-Alive');
	  header('Keep-Alive: timeout=60, max=300'); 

	  if (file_exists($pricelist_filename) && $HTTP_GET_VARS['overwrite_existing_file']!='yes' && $customer_discount['type']!='purchase') {
		clearstatcache();
		if (filemtime($pricelist_filename) >= $products_last_modified) {
		  tep_db_query("update " . TABLE_PRICELISTS . " set last_modified = now(), pricelists_downloads_count = pricelists_downloads_count + 1 where pricelists_id = '" . (int)$pricelist_check['pricelists_id'] . "'");
		  header('Content-Length: ' . (string)(filesize($pricelist_filename)));
		  $fp = fopen($pricelist_filename, 'r');
		  while (($line = fgets($fp, 65536)) !== false) {
			echo $line;
		  }
		  fclose($fp);
//		  readfile($pricelist_filename);
		  die();
		} else {
		  unlink($pricelist_filename);
		}
	  }

/*
	  if (!isset($argv)) {
		system('php ' . DIR_FS_CATALOG . FILENAME_PRICELIST . ' ' . implode(' ', explode('&', tep_get_all_get_params(array(tep_session_name())))) . ' > /dev/null &');
		do {
		  sleep(10);
		} while (!is_readable($pricelist_filename));

		  sleep(5);
		  readfile($pricelist_filename);

//		if ($ff = fopen($pricelist_filename, 'r')) {
//		  fpassthru($fp);
//		  $k = 0;
//		  while( (!feof($ff)) && (connection_status()==0) ) {
//			echo fread($ff, 1024*8);
//			flush();
//		  }
//		  fclose($ff);
//		}
		die();
	  }
*/

	  //создаем массив символов которые будем менять
	  $from = array('<', '>', '&', '"', '&#34;', '&#60;', '&#62;', '&#034;', '&#060;', '&#062;', "\r\n");
	  $from1 =  array('&amp;lt;', '&amp;gt;', '&amp;amp;', '&amp;quot;', '&amp;quot;', '&amp;lt;', '&amp;gt;', '&amp;quot;', '&amp;lt;', '&amp;gt;', '&amp;#039;', ' ');
	  //создаем массив символов на которые будем менять
	  $to =  array('&lt;', '&gt;', '&amp;', '&quot;', '&quot;', '&lt;', '&gt;', '&quot;', '&lt;', '&gt;', '&#039;', ' ');

	  unset($pricelist_currency);

	  $categories_audio = array();
	  tep_get_subcategories($categories_audio, 1104);

	  if ($customer_discount['type']=='purchase' && empty($for)) $fp = false;
	  else $fp = fopen($pricelist_filename, 'wb');
	  if ($ff=='csv') {
		if ($for=='shopmania') {
		  $fields_array = array('categories_name' => 'Категория',
								'manufacturers_name' => 'Изготовитель',
								'products_model' => 'Модель',
								'products_id' => 'Торговый Код',
								'products_name' => 'Имя продукта',
								'products_description' => 'Описание продукции',
								'products_url' => 'URL продукта',
								'products_image' => 'URL изображения продукта',
								'products_price' => 'Цена',
								'products_currency' => 'Валюта');
		  $fields = array_keys($fields_array);
		} elseif ($for=='nur_kz') {
//		Категория	Название товара	Производитель	Цена	Количество на складе	Ссылка на фотографию	Ссылка для покупки товара	Краткое описание	Полное описание	Ссылка на фотографию (уменьшенное фото)	Активность(товар активен если поле не пустое)
		  $fields_array = array('categories_name' => 'Категория',
								'products_name' => 'Название товара',
								'manufacturers_name' => 'Производитель',
								'products_price' => 'Цена',
								'value::100' => 'Количество на складе',
								'products_image_big' => 'Ссылка на фотографию',
								'products_url' => 'Ссылка для покупки товара',
//								'products_buy_now' => 'Ссылка для покупки товара',
								'products_description_short' => 'Краткое описание',
								'products_description' => 'Полное описание',
								'products_image' => 'Ссылка на фотографию (уменьшенное фото)',
								'value::1' => 'Активность(товар активен если поле не пустое)');
		  $fields = array_keys($fields_array);
		  reset($fields);
		  $temp_array = array();
		  while (list(, $field_id) = each($fields)) {
//			if ($field_id=='products_price') $temp_array[] = $fields_array[$field_id] . ' (' . DEFAULT_CURRENCY . ')';
//			else 
			$temp_array[] = $fields_array[$field_id];
		  }
		  write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
		} elseif (strpos($for, 'amazon')!==false) {
		  if ($for=='amazon_uk') {
			$fields_array = array(
						  'products_id' => 'sku',
						  'authors_name' => 'Author',
						  'products_name' => 'Title',
						  'manufacturers_name' => 'publisher',
						  'products_year' => 'pub-date',
						  'products_covers_name' => 'Binding',
						  'products_price' => 'Price',
						  'products_model' => 'product-id',
						  'value::"2"' => 'Product-id-type',
						  'value::100' => 'Quantity',
						  'value::1' => 'Item-condition',
						  'value::"In fact it\'s a new product! NOTE: The book is in Russian!!! Will be delivered within 2-3 weeks over Europe and within 3-4 weeks elsewhere. Please note that ordered books are shipped out from our warehouse in Moscow to our warehouse in Germany and then they are dispatched from Germany to the customers. Because we have to clear customs, some orders may take longer to reach the customers."' => 'Item-note',
						  'value::6' => 'Will-ship-internationally',
						  'value::N' => 'Expedited-shipping',
						  'products_image' => 'Main-image-url',
						  '09' => 'Package-height',
						  '10' => 'Package-width', #'products_width' = 'Package-width',
						  '11' => 'Package-length', #'products_height' = 'Package-length',
						  '12' => 'Package-length-unit-of-measure', #'products_width_height_measure' => 'Package-length-unit-of-measure',
		 				  'products_weight' => 'Package-weight',
						  'value::kg' => 'Package-weight-unit-of-measure',
						  'value::russian' => 'Language',
						  '13' => 'Illustrator',
						  '14' => 'Edition',
						  '15' => 'Subject',
						  'value::a' => 'Add-delete',
						  '17' => 'fulfillment-center-id',
						  );
		  } else {
			$fields_array = array(
						  'products_id' => 'sku',
						  'authors_name' => 'Author',
						  'products_name' => 'Title',
						  'manufacturers_name' => 'publisher',
						  'products_year' => 'pub-date',
						  'products_covers_name' => 'Binding',
						  'products_price' => 'Price',
						  'products_model' => 'product-id',
						  'value::"2"' => 'Product-id-type',
						  'value::100' => 'Quantity',
						  'value::11' => 'Item-condition',
						  '02' => 'Item-note',
						  '03' => 'Expedited-shipping',
						  'value::2' => 'Will-ship-internationally',
						  'products_image' => 'Main-image-url',
						  '04' => 'main-offer-image',
						  '05' => 'offer-image1',
						  '06' => 'offer-image2',
						  '07' => 'offer-image3',
						  '08' => 'offer-image4',
						  '09' => 'Package-height',
						  '10' => 'Package-width', #'products_width' = 'Package-width',
						  '11' => 'Package-length', #'products_height' = 'Package-length',
						  '12' => 'Package-length-unit-of-measure', #'products_width_height_measure' => 'Package-length-unit-of-measure',
		 				  'products_weight' => 'Package-weight',
						  'value::kg' => 'Package-weight-unit-of-measure',
						  'value::russian' => 'Language',
						  '13' => 'Illustrator',
						  '14' => 'Edition',
						  '15' => 'Subject',
						  'value::a' => 'Add-delete',
						  '17' => 'fulfillment-center-id',
						  '18' => 'Dust-jacket',
						  '19' => 'Signed-by',
						  );
		  }
		  $fields = array_keys($fields_array);
		  reset($fields_array);
		  $temp_array = array();
		  while (list($field_id, $field_name) = each($fields_array)) {
			$temp_array[] = $field_name;
		  }
		  write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
		} elseif ($for=='ebay') {
		  $fields_array = array('value::Add' => '*Action(SiteID=US|Country=US|Currency=USD|Version=403|CC=UTF-8)',
								'20' => 'Product:UPC',
								'products_model' => 'Product:ISBN',
								'00' => 'Product:ProductReferenceID',
								'01' => 'Product:IncludePreFilledItemInformation',
								'02' => 'Product:IncludeStockPhotoURL',
								'03' => 'Product:ReturnSearchResultsOnDuplicates',
								'products_name' => 'Title',
								'products_description' => 'Description',
								'value::1000' => '*ConditionID',
								'products_image' => 'PicURL',
								'value::100' => '*Quantity',
								'value::StoresFixedPrice' => '*Format',
								'products_price' => '*StartPrice',
								'04' => 'BuyItNowPrice',
								'05' => 'ReservePrice',
								'value::30' => '*Duration',
								'06' => 'ImmediatePayRequired',
								'value::Boston,MA,USA' => '*Location',
								'07' => 'GalleryType',
								'value::1' => 'PayPalAccepted',
								'value::claudia.lokshina@gmail.com' => 'PayPalEmailAddress',
								'value::- Book must be returned within 3 days. - Refund will be given as MoneyBack. - Seller pays for return shipping.' => 'PaymentInstructions',
								'categories_name' => 'StoreCategory',
								'09' => 'ShippingDiscountProfileID',
								'10' => 'ShippingService-1:Option',
								'11' => 'ShippingService-1:Cost',
								'12' => 'ShippingService-1:Priority',
								'13' => 'ShippingService-1:ShippingSurcharge',
								'14' => 'ShippingService-2:Option',
								'15' => 'ShippingService-2:Cost',
								'16' => 'ShippingService-2:Priority',
								'17' => 'ShippingService-2:ShippingSurcharge',
								'value::10' => '*DispatchTimeMax',
								'18' => 'CustomLabel',
								'value::ReturnsAccepted' => '*ReturnsAcceptedOption',
								'value::MoneyBack' => 'RefundOption',
								'value::Days_3' => 'ReturnsWithinOption',
								'value::Seller' => 'ShippingCostPaidBy',
								'19' => 'AdditionalDetails',
								);
		  $fields = array_keys($fields_array);
		  reset($fields_array);
		  $temp_array = array();
		  while (list($field_id, $field_name) = each($fields_array)) {
			$temp_array[] = $field_name;
		  }
		  write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
		} else {
		  reset($fields);
		  $temp_array = array();
		  while (list(, $field_id) = each($fields)) {
			if ($field_id=='products_price') $temp_array[] = $fields_array[$field_id] . ' (' . DEFAULT_CURRENCY . ')';
			else $temp_array[] = $fields_array[$field_id];
		  }
		  write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
		}
		$products_query = tep_db_query($select_string);
		$products_query_numrows = tep_db_num_rows($products_query);
		while ($products = tep_db_fetch_array($products_query)) {
		  $product_info = tep_get_full_product_info($products['products_id'], $ff);
		  if (strpos($for, 'amazon')!==false) {
			if (strlen($product_info['products_model']) > 14 || 
//				$product_info['products_width']=='' || 
//				$product_info['products_width']=='' || 
				strlen($product_info['products_model']) < 9
				) $product_info = array();
		  }
		  if (sizeof($product_info) > 0) {
			reset($fields);
			$temp_array = array();
			while (list(, $field_id) = each($fields)) {
			  switch ($field_id) {
				case 'products_currency':
				  $temp_array[] = str_replace('RUR', 'RUB', $product_info['products_currency']);
				  break;
				case 'products_id':
				  $temp_array[] = $product_info['products_id'];
				  break;
				case 'products_model':
				  $temp_array[] = $product_info['products_model'];
				  break;
				case 'products_name':
				  $temp_array[] = $product_info['products_name'];
				  break;
				case 'products_description':
				  $temp_array[] = $product_info['products_description'];
				  break;
				case 'products_description_short':
				  $temp_array[] = $product_info['products_description_short'];
				  break;
				case 'categories_name':
				  $temp_array[] = $product_info['categories_name'];
				  break;
				case 'authors_name':
				  $temp_array[] = $product_info['authors_name'];
				  break;
				case 'products_price':
				  $temp_array[] = $product_info['products_price'];
				  break;
				case 'manufacturers_name':
				  $temp_array[] = $product_info['manufacturers_name'];
				  break;
				case 'series_name':
				  $temp_array[] = $product_info['series_name'];
				  break;
				case 'products_pages_count':
				  $temp_array[] = $product_info['products_pages_count'];
				  break;
				case 'products_year':
				  $temp_array[] = $product_info['products_year'];
				  break;
				case 'products_copies':
				  $temp_array[] = $product_info['products_copies'];
				  break;
				case 'products_covers_name':
				  $temp_array[] = $product_info['products_covers_name'];
				  break;
				case 'products_formats_name':
				  $temp_array[] = $product_info['products_formats_name'];
				  break;
				case 'products_url':
				  $temp_array[] = $product_info['products_url'];
				  break;
				case 'products_image':
				  $temp_array[] = $product_info['products_image'];
				  break;
				case 'products_image_big':
				  $temp_array[] = $product_info['products_image_big'];
				  break;
				case 'products_buy_now':
				  $temp_array[] = $product_info['products_buy'];
				  break;
				case 'products_quantity':
				  $temp_array[] = $product_info['products_quantity'];
				  break;
				case 'products_weight':
				  $temp_array[] = $product_info['products_weight'];
				  break;
				case 'products_width':
				  $temp_array[] = $product_info['products_width'];
				  break;
				case 'products_height':
				  $temp_array[] = $product_info['products_height'];
				  break;
				case 'products_width_height_measure':
				  $temp_array[] = $product_info['products_width_height_measure'];
				  break;
				default:
				  $field_value = '';
				  if (substr($field_id, 0, 7)=='value::') {
					list(, $field_value) = explode('value::', $field_id);
					$field_value = preg_replace('/^"2"$/', '2', $field_value);
				  }
				  $temp_array[] = $field_value;
				  break;
			  }
			}
			if ($for=='shopmania') {
			  $string = implode($separator, $temp_array) . "\n";
			  echo $string;
			  fwrite($fp, $string);
			} elseif (strpos($for, 'amazon')!==false) {
			  clearstatcache();
			  $string = implode($separator, $temp_array) . "\n";
			  echo $string;
			  fwrite($fp, $string);
			} elseif ($for=='ebay') {
			  clearstatcache();
			  if (filesize($pricelist_filename) > '1500000') {
				fclose($fp);
				tep_exit();
			  } else {
				write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
			  }
			} else {
			  if ($for=='nur_kz' && empty($temp_array[8])) {
			  } else {
				write_to_file($pricelist_filename, $fp, tep_get_csv_string($temp_array, $separator));
			  }
			}
		  }
		}
	  } else {
		if ($for=='cenometr') {
		  $content = '<?xml version="1.0" encoding="windows-1251"?>' . "\n".
		  '<!DOCTYPE cenometr SYSTEM "cenometr.dtd">' . "\n" .
		  '<cenometr date="' . date('Y-m-d H:i', $products_last_modified) . '">' . "\n" .
		  '  <shop>' . "\n" .
		  '	<name>' . str_replace('&amp;amp;', '&amp;', str_replace($from, $to, STORE_NAME)) . '</name>' . "\n" .
		  '	<url>' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false) . '</url>' . "\n" .
		  '	<logo>' . HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_TEMPLATES . 'images/logo.gif</logo>' . "\n" .
		  '	<offers>' . "\n";
		  write_to_file($pricelist_filename, $fp, $content);
		  $products_currency = 'RUR';
		} else {
		  $content = '<?xml version="1.0" encoding="windows-1251"?>' . "\n".
		  '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n" .
		  '<yml_catalog date="' . date('Y-m-d H:i', $products_last_modified) . '">' . "\n" .
		  '  <shop>' . "\n" .
		  '	<name>' . str_replace('&amp;amp;', '&amp;', str_replace($from, $to, STORE_NAME)) . '</name>' . "\n" .
		  '	<company>' . str_replace('&amp;amp;', '&amp;', str_replace($from, $to, STORE_OWNER)) . '</company>' . "\n" .
		  '	<url>' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false) . '</url>' . "\n" .
		  '	<currencies>' . "\n";
		  if ($currency=='UAH') {
			$products_currency = 'UAH';
			$curs_query = tep_db_query("select * from " . TABLE_CURRENCIES . " where code in ('" . $currency . "')");
			while ($curs = tep_db_fetch_array($curs_query)) {
			  $content .= '	  <currency id="' . $curs['code'] . '" rate="1" />' . "\n";
			}
		  } elseif (!in_array($currency, array('RUR', 'EUR', 'USD', 'UAH'))) {
			$products_currency = 'RUR';
			$curs_query = tep_db_query("select * from " . TABLE_CURRENCIES . " where code in ('RUR')");
			while ($curs = tep_db_fetch_array($curs_query)) {
			  $content .= '	  <currency id="' . $curs['code'] . '" rate="1" />' . "\n";
			}
		  } else {
			$products_currency = 'RUR';
			$curs_query = tep_db_query("select * from " . TABLE_CURRENCIES . " where code in ('RUR', '" . $currency . "')");
			while ($curs = tep_db_fetch_array($curs_query)) {
			  $content .= '	  <currency id="' . $curs['code'] . '" rate="' . str_replace(',', '.', round(1/$curs['value'], 4)) . '" />' . "\n";
			}
		  }
		  $content .= '	</currencies>' . "\n" .
		  '	<categories>' . "\n";
		  write_to_file($pricelist_filename, $fp, $content);

		  $xml_categories_query = tep_db_query("select concat_ws('', '<category id=\"', c.categories_id, '\" parentId=\"', c.parent_id, '\">', cd.categories_name, '</category>') as categories_string from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '" . (int)$products_types_id . "' and c.categories_status = '1' and c.categories_xml_status = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		  while ($xml_categories = tep_db_fetch_array($xml_categories_query)) {
			write_to_file($pricelist_filename, $fp, $xml_categories['categories_string'] . "\n");
		  }

		  $content = '	</categories>' . "\n" .
		  '	<offers>' . "\n";
		  write_to_file($pricelist_filename, $fp, $content);
		}

		$temp_filename = UPLOAD_DIR . 'csv/products_' . substr(uniqid(rand()), 0, 10) . '.xml';
		$currency_decimal_places = $currencies->get_decimal_places($products_currency);
		$currency_value = $currencies->get_value($products_currency);
		if ($products_types_id==1) {
		  if ($for=='cenometr') {
			$xml_string = "concat_ws('', '<offer><name>', p.products_name, '</name><url>', '" . HTTP_SERVER . "', p.products_url, '</url><picture>', if(p.products_image,concat_ws('','http://85.236.24.26/thumbs/',p.products_image),''), '</picture><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><barcode>', replace(group_concat(p2m.products_model_1), ',', '</barcode><barcode>'), '</barcode></offer>') as products_string";
			$select_string = str_replace(" from " . TABLE_PRODUCTS_INFO . " p", " from " . TABLE_PRODUCTS_INFO . " p, " . TABLE_PRODUCTS_TO_MODELS . " p2m", $select_string);
			$select_string = str_replace(" and p.products_status = '1'", " and p.categories_id > '0' and p.products_status = '1' and p.products_id = p2m.products_id", $select_string);
		  } else {
//			if ($languages_id!=DEFAULT_LANGUAGE_ID) {
//			  $xml_string = "concat_ws('', '<offer id=\"', p.products_id, '\" type=\"book\" available=\"', if((p.products_listing_status=1" . (tep_not_null($HTTP_GET_VARS['limit']) ? " and p.products_available_in<=2" : "") . "),'true','false'), '\"><url>', '" . HTTP_SERVER . "', p.products_url, '</url><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><currencyId>" . $products_currency . "</currencyId><categoryId>', p.categories_id, '</categoryId><picture>', if(p.products_image,concat_ws('','" . HTTP_SERVER . DIR_WS_IMAGES . "thumbs/',p.products_image),''), '</picture><delivery>true</delivery><author>', a.authors_name, '</author><name>', pd.products_name, '</name><publisher>', mi.manufacturers_name, '</publisher><series>', s.series_name, '</series><year>', p.products_year, '</year><ISBN>', p.products_model, '</ISBN><language>" . $language . "</language><binding>', p.products_formats_name, '</binding><page_extent>', p.products_pages_count, '</page_extent><description>', replace(pd.products_description,'\n',if((locate(pd.products_description, '<br')>0 or locate(pd.products_description, '<p')>0),' ','<br />')), '</description>" . (!in_array(DOMAIN_ZONE, array('ru', 'ua', 'by', 'kz')) ? "<sales_notes>отправка по факту оплаты</sales_notes>" : "") . "<downloadable>false</downloadable></offer>') as products_string";
//			} else {
			  $xml_string = "concat_ws('', '<offer id=\"', p.products_id, '\" type=\"book\" available=\"', if((p.products_listing_status=1" . (tep_not_null($HTTP_GET_VARS['limit']) ? " and p.products_available_in<=2" : "") . "),'true','false'), '\"><url>', '" . HTTP_SERVER . "', p.products_url, '</url><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><currencyId>" . $products_currency . "</currencyId><categoryId>', p.categories_id, '</categoryId><picture>', if(p.products_image,concat_ws('','http://85.236.24.26/thumbs/',p.products_image),''), '</picture><delivery>true</delivery><author>', p.authors_name, '</author><name>', p.products_name, '</name><publisher>', p.manufacturers_name, '</publisher><series>', p.series_name, '</series><year>', p.products_year, '</year><ISBN>', p.products_model, '</ISBN><language>" . $language . "</language><binding>', p.products_formats_name, '</binding><page_extent>', p.products_pages_count, '</page_extent><description>', replace(p.products_description,'\n',if((locate(products_description, '<br')>0 or locate(products_description, '<p')>0),' ','<br />')), '</description>" . (!in_array(DOMAIN_ZONE, array('ru', 'ua', 'by', 'kz')) ? "<sales_notes>отправка по факту оплаты</sales_notes>" : "") . "<downloadable>false</downloadable></offer>') as products_string";
//			}
		  }
		} else {
		  $xml_string = "concat_ws('', '<offer id=\"', p.products_id, '\" available=\"', if((p.products_listing_status=1" . (tep_not_null($HTTP_GET_VARS['limit']) ? " and p.products_available_in<=2" : "") . "),'true','false'), '\"><url>', '" . HTTP_SERVER . "', p.products_url, '</url><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><currencyId>" . $products_currency . "</currencyId><categoryId>', p.categories_id, '</categoryId><picture>', if(p.products_image,concat_ws('','http://85.236.24.26/thumbs/',p.products_image),''), '</picture><delivery>true</delivery><name>', p.products_name, '</name><vendor>', p.manufacturers_name, '</vendor><description>', replace(p.products_description,'\n',if((locate(products_description, '<br')>0 or locate(products_description, '<p')>0),' ','<br />')), '</description>" . (!in_array(DOMAIN_ZONE, array('ru', 'ua', 'by', 'kz')) ? "<sales_notes>отправка по факту оплаты</sales_notes>" : "") . "<downloadable>', if((p.products_filename is null), 'false', 'true'), '</downloadable></offer>') as products_string";
		}
		$xml_query_row = str_replace("select distinct p.products_id from " . TABLE_PRODUCTS_INFO . " p", "select " . $xml_string . " from " . TABLE_PRODUCTS_INFO . " p", $select_string);
		$xml_query_row = str_replace("where ", "where 1 and p.categories_id not in ('" . implode("','", $categories_audio) . "') and ", $xml_query_row);
		if (strpos($xml_query_row, 'order by')!==false) $xml_query_row = substr($xml_query_row, 0, strpos($xml_query_row, 'order by'));
		if (strpos($xml_query_row, ' limit ')===false) $xml_query_row .= $limit_string;
//		write_to_file($pricelist_filename, $fp,  $xml_query_row . " into outfile '" . $temp_filename . "'"); die;
//		if ($customer_id==2) { echo $xml_query_row; die; }

//		echo $xml_query_row; die;
		$query = tep_db_query($xml_query_row);
		while ($row = tep_db_fetch_array($query)) {
		  $t_str = $row['products_string'];
		  $t_str = preg_replace('/<series>(.*)<\/series>/ie', "'<series>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</series>'", $t_str);
		  $t_str = preg_replace('/<description>(.*)<\/description>/ie', "'<description>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</description>'", $t_str);
		  $t_str = preg_replace('/<name>(.*)<\/name>/ie', "'<name>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</name>'", $t_str);
		  write_to_file($pricelist_filename, $fp, $t_str . "\n");
		}

		if ($for=='cenometr') {
		  $content = '	</offers>' . "\n" .
		  '  </shop>' . "\n" .
		  '</cenometr>' . "\n";
		} else {
		  $content = '	</offers>' . "\n" .
		  '  </shop>' . "\n" .
		  '</yml_catalog>' . "\n";
		}
		write_to_file($pricelist_filename, $fp, $content);
	  }
	  if ($fp) fclose($fp);
	  tep_exit();
//	}
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>