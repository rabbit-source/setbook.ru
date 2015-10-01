<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
      case 'insert':
		if (isset($HTTP_GET_VARS['bID'])) $blacklist_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);
		$blacklist_ip = tep_db_prepare_input($HTTP_POST_VARS['blacklist_ip']);
		$blacklist_comments = tep_db_prepare_input($HTTP_POST_VARS['blacklist_comments']);

		$blacklist_check_query = tep_db_query("select count(*) as total from " . TABLE_BLACKLIST . " where blacklist_ip = '" . tep_db_input($blacklist_ip) . "'");
		$blacklist_check = tep_db_fetch_array($blacklist_check_query);
		if ($blacklist_check['total'] < 1) {
		  $sql_data_array = array('blacklist_ip' => $blacklist_ip, 'blacklist_comments' => $blacklist_comments);

		  if ($action == 'insert') {
			$insert_sql_data = array('date_added' => 'now()', 'users_id' => $REMOTE_USER);
			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
			tep_db_perform(TABLE_BLACKLIST, $sql_data_array);

			$blacklist_id = tep_db_insert_id();
		  } elseif ($action == 'save') {
			tep_db_perform(TABLE_BLACKLIST, $sql_data_array, 'update', "blacklist_id = '" . (int)$blacklist_id . "'");
		  }
		} else {
		  $messageStack->add_session(sprintf(WARNING_IP_ALREADY_EXISTS, $blacklist_ip), 'warning');
		}

        tep_redirect(tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . ($blacklist_id>0 ? '&bID=' . $blacklist_id : '')));
        break;
      case 'deleteconfirm':
        $blacklist_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);

        tep_db_query("delete from " . TABLE_BLACKLIST . " where blacklist_id = '" . (int)$blacklist_id . "'");

        tep_redirect(tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page']));
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_IP; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COMMENTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_USER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
  $blacklist_query_raw = "select bl.*, u.users_name from " . TABLE_BLACKLIST . " bl left join " . TABLE_USERS . " u on bl.users_id = u.users_id where 1 order by bl.date_added desc";
  $blacklist_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $blacklist_query_raw, $blacklist_query_numrows);
  $blacklist_query = tep_db_query($blacklist_query_raw);
  while ($blacklist = tep_db_fetch_array($blacklist_query)) {
    if ((!isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $blacklist['blacklist_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
      $bInfo = new objectInfo($blacklist);
    }

    if (isset($bInfo) && is_object($bInfo) && ($blacklist['blacklist_id'] == $bInfo->blacklist_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->blacklist_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $blacklist['blacklist_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $blacklist['blacklist_ip']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $blacklist['blacklist_comments']; ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_date_short($blacklist['date_added']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $blacklist['users_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($bInfo) && is_object($bInfo) && ($blacklist['blacklist_id'] == $bInfo->blacklist_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $blacklist['blacklist_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $blacklist_split->display_count($blacklist_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $blacklist_split->display_links($blacklist_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
    case 'edit':
      $heading[] = array('text' => '<strong>' . ($action=='edit' ? TEXT_INFO_HEADING_EDIT_RECORD : TEXT_INFO_HEADING_NEW_RECORD) . '</strong>');

      $contents = array('form' => tep_draw_form('blacklist', FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . ($action=='edit' ? '&bID=' . $bInfo->blacklist_id : '') . '&action=' . ($action=='edit' ? 'save' : 'insert')));
      $contents[] = array('text' => ($action=='edit' ? TEXT_INFO_EDIT_INTRO : TEXT_INFO_INSERT_INTRO));

	  $contents[] = array('text' => '<br>' . TEXT_INFO_BLACKLIST_IP . '<br>' . tep_draw_input_field('blacklist_ip', $bInfo->blacklist_ip, 'size=32'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_BLACKLIST_COMMENTS . '<br>' . tep_draw_textarea_field('blacklist_comments', 'soft', '32', '3', $bInfo->blacklist_comments));

      $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='edit' ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . '&nbsp;<a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . ($action=='edit' ? '&bID=' . $bInfo->blacklist_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_RECORD . '</strong>');

      $contents = array('form' => tep_draw_form('blacklist', FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->blacklist_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $bInfo->blacklist_ip . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->blacklist_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
	default:
      if (is_object($bInfo)) {
		$heading[] = array('text' => '<strong>' . $bInfo->blacklist_ip . '</strong>');

		if (DEBUG_MODE=='on') $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->blacklist_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BLACKLIST, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->blacklist_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		$contents[] = array('text' => '<br>' . $bInfo->blacklist_comments);
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
