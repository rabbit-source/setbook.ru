<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    if (is_object($navigation)) $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = FILENAME_ACCOUNT_BOARDS;
  $javascript = 'boards.js.php';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename(FILENAME_BOARDS)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ACCOUNT_BOARDS, '', 'SSL'));

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

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  switch($action) {

//страница за€вок
	case 'view_apps':
	  $breadcrumb->add(BOARDS_VIEW_APPS_NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=view_apps', 'SSL'));
	  break;

	case 'edit':
	  $breadcrumb->add(BOARDS_EDIT_NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(), 'SSL'));
	  break;

	case 'new':
	  $breadcrumb->add(BOARDS_NEW_NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(), 'SSL'));
	  break;

//новое объ€вление
	case 'insert':
	case 'update':
	  if (!tep_session_is_registered('customer_id')) {
		$messageStack->add_session('header', sprintf(BOARDS_ERROR_REGISTER, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')));
		tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action'))));
	  }
	  if ($action=='update') $boards_id = (int)$HTTP_GET_VARS['boards_id'];

	  $breadcrumb->add(($action=='update' ? BOARDS_EDIT_NAVBAR_TITLE : BOARDS_NEW_NAVBAR_TITLE), tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(), 'SSL'));

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
		$boards_currency_value = $currencies->get_value($boards_currency);
		if ($action=='update') {
		  $currency_check_query = tep_db_query("select boards_currency, boards_currency_value from " . TABLE_BOARDS . " where boards_id = '" . (int)$boards_id . "'");
		  $currency_check = tep_db_fetch_array($currency_check_query);
		  if ($currency_check['boards_currency']==$boards_currency) {
			$boards_currency_value = $currency_check['boards_currency_value'];
		  }
		} else {
		}
		$price = str_replace(',', '.', $boards_price/$boards_currency_value);

		$sql_data_array = array('customers_id' => (int)$customer_id,
								'customers_name' => $customers_name,
								'customers_email_address' => $customers_email_address,
								'customers_telephone' => $customers_telephone,
								'boards_share_contacts' => $boards_share_contacts_string,
								'customers_other_contacts' => $customers_other_contacts,
								'customers_country' => $customers_country,
								'customers_state' => $customers_state,
								'customers_city' => $customers_city,
								'boards_name' => $boards_name,
								'boards_description' => $boards_description,
								'boards_status' => '0',
								'boards_price' => $price,
								'boards_currency' => $boards_currency,
								'boards_currency_value' => $boards_currency_value,
								'boards_quantity' => $boards_quantity,
								'boards_condition' => $boards_condition,
								'boards_payment_method' => $boards_payment_method,
								'boards_shipping_method' => $boards_shipping_method,
								'expires_date' => $expires_date,
								'boards_notify' => $boards_notify,
								'shops_id' => SHOP_ID);

		if ($action=='update') {
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

		tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'edit')), 'SSL'));
	  } else {
		$action = ($action=='update' ? 'edit' : 'new');
	  }
	  break;

//удаление объ€влени€
	case 'delete':
	  if (isset($HTTP_GET_VARS['boards_id'])) {
		$adv_check_query = tep_db_query("select boards_id from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and customers_id = '" . (int)$customer_id . "'");
		if (tep_db_num_rows($adv_check_query) > 0) {
		  $adv_check = tep_db_fetch_array($adv_check_query);
		  tep_db_query("delete from " . TABLE_BOARDS . " where parent_id = '" . (int)$adv_check['boards_id'] . "'");
		  tep_db_query("delete from " . TABLE_BOARDS . " where boards_id = '" . (int)$adv_check['boards_id'] . "'");

		  $messageStack->add_session('header', BOARDS_DELETE_SUCCESS, 'success');
		}
	  }

	  tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL'));
	  break;

//присвоение объ€влению статус продано
	case 'sold':
	  if (isset($HTTP_GET_VARS['boards_id'])) {
		$adv_check_query = tep_db_query("select boards_id from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and customers_id = '" . (int)$customer_id . "'");
		if (tep_db_num_rows($adv_check_query) > 0) {
		  $adv_check = tep_db_fetch_array($adv_check_query);
		  tep_db_query("update " . TABLE_BOARDS . " set boards_status = '3', last_modified = now() where boards_id = '" . (int)$adv_check['boards_id'] . "'");

		  $messageStack->add_session('header', BOARDS_SOLD_SUCCESS, 'success');
		}

		tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL'));
	  }
	  break;

//приостановление размещени€ объ€влени€
	case 'stop':
	  if (isset($HTTP_GET_VARS['boards_id'])) {
		$adv_check_query = tep_db_query("select boards_id from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and customers_id = '" . (int)$customer_id . "'");
		if (tep_db_num_rows($adv_check_query) > 0) {
		  $adv_check = tep_db_fetch_array($adv_check_query);
		  tep_db_query("update " . TABLE_BOARDS . " set boards_listing_status = '0' where boards_id = '" . (int)$adv_check['boards_id'] . "'");

		  $messageStack->add_session('header', BOARDS_STOP_SUCCESS, 'success');
		}

		tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL'));
	  }
	  break;

//возобновление размещени€ объ€влени€
	case 'resume':
	  if (isset($HTTP_GET_VARS['boards_id'])) {
		$adv_check_query = tep_db_query("select boards_id from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and customers_id = '" . (int)$customer_id . "'");
		if (tep_db_num_rows($adv_check_query) > 0) {
		  $adv_check = tep_db_fetch_array($adv_check_query);
		  tep_db_query("update " . TABLE_BOARDS . " set boards_listing_status = '1' where boards_id = '" . (int)$adv_check['boards_id'] . "'");

		  $messageStack->add_session('header', BOARDS_RESUME_SUCCESS, 'success');
		}

		tep_redirect(tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL'));
	  }
	  break;
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

  function tep_get_all_currencies($shop_id) {
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