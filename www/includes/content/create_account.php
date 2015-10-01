<?php
  if (tep_session_is_registered('confirm_registration')) {
	echo $confirm_registration;
	tep_session_unregister('confirm_registration');
  } else {
	echo $page['pages_description'];

	echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onsubmit="return check_form(create_account);" class="form-div"') . tep_draw_hidden_field('action', 'process'); ?>
	<p><?php echo sprintf(TEXT_ORIGIN_LOGIN, tep_href_link(FILENAME_LOGIN, tep_get_all_get_params(), 'SSL')); ?></p>
	<fieldset>
	<legend><?php echo CATEGORY_PERSONAL; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_CUSTOMER_TYPE; ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('customer_type', 'private', ((!isset($customer_type) || $customer_type=='private') ? true : false), 'onclick="if (document.getElementById(\'account_company\')) { if (this.checked) document.getElementById(\'account_company\').style.display = \'none\'; };"') . ENTRY_CUSTOMER_TYPE_PRIVATE . '<br />' . "\n" . tep_draw_radio_field('customer_type', 'corporate', '', 'onclick="if (document.getElementById(\'account_company\')) { if (this.checked) document.getElementById(\'account_company\').style.display = \'\'; };"') . ENTRY_CUSTOMER_TYPE_CORPORATE; ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FIRST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FIRST_NAME) . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;' . ENTRY_FIRST_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('firstname', '', 'size="20"'); ?></td>
	  </tr>
<?php
	if (ACCOUNT_MIDDLE_NAME == 'true') {
?>
	  <tr>
		<td><?php echo (ENTRY_MIDDLE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_MIDDLE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_MIDDLE_NAME) . (tep_not_null(ENTRY_MIDDLE_NAME_TEXT) ? '&nbsp;' . ENTRY_MIDDLE_NAME_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('middlename', '', 'size="20"'); ?></td>
	  </tr>
<?php
	}
?>
	  <tr>
		<td><?php echo (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_LAST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_LAST_NAME) . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;' . ENTRY_LAST_NAME_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('lastname', '', 'size="20"'); ?></td>
	  </tr>
<?php
	if (ACCOUNT_GENDER == 'true') {
?>
	  <tr>
		<td><?php echo (ENTRY_GENDER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_GENDER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_GENDER) . (tep_not_null(ENTRY_GENDER_TEXT) ? '&nbsp;' . ENTRY_GENDER_TEXT : ''); ?></td>
		<td><?php echo tep_draw_radio_field('gender', 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f') . '&nbsp;&nbsp;' . FEMALE; ?></td>
	  </tr>
<?php
	}
	if (ACCOUNT_DOB == 'true') {
?>
	  <tr>
		<td><?php echo (ENTRY_DOB_MIN_LENGTH=='true' ? '<strong>' . ENTRY_DOB . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_DOB) . (tep_not_null(ENTRY_DOB_TEXT) ? '&nbsp;' . ENTRY_DOB_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('dob', '', 'size="20"'); ?></td>
	  </tr>
<?php
	}
?>
	  <tr>
		<td><?php echo '<strong>' . ENTRY_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' . (tep_not_null(ENTRY_ENAIL_ADDRESS_TEXT) ? '&nbsp;' . ENTRY_EMAIL_ADDRESS_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('email_address', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td></td>
		<td></td>
	  </tr>
	</table>
	</fieldset>
<?php
	if (DOMAIN_ZONE=='ru') {
	  $company_fields = array('company', 'company_full', 'company_inn', 'company_kpp', 'company_ogrn', 'company_okpo', 'company_okogu', 'company_okato', 'company_okved', 'company_okfs', 'company_okopf', 'company_address_corporate', 'company_address_post', 'company_telephone', 'company_fax', 'company_bank', 'company_bik', 'company_ks', 'company_rs', 'company_general', 'company_financial');
	} elseif (DOMAIN_ZONE=='by') {
	  $company_fields = array('company', 'company_full', 'company_inn', 'company_address_corporate', 'company_address_post', 'company_telephone', 'company_fax', 'company_bank', 'company_bik', 'company_rs', 'company_general', 'company_financial');
	} elseif (DOMAIN_ZONE=='kz') {
	  $company_fields = array('company', 'company_full', 'company_inn', 'company_address_corporate', 'company_address_post', 'company_telephone', 'company_fax', 'company_bank', 'company_bik', 'company_rs', 'company_general', 'company_financial');
	} elseif (DOMAIN_ZONE=='ua') {
	  $company_fields = array('company', 'company_full', 'company_inn', 'company_kpp', 'company_address_corporate', 'company_address_post', 'company_telephone', 'company_fax', 'company_bank', 'company_bik', 'company_rs', 'company_general', 'company_financial');
	} else {
	  if (SHOP_ID==14 || SHOP_ID==16) $company_fields = array('company', 'company_type_name', 'company_tax_exempt', 'company_telephone', 'company_fax');
	  else $company_fields = array('company', 'company_address_post', 'company_telephone', 'company_fax');
	}
?>
	<fieldset id="account_company" style="display: <?php echo ((isset($customer_type) && $customer_type=='corporate') ? '' : 'none') ?>;">
	<legend><?php echo CATEGORY_COMPANY; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
	if (in_array('company', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY) . (tep_not_null(ENTRY_COMPANY_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_type_name', $company_fields)) {
	  $company_types_names = array();
	  $company_types_names[] = array('id' => ENTRY_COMPANY_TYPE_NAME_LIBRARY, 'text' => ENTRY_COMPANY_TYPE_NAME_LIBRARY);
	  $company_types_names[] = array('id' => ENTRY_COMPANY_TYPE_NAME_CORPORATION, 'text' => ENTRY_COMPANY_TYPE_NAME_CORPORATION);
	  $company_types_names[] = array('id' => ENTRY_COMPANY_TYPE_NAME_OTHER, 'text' => ENTRY_COMPANY_TYPE_NAME_OTHER);
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_TYPE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TYPE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TYPE_NAME); ?></td>
		<td width="50%"><?php echo tep_draw_pull_down_menu('company_type_name', $company_types_names, '', 'onchange="if (this.selectedIndex==2) { document.getElementById(\'company_type_name_other_div\').style.display = \'\'; } else { document.getElementById(\'company_type_name_other_div\').style.display = \'none\'; company_type_name_other.value = \'\'; }"'); ?><span id="company_type_name_other_div" style="display: none;">&nbsp;&nbsp;<?php echo ENTRY_COMPANY_TYPE_NAME_OTHER . ':' . tep_draw_input_field('company_type_name_other', '', 'size="12"'); ?></span></td>
	  </tr>
<?php
	}
	if (in_array('company_tax_exempt', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_TAX_EXEMPT_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TAX_EXEMPT . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TAX_EXEMPT); ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('company_tax_exempt', '1', true) . ' ' . TEXT_YES; ?><br />
		<?php echo tep_draw_radio_field('company_tax_exempt', '0', false, 'onclick="company_tax_exempt_number.value = \'\';"') . ' ' . TEXT_NO; ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_TAX_EXEMPT_NUMBER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TAX_EXEMPT_NUMBER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TAX_EXEMPT_NUMBER); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_tax_exempt_number', '', 'size="20"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_full', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FULL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FULL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FULL) . (tep_not_null(ENTRY_COMPANY_FULL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FULL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_full', 'soft', '40', '3'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_inn', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_INN_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_INN . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_INN) . (tep_not_null(ENTRY_COMPANY_INN_TEXT) ? '&nbsp;' . ENTRY_COMPANY_INN_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_inn', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_kpp', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_KPP_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_KPP . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_KPP) . (tep_not_null(ENTRY_COMPANY_KPP_TEXT) ? '&nbsp;' . ENTRY_COMPANY_KPP_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_kpp', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_ogrn', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OGRN_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OGRN . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OGRN) . (tep_not_null(ENTRY_COMPANY_OGRN_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OGRN_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_ogrn', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okpo', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKPO_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKPO . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKPO) . (tep_not_null(ENTRY_COMPANY_OKPO_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKPO_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okpo', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okogu', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKOGU_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKOGU . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKOGU) . (tep_not_null(ENTRY_COMPANY_OKOGU_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKOGU_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okogu', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okato', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKATO_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKATO . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKATO) . (tep_not_null(ENTRY_COMPANY_OKATO_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKATO_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okato', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okved', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKVED_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKVED . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKVED) . (tep_not_null(ENTRY_COMPANY_OKVED_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKVED_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_okved', 'soft', '40', '3'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okfs', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKFS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKFS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKFS) . (tep_not_null(ENTRY_COMPANY_OKFS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKFS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okfs', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_okopf', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKOPF_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKOPF . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKOPF) . (tep_not_null(ENTRY_COMPANY_OKOPF_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKOPF_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okopf', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_address_corporate', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_ADDRESS_CORPORATE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_CORPORATE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_CORPORATE) . (tep_not_null(ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_address_corporate', 'soft', '30', '3'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_address_post', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_ADDRESS_POST_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_POST . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_POST) . (tep_not_null(ENTRY_COMPANY_ADDRESS_POST_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_POST_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_address_post', 'soft', '30', '3'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_telephone', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_TELEPHONE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TELEPHONE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TELEPHONE) . (tep_not_null(ENTRY_COMPANY_TELEPHONE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TELEPHONE_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_telephone', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_fax', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FAX_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FAX . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FAX) . (tep_not_null(ENTRY_COMPANY_FAX_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FAX_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_fax', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_bank', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_BANK_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_BANK . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_BANK) . (tep_not_null(ENTRY_COMPANY_BANK_TEXT) ? '&nbsp;' . ENTRY_COMPANY_BANK_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_bank', 'soft', '40', '3'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_bik', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_BIK_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_BIK . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_BIK) . (tep_not_null(ENTRY_COMPANY_BIK_TEXT) ? '&nbsp;' . ENTRY_COMPANY_BIK_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_bik', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_ks', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_KS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_KS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_KS) . (tep_not_null(ENTRY_COMPANY_KS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_KS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_ks', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_rs', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_RS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_RS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_RS) . (tep_not_null(ENTRY_COMPANY_RS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_RS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_rs', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_general', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_GENERAL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_GENERAL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_GENERAL) . (tep_not_null(ENTRY_COMPANY_GENERAL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_GENERAL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_general', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
	if (in_array('company_financial', $company_fields)) {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FINANCIAL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FINANCIAL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FINANCIAL) . (tep_not_null(ENTRY_COMPANY_FINANCIAL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FINANCIAL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_financial', '', 'size="40"'); ?></td>
	  </tr>
<?php
	}
?>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo CATEGORY_PASSWORD; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_PASSWORD . '</strong>&nbsp;<span class="inputRequirement">*</span>' . (tep_not_null(ENTRY_PASSWORD_TEXT) ? '&nbsp;' . ENTRY_PASSWORD_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_password_field('password', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo '<strong>' . ENTRY_PASSWORD_CONFIRMATION . '</strong>&nbsp;<span class="inputRequirement">*</span>' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '&nbsp;' . ENTRY_PASSWORD_CONFIRMATION_TEXT : ''); ?></td>
		<td><?php echo tep_draw_password_field('confirmation', '', 'size="20"'); ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
	if (tep_not_null(ENTRY_NEWSLETTER)) {
?>
	<fieldset>
	<legend><?php echo CATEGORY_OPTIONS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_NEWSLETTER . (tep_not_null(ENTRY_NEWSLETTER_TEXT) ? '&nbsp;<span class="inputRequirement">*</span>' : ''); ?></td>
		<td width="50%"><?php echo tep_draw_checkbox_field('newsletter', '1') . ENTRY_NEWSLETTER_YES; ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
	}

	$is_blacklisted = tep_check_blacklist();
	if (defined('ENTRY_FEEDBACK') && !$is_blacklisted) {
?>
	<fieldset>
	<legend><?php echo CATEGORY_FEEDBACK; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_FEEDBACK; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('comments', 'soft', '30', '3'); ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
	}
	if (TERMS_OF_AGREEMENT=='registration' || TERMS_OF_AGREEMENT=='both') {
?>
	<fieldset>
	<legend><?php echo TERMS_OF_AGREEMENT_TITLE; ?></legend>
	<div><?php echo (tep_not_null(TERMS_OF_AGREEMENT_TEXT) ? '<span style="display: block; margin-bottom: 10px;">' . TERMS_OF_AGREEMENT_TEXT . '</span>' . "\n" : '') . tep_draw_checkbox_field('agreement', '1') . TERMS_OF_AGREEMENT_LINK; ?></div>
	</fieldset>
<?php
	}
?>
	<div class="buttons">
	  <div style="float: left;"><span class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></span></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_register.gif', IMAGE_BUTTON_REGISTER); ?></div>
	</div>
	</form>
<?php
  }
?>