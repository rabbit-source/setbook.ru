<?php
  if ($session_started && $spider_flag==false && ($customer_id==2 || $customer_id==100 || $customer_id==73788)) {
?>
<script language="javascript" type="text/javascript"><!--
  function showCallbackForm(connectionType) {
	if (connectionType=='skype') {
	  document.getElementById('callback_phone').style.display = 'none';
	  document.callbackForm.callback_region_code.value = '';
	  document.callbackForm.callback_telephone_number.value = '';
	  document.getElementById('callback_skype').style.display = (document.getElementById('callback_skype').style.display=='none' ? '' : 'none');
	} else {
	  document.getElementById('callback_skype').style.display = 'none';
	  document.callbackForm.callback_skype_number.value = '';
	  document.getElementById('callback_phone').style.display = (document.getElementById('callback_phone').style.display=='none' ? '' : 'none');
	}
  }

  function callbackMe() {
	error = false;
	qString = '';
	if (document.getElementById('callback_skype').style.display=='') {
	  if (document.callbackForm.callback_skype_number.value=='') {
		alert('<?php echo HEADER_TITLE_CALLBACK_ERROR_SKYPE; ?>');
		document.callbackForm.callback_skype_number.focus();
		error = true;
	  } else {
		qString += '&callback_skype_number='+document.callbackForm.callback_skype_number.value;
	  }
	} else if (document.getElementById('callback_phone').style.display=='') {
	  if (document.callbackForm.callback_country_code.type=='select-one' && document.callbackForm.callback_country_code[document.callbackForm.callback_country_code.selectedIndex].value=='') {
		alert('<?php echo HEADER_TITLE_CALLBACK_ERROR_COUNTRY; ?>');
		error = true;
	  } else if (document.callbackForm.callback_region_code.value=='') {
		alert('<?php echo HEADER_TITLE_CALLBACK_ERROR_REGION_CODE; ?>');
		document.callbackForm.callback_region_code.focus();
		error = true;
	  } else if (document.callbackForm.callback_telephone_number.value=='') {
		alert('<?php echo HEADER_TITLE_CALLBACK_ERROR_PHONE; ?>');
		document.callbackForm.callback_telephone_number.focus();
		error = true;
	  } else {
		qString += '&callback_country_code=' + (document.callbackForm.callback_country_code.type=="hidden" ? document.callbackForm.callback_country_code.value : document.callbackForm.callback_country_code.options[document.callbackForm.callback_country_code.selectedIndex].value) + 
		'&callback_region_code=' + document.callbackForm.callback_region_code.value + 
		'&callback_telephone_number=' + document.callbackForm.callback_telephone_number.value;
	  }
	}
	if (error==false) {
	  getXMLDOM('<?php echo tep_href_link(FILENAME_LOADER, 'action=callback', $request_type); ?>'+qString, 'callbackAnswer');
	}
  }
//--></script>
<div id="callback"> 
  <div class="inner"> 
    <div class="callback_title"><?php echo HEADER_TITLE_CALLBACK; ?></div> 
    <div class="contents"><?php echo HEADER_TITLE_CALLBACK_DESCRIPTION; ?>
	<?php echo tep_draw_form('callbackForm', '#', 'POST', 'onsubmit="callbackMe(); return false;"'); ?>
	  <div id="callback_phone" style="display: none;"><br />
		<table border="0"cellspacing="0" cellpadding="0">
		  <tr>
			<td><small><?php echo HEADER_TITLE_CALLBACK_COUNTRY; ?>&nbsp;</small></td>
			<td><span id="callback_country">&nbsp;<?php echo tep_image(DIR_WS_ICONS . 'flags/' . (tep_not_null($session_country_code) ? strtolower($session_country_code) : 'unknown') . '.gif') . tep_draw_hidden_field('callback_country_code', $session_country_code); ?>&nbsp;</span><small><span onclick="getXMLDOM('<?php echo tep_href_link(FILENAME_LOADER, 'action=show_all_countries_pull_down', $request_type); ?>&country_code='+document.callbackForm.callback_country_code.value, 'callback_country'); this.style.display='none';" id="callback_change_country" style="cursor: pointer; border-bottom: 1px dotted black;"><?php echo HEADER_TITLE_CALLBACK_COUNTRY_CHANGE; ?></span></small></td>
		  </tr>
		  <tr>
			<td><small><?php echo HEADER_TITLE_CALLBACK_REGION_CODE; ?>&nbsp;</small></td>
			<td><?php echo tep_draw_input_field('callback_region_code', '', 'size="3"'); ?></td>
		  </tr>
		  <tr>
			<td><small><?php echo HEADER_TITLE_PHONE_NUMBER; ?>&nbsp;</small></td>
			<td><?php echo tep_draw_input_field('callback_telephone_number', '', 'size="12"'); ?></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php echo tep_image_submit('button_callback.gif', IMAGE_BUTTON_CALLBACK); ?></td>
		  </tr>
		</table>
	  </div>
	  <div id="callback_skype" style="display: none;"><br />
		<table border="0"cellspacing="0" cellpadding="0">
		  <tr>
			<td><small><?php echo HEADER_TITLE_CALLBACK_SKYPE_NUMBER; ?>&nbsp;</small></td>
			<td><?php echo tep_draw_input_field('callback_skype_number', '', 'size="10"'); ?></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php echo tep_image_submit('button_callback.gif', IMAGE_BUTTON_CALLBACK); ?></td>
		  </tr>
		</table>
	  </div>
	</form>
	<div id="callbackAnswer" class="errorText"></div>
	</div> 
  </div> 
</div> 
<?php
  }
?>