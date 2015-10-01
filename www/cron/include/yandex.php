<?php

function tep_get_full_product_info($products_id) 
{
	global $languages_id, 
	$all_categories, 
	$currency, 
	$currencies, 
	$HTTP_GET_VARS, 
	$categories_audio, 
	$for, 
	$customer_discount;

	$products = tep_db_query_fetch_array("SELECT * FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . (int)$products_id . "'");

	if (DEFAULT_LANGUAGE_ID == $languages_id) 
	{
		$product_info = tep_db_query_fetch_array("SELECT * FROM " . TABLE_PRODUCTS_INFO . " WHERE products_id = '" . (int)$products['products_id'] . "'");
		$product_info = array_merge($product_info, $products);
		$product_info['products_url'] = HTTP_SERVER . $product_info['products_url'];
	} 
	else 
	{
		//Добавление перевода
		$product_info_query = tep_db_query("
			SELECT products_name, 
			products_description 
			FROM " . TABLE_PRODUCTS_DESCRIPTION . " 
			WHERE products_id = '" . (int)$products['products_id'] . "' 
			AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		
		$product_info = tep_db_fetch_array($product_info_query);
		if (DEFAULT_LANGUAGE_ID==1) 
		{
			$product_ru_info = tep_db_query_fetch_array("
				SELECT products_name, 
				products_description 
				FROM " . TABLE_PRODUCTS_DESCRIPTION . " 
				WHERE products_id = '" . (int)$products['products_id'] . "' 
				AND language_id = '" . (int)$languages_id . "'");
			$product_ru_name = tep_transliterate($product_ru_info['products_name']);
			if ($product_info['products_name'] != $product_ru_info['products_name'] && $product_info['products_name'] != $product_ru_name) 
				$product_info['products_name'] .= (tep_not_null($product_info['products_name']) ? ' / ' : '') . $product_ru_name;
		}
		
		$author_info = tep_db_query_fetch_array("
				SELECT authors_name 
				FROM " . TABLE_AUTHORS . " 
				WHERE authors_id = '" . (int)$products['authors_id'] . "' 
				AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		if (!is_array($author_info)) 
			$author_info = array();
		
		$serie_info = tep_db_query_fetch_array("
				SELECT series_name 
				FROM " . TABLE_SERIES . " 
				WHERE series_id = '" . (int)$products['series_id'] . "' 
				AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		if (!is_array($serie_info)) 
			$serie_info = array();
		
		$manufacturer_info = tep_db_query_fetch_array("
				SELECT manufacturers_name 
				FROM " . TABLE_MANUFACTURERS_INFO . " 
				WHERE manufacturers_id = '" . (int)$products['manufacturers_id'] . "' 
				AND languages_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		if (!is_array($manufacturer_info)) 
			$manufacturer_info = array();
		
		$product_info['products_width'] = '';
		$product_info['products_height'] = '';
		$product_info['products_width_height_measure'] = '';
		
		$product_format_info = tep_db_query_fetch_array("
				SELECT products_formats_name 
				FROM " . TABLE_PRODUCTS_FORMATS . " 
				WHERE products_formats_id = '" . (int)$products['products_formats_id'] . "'");
		if (!is_array($product_format_info)) 
			$product_format_info = array();
		
		if (tep_not_null($product_format_info['products_formats_name'])) 
		{
			$product_format = $product_format_info['products_formats_name'];
			list($product_format) = explode(' ', $product_format);
			list($product_format) = explode('/', $product_format);
			if (preg_match('/^(\d+)x(\d+)$/i', $product_format, $regs)) 
			{
		  		$product_info['products_width'] = $regs[1];
		  		$product_info['products_height'] = $regs[1];
		  		$product_info['products_width_height_measure'] = 'mm';
			}
		}
		
		$product_cover_info = tep_db_query_fetch_array("
				SELECT products_covers_name 
				FROM " . TABLE_PRODUCTS_COVERS . " 
				WHERE products_covers_id = '" . (int)$products['products_covers_id'] . "' 
				AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		if (!is_array($product_cover_info)) 
			$product_cover_info = array();
		
		$product_type_info = tep_db_query_fetch_array("
				SELECT products_types_name 
				FROM " . TABLE_PRODUCTS_TYPES . " 
				WHERE products_types_id = '" . (int)$products['products_types_id'] . "' 
				AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
		if (!is_array($product_type_info)) 
			$product_type_info = array();
		
		$category_info = tep_db_query_fetch_array("
				SELECT categories_id 
				FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " 
				WHERE products_id = '" . (int)$products['products_id'] . "' 
				ORDER BY categories_id DESC 
				LIMIT 1");
		if (!is_array($category_info)) 
			$category_info = array();
		
		$product_info = array_merge($product_info, 
									$products, 
									$author_info, 
									$serie_info, 
									$manufacturer_info, 
									$product_format_info, 
									$product_cover_info, 
									$category_info);
		
		$product_info['products_url'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id'], 'NONSSL', false);
	}

	if ($customer_discount['type'] == 'purchase' && $products['products_purchase_cost'] > 0) 
		$product_info['products_price'] = $products['products_purchase_cost'] * (1 + $customer_discount['value']/100);

	if (mb_strpos($product_info['products_description'], '<table', 0, 'CP1251')!==false) 
		$product_info['products_description'] = mb_substr($product_info['products_description'], 0, mb_strpos($product_info['products_description'], '<table', 0, 'CP1251'), 'CP1251');
	
	$short_description = trim(preg_replace('/\s+/', ' ', preg_replace('/<\/?[^>]+>/', ' ', $product_info['products_description'])));
	$product_info['products_description'] = $short_description;
	$product_info['products_description_short'] = $short_description;

	if (!in_array($currency, array('RUR', 'EUR', 'USD', 'UAH'))) 
	{
		$product_info['products_currency'] = 'RUR';
		$product_info['products_price'] = str_replace(',', '.', round($product_info['products_price'], $currencies->get_decimal_places($product_info['products_currency'])));
	} 
	else 
	{
		$product_info['products_currency'] = str_replace('RUB', 'RUR', DEFAULT_CURRENCY);
		$product_info['products_price'] = str_replace(',', '.', round($product_info['products_price'] * $currencies->get_value($product_info['products_currency']), $currencies->get_decimal_places($product_info['products_currency'])));
	}
	
	if (tep_not_null($product_info['products_image'])) 
	{
		$product_info['products_image_big'] = 'http://149.126.96.163/big/' . $product_info['products_image'];
	  	$product_info['products_image'] = 'http://149.126.96.163/thumbs/' . $product_info['products_image'];
	}

	$product_info['products_buy'] = tep_href_link(FILENAME_SHOPPING_CART, 'action=buy_now&product_id=' . $product_info['products_id'], 'NONSSL', false);

	$product_info['products_quantity'] = '';

	$product_info['is_audio'] = false;
	
	if (in_array($product_info['categories_id'], $categories_audio)) 
		$product_info['is_audio'] = true;

	if ( (ALLOW_SHOW_AVAILABLE_IN == 'true' && tep_not_null($HTTP_GET_VARS['limit'])) || (SHOP_ID == 4) ) 
	{
		//пусто
	} 
	elseif ($product_info['products_listing_status'] == 1) 
	{
		$product_info['products_available_in'] = 1;
	} 
	else 
	{
	  	$product_info['products_available_in'] = 10;
	}

	reset($product_info);
	while (list($k, $v) = each($product_info)) 
	{
		$v = str_replace($from1, $to, str_replace($from, $to, $v));
		if (in_array($k, array('products_name', 'products_description'))) 
			$v = preg_replace('/\s{2,}/', ' ', preg_replace('/[^_\/\w\d\#\&(\)\-\[\]\.",;]/', ' ', $v));
		
		$product_info[$k] = htmlspecialchars(strip_tags(tep_html_entity_decode($v)), ENT_QUOTES);
	}

	return $product_info;
}
  
function write_to_file($filename, $stream_id, $content) 
{
	global $GLOBAL_CRON;
	$file_ext = substr($filename, strrpos($filename, '.')+1);
	switch ($file_ext) {
	  case 'gz':
		$string = gzencode($content, 9);
		break;
	  case 'bz2':
		$string = bzcompress($content, 9);
		break;
	  default:
		$string = $content;
		break;
	}
	flush();
	if(!$GLOBAL_CRON) echo $string;
	//else echo "file ".$filename." ".strlen($string)."\n";
	if ($stream_id) return fwrite($stream_id, $string);
}

  function tep_get_csv_string($csv_data_array, $separator = ";") {
	ob_start();
	$out = fopen('php://output', 'w');
	fputcsv($out, $csv_data_array, $separator);
	fclose($out);
	return ob_get_clean();
  }


function tep_replace_non_xml_chars($string)
{
	if (empty($string))
		return $string;
	
	for ($i = 0; $i < strlen($string); $i++)
	{
		if ($string[$i] == chr(12))
		{
			$string[$i] = ' ';
		}
	}
	
	return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', ' ', $string, ENT_QUOTES);
}
  
//Основная функция генерации YML
function generate($HTTP_GET_VARS)
{
	global $languages_id, 
	$all_categories, 
	$currency, 
	$currencies, 
	$categories_audio, 
	$customer_discount, 
	$cart,
	$breadcrumb;
	
	$customer_discount = $cart->get_customer_discount();
	if (!is_array($customer_discount)) 
		$customer_discount = array();
	
	$content = FILENAME_PRICELIST;
	
	$page = tep_db_query_fetch_array("
			SELECT pages_id, 
			pages_name, 
			pages_additional_description, 
			pages_description 
			FROM " . TABLE_PAGES . " 
			WHERE pages_filename = '" . tep_db_input(basename($content)) . "' 
			AND language_id = '" . (int)$languages_id . "'");
	
	define('ADDITIONAL_DESCRIPTION', $page['pages_additional_description']);
	
	$translation_query = tep_db_query("
			SELECT pages_translation_key, 
			pages_translation_value 
			FROM " . TABLE_PAGES_TRANSLATION . " 
			WHERE pages_filename = '" . tep_db_input(basename($content)) . "' 
			AND language_id = '" . (int)$languages_id . "'");
	
	while ($translation = tep_db_fetch_array($translation_query)) 
		define($translation['pages_translation_key'], $translation['pages_translation_value']);

  	$breadcrumb->add($page['pages_name'], tep_href_link(FILENAME_PRICELIST));

	$fields_array = array();
	$fields_array['products_model'] = TEXT_CHOOSE_MODEL;
	$fields_array['products_name'] = TEXT_CHOOSE_NAME;
	$fields_array['categories_name'] = TEXT_CHOOSE_CATEGORY;
	$fields_array['authors_name'] = TEXT_CHOOSE_AUTHOR;
	$fields_array['manufacturers_name'] = TEXT_CHOOSE_MANUFACTURER;
	$fields_array['series_name'] = TEXT_CHOOSE_SERIE;
	$fields_array['products_description'] = TEXT_CHOOSE_DESCRIPTION;
	$fields_array['products_price'] = TEXT_CHOOSE_PRICE;
	$fields_array['products_year'] = TEXT_CHOOSE_YEAR;
	$fields_array['products_pages_count'] = TEXT_CHOOSE_PAGES_COUNT;
	$fields_array['products_copies'] = TEXT_CHOOSE_COPIES;
	$fields_array['products_covers_name'] = TEXT_CHOOSE_COVER;
	$fields_array['products_formats_name'] = TEXT_CHOOSE_FORMAT;
	$fields_array['products_image'] = TEXT_CHOOSE_IMAGE;
	$fields_array['products_url'] = TEXT_CHOOSE_URL;

	$fileds_required = array('products_model', 'products_name', 'authors_name', 'products_price', 'manufacturers_name');

	$specials_array = array();
	$specials_types_query = tep_db_query("
			SELECT specials_types_id, 
			specials_types_name 
			FROM " . TABLE_SPECIALS_TYPES . " 
			WHERE specials_types_status = '1' 
			AND specials_types_path <> '' 
			AND language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
	while ($specials_types = tep_db_fetch_array($specials_types_query)) 
	{
		$specials_type_check = tep_db_query_fetch_array("
				SELECT count(*) as total 
				FROM " . TABLE_SPECIALS . " 
				WHERE specials_types_id = '" . (int)$specials_types['specials_types_id'] . "' 
				AND status = '1'");
		if($specials_type_check['total'] > 0) 
			$specials_array[ $specials_types['specials_types_id'] ] = $specials_types['specials_types_name'];
	}

	$specials_periods_array = array(array('id' => 'w', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_WEEK),
								  array('id' => '2w', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_2_WEEK),
								  array('id' => 'm', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_MONTH),
								  array('id' => 'h', 'text' => ENTRY_PRICELIST_SPECIALS_LAST_HALF_YEAR),
								  );

	$fields = array();
	//здесь была проверка на выдачу определенных полей
	$categories = array();
	//здесь была проверка на выдачу определенных категорий
	$manufacturers = array();
	//здесь была проверка на выдачу определенных издательств

	$specials = array();
	$specials_periods = array();
	////здесь была проверка на выдачу определенных типов

	$ff = 'xml';

	$status = 'active';

	$compression_method = '';//проверить наличие переменной далее

	$type_info_query = tep_db_query("
			SELECT products_types_id, 
			products_last_modified 
			FROM " . TABLE_PRODUCTS_TYPES . " 
			WHERE products_types_status = '1'" . 
				( tep_not_null($HTTP_GET_VARS['type']) ? 
				" AND products_types_path = '" . tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['type'])) . "'" : 
				" AND products_types_default_status = '1'" ) 
			." limit 1");
			
	if (tep_db_num_rows($type_info_query) < 1) tep_exit();
	else 
	{
		$type_info = tep_db_fetch_array($type_info_query);
		$products_types_id = $type_info['products_types_id'];
		$products_last_modified = strtotime($type_info['products_last_modified']);
	}

	$select_string_select = "SELECT distinct p.products_id";
	$select_string_from = " FROM " . TABLE_PRODUCTS_INFO . " p";
	$select_string_where = " WHERE p.products_types_id = '" . (int)$products_types_id . 
	"' AND p.categories_id <> '4990' 
	AND p.products_status = '1'" . 
		($status=='active' ? 
		" AND p.products_listing_status = '1'" : 
		"") 
	." AND p.products_price > '0'";

	//Формируем список категорий
	$disabled_categories = array();
	$type_categories_check = tep_db_query_fetch_array("
			SELECT categories_id 
			FROM " . TABLE_CATEGORIES . " 
			WHERE products_types_id = '" . (int)$products_types_id . "'");
	
	if ($type_categories_check['categories_id'] > 0) 
		$active_categories = array();
	else 
		$active_categories = array('0');
	
	$categories_query = tep_db_query("
			SELECT categories_id, 
			categories_xml_status 
			FROM " . TABLE_CATEGORIES . " 
			WHERE products_types_id = '" . (int)$products_types_id . "' 
			AND categories_status = '1' 
			ORDER BY parent_id");
	while ($categories = tep_db_fetch_array($categories_query)) 
	{
		if ($categories['categories_xml_status'] < 1 && !in_array($categories['categories_id'], $disabled_categories)) 
		{
			$disabled_categories[] = $categories['categories_id'];
			tep_get_subcategories($disabled_categories, $categories['categories_id']);
		} 
		elseif (!in_array($categories['categories_id'], $disabled_categories)) 
		{
			if (!in_array($categories['categories_id'], $active_categories)) 
				$active_categories[] = $categories['categories_id'];
		}
	}
	$select_string_where .= " AND p.categories_id > '0'";
	
	if (sizeof($disabled_categories) > 0) 
		$select_string_where .= " AND p.categories_id NOT IN ('" . implode("', '", $disabled_categories) . "')";
	//END Формируем список категорий

	$all_categories = array();
	//Здесь была выборка на разные сайты
	$separator = ';';

	$limit_string = "";
	$select_string = $select_string_select . $select_string_from . $select_string_where;
	
	$select_string .= " GROUP BY p.products_id";
	
	if($HTTP_GET_VARS['file'] !== '') 
		$pricelist_filename = $HTTP_GET_VARS['file'];


	//создаем массив символов которые будем менять
	$from = array('<', '>', '&', '"', '&#34;', '&#60;', '&#62;', '&#034;', '&#060;', '&#062;', "\r\n");
	$from1 =  array('&amp;lt;', '&amp;gt;', '&amp;amp;', '&amp;quot;', '&amp;quot;', '&amp;lt;', '&amp;gt;', '&amp;quot;', '&amp;lt;', '&amp;gt;', '&amp;#039;', ' ');
	//создаем массив символов на которые будем менять
	$to =  array('&lt;', '&gt;', '&amp;', '&quot;', '&quot;', '&lt;', '&gt;', '&quot;', '&lt;', '&gt;', '&#039;', ' ');

	unset($pricelist_currency);

	$categories_audio = array();
	tep_get_subcategories($categories_audio, 1104);

	if ($customer_discount['type']=='purchase' && empty($for)) 
		$fp = false;
	else 
		$fp = fopen($pricelist_filename, 'wb');
	  
	 
	$content = '<?xml version="1.0" encoding="windows-1251"?>' . "\n".
	'<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n" .
	'<yml_catalog date="' . date('Y-m-d H:i', $products_last_modified) . '">' . "\n" .
	'  <shop>' . "\n" .
	'	<name>' . str_replace('&amp;amp;', '&amp;', str_replace($from, $to, STORE_NAME)) . '</name>' . "\n" .
	'	<company>' . str_replace('&amp;amp;', '&amp;', str_replace($from, $to, STORE_OWNER)) . '</company>' . "\n" .
	'	<url>' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false) . '</url>' . "\n" .
	'	<currencies>' . "\n";
	
	if ($currency=='UAH') 
	{
		$products_currency = 'UAH';
		$curs_query = tep_db_query("
				SELECT * 
				FROM " . TABLE_CURRENCIES . " 
				WHERE code in ('" . $currency . "')");
		while ($curs = tep_db_fetch_array($curs_query)) 
		{
			$content .= '	  <currency id="' . $curs['code'] . '" rate="1" />' . "\n";
		}
	} 
	elseif (!in_array($currency, array('RUR', 'EUR', 'USD', 'UAH'))) 
	{
		$products_currency = 'RUR';
		$curs_query = tep_db_query("
				SELECT * 
				FROM " . TABLE_CURRENCIES . " 
				WHERE code in ('RUR')");
		while ($curs = tep_db_fetch_array($curs_query)) 
		{
			$content .= '	  <currency id="' . $curs['code'] . '" rate="1" />' . "\n";
		}
	} 
	else 
	{
		$products_currency = 'RUR';
		$curs_query = tep_db_query("
				SELECT * 
				FROM " . TABLE_CURRENCIES . " 
				WHERE code in ('RUR', '" . $currency . "')");
		while ($curs = tep_db_fetch_array($curs_query)) 
		{
			$content .= '	  <currency id="' . $curs['code'] . '" rate="' . str_replace(',', '.', round(1/$curs['value'], 4)) . '" />' . "\n";
		}
	}
	$content .= "	</currencies>  \n	<categories>  \n";
	
	write_to_file($pricelist_filename, $fp, $content);

	$xml_categories_query = tep_db_query("
			SELECT concat_ws('', '<category id=\"', c.categories_id, '\" parentId=\"', c.parent_id, '\">', cd.categories_name, '</category>') as categories_string 
			FROM " . TABLE_CATEGORIES . " c, 
			" . TABLE_CATEGORIES_DESCRIPTION . " cd 
			WHERE c.products_types_id = '" . (int)$products_types_id . "' 
			AND c.categories_status = '1' 
			AND c.categories_xml_status = '1' 
			AND c.categories_id = cd.categories_id 
			AND cd.language_id = '" . (int)DEFAULT_LANGUAGE_ID . "'");
			
	while ($xml_categories = tep_db_fetch_array($xml_categories_query)) 
	{
		write_to_file($pricelist_filename, $fp, $xml_categories['categories_string'] . "\n");
		$COUNTER['Categories']++;
	}
	
	$content = "	</categories> \n	<offers> \n";
	
	write_to_file($pricelist_filename, $fp, $content);

	$temp_filename = UPLOAD_DIR . 'csv/products_' . substr(uniqid(rand()), 0, 10) . '.xml';
	$currency_decimal_places = $currencies->get_decimal_places($products_currency);
	$currency_value = $currencies->get_value($products_currency);
	if ($products_types_id==1) 
		$xml_string = "concat_ws('', '<offer id=\"', p.products_id, '\" type=\"book\" available=\"false\"><url>', '" . HTTP_SERVER . "', p.products_url, '</url><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><currencyId>" . $products_currency . "</currencyId><categoryId>', p.categories_id, '</categoryId><picture>', if(p.products_image,concat_ws('','http://149.126.96.163/thumbs/',p.products_image),''), '</picture><delivery>true</delivery><author>', p.authors_name, '</author><name>', p.products_name, '</name><publisher>', p.manufacturers_name, '</publisher><series>', p.series_name, '</series><year>', p.products_year, '</year>', '', '<language>" . $language . "</language><binding>', p.products_formats_name, '</binding><page_extent>', p.products_pages_count, '</page_extent><description>', replace(p.products_description,'\n',if((locate(products_description, '<br')>0 or locate(products_description, '<p')>0),' ','<br />')), '</description>" . (!in_array(DOMAIN_ZONE, array('ru', 'ua', 'by', 'kz')) ? "<sales_notes>отправка по факту оплаты</sales_notes>" : "") . "<downloadable>false</downloadable></offer>') as products_string";
	else 
		$xml_string = "concat_ws('', '<offer id=\"', p.products_id, '\" available=\"false\"><url>', '" . HTTP_SERVER . "', p.products_url, '</url><price>', replace(round(p.products_price*" . $currency_value . "," . $currency_decimal_places . "),',','.'), '</price><currencyId>" . $products_currency . "</currencyId><categoryId>', p.categories_id, '</categoryId><picture>', if(p.products_image,concat_ws('','http://149.126.96.163/thumbs/',p.products_image),''), '</picture><delivery>true</delivery><name>', p.products_name, '</name><vendor>', p.manufacturers_name, '</vendor><description>', replace(p.products_description,'\n',if((locate(products_description, '<br')>0 or locate(products_description, '<p')>0),' ','<br />')), '</description>" . (!in_array(DOMAIN_ZONE, array('ru', 'ua', 'by', 'kz')) ? "<sales_notes>отправка по факту оплаты</sales_notes>" : "") . "<downloadable>', if((p.products_filename is null), 'false', 'true'), '</downloadable></offer>') as products_string";
	
	$xml_query_row = str_replace("SELECT distinct p.products_id FROM " . TABLE_PRODUCTS_INFO . " p", "SELECT " . $xml_string . " FROM " . TABLE_PRODUCTS_INFO . " p", $select_string);
	$xml_query_row = str_replace("WHERE ", "WHERE 1 and p.categories_id not in ('" . implode("','", $categories_audio) . "') and ", $xml_query_row);
	
	if (strpos($xml_query_row, 'order by') !== false) 
		$xml_query_row = substr($xml_query_row, 0, strpos($xml_query_row, 'order by'));
		
	if (strpos($xml_query_row, ' limit ') === false) 
		$xml_query_row .= $limit_string;

	$query = tep_db_query($xml_query_row);
	while ($row = tep_db_fetch_array($query)) 
	{
		
		$t_str = preg_replace('/<series>(.*)<\/series>/ie', "'<series>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</series>'", $t_str);
		//$t_str = preg_replace('/<description>(.*)<\/description>/ie', "'<description>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', tep_replace_non_xml_chars(strip_tags(tep_html_entity_decode('$1')))), ENT_QUOTES) . '</description>'", $t_str);
		$t_str = preg_replace('/<description>(.*)<\/description>/ie', "'<description>' . tep_replace_non_xml_chars(htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES)) . '</description>'", $t_str);
		$t_str = preg_replace('/<name>(.*)<\/name>/ie', "'<name>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</name>'", $t_str);
		$t_str = preg_replace('/<author>(.*)<\/author>/ie', "'<author>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</author>'", $t_str);
		$t_str = preg_replace('/<publisher>(.*)<\/publisher>/ie', "'<publisher>' . htmlspecialchars(preg_replace('/[^_\\\/\s\w\d\#\&(\)\-\[\]\.\",;]/', '', strip_tags(tep_html_entity_decode('$1'))), ENT_QUOTES) . '</publisher>'", $t_str);
		$t_str = $row['products_string'];
	    	$pos1 = strripos($t_str,'Состояние');
	   	if (($pos1 === false))
       		{

			write_to_file($pricelist_filename, $fp, $t_str . "\n");
                        $COUNTER['Offers']++;
        	}
	}
	
	
	$content = '	</offers>' . "\n" .
	'  </shop>' . "\n" .
	'</yml_catalog>' . "\n";
	
	write_to_file($pricelist_filename, $fp, $content);
	
	if ($fp) fclose($fp);

	return $COUNTER;
}

?>
