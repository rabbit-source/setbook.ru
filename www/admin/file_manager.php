<?php
  require('includes/application_top.php');

  if (!function_exists('tep_get_file_permissions')) {
	function tep_get_file_permissions($mode) {
// determine type
	  if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
		$type = 's';
	  } elseif ( ($mode & 0x4000) == 0x4000) { // directory
		$type = 'd';
	  } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
		$type = 'l';
	  } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
		$type = '-';
	  } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
		$type = 'b';
	  } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
		$type = 'c';
	  } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
		$type = 'p';
	  } else { // unknown
		$type = '?';
	  }

// determine permissions
	  $owner['read']    = ($mode & 00400) ? 'r' : '-';
	  $owner['write']   = ($mode & 00200) ? 'w' : '-';
	  $owner['execute'] = ($mode & 00100) ? 'x' : '-';
	  $group['read']    = ($mode & 00040) ? 'r' : '-';
	  $group['write']   = ($mode & 00020) ? 'w' : '-';
	  $group['execute'] = ($mode & 00010) ? 'x' : '-';
	  $world['read']    = ($mode & 00004) ? 'r' : '-';
	  $world['write']   = ($mode & 00002) ? 'w' : '-';
	  $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
	  if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
	  if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
	  if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

	  return $type .
			 $owner['read'] . $owner['write'] . $owner['execute'] .
			 $group['read'] . $group['write'] . $group['execute'] .
			 $world['read'] . $world['write'] . $world['execute'];
	}
  }

  if (!tep_session_is_registered('current_path')) {
    $current_path = 'images';
    tep_session_register('current_path');
  }

  $real_path = DIR_FS_CATALOG . $current_path;

  if (substr($current_path, -1)=='/') $current_path = substr($current_path, 0, -1);

  if (isset($HTTP_GET_VARS['goto'])) {
    $current_path = $HTTP_GET_VARS['goto'];
    tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
  }

  while (substr($current_path, -1)=='/') $current_path = substr($current_path, 0, -1);

  if (strstr($current_path, '..')) $current_path = 'images';

  if (!is_dir($real_path)) $current_path = 'images';

  if (strpos(DIR_FS_CATALOG . 'images', $real_path)===false) $current_path = 'images';

  $real_path = DIR_FS_CATALOG . $current_path;

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'reset':
        tep_session_unregister('current_path');
        tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
        break;
      case 'deleteconfirm':
        if (strstr($HTTP_GET_VARS['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

        tep_remove($real_path . '/' . $HTTP_GET_VARS['info']);
        if (!$tep_remove_error) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
        break;
      case 'insert':
        if (mkdir($real_path . '/' . $HTTP_POST_VARS['folder_name'], 0777)) {
          tep_redirect(tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($HTTP_POST_VARS['folder_name'])));
        }
        break;
      case 'save':
        if ($fp = fopen($real_path . '/' . $HTTP_POST_VARS['filename'], 'w+')) {
          fputs($fp, stripslashes($HTTP_POST_VARS['file_contents']));
          fclose($fp);
          tep_redirect(tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($HTTP_POST_VARS['filename'])));
        }
        break;
      case 'processuploads':
        for ($i=1; $i<6; $i++) {
          if (isset($GLOBALS['file_' . $i]) && tep_not_null($GLOBALS['file_' . $i])) {
            new upload('file_' . $i, $real_path);
          }
        }

        tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
        break;
      case 'download':
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . urldecode($HTTP_GET_VARS['filename']));
        readfile($real_path . '/' . urldecode($HTTP_GET_VARS['filename']));
        exit;
        break;
      case 'upload':
      case 'new_folder':
      case 'new_file':
        $directory_writeable = true;
        if (!is_writeable($real_path)) {
          $directory_writeable = false;
          $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_WRITEABLE, $current_path), 'error');
        }
        break;
      case 'edit':
        if (strstr($HTTP_GET_VARS['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

        $file_writeable = true;
        if (!is_writeable($real_path . '/' . $HTTP_GET_VARS['info'])) {
          $file_writeable = false;
          $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $current_path . '/' . $HTTP_GET_VARS['info']), 'error');
        }
        break;
      case 'delete':
        if (strstr($HTTP_GET_VARS['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
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
<?php
  if ( (($action == 'new_file') && ($directory_writeable == true)) || ($action == 'edit') ) {
    if (isset($HTTP_GET_VARS['info']) && strstr($HTTP_GET_VARS['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

    if (!isset($file_writeable)) $file_writeable = true;
    $file_contents = '';
    if ($action == 'new_file') {
      $filename_input_field = tep_draw_input_field('filename');
    } elseif ($action == 'edit') {
      if ($file_array = file($real_path . '/' . $HTTP_GET_VARS['info'])) {
        $file_contents = htmlspecialchars(implode('', $file_array));
      }
      $filename_input_field = $HTTP_GET_VARS['info'] . tep_draw_hidden_field('filename', $HTTP_GET_VARS['info']);
    }
?>
      <tr><?php echo tep_draw_form('new_file', FILENAME_FILE_MANAGER, 'action=save'); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_FILE_NAME; ?></td>
            <td class="main"><?php echo $filename_input_field; ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_FILE_CONTENTS; ?></td>
            <td class="main"><?php echo tep_draw_textarea_field('file_contents', 'soft', '80', '20', $file_contents, (($file_writeable) ? '' : 'readonly')); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td align="right" class="main" colspan="2"><?php if ($file_writeable == true) echo tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;'; echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($HTTP_GET_VARS['info']) ? 'info=' . urlencode($HTTP_GET_VARS['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
    $showuser = (function_exists('posix_getpwuid') ? true : false);
    $contents = array();
    $dir = dir($real_path);
    while ($file = $dir->read()) {
      if ( ($file != '.') && ($file != 'CVS') && ($file != '.htaccess') && ( ($file != '..') || ($real_path != DIR_FS_CATALOG) ) ) {
        $file_size = number_format(filesize($real_path . '/' . $file)) . ' bytes';

        $permissions = tep_get_file_permissions(fileperms($real_path . '/' . $file));
        if ($showuser) {
          $user = @posix_getpwuid(fileowner($real_path . '/' . $file));
          $group = @posix_getgrgid(filegroup($real_path . '/' . $file));
        } else {
          $user = $group = array();
        }

        $contents[] = array('name' => $file,
                            'is_dir' => is_dir($real_path . '/' . $file),
                            'last_modified' => strftime(DATE_TIME_FORMAT, filemtime($real_path . '/' . $file)),
                            'size' => $file_size,
                            'permissions' => $permissions,
                            'user' => $user['name'],
                            'group' => $group['name']);
      }
    }

    function tep_cmp($a, $b) {
      return strcmp( ($a['is_dir'] ? 'D' : 'F') . $a['name'], ($b['is_dir'] ? 'D' : 'F') . $b['name']);
    }
    usort($contents, 'tep_cmp');
?>

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILENAME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PERMISSIONS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_USER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
	if ($current_path == 'images' && $contents[$i]['name'] == '..') {
	} else {
	  if ((!isset($HTTP_GET_VARS['info']) || (isset($HTTP_GET_VARS['info']) && ($HTTP_GET_VARS['info'] == $contents[$i]['name']))) && !isset($fInfo) && ($action != 'upload') && ($action != 'new_folder')) {
		$fInfo = new objectInfo($contents[$i]);
	  }

	  if ($contents[$i]['name'] == '..') {
		$goto_link = substr($current_path, 0, strrpos($current_path, '/'));
	  } else {
		$goto_link = $current_path . '/' . $contents[$i]['name'];
	  }

	  if (isset($fInfo) && is_object($fInfo) && ($contents[$i]['name'] == $fInfo->name)) {
		if ($fInfo->is_dir) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
		  $onclick_link = 'goto=' . $goto_link;
		} else {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
		  if ($size = @getimagesize($real_path . '/' . $fInfo->name)) $onclick_link = 'info=' . urlencode($fInfo->name);
		  else $onclick_link = 'info=' . urlencode($fInfo->name) . '&action=edit';
		}
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
		$onclick_link = 'info=' . urlencode($contents[$i]['name']);
	  }

	  if ($contents[$i]['is_dir']) {
		if ($contents[$i]['name'] == '..') {
		  $icon = tep_image(DIR_WS_ICONS . 'previous_level.gif', ICON_PREVIOUS_LEVEL);
		} else {
		  $icon = tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"');
		}
		$link = tep_href_link(FILENAME_FILE_MANAGER, 'goto=' . $goto_link);
	  } else {
		$icon = tep_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD);
		$link = tep_href_link(FILENAME_FILE_MANAGER, 'action=download&filename=' . urlencode($contents[$i]['name']));
	  }
?>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo '<a href="' . $link . '">' . $icon . '</a>&nbsp;' . $contents[$i]['name']; ?></td>
                <td class="dataTableContent" align="right" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo ($contents[$i]['is_dir'] ? '&nbsp;' : $contents[$i]['size']); ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><tt><?php echo $contents[$i]['permissions']; ?></tt></td>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['user']; ?></td>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['group']; ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['last_modified']; ?></td>
                <td class="dataTableContent" align="right"><?php if ($contents[$i]['name'] != '..') echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($contents[$i]['name']) . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'delete.gif', ICON_DELETE) . '</a>&nbsp;'; if (isset($fInfo) && is_object($fInfo) && ($fInfo->name == $contents[$i]['name'])) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($contents[$i]['name'])) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
  }
?>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr valign="top">
                    <td class="smallText"><?php echo ($current_path!='images' ? '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'action=reset') . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>' : '&nbsp;'); ?></td>
                    <td class="smallText" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($HTTP_GET_VARS['info']) ? 'info=' . urlencode($HTTP_GET_VARS['info']) . '&' : '') . 'action=upload') . '">' . tep_image_button('button_upload.gif', IMAGE_UPLOAD) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($HTTP_GET_VARS['info']) ? 'info=' . urlencode($HTTP_GET_VARS['info']) . '&' : '') . 'action=new_folder') . '">' . tep_image_button('button_new_folder.gif', IMAGE_NEW_FOLDER) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<strong>' . $fInfo->name . '</strong>');

        $contents = array('form' => tep_draw_form('file', FILENAME_FILE_MANAGER, 'info=' . urlencode($fInfo->name) . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_DELETE_INTRO);
        $contents[] = array('text' => '<br><strong>' . $fInfo->name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (tep_not_null($fInfo->name) ? 'info=' . urlencode($fInfo->name) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'new_folder':
        $heading[] = array('text' => '<strong>' . TEXT_NEW_FOLDER . '</strong>');

        $contents = array('form' => tep_draw_form('folder', FILENAME_FILE_MANAGER, 'action=insert'));
        $contents[] = array('text' => TEXT_NEW_FOLDER_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_FILE_NAME . '<br>' . tep_draw_input_field('folder_name'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . (($directory_writeable == true) ? tep_image_submit('button_save.gif', IMAGE_SAVE) : '') . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($HTTP_GET_VARS['info']) ? 'info=' . urlencode($HTTP_GET_VARS['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'upload':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_UPLOAD . '</strong>');

        $contents = array('form' => tep_draw_form('file', FILENAME_FILE_MANAGER, 'action=processuploads', 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_UPLOAD_INTRO);

        $file_upload = '';
        for ($i=1; $i<6; $i++) $file_upload .= tep_draw_file_field('file_' . $i) . '<br>';

        $contents[] = array('text' => '<br>' . $file_upload);
        $contents[] = array('align' => 'center', 'text' => '<br>' . (($directory_writeable == true) ? tep_image_submit('button_upload.gif', IMAGE_UPLOAD) : '') . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($HTTP_GET_VARS['info']) ? 'info=' . urlencode($HTTP_GET_VARS['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($fInfo) && is_object($fInfo)) {
          $heading[] = array('text' => '<strong>' . $fInfo->name . '</strong>');

          if (!$fInfo->is_dir) {
			$size = @getimagesize($real_path . '/' . $fInfo->name);
			if ($size[2]==1 || $size[2]==2 || $size[2]==3) $contents[] = array('align' => 'center', 'text' => tep_image(tep_catalog_href_link($current_path . '/' . $fInfo->name), $fInfo->name, ($size[0]<150 ? $size[0] : 150)));
			elseif ($size) echo '';
			else $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($fInfo->name) . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
		  }
          $contents[] = array('text' => '<br>' . TEXT_FILE_NAME . ' <strong>' . $fInfo->name . '</strong>');
          if (!$fInfo->is_dir) $contents[] = array('text' => '<br>' . TEXT_FILE_SIZE . ' <strong>' . $fInfo->size . '</strong>');
          $contents[] = array('text' => '<br>' . TEXT_LAST_MODIFIED . ' ' . $fInfo->last_modified);
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
