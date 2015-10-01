<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $error = false;
  $processed = false;

  if (DOMAIN_ZONE=='ru') {
	$company_fields = array('companies_name', 'companies_full_name', 'companies_inn', 'companies_kpp', 'companies_address_corporate', 'companies_address_post', 'companies_telephone');
  } elseif (DOMAIN_ZONE=='by') {
	$company_fields = array('companies_name', 'companies_full_name', 'companies_inn', 'companies_address_corporate', 'companies_address_post', 'companies_telephone');
  } elseif (DOMAIN_ZONE=='kz') {
	$company_fields = array('companies_name', 'companies_full_name', 'companies_inn', 'companies_address_corporate', 'companies_address_post', 'companies_telephone');
  } elseif (DOMAIN_ZONE=='ua') {
	$company_fields = array('companies_name', 'companies_full_name', 'companies_inn', 'companies_kpp', 'companies_address_corporate', 'companies_address_post', 'companies_telephone');
  } else {
	$company_fields = array('companies_name', 'companies_address_post', 'companies_telephone');
	if (DOMAIN_ZONE=='org' || DOMAIN_ZONE=='com' || DOMAIN_ZONE=='us') {
	  $company_fields[] = 'companies_type_name';
	  $company_fields[] = 'companies_tax_exempt';
	  $company_fields[] = 'companies_tax_exempt_number';
	}
  }
  if (isset($HTTP_GET_VARS['cID'])) {
	$customers_address_book_check_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
	$customers_address_book_check = tep_db_fetch_array($customers_address_book_check_query);
	$customer_address_book_entries_count = (int)$customers_address_book_check['total'];
  }

  if (tep_not_null($action)) {
	switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['cID'])) {
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where customers_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('flag', 'action', 'cID')) . '&cID=' . $HTTP_GET_VARS['cID']));
        break;
	  case 'download':
		$i = 0;
		$customers_string = '№ п/п	Имя	Фамилия	Компания	Телефон	Факс	E-mail адрес' . "\r\n";
		$customers_query_raw = "select c.customers_firstname, c.customers_lastname, ab.entry_company, c.customers_telephone, c.customers_fax, c.customers_email_address from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab where c.customers_id = ab.customers_id and c.customers_default_address_id = ab.address_book_id";
		if (sizeof($allowed_shops_array)>0) {
		  $customers_array = array();
		  $shops_customers_query = tep_db_query("select distinct customers_id from " . TABLE_ORDERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
		  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
			$customers_array[] = $shops_customers['customers_id'];
		  }
		  $shops_customers_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
		  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
			$customers_array[] = $shops_customers['customers_id'];
		  }
		  $customers_query_raw .= " and c.customers_id in ('" . implode("', '", $allowed_shops_array) . "')";
		}
		$customers_query_raw .= " order by c.customers_id desc";
		$customers_query = tep_db_query($customers_query_raw);
		while ($customers = tep_db_fetch_array($customers_query)) {
// Имя   |   Фамилия   |    Компания  |   Телефон    |  Факс   |  E-mail адрес
		  $customers_string .= ($i+1) . '	' . $customers['customers_firstname'] . '	' . $customers['customers_lastname'] . '	' . $customers['entry_company'] . '	' . $customers['customers_telephone'] . '	' . $customers['customers_fax'] . '	' . $customers['customers_email_address'] . "\r\n";
		  $i ++;
		}
		header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: Application/vnd-excel");
		header("Content-disposition: attachment; filename=customers.xls");
		echo $customers_string;
		die();
		break;
	  case 'update':
		$customers_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
		$customers_firstname = tep_db_prepare_input($HTTP_POST_VARS['customers_firstname']);
		$customers_lastname = tep_db_prepare_input($HTTP_POST_VARS['customers_lastname']);
		$customers_email_address = tep_db_prepare_input($HTTP_POST_VARS['customers_email_address']);
		$customers_telephone = tep_db_prepare_input($HTTP_POST_VARS['customers_telephone']);
		$customers_fax = tep_db_prepare_input($HTTP_POST_VARS['customers_fax']);
		$customers_newsletter = tep_db_prepare_input($HTTP_POST_VARS['customers_newsletter']);
		$customers_discount = tep_db_prepare_input($HTTP_POST_VARS['customers_discount']);
		$customers_discount_type = tep_db_prepare_input($HTTP_POST_VARS['customers_discount_type']);
		$customers_status = tep_db_prepare_input($HTTP_POST_VARS['customers_status']);

		$customers_gender = tep_db_prepare_input($HTTP_POST_VARS['customers_gender']);
		$customers_dob = tep_db_prepare_input($HTTP_POST_VARS['customers_dob']);

		$default_address_id = tep_db_prepare_input($HTTP_POST_VARS['default_address_id']);
		$entry_street_address = tep_db_prepare_input($HTTP_POST_VARS['entry_street_address']);
		$entry_suburb = tep_db_prepare_input($HTTP_POST_VARS['entry_suburb']);
		$entry_postcode = tep_db_prepare_input($HTTP_POST_VARS['entry_postcode']);
		$entry_city = tep_db_prepare_input($HTTP_POST_VARS['entry_city']);
		$entry_country_id = tep_db_prepare_input($HTTP_POST_VARS['entry_country_id']);

		$entry_company = tep_db_prepare_input($HTTP_POST_VARS['entry_company']);
		$entry_state = tep_db_prepare_input($HTTP_POST_VARS['entry_state']);
		if (isset($HTTP_POST_VARS['entry_zone_id'])) $entry_zone_id = tep_db_prepare_input($HTTP_POST_VARS['entry_zone_id']);

		if (ACCOUNT_GENDER == 'true' && ENTRY_GENDER_MIN_LENGTH == 'true' && $customers_gender != 'm' && $customers_gender != 'f') {
		  $error = true;
		  $entry_gender_error = true;
		} else {
		  $entry_gender_error = false;
		}

		if (empty($customers_firstname) && ENTRY_FIRST_NAME_MIN_LENGTH == 'true') {
		  $error = true;
		  $entry_firstname_error = true;
		} else {
		  $entry_firstname_error = false;
		}

		if (empty($customers_lastname) && ENTRY_LAST_NAME_MIN_LENGTH == 'true') {
		  $error = true;
		  $entry_lastname_error = true;
		} else {
		  $entry_lastname_error = false;
		}

		if (ACCOUNT_DOB == 'true' && (tep_not_null($customers_dob) || ENTRY_DOB_MIN_LENGTH == 'true') ) {
		  if (checkdate(substr(tep_date_raw($customers_dob), 4, 2), substr(tep_date_raw($customers_dob), 6, 2), substr(tep_date_raw($customers_dob), 0, 4))) {
			$entry_date_of_birth_error = false;
		  } else {
			$error = true;
			$entry_date_of_birth_error = true;
		  }
		}

		if (empty($customers_email_address)) {
		  $error = true;
		  $entry_email_address_error = true;
		} else {
		  $entry_email_address_error = false;
		}

		if (!tep_validate_email($customers_email_address)) {
		  $error = true;
		  $entry_email_address_check_error = true;
		} else {
		  $entry_email_address_check_error = false;
		}

		if (empty($entry_street_address) && ENTRY_STREET_ADDRESS_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		  $error = true;
		  $entry_street_address_error = true;
		} else {
		  $entry_street_address_error = false;
		}

		if (ACCOUNT_POSTCODE == 'true' && (tep_not_null($entry_postcode) || ENTRY_POSTCODE_MIN_LENGTH == 'true') && $customer_address_book_entries_count > 0) {
		  if (empty($entry_postcode)) {
			$error = true;
			$entry_post_code_error = true;
		  } else {
			$entry_post_code_error = false;
		  }
		}

		if (empty($entry_city) && ENTRY_CITY_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		  $error = true;
		  $entry_city_error = true;
		} else {
		  $entry_city_error = false;
		}

		if ($entry_country_id == false && ENTRY_COUNTRY_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		  $error = true;
		  $entry_country_error = true;
		} else {
		  $entry_country_error = false;
		}

		if (ACCOUNT_STATE == 'true' && ENTRY_STATE_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		  if ($entry_country_error == true) {
			$entry_state_error = true;
		  } else {
			$zone_id = 0;
			$entry_state_error = false;
			$check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$entry_country_id . "'");
			$check_value = tep_db_fetch_array($check_query);
			$entry_state_has_zones = ($check_value['total'] > 0);
			if ($entry_state_has_zones == true) {
			  $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$entry_country_id . "' and zone_name = '" . tep_db_input($entry_state) . "'");
			  if (tep_db_num_rows($zone_query) == 1) {
				$zone_values = tep_db_fetch_array($zone_query);
				$entry_zone_id = $zone_values['zone_id'];
			  } else {
				$error = true;
				$entry_state_error = true;
			  }
			} else {
			  if ($entry_state == false) {
				$error = true;
				$entry_state_error = true;
			  }
			}
		 }
	  }

	  if (empty($customers_telephone) && ENTRY_TELEPHONE_NUMBER_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		$error = true;
		$entry_telephone_error = true;
	  } else {
		$entry_telephone_error = false;
	  }

	  if (empty($customers_fax) && ENTRY_FAX_NUMBER_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
		$error = true;
		$entry_fax_error = true;
	  } else {
		$entry_fax_error = false;
	  }

	  $check_email = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "' and customers_id != '" . (int)$customers_id . "'");
	  if (tep_db_num_rows($check_email)) {
		$error = true;
		$entry_email_address_exists = true;
	  } else {
		$entry_email_address_exists = false;
	  }

	  if ($error == false) {
		$sql_data_array = array('customers_firstname' => $customers_firstname,
								'customers_lastname' => $customers_lastname,
								'customers_email_address' => $customers_email_address,
								'customers_telephone' => $customers_telephone,
								'customers_fax' => $customers_fax,
								'customers_status' => $customers_status,
								'customers_newsletter' => $customers_newsletter);

		if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $customers_gender;
		if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($customers_dob);
		if (tep_db_field_exists(TABLE_CUSTOMERS, 'customers_discount')) $sql_data_array['customers_discount'] = (float)str_replace(',', '.', $customers_discount);
		if (tep_db_field_exists(TABLE_CUSTOMERS, 'customers_discount_type')) $sql_data_array['customers_discount_type'] = $customers_discount_type;

		tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "'");

		tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customers_id . "'");

		if ($entry_zone_id > 0) $entry_state = '';

		$sql_data_array = array('entry_firstname' => $customers_firstname,
								'entry_lastname' => $customers_lastname,
								'entry_street_address' => $entry_street_address,
								'entry_telephone' => $customers_telephone,
								'entry_fax' => $customers_fax,
								'entry_city' => $entry_city,
								'entry_country_id' => $entry_country_id);

		if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $entry_company;
		if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $entry_suburb;
		if (ACCOUNT_POSTCODE == 'true') $sql_data_array['entry_postcode'] = $entry_postcode;

		if (ACCOUNT_STATE == 'true') {
		  if ($entry_zone_id > 0) {
			$sql_data_array['entry_zone_id'] = $entry_zone_id;
			$sql_data_array['entry_state'] = '';
		  } else {
			$sql_data_array['entry_zone_id'] = '0';
			$sql_data_array['entry_state'] = $entry_state;
		  }
		}

		tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");

		$company_data = array();
		reset($company_fields);
		while (list(, $company_field) = each($company_fields)) {
		  $company_data[$company_field] = tep_db_prepare_input($HTTP_POST_VARS[$company_field]);
		}
		if (tep_not_null($company_data['companies_name'])) {
		  $customer_company_check_query = tep_db_query("select count(*) as total from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customers_id . "'");
		  $customer_company_check = tep_db_fetch_array($customer_company_check_query);
		  if ($customer_company_check['total'] > 0) {
			tep_db_perform(TABLE_COMPANIES, $company_data, 'update', "customers_id = '" . (int)$customers_id . "'");
		  } else {
			tep_db_perform(TABLE_COMPANIES, $company_data);
		  }
		  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_type = 'corporate' where customers_id = '" . (int)$customers_id . "'");
		  tep_db_query("update " . TABLE_ADDRESS_BOOK . " set entry_company = '" . tep_db_input($companies_name) . "' where customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");
		} else {
		  tep_db_query("delete from " . TABLE_COMPANIES . " where customers_id = '" . (int)$customers_id . "'");
		  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_type = 'private' where customers_id = '" . (int)$customers_id . "'");
		  tep_db_query("update " . TABLE_ADDRESS_BOOK . " set entry_company = '' where customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id));

		} else if ($error == true) {
		  $cInfo = new objectInfo($HTTP_POST_VARS);
		  $processed = true;
		}

		break;
	  case 'deleteconfirm':
		$customers_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

		if (isset($HTTP_POST_VARS['delete_reviews']) && ($HTTP_POST_VARS['delete_reviews'] == 'on')) {
		  tep_db_query("delete from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customers_id . "'");
		} else {
		  tep_db_query("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . (int)$customers_id . "'");
		}

		tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customers_id . "'");
		tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customers_id . "'");
		tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customers_id . "'");
		tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customers_id . "'");
		tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int)$customers_id . "'");

		tep_redirect(tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action'))));
		break;
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
<?php
  if ($action == 'edit' || $action == 'update') {
?>
<script language="javascript"><!--

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
<?php if (ACCOUNT_COMPANY == 'true') echo 'var entry_company = document.customers.entry_company.value;' . "\n"; ?>
<?php if (ACCOUNT_DOB == 'true') echo 'var customers_dob = document.customers.customers_dob.value;' . "\n"; ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
<?php if (ACCOUNT_POSTCODE == 'true') echo 'var entry_postcode = document.customers.entry_postcode.value;' . "\n"; ?>
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;

<?php
  if (ACCOUNT_GENDER == 'true' && ENTRY_GENDER_MIN_LENGTH == 'true') {
?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
	error_message = error_message + "<?php echo JS_GENDER; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_FIRST_NAME_MIN_LENGTH == 'true') {
?>
  if (customers_firstname == "") {
	error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_LAST_NAME_MIN_LENGTH == 'true') {
?>
  if (customers_lastname == "") {
	error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
	error = 1;
  }
<?php
  }
  if (ACCOUNT_DOB == 'true' && ENTRY_DOB_MIN_LENGTH == 'true') {
?>
  if (customers_dob == "") {
	error_message = error_message + "<?php echo JS_DOB; ?>";
	error = 1;
  }
<?php
  }
?>
  if (customers_email_address == "") {
	error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
	error = 1;
  }
<?php
  if (ENTRY_COUNTRY_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (document.customers.elements['entry_country_id'].type != "hidden") {
	if (document.customers.entry_country_id.value == 0) {
	  error_message = error_message + "<?php echo JS_COUNTRY; ?>";
	  error = 1;
	}
  }
<?php
  }
  if (ACCOUNT_STATE == 'true' && ENTRY_STATE_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
	if (document.customers.entry_state.value == '') {
	   error_message = error_message + "<?php echo JS_STATE; ?>";
	   error = 1;
	}
  }
<?php
  }
  if (ACCOUNT_POSTCODE == 'true' && ENTRY_POSTCODE_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (entry_postcode == "") {
	error_message = error_message + "<?php echo JS_POST_CODE; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_CITY_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (entry_city == "") {
	error_message = error_message + "<?php echo JS_CITY; ?>";
	error = 1;
  }
<?php
  }
  if (ACCOUNT_SUBURB == 'true' && ENTRY_SUBURB_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (entry_suburb == "") {
	error_message = error_message + "<?php echo JS_SUBURB; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_STREET_ADDRESS_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (entry_street_address == "") {
	error_message = error_message + "<?php echo JS_ADDRESS; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (customers_telephone == "") {
	error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
	error = 1;
  }
<?php
  }
  if (ENTRY_FAX_NUMBER_MIN_LENGTH == 'true' && $customer_address_book_entries_count > 0) {
?>
  if (customers_fax == "") {
	error_message = error_message + "<?php echo JS_FAX; ?>";
	error = 1;
  }
<?php
  }
?>
  if (error == 1) {
	alert(error_message);
	return false;
  } else {
	return true;
  }
}
//--></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
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
  if ($action == 'edit' || $action == 'update') {
	$customer_info_query_raw = "select c.*, a.*, co.* from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on (c.customers_default_address_id = a.address_book_id and a.customers_id = c.customers_id) left join " . TABLE_COMPANIES . " co on (c.customers_id = co.customers_id) where c.customers_id = '" . (int)$HTTP_GET_VARS['cID'] . "'";

	if (sizeof($allowed_shops_array) > 0) {
	  $customers_array = array();
	  $shops_customers_query = tep_db_query("select distinct customers_id from " . TABLE_ORDERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
	  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
		$customers_array[] = $shops_customers['customers_id'];
	  }
	  $shops_customers_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
	  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
		$customers_array[] = $shops_customers['customers_id'];
	  }
	  $customer_info_query_raw .= " and c.customers_id in ('" . implode("', '", $customers_array) . "')";
	}

	$customer_info_query = tep_db_query($customer_info_query_raw);
	$customer_info = tep_db_fetch_array($customer_info_query);
	$cInfo = new objectInfo($customer_info);
	$newsletter_array = array(array('id' => '1', 'text' => ENTRY_NEWSLETTER_YES),
							  array('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
?>
	  <tr>
		<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
	  </tr>
	  <tr><?php echo tep_draw_form('customers', FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')) . 'action=update', 'post', 'onSubmit="return check_form();"') . tep_draw_hidden_field('default_address_id', $cInfo->customers_default_address_id); ?>
		<td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
	  </tr>
	  <tr>
		<td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
	if (ACCOUNT_GENDER == 'true') {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_GENDER; ?></td>
			<td class="main">
<?php
	  if ($error == true) {
		if ($entry_gender_error == true) {
		  echo tep_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . ENTRY_GENDER_ERROR;
		} else {
		  echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
		  echo tep_draw_hidden_field('customers_gender');
		}
	  } else {
		echo tep_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (ENTRY_GENDER_MIN_LENGTH=='true' ? '<span class="errorText">' . TEXT_FIELD_REQUIRED . '</span>' : '');
	  }
?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_FIRST_NAME; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_firstname_error == true) {
		echo tep_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"') . '&nbsp;' . ENTRY_FIRST_NAME_ERROR;
	  } else {
		echo $cInfo->customers_firstname . tep_draw_hidden_field('customers_firstname');
	  }
	} else {
	  echo tep_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"', (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_LAST_NAME; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_lastname_error == true) {
		echo tep_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"') . '&nbsp;' . ENTRY_LAST_NAME_ERROR;
	  } else {
		echo $cInfo->customers_lastname . tep_draw_hidden_field('customers_lastname');
	  }
	} else {
	  echo tep_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"', (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
<?php
	if (ACCOUNT_DOB == 'true') {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
			<td class="main">

<?php
	if ($error == true) {
	  if ($entry_date_of_birth_error == true) {
		echo tep_draw_input_field('customers_dob', tep_date_short($cInfo->customers_dob), 'maxlength="10"') . '&nbsp;' . ENTRY_DATE_OF_BIRTH_ERROR;
	  } else {
		echo $cInfo->customers_dob . tep_draw_hidden_field('customers_dob');
	  }
	} else {
	  echo tep_draw_input_field('customers_dob', tep_date_short($cInfo->customers_dob), 'maxlength="10"', (ENTRY_DOB_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_email_address_error == true) {
		echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR;
	  } elseif ($entry_email_address_check_error == true) {
		echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
	  } elseif ($entry_email_address_exists == true) {
		echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
	  } else {
		echo $customers_email_address . tep_draw_hidden_field('customers_email_address');
	  }
	} else {
	  echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"', true);
	}
?></td>
		  </tr>
<?php
	if (tep_db_field_exists(TABLE_CUSTOMERS, 'customers_discount')) {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_DISCOUNT; ?></td>
			<td class="main"><?php
	  if ($error == true) {
		echo $customers_discount . '%' . tep_draw_hidden_field('customers_discount');
	  } else {
		echo tep_draw_input_field('customers_discount', (string)(float)$cInfo->customers_discount, 'size="3" maxlength="4"') . '%';
	  }
?></td>
		  </tr>
<?php
	}
	if (tep_db_field_exists(TABLE_CUSTOMERS, 'customers_discount_type')) {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_DISCOUNT_TYPE; ?></td>
			<td class="main"><?php
	  if ($error == true) {
		echo ($customers_discount_type=='purchase' ? ENTRY_DISCOUNT_TYPE_PURCHASE : ENTRY_DISCOUNT_TYPE_DISCOUNT) . tep_draw_hidden_field('customers_discount_type');
	  } else {
		echo tep_draw_radio_field('customers_discount_type', 'discount', $cInfo->customers_discount_type=='discount') . ' ' . ENTRY_DISCOUNT_TYPE_DISCOUNT . '<br>' . "\n" .
		tep_draw_radio_field('customers_discount_type', 'purchase', $cInfo->customers_discount_type=='purchase') . ' ' . ENTRY_DISCOUNT_TYPE_PURCHASE;
	  }
?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200">&nbsp;</td>
			<td class="main"><?php echo tep_draw_checkbox_field('customers_status', '1', $cInfo->customers_status==1) . '&nbsp;' . ENTRY_CUSTOMER_STATUS; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
	  </tr>
	  <tr>
		<td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_COUNTRY; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_country_error == true) {
		echo tep_draw_pull_down_menu('entry_country_id', array_merge(array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT)), tep_get_countries()), $cInfo->entry_country_id) . '&nbsp;' . ENTRY_COUNTRY_ERROR;
	  } else {
		echo tep_get_country_name($cInfo->entry_country_id) . tep_draw_hidden_field('entry_country_id');
	  }
	} else {
	  echo tep_draw_pull_down_menu('entry_country_id', array_merge(array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT)), tep_get_countries()), $cInfo->entry_country_id, '', (ENTRY_COUNTRY_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
<?php
	if (ACCOUNT_STATE == 'true') {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_STATE; ?></td>
			<td class="main">
<?php
	  $entry_state = tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
	  if ($error == true) {
		if ($entry_state_error == true) {
		  echo tep_draw_input_field('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)) . '&nbsp;' . ENTRY_STATE_ERROR;
		} else {
		  echo $entry_state . tep_draw_hidden_field('entry_zone_id') . tep_draw_hidden_field('entry_state');
		}
	  } else {
		echo tep_draw_input_field('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state), '', (ENTRY_STATE_MIN_LENGTH=='true' ? true : false));
	  }

?></td>
		 </tr>
<?php
	}
	if (ACCOUNT_POSTCODE == 'true') {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_POST_CODE; ?></td>
			<td class="main">
<?php
	  if ($error == true) {
		if ($entry_post_code_error == true) {
		  echo tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"') . '&nbsp;' . ENTRY_POST_CODE_ERROR;
		} else {
		  echo $cInfo->entry_postcode . tep_draw_hidden_field('entry_postcode');
		}
	  } else {
		echo tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"', (ENTRY_POSTCODE_MIN_LENGTH=='true' ? true : false));
	  }
?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_CITY; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_city_error == true) {
		echo tep_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"') . '&nbsp;' . ENTRY_CITY_ERROR;
	  } else {
		echo $cInfo->entry_city . tep_draw_hidden_field('entry_city');
	  }
	} else {
	  echo tep_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"', (ENTRY_CITY_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
<?php
	if (ACCOUNT_SUBURB == 'true') {
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_SUBURB; ?></td>
			<td class="main">
<?php
	  if ($error == true) {
		if ($entry_suburb_error == true) {
		  echo tep_draw_input_field('suburb', $cInfo->entry_suburb, 'maxlength="32"') . '&nbsp;' . ENTRY_SUBURB_ERROR;
		} else {
		  echo $cInfo->entry_suburb . tep_draw_hidden_field('entry_suburb');
		}
	  } else {
		echo tep_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32"', (ENTRY_GSUBURB_MIN_LENGTH=='true' ? true : false));
	  }
?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_STREET_ADDRESS; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_street_address_error == true) {
		echo tep_draw_textarea_field('entry_street_address', 'soft', '35', '4', $cInfo->entry_street_address) . '&nbsp;' . ENTRY_STREET_ADDRESS_ERROR;
	  } else {
		echo $cInfo->entry_street_address . tep_draw_hidden_field('entry_street_address');
	  }
	} else {
	  echo tep_draw_textarea_field('entry_street_address', 'soft', '35', '4', $cInfo->entry_street_address, (ENTRY_STREET_ADDRESS_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
	  </tr>
	  <tr>
		<td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
			<td class="main">
<?php
	if ($error == true) {
	  if ($entry_telephone_error == true) {
		echo tep_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . '&nbsp;' . ENTRY_TELEPHONE_NUMBER_ERROR;
	  } else {
		echo $cInfo->customers_telephone . tep_draw_hidden_field('customers_telephone');
	  }
	} else {
	  echo tep_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"', (ENTRY_TELEPHONE_NUMBER_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_FAX_NUMBER; ?></td>
			<td class="main">
<?php
	if ($processed == true) {
	  if ($entry_fax_error == true) {
		echo tep_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . '&nbsp;' . ENTRY_TELEPHONE_NUMBER_ERROR;
	  } else {
		echo $cInfo->customers_telephone . tep_draw_hidden_field('customers_telephone');
	  }
	} else {
	  echo tep_draw_input_field('customers_fax', $cInfo->customers_fax, 'maxlength="32"', (ENTRY_FAX_NUMBER_MIN_LENGTH=='true' ? true : false));
	}
?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
	  </tr>
	  <tr>
		<td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
	if (in_array('companies_name', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY) . (tep_not_null(ENTRY_COMPANY_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_name', $cInfo->companies_name, 'size="40"'); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_full_name', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_FULL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FULL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FULL) . (tep_not_null(ENTRY_COMPANY_FULL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FULL_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_textarea_field('companies_full_name', 'soft', '40', '3', $cInfo->companies_full_name); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_type_name', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_TYPE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TYPE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TYPE_NAME) . (tep_not_null(ENTRY_COMPANY_TYPE_NAME_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TYPE_NAME_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_type_name', $cInfo->companies_type_name, 'size="20"'); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_tax_exempt', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_TAX_EXEMPT_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TAX_EXEMPT . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TAX_EXEMPT) . (tep_not_null(ENTRY_COMPANY_TAX_EXEMPT_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TAX_EXEMPT_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_radio_field('companies_tax_exempt', '1', $cInfo->companies_tax_exempt=='1') . ' ' . TEXT_YES . '<br />' . tep_draw_radio_field('companies_tax_exempt', '0', $cInfo->companies_tax_exempt=='0') . ' ' . TEXT_NO; ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_tax_exempt_number', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_TAX_EXEMPT_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TAX_EXEMPT_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TAX_EXEMPT_NUMBER) . (tep_not_null(ENTRY_COMPANY_TAX_EXEMPT_NUMBER_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TAX_EXEMPT_NUMBER_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_tax_exempt_number', $cInfo->companies_tax_exempt_number, 'size="20"'); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_inn', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_INN_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_INN . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_INN) . (tep_not_null(ENTRY_COMPANY_INN_TEXT) ? '&nbsp;' . ENTRY_COMPANY_INN_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_inn', $cInfo->companies_inn, 'size="40"'); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_kpp', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_KPP_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_KPP . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_KPP) . (tep_not_null(ENTRY_COMPANY_KPP_TEXT) ? '&nbsp;' . ENTRY_COMPANY_KPP_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_kpp', $cInfo->companies_kpp, 'size="40"'); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_address_corporate', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_ADDRESS_CORPORATE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_CORPORATE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_CORPORATE) . (tep_not_null(ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_textarea_field('companies_address_corporate', 'soft', '40', '3', $cInfo->companies_address_corporate); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_address_post', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_ADDRESS_POST_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_POST . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_POST) . (tep_not_null(ENTRY_COMPANY_ADDRESS_POST_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_POST_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_textarea_field('companies_address_post', 'soft', '40', '3', $cInfo->companies_address_post); ?></td>
		  </tr>
<?php
	}
	if (in_array('companies_telephone', $company_fields)) {
?>
		  <tr>
			<td class="main" width="200"><?php echo (ENTRY_COMPANY_TELEPHONE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TELEPHONE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TELEPHONE) . (tep_not_null(ENTRY_COMPANY_TELEPHONE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TELEPHONE_TEXT : ''); ?></td>
			<td class="main"><?php echo tep_draw_input_field('companies_telephone', $cInfo->companies_telephone, 'size="40"'); ?></td>
		  </tr>
<?php
	}
?>
		  <tr>
			<td class="main" width="200">&nbsp;</td>
			<td class="main"><?php echo tep_draw_checkbox_field('companies_corporate', '1', $cInfo->companies_corporate=='1') . ' ' . ENTRY_COMPANY_CORPORATE; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
	  </tr>
	  <tr>
		<td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
		  <tr>
			<td class="main" width="200"><?php echo ENTRY_NEWSLETTER; ?></td>
			<td class="main">
<?php
	if ($processed == true) {
	  if ($cInfo->customers_newsletter == '1') {
		echo ENTRY_NEWSLETTER_YES;
	  } else {
		echo ENTRY_NEWSLETTER_NO;
	  }
	  echo tep_draw_hidden_field('customers_newsletter');
	} else {
	  echo tep_draw_pull_down_menu('customers_newsletter', $newsletter_array, (($cInfo->customers_newsletter == '1') ? '1' : '0'));
	}
?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
	  </tr>
	  <tr>
		<td align="right" class="main"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action'))) .'">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
	  </tr></form>
<?php
  } else {
?>
	  <tr>
		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
			<td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
			<td align="right"><?php echo tep_draw_form('search', FILENAME_CUSTOMERS, '', 'get'); ?>
			<table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search'); ?></td>
<?php
	if (sizeof($allowed_shops_array)!=1) {
	  $shops_array = array(array('id' => '', 'text' => TEXT_ALL_SHOPS));
	  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " where 1" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		$shops_array[] = array('id' => $shops['shops_id'], 'text' => str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])));
	  }
?>
				<td>&nbsp;&nbsp;</td>
				<td class="smallText" align="right"><?php echo HEADING_TITLE_SHOP . ' ' . tep_draw_pull_down_menu('shop', $shops_array, '', 'onChange="this.form.submit();"'); ?></td>
<?php
	}
?>
			  </tr>
			</table> </form></td>
		 </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACCOUNT_CREATED; ?></td>
				<td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
			  </tr>
<?php
	$search = "where 1";
	if (isset($HTTP_GET_VARS['shop']) && tep_not_null($HTTP_GET_VARS['shop'])) {
	  if (sizeof($allowed_shops_array)==0 || in_array($HTTP_GET_VARS['shop'], $allowed_shops_array)) $search .= " and shops_id = '" . (int)$HTTP_GET_VARS['shop'] . "'";
	}
	if (isset($HTTP_GET_VARS['search']) && tep_not_null($HTTP_GET_VARS['search'])) {
	  $keywords = tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['search']));
	  $fields = array('c.customers_firstname', 'c.customers_lastname', 'c.customers_email_address', 'c.customers_telephone');
	  reset($fields);
	  while (list(, $field) = each($fields)) {
		$search_array[] = $field . " like '%" . str_replace(" ", "%' and " . $field . " like '%", $keywords) . "%'";
	  }
	  $search .= " and (" . implode(" or ", $search_array) . ")";
	}
	if (sizeof($allowed_shops_array) > 0) {
	  $customers_array = array();
	  $shops_customers_query = tep_db_query("select distinct customers_id from " . TABLE_ORDERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
	  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
		$customers_array[] = $shops_customers['customers_id'];
	  }
	  $shops_customers_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "')");
	  while ($shops_customers = tep_db_fetch_array($shops_customers_query)) {
		$customers_array[] = $shops_customers['customers_id'];
	  }
	  $search .= " and c.customers_id in ('" . implode("', '", $customers_array) . "')";
	}
	$customers_query_raw = "select c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, a.entry_country_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $search . " order by c.customers_lastname, c.customers_firstname";
	$customers_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $customers_query_raw, $customers_query_numrows);
	$customers_query = tep_db_query($customers_query_raw);
	while ($customers = tep_db_fetch_array($customers_query)) {
	  $info_query = tep_db_query("select customers_info_date_account_created as date_account_created, customers_info_date_account_last_modified as date_account_last_modified, customers_info_date_of_last_logon as date_last_logon, customers_info_number_of_logons as number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $customers['customers_id'] . "'");
	  $info = tep_db_fetch_array($info_query);
	  if (!is_array($info)) $info = array();

	  if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $customers['customers_id']))) && !isset($cInfo)) {
		$country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$customers['entry_country_id'] . "' and language_id = '" . (int)$languages_id . "'");
		$country = tep_db_fetch_array($country_query);
		if (!is_array($country)) $country = array();

		$reviews_query = tep_db_query("select count(*) as number_of_reviews from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customers['customers_id'] . "'");
		$reviews = tep_db_fetch_array($reviews_query);
		if (!is_array($reviews)) $reviews = array();

		$customer_info = array_merge($country, $info, $reviews);

		$cInfo_array = array_merge($customers, $customer_info);
		$cInfo = new objectInfo($cInfo_array);
	  }

	  if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) {
		echo '		  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '		  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '\'">' . "\n";
	  }
?>
				<td class="dataTableContent"><?php echo $customers['customers_lastname']; ?></td>
				<td class="dataTableContent"><?php echo $customers['customers_firstname']; ?></td>
				<td class="dataTableContent" align="center"><?php echo tep_date_short($info['date_account_created']); ?></td>
                <td class="dataTableContent" align="center">
<?php
	  if ($customers['customers_status'] == '1') {
		echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action', 'cID')) . '&action=setflag&flag=0&cID=' . $customers['customers_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
	  } else {
		echo '<a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action', 'cID')) . '&action=setflag&flag=1&cID=' . $customers['customers_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
	  }
?></td>
				<td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
	}
?>
			  <tr>
				<td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
					<td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
				  </tr>
<?php
	if (isset($HTTP_GET_VARS['search']) && tep_not_null($HTTP_GET_VARS['search'])) {
?>
				  <tr>
					<td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_CUSTOMERS) . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
				  </tr>
<?php
	}
?>
				</table></td>
			  </tr>
<!-- <?php echo tep_draw_form('download', FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')) . 'action=download'); ?>
			  <tr>
				<td colspan="5" align="right"><table border="0" cellspacing="0" cellpadding="2">
				  <tr>
					<td class="smallText"><?php echo TEXT_DOWNLOAD_CUSTOMERS; ?></td>
					<td class="smallText"><?php echo tep_image_submit('button_download.gif', IMAGE_DOWNLOAD) . '</form>' ; ?></td>
				  </tr>
				</table></td>
			  </tr>
			  </form> -->
			</table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
	case 'confirm':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</strong>');

	  $contents = array('form' => tep_draw_form('customers', FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=deleteconfirm'));
	  $contents[] = array('text' => TEXT_DELETE_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
	  if (isset($cInfo->number_of_reviews) && ($cInfo->number_of_reviews) > 0) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	default:
	  if (isset($cInfo) && is_object($cInfo)) {
		$heading[] = array('text' => '<strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=confirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, 'cID=' . $cInfo->customers_id) . '">' . tep_image_button('button_orders.gif', IMAGE_ORDERS) . '</a> <a href="' . tep_href_link(FILENAME_MAIL, 'selected_box=tools&customer=' . $cInfo->customers_email_address) . '">' . tep_image_button('button_email.gif', IMAGE_EMAIL) . '</a>');
		$contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_CREATED . ' ' . tep_date_short($cInfo->date_account_created));
		$contents[] = array('text' => '<br>' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->date_account_last_modified));
		$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_LAST_LOGON . ' '  . tep_date_short($cInfo->date_last_logon));
		$contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OF_LOGONS . ' ' . $cInfo->number_of_logons);
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . ' ' . $cInfo->countries_name);
		$contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_OF_REVIEWS . ' ' . $cInfo->number_of_reviews);
	  }
	  break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
	echo '			<td width="25%" valign="top">' . "\n";

	$box = new box;
	echo $box->infoBox($heading, $contents);

	echo '			</td>' . "\n";
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