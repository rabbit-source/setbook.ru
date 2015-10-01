<?php
  require('includes/application_top.php');

  function tep_get_pages_info($pages_id, $language_id, $field = 'pages_name') {
    $pages_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_PAGES . " where pages_id = '" . (int)$pages_id . "' and language_id = '" . (int)$language_id . "'");
    $pages = tep_db_fetch_array($pages_query);

    return $pages[$field];
  }

  function tep_get_translation_info($pages_translation_id, $language_id, $field = 'pages_translation_description') {
	global $languages_id;
	if (!tep_not_null($language_id)) $language_id = $languages_id;
    $pages_translation_query = tep_db_query("select " . tep_db_input($field) . " from " . TABLE_PAGES_TRANSLATION . " where pages_translation_id = '" . (int)$pages_translation_id . "' and language_id = '" . (int)$language_id . "'");
    $pages_translation = tep_db_fetch_array($pages_translation_query);

    return $pages_translation[$field];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (DEBUG_MODE=='off') {
	if (in_array($action, array('new_translation', 'insert_translation', 'delete_translation', 'delete_translation_confirm'))) {
	  tep_redirect(tep_href_link(FILENAME_PAGES, tep_get_all_get_params(array('action'))));
	}
  }

  $added = 0;
  $deleted = 0;
  $languages = tep_get_languages();
  $files = tep_get_files(DIR_FS_CATALOG . 'includes/content/');
  $message = '';
  reset($files);
  while (list(, $filename) = each($files)) {
	$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
	while ($shops = tep_db_fetch_array($shops_query)) {
	  tep_db_select_db($shops['shops_database']);
	  $store_name_query = tep_db_query("select configuration_value as store_name from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
	  $store_name = tep_db_fetch_array($store_name_query);
	  $page_check_query = tep_db_query("select count(*) as total from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input($filename) . "'");
	  $page_check = tep_db_fetch_array($page_check_query);
	  if ($page_check['total']=='0') {
		$max_id_query = tep_db_query("select max(pages_id) + 1 as new_id from " . TABLE_PAGES . "");
		$max_id = tep_db_fetch_array($max_id_query);
		$pages_id = (int)$max_id['new_id'] + 1;
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $sql_data_array = array('pages_name' => tep_db_input($filename),
	  							  'pages_filename' => tep_db_input($filename),
								  'date_added' => 'now()',
								  'pages_id' => $pages_id,
								  'language_id' => $languages[$i]['id']);

		  tep_db_perform(TABLE_PAGES, $sql_data_array);
		}
		$added ++;
		$message .= (tep_not_null($message) ? '<br />' . "\n" : '') . $store_name['store_name'] . ': ' . $filename;
	  }
	}
	tep_db_select_db(DB_DATABASE);
  }
  if ($added > 0) {
	$messageStack->add(sprintf(TEXT_SUCCESS_PAGES_ADDED, $added) . '<div style="padding-left: 30px;">' . $message . '</div>', 'success');
  }

  $message = '';
  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
  while ($shops = tep_db_fetch_array($shops_query)) {
	tep_db_select_db($shops['shops_database']);
	$store_name_query = tep_db_query("select configuration_value as store_name from " . TABLE_CONFIGURATION . " where configuration_key = 'STORE_NAME'");
	$store_name = tep_db_fetch_array($store_name_query);
	$pages_query = tep_db_query("select distinct pages_filename from " . TABLE_PAGES . "");
	while ($pages = tep_db_fetch_array($pages_query)) {
	  if (!in_array($pages['pages_filename'], $files)) {
		$page_info_query = tep_db_query("select distinct pages_id from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input($pages['pages_filename']) . "'");
		while ($page_info = tep_db_fetch_array($page_info_query)) {
		  tep_db_query("delete from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input($pages['pages_filename']) . "'");
		  tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'page' and content_id = '" . (int)$page_info['pages_id'] . "'");
		  tep_db_query("delete from " . TABLE_TEMPLATES_TO_CONTENT . " where content_type = 'page' and content_id = '" . (int)$page_info['pages_id'] . "'");
		}
		tep_db_query("delete from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input($pages['pages_filename']) . "'");

		$deleted ++;
		$message .= (tep_not_null($message) ? '<br />' . "\n" : '') . $store_name['store_name'] . ': ' . $pages['pages_filename'];
	  }
	}
  }
  tep_db_select_db(DB_DATABASE);
  if ($deleted > 0) {
	$messageStack->add(sprintf(TEXT_SUCCESS_PAGES_DELETED, $deleted) . '<div style="padding-left: 30px;">' . $message . '</div>', 'warning');
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_page':
        if (isset($HTTP_GET_VARS['pID'])) {
		  $pages_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
		}
		$languages = tep_get_languages();
		$pages_name_array = $HTTP_POST_VARS['pages_name'];
		$pages_additional_description_array = $HTTP_POST_VARS['pages_additional_description'];
		$pages_description_array = $HTTP_POST_VARS['pages_description'];
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $description = str_replace('\\\"', '"', $pages_description_array[$language_id]);
		  $description = str_replace('\"', '"', $description);
		  $description = str_replace("\\\'", "\'", $description);
		  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		  $description = str_replace(' - ', ' &ndash; ', $description);
		  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

		  $additional_description = str_replace('\\\"', '"', $pages_additional_description_array[$language_id]);
		  $additional_description = str_replace('\"', '"', $additional_description);
		  $additional_description = str_replace("\\\'", "\'", $additional_description);
		  $additional_description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $additional_description);
		  $additional_description = str_replace('="' . HTTP_SERVER . '/', '="/', $additional_description);
		  $additional_description = str_replace(' - ', ' &ndash; ', $additional_description);
		  $additional_description = str_replace(' &mdash; ', ' &ndash; ', $additional_description);

		  $sql_data_array = array('last_modified' => 'now()',
								  'pages_name' => tep_db_prepare_input($pages_name_array[$language_id]),
								  'pages_additional_description' => $additional_description,
								  'pages_description' => $description);
		  tep_db_perform(TABLE_PAGES, $sql_data_array, 'update', "pages_id = '" . (int)$pages_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		}

		tep_update_blocks($pages_id, 'page');

        tep_redirect(tep_href_link(FILENAME_PAGES, 'pID=' . $pages_id));
        break;
      case 'insert_translation':
      case 'update_translation':
		$p_query = tep_db_query("select pages_filename from " . TABLE_PAGES . " where pages_id = '" . (int)$HTTP_GET_VARS['pPath'] . "'");
		$p = tep_db_fetch_array($p_query);

		$error = false;
        if (isset($HTTP_POST_VARS['translation_id'])) {
		  $translation_id = tep_db_prepare_input($HTTP_POST_VARS['translation_id']);
		} else {
		  $max_id_query = tep_db_query("select max(pages_translation_id) as new_id from " . TABLE_PAGES_TRANSLATION . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $translation_id = (int)$max_id['new_id'] + 1;
		}

        if (isset($HTTP_POST_VARS['translation_key'])) {
		  $translation_key = tep_db_prepare_input($HTTP_POST_VARS['translation_key']);
		  $translation_key = preg_replace('/[^_\d\w]/i', '_', $translation_key);
		  $translation_key = preg_replace('/_+/', '_', $translation_key);
		}

		$disabled_names = array();
		$translation_exists_query = tep_db_query("select distinct pages_translation_key from " . TABLE_PAGES_TRANSLATION . " where (pages_filename = '" . tep_db_input(basename($p['pages_filename'])) . "' || pages_filename = '') and pages_translation_id <> '" . (int)$translation_id . "'");
		while ($translation_exists = tep_db_fetch_array($translation_exists_query)) {
		  $disabled_names[] = $translation_exists['pages_translation_key'];
		}

		if ($translation_key == '' && $action == 'insert_translation') {
		  $messageStack->add(ERROR_EMPTY_TRANSLATION_KEY, 'error');
		  $error = true;
		} elseif ($action == 'insert_translation' && in_array($translation_key, $disabled_names)) {
		  $messageStack->add(sprintf(ERROR_TRANSLATION_KEY_EXIST, $translation_key), 'error');
		  $error = true;
		}

		if (!$error) {
		  $translation_value_array = $HTTP_POST_VARS['translation_value'];
		  $translation_description_array = $HTTP_POST_VARS['translation_description'];
		  $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

			$sql_data_array = array('pages_filename' => tep_db_input(basename($p['pages_filename'])),
		  							'pages_translation_value' => tep_db_prepare_input($translation_value_array[$language_id]));

			$translation_check_query = tep_db_query("select count(*) as total from " . TABLE_PAGES_TRANSLATION . " where pages_translation_id = '" . (int)$translation_id . "' and language_id = '" . (int)$language_id . "'");
			$translation_check = tep_db_fetch_array($translation_check_query);

			if (DEBUG_MODE=='on') {
			  $sql_data_array['pages_translation_key'] = tep_db_prepare_input($translation_key);
			  $sql_data_array['pages_translation_description'] = tep_db_prepare_input($translation_description_array[$language_id]);
			  $sql_data_array['sort_order'] = tep_db_input($sort_order);
			} elseif ($translation_check['total'] == 0) {
			  $translation_info_query = tep_db_query("select pages_translation_key, pages_translation_description, sort_order from " . TABLE_PAGES_TRANSLATION . " where pages_translation_id = '" . (int)$translation_id . "' limit 1");
			  $translation_info = tep_db_fetch_array($translation_info_query);

			  $sql_data_array['pages_translation_key'] = tep_db_prepare_input($translation_info['pages_translation_key']);
			  $sql_data_array['pages_translation_description'] = tep_db_prepare_input($translation_info['pages_translation_description']);
			  $sql_data_array['sort_order'] = tep_db_prepare_input($translation_info['sort_order']);
			}

			if ($action == 'insert_translation' || $translation_check['total'] == 0) {
			  $insert_sql_data = array('pages_translation_id' => $translation_id,
									   'language_id' => $languages[$i]['id']);
			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_PAGES_TRANSLATION, $sql_data_array);
			} elseif ($action == 'update_translation') {
			  tep_db_perform(TABLE_PAGES_TRANSLATION, $sql_data_array, 'update', "pages_translation_id = '" . (int)$translation_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  tep_redirect(tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $translation_id));
		} else {
		  $action = ($action=='insert_translation' ? 'new_translation' : 'edit_translation');
		}
		break;
      case 'delete_translation_confirm':
        if (isset($HTTP_POST_VARS['translation_id'])) {
          $translation_id = tep_db_prepare_input($HTTP_POST_VARS['translation_id']);

		  tep_db_query("delete from " . TABLE_PAGES_TRANSLATION . " where pages_translation_id = '" . (int)$translation_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath']));
        break;
    }
  }

  if (tep_not_null($HTTP_GET_VARS['pPath'])) {
	$page_filename = tep_get_pages_info($HTTP_GET_VARS['pPath'], $languages_id, 'pages_filename');
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
  if ($action == 'edit_page') {
    $parameters = array('pages_id' => '',
						'pages_name' => '',
						'pages_additional_description' => '',
						'pages_description' => '',
						'date_added' => '',
						'pages_filename' => '',
						'last_modified' => '');

    $pInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['pID']) && empty($HTTP_POST_VARS)) {
      $page_query = tep_db_query("select * from " . TABLE_PAGES . " where pages_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and language_id = '" . (int)$languages_id . "'");
      $page = tep_db_fetch_array($page_query);
      $pInfo->objectInfo($page);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $pInfo->objectInfo($HTTP_POST_VARS);
      $pages_name = array_map("stripslashes", $HTTP_POST_VARS['pages_name']);
      $page_query = tep_db_query("select * from " . TABLE_PAGES . " where pages_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and language_id = '" . (int)$languages_id . "'");
      $page = tep_db_fetch_array($page_query);
	  $pInfo->pages_name = $page['pages_name'];
	  $pInfo->pages_additional_description = $page['pages_additional_description'];
	  $pInfo->pages_description = $page['pages_description'];
	  $pInfo->pages_filename = $page['pages_filename'];
    }

    $languages = tep_get_languages();

	echo tep_draw_form('new_information', FILENAME_PAGES, 'pID=' . $HTTP_GET_VARS['pID'] . '&action=update_page', 'post');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo sprintf(HEADING_TITLE_1, $pInfo->pages_filename, $pInfo->pages_name); ?></td>
      </tr>
	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
	  </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="1">
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_PAGE_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('pages_name[' . $languages[$i]['id'] . ']', (isset($pages_name[$languages[$i]['id']]) ? $pages_name[$languages[$i]['id']] : tep_get_pages_info($pInfo->pages_id, $languages[$i]['id'])), 'size="55"'); ?></td>
          </tr>
<?php
	}
?>
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_PAGE_ADDITIONAL_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('pages_additional_description[' . $languages[$i]['id'] . ']', 'soft', '55', '5', (isset($pages_additional_description[$languages[$i]['id']]) ? $pages_additional_description[$languages[$i]['id']] : tep_get_pages_info($pInfo->pages_id, $languages[$i]['id'], 'pages_additional_description'))); ?></td>
          </tr>
<?php
	}
?>
		</table>
<?php
	tep_load_blocks($pInfo->pages_id, 'page');
?>
		<table border="0" width="100%" cellspacing="0" cellpadding="1">
		  <tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
		  </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_PAGE_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($pages_description[$languages[$i]['id']]) ? $pages_description[$languages[$i]['id']] : tep_get_pages_info($pInfo->pages_id, $languages[$i]['id'], 'pages_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('pages_description[' . $languages[$i]['id'] . ']');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	}
?>
		</table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo tep_draw_hidden_field('date_added', (tep_not_null($pInfo->date_added) ? $pInfo->date_added : date('Y-m-d'))) . tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_PAGES, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo tep_not_null($HTTP_GET_VARS['pPath']) ? sprintf(HEADING_TITLE, $page_filename) : HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
	if (tep_not_null($HTTP_GET_VARS['pPath'])) {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
<?php
	  if (DEBUG_MODE=='on') {
?>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRANSLATION_KEY; ?></td>
<?php
	  }
?>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRANSLATION_VALUE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRANSLATION_DESCRIPTION; ?></td>
                <td class="dataTableHeadingContent" align="ךרןנו">&nbsp;<?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $translation_query_raw = "select * from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($page_filename)) . "' and language_id = '" . $languages_id . "' order by sort_order, pages_translation_key";
	  $translation_query = tep_db_query($translation_query_raw);
	  while ($translation = tep_db_fetch_array($translation_query)) {
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $translation['pages_translation_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
		  $tInfo_array = $translation;
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($translation['pages_translation_id'] == $tInfo->pages_translation_id)) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $translation['pages_translation_id'] . '&action=edit_translation') . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $translation['pages_translation_id']) . '\'">' . "\n";
		}
?>
<?php
		if (DEBUG_MODE=='on') {
?>
                <td class="dataTableContent"><?php echo '[' . $translation['sort_order'] . ']&nbsp;' . $translation['pages_translation_key']; ?></td>
<?php
		}
?>
                <td class="dataTableContent"><?php echo $translation['pages_translation_value']; ?></td>
                <td class="dataTableContent"><?php echo $translation['pages_translation_description']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($translation['pages_translation_id'] == $tInfo->pages_translation_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $translation['pages_translation_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
?>
              <tr>
                <td colspan="<?php echo DEBUG_MODE=='on' ? '4' : '3'; ?>"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_PAGES, 'pID=' . $HTTP_GET_VARS['pPath']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>' . (DEBUG_MODE=='on' ? '&nbsp;<a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=new_translation') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>' : ''); ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
			</table></td>
<?php
	} else {
?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAGES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $pages_query_raw = "select * from " . TABLE_PAGES . " where language_id = '" . $languages_id . "' order by pages_filename";
	  $pages_query = tep_db_query($pages_query_raw);
	  while ($pages = tep_db_fetch_array($pages_query)) {
		if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $pages['pages_id']))) && !isset($pInfo) && (substr($action, 0, 3) != 'new')) {
		  $pInfo_array = $pages;
		  $pInfo = new objectInfo($pInfo_array);
		}

		if (isset($pInfo) && is_object($pInfo) && ($pages['pages_id'] == $pInfo->pages_id)) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAGES, 'pID=' . $pages['pages_id'] . '&action=edit_page') . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAGES, 'pID=' . $pages['pages_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $pages['pages_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;<strong>' . $pages['pages_filename'] . '</strong> [' . $pages['pages_name'] . ']'; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($pages['pages_id'] == $pInfo->pages_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_PAGES, 'pID=' . $pages['pages_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
?>
            </table></td>
<?php
	}
	$heading = array();
	$contents = array();
	switch ($action) {
      case 'delete_translation':
        $heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_TRANSLATION . '</strong>');

        $contents = array('form' => tep_draw_form('translation', FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=delete_translation_confirm') . tep_draw_hidden_field('translation_id', $tInfo->pages_translation_id));
        $contents[] = array('text' => TEXT_DELETE_TRANSLATION_INTRO);
        $contents[] = array('text' => '<br><strong>' . $tInfo->pages_translation_description . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $tInfo->pages_translation_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'new_translation':
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_NEW_TRANSLATION . '</strong>');

		$contents = array('form' => tep_draw_form('new_translation', FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&action=insert_translation', 'post'));
		$contents[] = array('text' => TEXT_NEW_TRANSLATION_INTRO);

		$translation_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $translation_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('translation_description[' . $languages[$i]['id'] . ']', 'soft', '36', '3');
		}
		$contents[] = array('text' => '<br>' . TEXT_TRANSLATION_DESCRIPTION . $translation_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', '', 'size="5"'));

		$contents[] = array('text' => '<br>' . TEXT_TRANSLATION_KEY . '<br>' . tep_draw_input_field('translation_key', '', 'size="41"'));

		$translation_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $translation_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('translation_value[' . $languages[$i]['id'] . ']', 'soft', '37', '7', $HTTP_POST_VARS['translation_value'][$languages[$i]['id']]);
		}
		$contents[] = array('text' => '<br>' . TEXT_TRANSLATION_VALUE . $translation_inputs_string);

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'edit_translation':
		$heading[] = array('text' => '<strong>' . TEXT_HEADING_EDIT_TRANSLATION . '</strong>');

		$contents = array('form' => tep_draw_form('edit_translation', FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $tInfo->pages_translation_id . '&action=update_translation', 'post') . tep_draw_hidden_field('translation_id', $tInfo->pages_translation_id));
		$contents[] = array('text' => TEXT_EDIT_TRANSLATION_INTRO);

		if (DEBUG_MODE=='on') {
		  $translation_inputs_string = '';
		  $languages = tep_get_languages();
		  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$translation_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('translation_description[' . $languages[$i]['id'] . ']', 'soft', '36', '3', tep_get_translation_info($tInfo->pages_translation_id, $languages[$i]['id']));
		  }
		  $contents[] = array('text' => '<br>' . TEXT_TRANSLATION_DESCRIPTION . (DEBUG_MODE=='on' ? $translation_inputs_string : $tInfo->pages_translation_description));

		  $contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="5"'));

		  $contents[] = array('text' => '<br>' . TEXT_TRANSLATION_KEY . '<br>' . tep_draw_input_field('translation_key', $tInfo->pages_translation_key, 'size="41"'));
		} else {
		  $contents[] = array('text' => '<br>' . TEXT_TRANSLATION_DESCRIPTION . '<br>' . $tInfo->pages_translation_description);
		}

		$translation_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $translation_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('translation_value[' . $languages[$i]['id'] . ']', 'soft', '37', '7', tep_get_translation_info($tInfo->pages_translation_id, $languages[$i]['id'], 'pages_translation_value'));
		}
		$contents[] = array('text' => '<br>' . TEXT_TRANSLATION_VALUE . $translation_inputs_string);

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $tInfo->pages_translation_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (isset($pInfo) && is_object($pInfo)) {
		  $heading[] = array('text' => '<strong>' . $pInfo->pages_filename . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PAGES, 'pID=' . $pInfo->pages_id . '&action=edit_page') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
		  $contents[] = array('text' => '<br>' . $pInfo->pages_description);
		  $contents[] = array('text' => '<br>' . TEXT_PAGE_FILENAME . '<br>' . $pInfo->pages_filename);
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . '<br>' . tep_datetime_short($pInfo->date_added));
		  if (tep_not_null($pInfo->last_modified)) $contents[] = array('text' => '<br>' . TEXT_LAST_MODIFIED . '<br>' . tep_datetime_short($pInfo->last_modified));
	    } elseif (isset($tInfo) && is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_TRANSLATION . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $tInfo->pages_translation_id . '&action=edit_translation') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>' . (DEBUG_MODE=='on' ? ' <a href="' . tep_href_link(FILENAME_PAGES, 'pPath=' . $HTTP_GET_VARS['pPath'] . '&tID=' . $tInfo->pages_translation_id . '&action=delete_translation') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' : ''));
		  $contents[] = array('text' => '<br>' . $tInfo->pages_translation_description);
		  if (DEBUG_MODE=='on') $contents[] = array('text' => '<br>' . TEXT_TRANSLATION_KEY . '<br>' . $tInfo->pages_translation_key);
		  $contents[] = array('text' => '<br>' . TEXT_TRANSLATION_VALUE . '<br>' . $tInfo->pages_translation_value);
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