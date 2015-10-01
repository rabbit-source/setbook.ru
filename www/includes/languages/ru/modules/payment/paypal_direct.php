<?php
  if ($order->info['shops_id']==9 || SHOP_ID==9 || $order->info['shops_id']==14 || SHOP_ID==14 || $order->info['shops_id']==16 || SHOP_ID==16) {
	define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_TITLE', 'Credit/Debit Card');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_OWNER', 'Cardholder\'s name:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_TYPE', 'Card type:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_NUMBER', 'Card number:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_EXPIRES', 'Expiration Date:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC', 'Security code:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC_INFO', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'cvv.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'cvv2\').style.display = (document.getElementById(\'cvv2\').style.display==\'block\' ? \'none\' : \'block\'); return false;">How to find the Security code</a>)' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'cvv.gif', 'CVV', '', '', 'id="cvv2" style="position: absolute; display: none; border: 1px solid black;" onclick="this.style.display = \'none\';"'));
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER', 'Issue number:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER_INFO', '(Only for Maestra and Solo cards)');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CURRENCY', 'Card currency:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_ERROR_ALL_FIELDS_REQUIRED', 'Warning: All fields required!');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS', '<br /><strong>Billing address</strong>');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_SHORT', '<br /><strong>Billing address</strong>');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_COUNTRY', 'Country:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_POSTCODE', 'ZIP / Postcode:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STATE', 'State / Province:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_CITY', 'City:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STREET', 'Street address:');
	if ($order->info['shops_id']==14 || SHOP_ID==14 || $order->info['shops_id']==16 || SHOP_ID==16) {
	  define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_DESCRIPTION', '');
	  define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_EMAIL_FOOTER', '');
	} else {
	  define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_DESCRIPTION', 'Note: Your credit card will be charged by <span class="errorText">"UBPS"</span>');
	  define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_EMAIL_FOOTER', 'Your credit card will be charged by "UBPS"');
	}
  } else {
	define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_TITLE', 'Банковской картой / Credit/Debit card');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_DESCRIPTION', 'Обратите внимание: В транзакции по данной операции будет отображена компания <span class="errorText">UBPS</span>');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_OWNER', 'Владелец карты / Cardholder\'s name:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_TYPE', 'Тип карты / Card type:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_NUMBER', 'Номер карты / Card number:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_EXPIRES', 'Срок действия карты / Valid thru:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC', 'Security code / CVV:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CVC_INFO', '(<a href="' . tep_href_link(DIR_WS_TEMPLATES_IMAGES . 'cvv.gif', '', 'SSL', false) . '" onclick="document.getElementById(\'cvv2\').style.display = (document.getElementById(\'cvv2\').style.display==\'block\' ? \'none\' : \'block\'); return false;">как найти код CVV</a>)' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'cvv.gif', 'CVV', '', '', 'id="cvv2" style="position: absolute; display: none; border: 1px solid black;" onclick="this.style.display = \'none\';"'));
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER', 'Номер выпуска / Issue number:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_ISSUE_NUMBER_INFO', '(только для карт Maestro и Solo)');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_CARD_CURRENCY', 'Валюта карты / Card currency:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_ERROR_ALL_FIELDS_REQUIRED', 'Ошибка! Для осуществления платежа необходимо заполнить все поля!');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS', '<br /><span class="errorText">В целях дополнительной защиты укажите точный адрес владельца карты / <strong>Billing address</strong></span>');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_SHORT', '<br /><strong>Billing address</strong>');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_COUNTRY', 'Страна / Country:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_POSTCODE', 'Почтовый индекс / ZIP / Postcode:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STATE', 'Штат (провинция, регион) / State (province):');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_CITY', 'Город / City:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_BILLING_ADDRESS_STREET', 'Точный адрес / Street address:');
	define('MODULE_PAYMENT_PAYPAL_DIRECT_TEXT_EMAIL_FOOTER', 'В транзакции по данной операции будет отображена компания UBPS');
  }
?>