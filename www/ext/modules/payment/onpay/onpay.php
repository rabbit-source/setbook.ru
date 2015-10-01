<?php

function get_var($name)
{
    return (isset($_GET[$name])) ? $_GET[$name] : ((isset($_POST[$name])) ? $_POST[$name] : null);
}

require ('includes/application_top.php');
/**
 *  ��������������
 */
function encodestring($st)
{
    return strtr($st, array('�' => 'a', '�' => 'b', '�' => 'v', '�' => 'g', '�' =>
        'd', '�' => 'e', '�' => 'g', '�' => 'z', '�' => 'i', '�' => 'y', '�' => 'k', '�' =>
        'l', '�' => 'm', '�' => 'n', '�' => 'o', '�' => 'p', '�' => 'r', '�' => 's', '�' =>
        't', '�' => 'u', '�' => 'f', '�' => 'i', '�' => 'e', '�' => 'A', '�' => 'B', '�' =>
        'V', '�' => 'G', '�' => 'D', '�' => 'E', '�' => 'G', '�' => 'Z', '�' => 'I', '�' =>
        'Y', '�' => 'K', '�' => 'L', '�' => 'M', '�' => 'N', '�' => 'O', '�' => 'P', '�' =>
        'R', '�' => 'S', '�' => 'T', '�' => 'U', '�' => 'F', '�' => 'I', '�' => 'E', '�' =>
        "yo", '�' => "h", '�' => "ts", '�' => "ch", '�' => "sh", '�' => "shch", '�' =>
        "", '�' => "", '�' => "yu", '�' => "ya", '�' => "YO", '�' => "H", '�' => "TS",
        '�' => "CH", '�' => "SH", '�' => "SHCH", '�' => "", '�' => "", '�' => "YU", '�' =>
        "YA"));
}
/**
 *  XML ����� �� check ������
 */
function uc_onpay_answer($type, $code, $pay_for, $order_amount, $order_currency,
    $text, $key)
{
    $md5 = strtoupper(md5("$type;$pay_for;$order_amount;$order_currency;$code;$key"));
    $text = encodestring($text);
    echo iconv('cp1251', 'utf-8', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n<pay_for>$pay_for</pay_for>\n<comment>$text</comment>\n<md5>$md5</md5>\n</result>");
    exit;
}
/**
 *  XML ����� �� pay ������
 */
function uc_onpay_answerpay($type, $code, $pay_for, $order_amount, $order_currency,
    $text, $onpay_id, $key)
{
    $md5 = strtoupper(md5("$type;$pay_for;$onpay_id;$pay_for;$order_amount;$order_currency;$code;$key"));
    $text = encodestring($text);
    echo iconv('cp1251', 'utf-8', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>$code</code>\n <comment>$text</comment>\n<onpay_id>$onpay_id</onpay_id>\n <pay_for>$pay_for</pay_for>\n<order_id>$pay_for</order_id>\n<md5>$md5</md5>\n</result>");
    exit;
}
/**
 *  ONPAY API
 */

if (empty($_REQUEST['type']))
    exit;
$login = MODULE_PAYMENT_ONPAY_LOGIN; //���� "��� ������������" (�����) � ������� OnPay.ru
$key = MODULE_PAYMENT_ONPAY_PASSWORD1; //��� "��������� ���� ��� API IN" � ������� OnPay.ru
//����� �� ������ check �� OnPay
if ($_REQUEST['type'] == 'check') {
    $order_amount = $amount = $_REQUEST['order_amount'];
    $order_currency = $_REQUEST['order_currency'];
    $order_id = $pay_for = $_REQUEST['pay_for'];
    $sum = floor(100*floatval($order_amount)/MODULE_PAYMENT_ONPAY_KURS)*0.01;
    $order_id = intval($order_id); //��� ������ ���� ����� ������


    $orders_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL .
        " where orders_id = '" . $order_id . "' and class = 'ot_total' limit 1");
    $orders = @tep_db_fetch_array($orders_query);
    $order_summ = @floatval($orders['value']);
    unset($orders_query, $orders);


    $res = "";
    if (empty($order_summ)) {
        $res = 'ERROR 13: NO ORDER';
    } elseif ($order_summ != $sum) {
        $res = 'ERROR 14: ORDER SUM HACKED';
    }
    if ($res != "") {
        uc_onpay_answer($_REQUEST['type'], 2, $pay_for, $order_amount, $order_currency,
            $res, $key);
    }
    // ����� ��������� ������
    uc_onpay_answer($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency,
        'OK', $key);
}
//����� �� ������ pay �� OnPay
elseif ($_REQUEST['type'] == "pay") {
    $onpay_id = $_REQUEST['onpay_id'];
    $order_id = $code = $pay_for = $_REQUEST['pay_for'];
    $amount = $order_amount = $_REQUEST['order_amount'];
    $order_currency = $_REQUEST['order_currency'];
    $balance_amount = $_REQUEST['balance_amount'];
    $balance_currency = $_REQUEST['balance_currency'];
    $exchange_rate = $_REQUEST['exchange_rate'];
    $paymentDateTime = $_REQUEST['paymentDateTime'];
    $md5 = $_REQUEST['md5'];
    $error = '';
    //�������� ������� ������
    if (preg_replace('/[^0-9]/ismU', '', $onpay_id) != $onpay_id)
        $error = "ERROR 1: NO ID";
    elseif (strlen($onpay_id) < 1 or strlen($onpay_id) > 32)
        $error = "ERROR 2: NO ID";
    elseif (preg_replace('/[^0-9a-z]/ismU', '', $pay_for) != $pay_for)
        $error = "ERROR 3: NO ORDER ID";
    elseif (strlen($pay_for) < 1 or strlen($pay_for) > 32)
        $error = "ERROR 4: NO ORDER ID";
    elseif (preg_replace('/[^0-9\.]/ismU', '', $order_amount) != $order_amount)
        $error = "ERROR 5: NO ORDER SUM";
    elseif (floatval($order_amount) <= 0)
        $error = "ERROR 6: NO ORDER SUM";
    elseif (preg_replace('/[^0-9\.]/ismU', '', $balance_amount) != $balance_amount)
        $error = "ERROR 7: NO ORDER SUM";
    elseif (floatval($balance_amount) <= 0)
        $error = "ERROR 8: NO ORDER SUM";
    elseif (strlen($order_currency) != 3)
        $error = "ERROR 9: NO ORDER CURRENCY";
    elseif (strlen($balance_currency) != 3)
        $error = "ERROR 10: NO ORDER CURRENCY";
    elseif (preg_replace('/[^0-9a-z\.]/ismU', '', $exchange_rate) != $exchange_rate)
        $error = "ERROR 11: NO ORDER SUM";
    elseif (strlen($exchange_rate) < 1 or strlen($exchange_rate) > 10)
        $error = "ERROR 12: NO ORDER SUM";
    // ��������� ������, �� ��������� ������
    if ($error != '')
        uc_onpay_answerpay($_REQUEST['type'], 3, $pay_for, $order_amount, $order_currency,
            $error, $onpay_id, $key);
    $order_id = intval($order_id);
    $sum = floor(100*floatval($order_amount)/MODULE_PAYMENT_ONPAY_KURS)*0.01;
    

    $orders_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL .
        " where orders_id = '" . $order_id . "' and class = 'ot_total' limit 1");
    $orders = @tep_db_fetch_array($orders_query);
    $order_summ = @floatval($orders['value']);
    unset($orders_query, $orders);


    $res = "";
    if (empty($order_summ)) {
        $res = 'ERROR 13: NO ORDER';
    } elseif ($order_summ != $sum) {
        $res = 'ERROR 14: ORDER SUM HACKED';
    } elseif (strtoupper(md5($_REQUEST['type'] . ";" . $pay_for . ";" . $onpay_id .
    ";" . $order_amount . ";" . $order_currency . ";" . $key . "")) != $_REQUEST['md5']) {
        $res = 'ERROR 15: MD5 SIGN HACKED';
        uc_onpay_answerpay($_REQUEST['type'], 7, $pay_for, $order_amount, $order_currency,
            $res, $onpay_id, $key);
    }
    if ($res != "") {
        // ��������� ������, �� ��������� ������
        uc_onpay_answerpay($_REQUEST['type'], 3, $pay_for, $order_amount, $order_currency,
            $res, $onpay_id, $key);
    }
    // ��������� ������
    $sql_data_array = array('orders_status' => MODULE_PAYMENT_ONPAY_ORDER_STATUS);
    tep_db_perform('orders', $sql_data_array, 'update', "orders_id='" . $order_id .
        "'");

    $sql_data_arrax = array('orders_id' => $order_id, 'orders_status_id' =>
        MODULE_PAYMENT_ONPAY_ORDER_STATUS, 'date_added' => 'now()', 'customer_notified' =>
        '0', 'comments' => 'Onpay accepted this order payment');
    tep_db_perform('orders_status_history', $sql_data_arrax);
    uc_onpay_answerpay($_REQUEST['type'], 0, $pay_for, $order_amount, $order_currency,
        'OK', $onpay_id, $key);
}

?>