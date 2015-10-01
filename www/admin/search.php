<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

$query = '';
if((int)$HTTP_GET_VARS['limit']>0 && (int)$HTTP_GET_VARS['limit']<100) $limit = (int)$HTTP_GET_VARS['limit'];
else $limit = 15; 
$q = addslashes($HTTP_GET_VARS['q']);
switch ($action) {
      case 'category':
      	$query = "SELECT s.category_id as id, c.categories_name as name FROM subscribe s
				  LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." AS c ON c.categories_id = s.category_id
				  WHERE s.type_id = 1
				  AND c.language_id = 2
				  AND c.categories_name LIKE '%".$q."%'
				  GROUP BY id
				  LIMIT 0,".$limit.";";	
      break;
      case 'series':
      	$query = "SELECT s.category_id as id, c.series_name as name FROM subscribe s
				  LEFT JOIN ".TABLE_SERIES." AS c ON c.series_id = s.category_id
				  WHERE s.type_id = 2
				  AND c.language_id = 2
				  AND c.series_name LIKE '%".$q."%'
				  GROUP BY id
				  LIMIT 0,".$limit.";";	
      break;
      case 'author':
      	$query = "SELECT s.category_id as id, c.authors_name as name FROM subscribe s
  				  LEFT JOIN ".TABLE_AUTHORS." AS c ON c.authors_id = s.category_id
				  WHERE s.type_id = 3
				  AND c.language_id = 2
				  AND c.authors_name LIKE '%".$q."%'
				  GROUP BY id
				  LIMIT 0,".$limit.";";	
      break;
      case 'publisher':
      	$query = "SELECT s.category_id as id, c.manufacturers_name as name FROM subscribe s
  				  LEFT JOIN ".TABLE_MANUFACTURERS_INFO." AS c ON c.manufacturers_id = s.category_id
				  WHERE s.type_id = 4
				  AND c.languages_id = 2
				  AND c.manufacturers_name LIKE '%".$q."%'
				  GROUP BY id
				  LIMIT 0,".$limit.";";	
      break;
      case 'city':
      	$query = "SELECT entry_city as id, entry_city as name 
				  FROM ".TABLE_ADDRESS_BOOK."
				  WHERE entry_city LIKE '%".$q."%'
				  GROUP BY entry_city
				  LIMIT 0,".$limit.";";	
      break;
      default:
      	exit;
      break;
}
		$result = tep_db_query(iconv('utf-8', 'cp1251', $query)); 
      	while ($r = tep_db_fetch_array($result)) 
      	{ 
      		echo $r['name'].'|'.$r['id']."\n";
      	}

?>
        
