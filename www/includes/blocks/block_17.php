<?php
  if ($banner = tep_banner_exists('column_r')) {
?>
<div id="column_right_banner" style="height: 200px;"><?php echo tep_display_banner($banner); ?></div>
<script language="javascript" type="text/javascript"><!--
  function rotateRightBanner() {
	setTimeout(function() {
	  document.getElementById('column_right_banner').style.opacity = 1;
	  $('#column_right_banner').animate({opacity: 0.01}, 500);
	  setTimeout(function() {
		jQuery('#column_right_banner').load('<?php echo tep_href_link(FILENAME_LOADER, 'action=rotate_banner&group=column_r', $request_type)?>', function() {
		  $('#column_right_banner').animate({opacity: 1}, 500);
		});
	  }, 500);
	  rotateRightBanner();
	}, 12000);
  }
//  rotateRightBanner();
//--></script>
<?php
  }
?>