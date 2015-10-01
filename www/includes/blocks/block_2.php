<?php
  $boxID = 'sections';
  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
  $box_info = tep_db_fetch_array($box_info_query);
  $boxHeading = $box_info['blocks_name'];
  $boxContent = tep_show_sections_tree();
  include(DIR_WS_TEMPLATES_BOXES . 'box.php');
?>