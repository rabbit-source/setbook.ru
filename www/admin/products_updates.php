<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'update_prices':
		$update_products_prices = false;
		$update_products_params = false;
		if ( ((double)$HTTP_POST_VARS['value_1']>0 || (double)$HTTP_POST_VARS['value_2']>0) && (tep_not_null($HTTP_POST_VARS['price_from']) || tep_not_null($HTTP_POST_VARS['price_to'])) ) {
		  $update_products_prices = true;
		}
		if ( (int)$HTTP_POST_VARS['new_categories_id']==0 && (int)$HTTP_POST_VARS['new_manufacturers_id']==0 && (int)$HTTP_POST_VARS['new_series_id']==0 && (int)$HTTP_POST_VARS['new_products_types_id']==0 ) {
		} else {
		  $update_products_params = true;
		}
		if ( (int)$HTTP_POST_VARS['categories_id'] == 0 && (int)$HTTP_POST_VARS['manufacturers_id'] == 0 && (int)$HTTP_POST_VARS['series_id'] == 0 && (int)$HTTP_POST_VARS['products_types_id'] == 0 ) {
		  $messageStack->add(ERROR_NO_PARAMETERS_SELECTED, 'error');
		} elseif ($update_products_prices==false && $update_products_params==false) {
		  $messageStack->add(ERROR_NO_VALUES_SELECTED, 'error');
		} else {
		  $sql = "select distinct p.products_id" . ($update_products_prices ? ", p." . tep_db_input($HTTP_POST_VARS['price_from']) . " as products_price" : '') . " from " . TABLE_PRODUCTS . " p";
		  if (tep_not_null($HTTP_POST_VARS['categories_id'])) {
			$sql .= ", " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ";
		  }
		  $sql .= " where";
		  if (tep_not_null($HTTP_POST_VARS['categories_id'])) {
			$subcategories = array($HTTP_POST_VARS['categories_id']);
			if ($HTTP_POST_VARS['inc_sub']) {
			  tep_get_subcategories($subcategories, $HTTP_POST_VARS['categories_id']);
			}
			$subcategories = array_map("tep_string_to_int", $subcategories);
			$sql .= " p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("', '", $subcategories) . "')";
		  }
		  if (tep_not_null($HTTP_POST_VARS['manufacturers_id'])) {
			$sql .= " and p.manufacturers_id = '" . (int)$HTTP_POST_VARS['manufacturers_id'] . "'";
		  }
		  if (tep_not_null($HTTP_POST_VARS['series_id'])) {
			$sql .= " and p.series_id = '" . (int)$HTTP_POST_VARS['series_id'] . "' ";
		  }
		  if (tep_not_null($HTTP_POST_VARS['products_types_id'])) {
			$sql .= " and p.products_types_id = '" . (int)$HTTP_POST_VARS['products_types_id'] . "' ";
		  }
		  $updated = 0;
		  $products_query = tep_db_query($sql);
		  while ($products = tep_db_fetch_array($products_query)) {
			$update_product_result = false;
			if ($update_products_prices) {
			  $end_value = $products['products_price'];
			  $value_1 = (float)str_replace(',', '.', $HTTP_POST_VARS['value_1']);
			  $value_2 = (float)str_replace(',', '.', $HTTP_POST_VARS['value_2']);
			  if ($value_1 > 0) {
				if ($HTTP_POST_VARS['currency_1'] == '%') $value = $end_value * $value_1/100;
				else $value = $value_1;
				if ($HTTP_POST_VARS['sign_1'] == '+') $value = $end_value + $value;
				else $value = $end_value - $value;
				$end_value = $value;
			  }
			  if ($value_2 > 0) {
				if ($HTTP_POST_VARS['currency_2'] == '%') $value = $end_value * $value_2/100;
				else $value = $value_2;
				if ($HTTP_POST_VARS['sign_2'] == '+') $value = $end_value + $value;
				else $value = $end_value - $value;
			  }
			  if ($products['products_price']!=$value) {
				$query = "update " . TABLE_PRODUCTS . " set " . tep_db_input($HTTP_POST_VARS['price_to']) . " = '" . tep_db_input($value) . "' where products_id = '" . (int)$products['products_id'] . "'";
				tep_db_query($query);
				$update_product_result = true;
			  }
			}
			if ($update_products_params) {
			  if (tep_not_null($HTTP_POST_VARS['new_categories_id'])) {
				tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products['products_id'] . "'");
				tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products['products_id'] . "', '" . (int)$HTTP_POST_VARS['new_categories_id'] . "')");
				$update_product_result = true;
			  }
			  $query = '';
			  if (tep_not_null($HTTP_POST_VARS['new_manufacturers_id'])) {
				$query .= (tep_not_null($query) ? ', ' : '') . "manufacturers_id = '" . (int)$HTTP_POST_VARS['new_manufacturers_id'] . "'";
			  }
			  if (tep_not_null($HTTP_POST_VARS['new_series_id'])) {
				$query .= (tep_not_null($query) ? ', ' : '') . "p.series_id = '" . (int)$HTTP_POST_VARS['new_series_id'] . "'";
			  }
			  if (tep_not_null($HTTP_POST_VARS['new_products_types_id'])) {
				$query .= (tep_not_null($query) ? ', ' : '') . "p.products_types_id = '" . (int)$HTTP_POST_VARS['new_products_types_id'] . "'";
			  }
			  if (tep_not_null($query)) {
				$query = "update " . TABLE_PRODUCTS . " set " . $query . " where products_id = '" . (int)$products['products_id'] . "'";
				tep_db_query($query);
				$update_product_result = true;
			  }
			}
			if ($update_product_result) $updated ++;
		  }
		  $messageStack->add_session(sprintf(SUCCESS_PRODUCTS_UPDATED, $updated), 'success');
		  tep_redirect(FILENAME_PRODUCTS_UPDATES);
		}
		break;
    }
  }

  $categories_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $categories_array = array_merge($categories_array, tep_get_category_tree(0, '', 0));

  $manufacturers_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' order by manufacturers_name");
  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
	$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
								   'text' => $manufacturers['manufacturers_name']);
  }

  $products_types_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $products_types_query = tep_db_query("select products_types_id, products_types_name from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "' order by sort_order, products_types_name");
  while ($products_types = tep_db_fetch_array($products_types_query)) {
	$products_types_array[] = array('id' => $products_types['products_types_id'],
									'text' => $products_types['products_types_name']);
  }

  $series_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $series_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where language_id = '" . (int)$languages_id . "' order by sort_order, series_name");
  while ($series = tep_db_fetch_array($series_query)) {
	$series_array[] = array('id' => $series['series_id'],
							'text' => $series['series_name']);
  }

  $prices_array = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $prices_array[] = array('id' => 'products_price', 'text' => TEXT_PRODUCTS_PRICE);
  $prices_array[] = array('id' => 'products_cost', 'text' => TEXT_PRODUCTS_COST);

  $signs_array = array();
  $signs_array[] = array('id' => '+', 'text' => '&nbsp;+&nbsp;');
  $signs_array[] = array('id' => '-', 'text' => '&nbsp;-&nbsp;');

  $currencies_array = array();
  $currencies_array[] = array('id' => '%', 'text' => '&nbsp;%');
  $currencies_array[] = array('id' => DEFAULT_CURRENCY, 'text' => $currencies->currencies[DEFAULT_CURRENCY]['title']);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
function updateIt() {
  var initialValue = 200;
  var endValue;
  var str_1 = '';
  var str_2 = '';
  var val = 0;
  var f = document.update_prices;
  endValue = initialValue;
  val_1 = Number(f.value_1.value);
  if (val_1 > 0) {
	if (f.currency_1.options[f.currency_1.selectedIndex].value == '%') {
	  val = endValue*val_1/100;
	  str_1 += String(val_1) + '% ';
	} else {
	  val = val_1;
	  str_1 += String(val_1) + 'USD ';
	}
	if (f.sign_1.options[f.sign_1.selectedIndex].value == '+') {
	  val = endValue + val;
	  str_1 = '+ ' + str_1;
	} else {
	  val = endValue - val;
	  str_1 = '- ' + str_1;
	}
	endValue = val;
  }
  val_2 = Number(f.value_2.value);
  if (val_2 > 0) {
	if (f.currency_2.options[f.currency_2.selectedIndex].value == '%') {
	  val = endValue*val_2/100;
	  str_2 += String(val_2) + '% ';
	} else {
	  val = val_2;
	  str_2 += String(val_2) + 'USD ';
	}
	if (f.sign_2.options[f.sign_2.selectedIndex].value == '+') {
	  val = endValue + val;
	  str_2 = '+ ' + str_2;
	} else {
	  val = endValue - val;
	  str_2 = '- ' + str_2;
	}
  }
  f.total.value = initialValue + ' ' + str_1 + str_2 + '= ' + val; 
//  alert(val);
}
//--></script>
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
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
			<?php echo tep_draw_form('update_prices', FILENAME_PRODUCTS_UPDATES, 'action=update_prices', 'post', 'onsubmit="return confirm(\'' . TEXT_APPLY_CHANGES . '\');"'); ?>
			<table border="0" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TITLE; ?></td>
              </tr>
			  <tr class="dataTableRow">
                <td valign="top" class="dataTableContent"><?php echo TEXT_SELECT_CATEGORY; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('categories_id', $categories_array) . '<br /><small>' . tep_draw_checkbox_field('inc_sub', '1') . TEXT_INCLUDE_SUBCATEGORIES . '</small>'; ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_SELECT_MANUFACTURER; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_SELECT_SERIE; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('series_id', $series_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_SELECT_TYPE; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('products_types_id', $products_types_array); ?></td>
			  </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TITLE_1; ?></td>
              </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_SELECT_PRICE_FROM; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('price_from', $prices_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_SELECT_PRICE_TO; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('price_to', $prices_array, 'products_price'); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_VALUE_1; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('sign_1', $signs_array, '', 'onchange="updateIt();"') . ' ' . tep_draw_input_field('value_1', '', 'size="4" onkeyup="updateIt();"') . ' ' . tep_draw_pull_down_menu('currency_1', $currencies_array, '', 'onchange="updateIt();"'); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_VALUE_2; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('sign_2', $signs_array, '', 'onchange="updateIt();"') . ' ' . tep_draw_input_field('value_2', '', 'size="4" onkeyup="updateIt();"') . ' ' . tep_draw_pull_down_menu('currency_2', $currencies_array, '', 'onchange="updateIt();"'); ?></td>
			  </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_TITLE_2; ?></td>
              </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_MOVE_TO_CATEGORY; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('new_categories_id', $categories_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_CHANGE_MANUFACTURER; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('new_manufacturers_id', $manufacturers_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_CHANGE_SERIE; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('new_series_id', $series_array); ?></td>
			  </tr>
			  <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo TEXT_CHANGE_TYPE; ?></td>
                <td class="dataTableContent"><?php echo tep_draw_pull_down_menu('new_products_types_id', $products_types_array); ?></td>
			  </tr>
			  <tr>
				<td colspan="2"><?php echo tep_draw_separator(); ?></td>
			  </tr>
              <tr>
				<td><?php echo tep_draw_input_field('total', '', 'size="25" readonly="readonly" style="border: 0px;"'); ?></td>
                <td class="smallText"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
              </tr>
			  <tr>
				<td colspan="2"><?php echo tep_draw_separator(); ?></td>
			  </tr>
			</table></form></td>
          </tr>
        </table></td>
      </tr>
    </table>
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