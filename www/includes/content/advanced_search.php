<?php
  echo $page['pages_description'];
?>
	<?php echo tep_draw_form('advanced_search', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onsubmit="return check_form(this);" class="form-div"'); ?>
	<fieldset>
	<legend><?php echo HEADING_SEARCH_CRITERIA; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td colspan="2"><?php echo tep_draw_input_field('keywords', '', 'size="93%"'); ?><br /><span class="smallText"><?php echo HEADING_SEARCH_CRITERIA_TEXT; ?></span></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" width="100%" id="advanced_search">
	  <tr>
		<td width="40%"><?php echo ENTRY_CATEGORY; ?></td>
		<td width="60%"><?php echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES))), '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_MANUFACTURER; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('manufacturers', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_SERIE; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('series', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td width="40%"><?php echo ENTRY_AUTHOR; ?> <span class="errorText">*</span></td>
		<td width="60%"><?php echo tep_draw_input_field('authors', '', 'style="width: 95%;"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo ENTRY_PRICE; ?></td>
		<td><?php echo TEXT_FROM . ' ' . tep_draw_input_field('pfrom', '', 'size="5"') . ' &nbsp; ' . TEXT_TO . ' ' . tep_draw_input_field('pto', '', 'size="5"'). ' <span class="mediumText">(' . ($languages_id!=DEFAULT_LANGUAGE_ID ? ENTRY_PRICE_CURRENCY . ' ' . $currency : ENTRY_PRICE_CURRENCY . ' ' . $currencies->currencies[$currency]['title']) . ')</span>'; ?></td>
	  </tr>
	  <tr>
		<td><?php echo ENTRY_YEAR; ?></td>
		<td><?php echo TEXT_FROM . ' ' . tep_draw_input_field('year_from', '', 'size="5" maxlength="4"') . ' &nbsp; ' . TEXT_TO . ' ' . tep_draw_input_field('year_to', '', 'size="5" maxlength="4"'); ?></td
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" width="100%" id="advanced_search">
	  <tr>
		<td width="40%"><?php echo TEXT_SORT_PRODUCTS . TEXT_BY; ?></td>
		<td width="60%"><?php
  $sort_by_array = array();
  $sort_by_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
  $sort_by_array[] = array('id' => '1a', 'text' => TABLE_HEADING_PRICE);
  $sort_by_array[] = array('id' => '2a', 'text' => TABLE_HEADING_YEAR);
  $sort_by_array[] = array('id' => '3a', 'text' => TABLE_HEADING_NAME);
  $sort_by_array[] = array('id' => '4a', 'text' => TABLE_HEADING_AUTHOR);
  echo tep_draw_pull_down_menu('sort', $sort_by_array);
?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_PER_PAGE; ?></td>
		<td><?php
  $per_page_array = array();
  $per_page_array[] = array('id' => '10', 'text' => '10');
  $per_page_array[] = array('id' => '25', 'text' => '25');
  $per_page_array[] = array('id' => '50', 'text' => '50');
  $per_page_array[] = array('id' => '100', 'text' => '100');
  echo tep_draw_pull_down_menu('per_page', $per_page_array);
?></td>
	  </tr>
	  <tr>
		<td colspan="2"><span class="errorText">* <span class="smallText"><?php echo TEXT_SEPARATED_BY_COMMAS; ?></span></span></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH); ?></div>
	</div>
	</form>
