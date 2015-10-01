<?php
  echo $page['pages_description'];

  echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_form();" class="form-div"');

  if (isset($HTTP_GET_VARS['payment_error']) && is_object(${$HTTP_GET_VARS['payment_error']}) && ($error = ${$HTTP_GET_VARS['payment_error']}->get_error())) {
?>
	<div style="font-weight: bold;"><?php echo tep_output_string_protected($error['title']); ?></div>
	<div class="inputRequirement" style="margin: 5px 0px;"><?php echo tep_output_string_protected($error['error']); ?></div>
<?php
  }
  if (1==2) {
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td><?php echo '<strong class="errorText">' . TEXT_SELECTED_BILLING_DESTINATION . '</strong>'; ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo tep_address_label($customer_id, $billto, true); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . IMAGE_BUTTON_CHANGE_ADDRESS . ' &raquo;</a>'; ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
  }
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></legend>
	<div><?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) == 1) {
	echo TEXT_ENTER_PAYMENT_INFORMATION . '<br /><br />' . "\n";
  }

  $js_string = '';
  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
    if ( ($selection[$i]['id'] == $payment) || ($n == 1) ) {
      echo '	  <tr id="defaultSelected" class="moduleRowSelected">' . "\n";
    } else {
      echo '	  <tr class="moduleRow">' . "\n";
    }
?>
		<td valign="top"><?php echo tep_draw_radio_field('payment', $selection[$i]['id'], ($i==0), 'id="rb' . $i . '"' . (tep_not_null($selection[$i]['error']) ? ' disabled="disabled"' : ' onclick="showSelectionFields(\'' . $selection[$i]['id'] . '\');"')); ?></td>
		<td valign="top" width="100%"><?php echo '<label for="rb' . $i . '"><strong>' . $selection[$i]['module'] . '</strong></label><br />' . (tep_not_null($selection[$i]['error']) ? '<span class="errorText">' . $selection[$i]['error'] . '</span>' : nl2br(trim($selection[$i]['description']))); ?><?php
    if (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
	  $js_string .= (tep_not_null($js_string) ? ',' : '') . '"' . $selection[$i]['id'] . '"';
?>
		<table border="0" cellspacing="0" cellpadding="0" id="payment_<?php echo $selection[$i]['id']; ?>" style="display: <?php echo (($selection[$i]['id']==$payment || ($i==0 && !isset($payment))) ? 'block' : 'none'); ?>;">
<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
		if (!isset($selection[$i]['fields'][$j]['field'])) {
?>
		  <tr>
			<td colspan="3"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
		  </tr>
<?php
		} else {
?>
		  <tr>
			<td width="50%"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
			<td width="50%"><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
		  </tr>
<?php
		}
      }
?>
		</table>
<?php
    }
?></td>
	  </tr>
	</table>
<?php
    $radio_buttons++;
  }
?></div>
	<script language="javascript" type="text/javascript"><!--
	  function showSelectionFields(selectedModule) {
		modulesFields = new Array(<?php echo $js_string; ?>);
		for (i=0; i<modulesFields.length; i++) {
		  document.getElementById("payment_"+modulesFields[i]).style.display = "none";
		  if (modulesFields[i]==selectedModule) {
			document.getElementById("payment_"+modulesFields[i]).style.display = "block";
		  }
		}
	  }
	//--></script>
	</fieldset>
	<fieldset>
	<legend><?php echo TABLE_HEADING_COMMENTS; ?></legend>
	<div><?php echo tep_draw_textarea_field('comments', 'soft', '50', '8'); ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="float: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	  <div style="text-align: center;"><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
	</div>
	</form>