<?php
  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }
?>

<div id="menubar"><?php
  echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'SSL') . '">' . HEADER_TITLE_TOP . '</a> ' . 
       '<a href="' . tep_catalog_href_link(FILENAME_DEFAULT) . '" target="_blank">' . HEADER_TITLE_ONLINE_CATALOG . '</a> ';
?></div>
