<?php
  if ($series_id > 0) {
	if (empty($HTTP_GET_VARS['page']) && empty($HTTP_GET_VARS['detailed']) && empty($HTTP_GET_VARS['sort'])) {
	  $serie_info_query = tep_db_query("select * from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $serie_info = tep_db_fetch_array($serie_info_query);

	  if (tep_not_null($serie_info['series_description']) && tep_not_null($serie_info['series_image']) && file_exists(DIR_FS_CATALOG . 'images/' . $serie_info['series_image'])) {
		echo tep_image(DIR_WS_IMAGES . $serie_info['series_image'], $serie_info['series_name'], '', '', 'align="right" class="one_image"');
	  }
	  echo $serie_info['series_description'];
	}

	include(DIR_WS_MODULES . 'product_listing.php');
  } else {
	echo $page['pages_description'];

	echo '<p align="center">' . $letters_string . '</p>' . "\n";

	$series_query_row = "select series_id, series_name, series_description, series_image from " . TABLE_SERIES . " where series_status = '1' and products_types_id = '" . (int)$show_product_type . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'";
	if ($letter!='all') $series_query_row .= " and series_letter = '" . tep_db_input($letter) . "'";
	$series_query_row .= " order by sort_order, series_name";
	$listing_split = new splitPageResults($series_query_row, '50');

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
		$serie_image = '';
		if (tep_not_null($row['series_image'])) {
		  $serie_image = tep_image(DIR_WS_IMAGES . $row['series_image'], $row['series_name'], '', '', 'style="border: 1px solid #000000;"') . '<br />' . "\n";
		}
		echo '<p><a href="' . tep_href_link(FILENAME_SERIES, 'tPath=' . $show_product_type . '&series_id=' . $row['series_id']) . '">' . $serie_image . $row['series_name'] . '</a></p>' . "\n";
	  }

	  if (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') {
		echo $listing_string;
	  }
	}
  }
?>