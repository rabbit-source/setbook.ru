<?php
  if ($category_depth=='top' && empty($HTTP_GET_VARS['page']) && empty($HTTP_GET_VARS['detailed']) && empty($HTTP_GET_VARS['sort'])) {
	$product_type_info_query = tep_db_query("select products_types_description from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$show_product_type . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$product_type_info = tep_db_fetch_array($product_type_info_query);
	echo $product_type_info['products_types_description'];
  }

  if ( ($category_depth == 'nested' || $category_depth == 'products') && isset($show_subcategories) && $show_subcategories==true) {
/*
	$subcategories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '" . (int)$show_product_type . "' and c.categories_status = '1' and c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
	if (tep_db_num_rows($subcategories_query) > 0) {
	  $category_name_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  $category_name = tep_db_fetch_array($category_name_query);
	  $subcategories_string = '';
	  while ($subcategories = tep_db_fetch_array($subcategories_query)) {
		$subcategories_string .= (tep_not_null($subcategories_string) ? ', ' : '') . '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $subcategories['categories_id']) . '">' . $subcategories['categories_name'] . '</a>';
	  }
	  echo '<p>' . sprintf(TEXT_SUBCATEGORIES, $category_name['categories_name'], $subcategories_string) . '</p>' . "\n";
	}
*/
	$category_description_query = tep_db_query("select categories_description from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$category_description = tep_db_fetch_array($category_description_query);
	echo $category_description['categories_description'];
  } elseif ($category_depth == 'top' && isset($show_subcategories) && $show_subcategories==true) {
	if ($HTTP_GET_VARS['view']=='all' && $currency_category_id==0 && $show_product_type > 1) {
	} else {
	  $subcategories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.products_types_id = '" . (int)$show_product_type . "' and c.parent_id = '0' and c.categories_status = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
	  $subcategories_string = '';
	  while ($subcategories = tep_db_fetch_array($subcategories_query)) {
		$category_image = '';
		if (tep_not_null($subcategories['categories_image'])) {
		  $category_image = tep_image(DIR_WS_IMAGES . $subcategories['categories_image'], $subcategories['categories_name'], CATEGORY_IMAGE_WIDTH, CATEGORY_IMAGE_HEIGHT, 'style="padding: 3px; border: 1px solid #CCCCCC;"') . '<br />' . "\n";
		}
		$subcategories_string .= '<p><strong><a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $subcategories['categories_id']) . '">' . $subcategories['categories_name'] . '</a></strong>' . "\n";
		$subcategories_query_1 = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . $subcategories['categories_id'] . "' and c.categories_status = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
		if (tep_db_num_rows($subcategories_query_1) > 0) {
		  $i = 0;
		  while ($subcategories_1 = tep_db_fetch_array($subcategories_query_1)) {
			$subcategories_string .= ($i>0 ? ', ' : '<br />' . "\n") .
			'<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $subcategories_1['categories_id']) . '">' . $subcategories_1['categories_name'] . '</a>';
			$i ++;
		  }
		}
		$subcategories_string .= '</p>' . "\n";
	  }
	  echo $subcategories_string;
	}
  }

  if ($current_category_id > 0 && $HTTP_GET_VARS['view']!='all') {
	$linked_categories = array();
	$linked_products_types = array();
	$linked_query = tep_db_query("select c.products_types_id, cl.linked_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_LINKED . " cl where c.categories_id = cl.linked_id and cl.categories_id = '" . (int)$current_category_id . "'");
	while ($linked = tep_db_fetch_array($linked_query)) {
	  $linked_products_types[$linked['products_types_id']][] = $linked['linked_id'];
	}
	if (sizeof($linked_products_types) > 0) {
	  $linked_string = '';
	  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and products_types_id in ('" . implode("', '", array_keys($linked_products_types)) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, products_types_name");
	  while ($products_types = tep_db_fetch_array($products_types_query)) {
		$linked_categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id in ('" . implode("', '", $linked_products_types[$products_types['products_types_id']]) . "') and c.products_types_id = '" . (int)$products_types['products_types_id'] . "' and c.categories_status = '1' and c.categories_listing_status = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by c.sort_order, cd.categories_name");
		if (tep_db_num_rows($linked_categories_query) > 0) {
		  $linked_string .= '<div style="padding-left: 20px;" class="mediumText"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id']) . '"><strong>' . $products_types['products_types_name'] . '</strong></a>:</div>' . "\n";
		  $temp_linked_string = '';
		  while ($linked_categories = tep_db_fetch_array($linked_categories_query)) {
			$temp_linked_string .= (tep_not_null($temp_linked_string) ? ', ' : '') . '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $linked_categories['categories_id']) . '">' . $linked_categories['categories_name'] . '</a>';
		  }
		  $linked_string .= '<div style="padding-left: 40px;" class="mediumText">' . $temp_linked_string . '</div>' . "\n";
		}
	  }
	  if (tep_not_null($linked_string)) echo '<span class="mediumText">' . TEXT_LINKED_CATEGORIES . '</span>' . "\n" . $linked_string;
	}
  }

  if ($category_depth=='products' || isset($HTTP_GET_VARS['manufacturers_id']) || (isset($show_subcategories_products) && $show_subcategories_products==true) || ($current_category_id==0 && $HTTP_GET_VARS['view']=='all' && $show_product_type > 1) ) {
	echo $letters_string;

	include(DIR_WS_MODULES . 'product_listing.php');
  }
?>