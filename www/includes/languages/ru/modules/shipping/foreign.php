<?php
  if ($order->info['shops_id']==9 || SHOP_ID==9 || $order->info['shops_id']==14 || SHOP_ID==14 || $order->info['shops_id']==16 || SHOP_ID==16) {
	define('MODULE_SHIPPING_FOREIGN_TEXT_TITLE', 'Delivery by post');
	define('MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING', 'Free with an order amount over %s');
  } else {
	define('MODULE_SHIPPING_FOREIGN_TEXT_TITLE', 'Доставка почтой');
	define('MODULE_SHIPPING_FOREIGN_TEXT_FREE_SHIPPING', 'Бесплатно при сумме заказа свыше %s');
  }
  define('MODULE_SHIPPING_FOREIGN_TEXT_DESCRIPTION', 'Доставка с возможностью задания таблицы стоимости в зависимости от количества');
?>