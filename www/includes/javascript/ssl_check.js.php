<script language="javascript" type="text/javascript"><!--
  function checkSSL() {
	var img = new Image();
	img.src = '<?php echo HTTPS_SERVER . DIR_WS_IMAGES; ?>pixel_trans.gif';
	setTimeout(function() {
	  if (img.width==1 || img.complete) s = 'on';
	  else s = 'off';
	  getXMLDOM('<?php echo tep_href_link(FILENAME_LOADER, 'action=check_ssl', 'NONSSL', true); ?>&ssl='+s, 'check_ssl_result');
	}, 1000);
  }
  runOnDocumentLoad += "checkSSL();\n";
//--></script>

