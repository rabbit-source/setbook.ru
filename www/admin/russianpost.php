<?php

// === Исходные данные
$HTTP_GET_VARS = &$_GET;

$Country='RU';
$From = '101000';
$To = (int)$HTTP_GET_VARS['index']; 
$Weight= (float)$HTTP_GET_VARS['weight']; 
$Valuation = (float)$HTTP_GET_VARS['total'];
$Site = 'setbook.ru';
$Email = 's.eremenko@setbook.ru';



// ==========
header('Content-Type: text/html; charset=utf-8');

// Формируем запрос со всеми необходимыми переменными
$QueryString  = 'f=' .rawurlencode($From);
$QueryString .= '&t=' .rawurlencode($To);
$QueryString .= "&w=$Weight&v=$Valuation&c=RU&o=php&cs=utf-8&st=$Site&ml=$Email";

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

        $res = 1500;

        if ($Weight>2000)
        {
            
		if ($arrResponse['Отправления']['ЦеннаяПосылка']['Доставка'] == 0)
			$res = 1500;
		else
			$res = $arrResponse['Отправления']['ЦеннаяПосылка']['Доставка'];
        }
        else
        {

		if ($arrResponse['Отправления']['ЦеннаяБандероль']['Доставка'] == 0)
			$res = 1500;
		else
			$res = $arrResponse['Отправления']['ЦеннаяБандероль']['Доставка'];


		$arrResponse['Отправления']['ЦеннаяБандероль']['Доставка'];

        }

	


echo $res;

//  Выводим в цикле все доступные тарифы 
//echo "<pre>\n";
//foreach  ( $arrResponse['Отправления'] as $parcel )
  //                        echo "$parcel[Название]\t$parcel[Доставка]\n";

//echo "</pre>\n";
