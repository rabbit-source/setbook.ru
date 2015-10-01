<?php
  echo $page['pages_description'];
?>
	<form class="form-div">
<?php
  if (tep_count_customer_orders() > 0) {
?>
	<fieldset>
	<legend><?php echo MY_ORDERS_TITLE; ?></legend>
	<div><strong><?php echo MY_ORDERS_LAST_ORDERS_TEXT; ?><br/></strong>
	<?php
    $orders_query = tep_db_query("select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_country, o.billing_name, o.billing_country, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by orders_id desc limit 3");
    while ($orders = tep_db_fetch_array($orders_query)) {
      if (tep_not_null($orders['delivery_name'])) {
        $order_name = $orders['delivery_name'];
        $order_country = $orders['delivery_country'];
      } else {
        $order_name = $orders['billing_name'];
        $order_country = $orders['billing_country'];
      }
	  echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL') . '"> #' . $orders['orders_id'] . ', ' . tep_date_long($orders['date_purchased']) . ', ' . $orders['order_total'] . '</a><br />' . "\n";
    }
	echo '<br /><a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '"><strong>' . MY_ORDERS_VIEW . '</a></strong><br/>' . MY_ORDERS_VIEW_TEXT; ?></div>
	</fieldset>
<?php
  }
?>	
	<fieldset>
	<legend><?php echo MY_ACCOUNT_TITLE; ?></legend>
	<div><?php echo '<strong><a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a></strong><br />' . MY_ACCOUNT_INFORMATION_TEXT; ?><br /><br />
	  <?php echo '<strong><a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a></strong><br />' . MY_ACCOUNT_ADDRESS_BOOK_TEXT; ?><br /><br />
	  <?php echo '<strong><a href="' . tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a></strong><br />' . MY_ACCOUNT_PASSWORD_TEXT; ?></div>
	</fieldset>
<?php
  if (tep_not_null(ENTRY_NEWSLETTER)) {
?>
	<fieldset>
	<legend><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></legend>
	<div><?php echo '<strong><a href="' . tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a></strong><br/>' . EMAIL_NOTIFICATIONS_NEWSLETTERS_TEXT; ?></div>
	
	<div><?php echo '<strong><a href="' . tep_href_link('account_subscribe.php', '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEW . '</a></strong><br/>' . EMAIL_NOTIFICATIONS_NEW_TEXT; ?></div>
	</fieldset>
<?php
  }

  if (tep_db_table_exists(DB_DATABASE, TABLE_BOARDS)) {
  $advs_check_query = tep_db_query("select count(*) as total from " . TABLE_BOARDS . " where customers_id = '" . (int)$customer_id . "' and parent_id = '0'");
  $advs_check = tep_db_fetch_array($advs_check_query);
  if ($advs_check['total'] > 0) {
?>
	<fieldset>
	<legend><?php echo MY_BOARDS_TITLE; ?></legend>
	<div><?php echo ($advs_check['total']>0 ? '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, '', 'SSL') . '">' . MY_BOARDS_ADVS . '</a><br />' : '') . '<a href="' . tep_href_link(FILENAME_ACCOUNT_BOARDS, 'action=new', 'SSL') . '">' . MY_BOARDS_NEW . '</a>'; ?></div>
	</fieldset>
<?php
  }

  if (tep_not_null(ENTRY_WISHLIST)) {
	$wls_search_parameters = array();
	$wls_query = tep_db_query("select * from " . TABLE_WISHLISTS . " where customers_id = '" . (int)$customer_id . "'");
	$wls_check = tep_db_num_rows($wls_query);
	$wls = tep_db_fetch_array($wls_query);
	if (tep_not_null($wls['wishlists_search_params'])) $wls_search_parameters = unserialize($wls['wishlists_search_params']);
	$wls_categories = $wls_search_parameters['categories'];
	if (!is_array($wls_categories)) $wls_categories = array();
	$wls_tree = tep_get_category_level(0, 0, 1, $wls_categories, false);
?>
	<fieldset>
	<legend><?php echo MY_WISHLIST_TITLE; ?></legend>
	<div><?php echo '<strong><a href="' . tep_href_link(FILENAME_ACCOUNT_WISHLIST, '', 'SSL') . '">' . MY_ACCOUNT_WISHLIST . '</a></strong>' . (tep_not_null(MY_ACCOUNT_WISHLIST_TEXT) ? '<br />' . MY_ACCOUNT_WISHLIST_TEXT : '') . '<br /><br />' . (tep_not_null($wls_tree) ? $wls_tree : MY_ACCOUNT_WISHLIST_EMPTY); ?></div>
	</fieldset>
<?php
  }
  }
?>
	</form>

