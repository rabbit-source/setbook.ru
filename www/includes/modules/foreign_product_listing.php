<?php
  $listing_sql_select = "select p.*";
  $listing_sql_from = " from " . TABLE_FOREIGN_PRODUCTS . " p";
  $listing_sql_where = " where 1";
  $listing_sql_group_by = "";

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

  $max_value = 0;
  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
	if ($value > 0) {
	  $column_list[] = $key;
	  if ($value > $max_value) $max_value = $value;
	}
  }

  $products_in_foreign_cart = array();
  $foreign_cart_products = $foreign_cart->get_product_id_list();
  if (tep_not_null($foreign_cart_products)) $products_in_foreign_cart = explode(', ', $foreign_cart_products);

  $select_column_list = '';

  if (isset($HTTP_GET_VARS['sort'])) $sort = $HTTP_GET_VARS['sort'];
  if (!tep_session_is_registered('sort')) tep_session_register('sort');

  if (is_numeric(substr($sort, 0, 1)) && substr($sort, 0, 1) <= sizeof($column_list)-1) {
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

  if (tep_not_null($HTTP_GET_VARS['author'])) {
    $author = stripslashes(trim(htmlspecialchars(strip_tags($HTTP_GET_VARS['author']), ENT_QUOTES)));
	$listing_sql_where .= " and p.products_author like '%" . str_replace(' ', "%' and p.products_author like '%", $author) . "%'";
  }

  if (isset($products_to_search)) {
	if (!is_array($products_to_search)) $products_to_search = array();
	$listing_sql_where .= " and p.products_id in ('" . implode("', '", array_map('tep_string_to_int', $products_to_search)) . "')";
  }

  $listing_sql = $listing_sql_select . $listing_sql_from . $listing_sql_where . "";

  $listing_sql = preg_replace('/\s*select\s+/i', 'select ' . $select_column_list, $listing_sql);

  if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) $sort_by = '';

  if (tep_not_null($sort_by)) {
	$listing_sql .= ' order by ' . $sort_by;
  } elseif (basename(PHP_SELF)!=FILENAME_SHOPPING_CART) {
	$listing_sql .= ' order by ';
	switch ($column_list[$sort_col-1]) {
	  case 'PRODUCT_LIST_MODEL':
		$listing_sql .= "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_NAME':
		$listing_sql .= "p.products_name " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_MANUFACTURER':
		$listing_sql .= "p.products_manufacturer " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_AUTHOR':
		$listing_sql .= "p.products_author " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_YEAR':
		$listing_sql .= "p.products_year " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_IMAGE':
		$listing_sql .= "p.products_image_exists " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_PRICE':
		$listing_sql .= "p.products_price " . ($sort_order == 'd' ? 'desc' : '') . ", p.sort_order";
		break;
	  case 'PRODUCT_LIST_SORT_ORDER':
	  case '':
		$listing_sql .= "p.sort_order " . ($sort_order == 'd' ? 'desc' : '');
		break;
	}
  }

  $listing_split = new splitPageResults($listing_sql, 10);

  $records_found = $listing_split->number_of_rows;
  if ($records_found > 0) {

// optional Authors List Filter
	$filterlist_authors_string = '';
    if (PRODUCT_LIST_FILTER > 0 && basename(SCRIPT_FILENAME)!=FILENAME_AUTHORS) {
	  $preload_authors = array();

	  if (tep_not_null(REQUEST_URI)) $authors_form_link = preg_replace('/author=[^\&]* /i', '', REQUEST_URI);
	  else $authors_form_link = basename(SCRIPT_FILENAME);
	  $temp_string = tep_draw_input_field('author', TEXT_INPUT_AUTHOR, 'size="15" class="' . ((tep_not_null($HTTP_GET_VARS['author']) && $HTTP_GET_VARS['author']!=TEXT_INPUT_AUTHOR) ? 'author_activated' : 'author_disabled') . '" onfocus="this.className=\'author_activated\'; if (this.value==\'' . TEXT_INPUT_AUTHOR . '\') this.value = \'\';" onblur="if (this.value==\'\') { this.value = \'' . TEXT_INPUT_AUTHOR . '\'; this.className=\'author_disabled\'; }"');

	  if (tep_not_null($temp_string)) {
		$filterlist_authors_string .= '<div id="AuthorsList">' . TEXT_CUSTOMIZE_AUTHOR . '<br />' . "\n" . tep_draw_form('authors', $authors_form_link, 'get', 'onsubmit="if (document.authors.author) { if (document.authors.author.value==\'' . TEXT_INPUT_AUTHOR . '\' || document.authors.author.value==\'\') { return false; } }"');
		reset($HTTP_GET_VARS);
		while (list($key, $value) = each($HTTP_GET_VARS)) {
		  if (tep_not_null($value) && $key!=tep_session_name() && $key!='page' && $key!='author') {
			$filterlist_authors_string .= tep_draw_hidden_field($key, tep_output_string_protected(urldecode($value)));
		  }
		}
        $filterlist_authors_string .= $temp_string . ' ' . tep_image_submit('button_quick_search.gif', IMAGE_BUTTON_QUICK_SEARCH) . '</form></div>' . "\n";
	  }
	}

	$listing_string = '	<div id="listing-split">' . "\n" .
	'	  <div style="float: left;">' . $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS) . '</div>' . "\n" .
	'	  <div style="text-align: right;">' . TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))) . '</div>' . "\n" .
	'	</div>' . "\n";

	if ( (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') && basename(PHP_SELF)!=FILENAME_SHOPPING_CART ) {
	  echo $listing_string;
	}

	$rows = 0;
	$list_box_contents = array();
	$sorting_text = '';

	for ($col=0, $n=sizeof($column_list)-1, $sorting_text=''; $col<$n; $col++) {
	  switch ($column_list[$col]) {
		case 'PRODUCT_LIST_MODEL':
		  $lc_text = TABLE_HEADING_MODEL;
		  break;
		case 'PRODUCT_LIST_NAME':
		  $lc_text = TABLE_HEADING_PRODUCTS;
		  break;
		case 'PRODUCT_LIST_MANUFACTURER':
		  $lc_text = TABLE_HEADING_MANUFACTURER;
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
	if (tep_not_null($sorting_text) && basename(PHP_SELF)!=FILENAME_SHOPPING_CART) echo '<div class="sortHeading">' . (tep_not_null($filterlist_authors_string) ? $filterlist_authors_string : '') . TEXT_SORT_PRODUCTS . TEXT_BY . '<br />' . "\n" . $sorting_text . ($sort_col>0 ? '<a href="#" onmouseover="this.href=\'' . tep_href_link(PHP_SELF, tep_get_all_get_params(array('page', 'info', 'sort')) . 'sort=') . '\';" title="' . TEXT_RESET_SORTING_TEXT . '">' . TEXT_RESET_SORTING . '</a>' : '') . '</div>' . "\n";

	$form_link = REQUEST_URI;
	if (strpos($form_link, 'action')) $form_link = preg_replace('/action=[^\&]*/i', 'action=[form_action]', $form_link);
	elseif (strpos($form_link, '?')!==FALSE) $form_link = $form_link . '&action=[form_action]';
	else $form_link = $form_link . '?action=[form_action]';
	while (strpos($form_link, '?&')) $form_link = str_replace('?&', '?', $form_link);
	while (strpos($form_link, '&&')) $form_link = str_replace('&&', '&', $form_link);

	$cur_row = 0;

	$listing = array();
    if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) $listing_query = tep_db_query($listing_sql);
	else $listing_query = tep_db_query($listing_split->sql_query);
	while ($product_info = tep_db_fetch_array($listing_query)) {
	  reset($product_info);
	  while (list($k, $v) = each($product_info)) {
		while (strpos($v, "\'")!==false) $v = str_replace("\'", "'", $v);
		while (strpos($v, '\"')!==false) $v = str_replace('\"', '"', $v);
		$product_info[$k] = $v;
	  }

	  $product_link = tep_href_link(FILENAME_FOREIGN, 'products_id=' . $product_info['products_id']);

	  $lc_align = 'center';
	  $lc_text = '';
	  $form_string = '';
	  $row_params = 'class="productListing-data-image"';
	  $product_image_link = '';
	  if (tep_not_null($product_info['products_image'])) {
		$product_image_link = DIR_WS_IMAGES . 'foreign/' . $product_info['products_image'];
	  } else {
		$product_image_link = DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif';
	  }
	  $lc_text = '<a href="' . $product_link . '">' . tep_image($product_image_link, $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';

	  $list_box_contents[$cur_row][] = array('align' => $lc_align,
											 'params' => $row_params,
											 'text'  => $lc_text);

	  $lc_text = '';
	  $lc_align = '';
	  $row_params = 'class="productListing-data-name"';

	  $lc_text .= '<div class="row_product_name"><a href="' . $product_link . '">' . $product_info['products_name'] . '</a>';
	  if (tep_not_null($product_info['products_description'])) {

		if (mb_strlen($product_info['products_description'], 'CP1251') > 100) {
		  $short_description = strrev(mb_substr($product_info['products_description'], 0, 120, 'CP1251'));
		  $short_description = mb_substr($short_description, strcspn($short_description, '":,.!?()'), mb_strlen($short_description, 'CP1251'), 'CP1251');
		  $short_description = trim(strrev($short_description));
		  if (in_array(mb_substr($short_description, -1, mb_strlen($short_description, 'CP1251'), 'CP1251'), array(':', '(', ')', ','))) $short_description = mb_substr($short_description, 0, -1, 'CP1251') . '...';
		} else {
		  $short_description = $product_info['products_description'];
		}

		$lc_text .= "\n" . '<div class="row_product_description">' . $short_description . '</div>' . "\n";
	  }
	  $lc_text .= '</div>' . "\n";

	  $temp_string = '';
	  if (tep_not_null($product_info['products_author'])) $temp_string .= (strpos($product_info['products_author'], ',') ? TEXT_AUTHORS : TEXT_AUTHOR) . ' ' . $product_info['products_author'];
	  if (tep_not_null($product_info['products_manufacturer'])) {
		$temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_MANUFACTURER . ' ' . $product_info['products_manufacturer'];
	  }
	  if ($product_info['products_date_available']>date('Y-m-d')) {
		$temp_string .= (tep_not_null($temp_string) ? '<br />' . "\n" : '') . sprintf(TEXT_PRODUCT_NOT_AVAILABLE, tep_date_long($product_info['products_date_available']) . TEXT_YEAR);
	  } elseif (tep_not_null($product_info['products_year'])) {
		$temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $product_info['products_year'] . TEXT_YEAR;
	  }
	  $lc_text .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";

	  $temp_string = '';
	  if (tep_not_null($product_info['products_available_in'])) {
		if (ALLOW_SHOW_AVAILABLE_IN=='true') $temp_string .= sprintf(TEXT_AVAILABLE_IN_FOREIGN, $product_info['products_available_in']) . ' ';
	  }
	  $lc_text .= '<div class="row_product_author"><strong>' . $temp_string . '</strong></div>' . "\n";

	  $lc_text .= '<div class="row_product_price">' . $currencies->format($product_info['products_price'], false, $product_info['products_currency']) . ($product_info['products_currency']!=$currency ? ' <span>(' . $currencies->display_price($product_info['products_price']/$currencies->currencies[$product_info['products_currency']]['value'], 0) . ')</span>' : '') . '</div>' . "\n";

	  $form_link_1 = str_replace('[form_action]', 'add_product&to=foreign', $form_link);
	  $form_link_2 = str_replace('[form_action]', 'buy_now&type=1&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id(), $form_link);

	  if (basename(PHP_SELF)==FILENAME_SHOPPING_CART) {
		$lc_text .= '<div class="row_product_buy"><a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&from=foreign&products_id=' . $product_info['products_id']) . '">' . tep_image_button('button_delete.gif', IMAGE_BUTTON_DELETE) . '</a></div>' . "\n";
	  } else {
		$form_string = tep_draw_form('p_form_' . $product_info['products_id'] . '_foreign', $form_link_1, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '&to=foreign\', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text_foreign; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

		$lc_text .= '<div class="row_product_buy" id="p_l_' . $product_info['products_id'] . '"">';
		if (in_array($product_info['products_id'], $products_in_foreign_cart)) {
		  $lc_text .= tep_image_button('button_in_order2.gif', IMAGE_BUTTON_IN_ORDER2);
		} else {
		  $lc_text .= $form_string . tep_image_submit('button_in_order.gif', IMAGE_BUTTON_IN_ORDER) . '<br /></form>' . "\n";
		}
		$lc_text .= '</div>' . "\n";
	  }

	  $list_box_contents[$cur_row][] = array('align' => $lc_align,
											 'params' => $row_params,
											 'text'  => $lc_text);

      $cur_row = sizeof($list_box_contents);
    }

	echo '<script language="javascript" type="text/javascript"><!--' . "\n" .
	' var new_text_foreign = \'' . tep_image_button('button_in_order2.gif', IMAGE_BUTTON_IN_ORDER2) . '\';' . "\n" .
	'//--></script>';

	$box = new tableBox(array());
	$box->table_width = '';
	$box->table_border = '0';
	$box->table_parameters = 'class="productListing"';
	$box->table_cellspacing = '0';
	$box->table_cellpadding = '0';
	echo $box->tableBox($list_box_contents);

	if ( (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') && basename(PHP_SELF)!=FILENAME_SHOPPING_CART ) {
	  echo $listing_string;
	}
  } else {
    echo '<p>' . TEXT_NO_PRODUCTS . '</p>';
  }
?>