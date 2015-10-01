<?php
  require('includes/application_top.php');

  $content = FILENAME_MANUFACTURERS;

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

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_MANUFACTURERS));

  $manufacturers_id = 0;
  if (isset($HTTP_GET_VARS['mName'])) {
	$mname = $HTTP_GET_VARS['mName'];
	if (substr($mname, -1)=='/') $mname = substr($mname, 0, -1);
	$manufacturer_info_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_name from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_status = '1' and m.manufacturers_path = '" . tep_db_input(tep_db_prepare_input($mname)) . "' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	if (tep_db_num_rows($manufacturer_info_query) < 1) {
	  $manufacturer_info_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_name from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_status = '1' and m.manufacturers_id = '" . (int)$mname . "' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	}
	$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
	$manufacturers_id = $manufacturer_info['manufacturers_id'];
	if ($manufacturers_id > 0) {
	  $breadcrumb->add($manufacturer_info['manufacturers_name'], tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $manufacturers_id));
	  $content_id = $manufacturers_id;
	  $content_type = 'manufacturer';
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['mName']);
  } else {
#	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }

  $letter = '';
  if (isset($HTTP_GET_VARS['letter'])) {
	$letter = tep_db_prepare_input(urldecode($HTTP_GET_VARS['letter']));
	if ($letter != 'all') $letter = substr($letter, 0, 1);
	$letter = strtolower($letter);
  }
  if (!preg_match('/#|[àáâãäå¸æçèéêëìíîïðñòóôõö÷øùúûüýþÿ]|[a-z0-9]/i', $letter) || empty($letter)) $letter = 'all';

  $letters_string = '';

  $manufacturers_last_modified_query = tep_db_query("select max(last_modified) as last_modified from " . TABLE_MANUFACTURERS . "");
  $manufacturers_last_modified = tep_db_fetch_array($manufacturers_last_modified_query);
  $include_manufacturers_cache_filename = false;
  clearstatcache();
  $manufacturers_cache_filename = DIR_FS_CATALOG . 'cache/manufacturers.html';
  $include_manufacturers_cache_filename = false;
  if (file_exists($manufacturers_cache_filename)) {
	if (date('Y-m-d H:i:s', filemtime($manufacturers_cache_filename)) > $manufacturers_last_modified['last_modified']) {
	  $include_manufacturers_cache_filename = true;
	}
  }

  $letters = array();
  //$include_manufacturers_cache_filename  = true;
  if ($include_manufacturers_cache_filename) {
	$fp = fopen($manufacturers_cache_filename, 'r');
	while (!feof($fp)) {
	  $letter_char = fgets($fp, 8);
	  $letters[] = trim($letter_char);
	}
	fclose($fp);
  } else {
	$letters_query = tep_db_query("select distinct mi.manufacturers_letter as letter from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_status = '1' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by letter");
	while ($letters_row = tep_db_fetch_array($letters_query)) {
	  $letters[] = strtolower($letters_row['letter']);
	}
	$fp = fopen($manufacturers_cache_filename, 'w');
	fwrite($fp, implode("\n", $letters));
	fclose($fp);
  }

  reset($letters);
  while (list(, $letter_char) = each($letters)) {
	$letters_string .= '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'letter=' . urlencode($letter_char)) . '"' . ($letter==$letter_char ? ' class="active_letter"' : '') . '>' . strtoupper($letter_char) . '</a>&nbsp; ';
  }
  if (tep_not_null($letters_string)) $letters_string .= '<a href="' . tep_href_link(FILENAME_MANUFACTURERS) . '"' . ($letter=='all' ? 'class="active_letter"' : '') . '>' . TEXT_ALL_MANUFACTURERS . '</a>';

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>