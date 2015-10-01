<?php
  if (DEFAULT_LANGUAGE_ID==1) {
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', 'Invoicing (For Library Use Only)');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', '');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION_1', STORE_NAME . ' will include the invoice with the materials when they are shipped to the Library or School. If you prefer this method, this must be discussed in advance with your Account Manager. Payment is due within the timeframe discussed (usually 30 days).');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', '');
	define('MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER', 'Purchase Order:');
  } else {
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_TITLE', 'По безналичному расчету');
	define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_DESCRIPTION', 'Оплата заказа по безналичному расчету');
	if (DEFAULT_CURRENCY=='UAH') {
	  define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', 'Реквизиты для оплаты:' . "\n\n" . STORE_OWNER . ' (Код ЕГРПОУ: ' . STORE_OWNER_KPP . ')' . "\n\n" . 'Юридический адрес: ' . STORE_OWNER_ADDRESS_CORPORATE . "\n" . 'Фактический адрес: ' . STORE_OWNER_ADDRESS_POST . "\n" . 'Р/с ' . STORE_OWNER_RS . ' в ' . STORE_OWNER_BANK . ', МФО ' . STORE_OWNER_BIK . "\n\n" . 'Подробнее можно прочитать на странице:' . "\n" . '<a href="' . tep_href_link('payment/requisites.html', '', 'NONSSL', false) . '" target="_blank">' . tep_href_link('payment/requisites.html', '', 'NONSSL', false) . '</a>' . "\n\n" . 'Не забудьте указать номер своего заказа в примечании к платежу.');
	} else {
	  define('MODULE_PAYMENT_BANK_CORPORATE_TEXT_EMAIL_FOOTER', 'Вы можете распечатать бланк документа на оплату на следующей странице:' . "\n" . '<a href="' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '" target="_blank">' . tep_href_link('advice.php', 'order_id=[order_id]', 'SSL', false) . '</a>');
	}
	define('MODULE_PAYMENT_BANK_CORPORATE_PURCHASE_ORDER', 'Ваш внутренний номер заявки на приобретение:');
  }
?>