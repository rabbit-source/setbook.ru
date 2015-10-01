<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && (PHP_SELF==DIR_WS_CATALOG . 'wholesale/download_price.html' || PHP_SELF==DIR_WS_CATALOG . 'wholesale/order.html') ) {
//  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && $current_information_id==44 ) {
	$boxHeading = ENTRY_CORPORATE_FORM_TITLE;
	if (basename(PHP_SELF)=='download_price.html') $boxHeading = ENTRY_CORPORATE_FORM_CHOOSE_DOWNLOAD;
	$boxContent = '';
	if (tep_session_is_registered('customer_id')) {
	  if (isset($HTTP_GET_VARS['action']) && $HTTP_GET_VARS['action']=='success') {
		$boxContent .= '<p>' . nl2br(tep_output_string_protected(ENTRY_CORPORATE_FORM_SUCCESS)) . '</p>';
	  } else {
		$fields_array = array();
		$fields_array['products_model'] = TEXT_MODEL;
		$fields_array['products_name'] = TEXT_NAME;
		$fields_array['authors_name'] = TEXT_AUTHOR;
		$fields_array['products_price'] = TEXT_PRICE;
		$fields_array['manufacturers_name'] = TEXT_MANUFACTURER;
		$fields_array['series_name'] = TEXT_SERIE;
		$fields_array['products_year'] = TEXT_YEAR_FULL;
		$fields_array['products_pages_count'] = TEXT_PAGES_COUNT;
		$fields_array['products_copies'] = TEXT_COPIES;
		$fields_array['products_covers_name'] = TEXT_COVER;
		$fields_array['products_formats_name'] = TEXT_FORMAT;
		$fields_array['products_url'] = TEXT_URL;
//		$fields_array[] = array('id' => '', 'text' => );
//		$fields_array[] = array('id' => '', 'text' => );

		$specials_array = array();
		$specials_types_query = tep_db_query("select s.specials_types_id, st.specials_types_name from " . TABLE_SPECIALS . " s, " . TABLE_SPECIALS_TYPES . " st where st.specials_types_status = '1' and s.specials_types_id = st.specials_types_id and s.status = '1' and st.specials_types_path <> '' and st.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by st.sort_order, st.specials_types_name");
		while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		  $specials_array[$specials_types['specials_types_id']] = $specials_types['specials_types_name'];
		}

		$new_form_action_1 = 'corporate_price';
		$new_form_action_2 = 'corporate_order';

		if (strpos(REQUEST_URI, 'action')!==false) {
		  $link_1 = preg_replace('/action=[^\&]*/i', 'action=' . $new_form_action_1, REQUEST_URI);
		  $link_2 = preg_replace('/action=[^\&]*/i', 'action=' . $new_form_action_2, REQUEST_URI);
		} elseif (strpos(REQUEST_URI, '?')!==false) {
		  $link_1 = REQUEST_URI . '&amp;action=' . $new_form_action_1;
		  $link_2 = REQUEST_URI . '&amp;action=' . $new_form_action_2;
		} else {
		  $link_1 = REQUEST_URI . '?action=' . $new_form_action_1;
		  $link_2 = REQUEST_URI . '?action=' . $new_form_action_2;
		}

		ob_start();
		if (basename(PHP_SELF)=='download_price.html') {
		  echo tep_draw_form('corporate_download', $link_1, 'post', 'class="form-div"');
?>
	<fieldset>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_FIELDS; ?></td>
		<td width="50%"><?php
		  $i = 0;
		  reset($fields_array);
		  while (list($field_id, $field_text) = each($fields_array)) {
			$field_text = trim($field_text);
			if (substr($field_text, -1)==':') $field_text = substr($field_text, 0, -1);
			if ($field_id=='products_model') echo tep_draw_checkbox_field('', '1', true, 'disabled="disabled"') . ' ' . $field_text . tep_draw_hidden_field('field_' . $i, $field_id) . '<br />' . "\n";
			else echo tep_draw_checkbox_field('field_' . $i, $field_id) . ' ' . $field_text . '<br />' . "\n";
			$i ++;
		  }
?></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_CATEGORIES; ?></td>
		<td width="50%"><?php
		  $i = 0;
		  $categories_query = tep_db_query("select cd.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '1' and c.parent_id = '0' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
		  while ($categories = tep_db_fetch_array($categories_query)) {
			echo tep_draw_checkbox_field('category_' . $i, $categories['categories_id']) . ' ' . $categories['categories_name'] . '<br />' . "\n";
			$i ++;
		  }
?></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS . '<br />' . "\n" . ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('manufacturer', 'soft', '40', '6'); ?></td>
	  </tr>
<?php
		  if (sizeof($specials_array) > 0) {
?>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_SPECIALS; ?></td>
		<td width="50%"><?php
			$i = 0;
			reset($specials_array);
			while (list($special_id, $special_text) = each($specials_array)) {
			  echo tep_draw_checkbox_field('special_' . $i, $special_id) . ' ' . $special_text . '<br />' . "\n";
			  $i ++;
			}
?></td>
	  </tr>
<?php
		  }
?>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_STATUS; ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('status', 'active', true) . ' ' . ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ACTIVE . '<br />' . "\n" . tep_draw_radio_field('status', 'all', false) . ' ' . ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ALL; ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div class="inputRequirement" style="float: left;"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>
<?php
		} else {
		  echo tep_draw_form('corporate_upload', $link_2, 'post', 'class="form-div" enctype="multipart/form-data"');
?>
	<fieldset>
	<legend><?php echo ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_FILE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_CORPORATE_FORM_CHOOSE_FILE . '</strong> <span class="errorText">*</span><br />' . ENTRY_CORPORATE_FORM_CHOOSE_FILE_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('corporate_file', '', '', 'file'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL . '</strong> <span class="errorText">*</span><br />' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('model_no', '1', 'size="2"'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY . '</strong> <span class="errorText">*</span><br />' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('qty_no', '2', 'size="2"'); ?></td>
	  </tr>
	  <tr>
		<td colspan="2" class="errorText"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_ANOTHER_METHOD; ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_TEXT; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN . '</strong> <span class="errorText">*</span><br />' . ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('corporate_text', 'soft', '40', '20'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_OPTIONS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_CORPORATE_FORM_CHOOSE_ABSENT; ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('absent', 'skip', true) . ' ' . ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_SKIP . '<br />' . "\n" . tep_draw_radio_field('absent', 'postpone', false) . ' ' . ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_POSTPONE; ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div class="inputRequirement" style="float: left;"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>
<?php
		}
		$boxContent .= ob_get_clean();
	  }
	} else {
	  $boxContent .= '<p>' . ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED . '</p>';
	}

	echo '<a name="request"></a>';
	include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
  }
?>