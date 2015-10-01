<?php
  define('MODULE_PAYMENT_BANK_PRIVATE_TEXT_TITLE', 'Банковским переводом');
  define('MODULE_PAYMENT_BANK_PRIVATE_TEXT_DESCRIPTION', 'Оплата заказа через банк');
  $payment_doc_link = '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '</a>';
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
  if (DOMAIN_ZONE=='ua') {
	$bank_private_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'Получатель платежа:' . "\n" . $owner_private . "\n\n" . 'Код ЕГРПОУ:' . "\n" . $owner_kpp_private . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . $owner_rs_private . ' ' . $owner_bank_private . ', МФО ' . $owner_bik_private) . "\n\n" . 'Вы можете распечатать бланк квитанции на оплату на следующей странице:' . "\n" . $payment_doc_link;
  } elseif (DOMAIN_ZONE=='kz') {
	$bank_private_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'РНН и наименование получателя платежа:' . "\n" . trim($owner_inn_private . ', ' . $owner_private) . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . $owner_rs_private . ' ' . $owner_bank_private) . "\n\n" . 'Вы можете распечатать бланк квитанции на оплату на следующей странице:' . "\n" . $payment_doc_link;
  } elseif (DOMAIN_ZONE=='by') {
	$bank_private_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'УПН и наименование получателя платежа:' . "\n" . trim($owner_inn_private . ', ' . $owner_private) . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . $owner_rs_private . ' ' . $owner_bank_private) . "\n\n" . 'Вы можете распечатать бланк квитанции на оплату на следующей странице:' . "\n" . $payment_doc_link;
  } else {
	$bank_private_email_footer = 'Вы можете оплатить свой заказ, используя следующие реквизиты:' . "\n\n" . 'ИНН/КПП и наименование получателя платежа:' . "\n" . trim(STORE_OWNER_INN . ' / ' . STORE_OWNER_KPP . ', ' . STORE_OWNER) . "\n\n" . 'Номер счета получателя платежа и наименование банка:' . "\n" . trim('р/с ' . STORE_OWNER_RS . ' ' . STORE_OWNER_BANK . ', к/с ' . STORE_OWNER_KS) . "\n\n" . 'Банковские реквизиты получателя платежа (БИК):' . "\n" . trim(STORE_OWNER_BIK) . "\n\n" . 'Вы можете распечатать бланк квитанции на оплату на следующей странице:' . "\n" . $payment_doc_link;
  }
  define('MODULE_PAYMENT_BANK_PRIVATE_TEXT_EMAIL_FOOTER', $bank_private_email_footer . "\n\n" . 'Чтобы ускорить обработку заказа, вышлите, пожалуйста, отсканированную копию оплаченной квитанции на адрес <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>');
?>