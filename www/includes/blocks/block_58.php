<?php
  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
  $box_info = tep_db_fetch_array($box_info_query);
  $boxHeading = '<a href="' . tep_href_link(FILENAME_BOARDS) . '">' . $box_info['blocks_name'] . '</a>';
  $boxID = 'boards';
  $boxContent = '';

  $boards_types_query = tep_db_query("select boards_types_id, boards_types_name from " . TABLE_BOARDS_TYPES . " where boards_types_status = '1' and language_id = '" . (int)$languages_id . "' order by sort_order, boards_types_name");
  $boards_types_count = tep_db_num_rows($boards_types_query);
  $i = 0;
  while ($boards_types = tep_db_fetch_array($boards_types_query)) {
	$boxContent .= '		<div class="li' . ($i==0 ? '_first' : '') . '"><div class="level_0"><a href="' . tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_types['boards_types_id']) . '"' . ($boards_types['boards_types_id']==$boards_types_id ? ' class="active"' : '') . '>' . $boards_types['boards_types_name'] . '</a></div></div>' . "\n";
	$i ++;
  }

  include(DIR_WS_TEMPLATES_BOXES . 'box.php');
?>