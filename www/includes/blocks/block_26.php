<?php
  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
  $box_info = tep_db_fetch_array($box_info_query);
  $boxHeading = $box_info['blocks_name'];
  $boxID = 'specials';
  $boxContent = '';
  $active = (basename(SCRIPT_FILENAME)==FILENAME_SPECIALS);
  $boxContent .= '	  <div class="li_first"><div class="level_0"><a href="' . tep_href_link(FILENAME_SPECIALS) . '"' . ($active ? ' class="active"' : '') . ' onclick="if (document.getElementById(\'column_left_specials_block\')) { document.getElementById(\'column_left_specials_block\').style.display = (document.getElementById(\'column_left_specials_block\').style.display==\'none\' ? \'\' : \'none\'); return false; }">' . LEFT_COLUMN_TITLE_SPECIALS . '</a></div></div>' . "\n";
  if (sizeof($active_specials_types_array) > 0) {
	$boxContent .= '	  <span id="column_left_specials_block" style="display: ' . ($active ? '' : 'none') . ';">' . "\n";
	$specials_types_query = tep_db_query("select specials_types_id, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in (" . implode(', ', $active_specials_types_array) . ") and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name");
	while ($specials_types = tep_db_fetch_array($specials_types_query)) {
	  $boxContent .= '		<div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types['specials_types_id']) . '"' . ($specials_types['specials_types_id']==$specials_types_id ? ' class="active"' : '') . '>' . $specials_types['specials_types_name'] . '</a></div></div>' . "\n";
	  if ($specials_types['specials_types_id']==$specials_types_id && $specials_types_id == 1) {
		$years_query = tep_db_query("select distinct year(specials_date_added) as year from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and status = '1' order by year desc");
		if (tep_db_num_rows($years_query) > 0) {
		  while ($years = tep_db_fetch_array($years_query)) {
			$boxContent .= '		<div class="li"><div class="level_2"><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $years['year']) . '"' . ($years['year']==$specials_year ? ' class="active"' : '') . '>' . $years['year'] . '</a></div></div>' . "\n";
			if ($years['year']==$specials_year) {
			  $months_query = tep_db_query("select distinct month(specials_date_added) as month, count(*) as total from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and status = '1' and year(specials_date_added) = '" . (int)$years['year'] . "' group by month(specials_date_added) order by month desc");
			  if (tep_db_num_rows($months_query) > 0) {
				while ($months = tep_db_fetch_array($months_query)) {
				  $boxContent .= '		  <div class="li"><div class="level_3"><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $years['year'] . '&month=' . $months['month']) . '"' . (((int)$months['month']==$specials_month && $years['year']==$specials_year) ? ' class="active"' : '') . '>' . $monthes_array[(int)$months['month']] . '</a></div></div>' . "\n";

				  if ((int)$months['month']==$specials_month && $years['year']==$specials_year) {
					$old_week = 0;
					$weeks_query = tep_db_query("select week(specials_date_added, 5) - week(date_sub(specials_date_added, INTERVAL DAYOFMONTH(specials_date_added) - 1
DAY), 5) +1 as week_added from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and status = '1' and year(specials_date_added) = '" . (int)$years['year'] . "' and month(specials_date_added) = '" . (int)$months['month'] . "' order by week_added desc");
					if (tep_db_num_rows($weeks_query) > 0) {
					  while ($weeks = tep_db_fetch_array($weeks_query)) {
						$week_no = $weeks['week_added'];
						if ($week_no==1) $week_name = sprintf(TEXT_WEEK_1, $week_no);
						elseif ($week_no==2) $week_name = sprintf(TEXT_WEEK_2, $week_no);
						elseif ($week_no==3) $week_name = sprintf(TEXT_WEEK_3, $week_no);
						else $week_name = sprintf(TEXT_WEEK, $week_no);
						if ($week_no!=$old_week) $boxContent .= '			<div class="li"><div class="level_4"><a href="' . tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $years['year'] . '&month=' . $months['month'] . '&week=' . $week_no) . '"' . (($years['year']==$specials_year && (int)$months['month']==$specials_month && $week_no==$specials_week) ? ' class="active"' : '') . '>' . $week_name . '</a></div></div>' . "\n";
						$old_week = $week_no;
					  }
					}
				  }
				}
			  }
			}
		  }
		}
	  }
	}
	$boxContent .= '	</span>' . "\n";
  }

  $active = (basename(SCRIPT_FILENAME)==FILENAME_REVIEWS);
  $boxContent .= '	<div class="li"><div class="level_0"><a href="' . tep_href_link(FILENAME_REVIEWS) . '"' . ($active ? ' class="active"' : '') . ' onclick="if (document.getElementById(\'column_left_reviews_block\')) { document.getElementById(\'column_left_reviews_block\').style.display = (document.getElementById(\'column_left_reviews_block\').style.display==\'none\' ? \'\' : \'none\'); return false; }">' . LEFT_COLUMN_TITLE_REVIEWS . '</a></div></div>' . "\n";
//  if ($active) {
	$boxContent .= '	<span id="column_left_reviews_block" style="display: ' . ($active ? '' : 'none') . ';">' . "\n";
	$reviews_types_query = tep_db_query("select reviews_types_id, reviews_types_name from " . TABLE_REVIEWS_TYPES . " where reviews_types_id in (" . implode(', ', $active_reviews_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order, reviews_types_name");
	while ($reviews_types = tep_db_fetch_array($reviews_types_query)) {
	  $boxContent .= '		<div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_REVIEWS, 'tPath=' . $reviews_types['reviews_types_id']) . '"' . ($reviews_types['reviews_types_id']==$reviews_types_id ? ' class="active"' : '') . '>' . $reviews_types['reviews_types_name'] . '</a></div></div>' . "\n";
	}
	$boxContent .= '	</span>' . "\n";
//  }

  $active = (basename(SCRIPT_FILENAME)==FILENAME_NEWS && ($news_type_id>0 || isset($HTTP_GET_VARS['by_theme'])));
  $boxContent .= '		<div class="li"><div class="level_0"><a href="' . tep_href_link(FILENAME_NEWS, 'by_theme') . '"' . ($active ? ' class="active"' : '') . ' onclick="if (document.getElementById(\'column_left_news_block\')) { document.getElementById(\'column_left_news_block\').style.display = (document.getElementById(\'column_left_news_block\').style.display==\'none\' ? \'\' : \'none\'); return false; }">' . LEFT_COLUMN_TITLE_NEWS_BY_CATEGORY . '</a></div></div>' . "\n";
//  if ($active) {
	$news_types_query = tep_db_query("select news_types_id, news_types_name from " . TABLE_NEWS_TYPES . " where news_types_id in (" . implode(', ', $active_news_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order");
	$boxContent .= '	<span id="column_left_news_block" style="display: ' . ($active ? '' : 'none') . ';">' . "\n";
	while ($news_types = tep_db_fetch_array($news_types_query)) {
	  $boxContent .= '	  <div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_NEWS, 'tPath=' . $news_types['news_types_id']) . '"' . ($news_types['news_types_id']==$news_type_id ? ' class="active"' : '') . '>' . $news_types['news_types_name'] . '</a></div></div>' . "\n";
	}
	$boxContent .= '	</span>' . "\n";
//  }

  $boxContent .= '	<div class="li"><div class="level_0"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'view=with_fragments') . '"' . ($HTTP_GET_VARS['view']=='with_fragments' ? ' class="active"' : '') . '>' . LEFT_COLUMN_TITLE_FRAGMENTS . '</a></div></div>' . "\n";

//  if ($languages_id==DEFAULT_LANGUAGE_ID) $boxContent .= '	<div class="li"><div class="level_0"><a href="' . tep_href_link(FILENAME_HOLIDAY) . '"' . (basename(SCRIPT_FILENAME)==FILENAME_HOLIDAY ? ' class="active"' : '') . '>' . LEFT_COLUMN_TITLE_HOLIDAY . '</a></div></div>' . "\n";

  include(DIR_WS_TEMPLATES_BOXES . 'box.php');
?>