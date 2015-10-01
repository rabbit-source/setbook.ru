<?php
  $specials_types_string = '';
  $a = array_intersect($active_specials_types_array, array('1', '2', '3'));
  $specials_types_query = tep_db_query("select specials_types_id, specials_types_name, specials_types_path from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $a) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name limit 3");
  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	$specials_types_string .= (tep_not_null($specials_types_string) ? '&nbsp; | &nbsp;' : '') . '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'view=' . $specials_types['specials_types_path']) . '">' . $specials_types['specials_types_name'] . '</a>';
  }
  if (tep_not_null($specials_types_string)) {
	echo '<div id="note">' . "\n" .
	'  <div class="inner">' . "\n" .
	'    <div class="note_title">' . HEADER_TITLE_NOTE . '</div>' . "\n" .
	'    <div class="contents">' . $specials_types_string . '</div>' . "\n" .
	'  </div>' . "\n" .
	'</div>' . "\n";
  }
?>