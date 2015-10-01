<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'setflag':
		if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['sID'])) {
			tep_db_query("update " . TABLE_SUBJECTS . " set status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where subjects_id = '" . tep_db_input($HTTP_GET_VARS['sID']) . "'");
		  }
		}

		tep_redirect(tep_href_link(FILENAME_SUBJECTS, tep_get_all_get_params(array('action', 'flag'))));
		break;
      case 'insert':
      case 'save':
        if (isset($HTTP_GET_VARS['sID'])) $subject_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);

		$subject_sort_order = $HTTP_POST_VARS['sort_order'];
		$subject_email = $HTTP_POST_VARS['subjects_email'];
		$subject_status = $HTTP_POST_VARS['subjects_status'];

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $subject_name_array = $HTTP_POST_VARS['subjects_name'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('subjects_name' => tep_db_prepare_input($subject_name_array[$language_id]));

          if ($action == 'insert') {
            if (empty($subject_id)) {
              $next_id_query = tep_db_query("select max(subjects_id) as subjects_id from " . TABLE_SUBJECTS . "");
              $next_id = tep_db_fetch_array($next_id_query);
              $subject_id = $next_id['subjects_id'] + 1;
            }

            $insert_sql_data = array('subjects_id' => $subject_id,
                                     'language_id' => $language_id,
									 'date_added' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_SUBJECTS, $sql_data_array);
          } elseif ($action == 'save') {
            $update_sql_data = array('last_modified' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            tep_db_perform(TABLE_SUBJECTS, $sql_data_array, 'update', "subjects_id = '" . (int)$subject_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }
		tep_db_query("update " . TABLE_SUBJECTS . " set subjects_email = '" . tep_db_input(tep_db_prepare_input($subject_email)) . "', sort_order = '" . tep_db_input(tep_db_prepare_input($subject_sort_order)) . "', status = '" . tep_db_input(tep_db_prepare_input($subject_status)) . "' where subjects_id = '" . (int)$subject_id . "'");

        tep_redirect(tep_href_link(FILENAME_SUBJECTS, 'sID=' . $subject_id));
        break;
      case 'deleteconfirm':
        $sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        tep_db_query("delete from " . TABLE_SUBJECTS . " where subjects_id = '" . tep_db_input($sID) . "'");

        tep_redirect(tep_href_link(FILENAME_SUBJECTS));
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
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUBJECT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
  $subjects_query_raw = "select * from " . TABLE_SUBJECTS . " where language_id = '" . (int)$languages_id . "' order by sort_order";
  $subjects_query = tep_db_query($subjects_query_raw);
  while ($subjects = tep_db_fetch_array($subjects_query)) {
    if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $subjects['subjects_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
      $sInfo = new objectInfo($subjects);
    }

    if (isset($sInfo) && is_object($sInfo) && ($subjects['subjects_id'] == $sInfo->subjects_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $subjects['subjects_id']) . '\'">' . "\n";
    }

    echo '                <td class="dataTableContent">[' . $subjects['sort_order'] . '] ' . $subjects['subjects_name'] . '</td>' . "\n";
?>
                <td class="dataTableContent" align="center">
<?php
		if ($subjects['status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SUBJECTS, tep_get_all_get_params(array('action', 'sID')) . '&action=setflag&flag=0&sID=' . $subjects['subjects_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_SUBJECTS, tep_get_all_get_params(array('action', 'sID')) . '&action=setflag&flag=1&sID=' . $subjects['subjects_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($subjects['subjects_id'] == $sInfo->subjects_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $subjects['subjects_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_SUBJECTS, 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_SUBJECT . '</strong>');

      $contents = array('form' => tep_draw_form('status', FILENAME_SUBJECTS, 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

      $subject_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $subject_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('subjects_name[' . $languages[$i]['id'] . ']', '', 'size="30"');
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_NAME . $subject_inputs_string);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_EMAIL . '<br>' . tep_draw_input_field('subjects_email', '', 'size="34"') . '<br>' . TEXT_INFO_SUBJECT_EMAIL_TEXT);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', '0', 'size="3"'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('subjects_status', '1', true) . ' ' . TEXT_INFO_SUBJECT_STATUS);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_SUBJECTS) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SUBJECT . '</strong>');

      $contents = array('form' => tep_draw_form('subject', FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id  . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $subject_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $subject_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('subjects_name[' . $languages[$i]['id'] . ']', tep_get_subject_name($sInfo->subjects_id, $languages[$i]['id']), 'size="30"');
      }
      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_NAME . $subject_inputs_string);

      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_EMAIL . '<br>' . tep_draw_input_field('subjects_email', $sInfo->subjects_email, 'size="34"') . '<br>' . TEXT_INFO_SUBJECT_EMAIL_TEXT);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $sInfo->sort_order, 'size="3"'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('subjects_status', '1', $sInfo->status=='1') . ' ' . TEXT_INFO_SUBJECT_STATUS);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SUBJECT . '</strong>');

      $contents = array('form' => tep_draw_form('status', FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $sInfo->subjects_name . '</strong>');
      if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($sInfo) && is_object($sInfo)) {
        $heading[] = array('text' => '<strong>' . $sInfo->subjects_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SUBJECTS, 'sID=' . $sInfo->subjects_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

        $subjects_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $subjects_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_get_subject_name($sInfo->subjects_id, $languages[$i]['id']);
        }
        $contents[] = array('text' => $subjects_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_INFO_SUBJECT_EMAIL . ' ' . (tep_not_null($sInfo->subjects_email) ? $sInfo->subjects_email : TEXT_INFO_SUBJECT_EMAIL_DEFAULT));
		$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added));
		if (tep_not_null($sInfo->last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified));
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