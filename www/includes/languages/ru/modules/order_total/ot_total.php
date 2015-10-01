<?php
  if (DEFAULT_LANGUAGE_ID==1) {
	define('MODULE_ORDER_TOTAL_TOTAL_TITLE', 'Total');
	define('MODULE_ORDER_TOTAL_TOTAL_TITLE_ADD', '(excluding delivery cost)');
  } else {
	define('MODULE_ORDER_TOTAL_TOTAL_TITLE', 'Итого');
	define('MODULE_ORDER_TOTAL_TOTAL_TITLE_ADD', '(без учета стоимости доставки)');
  }
  define('MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION', 'Итого стоимость заказа');
?>