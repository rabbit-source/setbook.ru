<?php
  $products_types_query = tep_db_query("select products_types_id, products_types_name, products_types_default_status, products_last_modified from " . TABLE_PRODUCTS_TYPES . " where products_types_id in (" . implode(', ', $active_products_types_array) . ") and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, products_types_name");
  if (tep_db_num_rows($products_types_query) < 3) $default_type_is_opened = true;
  else $default_type_is_opened = false;
  $boxContent = '';
  $k = 0;
  while ($products_types = tep_db_fetch_array($products_types_query)) {
	$active = ($show_product_type==$products_types['products_types_id'] && $blocks['sort_order']==0);
	if (!$active && ($default_type_is_opened || SHOP_ID==14 || SHOP_ID==16) && $products_types['products_types_default_status']=='1' && $show_product_type < 1) $active = true;

	$boxContent .= '		<div class="li' . ($k==0 ? '_first' : '') . '"><div class="level_0"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id']) . '"' . ($active ? ' class="active"' : '') . ' onclick="if (document.getElementById(\'categories_' . $products_types['products_types_id'] . '\')) { if (document.getElementById(\'categories_' . $products_types['products_types_id'] . '\').innerHTML!=\'\') { document.getElementById(\'categories_' . $products_types['products_types_id'] . '\').style.display = (document.getElementById(\'categories_' . $products_types['products_types_id'] . '\').style.display==\'none\' ? \'\' : \'none\'); } else { jQuery(\'#categories_' . $products_types['products_types_id'] . '\').load(\'' . tep_href_link(FILENAME_LOADER, 'action=load_tree&type=' . $products_types['products_types_id'], $request_type) . '\'); document.getElementById(\'categories_' . $products_types['products_types_id'] . '\').style.display = \'\'; } if (document.getElementById(\'all_categories_' . $products_types['products_types_id'] . '\')) document.getElementById(\'all_categories_' . $products_types['products_types_id'] . '\').style.display = (document.getElementById(\'categories_' . $products_types['products_types_id'] . '\').style.display==\'none\' ? \'none\' : \'\'); return false; }">' . $products_types['products_types_name'] . '</a></div></div>' . "\n";

	$boxContent .= '<span id="categories_' . $products_types['products_types_id'] . '" style="display: ' . ($active ? '' : 'none') . ';">';
	if ($active) {
	  clearstatcache();
	  $categories_cache_dir = DIR_FS_CATALOG . 'cache/catalog/';
	  if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
	  $categories_cache_dir .= $products_types['products_types_id'] . '/';
	  if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
	  $categories_cache_filename = $categories_cache_dir . 'tree_' . $current_category_id . '.html';
	  $include_categories_cache_filename = false;
	  if (file_exists($categories_cache_filename)) {
		if (date('Y-m-d H:i:s', filemtime($categories_cache_filename)) > $products_types['products_last_modified']) {
		  $include_categories_cache_filename = true;
		}
	  }

	  if ($include_categories_cache_filename==false) {
		$categories_tree_string = tep_show_category(0, 1, '', $products_types['products_types_id'], true);
		$categories_tree_string = str_replace('?' . tep_session_name() . '=' . tep_session_id(), '', $categories_tree_string);
		if ($fp = @fopen($categories_cache_filename, 'w')) {
		  fwrite($fp, $categories_tree_string);
		  fclose($fp);
		}
	  } else {
		$categories_tree_string = '';
		$fp = fopen($categories_cache_filename, 'r');
		while (!feof($fp)) {
		  $categories_tree_string .= fgets($fp, 400);
		}
		fclose($fp);
	  }

	  if ($products_types['products_types_default_status']=='0' && tep_not_null($categories_tree_string)) {
		$boxContent .= '<div id="all_categories_' . $products_types['products_types_id'] . '" style="display: ' . ($active ? '' : 'none') . ';">' . "\n";
		$boxContent .= '		<div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=all') . '"' . (($HTTP_GET_VARS['view']=='all' && $products_types['products_types_id']==$show_product_type && $current_category_id==0) ? ' class="active"' : '') . '>' . TEXT_ALL_CATEGORY_PRODUCTS . '</a></div></div>' . "\n";
		$boxContent .= '</div>' . "\n";
	  } elseif ($products_types['products_types_default_status']=='1') {
		$boxContent .= '<div id="all_categories_' . $products_types['products_types_id'] . '" style="display: ' . ($active ? '' : 'none') . ';">' . "\n";
		$specials_types_query = tep_db_query("select specials_types_id, specials_types_path, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name limit 4");
		while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		  $boxContent .= '		<div class="li_special"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=' . $specials_types['specials_types_path']) . '"' . (($HTTP_GET_VARS['view']==$specials_types['specials_types_path'] && $products_types['products_types_id']==$show_product_type && $current_category_id==0) ? ' class="active"' : '') . '>' . $specials_types['specials_types_name'] . '</a></div></div>' . "\n";
		}
		$boxContent .= '		<div class="li_special"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=with_fragments') . '"' . (($HTTP_GET_VARS['view']=='with_fragments' && $products_types['products_types_id']==$show_product_type && $current_category_id==0) ? ' class="active"' : '') . '>' . LEFT_COLUMN_TITLE_FRAGMENTS . '</a></div></div>' . "\n";
		$boxContent .= '</div>' . "\n";
	  }

	  $boxContent .= $categories_tree_string;
	}
	$boxContent .= '</span>' . "\n";
	$k ++;
  }

  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
  $box_info = tep_db_fetch_array($box_info_query);
  $boxHeading = $box_info['blocks_name'];
  $boxID = 'catalog';
  if (tep_not_null($boxContent)) include(DIR_WS_TEMPLATES_BOXES . 'box.php');
?>