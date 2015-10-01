<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update':
        $products_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
        $products_text = tep_db_prepare_input($HTTP_POST_VARS['reviews_text']);

        tep_db_query("update " . TABLE_REVIEWS . " set reviews_text = '" . tep_db_input($products_text) . "' where products_id = '" . (int)$products_id . "'");

        tep_redirect(tep_href_link(FILENAME_EXPECTED_PRODUCTS, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $products_id));
        break;
      case 'deleteconfirm':
        $products_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);

        tep_db_query("delete from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "'");

        tep_redirect(tep_href_link(FILENAME_EXPECTED_PRODUCTS, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
			<td align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
		  </tr>
		</table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
<?php
  if ($action == 'edit1') {
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_EXPECTING_NUM; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_EXPECTING_TYPE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$expected_types = array(TABLE_EXPECTING_TYPE_0, TABLE_EXPECTING_TYPE_1, TABLE_EXPECTING_TYPE_2);
    $products_query_raw = "select pi.products_id, pi.products_image, pi.products_name, pi.authors_name, pi.manufacturers_name, pi.products_price, cb.customers_basket_notify, cb.customers_id, count(*) as expected from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_PRODUCTS_INFO . " pi where cb.customers_basket_notify > 0" . (sizeof($allowed_shops_array)>0 ? " and cb.customers_id in (select customers_id from " . TABLE_CUSTOMERS . " where shops_id in ('" . implode("', '", $allowed_shops_array) . "'))" : "") . " and cb.products_id = pi.products_id group by concat_ws('_', cb.products_id, cb.customers_basket_notify) order by cb.customers_basket_notify, expected desc, pi.products_name";
    $products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
    $products_query = tep_db_query($products_query_raw);
    while ($products = tep_db_fetch_array($products_query)) {
      if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo)) {
		$products['customers'] = array();
		$customers_query = tep_db_query("select concat_ws(' ', c.customers_firstname, c.customers_lastname) as customers_name, c.customers_email_address, cb.customers_basket_date_added from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_CUSTOMERS . " c where cb.customers_id = c.customers_id and cb.customers_basket_notify = '" . (int)$products['customers_basket_notify'] . "' and cb.products_id = '" . (int)$products['products_id'] . "' order by customers_basket_date_added");
		while ($customers = tep_db_fetch_array($customers_query)) {
		  $products['customers'][] = $customers['customers_name'] . ' <br>&lt;<a href="mailto:' . $customers['customers_email_address'] . '">' . $customers['customers_email_address'] . '</a>&gt;<br />' . tep_date_long(preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $customers['customers_basket_date_added']));
		}
        $pInfo = new objectInfo($products);
      }

      if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EXPECTED_PRODUCTS, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $pInfo->products_id) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EXPECTED_PRODUCTS, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $products['products_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $products['products_id']) . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $products['products_name'] . (tep_not_null($products['authors_name']) ? ', ' . $products['authors_name'] : '') . (tep_not_null($products['manufacturers_name']) ? ', издательство ' . $products['manufacturers_name'] : ''); ?></td>
                <td class="dataTableContent" align="center" nowrap="nowrap"><?php echo $currencies->format($products['products_price']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $products['expected']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $expected_types[$products['customers_basket_notify']]; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($pInfo)) && ($products['products_id'] == $pInfo->products_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_EXPECTED_PRODUCTS, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();
	$expecting_customers = array(TEXT_INFO_EXPECTING_CUSTOMERS_0, TEXT_INFO_EXPECTING_CUSTOMERS_1, TEXT_INFO_EXPECTING_CUSTOMERS_2);

    switch ($action) {
      default:
      if (isset($pInfo) && is_object($pInfo)) {
        $heading[] = array('text' => '<strong>' . $pInfo->products_name . '</strong>');

        $contents[] = array('text' => '<br>' . tep_info_image('thumbs/' . $pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br>' . $expecting_customers[$pInfo->customers_basket_notify]);
        $contents[] = array('text' => '<ol><li>' . implode('</li><br><li>', $pInfo->customers) . '</li></ol>');
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
<?php
  }
?>
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
