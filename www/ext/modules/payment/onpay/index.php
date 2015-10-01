<?php
  chdir('../../../../');
  require('includes/application_top.php');

  $str = '';
  reset($HTTP_GET_VARS);
  while (list($k, $v) = each($HTTP_GET_VARS)) {
	$str .= $k . ' = ' . urldecode($v) . "\n";
  }
  $str .= "\n";
  reset($HTTP_POST_VARS);
  while (list($k, $v) = each($HTTP_POST_VARS)) {
	$str .= $k . ' = ' . urldecode($v) . "\n";
  }

  tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'onpay result processing', trim($str), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
//  die();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $payment_types = array('EGU' => 'E-Gold', 'LIE' => 'Visa & Mastercard EUR', 'LIQ' => 'Visa & Mastercard Rub', 'LIU' => 'Visa & Mastercard Ukr', 'LIZ' => 'Visa & Mastercard USD', 'MMR' => 'Moneymail.ru', 'PEU' => 'Pecunix', 'RUP' => 'RuPay', 'RUR' => 'Рублевый счет', 'USD' => 'Счет в долларах США', 'VCR' => 'Вывод на VISA', 'W05' => 'Webmoney Card 500', 'WC1' => 'Webmoney Card 1000', 'WC3' => 'Webmoney Card 3000', 'WMB' => 'Webmoney WMB', 'WME' => 'Webmoney WME', 'WMR' => 'Webmoney WMR', 'WMU' => 'Webmoney WMU', 'WMY' => 'Webmoney WMY', 'WMZ' => 'Webmoney WMZ', 'Y05' => 'Яндекс Карта 500 руб.', 'YC1' => 'Яндекс Карта 1000 руб', 'YC3' => 'Яндекс Карта 3000 руб.', 'YC5' => 'Яндекс Карта 5000 руб.', 'YCX' => 'Яндекс Карта 10000 руб.', 'YDM' => 'Яндекс.Деньги', 'YDX' => 'Яндекс.Деньги');

  if (tep_not_null($HTTP_POST_VARS)) {
	$ot_total_value = $HTTP_POST_VARS['order_amount'];
	$insert_id = $HTTP_POST_VARS['pay_for'];
	$crc = strtoupper($HTTP_POST_VARS['md5']);
	$payment_method = $payment_types[$HTTP_POST_VARS['balance_currency']];
	$custom = $HTTP_POST_VARS['custom'];

	// build own CRC
	if ($HTTP_POST_VARS['type']=='check') $src_string = 'check;' . $insert_id . ';' . $ot_total_value . ';' . $HTTP_POST_VARS['order_currency'] . ';' . MODULE_PAYMENT_ONPAY_PASSWORD1;
	else $src_string = 'pay;' . $insert_id . ';' . $HTTP_POST_VARS['onpay_id'] . ';' . $insert_id . ';' . $ot_total_value . ';' . $HTTP_POST_VARS['order_currency'] . ';0;' . MODULE_PAYMENT_ONPAY_PASSWORD1;
	$my_crc = strtoupper(md5($src_string));

	$insert_id = preg_replace('/[^\d]/', '', $insert_id);

	if ($my_crc==$crc) {
	  $content = FILENAME_CHECKOUT_PROCESS;

	  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
	  $page = tep_db_fetch_array($page_query);
	  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
	  while ($translation = tep_db_fetch_array($translation_query)) {
		define($translation['pages_translation_key'], $translation['pages_translation_value']);
	  }

	  if (substr($HTTP_POST_VARS['pay_for'], 0, 2)=='aa') {
		switch ($action) {
		  case 'payment_failed':
		  case 'payment_success':
			if ($action=='payment_success') {
			  $messageStack->add_session('header', '<p style="font-size: 12px;"><strong>Вы успешно произвели предоплату за иностранные товары, заявка #' . $insert_id . '!</strong></p><p style="font-size: 12px;">Информация о зачислении средств на счет магазина должна поступить в ближайшее время.</p><p style="font-size: 12px;">Мы незамедлительно проинформируем вас о факте оплаты.</p>', 'success');
			} else {
			  $messageStack->add_session('header', '<p style="font-size: 12px;"><strong>Ошибка! Предоплата не произведена!</strong></p>');
			}
			break;
		  case 'payment_process':
		  case '':
			if ($HTTP_POST_VARS['type']=='check') {
			  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
				   '<result>' . "\n" .
				   '<code>0</code>' . "\n" .
				   '<pay_for>' . $HTTP_POST_VARS['pay_for'] . '</pay_for>' . "\n" .
				   '<comment>OK</comment>' . "\n" .
				   '<md5>' . md5('check;' . $HTTP_POST_VARS['pay_for'] . ';' . $ot_total_value . ';' . $HTTP_POST_VARS['order_currency'] . ';0;' . MODULE_PAYMENT_ONPAY_PASSWORD1) . '</md5>' . "\n" .
				   '</result>';
			} elseif ($HTTP_POST_VARS['type']=='pay') {
			  $order_info_query = tep_db_query("select * from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$insert_id . "'");
			  $order_info = tep_db_fetch_array($order_info_query);
			  $customers_name = $order_info['customers_name'];
			  $customers_email_address = $order_info['customers_email_address'];
			  $date_purchased = $order_info['date_purchased'];

			  $order_status_info_query = tep_db_query("select advance_orders_status_id from " . TABLE_ADVANCE_ORDERS_STATUS_HISTORY . " where advance_orders_id = '" . (int)$insert_id . "' order by date_added desc limit 1");
			  if (tep_db_num_rows($order_status_info_query) < 1) $order_status_info_query = tep_db_query("select advance_orders_status as advance_orders_status_id from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$insert_id . "'");
			  $order_status_info = tep_db_fetch_array($order_status_info_query);
			  $order_status = $order_status_info['advance_orders_status_id'];
			  if ((int)$order_status==0) $order_status = DEFAULT_ORDERS_STATUS_ID;
			  $sql_data_array = array('advance_orders_id' => $insert_id,
									  'advance_orders_status_id' => $order_status,
									  'date_added' => 'now()',
									  'customer_notified' => '1',
									  'comments' => 'Заявка успешно оплачена' . (tep_not_null($payment_method) ? ', выбранный способ оплаты - ' . $payment_method : '') . ', сумма - ' . $currencies->format($ot_total_value),
									  'operator' => 'robot');
			  tep_db_perform(TABLE_ADVANCE_ORDERS_STATUS_HISTORY, $sql_data_array);

			  $email_subject = STORE_NAME . ' - Поступление предоплаты за иностранные товары, заявка #' . $insert_id;
			  $email_order = STORE_NAME . "\n" . 
							 EMAIL_SEPARATOR . "\n" . 
							 'Номер заявки: ' . $insert_id . "\n" .
							 'Дата заявки: ' . tep_date_long($date_purchased) . "\n" .
							 "\n" . EMAIL_TEXT_PAYMENT_METHOD . "\n" . 
							 EMAIL_SEPARATOR . "\n" .
							 $payment_method . "\n\n" .
							 'Сумма в размере ' . $currencies->format($ot_total_value) . ' успешно зачислена на счет магазина';
			  tep_mail($customers_name, $customers_email_address, $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
// send emails to other people
			  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
				tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
			  }
			  $fp = fopen(UPLOAD_DIR . 'payments/aa' . $insert_id . '.csv', 'w');
			  fputcsv($fp, array(SHOP_ID, 'aa' . $insert_id, str_replace(',', '.', $ot_total_value)), ',');
			  fclose($fp);
//			  echo 'OK' . $insert_id . "\n";
			}
			tep_exit();
			break;
		}
	  } else {
		require(DIR_WS_CLASSES . 'order.php');
		$order = new order($insert_id);

		switch ($action) {
		  case 'payment_failed':
		  case 'payment_success':
			if ($action=='payment_success') {
			  $messageStack->add_session('header', '<p style="font-size: 12px;"><strong>Вы успешно оплатили свой заказ #' . $insert_id . '!</strong></p><p style="font-size: 12px;">Информация о зачислении средств на счет магазина должна поступить в ближайшее время.</p><p style="font-size: 12px;">Мы незамедлительно проинформируем вас о факте оплаты.</p>', 'success');
			} else {
			  $messageStack->add_session('header', '<p style="font-size: 12px;"><strong>Ошибка! Ваш заказ не оплачен!</strong></p><p style="font-size: 12px;">Вы можете найти ссылку на оплату в своем личном кабинете, в истории обработки заказа.</p>');
			}
			break;
		  case 'payment_process':
		  case '':
			if ($HTTP_POST_VARS['type']=='check') {
			  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
				   '<result>' . "\n" .
				   '<code>0</code>' . "\n" .
				   '<pay_for>' . $HTTP_POST_VARS['pay_for'] . '</pay_for>' . "\n" .
				   '<comment>OK</comment>' . "\n" .
				   '<md5>' . md5('check;' . $HTTP_POST_VARS['pay_for'] . ';' . $ot_total_value . ';' . $order->info['currency'] . ';0;' . MODULE_PAYMENT_ONPAY_PASSWORD1) . '</md5>' . "\n" .
				   '</result>' . "\n";
			} elseif ($HTTP_POST_VARS['type']=='pay') {
			  $order_status_info_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$insert_id . "' order by date_added desc limit 1");
			  $order_status_info = tep_db_fetch_array($order_status_info_query);
			  $sql_data_array = array('orders_id' => $insert_id,
									  'orders_status_id' => $order_status_info['orders_status_id'],
									  'date_added' => 'now()',
									  'customer_notified' => '1',
									  'comments' => 'Заказ успешно оплачен' . (tep_not_null($payment_method) ? ', выбранный способ оплаты - ' . $payment_method : '') . ', сумма - ' . $currencies->format($ot_total_value),
									  'operator' => 'robot');
			  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
			  $email_subject = STORE_NAME . ' - Поступление оплаты за заказ #' . $insert_id;
			  $email_order = STORE_NAME . "\n" . 
							 EMAIL_SEPARATOR . "\n" . 
							 EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
							 EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
							 EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($order->info['date_purchased']) . "\n" .
							 "\n" . EMAIL_TEXT_PAYMENT_METHOD . "\n" . 
							 EMAIL_SEPARATOR . "\n" .
							 $payment_method . "\n\n" .
							 'Сумма в размере ' . $currencies->format($ot_total_value) . ' успешно зачислена на счет магазина';
			  tep_mail($order->customer['name'], $order->customer['email_address'], $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
// send emails to other people
			  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
				tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
			  }
			  $fp = fopen(UPLOAD_DIR . 'payments/' . $insert_id . '.csv', 'w');
			  fputcsv($fp, array(SHOP_ID, $insert_id, str_replace(',', '.', $ot_total_value)), ',');
			  fclose($fp);
//			  echo 'OK' . $insert_id . "\n";
			}
			tep_exit();
			break;
		}
	  }
	}
  }

  tep_redirect(tep_href_link(FILENAME_DEFAULT));
?>