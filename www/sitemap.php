<?php
  require('includes/application_top.php');

  function tep_get_sections_for_sitemap($parent_id = 0) {
	global $languages_id;

	$sections_array = array();
	$sort_array = array();
	$sections_query = tep_db_query("select sections_id, sort_order from " . TABLE_SECTIONS . " where sections_status = '1' and sections_sitemap_status = '1' and parent_id = '" . (int)$parent_id . "' and language_id = '" . (int)$languages_id . "' order by sort_order, sections_name");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  $sort_array['section:' . $sections['sections_id']] = $sections['sort_order'];
	}
	$information_query = tep_db_query("select i.information_id, i.sort_order, i2s.information_default_status from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_sitemap_status = '1' and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$parent_id . "' and i.language_id = '" . (int)$languages_id . "' order by i2s.information_default_status desc, i.sort_order, i.information_name");
	while ($information = tep_db_fetch_array($information_query)) {
	  if ($information['information_default_status']==1) $information['sort_order'] = -1;
	  $sort_array['information:' . $information['information_id']] = $information['sort_order'];
	}
	asort($sort_array);
	reset($sort_array);
	while (list($array) = each($sort_array)) {
	  list($type, $id) = explode(':', $array);
	  if ($type=='section') {
		$information_query = tep_db_query("select i.information_id, i2s.information_default_status, i.information_name from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$id . "' and i.language_id = '" . (int)$languages_id . "' order by i2s.information_default_status desc, i.sort_order limit 1");
		$information = tep_db_fetch_array($information_query);
		if (tep_not_null($information)) {
		  if ($information['information_default_status']==1) $link = tep_href_link(FILENAME_DEFAULT, 'sPath=' . $id);
		  else $link = '#';
		  $section_query = tep_db_query("select sections_name from " . TABLE_SECTIONS . " where sections_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
		  $section = tep_db_fetch_array($section_query);

		  $sections_array[] = array('type' => 'section', 'id' => $id, 'link' => $link, 'title' => $section['sections_name']);
		}
	  } else {
		$information_query = tep_db_query("select i.information_name, i2s.information_default_status from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_id = '" . (int)$id . "' and i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "'");
		$information = tep_db_fetch_array($information_query);
		$link = tep_href_link(FILENAME_DEFAULT, 'sPath=' . $parent_id . '&info_id=' . $id);
		if ($information['information_default_status']==1 && $parent_id==0) $information['information_name'] = SITEMAP_INFORMATION_LINK;
		$sections_array[] = array('type' => 'information', 'id' => $id, 'link' => $link, 'title' => $information['information_name']);
	  }
	}

	return $sections_array;
  }

  function tep_show_sections_map($parent_id = 0, $level = 0) {
	global $languages_id;

	$sections_string = '';
	if ($level==0) $sections_string .= '<div id="sitemap_sections">' . "\n";

	$first_page_found = false;
	$menu = tep_get_sections_for_sitemap($parent_id);
	reset($menu);
	while (list($i, $menu_item) = each($menu)) {
	  if (tep_not_null($menu_item['link'])) {
		if ($menu_item['link']==tep_href_link(FILENAME_DEFAULT)) {
		  $sections_string .= '  <a href="' . $menu_item['link'] . '" class="level_0">' . $menu_item['title'] . '</a>' . "\n" .
		  '  <div class="level_0">' . "\n";
		  $first_page_found = true;
		} else {
		  $sections_string .= '	<a href="' . $menu_item['link'] . '" class="level_' . ($level+1) . '">' . $menu_item['title'] . '</a>' . "\n";
		}
		if ($menu_item['type']=='section' && $level<SITEMAP_LEVEL) {
		  $sections_string .= '	<div class="level_' . ($level+1) . '">' . "\n" . tep_show_sections_map($menu_item['id'], $level+1) . '</div>' . "\n";
		}
	  }
	}
	if ($first_page_found) $sections_string .= '  </div><br />' . "\n";

	if ($level==0) {
	  $sections_string .= '  <a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH) . '" class="level_0">' . SITEMAP_SEARCH_LINK . '</a><br /><br />' . "\n" .
	  '</div>' . "\n";
	}

	return $sections_string;
  }

  function tep_show_categories_map($parent_id = '0', $level = 0, $products_types_id = '1') {
    global $languages_id;

	$categories_string = '';
	$product_type_info_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$product_type_info = tep_db_fetch_array($product_type_info_query);

	if ($level==0) $categories_string .= '<div id="sitemap_categories">' . "\n" .
	'  <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types_id) . '" class="level_0">' . $product_type_info['products_types_name'] . '</a><br />' . "\n" .
	'  <div class="level_0">' . "\n";

	$subcategories_check_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types_id . "' and categories_status = '1' and categories_listing_status = '1' and parent_id > '0'");
	$subcategories_check = tep_db_fetch_array($subcategories_check_query);

    $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and c.products_types_id = '" . (int)$products_types_id . "' and c.categories_status = '1' and c.categories_listing_status = '1' and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");
    while ($categories = tep_db_fetch_array($categories_query)) {
	  if ($products_types_id=='1') $category_link = tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories['categories_id']);
	  else $category_link = tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types_id . '&categories_id=' . $categories['categories_id']);
	  $next_level = tep_show_categories_map($categories['categories_id'], $level+1, $products_types_id);
      $categories_string .= '    <a href="' . $category_link . '" class="level_' . ($level+1) . '"' . ((empty($next_level) && $subcategories_check['total']==0) ? ' style="font-weight: normal;"' : '') . '>' . $categories['categories_name'] . '</a>' . "\n";
	  if ($level<SITEMAP_LEVEL) {
		if (tep_not_null($next_level)) $categories_string .= '    <div class="level_' . ($level+1) . '">' . "\n" . $next_level . '</div>' . "\n";
	  }
    }
	if ($level==0) $categories_string .= '  </div><br />' . "\n";

    return $categories_string;
  }

  function tep_show_specials_map() {
    global $languages_id, $active_specials_types_array;

	$specials_string = '';

	if (sizeof($active_specials_types_array) > 0) {
	  $specials_types_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in (" . implode(', ', $active_specials_types_array) . ") and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name");
	  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		$specials_string .= '  <a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types['specials_types_id']) . '" class="level_1">' . $specials_types['specials_types_name'] . '</a>' . "\n";
	  }
	}
	if (tep_not_null($specials_string)) {
	  $specials_string = '<div id="sitemap_specials">' . "\n" .
	  '  <a href="' . tep_href_link(FILENAME_SPECIALS) . '" class="level_0">' . SITEMAP_SPECIALS_LINK . '</a><br />' . "\n" .
	  '  <div class="level_1">' . "\n" .
	  $specials_string .
	  '  </div>' . "\n" .
	  '</div><br />' . "\n";
	}

    return $specials_string;
  }

  function tep_show_manufacturers_map() {
	$manufacturers_string = '<div id="sitemap_manufacturers">' . "\n" .
	'  <a href="' . tep_href_link(FILENAME_MANUFACTURERS) . '" class="level_0">' . SITEMAP_MANUFACTURERS_LINK . '</a><br />' . "\n";
/*	$manufacturers_string .= '  <div class="level_0">' . "\n";
	$manufacturers_array = tep_get_manufacturers();
	reset($manufacturers_array);
	while (list(, $manufacturer) = each($manufacturers_array)) {
	  $manufacturers_string .= '	<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $manufacturer['id']) . '" class="level_1">' . $manufacturer['text'] . '</a>' . "\n";
	}
	$manufacturers_string .= '  </div>' . "\n";*/
	$manufacturers_string .= '</div><br />' . "\n";

    return $manufacturers_string;
  }

  function tep_show_authors_map() {
	$authors_string = '<div id="sitemap_authors">' . "\n" .
	'  <a href="' . tep_href_link(FILENAME_AUTHORS) . '" class="level_0">' . SITEMAP_AUTHORS_LINK . '</a><br />' . "\n";
/*	$authors_string .= '  <div class="level_0">' . "\n";
	$authors_array = tep_get_authors();
	reset($authors_array);
	while (list(, $author) = each($authors_array)) {
	  $authors_string .= '	<a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $author['id']) . '" class="level_1">' . $author['text'] . '</a>' . "\n";
	}
	$authors_string .= '  </div>' . "\n";*/
	$authors_string .= '</div><br />' . "\n";

    return $authors_string;
  }

  function tep_show_series_map() {
	$series_string = '<div id="sitemap_series">' . "\n" .
	'  <a href="' . tep_href_link(FILENAME_SERIES) . '" class="level_0">' . SITEMAP_SERIES_LINK . '</a><br />' . "\n";
/*	$series_string .= '  <div class="level_0">' . "\n";
	$series_array = tep_get_series();
	reset($series_array);
	while (list(, $serie) = each($series_array)) {
	  $series_string .= '	<a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $serie['id']) . '" class="level_1">' . $serie['text'] . '</a>' . "\n";
	}
	$series_string .= '  </div>' . "\n";*/
	$series_string .= '</div><br />' . "\n";

    return $series_string;
  }

  function tep_show_account_map() {
	global $cart;

	$account_string = '<div id="sitemap_account">' . "\n";
	if (tep_session_is_registered('customer_id')) {
	  $account_string .= '  <a href="' . tep_href_link(FILENAME_ACCOUNT) . '" class="level_0">' . SITEMAP_ACCOUNT_LINK . '</a><br />' . "\n" .
	  '  <div class="level_0">' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY) . '" class="level_1">' . SITEMAP_ACCOUNT_HISTORY_LINK . '</a>' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT) . '" class="level_1">' . SITEMAP_ACCOUNT_EDIT_LINK . '</a>' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK) . '" class="level_1">' . SITEMAP_ADDRESS_BOOK_LINK . '</a>' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_ACCOUNT_PASSWORD) . '" class="level_1">' . SITEMAP_ACCOUNT_PASSWORD_LINK . '</a>' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS) . '" class="level_1">' . SITEMAP_ACCOUNT_NEWSLETTERS_LINK . '</a>' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_LOGOFF) . '" class="level_1">' . SITEMAP_LOGOFF_LINK . '</a>' . "\n" .
	  '  </div><br />' . "\n";
	} else {
	  $account_string .= '  <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT) . '" class="level_0">' . SITEMAP_CREATE_ACCOUNT_LINK . '</a><br /><br />' . "\n" .
	  '  <a href="' . tep_href_link(FILENAME_LOGIN) . '" class="level_0">' . SITEMAP_LOGIN_LINK . '</a>' . "\n" .
	  '  <div class="level_0">' . "\n" .
	  '	<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN) . '" class="level_1">' . SITEMAP_PASSWORD_FORGOTTEN_LINK . '</a>' . "\n" .
	  '  </div><br />' . "\n" .
	  '	 <a href="' . tep_href_link(FILENAME_ACCOUNT) . '" class="level_0">' . SITEMAP_ACCOUNT_LINK . '</a><br /><br />' . "\n\n";
	}

	if ($cart->count_contents() > 0) {
	  $account_string .= '  <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '" class="level_0">' . SITEMAP_CART_LINK . '</a><br /><br />' . "\n\n" .
	  '  <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING) . '" class="level_0">' . SITEMAP_ORDER_LINK . '</a><br /><br />' . "\n\n";
	}
	$account_string .= '</div>' . "\n";

	return $account_string;
  }

  $content = FILENAME_SITEMAP;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_SITEMAP));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>