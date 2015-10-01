<?php
  require('includes/application_top.php');

  if (DEBUG_MODE=='off' && in_array($action, array('new_type', 'edit_type', 'insert_type', 'update_type', 'delete_type', 'delete_type_confirm'))) {
	tep_redirect(tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action'))));
  }

  function tep_get_news_type_info($news_types_id, $language_id, $field = 'news_types_name') {
	if (tep_db_field_exists(TABLE_NEWS_TYPES, $field)) {
	  $type_info_query = tep_db_query("select " . tep_db_input($field) . " as field from " . TABLE_NEWS_TYPES . " where news_types_id = '" . (int)$news_types_id . "' and language_id = '" . (int)$language_id . "'");
	  $type_info = tep_db_fetch_array($type_info_query);
	  return $type_info['field'];
	} else {
	  return false;
	}
  }

  function tep_get_news_info($news_id, $language_id = '', $field = 'news_name') {
	global $languages_id;
	if (empty($language_id)) $language_id = $languages_id;

	$news_query = tep_db_query("select " . tep_db_input($field) . " as news_field from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "' and language_id = '" . (int)$language_id . "'");
	$news_array = tep_db_fetch_array($news_query);

	return $news_array['news_field'];
  }

  $tPath = (isset($HTTP_GET_VARS['tPath']) ? $HTTP_GET_VARS['tPath'] : '');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $news_keys = array('MAX_DISPLAY_NEWS', 'MAX_DISPLAY_NEWS_RESULTS');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['tID'])) {
            tep_db_query("update " . TABLE_NEWS_TYPES . " set news_types_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where news_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
          } elseif (isset($HTTP_GET_VARS['nID'])) {
            tep_db_query("update " . TABLE_NEWS . " set news_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where news_id = '" . (int)$HTTP_GET_VARS['nID'] . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('flag', 'action', 'nID')) . 'nID=' . $HTTP_GET_VARS['nID']));
        break;
      case 'delete_news_confirm':
        if (isset($HTTP_POST_VARS['news_id'])) {
          $news_id = tep_db_prepare_input($HTTP_POST_VARS['news_id']);
		  $prev_file_query = tep_db_query("select news_image from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "'");
		  $prev_file = tep_db_fetch_array($prev_file_query);
		  if (tep_not_null($prev_file['news_image'])) {
			@unlink(DIR_FS_CATALOG_IMAGES . $prev_file['news_image']);
			@unlink(DIR_FS_CATALOG_IMAGES . str_replace('news/', 'news/thumbs/', $prev_file['news_image']));
		  }
          tep_db_query("delete from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "'");
		  tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_style = 'news' and content_id = '" . (int)$news_id . "'");
		  tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'news' and content_id = '" . (int)$news_id . "'");
		  tep_db_query("update " . TABLE_NEWS_TYPES . " set last_modified = now() where news_types_id = '" . (int)$tPath . "'");
        }

        tep_redirect(tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID'))));
        break;
      case 'insert_news':
	  case 'update_news':
		tep_set_time_limit(300);
		$page_parse_start_time = microtime();

		if (isset($HTTP_GET_VARS['nID'])) {
		  $news_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);
		} else {
		  $max_news_id_query = tep_db_query("select max(news_id) as new_id from " . TABLE_NEWS . "");
		  $max_news_id_array = tep_db_fetch_array($max_news_id_query);
		  $news_id = (int)$max_news_id_array['new_id'] + 1;
		}

		$news_types_id = $HTTP_POST_VARS['news_types_id'];
		$shops_array = $HTTP_POST_VARS['news_shops'];
		if (!is_array($shops_array)) $shops_array = array();

		$max_news_id = $news_id;
		if (sizeof($shops_array) > 1 && $action=='insert_news') {
		  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> '' and shops_id in ('" . implode("', '", $shops_array) . "')");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			tep_db_select_db($shops['shops_database']);
			$max_news_id_query = tep_db_query("select max(news_id) as new_id from " . TABLE_NEWS . "");
			$max_news_id_array = tep_db_fetch_array($max_news_id_query);
			$shop_news_id = (int)$max_news_id_array['new_id'] + 1;
			if ($shop_news_id > $max_news_id) $max_news_id = $shop_news_id;
		  }
		  tep_db_select_db(DB_DATABASE);
		  $news_id = $max_news_id;
		}

		if (tep_not_null($HTTP_POST_VARS['news_date_added'])) {
		  $date_added = preg_replace('/(\d{2})\.(\d{2})\.(\d{4})/', '$3-$2-$1', $HTTP_POST_VARS['news_date_added']) . ' ' . date('H:i:s');
		} else {
		  $date_added = date('Y-m-d H:i:s');
		}
		if ($date_added > date('Y-m-d H:i:s')) $date_added = date('Y-m-d H:i:s');

		if (tep_not_null($HTTP_POST_VARS['news_expires_date'])) {
		  $expires_date = preg_replace('/(\d{2})\.(\d{2})\.(\d{4})/', '$3-$2-$1', $HTTP_POST_VARS['news_expires_date']);
		} else {
		  $expires_date = 'null';
		}
//		if ($expires_date > date('Y-m-d')) $expires_date = date('Y-m-d');

		if (tep_not_null($HTTP_POST_VARS['new_news_category'])) $news_category = tep_db_prepare_input($HTTP_POST_VARS['new_news_category']);
		else $news_category = tep_db_prepare_input($HTTP_POST_VARS['news_category']);

		$news_products = array();
		$products_discount = 0;
		if (tep_not_null($HTTP_POST_VARS['news_products'])) {
		  $products = array_map('tep_string_to_int', explode("\n", trim($HTTP_POST_VARS['news_products'])));
		  reset($products);
		  while (list(, $product_id) = each($products)) {
			if ($product_id > 0) $news_products[] = $product_id;
		  }
		  if (sizeof($news_products) > 0) {
			$products_discount = str_replace(',', '.', (float)$HTTP_POST_VARS['news_products_discount']);
		  }
		}

		$languages = tep_get_languages();
		$news_name_array = $HTTP_POST_VARS['news_name'];
		$news_description_array = $HTTP_POST_VARS['news_description'];
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $description = str_replace('\\\"', '"', $news_description_array[$language_id]);
		  $description = str_replace('\"', '"', $description);
		  $description = str_replace("\\\'", "\'", $description);
		  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		  $description = str_replace(' - ', ' &ndash; ', $description);
		  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

		  $sql_data_array = array('news_id' => $news_id,
								  'news_status' => tep_db_prepare_input($HTTP_POST_VARS['news_status']),
								  'date_added' => $date_added,
								  'news_name' => tep_db_prepare_input($news_name_array[$language_id]),
								  'news_types_id' => (int)$news_types_id,
								  'language_id' => $language_id,
								  'news_description' => $description,
								  'news_category' => $news_category,
								  'expires_date' => $expires_date,
								  'news_products' => implode("\n", $news_products),
								  'news_products_discount' => $products_discount);

		  $t = 0;
		  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''" . ($action=='update_news' ? " and shops_id = '" . (int)SHOP_ID . "'" : " and shops_id in ('" . implode("', '", $shops_array) . "')"));
		  while ($shops = tep_db_fetch_array($shops_query)) {
			$database = $shops['shops_database'];

			if ($action == 'insert_news') {
			  tep_db_perform($database . '.' . TABLE_NEWS, $sql_data_array);
			} elseif ($action == 'update_news') {
			  tep_db_perform($database . '.' . TABLE_NEWS, $sql_data_array, 'update', "news_id = '" . (int)$news_id . "' and language_id = '" . (int)$language_id . "'");
			}

			if ($products_discount > 0 && $products_discount < 100 && $HTTP_POST_VARS['news_status']=='1') {
			  tep_db_query("delete from " . $database . "." . TABLE_SPECIALS . " where products_id in ('" . implode("', '", $news_products) . "') and specials_types_id = '5'");

			  reset($news_products);
			  while (list(, $product_id) = each($news_products)) {
				$max_id_query = tep_db_query("select max(specials_id) as max_id from " . $database . "." . TABLE_SPECIALS . "");
				$max_id = tep_db_fetch_array($max_id_query);
				$specials_id = (int)$max_id['max_id'] + 1;

				tep_db_query("insert into " . $database . "." . TABLE_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_first_page, products_image_exists, specials_new_products_price, specials_date_added, expires_date, status) select '" . (int)$specials_id . "', '5', '" . (int)$languages_id . "', products_id, if((products_image_exists='1' and products_listing_status='1'), 1, 0), products_image_exists, (products_price * (1 - " . $products_discount . " / 100)), now(), '" . tep_db_input($expires_date) . "', products_status from " . $database . "." . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
			  }

			  tep_db_query("update " . $database . "." . TABLE_SPECIALS_TYPES . " set specials_last_modified = now() where specials_types_id = '5'");
			}

			tep_update_blocks($news_id, 'news', $database);
			$t ++;
		  }
		}

		if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
		  $size = @getimagesize($news_image);
		  if ($size[2]=='3') $ext = '.png';
		  elseif ($size[2]=='2') $ext = '.jpg';
		  else $ext = '.gif';
		  $new_filename = preg_replace('/[^\d\w]/i', '', strtolower($news_path));
		  if (!tep_not_null($new_filename)) $new_filename = $news_id;
		  $new_filename .= $ext;
		  $upload->filename = 'news/' . $new_filename;
          if ($upload->upload('news_image', DIR_FS_CATALOG_IMAGES)) {
			if (NEWS_IMAGE_WIDTH > 0 || NEWS_IMAGE_HEIGHT > 0) {
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, '', NEWS_IMAGE_WIDTH, NEWS_IMAGE_HEIGHT);
			  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'news/thumbs')) mkdir(DIR_FS_CATALOG_IMAGES . 'news/thumbs', 0777);
			  tep_create_thumb(DIR_FS_CATALOG_IMAGES . $upload->filename, DIR_FS_CATALOG_IMAGES . str_replace('news/', 'news/thumbs/', $upload->filename), XSMALL_IMAGE_WIDTH, XSMALL_IMAGE_HEIGHT);
			}
			$prev_file_query = tep_db_query("select news_image from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['news_image']) && $prev_file['news_image']!=$upload->filename) {
			  @unlink(DIR_FS_CATALOG_IMAGES . $prev_file['news_image']);
			  @unlink(DIR_FS_CATALOG_IMAGES . str_replace('news/', 'news/thumbs/', $prev_file['news_image']));
			}

			$t = 0;
			$shops_query = tep_db_query("select shops_id, shops_database, shops_fs_dir, shops_default_status from " . TABLE_SHOPS . " where shops_database <> ''" . ($action=='update_news' ? " and shops_id = '" . (int)SHOP_ID . "'" : " and shops_id in ('" . implode("', '", $shops_array) . "')"));
			while ($shops = tep_db_fetch_array($shops_query)) {
			  $database = $shops['shops_database'];
			  tep_db_query("update " . $database . "." . TABLE_NEWS . " set news_image = '" . tep_db_input($upload->filename) . "' where news_id = '" . (int)$news_id . "'");
			  $copy_to = str_replace('//', '/', $shops['shops_fs_dir'] . DIR_WS_CATALOG_IMAGES);
			  if ($action=='insert_news' && tep_not_null($upload->filename)) {
				copy(DIR_FS_CATALOG_IMAGES . $upload->filename, $copy_to . $upload->filename);
				if (!is_dir($copy_to . 'news/thumbs')) mkdir($copy_to . 'news/thumbs', 0777);
				copy(DIR_FS_CATALOG_IMAGES . str_replace('news/', 'news/thumbs/', $upload->filename), $copy_to . str_replace('news/', 'news/thumbs/', $upload->filename));
			  }
			  $t ++;
			}
		  }
        }
		tep_db_query("update " . TABLE_NEWS_TYPES . " set last_modified = now() where news_types_id = '" . (int)$tPath . "'");

		tep_redirect(tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID', 'tPath')) . 'tPath=' . $news_types_id . '&nID=' . $news_id));
		break;
      case 'delete_type_confirm':
        $news_types_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

        tep_db_query("delete from " . TABLE_NEWS . " where news_types_id = '" . (int)$news_types_id . "'");;
        tep_db_query("delete from " . TABLE_NEWS_TYPES . " where news_types_id = '" . (int)$news_types_id . "'");

        tep_redirect(tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'tID'))));
        break;
      case 'insert_type':
      case 'update_type':
		if (isset($HTTP_POST_VARS['news_types_id'])) {
		  $news_types_id = tep_db_prepare_input($HTTP_POST_VARS['news_types_id']);
		} else {
		  $max_news_types_id_query = tep_db_query("select max(news_types_id) as news_types_id from " . TABLE_NEWS_TYPES . "");
		  $max_news_types_id_array = tep_db_fetch_array($max_news_types_id_query);
		  $news_types_id = (int)$max_news_types_id_array['news_types_id'] + 1;
		}

        $news_types_path = tep_db_prepare_input($HTTP_POST_VARS['news_types_path']);
        $news_types_path = preg_replace('/\_+/', '_', preg_replace('/[^\d\w]/i', '_', strtolower(trim($news_types_path))));

		if (!tep_not_null($news_types_path)) {
		  $messageStack->add(ERROR_PATH_EMPTY);
		  $action = ($action == 'update_type' && tep_not_null($news_types_id)) ? 'edit_type' : 'new_type';
		} else {
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$news_types_name_array = $HTTP_POST_VARS['news_types_name'];
			$news_types_short_description_array = $HTTP_POST_VARS['news_types_short_description'];
			$news_types_description_array = $HTTP_POST_VARS['news_types_description'];

			$language_id = $languages[$i]['id'];

			$sql_data_array = array('news_types_path' => $news_types_path,
									'news_types_name' => tep_db_prepare_input($news_types_name_array[$language_id]),
									'sort_order' => (int)$HTTP_POST_VARS['sort_order'],
									'news_types_short_description' => tep_db_prepare_input($news_types_short_description_array[$language_id]),
									'news_types_description' => tep_db_prepare_input($news_types_description_array[$language_id]));

			if ($action == 'insert_type') {
			  $insert_sql_data = array('date_added' => 'now()',
									   'news_types_id' => $news_types_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_NEWS_TYPES, $sql_data_array);
			} elseif ($action == 'update_type') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_NEWS_TYPES, $sql_data_array, 'update', "news_types_id = '" . (int)$news_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  tep_redirect(tep_href_link(FILENAME_NEWS, 'tID=' . $news_types_id));
		}
        break;
	}
  }

  $years_query = tep_db_query("select max(date_format(date_added, '%Y')) as max_year, min(date_format(date_added, '%Y')) as min_year from " . TABLE_NEWS . "");
  $years = tep_db_fetch_array($years_query);
  if ((int)$years['max_year']=='0') $years['max_year'] = date('Y');
  if ((int)$years['min_year']=='0') $years['min_year'] = date('Y');
  $news_years = array();
  $news_years[] = array('id' => '', 'text' => TEXT_CHOOSE_YEAR);
  for ($i=$years['max_year']; $i>=$years['min_year']; $i--) {
	$news_years[] = array('id' => $i, 'text' => $i);
  }

  $news_months = array();
  $news_months[] = array('id' => '', 'text' => TEXT_CHOOSE_MONTH);
  for ($i=1; $i<=12; $i++) {
	$news_months[] = array('id' => $i, 'text' => $months_names[$i]);
  }

  $month = '';
  $year = '';
  if (isset($HTTP_GET_VARS['year']) && tep_not_null($HTTP_GET_VARS['year'])) {
	$year = (int)$HTTP_GET_VARS['year'];
	$month = (isset($HTTP_GET_VARS['month']) && tep_not_null($HTTP_GET_VARS['month'])) ? (int)$HTTP_GET_VARS['month'] : '';
  }

  $news_page_heading = HEADING_TITLE;
  $news_types_array = array(array('id' => '', 'text' => TEXT_CHOOSE_TYPE));
  $news_types_query = tep_db_query("select news_types_id, news_types_name from " . TABLE_NEWS_TYPES . "");
  while ($news_types = tep_db_fetch_array($news_types_query)) {
	$news_types_array[] = array('id' => $news_types['news_types_id'], 'text' => $news_types['news_types_name']);
	if (tep_not_null($tPath) && $tPath==$news_types['news_types_id']) {
	  $news_page_heading = $news_types['news_types_name'] . (tep_not_null($month) ? ', ' . $months_names[(int)$month] : '') . (tep_not_null($year) ? ', ' . $year : '');
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
<?php
  if ($action == 'new_news') {
	$parameters = array('news_id' => '',
						'news_name' => '');

    $nInfo = new objectInfo($parameters);

	$news_categories_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
	$news_categories_query = tep_db_query("select distinct news_category from " . TABLE_NEWS . " where news_types_id = '" . (int)$tPath . "' and news_category <> '' order by news_category");
	while ($news_categories = tep_db_fetch_array($news_categories_query)) {
	  $news_categories_array[] = array('id' => $news_categories['news_category'], 'text' => $news_categories['news_category']);
	}

    if (isset($HTTP_GET_VARS['nID']) && empty($HTTP_POST_VARS)) {
      $news_query = tep_db_query("select * from " . TABLE_NEWS . " where news_id = '" . (int)$HTTP_GET_VARS['nID'] . "' and language_id = '" . (int)$languages_id . "'");
      $news = tep_db_fetch_array($news_query);

      $nInfo->objectInfo($news);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $nInfo->objectInfo($HTTP_POST_VARS);
      $news_name = array_map("stripslashes", $HTTP_POST_VARS['news_name']);
      $news_description = array_map("stripslashes", $HTTP_POST_VARS['news_desciprion']);
      $news_status = $HTTP_POST_VARS['news_status'];
      $date_added = $HTTP_POST_VARS['news_date_added'];
    }

    $languages = tep_get_languages();

    if (!isset($nInfo->date_added)) $nInfo->date_added = date('Y-m-d');
    if (!isset($nInfo->news_status)) $nInfo->news_status = '1';
    switch ($nInfo->news_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

	$form_action = (isset($HTTP_GET_VARS['nID'])) ? 'update_news' : 'insert_news';
?>
    <?php echo tep_draw_form('new_news', FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . (isset($HTTP_GET_VARS['nID']) ? '&nID=' . $HTTP_GET_VARS['nID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $news_page_heading . ' &raquo; ' . TEXT_NEW_NEWS; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1">
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var newsAdded = new ctlSpiffyCalendarBox("newsAdded", "new_news", "news_date_added", "btnDate1", "<?php echo tep_date_short($nInfo->date_added); ?>", scBTNMODE_CUSTOMBLUE);
  var newsExpires = new ctlSpiffyCalendarBox("newsExpires", "new_news", "news_expires_date", "btnDate2", "<?php echo (tep_not_null($nInfo->expires_date) ? tep_date_short($nInfo->expires_date) : ''); ?>", scBTNMODE_CUSTOMBLUE);
</script>
          <tr>
            <td class="main" width="250"><?php echo TEXT_NEWS_DATE; ?><br><small>(dd.mm.yyyy)</small></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;'; ?><script language="javascript">newsAdded.writeControl(); newsAdded.dateFormat="dd.MM.yyyy";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_NEWS_TYPE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('news_types_id', $news_types_array, (isset($nInfo->news_types_id) ? $nInfo->news_types_id : $HTTP_GET_VARS['tPath'])); ?></td>
          </tr>
<?php
	if (sizeof($allowed_shops_array) <> 1 && $form_action=='insert_news') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_NEWS_SHOPS; ?></td>
            <td class="main"><?php
	  $shops_query = tep_db_query("select shops_id, shops_url from " . TABLE_SHOPS . " where shops_database <> ''" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
	  while ($shops = tep_db_fetch_array($shops_query)) {
		echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . ($shops['shops_id']==SHOP_ID ? tep_draw_checkbox_field('', '', true, '', 'disabled="disabled"') . tep_draw_hidden_field('news_shops[]', $shops['shops_id']) : tep_draw_checkbox_field('news_shops[]', $shops['shops_id'])) . ' ' . str_replace('http://', '', str_replace('www.', '', $shops['shops_url'])) . '<br>' . "\n";
	  }
?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_NEWS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('news_status', '1', $in_status) . '&nbsp;' . TEXT_NEWS_ACTIVE . '&nbsp;' . tep_draw_radio_field('news_status', '0', $out_status) . '&nbsp;' . TEXT_NEWS_NOT_ACTIVE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_NEWS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES .$languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('news_name[' . $languages[$i]['id'] . ']', 'soft', '55', '3', (isset($news_name[$languages[$i]['id']]) ? $news_name[$languages[$i]['id']] : tep_get_news_info($nInfo->news_id, $languages[$i]['id']))); ?></td>
          </tr>
<?php
    }
?>
		</table>
<?php
	echo tep_load_blocks($nInfo->news_id, 'news');
?>
		<table border="0" width="100%" cellspacing="0" cellpadding="1">
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_NEWS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('news_image') . (tep_not_null($nInfo->news_image) ? '<br><span class="smallText">' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . $nInfo->news_image : '') . '</span>'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main" width="250"><?php echo TEXT_NEWS_CATEGORY; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . (sizeof($news_categories_array)>1 ? tep_draw_pull_down_menu('news_category', $news_categories_array, $nInfo->news_category) . '<br />' . TEXT_NEWS_CATEGORY_TEXT . '<br />' : '') . tep_draw_input_field('new_news_category');
?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_NEWS_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($news_description[$languages[$i]['id']]) ? $news_description[$languages[$i]['id']] : tep_get_news_info($nInfo->news_id, $languages[$i]['id'], 'news_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('news_description[' . $languages[$i]['id'] . ']');
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
            <td class="main" width="250"><?php echo TEXT_NEWS_PRODUCTS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_textarea_field('news_products', 'soft', '15', '10', tep_get_news_info($nInfo->news_id, $languages[$i]['id'], 'news_products')); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo TEXT_NEWS_ACTIONS; ?></strong></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_NEWS_ACTIONS_EXPIRES_DATE; ?><br><small>(dd.mm.yyyy)</small></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;'; ?><script language="javascript">newsExpires.writeControl(); newsExpires.dateFormat="dd.MM.yyyy";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_NEWS_ACTIONS_PRODUCTS_DISCOUNT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('news_products_discount', tep_get_news_info($nInfo->news_id, $languages[$i]['id'], 'news_products_discount'), 'size="3"') . '%'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo (tep_not_null($HTTP_GET_VARS['nID']) ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo $news_page_heading; ?></td>
            <td class="pageHeading" align="right"><?php
	echo tep_draw_form('goto', FILENAME_NEWS, '', 'get');
    echo tep_draw_pull_down_menu('year', $news_years, $year, 'onChange="this.form.submit();"');
	if (isset($HTTP_GET_VARS['year']) && tep_not_null($HTTP_GET_VARS['year'])) {
	  echo tep_draw_pull_down_menu('month', $news_months, $month, 'onChange="this.form.submit();"');
	}
    echo tep_draw_pull_down_menu('tPath', $news_types_array, $tPath, 'onChange="this.form.submit();"');
	reset($HTTP_GET_VARS);
	while (list($k, $v) = each($HTTP_GET_VARS)) {
	  if (!in_array($k, array('year', 'month', 'type'))) echo tep_draw_hidden_field($k, $v);
	}
    echo '</form>';
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if (tep_not_null($tPath)) {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $news_count = 0;
	  $news_query_row = "select * from " . TABLE_NEWS . " where language_id = '" . (int)$languages_id . "' and news_types_id = '" . (int)$tPath . "'";
	  if ($year > 0) $news_query_row .= " and year(date_added) = '" . (int)$year . "'";
	  if ($month > 0) $news_query_row .= " and month(date_added) = '" . (int)$month . "'";
	  $news_query_row .= " order by date_added desc";
      $news_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $news_query_row, $news_query_numrows);
	  $news_query = tep_db_query($news_query_row);
	  while ($news = tep_db_fetch_array($news_query)) {
		$news_count++;
		$rows++;

		if ( (!isset($HTTP_GET_VARS['nID']) || (isset($HTTP_GET_VARS['nID']) && ($HTTP_GET_VARS['nID'] == $news['news_id']))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
		  $nInfo_array = $news;
		  $nInfo = new objectInfo($nInfo_array);
		}

		if (isset($nInfo) && is_object($nInfo) && ($news['news_id'] == $nInfo->news_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $news['news_id']) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $news['news_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_catalog_href_link(FILENAME_CATALOG_NEWS, 'news_id=' . $news['news_id'] . '&version=new') . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW, '16', '16', 'style="margin: 3px 0 -3px 0;"') . '</a>&nbsp;' . $news['news_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_date_short($news['date_added']); ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($news['news_status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'action=setflag&flag=0&nID=' . $news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'action=setflag&flag=1&nID=' . $news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($nInfo) && is_object($nInfo) && ($news['news_id'] == $nInfo->news_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo $news_split->display_count($news_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $news_split->display_links($news_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'nID', 'page'))); ?></td>
                  </tr>
				</table></td>
			  </tr>
<?php
	} else {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $news_types_query = tep_db_query("select * from " . TABLE_NEWS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, news_types_name");
	  while ($news_types = tep_db_fetch_array($news_types_query)) {
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $news_types['news_types_id']))) && !isset($tInfo) && substr($action, 0, 3)!='new') {
		  $tInfo_array = $news_types;
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($news_types['news_types_id'] == $tInfo->news_types_id)) {
		  echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('tID', 'action', 'page', 'nID')) . 'tID=' . $tInfo->news_types_id . '&action=edit_type') . '\'">' . "\n";
		} else {
		  echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('tID', 'action', 'page', 'nID')) . 'tID=' . $news_types['news_types_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="2" title="<?php echo $news_types['news_types_short_description']; ?>"><?php echo '<a href="' . tep_href_link(FILENAME_NEWS, 'tPath=' . $news_types['news_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $news_types['sort_order'] . '] <strong>' . $news_types['news_types_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($news_types['news_types_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=0&tID=' . $news_types['news_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=1&tID=' . $news_types['news_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($news_types['news_types_id'] == $tInfo->news_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('nID', 'action', 'page', 'tID')) . 'tID=' . $news_types['news_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
	  }
	}
	if (empty($action)) {
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td align="right"><?php if (tep_not_null($tPath)) echo '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('nID', 'tID', 'action', 'tPath', 'page', 'year', 'month')) . 'tID=' . $tPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('nID', 'tID', 'action')) . 'action=new_news') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; elseif (DEBUG_MODE=='on') echo '&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('tID', 'nID', 'action', 'year', 'month')) . 'action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>' ?>&nbsp;</td>
                  </tr>
                </table></td>
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
        $heading[] = array('text' => '<strong>' . ($action=='edit_type' ? TEXT_INFO_HEADING_EDIT_TYPE : TEXT_INFO_HEADING_NEW_TYPE) . '</strong>');

        $contents = array('form' => tep_draw_form('types', FILENAME_NEWS, tep_get_all_get_params(array('action', 'tID')) . 'action=' . ($action=='edit_type' ? 'update_type' : 'insert_type'), 'post') . ($action=='edit_type' ? tep_draw_hidden_field('news_types_id', $tInfo->news_types_id) : ''));
        $contents[] = array('text' => ($action=='edit_type' ? TEXT_EDIT_TYPE_INTRO : TEXT_NEW_TYPE_INTRO));

        $type_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('news_types_name[' . $languages[$i]['id'] . ']', tep_get_news_type_info($tInfo->news_types_id, $languages[$i]['id']), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_NAME . $type_inputs_string);

		$type_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('news_types_short_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', tep_get_news_type_info($tInfo->news_types_id, $languages[$i]['id'], 'news_types_short_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_DESCRIPTION . $type_inputs_string);

		$type_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('news_types_description[' . $languages[$i]['id'] . ']', 'soft', '30', '7', tep_get_news_type_info($tInfo->news_types_id, $languages[$i]['id'], 'news_types_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_DESCRIPTION . $type_inputs_string);

        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_NEWS) . tep_draw_input_field('news_types_path', $tInfo->news_types_path, 'size="' . (tep_not_null($tInfo->news_types_path) ? strlen($tInfo->news_types_path) - 1 : '7') . '"') . '/');

        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'));

        $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='edit_type' ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . ' <a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'tID')) . (tep_not_null($tInfo->news_types_id) ? 'tID=' . $tInfo->news_types_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_news':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_NEWS . '</strong>');

        $contents = array('form' => tep_draw_form('news', FILENAME_NEWS, tep_get_all_get_params(array('action')) . '&action=delete_news_confirm') . tep_draw_hidden_field('news_id', $nInfo->news_id));
        $contents[] = array('text' => TEXT_DELETE_NEWS_INTRO);
        $contents[] = array('text' => '<br><strong>' . $nInfo->news_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $nInfo->news_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('news', FILENAME_NEWS, tep_get_all_get_params(array('action', 'tID', 'page')) . 'tID=' . $sInfo->news_types_id . '&action=delete_type_confirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->news_types_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('tID', 'action', 'page')) . 'tID=' . $tInfo->news_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
      default:
		if (is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->news_types_name . '</strong>');

		  if (DEBUG_MODE=='on') $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('nID', 'action', 'tID')) . 'tID=' . $tInfo->news_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('nID', 'action', 'tID')) . 'tID=' . $tInfo->news_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br><br>');
		  if (tep_not_null($tInfo->news_types_short_description)) $contents[] = array('text' => $tInfo->news_types_short_description);
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tInfo->last_modified));
		} elseif (is_object($nInfo)) {
		  $heading[] = array('text' => '<strong>' . tep_get_news_info($nInfo->news_id, $languages_id) . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $nInfo->news_id . '&action=new_news') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_NEWS, tep_get_all_get_params(array('action', 'nID')) . 'nID=' . $nInfo->news_id . '&action=delete_news') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_datetime_short($nInfo->date_added));
		  $contents[] = array('text' => '<br>' . tep_info_image($nInfo->news_image, $nInfo->news_name));
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
?>
    </td>
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