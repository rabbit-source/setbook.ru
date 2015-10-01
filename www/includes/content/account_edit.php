<?php
  echo $page['pages_description'];

  echo tep_draw_form('account_edit', tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onsubmit="return check_form(account_edit);" class="form-div"') . tep_draw_hidden_field('customer_type', $account['customers_type']) . tep_draw_hidden_field('action', 'process');
  if (ACCOUNT_MIDDLE_NAME == 'true') {
	list($account_firstname, $account_middlename) = explode(' ', $account['customers_firstname']);
  } else {
	$account_firstname = $account['customers_firstname'];
	$account_middlename = '';
  }
?>
	<fieldset>
	<legend><?php echo CATEGORY_PERSONAL; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo (ENTRY_FIRST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_FIRST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_FIRST_NAME) . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;' . ENTRY_FIRST_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('firstname', $account_firstname, 'size="20"'); ?></td>
	  </tr>
<?php
  if (ACCOUNT_MIDDLE_NAME == 'true') {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_MIDDLE_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_MIDDLE_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_MIDDLE_NAME) . (tep_not_null(ENTRY_MIDDLE_NAME_TEXT) ? '&nbsp;' . ENTRY_MIDDLE_NAME_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('middlename', $account_middlename, 'size="20"'); ?></td>
	  </tr>
<?php
  }
?>
	  <tr>
		<td><?php echo (ENTRY_LAST_NAME_MIN_LENGTH=='true' ? '<strong>' . ENTRY_LAST_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_LAST_NAME) . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;' . ENTRY_LAST_NAME_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('lastname', $account['customers_lastname'], 'size="20"'); ?></td>
	  </tr>
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($account['customers_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>
	  <tr>
		<td><?php echo (ENTRY_GENDER_MIN_LENGTH=='true' ? '<strong>' . ENTRY_GENDER . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_GENDER) . (tep_not_null(ENTRY_GENDER_TEXT) ? '&nbsp;' . ENTRY_GENDER_TEXT : ''); ?></td>
		<td><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE; ?></td>
	  </tr>
<?php
  }
  if (ACCOUNT_DOB == 'true') {
?>
	  <tr>
		<td><?php echo (ENTRY_DOB_MIN_LENGTH=='true' ? '<strong>' . ENTRY_DOB . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_DOB) . (tep_not_null(ENTRY_DOB_TEXT) ? '&nbsp;' . ENTRY_DOB_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('dob', tep_date_short($account['customers_dob'])); ?></td>
	  </tr>
<?php
  }
?>
	  <tr>
		<td><?php echo '<strong>' . ENTRY_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '&nbsp;' . ENTRY_EMAIL_ADDRESS_TEXT : ''); ?></td>
		<td><?php echo tep_draw_input_field('email_address', $account['customers_email_address']); ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
  if ($account['customers_type']=='corporate') {
?>
	<fieldset>
	<legend><?php echo CATEGORY_COMPANY; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY) . (tep_not_null(ENTRY_COMPANY_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company', $company_info['company'], 'size="40"'); ?></td>
	  </tr>
<?php
	if (DEFAULT_CURRENCY=='USD' || DEFAULT_CURRENCY=='EUR') {
	} else {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FULL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FULL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FULL) . (tep_not_null(ENTRY_COMPANY_FULL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FULL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_full', 'soft', '40', '3', $company_info['company_full']); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_INN_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_INN . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_INN) . (tep_not_null(ENTRY_COMPANY_INN_TEXT) ? '&nbsp;' . ENTRY_COMPANY_INN_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_inn', $company_info['company_inn'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_KPP_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_KPP . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_KPP) . (tep_not_null(ENTRY_COMPANY_KPP_TEXT) ? '&nbsp;' . ENTRY_COMPANY_KPP_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_kpp', $company_info['company_kpp'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OGRN_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OGRN . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OGRN) . (tep_not_null(ENTRY_COMPANY_OGRN_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OGRN_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_ogrn', $company_info['company_ogrn'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKPO_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKPO . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKPO) . (tep_not_null(ENTRY_COMPANY_OKPO_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKPO_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okpo', $company_info['company_okpo'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKOGU_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKOGU . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKOGU) . (tep_not_null(ENTRY_COMPANY_OKOGU_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKOGU_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okogu', $company_info['company_okogu'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKATO_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKATO . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKATO) . (tep_not_null(ENTRY_COMPANY_OKATO_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKATO_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okato', $company_info['company_okato'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKVED_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKVED . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKVED) . (tep_not_null(ENTRY_COMPANY_OKVED_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKVED_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_okved', 'soft', '40', '3', $company_info['company_okved']); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKFS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKFS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKFS) . (tep_not_null(ENTRY_COMPANY_OKFS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKFS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okfs', $company_info['company_okfs'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_OKOPF_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_OKOPF . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_OKOPF) . (tep_not_null(ENTRY_COMPANY_OKOPF_TEXT) ? '&nbsp;' . ENTRY_COMPANY_OKOPF_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_okopf', $company_info['company_okopf'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_ADDRESS_CORPORATE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_CORPORATE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_CORPORATE) . (tep_not_null(ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_address_corporate', 'soft', '40', '3', $company_info['company_address_corporate']); ?></td>
	  </tr>
<?php
	}
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_ADDRESS_POST_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_ADDRESS_POST . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_ADDRESS_POST) . (tep_not_null(ENTRY_COMPANY_ADDRESS_POST_TEXT) ? '&nbsp;' . ENTRY_COMPANY_ADDRESS_POST_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_address_post', 'soft', '40', '3', $company_info['company_address_post']); ?></td>
	  </tr>
<?php
	if (DEFAULT_CURRENCY=='USD' || DEFAULT_CURRENCY=='EUR') {
	} else {
?>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_TELEPHONE_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_TELEPHONE . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_TELEPHONE) . (tep_not_null(ENTRY_COMPANY_TELEPHONE_TEXT) ? '&nbsp;' . ENTRY_COMPANY_TELEPHONE_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_telephone', $company_info['company_telephone'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FAX_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FAX . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FAX) . (tep_not_null(ENTRY_COMPANY_FAX_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FAX_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_fax', $company_info['company_fax'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_BANK_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_BANK . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_BANK) . (tep_not_null(ENTRY_COMPANY_BANK_TEXT) ? '&nbsp;' . ENTRY_COMPANY_BANK_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('company_bank', 'soft', '40', '3', $company_info['company_bank']); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_BIK_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_BIK . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_BIK) . (tep_not_null(ENTRY_COMPANY_BIK_TEXT) ? '&nbsp;' . ENTRY_COMPANY_BIK_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_bik', $company_info['company_bik'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_KS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_KS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_KS) . (tep_not_null(ENTRY_COMPANY_KS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_KS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_ks', $company_info['company_ks'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_RS_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_RS . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_RS) . (tep_not_null(ENTRY_COMPANY_RS_TEXT) ? '&nbsp;' . ENTRY_COMPANY_RS_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_rs', $company_info['company_rs'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_GENERAL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_GENERAL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_GENERAL) . (tep_not_null(ENTRY_COMPANY_GENERAL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_GENERAL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_general', $company_info['company_general'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo (ENTRY_COMPANY_FINANCIAL_MIN_LENGTH=='true' ? '<strong>' . ENTRY_COMPANY_FINANCIAL . '</strong>&nbsp;<span class="inputRequirement">*</span>' : ENTRY_COMPANY_FINANCIAL) . (tep_not_null(ENTRY_COMPANY_FINANCIAL_TEXT) ? '&nbsp;' . ENTRY_COMPANY_FINANCIAL_TEXT : ''); ?></td>
		<td width="50%"><?php echo tep_draw_input_field('company_financial', $company_info['company_financial'], 'size="40"'); ?></td>
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
	  <div class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>
