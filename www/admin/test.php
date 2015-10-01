<?php
  require('includes/application_top.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<?php
$products_query = tep_db_query("select products_id, products_image from " . TABLE_PRODUCTS . " where products_image_exists = '1'");
while ($products = tep_db_fetch_array($produccts_query)) {
  if (!file_exists(DIR_FS_CATALOG_IMAGES . 'thumbs/' . $products['products_image'])) tep_db_query("update " . TABLE_PRODUCTS . " set products_image = '', products_image_exists = '0' where products_id = '" . (int)$products['products_id'] . "'");
}
?>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>