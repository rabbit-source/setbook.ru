<?php
  echo $page['pages_description'];

  if ($cart->count_contents() > 0) {
	echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_POPUP_SHOPPING_CART, 'action=update_product&short=1'));

    $info_box_contents = array();

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading-first"',
                                    'text' => (tep_not_null(TABLE_HEADING_REMOVE) ? TABLE_HEADING_REMOVE : '&nbsp;'));

    $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                    'text' => (tep_not_null(TABLE_HEADING_PRODUCTS) ? TABLE_HEADING_PRODUCTS : '&nbsp;'));

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => (tep_not_null(TABLE_HEADING_WEIGHT) ? TABLE_HEADING_WEIGHT : '&nbsp;'));

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => (tep_not_null(TABLE_HEADING_PRICE) ? TABLE_HEADING_PRICE : '&nbsp;'));

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => (tep_not_null(TABLE_HEADING_QUANTITY) ? TABLE_HEADING_QUANTITY : '&nbsp;'));

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading-last"',
                                    'text' => (tep_not_null(TABLE_HEADING_TOTAL) ? TABLE_HEADING_TOTAL : '&nbsp;'));

    $any_out_of_stock = 0;
    $products = $cart->get_products();

    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="productListing-even"');
      } else {
        $info_box_contents[] = array('params' => 'class="productListing-odd"');
      }

      $cur_row = sizeof($info_box_contents) - 1;

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data-first"',
                                             'text' => tep_draw_checkbox_field('cart_delete[]', $products[$i]['id']));

      $products_name = '<div class="row_product_name"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '" target="_blank" onclick="if (window.opener) { window.opener.focus(); window.opener.location.href = \'' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '\'; } else { window.open(\'' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '\'); } window.close(); return false;">' . $products[$i]['name'] . '</a></div>' . "\n";

	  $additional_info_query = tep_db_query("select products_image, manufacturers_id, products_year from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products[$i]['id'] . "' limit 1");
	  $additional_info = tep_db_fetch_array($additional_info_query);
	  $temp_string = '';
	  if ((int)$additional_info['manufacturers_id'] > 0) {
		$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . $additional_info['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
		$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
		$temp_string .= $manufacturer_info['manufacturers_name'];
	  }
	  if ((int)$additional_info['products_year'] > 0) $temp_string .= (tep_not_null($temp_string) ? ', ' : '') . $additional_info['products_year'] . TEXT_YEAR;
	  if (tep_not_null($temp_string)) $products_name .= '<div class="row_product_author">' . $temp_string . '</div>' . "\n";

      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

      $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                             'text' => $products_name);

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' => ($products[$i]['quantity']>1 ? $products[$i]['quantity'] . 'x' : '') . ($products[$i]['weight']*1000) . TEXT_WEIGHT_GRAMMS);

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' => '<strong>' . $currencies->display_price($products[$i]['price'], tep_get_tax_rate($products[$i]['tax_class_id'])) . '</strong>');

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' => tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'class="productQuantity" maxlength="2"') . tep_draw_hidden_field('products_id[]', $products[$i]['id']));

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data-last"',
                                             'text' => '<strong>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</strong>');
    }

	$cur_row = sizeof($info_box_contents);

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading-first"',
                                    'text' => '&nbsp;');

    $info_box_contents[$cur_row][] = array('align' => 'left',
                                    'params' => 'class="productListing-heading"',
                                    'text' => (tep_not_null(TABLE_HEADING_SUBTOTAL) ? TABLE_HEADING_SUBTOTAL : '&nbsp;'));

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => ($cart->weight*1000) . TEXT_WEIGHT_GRAMMS);

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => '&nbsp;');

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => '&nbsp;');

    $info_box_contents[$cur_row][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading-last"',
                                    'text' => $currencies->format($cart->show_total()));

	$box = new tableBox(array());
	$box->table_width = '100%';
	$box->table_border = '0';
	$box->table_parameters = 'class="productListing"';
	$box->table_cellspacing = '0';
	echo $box->tableBox($info_box_contents);
?>
	<div class="clear">
<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
	  <div style="float: left;"><?php echo sprintf(OUT_OF_STOCK_CAN_CHECKOUT, STOCK_MARK_PRODUCT_OUT_OF_STOCK); ?></div>
<?php
      } else {
?>
	  <div style="float: left;"><span class="stockWarning"><?php echo sprintf(OUT_OF_STOCK_CANT_CHECKOUT, STOCK_MARK_PRODUCT_OUT_OF_STOCK); ?></span></div>
<?php
      }
    }
?>
	</div><br />
	<div class="buttons">
	  <div style="float: left;"><?php echo tep_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART); ?></div>
	  <div style="float: left; margin-left: 15px;"><?php echo '<a href="' . tep_href_link(FILENAME_POPUP_SHOPPING_CART, 'short=1&action=reset_cart', 'SSL') . '" onclick="if (confirm(\'' . TEXT_RESET_CART_WARNING . '\')) document.location.href=\'' . tep_href_link(FILENAME_POPUP_SHOPPING_CART, 'short=1&action=reset_cart', 'SSL') . '\';">' . tep_image_button('button_reset.gif', IMAGE_BUTTON_RESET_CART) . '</a>'; ?></div>
	  <div style="text-align: right;"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" onclick="if (window.opener) { window.opener.focus(); window.opener.location.href = \'' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '\'; } else { window.open(\'' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '\'); } window.close(); return false;">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; ?></div>
	</div>
	</form>
<?php
  } else {
	echo '  <p style="text-align: center"><strong>' . TEXT_CART_EMPTY . '</strong></p>' . "\n";
  }
  if ($HTTP_GET_VARS['short']=='1') {
	echo '<script language="JavaScript" type="text/javascript"><!--' . "\n" .
	'  function reloadParent() { window.opener.location.reload(); }' . "\n" .
	'  if (window.opener) window.onunload = reloadParent();' . "\n" .
	'//--></script>' . "\n";
  }
?>
