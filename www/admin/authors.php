<?php
  require('includes/application_top.php');

  function tep_get_author_info($author_id, $language_id = '', $field = 'authors_name') {
	global $languages_id;

	if (empty($language_id)) $language_id = $languages_id;
	$author_query = tep_db_query("select " . tep_db_input($field) . " as value from " . TABLE_AUTHORS . " where authors_id = '" . (int)$author_id . "' and language_id = '" . (int)$language_id . "'");
	$author = tep_db_fetch_array($author_query);

	return $author['value'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($HTTP_POST_VARS['authors_id'])) {
		  $authors_id = tep_db_prepare_input($HTTP_POST_VARS['authors_id']);
		} else {
		  $max_id_query = tep_db_query("select max(authors_id) as new_id from " . TABLE_AUTHORS . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $authors_id = (int)$max_id['new_id'] + 1;
		}

		if (tep_not_null($HTTP_POST_VARS['authors_path'])) $authors_path = $HTTP_POST_VARS['authors_path'];
		else $authors_path = $authors_name;

		$authors_path = preg_replace('/[^a-z0-9]/', '_', strtolower($authors_path));
		$authors_path = preg_replace('/_+/', '_', $authors_path);
		if (substr($authors_path, 0, 1)=='_') $authors_path = substr($authors_path, 1);
		if (substr($authors_path, -1)=='_') $authors_path = substr($authors_path, 0, -1);
		if (empty($authors_path)) $authors_path = $authors_id;
		$check_query = tep_db_query("select count(*) as total from " . TABLE_AUTHORS . " where authors_path = '" . tep_db_input($authors_path) . "' and authors_id <> '" . (int)$authors_id . "'");
		$check = tep_db_fetch_array($check_query);
		if ($check['total'] > 0) $authors_path = 'author' . $authors_id;

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $authors_name_array = $HTTP_POST_VARS['authors_name'];
          $authors_description_array = $HTTP_POST_VARS['authors_description'];
          $language_id = $languages[$i]['id'];

		  $description = str_replace('\\\"', '"', $authors_description_array[$language_id]);
		  $description = str_replace('\"', '"', $description);
		  $description = str_replace("\\\'", "\'", $description);
		  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		  $description = str_replace(' - ', ' &ndash; ', $description);
		  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

          $sql_data_array = array('authors_name' => tep_db_prepare_input($authors_name_array[$language_id]),
								  'authors_description' => $description,
								  'authors_path' => tep_db_input($authors_path),
								  'sort_order' => tep_db_prepare_input($HTTP_POST_VARS['sort_order']));

          if ($action == 'insert') {
            $insert_sql_data = array('authors_id' => $authors_id,
                                     'language_id' => $language_id,
									 'date_added' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_AUTHORS, $sql_data_array);
          } elseif ($action == 'save') {
            $update_sql_data = array('last_modified' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            tep_db_perform(TABLE_AUTHORS, $sql_data_array, 'update', "authors_id = '" . (int)$authors_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }

		if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
		  $size = @getimagesize($authors_image);
		  if ($size[2]=='3') $ext = '.png';
		  elseif ($size[2]=='2') $ext = '.jpg';
		  else $ext = '.gif';
		  $new_filename = preg_replace('/[^\d\w]/i', '', strtolower($authors_path));
		  if (!tep_not_null($new_filename)) $new_filename = $authors_id;
		  $new_filename .= $ext;
		  $upload->filename = 'authors/' . $new_filename;
          if ($upload->upload('authors_image', DIR_FS_CATALOG_IMAGES)) {
			$prev_file_query = tep_db_query("select authors_image from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['authors_image']) && $prev_file['authors_image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['authors_image']);
			}
			if (AUTHOR_IMAGE_WIDTH > 0 || AUTHOR_IMAGE_HEIGHT > 0) {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', AUTHOR_IMAGE_WIDTH, AUTHOR_IMAGE_HEIGHT);
			  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'authors/thumbs')) mkdir(DIR_FS_CATALOG_IMAGES . 'authors/thumbs', 0777);
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, DIR_FS_CATALOG_IMAGES . str_replace('authors/', 'authors/thumbs/', $upload->filename), 50, 70);
			}
			tep_db_query("update " . TABLE_AUTHORS . " set authors_image = '" . $upload->filename . "' where authors_id = '" . (int)$authors_id . "'");
		  }
        }

		tep_update_blocks($authors_id, 'author');

        tep_redirect(tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $authors_id));
        break;
      case 'deleteconfirm':
        $authors_id = tep_db_prepare_input($HTTP_GET_VARS['aID']);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $author_query = tep_db_query("select authors_image from " . TABLE_AUTHORS . " where authors_id = '" . (int)$authors_id . "'");
          $author = tep_db_fetch_array($author_query);

          $image_location = DIR_FS_CATALOG . DIR_WS_CATALOG_IMAGES . $author['authors_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

		tep_remove_author($authors_id);

        if (isset($HTTP_POST_VARS['delete_products']) && ($HTTP_POST_VARS['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where authors_id = '" . (int)$authors_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            tep_remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set authors_id = '' where authors_id = '" . (int)$authors_id . "'");
        }

        tep_redirect(tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page']));
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
    $parameters = array();
	$query = tep_db_query("describe " . TABLE_AUTHORS . "");
	while ($row = tep_db_fetch_array($query)) {
	  $parameters[$row['Field']] = '';
	}

    $aInfo = new objectInfo($parameters);

    if (tep_not_null($HTTP_GET_VARS['aID']) && empty($HTTP_POST_VARS)) {
      $author_query = tep_db_query("select * from " . TABLE_AUTHORS . " where authors_id = '" . (int)$HTTP_GET_VARS['aID'] . "' and language_id = '" . (int)$languages_id . "'");
      $author = tep_db_fetch_array($author_query);

      $aInfo->objectInfo($author);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $aInfo->objectInfo($HTTP_POST_VARS);
      $authors_name = array_map("stripslashes", $HTTP_POST_VARS['authors_name']);
      $authors_description = array_map("stripslashes", $HTTP_POST_VARS['authors_description']);
    }

    $languages = tep_get_languages();

	$form_action = (tep_not_null($HTTP_GET_VARS['aID'])) ? 'save' : 'insert';
	echo tep_draw_form('authors', FILENAME_AUTHORS, tep_get_all_get_params(array('aID', 'action')) . (tep_not_null($HTTP_GET_VARS['aID']) ? '&aID=' . $HTTP_GET_VARS['aID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"') . (tep_not_null($HTTP_GET_VARS['aID']) ? tep_draw_hidden_field('authors_id', $aInfo->authors_id) : '');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='save' ? sprintf(TEXT_HEADING_EDIT_AUTHOR, $aInfo->authors_name) : TEXT_HEADING_NEW_AUTHOR; ?></td>
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
            <td width="250" class="main"><?php if ($i == 0) echo TEXT_AUTHORS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('authors_name[' . $languages[$i]['id'] . ']', (isset($authors_name[$languages[$i]['id']]) ? $authors_name[$languages[$i]['id']] : tep_get_author_info($aInfo->authors_id, $languages[$i]['id'])), 'size="40"'); ?></td>
          </tr>
<?php
    }
?>
		</table>
<?php
	echo tep_load_blocks($aInfo->authors_id, 'author');
?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_AUTHORS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('authors_image') . (tep_not_null($aInfo->authors_image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $aInfo->authors_image : '') . '</span>'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_AUTHORS_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($authors_description[$languages[$i]['id']]) ? $authors_description[$languages[$i]['id']] : tep_get_author_info($aInfo->authors_id, $languages[$i]['id'], 'authors_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('authors_description[' . $languages[$i]['id'] . ']');
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
            <td width="250" class="main"><?php echo TEXT_AUTHORS_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $aInfo->sort_order, 'size="5"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_AUTHORS_PATH; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_AUTHORS, '', 'NONSSL', false) . tep_draw_input_field('authors_path', $aInfo->authors_path, 'size="' . (tep_not_null($aInfo->authors_path) ? (strlen($aInfo->authors_path)-1) : '7') . '"') . '/'; ?></td>
          </tr>
		</table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	echo tep_draw_hidden_field('date_added', (tep_not_null($aInfo->date_added) ? $aInfo->date_added : date('Y-m-d')));

	if (tep_not_null($HTTP_GET_VARS['aID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_AUTHORS, tep_get_all_get_params(array('aID', 'action')) . (tep_not_null($HTTP_GET_VARS['aID']) ? '&aID=' . $HTTP_GET_VARS['aID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
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
			  <?php echo tep_draw_form('authors', FILENAME_AUTHORS, '', 'get'); ?>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_AUTHORS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$search = tep_db_prepare_input($HTTP_GET_VARS['search']);
	$authors_query_raw = "select * from " . TABLE_AUTHORS . " where language_id = '" . (int)$languages_id . "'" . (tep_not_null($search) ? " and (authors_name like '%" . str_replace(" ", "%' and authors_name like '%", $search) . "%')" : "") . " order by sort_order, authors_name";
	$authors_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $authors_query_raw, $authors_query_numrows);
	$authors_query = tep_db_query($authors_query_raw);
	while ($authors = tep_db_fetch_array($authors_query)) {
	  if ((!isset($HTTP_GET_VARS['aID']) || (isset($HTTP_GET_VARS['aID']) && ($HTTP_GET_VARS['aID'] == $authors['authors_id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
		$author_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where authors_id = '" . (int)$authors['authors_id'] . "'");
		$author_products = tep_db_fetch_array($author_products_query);

		$aInfo_array = array_merge($authors, $author_products);
		$aInfo = new objectInfo($aInfo_array);
	  }

	  if (isset($aInfo) && is_object($aInfo) && ($authors['authors_id'] == $aInfo->authors_id)) {
		echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $authors['authors_id'] . '&action=edit') . '\'">' . "\n";
	  } else {
		echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $authors['authors_id']) . '\'">' . "\n";
	  }
?>
                <td class="dataTableContent"><?php echo $authors['authors_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($aInfo) && is_object($aInfo) && ($authors['authors_id'] == $aInfo->authors_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $authors['authors_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $authors_split->display_count($authors_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $authors_split->display_links($authors_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'action', 'aID'))); ?></td>
                  </tr>
<?php
	if (empty($action)) {
?>
				  <tr>
					<td align="right" colspan="2" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_AUTHOR . '</strong>');

		$contents = array('form' => tep_draw_form('authors', FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $aInfo->authors_id . '&action=deleteconfirm'));
		$contents[] = array('text' => TEXT_DELETE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $aInfo->authors_name . '</strong>');
		$contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

		if ($aInfo->products_count > 0) {
		  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count));
		  $contents[] = array('text' => tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
		}

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $aInfo->authors_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (isset($aInfo) && is_object($aInfo)) {
		  $heading[] = array('text' => '<strong>' . $aInfo->authors_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $aInfo->authors_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_AUTHORS, 'search=' . urlencode($search) . '&page=' . $HTTP_GET_VARS['page'] . '&aID=' . $aInfo->authors_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($aInfo->date_added));
		  if (tep_not_null($aInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($aInfo->last_modified));
		  $contents[] = array('text' => '<br>' . tep_info_image($aInfo->authors_image, $aInfo->authors_name));
		  $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_count);
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