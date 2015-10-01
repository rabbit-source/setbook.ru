<?php
  class navigationHistory {
    var $path, $snapshot;

    function navigationHistory() {
      $this->reset();
    }

    function reset() {
      $this->path = array();
      $this->snapshot = array();
    }

    function add_current_page() {
	  $last_entry_position = sizeof($this->path) - 1;
	  $current_page = $this->get_current_page();
	  if ($this->path[$last_entry_position]!=$current_page) {
        $this->path[] = $current_page;
      }
    }

	function get_current_page() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $request_type;

	  $get_vars = array();
	  if (tep_not_null(REQUEST_URI)) {
		if (strpos(REQUEST_URI, '?')) {
		  $current_page = PHP_SELF;
//		  if (substr($current_page, 0, strlen(DIR_WS_CATALOG))==DIR_WS_CATALOG) $current_page = substr($current_page, strlen(DIR_WS_CATALOG));
		  $qstring = substr(REQUEST_URI, strpos(REQUEST_URI, '?')+1);

		  $qstring_array = explode('&', str_replace('&amp;', '&', $qstring));
		  reset($qstring_array);
		  while (list(, $get_var) = each($qstring_array)) {
			list($var_name, $var_value) = explode('=', $get_var);
			if (!in_array($var_name, array('x', 'y'))) $get_vars[$var_name] = urldecode($var_value);
		  }
		} else {
		  $current_page = REQUEST_URI;
		  $qstring = '';
		}
	  } else {
		$current_page = basename(SCRIPT_FILENAME);
		$get_vars = $HTTP_GET_VARS;
	  }
	  if (!in_array(tep_session_name(), array_keys($get_vars))) $get_vars[tep_session_name()] = tep_session_id();

	  return array('page' => $current_page, 'mode' => $request_type, 'get' => $get_vars, 'post' => $HTTP_POST_VARS, 'real_page' => basename(SCRIPT_FILENAME), 'real_get' => $HTTP_GET_VARS);
	}

    function remove_current_page() {
      $last_entry_position = sizeof($this->path) - 1;
      unset($this->path[$last_entry_position]);
    }

    function set_snapshot($page = '') {
      if (is_array($page)) {
        $this->snapshot = array('page' => $page['page'],
                                'mode' => $page['mode'],
                                'get' => $page['get'],
                                'post' => $page['post']);
      } else {
        $this->snapshot = $this->get_current_page();
      }
    }

    function clear_snapshot() {
      $this->snapshot = array();
    }

    function set_path_as_snapshot($history = 0) {
      $pos = (sizeof($this->path)-1-$history);
      $this->snapshot = array('page' => $this->path[$pos]['page'],
                              'mode' => $this->path[$pos]['mode'],
                              'get' => $this->path[$pos]['get'],
                              'post' => $this->path[$pos]['post']);
    }

    function debug() {
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        echo $this->path[$i]['page'] . '?';
        while (list($key, $value) = each($this->path[$i]['get'])) {
          echo $key . '=' . $value . '&';
        }
        if (sizeof($this->path[$i]['post']) > 0) {
          echo '<br />';
          while (list($key, $value) = each($this->path[$i]['post'])) {
            echo '&nbsp;&nbsp;<strong>' . $key . '=' . $value . '</strong><br />';
          }
        }
        echo '<br />';
      }

      if (sizeof($this->snapshot) > 0) {
        echo '<br /><br />';

        echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . tep_array_to_string($this->snapshot['get'], array(tep_session_name())) . '<br />';
      }
    }

    function unserialize($broken) {
      for (reset($broken); $kv=each($broken); ) {
        $key = $kv['key'];
        if (gettype($this->$key) != "user function")
        $this->$key = $kv['value'];
      }
    }
  }
?>