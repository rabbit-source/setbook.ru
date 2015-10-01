<?php
  if (basename(SCRIPT_FILENAME)==FILENAME_PRODUCT_INFO && $show_product_type==1) {
	$products_id = (int)$HTTP_GET_VARS['products_id'];

	$is_blacklisted = tep_check_blacklist();

	$box_info_query = tep_db_query("select blocks_name from " . TABLE_BLOCKS . " where blocks_filename = '" . tep_db_input(basename(__FILE__)) . "' and language_id = '" . (int)$languages_id . "'");
	$box_info = tep_db_fetch_array($box_info_query);

	$author_info_query = tep_db_query("select authors_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$author_info = tep_db_fetch_array($author_info_query);
	$block_authors_name = tep_get_authors_info($author_info['authors_id'], DEFAULT_LANGUAGE_ID);
	$block_products_name = tep_get_products_info($products_id, DEFAULT_LANGUAGE_ID);
	$block_product_name = (tep_not_null($block_authors_name) ? $block_authors_name . ': ' : '') . $block_products_name;
	$boxHeading = '<a href="' . tep_href_link(FILENAME_REVIEWS, 'products_id=' . $products_id) . '">' . sprintf($box_info['blocks_name'], $block_product_name) . '</a>';

	$votes_check_query = tep_db_query("select count(*) as votes, sum(reviews_vote)/count(*) as rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and reviews_ip = '" . tep_db_input($_SERVER['REMOTE_ADDR']) . "' and reviews_agent = '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "' and date_added > '" . date('Y-m-d H:i:s', (time()-60*60*24)) . "'");
	$votes_check = tep_db_fetch_array($votes_check_query);
	$rating_vote = round($votes_check['rating']*2, 0)/2;
	$solid_part_vote = 0;
	$decimal_part_vote = 0;
	list($solid_part_vote, $decimal_part_vote) = explode('.', str_replace(',', '.', $rating_vote));

	$votes_query = tep_db_query("select count(*) as votes, sum(reviews_vote)/count(*) as rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "'");
	$votes = tep_db_fetch_array($votes_query);
	$rating = round($votes['rating']*2, 0)/2;
	$solid_part = 0;
	$decimal_part = 0;
	list($solid_part, $decimal_part) = explode('.', str_replace(',', '.', $rating));

	$stars_string = '';
	$stars_string_voted = '';
	$js_string = '';
	for ($i=1; $i<=5; $i++) {
	  if ($i<=$solid_part_vote) $image_vote = 'star.gif';
	  elseif ($i==($solid_part_vote+1) && $decimal_part_vote > 0) $image_vote = 'star_half.gif';
	  else $image_vote = 'star_none.gif';
	  $stars_string .= (($votes_check['votes']<1 && $session_started) ? '<a href="#" onmouseover="this.href=\'' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_id . '&action=vote&vote=' . $i) . '\';">' . tep_image(DIR_WS_TEMPLATES_IMAGES . 'star_none.gif', sprintf(TEXT_REVIEW_VOTES_OF, $i, 5), '', '', 'onmouseover="highlightStar(' . $i . ')" onmouseout="st = setTimeout(\'blurStar()\', 100)" id="s' . $i . '"') . '</a>' : tep_image(DIR_WS_TEMPLATES_IMAGES . $image_vote, sprintf(TEXT_REVIEW_VOTES_OF, $rating_vote, 5)));
	  if ($i<=$solid_part) $image = 'star.gif';
	  elseif ($i==($solid_part+1) && $decimal_part > 0) $image = 'star_half.gif';
	  else $image = 'star_none.gif';
	  $stars_string_voted .= tep_image(DIR_WS_TEMPLATES_IMAGES . $image, sprintf(TEXT_REVIEW_VOTES_OF, $rating, 5));
	  $js_string .= (tep_not_null($js_string) ? ', ' : '') . "'" . $image_vote . "'";
	}
	if (!$is_blacklisted) echo '<div class="row_product_rating">' . ($votes['votes']>0 ? '<div style="float: right; text-align: right;">' . sprintf(TEXT_REVIEW_VOTED, (int)$votes['votes']) . '<br />' . "\n" . $stars_string_voted . '</div>' : '') . TEXT_REVIEW_VOTE . '<br />' . "\n" . $stars_string . '</div><br />' . "\n";

	ob_start();
	$reviews_string = '';
//	$reviews_string .= '<strong>' . ENTRY_REVIEWS . '</strong><br /><br />' . "\n";

	$reviews_limit = 3;
	$reviews_query = tep_db_query("select reviews_id, customers_name, reviews_text, reviews_vote, date_added, products_id, reviews_types_id from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and reviews_status = '1' and reviews_types_id >= '1' order by date_added desc limit " . $reviews_limit);
	if (tep_db_num_rows($reviews_query) > 0) {
	  while ($reviews = tep_db_fetch_array($reviews_query)) {
		if ($reviews['products_id']==$products_id) {
		  $page_numbers_query = tep_db_query("select * from " . TABLE_REVIEWS . " where reviews_types_id = '" . (int)$reviews['reviews_types_id'] . "' and reviews_status = '1' and date_added >= '" . tep_db_input($reviews['date_added']) . "'");
		  $page_numbers_count = tep_db_num_rows($page_numbers_query);
		  $page_number = ceil($page_numbers_count/MAX_DISPLAY_REVIEWS_RESULTS);

		  $reviews_description = $reviews['reviews_text'];
		  $reviews_description = str_replace('<br />', "\n", $reviews_description);
		  $reviews_description = str_replace('<p>', '', $reviews_description);
		  $reviews_description = str_replace('</p>', "\n\n", $reviews_description);
		  while (strpos($reviews_description, "\n\n")!==false) $reviews_description = trim(str_replace("\n\n", "\n", $reviews_description));
		  $reviews_short_description = tep_cut_string($reviews_description, 350) . '...';
		  $reviews_string .= '<div class="product_review" id="rfd' . $reviews['reviews_id'] . '" style="margin: 0;"><div style="float: right;">' . str_repeat(tep_image(DIR_WS_TEMPLATES_IMAGES . 'star.gif', sprintf(TEXT_REVIEW_VOTES_OF, $reviews['reviews_vote'], 5)), $reviews['reviews_vote']) . '</div><strong>' . tep_date_long($reviews['date_added']) . '</strong> ' . $reviews['customers_name'] . ' &ndash; ' . TEXT_REVIEW_OF . ' ' . ($block_authors_name ? $block_authors_name . ', ' : '') . $block_products_name . "\n" . '<div id="rsd' . $reviews['reviews_id'] . '">' . nl2br($reviews_short_description) . '</div>' . "\n" .
		  '<a href="' . tep_href_link(FILENAME_REVIEWS, 'reviews_id=' . $reviews['reviews_id'] . ($page_number>1 ? '&page=' . $page_number : '')) . '#rd' . $reviews['reviews_id'] . '"' .
//		  ' onclick="getXMLDOM(\'' . tep_href_link(FILENAME_LOADER, 'action=load_review&reviews_id=' . $reviews['reviews_id']) . '\', \'rsd' . $reviews['reviews_id'] . '\'); document.getElementById(\'rfd' . $reviews['reviews_id'] . '\').style.backgroundColor = \'#eeeeee\'; this.style.display = \'none\'; return false;" ' .
		  'class="mediumText">' . VIEW_FULL_REVIEW . '</a></div><br /><br />' . "\n\n";
		}
	  }
	  $product_reviews_count_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and reviews_status = '1' and reviews_types_id >= '1'");
	  $product_reviews_count_check = tep_db_fetch_array($product_reviews_count_check_query);
	  if ($product_reviews_count_check['total'] > $reviews_limit) {
		$reviews_string .= '<div><a href="' . tep_href_link(FILENAME_REVIEWS, 'products_id=' . $products_id) . '">' . sprintf(TEXT_ALL_PRODUCT_REVIEWS, $block_product_name) . '</a></div><br />' . "\n";
	  }
	} else {
	  $reviews_string .= TEXT_NO_PRODUCT_REVIEWS . '<br /><br />' . "\n";
	}

	$votes_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and reviews_types_id = '1' and reviews_ip = '" . tep_db_input($_SERVER['REMOTE_ADDR']) . "' and reviews_agent = '" . tep_db_input(tep_db_prepare_input($_SERVER['HTTP_USER_AGENT'])) . "' and date_added > '" . date('Y-m-d H:i:s', (time()-60*60*24)) . "'");
	$votes_check = tep_db_fetch_array($votes_check_query);
	if ($votes_check['total']<1) {
	  if (strpos(REQUEST_URI, 'action')!==false) $link = preg_replace('/action=[^\&]*/i', 'action=vote', REQUEST_URI);
	  elseif (strpos(REQUEST_URI, '?')!==false) $link = REQUEST_URI . '&amp;action=vote';
	  else $link = REQUEST_URI . '?action=vote';

	  $customer_default_email = '';
	  if (tep_session_is_registered('customer_id') && !$is_dummy_account) {
		$customer_info_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$customer_info = tep_db_fetch_array($customer_info_query);
		$customer_default_email = $customer_info['customers_email_address'];
	  }

	  $reviews_string .= '<div id="review_form" style="display: ' . (tep_not_null($HTTP_POST_VARS) ? 'block' : 'none') . ';">' . "\n";
	  if ($is_blacklisted) {
		$reviews_string .= ENTRY_BLACKLIST_REVIEW_ERROR;
	  } elseif (tep_session_is_registered('customer_id')) {
		$rating_array = array(array('id' => '5', 'text' => '5'), array('id' => '4', 'text' => '4'), array('id' => '3', 'text' => '3'), array('id' => '2', 'text' => '2'), array('id' => '1', 'text' => '1'), );
		$reviews_string .= tep_draw_form('review', $link, 'post', 'id="contact_us" onsubmit="if (document.getElementById(\'review_form\').style.display==\'none\') { document.getElementById(\'review_form\').style.display = \'block\'; return false; }"') . "\n" .
		ENTRY_REVIEW_NAME . '&nbsp;<span class="inputRequirement">*</span><br />' . "\n" .
		tep_draw_input_field('review_name', ((tep_session_is_registered('customer_id') && !isset($HTTP_POST_VARS['review_name'])) ? $customer_first_name . ' ' . $customer_last_name : '')) . '<br />' . "\n" .
		ENTRY_REVIEW_EMAIL . '&nbsp;<span class="inputRequirement">*</span><br />' . "\n" .
		tep_draw_input_field('review_email', ((tep_session_is_registered('customer_id') && !isset($HTTP_POST_VARS['review_email'])) ? $customer_default_email : '')) . '<br />' . "\n" .
		ENTRY_REVIEW_TEXT . '&nbsp;<span class="inputRequirement">*</span><br />' . "\n" .
		tep_draw_textarea_field('review_text', 'soft', 45, 8) . '<br />' . "\n" .
		ENTRY_REVIEW_STARS . '&nbsp;<span class="inputRequirement">*</span><br />' . "\n" .
		tep_draw_pull_down_menu('review_rating', $rating_array) . '<br />' . "\n" .
		ENTRY_CAPTCHA_TITLE . '&nbsp;<span class="errorText">*</span><br /><small>' . ENTRY_CAPTCHA_TEXT . '</small><br />' . "\n" .
		tep_image(tep_href_link(FILENAME_LOADER, 'action=load_captcha'), '', '', '', 'style="margin-bottom: -4px;"') . ' ' . tep_draw_input_field('captcha', '', 'size="2" maxlength="2"') . '<br /><br />' . "\n" .
		'<span class="inputRequirement">' . FORM_REQUIRED_INFORMATION . '</span><br /><br />' . "\n\n" .
		tep_image_submit('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW) . "\n" .
		'</form>' . "\n";
	  } else {
		$reviews_string .= TEXT_REVIEW_REGISTER;
	  }
	  $reviews_string .= '</div>' . "\n";
	  $reviews_string .= tep_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW, 'style="display: ' . (tep_not_null($HTTP_POST_VARS) ? 'none' : 'block') . '; cursor: pointer;" onclick="document.getElementById(\'review_form\').style.display = \'block\'; this.style.display = \'none\';"') . "\n";
	  $reviews_string .= '<br />' . "\n";
	}

	echo $reviews_string;
?>

<script language="javascript" type="text/javascript"><!--
  originals = Array('', <?php echo $js_string ?>);
  var st = setTimeout('', 0);

  function highlightStar(starNumber) {
	if (document.images) {
	  var newImage1 = new Image;
	  newImage1.src = '<?php echo DIR_WS_TEMPLATES_IMAGES; ?>star.gif';
	  var newImage2 = new Image;
	  newImage2.src = '<?php echo DIR_WS_TEMPLATES_IMAGES; ?>star_none.gif';
	}
	if (st) clearTimeout(st);
	for (i=1; i<=5; i++) {
	  if (i<=starNumber) document.getElementById('s'+i).src = newImage1.src;
	  else document.getElementById('s'+i).src = newImage2.src;
	}
  }

  function blurStar(starNumber) {
	if (blurStar.arguments.length==0) starNumber = 5;
	document.getElementById('s'+starNumber).src = '<?php echo DIR_WS_TEMPLATES_IMAGES; ?>'+originals[starNumber];
	if (starNumber>1) st = setTimeout('blurStar('+(starNumber-1)+')', 100);
  }

  preloadPopups();
//--></script>

<?php
	$boxContent = ob_get_clean();
	include(DIR_WS_TEMPLATES_BOXES . 'box1.php');
  }
?>