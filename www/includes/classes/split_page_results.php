<?php
  class splitPageResults {
    var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $number_of_rows_per_page, $page_name;

/* class constructor */
    function splitPageResults($query, $max_rows, $count_key = '*', $page_holder = 'page', $number_of_rows = '') {
      global $HTTP_GET_VARS, $HTTP_POST_VARS;

      $this->sql_query = $query;
      $this->page_name = $page_holder;

      if (isset($HTTP_GET_VARS[$page_holder])) {
        $page = $HTTP_GET_VARS[$page_holder];
      } elseif (isset($HTTP_POST_VARS[$page_holder])) {
        $page = $HTTP_POST_VARS[$page_holder];
      } else {
        $page = '';
      }

      if (empty($page) || (int)$page==0) $page = 1;
      $this->current_page_number = $page;

	  if (strlen($number_of_rows) > 0) {
		$this->number_of_rows = $number_of_rows;
	  } else {
		$pos_to = mb_strlen($this->sql_query, 'CP1251');
		$pos_from = mb_strpos($this->sql_query, ' from', 0, 'CP1251');

		$pos_group_by = mb_strpos($this->sql_query, ' group by', $pos_from, 'CP1251');
		if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;
		if ($pos_group_by != false) {
		  if (preg_match('/group by ([^\s|,]+)/i', $this->sql_query, $regs)) $count_key = trim($regs[1]);
		}

		$pos_having = mb_strpos($this->sql_query, ' having', $pos_from, 'CP1251');
		if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

		$pos_order_by = mb_strpos($this->sql_query, ' order by', $pos_from, 'CP1251');
		if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

		if (mb_strpos($this->sql_query, 'distinct', 0, 'CP1251') || mb_strpos($this->sql_query, 'group by', 0, 'CP1251')) {
		  $count_string = 'distinct ' . tep_db_input($count_key);
		} else {
		  $count_string = tep_db_input($count_key);
		}

		$count_query = tep_db_unbuffered_query("select count(" . $count_string . ") as total " . mb_substr($this->sql_query, $pos_from, ($pos_to - $pos_from), 'CP1251'));
		$count = tep_db_fetch_array($count_query);

		$this->number_of_rows = $count['total'];
	  }

      $this->number_of_rows_per_page = ($max_rows > 0 ? $max_rows : $this->number_of_rows);

      $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

      if ($this->current_page_number > $this->number_of_pages) {
        $this->current_page_number = $this->number_of_pages;
      }

      $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      $this->sql_query .= " limit " . $offset . ", " . $this->number_of_rows_per_page;
    }

/* class functions */

// display split-page-number-links
    function display_links($max_page_links, $parameters = '') {
      global $request_type;

	  $exclude_parameters = array();
	  $get_params = explode('&', $parameters);
	  reset($get_params);
	  while (list(, $get_param) = each($get_params)) {
		list($get_param_name, $get_param_value) = explode('=', $get_param);
//		if (tep_not_null($get_param_name)) $exclude_parameters[] = $get_param_name;
	  }
	  $parameters = '';
	  reset($_GET);
	  while (list($k, $v) = each($_GET)) {
		$v = urldecode($v);
		if ($k!=tep_session_name() && !in_array($k, $exclude_parameters) && $k!=$this->page_name && tep_not_null($v)) {
		  $parameters .= $k . '=' . urlencode(stripslashes($v)) . '&';
		}
	  }

      $display_links_string = '';

      $class = 'class="pageResults"';

// button to first page - not displayed on first page
      if ($this->current_page_number > 1) $display_links_string .= '<a href="' . tep_href_link(PHP_SELF, $parameters, $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_FIRST_PAGE . ' ">' . PREVNEXT_BUTTON_FIRST . '</a>&nbsp;&nbsp;';

// previous button - not displayed on first page
      if ($this->current_page_number > 1) $display_links_string .= '<a href="' . tep_href_link(PHP_SELF, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';

// check if number_of_pages > $max_page_links
      $cur_window_num = intval($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = intval($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

// previous window of pages
      if ($cur_window_num > 1) $display_links_string .= '<a href="' . tep_href_link(PHP_SELF, $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

// page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if ($jump_to_page == $this->current_page_number) {
          $display_links_string .= '<span class="pageResultsActive">&nbsp;' . $jump_to_page . '&nbsp;</span>';
        } else {
          $display_links_string .= '&nbsp;<a href="' . tep_href_link(PHP_SELF, $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a>&nbsp;';
        }
      }

// next window of pages
      if ($cur_window_num < $max_window_num) $display_links_string .= '<a href="' . tep_href_link(PHP_SELF, $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>&nbsp;';

// next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) $display_links_string .= '&nbsp;<a href="' . tep_href_link(PHP_SELF, $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">' . PREVNEXT_BUTTON_NEXT . '</a>&nbsp;&nbsp;';

// button to last page - not displayed on last page
      if ($this->current_page_number < $this->number_of_pages) $display_links_string .= '<a href="' . tep_href_link(PHP_SELF, $parameters . $this->page_name . '=' . $this->number_of_pages, $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_LAST_PAGE . ' ">' . PREVNEXT_BUTTON_LAST . '</a>&nbsp;&nbsp;';

      return $display_links_string;
    }

// display number of total products found
    function display_count($text_output) {
      $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }

// display rows count per page
    function display_rows_per_page($rows_per_page = 10, $parameters = '') {
      global $request_type;

	  $per_page_array = array(10, 25, 50, 100);

	  $exclude_parameters = array(tep_session_name(), $this->page_name, 'per_page');
	  $get_params = explode('&', $parameters);
	  reset($get_params);
	  while (list(, $get_param) = each($get_params)) {
		list($get_param_name, $get_param_value) = explode('=', $get_param);
//		if (tep_not_null($get_param_name)) $exclude_parameters[] = $get_param_name;
	  }
	  $parameters = '';
	  reset($_GET);
	  while (list($k, $v) = each($_GET)) {
		$v = urldecode($v);
		if (!in_array($k, $exclude_parameters) && tep_not_null($v)) {
		  $parameters .= $k . '=' . urlencode(stripslashes($v)) . '&';
		}
	  }

      $display_rows_per_page_string = '';

	  reset($per_page_array);
	  while (list(, $per_page_count) = each($per_page_array)) {
		$display_rows_per_page_string .= ($per_page_count==$rows_per_page ? '<span class="pageResultsActive">&nbsp;' . $per_page_count . '&nbsp;</span>' : '&nbsp;<a href="' . tep_href_link(PHP_SELF, $parameters . 'per_page=' . $per_page_count) . '" class="pageResults">' . $per_page_count . '</a>&nbsp;');
	  }

      return $display_rows_per_page_string;
    }
  }
?>