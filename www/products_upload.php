<?php
  require('includes/application_top.php');

  $categories_audio_top_id = 1104;

  include(DIR_WS_CLASSES . 'order.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $languages_array = array('en' => 1,
//						   'ru' => 2,
//						   'de' => 3,
//						   'fr' => 4,
//						   'es' => 5,
						   );

  if ($HTTP_POST_VARS['update_images']=='1' || $HTTP_POST_VARS['update_other_images']=='1') {
	$HTTP_GET_VARS['update_images'] = '1';
  }

  if (isset($argv)) {
	reset($argv);
	while (list($i, $arg) = each($argv)) {
	  if ($i > 0) {
		list($arg_key, $arg_value) = explode('=', $arg);
		$HTTP_GET_VARS[$arg_key] = $arg_value;
	  }
	}
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $temp_tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_INFO, TABLE_SPECIALS);

  if ($action=='small_upload') {
	if (tep_db_table_exists(DB_DATABASE, TABLE_TEMP_PRODUCTS)) die('FAIL');
	$upload_csv_dir = 'SHOTCSV/';
  } else {
	$upload_csv_dir = 'CSV/';
  }

  if (tep_not_null($action)) {
	tep_set_time_limit(600);

	if ($action=='small_upload_check') {
	  if (tep_db_table_exists(DB_DATABASE, TABLE_TEMP_PRODUCTS)) die('FAIL');
	  else die('FREE');
	}

	if ($action=='get_last_upload_date') {
	  $config_check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_DATE'");
	  if (tep_db_num_rows($config_check_query) > 0) {
		$config_check = tep_db_fetch_array($config_check_query);
		echo $config_check['configuration_value'] . "\r\n";
	  } else {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('Дата последнего обновления', 'CONFIGURATION_LAST_UPDATE_DATE', '0', '6', now())");
		echo "0\r\n";
	  }
	  $config_check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_DATE_OTHER'");
	  if (tep_db_num_rows($config_check_query) > 0) {
		$config_check = tep_db_fetch_array($config_check_query);
		echo $config_check['configuration_value'];
	  } else {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_group_id, date_added) values ('Дата последнего обновления', 'CONFIGURATION_LAST_UPDATE_DATE_OTHER', '0', '6', now())");
		echo '0';
	  }
	  die;
	}

	if ($action=='check_file') {
	  if (file_exists(UPLOAD_DIR . urldecode($HTTP_GET_VARS['file']))) {
		die('1');
	  } else {
		die('0');
	  }
	}

	if ($action=='upload_categories' || $action=='small_upload') {
	  $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Rubrikator.csv.gz';
	  $file_to_include = DIR_WS_INCLUDES . 'upload/categories.php';
	  if (file_exists($filename_gz)) {
		if (isset($argv)) {
		  $filename = str_replace('.gz', '', $filename_gz);
		  if (strpos($filename_gz, '.gz')!==false) {
			$gz = gzopen($filename_gz, 'r') or die('Cann\'t open ' . $filename_gz . ' for reading!');
			$ff = fopen($filename, 'w') or die('Cann\'t open ' . $filename . ' for writing!');
			while (!gzeof($gz)) {
			  $string = gzgets($gz, 1024);
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  }

		  ob_start();
		  include($file_to_include);
		  $output = ob_get_clean();
		  $output = mb_convert_encoding($output, 'UTF-8', 'CP1251');
		  unlink($filename);
		  if (strpos($action, 'small')===false) die($output);
		} else {
		  system('php ' . $dir_fs_catalog . DIR_WS_ADMIN_PART . FILENAME_PRODUCTS_UPLOAD . ' action=' . $action . ' > /dev/null &');
		  sleep(1);
		  tep_redirect(tep_href_link(FILENAME_PRODUCTS_UPLOAD));
		}
	  } else {
		$error = sprintf(ERROR_NO_FILE_UPLOAD, $filename_gz);
		if (isset($argv)) {
		  $error = mb_convert_encoding($error, 'UTF-8', 'CP1251');
		  die($error);
		} else {
		  $messageStack->add($error, 'error');
		}
	  }
	}

	if ($action=='upload_manufacturers' || $action=='small_upload') {
	  $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Publishers.csv.gz';
	  $file_to_include = DIR_WS_INCLUDES . 'upload/manufacturers.php';
	  if (file_exists($filename_gz)) {
		if (isset($argv)) {
		  $filename = str_replace('.gz', '', $filename_gz);
		  if (strpos($filename_gz, '.gz')!==false) {
			$gz = gzopen($filename_gz, 'r') or die('Cann\'t open ' . $filename_gz . ' for reading!');
			$ff = fopen($filename, 'w') or die('Cann\'t open ' . $filename . ' for writing!');
			while (!gzeof($gz)) {
			  $string = gzgets($gz, 1024);
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  }

		  ob_start();
		  include($file_to_include);
		  $output = ob_get_clean();
		  $output = mb_convert_encoding($output, 'UTF-8', 'CP1251');
		  unlink($filename);
		  if (strpos($action, 'small')===false) die($output);
		} else {
		  system('php ' . $dir_fs_catalog . DIR_WS_ADMIN_PART . FILENAME_PRODUCTS_UPLOAD . ' action=' . $action . ' > /dev/null &');
		  sleep(1);
		  tep_redirect(tep_href_link(FILENAME_PRODUCTS_UPLOAD));
		}
	  } else {
		$error = sprintf(ERROR_NO_FILE_UPLOAD, $filename_gz);
		if (isset($argv)) {
		  $error = mb_convert_encoding($error, 'UTF-8', 'CP1251');
		  die($error);
		} else {
		  $messageStack->add($error, 'error');
		}
	  }
	}

	if ($action=='upload_series' || $action=='small_upload') {
	  $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Seria.csv.gz';
	  $file_to_include = DIR_WS_INCLUDES . 'upload/series.php';
	  if (file_exists($filename_gz)) {
		if (isset($argv)) {
		  $filename = str_replace('.gz', '', $filename_gz);
		  if (strpos($filename_gz, '.gz')!==false) {
			$gz = gzopen($filename_gz, 'r') or die('Cann\'t open ' . $filename_gz . ' for reading!');
			$ff = fopen($filename, 'w') or die('Cann\'t open ' . $filename . ' for writing!');
			while (!gzeof($gz)) {
			  $string = gzgets($gz, 1024);
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  }

		  ob_start();
		  include($file_to_include);
		  $output = ob_get_clean();
		  $output = mb_convert_encoding($output, 'UTF-8', 'CP1251');
		  unlink($filename);
		  if (strpos($action, 'small')===false) die($output);
		} else {
		  system('php ' . $dir_fs_catalog . DIR_WS_ADMIN_PART . FILENAME_PRODUCTS_UPLOAD . ' action=' . $action . ' > /dev/null &');
		  sleep(1);
		  tep_redirect(tep_href_link(FILENAME_PRODUCTS_UPLOAD));
		}
	  } else {
		$error = sprintf(ERROR_NO_FILE_UPLOAD, $filename_gz);
		if (isset($argv)) {
		  $error = mb_convert_encoding($error, 'UTF-8', 'CP1251');
		  die($error);
		} else {
		  $messageStack->add($error, 'error');
		}
	  }
	}

	if ($action=='upload_authors' || $action=='small_upload') {
	  $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Autors.csv.gz';
	  $file_to_include = DIR_WS_INCLUDES . 'upload/authors.php';
	  if (file_exists($filename_gz)) {
		if (isset($argv)) {
		  $filename = str_replace('.gz', '', $filename_gz);
		  if (strpos($filename_gz, '.gz')!==false) {
			$gz = gzopen($filename_gz, 'r') or die('Cann\'t open ' . $filename_gz . ' for reading!');
			$ff = fopen($filename, 'w') or die('Cann\'t open ' . $filename . ' for writing!');
			while (!gzeof($gz)) {
			  $string = gzgets($gz, 1024);
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  }

		  ob_start();
		  include($file_to_include);
		  $output = ob_get_clean();
		  $output = mb_convert_encoding($output, 'UTF-8', 'CP1251');
		  unlink($filename);
		  if (strpos($action, 'small')===false) die($output);
		} else {
		  system('php ' . $dir_fs_catalog . DIR_WS_ADMIN_PART . FILENAME_PRODUCTS_UPLOAD . ' action=' . $action . ' > /dev/null &');
		  sleep(1);
		  tep_redirect(tep_href_link(FILENAME_PRODUCTS_UPLOAD));
		}
	  } else {
		$error = sprintf(ERROR_NO_FILE_UPLOAD, $filename_gz);
		if (isset($argv)) {
		  $error = mb_convert_encoding($error, 'UTF-8', 'CP1251');
		  die($error);
		} else {
		  $messageStack->add($error, 'error');
		}
	  }
	}

	if ($action=='upload_products' || $action=='upload_products_new' || $action=='upload_other_products' || $action=='small_upload') {
	  $categories_audio = array();
	  tep_get_subcategories($categories_audio, $categories_audio_top_id);

	  $in_shops = array();

	  if ($action=='upload_other_products') $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Tovar.csv.gz';
	  elseif ($action=='upload_products_new') $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Books1.csv.gz';
	  else $filename_gz = UPLOAD_DIR . $upload_csv_dir . 'Books.csv.gz';
	  if ($action=='upload_products_new') $file_to_include = DIR_WS_INCLUDES . 'upload/products_1.php';
	  else $file_to_include = DIR_WS_INCLUDES . 'upload/products.php';
	  if (tep_db_table_exists(DB_DATABASE, TABLE_TEMP_PRODUCTS)) {
		$messageStack->add(WARNING_UPDATE_IN_PROGRESS, 'warning');
	  } elseif (file_exists($filename_gz)) {
		if (isset($argv)) {
		  $filename = str_replace('.gz', '', $filename_gz);
		  if (strpos($filename_gz, '.gz')!==false) {
			$gz = gzopen($filename_gz, 'r') or die('Cann\'t open ' . $filename_gz . ' for reading!');
			$ff = fopen($filename, 'w') or die('Cann\'t open ' . $filename . ' for writing!');
			while (!gzeof($gz)) {
			  $string = gzgets($gz, 1024);
			  fwrite($ff, $string);
			}
			fclose($ff);
			gzclose($gz);
		  }

		  ob_start();
		  include($file_to_include);
		  $output = ob_get_clean();
		  $output = mb_convert_encoding($output, 'UTF-8', 'CP1251');
		  unlink($filename);
		  if (strpos($action, 'small')===false) die($output);
		} else {
		  system('php ' . $dir_fs_catalog . DIR_WS_ADMIN_PART . FILENAME_PRODUCTS_UPLOAD . ' action=' . $action . ($update_images=='1' ? ' update_images=' . $update_images : '') . ' > /dev/null &');
		  sleep(5);
		  tep_redirect(tep_href_link(FILENAME_PRODUCTS_UPLOAD));
		}
	  } else {
		$error = sprintf(ERROR_NO_FILE_UPLOAD, $filename_gz);
		if (isset($argv)) {
		  $error = mb_convert_encoding($error, 'UTF-8', 'CP1251');
		  die($error);
		} else {
		  $messageStack->add($error, 'error');
		}
	  }
	}
  }

  $upload_in_process = true;
  if (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Rubrikator.csv')) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_CATEGORIES;
  } elseif (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Publishers.csv')) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_MANUFACTURERS;
  } elseif (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Seria.csv')) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_SERIES;
  } elseif (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Autors.csv')) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_AUTHORS;
  } elseif (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Tovar.csv') && tep_db_table_exists(DB_DATABASE, TABLE_TEMP_PRODUCTS)) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_OTHER_PRODUCTS;
  } elseif (file_exists($upload_file = UPLOAD_DIR . $upload_csv_dir . 'Books.csv') && tep_db_table_exists(DB_DATABASE, TABLE_TEMP_PRODUCTS)) {
	$upload_check_file = $upload_file;
	$upload_text = TEXT_UPLOAD_PRODUCTS;
  } else {
	$upload_in_process = false;
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
    <td width="100%" valign="top">
<?php
  if (tep_session_is_registered('report_string')) {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE_REPORT; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo $report_string; ?></td>
      </tr>
	</table>
<?php
	tep_session_unregister('report_string');
  } else {
?>
	<script language="javascript" type="text/javascript"><!--
	  function getFieldValue() {
		var val = '';
		for (i=0; i<document.upload.type.length; i++) {
		  if (document.upload.type[i].checked) {
			return document.upload.type[i].value;
		  }
		}
		return val;
	  }
	//--></script>
    <table border="0" cellspacing="0" cellpadding="2" width="100%">
	<?php echo tep_draw_form('upload', basename($PHP_SELF), tep_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data" onsubmit="this.action+=(this.action.indexOf(\'?\')>=0 ? \'&\' : \'?\')+\'action=upload_\'+getFieldValue();"'); ?>
      <tr>
        <td colspan="2" class="pageHeading"><?php echo HEADING_TITLE_CATALOG; ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
	</table>
<?php
	if ($upload_in_process) {
?>
	<div id="checkUpdateDiv" style="display: none;">1</div>
<script language="javascript" type="text/javascript"><!--
  function checkUpdate() {
	setTimeout(function() {
	top: {
	  getXMLDOM("<?php echo tep_href_link(FILENAME_PRODUCTS_UPLOAD, 'action=check_file&file=' . urlencode(str_replace(UPLOAD_DIR, '', $upload_check_file))); ?>", "checkUpdateDiv");
//	  alert(document.getElementById("checkUpdateDiv").innerHTML);
	  if (document.getElementById("checkUpdateDiv").innerHTML=="0") {
		window.location.reload();
		document.getElementById("uploadCheck").style.display = "none";
		document.getElementById("uploadChoice").style.display = "";
		break top;
	  }
	  checkUpdate();
	}
	}, 2000);
  }
  checkUpdate();
//--></script>
	<table border="0" cellspacing="0" cellpadding="2" id="uploadCheck">
	  <tr>
		<td width="297" class="main" align="center" height="141"><?php echo tep_image(DIR_WS_IMAGES . 'loading.gif', $upload_text); ?></td>
		<td class="main"><?php echo sprintf(ERROR_UPLOAD_IN_PROCESS, strtolower($upload_text)); ?></td>
	  </tr>
	</table>
<?php
	}
?>
    <table border="0" cellspacing="0" cellpadding="2" width="100%" id="uploadChoice" style="display: <?php echo ($upload_in_process ? 'none' : 'table'); ?>;">
      <tr valign="top">
		<td width="270" class="main"><br><br><br><br><?php echo TEXT_SELECT_UPLOAD_TYPE; ?></td>
		<td class="main"><?php
	$products_types_last_modified_array = array();
	$products_types_last_modified_query = tep_db_query("select products_types_id, products_last_modified from " . TABLE_PRODUCTS_TYPES . " where language_id = '" . (int)$languages_id . "'");
	while ($products_types_last_modified = tep_db_fetch_array($products_types_last_modified_query)) {
	  $products_types_last_modified_array[$products_types_last_modified['products_types_id']] = tep_datetime_short($products_types_last_modified['products_last_modified']);
	}

	$categories_last_update_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_CATEGORIES_DATE'");
	$categories_last_update = tep_db_fetch_array($categories_last_update_query);

	$manufacturers_last_update_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_MANUFACTURERS_DATE'");
	$manufacturers_last_update = tep_db_fetch_array($manufacturers_last_update_query);

	$series_last_update_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_SERIES_DATE'");
	$series_last_update = tep_db_fetch_array($series_last_update_query);

	$authors_last_update_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'CONFIGURATION_LAST_UPDATE_AUTHORS_DATE'");
	$authors_last_update = tep_db_fetch_array($authors_last_update_query);

	echo '<table border="0" cellspacing="0" cellpadding="2">' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'categories', false, '', 'onclick="document.getElementById(\'updateImages\').style.display = \'none\'; document.getElementById(\'updateOtherImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_CATEGORIES . '</td>' . "\n" . '<td class="main">&nbsp;[' . tep_datetime_short($categories_last_update['configuration_value']) . ']</td>' . "\n" . '</tr>' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'manufacturers', false, '', 'onclick="document.getElementById(\'updateImages\').style.display = \'none\'; document.getElementById(\'updateOtherImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_MANUFACTURERS . '</td>' . "\n" . '<td class="main">&nbsp;[' . tep_datetime_short($manufacturers_last_update['configuration_value']) . ']</td>' . "\n" . '</tr>' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'series', false, '', 'onclick="document.getElementById(\'updateImages\').style.display = \'none\'; document.getElementById(\'updateOtherImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_SERIES . '</td>' . "\n" . '<td class="main">&nbsp;[' . tep_datetime_short($series_last_update['configuration_value']) . ']</td>' . "\n" . '</tr>' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'authors', false, '', 'onclick="document.getElementById(\'updateImages\').style.display = \'none\'; document.getElementById(\'updateOtherImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_AUTHORS . '</td>' . "\n" . '<td class="main">&nbsp;[' . tep_datetime_short($authors_last_update['configuration_value']) . ']</td>' . "\n" . '</tr>' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'products', false, '', 'onclick="if (this.checked) document.getElementById(\'updateImages\').style.display = \'\'; document.getElementById(\'updateOtherImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_PRODUCTS . '<div id="updateImages" style="display: none;">' . tep_draw_checkbox_field('update_images', '1', false) . TEXT_UPDATE_IMAGES . '</div></td>' . "\n" . '<td class="main">&nbsp;[' . $products_types_last_modified_array[1] . ']</td>' . "\n" . '</tr>' . "\n" .
	'<tr valign="top">' . "\n" . '<td class="main">' . tep_draw_radio_field('type', 'other_products', false, '', 'onclick="if (this.checked) document.getElementById(\'updateOtherImages\').style.display = \'\'; document.getElementById(\'updateImages\').style.display = \'none\';"') . '</td>' . "\n" . '<td class="main">' . TEXT_UPLOAD_OTHER_PRODUCTS . '<div id="updateOtherImages" style="display: none;">' . tep_draw_checkbox_field('update_other_images', '1', false) . TEXT_UPDATE_IMAGES . '</div></td>' . "\n" . '<td class="main">&nbsp;[' . $products_types_last_modified_array[2] . ']</td>' . "\n" . '</tr>' . "\n" .
	'</table>'; ?></td>
	  </tr>
      <tr>
        <td colspan="3"><?php echo tep_draw_separator('pixel_trans.gif', '1', '15'); ?></td>
      </tr>
	  <tr>
		<td>&nbsp;</td>
		<td colspan="2"><?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD); ?></td>
    	</form>
	  </tr>
	</table>
<?php
  }
?></td>
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