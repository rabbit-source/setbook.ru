<?php
  require('includes/application_top.php');

  $geo_zones = array();
  $geo_zones_query = tep_db_query("select geo_zone_name, geo_zone_id from " . TABLE_GEO_ZONES . "");
  while ($geo_zones_array = tep_db_fetch_array($geo_zones_query)) {
	$geo_zones[] = array('id' => $geo_zones_array['geo_zone_id'], 'text' => $geo_zones_array['geo_zone_name']);
  }

  $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
  $module_key = 'MODULE_PAYMENT_INSTALLED';
  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($module_directory)) {
	while ($file = $dir->read()) {
	  if (!is_dir($module_directory . $file)) {
		if (substr($file, strrpos($file, '.')) == $file_extension) {
		  $directory_array[] = $file;
		}
	  }
	}
	sort($directory_array);
	$dir->close();
  }

  $payment_modules = array();
  $installed_payment = array();
  $modules = array();
  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	$file = $directory_array[$i];

	include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $file);
	include($module_directory . $file);

	$class = substr($file, 0, strrpos($file, '.'));
	if (tep_class_exists($class)) {
	  $module = new $class;
	  if ($module->check() > 0) {
		$payment_modules[] = array('id' => $file, 'text' => $module->title);
		$installed_payment[$file] = $module->title;
	  }
	}
  }

  if ($HTTP_GET_VARS['action']) {
    switch ($HTTP_GET_VARS['action']) {
      case 'insert':
      case 'save':
        if (isset($HTTP_POST_VARS['payment'])) $payment = tep_db_prepare_input($HTTP_POST_VARS['payment']);
        elseif (isset($HTTP_GET_VARS['sID'])) $payment = tep_db_prepare_input($HTTP_GET_VARS['sID']);
		$status = (isset($HTTP_POST_VARS['status'])) ? $HTTP_POST_VARS['status'] : '0';
		tep_db_query("delete from " . TABLE_PAYMENT_TO_GEO_ZONES . " where payment = '" . tep_db_input($payment) . "'");
        if (isset($HTTP_POST_VARS['geo']) && is_array($HTTP_POST_VARS['geo'])) {
		  while (list(, $geo_zone) = each($HTTP_POST_VARS['geo'])) {
			tep_db_query("insert into " . TABLE_PAYMENT_TO_GEO_ZONES . " (payment, geo_zone_id, status) values ('" . tep_db_input($payment) . "', '" . tep_db_input($geo_zone) . "', '" . (int)$status . "')");
		  }
        }
        tep_redirect(tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment));
        break;
      case 'deleteconfirm':
        if (isset($HTTP_POST_VARS['payment'])) $payment = tep_db_prepare_input($HTTP_POST_VARS['payment']);
        elseif (isset($HTTP_GET_VARS['sID'])) $payment = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        tep_db_query("delete from " . TABLE_PAYMENT_TO_GEO_ZONES . " where payment = '" . tep_db_input($payment) . "'");
        tep_redirect(tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'setflag':
        if (isset($HTTP_POST_VARS['payment'])) $payment = tep_db_prepare_input($HTTP_POST_VARS['payment']);
        elseif (isset($HTTP_GET_VARS['sID'])) $payment = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        tep_db_query("update " . TABLE_PAYMENT_TO_GEO_ZONES . " set status = '" . (int)$HTTP_GET_VARS['flag'] . "' where payment = '" . tep_db_input($payment) . "'");
        tep_redirect(tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment));
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GEO_ZONES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $payment_query_raw = "select payment, status from " . TABLE_PAYMENT_TO_GEO_ZONES . " group by payment";
  $payment_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $payment_query_raw, $payment_query_numrows);
  $payment_query = tep_db_query($payment_query_raw);
  while ($payment = tep_db_fetch_array($payment_query)) {
	$payment['payment_name'] = $installed_payment[$payment['payment']];
	$geo_zones_query = tep_db_query("select gz.geo_zone_name, gz.geo_zone_id from " . TABLE_GEO_ZONES . " gz, " . TABLE_PAYMENT_TO_GEO_ZONES . " s2gz where s2gz.geo_zone_id = gz.geo_zone_id and s2gz.payment = '" . tep_db_input($payment['payment']) . "'");
	$payment['geo_zone_names'] = '';
	$payment['geo_zones_available'] = array();
	while ($geo_zones_array = tep_db_fetch_array($geo_zones_query)) {
	  $payment['geo_zone_names'] .= $geo_zones_array['geo_zone_name'] . ';<br>';
	  $payment['geo_zones_available'][] = $geo_zones_array['geo_zone_id'];
	}
    if (((!$HTTP_GET_VARS['sID']) || ($HTTP_GET_VARS['sID'] == $payment['payment'])) && (!$sInfo) && (substr($HTTP_GET_VARS['action'], 0, 3) != 'new')) {
      $sInfo = new objectInfo($payment);
    }

    if ( (is_object($sInfo)) && ($payment['payment'] == $sInfo->payment) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->payment . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment['payment']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent">&nbsp;<?php echo $payment['payment_name']; ?></td>
                <td class="dataTableContent"><?php echo $payment['geo_zone_names']; ?></td>
                <td class="dataTableContent" align="center">
<?php
	if ($payment['status'] == '1') {
	  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment['payment'] . '&action=setflag&flag=0') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
	} else {
	  echo '<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment['payment'] . '&action=setflag&flag=1') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
	}
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($payment['payment'] == $sInfo->payment) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $payment['payment']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $payment_split->display_count($payment_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $payment_split->display_links($payment_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
<?php
  if (!$HTTP_GET_VARS['action']) {
?>
                  <tr>
                    <td colspan="5" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
  switch ($HTTP_GET_VARS['action']) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_PAYMENT . '</strong>');
      $contents = array('form' => tep_draw_form('payment', FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT . '<br>' . tep_draw_pull_down_menu('payment', $payment_modules, '', 'style="width: 270px;"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_GEO_ZONES . '<br>' . tep_draw_pull_down_menu('geo[]', $geo_zones, '', 'size="10" multiple style="width: 270px;"'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_PAYMENT . '</strong>');
      $contents = array('form' => tep_draw_form('payment', FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&action=save') . tep_draw_hidden_field('payment', $sInfo->payment) . tep_draw_hidden_field('status', $sInfo->status));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT . '<br>' . $sInfo->payment_name);
      $contents[] = array('text' => '<br>' . TEXT_INFO_GEO_ZONES . '<br>' . tep_draw_pull_down_menu('geo[]', $geo_zones, $sInfo->geo_zones_available, 'size="10" multiple style="width: 270px;"'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->payment) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PAYMENT . '</strong>');
      $contents = array('form' => tep_draw_form('payment', FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&action=deleteconfirm') . tep_draw_hidden_field('payment', $sInfo->payment));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $sInfo->payment_name . '</strong> &raquo; ' . $sInfo->geo_zone_names);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->payment) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<strong>' . $sInfo->payment_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->payment . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_PAY2GEO, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->payment . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_GEO_ZONES_ALLOWED . '<br>' . $sInfo->geo_zone_names);
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