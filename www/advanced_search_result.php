<?php
  require('includes/application_top.php');

  $content = FILENAME_ADVANCED_SEARCH_RESULT;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $error = false;

  if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
  $qvars = explode('&', $_SERVER['QUERY_STRING']);
  reset($qvars);
  while (list(, $qvar) = each($qvars)) {
	list($qvar_key, $qvar_value) = explode('=', $qvar);
	$HTTP_GET_VARS[$qvar_key] = urldecode($qvar_value);
  }

  if (isset($HTTP_GET_VARS['pfrom'])) $HTTP_GET_VARS['pfrom'] = str_replace(',', '.', $HTTP_GET_VARS['pfrom']);
  if (isset($HTTP_GET_VARS['pto'])) $HTTP_GET_VARS['pto'] = str_replace(',', '.', $HTTP_GET_VARS['pto']);
  if ( (isset($HTTP_GET_VARS['categories_id']) && empty($HTTP_GET_VARS['categories_id'])) &&
	   (isset($HTTP_GET_VARS['manufacturers_id']) && empty($HTTP_GET_VARS['manufacturers_id'])) &&
	   (isset($HTTP_GET_VARS['manufacturers']) && empty($HTTP_GET_VARS['manufacturers'])) &&
	   (isset($HTTP_GET_VARS['series']) && empty($HTTP_GET_VARS['series'])) &&
	   (isset($HTTP_GET_VARS['authors']) && empty($HTTP_GET_VARS['authors'])) &&
	   (isset($HTTP_GET_VARS['keywords']) && empty($HTTP_GET_VARS['keywords'])) &&
       (isset($HTTP_GET_VARS['year_from']) && !is_numeric($HTTP_GET_VARS['year_from'])) &&
       (isset($HTTP_GET_VARS['year_to']) && !is_numeric($HTTP_GET_VARS['year_to'])) &&
       (isset($HTTP_GET_VARS['pfrom']) && !is_numeric($HTTP_GET_VARS['pfrom'])) &&
       (isset($HTTP_GET_VARS['pto']) && !is_numeric($HTTP_GET_VARS['pto'])) ) {
    $error = true;

    $messageStack->add_session('header', ERROR_AT_LEAST_ONE_INPUT);
  } else {
    $pfrom = '';
    $pto = '';
    $keywords = '';
	$year_from = '';
	$year_to = '';

    if (isset($HTTP_GET_VARS['pfrom'])) {
      $pfrom = $HTTP_GET_VARS['pfrom'];
    }

    if (isset($HTTP_GET_VARS['pto'])) {
      $pto = $HTTP_GET_VARS['pto'];
    }

    if (isset($HTTP_GET_VARS['year_from'])) {
      $year_from = $HTTP_GET_VARS['year_from'];
    }

    if (isset($HTTP_GET_VARS['year_to'])) {
      $year_to = $HTTP_GET_VARS['year_to'];
    }

    if (isset($HTTP_GET_VARS['keywords'])) {
      $keywords = htmlspecialchars(stripslashes(trim(strip_tags($HTTP_GET_VARS['keywords']))), ENT_QUOTES);
	  $keywords = str_replace(array('¸', '¨'), array('å', 'Å'), $keywords);
    }

    if (isset($HTTP_GET_VARS['categories_id'])) {
      $categories_id = $HTTP_GET_VARS['categories_id'];
    }

    if (isset($HTTP_GET_VARS['manufacturers_id'])) {
      $manufacturers_id = $HTTP_GET_VARS['manufacturers_id'];
    }

    $price_check_error = false;
    if (tep_not_null($pfrom)) {
      if (!settype($pfrom, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('header', ERROR_PRICE_MUST_BE_NUM);
      }
    }

    if (tep_not_null($pto)) {
      if (!settype($pto, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('header', ERROR_PRICE_MUST_BE_NUM);
      }
    }

    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
      if ($pfrom > $pto) {
        $error = true;

        $messageStack->add_session('header', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
      }
    }

    $year_check_error = false;
    if (tep_not_null($year_from)) {
      if (!settype($year_from, 'integer')) {
        $error = true;
        $year_check_error = true;

        $messageStack->add_session('header', ERROR_YEAR_MUST_BE_NUM);
      }
    }

    if (tep_not_null($year_to)) {
      if (!settype($year_to, 'integer')) {
        $error = true;
        $year_check_error = true;

        $messageStack->add_session('header', ERROR_YEAR_MUST_BE_NUM);
      }
    }

    if (($year_check_error == false) && is_int($year_from) && is_int($year_to)) {
      if ($year_from > $year_to) {
        $error = true;

        $messageStack->add_session('header', ERROR_TO_YEAR_LESS_THAN_FROM_YEAR);
      }
    }

//    if (tep_not_null($keywords)) {
//      if (!tep_parse_search_string($keywords, $search_keywords)) {
//        $error = true;

//        $messageStack->add_session('header', ERROR_INVALID_KEYWORDS);
//      }
//    }
  }

  if (empty($categories_id) && empty($manufacturers_id) && empty($manufacturers) && empty($series) && empty($authors) && empty($pfrom) && empty($pto) && empty($year_from) && empty($year_to) && empty($keywords)) {
    $error = true;

    $messageStack->add_session('header', ERROR_AT_LEAST_ONE_INPUT);
  }

  if ($error == true) {
    tep_redirect(tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(), 'NONSSL', true, false));
  }

  $products_to_search = array();
  $authors_to_search = array();
  $categories_to_search = array();
  $information_to_search = array();
  $news_to_search = array();
  $manufacturers_to_search = array();
  $series_to_search = array();
  $reviews_to_search = array();
  $pages_to_search = array();
  $total_found = 0;

  if (tep_not_null($keywords)) {
	$pages_query_row = "select distinct pages_id from " . TABLE_PAGES . " where language_id = '" . (int)$languages_id . "' and ( (concat_ws('', ' ', pages_name) like '%" . str_replace(' ', "%' and concat_ws('', ' ', pages_name) like '%", $keywords) . "%') )";
	$pages_query = tep_db_query($pages_query_row);
	while ($row = tep_db_fetch_array($pages_query)) {
	  $pages_to_search[] = $row['pages_id'];
	}
	$total_found += sizeof($pages_to_search);
  }

  if (tep_not_null($keywords)) {
	$categories_query_row = "select distinct c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = '1' and c.products_types_id = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and ( (concat_ws('', ' ', cd.categories_name) like '%" . str_replace(' ', "%' and concat_ws('', ' ', cd.categories_name) like '%", $keywords) . "%') )";
	$categories_query = tep_db_query($categories_query_row);
	while ($row = tep_db_fetch_array($categories_query)) {
	  $categories_to_search[] = $row['categories_id'];
	}
	$total_found += sizeof($categories_to_search);
  }

  if (tep_not_null($keywords)) {
	$manufacturers_query_row = "select distinct mi.manufacturers_id from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_status = '1' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "' and ( (concat_ws('', ' ', mi.manufacturers_name) like '%" . str_replace(' ', "%' and concat_ws('', ' ', mi.manufacturers_name) like '%", $keywords) . "%') )";
	$manufacturers_query = tep_db_query($manufacturers_query_row);
	while ($row = tep_db_fetch_array($manufacturers_query)) {
	  $manufacturers_to_search[] = $row['manufacturers_id'];
	}
	$total_found += sizeof($manufacturers_to_search);
  }

  if (tep_not_null($keywords)) {
	$series_query_row = "select distinct series_id from " . TABLE_SERIES . " where series_status = '1' and language_id = '" . (int)$languages_id . "' and ( (concat_ws('', ' ', series_name) like '%" . str_replace(' ', "%' and concat_ws('', ' ', series_name) like '%", $keywords) . "%') )";
	$series_query = tep_db_query($series_query_row);
	while ($row = tep_db_fetch_array($series_query)) {
	  $series_to_search[] = $row['series_id'];
	}
	$total_found += sizeof($series_to_search);
  }

  if (tep_not_null($keywords)) {
	$authors_query_row = "select distinct authors_id from " . TABLE_AUTHORS . " where authors_status = '1' and language_id = '" . (int)$languages_id . "' and ( (concat_ws('', ' ', authors_name) like '%" . str_replace(' ', "%' and concat_ws('', ' ', authors_name) like '%", $keywords) . "%') )";
	$authors_query = tep_db_query($authors_query_row);
	while ($row = tep_db_fetch_array($authors_query)) {
	  $authors_to_search[] = $row['authors_id'];
	}
	$total_found += sizeof($authors_to_search);
  }

  $listing_sql_from = '';
  $listing_sql_where = '';
  $listing_sql_group_by = '';
  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
	if (!tep_session_is_registered('customer_country_id')) {
	  $customer_country_id = STORE_COUNTRY;
	  $customer_zone_id = STORE_ZONE;
	}
  }

  if (isset($HTTP_GET_VARS['categories_id']) && tep_not_null($HTTP_GET_VARS['categories_id'])) {
	$subcategories_array = array($HTTP_GET_VARS['categories_id']);
	if (isset($HTTP_GET_VARS['inc_subcat']) && ($HTTP_GET_VARS['inc_subcat'] == '1')) {
	  tep_get_subcategories($subcategories_array, $HTTP_GET_VARS['categories_id']);
	}
	$listing_sql_from .= ", " . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c';
	$listing_sql_where .= " and p2c.products_id = p.products_id and p2c.categories_id in ('" . implode("', '", $subcategories_array) . "')";
  }

  if (tep_not_null($pfrom)) {
	if ($currencies->is_set($currency)) {
	  $rate = $currencies->get_value($currency);
	  $pfrom = $pfrom / $rate;
	}
  }

  if (tep_not_null($pto)) {
	if (isset($rate)) {
	  $pto = $pto / $rate;
	}
  }

  if ($pfrom > 0) $listing_sql_where .= " and p.products_price >= '" . str_replace(',', '.', (float)$pfrom) . "'";
  if ($pto > 0) $listing_sql_where .= " and p.products_price <= '" . str_replace(',', '.', (float)$pto) . "'";

  if ($year_from > 0) $listing_sql_where .= " and p.products_year >= '" . str_replace(',', '.', (int)$year_from) . "'";
  if ($year_to > 0) $listing_sql_where .= " and p.products_year <= '" . str_replace(',', '.', (int)$year_to) . "'";

  if (isset($HTTP_GET_VARS['manufacturers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
	$listing_sql_where .= " and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'";
  } elseif (tep_not_null($HTTP_GET_VARS['manufacturers'])) {
    $manufacturers = stripslashes(trim(htmlspecialchars(strip_tags($HTTP_GET_VARS['manufacturers']), ENT_QUOTES)));
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
  }

  if (tep_not_null($HTTP_GET_VARS['series_id'])) {
	$series_id = $HTTP_GET_VARS['series_id'];
	$listing_sql_where .= " and p.series_id = '" . (int)$series_id . "'";
	$search_params_array['series_id'] = $series_id;
  } elseif (tep_not_null($HTTP_GET_VARS['series'])) {
    $series = stripslashes(trim(htmlspecialchars(strip_tags($HTTP_GET_VARS['series']), ENT_QUOTES)));
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

  if (tep_not_null($HTTP_GET_VARS['authors_id'])) {
	$authors_id = $HTTP_GET_VARS['authors_id'];
	$listing_sql_where .= " and p.authors_id = '" . (int)$authors_id . "'";
	$search_params_array['authors_id'] = $authors_id;
  } elseif (tep_not_null($HTTP_GET_VARS['authors'])) {
    $authors = stripslashes(trim(htmlspecialchars(strip_tags($HTTP_GET_VARS['authors']), ENT_QUOTES)));
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

  if (tep_not_null($keywords)) {
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
	if (preg_match('/^[-\d(x|õ)]+$/', $keywords)) {
	  if (strlen($keywords) >= 7) {
		$products_models_found_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_MODELS . " where products_model_1 like '%" . preg_replace('/[^\dxõ]/', '', $keywords) . "'");
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
	$searched_products = array();
	reset($keywords_to_search);
	while (list(, $keyword_to_search) = each($keywords_to_search)) {
	  $keyword_query = tep_db_query("select search_keywords_id from " . TABLE_SEARCH_KEYWORDS . " where search_keywords_word = '" . $keyword_to_search . "'");
	  $keyword_row = tep_db_fetch_array($keyword_query);
	  $searched_products_temp = array();
	  $search_keywords_id = (int)$keyword_row['search_keywords_id'];
	  if ($search_keywords_id > 0) {
		tep_db_query("update " . TABLE_SEARCH_KEYWORDS . " set last_modified = now(), search_keywords_count = search_keywords_count + 1 where search_keywords_id = '" . (int)$search_keywords_id . "'");
	  } else {
		tep_db_query("insert into " . TABLE_SEARCH_KEYWORDS . " (search_keywords_word, search_keywords_count, date_added) values ('" . tep_db_input($keyword_to_search) . "', '1', now())");
		$search_keywords_id = tep_db_insert_id();
		tep_db_query("insert ignore into " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " (search_keywords_id, products_id) select '" . (int)$search_keywords_id . "', products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where products_text like '% " . $keyword_to_search . " %'");
	  }
	  $non_used_keywords_query = tep_db_query("select search_keywords_id from " . TABLE_SEARCH_KEYWORDS . " where date_added < '" . date('Y-m-d H:i:s', time()-60*60*24*30) . "' and last_modified is null");
	  while ($non_used_keywords = tep_db_fetch_array($non_used_keywords_query)) {
		tep_db_query("delete from " . TABLE_SEARCH_KEYWORDS . " where search_keywords_id = '" . (int)$non_used_keywords['search_keywords_id'] . "'");
		tep_db_query("delete from " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . " where search_keywords_id = '" . (int)$non_used_keywords['search_keywords_id'] . "'");
	  }

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
//	$searched_products_query = tep_db_unbuffered_query($searched_products_query_raw);
//	while ($searched_products_row = tep_db_fetch_array($searched_products_query)) {
//	  $searched_products_fulltext[] = $searched_products_row['products_id'];
//	}
//	$searched_products = array_merge($searched_products, $searched_products_fulltext);

	$listing_sql_where .= " and p.products_id in ('" . implode("', '", $searched_products) . "')";
  }

  $products_by_types_found = array();
  $products_types_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS_TYPES . " where products_types_status = '1' and products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
  while ($products_types = tep_db_fetch_array($products_types_query)) {
	$products_query_row = "select distinct p.products_id from " . TABLE_PRODUCTS . " p" . $listing_sql_from . " where p.products_status = '1'" . ((int)PRODUCT_SHOW_NONACTIVE=='0' ? " and p.products_listing_status = '1'" : "") . " and p.products_types_id = '" . (int)$products_types['products_types_id'] . "'" . $listing_sql_where . $listing_sql_group_by;
	$products_query = tep_db_query($products_query_row);
	while ($row = tep_db_fetch_array($products_query)) {
	  $products_to_search[] = $row['products_id'];
	  $products_by_types_found[$products_types['products_types_id']][] = $row['products_id'];
	}
	$total_found += sizeof($products_to_search);
  }

  if (tep_not_null($keywords)) {
	$information_query_row = "select distinct information_id from " . TABLE_INFORMATION . " pd where information_status = '1' and language_id = '" . (int)$languages_id . "' and ( (information_name like '%" . str_replace(' ', "%' and information_name like '%", $keywords) . "%') or (information_description like '%" . str_replace(' ', "%' and information_description like '%", $keywords) . "%') )";
	$information_query = tep_db_query($information_query_row);
	while ($row = tep_db_fetch_array($information_query)) {
	  $information_to_search[] = $row['information_id'];
	}
	$total_found += sizeof($information_to_search);
  }

  if (tep_not_null($keywords)) {
	$news_query_row = "select distinct news_id from " . TABLE_NEWS . " pd where news_status = '1' and language_id = '" . (int)$languages_id . "' and ( (news_name like '%" . str_replace(' ', "%' and news_name like '%", $keywords) . "%') or (news_description like '%" . str_replace(' ', "%' and news_description like '%", $keywords) . "%') )";
	$news_query = tep_db_query($news_query_row);
	while ($row = tep_db_fetch_array($news_query)) {
	  $news_to_search[] = $row['news_id'];
	}
	$total_found += sizeof($news_to_search);
  }

  $request_string = '';
  if (tep_not_null($keywords)) {
	$request_string .= tep_strtolower(ENTRY_KEYWORDS) . ' "<strong>' . $keywords . '</strong>"';
  }
  if (tep_not_null($HTTP_GET_VARS['categories_id'])) {
	$category_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$HTTP_GET_VARS['categories_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$category_array = tep_db_fetch_array($category_query);
	$request_string .= (tep_not_null($request_string) ? '; ' : '') . tep_strtolower(ENTRY_CATEGORY) . ' "<strong>' . $category_array['categories_name'] . '</strong>"';
	if ($HTTP_GET_VARS['inc_subcat']=='1') {
	  $request_string .= ', <strong>' . tep_strtolower(ENTRY_INCLUDE_SUBCATEGORIES) . '</strong>';
	}
  }
  if (tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
	$manufacturer_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
	$manufacturer_array = tep_db_fetch_array($manufacturer_query);
	$request_string .= (tep_not_null($request_string) ? '; ' : '') . tep_strtolower(ENTRY_MANUFACTURER) . ' <strong>' . $manufacturer_array['manufacturers_name'] . '</strong>';
  }
  $temp_string = '';
  if (tep_not_null($pfrom)) {
	$temp_string .= ' ' . TEXT_FROM . ' <strong>' . $currencies->format($pfrom) . '</strong>';
  }
  if (tep_not_null($pto)) {
	$temp_string .= ' ' . TEXT_TO . ' <strong>' . $currencies->format($pto) . '</strong>';
  }
  if (tep_not_null($temp_string)) $request_string .= (tep_not_null($request_string) ? '; ' : '') . tep_strtolower(ENTRY_PRICE) . $temp_string;
  $temp_string = '';
  if (tep_not_null($year_from)) {
	$temp_string .= ' ' . TEXT_FROM . ' <strong>' . $year_from . '</strong>';
  }
  if (tep_not_null($year_to)) {
	$temp_string .= ' ' . TEXT_TO . ' <strong>' . $year_to . '</strong>';
  }
  if (tep_not_null($temp_string)) $request_string .= (tep_not_null($request_string) ? '; ' : '') . tep_strtolower(ENTRY_YEAR) . $temp_string;

  $advanced_search_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ADVANCED_SEARCH) . "' and language_id = '" . (int)$languages_id . "'");
  $advanced_search_page = tep_db_fetch_array($advanced_search_page_query);

  $breadcrumb->add($advanced_search_page['pages_name'], tep_href_link(FILENAME_ADVANCED_SEARCH, '', 'NONSSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, tep_get_all_get_params(), 'NONSSL', true, false));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>