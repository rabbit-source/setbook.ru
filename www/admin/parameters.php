<?php
  require('includes/application_top.php');

  function tep_get_products_types_info($products_types_id, $language_id = '', $field = '') {
	global $languages_id;
	if (empty($language_id)) $language_id = $languages_id;
	if (empty($field)) $field = 'products_types_name';

	$type_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_PRODUCTS_TYPES  ." where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$language_id . "'");
	$type = tep_db_fetch_array($type_query);

	return $type[$field];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
	if (isset($HTTP_GET_VARS['pID'])) $parameters_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
    switch ($action) {
	  case 'insert_type':
	  case 'update_type':
		$products_types_id = tep_db_prepare_input($HTTP_POST_VARS['types_id']);
		if ($action == 'insert_type') {
		  $last_row_query = tep_db_query("select max(products_types_id) as last_id from " . TABLE_PRODUCTS_TYPES . "");
		  $last_row = tep_db_fetch_array($last_row_query);
		  $products_types_id = (int)$last_row['last_id'] + 1;
		}

		$products_types_name_array = $HTTP_POST_VARS['products_types_name'];
		$products_types_description_array = $HTTP_POST_VARS['products_types_description'];

		$languages = tep_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $sql_data_array = array('sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']),
								  'products_types_name' => tep_db_prepare_input($products_types_name_array[$language_id]),
								  'products_types_description' => tep_db_prepare_input($products_types_description_array[$language_id]));

		  if ($action == 'insert_type') {
			$insert_sql_data = array('date_added' => 'now()',
									 'products_types_id' => $products_types_id,
									 'language_id' => $language_id);

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			tep_db_perform(TABLE_PRODUCTS_TYPES, $sql_data_array);
		  } elseif ($action == 'update_type') {
			$update_sql_data = array('last_modified' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $update_sql_data);

			tep_db_perform(TABLE_PRODUCTS_TYPES, $sql_data_array, 'update', "products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		  }
		}
		tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_types_path = '" . tep_db_prepare_input($HTTP_POST_VARS['products_types_path']) . "', products_types_letter_search = '" . (int)$HTTP_POST_VARS['products_types_letter_search'] . "' where products_types_id = '" . (int)$products_types_id . "'");

		tep_redirect(tep_href_link(FILENAME_PARAMETERS, 'tID=' . $products_types_id));
		break;
	  case 'delete_type_confirm':
		$products_types_id = tep_db_prepare_input($HTTP_POST_VARS['types_id']);
		tep_db_query("update " . TABLE_PRODUCTS . " set products_types_id = '0' where products_types_id = '" . (int)$products_types_id . "'");
		tep_db_query("delete from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "'");

		tep_redirect(tep_href_link(FILENAME_PARAMETERS));
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
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $rows = 0;
  $types_query = tep_db_query("select * from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . $languages_id . "' order by sort_order, products_types_name");
  while ($types = tep_db_fetch_array($types_query)) {
	$rows ++;
	if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $types['products_types_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
	  $tInfo = new objectInfo($types);
	}

	if (isset($tInfo) && is_object($tInfo) && ($types['products_types_id'] == $tInfo->products_types_id)) {
	  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $types['products_types_id'] . '&action=edit_type') . '\'">' . "\n";
	} else {
	  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $types['products_types_id']) . '\'">' . "\n";
	}
?>
                <td class="dataTableContent" title="<?php echo $types['products_types_description']; ?>"><?php echo '[' . $types['sort_order'] . ']&nbsp;' . $types['products_types_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($types['products_types_id'] == $tInfo->products_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $types['products_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="4" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $tInfo->products_types_id . '&action=new_type') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
	case 'new_type':
    case 'edit_type':
      $heading[] = array('text' => '<strong>' . ($action=='edit_type' ? TEXT_HEADING_EDIT_TYPE : TEXT_HEADING_NEW_TYPE) . '</strong>');

      $contents = array('form' => tep_draw_form('types', FILENAME_PARAMETERS, 'action=' . ($action=='edit_type' ? 'update_type' : 'insert_type') . (tep_not_null($tInfo->products_types_id) ? '&tID=' . $tInfo->products_types_id : ''), 'post') . tep_draw_hidden_field('types_id', $tInfo->products_types_id));
      $contents[] = array('text' => ($action=='edit_type' ? TEXT_EDIT_TYPE_INTRO : TEXT_NEW_TYPE_INTRO));

	  $products_types_inputs_string = '';
	  $languages = tep_get_languages();
	  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		$products_types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('products_types_name[' . $languages[$i]['id'] . ']', tep_get_products_types_info($tInfo->products_types_id, $languages[$i]['id']), 'size="35"');
	  }
	  $contents[] = array('text' => '<br>' . TEXT_TYPES_NAME . $products_types_inputs_string);

	  $products_types_inputs_string = '';
	  $languages = tep_get_languages();
	  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		$products_types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('products_types_description[' . $languages[$i]['id'] . ']', 'soft', '35', '3', tep_get_products_types_info($tInfo->products_types_id, $languages[$i]['id'], 'products_types_description'));
	  }
	  $contents[] = array('text' => '<br>' . TEXT_TYPES_DESCRIPTION . $products_types_inputs_string);

	  $contents[] = array('text' => '<br>' . TEXT_TYPES_PATH . '<br>' . tep_draw_input_field('products_types_path', $tInfo->products_types_path, 'size="8"'));

	  $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('products_types_letter_search', '1', $tInfo->products_types_letter_search=='1') . TEXT_TYPES_LETTER_SEARCH);

	  $contents[] = array('text' => '<br>' . TEXT_EDIT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="2"'));

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PARAMETERS, (tep_not_null($tInfo->products_types_id) ? 'tID=' . $tInfo->products_types_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete_type':
      $heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_TYPE . '</strong>');

      $contents = array('form' => tep_draw_form('types', FILENAME_PARAMETERS, 'tID=' . $tInfo->products_types_id . '&action=delete_type_confirm') . tep_draw_hidden_field('types_id', $tInfo->products_types_id));
      $contents[] = array('text' => TEXT_DELETE_TYPE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $tInfo->products_types_name . '</strong>');

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $tInfo->products_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
	default:
	  if ($rows > 0) {
		if (isset($tInfo) && is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->products_types_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $tInfo->products_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_PARAMETERS, 'tID=' . $tInfo->products_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  if (tep_not_null($tInfo->products_types_description)) $contents[] = array('text' => '<br>' . TEXT_TYPES_DESCRIPTION . '<br>' .nl2br($tInfo->products_types_description));
		  $contents[] = array('text' => '<br>' . TEXT_TYPES_PATH . ' ' . $tInfo->products_types_path);
		  $contents[] = array('text' => '<br>' . TEXT_TYPES_LETTER_SEARCH . ' ' . ($tInfo->products_types_letter_search=='1' ? TEXT_YES : TEXT_NO));
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_datetime_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_datetime_short($tInfo->last_modified));
		}
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