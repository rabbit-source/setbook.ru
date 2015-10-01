<?php
  if ($banner = tep_banner_exists('header_banner')) {
	echo tep_display_banner($banner);
  }
?>