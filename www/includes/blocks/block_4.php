<?php
  if (basename(PHP_SELF)=='contacts.html') {
	$boxHeading = ENTRY_CONTACT_US_TITLE;
	$boxContent = '';
	if (isset($HTTP_GET_VARS['action']) && $HTTP_GET_VARS['action']=='success') {
	  $boxContent .= '<p>' . nl2br(tep_output_string_protected(ENTRY_CONTACT_US_SUCCESS)) . '</p>';
	} else {
	  if (strpos(REQUEST_URI, 'action')!==false) $link = preg_replace('/action=[^\&]*/i', 'action=send', REQUEST_URI);
	  elseif (strpos(REQUEST_URI, '?')!==false) $link = REQUEST_URI . '&amp;action=send';
	  else $link = REQUEST_URI . '?action=send';

	  $customer_default_email = '';
	  $customer_phone_number = '';
	  if (tep_session_is_registered('customer_id') && !$is_dummy_account) {
		$customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_info = tep_db_fetch_array($customer_info_query);
		$customer_default_email = $customer_info['customers_email_address'];

		$phone_info_query = tep_db_query("select entry_telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "'");
		$phone_info = tep_db_fetch_array($phone_info_query);
		$customer_phone_number = $phone_info['entry_telephone'];
	  }

	  $subjects_array = array();
	  $subjects_query = tep_db_query("select subjects_id, subjects_name from " . TABLE_SUBJECTS . " where language_id = '" . (int)$languages_id . "' and status = '1' order by sort_order, subjects_name");
	  while ($subjects = tep_db_fetch_array($subjects_query)) {
		if (tep_not_null($subjects['subjects_name'])) $subjects_array[] = array('id' => $subjects['subjects_id'], 'text' => $subjects['subjects_name']);
	  }

	  $is_blacklisted = tep_check_blacklist();

	  if ($is_blacklisted) {
		$boxContent = ENTRY_BLACKLIST_CONTACT_US_ERROR;
	  } else {
		ob_start();
?>
<?php
		$boxContent .= ob_get_clean();
	  }
	}
	include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
  }
?>