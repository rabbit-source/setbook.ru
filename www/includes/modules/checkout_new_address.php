<?php
  if (!isset($process)) $process = false;

  $address_fields = array();
  if (SHOP_ID==9 || SHOP_ID==14 || SHOP_ID==16) {
	$address_fields_order = array('gender', 'first_name', 'middle_name', 'last_name', 'street_address', 'suburb', 'city', 'state', 'postcode', 'country', 'telephone_number', 'fax_number');
  } else {
	$address_fields_order = array('gender', 'first_name', 'middle_name', 'last_name', 'country', 'postcode', 'state', 'city', 'suburb', 'street_address', 'telephone_number', 'fax_number');
  }
  if ($is_dummy_account) $address_fields_order[] = 'email_address';

  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
      $female = ($gender == 'f') ? true : false;
    } else {
      $male = false;
      $female = false;
    }
	$address_fields['gender'] = array('title' => (ENTRY_GENDER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_GENDER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_GENDER) . (tep_not_null(ENTRY_GENDER_TEXT) ? '&nbsp;' . ENTRY_GENDER_TEXT : ''),
									  'field' => tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE);
  }

  $address_fields['first_name'] = array('title' => (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FIRST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FIRST_NAME) . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;' . ENTRY_FIRST_NAME_TEXT : ''),
										'field' => tep_draw_input_field('firstname', (isset($firstname) ? $firstname : $customer_first_name), 'size="20"'));

  if (ACCOUNT_MIDDLE_NAME == 'true') {
	$address_fields['middle_name'] = array('title' => (ENTRY_MIDDLE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_MIDDLE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_MIDDLE_NAME) . (tep_not_null(ENTRY_MIDDLE_NAME_TEXT) ? '&nbsp;' . ENTRY_MIDDLE_NAME_TEXT : ''),
										   'field' => tep_draw_input_field('middlename', (isset($middlename) ? $middlename : $customer_middle_name), 'size="20"'));
  }

  $address_fields['last_name'] = array('title' => (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_LAST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_LAST_NAME) . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;' . ENTRY_LAST_NAME_TEXT : ''),
									   'field' => tep_draw_input_field('lastname', (isset($lastname) ? $lastname : $customer_last_name), 'size="20"'));

  $address_fields['country'] = array('title' => (ENTRY_COUNTRY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COUNTRY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COUNTRY),
									 'field' => tep_get_country_list('country', '', 'onchange="if (this.form.postcode) this.form.postcode.focus();"') . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<br />' . "\n" . '<small>' . ENTRY_COUNTRY_TEXT . '</small>' : ''));

  if (ACCOUNT_POSTCODE == 'true') {
	$address_fields['postcode'] = array('title' => (ENTRY_POSTCODE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_POSTCODE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_POSTCODE) . (tep_not_null(ENTRY_POSTCODE_TEXT) ? '&nbsp;' . ENTRY_POSTCODE_TEXT : ''),
										'field' => tep_draw_input_field('postcode', '', 'size="20" onblur="loadCity(this.form, this.value);"') . (in_array(DOMAIN_ZONE, array('ru', 'by', 'ua', 'kz', 'us')) ? ' &nbsp; <small style="float: right;"><a href="#" onclick="var countryField = checkout_address.country; if (countryField.type==\'hidden\') countrySelected = countryField.value; else if (countryField.type==\'select-one\') countrySelected = countryField.options[countryField.selectedIndex].value; else countrySelected = 0; if (parseInt(countrySelected) &gt; 0) { document.getElementById(\'checkPostcode\').style.display = \'block\'; getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_states', $request_type) . '&country=\'+countryField.value, \'postcodes\'); } else { alert(\'Пожалуйста, выберите страну!\'); } return false;">Я не знаю свой индекс</a></small>' : '') . '<div id="checkPostcode" style="background: #ECECEC; padding: 10px; border: 1px solid #D6D6D6; display: none; position: absolute;"><div id="postcodes"></div><a href="#" style="display: block; text-align: center;" onclick="document.getElementById(\'checkPostcode\').style.display=\'none\'; return false;">закрыть [x]</a></div>');
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
	if ($entry_state_has_zones == true) {
	  $zones_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (isset($HTTP_POST_VARS['country']) ? (int)$country : (int)$country_id) . "' order by zone_name");
	  while ($zones_values = tep_db_fetch_array($zones_query)) {
		$zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
	  }
	  $field = tep_draw_pull_down_menu('state', $zones_array);
	} else {
	  $field = tep_draw_input_field('state', '', 'size="40"');
	}
	$address_fields['state'] = array('title' => (ENTRY_STATE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_STATE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_STATE) . (tep_not_null(ENTRY_STATE_TEXT) ? '&nbsp;' . ENTRY_STATE_TEXT : ''),
									 'field' => '<span id="address_region">' . $field . '</span>');
  }

  $address_fields['city'] = array('title' => (ENTRY_CITY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_CITY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_CITY) . (tep_not_null(ENTRY_CITY_TEXT) ? '&nbsp;' . ENTRY_CITY_TEXT : ''),
								  'field' => '<span id="address_city">' . tep_draw_input_field('city', '', 'size="40"') . '</span>');

  if (ACCOUNT_SUBURB == 'true') {
	$address_fields['suburb'] = array('title' => (ENTRY_SUBURB_MIN_LENGTH=='true' ? '<strong>' . ENTRY_SUBURB . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_SUBURB) . (tep_not_null(ENTRY_SUBURB_TEXT) ? '&nbsp;' . ENTRY_SUBURB_TEXT : ''),
									  'field' => '<span id="address_suburb">' . tep_draw_input_field('suburb', '', 'size="40"') . '</span>');
  } else {
	$address_fields['suburb'] = array('title' => '',
									  'field' => '<span id="address_suburb" style="display: none;"></span>');
  }

  $address_fields['street_address'] = array('title' => (ENTRY_STREET_ADDRESS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_STREET_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_STREET_ADDRESS) . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<br />' . ENTRY_STREET_ADDRESS_TEXT : ''),
											'field' => tep_draw_textarea_field('street_address', 'soft', '40', '4'));

  $address_fields['telephone_number'] = array('title' => (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_TELEPHONE_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_TELEPHONE_NUMBER) . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<br />' . ENTRY_TELEPHONE_NUMBER_TEXT : ''),
											  'field' => tep_draw_input_field('telephone', '', 'size="20"'));

  if ($is_dummy_account) {
	$address_fields['email_address'] = array('title' => (ENTRY_DUMMY_EMAIL_ADDRESS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_DUMMY_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_DUMMY_EMAIL_ADDRESS) . (tep_not_null(ENTRY_EMAIL_DUMMY_ADDRESS_TEXT) ? '<br />' . ENTRY_DUMMY_EMAIL_ADDRESS_TEXT : ''),
											 'field' => tep_draw_input_field('temp_email_address', '', 'size="20"'));
  } else {
	$address_fields['fax_number'] = array('title' => (ENTRY_FAX_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FAX_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FAX_NUMBER) . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '&nbsp;' . ENTRY_FAX_NUMBER_TEXT : ''),
										'field' => tep_draw_input_field('fax', '', 'size="20"'));
  }
?>

	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
  reset($address_fields_order);
  while (list(, $address_field) = each($address_fields_order)) {
	if (isset($address_fields[$address_field])) {
	  if (tep_not_null($address_fields[$address_field]['title'])) {
?>
	  <tr>
		<td width="50%"><?php echo $address_fields[$address_field]['title']; ?></td>
		<td width="50%"><?php echo $address_fields[$address_field]['field']; ?></td>
	  </tr>
<?php
	  } elseif (tep_not_null($address_fields[$address_field]['field'])) {
		echo $address_fields[$address_field]['field'] . "\n";
	  }
	}
  }
?>
	</table>
