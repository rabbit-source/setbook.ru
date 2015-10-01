<?php
  if (isset($HTTP_GET_VARS['products_id'])) {
    $orders_query = tep_db_query("select p.products_id, p.products_image from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p where opa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and opa.orders_id = opb.orders_id and opb.products_id != '" . (int)$HTTP_GET_VARS['products_id'] . "' and opb.products_id = p.products_id and opb.orders_id = o.orders_id and p.products_status = '1' group by p.products_id order by o.date_purchased desc limit " . MAX_DISPLAY_ALSO_PURCHASED);
    $num_products_ordered = tep_db_num_rows($orders_query);
    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED) {
?>
<!-- also_purchased_products //-->
<?php
	  echo TEXT_ALSO_PURCHASED_PRODUCTS;

      $row = 0;
      $col = 0;
      $info_box_contents = array();
      while ($orders = tep_db_fetch_array($orders_query)) {
		if ($col > 0) {
		  $info_box_contents[$row][] = array('text' => tep_draw_separator('pixel_trans.gif', 20, 1));
		}

        $orders['products_name'] = tep_get_products_info($orders['products_id'], DEFAULT_LANGUAGE_ID);
        $info_box_contents[$row][] = array('align' => 'center',
										   'params' => 'width="' . SMALL_IMAGE_WIDTH . '" class="smallText" valign="top" style="line-height: 1em; padding-bottom: 5px;"',
										   'text' => '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $orders['products_image'], $orders['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'vspace="2"') . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . $orders['products_name'] . '</a>');

        $col ++;
        if ($col > 5) {
          $col = 0;
          $row ++;
        }
      }

	  $box = new tableBox(array());
	  $box->table_width = '';
	  $box->table_border = '0';
	  $box->table_cellpadding = '0';
      $box->tableBox($info_box_contents, true);
?>
<!-- also_purchased_products_eof //-->
<?php
    }
  }
?>
