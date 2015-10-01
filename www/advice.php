<?php
  require('includes/application_top.php');

  $customerId = '';

  $admin_access = false;
  if (tep_not_null($HTTP_GET_VARS['email_address']) && tep_not_null($HTTP_GET_VARS['password'])) {
    $email_address = tep_db_prepare_input($HTTP_GET_VARS['email_address']);
    $password = tep_db_prepare_input($HTTP_GET_VARS['password']);
	$order_id = tep_db_prepare_input($HTTP_GET_VARS['order_id']);
	$customer_info_query = tep_db_query("select customers_id, customers_firstname, customers_lastname, customers_type from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_password = '" . tep_db_input($password) . "'");
	$customer_info = tep_db_fetch_array($customer_info_query);
	$order_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_info['customers_id'] . "' and orders_id = '" . (int)$order_id . "'");
	$order_check = tep_db_fetch_array($order_check_query);
	if ($order_check['total'] > 0) {
	  $admin_access = true;
	  $customerId = $customer_info['customers_id'];
	}
  } elseif (tep_session_is_registered('customer_id') && tep_session_is_registered('customer_first_name')) {
	$customerId = $customer_id;
  }

  if ((int)$customerId == 0) {
	if (is_object($navigation)) $navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_CLASSES . 'order.php');
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title>Документ на оплату</title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
</head>
<body bgcolor="#ffffff">

<?php
  if (isset($HTTP_GET_VARS['order_id'])) $order_id = tep_db_prepare_input($HTTP_GET_VARS['order_id']);
  else $order_id = '';
  if (tep_not_null($order_id)) {
	$orders_query = tep_db_query("select orders_id, customers_company_name from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customerId . "'");
	$orders = tep_db_fetch_array($orders_query);
	$order_id = $orders['orders_id'];
	$customer_type = (tep_not_null($orders['customers_company_name']) ? 'corporate' : 'private');
  }
  if ( (tep_session_is_registered('customer_id') || $admin_access==true) && tep_not_null($order_id)) {
	$order = new order($order_id);

	$user_string = (tep_not_null($order->billing['name']) ? str_replace(" ", "&nbsp;", $order->billing['name']) . '; ' : '') . (tep_not_null($order->billing['postcode']) ? $order->billing['postcode'] . ', ' : '') . (tep_not_null($order->billing['city']) ? $order->billing['city'] . ', ' : '') . $order->billing['street_address'];
	$user_address = (tep_not_null($order->billing['postcode']) ? $order->billing['postcode'] . ', ' : '') . (tep_not_null($order->billing['country']) ? $order->billing['country'] . ', ' : '') . (tep_not_null($order->billing['city']) ? $order->billing['city'] . ', ' : '') . $order->billing['street_address'];
	$order_date = tep_date_short($order->info['date_purchased']);
	$order_date_long = tep_date_long($order->info['date_purchased']);

	$totals_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where class = 'ot_total' and orders_id = '" . (int)$order_id . "' limit 1");
	$totals_array = tep_db_fetch_array($totals_query);
	$total_sum = str_replace(',', '.', $totals_array['value']);
	list($total_solid, $total_decimal) = explode('.', $total_sum);
	$total_solid = round($total_sum, $currencies->get_decimal_places[$order->info['currency']]);
	if ($currencies->get_decimal_places[$order->info['currency']]==0) $total_decimal = '00';
	else $total_decimal = round($total_decimal/100, $currencies->get_decimal_places[$order->info['currency']])*100;
	$total_decimal = substr($total_decimal, 0, 2);
	$total_sum_string = str_replace('.', '', $currencies->format($totals_array['value'], true, 'RUR', $order->info['currency_value'])) . '.';

	$recipient_account = 'Получатель платежа: ' . str_replace(', ', '<br />', STORE_OWNER . ', ИНН ' . STORE_OWNER_INN . ', р/с ' . STORE_OWNER_RS . ' в ' . STORE_OWNER_BANK . ', к/с ' . STORE_OWNER_KS . ', БИК ' . STORE_OWNER_BIK) . '<br /><br />';

	if ($customer_type=='corporate') {
?>

<style type="text/css">
body, p, td { font-size: 9pt; font-family: Arial, Helvetica, Tahoma, Verdana; }
table { border-right: 1px solid black; border-top: 1px solid black; }
td { border-left: 1px solid black; border-bottom: 1px solid black; }
</style>
<table cellspacing="0" align="center" border="0" cellpadding="0" width="615" style="border: none;">
  <tr valign="top">
	<td style="border: none;"><strong><u><?php echo STORE_OWNER; ?></u></strong><br><br>
	<strong>Адрес: <?php echo STORE_OWNER_ADDRESS_CORPORATE . ', тел.: ' . STORE_OWNER_PHONE_NUMBER; ?></strong><br><br>
	<table border="0" cellspacing="0" cellpadding="1" width="100%">
	  <tr valign="bottom">
		<td width="33%">ИНН <?php echo STORE_OWNER_INN; ?></td>
		<td width="24%">КПП <?php echo STORE_OWNER_KPP; ?></td>
		<td width="7%" rowspan="2" align="center">Сч. №</td>
		<td width="36%" rowspan="2"><?php echo STORE_OWNER_RS; ?></td>
	  </tr>
	  <tr>
		<td colspan="2">Получатель<br><?php echo STORE_OWNER; ?></td>
	  </tr>
	  <tr>
		<td colspan="2" style="border-bottom: none;">Банк получателя</td>
		<td align="center">БИК</td>
		<td style="border-bottom: none;"><?php echo STORE_OWNER_BIK; ?></td>
	  </tr>
	  <tr>
		<td colspan="2"><?php echo STORE_OWNER_BANK; ?></td>
		<td align="center">Сч. №</td>
		<td><?php echo STORE_OWNER_KS; ?></td>
	  </tr>
	</table>
	<div align="center"><h2>СЧЕТ № <?php echo $order_id ?> от <?php echo $order_date_long; ?> г.</h2></div>
	Заказчик: <?php echo 'ИНН ' . $order->customer['company_inn'] . (tep_not_null($order->customer['company_kpp']) ? ' &nbsp;КПП ' . $order->customer['company_kpp'] : '') . ' &nbsp;' . $order->customer['company']; ?><br><br>
	Плательщик: <?php echo 'ИНН ' . $order->customer['company_inn'] . (tep_not_null($order->customer['company_kpp']) ? ' &nbsp;КПП ' . $order->customer['company_kpp'] : '') . ' &nbsp;' . $order->customer['company']; ?><br><br><br>
	<table border="0" cellspacing="0" cellpadding="1" width="100%">
	  <tr align="center">
		<td width="3%" >№</td>
		<td width="52%">Наименование<br>товара</td>
		<td width="10%">Единица<br>изме-<br>рения</td>
		<td width="9%">Коли-<br>чество</td>
		<td width="13%">Цена</td>
		<td width="13%">Сумма</td>
	  </tr>
<?php
	  $i = 1;
	  reset($order->products);
	  while (list(, $order_product_info) = each($order->products)) {
?>
	  <tr align="right">
		<td align="center"><?php echo $i; ?></td>
		<td align="left"><?php echo $order_product_info['name']; ?></td>
		<td align="center">шт</td>
		<td align="center"><?php echo $order_product_info['qty']; ?></td>
		<td><?php echo $currencies->format($order_product_info['final_price']); ?></td>
		<td><?php echo $currencies->format($order_product_info['final_price']*$order_product_info['qty']); ?></td>
	  </tr>
<?php
		$i ++;
	  }

	  $totals_query = tep_db_query("select text, title, class from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
	  while ($totals_array = tep_db_fetch_array($totals_query)) {
		if (substr($totals_array['title'], -1)!=':') $totals_array['title'] .= ':';
		if ($totals_array['class']=='ot_shipping') $totals_array['title'] = 'Доставка:';
?>
	  <tr align="right">
		<td colspan="5" style="border-left: none; border-bottom: none;"><?php echo ($totals_array['class']=='ot_total' ? '<strong>' . $totals_array['title'] . '</strong>' : $totals_array['title']); ?></td>
		<td><?php echo $totals_array['text']; ?></td>
	  </tr>
<?php
		if ($totals_array['class']=='ot_subtotal') {
		  $nds_value = $totals_array['value'] - $totals_array['value']/1.12;
?>
	  <tr align="right">
		<td colspan="5" style="border-left: none; border-bottom: none;">Без налога (НДС):</td>
		<td align="center">-</td>
	  </tr>
<?php
		}
	  }
?>
	</table><br>
	Всего наименований <?php echo ($i-1); ?>, на сумму <?php echo number_format(round($total_sum, $currencies->get_decimal_places[$order->info['currency']]), 2, ',', '`'); ?><br>
	<strong><?php $total_sum_text = tep_number_to_string($total_sum); echo $total_sum_text['solid']['text'] . ' ' . $total_sum_text['solid']['currency'] . ' ' . $total_sum_text['decimal']['text'] . ' ' . $total_sum_text['decimal']['currency']; ?></strong><br><br>
	<small>Примечание: НДС не облагается в связи с применением упрощенной системы налогообложения</small><br><br>
	<div style="position: absolute; margin: -35px 0 0 200px; z-index: -1;"><?php echo tep_image(DIR_WS_IMAGES . 'signature.gif', ''); ?></div>Руководитель предприятия__________________(<?php echo STORE_OWNER_GENERAL; ?>)<br><br>
	<div style="position: absolute; margin: -25px 0 0 135px; z-index: -1;"><?php echo tep_image(DIR_WS_IMAGES . 'signature.gif', ''); ?></div>Главный бухгалтер_________________________(<?php echo STORE_OWNER_FINANCIAL; ?>)<br>
	<div style="margin: -40px 0 0 300px; position: absolute; z-index: -1;"><?php echo tep_image(DIR_WS_IMAGES . 'stamp.gif', ''); ?></div>
	</td>
  </tr>
</table>

<?php
	} elseif (mb_strpos($order->info['payment_method'], 'Почтовым', 0, 'CP1251')!==false) {
	  $total_sum_1 = round($total_sum, $currencies->get_decimal_places[$order->info['currency']]);
	  $total_sum_text = tep_number_to_string($total_sum_1);
?>

<style type="text/css">
  body, p, td { font-family: Arial; font-size: 8pt; }
  small { font-size: 7pt; }
 </style>
<table width="590" align="center" cellspacing="0" cellpadding="0" style="border: 1px solid black;">
  <tr valign="top">
	<td width="160" style="border-right: 1px solid black;">&nbsp;</td>
	<td><br>
	<table width="420" cellspacing="0" cellpadding="0" border="0">
	  <tr valign="top">
		<td valign="top" width="120">&nbsp;&nbsp;&nbsp;<?php echo tep_image(DIR_WS_IMAGES . 'gerb.jpg', '', '60', '60'); ?><br>
		&nbsp;Почта России<br>
		&nbsp;№__________________&nbsp;&nbsp;
		&nbsp;(по р.ф.11)</td>
		<td width="20" align="center">П<br>Р<br>И<br>Е<br>М</td>
		<td width="250">&nbsp;</td>
		<td valign="top" width="30"><div align="right">ф.112э&nbsp;&nbsp;&nbsp;</div></td>
	  </tr>
	</table>
	<table width="420" cellspacing="0" cellpadding="0" border="0">
	  <tr valign="top">
 		<td><br>
		<table border="0" align="center" cellspacing="0" cellpadding="0" width="410">
		  <tr valign="bottom">
			<td>ПОЧТОВЫЙ ПЕРЕВОД &nbsp; (Электронный) &nbsp; на&nbsp;</td>
			<td align="center" style="border-bottom: 1px solid black;" width="20%">&nbsp;<?php echo ($currencies->get_decimal_places[$order->info['currency']]==0 ? round($total_sum) : $total_sum_text['solid']['value']); ?>&nbsp;</td>
			<td>&nbsp;руб.&nbsp;</td>
			<td align="center" style="border-bottom: 1px solid black; padding-top: 2px;" width="12%">&nbsp;<?php echo ($currencies->get_decimal_places[$order->info['currency']]==0 ? '00' : $total_sum_text['decimal']['value']); ?>&nbsp;</td>
			<td>&nbsp;коп.&nbsp;</td>
		  </tr>
		  <tr valign="bottom">
			<td align="center" colspan="2" style="border-bottom: 1px solid black;" width="80%">&nbsp;<?php echo $total_sum_text['solid']['text']; ?>&nbsp;</td>
			<td>&nbsp;руб.&nbsp;</td>
			<td align="center" style="border-bottom: 1px solid black; padding-top: 2px;" width="12%">&nbsp;<?php echo $total_sum_text['decimal']['text']; ?>&nbsp;</td>
			<td>&nbsp;коп.&nbsp;</td>
		  </tr>
		  <tr align="center">
			<td colspan="2"><small>(прописью)</small></td>
			<td colspan="3"><small>(цифрами)</small></td>
		  </tr>
		</table><br>
		<table border="0" align="center" cellspacing="0" cellpadding="0" width="410">
		  <tr valign="top">
			<td><em>Куда:&nbsp;&nbsp;</em></td>
			<td><?php echo STORE_OWNER_ADDRESS_POST; ?></td>
		  </tr>
		  <tr valign="top">
			<td><em>Кому:&nbsp;&nbsp;</em></td>
			<td><?php echo STORE_OWNER; ?>, ИНН: <?php echo STORE_OWNER_INN; ?>, Р/c: <?php echo STORE_OWNER_RS; ?>, <?php echo str_replace(' ', '&nbsp;', STORE_OWNER_BANK); ?>, К/с: <?php echo STORE_OWNER_KS; ?>, БИК: <?php echo STORE_OWNER_BIK; ?></td>
		  </tr>
		</table><br>
		<table border="0" align="center" cellspacing="0" cellpadding="0" width="410">
		  <tr valign="bottom">
			<td><em>От&nbsp;кого:&nbsp;</em></td>
			<td width="100%" style="border-bottom: 1px solid black; padding-top: 2px;">&nbsp;<?php echo $order->billing['name']; ?></td>
		  </tr>
		</table>
		<table border="0" align="center" cellspacing="0" cellpadding="0" width="410">
		  <tr valign="bottom">
			<td><em>Адрес:&nbsp;</em></td>
			<td width="100%" style="border-bottom: 1px solid black; padding-top: 2px;">&nbsp;<?php echo $user_address; ?></td>
		  </tr>
		</table>
		<table border="0" align="center" cellspacing="0" cellpadding="0" width="410">
		  <tr valign="bottom">
			<td><em>Сообщение:&nbsp;</em></td>
			<td width="100%" style="border-bottom: 1px solid black; padding-top: 2px;">&nbsp;Оплата заказа № <?php echo $order_id ?> в <?php echo STORE_NAME; ?> от <?php echo $order_date; ?></td>
		  </tr>
		</table><br>
		<table border="0" align="right" cellspacing="0" cellpadding="0" width="110">
		  <tr>
			<td style="border-bottom: 1px solid black;">&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td align="center"><small>(Подпись оператора)</small></td>
			<td>&nbsp;</td>
		  </tr>
		</table>
		</td>
	  </tr>
	</table></td>
  </tr>
</table>

<?php
	} else {
?>

<style type="text/css">
H1 { font-size: 11pt; }
p, ul, ol, h1 { margin-top: 6px; margin-bottom: 6px; } 
td { font-size: 9pt; }
small { font-size: 7pt; }
body { font-size: 10pt; }
</style>
<table border="0" cellspacing="0" cellpadding="0" style="width:180mm;" align="center"><tr><td>
<table border="0" cellspacing="0" cellpadding="0" style="width:180mm; height:145mm;">
  <tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-bottom:none; border-right:none;" align="center">
	<b>Извещение</b><br>
	<font style="font-size: 224px;">&nbsp;<br></font>
	<b>Кассир</b>
	</td>
	<td style="border:1pt solid #000000; border-bottom:none;" align="center">
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td align="right"><small><i>Форма № ПД-4</i></small></td>
	  </tr>
	  <tr>
		<td style="border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER; ?></td>
	  </tr>
	  <tr>
		<td align="center"><small>(наименование получателя платежа)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td style="width:37mm; border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER_INN; ?>/<?php echo STORE_OWNER_KPP; ?></td>
		<td style="width:9mm;">&nbsp;</td>
		<td style="border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER_RS; ?></td>
	  </tr>
	  <tr>
		<td align="center"><small>(ИНН получателя платежа)</small></td>
		<td><small>&nbsp;</small></td>
		<td align="center"><small>(номер счета получателя платежа)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>в&nbsp;</td>
		<td style="width:73mm; border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_BANK; ?></td>
		<td align="right">БИК&nbsp;&nbsp;</td>
		<td style="width:33mm; border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_BIK; ?></td>
	  </tr>
	  <tr>
		<td></td>
		<td align="center"><small>(наименование банка получателя платежа)</small></td>
		<td></td>
		<td></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Номер кор./сч. банка получателя платежа&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_KS; ?></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td style="width:60mm; border-bottom:1pt solid #000000;" align="center">Оплата заказа № <?php echo $order_id ?> от <?php echo $order_date; ?></td>
		<td style="width:2mm;">&nbsp;</td>
		<td style="border-bottom:1pt solid #000000;">&nbsp;</td>
	  </tr>
	  <tr>
		<td align="center"><small>(наименование платежа)</small></td>
		<td><small>&nbsp;</small></td>
		<td align="center"><small>(номер лицевого счета (код) плательщика)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Ф.И.О. плательщика&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo $order->billing['name']; ?></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Адрес плательщика&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo $user_address; ?>&nbsp;</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>Сумма платежа&nbsp;<font style="text-decoration:underline;">&nbsp;<?php echo $total_solid; ?>&nbsp;</font>&nbsp;руб.&nbsp;<font style="text-decoration:underline;">&nbsp;<?php echo $total_decimal; ?>&nbsp;</font>&nbsp;коп.</td>
		<td align="right">&nbsp;&nbsp;Сумма платы за услуги&nbsp;&nbsp;_____&nbsp;руб.&nbsp;____&nbsp;коп.</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>Итого&nbsp;&nbsp;_______&nbsp;руб.&nbsp;____&nbsp;коп.</td>
		<td align="right">&nbsp;&nbsp;&laquo;______&raquo;________________ 201____ г.</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td><small>С условиями приема указанной в платежном документе суммы, 
				в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td align="right"><b>Подпись плательщика _____________________</b></td>
	  </tr>
	</table></td>
  </tr>
  <tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-right:none;" align="center">
	<b>Квитанция</b><br>
	<font style="font-size: 224px;">&nbsp;<br></font>
	<b>Кассир</b>
	</td>
	<td style="border:1pt solid #000000;" align="center">
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td align="right"><small><i>Форма № ПД-4</i></small></td>
	  </tr>
	  <tr>
		<td style="border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER; ?></td>
	  </tr>
	  <tr>
		<td align="center"><small>(наименование получателя платежа)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td style="width:37mm; border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER_INN; ?>/<?php echo STORE_OWNER_KPP; ?></td>
		<td style="width:9mm;">&nbsp;</td>
		<td style="border-bottom:1pt solid #000000;" align="center"><?php echo STORE_OWNER_RS; ?></td>
	  </tr>
	  <tr>
		<td align="center"><small>(ИНН получателя платежа)</small></td>
		<td><small>&nbsp;</small></td>
		<td align="center"><small>(номер счета получателя платежа)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>в&nbsp;</td>
		<td style="width:73mm; border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_BANK; ?></td>
		<td align="right">БИК&nbsp;&nbsp;</td>
		<td style="width:33mm; border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_BIK; ?></td>
	  </tr>
	  <tr>
		<td></td>
		<td align="center"><small>(наименование банка получателя платежа)</small></td>
		<td></td>
		<td></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Номер кор./сч. банка получателя платежа&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo STORE_OWNER_KS; ?></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	<tr>
		<td style="width:60mm; border-bottom:1pt solid #000000;" align="center">Оплата заказа № <?php echo $order_id ?> от <?php echo $order_date; ?></td>
		<td style="width:2mm;">&nbsp;</td>
		<td style="border-bottom:1pt solid #000000;">&nbsp;</td>
	  </tr>
	  <tr>
		<td align="center"><small>(наименование платежа)</small></td>
		<td><small>&nbsp;</small></td>
		<td align="center"><small>(номер лицевого счета (код) плательщика)</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Ф.И.О. плательщика&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo $order->billing['name']; ?></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td width="1%" nowrap>Адрес плательщика&nbsp;&nbsp;</td>
		<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;&nbsp;<?php echo $user_address; ?>&nbsp;</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>Сумма платежа&nbsp;<font style="text-decoration:underline;">&nbsp;<?php echo $total_solid; ?>&nbsp;</font>&nbsp;руб.&nbsp;<font style="text-decoration:underline;">&nbsp;<?php echo $total_decimal; ?>&nbsp;</font>&nbsp;коп.</td>
		<td align="right">&nbsp;&nbsp;Сумма платы за услуги&nbsp;&nbsp;_____&nbsp;руб.&nbsp;____&nbsp;коп.</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td>Итого&nbsp;&nbsp;_______&nbsp;руб.&nbsp;____&nbsp;коп.</td>
		<td align="right">&nbsp;&nbsp;&laquo;______&raquo;________________ 201____ г.</td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td><small>С условиями приема указанной в платежном документе суммы, 
				в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.</small></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
	  <tr>
		<td align="right"><b>Подпись плательщика _____________________</b></td>
	  </tr>
	</table></td>
  </tr>
</table>
<br />
<h1>Внимание! В стоимость заказа не включена комиссия банка.</h1>

<!-- Условия поставки -->
<h1><b>Метод оплаты:</b></h1>
<ol>
  <li>Распечатайте квитанцию. Если у вас нет принтера, перепишите верхнюю часть квитанции и заполните по этому образцу стандартный бланк квитанции в вашем банке.</li>
  <li>Вырежьте по контуру квитанцию.</li>
  <li>Оплатите квитанцию в любом отделении банка, принимающего платежи от частных лиц.</li>
  <li>Сохраните квитанцию до подтверждения исполнения заказа.</li>
</ol>

<h1><b>Условия поставки:</b> </h1>
<ul>
  <li>Отгрузка оплаченного товара производится после подтверждения факта платежа.</li>
  <li>Идентификация платежа производится по квитанции, поступившей в наш банк.</li>
</ul>


<p><b>Примечание:</b>
<?php echo STORE_OWNER; ?> не может гарантировать конкретные сроки проведения вашего платежа. За дополнительной информацией о сроках доставки квитанции в банк получателя, обращайтесь в свой банк.</p>
</td></tr></table>

<?php
	}
?>
<script language="javascript" type="text/javascript"><!--
function print_kvit() {
  if (confirm("Распечатать документ?")) {
	window.print();
  }
}

setTimeout('print_kvit()',2000);
//--></script>
<?php
  } else {
	echo tep_draw_form('advice', tep_href_link(PHP_SELF, '', 'SSL'), 'get');
?>
<table border="0" cellspacing="0" cellpadding="3">
  <tr>
	<td>Введите номер заказа:</td>
	<td><?php echo tep_draw_input_field('order_id'); ?></td>
	<td><?php echo tep_draw_selection_field('', 'submit', 'Счет'); ?></td>
  </tr>
</table>
</form>
<?php
  }
?>
</body>
</html>
<?php
  tep_exit();
?>