<?php
  echo $page['pages_description'];

  echo '	<table border="0" cellspacing="0" cellpadding="0">' . "\n" .
  '	  <tr>' . "\n" .
  '		<td class="sitemap_left_column">' . "\n" .
  tep_show_categories_map() .
  '</td>' . "\n" .
  '		<td class="sitemap_right_column">' . "\n";
  reset($active_products_types_array);
  while (list(, $product_type_id) = each($active_products_types_array)) {
	if ($product_type_id > 1) echo tep_show_categories_map(0, 0, $product_type_id);
  }
  echo '' .
  tep_show_sections_map() .
  tep_show_manufacturers_map() .
  tep_show_authors_map() .
  tep_show_series_map() .
  tep_show_specials_map() .
  tep_show_account_map() .
  '</td>' . "\n" .
  '	  </tr>' . "\n" .
  '	</table>' . "\n";
?>