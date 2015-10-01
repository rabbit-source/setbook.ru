<?php
  if ($manufacturers_id > 0) {
	if (empty($HTTP_GET_VARS['page']) && empty($HTTP_GET_VARS['detailed']) && empty($HTTP_GET_VARS['sort'])) {
	  $manufacturer_info_query = tep_db_query("select * from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_id = '" . (int)$manufacturers_id . "' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $manufacturer_info = tep_db_fetch_array($manufacturer_info_query);

	  if (tep_not_null($manufacturer_info['manufacturers_description']) && tep_not_null($manufacturer_info['manufacturers_image']) && file_exists(DIR_FS_CATALOG . 'images/' . $manufacturer_info['manufacturers_image'])) {
		echo tep_image(DIR_WS_IMAGES . $manufacturer_info['manufacturers_image'], $manufacturer_info['manufacturers_name'], '', '', 'align="right" class="one_image"');
	  }
	  echo $manufacturer_info['manufacturers_description'];
	}

	include(DIR_WS_MODULES . 'product_listing.php');
  } else {
	echo $page['pages_description'];

	echo '<p align="center">' . $letters_string . '</p>' . "\n";

	$manufacturers_query_row = "select m.manufacturers_id, mi.manufacturers_name, mi.manufacturers_description, m.manufacturers_image from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_status = '1' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'";
	if ($letter!='all') $manufacturers_query_row .= " and mi.manufacturers_letter = '" . tep_db_input($letter) . "'";
	$manufacturers_query_row .= " order by m.sort_order, mi.manufacturers_name";
	$listing_split = new splitPageResults($manufacturers_query_row, '50');

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
		$manufacturer_image = '';
		if (tep_not_null($row['manufacturers_image'])) {
		  $manufacturer_image = tep_image(DIR_WS_IMAGES . $row['manufacturers_image'], $row['manufacturers_name'], '', '', 'style="border: 1px solid #000000;"') . '<br />' . "\n";
		}
		echo '<p><a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'tPath=' . $show_product_type . '&manufacturers_id=' . $row['manufacturers_id']) . '">' . $manufacturer_image . $row['manufacturers_name'] . '</a></p>' . "\n";
	  }

	  if (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') {
		echo $listing_string;
	  }
	}
  }
?>