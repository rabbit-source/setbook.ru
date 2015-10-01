<?php
  if ($reviews_types_id > 0 || $HTTP_GET_VARS['products_id'] > 0) {
	echo $type_info['reviews_types_description'] . "\n" .
	'<p>&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, 'tPath=' . $type_info['reviews_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_REVIEWS_RSS, '', '', 'style="float: left;"') . TEXT_REVIEWS_RSS_TEXT . '</a></p>' . "\n";

	$listing_sql = "select * from " . TABLE_REVIEWS . " where reviews_status = '1'";
	if ($HTTP_GET_VARS['products_id'] > 0) $listing_sql .= " and products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'";
	if ($reviews_types_id > 0) $listing_sql .= " and reviews_types_id = '" . (int)$reviews_types_id . "'";
	else $listing_sql .= " and reviews_types_id >= '1'";
	$listing_sql .= " order by date_added desc";

	$listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_REVIEWS_RESULTS, 'reviews_id');

	if ($listing_split->number_of_rows > 0) {
	  $listing_query = tep_db_query($listing_split->sql_query);
	  while ($listing = tep_db_fetch_array($listing_query)) {
		$product_info_query = tep_db_query("select authors_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$listing['products_id'] . "'");
		$product_info = tep_db_fetch_array($product_info_query);
		if (!is_array($product_info)) $product_info = array();

		$author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$author_info = tep_db_fetch_array($author_info_query);
		if (!is_array($author_info)) $author_info = array();

		$product_info = array_merge($product_info, $author_info);

		$title = tep_get_products_info($listing['products_id'], DEFAULT_LANGUAGE_ID);
		if (tep_not_null($product_info['authors_name'])) $title = $product_info['authors_name'] . ': ' . $title;

		$show_full_description = false;
		$reviews_description = $listing['reviews_text'];
		$reviews_description = str_replace('<br />', "\n", $reviews_description);
		$reviews_description = str_replace('<p>', '', $reviews_description);
		$reviews_description = str_replace('</p>', "\n\n", $reviews_description);
		while (strpos($reviews_description, "\n\n")!==false) $reviews_description = trim(str_replace("\n\n", "\n", $reviews_description));
		$reviews_short_description = tep_cut_string($reviews_description, 300);
		if (strlen($reviews_description) > 300) {
		  $reviews_short_description .= '...';
		  $show_full_description = true;
		}

		$stars_string = str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(TEXT_REVIEW_VOTES_OF, $listing['reviews_vote'], 5)), $listing['reviews_vote']);

		echo '<a name="rd' . $listing['reviews_id'] . '"></a><br />' . "\n" .
		'<div class="reviews_block' . ($HTTP_GET_VARS['reviews_id']==$listing['reviews_id'] ? '_active' : '') . '" id="rfd' . $listing['reviews_id'] . '">' . "\n" .
		'<div style="float: right;">' . $stars_string . '</div>' . "\n" .
		'<div class="reviews_title"><strong><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $listing['products_id']) . '">' . $title . '</a></strong></div>' . "\n" .
		'<div class="mediumText">' . tep_date_long($listing['date_added']) . ', ' . $listing['customers_name'] . '</div>' . "\n";
		if ($HTTP_GET_VARS['reviews_id']==$listing['reviews_id'] || $show_full_description==false) {
		  echo '<div class="reviews_description">' . nl2br($reviews_description) . '</div>' . "\n";
		} else {
		  echo '<div class="reviews_description" id="rsd' . $listing['reviews_id'] . '">' . nl2br($reviews_short_description) . '</div>' . "\n" .
		  '<a href="' . tep_href_link(FILENAME_REVIEWS, 'reviews_id=' . $listing['reviews_id'] . (isset($HTTP_GET_VARS['page']) ? '&page=' . $HTTP_GET_VARS['page'] : '')) . '#rd' . $listing['reviews_id'] . '" onclick="getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_review&reviews_id=' . $listing['reviews_id']) . '\', \'rsd' . $listing['reviews_id'] . '\'); document.getElementById(\'rfd' . $listing['reviews_id'] . '\').style.backgroundColor = \'#eeeeee\'; this.style.display = \'none\'; return false;" class="mediumText">' . VIEW_FULL_REVIEW . '</a>';
		}
		echo '</div><br />' . "\n";
	  }
?>
	<div id="listing-split">
	  <div style="float: left;"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></div>
	  <div style="text-align: right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_REVIEWS_RESULTS, tep_get_all_get_params(array('reviews_id', 'page', 'info', 'x', 'y'))); ?></div>
	</div>
<?php
	} else {
	  echo '<p>' . TEXT_NO_REVIEWS . '</p>';
	}
  } else {
	echo $page['pages_description'];
	$reviews_types_query = tep_db_query("select reviews_types_id, reviews_types_name, reviews_types_description from " . TABLE_REVIEWS_TYPES . " where reviews_types_id in (" . implode(', ', $active_reviews_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order, reviews_types_name");
	while ($reviews_types = tep_db_fetch_array($reviews_types_query)) {
	  echo '<p><a href="' . tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types['reviews_types_id'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_REVIEWS_RSS, '', '', 'style="margin: 0 4px -4px 0;"') . '</a><a href="' . tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types['reviews_types_id']) . '"><strong>' . $reviews_types['reviews_types_name'] . '</strong></a>' . (tep_not_null($reviews_types['reviews_types_description']) ? '<br />' . "\n" . $reviews_types['reviews_types_description'] : '') . '</p>' . "\n\n";
	}
  }
?>