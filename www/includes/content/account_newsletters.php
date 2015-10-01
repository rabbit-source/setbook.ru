<?php
  echo $page['pages_description'];
?>
    <?php echo tep_draw_form('account_newsletter', tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'), 'post', 'class="form-div"') . tep_draw_hidden_field('action', 'process'); ?>
	<fieldset>
	<legend><?php echo MY_NEWSLETTERS_TITLE; ?></legend>
	<div><?php echo tep_draw_checkbox_field('newsletter_general', '1', ($newsletter['customers_newsletter'] == '1')) . MY_NEWSLETTERS_GENERAL_NEWSLETTER_DESCRIPTION; ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>
