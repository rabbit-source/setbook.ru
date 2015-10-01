<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $name = tep_db_prepare_input($HTTP_POST_VARS['name']);
        $code = tep_db_prepare_input($HTTP_POST_VARS['code']);
        $image = tep_db_prepare_input($HTTP_POST_VARS['image']);
        $directory = tep_db_prepare_input($HTTP_POST_VARS['directory']);
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

        tep_db_query("insert into " . TABLE_LANGUAGES . " (name, code, image, directory, sort_order) values ('" . tep_db_input($name) . "', '" . tep_db_input($code) . "', '" . tep_db_input($image) . "', '" . tep_db_input($directory) . "', '" . tep_db_input($sort_order) . "')");
        $insert_id = tep_db_insert_id();

		$result = tep_db_list_tables(DB_DATABASE);
		for ($i = 0; $i < tep_db_num_rows($result); $i++) {
		  $tablename = tep_db_tablename($result, $i);
		  if ($tablename!=TABLE_LANGUAGES) {
			if (tep_db_field_exists($tablename, 'language_id')) {
			  $all_rows_query = tep_db_query("select * from " . tep_db_input($tablename) . " where language_id = '" . (int)$languages_id . "'");
			  while ($all_rows = tep_db_fetch_array($all_rows_query)) {
				$sql_data_array = array();
				reset($all_rows);
				while (list($field_name, $field_value) = each($all_rows)) {
				  if ($field_name=='language_id') {
					$sql_data_array[$field_name] = $insert_id;
				  } else {
					$sql_data_array[$field_name] = $field_value;
				  }
				}
				tep_db_perform($tablename, $sql_data_array);
			  }
			} elseif (tep_db_field_exists($tablename, 'languages_id')) {
			  $all_rows_query = tep_db_query("select * from " . tep_db_input($tablename) . " where languages_id = '" . (int)$languages_id . "'");
			  while ($all_rows = tep_db_fetch_array($all_rows_query)) {
				$sql_data_array = array();
				reset($all_rows);
				while (list($field_name, $field_value) = each($all_rows)) {
				  if ($field_name=='languages_id') {
					$sql_data_array[$field_name] = $insert_id;
				  } else {
					$sql_data_array[$field_name] = $field_value;
				  }
				}
				tep_db_perform($tablename, $sql_data_array);
			  }
			}
		  }
		}

		if (isset($HTTP_POST_VARS['default']) && $HTTP_POST_VARS['default']=='on') {
		  tep_db_query("update " . TABLE_LANGUAGES . " set default_status = '0'");
		  tep_db_query("update " . TABLE_LANGUAGES . " set default_status = '1' where languages_id = '" . (int)$insert_id . "'");
		}

		if ($upload = new upload('', '', '777', array('gif', 'jpg', 'jpeg', 'png'))) {
		  list(, , $image_type) = getimagesize($image);
		  if ($image_type=='3') $ext = 'png';
		  elseif ($image_type=='2') $ext = 'jpg';
		  else $ext = 'gif';
		  $upload->filename = 'languages/' . $code . '.' . $ext;
          if ($upload->upload('image', DIR_FS_CATALOG_IMAGES)) {
			tep_db_query("update " . TABLE_LANGUAGES . " set image = '" . tep_db_input($upload->filename) . "' where languages_id = '" . (int)$insert_id . "'");
		  }
        }

		$messageStack->add_session(SUCCESS_LANGUAGE_ADDED, 'success');

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'lID=' . $insert_id));
        break;
      case 'save':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);
        $name = tep_db_prepare_input($HTTP_POST_VARS['name']);
        $code = tep_db_prepare_input($HTTP_POST_VARS['code']);
        $image = tep_db_prepare_input($HTTP_POST_VARS['image']);
        $directory = tep_db_prepare_input($HTTP_POST_VARS['directory']);
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

        tep_db_query("update " . TABLE_LANGUAGES . " set name = '" . tep_db_input($name) . "', code = '" . tep_db_input($code) . "', directory = '" . tep_db_input($directory) . "', sort_order = '" . tep_db_input($sort_order) . "' where languages_id = '" . (int)$lID . "'");
		if (isset($HTTP_POST_VARS['default']) && $HTTP_POST_VARS['default']=='on') {
		  tep_db_query("update " . TABLE_LANGUAGES . " set default_status = '0'");
		  tep_db_query("update " . TABLE_LANGUAGES . " set default_status = '1' where languages_id = '" . (int)$lID . "'");
		}

		if ($upload = new upload('', '', '777', array('gif', 'jpg', 'jpeg', 'png'))) {
		  list(, , $image_type) = getimagesize($image);
		  if ($image_type=='3') $ext = 'png';
		  elseif ($image_type=='2') $ext = 'jpg';
		  else $ext = 'gif';
		  $upload->filename = 'languages/' . $code . '.' . $ext;
          if ($upload->upload('image', DIR_FS_CATALOG_IMAGES)) {
			$prev_file_query = tep_db_query("select image from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['image']) && $prev_file['image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['image']);
			}
			tep_db_query("update " . TABLE_LANGUAGES . " set image = '" . tep_db_input($upload->filename) . "' where languages_id = '" . (int)$lID . "'");
		  }
        }

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $HTTP_GET_VARS['lID']));
        break;
      case 'deleteconfirm':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);

		$result = tep_db_list_tables(DB_DATABASE);
		for ($i = 0; $i < tep_db_num_rows($result); $i++) {
		  $tablename = tep_db_tablename($result, $i);
		  if (tep_db_field_exists($tablename, 'language_id')) {
			tep_db_query("delete from " . tep_db_input($tablename) . " where language_id = '" . (int)$lID . "'");
		  } elseif (tep_db_field_exists($tablename, 'languages_id')) {
			tep_db_query("delete from " . tep_db_input($tablename) . " where languages_id = '" . (int)$lID . "'");
		  }
		}

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'delete':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);

        $lng_query = tep_db_query("select code, default_status from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        $lng = tep_db_fetch_array($lng_query);

        $remove_language = true;
        if ($lng['default_status'] == '1') {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        break;
    }
  }

  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'languages/')) {
	$messageStack->add(WARNING_IMAGES_LANGUAGES_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_IMAGES . 'languages/')) {
	$messageStack->add(WARNING_IMAGES_LANGUAGES_DIRECTORY_NOT_WRITEABLE, 'warning');
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
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $languages_query_raw = "select languages_id, name, code, image, directory, sort_order, default_status from " . TABLE_LANGUAGES . " order by sort_order";
  $languages_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
  $languages_query = tep_db_query($languages_query_raw);

  while ($languages = tep_db_fetch_array($languages_query)) {
    if ((!isset($HTTP_GET_VARS['lID']) || (isset($HTTP_GET_VARS['lID']) && ($HTTP_GET_VARS['lID'] == $languages['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages);
    }

    if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $languages['languages_id']) . '\'">' . "\n";
    }

    if ($languages['default_status']=='1') {
      echo '                <td class="dataTableContent"><strong>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $languages['name'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $languages['code']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $languages['languages_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</strong>');

      $contents = array('form' => tep_draw_form('languages', FILENAME_LANGUAGES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . tep_draw_input_field('name'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . tep_draw_input_field('code'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . tep_draw_file_field('image'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default', 'on') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $HTTP_GET_VARS['lID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</strong>');

      $contents = array('form' => tep_draw_form('languages', FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . tep_draw_input_field('name', $lInfo->name));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . tep_draw_input_field('code', $lInfo->code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . tep_draw_file_field('image') . (tep_not_null($lInfo->image) ? '<br>' . DIR_WS_CATALOG_IMAGES . '<strong>' . $lInfo->image . '</strong>' : ''));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $lInfo->sort_order, 'size="3"'));
      if ($lInfo->default_status=='0') $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default', 'on') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</strong>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $lInfo->name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . (($remove_language) ? '<a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' : '') . ' <a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<strong>' . $lInfo->name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $lInfo->image, $lInfo->name));
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
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