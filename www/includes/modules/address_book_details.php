<?php
  if (!isset($process)) $process = false;
?>
	<fieldset>
	<legend><?php echo NEW_ADDRESS_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($entry['entry_gender'] == 'm') ? true : false;
    }
    $male = !$female;
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_GENDER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_GENDER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_GENDER) . (tep_not_null(ENTRY_GENDER_TEXT) ? '&nbsp;' . ENTRY_GENDER_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE; ?></td>
	  </tr>
<?php
  }
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FIRST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FIRST_NAME) . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;' . ENTRY_FIRST_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('firstname', (tep_not_null($entry['entry_firstname']) ? $entry['entry_firstname'] : $customer_first_name), 'size="20"'); ?></td>
	  </tr>
<?php
  if (ACCOUNT_MIDDLE_NAME == 'true') {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_MIDDLE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_MIDDLE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_MIDDLE_NAME) . (tep_not_null(ENTRY_MIDDLE_NAME_TEXT) ? '&nbsp;' . ENTRY_MIDDLE_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('middlename', (isset($middlename) ? $middlename : $customer_middle_name), 'size="20"'); ?></td>
	  </tr>
<?php
  }
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_LAST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_LAST_NAME) . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;' . ENTRY_LAST_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('lastname', (tep_not_null($entry['entry_lastname']) ? $entry['entry_lastname'] : $customer_last_name), 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COUNTRY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COUNTRY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COUNTRY); ?></td>
		<td width="50%"><?php echo tep_get_country_list('country', $entry['entry_country_id'], 'onchange="if (this.form.postcode) this.form.postcode.focus();"') . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<br />' . "\n" . '<small>' . ENTRY_COUNTRY_TEXT . '</small>' : ''); ?></td>
	  </tr>
<?php
  if (ACCOUNT_POSTCODE == 'true') {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_POSTCODE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_POSTCODE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_POSTCODE) . (tep_not_null(ENTRY_POSTCODE_TEXT) ? '&nbsp;' . ENTRY_POSTCODE_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('postcode', $entry['entry_postcode'], 'size="20" onblur="loadCity(this.form, this.value);"'); ?></td>
	  </tr>
<?php
  }
  if (ACCOUNT_STATE == 'true') {
	$country_id = 0;
	if ($process != true) {
	  $countries_check_query = tep_db_query("select countries_id from " . TABLE_COUNTRIES . "");
	  if (tep_db_num_rows($countries_check_query)==1) {
		$countries_check = tep_db_fetch_array($countries_check_query);
		$country_id = $countries_check['countries_id'];
	  }
	  if ($country_id > 0) {
		$zones_check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "'");
		$zones_check = tep_db_fetch_array($zones_check_query);
		if ($zones_check['total'] > 0) $entry_state_has_zones = true;
	  }
	}
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_STATE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_STATE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_STATE) . (tep_not_null(ENTRY_STATE_TEXT) ? '&nbsp;' . ENTRY_STATE_TEXT : ''); ?></td>
		<td width="50%"><span id="address_region"><?php
	if ($entry_state_has_zones == true) {
	  $zones_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (isset($HTTP_POST_VARS['country']) ? (int)$country : (int)$country_id) . "' order by zone_name");
	  while ($zones_values = tep_db_fetch_array($zones_query)) {
		$zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
	  }
	  echo tep_draw_pull_down_menu('state', $zones_array, $entry['entry_state']);
	} else {
	  echo tep_draw_input_field('state', tep_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']), 'size="40"');
	}
?></span></td>
	  </tr>
<?php
  }
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_CITY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_CITY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_CITY) . (tep_not_null(ENTRY_CITY_TEXT) ? '&nbsp;' . ENTRY_CITY_TEXT : ''); ?></td>
		<td width="50%"><span id="address_city"><?php echo tep_draw_input_field('city', $entry['entry_city'], 'size="40"'); ?></span></td>
	  </tr>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_SUBURB_MIN_LENGTH=='true' ? '<strong>' . ENTRY_SUBURB . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_SUBURB) . (tep_not_null(ENTRY_SUBURB_TEXT) ? '&nbsp;' . ENTRY_SUBURB_TEXT : ''); ?></td>
		<td width="50%"><span id="address_suburb"><?php echo tep_draw_input_field('suburb', $entry['entry_suburb'], 'size="40"'); ?></span></td>
	  </tr>
<?php
  } else {
	echo '<span id="address_suburb" style="display: none;"></span>';
  }
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_STREET_ADDRESS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_STREET_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_STREET_ADDRESS) . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<br />' . ENTRY_STREET_ADDRESS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('street_address', 'soft', '40', '4', $entry['entry_street_address']); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_TELEPHONE_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_TELEPHONE_NUMBER) . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '&nbsp;' . ENTRY_TELEPHONE_NUMBER_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('telephone', $entry['entry_telephone'], 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_FAX_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FAX_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FAX_NUMBER) . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '&nbsp;' . ENTRY_FAX_NUMBER_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('fax', $entry['entry_fax'], 'size="20"'); ?></td>
	  </tr>
<?php
  if (tep_count_customer_address_book_entries() <= 1) {
	echo tep_draw_hidden_field('primary', 'on');
  } elseif ((isset($HTTP_GET_VARS['edit']) && ($customer_default_address_id != $HTTP_GET_VARS['edit'])) || (isset($HTTP_GET_VARS['edit']) == false) ) {
?>
	  <tr>
		<td width="50%"></td>
		<td width="50%"><?php echo tep_draw_checkbox_field('primary', 'on', false, 'id="primary"') . ' ' . SET_AS_PRIMARY; ?></td>
	  </tr>
<?php
  }
?>
	</table>
	</fieldset>
