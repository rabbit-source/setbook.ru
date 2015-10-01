<?php
  if (empty($content_type) || (int)$content_id == '0') {
	$content_id = $page['pages_id'];
	$content_type = 'page';
  }
  $page_parse_start_time = PAGE_PARSE_START_TIME;

  if ((int)$templates_id==0) $templates_id = tep_get_template_id($page['pages_id'], 'page');

  if (in_array($content_type, array('author', 'category', 'manufacturer', 'product', 'type'))) $metatags_languages_id = DEFAULT_LANGUAGE_ID;
  else $metatags_languages_id = $languages_id;

  $metatags_info_query = tep_db_query("select * from " . TABLE_METATAGS . " where content_type = '" . tep_db_input($content_type) . "' and content_id = '" . (int)$content_id . "' and language_id = '" . (int)$metatags_languages_id . "'");
  $metatags_info = tep_db_fetch_array($metatags_info_query);
  define('PAGE_TITLE', $metatags_info['metatags_page_title']);
  define('META_KEYWORDS', $metatags_info['metatags_keywords']);
  define('META_DESCRIPTION', $metatags_info['metatags_description']);
  define('TITLE', $metatags_info['metatags_title']);

  $template_query = tep_db_query("select templates_filename from " . TABLE_TEMPLATES . " where templates_id = '" . (int)$templates_id . "' limit 1");
  $template = tep_db_fetch_array($template_query);
  $constants = get_defined_constants();
  ob_start();
  require(DIR_WS_TEMPLATES . basename($template['templates_filename']));
  $html = ob_get_clean();

  $to_replace = '';
  if (substr($javascript, -4)=='.php') {
	ob_start();
	include(DIR_FS_CATALOG . DIR_WS_JAVASCRIPT . $javascript);
	$to_replace .= ob_get_clean();
  } elseif (substr($javascript, -3)=='.js') {
	$to_replace .= '<script language="javascript" src="' . DIR_WS_CATALOG . DIR_WS_JAVASCRIPT . $javascript . '" type="text/javascript"></script>' . "\n";
  }
  if (tep_not_null($to_replace)) {
	$html = str_replace('</head>', $to_replace . '</head>', $html);
  }

  $included_blocks = array();

  $all_parts = explode('}}', $html);
  reset($all_parts);
  while (list(, $part) = each($all_parts)) {
	list($other_part, $match) = explode('{{', $part);
	$content_string = '';
	if (defined($match)) {
	  $content_string = $constants[$match];
	} elseif ($match=='content') {
	  ob_start();
	  if (tep_not_null($content)) {
		if (file_exists(DIR_WS_CONTENT . basename($content))) {
		  include(DIR_WS_CONTENT . basename($content));
		}
	  }
	  $content_string = ob_get_clean();
	} elseif ($match=='warnings') {
	  ob_start();
	  @include(DIR_WS_INCLUDES . 'warnings.php');
	  $content_string = ob_get_clean();
	} else {
	  $blocks_to_include[$match] = array();

	  $field_type = 'textarea_text';
	  $type_query = tep_db_query("select blocks_types_id, blocks_types_field from " . TABLE_BLOCKS_TYPES . " where blocks_types_identificator = '" . tep_db_input($match) . "' limit 1");
	  $type = tep_db_fetch_array($type_query);
	  if (tep_db_num_rows($type_query) > 0) {
		$field_type = $type['blocks_types_field'];
		$blocks_query = tep_db_query("select distinct blocks_id from " . TABLE_BLOCKS . " where blocks_types_id = '" . (int)$type['blocks_types_id'] . "' and blocks_status = '1'");
		while ($blocks = tep_db_fetch_array($blocks_query)) {
		  if (!in_array($blocks['blocks_id'], $included_blocks)) {
			$blocks_to_include[$match][] = $blocks['blocks_id'];
			$included_blocks[] = $blocks['blocks_id'];
		  }
		}
		if ($match=='counters' && basename(SCRIPT_FILENAME)==FILENAME_ERROR_404) $blocks_to_include[$match] = array();
	  } else {
		$block_query = tep_db_query("select blocks_id from " . TABLE_BLOCKS . " where blocks_identificator = '" . tep_db_input($match) . "' and language_id = '" . (int)$languages_id . "' and blocks_status = '1'");
		$block = tep_db_fetch_array($block_query);
		if (!in_array($block['blocks_id'], $included_blocks)) {
		  $blocks_to_include[$match][] = $block['blocks_id'];
		  $included_blocks[] = $block['blocks_id'];
		}
	  }

	  $default_blocks = array();
	  if ($match=='column_left') {
		if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && strpos(PHP_SELF, DIR_WS_CATALOG . 'from_abroad/')!==false) {
		  $default_blocks[] = 32;
		} elseif (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && $current_information_id!=1 || basename(SCRIPT_FILENAME)==FILENAME_SITEMAP) {
		  $default_blocks[] = 2;
		} elseif ($show_product_type==2) {
		  $default_blocks[] = 23;
		  $default_blocks[] = 47;
		} elseif ($show_product_type==3) {
		  $default_blocks[] = 30;
		  $default_blocks[] = 47;
		} elseif ($show_product_type==4) {
		  $default_blocks[] = 42;
		  $default_blocks[] = 47;
		} elseif ($show_product_type==5) {
		  $default_blocks[] = 31;
		  $default_blocks[] = 47;
		} elseif ($show_product_type==6) {
		  $default_blocks[] = 44;
		  $default_blocks[] = 47;
		} elseif (in_array(basename(SCRIPT_FILENAME), array(FILENAME_SPECIALS, FILENAME_REVIEWS, FILENAME_HOLIDAY)) || (basename(SCRIPT_FILENAME)==FILENAME_NEWS && ($news_type_id>0 || $HTTP_GET_VARS['view']=='by_theme')) ) {
		  $default_blocks[] = 26;
		} elseif (basename(SCRIPT_FILENAME)==FILENAME_BOARDS) {
		  $default_blocks[] = 58;
		} elseif (basename(SCRIPT_FILENAME)==FILENAME_NEWS) {
		  $default_blocks[] = 8;
		} elseif (basename(SCRIPT_FILENAME)==FILENAME_CATEGORIES || (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO && $show_product_type==1) ) {
		  $default_blocks[] = 1;
		  $default_blocks[] = 47;
		}
	  }
	  if (sizeof($default_blocks) == 0) {
		if (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) {
		} else {
		  $default_blocks[] = 1;
		  $default_blocks[] = 47;
		}
	  }
	  $i = 0;
	  $blocks_query_raw = "select blocks_id, blocks_name, if('" . $field_type . "'='textarea_text', blocks_description, blocks_description_short) as blocks_description, blocks_filename," . (sizeof($default_blocks)>0 ? " if((blocks_id in ('" . implode("', '", $default_blocks) . "')), 0, sort_order) as" : "") . " sort_order from " . TABLE_BLOCKS . " where blocks_id in ('" . implode("', '", $blocks_to_include[$match]) . "') and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_name";
	  $blocks_query = tep_db_query($blocks_query_raw);
	  $temp_string = '';
	  while ($blocks = tep_db_fetch_array($blocks_query)) {
		if ($i==0) {
		  if ($blocks['sort_order'] > 0) $default_blocks = array();
		  $i ++;
		}
		if (tep_not_null($blocks['blocks_filename']) && file_exists(DIR_WS_BLOCKS . basename($blocks['blocks_filename']))) {
		  ob_start();
		  $boxHeading = $blocks['blocks_name'];
		  include(DIR_WS_BLOCKS . basename($blocks['blocks_filename']));
		  $temp_string .= ob_get_clean();
		} else {
		  $temp_string .= $blocks['blocks_description'];
		}
		if ($customer_id==2 && $HTTP_GET_VARS['action']=='show_load_stat') {
		  $load_time = round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', $page_parse_start_time))), 3);
		  if ($load_time>=0.03) echo 'block[' . $match . '][' . $blocks['blocks_id'] . '] - ' . $load_time . '<br>';
		  $page_parse_start_time = microtime();
		}
	  }
	  $content_string = $temp_string;
	}
	if ($customer_id==2 && $HTTP_GET_VARS['action']=='show_load_stat' && tep_not_null($match)) {
	  $load_time = round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', $page_parse_start_time))), 3);
	  if ($load_time>=0.03) echo 'block[' . $match . '] - ' . $load_time . '<br>';
	  $page_parse_start_time = microtime();
	}
	if ($match!='content') {
	  $content_string = str_replace("\'", "'", $content_string);
	  $content_string = str_replace('\"', '"', $content_string);
	}
//	$html = str_replace('{{' . $match . '}}', $content_string, $html);
	if ($customer_id==2 && $HTTP_GET_VARS['action']=='show_load_stat') {
	} else {
	  echo $other_part . $content_string;
	}
  }

//  echo $html;

  if (SHOW_PAGE_GENERATION_TIME=='true' && $show_parse_time!==false) echo '<div align="center"><font color="#FFFFFF">' . round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', PAGE_PARSE_START_TIME))), 2) . '</font></div>';
// close session (store variables)
  tep_session_close();
  tep_db_close();

  //tep_log_ex('Process stops: pid=[' . getmypid() . ']');
  
	//if ($_SERVER['REMOTE_ADDR']=='213.138.80.140')
	/*{
		//echo 'QueryCount: '.$queries_count.'   QueryDuration:'.$queries_duration;
		$execution_time = round((array_sum(explode(' ', microtime())) - array_sum(explode(' ', PAGE_PARSE_START_TIME))), 2);
		
		$time_file = @fopen('logs/times.txt', 'a');
		if ($time_file)
		{
			//echo 'YES';
			fwrite($time_file, ''.date('h:i:s').' '.$queries_count.' '.$execution_time.' '.$queries_duration.' '.$_SERVER["REQUEST_URI"].PHP_EOL);
			fclose($time_file);
		}
		else
		{
			//echo 'NO';
		}
	}*/
?>