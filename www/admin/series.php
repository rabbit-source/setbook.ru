<?php
  require('includes/application_top.php');

  function tep_get_serie_info($serie_id, $language_id = '', $field = 'series_name') {
	global $languages_id;

	if (empty($language_id)) $language_id = $languages_id;
	$serie_query = tep_db_query("select " . tep_db_input($field) . " as value from " . TABLE_SERIES . " where series_id = '" . (int)$serie_id . "' and language_id = '" . (int)$language_id . "'");
	$serie = tep_db_fetch_array($serie_query);

	return $serie['value'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($HTTP_POST_VARS['series_id'])) {
		  $series_id = tep_db_prepare_input($HTTP_POST_VARS['series_id']);
		} else {
		  $max_id_query = tep_db_query("select max(series_id) as new_id from " . TABLE_SERIES . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $series_id = (int)$max_id['new_id'] + 1;
		}

		if (tep_not_null($HTTP_POST_VARS['series_path'])) $series_path = $HTTP_POST_VARS['series_path'];
		else $series_path = $series_name;

		$series_path = preg_replace('/[^a-z0-9]/', '_', strtolower($series_path));
		$series_path = preg_replace('/_+/', '_', $series_path);
		if (substr($series_path, 0, 1)=='_') $series_path = substr($series_path, 1);
		if (substr($series_path, -1)=='_') $series_path = substr($series_path, 0, -1);
		if (empty($series_path)) $series_path = $series_id;
		$check_query = tep_db_query("select count(*) as total from " . TABLE_SERIES . " where series_path = '" . tep_db_input($series_path) . "' and series_id <> '" . (int)$series_id . "'");
		$check = tep_db_fetch_array($check_query);
		if ($check['total'] > 0) $series_path = 'serie' . $series_id;

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $series_name_array = $HTTP_POST_VARS['series_name'];
          $series_description_array = $HTTP_POST_VARS['series_description'];
          $language_id = $languages[$i]['id'];

		  $description = str_replace('\\\"', '"', $series_description_array[$language_id]);
		  $description = str_replace('\"', '"', $description);
		  $description = str_replace("\\\'", "\'", $description);
		  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		  $description = str_replace(' - ', ' &ndash; ', $description);
		  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

          $sql_data_array = array('series_name' => tep_db_prepare_input($series_name_array[$language_id]),
								  'series_description' => $description,
								  'series_path' => tep_db_input($series_path),
								  'sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']),
								  'manufacturers_id' => tep_db_prepare_input($HTTP_POST_VARS['manufacturers_id']));

          if ($action == 'insert') {
            $insert_sql_data = array('series_id' => $series_id,
                                     'language_id' => $language_id,
									 'date_added' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_SERIES, $sql_data_array);
          } elseif ($action == 'save') {
            $update_sql_data = array('last_modified' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            tep_db_perform(TABLE_SERIES, $sql_data_array, 'update', "series_id = '" . (int)$series_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }

		if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
		  $size = @getimagesize($series_image);
		  if ($size[2]=='3') $ext = '.png';
		  elseif ($size[2]=='2') $ext = '.jpg';
		  else $ext = '.gif';
		  $new_filename = preg_replace('/[^\d\w]/i', '', strtolower($series_path));
		  if (!tep_not_null($new_filename)) $new_filename = $series_id;
		  $new_filename .= $ext;
		  $upload->filename = 'series/' . $new_filename;
          if ($upload->upload('series_image', DIR_FS_CATALOG_IMAGES)) {
			$prev_file_query = tep_db_query("select series_image from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['series_image']) && $prev_file['series_image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['series_image']);
			}
			if (SERIE_IMAGE_WIDTH > 0 || SERIE_IMAGE_HEIGHT > 0) {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', SERIE_IMAGE_WIDTH, SERIE_IMAGE_HEIGHT);
			}
			tep_db_query("update " . TABLE_SERIES . " set series_image = '" . $upload->filename . "' where series_id = '" . (int)$series_id . "'");
		  }
        }

		tep_update_blocks($series_id, 'serie');

        tep_redirect(tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $series_id));
        break;
      case 'deleteconfirm':
        $series_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $serie_query = tep_db_query("select series_image from " . TABLE_SERIES . " where series_id = '" . (int)$series_id . "'");
          $serie = tep_db_fetch_array($serie_query);

          $image_location = DIR_FS_CATALOG . DIR_WS_CATALOG_IMAGES . $serie['series_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

		tep_remove_serie($series_id);

        if (isset($HTTP_POST_VARS['delete_products']) && ($HTTP_POST_VARS['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where series_id = '" . (int)$series_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            tep_remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set series_id = '' where series_id = '" . (int)$series_id . "'");
        }

        tep_redirect(tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  $manufacturers_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " order by manufacturers_name");
  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
	$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
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
    <td width="100%" valign="top">
<?php
  if ($action == 'edit' || $action == 'new') {
    $parameters = array();
	$query = tep_db_query("describe " . TABLE_SERIES . "");
	while ($row = tep_db_fetch_array($query)) {
	  $parameters[$row['Field']] = '';
	}

    $sInfo = new objectInfo($parameters);

    if (tep_not_null($HTTP_GET_VARS['sID']) && empty($HTTP_POST_VARS)) {
      $serie_query = tep_db_query("select * from " . TABLE_SERIES . " where series_id = '" . (int)$HTTP_GET_VARS['sID'] . "' and language_id = '" . (int)$languages_id . "'");
      $serie = tep_db_fetch_array($serie_query);

      $sInfo->objectInfo($serie);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $sInfo->objectInfo($HTTP_POST_VARS);
      $series_name = array_map("stripslashes", $HTTP_POST_VARS['series_name']);
      $series_description = array_map("stripslashes", $HTTP_POST_VARS['series_description']);
    }

    $languages = tep_get_languages();

	$form_action = (tep_not_null($HTTP_GET_VARS['sID'])) ? 'save' : 'insert';
	echo tep_draw_form('series', FILENAME_SERIES, tep_get_all_get_params(array('sID', 'action')) . (tep_not_null($HTTP_GET_VARS['sID']) ? '&sID=' . $HTTP_GET_VARS['sID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"') . (tep_not_null($HTTP_GET_VARS['sID']) ? tep_draw_hidden_field('series_id', $sInfo->series_id) : '');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='save' ? sprintf(TEXT_HEADING_EDIT_SERIE, $sInfo->series_name) : TEXT_HEADING_NEW_SERIE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="100%">
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td width="250" class="main"><?php if ($i == 0) echo TEXT_SERIES_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('series_name[' . $languages[$i]['id'] . ']', (isset($series_name[$languages[$i]['id']]) ? $series_name[$languages[$i]['id']] : tep_get_serie_info($sInfo->series_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
		</table>
<?php
	echo tep_load_blocks($sInfo->series_id, 'serie');
?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_SERIES_MANUFACTURER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $sInfo->manufacturers_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_SERIES_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('series_image') . (tep_not_null($sInfo->series_image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $sInfo->series_image : '') . '</span>'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_SERIES_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($series_description[$languages[$i]['id']]) ? $series_description[$languages[$i]['id']] : tep_get_serie_info($sInfo->series_id, $languages[$i]['id'], 'series_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('series_description[' . $languages[$i]['id'] . ']');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_SERIES_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $sInfo->sort_order, 'size="5"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_SERIES_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_SERIES, '', 'NONSSL', false) . tep_draw_input_field('series_path', $sInfo->series_path, 'size="' . (tep_not_null($sInfo->series_path) ? (strlen($sInfo->series_path)-1) : '7') . '"') . '/'; ?></td>
          </tr>
		</table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('date_added', (tep_not_null($sInfo->date_added) ? $sInfo->date_added : date('Y-m-d')));

	if (tep_not_null($HTTP_GET_VARS['sID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SERIES, tep_get_all_get_params(array('sID', 'action')) . (tep_not_null($HTTP_GET_VARS['sID']) ? '&sID=' . $HTTP_GET_VARS['sID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
			<td align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
			<td align="right"><table border="0" cellspacing="0" cellpadding="0">
			  <tr>
			  <?php echo tep_draw_form('series', FILENAME_SERIES, '', 'get'); ?>
				<td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search') . tep_draw_hidden_field('page', $HTTP_GET_VARS['page']); ?></td>
			  </form>
			  </tr>
			</table></td>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SERIES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	$series_query_raw = "select * from " . TABLE_SERIES . " where language_id = '" . (int)$languages_id . "'" . (tep_not_null($search) ? " and (series_name like '%" . str_replace(" ", "%' and series_name like '%", $search) . "%')" : "") . " order by sort_order, series_name";
	$series_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $series_query_raw, $series_query_numrows);
	$series_query = tep_db_query($series_query_raw);
	while ($series = tep_db_fetch_array($series_query)) {
	  if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $series['series_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
		$serie_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where series_id = '" . (int)$series['series_id'] . "'");
		$serie_products = tep_db_fetch_array($serie_products_query);

		$sInfo_array = array_merge($series, $serie_products);
		$sInfo = new objectInfo($sInfo_array);
	  }

	  if (isset($sInfo) && is_object($sInfo) && ($series['series_id'] == $sInfo->series_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $series['series_id'] . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $series['series_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo $series['series_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($series['series_id'] == $sInfo->series_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $series['series_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $series_split->display_count($series_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $series_split->display_links($series_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'action', 'sID'))); ?></td>
                  </tr>
<?php
	if (empty($action)) {
?>
				  <tr>
					<td align="right" colspan="2" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
	  case 'delete':
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_SERIE . '</strong>');

		$contents = array('form' => tep_draw_form('series', FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->series_id . '&action=deleteconfirm'));
		$contents[] = array('text' => TEXT_DELETE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $sInfo->series_name . '</strong>');
		$contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

		if ($sInfo->products_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $sInfo->products_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
		}

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->series_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (isset($sInfo) && is_object($sInfo)) {
		  $heading[] = array('text' => '<strong>' . $sInfo->series_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->series_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SERIES, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->series_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added));
		  if (tep_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified));
		  $contents[] = array('text' => '<br>' . tep_info_image($sInfo->series_image, $sInfo->series_name));
		  $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $sInfo->products_count);
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