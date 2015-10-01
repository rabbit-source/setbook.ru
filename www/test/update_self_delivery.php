<?php
	echo '1<br/>';

	require('/var/www/2009/includes/application_top.php');

	echo '2<br/>';
	
	function get_child_cities_r($city_id, $cities)
	{
		echo 'get_child_cities_r('.$city_id.')<br/>';
		
		$child_cities_query = tep_db_query("SELECT city_id FROM cities WHERE parent_id=".(int)$city_id);
		
		while ($child_city = tep_db_fetch_array($child_cities_query))
		{
			echo 'get_child_cities_r '.$child_city['city_id'].'<br/>';
				
			$cities[] = $child_city['city_id'];
			get_child_cities_r($child_city['city_id'], $cities);
		}

		echo 'get_child_cities_r ('.$city_id.') finished<br/>';
	}
	
	$self_deliveries_query = tep_db_query("SELECT entry_postcode FROM self_delivery");
	while ($self_delivery = tep_db_fetch_array($self_deliveries_query))
	{
		echo $self_delivery.'<br/>';
		echo $self_delivery['entry_postcode'].'<br/>';
		
		$cities = array();
		get_child_cities_r($self_delivery['entry_postcode'], $cities);

		echo 'Result: '.$cities.'<br/>';
	}
?>