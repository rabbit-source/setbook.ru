<?php
  $random_value = rand(1, 8);
  $random_products_array = array();

  if ($request_type=='NONSSL') {
	if (DEFAULT_LANGUAGE_ID=='1') {
	  $type_info_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by rand() limit 1");
	  $type_info = tep_db_fetch_array($type_info_query);
	  if (substr($type_info['specials_types_name'], -1)=='s') $type_info['specials_types_name'] = substr($type_info['specials_types_name'], 0, -1);
	  $boxHeading = '<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $type_info['specials_types_id']) . '">' . $type_info['specials_types_name'] . '</a>';
	  $random_products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$type_info['specials_types_id'] . "' and specials_first_page = '1' order by rand() limit 20");
	  while ($random_products = tep_db_fetch_array($random_products_query)) {
		$random_products_array[] = $random_products['products_id'];
	  }
	} else {
	  if ($random_value==0) {
		$boxHeading = 'В корзинах посетителей';
		$random_products_query = tep_db_query("select products_id, count(*) as total from " . TABLE_CUSTOMERS_BASKET . " where customers_basket_type = 'cart' and shops_id = '" . (int)SHOP_ID . "' group by products_id having total >= '5' order by total desc limit " . rand(0, 20) . ", 20");
		while ($random_products = tep_db_fetch_array($random_products_query)) {
		  $random_products_array[] = $random_products['products_id'];
		}
	  } elseif ($random_value>=1 && $random_value<=6) {
		$type_info_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by rand() limit 1");
		$type_info = tep_db_fetch_array($type_info_query);

		$boxHeading = '<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $type_info['specials_types_id']) . '">' . $type_info['specials_types_name'] . '</a>';
		$specials_cache_file = DIR_FS_CATALOG . 'cache/first_page/specials/' . $type_info['specials_types_id'] . '.html';
		if (file_exists($specials_cache_file) && filesize($specials_cache_file) > 0) {
		  if ($fp = fopen($specials_cache_file, 'r')) {
			while (!feof($fp)) {
			  $random_products_array[] = trim(fgets($fp, 16));
			}
			fclose($fp);
		  }
		}
		$random_products_array_size = sizeof($random_products_array);
		if ($random_products_array_size > 0) $random_products_array = array_rand(array_flip($random_products_array), ($random_products_array_size<20 ? $random_products_array_size : 20));
	  } elseif ($random_value==7) {
		$boxHeading = 'Пролистать книгу';
		$random_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_IMAGES . " where 1 order by rand() limit 20");
		while ($random_products = tep_db_fetch_array($random_products_query)) {
		  $random_products_array[] = $random_products['products_id'];
		}
	  } elseif ($random_value==8) {
		$boxHeading = 'Популярный товар';
		$random_products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_VIEWED . " where date_viewed >=  '" . date('Y-m-d', time()-60*60*24) . "' order by products_viewed desc limit 20");
		while ($random_products = tep_db_fetch_array($random_products_query)) {
		  $random_products_array[] = $random_products['products_id'];
		}
	  }
	}
	if (!is_array($random_products_array)) $random_products_array = array();

	if (sizeof($random_products_array) > 0) {
	  $product_info_query = tep_db_query("select products_id, authors_id, products_price, products_tax_class_id, products_image, products_status, products_listing_status, products_date_available from " . TABLE_PRODUCTS . " where products_id in ('" . implode("', '", $random_products_array) . "') and products_types_id = '1' order by rand() limit 1");
	  $product_info = tep_db_fetch_array($product_info_query);
	  if ($product_info['products_status']=='1') {
		if (!is_array($product_info)) $product_info = array();

		$product_name_info_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$product_name_info = tep_db_fetch_array($product_name_info_query);
		if (!is_array($product_name_info)) $product_name_info = array();

		$special_info_query = tep_db_query("select if((status and specials_new_products_price > 0), specials_new_products_price, null) as specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and specials_new_products_price > 0 order by specials_date_added desc limit 1");
		$special_info = tep_db_fetch_array($special_info_query);
		if (!is_array($special_info)) $special_info = array();

//		$author_name_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
//		$author_name_info = tep_db_fetch_array($author_name_info_query);
//		if (!is_array($author_name_info)) $author_name_info = array();
		$author_name_info = array();

		$product_info = array_merge($product_info, $special_info, $product_name_info, $author_name_info);
		$product_link = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id']);

		$product_info['products_short_name'] = $product_info['products_name'];
		if (mb_strlen($product_info['products_short_name'], 'CP1251') > 40) {
		  $product_info['products_short_name'] = mb_substr($product_info['products_short_name'], 0, 45, 'CP1251');
		  $products_short_name_parts = explode(' ', $product_info['products_short_name']);
		  unset($products_short_name_parts[sizeof($products_short_name_parts)-1]);
		  $product_info['products_short_name'] = trim(implode(' ', $products_short_name_parts));
		  $last_letter = mb_substr($product_info['products_short_name'], -1, mb_strlen($product_info['products_short_name'], 'CP1251'), 'CP1251');
		  if (!in_array($last_letter, array(':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) {
			$product_info['products_short_name'] .= ' ';
			$last_letter = ' ';
		  }
		  if (in_array($last_letter, array(' ', ':', ',', '.', '!', '?', '(', ')', '-', '*', '/'))) $product_info['products_short_name'] = mb_substr($product_info['products_short_name'], 0, -1, 'CP1251') . '...';
		}

		$boxContent = '';
		if (tep_not_null($product_info['products_image'])) {
//		  $product_image = '<div class="row_product_image"><a href="' . $product_link . '">' . tep_image(DIR_WS_IMAGES . 'thumbs/' . $product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div>';
		  $product_image = '<div class="row_product_image"><a href="' . $product_link . '">' . tep_image('http://149.126.96.163/thumbs/' . $product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div>';
		} else {
		  $product_image = '<div class="row_product_img"><a href="' . $product_link . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif', $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div>' . "\n";
		}

		$boxContent .= $product_image . '<div class="row_product_name"><a href="' . $product_link . '" title="' . $product_info['products_name'] . '">' . $product_info['products_short_name'] . '</a></div>' . "\n";
//		$boxContent .= ((int)$product_info['authors_id'] > 0 ? '<div class="row_product_author"><a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $product_info['authors_id']) . '">' . $product_info['authors_name'] . '</a></div>' . "\n" : '&nbsp;');

		$product_price = '';
		list($available_year, $available_month, $available_day) = explode('-', preg_replace('/^([^\s]+)\s/', '$1', $product_info['products_date_available']));
		if ($product_info['products_listing_status']=='0') {
		  $available_soon_check_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and specials_types_id = '4'");
		  $available_soon_check = tep_db_fetch_array($available_soon_check_query);
		  if ($product_info['products_date_available']>date('Y-m-d')) {
			$product_price = sprintf(TEXT_PRODUCT_NOT_AVAILABLE, $monthes_array[(int)$available_month] . ' ' . $available_year);
		  } elseif ($available_soon_check['total'] > 0) {
			$product_price = TEXT_PRODUCT_NOT_AVAILABLE_2;
		  } else {
//			$product_price = TEXT_PRODUCT_NOT_AVAILABLE_SHORT;
		  }
		} else {
		  if ($product_info['specials_new_products_price'] > 0 && $product_info['specials_new_products_price'] < $product_info['products_price']) $product_price = '<div class="row_product_price_old" style="display: inline; font-weight: normal; padding-right: 5px;">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '';
		  elseif ($customer_discount['type']=='purchase' && $product_info['products_purchase_cost'] > 0) $product_price = $currencies->display_price($product_info['products_purchase_cost'] * (1 + $customer_discount['value']/100), tep_get_tax_rate($product_info['products_tax_class_id']));
		  else $product_price = $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
		}

		if (tep_not_null($product_price)) $boxContent .= '<div class="row_product_price">' . $product_price . '</div>' . "\n";

		if (strpos(HTTP_SERVER, 'owl') || strpos(HTTP_SERVER, 'insell')) {
		  include(DIR_WS_TEMPLATES_BOXES . 'box2.php');
		} else {
		  include(DIR_WS_TEMPLATES_BOXES . 'box.php');
		}
	  }
	}
  }
?>