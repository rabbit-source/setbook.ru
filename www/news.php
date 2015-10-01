<?php
  require('includes/application_top.php');

  $content = FILENAME_NEWS;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_NEWS, (preg_match('/^\/[a-z]+\//', $HTTP_GET_VARS['nName']) ? 'by_theme' : '')));

  $news_depth = '';
  $news_year = '';
  $news_month = '';
  $news_path = '';
  $news_type_id = 0;
  $news_id = '';
  $news_type_info = array();
  if (isset($HTTP_GET_VARS['nName'])) {
	$news_date = $HTTP_GET_VARS['nName'];
	$news_date = substr($news_date, strpos($news_date, '/')+1);
	while (substr($news_date, -1)=='/') $news_date = substr($news_date, 0, -1);
	while (strpos($news_date, '//')) $news_date = str_replace('//', '/', $news_date);
	if (strpos($news_date, '.')) $news_date = substr($news_date, 0, strpos($news_date, '.'));
	$news_params = explode('/', $news_date);
	if (preg_match('/^[a-z]+$/', $news_params[0])) {
	  $news_path = $news_params[0];
	  $news_id = $news_params[1];
	} elseif (preg_match('/^[0-9]+$/', $news_params[0])) {
	  $news_year = $news_params[0];
	  $news_month = $news_params[1];
	  $news_id = $news_params[2];
	} elseif (tep_not_null($news_params[0])) {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	if (tep_not_null($news_path)) {
	  $news_type_query = tep_db_query("select * from " . TABLE_NEWS_TYPES . " where news_types_status = '1' and news_types_path = '" . tep_db_input(tep_db_prepare_input($news_path)) . "' and language_id = '" . (int)$languages_id . "'");
	  if (tep_db_num_rows($news_type_query) > 0) {
		$news_type_info = tep_db_fetch_array($news_type_query);
		$news_type_id = $news_type_info['news_types_id'];
		$breadcrumb->add($news_type_info['news_types_name'], tep_href_link(FILENAME_NEWS, 'tPath=' . $news_type_info['news_types_id']));
	  } else {
		tep_redirect(tep_href_link(FILENAME_ERROR_404));
	  }
	} elseif (tep_not_null($news_year)) {
	  $breadcrumb->add($news_year, tep_href_link(FILENAME_NEWS, 'year=' . $news_year));
	  if (tep_not_null($news_month)) {
		$breadcrumb->add($monthes_array[(int)$news_month], tep_href_link(FILENAME_NEWS, 'year=' . $news_year . '&month=' . $news_month));
	  }
	}
	if (tep_not_null($news_id)) {
	  $news_check_query = tep_db_query("select news_id, news_name, date_added from " . TABLE_NEWS . " where " . ($news_type_id>0 ? "news_types_id = '" . (int)$news_type_id . "'" : "year(date_added) = '" . (int)$news_year . "' and month(date_added) = '" . (int)$news_month . "'") . " and news_id = '" . (int)$news_id . "' and language_id = '" . (int)$languages_id . "'");
	  if (tep_db_num_rows($news_check_query) > 0) {
		$news_check = tep_db_fetch_array($news_check_query);
		$news_depth = 'news';
		$content_id = $news_check['news_id'];
		$content_type = 'news';

		$breadcrumb->add(tep_date_long($news_check['date_added']) . ' - ' . $news_check['news_name'], tep_href_link(FILENAME_NEWS, 'news_id=' . $news_check['news_id'] . ($news_type_id>0 ? '&tPath=' . $news_type_id : '')));
	  }
	}
	unset($HTTP_GET_VARS['nName']);
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>