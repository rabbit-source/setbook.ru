<?php
  if (tep_session_is_registered('partner_id')) {
	if ($action=='show_statistics') {
?>
	<table border="0" cellspacing="0" cellpadding="0" class="partner_table" style="margin-right: 4%; float: left;" width="48%">
	  <tr>
		<td colspan="3" align="center"><strong>Статистика по переходам</strong></td>
	  </tr>
	  <tr>
		<td align="center"><strong>Дата</strong></td>
		<td align="center"><strong>Переходы</strong></td>
		<td align="center"><strong>Заказы</strong></td>
	  </tr>
<?php
	$total_visits = 0;
	$total_orders = 0;
	$total_sum = 0;
	for ($i=time(); $i>time()-60*60*24*30*2; $i-=60*60*24) {
	  $visits_count_query = tep_db_query("select count(*) as visits_count from " . TABLE_PARTNERS_STATISTICS . " where partners_id = '" . (int)$partner_id . "' and date_format(date_added,  '%Y-%m-%d') = '" . date('Y-m-d', $i) . "'");
	  $visits_count = tep_db_fetch_array($visits_count_query);
	  $orders_query = tep_db_query("select count(*) as orders_count, sum(orders_total*partners_comission*currency_value) as orders_sum from " . TABLE_ORDERS . " where partners_id = '" . (int)$partner_id . "' and date_format(date_purchased,  '%Y-%m-%d') = '" . date('Y-m-d', $i) . "'");
	  $orders = tep_db_fetch_array($orders_query);
	  $orders['orders_sum'] = round($orders['orders_sum'], $currencies->get_decimal_places($currency));
?>
	  <tr>
		<td align="center"><?php echo tep_date_short(date('Y-m-d', $i)); ?></td>
		<td align="center"><?php echo $visits_count['visits_count']; ?></td>
		<td align="center"><?php echo $orders['orders_count'] . ($orders['orders_sum']>0 ? ' (' . $currencies->format($orders['orders_sum'], false) . ')' : ''); ?></td>
	  </tr>
<?php
	  $total_visits += $visits_count['visits_count'];
	  $total_orders += $orders['orders_count'];
	  $total_sum += $orders['orders_sum'];
	}
?>
	  <tr>
		<td align="center"><strong><?php echo 'Итого:'; ?></strong></td>
		<td align="center"><strong><?php echo $total_visits; ?></strong></td>
		<td align="center"><strong><?php echo $total_orders . ' (' . $currencies->format($total_sum) . ')'; ?></strong></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" class="partner_table" width="48%">
	  <tr>
		<td colspan="3" align="center"><strong>Поступления на ваш счет</strong></td>
	  </tr>
	  <tr>
		<td align="center"><strong>Дата</strong></td>
		<td align="center"><strong>Комментарий</strong></td>
		<td align="center"><strong>Сумма</strong></td>
	  </tr>
<?php
	$total_sum = 0;
	$balance_query = tep_db_query("select * from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$partner_id . "' order by date_added desc");
	while ($balance = tep_db_fetch_array($balance_query)) {
	  $order_currency = $currency;
	  $order_currency_value = $currencies->get_value($currency);
	  if ($balance['orders_id'] > 0) {
		$order_currency_value_info_query = tep_db_query("select currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$balance['orders_id'] . "' and partners_id = '" . (int)$partner_id . "'");
		if (tep_db_num_rows($order_currency_value_info_query) > 0) {
		  $order_currency_value_info = tep_db_fetch_array($order_currency_value_info_query);
		  $order_currency = $order_currency_value_info['currency'];
		  $order_currency_value = $order_currency_value_info['currency_value'];
		}
	  }
	  $balance['partners_balance_sum'] = round($balance['partners_balance_sum'] * $order_currency_value, $currencies->get_decimal_places($order_currency));
?>
	  <tr>
		<td align="center"><?php echo tep_date_short($balance['date_added']); ?></td>
		<td align="center"><?php echo (tep_not_null($balance['partners_balance_comments']) ? tep_output_string_protected($balance['partners_balance_comments']) : '&nbsp;'); ?></td>
		<td align="center"><?php echo $currencies->format($balance['partners_balance_sum'], false); ?></td>
	  </tr>
<?php
	  $total_sum += $balance['partners_balance_sum'];
	}
?>
	  <tr>
		<td align="center"><strong>Итого:</strong></td>
		<td>&nbsp;</td>
		<td align="center"><strong><?php echo $currencies->format($total_sum, false); ?></strong></td>
	  </tr>
	</table>
	<div class="clear"><br /></div><br />
	<div class="buttons">
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	</div>
<?php
	} elseif ($action=='edit' || $action=='edit_process') {
	  $partner_info_query = tep_db_query("select * from " . TABLE_PARTNERS . " where partners_id = '" . (int)$partner_id . "'");
	  $partner_info = tep_db_fetch_array($partner_info_query);
	  echo tep_draw_form('partner_edit', tep_href_link(FILENAME_PARTNER, 'action=edit_process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<legend><?php echo CATEGORY_PARTNER_PERSONAL; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_name', $partner_info['partners_name'], 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_email_address', $partner_info['partners_email_address'], 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_URL; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_url', 'http://' . $partner_info['partners_url'], 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_TELEPHONE; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_telephone', $partner_info['partners_telephone'], 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_BANK; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('partners_bank', 'soft', '40', '4', $partner_info['partners_bank']); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>
<?php
	} elseif ($action=='change_password' || $action=='change_password_process') {
	  echo tep_draw_form('change_password', tep_href_link(FILENAME_PARTNER, 'action=change_password_process', 'SSL'), 'post', 'class="form-div"'); ?>
	<fieldset>
	<legend><?php echo MY_PARTNER_PASSWORD_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_PASSWORD_CURRENT . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('partners_password_current'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_PASSWORD_NEW . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('partners_password_new'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_PASSWORD_CONFIRMATION . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('partners_password_confirmation'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></div>
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>
<?php
	} else {
	  echo $page['pages_description'];
?>
	<form class="form-div">
	<fieldset>
	<legend><?php echo MY_PARTNER_ACCOUNT_TITLE; ?></legend>
	<div><?php echo '<strong><a href="' . tep_href_link(FILENAME_PARTNER, 'action=show_statistics', 'SSL') . '">' . MY_PARTNER_STATISTICS . '</a></strong><br />' . MY_PARTNER_STATISTICS_TEXT; ?><br /><br />
	  <?php echo '<strong><a href="' . tep_href_link(FILENAME_PARTNER, 'action=edit', 'SSL') . '">' . MY_PARTNER_INFORMATION . '</a></strong><br />' . MY_PARTNER_INFORMATION_TEXT; ?><br /><br />
	  <?php echo '<strong><a href="' . tep_href_link(FILENAME_PARTNER, 'action=change_password', 'SSL') . '">' . MY_PARTNER_PASSWORD . '</a></strong><br />' . MY_PARTNER_PASSWORD_TEXT; ?><br /><br />
	  <?php echo '<strong><a href="' . tep_href_link(FILENAME_PARTNER, 'action=logoff', 'SSL') . '">' . MY_PARTNER_LOGOFF . '</a></strong><br />' . MY_PARTNER_LOGOFF_TEXT; ?></div>
	</fieldset>
	</form>
<?php
	}
  } elseif ($action=='remind_password' || $action=='remind_password_process') {
	echo tep_draw_form('remind_password', tep_href_link(FILENAME_PARTNER, 'action=remind_password_process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_LOGIN; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_login', '', 'maxlength="16"'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>
<?php
  } elseif ($action=='register' || $action=='register_process') {
	echo tep_draw_form('partner_register', tep_href_link(FILENAME_PARTNER, 'action=register_process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<legend><?php echo CATEGORY_PARTNER_REGISTER; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_NEW_PARTNER_LOGIN . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_login', '', 'size="20" maxlength="16"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_NEW_PARTNER_PASSWORD . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('partners_password', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo '<strong>' . ENTRY_NEW_PARTNER_PASSWORD_CONFIRMATION . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td><?php echo tep_draw_password_field('partners_confirmation', '', 'size="20"'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo CATEGORY_PARTNER_PERSONAL; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_NAME . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_name', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo '<strong>' . ENTRY_PARTNER_EMAIL_ADDRESS . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_email_address', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_URL; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_url', 'http://', 'size="40"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_TELEPHONE; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_telephone', '', 'size="20"'); ?></td>
	  </tr>
	  <tr>
		<td width="50%"><?php echo ENTRY_PARTNER_BANK; ?></td>
		<td width="50%"><?php echo tep_draw_textarea_field('partners_bank','soft', '40', '4'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset>
	<legend><?php echo CATEGORY_PARTNER_RULES; ?></legend>
	<div><?php echo tep_draw_checkbox_field('agreement', '1') . TERMS_OF_PARTNER_AGREEMENT_TEXT; ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><span class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></span></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_register.gif', IMAGE_BUTTON_REGISTER); ?></div>
	</div>
	</form>
<?php
  } else {
	echo tep_draw_form('login', tep_href_link(FILENAME_PARTNER, 'action=login_process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<legend><?php echo HEADING_RETURNING_PARTNER; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo ENTRY_PARTNER_LOGIN; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('partners_login', '', 'maxlength="16"'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo ENTRY_PARTNER_PASSWORD; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('partners_password'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%">&nbsp;</td>
		<td width="50%"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, 'action=remind_password', 'SSL') . '" class="smallText">' . TEXT_PARTNER_PASSWORD_FORGOTTEN . '</a>'; ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_login.gif', IMAGE_BUTTON_LOGIN); ?></div>
	</div>
	<fieldset>
	<legend><?php echo HEADING_NEW_PARTNER; ?></legend>
	<div><?php echo (tep_not_null(TEXT_NEW_PARTNER) ? TEXT_NEW_PARTNER . '<br /><br />' : '') . TEXT_NEW_PARTNER_INTRODUCTION; ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNER, 'action=register', 'SSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
	</div>
	</form>
<?php
  }
?>