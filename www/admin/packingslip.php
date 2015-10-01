<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
  $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");

  include(DIR_WS_CLASSES . 'order.php');
  $order = new order($oID);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html<?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo PAGE_TITLE; ?></title>
<style type="text/css">
body, div, span, p, th, td {
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
}

.headerdata {
  border: solid 1px #000000; 
  padding: 5px 5px 5px 5px;
  display: inline;
}

.data {
  border: solid 1px #cccccc; 
  padding: 3px 3px 3px 3px;
  margin-left: 5px;
  background-color: #e9e9e9;
}

#logo {
  padding-right: 20px;
}

#headerdesc1 {
  font-weight: normal;
  line-height: 1.5em;
}

#headername { 
  font-size: 16px;
  color: #333333;
  font-weight: bold;
  padding-bottom: 12px;
  padding-top: 4px;
}

#title { 
  font-size: 14px;
  color: #333333;
  font-weight: bold;
  padding-bottom: 1em;
} 

#container {
  margin: 10px;
  padding: 10px;
  border: solid 1px black;
  width: 700px;
}

td.main1 {
  width: 150px;
  padding: 5px 5px 5px 5px;
  text-align: right;
}

td.main2 {
  width: 100%;
}

.itemtable {
  background-color: #cccccc;
}

td.itemheader {
  border: solid 0px black;
  background-color: #efefef;
  padding: 5px 5px 5px 5px;
  font-weight: bold;
}

td.itemdata {
  background-color: #ffffff;
  padding: 3px 3px 3px 3px;
}

td.itemtotal {
  background-color: #ffffff;
  padding: 5px 5px 5px 5px;	
}

.signaturesdata {
  border: solid 1px #000000; 
  padding: 5px 5px 5px 5px;
  width: 150px;
}

.signaturesdata2 {
  border: solid 1px #000000; 
  padding: 5px 5px 5px 5px;
  width: 250px;
}

.line {
  border-bottom: dotted 1px #cccccc;
}

</style>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- body_text //-->
<div id="container">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
	<td valign="top"><div id="logo"><img src="<?php echo DIR_WS_CATALOG . 'includes/templates/images/logo.gif'; ?>" border="0" /></div></td>
	<td valign="top" width="100%">
	  <div id="headername"><?php echo STORE_FULL_NAME; ?></div>
	  <div id="headerdesc1"><?php echo ENTRY_REFFERAL_SERVICE; ?></div>
	  <div id="headerdesc2"><b><?php echo tep_catalog_href_link(FILENAME_DEFAULT); ?></b>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;E-mail: <b><?php echo STORE_OWNER_EMAIL_ADDRESS; ?></b></b></div>
	</td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>	
  <tr>
	<td colspan="2"><div id="title"><?php echo sprintf(TITLE_PRINT_ORDER_NUM, (int)$oID); ?></div></td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_SOLD_TO; ?></b></td>
	<td class="main2"><div class="data"><?php echo $order->customer['name']; ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>	
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_DELIVERY_TO; ?></b></td>
	<td class="main2"><div class="data"><?php echo (tep_not_null($order->delivery['name']) ? $order->delivery['name'] : $order->customer['name']); ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_DELIVERY_ADDRESS; ?></b></td>
	<td class="main2"><div class="data"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_DELIVERY_PHONE; ?></b></td>
	<td class="main2"><div class="data"><?php echo $order->customer['telephone']; ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_PAYMENT_TYPE; ?></b></td>
	<td class="main2"><div class="data"><?php echo $order->info['payment_method']; ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
  <tr>
	<td class="main1" valign="top"><b><?php echo ENTRY_DELIVERY_DATE; ?></b></td>
	<td class="main2"><div class="data"><?php echo (tep_not_null($order->delivery['date']) ? preg_replace('/^(\d{4})-(\d{2})-(\d{2})$/e', '(int)$3 . " " . $monthes_array[(int)$2] . " $1"', $order->delivery['date']) . (tep_not_null($order->delivery['time']) ? ' ' . $order->delivery['time'] : ''): ''); ?>&nbsp;</div></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
  <tr>
	<td colspan="2"><table class="itemtable" border="0" width="100%" cellspacing="1" cellpadding="0">
	  <tr class="itemheader">
		<td class="itemheader" align="center"><?php echo TABLE_HEADING_COUNTER; ?></td>
		<td class="itemheader"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
		<td class="itemheader"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
		<td class="itemheader" align="center"><?php echo TABLE_HEADING_PRODUCTS_COUNT; ?></td>
		<td class="itemheader" align="right"><?php echo TABLE_HEADING_PRICE; ?></td>
		<td class="itemheader" align="right"><?php echo TABLE_HEADING_TOTAL; ?></td>
	  </tr>
<?php
	for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
?>
	  <tr>
		<td class="itemdata" align="center" valign="top"><?php echo $i+1; ?></td>
		<td class="itemdata" valign="top"><?php echo $order->products[$i]['model']; ?></td>
		<td class="itemdata" valign="top"><?php echo $order->products[$i]['name']; ?></td>
		<td class="itemdata" valign="top" align="center"><?php echo $order->products[$i]['qty']; ?></td>
		<td class="itemdata" align="right" valign="top"><b><?php echo $currencies->format($order->products[$i]['price'], true, $order->info['currency'], $order->info['currency_value']); ?></b></td>
		<td class="itemdata" align="right" valign="top"><b><?php echo $currencies->format($order->products[$i]['final_price']*$order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']); ?></b></td>
	  </tr>
<?php
	}
?>
	  <tr>
		<td class="itemtotal" align="right" colspan="6"><table border="0" cellspacing="0" cellpadding="2">
<?php
	for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
?>
		  <tr>
			<td align="right"><?php echo $order->totals[$i]['title']; ?></td>
			<td align="right"><?php echo $order->totals[$i]['text']; ?></td>
		  </tr>
<?php
	}
?>
		</table></td>
	  </tr>
	</table></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
	<td colspan="2" align="right"><table border="0" cellspacing="0" cellpadding="5">
	  <tr>
		<td><?php echo ENTRY_SIGNATURE; ?></td>
		<td><div class="signaturesdata">&nbsp;</div></td>
		<td>/ <?php echo (tep_not_null($order->delivery['name']) ? $order->delivery['name'] : $order->customer['name']); ?> /</td>										
	  </tr>		
	</table></td>
  </tr>
  <tr>
	<td colspan="2" valign="bottom"><div class="line"><?php echo tep_draw_separator('pixel_trans.gif', '1', '15'); ?></div></td>
  </tr>	
</table>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>