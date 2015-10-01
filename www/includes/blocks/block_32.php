<?php
  $section_path = 'from_abroad';
  $boxID = 'sections_' . $section_path;
  $box_info_query = tep_db_query("select sections_id, sections_name from " . TABLE_SECTIONS . " where sections_path = '" . tep_db_input($section_path) . "' and language_id = '" . (int)$languages_id . "'");
  if (tep_db_num_rows($box_info_query) > 0) {
	$box_info = tep_db_fetch_array($box_info_query);
	$boxHeading = '<a href="' . tep_href_link(FILENAME_DEFAULT, 'sPath=' . $box_info['sections_id']) . '">' . $box_info['sections_name'] . '</a>';
	$boxContent = tep_show_sections_tree($box_info['sections_id']);
	include(DIR_WS_TEMPLATES_BOXES . 'box.php');
  }
?>