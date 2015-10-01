<?php
   header('Content-type: text/html; charset=windows-1251');

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);

  if (isset($_SERVER)) {
	$HTTP_GET_VARS = &$_GET;
	$HTTP_POST_VARS = &$_POST;
	$HTTP_POST_FILES = &$_FILES;
	$HTTP_ENV_VARS = &$_ENV;
	$HTTP_SERVER_VARS = &$_SERVER;
	$HTTP_COOKIE_VARS = &$_COOKIE;
	$HTTP_SESSION_VARS = &$_SESSION;
	$PHP_SELF = $_SERVER['PHP_SELF'];
  }

// Check if register_globals is enabled.
// Since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
  if (function_exists('ini_get')) {
//    ini_get('register_globals') or die('НЕУСТРАНИМАЯ ОШИБКА: регистрация глобальных переменных в файле php.ini запрещена, пожалуйста, исправьте!');
  }

  if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');

  $REMOTE_USER = (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : getenv('REMOTE_USER'));
  if ($REMOTE_USER == '') $REMOTE_USER = $_SERVER['REDIRECT_REMOTE_USER'];
  if ($REMOTE_USER == '' && isset($argv)) $REMOTE_USER = 'robot';

  $dir_fs_catalog = '/var/www/';
  switch (str_replace('www.', '', getenv('HTTP_HOST'))) {
	case 'setbook.org':
	  $dir_fs_catalog .= 'setbook_org';
	  break;
	case 'setbook.kz':
	  $dir_fs_catalog .= 'setbook_kz';
	  break;
	case 'bookva.by':
	  $dir_fs_catalog .= 'bookva_by';
	  break;
	case 'setbook.by':
	  $dir_fs_catalog .= 'setbook_by';
	  break;
	case 'setbook.com.ua':
	  if (strpos($_SERVER['SERVER_SOFTWARE'], 'Win32')!==false) $dir_fs_catalog = '/www/setbook_ua';
	  else $dir_fs_catalog .= 'setbook_ua';
	  break;
	case 'setbook.eu':
	  $dir_fs_catalog .= 'setbook_eu';
	  break;
	case 'setbook.us':
	  $dir_fs_catalog .= 'setbook_us';
	  break;
	case 'setbook.net':
	  $dir_fs_catalog .= 'setbook_net';
	  break;
	case 'knizhnik.eu':
	  $dir_fs_catalog .= 'knizhnik';
	  break;
	case 'setbook.biz':
	  $dir_fs_catalog .= 'setbook_biz';
	  break;
	case 'easternowl.com':
	case 'easternowl.setbook.ru':
	  $dir_fs_catalog .= 'easternowl';
	  break;
	case 'insellbooks.com':
	case 'insellbooks.setbook.ru':
	  $dir_fs_catalog .= 'insellbooks';
	  break;
	case 'test.setbook.ru':
	  $dir_fs_catalog .= 'test';
	  break;
	case 'periodical.setbook.ru:81':
	  $dir_fs_catalog .= 'admin';
	  break;
	default:
	  $dir_fs_catalog .= '2009';
	  break;
  }
  $dir_fs_catalog .= '/';
  if (!is_dir($dir_fs_catalog)) $dir_fs_catalog = '/home/setbook/www/';

  chdir($dir_fs_catalog . 'admin/');

  if (@file_exists($dir_fs_catalog . 'includes/license.php')) {
	if (isset($site_type)) unset($site_type);
	if (isset($allowed_domains)) unset($allowed_domains);
	if (isset($check_link)) unset($check_link);
	if (isset($sitebistro_link)) unset($sitebistro_link);
	if (isset($webnovations_link)) unset($webnovations_link);
	if (isset($up_to_date)) unset($up_to_date);
	if (isset($admin_logins)) unset($admin_logins);

	include($dir_fs_catalog . 'includes/license.php');

	if (!isset($site_type) || !isset($allowed_domains) || !isset($check_link) || (!isset($sitebistro_link) && !isset($webnovations_link)) || !isset($up_to_date) || !isset($admin_logins)) {
	  die('<p><strong>Ошибка чтения лицензионного файла! Обратитесь, пожалуйста, к разработчику!</strong></p>');
	}

// Проверка домена, на котором запущен сайт
	$c_host = preg_replace('/^www\./', '', getenv('HTTP_HOST'));
	if (!is_array($allowed_domains)) $allowed_domains = array();
	if (sizeof($allowed_domains)>0) {
	  if (!in_array($c_host, $allowed_domains)) {
		if (sizeof($allowed_domains)>1) $error = 'доменах';
		else $error = 'домене';
		die('<p><strong>Запуск сайта разрешен только на ' . $error . ':<br /><li>http://' . implode('/</li><br /><li>http://', $allowed_domains) . '/</li></strong></p>');
	  }
	}

// Проверка ограничения работы сайта по дате
	if (!empty($up_to_date)) {
	  if (date('Y-m-d') > $up_to_date) {
		die('<p><strong>Время действия лицензии истекло. Пожалуйста, обратитесь к разработчику!</strong></p>');
	  }
	}

	define('STORE_TYPE', $site_type);

	if (!is_array($admin_logins)) $admin_logins = array();
	if (sizeof($admin_logins) > 0) {
	  if (in_array($REMOTE_USER, $admin_logins)) {
		define('DEBUG_MODE', 'on');
	  }
	}

	unset($site_type);
	unset($allowed_domains);
	unset($check_link);
	if (isset($sitebistro_link)) unset($sitebistro_link);
	if (isset($webnovations_link)) unset($webnovations_link);
	unset($up_to_date);
	unset($admin_logins);
  } else {
	die('<p><strong>Не найден лицензионный файл <code>../includes/license.php</code>!</strong></p>');
  }

// Include application configuration parameters
  require($dir_fs_catalog . 'includes/configure.php');

#  if (HTTP_SERVER!='http://www.setbook.eu') die('На сайте проводятся регламентные работы, зайдите, пожалуйста, через несколько минут');

  define('DIR_FS_ADMIN', DIR_FS_CATALOG . DIR_WS_ADMIN_PART);
  define('DIR_WS_ADMIN', DIR_WS_CATALOG . DIR_WS_ADMIN_PART);
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
  define('DIR_WS_CATALOG_LANGUAGES', DIR_WS_CATALOG . 'includes/languages/');
  define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG . 'images/');
  define('DIR_WS_CATALOG_IMAGES_MIDDLE', DIR_WS_CATALOG_IMAGES . 'middle/');
  define('DIR_WS_CATALOG_IMAGES_BIG', DIR_WS_CATALOG_IMAGES . 'big/');
  define('DIR_WS_CATALOG_BLOCKS', DIR_WS_CATALOG . 'includes/blocks/');

  define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');
  define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
  define('DIR_FS_CATALOG_IMAGES_MIDDLE', DIR_FS_CATALOG_IMAGES . 'middle/');
  define('DIR_FS_CATALOG_IMAGES_BIG', DIR_FS_CATALOG_IMAGES . 'big/');
  define('DIR_FS_CATALOG_BLOCKS', DIR_FS_CATALOG . 'includes/blocks/');
  define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
  define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');

// set php_self in the local scope
  $PHP_SELF = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);

// Used in the "Users" to generate passwords
  $htpasswd_command = trim(@exec('which htpasswd'));
  if (empty($htpasswd_command)) $htpasswd_command = '/usr/local/apache/bin/htpasswd';
  if (!@file_exists($htpasswd_command)) $htpasswd_command = '/usr/local/apache/bin/htpasswd.exe';
  define('LOCAL_EXE_HTPASSWD', $htpasswd_command);

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

// customization for the design layout
  define('BOX_WIDTH', 175); // how wide the boxes should be in pixels (default: 125)

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
  define('CURRENCY_SERVER_PRIMARY', 'rbc');
  define('CURRENCY_SERVER_BACKUP', 'oanda');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  @tep_db_connect() or die('Unable to connect to database server!');

  preg_match("/^(\d+)\.(\d+)\.(\d+)/", mysql_get_server_info(), $m);
  define('MYSQL_VERSION', sprintf("%d%02d%02d", $m[1], $m[2], $m[3]));
  if (MYSQL_VERSION > 40101) {
	tep_db_query("set character_set_client='cp1251'");
	tep_db_query("set character_set_results='cp1251'");
	tep_db_query("set collation_connection='cp1251_general_ci'");
	tep_db_query("set names cp1251");
  }

  $domain_info_query = tep_db_query("select * from " . TABLE_SHOPS . " where shops_url = '" . tep_db_input(HTTP_SERVER) . "'");
  $domain_info = tep_db_fetch_array($domain_info_query);
  list($default_currency) = explode(',', $domain_info['shops_currency']);
  define('DEFAULT_CURRENCY', $default_currency);
  define('SHOP_ID', $domain_info['shops_id']);
  define('SHOP_DESCRIPTION', $domain_info['shops_description']);
  define('DOMAIN_ZONE', substr($domain_info['shops_url'], strrpos($domain_info['shops_url'], '.')+1));
  define('SHOP_PREFIX', $domain_info['shops_prefix']);
  define('DIR_WS_CATALOG_TEMPLATES', DIR_WS_CATALOG . 'includes/templates/' . $domain_info['shops_templates_dir'] . '/');
  define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG . 'includes/templates/' . $domain_info['shops_templates_dir'] . '/');
  define('EMAIL_USE_HTML', ($domain_info['shops_email_use_html']=='1' ? 'true' : 'false'));
  tep_db_select_db($domain_info['shops_database']);

  $shop_ssl_status = 'off';
  if (!empty($domain_info['shops_ssl']) && $domain_info['shops_ssl']!=$domain_info['shops_url']) $shop_ssl_status = 'on';
  define('SHOP_SSL_STATUS', $shop_ssl_status);
  define('ENABLE_SSL', (SHOP_SSL_STATUS=='on' ? true : false));

// set application wide parameters
  $configuration_query = tep_db_query("select configuration_key, configuration_value from " . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['configuration_key'], $configuration['configuration_value']);
  }

  if (preg_match_all('/([^dar]win[dows]*)[\s]?([0-9a-z]*)[\w\s]?([a-z0-9.]*)/i', $_SERVER['SERVER_SOFTWARE'], $match) || preg_match('/(68[k0]{1,3})|(ppc mac os x)|([p\S]{1,5}pc)|(darwin)/i', $_SERVER['SERVER_SOFTWARE'], $match)) {
	define('EMAIL_TRANSPORT', 'smtp');
	define('EMAIL_LINEFEED', "\r\n");
  } else {
	define('EMAIL_TRANSPORT', 'sendmail');
	define('EMAIL_LINEFEED', "\n");
  }

  define('STORE_SESSIONS', 'mysql'); // leave empty '' for default handler or set to 'mysql'
  define('SESSION_WRITE_DIRECTORY', '/tmp');
  define('SESSION_BLOCK_SPIDERS', 'True');
  define('EMAIL_FROM', STORE_NAME . ' <' . STORE_OWNER_EMAIL_ADDRESS . '>');

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// check to see if php implemented session management functions - if not, include php3/php4 compatible session class
  if (!function_exists('session_start')) {
    define('PHP_SESSION_NAME', 'PHPSESSAdminID');
    define('PHP_SESSION_PATH', '/');
    define('PHP_SESSION_SAVE_PATH', SESSION_WRITE_DIRECTORY);

    include(DIR_WS_CLASSES . 'sessions.php');
  }

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  tep_session_name('PHPSESSAdminID');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
   if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, DIR_WS_ADMIN);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', DIR_WS_ADMIN);
  }

// lets start our session
  tep_session_start();

  $lang_query = tep_db_query("select code, languages_id from " . TABLE_LANGUAGES . " where default_status = '1'");
  $lang = tep_db_fetch_array($lang_query);
  define('DEFAULT_LANGUAGE', $lang['code']);

  $user_group_info_query = tep_db_query("select ug.users_groups_shops from " . TABLE_USERS . " u, " . TABLE_USERS_GROUPS . " ug where u.users_status = '1' and u.users_groups_id = ug.users_groups_id and u.users_id = '" . tep_db_input($REMOTE_USER) .  "'");
  $user_group_info = tep_db_fetch_array($user_group_info_query);
  define('ALLOWED_SHOPS', $user_group_info['users_groups_shops']);
  $allowed_shops_array = array();
  if (tep_not_null(ALLOWED_SHOPS)) $allowed_shops_array = explode(',', ALLOWED_SHOPS);

// set the language
  if (!tep_session_is_registered('language') || isset($HTTP_GET_VARS['language'])) {
    if (!tep_session_is_registered('language')) {
      tep_session_register('language');
      tep_session_register('languages_id');
    }

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

    if (isset($HTTP_GET_VARS['language']) && tep_not_null($HTTP_GET_VARS['language'])) {
#      $lng->set_language($HTTP_GET_VARS['language']);
    } else {
#      $lng->get_browser_language();
    }
	$lng->set_language('');

    $language = $lng->language['code'];
    $languages_id = $lng->language['id'];
  }
  if ($language=='') {
	$language = DEFAULT_LANGUAGE;
	$languages_id = $lang['languages_id'];
  }

// include the language translations
  require(DIR_WS_LANGUAGES . 'lang.php');
  $current_page = basename($PHP_SELF);
  if (file_exists(DIR_WS_LANGUAGES . 'lang/' . $current_page)) {
    include(DIR_WS_LANGUAGES .  'lang/' . $current_page);
  }

// set the secret key
  if (isset($HTTP_GET_VARS['some_secret_key']) && tep_not_null($HTTP_GET_VARS['some_secret_key'])) {
	if (!tep_session_is_registered('some_secret_key')) {
	  tep_session_register('some_secret_key');
	}
	$some_secret_key = $HTTP_GET_VARS['some_secret_key'];
  }

  if (tep_session_is_registered('some_secret_key') && md5($some_secret_key)=='837fc715777b20bda9945993864b8cec') {
	define('DEBUG_MODE', 'on');
  } else {
	define('DEBUG_MODE', 'off');
  }

// define our localization functions
  require(DIR_WS_FUNCTIONS . 'localization.php');

// Include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// setup our boxes
  require(DIR_WS_CLASSES . 'table_block.php');
  require(DIR_WS_CLASSES . 'box.php');

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
  require(DIR_WS_CLASSES . 'object_info.php');

// email classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

// file uploading class
  require(DIR_WS_CLASSES . 'upload.php');

// html editor class
  require(DIR_WS_CLASSES . 'editor.php');

// default open navigation box
  if (!tep_session_is_registered('selected_box')) {
    tep_session_register('selected_box');
    $selected_box = 'content';
  }

  if (isset($HTTP_GET_VARS['selected_box'])) {
    $selected_box = $HTTP_GET_VARS['selected_box'];
  }

// check if a default currency is set
  if (!defined('DEFAULT_CURRENCY')) {
    $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
  }

// check if a default language is set
  if (!defined('DEFAULT_LANGUAGE')) {
    $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
  }

  if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
    $messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'warning');
  }

  $blocks_contents = array(
							array('title' => BOX_HEADING_CONTENT,
								  'id' => 'content',
								  'pages' => array(FILENAME_INFORMATION => BOX_CONTENT_SECTIONS,
												   FILENAME_NEWS => BOX_CONTENT_NEWS,
												   FILENAME_BLOCKS => BOX_CONTENT_BLOCKS,
												   FILENAME_PAGES => BOX_CONTENT_PAGES,
												   FILENAME_REVIEWS => BOX_CONTENT_REVIEWS,
												   FILENAME_BOARDS => BOX_CONTENT_BOARDS,
												   FILENAME_MESSAGES => BOX_CONTENT_MESSAGES,
												   FILENAME_BLACKLIST => BOX_CONTENT_BLACKLIST)),
							array('title' => BOX_HEADING_CATALOG,
								  'id' => 'catalog',
								  'pages' => array(FILENAME_CATEGORIES => BOX_CATALOG_CATEGORIES,
//												   FILENAME_PRODUCTS_UPDATES => BOX_CATALOG_UPDATES,
//												   FILENAME_PARAMETERS => BOX_CATALOG_PARAMETERS,
												   FILENAME_MANUFACTURERS => BOX_CATALOG_MANUFACTURERS,
												   FILENAME_SERIES => BOX_CATALOG_SERIES,
												   FILENAME_AUTHORS => BOX_CATALOG_AUTHORS,
												   FILENAME_SPECIALS => BOX_CATALOG_SPECIALS,
												   FILENAME_FOREIGN_PRODUCTS => BOX_CATALOG_FOREIGN_PRODUCTS,
												   FILENAME_EXPECTED_PRODUCTS => BOX_CATALOG_EXPECTED_PRODUCTS,
												   FILENAME_PRODUCTS_UPLOAD => BOX_CATALOG_UPLOAD)),
							array('title' => BOX_HEADING_ORDERS,
								  'id' => 'orders',
								  'pages' => array(FILENAME_CUSTOMERS => BOX_ORDERS_CUSTOMERS,
												   FILENAME_ORDERS => BOX_ORDERS_ORDERS,
												   FILENAME_DISCOUNTS => BOX_ORDERS_DISCOUNTS,
												   'temp_orders.php' => 'Проверка заказов',
												   FILENAME_ADVANCE_ORDERS => BOX_ORDERS_ADVANCE_ORDERS),
								  'depends' => array(FILENAME_ORDERS => FILENAME_PACKINGSLIP, FILENAME_ORDERS => 'cron.php')),
							array('title' => BOX_HEADING_PARTNERS,
								  'id' => 'partners',
								  'pages' => array(FILENAME_PARTNERS => BOX_PARTNERS_PARTNERS)),
							array('title' => BOX_HEADING_CONFIGURATION,
								  'id' => 'configuration',
								  'pages' => array(FILENAME_CONFIGURATION => BOX_CONFIGURATION_SETTINGS,
												   FILENAME_USERS => BOX_CONFIGURATION_USERS)),
							array('title' => BOX_HEADING_MODULES,
								  'id' => 'modules',
								  'pages' => array(FILENAME_MODULES . '?set=payment' => BOX_MODULES_PAYMENT,
												   FILENAME_PAY2GEO => BOX_MODULES_PAYMENT_TO_GEOZONES,
												   FILENAME_MODULES . '?set=shipping' => BOX_MODULES_SHIPPING,
												   FILENAME_SHIP2PAY => BOX_MODULES_SHIPPING_TO_PAYMENT,
												   FILENAME_SHIP2GEO => BOX_MODULES_SHIPPING_TO_GEOZONES,
												   FILENAME_MODULES . '?set=ordertotal' => BOX_MODULES_ORDER_TOTAL)),
							array('title' => BOX_HEADING_LOCALIZATION,
								  'id' => 'localization',
								  'pages' => array(FILENAME_COUNTRIES => BOX_LOCALIZATION_COUNTRIES,
												   FILENAME_ZONES => BOX_LOCALIZATION_ZONES,
												   FILENAME_GEO_ZONES => BOX_LOCALIZATION_GEO_ZONES,
												   FILENAME_CURRENCIES => BOX_LOCALIZATION_CURRENCIES,
												   FILENAME_SHOPS => BOX_LOCALIZATION_SHOPS,
												   FILENAME_SELF_DELIVERY => BOX_LOCALIZATION_SELF_DELIVERY,
												   FILENAME_LANGUAGES => BOX_LOCALIZATION_LANGUAGES,
												   FILENAME_SUBJECTS => BOX_LOCALIZATION_SUBJECTS,
												   FILENAME_ORDERS_STATUS => BOX_LOCALIZATION_ORDERS_STATUS)),
							array('title' => BOX_HEADING_REPORTS,
								  'id' => 'reports',
								  'pages' => array(FILENAME_STATS_PRODUCTS_VIEWED => BOX_REPORTS_VIEWED,
												   FILENAME_STATS_PRODUCTS_PURCHASED => BOX_REPORTS_PURCHASED,
												   FILENAME_STATS_CUSTOMERS => BOX_REPORTS_CUSTOMERS)),
							array('title' => BOX_HEADING_TOOLS,
								  'id' => 'tools',
								  'pages' => array(FILENAME_BACKUP => BOX_TOOLS_BACKUP,
												   FILENAME_BANNER_MANAGER => BOX_TOOLS_BANNERS,
												   FILENAME_FILE_MANAGER => BOX_TOOLS_FILE_MANAGER,
												   FILENAME_MAIL => BOX_TOOLS_MAIL,
												   FILENAME_NEWSLETTERS => BOX_TOOLS_NEWSLETTERS,
												   FILENAME_WHOS_ONLINE => BOX_TOOLS_WHOS_ONLINE),
								  'depends' => array(FILENAME_BANNER_MANAGER => FILENAME_BANNER_STATISTICS)),
							array('title' => BOX_HEADING_TAXES,
								  'id' => 'taxes',
								  'pages' => array(FILENAME_TAX_CLASSES => BOX_TAXES_TAX_CLASSES,
												   FILENAME_TAX_RATES => BOX_TAXES_TAX_RATES)),
  );

  if (DEBUG_MODE=='on') $blocks_contents[0]['pages']['templates.php'] = BOX_CONTENT_TEMPLATES;

  $available_files = array(FILENAME_DEFAULT, FILENAME_POPUP_IMAGE, 'cron.php');
  $available_files_query = tep_db_query("select gb.filename from " . TABLE_USERS . " u, " . TABLE_USERS_GROUPS_TO_CONTENT . " gb where u.users_groups_id = gb.users_groups_id and u.users_id = '" . tep_db_input($REMOTE_USER) . "'");
  while ($available_files_array = tep_db_fetch_array($available_files_query)) {
	$available_files[] = $available_files_array['filename'];
  }

  $all_files = array(FILENAME_DEFAULT, FILENAME_POPUP_IMAGE, 'cron.php');
  reset($blocks_contents);
  while (list(, $block_content) = each($blocks_contents)) {
	reset($block_content['pages']);
	while (list($filename) = each($block_content['pages'])) {
	  $filename = basename($filename);
	  if (strpos($filename, '?')!==false) $filename = substr($filename, 0, strpos($filename, '?'));
	  if (!in_array($filename, $all_files) && file_exists($dir_fs_catalog . DIR_WS_ADMIN_PART . $filename)) {
		$all_files[] = $filename;
		if (isset($block_content['depends']) && is_array($block_content['depends'])) {
		  if (in_array($filename, $available_files) && in_array($filename, array_keys($block_content['depends']))) {
			reset($block_content['depends']);
			while (list($fname, $depend) = each($block_content['depends'])) {
			  if (strpos($depend, '?')!==false) $depend = substr($depend, 0, strpos($depend, '?'));
			  if (in_array($fname, $available_files) && !in_array($depend, $available_files) && file_exists($dir_fs_catalog . DIR_WS_ADMIN_PART . $fname)) {
				$available_files[] = $depend;
				$all_files[] = $depend;
			  }
			}
		  }
		}
	  }
	}
  }
  $all_files = array_unique($all_files);
  $available_files = array_unique($available_files);

  if (DEBUG_MODE=='on') $available_files = $all_files;
	$available_files[] = 'search.php';
  if (in_array(basename($PHP_SELF), $available_files) || basename($PHP_SELF)==FILENAME_DEFAULT) {
  } else {
	header("Status: 403 Forbidden");
	header("HTTP/1.1 403 Forbidden");
	die();
  }

  $denied_operations_query = tep_db_query("select ug2c.denied_actions from " . TABLE_USERS . " u, " . TABLE_USERS_GROUPS_TO_CONTENT . " ug2c where u.users_groups_id = ug2c.users_groups_id and u.users_id = '" . $REMOTE_USER . "' and ug2c.filename = '" . basename($PHP_SELF) . "'");
  $denied_operations_array = tep_db_fetch_array($denied_operations_query);
  $denied_operations = explode(',', $denied_operations_array['denied_actions']);
  if (!is_array($denied_operations)) $denied_operations = array();

  $user_defined_action = '';
  if (isset($HTTP_GET_VARS['action']) && tep_not_null($HTTP_GET_VARS['action'])) {
	$action = $HTTP_GET_VARS['action'];
	if ( stristr($action, 'update') || stristr($action, 'edit') || stristr($action, 'copy') || stristr($action, 'move') ) $user_defined_action = 'edit';
	if (stristr($action, 'delete')) $user_defined_action = 'delete';
	if (tep_not_null($user_defined_action) && in_array($user_defined_action, $denied_operations)) {
	  $messageStack->add_session(TEXT_OPERATION_DENIED, 'error');
	  tep_redirect($_SERVER['HTTP_REFERER']);
	  exit;
	}
  }

  $debug_sections = array();
  $debug_sections[] = array('id' => 'create', 'text' => DEBUG_MODES_DISALLOW_CREATE);
  $debug_sections[] = array('id' => 'edit', 'text' => DEBUG_MODES_DISALLOW_EDIT);
  $debug_sections[] = array('id' => 'delete', 'text' => DEBUG_MODES_DISALLOW_DELETE);

  $debug_information = array();
  $debug_information[] = array('id' => 'move', 'text' => DEBUG_MODES_DISALLOW_MOVE);
  $debug_information[] = array('id' => 'edit', 'text' => DEBUG_MODES_DISALLOW_EDIT);
  $debug_information[] = array('id' => 'delete', 'text' => DEBUG_MODES_DISALLOW_DELETE);
?>