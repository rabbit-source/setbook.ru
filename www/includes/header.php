<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title><?php echo PAGE_TITLE; ?></title>
  <meta name="keywords" content="<?php echo META_KEYWORDS; ?>" />
  <meta name="description" content="{<?php echo META_DESCRIPTION; ?>" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo CHARSET; ?>" />
  <meta name="language" content="<?php echo LANGUAGE; ?>" />
  <style type="text/css">
	@import url("<?php echo DIR_WS_TEMPLATES_STYLES; ?>modern.css");
	@import url("<?php echo DIR_WS_TEMPLATES_STYLES; ?>stylesheet.css");
  </style>
  <script language="javascript" src="<?php echo DIR_WS_CATALOG . DIR_WS_JAVASCRIPT; ?>popups.js" type="text/javascript"></script>
</head>
<body>
<?php @include(DIR_WS_INCLUDES . 'warnings.php'); ?>
<!-- Page BOF-->
<a name="top"></a>
<div id="page">
<!-- Header BOF -->
  <div id="header" class="clear">
	<div id="logo"><a href="<?php echo DIR_WS_CATALOG; ?>" title="<?php echo STORE_NAME; ?>"><img src="<?php echo DIR_WS_TEMPLATES_IMAGES; ?>logo.gif" border="0" alt="<?php echo STORE_NAME; ?>" /></a></div>
	<div id="mainmenu">
	  <div class="menubar">
		<ul class="clear">
		  <li class="item1"><a href="#">Главная</a></li>
		  <li class="item2"><a href="#">Новинки</a></li>
		  <li class="item3"><a href="#">Хиты продаж</a></li>
		  <li class="item4"><a href="#">Мы рекомендуем</a></li>
		  <li class="item5"><a href="#">Оплата</a></li>
		  <li class="item6"><a href="#">Доставка</a></li>
		</ul>
	  </div>
	</div>
  </div>
<!-- Header EOF-->
<!-- Shortcuts BOF -->
  <div id="shortcuts" class="clear">
	<div class="center">
	  <div id="search"><form action="{{DIR_WS_CATALOG}}advanced_search_result.php" method="get"><input id="s-field" name="keywords" type="text" /><input id="s-submit" type="submit" value="Искать" /></form></div>
	  <div id="account"><?php
  if (tep_session_is_registered('customer_id')) {
	echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">Личный кабинет</a> | <a href="' . tep_href_link(FILENAME_LOGOFF, '', 'SSL') . '">Выход [X]</a>';
  } else {
	echo '<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">Авторизация</a> | <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">Регистрация</a>';
  }
?></div>
	  <div id="shopping_cart">
		<div class="green">Ваша корзина <a href="<?php echo DIR_WS_CATALOG; ?>shopping_cart.php">Ваша корзина</a></div>
		<div class="contents">товаров: 5 (на сумму 1523,12руб.)</div>
	  </div>
	</div>
  </div>
<div id="breadcrumb"><?php if (sizeof($breadcrumb->_trail) > 1) echo $breadcrumb->trail(' &raquo; '); ?></div>
<!-- Shortcuts EOF -->