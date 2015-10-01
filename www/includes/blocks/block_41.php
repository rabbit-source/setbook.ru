<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) {
	$from_abroad_page_check_query = tep_db_query("select count(*) as total from " . TABLE_SECTIONS . " where sections_path = 'from_abroad' and sections_status = '1'");
	$from_abroad_page_check = tep_db_fetch_array($from_abroad_page_check_query);
	if ($from_abroad_page_check['total'] > 0) {
	  $images = array();
	  $images[] = array('image_small' => '/images/Image/from_abroad/auto.jpg', 'image_title' => 'Автомобили и запчасти', 'image_link' => '/from_abroad/shops/auto.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/children.jpg', 'image_title' => 'Детские товары', 'image_link' => '/from_abroad/shops/children.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/furniture.jpg', 'image_title' => 'Все для дома', 'image_link' => '/from_abroad/shops/furniture.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/tools.jpg', 'image_title' => 'Инструменты и промышленные товары', 'image_link' => '/from_abroad/shops/tools.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/cosmetics.jpg', 'image_title' => 'Косметика и парфюмерия', 'image_link' => '/from_abroad/shops/cosmetics.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/musical.jpg', 'image_title' => 'Музыкальные инструменты', 'image_link' => '/from_abroad/shops/musical.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/haberdashery.jpg', 'image_title' => 'Одежда, обувь, галантерея', 'image_link' => '/from_abroad/shops/haberdashery.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/sport.jpg', 'image_title' => 'Спортивные товары', 'image_link' => '/from_abroad/shops/sport.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/jewelry_watches.jpg', 'image_title' => 'Украшения и часы', 'image_link' => '/from_abroad/shops/jewelry_watches.html');
	  $images[] = array('image_small' => '/images/Image/from_abroad/electronics.jpg', 'image_title' => 'Электроника', 'image_link' => '/from_abroad/shops/electronics.html');

	  $box_info_query = tep_db_query("select blocks_id, blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	  $box_info = tep_db_fetch_array($box_info_query);
	  $boxHeading = '<a href="' . tep_href_link(DIR_WS_CATALOG . 'from_abroad/') . '">' . $box_info['blocks_name'] . '</a>';
	  $carousel_id = 'from_abroad_carousel';
	  $boxID = 'block_' . $carousel_id;
	  $carousel_function_name = $carousel_id . '_onload';
	  $boxContent = tep_show_images_carousel($images, $carousel_id);
	  include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
	}
  }
?>