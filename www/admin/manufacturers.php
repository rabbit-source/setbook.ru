<?php
  require('includes/application_top.php');

  function tep_get_manufacturer_info($manufacturer_id, $language_id = '', $field = 'manufacturers_name') {
	global $languages_id;

	if (empty($language_id)) $language_id = $languages_id;
	$manufacturer_query = tep_db_query("select " . tep_db_input($field) . " as value from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturer_id . "' and languages_id = '" . (int)$language_id . "'");
	$manufacturer = tep_db_fetch_array($manufacturer_query);

	return $manufacturer['value'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (tep_not_null($HTTP_GET_VARS['mID'])) $manufacturers_id = tep_db_prepare_input($HTTP_GET_VARS['mID']);

        $sql_data_array = array('sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']));

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
          $manufacturers_id = tep_db_insert_id();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

		if (tep_not_null($HTTP_POST_VARS['manufacturers_path'])) $manufacturers_path = $HTTP_POST_VARS['manufacturers_path'];
		else $manufacturers_path = $manufacturers_name;

		$manufacturers_path = preg_replace('/[^a-z0-9]/', '_', strtolower($manufacturers_path));
		$manufacturers_path = preg_replace('/_+/', '_', $manufacturers_path);
		if (substr($manufacturers_path, 0, 1)=='_') $manufacturers_path = substr($manufacturers_path, 1);
		if (substr($manufacturers_path, -1)=='_') $manufacturers_path = substr($manufacturers_path, 0, -1);
		if (empty($manufacturers_path)) $manufacturers_path = $manufacturers_id;
		$check_query = tep_db_query("select count(*) as total from " . TABLE_MANUFACTURERS . " where manufacturers_path = '" . tep_db_input($manufacturers_path) . "' and manufacturers_id <> '" . (int)$manufacturers_id . "'");
		$check = tep_db_fetch_array($check_query);
		$check_categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where categories_path = '" . tep_db_input($manufacturers_path) . "' and parent_id = '0'");
		$check_categories = tep_db_fetch_array($check_categories_query);
		if ($check['total'] > 0 || $check_categories['total'] > 0) $manufacturers_path = 'manufacturer' . $manufacturers_id;
		tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_path = '" . tep_db_input($manufacturers_path) . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");

		if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
		  $size = @getimagesize($manufacturers_image);
		  if ($size[2]=='3') $ext = '.png';
		  elseif ($size[2]=='2') $ext = '.jpg';
		  else $ext = '.gif';
		  $new_filename = preg_replace('/[^\d\w]/i', '', strtolower($manufacturers_path));
		  if (!tep_not_null($new_filename)) $new_filename = $manufacturers_id;
		  $new_filename .= $ext;
		  $upload->filename = 'manufacturers/' . $new_filename;
          if ($upload->upload('manufacturers_image', DIR_FS_CATALOG_IMAGES)) {
			if (MANUFACTURER_IMAGE_WIDTH > 0 || MANUFACTURER_IMAGE_HEIGHT > 0) {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', MANUFACTURER_IMAGE_WIDTH, MANUFACTURER_IMAGE_HEIGHT);
			  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'manufacturers/thumbs')) mkdir(DIR_FS_CATALOG_IMAGES . 'manufacturers/thumbs', 0777);
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, DIR_FS_CATALOG_IMAGES . str_replace('manufacturers/', 'manufacturers/thumbs/', $upload->filename), 50, 70);
			}
			$prev_file_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['manufacturers_image']) && $prev_file['manufacturers_image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['manufacturers_image']);
			}
			tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_image = '" . $upload->filename . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");
		  }
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $manufacturers_name_array = $HTTP_POST_VARS['manufacturers_name'];
          $manufacturers_description_array = $HTTP_POST_VARS['manufacturers_description'];
          $manufacturers_url_array = $HTTP_POST_VARS['manufacturers_url'];
          $language_id = $languages[$i]['id'];

		  $description = str_replace('\\\"', '"', $manufacturers_description_array[$language_id]);
		  $description = str_replace('\"', '"', $description);
		  $description = str_replace("\\\'", "\'", $description);
		  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		  $description = str_replace(' - ', ' &ndash; ', $description);
		  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

          $sql_data_array = array('manufacturers_name' => tep_db_prepare_input($manufacturers_name_array[$language_id]),
								  'manufacturers_description' => $description,
								  'manufacturers_url' => tep_db_prepare_input($manufacturers_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
          } elseif ($action == 'save') {
            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$language_id . "'");
          }
        }

		tep_update_blocks($manufacturers_id, 'manufacturer');

        tep_redirect(tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers_id));
        break;
      case 'deleteconfirm':
        $manufacturers_id = tep_db_prepare_input($HTTP_GET_VARS['mID']);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $manufacturer_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          $manufacturer = tep_db_fetch_array($manufacturer_query);

          $image_location = DIR_FS_CATALOG . DIR_WS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        tep_remove_manufacturer($manufacturers_id);

        if (isset($HTTP_POST_VARS['delete_products']) && ($HTTP_POST_VARS['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            tep_remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '0' where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        if (isset($HTTP_POST_VARS['delete_series']) && ($HTTP_POST_VARS['delete_series'] == 'on')) {
          $series_query = tep_db_query("select series_id from " . TABLE_SERIES . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          while ($series = tep_db_fetch_array($series_query)) {
            tep_remove_serie($series['series_id']);
          }
        } else {
          tep_db_query("update " . TABLE_SERIES . " set manufacturers_id = '0' where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        tep_redirect(tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page']));
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
    <td width="100%" valign="top">
<?php
  if ($action == 'edit' || $action == 'new') {
    $parameters = array('manufacturers_name' => '',
						'manufacturers_description' => '',
						'manufacturers_id' => '',
						'manufacturers_image' => '',
						'date_added' => '',
						'last_modified' => '');

    $mInfo = new objectInfo($parameters);

    if (tep_not_null($HTTP_GET_VARS['mID']) && empty($HTTP_POST_VARS)) {
      $manufacturer_query = tep_db_query("select m.*, mi.* from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_id = '" . (int)$HTTP_GET_VARS['mID'] . "' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "'");
      $manufacturer = tep_db_fetch_array($manufacturer_query);

      $mInfo->objectInfo($manufacturer);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $mInfo->objectInfo($HTTP_POST_VARS);
      $manufacturers_name = array_map("stripslashes", $HTTP_POST_VARS['manufacturers_name']);
      $manufacturers_description = array_map("stripslashes", $HTTP_POST_VARS['manufacturers_description']);
    }

    $languages = tep_get_languages();

	$form_action = (tep_not_null($HTTP_GET_VARS['mID'])) ? 'save' : 'insert';
	echo tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, tep_get_all_get_params(array('mID', 'action')) . (tep_not_null($HTTP_GET_VARS['mID']) ? '&mID=' . $HTTP_GET_VARS['mID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('manufacturers_id', $mInfo->manufacturers_id);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='save' ? sprintf(TEXT_HEADING_EDIT_MANUFACTURER, $mInfo->manufacturers_name) : TEXT_HEADING_NEW_MANUFACTURER; ?></td>
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
            <td width="250" class="main"><?php if ($i == 0) echo TEXT_MANUFACTURERS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('manufacturers_name[' . $languages[$i]['id'] . ']', (isset($manufacturers_name[$languages[$i]['id']]) ? $manufacturers_name[$languages[$i]['id']] : tep_get_manufacturer_info($mInfo->manufacturers_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
		</table>
<?php
	echo tep_load_blocks($mInfo->manufacturers_id, 'manufacturer');
?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_MANUFACTURERS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('manufacturers_image') . (tep_not_null($mInfo->manufacturers_image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $mInfo->manufacturers_image : '') . '</span>'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_MANUFACTURERS_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($manufacturers_description[$languages[$i]['id']]) ? $manufacturers_description[$languages[$i]['id']] : tep_get_manufacturer_info($mInfo->manufacturers_id, $languages[$i]['id'], 'manufacturers_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('manufacturers_description[' . $languages[$i]['id'] . ']');
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
            <td class="main" width="250"><?php echo TEXT_MANUFACTURERS_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $mInfo->sort_order, 'size="5"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_MANUFACTURERS_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_MANUFACTURERS, '', 'NONSSL', false) . tep_draw_input_field('manufacturers_path', $mInfo->manufacturers_path, 'size="' . (tep_not_null($mInfo->manufacturers_path) ? (strlen($mInfo->manufacturers_path)-1) : '7') . '"') . '/'; ?></td>
          </tr>
		</table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('date_added', (tep_not_null($mInfo->date_added) ? $mInfo->date_added : date('Y-m-d')));

	if (tep_not_null($HTTP_GET_VARS['mID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_MANUFACTURERS, tep_get_all_get_params(array('mID', 'action')) . (tep_not_null($HTTP_GET_VARS['mID']) ? '&mID=' . $HTTP_GET_VARS['mID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
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
			  <?php echo tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, '', 'get'); ?>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
	$search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	$manufacturers_query_raw = "select m.manufacturers_id, mi.manufacturers_name, m.manufacturers_image, m.date_added, m.last_modified from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "'" . (tep_not_null($search) ? " and (mi.manufacturers_name like '%" . str_replace(" ", "%' and mi.manufacturers_name like '%", $search) . "%')" : "") . " order by m.sort_order, mi.manufacturers_name";
	$manufacturers_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $manufacturers_query_raw, $manufacturers_query_numrows);
	$manufacturers_query = tep_db_query($manufacturers_query_raw);
	while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
	  if ((!isset($HTTP_GET_VARS['mID']) || (isset($HTTP_GET_VARS['mID']) && ($HTTP_GET_VARS['mID'] == $manufacturers['manufacturers_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
		$manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers['manufacturers_id'] . "'");
		$manufacturer_products = tep_db_fetch_array($manufacturer_products_query);

		$manufacturer_series_query = tep_db_query("select count(*) as series_count from " . TABLE_SERIES . " where manufacturers_id = '" . (int)$manufacturers['manufacturers_id'] . "'");
		$manufacturer_series = tep_db_fetch_array($manufacturer_series_query);

		$mInfo_array = array_merge($manufacturers, $manufacturer_products, $manufacturer_series);
		$mInfo = new objectInfo($mInfo_array);
	  }

	  if (isset($mInfo) && is_object($mInfo) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id'] . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo $manufacturers['manufacturers_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $manufacturers_split->display_count($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $manufacturers_split->display_links($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'action', 'sID'))); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
	if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_MANUFACTURER . '</strong>');

		$contents = array('form' => tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm'));
		$contents[] = array('text' => TEXT_DELETE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $mInfo->manufacturers_name . '</strong>');
		$contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

		if ($mInfo->series_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_SERIES, $mInfo->series_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_series') . ' ' . TEXT_DELETE_SERIES);
		}

		if ($mInfo->products_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
		}

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (isset($mInfo) && is_object($mInfo)) {
		  $heading[] = array('text' => '<strong>' . $mInfo->manufacturers_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($mInfo->date_added));
		  if (tep_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($mInfo->last_modified));
		  $contents[] = array('text' => '<br>' . tep_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name));
		  $contents[] = array('text' => '<br>' . TEXT_SERIES . ' ' . $mInfo->series_count);
		  $contents[] = array('text' => TEXT_PRODUCTS . ' ' . $mInfo->products_count);
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