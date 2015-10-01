<?php
  echo $page['pages_description'];

  echo tep_draw_form('password_forgotten', tep_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action=process', 'SSL'), 'post', 'class="form-div"');
?>
	<fieldset>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td width="20%"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
		<td width="80%"><?php echo tep_draw_input_field('email_address'); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	</div>
	</form>

