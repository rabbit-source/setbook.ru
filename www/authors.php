<?php
  require('includes/application_top.php');

  $content = FILENAME_AUTHORS;

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

  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_AUTHORS));

  if ($HTTP_GET_VARS['rName']=='index') $HTTP_GET_VARS['rName'] = '';

  $authors_id = 0;
  if (isset($HTTP_GET_VARS['rName'])) {
	$rname = $HTTP_GET_VARS['rName'];
	if (substr($rname, -1)=='/') $rname = substr($rname, 0, -1);
	$author_info_query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " where authors_path = '" . tep_db_input(tep_db_prepare_input($rname)) . "' and authors_status = '1' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	if (tep_db_num_rows($author_info_query) < 1) {
	  $author_info_query = tep_db_query("select authors_id, authors_name from " . TABLE_AUTHORS . " where authors_path = '" . (int)$rname . "' and authors_status = '1' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	}
	$author_info = tep_db_fetch_array($author_info_query);
	$authors_id = $author_info['authors_id'];
	if ($authors_id > 0) {
	  $breadcrumb->add($author_info['authors_name'], tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $authors_id));
	  $content_id = $authors_id;
	  $content_type = 'author';
	} else {
	  tep_redirect(tep_href_link(FILENAME_ERROR_404));
	}
	unset($HTTP_GET_VARS['rName']);
  } else {
#	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }

  $letter = '';
  if (isset($HTTP_GET_VARS['letter'])) {
	$letter = tep_db_prepare_input(urldecode($HTTP_GET_VARS['letter']));
//	if ($letter != 'all') $letter = substr($letter, 0, 1);
	$letter = strtolower($letter);
  }
  if (empty($letter)) $letter = 'all';

  $letters_string = '';

  $authors_last_modified_query = tep_db_query("select max(last_modified) as last_modified from " . TABLE_AUTHORS . "");
  $authors_last_modified = tep_db_fetch_array($authors_last_modified_query);
  $include_authors_cache_filename = false;
  clearstatcache();
  $authors_cache_filename = DIR_FS_CATALOG . 'cache/authors.html';
  $include_authors_cache_filename = false;
  if (file_exists($authors_cache_filename)) {
	if (date('Y-m-d H:i:s', filemtime($authors_cache_filename)) > $authors_last_modified['last_modified']) {
	  $include_authors_cache_filename = true;
	}
  }

  $letters = array();
  //$include_authors_cache_filename = true;
  if ($include_authors_cache_filename) {
	$fp = fopen($authors_cache_filename, 'r');
	while (!feof($fp)) {
	  $letter_char = fgets($fp, 8);
	  $letters[] = trim($letter_char);
	}
	fclose($fp);
  } else {
	if (DEFAULT_LANGUAGE_ID==$languages_id) $letters_query = tep_db_query("select distinct authors_letter from " . TABLE_AUTHORS . " where authors_status = '1' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by authors_letter");
	else $letters_query = tep_db_query("select distinct a1.authors_letter from " . TABLE_AUTHORS . " a1, " . TABLE_AUTHORS . " a2 where a1.authors_status = '1' and a1.authors_id = a2.authors_id and a1.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' and a2.language_id = '" . (int)$languages_id . "' order by a2.authors_letter");
	while ($letters_row = tep_db_fetch_array($letters_query)) {
	  $letters[] = $letters_row['authors_letter'];
	}
	$fp = fopen($authors_cache_filename, 'w');
	fwrite($fp, implode("\n", $letters));
	fclose($fp);
  }

  reset($letters);
  while (list(, $letter_char) = each($letters)) {
	$letters_string .= '<a href="' . tep_href_link(FILENAME_AUTHORS, 'letter=' . urlencode($letter_char)) . '"' . ($letter==$letter_char ? ' class="active_letter"' : '') . '>' . $letter_char . '</a>&nbsp; ';
  }
  if (tep_not_null($letters_string)) $letters_string .= '<a href="' . tep_href_link(FILENAME_AUTHORS) . '"' . ($letter=='all' ? 'class="active_letter"' : '') . '>' . TEXT_ALL_AUTHORS . '</a>';

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>