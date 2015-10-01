<?php
  echo '<div id="account">' . "\n" .
  '  <div class="inner">' . "\n" .
  '    <div class="account_title"><a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_ACCOUNT . '</a></div>' . "\n";
  if (tep_session_is_registered('customer_id') && !$is_dummy_account) {
	$customer_discount_value = 0;
	if (MODULE_ORDER_TOTAL_DISCOUNT_STATUS=='true') {
	  $customer_discount_info_query = tep_db_query("select customers_discount, customers_discount_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_discount_info = tep_db_fetch_array($customer_discount_info_query);
	  if ($customer_discount_info['customers_discount_type']=='discount') $customer_discount_value = (float)$customer_discount_info['customers_discount'];
	}

	if (strpos(HTTP_SERVER, 'owl') || strpos(HTTP_SERVER, 'insell')) {
	  echo '    <div class="contents">Welcome, ' . $customer_first_name . '!&nbsp; | &nbsp;<a href="' . tep_href_link(FILENAME_LOGOFF, '', 'SSL') . '">' . HEADER_TITLE_ACCOUNT_LOGOFF_OWL . '</a></div>' . "\n";
	} else {
	  echo '    <div class="contents">' . $customer_first_name . ' ' . $customer_last_name . (tep_not_null($customer_company) ? '<br />' . "\n" . tep_output_string_protected($customer_company) : '') . ($customer_discount_value>0 ? '<br />' . "\n" . HEADER_TITLE_ACCOUNT_DISCOUNT . ' ' . $customer_discount_value . '%' : '') . '</div>' . "\n" .
	'    <div class="register"><a href="' . tep_href_link(FILENAME_LOGOFF, '', 'SSL') . '">' . HEADER_TITLE_ACCOUNT_LOGOFF . '</a></div>' . "\n";
	}
  } else {
	echo '    <div class="contents"><a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . HEADER_TITLE_ACCOUNT_LOGIN . '</a>&nbsp; | &nbsp;<a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_ACCOUNT_REGISTER . '</a></div>' . "\n";
  }
  echo '  </div>' . "\n" .
  '</div>' . "\n";
?>