<?php
  echo $page['pages_description'];

  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  if (is_array($payment_modules->modules)) {
	reset($payment_modules->modules);
	while (list(, $payment_row) = each($payment_modules->modules)) {
	  $payment = substr($payment_row, 0, strrpos($payment_row, '.'));
	  if (is_object($$payment)) {
		$payment_class = $$payment;
		if ($payment_class->title==$payment_method) {
		  if ($payment_class->email_footer) {
			echo '<p>' . nl2br(str_replace('[order_id]', $last_order, $payment_class->email_footer)) . '</p>';
		  }
		}
	  }
	}
  }

  if (tep_session_is_registered('rx_code')) {
	$code = $rx_code;
	list(, $c_id, $order_id, $total_value) = explode('-', base64_decode($code));
	$total_value = str_replace(',', '.', round($total_value, 2));
	tep_session_unregister('rx_code');

	require(DIR_WS_CLASSES . 'order.php');
	$order = new order($last_order);

	$ot_total_value = 0;
	reset($order->totals);
	while (list(, $ot) = each($order->totals)) {
	  if ($ot['class']=='ot_total') {
		$ot_total_value = str_replace(',', '.', round($ot['value'], $currencies->get_decimal_places($currency)));
		break;
	  }
	}

	$robox_currency = DEFAULT_CURRENCY;
	if ($robox_currency == 'KZT')
		$robox_currency = '';

	if ($c_id==$customer_id && $order_id==$last_order && $total_value==$ot_total_value) {
	  $sign = md5(MODULE_PAYMENT_ROBOX_LOGIN . ':' . $ot_total_value . ':' . $order_id . ':' . MODULE_PAYMENT_ROBOX_PASSWORD_1);
	  echo '<br /><p>' . MODULE_PAYMENT_ROBOX_TEXT_DESCRIPTION_2 . '</p>' .
		   tep_draw_form('merchant', (MODULE_PAYMENT_ROBOX_MODE=='Test' ? 'http://test.robokassa.ru/Index.aspx' : 'https://merchant.roboxchange.com/Index.aspx')) .
		   tep_draw_hidden_field('MrchLogin',		MODULE_PAYMENT_ROBOX_LOGIN) .
		   tep_draw_hidden_field('OutSum',			$ot_total_value) .
		   tep_draw_hidden_field('InvId',			$order_id) .
		   tep_draw_hidden_field('Desc',			'Оплата заказа #' . $order_id . ' в магазине ' . STORE_NAME) .
		   tep_draw_hidden_field('SignatureValue',	$sign) .
//		   tep_draw_hidden_field('IncCurrLabel',	DEFAULT_CURRENCY) .
		   tep_draw_hidden_field('IncCurrLabel',	$robox_currency) .
		   tep_draw_hidden_field('Culture',			'ru') .
//		   tep_draw_hidden_field('custom',			$code) .
		   tep_image_submit('button_pay_for_order.gif', IMAGE_BUTTON_PAY_FOR_ORDER) .
		   '</form>';
	}
  } elseif (tep_session_is_registered('ik_code')) {
	$code = $ik_code;
	list(, $c_id, $order_id, $total_value) = explode('-', base64_decode($code));
	$total_value = str_replace(',', '.', round($total_value, 2));
	tep_session_unregister('ik_code');

	require(DIR_WS_CLASSES . 'order.php');
	$order = new order($last_order);

	$ot_total_value = 0;
	reset($order->totals);
	while (list(, $ot) = each($order->totals)) {
	  if ($ot['class']=='ot_total') {
		$ot_total_value = $ot['value'];
		break;
	  }
	}
	$ot_total_value = round($ot_total_value * $currencies->get_value(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY), $currencies->get_decimal_places(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY));
	$ot_total_value = str_replace(',', '.', $ot_total_value);

	if ($c_id==$customer_id && $order_id==$last_order && $total_value==$ot_total_value) {
	  $sign = md5(MODULE_PAYMENT_INTERKASSA_LOGIN . ':' . $ot_total_value . ':' . $order_id . ':' . '' . ':' . tep_session_id() . ':' . MODULE_PAYMENT_INTERKASSA_PASSWORD);
	  echo '<br /><p>' . MODULE_PAYMENT_INTERKASSA_TEXT_DESCRIPTION_2 . '</p>' .
		   tep_draw_form('merchant', (MODULE_PAYMENT_INTERKASSA_MODE=='Test' ? 'https://test.interkassa.com/lib/payment.php' : 'https://interkassa.com/lib/payment.php')) .
		   tep_draw_hidden_field('ik_shop_id',			MODULE_PAYMENT_INTERKASSA_LOGIN) .
		   tep_draw_hidden_field('ik_payment_amount',	$ot_total_value) .
		   tep_draw_hidden_field('ik_payment_id',		$order_id) .
		   tep_draw_hidden_field('ik_payment_desc',		'Оплата заказа #' . $order_id . ' в магазине ' . STORE_NAME) .
		   tep_draw_hidden_field('ik_paysystem_alias',	'') .
		   tep_draw_hidden_field('ik_baggage_fields',	tep_session_id()) .
		   tep_draw_hidden_field('ik_sign_hash',		$sign) .
		   tep_image_submit('button_pay_for_order.gif', IMAGE_BUTTON_PAY_FOR_ORDER) .
		   '</form>';
	}
  } elseif (tep_session_is_registered('op_code')) {
	$code = $op_code;
	list(, $c_id, $order_id, $total_value) = explode('-', base64_decode($code));
	$total_value = str_replace(',', '.', round($total_value, 2));
//	tep_session_unregister('op_code');

	require(DIR_WS_CLASSES . 'order.php');
	$order = new order($last_order);

	$ot_total_value = 0;
	reset($order->totals);
	while (list(, $ot) = each($order->totals)) {
	  if ($ot['class']=='ot_total') {
		$ot_total_value = str_replace(',', '.', sprintf("%01.1f", round($ot['value'], $currencies->get_decimal_places($currency))));
		break;
	  }
	}

	if ($c_id==$customer_id && $order_id==$last_order && $total_value==$ot_total_value) {
	  $sign = md5('fix;' . $ot_total_value . ';' . $order->info['currency'] . ';' . $order_id . ';yes;' . MODULE_PAYMENT_ONPAY_PASSWORD1);

	  $user_phone = $order->customer['telephone'];
	  $user_phone_1 = preg_replace('/[^\d]/', '', $user_phone);
	  if (strlen($user_phone_1) == 7) $user_phone = '+7495' . $user_phone_1;
	  elseif (strlen($user_phone_1) == 11 && substr($user_phone_1, 0, 1)=='8') $user_phone = '+7' . substr($user_phone_1, 1);
	  elseif (strlen($user_phone_1) == 11 && substr($user_phone_1, 0, 1)=='7') $user_phone = '+' . $user_phone_1;
	  elseif (strlen($user_phone_1) == 10) $user_phone = '+7' . $user_phone_1;

	  $payment_url = 'https://secure.onpay.ru/pay/' . MODULE_PAYMENT_ONPAY_LOGIN . '?pay_mode=fix&pay_for=' . $order_id . '&price=' . urlencode($ot_total_value) . '&currency=' . urlencode($order->info['currency']) . '&convert=yes&md5=' . $sign . '&user_email=' . urlencode($order->customer['email_address']) . '&user_phone=' . urlencode($user_phone) . '&url_success=' . MODULE_PAYMENT_ONPAY_SUCCESS . '&url_fail=' . MODULE_PAYMENT_ONPAY_FAIL . '&note=' . urlencode('Оплата заказа #' . $insert_id . ' в магазине ' . STORE_NAME);
	  $payment_url = 'https://secure.onpay.ru/pay/' . MODULE_PAYMENT_ONPAY_LOGIN . '?md5=' . $sign;
	  echo '<br /><p>' . MODULE_PAYMENT_ROBOX_TEXT_DESCRIPTION_2 . '</p>' .
		   tep_draw_form('merchant', $payment_url) .
		   tep_draw_hidden_field('pay_mode',		'fix') .
		   tep_draw_hidden_field('pay_for',			$order_id) .
		   tep_draw_hidden_field('price',			$ot_total_value) .
		   tep_draw_hidden_field('currency',		$order->info['currency']) .
		   tep_draw_hidden_field('convert',			'yes') .
		   tep_draw_hidden_field('user_email',		$order->customer['email_address']) .
		   tep_draw_hidden_field('user_phone',		$user_phone) .
		   tep_draw_hidden_field('url_success',		MODULE_PAYMENT_ONPAY_SUCCESS) .
		   tep_draw_hidden_field('url_fail',		MODULE_PAYMENT_ONPAY_FAIL) .
		   tep_draw_hidden_field('note',			'Оплата заказа #' . $order_id . ' в магазине ' . STORE_NAME) .
		   tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE) .
		   '</form>';
	}
  } else {
?>
	<div class="buttons">
	  <div style="text-align: right;"><a href="<?php echo tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>"><?php echo tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a></div>
	</div>
<?php
  }
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order($last_order);
?>

<script language="javascript" type="text/javascript">
var ya_params = 
{
  order_id: <?php echo $last_order; ?>,
  order_price: <?php echo str_replace(',', '.', round($order->info['total_value'], $currencies->get_decimal_places($order->info['currency']))); ?>, 
  currency: "<?php echo $order->info['currency']; ?>",
  exchange_rate: <?php echo str_replace(',', '.', round(1/$order->info['currency_value'], 4)); ?>,
  goods: 
	[
<?php
  reset($order->products);
  while (list(, $order_product_info) = each($order->products)) {
?>
	{
	  id: <?php echo $order_product_info['id']; ?>, 
	  name: "<?php echo $order_product_info['name']; ?>"
	  price: <?php echo str_replace(',', '.', round($order_product_info['price']*$order->info['currency_value'], $currencies->get_decimal_places($order->info['currency']))); ?>

	},
<?php
  }
?>
      ]
};
</script>
