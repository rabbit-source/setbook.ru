<?php
  echo $page['pages_description'];

  echo '<form class="form-div">';
  $orders_total = tep_count_customer_orders();

  if ($orders_total > 0) {
    $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_address_format_id, o.delivery_company, o.delivery_street_address, o.delivery_suburb, o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country, o.billing_name, o.billing_address_format_id, o.billing_company, o.billing_street_address, o.billing_suburb, o.billing_city, o.billing_postcode, o.billing_state, o.billing_country, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by orders_id desc";
    $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
    $history_query = tep_db_query($history_split->sql_query);

    while ($history = tep_db_fetch_array($history_query)) {
      $products_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$history['orders_id'] . "'");
      $products = tep_db_fetch_array($products_query);

      if (tep_not_null($history['delivery_name'])) {
        $order_type = TEXT_ORDER_SHIPPED_TO;
		$history['address_format_id'] = $history['delivery_address_format_id'];
		$history['name'] = $history['delivery_name'];
		$history['company'] = $history['delivery_company'];
		$history['street_address'] = $history['delivery_street_address'];
		$history['suburb'] = $history['delivery_suburb'];
		$history['city'] = $history['delivery_city'];
		$history['postcode'] = $history['delivery_postcode'];
		$history['state'] = $history['delivery_state'];
		$history['country'] = $history['delivery_country'];
      } else {
        $order_type = TEXT_ORDER_BILLED_TO;
		$history['address_format_id'] = $history['billing_address_format_id'];
		$history['name'] = $history['billing_name'];
		$history['company'] = $history['billing_company'];
		$history['street_address'] = $history['billing_street_address'];
		$history['suburb'] = $history['billing_suburb'];
		$history['city'] = $history['billing_city'];
		$history['postcode'] = $history['billing_postcode'];
		$history['state'] = $history['billing_state'];
		$history['country'] = $history['billing_country'];
      }
	  $tracking_numbers = array();
	  $tracking_number_info_query = tep_db_query("select distinct tracking_number from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$history['orders_id'] . "' and tracking_number <> ''");
	  while ($tracking_number_info = tep_db_fetch_array($tracking_number_info_query)) {
		$tracking_numbers[] = $tracking_number_info['tracking_number'];
	  }
?>

	<fieldset>
	<legend><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL') . '">#' . $history['orders_id'] . ' (' . $history['orders_status_name'] . ') ' . '</a>'; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo TEXT_ORDER_DATE; ?></td>
		<td width="50%"><?php echo tep_date_long($history['date_purchased']); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo TEXT_ORDER_PRODUCTS; ?></td>
		<td><?php echo $products['count'] . ' (' . strip_tags($history['order_total']) . ')'; ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo $order_type; ?></td>
		<td><?php echo tep_output_string_protected(tep_address_format($history['address_format_id'], $history, 1, ' ', ' ')); ?></td>
	  </tr>
<?php
    if (sizeof($tracking_numbers) > 0) {
?>
	  <tr valign="top">
		<td><?php echo TEXT_TRACKING_NUMBER; ?></td>
		<td><?php echo implode('<br />', $tracking_numbers); ?></td>
	  </tr>
<?php
    }
?>
	</table>
	</fieldset>

<?php
    }
?>
	<div id="listing-split">
	  <div style="float: left;"><?php echo $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></div>
	  <div style="text-align: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
	</div>
<?php
  } else {
	echo TEXT_NO_PURCHASES;
  }
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
	</form>
