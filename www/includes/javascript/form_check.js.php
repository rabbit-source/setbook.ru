<script language="javascript" type="text/javascript"><!--
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function loadCity(formName, cityIndex) {
  var countryField = formName.country;
  if (countryField.type=='hidden') countrySelected = countryField.value;
  else if (countryField.type=='select-one') countrySelected = countryField.options[countryField.selectedIndex].value;
  else countrySelected = 0;
  if (parseInt(countrySelected) > 0) {
	if (document.getElementById('address_region')) {
	  lnk = '<?php echo tep_href_link(FILENAME_LOADER, 'action=load_region', 'SSL'); ?>&postcode='+escape(cityIndex)+'&country='+countrySelected;
	  lnk = lnk.replace('&amp;', '&');
	  getXMLDOM(lnk, 'address_region');
	}
	if (document.getElementById('address_city')) {
	  lnk = '<?php echo tep_href_link(FILENAME_LOADER, 'action=load_city', 'SSL'); ?>&postcode='+escape(cityIndex)+'&country='+countrySelected;
	  lnk = lnk.replace('&amp;', '&');
	  getXMLDOM(lnk, 'address_city');
	  if (regs = document.getElementById('address_city').innerHTML.match(/<script>([^<]+)<\/script>/)) alert(regs[1]);
	}
	if (document.getElementById('address_suburb')) {
	  if (formName.elements['suburb']) {
		lnk = '<?php echo tep_href_link(FILENAME_LOADER, 'action=load_suburb', 'SSL'); ?>&postcode='+escape(cityIndex)+'&country='+countrySelected;
	  } else {
		lnk = '<?php echo tep_href_link(FILENAME_LOADER, 'action=load_suburb', 'SSL'); ?>&only_name=1&postcode='+escape(cityIndex)+'&country='+countrySelected;
	  }
	  lnk = lnk.replace('&amp;', '&');
	  getXMLDOM(lnk, 'address_suburb');
	  if (!formName.elements['suburb']) {
		if (document.getElementById('address_suburb').innerHTML) formName.elements['street_address'].value = document.getElementById('address_suburb').innerHTML + (formName.elements['street_address'].value ? ', ' : '') + formName.elements['street_address'].value;
	  }
	}
  } else {
	alert('Пожалуйста, выберите страну!');
	return false;
  }
}

function check_input(field_name, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '') {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_field_length(field_name, min_length, length_type, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

	if (!length_type) length_type = "all";

	if (length_type=='digits') {
	  var fl = field_value.replace(/[^\d]+/g, "").length;
	} else {
	  var fl = field_value.length;
	}

	if (fl < min_length) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_checkbox(field_name, message) {
  if (form.elements[field_name]) {
    var field_value = form.elements[field_name].checked ? 'checked' : '';

    if (field_value == '') {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_radio(field_name, message) {
  var isChecked = false;

  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var radio = form.elements[field_name];

    for (var i=0; i<radio.length; i++) {
      if (radio[i].checked == true) {
        isChecked = true;
        break;
      }
    }

    if (isChecked == false) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password = form.elements[field_name_1].value;
    var confirmation = form.elements[field_name_2].value;

    if (password == '' || password.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password != confirmation) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    }
  }
}

function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password_current = form.elements[field_name_1].value;
    var password_new = form.elements[field_name_2].value;
    var password_confirmation = form.elements[field_name_3].value;

    if (password_current == '' || password_current.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password_new == '' || password_new.length < field_size) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    } else if (password_new != password_confirmation) {
      error_message = error_message + "* " + message_3 + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }

  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

<?php
  if (ENTRY_FIRST_NAME_MIN_LENGTH == 'true') echo '  check_input("firstname", "' .  ENTRY_FIRST_NAME_ERROR . '");' . "\n";
  if (ACCOUNT_MIDDLE_NAME == 'true' && ENTRY_MIDDLE_NAME_MIN_LENGTH == 'true') echo '  check_input("middlename", "' .  ENTRY_MIDDLE_NAME_ERROR . '");' . "\n";
  if (ENTRY_LAST_NAME_MIN_LENGTH == 'true') echo '  check_input("lastname", "' . ENTRY_LAST_NAME_ERROR . '");' . "\n";

  if (ACCOUNT_GENDER == 'true' && ENTRY_GENDER_MIN_LENGTH == 'true') echo '  check_radio("gender", "' . ENTRY_GENDER_ERROR . '");' . "\n";
  if (ACCOUNT_DOB == 'true' && ENTRY_DOB_MIN_LENGTH == 'true') echo '  check_input("dob", "' . ENTRY_DOB_ERROR . '");' . "\n";

  echo '  check_input("email_address", "' . ENTRY_EMAIL_ADDRESS_ERROR . '");' . "\n";

  if (basename(SCRIPT_FILENAME)==FILENAME_CREATE_ACCOUNT || basename(SCRIPT_FILENAME)==FILENAME_ACCOUNT_EDIT) {
	if (basename(SCRIPT_FILENAME)==FILENAME_CREATE_ACCOUNT) echo '  if (form.elements[\'customer_type\'][1].checked) {' . "\n";
	else echo '  if (form.elements[\'customer_type\'].value==\'corporate\') {' . "\n";
	if (ENTRY_COMPANY_MIN_LENGTH == 'true') echo '	check_input("company", "' . ENTRY_COMPANY_ERROR . '");' . "\n";
	if (ENTRY_COMPANY_INN_MIN_LENGTH == 'true') echo '	check_input("company_inn", "' . ENTRY_COMPANY_INN_ERROR . '");' . "\n";
	if (ENTRY_COMPANY_KPP_MIN_LENGTH == 'true') echo '	check_input("company_kpp", "' . ENTRY_COMPANY_KPP_ERROR . '");' . "\n";
	echo '  }' . "\n";
  } else {
	if (ENTRY_COUNTRY_MIN_LENGTH == 'true') echo '  check_select("country", "", "' . ENTRY_COUNTRY_ERROR . '");' . "\n";
	if (ACCOUNT_POSTCODE == 'true' && ENTRY_POSTCODE_MIN_LENGTH == 'true') echo '  check_input("postcode", "' . ENTRY_POSTCODE_ERROR . '")' . "\n";
	if (ACCOUNT_STATE == 'true' && ENTRY_STATE_MIN_LENGTH == 'true') echo '  check_input("state", "' . ENTRY_STATE_ERROR . '");' . "\n";
	if (ENTRY_CITY_MIN_LENGTH == 'true') echo '  check_input("city", "' . ENTRY_CITY_ERROR . '");' . "\n";
	if (ACCOUNT_SUBURB == 'true' && ENTRY_SUBURB_MIN_LENGTH == 'true') echo '  check_input("suburb", "' . ENTRY_SUBURB_ERROR . '");' . "\n";
	if (ENTRY_STREET_ADDRESS_MIN_LENGTH == 'true') echo '  check_input("street_address", "' . ENTRY_STREET_ADDRESS_ERROR . '");' . "\n";

	if (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH == 'true') {
	  echo '  check_input("telephone", "' . ENTRY_TELEPHONE_NUMBER_ERROR . '");' . "\n";
	  echo '  check_field_length("telephone", 9, "digits", "' . ENTRY_TELEPHONE_NUMBER_ERROR_1 . '");' . "\n";
	}
	if (ENTRY_FAX_NUMBER_MIN_LENGTH == 'true') echo '  check_input("fax", "' . ENTRY_FAX_NUMBER_ERROR . '");' . "\n";
  }

  echo '  check_password("password", "confirmation", ' . ENTRY_PASSWORD_MIN_LENGTH . ', "' . ENTRY_PASSWORD_ERROR . '", "' . ENTRY_PASSWORD_ERROR_NOT_MATCHING . '");' . "\n";
  echo '  check_password_new("password_current", "password_new", "password_confirmation", ' . ENTRY_PASSWORD_MIN_LENGTH . ', "' . ENTRY_PASSWORD_ERROR . '", "' . ENTRY_PASSWORD_NEW_ERROR . '", "' . ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING . '");' . "\n";
  if (basename(SCRIPT_FILENAME)==FILENAME_CREATE_ACCOUNT && (TERMS_OF_AGREEMENT=='registration' || TERMS_OF_AGREEMENT=='both') ) {
	echo '  check_checkbox("agreement", "' . ENTRY_AGREEMENT_ERROR . '");' . "\n";
  }
?>
  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
//-->
</script>
