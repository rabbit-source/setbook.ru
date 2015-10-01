<?php
  define('MODULE_PAYMENT_FORWARD_TEXT_TITLE', 'Наложенным платежом');
  define('MODULE_PAYMENT_FORWARD_MAX_SUM_ERROR', 'Этот способ оплаты доступен только для заказов стоимостью не более %s');
  $forward_email_footer = '';
  if (DOMAIN_ZONE=='ua') $forward_email_footer = 'Внимание: Ваш заказ будет автоматически аннулирован, если наши менеджеры не смогут связаться с Вами по телефону и/или электронной почте в течение ТРЁХ рабочих дней!';
  define('MODULE_PAYMENT_FORWARD_TEXT_EMAIL_FOOTER', $forward_email_footer);
?>