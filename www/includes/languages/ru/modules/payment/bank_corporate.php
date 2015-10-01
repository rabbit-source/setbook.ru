<?php
  if (DEFAULT_LANGUAGE_ID==1) {
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', 'Invoicing (For Library Use Only)');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', '');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION_1', STORE_NAME . ' will include the invoice with the materials when they are shipped to the Library or School. If you prefer this method, this must be discussed in advance with your Account Manager. Payment is due within the timeframe discussed (usually 30 days).');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', '');
	define('MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER', 'Purchase Order:');
  } else {
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', '�� ������������ �������');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', '������ ������ �� ������������ �������');
	if (DEFAULT_CURRENCY=='UAH') {
	  define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', '��������� ��� ������:' . "\n\n" . STORE_OWNER . ' (��� ������: ' . STORE_OWNER_KPP . ')' . "\n\n" . '����������� �����: ' . STORE_OWNER_ADDRESS_CORPORATE . "\n" . '����������� �����: ' . STORE_OWNER_ADDRESS_POST . "\n" . '�/� ' . STORE_OWNER_RS . ' � ' . STORE_OWNER_BANK . ', ��� ' . STORE_OWNER_BIK . "\n\n" . '��������� ����� ��������� �� ��������:' . "\n" . '<a href="' . tep_href_link('payment/requisites.html', '', 'NONSSL', false) . '" target="_blank">' . tep_href_link('payment/requisites.html', '', 'NONSSL', false) . '</a>' . "\n\n" . '�� �������� ������� ����� ������ ������ � ���������� � �������.');
	} else {
	  define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', '�� ������ ����������� ����� ��������� �� ������ �� ��������� ��������:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '</a>');
	}
	define('MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER', '��� ���������� ����� ������ �� ������������:');
  }
?>