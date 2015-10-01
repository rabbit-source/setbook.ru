<?php
  echo $page['pages_description'];

  $is_periodical = false;
  reset($order->products);
  while (list(, $order_product) = each($order->products)) {
	if ($order_product['periodicity'] > 0) {
	  $is_periodical = true;
	  break;
	}
  }

  $self_delivery_query = tep_db_query("select self_delivery_id, self_delivery_cost, self_delivery_free, entry_country_id, entry_zone_id, entry_city as city, entry_street_address as street_address from " . TABLE_SELF_DELIVERY . " where self_delivery_status = '1' " . ($is_periodical ? "" : " and self_delivery_only_periodicals = '0'") . "order by city, street_address");
  if (tep_db_num_rows($self_delivery_query) > 0) {
	$points = array(array('id' => '', 'text' => TEXT_SELF_SHIPPING_ADDRESS_OFFICE_DEFAULT));
	while ($self_delivery = tep_db_fetch_array($self_delivery_query)) {
	  $region_info_query = tep_db_query("select zone_name as state from " . TABLE_ZONES . " where zone_id = '" . (int)$self_delivery['entry_zone_id'] . "' and zone_country_id = '" . (int)$self_delivery['entry_country_id'] . "'");
	  if (tep_db_num_rows($region_info_query) > 0) {
		$region_info = tep_db_fetch_array($region_info_query);
		if ($region_info['state']==$self_delivery['city']) $self_delivery = array_merge($self_delivery, $region_info);
	  }
	  $format_info_query = tep_db_query("select address_format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$self_delivery['entry_country_id'] . "'");
	  $format_info = tep_db_fetch_array($format_info_query);
	  $points[] = array('id' => 'slf_' . $self_delivery['self_delivery_id'], 'text' => tep_address_format($format_info['address_format_id'], $self_delivery, 1, '', ', '));
	}
	if (sizeof($points) > 1) {
	  echo tep_draw_form('checkout_self_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), 'post', 'class="form-div"') . tep_draw_hidden_field('action', 'process') . tep_draw_hidden_field('self_delivery', 'true');

	  $customer_telephone_info_query = tep_db_query("select customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	  $customer_telephone_info = tep_db_fetch_array($customer_telephone_info_query);
	  $customer_telephone = $customer_telephone_info['customers_telephone'];

	  $field_length = ($is_dummy_account ? '15%' : '20%');
?>
	<fieldset id="self_shipping_address">
	<legend><?php echo TABLE_HEADING_SELF_SHIPPING_ADDRESS; ?></legend>
	<div><?php echo TEXT_SELF_SHIPPING_ADDRESS; ?></div>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="25%"><?php echo TEXT_SELF_SHIPPING_ADDRESS_OFFICE; ?></td>
		<td width="75%" colspan="<?php echo ($is_dummy_account ? 10 : 8); ?>"><?php echo tep_draw_pull_down_menu('shipping_code', $points, '', 'style="width: 98%;"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_SELF_SHIPPING_ADDRESS_RECIPIENT; ?></td>
		<td><?php echo (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FIRST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FIRST_NAME); ?><br />
		<?php echo tep_draw_input_field('delivery_to_self_firstname', (isset($delivery_to_self_firstname) ? $delivery_to_self_firstname : $customer_first_name), 'size="' . $field_length . '"'); ?></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td><?php echo (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_LAST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_LAST_NAME); ?><br />
		<?php echo tep_draw_input_field('delivery_to_self_lastname', (isset($delivery_to_self_lastname) ? $delivery_to_self_lastname : $customer_last_name), 'size="' . $field_length . '"'); ?></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td><?php echo (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_TELEPHONE_NUMBER_SHORT . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_TELEPHONE_NUMBER_SHORT); ?><br />
		<?php echo tep_draw_input_field('delivery_to_self_telephone', (isset($delivery_to_self_telephone) ? $delivery_to_self_telephone : $customer_telephone), 'size="' . $field_length . '"'); ?></td>
<?php
	  if ($is_dummy_account) {
?>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td><?php echo (ENTRY_DUMMY_EMAIL_ADDRESS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_DUMMY_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_DUMMY_EMAIL_ADDRESS); ?><br />
		<?php echo tep_draw_input_field('delivery_to_self_email', '', 'size="' . $field_length . '"'); ?></td>
<?php
	  }
?>
	  </tr>
	</table>
	</fieldset>
<?php
	}
?>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>
<?php
  }

  echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', ($addresses_count<MAX_ADDRESS_BOOK_ENTRIES ? 'onsubmit="return check_form_optional(checkout_address);" ' : '') . 'class="form-div"');
  if ($process == false) {
    if ($addresses_count > 0) {
?>
<!-- 	<fieldset>
	<legend><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></legend>
	<div><?php echo tep_address_label($customer_id, $sendto, true); ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_draw_hidden_field('action', 'submit') . tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div> -->
	<fieldset>
	<legend><?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
      $radio_buttons = 0;

      $addresses_query = tep_db_query("select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone as telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ")");
      while ($addresses = tep_db_fetch_array($addresses_query)) {
        $format_id = tep_get_address_format_id($addresses['country_id']);
		echo '	  <tr valign="top">' . "\n" .
		'		<td>' . tep_draw_radio_field('address', $addresses['address_book_id'], ($addresses['address_book_id'] == $sendto), 'style="margin-bottom: -2px;"') . tep_address_format($format_id, $addresses, true, ' ', ', ') . '</td>' . "\n" .
		'	  </tr>';
        $radio_buttons++;
      }
?>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
<?php
    }
  }

  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
//	echo '<p style="cursor: pointer" onclick="document.getElementById(\'new_shipping_address\').style.display = \'block\'; this.style.display = \'none\';">' . TABLE_HEADING_NEW_SHIPPING_ADDRESS . '</p>';

	echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', ($addresses_count<MAX_ADDRESS_BOOK_ENTRIES ? 'onsubmit="return check_form_optional(checkout_address);" ' : '') . 'class="form-div"');
?>
	<fieldset id="new_shipping_address">
	<legend><?php echo TABLE_HEADING_NEW_SHIPPING_ADDRESS; ?></legend>
<?php require(DIR_WS_MODULES . 'checkout_new_address.php'); ?>
	</fieldset>
<?php
  }

  if ($process == true) {
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
<?php
  } else {
?>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="float: right;"><?php echo tep_draw_hidden_field('action', 'submit') . tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	  <div style="text-align: center;"><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
	</div>
<?php
  }
?>
	</form>
