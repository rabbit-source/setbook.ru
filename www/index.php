<?php
  require('includes/application_top.php');

  $content = FILENAME_DEFAULT;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  if (empty($sPath_array) && ($iName=='index' || $iName=='')) {
	define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  }
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  if ((int)$current_section_id > 0) {
	$parents = array($current_section_id);
	tep_get_parents($parents, $current_section_id, TABLE_SECTIONS);
	reset($parents);
	while (list(, $parent_id) = each($parents)) {
	  $section_check_query = tep_db_query("select sections_status from " . TABLE_SECTIONS . " where sections_id = '" . (int)$parent_id . " limit 1'");
	  $section_check = tep_db_fetch_array($section_check_query);
	  if ($section_check['sections_status'] == 0) {
		tep_redirect(tep_href_link(FILENAME_ERROR_404));
	  }
	}
  }

  if (tep_not_null($current_information_id)) {
	$information_sql = "select i.information_id, i.information_name, i2s.information_default_status, i.information_redirect from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = '" . (int)$current_information_id . "' and i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$current_section_id . "' and i.information_status = '1' and i.language_id = '" . (int)$languages_id . "'";
	$information_query = tep_db_query($information_sql);
	$information = tep_db_fetch_array($information_query);
	$content_id = $information['information_id'];
	$content_type = 'information';
	if (tep_not_null($information['information_redirect'])) {
	  tep_redirect($information['information_redirect'], 301);
	  tep_exit();
	} elseif ($information['information_default_status']!='1') {
	  $breadcrumb->add($information['information_name'], tep_href_link(FILENAME_DEFAULT, 'sPath=' . $sPath . '&info_id=' . $information['information_id']));
	}
  } else {
	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }

  if (empty($sPath_array) && ($iName=='index' || $iName=='')) {
	$default_page_template_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES . " where default_status = '1'");
	$default_page_template = tep_db_fetch_array($default_page_template_query);
	$templates_id = $default_page_template['templates_id'];
  }

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>