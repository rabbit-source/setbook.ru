<?php
  $boxContent = '';
  $boxID = 'news';
  if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) {
	$news_query = tep_db_query("select news_id, news_name, date_added from " . TABLE_NEWS . " where news_status = '1' order by date_added desc limit " . MAX_DISPLAY_NEWS);
	$news_count = tep_db_num_rows($news_query);
	if ($news_count > 0) {
	  $i = 0;
	  $level = 0;
	  $box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	  $box_info = tep_db_fetch_array($box_info_query);
	  $boxHeading = $box_info['blocks_name'];
	  while ($news = tep_db_fetch_array($news_query)) {
		$boxContent .= '<div class="li' . (($level==0 && $i==0) ? '_first' : '') . '"><div class="level_0"><a href="' . tep_href_link(FILENAME_NEWS, 'news_id=' . $news['news_id']) . '">' . tep_date_short($news['date_added']) . '<br />' . "\n" . $news['news_name'] . '</a></div></div>' . "\n";
		$i ++;
	  }
	}
  } else {
	$boxHeading = LEFT_COLUMN_TITLE_NEWS;
	$news_years_query = tep_db_query("select distinct year(date_added) as news_year from " . TABLE_NEWS . " where news_status = '1' order by news_year desc");
	$news_years_count = tep_db_num_rows($news_years_query);
	$i = 0;
	while ($news_years = tep_db_fetch_array($news_years_query)) {
	  $boxContent .= '<div class="li' . ($i==0 ? '_first' : '') . '"><div class="level_0"><a href="' . tep_href_link(FILENAME_NEWS, 'year=' . $news_years['news_year']) . '"' . ($news_years['news_year']==$news_year ? ' class="active"' : '') . '>' . $news_years['news_year'] . '</a></div></div>' . "\n";
	  if ($news_years['news_year']==$news_year) {
		$news_monthes_query = tep_db_query("select distinct month(date_added) as news_month from " . TABLE_NEWS . " where news_status = '1' and year(date_added) = '" . (int)$news_years['news_year'] . "' order by news_month desc");
		$news_monthes_count = tep_db_num_rows($news_monthes_query);
		$j = 0;
		while ($news_monthes = tep_db_fetch_array($news_monthes_query)) {
		  $boxContent .= '<div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_NEWS, 'year=' . $news_years['news_year'] . '&month=' . $news_monthes['news_month']) . '"' . (($news_years['news_year']==$news_year && $news_monthes['news_month']==$news_month) ? ' class="active"' : '') . '>' . $monthes_array[(int)$news_monthes['news_month']] . '</a></div></div>' . "\n";
		  $j ++;
		}
	  }
	  $i ++;
	}
  }
  if (tep_not_null($boxContent)) {
	$boxHeading = '<a href="' . tep_href_link(FILENAME_NEWS) . '">' . $boxHeading . '</a>';
	require(DIR_WS_TEMPLATES_BOXES . 'box.php');
  }
?>