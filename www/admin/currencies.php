<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($HTTP_GET_VARS['cID'])) $currency_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
        $title = tep_db_prepare_input($HTTP_POST_VARS['title']);
        $code = tep_db_prepare_input($HTTP_POST_VARS['code']);
        $symbol_left = tep_db_prepare_input($HTTP_POST_VARS['symbol_left']);
        $symbol_right = tep_output_string_protected($HTTP_POST_VARS['symbol_right']);
        $decimal_point = tep_db_prepare_input($HTTP_POST_VARS['decimal_point']);
        $thousands_point = tep_db_prepare_input($HTTP_POST_VARS['thousands_point']);
        $decimal_places = tep_db_prepare_input($HTTP_POST_VARS['decimal_places']);
        $value = tep_db_prepare_input($HTTP_POST_VARS['value']);
        $allow_auto_update = tep_db_prepare_input($HTTP_POST_VARS['allow_auto_update']);
		$value = str_replace(',', '.', $value);

		$old_value = 0;
        if (isset($HTTP_GET_VARS['cID'])) {
		  $old_value = $currencies->currencies[$code]['value'];
		}

        $sql_data_array = array('title' => $title,
                                'code' => $code,
                                'symbol_left' => $symbol_left,
                                'symbol_right' => $symbol_right,
                                'decimal_point' => $decimal_point,
                                'thousands_point' => $thousands_point,
                                'decimal_places' => $decimal_places,
                                'value' => $value,
                                'allow_auto_update' => $allow_auto_update);

        if ($action == 'insert') {
          tep_db_perform(TABLE_CURRENCIES, $sql_data_array);
          $currency_id = tep_db_insert_id();
        } elseif ($action == 'save') {
		  $sql_data_array['last_updated'] = 'now()';

          tep_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency_id . "'");

		  unset($currencies);
		  $currencies = new currencies();

//		  if ($old_value!=$value) {
//			if ($old_value > $value) $diff = 100 - round($value / $old_value * 100);
//			else $diff = 100 - round($old_value / $value * 100);
//			if ($diff <= 5) {
//			  $messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_NOT_CHANGED, $currency['title'], $currency['code'], $diff));
//			  unset($sql_data_array['value']);
//			} else {
			  tep_update_shops_prices('', $code);
//			}
//		  }
        }

        if (isset($HTTP_POST_VARS['default']) && ($HTTP_POST_VARS['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_redirect(tep_href_link(FILENAME_CURRENCIES, 'cID=' . $currency_id));
        break;
      case 'deleteconfirm':
        $currencies_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        $currency_query = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . DEFAULT_CURRENCY . "'");
        $currency = tep_db_fetch_array($currency_query);

        if ($currency['currencies_id'] == $currencies_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_db_query("delete from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");

        tep_redirect(tep_href_link(FILENAME_CURRENCIES));
        break;
      case 'update':
        $server_used = CURRENCY_SERVER_PRIMARY;
		$currency_id = $HTTP_GET_VARS['currencyID'];

        $currency_query = tep_db_query("select currencies_id, code, title, value from " . TABLE_CURRENCIES . " where allow_auto_update = '1' and value <> '1'" . (tep_not_null($currency_id) ? " and currencies_id = '" . (int)$currency_id . "'" : ""));
        while ($currency = tep_db_fetch_array($currency_query)) {
          $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
          $value = $quote_function($currency['code']);
		  $old_value = $currency['value'];

          if (empty($value) && (tep_not_null(CURRENCY_SERVER_BACKUP))) {
            $messageStack->add_session(sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency['title'], $currency['code']), 'warning');

            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
            $value = $quote_function('', $currency['code']);

            $server_used = CURRENCY_SERVER_BACKUP;
          }

          if (tep_not_null($value)) {
			if ($old_value!=$value) {
			  if ($old_value > $value) $diff = 100 - round($value / $old_value * 100);
			  else $diff = 100 - round($old_value / $value * 100);
			  if ($diff <= 5) {
				$messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_NOT_CHANGED, $currency['title'], $currency['code'], $diff));
			  } else {
				tep_update_shops_prices('', $currency['code']);
				tep_db_query("update " . TABLE_CURRENCIES . " set value = '" . $value . "', last_updated = now() where currencies_id = '" . (int)$currency['currencies_id'] . "'");
				$messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency['title'], $currency['code'], $server_used), 'success');
			  }
			} else {
			  $messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_NOT_CHANGED_1, $currency['title'], $currency['code']));
			}
          } else {
            $messageStack->add_session(sprintf(ERROR_CURRENCY_INVALID, $currency['title'], $currency['code'], $server_used), 'error');
          }
        }

		unset($currencies);
		$currencies = new currencies();

        tep_redirect(tep_href_link(FILENAME_CURRENCIES, 'cID=' . $HTTP_GET_VARS['cID']));
        break;
      case 'delete':
        $currencies_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        $currency_query = tep_db_query("select code from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");
        $currency = tep_db_fetch_array($currency_query);

        $remove_currency = true;
        if ($currency['code'] == DEFAULT_CURRENCY) {
          $remove_currency = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
        }
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_CODES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $currency_query_raw = "select * from " . TABLE_CURRENCIES . " order by title";
  $currency_query = tep_db_query($currency_query_raw);
  while ($currency = tep_db_fetch_array($currency_query)) {
    if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $currency['currencies_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($currency);
	  $cInfo->symbol_left = str_replace(' ', '&nbsp;', $currency['symbol_left']);
	  $cInfo->symbol_right = str_replace(' ', '&nbsp;', $currency['symbol_right']);
    }

    if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $currency['currencies_id']) . '\'">' . "\n";
    }

    if (DEFAULT_CURRENCY == $currency['code']) {
      echo '                <td class="dataTableContent"><strong>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $currency['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $currency['code']; ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($currency['value'], 8); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $currency['currencies_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
  if (empty($action)) {
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td><?php if (CURRENCY_SERVER_PRIMARY) { echo '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=update') . '">' . tep_image_button('button_update_currencies.gif', IMAGE_UPDATE_CURRENCIES) . '</a>'; } ?></td>
                    <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=new') . '">' . tep_image_button('button_new_currency.gif', IMAGE_NEW_CURRENCY) . '</a>'; ?></td>
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
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</strong>');

      $contents = array('form' => tep_draw_form('currencies', FILENAME_CURRENCIES, (isset($cInfo) ? 'cID=' . $cInfo->currencies_id : '') . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . tep_draw_input_field('title'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . tep_draw_input_field('code'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br>' . tep_draw_input_field('symbol_left'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br>' . tep_draw_input_field('symbol_right'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . tep_draw_input_field('decimal_point'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . tep_draw_input_field('thousands_point'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . tep_draw_input_field('decimal_places'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . tep_draw_input_field('value'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('allow_auto_update', '1') . TEXT_INFO_CURRENCY_ALLOW_AUTO_UPDATE);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $HTTP_GET_VARS['cID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</strong>');

      $contents = array('form' => tep_draw_form('currencies', FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . tep_draw_input_field('title', $cInfo->title));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . tep_draw_input_field('code', $cInfo->code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br>' . tep_draw_input_field('symbol_left', $cInfo->symbol_left));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br>' . tep_draw_input_field('symbol_right', $cInfo->symbol_right));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . tep_draw_input_field('decimal_point', $cInfo->decimal_point));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . tep_draw_input_field('thousands_point', $cInfo->thousands_point));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . tep_draw_input_field('decimal_places', $cInfo->decimal_places));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . tep_draw_input_field('value', $cInfo->value));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('allow_auto_update', '1', $cInfo->allow_auto_update=='1') . TEXT_INFO_CURRENCY_ALLOW_AUTO_UPDATE);
      if (DEFAULT_CURRENCY != $cInfo->code) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</strong>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $cInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . (($remove_currency) ? '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=deleteconfirm') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' : '') . ' <a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' . ($cInfo->allow_auto_update=='1' ? ' <a href="' . tep_href_link(FILENAME_CURRENCIES, 'cID=' . $cInfo->currencies_id . '&currencyID=' . $cInfo->currencies_id . '&action=update') . '">' . tep_image_button('button_update.gif', IMAGE_UPDATE) . '</a>' : ''));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . tep_date_short($cInfo->last_updated));
        $contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
        $contents[] = array('text' => TEXT_INFO_CURRENCY_ALLOW_AUTO_UPDATE . ' ' . ($cInfo->allow_auto_update=='1' ? TEXT_YES : TEXT_NO));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_EXAMPLE . '<br>' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
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