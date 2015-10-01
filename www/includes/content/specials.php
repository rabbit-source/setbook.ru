<?php
  if ($specials_types_id > 0) {
	echo $type_info['specials_types_description'] . "\n" .
	'<p>&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $type_info['specials_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_SPECIALS_RSS, '', '', 'style="float: left;"') . TEXT_SPECIALS_RSS_TEXT . '</a></p>' . "\n";

	include(DIR_WS_MODULES . 'product_listing.php');
  } else {
	echo $page['pages_description'];
	if (sizeof($active_specials_types_array) > 0) {
	  $specials_types_query = tep_db_query("select specials_types_id, specials_types_name, specials_types_short_name, specials_types_image, specials_types_description from " . TABLE_SPECIALS_TYPES . " where specials_types_id in (" . implode(', ', $active_specials_types_array) . ") and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name");
	  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		echo '<p><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types['specials_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_SPECIALS_RSS, '', '', 'style="margin: 0 4px -4px 0;"') . '</a><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types['specials_types_id']) . '"><strong>' . $specials_types['specials_types_name'] . '</strong></a>' . (tep_not_null($specials_types['specials_types_description']) ? '<br />' . "\n" . (tep_not_null($specials_types['specials_types_image']) ? '<span style="float: right; display: block; text-align: center; width: 45px; padding: 0px; margin: 0px; height: auto;">' . tep_image(DIR_WS_IMAGES . $specials_types['specials_types_image'], $specials_types['specials_types_short_name']) . '</span>' : '') . $specials_types['specials_types_description'] : '') . '</p>' . "\n\n";
	  }
	}
  }
?>