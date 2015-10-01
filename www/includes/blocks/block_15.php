<?php
  if ($banner = tep_banner_exists('main_page')) {
?>
<div id="main_page_banner"><?php echo tep_display_banner($banner); ?></div>
<script language="javascript" type="text/javascript"><!--
  function rotateMainBanner(notShowBanner) {
	setTimeout(function() {
	  document.getElementById('main_page_banner').style.opacity = 1;
	  $('#main_page_banner').animate({opacity: 0.01}, 500);
	  setTimeout(function() {
		jQuery('#main_page_banner').load('<?php echo tep_href_link(FILENAME_LOADER, 'action=rotate_banner&group=main_page', $request_type)?>&shown='+notShowBanner, function() {
		  $('#main_page_banner').animate({opacity: 1}, 500);
		});
	  }, 500);
	  if (window.shownBanner) notShowBanner = shownBanner;
	  rotateMainBanner(notShowBanner);
	}, 10000);
  }
//  rotateMainBanner(<?php echo $banner['banners_id']; ?>);
//--></script>
<?php
  }
?>