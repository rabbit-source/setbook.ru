<?php
  echo $page['pages_description'];
?>

<script language="javascript" type="text/javascript"><!--
  function setResultURI() {
	f = document.download_pricelist;
	str = "";
	for (i=0; i<f.elements.length; i++) {
	  if (f.elements[i].value) {
		f.elements[i].value = f.elements[i].value.replace(/^\s*([\S\s]*)\b\s*$/, '$1');
		if (f.elements[i].value!="" && f.elements[i].name!="result_uri") {
		  addValue = true;
		  if (f.elements[i].type=="radio" || f.elements[i].type=="checkbox") {
			if (!f.elements[i].checked || f.elements[i].disabled || f.elements[i].name=="") addValue = false;
		  }
		  if (addValue) {
			str += f.elements[i].name + "=" + encodeURL(f.elements[i].value) + "&";
		  }
		}
	  }
	}
	str = "<?php echo tep_href_link(FILENAME_PRICELIST, '', 'NONSSL', false); ?>?" + str.substring(0, str.length-1);
	f.result_uri.value = str;
	document.getElementById("resultURI").style.display = "";

	return true;
  }

  function checkAllBoxes(n, check, disable) {
	if (!check) check = 1;
	if (!disable) disable = 0;
	f = document.download_pricelist;
	str = "";
	for (i=0; i<f.elements.length; i++) {
	  if (f.elements[i].type=="checkbox" && f.elements[i].name.indexOf(n)>=0) {
		f.elements[i].checked = (check=='1' ? true : false);
		f.elements[i].disabled = (disable=='1' ? true : false);
	  }
	}
  }
//--></script>

<?php
  echo tep_draw_form('download_pricelist', tep_href_link(FILENAME_PRICELIST), 'get', 'class="form-div" onclick="setResultURI();" onkeyup="setResultURI();"') . tep_draw_hidden_field('action', 'download_pricelist');

  $product_type_info_query = tep_db_query("select products_types_id, products_types_path, products_types_default_status from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and " . (tep_not_null($HTTP_GET_VARS['type']) ? "products_types_path = '" . tep_db_input(tep_sanitize_string($HTTP_GET_VARS['type'])) . "'" : "products_types_default_status = '1'") . "");
  $product_type_info = tep_db_fetch_array($product_type_info_query);
  $products_types_id = $product_type_info['products_types_id'];
  $products_types_path = ($product_type_info['products_types_default_status']=='1' ? '' : $product_type_info['products_types_path']);
  if ($products_types_id > 1) {
	unset($fields_array['authors_name']);
	unset($fields_array['products_year']);
	unset($fields_array['products_pages_count']);
	unset($fields_array['products_copies']);
	unset($fields_array['products_covers_name']);
	unset($fields_array['products_formats_name']);
  }

  $products_types_pathes = array();
  $product_types_query = tep_db_query("select products_types_path, products_types_name, products_types_default_status from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, products_types_name");
  while ($product_types = tep_db_fetch_array($product_types_query)) {
	$products_types_pathes[] = array('id' => ($product_types['products_types_default_status']==1 ? '' : $product_types['products_types_path']), 'text' => $product_types['products_types_name']);
  }

  if (sizeof($products_types_pathes) > 1) {
?>
	<fieldset>
	<legend><?php echo ENTRY_PRICELIST_PRODUCT_TYPE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_CHOOSE_PRODUCT_TYPE; ?></td>
		<td width="50%"><?php echo tep_draw_pull_down_menu('type', $products_types_pathes, $products_types_path, 'onchange="document.location.href=\'' . tep_href_link(FILENAME_PRICELIST, 'action=') . '&type=\'+this.options[this.selectedIndex].value;"');
?></td>
	  </tr>
	</table>
	</fieldset>
<?php
  }
?>
	<fieldset>
	<legend><?php echo ENTRY_PRICELIST_FILE_FORMAT; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_CHOOSE_FILE_FORMAT; ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('ff', 'xml', true, 'id="format_xml" onclick="document.getElementById(\'csv\').style.display = \'none\'; checkAllBoxes(\'f_\', \'0\');"') . ' <label for="format_xml">' . ENTRY_PRICELIST_XML . '</label><br />' . "\n" . tep_draw_radio_field('ff', 'csv', false, 'id="format_csv" onclick="document.getElementById(\'csv\').style.display = \'\';"') . ' <label for="format_csv">' . ENTRY_PRICELIST_CSV . '</label>'; ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset id="csv" style="display: <?php echo ($HTTP_GET_VARS['ff']=='csv' ? 'block' : 'none'); ?>">
	<legend><?php echo ENTRY_PRICELIST_FORMAT_CSV; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_CHOOSE_FIELDS . '<br />' . "\n" . '<small><a href="#" onclick="checkAllBoxes(\'f_\'); return false;">' . ENTRY_PRICELIST_CHECK_ALL . '</a></small>'; ?></td>
		<td width="50%"><?php
  $i = 0;
  reset($fields_array);
  while (list($field_id, $field_text) = each($fields_array)) {
	$field_text = trim($field_text);
	if (substr($field_text, -1)==':') $field_text = substr($field_text, 0, -1);
	$checked = false;
//	if (in_array($field_id, $fileds_required)) $checked = true;
	echo tep_draw_checkbox_field('f_' . $i, $field_id, $checked) . ' ' . $field_text . '<br />' . "\n";
	$i ++;
  }
?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo ENTRY_PRICELIST_SEARCH_CRITERIA; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_CATEGORIES; ?></td>
		<td width="50%"><?php
  echo tep_draw_checkbox_field('', $categories['categories_id'], true, 'onclick="if (this.checked==true) { checkAllBoxes(\'c_\', \'1\', \'1\'); } else { checkAllBoxes(\'c_\', \'0\', \'0\'); }"') . ' ' . ENTRY_PRICELIST_CATEGORIES_ALL . "\n" . '<div id="pricelist_rubrics" style="padding: 0 0 0 20px; margin: 0;">';
  $i = 0;
  $categories_query = tep_db_query("select cd.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_status = '1' and c.categories_listing_status = '1' and c.products_types_id = '" . $products_types_id . "' and c.parent_id = '0' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
  while ($categories = tep_db_fetch_array($categories_query)) {
	echo ($i>0 ? '<br />' . "\n" : '') . tep_draw_checkbox_field('c_' . $i, $categories['categories_id'], true, 'disabled="true"') . ' ' . $categories['categories_name'];
	$i ++;
  }
  echo '</div>';
?></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_MANUFACTURERS . '<br />' . "\n" . ENTRY_PRICELIST_MANUFACTURERS_TEXT; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('m_0', 'soft', '30', '6', '', 'style="overflow: auto;"'); ?></td>
	  </tr>
<?php
  if (sizeof($specials_array) > 0 && $products_types_id == 1) {
?>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_SPECIALS; ?></td>
		<td width="50%"><table border="0" cellspacing="0" cellpadding="0" width="100%"><?php
	$specials_js = '';
	$i = 0;
	reset($specials_array);
	while (list($special_id, $special_text) = each($specials_array)) {
	  echo '		  <tr valign="bottom">' . "\n" .
	  '			<td style="height: 22px;">' . tep_draw_checkbox_field('s_' . $i, $special_id, false, 'onclick="if (this.checked) document.getElementById(\'spd' . $i . '\').innerHTML = sp' . $i . '; else  document.getElementById(\'spd' . $i . '\').innerHTML = \'&nbsp;\';"') . ' ' . $special_text . '</td>' . "\n" .
	  '			<td id="spd' . $i . '">&nbsp;</td>' . "\n" .
	  '		  </tr>' . "\n";
	  $specials_js .= '		var sp' . $i . ' = \'' . str_replace("\n", '', tep_draw_pull_down_menu('sp_' . $i, $specials_periods_array)) . '\'' . "\n";
	  $i ++;
	}
?></table></td>
	  </tr>
	  <script language="javascript" type="text/javascript"><!--
<?php echo $specials_js; ?>
	  //--></script>
<?php
  }
?>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_PRICE; ?></td>
		<td width="50%"><?php echo ENTRY_PRICELIST_PRICE_FROM . ' ' . $currencies->currencies[$currency]['symbol_left'] . ' ' . tep_draw_input_field('pf', '', 'size="5" style="text-align: right;"') . $currencies->currencies[$currency]['symbol_right'] . ' &nbsp; ' . ENTRY_PRICELIST_PRICE_TO . ' ' . $currencies->currencies[$currency]['symbol_left'] . ' ' . tep_draw_input_field('pt', '', 'size="5" style="text-align: right;"') . $currencies->currencies[$currency]['symbol_right']; ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_YEAR; ?></td>
		<td width="50%"><?php echo ENTRY_PRICELIST_YEAR_FROM . ' ' . tep_draw_input_field('yf', '', 'size="5" maxlength="4"') . ' &nbsp; ' . ENTRY_PRICELIST_YEAR_TO . ' ' . tep_draw_input_field('yt', '', 'size="5" maxlength="4"'); ?></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center">+</td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_STATUS; ?></td>
		<td width="50%"><?php echo tep_draw_radio_field('st', 'active', true) . ' ' . ENTRY_PRICELIST_STATUS_ACTIVE . '<br />' . "\n" . tep_draw_radio_field('st', 'all', false) . ' ' . ENTRY_PRICELIST_STATUS_ALL; ?></td>
	  </tr>
<?php
  if (sizeof($comp_methods) > 1) {
?>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo ENTRY_PRICELIST_DOWNLOAD; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PRICELIST_COMPRESSION; ?></td>
		<td width="50%"><?php
	reset($comp_methods);
	while (list($comp_method_id, $comp_method_text) = each($comp_methods)) {
	  echo tep_draw_radio_field('cm', $comp_method_id, $comp_method_id=='none') . ' ' . $comp_method_text . '<br />' . "\n";
	}
?></td>
	  </tr>
<?php
  }
?>
	</table>
	</fieldset>
	<fieldset id="resultURI" style="display: <?php echo (tep_not_null($HTTP_GET_VARS['action']) ? 'block' : 'none'); ?>;">
	<legend><?php echo ENTRY_PRICELIST_RESULT_URI; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td><?php echo ENTRY_PRICELIST_RESULT_URI_TEXT . '<br /><br />' . "\n" . tep_draw_textarea_field('result_uri', 'soft', '65', '6'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div class="inputRequirement" style="float: left;"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>
