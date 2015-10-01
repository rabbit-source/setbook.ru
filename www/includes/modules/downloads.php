<?php
  if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
// Get last order id for checkout_success
    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by orders_id desc limit 1");
    $orders = tep_db_fetch_array($orders_query);
    $last_order = $orders['orders_id'];
  } else {
    $last_order = $HTTP_GET_VARS['order_id'];
  }

// Now get all downloadable products in that order
  $downloads_query = tep_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, op.products_name, op.products_id, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = '" . (int)$last_order . "' and o.orders_is_paid = '1' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename <> ''");
  if (tep_db_num_rows($downloads_query) > 0) {
?>
	<fieldset>
	<legend><?php echo HEADING_DOWNLOAD ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<!-- list of products -->
<?php
    while ($downloads = tep_db_fetch_array($downloads_query)) {
// MySQL 3.22 does not have INTERVAL
	  $download_timestamp = time();
	  if ($downloads['download_maxdays'] > 0) {
		$date_finished_query = tep_db_query("select date_added from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$last_order . "' and orders_status_id = '" . DOWNLOAD_ORDERS_STATUS . "' order by date_added desc limit 1");
		$date_finished_info = tep_db_fetch_array($date_finished_query);
		list($dt_year, $dt_month, $dt_day) = explode('-', $date_finished_info['date_added']);
		$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
		$download_expiry = date('Y-m-d H:i:s', $download_timestamp);
	  }
// The link will appear only if:
// - Download remaining count is > 0, AND
// - The file is present in the DOWNLOAD directory, AND EITHER
// - No expiry date is enforced (maxdays == 0), OR
// - The expiry date is not reached
	  $orders_products_filename = DIR_FS_DOWNLOAD . substr(sprintf('%010d', $downloads['products_id']), 0, 6) . '/' . substr(sprintf('%010d', $downloads['products_id']), 0, 8) . '/' . $downloads['orders_products_filename'];

	  $temp_string = '';
	  if ($downloads['download_maxdays'] > 0) $temp_string .= sprintf(TABLE_HEADING_DOWNLOAD_DATE, tep_date_long($download_expiry));
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . sprintf(TABLE_HEADING_DOWNLOAD_COUNT, $downloads['download_count']);

	  echo '	  <tr valign="top">' . "\n" .
	  '		<td width="50">' . $downloads['products_name'] . '</td>' . "\n" .
	  '		<td width="50%">';
      if ( ($downloads['download_count'] > 0) && (file_exists($orders_products_filename)) && ( ($downloads['download_maxdays'] == 0) || $download_timestamp > time()) ) {
		$filename_ext = substr($downloads['orders_products_filename'], strrpos($downloads['orders_products_filename'], '.')+1);
		echo '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads['orders_products_download_id']) . '">' . 'Скачать файл' . ' ' . $filename_ext . '</a> ';
      } else {
		$filename_ext = '';
	  }
	  echo (tep_not_null($filename_ext) ? '(' . $temp_string . ')' : $temp_string) . '</td>' . "\n" . 
	  '	  </tr>' . "\n";
    }
?>
<!-- downloads_eof //-->
	</table>
	</fieldset>
<!-- downloads //-->
<?php
  }
?>