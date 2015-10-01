<?php
  function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

	$$link = mysql_connect($server, $username, $password);

    if ($$link) {
	  mysql_select_db($database);
	}

    return $$link;
  }

  function tep_db_select_db($database, $link = 'db_link') {
    global $$link;

	return mysql_select_db($database, $$link);
  }

  function tep_db_close($link = 'db_link') {
    global $$link;

    return mysql_close($$link);
  }

  function tep_db_error($query, $errno, $error) {
    die('<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></strong></font>');
  }

  function tep_db_query($query, $link = 'db_link') {
    global $$link, $queries_count;
	global $queries_duration;
	$queries_count ++;

	$start_time = microtime(true);
	//$start_date_str = date('Ymd');
	//$start_time_str = date('h:i:s');

	$result = @mysql_query($query, $$link) or tep_db_error($query, mysql_errno($$link), mysql_error($$link));

	$queries_duration = microtime(true) - $start_time;

	/*if ($queries_duration >= 5)
	{
		$db_time_file = @fopen(SESSION_WRITE_DIRECTORY . '/dbtimes_' . $start_date_str . '.txt', 'a');
		if ($db_time_file)
		{
			//echo 'YES';
			fwrite($db_time_file, '[' . date('h:i:s') . '] (' . $start_time_str . ') ' . $queries_duration . ' ' . $query . ' [' . $_SERVER["REQUEST_URI"] . ']' . PHP_EOL);
			fclose($db_time_file);
		}
	}*/
	
    return $result;
  }

  function tep_db_unbuffered_query($query, $link = 'db_link') {
    global $$link;

    $result = mysql_unbuffered_query($query, $$link) or tep_db_error($query, mysql_errno(), mysql_error());

    return $result;
  }

  function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return tep_db_query($query, $link);
  }

  function tep_db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function tep_db_fetch_row($db_query) {
    return mysql_fetch_row($db_query);
  }

  function tep_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function tep_db_num_fields($db_query) {
    return mysql_num_fields($db_query);
  }

  function tep_db_field_name($db_query) {
    return mysql_field_name($db_query);
  }

  function tep_db_data_seek($db_query, $row_number) {
    return mysql_data_seek($db_query, $row_number);
  }

  function tep_db_result($result, $row, $field = '') {
    return mysql_result($result, $row, $field);
  }

  function tep_db_list_tables($db = DB_DATABASE) {
	return mysql_list_tables($db);
  }

  function tep_db_tablename($db_query, $row_number) {
	return mysql_tablename($db_query, $row_number);
  }

  function tep_db_insert_id() {
	$last_record_query = tep_db_query("select last_insert_id() as last_id");
	$last_record = tep_db_fetch_array($last_record_query);
	return $last_record['last_id'];

#    return mysql_insert_id();
  }

  function tep_db_free_result($db_query) {
    return mysql_free_result($db_query);
  }

  function tep_db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }

  function tep_db_field_exists($table, $field) {
	$query = tep_db_query("describe " . tep_db_input($table));
	while ($row = tep_db_fetch_array($query)) {
	  if ($row['Field'] == $field) return true;
	}
	return false;
  }

  function tep_db_table_exists($database, $table) {
	if (empty($database)) $database = DB_DATABASE;

	$tables_query = tep_db_query("show tables in " . $database);
	for ($i=0, $n=tep_db_num_rows($tables_query); $i<$n; $i++) {
	  $tablename = tep_db_result($tables_query, $i, 0);
	  if ($tablename==$table) return true;
	}
	return false;
  }

  function tep_db_output($string) {
    return htmlspecialchars($string);
  }

  function tep_db_input($string) {
    return addslashes($string);
  }

  function tep_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(tep_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  function tep_db_fetch_array_all($db_query) {
  	while ($row = tep_db_fetch_array($db_query)) {
	  $result[] = $row;
	  $row = array();
	}
    return $result;
  }
  
  function tep_db_query_fetch_array($query) {
  	$db_query = tep_db_query($query);
  	return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }
?>