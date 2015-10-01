<?php
  require('includes/application_top.php');

  define('FILENAME_TEMP_ORDERS', 'temp_orders.php');
  define('TABLE_TEMP_ORDERS', 'temp_orders');
  define('TABLE_TEMP_ORDERS_PRODUCTS', 'temp_orders_products');
  define('TABLE_TEMP_ORDERS_STATUS_HISTORY', 'temp_orders_status_history');
  define('TABLE_TEMP_ORDERS_TOTAL', 'temp_orders_total');

  $shops_array = array(array('id' => '', 'text' => TEXT_ALL_SHOPS));
  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " order by sort_order");
  while ($shops = tep_db_fetch_array($shops_query)) {
	$shops_array[] = array('id' => $shops['shops_id'], 'text' => str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])));
  }

  function tep_get_country_id($country_name) {
	$country_id_query = tep_db_query("select countries_id from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "' limit 1");
	if (tep_db_num_rows($country_id_query) > 0) {
	  $country_id_row = tep_db_fetch_array($country_id_query);
	  return $country_id_row['countries_id'];
	}

	return 0;
  }

  function tep_get_country_iso_code_2($country_id) {
	$country_iso_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "' limit 1");
	if (tep_db_num_rows($country_iso_query) > 0) {
	  $country_iso_row = tep_db_fetch_array($country_iso_query);
	  return $country_iso_row['countries_iso_code_2'];
	}

	return 0;
  }

  function tep_get_zone_id($country_id, $zone_name) {
	$zone_id_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_name = '" . tep_db_input($zone_name) . "'");

	if (tep_db_num_rows($zone_id_query) > 0) {
	  $zone_id_row = tep_db_fetch_array($zone_id_query);
	  return $zone_id_row['zone_id'];
    }
	return 0;
  }

  function tep_html_quotes($string) {
	return str_replace("'", "&#039;", $string);
  }

  function tep_html_unquote($string) {
	return str_replace("&#039;", "'", $string);
  }

  reset($HTTP_GET_VARS);
  while (list($k, $v) = each($HTTP_GET_VARS)) {
	if (empty($v)) unset($HTTP_GET_VARS[$k]);
  }

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_TEMP_ORDERS . " where (orders_id = '" . (int)$oID . "' or orders_code = '" . tep_db_input($oID) . "')" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
    $order_exists = true;
    if (tep_db_num_rows($orders_query) < 1) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	} else {
	  $orders = tep_db_fetch_array($orders_query);
	  $HTTP_GET_VARS['oID'] = $orders['orders_id'];
	}
  }

  class order {
    var $info, $totals, $products, $customer, $delivery;

    function order($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
	  global $languages_id;
      $order_query = tep_db_query("select *, if(delivery_transfer>0, datediff(delivery_transfer, date_purchased), 0) as delivery_transfer_days from " . TABLE_TEMP_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      $order = tep_db_fetch_array($order_query);
	  if (!is_array($order)) $order = array();

	  $company = array();
	  reset($order);
	  while (list($k, $v) = each($order)) {
		if (strpos($k, 'customers_company_')!==false) {
		  unset($order[$k]);
		  $k = str_replace('customers_company_', 'company_', $k);
		  $k = str_replace('company_full_name', 'company_full', $k);
		  $k = str_replace('company_name', 'company', $k);
		  $company[$k] = $v;
		}
	  }

	  $comments_query = tep_db_query("select comments from " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$insert_id . "' order by orders_status_history_id limit 1");
	  $comments = tep_db_fetch_array($comments_query);

      $totals_query = tep_db_query("select class, title, text, value from " . TABLE_TEMP_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
      while ($totals = tep_db_fetch_array($totals_query)) {
        $this->totals[] = array('class' => $totals['class'],
								'title' => $totals['title'],
                                'text' => $totals['text'],
								'value' => $totals['value']);
      }

      $this->info = array('id' => $order['orders_id'],
						  'code' => $order['orders_code'],
						  'currency' => $order['currency'],
                          'currency_value' => $order['currency_value'],
                          'payment_method' => $order['payment_method'],
                          'payment_method_class' => $order['payment_method_class'],
                          'cc_type' => $order['cc_type'],
                          'cc_owner' => $order['cc_owner'],
                          'cc_number' => $order['cc_number'],
                          'cc_expires' => $order['cc_expires'],
                          'check_account_type' => $order['check_account_type'],
                          'check_bank_name' => $order['check_bank_name'],
                          'check_routing_number' => $order['check_routing_number'],
                          'check_account_number' => $order['check_account_number'],
                          'date_purchased' => $order['date_purchased'],
                          'orders_status' => $order['orders_status'],
                          'last_modified' => $order['last_modified'],
						  'payer_requisites' => $order['payer_requisites'],
						  'self_delivery' => $order['delivery_self_address'],
						  'delivery_transfer' => $order['delivery_transfer'],
						  'delivery_transfer_days' => $order['delivery_transfer_days'],
						  'comments' => $comments['comments'],
						  'enabled_ssl' => $order['orders_ssl_enabled'],
						  'shops_id' => $order['shops_id']);

      $this->customer = array('type' => (tep_not_null($order['customers_company']) ? 'corporate' : 'private'),
							  'name' => $order['customers_name'],
                              'street_address' => $order['customers_street_address'],
                              'suburb' => $order['customers_suburb'],
                              'city' => $order['customers_city'],
                              'postcode' => $order['customers_postcode'],
                              'state' => $order['customers_state'],
                              'country' => $order['customers_country'],
                              'format_id' => $order['customers_address_format_id'],
                              'telephone' => $order['customers_telephone'],
                              'email_address' => $order['customers_email_address'],
                              'id' => $order['customers_id'],
                              'referer' => $order['customers_referer']);

	  $this->customer = array_merge($this->customer, $company);

      $this->delivery = array('name' => $order['delivery_name'],
                              'company' => $order['delivery_company'],
                              'street_address' => $order['delivery_street_address'],
                              'suburb' => $order['delivery_suburb'],
                              'city' => $order['delivery_city'],
                              'postcode' => $order['delivery_postcode'],
                              'state' => $order['delivery_state'],
                              'country' => $order['delivery_country'],
                              'format_id' => $order['delivery_address_format_id'],
                              'telephone' => $order['delivery_telephone'],
                              'date' => ($order['delivery_date']=='0000-00-00' ? '' : $order['delivery_date']),
                              'time' => $order['delivery_time'],
							  'delivery_method' => $order['delivery_method'],
							  'delivery_method_class' => $order['delivery_method_class'],
							  'delivery_self_address' => $order['delivery_self_address'],
							  'delivery_self_address_id' => $order['delivery_self_address_id']);

      $this->billing = array('name' => $order['billing_name'],
                             'company' => $order['billing_company'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $order['billing_country'],
                             'format_id' => $order['billing_address_format_id'],
                             'telephone' => $order['billing_telephone']);

      $index = 0;
      $orders_products_query = tep_db_query("select * from " . TABLE_TEMP_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                                        'id' => $orders_products['products_id'],
                                        'name' => $orders_products['products_name'],
                                        'model' => $orders_products['products_model'],
                                        'manufacturer' => $orders_products['manufacturers_name'],
                                        'year' => $orders_products['products_year'],
                                        'type' => $orders_products['products_types_id'],
                                        'code' => $orders_products['products_code'],
                                        'weight' => $orders_products['products_weight'],
                                        'tax' => $orders_products['products_tax'],
                                        'price' => $orders_products['products_price'],
                                        'final_price' => $orders_products['final_price']);
        $index++;
      }
    }
  }
  $orders_query = tep_db_query("select orders_id from " . TABLE_TEMP_ORDERS . "");
  while ($orders = tep_db_fetch_array($orders_query)) {
	tep_upload_order($orders['orders_id'], ',', UPLOAD_DIR . 'orders2/');
  }

  if (isset($HTTP_GET_VARS['oID'])) {
	if ($order_exists) {
	  $order = new order($oID);
	  if ($order->info['currency_value'] > 10) $round_to = 3;
	  elseif ($order->info['currency_value'] < 0.1) $round_to = 0;
	  else $round_to = 1;
	}
  }

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_descriptions_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name, orders_status_description from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by sort_order");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
    $orders_status_descriptions_array[$orders_status['orders_status_id']] = $orders_status['orders_status_description'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if ($action=='view' || $action=='edit' || $action=='add_products') {
    $order = new order($oID);

	$module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
	$module_key = 'MODULE_PAYMENT_INSTALLED';
	$file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
	$directory_array = array();
	if ($dir = @dir($module_directory)) {
	  while ($file = $dir->read()) {
		if (!is_dir($module_directory . $file)) {
		  if (substr($file, strrpos($file, '.')) == $file_extension) {
			$directory_array[] = $file;
		  }
		}
	  }
	  sort($directory_array);
	  $dir->close();
	}

	$payments_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
	$payment_modules = array();
	$installed_payment = array();
	$modules = array();
	for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	  $file = $directory_array[$i];

	  include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $file);
	  include($module_directory . $file);

	  $class = substr($file, 0, strrpos($file, '.'));
	  if (tep_class_exists($class)) {
		$module = new $class;
		$payment_modules[] = array('id' => $file, 'text' => $module->title, 'email_footer' => $module->email_footer);
		$installed_payment[$file] = $module->title;
		$payments_array[] = array('id' => $module->title, 'text' => $module->title);
	  }
	}

	$module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
	$module_key = 'MODULE_SHIPPING_INSTALLED';
	$file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
	$directory_array = array();
	if ($dir = @dir($module_directory)) {
	  while ($file = $dir->read()) {
		if (!is_dir($module_directory . $file)) {
		  if (substr($file, strrpos($file, '.')) == $file_extension) {
			$directory_array[] = $file;
		  }
		}
	  }
	  sort($directory_array);
	  $dir->close();
	}

	$shipping_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
	$shipping_modules = array();
	$installed_shipping = array();
	$modules = array();
	for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	  $file = $directory_array[$i];

	  include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/shipping/' . $file);
	  include($module_directory . $file);

	  $class = substr($file, 0, strrpos($file, '.'));
	  if (tep_class_exists($class)) {
		$module = new $class;
		$shipping_modules[] = array('id' => $class, 'text' => $module->title);
		$installed_shipping[$class] = $module->title;
		$shipping_array[] = array('id' => $module->title, 'text' => $module->title);
	  }
	}

	$shop_info_query = tep_db_query("select if((s.shops_ssl<>'' and s.shops_ssl<>s.shops_url), s.shops_ssl, s.shops_url) as orders_domain from " . TABLE_SHOPS . " s, " . TABLE_TEMP_ORDERS . " o where o.shops_id = s.shops_id and o.orders_id = '" . (int)$oID . "'");
	$shop_info = tep_db_fetch_array($shop_info_query);
	if (!is_array($shop_info)) $shop_info = array();

	$payment_link = '';
	reset($payment_modules);
	while (list(, $payment_detail) = each($payment_modules)) {
	  if ($payment_detail['text']==$order->info['payment_method']) {
		$payment_email_footer = strip_tags($payment_detail['email_footer'], '<a>');
		break;
	  }
	}
	if (strpos($payment_email_footer, 'advice.php')!==false || $order->customer['type']=='corporate') {
	  $payment_link = $shop_info['orders_domain'] . '/advice.php?order_id=' . $oID;
	  $customer_password_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$order->customer['id'] . "'");
	  $customer_password = tep_db_fetch_array($customer_password_query);
	  $payment_link .= (strpos($payment_link, '?')!==false ? '&' : '?') . 'email_address=' . $order->customer['email_address'] . '&password=' . $customer_password['customers_password'];
	  $payment_link = '<a href="' . $payment_link . '" target="_blank"><u>' . ENTRY_PRINTABLE . '</u></a>';
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
  $oID = (int)$HTTP_GET_VARS['oID'];
  if (($action == 'view') && ($order_exists == true)) {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(HEADING_TITLE_1, $oID); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
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
                <td class="main" width="100"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php echo $order->customer['name'] . ', ' . tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
<?php
	if ($order->customer['type']=='corporate') {
?>
              <tr valign="top">
                <td class="main" width="100"><strong><?php echo ENTRY_CUSTOMER_COMPANY; ?></strong><div id="customer_company" style="background: #EFEFEF; border: 1px solid #CCCCCC; position: absolute; display: none;">
			<div style="text-align: right;"><a href="#" onclick="document.getElementById('customer_company').style.display = 'none'; return false;"><?php echo tep_image(DIR_WS_IMAGES . 'cal_close_small.gif', ''); ?></a></div>
			<div style="width: 400px; margin: 5px 15px 0 15px;">
<?php
echo (tep_not_null($order->customer['company_full']) ? ENTRY_COMPANY_FULL . ' ' . $order->customer['company_full'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okved']) ? ENTRY_COMPANY_OKVED . ' ' . $order->customer['company_okved'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_inn']) ? ENTRY_COMPANY_INN . ' ' . $order->customer['company_inn'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_kpp']) ? ENTRY_COMPANY_KPP . ' ' . $order->customer['company_kpp'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okpo']) ? ENTRY_COMPANY_OKPO . ' ' . $order->customer['company_okpo'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okogu']) ? ENTRY_COMPANY_OKOGU . ' ' . $order->customer['company_okogu'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okato']) ? ENTRY_COMPANY_OKATO . ' ' . $order->customer['company_okato'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_ogrn']) ? ENTRY_COMPANY_OGRN . ' ' . $order->customer['company_ogrn'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okfs']) ? ENTRY_COMPANY_OKFS . ' ' . $order->customer['company_okfs'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_okopf']) ? ENTRY_COMPANY_OKOPF . ' ' . $order->customer['company_okopf'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_address_corporate']) ? ENTRY_COMPANY_ADDRESS_CORPORATE . ' ' . $order->customer['company_address_corporate'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_address_post']) ? ENTRY_COMPANY_ADDRESS_POST . ' ' . $order->customer['company_address_post'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_telephone']) ? ENTRY_COMPANY_TELEPHONE . ' ' . $order->customer['company_telephone'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_fax']) ? ENTRY_COMPANY_FAX . ' ' . $order->customer['company_fax'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_bank']) ? ENTRY_COMPANY_BANK . ' ' . $order->customer['company_bank'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_bik']) ? ENTRY_COMPANY_BIK . ' ' . $order->customer['company_bik'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_ks']) ? ENTRY_COMPANY_KS . ' ' . $order->customer['company_ks'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_rs']) ? ENTRY_COMPANY_RS . ' ' . $order->customer['company_rs'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_general']) ? ENTRY_COMPANY_GENERAL . ' ' . $order->customer['company_general'] . '<br><br>' . "\n" : '') .
(tep_not_null($order->customer['company_financial']) ? ENTRY_COMPANY_FINANCIAL . ' ' . $order->customer['company_financial'] . '<br><br>' . "\n" : '');
?>
			  </div></div></td>
                <td class="main"><?php echo '<a href="#" onclick="document.getElementById(\'customer_company\').style.display = (document.getElementById(\'customer_company\').style.display==\'none\' ? \'block\' : \'none\'); return false;" title="' . ENTRY_COMPANY_DETAILS . '"><u>' . $order->customer['company'] . '</u></a>'; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
<?php
	}
?>
              <tr>
                <td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
              </tr>
            </table></td>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="120"><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
                <td class="main"><?php echo $order->delivery['name'] . ', ' . tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_SHIPPING_DATE; ?></strong></td>
                <td class="main"><?php echo tep_not_null($order->delivery['date']) ? tep_date_short($order->delivery['date']) : TEXT_NOT_SET; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_SHIPPING_TIME; ?></strong></td>
                <td class="main"><?php echo tep_not_null($order->delivery['time']) ? $order->delivery['time'] : TEXT_NOT_SET; ?></td>
              </tr>
            </table></td>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td class="main" width="100"><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
                <td class="main"><?php echo $order->billing['name'] . ', ' . tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
            <td class="main"><?php echo $order->info['payment_method'] . (tep_not_null($payment_link) ? ' (' . $payment_link . ')' : ''); ?></td>
          </tr>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    }
    if (tep_not_null($order->info['check_account_type']) || tep_not_null($order->info['check_bank_name']) || tep_not_null($order->info['check_routing_number']) || tep_not_null($order->info['check_account_number'])) {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ACCOUNT_TYPE; ?></td>
            <td class="main"><?php echo $order->info['check_account_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_BANK_NAME; ?></td>
            <td class="main"><?php echo $order->info['check_bank_name']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ROUTING_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['check_routing_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CHECK_ACCOUNT_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['check_account_number']; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><strong><?php echo ENTRY_SELF_DELIVERY; ?></strong></td>
            <td class="main"><?php echo (tep_not_null($order->info['self_delivery']) ? $order->info['self_delivery'] : TEXT_NOT_SET); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo ENTRY_DELIVERY_TRANSFER; ?></strong> <?php echo ((tep_not_null($order->info['delivery_transfer']) && $order->info['delivery_transfer']!='0000-00-00') ? tep_date_short($order->info['delivery_transfer']) : TEXT_NOT_SET); ?></td>
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
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_CODE; ?></td>
		    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_WEIGHT; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_QUANTITY; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UNIT_PRICE; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TOTAL; ?></td>
          </tr>
<?php
    for ($i=0, $total_weight=0, $n=sizeof($order->products); $i<$n; $i++) {
	  $product_info_query = tep_db_query("select products_year, manufacturers_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$order->products[$i]['id'] . "'");
	  $product_info = tep_db_fetch_array($product_info_query);
	  $manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$product_info['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
	  $manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
	  $manufacturer_string = $manufacturer_info['manufacturers_name'];
	  if ($product_info['products_year'] > 0) $manufacturer_string .= (tep_not_null($manufacturer_string) ? ', ' : '') . $product_info['products_year'];
      echo '          <tr class="dataTableRow" align="center">' . "\n" .
           '            <td class="dataTableContent" align="left">' . ($i+1) . '.&nbsp;<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $order->products[$i]['id']) . '" target="_blank"><u>' . $order->products[$i]['name'] . '</u></a></td>' . "\n" .
           '            <td class="dataTableContent">' . $manufacturer_string . '</td>' . "\n" .
           '            <td class="dataTableContent"><nobr>' . $order->products[$i]['model'] . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['code'] . '</td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['weight'] . '</td>' . "\n" .
           '            <td class="dataTableContent">' . $order->products[$i]['qty'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</nobr></td>' . "\n" .
           '            <td class="dataTableContent">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right"><nobr><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></nobr></td>' . "\n" .
      '          </tr>' . "\n";
	  $total_weight += $order->products[$i]['weight'] * $order->products[$i]['qty'];
    }
?>
          <tr>
            <td align="right" colspan="9"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td class="smallText" align="right"><?php echo ENTRY_TOTAL_WEIGHT; ?></td>
				<td class="smallText" align="center"><?php echo $total_weight . ENTRY_TOTAL_WEIGHT_UNITS; ?></td>
			  </tr>
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['title'] . (substr($order->totals[$i]['title'], -1)!=':' ? ':' : '') . '</td>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><table border="0" cellspacing="1" cellpadding="5">
          <tr align="center" class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_ADMIN_COMMENTS; ?></strong></td>
            <td class="dataTableHeadingContent"><strong><?php echo TABLE_HEADING_OPERATOR; ?></strong></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select * from " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
		$users_query = tep_db_query("select users_name from " . TABLE_USERS . " where users_id = '" . tep_db_input($orders_history['operator']) . "'");
		$users = tep_db_fetch_array($users_query);
        echo '		  <tr class="dataTableRow">' . "\n" .
             '			<td class="dataTableContent" align="center">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '			<td class="dataTableContent" align="center">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '			<td class="dataTableContent">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '			<td class="dataTableContent">' . nl2br($orders_history['comments']) . '&nbsp;</td>' . "\n" .
             '			<td class="smallText">' . nl2br($orders_history['admin_comments']) . '&nbsp;</td>' . "\n" .
			 '			<td class="dataTableContent">' . $users['users_name'] . '&nbsp;</td>' . "\n" .
             '		  </tr>' . "\n";
      }
    } else {
        echo '		  <tr class="dataTableRow">' . "\n" .
             '			<td class="dataTableContent" colspan="6">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '		  </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('action')) . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>'; ?></td>
      </tr>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" cellspacing="0" cellpadding="0">
              <?php echo tep_draw_form('orders', FILENAME_TEMP_ORDERS, '', 'get', 'onkeypress="if (event.keyCode==13) this.submit();" onkeydown="if (event.keyCode==13) this.submit();" onkeyup="if (event.keyCode==13) this.submit();"'); ?><tr>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12" onkeypress="if (event.which==13) this.form.submit();"') . tep_draw_hidden_field('action', 'view'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?></td>
              </tr>           
              <tr>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_CUSTOMER . ' ' . tep_draw_input_field('search', '', 'size="12" onkeypress="if (event.which==13) this.form.submit();"'); ?></td>
				<td>&nbsp;&nbsp;</td>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SHOP . ' ' . tep_draw_pull_down_menu('shop', $shops_array, '', 'onChange="this.form.submit();"'); ?></td>
              </tr></form>
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
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_METHODS; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
			  </tr>
<?php
	$orders_query_raw = "select o.orders_id from " . TABLE_TEMP_ORDERS . " o where 1";
    if (tep_not_null($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw .= " and o.customers_id = '" . (int)$cID . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['status'])) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw .= " and o.orders_status = '" . (int)$status . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['shop'])) {
      $shop = tep_db_prepare_input($HTTP_GET_VARS['shop']);
      $orders_query_raw .= " and o.shops_id = '" . (int)$shop . "'";
    }
	if (tep_not_null($HTTP_GET_VARS['search'])) {
      $search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	  $fields = array('o.customers_name', 'o.customers_company', 'o.customers_company', 'o.customers_street_address', 'o.customers_suburb', 'o.customers_city', 'o.customers_postcode', 'o.customers_state', 'o.customers_country', 'o.customers_telephone', 'o.customers_email_address', 'o.delivery_name', 'o.delivery_company', 'o.delivery_street_address', 'o.delivery_suburb', 'o.delivery_city', 'o.delivery_postcode', 'o.delivery_state', 'o.delivery_country', 'o.billing_name', 'o.billing_company', 'o.billing_street_address', 'o.billing_suburb', 'o.billing_city', 'o.billing_postcode', 'o.billing_state', 'o.billing_country');
	  $orders_query_array = array();
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		$orders_query_array[] = $field . " like '%" . tep_db_input(str_replace(' ', "%' and " . $field . " like '%", $search)) . "%'";
	  }
	  $orders_query_raw .= " and (" . implode(" or ", $orders_query_array) . ")";
    }
	if (sizeof($allowed_shops_array) > 0) $orders_query_raw .= " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')";
	$orders_query_raw .= " order by o.orders_id desc";

    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
	  $order_info_query = tep_db_query("select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.delivery_date, o.delivery_time, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total, if(sh.shops_id='" . (int)SHOP_ID . "', '', sh.shops_url) as shops_url from " . TABLE_TEMP_ORDERS . " o left join " . TABLE_TEMP_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) left join " . TABLE_SHOPS . " sh on (o.shops_id = sh.shops_id), " . TABLE_ORDERS_STATUS . " s where o.orders_id = '" . (int)$orders['orders_id'] . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' limit 1");
	  $order_info = tep_db_fetch_array($order_info_query);
	  $shipping_info_query = tep_db_query("select title as shipping_method from " . TABLE_TEMP_ORDERS_TOTAL . " where orders_id = '" . (int)$orders['orders_id'] . "' and class = 'ot_shipping'");
	  $shipping_info = tep_db_fetch_array($shipping_info_query);
	  if (!is_array($shipping_info)) $shipping_info = array();
	  $comments_info_query = tep_db_query("select comments from " . TABLE_TEMP_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$orders['orders_id'] . "' order by date_added limit 1");
	  $comments_info = tep_db_fetch_array($comments_info_query);
	  if (!is_array($comments_info)) $comments_info = array();
	  $order_info = array_merge($order_info, $shipping_info, $comments_info);
	  if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $order_info['orders_id']))) && !isset($oInfo)) {
		$oInfo = new objectInfo($order_info);
	  }

      if (isset($oInfo) && is_object($oInfo) && ($order_info['orders_id'] == $oInfo->orders_id)) {
        echo '			  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '			  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['orders_id']) . '\'">' . "\n";
      }
?>
				<td class="dataTableContent" nowrap="nowrap"><?php echo '<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $order_info['orders_id'] . '&action=view') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;[' . $order_info['orders_id'] . '] ' . $order_info['customers_name']; ?></td>
				<td class="dataTableContent" align="right" nowrap="nowrap"><?php echo strip_tags($order_info['order_total']); ?></td>
				<td class="dataTableContent" align="center" nowrap="nowrap"><?php echo substr(tep_datetime_short($order_info['date_purchased']), 0, -3); ?></td>
				<td class="dataTableContent"><?php echo ((sizeof($allowed_shops_array)!=1 && tep_not_null($order_info['shops_url'])) ? '[' . str_replace('http://www.', '', $order_info['shops_url']) . ']<br />' : '') . "\n" . $order_info['shipping_method'] . '<br />' . "\n" . $order_info['payment_method'] . (tep_not_null($order_info['comments']) ? '<br><strong>(' . nl2br($order_info['comments']) . ')</strong>' : ''); ?></td>
				<td class="dataTableContent" align="center"><?php echo $order_info['orders_status_name']; ?></td>
				<td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($order_info['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $order_info['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
    }
?>
			  <tr>
				<td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
					<td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
				  </tr>
				</table></td>
			  </tr>
<?php echo tep_draw_form('download', FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('action')) . 'action=download'); ?>
			  <tr>
				<td colspan="6" align="right"><table border="0" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText"><?php echo sprintf(TEXT_DOWNLOAD_ORDERS, tep_draw_input_field('days', '7', 'size="1"')); ?></td>
					<td class="smallText"><?php echo tep_image_submit('button_download.gif', IMAGE_DOWNLOAD) . '</form>' ; ?></td>
				  </tr>
				</table></td>
			  </tr>
			  </form>
			</table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=view') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=download') . '">' . tep_image_button('button_download.gif', IMAGE_DOWNLOAD) . '</a> <a href="' . tep_href_link(FILENAME_TEMP_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
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