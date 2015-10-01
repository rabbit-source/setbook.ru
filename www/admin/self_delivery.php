<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'load':
		$postcode_info_query = tep_db_query("select city_name, suburb_name, zone_name, city_country_id, zone_id from " . TABLE_CITIES . " where city_id = '" . tep_db_input($HTTP_GET_VARS['postcode']) . "' and city_country_id = '" . (int)$HTTP_GET_VARS['country'] . "'");
		$postcode_found = (tep_db_num_rows($postcode_info_query) < 1 ? false : true);
		$postcode_info = tep_db_fetch_array($postcode_info_query);
		if (!is_array($postcode_info)) $postcode_info = array();
		if ($HTTP_GET_VARS['type']=='state') {
		  if ($postcode_found) echo tep_draw_pull_down_menu('zone_id', tep_get_country_zones($postcode_info['city_country_id']), $postcode_info['zone_id']);
		  else echo tep_draw_input_field('zone_id', '', 'size="30"');
		} elseif ($HTTP_GET_VARS['type']=='suburb') {
		  echo tep_draw_input_field('suburb', $postcode_info['suburb_name'], 'size="30"');
		} elseif ($HTTP_GET_VARS['type']=='city') {
		  echo tep_draw_input_field('city', $postcode_info['city_name'], 'size="30"');
		} elseif ($HTTP_GET_VARS['type']=='postcode') {
		  echo tep_draw_input_field('postcode', $postcode_info['city_id'], 'size="10"');
		}
		die();
		break;
	  case 'setflag':
		if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['dID'])) {
			tep_db_query("update " . TABLE_SELF_DELIVERY . " set self_delivery_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where self_delivery_id = '" . tep_db_input($HTTP_GET_VARS['dID']) . "'");
		  }
		}

		tep_redirect(tep_href_link(FILENAME_SELF_DELIVERY, tep_get_all_get_params(array('action', 'flag'))));
		break;
      case 'insert':
      case 'save':
        if (isset($HTTP_GET_VARS['dID'])) $self_delivery_id = tep_db_prepare_input($HTTP_GET_VARS['dID']);

		if (tep_not_null($HTTP_POST_VARS['city_id'])) {
		  $city_info_query = tep_db_query("select city_name from " . TABLE_CITIES . " where city_id = '" . tep_db_input($HTTP_POST_VARS['city_id']) . "' and city_country_id = '" . (int)$HTTP_POST_VARS['country_id'] . "'" . (tep_not_null($HTTP_POST_VARS['zone_id']) ? " and zone_id = '" . (int)$HTTP_POST_VARS['zone_id'] . "'" : "") . "");
		  $city_info = tep_db_fetch_array($city_info_query);
		  $city_name = $city_info['city_name'];
		} else {
		  $city_name = $HTTP_POST_VARS['city'];
		}

		$sql_data_array = array('self_delivery_cost' => str_replace(',', '.', $HTTP_POST_VARS['self_delivery_cost']),
								'self_delivery_free' => str_replace(',', '.', $HTTP_POST_VARS['self_delivery_free']),
								'entry_country_id' => tep_db_prepare_input($HTTP_POST_VARS['country_id']),
								'entry_postcode' => tep_db_prepare_input($HTTP_POST_VARS['postcode']),
								'entry_zone_id' => tep_db_prepare_input($HTTP_POST_VARS['zone_id']),
								'entry_suburb' => tep_db_prepare_input($HTTP_POST_VARS['suburb']),
								'entry_city' => tep_db_prepare_input($city_name),
								'entry_street_address' => tep_db_prepare_input($HTTP_POST_VARS['street_address']),
								'entry_telephone' => tep_db_prepare_input($HTTP_POST_VARS['telephone']),
								'self_delivery_status' => tep_db_prepare_input($HTTP_POST_VARS['self_delivery_status']),
								'self_delivery_description' => tep_db_prepare_input($HTTP_POST_VARS['self_delivery_description']),
								'self_delivery_days' => tep_db_prepare_input($HTTP_POST_VARS['self_delivery_days']),
								'self_delivery_only_periodicals' => tep_db_prepare_input($HTTP_POST_VARS['self_delivery_only_periodicals']));

		if ($action == 'insert') {
		  $sql_data_array['date_added'] = 'now()';
		  tep_db_perform(TABLE_SELF_DELIVERY, $sql_data_array);
		  $self_delivery_id = tep_db_insert_id();
		} elseif ($action == 'save') {
		  $sql_data_array['last_modified'] = 'now()';
		  tep_db_perform(TABLE_SELF_DELIVERY, $sql_data_array, 'update', "self_delivery_id = '" . (int)$self_delivery_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_SELF_DELIVERY, tep_get_all_get_params(array('action', 'dID')) . 'dID=' . $self_delivery_id));
        break;
      case 'deleteconfirm':
        $dID = tep_db_prepare_input($HTTP_GET_VARS['dID']);

        tep_db_query("delete from " . TABLE_SELF_DELIVERY . " where self_delivery_id = '" . tep_db_input($dID) . "'");

        tep_redirect(tep_href_link(FILENAME_SELF_DELIVERY, tep_get_all_get_params(array('action', 'dID'))));
        break;
    }
  }
  $address_formats = tep_get_address_formats();
  $address_format_id = $address_formats[0]['id'];
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
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SELF_DELIVERY; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SELF_DELIVERY_COST; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
  $self_delivery_query_raw = "select * from " . TABLE_SELF_DELIVERY . " where 1";
  $self_delivery_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $self_delivery_query_raw, $self_delivery_query_numrows);
  $self_delivery_query = tep_db_query($self_delivery_query_raw);
  while ($self_delivery = tep_db_fetch_array($self_delivery_query)) {
	reset($self_delivery);
	while (list($k, $v) = each($self_delivery)) {
	  $k = str_replace('entry_', '', $k);
	  $self_delivery[$k] = $v;
	}
	$self_delivery['self_delivery_name'] = tep_address_format($address_format_id, $self_delivery, 1, '', ', ');
    if ((!isset($HTTP_GET_VARS['dID']) || (isset($HTTP_GET_VARS['dID']) && ($HTTP_GET_VARS['dID'] == $self_delivery['self_delivery_id']))) && !isset($dInfo) && (substr($action, 0, 3) != 'new')) {
      $dInfo = new objectInfo($self_delivery);
    }

    if (isset($dInfo) && is_object($dInfo) && ($self_delivery['self_delivery_id'] == $dInfo->self_delivery_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $dInfo->self_delivery_id . '&action=edit&page=' . $HTTP_GET_VARS['page']) . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $self_delivery['self_delivery_id'] . '&page=' . $HTTP_GET_VARS['page']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $self_delivery['self_delivery_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $currencies->format($self_delivery['self_delivery_cost'], false); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($self_delivery['self_delivery_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SELF_DELIVERY, tep_get_all_get_params(array('action', 'flag', 'dID')) . '&action=setflag&flag=0&dID=' . $self_delivery['self_delivery_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_SELF_DELIVERY, tep_get_all_get_params(array('action', 'flag', 'dID')) . '&action=setflag&flag=1&dID=' . $self_delivery['self_delivery_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($dInfo) && is_object($dInfo) && ($self_delivery['self_delivery_id'] == $dInfo->self_delivery_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $self_delivery['self_delivery_id'] . '&page=' . $HTTP_GET_VARS['page']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>

              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo $self_delivery_split->display_count($self_delivery_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $self_delivery_split->display_links($self_delivery_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
			  <tr>
				<td colspan="4" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=new&page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
			  </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
	case 'new':
    case 'edit':
	  $city_field = '';
	  $countries = tep_get_countries('', true);
	  if ($action=='new') {
		$self_delivery = array('country_id' => $countries[0]['id'],
							   'postcode' => '',
							   'zone_id' => 0,
							   'suburb' => '',
							   'city' => '',
							   'street_address' => '',
							   'telephone' => '',
							   'self_delivery_status' => 0,
							   'self_delivery_description' => '',
							   'self_delivery_cost' => '',
							   'self_delivery_free' => '',
							   'self_delivery_days' => '',
							   'self_delivery_only_periodicals' => '',);
		$dInfo = new objectInfo($self_delivery);
	  }
	  if (sizeof($countries==1)) {
		$country_cities = array();
		$old_city_name = '';
		$parent_cities_query = tep_db_query("select city_id, city_name from " . TABLE_CITIES . " where parent_id = '0' and suburb_name = '' order by city_id");
		while ($parent_cities = tep_db_fetch_array($parent_cities_query)) {
		  if ($old_city_name != $parent_cities['city_name']) {
			$country_cities[$parent_cities['city_id']] = $parent_cities['city_name'];
			$old_city_name = $parent_cities['city_name'];
		  }
		}
		asort($country_cities, SORT_LOCALE_STRING);
		$parent_cities_array = array(array('id' => '', 'text' => '- - - - - - - - - '));
		reset($country_cities);
		while (list($country_city_id, $country_city_name) = each($country_cities)) {
		  $parent_cities_array[] = array('id' => $country_city_id, 'text' => $country_city_name);
		}
		$city_field = tep_draw_pull_down_menu('city_id', $parent_cities_array, $dInfo->postcode, 'onchange="if (this.options[this.selectedIndex].value.length &gt;= 4 && country_id.options[country_id.selectedIndex].value &gt; 0) { getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=postcode') . '&postcode=\'+this.options[this.selectedIndex].value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_postcode\'); getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=state') . '&postcode=\'+this.options[this.selectedIndex].value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_state\'); getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=suburb') . '&postcode=\'+this.options[this.selectedIndex].value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_suburb\'); getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=city') . '&postcode=\'+this.options[this.selectedIndex].value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_city\'); }""');
	  }
	  if (empty($city_field)) $city_field = tep_draw_input_field('city', $dInfo->city, 'size="30"');

      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SELF_DELIVERY . '</strong>');

      $contents = array('form' => tep_draw_form('delivery', FILENAME_SELF_DELIVERY, 'page=' . $HTTP_GET_VARS['page'] . ($action=='new' ? '' : '&dID=' . $dInfo->self_delivery_id)  . '&action=' . ($action=='new' ? 'insert' : 'save')));
      $contents[] = array('text' => ($action=='new' ? TEXT_INFO_INSERT_INTRO : TEXT_INFO_EDIT_INTRO));

      $contents[] = array('text' => '<br>' . TEXT_INFO_SELF_DELIVERY_COUNTRY . '<br>' . tep_draw_pull_down_menu('country_id', $countries, $dInfo->country_id));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_POSTCODE . '<br><span id="delivery_postcode">' . tep_draw_input_field('postcode', $dInfo->postcode, 'size="10" onkeyup="if (this.value.length &gt;= 4 && country_id.options[country_id.selectedIndex].value &gt; 0) { getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=state') . '&postcode=\'+this.value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_state\'); getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=suburb') . '&postcode=\'+this.value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_suburb\'); getXMLDOM(\'' . tep_href_link(FILENAME_SELF_DELIVERY, 'action=load&type=city') . '&postcode=\'+this.value+\'&country=\'+country_id.options[country_id.selectedIndex].value, \'delivery_city\'); }"') . '</span>');

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_STATE . '<br><span id="delivery_state">' . tep_draw_pull_down_menu('zone_id', tep_get_country_zones($dInfo->country_id), $dInfo->zone_id) . '</span>');

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_SUBURB . '<br><span id="delivery_suburb">' . tep_draw_input_field('suburb', $dInfo->suburb, 'size="30"') . '</span>');

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_CITY . '<br><span id="delivery_city">' . $city_field . '</span>');

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_STREET_ADDRESS . '<br>' . tep_draw_textarea_field('street_address', 'soft', '30', '4', $dInfo->street_address));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_TELEPHONE . '<br>' . tep_draw_input_field('telephone', $dInfo->telephone, 'size="30"'));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_STATUS . '<br>' . tep_draw_radio_field('self_delivery_status', '1', $dInfo->self_delivery_status=='1') . TEXT_INFO_SELF_DELIVERY_STATUS_YES . '&nbsp; ' . tep_draw_radio_field('self_delivery_status', '0', $dInfo->self_delivery_status=='0') . TEXT_INFO_SELF_DELIVERY_STATUS_NO);

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_DESCRIPTION . '<br>' . tep_draw_textarea_field('self_delivery_description', 'soft', '30', '4', $dInfo->self_delivery_description));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_COST . '<br>' . tep_draw_input_field('self_delivery_cost', ($dInfo->self_delivery_cost>0 ? (string)(float)$dInfo->self_delivery_cost : ''), 'size="5"'));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_FREE . '<br>' . tep_draw_input_field('self_delivery_free', ($dInfo->self_delivery_free>0 ? (string)(float)$dInfo->self_delivery_free : ''), 'size="5"'));

      $contents[] = array('text' => TEXT_INFO_SELF_DELIVERY_DAYS . '<br>' . tep_draw_input_field('self_delivery_days', ($dInfo->self_delivery_days>0 ? (int)$dInfo->self_delivery_days : ''), 'size="2"'));

      $contents[] = array('text' => tep_draw_checkbox_field('self_delivery_only_periodicals', '1', $dInfo->self_delivery_only_periodicals=='1') . TEXT_INFO_SELF_DELIVERY_ONLY_PERIODICALS);

      $contents[] = array('align' => 'center', 'text' => '<br>' . ($action=='new' ? tep_image_submit('button_insert.gif', IMAGE_INSERT) : tep_image_submit('button_update.gif', IMAGE_UPDATE)) . ' <a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'page=' . $HTTP_GET_VARS['page'] . ($action=='new' ? '' : '&dID=' . $dInfo->self_delivery_id)) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SELF_DELIVERY . '</strong>');

      $contents = array('form' => tep_draw_form('status', FILENAME_SELF_DELIVERY, 'dID=' . $dInfo->self_delivery_id  . '&action=deleteconfirm&page=' . $HTTP_GET_VARS['page']));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $dInfo->self_delivery_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $dInfo->self_delivery_id . '&page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($dInfo) && is_object($dInfo)) {
        $heading[] = array('text' => '<strong>' . $dInfo->city . ', ' . $dInfo->street_address . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $dInfo->self_delivery_id . '&action=edit&page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SELF_DELIVERY, 'dID=' . $dInfo->self_delivery_id . '&action=delete&page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

        $contents[] = array('text' => '<br>' . $dInfo->self_delivery_description);

        $contents[] = array('text' => '<br>' . TEXT_INFO_SELF_DELIVERY_COST . ' ' . $currencies->format($dInfo->self_delivery_cost, false) . (($dInfo->self_delivery_cost>0 && $dInfo->self_delivery_free>0) ? ' (' . TEXT_INFO_SELF_DELIVERY_FREE . ' ' . $currencies->format($dInfo->self_delivery_free, false) . ')' : ''));

        $contents[] = array('text' => '<br>' . TEXT_INFO_SELF_DELIVERY_DAYS . ' ' . $dInfo->self_delivery_days);
		if ($dInfo->self_delivery_only_periodicals=='1') $contents[] = array('text' => '<br>' . TEXT_INFO_SELF_DELIVERY_ONLY_PERIODICALS);

        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_datetime_short($dInfo->date_added));
        if (tep_not_null($dInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_datetime_short($dInfo->last_modified));
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
