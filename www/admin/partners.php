<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'setflag':
		if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['pID'])) {
			tep_db_query("update " . TABLE_PARTNERS . " set partners_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where partners_id = '" . tep_db_input($HTTP_GET_VARS['pID']) . "'");
		  }
		}

		tep_redirect(tep_href_link(FILENAME_PARTNERS, tep_get_all_get_params(array('action', 'flag'))));
		break;
      case 'update_partner':
		$partners_id = $HTTP_POST_VARS['partners_id'];

		$sql_data_array = array('last_modified' => 'now()',
								'partners_name' => tep_db_prepare_input($HTTP_POST_VARS['partners_name']),
								'partners_comission' => str_replace(',', '.', $HTTP_POST_VARS['partners_comission']/100),
								'partners_email_address' => tep_db_prepare_input($HTTP_POST_VARS['partners_email_address']),
								'partners_telephone' => tep_db_prepare_input($HTTP_POST_VARS['partners_telephone']),
								'partners_bank' => tep_db_prepare_input($HTTP_POST_VARS['partners_bank']),
								'partners_url' => tep_db_prepare_input(str_replace('http://', '', $HTTP_POST_VARS['partners_url'])));
		tep_db_perform(TABLE_PARTNERS, $sql_data_array, 'update', "partners_id = '" . (int)$partners_id . "'");

        tep_redirect(tep_href_link(FILENAME_PARTNERS, 'pID=' . $partners_id));
        break;
      case 'insert_balance':
      case 'update_balance':
		$balance_id = $HTTP_POST_VARS['balance_id'];
		$sql_data_array = array('partners_balance_sum' => tep_db_prepare_input($HTTP_POST_VARS['partners_balance_sum']),
								'partners_balance_comments' => tep_db_prepare_input($HTTP_POST_VARS['partners_balance_comments']),
								'partners_id' => $HTTP_GET_VARS['pPath']);
		if ($action == 'insert_balance') {
		  $insert_sql_data = array('date_added' => 'now()');
		  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

		  tep_db_perform(TABLE_PARTNERS_BALANCE, $sql_data_array);
		  $balance_id = tep_db_insert_id();
		} elseif ($action == 'update_balance') {
		  $update_sql_data = array('last_modified' => 'now()');
		  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

		  tep_db_perform(TABLE_PARTNERS_BALANCE, $sql_data_array, 'update', "partners_balance_id = '" . (int)$balance_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $balance_id));
		break;
      case 'delete_partner_confirm':
        if (isset($HTTP_POST_VARS['partners_id'])) {
          $partners_id = tep_db_prepare_input($HTTP_POST_VARS['partners_id']);

		  tep_db_query("delete from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$partners_id . "'");
		  tep_db_query("delete from " . TABLE_PARTNERS_STATISTICS . " where partners_id = '" . (int)$partners_id . "'");
		  tep_db_query("delete from " . TABLE_PARTNERS . " where partners_id = '" . (int)$partners_id . "'");
		  tep_db_query("update " . TABLE_ORDERS . " set partners_id = '0', partners_comission = '0' where partners_id = '" . (int)$partners_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_PARTNERS));
        break;
      case 'delete_balance_confirm':
        if (isset($HTTP_POST_VARS['balance_id'])) {
          $balance_id = tep_db_prepare_input($HTTP_POST_VARS['balance_id']);

		  $order_info_query = tep_db_query("select orders_id from " . TABLE_PARTNERS_BALANCE . " where partners_balance_id = '" . (int)$balance_id . "'");
		  $order_info = tep_db_fetch_array($order_info_query);
		  if ($order_info['orders_id'] > 0) {
			tep_db_query("update " . TABLE_ORDERS . " set partners_id = '0', partners_comission = '0' where orders_id = '" . (int)$order_info['orders_id'] . "'");
		  }
		  tep_db_query("delete from " . TABLE_PARTNERS_BALANCE . " where partners_balance_id = '" . (int)$balance_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath']));
        break;
    }
  }

  if (tep_not_null($HTTP_GET_VARS['pPath'])) {
	$partner_info_query = tep_db_query("select partners_name from " . TABLE_PARTNERS . " where partners_id = '" . (int)$HTTP_GET_VARS['pPath'] . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : ""));
	$partner_info = tep_db_fetch_array($partner_info_query);
	$partner_name = $partner_info['partners_name'];
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
        <td class="pageHeading"><?php echo tep_not_null($HTTP_GET_VARS['pPath']) ? sprintf(HEADING_TITLE, $partner_name) : HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if (tep_not_null($HTTP_GET_VARS['pPath'])) {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BALANCE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_BALANCE_SUM; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_BALANCE_COMMENTS; ?></td>
                <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$balance_query_raw = "select * from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$pPath . "' order by date_added desc";
	$balance_query = tep_db_query($balance_query_raw);
	while ($balance = tep_db_fetch_array($balance_query)) {
	  if ((!isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $balance['partners_balance_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
		$bInfo_array = $balance;
		$bInfo = new objectInfo($bInfo_array);
	  }

	  if (isset($bInfo) && is_object($bInfo) && ($balance['partners_balance_id'] == $bInfo->partners_balance_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $balance['partners_balance_id'] . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $balance['partners_balance_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo tep_date_short($balance['date_added']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $currencies->format($balance['partners_balance_sum']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $balance['partners_balance_comments']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($bInfo) && is_object($bInfo) && ($balance['partners_balance_id'] == $bInfo->partners_balance_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $balance['partners_balance_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $HTTP_GET_VARS['pPath']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=new_balance') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
			</table></td>
<?php
  } else {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PARTNERS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_BALANCE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COMISSION; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$partners_query_raw = "select * from " . TABLE_PARTNERS . " where 1" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by partners_name";
	$partners_query = tep_db_query($partners_query_raw);
	while ($partners = tep_db_fetch_array($partners_query)) {
	  $balance_query = tep_db_query("select sum(partners_balance_sum) as partners_balance from " . TABLE_PARTNERS_BALANCE . " where partners_id = '" . (int)$partners['partners_id'] . "'");
	  $balance = tep_db_fetch_array($balance_query);
	  $partners = array_merge($partners, $balance);
	  if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $partners['partners_id']))) && !isset($pInfo) && (substr($action, 0, 3) != 'new')) {
		$pInfo_array = $partners;
		$pInfo = new objectInfo($pInfo_array);
	  }

	  if (isset($pInfo) && is_object($pInfo) && ($partners['partners_id'] == $pInfo->partners_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $partners['partners_id'] . '&action=edit_partner') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $partners['partners_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $partners['partners_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;<strong>' . $partners['partners_name'] . ' [' . $partners['partners_login'] . ']' . '</strong>'; ?></td>
				<td class="dataTableContent" align="center"><?php echo $currencies->format($partners['partners_balance']); ?></td>
				<td class="dataTableContent" align="center"><?php echo $partners['partners_comission']*100 . '%'; ?></td>
				<td class="dataTableContent" align="center"><?php echo ($partners['partners_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_PARTNERS, tep_get_all_get_params(array('action', 'flag', 'pID')) . 'action=setflag&flag=0&pID=' . $partners['partners_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_PARTNERS, tep_get_all_get_params(array('action', 'flag', 'pID')) . 'action=setflag&flag=1&pID=' . $partners['partners_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($partners['partners_id'] == $pInfo->partners_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $partners['partners_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
            </table></td>
<?php
  }
  $heading = array();
  $contents = array();
  switch ($action) {
	case 'delete_balance':
	  $heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_BALANCE . '</strong>');

	  $contents = array('form' => tep_draw_form('balance', FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=delete_balance_confirm') . tep_draw_hidden_field('balance_id', $bInfo->partners_balance_id));
	  $contents[] = array('text' => TEXT_DELETE_BALANCE_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $bInfo->partners_balance_comments . '</strong>');
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'new_balance':
	  $heading[] = array('text' => '<strong>' . TEXT_HEADING_NEW_BALANCE . '</strong>');

	  $contents = array('form' => tep_draw_form('new_balance', FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=insert_balance', 'post'));
	  $contents[] = array('text' => TEXT_NEW_BALANCE_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_BALANCE_SUM . '<br>' . tep_draw_input_field('partners_balance_sum', '', 'size="10"'));

	  $contents[] = array('text' => '<br>' . TEXT_BALANCE_COMMENTS . '<br>' . tep_draw_textarea_field('partners_balance_comments', 'soft', '30', '4'));

	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'edit_balance':
	  $heading[] = array('text' => '<strong>' . TEXT_HEADING_EDIT_BALANCE . '</strong>');

	  $contents = array('form' => tep_draw_form('edit_balance', FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id . '&action=update_balance', 'post') . tep_draw_hidden_field('balance_id', $bInfo->partners_balance_id));
	  $contents[] = array('text' => TEXT_EDIT_BALANCE_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_BALANCE_SUM . '<br>' . tep_draw_input_field('partners_balance_sum', $bInfo->partners_balance_sum, 'size="10"'));

	  $contents[] = array('text' => '<br>' . TEXT_BALANCE_COMMENTS . '<br>' . tep_draw_textarea_field('partners_balance_comments', 'soft', '30', '4', $bInfo->partners_balance_comments));

	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'delete_partner':
	  $heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_PARTNER . '</strong>');

	  $contents = array('form' => tep_draw_form('balance', FILENAME_PARTNERS, 'action=delete_partner_confirm') . tep_draw_hidden_field('partners_id', $pInfo->partners_id));
	  $contents[] = array('text' => TEXT_DELETE_PARTNER_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $pInfo->partners_name . '</strong>');
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $pInfo->partners_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'edit_partner':
	  $heading[] = array('text' => '<strong>' . TEXT_HEADING_EDIT_PARTNER . '</strong>');

	  $contents = array('form' => tep_draw_form('edit_partner', FILENAME_PARTNERS, 'pID=' . $pInfo->partners_id . '&action=update_partner', 'post') . tep_draw_hidden_field('partners_id', $pInfo->partners_id));
	  $contents[] = array('text' => TEXT_EDIT_PARTNER_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_NAME . '<br>' . tep_draw_input_field('partners_name', $pInfo->partners_name, 'size="30"'));

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_COMISSION . '<br>' . tep_draw_input_field('partners_comission', '' . ($pInfo->partners_comission*100), 'size="2" style="text-align: right;"') . '%');

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_EMAIL_ADDRESS . '<br>' . tep_draw_input_field('partners_email_address', $pInfo->partners_email_address, 'size="30"'));

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_URL . '<br>' . tep_draw_input_field('partners_url', 'http://' . $pInfo->partners_url, 'size="30"'));

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_BANK . '<br>' . tep_draw_textarea_field('partners_bank', 'soft', '30', '4', $pInfo->partners_bank, 'size="30"'));

	  $contents[] = array('text' => '<br>' . TEXT_PARTNER_TELEPHONE . '<br>' . tep_draw_input_field('partners_telephone', $pInfo->partners_telephone, 'size="30"'));

	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	default:
	  if (isset($pInfo) && is_object($pInfo)) {
		$heading[] = array('text' => '<strong>' . $pInfo->partners_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $pInfo->partners_id . '&action=edit_partner') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_PARTNERS, 'pID=' . $pInfo->partners_id . '&action=delete_partner') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_COMISSION . '<br>' . ($pInfo->partners_comission*100) . '%');
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_EMAIL_ADDRESS . '<br>' . '<a href="mailto:' . $pInfo->partners_email_address . '"><u>' . $pInfo->partners_email_address . '</u></a>');
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_URL . '<br>' . '<a href="http://' . $pInfo->partners_url . '" target="_blank"><u>http://' . $pInfo->partners_url . '</u></a>');
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_BANK . '<br>' . $pInfo->partners_bank);
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_TELEPHONE . '<br>' . $pInfo->partners_telephone);
		$contents[] = array('text' => '<br>' . TEXT_DATE_OF_LAST_LOGON . '<br>' . tep_datetime_short($pInfo->date_of_last_logon));
		$contents[] = array('text' => '<br>' . TEXT_PARTNER_NUMBER_OF_LOGONS . '<br>' . $pInfo->number_of_logons);
		$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . '<br>' . tep_datetime_short($pInfo->date_added));
		if (tep_not_null($pInfo->last_modified)) $contents[] = array('text' => '<br>' . TEXT_LAST_MODIFIED . '<br>' . tep_datetime_short($pInfo->last_modified));
	  } elseif (isset($bInfo) && is_object($bInfo)) {
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_BALANCE . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id . '&action=edit_balance') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_PARTNERS, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&bID=' . $bInfo->partners_balance_id . '&action=delete_balance') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		$contents[] = array('text' => '<br>' . $bInfo->partners_balance_comments);
	  }
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
