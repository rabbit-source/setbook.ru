<?php
class RussianPost
{
	function test($index, $weight, $total)
	{
		$link = 'http://www.russianpost.ru/autotarif/Autotarif.aspx?countryCode=643&typePost=1' . ($weight>2000 ? '&viewPost=36&viewPostName=%D0%A6%D0%B5%D0%BD%D0%BD%D0%B0%D1%8F%20%D0%BF%D0%BE%D1%81%D1%8B%D0%BB%D0%BA%D0%B0' : '&viewPost=26&viewPostName=%D0%A6%D0%B5%D0%BD%D0%BD%D0%B0%D1%8F%20%D0%B1%D0%B0%D0%BD%D0%B4%D0%B5%D1%80%D0%BE%D0%BB%D1%8C') . '&countryCodeName=%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%B0%D1%8F%20%D0%A4%D0%B5%D0%B4%D0%B5%D1%80%D0%B0%D1%86%D0%B8%D1%8F&typePostName=%D0%9D%D0%90%D0%97%D0%95%D0%9C%D0%9D.&weight=' . $weight . '&value1=' . round($total) . '&postOfficeId=' . $index;
		echo $link;
		$page_content = file_get_contents($link);
		if (preg_match('/"TarifValue">([^<]+)</', $page_content, $regs)) {
		  $shipping_cost = str_replace(',', '.', $regs[1]);
		}
		return $shipping_cost;
	}

	function getPostZone($index)
	{
		$result = tep_db_query("SELECT zone FROM shipping_post_zone WHERE `index` = ".(int)$index.";");
	   	$data = tep_db_fetch_array($result);
	   	return ($data['zone']?$data['zone']:false);
	}

	function getZone($zone)
	{
		$result = tep_db_query("SELECT * FROM shipping_zones WHERE zone = ".(int)$zone.";");
	   	$data = tep_db_fetch_array($result);
	   	return $data;
	}


     function getPriceOld($index, $weight, $total)
    	{

    		$zone = $this->getPostZone($index);
    		if(!$zone) return false;

    		$z = $this->getZone($zone);
    		//print_r($z);

    		$result = 0;
    		if($weight > 2000)
    		{
    			$result = $z['cost'];
    			if($weight > $z['max_weight'])
    			{
    				$weight_up = $weight - $z['max_weight'];
    				$coef = (int)($weight_up/$z['next_weight']);
    				if($weight_up%$z['next_weight'] > 0)
    					$coef++;
    				$result = $result + $coef * $z['cost_weight'];
    			}

    			if($weight > $z['next_weight_margin'])
    				$result = $result * $z['margin'];
    			$result = $result + $total * $z['tax'];
    		}
    		else
    		{
    			$result = $z['parcel_cost'];
    			if($weight > $z['parcel_max_weight'])
    			{
    				$weight_up = $weight - $z['parcel_max_weight'];
    				$coef = (int)($weight_up/$z['parcel_next_weight']);
    				if($weight_up%$z['parcel_next_weight'] > 0)
    					$coef++;
    				$result = $result + $coef * $z['parcel_next_weight_cost'];
    			}
    			$result = $result + $total * $z['parcel_tax'];
    		}

    		$res = array(
    			'Prepaid' => ($result + 30),
    			'Collect' => (int)($result + 30 + 0.05*$result + 0.2*$total),
    			'Price' => $result
    			//'test' => $this->test($index, $weight, $total)
    		);

    		return $res;
    	}



	function getPrice($index, $weight, $total)
	{



	/*	$Country='RU';
		$From = '101000';
		$To = $index; 
		$Weight= $weight; 
		$Valuation = $total;
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
            
				if ($arrResponse['Отправления']['ЦеннаяПосылка']['Тариф'] == 0)
					$res = 1500;
				else
					$res = $arrResponse['Отправления']['ЦеннаяПосылка']['Тариф'];
		        }
		        else
		        {

				if ($arrResponse['Отправления']['ЦеннаяБандероль']['Тариф'] == 0)
					$res = 1500;
				else
					$res = $arrResponse['Отправления']['ЦеннаяБандероль']['Тариф'];


				$arrResponse['Отправления']['ЦеннаяБандероль']['Тариф'];

		        }

	

                header('Content-type: text/html; charset=windows-1251');  */

  		$link = 'http://sergey:Ext15122005@www.setbook.ru/admin/russianpost.php?index='.$index.'&weight='.$weight.'&total='.$total;
		//echo $link;                                                                            
		$res = file_get_contents($link);
                $res = $res + $res*0.3;

		

		return $res;   
	}
}




?>