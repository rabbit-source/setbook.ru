<?php

    $HTTP_GET_VARS = &$_GET;

    $index = (int)$HTTP_GET_VARS['index']; 
    $weight= (float)$HTTP_GET_VARS['weight']; 
    $total = (float)$HTTP_GET_VARS['total'];

    header('Content-Type: text/html; charset=windows-1251');
    //$Request='http://api.postcalc.ru/?f=101000&c=RU&t='.$index.'&w='.$weight.'&v='.$total.'&o=php';
    $Request='http://api.postcalc.ru/?f=190000&c=RU&t='.$index.'&w='.$weight.'&v='.$total.'&o=php&st=setbook.ru&ml=s.eremenko@setbook.ru&pn=Sergey_Eremenko';
	
	$Response=file_get_contents($Request);
	$arrResponse=unserialize($Response);
    
    $res = 0;
    
    if ($weight>2000)
    {
        if ($arrResponse['ЦеннаяПосылка']['Доставка'] == 0)
            $res = $arrResponse['ЦеннаяАвиаПосылка']['Доставка'];
        else
            $res = $arrResponse['ЦеннаяПосылка']['Доставка'];
    }
    else
    {
        if ($arrResponse['ЦеннаяБандероль']['Доставка'] == 0)
            $res = $arrResponse['ЦеннаяАвиаБандероль']['Доставка'];
        else
            $res = $arrResponse['ЦеннаяБандероль']['Доставка'];
    }   
    echo $res;
?>