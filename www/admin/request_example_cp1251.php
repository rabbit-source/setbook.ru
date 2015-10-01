<?php
// Заголовок с указанием набора символов. 
// Для форматов php, arr, html кодировка определяется переменной cs и по умолчанию в версии 1.0 это UTF-8.
// Для форматов json, wddx кодировка всегда UTF-8
header('Content-Type: text/html; charset=Windows-1251');

// === Исходные данные
$From='Москва';
$To='Ленинградская область';
$Weight=1000;
$Valuation=500;
$Country='RU';
// ==========


// Обращаемся к функции postcalcRequest
$arr=postcalcRequest($From,$To,$Weight,$Valuation,$Country);

// Выводим значение тарифа для бандероли
echo $arr['Отправления']['ПростаяБандероль']['Тариф'];

echo "\n<hr>\n";
// Выводим таблицу отправлений
echo "<table>\n<tr><th>Название</th><th>Доставка</th><th>Сроки</th></tr>\n";
// Выводим список тарифов
foreach ( $arr['Отправления'] as $parcel )
	echo "<tr><td>$parcel[Название] </td><td>$parcel[Доставка] руб.</td><td>$parcel[СрокДоставки]</td></tr>\n";
echo "</table>\n";

function postcalcRequest($From,$To,$Weight,$Valuation=0,$Country='RU'){
    // Обязательно! Проверяем данные - больше всего ошибочных запросов из-за неверных значений веса и оценки.
    if ( !($Weight>0 && $Weight<=100000) ) die("Bec - от 1 г до 100000 г!");
    if ( !($Valuation>=0 && $Valuation<=100000) ) die("Оценка - от 0 руб. до 100000 руб.!");
    
    // Кодируем строки, которые могут содержать пробелы и русские буквы
    $From=rawurlencode($From);
    $To=rawurlencode($To);


    // Формируем запрос со всеми необходимыми переменными. Не забудьте поменять переменные st, ml, pn!
    $QueryString = "st=setbook.ru&ml=s.eremenko@setbook.ru&pn=Sergey_Eremenko";
    $QueryString.= "&f=$From&t=$To&w=$Weight&v=$Valuation";
    $QueryString.= "&o=php&cs=Windows-1251";

    // Кэширование. Каталог - любой, в который веб-сервер имеет право записи.
    $CacheDir=sys_get_temp_dir();
    // Название файла - префикс плюс хэш строки запроса
    $CacheFile="$CacheDir/postcalc_".md5($QueryString).'.txt';
    // Сборка мусора. Удаляем все файлы, которые подходят под маску, старше 600 секунд 
    $arrCacheFiles=glob("$CacheDir/postcalc_*.txt");
    $Now=time();
    foreach ($arrCacheFiles as $fileObj) {
        if ( $Now-filemtime($fileObj) > 600 ) unlink($CacheFile);
    }
    // Если существует файл кэша для данной строки запроса, просто зачитываем его
    if ( file_exists($CacheFile) ) {
        $Response=  file_get_contents($CacheFile); 
        echo "Запрос из кэша!<br>\n";
    } else {
        // Формируем URL запроса.
        $Request="http://api.postcalc.ru/?$QueryString";

        // Формируем опции запроса. Это _необязательно_, однако упрощает контроль и отладку
        $arrOptions = array('http' =>
          array( 'header'  => 'Accept-Encoding: gzip','timeout' => 5, 'user_agent' => phpversion() )
        );

        // Запрос к серверу. Сохраняем ответ в переменной $Response
        $Response=file_get_contents($Request, false , stream_context_create($arrOptions)) or die('Не удалось соединиться с сервером postcalc.ru!');

        // Если поток сжат, разархивируем его
        if ( substr($Response,0,3) == "\x1f\x8b\x08" ) $Response=gzinflate(substr($Response,10,-8));

        // Пишем в кэш
        file_put_contents($CacheFile,$Response);
    }
    
    // Переводим ответ сервера в массив PHP
    if (!$arrResponse=unserialize($Response)) die("Получены странные данные. Ответ сервера:\n$Response");

    // Обработка возможной ошибки
    if ( $arrResponse['Status'] != 'OK' ) die("Сервер вернул ошибку: $arrResponse[Status]!");

    return $arrResponse;
}
