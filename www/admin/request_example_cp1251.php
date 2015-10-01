<?php
// ��������� � ��������� ������ ��������. 
// ��� �������� php, arr, html ��������� ������������ ���������� cs � �� ��������� � ������ 1.0 ��� UTF-8.
// ��� �������� json, wddx ��������� ������ UTF-8
header('Content-Type: text/html; charset=Windows-1251');

// === �������� ������
$From='������';
$To='������������� �������';
$Weight=1000;
$Valuation=500;
$Country='RU';
// ==========


// ���������� � ������� postcalcRequest
$arr=postcalcRequest($From,$To,$Weight,$Valuation,$Country);

// ������� �������� ������ ��� ���������
echo $arr['�����������']['����������������']['�����'];

echo "\n<hr>\n";
// ������� ������� �����������
echo "<table>\n<tr><th>��������</th><th>��������</th><th>�����</th></tr>\n";
// ������� ������ �������
foreach ( $arr['�����������'] as $parcel )
	echo "<tr><td>$parcel[��������] </td><td>$parcel[��������] ���.</td><td>$parcel[������������]</td></tr>\n";
echo "</table>\n";

function postcalcRequest($From,$To,$Weight,$Valuation=0,$Country='RU'){
    // �����������! ��������� ������ - ������ ����� ��������� �������� ��-�� �������� �������� ���� � ������.
    if ( !($Weight>0 && $Weight<=100000) ) die("Bec - �� 1 � �� 100000 �!");
    if ( !($Valuation>=0 && $Valuation<=100000) ) die("������ - �� 0 ���. �� 100000 ���.!");
    
    // �������� ������, ������� ����� ��������� ������� � ������� �����
    $From=rawurlencode($From);
    $To=rawurlencode($To);


    // ��������� ������ �� ����� ������������ �����������. �� �������� �������� ���������� st, ml, pn!
    $QueryString = "st=setbook.ru&ml=s.eremenko@setbook.ru&pn=Sergey_Eremenko";
    $QueryString.= "&f=$From&t=$To&w=$Weight&v=$Valuation";
    $QueryString.= "&o=php&cs=Windows-1251";

    // �����������. ������� - �����, � ������� ���-������ ����� ����� ������.
    $CacheDir=sys_get_temp_dir();
    // �������� ����� - ������� ���� ��� ������ �������
    $CacheFile="$CacheDir/postcalc_".md5($QueryString).'.txt';
    // ������ ������. ������� ��� �����, ������� �������� ��� �����, ������ 600 ������ 
    $arrCacheFiles=glob("$CacheDir/postcalc_*.txt");
    $Now=time();
    foreach ($arrCacheFiles as $fileObj) {
        if ( $Now-filemtime($fileObj) > 600 ) unlink($CacheFile);
    }
    // ���� ���������� ���� ���� ��� ������ ������ �������, ������ ���������� ���
    if ( file_exists($CacheFile) ) {
        $Response=  file_get_contents($CacheFile); 
        echo "������ �� ����!<br>\n";
    } else {
        // ��������� URL �������.
        $Request="http://api.postcalc.ru/?$QueryString";

        // ��������� ����� �������. ��� _�������������_, ������ �������� �������� � �������
        $arrOptions = array('http' =>
          array( 'header'  => 'Accept-Encoding: gzip','timeout' => 5, 'user_agent' => phpversion() )
        );

        // ������ � �������. ��������� ����� � ���������� $Response
        $Response=file_get_contents($Request, false , stream_context_create($arrOptions)) or die('�� ������� ����������� � �������� postcalc.ru!');

        // ���� ����� ����, ������������� ���
        if ( substr($Response,0,3) == "\x1f\x8b\x08" ) $Response=gzinflate(substr($Response,10,-8));

        // ����� � ���
        file_put_contents($CacheFile,$Response);
    }
    
    // ��������� ����� ������� � ������ PHP
    if (!$arrResponse=unserialize($Response)) die("�������� �������� ������. ����� �������:\n$Response");

    // ��������� ��������� ������
    if ( $arrResponse['Status'] != 'OK' ) die("������ ������ ������: $arrResponse[Status]!");

    return $arrResponse;
}
