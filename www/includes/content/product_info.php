<?php
  echo $page['pages_description'];

  $image_string = '';
  $description_string = '';
  if ($product_check < 1) {
	echo '<p align="left"><a href="' . tep_href_link(FILENAME_CATEGORIES) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'class="button_back"') . '</a></p>' . "\n";
  } else {
	$products_in_cart = array();
	$cart_products = $cart->get_product_id_list();
	if (tep_not_null($cart_products)) $products_in_cart = explode(', ', $cart_products);

	$products_in_postpone_cart = array();
	$postpone_cart_products = $postpone_cart->get_product_id_list();
	if (tep_not_null($postpone_cart_products)) $products_in_postpone_cart = explode(', ', $postpone_cart_products);

	$form_link = REQUEST_URI;
	if (strpos($form_link, 'action')) $form_link = preg_replace('/action=[^\&]*/i', 'action=[form_action]', $form_link);
	elseif (strpos($form_link, '?')) $form_link = $form_link . '&action=[form_action]';
	else $form_link = $form_link . '?action=[form_action]';
	while (strpos($form_link, '?&')) $form_link = str_replace('?&', '?', $form_link);
	while (strpos($form_link, '&&')) $form_link = str_replace('&&', '&', $form_link);

	$product_info_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
	$product_info = tep_db_fetch_array($product_info_query);

	$special_info_query = tep_db_query("select specials_new_products_price, if(status, specials_name, '') as specials_name from " . TABLE_SPECIALS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and status = '1' and language_id = '" . (int)$languages_id . "' and specials_new_products_price > '0' order by specials_date_added desc limit 1");
	if (tep_db_num_rows($special_info_query) > 0) {
	  $special_info = tep_db_fetch_array($special_info_query);
	  $product_info['specials_new_products_price'] = $special_info['specials_new_products_price'];
	  if ($product_info['products_price'] > $product_info['specials_new_products_price']) $product_info['final_price'] = $product_info['specials_new_products_price'];
	  else $product_info['final_price'] = $product_info['products_price'];
	} else {
	  $product_info['specials_new_products_price'] = 0;
	  $product_info['final_price'] = $product_info['products_price'];
	}

	$product_description_info_query = tep_db_query("select products_name, products_description, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_info['products_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$product_description_info = tep_db_fetch_array($product_description_info_query);
	if (!is_array($product_description_info)) $product_description_info = array();
	$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$product_info['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
	if (!is_array($manufacturer_info)) $manufacturer_info = array();
	$serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$product_info['series_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$serie_info = tep_db_fetch_array($serie_info_query);
	if (!is_array($serie_info)) $serie_info = array();
	$author_info_query = tep_db_query("select authors_name from authors where authors_id = '" . (int)$product_info['authors_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$author_info = tep_db_fetch_array($author_info_query);
	if (!is_array($author_info)) $author_info = array();
	$cover_info_query = tep_db_query("select products_covers_name from " . TABLE_PRODUCTS_COVERS . " where products_covers_id = '" . (int)$product_info['products_covers_id'] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$cover_info = tep_db_fetch_array($cover_info_query);
	if (!is_array($cover_info)) $cover_info = array();
	$format_info_query = tep_db_query("select products_formats_name from " . TABLE_PRODUCTS_FORMATS . " where products_formats_id = '" . (int)$product_info['products_formats_id'] . "'");
	$format_info = tep_db_fetch_array($format_info_query);
	if (!is_array($format_info)) $format_info = array();
	$product_info = array_merge($product_info, $product_description_info, $manufacturer_info, $serie_info, $author_info, $cover_info, $format_info);
	reset($product_info);
	while (list($k, $v) = each($product_info)) {
	  while (strpos($v, "\'")!==false) $v = str_replace("\'", "'", $v);
	  while (strpos($v, '\"')!==false) $v = str_replace('\"', '"', $v);
	  $product_info[$k] = $v;
	}
	if ($product_info['products_date_available']=='0000-00-00 00:00:00') $product_info['products_date_available'] = '';

	$customer_discount = $cart->get_customer_discount();
	$product_info['corporate_price'] = 0;
	if ($customer_discount['type']=='purchase' && $product_info['products_purchase_cost'] > 0) $product_info['corporate_price'] = $product_info['products_purchase_cost'] * (1 + $customer_discount['value']/100);

	$lc_text = '';

	$lc_text .= '<div class="row_product_image">';
	if (tep_not_null($product_info['products_image'])) {
//	  $lc_text .= tep_image(DIR_WS_IMAGES_BIG . $product_info['products_image'], $product_info['products_name']);
	  $lc_text .= tep_image('http://149.126.96.163/big/' . $product_info['products_image'], $product_info['products_name']);
	} else {
	  $lc_text .= tep_image(DIR_WS_TEMPLATES_IMAGES . 'nofoto_big.gif', $product_info['products_name']);
	}
	$lc_text .= '</div>' . "\n";

	$lc_text .= '<div class="row_product_name">' . $product_info['products_name'] . '</div>' . "\n";

	$temp_string = '';
	if (tep_not_null($product_info['authors_name'])) $temp_string .= (strpos($product_info['authors_name'], ',') ? TEXT_AUTHORS : TEXT_AUTHOR) . ' <a href="' . tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $product_info['authors_id']) . '">' . $product_info['authors_name'] . '</a>';
	if (tep_not_null($product_info['manufacturers_name'])) {
	  if (tep_not_null($product_description_info['manufacturers_name']) && $product_info['products_types_id'] > 1) {
		$temp_string .= (tep_not_null($temp_string) ? ', ' : '') . ($show_product_type==2 ? TEXT_MANUFACTURER : TEXT_MANUFACTURER_1) . ' ' . $product_description_info['manufacturers_name'] . ((int)$product_info['products_year']>0 ? ', ' . $product_info['products_year'] . TEXT_YEAR : '');
	  } else {
		$temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_MANUFACTURER . ' <a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $product_info['manufacturers_id']) . '">' . $product_info['manufacturers_name'] . '</a>' . ((int)$product_info['products_year']>0 ? ', ' . $product_info['products_year'] . TEXT_YEAR : '');
	  }
	}
	if ((int)$product_info['series_id'] > 0) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_SERIE . ' <a href="' . tep_href_link(FILENAME_SERIES, 'series_id=' . $product_info['series_id']) . '">' . $product_info['series_name'] . '</a>';
	}
	if (tep_not_null($product_info['products_warranty'])) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_WARRANTY . ' ' . $product_info['products_warranty'];
	}
	$lc_text .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";

	if ($product_info['products_periodicity']>0) {
	  $periodicity_count = $product_info['products_periodicity'];
	  $periodicity_text = sprintf(TEXT_PERIODICITY, $periodicity_count);
	  if (substr($periodicity_count, -1)==1 && $periodicity_count!=11) $periodicity_text = sprintf(TEXT_PERIODICITY_1, $periodicity_count);
	  elseif (substr($periodicity_count, -1) > 1 && substr($periodicity_count, -1) < 5 && substr($periodicity_count, -2, 1) != 1) $periodicity_text = sprintf(TEXT_PERIODICITY_2, $periodicity_count);
	  $lc_text .= '<div class="row_product_author">' . $periodicity_text . '</div>' . "\n";
	}

	if (tep_not_null($product_info['products_model'])) {
	  $lc_text .= '<div class="row_product_model">' . ($product_info['products_types_id']>2 ? TEXT_MODEL_1 : TEXT_MODEL) . ' ' . $product_info['products_model'] . '</div>' . "\n";
	}

	$temp_string = '';
	if (tep_not_null($product_info['products_covers_name'])) {
	  $temp_string .= TEXT_COVER . ' ' . $product_info['products_covers_name'];
	}
	if (tep_not_null($product_info['products_formats_name'])) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_FORMAT . ' ' . $product_info['products_formats_name'];
	}
	if (tep_not_null($temp_string)) $lc_text .= '<div class="row_product_cover">' . $temp_string . '</div>' . "\n";

	$temp_string = '';
	if ($product_info['products_weight'] > 0 && $product_info['products_types_id']=='1') {
	  $temp_string .= TEXT_WEIGHT . ' ' . ($product_info['products_weight']*1000) . TEXT_WEIGHT_GRAMMS;
	}
	if ($product_info['products_pages_count'] > 0) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_PAGES_COUNT . ' ' . $product_info['products_pages_count'];
	}
	if ($product_info['products_copies'] > 0) {
	  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . TEXT_COPIES . ' ' . $product_info['products_copies'];
	}
	if (tep_not_null($temp_string)) $lc_text .= '<div class="row_product_weight">' . $temp_string . '</div>' . "\n";

	if ((int)$product_info['products_available_in'] > 0 && $product_info['products_date_available']=='' && $product_info['products_listing_status']=='1' && $product_info['products_periodicity'] < 1) {
	  if (ALLOW_SHOW_AVAILABLE_IN=='true' && $product_info['products_status']=='1' && $product_info['products_listing_status']=='1') $lc_text .= '<div class="row_product_available">' . TEXT_AVAILABLE_IN . ' ' . tep_date_long(tep_calculate_date_available($product_info['products_available_in'])). '<!--'.$product_info['products_available_in'].'-->' . '</div>' . "\n";
	}

	$lc_text .= '<div class="clear">' . "\n";

	if ($product_info['specials_new_products_price'] > 0) {
//	  $lc_text .= '<div class="row_product_special_price">' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div><div class="row_product_special_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . "\n";
	} else {
//	  $lc_text .= '<div class="row_product_price">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . "\n";
	}
	$lc_text .= '<div class="row_product_price">';
	if ($product_info['products_status']=='1') {
	  list($available_year, $available_month, $available_day) = explode('-', preg_replace('/^([^\s]+)\s/', '$1', $product_info['products_date_available']));
	  if ($product_info['products_listing_status']=='0') {
		$available_soon_check_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_info['products_id'] . "' and specials_types_id = '4'");
		$available_soon_check = tep_db_fetch_array($available_soon_check_query);
		if ($product_info['products_date_available'] > date('Y-m-d')) {
		  $lc_text .= sprintf(TEXT_PRODUCT_NOT_AVAILABLE, $monthes_array[(int)$available_month] . ' ' . $available_year);
		} elseif ($available_soon_check['total'] > 0) {
		  $lc_text .= TEXT_PRODUCT_NOT_AVAILABLE_2;
		} else {
		  $lc_text .= sprintf(TEXT_PRODUCT_NOT_AVAILABLE_1, $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])));
		}
	  } elseif ($product_info['products_periodicity'] < 1) {
		if ($product_info['specials_new_products_price'] > 0 && $product_info['specials_new_products_price'] < $product_info['products_price']) $lc_text .= '<div class="row_product_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div>' . $currencies->display_price($product_info['specials_new_products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '';
		elseif ($product_info['corporate_price'] > 0) $lc_text .= '<div class="row_product_price_old">' .  $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</div> ' . TEXT_CORPORATE_PRICE . ' ' . $currencies->display_price($product_info['corporate_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
		else $lc_text .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
	  }
	} else {
	  $lc_text .= TEXT_PRODUCT_NOT_AVAILABLE_SHORT;
	}
	$lc_text .= '</div>' . "\n";

	if ($product_info['products_status']=='1') {
	  $form_link_1 = str_replace('[form_action]', 'add_product', $form_link);
	  $form_link_1_postpone = str_replace('[form_action]', 'add_product&to=postpone', $form_link);
	  $form_link_2 = str_replace('[form_action]', 'buy_now&type=1&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id(), $form_link);
	  $form_link_2 = tep_href_link(FILENAME_LOADER, 'action=buy_now&product_id=' . $product_info['products_id'] . '&' . tep_session_name() . '=' . tep_session_id());

	  $form_string = tep_draw_form('p_form_' . $product_info['products_id'], $form_link_1, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '\'' . ($product_info['products_periodicity']>0 ? '+\'&quantity=\'+quantity.options[quantity.selectedIndex].value' : '') . ', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

	  $form_string_postpone = tep_draw_form('p_form_' . $product_info['products_id'] . '_postpone', $form_link_1_postpone, 'post', (($popup=='on' && (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id'))) ? 'onsubmit="if (getXMLDOM(\'' . $form_link_2 . '&to=postpone\', \'shopping_cart\')) { document.getElementById(\'p_l_' . $product_info['products_id'] . '\').innerHTML = new_text_postpone; return false; }"' : '') . ' class="productListing-form"') . tep_draw_hidden_field('products_id', $product_info['products_id']);

	  $lc_text .= '<div class="row_product_buy" id="p_l_' . $product_info['products_id'] . '" onmouseover="if (document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\')) document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\').style.display = \'inline\'" onmouseout="if (document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\')) document.getElementById(\'form_' . $product_info['products_id'] . '_postpone\').style.display = \'none\'">';
	  if (in_array($product_info['products_id'], $products_in_cart)) {
		$lc_text .= tep_image_button('button_in_cart2.gif', IMAGE_BUTTON_IN_CART2);
	  } elseif (in_array($product_info['products_id'], $products_in_postpone_cart)) {
		$lc_text .= tep_image_button('button_postpone2.gif', IMAGE_BUTTON_POSTPONE2);
	  } else {
		if ($product_info['products_listing_status']=='1') {
		  $lc_text .= $form_string;
		  if ($product_info['products_periodicity'] > 0) {
			$periodicity_array = array();
			if (substr($product_info['products_model'], -1) == 'e') {
			  $periodicity_array[] = array('id' => ceil($periodicity_count/12), 'text' => TEXT_SUBSCRIBE_TO_1_MONTH . ': ' . $currencies->display_price($product_info['products_price'] * ceil($periodicity_count/12), tep_get_tax_rate($product_info['products_tax_class_id'])));
			}
			if ($product_info['products_periodicity_min'] <= 3 && $periodicity_count > 6) {
			  $periodicity_array[] = array('id' => ceil($periodicity_count/4), 'text' => TEXT_SUBSCRIBE_TO_3_MONTHES . ': ' . $currencies->display_price($product_info['products_price'] * ceil($periodicity_count/4), tep_get_tax_rate($product_info['products_tax_class_id'])));
			}
			if ($product_info['products_periodicity_min'] <= 7) {
			  $periodicity_array[] = array('id' => $periodicity_count/2, 'text' => TEXT_SUBSCRIBE_TO_HALF_A_YEAR . ': ' . $currencies->display_price($product_info['products_price']*$periodicity_count/2, tep_get_tax_rate($product_info['products_tax_class_id'])));
			}
			$periodicity_array[] = array('id' => $periodicity_count, 'text' => TEXT_SUBSCRIBE_TO_YEAR . ': ' . $currencies->display_price($product_info['products_price']*$periodicity_count, tep_get_tax_rate($product_info['products_tax_class_id'])));
			$lc_text .= '<div class="subscribe_to">' . TEXT_SUBSCRIBE_TO . ' ' . tep_draw_pull_down_menu('quantity', $periodicity_array) . '&nbsp;</div>';
		  }
		  $lc_text .= tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '<br /></form>' . "\n";
		  $lc_text .= '<div id="form_' . $product_info['products_id'] . '_postpone" style="display: none; position: absolute; margin-top: 1px;">' . $form_string_postpone . ($product_info['products_types_id']==1 ? tep_image_submit('button_postpone.gif', IMAGE_BUTTON_POSTPONE) : '') . '</form></div>';
		} elseif ($product_info['products_types_id']=='1') {
		  $lc_text .= $form_string_postpone . tep_image_submit('button_postpone.gif', IMAGE_BUTTON_POSTPONE) . '</form>';
		} else {
		  $lc_text .= '';
		}
	  }
	  $lc_text .= '</div>' . "\n";
	}

	$lc_text .= '</div>' . "\n";

	if ($product_info['products_listing_status']=='1' && defined('TEXT_CODE') && tep_not_null(TEXT_CODE)) {
	  $lc_text .= '<div class="row_product_code">' . sprintf(TEXT_CODE, (int)str_replace('bbk', '', $product_info['products_code'])) . '</div>' . "\n";
	}

	if (tep_not_null($product_info['products_description'])) {
	  if (strip_tags($product_info['products_description'])==$product_info['products_description']) $product_info['products_description'] = nl2br($product_info['products_description']);
	  $lc_text .= '<div class="row_product_description">' . $product_info['products_description'] . '</div>' . "\n";
	}

	if ($languages_id==DEFAULT_LANGUAGE_ID) {
	  ob_start();
?>
<script type="text/javascript" src="<?php echo DIR_WS_CATALOG . DIR_WS_JAVASCRIPT; ?>orphus.js"></script>
<div class="row_product_description">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
	<td><span class="errorText">Нашли ошибку в названии или описании? Выделите её мышкой и нажмите Ctrl+Enter</span></td>
	<td align="right"><a href="<?php echo (!$session_started ? tep_href_link(FILENAME_REDIRECT, 'goto=orphus.ru') : 'http://orphus.ru') ?>" id="orphus" target="_blank"><?php echo tep_image(DIR_WS_CATALOG . DIR_WS_JAVASCRIPT . 'orphus.gif', 'Powered by Orphus', '72', '24', 'style="border: 1px solid black;"'); ?></a></td>
  </tr>
</table><div class="mediumText"></div></div>
<?php
	  $lc_text .= ob_get_clean();
	}

	if ($product_info['products_listing_status']=='1') {
	  ob_start();
?>
<!-- AddThis Button BEGIN -->
<link href="http://stg.odnoklassniki.ru/share/odkl_share.css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="http://stg.odnoklassniki.ru/share/odkl_share.js"></script>
<div class="row_product_description">
<table border="0" cellspacing="0" cellpadding="0">
  <tr align="center">
	<td width="30"><a class="addthis_button_facebook" title="Facebook"></a></td>
	<td width="30"><a class="addthis_button_twitter" title="Twitter"></a></td>
	<td width="30"><a class="addthis_button_vk" title="VKontakte"></a></td>
	<td width="30"><a class="addthis_button_googlebuzz" title="Google Buzz"></a></td>
	<td width="30"><a class="addthis_button_myspace" title="My Space"></a></td>
	<td width="30"><a class="addthis_button_livejournal" title="Livejournal"></a></td>
	<td width="30"><a class="odkl-klass-s" style="cursor: pointer;" href="<?php echo HTTP_SERVER . PHP_SELF; ?>" onclick="ODKL.Share(this); return false;" title="Odnoklassniki"></a></td>
	<td width="30"><a class="addthis_button_email" title="Email"></a></td>
	<td width="100"><a class="addthis_counter addthis_pill_style"></a></td>
  </tr>
</table>
</div>
<script language="javascript" type="text/javascript"><!--
  var addthis_config = {
	data_track_clickback: true,
	ui_click: true
  };
  var addthis_share = {
    url_transforms : {
        clean: true,
        remove: ['PHPSESSID']
    }
  }
//--></script>
<script language="javascript" type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=setbook"></script>
<!-- AddThis Button END -->
<?php
	  $lc_text .= ob_get_clean();
	}

	$lc_text .= '<script language="javascript" type="text/javascript"><!--' . "\n" .
	' var new_text = \'<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . tep_image_button('button_in_cart2.gif', IMAGE_BUTTON_IN_CART3) . '</a>\';' . "\n" .
	' var new_text_postpone = \'<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '#postpone">' . tep_image_button('button_postpone2.gif', IMAGE_BUTTON_POSTPONE3) . '</a>\';' . "\n" .
	'//--></script>';

	echo '<div class="product_description">' .  $lc_text . '</div>';
//	echo '<br />' . "\n";
  }
?>