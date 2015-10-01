<?php
////
// Sets the status of a banner
  function tep_set_banner_status($banners_id, $status) {
    if ($status == '1') {
      return tep_db_query("update " . TABLE_BANNERS . " set status = '1', date_status_change = now(), date_scheduled = NULL where banners_id = '" . (int)$banners_id . "'");
    } elseif ($status == '0') {
      return tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_status_change = now() where banners_id = '" . (int)$banners_id . "'");
    } else {
      return -1;
    }
  }

////
// Auto activate banners
  function tep_activate_banners() {
    $banners_query = tep_db_query("select banners_id, date_scheduled from " . TABLE_BANNERS . " where date_scheduled != ''");
    if (tep_db_num_rows($banners_query)) {
      while ($banners = tep_db_fetch_array($banners_query)) {
        if (date('Y-m-d H:i:s') >= $banners['date_scheduled']) {
          tep_set_banner_status($banners['banners_id'], '1');
        }
      }
    }
  }

////
// Auto expire banners
  function tep_expire_banners() {
    $banners_query = tep_db_query("select b.banners_id, b.expires_date, b.expires_impressions, sum(bh.banners_shown) as banners_shown from " . TABLE_BANNERS . " b, " . TABLE_BANNERS_HISTORY . " bh where b.status = '1' and b.banners_id = bh.banners_id group by b.banners_id");
    if (tep_db_num_rows($banners_query)) {
      while ($banners = tep_db_fetch_array($banners_query)) {
        if (tep_not_null($banners['expires_date'])) {
          if (date('Y-m-d H:i:s') >= $banners['expires_date']) {
            tep_set_banner_status($banners['banners_id'], '0');
          }
        } elseif (tep_not_null($banners['expires_impressions'])) {
          if ( ($banners['expires_impressions'] > 0) && ($banners['banners_shown'] >= $banners['expires_impressions']) ) {
            tep_set_banner_status($banners['banners_id'], '0');
          }
        }
      }
    }
  }

////
// Display a banner from the specified group or banner id ($identifier)
  function tep_display_banner($banner) {
	global $request_type;

	if (is_array($banner)) {
	  if (tep_not_null($banner['banners_html_text'])) {
		$banner_string = $banner['banners_html_text'];
	  } else {
		$banner_string = '<a href="' . tep_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']) . '">' . tep_image(($request_type=='SSL' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_IMAGES . $banner['banners_image'], $banner['banners_title']) . '</a>';
	  }

	  tep_update_banner_display_count($banner['banners_id']);

	  return $banner_string;
	}
  }

////
// Check to see if a banner exists
  function tep_banner_exists($identifier, $exclude_banner = '', $check_banner_conditions = true) {
	$banners_array = array();
	$group_info_query = tep_db_query("select banners_groups_id, status from " . TABLE_BANNERS_GROUPS . " where banners_groups_path = '" . tep_db_input($identifier) . "'");
	$group_info = tep_db_fetch_array($group_info_query);
	if ($group_info['status']=='1') {
	  if (tep_check_banner_conditions('group', $group_info['banners_groups_id']) || !$check_banner_conditions) {
		$banners_query = tep_db_query("select banners_id, banners_use_group_conditions from " . TABLE_BANNERS . " where banners_groups_id = '" . (int)$group_info['banners_groups_id'] . "' and status = '1'" . ($exclude_banner>0 ? " and banners_id <> '" . (int)$exclude_banner . "'" : ""));
		while ($banners = tep_db_fetch_array($banners_query)) {
		  if ($banners['banners_use_group_conditions']=='1') {
			$banners_array[] = $banners['banners_id'];
		  } elseif (tep_check_banner_conditions('banner', $banners['banners_id'])) {
			$banners_array[] = $banners['banners_id'];
		  }
		}
	  }
	  if (sizeof($banners_array) > 0) {
		$max_weight_info_query = tep_db_query("select max(banners_weight) as max_banners_weight from " . TABLE_BANNERS . " where banners_id in ('" . implode("', '", $banners_array) . "')");
		$max_weight_info = tep_db_fetch_array($max_weight_info_query);
		$max_weight = $max_weight_info['max_banners_weight'];
		$sql_query = "select banners_id, banners_title, banners_image, banners_html_text from " . TABLE_BANNERS . " where banners_id in ('" . implode("', '", $banners_array) . "') and banners_weight >= '" . rand(0, $max_weight) . "' order by rand() limit 1";
		$query = tep_db_query($sql_query);
		return tep_db_fetch_array($query);
	  }
	}
	return false;
  }

////
// Check conditions
  function tep_check_banner_conditions($condition_type, $condition_type_id) {
	$conditions = array();
	$i = 0;
	$condition_query = tep_db_query("select * from " . TABLE_BANNERS_CONDITIONS . " where banners_conditions_type = '" . tep_db_input($condition_type) . "' and banners_conditions_type_id = '" . (int)$condition_type_id . "'");
	if (tep_db_num_rows($condition_query) > 0) {
	  while ($condition = tep_db_fetch_array($condition_query)) {
		$condition_string = '';
		if (!isset($condition_equation)) {
		  $condition_equation = $condition['banners_conditions_equation'];
		  $condition_equation = str_replace('|', '||', $condition_equation);
		  $condition_equation = str_replace('&', '&&', $condition_equation);
		}

		if ($condition['banners_conditions_page_type']=='physical') $page = SCRIPT_FILENAME;
		else $page = PHP_SELF;

		if ($condition['banners_conditions_page_consist']=='match') $condition_string .= '\'' . $page . '\'==\'' . $condition['banners_conditions_page'] . '\'';
		elseif ($condition['banners_conditions_page_consist']=='not_match') $condition_string .= '\'' . $page . '\'!=\'' . $condition['banners_conditions_page'] . '\'';
		elseif ($condition['banners_conditions_page_consist']=='begin') $condition_string .= 'strpos(\'' . $page . '\', \'' . $condition['banners_conditions_page'] . '\')===0';
		elseif ($condition['banners_conditions_page_consist']=='not_begin') $condition_string .= 'strpos(\'' . $page . '\', \'' . $condition['banners_conditions_page'] . '\')!==0';
		elseif ($condition['banners_conditions_page_consist']=='contain') $condition_string .= 'strpos(\'' . $page . '\', \'' . $condition['banners_conditions_page'] . '\')!==false';
		elseif ($condition['banners_conditions_page_consist']=='not_contain') $condition_string .= 'strpos(\'' . $page . '\', \'' . $condition['banners_conditions_page'] . '\')===false';
		elseif ($condition['banners_conditions_page_consist']=='end') $condition_string .= 'strpos(\'' . strrev($page) . '\', \'' . strrev($condition['banners_conditions_page']) . '\')===0';
		elseif ($condition['banners_conditions_page_consist']=='not_end') $condition_string .= 'strpos(\'' . strrev($page) . '\', \'' . strrev($condition['banners_conditions_page']) . '\')!==0';

		if (tep_not_null($condition_string)) {
		  $conditions[$i+1] = '(' . $condition_string . ')';
		  $i ++;
		}
	  }

	  $temp_string = '';
	  if (tep_not_null($condition_equation)) {
		preg_match_all('/[^\d]*(\d+)[^\d]*/', $condition_equation, $regs);
		reset($regs[0]);
		while (list($k) = each($regs[0])) {
		  $temp_string .= str_replace($regs[1][$k], $conditions[$regs[1][$k]], $regs[0][$k]);
		}
		$temp_string = trim($temp_string);
		if (substr($temp_string, -2)=='||' || substr($temp_string, -2)=='&&') $temp_string = trim(substr($temp_string, 0, -2));
	  } else {
		$temp_string .= implode(' || ', $conditions);
	  }

	  if (tep_not_null($temp_string)) {
		$condition_string = 'if (' . $temp_string . ') $condition_check = true;';

		$condition_check = false;
		eval($condition_string);
	  } else {
		$condition_check = true;
	  }
	} else {
	  $condition_check = true;
	}

	return $condition_check;
  }

////
// Update the banner display statistics
  function tep_update_banner_display_count($banner_id) {
    // [2013-01-15] Evgeniy Spashko: OPTIM Temporary disabled banners_history update
  	/*$banner_check_query = tep_db_query("select count(*) as count from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banner_id . "' and date_format(banners_history_date, '%Y%m%d') = date_format(now(), '%Y%m%d')");
    $banner_check = tep_db_fetch_array($banner_check_query);

    if ($banner_check['count'] > 0) {
      tep_db_query("update " . TABLE_BANNERS_HISTORY . " set banners_shown = banners_shown + 1 where banners_id = '" . (int)$banner_id . "' and date_format(banners_history_date, '%Y%m%d') = date_format(now(), '%Y%m%d')");
    } else {
      tep_db_query("insert into " . TABLE_BANNERS_HISTORY . " (banners_id, banners_shown, banners_history_date) values ('" . (int)$banner_id . "', 1, now())");
    }*/
  }

////
// Update the banner click statistics
  function tep_update_banner_click_count($banner_id) {
    tep_db_query("update " . TABLE_BANNERS_HISTORY . " set banners_clicked = banners_clicked + 1 where banners_id = '" . (int)$banner_id . "' and date_format(banners_history_date, '%Y%m%d') = date_format(now(), '%Y%m%d')");
  }
?>