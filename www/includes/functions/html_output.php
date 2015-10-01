<?php
////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID, $spider_flag;

    if (!tep_not_null($page)) $page = FILENAME_DEFAULT;

	$link = '';

	$redirect_url = '';
	$categories_id = '';
	$products_id = '';
	$categories_parameters = '';
	$sections_id = '';
	$information_id = '';
	$sections_parameters = '';
	$news_id = '';
	$news_parameters = '';
	$cname = '';
	$sname = '';
	$nname = '';
	$rname = '';
	$mname = '';
	$product_type_id = 0;
	if (basename($page)==FILENAME_CATEGORIES) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'cPath') {
		  $categories_id = $param_value;
		} elseif ($param_name == 'categories_id') {
		  $categories_id = $param_value;
		} elseif ($param_name == 'products_id') {
		  $products_id = $param_value;
		} elseif ($param_name == 'tPath') {
		  $product_type_id = $param_value;
		} else {
		  $categories_parameters .= (tep_not_null($categories_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'cName') {
		  $cname = $param_value;
#		  if (substr($cname, -1)=='/') $cname = substr($cname, 0, -1);
#		  if (substr($cname, 0, 1)=='/') $cname = substr($cname, 1);
		}
	  }
	  if (tep_not_null($products_id) && empty($categories_id)) $categories_id = tep_get_product_path($products_id);

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
			$product_type_id = $categories_path['products_types_id'];
		  }
		}
	  }
	  if (empty($categories_id) && empty($products_id) && tep_not_null($cname)) {
		$link .= $cname;
	  }
	  $parameters = $categories_parameters;
	} elseif (basename($page)==FILENAME_PRODUCT_INFO) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'products_id') {
		  $products_id = $param_value;
		} else {
		  $categories_parameters .= (tep_not_null($categories_parameters) ? '&' : '') . $param;
		}
	  }

	  if (tep_not_null($products_id)) {
		$link .= $products_id . '.html';
	  }
	  $parameters = $categories_parameters;
	} elseif (basename($page)==FILENAME_SPECIALS) {
	  $params = explode("&", $parameters);
	  $year = '';
	  $month = '';
	  $week = '';
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'tPath') {
		  $tpath = $param_value;
		} elseif ($param_name == 'year') {
		  $year = (int)$param_value;
		} elseif ($param_name == 'month') {
		  $month = (int)$param_value;
		} elseif ($param_name == 'week') {
		  $week = (int)$param_value;
		} elseif ($param_name == 'view') {
		  $specials_view = $param_value;
		} else {
		  $types_parameters .= (tep_not_null($types_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'specials/';
	  if (tep_not_null($tpath)) {
		$types_path_query = tep_db_query("select specials_types_path from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$tpath . "' limit 1");
		$types_path = tep_db_fetch_array($types_path_query);
		if (tep_not_null($types_path['specials_types_path'])) {
		  $link .= $types_path['specials_types_path'] . '/';
		  if ($specials_view=='rss') {
			$link = substr($link, 0, -1) . '.rss';
			$types_parameters = '';
			$add_session_id = false;
		  } elseif ($year>2000 && $year<=date('Y')) {
			$link .= $year . '/';
			if ($month>0 && $month<=12) {
			  $m_array = array('', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
//			  $link .= $m_array[(int)$month] . '/';
			  $link .= sprintf('%02d', (int)$month) . '/';
			  if ($week>0 && $week<=53) {
				$link .= (int)$week . '/';
			  }
			}
		  }
		}
	  }
	  $parameters = $types_parameters;
	} elseif (basename($page)==FILENAME_REVIEWS) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'tPath') {
		  $tpath = $param_value;
		} elseif ($param_name == 'view') {
		  $reviews_view = $param_value;
		} else {
		  $types_parameters .= (tep_not_null($types_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'reviews_id') {
		  $reviews_id = $param_value;
		}
	  }

	  $link .= 'reviews/';
	  if (empty($tpath) && $reviews_id > 0) {
		$review_type_query = tep_db_query("select reviews_types_id from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
		$review_type = tep_db_fetch_array($review_type_query);
		$tpath = $review_type['reviews_types_id'];
	  }
	  if (tep_not_null($tpath)) {
		$types_path_query = tep_db_query("select reviews_types_path from " . TABLE_REVIEWS_TYPES . " where reviews_types_id = '" . (int)$tpath . "' limit 1");
		$types_path = tep_db_fetch_array($types_path_query);
		if (tep_not_null($types_path['reviews_types_path'])) {
		  $link .= $types_path['reviews_types_path'] . '/';
		  if ($reviews_view=='rss') {
			$link = substr($link, 0, -1) . '.rss';
			$types_parameters = '';
			$add_session_id = false;
		  }
		}
	  }
	  $parameters = $types_parameters;
	} elseif (basename($page)==FILENAME_BOARDS) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'tPath') {
		  $tpath = $param_value;
		} elseif ($param_name == 'boards_id') {
		  $boards_id = $param_value;
		} elseif ($param_name == 'view') {
		  $boards_view = $param_value;
		} else {
		  $types_parameters .= (tep_not_null($types_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'boards/';
	  if (empty($tpath) && $boards_id > 0) {
		$board_type_query = tep_db_query("select boards_types_id from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
		$board_type = tep_db_fetch_array($board_type_query);
		$tpath = $board_type['boards_types_id'];
	  }
	  if (tep_not_null($tpath)) {
		$types_path_query = tep_db_query("select boards_types_path from " . TABLE_BOARDS_TYPES . " where boards_types_id = '" . (int)$tpath . "' limit 1");
		$types_path = tep_db_fetch_array($types_path_query);
		if (tep_not_null($types_path['boards_types_path'])) {
		  $link .= $types_path['boards_types_path'] . '/';
		  if ($boards_view=='rss') {
			$link = substr($link, 0, -1) . '.rss';
			$types_parameters = '';
			$add_session_id = false;
		  } elseif (tep_not_null($boards_id)) {
			$link .= $boards_id . '.html';
		  }
		}
	  }
	  $parameters = $types_parameters;
	} elseif (basename($page)==FILENAME_HOLIDAY) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'hPath') {
		  $hpath = $param_value;
		} else {
		  $types_parameters .= (tep_not_null($types_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'new_year/' . (tep_not_null($hpath) ? $hpath . '/' : '');
	  $parameters = $types_parameters;
	} elseif (basename($page)==FILENAME_MANUFACTURERS) {
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

	  $link .= 'publishers/';
	  if (tep_not_null($manufacturers_id)) {
		$manufacturers_path_query = tep_db_query("select manufacturers_id, manufacturers_path from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "' limit 1");
		$manufacturers_path = tep_db_fetch_array($manufacturers_path_query);
		$link .= (tep_not_null($manufacturers_path['manufacturers_path']) ? $manufacturers_path['manufacturers_path'] : $manufacturers_path['manufacturers_id']) . '.html';
	  }
	  $parameters = $manufacturers_parameters;
	} elseif (basename($page)==FILENAME_SERIES) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'series_id') {
		  $series_id = $param_value;
		} elseif ($param_name == 'tPath') {
		  $product_type_id = $param_value;
		} else {
		  $series_parameters .= (tep_not_null($series_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'series/';
	  if (tep_not_null($series_id)) {
		$series_path_query = tep_db_query("select series_id, series_path from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "' limit 1");
		$series_path = tep_db_fetch_array($series_path_query);
		$link .= (tep_not_null($series_path['series_path']) ? $series_path['series_path'] : $series_path['series_id']) . '.html';
	  }
	  $parameters = $series_parameters;
	} elseif (basename($page)==FILENAME_AUTHORS) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'authors_id') {
		  $authors_id = $param_value;
		} else {
		  $authors_parameters .= (tep_not_null($authors_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'authors/';
	  if (tep_not_null($authors_id)) {
		$authors_path_query = tep_db_query("select authors_id, authors_path from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "' limit 1");
		$authors_path = tep_db_fetch_array($authors_path_query);
		$link .= (tep_not_null($authors_path['authors_path']) ? $authors_path['authors_path'] : $authors_path['authors_id']) . '.html';
	  }
	  $parameters = $authors_parameters;
	} elseif (basename($page)==FILENAME_FOREIGN) {
	  $params = explode("&", $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'products_id') {
		  $products_id = $param_value;
		} else {
		  $products_parameters .= (tep_not_null($products_parameters) ? '&' : '') . $param;
		}
	  }

	  $link .= 'foreign/';
	  if (tep_not_null($products_id)) {
		$link .= $products_id . '.html';
	  }
	  $parameters = $products_parameters;
	} elseif (basename($page)==FILENAME_DEFAULT) {
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
	} elseif (basename($page)==FILENAME_NEWS) {
	  $link .= 'news/';
	  $params = explode('&', $parameters);
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name, $param_value) = explode('=', $param);
		if ($param_name == 'news_id') {
		  $news_id = $param_value;
		}
		if ($param_name == 'year') {
		  $news_year = $param_value;
		}
		if ($param_name == 'month') {
		  $news_month = $param_value;
		}
		if ($param_name == 'view') {
		  $news_view = $param_value;
		}
		if ($param_name == 'type') {
		  $news_type = $param_value;
		}
		if ($param_name == 'tPath') {
		  $news_path = $param_value;
		}
		if (!in_array($param_name, array('news_id', 'year', 'month', 'view', 'type', 'tPath'))) {
		  $news_parameters .= (tep_not_null($news_parameters) ? '&' : '') . $param;
		}
		if ($param_name == 'nName') {
		  $nname = $param_value;
#		  if (substr($cname, -1)=='/') $cname = substr($cname, 0, -1);
#		  if (substr($cname, 0, 1)=='/') $cname = substr($cname, 1);
		}
		$parameters = $news_parameters;
	  }

	  if (tep_not_null($news_path)) {
		$nInfo_query = tep_db_query("select news_types_path, news_types_path as news_date from " . TABLE_NEWS_TYPES . " where news_types_id = '" . (int)$news_path . "'");
	  } else {
		$nInfo_query = tep_db_query("select date_format(date_added, '%Y/%m') as news_date from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "' limit 1");
	  }
	  $nInfo = tep_db_fetch_array($nInfo_query);
	  if (tep_not_null($news_id)) {
		$link .= $nInfo['news_date'] . '/' . $news_id . '.html';
		$parameters = $news_parameters;
	  } elseif ($news_view=='rss') {
		$link .= (tep_not_null($nInfo['news_types_path']) ? $nInfo['news_types_path'] : $news_type) . '.rss';
		$parameters = '';
		$add_session_id = false;
	  } elseif (tep_not_null($news_path)) {
		$news_type_query = tep_db_query("select news_types_path from " . TABLE_NEWS_TYPES . " where news_types_id = '" . (int)$news_path . "'");
		$news_type = tep_db_fetch_array($news_type_query);
		$link .= $news_type['news_types_path'] . '/';
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

	if ($page!=FILENAME_DEFAULT && $page!=DIR_WS_ONLINE_STORE && $page!=DIR_WS_ONLINE_STORE) {
	  $params = explode('&', $parameters);
	  $temp_parameters = '';
	  reset($params);
	  while (list(, $param) = each($params)) {
		list($param_name) = explode('=', $param);
		if (!in_array($param_name, array('cPath', 'sPath', 'tPath', 'pName', 'nName', 'cName', 'sName', 'info_id', 'iName', 'tName', 'mName', 'rName'))) {
		  if (basename($page)==FILENAME_PRODUCT_INFO && $param_name=='products_id') {
		  } else {
			$temp_parameters .= (tep_not_null($temp_parameters) ? '&' : '') . $param;
		  }
		}
	  }
#	  if (tep_not_null($temp_parameters)) 
	  $parameters = $temp_parameters;
	}

	if (in_array(basename($page), array(FILENAME_CATEGORIES, FILENAME_PRODUCT_INFO, FILENAME_SPECIALS, FILENAME_MANUFACTURERS, FILENAME_DEFAULT, FILENAME_NEWS, FILENAME_SERIES, FILENAME_AUTHORS, FILENAME_REVIEWS, FILENAME_FOREIGN, FILENAME_BOARDS, FILENAME_HOLIDAY))) {
	  if (!in_array(basename($page), array(FILENAME_DEFAULT, FILENAME_NEWS, FILENAME_BOARDS, FILENAME_HOLIDAY))) {
		if ( (basename($page)==FILENAME_PRODUCT_INFO && tep_not_null($products_id)) || (basename($page)==FILENAME_CATEGORIES && tep_not_null($categories_id)) || (basename($page)==FILENAME_SERIES && tep_not_null($series_id)) ) {
		  if (basename($page)==FILENAME_CATEGORIES && tep_not_null($categories_id)) {
			$product_type_info_query = tep_db_query("select products_types_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
			$product_type_info = tep_db_fetch_array($product_type_info_query);
			$product_type_id = $product_type_info['products_types_id'];
		  } elseif (basename($page)==FILENAME_SERIES && tep_not_null($series_id)) {
			$product_type_info_query = tep_db_query("select products_types_id from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "'");
			$product_type_info = tep_db_fetch_array($product_type_info_query);
			$product_type_id = $product_type_info['products_types_id'];
		  } else {
			$product_type_info_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			$product_type_info = tep_db_fetch_array($product_type_info_query);
			$product_type_id = $product_type_info['products_types_id'];
		  }
		}
		$product_type_info_query = tep_db_query("select products_types_path from " . TABLE_PRODUCTS_TYPES . " where " . ((int)$product_type_id==0 ? "products_types_default_status = '1'" : "products_types_id = '" . (int)$product_type_id . "'") . "");
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$product_type_path = $product_type_info['products_types_path'];
		$link = $product_type_path . '/' . $link;
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


// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) ) {
      if (tep_not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
		$_sid = tep_session_name() . '=' . tep_session_id();
      }
    }

	$other_server_check = substr($link, 0, 4) == 'http';

    if (tep_not_null($_sid) && $spider_flag == false && basename($page) != FILENAME_REDIRECT) {
      $link .= $separator . $_sid;
    }
	if (tep_not_null($redirect_url)) {
	  $link = $redirect_url;
	  if (!$other_server_check && tep_not_null($_sid)) {
		$link .= $separator . $_sid;
	  }
	}
	if (substr($link, 0, 1)=='/') $link = substr($link, 1);
	$link = str_replace('&amp;', '&', $link);
//	$link = str_replace('&', '&amp;', $link);

    while (strpos($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strpos($link, '?&')) $link = str_replace('?&', '?', $link);
    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
    while (strpos($link, '//')) $link = str_replace('//', '/', $link);
	if ($other_server_check) $link = preg_replace('/(http[s]?):\//i', '$1://', $link);

	if ($other_server_check == false) {
	  if ($connection == 'SSL' && ENABLE_SSL == true) $link = HTTPS_SERVER . DIR_WS_CATALOG . $link;
	  else $link = HTTP_SERVER . DIR_WS_CATALOG . $link;
	}

    if ($connection != 'NONSSL' && $connection != 'SSL') {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
    }

    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if (empty($src)) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . tep_output_string($src) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . tep_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = $image_size[0] * $ratio;
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      }
    }

    if (tep_not_null($width)) {
      $image .= ' width="' . tep_output_string($width) . '"';
	}
	if (tep_not_null($height)) {
      $image .= ' height="' . tep_output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '', $type = '') {
    global $language;

	if (empty($type)) $type = 'submit';

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_TEMPLATES_IMAGES . 'buttons/' . $image) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' style="border: 0;" />';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    global $language;

	return tep_image(DIR_WS_TEMPLATES_IMAGES . 'buttons/' . $image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . tep_output_string($name) . '" action="' . tep_output_string($action) . '" method="' . tep_output_string($method) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a form submit field
  function tep_draw_submit_field($name, $value, $parameters = '') {
    return tep_draw_input_field($name, $value, $parameters, 'submit', false);
  }

////
// Output a form file field
  function tep_draw_file_field($name, $parameters = '') {
    return tep_draw_input_field($name, '', $parameters, 'file', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
      $selection .= ' checked="checked"';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . tep_output_string($name) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= stripslashes($GLOBALS[$name]);
    } elseif (tep_not_null($text)) {
      $field .= $text;
    }

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
// Hide form elements
  function tep_hide_session_id() {
    global $session_started, $SID;

    if (($session_started == true) && tep_not_null($SID)) {
      return tep_draw_hidden_field(tep_session_name(), tep_session_id());
    }
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $def = '', $parameters = '', $required = false) {
	$default = array();

    if (empty($def) && isset($GLOBALS[$name])) $def = stripslashes($GLOBALS[$name]);

	if (!is_array($def)) $default[0] = $def;
	else $default = $def;

    $field = '<select name="' . tep_output_string($name) . '"';
    if (tep_not_null($parameters)) $field .= ' ' . $parameters;
    $field .= '>';
	reset($values);
	while (list(, $value) = each($values)) {
	  if (!isset($value['active'])) $value['active'] = '1';
      if (tep_not_null($value['id']) || tep_not_null($value['text'])) {
		if ($value['active']=='0') {
		  $field .= '<optgroup label="' . tep_output_string($value['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '"' . (tep_not_null($value['params']) ? ' ' . $value['params'] : '') . '></optgroup>' . "\n";
		} else {
		  $field .= '<option value="' . tep_output_string($value['id']) . '"';
		  if (in_array($value['id'], $default)) {
			$field .= ' selected="selected"';
		  }
		  $field .= (tep_not_null($value['params']) ? ' ' . $value['params'] : '') . '>' . tep_output_string($value['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
		}
	  }
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    global $languages_id;

	$countries_query = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int)$languages_id . "' order by countries_name");
	if (tep_db_num_rows($countries_query) > 1) {
 	  $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  while ($countries = tep_db_fetch_array($countries_query)) {
		$countries_array[] = array('id' => $countries['countries_id'], 'text' => $countries['countries_name']);
	  }
	  return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
    } else {
	  $countries = tep_db_fetch_array($countries_query);
	  return $countries['countries_name'] . tep_draw_hidden_field($name, $countries['countries_id']);
	}
  }

  function tep_get_subscribe_status($category_id, $type_id) {
	global $customer_id;
	if (!tep_session_is_registered('customer_id')) return 0;
	$category = tep_db_query("select user_id from `subscribe` where user_id = '" . (int)$customer_id . "' and category_id = '" . (int)$category_id . "' and type_id = '" . (int)$type_id . "'");
	return tep_db_num_rows($category);
  }
?>