<?php
  require('includes/application_top.php');

  function tep_has_city_subcities($city_id, $city_name='') {
    $child_city_query = tep_db_query("select count(*) as count from " . TABLE_CITIES . " where parent_id = '" . tep_db_input($city_id) . "'" . (tep_not_null($city_name) ? " and city_name <> '" . tep_db_input($city_name) . "'" : ""));
    $child_city = tep_db_fetch_array($child_city_query);

    if ($child_city['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }

  function tep_get_zone_cities($zone_id, $all = false) {
	$cities = array();
	if ($all) {
	  $cities_query = tep_db_query("select city_id, if(suburb_name='',city_name,concat_ws('', city_name, ' (', suburb_name, ')')) as city_name from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "' order by city_name, city_id");
	} else {
//	  $cities_query = tep_db_query("select c.city_id, c.city_name from " . TABLE_CITIES . " c left join " . TABLE_CITIES_TO_GEO_ZONES . " c2gz on (c.city_id = c2gz.city_id) where c.zone_id = '" . (int)$zone_id . "' and c.parent_id = '0' and c2gz.city_id is null order by c.city_name");
	  $cities_query = tep_db_query("select city_id, city_name from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "' and (parent_id = '0' or suburb_name = '') order by city_name, city_id");
	}
	while ($cities_array = tep_db_fetch_array($cities_query)) {
	  $cities[$cities_array['city_id']] = $cities_array['city_name'];
	}
	return $cities;
  }

  $saction = (isset($HTTP_GET_VARS['saction']) ? $HTTP_GET_VARS['saction'] : '');

  if (tep_not_null($saction)) {
	switch ($saction) {
	  case 'insert_sub':
		$zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
		$zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
		$zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
        $zone_factor = tep_db_prepare_input($HTTP_POST_VARS['zone_factor']);
        $zone_delivery_time = tep_db_prepare_input($HTTP_POST_VARS['zone_delivery_time']);

		tep_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, geo_zone_id, zone_factor, zone_delivery_time, date_added) values ('" . (int)$zone_country_id . "', '" . (int)$zone_id . "', '" . (int)$zID . "', '" . (double)$zone_factor . "', '" . tep_db_prepare_input($zone_delivery_time) . "', now())");
		$new_subzone_id = tep_db_insert_id();

		if (is_array($HTTP_POST_VARS['city_id'])) {
		  while (list(, $city) = each($HTTP_POST_VARS['city_id'])) {
			$subcities = array();
			$subcities[] = $city;
			tep_get_subcities($subcities, $city);
			while (list(, $city_id) = each($subcities)) {
			  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, geo_zone_id, date_added) values ('" . tep_db_input($city_id) . "', '" . (int)$new_subzone_id . "', '" . (int)$zID . "', now())");
			}
		  }
		} else {
		  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, geo_zone_id, date_added) select city_id, '" . (int)$new_subzone_id . "', '" . (int)$zID . "', now() from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $new_subzone_id));
		break;
	  case 'moveconfirm_sub':
		$sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);
		$zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
		$geo_zone_id = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_id']);
        $zone_factor = tep_db_prepare_input($HTTP_POST_VARS['zone_factor']);
        $zone_delivery_time = tep_db_prepare_input($HTTP_POST_VARS['zone_delivery_time']);

		tep_db_query("update " . TABLE_ZONES_TO_GEO_ZONES . " set geo_zone_id = '" . (int)$geo_zone_id . "', zone_factor = '" . (double)$zone_factor . "', zone_delivery_time = '" . tep_db_prepare_input($zone_delivery_time) . "', last_modified = now() where association_id = '" . (int)$sID . "'");

		tep_db_query("update " . TABLE_CITIES_TO_GEO_ZONES . " set geo_zone_id = '" . (int)$geo_zone_id . "', last_modified = now() where association_id = '" . (int)$sID . "'");

		tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zID=' . $geo_zone_id . '&action=list&sID=' . $HTTP_GET_VARS['sID']));
		break;
	  case 'save_sub':
		$sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);
		$zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
		$zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
		$zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);
        $zone_factor = tep_db_prepare_input($HTTP_POST_VARS['zone_factor']);
        $zone_delivery_time = tep_db_prepare_input($HTTP_POST_VARS['zone_delivery_time']);

		tep_db_query("update " . TABLE_ZONES_TO_GEO_ZONES . " set geo_zone_id = '" . (int)$zID . "', zone_country_id = '" . (int)$zone_country_id . "', zone_id = " . (tep_not_null($zone_id) ? "'" . (int)$zone_id . "'" : 'null') . ", zone_factor = '" . (double)$zone_factor . "', zone_delivery_time = '" . tep_db_prepare_input($zone_delivery_time) . "', last_modified = now() where association_id = '" . (int)$sID . "'");

		tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");
		if (is_array($HTTP_POST_VARS['city_id'])) {
		  while (list(, $city) = each($HTTP_POST_VARS['city_id'])) {
			$subcities = array();
			$subcities[] = $city;
//			tep_get_subcities($subcities, $city);
			while (list(, $city_id) = each($subcities)) {
			  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, geo_zone_id, date_added) values ('" . tep_db_input($city_id) . "', '" . (int)$sID . "', '" . (int)$zID . "', now())");
			}
		  }
		} else {
		  tep_db_query("replace into " . TABLE_CITIES_TO_GEO_ZONES . " (city_id, association_id, geo_zone_id, date_added) select city_id, '" . (int)$sID . "', '" . (int)$zID . "', now() from " . TABLE_CITIES . " where zone_id = '" . (int)$zone_id . "'");
		}

		tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $HTTP_GET_VARS['sID']));
		break;
	  case 'deleteconfirm_sub':
		$sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);

		tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");
		tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");

		tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage']));
		break;
	}
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert_zone':
        $geo_zone_name = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_name']);
        $geo_zone_description = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_description']);

        tep_db_query("insert into " . TABLE_GEO_ZONES . " (geo_zone_name, geo_zone_description, date_added) values ('" . tep_db_input($geo_zone_name) . "', '" . tep_db_input($geo_zone_description) . "', now())");
        $new_zone_id = tep_db_insert_id();

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $new_zone_id));
        break;
      case 'save_zone':
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
        $geo_zone_name = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_name']);
        $geo_zone_description = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_description']);

        tep_db_query("update " . TABLE_GEO_ZONES . " set geo_zone_name = '" . tep_db_input($geo_zone_name) . "', geo_zone_description = '" . tep_db_input($geo_zone_description) . "', last_modified = now() where geo_zone_id = '" . (int)$zID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID']));
        break;
      case 'deleteconfirm_zone':
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);

        tep_db_query("delete from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int)$zID . "'");
        tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zID . "'");
        tep_db_query("delete from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage']));
        break;
    }
  }
  if (isset($HTTP_GET_VARS['country_id']) && tep_not_null($HTTP_GET_VARS['country_id'])) {
	header('Content-type: text/html; charset=' . CHARSET . '');
	echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
  } else {
	echo '<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">';
  }
?>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>"/>
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
<script language="javascript" src="includes/general.js"></script>
<?php
  if (isset($HTTP_GET_VARS['zID']) && (($saction == 'edit') || ($saction == 'new'))) {
?>
<script language="javascript"><!--
var xmldoc;
var ns4 = document.layers ? true : false;
var ie = (typeof window.ActiveXObject != 'undefined');
var moz = (typeof document.implementation.createDocument!='undefined');

function resetZoneSelected(theForm) {
  if (theForm.state.value != '') {
    theForm.zone_id.selectedIndex = '0';
    if (theForm.zone_id.options.length > 0) {
      theForm.state.value = '<?php echo JS_STATE_SELECT; ?>';
    }
  }
}

function update_zone(theForm) {
  var NumState = theForm.zone_id.options.length;
  var SelectedCountry = "";

  while(NumState > 0) {
    NumState--;
    theForm.zone_id.options[NumState] = null;
  }         

  SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

<?php echo tep_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

}
//--></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?php
  if (isset($HTTP_GET_VARS['country_id']) && tep_not_null($HTTP_GET_VARS['country_id'])) {
	if (isset($HTTP_GET_VARS['zone_id'])) {
	  $cities_array = tep_get_zone_cities($HTTP_GET_VARS['zone_id'], false);
	  while (list($city_id, $city_name) = each($cities_array)) {
		$cities[] = array('id' => $city_id, 'text' => $city_name);
	  }
	  echo '<br/>' . TEXT_INFO_CITY_NAME . '<br/>' . tep_draw_pull_down_menu('city_id[]', $cities, '', 'size="15" style="width: 100%;" multiple="multiple"');
	} else {
	  $zones = array();
	  $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$HTTP_GET_VARS['country_id'] . "' order by sort_order, zone_name");
	  while ($zones_array = tep_db_fetch_array($zones_query)) {
		$zones[] = array('id' => $zones_array['zone_id'], 'text' => $zones_array['zone_name']);
	  }
	  echo '<br/>' . TEXT_INFO_COUNTRY_ZONE . '<br/>' . tep_draw_pull_down_menu('zone_id', $zones, '', 'onChange="getXMLDOM(\'' . FILENAME_GEO_ZONES . '?country_id=\' + document.zones.zone_country_id.options[document.zones.zone_country_id.selectedIndex].value + \'&amp;zone_id=\' + this.options[this.selectedIndex].value, \'city_id\');"') . '<br/><div id="city_id"></div>';
	}
?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
<?php
	exit;
  }
?>
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
        <td class="pageHeading"><?php echo HEADING_TITLE; if (isset($HTTP_GET_VARS['zone'])) echo '<br><span class="smallText">' . tep_get_geo_zone_name($HTTP_GET_VARS['zone']) . '</span>'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if ($action == 'cities') {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_CITY; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_CITY_POSTCODE; ?></td>
              </tr>
<?php
    $rows = 0;
    $cities_query = tep_db_query("select c.city_name, c.city_id, z.zone_name, co.countries_name from " . TABLE_CITIES . " c, " . TABLE_CITIES_TO_GEO_ZONES . " c2gz, " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " co where c.city_id = c2gz.city_id and c.parent_id = '0' and c.city_country_id = co.countries_id and c.zone_id = z.zone_id and c.city_country_id = z.zone_country_id and c2gz.association_id = " . $HTTP_GET_VARS['sID'] . " order by city_name");
    while ($cities = tep_db_fetch_array($cities_query)) {
      $rows++;
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
?>
                <td class="dataTableContent"><?php echo (($cities['countries_name']) ? $cities['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent" align="center"><?php echo $cities['zone_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cities['city_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cities['city_id']; ?></td>
              </tr>
<?php
    }
?>
              <tr>
                <td align="right" colspan="4"><?php if (empty($saction)) echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] : '')) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  } elseif ($action == 'list') {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_FACTOR; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COUNTRY_DELIVERY_TIME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $rows = 0;
    $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.zone_factor, a.zone_delivery_time, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_GEO_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.geo_zone_id = " . $HTTP_GET_VARS['zID'] . " order by z.zone_name";
    $zones_split = new splitPageResults($HTTP_GET_VARS['spage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $zones_query = tep_db_query($zones_query_raw);
    while ($zones = tep_db_fetch_array($zones_query)) {
      $rows++;
      if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $zones['association_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
		$num_cities_query = tep_db_query("select count(*) as num_cities from " . TABLE_CITIES . " c, " . TABLE_CITIES_TO_GEO_ZONES . " c2gz where c.city_id = c2gz.city_id and c.parent_id = '0' and c2gz.association_id = '" . $zones['association_id'] . "'");
		$num_cities = tep_db_fetch_array($num_cities_query);
		$zones['num_cities'] = $num_cities['num_cities'];
		$zone_localities = array();
		$localities_query = tep_db_query("select city_id from " . TABLE_CITIES_TO_GEO_ZONES . " where association_id = '" . $zones['association_id'] . "'");
		while ($localities = tep_db_fetch_array($localities_query)) {
		  $zone_localities[] = $localities['city_id'];
		}
		$zones['zone_localities'] = $zone_localities;
		$zones['num_localities'] = tep_db_num_rows($localities_query);
        $sInfo = new objectInfo($zones);
      }
      if (isset($sInfo) && is_object($sInfo) && ($zones['association_id'] == $sInfo->association_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $zones['association_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo (($zones['countries_name']) ? $zones['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id'] . '&action=cities&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $zones['association_id']) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;' . (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT); ?></td>
                <td class="dataTableContent" align="center"><?php echo $zones['zone_factor']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $zones['zone_delivery_time']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($zones['association_id'] == $sInfo->association_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $zones['association_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['spage'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['spage'], 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list', 'spage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="5"><?php if (empty($saction)) echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a> <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($sInfo) ? 'sID=' . $sInfo->association_id . '&' : '') . 'saction=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  } else {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $zones_query_raw = "select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_GEO_ZONES . " order by geo_zone_name";
    $zones_split = new splitPageResults($HTTP_GET_VARS['zpage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $zones_query = tep_db_query($zones_query_raw);
    while ($zones = tep_db_fetch_array($zones_query)) {
      if ((!isset($HTTP_GET_VARS['zID']) || (isset($HTTP_GET_VARS['zID']) && ($HTTP_GET_VARS['zID'] == $zones['geo_zone_id']))) && !isset($zInfo) && (substr($action, 0, 3) != 'new')) {
        $num_zones_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zones['geo_zone_id'] . "' group by geo_zone_id");
        $num_zones = tep_db_fetch_array($num_zones_query);

        if ($num_zones['num_zones'] > 0) {
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }

        $num_cities_query = tep_db_query("select count(*) as num_cities from " . TABLE_CITIES . " c, " . TABLE_CITIES_TO_GEO_ZONES . " c2gz where c.city_id = c2gz.city_id and c.parent_id = '0' and c2gz.geo_zone_id = '" . (int)$zones['geo_zone_id'] . "'");
        $num_cities = tep_db_fetch_array($num_cities_query);

        if ($num_cities['num_cities'] > 0) {
          $zones['num_cities'] = $num_cities['num_cities'];
        } else {
          $zones['num_cities'] = 0;
        }

        $num_localities_query = tep_db_query("select count(*) as num_localities from " . TABLE_CITIES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zones['geo_zone_id'] . "'");
        $num_localities = tep_db_fetch_array($num_localities_query);

        if ($num_localities['num_localities'] > 0) {
          $zones['num_localities'] = $num_localities['num_localities'];
        } else {
          $zones['num_localities'] = 0;
        }

        $zInfo = new objectInfo($zones);
      }
      if (isset($zInfo) && is_object($zInfo) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id'] . '&action=list') . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;' . $zones['geo_zone_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($zInfo) && is_object($zInfo) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['zpage'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['zpage'], '', 'zpage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="2"><?php if (!$action) echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  }
?>
            </td>
<?php
  $heading = array();
  $contents = array();

  if ($action == 'list') {
    switch ($saction) {
      case 'new':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] . '&' : '') . 'saction=insert_sub'));
        $contents[] = array('text' => TEXT_INFO_NEW_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . '<br>' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries(TEXT_ALL_COUNTRIES), '', 'onChange="getXMLDOM(\'' . FILENAME_GEO_ZONES . '?country_id=\'+this.options[this.selectedIndex].value, \'zone_id\');"') . '<br><div id="zone_id"></div>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_FACTOR . '<br>' . tep_draw_input_field('zone_factor', '', 'size="4"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_DELIVERY_TIME . '<br>' . tep_draw_input_field('zone_delivery_time'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub'));
        $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . '<br>' . tep_draw_hidden_field('zone_country_id', $sInfo->zone_country_id) . $sInfo->countries_name);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_ZONE . '<br>' . tep_draw_hidden_field('zone_id', $sInfo->zone_id) . $sInfo->zone_name);
		$zone_cities = tep_get_cities_tree($sInfo->zone_id, 0, '', 0);
		$contents[] = array('text' => '<br>' . TEXT_INFO_CITY_NAME . '<br>' . tep_draw_pull_down_menu('city_id[]', $zone_cities, $sInfo->zone_localities, 'size="15" style="width: 100%;" multiple="multiple"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_FACTOR . '<br>' . tep_draw_input_field('zone_factor', $sInfo->zone_factor, 'size="4"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_DELIVERY_TIME . '<br>' . tep_draw_input_field('zone_delivery_time', $sInfo->zone_delivery_time));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub'));
        $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br><strong>' . $sInfo->zone_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_SUB_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=moveconfirm_sub'));

		$zones = array();
		$zones_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
		while ($zones_array = tep_db_fetch_array($zones_query)) {
		  $zones[] = array('id' => $zones_array['geo_zone_id'], 'text' => $zones_array['geo_zone_name']);
		}
        $contents[] = array('text' => '<br>' . TEXT_INFO_NEW_ZONE_NAME . '<br>' . tep_draw_pull_down_menu('geo_zone_id', $zones, $HTTP_GET_VARS['zID']));
        $contents[] = array('text' => '<br>' . TEXT_INFO_NEW_FACTOR . '<br>' . tep_draw_input_field('zone_factor', $sInfo->zone_factor, 'size="4"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_NEW_DELIVERY_TIME . '<br>' . tep_draw_input_field('zone_delivery_time', $sInfo->zone_delivery_time));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($sInfo) && is_object($sInfo)) {
          $heading[] = array('text' => '<strong>' . $sInfo->zone_name . '</strong>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=move') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_CITIES . ' ' . $sInfo->num_cities);
          $contents[] = array('text' => TEXT_INFO_NUMBER_LOCALITIES . ' ' . $sInfo->num_localities);
          $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added));
          if (tep_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified));
        }
        break;
    }
  } else {
    switch ($action) {
      case 'new_zone':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=insert_zone'));
        $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . tep_draw_input_field('geo_zone_name', '', 'size="32"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . tep_draw_input_field('geo_zone_description', '', 'size="32"'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit_zone':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone'));
        $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . tep_draw_input_field('geo_zone_name', $zInfo->geo_zone_name, 'size="32"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . tep_draw_input_field('geo_zone_description', $zInfo->geo_zone_description, 'size="32"'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_zone':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ZONE . '</strong>');

        $contents = array('form' => tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=deleteconfirm_zone'));
        $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
        $contents[] = array('text' => '<br><strong>' . $zInfo->geo_zone_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($zInfo) && is_object($zInfo)) {
          $heading[] = array('text' => '<strong>' . $zInfo->geo_zone_name . '</strong>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' . ' <a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '">' . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones);
          $contents[] = array('text' => TEXT_INFO_NUMBER_CITIES . ' ' . $zInfo->num_cities);
          $contents[] = array('text' => TEXT_INFO_NUMBER_LOCALITIES . ' ' . $zInfo->num_localities);
          $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($zInfo->date_added));
          if (tep_not_null($zInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($zInfo->last_modified));
          $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . $zInfo->geo_zone_description);
        }
        break;
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
