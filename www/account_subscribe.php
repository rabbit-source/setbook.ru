<?php
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') || !tep_session_is_registered('customer_first_name')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $content = 'account_subscribe.php';
  $javascript = 'account_newsletters.js';

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

//Модель
 //1: Тематики и разделы
  $query = tep_db_query("SELECT s.category_id as id, c.categories_name as name FROM subscribe s
  LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." AS c ON c.categories_id = s.category_id
  WHERE s.user_id = " . (int)$customer_id . " 
  AND s.type_id = 1
  AND c.language_id = '" . (int)$languages_id . "'
  ORDER BY name ASC;");
  $data['section'] = tep_db_fetch_array_all($query);
  //2: Серии
  $query = tep_db_query("SELECT s.category_id as id, c.series_name as name FROM subscribe s
  LEFT JOIN ".TABLE_SERIES." AS c ON c.series_id = s.category_id
  WHERE s.user_id = " . (int)$customer_id . " 
  AND s.type_id = 2
  AND c.language_id = '" . (int)$languages_id . "'
  ORDER BY name ASC;");
  $data['series'] = tep_db_fetch_array_all($query);
  //3: Авторы
  $query = tep_db_query("SELECT s.category_id as id, c.authors_name as name FROM subscribe s
  LEFT JOIN ".TABLE_AUTHORS." AS c ON c.authors_id = s.category_id
  WHERE s.user_id = " . (int)$customer_id . " 
  AND s.type_id = 3
  AND c.language_id = '" . (int)$languages_id . "'
  ORDER BY name ASC;");
  $data['authors'] = tep_db_fetch_array_all($query);
  //4: Издательство
  $query = tep_db_query("SELECT s.category_id as id, c.manufacturers_name as name FROM subscribe s
  LEFT JOIN ".TABLE_MANUFACTURERS_INFO." AS c ON c.manufacturers_id = s.category_id
  WHERE s.user_id = " . (int)$customer_id . " 
  AND s.type_id = 4
  AND c.languages_id = '" . (int)$languages_id . "'
  ORDER BY name ASC;");
  $data['munufacturers'] = tep_db_fetch_array_all($query);
//End Модель

//Форма
  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process')) {
    for ($i = 1; $i <= 4; $i++) {
	  if(count($HTTP_POST_VARS['subscribe_'.$i]) > 0) {
		foreach($HTTP_POST_VARS['subscribe_'.$i] as $key => $val) {
		  tep_db_query("DELETE FROM subscribe WHERE user_id = ".$customer_id." AND category_id = ".$key." AND type_id = ".$i.";");
		}
	  }
	}

	$messageStack->add_session('header', SUCCESS_SUBSCRIBE, 'success');

	tep_redirect(tep_href_link('account_subscribe.php', '', 'SSL'));
  }

  $account_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_ACCOUNT) . "'");
  $account_page = tep_db_fetch_array($account_page_query);

  $breadcrumb->add($account_page['pages_name'], tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>