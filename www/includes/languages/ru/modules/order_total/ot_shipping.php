<?php
  if (DEFAULT_LANGUAGE_ID==1) {
	define('MODULE_ORDER_TOTAL_SHIPPING_TITLE', 'Shipping');
	define('FREE_SHIPPING_TITLE', 'Free shipping');
	define('FREE_SHIPPING_DESCRIPTION', 'Free shipping for orders over %s');
  } else {
	define('MODULE_ORDER_TOTAL_SHIPPING_TITLE', 'Доставка');
	define('FREE_SHIPPING_TITLE', 'Бесплатная доставка');
	define('FREE_SHIPPING_DESCRIPTION', 'Бесплатная доставка для заказов свыше %s');
  }
  define('MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION', 'Стоимость доставки');
?>