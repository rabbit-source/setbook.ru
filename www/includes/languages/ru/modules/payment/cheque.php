<?php
  $delivery_to_country = '';
  if (tep_not_null($sendto)) {
	$country_info_query = tep_db_query("select c.countries_name from " . TABLE_COUNTRIES . " c, " . TABLE_ADDRESS_BOOK . " ab where ab.address_book_id = '" . (int)$sendto . "' and ab.entry_country_id = c.countries_id");
	$country_info = tep_db_fetch_array($country_info_query);
	$delivery_to_country = $country_info['countries_name'];
  } elseif (is_object($order)) {
	if (is_array($order->delivery['country'])) $delivery_to_country = $order->delivery['country']['title'];
	else $delivery_to_country = $order->delivery['country'];
  }
  $delivery_to_country = strtolower($delivery_to_country);

  if ($order->info['shops_id']==9 || SHOP_ID==9 || $order->info['shops_id']==14 || SHOP_ID==14 || $order->info['shops_id']==16 || SHOP_ID==16) {
	if ($delivery_to_country=='germany') {
	  define('MODULE_PAYMENT_CHEQUE_TEXT_TITLE', 'Lastschrift');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_DESCRIPTION', 'The required amount will be debited from your bank account');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE', '');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_CHECKING', 'Checking');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_SAVINGS', 'Savings');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME', 'Bankname');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER', 'Bankleitzahl (BLZ)');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER', 'Kontonummer');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER', '');
	} else {
	  define('MODULE_PAYMENT_CHEQUE_TEXT_TITLE', 'Check');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_DESCRIPTION', '');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE', 'Account type');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_CHECKING', 'Checking');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_SAVINGS', 'Savings');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME', 'Bank name');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER', 'Routing number');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER_TEXT', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'bank_check\').style.display = (document.getElementById(\'bank_check\').style.display==\'block\' ? \'none\' : \'block\'); return false;">how to find</a>)' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', 'Check example', '', '', 'id="bank_check" style="position: absolute; display: none; border: 1px solid black;" onclick="this.style.display = \'none\';"'));
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER', 'Account number');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER_TEXT', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'bank_check\').style.display = (document.getElementById(\'bank_check\').style.display==\'block\' ? \'none\' : \'block\'); return false;">how to find</a>)');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER', '<span class="errorText">Do not forget to leave an entry in your checkbook!</span>');
	}
	define('MODULE_PAYMENT_CHEQUE_ERROR_ALL_FIELDS_REQUIRED', 'Warning: All fields required!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_ERROR', 'Warning: ' . MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER . ' musts contain at least 5 digits!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_CHECK_ERROR', 'Warning: Wrong ' . MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER . '!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ACCOUNT_NUMBER_ERROR', 'Warning: ' . MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER . ' musts contain at least 7 digits!');
	define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER1', 'You can pay for your order using the following data:' . "\n\n" . STORE_OWNER . "\n" . STORE_OWNER_ADDRESS_POST . (tep_not_null($order->customer['company']) ? "\n\n" . 'You can print the payment document on the next page:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', '', 'SSL', false) . '</a>' : ''));
  } else {
	if ($delivery_to_country=='germany') {
	  define('MODULE_PAYMENT_CHEQUE_TEXT_TITLE', 'Lastschrift');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_DESCRIPTION', '����������� ����� ����� ����� � ������ ����������� �����');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE', '');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_CHECKING', 'Checking');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_SAVINGS', 'Savings');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME', '�������� ����� / Bankname');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER', '��� ����� / Bankleitzahl (BLZ)');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER', '����� ����� / Kontonummer');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER', '');
	} else {
	  define('MODULE_PAYMENT_CHEQUE_TEXT_TITLE', '����� / Check');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_DESCRIPTION', '�� �������� �������� ��������������� ������ � ����� ������� ������!');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE', '��� ����� / Account type');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_CHECKING', 'Checking');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_TYPE_SAVINGS', 'Savings');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME', '�������� ����� / Bank name');
	  define('MODULE_PAYMENT_CHEQUE_BANK_NAME_TEXT', '');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER', '��� ����� / Routing number');
	  define('MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER_TEXT', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'bank_check\').style.display = (document.getElementById(\'bank_check\').style.display==\'block\' ? \'none\' : \'block\'); return false;">��� �����</a>)' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', 'Check example', '', '', 'id="bank_check" style="position: absolute; display: none; border: 1px solid black;" onclick="this.style.display = \'none\';"'));
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER', '����� ����� / Account number');
	  define('MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER_TEXT', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'bank_check.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'bank_check\').style.display = (document.getElementById(\'bank_check\').style.display==\'block\' ? \'none\' : \'block\'); return false;">��� �����</a>)');
	  define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER', '<span class="errorText">�� �������� �������� ������ � �������� � ����� ������� ������!</span>');
	}
	define('MODULE_PAYMENT_CHEQUE_ERROR_ALL_FIELDS_REQUIRED', '������! ���������� ��������� ��� ����!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_ERROR', '������! ' . MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER . ' ������ ��������� �� ����� 5 ����!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ROUTING_NUMBER_CHECK_ERROR', '������! ������������ ' . MODULE_PAYMENT_CHEQUE_ROUTING_NUMBER . '!');
	define('MODULE_PAYMENT_CHEQUE_ERROR_ACCOUNT_NUMBER_ERROR', '������! ' . MODULE_PAYMENT_CHEQUE_ACCOUNT_NUMBER . ' ������ ��������� �� ����� 7 ����!');
	define('MODULE_PAYMENT_CHEQUE_TEXT_EMAIL_FOOTER1', '�� ������ �������� ���� �����, ��������� ��������� ������:' . "\n\n" . STORE_OWNER . "\n" . STORE_OWNER_ADDRESS_POST . (tep_not_null($order->customer['company']) ? "\n\n" . '�� ������ ����������� ����� ��������� �� ������ �� ��������� ��������:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', '', 'SSL', false) . '</a>' : ''));
  }
?>