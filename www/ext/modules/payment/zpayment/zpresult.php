<?php
  chdir('../../../../');
  require('includes/application_top.php');

  $str = '';
  reset($HTTP_GET_VARS);
  while (list($k, $v) = each($HTTP_GET_VARS)) {
	$str .= $k . ' = ' . urldecode($v) . "\n";
  }
  $str .= "\n";
  reset($HTTP_POST_VARS);
  while (list($k, $v) = each($HTTP_POST_VARS)) {
	$str .= $k . ' = ' . urldecode($v) . "\n";
  }

//  echo 'YES';
  tep_mail('sivkov@setbook.ru', 'sivkov@setbook.ru', 'z-payment result processing', trim($str), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
  die();

$lmi_prerequest =0;	
if  (isset($_POST['LMI_PREREQUEST'])) {$lmi_prerequest=$_POST['LMI_PREREQUEST'];} else {$lmi_prerequest=$_GET['LMI_PREREQUEST'];};
if  (isset($_POST['LMI_PAYEE_PURSE'])) {$lmi_payee_purse=$_POST['LMI_PAYEE_PURSE'];} else {$lmi_payee_purse=$_GET['LMI_PAYEE_PURSE'];}; // id магазина
if  (isset($_POST['LMI_PAYMENT_AMOUNT'])) {$lmi_payment_amount=$_POST['LMI_PAYMENT_AMOUNT'];} else {$lmi_payment_amount=$_GET['LMI_PAYMENT_AMOUNT'];};
if  (isset($_POST['LMI_PAYER_PURSE'])) {$lmi_payer_purse=$_POST['LMI_PAYER_PURSE'];} else {$lmi_payer_purse=$_GET['LMI_PAYER_PURSE'];};
if  (isset($_POST['LMI_PAYER_WM'])) {$lmi_payer_wm=$_POST['LMI_PAYER_WM'];} else {$lmi_payer_wm=$_GET['LMI_PAYER_WM'];};
if  (isset($_POST['LMI_PAYMENT_NO'])) {$lmi_payment_no=$_POST['LMI_PAYMENT_NO'];} else {$lmi_payment_no=$_GET['LMI_PAYMENT_NO'];};
if  (isset($_POST['LMI_MODE'])) {$lmi_mode=$_POST['LMI_MODE'];} else {$lmi_mode=$_GET['LMI_MODE'];};
if  (isset($_POST['ID_PAY'])) {$id_pay=$_POST['ID_PAY'];} else {$id_pay=$_GET['ID_PAY'];};
if  (isset($_POST['CLIENT_MAIL'])) {$client_mail=$_POST['CLIENT_MAIL'];} else {$client_mail=$_GET['CLIENT_MAIL'];};
if  (isset($_POST['custom'])) {$custom=$_POST['custom'];} else {$custom=$_GET['custom'];};
if  (isset($_POST['LMI_SYS_TRANS_NO'])) {$lmi_sys_trans_no=$_POST['LMI_SYS_TRANS_NO'];} else {$lmi_sys_trans_no=$_GET['LMI_SYS_TRANS_NO'];};
if  (isset($_POST['LMI_SYS_INVS_NO'])) {$lmi_sys_invs_no=$_POST['LMI_SYS_INVS_NO'];} else {$lmi_sys_invs_no=$_GET['LMI_SYS_INVS_NO'];};
if  (isset($_POST['LMI_SYS_TRANS_DATE'])) {$lmi_sys_trans_date=$_POST['LMI_SYS_TRANS_DATE'];} else {$lmi_sys_trans_date=$_GET['LMI_SYS_TRANS_DATE'];};
if  (isset($_POST['LMI_HASH'])) {$lmi_hash=$_POST['LMI_HASH'];} else {$lmi_hash=$_GET['LMI_HASH'];};
if  (isset($_POST['LMI_SECRET_KEY'])) {$lmi_secret_key=$_POST['LMI_SECRET_KEY'];} else {$lmi_secret_key=$_GET['LMI_SECRET_KEY'];};

$p = explode("-", base64_decode($custom));
$u_id = $p[1];
$item = $p[2];
$cost = $p[3];

    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;
    $order ->query($item);

$customer_id = $order ->customer['id'];


/*echo $order->info['total'] * $currencies->get_value('RUR');
echo '<br>'. $customer_id;*/


$err=0;
$err_text='';
// Если такой товар не обнаружен, то выводим ошибку и прерываем работу скрипта.
if ($customer_id=='' && $customer_id!=$u_id) {
    $err=1;
    $err_text= 'ERR: НЕТ ТАКОГО ЗАКАЗА на VAM'.'| '.$item.' | - |'.$customer_id;
               };

//проверяем ID магазина

if($lmi_payee_purse != MODULE_PAYMENT_ZPAYMENT_LMI_PAYEE_PURSE) {
    $err=3;
    $err_text='ERR: НЕВЕРЕН ID МАГАЗИНА : '.$lmi_payee_purse;
        
                    };


// Проверяем, не произошла ли подмена суммы.

$total_value_query = 'select value from '.TABLE_ORDERS_TOTAL.' where class = "ot_total" and orders_id = '.$item;
$total_value = tep_db_query($total_value_query);
$total_value_values = tep_db_fetch_array($total_value);
  
$order_amount = $total_value_values['value'] * $currencies->get_value('RUR');
if ($order_amount !=(string)(REAL)$lmi_payment_amount){
    $err=2;
    $err_text='ERR: НЕВЕРНАЯ СУММА : '.(string)(REAL)$lmi_payment_amount;
                                                               };



IF($lmi_prerequest == 1) { //форма предварительного запроса
if ($err != 0) {echo $err_text;} else {echo 'YES';};
}
else
{


$sk=MODULE_PAYMENT_ZPAYMENT_MERCHANT_KEY;
$common_string = $lmi_payee_purse.$lmi_payment_amount.$lmi_payment_no.$lmi_mode.$lmi_sys_invs_no.$lmi_sys_trans_no.$lmi_sys_trans_date.$sk.$lmi_payer_purse.$lmi_payer_wm;
$hash =strtoupper(md5($common_string));

 if ($err==0) {
      if ($hash == $lmi_hash) {

                        if(MODULE_PAYMENT_ZPAYMENT_CH_ORDER_STATUS == "Yes" )

                             $sql_data_array = array('orders_status' => '99');
	                     $now_data = date('Y-m-d H:i:s');
                             $sql_data_arrax = array('orders_id' => $item,
                                                     'orders_status_id' => '99',
                                                     'date_added' => $now_data,
                                                     'customer_notified' => '0',
                                                     'comments' => 'Оплата через Z-PAYMENT');
                             tep_db_perform('orders', $sql_data_array, 'update', "orders_id = '" . $item . "'");
                             tep_db_perform('orders_status_history', $sql_data_arrax);
                              };
            };
};

?>