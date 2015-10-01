<?php
  require('includes/application_top.php');

  if (DEBUG_MODE=='off' && in_array($action, array('new_type', 'edit_type', 'insert_type', 'update_type', 'delete_type', 'delete_type_confirm'))) {
	tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action'))));
  }

  function tep_update_product_rating($reviews_id) {
	$product_info_query = tep_db_query("select products_id from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
	$product_info = tep_db_fetch_array($product_info_query);
	tep_db_query("update " . TABLE_PRODUCTS . " set products_rating = (select sum(reviews_vote)/count(*) from " . TABLE_REVIEWS . " where products_id = '" . (int)$product_info['products_id'] . "' and reviews_status = '1') where products_id = '" . (int)$product_info['products_id'] . "'");
  }

  function tep_get_reviews_type_info($reviews_types_id, $language_id, $field = 'reviews_types_name') {
	if (tep_db_field_exists(TABLE_REVIEWS_TYPES, $field)) {
	  $type_info_query = tep_db_query("select " . tep_db_input($field) . " as field from " . TABLE_REVIEWS_TYPES . " where reviews_types_id = '" . (int)$reviews_types_id . "' and language_id = '" . (int)$language_id . "'");
	  $type_info = tep_db_fetch_array($type_info_query);
	  return $type_info['field'];
	} else {
	  return false;
	}
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $tPath = (isset($HTTP_GET_VARS['tPath']) ? $HTTP_GET_VARS['tPath'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['tID'])) {
            tep_db_query("update " . TABLE_REVIEWS_TYPES . " set reviews_types_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where reviews_types_id = '" . (int)$HTTP_GET_VARS['tID'] . "'");
          } elseif (isset($HTTP_GET_VARS['rID'])) {
            tep_db_query("update " . TABLE_REVIEWS . " set reviews_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where reviews_id = '" . (int)$HTTP_GET_VARS['rID'] . "'");
          }
        }
		tep_update_product_rating($HTTP_GET_VARS['rID']);

        tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('flag', 'action', 'rID')) . '&rID=' . $HTTP_GET_VARS['rID']));
        break;
      case 'move_confirm':
        $reviews_id = tep_db_prepare_input($HTTP_POST_VARS['reviews_id']);
        $new_review_type_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_review_type_id']);

		tep_db_query("update " . TABLE_REVIEWS . " set reviews_types_id = '" . (int)$new_review_type_id . "' where reviews_id = '" . (int)$reviews_id . "'");

		tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'tPath', 'page', 'rID')) . 'tPath=' . $new_review_type_id . '&rID=' . $reviews_id));
        break;
	  case 'insert':
      case 'update':
		if (tep_not_null($HTTP_POST_VARS['reviews_date_added'])) {
		  $date_added = preg_replace('/(\d{2})\.(\d{2})\.(\d{4})/', '$3-$2-$1', $HTTP_POST_VARS['reviews_date_added']) . ' ' . date('H:i:s');
		} else {
		  $date_added = date('Y-m-d H:i:s');
		}
		if ($date_added > date('Y-m-d H:i:s')) $date_added = date('Y-m-d H:i:s');

		if ($action=='update') {
		  $reviews_id = tep_db_prepare_input($HTTP_POST_VARS['reviews_id']);
		}

		$description = str_replace('\\\"', '"', $HTTP_POST_VARS['reviews_text']);
		$description = str_replace('\"', '"', $description);
		$description = str_replace("\\\'", "\'", $description);
		$description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
		$description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
		$description = str_replace(' - ', ' &ndash; ', $description);
		$description = str_replace(' &mdash; ', ' &ndash; ', $description);
		$description = preg_replace('/<a href="?([^"|>]+)"?>/ie', "'<a href=' . (strpos('$1', 'setbook.')===false ? '\"' . DIR_WS_CATALOG . 'redirect.php?goto=' . urlencode(trim(str_replace('http://', '', '$1'))) . '\" target=\"_blank\"' : '\"' . substr(str_replace('http://', '', '$1'), strpos(str_replace('http://', '', '$1'), '/')) . '\"') . '>'", $description);

		$customer_check_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($HTTP_POST_VARS['customers_email']) . "'");
		$customer_check = tep_db_fetch_array($customer_check_query);
		$customers_id = (int)$customer_check['customers_id'];

		$sql_data_array = array('date_added' => $date_added,
								'reviews_text' => $description,
								'reviews_types_id' => (int)$tPath,
								'customers_id' => (int)$customers_id,
								'customers_name' => tep_db_prepare_input($HTTP_POST_VARS['customers_name']),
								'customers_email' => tep_db_prepare_input($HTTP_POST_VARS['customers_email']),
								'reviews_status' => (int)$HTTP_POST_VARS['reviews_status'],
								);


		if ($action == 'insert') {
		  $url_info = parse_url($HTTP_POST_VARS['product_link']);
		  $shops_id = 0;
		  $products_id = 0;
		  if (tep_not_null($url_info['host'])) {
			$shop_info_query = tep_db_query("select shops_id from " . TABLE_SHOPS . " where shops_url = 'http://" . tep_db_input($url_info['host']) . "'");
			$shop_info = tep_db_fetch_array($shop_info_query);
			$shops_id = $shop_info['shops_id'];
			$products_id = str_replace('.html', '', substr($url_info['path'], strrpos($url_info['path'], '/')+1));
		  }

		  $insert_sql_data = array('products_id' => (int)$products_id,
								   'reviews_vote' => (int)$HTTP_POST_VARS['reviews_rating'],
								   'reviews_ip' => tep_db_prepare_input($_SERVER['REMOTE_ADDR']),
								   'reviews_agent' => tep_db_prepare_input($_SERVER['HTTP_USER_AGENT']),
								   'shops_id' => (int)$shops_id);

		  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

		  tep_db_perform(TABLE_REVIEWS, $sql_data_array);

		  $reviews_id = tep_db_insert_id();
		} elseif ($action == 'update') {
		  $update_sql_data = array('last_modified' => 'now()');

		  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

		  tep_db_perform(TABLE_REVIEWS, $sql_data_array, 'update', "reviews_id = '" . (int)$HTTP_POST_VARS['reviews_id'] . "'");
		}
		tep_db_query("update " . TABLE_REVIEWS_TYPES . " set last_modified = now() where reviews_types_id = '" . (int)$tPath . "'");

		tep_update_product_rating($HTTP_POST_VARS['reviews_id']);

        tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action')) . '&rID=' . $reviews_id));
        break;
      case 'delete_confirm':
        $reviews_id = tep_db_prepare_input($HTTP_GET_VARS['rID']);

		if ($HTTP_POST_VARS['review_blacklist']=='1') {
		  $blacklist_check_query = tep_db_query("select count(*) as total from " . TABLE_BLACKLIST . " where blacklist_ip in (select reviews_ip from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "')");
		  $blacklist_check = tep_db_fetch_array($blacklist_check_query);
		  if ($blacklist_check['total'] < 1) {
			tep_db_query("insert into " . TABLE_BLACKLIST . " (blacklist_ip, customers_id, blacklist_comments, date_added, users_id) select reviews_ip, customers_id, '" . tep_db_input(tep_db_prepare_input($HTTP_POST_VARS['review_blacklist_reason'])) . "', now(), '" . tep_db_input($REMOTE_USER) . "' from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
		  }
		}

        tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
		tep_db_query("update " . TABLE_REVIEWS_TYPES . " set last_modified = now() where reviews_types_id = '" . (int)$tPath . "'");

		tep_update_product_rating($reviews_id);

        tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID'))));
        break;
      case 'insert_type':
      case 'update_type':
		if (isset($HTTP_POST_VARS['reviews_types_id'])) {
		  $reviews_types_id = tep_db_prepare_input($HTTP_POST_VARS['reviews_types_id']);
		} else {
		  $max_reviews_types_id_query = tep_db_query("select max(reviews_types_id) as reviews_types_id from " . TABLE_REVIEWS_TYPES . "");
		  $max_reviews_types_id_array = tep_db_fetch_array($max_reviews_types_id_query);
		  $reviews_types_id = (int)$max_reviews_types_id_array['reviews_types_id'] + 1;
		}

        $reviews_types_path = tep_db_prepare_input($HTTP_POST_VARS['reviews_types_path']);
        $reviews_types_path = preg_replace('/\_+/', '_', preg_replace('/[^\d\w]/i', '_', strtolower(trim($reviews_types_path))));

		if (!tep_not_null($reviews_types_path)) {
		  $messageStack->add(ERROR_PATH_EMPTY);
		  $action = ($action == 'update_type' && tep_not_null($reviews_types_id)) ? 'edit_type' : 'new_type';
		} else {
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$reviews_types_name_array = $HTTP_POST_VARS['reviews_types_name'];
			$reviews_types_short_description_array = $HTTP_POST_VARS['reviews_types_short_description'];
			$reviews_types_description_array = $HTTP_POST_VARS['reviews_types_description'];

			$language_id = $languages[$i]['id'];

			$sql_data_array = array('reviews_types_path' => $reviews_types_path,
									'reviews_types_name' => tep_db_prepare_input($reviews_types_name_array[$language_id]),
									'sort_order' => (int)$HTTP_POST_VARS['sort_order'],
									'reviews_types_short_description' => tep_db_prepare_input($reviews_types_short_description_array[$language_id]),
									'reviews_types_description' => tep_db_prepare_input($reviews_types_description_array[$language_id]));

			if ($action == 'insert_type') {
			  $insert_sql_data = array('date_added' => 'now()',
									   'reviews_types_id' => $reviews_types_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_REVIEWS_TYPES, $sql_data_array);
			} elseif ($action == 'update_type') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_REVIEWS_TYPES, $sql_data_array, 'update', "reviews_types_id = '" . (int)$reviews_types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  tep_redirect(tep_href_link(FILENAME_REVIEWS, 'tID=' . $reviews_types_id));
		}
        break;
      case 'delete_type_confirm':
        $reviews_types_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

        tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_types_id = '" . (int)$reviews_types_id . "'");;
        tep_db_query("delete from " . TABLE_REVIEWS_TYPES . " where reviews_types_id = '" . (int)$reviews_types_id . "'");

        tep_redirect(tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'tID'))));
        break;
    }
  }

  $review_types = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $reviews_types_query = tep_db_query("select reviews_types_id, reviews_types_name from " . TABLE_REVIEWS_TYPES . "");
  while ($reviews_types = tep_db_fetch_array($reviews_types_query)) {
	$review_types[] = array('id' => $reviews_types['reviews_types_id'], 'text' => $reviews_types['reviews_types_name']);
	if (tep_not_null($tPath) && $tPath==$reviews_types['reviews_types_id']) $reviews_types_heading = $reviews_types['reviews_types_name'];
  }

  $review_ratings_array = array(array('id' => '', 'text' => '- - - - - -'), array('id' => '5', 'text' => '5 *****'), array('id' => '4', 'text' => '4 ****'), array('id' => '3', 'text' => '3 ***'), array('id' => '2', 'text' => '2 **'), array('id' => '1', 'text' => '1 *'));
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE . (tep_not_null($tPath) ? ' &raquo; ' . $reviews_types_heading : ''); ?></td>
			<td align="right"><?php
    echo tep_draw_form('goto', FILENAME_REVIEWS, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('tPath', $review_types, $tPath, 'onChange="this.form.submit();"');
    echo '</form>';
?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ($action == 'edit' || $action == 'new') {
    $rID = tep_db_prepare_input($HTTP_GET_VARS['rID']);

    $reviews_query = tep_db_query("select * from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$rID . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . "");
    $reviews = tep_db_fetch_array($reviews_query);
	if (!is_array($reviews)) $reviews = array();

    $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
    $products = tep_db_fetch_array($products_query);
	if (!is_array($products)) $products = array();

    $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $products_name = tep_db_fetch_array($products_name_query);
	if (!is_array($products_name)) $products_name = array();

    $rInfo_array = array_merge($reviews, $products, $products_name);
    $rInfo = new objectInfo($rInfo_array);

	if (empty($rInfo->products_image)) $rInfo->products_image = DIR_WS_CATALOG_TEMPLATES . 'images/nofoto.gif';
	else $rInfo->products_image = DIR_WS_CATALOG_IMAGES . 'thumbs/' . $rInfo->products_image;
	$product_image = tep_image((ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);

	if (strpos($rInfo->reviews_text, '<p')===false && strpos($rInfo->reviews_text, '<br')===false) $rInfo->reviews_text = nl2br($rInfo->reviews_text);

	$form_action = (isset($HTTP_GET_VARS['rID'])) ? 'update' : 'insert';
?>
      <tr><?php echo tep_draw_form('review', FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&action=' . $form_action) . (isset($HTTP_GET_VARS['rID']) ? tep_draw_hidden_field('reviews_id', $HTTP_GET_VARS['rID']) : ''); ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if ($action=='new') {
	  $rInfo->date_added = date('Y-m-d');
	  $rInfo->reviews_status = 1;
?>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_PRODUCT_LINK; ?></td>
            <td class="main"><?php echo tep_draw_input_field('product_link', '', 'size="50"'); ?></td>
            <td class="main" align="right" rowspan="9">&nbsp;</td>
          </tr>
<?php
	} else {
?>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_PRODUCT; ?></td>
            <td class="main"><?php echo '<a href="' . tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $rInfo->products_id) . '" target="_blank"><u>' . $rInfo->products_name . '</u></a>'; ?></td>
            <td class="main" align="right" rowspan="9"><?php echo $product_image; ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_FROM_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('customers_name', $rInfo->customers_name, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_FROM_EMAIL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('customers_email', $rInfo->customers_email, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_radio_field('reviews_status', '1', $rInfo->reviews_status==1) . '&nbsp;' . ENTRY_STATUS_ACTIVE . '&nbsp;' . tep_draw_radio_field('reviews_status', '0', $rInfo->reviews_status==0) . '&nbsp;' . ENTRY_STATUS_NOT_ACTIVE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var reviewAdded = new ctlSpiffyCalendarBox("reviewAdded", "review", "reviews_date_added", "btnDate1", "<?php echo tep_date_short($rInfo->date_added); ?>", scBTNMODE_CUSTOMBLUE);
</script>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_DATE; ?><br><small>(dd.mm.yyyy)</small></td>
            <td class="main"><script language="javascript">reviewAdded.writeControl(); reviewAdded.dateFormat="dd.MM.yyyy";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main" width="250"><?php echo ENTRY_REVIEW; ?></td>
            <td class="main" colspan="2"><?php
	  $field_value = $rInfo->reviews_text;
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('reviews_text');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	if ($action=='new') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo ENTRY_RATING; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('reviews_rating', $review_ratings_array); ?></td>
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
        <td align="right" class="main"><?php echo tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if (tep_not_null($tPath)) {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_RATING; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $reviews_query_raw = "select * from " . TABLE_REVIEWS . " where reviews_types_id = '" . (int)$tPath . "'" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by date_added desc";
	  $reviews_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
	  $reviews_query = tep_db_query($reviews_query_raw);
	  while ($reviews = tep_db_fetch_array($reviews_query)) {
		$review_rating_query = tep_db_query("select sum(reviews_vote)/count(*) as reviews_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
		$review_rating = tep_db_fetch_array($review_rating_query);
		if (!is_array($review_rating)) $review_rating = array();
		$reviews = array_merge($reviews, $review_rating);

		if ((!isset($HTTP_GET_VARS['rID']) || (isset($HTTP_GET_VARS['rID']) && ($HTTP_GET_VARS['rID'] == $reviews['reviews_id']))) && !isset($rInfo)) {
		  $product_info_query = tep_db_query("select products_image, products_name from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$reviews['products_id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if (!is_array($product_info)) $product_info = array();

		  $rInfo_array = array_merge($reviews, $product_info);
		  $rInfo = new objectInfo($rInfo_array);

		  if (empty($rInfo->products_image)) $rInfo->products_image = DIR_WS_CATALOG_TEMPLATES . 'images/nofoto.gif';
		  else $rInfo->products_image = DIR_WS_CATALOG_IMAGES . 'thumbs/' . $rInfo->products_image;
		  $rInfo->products_image = tep_image((ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
		}

		if (isset($rInfo) && is_object($rInfo) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . 'rID=' . $rInfo->reviews_id . '&action=preview') . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . 'rID=' . $reviews['reviews_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . 'rID=' . $reviews['reviews_id'] . '&action=preview') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . tep_get_products_name($reviews['products_id']); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_round($reviews['reviews_rating'], 1); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_date_short($reviews['date_added']); ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($reviews['reviews_status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&action=setflag&flag=0&rID=' . $reviews['reviews_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&action=setflag&flag=1&rID=' . $reviews['reviews_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($rInfo)) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . 'rID=' . $reviews['reviews_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('action', 'rID', 'page'))); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
	} else {
?>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="3"><?php echo TABLE_HEADING_TYPES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	  $reviews_types_query = tep_db_query("select * from " . TABLE_REVIEWS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, reviews_types_name");
	  while ($reviews_types = tep_db_fetch_array($reviews_types_query)) {
		if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $reviews_types['reviews_types_id']))) && !isset($tInfo) && substr($action, 0, 3)!='new') {
		  $tInfo_array = $reviews_types;
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($reviews_types['reviews_types_id'] == $tInfo->reviews_types_id)) {
		  echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('tID', 'action', 'page', 'rID')) . 'tID=' . $tInfo->reviews_types_id . '&action=edit_type') . '\'">' . "\n";
		} else {
		  echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('tID', 'action', 'page', 'rID')) . 'tID=' . $reviews_types['reviews_types_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="3" title="<?php echo $reviews_types['reviews_types_short_description']; ?>"><?php echo '<a href="' . tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types['reviews_types_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $reviews_types['sort_order'] . '] <strong>' . $reviews_types['reviews_types_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($reviews_types['reviews_types_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=0&tID=' . $reviews_types['reviews_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'flag', 'tID')) . 'action=setflag&flag=1&tID=' . $reviews_types['reviews_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($reviews_types['reviews_types_id'] == $tInfo->reviews_types_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('rID', 'action', 'page', 'tID')) . 'tID=' . $reviews_types['reviews_types_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
	  }
	}
	if (empty($action)) {
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td colspan="2" align="right"><?php if (tep_not_null($tPath)) echo '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('rID', 'tID', 'action', 'tPath', 'page')) . 'tID=' . $tPath) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('rID', 'tID', 'action')) . 'action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; elseif (DEBUG_MODE=='on') echo '&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('tID', 'rID', 'action')) . 'action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>' ?>&nbsp;</td>
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

        $contents = array('form' => tep_draw_form('types', FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'tID')) . 'action=' . ($action=='edit_type' ? 'update_type' : 'insert_type'), 'post') . ($action=='edit_type' ? tep_draw_hidden_field('reviews_types_id', $tInfo->reviews_types_id) : ''));
        $contents[] = array('text' => ($action=='edit_type' ? TEXT_EDIT_TYPE_INTRO : TEXT_NEW_TYPE_INTRO));

        $type_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('reviews_types_name[' . $languages[$i]['id'] . ']', tep_get_reviews_type_info($tInfo->reviews_types_id, $languages[$i]['id']), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_NAME . $type_inputs_string);

		$type_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('reviews_types_short_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', tep_get_reviews_type_info($tInfo->reviews_types_id, $languages[$i]['id'], 'reviews_types_short_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SHORT_DESCRIPTION . $type_inputs_string);

		$type_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $type_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('reviews_types_description[' . $languages[$i]['id'] . ']', 'soft', '30', '7', tep_get_reviews_type_info($tInfo->reviews_types_id, $languages[$i]['id'], 'reviews_types_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_DESCRIPTION . $type_inputs_string);

        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_REVIEWS) . tep_draw_input_field('reviews_types_path', $tInfo->reviews_types_path, 'size="' . (tep_not_null($tInfo->reviews_types_path) ? strlen($tInfo->reviews_types_path) - 1 : '7') . '"') . '/');

        $contents[] = array('text' => '<br>' . TEXT_EDIT_TYPES_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $tInfo->sort_order, 'size="3"'));

        $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='edit_type' ? tep_image_submit('button_update.gif', IMAGE_UPDATE) : tep_image_submit('button_insert.gif', IMAGE_INSERT)) . ' <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'tID')) . (tep_not_null($tInfo->reviews_types_id) ? 'tID=' . $tInfo->reviews_types_id : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_REVIEW . '</strong>');

        $contents = array('form' => tep_draw_form('reviews', FILENAME_REVIEWS, tep_get_all_get_params(array('action')) . 'action=move_confirm') . tep_draw_hidden_field('reviews_id', $rInfo->reviews_id));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $rInfo->customers_name, $rInfo->products_name) . '<br>' . tep_draw_pull_down_menu('move_to_review_type_id', $review_types, $tPath));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&rID=' . $rInfo->reviews_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('reviews', FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'tID', 'page')) . 'tID=' . $sInfo->reviews_types_id . '&action=delete_type_confirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->reviews_types_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('tID', 'action', 'page')) . 'tID=' . $tInfo->reviews_types_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
      case 'delete':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</strong>');

        $contents = array('form' => tep_draw_form('reviews', FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID', 'tID')) . 'rID=' . $rInfo->reviews_id . '&action=delete_confirm'));
        $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
        $contents[] = array('text' => '<br><strong>' . $rInfo->products_name . '</strong>');
        if (tep_not_null($rInfo->reviews_ip)) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('review_blacklist', '1', false, '', 'onclick="if (this.checked) document.getElementById(\'review_blacklist_comment\').style.display = \'block\'; else document.getElementById(\'review_blacklist_comment\').style.display = \'none\';"') . ' ' . TEXT_DELETE_REVIEW_BLACKLIST . '<div id="review_blacklist_comment" style="display: none;"><br>' . TEXT_DELETE_REVIEW_BLACKLIST_COMMENTS . '<br>' . tep_draw_input_field('review_blacklist_reason', TEXT_DELETE_REVIEW_BLACKLIST_COMMENTS_DEFAULT, 'size="35"') . '</div>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&rID=' . $rInfo->reviews_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
		if (is_object($tInfo)) {
		  $heading[] = array('text' => '<strong>' . $tInfo->reviews_types_name . '</strong>');

		  if (DEBUG_MODE=='on') $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('rID', 'action', 'tID')) . 'tID=' . $tInfo->reviews_types_id . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('rID', 'action', 'tID')) . 'tID=' . $tInfo->reviews_types_id . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br><br>');
		  if (tep_not_null($tInfo->reviews_types_short_description)) $contents[] = array('text' => $tInfo->reviews_types_short_description);
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tInfo->date_added));
		  if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tInfo->last_modified));
		} elseif (isset($rInfo) && is_object($rInfo)) {
		  $heading[] = array('text' => '<strong>' . $rInfo->products_name . '</strong>');

		  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&rID=' . $rInfo->reviews_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&rID=' . $rInfo->reviews_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_REVIEWS, tep_get_all_get_params(array('action', 'rID')) . '&rID=' . $rInfo->reviews_id . '&action=move') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a>');
		  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($rInfo->date_added));
		  if (tep_not_null($rInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($rInfo->last_modified));
		  $contents[] = array('text' => '<br>' . $rInfo->products_image);
		  $contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name);
		  $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . $rInfo->reviews_rating);
		  $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
		  $contents[] = array('text' => '<br>' . (strpos($rInfo->reviews_text, '<')===false ? nl2br($rInfo->reviews_text) : $rInfo->reviews_text));
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