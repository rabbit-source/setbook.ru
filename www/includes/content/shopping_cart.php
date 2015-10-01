<?php
  echo $page['pages_description'];

  if (basename(SCRIPT_FILENAME)==FILENAME_SHOPPING_CART) {
	$available_products_types_array = array();
	$available_products_types_query = tep_db_query("select products_types_path from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and language_id = '" . (int)$languages_id . "'");
	while ($available_products_types = tep_db_fetch_array($available_products_types_query)) {
	  $available_products_types_array[] = $available_products_types['products_types_path'];
	}

	$back = '';
	$navigation_path = $navigation->path;
	for ($i=sizeof($navigation_path)-1; $i>=0; $i--) {
	  $navigation_page = preg_replace('/^' . preg_quote(DIR_WS_CATALOG, '/') . '/', '', $navigation_path[$i]['page']);
	  list($navigation_section) = explode('/', $navigation_page);
	  $navigation_page_ext = substr($navigation_page, strrpos($navigation_page, '.')+1);
	  if ( (in_array($navigation_section, $available_products_types_array) || basename($navigation_path[$i]['page'])==FILENAME_ADVANCED_SEARCH_RESULT) && basename($navigation_path[$i]['page'])!=FILENAME_LOADER && $navigation_page_ext!='rss') {
		$back = ($navigation_path[$i]['mode']=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . $navigation_path[$i]['page'] . (tep_not_null(tep_array_to_string($navigation_path[$i]['get'])) ? '?' . tep_array_to_string($navigation_path[$i]['get']) : '');
		break;
	  }
	}
	if (empty($back)) $back = tep_href_link(FILENAME_CATEGORIES);
  }

  echo '<ul class="search_results">' . "\n";
  echo '<li id="show_list_1" class=" ' . (($HTTP_GET_VARS['type']=='postpone' || $HTTP_GET_VARS['type']=='foreign') ? 'show_list_inactive' : 'show_list_active') . '" style="width: 135px;"><a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '#" onclick="showResultPage(1); return false;">' . SHOPPING_CART_TITLE . ' (' . $cart->count_contents() . ')</a></li>' . "\n";
  echo '<li id="show_list_2" class="' . ($postpone_cart->count_contents()>0 ? ($HTTP_GET_VARS['type']=='postpone' ? 'show_list_active' : 'show_list_inactive') : 'show_list_desactive') . '" style="width: 135px;">' . ($postpone_cart->count_contents()>0 ? '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'type=postpone') . '" onclick="showResultPage(2); return false;">' . POSTPONE_CART_TITLE . ' (' . $postpone_cart->count_contents() . ')</a>' : POSTPONE_CART_TITLE) . '</li>' . "\n";
  if (in_array(DOMAIN_ZONE, array('ru'))) echo '<li id="show_list_3" class="' . ($foreign_cart->count_contents()>0 ? ($HTTP_GET_VARS['type']=='foreign' ? 'show_list_active' : 'show_list_inactive') : 'show_list_desactive') . '" style="width: 135px;">' . ($foreign_cart->count_contents()>0 ? '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'type=foreign') . '" onclick="showResultPage(3); return false;">' . FOREIGN_CART_TITLE . ' (' . $foreign_cart->count_contents() . ')</a>' : FOREIGN_CART_TITLE) . '</li>' . "\n";
  echo '</ul>' . "\n";

  echo '<div id="show_results_list_1" class="advanced-search" style="display: ' . (($cart->count_contents()>0 && $HTTP_GET_VARS['type']!='postpone' && $HTTP_GET_VARS['type']!='foreign') ? 'block' : '') . ';"><br />' . "\n";

  if ($cart->count_contents() > 0) {
	echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product'));
?>
<script language="javascript" type="text/javascript"><!--
  var alertQtyChanged = true;
  function updateCartAlert() {
	if (alertQtyChanged) alert("<?php echo WARNING_UPDATE_CART; ?>");
	alertQtyChanged = false;
	document.getElementById("update_cart").style.display = "block";
  }
//--></script>
<?php
    $info_box_contents = array();

    $info_box_contents[0][] = array('params' => 'width="56%" class="productListing-heading-first"',
                                    'text' => TABLE_HEADING_PRODUCTS);

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'width="9%" class="productListing-heading"',
                                    'text' => TABLE_HEADING_WEIGHT);

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'width="12%" class="productListing-heading"',
                                    'text' => TABLE_HEADING_PRICE);

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'width="9%" class="productListing-heading"',
                                    'text' => TABLE_HEADING_QUANTITY);

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'width="14%" class="productListing-heading-last"',
                                    'text' => TABLE_HEADING_TOTAL);

	$cur_row = sizeof($info_box_contents);

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                           'params' => 'colspan="5" class="row_btwh"',
                                           'text' => tep_draw_separator('pixel_trans.gif', '1', '1'));

    $any_out_of_stock = 0;
    $products = $cart->get_products();

	$max_available_in = 0;
	$only_periodicals = true;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="productListing-even"');
      } else {
        $info_box_contents[] = array('params' => 'class="productListing-odd"');
      }

	  $cur_row = sizeof($info_box_contents) - 1;

	  $temp_string = '';

	  if ($products[$i]['periodicity'] > 0) {
		$periodicity_count = $products[$i]['periodicity'];
		$periodicity_array = array();
		if (substr($products[$i]['model'], -1)=='e') {
		  $periodicity_array[] = array('id' => ceil($periodicity_count/12), 'text' => TEXT_SUBSCRIBE_TO_1_MONTH);
		}
		if ($products[$i]['periodicity_min'] <= 3 && $periodicity_count > 6) {
		  $periodicity_array[] = array('id' => ceil($periodicity_count/4), 'text' => TEXT_SUBSCRIBE_TO_3_MONTHES);
		}
		if ($products[$i]['periodicity_min'] <= 7) {
		  $periodicity_array[] = array('id' => $periodicity_count/2, 'text' => TEXT_SUBSCRIBE_TO_HALF_A_YEAR );
		}
		$periodicity_array[] = array('id' => $periodicity_count, 'text' => TEXT_SUBSCRIBE_TO_YEAR);
		$qty_field =  TEXT_SUBSCRIBE_TO . ' ' . tep_draw_pull_down_menu('cart_quantity[]', $periodicity_array, $products[$i]['quantity'], 'onchange="updateCartAlert();"');
		if (tep_not_null($products[$i]['manufacturer'])) $temp_string .= $products[$i]['manufacturer'];
		if ($periodicity_count > 0) {
		  $periodicity_text = sprintf(TEXT_PERIODICITY, $periodicity_count);
		  if (substr($periodicity_count, -1)==1 && $periodicity_count!=11) $periodicity_text = sprintf(TEXT_PERIODICITY_1, $periodicity_count);
		  elseif (substr($periodicity_count, -1) > 1 && substr($periodicity_count, -1) < 5 && substr($periodicity_count, -2, 1) != 1) $periodicity_text = sprintf(TEXT_PERIODICITY_2, $periodicity_count);
		  $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $periodicity_text;
		}
	  } else {
		$only_periodicals = false;
		$qty_field = tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'class="productQuantity" maxlength="3" onkeyup="updateCartAlert();"');
		if (tep_not_null($products[$i]['manufacturer'])) {
		  $temp_string .= $products[$i]['manufacturer'];
		  if ((int)$products[$i]['year'] > 0) $temp_string .= ', ' . $products[$i]['year'] . TEXT_YEAR;
		}
	  }

      $products_name = '<div class="row_product_name"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . $products[$i]['name'] . '</a></div>' . "\n";

	  if (tep_not_null($temp_string)) $products_name .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";

      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

	  if (tep_not_null($products[$i]['image'])) {
		$products_image = '<div class="row_product_image_cart">' . tep_image(DIR_WS_IMAGES . 'thumbs/' . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</div>';
	  } else {
		$products_image = '<div class="row_product_image_cart">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'nofoto.gif', $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</div>';
	  }

	  $products_name = '<table border="0" cellspacing="0" cellpadding="0" width="100%">' . "\n" .
	  '  <tr>' . "\n" .
	  '	<td rowspan="2" width="90">' . $products_image . '</td>' . "\n" .
	  '	<td colspan="2">' . $products_name . '</td>' . "\n" .
	  '  </tr>' . "\n" .
	  '  <tr>' . "\n" .
	  '	<td valign="bottom">' . ($products[$i]['type']>1 ? '&nbsp;' : '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=move_product&to=postpone&products_id=' . $products[$i]['id']) . '">' . tep_image_button('button_postpone.gif', IMAGE_BUTTON_POSTPONE) . '</a>') . '</td>' . "\n" .
	  '	<td valign="bottom" align="right"><a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&products_id=' . $products[$i]['id']) . '">' . tep_image_button('button_delete.gif', IMAGE_BUTTON_DELETE) . '</a></td>' . "\n" .
	  '  </tr>' . "\n" .
	  '</table>' . "\n";

      $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data-first"',
                                             'text' => $products_name);

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' => ($products[$i]['quantity']>1 ? $products[$i]['quantity'] . 'x' : '') . (tep_not_null($products[$i]['filename']) ? '-' : ($products[$i]['weight']*1000) . TEXT_WEIGHT_GRAMMS));

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data" nowrap="nowrap"',
                                             'text' => $currencies->display_price($products[$i]['price'], tep_get_tax_rate($products[$i]['tax_class_id'])));

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' => $qty_field . tep_draw_hidden_field('products_id[]', $products[$i]['id']));

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data-last" align="right" nowrap="nowrap"',
                                             'text' => '<div class="row_product_price">' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</div>');

	  $cur_row = sizeof($info_box_contents);

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'colspan="5" class="row_btwh"',
                                             'text' => tep_draw_separator('pixel_trans.gif', '1', '1'));
    }

	if (MODULE_ORDER_TOTAL_INSTALLED) {
	  require(DIR_WS_CLASSES . 'order.php');
	  $order = new order;

	  require(DIR_WS_CLASSES . 'order_total.php');
	  $order_total_modules = new order_total;

	  $order_total_array = $order_total_modules->process();

	  reset($order_total_array);
	  while (list(, $order_total_row) = each($order_total_array)) {
//		if ( ($order_total_row['code']=='ot_total' && !in_array('ot_discount', $order_total_modules->classes) && !in_array('ot_tax', $order_total_modules->classes)) || $order_total_row['code']=='ot_shipping' ) continue;

		$cur_row ++;

		if ($order_total_row['code']=='ot_subtotal') {
		  $total_weight = $cart->show_weight() * 1000;
		  if ($total_weight > 2000) $total_weight_text = round($total_weight / 1000, 2) . TEXT_WEIGHT_KILOGRAMMS;
		  else $total_weight_text = $total_weight . TEXT_WEIGHT_GRAMMS;

		  $info_box_contents[$cur_row][] = array('align' => 'left',
												 'params' => 'class="productListing-heading-first"',
												 'text' => $order_total_row['title']);

		  $info_box_contents[$cur_row][] = array('align' => 'center',
												 'params' => 'class="productListing-heading"',
												 'text' => $total_weight_text);

		  $info_box_contents[$cur_row][] = array('align' => 'center',
												 'params' => 'class="productListing-heading"',
												 'text' => '&nbsp;');

		  $info_box_contents[$cur_row][] = array('align' => 'center',
												 'params' => 'class="productListing-heading"',
												 'text' => $cart->count_contents());

		  $info_box_contents[$cur_row][] = array('align' => 'center',
												 'params' => 'class="productListing-heading-last" align="right" nowrap="nowrap"',
												 'text' => '<div class="row_product_price" style="text-align: right; float: none;">' . $order_total_row['text'] . '</div>');
		} else {
		  $info_box_contents[$cur_row][] = array('align' => 'left',
												 'params' => 'colspan="4" class="productListing-heading-first"',
												 'text' => $order_total_row['title']);

		  $info_box_contents[$cur_row][] = array('align' => 'center',
												 'params' => 'class="productListing-heading-last" align="right" nowrap="nowrap"',
												 'text' => '<div class="row_product_price" style="font-weight: normal; text-align: right; float: none;">' . $order_total_row['text'] . '</div>');
		}

		$cur_row ++;

	    $info_box_contents[$cur_row][] = array('align' => 'center',
    	                                       'params' => 'colspan="5" class="row_btwh"',
        	                                   'text' => tep_draw_separator('pixel_trans.gif', '1', '1'));
	  }
	}

	$box = new tableBox(array());
	$box->table_width = '100%';
	$box->table_border = '0';
	$box->table_parameters = 'class="productListing"';
	$box->table_cellspacing = '0';
	$box->table_cellpadding = '0';
	echo $box->tableBox($info_box_contents) . '<br />';

    if ($any_out_of_stock == 1) {
	  echo '	<div class="buttons">' . "\n";
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
	  <div><?php echo sprintf(OUT_OF_STOCK_CAN_CHECKOUT, STOCK_MARK_PRODUCT_OUT_OF_STOCK); ?></div>
<?php
      } else {
?>
	  <div><span class="stockWarning"><?php echo sprintf(OUT_OF_STOCK_CANT_CHECKOUT, STOCK_MARK_PRODUCT_OUT_OF_STOCK); ?></span></div>
<?php
      }
	  echo '	</div><br />' . "\n";
    }
	if (ALLOW_SHOW_AVAILABLE_IN=='true') {
	  if (!$only_periodicals && $cart->content_type!='virtual') echo '<div class="buttons">' . "\n" . '<div style="text-align: right;"><strong class="mediumText">' . sprintf(MAX_AVAILABLE_IN, tep_date_long(tep_calculate_date_available($cart->info['delivery_transfer']))) . '</strong></div>' . "\n" . '</div>';
	}
?>
	<div class="buttons">
	  <div style="float: left; margin-right: 15px;"><a href="<?php echo $back; ?>"><?php echo tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING); ?></a></div>
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=reset_cart') . '" onclick="return confirm(\'' . TEXT_RESET_CART_WARNING . '\');">' . tep_image_button('button_reset.gif', IMAGE_BUTTON_RESET_CART) . '</a>'; ?></div>
	  <div id="update_cart" style="float: left; margin-left: 15px; display: none;"><?php echo tep_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART); ?></div>
	  <div style="text-align: right;"><a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT); ?></a></div>
	</div>
	</form>
<?php
  } else {
	echo '<p>' . TEXT_CART_EMPTY . '</p>';
  }
  echo '</div>' . "\n";

  if ($postpone_cart->count_contents() > 0) {
	echo '<div id="show_results_list_2" class="advanced-search"' . ($HTTP_GET_VARS['type']=='postpone' ? ' style="display: block;"' : '') . '>' . "\n";
	$products_to_search = explode(', ', $postpone_cart->get_product_id_list());
	if (!is_array($products_to_search)) $products_to_search = array();
	if (sizeof($products_to_search) > 0) {
?>
<div class="clear"></div><br />
<?php
	  $show_listing_string = false;
	  $show_filterlist_string = false;
	  include(DIR_WS_MODULES . 'product_listing.php');
?><br />
	<div class="buttons">
	  <div style="float: left; margin-right: 15px;"><a href="<?php echo $back; ?>"><?php echo tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING); ?></a></div>
	  <div style="text-align: left;"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=reset_cart&cart_type=postpone') . '" onclick="return confirm(\'' . TEXT_RESET_CART_WARNING . '\');">' . tep_image_button('button_reset.gif', IMAGE_BUTTON_RESET_CART) . '</a>'; ?></div>
	</div>
<?php
	}
	echo '</div>' . "\n";
  }

  if ($foreign_cart->count_contents() > 0 && in_array(DOMAIN_ZONE, array('ru'))) {
	echo '<div id="show_results_list_3" class="advanced-search"' . ($HTTP_GET_VARS['type']=='foreign' ? ' style="display: block;"' : '') . '>' . "\n";
	$products_to_search = explode(', ', $foreign_cart->get_product_id_list());
	if (!is_array($products_to_search)) $products_to_search = array();
	if (sizeof($products_to_search) > 0) {
?>
<div class="clear"></div><br />
<?php
	  include(DIR_WS_MODULES . 'foreign_product_listing.php');
?><br />
	<div class="buttons">
	  <div style="float: left; margin-right: 15px;"><a href="<?php echo tep_href_link(FILENAME_FOREIGN); ?>"><?php echo tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING); ?></a></div>
	  <div style="float: left;"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=reset_cart&cart_type=foreign') . '" onclick="return confirm(\'' . TEXT_RESET_CART_WARNING . '\');">' . tep_image_button('button_reset.gif', IMAGE_BUTTON_RESET_CART) . '</a>'; ?></div>
	  <div style="text-align: right;"><a href="<?php echo tep_href_link('/foreign.html'); ?>#request"><?php echo tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT); ?></a></div>
	</div>
<?php
	}
	echo '</div>' . "\n";
  }
?>
<script language="javascript" type="text/javascript"><!--
  if (document.location.href.indexOf("#postpone")>=0<?php if ($HTTP_GET_VARS['type']=='postpone') echo ' || 1'; ?>) showResultPage(2);
  else if (document.location.href.indexOf("#foreign")>=0<?php if ($HTTP_GET_VARS['type']=='foreign') echo ' || 1'; ?>) showResultPage(3);
  else showResultPage(1);
//--></script>