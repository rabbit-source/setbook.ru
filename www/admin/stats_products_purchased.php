<?php
  require('includes/application_top.php');

  $products_types_array = array(array('id' => '', 'text' => TEXT_ALL_PRODUCTS));
  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_status = '1' and language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
  while ($products_types = tep_db_fetch_array($products_types_query)) {
	$products_types_array[] = array('id' => $products_types['products_types_id'], 'text' => $products_types['products_types_name']);
  }

  $shops_array = array(array('id' => '', 'text' => TEXT_ALL_SHOPS));
  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " where 1 " . (sizeof($allowed_shops_array)>0 ? " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
  while ($shops = tep_db_fetch_array($shops_query)) {
	$shops_array[] = array('id' => $shops['shops_id'], 'text' => str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])));
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
<div id="spiffycalendar" class="text"></div>
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
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var dateBegin = new ctlSpiffyCalendarBox("dateBegin", "dates", "date_begin", "btnDate1", "<?php echo isset($HTTP_GET_VARS['date_begin']) ? $HTTP_GET_VARS['date_begin'] : date('d.m.Y', time() - 60*60*24*7); ?>", scBTNMODE_CUSTOMBLUE);
  var dateEnd = new ctlSpiffyCalendarBox("dateEnd", "dates", "date_end", "btnDate2", "<?php echo isset($HTTP_GET_VARS['date_end']) ? $HTTP_GET_VARS['date_end'] : date('d.m.Y'); ?>", scBTNMODE_CUSTOMBLUE);
</script>
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('dates', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td class="smallText"><?php echo tep_draw_pull_down_menu('shop', $shops_array, '', 'onchange="this.form.submit();"'); ?></td>
				<td class="smallText"><?php echo tep_draw_pull_down_menu('type', $products_types_array, '', 'onchange="this.form.submit();"'); ?></td>
				<td class="smallText"><?php echo TABLE_HEADING_DATA . ' ' . TABLE_HEADING_DATE_FROM; ?></td>
				<td class="smallText"><script language="javascript">dateBegin.writeControl(); dateBegin.dateFormat="dd.MM.yyyy";</script></td>
				<td class="smallText"><?php echo TABLE_HEADING_DATE_TO; ?></td>
				<td class="smallText"><script language="javascript">dateEnd.writeControl(); dateEnd.dateFormat="dd.MM.yyyy";</script></td>
				<td><?php echo tep_image_submit('button_select.gif', IMAGE_SELECT); ?></td>
			  </tr>
			</table></td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
  if ( tep_not_null($HTTP_GET_VARS['date_begin']) || tep_not_null($HTTP_GET_VARS['date_end']) || tep_not_null($HTTP_GET_VARS['shop']) || tep_not_null($HTTP_GET_VARS['type']) ) {
	$where_string = '';
	if (tep_not_null($HTTP_GET_VARS['shop'])) $where_string .= " and o.shops_id = '" . (int)$HTTP_GET_VARS['shop'] . "'";
	elseif (sizeof($allowed_shops_array)>0) $where_string .= " and o.shops_id in ('" . implode("', '", $allowed_shops_array) . "')";
	if (tep_not_null($HTTP_GET_VARS['date_begin'])) $where_string .= " and o.date_purchased >= '" . preg_replace('/^(\d+)\.(\d+)\.(\d+)$/', '$3-$2-$1', $HTTP_GET_VARS['date_begin']) . "'";
	if (tep_not_null($HTTP_GET_VARS['date_end'])) $where_string .= " and o.date_purchased <= '" . preg_replace('/^(\d+)\.(\d+)\.(\d+)$/', '$3-$2-$1', $HTTP_GET_VARS['date_end']) . "'";
	if (tep_not_null($HTTP_GET_VARS['type'])) $where_string .= " and op.products_types_id = '" . (int)$HTTP_GET_VARS['type'] . "'";
?>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</td>
              </tr>
<?php
	if (isset($HTTP_GET_VARS['page']) && ($HTTP_GET_VARS['page'] > 1)) $rows = $HTTP_GET_VARS['page'] * MAX_DISPLAY_SEARCH_RESULTS - MAX_DISPLAY_SEARCH_RESULTS;
	$products_query_raw = "select op.products_id, op.products_model, op.products_name, count(*) as products_ordered from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS . " o where o.orders_status <> '10'" . $where_string . " and op.orders_id = o.orders_id group by op.products_id order by products_ordered DESC, op.products_name";
	$products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);

	$rows = 0;
	$products_query = tep_db_query($products_query_raw);
	while ($products = tep_db_fetch_array($products_query)) {
	  $rows++;

	  if (strlen($rows) < 2) {
		$rows = '0' . $rows;
	  }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo $rows; ?>.</td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $products['products_id'], 'NONSSL') . '" target="_blank">[' . $products['products_model'] . '] ' . $products['products_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products['products_ordered']; ?>&nbsp;</td>
              </tr>
<?php
	}
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'page', tep_session_name()))); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
<?php
  }
?>
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
