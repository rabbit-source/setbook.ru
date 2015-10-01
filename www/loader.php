<?php
  require('includes/application_top.php');

  if (is_object($navigation)) $navigation->remove_current_page();

  $content = FILENAME_LOADER;

  $page_query = tep_db_query("select pages_id, pages_name, pages_additional_description, pages_description from " . TABLE_PAGES . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  $page = tep_db_fetch_array($page_query);
  define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
  $translation_query = tep_db_query("select pages_translation_key, pages_translation_value from " . TABLE_PAGES_TRANSLATION . " where pages_filename = '" . tep_db_input(basename($content)) . "' and language_id = '" . (int)$languages_id . "'");
  while ($translation = tep_db_fetch_array($translation_query)) {
	define($translation['pages_translation_key'], $translation['pages_translation_value']);
  }

  header('Content-type: text/html; charset=' . CHARSET . '');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  switch ($action) {
	case 'buy_now':
	  if (ALLOW_GUEST_TO_ADD_CART=='true' || tep_session_is_registered('customer_id')) {
		if (isset($HTTP_GET_VARS['product_id'])) {
		  $quantity = (int)$HTTP_GET_VARS['quantity'];

		  if ($quantity < 1) $quantity = 1;

		  if ($HTTP_GET_VARS['to']=='foreign') {
			$foreign_cart->add_cart($HTTP_GET_VARS['product_id'], 1);
		  } elseif ($HTTP_GET_VARS['to']=='postpone') {
			$postpone_cart->add_cart($HTTP_GET_VARS['product_id'], 1);
			$cart->remove($HTTP_GET_VARS['product_id']);
		  } else {
			$quantity = $cart->get_quantity($HTTP_GET_VARS['product_id']) + $quantity;
			$cart->add_cart($HTTP_GET_VARS['product_id'], $quantity);
			$postpone_cart->remove($HTTP_GET_VARS['product_id']);
		  }
		}
	  }
	  $block_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '7' limit 1");
	  if (tep_db_num_rows($block_query) > 0) {
		$block = tep_db_fetch_array($block_query);
		if (tep_not_null($block['blocks_filename']) && file_exists(DIR_WS_BLOCKS . $block['blocks_filename'])) {
		  include(DIR_WS_BLOCKS . $block['blocks_filename']);
		}
	  }
	  tep_exit();
	  break;
	case 'callback':
	  if (isset($HTTP_GET_VARS['callback_country_code']) && isset($HTTP_GET_VARS['callback_region_code']) && isset($HTTP_GET_VARS['callback_telephone_number'])) {
		$callback_country_code = substr(preg_replace('/[^a-z\d]/', '', $HTTP_GET_VARS['callback_country_code']), 0, 2);
		$callback_country_phone_code = 0;
		$all_countries = tep_get_shops_countries();
		reset($all_countries);
		while (list($country_code, $country_info) = each($all_countries)) {
		  if ($country_info['country_iso_code_2']==$callback_country_code) {
			if (tep_not_null($country_info['phone_code'])) $callback_country_phone_code = $country_info['phone_code'];
			break;
		  }
		}
		reset($all_countries);
		while (list($country_code, $country_info) = each($all_countries)) {
		  if ($country_info['country_id']==STORE_COUNTRY) {
			$store_country_phone_code = $country_info['phone_code'];
			break;
		  }
		}
		if ($callback_country_phone_code > 0) {
		  $callback_region_code = preg_replace('/[^\d]/', '', $HTTP_GET_VARS['callback_region_code']);
		  $callback_telephone_number = preg_replace('/[^\d]/', '', $HTTP_GET_VARS['callback_telephone_number']);
		  $callback_number = $callback_country_phone_code . $callback_region_code . $callback_telephone_number;
		  $callback_number_type = 'phone';
		} else {
		  echo '<br />' . CALLBACK_ERROR_COUNTRY;
		}
	  } elseif (isset($HTTP_GET_VARS['callback_skype_number'])) {
		$callback_skype_number = preg_replace('/[^-_\.\w\d]/', '', $HTTP_GET_VARS['callback_skype_number']);
		$callback_number = $callback_skype_number;
		$callback_number_type = 'skype';
	  } else {
		$callback_number = '';
		$callback_number_type = 'skype';
	  }
	  if (tep_not_null($callback_number)) {
		$cookie = '';
		if ($callback_answer = tep_request_html('http://users.telecomax.net/cabapi/amfphp/json.php?/service2.login/evgeniypev@gmail.com/2716166953/1/', 'GET')) {
		  if (tep_not_null($callback_answer)) {
			if (preg_match("/Set-Cookie\: ([^\r]+); path/i", $callback_answer, $regs)) {
			  $cookie = $regs[1];
			}
			list(, $callback_answer) = explode("\r\n\r\n", $callback_answer);
			$callback_answer = preg_replace('/[^a-z]/', '', $callback_answer);
			if (strtolower(trim($callback_answer))=='true') {
			  $store_phone_number = preg_replace('/[^+\d]/', '', STORE_OWNER_PHONE_NUMBER);
			  if (substr($store_phone, 0, 1)=='+') $store_phone_number = substr($store_phone_number, 1);
			  else $store_phone_number = $store_country_phone_code . $store_phone_number;

			  $skype_cache_filename = DIR_FS_CATALOG . 'cache/skype_status.txt';
			  if (STORE_CALLBACK_ORDER=='skype_phone') {
				list($store_skype_number, $skype_status) = explode(':', strtolower(trim(implode('', @file($skype_cache_filename)))));
				if ($skype_status=='online') {
				  $store_number = $store_skype_number;
				  $store_number_type = 'skype';
				} else {
				  $store_number = $store_phone_number;
				  $store_number_type = 'phone';
				}
			  } elseif (STORE_CALLBACK_ORDER=='skype') {
				list($store_skype_number) = array_map('trim', explode(',', STORE_OWNER_SKYPE_NUMBER));
				$store_number = $store_skype_number;
				$store_number_type = 'skype';
			  } else {
				$store_number = $store_phone_number;
				$store_number_type = 'phone';
			  }
			  if ($customer_id==2) echo $store_number . ' - ' . $store_number_type;

			  if ($callback_answer = tep_request_html('http://users.telecomax.net/cabapi/amfphp/json.php?/service2.makeCallBack/' . $store_number . '/' . $callback_number . '/' . $store_number_type . '/' . $callback_number_type . '/', 'GET', array('cookie' => $cookie))) {
				list(, $callback_answer) = explode("\r\n\r\n", $callback_answer);
				if (tep_not_null($callback_answer)) {
				  $callback_answer_array = unserialize($callback_answer);
				  if (strpos($callback_answer, '"desc":"OK"')) {
					echo '<br /><span>' . CALLBACK_CONNECTION_SUCCCESS . '</span><script language="javascript" type="text/javascript">' . "\n" . 'if (document.getElementById(\'callback_phone\')) document.getElementById(\'callback_phone\').style.display = \'none\'; else parent.document.getElementById(\'callback_phone\').style.display = \'none\';' . "\n" . 'if (document.getElementById(\'callback_skype\')) document.getElementById(\'callback_skype\').style.display = \'none\'; else parent.document.getElementById(\'callback_skype\').style.display = \'none\';' . "\n" . '</script>';
				  } else {
					echo '<br />' . CALLBACK_CONNECTION_ERROR;
				  }
				} else {
				 echo '<br />' . CALLBACK_CONNECTION_ERROR;
				}
			  } else {
				echo '<br />' . CALLBACK_CONNECTION_ERROR;
			  }
			} else {
			  echo '<br />' . CALLBACK_CONNECTION_ERROR;
			}
		  } else {
			echo '<br />' . CALLBACK_CONNECTION_ERROR;
		  }
		} else {
		  echo '<br />' . CALLBACK_CONNECTION_ERROR;
		}
	  } else {
		echo '<br />' . CALLBACK_ERROR_CONTACTS;
	  }
	  break;
	case 'show_all_countries_pull_down':
	  $all_countries_array = array();
	  $all_countries_unsorted = array();
	  $all_countries = tep_get_shops_countries();
	  reset($all_countries);
	  while (list($country_code, $country_info) = each($all_countries)) {
		if ($country_info['phone_code'] > 0) $all_countries_unsorted[$country_code] = ($languages_id==DEFAULT_LANGUAGE_ID ? $country_info['country_ru_name'] : $country_info['country_name']);
	  }
	  reset($all_countries_unsorted);
	  asort($all_countries_unsorted);
	  while (list($country_code, $country_name) = each($all_countries_unsorted)) {
		$all_countries_array[] = array('id' => $country_code, 'text' => $country_name);
	  }
	  echo tep_draw_pull_down_menu('callback_country_code', $all_countries_array, (tep_not_null($HTTP_GET_VARS['country_code']) ? $HTTP_GET_VARS['country_code'] : $session_country_code), 'style="width: 90px;" onchange="document.getElementById(\'callback_country\').innerHTML = \'&nbsp;<img src=&quot;' . DIR_WS_ICONS . 'flags/\'+this.options[this.selectedIndex].value.toLowerCase()+\'.gif&quot; /> <input type=&quot;hidden&quot; name=&quot;callback_country_code&quot; value=&quot;\'+this.options[this.selectedIndex].value+\'&quot; />&nbsp;\'; if (document.getElementById(\'callback_change_country\')) document.getElementById(\'callback_change_country\').style.display = \'\'; else parent.document.getElementById(\'callback_change_country\').style.display = \'\';" onblur="document.getElementById(\'callback_country\').innerHTML = \'&nbsp;<img src=&quot;' . DIR_WS_ICONS . 'flags/\'+this.options[this.selectedIndex].value.toLowerCase()+\'.gif&quot; /> <input type=&quot;hidden&quot; name=&quot;callback_country_code&quot; value=&quot;\'+this.options[this.selectedIndex].value+\'&quot; />&nbsp;\'; if (document.getElementById(\'callback_change_country\')) document.getElementById(\'callback_change_country\').style.display = \'\'; else parent.document.getElementById(\'callback_change_country\').style.display = \'\';"');
//;
	  break;
	case 'test_block':
//	  $HTTP_GET_VARS['products_id'] = 422278;
//	  include(DIR_WS_BLOCKS . 'block_' . (int)$HTTP_GET_VARS['blocks_id'] . '.php');
	  tep_exit();
	  break;
	case 'load_keyboard':
	  $swf_file = DIR_WS_TEMPLATES_IMAGES . ($HTTP_GET_VARS['kbrd_type']=='ua' ? 'kbrd_ua.swf' : 'kbrd_ru.swf');
?>
<object id="FlashID6" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="340" height="115" onmouseup="setTimeout('sfc()',200);" onclick="setTimeout('sfc()',200);">
  <param name="movie" value="<?php echo $swf_file; ?>" />
  <param name="quality" value="high" />
  <param name="wmode" value="opaque" />
  <param name="swfversion" value="8.0.35.0" />
  <!-- Next object tag is for non-IE browsers. So hide it from IE using IECC. -->
  <!--[if !IE]>-->
  <object type="application/x-shockwave-flash" data="<?php echo $swf_file; ?>" width="340" height="115">
	<!--<![endif]-->
	<param name="quality" value="high" />
	<param name="wmode" value="opaque" />
	<param name="swfversion" value="8.0.35.0" />
	<!-- The browser displays the following alternative content for users with Flash Player 6.0 and older. -->
	<div>
	  <h4>Content on this page requires a newer version of Adobe Flash Player.</h4>
	  <p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
	</div>
	<!--[if !IE]>-->
  </object>
  <!--<![endif]-->
</object>
<?php
	  tep_exit();
	  break;
	case 'load_region':
	case 'load_suburb':
	case 'load_city':
	  $region_field = tep_draw_input_field('state', '', 'size="40"');
	  $suburb_field = tep_draw_input_field('suburb', '', 'size="40"');
	  $city_field = tep_draw_input_field('city', '', 'size="40"');

	  if ($action=='load_region') {
		$country_has_zones = false;
		$zones_array = array();
		$zones_check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$HTTP_GET_VARS['country'] . "'");
		$zones_check = tep_db_fetch_array($zones_check_query);
		if ($zones_check['total'] > 0) {
		  $country_has_zones = true;
		  $zones_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
		  $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$HTTP_GET_VARS['country'] . "' order by zone_name");
		  while ($zones_row = tep_db_fetch_array($zones_query)) {
			$zones_array[] = array('id' => $zones_row['zone_name'], 'text' =>  $zones_row['zone_name']);
		  }
		}
	  }

	  $cities_count_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where city_country_id = '" . (int)$HTTP_GET_VARS['country'] . "'");
	  $cities_count = tep_db_fetch_array($cities_count_query);

	  $city_info_query = tep_db_query("select city_name, suburb_name, zone_name, parent_id from " . TABLE_CITIES . " where city_country_id = '" . (int)$HTTP_GET_VARS['country'] . "' and city_id = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['postcode'])) . "'");
	  if (tep_db_num_rows($city_info_query) > 0) {
		$city_info = tep_db_fetch_array($city_info_query);
		if ($action=='load_suburb') {
		  if ($HTTP_GET_VARS['only_name']=='1') {
			if ($city_info['parent_id'] > 0) echo $city_info['suburb_name'];
		  } else {
			$suburb_field = tep_draw_input_field('suburb', $city_info['suburb_name'], 'size="40"');
		  }
		} elseif ($action=='load_region') {
		  if ($country_has_zones) $region_field = tep_draw_pull_down_menu('state', $zones_array, $city_info['zone_name']);
		  else $region_field = tep_draw_input_field('state', $city_info['zone_name'], 'size="40"');
		} elseif ($action=='load_city') {
		  $city_field = tep_draw_input_field('city', $city_info['city_name'], 'size="40"');
		}
	  } else {
		if ($action=='load_region' && $country_has_zones) {
		  $region_field = tep_draw_pull_down_menu('state', $zones_array);
		} elseif ($action=='load_city') {
		  $city_field .= (($cities_count['total']>0 && ENTRY_POSTCODE_CHECK=='true' && tep_not_null($HTTP_GET_VARS['postcode'])) ? '<script>Указан несуществующий почтовый индекс!</script>' : '');
		}
	  }

	  if ($action=='load_region') echo $region_field;
	  elseif ($action=='load_suburb') echo $suburb_field;
	  elseif ($action=='load_city') echo $city_field;
	  tep_exit();
	  break;
	case 'load_states':
	  $states_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  $states_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$HTTP_GET_VARS['country'] . "' order by zone_name");
	  while ($states = tep_db_fetch_array($states_query)) {
		$states_array[] = array('id' => $states['zone_id'], 'text' => $states['zone_name']);
	  }
	  echo 'Выберите свой регион:<br />' . "\n" . tep_draw_pull_down_menu('states', $states_array, '', 'onchange="if (this.options[this.selectedIndex].value > 0) { getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_cities', 'SSL') . '&country=' . tep_output_string_protected($HTTP_GET_VARS['country']) . '&state=\'+this.value, \'cities\'); }"') . '<br />' . "\n" .
	  '<div id="cities" class="small"></div>' . "\n";
	  tep_exit();
	  break;
	case 'load_cities':
	  $suburbs_count_query = tep_db_query("select count(*) as total from " . TABLE_CITIES . " where parent_id > '0'");
	  $suburbs_count = tep_db_fetch_array($suburbs_count_query);
	  $cities_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  $cities_query = tep_db_query("select city_id, city_name from " . TABLE_CITIES . " where city_country_id = '" . (int)$HTTP_GET_VARS['country'] . "' and zone_id = '" . (int)$HTTP_GET_VARS['state'] . "' and parent_id = '0' group by city_name order by city_name");
	  while ($cities = tep_db_fetch_array($cities_query)) {
		$cities_array[] = array('id' => $cities['city_id'], 'text' => $cities['city_name']);
	  }
	  echo 'Выберите свой город:<br />' . "\n" . tep_draw_pull_down_menu('cities', $cities_array, '', 'onchange="if (this.options[this.selectedIndex].value > 0) { document.getElementById(\'confirmCity\').style.display = \'block\'; } if (document.getElementById(\'suburbs\').innerHTML!=\'\') getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_suburbs', 'SSL') . '&country=' . tep_output_string_protected($HTTP_GET_VARS['country']) . '&city=\'+this.value, \'suburbs\');"') . '<br />' . "\n" .
	  ($suburbs_count['total']>0 ? '<small>если Вашего города в списке нет, выберите ближайший районный центр</small><br />' . "\n" : '') .
	  '<div id="suburbs" class="small"></div>' . "\n" .
	  '<div id="confirmCity" style="display: none;">' . tep_draw_input_field('', 'Да, я живу тут!', 'onclick="var indexToLoad = \'\'; if (this.form.elements[\'suburbs\']) { if (suburbs.options[suburbs.selectedIndex].value!=\'\') indexToLoad = suburbs.options[suburbs.selectedIndex].value; } if (indexToLoad==\'\') { indexToLoad = cities.options[cities.selectedIndex].value; } if (indexToLoad!=\'\') { postcode.value = indexToLoad; loadCity(this.form, indexToLoad); document.getElementById(\'checkPostcode\').style.display = \'none\'; }"', 'button') . ($suburbs_count['total']>0 ? ' &nbsp; ' . tep_draw_input_field('', 'Я живу где-то рядом...', 'onclick="getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_suburbs', 'SSL') . '&country=' . tep_output_string_protected($HTTP_GET_VARS['country']) . '&city=\'+cities.options[cities.selectedIndex].value, \'suburbs\'); this.style.display = \'none\';"', 'button') : '') . '</div>' . "\n";
	  tep_exit();
	  break;
	case 'load_suburbs':
	  $cities_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
	  $cities_query = tep_db_query("select city_id, city_name from " . TABLE_CITIES . " where city_country_id = '" . (int)$HTTP_GET_VARS['country'] . "' and parent_id = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['city'])) . "' group by city_name order by city_name");
	  if (tep_db_num_rows($cities_query) > 0) {
		while ($cities = tep_db_fetch_array($cities_query)) {
		  $cities_array[] = array('id' => $cities['city_id'], 'text' => $cities['city_name']);
		}
		echo 'Рядом расположены:<br />' . "\n" . tep_draw_pull_down_menu('suburbs', $cities_array);
	  }
	  tep_exit();
	  break;
	case 'load_category_level':
	  echo tep_get_category_level($HTTP_GET_VARS['parent'], $HTTP_GET_VARS['level']);
	  tep_exit();
	  break;
	case 'load_review':
	case 'load_board':
	  if ($action=='load_board') {
		$query = tep_db_query("select customers_boards_description as description from " . TABLE_CUSTOMERS_BOARDS . " where customers_boards_id = '" . (int)$HTTP_GET_VARS['boards_id'] . "'");
	  } else {
		$query = tep_db_query("select reviews_text as description from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$HTTP_GET_VARS['reviews_id'] . "'");
	  }
	  $row = tep_db_fetch_array($query);
	  $description = $row['description'];
	  $description = str_replace('<br />', "\n", $description);
	  $description = str_replace('<p>', '', $description);
	  $description = str_replace('</p>', "\n\n", $description);
	  while (strpos($description, "\n\n")!==false) $description = trim(str_replace("\n\n", "\n", $description));
	  echo nl2br($description);
	  tep_exit();
	  break;
	case 'check_ssl':
	  if (isset($HTTP_GET_VARS['ssl']) && ($HTTP_GET_VARS['ssl']=='on' || $HTTP_GET_VARS['ssl']=='off') ) {
		$enable_ssl = $HTTP_GET_VARS['ssl'];
		if (!tep_session_is_registered('enable_ssl')) tep_session_register('enable_ssl');
//		echo 'var ssl_enabled = "' . $enable_ssl . '";' . "\n";
	  }
	  tep_exit();
	  break;
	case 'rotate_banner':
	  $banner_group = tep_sanitize_string($HTTP_GET_VARS['group']);
	  if ($banner = tep_banner_exists($banner_group, $HTTP_GET_VARS['shown'])) {
		echo tep_display_banner($banner);
?>
<script language="javascript" type="text/javascript"><!--
  var shownBanner = <?php echo $banner['banners_id']; ?>;
//--></script>
<?php
	  }
	  tep_exit();
	  break;
	case 'load_tree':
	  $product_type_id = (int)$HTTP_GET_VARS['type'];
	  if ((int)$HTTP_GET_VARS['type'] > 0) {
		$products_types_query = tep_db_query("select products_types_id, products_types_default_status, products_last_modified from " . TABLE_PRODUCTS_TYPES . " where products_types_id = '" . (int)$HTTP_GET_VARS['type'] . "'");
		if (tep_db_num_rows($products_types_query) > 0) {
		  $products_types = tep_db_fetch_array($products_types_query);
		  clearstatcache();
		  $categories_cache_dir = DIR_FS_CATALOG . 'cache/catalog/';
		  if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
		  $categories_cache_dir .= $products_types['products_types_id'] . '/';
		  if (!is_dir($categories_cache_dir)) mkdir($categories_cache_dir, 0777);
		  $categories_cache_filename = $categories_cache_dir . 'tree_0.html';
		  $include_categories_cache_filename = false;
		  if (file_exists($categories_cache_filename)) {
			if (date('Y-m-d H:i:s', filemtime($categories_cache_filename)) > $products_types['products_last_modified']) {
			  $include_categories_cache_filename = true;
			}
		  }
		  if ($include_categories_cache_filename==false) {
			$categories_string = tep_show_category(0, 1, '', $products_types['products_types_id'], true);
			$categories_string = str_replace('?' . tep_session_name() . '=' . tep_session_id(), '', $categories_string);
			$fp = fopen($categories_cache_filename, 'w');
			fwrite($fp, $categories_string);
			fclose($fp);
		  } else {
			$categories_string = '';
			$fp = fopen($categories_cache_filename, 'r');
			while (!feof($fp)) {
			  $categories_string .= fgets($fp, 400);
			}
			fclose($fp);
		  }
		  if ($products_types['products_types_default_status']=='0') {
			echo '		<div class="li"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=all') . '" class="active">' . TEXT_ALL_CATEGORY_PRODUCTS . '</a></div></div>' . "\n";
		  } elseif ($products_types['products_types_default_status']=='1') {
			$specials_types_query = tep_db_query("select specials_types_id, specials_types_path, specials_types_name from " . TABLE_SPECIALS_TYPES . " where specials_types_id in ('" . implode("', '", $active_specials_types_array) . "') and language_id = '" . (int)DEFAULT_LANGUAGE_ID . "' order by sort_order, specials_types_name limit 4");
			while ($specials_types = tep_db_fetch_array($specials_types_query)) {
			  echo '		<div class="li_special"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=' . $specials_types['specials_types_path']) . '"' . (($HTTP_GET_VARS['view']==$specials_types['specials_types_path'] && $products_types['products_types_id']==$show_product_type && $current_category_id==0) ? ' class="active"' : '') . '>' . $specials_types['specials_types_name'] . '</a></div></div>' . "\n";
			}
			echo '		<div class="li_special"><div class="level_1"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'tPath=' . $products_types['products_types_id'] . '&view=with_fragments') . '"' . (($HTTP_GET_VARS['view']=='with_fragments' && $products_types['products_types_id']==$show_product_type && $current_category_id==0) ? ' class="active"' : '') . '>' . LEFT_COLUMN_TITLE_FRAGMENTS . '</a></div></div>' . "\n";
		  }
		  echo $categories_string;
		}
	  }
	  tep_exit();
	  break;
	case 'load_carousel':
	  list($type, $type_id) = explode('_', urldecode($HTTP_GET_VARS['carousel_type']));
	  $type = tep_sanitize_string($type) . '_types';
	  $type_id = (int)$type_id;
	  if (in_array($type, array(TABLE_SPECIALS_TYPES, TABLE_PRODUCTS_TYPES))) {
		$carousel_products = array();
		$carousel_id = 'carousel_' . $type . '_' . $type_id;
		if ($type==TABLE_SPECIALS_TYPES) {
		  $products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$type_id . "' and specials_first_page = '1' and status = '1' and specials_date_added >= '" . date('Y-m-d', time()-60*60*24*7) . " 00:00:00' order by rand() limit 13");
		  if (tep_db_num_rows($products_query)==0) {
			$products_query = tep_db_query("select products_id from " . TABLE_SPECIALS . " where specials_types_id = '" . (int)$type_id . "' and specials_first_page = '1' and status = '1' order by rand() limit 13");
		  }
		} else {
		  $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$type_id . "' and products_status = '1' and products_listing_status = '1' and products_image_exists = '1'" . ((int)$type_id>2 ? " and products_quantity > '0'" : "") . " order by rand() limit 13");
		  if ($type_id > 2 && tep_db_num_rows($products_query)==0) {
			$products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_types_id = '" . (int)$type_id . "' and products_status = '1' and products_listing_status = '1' and products_image_exists = '1' order by rand() limit 13");
		  }
		}
		while ($products = tep_db_fetch_array($products_query)) {
		  $carousel_products[] = $products['products_id'];
		}
		if (sizeof($carousel_products) > 0) {
		  echo tep_show_products_carousel($carousel_products, $carousel_id, '', 'html');
		}
	  }
	  tep_exit();
	  break;
	case 'load_captcha':
	  header('Content-type: image/gif');
	  $image_width = 95;
	  $image_height = 18;
	  $font_size = 5;
	  $rand_number1 = rand(6, 20);
	  $rand_number2 = rand(1, 15);
	  $string = $rand_number1 . ($rand_number1>$rand_number2 ? ' - ' : ' + ') . $rand_number2 . ' = ';
	  $captcha_value = ($rand_number1>$rand_number2 ? $rand_number1 - $rand_number2 : $rand_number1 + $rand_number2);
	  if (!tep_session_is_registered('captcha_value')) tep_session_register('captcha_value');
	  $string_width = imagefontwidth($font_size) * strlen($string);
	  $string_height = imagefontheight($font_size);
	  $image = imagecreate($image_width, $image_height);
	  $rand1 = rand(0, 255);
	  $rand2 = rand(0, 255);
	  $rand3 = rand(0, 255);
	  $color = imagecolorallocate($image, $rand1, $rand2, $rand3);
	  $diff = 55;
	  $rand_new1 = ($rand1>$diff ? $rand1 - $diff : $rand1 + $diff);
	  $rand_new2 = ($rand2>$diff ? $rand2 - $diff : $rand2 + $diff);
	  $rand_new3 = ($rand3>$diff ? $rand3 - $diff : $rand3 + $diff);
	  if (($rand1+$rand2+$rand3)>380) $color1 = imagecolorallocate($image, $rand_new1, $rand_new2, $rand_new3);
	  else $color1 = imagecolorallocate($image, (255-$rand1), (255-$rand2), (255-$rand3));
	  imagefill($image, 0, 0, $color);
	  for ($i=rand(0, 5); $i<=$image_width; $i+=5) {
	//	imageline($image, $i, 0, $i, $image_height, $color2);
	  }
	  imagestring($image, $font_size, round($image_width-$string_width), floor(($image_height-$string_height)/2), $string, $color1);
	  imagegif($image);
	  imagedestroy($image);
	  tep_exit();
	  break;
	case 'load_block':
	  $block_info_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$HTTP_GET_VARS['block_id'] . "' and language_id = '" . (int)$languages_id . "'");
	  $block_info = tep_db_fetch_array($block_info_query);
	  if (tep_not_null($block_info['blocks_filename'])) {
		if (file_exists(DIR_WS_BLOCKS . basename($block_info['blocks_filename']))) include(DIR_WS_BLOCKS . basename($block_info['blocks_filename']));
	  }
	  tep_exit();
	  break;
	case 'update_product_info':
	  set_time_limit(6000);
	  tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1' and (trim(products_name) = '' or trim(products_name) = '&nbsp;')");
	  $fields = array('products_name', 'products_description');
	  $products_query = tep_db_query("select products_id, products_name, products_description, products_model, authors_name, manufacturers_name, series_name from " . TABLE_PRODUCTS_INFO . " where " . (tep_not_null($HTTP_GET_VARS['products_id']) ? "products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'" : "products_id not in (select products_id from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '1')") . " order by rand()");
	  while ($products = tep_db_fetch_array($products_query)) {
		$products_id = $products['products_id'];
		$check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '1'");
		$check = tep_db_fetch_array($check_query);
		if ($check['total']==0) {
		  reset($fields);
		  $products_name = '';
		  $products_description = '';
		  while (list(, $field) = each($fields)) {
			if (tep_not_null($products[$field])) {
			  $result = tep_get_translation($products[$field]);
			  ${$field} = $result['translation'];
			  if (tep_not_null($HTTP_GET_VARS['products_id'])) { echo $result['page']; die(); }
			} else {
			  ${$field} = '';
			}
		  }

		  $products_text = $products_name;
		  $products_model = $products['products_model'];
		  $authors_name = tep_transliterate($products['authors_name']);
		  $manufacturers_name = tep_transliterate($products['manufacturers_name']);
		  $series_name = tep_transliterate($products['series_name']);

		  if (tep_not_null($products_model)) $products_text .= ' ISBN ' . $products_model;
		  if (strlen($authors_name) > 2) $products_text .= ' by ' . $authors_name;
//		  if (strlen($manufacturers_name) > 2) $products_text .= ' publisher ' . $manufacturers_name;
//		  if (strlen($series_name) > 2) $products_text .= ' serie ' . $series_name;
		  $products_text = strip_tags(strtolower(html_entity_decode($products_text)));
		  $products_text = str_replace(array('«', '»', '+', '"', '/', '.', ',', '(', ')', '{', '}', '[', ']', '!', '?', '*', ';', '\'', '—', '_', ' - ', ':', '#', '\\', '|', '`', '~', '$', '^'), ' ', $products_text);
		  $products_text = trim(preg_replace('/\s+/', ' ', $products_text));

		  $sql = "insert ignore into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, products_name, products_description, products_text, language_id) values ('" . (int)$products_id . "', '" . tep_db_input($products_name) . "', '" . tep_db_input($products_description) . "', ' " . tep_db_input($products_text) . " ', '1')";
		  tep_db_query($sql);
		}
	  }
	  tep_exit();
	  break;
	case 'update_product_description':
	  set_time_limit(6000);
	  $products_query = tep_db_query("select products_id, products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$languages_id . "'" . ($HTTP_GET_VARS['products_id']>0 ? " and products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'" : "") . " and products_description <> ''");
	  while ($products = tep_db_fetch_array($products_query)) {
		$products_id = $products['products_id'];
		$check_query = tep_db_query("select products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '1'");
		$check = tep_db_fetch_array($check_query);
		$check['products_description'] = trim(html_entity_decode($check['products_description'], ENT_QUOTES));
		if (empty($check['products_description'])) {
		  $products_description = '';
		  $result = tep_get_translation($products['products_description']);
		  $products_description = $result['translation'];

		  $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . " set products_description = '" . tep_db_input($products_description) . "' where products_id = '" . (int)$products_id . "' and language_id = '1'";
		  tep_db_query($sql);
		}
	  }
	  tep_exit();
	  break;
	 case 'subscribe':
	  if (tep_session_is_registered('customer_id')) {
		$sub = (int)$HTTP_GET_VARS['sub'];
		$type = (int)$HTTP_GET_VARS['type'];
		//1: Тематики и разделы
		//2: Серии
		//3: Авторы
		//4: Издательство
		$cid = (int)$HTTP_GET_VARS['cid'];

		$tables_array = array(TABLE_CATEGORIES, TABLE_SERIES, TABLE_AUTHORS, TABLE_MANUFACTURERS);
		$params_array = array('categories_id', 'series_id', 'authors_id', 'manufacturers_id');

		$category = tep_db_query("select 1 from " . $tables_array[$type-1] . " where " . $params_array[$type-1] . " = '" . $cid . "'");
		if (tep_db_num_rows($category) > 0) {
		  if ($sub == 1) {
			tep_db_query("INSERT INTO subscribe (user_id, category_id, type_id, date_created) VALUES (".$customer_id.", ".$cid.", ".$type.", NOW());");
		  } elseif ($sub == 2) {
			tep_db_query("delete from subscribe where user_id = '" . $customer_id . "' and category_id = '" . $cid . "' and type_id = '" . $type . "'");
		  }
		}
	  }
	  tep_exit();
	  break;
  }

  tep_exit();
?>