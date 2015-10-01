<?php
  echo $page['pages_description'];
?>
    <?php if (!isset($HTTP_GET_VARS['delete'])) echo tep_draw_form('addressbook', tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, (isset($HTTP_GET_VARS['edit']) ? 'edit=' . $HTTP_GET_VARS['edit'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);" class="form-div"'); else echo '<form class="form-div">'; ?>
<?php
  if (isset($HTTP_GET_VARS['delete'])) {
?>
	<fieldset>
	<legend><?php echo DELETE_ADDRESS_TITLE; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td><?php echo tep_address_label($customer_id, $HTTP_GET_VARS['delete'], true); ?></td>
	  </tr>
	</table>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $HTTP_GET_VARS['delete'] . '&action=deleteconfirm', 'SSL') . '">' . tep_image_button('button_delete.gif', IMAGE_BUTTON_DELETE) . '</a>'; ?></div>
	</div>
<?php
  } else {
?>
	<div>
<?php include(DIR_WS_MODULES . 'address_book_details.php'); ?>
	</div>
<?php
    if (isset($HTTP_GET_VARS['edit']) && is_numeric($HTTP_GET_VARS['edit'])) {
?>
	<div class="buttons">
	  <div style="float: left;"><span class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></span></div>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a> &nbsp; ' . tep_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE) . tep_draw_hidden_field('action', 'update') . tep_draw_hidden_field('edit', $HTTP_GET_VARS['edit']); ?></div>
	</div>
<?php
    } else {
      if (sizeof($navigation->snapshot) > 0) {
        $back_link = ($navigation->snapshot['mode']=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . $navigation->snapshot['page'] . (tep_not_null(tep_array_to_string($navigation->snapshot['get'])) ? '?' . tep_array_to_string($navigation->snapshot['get']) : '');
      } else {
        $back_link = tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL');
      }
?>
	<div class="buttons">
	  <div style="float: left;"><span class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></span></div>
	  <div style="text-align: right;"><?php echo '<a href="' . $back_link . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a> &nbsp; ' . tep_draw_hidden_field('action', 'process') . tep_image_submit('button_add_address.gif', IMAGE_BUTTON_ADD_ADDRESS); ?></div>
	</div>
<?php
    }
  }
?>
	</form>

