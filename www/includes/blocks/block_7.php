<?php
  if ($HTTP_GET_VARS['type']=='1') {
//	header('Content-type: text/html; charset=' . CHARSET . '');
  }
  ob_start();
  if ($HTTP_GET_VARS['type']=='1') {
//	echo '<?xml version="1.0" encoding="' . CHARSET . '"?' . '>' . "\n";
  }
  $cart_contents_count = $cart->count_contents();
  $cart_total_sum = $cart->show_total();

  $postpone_cart_contents_count = $postpone_cart->count_contents();
  $postpone_cart_total_sum = $postpone_cart->show_total();

  $foreign_cart_contents_count = $foreign_cart->count_contents();
  $foreign_cart_total_sum = $foreign_cart->show_total();

  if ($HTTP_GET_VARS['action']!='buy_now') echo '<div id="shopping_cart">' . "\n";

  echo '<div class="inner">' . "\n" .
  '<div class="cart_title"><a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . HEADER_TITLE_SHOPPING_CART . '</a></div>' . "\n";
  if ($cart_contents_count > 0 || $postpone_cart_contents_count > 0 || $foreign_cart_contents_count > 0) {
	if ($cart_contents_count > 0) {
	  if (strpos(HTTP_SERVER, 'owl') || strpos(HTTP_SERVER, 'insell')) {
		echo '<div class="contents" style="color: #CB0606;">' . sprintf(HEADER_TITLE_SHOPPING_CART_PRODUCTS_OWL, $cart_contents_count) . '</div>' . "\n";
	  } else {
		echo '<div class="contents"><a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '" title="' . HEADER_TITLE_SHOPPING_CART . '">' . HEADER_TITLE_SHOPPING_CART_PRODUCTS . '</a> <span>' . $cart_contents_count . ' (' . $currencies->format($cart_total_sum, true, $currency) . ')</span>' . "\n" .
		'<div class="checkout"><a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' .  tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a></div>' . "\n" . '</div>' . "\n";
	  }
	}
	if ($postpone_cart_contents_count > 0 && !strpos(HTTP_SERVER, 'owl') && !strpos(HTTP_SERVER, 'insell')) {
	  echo '<div class="contents"><a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'type=postpone') . '#postpone" title="' . HEADER_TITLE_POSTPONE_CART . '">' . HEADER_TITLE_POSTPONE_CART_PRODUCTS . '</a> <span>' . $postpone_cart_contents_count . '</span></div>' . "\n";
	}
	if ($foreign_cart_contents_count > 0 && in_array(DOMAIN_ZONE, array('ru'))) {
	  echo '<div class="contents"><a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '#foreign" title="' . HEADER_TITLE_FOREIGN_CART . '">' . HEADER_TITLE_FOREIGN_CART_PRODUCTS . '</a> <span>' . $foreign_cart_contents_count . '</span>' . "\n" .
	  '<div class="checkout"><a href="' . tep_href_link('/foreign.html', '') . '#request">' . HEADER_TITLE_SHOPPING_CART_CHECKOUT. '</a></div>' . "\n" . '</div>' . "\n";
	}
  } else {
	echo '<div class="contents">' . (strpos(HTTP_SERVER, 'insell') ? HEADER_TITLE_SHOPPING_CART_EMPTY_OWL : HEADER_TITLE_SHOPPING_CART_EMPTY) . '</div>' . "\n";
  }
  echo '</div>' . "\n";
  if ($HTTP_GET_VARS['action']!='buy_now') echo '</div>' . "\n";
  $cart_content = ob_get_clean();
//  $cart_content = str_replace('&', '&amp;', $cart_content);
//  $cart_content = str_replace('&amp;amp;', '&amp;', $cart_content);
  echo $cart_content;
?>