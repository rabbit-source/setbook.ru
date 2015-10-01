<?php
  require('includes/application_top.php');

  $content = FILENAME_SPECIALS;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_SPECIALS));

  if (isset($HTTP_GET_VARS['tName'])) {
	$tname = $HTTP_GET_VARS['tName'];
	if (substr($tname, -1)=='/') $tname = substr($tname, 0, -1);
	$tnames = explode('/', $tname);
	$type_name = $tnames[0];
	$type_info_query = tep_db_query("select * from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_path = '" . tep_db_input(tep_db_prepare_input($type_name)) . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$type_info = tep_db_fetch_array($type_info_query);
	$specials_types_id = $type_info['specials_types_id'];
	$specials_dates_query = tep_db_query("select min(specials_date_added) as min_date, max(specials_date_added) as max_date from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and status = '1'");
	$specials_dates_row = tep_db_fetch_array($specials_dates_query);
	list($min_year, $min_month) = explode('-', $specials_dates_row['min_date']);
	list($max_year, $max_month) = explode('-', $specials_dates_row['max_date']);
	$specials_year = (int)$tnames[1];
	$specials_month = (int)$tnames[2];
	$specials_week = (int)$tnames[3];
	if ($specials_year<$min_year || $specials_year>$max_year) {
	  $specials_year = 0;
	  $specials_month = 0;
	  $specials_week = 0;
	} elseif ($specials_month<1 || $specials_month>12) {
	  $specials_month = 0;
	  $specials_week = 0;
	} elseif ($specials_week<1 || $specials_week>5) {
	  $specials_week = 0;
	}
	if ($specials_types_id > 0) {
	  if (!isset($tnames[1])) {
		if ($specials_types_id == 1) {
		  $max_specials_date_query = tep_db_query("select year(specials_date_added) as specials_year, month(specials_date_added) as specials_month, week(specials_date_added, 5) - week(date_sub(specials_date_added, INTERVAL DAYOFMONTH(specials_date_added) - 1
DAY), 5) +1 as specials_week from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and language_id = '" . (int)$languages_id . "' and status = '1' order by specials_date_added desc limit 1");
		  $max_specials_date_row = tep_db_fetch_array($max_specials_date_query);
		  $specials_year = $max_specials_date_row['specials_year'];
		  $specials_month = $max_specials_date_row['specials_month'];
		  $specials_week = $max_specials_date_row['specials_week'];
		  tep_redirect(tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $specials_year . '&month=' . $specials_month . '&week=' . $specials_week), '307');
		}
	  }
	  $breadcrumb->add($type_info['specials_types_name'], tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id));
	  if ($specials_year > 0 && $specials_types_id == 1) {
		$breadcrumb->add($specials_year, tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $specials_year));
		if ($specials_month > 0) {
		  $breadcrumb->add($monthes_array[(int)$specials_month], tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $specials_year . '&month=' . $specials_month));
		  if ($specials_week > 0) {
			$date_query = tep_db_query("select week(specials_date_added, 5) - week(date_sub(specials_date_added, INTERVAL DAYOFMONTH(specials_date_added) - 1
DAY), 5) +1 as week_added from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$specials_types_id . "' and status = '1' and year(specials_date_added) = '" . (int)$specials_year . "' having week_added = '" . (int)$specials_week . "'");
			$date_row = tep_db_fetch_array($date_query);
			$week_no = $date_row['week_added'];
			if ($week_no==1) $week_name = sprintf(TEXT_WEEK_1, $week_no);
			elseif ($week_no==2) $week_name = sprintf(TEXT_WEEK_2, $week_no);
			elseif ($week_no==3) $week_name = sprintf(TEXT_WEEK_3, $week_no);
			else $week_name = sprintf(TEXT_WEEK, $week_no);
			$breadcrumb->add($week_name, tep_href_link(FILENAME_SPECIALS, 'tPath=' . $specials_types_id . '&year=' . $specials_year . '&month=' . $specials_month . '&week=' . $week_no));
		  }
		}
	  }
	} elseif (trim($HTTP_GET_VARS['tName'])!='') {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['tName']);
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>