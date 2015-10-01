<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $gPath = (isset($HTTP_GET_VARS['gPath']) ? (int)$HTTP_GET_VARS['gPath'] : 0);

  $banner_extension = tep_banner_image_extension();

  function tep_remove_banner($banners_id, $delete_image = true) {
	global $messageStack, $banner_extension;

	if ($delete_image==true) {
	  $banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
	  $banner = tep_db_fetch_array($banner_query);

	  if (is_file(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
		if (is_writeable(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
		  unlink(DIR_FS_CATALOG_IMAGES . $banner['banners_image']);
		} else {
		  $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
		}
	  } else {
		$messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
	  }
	}

	tep_db_query("delete from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
	tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");
	tep_db_query("delete from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'banner' and banners_conditions_type_id = '" . (int)$banners_id . "'");

	if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
	  if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
		if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
		  unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
		}
	  }

	  if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
		if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
		  unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
		}
	  }

	  if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
		if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
		  unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
		}
	  }

	  if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
		if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
		  unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
		}
	  }
	}
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['bID']) && tep_not_null($HTTP_GET_VARS['bID'])) {
			tep_set_banner_status($HTTP_GET_VARS['bID'], $HTTP_GET_VARS['flag']);
		  } elseif (isset($HTTP_GET_VARS['gID']) && tep_not_null($HTTP_GET_VARS['gID'])) {
			tep_db_query("update " . TABLE_BANNERS_GROUPS . " set status = '" . (int)$HTTP_GET_VARS['flag'] . "' where banners_groups_id = '" . (int)$HTTP_GET_VARS['gID'] . "'");
		  }
        }

        tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['bID']) ? 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $HTTP_GET_VARS['bID'] : 'gID=' . $HTTP_GET_VARS['gID'])));
        break;
      case 'insert_group':
      case 'update_group':
        if (isset($HTTP_POST_VARS['groups_id'])) $groups_id = tep_db_prepare_input($HTTP_POST_VARS['groups_id']);

        $sql_data_array = array('banners_groups_name' => tep_db_prepare_input($HTTP_POST_VARS['groups_name']),
								'banners_groups_path' => tep_db_prepare_input($HTTP_POST_VARS['groups_path']));

        if ($action == 'insert_group') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_BANNERS_GROUPS, $sql_data_array);

          $groups_id = tep_db_insert_id();
        } elseif ($action == 'update_group') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_BANNERS_GROUPS, $sql_data_array, 'update', "banners_groups_id = '" . (int)$groups_id . "'");
        }

		$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
		while ($shops = tep_db_fetch_array($shops_query)) {
		  tep_db_select_db($shops['shops_database']);
		  tep_db_query("delete from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'group' and banners_conditions_type_id = '" . (int)$groups_id . "'");
		  reset($HTTP_POST_VARS['conditions_page_name']);
		  while (list($i, $page_address) = each($HTTP_POST_VARS['conditions_page_name'])) {
			$page_address = tep_db_prepare_input($page_address);
			if (tep_not_null($page_address)) {
			  tep_db_query("insert into " . TABLE_BANNERS_CONDITIONS . " (banners_conditions_type, banners_conditions_type_id, banners_conditions_page, banners_conditions_page_type, banners_conditions_page_consist, banners_conditions_equation) values ('group', '" . (int)$groups_id . "', '" . tep_db_input($page_address) . "', '" . tep_db_input($HTTP_POST_VARS['conditions_page_type'][$i]) . "', '" . tep_db_input($HTTP_POST_VARS['conditions_page_consist'][$i]) . "', '" . tep_db_input(tep_db_prepare_input($HTTP_POST_VARS['conditions_equation'])) . "')");
			}
		  }
		}
		tep_db_select_db(DB_DATABASE);

        tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups_id));
        break;
      case 'delete_group_confirm':
        if (isset($HTTP_POST_VARS['groups_id'])) {
          $groups_id = tep_db_prepare_input($HTTP_POST_VARS['groups_id']);

		  $banners_query = tep_db_query("select banners_id from " . TABLE_BANNERS . " where banners_groups_id = '" . (int)$groups_id . "'");
		  while ($banners = tep_db_fetch_array($banners_query)) {
			tep_remove_banner($banners['banners_id']);
		  }
		  tep_db_query("delete from " . TABLE_BANNERS_GROUPS . " where banners_groups_id = '" . (int)$groups_id . "'");

		  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' order by shops_default_status");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			tep_db_select_db($shops['shops_database']);
			tep_db_query("delete from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'group' and banners_conditions_type_id '" . (int)$groups_id . "'");
		  }
		  tep_db_select_db(DB_DATABASE);
		}

        tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER));
        break;
      case 'insert':
      case 'update':
        if (isset($HTTP_POST_VARS['banners_id'])) $banners_id = tep_db_prepare_input($HTTP_POST_VARS['banners_id']);
        $banners_title = tep_db_prepare_input($HTTP_POST_VARS['banners_title']);
        $banners_url = tep_db_prepare_input($HTTP_POST_VARS['banners_url']);
        $banners_html_text = tep_db_prepare_input($HTTP_POST_VARS['banners_html_text']);
        $banners_image_local = tep_db_prepare_input($HTTP_POST_VARS['banners_image_local']);
        $banners_image_target = tep_db_prepare_input($HTTP_POST_VARS['banners_image_target']);
        $db_image_location = '';
        $expires_date = tep_db_prepare_input($HTTP_POST_VARS['expires_date']);
        $expires_impressions = tep_db_prepare_input($HTTP_POST_VARS['expires_impressions']);
        $date_scheduled = tep_db_prepare_input($HTTP_POST_VARS['date_scheduled']);
        $banners_weight = tep_db_prepare_input($HTTP_POST_VARS['banners_weight']);
		$banners_use_group_conditions = tep_db_prepare_input($HTTP_POST_VARS['banners_use_group_conditions']);

        $banner_error = false;
        if (empty($banners_title)) {
          $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_html_text)) {
          if (empty($banners_image_local)) {
			$banners_image = new upload();
			if ($banners_image->upload('banners_image', DIR_FS_CATALOG_IMAGES . 'banners/' . $banners_image_target)) {
			} else {
              $banner_error = true;
            }
          }
        }

        if ($banner_error == false) {
          $db_image_location = 'banners/' . (tep_not_null($banners_image_local) ? $banners_image_local : $banners_image_target . $banners_image->filename);
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_groups_id' => $gPath,
                                  'banners_weight' => $banners_weight,
								  'banners_use_group_conditions' => $banners_use_group_conditions,
                                  'banners_html_text' => $banners_html_text);

          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_BANNERS, $sql_data_array);

            $banners_id = tep_db_insert_id();

            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            tep_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");

            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }

		  tep_db_query("delete from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'banner' and banners_conditions_type_id = '" . (int)$banners_id . "'");
		  if ($banners_use_group_conditions==0) {
			reset($HTTP_POST_VARS['conditions_page_name']);
			while (list($i, $page_address) = each($HTTP_POST_VARS['conditions_page_name'])) {
			  $page_address = tep_db_prepare_input($page_address);
			  if (tep_not_null($page_address)) {
				tep_db_query("insert into " . TABLE_BANNERS_CONDITIONS . " (banners_conditions_type, banners_conditions_type_id, banners_conditions_page, banners_conditions_page_type, banners_conditions_page_consist, banners_conditions_equation) values ('banner', '" . (int)$banners_id . "', '" . tep_db_input($page_address) . "', '" . tep_db_input($HTTP_POST_VARS['conditions_page_type'][$i]) . "', '" . tep_db_input($HTTP_POST_VARS['conditions_page_consist'][$i]) . "', '" . tep_db_input(tep_db_prepare_input($HTTP_POST_VARS['conditions_equation'])) . "')");
			  }
			}
		  }

          if (tep_not_null($expires_date)) {
            list($day, $month, $year) = explode('/', $expires_date);

            $expires_date = $year .
                            ((strlen($month) == 1) ? '0' . $month : $month) .
                            ((strlen($day) == 1) ? '0' . $day : $day);

            tep_db_query("update " . TABLE_BANNERS . " set expires_date = '" . tep_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
          } elseif (tep_not_null($expires_impressions)) {
            tep_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . tep_db_input($expires_impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
          }

          if (tep_not_null($date_scheduled)) {
            list($day, $month, $year) = explode('/', $date_scheduled);

            $date_scheduled = $year .
                              ((strlen($month) == 1) ? '0' . $month : $month) .
                              ((strlen($day) == 1) ? '0' . $day : $day);

            tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . tep_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
          }

          tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'gPath=' . $gPath . '&bID=' . $banners_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
		$banners_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);

		if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
		  $delete_banner_image = true;
		}

		tep_remove_banner($banners_id, $delete_banner_image);

		$messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

		tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'gPath=' . $gPath));
		break;
    }
  }

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  $pages_types = array('virtual' => TEXT_PAGES_TYPE_VIRTUAL, 'physical' => TEXT_PAGES_TYPE_PHYSICAL);
  $pages_types_array = array();
  reset($pages_types);
  while (list($k, $v) = each($pages_types)) {
	$pages_types_array[] = array('id' => $k, 'text' => $v);
  }

  $pages_consist = array('match' => TEXT_PAGES_CONSIST_MATCH, 'not_match' => TEXT_PAGES_CONSIST_NOT_MATCH, 'begin' => TEXT_PAGES_CONSIST_BEGIN, 'not_begin' => TEXT_PAGES_CONSIST_NOT_BEGIN, 'contain' => TEXT_PAGES_CONSIST_CONTAIN, 'not_contain' => TEXT_PAGES_CONSIST_NOT_CONTAIN, 'end' => TEXT_PAGES_CONSIST_END, 'not_end' => TEXT_PAGES_CONSIST_NOT_END);
  $pages_consist_array = array();
  reset($pages_consist);
  while (list($k, $v) = each($pages_consist)) {
	$pages_consist_array[] = array('id' => $k, 'text' => $v);
  }

  $banners_group_name = '';
  if ($gPath > 0) {
	$group_info_query = tep_db_query("select banners_groups_name from " . TABLE_BANNERS_GROUPS . " where banners_groups_id = '" . (int)$gPath . "'");
	$group_info = tep_db_fetch_array($group_info_query);
	$banners_group_name = $group_info['banners_groups_name'];
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE . (tep_not_null($banners_group_name) ? ' &raquo; ' . $banners_group_name : ''); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('expires_date' => '',
                        'date_scheduled' => '',
                        'banners_title' => '',
                        'banners_url' => '',
                        'banners_groups_id' => '',
                        'banners_image' => '',
                        'banners_html_text' => '',
                        'expires_impressions' => '',
						'banners_weight' => 0,
						'banners_use_group_conditions' => '1');

    $bInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['bID'])) {
      $form_action = 'update';

      $bID = tep_db_prepare_input($HTTP_GET_VARS['bID']);

      $banner_query = tep_db_query("select *, date_format(date_scheduled, '%d/%m/%Y') as date_scheduled, date_format(expires_date, '%d/%m/%Y') as expires_date from " . TABLE_BANNERS . " where banners_id = '" . (int)$bID . "'");
      $banner = tep_db_fetch_array($banner_query);

	  $conditions_array = array();
	  if ($banner['banners_use_group_conditions']=='0') {
		$conditions_query = tep_db_query("select * from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'banner' and banners_conditions_type_id = '" . $banner['banners_id'] . "' order by banners_conditions_id");
		while ($conditions = tep_db_fetch_array($conditions_query)) {
		  $conditions_array['conditions_page_type'][] = $conditions['banners_conditions_page_type'];
		  $conditions_array['conditions_page_consist'][] = $conditions['banners_conditions_page_consist'];
		  $conditions_array['conditions_page_name'][] = $conditions['banners_conditions_page'];
		  $conditions_array['conditions_equation'] = $conditions['banners_conditions_equation'];
		}
	  }

	  $banner_info = array_merge($banner, $conditions_array);
      $bInfo->objectInfo($banner_info);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $bInfo->objectInfo($HTTP_POST_VARS);
    }

	$weights_array = array();
	for ($i=0; $i<10; $i++) {
	  $weights_array[] = array('id' => $i, 'text' => ($i+1));
	}
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var dateExpires = new ctlSpiffyCalendarBox("dateExpires", "new_banner", "expires_date","btnDate1","<?php echo $bInfo->expires_date; ?>",scBTNMODE_CUSTOMBLUE);
  var dateScheduled = new ctlSpiffyCalendarBox("dateScheduled", "new_banner", "date_scheduled","btnDate2","<?php echo $bInfo->date_scheduled; ?>",scBTNMODE_CUSTOMBLUE);
</script>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('new_banner', FILENAME_BANNER_MANAGER, 'gPath=' . $gPath . (isset($HTTP_GET_VARS['page']) ? '&page=' . $HTTP_GET_VARS['page'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo tep_draw_hidden_field('banners_id', $bID); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" width="250"><?php echo TEXT_BANNERS_TITLE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('banners_title', $bInfo->banners_title, 'size="43"', true); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BANNERS_URL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('banners_url', $bInfo->banners_url, 'size="43"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_file_field('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br>' . DIR_FS_CATALOG_IMAGES . 'banners/' . tep_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? preg_replace('/^banners\//', '', $bInfo->banners_image) : '')); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
            <td class="main"><?php echo DIR_FS_CATALOG_IMAGES . 'banners/' . tep_draw_input_field('banners_image_target', ''); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" width="250" class="main"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
            <td class="main"><?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '5', $bInfo->banners_html_text); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BANNERS_WEIGHT . '<br>' . TEXT_BANNERS_WEIGHT_TEXT; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('banners_weight', $weights_array, $bInfo->banners_weight); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?><br><small>(dd/mm/yyyy)</small></td>
            <td valign="top" class="main"><script language="javascript">dateScheduled.writeControl(); dateScheduled.dateFormat="dd/MM/yyyy";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" width="250" class="main"><?php echo TEXT_BANNERS_EXPIRES_ON; ?><br><small>(dd/mm/yyyy)</small></td>
            <td class="main"><script language="javascript">dateExpires.writeControl(); dateExpires.dateFormat="dd/MM/yyyy";</script><?php echo TEXT_BANNERS_OR_AT . '<br>' . tep_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td width="250" class="main"><?php echo TEXT_BANNERS_CONDITIONS; ?></td>
			<td class="main"><?php echo tep_draw_radio_field('banners_use_group_conditions', '1', ($bInfo->banners_use_group_conditions==1), '', 'onclick="document.getElementById(\'conditions\').style.display = \'none\';"') . TEXT_BANNERS_GROUP_CONDITIONS . '<br>' . tep_draw_radio_field('banners_use_group_conditions', '0', ($bInfo->banners_use_group_conditions==0), '', 'onclick="document.getElementById(\'conditions\').style.display = \'block\';"') . TEXT_BANNERS_OTHER_CONDITIONS; ?></td>
		  </tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="2" id="conditions" style="display: <?php echo ($bInfo->banners_use_group_conditions=='1' ? 'none' : 'block'); ?>;">
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	for ($i=0; $i<10; $i++) {
?>
          <tr>
            <td class="main" width="250"><?php echo sprintf(TEXT_CONDITION_N, ($i+1)); ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('conditions_page_type[]', $pages_types_array, $bInfo->conditions_page_type[$i]) . ' ' . tep_draw_pull_down_menu('conditions_page_consist[]', $pages_consist_array, $bInfo->conditions_page_consist[$i]) . ' ' . tep_draw_input_field('conditions_page_name[]', $bInfo->conditions_page_name[$i], 'size="30" id="condition_field_' . $i . '"'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td class="main" width="250"><?php echo TEXT_CONDITION_EQUATION; ?></td>
            <td class="main"><?php echo tep_draw_input_field('conditions_equation', $gInfo->conditions_equation, 'size="30"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_BANNER_NOTE . '<br>' . TEXT_BANNERS_INSERT_NOTE . '<br>' . TEXT_BANNERS_EXPIRCY_NOTE . '<br>' . TEXT_BANNERS_SCHEDULE_NOTE . '<br>' . TEXT_BANNERS_CONDITIONS_NOTE; ?></td>
            <td class="main" align="right" valign="top" nowrap><?php echo (($form_action == 'insert') ? tep_image_submit('button_new_record.gif', IMAGE_NEW_RECORD) : tep_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gPath=' . $gPath . (isset($HTTP_GET_VARS['page']) ? '&page=' . $HTTP_GET_VARS['page'] : '') . (isset($HTTP_GET_VARS['bID']) ? '&bID=' . $HTTP_GET_VARS['bID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
	if ((int)$gPath == 0) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_GROUPS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $rows = 0;
	  $groups_query = tep_db_query("select * from " . TABLE_BANNERS_GROUPS . " order by banners_groups_name");
	  while ($groups = tep_db_fetch_array($groups_query)) {
		$groups_count++;
		$rows++;

		if ((!isset($HTTP_GET_VARS['gID']) && !isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['gID']) && ($HTTP_GET_VARS['gID'] == $groups['banners_groups_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
		  $group_banners_query = tep_db_query("select count(*) as banners_count from " . TABLE_BANNERS . " where banners_groups_id = '" . $groups['banners_groups_id'] . "'");
		  $group_banners = tep_db_fetch_array($group_banners_query);

		  $k = 0;
		  unset($condition_equation);
		  $conditions_array = array();
		  $temp_string = '';
		  $conditions_query = tep_db_query("select * from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'group' and banners_conditions_type_id = '" . $groups['banners_groups_id'] . "' order by banners_conditions_id");
		  while ($conditions = tep_db_fetch_array($conditions_query)) {
			$conditions_array['conditions_page_type'][] = $conditions['banners_conditions_page_type'];
			$conditions_array['conditions_page_consist'][] = $conditions['banners_conditions_page_consist'];
			$conditions_array['conditions_page_name'][] = $conditions['banners_conditions_page'];
			$conditions_array['conditions_equation'] = $conditions['banners_conditions_equation'];

			if (tep_not_null($conditions['banners_conditions_equation'])) {
			  if (!isset($condition_equation)) {
				$condition_equation = $conditions['banners_conditions_equation'];
				$condition_equation = str_replace('|', TEXT_CONDITION_OR, $condition_equation);
				$condition_equation = str_replace('&', TEXT_CONDITION_AND, $condition_equation);
				if (tep_not_null($condition_equation)) {
				  preg_match_all('/[^\d]*(\d+)[^\d]*/', $condition_equation, $regs);
				  reset($regs[0]);
				  while (list($k) = each($regs[0])) {
					$temp_string .= str_replace($regs[1][$k], sprintf(TEXT_CONDITION_N, ($k+1)), $regs[0][$k]);
				  }
				}
			  }
			} else {
			  $temp_string .= (tep_not_null($temp_string) ? ' ' . TEXT_CONDITION_OR . ' ' : '') . sprintf(TEXT_CONDITION_N, ($k+1));
			  $k ++;
			}
		  }
		  $conditions_array['conditions_equation_string'] = $temp_string;

		  $gInfo_array = array_merge($groups, $group_banners, $conditions_array);
		  $gInfo = new objectInfo($gInfo_array);
		}

		if (isset($gInfo) && is_object($gInfo) && ($groups['banners_groups_id'] == $gInfo->banners_groups_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups['banners_groups_id'] . '&action=edit_group') . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups['banners_groups_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gPath=' . $groups['banners_groups_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;<strong>' . $groups['banners_groups_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="right">
<?php
		if ($groups['status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups['banners_groups_id'] . '&action=setflag&flag=0') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups['banners_groups_id'] . '&action=setflag&flag=1') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($gInfo) && is_object($gInfo) && ($groups['banners_groups_id'] == $gInfo->banners_groups_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $groups['banners_groups_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
	} else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BANNERS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_WEIGHT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATISTICS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $banners_query_raw = "select * from " . TABLE_BANNERS . " where banners_groups_id = '" . (int)$gPath . "' order by banners_title";
	  $banners_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
	  $banners_query = tep_db_query($banners_query_raw);
	  while ($banners = tep_db_fetch_array($banners_query)) {
		$info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners['banners_id'] . "'");
		$info = tep_db_fetch_array($info_query);

		if ((!isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $banners['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
		  $k = 0;
		  unset($condition_equation);
		  $conditions_array = array();
		  $temp_string = '';
		  $conditions_query = tep_db_query("select * from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = 'banner' and banners_conditions_type_id = '" . $banners['banners_id'] . "' order by banners_conditions_id");
		  while ($conditions = tep_db_fetch_array($conditions_query)) {
			$conditions_array['conditions_page_type'][] = $conditions['banners_conditions_page_type'];
			$conditions_array['conditions_page_consist'][] = $conditions['banners_conditions_page_consist'];
			$conditions_array['conditions_page_name'][] = $conditions['banners_conditions_page'];
			$conditions_array['conditions_equation'] = $conditions['banners_conditions_equation'];

			if (tep_not_null($conditions['banners_conditions_equation'])) {
			  if (!isset($condition_equation)) {
				$condition_equation = $conditions['banners_conditions_equation'];
				$condition_equation = str_replace('|', TEXT_CONDITION_OR, $condition_equation);
				$condition_equation = str_replace('&', TEXT_CONDITION_AND, $condition_equation);
				if (tep_not_null($condition_equation)) {
				  preg_match_all('/[^\d]*(\d+)[^\d]*/', $condition_equation, $regs);
				  reset($regs[0]);
				  while (list($k) = each($regs[0])) {
					$temp_string .= str_replace($regs[1][$k], sprintf(TEXT_CONDITION_N, ($k+1)), $regs[0][$k]);
				  }
				}
			  }
			} else {
			  $temp_string .= (tep_not_null($temp_string) ? ' ' . TEXT_CONDITION_OR . ' ' : '') . sprintf(TEXT_CONDITION_N, ($k+1));
			  $k ++;
			}
		  }
		  $conditions_array['conditions_equation_string'] = $temp_string;

		  $bInfo_array = array_merge($banners, $info, $conditions_array);
		  $bInfo = new objectInfo($bInfo_array);
		}

		$banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
		$banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';

		if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $bInfo->banners_id) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $banners['banners_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="javascript:popupImageWindow(\'' . FILENAME_POPUP_IMAGE . '?' . tep_get_all_get_params(array('banner')) . 'banner=' . $banners['banners_id'] . '\')">' . tep_image(DIR_WS_IMAGES . 'icon_popup.gif', 'View Banner') . '</a>&nbsp;' . $banners['banners_title']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $banners['banners_weight']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $banners_shown . ' / ' . $banners_clicked . ' (' . ($banners_shown>0 ? tep_round($banners_clicked*100/$banners_shown, 2) : 0) . '%)'; ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($banners['status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=0') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=1') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $banners['banners_id']) . '">' . tep_image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS) . '</a>&nbsp;'; if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $banners['banners_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	}
  }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($gPath > 0) {
?>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'bID', 'page'))); ?></td>
                  </tr>
<?php
  }
?>
                  <tr>
                    <td align="right" colspan="2" class="smallText"><?php if (empty($action)) echo (($gPath==0) ? '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'action=new_group') . '">' . tep_image_button('button_new_section.gif', IMAGE_NEW_SECTION) . '</a>&nbsp;' : '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $gPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gPath=' . $gPath . '&action=new') . '">' . tep_image_button('button_new_record.gif', IMAGE_NEW_RECORD) . '</a>&nbsp;'); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
	case 'new_group':
	case 'edit_group':
	  $heading[] = array('text' => '<strong>' . ($action=='edit_group' ? TEXT_INFO_HEADING_EDIT_GROUP : TEXT_INFO_HEADING_NEW_GROUP) . '</strong>');

	  $contents = array('form' => tep_draw_form('groups', FILENAME_BANNER_MANAGER, 'action=' . ($action=='edit_group' ? 'update_group' : 'insert_group'), 'post') . tep_draw_hidden_field('groups_id', $gInfo->banners_groups_id));
	  $contents[] = array('text' => ($action=='edit_group' ? TEXT_EDIT_GROUP_INTRO : TEXT_NEW_GROUP_INTRO));

	  $contents[] = array('text' => '<br>' . TEXT_GROUP_NAME . '<br>' . tep_draw_input_field('groups_name', $gInfo->banners_groups_name, 'size="30"'));

	  $contents[] = array('text' => '<br>' . TEXT_GROUP_PATH . '<br>' . tep_draw_input_field('groups_path', $gInfo->banners_groups_path, 'size="15"'));

	  $contents[] = array('text' => '<br>' . TEXT_BANNER_GROUP_CONDITIONS);
	  for ($i=0; $i<10; $i++) {
		$contents[] = array('text' => '<a href="#" onclick="' . (tep_not_null($gInfo->conditions_page_name[$i]) ? '' : 'document.getElementById(\'condition' . $i . '\').style.display = (document.getElementById(\'condition' . $i . '\').style.display==\'none\' ? \'block\' : \'none\'); document.getElementById(\'condition_field_' . $i . '\').value = \'\'; ') . 'return false">' . sprintf(TEXT_CONDITION_N, ($i+1)) . '</a><div id="condition' . $i . '" style="display: ' . (tep_not_null($gInfo->conditions_page_name[$i]) ? 'block' : 'none') . ';">' . tep_draw_pull_down_menu('conditions_page_type[]', $pages_types_array, $gInfo->conditions_page_type[$i]) . ' ' . tep_draw_pull_down_menu('conditions_page_consist[]', $pages_consist_array, $gInfo->conditions_page_consist[$i]) . ' ' . tep_draw_input_field('conditions_page_name[]', $gInfo->conditions_page_name[$i], 'size="30" id="condition_field_' . $i . '"') . '</div>');
	  }

	  $contents[] = array('text' => '<br>' . TEXT_CONDITION_EQUATION . '<br>' . tep_draw_input_field('conditions_equation', $gInfo->conditions_equation, 'size="30"') . '<br>' . TEXT_CONDITION_EQUATION_TEXT);

	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_BANNER_MANAGER, (tep_not_null($gInfo->banners_groups_id) ? 'gID=' . $gInfo->banners_groups_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
	case 'delete_group':
	  $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_GROUP . '</strong>');

	  $contents = array('form' => tep_draw_form('groups', FILENAME_BANNER_MANAGER, 'action=delete_group_confirm') . tep_draw_hidden_field('groups_id', $gInfo->banners_groups_id));
	  $contents[] = array('text' => TEXT_DELETE_GROUP_INTRO);
	  $contents[] = array('text' => '<br><strong>' . $gInfo->banners_groups_name . '</strong>');
	  if ($gInfo->banners_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_BANNERS, $gInfo->banners_count));
	  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $gID) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	  break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');

      $contents = array('form' => tep_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $bInfo->banners_title . '</strong>');
      if ($bInfo->banners_image) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $HTTP_GET_VARS['bID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($gInfo) && is_object($gInfo)) { // group info box contents
		$heading[] = array('text' => '<strong>' . $gInfo->banners_groups_name . '</strong>');

		$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $gInfo->banners_groups_id . '&action=edit_group') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'gID=' . $gInfo->banners_groups_id . '&action=delete_group') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		if (sizeof($gInfo->conditions_page_name) > 0) {
		  $contents[] = array('text' => '<br>' . TEXT_BANNER_GROUP_CONDITIONS);
		  reset($gInfo->conditions_page_name);
		  while (list($i) = each($gInfo->conditions_page_name)) {
			$contents[] = array('text' => sprintf(TEXT_CONDITION_N, ($i+1)) . '<br>' . $pages_types[$gInfo->conditions_page_type[$i]] . ' ' . strtolower($pages_consist[$gInfo->conditions_page_consist[$i]]) . ' "' . $gInfo->conditions_page_name[$i] . '"');
		  }

		  $contents[] = array('text' => '<br>' . TEXT_CONDITION_EQUATION . '<br>' . $gInfo->conditions_equation_string);
		}
		$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($gInfo->date_added));
		if (tep_not_null($gInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($gInfo->last_modified));
	  } elseif (is_object($bInfo)) {
        $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $bInfo->banners_id . '&action=new') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&gPath=' . $gPath . '&bID=' . $bInfo->banners_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		if (sizeof($bInfo->conditions_page_name) > 0) {
		  $contents[] = array('text' => '<br>' . TEXT_BANNERS_CONDITIONS);
		  reset($bInfo->conditions_page_name);
		  while (list($i) = each($bInfo->conditions_page_name)) {
			$contents[] = array('text' => sprintf(TEXT_CONDITION_N, ($i+1)) . '<br>' . $pages_types[$bInfo->conditions_page_type[$i]] . ' ' . strtolower($pages_consist[$bInfo->conditions_page_consist[$i]]) . ' "' . $bInfo->conditions_page_name[$i] . '"');
		  }

		  $contents[] = array('text' => '<br>' . TEXT_CONDITION_EQUATION . '<br>' . $bInfo->conditions_equation_string);
		}

        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($bInfo->date_added));

        if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
          $banner_id = $bInfo->banners_id;
          $days = '3';
          include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
          $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
        } else {
          include(DIR_WS_FUNCTIONS . 'html_graphs.php');
          $contents[] = array('align' => 'center', 'text' => '<br>' . tep_banner_graph_infoBox($bInfo->banners_id, '3'));
        }

        $contents[] = array('text' => tep_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br>' . tep_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);

        if ($bInfo->date_scheduled) $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, tep_date_short($bInfo->date_scheduled)));

        if ($bInfo->expires_date) {
          $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, tep_date_short($bInfo->expires_date)));
        } elseif ($bInfo->expires_impressions) {
          $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
        }

        if ($bInfo->date_status_change) $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_STATUS_CHANGE, tep_date_short($bInfo->date_status_change)));
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
