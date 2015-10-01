<?php
  switch($action) {
	case 'new':
	  $adv_info = array();
	  if (tep_not_null($HTTP_POST_VARS)) {
		$adv_info = $HTTP_POST_VARS;
	  } else {
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
		$adv_info['boards_condition'] = '5';
		$adv_info['boards_quantity'] = '1';
		$adv_info = array_merge($customer_info, $adv_info);
		if (isset($HTTP_GET_VARS['products_id'])) {
		  $product_info_query = tep_db_query("select products_id, products_model, products_year, authors_id, manufacturers_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if (!is_array($product_info)) $product_info = array();
		  $product_description_info_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		  $product_description_info = tep_db_fetch_array($product_description_info_query);
		  if (!is_array($product_description_info)) $product_description_info = array();
		  $manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$product_info['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		  $manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
		  if (!is_array($manufacturer_info)) $manufacturer_info = array();
		  $author_info_query = tep_db_query("select authors_name from authors where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		  $author_info = tep_db_fetch_array($author_info_query);
		  if (!is_array($author_info)) $author_info = array();
		  $product_info = array_merge($product_info, $product_description_info, $manufacturer_info, $author_info);
		  if (tep_not_null($product_info['products_name'])) {
			$adv_info['boards_name'] = (tep_not_null($product_info['authors_name']) ? $product_info['authors_name'] . ': ' : '') . $product_info['products_name'];
			$adv_info['boards_description'] = '';
			if (tep_not_null($product_info['products_model'])) $adv_info['boards_description'] .= (tep_not_null($adv_info['boards_description']) ? ', ' : '') . TEXT_MODEL . ' ' . $product_info['products_model'];
			if (tep_not_null($product_info['manufacturers_name'])) {
			  $adv_info['boards_description'] .= (tep_not_null($adv_info['boards_description']) ? ', ' : '') . TEXT_MANUFACTURER . ' ' . $product_info['manufacturers_name'] . ($product_info['products_year']>0 ? ', ' . $product_info['products_year'] . TEXT_YEAR : '');
			}
		  }
		}
	  }
	  list($adv_info['expires_year'], $adv_info['expires_month'], $adv_info['expires_day']) = explode('-', $adv_info['expires_date']);

	  echo tep_draw_form('boards', tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types_id . '&action=insert', 'SSL'), 'post', 'enctype="multipart/form-data" onsubmit="return checkBoardForm(\'adv\');" class="form-div"') . "\n";

	  $boards_conditions_array = array();
	  for ($i=5,$j=0; $i>0; $i--,$j++) {
		$boards_conditions_array[$i] = str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(BOARDS_CONDITION_OF, (5-$j), 5)), (5-$j)) . str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star_none.gif', sprintf(BOARDS_CONDITION_OF, (5-$j), 5)), $j);
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
	  echo tep_draw_pull_down_menu('boards_types_id', $boards_types_array, (isset($bInfo->boards_types_id) ? $bInfo->boards_types_id : $boards_types_id));
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
		<td id="b_images"><script language="javascript" type="text/javascript"><!--
  var k = 0;
  var newField = '<?php echo tep_draw_input_field('', '+', 'style="width: 30px; text-align: center;" onclick="if (this.value!=\\\'&ndash;\\\') { this.value = \\\'&ndash;\\\'; if (k < 10) { document.getElementById(\\\'b_images\\\').innerHTML += newField; k ++; } }"', 'button', false) . ' ' . tep_draw_file_field('boards_images[]') . '<br />'; ?>';
  document.write(newField);
//--></script></td>
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
			<td style="height: 21px;"><div style="display: <?php echo ($adv_info['expires_date'] ? 'block' : 'none'); ?>; margin: 0; padding: 0;" id="boards_images"><?php
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
	  <tr>
		<td width="200">&nbsp;</td>
		<td><?php echo tep_draw_checkbox_field('boards_notify', '1', $adv_info['boards_notify']) . ' ' . BOARDS_ENTRY_NOTIFY; ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'boards_id')) . 'tPath=' . $boards_types_id) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo ($action=='edit' ? tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_BUTTON_INSERT)); ?></div>
	</div>
  </form>
<?php
	  break;

	default:
	  if (tep_not_null($HTTP_GET_VARS['boards_id'])) {
		$board_info_query = tep_db_query("select * from " . TABLE_BOARDS . " where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "' and boards_status = '1'");
		if (tep_db_num_rows($board_info_query) > 0) {
		  tep_db_query("update " . TABLE_BOARDS . " set boards_viewed = boards_viewed + 1 where boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "'");

		  $board_info = tep_db_fetch_array($board_info_query);

		  $condition = round($board_info['boards_condition']*2, 0)/2;
		  $solid_part = 0;
		  $decimal_part = 0;
		  list($solid_part, $decimal_part) = explode('.', str_replace(',', '.', $condition));

		  $stars_string = '';
		  for ($i=1; $i<=5; $i++) {
			if ($i<=$solid_part) $stars_image = 'star.gif';
			elseif ($i==($solid_part+1) && $decimal_part > 0) $stars_image = 'star_half.gif';
			else $stars_image = 'star_none.gif';
			$stars_string .= tep_image(DIR_WS_TEMPLATES_IMAGES . $stars_image, sprintf(BOARDS_CONDITION_OF, $condition, 5));
		  }

		  $boards_images = array();
		  if (tep_not_null($board_info['boards_image'])) {
			$boards_images = explode("\n", $board_info['boards_image']);

			$boards_image = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $board_info['boards_id']), 0, 6) . '/' . $boards_images[0];
			$boards_image_big = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $board_info['boards_id']), 0, 6) . '/big/' . $boards_images[0];
			$boards_image_link = '<a href="' . $boards_image_big . '" onclick="popupImage(\'' . $boards_image_big . '\', \'' . $board_info['boards_name'] . '\'); return false;" target="_blank">' . tep_image($boards_image, $board_info['boards_name'], BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT) . '</a>';
		  } else {
			$boards_image_link = tep_image(DIR_WS_TEMPLATES_IMAGES . 'nofoto_big.gif', $board_info['boards_name'], BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT);
		  }

		  $other_boards_images = array();
		  if (sizeof($boards_images) > 1) {
			reset($boards_images);
			while (list($i, $board_image) = each($boards_images)) {
			  if ($i > 0) {
				$board_image_small = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $board_info['boards_id']), 0, 6) . '/' . $board_image;
				$board_image_big = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $board_info['boards_id']), 0, 6) . '/big/' . $board_image;
				$board_other_images[] = array('image_small' => $board_image_small, 'image_link' => $board_image_big, 'image_title' => 'Изображение ' . $i);
			  }
			}
		  }

		  $share_contacts = explode("\n", $board_info['boards_share_contacts']);
		  if (!is_array($share_contacts)) $share_contacts = array();

		  $contacts_string = '';
		  if (tep_not_null($board_info['customers_email_address']) && !in_array('email_address', $share_contacts)) $contacts_string .= BOARDS_ENTRY_EMAIL_ADDRESS . ' ' . $board_info['customers_email_address'];
		  if (tep_not_null($board_info['customers_telephone']) && !in_array('telephone', $share_contacts)) $contacts_string .= (tep_not_null($contacts_string) ? '<br />' : '') . BOARDS_ENTRY_TELEPHONE_NUMBER . ' ' . $board_info['customers_telephone'];
		  if (tep_not_null($board_info['customers_other_contacts'])) $contacts_string .= (tep_not_null($contacts_string) ? '<br />' : '') . BOARDS_ENTRY_OTHER_CONTACTS . ' ' . $board_info['customers_other_contacts'];

		  $payment_methods_array = array();
		  if (tep_not_null($board_info['boards_payment_method'])) {
			$payment_methods = explode("\n", $board_info['boards_payment_method']);
			reset($payment_methods);
			while (list(, $payment_method_id) = each($payment_methods)) {
			  if (in_array($payment_method_id, array_keys($boards_payments_array))) {
				$payment_methods_array[] = strtolower($boards_payments_array[$payment_method_id]);
			  }
			}
		  }

		  $shipping_methods_array = array();
		  if (tep_not_null($board_info['boards_shipping_method'])) {
			$shipping_methods = explode("\n", $board_info['boards_shipping_method']);
			reset($shipping_methods);
			while (list(, $shipping_method_id) = each($shipping_methods)) {
			  if (in_array($shipping_method_id, array_keys($boards_shippings_array))) {
				$shipping_methods_array[] = strtolower($boards_shippings_array[$shipping_method_id]);
			  }
			}
		  }
?>
	<div class="product_description">
	  <div class="row_product_image"><?php echo $boards_image_link; ?></div>
	  <div class="row_product_author">
<?php
		  if (tep_not_null($board_info['boards_description'])) {
			echo ($board_info['boards_description']==strip_tags($board_info['boards_description']) ? nl2br($board_info['boards_description']) : $board_info['boards_description']) . '<br /><br />' . "\n";
		  }
		  echo '<span class="mediumText"><strong>' . BOARDS_ENTRY_CONTACTS . '</strong><br />' . $board_info['customers_name'] . ' (' . $board_info['customers_country'] . ((tep_not_null($board_info['customers_state']) && $board_info['customers_state']!=$board_info['customers_city']) ? ' / ' . $board_info['customers_state'] : '') . ' / ' . $board_info['customers_city'] . ')' . '<br />' . $contacts_string; 
		  if (sizeof($payment_methods_array) > 0) {
			echo '<br /><br /><strong>' . BOARDS_ENTRY_PAYMENT . '</strong> ' . implode(', ', $payment_methods_array);
		  }
		  if (sizeof($shipping_methods_array) > 0) {
			echo '<br /><br /><strong>' . BOARDS_ENTRY_SHIPPING . '</strong> ' . implode(', ', $shipping_methods_array);
		  }
		  if ($board_info['boards_quantity'] > 0) {
			echo '<br /><br /><strong>' . BOARDS_ENTRY_QUANTITY . '</strong> ' . $board_info['boards_quantity'];
		  }
		  echo '</span>';
?></div>
	  <div class="clear">
		<div style="float: right;"><?php echo $stars_string; ?></div>
		<div class="row_product_price"><?php echo $currencies->format($board_info['boards_price'], true, $board_info['boards_currency'], $board_info['boards_currency_value']); ?></div>
		<div style="clear: right;"><?php if (!tep_check_blacklist()) { ?><br /><a href="#" onclick="document.getElementById('boards_reply_body').style.display = 'block'; document.getElementById('boards_reply').style.display = 'block'; return false;" class="mediumText"><strong><?php echo BOARDS_ENTRY_ADD_APP; ?></strong></a><?php } ?></div>
	  </div><br />
<?php
		  $adv_info = array();
		  if (tep_session_is_registered('customer_id') && !$is_dummy_account) {
			$customer_info_query = tep_db_query("select customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
			$customer_info = tep_db_fetch_array($customer_info_query);
			$adv_info['customers_name'] = preg_replace('/\s{2,}/', ' ', trim($customer_first_name . ' ' . $customer_middle_name . ' ' . $customer_last_name));
			$adv_info = array_merge($customer_info, $adv_info);
		  }
?>
	<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: none; padding: 0; z-index: 9; display: none; margin: 0; background: #AAAAAA; opacity: 0.5;" id="boards_reply_body"></div>
	<div style="position: absolute; width: 553px; padding: 10px 10px 0 10px; display: none; z-index: 10; margin: -250px 5px 0 -5px; background: #FFFFFF; border: 1px solid black; opacity: 2;" id="boards_reply">
<?php
		  ob_start();
?>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<?php echo tep_draw_form('boards', tep_href_link(FILENAME_BOARDS, 'boards_id=' . (int)$HTTP_GET_VARS['boards_id'] . '&action=insert_reply', 'SSL'), 'post', 'onsubmit="return checkBoardForm(\'app\');"'); ?>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_NAME . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_input_field('customers_name', $adv_info['customers_name'], 'size="30"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_EMAIL_ADDRESS . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_input_field('customers_email_address', $adv_info['customers_email_address'], 'size="30"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo BOARDS_ENTRY_TELEPHONE_NUMBER; ?></td>
		<td><?php echo tep_draw_input_field('customers_telephone', $adv_info['customers_telephone'], 'size="30"'); ?></td>
	  </tr>
	  <tr>
		<td width="200"><?php echo '<strong>' . BOARDS_ENTRY_COMMENTS . '</strong><span class="errorText">*</span>'; ?></td>
		<td><?php echo tep_draw_textarea_field('boards_description', 'soft', '60', '8'); ?></td>
	  </tr>
	  <tr>
		<td colspan="2">
		<br /><div class="buttons">
		  <div style="float: left;"><?php echo '<a href="#" onclick="document.getElementById(\'boards_reply_body\').style.display = \'none\'; document.getElementById(\'boards_reply\').style.display=\'none\'; return false;">' . BOARDS_NEW_APP_CLOSE . '</a>'; ?></div>
		  <div style="text-align: right"><?php echo tep_image_submit('button_send.gif', IMAGE_BUTTON_SEND); ?></div>
		</div></td>
	  </tr>
	</form>
	</table>
<?php
		  $boxContent = ob_get_clean();
		  $boxHeading = BOARDS_NEW_APP_NAVBAR_TITLE;
		  include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
?>
	</div>
	  <!--div class="row_product_description"><?php echo ($board_info['boards_description']==strip_tags($board_info['boards_description']) ? nl2br($board_info['boards_description']) : $board_info['boards_description']); ?></div//-->
	</div>
<?php
		  if (sizeof($board_other_images) > 0) {
			$boxID = '';
			$boxHeading = BOARDS_ENTRY_OTHER_IMAGES;
			$boxContent = tep_show_images_carousel($board_other_images, 'board_images');
			include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
		  }
		} else {
		  echo '<p>' . BOARDS_ERROR_NO_BOARD_FOUND . '</p>';
		}
?>
	<br /><div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('boards_id')) . 'tPath=' . $boards_types_id) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	</div>
<?php
	  } else {
		if ($boards_types_id > 0) {
?>  
	   
	<!--<?php echo tep_draw_form('search', tep_href_link(FILENAME_BOARDS), 'get', ''); ?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
	  <tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td><strong><?php echo SEARCH; ?><font color="#FF0000">*</font></strong></td>
			<td><?php echo tep_draw_input_field('search'); ?></td>
			<td><?php echo tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH);?></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td><table>
		  <tr>
			<td><?php echo tep_draw_pull_down_menu('date', array(array('id' => '0', 'text' => 'За все время'),array('id' => '1', 'text' => 'За последний день'),array('id' => '2', 'text' => 'За последнюю неделю'),array('id' => '3', 'text' => 'За последний месяц')));
?></td>
			<td><?php echo tep_draw_input_field('m_start', '', 'size="10"'); ?> &nbsp;&mdash;&nbsp; <?php echo tep_draw_input_field('m_end', '', 'size="10"'); ?></td>
			<td><?php
		  $ctry = array(array('id' => '', 'text' => 'Выберите страну'));
		  $cntry = tep_db_query("SELECT DISTINCT customers_country as country FROM " . TABLE_BOARDS . " WHERE boards_status = '1' ORDER BY customers_country");
		  while ($dat = tep_db_fetch_array($cntry)) {
			$ctry[] = array('id' => $dat['country'], 'text' => $dat['country']);
		  }
		  echo tep_draw_pull_down_menu('country', $ctry, '', 'id="country" onchange="loadList(this.value);"');
?></td>
			<td><div id="cityL" style="display: none;"></div></td>
		  </tr>
		  <tr>		
			<td>Дата</td>
			<td>Диапазон цен</td>
			<td>Страна</td>			
			<td><div id="cityL" style="display: none;">Город</div></td>
		  </tr>                                     
		</table></td>
	  </tr>
	</table>
	</form//-->
	  
<?php
		  if (tep_not_null($HTTP_GET_VARS['sort'])) $sort = $HTTP_GET_VARS['sort'];
		  else $sort = '1d';
		  $podate = "<a title=\"Сортировать по возрастанию по полю: Дата публикации\"  href='" . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=1a') ."'\">Дата публикации</a>";
		  $poprice = "<a title=\"Сортировать по возрастанию по полю: Цена\"  href='" . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=2a') ."'\">Цена</a>";
		  $pogorody = "<a title=\"Сортировать по возрастанию по полю: Город\"  href='" . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=3a') ."'\">Город</a>";
		  switch ($sort) {
			case '2a':
			  $order_str = "order by boards_price asc";
			  $poprice = '<a class="sorted_asc" title="Сортировать по возрастанию по полю: Цена" href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=2d') . '">Цена</a>';
			  break;
			case '2d':
			  $order_str = "order by boards_price desc";
			  $poprice = '<a class="sorted_desc" title="Сортировать по убыванию по полю: Цена" href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=2a') . '">Цена</a>';
			  break;
			case '3a':
			  $order_str = "order by customers_city asc";
			  $pogorody = '<a class="sorted_asc" title="Сортировать по возрастанию по полю: Город" href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=3d') . '">Город</a>';
			  break;
			case '3d':
			  $order_str = "order by customers_city desc";
			  $pogorody = '<a class="sorted_desc" title="Сортировать по убыванию по полю: Город"  href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=3a') . '">Город</a>';
			  break;
			case '1a':
			  $order_str = "order by sort_order";
			  $podate = '<a class="sorted_asc" title="Сортировать по возрастанию по полю: Дата публикации" href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=1d') . '">Дата публикации</a>';
			  break;
			case '1d':
			default:
			  $order_str = "order by sort_order desc";
			  $podate = '<a class="sorted_desc" title="Сортировать по убыванию по полю: Дата публикации" href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('sort', 'page')) . 'tPath=' . $boards_types_id . '&sort=1a') . '">Дата публикации</a>';
			  break;
		  }

		  switch ($date) {
			case 0: 
			  $adv = '';
			  break;
			case 1: 
			  $adv = " and date_format(date_added, '%Y-%m-%d') = '" . date('Y-m-d') . "'";
			  break;
			case 2: 
			  $adv = " and date_format(date_added, '%Y-%m-%d') >= '" . date('Y-m-d', time()-60*60*24*7) . "'";
			  break;
			case 3: 
			  $adv = " and date_format(date_added, '%Y-%m-%d') >= '" . date('Y-m-d', time()-60*60*24*30) . "'";
			  break;
			default: 
			  $adv = "";
			  break;
		  }

		  if ($m_start) $adv .= " and boards_price >= '" . (float)$m_start . "'";
		  if ($m_end) $adv .= " and boards_price <= '" . (float)$m_end . "'";
		  if ($country) $adv .= " and customers_country = '" . tep_db_input(tep_output_string_protected($country)) . "'";
		  if ($city) $adv .= " and customers_city = '" . tep_db_input(tep_output_string_protected($city)) . "'";
		  $search = tep_db_prepare_input($HTTP_GET_VARS['search']);
		  $search = substr($search, 0, 64);
		  $search = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $search);
		  $good = trim(preg_replace("/\s(\S{1,2})\s/", " ", preg_replace('/ +/', "  ", " $search ")));
		  $good = preg_replace('/ +/', " ", $good);

		  $query = "select *, if(last_modified>0, last_modified, date_added) as sort_order from " . TABLE_BOARDS . " where boards_status = '1' and boards_types_id = '" . (int)$boards_types_id . "' $adv and (boards_name like '%" . str_replace(' ', "%' or boards_name like '%", $good). "%') $order_str";

		  $products_new_split = new splitPageResults($query, '10', 'boards_id');
		  $q = $query;

		  if ($products_new_split->number_of_rows > 0) {
?>
	<div class="sortHeading"><?php echo TEXT_SORT_PRODUCTS . TEXT_BY; ?><br>
	<?php echo $podate;?>
	<?php echo $poprice;?>
	<?php echo $pogorody;?>
	<!-- <a title="Сбросить установленную сортировку" onmouseover="this.href='<?php echo tep_href_link(FILENAME_BOARDS, "sort=") ;?>';" href="<?php echo tep_href_link(FILENAME_BOARDS, "sort=") ;?>"><?php echo TEXT_FILTER_PRODUCTS_RESET; ?></a> -->
	</div>
<?php
			$cur_row = 0;
			$query = tep_db_query($products_new_split->sql_query);
			while ($data = mysql_fetch_array($query)) {
			  if (mb_strlen($data['boards_description'], 'CP1251') > 200) {
				$short_description = strrev(mb_substr($data['boards_description'], 0, 120, 'CP1251'));
				$short_description = mb_substr($short_description, strcspn($short_description, '":,.!?()'), mb_strlen($short_description, 'CP1251'), 'CP1251');
				$short_description = trim(strrev($short_description));
				if (in_array(mb_substr($short_description, -1, mb_strlen($short_description, 'CP1251'), 'CP1251'), array(':', '(', ')', ','))) $short_description = mb_substr($short_description, 0, -1, 'CP1251') . '...';
			  } else {
				$short_description = $data['boards_description'];
			  }

			  $condition = round($data['boards_condition']*2, 0)/2;
			  $solid_part = 0;
			  $decimal_part = 0;
			  list($solid_part, $decimal_part) = explode('.', str_replace(',', '.', $condition));

			  $stars_string = '';
			  for ($i=1; $i<=5; $i++) {
				if ($i<=$solid_part) $stars_image = 'star.gif';
				elseif ($i==($solid_part+1) && $decimal_part > 0) $stars_image = 'star_half.gif';
				else $stars_image = 'star_none.gif';
				$stars_string .= tep_image(DIR_WS_TEMPLATES_IMAGES . $stars_image, sprintf(BOARDS_CONDITION_OF, $condition, 5));
			  }

			  $boards_link = tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('boards_id')) . 'boards_id=' . $data['boards_id']);

			  $lc_align = 'center';
			  $lc_text = '';
			  $form_string = '';
			  $row_params = 'class="productListing-data-image"';

			  $boards_images = array();
			  if (tep_not_null($data['boards_image'])) {
				$boards_images = explode("\n", $data['boards_image']);
			  }

			  if (tep_not_null($data['boards_image'])) {
				$boards_image_link = DIR_WS_IMAGES . 'boards/' . substr(sprintf('%09d', $data['boards_id']), 0, 6) . '/thumbs/' . $boards_images[0];
			  } else {
				$boards_image_link = DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif';
			  }
			  $lc_text = '<a href="' . $boards_link . '">' . tep_image($boards_image_link, $data['boards_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>' . "\n" .
			  (sizeof($boards_images)>1 ? '<div class="icon_fragments" title="' . TEXT_ADDITIONAL_IMAGES_1 . '"></div>' . "\n" : '');

			  $list_box_contents[$cur_row][] = array('align' => $lc_align,
													 'params' => $row_params,
													 'text'  => $lc_text);

			  $lc_text = '';
			  $lc_align = '';
			  $row_params = 'class="productListing-data-name"';

			  $lc_text .= '<div style="float: right;">' . $stars_string . '</div><div class="row_product_author">' . tep_date_long($data['sort_order']) . ', ' . $data['customers_name'] . ', ' . $data['customers_country'] . ' / ' . $data['customers_city'] . '</div>' . "\n" .
			  '<div class="row_product_name"><a href="' . $boards_link . '">' . $data['boards_name'] . '</a>';
			  if (tep_not_null($short_description)) {
				$lc_text .= "\n" . '<div class="row_product_description">' . $short_description . '</div>' . "\n";
			  }
			  $lc_text .= '</div>' . "\n";
			  $lc_text .= '<div class="row_product_price">' . ($data['boards_price']>0 ? $currencies->format($data['boards_price'], true, $data['boards_currency'], $data['boards_currency_value']) . ($data['boards_currency']!=DEFAULT_CURRENCY ? ' (' . $currencies->format($data['boards_price'], true, DEFAULT_CURRENCY) . ')' : '') : 'Договорная') . '</div>' . "\n" ;

			  $list_box_contents[$cur_row][] = array('align' => $lc_align,
													 'params' => $row_params,
													 'text'  => $lc_text);

			  $cur_row = sizeof($list_box_contents);
			}

			$box = new tableBox(array());
			$box->table_width = '';
			$box->table_border = '0';
			$box->table_parameters = 'class="productListing"';
			$box->table_cellspacing = '0';
			$box->table_cellpadding = '0';
			echo $box->tableBox($list_box_contents);
		  } else {
			echo '<p>' . BOARDS_NO_FOUND . '</p>';
		  }

		  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
	<div id="listing-split">
	  <div style="float: left;"><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></div>
	  <div style="text-align: right"><?php echo TEXT_RESULT_PAGE . ' ' . $products_new_split->display_links(MAX_DISPLAY_REVIEWS_RESULTS, tep_get_all_get_params(array('boards_id', 'page', 'info', 'x', 'y'))); ?></div>
	</div>
<?php
		  }
?>
	<br /><div class="buttons">
	  <div style="text-align: right;" id="addAdv"><?php if (!tep_check_blacklist()) echo '<a href="' . tep_href_link(FILENAME_BOARDS, tep_get_all_get_params(array('action', 'edit')) . 'tPath=' . $boards_types_id . '&action=new') . '"' . (!tep_session_is_registered('customer_id') ? ' onclick="document.getElementById(\'addAdv\').innerHTML = \'' . htmlspecialchars(sprintf(BOARDS_ERROR_REGISTER, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'))) . '\'; document.getElementById(\'addAdv\').style.textAlign = \'left\'; return false;"' : '') . '>' . tep_image_button('button_insert.gif', IMAGE_BUTTON_INSERT) . '</a>'; ?></div>
	</div>
<?php
		} else {
		  echo $page['pages_description'];
		  $boards_types_query = tep_db_query("select boards_types_id, boards_types_name, boards_types_description from " . TABLE_BOARDS_TYPES . " where boards_types_status = '1' and language_id = '" . (int)$languages_id . "' order by sort_order, boards_types_name");
		  while ($boards_types = tep_db_fetch_array($boards_types_query)) {
			echo '<p><a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types['boards_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_BOARDS_RSS, '', '', 'style="margin: 0 4px -4px 0;"') . '</a><a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types['boards_types_id']) . '"><strong>' . $boards_types['boards_types_name'] . '</strong></a>' . (tep_not_null($boards_types['boards_types_description']) ? '<br />' . "\n" . $boards_types['boards_types_description'] : '') . '</p>' . "\n\n";
		  }
		}
	  }
	  break;
  }
?>