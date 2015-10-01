<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if ($HTTP_GET_VARS['action']=='get_letmeprint_books') {
    function sendTransactionToGateway($url, $nvpStr_) {
	  // Set the curl parameters.
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_VERBOSE, 1);

	  // Turn off the server and peer verification (TrustManager Concept).
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_POST, 1);

	  // Set the request as a POST FIELD for curl.
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpStr_);

	  // Get response from the server.
	  $httpResponse = curl_exec($ch);

	  if (!$httpResponse) {
		die("$methodName_ failed: " . curl_error($ch) . ' (' . curl_errno($ch) . ')');
	  }

	  // Extract the response details.
	  $httpResponseAr = explode("&", $httpResponse);

	  $httpParsedResponseAr = array();
	  foreach($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if (sizeof($tmpAr) > 1) {
		  $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	  }

	  if ((0 == sizeof($httpParsedResponseAr))) {
		exit('Invalid HTTP Response for POST request(' . $nvpStr_ . ') to ' . $url . '.');
	  }

//	  tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'credit card transaction', str_replace('=', ' = ', str_replace('&', "\n", urldecode($nvpStr_))) . "\n\n" . str_replace('=', ' = ', str_replace('&', "\n", urldecode($httpResponse))), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	  return $httpResponse;
	}

	set_time_limit(300);
	$from_date = trim($HTTP_GET_VARS['from_date']);
	if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $from_date)) $from_date = '0000-00-00';
	$data = array('<?xml version="1.0" encoding="utf-8"?>' . "\n" .
	'<request>' . "\n" .
	'<operation>get_incremental_update</operation>' . "\n" .
	'<last_update_time>' . $from_date . ' 00:00:00</last_update_time>' . "\n" .
	'</request>' => '');
	$url = 'https://api.letmeprint.ru:443/api/shop/key/d19177552788ff64af9cca8273cdf596';
	//api.letmeprint.ru/api/shop/key/
//	echo $result = tep_request_html($url, 'POST', $data);
	$nvpStr_ = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
	'<request>' . "\n" .
	'<operation>get_incremental_update</operation>' . "\n" .
	'<last_update_time>' . $from_date . ' 00:00:00</last_update_time>' . "\n" .
	'</request>';
	$result = sendTransactionToGateway($url, $nvpStr_);
	$fp = fopen(UPLOAD_DIR . 'csv/books.xml', 'w');
	fwrite($fp, $result);
	fclose($fp);
	die('OK');
  } elseif ($HTTP_GET_VARS['action']=='upload_order') {
	$order_id = (int)$HTTP_GET_VARS['order_id'];
	include(DIR_WS_CLASSES . 'order.php');
	tep_upload_order($order_id);
	die('OK');
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
		  <tr valign="top">
			<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
			<td><table border="0" width="250" cellspacing="0" cellpadding="0" class="columnLeft">
			  <tr>
				<td>
<?php
  $orders_contents = '';
  $orders_status_query = tep_db_query("select orders_status_name, orders_status_id from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' order by sort_order");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_pending_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . $orders_status['orders_status_id'] . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
    $orders_pending = tep_db_fetch_array($orders_pending_query);
    $orders_contents .= '<a href="' . tep_href_link(FILENAME_ORDERS, 'selected_box=orders&status=' . $orders_status['orders_status_id']) . '">' . $orders_status['orders_status_name'] . '</a>: ' . $orders_pending['count'] . '<br>';
  }

  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="infoBoxHeading"',
                     'text'  => BOX_TITLE_ORDERS);

  $contents[] = array('params' => 'class="infoBoxContent"',
                      'text'  => $orders_contents);

  $box = new box;
  echo $box->menuBox($heading, $contents);
?></td>
			  </tr>
			</table></td>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
			<td><table border="0" width="250" cellspacing="0" cellpadding="0" class="columnLeft">
			  <tr>
				<td>
<?php
  $shops_array = array();
  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . (sizeof($allowed_shops_array)>0 ? " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
  while ($shops = tep_db_fetch_array($shops_query)) {
	$shops_array[$shops['shops_id']] = str_replace('http://', '', str_replace('www.', '', $shops['shops_url']));
  }

  $total_today_orders_count = 0;
  $total_today_orders_sum = 0;
  $total_orders_string = BOX_ENTRY_TODAY;
  $temp_string = '';
  reset($shops_array);
  while (list($shops_id, $shops_url) = each($shops_array)) {
	$today_orders_query = tep_db_query("select count(*) as orders_count, sum(orders_total) as total_sum from " . TABLE_ORDERS . " where date_format(date_purchased, '%Y-%m-%d') = '" . date('Y-m-d') . "' and orders_status <> '10' and shops_id = '" . (int)$shops_id . "'");
	$today_orders = tep_db_fetch_array($today_orders_query);
	$total_today_orders_count += $today_orders['orders_count'];
	$total_today_orders_sum += $today_orders['total_sum'];
	$temp_string .= '<tr><td>' . $shops_url . ':</td><td>&nbsp;</td><td align="center">' . $today_orders['orders_count'] . '</td><td>&nbsp;</td><td align="right">' . $currencies->format($today_orders['total_sum']) . '</td></tr>';
  }
  $total_orders_string .= ' ' . $total_today_orders_count . ' (' . $currencies->format($total_today_orders_sum) . ')';
  if (sizeof($allowed_shops_array)!=1) $total_orders_string .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 20px;">' . $temp_string . '</table>';

  $total_yesterday_orders_count = 0;
  $total_yesterday_orders_sum = 0;
  $total_orders_string .= '<br>' . BOX_ENTRY_YESTERDAY;
  $temp_string = '';
  reset($shops_array);
  while (list($shops_id, $shops_url) = each($shops_array)) {
	$yesterday_orders_query = tep_db_query("select count(*) as orders_count, sum(orders_total) as total_sum from " . TABLE_ORDERS . " where date_format(date_purchased, '%Y-%m-%d') = '" . date('Y-m-d', time()-60*60*24) . "' and orders_status <> '10' and shops_id = '" . (int)$shops_id . "'");
	$yesterday_orders = tep_db_fetch_array($yesterday_orders_query);
	$total_yesterday_orders_count += $yesterday_orders['orders_count'];
	$total_yesterday_orders_sum += $yesterday_orders['total_sum'];
	$temp_string .= '<tr><td>' . $shops_url . ':</td><td>&nbsp;</td><td align="center">' . $yesterday_orders['orders_count'] . '</td><td>&nbsp;</td><td align="right">' . $currencies->format($yesterday_orders['total_sum']) . '</td></tr>';
  }
  $total_orders_string .= ' ' . $total_yesterday_orders_count . ' (' . $currencies->format($total_yesterday_orders_sum) . ')';
  if (sizeof($allowed_shops_array)!=1) $total_orders_string .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 20px;">' . $temp_string . '</table>';

  $total_week_orders_count = 0;
  $total_week_orders_sum = 0;
  $total_orders_string .= '<br>' . BOX_ENTRY_WEEK;
  $temp_string = '';
  reset($shops_array);
  while (list($shops_id, $shops_url) = each($shops_array)) {
	$week_orders_query = tep_db_query("select count(*) as orders_count, sum(orders_total) as total_sum from " . TABLE_ORDERS . " where date_format(date_purchased, '%Y-%m-%d') >= '" . date('Y-m-d', time()-60*60*24*7) . "' and date_format(date_purchased, '%Y-%m-%d') < '" . date('Y-m-d') . "' and orders_status <> '10' and shops_id = '" . (int)$shops_id . "'");
	$week_orders = tep_db_fetch_array($week_orders_query);
	$total_week_orders_count += $week_orders['orders_count'];
	$total_week_orders_sum += $week_orders['total_sum'];
	$temp_string .= '<tr><td>' . $shops_url . ':</td><td>&nbsp;</td><td align="center">' . $week_orders['orders_count'] . '</td><td>&nbsp;</td><td align="right" nowrap="nowrap">' . $currencies->format($week_orders['total_sum']) . '</td></tr>';
  }
  $total_orders_string .= ' ' . $total_week_orders_count . ' (' . $currencies->format($total_week_orders_sum) . ')';
  if (sizeof($allowed_shops_array)!=1) $total_orders_string .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 20px;">' . $temp_string . '</table>';

  $total_month_orders_count = 0;
  $total_month_orders_sum = 0;
  $total_orders_string .= '<br>' . BOX_ENTRY_MONTH;
  $temp_string = '';
  reset($shops_array);
  while (list($shops_id, $shops_url) = each($shops_array)) {
	$month_orders_query = tep_db_query("select count(*) as orders_count, sum(orders_total) as total_sum from " . TABLE_ORDERS . " where date_format(date_purchased, '%Y-%m-%d') >= '" . date('Y-m-d', time()-60*60*24*30) . "' and date_format(date_purchased, '%Y-%m-%d') < '" . date('Y-m-d') . "' and orders_status <> '10' and shops_id = '" . (int)$shops_id . "'");
	$month_orders = tep_db_fetch_array($month_orders_query);
	$total_month_orders_count += $month_orders['orders_count'];
	$total_month_orders_sum += $month_orders['total_sum'];
	$temp_string .= '<tr><td>' . $shops_url . ':</td><td>&nbsp;</td><td align="center">' . $month_orders['orders_count'] . '</td><td>&nbsp;</td><td align="right" nowrap="nowrap">' . $currencies->format($month_orders['total_sum']) . '</td></tr>';
  }
  $total_orders_string .= ' ' . $total_month_orders_count . ' (' . $currencies->format($total_month_orders_sum) . ')';
  if (sizeof($allowed_shops_array)!=1) $total_orders_string .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 20px;">' . $temp_string . '</table>';

  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="infoBoxHeading"',
                     'text'  => BOX_TITLE_TOTAL);

  $contents[] = array('params' => 'class="infoBoxContent"',
                      'text'  => $total_orders_string);

  $box = new box;
  echo $box->menuBox($heading, $contents);
?></td>
			  </tr>
			</table></td>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
			<td><table border="0" width="250" cellspacing="0" cellpadding="0" class="columnLeft">
			  <tr>
				<td>
<?php
  $customers_query = tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS. (sizeof($allowed_shops_array)>0 ? " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
  $customers = tep_db_fetch_array($customers_query);
  $products_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS . " p");
  $products = tep_db_fetch_array($products_query);
  $active_products_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS . " p where p.products_listing_status = '1'");
  $active_products = tep_db_fetch_array($active_products_query);

  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="infoBoxHeading"',
                     'text'  => BOX_TITLE_STATISTICS);

  $contents[] = array('params' => 'class="infoBoxContent"',
                      'text'  => BOX_ENTRY_CUSTOMERS . ' ' . $customers['count'] . '<br>' .
                                 BOX_ENTRY_PRODUCTS . ' ' . $products['count'] . ' (' . $active_products['count'] . ')');

  $box = new box;
  echo $box->menuBox($heading, $contents);
?></td>
			  </tr>
			</table></td>
		  </tr>
		</table>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>