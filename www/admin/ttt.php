<?php
 
// === Исходные данные
$From='101000';
$To='190000';
$Weight=1000;
$Valuation=500;
$Country='RU';
$Site='shop.mysite.ru';
$Email='admin@mysite.ru';
// ==========
header('Content-Type: text/html; charset=utf-8');
 
// Формируем запрос со всеми необходимыми переменными
$QueryString  = 'f=' .rawurlencode($From);
$QueryString .= '&t=' .rawurlencode($To);
$QueryString .= "&w=$Weight&v=$Valuation&c=RU&o=php&cs=utf-8";
$QueryString .= "&st=$Site&ml=$Email";
 
// Формируем URL запроса.
$Request="http://api.postcalc.ru/?$QueryString";
 
// Формируем опции запроса. Это необязательно, однако упрощает контроль и отладку
$arrOptions = array('http' =>
array( 'header'  => 'Accept-Encoding: gzip','timeout' => 5, 'user_agent' => phpversion() )
);
 
// Соединяемся с сервером
if ( !$Response=file_get_contents($Request, false , stream_context_create($arrOptions)) )
die('Не удалось соединиться с api.postcalc.ru!');
 
 
// Разархивируем ответ
if ( substr($Response,0,3) == "\x1f\x8b\x08" )  $Response=gzinflate(substr($Response,10,-8));
 
// Переводим ответ в массив PHP
$arrResponse = unserialize($Response);
 
// Обработка ошибки
if ( $arrResponse['Status'] != 'OK' ) die("Сервер вернул ошибку: $arrResponse[Status]!");
 
// Выводим значение тарифа для бандероли
echo 'Тариф на бандероль: '. $arrResponse['Отправления']['ПростаяБандероль']['Тариф'];
 
//  Выводим в цикле все доступные тарифы 
echo "<pre>\n";
foreach  ( $arrResponse['Отправления'] as $parcel )
echo "$parcel[Название]\t$parcel[Доставка]\n";
 
echo "</pre>\n";
?>