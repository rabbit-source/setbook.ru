<?php
  require('includes/application_top.php');

  function tep_get_templates_info($templates_id, $language_id, $field = 'templates_name') {
    $templates_query = tep_db_query("select " . $field . " from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "' and language_id = '" . (int)$language_id . "'");
    $templates = tep_db_fetch_array($templates_query);

    return $templates[$field];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'update':
        if (isset($HTTP_GET_VARS['tID'])) {
		  $templates_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);
		} else {
		  $max_id_query = tep_db_query("select max(templates_id) as new_id from " . TABLE_TEMPLATES . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $templates_id = (int)$max_id['new_id'] + 1;
		}

		$default_status = tep_db_prepare_input($HTTP_POST_VARS['default_status']);
		$sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
		if ($default_status == '1') {
		  tep_db_query("update " . TABLE_TEMPLATES . " set default_status = '0'");
		} else {
		  $default_status_check_query = tep_db_query("select count(*) as total from " . TABLE_TEMPLATES  . " where default_status = '1' and templates_id <> '" . (int)$templates_id . "'");
		  $default_status_check = tep_db_fetch_array($default_status_check_query);
		  if ($default_status_check['total'] < 1) $default_status = '1';
		}

		$blocks = $HTTP_POST_VARS['blocks'];
		if (!is_array($blocks)) $blocks = array();
		tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS . " where templates_id = '" . (int)$templates_id . "'");
		reset($blocks);
		while (list($blocks_id) = each($blocks)) {
		  tep_db_query("insert into " . TABLE_TEMPLATES_TO_BLOCKS . " (blocks_id, templates_id) values ('" . (int)$blocks_id . "', '" . (int)$templates_id . "')");
		}

		$types = $HTTP_POST_VARS['types'];
		if (!is_array($types)) $types = array();
		tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where templates_id = '" . (int)$templates_id . "'");
		reset($types);
		while (list($blocks_types_id) = each($types)) {
		  tep_db_query("insert into " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " (blocks_types_id, templates_id) values ('" . (int)$blocks_types_id . "', '" . (int)$templates_id . "')");
		}

		$languages = tep_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $templates_name_array = $HTTP_POST_VARS['templates_name'];
		  $templates_description_array = $HTTP_POST_VARS['templates_description'];

		  $language_id = $languages[$i]['id'];

		  $sql_data_array = array('templates_name' => tep_db_prepare_input($templates_name_array[$language_id]),
		  						  'templates_description' => tep_db_prepare_input($templates_description_array[$language_id]),
								  'default_status' => $default_status,
								  'sort_order' => $sort_order);

		  if ($action == 'insert') {
			$insert_sql_data = array('date_added' => 'now()',
									 'templates_id' => $templates_id,
            						 'language_id' => $languages[$i]['id']);
			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			tep_db_perform(TABLE_TEMPLATES, $sql_data_array);
		  } elseif ($action == 'update') {
			$update_sql_data = array('last_modified' => 'now()');
			$sql_data_array = array_merge($sql_data_array, $update_sql_data);
			tep_db_perform(TABLE_TEMPLATES, $sql_data_array, 'update', "templates_id = '" . (int)$templates_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		  }
		}

		if ($upload = new upload('', '', '777', array('php'))) {
		  $upload->filename = 'template_' . $templates_id . '.html';
          if ($upload->upload('templates_filename', DIR_FS_CATALOG_TEMPLATES)) {
			$prev_file_query = tep_db_query("select templates_filename from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['templates_filename']) && $prev_file['templates_filename']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_TEMPLATES . $prev_file['templates_filename']);
			}
			tep_db_query("update " . TABLE_TEMPLATES . " set templates_filename = '" . tep_db_input($upload->filename) . "' where templates_id = '" . (int)$templates_id . "'");
		  } elseif (isset($HTTP_POST_VARS['templates_filename_contents'])) {
			if (get_magic_quotes_gpc()) {
			  $templates_filename_contents = stripslashes(trim($HTTP_POST_VARS['templates_filename_contents']));
			} else {
			  $templates_filename_contents = str_replace('\"', '"', trim($HTTP_POST_VARS['templates_filename_contents']));
			}
			$prev_file_query = tep_db_query("select templates_filename from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['templates_filename'])) {
			  @unlink(DIR_FS_CATALOG_TEMPLATES . $prev_file['templates_filename']);
			} else {
			  $prev_file['templates_filename'] = 'template_' . $templates_id . '.html';
			}
			if (tep_not_null($templates_filename_contents)) {
			  $fp = fopen(DIR_FS_CATALOG_TEMPLATES . $prev_file['templates_filename'], 'w');
			  fwrite($fp, $templates_filename_contents);
			  fclose($fp);
			  tep_db_query("update " . TABLE_TEMPLATES . " set templates_filename = '" . tep_db_input($prev_file['templates_filename']) . "' where templates_id = '" . (int)$templates_id . "'");
			}
		  }
        }

        tep_redirect(tep_href_link(FILENAME_TEMPLATES, 'tID=' . $templates_id));
        break;
      case 'deleteconfirm':
        $templates_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

		$file_query = tep_db_query("select templates_filename from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "' limit 1");
		$file = tep_db_fetch_array($file_query);
		if (tep_not_null($file['templates_filename'])) {
		  @unlink(DIR_FS_CATALOG_TEMPLATES . $file['templates_filename']);
		}

        tep_db_query("delete from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "'");
		tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS . " where templates_id = '" . (int)$templates_id . "'");
		tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where templates_id = '" . (int)$templates_id . "'");

        tep_redirect(tep_href_link(FILENAME_TEMPLATES));
        break;
    }
  }

  if (!is_dir(DIR_FS_CATALOG_TEMPLATES)) {
	$messageStack->add(WARNING_INCLUDES_TEMPLATES_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_TEMPLATES)) {
	$messageStack->add(WARNING_INCLUDES_TEMPLATES_DIRECTORY_NOT_WRITEABLE, 'warning');
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
    <td width="100%" valign="top"><?php
  if ($action == 'new' || $action == 'edit') {
    $parameters = array();
	$query = tep_db_query("describe " . TABLE_TEMPLATES);
	while ($row = tep_db_fetch_array($query)) {
	  $parameters[$row['Field']] == (tep_not_null($row['Default']) ? $row['Default'] : '');
	}

    $tInfo = new objectInfo($parameters);
	$tInfo->blocks = array();
	$tInfo->blocks_types = array();

    if (isset($HTTP_GET_VARS['tID']) && empty($HTTP_POST_VARS)) {
	  $template_query = tep_db_query("select * from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$HTTP_GET_VARS['tID'] . "' and language_id = '" . (int)$languages_id . "'");
      $template = tep_db_fetch_array($template_query);

	  $template['blocks'] = array();
	  $blocks_query = tep_db_query("select blocks_id from " . TABLE_TEMPLATES_TO_BLOCKS . " where templates_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
	  while ($blocks = tep_db_fetch_array($blocks_query)) {
		$template['blocks'][] = $blocks['blocks_id'];
	  }

	  $template['blocks_types'] = array();
	  $types_query = tep_db_query("select blocks_types_id from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where templates_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
	  while ($types = tep_db_fetch_array($types_query)) {
		$template['blocks_types'][] = $types['blocks_types_id'];
	  }

      $tInfo->objectInfo($template);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $tInfo->objectInfo($HTTP_POST_VARS);
      $templates_name = array_map("stripslashes", $HTTP_POST_VARS['templates_name']);
      $templates_description = array_map("stripslashes", $HTTP_POST_VARS['templates_description']);
    }
	if (!is_array($tInfo->templates)) $tInfo->templates = array();

    $languages = tep_get_languages();

	$form_action = (isset($HTTP_GET_VARS['tID'])) ? 'update' : 'insert';

	echo tep_draw_form('templates', FILENAME_TEMPLATES, 'action=' . $form_action . (isset($HTTP_GET_VARS['tID']) ? '&tID=' . $HTTP_GET_VARS['tID'] : ''), 'post', 'enctype="multipart/form-data"');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo (isset($HTTP_GET_VARS['tID'])) ? sprintf(TEXT_INFO_HEADING_EDIT_TEMPLATE, $tInfo->templates_name) : TEXT_INFO_HEADING_NEW_TEMPLATE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="1">
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_TEMPLATE_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('templates_name[' . $languages[$i]['id'] . ']', tep_get_templates_info($tInfo->templates_id, $languages[$i]['id']), 'size="35"'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_TEMPLATE_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('templates_description[' . $languages[$i]['id'] . ']', 'soft', '34', '4', tep_get_templates_info($tInfo->templates_id, $languages[$i]['id'], 'templates_description')); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	if (DEBUG_MODE!='on' && tep_not_null($tInfo->templates_filename) && file_exists(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename))) {
?>
	  <tr>
		<td colspan="2"" class="main"><?php echo TEXT_TEMPLATE_NOT_EDITABLE; ?></td>
	  </tr>
<?php
	} else {
	  if (DEBUG_MODE=='on') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TEMPLATE_FILENAME; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('templates_filename') . (tep_not_null($tInfo->templates_filename) ? '<br>' . tep_draw_separator('pixel_trans.gif', '18', '1') . '&nbsp;<small>' . (!file_exists(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename)) ? TEXT_FILE_NOT_FOUND . ' ' : '') . DIR_WS_CATALOG_TEMPLATES . '<strong>' . basename($tInfo->templates_filename) . '</strong></small>' : ''); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TEMPLATE_FILENAME_CONTENT; ?></td>
            <td class="main"><?php
		  if (file_exists(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename)) && !is_writeable(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename))) {
			echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;<strong>' . ERROR_FILE_NO_WRITEABLE . '</strong>';
		  } else {
			echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_textarea_field('templates_filename_contents', 'off', '100%', '20', ((tep_not_null($tInfo->templates_filename) && file_exists(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename))) ? implode('', file(DIR_FS_CATALOG_TEMPLATES . basename($tInfo->templates_filename))) : ''));
		  }
?></td>
          </tr>
<?php
	  }
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TEMPLATE_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_TEMPLATE_DEFAULT_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('default_status', '1', $tInfo->default_status=='1'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_ALLOW_BLOCKS; ?></td>
            <td class="main" style="padding-left: 23px;"><?php
	$blocks_string = '';
	$blocks_query = tep_db_query("select blocks_id, blocks_name from " . TABLE_BLOCKS . " where blocks_types_id = '0' and blocks_style = 'static' and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_name");
	while ($blocks_array = tep_db_fetch_array($blocks_query)) {
	  $blocks_string .= tep_draw_checkbox_field('blocks[' . $blocks_array['blocks_id'] . ']', '1', in_array($blocks_array['blocks_id'], $tInfo->blocks)) . $blocks_array['blocks_name'] . '<br>' . "\n";
	}
	if (tep_not_null($blocks_string)) echo $blocks_string . '<br><br>';

	$types_string = '';
	$types_query = tep_db_query("select blocks_types_id, blocks_types_name from " . TABLE_BLOCKS_TYPES . " where blocks_types_style = 'static' and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_types_name");
	while ($types_array = tep_db_fetch_array($types_query)) {
	  $types_string .= '<br>' . "\n" . tep_draw_checkbox_field('types[' . $types_array['blocks_types_id'] . ']', '1', in_array($types_array['blocks_types_id'], $tInfo->blocks_types)) . $types_array['blocks_types_name'];
	}
	if (tep_not_null($types_string)) echo TEXT_STATIC_BLOCKS . $types_string . '<br><br>';

	$types_string = '';
	$types_query = tep_db_query("select blocks_types_id, blocks_types_name from " . TABLE_BLOCKS_TYPES . " where blocks_types_style = 'dynamic' and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_types_name");
	while ($types_array = tep_db_fetch_array($types_query)) {
	  $types_string .= '<br>' . "\n" . tep_draw_checkbox_field('types[' . $types_array['blocks_types_id'] . ']', '1', in_array($types_array['blocks_types_id'], $tInfo->blocks_types)) . $types_array['blocks_types_name'];
	}
	if (tep_not_null($types_string)) echo TEXT_DYNAMIC_BLOCKS . $types_string;
?></td>
          </tr>
          <tr>
            <td width="250"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo tep_draw_hidden_field('date_added', (tep_not_null($tInfo->date_added) ? $tInfo->date_added : date('Y-m-d'))) . tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_TEMPLATES, (isset($HTTP_GET_VARS['tID']) ? 'tID=' . $HTTP_GET_VARS['tID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TEMPLATES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$templates_query_raw = "select * from " . TABLE_TEMPLATES . " where language_id = '" . $languages_id . "' order by sort_order, default_status desc, templates_id";
	$templates_query = tep_db_query($templates_query_raw);
	while ($templates = tep_db_fetch_array($templates_query)) {
	  if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $templates['templates_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
		$templates['blocks'] = array();
		$blocks_query = tep_db_query("select blocks_id from " . TABLE_TEMPLATES_TO_BLOCKS . " where templates_id = '" . $templates['templates_id'] . "'");
		while ($blocks = tep_db_fetch_array($blocks_query)) {
		  $templates['blocks'][] = $blocks['blocks_id'];
		}
		$templates['blocks_types'] = array();
		$types_query = tep_db_query("select blocks_types_id from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where templates_id = '" . $templates['templates_id'] . "'");
		while ($types = tep_db_fetch_array($types_query)) {
		  $templates['blocks_types'][] = $types['blocks_types_id'];
		}
		$tInfo_array = $templates;
		$tInfo = new objectInfo($tInfo_array);
	  }

	  if (isset($tInfo) && is_object($tInfo) && ($templates['templates_id'] == $tInfo->templates_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $templates['templates_id'] . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $templates['templates_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent" title="<?php echo $templates['templates_description']; ?>"><?php echo '[' . $templates['sort_order'] . ']&nbsp;' . ($templates['default_status']=='1' ? '<strong>' . $templates['templates_name'] . '</strong>' : $templates['templates_name']); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($templates['templates_id'] == $tInfo->templates_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $templates['templates_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
	if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_TEMPLATES, 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
	}
?>
            </table></td>
<?php
	$heading = array();
	$contents = array();

	switch ($action) {
	  case 'delete':
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_TEMPLATE . '</strong>');

		$contents = array('form' => tep_draw_form('templates', FILENAME_TEMPLATES, 'tID=' . $tInfo->templates_id . '&action=deleteconfirm'));
		$contents[] = array('text' => TEXT_DELETE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->templates_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $tInfo->templates_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (isset($tInfo) && is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->templates_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $tInfo->templates_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_TEMPLATES, 'tID=' . $tInfo->templates_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  if (tep_not_null($tInfo->templates_description)) $contents[] = array('text' => $tInfo->templates_description);
		  if (tep_not_null($tInfo->templates_filename)) $contents[] = array('text' => '<br>' . (!file_exists(DIR_FS_CATALOG_TEMPLATES . $tInfo->templates_filename) ? TEXT_FILE_NOT_FOUND . '<br>' : '') . DIR_WS_CATALOG_TEMPLATES . $tInfo->templates_filename);
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . '<br>' . tep_datetime_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => '<br>' . TEXT_LAST_MODIFIED . '<br>' . tep_datetime_short($tInfo->last_modified));
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
    </table><?php
  }
?></td>
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