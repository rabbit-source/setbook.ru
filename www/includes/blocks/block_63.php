<?php
  if ($banner = tep_banner_exists('inner_page')) {
?>
<div id="inner_page_banner"><?php echo tep_display_banner($banner); ?></div><br />
<?php
  }
?>