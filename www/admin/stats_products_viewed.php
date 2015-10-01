<?php
  require('includes/application_top.php');
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('dates', FILENAME_STATS_PRODUCTS_VIEWED, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><table border="0" cellspacing="0" cellpadding="2">
			  <tr>
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
  if ( tep_not_null($HTTP_GET_VARS['date_begin']) || tep_not_null($HTTP_GET_VARS['date_end']) ) {
	$where_string = '';
	if (tep_not_null($HTTP_GET_VARS['date_begin'])) $where_string .= " and pv.date_viewed >= '" . preg_replace('/^(\d+)\.(\d+)\.(\d+)$/', '$3-$2-$1', $HTTP_GET_VARS['date_begin']) . "'";
	if (tep_not_null($HTTP_GET_VARS['date_end'])) $where_string .= " and pv.date_viewed <= '" . preg_replace('/^(\d+)\.(\d+)\.(\d+)$/', '$3-$2-$1', $HTTP_GET_VARS['date_end']) . "'";
?>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VIEWED; ?>&nbsp;</td>
              </tr>
<?php
	$categories_count = 0;
	if (isset($HTTP_GET_VARS['page']) && ($HTTP_GET_VARS['page'] > 1)) $rows = $HTTP_GET_VARS['page'] * MAX_DISPLAY_SEARCH_RESULTS - MAX_DISPLAY_SEARCH_RESULTS;
	$rows = 0;
	$products_query_raw = "select pv.products_id, pd.products_name, sum(pv.products_viewed) as viewed from " . TABLE_PRODUCTS_VIEWED . " pv, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pv.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'" . $where_string . " group by pv.products_id order by viewed desc";
	$products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
	$products_query = tep_db_query($products_query_raw);
	while ($products = tep_db_fetch_array($products_query)) {
	  $rows++;
	  if (strlen($rows) < 2) {
		$rows = '0' . $rows;
	  }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo tep_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_PRODUCTS_VIEWED . '?page=' . $HTTP_GET_VARS['page'], 'NONSSL'); ?>'">
                <td class="dataTableContent"><?php echo $rows; ?>.</td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_PRODUCTS_VIEWED . '?page=' . $HTTP_GET_VARS['page'], 'NONSSL') . '">' . $products['products_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products['viewed']; ?>&nbsp;</td>
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
                <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'page', tep_session_name()))); ?></td>
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
