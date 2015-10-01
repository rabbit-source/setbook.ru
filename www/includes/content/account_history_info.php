<?php
  echo $page['pages_description'];
?>
	<form class="form-div">
	<fieldset>
	<legend><?php echo HEADING_ORDER_INFO; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo HEADING_ORDER_NUMBER; ?></td>
		<td width="50%"><?php echo $HTTP_GET_VARS['order_id']; ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo HEADING_ORDER_DATE; ?></td>
		<td><?php echo tep_date_long($order->info['date_purchased']); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo HEADING_ORDER_STATUS; ?></td>
		<td><?php echo $order->info['orders_status']; ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo HEADING_ORDER_TOTAL; ?></td>
		<td><?php echo $order->info['total']; ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
  if ($order->delivery != false) {
?>
	<fieldset>
	<legend><?php echo HEADING_DELIVERY_INFORMATION; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo HEADING_DELIVERY_ADDRESS; ?></td>
		<td width="50%"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', ', '); ?></td>
	  </tr>
<?php
    if (tep_not_null($order->info['shipping_method'])) {
?>
	  <tr valign="top">
		<td><?php echo HEADING_SHIPPING_METHOD; ?></td>
		<td><?php echo $order->info['shipping_method']; ?></td>
	  </tr>
<?php
    }
	$tracking_numbers_count = 0;
    if (tep_not_null($order->delivery['tracking_number'])) {
	  $tracking_numbers_count = sizeof(explode("\n", $order->delivery['tracking_number']));
?>
	  <tr valign="top">
		<td><?php echo HEADING_TRACKING_NUMBER; ?></td>
		<td><?php echo nl2br($order->delivery['tracking_number']); ?></td>
	  </tr>
<?php
    }
?>
	</table>
	</fieldset>
<?php
  }
  if ($order->billing != false) {
?>
	<fieldset>
	<legend><?php echo HEADING_BILLING_INFORMATION; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo HEADING_BILLING_ADDRESS; ?></td>
		<td width="50%"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', ', '); ?></td>
	  </tr>
<?php
	$payment_details = '';
    if (tep_not_null($order->info['payment_method'])) {
	  if (is_array($payment_modules->modules)) {
		reset($payment_modules->modules);
		while (list(, $payment_row) = each($payment_modules->modules)) {
		  $payment = substr($payment_row, 0, strrpos($payment_row, '.'));
		  if (is_object($$payment)) {
			$payment_class = new $$payment;
			if ($payment_class->title==$order->info['payment_method']) {
			  if (tep_not_null($payment_class->email_footer)) {
				$payment_details .= '<br /><br />' . "\n" . nl2br(str_replace('[order_id]', $HTTP_GET_VARS['order_id'], $payment_class->email_footer));
			  }
			}
		  }
		}
	  }
?>
	  <tr valign="top">
		<td><?php echo HEADING_PAYMENT_METHOD; ?></td>
		<td><?php echo $order->info['payment_method'] . $payment_details; ?></td>
	  </tr>
<?php
    }
?>
	</table>
	</fieldset>
<?php
  }
?>
	<fieldset>
	<legend><?php echo HEADING_PRODUCTS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
	echo '	  <tr valign="top">' . "\n" .
	'		<td width="1%" align="right">' . $order->products[$i]['qty'] . '</td>' . "\n" .
	'		<td width="1%">x</td>' . "\n" .
	'		<td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']) . '">' . $order->products[$i]['name'] . '</a>' . ((tep_not_null($order->products[$i]['tracking_number']) && $tracking_numbers_count>1) ? '<br />' . "\n" . HEADING_TRACKING_NUMBER . ' ' . $order->products[$i]['tracking_number']: '') . '</td>' . "\n" .
		 '		<td align="right" nowrap="nowrap">' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
		 '	  </tr>' . "\n";
  }
?>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo HEADING_ORDER_HISTORY; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
  $statuses_query = tep_db_query("select os.orders_status_name, os.orders_status_description, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$HTTP_GET_VARS['order_id'] . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by osh.date_added");
  while ($statuses = tep_db_fetch_array($statuses_query)) {
	echo '	  <tr valign="top">' . "\n" .
	'		<td width="50%">' . tep_date_long($statuses['date_added']) . '</td>' . "\n" .
	'		<td width="50%">' . $statuses['orders_status_name'] . (tep_not_null($statuses['comments']) ? ' (' . nl2br(tep_output_string_protected($statuses['comments'])) . ')' : (tep_not_null($statuses['orders_status_description']) ? ' (' . tep_output_string_protected($statuses['orders_status_description']) . ')' : '')) . '</td>' . "\n" .
	'	  </tr>' . "\n";
  }
?>
	</table>
	</fieldset>
<?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, tep_get_all_get_params(array('order_id')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
	</form>
