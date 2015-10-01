<?php
  class splitPageResults {
    function splitPageResults(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows, $count = false) {
      if ((int)$current_page_number < 1) $current_page_number = 1;

      $pos_to = mb_strlen($sql_query, 'CP1251');
      $pos_from = mb_strpos($sql_query, ' from', 0, 'CP1251');

      $pos_group_by = mb_strpos($sql_query, ' group by', $pos_from, 'CP1251');
      if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

      $pos_having = mb_strpos($sql_query, ' having', $pos_from, 'CP1251');
      if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

      $pos_order_by = mb_strpos($sql_query, ' order by', $pos_from, 'CP1251');
      if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      if($count)
      {
		  $reviews_count_query = tep_db_query($sql_query);
	      $query_num_rows = tep_db_num_rows($reviews_count_query);
      }
      else
      {
	      $reviews_count_query = tep_db_query("select count(*) as total " . mb_substr($sql_query, $pos_from, ($pos_to - $pos_from), 'CP1251'));
	      $reviews_count = tep_db_fetch_array($reviews_count_query);
	      $query_num_rows = $reviews_count['total'];
      }

      $num_pages = ceil($query_num_rows / $max_rows_per_page);
      if ($current_page_number > $num_pages) {
        $current_page_number = $num_pages;
      }
      if ((int)$current_page_number < 1) $current_page_number = 1;
      $offset = ($max_rows_per_page * ($current_page_number - 1));
      $sql_query .= " limit " . $offset . ", " . $max_rows_per_page;
    }

    function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page') {
      global $SCRIPT_FILENAME;

      if ( tep_not_null($parameters) && (substr($parameters, -1) != '&') ) $parameters .= '&';

// calculate number of pages needing links
      $num_pages = ceil($query_numrows / $max_rows_per_page);

      $pages_array = array();
      for ($i=1; $i<=$num_pages; $i++) {
        $pages_array[] = array('id' => $i, 'text' => $i);
      }

      if ($num_pages > 1) {
        $display_links = tep_draw_form('pages', basename($SCRIPT_FILENAME), '', 'get');

        if ($current_page_number > 1) {
          $display_links .= '<a href="' . tep_href_link(basename($SCRIPT_FILENAME), $parameters . $page_name . '=' . ($current_page_number - 1)) . '" class="splitPageLink">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';
        } else {
          $display_links .= PREVNEXT_BUTTON_PREV . '&nbsp;&nbsp;';
        }

        $display_links .= sprintf(TEXT_RESULT_PAGE, tep_draw_pull_down_menu($page_name, $pages_array, $current_page_number, 'onChange="this.form.submit();"'), $num_pages);

        if (($current_page_number < $num_pages) && ($num_pages != 1)) {
          $display_links .= '&nbsp;&nbsp;<a href="' . tep_href_link(basename($SCRIPT_FILENAME), $parameters . $page_name . '=' . ($current_page_number + 1)) . '" class="splitPageLink">' . PREVNEXT_BUTTON_NEXT . '</a>';
        } else {
          $display_links .= '&nbsp;&nbsp;' . PREVNEXT_BUTTON_NEXT;
        }

        if ($parameters != '') {
          if (substr($parameters, -1) == '&') $parameters = substr($parameters, 0, -1);
          $pairs = explode('&', $parameters);
          while (list(, $pair) = each($pairs)) {
            list($key,$value) = explode('=', $pair);
            $display_links .= tep_draw_hidden_field(urldecode($key), urldecode($value));
          }
        }

        if (SID) $display_links .= tep_draw_hidden_field(tep_session_name(), tep_session_id());

        $display_links .= '</form>';
      } else {
        $display_links = sprintf(TEXT_RESULT_PAGE, $num_pages, $num_pages);
      }

      return $display_links;
    }

    function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output) {
      $to_num = ($max_rows_per_page * $current_page_number);
      if ($to_num > $query_numrows) $to_num = $query_numrows;
      $from_num = ($max_rows_per_page * ($current_page_number - 1));
      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $query_numrows);
    }
  }
?>
