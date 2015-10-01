<?php
  require('includes/application_top.php');

// calculate category path
  if (isset($HTTP_GET_VARS['cPath'])) {
    $cPath = $HTTP_GET_VARS['cPath'];
    $current_category_id = end(explode('_', $HTTP_GET_VARS['cPath']));
  } else {
    $cPath = '';
    $current_category_id = 0;
  }

  if (tep_not_null($cPath)) {
	$cPath_array = array($current_category_id);
	tep_get_parents($cPath_array, $current_category_id, TABLE_CATEGORIES);
	$cPath_array = array_reverse($cPath_array);
//    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  function tep_get_products_images_title($products_images_id, $language_id = '') {
	global $languages_id;

	if (empty($language_id)) $language_id = $languages_id;
	$title_query = tep_db_query("select products_images_name from " . TABLE_PRODUCTS_IMAGES . " where products_images_id = '" . (int)$products_images_id . "' and language_id = '" . (int)$language_id . "'");
	$title = tep_db_fetch_array($title_query);

	return $title['products_images_name'];
  }

  function tep_get_products_types_info($products_types_id, $language_id = '', $field = '') {
	global $languages_id;
	if (empty($language_id)) $language_id = $languages_id;
	if (empty($field)) $field = 'products_types_name';

	$type_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_PRODUCTS_TYPES  ." where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$language_id . "'");
	$type = tep_db_fetch_array($type_query);

	return $type[$field];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $tPath = (isset($HTTP_GET_VARS['tPath']) ? $HTTP_GET_VARS['tPath'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'insert_type':
	  case 'update_type':
		$products_types_id = tep_db_prepare_input($HTTP_POST_VARS['products_types_id']);
		if ($action == 'insert_type') {
		  $last_row_query = tep_db_query("select max(products_types_id) as last_id from " . TABLE_PRODUCTS_TYPES . "");
		  $last_row = tep_db_fetch_array($last_row_query);
		  $products_types_id = (int)$last_row['last_id'] + 1;
		}

		$products_types_name_array = $HTTP_POST_VARS['products_types_name'];
		$products_types_description_array = $HTTP_POST_VARS['products_types_description'];

		$languages = tep_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $sql_data_array = array('sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']),
								  'products_types_name' => tep_db_prepare_input($products_types_name_array[$language_id]),
								  'products_types_description' => tep_db_prepare_input($products_types_description_array[$language_id]));

		  if ($action == 'insert_type') {
			$insert_sql_data = array('date_added' => 'now()',
									 'products_types_id' => $products_types_id,
									 'language_id' => $language_id);
			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			$shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
			while ($shops = tep_db_fetch_array($shops_query)) {
			  tep_db_select_db($shops['shops_database']);
			  tep_db_perform(TABLE_PRODUCTS_TYPES, $sql_data_array);
			}
			tep_db_select_db(DB_DATABASE);
		  } elseif ($action == 'update_type') {
			$update_sql_data = array('last_modified' => 'now()', 'products_last_modified' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $update_sql_data);

			tep_db_perform(TABLE_PRODUCTS_TYPES, $sql_data_array, 'update', "products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		  }
		}
		$shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
		while ($shops = tep_db_fetch_array($shops_query)) {
		  tep_db_select_db($shops['shops_database']);
		  tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_types_path = '" . tep_db_prepare_input($HTTP_POST_VARS['products_types_path']) . "', products_types_letter_search = '" . (int)$HTTP_POST_VARS['products_types_letter_search'] . "', products_types_discounts = '" . (int)$HTTP_POST_VARS['products_types_discounts'] . "', products_types_free_shipping = '" . (int)$HTTP_POST_VARS['products_types_free_shipping'] . "' where products_types_id = '" . (int)$products_types_id . "'");
		}
		tep_db_select_db(DB_DATABASE);

		if ($HTTP_POST_VARS['products_types_default_status']=='1') {
		  tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_types_default_status = '0'");
		  tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_types_default_status = '1' where products_types_id = '" . (int)$products_types_id . "'");
		}

		tep_update_blocks($products_types_id, 'type');

		tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'tID=' . $products_types_id));
		break;
	  case 'delete_type_confirm':
		$products_types_id = tep_db_prepare_input($HTTP_POST_VARS['types_id']);

		tep_remove_product_type($products_types_id);

        if (isset($HTTP_POST_VARS['delete_products']) && ($HTTP_POST_VARS['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$products_types_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            tep_remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set products_types_id = '0' where products_types_id = '" . (int)$products_types_id . "'");
        }

        if (isset($HTTP_POST_VARS['delete_categories']) && ($HTTP_POST_VARS['delete_categories'] == 'on')) {
          $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types_id . "'");
          while ($categories = tep_db_fetch_array($categories_query)) {
            tep_remove_category($categories['categories_id']);
          }
        } else {
          tep_db_query("update " . TABLE_CATEGORIES . " set products_types_id = '0' where products_types_id = '" . (int)$products_types_id . "'");
        }

		tep_redirect(tep_href_link(FILENAME_CATEGORIES));
		break;
	  case 'list_products':
		header('Content-type: text/html; charset=' . CHARSET . '');
		echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
		if ((int)$HTTP_GET_VARS['categories_id'] > 0 || (int)$HTTP_GET_VARS['manufacturers_id'] > 0) {
		  $products_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
		  $products_query_row = "select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_types_id = '" . (int)$tPath . "' and p.products_id = pd.products_id and p.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "'";
		  if ((int)$HTTP_GET_VARS['categories_id'] > 0) {
			$subcategories = array($HTTP_GET_VARS['categories_id']);
			tep_get_subcategories($subcategories, $HTTP_GET_VARS['categories_id']);
			$products_query_row .= " and p2c.categories_id in ('" . implode("', '", $subcategories) . "')";
		  }
		  if ((int)$HTTP_GET_VARS['manufacturers_id'] > 0) $products_query_row .= " and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'";
		  $products_query_row .= " order by pd.products_name";
		  $products_query = tep_db_query($products_query_row);
		  while ($products = tep_db_fetch_array($products_query)) {
			$products_array[] = array('id' => $products['products_id'], 'text' => htmlspecialchars(str_replace("'", '#039;', $products['products_name'])));
		  }
		  echo tep_draw_pull_down_menu('products_tree', $products_array);
		}
		require('includes/application_bottom.php');
		die();
		break;
	  case 'add_linked':
		header('Content-type: text/html; charset=' . CHARSET . '');
		echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
		$added = $HTTP_GET_VARS['added'];
		$added_array = array_map('tep_string_to_int', explode(',', $added));
		$added_array[] = (int)$HTTP_GET_VARS['linked_id'];
		if ($action=='add_linked') {
		  if ((int)$HTTP_GET_VARS['categories_id'] > 0 && (int)$HTTP_GET_VARS['linked_id'] > 0) {
			tep_db_query("replace into " . TABLE_CATEGORIES_LINKED . " (categories_id, linked_id) values ('" . (int)$HTTP_GET_VARS['categories_id'] . "', '" . (int)$HTTP_GET_VARS['linked_id'] . "')");
			if ((int)$HTTP_GET_VARS['linked_type']=='2') {
			  tep_db_query("replace into " . TABLE_CATEGORIES_LINKED . " (categories_id, linked_id) values ('" . (int)$HTTP_GET_VARS['linked_id'] . "', '" . (int)$HTTP_GET_VARS['categories_id'] . "')");
			}
		  } elseif ((int)$HTTP_GET_VARS['products_id'] > 0 && (int)$HTTP_GET_VARS['linked_id'] > 0) {
			tep_db_query("replace into " . TABLE_PRODUCTS_LINKED . " (products_id, linked_id) values ('" . (int)$HTTP_GET_VARS['products_id'] . "', '" . (int)$HTTP_GET_VARS['linked_id'] . "')");
			if ((int)$HTTP_GET_VARS['linked_type']=='2') {
			  tep_db_query("replace into " . TABLE_PRODUCTS_LINKED . " (products_id, linked_id) values ('" . (int)$HTTP_GET_VARS['linked_id'] . "', '" . (int)$HTTP_GET_VARS['products_id'] . "')");
			}
		  }
		}
		echo '<font class="smallText">' . "\n";
		if (isset($HTTP_GET_VARS['categories_id'])) {
		  $linked_query = tep_db_query("select cl.categories_id, cd.categories_name, cl.linked_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES_LINKED . " cl where c.categories_id = cd.categories_id and c.categories_id = cl.linked_id and cd.language_id = '" . (int)$languages_id . "' and cl.categories_id = '" . (int)$HTTP_GET_VARS['categories_id'] . "' order by c.sort_order, cd.categories_name");
#		  $linked_query = tep_db_query("select cl.categories_id, cd.categories_name, cl.linked_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES_LINKED . " cl where c.categories_id = cd.categories_id and c.categories_id = cl.linked_id and cd.language_id = '" . (int)$languages_id . "' and cl.linked_id in ('" . implode("', '", $added_array) . "') order by c.sort_order, cd.categories_name");
		  $i = 0;
		  while ($linked = tep_db_fetch_array($linked_query)) {
			echo '<br />' . "\n" . tep_draw_checkbox_field('categories_linked[' . $i . ']', $linked['linked_id'], true) . htmlspecialchars($linked['categories_name'], ENT_QUOTES);
			$i ++;
		  }
		} elseif (isset($HTTP_GET_VARS['products_id'])) {
		  $linked_query = tep_db_query("select pl.products_id, pd.products_name, pl.linked_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_LINKED . " pl where p.products_id = pd.products_id and p.products_id = pl.linked_id and pd.language_id = '" . (int)$languages_id . "' and pl.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' order by pd.products_name");
		  while ($linked = tep_db_fetch_array($linked_query)) {
			echo '<br />' . "\n" . tep_draw_checkbox_field('products_linked[]', $linked['linked_id'], true) . str_replace("'", '#039;', htmlspecialchars($linked['products_name']));
		  }
		}
		echo '</font>';
		require('includes/application_bottom.php');
		die();
		break;
	  case 'add_linked_information':
		header('Content-type: text/html; charset=' . CHARSET . '');
		echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
		$added = $HTTP_GET_VARS['added'];
		$added_array = array_map('tep_string_to_int', explode(',', $added));
		$added_array[] = (int)$HTTP_GET_VARS['linked_id'];
		if ((int)$HTTP_GET_VARS['products_id'] > 0 && (int)$HTTP_GET_VARS['linked_id'] > 0) {
		  tep_db_query("replace into " . TABLE_PRODUCTS_TO_INFORMATION . " (products_id, information_id) values ('" . (int)$HTTP_GET_VARS['products_id'] . "', '" . (int)$HTTP_GET_VARS['linked_id'] . "')");
		}
		echo '<font class="smallText">' . "\n";
		if (isset($HTTP_GET_VARS['products_id'])) {
		  $linked_query = tep_db_query("select i.information_id, i.information_name from " . TABLE_INFORMATION . " i, " . TABLE_PRODUCTS_TO_INFORMATION . " p2i, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p2i.information_id = i.information_id and pd.products_id = p2i.products_id and pd.language_id = '" . (int)$languages_id . "' and i.language_id = '" . (int)$languages_id . "' and pd.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' order by i.information_name");
		  while ($linked = tep_db_fetch_array($linked_query)) {
			echo '<br />' . "\n" . tep_draw_checkbox_field('information_linked[]', $linked['information_id'], true) . str_replace("'", '#039;', htmlspecialchars($linked['information_name']));
		  }
		}
		echo '</font>';
		require('includes/application_bottom.php');
		die();
		break;
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  $new_status = (int)$HTTP_GET_VARS['flag'];
          if (isset($HTTP_GET_VARS['tID'])) {
			tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_types_status = '" . $new_status . "', last_modified = now(), products_last_modified = now() where products_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
          } elseif (isset($HTTP_GET_VARS['pID'])) {
			tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
			if ($new_status=='1') {
			  tep_db_query("delete from " . TABLE_PRODUCTS_TO_SHOPS . " where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and shops_id = '" . (int)SHOP_ID . "'");
			} else {
			  tep_db_query("replace into " . TABLE_PRODUCTS_TO_SHOPS . " (products_id, shops_id, products_status) values ('" . (int)$HTTP_GET_VARS['pID'] . "', '" . (int)SHOP_ID . "', '0')");
			}
          } elseif (isset($HTTP_GET_VARS['cID'])) {
			tep_db_query("update " . TABLE_CATEGORIES . " set categories_status = '" . $new_status . "', last_modified = now() where categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
			if ($HTTP_GET_VARS['other']=='1') {
			  $subcategories = array($HTTP_GET_VARS['cID']);
			  tep_get_subcategories($subcategories, $HTTP_GET_VARS['cID']);
			  tep_db_query("update " . TABLE_CATEGORIES . " set categories_status = '" . $new_status . "', last_modified = now() where categories_id in ('" . implode("', '", $subcategories) . "')");
			  $products_query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories) . "')");
			  while ($products = tep_db_fetch_array($products_query)) {
				tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$products['products_id'] . "'");
				if ($new_status=='1') {
//				  tep_db_query("delete from " . TABLE_PRODUCTS_TO_SHOPS . " where products_id = '" . (int)$products['products_id'] . "' and shops_id = '" . (int)SHOP_ID . "'");
				} else {
//				  tep_db_query("replace into " . TABLE_PRODUCTS_TO_SHOPS . " (products_id, shops_id, products_status) values ('" . (int)$products['products_id'] . "', '" . (int)SHOP_ID . "', '0')");
				}
			  }
			}
		  }
		} elseif ( ($HTTP_GET_VARS['listing_flag'] == '0') || ($HTTP_GET_VARS['listing_flag'] == '1') ) {
		  $new_status = (int)$HTTP_GET_VARS['listing_flag'];
          if (isset($HTTP_GET_VARS['pID'])) {
			tep_db_query("update " . TABLE_PRODUCTS . " set products_listing_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
          } elseif (isset($HTTP_GET_VARS['cID'])) {
			tep_db_query("update " . TABLE_CATEGORIES . " set categories_listing_status = '" . $new_status . "', last_modified = now() where categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
			if ($HTTP_GET_VARS['other']=='1') {
			  $subcategories = array($HTTP_GET_VARS['cID']);
			  tep_get_subcategories($subcategories, $HTTP_GET_VARS['cID']);
			  tep_db_query("update " . TABLE_CATEGORIES . " set categories_listing_status = '" . $new_status . "', last_modified = now() where categories_id in ('" . implode("', '", $subcategories) . "')");
			  $products_query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories) . "')");
			  while ($products = tep_db_fetch_array($products_query)) {
				tep_db_query("update " . TABLE_PRODUCTS . " set products_listing_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$products['products_id'] . "'");
			  }
			}
		  }
        } elseif ( ($HTTP_GET_VARS['xml_flag'] == '0') || ($HTTP_GET_VARS['xml_flag'] == '1') ) {
		  $new_status = (int)$HTTP_GET_VARS['xml_flag'];
          if (isset($HTTP_GET_VARS['pID'])) {
			tep_db_query("update " . TABLE_PRODUCTS . " set products_xml_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
          } elseif (isset($HTTP_GET_VARS['cID'])) {
			tep_db_query("update " . TABLE_CATEGORIES . " set categories_xml_status = '" . $new_status . "', last_modified = now() where categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
			if ($HTTP_GET_VARS['other']=='1' || !isset($HTTP_GET_VARS['other'])) {
			  $subcategories = array($HTTP_GET_VARS['cID']);
			  tep_get_subcategories($subcategories, $HTTP_GET_VARS['cID']);
			  tep_db_query("update " . TABLE_CATEGORIES . " set categories_xml_status = '" . $new_status . "', last_modified = now() where categories_id in ('" . implode("', '", $subcategories) . "')");
			  $products_query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories) . "')");
			  while ($products = tep_db_fetch_array($products_query)) {
				tep_db_query("update " . TABLE_PRODUCTS . " set products_xml_status = '" . $new_status . "', products_last_modified = now() where products_id = '" . (int)$products['products_id'] . "'");
			  }
			}
		  }
        }

		tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '" . (int)$tPath . "'");

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'listing_flag', 'xml_flag', 'cID', 'pID', 'other')) . (!empty($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '') . (!empty($HTTP_GET_VARS['cID']) ? '&cID=' . $HTTP_GET_VARS['cID'] : '')));
        break;
      case 'update_opt_confirm':
		$new_categories_id = $HTTP_POST_VARS['new_categories_id'];
		$new_manufacturers_id = $HTTP_POST_VARS['new_manufacturers_id'];
		$new_series_id = $HTTP_POST_VARS['new_series_id'];
		$new_types_id = $HTTP_POST_VARS['new_products_types_id'];
		$sort_order_array = $HTTP_POST_VARS['sort_order'];
		$update_array = $HTTP_POST_VARS['update'];
		$prices_array = $HTTP_POST_VARS['price'];
		$status_array = $HTTP_POST_VARS['status'];
		$listing_array = $HTTP_POST_VARS['listing_status'];
		$xml_array = $HTTP_POST_VARS['xml_status'];
		if (!is_array($sort_order_array)) $sort_order_array = array();
		if (!is_array($update_array)) $update_array = array();
		reset($sort_order_array);
		while (list($products_id, $sort_order) = each($sort_order_array)) {
		  $prices_array[$products_id] = str_replace(',', '.', $prices_array[$products_id]);
		  $sql_data_array = array('products_status' => (int)$status_array[$products_id],
								  'products_listing_status' => (int)$listing_array[$products_id],
								  'products_xml_status' => (int)$xml_array[$products_id],
								  'products_price' => tep_db_prepare_input($prices_array[$products_id]),
								  'sort_order' => (int)$sort_order,
								  'products_last_modified' => 'now()');
		  if (in_array($products_id, $update_array)) {
			if (tep_not_null($new_manufacturers_id)) {
			  $sql_data_array['manufacturers_id'] = (int)$new_manufacturers_id;
			}
			if (tep_not_null($new_series_id)) {
			  $sql_data_array['series_id'] = (int)$new_series_id;
			}
			if (tep_not_null($new_products_types_id)) {
			  $sql_data_array['products_types_id'] = (int)$new_products_types_id;
			}
			if (tep_not_null($new_categories_id)) {
			  tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "'");
			  tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$new_categories_id . "')");
			}
		  }
		  tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
        }

		tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '" . (int)$tPath . "'");

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'cID', 'pID')) . (!empty($HTTP_GET_VARS['cID']) ? 'cID=' . $HTTP_GET_VARS['cID'] : '')));
        break;
      case 'insert_category':
      case 'update_category':
        if (isset($HTTP_POST_VARS['categories_id'])) $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
        $categories_path = tep_db_prepare_input($HTTP_POST_VARS['categories_path']);
        $categories_status = tep_db_prepare_input($HTTP_POST_VARS['categories_status']);
        $categories_listing_status = tep_db_prepare_input($HTTP_POST_VARS['categories_listing_status']);
		$categories_path = preg_replace('/\_+/', '_', preg_replace('/\W/', '_', strtolower($categories_path)));
        $products_listing = tep_db_prepare_input($HTTP_POST_VARS['products_listing']);

		$disabled_path = array('admin', 'news', 'information', 'links', 'images', 'includes', 'download', 'pub', 'styles');
		$same_path_query = tep_db_query("select categories_path from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and categories_id <> '" . (int)$categories_id . "'");
		while ($same_path = tep_db_fetch_array($same_path_query)) {
		  $disabled_path[] = $same_path['categories_path'];
		}
		if (!tep_not_null($categories_path)) {
		  $messageStack->add(ERROR_CATEGORY_PATH_EMPTY, 'error');
		  $action = $action=='update_category' ? 'edit_category' : 'new_category';
		} elseif (in_array($categories_path, $disabled_path)) {
		  $messageStack->add(ERROR_CATEGORY_PATH_EXISTS, 'error');
		  $action = $action=='update_category' ? 'edit_category' : 'new_category';
		} else {
		  $sql_data_array = array('sort_order' => $sort_order,
								  'categories_status' => $categories_status,
								  'products_types_id' => $tPath,
								  'categories_listing_status' => $categories_listing_status,
								  'products_listing' => $products_listing,
								  'categories_path' => $categories_path);
		  if (tep_db_field_exists(TABLE_CATEGORIES, 'categories_code')) {
			$sql_data_array['categories_code'] = tep_db_prepare_input($HTTP_POST_VARS['categories_code']);
		  }

		  if ($action == 'insert_category') {
			$insert_sql_data = array('parent_id' => $current_category_id,
									 'date_added' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			$categories_id = '';
			$shops_query = tep_db_query("select shops_database, shops_default_status from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status desc");
			while ($shops = tep_db_fetch_array($shops_query)) {
			  tep_db_select_db($shops['shops_database']);
			  if ($shops['shops_default_status'] < 1) $sql_data_array['categories_id'] = $categories_id;
			  tep_db_perform(TABLE_CATEGORIES, $sql_data_array);
			  if ($shops['shops_default_status']=='1') $categories_id = tep_db_insert_id();
			}
			tep_db_select_db(DB_DATABASE);
		  } elseif ($action == 'update_category') {
			$update_sql_data = array('last_modified' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $update_sql_data);

			tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
		  }

		  $shops_query = tep_db_query("select shops_database, shops_fs_dir from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			tep_db_select_db($shops['shops_database']);
			tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . tep_db_input($sort_order) . "', products_listing = '" . tep_db_input($products_listing) . "', categories_path = '" . tep_db_input($categories_path) . "' where categories_id = '" . (int)$categories_id . "'");
		  }
		  tep_db_select_db(DB_DATABASE);

		  $languages = tep_get_languages();
		  $categories_name_array = $HTTP_POST_VARS['categories_name'];
		  $categories_description_array = $HTTP_POST_VARS['categories_description'];
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

			$description = str_replace('\\\"', '"', $categories_description_array[$language_id]);
			$description = str_replace('\"', '"', $description);
			$description = str_replace("\\\'", "\'", $description);
			$description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
			$description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
			$description = str_replace(' - ', ' &ndash; ', $description);

			$sql_data_array = array('categories_name' => tep_db_prepare_input($categories_name_array[$language_id]),
									'categories_description' => $description);

			if ($action == 'insert_category') {
			  $insert_sql_data = array('categories_id' => $categories_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
			} elseif ($action == 'update_category') {
			  tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  $categories_linked = $HTTP_POST_VARS['categories_linked'];
		  if (!is_array($categories_linked)) $categories_linked = array();
		  tep_db_query("delete from " . TABLE_CATEGORIES_LINKED . " where categories_id = '" . (int)$categories_id . "'");
		  while (list(, $linked_id) = each($categories_linked)) {
			if ( ((int)$linked_id > 0) && ($linked_id != $categories_id) ) {
			  tep_db_query("insert into " . TABLE_CATEGORIES_LINKED . " (categories_id, linked_id) values ('" . (int)$categories_id . "', '" . (int)$linked_id . "')");
			}
		  }

		  if ($HTTP_POST_VARS['categories_image_delete']=='1') {
			$prev_file_query = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['categories_image']) && $prev_file['categories_image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['categories_image']);
			}
			tep_db_query("update " . TABLE_CATEGORIES . " set categories_image = '' where categories_id = '" . (int)$categories_id . "'");
		  } else {
			$uploaded = false;
			if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
			  $size = @getimagesize($categories_image);
			  if ($size[2]=='3') $ext = '.png';
			  elseif ($size[2]=='2') $ext = '.jpg';
			  else $ext = '.gif';
			  $new_filename = $categories_id . $ext;
			  $upload->filename = 'categories/' . $new_filename;
			  if ($upload->upload('categories_image', DIR_FS_CATALOG_IMAGES)) {
				if (CATEGORY_IMAGE_WIDTH > 0 || CATEGORY_IMAGE_HEIGHT > 0) {
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', CATEGORY_IMAGE_WIDTH, CATEGORY_IMAGE_HEIGHT);
				}
				$prev_file_query = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
				$prev_file = tep_db_fetch_array($prev_file_query);
				if (tep_not_null($prev_file['categories_image']) && $prev_file['categories_image']!=$upload->filename) {
				  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['categories_image']);
				}
				tep_db_query("update " . TABLE_CATEGORIES . " set categories_image = '" . $upload->filename . "' where categories_id = '" . (int)$categories_id . "'");
			  }
			}
		  }

		  tep_update_blocks($categories_id, 'category');

		  tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '" . (int)$tPath . "'");

		  tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $categories_id));
		}
        break;
      case 'delete_category_confirm':
        if (isset($HTTP_POST_VARS['categories_id'])) {
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);

          $categories = array($categories_id);
		  tep_get_subcategories($categories, $categories_id);

          $products = array();
          $products_delete = array();

		  if ($HTTP_POST_VARS['delete_category_products']=='1') {
			for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
			  $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$categories[$i]['id'] . "'");

			  while ($product_ids = tep_db_fetch_array($product_ids_query)) {
				$products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
			  }
			}

			reset($products);
			while (list($key, $value) = each($products)) {
			  $category_ids = '';

			  for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
				$category_ids .= "'" . (int)$value['categories'][$i] . "', ";
			  }
			  $category_ids = substr($category_ids, 0, -2);

			  $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$key . "' and categories_id not in (" . $category_ids . ")");
			  $check = tep_db_fetch_array($check_query);
			  if ($check['total'] < '1') {
				$products_delete[$key] = $key;
			  }
			}
		  }

// removing categories can be a lengthy process
//		  reset($categories);
//		  while (list(, $category_id) = each($categories)) {
			tep_remove_category($categories, ($HTTP_POST_VARS['delete_category_products']=='1'));
//		  }

		  if ($HTTP_POST_VARS['delete_category_products']=='1') {
//			reset($products_delete);
//			while (list($key) = each($products_delete)) {
			  tep_remove_product($products_delete);
//			}
		  }
		}

		tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '" . (int)$tPath . "'");

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath));
        break;
      case 'delete_product_confirm':
        if (isset($HTTP_POST_VARS['products_id']) && isset($HTTP_POST_VARS['product_categories']) && is_array($HTTP_POST_VARS['product_categories'])) {
          $product_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
          $product_categories = $HTTP_POST_VARS['product_categories'];

          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "' and categories_id = '" . (int)$product_categories[$i] . "'");
          }

          $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
          $product_categories = tep_db_fetch_array($product_categories_query);

          if ($product_categories['total'] == '0') {
            tep_remove_product($product_id);
          }
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action'))));
        break;
      case 'move_category_confirm':
        if (isset($HTTP_POST_VARS['categories_id']) && ($HTTP_POST_VARS['categories_id'] != $HTTP_POST_VARS['move_to_category_id'])) {
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);
          $new_parent_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_category_id']);

          $path = explode('_', tep_get_generated_category_path_ids($new_parent_id));

          if (in_array($categories_id, $path)) {
            $messageStack->add_session(ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $categories_id));
          } else {
            tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where categories_id = '" . (int)$categories_id . "'");

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $new_parent_id . '&cID=' . $categories_id));
          }
        }

        break;
      case 'move_product_confirm':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $new_parent_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_category_id']);

        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$new_parent_id . "'");
        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
        if ($duplicate_check['total'] < 1) tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int)$new_parent_id . "' where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$current_category_id . "'");

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'cPath')) . 'cPath=' . $new_parent_id . '&pID=' . $products_id));
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($HTTP_POST_VARS['edit_x']) || isset($HTTP_POST_VARS['edit_y'])) {
          $action = 'new_product';
        } else {
		  $languages = tep_get_languages();
          if (isset($HTTP_GET_VARS['pID'])) $products_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
          $products_date_available = tep_db_prepare_input($HTTP_POST_VARS['products_date_available']);
		  $products_path = tep_db_prepare_input($HTTP_POST_VARS['products_path']);
		  $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
		  $products_path = preg_replace('/\_+/', '_', preg_replace('/\W/', '_', strtolower($products_path)));

		  $disabled_path = array();
		  $same_path_query = tep_db_query("select products_path from " . TABLE_PRODUCTS . " where products_id <> '" . (int)$products_id . "'");
		  while ($same_path = tep_db_fetch_array($same_path_query)) {
			$disabled_path[] = $same_path['products_path'];
		  }
		  if (!tep_not_null($products_path)) {
			$messageStack->add(ERROR_PRODUCT_PATH_EMPTY, 'error');
			$action = 'new_product';
		  } elseif (in_array($categories_path, $disabled_path)) {
			$messageStack->add(ERROR_PRODUCT_PATH_EXISTS, 'error');
			$action = 'new_product';
		  } else {
			$products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

			$sql_data_array = array('products_quantity' => tep_db_prepare_input($HTTP_POST_VARS['products_quantity']),
									'products_model' => tep_db_prepare_input($HTTP_POST_VARS['products_model']),
									'products_price' => tep_db_prepare_input($HTTP_POST_VARS['products_price']),
									'products_cost' => tep_db_prepare_input($HTTP_POST_VARS['products_cost']),
									'products_date_available' => $products_date_available,
									'products_weight' => tep_db_prepare_input($HTTP_POST_VARS['products_weight']),
									'products_path' => $products_path,
									'sort_order' => $sort_order,
									'products_status' => tep_db_prepare_input($HTTP_POST_VARS['products_status']),
									'products_listing_status' => tep_db_prepare_input($HTTP_POST_VARS['products_listing_status']),
									'products_xml_status' => tep_db_prepare_input($HTTP_POST_VARS['products_xml_status']),
									'products_tax_class_id' => tep_db_prepare_input($HTTP_POST_VARS['products_tax_class_id']),
									'products_types_id' => $tPath,
									'series_id' => tep_db_prepare_input($HTTP_POST_VARS['series_id']),
									'manufacturers_id' => tep_db_prepare_input($HTTP_POST_VARS['manufacturers_id']));
			if (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock')) {
			  $sql_data_array['products_stock'] = tep_db_prepare_input($HTTP_POST_VARS['products_stock']);
			}
			if (tep_db_field_exists(TABLE_PRODUCTS, 'products_tested_status')) {
			  $sql_data_array['products_tested_status'] = tep_db_prepare_input($HTTP_POST_VARS['products_tested_status']);
			}

			if (isset($HTTP_POST_VARS['products_image']) && tep_not_null($HTTP_POST_VARS['products_image']) && ($HTTP_POST_VARS['products_image'] != 'none')) {
			  $sql_data_array['products_image'] = tep_db_prepare_input($HTTP_POST_VARS['products_image']);
			}

			if ($action == 'insert_product') {
			  $insert_sql_data = array('products_date_added' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
			  $products_id = tep_db_insert_id();

			  tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
			} elseif ($action == 'update_product') {
			  $update_sql_data = array('products_last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
			}

			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			  $language_id = $languages[$i]['id'];
			  $description = $HTTP_POST_VARS['products_description'][$language_id];
			  $description = htmlspecialchars(strip_tags(stripslashes(trim($description))));

			  $sql_data_array = array('products_name' => tep_db_prepare_input($HTTP_POST_VARS['products_name'][$language_id]),
									  'products_description' => tep_db_input($description),
									  'products_url' => tep_db_prepare_input($HTTP_POST_VARS['products_url'][$language_id]),
									  'products_url_name' => tep_db_prepare_input($HTTP_POST_VARS['products_url_name'][$language_id]));

			  if (tep_db_field_exists(TABLE_PRODUCTS_DESCRIPTION, 'products_rating')) {
				$sql_data_array['products_rating'] = tep_db_prepare_input($HTTP_POST_VARS['products_rating'][$language_id]);
			  }

			  if (tep_db_field_exists(TABLE_PRODUCTS_DESCRIPTION, 'products_pack')) {
				$sql_data_array['products_pack'] = tep_db_prepare_input($HTTP_POST_VARS['products_pack'][$language_id]);
			  }

			  if ($action == 'insert_product') {
				$insert_sql_data = array('products_id' => $products_id,
										 'language_id' => $language_id);

				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

				tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
			  } elseif ($action == 'update_product') {
				tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
			  }
			}

			$prev_info_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			$prev_info = tep_db_fetch_array($prev_info_query);
			$prev_image = $prev_info['products_image'];
			$upload = new upload('', '', '777', array('gif', 'jpeg', 'jpg', 'png'));
			if (tep_not_null($prev_image) && file_exists(DIR_FS_CATALOG_IMAGES . $prev_image)) {
			  $upload->filename = $prev_image;
			} else {
			  $image_type = '';
			  $ext = '';
			  if (is_uploaded_file($products_image_big)) {
				list(, , $image_type) = @getimagesize($products_image_big);
			  } elseif (is_uploaded_file($products_image_middle)) {
				list(, , $image_type) = @getimagesize($products_image_middle);
			  } elseif (is_uploaded_file($products_image_small)) {
				list(, , $image_type) = @getimagesize($products_image_small);
			  }
			  if (tep_not_null($image_type)) {
				if ($image_type=='1') $ext = 'gif';
				elseif ($image_type=='2') $ext = 'jpg';
				elseif ($image_type=='3') $ext = 'png';
			  } else {
				$ext = 'jpg';
			  }
			  $upload->filename = 'thumbs/p' . $products_id . '.' . $ext;
			}
			$uploaded = false;
			if (is_uploaded_file($products_image_big)) {
			  if ($upload->upload('products_image_big', DIR_FS_CATALOG_IMAGES_BIG)) {
				$uploaded = true;
				tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename, '', BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT);
			  }
			} elseif ($HTTP_POST_VARS['delete_big']=='1') {
			  @unlink(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename);
			}
			if (is_uploaded_file($products_image_middle)) {
			  if ($upload->upload('products_image_middle', DIR_FS_CATALOG_IMAGES_MIDDLE)) {
				$uploaded = true;
				tep_create_thumb(DIR_FS_CATALOG_IMAGES_MIDDLE . $upload->filename, '', BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT);
			  }
			} elseif ($HTTP_POST_VARS['delete_middle']=='1') {
			  @unlink(DIR_FS_CATALOG_IMAGES_MIDDLE . $upload->filename);
			} elseif ($HTTP_POST_VARS['middle_from_big']=='1') {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename, DIR_FS_CATALOG_IMAGES_MIDDLE . $upload->filename, MIDDLE_IMAGE_WIDTH, MIDDLE_IMAGE_HEIGHT);
			}
			if (is_uploaded_file($products_image_small)) {
			  if ($upload->upload('products_image_small', DIR_FS_CATALOG_IMAGES)) {
				$uploaded = true;
				tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
			  }
			} elseif ($HTTP_POST_VARS['delete_small']=='1') {
			  @unlink(DIR_FS_CATALOG_IMAGES . $upload->filename);
			} elseif ($HTTP_POST_VARS['small_from_big']=='1') {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename, DIR_FS_CATALOG_IMAGES . $upload->filename, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
			} elseif ($HTTP_POST_VARS['small_from_middle']=='1') {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES_MIDDLE . $upload->filename, DIR_FS_CATALOG_IMAGES . $upload->filename, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
			}
			if ($uploaded) {
			  if (tep_not_null($prev_image) && $prev_image!=$upload->filename) {
				@unlink(DIR_FS_CATALOG_IMAGES_BIG . $prev_image);
				@unlink(DIR_FS_CATALOG_IMAGES_MIDDLE . $prev_image);
				@unlink(DIR_FS_CATALOG_IMAGES . $prev_image);
			  }
			  tep_db_query("update " . TABLE_PRODUCTS . " set products_image = '" . tep_db_input($upload->filename) . "' where products_id = '" . (int)$products_id . "'");
			}

			$products_images_ids = $HTTP_POST_VARS['products_images_id'];
			if (!is_array($products_images_ids)) $products_images_ids = array();
			reset($products_images_ids);
			while (list($j, $image_id) = each($products_images_ids)) {
			  $update_image_info = true;
			  if (empty($image_id) && is_uploaded_file($_FILES['products_images_' . $j]['tmp_name'])) {
				$new_id_query = tep_db_query("select max(products_images_id) as max_id from " . TABLE_PRODUCTS_IMAGES . "");
				$new_id = tep_db_fetch_array($new_id_query);
				$image_id = (int)$new_id['max_id'] + 1;
				$update_image_info = false;
			  }

			  $image_type = '';
			  $ext = '';
			  if (is_uploaded_file($_FILES['products_images_' . $j]['tmp_name'])) {
				list( , , $image_type) = getimagesize($_FILES['products_images_' . $j]['tmp_name']);
			  }
			  if (tep_not_null($image_type)) {
				if ($image_type=='1') $ext = 'gif';
				elseif ($image_type=='2') $ext = 'jpg';
				elseif ($image_type=='3') $ext = 'png';
			  } else {
				$ext = 'jpg';
			  }
			  $upload->filename = 'thumbs/a' . $products_id . '_' . $image_id . '.' . $ext;

			  if (is_uploaded_file($_FILES['products_images_' . $j]['tmp_name']) || tep_not_null($image_id)) {
				for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				  $language_id = $languages[$i]['id'];

				  $sql_data_array = array('products_images_id' => $image_id,
										  'products_id' => $products_id,
										  'products_images_name' => tep_db_prepare_input($HTTP_POST_VARS['products_images_title'][$j][$language_id]),
										  'language_id' => $language_id);
				  if (is_uploaded_file($_FILES['products_images_' . $j]['tmp_name'])) {
					$sql_data_array['products_images_image'] = $upload->filename;
				  }

				  if (tep_not_null($image_id) && $update_image_info==true) {
					tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_images_id = '" . (int)$image_id . "' and language_id = '" . (int)$language_id . "'");
				  } elseif (is_uploaded_file($_FILES['products_images_' . $j]['tmp_name'])) {
					tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
				  }
				}
			  }

			  if (is_uploaded_file($_FILES['products_images_' . $j]['tmp_name'])) {
				if ($upload->upload('products_images_' . $j, DIR_FS_CATALOG_IMAGES_BIG)) {
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename, '', BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT);
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename, DIR_FS_CATALOG_IMAGES . $upload->filename, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
				}
			  } elseif ($HTTP_POST_VARS['delete_additional_' . $j]=='1') {
				@unlink(DIR_FS_CATALOG_IMAGES_BIG . $upload->filename);
				@unlink(DIR_FS_CATALOG_IMAGES . $upload->filename);
				tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_images_id = '" . (int)$image_id . "' and products_id = '" . (int)$products_id . "'");
			  }
			}

			$products_linked = $HTTP_POST_VARS['products_linked'];
			if (!is_array($products_linked)) $products_linked = array();
			tep_db_query("delete from " . TABLE_PRODUCTS_LINKED . " where products_id = '" . (int)$products_id . "'");
			while (list(, $linked_id) = each($products_linked)) {
			  if ( ((int)$linked_id > 0) && ($linked_id != $products_id) ) {
				tep_db_query("insert into " . TABLE_PRODUCTS_LINKED . " (products_id, linked_id) values ('" . (int)$products_id . "', '" . (int)$linked_id . "')");
			  }
			}

			$information_linked = $HTTP_POST_VARS['information_linked'];
			if (!is_array($information_linked)) $information_linked = array();
			tep_db_query("delete from " . TABLE_PRODUCTS_TO_INFORMATION . " where products_id = '" . (int)$products_id . "'");
			while (list(, $linked_id) = each($information_linked)) {
			  if ((int)$linked_id > 0) {
				tep_db_query("insert into " . TABLE_PRODUCTS_TO_INFORMATION . " (products_id, information_id) values ('" . (int)$products_id . "', '" . (int)$linked_id . "')");
			  }
			}

			tep_update_blocks($products_id, 'product');

			$images_query = tep_db_query("select products_image, products_id from " . TABLE_PRODUCTS . " where products_image <> ''");
			while ($images = tep_db_fetch_array($images_query)) {
			  if (!file_exists(DIR_FS_CATALOG_IMAGES . $images['products_image'])) {
				if (file_exists(DIR_FS_CATALOG_IMAGES_BIG . $images['products_image'])) {
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES_BIG . $images['products_image'], DIR_FS_CATALOG_IMAGES . $images['products_image'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
				} elseif (file_exists(DIR_FS_CATALOG_IMAGES_MIDDLE . $images['products_image'])) {
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES_MIDDLE . $images['products_image'], DIR_FS_CATALOG_IMAGES . $images['products_image'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
				} else {
				  tep_db_query("update " . TABLE_PRODUCTS . " set products_image = '' where products_id = '" . (int)$images['products_id'] . "'");
				}
			  }
			}

			tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $products_id));
		  }
        }
        break;
      case 'copy_to_confirm':
        if (isset($HTTP_POST_VARS['products_id']) && isset($HTTP_POST_VARS['categories_id'])) {
          $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);

          if ($HTTP_POST_VARS['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$categories_id . "'");
              $check = tep_db_fetch_array($check_query);
              if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($HTTP_POST_VARS['copy_as'] == 'duplicate') {
            $product_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $product = tep_db_fetch_array($product_query);

            tep_db_query("insert into " . TABLE_PRODUCTS . " (products_quantity" . (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock') ? ", products_stock" : "") . ", products_model,products_image, products_price, products_date_added, products_date_available, products_weight, products_status, products_tax_class_id, manufacturers_id) values ('" . tep_db_input($product['products_quantity']) . "'" . (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock') ? ", '" . tep_db_input($product['products_stock']) . "'" : "") . ", '" . tep_db_input($product['products_model']) . "', '" . tep_db_input($product['products_image']) . "', '" . tep_db_input($product['products_price']) . "',  now(), '" . tep_db_input($product['products_date_available']) . "', '" . tep_db_input($product['products_weight']) . "', '0', '" . (int)$product['products_tax_class_id'] . "', '" . (int)$product['manufacturers_id'] . "')");
            $dup_products_id = tep_db_insert_id();

            $description_query = tep_db_query("select language_id, products_name, products_description, products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "'");
            while ($description = tep_db_fetch_array($description_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, products_viewed) values ('" . (int)$dup_products_id . "', '" . (int)$description['language_id'] . "', '" . tep_db_input($description['products_name']) . "', '" . tep_db_input($description['products_description']) . "', '" . tep_db_input($description['products_url']) . "', '0')");
            }

            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
            $products_id = $dup_products_id;
          }
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'cPath')) . 'cPath=' . $categories_id . '&pID=' . $products_id));
        break;
      case 'new_product_preview':
// copy image only if modified
        $products_image = new upload('products_image');
        $products_image->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image->parse() && $products_image->save()) {
          $products_image_name = $products_image->filename;
        } else {
          $products_image_name = (isset($HTTP_POST_VARS['products_previous_image']) ? $HTTP_POST_VARS['products_previous_image'] : '');
        }
        break;
      case 'delete_all_products_confirm':
		if (DEBUG_MODE=='on') {
		  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . "");
		  while ($products = tep_db_fetch_array($products_query)) {
			tep_remove_product($products['products_id']);
		  }
		}

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action'))));
        break;
    }
  }

// check if the catalog image directory exists
  if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!is_writeable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  $divs = array('main' => CATEGORY_MAIN,
				'parameters' => CATEGORY_PARAMETERS,
				'images' => CATEGORY_IMAGES);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
function changeSection(section) {
  sections = new Array(<?php $i = 0; while (list($div) = each($divs)) { echo ($i>0 ? ',' : '') . "'" . $div . "'"; $i ++; } ?>);
  for (i=0; i<sections.length; i++) {
	if (document.getElementById(sections[i])) {
	  document.getElementById(sections[i] + '_title').style.background = (sections[i]==section) ? '#F0F1F1' : '#FFFFFF';
	  document.getElementById(sections[i] + '_td').style.background = (sections[i]==section) ? '#F0F1F1' : '#000000';
	  document.getElementById(sections[i]).style.background = (sections[i]==section) ? '#F0F1F1' : '#000000';
	  document.getElementById(sections[i]).style.display = (sections[i]==section) ? '' : 'none';
	}
  }
}
//--></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
<?php
  if ($action == 'edit_type' || $action == 'new_type') {
    $parameters = array('products_types_name' => '',
						'products_types_description' => '',
						'products_types_id' => '',
						'date_added' => '',
						'last_modified' => '',
						'products_types_status' => '',
						'products_types_path' => '',
						'sort_order' => '',
						'products_types_letter_search' => '0');

    $tInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['tID']) && empty($HTTP_POST_VARS)) {
      $type_query = tep_db_query("select * from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "' and language_id = '" . (int)$languages_id . "'");
      $type = tep_db_fetch_array($type_query);

      $tInfo->objectInfo($type);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $tInfo->objectInfo($HTTP_POST_VARS);
      $products_types_name = $HTTP_POST_VARS['products_types_name'];
      $products_types_description = $HTTP_POST_VARS['products_types_description'];
    }

    $languages = tep_get_languages();

    if (!isset($tInfo->products_types_letter_search)) $tInfo->products_types_letter_search = '0';
    switch ($tInfo->products_types_letter_search) {
      case '1': $in_listing_status = true; $out_listing_status = false; break;
      case '0':
      default: $in_listing_status = false; $out_listing_status = true;
    }

    if (!isset($tInfo->products_types_discounts)) $tInfo->products_types_discounts = '0';
    switch ($tInfo->products_types_discounts) {
      case '1': $in_discounts_status = true; $out_discounts_status = false; break;
      case '0':
      default: $in_discounts_status = false; $out_discounts_status = true;
    }

    if (!isset($tInfo->products_types_free_shipping)) $tInfo->products_types_free_shipping = '0';
    switch ($tInfo->products_types_free_shipping) {
      case '1': $in_free_shipping_status = true; $out_free_shipping_status = false; break;
      case '0':
      default: $in_free_shipping_status = false; $out_free_shipping_status = true;
    }

    if (!isset($tInfo->products_types_default_status)) $tInfo->products_types_default_status = '0';
    switch ($tInfo->products_types_default_status) {
      case '1': $in_default_status = true; $out_default_status = false; break;
      case '0':
      default: $in_default_status = false; $out_default_status = true;
    }

    if (!isset($tInfo->products_types_status)) $tInfo->products_types_status = '1';
    switch ($tInfo->products_types_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

	$form_action = (isset($HTTP_GET_VARS['tID'])) ? 'update_type' : 'insert_type';
	echo tep_draw_form('new_type', FILENAME_CATEGORIES, tep_get_all_get_params(array('tID', 'action')) . (isset($HTTP_GET_VARS['tID']) ? 'tID=' . $HTTP_GET_VARS['tID'] . '&' : '') . 'action=' . $form_action) . tep_draw_hidden_field('products_types_id', $tInfo->products_types_id);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_type' ? sprintf(TEXT_EDIT_TYPE, $tInfo->products_types_name) : TEXT_NEW_TYPE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="100%">
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_TYPE_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_types_name[' . $languages[$i]['id'] . ']', (isset($products_types_name[$languages[$i]['id']]) ? $products_types_name[$languages[$i]['id']] : tep_get_products_types_info($tInfo->products_types_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_types_status', '1', $in_status) . ' ' . TEXT_TYPE_AVAILABLE; ?></td>
          </tr>
		</table>
<?php
	echo tep_load_blocks($tInfo->products_types_id, 'type');
?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_TYPE_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($products_types_description[$languages[$i]['id']]) ? $products_types_description[$languages[$i]['id']] : tep_get_products_types_info($tInfo->products_types_id, $languages[$i]['id'], 'products_types_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('products_types_description[' . $languages[$i]['id'] . ']');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_LETTER_SEARCH_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_types_letter_search', '1', $in_listing_status) . ' ' . TEXT_TYPE_LETTER_SEARCH; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_DISCOUNTS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_types_discounts', '1', $in_discounts_status) . ' ' . TEXT_TYPE_DISCOUNTS; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_FREE_SHIPPING_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_types_free_shipping', '1', $in_free_shipping_status) . ' ' . TEXT_TYPE_FREE_SHIPPING; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	if ($tInfo->products_types_default_status=='0') {
?>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_DEFAULT_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_types_default_status', '1', $in_default_status) . ' ' . TEXT_TYPE_DEFAULT; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TYPE_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . HTTP_SERVER . DIR_WS_CATALOG . tep_draw_input_field('products_types_path', $tInfo->products_types_path, 'size="' . (tep_not_null($tInfo->products_types_path) ? strlen($tInfo->products_types_path) - 1 : '7') . '"') . '/'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('date_added', (tep_not_null($tInfo->date_added) ? $tInfo->date_added : date('Y-m-d')));

	if (isset($HTTP_GET_VARS['tID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, (isset($HTTP_GET_VARS['tID']) ? 'tID=' . $HTTP_GET_VARS['tID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } elseif ($action == 'edit_category' || $action == 'new_category') {
    $parameters = array('categories_name' => '',
						'categories_description' => '',
						'categories_id' => '',
						'categories_image' => '',
						'date_added' => '',
						'last_modified' => '',
						'categories_status' => '',
						'categories_listing_status' => '',
						'categories_path' => '',
						'categories_image' => '',
						'products_listing' => '',
						'categories_linked' => array());

    $cInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['cID']) && empty($HTTP_POST_VARS)) {
      $category_query = tep_db_query("select c.*, cd.* from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);
	  $category['categories_linked'] = array();
	  $linked_query = tep_db_query("select linked_id from " . TABLE_CATEGORIES_LINKED . " where categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
	  while ($linked = tep_db_fetch_array($linked_query)) {
		$category['categories_linked'][] = $linked['linked_id'];
	  }

      $cInfo->objectInfo($category);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $cInfo->objectInfo($HTTP_POST_VARS);
      $categories_name = $HTTP_POST_VARS['categories_name'];
      $categories_description = $HTTP_POST_VARS['categories_description'];
    }

    $languages = tep_get_languages();

    if (!isset($cInfo->categories_status)) $cInfo->categories_status = '1';
    switch ($cInfo->categories_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

    if (!isset($cInfo->categories_listing_status)) $cInfo->categories_listing_status = '1';
    switch ($cInfo->categories_listing_status) {
      case '0': $in_listing_status = false; $out_listing_status = true; break;
      case '1':
      default: $in_listing_status = true; $out_listing_status = false;
    }

	$form_action = (isset($HTTP_GET_VARS['cID'])) ? 'update_category' : 'insert_category';
	echo tep_draw_form('new_category', FILENAME_CATEGORIES, tep_get_all_get_params(array('cID', 'action')) . (isset($HTTP_GET_VARS['cID']) ? 'cID=' . $HTTP_GET_VARS['cID'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('categories_id', $cInfo->categories_id);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_category' ? sprintf(TEXT_EDIT_CATEGORY, $cInfo->categories_name) : sprintf(TEXT_NEW_CATEGORY, tep_output_generated_category_path($current_category_id)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td class="main" width="250"><?php echo TEXT_CATEGORY_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('categories_status', '1', $in_status) . ' ' . TEXT_CATEGORY_AVAILABLE . '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('categories_listing_status', '1', $in_listing_status) . ' ' . TEXT_CATEGORY_LISTING_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_CATEGORY_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? $categories_name[$languages[$i]['id']] : tep_get_category_name($cInfo->categories_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_CATEGORY_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('categories_image') . (tep_not_null($cInfo->categories_image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $cInfo->categories_image . ' &nbsp; ' . tep_draw_checkbox_field('categories_image_delete', '1', false) . TEXT_IMAGE_DELETE . '</span>' : ''); ?></td>
          </tr>
<?php
	if (tep_db_field_exists(TABLE_CATEGORIES, 'categories_code')) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_CATEGORY_CODE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('categories_code', $cInfo->categories_code); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_CATEGORY_LISTING; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('products_listing', '1', $cInfo->products_listing==1) . ' ' . TEXT_CATEGORY_PRODUCTS_LISTING . '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('products_listing', '0', $cInfo->products_listing==0) . ' ' . TEXT_CATEGORY_SUBCATEGORIES_LISTING . '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('products_listing', '2', $cInfo->products_listing==2) . ' ' . TEXT_CATEGORY_SUBCATEGORIES_PRODUCTS_LISTING; ?></td>
          </tr>
		</table>
<?php
	echo tep_load_blocks($cInfo->categories_id, 'category');
?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_CATEGORY_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($categories_description[$languages[$i]['id']]) ? $categories_description[$languages[$i]['id']] : tep_get_category_description($cInfo->categories_id, $languages[$i]['id']));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('categories_description[' . $languages[$i]['id'] . ']');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr>
			<td valign="top" class="main"><?php echo TEXT_CATEGORIES_LINKED; ?></td>
			<td class="main" style="padding-left: 25px;"><?php
	if ((int)$cInfo->categories_id > 0) {
?>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="smallText"><?php echo TEXT_CHOOSE_CATEGORY . '<br>' . tep_draw_pull_down_menu('categories_tree', tep_get_category_tree()); ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td class="smallText"><?php echo '&nbsp;' . TEXT_LINKED_TYPE . '<br>' . tep_draw_radio_field('linked_type', '1', true) . TEXT_LINKED_ONE_WAY . ' ' . tep_draw_radio_field('linked_type', '2', false) . TEXT_LINKED_TWO_WAY; ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td class="smallText"><?php echo '<a href="#" onclick="for (i=0,add_str=\'\'; i<(document.new_category.elements[\'categories_linked[]\'] ? document.new_category.elements[\'categories_linked[]\'].length : 0); i++) { add_str += (add_str ? \',\' : \'\') + document.new_category.elements[\'categories_linked[]\'][i].value; } getXMLDOM(\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&categories_id=' . $cInfo->categories_id . '&action=add_linked') . '&linked_id=\' + document.new_category.categories_tree.options[document.new_category.categories_tree.selectedIndex].value + \'&linked_type=\' + (document.new_category.linked_type[1].checked ? 2 : 1) + \'&added=\' + add_str, \'linked_categories\'); document.new_category.categories_tree.selectedIndex = \'\'; document.new_category.linked_type[0].checked = true; document.new_category.linked_type[1].checked = false; return false;">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
			  </tr>
			</table>
<?php
	  $i = 0;
	  echo '<div id="linked_categories" class="smallText">' . "\n";
	  $linked_query = tep_db_query("select cl.categories_id, cd.categories_name, cl.linked_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES_LINKED . " cl where c.categories_id = cd.categories_id and c.categories_id = cl.linked_id and cd.language_id = '" . (int)$languages_id . "' and cl.categories_id = '" . (int)$cInfo->categories_id . "' order by c.sort_order, cd.categories_name");
	  while ($linked = tep_db_fetch_array($linked_query)) {
		echo '<br />' . "\n" . tep_draw_checkbox_field('categories_linked[]', $linked['linked_id'], in_array($linked['linked_id'], $cInfo->categories_linked)) . str_replace("'", '#039;', htmlspecialchars($linked['categories_name']));
		$i ++;
	  }
	  echo '</div>' . "\n";
	} else {
	  echo TEXT_CATEGORY_WARNING_LINKED;
	}
?></td>
		  </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_CATEGORY_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_CATEGORIES, 'cPath=' . $current_category_id) . tep_draw_input_field('categories_path', $cInfo->categories_path, 'size="' . (tep_not_null($cInfo->categories_path) ? strlen($cInfo->categories_path) - 1 : '7') . '"') . '/'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $cInfo->sort_order, 'size="3"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('date_added', (tep_not_null($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d')));

	if (isset($HTTP_GET_VARS['cID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . (isset($HTTP_GET_VARS['cID']) ? '&cID=' . $HTTP_GET_VARS['cID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } elseif ($action == 'new_product' || $action == 'edit_product') {
    $parameters = array('products_name' => '',
                       'products_description' => '',
                       'products_rating' => '',
					   'products_pack' => '',
					   'products_images' => '',
                       'products_url' => '',
                       'products_url_name' => '',
                       'products_id' => '',
                       'products_quantity' => '',
                       'products_stock' => '',
                       'products_model' => '',
                       'products_image' => '',
                       'products_price' => '',
                       'products_weight' => '',
					   'sort_order' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
                       'products_date_available' => '',
                       'products_status' => '',
                       'products_listing_status' => '',
                       'products_xml_status' => '',
                       'products_tested_status' => '',
                       'products_tax_class_id' => '',
					   'products_path' => '',
                       'products_types_id' => '',
                       'series_id' => '',
                       'manufacturers_id' => '');

    $pInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['pID']) && empty($HTTP_POST_VARS)) {
      $product_query = tep_db_query("select p.*, pd.*, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");
      $product = tep_db_fetch_array($product_query);

	  $product['products_images'] = array();
	  $images_query = tep_db_query("select products_images_id as id, products_images_name as title, products_images_image as image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and language_id = '" . (int)$languages_id . "'");
	  while ($images = tep_db_fetch_array($images_query)) {
		$product['products_images'][] = array('id' => $images['id'],
											  'title' => $images['title'],
											  'image' => $images['image']);
	  }

      $pInfo->objectInfo($product);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $pInfo->objectInfo($HTTP_POST_VARS);
      $products_name = $HTTP_POST_VARS['products_name'];
      $products_description = $HTTP_POST_VARS['products_description'];
      $products_url = $HTTP_POST_VARS['products_url'];
    }

    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);
    }

    $products_types_array = array(array('id' => '', 'text' => TEXT_NONE));
    $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
    while ($products_types = tep_db_fetch_array($products_types_query)) {
      $products_types_array[] = array('id' => $products_types['products_types_id'],
                                     'text' => $products_types['products_types_name']);
    }

    $series_array = array(array('id' => '', 'text' => TEXT_NONE));
    $series_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where language_id = '" . (int)$languages_id . "' order by sort_order, series_name");
    while ($series = tep_db_fetch_array($series_query)) {
      $series_array[] = array('id' => $series['series_id'],
                                     'text' => $series['series_name']);
    }

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }

    $languages = tep_get_languages();

    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
    switch ($pInfo->products_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

    if (!isset($pInfo->products_listing_status)) $pInfo->products_listing_status = '1';
    switch ($pInfo->products_listing_status) {
      case '0': $in_listing_status = false; $out_listing_status = true; break;
      case '1':
      default: $in_listing_status = true; $out_listing_status = false;
    }

    if (!isset($pInfo->products_xml_status)) $pInfo->products_xml_status = '1';
    switch ($pInfo->products_xml_status) {
      case '0': $in_xml_status = false; $out_xml_status = true; break;
      case '1':
      default: $in_xml_status = true; $out_xml_status = false;
    }
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript"><!--
  var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $pInfo->products_date_available; ?>",scBTNMODE_CUSTOMBLUE);
//--></script>
<script language="javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;

  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;

  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>
<?php
	$form_action = (isset($HTTP_GET_VARS['pID'])) ? 'update_product' : 'insert_product';
	echo tep_draw_form('new_product', FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . (isset($HTTP_GET_VARS['pID']) ? 'pID=' . $HTTP_GET_VARS['pID'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_product' ? sprintf(TEXT_EDIT_PRODUCT, $pInfo->products_name) : sprintf(TEXT_NEW_PRODUCT, tep_output_generated_category_path($current_category_id)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
<?php
	$i = 0;
	reset($divs);
	while (list($div_name, $div_title) = each($divs)) {
	  echo ($i==0 ? '			<td width="1" rowspan="3" bgcolor="black">' . tep_draw_separator('pixel_trans.gif', '1', '1') . '</td>' . "\n" : '') .
	  '			<td bgcolor="black">' . tep_draw_separator('pixel_trans.gif') . '</td>' . "\n" .
	  '			<td width="1" rowspan="3" bgcolor="black">' . tep_draw_separator('pixel_trans.gif', '1', '1') . '</td>' . "\n";
	  $i ++;
	}
?>
		  </tr>
		  <tr align="center">
<?php
	$i = 0;
	reset($divs);
	while (list($div_name, $div_title) = each($divs)) {
	  echo '			<td style="padding: 10px; background: #' . ($i==0 ? 'F0F1F1' : 'FFFFFF') . ';" id="' . $div_name . '_title" class="main"><u onClick="changeSection(\'' . $div_name . '\');" style="cursor: pointer;">' . $div_title . '</u></td>' . "\n";
	  $i ++;
	}
?>
		  </tr>
		  <tr>
<?php
	$i = 0;
	reset($divs);
	while (list($div_name, $div_title) = each($divs)) {
	  echo '			<td style="background: #' . ($i==0 ? 'F0F1F1' : '000000') . ';" id="' . $div_name . '_td">' . tep_draw_separator('pixel_trans.gif') . '</td>' . "\n";
	  $i ++;
	}
?>
		  </tr>
		</table>
		<div id="main" style="display: ''; background: F0F1F1; padding: 10px; border: 1px solid black; border-top: none;"><table border="0" cellspacing="0" cellpadding="1" width="98%">
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? $products_name[$languages[$i]['id']] : tep_get_products_name($pInfo->products_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_DESCRIPTION . '<br /><span class="smallText"><strong>' . TEXT_NO_HTML . '<br />' . TEXT_MAX_255 . '</strong></span>'; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0" width="100%">
              <tr>
                <td class="main" width="15" valign="top"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '55', '3', (isset($products_description[$languages[$i]['id']]) ? $products_description[$languages[$i]['id']] : str_replace('&amp;', '&', tep_get_products_description($pInfo->products_id, $languages[$i]['id'])))) ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	if (tep_db_field_exists(TABLE_PRODUCTS_DESCRIPTION, 'products_rating')) {
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_RATING; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_rating[' . $languages[$i]['id'] . ']', (isset($products_rating[$languages[$i]['id']]) ? $products_rating[$languages[$i]['id']] : tep_get_products_url($pInfo->products_id, $languages[$i]['id'], 'products_rating')), 'size="40"'); ?></td>
          </tr>
<?php
	  }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	}
	if (tep_db_field_exists(TABLE_PRODUCTS_DESCRIPTION, 'products_pack')) {
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_PACK; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_pack[' . $languages[$i]['id'] . ']', (isset($products_pack[$languages[$i]['id']]) ? $products_pack[$languages[$i]['id']] : tep_get_products_url($pInfo->products_id, $languages[$i]['id'], 'products_pack')), 'size="40"'); ?></td>
          </tr>
<?php
	  }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_model', $pInfo->products_model); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $pInfo->sort_order, 'size="4"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_CATEGORIES, 'cPath=' . $current_category_id) . tep_draw_input_field('products_path', $pInfo->products_path, 'size="' . (tep_not_null($pInfo->products_path) ? strlen($pInfo->products_path) : '7') . '" onFocus="if (this.value==\'\') this.value = products_model.value.toLowerCase().replace(/\W/i, \'_\').replace(/\_+/, \'_\');"') . '.html'; ?></td>
          </tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" width="98%">
		  <tr>
			<td><?php echo tep_load_blocks($pInfo->products_id, 'product'); ?></td>
		  </tr>
		</table></div>
		<div id="parameters" style="display: 'none'; padding: 10px; border: 1px solid black; border-top: none;"><table border="0" cellspacing="0" cellpadding="2" width="98%">
          <tr>
            <td class="main" width="250"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_status', '1', $in_status) . ' ' . TEXT_PRODUCT_STATUS_1 . '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_listing_status', '1', $in_listing_status) . ' ' . TEXT_PRODUCT_LISTING_STATUS_1 . '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_xml_status', '1', $in_xml_status) . ' ' . TEXT_PRODUCT_XML_STATUS_1 . (tep_db_field_exists(TABLE_PRODUCTS, 'products_tested_status') ? '<br>' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('products_tested_status', '1', $pInfo->products_tested_status) . ' ' . TEXT_PRODUCT_TESTED_STATUS : ''); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?><br><small>(YYYY-MM-DD)</small></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;'; ?><script language="javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_TYPE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('products_types_id', $products_types_array, $pInfo->products_types_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_SERIE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('series_id', $series_array, $pInfo->series_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_price', $pInfo->products_price, 'onKeyUp="updateGross()"'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_price_gross', $pInfo->products_price, 'OnKeyUp="updateNet()"'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_COST; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_cost', $pInfo->products_cost); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<script language="javascript"><!--
updateGross();
//--></script>
          <tr>
            <td class="main" width="250"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_quantity', $pInfo->products_quantity); ?></td>
          </tr>
<?php
	if (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock')) {
?>
          <tr>
            <td class="main" width="250"><?php echo TEXT_PRODUCTS_STOCK; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_stock', $pInfo->products_stock); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('products_weight', $pInfo->products_weight); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		</table></div>
		<div id="images" style="display: 'none'; padding: 10px; border: 1px solid black; border-top: none;"><table border="0" cellspacing="0" cellpadding="1" width="98%">
		  <tr valign="top">
			<td class="main" width="250"><?php echo TEXT_BIG_IMAGE; ?></td>
            <td class="smallText" style="padding-left: 22px;"><?php echo tep_draw_file_field('products_image_big') .
	((tep_not_null($pInfo->products_image) && file_exists(DIR_FS_CATALOG_IMAGES_BIG . $pInfo->products_image)) ? '<br /><u style="cursor: pointer;" onMouseOver="document.getElementById(\'big_image\').style.display = \'\';" onMouseOut="document.getElementById(\'big_image\').style.display = \'none\';">' . $pInfo->products_image . '</u> &nbsp; ' . tep_draw_checkbox_field('delete_big', '1', false) . TEXT_DELETE_IMAGE . '<div id="big_image" style="display: none; position: absolute;">' . tep_image(DIR_WS_CATALOG_IMAGES_BIG . $pInfo->products_image, '') . '</div>' : ''); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo TEXT_MIDDLE_IMAGE; ?></td>
            <td class="smallText" style="padding-left: 22px;"><?php echo tep_draw_file_field('products_image_middle') . ' &nbsp; ' . tep_draw_checkbox_field('middle_from_big', '1', false) . TEXT_CREATE_FROM_BIG_IMAGE .
	((tep_not_null($pInfo->products_image) && file_exists(DIR_FS_CATALOG_IMAGES_MIDDLE . $pInfo->products_image)) ? '<br /><u style="cursor: pointer;" onMouseOver="document.getElementById(\'middle_image\').style.display = \'\';" onMouseOut="document.getElementById(\'middle_image\').style.display = \'none\';">' . $pInfo->products_image . '</u> &nbsp; ' . tep_draw_checkbox_field('delete_middle', '1', false) . TEXT_DELETE_IMAGE . '<div id="middle_image" style="display: none; position: absolute;">' . tep_image(DIR_WS_CATALOG_IMAGES_MIDDLE . $pInfo->products_image, '') . '</div>' : ''); ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo TEXT_SMALL_IMAGE; ?></td>
            <td class="smallText" style="padding-left: 22px;"><?php echo tep_draw_file_field('products_image_small') . ' &nbsp; ' . tep_draw_checkbox_field('small_from_big', '1', false, '', 'onClick="if (this.checked && small_from_middle.checked) small_from_middle.checked = false;"') . TEXT_CREATE_FROM_BIG_IMAGE . ' &nbsp; ' . tep_draw_checkbox_field('small_from_middle', '1', false, '', 'onClick="if (this.checked && small_from_big.checked) small_from_big.checked = false;"') . TEXT_CREATE_FROM_MIDDLE_IMAGE .
	((tep_not_null($pInfo->products_image) && file_exists(DIR_FS_CATALOG_IMAGES . $pInfo->products_image)) ? '<br /><u style="cursor: pointer;" onMouseOver="document.getElementById(\'small_image\').style.display = \'\';" onMouseOut="document.getElementById(\'small_image\').style.display = \'none\';">' . $pInfo->products_image . '</u> &nbsp; ' . tep_draw_checkbox_field('delete_small', '1', false) . TEXT_DELETE_IMAGE . '<div id="small_image" style="display: none; position: absolute;">' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image, '') . '</div>' : ''); ?></td>
		  </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr valign="top">
			<td class="main"><?php echo TEXT_ADDITIONAL_IMAGES; ?></td>
            <td class="smallText" style="padding-left: 22px;"><table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="main"><?php echo TEXT_IMAGE_FILE; ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td class="main"><?php echo TEXT_IMAGE_TITLE; ?></td>
			  </tr><?php
	for ($j=0; $j<10; $j++) {
	  if (isset($pInfo->products_images[$j])) $image = $pInfo->products_images[$j];
	  else $image = array();
?>
			  <tr>
				<td colspan="3"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
			  </tr>
			  <tr valign="top">
				<td class="smallText"><?php
	  echo tep_draw_file_field('products_images_' . $j);
	  if (tep_not_null($image['image']) && file_exists(DIR_FS_CATALOG_IMAGES . $image['image'])) {
		echo '<br />' . "\n" . '<a href="" onMouseOver="document.getElementById(\'p_image_' . $j . '\').style.display = \'\';" onMouseOut="document.getElementById(\'p_image_' . $j . '\').style.display = \'none\';" onClick="return false;"><u>' . $image['image'] . '</u></a> &nbsp; ' . tep_draw_checkbox_field('delete_additional_' . $j, '1', false) . TEXT_DELETE_IMAGE . '<div id="p_image_' . $j . '" style="display: none; position: absolute;">' . tep_image(DIR_WS_CATALOG_IMAGES . $image['image'], '') . '</div>';
	  }
	  echo tep_draw_hidden_field('products_images_id[' . $j . ']', $image['id']);
?></td>
				<td>&nbsp;</td>
				<td class="main"><?php
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_images_title[' . $j . '][' . $languages[$i]['id'] . ']', (isset($products_images_title[$j][$languages[$i]['id']]) ? $products_images_title[$j][$languages[$i]['id']] : tep_get_products_images_title($image['id'], $languages[$i]['id'])), 'size="40"') . '<br />';
	  }
?></td>
			  </tr>
<?php
	}
?></table></td>
		  </tr><!--
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_URLS; ?></td>
            <td class="main"><table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="main"><?php echo TEXT_PRODUCTS_URL . ' <small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
				<td class="main"><?php echo TEXT_PRODUCTS_URL_TITLE; ?></td>
			  </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
			  <tr>
				<td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? $products_url[$languages[$i]['id']] : tep_get_products_url($pInfo->products_id, $languages[$i]['id'])), 'size="38"'); ?>&nbsp;</td>
				<td class="main"><?php echo tep_draw_input_field('products_url_name[' . $languages[$i]['id'] . ']', (isset($products_url_name[$languages[$i]['id']]) ? $products_url_name[$languages[$i]['id']] : tep_get_products_url($pInfo->products_id, $languages[$i]['id'], 'products_url_name')), 'size="38"'); ?></td>
			  </tr>
<?php
    }
?>
			</table></td>
          </tr>//-->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr>
			<td valign="top" class="main"><?php echo TEXT_PRODUCTS_LINKED; ?></td>
			<td class="smallText" style="padding-left: 25px;"><?php
	if ((int)$pInfo->products_id > 0) {
?>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr valign="top">
				<td class="smallText"><?php echo TEXT_CHOOSE_CATEGORY . '<br>' . tep_draw_pull_down_menu('categories_tree', tep_get_category_tree(0, '', '', '', false, $tPath)); ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td valign="bottom"><?php echo tep_image_button('button_search.gif', IMAGE_SEARCH, 'style="cursor: pointer;" onClick="getXMLDOM(\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&action=list_products', (getenv('HTTPS')=='on' ? 'SSL' : 'NONSSL'), false) . '&categories_id=\' + categories_tree.options[categories_tree.selectedIndex].value, \'products_list\'); document.getElementById(\'products_table\').style.display =\'\';"'); ?></td>
			  </tr>
			</table>
			<div id="products_table" style="display: none;"><br /><table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="smallText"><?php echo TEXT_CHOOSE_PRODUCT . '<br /><div id="products_list">' . tep_draw_pull_down_menu('products_tree', array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT))) . '</div>'; ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td class="smallText"><?php echo '&nbsp;' . TEXT_LINKED_TYPE . '<br>' . tep_draw_radio_field('linked_type', '1', true) . TEXT_LINKED_ONE_WAY . ' ' . tep_draw_radio_field('linked_type', '2', false) . TEXT_LINKED_TWO_WAY; ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td valign="bottom"><?php echo tep_image_button('button_insert.gif', IMAGE_INSERT, 'style="cursor: pointer;" onClick="getXMLDOM(\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&action=add_linked&products_id=' . $pInfo->products_id, (getenv('HTTPS')=='on' ? 'SSL' : 'NONSSL'), false) . '&linked_id=\' + products_tree.options[products_tree.selectedIndex].value + \'&linked_type=\' + (linked_type[1].checked ? 2 : 1), \'linked\'); products_tree.selectedIndex = \'\'; linked_type[0].checked = true; linked_type[1].checked = false;"'); ?></td>
			  </tr>
			</table></div>
<?php
	  $linked_query = tep_db_query("select pl.products_id, pd.products_name, pl.linked_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_LINKED . " pl where p.products_id = pd.products_id and p.products_id = pl.linked_id and pd.language_id = '" . (int)$languages_id . "' and pl.products_id = '" . (int)$pInfo->products_id . "' order by pd.products_name");
	  echo '<div id="linked" class="smallText">';
	  while ($linked = tep_db_fetch_array($linked_query)) {
		echo '<br />' . "\n" . tep_draw_checkbox_field('products_linked[]', $linked['linked_id'], true) . htmlspecialchars(str_replace("'", '#039;', $linked['products_name']));
	  }
	  echo '</div>';
	} else {
	  echo TEXT_PRODUCT_WARNING_LINKED;
	}
?></td>
		  </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr>
			<td valign="top" class="main"><?php echo TEXT_INFORMATION_LINKED; ?></td>
			<td class="smallText" style="padding-left: 25px;"><?php
	if ((int)$pInfo->products_id > 0) {
?>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="smallText"><?php echo TEXT_CHOOSE_INFORMATION . '<br /><div id="information_list">' . tep_draw_pull_down_menu('information_tree', tep_get_information_tree()) . '</div>'; ?></td>
				<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
				<td valign="bottom"><?php echo tep_image_button('button_insert.gif', IMAGE_INSERT, 'style="cursor: pointer;" onClick="getXMLDOM(\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&action=add_linked_information&products_id=' . $pInfo->products_id, (getenv('HTTPS')=='on' ? 'SSL' : 'NONSSL'), false) . '&linked_id=\' + information_tree.options[information_tree.selectedIndex].value, \'linked_information\'); information_tree.selectedIndex = \'\';"'); ?></td>
			  </tr>
			</table>
<?php
	  $linked_query = tep_db_query("select i.information_id, i.information_name from " . TABLE_PRODUCTS_TO_INFORMATION . " p2i, " . TABLE_INFORMATION . " i where i.information_id = p2i.information_id and i.language_id = '" . (int)$languages_id . "' and p2i.products_id = '" . (int)$pInfo->products_id . "' order by i.information_name");
	  echo '<div id="linked_information" class="smallText">';
	  while ($linked = tep_db_fetch_array($linked_query)) {
		echo '<br />' . "\n" . tep_draw_checkbox_field('information_linked[]', $linked['information_id'], true) . htmlspecialchars(str_replace("'", '#039;', $linked['information_name']));
	  }
	  echo '</div>';
	} else {
	  echo TEXT_PRODUCT_WARNING_LINKED_INFORMATION;
	}
?></td>
		  </tr>
        </table></div></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));

	if (isset($HTTP_GET_VARS['pID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . (isset($HTTP_GET_VARS['pID']) ? 'pID=' . $HTTP_GET_VARS['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
#	echo tep_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } elseif ($action == 'new_product_preview') {
    if (tep_not_null($HTTP_POST_VARS)) {
      $pInfo = new objectInfo($HTTP_POST_VARS);
      $products_name = $HTTP_POST_VARS['products_name'];
      $products_description = $HTTP_POST_VARS['products_description'];
      $products_url = $HTTP_POST_VARS['products_url'];
    } else {
      $product_query = tep_db_query("select p.products_id, pd.language_id, pd.products_name, pd.products_description, pd.products_url, p.products_quantity" . (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock') ? ", p.products_stock" : "") . ", p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.manufacturers_id  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
      $product = tep_db_fetch_array($product_query);

      $pInfo = new objectInfo($product);
      $products_image_name = $pInfo->products_image;
    }

    $form_action = (isset($HTTP_GET_VARS['pID'])) ? 'update_product' : 'insert_product';

    echo tep_draw_form($form_action, FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"');

    $languages = tep_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if (isset($HTTP_GET_VARS['read']) && ($HTTP_GET_VARS['read'] == 'only')) {
        $pInfo->products_name = tep_get_products_name($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = tep_get_products_description($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_url = tep_get_products_url($pInfo->products_id, $languages[$i]['id']);
      } else {
        $pInfo->products_name = tep_db_prepare_input($products_name[$languages[$i]['id']]);
        $pInfo->products_description = tep_db_prepare_input($products_description[$languages[$i]['id']]);
        $pInfo->products_url = tep_db_prepare_input($products_url[$languages[$i]['id']]);
      }
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . $pInfo->products_name; ?></td>
            <td class="pageHeading" align="right"><?php echo $currencies->format($pInfo->products_price); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; ?></td>
      </tr>
<?php
      if ($pInfo->products_url) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, tep_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, tep_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }

    if (isset($HTTP_GET_VARS['read']) && ($HTTP_GET_VARS['read'] == 'only')) {
      if (isset($HTTP_GET_VARS['origin'])) {
        $pos_params = strpos($HTTP_GET_VARS['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($HTTP_GET_VARS['origin'], 0, $pos_params);
          $back_url_params = substr($HTTP_GET_VARS['origin'], $pos_params + 1);
        } else {
          $back_url = $HTTP_GET_VARS['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = FILENAME_CATEGORIES;
        $back_url_params = tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id;
      }
?>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link($back_url, $back_url_params, 'NONSSL') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td align="right" class="smallText">
<?php
/* Re-Post all POST'ed variables */
      reset($HTTP_POST_VARS);
      while (list($key, $value) = each($HTTP_POST_VARS)) {
        if (!is_array($HTTP_POST_VARS[$key])) {
          echo tep_draw_hidden_field($key, $value);
        }
      }

      echo tep_image_submit('button_back.gif', IMAGE_BACK, 'name="edit"') . '&nbsp;&nbsp;';

      if (isset($HTTP_GET_VARS['pID'])) {
        echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
      } else {
        echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
      }
      echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
    }
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, 22); ?></td>
<?php
	if ($tPath > 0) {
?>
			<td align="right"><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
	  echo tep_draw_form('search', FILENAME_CATEGORIES, '', 'get');
	  echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search');
	  reset($HTTP_GET_VARS);
	  while (list($k, $v) = each($HTTP_GET_VARS)) {
		if (!in_array($k, array('cPath', 'search'))) echo tep_draw_hidden_field($k, $v);
	  }
	  echo '</form>';
?>
                </td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right">
<?php
	  echo tep_draw_form('goto', FILENAME_CATEGORIES, 'tPath=' . $tPath, 'get');
	  echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('cPath', tep_get_category_tree(0, '', '', '', false, $tPath), $current_category_id, 'onChange="this.form.submit();"');
	  reset($HTTP_GET_VARS);
	  while (list($k, $v) = each($HTTP_GET_VARS)) {
		if (!in_array($k, array('cPath', 'search'))) echo tep_draw_hidden_field($k, $v);
	  }
	  echo '</form>';
?>
                </td>
              </tr>
			</table></td>
<?php
	}
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if ($tPath > 0) {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CATEGORIES_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $categories_count = 0;
	  $rows = 0;
	  if (isset($HTTP_GET_VARS['search'])) {
		$search = tep_db_prepare_input($HTTP_GET_VARS['search']);

		$categories_query = tep_db_query("select c.*, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '" . (int)$tPath . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and (cd.categories_name like '%" . str_replace(' ', "%' and cd.categories_name like '%", tep_db_input($search)) . "%') order by c.sort_order, cd.categories_name");
	  } else {
		$categories_query = tep_db_query("select c.*, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.products_types_id = '" . (int)$tPath . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
	  }
	  while ($categories = tep_db_fetch_array($categories_query)) {
		$categories_count++;
		$rows++;

// Get parent_id for subcategories if search
		if (isset($HTTP_GET_VARS['search'])) $cPath = $categories['parent_id'];

		if ((!isset($HTTP_GET_VARS['cID']) && !isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
		  $category_childs = array('childs_count' => tep_childs_in_category_count($categories['categories_id']));
		  $category_products = array('products_count' => tep_products_in_category_count($categories['categories_id'], true));

		  $cInfo_array = array_merge($categories, $category_childs, $category_products);
		  $cInfo = new objectInfo($cInfo_array);
		}

		if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&' . tep_get_path($categories['categories_id'])) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="3"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&' . tep_get_path($categories['categories_id'])) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $categories['sort_order'] . ']&nbsp;<strong>' . $categories['categories_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($categories['categories_status'] == '1') {
		  echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&flag=0&cID=' . $categories['categories_id']) . '" onClick="this.href += \'&other=\'+(confirm(\'' . WARNING_SUBSTATUS_OFF . '\') ? 1 : 0);">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&flag=1&cID=' . $categories['categories_id']) . '" onClick="this.href += \'&other=\'+(confirm(\'' . WARNING_SUBSTATUS_ON . '\') ? 1 : 0);">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		}
		if ($categories['categories_listing_status'] == '1') {
		  echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&listing_flag=0&cID=' . $categories['categories_id']) . '" onClick="this.href += \'&other=\'+(confirm(\'' . WARNING_SUBSTATUS_LISTING_OFF . '\') ? 1 : 0);">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		} else {
		  echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&listing_flag=1&cID=' . $categories['categories_id']) . '" onClick="this.href += \'&other=\'+(confirm(\'' . WARNING_SUBSTATUS_LISTING_ON . '\') ? 1 : 0);">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		}
		if ($categories['categories_xml_status'] == '1') {
		  echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&xml_flag=0&cID=' . $categories['categories_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		} else {
		  echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'cID')) . 'action=setflag&xml_flag=1&cID=' . $categories['categories_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }

	  if ($action=='update_opt') echo tep_draw_form('opt', FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'pID', 'cID')) . 'action=update_opt_confirm', 'post');
	  $products_count = 0;
	  if (isset($HTTP_GET_VARS['search'])) {
		$products_query_row = "select p.*, pd.products_name, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_types_id = '" . (int)$tPath . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and ((pd.products_name like '%" . str_replace(' ', "%' and pd.products_name like '%", tep_db_input($search)) . "%') or (p.products_model like '%" . str_replace(' ', "%' and p.products_model like '%", tep_db_input($search)) . "%') or (pd.products_description like '%" . str_replace(' ', "%' and pd.products_description like '%", tep_db_input($search)) . "%') or p.products_id = '" . (int)tep_db_input($search) . "') order by p.sort_order, pd.products_name";
	  } else {
		$products_query_row = "select p.*, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_types_id = '" . (int)$tPath . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by p.sort_order, pd.products_name";
	  }
      $products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_row, $products_query_numrows);
	  $products_query = tep_db_query($products_query_row);
	  while ($products = tep_db_fetch_array($products_query)) {
		$products_count++;
		$rows++;

// Get categories_id for product if search
		if (isset($HTTP_GET_VARS['search'])) $cPath = $products['categories_id'];

		if ( (!isset($HTTP_GET_VARS['pID']) && !isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
		  $reviews_query = tep_db_query("select (avg(reviews_vote) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products['products_id'] . "'");
		  $reviews = tep_db_fetch_array($reviews_query);
		  if (!is_array($reviews)) $reviews = array();
		  $pInfo_array = array_merge($products, $reviews);
		  $pInfo = new objectInfo($pInfo_array);
		}

		if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)"' . ($action=='update_opt' ? '' : ' onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'read')) . 'pID=' . $products['products_id'] . '&action=new_product_preview&read=only') . '\'"') . '>' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)"' . ($action=='update_opt' ? '' : ' onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'read')) . 'pID=' . $products['products_id']) . '\'"') . '>' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo ($action=='update_opt' ? tep_draw_checkbox_field('update[]', $products['products_id'], false, '', 'id="c_' . ($products_count-1) . '"') . '&nbsp;[' . tep_draw_input_field('sort_order[' . $products['products_id'] . ']', $products['sort_order'], 'size="2"') : '[' . $products['sort_order']) . ']&nbsp;' . $products['products_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products['products_model']; ?></td>
                <td class="dataTableContent" align="center"><?php echo (($action=='update_opt') ? $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'] . tep_draw_input_field('price[' . $products['products_id'] . ']', (string)tep_round($products['products_price'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']), 'size="7"' . (tep_not_null($currencies->currencies[DEFAULT_CURRENCY]['symbol_right']) ? ' style="text-align: right;"' : '')) . $currencies->currencies[DEFAULT_CURRENCY]['symbol_right'] : $currencies->format($products['products_price'])); ?></td>
                <td class="dataTableContent" align="center"><nobr>
<?php
		if ($action=='update_opt') {
		  echo tep_draw_checkbox_field('status[' . $products['products_id'] . ']', '1', $products['products_status']) . ' ' . tep_draw_checkbox_field('listing_status[' . $products['products_id'] . ']', '1', $products['products_listing_status']) . ' ' . tep_draw_checkbox_field('xml_status[' . $products['products_id'] . ']', '1', $products['products_xml_status']);
		} else {
		  if ($products['products_status'] == '1') {
			echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'flag')) . 'action=setflag&flag=0&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		  } else {
			echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'flag')) . 'action=setflag&flag=1&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		  }
		  if ($products['products_listing_status'] == '1') {
			echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'listing_flag')) . 'action=setflag&listing_flag=0&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		  } else {
			echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'listing_flag')) . 'action=setflag&listing_flag=1&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		  }
		  if ($products['products_xml_status'] == '1') {
			echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'xml_flag')) . 'action=setflag&xml_flag=0&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '</a>';
		  } else {
			echo '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action', 'xml_flag')) . 'action=setflag&xml_flag=1&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '</a>';
		  }
		}
?></nobr></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
	  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'pID', 'page'))); ?></td>
                  </tr>
				</table></td>
			  </tr>
<?php
	} else {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="3"><?php echo TABLE_HEADING_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $rows = 0;
	  $types_query = tep_db_query("select * from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . $languages_id . "' order by sort_order, products_types_name");
	  while ($types = tep_db_fetch_array($types_query)) {
		$rows ++;
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $types['products_types_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
		  $types_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$types['products_types_id'] . "'");
		  $types_products = tep_db_fetch_array($types_products_query);

		  $types_categories_query = tep_db_query("select count(*) as categories_count from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$types['products_types_id'] . "'");
		  $types_categories = tep_db_fetch_array($types_categories_query);

		  $tInfo_array = array_merge($types, $types_products, $types_categories);

		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($types['products_types_id'] == $tInfo->products_types_id)) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $types['products_types_id'] . '&action=edit_type') . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $types['products_types_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="3"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $types['products_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $types['sort_order'] . ']&nbsp;' . ($types['products_types_default_status']=='1' ? '<strong>' . $types['products_types_name'] . '</strong>' : $types['products_types_name']); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($types['products_types_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=0&tID=' . $types['products_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=1&tID=' . $types['products_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($types['products_types_id'] == $tInfo->products_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $types['products_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
	}

    $cPath_back = '';
    if (sizeof($cPath_array) > 0) {
      for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
        if (empty($cPath_back)) {
          $cPath_back .= $cPath_array[$i];
        } else {
          $cPath_back .= '_' . $cPath_array[$i];
        }
      }
    }

    $cPath_back = (tep_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';

	if ($action=='update_opt') {
	  $categories_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
	  $categories_array = array_merge($categories_array, tep_get_category_tree(0, '', 0));

	  $manufacturers_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
	  $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' order by manufacturers_name");
	  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
		$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
									   'text' => $manufacturers['manufacturers_name']);
	  }

	  $products_types_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
	  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
	  while ($products_types = tep_db_fetch_array($products_types_query)) {
		$products_types_array[] = array('id' => $products_types['products_types_id'],
										'text' => $products_types['products_types_name']);
	  }

	  $series_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
	  $series_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where language_id = '" . (int)$languages_id . "' order by sort_order, series_name");
	  while ($series = tep_db_fetch_array($series_query)) {
		$series_array[] = array('id' => $series['series_id'],
								'text' => $series['series_name']);
	  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr valign="top">
                    <td colspan="4" class="smallText"><?php echo sprintf(PRODUCT_UPDATE, tep_draw_checkbox_field('', '', false, '', 'onclick="for (i=0; i<' . $products_count . '; i++) { document.getElementById(\'c_\'+i).checked = this.checked ? true : false; }"')); ?></td>
				  </tr>
				  <tr valign="top">
					<td class="smallText" nowrap><?php echo PRODUCT_UPDATE_CATEGORY; ?><br><?php echo tep_draw_pull_down_menu('new_categories_id', $categories_array); ?></td>
					<td class="smallText" nowrap><?php echo PRODUCT_UPDATE_TYPE; ?><br><?php echo tep_draw_pull_down_menu('new_products_types_id', $products_types_array); ?></td>
					<td class="smallText" nowrap><?php echo PRODUCT_UPDATE_MANUFACTURER; ?><br><?php echo tep_draw_pull_down_menu('new_manufacturers_id', $manufacturers_array); ?></td>
					<td class="smallText" nowrap><?php echo PRODUCT_UPDATE_SERIE; ?><br><?php echo tep_draw_pull_down_menu('new_series_id', $series_array); ?></td>
				  </tr>
				  <tr valign="bottom">
					<td colspan="4" align="right" class="smallText"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?>&nbsp;</td>
                  </tr>
                </table></td>
			  </tr>
<?php
	} else {
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php if ($tPath > 0) echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br>' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></td>
                    <td align="right" class="smallText"><?php
	  if ($products_count > 0) {
		echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&action=update_opt') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;';
	  }
	  if (sizeof($cPath_array) > 0) {
		echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, $cPath_back . 'tPath=' . $tPath . '&cID=' . $current_category_id) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;';
	  } elseif ($tPath > 0) {
		echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $tPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;';
	  }
	  if (!isset($HTTP_GET_VARS['search'])) {
		if ($tPath > 0) {
		  echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&action=new_category') . '">' . tep_image_button('button_new_category.gif', IMAGE_NEW_CATEGORY) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&action=new_product') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>';
		}
	  }
?>&nbsp;</td>
                  </tr>
                </table></td>
			  </tr>
<?php
	}
?>
<?php
	if ($action=='update_opt') echo '</form>';
?>
            </table></td>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_category':
		if (tep_not_null($HTTP_POST_VARS)) $cInfo = new objectInfo($HTTP_POST_VARS);
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('newcategory', FILENAME_CATEGORIES, tep_get_all_get_params(array('action')) . 'action=insert_category', 'post'));
        $contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']');
        }
        $contents[] = array('text' => '<br>' . TEXT_CATEGORIES_NAME . $category_inputs_string);

        $contents[] = array('text' => '<br>' . TEXT_EDIT_CATEGORIES_PATH . '<br>' . tep_catalog_href_link(FILENAME_CATEGORIES, 'cPath=' . $current_category_id) . tep_draw_input_field('categories_path', $cInfo->categories_path, 'size="7"') . '/');
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('categories_status', '1', $cInfo->categories_status) . TEXT_EDIT_CATEGORIES_STATUS);

        $contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', '', 'size="2"'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit_category':
		if (tep_not_null($HTTP_POST_VARS)) $cInfo = new objectInfo($HTTP_POST_VARS);
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, tep_get_all_get_params(array('action')) . 'action=update_category', 'post') . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_EDIT_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', tep_get_category_name($cInfo->categories_id, $languages[$i]['id']));
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);

        $contents[] = array('text' => '<br>' . TEXT_EDIT_CATEGORIES_PATH . '<br>' . tep_catalog_href_link(FILENAME_CATEGORIES, 'cPath=' . $current_category_id) . tep_draw_input_field('categories_path', $cInfo->categories_path, 'size="' . ((tep_not_null($cInfo->categories_path) && strlen($cInfo->categories_path)-1 > 0) ? strlen($cInfo->categories_path) - 1 : '7') . '"') . '/');
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('categories_status', '1', $cInfo->categories_status) . TEXT_EDIT_CATEGORIES_STATUS);
        $contents[] = array('text' => '<br>' . TEXT_EDIT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $cInfo->sort_order, 'size="2"'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'tPath=' . $tPath . '&action=delete_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
        $contents[] = array('text' => '<br><strong>' . $cInfo->categories_name . '</strong>');
        if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count));
        if ($cInfo->products_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count));
		  $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_category_products', '1', false) . TEXT_DELETE_PRODUCTS);
		}
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'tPath=' . $tPath . '&action=move_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_CATEGORIES_INTRO, $cInfo->categories_name));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $cInfo->categories_name) . '<br>' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'action=delete_product_confirm') . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br><strong>' . $pInfo->products_name . '</strong>');

        $product_categories_string = '';
        $product_categories = tep_generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
          $category_path = '';
          for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
            $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $category_path = substr($category_path, 0, -16);
          $product_categories_string .= tep_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br>';
        }
        $product_categories_string = substr($product_categories_string, 0, -4);

        $contents[] = array('text' => '<br>' . $product_categories_string);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'action=move_product_confirm') . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENT_CATEGORIES . '<br><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br>' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'copy_to':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_COPY_TO . '</strong>');

        $contents = array('form' => tep_draw_form('copy_to', FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'action=copy_to_confirm') . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENT_CATEGORIES . '<br><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br>' . TEXT_CATEGORIES . '<br>' . tep_draw_pull_down_menu('categories_id', tep_get_category_tree(), $current_category_id));
#        $contents[] = array('text' => '<br>' . TEXT_HOW_TO_COPY . '<br>' . tep_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br>' . tep_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_draw_hidden_field('copy_as', 'link') . tep_image_submit('button_copy.gif', IMAGE_COPY) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('types', FILENAME_CATEGORIES, 'tID=' . $tInfo->products_types_id . '&action=delete_type_confirm') . tep_draw_hidden_field('types_id', $tInfo->products_types_id));
		$contents[] = array('text' => TEXT_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->products_types_name . '</strong>');

		if ($tInfo->categories_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CATEGORIES, $tInfo->categories_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_categories') . ' ' . TEXT_DELETE_CATEGORIES);
		}

		if ($tInfo->products_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS_TYPES, $tInfo->products_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
		}

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $tInfo->products_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
      default:
        if ($rows > 0) {
		  if (isset($tInfo) && is_object($tInfo)) {
			$heading[] = array('text' => '<strong>' . $tInfo->products_types_name . '</strong>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $tInfo->products_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tID=' . $tInfo->products_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('text' => '<br>' . TEXT_TYPE_PATH . '<br />' . HTTP_SERVER . DIR_WS_CATALOG . $tInfo->products_types_path . '/');
			$contents[] = array('text' => '<br>' . TEXT_TYPE_LETTER_SEARCH . '<br />' . ($tInfo->products_types_letter_search=='1' ? TEXT_YES : TEXT_NO));
			$contents[] = array('text' => '<br>' . TEXT_TYPE_DISCOUNTS . '<br />' . ($tInfo->products_types_discounts=='1' ? TEXT_YES : TEXT_NO));
			$contents[] = array('text' => '<br>' . TEXT_TYPE_FREE_SHIPPING . '<br />' . ($tInfo->products_types_free_shipping=='1' ? TEXT_YES : TEXT_NO));
			$contents[] = array('text' => '<br>' . TEXT_TYPE_DEFAULT . '<br />' . ($tInfo->products_types_default_status=='1' ? TEXT_YES : TEXT_NO));
			$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_datetime_short($tInfo->date_added));
			if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_datetime_short($tInfo->last_modified));
			$contents[] = array('text' => '<br>' . TEXT_CATEGORIES . ' ' . $tInfo->categories_count);
			$contents[] = array('text' => TEXT_PRODUCTS . ' ' . $tInfo->products_count);
		  } elseif (isset($cInfo) && is_object($cInfo)) { // category info box contents
            $heading[] = array('text' => '<strong>' . $cInfo->categories_name . '</strong>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=edit_category') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=delete_category') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=move_category') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
            if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
            $contents[] = array('text' => '<br>' . TEXT_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br>' . TEXT_PRODUCTS . ' ' . $cInfo->products_count);
			if (tep_not_null($cInfo->categories_image) && file_exists(DIR_FS_CATALOG_IMAGES . $cInfo->categories_image)) {
			  $contents[] = array('text' => '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $cInfo->categories_image, $cInfo->categories_name) . '<br>' . $cInfo->categories_image);
			}
          } elseif (isset($pInfo) && is_object($pInfo)) { // product info box contents
            $heading[] = array('text' => '<strong>' . tep_get_products_name($pInfo->products_id, $languages_id) . '</strong>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id . '&action=edit_product') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id . '&action=delete_product') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id . '&action=move_product') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->products_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($pInfo->products_date_added));
            if (tep_not_null($pInfo->products_last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($pInfo->products_last_modified));
            if (date('Y-m-d') < $pInfo->products_date_available) $contents[] = array('text' => TEXT_DATE_AVAILABLE . ' ' . tep_date_short($pInfo->products_date_available));
            $contents[] = array('text' => '<br>' . tep_info_image('thumbs/' . $pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br>' . $pInfo->products_image);
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price) . '<br>' . TEXT_PRODUCTS_QUANTITY_INFO . ' ' . $pInfo->products_quantity . (tep_db_field_exists(TABLE_PRODUCTS, 'products_stock') ? '<br>' . TEXT_PRODUCTS_STOCK_INFO . ' ' . $pInfo->products_stock : ''));
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_AVERAGE_RATING . ' ' . number_format($pInfo->average_rating, 2) . '%');
          }
        } else { // create category/product info
          $heading[] = array('text' => '<strong>' . EMPTY_CATEGORY . '</strong>');

          $contents[] = array('text' => TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS);
        }
        break;
    }

    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '            <td width="25%" valign="top">' . "\n";

      $box = new box;
      echo $box->infoBox($heading, $contents);

      echo '            </td>' . "\n";
    }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  }
?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>