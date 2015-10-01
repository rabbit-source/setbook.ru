<?php
  echo $page['pages_description'];
?>
	<form class="form-div">
	<fieldset>
	<legend><?php echo ADDRESS_BOOK_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
  $counter = 0;
  $addresses_query = tep_db_query("(select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone as telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ")) union (select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id, entry_telephone as telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id <> '" . (int)$customer_default_address_id . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ") order by firstname, lastname) order by '" . (int)$customer_default_address_id . "'");
  while ($addresses = tep_db_fetch_array($addresses_query)) {
    $format_id = tep_get_address_format_id($addresses['country_id']);
	$counter ++;
	$is_primary = false;
	if ($addresses['address_book_id'] == $customer_default_address_id) $is_primary = true;
?>
	  <tr valign="top">
		<td><?php echo $counter . '. ' . ($is_primary ? '<strong>' : '') . tep_address_format($format_id, $addresses, true, ' ', ', ') . ($is_primary ? '</strong>&nbsp;<small>' . PRIMARY_ADDRESS . '</small>' : ''); ?></td>
		<td width="150" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses['address_book_id'], 'SSL') . '">' . SMALL_IMAGE_BUTTON_EDIT . '</a>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $addresses['address_book_id'], 'SSL') . '">' . SMALL_IMAGE_BUTTON_DELETE . '</a>'; ?></td>
	  </tr>
<?php
  }
?>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
<?php
  if (tep_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
?>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL') . '">' . tep_image_button('button_add_address.gif', IMAGE_BUTTON_ADD_ADDRESS) . '</a>'; ?></div>
<?php
  } else {
?>
	  <div style="text-align: right;"><span class="inputRequirement"><?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></span></div>
<?php
  }
?>
	</div>
	</form>

