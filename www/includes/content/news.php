<?php
  if ($news_depth=='news') {
	$news_info_query = tep_db_query("select * from " . TABLE_NEWS . " where news_id = '" . (int)$news_id . "'");
	$news_info = tep_db_fetch_array($news_info_query);
//	echo '<p><strong>' . tep_date_long($news_info['date_added']) . ' - ' . $news_info['news_name'] . '</strong></p>' . "\n";
	if (tep_not_null($news_info['news_image']) && file_exists(DIR_FS_CATALOG . 'images/' . $news_info['news_image'])) {
	  echo tep_image(DIR_WS_IMAGES . $news_info['news_image'], $news_info['news_name'], '', '', 'align="right" class="one_image"');
	}
	echo $news_info['news_description'];

//	if ($customer_id==2) {
	  ob_start();
?>
<!-- AddThis Button BEGIN -->
<link href="http://stg.odnoklassniki.ru/share/odkl_share.css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="http://stg.odnoklassniki.ru/share/odkl_share.js"></script>
<div style="background: url(<?php echo DIR_WS_TEMPLATES_IMAGES; ?>bg_dotted.gif) top left repeat-x;">
<div style="padding: 10px 0; background: url(<?php echo DIR_WS_TEMPLATES_IMAGES; ?>bg_dotted.gif) bottom left repeat-x;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr align="center">
	<td width="30"><a class="addthis_button_facebook" title="Facebook"></a></td>
	<td width="30"><a class="addthis_button_twitter" title="Twitter"></a></td>
	<td width="30"><a class="addthis_button_vk" title="VKontakte"></a></td>
	<td width="30"><a class="addthis_button_googlebuzz" title="Google Buzz"></a></td>
	<td width="30"><a class="addthis_button_myspace" title="My Space"></a></td>
	<td width="30"><a class="addthis_button_livejournal" title="Livejournal"></a></td>
	<td width="30"><a class="odkl-klass-s" style="cursor: pointer;" href="<?php echo HTTP_SERVER . PHP_SELF; ?>" onclick="ODKL.Share(this); return false;" title="Odnoklassniki"></a></td>
	<td width="30"><a class="addthis_button_email" title="Email"></a></td>
	<td width="100"><a class="addthis_counter addthis_pill_style"></a></td>
  </tr>
</table>
</div>
</div>
<script language="javascript" type="text/javascript"><!--
  var addthis_config = {
	data_track_clickback: true,
	ui_click: true
  };
  var addthis_share = {
    url_transforms : {
        clean: true,
        remove: ['PHPSESSID']
    }
  }
//--></script>
<script language="javascript" type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=setbook"></script>
<!-- AddThis Button END -->
<?php
	  $lc_text .= ob_get_clean();
	  echo $lc_text;
//	}

	if (tep_not_null($news_info['news_products'])) {
	  echo '<br clear="all" />' . "\n";
	  $products_to_search = explode("\n", $news_info['news_products']);
	  include(DIR_WS_MODULES . 'product_listing.php');
	}
  } else {
	if (isset($HTTP_GET_VARS['by_theme'])) {
	  echo $page['pages_description'];
	  $news_types_query = tep_db_query("select news_types_id, news_types_path, news_types_name, news_types_description from " . TABLE_NEWS_TYPES . " where news_types_id in (" . implode(', ', $active_news_types_array) . ") and language_id = '" . (int)$languages_id . "' order by sort_order, news_types_name");
	  while ($news_types = tep_db_fetch_array($news_types_query)) {
		echo '<p><a href="' . tep_href_link(FILENAME_NEWS, 'type=' . $news_types['news_types_path'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_NEWS_RSS, '', '', 'style="margin: 0 4px -4px 0;"') . '</a><a href="' . tep_href_link(FILENAME_NEWS, 'tPath=' . $news_types['news_types_id']) . '"><strong>' . $news_types['news_types_name'] . '</strong></a>' . (tep_not_null($news_types['news_types_description']) ? '<br />' . "\n" . $news_types['news_types_description'] : '') . '</p>' . "\n\n";
	  }
	} else {
	  if (tep_not_null($news_type_id)) {
		$news_categories_array = array();
		$news_categories_query = tep_db_query("select news_id, news_category from " . TABLE_NEWS . " where news_types_id = '" . (int)$news_type_id . "' and news_status = '1' and news_category <> '' and language_id = '" . (int)$languages_id . "' group by news_category order by news_category");
		while ($news_categories = tep_db_fetch_array($news_categories_query)) {
		  $news_categories_array[] = array('id' => $news_categories['news_id'], 'text' => $news_categories['news_category']);
		}

		if (strpos($HTTP_GET_VARS['category'], ',')!==false) {
		  $multiple_news_categories_id = $HTTP_GET_VARS['category'];
		  $news_category_info_query = tep_db_query("select news_category from " . TABLE_NEWS . " where news_id in ('" . implode("', '", array_map('tep_string_to_int', explode(',', $multiple_news_categories_id))) . "') and news_status = '1' and language_id = '" . (int)$languages_id . "' order by news_category");
		  if (tep_db_num_rows($news_category_info_query)) {
			$multiple_news_categories_text = '';
			$k = 0;
			while ($news_category_info = tep_db_fetch_array($news_category_info_query)) {
			  $multiple_news_categories_text .= ($k>0 ? ', ' : '') . $news_category_info['news_category'];
			  $k ++;
			}
			if ($k>1) $news_categories_array = array_merge(array(array('id' => $multiple_news_categories_id, 'text' => $multiple_news_categories_text)), $news_categories_array);
		  }
		}
		$news_categories_array = array_merge(array(array('id' => '', 'text' => PULL_DOWN_DEFAULT)), $news_categories_array);

		echo $news_type_info['news_types_description'] . "\n" .
		'<p>' . ((sizeof($news_categories_array)>1 && defined('TEXT_NEWS_CATEGORY')) ? tep_draw_form('news_category', tep_href_link(FILENAME_NEWS, 'tPath=' . $news_type_id), 'GET') . '<div style="float: right;">' . TEXT_NEWS_CATEGORY . ' ' . tep_draw_pull_down_menu('category', $news_categories_array, '', 'onchange="this.form.submit();"') . '</form></div>' : '') . '&nbsp;<a href="' . tep_href_link(FILENAME_NEWS, 'type=' . $news_type_info['news_types_path'] . '&view=rss') . '">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'rss.gif', TEXT_NEWS_RSS, '', '', 'style="float: left;"') . TEXT_NEWS_RSS_TEXT . '</a></p>' . "\n";
	  }

	  $listing_sql = "select news_id, news_name, news_description, news_image, date_added, news_types_id from " . TABLE_NEWS . " where language_id = '" . (int)$languages_id . "' and news_status = '1'";
	  if (tep_not_null($news_type_id)) $listing_sql .= " and news_types_id = '" . (int)$news_type_id . "'";
	  if (tep_not_null($news_year)) $listing_sql .= " and year(date_added) = '" . (int)$news_year . "'";
	  if (tep_not_null($news_month)) $listing_sql .= " and month(date_added) = '" . (int)$news_month . "'";
	  if (isset($HTTP_GET_VARS['category'])) {
		if (strpos($HTTP_GET_VARS['category'], ',')!==false) {
		  $news_categories_array = array_map('tep_string_to_int', explode(',', $HTTP_GET_VARS['category']));
		} else {
		  $news_categories_array = array((int)$HTTP_GET_VARS['category']);
		}
		$news_category_info_query = tep_db_query("select news_category from " . TABLE_NEWS . " where news_id in ('" . implode("', '", $news_categories_array) . "') and language_id = '" . (int)$languages_id . "'");
		if (tep_db_num_rows($news_category_info_query)) {
		  $listing_sql .= " and (";
		  $k = 0;
		  while ($news_category_info = tep_db_fetch_array($news_category_info_query)) {
			$listing_sql .= ($k>0 ? " or " : "") . "news_category = '" . tep_db_input($news_category_info['news_category']) . "'";
			$k ++;
		  }
		  $listing_sql .= ")";
		}
	  }
	  $listing_sql .= " order by date_added desc";

	  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_NEWS_RESULTS, 'news_id');

	  if ($listing_split->number_of_rows > 0) {
		$listing_query = tep_db_query($listing_split->sql_query);
		while ($listing = tep_db_fetch_array($listing_query)) {
		  $news_type_info_query = tep_db_query("select news_types_name from " . TABLE_NEWS_TYPES . " where news_types_id = '" . (int)$listing['news_types_id'] . "' and language_id = '" . (int)$languages_id . "'");
		  $news_type_info = tep_db_fetch_array($news_type_info_query);
		  $news_description = $listing['news_description'];
		  $news_description = str_replace('<p>', '', $news_description);
		  $news_description = str_replace(array('<br />', '<br>', '</p>'), "\n", $news_description);
		  $news_description = trim(preg_replace("/[\r\n]+/", "\n", $news_description));
		  $news_short_description = tep_cut_string($news_description, 200);
		  if (strlen($news_description) > 200) $news_short_description .= '...';
		  $news_image = '';
		  if (tep_not_null($listing['news_image'])) {
			$news_image = str_replace('news/', 'news/thumbs/', $listing['news_image']);
			if (!file_exists(DIR_FS_CATALOG . 'images/' . $news_image)) $news_image = '';
		  }
		  $news_link = tep_href_link(FILENAME_NEWS, 'news_id=' . $listing['news_id'] . ($news_type_id>0 ? '&tPath=' . $news_type_id : ''));
		  echo '<br clear="right" />' . "\n" .
			   '<strong><a href="' . $news_link . '">' . $listing['news_name'] . '</a></strong><br />' . "\n" .
			   (tep_not_null($news_image) ? '<a href="' . $news_link . '">' . tep_image(DIR_WS_IMAGES . $news_image, $listing['news_name'], '', '', 'class="one_image"') . '</a>' : '') .
			   ($news_type_id==0 ? '<a href="' . tep_href_link(FILENAME_NEWS, 'tPath=' . $listing['news_types_id']) . '" class="mediumText">' . $news_type_info['news_types_name'] . '</a>' . "\n" : '') .
			   '<div class="smallText">' . tep_date_long($listing['date_added']) . '</div>' . "\n" .
			   '' . nl2br($news_short_description) . '<br />' . "\n";
		}
?>
	<br clear="all" /><div id="listing-split">
	  <div style="float: left;"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></div>
	  <div style="text-align: right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_NEWS_RESULTS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
	</div>
<?php
	  } else {
		echo '<p>' . TEXT_NO_NEWS . '</p>';
	  }
	}
  }
?>