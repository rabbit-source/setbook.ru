<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
		$old_price_equation = '';
        if (isset($HTTP_GET_VARS['sID'])) {
		  $shop_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);
		  $prev_info_query = tep_db_query("select shops_price_equation from " . TABLE_SHOPS . " where shops_id = '" . (int)$shop_id . "'");
		  $prev_info = tep_db_fetch_array($prev_info_query);
		  $old_price_equation = $prev_info['shops_price_equation'];
		}
        $shops_url = 'http://' . str_replace('http://', '', tep_db_prepare_input($HTTP_POST_VARS['shops_url']));
        $shops_ssl = tep_db_prepare_input($HTTP_POST_VARS['shops_ssl']);
        $shops_currency_string = '';
		$shop_currencies = $HTTP_POST_VARS['shops_currencies'];
		reset($shop_currencies);
		while (list(, $shop_currency) = each($shop_currencies)) {
		  if ($shop_currency==$HTTP_POST_VARS['shops_currency_default']) $shops_currency_string =$shop_currency . ',' . $shops_currency_string;
		  else $shops_currency_string .= $shop_currency . ',';
		}
		if (substr($shops_currency_string, -1)==',') $shops_currency_string = substr($shops_currency_string, 0, -1);
        $shops_name = tep_db_prepare_input($HTTP_POST_VARS['shops_name']);
        $shops_description = tep_db_prepare_input($HTTP_POST_VARS['shops_description']);
        $shops_database = tep_db_prepare_input($HTTP_POST_VARS['shops_database']);
        $shops_htpasswd_file = tep_db_prepare_input($HTTP_POST_VARS['shops_htpasswd_file']);
        $shops_price_equation = str_replace(',', '.', tep_db_prepare_input($HTTP_POST_VARS['shops_price_equation']));
        $shops_default_status = tep_db_prepare_input($HTTP_POST_VARS['shops_default_status']);
        $shops_shipping_days = tep_db_prepare_input($HTTP_POST_VARS['shops_shipping_days']);
        $shops_listing_status = tep_db_prepare_input($HTTP_POST_VARS['shops_listing_status']);
        $shops_templates_dir = tep_db_prepare_input($HTTP_POST_VARS['shops_templates_dir']);
        $shops_fs_dir = tep_db_prepare_input($HTTP_POST_VARS['shops_fs_dir']);
        $shops_prefix = tep_db_prepare_input($HTTP_POST_VARS['shops_prefix']);
        $shops_email_use_html = tep_db_prepare_input($HTTP_POST_VARS['shops_email_use_html']);
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
		if (substr($shops_templates_dir, -1)=='/') $shops_templates_dir = substr($shops_templates_dir, 0, -1);

        $sql_data_array = array('shops_name' => $shops_name,
                                'shops_description' => $shops_description,
                                'shops_url' => $shops_url,
                                'shops_ssl' => $shops_ssl,
                                'shops_database' => $shops_database,
                                'shops_currency' => $shops_currency_string,
                                'shops_price_equation' => $shops_price_equation,
                                'shops_default_status' => $shops_default_status,
                                'shops_htpasswd_file' => $shops_htpasswd_file,
                                'shops_shipping_days' => $shops_shipping_days,
                                'shops_listing_status' => $shops_listing_status,
                                'shops_templates_dir' => $shops_templates_dir,
                                'shops_email_use_html' => $shops_email_use_html,
                                'shops_fs_dir' => $shops_fs_dir,
                                'shops_prefix' => $shops_prefix,
                                'sort_order' => $sort_order);

        if ($action == 'insert') {
          tep_db_perform(TABLE_SHOPS, $sql_data_array);
          $shop_id = tep_db_insert_id();
        } elseif ($action == 'save') {
          tep_db_perform(TABLE_SHOPS, $sql_data_array, 'update', "shops_id = '" . (int)$shop_id . "'");
		  if ($old_price_equation!=$shops_price_equation) tep_update_shops_prices($shop_id);
        }

        tep_redirect(tep_href_link(FILENAME_SHOPS, 'sID=' . $shop_id));
        break;
      case 'deleteconfirm':
        $shops_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        tep_db_query("delete from " . TABLE_SHOPS . " where shops_id = '" . (int)$shops_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_SHOPS . " where shops_id = '" . (int)$shops_id . "'");

        tep_redirect(tep_href_link(FILENAME_SHOPS));
        break;
    }
  }

  $databases_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $query = tep_db_list_dbs();
  while ($row = tep_db_fetch_array($query)) {
	if (!in_array($row['Database'], array('information_schema', 'mysql'))) $databases_array[] = array('id' => $row['Database'], 'text' => $row['Database']);
  }

  $currencies_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  reset($currencies->currencies);
  while (list($currency_code, $currency_row) = each($currencies->currencies)) {
	$currencies_array[] = array('id' => $currency_code, 'text' => $currency_row['title']);
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SHOPS_URL; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SHOPS_EQUATION; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
  $default_currency = '';
  $shops_query_raw = "select * from " . TABLE_SHOPS . " order by sort_order, shops_url";
  $shops_query = tep_db_query($shops_query_raw);
  while ($shops = tep_db_fetch_array($shops_query)) {
    if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $shops['shops_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
      $sInfo = new objectInfo($shops);
	  list($sInfo->shops_currency_default) = explode(',', $sInfo->shops_currency);
    }

    if (isset($sInfo) && is_object($sInfo) && ($shops['shops_id'] == $sInfo->shops_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SHOPS, 'sID=' . $shops['shops_id']) . '\'">' . "\n";
    }

    if ($shops['shops_default_status']=='1') {
	  $default_currency = $shops['shops_currency'];
      echo '                <td class="dataTableContent" title="' . $shops['shops_description'] . '">[' . $shops['sort_order'] . '] <strong>' . $shops['shops_url'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent" title="' . $shops['shops_description'] . '">[' . $shops['sort_order'] . '] ' . $shops['shops_url'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $shops['shops_equation']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($shops['shops_id'] == $sInfo->shops_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $shops['shops_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
  if (empty($action)) {
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id . '&action=new') . '">' . tep_image_button('button_new_record.gif', IMAGE_NEW_RECORD) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
    case 'edit':
      $heading[] = array('text' => '<strong>' . ($action=='new' ? TEXT_INFO_HEADING_NEW_SHOP : $sInfo->shops_name) . '</strong>');

      $contents = array('form' => tep_draw_form('shops', FILENAME_SHOPS, (isset($sInfo) ? 'sID=' . $sInfo->shops_id : '') . '&action=' . ($action=='new' ? 'insert' : 'save')));
      $contents[] = array('text' => ($action=='new' ? TEXT_INFO_INSERT_INTRO : TEXT_INFO_EDIT_INTRO));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_NAME . '<br>' . tep_draw_input_field('shops_name', $sInfo->shops_name, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_DESCRIPTION . '<br>' . tep_draw_textarea_field('shops_description', 'soft', '30', '3', $sInfo->shops_description));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_URL . '<br>' . tep_draw_input_field('shops_url', $sInfo->shops_url, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_SSL . '<br>' . tep_draw_input_field('shops_ssl', $sInfo->shops_ssl, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_DATABASE . '<br>' . tep_draw_pull_down_menu('shops_database', $databases_array, $sInfo->shops_database));
	  $currencies_string = '';
	  reset($currencies->currencies);
	  while (list($currency_code, $currency_row) = each($currencies->currencies)) {
		$currencies_string .= '<br>'. tep_draw_radio_field('shops_currency_default', $currency_code, $sInfo->shops_currency_default==$currency_code) . tep_draw_checkbox_field('shops_currencies[]', $currency_code, in_array($currency_code, explode(',', $sInfo->shops_currency))) . ($sInfo->shops_currency_default==$currency_code ? '<strong>' . $currency_row['title'] . '</strong>' : $currency_row['title']);
	  }
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_CURRENCY . $currencies_string);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_PRICE_EQUATION . '<br><small>' . TEXT_INFO_SHOPS_PRICE_EQUATION_TEXT . '</small>' . '<br>' . tep_draw_input_field('shops_price_equation', $sInfo->shops_price_equation, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_HTPASSWD_FILE . '<br>' . tep_draw_input_field('shops_htpasswd_file', $sInfo->shops_htpasswd_file, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_TEMPLATES_DIR . '<br>' . DIR_WS_CATALOG_TEMPLATES . tep_draw_input_field('shops_templates_dir', $sInfo->shops_templates_dir, 'size="5"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_FS_DIR . '<br>' . tep_draw_input_field('shops_fs_dir', $sInfo->shops_fs_dir, 'size="30"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_PREFIX . '<br>' . tep_draw_input_field('shops_prefix', $sInfo->shops_prefix, 'size="2"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $sInfo->sort_order, 'size="2"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_SHIPPING_DAYS . '<br>' . tep_draw_input_field('shops_shipping_days', $sInfo->shops_shipping_days, 'size="2"'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('shops_email_use_html', '1', $sInfo->shops_email_use_html==1) . ' ' . TEXT_INFO_SHOPS_EMAIL_USE_HTML);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('shops_listing_status', '1', $sInfo->shops_listing_status==1) . ' ' . TEXT_INFO_SHOPS_LISTING_STATUS);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('shops_default_status', '1', $sInfo->shops_default_status==1) . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='new' ? tep_image_submit('button_insert.gif', IMAGE_INSERT) : tep_image_submit('button_update.gif', IMAGE_UPDATE)) . ' <a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $HTTP_GET_VARS['sID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SHOP . '</strong>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $sInfo->shops_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id . '&action=deleteconfirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<strong>' . $sInfo->shops_name . '</strong>');

		$example_price = 100;
		if (tep_not_null($sInfo->shops_price_equation)) eval('$shop_price = ' . sprintf(str_replace('%s', '%s*' . $currencies->currencies[$sInfo->shops_currency_default]['value'], $sInfo->shops_price_equation), $example_price) . ';');
		else $shop_price = $example_price;

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SHOPS, 'sID=' . $sInfo->shops_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        if (tep_not_null($sInfo->shops_description)) $contents[] = array('text' => '<br>' . $sInfo->shops_description);
        $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_URL . ' <a href="' . $sInfo->shops_url . '" target="_blank"><u>' . $sInfo->shops_url . '</u></a>');
        $contents[] = array('text' => TEXT_INFO_SHOPS_CURRENCY . ' ' . $currencies->currencies[$sInfo->shops_currency_default]['title']);
        $contents[] = array('text' => TEXT_INFO_SHOPS_DATABASE . ' ' . $sInfo->shops_database);
        $contents[] = array('text' => TEXT_INFO_SHOPS_HTPASSWD_FILE . ' ' . $sInfo->shops_htpasswd_file);
        $contents[] = array('text' => TEXT_INFO_SHOPS_TEMPLATES_DIR . ' ' . DIR_WS_CATALOG_TEMPLATES . (SHOP_ID!=$sInfo->shops_id ? $sInfo->shops_templates_dir . '/' : ''));
        $contents[] = array('text' => TEXT_INFO_SHOPS_FS_DIR . ' ' . $sInfo->shops_fs_dir);
        $contents[] = array('text' => TEXT_INFO_SHOPS_PREFIX . ' ' . $sInfo->shops_prefix);
        $contents[] = array('text' => TEXT_INFO_SHOPS_EMAIL_USE_HTML . ' ' . ($sInfo->shops_email_use_html=='1' ? TEXT_YES : TEXT_NO));
        $contents[] = array('text' => TEXT_INFO_SHOPS_SHIPPING_DAYS . ' ' . ($sInfo->shops_shipping_days>0 ? '+' : '') . $sInfo->shops_shipping_days);
        $contents[] = array('text' => TEXT_INFO_SHOPS_LISTING_STATUS . ' ' . ($sInfo->shops_listing_status=='1' ? TEXT_YES : TEXT_NO));
        $contents[] = array('text' => '<br>' . TEXT_INFO_SHOPS_PRICE_EQUATION . ' ' . $sInfo->shops_price_equation);
        $contents[] = array('text' => TEXT_INFO_SHOPS_PRICE_EXAMPLE . ' ' . $currencies->format($example_price, false, $default_currency) . ' &raquo; ' . $currencies->format($shop_price, false, $sInfo->shops_currency_default) . ($sInfo->shops_currency_default!=$default_currency ? ' (' . $currencies->format($shop_price/$currencies->currencies[$sInfo->shops_currency_default]['value'], false, $default_currency) . ')' : ''));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>