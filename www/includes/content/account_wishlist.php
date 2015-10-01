<?php
  echo $page['pages_description'];
?>
    <?php echo tep_draw_form('account_wishlist', tep_href_link(FILENAME_ACCOUNT_WISHLIST, '', 'SSL'), 'post', 'class="form-div"') . tep_draw_hidden_field('action', ($wls_check>0 ? 'process' : 'update')); ?>
	<fieldset>
	<legend><?php echo MY_WISHLIST_TITLE; ?></legend>
	<div>
<?php echo tep_get_category_level(0, 0, 1, $wls_categories); ?>
	</div>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo ($wls_check>0 ? tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_BUTTON_INSERT)); ?></div>
	</div>
	</form>
