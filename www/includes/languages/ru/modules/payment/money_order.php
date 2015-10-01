<?php
  if ($order->info['shops_id']==9 || SHOP_ID==9 || $order->info['shops_id']==14 || SHOP_ID==14 || $order->info['shops_id']==16 || SHOP_ID==16) {
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_TITLE', 'Money order');
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_DESCRIPTION', 'Pay by money order');
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_EMAIL_FOOTER', 'Please note that the completion of your order will begin only after receipt of funds in our account' . "\n\n" . 'You can pay for your order using the following data:' . "\n\n" . STORE_OWNER . "\n" . STORE_OWNER_ADDRESS_POST . "\n" . STORE_OWNER_BANK);
  } else {
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_TITLE', 'Переводом / Money order');
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_DESCRIPTION', 'Оплата заказа денежным переводом');
	define('MODULE_PAYMENT_MONEY_ORDER_TEXT_EMAIL_FOOTER', 'Обращаем Ваше внимание на то, что комплектация Вашего заказа начнется только после поступления денежных средств на наш счет' . "\n\n" . 'Вы можете оплатить свой заказ, используя следующие данные:' . "\n\n" . STORE_OWNER . "\n" . STORE_OWNER_ADDRESS_POST . "\n" . STORE_OWNER_BANK);
  }
?>