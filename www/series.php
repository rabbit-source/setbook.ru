<?php
  require('includes/application_top.php');

  $content = FILENAME_SERIES;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if (isset($_SERVER['REDIRECT_QUERY_STRING'])) $_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
  $qvars = explode('&', $_SERVER['QUERY_STRING']);
  reset($qvars);
  while (list(, $qvar) = each($qvars)) {
	list($qvar_key, $qvar_value) = explode('=', $qvar);
	$HTTP_GET_VARS[$qvar_key] = $qvar_value;
  }

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_SERIES, 'tPath=' . $show_product_type));

  if ($HTTP_GET_VARS['rName']=='index') $HTTP_GET_VARS['rName'] = '';

  $series_id = 0;
  if (isset($HTTP_GET_VARS['rName'])) {
	$rname = $HTTP_GET_VARS['rName'];
	if (substr($rname, -1)=='/') $rname = substr($rname, 0, -1);
	$serie_info_query = tep_db_query("select series_id, series_name from " . TABLE_SERIES . " where series_status = '1' and products_types_id = '" . (int)$show_product_type . "' and series_path = '" . tep_db_input(tep_db_prepare_input($rname)) . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$serie_info = tep_db_fetch_array($serie_info_query);
	$series_id = $serie_info['series_id'];
	if ($series_id > 0) {
	  $breadcrumb->add($serie_info['series_name'], tep_href_link(FILENAME_SERIES, 'series_id=' . $series_id . '&tPath=' . $show_product_type));
	  $content_id = $series_id;
	  $content_type = 'serie';
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['rName']);
  }

  $letter = '';
  if (isset($HTTP_GET_VARS['letter'])) {
	$letter = tep_db_prepare_input(urldecode($HTTP_GET_VARS['letter']));
	if ($letter != 'all') $letter = substr($letter, 0, 1);
	$letter = strtolower($letter);
  }
  if (!preg_match('/#|[àáâãäå¸æçèéêëìíîïðñòóôõö÷øùúûüýþÿ]|[a-z0-9]/i', $letter) || empty($letter)) $letter = 'all';

  $letters_string = '';

  $series_last_modified_query = tep_db_query("select max(last_modified) as last_modified from " . TABLE_SERIES . "");
  $series_last_modified = tep_db_fetch_array($series_last_modified_query);
  $include_series_cache_filename = false;
  clearstatcache();
  $series_cache_filename = DIR_FS_CATALOG . 'cache/series.html';
  $include_series_cache_filename = false;
  if (file_exists($series_cache_filename)) {
	if (date('Y-m-d H:i:s', filemtime($series_cache_filename)) > $series_last_modified['last_modified']) {
	  $include_series_cache_filename = true;
	}
  }

  $letters = array();
  if ($include_series_cache_filename) {
	$fp = fopen($series_cache_filename, 'r');
	while (!feof($fp)) {
	  $letter_char = fgets($fp, 8);
	  $letters[] = trim($letter_char);
	}
	fclose($fp);
  } else {
	$letters_query = tep_db_query("select distinct series_letter as letter from " . TABLE_SERIES . " where series_status = '1' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by letter");
	while ($letters_row = tep_db_fetch_array($letters_query)) {
	  $letters[] = strtolower($letters_row['letter']);
	}
	$fp = fopen($series_cache_filename, 'w');
	fwrite($fp, implode("\n", $letters));
	fclose($fp);
  }

  reset($letters);
  while (list(, $letter_char) = each($letters)) {
	$letters_string .= '<a href="' . tep_href_link(FILENAME_SERIES, 'letter=' . urlencode($letter_char)) . '"' . ($letter==$letter_char ? ' class="active_letter"' : '') . '>' . strtoupper($letter_char) . '</a>&nbsp; ';
  }
  if (tep_not_null($letters_string)) $letters_string .= '<a href="' . tep_href_link(FILENAME_SERIES) . '"' . ($letter=='all' ? 'class="active_letter"' : '') . '>' . TEXT_ALL_SERIES . '</a>';

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>