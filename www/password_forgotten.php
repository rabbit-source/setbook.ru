<?php
  require('includes/application_top.php');

  $content = FILENAME_PASSWORD_FORGOTTEN;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process')) {
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);

    $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
    if (tep_db_num_rows($check_customer_query)) {
      $check_customer = tep_db_fetch_array($check_customer_query);

      $new_password = tep_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
      $crypted_password = tep_encrypt_password($new_password);

      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");

      tep_mail($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, sprintf(EMAIL_PASSWORD_REMINDER_SUBJECT, STORE_NAME), sprintf(EMAIL_PASSWORD_REMINDER_BODY, $REMOTE_ADDR, STORE_NAME, $new_password), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

      $messageStack->add_session('header', SUCCESS_PASSWORD_SENT, 'success');

      tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    } else {
      $messageStack->add('header', TEXT_NO_EMAIL_ADDRESS_FOUND);
    }
  }

  $login_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_LOGIN) . "' and language_id = '" . (int)$languages_id . "'");
  $login_page = tep_db_fetch_array($login_page_query);

  $breadcrumb->add($login_page['pages_name'], tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
