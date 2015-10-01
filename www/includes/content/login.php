<?php
  echo $page['pages_description'];

  echo tep_draw_form('login', tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<legend><?php echo HEADING_RETURNING_CUSTOMER . ' &nbsp; <a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '" style="font-weight: normal;">[' . TEXT_PASSWORD_FORGOTTEN . ']</a>'; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td width="50%"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
		<td width="50%"><?php echo tep_draw_input_field('email_address'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%"><?php echo ENTRY_PASSWORD; ?></td>
		<td width="50%"><?php echo tep_draw_password_field('password'); ?></td>
	  </tr>
	  <tr valign="top">
		<td width="50%">&nbsp;</td>
		<td width="50%"><?php echo tep_draw_checkbox_field('remember_me', '1') . ' ' . ENTRY_REMEMBER_ME; ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo tep_image_submit('button_login.gif', IMAGE_BUTTON_LOGIN); ?></div>
	</div>
	<fieldset>
	<legend><?php echo HEADING_NEW_CUSTOMER; ?></legend>
	<div><?php echo (tep_not_null(TEXT_NEW_CUSTOMER) ? TEXT_NEW_CUSTOMER . '<br /><br />' : '') . TEXT_NEW_CUSTOMER_INTRODUCTION; ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_register.gif', IMAGE_BUTTON_REGISTER) . '</a>'; ?></div>
	</div>
<?php
  if (basename($navigation->snapshot['page'])==FILENAME_CHECKOUT_SHIPPING && ALLOW_CHECKOUT_FOR_UNREGISTERED=='true') {
?>
	<fieldset>
	<legend><?php echo HEADING_CONTINUE_CHECKOUT; ?></legend>
	<div><?php echo TEXT_CONTINUE_CHECKOUT_INTRODUCTION; ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'registration=off', 'SSL') . '">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; ?></div>
	</div>
<?php
  }
?>
	</form>
