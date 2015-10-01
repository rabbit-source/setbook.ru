<?php
  reset($blocks_contents);
  while (list(, $block_content) = each($blocks_contents)) {
	$heading = array();
	$contents = array();
	$contents_string = '';
	reset($block_content['pages']);
	while (list($filename, $pagetitle) = each($block_content['pages'])) {
	  $url_params = '';
	  if (strpos($filename, '?')) {
		$url_params = substr($filename, strpos($filename, '?')+1) . '&';
		$filename = substr($filename, 0, strpos($filename, '?'));
	  }
	  $contents_check_query = tep_db_query("select count(*) as total from " . TABLE_USERS . " u, " . TABLE_USERS_GROUPS_TO_CONTENT . " ug where u.users_groups_id = ug.users_groups_id and u.users_id = '" . tep_db_input($REMOTE_USER) . "' and ug.filename = '" . tep_db_input($filename) . "'");
	  $contents_check = tep_db_fetch_array($contents_check_query);
	  if ($contents_check['total'] > 0) {
		if (!$heading) {
		  $heading[] = array('text'  => $block_content['title'],
							 'link'  => tep_href_link($filename, $url_params . 'selected_box=' . $block_content['id']));
		}
		$contents_string .= '<a href="' . tep_href_link($filename, $url_params . 'selected_box=' . $block_content['id']) . '" class="menuBoxContentLink">' . $pagetitle . '</a><br>';
	  }
	}
	if (tep_not_null($contents_string)) {
	  echo '		  <tr>' . "\n" .
		   '			<td>';
	  $contents[] = array('text'  => $contents_string);
	  $box = new box;
	  $box->table_data_parameters = 'class="menuBoxHeading" onclick="openMenu(\'contents' .  $block_content['id'] . '\');"';
	  echo $box->menuBoxHeading($heading);
	  $box->table_data_parameters = 'class="menuBoxContent" id="contents' .  $block_content['id'] . '"' . ($selected_box== $block_content['id'] ? '' : ' style="display: none;"') . '';
	  echo $box->menuBoxContents($contents);
	  echo '			</td>' . "\n" .
		   '		  </tr>';
	}
  }
?>