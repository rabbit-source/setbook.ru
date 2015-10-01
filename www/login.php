<?php
  require('includes/application_top.php');

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
  if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  $content = FILENAME_LOGIN;
  if (sizeof($navigation->snapshot) < 1) {
	if (sizeof($navigation->path) < 2) $back = 0;
	else $back = 1;
	if (is_object($navigation)) $navigation->set_path_as_snapshot($back);
  }

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (!$dummy_customers_check_time = get_cfg_var('session.gc_maxlifetime')) {
	$dummy_customers_check_time = 1440;
  }
  $dummy_customers_query = tep_db_query("select c.customers_id from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci where c.customers_id = ci.customers_info_id and c.customers_is_dummy_account = '1' and ci.customers_info_date_account_created < '" . date('Y-m-d H:i:s', time()-$dummy_customers_check_time) . "'");
  while ($dummy_customers = tep_db_fetch_array($dummy_customers_query)) {
	tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$dummy_customers['customers_id'] . "'");
	tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$dummy_customers['customers_id'] . "'");
	tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int)$dummy_customers['customers_id'] . "'");
	tep_db_query("update " . TABLE_ORDERS . " set customers_id = '0' where customers_id = '" . (int)$dummy_customers['customers_id'] . "'");
	tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$dummy_customers['customers_id'] . "'");
	tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$dummy_customers['customers_id'] . "'");
  }

  $error = false;
  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'repeat')) {
	$email_address = tep_db_prepare_input($HTTP_GET_VARS['email']);
    $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_lastname, customers_password, customers_email_address, customers_email_address_confirmed from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_is_dummy_account = '0'");
    if (!tep_db_num_rows($check_customer_query)) {
	  $messageStack->add_session('header', TEXT_ACCOUNT_DOESNT_EXIST_ERROR);
    } else {
      $check_customer = tep_db_fetch_array($check_customer_query);
	  if ($check_customer['customers_email_address_confirmed']=='1') {
		$messageStack->add_session('header', TEXT_ACCOUNT_ALREADY_CONFIRMED_ERROR);
	  } else {
		list($activation_key) = explode(':', $check_customer['customers_password']);
		$email_subject = sprintf(EMAIL_SUBJECT_BEFORE, STORE_NAME);
		$email_text = sprintf(EMAIL_GREET_NONE, $check_customer['customers_firstname']) . "\n\n" . sprintf(EMAIL_WELCOME_BEFORE, STORE_NAME) . "\n\n" . EMAIL_TEXT_BEFORE . "\n\n\n" . sprintf(EMAIL_CONTACT, STORE_OWNER_EMAIL_ADDRESS);
		$email_text = str_replace(array('{{store_name}}', '{{email_address}}', '{{confirmation_link}}'), array(STORE_NAME, $email_address, tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, 'email=' . urlencode($email_address) . '&key=' . urlencode($activation_key), 'SSL', false)), $email_text);
		tep_mail($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, $email_subject, $email_text, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
		$messageStack->add_session('header', sprintf(TEXT_LETTER_REPEAT_SUCCESS, $email_address), 'success');
	  }
	}
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  } elseif (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process')) {
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);
    $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

// Check if email exists
    $check_customer_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_password, c.customers_email_address, c.customers_email_address_confirmed, c.customers_default_address_id, c.customers_type, c.customers_status, co.companies_name, co.companies_corporate from " . TABLE_CUSTOMERS . " c left join " . TABLE_COMPANIES . " co on (c.customers_id = co.customers_id) where c.customers_email_address = '" . tep_db_input($email_address) . "' and customers_is_dummy_account = '0'");
    if (!tep_db_num_rows($check_customer_query)) {
      $error = true;
	  $error_message = TEXT_ACCOUNT_DOESNT_EXIST_ERROR;
    } else {
      $check_customer = tep_db_fetch_array($check_customer_query);
	  if ($check_customer['customers_email_address_confirmed']=='0') {
		$error = true;
		$error_message = sprintf(TEXT_ACCOUNT_NOT_CONFIRMED_ERROR, tep_href_link(FILENAME_LOGIN, 'action=repeat&email=' . urlencode($email_address), 'SSL'));
// Check that password is good
      } elseif (tep_validate_password($password, $check_customer['customers_password']) || (tep_not_null(ACCOUNT_UNIVERSAL_PASSWORD) && $password==ACCOUNT_UNIVERSAL_PASSWORD) ) {
		$check_country_query = tep_db_query("(select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ")) union (select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id <> '" . (int)$check_customer['customers_default_address_id'] . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ") order by address_book_id desc) order by '" . (int)$check_customer['customers_default_address_id'] . "'");
		$check_country = tep_db_fetch_array($check_country_query);

        $customer_id = $check_customer['customers_id'];
        $customer_type = $check_customer['customers_type'];
        if (ACCOUNT_MIDDLE_NAME == 'true') {
		  list($customer_first_name, $customer_middle_name) = explode(' ', trim($check_customer['customers_firstname']));
		} else {
		  $customer_first_name = $check_customer['customers_firstname'];
		  $customer_middle_name = '';
		}
        $customer_last_name = $check_customer['customers_lastname'];
        $customer_status = $check_customer['customers_status'];
        $customer_company = $check_customer['companies_name'];
        $customer_corporate = $check_customer['companies_corporate'];
        $customer_default_address_id = $check_country['address_book_id'];
        $customer_country_id = $check_country['entry_country_id'];
        $customer_zone_id = $check_country['entry_zone_id'];
        tep_session_register('customer_id');
        tep_session_register('customer_default_address_id');
        tep_session_register('customer_first_name');
        tep_session_register('customer_middle_name');
        tep_session_register('customer_last_name');
        tep_session_register('customer_status');
        tep_session_register('customer_company');
        tep_session_register('customer_corporate');
        tep_session_register('customer_type');
        tep_session_register('customer_country_id');
        tep_session_register('customer_zone_id');
		tep_session_unregister('is_dummy_account');

		if ($HTTP_POST_VARS['remember_me']=='1') {
		  $pass_info_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		  $pass_info = tep_db_fetch_array($pass_info_query);
		  tep_setcookie('remember_customer', $pass_info['customers_password'] . '||' . $customer_id, time()+60*60*24*365);
		}

        tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
        tep_db_query("update " . TABLE_CUSTOMERS . " set shops_id = '" . (int)SHOP_ID . "' where customers_id = '" . (int)$customer_id . "' and shops_id = '0'");

// restore cart contents
        $cart->restore_contents();

// restore postpone cart contents
        $postpone_cart->restore_contents();

// restore foreign cart contents
        $foreign_cart->restore_contents();

		if (MODULE_ORDER_TOTAL_INSTALLED) {
		  require(DIR_WS_CLASSES . 'order.php');
		  $order = new order;

		  require(DIR_WS_CLASSES . 'order_total.php');
		  $order_total_modules = new order_total;
		  $order_total_modules->process();
		}

// список страниц, на которые не должен перенаправиться пользователь после авторизации
		$disabled_pages = array(FILENAME_LOGIN,
								FILENAME_LOGOFF, 
								FILENAME_CREATE_ACCOUNT, 
								FILENAME_CREATE_ACCOUNT_SUCCESS, 
								FILENAME_PASSWORD_FORGOTTEN, 
								FILENAME_POPUP_SHOPPING_CART,
								FILENAME_LOADER,
								FILENAME_ERROR_404);

        if (sizeof($navigation->snapshot) > 0) {
		  $origin_href = ($navigation->snapshot['mode']=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . $navigation->snapshot['page'] . (tep_not_null(tep_array_to_string($navigation->snapshot['get'])) ? '?' . tep_array_to_string($navigation->snapshot['get']) : '');
		  if (in_array(basename($navigation->snapshot['page']), $disabled_pages)) {
			$origin_href = '';
			$navigation_path = $navigation->path;
			for ($i=sizeof($navigation_path)-1; $i>=0; $i--) {
			  if (!in_array(basename($navigation_path[$i]['page']), $disabled_pages)) {
				$origin_href = ($navigation_path[$i]['mode']=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . $navigation_path[$i]['page'] . (tep_not_null(tep_array_to_string($navigation_path[$i]['get'])) ? '?' . tep_array_to_string($navigation_path[$i]['get']) : '');
				break;
			  }
			}
			if (empty($origin_href)) $origin_href = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');
		  }
          $navigation->clear_snapshot();
          tep_redirect($origin_href);
        } else {
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }
	  } else {
        $error = true;
		$error_message = TEXT_WRONG_PASSWORD_ERROR;
      }
    }
  }

  if ($error == true) {
	if (empty($error_message)) $error_message = TEXT_LOGIN_ERROR;
    $messageStack->add('header', $error_message);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_LOGIN, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>