<?php
  echo $page['pages_description'];
  //print_r($data);
?>
    <?php echo tep_draw_form('account_newsletter', 'account_subscribe.php', 'post', 'class="form-div"') . tep_draw_hidden_field('action', 'process'); ?>
    <?php if(count($data['section']) > 0): ?>
		<fieldset>
			<legend><?php echo TEXT_SUBSCRIBE_SECTION; ?></legend>
			<?php foreach($data['section'] as $val): ?>
			<div><?php echo tep_draw_checkbox_field('subscribe_1['.$val['id'].']', '1', 0); ?>
			<a href="<?php echo tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $val['id']); ?>"><?php echo $val['name']; ?></a></div>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>
	
	<?php if(count($data['series']) > 0): ?>
		<fieldset>
			<legend><?php echo TEXT_SUBSCRIBE_SERIES; ?></legend>
			<?php foreach($data['series'] as $val): ?>
			<div><?php echo tep_draw_checkbox_field('subscribe_2['.$val['id'].']', '1', 0); ?>
			<a href="<?php echo tep_href_link(FILENAME_SERIES, 'series_id=' . $val['id']); ?>"><?php echo $val['name']; ?></a></div>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>
	
	<?php if(count($data['authors']) > 0): ?>
		<fieldset>
			<legend><?php echo TEXT_SUBSCRIBE_AURHORS; ?></legend>
			<?php foreach($data['authors'] as $val): ?>
			<div><?php echo tep_draw_checkbox_field('subscribe_3['.$val['id'].']', '1', 0); ?>
			<a href="<?php echo tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $val['id']); ?>"><?php echo $val['name']; ?></a></div>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>
	
	<?php if(count($data['munufacturers']) > 0): ?>
		<fieldset>
			<legend><?php echo TEXT_SUBSCRIBE_MUNUFACTURERS; ?></legend>
			<?php foreach($data['munufacturers'] as $val): ?>
			<div><?php echo tep_draw_checkbox_field('subscribe_4['.$val['id'].']', '1', 0); ?>
			<a href="<?php echo tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $val['id']); ?>"><?php echo $val['name']; ?></a></div>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo tep_image_submit('unsubscribe.gif', IMAGE_BUTTON_UPDATE); ?></div>
	</div>
	</form>
