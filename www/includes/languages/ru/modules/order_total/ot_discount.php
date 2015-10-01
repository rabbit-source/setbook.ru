<?php
  if (DEFAULT_LANGUAGE_ID==1) {
	if (SHOP_ID==14 || SHOP_ID==16) define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE', 'InSellBooks discount');
	else define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE', 'Personal discount');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_1', 'Savings discount');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_2', 'Bulk discount');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_3', 'Quantity discount');
  } else {
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE', 'Персональная скидка');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_1', 'Накопительная скидка');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_2', 'Оптовая скидка');
	define('MODULE_ORDER_TOTAL_DISCOUNT_TITLE_3', 'Оптовая скидка');
  }
  define('MODULE_ORDER_TOTAL_DISCOUNT_DESCRIPTION', 'Персональная скидка клиента');
?>