<?php
  if (isset($HTTP_GET_VARS['products_id'])) {
    $linked_query = tep_db_query("select p.products_id, trim(concat_ws(' ', pt.products_types_prefix, pd.products_name)) as products_name, p.products_image from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_LINKED . " pl, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_TYPES . " pt on (p.products_types_id = pt.products_types_id and pt.language_id = '" . (int)$languages_id . "') where p.products_id = pd.products_id and p.products_id = pl.linked_id and pd.language_id = '" . (int)$languages_id . "' and pl.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' group by p.products_id order by products_name");
    $num_products_linked = tep_db_num_rows($linked_query);
    if ($num_products_linked > 0) {
?>
<!-- linked_products //-->
<?php
      echo TEXT_LINKED_PRODUCTS;

      $row = 0;
      $col = 0;
      $info_box_contents = array();
      while ($linked = tep_db_fetch_array($linked_query)) {
		if ($col > 0) {
		  $info_box_contents[$row][] = array('text' => tep_draw_separator('pixel_trans.gif', 20, 1));
		}

        $info_box_contents[$row][] = array('align' => 'center',
										   'params' => 'width="' . SMALL_IMAGE_WIDTH . '" class="smallText" valign="top" style="line-height: 1em; padding-bottom: 5px;"',
										   'text' => '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $linked['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $linked['products_image'], $linked['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'vspace="2"') . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $linked['products_id']) . '">' . $linked['products_name'] . '</a>');

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
<!-- linked_products_eof //-->
<?php
    }
  }
?>