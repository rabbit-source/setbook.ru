<?php
//Nmedia - norismedia.ru
//Версия: 1.5.23
//Редакция: 152

class subscribe 
{
	var $date, $type = 1, $limit = 10, $template_param;
	//Дата с которой нужно забрать новинки, тип подписки(по умолчанию на рубрики), кол-во новинок в письме
	var $debug = 0; 
	var $send = 0; //отправлять ли письма (в последней версии не действует)
	var $user_id = 0;//13203;//36994;//75705; 
	var $subscribers_limit = 0;//Ограничить число получателей, например первые 100
	var $counter_domain = 'setbook.';
	function subscribe()
	{
		$this->date = date("Y-m-d", time()-60*60*24*7); //Поправить при переносе
	}
	function get_detail($name_field, $id_field, $table, $id)
	{
		$query = tep_db_query("select ".$name_field." as name
		from ".$table." 
		where ".$id_field." = " . $id . "
		and ".($table == TABLE_MANUFACTURERS_INFO?'languages_id':'language_id')." = 2;");
		$detail = tep_db_fetch_array($query);
		return $detail['name'];
	}
	function get_link_detail($category_id, $type_id)
	{
		if($type_id == 1) return tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $category_id);
		if($type_id == 2) return tep_href_link(FILENAME_SERIES, 'series_id=' . $category_id);
		if($type_id == 3) return tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $category_id);
		if($type_id == 4) return tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $category_id);
	}
	function get_new_products($row)
	{
		$filter = ' AND p.products_image_exists = 1 AND p.products_price > 0';
		if($this->type == 1)
		{
			$result = tep_db_query("SELECT c.products_id as id, 
				p.products_image as image, 
				p.products_image_exists as image_exists,
				d.products_name as name, 
				d.products_description as pdesc,
				p.manufacturers_id as mid,
				p.series_id as sid,
				p.authors_id as aid,
				p.products_price as products_price,
				p.products_tax_class_id as products_tax_class_id
				FROM ".TABLE_PRODUCTS_TO_CATEGORIES." c
				JOIN ".TABLE_PRODUCTS." AS p ON p.products_id = c.products_id
				LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS d ON d.products_id = c.products_id
				WHERE c.categories_id = ".$row['id']."
				AND p.products_date_added >= '".$this->date."'
				AND d.language_id = 2
				".$filter."
				ORDER BY p.products_date_added ASC
				LIMIT 0,".$this->limit.";");
		}
		else
		{
			$params_array = array('series_id', 'authors_id', 'manufacturers_id');
			$result = tep_db_query("SELECT p.products_id as id, 
				p.products_image as image, 
				p.products_image_exists as image_exists,
				d.products_name as name, 
				d.products_description as pdesc,
				p.manufacturers_id as mid,
				p.series_id as sid,
				p.authors_id as aid,
				p.products_price as products_price,
				p.products_tax_class_id as products_tax_class_id
				FROM ".TABLE_PRODUCTS." p
				LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS d ON d.products_id = p.products_id
				WHERE p.".$params_array[$this->type-2]." = ".$row['id']."
				AND p.products_date_added >= '".$this->date."'
				AND d.language_id = 2
				".$filter."
				ORDER BY p.products_date_added ASC
				LIMIT 0,".$this->limit.";");
		}
		$products = array();
		while ($p = tep_db_fetch_array($result)) 
		{
			$p['manufacturers'] = $this->get_detail('manufacturers_name', 'manufacturers_id', TABLE_MANUFACTURERS_INFO, $p['mid']);
			$p['series'] = $this->get_detail('series_name', 'series_id', TABLE_SERIES, $p['sid']);
			$p['author'] = $this->get_detail('authors_name', 'authors_id', TABLE_AUTHORS, $p['aid']);
			$products[] = $p;
		}
		return $products;
	}
	function get_custumer($category_id)
	{
		$result = tep_db_query("SELECT s.user_id as user_id, 
		c.customers_email_address as email,
		c.customers_firstname as firstname,
		c.customers_lastname as lastname,
		t.shops_currency as currency
		FROM subscribe s
		JOIN ".TABLE_CUSTOMERS." AS c ON c.customers_id = s.user_id
		LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id	
		WHERE s.type_id = ".$this->type."
		AND s.category_id = ".$category_id."
		".($this->user_id?' AND c.customers_id = '.$this->user_id:'')."
		GROUP BY s.user_id;");
			
		return $result;
	}
	function get_subscribers($data)
	{
		if($data['orders']) $heving = 'HAVING COUNT(o.orders_id) >= '.$data['orders'];
		else $heving = '';
		if($data['site']) $filter_string .= 'AND t.shops_id = '.$data['site'];
		if($data['city']) $filter_string .= ' AND b.entry_city LIKE "%'.addslashes($data['city']).'%"';
	    //Запрос подписчиков
	    $query = "SELECT c.customers_id as user_id, 
		c.customers_email_address as email,
		c.customers_firstname as firstname,
		c.customers_lastname as lastname,
		t.shops_currency as currency,
		count(o.orders_id) as total_orders
	    from " . TABLE_CUSTOMERS . " c 
	    LEFT JOIN ".TABLE_ADDRESS_BOOK." AS b ON b.address_book_id = c.customers_default_address_id
	    LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id
	    LEFT JOIN ".TABLE_ORDERS." AS o ON o.customers_id = c.customers_id
	    WHERE c.customers_newsletter = '1'
	    ".$filter_string."
	    ".($this->user_id?' AND c.customers_id = '.$this->user_id:' OR c.customers_id = 36994')."
	    GROUP BY c.customers_id
	    ".$heving."
	    ".($this->subscribers_limit?'LIMIT 0,'.$this->subscribers_limit:'').";";
		
		$result = tep_db_query($query);
		
		/*"SELECT c.customers_id as user_id, 
		c.customers_email_address as email,
		c.customers_firstname as firstname,
		c.customers_lastname as lastname,
		t.shops_currency as currency
		FROM ".TABLE_CUSTOMERS." c
		LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id	
		WHERE c.customers_newsletter = '1'
		".($this->user_id?'AND c.customers_id = '.$this->user_id:'')."
		".($this->subscribers_limit?'LIMIT 0,'.$this->subscribers_limit:'').";");
		*/
			
		return $result;
	}
	function get_newsletters()
	{
		$result = tep_db_query("SELECT newsletters_id as id, 
		title, 
		content,
		filter
		FROM newsletters 
		WHERE status = 1
		AND locked = 1
		;");
   	 	$news = array();
		while ($n = tep_db_fetch_array($result)) 
		{
			$n['content'] = $this->news_content_template($n['content']);
			$news[] = $n;
		}	
   	 	return $news;
	}
	function update_newsletters($newsletter_id, $status)
	{
		if(!$this->debug) tep_db_query("UPDATE newsletters 
		SET date_sent = NOW(), 
		status = '".$status."' 
		WHERE newsletters_id = '" . tep_db_input($newsletter_id) . "'
		;");
	}
	function get_new_products_list($categories, $mailAPI)
	{
		global $currencies;
		$message_type = explode(':', TEXT_CATEGORY_TYPE);
		$this->set_template_param('username', '<tmpl_var name>');
		while ($category = tep_db_fetch_array($categories)) 
		{
			$products = $this->get_new_products($category);
			$users = $this->get_custumer($category['id']);
			if(count($products) > 0 && tep_db_num_rows($users) > 0)
			{
				echo iconv('cp1251', 'utf-8', 'Новинки '.$message_type[$this->type-1].' "'.$category['name'].'"'."\n");
				$content = $this->new_products_template($category, $products);
				//Устанавливаем переменные для статистики
				$this->key_data['type_id'] = $this->type; //Устанавливем статус письма(0 - обычная рассылка)
				$this->key_data['id'] = $category['id']; //Идентификатор отправляемого письма
				$this->key_data['date'] = time(); //Дата отправки
				$mailAPI->init('Новинки '.$message_type[$this->type-1].' "'.$category['name'].'"', $this->get_template_params($content));
				while ($user = tep_db_fetch_array($users)) 
				{
					$mailAPI->set_tmpl_var('name', $user['firstname']);
					foreach($products as $k => $p)
					{
						$cost = $currencies->display_price($p['products_price'], tep_get_tax_rate($p['products_tax_class_id']), 1, true, $user['currency']);
						$mailAPI->set_tmpl_var('cost'.$k, $cost);
					}
					$mailAPI->add_recipient($user['firstname'].' '.$user['lastname'], $user['email']);
				}
				$mailAPI->send();
			}
		}
	}
	function new_products_template($category, $products)
	{
		global $currencies;
		ob_start();
		include(DIR_FS_CATALOG . 'includes/templates/setbook/mail/standart_products.php');
		$text = ob_get_clean();
		$text = str_replace("\n", '', $text);
		
		return $text;
		
	}
	function mail_counter($content)
	{
		define(COUNTER_DOMAIN, $this->counter_domain);
		$key_data = base64_encode(serialize($this->key_data));
		define(COUNTER_KEY, $key_data);
		if (!function_exists("href")) 
		{ 
			function href($matches)
			{
			 	$url = parse_url($matches[1]);
			 	$key_data = COUNTER_KEY;
			 	if(strpos($url['host'], COUNTER_DOMAIN) === false) $href = $matches[1];
			 	else $href = $url['scheme'].'://'.$url['host'].$url['path'].($url['query'] == ''?'?from=newlist&s='.$key_data:'?'.$url['query'].'&from=newlist&s='.$key_data);
			 	
			 	return 'href="'.$href.'"';
			}
		}
		$content = preg_replace_callback("'href=\"(.[^\"]*)\"'si", "href", $content);
		return $content;
	}
	function news_content_template($content)
	{
		ob_start();
		include(DIR_FS_CATALOG . 'includes/templates/setbook/mail/newsletter.php');
		$text = ob_get_clean();
		$text = str_replace("\n", '', $text);
		return $text;
	}
	function set_template_param($param, $value)
	{
		$this->template_param[$param] = $value;
	}
	function get_template_params($content)
	{
		$content = $this->mail_counter($content);
		if(count($this->template_param) > 0)
		{
			foreach($this->template_param as $key => $val)
			{
				$content = str_replace('{{'.$key.'}}', $val, $content);
			}
			return $content;
		}
		else return $content;
		
	}
	function send_mail($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $html)
	{
		$from_email_address = trim(implode('', array_map('trim', explode("\n", $from_email_address))));
		$from_email_name = trim(implode('', array_map('trim', explode("\n", $from_email_name))));
		$text = tep_html_entity_decode($text);
		$html = $this->get_template_params(trim($html));
		
		if($this->debug)
		{
			echo $email_subject;
			echo $html;
		}

		if (tep_not_null($to_email_address)) 
		{
			// Instantiate a new mail object
			$message = new email;
			
			// Build the text version
			$text = strip_tags($html);
			$message->add_html($html, $text);
			
			// Send message
			$message->build_message();
			if($this->send)
				$message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
		}
		
	}
}

class subscribe_statistic extends subscribe
{
	var $types_array = array('Тематика','Серия', 'Автор', 'Издательство');
	function get_main_statistic($date = 0)
	{
		if($date)
		{
			if(is_array($date))
			{
				$filter = ' AND ci.customers_info_date_account_created >= "'.$date[0].'"';
				$filter .= ' AND ci.customers_info_date_account_created <= "'.$date[1].'"';
				
				$sub_filter = ' AND date_created >= "'.$date[0].'"';
				$sub_filter .= ' AND date_created <= "'.$date[1].'"';
			}
			else
			{
				$filter = ' AND DATE(ci.customers_info_date_account_created) = "'.$date.'"';
				$sub_filter = ' AND date_created = "'.$date.'"';
			}
		}
		else
		{
			$result = tep_db_query("SELECT COUNT(*) AS clients
			FROM ".TABLE_CUSTOMERS.";");
			$r = tep_db_fetch_array($result);
			$statistic['clients'] = $r['clients'];
		}
		
		//Запрос подписчиков
	    $query = "SELECT COUNT(c.customers_id) AS subsribers
		FROM " . TABLE_CUSTOMERS . " c 
	    JOIN ".TABLE_CUSTOMERS_INFO." AS ci ON ci.customers_info_id = c.customers_id
	    WHERE c.customers_newsletter = '1'
	    ".$filter.";";
	    
		$result = tep_db_query($query);
		$r = tep_db_fetch_array($result);
		$statistic['subsribers'] = $r['subsribers'];
		
		$result = tep_db_query("SELECT COUNT(DISTINCT user_id) AS news_subsribers
		FROM subscribe
		WHERE 1
		".$sub_filter.";");
		$r = tep_db_fetch_array($result);
		$statistic['news_subsribers'] = $r['news_subsribers'];
		
		$result = tep_db_query("SELECT COUNT(*) AS count
		FROM subscribe
		WHERE 1
		".$sub_filter.";");
		$r = tep_db_fetch_array($result);
		$statistic['count'] = $r['count'];
	
		return $statistic;
	}
	function get_name_detail($category_id, $type_id)
	{
		if($type_id == 1) return $this->get_detail('categories_name', 'categories_id', TABLE_CATEGORIES_DESCRIPTION, $category_id);
		if($type_id == 2) return $this->get_detail('series_name', 'series_id', TABLE_SERIES, $category_id);
		if($type_id == 3) return $this->get_detail('authors_name', 'authors_id', TABLE_AUTHORS, $category_id);
		if($type_id == 4) return $this->get_detail('manufacturers_name', 'manufacturers_id', TABLE_MANUFACTURERS_INFO, $category_id);
	}
	function get_popular_subsribe($type_id = 0)
	{
		$result = tep_db_query("SELECT category_id, type_id, COUNT(*) AS count
		FROM subscribe
		".($type_id?"WHERE type_id = ".$type_id:'')."
		GROUP BY category_id, type_id
		ORDER BY count DESC
		LIMIT 0,10;");
		$popular = array();
		while($r = tep_db_fetch_array($result)) 
		{
			
			$r['name'] = $this->get_name_detail($r['category_id'], $r['type_id']);
			$popular[] = $r;
		}
		//print_r($popular);
	
		return $popular;
	}
}

?>