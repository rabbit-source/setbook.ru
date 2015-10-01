<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $configuration_title = tep_db_prepare_input($HTTP_POST_VARS['configuration_title']);
        $configuration_description = tep_db_prepare_input($HTTP_POST_VARS['configuration_description']);
        $configuration_value = tep_db_prepare_input($HTTP_POST_VARS['configuration_value']);
        $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);

		if (DEBUG_MODE=='on') {
		  $configuration_title = tep_db_prepare_input($HTTP_POST_VARS['configuration_title']);
		  $configuration_description = tep_db_prepare_input($HTTP_POST_VARS['configuration_description']);
		  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_title = '" . tep_db_input($configuration_title) . "', configuration_description = '" . tep_db_input($configuration_description) . "', configuration_value = '" . tep_db_input($configuration_value) . "', last_modified = now() where configuration_id = '" . (int)$cID . "'");
		} else {
		  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($configuration_value) . "', last_modified = now() where configuration_id = '" . (int)$cID . "'");
		}

        tep_redirect(tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $cID));
        break;
    }
  }

  $gID = (isset($HTTP_GET_VARS['gID'])) ? $HTTP_GET_VARS['gID'] : '';
  $gPath = (isset($HTTP_GET_VARS['gPath'])) ? $HTTP_GET_VARS['gPath'] : '';

  $cfg_group_query = tep_db_query("select configuration_group_title, visible from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gPath . "'");
  $cfg_group = tep_db_fetch_array($cfg_group_query);
  if ($cfg_group['visible']!='1') {
	$cfg_group = array();
	$gPath = '';
	$HTTP_GET_VARS['gPath'] = '';
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
		<td class="pageHeading"><?php echo tep_not_null($cfg_group['configuration_group_title']) ? $cfg_group['configuration_group_title'] : HEADING_TITLE; ?></td>
	  </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
	 </tr>
	  <tr>
		<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td valign="top">
<?php
  if ($gPath > 0) {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
			  </tr>
<?php
	$configuration_query = tep_db_query("select configuration_id, configuration_title, configuration_value, use_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gPath . "' order by sort_order");
	while ($configuration = tep_db_fetch_array($configuration_query)) {
	  if (tep_not_null($configuration['use_function'])) {
		$use_function = $configuration['use_function'];
		if (ereg('->', $use_function)) {
		  $class_method = explode('->', $use_function);
		  if (!is_object(${$class_method[0]})) {
			include(DIR_WS_CLASSES . $class_method[0] . '.php');
			${$class_method[0]} = new $class_method[0]();
		  }
		  $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
		} else {
		  $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
		}
	  } else {
		$cfgValue = $configuration['configuration_value'];
	  }

	  if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
		$cfg_extra_query = tep_db_query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
		$cfg_extra = tep_db_fetch_array($cfg_extra_query);

		$cInfo_array = array_merge($configuration, $cfg_extra);
		$cInfo = new objectInfo($cInfo_array);
	  }

	  if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
		echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
	  }
?>
				<td class="dataTableContent"><?php echo $configuration['configuration_title']; ?></td>
				<td class="dataTableContent"><?php echo nl2br(trim(htmlspecialchars($cfgValue))); ?></td>
				<td class="dataTableContent" align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $configuration['configuration_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
	}
?>
			  <tr>
				<td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $gPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?>&nbsp;</td>
                  </tr>
                </table></td>
			  </tr>
			</table>
<?php
  } else {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
			  <tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
			  </tr>
<?php
	$configuration_query = tep_db_query("select * from " . TABLE_CONFIGURATION_GROUP . " where visible = '1' order by sort_order");
	while ($configuration = tep_db_fetch_array($configuration_query)) {
	  if ((!isset($HTTP_GET_VARS['gID']) || (isset($HTTP_GET_VARS['gID']) && ($HTTP_GET_VARS['gID'] == $configuration['configuration_group_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
		$gInfo = new objectInfo($configuration);
	  }

	  if ( (isset($gInfo) && is_object($gInfo)) && ($configuration['configuration_group_id'] == $gInfo->configuration_group_id) ) {
		echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $gInfo->configuration_group_id) . '\'">' . "\n";
	  } else {
		echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $configuration['configuration_group_id']) . '\'">' . "\n";
	  }
?>
				<td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $configuration['configuration_group_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;<strong>' . $configuration['configuration_group_title'] . '</strong>'; ?></td>
				<td class="dataTableContent" align="right"><?php if ( (isset($gInfo) && is_object($gInfo)) && ($configuration['configuration_group_id'] == $gInfo->configuration_group_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $configuration['configuration_group_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
	}
?>
			</table>
<?php
  }
?>
			</td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<strong>' . $cInfo->configuration_title . '</strong>');

      if ($cInfo->set_function) {
        eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
      } else {
        $value_field = tep_draw_input_field('configuration_value', $cInfo->configuration_value);
      }

      $contents = array('form' => tep_draw_form('configuration', FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $cInfo->configuration_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
	  if (DEBUG_MODE=='on') {
		$contents[] = array('text' => '<br>' . TEXT_EDIT_CONFIGURATION_TITLE . '<br>' . tep_draw_input_field('configuration_title', $cInfo->configuration_title, 'size="32"'));
		$contents[] = array('text' => '<br>' . TEXT_EDIT_CONFIGURATION_DESCRIPTION . '<br>' . tep_draw_textarea_field('configuration_description', 'soft', '32', '5', $cInfo->configuration_description));
		$contents[] = array('text' => '<br>' . TEXT_EDIT_CONFIGURATION_VALUE . '<br>' . $value_field);
	  } else {
		$contents[] = array('text' => '<br><strong>' . $cInfo->configuration_title . '</strong><br>' . $cInfo->configuration_description . '<br>' . $value_field);
	  }
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $cInfo->configuration_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($gInfo) && is_object($gInfo)) {
        $heading[] = array('text' => '<strong>' . $gInfo->configuration_group_title . '</strong>');

        $contents[] = array('text' => $gInfo->configuration_group_description);
      } elseif (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->configuration_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gPath=' . $HTTP_GET_VARS['gPath'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
        if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '			<td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '</td>' . "\n";
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
