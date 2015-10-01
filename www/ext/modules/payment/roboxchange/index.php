<?php
  chdir('../../../../');
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');


  if (tep_not_null($HTTP_POST_VARS)) {
	$ot_total_value = $HTTP_POST_VARS['OutSum'];
	$insert_id = $HTTP_POST_VARS['InvId'];
	$crc = strtoupper($HTTP_POST_VARS['SignatureValue']);
	$payment_method = $HTTP_POST_VARS['PaymentMethod'];
	$custom = $HTTP_POST_VARS['custom'];

	// build own CRC
	$src_string = $ot_total_value . ':' . $insert_id . ':' . MODULE_PAYMENT_ROBOX_PASSWORD_1 . (tep_not_null($HTTP_POST_VARS['shp_prefix']) ? ':shp_prefix=' . $HTTP_POST_VARS['shp_prefix'] : '');
	$my_crc = strtoupper(md5($src_string));

	$src_string2 = $ot_total_value . ':' . $insert_id . ':' . MODULE_PAYMENT_ROBOX_PASSWORD_2 . (tep_not_null($HTTP_POST_VARS['shp_prefix']) ? ':shp_prefix=' . $HTTP_POST_VARS['shp_prefix'] : '');
	$my_crc2 = strtoupper(md5($src_string2));

	if (($my_crc==$crc) || ($my_crc2==$crc)) {
	  $content = FILENAME_CHECKOUT_PROCESS;

	  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
	  $page = tep_db_fetch_array($page_query);
	  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
	  while ($translation = tep_db_fetch_array($translation_query)) {
		define($translation['pages_translation_key'], $translation['pages_translation_value']);
	  }

	  if ($HTTP_POST_VARS['shp_prefix']=='aa') {
		switch ($action) {
		  case 'payment_process':
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
//			  $email_order .= "\n\n" . 'custom: ' . print_r($HTTP_POST_VARS, true);
			  tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
			}
			$fp = fopen(UPLOAD_DIR . 'payments/aa' . $insert_id . '.csv', 'w');
			fputcsv($fp, array(SHOP_ID, 'aa' . $insert_id, str_replace(',', '.', $ot_total_value)), ',');
			fclose($fp);
			echo 'OK' . $insert_id . "\n";
			tep_exit();
			break;
		  case 'payment_failed':
		  case 'payment_success':
			if ($action=='payment_success') {
			  $messageStack->add_session('header', '<p><strong>Вы успешно произвели предоплату за иностранные товары, заявка #' . $insert_id . '!</strong></p><p>Информация о зачислении средств на счет магазина должна поступить в ближайшее время.</p><p>Мы незамедлительно проинформируем вас о факте оплаты.</p>', 'success');
			} else {
			  $messageStack->add_session('header', '<p><strong>Ошибка! Предоплата не произведена!</strong></p>');
			}
			break;
		}
	  } else {
		require(DIR_WS_CLASSES . 'order.php');
		$order = new order($insert_id);

		switch ($action) {
		  case 'payment_process':
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
						   EMAIL_TEXT_INVOICE_URL . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . '">' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . '</a>' . "\n" .
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
			echo 'OK' . $insert_id . "\n";
			tep_db_query("update " . TABLE_ORDERS . " set orders_is_paid = '1' where orders_id = '" . (int)$insert_id . "'");
			tep_exit();
			break;
		  case 'payment_failed':
		  case 'payment_success':
			if ($action=='payment_success') {
			  $messageStack->add_session('header', '<p><strong>Вы успешно оплатили свой заказ #' . $insert_id . '!</strong></p><p>Информация о зачислении средств на счет магазина должна поступить в ближайшее время.</p><p>Мы незамедлительно проинформируем вас о факте оплаты.</p>', 'success');
			} else {
			  $messageStack->add_session('header', '<p><strong>Ошибка! Ваш заказ не оплачен!</strong></p><p>Вы можете найти ссылку на оплату в своем личном кабинете, в истории обработки заказа.</p>');
			}
			break;
		}
	  }
	}
  }

  tep_redirect(tep_href_link(FILENAME_DEFAULT));
?>