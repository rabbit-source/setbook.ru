<?php
  require('includes/application_top.php');

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

  $module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
  $module_key = 'MODULE_SHIPPING_INSTALLED';
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

  $shipping_modules = array();
  $installed_shipping = array();
  $modules = array();
  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
	$file = $directory_array[$i];

	include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/shipping/' . $file);
	include($module_directory . $file);

	$class = substr($file, 0, strrpos($file, '.'));
	if (tep_class_exists($class)) {
	  $module = new $class;
	  if ($module->check() > 0) {
		$shipping_modules[] = array('id' => $class, 'text' => $module->title);
		$installed_shipping[$class] = $module->title;
	  }
	}
  }

  if ($HTTP_GET_VARS['action']) {
    switch ($HTTP_GET_VARS['action']) {
      case 'insert':
      case 'save':
        if (isset($HTTP_POST_VARS['shipping'])) $shipping = tep_db_prepare_input($HTTP_POST_VARS['shipping']);
        elseif (isset($HTTP_GET_VARS['sID'])) $shipping = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        if (isset($HTTP_POST_VARS['payment']) && is_array($HTTP_POST_VARS['payment'])) {
          $payment = tep_db_prepare_input(implode(";", $HTTP_POST_VARS['payment']));
		  tep_db_query("replace into " . TABLE_SHIPPING_TO_PAYMENT . " (shipping, payments, status) values ('" . tep_db_input($shipping) . "', '" . tep_db_input($payment)."', '1')");
        }
        tep_redirect(tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $shipping));
        break;
      case 'deleteconfirm':
        if (isset($HTTP_POST_VARS['shipping'])) $shipping = tep_db_prepare_input($HTTP_POST_VARS['shipping']);
        elseif (isset($HTTP_GET_VARS['sID'])) $shipping = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        tep_db_query("delete from " . TABLE_SHIPPING_TO_PAYMENT . " where shipping = '" . tep_db_input($shipping) . "'");
        tep_redirect(tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'setflag':
        if (isset($HTTP_POST_VARS['shipping'])) $shipping = tep_db_prepare_input($HTTP_POST_VARS['shipping']);
        elseif (isset($HTTP_GET_VARS['sID'])) $shipping = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        tep_db_query("update " . TABLE_SHIPPING_TO_PAYMENT . " set status = '" . (int)$HTTP_GET_VARS['flag'] . "' where shipping = '" . tep_db_input($shipping) . "'");
        tep_redirect(tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $shipping));
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SHIPMENT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $s2p_query_raw = "select shipping, payments, status from " . TABLE_SHIPPING_TO_PAYMENT;
  $s2p_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $s2p_query_raw, $s2p_query_numrows);
  $s2p_query = tep_db_query($s2p_query_raw);
  while ($s2p = tep_db_fetch_array($s2p_query)) {
	$s2p['shipping_name'] = $installed_shipping[$s2p['shipping']];
	$s2p['payments_name'] = '';
	$payments_installed = explode(';', $s2p['payments']);
	while (list(, $p) = each($payments_installed)) {
	  $s2p['payments_name'] .= $installed_payment[$p] . ';<br>';
	}
    if (((!$HTTP_GET_VARS['sID']) || ($HTTP_GET_VARS['sID'] == $s2p['shipping'])) && (!$sInfo) && (substr($HTTP_GET_VARS['action'], 0, 3) != 'new')) {
      $sInfo = new objectInfo($s2p);
    }

    if ( (is_object($sInfo)) && ($s2p['shipping'] == $sInfo->shipping) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->shipping . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $s2p['shipping']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent">&nbsp;<?php echo $s2p['shipping_name']; ?></td>
                <td class="dataTableContent"><?php echo $s2p['payments_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php
	if ($s2p['status'] == '1') {
	  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $s2p['shipping'] . '&action=setflag&flag=0') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
	} else {
	  echo '<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $s2p['shipping'] . '&action=setflag&flag=1') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
	}
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($s2p['shipping'] == $sInfo->shipping) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $s2p['shipping']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $s2p_split->display_count($s2p_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $s2p_split->display_links($s2p_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
<?php
  if (!$HTTP_GET_VARS['action']) {
?>
                  <tr>
                    <td colspan="5" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_SHP2PAY . '</strong>');
      $contents = array('form' => tep_draw_form('s2p', FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHIPMENT . '<br>' . tep_draw_pull_down_menu('shipping', $shipping_modules, '', 'style="width: 270px;"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENTS . '<br>' . tep_draw_pull_down_menu('payment[]', $payment_modules, '', 'size="5" multiple style="width: 270px;"'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SHP2PAY . '</strong>');
      $contents = array('form' => tep_draw_form('s2p', FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&action=save') . tep_draw_hidden_field('shipping', $sInfo->shipping));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_SHIPMENT . '<br>' . $sInfo->shipping_name);
      $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENTS . '<br>' . tep_draw_pull_down_menu('payment[]', $payment_modules, explode(';', $sInfo->payments), 'size="5" multiple style="width: 270px;"'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->shipping) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SHP2PAY . '</strong>');
      $contents = array('form' => tep_draw_form('s2p', FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&action=deleteconfirm') . tep_draw_hidden_field('shipping', $sInfo->shipping));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $sInfo->shipping_name . '</strong> &raquo; ' . $sInfo->payments_name);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_SHP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->shipping) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<strong>' . $sInfo->shipping_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->shipping . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_SHIP2PAY, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->shipping . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENTS_ALLOWED . '<br>' . $sInfo->payments_name);
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