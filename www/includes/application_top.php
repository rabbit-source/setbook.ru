<?php
  header('Content-type: text/html; charset=windows-1251');

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
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

// check if register_globals is enabled.
// since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
  if (function_exists('ini_get')) {
    ini_get('register_globals') or die('НЕУСТРАНИМАЯ ОШИБКА: регистрация глобальных переменных в файле php.ini запрещена, пожалуйста, исправьте!');
  }

  if ($_SERVER['USE_DEBUG_CONFIGURATION'] != 1)
  {
  	require('includes/configure.php');
  }
  else
  {
  	require('includes/configure_debug.php');
  }
  	 
// include server parameters
//	if ($_SERVER['REMOTE_ADDR']=='94.199.108.66') echo getenv('HTTP_HOST');

  chdir(DIR_FS_CATALOG);

#  if (HTTP_SERVER!='http://www.setbook.ru') if ($_SERVER['REMOTE_ADDR']!='94.199.108.66') die('На сайте проводятся регламентные работы, зайдите, пожалуйста, через 5 минут / Sorry, site is closed for a few minutes.');

  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');

  define('DIR_WS_IMAGES', DIR_WS_CATALOG . 'images/');
  define('DIR_WS_IMAGES_BIG', DIR_WS_IMAGES . 'big/');
  define('DIR_WS_IMAGES_MIDDLE', DIR_WS_IMAGES . 'middle/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');

  define('DIR_WS_BLOCKS', DIR_WS_INCLUDES . 'blocks/');
  define('DIR_WS_JAVASCRIPT', DIR_WS_INCLUDES . 'javascript/');
  define('DIR_WS_CONTENT', DIR_WS_INCLUDES . 'content/');

  define('USE_PCONNECT', 'false'); // use persistent connections?
  define('STORE_SESSIONS', 'mysql'); // leave empty '' for default handler or set to 'mysql'

  if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('location: install/index.php');
    }
  }

// set the type of request (secure or not)
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

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

  $shops_count = 0;
  $available_shops_string = '';
  $available_currencies = array();
  $shops_query = tep_db_query("select * from " . TABLE_SHOPS . " where 1 order by sort_order, shops_url");
  while ($shops = tep_db_fetch_array($shops_query)) {
	if ($shops['shops_url']==HTTP_SERVER) {
	  $available_currencies = explode(',', $shops['shops_currency']);
	  $default_currency = $available_currencies[0];
	  define('DEFAULT_CURRENCY', $default_currency);
	  define('SHOP_ID', $shops['shops_id']);
	  define('DOMAIN_ZONE', substr($shops['shops_url'], strrpos($shops['shops_url'], '.')+1));
	  define('SHOP_PREFIX', $shops['shops_prefix']);
	  define('SHOP_LISTING_STATUS', $shops['shops_listing_status']);
	  define('STORE_DESCRIPTION', $shops['shops_description']);
	  define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/' . $shops['shops_templates_dir'] . '/');
	  define('EMAIL_USE_HTML', ($shops['shops_email_use_html']=='1' ? 'true' : 'false'));

	  tep_db_select_db($shops['shops_database']);

	  $shop_ssl_status = 'off';
	  if (!empty($shops['shops_ssl']) && $shops['shops_ssl']!=$shops['shops_url']) $shop_ssl_status = 'on';
	  define('SHOP_SSL_STATUS', $shop_ssl_status);
	}
	if ($shops['shops_listing_status']=='1' && $shops['shops_url']!=HTTP_SERVER && $shops_count < 5) {
	  $available_shops_string .= '<a href="' . $shops['shops_url'] . '">' . $shops['shops_name'] . '</a> ';
	  $shops_count ++;
	}
  }
  if (SHOP_LISTING_STATUS=='0') $available_shops_string = '';

  define('AVAILABLE_SHOPS_LINKS', trim($available_shops_string));

  define('DIR_WS_TEMPLATES_BOXES', DIR_WS_TEMPLATES . 'boxes/');
  define('DIR_WS_TEMPLATES_IMAGES', DIR_WS_CATALOG . DIR_WS_TEMPLATES . 'images/');
  define('DIR_WS_TEMPLATES_STYLES', DIR_WS_CATALOG . DIR_WS_TEMPLATES . 'styles/');

// set the application parameters
  $configuration_query = tep_db_query("select configuration_key, configuration_value from configuration");
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

  define('BASE_HREF', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);

  define('SESSION_BLOCK_SPIDERS', 'True');
  define('EMAIL_FROM', STORE_NAME . ' <' . STORE_OWNER_EMAIL_ADDRESS . '>');

  if (!empty($_SERVER['REQUEST_URI'])) $self_page = $_SERVER['REQUEST_URI'];
  else $self_page = $_SERVER['PHP_SELF'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
  define('REQUEST_URI', $self_page);
  if (strpos($self_page, '?')!==FALSE) $self_page = substr($self_page, 0, strpos($self_page, '?'));
  define('PHP_SELF', $self_page);
  define('SCRIPT_FILENAME', $_SERVER['SCRIPT_NAME']);

  $current_page_address = (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . PHP_SELF;
  define('CURRENT_PAGE_ADDRESS', $current_page_address);

// define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

// set the cookie domain
  $cookie_domain = HTTP_COOKIE_DOMAIN;
  $cookie_path = HTTP_COOKIE_PATH;

// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');

// include navigation history class
  require(DIR_WS_CLASSES . 'navigation_history.php');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// locks and synchronization support
  include(DIR_WS_FUNCTIONS . 'locks.php');

  /*tep_log_ex('Process starts: pid=[' . getmypid() . '], script=[' . __FILE__ . ']');
  if (isset($_SERVER['REQUEST_URI']))
  	tep_log_ex('Process info: pid=[' . getmypid() . '], uri=[' . $_SERVER['REQUEST_URI'] . ']');*/
  
// check if sessions are supported, otherwise use the php3 compatible session class
  if (!function_exists('session_start')) {
    define('PHP_SESSION_NAME', 'PHPSESSID');
    define('PHP_SESSION_PATH', $cookie_path);
    define('PHP_SESSION_DOMAIN', $cookie_domain);
    define('PHP_SESSION_SAVE_PATH', SESSION_WRITE_DIRECTORY);

    include(DIR_WS_CLASSES . 'sessions.php');
  }

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  tep_session_name('PHPSESSID');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

  $synchronized_pages = array(FILENAME_CHECKOUT_PROCESS);
  //if (in_array(basename(PHP_SELF), $synchronized_pages)) {
	$use_session_synchronization = true;
  	//tep_order_log_start(tep_session_id());
  //}
  
// set the session cookie parameters
   if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $cookie_path, $cookie_domain);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $cookie_path);
    ini_set('session.cookie_domain', $cookie_domain);
  }

// set the session ID if it exists
  if (isset($HTTP_POST_VARS[tep_session_name()])) {
	tep_session_id($HTTP_POST_VARS[tep_session_name()]);
  } elseif ( ($request_type == 'SSL') && isset($HTTP_GET_VARS[tep_session_name()]) ) {
	tep_session_id($HTTP_GET_VARS[tep_session_name()]);
  }

  if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');

// start the session
  $session_started = false;
  if (SESSION_BLOCK_SPIDERS == 'True') {
    $user_agent = strtolower(getenv('HTTP_USER_AGENT'));
    $spider_flag = false;

    if (tep_not_null($user_agent)) {
      $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');

      for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
        if (tep_not_null($spiders[$i])) {
          if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
            $spider_flag = true;
            break;
          }
        }
      }
    }

    if ($spider_flag == false) {
      tep_session_start();
      $session_started = true;
    }
  } else {
    tep_session_start();
    $session_started = true;
  }
  
  if ($session_started) {
	if (SHOP_SSL_STATUS=='on') {
	  if (isset($HTTP_GET_VARS['ssl']) && ($HTTP_GET_VARS['ssl']=='on' || $HTTP_GET_VARS['ssl']=='off') ) {
		$enable_ssl = $HTTP_GET_VARS['ssl'];
		if (!tep_session_is_registered('enable_ssl')) tep_session_register('enable_ssl');
		if (strpos($_SERVER['HTTP_REFERER'], str_replace('http://', '', HTTP_SERVER))!==false) {
		  tep_redirect($_SERVER['HTTP_REFERER']);
		}
	  } elseif (!tep_session_is_registered('enable_ssl')) {
		if ($request_type=='SSL') {
		  $enable_ssl = 'on';
		  tep_session_register('enable_ssl');
		} elseif (isset($HTTP_GET_VARS['ssl']) && ($HTTP_GET_VARS['ssl']=='on' || $HTTP_GET_VARS['ssl']=='off') ) {
		  $enable_ssl = $HTTP_GET_VARS['ssl'];
		  if (!tep_session_is_registered('enable_ssl')) tep_session_register('enable_ssl');
		  if (strpos($_SERVER['HTTP_REFERER'], str_replace('http://', '', HTTP_SERVER))!==false) {
			tep_redirect($_SERVER['HTTP_REFERER']);
		  }
		} else {
		  $session_name = tep_session_name();
		  $session_id = tep_session_id();
		  $link = REQUEST_URI;

		  if (strpos($link, $session_name)===false) {
			if (strpos($link, '?')!==false) $link .= '&' . $session_name . '=' . $session_id;
			else $link .= '?' . $session_name . '=' . $session_id;
		  }

		  if (strpos($link, '?')!==false) $link .= '&ssl=ssl_value';
		  else $link .= '?ssl=ssl_value';

		  $javascript = 'ssl_check.js.php';
		}
	  }
	} else {
	  $enable_ssl = 'off';
	}
  } else {
	$enable_ssl = SHOP_SSL_STATUS;
  }
  define('ENABLE_SSL', ($enable_ssl=='on' ? true : false));

  if ($_SERVER['USE_DEBUG_CONFIGURATION'] != 1)
  {
  	$encrypted_pages = array(FILENAME_ACCOUNT, FILENAME_ACCOUNT_BOARDS, FILENAME_ACCOUNT_EDIT, FILENAME_ACCOUNT_HISTORY, FILENAME_ACCOUNT_HISTORY_INFO, FILENAME_ACCOUNT_NEWSLETTERS, FILENAME_ACCOUNT_PASSWORD, FILENAME_ACCOUNT_WISHLIST, FILENAME_ADDRESS_BOOK, FILENAME_ADDRESS_BOOK_PROCESS, FILENAME_CHECKOUT_CONFIRMATION, FILENAME_CHECKOUT_PAYMENT, FILENAME_CHECKOUT_PAYMENT_ADDRESS, FILENAME_CHECKOUT_PROCESS, FILENAME_CHECKOUT_SHIPPING, FILENAME_CHECKOUT_SHIPPING_ADDRESS, FILENAME_CHECKOUT_SUCCESS, FILENAME_CREATE_ACCOUNT, FILENAME_CREATE_ACCOUNT_SUCCESS, FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_PARTNER, FILENAME_PASSWORD_FORGOTTEN);
  }
  else
  {
  	$encrypted_pages = array();
  }

  if (ENABLE_SSL == true && in_array(basename(PHP_SELF), $encrypted_pages)) { // We are loading an SSL page
	$url = $current_page_address;
	if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url
	  $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL
	  tep_redirect($url);
	}
  }

// set SID once, even if empty
  $SID = (defined('SID') ? SID : '');

// verify the ssl_session_id if the feature is enabled
  if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true) ) {
    $ssl_session_id = getenv('SSL_SESSION_ID');
    if (!tep_session_is_registered('SSL_SESSION_ID')) {
      $SESSION_SSL_ID = $ssl_session_id;
      tep_session_register('SESSION_SSL_ID');
    }

    if ($SESSION_SSL_ID != $ssl_session_id) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_DEFAULT));
    }
  }

// verify the browser user agent if the feature is enabled
  if (SESSION_CHECK_USER_AGENT == 'True') {
    $http_user_agent = getenv('HTTP_USER_AGENT');
    if (!tep_session_is_registered('SESSION_USER_AGENT')) {
      $SESSION_USER_AGENT = $http_user_agent;
      tep_session_register('SESSION_USER_AGENT');
    }

    if ($SESSION_USER_AGENT != $http_user_agent) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// verify the IP address if the feature is enabled
  if (SESSION_CHECK_IP_ADDRESS == 'True') {
    $ip_address = tep_get_ip_address();
    if (!tep_session_is_registered('SESSION_IP_ADDRESS')) {
      $SESSION_IP_ADDRESS = $ip_address;
      tep_session_register('SESSION_IP_ADDRESS');
    }

    if ($SESSION_IP_ADDRESS != $ip_address) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// create the shopping cart & fix the cart if necesary
  if (tep_session_is_registered('cart') && is_object($cart)) {
    if (PHP_VERSION < 4) {
      $broken_cart = $cart;
      $cart = new shoppingCart;
      $cart->unserialize($broken_cart);
    }
  } else {
    tep_session_register('cart');
    $cart = new shoppingCart;
  }

// create the postpone shopping cart & fix the cart if necesary
  if (tep_session_is_registered('postpone_cart') && is_object($postpone_cart)) {
    if (PHP_VERSION < 4) {
      $broken_cart = $postpone_cart;
      $postpone_cart = new shoppingCart('postpone');
      $postpone_cart->unserialize($broken_cart);
    }
  } else {
    tep_session_register('postpone_cart');
    $postpone_cart = new shoppingCart('postpone');
  }

// create the foreign shopping cart & fix the cart if necesary
  if (tep_session_is_registered('foreign_cart') && is_object($foreign_cart)) {
    if (PHP_VERSION < 4) {
      $broken_cart = $foreign_cart;
      $foreign_cart = new shoppingCart('foreign');
      $foreign_cart->unserialize($broken_cart);
    }
  } else {
    tep_session_register('foreign_cart');
    $foreign_cart = new shoppingCart('foreign');
  }

// include currencies class and create an instance
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

// include the mail classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');
  require(DIR_WS_CLASSES . 'class.phpmailer.php');

  $lang_query = tep_db_query("select languages_id, code from " . TABLE_LANGUAGES . " where default_status = '1'");
  $lang = tep_db_fetch_array($lang_query);
  define('DEFAULT_LANGUAGE', $lang['code']);
  if (DOMAIN_ZONE=='org' || strpos(HTTP_SERVER, 'owl') || strpos(HTTP_SERVER, 'insell')) $default_language_id = 1;
  else $default_language_id = $lang['languages_id'];
  define('DEFAULT_LANGUAGE_ID', $default_language_id);

// set the language
  if (!tep_session_is_registered('language') || isset($HTTP_GET_VARS['language'])) {
    if (!tep_session_is_registered('language')) {
      tep_session_register('language');
      tep_session_register('languages_id');
    }

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

    if (isset($HTTP_GET_VARS['language']) && tep_not_null($HTTP_GET_VARS['language'])) {
      $lng->set_language($HTTP_GET_VARS['language']);
    } else {
#      $lng->get_browser_language();
	  $lng->set_language('');
    }

    $language = $lng->language['code'];
    $languages_id = $lng->language['id'];
  }
  if ($language=='') $language = DEFAULT_LANGUAGE;

// include the language translations
  if (DEFAULT_LANGUAGE_ID==1) {
	require(DIR_WS_LANGUAGES . 'en.php');
  } else {
	require(DIR_WS_LANGUAGES . $language . '.php');
  }

  $monthes_array = array('', TEXT_MONTH_JANUARY, TEXT_MONTH_FEBRUARY, TEXT_MONTH_MARCH, TEXT_MONTH_APRIL, TEXT_MONTH_MAY, TEXT_MONTH_JUNE, TEXT_MONTH_JULY, TEXT_MONTH_AUGUST, TEXT_MONTH_SEPTEMBER, TEXT_MONTH_OCTOBER, TEXT_MONTH_NOVEMBER, TEXT_MONTH_DECEMBER);

  $periodicity_array = array(array('id' => '3', 'text' => '3 '. TEXT_MONTHES), array('id' => '6', 'text' => '6 '. TEXT_MONTHES), array('id' => '12', 'text' => '12 '. TEXT_MONTHES));

// set the flash
  if (!tep_session_is_registered('popup')) {
	tep_session_register('popup');
	$popup = 'on';
  }

  if (isset($HTTP_GET_VARS['popup']) && ($HTTP_GET_VARS['popup']=='on' || $HTTP_GET_VARS['popup']=='off') ) {
	$popup = $HTTP_GET_VARS['popup'];
  }

  if (isset($HTTP_GET_VARS['sort']) && empty($HTTP_GET_VARS['sort'])) {
	if (tep_session_is_registered('sort')) {
	  $sort = '';
	  tep_session_unregister('sort');
	}
  }

  $content_id = '';
  $content_type = '';
  $use_page_template = false;

// currency
  if (!tep_session_is_registered('currency') || isset($HTTP_GET_VARS['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $currency) ) ) {
    if (!tep_session_is_registered('currency')) tep_session_register('currency');

    if (isset($HTTP_GET_VARS['currency'])) {
      if (!$currency = tep_currency_exists($HTTP_GET_VARS['currency'])) $currency = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    } else {
      $currency = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : $HTTP_GET_VARS['currency'];
    }
  }
  if (!in_array($currency, $available_currencies)) $currency = DEFAULT_CURRENCY;

// navigation history
  if (tep_session_is_registered('navigation')) {
    if (PHP_VERSION < 4) {
      $broken_navigation = $navigation;
      $navigation = new navigationHistory;
      $navigation->unserialize($broken_navigation);
    }
  } else {
    tep_session_register('navigation');
    $navigation = new navigationHistory;
  }
  if (is_object($navigation)) $navigation->add_current_page();

// include the who's online functions
//  require(DIR_WS_FUNCTIONS . 'whos_online.php');
//  tep_update_whos_online();

// include the password crypto functions
  require(DIR_WS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// infobox
  require(DIR_WS_CLASSES . 'boxes.php');

// auto activate and expire banners
  require(DIR_WS_FUNCTIONS . 'banner.php');
  tep_activate_banners();
  tep_expire_banners();

// auto expire special products
//  require(DIR_WS_FUNCTIONS . 'specials.php');
//  tep_expire_specials();

// include the breadcrumb class and start the breadcrumb trail
  require(DIR_WS_CLASSES . 'breadcrumb.php');
  $breadcrumb = new breadcrumb;

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

  $default_page_query = tep_db_query("select pages_name from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(FILENAME_DEFAULT) . "' and language_id = '" . (int)$languages_id . "'");
  $default_page = tep_db_fetch_array($default_page_query);

  $breadcrumb->add($default_page['pages_name'], tep_href_link(FILENAME_DEFAULT));

  if (substr($HTTP_GET_VARS['action'], 0, 11)=='desactivate' || substr($HTTP_GET_VARS['action'], 0, 8)=='activate') {
	list($product_action, $product_code, $product_type) = explode('_', $HTTP_GET_VARS['action']);

	if ($product_action=='desactivate') $product_new_status = 0;
	elseif ($product_action=='activate') $product_new_status = 1;
	else $product_new_status = '';

	if ( ($product_new_status==0 || $product_new_status==1) && (int)$product_code > 0) {
	  $product_code = 'bbk' . sprintf('%010d', (int)$product_code);
	  $product_check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_code = '" . tep_db_input($product_code) . "' and products_types_id = '" . (int)$product_type . "'");
	  $product_check = tep_db_fetch_array($product_check_query);
	  if ($product_check['products_id'] > 0) {
		$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
		while ($shops = tep_db_fetch_array($shops_query)) {
		  tep_db_select_db($shops['shops_database']);
		  tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '" . (int)$product_new_status . "' where products_id = '" . (int)$product_check['products_id'] . "'");
		  tep_db_query("update " . TABLE_PRODUCTS_INFO . " set products_status = '" . (int)$product_new_status . "' where products_id = '" . (int)$product_check['products_id'] . "'");
		  if (tep_db_table_exists($shops['shops_database'], 'temp_' . TABLE_PRODUCTS)) {
			tep_db_query("update temp_" . TABLE_PRODUCTS . " set products_status = '" . (int)$product_new_status . "' where products_id = '" . (int)$product_check['products_id'] . "'");
		  }
		  if (tep_db_table_exists($shops['shops_database'], 'temp_' . TABLE_PRODUCTS_INFO)) {
			tep_db_query("update temp_" . TABLE_PRODUCTS_INFO . " set products_status = '" . (int)$product_new_status . "' where products_id = '" . (int)$product_check['products_id'] . "'");
		  }
		}
		tep_db_select_db(DB_DATABASE);
//		tep_db_query("update " . TABLE_PRODUCTS_TYPES . " set products_last_modified = now() where products_types_id = '1'");
		die('OK');
	  }
	}
	die('FAIL');
  } elseif ($HTTP_GET_VARS['action']=='get_currency_value') {
	die($currencies->get_value($HTTP_GET_VARS['currency']));
  } elseif ($HTTP_GET_VARS['action']=='get_product_price') {
	$product_info_query = tep_db_query("select if(s.specials_new_products_price>'0', s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on (s.products_id = p.products_id and s.status = '1' and s.specials_new_products_price > 0 and s.language_id = '" . (int)$languages_id . "') where p.products_code = 'bbk" . sprintf('%010d', (int)$HTTP_GET_VARS['code']) . "' and p.products_types_id = '" . (int)$HTTP_GET_VARS['type'] . "'");
	$product_info = tep_db_fetch_array($product_info_query);
	die($product_info['products_price']);
  }

  $http_r = preg_replace('/^https?:\/\//i', '', str_replace('www.', '', $_SERVER['HTTP_REFERER']));
  $http_s = preg_replace('/^https?:\/\//i', '', str_replace('www.', '', HTTP_SERVER . DIR_WS_CATALOG));
  if ($HTTP_GET_VARS['link'] == 'mail') $allow_action = true;
  elseif (strpos($http_r, $http_s)!==false) $allow_action = true;
  elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox/1.5')!==false) $allow_action = true;
  else $allow_action = false;

  if (tep_session_is_registered('customer_id')) {
	$allow_action = true;
	$dummy_customers_query = tep_db_query("select 1 from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "' and customers_is_dummy_account = '1'");
	if (tep_db_num_rows($dummy_customers_query) > 0) {
	  tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_created = now() where customers_info_id = '" . (int)$customer_id . "'");
	  if (!tep_session_is_registered('is_dummy_account')) {
		$is_dummy_account = true;
		tep_session_register('is_dummy_account');
	  }
	} elseif (tep_session_is_registered('is_dummy_account')) {
	  $is_dummy_account = false;
	  tep_session_unregister('is_dummy_account');
	}
  }

  if (isset($HTTP_GET_VARS['action']) && $session_started == true && $allow_action == true) {
    if (DISPLAY_CART == 'true') {
      $goto = FILENAME_SHOPPING_CART;
      $parameters = array('action', 'cPath', 'cName', 'products_id', 'pid', 'type', 'to', 'from', 'cart_type', 'notify');
    } else {
      $goto = basename(SCRIPT_FILENAME);
      $parameters = array('action', 'quantity');
    }
	if ($HTTP_GET_VARS['short']=='1') {
      $goto = FILENAME_POPUP_SHOPPING_CART;
      $parameters = array('action', 'cPath', 'cName', 'products_id', 'pid', 'type', 'to', 'from', 'cart_type', 'notify');
	}
//	if ($goto==FILENAME_SHOPPING_CART || $goto==FILENAME_POPUP_SHOPPING_CART) $ssl_params = 'SSL';
//	else
	$ssl_params = 'NONSSL';
    switch ($HTTP_GET_VARS['action']) {
      // customer wants to update the product quantity in their shopping cart
      case 'update_product':
		$is_postpone = ($HTTP_GET_VARS['cart_type']=='postpone');
		$products_to_delete = $HTTP_POST_VARS['cart_delete'];
		if (!is_array($products_to_delete)) $products_to_delete = array();
		for ($i=0, $n=sizeof($HTTP_POST_VARS['products_id']); $i<$n; $i++) {
		  if (in_array($HTTP_POST_VARS['products_id'][$i], $products_to_delete) || (int)$HTTP_POST_VARS['cart_quantity'][$i] < 1) {
			if ($is_postpone) $postpone_cart->remove($HTTP_POST_VARS['products_id'][$i]);
			else $cart->remove($HTTP_POST_VARS['products_id'][$i]);
		  } else {
			if (PHP_VERSION < 4) {
			  // if PHP3, make correction for lack of multidimensional array.
			  reset($HTTP_POST_VARS);
			  while (list($key, $value) = each($HTTP_POST_VARS)) {
				if (is_array($value)) {
				  while (list($key2, $value2) = each($value)) {
					if (preg_match("/(.*)\]\[(.*)/", $key2, $var)) {
					  $id2[$var[1]][$var[2]] = $value2;
					}
				  }
				}
			  }
			}
			if ($is_postpone) $postpone_cart->add_cart($HTTP_POST_VARS['products_id'][$i], $HTTP_POST_VARS['cart_quantity'][$i], false);
			else $cart->add_cart($HTTP_POST_VARS['products_id'][$i], $HTTP_POST_VARS['cart_quantity'][$i], false);
		  }
		}

		tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters), $ssl_params) . ($is_postpone ? '#postpone' : ''));
		break;
      // customer move a product from shopping cart to postpone cart
	  case 'move_product':
		if ($HTTP_GET_VARS['to']=='postpone') {
		  $cart->remove($HTTP_GET_VARS['products_id']);
		  $postpone_cart->add_cart($HTTP_GET_VARS['products_id'], '1');
		} else {
		  $postpone_cart->remove($HTTP_GET_VARS['products_id']);
		  $cart->add_cart($HTTP_GET_VARS['products_id'], '1');
		}

		tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, tep_get_all_get_params($parameters)) . ($HTTP_GET_VARS['to']=='postpone' ? '#postpone' : ''));
		break;
      // customer remove a product from shopping cart or postpone cart
      case 'remove_product':
		if ($HTTP_GET_VARS['from']=='postpone') {
          $postpone_cart->remove($HTTP_GET_VARS['products_id']);
		} elseif ($HTTP_GET_VARS['from']=='foreign') {
          $foreign_cart->remove($HTTP_GET_VARS['products_id']);
		} else {
          $cart->remove($HTTP_GET_VARS['products_id']);
		}

		tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters), $ssl_params) . ($HTTP_GET_VARS['from']!='' ? '#' . $HTTP_GET_VARS['from'] : ''));
		break;
      // customer remove a product from shopping cart or postpone cart
      case 'notify':
		if (tep_session_is_registered('customer_id')) {
          $postpone_cart->change_notification($HTTP_GET_VARS['products_id'], $HTTP_GET_VARS['notify']);
		} else {
		  $messageStack->add_session('header', POSTPONE_CART_NOTIFICATION_ERROR);
		}
		tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters), $ssl_params) . '#postpone');
		break;
      // customer adds a product from the products page
      case 'add_product':
		if (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id') || $HTTP_GET_VARS['link'] == 'mail') {
		  if ((isset($HTTP_POST_VARS['products_id']) && is_numeric($HTTP_POST_VARS['products_id']))  || (isset($HTTP_GET_VARS['products_id']) && is_numeric($HTTP_GET_VARS['products_id']))) {
			$quantity = (int)$HTTP_POST_VARS['quantity'];

			if (isset($HTTP_GET_VARS['products_id'])) $products_id = $HTTP_GET_VARS['products_id'];
			else $products_id = $HTTP_POST_VARS['products_id'];

			if ($quantity < 1) $quantity = 1;

			if ($HTTP_GET_VARS['to']=='foreign') {
			  $foreign_cart->add_cart($products_id, 1);
			} elseif ($HTTP_GET_VARS['to']=='postpone') {
			  $postpone_cart->add_cart($products_id, 1);
			  $cart->remove($products_id);
			} else {
			  $quantity = $cart->get_quantity($products_id) + $quantity;
			  $cart->add_cart($products_id, $quantity);
			  $postpone_cart->remove($products_id);
			}
		  }

		  tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters), $ssl_params) . '#' . $HTTP_GET_VARS['to']);
		} else {
		  $messageStack->add_session('header', ENTRY_GUEST_ADD_TO_CART_ERROR);
		  tep_redirect($_SERVER['HTTP_REFERER']);
		}
		break;
      // customer reset a cart
      case 'reset_cart':
		if ($HTTP_GET_VARS['cart_type']=='postpone') $postpone_cart->reset(true);
		elseif ($HTTP_GET_VARS['cart_type']=='foreign') $foreign_cart->reset(true);
		else $cart->reset(true);

		tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters), $ssl_params));
		break;
	  case 'send':
		$search_array = array('content-transfer-encoding:', 'content-type:', 'to:', 'from:', 'bcc:', 'cc:', 'subject:');
		$error = false;
		reset($HTTP_POST_VARS);
		while (list($k, $v) = each($HTTP_POST_VARS)) {
		  $$k = tep_output_string_protected($v);
		  if ($k!='enquiry') $$k = preg_replace("/\s+/", ' ', $$k);
		}
		$email = implode('', array_map('trim', explode("\n", $email)));
		$name = implode('', array_map('trim', explode("\n", $name)));
		reset($search_array);
		while (list(, $search_word) = each($search_array)) {
		  $email = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $email);
		  $name = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $name);
		}
		$email = substr(preg_replace('/[^-@_a-z0-9\.]/i', '', $email), 0, 64);
		$name = substr(preg_replace('/[^-\sa-z0-9\.абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ]/i', '', $name), 0, 32);
		$email_enquiry = ENTRY_CONTACT_US_ENQUIRY . ' ' . $enquiry;
		if (tep_not_null($name)) $email_enquiry .= "\n\n" . ENTRY_CONTACT_US_NAME . ' ' . $name;
		if (tep_not_null($email)) $email_enquiry .= "\n\n" . ENTRY_CONTACT_US_EMAIL . ' ' . $email;
		if (tep_not_null($phone)) $email_enquiry .= "\n\n" . ENTRY_CONTACT_US_PHONE_NUMBER . ' ' . $phone;
		$email_enquiry .= "\n\n" . ENTRY_CONTACT_US_IP_ADDRESS . ' ' . tep_get_ip_address();

		$captcha_check = false;
		if ((int)$captcha==(int)$captcha_value) $captcha_check = true;

		$is_blacklisted = tep_check_blacklist();
		if ($is_blacklisted) {
		  $error = true;

		  $messageStack->add('header', strip_tags(ENTRY_BLACKLIST_CONTACT_US_ERROR));
		} elseif ($captcha_check==false) {
		  $error = true;

		  $messageStack->add('header', ENTRY_CAPTCHA_CHECK_ERROR);
		} elseif (!tep_validate_email($email)) {
		  $error = true;

		  $messageStack->add('header', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
		}

		if (!$error) {
		  $contact_us_subject = ENTRY_CONTACT_US_EMAIL_SUBJECT;
		  $contact_us_to_email = STORE_OWNER_EMAIL_ADDRESS;
		  if (isset($HTTP_POST_VARS['subject'])) {
			$subjects_check_query = tep_db_query("select count(*) as total from " . TABLE_SUBJECTS . "");
			$subjects_check = tep_db_fetch_array($subjects_check_query);
			if ($subjects_check['total'] > 0) {
			  $subject_info_query = tep_db_query("select subjects_name, subjects_email from " . TABLE_SUBJECTS . " where subjects_id = '" . (int)$HTTP_POST_VARS['subject'] . "' and language_id = '" . (int)$languages_id . "' and status = '1'");
			  if (tep_db_num_rows($subject_info_query) > 0) {
				$subject_info = tep_db_fetch_array($subject_info_query);
				if (tep_not_null($subject_info['subjects_name'])) $contact_us_subject .= ' [' . $subject_info['subjects_name'] . ']';
				if (tep_not_null($subject_info['subjects_email'])) $contact_us_to_email = $subject_info['subjects_email'];
			  }
			} else {
			  $contact_us_subject .= ' [' . $subject . ']';
			}
		  }
		  $contact_us_subject = STORE_NAME . ' - ' . $contact_us_subject;
		  tep_mail(STORE_NAME, $contact_us_to_email, $contact_us_subject, $email_enquiry, $name, $email);
		  $messageStack->add_session('header', ENTRY_CONTACT_US_SUCCESS, 'success');
		  tep_session_unregister('captcha_value');

		  $back_url = REQUEST_URI;
		  $back_url = str_replace('?action=send', '', $back_url);
		  $back_url = str_replace('&action=send', '', $back_url);
		  $back_url = str_replace('action=send', '', $back_url);
		  tep_redirect($back_url);
		}
		break;
	  case 'process_request':
	  case 'process_foreign_books':
	  case 'process_foreign_products':
		$search_array = array('content-transfer-encoding:', 'content-type:', 'to:', 'from:', 'bcc:', 'cc:', 'subject:');
		$error = false;

		reset($HTTP_POST_VARS);
		while (list($k, $v) = each($HTTP_POST_VARS)) {
		  $$k = tep_output_string_protected($v);
		}

		$name = $customer_name;
		$email = $customer_email;

		$email = implode('', array_map('trim', explode("\n", $email)));
		$name = implode('', array_map('trim', explode("\n", $name)));
		reset($search_array);
		while (list(, $search_word) = each($search_array)) {
		  $email = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $email);
		  $name = preg_replace('/' . preg_quote($search_word, '/') . '/i', '', $name);
		}
		$email = substr(preg_replace('/[^-@_a-z0-9\.]/i', '', $email), 0, 64);
		$name = substr(preg_replace('/[^-\sa-z0-9\.абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ]/i', '', $name), 0, 32);

		$is_blacklisted = tep_check_blacklist();
		if ($is_blacklisted) {
		  $error = true;

		  $messageStack->add('header', strip_tags(ENTRY_BLACKLIST_REQUEST_ERROR));
		} elseif (tep_validate_email($email)) {
		  $advance_order_date_purchased = date('Y-m-d H:i:s');
		  $advance_order_products = array();
		  $advance_order_sum = 0;
		  $enquiry = (($HTTP_GET_VARS['action']=='process_foreign_books' || $HTTP_GET_VARS['action']=='process_foreign_products') ? ENTRY_REQUEST_FORM_ADDRESS . ' ' . $customer_delivery_address . "\n\n" : '');
		  if (tep_not_null($name)) $enquiry .= ENTRY_REQUEST_FORM_NAME . ' ' . $name . "\n\n";
		  if (tep_not_null($email)) $enquiry .= ENTRY_REQUEST_FORM_EMAIL . ' ' . $email . "\n\n";
		  if (tep_not_null($customer_phone_number)) $enquiry .= ENTRY_REQUEST_FORM_PHONE_NUMBER . ' ' . $customer_phone_number . "\n\n";
		  if (tep_not_null($customer_comments)) $enquiry .= ENTRY_REQUEST_FORM_COMMENTS . ' ' . $customer_comments . "\n\n";
		  $order_currencies = array();
		  for ($i=0, $k=1; $i<15; $i++) {
			$temp_string = '';
			if (tep_not_null(${'title_' . $i})) {
			  $qty = (int)${'qty_' . $i};
			  if ($qty < 1) $qty = 1;
			  $product_check_query = tep_db_query("select products_id, products_name, products_price, products_currency from " . TABLE_FOREIGN_PRODUCTS . " where products_model_1 = '" . tep_db_input(preg_replace('/[^\d]/', '', ${'model_' . $i})) . "'");
			  $product_check = tep_db_fetch_array($product_check_query);
			  $temp_string .= ($HTTP_GET_VARS['action']=='process_foreign_products' ? ENTRY_REQUEST_FORM_PRODUCT_TITLE : ENTRY_REQUEST_FORM_BOOK_TITLE) . ' ' . ${'title_' . $i} . "\n";
			  if (tep_not_null(${'author_' . $i})) $temp_string .= ENTRY_REQUEST_FORM_PRODUCT_AUTHOR . ' ' . ${'author_' . $i} . "\n";
			  if (tep_not_null(${'code_' . $i})) $temp_string .= ENTRY_REQUEST_FORM_PRODUCT_CODE_SHORT . ' ' . ${'code_' . $i} . "\n";
			  if (tep_not_null(${'model_' . $i})) $temp_string .= ($HTTP_GET_VARS['action']=='process_foreign_products' ? ENTRY_REQUEST_FORM_PRODUCT_MODEL : ENTRY_REQUEST_FORM_BOOK_MODEL) . ' ' . ${'model_' . $i} . "\n";
			  if (tep_not_null(${'manufacturer_' . $i})) $temp_string .= ($HTTP_GET_VARS['action']=='process_foreign_products' ? ENTRY_REQUEST_FORM_PRODUCT_MANUFACTURER : ENTRY_REQUEST_FORM_BOOK_MANUFACTURER) . ' ' . ${'manufacturer_' . $i} . "\n";
			  if (tep_not_null(${'year_' . $i})) $temp_string .= ENTRY_REQUEST_FORM_PRODUCT_YEAR . ' ' . ${'year_' . $i} . "\n";
			  if (tep_not_null(${'url_' . $i})) $temp_string .= ENTRY_REQUEST_FORM_PRODUCT_URL_SHORT . ' ' . ${'url_' . $i} . "\n";
			  if (tep_not_null(${'price_' . $i})) $temp_string .= ($HTTP_GET_VARS['action']=='process_foreign_products' ? ENTRY_REQUEST_FORM_PRODUCT_PRICE : ENTRY_REQUEST_FORM_BOOK_PRICE) . ' ' . (tep_not_null(${'currency_' . $i}) ? $currencies->format(${'price_' . $i}, false, ${'currency_' . $i}) : ${'price_' . $i}) . "\n";
			  if ($HTTP_GET_VARS['action']=='process_foreign_books' || $HTTP_GET_VARS['action']=='process_foreign_products') $temp_string .= ENTRY_REQUEST_FORM_PRODUCT_QTY . ' ' . $qty . "\n";
			  if ((int)$product_check['products_id'] > 0) {
				$temp_string .= sprintf(ENTRY_REQUEST_FORM_PRODUCT_EXISTS, $product_check['products_name'], $currencies->format($product_check['products_price'], true, $product_check['products_currency']));
				if ($product_check['products_currency']!=${'currency_' . $i}) $temp_string .= ' (' . $currencies->format($product_check['products_price'], true, ${'currency_' . $i}) . ')';
				$temp_string .= "\n";
			  }
			  $temp_string .= "\n";
			  if (tep_not_null(trim($temp_string))) {
				$enquiry .= $k . '. ' . $temp_string;
				$k ++;
				if ($HTTP_GET_VARS['action']=='process_foreign_books' || $HTTP_GET_VARS['action']=='process_foreign_products') {
				  $price = str_replace(',', '.', ${'price_' . $i});
				  $price = tep_round($price/$currencies->currencies[${'currency_' . $i}]['value'], 2);
				  $advance_order_products[] = array('products_id' => '0',
													'products_name' => ${'title_' . $i},
													'products_author' => ${'author_' . $i},
													'products_model' => ${'model_' . $i},
													'products_manufacturer' => ${'manufacturer_' . $i},
													'products_year' => (int)${'year_' . $i},
													'products_url' => ${'url_' . $i},
													'products_price' => $price,
													'currency' => ${'currency_' . $i},
													'currency_value' => $currencies->currencies[${'currency_' . $i}]['value'],
													'products_quantity' => $qty);
				  $advance_order_sum += $price * $qty;
				  $order_currencies[${'currency_' . $i}] ++;
				}
			  }
			}
		  }

		  arsort($order_currencies);
		  list($order_currency) = each($order_currencies);

		  $enquiry = trim($enquiry);
		  if ($HTTP_GET_VARS['action']=='process_foreign_books' || $HTTP_GET_VARS['action']=='process_foreign_products') {
			if (empty($customer_delivery_address) || empty($name) || empty($email)) {
			  $enquiry = '';
			} else {
			  $sql_data_array = array('customers_id' => $customer_id,
									  'customers_name' => $name,
									  'customers_email_address' => $email,
									  'customers_telephone' => $customer_phone_number,
									  'customers_address' => $customer_delivery_address,
									  'date_purchased' => $advance_order_date_purchased,
									  'comments' => $customer_comments,
									  'currency' => $order_currency,
									  'currency_value' => $currencies->get_value($order_currency),
									  'shops_id' => (int)SHOP_ID);
									  
			  tep_db_perform(TABLE_ADVANCE_ORDERS, $sql_data_array);
			  $advance_orders_id = tep_db_insert_id();

			  reset($advance_order_products);
			  while (list($i, $advance_order_product) = each($advance_order_products)) {
				$advance_order_product['advance_orders_id'] = $advance_orders_id;
				tep_db_perform(TABLE_ADVANCE_ORDERS_PRODUCTS, $advance_order_product);
				$advance_order_products[$i]['order_product_id'] = tep_db_insert_id();
			  }

			  $date_purchased = preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '$3-$2-$1 $4:$5:$6', $advance_order_date_purchased);
			  $date_purchased = preg_replace('/\s+/', ' ', $date_purchased);
			  $order_file = UPLOAD_DIR . 'orders1/' . substr(DOMAIN_ZONE . 'aa', 0, 4) . $advance_orders_id . '.csv';
			  $fp = fopen($order_file, 'w');
			  $common_data = array(SHOP_ID, $advance_orders_id, $date_purchased, $customer_delivery_address, $customer_phone_number, $name, $email, $order_currency, $currencies->get_value($order_currency), $customer_comments);
			  fputcsvsafe($fp, $common_data, ',');

			  reset($advance_order_products);
			  while (list(, $product) = each($advance_order_products)) {
				$common_data = array();
				$common_data[] = ($HTTP_GET_VARS['action']=='process_foreign_books' ? '1' : '2'); // тип товара
				$common_data[] = $product['products_model'];
				$common_data[] = $product['order_product_id'];
				$common_data[] = $product['products_name'];
				$common_data[] = $product['products_author'];
				$common_data[] = $product['products_manufacturer'];
				$common_data[] = $product['products_quantity'];
				$common_data[] = $product['products_price'];
				$common_data[] = $product['products_url'];
				fputcsvsafe($fp, $common_data, ',');
			  }
			  fclose($fp);
			}
		  }

		  if ($HTTP_GET_VARS['action']=='process_foreign_products') $email_subject = sprintf(ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_PRODUCTS, $advance_orders_id);
		  elseif ($HTTP_GET_VARS['action']=='process_foreign_books') $email_subject = sprintf(ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_BOOKS, $advance_orders_id);
		  else $email_subject = ENTRY_REQUEST_FORM_EMAIL_SUBJECT;

		  if (tep_not_null($enquiry)) {
			tep_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $enquiry, $name, $email);
			if (($HTTP_GET_VARS['action']=='process_foreign_books' || $HTTP_GET_VARS['action']=='process_foreign_products') && defined('SEND_ADVANCE_ORDER_EMAILS_TO') && tep_not_null(SEND_ADVANCE_ORDER_EMAILS_TO)) {
			  tep_mail('', SEND_ADVANCE_ORDER_EMAILS_TO, $email_subject, $enquiry, $name, $email);
			}
			$messageStack->add_session('header', ENTRY_REQUEST_FORM_SUCCESS, 'success');

			if ($HTTP_GET_VARS['action']=='process_foreign_books') {
			  $foreign_cart->reset(true);
			}

			tep_redirect(str_replace('action=' . $HTTP_GET_VARS['action'], 'action=success', REQUEST_URI));
		  } else {
			$error = true;

			$messageStack->add('header', ENTRY_REQUEST_FORM_ERROR);
		  }
		} else {
		  $error = true;

		  $messageStack->add('header', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
		}
		break;
	  case 'corporate_price':
		if (tep_session_is_registered('customer_id')) {
		  $fields = array();
		  for ($i=0; $i<100; $i++) {
			if (tep_not_null($HTTP_POST_VARS['field_' . $i])) $fields[] = tep_db_prepare_input($HTTP_POST_VARS['field_' . $i]);
		  }
		  $fields[] = 'products_quantity';
		  $categories = array();
		  $categories_count_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where products_types_id = '1' and parent_id = '0'");
		  $categories_count = tep_db_fetch_array($categories_count_query);
		  for ($i=0; $i<$categories_count['total']; $i++) {
			if (tep_not_null($HTTP_POST_VARS['category_' . $i])) $categories[] = tep_string_to_int($HTTP_POST_VARS['category_' . $i]);
		  }
		  $manufacturers = (tep_not_null($HTTP_POST_VARS['manufacturer']) ? array_map('tep_db_prepare_input', explode("\n", trim($HTTP_POST_VARS['manufacturer']))) : array());
		  $specials = array();
		  $specials_count_query = tep_db_query("select count(*) as total from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1'");
		  $specials_count = tep_db_fetch_array($specials_count_query);
		  for ($i=0; $i<$specials_count['total']; $i++) {
			if (tep_not_null($HTTP_POST_VARS['special_' . $i])) $specials[] = tep_string_to_int($HTTP_POST_VARS['special_' . $i]);
		  }
		  $status = $HTTP_POST_VARS['status'];
		  $products_to_load = array();
		  $subcategories_products = array();
		  $manufacturers_products = array();
		  $specials_products = array();
		  $products_selected = false;
		  $subcategories_array = array();
		  $eval_string = '';
		  if (sizeof($categories) > 0) {
			reset($categories);
			while (list(, $category_id) = each($categories)) {
			  $subcategories_array[] = $category_id;
			  tep_get_subcategories($subcategories_array, $category_id);
			}
			$eval_string .= '$subcategories_products, ';
		  }
		  $manufacturers_array = array();
		  if (sizeof($manufacturers) > 0) {
			reset($manufacturers);
			while (list(, $manufacturer_name) = each($manufacturers)) {
			  $manufacturer_name = trim(preg_replace('/\s+/', ' ', $manufacturer_name));
			  if (tep_not_null($manufacturer_name)) {
				$manufacturer_info_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where (manufacturers_name like '%" . str_replace(' ', "%' and manufacturers_name like '%", $manufacturer_name) . "%') and languages_id = '" . (int)$languages_id . "'");
				while ($manufacturer_info = tep_db_fetch_array($manufacturer_info_query)) {
				  $manufacturers_array[] = $manufacturer_info['manufacturers_id'];
				}
			  }
			}
			$eval_string .= '$manufacturers_products, ';
		  }
		  if (sizeof($subcategories_array) > 0) {
			$query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id in ('" . implode("', '", $subcategories_array) . "')");
			while ($row = tep_db_fetch_array($query)) {
			  $subcategories_products[] = $row['products_id'];
			}
		  }
		  if (sizeof($manufacturers_array) > 0) {
			$query = tep_db_query("select distinct products_id from " . TABLE_PRODUCTS . " where manufacturers_id in ('" . implode("', '", $manufacturers_array) . "')");
			while ($row = tep_db_fetch_array($query)) {
			  $manufacturers_products[] = $row['products_id'];
			}
		  }
		  if (sizeof($specials) > 0) {
			$query = tep_db_query("select distinct products_id from " . TABLE_SPECIALS . " where specials_types_id in ('" . implode("', '", $specials) . "')");
			while ($row = tep_db_fetch_array($query)) {
			  $specials_products[] = $row['products_id'];
			}
			$eval_string .= '$specials_products, ';
		  }
		  if (tep_not_null($eval_string)) {
			$products_selected = true;
			$eval_string = substr($eval_string, 0, -2);
			if (substr_count($eval_string, ',') > 0) eval('$products_to_load = array_intersect(' . $eval_string . ');');
			else eval('$products_to_load = ' . $eval_string . ';');
		  }
		  if (!$products_selected) {
			$messageStack->add('header', ENTRY_CORPORATE_FORM_PRODUCTS_CHOICE_ERROR);
		  } elseif (sizeof($products_to_load)==0) {
			$messageStack->add('header', ENTRY_CORPORATE_FORM_PRODUCTS_FOUND_ERROR);
		  } else {
			$select_string  = "select distinct p.products_id from " . TABLE_PRODUCTS . " p where p.products_status = '1'" . ($status=='all' ? "" : " and p.products_listing_status = '1'") . " and p.products_id in ('" . implode("', '", $products_to_load) . "')";
			$query = tep_db_query($select_string);

			header('Expires: Mon, 26 Nov 1962 00:00:00 GMT');
			header('Last-Modified: ' . gmdate('D,d M Y H:i:s') . ' GMT');
			header('Pragma: no-cache');
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-disposition: attachment; filename=price-' . date('Y-m-d') . '.csv');

			$out = fopen('php://output', 'w');
			reset($fields);
			$temp_array = array();
			while (list(, $field_id) = each($fields)) {
			  switch ($field_id) {
				case 'products_model':
				  $temp_array[] = TEXT_MODEL;
				  break;
				case 'products_name':
				  $temp_array[] = TEXT_NAME;
				  break;
				case 'authors_name':
				  $temp_array[] = TEXT_AUTHOR;
				  break;
				case 'products_price':
				  $temp_array[] = TEXT_PRICE;
				  break;
				case 'manufacturers_name':
				  $temp_array[] = TEXT_MANUFACTURER;
				  break;
				case 'series_name':
				  $temp_array[] = TEXT_SERIE;
				  break;
				case 'products_pages_count':
				  $temp_array[] = TEXT_PAGES_COUNT;
				  break;
				case 'products_year':
				  $temp_array[] = TEXT_YEAR_FULL;
				  break;
				case 'products_copies':
				  $temp_array[] = TEXT_COPIES;
				  break;
				case 'products_covers_name':
				  $temp_array[] = TEXT_COVER;
				  break;
				case 'products_formats_name':
				  $temp_array[] = TEXT_FORMAT;
				  break;
				case 'products_url':
				  $temp_array[] = TEXT_URL;
				  break;
				case 'products_quantity':
				  $temp_array[] = TEXT_QTY;
				  break;
			  }
			}
			fputcsvsafe($out, $temp_array, ";");
			while ($row = tep_db_fetch_array($query)) {
			  $product_info_query = tep_db_query("select p.*, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . $row['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
			  $product_info = tep_db_fetch_array($product_info_query);
			  reset($fields);
			  $temp_array = array();
			  while (list(, $field_id) = each($fields)) {
				switch ($field_id) {
				  case 'products_model':
					$temp_array[] = $product_info['products_model'];
					break;
				  case 'products_name':
					$temp_array[] = html_entity_decode($product_info['products_name'], ENT_QUOTES);
					break;
				  case 'authors_name':
					$author_info_query = tep_db_query("select authors_name from " . TABLE_AUTHORS . " where authors_id = '" . $product_info['authors_id'] . "' and language_id = '" . (int)$languages_id . "'");
					$author_info = tep_db_fetch_array($author_info_query);
					$temp_array[] = html_entity_decode($author_info['authors_name'], ENT_QUOTES);
					break;
				  case 'products_price':
					$temp_array[] = tep_round($product_info['products_price']*$currencies->currencies[$currency]['value'], $currencies->currencies[$currency]['decimal_places']);
					break;
				  case 'manufacturers_name':
					$manufacturer_info_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . $product_info['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
					$manufacturer_info = tep_db_fetch_array($manufacturer_info_query);
					$temp_array[] = html_entity_decode($manufacturer_info['manufacturers_name'], ENT_QUOTES);
					break;
				  case 'series_name':
					$serie_info_query = tep_db_query("select series_name from " . TABLE_SERIES . " where series_id = '" . $product_info['series_id'] . "' and language_id = '" . (int)$languages_id . "'");
					$serie_info = tep_db_fetch_array($serie_info_query);
					$temp_array[] = html_entity_decode($serie_info['series_name'], ENT_QUOTES);
					break;
				  case 'products_pages_count':
					$temp_array[] = ((int)$product_info['products_pages_count'] > 0 ? (int)$product_info['products_pages_count'] : '');
					break;
				  case 'products_year':
					$temp_array[] = ((int)$product_info['products_year'] > 0 ? (int)$product_info['products_year'] : '');
					break;
				  case 'products_copies':
					$temp_array[] = ((int)$product_info['products_copies'] > 0 ? (int)$product_info['products_copies'] : '');
					break;
				  case 'products_covers_name':
					$products_cover_info_query = tep_db_query("select products_covers_name from " . TABLE_PRODUCTS_COVERS . " where products_covers_id = '" . $product_info['products_covers_id'] . "' and language_id = '" . (int)$languages_id . "'");
					$products_cover_info = tep_db_fetch_array($products_cover_info_query);
					$temp_array[] = html_entity_decode($products_cover_info['products_covers_name'], ENT_QUOTES);
					break;
				  case 'products_formats_name':
					$products_format_info_query = tep_db_query("select products_formats_name from " . TABLE_PRODUCTS_FORMATS . " where products_formats_id = '" . $product_info['products_formats_id'] . "' and language_id = '" . (int)$languages_id . "'");
					$products_format_info = tep_db_fetch_array($products_format_info_query);
					$temp_array[] = html_entity_decode($products_format_info['products_formats_name'], ENT_QUOTES);
					break;
				  case 'products_url':
					$temp_array[] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id'], 'NONSSL', false);
					break;
				  case 'products_quantity':
					$temp_array[] = '';
					break;
				}
			  }
			  fputcsvsafe($out, $temp_array, ";");
			}
			fclose($out);
			tep_exit();
		  }
		} else {
		  $messageStack->add('header', strip_tags(ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED));
		}
		break;
	  case 'corporate_order':
		if (tep_session_is_registered('customer_id')) {
		  $cells = array();
		  $file_cells = array();
		  $error = false;
		  if (is_uploaded_file($_FILES['corporate_file']['tmp_name'])) {
			$ext = strtolower(substr($_FILES['corporate_file']['name'], strrpos($_FILES['corporate_file']['name'], '.')+1));
			if ( ($_FILES['corporate_file']['type']=='text/plain' && ($ext=='csv' || $ext=='txt') ) || ($_FILES['corporate_file']['type']=='application/vnd.ms-excel' && ($ext=='csv' || $ext=='xls') ) ) {
			  if ($_FILES['corporate_file']['type']=='application/vnd.ms-excel' && $ext=='xls') {
				require(DIR_WS_CLASSES . 'excel.php');
				$data = new excel;
				$data->setOutputEncoding('cp1251');
				$data->read($corporate_file);

				$cells = $data->sheets[0]['cells'];
			  } else {
				$file_contents_array = file($_FILES['corporate_file']['tmp_name']);
				$some_string = $file_contents_array[0];
				if (strpos($some_string, "\t")!==false) $delimiter = "\t";
				else $delimiter = ";";
				$cells = array();
				$fp = fopen($_FILES['corporate_file']['tmp_name'], 'r');
				while (($cell = fgetcsv($fp, 10000, $delimiter)) !== FALSE) {
				  $cells[] = array_merge(array(''), $cell);
				}
				fclose($fp);
			  }
			  $field_model = (int)trim($HTTP_POST_VARS['model_no']);
			  $field_qty = (int)trim($HTTP_POST_VARS['qty_no']);
			  reset($cells);
			  while (list(, $cell) = each($cells)) {
				$model = str_replace('Х', 'X', trim($cell[$field_model]));
				if (preg_match('/^[-\dx]{5,}$/i', $model)) {
				  $qty = (int)trim($cell[$field_qty]);
				  if ($qty > 0) $file_cells[$model] += $qty;
				}
			  }
			} else {
			  $error = true;
			  $messageStack->add('header', ENTRY_CORPORATE_FORM_UNKNOWN_FILE_UPLOADED_ERROR);
			}
		  } elseif (tep_not_null($HTTP_POST_VARS['corporate_text'])) {
			$ar = explode("\n", $HTTP_POST_VARS['corporate_text']);
			reset($ar);
			while (list(, $cell) = each($ar)) {
			  $cell = preg_replace('/\s+/', ' ', trim($cell));
			  list($model, $qty) = explode(' ', $cell);
			  $model = str_replace('Х', 'X', trim($model));
			  $qty = (int)trim($qty);
			  if ($qty < 1) $qty = 1;
			  if (tep_not_null($model)) {
				$file_cells[$model] += $qty;
			  }
			}
		  } else {
			$error = true;
		  }
		  if (!$error) {
			$total = 0;
			$added = 0;
			$added_total = 0;
			$skipped = 0;
			$not_found = 0;
			$absent = tep_db_prepare_input($HTTP_POST_VARS['absent']);
			$not_found_array = array();
			reset($file_cells);
			while (list($model, $qty) = each($file_cells)) {
			  if (preg_match('/^[-\dx]{5,}$/i', $model)) {
				$model_1 = preg_replace('/[^\d]/', '', $model);
				$total ++;
				$product_check_query = tep_db_query("select products_id, products_status, products_listing_status from " . TABLE_PRODUCTS . " where products_model_1 = '" . tep_db_input($model_1) . "'");
				$product_check = tep_db_fetch_array($product_check_query);
				if (!is_array($product_check)) $product_check = array();
				$products_id = $product_check['products_id'];
				$products_status = $product_check['products_status'];
				$listing_status = $product_check['products_listing_status'];
				if ($products_id > 0 && $products_status=='1') {
				  if ($listing_status=='0') {
					if ($absent=='postpone') {
					  $postpone_cart->add_cart($products_id);
					  $cart->remove($products_id);
					}
					$skipped ++;
				  } elseif ($listing_status=='1') {
					$cart->add_cart($products_id, abs($qty));
					$postpone_cart->remove($products_id);
					$added ++;
					$added_total += abs($qty);
				  }
				} else {
				  $not_found_array[] = $model;
				  $not_found ++;
				}
			  }
			}
			if ($total==0) {
			  $messageStack->add('header', ENTRY_CORPORATE_FORM_NO_MODELS_ERROR);
			} else {
			  if ($absent=='postpone') $message = sprintf(ENTRY_CORPORATE_FORM_SUCCESS_POSTPONE, $total, $added, $added_total, $skipped, $not_found . ($not_found>0 ? ' (' . implode(', ', $not_found_array) . ')' : ''));
			  else $message = sprintf(ENTRY_CORPORATE_FORM_SUCCESS_SKIP, $total, $added, $added_total, $skipped, $not_found . ($not_found>0 ? ' (' . implode(', ', $not_found_array) . ')' : ''));
			  $messageStack->add_session('header', $message, 'success');

			  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
			}
		  } else {
			$messageStack->add('header', ENTRY_CORPORATE_FORM_NO_DATA_UPLOADED_ERROR);
		  }
		} else {
		  $messageStack->add('header', strip_tags(ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED));
		}
		break;
	  case 'vote':
		$error = false;
		$vote = tep_db_prepare_input($HTTP_GET_VARS['vote']);
		$review_text = tep_output_string_protected($HTTP_POST_VARS['review_text']);
		$customers_name = tep_output_string_protected($HTTP_POST_VARS['review_name']);
		$customers_email = tep_output_string_protected($HTTP_POST_VARS['review_email']);
		$review_rating = tep_output_string_protected($HTTP_POST_VARS['review_rating']);
		$captcha = tep_output_string_protected($HTTP_POST_VARS['captcha']);
		$customers_id = 0;
		$remote_addr = tep_get_ip_address();
		if (tep_session_is_registered('customer_id') && !$is_dummy_account) $customers_id = $customer_id;
		$blacklist_check_query = tep_db_query("select count(*) as total from " . TABLE_BLACKLIST . " where blacklist_ip = '" . tep_db_input($remote_addr) . "'" . ($customers_id>0 ? " or customers_id = '" . (int)$customers_id . "'" : "") . "");
		$blacklist_check = tep_db_fetch_array($blacklist_check_query);
		if ($blacklist_check['total'] > 0) {
		  $messageStack->add('header', ENTRY_BLACKLIST_REVIEW_ERROR);
		} elseif ((int)$vote >= 1 && (int)$vote <= 5) {
		  $votes_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and reviews_types_id = '0' and reviews_ip = '" . tep_db_input($remote_addr) . "' and reviews_agent = '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "' and date_added > '" . date('Y-m-d H:i:s', (time()-60*60*24)) . "'");
		  $votes_check = tep_db_fetch_array($votes_check_query);
		  if ($votes_check['total'] < 1) {
			tep_db_query("insert into " . TABLE_REVIEWS . " (reviews_types_id, products_id, customers_id, reviews_vote, date_added, reviews_ip, reviews_agent, shops_id) values ('0', '" . (int)$HTTP_GET_VARS['products_id'] . "', '" . (int)$customers_id . "', '" . (int)$vote . "', now(), '" . tep_db_input($remote_addr) . "', '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "', '" . (int)SHOP_ID . "')");
			tep_db_query("update " . TABLE_PRODUCTS . " set products_rating = (select sum(reviews_vote)/count(*) from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "') where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");

			$messageStack->add_session('header', TEXT_REVIEW_SUCCESS_VOTED, 'success');
		  }
		} elseif (tep_not_null($review_text) || tep_not_null($customers_name) || tep_not_null($customers_email)) {
		  $votes_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and reviews_types_id = '1' and reviews_ip = '" . tep_db_input($remote_addr) . "' and reviews_agent = '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "' and date_added > '" . date('Y-m-d H:i:s', (time()-60*60*24)) . "'");
		  $votes_check = tep_db_fetch_array($votes_check_query);
		  if ($votes_check['total'] < 1) {
			$captcha_check = false;
			if ((int)$captcha==(int)$captcha_value) $captcha_check = true;

			if ($captcha_check==false) {
			  $error = true;
			  $messageStack->add('header', ENTRY_CAPTCHA_CHECK_ERROR);
			} elseif (empty($customers_name)) {
			  $error = true;
			  $messageStack->add('header', ENTRY_REVIEW_NAME_ERROR);
			} elseif (empty($customers_email)) {
			  $error = true;
			  $messageStack->add('header', ENTRY_REVIEW_EMAIL_ERROR);
			} elseif (REVIEW_TEXT_MIN_LENGTH > 0 && mb_strlen($review_text, 'CP1251') < REVIEW_TEXT_MIN_LENGTH) {
			  $error = true;
			  $messageStack->add('header', ENTRY_REVIEW_TEXT_ERROR);
			} elseif (tep_validate_email($customers_email)==false) {
			  $error = true;
			  $messageStack->add('header', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
			} else {
			  $reviews_status = 1;

			  tep_db_query("insert into " . TABLE_REVIEWS . " (reviews_types_id, products_id, customers_id, reviews_vote, customers_name, customers_email, reviews_text, date_added, reviews_ip, reviews_agent, shops_id, reviews_status) values ('1', '" . (int)$HTTP_GET_VARS['products_id'] . "', '" . (int)$customers_id . "', '" . (int)$review_rating . "', '" . tep_db_input($customers_name) . "', '" . tep_db_input($customers_email) . "', '" . tep_db_input($review_text) . "', now(), '" . tep_db_input($remote_addr) . "', '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "', '" . (int)SHOP_ID . "', '" . (int)$reviews_status . "')");
			if ($reviews_status > 0) tep_db_query("update " . TABLE_PRODUCTS . " set products_rating = (select sum(reviews_vote)/count(*) from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and reviews_status = '1') where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");

			  $messageStack->add_session('header', TEXT_REVIEW_SUCCESS_ADDED, 'success');
			  tep_session_unregister('captcha_value');
			}
		  }
		}

		if (!$error) tep_redirect(PHP_SELF);
		break;
    }
  }

  if (!tep_session_is_registered('customer_id') && isset($_COOKIE['remember_customer'])) {
	list($cookie_customer_password, $cookie_customer_id) = explode('||', $_COOKIE['remember_customer']);
    $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_lastname, customers_password, customers_email_address, customers_default_address_id, customers_type from " . TABLE_CUSTOMERS . " where customers_password = '" . tep_db_input(tep_db_prepare_input($cookie_customer_password)) . "' and customers_id = '" . (int)$cookie_customer_id . "'");
	if (tep_db_num_rows($check_customer_query) > 0) {
	  $check_customer = tep_db_fetch_array($check_customer_query);

	  $check_country_query = tep_db_query("(select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ")) union (select address_book_id, entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id <> '" . (int)$check_customer['customers_default_address_id'] . "' and entry_country_id in (select countries_id from " . TABLE_COUNTRIES . ") order by address_book_id desc) order by '" . (int)$check_customer['customers_default_address_id'] . "'");
	  $check_country = tep_db_fetch_array($check_country_query);

	  $customer_id = $check_customer['customers_id'];
	  $customer_type = $check_customer['customers_type'];
	  if (ACCOUNT_MIDDLE_NAME == 'true') {
		list($customer_first_name, $customer_middle_name) = explode(' ', $check_customer['customers_firstname']);
	  } else {
		$customer_first_name = $check_customer['customers_firstname'];
		$customer_middle_name = '';
	  }
	  $customer_last_name = $check_customer['customers_lastname'];
	  $customer_default_address_id = $check_country['address_book_id'];
	  $customer_country_id = $check_country['entry_country_id'];
	  $customer_zone_id = $check_country['entry_zone_id'];
	  tep_session_register('customer_id');
	  tep_session_register('customer_type');
	  tep_session_register('customer_default_address_id');
	  tep_session_register('customer_first_name');
	  tep_session_register('customer_middle_name');
	  tep_session_register('customer_last_name');
	  tep_session_register('customer_country_id');
	  tep_session_register('customer_zone_id');

// restore cart contents
	  $cart->restore_contents();

// restore postpone cart contents
	  $postpone_cart->restore_contents();

// restore foreign cart contents
	  $foreign_cart->restore_contents();
	}
  }

// calculate information path
  if (isset($HTTP_GET_VARS['sName'])) {
    $sName = $HTTP_GET_VARS['sName'];
	if (substr($sName, -5) == '.html') {
	  $iName = end(explode('/', $sName));
	  $sName = str_replace($iName, '', $sName);
	  $iName = substr($iName, 0, -5);
	}
	if (substr($sName, -1) == '/') $sName = substr($sName, 0, -1);
	if (substr($sName, 0, 1) == '/') $sName = substr($sName, 1);
	$sPath = '';
	if (tep_not_null($sName)) {
	  $parent_id = '0';
	  $sIds = explode('/', $sName);
	  reset($sIds);
	  while (list(, $sId) = each($sIds)) {
		$section_info_query = tep_db_query("select sections_id from " . TABLE_SECTIONS . " where sections_path = '" . tep_db_input($sId) . "' and parent_id = '" . (int)$parent_id . "' limit 1");
		if (tep_db_num_rows($section_info_query) < 1) {
		  tep_redirect(tep_href_link(FILENAME_ERROR_404));
		}
		$section_info = tep_db_fetch_array($section_info_query);
		$sPath .= (tep_not_null($sPath) ? '_' : '') . $section_info['sections_id'];
		$parent_id = $section_info['sections_id'];
	  }
	}
	unset($HTTP_GET_VARS['sName']);
  }

  if (tep_not_null($sPath)) {
    $sPath_array = explode('_', $sPath);
    $current_section_id = $sPath_array[(sizeof($sPath_array)-1)];
  } else {
    $current_section_id = 0;
  }

// add information sections names to the breadcrumb trail
  if (isset($sPath_array)) {
	for ($i=0, $n=sizeof($sPath_array); $i<$n; $i++) {
	  $section_info_query = tep_db_query("select sections_name from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sPath_array[$i] . "' and language_id = '" . (int)$languages_id . "'");
	  if (tep_db_num_rows($section_info_query) > 0) {
		$section_info = tep_db_fetch_array($section_info_query);
		$breadcrumb->add($section_info['sections_name'], tep_href_link(FILENAME_DEFAULT, 'sPath=' . implode('_', array_slice($sPath_array, 0, ($i+1)))));
	  } else {
		break;
      }
	}
  }
  if (tep_not_null($iName)) {
	$information_sql = "select i.information_id from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i2s.sections_id = '" . (int)$current_section_id . "' and i.information_status = '1' and i.language_id = '" . (int)$languages_id . "' and i.information_path = '" . tep_db_input($iName) . "' limit 1";
  } else {
	$information_sql = "select i.information_id from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_status = '1' and i.information_id = i2s.information_id and i2s.information_default_status = '1' and i2s.sections_id = '" . (int)$current_section_id . "' and i.language_id = '" . (int)$languages_id . "'";
  }
  $information_query = tep_db_query($information_sql);
  if (tep_db_num_rows($information_query) < 1) {
	tep_redirect(tep_href_link(FILENAME_ERROR_404));
  }
  $information = tep_db_fetch_array($information_query);
  $current_information_id = $information['information_id'];

// calculate category path
  if (isset($HTTP_GET_VARS['cName'])) {
    $cName = $HTTP_GET_VARS['cName'];
	$pName = '';
	if (substr($cName, -5) == '.html') {
	  $pName = end(explode('/', $cName));
	  $cName = str_replace($pName, '', $cName);
	  $pName = substr($pName, 0, -5);
	}
	while (substr($cName, -1) == '/') {
	  $cName = substr($cName, 0, -1);
	}
	$cPath = '';
	$category_not_found = false;
	$parent_id = '0';
	$cIds = explode('/', $cName);
	$manufacturer_found = false;
	if (sizeof($cIds) > 0) {
	  reset($cIds);
	  while (list(, $cId) = each($cIds)) {
		$category_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_path = '" . tep_db_input($cId) . "' and parent_id = '" . (int)$parent_id . "' limit 1");
		$category = tep_db_fetch_array($category_query);
		if (tep_not_null($category['categories_id'])) {
		  $cPath .= (tep_not_null($cPath) ? '_' : '') . $category['categories_id'];
		} else {
		  $category_not_found = true;
		  break;
		}
		$parent_id = $category['categories_id'];
	  }
	  if ($category_not_found && tep_not_null($cName)) {
		tep_redirect(tep_href_link(FILENAME_ERROR_404));
//		$category_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_path = '" . tep_db_input(end(explode('/', $cName))) . "' limit 1");
//		$category = tep_db_fetch_array($category_query);
//		$parents = array($category['categories_id']);
//		tep_get_parents($parents, $category['categories_id']);
//		$cPath = implode('_', array_reverse($parents));
	  }
	  $HTTP_GET_VARS['cPath'] = $cPath;
	}
	unset($HTTP_GET_VARS['cName']);
  }

  if (isset($HTTP_GET_VARS['cPath']) && tep_not_null($HTTP_GET_VARS['cPath'])) {
    $cPath = $HTTP_GET_VARS['cPath'];
  } elseif (isset($HTTP_GET_VARS['products_id']) && !isset($HTTP_GET_VARS['manufacturers_id'])) {
    $cPath = tep_get_product_path($HTTP_GET_VARS['products_id']);
  } elseif (tep_not_null($HTTP_GET_VARS['categories_id'])) {
	$cPath = $HTTP_GET_VARS['categories_id'];
  } else {
    $cPath = '';
  }

  if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

  if ($session_started == true) {
	if (sizeof($messageStack->messages)==0) {
	  tep_db_query("update " . TABLE_MESSAGES . " set status = '0' where expires_date <= '" . date('Y-m-d') . "'");

	  $messages_query = tep_db_query("select * from " . TABLE_MESSAGES . " where status = '1' order by sort_order");
	  while ($messages = tep_db_fetch_array($messages_query)) {
		$show_message = false;
		$messages['messages_pages'] = trim($messages['messages_pages']);
		if (tep_not_null($messages['messages_pages'])) {
		  $message_pages = explode("\n", trim($messages['messages_pages']));
		  if (!is_array($message_pages)) $message_pages = array();
		  if (sizeof($message_pages) > 0) {
			reset($message_pages);
			while (list(, $message_page) = each($message_pages)) {
			  if (strpos(PHP_SELF, $message_page)!==false || strpos(SCRIPT_FILENAME, $message_page)!==false) {
				if (basename($message_page)==FILENAME_DEFAULT) {
				  if (empty($sPath_array) && ($iName=='index' || $iName=='')) {
					$show_message = true;
				  }
				} else {
				  $show_message = true;
				}
			  }
			}
		  } else {
			$show_message = true;
		  }
		} else {
		  $show_message = true;
		}
		if ($show_message && tep_not_null(strip_tags($messages['messages_description']))) {
		  $messageStack->add('header', $messages['messages_description']);
		  break;
		}
	  }
	}

	$HTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER');
	$same_site_referer = strpos($HTTP_REFERER, str_replace('http://', '', str_replace('www.', '', HTTP_SERVER)))!==false;

	if (SHOP_LISTING_STATUS=='1' && $spider_flag==false && basename(SCRIPT_FILENAME)!=FILENAME_PRICELIST && substr(PHP_SELF, 0, 5)!='/ext/') {
//	  if ($_SERVER['REMOTE_ADDR']=='94.199.108.66') {
	  $request_uri = $_SERVER['REQUEST_URI'];
	  if ($request_uri==DIR_WS_CATALOG) $request_uri = '';
	  if (tep_session_is_registered('session_country_shop')) {
		list($session_country_code, $session_shop_id) = explode(':', $session_country_shop);
		if ($session_shop_id!=SHOP_ID && tep_not_null($session_country_code)) {
		  tep_redirect_to_shop($session_shop_id);
		}
	  } else {
		$country_code = tep_get_ip_info();
		if (tep_not_null($country_code)) {
//		  if (tep_not_null($HTTP_REFERER) || tep_not_null($request_uri)) {
			$referer_contents = parse_url($HTTP_REFERER);
			$shop_check_query = tep_db_query("select count(*) as total from " . TABLE_SHOPS . " where (shops_url = '" . tep_db_input($referer_contents['scheme'] . '://' . $referer_contents['host']) . "' or shops_ssl = '" . tep_db_input($referer_contents['scheme'] . '://' . $referer_contents['host']) . "') and shops_listing_status = '1'");
//			if ($_SERVER['REMOTE_ADDR']=='94.199.108.66') { print_r($_SERVER); die; }
			$shop_check = tep_db_fetch_array($shop_check_query);
			if ($shop_check['total'] < 1 && !in_array($HTTP_GET_VARS['from'], array('direct', 'adwords'))) {
			  tep_redirect_to_shop('', $country_code);
			} else {
			  $session_country_shop = $country_code . ':' . SHOP_ID;
			  tep_session_register('session_country_shop');
			}
//		  }
		} else {
		  $empty_country_dir = DIR_FS_CATALOG . 'cache/countries/';
		  if (!is_dir($empty_country_dir)) mkdir($empty_country_dir, 0777);
		  $empty_country_file = $empty_country_dir . date('Y-m-d');
		  $unknown_country_visitors_count = 1;
		  if (file_exists($empty_country_file)) {
			if ($fp = fopen($empty_country_file, 'r')) {
			  stream_set_timeout($fp, 1);
			  $content = fread($fp, filesize($empty_country_file));
			  fclose($fp);
			  $unknown_country_visitors_count = (int)trim($content) + 1;
			}
		  }

		  if ($fp = fopen($empty_country_file, 'w')) {
			fwrite($fp, $unknown_country_visitors_count);
			fclose($fp);
		  }

		  $session_country_shop = ':' . SHOP_ID;
		  tep_session_register('session_country_shop');
		}
	  }
//	  }
	  /*
	  $country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
	  $all_countries = tep_get_shops_countries(0, 1);
	  $available_domains = array();
	  $available_country_name = '';
	  reset($all_countries);
	  while (list(, $country_info) = each($all_countries)) {
		if ($country_code==$country_info['country_code']) {
		  $available_domains[] = $country_info['shop_url'];
		  $available_country_name = $country_info['country_ru_name'];
		}
	  }
	  if (!in_array(HTTP_SERVER, $available_domains) && sizeof($available_domains)>0) {
		$available_domains_string = '';
		'<strong>' . implode('</strong>, <strong>', $available_domains) . '</strong>';
		reset($available_domains);
		while (list(, $available_domain) = each($available_domains)) {
		  $available_domains_string .= (tep_not_null($available_domains_string) ? ', ': '') . '<strong><a href="' . $available_domain . '">' . str_replace('http://', '', $available_domain) . '</a></strong>';
		}
		if (sizeof($messageStack->messages)==0) {
		  $messageStack->add('header', sprintf(HOME_DOMAIN_INVITATION, $available_country_name, $available_domains_string));
		}
	  }
	  */
	}
  }
  if (in_array(HTTP_SERVER, array('http://www.easternowl.com')) && in_array(basename(SCRIPT_FILENAME), array(FILENAME_CHECKOUT_SHIPPING, FILENAME_CHECKOUT_PAYMENT, FILENAME_CHECKOUT_CONFIRMATION))) {
	tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  $update_products_types_cache_file = false;
  $active_products_types_array = array();
  $products_types_cache_file = DIR_FS_CATALOG . 'cache/active_products_types.html';
  if (!file_exists($products_types_cache_file)) {
	$update_products_types_cache_file = true;
  } else {
	$products_types_last_modified_query = tep_db_query("select max(products_last_modified) as last_modified from " . TABLE_PRODUCTS_TYPES);
	$products_types_last_modified = tep_db_fetch_array($products_types_last_modified_query);
	clearstatcache();
	if (date('Y-m-d H:i:s', filemtime($products_types_cache_file)) < $products_types_last_modified['last_modified']) {
	  $update_products_types_cache_file = true;
	}
  }
  if ($update_products_types_cache_file) {
	$products_types_query = tep_db_query("select distinct products_types_id from " . TABLE_PRODUCTS_TYPES . " where products_types_status = '1'");
	while ($products_types = tep_db_fetch_array($products_types_query)) {
	  $product_type_check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$products_types['products_types_id'] . "' and products_status = '1' limit 1");
	  $product_type_check = tep_db_fetch_array($product_type_check_query);
	  if ($product_type_check['products_id'] > 0) {
		$type_categories_check_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types['products_types_id'] . "' limit 1");
		$type_categories_check = tep_db_fetch_array($type_categories_check_query);
		if ($type_categories_check['categories_id'] > 0) {
		  $category_type_check_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where products_types_id = '" . (int)$products_types['products_types_id'] . "' and categories_status = '1' limit 1");
		  $category_type_check = tep_db_fetch_array($category_type_check_query);
		  if ($category_type_check['categories_id'] > 0) $active_products_types_array[] = $products_types['products_types_id'];
		} else {
		  $active_products_types_array[] = $products_types['products_types_id'];
		}
	  }
	}
	$fp = fopen($products_types_cache_file, 'w');
	fwrite($fp, implode("\n", $active_products_types_array));
	fclose($fp);
  } else {
	$fp = fopen($products_types_cache_file, 'r');
	while (!feof($fp)) {
	  $active_products_types_array[] = trim(fgets($fp, 128));
	}
	fclose($fp);
  }

  $update_specials_types_cache_file = false;
  $active_specials_types_array = array();
  $specials_types_cache_file = DIR_FS_CATALOG . 'cache/active_specials_types.html';
  if (!file_exists($specials_types_cache_file)) {
	$update_specials_types_cache_file = true;
  } else {
	$specials_types_last_modified_query = tep_db_query("select max(specials_last_modified) as last_modified from " . TABLE_SPECIALS_TYPES);
	$specials_types_last_modified = tep_db_fetch_array($specials_types_last_modified_query);
	clearstatcache();
	if (date('Y-m-d H:i:s', filemtime($specials_types_cache_file)) < $specials_types_last_modified['last_modified']) {
	  $update_specials_types_cache_file = true;
	}
  }
  if ($update_specials_types_cache_file) {
	$active_specials_types_query = tep_db_query("select distinct specials_types_id from " . TABLE_SPECIALS . " where specials_types_id > '0' and status = '1'");
	while ($active_specials_types = tep_db_fetch_array($active_specials_types_query)) {
	  $active_specials_types_array[] = $active_specials_types['specials_types_id'];
	}
	if (sizeof($active_specials_types_array) > 0) {
	  $special_type_check_query = tep_db_query("select distinct specials_types_id from " . TABLE_SPECIALS_TYPES . " where specials_types_status = '1' and specials_types_id in ('" . implode("', '", $active_specials_types_array) . "')");
	  $active_specials_types_array = array();
	  while ($special_type_check = tep_db_fetch_array($special_type_check_query)) {
		$active_specials_types_array[] = $special_type_check['specials_types_id'];
	  }
	}
	$fp = fopen($specials_types_cache_file, 'w');
	fwrite($fp, implode("\n", $active_specials_types_array));
	fclose($fp);
  } else {
	$fp = fopen($specials_types_cache_file, 'r');
	while (!feof($fp)) {
	  $active_special_type_id = (int)trim(fgets($fp, 128));
	  if ($active_special_type_id > 0) $active_specials_types_array[] = $active_special_type_id;
	}
	fclose($fp);
  }

  $update_reviews_types_cache_file = false;
  $active_reviews_types_array = array();
  $reviews_types_cache_file = DIR_FS_CATALOG . 'cache/active_reviews_types.html';
  if (!file_exists($reviews_types_cache_file)) {
	$update_reviews_types_cache_file = true;
  } else {
	$reviews_types_last_modified_query = tep_db_query("select max(last_modified) as last_modified from " . TABLE_REVIEWS_TYPES);
	$reviews_types_last_modified = tep_db_fetch_array($reviews_types_last_modified_query);
	clearstatcache();
	if (date('Y-m-d H:i:s', filemtime($reviews_types_cache_file)) < $reviews_types_last_modified['last_modified']) {
	  $update_reviews_types_cache_file = true;
	}
  }
  if ($update_reviews_types_cache_file) {
	$active_reviews_types_query = tep_db_query("select distinct reviews_types_id from " . TABLE_REVIEWS . " where reviews_types_id > '0' and reviews_status = '1'");
	while ($active_reviews_types = tep_db_fetch_array($active_reviews_types_query)) {
	  $active_reviews_types_array[] = $active_reviews_types['reviews_types_id'];
	}
	$review_type_check_query = tep_db_query("select distinct reviews_types_id from " . TABLE_REVIEWS_TYPES . " where reviews_types_status = '1' and reviews_types_id in ('" . implode("', '", $active_reviews_types_array) . "')");
	$active_reviews_types_array = array();
	while ($review_type_check = tep_db_fetch_array($review_type_check_query)) {
	  $active_reviews_types_array[] = $review_type_check['reviews_types_id'];
	}
	$fp = fopen($reviews_types_cache_file, 'w');
	fwrite($fp, implode("\n", $active_reviews_types_array));
	fclose($fp);
  } else {
	$fp = fopen($reviews_types_cache_file, 'r');
	while (!feof($fp)) {
	  $active_reviews_types_array[] = trim(fgets($fp, 128));
	}
	fclose($fp);
  }

  $update_news_types_cache_file = false;
  $active_news_types_array = array();
  $news_types_cache_file = DIR_FS_CATALOG . 'cache/active_news_types.html';
  if (!file_exists($news_types_cache_file)) {
	$update_news_types_cache_file = true;
  } else {
	$news_types_last_modified_query = tep_db_query("select max(last_modified) as last_modified from " . TABLE_NEWS_TYPES);
	$news_types_last_modified = tep_db_fetch_array($news_types_last_modified_query);
	clearstatcache();
	if (date('Y-m-d H:i:s', filemtime($news_types_cache_file)) < $news_types_last_modified['last_modified']) {
	  $update_news_types_cache_file = true;
	}
  }
  if ($update_news_types_cache_file) {
	$active_news_types_query = tep_db_query("select distinct news_types_id from " . TABLE_NEWS . " where news_types_id > '0' and news_status = '1'");
	while ($active_news_types = tep_db_fetch_array($active_news_types_query)) {
	  $active_news_types_array[] = $active_news_types['news_types_id'];
	}
	$news_type_check_query = tep_db_query("select distinct news_types_id from " . TABLE_NEWS_TYPES . " where news_types_status = '1' and news_types_id in ('" . implode("', '", $active_news_types_array) . "')");
	$active_news_types_array = array();
	while ($news_type_check = tep_db_fetch_array($news_type_check_query)) {
	  $active_news_types_array[] = $news_type_check['news_types_id'];
	}
	$fp = fopen($news_types_cache_file, 'w');
	fwrite($fp, implode("\n", $active_news_types_array));
	fclose($fp);
  } else {
	$fp = fopen($news_types_cache_file, 'r');
	while (!feof($fp)) {
	  $active_news_types_array[] = trim(fgets($fp, 128));
	}
	fclose($fp);
  }

  unset($products_to_search);
  unset($show_product_type);
  if (in_array(basename(SCRIPT_FILENAME), array(FILENAME_CATEGORIES, FILENAME_PRODUCT_INFO, FILENAME_MANUFACTURERS, FILENAME_SERIES, FILENAME_AUTHORS, FILENAME_SPECIALS, FILENAME_REVIEWS, FILENAME_FOREIGN))) {
	if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO && tep_not_null($HTTP_GET_VARS['products_id'])) {
	  $product_type_info_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
	  $product_type_info = tep_db_fetch_array($product_type_info_query);
	  $show_product_type = $product_type_info['products_types_id'];
	} elseif ($current_category_id > 0) {
	  $product_type_info_query = tep_db_query("select products_types_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
	  $product_type_info = tep_db_fetch_array($product_type_info_query);
	  $show_product_type = $product_type_info['products_types_id'];
	}
	if (isset($HTTP_GET_VARS['tName']) && in_array(basename(SCRIPT_FILENAME), array(FILENAME_CATEGORIES, FILENAME_PRODUCT_INFO, FILENAME_SERIES))) {
	  $tName = $HTTP_GET_VARS['tName'];
	  $tName = str_replace('/', '', $tName);
	  $product_type_info_query = tep_db_query("select products_types_id from " . TABLE_PRODUCTS_TYPES . " where products_types_id in ('" . implode("', '", $active_products_types_array) . "') and products_types_path = '" . tep_db_input(tep_db_prepare_input($tName)) . "'" . (isset($show_product_type) ? " and products_types_id = '" . (int)$show_product_type . "'" : "") . "");
	  $product_type_info = tep_db_fetch_array($product_type_info_query);
	  if ((int)$product_type_info['products_types_id'] == 0) {
		if ($show_product_type > 0) {
		  $product_type_info_query = tep_db_query("select products_types_id, products_types_path from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$show_product_type . "'");
		  $product_type_info = tep_db_fetch_array($product_type_info_query);
		  if ((int)$product_type_info['products_types_id'] > 0) {
			tep_redirect(str_replace(DIR_WS_CATALOG . $tName . '/', DIR_WS_CATALOG . $product_type_info['products_types_path'] . '/', REQUEST_URI), 301);
		  }
		}
		tep_redirect(tep_href_link(FILENAME_ERROR_404));
	  } else {
		$show_product_type = $product_type_info['products_types_id'];
	  }
	  unset($HTTP_GET_VARS['tName']);
	}
//	if (!isset($show_product_type)) $show_product_type = 1;
	$product_type_info_query = tep_db_query("select products_types_name, products_types_letter_search from " . TABLE_PRODUCTS_TYPES . " where" . (isset($show_product_type) ? " products_types_id = '" . (int)$show_product_type . "'" : " products_types_default_status = '1'") . " and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	$product_type_info = tep_db_fetch_array($product_type_info_query);
	$show_product_type_letter_search = $product_type_info['products_types_letter_search'];
	$breadcrumb->add($product_type_info['products_types_name'], tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type));
  }

// add category names or the manufacturer name to the breadcrumb trail
  if (isset($cPath_array) && basename(SCRIPT_FILENAME)!=FILENAME_REVIEWS) {
    for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
      $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
      if (tep_db_num_rows($categories_query) > 0) {
        $categories = tep_db_fetch_array($categories_query);
		if ($show_product_type==1) $category_link = tep_href_link(FILENAME_CATEGORIES, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1))));
		else $category_link = tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $show_product_type . '&categories_id=' . $cPath_array[$i]);
        $breadcrumb->add($categories['categories_name'], $category_link);
      } else {
        break;
      }
    }
  }
  if (isset($HTTP_GET_VARS['manufacturers_id']) && basename(SCRIPT_FILENAME)!=FILENAME_MANUFACTURERS) {
	if (basename(SCRIPT_FILENAME)!=FILENAME_ADVANCED_SEARCH && basename(SCRIPT_FILENAME)!=FILENAME_ADVANCED_SEARCH_RESULT) {
	  $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	  if (tep_db_num_rows($manufacturers_query) > 0) {
		$manufacturers = tep_db_fetch_array($manufacturers_query);
		$breadcrumb->add($manufacturers['manufacturers_name'], tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $HTTP_GET_VARS['manufacturers_id']));
	  }
	}
  }

// if partner is set update partners
  if (isset($HTTP_GET_VARS['partner']) && $session_started==true) {
	$partner_info_query = tep_db_query("select partners_id from " . TABLE_PARTNERS . " where partners_login = '" . tep_db_input(tep_db_prepare_input(mb_convert_encoding($HTTP_GET_VARS['partner'], 'CP1251', 'UTF-8'))) . "'");
	if (tep_db_num_rows($partner_info_query) < 1){
	  tep_db_query("insert into " . TABLE_PARTNERS . " (date_added, partners_login, date_of_last_logon, partners_register_type, partners_comission) values (now(), '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['partner'])) . "', now(), 'auto', '" . tep_db_input(str_replace(',', '.', PARTNERS_COMISSION_DEFAULT/100)) . "')");
	  $partners_id = tep_db_insert_id();
	} else {
	  $partner_info = tep_db_fetch_array($partner_info_query);
	  $partners_id = $partner_info['partners_id'];
	}
	tep_db_query("insert into " . TABLE_PARTNERS_STATISTICS . " (partners_id, date_added, partners_statistics_page, partners_statistics_referer, partners_statistics_ip, partners_statistics_sid) values ('" . (int)$partners_id . "', now(), '" . tep_db_input(tep_db_prepare_input(REQUEST_URI)) . "', '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_REFERER'])) . "', '" . tep_db_input(tep_get_ip_address()) . "', '" . tep_db_input(tep_session_id()) . "')");

	@tep_setcookie(str_replace('.', '_', STORE_NAME) . '_partner', $partners_id, time()+60*60*24*30*2, '/');
  }

// set which precautions should be checked
  define('WARN_INSTALL_EXISTENCE', 'true');
  define('WARN_CONFIG_WRITEABLE', 'false');
  define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
  define('WARN_SESSION_AUTO_START', 'true');
  define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

  $holiday_products_array = array('pearls' => array('title' => '«Жемчужина» вашей библиотеки', 'products' => '152879, 152879, 164742, 166080, 244419, 276036, 307975, 44451, 44455, 45277, 45284, 47537, 524851, 549504, 62377', 'categories' => ''),
								  'art_albums' => array('title' => 'Альбомы по искусству', 'products' => '151248, 177562, 227545, 275800, 302609, 305304, 305345, 305905, 306189, 308790, 310516, 326703, 332484, 333583, 351876, 355236, 385907, 3870, 3884, 394134, 408614, 416433, 417561, 423783, 43872, 43879, 43982, 45016, 450274, 45040, 45541, 467051, 468197, 473169, 473180, 473181, 473190, 50388, 50445, 512932, 526223, 545250, 62009', 'categories' => ''),
								  'pets' => array('title' => 'Ваши любимые питомцы', 'products' => '11308, 19503, 243569, 392509, 39910, 39913, 439461, 470371, 49446, 49455, 544530', 'categories' => ''),
								  'children' => array('title' => 'Детский Новый год', 'products' => '124613, 225298, 25222, 269475, 271673, 272098, 275298, 289242, 293623, 293624, 297554, 297648, 297649, 298433, 298466, 304929, 304930, 305004, 307479, 308927, 309508, 309709, 309709, 309710, 309710, 309712, 309712, 31134, 38982, 39458, 39784, 39784, 39785, 39786, 39787, 39788, 39788, 39790, 39790, 39791, 39791, 39795, 39799, 39998, 39998, 44561, 477244, 480629, 481035, 481036, 481038, 488411, 488412, 488413, 50995, 51056, 512939, 513991, 524395, 524471, 525152, 525425, 528934, 529049, 529050, 529051, 530685, 530726, 530900, 530901, 530902, 530903, 533382, 54117, 54199, 54290, 548651, 550001, 57222, 666515, 671325, 78137, 246321, 301109, 308927, 315131, 40712, 447897, 49131, 533187, 548656', 'categories' => '4946, 4987'),
								  'adventures' => array('title' => 'Мир путешествий и приключений', 'products' => '102635, 221700, 227545, 313937, 422826, 43947, 43949, 448966, 45471, 455812, 468528, 529778', 'categories' => ''),
								  'men' => array('title' => 'Подарки для настоящих мужчин', 'products' => '43901, 111616, 39944, 98000, 43744, 39961, 106390, 166091, 39954, 245990, 39946, 434611, 127472, 310371, 334410, 411303, 437137, 439455, 467793, 478709, 501918, 501992, 524435, 524436, 524437, 524438, 525963, 54023, 548939, 65757, 90793', 'categories' => ''),
								  'feast' => array('title' => 'Праздничный стол', 'products' => '127377, 127463, 165179, 176134, 178534, 186113, 225259, 238505, 238509, 245601, 306174, 310010, 331002, 334380, 354418, 400956, 419019, 43726, 449824, 450048, 468194, 480992, 499862, 501919, 513356, 513551, 531134, 533353, 544816, 548809, 549123, 549398, 549503, 549863, 57003, 57666, 62235, 667042', 'categories' => ''),
								  'christmas' => array('title' => 'Рождество', 'products' => '16739, 272302, 275543, 40210, 467253, 499757, 512645, 513038, 513046, 528760, 544610, 549263, 289391, 305962, 306345, 307085, 318316, 396525, 433076, 465379, 481098, 499328, 533278', 'categories' => ''),
								  'souvenirs' => array('title' => 'Сувениры и приятные мелочи', 'products' => '425028, 425065, 425094, 425170, 425171, 425175, 425176, 425177, 425178, 425180, 425181, 425183', 'categories' => '4893, 3406, 3415, 3419, 3429, 4872, 9506'),
								  'women' => array('title' => 'Только для женщин', 'products' => '49453, 76750, 102773, 174786, 195937, 241647, 245920, 258642, 267722, 270181, 275127, 306380, 308222, 43745, 448967, 464270, 49453, 500025, 525199, 531137, 532184, 544552, 545236, 549828', 'categories' => ''),
								  'encyclopedia' => array('title' => 'Энциклопедии', 'products' => '164683, 214042, 236812, 39907, 39939, 39940, 39945, 39949, 39953, 39966, 45020, 45378, 462128, 68400', 'categories' => ''),
								 );
?>
