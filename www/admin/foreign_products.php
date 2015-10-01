<?php
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $page = (isset($HTTP_GET_VARS['page']) ? $HTTP_GET_VARS['page'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'upload_confirm':
		$report_string = '';
		$create_report = false;

		if (!is_dir(DIR_FS_CATALOG_IMAGES . 'foreign/')) mkdir(DIR_FS_CATALOG_IMAGES . 'foreign/', 0777);
		if (!is_dir(DIR_FS_CATALOG_IMAGES . 'foreign/thumbs/')) mkdir(DIR_FS_CATALOG_IMAGES . 'foreign/thumbs/', 0777);
		if (!is_dir(DIR_FS_CATALOG_IMAGES . 'foreign/big/')) mkdir(DIR_FS_CATALOG_IMAGES . 'foreign/big/', 0777);
		if (!is_dir(DIR_FS_CATALOG_IMAGES . 'foreign/big/thumbs/')) mkdir(DIR_FS_CATALOG_IMAGES . 'foreign/big/thumbs/', 0777);

		if (is_uploaded_file($_FILES['products_file']['tmp_name'])) {
		  $updated = 0;
		  $added = 0;
		  $not_added = 0;
		  $total = 0;
		  $all_products = array();

		  $i = 0;
		  $cells = array();
		  $fp = fopen($_FILES['products_file']['tmp_name'], 'r');
		  while (($cell = fgetcsv($fp, 10000, ";")) !== FALSE) {
			$cell[0] = str_replace('–', '-', trim($cell[0]));
			if ($i > 0) {
			  $cells[] = array_merge(array(''), $cell);
			}
			$i ++;
		  }
		  fclose($fp);
//		  echo '<pre>' . print_r($cells, true) . '</pre>';
//		  die();

		  tep_set_time_limit(9600);
		  $cells_count = 0;

		  reset($cells);
		  while (list($i, $cell) = each($cells)) {
			if (tep_not_null($cell[1])) {
			  $products_model = tep_db_prepare_input($cell[1]);
			  $products_model_1 = preg_replace('/[^\d]/', '', $products_model);
			  $products_name = preg_replace('/\s+/', ' ', tep_db_input($cell[2]));
			  $products_name = htmlspecialchars(stripslashes($products_name));
			  $authors_name = tep_db_prepare_input($cell[3]);
			  $manufacturers_name = tep_db_prepare_input($cell[4]);
			  $products_year = tep_db_prepare_input($cell[5]);
			  $products_url = tep_db_prepare_input($cell[6]);
			  $image_big = tep_db_prepare_input($cell[7]);
			  $products_price = str_replace(',', '.', trim($cell[8]));
			  $products_currency = tep_db_prepare_input($cell[9]);
			  $products_description = tep_db_prepare_input($cell[10]);
			  $products_description = stripslashes(strip_tags($products_description, '<b><strong><i><em>'));
			  $products_description = preg_replace('/([^\r\n])[\r\n]+([^\r\n])/', "$1\n\n$2", $products_description);
			  $products_available_in = tep_db_prepare_input($cell[11]);
			  $products_language = mb_strtolower(tep_db_prepare_input($cell[12]), 'CP1251');
			  $products_genre = mb_strtolower(tep_db_prepare_input($cell[13]), 'CP1251');
			  if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $products_year, $regs)) {
				$products_year = $regs[3];
				$products_date_available = $regs[3] . '-' . $regs[2] . '-' . $regs[1];
			  } elseif (preg_match('/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/', $products_year, $regs)) {
				$products_year = $regs[1];
				$products_date_available = $regs[1] . '-' . $regs[2] . '-' . $regs[3];
			  } elseif ((int)$products_year > 0) {
				$products_year = (int)$products_year;
				$products_date_available = 'null';
			  } else {
				$products_year = '';
				$products_date_available = 'null';
			  }

			  $is_added = false;
			  $products_check_query = tep_db_query("select products_id from " . TABLE_FOREIGN_PRODUCTS . " where products_name = '" . tep_db_input($products_name) . "'");
			  $products_check = tep_db_fetch_array($products_check_query);
			  if (!is_array($products_check)) $products_check = array();
			  $products_id = (int)$products_check['products_id'];
			  $sql_data_array = array('products_model' => $products_model,
									  'products_model_1' => $products_model_1,
									  'products_name' => $products_name,
									  'products_author' => $authors_name,
									  'products_description' => $products_description,
									  'products_manufacturer' => $manufacturers_name,
									  'products_year' => $products_year,
									  'products_genre' => $products_genre,
									  'products_language' => $products_language,
									  'products_url' => $products_url,
									  'products_price' => $products_price,
									  'products_currency' => $products_currency,
									  'products_date_available' => $products_date_available,
									  'products_available_in' => $products_available_in,
									  );
			  if ((int)($products_id) < 1) {
				$sql_data_array['products_date_added'] = 'now()';

				tep_db_perform(TABLE_FOREIGN_PRODUCTS, $sql_data_array);
				$products_id = tep_db_insert_id();

				$added ++;
				$is_added = true;
			  } else {
				unset($sql_data_array['products_name']);
				unset($sql_data_array['products_description']);
				$sql_data_array['products_last_modified'] = 'now()';

				tep_db_perform(TABLE_FOREIGN_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

				$updated ++;
			  }

			  $category_name_1 = 'Книги на иностранных языках' . (tep_not_null($products_language) ? ' (' . $products_language . ')' : '');
			  $metatags_page_title = (tep_not_null($authors_name) ? $authors_name . ', ' : '') . 
									 $products_name . (substr($products_name, -1)!='.' ? '.' : '') . 
									 (tep_not_null($category_name_1) ? ' ' . $category_name_1 . '.' : '') . 
									 ' Интернет-магазин Setbook.';
			  $metatags_title = $products_name;
			  $metatags_keywords = (tep_not_null($category_name_1) ? $category_name_1 . '. ' : '') . (tep_not_null($authors_name) ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($products_genre) ? $products_genre . '.' : '');
			  $metatags_description = (tep_not_null($category_name_1) ? $category_name_1 . '. ' : '') . (tep_not_null($authors_name) ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($products_description) ? preg_replace('/^([^\.]+\.).*$/', '$1', $products_description) : '');
			  $content_type = 'foreign';
			  $content_id = $products_id;
			  tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

			  $prev_info_query = tep_db_query("select products_image from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			  $prev_info = tep_db_fetch_array($prev_info_query);
			  $old_width = 0;
			  $old_height = 0;
			  $new_width = 0;
			  $new_height = 0;
			  $replace_image = true;
			  if (tep_not_null($image_big) && $is_added) {
				$new_image_name = 'images/p_' . $products_id . '.jpg';
				copy($image_big, UPLOAD_DIR . $new_image_name);
				list($new_width, $new_height) = getimagesize(UPLOAD_DIR . $new_image_name);
				if (tep_not_null($prev_info['products_image'])) {
				  list($old_width, $old_height) = getimagesize(DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $prev_info['products_image']);
				  if ($old_width > $new_width || $old_height > $new_height) $replace_image = false;
				}

				if ($replace_image) {
				  if (tep_not_null($prev_info['products_image'])) {
					@unlink(DIR_FS_CATALOG_IMAGES . 'foreign/' . $prev_info['products_image']);
					@unlink(DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $prev_info['products_image']);
				  }
				  $new_filename = 'thumbs/' . substr(uniqid(rand()), 0, 10) . '.jpg';

				  tep_create_thumb(UPLOAD_DIR . $new_image_name, DIR_FS_CATALOG_IMAGES . 'foreign/' . $new_filename, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '85', 'reduce_only');
				  tep_create_thumb(UPLOAD_DIR . $new_image_name, DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $new_filename, BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT, '85', 'reduce_only');
				} else {
				  $new_filename = $prev_info['products_image'];
				}
				@unlink(UPLOAD_DIR . $new_image_name);
				tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image = '" . tep_db_input($new_filename) . "' where products_id = '" . (int)$products_id . "'");
			  }

			  $total ++;
			  if (tep_not_null($products_id)) $all_products[] = $products_id;
			}
			$cells_count ++;
		  }

		  tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image_exists = '0', sort_order = '0'");
		  tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image_exists = '1' where products_image <> ''");

		  // сортировка по умолчанию (сначала книги с картинками, потом все остальное)
		  $query = tep_db_query("select products_id, products_image_exists from " . TABLE_FOREIGN_PRODUCTS . " where 1 order by products_image_exists desc, products_year desc, products_name");
		  $s = 1;
		  while ($row = tep_db_fetch_array($query)) {
			tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
			$s ++;
		  }

		  $messageStack->add_session(sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added), 'success');
		  tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action'))));
		} else {
		  $messageStack->add(ERROR_NO_FILE_UPLOAD, 'error');
		  $action = 'upload';
		}
		break;
      case 'delete_product_confirm':
        if (isset($HTTP_POST_VARS['products_id'])) {
          $product_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);

		  $prev_info_query = tep_db_query("select products_image from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		  $prev_info = tep_db_fetch_array($prev_info_query);
		  if (tep_not_null($prev_info['products_image'])) {
			@unlink(DIR_FS_CATALOG_IMAGES . 'foreign/' . $prev_info['products_image']);
			@unlink(DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $prev_info['products_image']);
		  }
		  tep_db_query("delete from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		  tep_db_query("delete from " . TABLE_METATAGS . " where content_type = 'foreign' and content_id = '" . (int)$products_id . "'");
        }

        tep_redirect(tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page));
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($HTTP_GET_VARS['pID'])) $products_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
		$products_description = trim($HTTP_POST_VARS['products_description']);
		$products_description = strip_tags($products_description, '<b><strong><i><em>');
		$products_description = preg_replace('/([^\r\n])[\r\n]+([^\r\n])/', "$1\n\n$2", $products_description);
		$products_date_available = preg_replace('/(\d{2})\.(\d{2})\.(\d{4})/', '$3-$2-$1', $HTTP_POST_VARS['products_date_available']);
		$products_genre = (tep_not_null($HTTP_POST_VARS['new_products_genre']) ? $HTTP_POST_VARS['new_products_genre'] : $HTTP_POST_VARS['products_genre']);
		$products_genre = strtolower(trim($products_genre));
		$products_language = (tep_not_null($HTTP_POST_VARS['new_products_language']) ? $HTTP_POST_VARS['new_products_language'] : $HTTP_POST_VARS['products_language']);
		$products_language = strtolower(trim($products_language));
		$products_model = tep_db_prepare_input($HTTP_POST_VARS['products_model']);
		$products_model_1 = preg_replace('/[^\d]/', '', $products_model);
		$sql_data_array = array('products_model' => $products_model,
								'products_model_1' => $products_model_1,
								'products_name' => tep_db_prepare_input($HTTP_POST_VARS['products_name']),
								'products_author' => tep_db_prepare_input($HTTP_POST_VARS['products_author']),
								'products_description' => $products_description,
								'products_manufacturer' => tep_db_prepare_input($HTTP_POST_VARS['products_manufacturer']),
								'products_year' => tep_db_prepare_input($HTTP_POST_VARS['products_year']),
								'products_genre' => tep_db_prepare_input($products_genre),
								'products_language' => tep_db_prepare_input($products_language),
								'products_url' => tep_db_prepare_input($HTTP_POST_VARS['products_url']),
								'products_price' => tep_db_prepare_input(str_replace(',', '.', $HTTP_POST_VARS['products_price'])),
								'products_currency' => tep_db_prepare_input($HTTP_POST_VARS['products_currency']),
								'products_date_available' => $products_date_available,
								'products_available_in' => tep_db_prepare_input($HTTP_POST_VARS['products_available_in']),
								'products_currency' => tep_db_prepare_input($HTTP_POST_VARS['products_currency']),);

		if ($action == 'insert_product') {
		  $insert_sql_data = array('products_date_added' => 'now()');

		  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

		  tep_db_perform(TABLE_FOREIGN_PRODUCTS, $sql_data_array);
		  $products_id = tep_db_insert_id();
		} elseif ($action == 'update_product') {
		  $update_sql_data = array('products_last_modified' => 'now()');

		  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

		  tep_db_perform(TABLE_FOREIGN_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
		}

		$category_name_1 = 'Книги на иностранных языках' . (tep_not_null($products_language) ? ' (' . $products_language . ')' : '');
		$metatags_page_title = (tep_not_null($authors_name) ? $authors_name . ', ' : '') . 
							   $products_name . (substr($products_name, -1)!='.' ? '.' : '') . 
							   (tep_not_null($category_name_1) ? ' ' . $category_name_1 . '.' : '') . 
							   ' Интернет-магазин Setbook.';
		$metatags_title = $products_name;
		$metatags_keywords = (tep_not_null($category_name_1) ? $category_name_1 . '. ' : '') . (tep_not_null($authors_name) ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($products_genre) ? $products_genre . '.' : '');
		$metatags_description = (tep_not_null($category_name_1) ? $category_name_1 . '. ' : '') . (tep_not_null($authors_name) ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($products_description) ? preg_replace('/^([^\.]+\.).*$/', '$1', $products_description) : '');
		$content_type = 'foreign';
		$content_id = $products_id;
		tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

		if ($upload = new upload('', '', '777', array('gif', 'jpeg', 'jpg', 'png'))) {
		  $new_filename = 'thumbs/' . substr(uniqid(rand()), 0, 10) . '.jpg';
		  $is_copied = false;
		  $products_image_url = tep_db_prepare_input($HTTP_POST_VARS['products_image_url']);
		  if (is_uploaded_file($products_image)) {
			$upload->filename = $new_filename;
			if ($upload->upload('products_image', DIR_FS_CATALOG_IMAGES . 'foreign/')) $is_copied = true;
		  } elseif (tep_not_null($products_image_url)) {
		 	if (copy($products_image_url, DIR_FS_CATALOG_IMAGES . 'foreign/' . $new_filename)) $is_copied = true;
		  }
		  if ($is_copied) {
			$prev_file_query = tep_db_query("select products_image from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			$prev_file = tep_db_fetch_array($prev_file_query);
			if (tep_not_null($prev_file['products_image'])) {
			  @unlink(DIR_FS_CATALOG_IMAGES . 'foreign/' . $prev_file['products_image']);
			  @unlink(DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $prev_file['products_image']);
			}
			tep_create_thumb(DIR_FS_CATALOG_IMAGES . 'foreign/' . $new_filename, DIR_FS_CATALOG_IMAGES . 'foreign/big/' . $new_filename, BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT, '85', 'reduce_only');
			tep_create_thumb(DIR_FS_CATALOG_IMAGES . 'foreign/' . $new_filename, '', SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '85', 'reduce_only');
			tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image = '" . tep_db_input($new_filename) . "' where products_id = '" . (int)$products_id . "'");
		  }
		}

		tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image_exists = '0', sort_order = '0'");
		tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set products_image_exists = '1' where products_image <> ''");

		// сортировка по умолчанию (сначала книги с картинками, потом все остальное)
		$query = tep_db_query("select products_id, products_image_exists from " . TABLE_FOREIGN_PRODUCTS . " where 1 order by products_image_exists desc, products_year desc, products_name");
		$s = 1;
		while ($row = tep_db_fetch_array($query)) {
		  tep_db_query("update " . TABLE_FOREIGN_PRODUCTS . " set sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
		  $s ++;
		}

		tep_redirect(tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $products_id));
        break;
    }
  }

// check if the catalog image directory exists
  if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!is_writeable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  $currencies_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  reset($currencies->currencies);
  while (list($code, $currency_row) = each($currencies->currencies)) {
	$currencies_array[] = array('id' => $code, 'text' => $currency_row['title']);
  }

  $genres_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $genres_query = tep_db_query("select distinct products_genre from " . TABLE_FOREIGN_PRODUCTS . " where products_genre <> '' order by products_genre");
  while ($genres_row = tep_db_fetch_array($genres_query)) {
	$genres_array[] = array('id' => $genres_row['products_genre'], 'text' => $genres_row['products_genre']);
  }

  $languages_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $languages_query = tep_db_query("select distinct products_language from " . TABLE_FOREIGN_PRODUCTS . " where products_language <> '' order by products_language");
  while ($languages_row = tep_db_fetch_array($languages_query)) {
	$languages_array[] = array('id' => $languages_row['products_language'], 'text' => $languages_row['products_language']);
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
  if ($action == 'new_product' || $action == 'edit_product') {
    $parameters = array('products_name' => '',
                       'products_description' => '',
                       'products_url' => '',
                       'products_id' => '',
                       'products_model' => '',
                       'products_image' => '',
                       'products_price' => '',
                       'products_currency' => '',
					   'sort_order' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
					   'products_path' => '',
					   'products_year' => '',
                       'products_genre' => '',
                       'products_language' => '',
                       'products_manufacturer' => '',
                       'products_author' => '');

    $pInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['pID']) && empty($HTTP_POST_VARS)) {
      $product_query = tep_db_query("select * from " . TABLE_FOREIGN_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
      $product = tep_db_fetch_array($product_query);

      $pInfo->objectInfo($product);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $pInfo->objectInfo($HTTP_POST_VARS);
    }

	$form_action = (isset($HTTP_GET_VARS['pID'])) ? 'update_product' : 'insert_product';
	echo tep_draw_form('new_product', FILENAME_FOREIGN_PRODUCTS, tep_get_all_get_params(array('pID', 'action')). 'action=' . $form_action . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : ''), 'post', 'enctype="multipart/form-data"');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo $form_action=='update_product' ? sprintf(TEXT_EDIT_PRODUCT, $pInfo->products_name) : TEXT_NEW_PRODUCT; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="1" width="98%">
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_NAME; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_name', $pInfo->products_name, 'size="40"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_PRODUCTS_DESCRIPTION; ?></td>
            <td class="main"><?php
	$field_value = $pInfo->products_description;
	$field_value = str_replace('\\\"', '"', $field_value);
	$field_value = str_replace('\"', '"', $field_value);
	$field_value = str_replace("\\\'", "\'", $field_value);
	$field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	$editor = new editor('products_description');
	$editor->Value = $field_value;
	$editor->Height = '280';
	$editor->Create();?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="272"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_model', $pInfo->products_model, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_manufacturer', $pInfo->products_manufacturer, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_AUTHOR; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_author', $pInfo->products_author, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_price', (string)(float)$pInfo->products_price, 'size="4"') . ' ' . tep_draw_pull_down_menu('products_currency', $currencies_array, $pInfo->products_currency); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_YEAR; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_year', $pInfo->products_year, 'size="4"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_GENRE; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('products_genre', $genres_array, $pInfo->products_genre) . ' <br>' . TEXT_IF_NOT_LISTED . ' <br>' . tep_draw_input_field('new_products_genre', '', 'size="24"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_LANGUAGE; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('products_language', $languages_array, $pInfo->products_language) . ' <br>' . TEXT_IF_NOT_LISTED . ' <br>' . tep_draw_input_field('new_products_language', '', 'size="24"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var dateAdded = new ctlSpiffyCalendarBox("dateAdded", "new_product", "products_date_available", "btnDate1", "<?php echo tep_date_short($pInfo->products_date_available); ?>", scBTNMODE_CUSTOMBLUE);
</script>
          <tr>
            <td class="main" width="250"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?></td>
            <td class="main"><script language="javascript">dateAdded.writeControl(); dateAdded.dateFormat="dd.MM.yyyy";</script></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_AVAILABLE_IN; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_available_in', $pInfo->products_available_in, 'size="4"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250" style="width: 250px;"><?php echo TEXT_PRODUCTS_URL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('products_url', $pInfo->products_url, 'size="90%"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
		  <tr>
			<td class="main"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
            <td class="smallText"><?php echo tep_draw_file_field('products_image') . ((tep_not_null($pInfo->products_image) && file_exists(DIR_FS_CATALOG_IMAGES . 'foreign/' . $pInfo->products_image)) ? '<u style="cursor: pointer;" onMouseOver="document.getElementById(\'small_image\').style.display = \'\';" onMouseOut="document.getElementById(\'small_image\').style.display = \'none\';">' . $pInfo->products_image . '</u> <span id="small_image" style="display: none; position: absolute;">' . tep_image(DIR_WS_CATALOG_IMAGES .'foreign/' . $pInfo->products_image, '') . '</span>' : '') . '<br>' . TEXT_PRODUCTS_IMAGE_URL . '<br>' . tep_draw_input_field('products_image_url', '', 'size="90%"'); ?></td>
		  </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
	if (isset($HTTP_GET_VARS['pID'])) {
	  echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
	} else {
	  echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
	}
	echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right" class="smallText"><?php
    echo tep_draw_form('search', FILENAME_FOREIGN_PRODUCTS, '', 'get');
    echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search');
	reset($HTTP_GET_VARS);
	while (list($k, $v) = each($HTTP_GET_VARS)) {
	  if (!in_array($k, array('search')) && tep_not_null($v)) echo tep_draw_hidden_field($k, $v);
	}
    echo '</form>';
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr valign="top">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
    $rows = 0;
    $products_count = 0;
    if (isset($HTTP_GET_VARS['search'])) {
      $products_query_raw = "select * from " . TABLE_FOREIGN_PRODUCTS . " where ((products_name like '%" . str_replace(' ', "%' and products_name like '%", tep_db_input($search)) . "%') or (products_model like '%" . str_replace(' ', "%' and products_model like '%", tep_db_input($search)) . "%') or (products_description like '%" . str_replace(' ', "%' and products_description like '%", tep_db_input($search)) . "%') products_id = '" . (int)$search . "') order by products_name";
    } else {
      $products_query_raw = "select * from " . TABLE_FOREIGN_PRODUCTS . " where 1 order by products_name";
    }
	$products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
	$products_query = tep_db_query($products_query_raw);
    while ($products = tep_db_fetch_array($products_query)) {
      $products_count++;
      $rows++;

	  if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo) && (substr($action, 0, 3) != 'new')) {
        $pInfo = new objectInfo($products);
      }

	  if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this) onclick="document.location.href=\'' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $products['products_id'] . '&action=edit_product') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $products['products_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $products['products_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products['products_model']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $currencies->format($products['products_price'], false, $products['products_currency']); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, '&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
			  </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'action', 'pID'))); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
	if (!isset($HTTP_GET_VARS['search']) && empty($action)) {
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" class="smallText"><?php
	  echo '<a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&action=upload') . '">' . tep_image_button('button_upload.gif', IMAGE_UPLOAD) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&action=new_product') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>';
?></td>
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
    switch ($action) {
      case 'delete_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&action=delete_product_confirm') . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br><strong>' . $pInfo->products_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'upload':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&action=upload_confirm', 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_UPLOAD_PRODUCTS_INTRO);

        $contents[] = array('text' => '<br>' . tep_draw_file_field('products_file'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_upload.gif', IMAGE_UPLOAD) . ' <a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if ($rows > 0) {
          if (isset($pInfo) && is_object($pInfo)) { // product info box contents
            $heading[] = array('text' => '<strong>' . $pInfo->products_name . '</strong>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $pInfo->products_id . '&action=edit_product') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_FOREIGN_PRODUCTS, 'page=' . $page . '&pID=' . $pInfo->products_id . '&action=delete_product') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_AUTHOR . ' ' . $pInfo->products_author);
            $contents[] = array('text' => TEXT_MANUFACTURER . ' ' . $pInfo->products_manufacturer);
            if (tep_not_null($pInfo->products_date_available)) $contents[] = array('text' => TEXT_DATE_AVAILABLE . ' ' . tep_date_short($pInfo->products_date_available));
            if (tep_not_null($pInfo->products_available_in)) $contents[] = array('text' => sprintf(TEXT_AVAILABLE_IN, $pInfo->products_available_in));
            $contents[] = array('text' => TEXT_GENRE . ' ' . $pInfo->products_genre);
            $contents[] = array('text' => TEXT_LANGUAGE . ' ' . $pInfo->products_language);
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price, false, $pInfo->products_currency));
            if (tep_not_null($pInfo->products_url)) $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_URL . ' <br><a href="' . $pInfo->products_url . '" target="_blank"><u>' . substr($pInfo->products_url, 0, 28) . '...' . substr($pInfo->products_url, -10) . '</u></a>');
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($pInfo->products_date_added));
            if (tep_not_null($pInfo->products_last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($pInfo->products_last_modified));
            $contents[] = array('text' => '<br>' . tep_info_image('foreign/' . $pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br>' . $pInfo->products_image);
          }
        } else { // create category/product info
          $heading[] = array('text' => '<strong>' . EMPTY_CATEGORY . '</strong>');

          $contents[] = array('text' => TEXT_NO_PRODUCTS);
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