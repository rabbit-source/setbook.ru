<?php
 
// === �������� ������
$From='101000';
$To='190000';
$Weight=1000;
$Valuation=500;
$Country='RU';
$Site='shop.mysite.ru';
$Email='admin@mysite.ru';
// ==========
header('Content-Type: text/html; charset=utf-8');
 
// ��������� ������ �� ����� ������������ �����������
$QueryString  = 'f=' .rawurlencode($From);
$QueryString .= '&t=' .rawurlencode($To);
$QueryString .= "&w=$Weight&v=$Valuation&c=RU&o=php&cs=utf-8";
$QueryString .= "&st=$Site&ml=$Email";
 
// ��������� URL �������.
$Request="http://api.postcalc.ru/?$QueryString";
 
// ��������� ����� �������. ��� �������������, ������ �������� �������� � �������
$arrOptions = array('http' =>
array( 'header'  => 'Accept-Encoding: gzip','timeout' => 5, 'user_agent' => phpversion() )
);
 
// ����������� � ��������
if ( !$Response=file_get_contents($Request, false , stream_context_create($arrOptions)) )
die('�� ������� ����������� � api.postcalc.ru!');
 
 
// ������������� �����
if ( substr($Response,0,3) == "\x1f\x8b\x08" )  $Response=gzinflate(substr($Response,10,-8));
 
// ��������� ����� � ������ PHP
$arrResponse = unserialize($Response);
 
// ��������� ������
if ( $arrResponse['Status'] != 'OK' ) die("������ ������ ������: $arrResponse[Status]!");
 
// ������� �������� ������ ��� ���������
echo '����� �� ���������: '. $arrResponse['�����������']['����������������']['�����'];
 
//  ������� � ����� ��� ��������� ������ 
echo "<pre>\n";
foreach  ( $arrResponse['�����������'] as $parcel )
echo "$parcel[��������]\t$parcel[��������]\n";
 
echo "</pre>\n";
?>