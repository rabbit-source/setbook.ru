<?php
////
// Recursively handle magic_quotes_gpc turned off.
// This is due to the possibility of have an array in
// $HTTP_xxx_VARS
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    while (list($key, $value) = each($ar)) {
      if (is_array($value)) {
        do_magic_quotes_gpc($value);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
  }

// $HTTP_xxx_VARS are always set on php4
  if (!is_array($HTTP_GET_VARS)) $HTTP_GET_VARS = array();
  if (!is_array($HTTP_POST_VARS)) $HTTP_POST_VARS = array();
  if (!is_array($_COOKIE)) $_COOKIE = array();

// handle magic_quotes_gpc turned off.
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($HTTP_GET_VARS);
    do_magic_quotes_gpc($HTTP_POST_VARS);
    do_magic_quotes_gpc($_COOKIE);
  }

  if (!function_exists('is_numeric')) {
    function is_numeric($param) {
      return preg_match("/^[0-9]{1,50}.?[0-9]{0,50}$/", $param);
    }
  }

  if (!function_exists('is_uploaded_file')) {
    function is_uploaded_file($filename) {
      if (!$tmp_file = get_cfg_var('upload_tmp_dir')) {
        $tmp_file = dirname(tempnam('', ''));
      }

      if (strchr($tmp_file, '/')) {
        if (substr($tmp_file, -1) != '/') $tmp_file .= '/';
      } elseif (strchr($tmp_file, '\\')) {
        if (substr($tmp_file, -1) != '\\') $tmp_file .= '\\';
      }

      return file_exists($tmp_file . basename($filename));
    }
  }

  if (!function_exists('move_uploaded_file')) {
    function move_uploaded_file($file, $target) {
      return copy($file, $target);
    }
  }

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if (tep_not_null($host) && tep_not_null($type)) {
        @exec("nslookup -type=$type $host", $output);
        while(list($k, $line) = each($output)) {
          if (substr($line, 0, strpos($host))==$host) {
            return true;
          }
        }
      }
      return false;
    }
  }

  if (!function_exists('in_array')) {
    function in_array($lookup_value, $lookup_array) {
      reset($lookup_array);
      while (list($key, $value) = each($lookup_array)) {
        if ($value == $lookup_value) return true;
      }

      return false;
    }
  }

  if (!function_exists('array_merge')) {
    function array_merge($array1, $array2, $array3 = '') {
      if ($array3 == '') $array3 = array();

      while (list($key, $val) = each($array1)) $array_merged[$key] = $val;
      while (list($key, $val) = each($array2)) $array_merged[$key] = $val;

      if (sizeof($array3) > 0) while (list($key, $val) = each($array3)) $array_merged[$key] = $val;

      return (array)$array_merged;
    }
  }

  if (!function_exists('array_shift')) {
    function array_shift(&$array) {
      $i = 0;
      $shifted_array = array();
      reset($array);
      while (list($key, $value) = each($array)) {
        if ($i > 0) {
          $shifted_array[$key] = $value;
        } else {
          $return = $array[$key];
        }
        $i++;
      }
      $array = $shifted_array;

      return $return;
    }
  }

  if (!function_exists('array_reverse')) {
    function array_reverse($array) {
      $reversed_array = array();

      for ($i=sizeof($array)-1; $i>=0; $i--) {
        $reversed_array[] = $array[$i];
      }

      return $reversed_array;
    }
  }

  if (!function_exists('array_slice')) {
    function array_slice($array, $offset, $length = '0') {
      $length = abs($length);

      if ($length == 0) {
        $high = sizeof($array);
      } else {
        $high = $offset+$length;
      }

      for ($i=$offset; $i<$high; $i++) {
        $new_array[$i-$offset] = $array[$i];
      }

      return $new_array;
    }
  }

  if (!function_exists('fputcsv')) {
	function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"') {
	  // Sanity Check
	  if (!is_resource($handle)) {
		trigger_error('fputcsv() expects parameter 1 to be resource, ' . gettype($handle) . ' given', E_USER_WARNING);
		return false;
	  }

	  if ($delimiter!=NULL) {
		if (strlen($delimiter) < 1) {
		  trigger_error('delimiter must be a character', E_USER_WARNING);
		  return false;
		} elseif (strlen($delimiter) > 1) {
		  trigger_error('delimiter must be a single character', E_USER_NOTICE);
		}

		/* use first character from string */
		$delimiter = $delimiter[0];
	  }

	  if ($enclosure!=NULL) {
		if (strlen($enclosure) < 1) {
		  trigger_error('enclosure must be a character', E_USER_WARNING);
		  return false;
		} elseif (strlen($enclosure) > 1) {
		  trigger_error('enclosure must be a single character', E_USER_NOTICE);
		}

		/* use first character from string */
		$enclosure = $enclosure[0];
	  }

	  $i = 0;
	  $csvline = '';
	  $escape_char = '\\';
	  $field_cnt = count($fields);
	  $enc_is_quote = in_array($enclosure, array('"',"'"));
	  reset($fields);

	  foreach( $fields as $field ) {
		/* enclose a field that contains a delimiter, an enclosure character, or a newline */
		if (is_string($field) && (strpos($field, $delimiter)!==false || trpos($field, $enclosure)!==false || strpos($field, $escape_char)!==false || strpos($field, "\n")!==false || strpos($field, "\r")!==false || strpos($field, "\t")!==false || strpos($field, ' ')!==false ) ) {

		  $field_len = strlen($field);
		  $escaped = 0;

		  $csvline .= $enclosure;
		  for ($ch = 0; $ch < $field_len; $ch++) {
			if ($field[$ch] == $escape_char && $field[$ch+1] == $enclosure && $enc_is_quote) {
			  continue;
			} elseif ($field[$ch] == $escape_char) {
			  $escaped = 1;
			} elseif (!$escaped && $field[$ch] == $enclosure) {
			  $csvline .= $enclosure;
			} else {
			  $escaped = 0;
			}
			$csvline .= $field[$ch];
		  }
		  $csvline .= $enclosure;
		} else {
		  $csvline .= $field;
		}

		if ($i++ != $field_cnt) {
		  $csvline .= $delimiter;
		}
	  }

	  $csvline .= "\n";

	  return fwrite($handle, $csvline);
	}
  }
?>
