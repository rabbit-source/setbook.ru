<?php
  require('includes/application_top.php');

  $show_subcategories_products = false;
  $show_subcategories = false;

  if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
  $qvars = explode('&', $_SERVER['QUERY_STRING']);
  reset($qvars);
  while (list(, $qvar) = each($qvars)) {
	list($qvar_key, $qvar_value) = explode('=', $qvar);
	$HTTP_GET_VARS[$qvar_key] = $qvar_value;
  }

  $category_depth = 'top';
  if (isset($cPath) && tep_not_null($cPath)) {
	$categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
	$cateqories_products = tep_db_fetch_array($categories_products_query);
	if ($cateqories_products['total'] > 0) {
	  $category_depth = 'products'; // display products
	} else {
	  $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");
	  $category_parent = tep_db_fetch_array($category_parent_query);
	  if ($category_parent['total'] > 0) {
		$category_depth = 'nested'; // navigate through the categories
	  } else {
		$category_depth = 'products'; // category has no products, but display the 'no products' message
	  }
	}
	$products_listing_check_query = tep_db_query("select products_listing from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
	$products_listing_check = tep_db_fetch_array($products_listing_check_query);
	$show_subcategories_products = $products_listing_check['products_listing']>0;
	$show_subcategories = ($products_listing_check['products_listing']==0 || $products_listing_check['products_listing']==2);
  }
  if ($products_listing_check['products_listing']==0 && $category_depth=='products') $category_depth = 'nested';

  $category_check_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$show_product_type . "' and categories_id = '" . (int)$current_category_id . "' and categories_listing_status = '1'");
  $category_check = tep_db_fetch_array($category_check_query);
  if ($category_check['total'] > 0) {
	$content_id = $current_category_id;
	$content_type = 'category';
  } else {
	$content_id = $show_product_type;
	$content_type = 'type';
  }

  $content = FILENAME_CATEGORIES;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  if ($category_depth=='top') define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if ($category_depth != 'products') {
	if (isset($HTTP_GET_VARS['keywords']) || isset($HTTP_GET_VARS['detailed']) || isset($HTTP_GET_VARS['manufacturers']) || isset($HTTP_GET_VARS['series']) || isset($HTTP_GET_VARS['authors']) || $HTTP_GET_VARS['view']=='with_fragments' || ($show_product_type==1 && tep_not_null($HTTP_GET_VARS['view']) && $HTTP_GET_VARS['view']!='all')) {
	  $category_depth = 'products'; // display products
	  if ($HTTP_GET_VARS['view']=='with_fragments') $breadcrumb->add(TEXT_BOOKS_WITH_FRAGMENTS, tep_href_link(FILENAME_CATEGORIES, 'view=with_fragments'));
	}
  }

  if ($category_depth == 'top') {
	$subcategories_check_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$show_product_type . "' and parent_id > '0' and categories_status = '1' and categories_listing_status = '1'");
	$subcategories_check = tep_db_fetch_array($subcategories_check_query);
	if ($subcategories_check['total']==0) {
	  $show_subcategories_products = true;
	  $show_subcategories = false;
	} else {
	  $show_subcategories = true;
	}
  }

  if ($current_category_id==0 && tep_not_null($HTTP_GET_VARS['view']) && $HTTP_GET_VARS['view']!='with_fragments') {
	if ($HTTP_GET_VARS['view']=='all') {
	  $breadcrumb->add(TEXT_ALL_CATEGORY_PRODUCTS, tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&view=all'));
	} else {
	  $specials_type_info_query = tep_db_query("select specials_types_path, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and specials_types_path = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['view'])) . "'");
	  if (tep_db_num_rows($specials_type_info_query) > 0) {
		$specials_type_info = tep_db_fetch_array($specials_type_info_query);
		$breadcrumb->add( $specials_type_info['specials_types_name'], tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&view=' . $specials_type_info['specials_types_path']));
	  }
	}
  }

  $letters_string = '';
  if ($show_product_type_letter_search=='1') {
	$letter = '';
	if (isset($HTTP_GET_VARS['letter'])) {
	  $letter = tep_db_prepare_input(urldecode($HTTP_GET_VARS['letter']));
	  if ($letter != 'all') $letter = substr($letter, 0, 1);
	  $letter = strtolower($letter);
	}
	if (!preg_match('/#|[àáâãäå¸æçèéêëìíîïğñòóôõö÷øùúûüışÿÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÚÛÜİŞß]|[a-z0-9]/i', $letter) || empty($letter)) $letter = 'all';

	$other_letters_string = '';
	$other_letters_found = false;
	$letters = array();
	$periodicals_products = array();
	$products_query = tep_db_query("select distinct substring(products_name, 1, 1) as letter from " . TABLE_PRODUCTS_INFO . " where products_types_id = '" . (int)$show_product_type . "' and products_status = '1'" . ((int)PRODUCT_SHOW_NONACTIVE=='0' ? " and products_listing_status = '1'" : "") . "");
	while ($products = tep_db_fetch_array($products_query)) {
	  if (!in_array(strtolower($products['letter']), $letters)) $letters[] = strtolower($products['letter']);
	}
	sort($letters);
	reset($letters);
	while (list(, $l) = each($letters)) {
	  if (preg_match('/[\d\w]/i', $l)) {
		$letters_string .= '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&letter=' . urlencode($l)) . '"' . ($letter==$l ? ' class="active_letter"' : '') . '>' . strtoupper($l) . '</a>&nbsp; ';
	  } elseif (!$other_letters_found) {
		$other_letters_string = '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&letter=' . urlencode('#')) . '"' . ($l=='#' ? ' class="active_letter"' : '') . '>#</a>&nbsp; ';
		$other_letters_found = true;
	  }
	}
	$letters_string = '<p align="center">' . $other_letters_string . $letters_string . '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type) . '"' . ($letter=='all' ? 'class="active_letter"' : '') . '>' . TEXT_ALL_PRODUCTS . '</a></p>' . "\n";

	if (tep_not_null($HTTP_GET_VARS['letter']) && $letter!='all') {
	  $category_depth = 'products';

	  $breadcrumb->add(sprintf(TEXT_BY_LETTER, strtoupper($letter)), tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&letter=' . $letter));

	  $products_to_search = array();
	  $products_query_row = "select products_id from " . TABLE_PRODUCTS_INFO . " where products_types_id = '" . (int)$show_product_type . "' and products_status = '1'" . ((int)PRODUCT_SHOW_NONACTIVE=='0' ? " and products_listing_status = '1'" : "") . "";
	  if ($letter=='#') $products_query_row .= " and lower(products_name) rlike '^[^àáâãäå¸æçèéêëìíîïğñòóôõö÷øùúûüışÿÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÚÛÜİŞßa-z0-9]'";
	  elseif ($letter!='all' && preg_match('/[àáâãäå¸æçèéêëìíîïğñòóôõö÷øùúûüışÿÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÚÛÜİŞß]|[a-z0-9]/i', $letter)) $products_query_row .= " and lower(products_name) like '" . tep_db_input($letter) . "%'";
	  else $products_query_row .= "";
	  $products_query = tep_db_query($products_query_row);
	  while ($products = tep_db_fetch_array($products_query)) {
		$products_to_search[] = $products['products_id'];
	  }
	}
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>