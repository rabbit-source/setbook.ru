<?php
  echo $page['pages_description'];

  echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), 'post', ($addresses_count<MAX_ADDRESS_BOOK_ENTRIES ? 'onsubmit="return check_form_optional(checkout_address);" ' : '') . 'class="form-div"');

  if ($process == false) {
    if ($addresses_count > 0) {
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_PAYMENT_ADDRESS; ?></legend>
	<div><?php echo tep_address_label($customer_id, $billto, true); ?></div>
	</fieldset>
	<fieldset>
	<legend><?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
      $radio_buttons = 0;

      $addresses_query = tep_db_query("select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone as telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "'");
      while ($addresses = tep_db_fetch_array($addresses_query)) {
        $format_id = tep_get_address_format_id($addresses['country_id']);
		echo '	  <tr valign="top">' . "\n" .
		'		<td>' . tep_draw_radio_field('address', $addresses['address_book_id'], ($addresses['address_book_id'] == $billto), 'style="margin-bottom: -2px;"') . tep_address_format($format_id, $addresses, true, ' ', ', ') . '</td>' . "\n" .
		'	  </tr>' . "\n";
        $radio_buttons++;
      }
?>
	</table>
	</fieldset>
<?php
    }
  }

  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_NEW_PAYMENT_ADDRESS; ?></legend>
<?php require(DIR_WS_MODULES . 'checkout_new_address.php'); ?>
	</fieldset>
<?php
  }

  if ($process == true) {
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
<?php
  } else {
?>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="float: right;"><?php echo tep_draw_hidden_field('action', 'submit') . tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	  <div style="text-align: center;"><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
	</div>
<?php
  }
?>
	</form>
