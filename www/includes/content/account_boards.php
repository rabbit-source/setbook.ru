<?php
  echo $page['pages_description'];

  switch ($action) {
	case 'new':
	case 'edit':
	  $adv_info = array();
	  if (tep_not_null($HTTP_POST_VARS)) {
		$adv_info = $HTTP_POST_VARS;
	  } elseif ($action=='edit') {
		$adv_info_query = tep_db_query("select * from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and customers_id = '" . (int)$customer_id . "'");
		$adv_info = tep_db_fetch_array($adv_info_query);
		$adv_info['boards_price'] = tep_round(str_replace(',', '.', $adv_info['boards_price'] * $adv_info['boards_currency_value']), $currencies->get_decimal_places($adv_info['boards_currency']));
		$adv_info['boards_description'] = strip_tags($adv_info['boards_description']);
		list($adv_info['expires_year'], $adv_info['expires_month'], $adv_info['expires_day']) = explode('-', $adv_info['expires_date']);
	  } elseif ($action=='new') {
		$customer_info_query = tep_db_query("select customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_info = tep_db_fetch_array($customer_info_query);
		if ($customer_default_address_id > 0) {
		  $adv_info_query = tep_db_query("select entry_city as customers_city, entry_state as customers_state, entry_country_id, entry_telephone as customers_telephone from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "' order by address_book_id desc limit 1");
		  $adv_info = tep_db_fetch_array($adv_info_query);
		  $adv_info['customers_country'] = tep_get_country_name($adv_info['entry_country_id']);
		} else {
		  $all_countries = tep_get_shops_countries();
		  $customer_country_code = (isset($_SERVER['GEOIP_COUNTRY_CODE']) ? $_SERVER['GEOIP_COUNTRY_CODE'] : tep_get_ip_info());
		  reset($all_countries);
		  while (list(, $country_info) = each($all_countries)) {
			if ($country_info['country_code']==$customer_country_code) {
			  $adv_info['customers_country'] = $country_info['country_name'];
			  break;
			}
		  }
		}
		$adv_info['customers_name'] = preg_replace('/\s{2,}/', ' ', trim($customer_first_name . ' ' . $customer_middle_name . ' ' . $customer_last_name));
		$adv_info = array_merge($customer_info, $adv_info);
	  }
	  if (sizeof($adv_info)==0) $action = 'new';

	  echo tep_draw_form('boards', tep_href_link(FILENAME_ACCOUNT_BOARDS, ($action=='edit' ? 'action=update&boards_id=' . $HTTP_GET_VARS['boards_id'] : 'action=insert'), 'SSL'), 'post', 'enctype="multipart/form-data" onsubmit="return checkBoardForm(\'adv\');" class="form-div"') . "\n";

	  $boards_conditions_array = array();
	  for ($i=5,$j=0; $i>0; $i--,$j++) {
		$boards_conditions_array[$i] = str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(TEXT_REVIEW_VOTES_OF, (5-$j), 5)), (5-$j)) . str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star_none.gif', sprintf(TEXT_REVIEW_VOTES_OF, (5-$j), 5)), $j);
	  }
?>
	<fieldset>
	<legend><?php echo ($action=='edit' ? BOARDS_EDIT_TITLE : BOARDS_NEW_TITLE); ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_TYPE . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php
	  $boards_types_array = array();
	  $boards_types_query = tep_db_query("select boards_types_id, boards_types_short_name from " . TABLE_BOARDS_TYPES . " where language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, boards_types_name");
	  while ($boards_types = tep_db_fetch_array($boards_types_query)) {
		$boards_types_array[] = array('id' => $boards_types['boards_types_id'], 'text' => $boards_types['boards_types_short_name']);
	  }
	  echo tep_draw_pull_down_menu('boards_types_id', $boards_types_array, (isset($bInfo->boards_types_id) ? $bInfo->boards_types_id : '1'));
?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_NAME . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_input_field('customers_name', $adv_info['customers_name'], 'size="30"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_EMAIL_ADDRESS . '</strong><span class="errorText">*</span>'; ?></td>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td style="padding: 0;"><?php echo tep_draw_input_field('customers_email_address', $adv_info['customers_email_address'], 'size="30"'); ?></td>
			<td><?php echo tep_draw_checkbox_field('boards_share_contacts[]', 'email_address', in_array('email_address', explode("\n", $adv_info['boards_share_contacts']))); ?></td>
			<td style="padding: 0;"><?php echo BOARDS_ENTRY_NO_SHARE; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_TELEPHONE_NUMBER; ?></td>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td style="padding: 0;"><?php echo tep_draw_input_field('customers_telephone', $adv_info['customers_telephone'], 'size="30"'); ?></td>
			<td><?php echo tep_draw_checkbox_field('boards_share_contacts[]', 'telephone', in_array('telephone', explode("\n", $adv_info['boards_share_contacts']))); ?></td>
			<td style="padding: 0;"><?php echo BOARDS_ENTRY_NO_SHARE; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_OTHER_CONTACTS; ?></td>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td style="padding: 0;"><?php echo tep_draw_input_field('customers_other_contacts', $adv_info['customers_other_contacts'], 'size="30"'); ?></td>
			<td><?php echo BOARDS_ENTRY_OTHER_CONTACTS_TEXT; ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_COUNTRY . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_pull_down_menu('customers_country', tep_get_boards_countries(), $adv_info['customers_country']) ; ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_STATE; ?></td>
		<td><?php echo tep_draw_input_field('customers_state', $adv_info['customers_state'], 'size="30"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_CITY . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_input_field('customers_city', $adv_info['customers_city'], 'size="30"'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo BOARDS_PRODUCT_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_TITLE . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_input_field('boards_name', $adv_info['boards_name'], 'size="60"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_DESCRIPTION . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_textarea_field('boards_description', 'soft', '60', '8', $adv_info['boards_description']); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_PRICE . '</strong><span class="errorText">*</span>'; ?></td>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td style="padding: 0;"><?php echo tep_draw_input_field('boards_price', $adv_info['boards_price'], 'size="5" style="text-align: right;"'); ?></td>
			<td>&nbsp;&nbsp;</td>
			<td><?php echo BOARDS_ENTRY_CURRENCY; ?></td>
			<td><?php echo tep_draw_pull_down_menu('boards_currency', tep_get_all_currencies(), (isset($adv_info['boards_currency']) ? $adv_info['boards_currency'] : DEFAULT_CURRENCY)); ?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_QUANTITY; ?></td>
		<td><?php echo tep_draw_input_field('boards_quantity', $adv_info['boards_quantity'], 'size="2" style="text-align: right;"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_CONDITION . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php
	  reset($boards_conditions_array);
	  while (list($condition_id, $condition_descr) = each($boards_conditions_array)) {
		echo tep_draw_radio_field('boards_condition', $condition_id, $condition_id==$adv_info['boards_condition']) . ' ' . $condition_descr . '<br />' . "\n";
	  }
?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_IMAGES; ?></td>
		<td id="b_images"><table border="0" cellspacing="0" cellpadding="0"><?php
	  $images = explode("\n", $adv_info['boards_image']);
	  for ($i=0; $i<11; $i++) {
		$images_dir = '';
		if (tep_not_null($adv_info['boards_id'])) {
		  $images_dir = 'images/boards/' . substr(sprintf('%09d', $adv_info['boards_id']), 0, 6) . '/';
		}
		$orig_text = '<a href="' . $images_dir . $images[$i] . '" target="_blank" onmouseover="document.getElementById(\'bimages' . $i . '\').style.display = \'\';" onmouseout="document.getElementById(\'bimages' . $i . '\').style.display = \'none\';" id="bimg' . $i . '">' . $images[$i] . '</a>&nbsp;&nbsp;<a href="#" onclick="document.getElementById(\'boards_images' . $i . '\').innerHTML = newText' . $i . '; return false;" title="' . BOARDS_IMAGE_DELETE . '">[X]</a>';
		if (tep_not_null($images[$i]) && file_exists(DIR_FS_CATALOG . $images_dir . $images[$i])) {
?>
<script language="javascript" type="text/javascript"><!--
  var origText<?php echo $i; ?> = '<?php echo str_replace("'", "\'", trim($orig_text)); ?>';
  var newText<?php echo $i; ?> = '<?php echo str_replace("'", "\'", '' . tep_draw_checkbox_field('boards_images_delete[' . $i . ']', '1', true, 'onclick="if (this.checked==false) document.getElementById(\'boards_images' . $i . '\').innerHTML = origText' . $i . ';"') . ' <a href="' . $images_dir . $images[$i] . '" target="_blank" onmouseover="document.getElementById(\'bimages' . $i . '\').style.display = \'\';" onmouseout="document.getElementById(\'bimages' . $i . '\').style.display = \'none\';" id="bimg' . $i . '">' . BOARDS_IMAGE_DELETE . '</a>'); ?>';
//--></script>
<?php
		}
?>
		  <tr>
			<td><?php echo '<span style="position: absolute; display: none;" id="bimages' . $i . '">' . tep_image($images_dir . $images[$i], $adv_info['boards_name'], '', '', 'style="border: 1px solid black; margin-left: 30px; margin-top: -100px;"') . '</span>' . tep_draw_file_field('boards_images[' . $i . ']') . tep_draw_hidden_field('boards_existing_images[' . $i . ']', $images[$i]); ?></td>
			<td width="50%" align="right" id="boards_images<?php echo $i; ?>"><?php
		if (tep_not_null($images[$i]) && file_exists(DIR_FS_CATALOG . $images_dir . $images[$i])) {
		  echo $orig_text;
		} else {
		  echo '&nbsp;';
		}
?></td>
		  </tr>
<?php
	  }
?></table></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo BOARDS_SETTINGS_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_PAYMENT; ?></td>
		<td><?php
	  reset($boards_payments_array);
	  while (list($payment_id, $payment_descr) = each($boards_payments_array)) {
	 	$checked = false;
		if (in_array($payment_id, explode("\n", $adv_info['boards_payment_method']))) $checked = true;
		echo tep_draw_checkbox_field('boards_payment_methods[]', $payment_id, $checked) . ' ' . $payment_descr . '<br />' . "\n";
	  }
?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_SHIPPING; ?></td>
		<td><?php
	  reset($boards_shippings_array);
	  while (list($shipping_id, $shipping_descr) = each($boards_shippings_array)) {
	 	$checked = false;
		if (in_array($shipping_id, explode("\n", $adv_info['boards_shipping_method']))) $checked = true;
		echo tep_draw_checkbox_field('boards_shipping_methods[]', $shipping_id, $checked) . ' ' . $shipping_descr . '<br />' . "\n";
	  }
?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_EXPIRES_DATE; ?></td>
		<td><table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td colspan="2"><?php echo tep_draw_radio_field('boards_expires', '0', (!isset($adv_info['expires_date']) || $adv_info['expires_date']=='0000-00-00'), 'onclick="document.getElementById(\'boards_images\').style.display=\'none\'; document.boards.expires_day.selectedIndex=0; document.boards.expires_month.selectedIndex=0; document.boards.expires_year.selectedIndex=0;"') . ' ' . BOARDS_ENTRY_EXPIRES_DATE_NONE; ?></td>
		  </tr>
			<td><?php echo tep_draw_radio_field('boards_expires', '1', (isset($adv_info['expires_date']) && $adv_info['expires_date']!='0000-00-00'), 'onclick="document.getElementById(\'boards_images\').style.display=\'block\'"') . ' ' . BOARDS_ENTRY_EXPIRES_DATE_TILL; ?></td>
			<td style="height: 21px;"><div style="display: <?php echo (tep_not_null($adv_info['expires_date']) ? 'block' : 'none'); ?>; margin: 0; padding: 0;" id="boards_images"><?php
	  $expires_days = array(array('id' => '', 'text' => '- -'));
	  for ($i=1; $i<=31; $i++) {
		$expires_days[] = array('id' => $i, 'text' => sprintf('%02d', $i));
	  }
	  echo tep_draw_pull_down_menu('expires_day', $expires_days, $adv_info['expires_day']);

	  $expires_monthes = array(array('id' => '', 'text' => '- - - - - - -'));
	  for ($i=1; $i<=12; $i++) {
		$expires_monthes[] = array('id' => $i, 'text' => $monthes_array[$i]);
	  }
	  echo tep_draw_pull_down_menu('expires_month', $expires_monthes, $adv_info['expires_month']);

	  $expires_years = array(array('id' => '', 'text' => '- - - -'));
	  for ($i=date('Y'); $i<=date('Y')+1; $i++) {
		$expires_years[] = array('id' => $i, 'text' => $i);
	  }
	  echo tep_draw_pull_down_menu('expires_year', $expires_years, $adv_info['expires_year']);
?></div></td>
		  </tr>
		</table></td>
	  </tr>
	  <!--tr>
		<td width="200">&nbsp;</td>
		<td><?php echo tep_draw_checkbox_field('boards_notify', '1', $adv_info['boards_notify']) . ' ' . BOARDS_ENTRY_NOTIFY; ?></td>
	  </tr//-->
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo ($action=='edit' ? tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_BUTTON_INSERT)); ?></div>
	</div>
  </form>
<?php
	  break;

	case 'view_apps':
	  $adv_check_query = tep_db_query("select boards_id, boards_name from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and parent_id = '0' and customers_id = '" . $customer_id . "'");
	  if (tep_db_num_rows($adv_check_query) > 0) {
		$adv_check = tep_db_fetch_array($adv_check_query);
		echo sprintf(BOARDS_APPS_FOUND, $adv_check['boards_name']);
?>
	<form class="form-div">
<?php
		tep_db_query("update " . TABLE_BOARDS . " set boards_viewed = '1' where parent_id = '" . (int)$adv_check['boards_id'] . "'");
		if (tep_not_null($HTTP_GET_VARS['boards_id'])) {
		  $apps_query = tep_db_query("select * from " . TABLE_BOARDS . " where parent_id = '" . (int)$adv_check['boards_id'] . "' order by date_added desc");
		} else {
		  $apps_query = tep_db_query("select * from " . TABLE_BOARDS . " where parent_id > '0' and customers_id = '" . (int)$customer_id . "' order by date_added desc");
		}
		if (tep_db_num_rows($apps_query) > 0) {
		  while ($apps = tep_db_fetch_array($apps_query)) {
			$contacts_string = '';
			if (tep_not_null($apps['customers_email_address'])) $contacts_string .= BOARDS_ENTRY_EMAIL . ' <a href="mailto:' . $apps['customers_email_address'] . '">' . $apps['customers_email_address'] . '</a>';
			if (tep_not_null($apps['customers_telephone'])) $contacts_string .= (tep_not_null($contacts_string) ? ', ' : '') . BOARDS_ENTRY_TELEPHONE . ' ' . $apps['customers_telephone'];
			echo '	<fieldset>' . "\n" .
				 '	<legend>' . tep_date_long($apps['date_added']) . '</legend>' . "\n" .
				 '	<table border="0" cellspacing="0" cellpadding="0" width="100%">' . "\n" .
				 '	  <tr valign="top">' . "\n" .
				 '		<td><strong>' . $apps['customers_name'] . '</strong> (' . $contacts_string . ')<br /><br />' . "\n" .
				 '' . nl2br($apps['boards_description']) . '</td>' . "\n" .
				 '	  </tr>' . "\n" .
				 '	</table>' . "\n" .
				 '	</fieldset>' . "\n";
		  }
		} else {
		  echo '<p>' . BOARDS_ERROR_NO_APPS_FOUND . '</p>';
		}
		echo '</form>';
	  } else {
		echo '<p>' . BOARDS_ERROR_NO_BOARD_FOUND . '</p>';
	  }
?>
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'boards_id')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	</div>
<?php
	  break;

	default:
?>
	<form class="form-div">
<?php
	  $query = "select *, if(last_modified>0, last_modified, date_added) as sort_order from " . TABLE_BOARDS . " where customers_id = '" . (int)$customer_id . "' and parent_id = '0' order by sort_order desc";
	  $advs_new_split = new splitPageResults($query, 10, '*');
	  if ($advs_new_split->number_of_rows > 0) {
		$advs_query = tep_db_query($advs_new_split->sql_query);
		while ($advs = tep_db_fetch_array($advs_query)) {
		  switch ($advs['boards_status']) {
			case '0': 
			  $status = BOARDS_STATUS_MODERATION;
			  break;
			case '1': 
			  $status = BOARDS_STATUS_ACCEPTED;
			  break;
			case '2': 
			  $status = BOARDS_STATUS_REFUSE;
			  break;
			case '3':
			  $status = BOARDS_STATUS_SOLD;
			  break;
			case '-1':
			  $status = BOARDS_STATUS_STOPPED;
			  break;
		  }

		  $boards_images = array();
		  if (tep_not_null($advs['boards_image'])) {
			$boards_images = explode("\n", $advs['boards_image']);

			$boards_image = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $advs['boards_id']), 0, 6) . '/' . $boards_images[0];
		  } else {
			$boards_image = DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif';
		  }

		  $apps_viewed = array();
		  $apps_check_query = tep_db_query("select count(*) as total from " . TABLE_BOARDS . " where parent_id = '" . (int)$advs['boards_id'] . "'");
		  $apps_check = tep_db_fetch_array($apps_check_query);
		  if ($apps_check['total'] > 0) {
			$apps_viewed_query = tep_db_query("select count(*) as total from " . TABLE_BOARDS . " where parent_id = '" . (int)$advs['boards_id'] . "' and boards_viewed = '0'");
			$apps_viewed = tep_db_fetch_array($apps_viewed_query);
		  }

		  if (mb_strlen($advs['boards_description'], 'CP1251') > 300) {
			$short_description = strrev(mb_substr($advs['boards_description'], 0, 220, 'CP1251'));
			$short_description = mb_substr($short_description, strcspn($short_description, '":,.!?()'), mb_strlen($short_description, 'CP1251'), 'CP1251');
			$short_description = trim(strrev($short_description));
			if (in_array(mb_substr($short_description, -1, mb_strlen($short_description, 'CP1251'), 'CP1251'), array(':', '(', ')', ','))) $short_description = mb_substr($short_description, 0, -1, 'CP1251') . '...';
		  } else {
			$short_description = $advs['boards_description'];
		  }

		  echo '	<fieldset>' . "\n" .
			   '	<legend>' . tep_date_long($advs['sort_order']) . ' &nbsp;-&nbsp; <span class="errorText">' . $status . '</span></legend>' . "\n" .
			   '	<table border="0" cellspacing="0" cellpadding="0" width="100%">' . "\n" .
			   '	  <tr valign="top">' . "\n" .
			   '		<td rowspan="3" width="' . SMALL_IMAGE_WIDTH . '">' . tep_image($boards_image, $board_info['boards_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="one_image" style="margin-bottom: 0;"') . '</td>' . "\n" .
			   '		<td rowspan="3">&nbsp;&nbsp;&nbsp;</td>' . "\n" .
			   '		<td colspan="3"><strong class="usualText">' . $advs['boards_name'] . '</strong><br /><br />' . "\n" . $short_description . '</td>' . "\n" .
			   '	  </tr>' . "\n" .
			   '	  <tr valign="top">' . "\n" .
			   '		<td colspan="2"><table border="0" cellspacing="0" cellpadding="0" width="100%">' . "\n" .
			   '		  <tr>' . "\n" .
			   '			<td width="35%" style="padding: 0;">Просмотров объявления: ' . $advs['boards_viewed'] . '</td>' . "\n" .
			   '			<td width="35%" align="center">' . ($apps_check['total']>0 ? '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=view_apps&boards_id=' . $advs['boards_id'], 'SSL') . '">' . BOARDS_ACTION_VIEW_APPS . ' ' . $apps_check['total'] . '</a>' . ($apps_viewed['total']>0 ? ' (<strong>' . BOARDS_ACTION_VIEW_APPS_NEW . ' ' . $apps_viewed['total'] . '</strong>)' : '') : BOARDS_ACTION_VIEW_APPS . ' 0') . '</td>' . "\n" .
			   '			<td width="20%" align="right">' . ($advs['boards_status']=='1' ? ($advs['boards_listing_status']=='1' ? '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=stop&boards_id=' . $advs['boards_id'], 'SSL') . '" onclick="return confirm(\'' . BOARDS_WARNING_CONFIRM_STOP . '\');">' . BOARDS_ACTION_STOP . '</a>' : '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=resume&boards_id=' . $advs['boards_id'], 'SSL') . '" onclick="return confirm(\'' . BOARDS_WARNING_CONFIRM_RESUME . '\');">' . BOARDS_ACTION_RESUME . '</a>') : '&nbsp;') . '</td>' . "\n" .
			   '		  </tr>' . "\n" .
			   '		</table></td>' . "\n" .
			   '	  </tr>' . "\n" .
			   '	  <tr valign="top">' . "\n" .
			   '		<td width="65%" valign="bottom"><br />' . "\n" .
			   '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=delete&delete=' . $advs['boards_id'], 'SSL') . '" onclick="return confirm(\'' . BOARDS_WARNING_CONFIRM_DELETE . '\');">' . BOARDS_ACTION_DELETE . '</a> &nbsp; <a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=edit&boards_id=' . $advs['boards_id'], 'SSL') . '">' . BOARDS_ACTION_EDIT . '</a>';
		  if ($advs['boards_status']!='3') {
			echo ' &nbsp; <a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=sold&boards_id=' . $advs['boards_id'], 'SSL') . '" onclick="return confirm(\'' . BOARDS_WARNING_CONFIRM_SOLD . '\');">' . BOARDS_ACTION_SOLD . '</a>';
		  }
		  echo '</td>' . "\n" .
			   '		<td width="35%" class="row_product_price" valign="bottom" style="text-align: right; float: none; padding: 0;">' . $currencies->format($advs['boards_price'], true, $advs['boards_currency'], $advs['boards_currency_value']) . ($advs['boards_currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($advs['boards_price'], true, DEFAULT_CURRENCY) . ')' : '') . '</td>' . "\n" .
			   '	  </tr>' . "\n" .
			   '	</table>' . "\n" .
			   '	</fieldset>' . "\n";
		}
	  }
?>
<?php
	  if (($advs_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
	  <tr>
		<td class="smallText"><?php echo $advs_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
		<td align="right" class="smallText"><?php echo TEXT_RESULT_PAGE . ' ' . $advs_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
	  </tr>
	</table><br />
<?php
	  }
?>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, tep_get_all_get_params(array('action', 'edit')) . 'action=new', 'SSL') . '">' . tep_image_button('button_insert.gif', IMAGE_BUTTON_INSERT) . '</a>'; ?></div>
	</div>
	</form>
<?php
	  break;
  }
?>