<?php
  if ($HTTP_POST_VARS['update_images']=='1' || $HTTP_POST_VARS['update_other_images']=='1' || $HTTP_GET_VARS['update_images']=='1' || $action=='small_upload') {
	$update_images = true;
  } else {
	$update_images = false;
  }

  if (!is_dir(DIR_FS_CATALOG_IMAGES)) mkdir(DIR_FS_CATALOG_IMAGES, 0777);
  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'thumbs/')) mkdir(DIR_FS_CATALOG_IMAGES . 'thumbs/', 0777);
  if (!is_dir(DIR_FS_CATALOG_IMAGES_BIG)) mkdir(DIR_FS_CATALOG_IMAGES_BIG, 0777);

  $from_array =  array('&lt;', '&gt;', '&amp;', '&quot;', '&laquo;', '&raquo;');
  $to_array = array('<', '>', '&', '"', '«', '»');

  $updated = 0;
  $added = 0;
  $not_added = 0;
  $total = 0;
  $products_currency = 'RUR';
  $all_products = array();
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 1', 'поехали', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  $begin_time = date("Y-m-d H:i:s");
  tep_set_time_limit(36000);
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 2', 'создаем временные таблицы', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
  reset($temp_tables);
  while (list(, $temp_table) = each($temp_tables)) {
	tep_db_query("drop table if exists temp_" . $temp_table . "");
	tep_db_query("create table temp_" . $temp_table . " like " . $temp_table . "");
	tep_db_query("insert into temp_" . $temp_table . " select * from " . $temp_table . "");
  }

  $specials_sql_queries = array();
  $used_authors = array();
  $used_series = array();
  $used_manufacturers = array();
  $used_formats = array();
  $used_covers = array();
  $used_categories = array();
  $used_parents = array();
  $update_only_prices = ($HTTP_POST_VARS['only_prices']=='1' ? true : false);
//  tep_db_query("truncate " . TABLE_SEARCH_KEYWORDS_TO_PRODUCTS . "");
//  tep_db_query("truncate " . TABLE_SEARCH_KEYWORDS . "");

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 3', 'начали обработку файла', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  $last_upload_date = '';
  $np = fopen(UPLOAD_DIR . 'CSV/new_products.txt', 'w');
  $fp = fopen($filename, 'r');
  while (($cell = fgetcsv($fp, 15000, ';')) !== FALSE) {
//	if ($total==0 && preg_match('/^\d{4}\-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', trim($cell[0]))) {
//	  $last_upload_date = trim($cell[0]);
//	}
//	echo $last_upload_date;
//	die;

	if ($total > 0 && $total%100000==0) tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 3_' . ($total/100000), 'обработано ' . $total . ' записей', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	if ($action=='upload_other_products') {
	  list($original_products_code, $products_types_id, $products_name, $manufacturers_name, $products_description, $products_description_1, $image_exist, $products_weight, $categories_code, $products_model, $additional_images_count, $new_status, $bestseller_status, $recommended_status, $products_price, $purchase_price, $products_available_in, $serie_code, $products_periodicity_min, $products_periodicity, $media_files) = $cell;

	  if ($products_periodicity > 0 && $products_periodicity_min <= 0) $products_periodicity_min = 1;

	  $products_year = '';
	  $serie_code = '';
	  $author_code = '';
	  $manufacturer_code = '';
	  $subcategories = '';
	  $products_format = '';
	  $products_cover = '';
	  $additional_models = '';
	  $specials_price = '';
	  $reprint_status = '';
	  $soon_status = '';
	} else {
	  list($original_products_code, $products_name, $products_model, $products_price, $additional_models, $products_pages_count, $products_year, $products_cover, $products_format, $products_description, $products_weight, $products_copies, $serie_code, $author_code, $manufacturer_code, $categories_code, $new_status, $reprint_status, $bestseller_status, $recommended_status, $soon_status, $additional_images_count, $image_exist, $subcategories, $products_available_in, $specials_price, $another_price, $purchase_price) = $cell;
// IDKnigi	Name	ISBN	DopISBN	KolPages	Year	Pereplet	Format	Annot	Ves	Tiraj	IDSeria	IDAvtor	IDIzdat	KodTematika	New	DopTiraj	Lider	Rekomenduem	Anons	Elements	Foto	Rubriki

	  $products_periodicity = '';
	  $products_periodicity_min = '';
	  $products_types_id = 1;
	  $manufacturers_name = '';
	}
	if ((int)$original_products_code > 0) {
	  $original_products_code = sprintf('%010d', (int)$original_products_code);
	  $products_code = 'bbk' . $original_products_code;
	  $products_date_available = '';

	  $products_md5_sum = md5(serialize($cell));

	  $products_filenames = array();
	  $products_filenames_dir = UPLOAD_DIR . 'media/' . $products_types_id . '/' . substr($original_products_code, 0, 8) . '/' . $original_products_code . '/';
	  $products_filenames = tep_get_files($products_filenames_dir);
	  if (sizeof($products_filenames) > 0) {
		$products_weight = 0;
		$products_available_in = -1;
	  }

	  $products_check_query = tep_db_query("select products_id, products_model, products_price, products_another_cost, products_purchase_cost, series_id, authors_id, manufacturers_id, products_image_exists, products_image, products_filename, products_md5_sum from " . TABLE_TEMP_PRODUCTS . " where products_code = '" . tep_db_input($products_code) . "' and products_types_id = '" . (int)$products_types_id . "'");
	  $products_check = tep_db_fetch_array($products_check_query);
	  if (!is_array($products_check)) $products_check = array();
	  $products_id = (int)$products_check['products_id'];
	  if (
		($new_status + $reprint_status + $bestseller_status + $recommended_status + $soon_status) > 0 || 
		$products_price != $products_check['products_price'] || 
		$another_price != $products_check['products_another_cost'] || 
		$purchase_price != $products_check['products_purchase_cost'] 
	  ) $products_check['products_md5_sum'] = '';

	  // рисунки
	  if ($products_types_id==1) $image_big = UPLOAD_DIR . 'books/' . substr($original_products_code, 0, -2) . '/' . $original_products_code . '.jpg';
	  else $image_big = UPLOAD_DIR . 'product/' . $products_types_id . '/' . substr($original_products_code, 0, -2) . '/' . $original_products_code . '.jpg';
	  if (!file_exists($image_big) || $image_exist!='1') $image_big = '';

	  $prev_image = $products_check['products_image'];
	  if (!file_exists(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $prev_image)) $prev_image = '';

	  if ( ( tep_not_null($prev_image) && empty($image_big) ) || ( empty($prev_image) && tep_not_null($image_big) ) || ( $update_images == true ) ) {
		$update_image = true;
	  } elseif (tep_not_null($prev_image) && tep_not_null($image_big)) {
//		if (filesize(DIR_FS_CATALOG_IMAGES_BIG . $prev_image) != filesize($image_big)) {
//		  $update_image = true;
//		} else {
		  $update_image = false;
//		}
	  } else {
		$update_image = false;
	  }

	  $products_images = array();
	  if ($products_types_id==1)  {
		$products_files_dir = UPLOAD_DIR . 'ElKnigi/' . substr($original_products_code, 0, 8) . '/';
		if (is_dir($products_files_dir)) {
		  $temp_products_images = tep_get_files($products_files_dir);
		  reset($temp_products_images);
		  while (list(, $temp_products_image) = each($temp_products_images)) {
			if (substr($temp_products_image, 0, 10)==$original_products_code) $products_images[] = $temp_products_image;
		  }
		}
	  } else {
		$products_files_dir = UPLOAD_DIR . 'elproduct/' . $products_types_id . '/' . substr($original_products_code, 0, 8) . '/' . $original_products_code . '/';
		$products_images = tep_get_files($products_files_dir);
	  }

	  $categories_ids = array();
	  $categories = array();
	  if ((int)$categories_code > 0) {
		$categories[] = 'bfd' . sprintf("%010d", (int)$categories_code);
	  }
	  $subcategories_array = explode(';', trim($subcategories));
	  reset($subcategories_array);
	  while (list(, $subcategories_code) = each($subcategories_array)) {
		if ((int)$subcategories_code > 0) {
		  $subcategories_code = 'bfd' . sprintf("%010d", (int)$subcategories_code);
		  if (!in_array($subcategories_code, $categories)) $categories[] = $subcategories_code;
		}
	  }

	  if (sizeof($categories)==0) {
		if ($products_types_id==1) {
		  $categories = array('bfd0000001115');
		} else {
		  $categories = array('bfd0000000000');
		  $categories_ids[] = 0;
		}
	  }

	  reset($categories);
	  while (list(, $category_code) = each($categories)) {
		if (in_array($category_code, array_keys($used_categories))) {
		  $categories_ids[] = $used_categories[$category_code];
		} else {
		  $category_info_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_code = '" . tep_db_input($category_code) . "' and products_types_id = '" . (int)$products_types_id . "'");
		  $category_info = tep_db_fetch_array($category_info_query);
		  $used_categories[$category_code] = (int)$category_info['categories_id'];
		  $categories_ids[] = $category_info['categories_id'];
		}
	  }
	  $categories_ids = array_unique($categories_ids);

	  if ($products_id > 0) {
		$old_category_check_query = tep_db_query("select 1 from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id in ('" . implode("', '", $categories_ids) . "')");
		$old_category_check = tep_db_num_rows($old_category_check_query);
	  }

	  if ($products_id > 0 && $additional_images_count==0 && sizeof($products_filenames)==0 && sizeof($products_images)==0 && empty($products_check['products_filename']) && tep_not_null($products_check['products_md5_sum']) && $products_check['products_md5_sum']==$products_md5_sum && $old_category_check==sizeof($categories_ids) && $update_image==false) {
		tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_last_modified = now() where products_id = '" . (int)$products_id . "'");
		tep_db_query("update " . TABLE_TEMP_SPECIALS . " set specials_date_added = now() where products_id = '" . (int)$products_id . "' and specials_types_id > '1'");
		$total ++;
		continue;
	  }

	  if ($action=='upload_other_products') {
	  } else {
		$products_model = str_replace(array('х', 'Х', 'x'), 'X', $products_model);
		$products_model = str_replace(array('–', '—'), '-', $products_model);
		if (strlen($products_model)==13 && preg_match('/^[\dX]+$/', $products_model)) {
		  $products_model = preg_replace('/^(\d{3})(\d{1})(\d{5})(\d{3})(.{1})$/', '$1-$2-$3-$4-$5', $products_model);
		}
		$products_model = trim(preg_replace('/[^-\dX]/', '', $products_model));
	  }
	  $products_model_1 = preg_replace('/[^\d]/', '', $products_model);

	  $specials_price = str_replace(',', '.', trim($specials_price));

	  $another_price = str_replace(',', '.', trim($another_price));
	  $purchase_price = str_replace(',', '.', trim($purchase_price));

	  $products_price = str_replace(',', '.', trim($products_price));
	  $products_currency = 'RUR';
	  if ($products_currency!=DEFAULT_CURRENCY) {
		$products_price /= $currencies->currencies[$products_currency]['value'];
		$another_price /= $currencies->currencies[$products_currency]['value'];
		$purchase_price /= $currencies->currencies[$products_currency]['value'];
	  }

	  $products_weight = str_replace(',', '.', trim($products_weight)/1000);
	  if ($products_weight <= 0) $products_weight = '0.2';

	  $products_year = (int)trim($products_year);
	  if ($products_year <= 0 || strlen($products_year) != 4) $products_year = 0;

	  if (!$update_only_prices) {
		$products_name = preg_replace('/\s{2,}/', ' ', $products_name);
		$products_name = htmlspecialchars(stripslashes($products_name), ENT_QUOTES);
		$products_name = trim(preg_replace('/\s{2,}/', ' ', $products_name));
		if ($products_types_id==1) {
		  $products_description = str_replace('<div style=height:.7em><spacer/></div>', "\n\n", $products_description);
		  $products_description = tep_db_prepare_input(strip_tags($products_description, '<a>'));
		  $products_description = str_replace(array('&gt;&gt;&gt;', '>>>'), "\n\n", htmlspecialchars(stripslashes($products_description), ENT_QUOTES));
		}

		$series_id = 0;
		$series_name = '';
		if ($serie_code > 0) {
		  $serie_code = 'bsr' . sprintf('%010d', (int)$serie_code);
		  if (in_array($serie_code, array_keys($used_series))) {
			$series_id = $used_series[$serie_code]['id'];
			$series_name = $used_series[$serie_code]['name'];
		  } else {
			$serie_check_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where series_code = '" . $serie_code . "' and products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "'");
			$serie_check = tep_db_fetch_array($serie_check_query);
			$series_id = $serie_check['series_id'];
			$series_name = $serie_check['series_name'];
			if (strlen($serie_check['series_name']) < 3) {
			  $series_id = 0;
			  $series_name = '';
			}
//			$used_series[$serie_code] = array('id' => $series_id, 'name' => $series_name);
		  }
		}

		$authors_id = 0;
		$authors_name = '';
		if ($author_code > 0) {
		  $author_code = 'bat' . sprintf('%010d', (int)$author_code);
		  if (in_array($author_code, array_keys($used_authors))) {
			$authors_id = $used_authors[$author_code]['id'];
			$authors_name = $used_authors[$author_code]['name'];
		  } else {
			$author_check_query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " where authors_code = '" . $author_code . "' and language_id = '" . (int)$languages_id . "'");
			$author_check = tep_db_fetch_array($author_check_query);
			$authors_id = $author_check['authors_id'];
			$authors_name = $author_check['authors_name'];
			if (strlen($author_check['authors_name']) < 3) {
			  $authors_id = 0;
			  $authors_name = '';
			}
//			$used_authors[$author_code] = array('id' => $authors_id, 'name' => $authors_name);
		  }
		}

		$manufacturers_id = 0;
		if ($manufacturer_code > 0) {
		  $manufacturer_code = 'bpb' . sprintf('%010d', (int)$manufacturer_code);
		  if (in_array($manufacturer_code, array_keys($used_manufacturers))) {
			$manufacturers_id = $used_manufacturers[$manufacturer_code]['id'];
			$manufacturers_name = $used_manufacturers[$manufacturer_code]['name'];
		  } else {
			$manufacturer_check_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_code = '" . $manufacturer_code . "' limit 1");
			$manufacturer_check = tep_db_fetch_array($manufacturer_check_query);
			$manufacturers_id = $manufacturer_check['manufacturers_id'];
			$manufacturer_check_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$languages_id . "'");
			$manufacturer_check = tep_db_fetch_array($manufacturer_check_query);
			$manufacturers_name = $manufacturer_check['manufacturers_name'];
			if (strlen($manufacturer_check['manufacturers_name']) < 3) {
			  $manufacturers_id = 0;
			  $manufacturers_name = '';
			}
			$used_manufacturers[$manufacturer_code] = array('id' => $manufacturers_id, 'name' => $manufacturers_name);
		  }
		}

		$products_formats_id = 0;
		if (tep_not_null($products_format)) {
		  if (in_array($products_format, array_keys($used_formats))) {
			$products_formats_id = $used_formats[$products_format]['id'];
		  } else {
			$format_check_query = tep_db_query("select products_formats_id from " . TABLE_PRODUCTS_FORMATS . " where products_formats_name = '" . $products_format . "'");
			if (tep_db_num_rows($format_check_query) > 0) {
			  $format_check = tep_db_fetch_array($format_check_query);
			  $products_formats_id = $format_check['products_formats_id'];
			} else {
			  tep_db_query("insert into " . TABLE_PRODUCTS_FORMATS . " (products_formats_name) values ('" . $products_format . "')");
			  $products_formats_id = tep_db_insert_id();
			}
			$used_formats[$products_format]['id'] = $products_formats_id;
		  }
		}

		$products_cover = trim(htmlspecialchars(stripslashes($products_cover), ENT_QUOTES));
		$products_covers_id = 0;
		if (tep_not_null($products_cover)) {
		  if (in_array($products_cover, array_keys($used_covers))) {
			$products_covers_id = $used_covers[$products_cover]['id'];
		  } else {
			$cover_check_query = tep_db_query("select products_covers_id from " . TABLE_PRODUCTS_COVERS . " where products_covers_name = '" . tep_db_input($products_cover) . "' and language_id = '" . (int)$languages_id . "'");
			$cover_check = tep_db_fetch_array($cover_check_query);
			$products_covers_id = $cover_check['products_covers_id'];
			if ((int)$products_covers_id < 1) {
			  $new_id_query = tep_db_query("select max(products_covers_id) as max_id from " . TABLE_PRODUCTS_COVERS . "");
			  $new_id = tep_db_fetch_array($new_id_query);
			  $products_covers_id = (int)$new_id['max_id'] + 1;
			  tep_db_query("insert into " . TABLE_PRODUCTS_COVERS . " (products_covers_id, products_covers_name, language_id) values ('" . (int)$products_covers_id . "', '" . tep_db_input($products_cover) . "', '" . (int)$languages_id . "')");
			}
			$used_covers[$products_cover]['id'] = $products_covers_id;
		  }
		}
	  }

	  if ($products_name == '') {
		$status = 0;
		$listing_status = 0;
		$xml_status = 0;
	  } elseif ($products_price <= 0 || $soon_status == '1') {
		$status = 1;
		$listing_status = 0;
		$xml_status = 0;
	  } else {
		$status = 1;
		$listing_status = 1;
		$xml_status = 1;
	  }

	  $update_product_info = true;
	  if ($update_only_prices) {
		$update_product_info = false;
	  } elseif (sizeof($products_check) > 0) {
		$old_description_info_query = tep_db_query("select concat_ws(' ', products_name, products_description) as products_text from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages_id . "'");
		$old_description_info = tep_db_fetch_array($old_description_info_query);
		$old_products_text = trim($old_description_info['products_text']);
		$new_products_text = trim($products_name . ' ' . $products_description);

		$products_images_check_query = tep_db_query("select 1 from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "'");
		$products_images_check = tep_db_num_rows($products_images_check_query);

		$old_image_exists = $products_check['products_image_exists'];

		if ($old_products_text==$new_products_text && 
//			$products_check['authors_id']==$authors_id && 
//			$products_check['series_id']==$series_id && 
//			$products_check['manufacturers_id']==$manufacturers_id && 
			$update_image==false && 
			$old_image_exists==$image_exist && 
			sizeof($products_images)==0 && 
			$products_images_check==0 && 
			sizeof($products_filenames)==0 && 
			empty($products_check['products_filename']) && 
			$old_category_check==sizeof($categories_ids)) {
		  $update_product_info = false;
		}
	  }
//	  if ($products_types_id > 1) $update_product_info = true;

	  if ($update_only_prices) {
		$sql_data_array = array('products_code' => $products_code,
								'products_model' => $products_model,
								'products_model_1' => $products_model_1,
								'products_path' => $products_model,
								'products_cost' => $products_price,
								'products_another_cost' => $another_price,
								'products_purchase_cost' => $purchase_price,
								'products_price' => $products_price,
								'products_available_in' => ($products_available_in + 1),
								'products_weight' => $products_weight,
								'products_status' => $status,
								'products_listing_status' => $listing_status,
								'products_xml_status' => $xml_status,
								'products_warranty' => $products_warranty,
								'products_periodicity' => (int)$products_periodicity,
								'products_periodicity_min' => (int)$products_periodicity_min,
								'products_last_modified' => 'now()',
//								'products_md5_sum' => $products_md5_sum,
								);
		tep_db_perform(TABLE_TEMP_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

		$updated ++;
		$total ++;
	  } elseif ($update_product_info == false) {
		$sql_data_array = array('products_code' => $products_code,
								'products_model' => $products_model,
								'products_model_1' => $products_model_1,
								'products_path' => $products_model,
								'products_cost' => $products_price,
								'products_another_cost' => $another_price,
								'products_purchase_cost' => $purchase_price,
								'products_price' => $products_price,
								'products_available_in' => ($products_available_in + 1),
								'products_date_available' => $products_date_available,
								'products_weight' => $products_weight,
								'products_status' => $status,
								'products_listing_status' => $listing_status,
								'products_xml_status' => $xml_status,
								'manufacturers_id' => (int)$manufacturers_id,
								'series_id' => $series_id,
								'authors_id' => $authors_id,
								'products_types_id' => $products_types_id, // Книги
								'products_formats_id' => $products_formats_id,
								'products_covers_id' => $products_covers_id,
								'products_year' => $products_year,
								'products_pages_count' => $products_pages_count,
								'products_copies' => $products_copies,
								'products_warranty' => $products_warranty,
								'products_periodicity' => (int)$products_periodicity,
								'products_periodicity_min' => (int)$products_periodicity_min,
								'products_last_modified' => 'now()',
//								'products_md5_sum' => $products_md5_sum,
								);
		tep_db_perform(TABLE_TEMP_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

		$updated ++;
		$total ++;
	  } else {
		$models = array($products_model);
		if (tep_not_null($additional_models)) {
		  $pmodels = explode(';', $additional_models);
		  reset($pmodels);
		  while (list(, $pmodel) = each($pmodels)) {
			$pmodel = str_replace(array('х', 'Х', 'x'), 'X', $pmodel);
			$pmodel = str_replace(array('–', '—'), '-', $pmodel);
			if (strlen($pmodel)==13 && preg_match('/^[\dX]+$/', $pmodel)) {
			  $pmodel = preg_replace('/^(\d{3})(\d{1})(\d{5})(\d{3})(.{1})$/', '$1-$2-$3-$4-$5', $pmodel);
			}
			$pmodel = trim(preg_replace('/[^-\dX]/', '', $pmodel));
			if (tep_not_null($pmodel) && !in_array($pmodel, $models)) $models[] = $pmodel;
		  }
		}

		$sql_data_array = array('products_code' => $products_code,
								'products_model' => $products_model,
								'products_model_1' => $products_model_1,
								'products_path' => $products_model,
								'products_cost' => $products_price,
								'products_another_cost' => $another_price,
								'products_purchase_cost' => $purchase_price,
								'products_price' => $products_price,
								'products_available_in' => ($products_available_in + 1),
								'products_date_available' => $products_date_available,
								'products_weight' => $products_weight,
								'products_status' => $status,
								'products_listing_status' => $listing_status,
								'products_xml_status' => $xml_status,
								'manufacturers_id' => (int)$manufacturers_id,
								'series_id' => $series_id,
								'authors_id' => $authors_id,
								'products_types_id' => $products_types_id, // Книги
								'products_formats_id' => $products_formats_id,
								'products_covers_id' => $products_covers_id,
								'products_year' => $products_year,
								'products_pages_count' => $products_pages_count,
								'products_copies' => $products_copies,
								'products_warranty' => $products_warranty,
								'products_periodicity' => (int)$products_periodicity,
								'products_periodicity_min' => (int)$products_periodicity_min,
								'products_md5_sum' => $products_md5_sum,
								);
		if ((int)$products_id > 0) {
		  $sql_data_array['products_last_modified'] = 'now()';
		  tep_db_perform(TABLE_TEMP_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

		  $updated ++;
		} else {
		  $sql_data_array['products_date_added'] = 'now()';
		  tep_db_perform(TABLE_TEMP_PRODUCTS, $sql_data_array);
		  $products_id = tep_db_insert_id();
		  fwrite($np, $products_id . "\n");

		  $added ++;
		}

		$products_text = $products_name;
		if (mb_strlen($authors_name, 'CP1251')>2) $products_text .= ' автор ' . $authors_name;
		if (mb_strlen($manufacturers_name, 'CP1251')>2) $products_text .= ($products_types_id>2 ? ' производитель ' : ' издательство ') . $manufacturers_name;
		if (mb_strlen($series_name, 'CP1251')>2) $products_text .= ' серия ' . $series_name;
		$products_text = strip_tags(strtolower(html_entity_decode($products_text)));
		$products_text = str_replace(array('«', '»', '+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—', '_', '-', ':', '#', '\\', '|', '`', '~', '$', '^'), ' ', $products_text);
		$products_text = trim(preg_replace('/\s{2,}/', ' ', $products_text));
		if (tep_not_null($products_model)) $products_text .= ' ISBN ' . implode(' ', $models);
		$insert_sql_data = array('products_name' => $products_name,
								 'products_description' => $products_description,
								 'products_text' => ' ' . trim($products_text) . ' ',
								 'language_id' => $languages_id,
								 'products_id' => $products_id);
		if ($products_types_id > 1) {
		  $insert_sql_data['manufacturers_name'] = $manufacturers_name;
		}
		$product_description_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages_id . "'");
		$product_description_check = tep_db_fetch_array($product_description_check_query);
		if ($product_description_check['total'] > 0) {
		  tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $insert_sql_data, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages_id . "'");
		} else {
		  tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $insert_sql_data);
		}

		$categories_id = 0;
		tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "'");
		reset($categories_ids);
		while (list(, $categories_id) = each($categories_ids)) {
		  tep_db_query("replace into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
		}

		tep_db_query("delete from " . TABLE_PRODUCTS_TO_MODELS . " where products_id = '" . (int)$products_id . "'");
		reset($models);
		while (list(, $model) = each($models)) {
		  if (tep_not_null($model)) {
			tep_db_query("replace into " . TABLE_PRODUCTS_TO_MODELS . " (products_id, products_model, products_model_1) values ('" . (int)$products_id . "', '" . tep_db_input($model) . "', '" . tep_db_input(preg_replace('/[^\d]/', '', $model)) . "')");
		  }
		}


		$existing_images_query = tep_db_query("select products_images_image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' group by products_images_id");
		if (tep_db_num_rows($existing_images_query) > 0) {
		  while ($existing_images_row = tep_db_fetch_array($existing_images_query)) {
			if (file_exists($products_images_dir . basename($existing_images_row['products_images_image']))) {
			  @unlink($products_images_dir . basename($existing_images_row['products_images_image']));
			  @unlink($products_images_dir . 'thumbs/' . basename($existing_images_row['products_images_image']));
			}
		  }
		  tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "'");
		}

		if (sizeof($products_images) > 0) {
		  $products_images_dir = DIR_FS_CATALOG_IMAGES . 'prints/' . substr(sprintf("%06d", $products_id), 0, -4) . '/';
		  if (!is_dir($products_images_dir)) mkdir($products_images_dir, 0777);
		  $products_images_dir .= sprintf("%06d", $products_id) . '/';
		  if (!is_dir($products_images_dir)) mkdir($products_images_dir, 0777);
		  if (!is_dir($products_images_dir . 'thumbs/')) mkdir($products_images_dir . 'thumbs/', 0777);

		  reset($products_images);
		  while (list(, $products_image_path) = each($products_images)) {
			$products_image_path = $products_files_dir . $products_image_path;
			$products_image = strtolower(basename($products_image_path));
			$products_image = mb_convert_encoding($products_image, 'CP1251', 'UTF-8');
			$products_image = urldecode($products_image);

			$is_image = false;
			list($w, $h, $t) = @getimagesize($products_image_path);
			if (in_array($t, array('1', '2', '3'))) $is_image = true;

			if ($is_image) $copied = tep_create_thumb($products_image_path, $products_images_dir . $products_image, '', 750, '85', 'reduce_only');
			else $copied = copy($products_image_path, $products_images_dir . $products_image);

			if ($is_image && $copied) {
			  list($copied_image_width, $copied_image_height) = @getimagesize($products_images_dir . $products_image);
			  if ($copied_image_width < SMALL_IMAGE_WIDTH && $copied_image_height < SMALL_IMAGE_HEIGHT) {
				$copied = false;
				@unlink($products_images_dir . $products_image);
			  }
			}

			if ($copied) {
			  if ($is_image) tep_create_thumb($products_image_path, $products_images_dir . 'thumbs/' . $products_image, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '85', 'reduce_only');

			  $max_id_query = tep_db_query("select max(products_images_id) as max_id from " . TABLE_PRODUCTS_IMAGES . "");
			  $max_id_row = tep_db_fetch_array($max_id_query);
			  $products_images_id = (int)$max_id_row['max_id'] + 1;
			  tep_db_query("insert into " . TABLE_PRODUCTS_IMAGES . " (products_images_id, products_id, products_images_image, language_id) values ('" . (int)$products_images_id . "', '" . (int)$products_id . "', '" . tep_db_input($products_image) . "', '" . (int)$languages_id . "')");
			}
		  }
		}


		$existing_files = explode("\n", $products_check['products_filename']);
		if (!is_array($existing_files)) $existing_files = array();

		if (sizeof($products_filenames) > 0) {
		  $products_files_dir = DIR_FS_DOWNLOAD . substr(sprintf('%010d', $products_id), 0, 6) . '/';
		  if (!is_dir($products_files_dir)) mkdir($products_files_dir, 0777);
		  $products_files_dir .= substr(sprintf('%010d', $products_id), 0, 8) . '/';
		  if (!is_dir($products_files_dir)) mkdir($products_files_dir, 0777);

		  reset($existing_files);
		  while (list($e, $existing_file) = each($existing_files)) {
			if (!in_array($existing_file, $products_filenames) || !file_exists($products_files_dir . $existing_file)) {
			  unlink($products_files_dir . $existing_file);
			  unset($existing_files[$e]);
			}
		  }

		  reset($products_filenames);
		  while (list($f, $products_file_path) = each($products_filenames)) {
			$products_file_path = $products_filenames_dir . $products_file_path;
			$products_file = strtolower(basename($products_file_path));
			$products_file = mb_convert_encoding($products_file, 'CP1251', 'UTF-8');
			$products_file = urldecode($products_file);
			if (file_exists($products_files_dir . $products_file)) {
			  @unlink($products_files_dir . $products_file);
			}

			$copied = copy($products_file_path, $products_files_dir . $products_file);

			if ($copied) {
			  if (!in_array($products_file, $existing_files)) {
				$existing_files[] = $products_file;
			  }
			} else {
			  unset($products_filenames[$f]);
			}
		  }
		}

		if (sizeof($products_filenames) > 0) {
		  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_filename = '" . tep_db_input(implode("\n", $existing_files)) . "' where products_id = '" . (int)$products_id . "'");
		  if ($image_big=='') {
			if (substr($existing_files[0], strrpos($existing_files[0], '.')+1)=='fb2') {
			  $image_big = UPLOAD_DIR . 'other_images/' . $products_id . '.jpg';
			  $product_file_path = $products_filenames_dir . $existing_files[0];
			  $file_content = implode('', file($product_file_path));
			  if (preg_match('/<binary[^>]*>([^<]+)<\/binary>/i', $file_content, $regs)) {
				$fp = fopen($image_big, 'w');
				fwrite($fp, base64_decode($regs[1]));
				fclose($fp);
			  }
			}
		  }
		} elseif (sizeof($existing_files) > 0) {
		  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_filename = null where products_id = '" . (int)$products_id . "'");
		}


		if (in_array($categories_id, array_keys($used_parents))) {
		  $parent_categories = $used_parents[$categories_id];
		} else {
		  $parent_categories = array($categories_id);
		  tep_get_parents($parent_categories, $categories_id);
		  $used_parents[$categories_id] = $parent_categories;
		}

		$product_type_info_query = tep_db_query("select products_types_id from " . TABLE_TEMP_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		$product_type_info = tep_db_fetch_array($product_type_info_query);
		$product_type_name_query = tep_db_query("select products_types_name from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$products_types_id . "' and language_id = '" . (int)$languages_id . "'");
		$product_type_name_row = tep_db_fetch_array($product_type_name_query);
		$products_types_name = $product_type_name_row['products_types_name'];
		$categories_name = tep_get_category_name($parent_categories[sizeof($parent_categories)-1], $languages_id);
		$metatags_page_title = (mb_strlen($authors_name, 'CP1251')>2 ? $authors_name . ', ' : '') . 
							   $products_name . (substr($products_name, -1)!='.' ? '.' : '') . 
							   (tep_not_null($products_types_name) ? ' ' . $products_types_name . '.' : '') . 
							   ' Купить ' . $products_name . ' в интернет-магазине Setbook.';
		$metatags_title = $products_name;
		$metatags_keywords = (tep_not_null($products_types_name) ? $products_types_name . '. ' : '') . (mb_strlen($authors_name, 'CP1251')>2 ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($categories_name) ? $categories_name . '.' : '');
		$metatags_description = (tep_not_null($products_types_name) ? $products_types_name . '. ' : '') . (mb_strlen($authors_name, 'CP1251')>2 ? $authors_name . ', ' : '') . $products_name . (substr($products_name, -1)!='.' ? '. ' : ' ') . (tep_not_null($products_description) ? preg_replace('/^([^\.]+\.).*$/', '$1', preg_replace('/\s{2,}/', ' ', preg_replace('/<\/?[^>]+>/', ' ', $products_description))) : '');
		$content_type = 'product';
		$content_id = $products_id;
		tep_db_query("replace into " . TABLE_METATAGS . " (metatags_page_title, metatags_title, metatags_keywords, metatags_description, language_id, content_type, content_id) values ('" . tep_db_input($metatags_page_title) . "', '" . tep_db_input($metatags_title) . "', '" . tep_db_input($metatags_keywords) . "', '" . tep_db_input($metatags_description) . "', '" . (int)$languages_id . "', '" . tep_db_input($content_type) . "', '" . (int)$content_id . "')");

		tep_db_query("replace into " . TABLE_TEMP_PRODUCTS_INFO . " (products_id, products_code, products_model, products_name, products_description, authors_name, products_price, products_types_id, products_types_name, categories_id, categories_name, manufacturers_name, series_name, products_year, products_weight, products_pages_count, products_copies, products_covers_name, products_formats_name, products_image, products_url, products_available_in, products_status, products_listing_status, products_last_modified) values ('" . (int)$products_id . "', '" . tep_db_input($products_code) . "', '" . tep_db_input(implode(', ', $models)) . "', '" . tep_db_input($products_name) . "', '" . tep_db_input($products_description) . "', '" . tep_db_input($authors_name) . "', '" . $products_price . "', '" . (int)$product_type_info['products_types_id'] . "', '" . tep_db_input($products_types_name) . "', '" . (int)$parent_categories[sizeof($parent_categories)-1] . "', '" . tep_db_input($categories_name) . "', '" . tep_db_input($manufacturers_name) . "', '" . tep_db_input($series_name) . "', '" . tep_db_input($products_year) . "', '" . tep_db_input($products_weight) . "', '" . (int)$products_pages_count . "', '" . (int)$products_copies . "', '" . tep_db_input($products_cover) . "', '" . tep_db_input($products_format) . "', '" . tep_db_input($new_filename) . "', '" . tep_db_input(str_replace(HTTP_SERVER, '', tep_catalog_href_link(FILENAME_CATALOG_PRODUCT_INFO, 'products_id=' . $products_id))) . "', '" . (int)$products_available_in . "', '" . (int)$status . "', '" . (int)$listing_status . "', now())");

		$total ++;
	  }

	  // Новинка - 1
	  // Бестселлер - 2
	  // Мы рекомендуем - 3
	  // Скоро в продаже - 4
	  // Распродажа - 5
	  // Дополнительный тираж - 6
	  $product_specials_types = array();
	  if ($products_year > 0 && $products_year < date('Y')) $new_status = '0';
	  tep_db_query("delete from " . TABLE_TEMP_SPECIALS . " where products_id = '" . (int)$products_id . "' and specials_types_id > '1'");

	  if ($listing_status=='1') {
		if ($new_status=='1') {
		  $specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added) select max(specials_id)+1, '1', '" . (int)$languages_id . "', '" . (int)$products_id . "', now() from " . TABLE_TEMP_SPECIALS . "";
		}
		if ($bestseller_status=='1') {
		  $specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added) select max(specials_id)+1, '2', '" . (int)$languages_id . "', '" . (int)$products_id . "', now() from " . TABLE_TEMP_SPECIALS . "";
		}
		if ($recommended_status=='1') {
		  $specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added) select max(specials_id)+1, '3', '" . (int)$languages_id . "', '" . (int)$products_id . "', now() from " . TABLE_TEMP_SPECIALS . "";
		}
		if ($specials_price > 0 && $specials_price < $products_price) {
		  $specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added, specials_new_products_price) select max(specials_id)+1, '5', '" . (int)$languages_id . "', '" . (int)$products_id . "', now(), '" . $specials_price . "' from " . TABLE_TEMP_SPECIALS . "";
		}
		if ($reprint_status=='1') {
		  $specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added) select max(specials_id)+1, '6', '" . (int)$languages_id . "', '" . (int)$products_id . "', now() from " . TABLE_TEMP_SPECIALS . "";
		}
	  }
	  if ($soon_status=='1') {
		$specials_sql_queries[] = "insert into " . TABLE_TEMP_SPECIALS . " (specials_id, specials_types_id, language_id, products_id, specials_date_added) select max(specials_id)+1, '4', '" . (int)$languages_id . "', '" . (int)$products_id . "', now() from " . TABLE_TEMP_SPECIALS . "";
	  }

	  if ($update_image) {
		if (tep_not_null($prev_image)) {
		  @unlink(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $prev_image);
		  @unlink(DIR_FS_CATALOG_IMAGES_BIG . $prev_image);
		}

		$new_filename = '';

		if (tep_not_null($image_big)) {
		  list($w, $h) = @getimagesize($image_big);
		  if ($w > 10) {
			if (tep_not_null($prev_image)) $products_image_name = basename($prev_image);
			else $products_image_name = substr(uniqid(rand()), 0, 10) . '.jpg';

			$products_path = sprintf('%06d', $products_id);
			$levels = array(substr($products_path, 0, 3), substr($products_path, 3, 2));
			$full_level = '';
			reset($levels);
			while (list(, $level) = each($levels)) {
			  $full_level .= $level . '/';
			  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $full_level)) mkdir(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $full_level, 0777);
			  if (!is_dir(DIR_FS_CATALOG_IMAGES_BIG . $full_level)) mkdir(DIR_FS_CATALOG_IMAGES_BIG . $full_level, 0777);
			}
			if (tep_create_thumb($image_big, DIR_FS_CATALOG_IMAGES . 'thumbs/' . $full_level . $products_image_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '85', 'reduce_only')) {
			  $new_filename = $full_level . $products_image_name;
			  tep_create_thumb($image_big, DIR_FS_CATALOG_IMAGES_BIG . $new_filename, BIG_IMAGE_WIDTH, BIG_IMAGE_HEIGHT, '85', 'reduce_only');
			}
		  }
		}
		tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_image = '" . tep_db_input($new_filename) . "', products_image_exists = '" . (tep_not_null($new_filename) ? '1': '0') . "' where products_id = '" . (int)$products_id . "'");
		tep_db_query("update " . TABLE_TEMP_PRODUCTS_INFO . " set products_image = '" . tep_db_input($new_filename) . "' where products_id = '" . (int)$products_id . "'");
		tep_db_query("update " . TABLE_PRODUCTS . " set products_image = '" . tep_db_input($new_filename) . "', products_image_exists = '" . (tep_not_null($new_filename) ? '1': '0') . "' where products_id = '" . (int)$products_id . "'");
		tep_db_query("update " . TABLE_PRODUCTS_INFO . " set products_image = '" . tep_db_input($new_filename) . "' where products_id = '" . (int)$products_id . "'");
	  }
	}
  }
  fclose($fp);
  fclose($np);

  $products_query = tep_db_query("select products_id, categories_id from " . TABLE_TEMP_PRODUCTS_INFO . " where " . ($action=='upload_other_products' ? "products_types_id > '1'" : "products_types_id = '1'"));
  while ($products = tep_db_fetch_array($products_query)) {
	$products_id = $products['products_id'];
	$categories_id = $products['categories_id'];
	$category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id > '0' order by categories_id limit 1");
	$category = tep_db_fetch_array($category_query);
	if (!is_array($category)) $category = array();
	if ($category['categories_id']!=$categories_id && (int)$category['categories_id']>0) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS_INFO . " set categories_name = '" . tep_db_input(tep_get_category_name($category['categories_id'], $languages_id)) . "', categories_id = '" . (int)$category['categories_id'] . "' where products_id = '" . (int)$products_id . "'");
	}
  }

  reset($specials_sql_queries);
  while (list(, $specials_sql_query) = each($specials_sql_queries)) {
	tep_db_query($specials_sql_query);
  }
  tep_db_query("delete from " . TABLE_TEMP_SPECIALS . " where (specials_types_id > '1' and specials_date_added < '" . date('Y-m-d H:i:s', time()-60*60*24*14) . "') or (status = '0') or (expires_date > 0 and now() >= expires_date)");

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 4', 'закончили обработку файла', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5', 'начали запись обновлений', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  if ($action!='small_upload') {
	if ($action=='upload_products') {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_status = '0', products_listing_status = '0', products_xml_status = '0' where products_types_id = '1'");
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_status = '1', products_listing_status = '1', products_xml_status = '1' where products_types_id = '1' and (products_last_modified >= '" . date('Y-m-d', time()-60*60*18) . " 00:00:00' or (products_last_modified is null and products_date_added >= '" . date('Y-m-d', time()-60*60*18) . " 00:00:00') )");
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_image_exists = '0', sort_order = '0', products_sort_order = '0', manufacturers_sort_order = '0', authors_sort_order = '0', series_sort_order = '0' where products_types_id = '1'");
	} elseif ($action=='upload_other_products') {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_status = '0', products_listing_status = '0', products_xml_status = '0' where products_types_id > '1'");
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_status = '1', products_listing_status = '1', products_xml_status = '1' where products_types_id > '1' and (products_last_modified >= '" . date('Y-m-d', time()-60*60*18) . " 00:00:00' or (products_last_modified is null and products_date_added >= '" . date('Y-m-d', time()-60*60*18) . " 00:00:00') )");
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_image_exists = '0', sort_order = '0', products_sort_order = '0', manufacturers_sort_order = '0', authors_sort_order = '0', series_sort_order = '0' where products_types_id > '1'");
	}
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-1', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_image_exists = '1' where products_image <> ''");

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-2', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-3', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	// сортировка по названию книги
	$query = tep_db_query("select products_id, products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$languages_id . "' order by products_name");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set products_sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
	  $s ++;
	}
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-4', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	// сортировка по названию издательства для книг
	$query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where length(manufacturers_name) > '2' order by manufacturers_name");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set manufacturers_sort_order = '" . (int)$s . "' where manufacturers_id = '" . (int)$row['manufacturers_id'] . "'");
	  $s ++;
	}
	// сортировка по названию издательства для остальных товаров
	$query = tep_db_query("select products_id, manufacturers_name from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$languages_id . "' order by manufacturers_name");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set manufacturers_sort_order = '" . (int)$s . "' where products_id = '" . (int)$row['products_id'] . "'");
	  $s ++;
	}
	tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set manufacturers_id = '0', manufacturers_sort_order = '" . (int)$s . "' where manufacturers_sort_order = '0'");
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-5', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

	// сортировка по имени автора
	$query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " where length(authors_name) > '2' order by authors_name");
	$s = 1;
	while ($row = tep_db_fetch_array($query)) {
	  tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set authors_sort_order = '" . (int)$s . "' where authors_id = '" . (int)$row['authors_id'] . "'");
	  $s ++;
	}
	tep_db_query("update " . TABLE_TEMP_PRODUCTS . " set authors_id = '0', authors_sort_order = '" . (int)$s . "' where authors_sort_order = '0'");
  }
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-6', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_name = replace(products_name, '\\\"', '\"'), products_description = replace(products_description, '\\\"', '\"')");
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-7', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_db_query("update " . TABLE_AUTHORS . " set authors_status = '0'");
  tep_db_query("update " . TABLE_AUTHORS . " set authors_status = '1' where authors_id in (select distinct authors_id from " . TABLE_TEMP_PRODUCTS . " where products_status = '1')");
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-8', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_status = '0'");
  tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_status = '1' where manufacturers_id in (select distinct manufacturers_id from " . TABLE_TEMP_PRODUCTS . " where products_status = '1')");
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 5-9', '', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_db_query("update " . TABLE_SERIES . " set series_status = '0'");
  tep_db_query("update " . TABLE_SERIES . " set series_status = '1' where series_id in (select distinct series_id from " . TABLE_TEMP_PRODUCTS . " where products_status = '1')");

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 6', 'закончили запись обновлений', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 7', 'начали запись обновлений для других БД', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  if ($action=='upload_other_products') $update_products_types_id = '';
  else $update_products_types_id = 1;
  tep_update_all_shops($update_products_types_id);

  if (tep_not_null($last_upload_date)) {
	if ($action=='upload_other_products') $check_key = 'CONFIGURATION_LAST_UPDATE_DATE_OTHER';
	elseif ($action=='upload_products') $check_key = 'CONFIGURATION_LAST_UPDATE_DATE';
	else $check_key = '';
	if (tep_not_null($check_key)) tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($last_upload_date) . "', last_modified = now() where configuration_key = '" . tep_db_input($check_key) . "'");
  }

tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'Шаг 8', 'закончили запись обновлений для других БД', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

  tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1' and products_name = ''");
  $fields = array('products_name', 'products_description');
  $products_query = tep_db_query("select products_id, products_name, products_description, products_model, authors_name, categories_name, manufacturers_name, series_name from " . TABLE_PRODUCTS_INFO . " where products_id not in (select products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1') order by rand()");
  while ($products = tep_db_fetch_array($products_query)) {
	$products_id = $products['products_id'];
	$check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '1'");
	$check = tep_db_fetch_array($check_query);
	if ($check['total']==0) {
	  reset($fields);
	  $products_name = '';
	  $products_description = '';
	  while (list(, $field) = each($fields)) {
		if (tep_not_null($products[$field])) {
		  ${$field} = tep_get_translation($products[$field]);
		} else {
		  ${$field} = '';
		}
	  }

	  $products_text = $products_name;
	  $products_model = $products['products_model'];
	  $authors_name = tep_transliterate($products['authors_name']);
	  $manufacturers_name = tep_transliterate($products['manufacturers_name']);
	  $product_serie_info_query = tep_db_query("select series_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	  $product_serie_info = tep_db_fetch_array($product_serie_info_query);
	  $serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . (int)$product_serie_info['series_id'] . "' and language_id = '1'");
	  $serie_info = tep_db_fetch_array($serie_info_query);
	  $series_name = $serie_info['series_name'];

	  if (strlen($authors_name) > 2) $products_text .= ' by ' . $authors_name;
	  if (strlen($manufacturers_name) > 2) $products_text .= ' publisher ' . $manufacturers_name;
	  if (strlen($series_name) > 2) $products_text .= ' serie ' . $series_name;
	  $products_text = strip_tags(strtolower(html_entity_decode($products_text)));
	  $products_text = str_replace(array('«', '»', '+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—', '_', '-', ':', '#', '\\', '|', '`', '~', '$', '^'), ' ', $products_text);
	  $products_text = trim(preg_replace('/\s{2,}/', ' ', $products_text));
	  if (tep_not_null($products_model)) $products_text .= ' ISBN ' . $products_model;

	  $sql = "insert ignore into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, products_name, products_description, products_text, language_id) values ('" . (int)$products_id . "', '" . tep_db_input($products_name) . "', '" . tep_db_input($products_description) . "', ' " . tep_db_input(trim($products_text)) . " ', '1')";
	  tep_db_query($sql);
	}
  }

  echo sprintf(SUCCESS_RECORDS_UPDATED, $total, $updated, $added, $not_added);
?>