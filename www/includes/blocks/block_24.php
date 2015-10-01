<?php
  if (defined('STORE_OWNER_SKYPE_NUMBER') && tep_not_null(STORE_OWNER_SKYPE_NUMBER)) {
	$skype_numbers = array_map('trim', explode(',', STORE_OWNER_SKYPE_NUMBER));

	clearstatcache();
	$skype_cache_filename = DIR_FS_CATALOG . 'cache/skype_status.txt';
	$include_skype_cache_filename = false;
	if (file_exists($skype_cache_filename)) {
	  if (date('Y-m-d H:i:s', filemtime($skype_cache_filename)) > date('Y-m-d H:i:s', time()-60*60)) {
		$include_skype_cache_filename = true;
	  }
	}

	if ($include_skype_cache_filename==false) {
	  if (sizeof($skype_numbers) > 1) {
		$random = array_rand($skype_numbers, 1);
		$skype_number = $skype_numbers[$random];
	  } else {
		$skype_number = $skype_numbers[0];
	  }

	  if ($fs = @fopen('http://mystatus.skype.com/' . $skype_number . '.txt', 'r')) {
		stream_set_timeout($fs, 1);
		while (!feof($fs)) {
		  $skype_status_array[] = fgets($fs, 16);
		}
		fclose($fs);
	  }
	  if (!is_array($skype_status_array)) $skype_status_array = array();
	  $skype_status = strtolower(trim(implode('', $skype_status_array)));

	  if ($fc = @fopen($skype_cache_filename, 'w')) {
		stream_set_timeout($fc, 1);
		fwrite($fc, $skype_number . ':' . $skype_status);
		fclose($fc);
	  }
	} else {
	  list($skype_number, $skype_status) = explode(':', strtolower(trim(implode('', @file($skype_cache_filename)))));
	}

	if ($skype_status!='online' && $skype_status!='offline') $skype_status = 'offline';

	echo '<a href="skype:' . $skype_number . '?call">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'skype_' . $skype_status . '.gif', HEADER_TITLE_SKYPE_BUTTON) . '</a>';
  }
?>