<?php
	require('includes/application_top.php');

	$action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

	if ($action == 'generate')
	{
		$order_id = (isset($HTTP_POST_VARS['order_id']) ? $HTTP_POST_VARS['order_id'] : '');
		$description = (isset($HTTP_POST_VARS['order_desc']) ? $HTTP_POST_VARS['order_desc'] : '');
		$ot_total_value = (isset($HTTP_POST_VARS['order_total']) ? $HTTP_POST_VARS['order_total'] : '');
		
		$sign = md5(MODULE_PAYMENT_ROBOX_LOGIN . ':' . $ot_total_value . ':' . $order_id . ':' . MODULE_PAYMENT_ROBOX_PASSWORD_1 . ':shp_prefix=aa');

		$robox_link = 'https://merchant.roboxchange.com/Index.aspx?Culture=ru&IncCurrLabel=RUR' .
		'&MrchLogin=' . urlencode(MODULE_PAYMENT_ROBOX_LOGIN) .
		'&OutSum=' . urlencode($ot_total_value) .
		'&InvId=' . $order_id .
		'&Desc=' . urlencode($description) .
		'&SignatureValue=' . urlencode($sign) .
		'&shp_prefix=aa';
	}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo 'Создание ссылки на Робокассу'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo tep_draw_form('roboxchange', 'roboxchange.php', 'action=generate'); ?>
            <td><table border="0" cellpadding="0" cellspacing="2">
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo 'Номер заказа:'; ?></td>
                <td><?php echo tep_draw_input_field('order_id', $order_id); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo 'Сумма заказа:'; ?></td>
                <td><?php echo tep_draw_input_field('order_total', $order_value); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo 'Описание'; ?></td>
                <td><?php echo tep_draw_textarea_field('order_desc', 'soft', '60', '8', $order_desc); ?></td>
              </tr>
<?php if (isset($robox_link) && ($robox_link != NULL))
{
?>               
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo 'Ссылка'; ?></td>
                <td><?php echo tep_draw_textarea_field('order_link', 'soft', '60', '8', $robox_link); ?></td>
              </tr>
<?php
}
?>              
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td colspan="2" align="right">
    				<input type="submit" value="Создать ссылку" />
				</td>
              </tr>
            </table></td>
          </form>
          </tr>
<!-- body_text_eof //-->
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
