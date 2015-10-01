<?php
  $information_row_query = tep_db_query("select information_description from " . TABLE_INFORMATION . " where information_id = '" . (int)$current_information_id . "' and language_id = '" . (int)$languages_id . "'");
  $information_row = tep_db_fetch_array($information_row_query);
  echo $information_row['information_description'];
?>