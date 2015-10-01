<?php
  echo $page['pages_description'];
?>
	<?php echo tep_draw_form('account_password', tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);" class="form-div"') . tep_draw_hidden_field('action', 'process'); ?>
	<fieldset>
	<legend><?php echo MY_PASSWORD_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo '<strong>' . ENTRY_PASSWORD_CURRENT . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('password_current'); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo '<strong>' . ENTRY_PASSWORD_NEW . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td><?php echo tep_draw_password_field('password_new'); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo '<strong>' . ENTRY_PASSWORD_CONFIRMATION . '</strong>&nbsp;<span class="inputRequirement">*</span>'; ?></td>
		<td><?php echo tep_draw_password_field('password_confirmation'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<span class="inputRequirement">' . FORM_REQUIRED_INFORMATION . '</span>'; ?></div>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a> &nbsp; ' . tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>

