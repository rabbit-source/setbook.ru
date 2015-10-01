<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select advance_orders_id from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	}
  }

  include(DIR_WS_CLASSES . 'order.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $replace_orders_status_names_from = array('Принят', 'Частично доставлен', 'Доставлен', 'Отменён');
  $replace_orders_status_names_to = array('Принята', 'Частично доставлена', 'Доставлена', 'Отменена');
  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name, orders_status_description from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by sort_order");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
	$orders_status['orders_status_name'] = str_replace($replace_orders_status_names_from, $replace_orders_status_names_to, $orders_status['orders_status_name']);
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

		if ($HTTP_POST_VARS['order_blacklist']=='1') {
		  $blacklist_check_query = tep_db_query("select count(*) as total from " . TABLE_BLACKLIST . " where blacklist_ip in (select customers_ip from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "')");
		  $blacklist_check = tep_db_fetch_array($blacklist_check_query);
		  if ($blacklist_check['total'] < 1) {
			tep_db_query("insert into " . TABLE_BLACKLIST . " (blacklist_ip, customers_id, blacklist_comments, date_added, users_id) select customers_ip, customers_id, '" . tep_db_input(tep_db_prepare_input($HTTP_POST_VARS['order_blacklist_reason'])) . "', now(), '" . tep_db_input($REMOTE_USER) . "' from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'");
		  }
		}

        tep_db_query("delete from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'");
        tep_db_query("delete from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'");

        tep_redirect(tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
	  case 'add_history_record':
        $check_status_query = tep_db_query("select customers_name, customers_email_address, advance_orders_status, date_purchased, shops_id from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);

		$shop_info_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_id = '" . (int)$check_status['shops_id'] . "'");
		$shop_info = tep_db_fetch_array($shop_info_query);

		$check_status = array_merge($check_status, $shop_info);

		// Update Status History & Email Customer if Necessary
		$customer_notified = '0';
		if ($check_status['advance_orders_status'] != $status || tep_not_null($comments)) {
		  // Notify Customer
		  if (isset($HTTP_POST_VARS['notify']) && ($HTTP_POST_VARS['notify'] == 'on')) {
			$notify_comments = '';
			if (isset($HTTP_POST_VARS['notify_comments']) && ($HTTP_POST_VARS['notify_comments'] == 'on') && tep_not_null($comments)) {
			  $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
			}

			if (tep_not_null($check_status['shops_database'])) tep_db_select_db($check_status['shops_database']);
			$store_name_info_query = tep_db_query("select configuration_value as store_name from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
			$store_name_info = tep_db_fetch_array($store_name_info_query);
			tep_db_select_db(DB_DATABASE);

			$email = $store_name_info['store_name'] . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . EMAIL_TEXT_ORDER_CHANGED . "\n" . EMAIL_SEPARATOR . "\n" . ($check_status['advance_orders_status']!=$status ? sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]) . "\n\n" : '') . $notify_comments . "\n" . EMAIL_TEXT_PS;

			tep_mail($check_status['customers_name'], $check_status['customers_email_address'], sprintf(EMAIL_TEXT_SUBJECT, $store_name_info['store_name'], $oID), $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
//			tep_mail('Andrey Sivkov', 'sivkov@setbook.ru', sprintf(EMAIL_TEXT_SUBJECT, $store_name_info['store_name'], $oID), $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

			$customer_notified = '1';
		  }
		  tep_db_query("update " . TABLE_ADVANCE_ORDERS . " set advance_orders_status = '" . tep_db_input($status) . "' where advance_orders_id = '" . tep_db_input($oID) . "'");
		}

        $operator = tep_db_prepare_input($REMOTE_USER);
		if ($check_status['advance_orders_status'] != $status || tep_not_null($comments) || tep_not_null($admin_comments)) {
		  tep_db_query("insert into " . TABLE_ADVANCE_ORDERS_STATUS_HISTORY . " (advance_orders_id, advance_orders_status_id, date_added, customer_notified, comments, admin_comments, operator) values ('" . tep_db_input($oID) . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input(tep_db_prepare_input($comments))  . "', '" . tep_db_input(tep_db_prepare_input($admin_comments))  . "', '" . tep_db_input(tep_db_prepare_input($operator)) . "')");
		}

		tep_redirect(tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action')) . 'action=view'));
		break;
	  case 'create_payment_link':
		header('Content-type: text/html; charset=' . CHARSET . '');
		echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
		$ot_total_value = str_replace(',', '.', urldecode($HTTP_GET_VARS['payment_sum']));
		$order_id = $HTTP_GET_VARS['oID'];
		$description = urldecode($HTTP_GET_VARS['payment_description']);
		if ($description=='') $description = 'Предоплата за иностранные товары, заявка #' . $order_id . ' в магазине ' . STORE_NAME;
		$sign = md5(MODULE_PAYMENT_ROBOX_LOGIN . ':' . $ot_total_value . ':' . $order_id . ':' . MODULE_PAYMENT_ROBOX_PASSWORD_1 . ':shp_prefix=aa');
		$link = 'https://merchant.roboxchange.com/Index.aspx?Culture=ru&IncCurrLabel=RUR' .
		'&MrchLogin=' . urlencode(MODULE_PAYMENT_ROBOX_LOGIN) .
		'&OutSum=' . urlencode($ot_total_value) .
		'&InvId=' . $order_id .
		'&Desc=' . urlencode($description) .
		'&SignatureValue=' . urlencode($sign) .
		'&shp_prefix=aa';
		echo tep_draw_textarea_field('payment_link', 'soft', '55', '10', $link) . '<br>' .
		'<a href="' . $link . '" target="_blank"><u>Перейти по ссылке &raquo;</u></a><br><br>';
		require('includes/application_bottom.php');
		die();
    }
  }

  // автоматическое обновление заявок
  $is_default_shop_query = tep_db_query("select shops_default_status from " . TABLE_SHOPS . " where shops_id = '" . (int)SHOP_ID . "'");
  $is_default_shop = tep_db_fetch_array($is_default_shop_query);
  if ($is_default_shop['shops_default_status']=='1' && empty($HTTP_GET_VARS['oID'])) {
	$rows = 0;
	$files = tep_get_files(UPLOAD_DIR . 'changed_orders/', '.csv');
	$new_files = array();
	reset($files);
	while (list($i, $file) = each($files)) {
	  if (substr($file, 0, 2)=='aa') $new_files[] = $file;
	}

	if (sizeof($new_files) > 0) {
//	  tep_set_time_limit(300);

	  $all_shipping_modules = array();
	  $shops_query = tep_db_query("select shops_id from " . TABLE_SHOPS . " where 1");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		$all_shipping_modules[$shops['shops_id']] = tep_get_shipping_modules($shops['shops_id']);
	  }

	  $statuses_asc = array();
	  $statuses_query = tep_db_query("select orders_status_id, sort_order from " . TABLE_ORDERS_STATUS . " order by sort_order");
	  while ($statuses = tep_db_fetch_array($statuses_query)) {
		$statuses_asc[$statuses['orders_status_id']] = $statuses['sort_order'];
	  }

	  $operator = 'robot';

	  reset($new_files);
	  while (list($i, $file) = each($new_files)) {
		if ($fp = @fopen(UPLOAD_DIR . 'changed_orders/' . $file, 'r')) {
		  list($update_order_id, $update_customer_address, $update_customer_telephone, $update_customer_name, $update_customer_email_address, $update_order_currency, $update_order_currency_value, $comments, $update_order_status, $update_shipping_cost, $update_shipping_method) = fgetcsv($fp, '10000', ';');
		  $order_check_query = tep_db_query("select advance_orders_id, advance_orders_status, shops_id from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$update_order_id . "'");

		  if (tep_db_num_rows($order_check_query) > 0 && $update_order_status > 0) {
			$order_check = tep_db_fetch_array($order_check_query);
			$shipping_modules = $all_shipping_modules[$order_check['shops_id']];
			$shipping_method = $shipping_modules[$update_shipping_method];
			$shipping_cost = $update_shipping_cost;

			$sql_data_array = array('last_modified' => 'now()',
									'customers_name' => $update_customer_name,
									'customers_email_address' => $update_customer_email_address,
									'customers_telephone' => $update_customer_telephone,
									'customers_address' => $update_customer_address,
									'currency' => $update_order_currency,
									'currency_value' => $update_order_currency_value,
									'shipping_method_class' => $update_shipping_method,
									'shipping_method' => $shipping_method,
									'shipping_cost' => $shipping_cost,
									);
			tep_db_perform(TABLE_ADVANCE_ORDERS, $sql_data_array, 'update', "advance_orders_id = '" . (int)$update_order_id . "'");

			$order_products = array();
			while ( (list($products_model, $order_products_id, $products_name, $products_author, $products_manufacturer, $products_qty, $products_price, $products_url) = fgetcsv($fp, '10000', ';')) !== false) {
			  if ((int)$products_qty > 0) {
				$products_price = str_replace(',', '.', (float)$products_price);
				$products_qty = (int)$products_qty;

				$sql_data_array = array('advance_orders_id' => $update_order_id,
										'products_name' => $products_name,
										'products_author' => $products_author,
										'products_model' => $products_model,
										'products_manufacturer' => $products_manufacturer,
										'products_url' => $products_url,
										'products_price' => $products_price,
										'currency' => $update_order_currency,
										'currency_value' => $update_order_currency_value,
										'products_quantity' => $products_qty,
										);
				$order_product_check_query = tep_db_query("select count(*) as total from " . TABLE_ADVANCE_ORDERS_PRODUCTS . " where advance_orders_id = '" . (int)$update_order_id . "' and advance_orders_products_id = '" . (int)$order_products_id . "'");
				$order_product_check = tep_db_fetch_array($order_product_check_query);
				if ($order_product_check['total'] > 0) {
				  tep_db_perform(TABLE_ADVANCE_ORDERS_PRODUCTS, $sql_data_array, 'update', "advance_orders_id = '" . (int)$update_order_id . "' and advance_orders_products_id = '" . (int)$order_products_id . "'");
				} else {
				  tep_db_perform(TABLE_ADVANCE_ORDERS_PRODUCTS, $sql_data_array);
				  $order_products_id = tep_db_insert_id();
				}
				$order_products[] = $order_products_id;
			  }
			}

			tep_db_query("delete from " . TABLE_ADVANCE_ORDERS_PRODUCTS . " where advance_orders_id = '" . (int)$update_order_id . "' and advance_orders_products_id not in ('" . implode("', '", $order_products) . "')");

			if ($update_order_status==0 || ($update_order_status > 0 && $statuses_asc[$update_order_status] < $statuses_asc[$order_check['advance_orders_status']]) ) {
			  $update_order_status = $order_check['advance_orders_status'];
			}

			if (tep_not_null($comments)) {
			  $history_check_query = tep_db_query("select count(*) as total from " . TABLE_ADVANCE_ORDERS_STATUS_HISTORY . " where advance_orders_id = '" . (int)$update_order_id . "' and comments = '" . tep_db_input($comments) . "'");
			  $history_check = tep_db_fetch_array($history_check_query);
			  if ($history_check['total'] > 0) $comments = '';
			}

			if ($order_check['advance_orders_status']!=$update_order_status || tep_not_null($comments) || tep_not_null($admin_comments)) {
			  tep_db_query("insert into " . TABLE_ADVANCE_ORDERS_STATUS_HISTORY . " (advance_orders_id, advance_orders_status_id, date_added, customer_notified, comments, admin_comments, operator) values ('" . (int)$update_order_id . "', '" . (int)$update_order_status . "', now(), '0', '" . tep_db_input($comments)  . "', '" . tep_db_input($admin_comments)  . "', '" . tep_db_input($operator) . "')");
			  if ($order_check['advance_orders_status']!=$update_order_status) {
				tep_db_query("update " . TABLE_ADVANCE_ORDERS . " set advance_orders_status = '" . (int)$update_order_status . "' where advance_orders_id = '" . (int)$update_order_id . "'");
			  }
			}
		  }
		  fclose($fp);
		  @unlink(UPLOAD_DIR . 'changed_orders/' . $file);
		}
	  }
	}
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
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
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
<?php
  if (($action == 'view') && ($order_exists == true)) {
	$order_info_query = tep_db_query("select * from " . TABLE_ADVANCE_ORDERS . " where advance_orders_id = '" . (int)$oID . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
	$order_info = tep_db_fetch_array($order_info_query);
	$shop_info_query = tep_db_query("select shops_url from " . TABLE_SHOPS . " where shops_id = '" . (int)$order_info['shops_id'] . "'");
	$shop_info = tep_db_fetch_array($shop_info_query);
	if (!is_array($shop_info)) $shop_info = array();
	$order_info = array_merge($order_info, $shop_info);
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(HEADING_TITLE_1, $oID); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
	  <tr>
		<td><?php echo tep_draw_separator(); ?></td>
	  </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr valign="top">
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="200"><strong><?php echo ENTRY_CUSTOMER_NAME; ?></strong></td>
                <td class="main"><?php echo $order_info['customers_name']; ?></td>
              </tr>
              <tr valign="top">
                <td class="main" width="200"><strong><?php echo ENTRY_CUSTOMER_ADDRESS; ?></strong></td>
                <td class="main"><?php echo $order_info['customers_address']; ?></td>
              </tr>
              <tr>
                <td class="main" width="200"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order_info['customers_telephone']; ?></td>
              </tr>
              <tr>
                <td class="main" width="200"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order_info['customers_email_address'] . '"><u>' . $order_info['customers_email_address'] . '</u></a>'; ?></td>
              </tr>
              <tr>
                <td class="main" width="200"><strong><?php echo ENTRY_COMMENTS; ?></strong></td>
                <td class="main"><?php echo $order_info['comments']; ?></td>
              </tr>
              <tr>
                <td class="main" width="200"><strong><?php echo ENTRY_DOMAIN; ?></strong></td>
                <td class="main"><?php echo str_replace('http://', '', $order_info['shops_url']); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2">
          <tr class="dataTableHeadingRow" align="center">
            <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MANUFACTURER; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UNIT_PRICE; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_QTY; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TOTAL; ?></td>
          </tr>
<?php
	$i = 0;
	$subtotal_sum = 0;
	$total_sum = 0;
	$orders_products_query = tep_db_query("select * from " . TABLE_ADVANCE_ORDERS_PRODUCTS . " where advance_orders_id = '" . (int)$oID . "'");
    while ($orders_products = tep_db_fetch_array($orders_products_query)) {
	  $manufacturer_string = $orders_products['products_manufacturer'];
	  if ($orders_products['products_year']>0) $manufacturer_string .= (tep_not_null($manufacturer_string) ? ', ' : '') . $orders_products['products_year'];
	  if (tep_not_null($orders_products['products_url']) && substr($orders_products['products_url'], 0, 7)!='http://') $orders_products['products_url'] = 'http://' . $orders_products['products_url'];
      echo '          <tr class="dataTableRow" align="center">' . "\n" .
           '            <td class="dataTableContent" align="left">' . ($i+1) . '.&nbsp;[' . $orders_products['advance_orders_products_id'] . ']&nbsp;' . (tep_not_null($orders_products['products_url']) ? '<a href="' . $orders_products['products_url'] . '" target="_blank"><u>' . $orders_products['products_name'] . '</u></a>' : $orders_products['products_name']) . '</td>' . "\n" .
           '            <td class="dataTableContent">' . $manufacturer_string . '</td>' . "\n" .
           '            <td class="dataTableContent"><nobr>' . $orders_products['products_model'] . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr>' . $currencies->format($orders_products['products_price'], true, $orders_products['currency'], $orders_products['currency_value']) . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent"><nobr>' . $orders_products['products_quantity'] . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr><strong>' . $currencies->format($orders_products['products_price']*$orders_products['products_quantity'], true, $orders_products['currency'], $orders_products['currency_value']) . ($orders_products['currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($orders_products['products_price']) . ')' : '') . '</strong></nobr></td>' . "\n" .
      '          </tr>' . "\n";
	  $i ++;
	  $subtotal_sum += $orders_products['products_price']*$orders_products['products_quantity'];
	  $total_sum = $subtotal_sum + $order_info['shipping_cost'];
    }
?>
          <tr valign="top">
            <td colspan="2" class="main"><br><a href="#" onclick="document.getElementById('payment_table').style.display = (document.getElementById('payment_table').style.display=='none' ? 'table' : 'none'); return false;"><strong><?php echo 'Сформировать ссылку на оплату заявки электронным платежом'; ?></strong></a><br><br>
			<?php echo tep_draw_form('payment', '#'); ?><table border="0" cellspacing="0" cellpadding="2" id="payment_table" style="display: none;">
			  <tr>
				<td><?php echo 'Сумма платежа:'; ?></td>
				<td><?php echo tep_draw_input_field('payment_sum', (string)round($total_sum, $currencies->get_decimal_places($order_info['currency'])), 'size="4" style="text-align: right;"') . 'руб.'; ?></td>
			  </tr>
			  <tr>
				<td><?php echo 'Описание платежа:'; ?></td>
				<td><?php echo tep_draw_textarea_field('payment_description', 'soft', '55', '4', 'Предоплата за иностранные товары, заявка #' . $oID); ?></td>
			  </tr>
			  <tr id="payment_link_div" style="display: none;">
				<td><?php echo 'Ссылка на оплату:'; ?></td>
				<td><div id="payment_link"></div></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action'))) . '#">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW, 'onclick="getXMLDOM(\'' . tep_href_link(FILENAME_ADVANCE_ORDERS, 'action=create_payment_link&oID=' . $oID . '') . '&payment_sum=\'+encodeURL(document.payment.payment_sum.value)+\'&payment_description=\'+encodeURL(document.payment.payment_description.value), \'payment_link\'); document.getElementById(\'payment_link_div\').style.display = \'\'; return false;"') . '</a>'; ?></td>
			  </tr>
            </table></form></td>
            <td align="right" colspan="4"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td class="smallText" align="right"><?php echo ENTRY_SUB_TOTAL; ?></td>
				<td class="smallText" align="right"><?php echo $currencies->format($subtotal_sum); ?></td>
			  </tr>
<?php
	if (tep_not_null($order_info['shipping_method'])) {
?>
			  <tr>
				<td class="smallText" align="right"><?php echo $order_info['shipping_method'] . (substr($order_info['shipping_method'], -1)!=':' ? ':' : ''); ?></td>
				<td class="smallText" align="right"><?php echo $currencies->format($order_info['shipping_cost']); ?></td>
			  </tr>
			  <tr>
				<td class="smallText" align="right"><strong><?php echo ENTRY_TOTAL; ?></strong></td>
				<td class="smallText" align="right"><strong><?php echo $currencies->format($total_sum); ?></strong></td>
			  </tr>
<?php
	}
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
	  <tr>
		<td colspan="6" class="main"><br><table border="0" cellspacing="1" cellpadding="5">
		  <tr class="dataTableHeadingRow" align="center">
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></strong></td>
			<td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_OPERATOR; ?></strong></td>
		  </tr>
<?php
	$orders_history_query = tep_db_query("select * from " . TABLE_ADVANCE_ORDERS_STATUS_HISTORY . " where advance_orders_id = '" . tep_db_input($oID) . "' order by date_added");
	if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
		$users_query = tep_db_query("select users_name from " . TABLE_USERS . " where users_id = '" . tep_db_input($orders_history['operator']) . "'");
		$users = tep_db_fetch_array($users_query);
        echo '		  <tr class="dataTableRow" align="center">' . "\n" .
             '			<td class="dataTableContent">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '			<td class="dataTableContent">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '			<td class="dataTableContent">' . $orders_status_array[$orders_history['advance_orders_status_id']] . '</td>' . "\n" .
			 '			<td class="dataTableContent" align="left">' . nl2br($orders_history['comments']) . '&nbsp;</td>' . "\n" .
			 '			<td class="dataTableContent" align="left">' . nl2br($orders_history['admin_comments']) . '&nbsp;</td>' .
			 '			<td class="dataTableContent">' . $users['users_name'] . '&nbsp;</td>' . "\n" . "\n" .
			 '		  </tr>' . "\n";
      }
    } else {
        echo '		  <tr class="dataTableRow">' . "\n" .
             '			<td class="dataTableContent" colspan="6" align="center">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '		  </tr>' . "\n";
    }
?>
		</table><br>
		<a href="#" onclick="document.getElementById('history_table').style.display = (document.getElementById('history_table').style.display=='none' ? 'table' : 'none'); return false;"><strong><?php echo 'Изменить статус заявки / Добавить комментарий'; ?></strong></a><br><br>
		<?php echo tep_draw_form('history', FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action')) . 'action=add_history_record'); ?><table border="0" cellspacing="0" cellpadding="0" id="history_table" style="display: none;">
		  <tr>
			<td class="main"><?php echo TABLE_HEADING_COMMENTS; ?></td>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
			<td class="main"><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></td>
		  </tr>
		  <tr valign="top">
			<td class="main"><?php echo tep_draw_textarea_field('comments', 'soft', '50', '5', $order->info['comments']); ?></td>
			<td>&nbsp;</td>
			<td class="main"><?php echo tep_draw_textarea_field('admin_comments', 'soft', '50', '5'); ?></td>
		  </tr>
		  <tr>
			<td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr>
				<td width="170" class="main"><nobr><?php echo ENTRY_STATUS; ?></nobr></td>
				<td>&nbsp;<?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
			  </tr>
			  <tr>
				<td class="main"><nobr><?php echo ENTRY_NOTIFY_CUSTOMER; ?></nobr></td>
				<td>&nbsp;<?php echo tep_draw_checkbox_field('notify', 'on', false); ?></td>
			  </tr>
			  <tr>
				<td class="main"><nobr><?php echo ENTRY_NOTIFY_COMMENTS; ?></nobr></td>
				<td>&nbsp;<?php echo tep_draw_checkbox_field('notify_comments', 'on', true); ?></td>
			  </tr>
			  <tr>
				<td colspan="3"><br><?php echo tep_image_submit('button_new_record.gif', IMAGE_NEW_RECORD); ?>&nbsp;</td>
			  </tr>
			</table></form></td>
		  </tr>
		</table></td>
	  </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } else {
	$form_get_variables_string = '';
	reset($HTTP_GET_VARS);
	while (list($k, $v) = each($HTTP_GET_VARS)) {
	  if (!in_array($k, array('oID', 'search', 'status')) && tep_not_null($v)) $form_get_variables_string .= tep_draw_hidden_field($k, $v);
	}
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" cellspacing="0" cellpadding="0">
              <tr><?php echo tep_draw_form('orders', FILENAME_ADVANCE_ORDERS, '', 'get', 'onkeypress="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; if (this.elements[\'page\']) this.elements[\'page\'].value = \'\'; this.submit(); }" onkeydown="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; if (this.elements[\'page\']) this.elements[\'page\'].value = \'\'; this.submit(); }" onkeyup="if (event.keyCode==13) { if (this.oID.value) this.elements[\'action\'].value = \'view\'; if (this.elements[\'page\']) this.elements[\'page\'].value = \'\'; this.submit(); }"') . $form_get_variables_string; ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12" onkeypress="if (event.which==13) { this.form.elements[\'action\'].value = \'view\'; if (this.form.elements[\'page\']) this.form.elements[\'page\'].value = \'\'; this.form.submit(); }"') . tep_draw_hidden_field('action', ''); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_CUSTOMER . ' ' . tep_draw_input_field('search', '', 'size="12" onkeypress="if (event.which==13) { if (this.form.elements[\'page\']) this.form.elements[\'page\'].value = \'\'; this.form.submit(); }"'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="if (this.form.elements[\'page\']) this.form.elements[\'page\'].value = \'\'; this.form.submit();"'); ?></td>
              </form>
			  </tr>  
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr valign="top">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr valign="top">
			<td><table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COMMENTS; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
			  </tr>
<?php
	$orders_query_raw .= "select o.advance_orders_id from " . TABLE_ADVANCE_ORDERS . " o where 1";
    if (tep_not_null($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw .= " and o.customers_id = '" . (int)$cID . "'";
    }
    if (tep_not_null($HTTP_GET_VARS['status'])) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw .= " and o.advance_orders_status = '" . (int)$status . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['search'])) {
	  $orders_query_raw .= " and (";

      $search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	  $fields = array('o.customers_name', 'o.customers_address', 'o.customers_telephone', 'o.customers_email_address');
	  $orders_query_array = array();
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		$orders_query_array[] = $field . " like '%" . tep_db_input(str_replace(' ', "%' and " . $field . " like '%", $search)) . "%'";
	  }
	  $orders_query_raw .= " (" . implode(" or ", $orders_query_array) . ")";

	  $orders_by_products = array();
	  $fields = array('products_name', 'products_author', 'products_model', 'products_manufacturer', 'products_url');
	  $orders_query_array = array();
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		$orders_query_array[] = $field . " like '%" . tep_db_input(str_replace(' ', "%' and " . $field . " like '%", $search)) . "%'";
	  }
	  $orders_by_products_query = tep_db_query("select distinct advance_orders_id from " . TABLE_ADVANCE_ORDERS_PRODUCTS . " where 1 and (" . implode(" or ", $orders_query_array) . ")");
	  while ($orders_by_products_row = tep_db_fetch_array($orders_by_products_query)) {
		$orders_by_products[] = $orders_by_products_row['advance_orders_id'];
	  }
	  if (sizeof($orders_by_products) > 0) {
		$orders_query_raw .= " or o.advance_orders_id in ('" . implode("', '", $orders_by_products) . "')";
	  }

	  $orders_query_raw .= " )";
    }
	if (sizeof($allowed_shops_array) > 0) $orders_query_raw .= " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')";
	$orders_query_raw .= " order by o.advance_orders_id desc";

    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], 25, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
	  $order_info_query = tep_db_query("select o.* from " . TABLE_ADVANCE_ORDERS . " o where o.advance_orders_id = '" . (int)$orders['advance_orders_id'] . "'");
	  $order_info = tep_db_fetch_array($order_info_query);
	  $order_sum_query = tep_db_query("select sum(products_price * products_quantity) as total_sum from " . TABLE_ADVANCE_ORDERS_PRODUCTS . " where advance_orders_id = '" . (int)$orders['advance_orders_id'] . "'");
	  $order_sum = tep_db_fetch_array($order_sum_query);
	  $order_info = array_merge($order_info, $order_sum);
	  if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $order_info['advance_orders_id']))) && !isset($oInfo)) {
		$oInfo = new objectInfo($order_info);
	  }

      if (isset($oInfo) && is_object($oInfo) && ($order_info['advance_orders_id'] == $oInfo->advance_orders_id)) {
        echo '			  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->advance_orders_id . '&action=view') . '\'">' . "\n";
      } else {
        echo '			  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['advance_orders_id']) . '\'">' . "\n";
      }
?>
				<td class="dataTableContent" nowrap="nowrap"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $order_info['advance_orders_id'] . '&action=view') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;[' . $order_info['advance_orders_id'] . '] ' . $order_info['customers_name']; ?></td>
				<td class="dataTableContent" align="right" nowrap="nowrap"><?php echo $currencies->format($order_info['total_sum']); ?></td>
				<td class="dataTableContent" align="center" nowrap="nowrap"><?php echo substr(tep_datetime_short($order_info['date_purchased']), 0, -3); ?></td>
				<td class="dataTableContent" align="center"><?php echo strip_tags($order_info['comments']); ?></td>
				<td class="dataTableContent" align="center"><?php echo $orders_status_array[$order_info['advance_orders_status']]; ?></td>
				<td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($order_info['advance_orders_id'] == $oInfo->advance_orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['advance_orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
    }
?>
			  <tr>
				<td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, 25, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
					<td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, 25, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
				  </tr>
				</table></td>
			  </tr>
			</table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->advance_orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $oInfo->customers_name . '</strong>');
      if (tep_not_null($oInfo->customers_ip)) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('order_blacklist', '1', false, '', 'onclick="if (this.checked) document.getElementById(\'order_blacklist_comment\').style.display = \'block\'; else document.getElementById(\'order_blacklist_comment\').style.display = \'none\';"') . ' ' . TEXT_DELETE_ORDER_BLACKLIST . '<div id="order_blacklist_comment" style="display: none;"><br>' . TEXT_DELETE_ORDER_BLACKLIST_COMMENTS . '<br>' . tep_draw_input_field('order_blacklist_reason', TEXT_DELETE_ORDER_BLACKLIST_COMMENTS_DEFAULT, 'size="35"') . '</div>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->advance_orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->advance_orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->advance_orders_id . '&action=view') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_ADVANCE_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->advance_orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
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