<?php
  require('includes/application_top.php');

  $content = FILENAME_BOARDS;
  $javascript = 'boards.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_BOARDS));

  $boards_id = 0;
  $boards_types_id = 0;
  if (isset($HTTP_GET_VARS['tName'])) {
	$tname = $HTTP_GET_VARS['tName'];
	if (substr($tname, -1)=='/') $tname = substr($tname, 0, -1);
	list($type_name) = explode('/', $tname);
	if (preg_match('/(\d+)\.html$/', $tname, $regs)) {
	  $boards_id = $regs[1];
	}
	$type_info_query = tep_db_query("select * from " . TABLE_BOARDS_TYPES . " where boards_types_path = '" . tep_db_input(tep_db_prepare_input($type_name)) . "' and language_id = '" . (int)$languages_id . "'");
	$type_info = tep_db_fetch_array($type_info_query);
	$boards_types_id = $type_info['boards_types_id'];
	if ($boards_types_id > 0) {
	  $breadcrumb->add($type_info['boards_types_name'], tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types_id));
	  if ($boards_id > 0) {
		$board_check_query = tep_db_query("select count(*) as total from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "' and boards_status = '1' and boards_types_id = '" . (int)$boards_types_id . "'");
		$board_check = tep_db_fetch_array($board_check_query);
		if ($board_check['total'] < 1) {
		  tep_redirect(tep_href_link(FILENAME_ERROR_404));
		} else {
		  $HTTP_GET_VARS['boards_id'] = $boards_id;
		}
	  }
	} elseif (trim($HTTP_GET_VARS['tName'])!='') {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['tName']);
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($HTTP_GET_VARS['boards_id'])) {
	$board_info_query = tep_db_query("select boards_name from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and boards_status = '1'");
	if (tep_db_num_rows($board_info_query) > 0) {
	  $board_info = tep_db_fetch_array($board_info_query);
	  $breadcrumb->add($board_info['boards_name'], tep_href_link(FILENAME_BOARDS, 'boards_id=' . $HTTP_GET_VARS['boards_id']));
	} else {
	  $breadcrumb->add(BOARDS_ERROR_NO_BOARD_FOUND, tep_href_link(FILENAME_BOARDS, 'boards_id=' . $HTTP_GET_VARS['boards_id']));
	}
  }

  $constants = get_defined_constants();

  $boards_payments_array = array();
  $p_methods = array_map('trim', explode(',', BOARDS_ENTRY_PAYMENT_METHODS));
  reset($p_methods);
  while (list(, $p_method) = each($p_methods)) {
	$boards_payments_array[$p_method] = $constants['BOARDS_ENTRY_PAYMENT_' . strtoupper($p_method)];
  }

  $boards_shippings_array = array();
  $s_methods = array_map('trim', explode(',', BOARDS_ENTRY_SHIPPING_METHODS));
  reset($s_methods);
  while (list(, $s_method) = each($s_methods)) {
	$boards_shippings_array[$s_method] = $constants['BOARDS_ENTRY_SHIPPING_' . strtoupper($s_method)];
  }

  switch ($action) {
	case 'new':
	  $is_blacklisted = tep_check_blacklist();
	  if ($is_blacklisted) {
		$messageStack->add_session('header', strip_tags(ENTRY_BLACKLIST_BOARD_ERROR));
		tep_redirect($_SERVER['HTTP_REFERER']);
	  } elseif (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
 		if (is_object($navigation)) $navigation->set_snapshot();
		$messageStack->add_session('header', sprintf(BOARDS_ERROR_REGISTER, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')));
		tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
	  }
	  $breadcrumb->add(BOARDS_NEW_NAVBAR_TITLE, tep_href_link(FILENAME_BOARDS, tep_get_all_get_params()));
	  break;

//новая заявка
	case 'insert_reply':
	   $is_blacklisted = tep_check_blacklist();
	  if ($is_blacklisted) {
		$messageStack->add_session('header', strip_tags(ENTRY_BLACKLIST_BOARD_ERROR));
		tep_redirect($_SERVER['HTTP_REFERER']);
	  } elseif (!tep_session_is_registered('customer_id')) {
		$messageStack->add_session('header', sprintf(BOARDS_ERROR_REGISTER, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')));
		tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action'))));
	  }

	  $customers_name = tep_output_string_protected($HTTP_POST_VARS['customers_name']);
	  $customers_telephone = tep_output_string_protected($HTTP_POST_VARS['customers_telephone']);
	  $customers_email_address = tep_output_string_protected($HTTP_POST_VARS['customers_email_address']);
	  $boards_description = tep_output_string_protected($HTTP_POST_VARS['boards_description']);

	  $error = false;

	  if (strlen($customers_name) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_NAME);
	  }

	  if (strlen($customers_telephone) < 2 && strlen($customers_email_address) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_TOE);
	  }

	  if (strlen($boards_description) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_COMMENTS);
	  }

	  if ($error == false) {
		$sql_data_array = array('customers_id' => (int)$customer_id,
								'parent_id' => (int)$HTTP_GET_VARS['boards_id'],
								'customers_name' => $customers_name,
								'customers_email_address' => $customers_email_address,
								'customers_telephone' => $customers_telephone,
								'customers_ip' => tep_get_ip_address(),
								'boards_description' => $boards_description,
								'date_added' => 'now()');

		tep_db_perform(TABLE_BOARDS, $sql_data_array);

		$board_info_query = tep_db_query("select customers_name, customers_email_address from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "'");
		$board_info = tep_db_fetch_array($board_info_query);
		if (tep_not_null($board_info['customers_email_address'])) {
		  $email_subject = STORE_NAME . ' - ' . BOARDS_EMAIL_SUBJECT;
		  $enquiry = sprintf(BOARDS_EMAIL_TEXT, $board_info['customers_name'], $customers_name, $boards_description, (tep_not_null($customers_email_address) ? '<a href="mailto:' . $customers_email_address . '">' . $customers_email_address . '</a>' : BOARDS_EMAIL_TEXT_NONE), (tep_not_null($customers_telephone) ? $customers_telephone : BOARDS_EMAIL_TEXT_NONE)) . "\n\n";
		  $enquiry .= BOARDS_EMAIL_SEPARATOR . "\n" . sprintf(BOARDS_EMAIL_TEXT_FOOTER, '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'boards_id=' . $HTTP_GET_VARS['boards_id'] . '&action=view_apps', 'SSL', false) . '">' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'boards_id=' . $HTTP_GET_VARS['boards_id'] . '&action=view_apps', 'SSL', false) . '</a>');
		  $enquiry .= "\n\n";
		  tep_mail($board_info['customers_name'], $board_info['customers_email_address'], $email_subject, $enquiry, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
		}

		$messageStack->add_session('header', BOARDS_ADD_APP_SUCCESS, 'success');

		tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action'))));
	  } else {
		$action = 'new_reply';
	  }
	break;

//новое объявление
	case 'insert':
	  $is_blacklisted = tep_check_blacklist();
	  if ($is_blacklisted) {
		$messageStack->add_session('header', strip_tags(ENTRY_BLACKLIST_BOARD_ERROR));
		tep_redirect($_SERVER['HTTP_REFERER']);
	  } elseif (!tep_session_is_registered('customer_id')) {
		$messageStack->add_session('header', sprintf(BOARDS_ERROR_REGISTER, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')));
		tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action'))));
	  }

	  $customers_name = tep_output_string_protected($HTTP_POST_VARS['customers_name']);
	  $customers_telephone = tep_output_string_protected($HTTP_POST_VARS['customers_telephone']);
	  $customers_email_address = tep_output_string_protected($HTTP_POST_VARS['customers_email_address']);
	  $customers_other_contacts = tep_output_string_protected($HTTP_POST_VARS['customers_other_contacts']);
	  $customers_country = tep_output_string_protected($HTTP_POST_VARS['customers_country']);
	  $customers_state = tep_output_string_protected($HTTP_POST_VARS['customers_state']);
	  $customers_city = tep_output_string_protected($HTTP_POST_VARS['customers_city']);
	  $boards_price = str_replace(',', '.', (float)$HTTP_POST_VARS['boards_price']);
	  $boards_quantity = tep_output_string_protected($HTTP_POST_VARS['boards_quantity']);
	  $boards_name = tep_output_string_protected($HTTP_POST_VARS['boards_name']);
	  $boards_description = tep_output_string_protected($HTTP_POST_VARS['boards_description']);
	  $boards_currency = tep_output_string_protected($HTTP_POST_VARS['boards_currency']);
	  $boards_condition = tep_output_string_protected($HTTP_POST_VARS['boards_condition']);
	  $expires_day = (int)$HTTP_POST_VARS['expires_day'];
	  $expires_month = (int)$HTTP_POST_VARS['expires_month'];
	  $expires_year = (int)$HTTP_POST_VARS['expires_year'];
	  $boards_notify = (int)$HTTP_POST_VARS['boards_notify'];
	  $boards_payment_methods = tep_db_prepare_input($HTTP_POST_VARS['boards_payment_methods']);
	  $boards_shipping_methods = tep_db_prepare_input($HTTP_POST_VARS['boards_shipping_methods']);
	  $boards_share_contacts = tep_db_prepare_input($HTTP_POST_VARS['boards_share_contacts']);
	  $boards_types_id = tep_db_prepare_input($HTTP_POST_VARS['boards_types_id']);

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

	  $expires_date = '';
	  if ($HTTP_POST_VARS['boards_expires']=='1') {
		if (tep_not_null($expires_day) && tep_not_null($expires_month) && tep_not_null($expires_year)) {
		  $expires_date = $expires_year . '-' . $expires_month . '-' . $expires_day;
		  if ($expires_date < date('Y-m-d')) $expires_date = '';
		}
	  }

	  $error = false;

	  if (strlen($customers_name) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_NAME);
	  }

	  if (strlen($customers_email_address) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_EMAIL_ADDRESS);
	  } elseif (!tep_validate_email($customers_email_address)) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_EMAIL_ADDRESS_CHECK);
	  }

	  if (strlen($customers_country) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_COUNTRY);
	  }

	  if (strlen($customers_state) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_STATE);
	  }

	  if (strlen($customers_city) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_CITY);
	  }

	  if (strlen($boards_name) < 2) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_TITLE);
	  }

	  if ((float)$boards_price <= 0) {
		$error = true;
		$messageStack->add('header', BOARDS_ERROR_PRICE);
	  }

	  if ($error == false) {
		$price = str_replace(',', '.', $boards_price/$currencies->get_value($boards_currency));

		$sql_data_array = array('customers_id' => (int)$customer_id,
								'customers_name' => $customers_name,
								'customers_email_address' => $customers_email_address,
								'customers_telephone' => $customers_telephone,
								'boards_share_contacts' => $boards_share_contacts_string,
								'customers_other_contacts' => $customers_other_contacts,
								'customers_country' => $customers_country,
								'customers_state' => $customers_state,
								'customers_city' => $customers_city,
								'customers_ip' => tep_get_ip_address(),
								'boards_name' => $boards_name,
								'boards_description' => $boards_description,
								'boards_status' => '0',
								'boards_price' => $price,
								'boards_currency' => $boards_currency,
								'boards_currency_value' => $currencies->get_value($boards_currency),
								'boards_quantity' => $boards_quantity,
								'boards_condition' => $boards_condition,
								'boards_payment_method' => $boards_payment_method,
								'boards_shipping_method' => $boards_shipping_method,
								'expires_date' => $expires_date,
								'boards_notify' => $boards_notify,
								'shops_id' => SHOP_ID);

		if ($action=='update') {
		  $boards_id = (int)$HTTP_GET_VARS['boards_id'];
		  $update_sql_data = array('last_modified' => 'now()');
		  $sql_data_array = array_merge($sql_data_array, $update_sql_data);
		  tep_db_perform(TABLE_BOARDS, $sql_data_array, 'update', "boards_id = '" . (int)$boards_id . "' and customers_id = '" . (int)$customer_id . "'");
		  $messageStack->add_session('header', BOARDS_EDIT_SUCCESS, 'success');
		} elseif ($action=='insert') {
		  $insert_sql_data = array('customers_id' => (int)$customer_id,
								   'boards_types_id' => (int)$boards_types_id,
								   'date_added' => 'now()');
		  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
		  tep_db_perform(TABLE_BOARDS, $sql_data_array);
		  $boards_id = tep_db_insert_id();
		  $messageStack->add_session('header', BOARDS_ADD_SUCCESS, 'success');
		}

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
		  if (is_uploaded_file($_FILES['boards_images']['tmp_name'][$i])) {
			list($w, $h, $e) = @getimagesize($_FILES['boards_images']['tmp_name'][$i]);
			if ($e==1) $ext = '.gif';
			elseif ($e==2) $ext = '.jpg';
			elseif ($e==1) $ext = '.png';
			else $ext = '';
			if (tep_not_null($ext)) {
			  if (tep_not_null($prev_images_array[$i])) {
				$boards_image_name = basename($prev_images_array[$i]);
				@unlink($boards_images_dir . basename($prev_images_array[$i]));
				@unlink($boards_images_dir . 'big/' . basename($prev_images_array[$i]));
				@unlink($boards_images_dir . 'thumbs/' . basename($prev_images_array[$i]));
			  }
			  else $boards_image_name = substr(uniqid(rand()), 0, 10) . $ext;
			  if (tep_create_thumb($_FILES['boards_images']['tmp_name'][$i], $boards_images_dir . 'big/' . $boards_image_name, '', '750', '85', 'reduce_only')) {
				$boards_images[] = $boards_image_name;
				tep_create_thumb($boards_images_dir . 'big/' . $boards_image_name, $boards_images_dir . $boards_image_name, BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT, '85', 'reduce_only');
				tep_create_thumb($boards_images_dir . 'big/' . $boards_image_name, $boards_images_dir . 'thumbs/' . $boards_image_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '85', 'reduce_only');
			  }
			}
		  } elseif (tep_not_null($HTTP_POST_VARS['boards_existing_images'][$i])) {
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

		tep_redirect(tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action')) . 'tPath=' . $boards_types_id));
	  } else {
		$action = 'new';
	  }
	  break;
  }

  function new_tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = tep_get_countries();

	for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
	  $countries_array[] = array('id' => $countries[$i]['countries_name'], 'text' => $countries[$i]['countries_name']);
	}

	return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }

  function tep_get_boards_countries() {
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = tep_get_shops_countries(0, 1);
	reset($countries);
	while (list(, $country_info) = each($countries)) {
	  $countries_array[] = array('id' => $country_info['country_ru_name'], 'text' => $country_info['country_ru_name']);
	}

	return $countries_array;
  }

  function tep_get_all_currencies($shop_id=0) {
	global $currencies;

	$all_currencies = array();
	$all_currencies_array = array();
	$shops_currencies_query = tep_db_query("select shops_currency from " . TABLE_SHOPS . " where shops_listing_status = '1'" . ($shop_id>0 ? " and shops_id = '" . (int)$shop_id . "'" : "") . " order by sort_order");
	while ($shops_currencies = tep_db_fetch_array($shops_currencies_query)) {
	  $available_currencies = explode(',', $shops_currencies['shops_currency']);
	  reset($available_currencies);
	  while (list(, $available_currency_code) = each($available_currencies)) {
		if (!in_array($available_currency_code, $all_currencies)) $all_currencies[] = $available_currency_code;
	  }
	}
	reset($all_currencies);
	while (list(, $all_currency_code) = each($all_currencies)) {
	  $all_currencies_array[] = array('id' => $all_currency_code, 'text' => $currencies->currencies[$all_currency_code]['title']);
	}

	return $all_currencies_array;
  }

  $stopped_boards_query = tep_db_query("select boards_id, customers_id, customers_email_address, customers_name, expires_date from " . TABLE_BOARDS . " where boards_listing_status = '1' and parent_id = '0' and expires_date > '0000-00-00' and expires_date < '" . date('Y-m-d') . "'");
  while ($stopped_boards = tep_db_fetch_array($stopped_boards_query)) {
	$email_subject = STORE_NAME . ' - ' . BOARDS_EMAIL_STOPPED_SUBJECT;
	$enquiry = sprintf(BOARDS_EMAIL_STOPPED_TEXT, $stopped_boards['customers_name'], $stopped_boards['boards_name'], tep_date_long($stopped_boards['expires_date'])) . "\n\n";
	$enquiry .= BOARDS_EMAIL_SEPARATOR . "\n" . sprintf(BOARDS_EMAIL_STOPPED_TEXT_FOOTER, '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, '', 'SSL', false) . '">' . tep_href_link(FILENAME_ACCOUNT_BOARDS, '', 'SSL', false) . '</a>');
	$enquiry .= "\n\n";
	tep_mail($stopped_boards['customers_name'], $stopped_boards['customers_email_address'], $email_subject, $enquiry, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	tep_db_query("update " . TABLE_BOARDS . " set boards_listing_status = '0' where boards_id = '" . (int)$stopped_boards['boards_id'] . "'");
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>