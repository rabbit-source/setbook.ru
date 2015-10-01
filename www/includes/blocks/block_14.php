<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && (PHP_SELF==DIR_WS_CATALOG .'request.html' || PHP_SELF==DIR_WS_CATALOG . 'foreign/' || PHP_SELF==DIR_WS_CATALOG . 'from_abroad/order.html') ) {
	if (PHP_SELF==DIR_WS_CATALOG . 'from_abroad/order.html') {
	  $form_type = 'foreign_products';
	  $boxHeading = ENTRY_REQUEST_FORM_TITLE_FOREIGN_PRODUCTS;
	  $new_form_action = ((PHP_SELF==DIR_WS_CATALOG . 'foreign/') ? 'foreign' : 'request');
	} elseif (PHP_SELF==DIR_WS_CATALOG . 'foreign/') {
	  $form_type = 'foreign_books';
	  $boxHeading = ENTRY_REQUEST_FORM_TITLE_FOREIGN_BOOKS;
	} else {
	  $form_type = 'request';
	  $boxHeading = ENTRY_REQUEST_FORM_TITLE;
	}
	$boxContent = '';
//	if (tep_session_is_registered('customer_id')) {
	  if (tep_check_blacklist()) {
		$boxContent .= ENTRY_BLACKLIST_REQUEST_ERROR;
	  } elseif (isset($HTTP_GET_VARS['action']) && $HTTP_GET_VARS['action']=='success') {
		$boxContent .= '<p>' . nl2br(tep_output_string_protected(ENTRY_REQUEST_FORM_SUCCESS)) . '</p>';
	  } else {
		if (strpos(REQUEST_URI, 'action')!==false) $link = preg_replace('/action=[^\&]*/i', 'action=process_' . $form_type, REQUEST_URI);
		elseif (strpos(REQUEST_URI, '?')!==false) $link = REQUEST_URI . '&amp;action=process_' . $form_type;
		else $link = REQUEST_URI . '?action=process_' . $form_type;

		$customer_delivery_address = '';
		$customer_phone_number = '';
		$customer_name = '';
		$customer_email = '';
		if (tep_session_is_registered('customer_id') && !$is_dummy_account) {
		  $address_query = tep_db_query("select address_book_id, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "'");
		  $address = tep_db_fetch_array($address_query);
		  $format_id = tep_get_address_format_id($address['country_id']);

		  $customer_email_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		  $customer_email_info = tep_db_fetch_array($customer_email_info_query);

		  $customer_delivery_address = tep_address_format($format_id, $address, true, '', "\n");
		  $customer_phone_number = $address['entry_telephone'];
		  $customer_name = trim($customer_first_name . ' ' . $customer_last_name);
		  $customer_email = $customer_email_info['customers_email_address'];
		}

		if ((HTTP_SERVER=='http://www.setbook.com.ua'))
		{
			$currencies_array = array(array('id' => 'RUR', 'text' => ENTRY_REQUEST_FORM_CURRENCY_RUR));
		}
		else
		{
			$currencies_array = array(array('id' => '', 'text' => ENTRY_REQUEST_FORM_CURRENCY), array('id' => 'USD', 'text' => ENTRY_REQUEST_FORM_CURRENCY_USD), array('id' => 'EUR', 'text' => ENTRY_REQUEST_FORM_CURRENCY_EUR), array('id' => 'GBP', 'text' => ENTRY_REQUEST_FORM_CURRENCY_GBP), array('id' => 'RUR', 'text' => ENTRY_REQUEST_FORM_CURRENCY_RUR));
		}

		ob_start();
		echo tep_draw_form('contact_us', $link, 'post', 'class="form-div"');
?>
	<fieldset>
	<legend><?php echo ENTRY_REQUEST_FORM_CONTACTS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_REQUEST_FORM_NAME . '</strong> <span class="errorText">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('customer_name', (!isset($HTTP_POST_VARS['customer_phone_number']) ? $customer_name : $HTTP_POST_VARS['customer_name'])); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_REQUEST_FORM_EMAIL . '</strong> <span class="errorText">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('customer_email', (!isset($HTTP_POST_VARS['customer_phone_number']) ? $customer_email : $HTTP_POST_VARS['customer_email'])); ?></td>
	  </tr>
<?php
		if ($form_type == 'foreign_books' || $form_type == 'foreign_products') {
?>
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_REQUEST_FORM_ADDRESS . '</strong> <span class="errorText">*</span><br />' . "\n" . '<small>' . ENTRY_REQUEST_FORM_ADDRESS_TEXT . '</small>'; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('customer_delivery_address', 'soft', 50, 4, (!isset($HTTP_POST_VARS['customer_delivery_address']) ? $customer_delivery_address : $HTTP_POST_VARS['customer_delivery_address'])); ?></td>
	  </tr>
<?php
		}
?>
	  <tr>
		<td width="50%"><?php echo ENTRY_REQUEST_FORM_PHONE_NUMBER; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('customer_phone_number', (!isset($HTTP_POST_VARS['customer_phone_number']) ? $customer_phone_number : $HTTP_POST_VARS['customer_phone_number'])); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_REQUEST_FORM_COMMENTS; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('customer_comments', 'soft', 50, 4); ?></td>
	  </tr>
	</table>
	</fieldset>
<script type="text/javascript">
	function calcFinalPrice(index)
	{
<?php if ((HTTP_SERVER=='http://www.setbook.com.ua')/* && ($_SERVER['REMOTE_ADDR'] == '178.76.216.140')*/) : ?>
		var priceControl = document.getElementsByName('price_' + index)[0];
		var qtyControl = document.getElementsByName('qty_' + index)[0];
		var finalPriceControl = document.getElementById('finalPrice_' + index);
		var finalPriceUKRControl = document.getElementById('finalPriceUKR_' + index);
		var finalPrice = '', finalPriceUKR = '';

		if (!isNaN(priceControl.value) && !isNaN(qtyControl.value) && (Number(priceControl.value) > 0) && (Number(qtyControl.value) > 0))
		{
			price = Math.max(Number(priceControl.value), 120) * Number(qtyControl.value);
			finalPrice = price*1.2;
			
			if (price <= 200)			
				finalPrice = Math.ceil(finalPrice*1.8);
			else if (price <= 500)			
				finalPrice = Math.ceil(finalPrice*1.5);
			else if (price <= 1000)			
				finalPrice = Math.ceil(finalPrice*1.4);
			else if (price <= 1500)			
				finalPrice = Math.ceil(finalPrice*1.3);
			else if (price <= 5000)			
				finalPrice = Math.ceil(finalPrice*1.2);
			else if (finalPrice > 5000)
				finalPrice = Math.ceil(finalPrice*1.15);
				
			
			if (finalPrice > 0)
			{
				finalPriceUKR = finalPrice*0.25;
			
				finalPrice = finalPrice + ' руб';
				finalPriceUKR = '(' + finalPriceUKR +' грн)';
	  		}
			else
			{
				finalPrice = '';
				finalPriceUKR = '';
			}
		}

		while (finalPriceControl.firstChild)
		{
			finalPriceControl.removeChild(finalPriceControl.firstChild);
		}
		finalPriceControl.appendChild(document.createTextNode(finalPrice));

		while (finalPriceUKRControl.firstChild)
		{
			finalPriceUKRControl.removeChild(finalPriceUKRControl.firstChild);
		}
		finalPriceUKRControl.appendChild(document.createTextNode(finalPriceUKR));
		
<?php endif?>
	}
</script>	
	
<?php
		if ($form_type == 'foreign_books' || $form_type == 'foreign_products') {
		  if (empty($HTTP_POST_VARS)) {
			$products_in_foreign_cart = array();
			if ($form_type == 'foreign_books') {
			  $foreign_cart_products = $foreign_cart->get_product_id_list();
			  if (tep_not_null($foreign_cart_products)) $products_in_foreign_cart = explode(', ', $foreign_cart_products);

			  reset($products_in_foreign_cart);
			  while (list($i, $foreign_product_id) = each($products_in_foreign_cart)) {
				$product_info_query = tep_db_query("select * from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$foreign_product_id . "'");
				$product_info = tep_db_fetch_array($product_info_query);
				${'title_' . $i} = $product_info['products_name'];
				${'author_' . $i} = $product_info['products_author'];
				${'model_' . $i} = $product_info['products_model'];
				${'manufacturer_' . $i} = $product_info['products_manufacturer'];
				${'year_' . $i} = $product_info['products_year'];
				${'url_' . $i} = $product_info['products_url'];
				${'price_' . $i} = (string)(float)$product_info['products_price'];
				${'currency_' . $i} = $product_info['products_currency'];
			  }
			}
		  }

		  for ($i=0; $i<10; $i++) {
			if (!isset(${'currency_' . $i})) ${'currency_' . $i} = 'USD';
			if ($form_type=='foreign_books') {
			  $block_title = ENTRY_REQUEST_FORM_BOOK_INFO;
			  $url_title = ENTRY_REQUEST_FORM_BOOK_URL;
			  $price_title = ENTRY_REQUEST_FORM_BOOK_PRICE;
			  $model_title = ENTRY_REQUEST_FORM_BOOK_MODEL;
			  $manufacturer_title = ENTRY_REQUEST_FORM_BOOK_MANUFACTURER;
			} else {
			  $block_title = ENTRY_REQUEST_FORM_PRODUCT_INFO;
			  $url_title = ENTRY_REQUEST_FORM_PRODUCT_URL;
			  $price_title = ENTRY_REQUEST_FORM_PRODUCT_PRICE;
			  $model_title = ENTRY_REQUEST_FORM_PRODUCT_MODEL;
			  $manufacturer_title = ENTRY_REQUEST_FORM_PRODUCT_MANUFACTURER;
			}
?>
	<fieldset>
	<legend><?php echo sprintf($block_title, ($i+1)); ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
			if ($form_type=='foreign_books') {
?>
	  <tr valign="top">
		<td style="padding: 0 5px 0 0;"><?php echo '<strong>' . ENTRY_REQUEST_FORM_BOOK_TITLE . '</strong> <span class="errorText">*</span><br />' . "\n" . tep_draw_input_field('title_' . $i, '', 'size="27"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_AUTHOR . '<br />' . "\n" . tep_draw_input_field('author_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $model_title . '<br />' . "\n" . tep_draw_input_field('model_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $manufacturer_title . '<br />' . "\n" . tep_draw_input_field('manufacturer_' . $i, '', 'size="19"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_YEAR . '<br />' . "\n" . tep_draw_input_field('year_' . $i, '', 'size="5"'); ?></td>
	  </tr>
	  <tr valign="top">
		<td style="padding: 0 5px 0 0;" colspan="3"><?php echo $url_title . '<br />' . "\n" . tep_draw_input_field('url_' . $i, '', 'size="68"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $price_title . '<br />' . "\n" . tep_draw_input_field('price_' . $i, '', 'size="3"') . tep_draw_pull_down_menu('currency_' . $i, $currencies_array, ${'currency_' . $i}, 'style="width: 75px;"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_QTY . '<br />' . "\n" . tep_draw_input_field('qty_' . $i, '1', 'size="1" style="text-align: right;"'); ?></td>
	  </tr>
<?php 
		if ((HTTP_SERVER=='http://www.setbook.com.ua')/* && ($_SERVER['REMOTE_ADDR'] == '178.76.216.140')*/) {
?>
	  <tr valign="top">
		<td style="padding: 0 5px 0 0; font-size:11pt" colspan="5"><br/><b><a href="#" onclick="calcFinalPrice(<?php echo $i;?>); return false;">Рассчитать стоимость с доставкой до Украины:</a></b>&nbsp;&nbsp;<span id="finalPrice_<?php echo $i ?>"></span>&nbsp;&nbsp;<span id="finalPriceUKR_<?php echo $i ?>"></span></td>
	  </tr>
<?php
		}
?>		
	  
<?php
			} else {
?>
	  <tr valign="top">
		<td style="padding: 0 5px 0 0;" colspan="2"><?php echo '<strong>' . ENTRY_REQUEST_FORM_PRODUCT_TITLE . '</strong> <span class="errorText">*</span><br />' . "\n" . tep_draw_input_field('title_' . $i, '', 'size="46"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $manufacturer_title . '<br />' . "\n" . tep_draw_input_field('manufacturer_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center" colspan="2"><?php echo ENTRY_REQUEST_FORM_PRODUCT_CODE . '<br />' . "\n" . tep_draw_input_field('code_' . $i, '', 'size="27"'); ?></td>
		<td>&nbsp;</td>
	  </tr>
	  <tr valign="top">
		<td style="padding: 0 5px 0 0;" colspan="2"><?php echo $url_title . '<br />' . "\n" . tep_draw_input_field('url_' . $i, '', 'size="46"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $model_title . '<br />' . "\n" . tep_draw_input_field('model_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo $price_title . '<br />' . "\n" . tep_draw_input_field('price_' . $i, '', 'size="3"') . tep_draw_pull_down_menu('currency_' . $i, $currencies_array, ${'currency_' . $i}, 'style="width: 75px;"'); ?></td>
		<td style="padding: 0 5px 0 0; text-align: center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_QTY . '<br />' . "\n" . tep_draw_input_field('qty_' . $i, '1', 'size="1" style="text-align: right;"'); ?></td>
	  </tr>
<?php
			}
?>
	</table>
	</fieldset>
<?php
		  }
?>
<?php
		} else {
?>
	<fieldset>
	<legend><?php echo ENTRY_REQUEST_FORM ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td>&nbsp;</td>
		<td><?php echo '<strong>' . ENTRY_REQUEST_FORM_BOOK_TITLE . '</strong> <span class="errorText">*</span>'; ?></td>
		<td align="center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_AUTHOR; ?></td>
		<td align="center"><?php echo ENTRY_REQUEST_FORM_BOOK_MODEL; ?></td>
		<td align="center"><?php echo ENTRY_REQUEST_FORM_BOOK_MANUFACTURER; ?></td>
		<td align="center"><?php echo ENTRY_REQUEST_FORM_PRODUCT_YEAR; ?></td>
	  </tr>
<?php
		  for ($i=0; $i<15; $i++) {
?>
	  <tr>
		<td style="padding: 0 2px 5px 0; text-align: right"><?php echo ($i+1); ?>.</td>
		<td style="padding: 0 5px 5px 0;"><?php echo tep_draw_input_field('title_' . $i, '', 'size="27"'); ?></td>
		<td style="padding: 0 5px 5px 0; text-align: center"><?php echo tep_draw_input_field('author_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 5px 0; text-align: center"><?php echo tep_draw_input_field('model_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 5px 0; text-align: center"><?php echo tep_draw_input_field('manufacturer_' . $i, '', 'size="14"'); ?></td>
		<td style="padding: 0 5px 5px 0; text-align: center"><?php echo tep_draw_input_field('year_' . $i, '', 'size="5"'); ?></td>
	  </tr>
<?php
		  }
?>
	</table>
	</fieldset>
<?php
		}
?>
	<div class="buttons">
	  <div class="inputRequirement" style="float: left;"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_send.gif', IMAGE_BUTTON_SEND); ?></div>
	</div>
	</form>
<?php
		$boxContent .= ob_get_clean();
	  }
//	} else {
//	  $boxContent .= '<p>' . ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED . '</p>';
//	}

	echo '<a name="request"></a>';
	include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
  }
?>