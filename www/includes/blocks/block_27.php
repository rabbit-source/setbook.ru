<?php
  $page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_SPECIALS)) . "' and language_id = '" . (int)$languages_id . "'");
  $page_info = tep_db_fetch_array($page_info_query);
  if (sizeof($active_specials_types_array) > 0) {
	$specials_types_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in (" . implode(', ', $active_specials_types_array) . ") and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name");
	while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	  echo '<link rel="alternate" type="application/rss+xml" title="' . $page_info['pages_name'] . ': ' . $specials_types['specials_types_name'] . '" href="' . tep_href_link(FILENAME_SPECIALS, 'view=rss&tPath=' . $specials_types['specials_types_id'], 'NONSSL', false) . '" />' . "\n";
	}
  }

  $page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_NEWS)) . "' and language_id = '" . (int)$languages_id . "'");
  $page_info = tep_db_fetch_array($page_info_query);
  if (sizeof($active_news_types_array) > 0) {
	$news_types_query = tep_db_query("select news_types_id, news_types_name, news_types_path from " . TABLE_NEWS_TYPES . " where news_types_id in (" . implode(', ', $active_news_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order, news_types_name");
	while ($news_types = tep_db_fetch_array($news_types_query)) {
	  echo '<link rel="alternate" type="application/rss+xml" title="' . $page_info['pages_name'] . ': ' . $news_types['news_types_name'] . '" href="' . tep_href_link(FILENAME_NEWS, 'view=rss&type=' . $news_types['news_types_path'], 'NONSSL', false) . '" />' . "\n";
	}
  }

  $page_info_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename(FILENAME_REVIEWS)) . "' and language_id = '" . (int)$languages_id . "'");
  $page_info = tep_db_fetch_array($page_info_query);
  if (sizeof($active_reviews_types_array) > 0) {
	$reviews_types_query = tep_db_query("select reviews_types_id, reviews_types_name from " . TABLE_REVIEWS_TYPES . " where reviews_types_id in (" . implode(', ', $active_reviews_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order, reviews_types_name");
	while ($reviews_types = tep_db_fetch_array($reviews_types_query)) {
	  echo '<link rel="alternate" type="application/rss+xml" title="' . $page_info['pages_name'] . ': ' . $reviews_types['reviews_types_name'] . '" href="' . tep_href_link(FILENAME_REVIEWS, 'view=rss&tPath=' . $reviews_types['reviews_types_id'], 'NONSSL', false) . '" />' . "\n";
	}
  }
?>