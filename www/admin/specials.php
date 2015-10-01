<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $tPath = (isset($HTTP_GET_VARS['tPath']) ? $HTTP_GET_VARS['tPath'] : '');

  if (DEBUG_MODE=='off' && in_array($action, array('new_type', 'edit_type', 'insert_type', 'update_type', 'delete_type', 'delete_type_confirm'))) {
	tep_redirect(tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action'))));
  }

  function tep_get_specials_type_info($specials_types_id, $language_id, $field = 'specials_types_name') {
	if (tep_db_field_exists(TABLE_SPECIALS_TYPES, $field)) {
	  $type_info_query = tep_db_query("select " . tep_db_input($field) . " as field from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$specials_types_id . "' and language_id = '" . (int)$language_id . "'");
	  $type_info = tep_db_fetch_array($type_info_query);
	  return $type_info['field'];
	} else {
	  return false;
	}
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
		$new_status = (int)$HTTP_GET_VARS['flag'];
		if (isset($HTTP_GET_VARS['tID'])) {
		  tep_db_query("update " . TABLE_SPECIALS_TYPES . " set specials_types_status = '" . $new_status . "', last_modified = now(), specials_last_modified = now() where specials_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
		} elseif (isset($HTTP_GET_VARS['sID'])) {
		  tep_set_specials_status($HTTP_GET_VARS['sID'], $new_status);
		}

        tep_redirect(tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'flag'))));
        break;
	  case 'update':
      case 'insert':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $specials_price = tep_db_prepare_input($HTTP_POST_VARS['specials_price']);
		$specials_price = str_replace(',', '.', $specials_price);
        $day = tep_db_prepare_input($HTTP_POST_VARS['day']);
        $month = tep_db_prepare_input($HTTP_POST_VARS['month']);
        $year = tep_db_prepare_input($HTTP_POST_VARS['year']);
		if ($action=='update') {
		  $specials_id = tep_db_prepare_input($HTTP_POST_VARS['specials_id']);
		} else {
		  $max_id_query = tep_db_query("select max(specials_id) as max_id from " . TABLE_SPECIALS . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $specials_id = (int)$max_id['max_id'] + 1;
		}

        if (substr($specials_price, -1) == '%') {
          $new_special_insert_query = tep_db_query("select products_id, products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
          $new_special_insert = tep_db_fetch_array($new_special_insert_query);

          $products_price = $new_special_insert['products_price'];
          $specials_price = ($products_price - (($specials_price / 100) * $products_price));
        }

        $expires_date = '';
        if (tep_not_null($day) && tep_not_null($month) && tep_not_null($year)) {
          $expires_date = $year;
          $expires_date .= (strlen($month) == 1) ? '0' . $month : $month;
          $expires_date .= (strlen($day) == 1) ? '0' . $day : $day;
        }

		$specials_name_array = $HTTP_POST_VARS['specials_name'];
		$specials_description_array = $HTTP_POST_VARS['specials_description'];

		$languages = tep_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		  $language_id = $languages[$i]['id'];

		  $sql_data_array = array('status' => '1',
								  'products_id' => (int)$products_id,
								  'specials_types_id' => (int)$tPath,
								  'specials_new_products_price' => tep_db_input($specials_price),
								  'specials_name' => tep_db_prepare_input($specials_name_array[$language_id]),
								  'specials_description' => tep_db_prepare_input($specials_description_array[$language_id]));
		  if (tep_not_null($expires_date)) $sql_data_array['expires_date'] = tep_db_input($expires_date);

		  if ($action == 'insert') {
			$insert_sql_data = array('specials_date_added' => 'now()',
									 'specials_id' => $specials_id,
									 'language_id' => $languages[$i]['id']);

			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			tep_db_perform(TABLE_SPECIALS, $sql_data_array);
		  } elseif ($action == 'update') {
			$update_sql_data = array('specials_last_modified' => 'now()');

			$sql_data_array = array_merge($sql_data_array, $update_sql_data);

			tep_db_perform(TABLE_SPECIALS, $sql_data_array, 'update', "specials_id = '" . (int)$specials_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
		  }
		}

        tep_redirect(tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'categories_id')) . 'sID=' . $specials_id));
        break;
      case 'delete_type_confirm':
        $specials_types_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

		$specials_type_query = tep_db_query("select specials_types_image from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$specials_types_id . "'");
		$specials_type = tep_db_fetch_array($specials_type_query);

		$image_location = DIR_FS_CATALOG . DIR_WS_CATALOG_IMAGES . $specials_type['specials_types_image'];

		if (file_exists($image_location)) @unlink($image_location);

        tep_db_query("delete from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "'");;
        tep_db_query("delete from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$specials_types_id . "'");

        tep_redirect(tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'tID'))));
        break;
      case 'delete_confirm':
        $specials_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        tep_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");

        tep_redirect(tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'sID'))));
        break;
      case 'insert_type':
      case 'update_type':
		if (isset($HTTP_POST_VARS['specials_types_id'])) {
		  $specials_types_id = tep_db_prepare_input($HTTP_POST_VARS['specials_types_id']);
		} else {
		  $max_specials_types_id_query = tep_db_query("select max(specials_types_id) as specials_types_id from " . TABLE_SPECIALS_TYPES . "");
		  $max_specials_types_id_array = tep_db_fetch_array($max_specials_types_id_query);
		  $specials_types_id = (int)$max_specials_types_id_array['specials_types_id'] + 1;
		}

        $specials_types_path = tep_db_prepare_input($HTTP_POST_VARS['specials_types_path']);
        $specials_types_path = preg_replace('/\_+/', '_', preg_replace('/[^\d\w]/i', '_', strtolower(trim($specials_types_path))));

		if (!tep_not_null($specials_types_path)) {
		  $messageStack->add(ERROR_PATH_EMPTY);
		  $action = ($action == 'update_type' && tep_not_null($specials_types_id)) ? 'edit_type' : 'new_type';
		} else {
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$specials_types_name_array = $HTTP_POST_VARS['specials_types_name'];
			$specials_types_short_name_array = $HTTP_POST_VARS['specials_types_short_name'];
			$specials_types_short_description_array = $HTTP_POST_VARS['specials_types_short_description'];
			$specials_types_description_array = $HTTP_POST_VARS['specials_types_description'];

			$language_id = $languages[$i]['id'];

			$sql_data_array = array('specials_types_name' => tep_db_prepare_input($specials_types_name_array[$language_id]),
									'specials_types_short_name' => tep_db_prepare_input($specials_types_short_name_array[$language_id]),
									'specials_types_short_description' => tep_db_prepare_input($specials_types_short_description_array[$language_id]),
									'specials_types_description' => tep_db_prepare_input($specials_types_description_array[$language_id]));

			if ($action == 'insert_type') {
			  $insert_sql_data = array('date_added' => 'now()',
									   'specials_types_id' => $specials_types_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_SPECIALS_TYPES, $sql_data_array);
			} elseif ($action == 'update_type') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_SPECIALS_TYPES, $sql_data_array, 'update', "specials_types_id = '" . (int)$specials_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }
		  tep_db_query("update " . TABLE_SPECIALS_TYPES . " set specials_types_path = '" . tep_db_input($specials_types_path) . "', sort_order = '" . (int)$HTTP_POST_VARS['sort_order'] . "' where specials_types_id = '" . (int)$specials_types_id . "'");

		  if ($upload = new upload('', '', '777', array('jpeg', 'jpg', 'gif', 'png'))) {
			$size = @getimagesize($specials_types_image);
			if ($size[2]=='3') $ext = '.png';
			elseif ($size[2]=='2') $ext = '.jpg';
			else $ext = '.gif';
			$new_filename = preg_replace('/[^\d\w]/i', '', strtolower($specials_types_path));
			if (!tep_not_null($new_filename)) $new_filename = $specials_types_id;
			$new_filename .= $ext;
			$upload->filename = 'specials/' . $new_filename;
        	if ($upload->upload('specials_types_image', DIR_FS_CATALOG_IMAGES)) {
			  $prev_file_query = tep_db_query("select specials_types_image from " . TABLE_SPECIALS_TYPES . " where specials_types_id = '" . (int)$specials_types_id . "'");
			  $prev_file = tep_db_fetch_array($prev_file_query);
			  if (tep_not_null($prev_file['specials_types_image']) && $prev_file['specials_types_image']!=$upload->filename) {
				@unlink(DIR_FS_CATALOG_IMAGES . $prev_file['specials_types_image']);
			  }
			  tep_db_query("update " . TABLE_SPECIALS_TYPES . " set specials_types_image = '" . $upload->filename . "' where specials_types_id = '" . (int)$specials_types_id . "'");
			}
		  }

		  tep_redirect(tep_href_link(FILENAME_SPECIALS, 'tID=' . $specials_types_id));
		}
        break;
    }
  }

  $specials_types_heading = '';
  $special_types = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $specials_types_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, specials_types_name");
  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	$special_types[] = array('id' => $specials_types['specials_types_id'], 'text' => $specials_types['specials_types_name']);
	if (tep_not_null($tPath) && $tPath==$specials_types['specials_types_id']) $specials_types_heading = $specials_types['specials_types_name'];
  }

  $all_types = array();
  reset($special_types);
  while (list(, $item) = each($special_types)) {
	$all_types[$item['id']] = $item['text'];
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/calendar.css">
<script language="JavaScript" src="includes/javascript/calendarcode.js"></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div id="popupcalendar" class="text"></div>
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
        <td><table border="0" cellspacing="0" cellpadding="0" width="100%">
		  <tr>
			<td class="pageHeading"><?php echo HEADING_TITLE . (tep_not_null($tPath) ? ' &raquo; ' . $specials_types_heading : ''); ?></td>
			<td align="right"><?php
    echo tep_draw_form('goto', FILENAME_SPECIALS, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('tPath', $special_types, $tPath, 'onChange="this.form.submit();"');
    echo '</form>';
?></td>
		  </tr>
		</table></td>
      </tr>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
	$specials_array = array();
	if (isset($HTTP_GET_VARS['categories_id'])) {
	  $form_action = 'insert';
	  $product = array();
	  if ( ($action == 'edit') && isset($HTTP_GET_VARS['sID']) ) {
		$form_action = 'update';

		$product_query = tep_db_query("select p.products_id, pd.products_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and s.language_id = pd.language_id and p.products_id = s.products_id and s.specials_id = '" . (int)$HTTP_GET_VARS['sID'] . "' and pd.language_id = '" . (int)$languages_id . "'");
		$product = tep_db_fetch_array($product_query);

		$product_query = tep_db_query("select * from " . TABLE_SPECIALS . " where specials_id = '" . (int)$HTTP_GET_VARS['sID'] . "'");
		while ($product_info = tep_db_fetch_array($product_query)) {
		  reset($product_info);
		  while (list($k, $v) = each($product_info)) {
			if ($k=='specials_name' || $k=='specials_description') {
			  $product['specials_name'][$product_info['language_id']] = $product_info['specials_name'];
			  $product['specials_description'][$product_info['language_id']] = $product_info['specials_description'];
			} else {
			  $product[$k] = $v;
			}
		  }
		}
	  } else {
// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
		$specials_query = tep_db_query("select distinct products_id from " . TABLE_SPECIALS . "");
		while ($specials = tep_db_fetch_array($specials_query)) {
		  $specials_array[] = $specials['products_id'];
		}
	  }

	  $sInfo = new objectInfo($product);

	  $products_query = tep_db_query("select p.products_id, p.products_model, pd.products_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$HTTP_GET_VARS['categories_id'] . "' and pd.language_id = '" . (int)$languages_id . "' order by products_name");
	  while ($products = tep_db_fetch_array($products_query)) {
		if (!in_array($products['products_id'], $specials_array)) {
		  $products_array[] = array('id' => $products['products_id'], 'text' => '[' . $products['products_model'] . '] ' . $products['products_name'] . ' (' . $currencies->format($products['products_price']) . ')');
		}
	  }
?>
      <tr><?php echo tep_draw_form('new_special', FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'sID')) . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo tep_draw_hidden_field('specials_id', $HTTP_GET_VARS['sID']); ?>
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_PRODUCT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . (isset($sInfo->products_name) ? $sInfo->products_name . ' <small>(' . $currencies->format($sInfo->products_price) . ')</small>' . tep_draw_hidden_field('products_id', $sInfo->products_id) : tep_draw_pull_down_menu('products_id', $products_array)); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('specials_price', ((isset($sInfo->specials_new_products_price) && $sInfo->specials_new_products_price>0) ? (string)(float)$sInfo->specials_new_products_price : '')); ?></td>
          </tr>
		  <tr>
			<td colspan="2"><small><?php echo TEXT_SPECIALS_PRICE_TIP; ?></small></td>
		  </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_EXPIRES_DATE . '<br><small>(' . CALENDAR_DATE_FORMAT . ')</small>'; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('day', (isset($sInfo->expires_date) ? substr($sInfo->expires_date, 8, 2) : ''), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('month', (isset($sInfo->expires_date) ? substr($sInfo->expires_date, 5, 2) : ''), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('year', (isset($sInfo->expires_date) ? substr($sInfo->expires_date, 0, 4) : ''), 'size="4" maxlength="4" class="cal-TextBox"'); ?><a class="so-BtnLink" href="javascript:calClick();return false;" onmouseover="calSwapImg('BTN_date', 'img_Date_OVER',true);" onmouseout="calSwapImg('BTN_date', 'img_Date_UP',true);" onclick="calSwapImg('BTN_date', 'img_Date_DOWN');showCalendar('new_special','dteWhen','BTN_date');return false;"><?php echo tep_image(DIR_WS_IMAGES . 'cal_date_up.gif', 'Calendar', '22', '17', 'align="absmiddle" name="BTN_date"'); ?></a></td>
          </tr>
		  <tr>
			<td colspan="2" class="main" height="40"><strong><?php echo TEXT_SPECIALS_BLOCKS_DESCRIPTION; ?></strong></td>
		  </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_NAME; ?></td>
            <td class="main"><?php
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_input_field('specials_name[' . $languages[$i]['id'] . ']', $sInfo->specials_name[$languages[$i]['id']], 'size="45"') . '<br>';
	  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_DESCRIPTION; ?></td>
            <td class="main"><?php
	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
		echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"') . tep_draw_textarea_field('specials_description[' . $languages[$i]['id'] . ']', 'soft', '44', '4', $sInfo->specials_description[$languages[$i]['id']]) . '<br>';
	  }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="right" valign="top"><br><?php echo (($form_action == 'insert') ? tep_image_submit('button_insert.gif', IMAGE_INSERT) : tep_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'categories_id')) . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?>&nbsp;</td>
          </tr>
        </table></td>
      </form>
	  </tr>
<?php
	} else {
?>
      <tr><?php
	  echo tep_draw_form('specials', FILENAME_SPECIALS, '', 'get');
	  reset($HTTP_GET_VARS);
	  while (list($k, $v) = each($HTTP_GET_VARS)) {
		echo "\n" . tep_draw_hidden_field($k, $v);
	  }
?>
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_CATEGORY; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_pull_down_menu('categories_id', array_merge(array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT)), tep_get_category_tree(0, '&nbsp; ')), '', 'onchange="this.form.submit();"'); ?></td>
          </tr>
        </table></td>
      </form>
	  </tr>
<?php
	}
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if (tep_not_null($tPath)) {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $specials_query_raw = "select p.products_id, pd.products_name, p.products_price, s.*, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where s.specials_types_id = '" . (int)$tPath . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and s.language_id = pd.language_id and p.products_id = s.products_id and p2c.products_id = p.products_id group by p.products_id order by pd.products_name";
	  $specials_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
	  $specials_query = tep_db_query($specials_query_raw);
	  while ($specials = tep_db_fetch_array($specials_query)) {
		if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $specials['specials_id']))) && !isset($sInfo)) {
		  $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
		  $products = tep_db_fetch_array($products_query);
		  $sInfo_array = array_merge($specials, $products);
		  $sInfo = new objectInfo($sInfo_array);
		}

		if (isset($sInfo) && is_object($sInfo) && ($specials['specials_id'] == $sInfo->specials_id)) {
		  echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'tID', 'action')) . 'sID=' . $sInfo->specials_id . '&categories_id=' . $sInfo->categories_id . '&action=edit') . '\'">' . "\n";
		} else {
		  echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'tID', 'action')) . 'sID=' . $specials['specials_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo $specials['products_name']; ?></td>
                <td class="dataTableContent" align="right"><?php echo ($specials['specials_new_products_price'] > 0) ? '<span class="oldPrice">' . $currencies->format($specials['products_price']) . '</span> <span class="specialPrice">' . $currencies->format($specials['specials_new_products_price']) . '</span>' : '' . $currencies->format($specials['products_price']) . ''; ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($specials['status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'flag', 'sID', 'tID')) . 'action=setflag&flag=0&sID=' . $specials['specials_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'flag', 'sID', 'tID')) . 'action=setflag&flag=1&sID=' . $specials['specials_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($specials['specials_id'] == $sInfo->specials_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'sID=' . $specials['specials_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
	  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'sID', 'tID', 'page'))); ?></td>
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
	  $specials_types_query = tep_db_query("select * from " . TABLE_SPECIALS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, specials_types_name");
	  while ($specials_types = tep_db_fetch_array($specials_types_query)) {
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $specials_types['specials_types_id']))) && !isset($tInfo) && substr($action, 0, 3)!='new') {
		  $tInfo_array = $specials_types;
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($specials_types['specials_types_id'] == $tInfo->specials_types_id)) {
		  echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('tID', 'action', 'page', 'sID')) . 'tID=' . $tInfo->specials_types_id . '&action=edit_type') . '\'">' . "\n";
		} else {
		  echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('tID', 'action', 'page', 'sID')) . 'tID=' . $specials_types['specials_types_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="2" title="<?php echo $specials_types['specials_types_short_description']; ?>"><?php echo '<a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types['specials_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $specials_types['sort_order'] . '] <strong>' . $specials_types['specials_types_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($specials_types['specials_types_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=0&tID=' . $specials_types['specials_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=1&tID=' . $specials_types['specials_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($specials_types['specials_types_id'] == $tInfo->specials_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'page', 'tID')) . 'tID=' . $specials_types['specials_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
	  }
	}
	if (empty($action)) {
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td colspan="2" align="right"><?php if (tep_not_null($tPath)) echo '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'tID', 'action', 'tPath', 'page')) . 'tID=' . $tPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'tID', 'action')) . 'action=new') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; elseif (DEBUG_MODE=='on') echo '&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('tID', 'sID', 'action')) . 'action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>' ?>&nbsp;</td>
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

        $contents = array('form' => tep_draw_form('types', FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'tID')) . 'action=' . ($action=='edit_type' ? 'update_type' : 'insert_type'), 'post', 'enctype="multipart/form-data"') . ($action=='edit_type' ? tep_draw_hidden_field('specials_types_id', $tInfo->specials_types_id) : ''));
        $contents[] = array('text' => ($action=='edit_type' ? TEXT_EDIT_TYPE_INTRO : TEXT_NEW_TYPE_INTRO));

        $type_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('specials_types_name[' . $languages[$i]['id'] . ']', tep_get_specials_type_info($tInfo->specials_types_id, $languages[$i]['id']), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_NAME . $type_inputs_string);

        $type_inputs_string = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('specials_types_short_name[' . $languages[$i]['id'] . ']', tep_get_specials_type_info($tInfo->specials_types_id, $languages[$i]['id'], 'specials_types_short_name'), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_NAME . $type_inputs_string);

		$type_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('specials_types_short_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', tep_get_specials_type_info($tInfo->specials_types_id, $languages[$i]['id'], 'specials_types_short_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_DESCRIPTION . $type_inputs_string);

		$type_inputs_string = '';
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('specials_types_description[' . $languages[$i]['id'] . ']', 'soft', '30', '7', tep_get_specials_type_info($tInfo->specials_types_id, $languages[$i]['id'], 'specials_types_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_DESCRIPTION . $type_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_IMAGE . '<br>' . tep_draw_file_field('specials_types_image') . (tep_not_null($tInfo->specials_types_image) ? '<br><span class="smallText">' . $tInfo->specials_types_image : '') . '</span>');

        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_SPECIALS) . tep_draw_input_field('specials_types_path', $tInfo->specials_types_path, 'size="' . (tep_not_null($tInfo->specials_types_path) ? strlen($tInfo->specials_types_path) - 1 : '7') . '"') . '/');

        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'));

        $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='edit_type' ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . ' <a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'tID')) . (tep_not_null($tInfo->specials_types_id) ? 'tID=' . $tInfo->specials_types_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'delete':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</strong>');

		$contents = array('form' => tep_draw_form('specials', FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'sID', 'tID')) . 'sID=' . $sInfo->specials_id . '&action=delete_confirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $sInfo->products_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'sID=' . $sInfo->specials_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('specials', FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'tID', 'page')) . 'tID=' . $sInfo->specials_types_id . '&action=delete_type_confirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->specials_types_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('tID', 'action', 'page')) . 'tID=' . $tInfo->specials_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if (is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->specials_types_name . '</strong>');

		  if (DEBUG_MODE=='on') $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'tID=' . $tInfo->specials_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'tID=' . $tInfo->specials_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br><br>');
		  if (tep_not_null($tInfo->specials_types_short_description)) $contents[] = array('text' => $tInfo->specials_types_short_description);
		  if (tep_not_null($tInfo->specials_types_image)) $contents[] = array('text' => '<br>' . tep_info_image($tInfo->specials_types_image, $tInfo->specials_types_short_name));
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tInfo->last_modified));
		} elseif (is_object($sInfo)) {
		  $heading[] = array('text' => '<strong>' . $sInfo->products_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'sID=' . $sInfo->specials_id . '&categories_id=' . $sInfo->categories_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('sID', 'action', 'tID')) . 'sID=' . $sInfo->specials_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->specials_date_added));
		  if (tep_not_null($sInfo->specials_last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->specials_last_modified));
		  $contents[] = array('align' => 'center', 'text' => '<br>' . tep_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
		  $contents[] = array('text' => '<br>' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($sInfo->products_price));
		  if ($sInfo->specials_new_products_price > 0) {
			$contents[] = array('text' => '' . TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price));
			if ($sInfo->products_price > 0) $contents[] = array('text' => '' . TEXT_INFO_PERCENTAGE . ' ' . number_format(100 - (($sInfo->specials_new_products_price / $sInfo->products_price) * 100)) . '%');
		  }

		  $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <strong>' . tep_date_short($sInfo->expires_date) . '</strong>');
		  $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . tep_date_short($sInfo->date_status_change));
		}
		break;
	}
	if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
	  echo '            <td width="25%" valign="top">' . "\n";

	  $box = new box;
	  echo $box->infoBox($heading, $contents);

	  echo '            </td>' . "\n";
	}
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