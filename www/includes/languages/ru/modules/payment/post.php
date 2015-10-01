<?php
  define('MODULE_PAYMENT_POST_TEXT_TITLE', 'Почтовым переводом');
  define('MODULE_PAYMENT_POST_TEXT_DESCRIPTION', 'Оплата заказа посредством почтового перевода');
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

	$post_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'УНП и наименование получателя платежа:' . "\n" . trim($owner_inn_private . ', ' . $owner_private) . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . $owner_rs_private . ' ' . $owner_bank_private) . "\n\n" . 'Почтовый адрес получателя:' . "\n" . trim($owner_address_post_private);
  } else {
	$post_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'ИНН/КПП и наименование получателя платежа:' . "\n" . trim(STORE_OWNER_INN . ' / ' . STORE_OWNER_KPP . ', ' . STORE_OWNER) . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . STORE_OWNER_RS . ' ' . STORE_OWNER_BANK . ', к/с ' . STORE_OWNER_KS) . "\n\n" . 'Банковские реквизиты получателя платежа (БИК):' . "\n" . trim(STORE_OWNER_BIK) . "\n\n" . 'Почтовый адрес получателя:' . "\n" . trim(STORE_OWNER_ADDRESS_POST) . "\n\n" . 'Вы можете распечатать бланк извещения на следующей странице:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '</a>';
  }
  define('MODULE_PAYMENT_POST_TEXT_EMAIL_FOOTER', $post_email_footer . "\n\n" . 'Чтобы ускорить обработку заказа, вышлите, пожалуйста, отсканированную копию оплаченной квитанции на адрес <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>');
?>