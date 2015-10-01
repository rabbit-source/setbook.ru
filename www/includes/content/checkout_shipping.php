<?php
  echo $page['pages_description'];

  echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), 'post', 'class="form-div"') . tep_draw_hidden_field('action', 'process');
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr valign="top">
		<td><?php echo tep_address_label($customer_id, $sendto, true); ?></td>
	  </tr>
	  <tr valign="top">
		<td><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . IMAGE_BUTTON_CHANGE_ADDRESS . ' &raquo;</a>'; ?></td>
	  </tr>
	</table>
	</fieldset>
<?php
  if (tep_count_shipping_modules() > 0) {
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></legend>
	<div><?php
	if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
	} elseif ($free_shipping == false) {
	  echo TEXT_ENTER_SHIPPING_INFORMATION . '<br /><br />' . "\n";
	}

	if ($free_shipping == true) {
?>
	<strong><?php echo FREE_SHIPPING_TITLE; ?></strong>&nbsp;<?php echo $quotes[$i]['icon']; ?><br />
	<span class="smallText"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping_code', 'free_free'); ?></span>
<?php
	} else {
	  $radio_buttons = 0;
	  unset($checked);
	  for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
?>
	<strong><?php echo $quotes[$i]['module']; ?></strong><?php if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) { echo '&nbsp;' . $quotes[$i]['icon']; } ?>
<?php
		if (isset($quotes[$i]['error'])) {
?>
	<br /><span class="errorText"><?php echo $quotes[$i]['error']; ?></span><br />
<?php
		} else {
		  echo '	<table border="0" width="100%" cellspacing="0" cellpadding="0" style="padding: 0px; margin: 0px; margin-bottom: 5px;">' . "\n";
		  for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
// set the radio button to be checked if it is the method chosen
			$checked = false;
			if ($shipping['id'] == $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']) $checked = true;
//			elseif (sizeof($quotes)==1 && sizeof($quotes[$i]['methods'])==1) $checked = true;
			elseif (!isset($checked)) $checked = true;
			else $checked = false;

			if ( $checked || ($n == 1 && $n2 == 1) ) {
			  echo '	  <tr class="moduleRowSelected">' . "\n";
			} else {
              echo '	  <tr class="moduleRow">' . "\n";
			}
?>
		<td class="main"><?php echo tep_draw_radio_field('shipping_code', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked, 'style="float: left;" id="rb' . $i . $j . '"') . '<label for="rb' . $i . $j . '">' . $quotes[$i]['methods'][$j]['title'] . '</label>'; ?></td>
<?php
			if ( ($n > 1) || ($n2 > 1) ) {
?>
		<td align="right"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?></td>
<?php
			} else {
?>
		<td align="right"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])) . tep_draw_hidden_field('shipping_code', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></td>
<?php
			}
			$radio_buttons++;
		  }
		  echo '	</table>' . "\n";
		  if (defined('TEXT_ADDITIONAL_SHIPPING_INFO') && tep_not_null(TEXT_ADDITIONAL_SHIPPING_INFO)) echo '<br />' . TEXT_ADDITIONAL_SHIPPING_INFO;
		}
	  }
	}
?></div>
	</fieldset>
<?php
  }
?>
	<fieldset>
	<legend><?php echo TABLE_HEADING_COMMENTS; ?></legend>
	<div><?php echo tep_draw_textarea_field('comments', 'soft', 50, 8); ?></div>
	</fieldset>
	<div class="buttons">
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
	  <div style="float: right;"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
	  <div style="text-align: center;"><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></div>
	</div>
	</form>