<?php
	require('includes/application_top.php');
	require(DIR_WS_CLASSES . 'currencies.php');
	
	$action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

	if ($action == 'generate')
	{
		$order_id = (isset($HTTP_POST_VARS['order_id']) ? $HTTP_POST_VARS['order_id'] : '');
		$description = (isset($HTTP_POST_VARS['order_desc']) ? $HTTP_POST_VARS['order_desc'] : '');
		$ot_total_value = (isset($HTTP_POST_VARS['order_total']) ? $HTTP_POST_VARS['order_total'] : '');

		$currencies = new currencies();
		
		//$ot_total_value_usd = floor(($ot_total_value / $currencies->get_value('UAH')) * $currencies->get_value(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY), $currencies->get_decimal_places(MODULE_PAYMENT_INTERKASSA_DEFAULT_CURRENCY));
		$ot_total_value_usd = number_format(tep_round($ot_total_value/* * $currencies->get_value('UAH')*/, $currencies->get_decimal_places('UAH')), $currencies->get_decimal_places('UAH'), '.', '');
		$ot_total_value_usd = str_replace(',', '.', $ot_total_value_usd);
		
		$sign = md5(MODULE_PAYMENT_INTERKASSA_LOGIN . ':' . $ot_total_value_usd . ':' . $order_id . ':' . '' . ':' . tep_session_id() . ':' . MODULE_PAYMENT_INTERKASSA_PASSWORD);
		$interkassa_link = (MODULE_PAYMENT_INTERKASSA_MODE=='Test' ? 'https://test.interkassa.com/lib/payment.php' : 'https://interkassa.com/lib/payment.php') .
			'?ik_shop_id=' . urlencode(MODULE_PAYMENT_INTERKASSA_LOGIN) .
			'&ik_payment_amount=' . urlencode($ot_total_value_usd) .
			'&ik_payment_id=' . $order_id .
			'&ik_payment_desc=' . urlencode($description) .
			'&ik_baggage_fields=' . tep_session_id() .
			'&ik_sign_hash=' . urlencode($sign);
	}
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
        <td class="pageHeading"><?php echo 'Создание ссылки на Интеркассу'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo tep_draw_form('interkassa', 'interkassa.php', 'action=generate'); ?>
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
                <td class="main"><?php echo 'Сумма заказа (грн):'; ?></td>
                <td><?php echo tep_draw_input_field('order_total', $ot_total_value); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo 'Описание'; ?></td>
                <td><?php echo tep_draw_textarea_field('order_desc', 'soft', '60', '8', $description); ?></td>
              </tr>
<?php if (isset($interkassa_link) && ($interkassa_link != NULL))
{
?>               
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo 'Ссылка'; ?></td>
                <td><?php echo tep_draw_textarea_field('order_link', 'soft', '60', '8', $interkassa_link); ?></td>
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
