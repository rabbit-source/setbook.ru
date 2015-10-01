<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  function tep_update_htpasswd_file1() {
	$passwords = '';
	$users_query = tep_db_query("select users_id, users_password from " . TABLE_USERS . " where users_status = '1'");
	while ($users = tep_db_fetch_array($users_query)) {
	  $passwords .= $users['users_id'] . ':' . $users['users_password'] . "\r\n";
	}
	if ($fp = fopen(HTPASSWD_FILENAME, "w")) {
	  fwrite($fp, $passwords);
	  fclose($fp);
	}
  }

  function tep_update_htpasswd_file() {
	$shops = array();
	$shops_query = tep_db_query("select shops_id, shops_htpasswd_file from " . TABLE_SHOPS . " where shops_htpasswd_file <> ''");
	while ($shops_array = tep_db_fetch_array($shops_query)) {
	  $shops[$shops_array['shops_id']] = $shops_array['shops_htpasswd_file'];
	}
	$passwords = array();
	$users_query = tep_db_query("select u.users_id, u.users_password, ug.users_groups_shops from " . TABLE_USERS . " u, " . TABLE_USERS_GROUPS . " ug where u.users_status = '1' and u.users_groups_id = ug.users_groups_id");
	while ($users = tep_db_fetch_array($users_query)) {
	  $available_shops = array();
	  if (empty($users['users_groups_shops'])) $available_shops = array_keys($shops);
	  else $available_shops = explode(',', $users['users_groups_shops']);
	  reset($shops);
	  while (list($shop_id, $shops_htpasswd_file) = each($shops)) {
		if (in_array($shop_id, $available_shops)) {
		  $passwords[$shops_htpasswd_file] .= $users['users_id'] . ':' . $users['users_password'] . "\r\n";
		}
	  }
	}
	reset($passwords);
	while (list($shops_htpasswd_file, $passwords_string) = each($passwords)) {
	  if ($fp = fopen($shops_htpasswd_file, "w")) {
		fwrite($fp, $passwords_string);
		fclose($fp);
	  }
	}
  }

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'setflag':
		if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['uID'])) {
			tep_db_query("update " . TABLE_USERS . " set users_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where users_id = '" . tep_db_input($HTTP_GET_VARS['uID']) . "'");
		  }
		}
		tep_update_htpasswd_file();

		tep_redirect(tep_href_link(FILENAME_USERS, tep_get_all_get_params(array('action', 'flag'))));
		break;
      case 'insert_group':
      case 'update_group':
        if (isset($HTTP_POST_VARS['groups_id'])) $groups_id = tep_db_prepare_input($HTTP_POST_VARS['groups_id']);
		$groups_shops = array();
		if (is_array($HTTP_POST_VARS['shops'])) {
		  $groups_shops = $HTTP_POST_VARS['shops'];
		}
        $sql_data_array = array('users_groups_name' => tep_db_prepare_input($HTTP_POST_VARS['groups_name']),
								'users_groups_shops' => tep_db_prepare_input(implode(',', $groups_shops)),
								'allow_edit' => tep_db_prepare_input($HTTP_POST_VARS['allow_edit']),
								'allow_delete' => tep_db_prepare_input($HTTP_POST_VARS['allow_delete']));

        if ($action == 'insert_group') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_USERS_GROUPS, $sql_data_array);

          $groups_id = tep_db_insert_id();
        } elseif ($action == 'update_group') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_USERS_GROUPS, $sql_data_array, 'update', "users_groups_id = '" . (int)$groups_id . "'");
        }

		$files = $HTTP_POST_VARS['filenames'];
		$denied_actions = $HTTP_POST_VARS['deny_actions'];
		if (!is_array($files)) $files = array();
		if (!is_array($denied_actions)) $denied_actions = array();
		$files = array_unique($files);
		reset($files);
		tep_db_query("delete from " . TABLE_USERS_GROUPS_TO_CONTENT . " where users_groups_id = '" . (int)$groups_id . "'");
		while (list($i, $filename) = each($files)) {
		  $actions = '';
		  if (is_array($denied_actions[$i])) $actions = implode(',', $denied_actions[$i]);
		  tep_db_query("insert into " . TABLE_USERS_GROUPS_TO_CONTENT . " (users_groups_id, filename, denied_actions) values ('" . (int)$groups_id . "', '" . tep_db_input($filename) . "', '" . tep_db_input($actions) . "')");
		}
		tep_update_htpasswd_file();

        tep_redirect(tep_href_link(FILENAME_USERS, 'gID=' . $groups_id));
        break;
      case 'delete_group_confirm':
        if (isset($HTTP_POST_VARS['groups_id'])) {
          $groups_id = tep_db_prepare_input($HTTP_POST_VARS['groups_id']);

		  tep_db_query("delete from " . TABLE_USERS . " where users_groups_id = '" . (int)$groups_id . "'");
		  tep_db_query("delete from " . TABLE_USERS_GROUPS . " where users_groups_id = '" . (int)$groups_id . "'");
		  tep_db_query("delete from " . TABLE_USERS_GROUPS_TO_CONTENT . " where users_groups_id = '" . (int)$groups_id . "'");
		}
		tep_update_htpasswd_file();

        tep_redirect(tep_href_link(FILENAME_USERS));
        break;
      case 'delete_user_confirm':
        if (isset($HTTP_POST_VARS['users_id'])) {
          $user_id = tep_db_prepare_input($HTTP_POST_VARS['users_id']);
		  tep_db_query("delete from " . TABLE_USERS . " where users_id = '" . $user_id . "'");
        }
		tep_update_htpasswd_file();

        tep_redirect(tep_href_link(FILENAME_USERS, 'gPath=' . $gPath));
        break;
      case 'insert_user':
      case 'update_user':
        $error = false;
        if (isset($HTTP_POST_VARS['users_id'])) $users_id = tep_db_prepare_input($HTTP_POST_VARS['users_id']);
		$users_name = tep_db_prepare_input($HTTP_POST_VARS['users_name']);
		$users_email_address = tep_db_prepare_input($HTTP_POST_VARS['users_email_address']);
		$users_groups_id = tep_db_prepare_input($HTTP_POST_VARS['users_groups_id']);
		if (empty($users_groups_id)) $users_groups_id = tep_db_prepare_input($HTTP_GET_VARS['gPath']);
		$users_password = '';
		if (tep_not_null($HTTP_POST_VARS['users_password'])) {
		  if (file_exists(LOCAL_EXE_HTPASSWD)) {
			$command = sprintf('%s -nb %s "%s"', LOCAL_EXE_HTPASSWD, $users_id, escapeshellcmd($HTTP_POST_VARS['users_password']));
			ob_start();
			echo exec($command);
			$out = ob_get_clean();
			$users_password = trim(str_replace($users_id . ':', '', $out));
		  }
		  if (empty($users_password)) $users_password = crypt($HTTP_POST_VARS['users_password']);
		}

		$user_exists_query = tep_db_query("select count(*) as total from " . TABLE_USERS . " where users_id = '" . $users_id . "'");
		$user_exists = tep_db_fetch_array($user_exists_query);
		if ($action == 'insert_user' && $user_exists['total'] > 0) {
		  $messageStack->add(sprintf(ERROR_USER_EXIST, $users_id), 'error');
		  $error = true;
		} elseif ($HTTP_POST_VARS['users_password'] != $HTTP_POST_VARS['users_password_confirm']) {
		  $messageStack->add(ERROR_PASSWORDS_NOT_EQUALS, 'error');
		  $error = true;
		} elseif ($users_groups_id < 1) {
		  $messageStack->add(ERROR_EMPTY_GROUP, 'error');
		  $error = true;
		} elseif ($action == 'insert_user' && !tep_not_null($users_password)) {
		  $messageStack->add(ERROR_EMPTY_PASSWORD, 'error');
		  $error = true;
		}

		if (!$error) {
		  $sql_data_array = array('users_name' => $users_name,
								  'users_email_address' => $users_email_address,
								  'users_groups_id' => $users_groups_id);
		  if (tep_not_null($users_password)) $sql_data_array['users_password'] = $users_password;

		  if ($action == 'insert_user') {
			$insert_sql_data = array('users_id' => $users_id,
									 'date_added' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			tep_db_perform(TABLE_USERS, $sql_data_array);
		  } elseif ($action == 'update_user') {
			$update_sql_data = array('last_modified' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $update_sql_data);

			tep_db_perform(TABLE_USERS, $sql_data_array, 'update', "users_id = '" . $users_id . "'");
		  }
		  tep_update_htpasswd_file();

		  tep_redirect(tep_href_link(FILENAME_USERS, 'gPath=' . $users_groups_id . '&uID=' . $users_id));
		  break;
		} else {
		  $action = ($action=='insert_user' ? 'new_user' : 'edit_user');
		}
    }
  }

  $users_groups = array();
  $users_groups[] = array('id' => '', 'text' => TEXT_CHOOSE);
  $groups_query = tep_db_query("select users_groups_id, users_groups_name from " . TABLE_USERS_GROUPS . " order by users_groups_name");
  while ($groups_array = tep_db_fetch_array($groups_query)) {
	$users_groups[] = array('id' => $groups_array['users_groups_id'], 'text' => $groups_array['users_groups_name']);
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
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUPS_USERS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  if ($gPath == 0) {
	$rows = 0;
	$groups_query = tep_db_query("select * from " . TABLE_USERS_GROUPS . " order by users_groups_name");
	while ($groups = tep_db_fetch_array($groups_query)) {
	  $groups_count++;
	  $rows++;

	  if ((!isset($HTTP_GET_VARS['gID']) && !isset($HTTP_GET_VARS['uID']) || (isset($HTTP_GET_VARS['gID']) && ($HTTP_GET_VARS['gID'] == $groups['users_groups_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
		$group_users_query = tep_db_query("select count(*) as users_count from " . TABLE_USERS . " where users_groups_id = '" . $groups['users_groups_id'] . "'");
		$group_users = tep_db_fetch_array($group_users_query);

		$groups['files'] = array();
		$groups['actions'] = array();
		$bcontents_query = tep_db_query("select filename, denied_actions from " . TABLE_USERS_GROUPS_TO_CONTENT . " where users_groups_id = '" . $groups['users_groups_id'] . "' group by filename");
		while ($bcontents = tep_db_fetch_array($bcontents_query)) {
		  $groups['files'][] = $bcontents['filename'];
		  $groups['actions'][$bcontents['filename']] = $bcontents['denied_actions'];
		}
		$groups['shops'] = array();
		if (tep_not_null($groups['users_groups_shops'])) $groups['shops'] = explode(',', $groups['users_groups_shops']);

		$gInfo_array = array_merge($groups, $group_users);
		$gInfo = new objectInfo($gInfo_array);
	  }

	  if (isset($gInfo) && is_object($gInfo) && ($groups['users_groups_id'] == $gInfo->users_groups_id) ) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_USERS, 'gID=' . $groups['users_groups_id'] . '&action=edit_group') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_USERS, 'gID=' . $groups['users_groups_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $groups['users_groups_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;<strong>' . $groups['users_groups_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($gInfo) && is_object($gInfo) && ($groups['users_groups_id'] == $gInfo->users_groups_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $groups['users_groups_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
  } else {
	$users_count = 0;
	$users_query = tep_db_query("select * from " . TABLE_USERS . " where users_groups_id = '" . (int)$HTTP_GET_VARS['gPath'] . "' order by users_name");
	while ($users = tep_db_fetch_array($users_query)) {
	  $users_count++;
	  $rows++;

// Get groups_id for user if search
	  if (isset($HTTP_GET_VARS['search'])) $gID = $users['users_groups_id'];

	  if ( (!isset($HTTP_GET_VARS['uID']) && !isset($HTTP_GET_VARS['gID']) || (isset($HTTP_GET_VARS['uID']) && ($HTTP_GET_VARS['uID'] == $users['users_id']))) && !isset($uInfo) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
		$uInfo = new objectInfo($users);
	  }

	  if (isset($uInfo) && is_object($uInfo) && ($users['users_id'] == $uInfo->users_id) ) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $users['users_id'] . '&action=edit_user') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $users['users_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo $users['users_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($users['users_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_USERS, tep_get_all_get_params(array('action', 'flag', 'uID')) . '&action=setflag&flag=0&uID=' . $users['users_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_USERS, tep_get_all_get_params(array('action', 'flag', 'uID')) . '&action=setflag&flag=1&uID=' . $users['users_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($uInfo) && is_object($uInfo) && ($users['users_id'] == $uInfo->users_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $users['users_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td valign="top" class="smallText"><?php if ($gPath == 0) echo TEXT_GROUPS . '&nbsp;' . $groups_count; else echo TEXT_USERS . '&nbsp;' . $users_count; ?></td>
                    <td align="right" class="smallText"><?php if (empty($action)) echo (($gPath==0) ? '<a href="' . tep_href_link(FILENAME_USERS, 'action=new_group') . '">' . tep_image_button('button_new_section.gif', IMAGE_NEW_SECTION) . '</a>&nbsp;' : '<a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $gPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&action=new_user') . '">' . tep_image_button('button_new_record.gif', IMAGE_NEW_RECORD) . '</a>&nbsp;'); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
	case 'new_user':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_USER . '</strong>');

	  $contents = array('form' => tep_draw_form('newuser', FILENAME_USERS, 'gPath=' . $gPath . '&action=insert_user', 'post'));
	  $contents[] = array('text' => TEXT_NEW_USER_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_USERS_NAME . '<br>' . tep_draw_input_field('users_name', '', 'size="30"'));
	  $contents[] = array('text' => '<br>' . TEXT_USERS_EMAIL_ADDRESS . '<br>' . tep_draw_input_field('users_email_address', '', 'size="30"'));
	  $contents[] = array('text' => '<br>' . TEXT_USERS_LOGIN . '<br>' . tep_draw_input_field('users_id'));
	  $contents[] = array('text' => TEXT_USERS_PASSWORD . '<br>' . tep_draw_password_field('users_password'));
	  $contents[] = array('text' => TEXT_USERS_PASSWORD_CONFIRM . '<br>' . tep_draw_password_field('users_password_confirm'));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'edit_user':
	  $heading[] = array('text' => '<strong>' . $uInfo->users_id . (tep_not_null($uInfo->users_name) ? ' (' . $uInfo->users_name . ')' : '') . '</strong>');

	  $contents = array('form' => tep_draw_form('groups', FILENAME_USERS, 'action=update_user&gPath=' . $gPath . '&uID=' . $uID, 'post') . tep_draw_hidden_field('users_id', $uInfo->users_id));
	  $contents[] = array('text' => TEXT_EDIT_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_USERS_NAME . '<br>' . tep_draw_input_field('users_name', $uInfo->users_name, 'size="30"'));
	  $contents[] = array('text' => '<br>' . TEXT_USERS_EMAIL_ADDRESS . '<br>' . tep_draw_input_field('users_email_address', $uInfo->users_email_address, 'size="30"'));
	  $contents[] = array('text' => '<br>' . TEXT_USERS_NEW_PASSWORD . '<br>' . tep_draw_password_field('users_password'));
	  $contents[] = array('text' => TEXT_USERS_PASSWORD_CONFIRM . '<br>' . tep_draw_password_field('users_password_confirm'));
	  $contents[] = array('text' => '<br>' . TEXT_USERS_GROUP . '<br>' . tep_draw_pull_down_menu('users_groups_id', $users_groups, $gPath));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&users_id=' . $uInfo->users_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'new_group':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_GROUP . '</strong>');

	  $contents = array('form' => tep_draw_form('newgroup', FILENAME_USERS, 'action=insert_group', 'post'));
	  $contents[] = array('text' => TEXT_NEW_GROUP_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_GROUPS_NAME . '<br>' . tep_draw_input_field('groups_name', '', 'size="30"'));

	  $shops_string = '';
	  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " order by sort_order");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		$shops_string .= '<br>' . "\n" . tep_draw_checkbox_field('shops[]', $shops['shops_id']) . ' ' . str_replace('http://', '', str_replace('www.', '', $shops['shops_url']));
	  }
	  $contents[] = array('text' => '<br>' . TEXT_EDIT_GROUPS_SHOPS . $shops_string);

	  $i = 0;
	  $boxes_string = '<table border="0" cellspacing="0" cellpadding="0">' . "\n" .
	  '  <tr valign="top">' . "\n" .
	  '	<td><strong>' . TEXT_ALLOW_FILES . '</strong></td>' .
	  '	<td colspan="2" align="center"><strong>' . 'Запретить операции:' . '</strong></td>' .
	  '  </tr>' . "\n" .
	  '  <tr>' . "\n" .
	  '	<td>&nbsp;</td>' .
	  '	<td align="center">' . 'изм.' . '</td>' .
	  '	<td align="center">' . 'удал.' . '</td>' .
	  '  </tr>' . "\n";
	  reset($blocks_contents);
	  while (list(, $block_content) = each($blocks_contents)) {
		$boxes_string .= '  <tr>' . "\n" .
		'	<td colspan="3"><br><strong>' . $block_content['title'] . '</strong></td>' .
		'  </tr>' . "\n";
		reset($block_content['pages']);
		while (list($filename, $pagetitle) = each($block_content['pages'])) {
		  if (strpos($filename, '?')) $filename = substr($filename, 0, strpos($filename, '?'));
		  $actions = explode(',', $gInfo->actions[$filename]);
		  $boxes_string .= '<td>' . tep_draw_checkbox_field('filenames[' . $i . ']', $filename, false, '', 'onclick="if (this.checked==false) { this.form.elements(\'deny_actions[' . $i . '][0]\').checked = false; this.form.elements(\'deny_actions[' . $i . '][0]\').disabled = true; this.form.elements(\'deny_actions[' . $i . '][1]\').checked = false; this.form.elements(\'deny_actions[' . $i . '][1]\').disabled = true; } else { this.form.elements(\'deny_actions[' . $i . '][0]\').disabled = false; this.form.elements(\'deny_actions[' . $i . '][1]\').disabled = false; }"') . $pagetitle . '</td>' . "\n" .
		  '	<td align="center">' . tep_draw_checkbox_field('deny_actions[' . $i . '][0]', 'edit', false, '', 'title="' . TEXT_DENY_EDIT . '"') . '</td>' . "\n" .
		  '	<td align="center">' . tep_draw_checkbox_field('deny_actions[' . $i . '][1]', 'delete', false, '', 'title="' . TEXT_DENY_DELETE . '"') . '</td>' . "\n" .
		  '  </tr>' . "\n";
		  $i ++;
		}
	  }
	  $boxes_string .= '</table>' . "\n";
	  $contents[] = array('text' => '<br>' . $boxes_string);
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_USERS) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'edit_group':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_GROUP . '</strong>');

	  $contents = array('form' => tep_draw_form('groups', FILENAME_USERS, 'action=update_group', 'post') . tep_draw_hidden_field('groups_id', $gInfo->users_groups_id));
	  $contents[] = array('text' => TEXT_EDIT_INTRO);

	  $contents[] = array('text' => '<br>' . TEXT_EDIT_GROUPS_NAME . '<br>' . tep_draw_input_field('groups_name', $gInfo->users_groups_name, 'size="30"'));

	  $shops_string = '';
	  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " order by sort_order");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		$shops_string .= '<br>' . "\n" . tep_draw_checkbox_field('shops[]', $shops['shops_id'], in_array($shops['shops_id'], $gInfo->shops)) . ' ' . str_replace('http://', '', str_replace('www.', '', $shops['shops_url']));
	  }
	  $contents[] = array('text' => '<br>' . TEXT_EDIT_GROUPS_SHOPS . $shops_string);

	  $i = 0;
	  $boxes_string = '<table border="0" cellspacing="0" cellpadding="0">' . "\n" .
	  '  <tr valign="top">' . "\n" .
	  '	<td><strong>' . TEXT_ALLOW_FILES . '</strong></td>' .
	  '	<td colspan="2" align="center"><strong>' . 'Запретить операции:' . '</strong></td>' .
	  '  </tr>' . "\n" .
	  '  <tr>' . "\n" .
	  '	<td>&nbsp;</td>' .
	  '	<td align="center">' . 'изм.' . '</td>' .
	  '	<td align="center">' . 'удал.' . '</td>' .
	  '  </tr>' . "\n";
	  reset($blocks_contents);
	  while (list(, $block_content) = each($blocks_contents)) {
		$boxes_string .= '  <tr>' . "\n" .
		'	<td colspan="3"><br><strong>' . $block_content['title'] . '</strong></td>' .
		'  </tr>' . "\n";
		reset($block_content['pages']);
		while (list($filename, $pagetitle) = each($block_content['pages'])) {
		  if (strpos($filename, '?')) $filename = substr($filename, 0, strpos($filename, '?'));
		  $actions = explode(',', $gInfo->actions[$filename]);
		  $boxes_string .= '<td>' . tep_draw_checkbox_field('filenames[' . $i . ']', $filename, in_array($filename, $gInfo->files), '', 'onclick="if (this.checked==false) { this.form.elements(\'deny_actions[' . $i . '][0]\').checked = false; this.form.elements(\'deny_actions[' . $i . '][0]\').disabled = true; this.form.elements(\'deny_actions[' . $i . '][1]\').checked = false; this.form.elements(\'deny_actions[' . $i . '][1]\').disabled = true; } else { this.form.elements(\'deny_actions[' . $i . '][0]\').disabled = false; this.form.elements(\'deny_actions[' . $i . '][1]\').disabled = false; }"') . $pagetitle . '</td>' . "\n" .
		  '	<td align="center">' . tep_draw_checkbox_field('deny_actions[' . $i . '][0]', 'edit', in_array('edit', $actions), '', 'title="' . TEXT_DENY_EDIT . '"') . '</td>' . "\n" .
		  '	<td align="center">' . tep_draw_checkbox_field('deny_actions[' . $i . '][1]', 'delete', in_array('delete', $actions), '', 'title="' . TEXT_DENY_DELETE . '"') . '</td>' . "\n" .
		  '  </tr>' . "\n";
		  $i ++;
		}
	  }
	  $boxes_string .= '</table>' . "\n";
	  $contents[] = array('text' => '<br>' . $boxes_string);
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $gInfo->users_groups_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'delete_group':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_GROUP . '</strong>');

	  $contents = array('form' => tep_draw_form('groups', FILENAME_USERS, 'action=delete_group_confirm') . tep_draw_hidden_field('groups_id', $gInfo->users_groups_id));
	  $contents[] = array('text' => TEXT_DELETE_GROUP_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $gInfo->users_groups_name . '</strong>');
	  if ($gInfo->users_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_USERS, $gInfo->users_count));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $gID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'delete_user':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_USER . '</strong>');

	  $contents = array('form' => tep_draw_form('users', FILENAME_USERS, 'action=delete_user_confirm&gPath=' . $gPath) . tep_draw_hidden_field('users_id', $uInfo->users_id));
	  $contents[] = array('text' => TEXT_DELETE_USER_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $uInfo->users_name . '</strong>');
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $uInfo->users_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	default:
	  if ($rows > 0) {
		if (isset($gInfo) && is_object($gInfo)) { // group info box contents
		  $heading[] = array('text' => '<strong>' . $gInfo->users_groups_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $gInfo->users_groups_id . '&action=edit_group') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_USERS, 'gID=' . $gInfo->users_groups_id . '&action=delete_group') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($gInfo->date_added));
		  if (tep_not_null($gInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($gInfo->last_modified));
		} elseif (isset($uInfo) && is_object($uInfo)) { // user info box contents
		  $heading[] = array('text' => '<strong>' . $uInfo->users_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $uInfo->users_id . '&action=edit_user') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_USERS, 'gPath=' . $gPath . '&uID=' . $uInfo->users_id . '&action=delete_user') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($uInfo->date_added));
		  if (tep_not_null($uInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($uInfo->last_modified));
		}
	  } else { // create group/user info
		$heading[] = array('text' => '<strong>' . EMPTY_GROUP . '</strong>');

		$contents[] = array('text' => TEXT_NO_CHILD_GROUPS_OR_USERS);
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