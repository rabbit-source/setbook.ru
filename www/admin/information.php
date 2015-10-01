<?php
  require('includes/application_top.php');

// calculate section path
  if (isset($HTTP_GET_VARS['sPath'])) {
    $sPath = $HTTP_GET_VARS['sPath'];
  } else {
    $sPath = '';
  }

  $current_section_id = 0;
  $current_section_disallow = array();
  if (tep_not_null($sPath)) {
    $sPath_array = tep_parse_section_path($sPath);
    $sPath = implode('_', $sPath_array);
    $current_section_id = $sPath_array[(sizeof($sPath_array)-1)];
	$current_section_disallow = explode(';', tep_get_section_info($current_section_id, $languages_id, 'sections_debug'));
  }
  if (tep_not_null($HTTP_GET_VARS['sID'])) {
	$current_section_disallow = explode(';', tep_get_section_info($HTTP_GET_VARS['sID'], $languages_id, 'sections_debug'));
	if (in_array('create', $current_section_disallow)) {
	  $keys = array_keys($current_section_disallow, 'create');
	  reset($keys);
	  while (list(, $key) = each($keys)) {
		unset($current_section_disallow[$key]);
	  }
	}
  }

  if (defined('STORE_TYPE') && STORE_TYPE=='visitka') {
	if (!in_array('create', $current_section_disallow)) $current_section_disallow[] = 'create';
  }

  $current_information_disallow = array();
  if (tep_not_null($HTTP_GET_VARS['iID'])) {
	$current_information_disallow = explode(';', tep_get_information_info($HTTP_GET_VARS['iID'], $languages_id, 'information_debug'));
  }

  $debug_sections = array();
  $debug_sections[] = array('id' => 'create', 'text' => DEBUG_MODES_DISALLOW_CREATE);
  $debug_sections[] = array('id' => 'edit', 'text' => DEBUG_MODES_DISALLOW_EDIT);
  $debug_sections[] = array('id' => 'delete', 'text' => DEBUG_MODES_DISALLOW_DELETE);

  $debug_information = array();
  $debug_information[] = array('id' => 'move', 'text' => DEBUG_MODES_DISALLOW_MOVE);
  $debug_information[] = array('id' => 'edit', 'text' => DEBUG_MODES_DISALLOW_EDIT);
  $debug_information[] = array('id' => 'delete', 'text' => DEBUG_MODES_DISALLOW_DELETE);

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (DEBUG_MODE=='off') {
	if ($action=='new_section' && in_array('create', $current_section_disallow)) {
	  $messageStack->add_session(WARNING_SECTION_CREATE_DISABLED);
	  tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action'))));
	} elseif ($action=='edit_section' && in_array('edit', $current_section_disallow)) {
	  $messageStack->add_session(WARNING_SECTION_EDIT_DISABLED);
	  tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action'))));
	} elseif ($action=='delete_section' && in_array('delete', $current_section_disallow)) {
	  $messageStack->add_session(WARNING_SECTION_DELETE_DISABLED);
	  tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action'))));
	} elseif ($action=='new_information' && in_array('edit', $current_information_disallow)) {
	  $messageStack->add_session(WARNING_INFORMATION_EDIT_DISABLED);
	  tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action'))));
	} elseif ($action=='delete_information' && in_array('delete', $current_information_disallow)) {
	  $messageStack->add_session(WARNING_INFORMATION_DELETE_DISABLED);
	  tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action'))));
	}
  }

  function tep_get_information_info($info_id, $language_id, $field = 'information_name') {
	$info_query = tep_db_query("select " . $field . " from " . TABLE_INFORMATION . " where information_id = '" . (int)$info_id . "' and language_id = '" . (int)$language_id . "'");
	$info_array = tep_db_fetch_array($info_query);
	return $info_array[$field];
  }

  function tep_get_section_info($section_id, $language_id, $field = 'sections_name') {
	$section_query = tep_db_query("select " . $field . " from " . TABLE_SECTIONS . " where sections_id = '" . (int)$section_id . "' and language_id = '" . (int)$language_id . "'");
	$section_array = tep_db_fetch_array($section_query);
	return $section_array[$field];
  }

  function tep_get_section_path($current_section_id = '') {
    global $sPath_array, $languages_id;

    if ($current_section_id == '') {
      $sPath_new = implode('_', $sPath_array);
    } else {
      if (sizeof($sPath_array) == 0) {
        $sPath_new = $current_section_id;
      } else {
        $sPath_new = '';
        $last_section_query = tep_db_query("select parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sPath_array[(sizeof($sPath_array)-1)] . "' and language_id = '" . (int)$languages_id . "'");
        $last_section = tep_db_fetch_array($last_section_query);

        $current_section_query = tep_db_query("select parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$current_section_id . "' and language_id = '" . (int)$languages_id . "'");
        $current_section = tep_db_fetch_array($current_section_query);

        if ($last_section['parent_id'] == $current_section['parent_id']) {
          for ($i = 0, $n = sizeof($sPath_array) - 1; $i < $n; $i++) {
            $sPath_new .= '_' . $sPath_array[$i];
          }
        } else {
          for ($i = 0, $n = sizeof($sPath_array); $i < $n; $i++) {
            $sPath_new .= '_' . $sPath_array[$i];
          }
        }

        $sPath_new .= '_' . $current_section_id;

        if (substr($sPath_new, 0, 1) == '_') {
          $sPath_new = substr($sPath_new, 1);
        }
      }
    }

    return 'sPath=' . $sPath_new;
  }

  function tep_generate_section_path($id, $from = 'section', $sections_array = '', $index = 0) {
    global $languages_id;

    if (!is_array($sections_array)) $sections_array = array();

    if ($from == 'information') {
      $sections_query = tep_db_query("select sections_id from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$id . "'");
      while ($sections = tep_db_fetch_array($sections_query)) {
        if ($sections['sections_id'] == '0') {
          $sections_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
        } else {
          $section_query = tep_db_query("select sections_name, parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sections['sections_id'] . "' and language_id = '" . (int)$languages_id . "'");
          $section = tep_db_fetch_array($section_query);
          $sections_array[$index][] = array('id' => $sections['sections_id'], 'text' => $section['sections_name']);
          if ( (tep_not_null($section['parent_id'])) && ($section['parent_id'] != '0') ) $sections_array = tep_generate_section_path($section['parent_id'], 'section', $sections_array, $index);
          $sections_array[$index] = array_reverse($sections_array[$index]);
        }
        $index++;
      }
    } elseif ($from == 'section') {
      $section_query = tep_db_query("select sections_name, parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
      $section = tep_db_fetch_array($section_query);
      $sections_array[$index][] = array('id' => $id, 'text' => $section['sections_name']);
      if ( (tep_not_null($section['parent_id'])) && ($section['parent_id'] != '0') ) $sections_array = tep_generate_section_path($section['parent_id'], 'section', $sections_array, $index);
    }

    return $sections_array;
  }

  function tep_output_generated_section_path($id, $from = 'section') {
    $calculated_section_path_string = '';
    $calculated_section_path = tep_generate_section_path($id, $from);
    for ($i=0, $n=sizeof($calculated_section_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_section_path[$i]); $j<$k; $j++) {
        $calculated_section_path_string .= $calculated_section_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_section_path_string = substr($calculated_section_path_string, 0, -16) . '<br>';
    }
    $calculated_section_path_string = substr($calculated_section_path_string, 0, -4);

    if (strlen($calculated_section_path_string) < 1) $calculated_section_path_string = TEXT_TOP;

    return $calculated_section_path_string;
  }

  function tep_get_section_tree($parent_id = '0', $spacing = '', $exclude = '', $section_tree_array = '', $include_itself = false) {
    global $languages_id;

    if (!is_array($section_tree_array)) $section_tree_array = array();
    if ( (sizeof($section_tree_array) < 1) && ($exclude != '0') ) $section_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

    if ($include_itself) {
      $section_query = tep_db_query("select sections_name from " . TABLE_SECTIONS . " where language_id = '" . (int)$languages_id . "' and sections_id = '" . (int)$parent_id . "'");
      $section = tep_db_fetch_array($section_query);
      $section_tree_array[] = array('id' => $parent_id, 'text' => $section['sections_name']);
    }

    $sections_query = tep_db_query("select sections_id, sections_name, parent_id from " . TABLE_SECTIONS . " where language_id = '" . (int)$languages_id . "' and parent_id = '" . (int)$parent_id . "' order by sort_order, sections_name");
    while ($sections = tep_db_fetch_array($sections_query)) {
      if ($exclude != $sections['sections_id']) $section_tree_array[] = array('id' => $sections['sections_id'], 'text' => $spacing . $sections['sections_name']);
      $section_tree_array = tep_get_section_tree($sections['sections_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $section_tree_array);
    }

    return $section_tree_array;
  }

  function tep_remove_section($section_id) {
	$info_query = tep_db_query("select information_id from " . TABLE_INFORMATION_TO_SECTIONS . " where sections_id = '" . (int)$section_id . "'");
	while ($info = tep_db_fetch_array($info_query)) {
	  tep_remove_information($info['information_id']);
	}

	tep_db_query("delete from " . TABLE_SECTIONS . " where sections_id = '" . (int)$section_id . "'");
	$sections_query = tep_db_query("select sections_id from " . TABLE_SECTIONS . " where parent_id = '" . (int)$section_id . "'");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  tep_remove_section($sections['sections_id']);
	}
  }

  function tep_remove_information($information_id) {
    tep_db_query("delete from " . TABLE_INFORMATION . " where information_id = '" . (int)$information_id . "'");
    tep_db_query("delete from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "'");
    tep_db_query("delete from " . TABLE_METATAGS . " where content_id = '" . (int)$information_id . "' and content_type = 'information'");
  }

  function tep_childs_in_section_count($sections_id) {
	$sections_count = 0;

	$sections_query = tep_db_query("select distinct sections_id from " . TABLE_SECTIONS . " where parent_id = '" . (int)$sections_id . "'");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  $sections_count++;
	  $sections_count += tep_childs_in_section_count($sections['sections_id']);
	}

	return $sections_count;
  }

  function tep_informations_in_section_count($sections_id, $include_deactivated = false) {
    $information_count = 0;

    if ($include_deactivated) {
      $information_query = tep_db_query("select count(distinct information_id) as total from " . TABLE_INFORMATION_TO_SECTIONS . " where sections_id = '" . (int)$sections_id . "'");
    } else {
      $information_query = tep_db_query("select count(distinct i.information_id) as total from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i.information_status = '1' and i2s.sections_id = '" . (int)$sections_id . "'");
    }

    $information = tep_db_fetch_array($information_query);

    $information_count += $information['total'];

    $childs_query = tep_db_query("select distinct sections_id from " . TABLE_SECTIONS . " where parent_id = '" . (int)$sections_id . "'");
    if (tep_db_num_rows($childs_query)) {
      while ($childs = tep_db_fetch_array($childs_query)) {
        $information_count += tep_informations_in_section_count($childs['sections_id'], $include_deactivated);
      }
    }

    return $information_count;
  }

////
// Parse and secure the sPath parameter values
  function tep_parse_section_path($sPath) {
// make sure the section IDs are integers
    $sPath_array = array_map('tep_string_to_int', explode('_', $sPath));

// make sure no duplicate section IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($sPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($sPath_array[$i], $tmp_array)) {
        $tmp_array[] = $sPath_array[$i];
      }
    }

    return $tmp_array;
  }

////
// Recursively go through the information sections and retreive all parent sectionss IDs
// TABLES: sections
  function tep_get_parent_sections(&$sections, $sections_id) {
	$parent_section_query = tep_db_query("select parent_id from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sections_id . "' limit 1");
	$parent_section = tep_db_fetch_array($parent_section_query);
	if ($parent_section['parent_id'] == 0) return true;
	$sections[sizeof($sections)] = $parent_section['parent_id'];
	if ($parent_section['parent_id'] != $sections_id) {
	  tep_get_parent_sections($sections, $parent_section['parent_id']);
	}
  }

  function tep_get_subsections(&$subsections_array, $parent_id = 0, $table = TABLE_SECTIONS) {
    $subsections_query = tep_db_query("select sections_id from " . $table . " where parent_id = '" . (int)$parent_id . "' group by sections_id");
    while ($subsections = tep_db_fetch_array($subsections_query)) {
      $subsections_array[sizeof($subsections_array)] = $subsections['sections_id'];
      if ($subsections['sections_id'] != $parent_id) {
        tep_get_subsections($subsections_array, $subsections['sections_id'], $table);
      }
    }
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['iID'])) {
			tep_db_query("update " . TABLE_INFORMATION . " set information_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where information_id = '" . (int)$HTTP_GET_VARS['iID'] . "'");
          } elseif (isset($HTTP_GET_VARS['sID'])) {
			tep_db_query("update " . TABLE_SECTIONS . " set sections_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where sections_id = '" . (int)$HTTP_GET_VARS['sID'] . "'");
          }
        } elseif ( ($HTTP_GET_VARS['lflag'] == '0') || ($HTTP_GET_VARS['lflag'] == '1') ) {
          if (isset($HTTP_GET_VARS['iID'])) {
			tep_db_query("update " . TABLE_INFORMATION . " set information_listing_status = '" . (int)$HTTP_GET_VARS['flag'] . "' where information_id = '" . (int)$HTTP_GET_VARS['iID'] . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_INFORMATION, 'sPath=' . $HTTP_GET_VARS['sPath'] . (isset($HTTP_GET_VARS['sID']) ? '&sID=' . $HTTP_GET_VARS['sID'] : '') . (isset($HTTP_GET_VARS['iID']) ? '&iID=' . $HTTP_GET_VARS['iID'] : '')));
        break;
	  case 'move_section_confirm':
		if (isset($HTTP_POST_VARS['sections_id']) && ($HTTP_POST_VARS['sections_id'] != $HTTP_POST_VARS['move_to_section_id'])) {
		  $sections_id = tep_db_prepare_input($HTTP_POST_VARS['sections_id']);
		  $new_parent_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_section_id']);
		  $section_path_info_query = tep_db_query("select sections_path from " . TABLE_SECTIONS . " where sections_id = '" . (int)$sections_id . "' limit 1");
		  $section_path_info = tep_db_fetch_array($section_path_info_query);
		  $section_path = $section_path_info['sections_path'];

		  $path = array($sections_id);
		  tep_get_subsections($path, $sections_id);

		  $duplicate_path_query = tep_db_query("select count(*) as total from " . TABLE_SECTIONS . " where parent_id = '" . (int)$new_parent_id . "' and sections_path = '" . tep_db_input($section_path) . "'");
		  $duplicate_path = tep_db_fetch_array($duplicate_path_query);
		  if ($duplicate_path['total'] > 0) {
			$messageStack->add_session(ERROR_PATH_ALREADY_EXISTS, 'error');

			tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'sID')) . 'sID=' . $sections_id));
		  } elseif (in_array($new_parent_id, $path)) {
			$messageStack->add_session(ERROR_CANNOT_MOVE_SECTION_TO_PARENT, 'error');

			tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'sID')) . 'sID=' . $sections_id));
		  } else {
			tep_db_query("update " . TABLE_SECTIONS . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where sections_id = '" . (int)$sections_id . "'");

			tep_redirect(tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'sPath', 'sID', 'page')) . 'sPath=' . $new_parent_id . '&sID=' . $sections_id));
		  }
		}
		break;
      case 'insert_section':
      case 'update_section':
		if (isset($HTTP_POST_VARS['sections_id'])) {
		  $sections_id = tep_db_prepare_input($HTTP_POST_VARS['sections_id']);
		} else {
		  $max_sections_id_query = tep_db_query("select max(sections_id) as sections_id from " . TABLE_SECTIONS . "");
		  $max_sections_id_array = tep_db_fetch_array($max_sections_id_query);
		  $sections_id = (int)$max_sections_id_array['sections_id'] + 1;
		}

        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
        $default_id = tep_db_prepare_input($HTTP_POST_VARS['default_id']);
        $sections_path = tep_db_prepare_input($HTTP_POST_VARS['sections_path']);
		$sections_status = tep_db_prepare_input($HTTP_POST_VARS['sections_status']);
		$sections_listing_status = tep_db_prepare_input($HTTP_POST_VARS['sections_listing_status']);
		$sections_sitemap_status = tep_db_prepare_input($HTTP_POST_VARS['sections_sitemap_status']);
        $templates_id = tep_db_prepare_input($HTTP_POST_VARS['templates_id']);
        $sections_path = preg_replace('/\_+/', '_', preg_replace('/[^\d\w]/i', '_', strtolower(trim($sections_path))));

		$disallowed_names = array();
		$disallowed_names[] = 'admin';
		$disallowed_names[] = 'images';
		$disallowed_names[] = 'includes';
		$error = false;
		$path_query = tep_db_query("select sections_path from " . TABLE_SECTIONS . " where parent_id = '" . (int)$current_section_id . "' and sections_id <> '" . (int)$sections_id . "'");
		while ($path = tep_db_fetch_array($path_query)) {
		  $disallowed_names[] = $path['sections_path'];
		}
		if (in_array($sections_path, $disallowed_names)) {
		  $messageStack->add(ERROR_SECTION_PATH_EXISTS);
		  $action = ($action == 'update_section' && tep_not_null($sections_id)) ? 'edit_section' : 'new_section';
		} elseif (!tep_not_null($sections_path)) {
		  $messageStack->add(ERROR_PATH_EMPTY);
		  $action = ($action == 'update_section' && tep_not_null($sections_id)) ? 'edit_section' : 'new_section';
		} else {
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$sections_name_array = $HTTP_POST_VARS['sections_name'];
			$sections_description_array = $HTTP_POST_VARS['sections_description'];
			$sections_debug = $HTTP_POST_VARS['sections_debug'];

			$language_id = $languages[$i]['id'];

			$sql_data_array = array('sort_order' => $sort_order,
									'sections_path' => $sections_path,
									'sections_status' => $sections_status,
									'sections_listing_status' => $sections_listing_status,
									'sections_sitemap_status' => $sections_sitemap_status,
									'templates_id' => $templates_id,
									'sections_name' => tep_db_prepare_input($sections_name_array[$language_id]),
									'sections_description' => tep_db_prepare_input($sections_description_array[$language_id]));
			if (DEBUG_MODE=='on') {
			  if (!is_array($sections_debug)) $sections_debug = array();
			  $sql_data_array['sections_debug'] = tep_db_prepare_input(implode(';', $sections_debug));
			}

			if ($action == 'insert_section') {
			  $insert_sql_data = array('parent_id' => $current_section_id,
									   'date_added' => 'now()',
									   'sections_id' => $sections_id,
									   'language_id' => $languages[$i]['id']);

			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_SECTIONS, $sql_data_array);
			} elseif ($action == 'update_section') {
			  $update_sql_data = array('last_modified' => 'now()');

			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_SECTIONS, $sql_data_array, 'update', "sections_id = '" . (int)$sections_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  tep_db_query("update " . TABLE_INFORMATION_TO_SECTIONS . " set information_default_status = '0' where sections_id = '" . (int)$sections_id . "'");
		  tep_db_query("update " . TABLE_INFORMATION_TO_SECTIONS . " set information_default_status = '1' where information_id = '" . (int)$default_id . "' and sections_id = '" . (int)$sections_id . "'");

		  tep_redirect(tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sections_id));
		}

        break;
      case 'delete_section_confirm':
        if (isset($HTTP_POST_VARS['sections_id'])) {
          $sections_id = tep_db_prepare_input($HTTP_POST_VARS['sections_id']);

          $sections = tep_get_section_tree($sections_id, '', '0', '', true);
          $informations = array();
          $informations_delete = array();

          for ($i=0, $n=sizeof($sections); $i<$n; $i++) {
            $information_ids_query = tep_db_query("select information_id from " . TABLE_INFORMATION_TO_SECTIONS . " where sections_id = '" . (int)$sections[$i]['id'] . "'");

            while ($information_ids = tep_db_fetch_array($information_ids_query)) {
              $informations[$information_ids['information_id']]['sections'][] = $sections[$i]['id'];
            }
          }

          reset($informations);
          while (list($key, $value) = each($informations)) {
            $section_ids = '';

            for ($i=0, $n=sizeof($value['sections']); $i<$n; $i++) {
              $section_ids .= "'" . (int)$value['sections'][$i] . "', ";
            }
            $section_ids = substr($section_ids, 0, -2);

            $check_query = tep_db_query("select count(*) as total from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$key . "' and sections_id not in (" . $section_ids . ")");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
              $informations_delete[$key] = $key;
            }
          }

// removing sections can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($sections); $i<$n; $i++) {
            tep_remove_section($sections[$i]['id']);
          }

          reset($informations_delete);
          while (list($key) = each($informations_delete)) {
            tep_remove_information($key);
          }
        }

        tep_redirect(tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath));
        break;
      case 'delete_information_confirm':
        if (isset($HTTP_POST_VARS['information_id'])) {
		  $information_id = tep_db_prepare_input($HTTP_POST_VARS['information_id']);
		  $sections = $HTTP_POST_VARS['sections'];
		  if (!is_array($sections)) $sections = array();
		  reset($sections);
		  while (list(, $sections_id) = each($sections)) {
			tep_db_query("delete from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "' and sections_id = '" . (int)$sections_id . "'");
		  }
		  $sections_count_query = tep_db_query("select count(*) as total from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "'");
		  $sections_count = tep_db_fetch_array($sections_count_query);
		  if ($sections_count['total']=='0') {
			tep_remove_information($information_id);
		  }
        }

        tep_redirect(tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath));
        break;
      case 'insert_information':
      case 'update_information':
        if (isset($HTTP_POST_VARS['edit_x']) || isset($HTTP_POST_VARS['edit_y'])) {
          $action = 'new_information';
        } else {
          if (isset($HTTP_GET_VARS['iID'])) {
			$information_id = tep_db_prepare_input($HTTP_GET_VARS['iID']);
		  } else {
			$max_information_id_query = tep_db_query("select max(information_id) as information_id from " . TABLE_INFORMATION . "");
			$max_information_id_array = tep_db_fetch_array($max_information_id_query);
			$information_id = (int)$max_information_id_array['information_id'] + 1;
		  }

		  $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
		  $sections = $HTTP_POST_VARS['sections'];
		  if ($section_type=='news') {
			$information_path = date('YmdHis');
		  } else {
			$information_path = tep_db_prepare_input($HTTP_POST_VARS['information_path']);
			$information_path = preg_replace('/\_+/', '_', preg_replace('/[^_\d\w\.]/i', '_', strtolower(trim($information_path))));
		  }
		  $information_status = tep_db_prepare_input($HTTP_POST_VARS['information_status']);
		  $information_listing_status = tep_db_prepare_input($HTTP_POST_VARS['information_listing_status']);
		  $information_sitemap_status = tep_db_prepare_input($HTTP_POST_VARS['information_sitemap_status']);
		  $information_default_status = tep_db_prepare_input($HTTP_POST_VARS['information_default_status']);
		  $information_redirect = tep_db_prepare_input($HTTP_POST_VARS['information_redirect']);
		  $debug_information = $HTTP_POST_VARS['debug_information'];

		  if (!is_array($sections)) $sections = array($current_section_id);

		  $disallowed_names = array();
		  $error = false;
		  $path_query = tep_db_query("select i.information_path from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i2s.sections_id in ('" . implode("', '", $sections) . "') and i.language_id = '" . (int)$languages_id . "' and i.information_id <> '" . (int)$information_id . "'");
		  while ($path = tep_db_fetch_array($path_query)) {
			$disallowed_names[] = $path['information_path'];
		  }
		  if (in_array($information_path, $disallowed_names)) {
			$error = true;
			$messageStack->add(ERROR_INFORMATION_PATH_EXISTS);
		  } elseif (!tep_not_null($information_path)) {
			$error = true;
			$messageStack->add(ERROR_PATH_EMPTY);
		  }

		  if (DEBUG_MODE=='off' && $action=='update_information' && in_array('move', $current_information_disallow)) {
			$move_error = false;
			$sections_to_information = array();
			$sections_query = tep_db_query("select sections_id from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "'");
			while ($sections_array = tep_db_fetch_array($sections_query)) {
			  $sections_to_information[] = $sections_array['sections_id'];
			}
			if (!is_array($sections)) $sections = array();
			if (sizeof($sections_to_information)!=sizeof($sections)) {
			  $move_error = true;
			} else {
			  reset($sections);
			  while (list(, $sections_id) = each($sections)) {
				$sections_query = tep_db_query("select count(*) as total from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "' and sections_id = '" . (int)$sections_id . "'");
				$sections_array = tep_db_fetch_array($sections_query);
				if ($sections_array['total']=='0') {
				  $move_error = true;
				}
			  }
			}
			if ($move_error) {
			  $HTTP_POST_VARS['sections'] = $sections_to_information;
			  $error = true;
			  $messageStack->add(WARNING_INFORMATION_MOVE_DISABLED);
			}
		  }

		  if (!$error) {
			$languages = tep_get_languages();
			$information_name_array = $HTTP_POST_VARS['information_name'];
			$information_description_array = $HTTP_POST_VARS['information_description'];
        	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			  $language_id = $languages[$i]['id'];

			  $description = str_replace('\\\"', '"', $information_description_array[$language_id]);
			  $description = str_replace('\"', '"', $description);
			  $description = str_replace("\\\'", "\'", $description);
			  $description = str_replace('="' . str_replace('http://', 'http://www.', HTTP_SERVER) . '/', '="/', $description);
			  $description = str_replace('="' . HTTP_SERVER . '/', '="/', $description);
			  $description = str_replace(' - ', ' &ndash; ', $description);
			  $description = str_replace(' &mdash; ', ' &ndash; ', $description);

			  $sql_data_array = array('sort_order' => $sort_order,
									  'information_path' => $information_path,
									  'information_redirect' => $information_redirect,
									  'information_status' => $information_status,
									  'information_listing_status' => $information_listing_status,
									  'information_sitemap_status' => $information_sitemap_status,
									  'information_name' => tep_db_prepare_input($information_name_array[$language_id]),
									  'information_description' => $description);
			  if (DEBUG_MODE=='on') {
				if (!is_array($information_debug)) $information_debug = array();
				$sql_data_array['information_debug'] = tep_db_prepare_input(implode(';', $information_debug));
			  }

			  if ($action == 'insert_information') {
				$insert_sql_data = array('date_added' => 'now()',
										 'information_id' => $information_id,
										 'language_id' => $language_id);

				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

				tep_db_perform(TABLE_INFORMATION, $sql_data_array);
			  } elseif ($action == 'update_information') {
				$update_sql_data = array('last_modified' => 'now()');

				$sql_data_array = array_merge($sql_data_array, $update_sql_data);

				tep_db_perform(TABLE_INFORMATION, $sql_data_array, 'update', "information_id = '" . (int)$information_id . "' and language_id = '" . (int)$language_id . "'");
			  }
			}

			tep_update_blocks($information_id, 'information');

			reset($sections);
			while (list(, $sections_id) = each($sections)) {
			  $sections_count_query = tep_db_query("select count(*) as total from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "' and sections_id = '" . (int)$sections_id . "'");
			  $sections_count = tep_db_fetch_array($sections_count_query);
			  if ($sections_count['total']=='0') {
				tep_db_query("insert into " . TABLE_INFORMATION_TO_SECTIONS . " (information_id, sections_id) values ('" . (int)$information_id . "', '" . (int)$sections_id . "')");
			  }
			}
			tep_db_query("delete from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$information_id . "' and sections_id not in ('" . implode("', '", $sections) . "')");

			if ($current_section_id == 0 && $information_path=='index') {
			  tep_db_query("update " . TABLE_INFORMATION_TO_SECTIONS . " set information_default_status = '1' where information_id = '" . (int)$information_id . "' and sections_id = '" . (int)$current_section_id . "'");
			}

			if ($information_default_status=='1') {
			  tep_db_query("update " . TABLE_INFORMATION_TO_SECTIONS . " set information_default_status = '0' where sections_id = '" . (int)$current_section_id . "'");
			}
			tep_db_query("update " . TABLE_INFORMATION_TO_SECTIONS . " set information_default_status = '" . (int)$information_default_status . "' where information_id = '" . (int)$information_id . "' and sections_id = '" . (int)$current_section_id . "'");

			tep_redirect(tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sections[0] . '&iID=' . $information_id));
		  } else {
			$action = 'new_information';
		  }
        }
        break;
    }
  }

  $templates = array(array('id' => '', 'text' => TEXT_CHOOSE));
  $templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by templates_name");
  while ($templates_array = tep_db_fetch_array($templates_query)) {
	$templates[] = array('id' => $templates_array['templates_id'], 'text' => $templates_array['templates_name']);
  }

  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'Image/')) {
	$messageStack->add(WARNING_IMAGES_IMAGE_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_IMAGES . 'Image/')) {
	$messageStack->add(WARNING_IMAGES_IMAGE_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'Flash/')) {
	$messageStack->add(WARNING_IMAGES_FLASH_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_IMAGES . 'Flash/')) {
	$messageStack->add(WARNING_IMAGES_FLASH_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'File/')) {
	$messageStack->add(WARNING_IMAGES_FILE_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_IMAGES . 'File/')) {
	$messageStack->add(WARNING_IMAGES_FILE_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
  if (!is_dir(DIR_FS_CATALOG_IMAGES . 'Media/')) {
	$messageStack->add(WARNING_IMAGES_MEDIA_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_IMAGES . 'Media/')) {
	$messageStack->add(WARNING_IMAGES_MEDIA_DIRECTORY_NOT_WRITEABLE, 'warning');
  }

  $sections_tree = tep_get_section_tree(0, '&nbsp; ');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
<?php
  if ($action == 'new_information') {
    $parameters = array('information_name' => '',
						'information_description' => '',
						'information_id' => '',
						'date_added' => '',
						'information_path' => '',
						'information_redirect' => '',
						'last_modified' => '');

    $iInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['iID']) && empty($HTTP_POST_VARS)) {
      $information_query = tep_db_query("select * from " . TABLE_INFORMATION . " where information_id = '" . (int)$HTTP_GET_VARS['iID'] . "' and language_id = '" . (int)$languages_id . "'");
      $information = tep_db_fetch_array($information_query);
	  $information_sections_query = tep_db_query("select sections_id, information_default_status from " . TABLE_INFORMATION_TO_SECTIONS . " where information_id = '" . (int)$HTTP_GET_VARS['iID'] . "'");
	  while ($information_sections = tep_db_fetch_array($information_sections_query)) {
		$information['sections'][] = $information_sections['sections_id'];
		if ($information_sections['information_default_status']=='1' && $information_sections['sections_id']==$current_section_id) $information['information_default_status'] = 1;
	  }

	  if (!is_array($information)) $information = array();
      $iInfo->objectInfo($information);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $iInfo->objectInfo($HTTP_POST_VARS);
      $information_name = array_map("stripslashes", $HTTP_POST_VARS['information_name']);
	  $iInfo->information_name = $information_name[$languages_id];
      $information_description = array_map("stripslashes", $HTTP_POST_VARS['information_description']);
	  $iInfo->information_description = $information_description[$languages_id];
    }

    $languages = tep_get_languages();

	if (!isset($iInfo->information_status)) $iInfo->information_status = 1;
	if (!isset($iInfo->information_listing_status)) $iInfo->information_listing_status = 1;
	if (!isset($iInfo->information_sitemap_status)) $iInfo->information_sitemap_status = 1;
	if (!isset($iInfo->sections)) $iInfo->sections[] = $current_section_id;

	$form_action = (isset($HTTP_GET_VARS['iID'])) ? 'update_information' : 'insert_information';

	echo tep_draw_form('new_information', FILENAME_INFORMATION, 'sPath=' . $sPath . (isset($HTTP_GET_VARS['iID']) ? '&iID=' . $HTTP_GET_VARS['iID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo isset($HTTP_GET_VARS['iID']) ? sprintf(TEXT_EDIT_INFORMATION, $iInfo->information_name, tep_output_generated_section_path($current_section_id)) : sprintf(TEXT_NEW_INFORMATION, tep_output_generated_section_path($current_section_id)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="1">
          <tr valign="top">
            <td class="main" width="250"><?php echo TEXT_INFORMATION_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('information_status', '1', $iInfo->information_status==1) . '&nbsp;' . TEXT_INFORMATION_AVAILABLE . '&nbsp;' . tep_draw_radio_field('information_status', '0', $iInfo->information_status==0) . '&nbsp;' . TEXT_INFORMATION_NOT_AVAILABLE; ?></td>
          </tr>
          <tr valign="top">
            <td class="main"><?php echo TEXT_INFORMATION_LISTING_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('information_listing_status', '1', $iInfo->information_listing_status==1) . '&nbsp;' . TEXT_INFORMATION_AVAILABLE . '&nbsp;' . tep_draw_radio_field('information_listing_status', '0', $iInfo->information_listing_status==0) . '&nbsp;' . TEXT_INFORMATION_NOT_AVAILABLE; ?></td>
          </tr>
          <tr valign="top">
            <td class="main"><?php echo TEXT_INFORMATION_SITEMAP_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_radio_field('information_sitemap_status', '1', $iInfo->information_sitemap_status==1) . '&nbsp;' . TEXT_INFORMATION_AVAILABLE . '&nbsp;' . tep_draw_radio_field('information_sitemap_status', '0', $iInfo->information_sitemap_status==0) . '&nbsp;' . TEXT_INFORMATION_NOT_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main">&nbsp;</td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('information_default_status', '1', $iInfo->information_default_status) . sprintf(TEXT_INFORMATION_DEFAULT_STATUS, ($current_section_id > 0 ? tep_get_section_info($current_section_id, $languages_id) : TEXT_TOP)); ?></td>
          </tr>
<?php
	if ($section_type=='news') {
	  echo tep_draw_hidden_field('sections[]', $current_section_id);
	} else {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main"><?php echo TEXT_INFORMATION_SECTION; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('sections[]', $sections_tree, $iInfo->sections, 'multiple="true" size="' . (sizeof($sections_tree)<5 ? sizeof($sections_tree) + 1 : '5') . '"'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main"><?php if ($i == 0) echo TEXT_INFORMATION_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('information_name[' . $languages[$i]['id'] . ']', (isset($information_name[$languages[$i]['id']]) ? $information_name[$languages[$i]['id']] : tep_get_information_info($iInfo->information_id, $languages[$i]['id'])), 'size="35"'); ?></td>
          </tr>
<?php
    }
?>
		</table>
<?php echo tep_load_blocks($iInfo->information_id, 'information', $template_selected); ?>
		<table border="0" width="100%" cellspacing="0" cellpadding="1">
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr valign="top">
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_INFORMATION_DESCRIPTION; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
	  $field_value = (isset($information_description[$languages[$i]['id']]) ? $information_description[$languages[$i]['id']] : tep_get_information_info($iInfo->information_id, $languages[$i]['id'], 'information_description'));
	  $field_value = str_replace('\\\"', '"', $field_value);
	  $field_value = str_replace('\"', '"', $field_value);
	  $field_value = str_replace("\\\'", "\'", $field_value);
	  $field_value = str_replace('="/', '="' . HTTP_SERVER . '/', $field_value);
	  $editor = new editor('information_description[' . $languages[$i]['id'] . ']');
	  $editor->Value = $field_value;
	  $editor->Height = '280';
	  $editor->Create();
?></td>
          </tr>
<?php
	}
?>
<?php
	if ($section_type=='news') {
	} else {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $iInfo->sort_order, 'size="3"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main"><?php echo TEXT_REWRITE_NAME; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_catalog_href_link(FILENAME_DEFAULT, 'sPath=' . $current_section_id) . tep_draw_input_field('information_path', $iInfo->information_path, 'size="' . (tep_not_null($iInfo->information_path) ? strlen($iInfo->information_path) - 1 : '7') . '"') . '.html'; ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr valign="top">
            <td class="main" width="250"><?php echo TEXT_INFORMATION_REDIRECT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('information_redirect', $iInfo->information_redirect, 'size="30"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . (isset($HTTP_GET_VARS['iID']) ? '&iID=' . $HTTP_GET_VARS['iID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
    echo tep_draw_form('goto', FILENAME_INFORMATION, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('sPath', $sections_tree, $current_section_id, 'onChange="this.form.submit();"');
	reset($HTTP_GET_VARS);
	while (list($k, $v) = each($HTTP_GET_VARS)) {
	  if (!in_array($k, array(tep_session_name(), 'sPath', 'sID', 'iID'))) echo tep_draw_hidden_field($k, $v);
	}
    echo '</form>';
?>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SECTIONS_INFORMATIONS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INFO; ?>&nbsp;</td>
              </tr>
<?php
	$sort_array = array();
	$sections_query = tep_db_query("select sections_id, sort_order from " . TABLE_SECTIONS . " where parent_id = '" . (int)$current_section_id . "' and language_id = '" . (int)$languages_id . "' order by sort_order, sections_name");
	while ($sections = tep_db_fetch_array($sections_query)) {
	  $sort_array['section:' . $sections['sections_id']] = $sections['sort_order'];
	}
    $informations_query = tep_db_query("select i.information_id, (i.sort_order - i2s.information_default_status) as sort_order from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "' and i2s.sections_id = '" . (int)$current_section_id . "' order by i2s.information_default_status desc, i.sort_order, i.information_name");
    while ($informations = tep_db_fetch_array($informations_query)) {
	  $sort_array['information:' . $informations['information_id']] = $informations['sort_order'];
	}
	asort($sort_array);
	$sections_count = 0;
    $informations_count = 0;
	$rows = 0;
	reset($sort_array);
	while (list($s) = each($sort_array)) {
	  list($type, $id) = explode(':', $s);
	  if ($type=='section') {
		$sections_query = tep_db_query("select * from " . TABLE_SECTIONS . " where sections_id = '" . (int)$id . "' and language_id = '" . (int)$languages_id . "'");
		$sections = tep_db_fetch_array($sections_query);
		$sections_count++;
		$rows++;

		if ((!isset($HTTP_GET_VARS['sID']) && !isset($HTTP_GET_VARS['iID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $sections['sections_id']))) && !isset($iInfo) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
		  $section_childs = array('childs_count' => tep_childs_in_section_count($sections['sections_id']));
		  $section_informations = array('informations_count' => tep_informations_in_section_count($sections['sections_id']));
		  $sections['default_id'] = 0;
		  $sections['informations'] = array(array('id' => '', 'text' => TEXT_CHOOSE));
		  $info_query = tep_db_query("select i.information_id, i.information_name, i2s.information_default_status from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = i2s.information_id and i2s.sections_id = '" . $sections['sections_id'] . "' and i.language_id = '" . (int)$languages_id . "' order by i.sort_order, i.information_name");
		  while ($info = tep_db_fetch_array($info_query)) {
			$sections['informations'][] = array('id' => $info['information_id'], 'text' => $info['information_name']);
			if ($info['information_default_status']=='1') $sections['default_id'] = $info['information_id'];
		  }

		  $sInfo_array = array_merge($sections, $section_childs, $section_informations);
		  $sInfo = new objectInfo($sInfo_array);
		}

		if (isset($sInfo) && is_object($sInfo) && ($sections['sections_id'] == $sInfo->sections_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INFORMATION, tep_get_section_path($sections['sections_id'])) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sections['sections_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_INFORMATION, tep_get_section_path($sections['sections_id'])) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;[' . $sections['sort_order'] . ']&nbsp;<strong>' . $sections['sections_name'] . '</strong>'; ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($sections['sections_status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_INFORMATION, 'action=setflag&flag=0&sID=' . $sections['sections_id'] . '&sPath=' . $sPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_INFORMATION, 'action=setflag&flag=1&sID=' . $sections['sections_id'] . '&sPath=' . $sPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($sections['sections_id'] == $sInfo->sections_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sections['sections_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  } else {
		$informations_query = tep_db_query("select i.*, i2s.information_default_status from " . TABLE_INFORMATION . " i, " . TABLE_INFORMATION_TO_SECTIONS . " i2s where i.information_id = '" . (int)$id . "' and i.information_id = i2s.information_id and i.language_id = '" . (int)$languages_id . "'");
		$informations = tep_db_fetch_array($informations_query);
		$informations_count++;
		$rows++;

		if ( (!isset($HTTP_GET_VARS['iID']) && !isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['iID']) && ($HTTP_GET_VARS['iID'] == $informations['information_id']))) && !isset($iInfo) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
		  $iInfo_array = $informations;
		  $iInfo = new objectInfo($iInfo_array);
		}

		if (isset($iInfo) && is_object($iInfo) && ($informations['information_id'] == $iInfo->information_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&iID=' . $informations['information_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_catalog_href_link(FILENAME_CATALOG_DEFAULT, 'sPath=' . $current_section_id. '&info_id=' . $informations['information_id'] . '&version=new') . '" target="_blank">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW, '16', '16', 'style="margin: 3px 0 -3px 0;"') . '</a>&nbsp;[' . $informations['sort_order'] . ']&nbsp;' . ($informations['information_default_status']=='1' ? '<strong>' . $informations['information_name'] . '</strong>' : $informations['information_name']); ?></td>
                <td class="dataTableContent" align="center">
<?php
		if ($informations['information_status'] == '1') {
		  echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_INFORMATION, 'action=setflag&flag=0&iID=' . $informations['information_id'] . '&sPath=' . $sPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
		} else {
		  echo '<a href="' . tep_href_link(FILENAME_INFORMATION, 'action=setflag&flag=1&iID=' . $informations['information_id'] . '&sPath=' . $sPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
		}
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($iInfo) && is_object($iInfo) && ($informations['information_id'] == $iInfo->information_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&iID=' . $informations['information_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
	}

    $sPath_back = '';
    if (sizeof($sPath_array) > 0) {
	  $parents = array();
	  tep_get_parent_sections($parents, $current_section_id);
	  $parents = array_reverse($parents);
	  $sPath_back = implode('_', $parents);
    }

    $sPath_back = (tep_not_null($sPath_back)) ? 'sPath=' . $sPath_back . '&' : '';
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo TEXT_SECTIONS . '&nbsp;' . $sections_count . '<br>' . TEXT_INFORMATIONS . '&nbsp;' . $informations_count; ?></td>
                    <td align="right" class="smallText"><?php if (sizeof($sPath_array) > 0) echo '<a href="' . tep_href_link(FILENAME_INFORMATION, $sPath_back . 'sID=' . $current_section_id) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;'; echo ((DEBUG_MODE=='off' && in_array('create', $current_section_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&action=new_section') . '">' . tep_image_button('button_new_section.gif', IMAGE_NEW_SECTION) . '</a>&nbsp;') . '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&action=new_information') . '">' . tep_image_button('button_new_record.gif', IMAGE_NEW_RECORD) . '</a>'; ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_section':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_SECTION . '</strong>');

        $contents = array('form' => tep_draw_form('newsection', FILENAME_INFORMATION, 'action=insert_section&sPath=' . $sPath, 'post'));
        $contents[] = array('text' => TEXT_NEW_SECTION_INTRO);

        $section_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $section_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('sections_name[' . $languages[$i]['id'] . ']', (isset($sections_name[$languages[$i]['id']]) ? $sections_name[$languages[$i]['id']] : ''), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_SECTIONS_NAME . $section_inputs_string);

		$section_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $section_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('sections_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', (isset($sections_description[$languages[$i]['id']]) ? $sections_description[$languages[$i]['id']] : ''));
		}
		$contents[] = array('text' => '<br>' . TEXT_SECTIONS_DESCRIPTION . $section_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_SECTIONS_TEMPLATE . '<br>' . tep_draw_pull_down_menu('templates_id', $templates));

        $contents[] = array('text' => '<br>' . TEXT_EDIT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', '', 'size="2"'));
        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_DEFAULT, 'sPath=' . $current_section_id) . tep_draw_input_field('sections_path', '', 'size="7"') . '/');
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('sections_status', '1') . TEXT_SECTIONS_STATUS);
        $contents[] = array('text' => tep_draw_checkbox_field('sections_listing_status', '1') . TEXT_SECTIONS_LISTING_STATUS);
        $contents[] = array('text' => tep_draw_checkbox_field('sections_sitemap_status', '1') . TEXT_SECTIONS_SITEMAP_STATUS);

		if (DEBUG_MODE=='on') {
		  $contents[] = array('text' => '<br>' . DEBUG_MODES_DISALLOW . '<br>' . tep_draw_pull_down_menu('sections_debug[]', $debug_sections, '', 'multiple="true"'));
		}

        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit_section':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_SECTION . '</strong>');

        $contents = array('form' => tep_draw_form('sections', FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'sPath')) . 'action=update_section&sPath=' . $sPath, 'post') . tep_draw_hidden_field('sections_id', $sInfo->sections_id));
        $contents[] = array('text' => TEXT_EDIT_INTRO);

        $section_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $section_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('sections_name[' . $languages[$i]['id'] . ']', tep_get_section_info($sInfo->sections_id, $languages[$i]['id']), 'size="30"');
        }
        $contents[] = array('text' => '<br>' . TEXT_EDIT_SECTIONS_NAME . $section_inputs_string);

		$section_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $section_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('sections_description[' . $languages[$i]['id'] . ']', 'soft', '30', '3', tep_get_section_info($sInfo->sections_id, $languages[$i]['id'], 'sections_description'));
		}
		$contents[] = array('text' => '<br>' . TEXT_SECTIONS_DESCRIPTION . $section_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_SECTIONS_TEMPLATE . '<br>' . tep_draw_pull_down_menu('templates_id', $templates, $sInfo->templates_id));

		$contents[] = array('text' => '<br>' . TEXT_SECTIONS_DEFAULT_INFORMATION . '<br>' . tep_draw_pull_down_menu('default_id', $sInfo->informations, $sInfo->default_id));

        $contents[] = array('text' => '<br>' . TEXT_EDIT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $sInfo->sort_order, 'size="2"'));
        $contents[] = array('text' => '<br>' . TEXT_REWRITE_NAME . '<br>' . tep_catalog_href_link(FILENAME_DEFAULT, 'sPath=' . $current_section_id) . tep_draw_input_field('sections_path', $sInfo->sections_path, 'size="' . (tep_not_null($sInfo->sections_path) ? strlen($sInfo->sections_path) - 1 : '7') . '"') . '/');
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('sections_status', '1', $sInfo->sections_status) . TEXT_SECTIONS_STATUS);
        $contents[] = array('text' => tep_draw_checkbox_field('sections_listing_status', '1', $sInfo->sections_listing_status) . TEXT_SECTIONS_LISTING_STATUS);
        $contents[] = array('text' => tep_draw_checkbox_field('sections_sitemap_status', '1', $sInfo->sections_sitemap_status) . TEXT_SECTIONS_SITEMAP_STATUS);

		if (DEBUG_MODE=='on') {
		  $contents[] = array('text' => '<br>' . DEBUG_MODES_DISALLOW . '<br>' . tep_draw_pull_down_menu('sections_debug[]', $debug_sections, explode(';', $sInfo->sections_debug), 'multiple="true"'));
		}

        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sInfo->sections_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move_section':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_SECTION . '</strong>');

        $contents = array('form' => tep_draw_form('sections', FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'page')) . 'action=move_section_confirm') . tep_draw_hidden_field('sections_id', $sInfo->sections_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_SECTIONS_INTRO, $sInfo->sections_name));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $sInfo->sections_name) . '<br>' . tep_draw_pull_down_menu('move_to_section_id', tep_get_section_tree(), $current_section_id));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_INFORMATION, tep_get_all_get_params(array('action', 'sID')) . 'sID=' . $sInfo->sections_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_section':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_SECTION . '</strong>');

        $contents = array('form' => tep_draw_form('sections', FILENAME_INFORMATION, 'action=delete_section_confirm&sPath=' . $sPath) . tep_draw_hidden_field('sections_id', $sInfo->sections_id));
        $contents[] = array('text' => TEXT_DELETE_SECTION_INTRO);
        $contents[] = array('text' => '<br><strong>' . $sInfo->sections_name . '</strong>');
        if ($sInfo->childs_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CHILDS, $sInfo->childs_count));
        if ($sInfo->informations_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_INFORMATIONS, $sInfo->informations_count));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sInfo->sections_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_information':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_INFORMATION . '</strong>');

        $contents = array('form' => tep_draw_form('information', FILENAME_INFORMATION, 'action=delete_information_confirm&sPath=' . $sPath) . tep_draw_hidden_field('information_id', $iInfo->information_id));
        $contents[] = array('text' => TEXT_DELETE_INFORMATION_INTRO);
        $contents[] = array('text' => '<br><strong>' . $iInfo->information_name . '</strong>');

        $sections_string = '';
        $sections = tep_generate_section_path($iInfo->information_id, 'information');
        for ($i = 0, $n = sizeof($sections); $i < $n; $i++) {
          $section_path = '';
          for ($j = 0, $k = sizeof($sections[$i]); $j < $k; $j++) {
            $section_path .= $sections[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $section_path = substr($section_path, 0, -16);
          $sections_string .= tep_draw_checkbox_field('sections[]', $sections[$i][sizeof($sections[$i])-1]['id'], true) . '&nbsp;' . $section_path . '<br>';
        }
        $sections_string = substr($sections_string, 0, -4);

        $contents[] = array('text' => '<br>' . $sections_string);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&iID=' . $iInfo->information_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if ($rows > 0) {
          if (isset($sInfo) && is_object($sInfo)) { // section info box contents
            $heading[] = array('text' => '<strong>' . $sInfo->sections_name . '</strong>');

            $contents[] = array('align' => 'center', 'text' => ((DEBUG_MODE=='off' && in_array('edit', $current_section_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sInfo->sections_id . '&action=edit_section') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> ') . ((DEBUG_MODE=='off' && in_array('edit', $current_section_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sInfo->sections_id . '&action=move_section') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> ') . ((DEBUG_MODE=='off' && in_array('delete', $current_section_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&sID=' . $sInfo->sections_id . '&action=delete_section') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'));
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added));
            if (tep_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified));
            $contents[] = array('text' => '<br>' . TEXT_SUBSECTIONS . ' ' . $sInfo->childs_count . '<br>' . TEXT_INFORMATIONS . ' ' . $sInfo->informations_count);
          } elseif (isset($iInfo) && is_object($iInfo)) { // information info box contents
            $heading[] = array('text' => '<strong>' . tep_get_information_info($iInfo->information_id, $languages_id) . '</strong>');

            $contents[] = array('align' => 'center', 'text' => ((DEBUG_MODE=='off' && in_array('edit', $current_information_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&iID=' . $iInfo->information_id . '&action=new_information') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> ') . ((DEBUG_MODE=='off' && in_array('delete', $current_information_disallow)) ? '' : '<a href="' . tep_href_link(FILENAME_INFORMATION, 'sPath=' . $sPath . '&iID=' . $iInfo->information_id . '&action=delete_information') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'));
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($iInfo->date_added));
            if (tep_not_null($iInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($iInfo->last_modified));
          }
        } else { // create section/information info
          $heading[] = array('text' => '<strong>' . EMPTY_SECTION . '</strong>');

          $contents[] = array('text' => TEXT_NO_CHILD_SECTIONS_OR_INFORMATIONS);
        }
        break;
    }

    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '            <td width="25%" valign="top">' . "\n";

      $box = new box;
      echo $box->infoBox($heading, $contents);

      echo '            </td>' . "\n";
    }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  }
?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>