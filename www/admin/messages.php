<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'insert_message':
	  case 'update_message':
		$messages_id = tep_db_prepare_input($HTTP_POST_VARS['messages_id']);
		$messages_name = $HTTP_POST_VARS['messages_name'];
		$messages_description = $HTTP_POST_VARS['messages_description'];
		$messages_pages = $HTTP_POST_VARS['messages_pages'];
		$status = $HTTP_POST_VARS['status'];
		$sort_order = $HTTP_POST_VARS['sort_order'];
//		if ($status==1) tep_db_query("update " . TABLE_MESSAGES . " set status = '0'");
		$shops_array = $HTTP_POST_VARS['messages_shops'];
		if (!is_array($shops_array)) $shops_array = array();

		$messages_pages_array = array();
		if ($HTTP_POST_VARS['messages_pages_show']=='1') {
		  if (!is_array($messages_pages)) $messages_pages = array();
		  ksort($messages_pages);
		  while (list(, $messages_page) = each($messages_pages)) {
			if (tep_not_null($messages_page) && !in_array($messages_page, $messages_pages_array)) $messages_pages_array[] = $messages_page;
		  }
		}

		if (tep_not_null($HTTP_POST_VARS['expires_date'])) {
		  $expires_date = preg_replace('/(\d{2})\.(\d{2})\.(\d{4})/', '$3-$2-$1', $HTTP_POST_VARS['expires_date']);
		} else {
		  $expires_date = 'null';
		}

		$sql_data_array = array('sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']),
								'messages_name' => tep_db_prepare_input($messages_name),
								'messages_description' => tep_db_prepare_input($messages_description),
								'status' => tep_db_prepare_input($status),
								'sort_order' => tep_db_prepare_input($sort_order),
								'expires_date' => tep_db_prepare_input($expires_date),
								'messages_pages' => tep_db_input(implode("\n", $messages_pages_array)));

		if ($action == 'insert_message') {
		  $insert_sql_data = array('date_added' => 'now()',
								   'messages_id' => $messages_id);

		  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

		  $shops_query = tep_db_query("select shops_id, shops_database from " . TABLE_SHOPS . " where shops_database <> '' and shops_id in ('" . implode("', '", $shops_array) . "')");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			tep_db_select_db($shops['shops_database']);

			tep_db_perform(TABLE_MESSAGES, $sql_data_array);
			if ($shops['shops_id']==SHOP_ID) $messages_id = tep_db_insert_id();
		  }
		  tep_db_select_db(DB_DATABASE);
		} elseif ($action == 'update_message') {
		  $update_sql_data = array('last_modified' => 'now()');

		  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

		  tep_db_perform(TABLE_MESSAGES, $sql_data_array, 'update', "messages_id = '" . (int)$messages_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_MESSAGES, 'mID=' . $messages_id));
		break;
	  case 'delete_message_confirm':
		$messages_id = tep_db_prepare_input($HTTP_POST_VARS['messages_id']);

		tep_db_query("delete from " . TABLE_MESSAGES . " where messages_id = '" . (int)$messages_id . "'");

		tep_redirect(tep_href_link(FILENAME_MESSAGES));
		break;
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  $new_status = (int)$HTTP_GET_VARS['flag'];
          if (isset($HTTP_GET_VARS['mID'])) {
			tep_db_query("update " . TABLE_MESSAGES . " set status = '" . $new_status . "', last_modified = now() where messages_id = '" . (int)$HTTP_GET_VARS['mID'] . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_MESSAGES, tep_get_all_get_params(array('action', 'flag'))));
        break;
    }
  }

  $available_pages = array(FILENAME_CATALOG_DEFAULT, FILENAME_CATALOG_SHOPPING_CART, FILENAME_CATALOG_CHECKOUT_SHIPPING, FILENAME_CATALOG_CHECKOUT_PAYMENT, FILENAME_CATALOG_CHECKOUT_CONFIRMATION);
  $available_pages_array = array();
  reset($available_pages);
  while (list(, $page_filename) = each($available_pages)) {
	$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($page_filename)) . "' and language_id = '" . (int)$languages_id . "'");
	if (tep_db_num_rows($page_info_query) > 0) $page_info = tep_db_fetch_array($page_info_query);
	else $page_info = array('pages_name' => $page_filename);
	$available_pages_array[$page_filename] = $page_info['pages_name'];
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
    <td width="100%" valign="top">
<?php
  if ($action == 'edit_message' || $action == 'new_message') {
    $parameters = array('messages_name' => '',
						'messages_description' => '',
						'messages_id' => '',
						'date_added' => '',
						'last_modified' => '',
						'status' => '',
						'messages_pages' => array(),
						'sort_order' => '');

    $mInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['mID']) && empty($HTTP_POST_VARS)) {
      $message_query = tep_db_query("select * from " . TABLE_MESSAGES . " where messages_id = '" . (int)$HTTP_GET_VARS['mID'] . "'");
      $message = tep_db_fetch_array($message_query);

      $mInfo->objectInfo($message);
	  if (tep_not_null($mInfo->messages_pages)) $mInfo->messages_pages = explode("\n", $mInfo->messages_pages);
	  else $mInfo->messages_pages = array();
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $mInfo->objectInfo($HTTP_POST_VARS);
    }

    if (!isset($mInfo->status)) $mInfo->status = '1';
    switch ($mInfo->status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

	$form_action = (isset($HTTP_GET_VARS['mID']) ? 'update_message' : 'insert_message');
	echo tep_draw_form('new_message', FILENAME_MESSAGES, tep_get_all_get_params(array('mID', 'action')) . 'action=' . $form_action . (isset($HTTP_GET_VARS['mID']) ? '&mID=' . $HTTP_GET_VARS['mID'] : '')) . tep_draw_hidden_field('messages_id', $mInfo->messages_id);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_message' ? sprintf(TEXT_EDIT_MESSAGE, $mInfo->messages_name) : TEXT_NEW_MESSAGE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td class="main" width="250"><?php echo TEXT_MESSAGE_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('messages_name', $mInfo->messages_name, 'size="40"'); ?></td>
          </tr>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
          <tr valign="top">
            <td class="main" width="250"><?php echo TEXT_MESSAGE_DESCRIPTION; ?></td>
            <td class="main"><?php
	$field_value = $mInfo->messages_description;
	$field_value = str_replace('\\\"', '"', $field_value);
	$field_value = str_replace('\"', '"', $field_value);
	$field_value = str_replace("\\\'", "\'", $field_value);
//	$field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	$editor = new editor('messages_description');
	$editor->Value = $field_value;
	$editor->Height = '280';
	$editor->Create();
?></td>
          </tr>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
          <tr valign="top">
            <td class="main" width="250"><?php echo TEXT_MESSAGE_PAGES; ?></td>
            <td class="main"><?php
	echo tep_draw_radio_field('messages_pages_show', '0', sizeof($mInfo->messages_pages)==0, '', 'onclick="if (this.checked) document.getElementById(\'messagePages\').style.display = \'none\';"') . ' ' . TEXT_MESSAGE_PAGES_ALL. '<br />' . "\n";
	echo tep_draw_radio_field('messages_pages_show', '1', sizeof($mInfo->messages_pages)>0, '', 'onclick="if (this.checked) document.getElementById(\'messagePages\').style.display = \'block\';"') . ' ' . TEXT_MESSAGE_PAGES_EXACT. '<br />' . "\n";
?><table border="0" cellspacing="0" cellpadding="0" id="messagePages" style="display: <?php echo (sizeof($mInfo->messages_pages)==0 ? 'none' : 'table'); ?>;">
			  <tr>
				<td rowspan="6"><?php echo tep_draw_separator('pixek_trans.gif', 75, 1); ?></td>
				<td width="250"><?php echo TEXT_MESSAGE_PAGES_MAIN; ?></td>
				<td width="250"><?php echo TEXT_MESSAGE_PAGES_OTHER; ?></td>
			  </tr><?php
	$other_pages = array_diff($mInfo->messages_pages, array_keys($available_pages_array));
	$other_pages_array = array();
	reset($other_pages);
	while (list(, $other_page) = each($other_pages)) {
	  if (tep_not_null($other_page) && !in_array($other_page, $other_pages_array)) $other_pages_array[] = $other_page;
	}
	$i = 0;
	reset($available_pages_array);
	while (list($page_filename, $page_name) = each($available_pages_array)) {
?>
			  <tr>
				<td><?php echo tep_draw_checkbox_field('messages_pages[' . $i . ']', $page_filename, in_array($page_filename, $mInfo->messages_pages)) . ' ' . $page_name . '<br />' . "\n"; ?></td>
				<td><?php echo tep_draw_input_field('messages_pages[' . ($i+5) . ']', $other_pages_array[$i], 'size="30"') . '<br />' . "\n"; ?></td>
			  </tr>
<?php
	  $i ++;
	}
?>
			</table></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_MESSAGE_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_checkbox_field('status', '1', $in_status) . ' ' . TEXT_MESSAGE_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	if ($form_action=='insert_message') {
?>
          <tr>
            <td class="main" width="250"><?php echo TEXT_MESSAGE_SHOPS; ?></td>
            <td class="main"><?php
	  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " where shops_database <> ''" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		echo ($shops['shops_id']==SHOP_ID ? tep_draw_checkbox_field('', '', true, '', 'disabled="disabled"') . tep_draw_hidden_field('messages_shops[]', $shops['shops_id']) : tep_draw_checkbox_field('messages_shops[]', $shops['shops_id'])) . ' ' . str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])) . '<br>' . "\n";
	  }
?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	}
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var expiresDate = new ctlSpiffyCalendarBox("expiresDate", "new_message", "expires_date", "btnDate1", "<?php echo (tep_not_null($mInfo->expires_date) ? tep_date_short($mInfo->expires_date) : ''); ?>", scBTNMODE_CUSTOMBLUE);
</script>
		  <tr>
			<td class="main" width="250"><?php echo TEXT_EXPIRES_DATE; ?><br><small><?php echo TEXT_EXPIRES_DATE_TEXT; ?></small></td>
			<td class="main"><script language="javascript">expiresDate.writeControl(); expiresDate.dateFormat="dd.MM.yyyy";</script></td>
		  </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('sort_order', $mInfo->sort_order, 'size="3"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	if (isset($HTTP_GET_VARS['mID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_MESSAGES, (isset($HTTP_GET_VARS['mID']) ? 'mID=' . $HTTP_GET_VARS['mID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MESSAGES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$rows = 0;
	$messages_query = tep_db_query("select * from " . TABLE_MESSAGES . " where 1 order by sort_order, messages_name");
	while ($messages = tep_db_fetch_array($messages_query)) {
	  $rows ++;
	  if ((!isset($HTTP_GET_VARS['mID']) || (isset($HTTP_GET_VARS['mID']) && ($HTTP_GET_VARS['mID'] == $messages['messages_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
		$pages_string = '';
		if (tep_not_null($messages['messages_pages'])) {
		  $message_pages = explode("\n", $messages['messages_pages']);
		  reset($message_pages);
		  while (list(, $message_page) = each($message_pages)) {
			$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($message_page)) . "' and language_id = '" . (int)$languages_id . "'");
			$page_info = tep_db_fetch_array($page_info_query);
			$pages_string .= '<li>' . $message_page;
			if (tep_not_null($page_info['pages_name'])) $pages_string .= ' (' . $page_info['pages_name'] . ')';
			$pages_string .= '</li>' . "\n";
		  }
		}
		$messages['messages_pages'] = $pages_string;
		$mInfo_array = $messages;

		$mInfo = new objectInfo($mInfo_array);
	  }

	  if (isset($mInfo) && is_object($mInfo) && ($messages['messages_id'] == $mInfo->messages_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $messages['messages_id'] . '&action=edit_message') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $messages['messages_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo '[' . $messages['sort_order'] . ']&nbsp;' . $messages['messages_name'] . (tep_not_null($messages['expires_date']) ? ' (' . TEXT_EXPIRES_DATE_SHORT . ' ' . tep_date_short($messages['expires_date']) . ')' : ''); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($messages['status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_MESSAGES, tep_get_all_get_params(array('action', 'flag', 'mID')) . 'action=setflag&flag=0&mID=' . $messages['messages_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_MESSAGES, tep_get_all_get_params(array('action', 'flag', 'mID')) . 'action=setflag&flag=1&mID=' . $messages['messages_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($messages['messages_id'] == $mInfo->messages_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $messages['messages_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_MESSAGES, 'action=new_message') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?>&nbsp;</td>
                  </tr>
                </table></td>
			  </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
	  case 'delete_message':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_MESSAGE . '</strong>');

		$contents = array('form' => tep_draw_form('messages', FILENAME_MESSAGES, 'mID=' . $mInfo->messages_id . '&action=delete_message_confirm') . tep_draw_hidden_field('messages_id', $mInfo->messages_id));
		$contents[] = array('text' => TEXT_DELETE_MESSAGE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $mInfo->messages_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $mInfo->messages_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
      default:
		if (isset($mInfo) && is_object($mInfo)) {
		  $heading[] = array('text' => '<strong>' . $mInfo->messages_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $mInfo->messages_id . '&action=edit_message') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_MESSAGES, 'mID=' . $mInfo->messages_id . '&action=delete_message') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . $mInfo->messages_description);
		  $contents[] = array('text' => '<br>' . TEXT_MESSAGE_PAGES_FULL . ' ' . (tep_not_null($mInfo->messages_pages) ? TEXT_MESSAGE_PAGES_EXACT_1 . '<br>' . $mInfo->messages_pages : TEXT_MESSAGE_PAGES_ALL_1));
		  if (tep_not_null($mInfo->expires_date)) $contents[] = array('text' => '<br>' . TEXT_EXPIRES_DATE . ' ' . tep_date_short($mInfo->expires_date));
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_datetime_short($mInfo->date_added));
		  if (tep_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_datetime_short($mInfo->last_modified));
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
    </table>
<?php
  }
?>
    </td>
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