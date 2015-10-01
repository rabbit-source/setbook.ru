<?php
  if (tep_not_null($hname)) {
	$products_to_search = array();
	if (tep_not_null($holiday_products)) {
	  $products_to_search = array_map('trim', explode(',', $holiday_products));
	  $products_to_search = array_map('tep_string_to_int', $products_to_search);
	}
	$categories_to_search = array();
	if (tep_not_null($holiday_categories)) {
	  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in (" . $holiday_categories . ")");
	  while ($products = tep_db_fetch_array($products_query)) {
		if (!in_array($products['products_id'], $products_to_search)) $products_to_search[] = $products['products_id'];
	  }
	}
	include(DIR_WS_MODULES . 'product_listing.php');
  } else {
	echo $page['pages_description'];

	echo '<ul>' . "\n";
	reset($holiday_products_array);
	while (list($hpath, $holiday_array) = each($holiday_products_array)) {
	  echo '<li><a href="' . tep_href_link(FILENAME_HOLIDAY, 'hPath=' . $hpath) . '">' . $holiday_array['title'] . '</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
  }
?>