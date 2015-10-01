<?php
  if (!isset($show_listing_string)) $show_listing_string = true;
  if (!isset($show_filterlist_string)) $show_filterlist_string = true;

  $imhonet_authors = array();
  $imhonet_file_contents = array();
  if ($fp = @fopen(UPLOAD_DIR . 'csv/imhonet_authors.csv', 'r')) {
	while (!feof($fp)) {
	  $imhonet_file_contents[] = trim(fgets($fp, 1028));
	}
	fclose($fp);
	reset($imhonet_file_contents);
	while (list(, $imhonet_authors_line) = each($imhonet_file_contents)) {
	  list($imhonet_authors_id, $imhonet_authors_link) = explode(';', $imhonet_authors_line);
	  $imhonet_authors_id = (int)trim($imhonet_authors_id);
	  $imhonet_authors_link = str_replace('http://', '', trim($imhonet_authors_link));
	  if ($imhonet_authors_id > 0) $imhonet_authors[$imhonet_authors_id] = $imhonet_authors_link;
	}
  }

  $holiday_products = array();
  if (is_array($holiday_products_array) && $languages_id==DEFAULT_LANGUAGE_ID) {
	reset($holiday_products_array);
	while (list(, $holiday_info) = each($holiday_products_array)) {
	  $holiday_info_products = $holiday_info['products'];
	  $holiday_info_categories = $holiday_info['categories'];
	  if (tep_not_null($holiday_info_products)) {
		$holiday_temp_products = array_map('trim', explode(',', $holiday_info_products));
		$holiday_temp_products = array_map('tep_string_to_int', $holiday_temp_products);
		$holiday_products = array_merge($holiday_products, $holiday_temp_products);
	  }
	  if (tep_not_null($holiday_info_categories)) {
		$categories_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in (" . $holiday_info_categories . ")");
		while ($categories_products = tep_db_fetch_array($categories_products_query)) {
		  if (!in_array($categories_products['products_id'], $holiday_products)) $holiday_products[] = $categories_products['products_id'];
		}
	  }
	}
  }
  $holiday_products = array();

  $listing_number_of_rows = '';
  $search_params_array = array();

  $special_types_full_array = array();
  $special_types_query = tep_db_query("select specials_types_id, specials_types_short_name, specials_types_image from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_image <> '' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name");
  while ($special_types = tep_db_fetch_array($special_types_query)) {
	$special_types_full_array[$special_types['specials_types_id']] = tep_image(DIR_WS_IMAGES . $special_types['specials_types_image'], $special_types['specials_types_short_name']);
  }

  $show_category_id = 0;
  if (basename(SCRIPT_FILENAME)==FILENAME_SPECIALS || $show_product_type > 1) {
	if (tep_not_null($HTTP_GET_VARS['categories_id'])) $show_category_id = $HTTP_GET_VARS['categories_id'];
	elseif ($current_category_id > 0) $show_category_id = $current_category_id;
	$show_subcategories_products = true;
  } elseif (isset($HTTP_GET_VARS['categories_id']) && tep_not_null($HTTP_GET_VARS['categories_id'])) {
	$subcategories_array = array($HTTP_GET_VARS['categories_id']);
//	if (isset($HTTP_GET_VARS['inc_subcat']) && ($HTTP_GET_VARS['inc_subcat'] == '1')) {
	  $show_subcategories_products = true;
//	}
	$show_category_id = (int)$HTTP_GET_VARS['categories_id'];
  } elseif ($show_listing_string==true) {
	$show_category_id = $current_category_id;
  }
  if ($show_category_id > 0) $search_params_array['categories_id'] = $show_category_id;

  $specials_products = array();
  $subcategories_products = array();

  $max_specials_date_query = tep_db_query("select max(specials_date_added) as specials_date_added from " . TABLE_SPECIALS . " where status = '1'");
  $max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
  $max_specials_date = strtotime($max_specials_date_row['specials_date_added']);
  $min_specials_date_added = date('Y-m-d', $max_specials_date-60*60*24*7);

  $listing_sql_select = "select p.products_id";
  $listing_sql_from = " from " . TABLE_PRODUCTS . " p";
  $listing_sql_where = " where p.products_status = '1'" . (((int)PRODUCT_SHOW_NONACTIVE=='0' && basename(PHP_SELF)!=FILENAME_SHOPPING_CART) ? " and products_listing_status = '1'" : "") . (strlen($show_product_type)>0 ? " and products_types_id = '" . (int)$show_product_type . "'" : " and products_types_id in ('" . implode("', '", $active_products_types_array) . "')");
  $listing_sql_group_by = "";
  if ($show_product_type > 0) $search_params_array['products_types_id'] = $show_product_type;

// create column list
  $define_list = array('PRODUCT_LIST_SORT_ORDER' => 0,
					   'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
					   'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
					   'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
					   'PRODUCT_LIST_AUTHOR' => PRODUCT_LIST_AUTHOR,
					   'PRODUCT_LIST_YEAR' => PRODUCT_LIST_YEAR,
					   'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
					   'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
					   'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
					   'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
					   'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW,
					   'PRODUCT_LIST_DATE_ADDED' => PRODUCT_LIST_DATE_ADDED);

  asort($define_list);

  if ($show_product_type > 1) {
	unset($define_list['PRODUCT_LIST_MODEL']);
	unset($define_list['PRODUCT_LIST_AUTHOR']);
	unset($define_list['PRODUCT_LIST_YEAR']);
  }

  $max_value = 0;
  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
	if ($value > 0) {
	  $column_list[] = $key;
	  if ($value > $max_value) $max_value = $value;
	}
  }

  $products_in_cart = array();
  $cart_products = $cart->get_product_id_list();
  if (tep_not_null($cart_products)) $products_in_cart = explode(', ', $cart_products);

  $products_in_postpone_cart = array();
  $postpone_cart_products = $postpone_cart->get_product_id_list();
  if (tep_not_null($postpone_cart_products)) $products_in_postpone_cart = explode(', ', $postpone_cart_products);

  $select_column_list = '';

  if (isset($HTTP_GET_VARS['sort']) && preg_match('/[1-8][ad]/', $HTTP_GET_VARS['sort'])) $sort = $HTTP_GET_VARS['sort'];
  if (tep_not_null($sort) && !tep_session_is_registered('sort')) tep_session_register('sort');

  if (!isset($per_page)) $per_page = 10;
  if (isset($HTTP_GET_VARS['per_page']) && in_array($HTTP_GET_VARS['per_page'], array(10, 25, 50, 100))) $per_page = $HTTP_GET_VARS['per_page'];
  if (!tep_session_is_registered('per_page')) tep_session_register('per_page');
//  echo '"' . $sort . '"';

  if (is_numeric(substr($sort, 0, 1)) && substr($sort, 0, 1) <= sizeof($column_list)) {
	$sort_col = substr($sort, 0 , 1);
  } elseif (tep_not_null($sort_col)) {
  } else {
	$sort_col = $define_list['PRODUCT_LIST_SORT_ORDER'];
  }

  if (tep_not_null($sort) && preg_match('/[1-8][ad]/', $sort)) {
	$sort_order = substr($sort, 1);
  } elseif (tep_not_null($sort_order) && preg_match('/[ad]/', $sort_order)) {
  } else {
	$sort_order = 'a';
  }

  $list_type = 1;//Категория
  $entity_id = $current_category_id;
  if (basename(SCRIPT_FILENAME)==FILENAME_MANUFACTURERS || tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
	if (tep_not_null($HTTP_GET_VARS['manufacturers_id'])) $manufacturers_id = $HTTP_GET_VARS['manufacturers_id'];
	$listing_sql_where .= " and p.manufacturers_id = '" . (int)$manufacturers_id . "'";
	$search_params_array['manufacturers_id'] = $manufacturers_id;
	$list_type = 4;//Издательство
	$entity_id = (int)$manufacturers_id;
  } elseif (tep_not_null($HTTP_GET_VARS['manufacturers'])) {
    $manufacturers = stripslashes(trim(htmlspecialchars(strip_tags(urldecode($HTTP_GET_VARS['manufacturers'])), ENT_QUOTES)));
	$manufacturers_to_search_array = array_map('trim', explode(',', $manufacturers));
	$manufacturers_array = array();
	reset($manufacturers_to_search_array);
	while (list(, $manufacturer) = each($manufacturers_to_search_array)) {
	  $manufacturers_info_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_name like '%" . str_replace(' ', "%' and manufacturers_name like '%", $manufacturer) . "%'");
	  while ($manufacturers_info = tep_db_fetch_array($manufacturers_info_query)) {
		$manufacturers_array[] = $manufacturers_info['manufacturers_id'];
	  }
	}
	$manufacturers_products_array = array();
	reset($manufacturers_to_search_array);
	while (list(, $manufacturer) = each($manufacturers_to_search_array)) {
	  $manufacturers_info_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where manufacturers_name is not null and manufacturers_name like '%" . str_replace(' ', "%' and manufacturers_name like '%", $manufacturer) . "%'");
	  while ($manufacturers_info = tep_db_fetch_array($manufacturers_info_query)) {
		$manufacturers_products_array[] = $manufacturers_info['products_id'];
	  }
	}
	$listing_sql_where .= " and ((p.products_types_id = '1' and p.manufacturers_id > 0 and p.manufacturers_id in ('" . implode("', '", $manufacturers_array) . "')) or (p.products_types_id > '1' and p.products_id in ('" . implode("', '", $manufacturers_products_array) . "')))";
	$search_params_array['manufacturers'] = $manufacturers;
  }

  if (basename(SCRIPT_FILENAME)==FILENAME_SERIES || tep_not_null($HTTP_GET_VARS['series_id'])) {
	if (tep_not_null($HTTP_GET_VARS['series_id'])) $series_id = $HTTP_GET_VARS['series_id'];
	$listing_sql_where .= " and p.series_id = '" . (int)$series_id . "'";
	$search_params_array['series_id'] = $series_id;
	$list_type = 2;//Серия
	$entity_id = (int)$series_id;
  } elseif (tep_not_null($HTTP_GET_VARS['series'])) {
    $series = stripslashes(trim(htmlspecialchars(strip_tags(urldecode($HTTP_GET_VARS['series'])), ENT_QUOTES)));
	$series_to_search_array = array_map('trim', explode(',', $series));
	$series_array = array();
	reset($series_to_search_array);
	while (list(, $serie) = each($series_to_search_array)) {
	  $series_info_query = tep_db_query("select series_id from " . TABLE_SERIES . " where series_name like '%" . str_replace(' ', "%' and series_name like '%", $serie) . "%'");
	  while ($series_info = tep_db_fetch_array($series_info_query)) {
		$series_array[] = $series_info['series_id'];
	  }
	}
	$listing_sql_where .= " and p.series_id > 0 and p.series_id in ('" . implode("', '", $series_array) . "')";
	$search_params_array['series'] = $series;
  }

  if (basename(SCRIPT_FILENAME)==FILENAME_AUTHORS || tep_not_null($HTTP_GET_VARS['authors_id'])) {
	if (tep_not_null($HTTP_GET_VARS['authors_id'])) $authors_id = $HTTP_GET_VARS['authors_id'];
	$listing_sql_where .= " and p.authors_id = '" . (int)$authors_id . "'";
	$search_params_array['authors_id'] = $authors_id;
	$list_type = 3;//Автор
	$entity_id = (int)$authors_id;
  } elseif (tep_not_null($HTTP_GET_VARS['authors'])) {
    $authors = stripslashes(trim(htmlspecialchars(strip_tags(urldecode($HTTP_GET_VARS['authors'])), ENT_QUOTES)));
	$authors_to_search_array = array_map('trim', explode(',', $authors));
	$authors_array = array();
	reset($authors_to_search_array);
	while (list(, $author) = each($authors_to_search_array)) {
	  $authors_info_query = tep_db_query("select authors_id from " . TABLE_AUTHORS . " where authors_name like '%" . str_replace(' ', "%' and authors_name like '%", $author) . "%'");
	  while ($authors_info = tep_db_fetch_array($authors_info_query)) {
		$authors_array[] = $authors_info['authors_id'];
	  }
	}
	$listing_sql_where .= " and p.authors_id > 0 and p.authors_id in ('" . implode("', '", $authors_array) . "')";
	$search_params_array['authors'] = $authors;
  }

  if (tep_not_null($HTTP_GET_VARS['covers_id'])) {
	$listing_sql_where .= " and p.products_covers_id = '" . (int)$HTTP_GET_VARS['covers_id'] . "'";
	$search_params_array['covers_id'] = (int)$HTTP_GET_VARS['covers_id'];
  }

  if (tep_not_null($HTTP_GET_VARS['year_from'])) {
	$listing_sql_where .= " and p.products_year >= '" . (int)$HTTP_GET_VARS['year_from'] . "'";
	$search_params_array['products_year_from'] = (int)$HTTP_GET_VARS['year_from'];
  }
  if (tep_not_null($HTTP_GET_VARS['year_to'])) {
	$listing_sql_where .= " and p.products_year <= '" . (int)$HTTP_GET_VARS['year_to'] . "'";
	$search_params_array['products_year_to'] = (int)$HTTP_GET_VARS['year_to'];
  }

  if (tep_not_null($HTTP_GET_VARS['pfrom']) || tep_not_null($HTTP_GET_VARS['pto'])) {
	$pfrom = (float)$HTTP_GET_VARS['pfrom'];
	$pto = (float)$HTTP_GET_VARS['pto'];
	if ($currencies->is_set($currency)) {
	  $rate = $currencies->get_value($currency);
	  $pfrom = $pfrom / $rate;
	  $pto = $pto / $rate;

	  if ($pfrom > 0) {
		$listing_sql_where .= " and p.products_price >= '" . str_replace(',', '.', $pfrom) . "'";
		$search_params_array['products_price_from'] = str_replace(',', '.', $pfrom);
	  }
	  if ($pto > 0) {
		$listing_sql_where .= " and p.products_price <= '" . str_replace(',', '.', $pto) . "'";
		$search_params_array['products_price_to'] = str_replace(',', '.', $pto);
	  }
	}
  }

  // только поиск по словам
  if (basename(SCRIPT_FILENAME)==FILENAME_ADVANCED_SEARCH_RESULT && isset($searched_products)) {
	$products_to_search = $searched_products;
  } elseif (tep_not_null($HTTP_GET_VARS['keywords']) || tep_not_null($HTTP_GET_VARS['detailed'])) {
	$searched_products = array();
	$keywords = '';
	if (tep_not_null($HTTP_GET_VARS['detailed'])) $keywords .= ' ' . urldecode($HTTP_GET_VARS['detailed']);
	if (tep_not_null($HTTP_GET_VARS['keywords'])) $keywords .= ' ' . urldecode($HTTP_GET_VARS['keywords']);
	$keywords = htmlspecialchars(stripslashes(trim(strip_tags($keywords))), ENT_QUOTES);
	$keywords = str_replace(array('ё', 'Ё'), array('е', 'Е'), $keywords);
	$keywords_to_search = array();
	$keywords = strip_tags(tep_strtolower(html_entity_decode($keywords)));
	$keywords = str_replace(array('+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—'), ' ', $keywords);
	$keywords = preg_replace('/(\D)\-(\D)/i', '$1 $2', $keywords);
	$keywords = preg_replace('/\s+/', ' ', tep_db_input($keywords));
	$keywords_array = array_unique(explode(' ', $keywords));
	reset($keywords_array);
	while (list(, $keyword_to_search) = each($keywords_array)) {
	  if (mb_strlen(trim($keyword_to_search), 'CP1251') > 1) $keywords_to_search[] = $keyword_to_search;
	}

	$searched_products = array();
	if (preg_match('/^[-\d(x|х)]+$/', $keywords)) {
	  if (strlen($keywords) >= 7) {
		$products_models_found_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_MODELS . " where products_model_1 like '%" . preg_replace('/[^\dxх]/', '', $keywords) . "'");
		while ($products_models_found = tep_db_fetch_array($products_models_found_query)) {
		  $searched_products[] = $products_models_found['products_id'];
		}
	  }
	  if (preg_match('/^[\d]+$/', $keywords)) {
		$products_ids_found_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_code = 'bbk" . sprintf('%010d', (int)$keywords) . "'");
		while ($products_ids_found = tep_db_fetch_array($products_ids_found_query)) {
		  $searched_products[] = $products_ids_found['products_id'];
		}
	  }
	} else {
	  $keywords_query = tep_db_query("select search_keywords_id from " . TABLE_SEARCH_KEYWORDS . " where search_keywords_word = '" . tep_db_input(implode(' ', $keywords_to_search)) . "'");
	  $keywords_row = tep_db_fetch_array($keywords_query);
	  $search_keywords_id = (int)$keywords_row['search_keywords_id'];
	  if ($search_keywords_id < 1) {
		tep_db_query("insert into " . TABLE_SEARCH_KEYWORDS . " (search_keywords_word, search_keywords_count, date_added) values ('" . tep_db_input(implode(' ', $keywords_to_search)) . "', '1', now())");
		$search_keywords_id = tep_db_insert_id();
		$products_found_query = tep_db_query("select products_id, products_text from " . TABLE_PRODUCTS_DESCRIPTION . " where ( match(products_text) against ('+" . tep_db_input(implode(' +', $keywords_to_search)) . "' in boolean mode) ) > 0 having products_text like '%" . implode("%' and products_text like '%", $keywords_to_search) . "%'");
		while ($products_found = tep_db_fetch_array($products_found_query)) {
		  tep_db_query("insert ignore into " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " (search_keywords_id, products_id) values ('" . (int)$search_keywords_id . "', '" . (int)$products_found['products_id'] . "')");
		}
	  }

	  $products_keywords_query = tep_db_query("select products_id from " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " where search_keywords_id = '" . (int)$search_keywords_id . "'");
	  while ($products_keywords = tep_db_fetch_array($products_keywords_query)) {
		$searched_products[] = $products_keywords['products_id'];
	  }

/*
	  $products_keywords_query = tep_db_query("select products_id, products_text from " . TABLE_PRODUCTS_DESCRIPTION . " where ( match(products_text) against ('+" . tep_db_input(implode(' +', $keywords_to_search)) . "' in boolean mode) ) > 0 having products_text like '%" . implode("%' and products_text like '%", $keywords_to_search) . "%'");
	  while ($products_keywords = tep_db_fetch_array($products_keywords_query)) {
		$searched_products[] = $products_keywords['products_id'];
	  }
*/
	}

/*
	$k = 0;
	reset($keywords_to_search);
	while (list(, $keyword_to_search) = each($keywords_to_search)) {
	  $keyword_query = tep_db_query("select search_keywords_id from " . TABLE_SEARCH_KEYWORDS . " where search_keywords_word = '" . $keyword_to_search . "'");
	  $keyword_row = tep_db_fetch_array($keyword_query);
	  $searched_products_temp = array();
	  $search_keywords_id = (int)$keyword_row['search_keywords_id'];
	  if ($search_keywords_id > 0) {
		if (tep_not_null($HTTP_GET_VARS['detailed'])) tep_db_query("update " . TABLE_SEARCH_KEYWORDS . " set last_modified = now(), search_keywords_count = search_keywords_count + 1 where search_keywords_id = '" . (int)$search_keywords_id . "'");
	  } else {
		tep_db_query("insert into " . TABLE_SEARCH_KEYWORDS . " (search_keywords_word, search_keywords_count, date_added) values ('" . tep_db_input($keyword_to_search) . "', '1', now())");
		$search_keywords_id = tep_db_insert_id();
		tep_db_query("insert ignore into " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " (search_keywords_id, products_id) select '" . (int)$search_keywords_id . "', products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where products_text like '% " . $keyword_to_search . " %'");
	  }
	  $search_params_array['keywords'][] = $search_keywords_id;

	  $products_found_query = tep_db_query("select products_id from " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " where search_keywords_id = '" . (int)$search_keywords_id . "'");
	  while ($products_found = tep_db_fetch_array($products_found_query)) {
		$searched_products_temp[] = $products_found['products_id'];
	  }

	  if (preg_match('/^[-\d]+$/', $keyword_to_search)) {
		$products_models_found_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_model_1 = '" . preg_replace('/[^\d]/', '', $keyword_to_search) . "'");
		while ($products_models_found = tep_db_fetch_array($products_models_found_query)) {
		  $searched_products_temp[] = $products_models_found['products_id'];
		}
	  }

	  if (preg_match('/^[\d]+$/', $keyword_to_search)) {
		$products_ids_found_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$keyword_to_search . "' or products_code = 'bbk" . sprintf('%010d', (int)$keyword_to_search) . "'");
		while ($products_ids_found = tep_db_fetch_array($products_ids_found_query)) {
		  $searched_products_temp[] = $products_ids_found['products_id'];
		}
	  }

	  if ($k==0) {
		$searched_products = $searched_products_temp;
		$k ++;
	  } else {
		$searched_products = array_intersect($searched_products, $searched_products_temp);
	  }
	}
*/

//	$searched_products_fulltext = array();
//	$searched_products_query_raw = "select products_id, ( (1.5 * (match(products_name) against ('" . tep_db_input($keywords) . "' in boolean mode))) + (1.3 * (match(products_model) against ('" . tep_db_input($keywords) . "' in boolean mode))) + (1.1 * (match(authors_name) against ('" . tep_db_input($keywords) . "' in boolean mode))) + (0.9 * (match(series_name) against ('" . tep_db_input($keywords) . "' in boolean mode))) + (0.7 * (match(manufacturers_name) against ('" . tep_db_input($keywords) . "' in boolean mode))) ) as relevance from " . TABLE_PRODUCTS_INFO . " where ( match(products_name, products_model, authors_name, series_name, manufacturers_name) against ('" . tep_db_input($keywords) . "' in boolean mode) ) having relevance > 0 order by relevance desc";
//	$searched_products_query = tep_db_query($searched_products_query_raw);
//	while ($searched_products_row = tep_db_fetch_array($searched_products_query)) {
//	  $searched_products_fulltext[] = $searched_products_row['products_id'];
//	}
//	$searched_products = array_merge($searched_products, $searched_products_fulltext);

	if (isset($products_to_search) && is_array($products_to_search)) {
	  $products_to_search = array_intersect($products_to_search, $searched_products);
	} else {
	  $products_to_search = $searched_products;
	}
  }

  $available_views = array('with_fragments');
  $specials_types_query = tep_db_query("select specials_types_path from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "')");
  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	$available_views[] = $specials_types['specials_types_path'];
  }

  if (isset($HTTP_GET_VARS['view']) && in_array($HTTP_GET_VARS['view'], $available_views)) {
	$only_products = array();
  // только те книги, которые можно пролистать
	if ($HTTP_GET_VARS['view']=='with_fragments') {
	  $only_products_query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_IMAGES . "");
	} else {
	  $specials_type_info_query = tep_db_query("select specials_types_id from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and specials_types_path = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['view'])) . "' limit 1");
	  $specials_type_info = tep_db_fetch_array($specials_type_info_query);
	  if ($specials_type_info['specials_types_id'] > 0) {
		$only_products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_type_info['specials_types_id'] . "' and status = '1'" . ($HTTP_GET_VARS['view']=='new' ? " and specials_date_added >= '" . tep_db_input($min_specials_date_added) . " 00:00:00'" : "") . "");

		if ($show_product_type==1 && $current_category_id==0 && basename(SCRIPT_FILENAME)==FILENAME_CATEGORIES && in_array($HTTP_GET_VARS['view'], $available_views)) echo '<p>&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_type_info['specials_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_SPECIALS_RSS, '', '', 'style="float: left;"') . TEXT_RSS_SUBSCRIPTION . '</a></p>' . "\n";
	  } else {
		$only_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_id < '0'");
	  }
	}
	while ($only_products_row = tep_db_fetch_array($only_products_query)) {
	  $only_products[] = $only_products_row['products_id'];
	}
	if (isset($products_to_search) && is_array($products_to_search)) $products_to_search = array_intersect($products_to_search, $only_products);
	else $products_to_search = $only_products;
	$search_params_array['view'] = $HTTP_GET_VARS['view'];
  }

  // только спецпредложения
  if (basename(SCRIPT_FILENAME) == FILENAME_SPECIALS) {
	$specials_products_query_raw = "select products_id, week(specials_date_added, 5) - week(date_sub(specials_date_added, INTERVAL DAYOFMONTH(specials_date_added) - 1
DAY), 5) +1 as week_added from " . TABLE_SPECIALS . " where status = '1' and specials_types_id = '" . (int)$specials_types_id . "' and language_id = '" . (int)$languages_id . "'";
	if ($specials_year > 0) {
	  $specials_products_query_raw .= " and year(specials_date_added) = '" . (int)$specials_year . "'";
	  if ($specials_month > 0) {
		$specials_products_query_raw .= " and month(specials_date_added) = '" . (int)$specials_month . "'";
	  }
	  if ($specials_week > 0) {
		$specials_products_query_raw .= " having week_added = '" . (int)$specials_week . "'";
	  }
	} else {
	  $specials_products_query_raw .= " and specials_date_added >= '" . date('Y-m-d', time()-60*60*24*7) . " 00:00:00'";
	}
	$listing_sql_select .= ", date_format(p.products_date_added, '%Y-%m-%d') as products_date_added";
//	if (empty($HTTP_GET_VARS['sort']) && !tep_session_is_registered('sort')) $sort_by = "products_date_added desc, p.sort_order";
	$specials_products_query = tep_db_query($specials_products_query_raw);
	while ($specials_products_row = tep_db_fetch_array($specials_products_query)) {
	  $specials_products[] = $specials_products_row['products_id'];
	}
	if (isset($products_to_search) && is_array($products_to_search)) $products_to_search = array_intersect($products_to_search, $specials_products);
	else $products_to_search = $specials_products;
	$search_params_array['specials_types_id'] = $specials_types_id;
  } else {
//	$listing_sql_from .= " left join " . TABLE_SPECIALS . " s on (s.products_id = p.products_id and s.status = '1' and s.language_id = '" . (int)$languages_id . "')";
  }

  // только товары категории
  if ($show_category_id > 0) {
	if ($show_product_type > 0) {
/*
	  $subcategories = array($show_category_id);
	  if (isset($show_subcategories_products) && $show_subcategories_products==true) {
		tep_get_subcategories($subcategories, $show_category_id);
	  }
	  $listing_sql_from .= ", " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
	  $listing_sql_where .= " and p2c.categories_id in ('" . implode("', '", $subcategories) . "') and p.products_id = p2c.products_id";
*/

	  $last_modified_products_query = tep_db_query("select products_last_modified from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$show_product_type . "'");
	  $last_modified_products = tep_db_fetch_array($last_modified_products_query);
	  clearstatcache();
	  $products_cache_dir = DIR_FS_CATALOG . 'cache/products/';
	  if (!is_dir($products_cache_dir)) mkdir($products_cache_dir, 0777);
	  $products_cache_dir .= $show_product_type . '/';
	  if (!is_dir($products_cache_dir)) mkdir($products_cache_dir, 0777);
	  $products_cache_filename = $products_cache_dir . 'listing_' . $show_category_id . '.txt';
	  $include_products_cache_filename = false;
	  if (file_exists($products_cache_filename)) {
		if (date('Y-m-d H:i:s', filemtime($products_cache_filename)) > $last_modified_products['products_last_modified']) {
		  $include_products_cache_filename = true;
		}
	  }
	  $subcategories_products = array();
	  if ($include_products_cache_filename==false) {
		if (file_exists($products_cache_filename)) unlink($products_cache_filename);
		$subcategories = array($show_category_id);
		if (isset($show_subcategories_products) && $show_subcategories_products==true) {
		  tep_get_subcategories($subcategories, $show_category_id);
		}
		$subcategories_list_query = tep_db_query("select distinct p.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCTS . " p where p.products_types_id = '" . (int)$show_product_type . "' and p.products_status = '1'" . ((int)PRODUCT_SHOW_NONACTIVE=='0' ? " and products_listing_status = '1'" : "") . " and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("', '", $subcategories) . "')");
		while ($subcategories_list = tep_db_fetch_array($subcategories_list_query)) {
		  $subcategories_products[] = $subcategories_list['products_id'];
		}
		if ($fp = @fopen($products_cache_filename, 'w')) {
		  fwrite($fp, implode("\n", $subcategories_products));
		  fclose($fp);
		}
	  } else {
/*
		$subcategories_products = array_map('trim', file($products_cache_filename));
*/
/*
		$fp = fopen($products_cache_filename, 'r');
		while (!feof($fp)) {
		  $file_line = trim(fgets($fp, 16));
		  $subcategories_products[] = $file_line;
		}
		fclose($fp);
*/
//*
		if (filesize($products_cache_filename) > 0) {
		  if ($fp = @fopen($products_cache_filename, 'r')) {
			$products_cache_filename_contents = fread($fp, filesize($products_cache_filename));
			fclose($fp);
			$subcategories_products = array_map('trim', explode("\n", $products_cache_filename_contents));
		  }
		}
//*/
	  }
	  if (!is_array($subcategories_products)) $subcategories_products = array();
	  if (!isset($products_to_search)) $listing_number_of_rows = sizeof($subcategories_products);
	} else {
	  $subcategories = array($show_category_id);
	  if (isset($show_subcategories_products) && $show_subcategories_products==true) {
		tep_get_subcategories($subcategories, $show_category_id);
	  }
	  $subcategories_list_query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories) . "')");
	  while ($subcategories_list = tep_db_fetch_array($subcategories_list_query)) {
		$subcategories_products[] = $subcategories_list['products_id'];
	  }
	}

	if (isset($products_to_search) && is_array($products_to_search)) $products_to_search = array_intersect($products_to_search, $subcategories_products);
	else $products_to_search = $subcategories_products;
  }

  if (isset($products_to_search) && is_array($products_to_search)) {
	$listing_sql_where .= " and p.products_id in ('" . implode("', '", $products_to_search) . "')";
  }

  $listing_sql = $listing_sql_select . $listing_sql_from . $listing_sql_where . $listing_sql_group_by;
  if (SHOP_ID==1 && $session_started && !in_array(basename(SCRIPT_FILENAME), array(FILENAME_NEWS, FILENAME_SHOPPING_CART))) {
	if (in_array('categories_id', array_keys($search_params_array)) || in_array('series_id', array_keys($search_params_array))) unset($search_params_array['products_types_id']);
	$search_params_string = serialize($search_params_array);
	$exclude_search_params_array = array('page', 'sort', 'info', tep_session_name(), 'per_page', 'inc_subcat', 'x', 'y', 'cPath', 'author', 'authors_id', 'series_id', 'manufacturers_id', 'cName', 'tName');
	if ($HTTP_GET_VARS['view']=='all') $exclude_search_params_array[] = 'view';
	$search_params_page = str_replace(HTTP_SERVER, '', tep_href_link(PHP_SELF, tep_get_all_get_params($exclude_search_params_array), $request_type, false));
/*
	$products_search_page_check_query = tep_db_query("select products_search_id from " . TABLE_PRODUCTS_SEARCH . " where products_search_page = '" . tep_db_input($search_params_page) . "'");
	$products_search_page_check = tep_db_fetch_array($products_search_page_check_query);
	if ($products_search_page_check['products_search_id'] < 1) {
	  tep_db_query("insert into " . TABLE_PRODUCTS_SEARCH . " (products_search_page, products_search_params, date_added, last_modified) values ('" . tep_db_input($search_params_page) . "', '" . tep_db_input($search_params_string) . "', now(), now())");
	} else {
	  tep_db_query("update " . TABLE_PRODUCTS_SEARCH . " set products_search_count = products_search_count + 1 where products_search_id = '" . (int)$products_search_page_check['products_search_id'] . "'");
	}
//	tep_db_query("insert into " . TABLE_CUSTOMERS_NOTIFICATIONS . " (customers_notifications_id, customers_notifications_name, customers_id, shops_id, customers_notifications_check_date, ) values (NULL, '" . tep_db_input() . "')");
*/
  }

  $listing_sql = preg_replace('/\s*select\s+/i', 'select ' . $select_column_list, $listing_sql);
  if ($customer_id==2) {
//	$subcategories = array($show_category_id);
//	tep_get_subcategories($subcategories, $show_category_id);
//	define('PAGE_PARSE_START_TIME_1', microtime());
//	$query = tep_db_query($listing_sql);
//	echo '<br>' . round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', PAGE_PARSE_START_TIME_1))), 2);
//	define('PAGE_PARSE_START_TIME_2', microtime());
//	$query = tep_db_query("select distinct p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where 1 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("', '", $subcategories) . "') and p.products_status = '1' and p.products_types_id = '1'");
//	echo '<br>' . round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', PAGE_PARSE_START_TIME_2))), 2);
  }

  if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) {
	$sort_by = '';
	$listing_number_of_rows = sizeof($products_to_search);
  }

  if (tep_not_null($sort_by)) {
	$listing_sql .= " order by " . $sort_by;
  } elseif (basename(PHP_SELF)!=FILENAME_SHOPPING_CART) {
	$listing_sql .= " order by ";
	switch ($column_list[$sort_col-1]) {
	  case 'PRODUCT_LIST_MODEL':
		$listing_sql .= "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_NAME':
		$listing_sql .= "p.products_sort_order " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_MANUFACTURER':
		$listing_sql .= "p.manufacturers_sort_order " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_AUTHOR':
		$listing_sql .= "p.authors_sort_order " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_YEAR':
		$listing_sql .= "p.products_year " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_QUANTITY':
		$listing_sql .= "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_IMAGE':
		$listing_sql .= "p.products_image_exists " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_WEIGHT':
		$listing_sql .= "p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_PRICE':
		$listing_sql .= "p.products_listing_status desc, p.products_price " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  default:
//		if (tep_not_null($keywords)) $listing_sql .= "field(p.products_id, " . implode(", ", $products_to_search) . ")";
//		else 
		$listing_sql .= "p.sort_order desc";
		break;
	}
  }
  if ($customer_id==2) {
//	echo $listing_sql;
  }

  if ($listing_number_of_rows > 0) {
  }

  $listing_split = new splitPageResults($listing_sql, $per_page, '*', 'page', $listing_number_of_rows);

  $records_found = $listing_split->number_of_rows;
  if ($records_found > 0) {
// optional Authors List Filter
	$filterlist_authors_string = '';
    if (PRODUCT_LIST_FILTER > 0 && $show_filterlist_string == true) {
	  $preload_authors = array();
	  $temp_string = '';

	  $text_customize = TEXT_CUSTOMIZE_KEYWORD;
	  if (tep_not_null(REQUEST_URI)) $authors_form_link = preg_replace('/detailed=[^\&]* /i', '', REQUEST_URI);
	  else $authors_form_link = basename(SCRIPT_FILENAME);

	  $temp_string = tep_draw_input_field('detailed', TEXT_INPUT_KEYWORD, 'size="25" class="' . ((tep_not_null($HTTP_GET_VARS['detailed']) && $HTTP_GET_VARS['detailed']!=TEXT_INPUT_AUTHOR) ? 'author_activated' : 'author_disabled') . '" onfocus="this.className=\'author_activated\';' . (tep_not_null(TEXT_INPUT_KEYWORD) ? ' if (this.value==\'' . TEXT_INPUT_KEYWORD . '\') this.value = \'\';" onblur="if (this.value==\'\') { this.value = \'' . TEXT_INPUT_KEYWORD . '\'; this.className=\'author_disabled\'; }' : '') . '"');

	  if (tep_not_null($temp_string)) {
		$filterlist_authors_string .= '<div id="AuthorsList">' . $text_customize . '<br />' . "\n" . tep_draw_form('authors', $authors_form_link, 'get', 'onsubmit="if (document.authors.author) { if (document.authors.detailed.value==\'' . TEXT_INPUT_KEYWORD . '\' || document.authors.detailed.value==\'\') { return false; } }"');
		reset($HTTP_GET_VARS);
		while (list($key, $value) = each($HTTP_GET_VARS)) {
		  if (tep_not_null($value) && !in_array($key, array(tep_session_name(), 'page', 'x', 'y', 'cPath', 'author', 'authors_id', 'detailed', 'cName', 'tName'))) {
			if ($show_product_type > 1 && $key=='categories_id') {
			} else {
			  $filterlist_authors_string .= tep_draw_hidden_field($key, tep_output_string_protected(urldecode($value)));
			}
		  }
		}
        $filterlist_authors_string .= $temp_string . ' ' . tep_image_submit('button_quick_search.gif', IMAGE_BUTTON_QUICK_SEARCH) . '</form></div>' . "\n";
	  }
	}

	//Блок подписки
	if (!($list_type == 1 && $current_category_id < 1) && $languages_id==DEFAULT_LANGUAGE_ID) {
	  function get_detail($name_field, $id_field, $table, $id) {
		$query = tep_db_query("select ".$name_field." as name
		from ".$table." 
		where ".$id_field." = " . $id . "
		and ".($table == TABLE_MANUFACTURERS_INFO?'languages_id':'language_id')." = 2;");
		$detail = tep_db_fetch_array($query);
		return $detail['name'];
	  }
	  function get_name_detail($category_id, $type_id) {
		if ($type_id == 1) return get_detail('categories_name', 'categories_id', TABLE_CATEGORIES_DESCRIPTION, $category_id);
		if ($type_id == 2) return get_detail('series_name', 'series_id', TABLE_SERIES, $category_id);
		if ($type_id == 3) return get_detail('authors_name', 'authors_id', TABLE_AUTHORS, $category_id);
		if ($type_id == 4) return get_detail('manufacturers_name', 'manufacturers_id', TABLE_MANUFACTURERS_INFO, $category_id);
	  }
	  $message_type = explode(':', TEXT_CATEGORY_TYPE);
	  $message_type_alt = explode(':', TEXT_CATEGORY_TYPE_ALT);
	  $hide = tep_get_subscribe_status($entity_id, $list_type);
	  echo '<div id="subscribe-link"><div><span id="action_links">
	  <a href="#" alt=\''.sprintf(TEXT_CATEGORY_SUBSCRIBE_ALT, $message_type_alt[$list_type-1].' <br>"'.get_name_detail($entity_id, $list_type).'"').'\' class="subscribe'.($hide?' hide':'').'" cid="'.$entity_id.'" tid="'.$list_type.'">'.TEXT_CATEGORY_SUBSCRIBE.' '.$message_type[$list_type-1].'</a></span>';
	  if (!tep_session_is_registered('customer_id'))
		echo '<span class="message error hide">'.sprintf(TEXT_CATEGORY_ERROR, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
	  else echo '<span class="message '.($hide?'':' hide').'">'.TEXT_CATEGORY_MESSAGE.' '.$message_type[$list_type-1].'</span>';
	  echo '</div></div>';
	}
	//End Блок подписки

	$listing_string = '	<table width="100%" id="listing-split">' . "\n" .
	'	  <tr>' . "\n" .
	'		<td>' . $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS) . '</td>' . "\n" .
	'		<td align="center">' . sprintf(TEXT_DISPLAY_NUMBER_OF_RECORDS_PER_PAGE, $listing_split->display_rows_per_page($per_page)) . '</td>' . "\n" .
	'		<td align="right">' . TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y', 'sort'))) . '</td>' . "\n" .
	'	  </tr>' . "\n" .
	'	</table>' . "\n";

	if ( (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') && $show_listing_string == true) {
	  echo $listing_string;
	}

	$rows = 0;
	$list_box_contents = array();
	$sorting_text = '';

	for ($col=0, $n=sizeof($column_list), $sorting_text=''; $col<$n; $col++) {
	  switch ($column_list[$col]) {
		case 'PRODUCT_LIST_MODEL':
		  $lc_text = TABLE_HEADING_MODEL;
		  break;
		case 'PRODUCT_LIST_NAME':
		  $lc_text = TABLE_HEADING_PRODUCTS;
		  break;
		case 'PRODUCT_LIST_MANUFACTURER':
		  if ($show_product_type>2) $lc_text = TABLE_HEADING_MANUFACTURER_1;
		  else $lc_text = TABLE_HEADING_MANUFACTURER;
		  break;
		case 'PRODUCT_LIST_AUTHOR':
		  $lc_text = TABLE_HEADING_AUTHOR;
		  break;
		case 'PRODUCT_LIST_YEAR':
		  $lc_text = TABLE_HEADING_YEAR;
		  break;
		case 'PRODUCT_LIST_PRICE':
		  $lc_text = TABLE_HEADING_PRICE;
		  break;
		case 'PRODUCT_LIST_QUANTITY':
		  $lc_text = TABLE_HEADING_QUANTITY;
		  break;
		case 'PRODUCT_LIST_WEIGHT':
		  $lc_text = TABLE_HEADING_WEIGHT;
		  break;
		case 'PRODUCT_LIST_IMAGE':
		  $lc_text = TABLE_HEADING_IMAGE;
		  break;
		case 'PRODUCT_LIST_BUY_NOW':
		  $lc_text = TABLE_HEADING_BUY_NOW;
		  break;
	  }

	  if ( ($column_list[$col] != 'PRODUCT_LIST_BUY_NOW') && ($column_list[$col] != 'PRODUCT_LIST_IMAGE') && PRODUCT_LIST_ALLOW_SORT=='true' ) {
		$sorting_text .= tep_create_sort_heading($sort_col . $sort_order, $col+1, $lc_text);
	  }
	}
	if (tep_not_null($sorting_text) && $show_filterlist_string == true) {
	  echo '<div class="sortHeading">' . (tep_not_null($filterlist_authors_string) ? $filterlist_authors_string : '') . TEXT_SORT_PRODUCTS_SHORT . ' &nbsp; &nbsp; ' . $sorting_text . ($sort_col>0 ? '<a href="#" onmouseover="this.href=\'' . tep_href_link(PHP_SELF, tep_get_all_get_params(array('page', 'info', 'sort')) . 'sort=') . '\';" title="' . TEXT_RESET_SORTING_TEXT . '">' . TEXT_RESET_SORTING . '</a>' : '');
	  echo '<div style="padding-top: 4px;">' . TEXT_FILTER_PRODUCTS_SHORT . ' &nbsp; &nbsp; ';
	  $specials_types_query = tep_db_query("select specials_types_name, specials_types_path from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and specials_types_path in ('new', 'sales', 'bestsellers') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order limit 3");
	  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		echo '<a href="#" onmouseover="this.href=\'' . tep_href_link(PHP_SELF, tep_get_all_get_params(array('page', 'info', 'view')) . 'view=' . $specials_types['specials_types_path']) . '\';"' . ($HTTP_GET_VARS['view']==$specials_types['specials_types_path'] ? ' class="active"' : '') . '>' . $specials_types['specials_types_name'] . '</a>';
	  }
	  if (tep_not_null($HTTP_GET_VARS['view'])) echo '<a href="' . tep_href_link(PHP_SELF, tep_get_all_get_params(array('page', 'info', 'view'))) . '">' . TEXT_FILTER_PRODUCTS_RESET . '</a>';
	  echo '</div>' . "\n";
	  echo '</div>' . "\n";
	}

	$form_link = REQUEST_URI;
	if (strpos($form_link, 'action')) $form_link = preg_replace('/action=[^\&]*/i', 'action=[form_action]', $form_link);
	elseif (strpos($form_link, '?')!==FALSE) $form_link = $form_link . '&action=[form_action]';
	else $form_link = $form_link . '?action=[form_action]';
	while (strpos($form_link, '?&')) $form_link = str_replace('?&', '?', $form_link);
	while (strpos($form_link, '&&')) $form_link = str_replace('&&', '&', $form_link);

	$cur_row = 0;

	$customer_discount = $cart->get_customer_discount();

//	if ($customer_id==2) echo $listing_sql;
    if ($show_listing_string==false) $listing_query = tep_db_query($listing_sql);
	else $listing_query = tep_db_query($listing_split->sql_query);
    while ($listing_row = tep_db_fetch_array($listing_query)) {
	  $product_id = $listing_row['products_id'];
	  $product_info_query = tep_db_query("select " . $select_column_list . " p.* from " . TABLE_PRODUCTS . " p where p.products_id = '" . (int)$product_id . "'");
	  $product_info = tep_db_fetch_array($product_info_query);
	  $product_info['final_price'] = $product_info['products_price'];
	  $product_info['corporate_price'] = 0;
	  if ($customer_discount['type']=='purchase' && $product_info['products_purchase_cost'] > 0) $product_info['corporate_price'] = $product_info['products_purchase_cost'] * (1 + $customer_discount['value']/100);
	  $product_info['specials_new_products_price'] = 0;
	  $product_info['specials_name'] = '';
	  $product_info['specials_description'] = '';
	  $special_info_query = tep_db_query("select specials_name, specials_description, specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status > '0' and specials_new_products_price > '0' and specials_new_products_price < '" . $product_info['products_price'] . "' and language_id = '" . (int)$languages_id . "' order by specials_date_added desc limit 1");
	  if (tep_db_num_rows($special_info_query) > 0) {
		$special_info = tep_db_fetch_array($special_info_query);
		$product_info['final_price'] = $special_info['specials_new_products_price'];
		$product_info['specials_new_products_price'] = $special_info['specials_new_products_price'];
		$product_info['specials_name'] = $specials_info['specials_name'];
		$product_info['specials_description'] = $specials_info['specials_description'];
		$product_info['corporate_price'] = 0;
	  }
	  $product_description_info_query = tep_db_query("select products_name, products_description, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  $product_description_info = tep_db_fetch_array($product_description_info_query);
	  if (!is_array($product_description_info)) $product_description_info = array();
	  $product_description_en_info_query = tep_db_query("select products_name as products_en_name, products_description as products_en_description, manufacturers_name as manufacturers_en_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '1'");
	  $product_description_en_info = tep_db_fetch_array($product_description_en_info_query);
	  if (!is_array($product_description_en_info)) $product_description_en_info = array();
	  if (tep_not_null($product_description_info['products_description'])) {
		$product_description_info['products_description'] = preg_replace('/\s+/', ' ', preg_replace('/<\/?[^>]+>/', ' ', $product_description_info['products_description']));
		if (mb_strlen($product_description_info['products_description'], 'CP1251') > 100) {
		  $short_description = strrev(mb_substr($product_description_info['products_description'], 0, 120, 'CP1251'));
		  $short_description = mb_substr($short_description, strcspn($short_description, '":,.!?()'), mb_strlen($short_description, 'CP1251'), 'CP1251');
		  $short_description = trim(strrev($short_description));
		  if (in_array(mb_substr($short_description, -1, mb_strlen($short_description, 'CP1251'), 'CP1251'), array(':', '(', ')', ','))) $short_description = mb_substr($short_description, 0, -1, 'CP1251') . '...';
		} else {
		  $short_description = $product_description_info['products_description'];
		}

		$product_description_info['products_short_description'] = $short_description;
	  }
	  if (tep_not_null($product_description_en_info['products_en_description']) && DEFAULT_LANGUAGE_ID!=$languages_id) {
		if (strlen($product_description_en_info['products_en_description']) > 100) {
		  $short_description = strrev(substr($product_description_en_info['products_en_description'], 0, 120));
		  $short_description = substr($short_description, strcspn($short_description, '":,.!?()'), strlen($short_description));
		  $short_description = trim(strrev($short_description));
		  if (in_array(substr($short_description, -1, strlen($short_description)), array(':', '(', ')', ','))) $short_description = substr($short_description, 0, -1) . '...';
		} else {
		  $short_description = $product_description_en_info['products_en_description'];
		}

		$product_description_en_info['products_en_short_description'] = $short_description;
	  }
	  if ($product_info['products_types_id'] > 1) {
		$manufacturer_info = array();
	  } else {
		$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$product_info['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
		if (!is_array($manufacturer_info)) $manufacturer_info = array();
	  }
	  $serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$product_info['series_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $serie_info = tep_db_fetch_array($serie_info_query);
	  if (!is_array($serie_info)) $serie_info = array();
	  $author_info_query = tep_db_query("select authors_name from authors where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $author_info = tep_db_fetch_array($author_info_query);
	  if (!is_array($author_info)) $author_info = array();
	  $product_info = array_merge($product_info, $product_description_info, $product_description_en_info, $manufacturer_info, $serie_info, $author_info);
	  if (DEFAULT_LANGUAGE_ID==1) {
		$product_info['products_name'] .= ' / ' . (tep_not_null($product_info['products_en_name']) ? $product_info['products_en_name'] : tep_transliterate($product_info['products_name']));
		$product_info['products_short_description'] = (tep_not_null($product_info['products_en_short_description']) ? $product_info['products_en_short_description'] : tep_transliterate($product_info['products_short_description']));
	  }
//	  reset($product_info);
//	  while (list($k, $v) = each($product_info)) {
//		while (strpos($v, "\'")!==false) $v = str_replace("\'", "'", $v);
//		while (strpos($v, '\"')!==false) $v = str_replace('\"', '"', $v);
//		$product_info[$k] = $v;
//	  }

	  $special_text = '';
	  if (basename(PHP_SELF)!=FILENAME_SHOPPING_CART) {
		if (in_array($product_info['products_id'], $holiday_products)) {
		  $special_text .= tep_image(DIR_WS_ICONS . 'new_year.png', 'Рекомендуемый подарок на Новый год и Рождество');
		}
		$special_types_query = tep_db_query("select distinct specials_types_id from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and specials_date_added >= '" . tep_db_input($min_specials_date_added) . " 00:00:00' order by specials_types_id");
		while ($special_types = tep_db_fetch_array($special_types_query)) {
		  if (in_array($special_types['specials_types_id'], array_keys($special_types_full_array))) {
			$special_text .= $special_types_full_array[$special_types['specials_types_id']];
		  }
		}
	  }

	  /*
	  $product_path = $cPath;
	  $parents = array();
	  $product_to_category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_info['products_id'] . "' and categories_id = '" . (int)$show_category_id . "'");
	  if (tep_db_num_rows($product_to_category_query)==0) {
		$product_to_category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_info['products_id'] . "' limit 1");
	  }
	  $product_to_category = tep_db_fetch_array($product_to_category_query);

	  $product_path = $show_category_id;
	  if ($product_to_category['categories_id']!=$show_category_id) {
		$product_path = $product_to_category['categories_id'];
		$parents = array($product_to_category['categories_id']);
		tep_get_parents($parents, $product_to_category['categories_id']);
		$parents = array_reverse($parents);
	  }
	  */

	  $products_additional_images = 0;
	  if ($show_listing_string!=false) {
		$products_images_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
		$products_images_check = tep_db_fetch_array($products_images_check_query);
		$products_additional_images = $products_images_check['total'];
	  }

	  $product_link = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id']);

	  $lc_align = 'center';
	  $lc_text = '';
	  $form_string = '';
	  $row_params = 'class="productListing-data-image"';

	  $product_image_link = '';
	  if (tep_not_null($product_info['products_image'])) {
//		if (SHOP_ID > 0) $product_image_link = DIR_WS_IMAGES . 'thumbs/' . $product_info['products_image'];
//		else $product_image_link = tep_href_link('show_image.php', 'products_id=' . $product_info['products_id']);
		$product_image_link = 'http://149.126.96.163/thumbs/' . $product_info['products_image'];
	  } else {
		$product_image_link = DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif';
	  }
	  $lc_text = (tep_not_null($special_text) ? '<div class="special_text">' . $special_text . '</div>' . "\n" : '') .
	  '<a href="' . $product_link . '">' . tep_image($product_image_link, $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>' . "\n" .
	  ($products_additional_images>0 ? '<div class="icon_fragments" title="' . TEXT_ADDITIONAL_IMAGES_1 . '"></div>' . "\n" : '') .
	  '';

	  $list_box_contents[$cur_row][] = array('params' => 'colspan="2" class="productListing-data-btw' . ($cur_row==0 ? '_first' : '') . '"',
											 'text'  => tep_draw_separator('pixel_trans.gif', '1', '1'));

      $cur_row = sizeof($list_box_contents);

	  $list_box_contents[$cur_row][] = array('align' => $lc_align,
											 'params' => $row_params,
											 'text'  => $lc_text);

	  $lc_text = '';
	  $lc_align = '';
	  $row_params = 'class="productListing-data-name"';

	  if (basename(SCRIPT_FILENAME)==FILENAME_SPECIALS) {
		if (tep_not_null($product_info['specials_name'])) {
		  $lc_text .= '<div class="productSpecialName">' . $product_info['specials_name'] . '</div>' . "\n" .
		  (tep_not_null($product_info['specials_description']) ? '<div class="productSpecialDescription">' . $product_info['specials_description'] . '</div>' . "\n" : '');
		}
	  }

	  $lc_text .= '<div class="row_product_name"><a href="' . $product_link . '">' . $product_info['products_name'] . '</a>';
	  if (tep_not_null($product_info['products_short_description'])) {
		$lc_text .= "\n" . '<div class="row_product_description">' . $product_info['products_short_description'] . '</div>' . "\n";
	  }
	  $lc_text .= '</div>' . "\n";

	  $temp_string = '';
	  if ($product_info['products_types_id'] > 1) {
//		if (tep_not_null($product_info['products_model'])) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . ($product_info['products_types_id']==2 ? TEXT_MODEL : TEXT_MODEL_1) . ' ' . $product_info['products_model'];
		if (tep_not_null($product_info['manufacturers_name'])) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . ($product_info['products_types_id']==2 ? TEXT_MANUFACTURER : TEXT_MANUFACTURER_1) . ' ' . $product_info['manufacturers_name'];
		if ((int)$product_info['series_id'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_SERIE . ' <a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $product_info['series_id']) . '">' . $product_info['series_name'] . '</a>';
		if ($product_info['products_periodicity'] > 0) {
		  $periodicity_count = $product_info['products_periodicity'];
		  $periodicity_text = sprintf(TEXT_PERIODICITY, $periodicity_count);
		  if (substr($periodicity_count, -1)==1 && $periodicity_count!=11) $periodicity_text = sprintf(TEXT_PERIODICITY_1, $periodicity_count);
		  elseif (substr($periodicity_count, -1) > 1 && substr($periodicity_count, -1) < 5 && substr($periodicity_count, -2, 1) != 1) $periodicity_text = sprintf(TEXT_PERIODICITY_2, $periodicity_count);
		  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $periodicity_text;
		}
	  } else {
//		if (tep_not_null($product_info['products_model'])) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_MODEL . ' ' . $product_info['products_model'];
		if ((int)$product_info['authors_id'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . (strpos($product_info['authors_name'], ',') ? TEXT_AUTHORS : TEXT_AUTHOR) . ' <a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $product_info['authors_id']) . '">' . $product_info['authors_name'] . '</a>';
		if ((int)$product_info['manufacturers_id'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_MANUFACTURER . ' <a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $product_info['manufacturers_id']) . '">' . $product_info['manufacturers_name'] . '</a>' . ((int)$product_info['products_year']>0 ? ', ' . $product_info['products_year'] . TEXT_YEAR : '');
		if ((int)$product_info['series_id'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_SERIE . ' <a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $product_info['series_id']) . '">' . $product_info['series_name'] . '</a>';
	  }
	  $lc_text .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";
	  $temp_string = '';

	  $notify_text = '';
//	  if ($product_info['specials_new_products_price'] > 0) {
//		$lc_text .= '<div class="row_product_special_price">' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div><div class="row_product_special_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . "\n";
//	  } else {
	  $lc_text .= '<div class="row_product_price">';
	  list($available_year, $available_month, $available_day) = explode('-', preg_replace('/^([^\s]+)\s/', '$1', $product_info['products_date_available']));
	  if ($product_info['products_listing_status']=='0') {
		$available_soon_check_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and specials_types_id = '4'");
		$available_soon_check = tep_db_fetch_array($available_soon_check_query);
		if ($product_info['products_date_available']>date('Y-m-d')) {
		  $lc_text .= sprintf(TEXT_PRODUCT_NOT_AVAILABLE, $monthes_array[(int)$available_month] . ' ' . $available_year);
		} elseif ($available_soon_check['total'] > 0) {
		  $lc_text .= TEXT_PRODUCT_NOT_AVAILABLE_2;
		} else {
		  $lc_text .= sprintf(TEXT_PRODUCT_NOT_AVAILABLE_1, $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])));
		  if ($product_info['products_types_id']=='1' && defined('TEXT_PRODUCT_NOT_AVAILABLE_1_TEXT')) {
			$lc_text .= '<span onclick="document.getElementById(\'fd' . $product_info['products_id'] . '\').style.display = (document.getElementById(\'fd' . $product_info['products_id'] . '\').style.display==\'none\' ? \'block\' : \'none\');" style="cursor: pointer;">' . tep_image(DIR_WS_ICONS . 'question_icon1.gif', '?', '', '', 'style="position: absolute; margin-top: -14px; margin-left: -5px;"') . '</span><div style="display: none; position: absolute; width: 250px; margin-left: 170px; margin-top: -250px; padding: 0px 1.5em; background: #eeeeee; border: 1px solid #dddddd; font-weight: normal; font-size: 11px; color: #000000;" id="fd' . $product_info['products_id'] . '">' . sprintf(TEXT_PRODUCT_NOT_AVAILABLE_1_TEXT, $product_info['products_id']) . '</div>';
		  }
		}
	  } elseif ($product_info['products_periodicity'] < 1) {
		if ($product_info['specials_new_products_price'] > 0 && $product_info['specials_new_products_price'] < $product_info['products_price']) $lc_text .= '<div class="row_product_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
		elseif ($product_info['corporate_price'] > 0) $lc_text .= '<div class="row_product_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div> ' . TEXT_CORPORATE_PRICE . ' ' . $currencies->display_price($product_info['corporate_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
		else $lc_text .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
//	 . ($product_info['products_periodicity']>0 ? '/номер' : '');
	  }
	  $lc_text .= '</div>' . "\n";

	  if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) {
		$notify_text .= '<br /><br /><span class="row_product_notify">';
		if ($product_info['products_listing_status']=='1') {
		  $notify_text .= POSTPONE_CART_NOTIFICATION_PRICE . ' ';
		  if ($postpone_cart->check_notification($product_info['products_id'])==2) {
			$notify_text .= '<span class="notify_selected_yes">' . POSTPONE_CART_TEXT_YES . '</span> | <a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=notify&products_id=' . $product_info['products_id']) . '">' . POSTPONE_CART_TEXT_NO . '</a>';
		  } else {
			$notify_text .= (tep_session_is_registered('customer_id') ? '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=notify&notify=2&products_id=' . $product_info['products_id']) . '">' . POSTPONE_CART_TEXT_YES . '</a>' : '<a href="#" onclick="alert(\'' . POSTPONE_CART_NOTIFICATION_ERROR . '\'); return false;">' . POSTPONE_CART_TEXT_YES . '</a>') . ' | <span class="notify_selected_no">' . POSTPONE_CART_TEXT_NO . '</span>';
		  }
		} else {
		  $notify_text .= POSTPONE_CART_NOTIFICATION_AVAILABLE . ' ';
		  if ($postpone_cart->check_notification($product_info['products_id'])==1) {
			$notify_text .= '<span class="notify_selected_yes">' . POSTPONE_CART_TEXT_YES . '</span> | <a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=notify&products_id=' . $product_info['products_id']) . '">' . POSTPONE_CART_TEXT_NO . '</a>';
		  } else {
			$notify_text .= (tep_session_is_registered('customer_id') ? '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=notify&notify=1&products_id=' . $product_info['products_id']) . '">' . POSTPONE_CART_TEXT_YES . '</a>' : '<a href="#" onclick="alert(\'' . POSTPONE_CART_NOTIFICATION_ERROR . '\'); return false;">' . POSTPONE_CART_TEXT_YES . '</a>') . ' | <span class="notify_selected_no">' . POSTPONE_CART_TEXT_NO . '</span>';
		  }
		}
		$notify_text .= '</span>' . "\n";
	  }
//	  }

	  $form_link_1 = str_replace('[form_action]', 'add_product', $form_link);
	  $form_link_1_postpone = str_replace('[form_action]', 'add_product&to=postpone', $form_link);
	  $form_link_2 = str_replace('[form_action]', 'buy_now&type=1&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id(), $form_link);
	  $form_link_2 = tep_href_link(FILENAME_LOADER, 'action=buy_now&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id());

	  $form_string = tep_draw_form('p_form_' . $product_info['products_id'], $form_link_1, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '\'' . ($product_info['products_periodicity']>0 ? '+\'&quantity=\'+quantity.options[quantity.selectedIndex].value' : '') . ', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

	  $form_string_postpone = tep_draw_form('p_form_' . $product_info['products_id'] . '_postpone', $form_link_1_postpone, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '&to=postpone\', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text_postpone; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

	  if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) {
		$lc_text .= '<div class="row_product_buy"><a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&from=postpone&products_id=' . $product_info['products_id']) . '">' . tep_image_button('button_delete.gif', IMAGE_BUTTON_DELETE) . '</a> &nbsp; ' . ($product_info['products_listing_status']=='1' ? '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=move_product&products_id=' . $product_info['products_id']) . '">' . tep_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '</a>' : tep_image_button('button_in_cart3.gif', IMAGE_BUTTON_IN_CART)) . '</div>' . "\n";
	  } else {
		$lc_text .= '<div class="row_product_buy" id="p_l_' . $product_info['products_id'] . '" onmouseover="if (document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\')) document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\').style.display = \'inline\'" onmouseout="if (document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\')) document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\').style.display = \'none\'">';
		if (in_array($product_info['products_id'], $products_in_cart)) {
		  $lc_text .= tep_image_button('button_in_cart2.gif', IMAGE_BUTTON_IN_CART2);
		} elseif (in_array($product_info['products_id'], $products_in_postpone_cart)) {
		  $lc_text .= tep_image_button('button_postpone2.gif', IMAGE_BUTTON_POSTPONE2);
		} else {
		  if ($product_info['products_listing_status']=='1') {
			$lc_text .= $form_string;
			if ($product_info['products_periodicity'] > 0) {
			  $periodicity_array = array();
			  if (substr($product_info['products_model'], -1) == 'e') {
				$periodicity_array[] = array('id' => ceil($periodicity_count/12), 'text' => TEXT_SUBSCRIBE_TO_1_MONTH . ': ' . $currencies->display_price($product_info['products_price'] * ceil($periodicity_count/12), tep_get_tax_rate($product_info['products_tax_class_id'])));
			  }
			  if ($product_info['products_periodicity_min'] <= 3 && $periodicity_count > 6) {
				$periodicity_array[] = array('id' => ceil($periodicity_count/4), 'text' => TEXT_SUBSCRIBE_TO_3_MONTHES . ': ' . $currencies->display_price($product_info['products_price'] * ceil($periodicity_count/4), tep_get_tax_rate($product_info['products_tax_class_id'])));
			  }
			  if ($product_info['products_periodicity_min'] <= 7) {
				$periodicity_array[] = array('id' => $periodicity_count/2, 'text' => TEXT_SUBSCRIBE_TO_HALF_A_YEAR . ': ' . $currencies->display_price($product_info['products_price']*$periodicity_count/2, tep_get_tax_rate($product_info['products_tax_class_id'])));
			  }
			  $periodicity_array[] = array('id' => $periodicity_count, 'text' => TEXT_SUBSCRIBE_TO_YEAR . ': ' . $currencies->display_price($product_info['products_price']*$periodicity_count, tep_get_tax_rate($product_info['products_tax_class_id'])));
			  $lc_text .= '<div class="subscribe_to">' . TEXT_SUBSCRIBE_TO . ' ' . tep_draw_pull_down_menu('quantity', $periodicity_array) . '&nbsp;</div>';
			}
			$lc_text .= tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '<br /></form>' . "\n";
			$lc_text .= '<div id="form_' . $product_info['products_id'] . '_postpone" style="display: none; position: absolute; margin-top: 1px;">' . ($product_info['products_types_id']>1 ? "" : $form_string_postpone . tep_image_submit('button_postpone.gif', IMAGE_BUTTON_POSTPONE)) . '</form></div>';
		  } elseif ($product_info['products_types_id']=='1') {
			$lc_text .= $form_string_postpone . tep_image_submit('button_postpone.gif', IMAGE_BUTTON_POSTPONE) . '</form>';
		  } else {
			$lc_text .= '';
		  }
		}
		$lc_text .= '</div>' . "\n";
	  }
	  $lc_text .= $notify_text;

	  if ($product_info['products_listing_status']=='1' && basename(PHP_SELF)!=FILENAME_SHOPPING_CART) {
//		$lc_text .= '<div style="text-align: right; margin-right: 80px;"><fb:like href="' . $product_link . '" layout="button_count" action="recommend" width="120" font="arial"></fb:like></div>' . "\n";
	  }

	  if (in_array($product_info['authors_id'], array_keys($imhonet_authors)) && SHOP_ID==1 && date('Y-m-d') < '2010-10-22') {
		$lc_text .= '<a href="' . tep_href_link(FILENAME_REDIRECT, 'goto=' . $imhonet_authors[$product_info['authors_id']]) . '" onmouseover="this.href=\'http://' . $imhonet_authors[$product_info['authors_id']] . '\';" target="_blank" style="display: block; background: url(' . DIR_WS_IMAGES . 'imhonet_bg.gif) top left no-repeat; clear: both; height: 20px; padding: 4px 3px 0 37px; text-decoration: none; color: #23190B;" title="Автор номинирован на &laquo;Читательскую премию Имхонета&raquo;">Автор номинирован на &laquo;Читательскую премию Имхонета&raquo; &nbsp; &nbsp; <span style="color: #FFFFFF; font-weight: bold; text-decoration: underline;">Голосовать!</span></a>' . "\n";
	  }

	  if ($product_info['products_listing_status']=='1' && basename(PHP_SELF)!=FILENAME_SHOPPING_CART && defined('TEXT_CODE') && tep_not_null(TEXT_CODE)) {
		$lc_text .= '<br clear="right" /><div class="row_product_author" style="background: url(' . DIR_WS_TEMPLATES_IMAGES . 'phone_small_1.gif) top left no-repeat; clear: both; height: 20px; padding: 4px 3px 0 25px; text-decoration: none;">' . sprintf(TEXT_CODE, (int)str_replace('bbk', '', $product_info['products_code'])) . '</div>';
	  }

	  $list_box_contents[$cur_row][] = array('align' => $lc_align,
											 'params' => $row_params,
											 'text'  => $lc_text);

      $cur_row = sizeof($list_box_contents);
    }

	echo '<script language="javascript" type="text/javascript"><!--' . "\n" .
	' var new_text = \'<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . tep_image_button('button_in_cart2.gif', IMAGE_BUTTON_IN_CART3) . '</a>\';' . "\n" .
	' var new_text_postpone = \'<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '#postpone">' . tep_image_button('button_postpone2.gif', IMAGE_BUTTON_POSTPONE3) . '</a>\';' . "\n" .
	'//--></script>';

	$box = new tableBox(array());
	$box->table_width = '';
	$box->table_border = '0';
	$box->table_parameters = 'class="productListing"';
	$box->table_cellspacing = '0';
	$box->table_cellpadding = '0';
	echo $box->tableBox($list_box_contents);

	if ( (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') && $show_listing_string == true) {
	  echo $listing_string;
	}
//	echo '<br>+' . round((array_sum(explode(' ', microtime())) - $start), 2) . 'сек';
  } else {
    echo '<p>' . TEXT_NO_PRODUCTS . '</p>';
  }
?>