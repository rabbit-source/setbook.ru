<?php
  if ($authors_id > 0) {
	if (empty($HTTP_GET_VARS['page']) && empty($HTTP_GET_VARS['detailed']) && empty($HTTP_GET_VARS['sort'])) {
	  $author_info_query = tep_db_query("select * from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $author_info = tep_db_fetch_array($author_info_query);

	  if (tep_not_null($author_info['authors_description']) && tep_not_null($author_info['authors_image']) && file_exists(DIR_FS_CATALOG . 'images/' . $author_info['authors_image'])) {
		echo tep_image(DIR_WS_IMAGES . $author_info['authors_image'], $author_info['authors_name'], '', '', 'align="right" class="one_image"');
	  }
	  echo $author_info['authors_description'];
	}

	include(DIR_WS_MODULES . 'product_listing.php');
  } else {
	echo $page['pages_description'];

	echo '<p align="center">' . $letters_string . '</p>' . "\n";

	$authors_query_row = "select authors_id, authors_name, authors_description, authors_image from " . TABLE_AUTHORS . " where authors_status = '1' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'";
	if ($letter!='all') $authors_query_row .= " and authors_letter = '" . tep_db_input($letter) . "'";
	$authors_query_row .= " order by sort_order, authors_letter, authors_name";
	$listing_split = new splitPageResults($authors_query_row, '50');

	if ($listing_split->number_of_rows > 0) {
	  $listing_string = '	<div id="listing-split">' . "\n" .
	  '	  <div style="float: left;">' . $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS) . '</div>' . "\n" .
	  '	  <div style="text-align: right;">' . TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))) . '</div>' . "\n" .
	  '	</div>' . "\n";

	  if (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3') {
		echo $listing_string;
	  }

	  $listing_query = tep_db_query($listing_split->sql_query);
	  while ($row = tep_db_fetch_array($listing_query)) {
		$author_image = '';
		if (tep_not_null($row['authors_image'])) {
		  $author_image = tep_image(DIR_WS_IMAGES . $row['authors_image'], $row['authors_name'], '', '', 'style="border: 1px solid #000000;"') . '<br />' . "\n";
		}
		echo '<p><a href="' . tep_href_link(FILENAME_AUTHORS, 'tPath=' . $show_product_type . '&authors_id=' . $row['authors_id']) . '">' . $author_image . $row['authors_name'] . '</a></p>' . "\n";
	  }

	  if (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') {
		echo $listing_string;
	  }
	}
  }
?>