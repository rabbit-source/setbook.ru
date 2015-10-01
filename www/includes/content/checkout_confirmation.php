<?php
  echo $page['pages_description'];

  if (isset($$payment->form_action_url)) {
    $form_action_url = $$payment->form_action_url;
  } else {
    $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }
?>
	<script language="javascript" type="text/javascript"><!--
	  var order_is_processing = false;
	//--></script>
<?php
  $onsubmit_string = ' onsubmit="';
  if (TERMS_OF_AGREEMENT=='confirmation' || TERMS_OF_AGREEMENT=='both') {
	$onsubmit_string .= 'if (agreement.checked==false) { alert(\'* ' . ENTRY_AGREEMENT_ERROR . '\'); return false; } else {';
  }
  $onsubmit_string .= ' if (order_is_processing) { return false; } else { order_is_processing = true; document.getElementById(\'process_order_links\').innerHTML = \'' . htmlspecialchars('<div class="errorText" style="text-align: center;">' . TEXT_ORDER_IS_PROCESSING . '</div>') . '\'; }';
  if (TERMS_OF_AGREEMENT=='confirmation' || TERMS_OF_AGREEMENT=='both') {
	$onsubmit_string .= ' }';
  }
  $onsubmit_string .= '"';

  echo tep_draw_form('checkout_confirmation', $form_action_url, 'post', 'class="form-div"' . $onsubmit_string);

  if ($sendto != false) {
?>
	<fieldset>
	<legend><?php echo HEADING_DELIVERY_ADDRESS . ' &nbsp; <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<div><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', "\n"); ?></div>
	</fieldset>
<?php
    if ($order->info['shipping_method']) {
?>
	<fieldset>
	<legend><?php echo HEADING_SHIPPING_METHOD . ' &nbsp; <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<div><?php echo $order->info['shipping_method']; ?></div>
	<?php
	  if (ALLOW_SHOW_AVAILABLE_IN=='true') {
		$transfer_to_delivery_date = tep_calculate_date_available($cart->info['delivery_transfer']);
//		$delivery_to_city_date = date('Y-m-d', strtotime($transfer_to_delivery_date) + $order->info['city_delivery_days']*60*60*24);
		$delivery_to_city_date = tep_calculate_date_available($cart->info['delivery_transfer'] + $order->info['city_delivery_days']);
		echo '<div class="errorText">' . sprintf(MAX_AVAILABLE_IN, tep_date_long($transfer_to_delivery_date, false));
		if (ALLOW_SHOW_RECEIVE_IN=='true' && isset($order->info['city_delivery_days'])) echo ' ' . sprintf(MAX_RECEIVE_IN, tep_date_long($delivery_to_city_date, false)) . '';
		echo '</div>';
	  }
?>
	</fieldset>
<?php
    }
  }
  if ($cart->content_type=='virtual' || $cart->content_type=='mixed') {
?>
	<fieldset>
	<legend><?php echo HEADING_DOWNLOAD_INFO; ?></legend>
	<div><?php echo PRODUCTS_DOWNLOAD_INFO; ?></div>
	</fieldset>
<?php
  }
  if (1==2) {
?>
	<fieldset>
	<legend><?php echo HEADING_BILLING_ADDRESS . ' &nbsp; <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<div><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', ', '); ?></div>
	</fieldset>
<?php
  }
  if (tep_not_null($order->info['payment_method'])) {
?>
	<fieldset>
	<legend><?php echo HEADING_PAYMENT_METHOD . ' &nbsp; <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<div><?php echo $order->info['payment_method']; ?></div>
	</fieldset>
<?php
  }
?>
	<fieldset>
	<legend><?php echo HEADING_PRODUCTS . ' <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
	$product_info_query = tep_db_query("select products_types_id, products_periodicity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$order->products[$i]['id'] . "'");
	$product_info = tep_db_fetch_array($product_info_query);
	$periodicity_array = array();
	$periodicity_array['3'] = TEXT_SUBSCRIBE_TO_3_MONTHES;
	$periodicity_array[$product_info['products_periodicity']/2] = TEXT_SUBSCRIBE_TO_HALF_A_YEAR;
	$periodicity_array[$product_info['products_periodicity']] = TEXT_SUBSCRIBE_TO_YEAR;

	$products_name = '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']) . '">' . $order->products[$i]['name'] . '</a>';
	if ($product_info['products_types_id']=='2') $products_name .= ' (' . TEXT_SUBSCRIBE_TO . ' ' . $periodicity_array[$order->products[$i]['qty']] . ')';

    echo '	  <tr valign="top">' . "\n" .
         '		<td width="95%">' . $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $products_name;

    if (STOCK_CHECK == 'true') {
      echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo '		<td class="main" nowrap="nowrap" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";

    echo '		<td align="right" nowrap="nowrap">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</td>' . "\n" .
         '	  </tr>' . "\n";
  }
?>
	</table>
<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
    $order_total_modules->process();
    echo $order_total_modules->output();
?>
	</table>
<?php
  }
?>
	</fieldset>
<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>
	<fieldset>
	<legend><?php echo HEADING_PAYMENT_INFORMATION; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td colspan="2"><?php echo $confirmation['title']; ?></td>
	  </tr>
<?php
      for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
		if (!isset($confirmation['fields'][$i]['field'])) {
?>
	  <tr>
		<td colspan="2"><?php echo $confirmation['fields'][$i]['title']; ?></td>
	  </tr>
<?php
		} else {
?>
	  <tr>
		<td width="50%"><?php echo $confirmation['fields'][$i]['title']; ?></td>
		<td width="50%"><?php echo $confirmation['fields'][$i]['field']; ?></td>
	  </tr>
<?php
		}
      }
?>
	</table>
	</fieldset>
<?php
    }
  }
  if (tep_not_null($order->info['comments'])) {
?>
	<fieldset>
	<legend><?php echo HEADING_ORDER_COMMENTS . ' &nbsp; <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_EDIT . ']</a>'; ?></legend>
	<div><?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?></div>
	</fieldset>
<?php
  }
  if (TERMS_OF_AGREEMENT=='confirmation' || TERMS_OF_AGREEMENT=='both') {
?>
	<fieldset>
	<legend><?php echo TERMS_OF_AGREEMENT_TITLE; ?></legend>
	<div><?php echo (tep_not_null(TERMS_OF_AGREEMENT_TEXT) ? '<span style="display: block; margin-bottom: 10px;">' . TERMS_OF_AGREEMENT_TEXT . '</span>' . "\n" : '') . tep_draw_checkbox_field('agreement', '1', false) . TERMS_OF_AGREEMENT_LINK; ?></div>
	</fieldset>
<?php
  }
  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }
?>
	<div class="buttons" id="process_order_links">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER); ?></div>
	</div>
	</form>
