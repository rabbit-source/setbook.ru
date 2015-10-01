<?php
  echo $page['pages_description'];
?>
	<?php echo tep_draw_form('advanced_search', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onsubmit="return check_form(this);" class="form-div"'); ?>
	<fieldset>
	<legend><?php echo HEADING_SEARCH_CRITERIA; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td colspan="2"><?php echo tep_draw_input_field('keywords', '', 'size="93%"'); ?><br /><span class="smallText"><?php echo HEADING_SEARCH_CRITERIA_TEXT; ?></span></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" width="100%" id="advanced_search">
	  <tr>
		<td width="40%"><?php echo ENTRY_CATEGORY; ?></td>
		<td width="60%"><?php echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES))), '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_MANUFACTURER; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('manufacturers', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_SERIE; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('series', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_AUTHOR; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('authors', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo ENTRY_PRICE ; ?></td>
		<td><?php echo TEXT_FROM . ' ' . tep_draw_input_field('pfrom', '', 'size="5"') . ' &nbsp; ' . TEXT_TO . ' ' . tep_draw_input_field('pto', '', 'size="5"'). ' <span class="smallText">(' . ($languages_id!=DEFAULT_LANGUAGE_ID ? ENTRY_PRICE_CURRENCY . ' ' . $currency : ENTRY_PRICE_CURRENCY . ' ' . $currencies->currencies[$currency]['title']) . ')</span>'; ?></td>
	  </tr>
	  <tr>
		<td><?php echo ENTRY_YEAR; ?></td>
		<td><?php echo TEXT_FROM . ' ' . tep_draw_input_field('year_from', '', 'size="5" maxlength="4"') . ' &nbsp; ' . TEXT_TO . ' ' . tep_draw_input_field('year_to', '', 'size="5" maxlength="4"'); ?></td
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" width="100%" id="advanced_search">
	  <tr>
		<td width="40%"><?php echo TEXT_SORT_PRODUCTS . TEXT_BY; ?></td>
		<td width="60%"><?php
  $sort_by_array = array();
  $sort_by_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
  $sort_by_array[] = array('id' => '1a', 'text' => TABLE_HEADING_PRICE);
  $sort_by_array[] = array('id' => '2a', 'text' => TABLE_HEADING_YEAR);
  $sort_by_array[] = array('id' => '3a', 'text' => TABLE_HEADING_NAME);
  $sort_by_array[] = array('id' => '4a', 'text' => TABLE_HEADING_AUTHOR);
  echo tep_draw_pull_down_menu('sort', $sort_by_array);
?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_PER_PAGE; ?></td>
		<td><?php
  $per_page_array = array();
  $per_page_array[] = array('id' => '10', 'text' => '10');
  $per_page_array[] = array('id' => '25', 'text' => '25');
  $per_page_array[] = array('id' => '50', 'text' => '50');
  $per_page_array[] = array('id' => '100', 'text' => '100');
  echo tep_draw_pull_down_menu('per_page', $per_page_array);
?></td>
	  </tr>
	  <tr>
		<td colspan="2"><span class="errorText">* <span class="smallText"><?php echo TEXT_SEPARATED_BY_COMMAS; ?></span></span></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH); ?></div>
	</div>
	</form>

<?php
  echo '<p>' . TEXT_REQUEST_FOUND;
  echo tep_not_null($request_string) ? ' [' . $request_string . ']' : '';
  echo ($total_found > 0) ? ' ' . TEXT_FOUND : TEXT_NO_FOUND;
  echo '</p>' . "\n";

  $products_found = sizeof($products_to_search);
  $authors_found = sizeof($authors_to_search);
  $categories_found = sizeof($categories_to_search);
  $manufacturers_found = sizeof($manufacturers_to_search);
  $series_found = sizeof($series_to_search);
  $information_found = sizeof($pages_to_search) + sizeof($information_to_search);
  $news_found = sizeof($news_to_search);
  echo '<ul class="search_results">' . "\n";
  echo '<li id="show_list_1"' . (($products_found>0) ? ' class="show_list_active" onclick="showResultPage(1)"' : ' class="show_list_desactive"') . '>' . TEXT_PRODUCTS_FOUND . ' (' . $products_found . ')</li>' . "\n";
  echo '<li id="show_list_2"' . (($authors_found>0) ? ' class="show_list_inactive" onclick="showResultPage(2)"' : ' class="show_list_desactive"') . '>' . TEXT_AUTHORS_FOUND . ' (' . $authors_found . ')</li>' . "\n";
  echo '<li id="show_list_3"' . (($categories_found>0) ? ' class="show_list_inactive" onclick="showResultPage(3)"' : ' class="show_list_desactive"') . '>' . TEXT_CATEGORIES_FOUND . ' (' . $categories_found . ')</li>' . "\n";
  echo '<li id="show_list_4"' . (($manufacturers_found>0) ? ' class="show_list_inactive" onclick="showResultPage(4)"' : ' class="show_list_desactive"') . '>' . TEXT_MANUFACTURERS_FOUND . ' (' . $manufacturers_found . ')</li>' . "\n";
  echo '<li id="show_list_5"' . (($series_found>0) ? ' class="show_list_inactive" onclick="showResultPage(5)"' : ' class="show_list_desactive"') . '>' . TEXT_SERIES_FOUND . ' (' . $series_found . ')</li>' . "\n";
  echo '</ul>' . "\n";

  if ($products_found > 0) {
	echo '<div id="show_results_list_1" class="advanced-search" style="display: block;">' . "\n";
	$products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", array_keys($products_by_types_found)) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, products_types_name");
	while ($products_types = tep_db_fetch_array($products_types_query)) {
	  $products_found_array = $products_by_types_found[$products_types['products_types_id']];
//	  print_r($products_found_array);
	  echo '<p><a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('categories_id', 'inc_subcat', 'keywords', 'per_page')) . 'tPath=' . $products_types['products_types_id'] . (tep_not_null($keywords) ? '&detailed=' . $HTTP_GET_VARS['keywords'] : '')) . '"><strong>' . $products_types['products_types_name'] . '</strong></a> (' . sizeof($products_by_types_found[$products_types['products_types_id']]) . ')<br />' . "\n";
	  $categories_found_array = array();
	  $categories_found_query = tep_db_query("select categories_id, count(*) as total from " . TABLE_PRODUCTS_INFO . " where products_types_id = '" . (int)$products_types['products_types_id']. "' and products_id in ('" . implode("', '", $products_found_array) . "') group by categories_id");
	  while ($categories_found = tep_db_fetch_array($categories_found_query)) {
		$categories_found_array[$categories_found['categories_id']] = $categories_found['total'];
	  }
	  $j = 0;
	  $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = '1' and c.categories_id in ('" . implode("', '", array_keys($categories_found_array)) . "') and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
	  while ($categories = tep_db_fetch_array($categories_query)) {
		echo ($j>0 ? ', ' : '') . '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('categories_id', 'inc_subcat', 'keywords', 'per_page')) . 'cPath=' . $categories['categories_id'] . (tep_not_null($keywords) ? '&detailed=' . $HTTP_GET_VARS['keywords'] : '')) . '">' . $categories['categories_name'] . '</a> (' . $categories_found_array[$categories['categories_id']] . ')';
		$j ++;
	  }
	  echo '</p>' . "\n\n";
	}

	$original_show_product_type = $show_product_type;
	unset($show_product_type);
	include(DIR_WS_MODULES . 'product_listing.php');
	$show_product_type = $original_show_product_type;

	echo '</div>' . "\n";
  }

  if ($authors_found > 0) {
	echo '<ul id="show_results_list_2" class="advanced-search">' . "\n";
	$authors_query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " cd where authors_id in ('" . implode("', '", $authors_to_search) . "') and language_id = '" . (int)$languages_id . "' order by authors_name");
	while ($authors = tep_db_fetch_array($authors_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $authors['authors_id']) . '">' . $authors['authors_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }

  if ($categories_found > 0) {
	echo '<ul id="show_results_list_3" class="advanced-search">' . "\n";
	$categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '1' and c.categories_id = cd.categories_id and c.categories_status = '1' and c.categories_id in ('" . implode("', '", $categories_to_search) . "') and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by cd.categories_name");
	while ($categories = tep_db_fetch_array($categories_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories['categories_id']) . '">' . $categories['categories_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }

  if ($manufacturers_found > 0) {
	echo '<ul id="show_results_list_4" class="advanced-search">' . "\n";
	$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id in ('" . implode("', '", $manufacturers_to_search) . "') and languages_id = '" . (int)$languages_id . "' order by manufacturers_name");
	while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $manufacturers['manufacturers_id']) . '">' . $manufacturers['manufacturers_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }

  if ($series_found > 0) {
	echo '<ul id="show_results_list_5" class="advanced-search">' . "\n";
	$series_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where series_id in ('" . implode("', '", $series_to_search) . "') and language_id = '" . (int)$languages_id . "' order by series_name");
	while ($series = tep_db_fetch_array($series_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $series['series_id']) . '">' . $series['series_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }
/*
  if ($information_found > 0) {
	echo '<ul id="show_results_list_6" class="advanced-search">' . "\n";
	$pages_query = tep_db_query("select pages_id, pages_name, pages_filename from " . TABLE_PAGES . " where language_id = '" . (int)$languages_id . "' and pages_id in ('" . implode("', '", $pages_to_search) . "') order by pages_name");
	while ($pages = tep_db_fetch_array($pages_query)) {
	  echo '<li><a href="' . tep_href_link($pages['pages_filename']) . '">' . $pages['pages_name'] . '</a></li>' . "\n";
	}
	$information_query = tep_db_query("select i.information_id, i.information_name, i2s.sections_id from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "' and i.information_id in ('" . implode("', '", $information_to_search) . "') order by i.information_name");
	while ($information = tep_db_fetch_array($information_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_DEFAULT, 'sPath=' . $information['sections_id'] . '&info_id=' . $information['information_id']) . '">' . $information['information_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }

  if ($news_found > 0) {
	echo '<ul id="show_results_list_7" class="advanced-search">' . "\n";
	$news_query = tep_db_query("select date_added, news_id, news_name from " . TABLE_NEWS . " where news_status = '1' and language_id = '" . (int)$languages_id . "' and news_id in ('" . implode("', '", $news_to_search) . "') order by date_added desc, news_name");
	while ($news = tep_db_fetch_array($news_query)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_NEWS, 'news_id=' . $news['news_id']) . '">' . tep_date_short($news['date_added']) . ' ' . $news['news_name'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }
*/
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(array('sort', 'page')), 'NONSSL', true, false) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
