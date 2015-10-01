<?php
  require('includes/application_top.php');

  $content = FILENAME_RSS;
  $show_parse_time = false;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_RSS));

  if (tep_not_null($HTTP_GET_VARS['rName'])) {
	$rname_array = explode('/', $HTTP_GET_VARS['rName']);
	$rsize = sizeof($rname_array);
	$table_name = $rname_array[$rsize-2];
	$rss_type = $rname_array[$rsize-1];
	if (in_array($table_name, array(TABLE_SPECIALS, TABLE_NEWS, TABLE_REVIEWS, TABLE_BOARDS))) {
	  header('Content-type: text/xml; charset=' . CHARSET . '');

	  $rss_title = STORE_NAME;
	  $rss_link = tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false);
	  $rss_description = STORE_NAME;
	  $rss_date = date('Y-m-d H:i:s');
	  $rss_items = array();

	  if ($table_name==TABLE_NEWS) {
		$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_NEWS)) . "' and language_id = '" . (int)$languages_id . "'");
		$page_info = tep_db_fetch_array($page_info_query);

		$news_type_info_query = tep_db_query("select news_types_id, news_types_name, news_types_short_description from " . TABLE_NEWS_TYPES . " where news_types_path = '" . tep_db_input(tep_db_prepare_input($rss_type)) . "' and language_id = '" . (int)$languages_id . "'");
		$news_type_info = tep_db_fetch_array($news_type_info_query);
		$rss_title = $page_info['pages_name'] . ': ' . $news_type_info['news_types_name'];
		$rss_link = tep_href_link(FILENAME_NEWS, 'by_theme', 'NONSSL', false);
		$rss_self_link = tep_href_link(FILENAME_NEWS, 'tPath=' . $news_type_info['news_types_id'] . '&view=rss', 'NONSSL', false);
		$rss_description = $news_type_info['news_types_short_description'];

		$max_date_query = tep_db_query("select max(date_added) as date_added from " . TABLE_NEWS . " where news_types_id = '" . (int)$news_type_info['news_types_id'] . "' and language_id = '" . (int)$languages_id . "'");
		$max_date = tep_db_fetch_array($max_date_query);
		$rss_date = $max_date['date_added'];

		$query = tep_db_query("select news_id as id, news_name as title, news_description as description, news_image as image, date_added, news_types_id from " . TABLE_NEWS . " where news_status = '1' and news_types_id = '" . (int)$news_type_info['news_types_id'] . "' and language_id = '" . (int)$languages_id . "' order by date_added desc limit 30");
		while ($row = tep_db_fetch_array($query)) {
		  $image = '';
		  if (tep_not_null($row['image'])) {
			$image = str_replace('news/', 'news/thumbs/', $row['image']);
			if (!file_exists(DIR_FS_CATALOG . 'images/' . $image)) $image = '';
		  }
		  $rss_items[] = array('title' => $row['title'],
							   'link' => tep_href_link(FILENAME_NEWS, 'tPath=' . $row['news_types_id'] . '&news_id=' . $row['id'], 'NONSSL', false),
							   'description' => $row['description'],
							   'image' => $image,
							   'date' => gmdate('D, d M Y H:i:s \G\M\T', strtotime($row['date_added'])));
		}
	  } elseif ($table_name==TABLE_SPECIALS) {
		$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_SPECIALS)) . "' and language_id = '" . (int)$languages_id . "'");
		$page_info = tep_db_fetch_array($page_info_query);

		$specials_type_info_query = tep_db_query("select specials_types_id, specials_types_name, specials_types_short_description, specials_last_modified from " . TABLE_SPECIALS_TYPES . " where specials_types_path = '" . tep_db_input(tep_db_prepare_input($rss_type)) . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		$specials_type_info = tep_db_fetch_array($specials_type_info_query);
		$rss_link = tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_type_info['specials_types_id'], 'NONSSL', false);
		$rss_self_link = tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_type_info['specials_types_id'] . '&view=rss', 'NONSSL', false);
		$rss_description = $specials_type_info['specials_types_short_description'];
		$rss_date = $specials_type_info['specials_last_modified'];

		$max_date_query = tep_db_query("select specials_date_added as date_added, year(specials_date_added) as specials_year, month(specials_date_added) as specials_month, week(specials_date_added, 1) as specials_week from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_type_info['specials_types_id'] . "' and language_id = '" . (int)$languages_id . "' and status = '1' order by date_added desc limit 1");
		$max_date = tep_db_fetch_array($max_date_query);

		$rss_title = $page_info['pages_name'] . ': ' . $monthes_array[(int)$max_date['specials_month']] . ' ' . (int)$max_date['specials_year'] . ': ' . $specials_type_info['specials_types_name'];

		$query = tep_db_query("select products_id as id, specials_date_added as date_added from " . TABLE_SPECIALS . " where status = '1' and specials_types_id = '" . (int)$specials_type_info['specials_types_id'] . "' and year(specials_date_added) = '" . (int)$max_date['specials_year'] . "' and week(specials_date_added, 1) = '" . (int)$max_date['specials_week'] . "' and language_id = '" . (int)$languages_id . "' group by products_id order by date_added desc limit 300");
		while ($row = tep_db_fetch_array($query)) {
		  $product_info_query = tep_db_query("select products_name as title, products_description as description, products_image as image, authors_name, manufacturers_name, products_year from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$row['id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if (!is_array($product_info)) $product_info = array();
		  $row = array_merge($row, $product_info);
		  if (tep_not_null($product_info['authors_name'])) $row['title'] = $product_info['authors_name'] . ': ' . $row['title'];
		  if (tep_not_null($product_info['manufacturers_name'])) $row['title'] .= ', ' . $product_info['manufacturers_name'] . ($product_info['products_year']>0 ? ', ' . $product_info['products_year'] : '');
		  if (tep_not_null($row['image'])) $row['image'] = 'thumbs/' . $row['image'];
		  $rss_items[] = array('title' => $row['title'],
							   'link' => tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $row['id'], 'NONSSL', false),
							   'description' => $row['description'],
							   'image' => $row['image'],
							   'date' => gmdate('D, d M Y H:i:s \G\M\T', strtotime($row['date_added'])));
		}
	  } elseif ($table_name==TABLE_REVIEWS) {
		$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_REVIEWS)) . "' and language_id = '" . (int)$languages_id . "'");
		$page_info = tep_db_fetch_array($page_info_query);

		$reviews_type_info_query = tep_db_query("select reviews_types_id, reviews_types_name, reviews_types_short_description from " . TABLE_REVIEWS_TYPES . " where reviews_types_path = '" . tep_db_input(tep_db_prepare_input($rss_type)) . "' and language_id = '" . (int)$languages_id . "'");
		$reviews_type_info = tep_db_fetch_array($reviews_type_info_query);
		$rss_title = $page_info['pages_name'] . ': ' . $reviews_type_info['reviews_types_name'];
		$rss_link = tep_href_link(FILENAME_REVIEWS, '', 'NONSSL', false);
		$rss_self_link = tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_type_info['reviews_types_id'] . '&view=rss', 'NONSSL', false);
		$rss_description = $reviews_type_info['reviews_types_short_description'];

		$max_date_query = tep_db_query("select max(date_added) as date_added from " . TABLE_REVIEWS . " where reviews_types_id = '" . (int)$reviews_type_info['reviews_types_id'] . "' and reviews_status = '1'");
		$max_date = tep_db_fetch_array($max_date_query);
		$rss_date = $max_date['date_added'];

		$query = tep_db_query("select products_id, customers_name, reviews_id as id, reviews_text as description, date_added, reviews_vote from " . TABLE_REVIEWS . " where reviews_types_id = '" . (int)$reviews_type_info['reviews_types_id'] . "' and reviews_status = '1' order by date_added desc limit 30");
		while ($row = tep_db_fetch_array($query)) {
		  $product_info_query = tep_db_query("select products_name as title, products_image as image, authors_name, manufacturers_name, products_year from " . TABLE_PRODUCTS_INFO . " where products_id = '" . (int)$row['products_id'] . "'");
		  $product_info = tep_db_fetch_array($product_info_query);
		  if (!is_array($product_info)) $product_info = array();
		  $row = array_merge($row, $product_info);

		  $stars_string = str_repeat(tep_image(HTTP_SERVER . DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(TEXT_REVIEW_VOTES_OF, $row['reviews_vote'], 5)), $row['reviews_vote']);
		  $stars_string = '' . $stars_string . ' &nbsp; ';

		  $subtitle = '<table border="0" cellpadding="0" cellspacing="0"><tr><td>' . $stars_string . '</td><td>' . $row['customers_name'] . '</td></tr></table>';

		  if (tep_not_null($product_info['authors_name'])) $row['title'] = $product_info['authors_name'] . ': ' . $row['title'];
//		  if (tep_not_null($product_info['manufacturers_name'])) $row['title'] .= ', ' . $product_info['manufacturers_name'] . ($product_info['products_year']>0 ? ', ' . $product_info['products_year'] : '');
		  if (tep_not_null($row['image'])) $row['image'] = 'thumbs/' . $row['image'];

		  $rss_items[] = array('title' => $row['title'],
							   'subtitle' => $subtitle,
							   'link' => tep_href_link(FILENAME_REVIEWS, 'reviews_id=' . $row['id'], 'NONSSL', false) . '#rd' . $row['id'],
							   'description' => $row['description'],
							   'image' => $row['image'],
							   'date' => gmdate('D, d M Y H:i:s \G\M\T', strtotime($row['date_added'])));
		}
	  } elseif ($table_name==TABLE_BOARDS) {
		$page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_BOARDS)) . "' and language_id = '" . (int)$languages_id . "'");
		$page_info = tep_db_fetch_array($page_info_query);

		$boards_type_info_query = tep_db_query("select boards_types_id, boards_types_name, boards_types_short_description from " . TABLE_BOARDS_TYPES . " where boards_types_path = '" . tep_db_input(tep_db_prepare_input($rss_type)) . "' and language_id = '" . (int)$languages_id . "'");
		$boards_type_info = tep_db_fetch_array($boards_type_info_query);
		$rss_title = $page_info['pages_name'] . ': ' . $boards_type_info['boards_types_name'];
		$rss_link = tep_href_link(FILENAME_BOARDS, '', 'NONSSL', false);
		$rss_self_link = tep_href_link(FILENAME_BOARDS, 'tPath=' . $boards_type_info['boards_types_id'] . '&view=rss', 'NONSSL', false);
		$rss_description = $boards_type_info['boards_types_short_description'];

		$max_date_query = tep_db_query("select max(date_added) as date_added from " . TABLE_BOARDS . " where boards_types_id = '" . (int)$boards_type_info['boards_types_id'] . "' and parent_id = '0' and boards_status = '1'");
		$max_date = tep_db_fetch_array($max_date_query);
		$rss_date = $max_date['date_added'];

		$query = tep_db_query("select customers_name, customers_country, customers_city, boards_id as id, boards_name as title, boards_description as description, if(last_modified, last_modified, date_added) as date_added, boards_condition, boards_image as image from " . TABLE_BOARDS . " where boards_types_id = '" . (int)$boards_type_info['boards_types_id'] . "' and parent_id = '0' and boards_status = '1' order by date_added desc limit 30");
		while ($row = tep_db_fetch_array($query)) {
		  $stars_string = str_repeat(tep_image(HTTP_SERVER . DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(TEXT_REVIEW_VOTES_OF, $row['boards_condition'], 5)), $row['boards_condition']);
		  $stars_string = '' . $stars_string . ' &nbsp; ';
//		  $stars_string = '';

		  $subtitle = '<table border="0" cellpadding="0" cellspacing="0"><tr><td>' . $stars_string . '</td><td>' . $row['customers_name'] . ' (' . $row['customers_country'] . '/' . $row['customers_city'] . ')</td></tr></table>';

		  if (tep_not_null($row['image'])) {
			list($row['image']) = explode("\n", $row['image']);
			$row['image'] = 'boards/' . substr(sprintf('%09d', $row['id']), 0, 6) . '/thumbs/' . $row['image'];
		  }

		  $rss_items[] = array('title' => $row['title'],
							   'subtitle' => $subtitle,
							   'link' => tep_href_link(FILENAME_BOARDS, 'boards_id=' . $row['id'], 'NONSSL', false),
							   'description' => $row['description'],
							   'image' => $row['image'],
							   'date' => gmdate('D, d M Y H:i:s \G\M\T', strtotime($row['date_added'])));
		}
	  }
	  echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
	  define('RSS_FEED_SELF_LINK', $rss_self_link);
	  define('RSS_FEED_TITLE', STORE_NAME . ': ' . $rss_title);
	  define('RSS_FEED_LINK', $rss_link);
	  define('RSS_FEED_DESCRIPTION', (tep_not_null($rss_description) ? $rss_description : STORE_NAME . ': ' . $rss_title));
	  define('RSS_FEED_LANGUAGE', 'ru');
	  define('RSS_FEED_PUB_DATE', gmdate('D, d M Y H:i:s \G\M\T', strtotime($rss_date)));
	  define('RSS_FEED_LAST_BUILD_DATE', gmdate('D, d M Y H:i:s \G\M\T', strtotime($rss_date)));
	  define('RSS_FEED_IMAGE_TITLE', STORE_NAME . ': ' . $rss_title);
	  define('RSS_FEED_IMAGE_URL', HTTP_SERVER . DIR_WS_TEMPLATES_IMAGES . 'logo.gif');
	  define('RSS_FEED_IMAGE_LINK', $rss_link);
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
  } else {
	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>