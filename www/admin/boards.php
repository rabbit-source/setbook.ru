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

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $bPath = (isset($HTTP_GET_VARS['bPath']) ? $HTTP_GET_VARS['bPath'] : '');

  $boards_statuses = array('0' => BOARDS_STATUS_ON_MODERATION,
						   '1' => BOARDS_STATUS_ACCEPTED,
						   '2' => BOARDS_STATUS_REFUSED,
						   '3' => BOARDS_STATUS_SOLD);
  $boards_statuses_array = array(array('id' => '', 'text' => '- - - - - - -'));
  reset($boards_statuses);
  while (list($status_id, $status_name) = each($boards_statuses)) {
	$boards_statuses_array[] = array('id' => ($status_id+1), 'text' => $status_name);
  }

  $error = false;

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') || ($HTTP_GET_VARS['flag'] == '2') ) {
          if (isset($HTTP_GET_VARS['tID'])) {
            tep_db_query("update " . TABLE_BOARDS_TYPES . " set boards_types_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where boards_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
          } elseif (isset($HTTP_GET_VARS['bID'])) {
            tep_db_query("update " . TABLE_BOARDS . " set boards_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where boards_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('flag', 'action', 'bID')) . '&bID=' . $HTTP_GET_VARS['bID']));
        break;
      case 'move_confirm':
        $boards_id = tep_db_prepare_input($HTTP_POST_VARS['boards_id']);
        $new_board_type_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_board_type_id']);

		tep_db_query("update " . TABLE_BOARDS . " set boards_types_id = '" . (int)$new_board_type_id . "' where boards_id = '" . (int)$boards_id . "'");

		tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'tPath', 'page', 'bID')) . 'tPath=' . $new_board_type_id . '&bID=' . $boards_id));
        break;
      case 'insert_category':
      case 'update_category':
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
        $boards_categories_path = tep_db_prepare_input($HTTP_POST_VARS['boards_categories_path']);
		$boards_categories_path = preg_replace('/\_+/', '_', preg_replace('/\W/', '_', strtolower($boards_categories_path)));
        $status = tep_db_prepare_input($HTTP_POST_VARS['status']);

		$disabled_path = array('admin', 'news', 'information', 'links', 'images', 'includes', 'download', 'pub', 'styles');
		$same_path_query = tep_db_query("select boards_categories_path from " . TABLE_BOARDS_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and boards_categories_id <> '" . (int)$categories_id . "'");
		while ($same_path = tep_db_fetch_array($same_path_query)) {
		  $disabled_path[] = $same_path['categories_path'];
		}
		if (empty($boards_categories_path)) {
		  $messageStack->add(ERROR_CATEGORY_PATH_EMPTY, 'error');
		  $action = $action=='update_category' ? 'edit_category' : 'new_category';
		} elseif (in_array($boards_categories_path, $disabled_path)) {
		  $messageStack->add(ERROR_CATEGORY_PATH_EXISTS, 'error');
		  $action = $action=='update_category' ? 'edit_category' : 'new_category';
		} else {
		  $languages = tep_get_languages();
		  $categories_name_array = $HTTP_POST_VARS['categories_name'];
		  $categories_description_array = $HTTP_POST_VARS['categories_description'];

		  if ($action == 'insert_category') {
			$new_category_info_query = tep_db_query("select max(boards_categories_id) as max_categories_id from " . TABLE_BOARDS_CATEGORIES . "");
			$new_category_info = tep_db_fetch_array($new_category_info_query);
			$boards_categories_id = (int)$new_category_info['max_categories_id'] + 1;
		  } elseif ($action == 'update_category') {
			$boards_categories_id = tep_db_prepare_input($HTTP_POST_VARS['boards_categories_id']);
		  }

		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

			$description = str_replace('\\\"', '"', $categories_description_array[$language_id]);
			$description = str_replace('\"', '"', $description);
			$description = str_replace("\\\'", "\'", $description);
			$description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
			$description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
			$description = str_replace(' - ', ' &ndash; ', $description);

			$sql_data_array = array('sort_order' => $sort_order,
									'status' => $categories_status,
									'boards_categories_path' => $boards_categories_path,
									'categories_name' => tep_db_prepare_input($categories_name_array[$language_id]),
									'categories_description' => $description);

			if ($action == 'insert_category') {
			  $insert_sql_data = array('boards_categories_id' => $boards_categories_id,
									   'language_id' => $languages[$i]['id'],
									   'parent_id' => $current_category_id,
									   'date_added' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_BOARDS_CATEGORIES, $sql_data_array);
			} elseif ($action == 'update_category') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_BOARDS_CATEGORIES, $sql_data_array, 'update', "boards_categories_id = '" . (int)$boards_categories_id . "' and language_id = '" . (int)$language_id . "'");
			}
		  }

		  if ($HTTP_POST_VARS['image_delete']=='1') {
			$prev_file_query = tep_db_query("select image from " . TABLE_BOARDS_CATEGORIES . " where boards_categories_id = '" . (int)$boards_categories_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['image']) && $prev_file['image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['image']);
			}
			tep_db_query("update " . TABLE_BOARDS_CATEGORIES . " set image = '' where boards_categories_id = '" . (int)$boards_categories_id . "'");
		  } else {
			$uploaded = false;
			if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
			  $size = @getimagesize($categories_image);
			  if ($size[2]=='3') $ext = '.png';
			  elseif ($size[2]=='2') $ext = '.jpg';
			  else $ext = '.gif';
			  $new_filename = $boards_categories_id . $ext;
			  $upload->filename = 'categories/' . $new_filename;
			  if ($upload->upload('image', DIR_FS_CATALOG_IMAGES)) {
				if (CATEGORY_IMAGE_WIDTH > 0 || CATEGORY_IMAGE_HEIGHT > 0) {
				  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', CATEGORY_IMAGE_WIDTH, CATEGORY_IMAGE_HEIGHT);
				}
				$prev_file_query = tep_db_query("select image from " . TABLE_BOARDS_CATEGORIES . " where boards_categories_id = '" . (int)$boards_categories_id . "'");
				$prev_file = tep_db_fetch_array($prev_file_query);
				if (tep_not_null($prev_file['image']) && $prev_file['image']!=$upload->filename) {
				  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['image']);
				}
				tep_db_query("update " . TABLE_BOARDS_CATEGORIES . " set image = '" . $upload->filename . "' where boards_categories_id = '" . (int)$boards_categories_id . "'");
			  }
			}
		  }

		  tep_update_blocks($boards_categories_id, 'boards_category');

		  tep_redirect(tep_href_link(FILENAME_BOARDS, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $boards_categories_id));
		}
        break;
      case 'delete_category_confirm':
        if (isset($HTTP_POST_VARS['boards_categories_id'])) {
          $boards_categories_id = tep_db_prepare_input($HTTP_POST_VARS['boards_categories_id']);

          $categories = array($boards_categories_id);
		  tep_get_subcategories($categories, $boards_categories_id, TABLE_BOARDS_CATEGORIES);

		  tep_remove_board_category($categories);
		}

        tep_redirect(tep_href_link(FILENAME_BOARDS, 'tPath=' . $tPath . '&cPath=' . $cPath));
        break;
      case 'update':
		$boards_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);

		$boards_status = tep_db_prepare_input($HTTP_POST_VARS['boards_status']);
		$customers_name = tep_db_prepare_input($HTTP_POST_VARS['customers_name']);
		$customers_telephone = tep_db_prepare_input($HTTP_POST_VARS['customers_telephone']);
		$customers_email_address = tep_db_prepare_input($HTTP_POST_VARS['customers_email_address']);
		$customers_other_contacts = tep_db_prepare_input($HTTP_POST_VARS['customers_other_contacts']);
		$customers_country = tep_db_prepare_input($HTTP_POST_VARS['customers_country']);
		$customers_state = tep_db_prepare_input($HTTP_POST_VARS['customers_state']);
		$customers_city = tep_db_prepare_input($HTTP_POST_VARS['customers_city']);
		$boards_price = str_replace(',', '.', (float)$HTTP_POST_VARS['boards_price']);
		$boards_quantity = tep_db_prepare_input($HTTP_POST_VARS['boards_quantity']);
		$boards_name = tep_db_prepare_input($HTTP_POST_VARS['boards_name']);
		$boards_currency = tep_db_prepare_input($HTTP_POST_VARS['boards_currency']);
		$boards_condition = tep_db_prepare_input($HTTP_POST_VARS['boards_condition']);
		$expires_day = (int)$HTTP_POST_VARS['expires_day'];
		$expires_month = (int)$HTTP_POST_VARS['expires_month'];
		$expires_year = (int)$HTTP_POST_VARS['expires_year'];
		$boards_notify = (int)$HTTP_POST_VARS['boards_notify'];
		$boards_payment_methods = tep_db_prepare_input($HTTP_POST_VARS['boards_payment_methods']);
		$boards_shipping_methods = tep_db_prepare_input($HTTP_POST_VARS['boards_shipping_methods']);
		$boards_share_contacts = tep_db_prepare_input($HTTP_POST_VARS['boards_share_contacts']);

		$description = str_replace('\\\"', '"', $HTTP_POST_VARS['boards_description']);
		$description = str_replace('\"', '"', $description);
		$description = str_replace("\\\'", "\'", $description);
		$description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		$description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		$description = str_replace(' - ', ' &ndash; ', $description);
		$description = str_replace(' &mdash; ', ' &ndash; ', $description);
		$description = preg_replace('/<a href="?([^"|>]+)"?>/ie', "'<a href=' . (strpos('$1', 'setbook.')===false ? '\"' . DIR_WS_CATALOG . 'redirect.php?goto=' . urlencode(trim(str_replace('http://', '', '$1'))) . '\" target=\"_blank\"' : '\"' . substr(str_replace('http://', '', '$1'), strpos(str_replace('http://', '', '$1'), '/')) . '\"') . '>'", $description);

		$boards_payment_method = '';
		if (is_array($boards_payment_methods)) {
		  reset($boards_payment_methods);
		  while (list(, $boards_payment_id) = each($boards_payment_methods)) {
			if (in_array($boards_payment_id, array_keys($boards_payments_array))) $boards_payment_method .= $boards_payment_id . "\n";
		  }
		  $boards_payment_method = trim($boards_payment_method);
		}

		$boards_shipping_method = '';
		if (is_array($boards_shipping_methods)) {
		  reset($boards_shipping_methods);
		  while (list(, $boards_shipping_id) = each($boards_shipping_methods)) {
			if (in_array($boards_shipping_id, array_keys($boards_shippings_array))) $boards_shipping_method .= $boards_shipping_id . "\n";
		  }
		  $boards_shipping_method = trim($boards_shipping_method);
		}

		$boards_share_contacts_string = '';
		if (is_array($boards_share_contacts)) {
		  reset($boards_share_contacts);
		  while (list(, $boards_share_contacts_id) = each($boards_share_contacts)) {
			if (in_array($boards_share_contacts_id, array('telephone', 'email_address'))) $boards_share_contacts_string .= $boards_share_contacts_id . "\n";
		  }
		  $boards_share_contacts_string = trim($boards_share_contacts_string);
		}

        if (strlen($customers_name) < 2) {
          $error = true;
          $entry_lastname_error = true;
        } else {
          $entry_lastname_error = false;
        }

        if (strlen($boards_name) < 2) {
          $error = true;
          $boards_name_error = true;
        } else {
          $boards_name_error = false;
        }

		if (strlen($boards_price) < 1) {
          $error = true;
          $boards_price_error = true;
        } else {
          $boards_price_error = false;
        }

        if (strlen($customers_city) < 2) {
          $error = true;
          $customers_city_error = true;
        } else {
          $customers_city_error = false;
        }

        if (strlen($customers_state) < 2) {
          $error = true;
          $customers_state_error = true;
        } else {
          $customers_state_error = false;
        }

        if (strlen($customers_country) < 2) {
          $error = true;
          $entry_country_error = true;
        } else {
          $entry_country_error = false;
        }

        if (strlen($customers_email_address) < 2) {
          $error = true;
          $entry_email_address_error = true;
        } else {
          $entry_email_address_error = false;
        }

		if ($error == false) {
		  $boards_currency_value = $currencies->get_value($boards_currency);
		  $currency_check_query = tep_db_query("select boards_currency, boards_currency_value from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
		  $currency_check = tep_db_fetch_array($currency_check_query);
		  if ($currency_check['boards_currency']==$boards_currency) {
			$boards_currency_value = $currency_check['boards_currency_value'];
		  }
		  $price = str_replace(',', '.', $boards_price/$boards_currency_value);

		  $sql_data_array = array('customers_name' => $customers_name,
								  'customers_email_address' => $customers_email_address,
								  'customers_telephone' => $customers_telephone,
//								  'boards_share_contacts' => $boards_share_contacts_string,
								  'customers_other_contacts' => $customers_other_contacts,
								  'customers_country' => $customers_country,
								  'customers_state' => $customers_state,
								  'customers_city' => $customers_city,
								  'boards_name' => $boards_name,
								  'boards_description' => $boards_description,
								  'boards_status' => $boards_status,
								  'boards_price' => $price,
								  'boards_currency' => $boards_currency,
								  'boards_currency_value' => $boards_currency_value,
								  'boards_quantity' => $boards_quantity,
								  'boards_condition' => $boards_condition,
//								  'boards_payment_method' => $boards_payment_method,
//								  'boards_shipping_method' => $boards_shipping_method,
								  'last_modified' => 'now()');

		  tep_db_perform(TABLE_BOARDS, $sql_data_array, 'update', "boards_id = '" . (int)$boards_id . "'");

		  $prev_image_query = tep_db_query("select boards_image from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
		  $prev_image = tep_db_fetch_array($prev_image_query);
		  $prev_images_array = explode("\n", $prev_image['boards_image']);
		  if (!is_array($prev_images_array)) $prev_images_array = array();

		  $boards_images = array();
		  $boards_images_dir = DIR_FS_CATALOG . 'images/boards/' . substr(sprintf('%09d', $boards_id), 0, 6) . '/';
		  for ($i=0; $i<11; $i++) {
			if (!is_dir($boards_images_dir)) mkdir($boards_images_dir, 0777);
			if (!is_dir($boards_images_dir . 'big/')) mkdir($boards_images_dir . 'big/', 0777);
			if (!is_dir($boards_images_dir . 'thumbs/')) mkdir($boards_images_dir . 'thumbs/', 0777);
			if (tep_not_null($HTTP_POST_VARS['boards_existing_images'][$i])) {
			  if ($HTTP_POST_VARS['boards_images_delete'][$i]=='1') {
				@unlink($boards_images_dir . basename($prev_images_array[$i]));
				@unlink($boards_images_dir . 'big/' . basename($prev_images_array[$i]));
				@unlink($boards_images_dir . 'thumbs/' . basename($prev_images_array[$i]));
			  } else {
				$boards_images[] = basename($prev_images_array[$i]);
			  }
			}
		  }
		  tep_db_query("update " . TABLE_BOARDS . " set boards_image = '" . implode("\n", $boards_images) . "' where boards_id = '" . (int)$boards_id . "'");

		  tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $boards_id));

        } elseif ($error == true) {
          $bInfo = new objectInfo($HTTP_POST_VARS);
		  $action = 'edit';
        }

        break;
	  case 'delete_confirm':
        $boards_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);

		if ($HTTP_POST_VARS['board_blacklist']=='1') {
		  $blacklist_check_query = tep_db_query("select count(*) as total from " . TABLE_BLACKLIST . " where blacklist_ip in (select customers_ip from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "')");
		  $blacklist_check = tep_db_fetch_array($blacklist_check_query);
		  if ($blacklist_check['total'] < 1) {
			tep_db_query("insert into " . TABLE_BLACKLIST . " (blacklist_ip, customers_id, blacklist_comments, date_added, users_id) select customers_ip, customers_id, '" . tep_db_input(tep_db_prepare_input($HTTP_POST_VARS['board_blacklist_reason'])) . "', now(), '" . tep_db_input($REMOTE_USER) . "' from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
		  }
		}

		tep_remove_board($boards_id);

        tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bPath', 'bID', 'action'))));
        break;
      case 'insert_type':
      case 'update_type':
		if (isset($HTTP_POST_VARS['boards_types_id'])) {
		  $boards_types_id = tep_db_prepare_input($HTTP_POST_VARS['boards_types_id']);
		} else {
		  $max_boards_types_id_query = tep_db_query("select max(boards_types_id) as boards_types_id from " . TABLE_BOARDS_TYPES . "");
		  $max_boards_types_id_array = tep_db_fetch_array($max_boards_types_id_query);
		  $boards_types_id = (int)$max_boards_types_id_array['boards_types_id'] + 1;
		}

        $boards_types_path = tep_db_prepare_input($HTTP_POST_VARS['boards_types_path']);
        $boards_types_path = preg_replace('/\_+/', '_', preg_replace('/[^\d\w]/i', '_', strtolower(trim($boards_types_path))));

		if (!tep_not_null($boards_types_path)) {
		  $messageStack->add(ERROR_PATH_EMPTY);
		  $action = ($action == 'update_type' && tep_not_null($boards_types_id)) ? 'edit_type' : 'new_type';
		} else {
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$boards_types_name_array = $HTTP_POST_VARS['boards_types_name'];
			$boards_types_short_name_array = $HTTP_POST_VARS['boards_types_short_name'];
			$boards_types_short_description_array = $HTTP_POST_VARS['boards_types_short_description'];
			$boards_types_description_array = $HTTP_POST_VARS['boards_types_description'];

			$language_id = $languages[$i]['id'];

			$sql_data_array = array('boards_types_path' => $boards_types_path,
									'boards_types_name' => tep_db_prepare_input($boards_types_name_array[$language_id]),
									'boards_types_short_name' => tep_db_prepare_input($boards_types_short_name_array[$language_id]),
									'sort_order' => (int)$HTTP_POST_VARS['sort_order'],
									'boards_types_short_description' => tep_db_prepare_input($boards_types_short_description_array[$language_id]),
									'boards_types_description' => tep_db_prepare_input($boards_types_description_array[$language_id]));

			if ($action == 'insert_type') {
			  $insert_sql_data = array('date_added' => 'now()',
									   'boards_types_id' => $boards_types_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_BOARDS_TYPES, $sql_data_array);
			} elseif ($action == 'update_type') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_BOARDS_TYPES, $sql_data_array, 'update', "boards_types_id = '" . (int)$boards_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  tep_redirect(tep_href_link(FILENAME_BOARDS, 'tID=' . $boards_types_id));
		}
        break;
      case 'delete_type_confirm':
        $boards_types_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

		$boards_query = tep_db_query("select boards_id from " . TABLE_BOARDS . " where boards_types_id = '" . (int)$boards_types_id . "'");
		while ($boards = tep_db_fetch_array($boards_query)) {
		  tep_remove_board($boards['boards_id']);
		}
        tep_db_query("delete from " . TABLE_BOARDS_TYPES . " where boards_types_id = '" . (int)$boards_types_id . "'");

        tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'tID'))));
        break;
      default:
        $boards_query = tep_db_query("select * from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
        $boards = tep_db_fetch_array($boards_query);
        $bInfo = new objectInfo($boards);
    }
  }

  $boards_types_heading = '';
  $board_types = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $boards_types_query = tep_db_query("select boards_types_id, boards_types_name from " . TABLE_BOARDS_TYPES . "");
  while ($boards_types = tep_db_fetch_array($boards_types_query)) {
	$board_types[] = array('id' => $boards_types['boards_types_id'], 'text' => $boards_types['boards_types_name']);
	if (tep_not_null($tPath) && $tPath==$boards_types['boards_types_id']) $boards_types_heading = $boards_types['boards_types_name'];
  }

  function tep_get_boards_countries() {
	$countries_array = array(array('id' => '', 'text' => ''));
	$countries = tep_get_countries();
	reset($countries);
	while (list(, $country_info) = each($countries)) {
	  $countries_array[] = array('id' => $country_info['text'], 'text' => $country_info['text']);
	}
	return $countries_array;
  }

  function tep_get_boards_type_info($boards_types_id, $language_id, $field = 'boards_types_name') {
	if (tep_db_field_exists(TABLE_BOARDS_TYPES, $field)) {
	  $type_info_query = tep_db_query("select " . tep_db_input($field) . " as field from " . TABLE_BOARDS_TYPES . " where boards_types_id = '" . (int)$boards_types_id . "' and language_id = '" . (int)$language_id . "'");
	  $type_info = tep_db_fetch_array($type_info_query);
	  return $type_info['field'];
	} else {
	  return false;
	}
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($action == 'edit' && $bPath < 1) {
?>
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <?php echo tep_draw_form('boards', FILENAME_BOARDS, tep_get_all_get_params(array('action')) . 'action=update'); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main" width="250"><?php echo ENTRY_NAME;?></td>
            <td class="main"><?php
	if ($error == true) {
	  if ($entry_name_error == true) {
		echo tep_draw_input_field('customers_name', $bInfo->customers_name, 'size="30" maxlength="150"') . '&nbsp;' . ENTRY_NAME_ERROR;
	  } else {
		echo $bInfo->customers_name . tep_draw_hidden_field('customers_name', $bInfo->customers_name);
	  }
	} else {
	  echo tep_draw_input_field('customers_name', $bInfo->customers_name, 'size="30" maxlength="150"', true);
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_STATUS;?></td>
            <td class="main"><?php
	reset($boards_statuses);
	while (list($status_id, $status_name) = each($boards_statuses)) {
	  echo tep_draw_radio_field('boards_status', (string)$status_id, false, (string)$bInfo->boards_status) . ' ' . $status_name . '<br>';
	}
?></td>
          </tr>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
		  <tr>
			<td colspan="2" class="formAreaTitle"><?php echo CATEGORY_PRODUCT;?></td>
		  </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_TYPE;?></td>
            <td class="main"><?php
	echo tep_draw_pull_down_menu('boards_types_id', $board_types, $tPath)
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_PRODUCT;?></td>
            <td class="main"><?php
    if ($error == true) {
      if ($boards_name_error == true) {
        echo tep_draw_input_field('boards_name', $bInfo->boards_name, 'size="70" maxlength="32"') . '&nbsp;<span class="errorText">минимум 2 символа</span>';
      } else {
        echo $bInfo->boards_name . tep_draw_hidden_field('boards_name', $bInfo->boards_name);
      }
    } else {
      echo tep_draw_input_field('boards_name', $bInfo->boards_name, 'size="70" maxlength="32"');
    }
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_DESCRIPTION;?></td>
            <td class="main"><?php
	  $field_value = $bInfo->boards_description;
	  if (strpos($field_value, '<br'===false) && strpos($field_value, '<p')===false) $field_value = nl2br($field_value);
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('boards_description');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
		  <tr>
            <td class="main" width="250"><?php echo ENTRY_PRICE;?></td>
            <td class="main"><?php
	$item_price = str_replace(',', '.', $bInfo->boards_price * $currencies->get_value($bInfo->boards_currency));
    if ($error == true) {
      if ($boards_price_error == true) {
        echo tep_draw_input_field('boards_price', $item_price, 'size="10" maxlength="32"') . '&nbsp;<span class="errorText">минимум 1 символов</span>';
      } else {
        echo $currencies->format($bInfo->boards_price, true, $bInfo->boards_currency) . tep_draw_hidden_field('boards_price', $item_price);
      }
    } else {
      echo tep_draw_input_field('boards_price', $item_price, 'size="10" maxlength="32"');
    }
	$currencies_array = array();
	reset($currencies->currencies);
	while (list($currency_code, $currency_info) = each($currencies->currencies)) {
	  $currencies_array[] = array('id' => $currency_code, 'text' => $currency_info['title']);
	}
	echo tep_draw_pull_down_menu('boards_currency', $currencies_array, $bInfo->boards_currency);
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_QUANTITY; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  echo $bInfo->boards_quantity . tep_draw_hidden_field('boards_quantity', $bInfo->boards_quantity);
	} else {
	  echo tep_draw_input_field('boards_quantity', $bInfo->boards_quantity, 'size="3"');
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_CONDITION; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  echo sprintf(ENTRY_CONDITION_OF, $bInfo->boards_condition) . tep_draw_hidden_field('boards_condition', $bInfo->boards_condition);
	} else {
	  $conditions_array = array(array('id' => '', 'text' => '- - - - -'));
	  for ($i=1; $i<=5; $i++) {
		$conditions_array[] = array('id' => $i, 'text' => sprintf(ENTRY_CONDITION_OF, $i));
	  }
	  echo tep_draw_pull_down_menu('boards_condition', $conditions_array, $bInfo->boards_condition);
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_EXPIRES_DATE; ?></td>
            <td class="main"><?php
	if ($bInfo->expires_date > '0000-00-00') echo ENTRY_EXPIRES_DATE_TILL . ' ' . tep_date_long($bInfo->expires_date);
	else echo ENTRY_EXPIRES_DATE_NONE;
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_IMAGES; ?></td>
            <td class="main"><?php
	if (tep_not_null($bInfo->boards_image)) {
	  $boards_images_dir = 'boards/' . substr(sprintf('%09d', $bInfo->boards_id), 0, 6) . '/';
	  $boards_images = explode("\n", $bInfo->boards_image);
	  reset($boards_images);
	  while (list($i, $boards_image) = each($boards_images)) {
		echo '<a href="#" onclick="return false;" onmouseover="document.getElementById(\'bim' . $i . '\').style.display = \'\';" onmouseout="document.getElementById(\'bim' . $i . '\').style.display = \'none\';">' . $boards_image . '</a><span style="position: absolute; display: none; margin-top: -100px; border: 1px solid black;" id="bim' . $i . '">' . tep_info_image($boards_images_dir . $boards_image, '') . '</span> &nbsp; ' . tep_draw_checkbox_field('boards_images_delete[' . $i . ']', '1', false) . ENTRY_IMAGES_DELETE . tep_draw_hidden_field('boards_existing_images[' . $i . ']', $boards_image) . '<br>' . "\n";
	  }
	}
?></td>
          </tr>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
		  <tr>
			<td colspan="2" class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
		  </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  if ($entry_country_error == true) {
		echo tep_draw_pull_down_menu('customers_country', tep_get_boards_countries(), $bInfo->customers_country) . '&nbsp;' . ENTRY_COUNTRY_ERROR;
	  } else {
		echo $bInfo->customers_country . tep_draw_hidden_field('customers_country', $bInfo->customers_country);
	  }
	} else {
	  echo tep_draw_pull_down_menu('customers_country', tep_get_boards_countries(), $bInfo->customers_country, '', true);
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_STATE; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  if ($customers_state_error == true) {
		echo tep_draw_input_field('customers_state', $bInfo->customers_state, 'maxlength="32"') . '&nbsp;' . ENTRY_STATE_ERROR;
	  } else {
		echo $bInfo->customers_state . tep_draw_hidden_field('customers_state', $bInfo->customers_state);
	  }
	} else {
	  echo tep_draw_input_field('customers_state', $bInfo->customers_state, 'maxlength="32"', true);
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_CITY; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  if ($customers_city_error == true) {
		echo tep_draw_input_field('customers_city', $bInfo->customers_city, 'maxlength="32"') . '&nbsp;' . ENTRY_CITY_ERROR;
	  } else {
		echo $bInfo->customers_city . tep_draw_hidden_field('customers_city', $bInfo->customers_city);
	  }
	} else {
	  echo tep_draw_input_field('customers_city', $bInfo->customers_city, 'maxlength="32"', true);
	}
?></td>
          </tr>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		  </tr>
		  <tr>
			<td colspan="2" class="formAreaTitle"><?php echo CATEGORY_CONTACTS; ?></td>
		  </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_TELEPHONE; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  echo $bInfo->customers_telephone . tep_draw_hidden_field('customers_telephone', $bInfo->customers_telephone);
	} else {
	  echo tep_draw_input_field('customers_telephone', $bInfo->customers_telephone, 'maxlength="32"');
	}
?></td>
          </tr>
		  <tr>
            <td class="main" width="250"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  if ($entry_email_address_error == true) {
		echo tep_draw_input_field('customers_email_address', $bInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR;
	  } elseif ($entry_email_address_exists == true) {
		echo tep_draw_input_field('customers_email_address', $bInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
	  } else {
		echo $customers_email_address . tep_draw_hidden_field('customers_email_address', $bInfo->customers_email_address);
	  }
	} else {
	  echo tep_draw_input_field('customers_email_address', $bInfo->customers_email_address, 'maxlength="96"', true);
	}
?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_OTHER_CONTACTS; ?></td>
            <td class="main"><?php
	if ($error == true) {
	  echo $bInfo->customers_other_contacts . tep_draw_hidden_field('customers_other_contacts', $bInfo->customers_other_contacts);
	} else {
	  echo tep_draw_input_field('customers_other_contacts', $bInfo->customers_other_contacts, 'maxlength="32"');
	}
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action'))) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr></form>
<?php
  } elseif ($action=='new_category' || $action=='edit_category') {
    $parameters = array('boards_categories_name' => '',
						'boards_categories_description' => '',
						'boards_categories_id' => '',
						'image' => '',
						'date_added' => '',
						'last_modified' => '',
						'status' => '',
						'boards_categories_path' => '',
						);

    $cInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['cID']) && empty($HTTP_POST_VARS)) {
      $boards_category_query = tep_db_query("select * from " . TABLE_BOARDS_CATEGORIES . " where boards_categories_id = '" . (int)$HTTP_GET_VARS['cID'] . "' and language_id = '" . (int)$languages_id . "'");
      $boards_category = tep_db_fetch_array($boards_category_query);

      $cInfo->objectInfo($boards_category);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $cInfo->objectInfo($HTTP_POST_VARS);
      $boards_categories_name = $HTTP_POST_VARS['boards_categories_name'];
      $boards_categories_description = $HTTP_POST_VARS['boards_categories_description'];
    }

    $languages = tep_get_languages();

    if (!isset($cInfo->status)) $cInfo->status = '1';
    switch ($cInfo->status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

	$form_action = (isset($HTTP_GET_VARS['cID'])) ? 'update_category' : 'insert_category';
	echo tep_draw_form('new_category', FILENAME_BOARDS, tep_get_all_get_params(array('cID', 'action')) . (isset($HTTP_GET_VARS['cID']) ? 'cID=' . $HTTP_GET_VARS['cID'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('boards_categories_id', $cInfo->boards_categories_id);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_category' ? sprintf(TEXT_EDIT_CATEGORY, $cInfo->boards_categories_name) : sprintf(TEXT_NEW_CATEGORY, tep_output_generated_category_path($current_category_id)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td class="main" width="250"><?php echo TEXT_CATEGORY_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('status', '1', $in_status) . ' ' . TEXT_CATEGORY_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_CATEGORY_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('boards_categories_name[' . $languages[$i]['id'] . ']', (isset($boards_categories_name[$languages[$i]['id']]) ? $boards_categories_name[$languages[$i]['id']] : $cInfo->boards_categories_name), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_CATEGORY_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('image') . (tep_not_null($cInfo->image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $cInfo->image . ' &nbsp; ' . tep_draw_checkbox_field('image_delete', '1', false) . TEXT_IMAGE_DELETE . '</span>' : ''); ?></td>
          </tr>
		</table>
<?php
	echo tep_load_blocks($cInfo->categories_id, 'boards_category');
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
	  $field_value = (isset($boards_categories_description[$languages[$i]['id']]) ? $boards_categories_description[$languages[$i]['id']] : $cInfo->boards_categories_description);
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('boards_categories_description[' . $languages[$i]['id'] . ']');
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
            <td class="main" width="250"><?php echo TEXT_CATEGORY_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('boards_categories_path', $cInfo->boards_categories_path, 'size="' . (tep_not_null($cInfo->boards_categories_path) ? strlen($cInfo->boards_categories_path) - 1 : '7') . '"'); ?></td>
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
	if (isset($HTTP_GET_VARS['cID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $tPath . '&cPath=' . $cPath . (isset($HTTP_GET_VARS['cID']) ? '&cID=' . $HTTP_GET_VARS['cID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } else {
	$countries_array = array(array('id' => '', 'text' => '- - - - - -'));
	$countries_query = tep_db_query("select distinct customers_country from " . TABLE_BOARDS . " where parent_id = '0' order by customers_country");
	while ($countries = tep_db_fetch_array($countries_query)) {
	  $countries_array[] = array('id' => $countries['customers_country'], 'text' => $countries['customers_country']);
	}
	$states_array = array(array('id' => '', 'text' => '- - - - - -'));
	$states_query = tep_db_query("select distinct customers_state from " . TABLE_BOARDS . " where parent_id = '0' order by customers_state");
	while ($states = tep_db_fetch_array($states_query)) {
	  $states_array[] = array('id' => $states['customers_state'], 'text' => $states['customers_state']);
	}
	$cities_array = array(array('id' => '', 'text' => '- - - - - -'));
	$cities_query = tep_db_query("select distinct customers_city from " . TABLE_BOARDS . " where parent_id = '0' order by customers_city");
	while ($cities = tep_db_fetch_array($cities_query)) {
	  $cities_array[] = array('id' => $cities['customers_city'], 'text' => $cities['customers_city']);
	}
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading" height="25"><?php echo HEADING_TITLE . (tep_not_null($tPath) ? ' &raquo; ' . $boards_types_heading : ''); ?></td>
<?php
	if (tep_not_null($tPath)) {
?>
            <td class="smallText" align="right">
			<?php echo tep_draw_form('filter', FILENAME_BOARDS, '', 'get'); ?>
			<table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td><?php echo HEADING_TITLE_COUNTRY; ?></td>
				<td><?php echo tep_draw_pull_down_menu('country', $countries_array, '', 'onchange="this.form.submit()"'); ?></td>
				<td><?php echo HEADING_TITLE_STATE; ?></td>
				<td><?php echo tep_draw_pull_down_menu('state', $states_array, '', 'onchange="this.form.submit()"'); ?></td>
				<td><?php echo HEADING_TITLE_CITY; ?></td>
				<td><?php echo tep_draw_pull_down_menu('city', $cities_array, '', 'onchange="this.form.submit()"'); ?></td>
				<td><?php echo HEADING_TITLE_STATUS; ?></td>
				<td><?php echo tep_draw_pull_down_menu('status', $boards_statuses_array, '', 'onchange="this.form.submit();"'); ?></td>
			  </tr>                                     
			</table></form></td>
<?php
	}
?>
		  </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if (tep_not_null($tPath)) {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo ($bPath>0 ? TABLE_HEADING_TITLE_APPS : TABLE_HEADING_TITLE); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_FROM; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $where = " where 1 and parent_id = '" . (tep_not_null($bPath) ? (int)$bPath : '0') . "'";
	  if ($bPath < 1) {
		$where .= " and boards_types_id = '" . (int)$tPath . "'";
		if (tep_not_null($HTTP_GET_VARS['status'])) $where .= " and boards_status = '" . (int)($HTTP_GET_VARS['status']-1) . "'";
		if (tep_not_null($HTTP_GET_VARS['country'])) $where .= " and customers_country = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['country'])) . "'";
		if (tep_not_null($HTTP_GET_VARS['state'])) $where .= " and customers_state = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['state'])) . "'";
		if (tep_not_null($HTTP_GET_VARS['city'])) $where .= " and customers_city = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['city'])) . "'";
	  }

	  $boards_query_raw = "select * from " . TABLE_BOARDS . "" . $where . " order by date_added desc";
	  if ($bPath < 1) $boards_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $boards_query_raw, $boards_query_numrows);
	  $boards_query = tep_db_query($boards_query_raw);
	  while ($boards = tep_db_fetch_array($boards_query)) {
		$apps_count_query = tep_db_query("select count(*) as apps_count from " . TABLE_BOARDS . " where parent_id = '" . (int)$boards['boards_id'] . "'");
		$apps_count = tep_db_fetch_array($apps_count_query);
		$boards = array_merge($boards, $apps_count);
		if ((!isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $boards['boards_id']))) && !isset($bInfo)) {
		  $bInfo_array = $boards;
		  $bInfo = new objectInfo($bInfo_array);
		}

		if (isset($bInfo) && is_object($bInfo) && ($boards['boards_id'] == $bInfo->boards_id) ) {
		  echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id . '&action=edit') . '\'">' . "\n";
		} else {
		  echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID')) . 'bID=' . $boards['boards_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php if ($bPath < 1) echo ($boards['apps_count']>0 ? '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bPath', 'bID')) . 'bPath=' . $boards['boards_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>' : tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0; opacity: 0.3;"')) . '&nbsp;'; echo $boards['customers_name'] . (tep_not_null($boards['boards_name']) ? ': ' . $boards['boards_name'] : ''); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($boards['date_added']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $boards['customers_country'] . '/' . $boards['customers_city']; ?></td>
                <td class="dataTableContent" align="right"><?php echo ($bPath<1 ? $currencies->format($boards['boards_price'], true, $boards['boards_currency']) : '&nbsp;'); ?></td>
                <td class="dataTableContent" align="center"><?php
		if ($boards['boards_status'] == '3') {
		  echo BOARDS_STATUS_SOLD;
		} elseif ($bPath < 1) {
		  echo ($boards['boards_status']!='1' ? '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'bID')) . '&action=setflag&flag=1&bID=' . $boards['boards_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>' : tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10)) . 
		  '&nbsp;' . 
		  ($boards['boards_status']!='0' ? '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'bID')) . '&action=setflag&flag=0&bID=' . $boards['boards_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_yellow_light.gif', IMAGE_ICON_STATUS_YELLOW_LIGHT, 10, 10) . '</a>' : tep_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_YELLOW, 10, 10)) . 
		  '&nbsp;' . 
		  ($boards['boards_status']!='2' ? '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'bID')) . '&action=setflag&flag=2&bID=' . $boards['boards_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10));
		} else {
		  echo '&nbsp;';
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($bInfo) && is_object($bInfo) && ($boards['boards_id'] == $bInfo->boards_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID')) . 'bID=' . $boards['boards_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	  if ($bPath < 1) {
?>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $boards_split->display_count($boards_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $boards_split->display_links($boards_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'info', 'x', 'y', 'bID'))); ?></td>
                  </tr>
<?php
		if (tep_not_null($HTTP_GET_VARS['country']) || tep_not_null($HTTP_GET_VARS['state']) || tep_not_null($HTTP_GET_VARS['city']) || tep_not_null($HTTP_GET_VARS['status'])) {
?>
				  <tr>
					<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
				  </tr>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_BOARDS) . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                  </tr>
<?php
		}
	  }
?>
                </table></td>
              </tr>
<?php
	} else {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="3"><?php echo TABLE_HEADING_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $boards_types_query = tep_db_query("select * from " . TABLE_BOARDS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, boards_types_name");
	  while ($boards_types = tep_db_fetch_array($boards_types_query)) {
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $boards_types['boards_types_id']))) && !isset($tInfo) && substr($action, 0, 3)!='new') {
		  $boards_count_query = tep_db_query("select count(*) as boards_count from " . TABLE_BOARDS . " where boards_types_id = '" . (int)$boards_types['boards_types_id'] . "' and parent_id = '0'");
		  $boards_count = tep_db_fetch_array($boards_count_query);
		  $tInfo_array = array_merge($boards_types, $boards_count);
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($boards_types['boards_types_id'] == $tInfo->boards_types_id)) {
		  echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('tID', 'action', 'page', 'bID')) . 'tID=' . $tInfo->boards_types_id . '&action=edit_type') . '\'">' . "\n";
		} else {
		  echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('tID', 'action', 'page', 'bID')) . 'tID=' . $boards_types['boards_types_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="3" title="<?php echo $boards_types['boards_types_short_description']; ?>"><?php echo '<a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types['boards_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $boards_types['sort_order'] . '] <strong>' . $boards_types['boards_types_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($boards_types['boards_types_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=0&tID=' . $boards_types['boards_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=1&tID=' . $boards_types['boards_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($boards_types['boards_types_id'] == $tInfo->boards_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('rID', 'action', 'page', 'tID')) . 'tID=' . $boards_types['boards_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
	  }
	}
	if (empty($action)) {
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td colspan="2" align="right"><?php if (tep_not_null($bPath)) echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'bPath')) . 'bID=' . $bPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; elseif (tep_not_null($tPath)) echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'tID', 'action', 'tPath', 'page')) . 'tID=' . $tPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; if (DEBUG_MODE=='on') { if ($tPath>0) echo '&nbsp;<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action', 'page')) . 'action=new_category') . '">' . tep_image_button('button_new_category.gif', IMAGE_NEW_CATEGORY) . '</a>'; else echo '&nbsp;<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('tID', 'bID', 'action', 'page')) . 'action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
<?php
	}
?>
            </table></td>
<?php
	$heading = array();
	$contents = array();

	switch ($action) {
	  case 'new_type':
      case 'edit_type':
        $heading[] = array('text' => '<strong>' . ($action=='edit_type' ? TEXT_INFO_HEADING_EDIT_TYPE : TEXT_INFO_HEADING_NEW_TYPE) . '</strong>');

        $contents = array('form' => tep_draw_form('types', FILENAME_BOARDS, tep_get_all_get_params(array('action', 'tID')) . 'action=' . ($action=='edit_type' ? 'update_type' : 'insert_type'), 'post') . ($action=='edit_type' ? tep_draw_hidden_field('boards_types_id', $tInfo->boards_types_id) : ''));
        $contents[] = array('text' => ($action=='edit_type' ? TEXT_EDIT_TYPE_INTRO : TEXT_NEW_TYPE_INTRO));

        $languages = tep_get_languages();

        $type_inputs_string = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('boards_types_name[' . $languages[$i]['id'] . ']', tep_get_boards_type_info($tInfo->boards_types_id, $languages[$i]['id']), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_NAME . $type_inputs_string);

        $type_inputs_string = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('boards_types_short_name[' . $languages[$i]['id'] . ']', tep_get_boards_type_info($tInfo->boards_types_id, $languages[$i]['id'], 'boards_types_short_name'), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_NAME . $type_inputs_string);

		$type_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('boards_types_short_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', tep_get_boards_type_info($tInfo->boards_types_id, $languages[$i]['id'], 'boards_types_short_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_DESCRIPTION . $type_inputs_string);

		$type_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('boards_types_description[' . $languages[$i]['id'] . ']', 'soft', '30', '7', tep_get_boards_type_info($tInfo->boards_types_id, $languages[$i]['id'], 'boards_types_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_DESCRIPTION . $type_inputs_string);

        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_BOARDS) . tep_draw_input_field('boards_types_path', $tInfo->boards_types_path, 'size="' . (tep_not_null($tInfo->boards_types_path) ? strlen($tInfo->boards_types_path) - 1 : '7') . '"') . '/');

        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'));

        $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='edit_type' ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . ' <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'tID')) . (tep_not_null($tInfo->boards_types_id) ? 'tID=' . $tInfo->boards_types_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_BOARD . '</strong>');

        $contents = array('form' => tep_draw_form('boards', FILENAME_BOARDS, tep_get_all_get_params(array('action')) . 'action=move_confirm') . tep_draw_hidden_field('boards_id', $bInfo->boards_id));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $bInfo->customers_name) . '<br>' . tep_draw_pull_down_menu('move_to_board_type_id', $board_types, $tPath));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'bID')) . '&bID=' . $bInfo->boards_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('boards', FILENAME_BOARDS, tep_get_all_get_params(array('action', 'tID', 'page')) . 'tID=' . $sInfo->boards_types_id . '&action=delete_type_confirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->boards_types_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('tID', 'action', 'page')) . 'tID=' . $tInfo->boards_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'delete_app':
	  case 'delete':
		$heading[] = array('text' => '<strong>' . ($action=='delete_app' ? TEXT_INFO_HEADING_DELETE_APP : TEXT_INFO_HEADING_DELETE) . '</strong>');

		$contents = array('form' => tep_draw_form('boards', FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id . '&action=delete_confirm'));
		$contents[] = array('text' => ($action=='delete_app' ? TEXT_INFO_DELETE_APP_INTRO : TEXT_INFO_DELETE_INTRO));
		if (tep_not_null($bInfo->boards_name)) $contents[] = array('text' => '<br><strong>' . $bInfo->boards_name . '</strong>');
        if (tep_not_null($bInfo->customers_ip)) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('board_blacklist', '1', false, '', 'onclick="if (this.checked) document.getElementById(\'board_blacklist_comment\').style.display = \'block\'; else document.getElementById(\'board_blacklist_comment\').style.display = \'none\';"') . ' ' . TEXT_DELETE_BOARD_BLACKLIST . '<div id="board_blacklist_comment" style="display: none;"><br>' . TEXT_DELETE_BOARD_BLACKLIST_COMMENTS . '<br>' . tep_draw_input_field('board_blacklist_reason', TEXT_DELETE_BOARD_BLACKLIST_COMMENTS_DEFAULT, 'size="35"') . '</div>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->boards_types_name . '</strong>');

		  if (DEBUG_MODE=='on') $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action', 'tID')) . 'tID=' . $tInfo->boards_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action', 'tID')) . 'tID=' . $tInfo->boards_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br><br>');
		  if (tep_not_null($tInfo->boards_types_short_description)) $contents[] = array('text' => $tInfo->boards_types_short_description);
		  $contents[] = array('text' => '<br>' . TEXT_INFO_BOARDS_COUNT . ' ' . (int)$tInfo->boards_count);
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tInfo->last_modified));
		} elseif (isset($cInfo) && is_object($cInfo)) { // category info box contents
		  $heading[] = array('text' => '<strong>' . $cInfo->boards_categories_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->boards_categories_id . '&action=edit_category') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $tPath . '&cPath=' . $cPath . '&cID=' . $cInfo->boards_categories_id . '&action=delete_category') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
		  if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
		  $contents[] = array('text' => '<br>' . TEXT_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br>' . TEXT_BOARDS . ' ' . $cInfo->boards_count);
		  if (tep_not_null($cInfo->image) && file_exists(DIR_FS_CATALOG_IMAGES . $cInfo->image)) {
			$contents[] = array('text' => '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $cInfo->image, $cInfo->boards_categories_name) . '<br>' . $cInfo->image);
		  }
		} elseif (isset($bInfo) && is_object($bInfo)) {
		  if ($bPath > 0) {
			$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_VIEW_APP . '</strong>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id . '&action=delete_app') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('text' => $bInfo->boards_description);
			$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' '  . tep_datetime_short($bInfo->date_added));
			$contents[] = array('text' => '<br>' . TEXT_INFO_NAME . ' ' . $bInfo->customers_name);
			$contents[] = array('text' => TEXT_INFO_TELEPHONE . ' ' . $bInfo->customers_telephone);
			$contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $bInfo->customers_email_address);
			$contents[] = array('text' => TEXT_INFO_COUNTRY . ' ' . $bInfo->customers_country);
			$contents[] = array('text' => TEXT_INFO_STATE . ' ' . $bInfo->customers_state);
			$contents[] = array('text' => TEXT_INFO_CITY . ' ' . $bInfo->customers_city);
		  } else {
			$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_VIEW_ADV . '</strong>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('bID', 'action')) . 'bID=' . $bInfo->boards_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('text' => '<br><strong>' . $bInfo->boards_name . '</strong>');
			$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' '  . tep_datetime_short($bInfo->date_added));
			$contents[] = array('text' => '<br>' . TEXT_INFO_PRICE . ' ' . $currencies->format($bInfo->boards_price, true, $bInfo->boards_currency));
			$contents[] = array('text' => '<br>' . TEXT_INFO_STATUS . ' ' . $boards_statuses[$bInfo->boards_status]);
			$contents[] = array('text' => '<br>' . TEXT_INFO_APPS_COUNT . ' ' . $bInfo->apps_count);
			$contents[] = array('text' => '<br>' . TEXT_INFO_NAME . ' ' . $bInfo->customers_name);
			$contents[] = array('text' => TEXT_INFO_TELEPHONE . ' ' . $bInfo->customers_telephone);
			$contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $bInfo->customers_email_address);
			$contents[] = array('text' => TEXT_INFO_COUNTRY . ' ' . $bInfo->customers_country);
			$contents[] = array('text' => TEXT_INFO_STATE . ' ' . $bInfo->customers_state);
			$contents[] = array('text' => TEXT_INFO_CITY . ' ' . $bInfo->customers_city);
			$contents[] = array('text' => '<br>' . $bInfo->boards_description);
		  }
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
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
