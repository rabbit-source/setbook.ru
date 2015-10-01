<?php
  define('MODULE_PAYMENT_POST_TEXT_TITLE', '�������� ���������');
  define('MODULE_PAYMENT_POST_TEXT_DESCRIPTION', '������ ������ ����������� ��������� ��������');
  if (DEFAULT_CURRENCY=='BYR') {
	list($owner_private) = explode(' | ', STORE_OWNER);
	list($owner_address_post_private) = explode(' | ', STORE_OWNER_ADDRESS_POST);
	list($owner_address_corporate_private) = explode(' | ', STORE_OWNER_ADDRESS_CORPORATE);
	list($owner_inn_private) = explode(' | ', STORE_OWNER_INN);
	list($owner_kpp_private) = explode(' | ', STORE_OWNER_KPP);
	list($owner_rs_private) = explode(' | ', STORE_OWNER_RS);
	list($owner_bik_private) = explode(' | ', STORE_OWNER_BIK);
	list($owner_bank_private) = explode(' | ', STORE_OWNER_BANK);
	list($owner_ks_private) = explode(' | ', STORE_OWNER_KS);
	list($owner_general_private) = explode(' | ', STORE_OWNER_GENERAL);
	list($owner_financial_private) = explode(' | ', STORE_OWNER_FINANCIAL);

	$post_email_footer = '�� ������ �������� ���� �����, ��������� ��������� ���������:' . "\n\n" . '��� � ������������ ���������� �������:' . "\n" . trim($owner_inn_private . ', ' . $owner_private) . "\n\n" . '����� ����� ���������� ������� � ������������ �����:' . "\n" . trim('�/� ' . $owner_rs_private . ' ' . $owner_bank_private) . "\n\n" . '�������� ����� ����������:' . "\n" . trim($owner_address_post_private);
  } else {
	$post_email_footer = '�� ������ �������� ���� �����, ��������� ��������� ���������:' . "\n\n" . '���/��� � ������������ ���������� �������:' . "\n" . trim(STORE_OWNER_INN . ' / ' . STORE_OWNER_KPP . ', ' . STORE_OWNER) . "\n\n" . '����� ����� ���������� ������� � ������������ �����:' . "\n" . trim('�/� ' . STORE_OWNER_RS . ' ' . STORE_OWNER_BANK . ', �/� ' . STORE_OWNER_KS) . "\n\n" . '���������� ��������� ���������� ������� (���):' . "\n" . trim(STORE_OWNER_BIK) . "\n\n" . '�������� ����� ����������:' . "\n" . trim(STORE_OWNER_ADDRESS_POST) . "\n\n" . '�� ������ ����������� ����� ��������� �� ��������� ��������:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '</a>';
  }
  define('MODULE_PAYMENT_POST_TEXT_EMAIL_FOOTER', $post_email_footer . "\n\n" . '����� �������� ��������� ������, �������, ����������, ��������������� ����� ���������� ��������� �� ����� <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>');
?>