<?php
  if ($products_id > 0) {
	$products_in_foreign_cart = array();
	$foreign_cart_products = $foreign_cart->get_product_id_list();
	if (tep_not_null($foreign_cart_products)) $products_in_foreign_cart = explode(', ', $foreign_cart_products);

	$form_link = REQUEST_URI;
	if (strpos($form_link, 'action')) $form_link = preg_replace('/action=[^\&]*/i', 'action=[form_action]', $form_link);
	elseif (strpos($form_link, '?')) $form_link = $form_link . '&action=[form_action]';
	else $form_link = $form_link . '?action=[form_action]';
	while (strpos($form_link, '?&')) $form_link = str_replace('?&', '?', $form_link);
	while (strpos($form_link, '&&')) $form_link = str_replace('&&', '&', $form_link);

	$product_info_query = tep_db_query("select * from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$product_info = tep_db_fetch_array($product_info_query);
	reset($product_info);
	while (list($k, $v) = each($product_info)) {
	  while (strpos($v, "\'")!==false) $v = str_replace("\'", "'", $v);
	  while (strpos($v, '\"')!==false) $v = str_replace('\"', '"', $v);
	  $product_info[$k] = $v;
	}

	$lc_text = '';

	if (tep_not_null($product_info['products_image'])) {
	  $lc_text .= '<div class="row_product_image">' . tep_image(DIR_WS_IMAGES . 'foreign/big/' . $product_info['products_image'], $product_info['products_name']) . '</div>' . "\n";
	} else {
	  $lc_text .= '<div class="row_product_image">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'nofoto_big.gif', $product_info['products_name']) . '</div>' . "\n";
	}

	$lc_text .= '<div class="row_product_name">' . $product_info['products_name'] . '</div>' . "\n";

	$temp_string = '';
	if (tep_not_null($product_info['products_author'])) $temp_string .= (strpos($product_info['products_author'], ',') ? TEXT_AUTHORS : TEXT_AUTHOR) . ' ' . $product_info['products_author'];
	if (tep_not_null($product_info['products_manufacturer'])) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_MANUFACTURER . ' ' . $product_info['products_manufacturer'] . (($product_info['products_date_available']<=date('Y-m-d') && tep_not_null($product_info['products_year'])) ? ', ' . $product_info['products_year'] . TEXT_YEAR : '');
	}
	$lc_text .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";

	if ($product_info['products_date_available']>date('Y-m-d')) {
	  $lc_text .= '<div class="row_product_author">' . sprintf(TEXT_PRODUCT_NOT_AVAILABLE, tep_date_long($product_info['products_date_available']) . TEXT_YEAR) . '</div>' . "\n";
	}

	$temp_string = '';
	if (tep_not_null($product_info['products_available_in'])) {
	  if (ALLOW_SHOW_AVAILABLE_IN=='true') $temp_string .= sprintf(TEXT_AVAILABLE_IN_FOREIGN, $product_info['products_available_in']) . ' ';
	}
	$lc_text .= '<div class="row_product_author"><strong>' . $temp_string . '</strong></div>' . "\n";

	if (tep_not_null($product_info['products_model'])) {
	  $lc_text .= '<div class="row_product_model">' . TEXT_MODEL . ' ' . $product_info['products_model'] . '</div>' . "\n";
	}

	if (tep_not_null($product_info['products_genre'])) {
	  $lc_text .= '<div class="row_product_author">' . TEXT_GENRE . ' ' . $product_info['products_genre'] . '</div>' . "\n";
	}

	if (tep_not_null($product_info['products_language'])) {
	  $lc_text .= '<div class="row_product_author">' . TEXT_LANGUAGE . ' ' . $product_info['products_language'] . '</div>' . "\n";
	}

	if (tep_not_null($product_info['products_url'])) {
	  $products_url = str_replace('http://', '', $product_info['products_url']);
	  $slash_pos = (int)strpos($products_url, '/');
	  if ($slash_pos==0) $slash_pos = 20;
	  if (strlen($products_url) > ($slash_pos+20)) {
		$products_url_short = substr($products_url, 0, $slash_pos+1) . '...' . substr($products_url, -20);
	  } else {
		$products_url_short = $products_url;
	  }
	  $lc_text .= '<div class="row_product_author">' . TEXT_URL . ' <a href="' . tep_href_link(FILENAME_REDIRECT, 'goto=' . urlencode($products_url)) . '" target="_blank">' . $products_url_short . '</a></div>' . "\n";
	}

	$lc_text .= '<div class="clear">' . "\n";

	$lc_text .= '<div class="row_product_price">' . $currencies->format($product_info['products_price'], false, $product_info['products_currency']) . ' <span>(' . $currencies->display_price($product_info['products_price']/$currencies->currencies[$product_info['products_currency']]['value'], 0) . ')</span></div>' . "\n";

	$form_link_1 = str_replace('[form_action]', 'add_product&to=foreign', $form_link);
	$form_link_2 = str_replace('[form_action]', 'buy_now&type=1&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id(), $form_link);

	$form_string = tep_draw_form('p_form_' . $product_info['products_id'] . '_foreign', $form_link_1, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '&to=foreign\', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text_foreign; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

	$lc_text .= '<div class="row_product_buy" id="p_l_' . $product_info['products_id'] . '"">';
	if (in_array($product_info['products_id'], $products_in_foreign_cart)) {
	  $lc_text .= tep_image_button('button_in_order2.gif', IMAGE_BUTTON_IN_ORDER2);
	} else {
	  $lc_text .= $form_string . tep_image_submit('button_in_order.gif', IMAGE_BUTTON_IN_ORDER) . '<br /></form>' . "\n";
	}
	$lc_text .= '</div>' . "\n";

	$lc_text .= '</div>' . "\n";

	if (tep_not_null($product_info['products_description'])) {
	  $lc_text .= '<div class="row_product_description">' . nl2br($product_info['products_description']) . '</div>';
	}

	$lc_text .= '<script language="javascript" type="text/javascript"><!--' . "\n" .
	' var new_text_foreign = \'' . tep_image_button('button_in_order2.gif', IMAGE_BUTTON_IN_ORDER2) . '\';' . "\n" .
	'//--></script>';

	echo '<div class="product_description">' .  $lc_text . '</div>';
  } else {
	echo $page['pages_description'];
	include(DIR_WS_MODULES . 'foreign_product_listing.php');
  }
?>