<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) {
	$block_info_query = tep_db_query("select blocks_id, blocks_description from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	$block_info = tep_db_fetch_array($block_info_query);
	if (tep_not_null(strip_tags($block_info['blocks_description']))) {
	  echo $block_info['blocks_description'] . "\n";
	}
  }
?>