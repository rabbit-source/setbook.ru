<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $type = (isset($HTTP_GET_VARS['type']) ? $HTTP_GET_VARS['type'] : 'static');
  $tPath = (isset($HTTP_GET_VARS['tPath']) ? (int)$HTTP_GET_VARS['tPath'] : 0);

  if (!tep_db_field_exists(TABLE_BLOCKS, 'blocks_status')) tep_db_query("alter table " . TABLE_BLOCKS . " add blocks_status smallint not null default '1'");

  if (!tep_db_field_exists(TABLE_BLOCKS_TYPES, 'blocks_types_multiple')) tep_db_query("alter table " . TABLE_BLOCKS_TYPES . " add blocks_types_multiple smallint not null default '1'");

  if (!in_array($type, array('dynamic', 'static'))) $type = 'static';

  if (DEBUG_MODE!='on') {
	$type = 'static';
	$allow_edit = true;
	$dissallowed_actions_1 = array('new_type', 'insert_type', 'edit_type', 'update_type', 'delete_type', 'delete_type_confirm');
	$dissallowed_actions_2 = array('new_block', 'insert_block', 'delete_block', 'delete_block_confirm');
	$dissallowed_actions_3 = array('edit_block', 'update_block');
	if (in_array($action, $dissallowed_actions_1)) {
	  $allow_edit = false;
	} elseif ($tPath==0 && in_array($action, $dissallowed_actions_2)) {
	  $allow_edit = false;
	} elseif ($tPath==0 && in_array($action, $dissallowed_actions_3)) {
	  if (isset($HTTP_GET_VARS['bID'])) {
		$block_info_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
		$block_info = tep_db_fetch_array($block_info_query);
		if (tep_not_null($block_info['blocks_filename'])) $allow_edit = false;
	  }
	}
	if ($allow_edit == false) {
	  $action = '';
	  tep_redirect(tep_href_link(FILENAME_BLOCKS, tep_get_all_get_params(array('action'))));
	}
  }

  function tep_get_blocks_types_info($types_id, $language_id, $field = 'blocks_types_name') {
    $types_query = tep_db_query("select " . $field . " from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$types_id . "' and language_id = '" . (int)$language_id . "'");
    $types = tep_db_fetch_array($types_query);

    return $types[$field];
  }

  function tep_get_blocks_info($blocks_id, $language_id, $field = 'blocks_name') {
    $blocks_query = tep_db_query("select " . $field . " from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$blocks_id . "' and language_id = '" . (int)$language_id . "'");
    $blocks = tep_db_fetch_array($blocks_query);

    return $blocks[$field];
  }

  $all_types = array('static' => TEXT_TYPES_STYLE_STATIC, 'dynamic' => TEXT_TYPES_STYLE_DYNAMIC, 'page' => TEXT_TYPES_STYLE_PAGES, 'information' => TEXT_TYPES_STYLE_INFORMATION);
  if (file_exists(FILENAME_CATEGORIES)) {
	$all_types['category'] = TEXT_TYPES_STYLE_CATEGORIES;
	$all_types['product'] = TEXT_TYPES_STYLE_PRODUCTS;
  }
  if (file_exists(FILENAME_MANUFACTURERS)) {
	$all_types['manufacturer'] = TEXT_TYPES_STYLE_MANUFACTURERS;
  }
  if (file_exists(FILENAME_NEWS)) {
	$all_types['news'] = TEXT_TYPES_STYLE_NEWS;
  }
  if (file_exists(FILENAME_SERIES)) {
	$all_types['series'] = TEXT_TYPES_STYLE_SERIES;
  }
  if (file_exists(FILENAME_SPECIALS)) {
	$all_types['specials'] = TEXT_TYPES_STYLE_SPECIALS;
  }

  if (tep_not_null($action)) {
    switch ($action) {
	  case 'setflag':
		if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
		  if (isset($HTTP_GET_VARS['bID'])) {
			tep_db_query("update " . TABLE_BLOCKS . " set blocks_status = '" . (int)$HTTP_GET_VARS['flag'] . "', last_modified = now() where blocks_id = '" . tep_db_input($HTTP_GET_VARS['bID']) . "'");
		  }
		}

		tep_redirect(tep_href_link(FILENAME_BLOCKS, tep_get_all_get_params(array('action', 'flag'))));
		break;
      case 'insert_type':
      case 'update_type':
		$error = false;
        if (isset($HTTP_POST_VARS['types_id'])) {
		  $types_id = tep_db_prepare_input($HTTP_POST_VARS['types_id']);
		} else {
		  $max_id_query = tep_db_query("select max(blocks_types_id) as new_id from " . TABLE_BLOCKS_TYPES . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $types_id = (int)$max_id['new_id'] + 1;
		}

		$sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
        if (isset($HTTP_POST_VARS['types_identificator'])) {
		  $types_identificator = tep_db_prepare_input($HTTP_POST_VARS['types_identificator']);
		  $types_identificator = preg_replace('/[^_\d\w]/', '_', strtolower($types_identificator));
		  $types_identificator = preg_replace('/_+/', '_', $types_identificator);
		}

		$disabled_names = array('warnings', 'banner');
		$type_exists_query = tep_db_query("select distinct blocks_types_identificator from " . TABLE_BLOCKS_TYPES . " where blocks_types_id <> '" . (int)$types_id . "'");
		while ($type_exists = tep_db_fetch_array($type_exists_query)) {
		  $disabled_names[] = $type_exists['blocks_types_identificator'];
		}
		$blocks_exists_query = tep_db_query("select distinct blocks_identificator from " . TABLE_BLOCKS . "");
		while ($blocks_exists = tep_db_fetch_array($blocks_exists_query)) {
		  if (tep_not_null($blocks_exists['blocks_identificator'])) $disabled_names[] = $blocks_exists['blocks_identificator'];
		}

		if ($types_identificator == '') {
		  $messageStack->add(ERROR_EMPTY_TYPE, 'error');
		  $error = true;
		} elseif ($action == 'insert_type' && in_array($types_identificator, $disabled_names)) {
		  $messageStack->add(sprintf(ERROR_BLOCK_EXIST, $types_identificator), 'error');
		  $error = true;
		}

		if (!$error) {
		  $old_field_query = tep_db_query("select blocks_types_field, blocks_types_field_html from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$types_id . "' limit 1");
		  $old_field = tep_db_fetch_array($old_field_query);
		  if ($old_field['blocks_types_field']=='textarea_text' && $HTTP_POST_VARS['types_field']!='textarea_text') {
			tep_db_query("update " . TABLE_BLOCKS . " set blocks_description_short = blocks_description where blocks_types_id = '" . (int)$types_id . "'");
			tep_db_query("update " . TABLE_BLOCKS . " set blocks_description = '' where blocks_types_id = '" . (int)$types_id . "'");
		  } elseif ($old_field['blocks_types_field']!='textarea_text' && $HTTP_POST_VARS['types_field']=='textarea_text') {
			tep_db_query("update " . TABLE_BLOCKS . " set blocks_description = blocks_description_short where blocks_types_id = '" . (int)$types_id . "'");
			tep_db_query("update " . TABLE_BLOCKS . " set blocks_description_short = '' where blocks_types_id = '" . (int)$types_id . "'");
		  }
		  if ($old_field['blocks_types_field_html'] != $HTTP_POST_VARS['types_field_html']) {
			$field_get = ($HTTP_POST_VARS['types_field']=='textarea_text') ? 'blocks_description' : 'blocks_description_short';
			$blocks_descriptions_query = tep_db_query("select " . tep_db_input($field_get) . " as description, blocks_id, language_id from " . TABLE_BLOCKS . " where blocks_types_id = '" . (int)$types_id . "'");
			while ($blocks_descriptions = tep_db_fetch_array($blocks_descriptions_query)) {
			  if ($HTTP_POST_VARS['types_field_html']=='1') {
				$new_description = nl2br(html_entity_decode(trim($blocks_descriptions['description'])));
			  } else {
				$new_description = str_replace("'", '&#039;', strip_tags(stripslashes(trim($blocks_descriptions['description']))));
			  }
			  tep_db_query("update " . TABLE_BLOCKS . " set " . tep_db_input($field_get) . " = '" . tep_db_input($new_description) . "' where blocks_id = '" . (int)$blocks_descriptions['blocks_id'] . "' and language_id = '" . (int)$blocks_descriptions['language_id'] . "'");
			}
		  }

		  $types_name_array = $HTTP_POST_VARS['types_name'];
		  $types_description_array = $HTTP_POST_VARS['types_description'];
		  $types_move = $HTTP_POST_VARS['types_move'];
		  if (!is_array($types_move)) $types_move = array();
		  $types_move_array = array();
		  reset($types_move);
		  while (list(, $type_move) = each($types_move)) {
			if ($type_move!=$types_id) $types_move_array[] = $type_move;
		  }
		  $sort_order = tep_db_prepare_input($sort_order);
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

			$sql_data_array = array('blocks_types_identificator' => tep_db_prepare_input($types_identificator),
		  							'blocks_types_style' => tep_db_prepare_input($type),
		  							'blocks_types_field' => tep_db_prepare_input($HTTP_POST_VARS['types_field']),
		  							'blocks_types_field_html' => tep_db_prepare_input($HTTP_POST_VARS['types_field_html']),
		  							'blocks_types_field_editor' => tep_db_prepare_input($HTTP_POST_VARS['types_field_editor']),
		  							'blocks_types_name' => tep_output_string_protected($types_name_array[$language_id]),
		  							'blocks_types_description' => tep_output_string_protected($types_description_array[$language_id]),
									'blocks_types_move' => implode(',', $types_move_array),
		  							'blocks_types_type' => tep_db_prepare_input($HTTP_POST_VARS['types_type']),
									'sort_order' => $sort_order);
			if ($type=='static') $sql_data_array['blocks_types_multiple'] = tep_db_prepare_input($HTTP_POST_VARS['types_multiple']);

			if ($action == 'insert_type') {
			  $insert_sql_data = array('blocks_types_id' => $types_id,
            						   'date_added' => 'now()',
									   'language_id' => $languages[$i]['id']);
			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

			  tep_db_perform(TABLE_BLOCKS_TYPES, $sql_data_array);
			} elseif ($action == 'update_type') {
			  $update_sql_data = array('last_modified' => 'now()');
			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);

			  tep_db_perform(TABLE_BLOCKS_TYPES, $sql_data_array, 'update', "blocks_types_id = '" . (int)$types_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			}
		  }

		  $templates = $HTTP_POST_VARS['templates'];
		  if (!is_array($templates)) $templates = array();
		  reset($templates);
		  tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$types_id . "'");
		  while (list($templates_id) = each($templates)) {
			tep_db_query("insert into " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " (blocks_types_id, templates_id) values ('" . (int)$types_id . "', '" . (int)$templates_id . "')");
		  }

		  tep_redirect(tep_href_link(FILENAME_BLOCKS, 'tID=' . $types_id . '&type=' . $type));
		} else {
		  $action = ($action=='insert_type' ? 'new_type' : 'edit_type');
		}
		break;
      case 'delete_type_confirm':
        if (isset($HTTP_POST_VARS['types_id'])) {
          $types_id = tep_db_prepare_input($HTTP_POST_VARS['types_id']);

		  $blocks_query = tep_db_query("select blocks_id, blocks_filename from " . TABLE_BLOCKS . " where blocks_types_id = '" . (int)$types_id . "'");
		  while ($blocks = tep_db_fetch_array($blocks_query)) {
			tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS . " where blocks_id = '" . (int)$blocks['blocks_id'] . "'");
			if (tep_not_null($blocks['blocks_filename']) && file_exists(DIR_FS_CATALOG_BLOCKS . basename($blocks['blocks_filename']))) {
			  @unlink(DIR_FS_CATALOG_BLOCKS . basename($blocks['blocks_filename']));
			}
		  }

		  tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_types_id = '" . (int)$types_id . "'");
		  tep_db_query("delete from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$types_id . "'");
		  tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$types_id . "'");
		}

        tep_redirect(tep_href_link(FILENAME_BLOCKS, 'type=' . $type));
        break;
      case 'delete_block_confirm':
        if (isset($HTTP_POST_VARS['blocks_id'])) {
          $blocks_id = tep_db_prepare_input($HTTP_POST_VARS['blocks_id']);
		  $block_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '" . $blocks_id . "' limit 1");
		  $block = tep_db_fetch_array($block_query);
		  if (tep_not_null($block['blocks_filename']) && file_exists(DIR_FS_CATALOG_BLOCKS . basename($block['blocks_filename']))) {
			@unlink(DIR_FS_CATALOG_BLOCKS . basename($block['blocks_filename']));
		  }
		  $shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
		  while ($shops = tep_db_fetch_array($shops_query)) {
			tep_db_select_db($shops['shops_database']);
			tep_db_query("delete from " . TABLE_BLOCKS . " where blocks_id = '" . $blocks_id . "'");
			tep_db_query("delete from " . TABLE_TEMPLATES_TO_BLOCKS . " where blocks_id = '" . (int)$blocks_id . "'");
		  }
		  tep_db_select_db(DB_DATABASE);
        }

        tep_redirect(tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&type=' . $type));
        break;
      case 'insert_block':
      case 'update_block':
        $error = false;
        if (isset($HTTP_GET_VARS['bID'])) {
		  $blocks_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);
		} else {
		  $max_id_query = tep_db_query("select max(blocks_id) as new_id from " . TABLE_BLOCKS . "");
		  $max_id = tep_db_fetch_array($max_id_query);
		  $blocks_id = (int)$max_id['new_id'] + 1;
		}

		$blocks_identificator = '';
		$blocks_types_id = (isset($HTTP_POST_VARS['blocks_types_id']) && tep_not_null($HTTP_POST_VARS['blocks_types_id'])) ? $HTTP_POST_VARS['blocks_types_id'] : $tPath;
		$sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);
        if (isset($HTTP_POST_VARS['blocks_identificator'])) {
		  $blocks_identificator = tep_db_prepare_input($HTTP_POST_VARS['blocks_identificator']);
		  $blocks_identificator = preg_replace('/[^_\d\w]/', '_', strtolower($blocks_identificator));
		  $blocks_identificator = preg_replace('/_+/', '_', $blocks_identificator);
		}

		$disabled_names = array('warnings', 'banner');
		$type_exists_query = tep_db_query("select distinct blocks_types_identificator from " . TABLE_BLOCKS_TYPES . "");
		while ($type_exists = tep_db_fetch_array($type_exists_query)) {
		  $disabled_names[] = $type_exists['blocks_types_identificator'];
		}
		$blocks_exists_query = tep_db_query("select distinct blocks_identificator from " . TABLE_BLOCKS . " where blocks_id <> '" . (int)$blocks_id . "'");
		while ($blocks_exists = tep_db_fetch_array($blocks_exists_query)) {
		  if (tep_not_null($blocks_exists['blocks_identificator'])) $disabled_names[] = $blocks_exists['blocks_identificator'];
		}

		if ($type == 'dynamic' && (int)$blocks_types_id < 1) {
		  $messageStack->add_session(ERROR_EMPTY_BLOCK_TYPE, 'error');
		  $error = true;
		} elseif ($type == 'static' && $tPath == '0' && empty($blocks_identificator)) {
		  $messageStack->add(ERROR_EMPTY_TYPE, 'error');
		  $error = true;
		} elseif ($type == 'static' && $tPath == '0' && in_array($blocks_identificator, $disabled_names)) {
		  $messageStack->add(sprintf(ERROR_BLOCK_EXIST, $blocks_identificator), 'error');
		  $error = true;
		}

		if (!$error) {
		  $type_query = tep_db_query("select blocks_types_name, blocks_types_field, blocks_types_field_html from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$tPath . "' limit 1");
		  $type_array = tep_db_fetch_array($type_query);

		  $blocks_name_array = $HTTP_POST_VARS['blocks_name'];
		  $blocks_description_array = $HTTP_POST_VARS['blocks_description'];
		  $blocks_type = tep_db_prepare_input($HTTP_POST_VARS['blocks_type']);
		  $languages = tep_get_languages();
		  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$language_id = $languages[$i]['id'];

			if ($type_array['blocks_types_field_html']=='0' && $type_array['blocks_types_field']!='input') {
			  $description = htmlspecialchars(strip_tags(stripslashes(trim($blocks_description_array[$language_id]))), ENT_COMPAT);
			} else {
			  $description = $blocks_description_array[$language_id];
			}
			$description = $description;
			$block_field = ($type_array['blocks_types_field']=='textarea_text' || ($type=='static' && $tPath=='0')) ? 'blocks_description' : 'blocks_description_short';

			$sql_data_array = array('blocks_id' => $blocks_id,
									'blocks_name' => tep_output_string_protected($blocks_name_array[$language_id]),
		  							$block_field => $description,
									'blocks_types_id' => tep_db_prepare_input($blocks_types_id),
									'blocks_style' => tep_db_prepare_input($type),
									'blocks_type' => $blocks_type,
            						'sort_order' => $sort_order,
									'language_id' => $languages[$i]['id'],
									'blocks_identificator' => $blocks_identificator);

			if ($action == 'insert_block') {
			  $insert_sql_data = array('date_added' => 'now()');
			  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
			} elseif ($action == 'update_block') {
			  $update_sql_data = array('last_modified' => 'now()');
			  $sql_data_array = array_merge($sql_data_array, $update_sql_data);
			}

			$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''" . ($action=='update_block' ? " and shops_id = '" . (int)SHOP_ID . "'" : ""));
			while ($shops = tep_db_fetch_array($shops_query)) {
			  tep_db_select_db($shops['shops_database']);
			  if ($action == 'insert_block') {
				tep_db_perform(TABLE_BLOCKS, $sql_data_array);
			  } elseif ($action == 'update_block') {
				tep_db_perform(TABLE_BLOCKS, $sql_data_array, 'update', "blocks_id = '" . (int)$blocks_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
			  }
			}
			tep_db_select_db(DB_DATABASE);
		  }

		  $sql = "";
		  if (DEBUG_MODE=='on') {
			if ($upload = new upload('', '', '777', array('php'))) {
			  $upload->filename = 'block_' . $blocks_id . '.php';
        	  if ($upload->upload('blocks_filename', DIR_FS_CATALOG_BLOCKS)) {
				$prev_file_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$blocks_id . "'");
				$prev_file = tep_db_fetch_array($prev_file_query);
				if (tep_not_null($prev_file['blocks_filename']) && $prev_file['blocks_filename']!=$upload->filename) {
				  @unlink(DIR_FS_CATALOG_BLOCKS . $prev_file['blocks_filename']);
				}
				$sql .= "update " . TABLE_BLOCKS . " set blocks_filename = '" . tep_db_input($upload->filename) . "' where blocks_id = '" . (int)$blocks_id . "';\n";
			  } elseif (isset($HTTP_POST_VARS['blocks_filename_contents'])) {
				$blocks_filename_contents = stripslashes($HTTP_POST_VARS['blocks_filename_contents']);
				$prev_file_query = tep_db_query("select blocks_filename from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$blocks_id . "'");
				$prev_file = tep_db_fetch_array($prev_file_query);
				if (tep_not_null($prev_file['blocks_filename'])) {
				  @unlink(DIR_FS_CATALOG_BLOCKS . $prev_file['blocks_filename']);
				} else {
				  $prev_file['blocks_filename'] = 'block_' . $blocks_id . '.php';
				}
				if (tep_not_null($blocks_filename_contents)) {
				  if ($HTTP_POST_VARS['update_block_contents']=='1') {
					$fp = fopen(DIR_FS_CATALOG_BLOCKS . $prev_file['blocks_filename'], 'w');
					fwrite($fp, $blocks_filename_contents);
					fclose($fp);
					$sql .= "update " . TABLE_BLOCKS . " set blocks_filename = '" . tep_db_input($prev_file['blocks_filename']) . "' where blocks_id = '" . (int)$blocks_id . "';\n";
				  }
				} else {
				  $sql .= "update " . TABLE_BLOCKS . " set blocks_filename = '' where blocks_id = '" . (int)$blocks_id . "';\n";
				  if (tep_not_null($prev_file['blocks_filename'])) @unlink(DIR_FS_CATALOG_BLOCKS . $prev_file['blocks_filename']);
				}
			  }
			}
			if ((int)$blocks_types_id == 0) {
			  $templates = $HTTP_POST_VARS['templates'];
			  if (!is_array($templates)) $templates = array();
			  $sql .= "delete from " . TABLE_TEMPLATES_TO_BLOCKS . " where blocks_id = '" . (int)$blocks_id . "';\n";
			  reset($templates);
			  while (list(, $template_id) = each($templates)) {
				$sql .= "insert into " . TABLE_TEMPLATES_TO_BLOCKS . " (blocks_id, templates_id) values ('" . (int)$blocks_id . "', '" . (int)$template_id . "');\n";
			  }
			}
		  }

		  if ($type=='static') {
			$sql .= "update " . TABLE_BLOCKS . " set blocks_default_status = '" . tep_db_input($HTTP_POST_VARS['blocks_default_status']) . "', blocks_status = '" . tep_db_input($HTTP_POST_VARS['blocks_status']) . "' where blocks_id = '" . (int)$blocks_id . "';\n";
		  }

		  if (tep_not_null($sql)) {
			$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''" . ($action=='update_block' ? " and shops_id = '" . (int)SHOP_ID . "'" : ""));
			while ($shops = tep_db_fetch_array($shops_query)) {
			  tep_db_select_db($shops['shops_database']);
			  $queries = explode(";\n", $sql);
			  reset($queries);
			  while (list(, $query) = each($queries)) {
				if (tep_not_null(trim($query))) tep_db_query($query);
			  }
			}
			tep_db_select_db(DB_DATABASE);
		  }

		  if ($action=='update_block') {
			$shops_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''");
			while ($shops = tep_db_fetch_array($shops_query)) {
			  tep_db_select_db($shops['shops_database']);
//			  tep_db_query("update " . TABLE_BLOCKS . " set sort_order = '" . tep_db_input($sort_order) . "' where blocks_id = '" . $blocks_id . "'");
			}
			tep_db_select_db(DB_DATABASE);
		  }

		  tep_redirect(tep_href_link(FILENAME_BLOCKS, 'tPath=' . $blocks_types_id . '&bID=' . $blocks_id . '&type=' . $type));
		} else {
		  $action = ($action=='insert_block' ? 'new_block' : 'edit_block');
		}
		break;
    }
  }

  $types_fields = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $types_fields[] = array('id' => 'input', 'text' => TEXT_FIELD_INPUT);
  $types_fields[] = array('id' => 'textarea_varchar', 'text' => TEXT_FIELD_TEXTAREA_VARCHAR);
  $types_fields[] = array('id' => 'textarea_text', 'text' => TEXT_FIELD_TEXTAREA_TEXT);

  $move_array = array();
  $move_query = tep_db_query("select blocks_types_id, blocks_types_name from " . TABLE_BLOCKS_TYPES . " where blocks_types_style = 'static' and language_id = '" . (int)$languages_id . "' and blocks_types_id <> '" . (int)$HTTP_GET_VARS['tID'] . "' order by sort_order, blocks_types_name");
  while ($move = tep_db_fetch_array($move_query)) {
	$move_array[] = array('id' => $move['blocks_types_id'], 'text' => $move['blocks_types_name']);
  }

  $types_styles = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  $types_types = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
  reset($all_types);
  while (list($k, $v) = each($all_types)) {
	if ($k=='static' || $k=='dynamic') {
	  $types_styles[] = array('id' => $k, 'text' => $v);
	}
	if ($k!='static' && $k!='dynamic') {
	  $types_types[] = array('id' => $k, 'text' => $v);
	}
  }

  if (!is_dir(DIR_FS_CATALOG_BLOCKS)) {
	$messageStack->add(WARNING_INCLUDES_BLOCKS_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_CATALOG_BLOCKS)) {
	$messageStack->add(WARNING_INCLUDES_BLOCKS_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
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
  if ($action == 'new_block' || $action == 'edit_block') {
    $parameters = array();
	$query = tep_db_query("describe " . TABLE_BLOCKS);
	while ($row = tep_db_fetch_array($query)) {
	  $parameters[$row['Field']] == (tep_not_null($row['Default']) ? $row['Default'] : '');
	}

    $bInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['bID']) && empty($HTTP_POST_VARS)) {
	  if ($tPath == '0') {
		$block_query = tep_db_query("select * from " . TABLE_BLOCKS . " where blocks_id = '" . (int)$HTTP_GET_VARS['bID'] . "' and language_id = '" . (int)$languages_id . "'");
	  } else {
		$block_query = tep_db_query("select b.*, if (bt.blocks_types_field='textarea_text', b.blocks_description, b.blocks_description_short) as blocks_description from " . TABLE_BLOCKS . " b, " . TABLE_BLOCKS_TYPES . " bt where b.blocks_id = '" . (int)$HTTP_GET_VARS['bID'] . "' and b.blocks_types_id = bt.blocks_types_id and b.language_id = bt.language_id and b.language_id = '" . (int)$languages_id . "'");
	  }
      $block = tep_db_fetch_array($block_query);
	  $block['templates'] = array();
	  $templates = array();
	  $templates_query = tep_db_query("select distinct templates_id from " . TABLE_TEMPLATES_TO_BLOCKS . " where blocks_id = '" . (int)$block['blocks_id'] . "'");
	  while ($templates_array = tep_db_fetch_array($templates_query)) {
		$block['templates'][] = $templates_array['templates_id'];
	  }

      $bInfo->objectInfo($block);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $bInfo->objectInfo($HTTP_POST_VARS);
      $blocks_name = array_map("stripslashes", $HTTP_POST_VARS['blocks_name']);
      $blocks_description = array_map("stripslashes", $HTTP_POST_VARS['blocks_description']);
    }
	if (!is_array($bInfo->templates)) $bInfo->templates = array();

    $languages = tep_get_languages();

	$form_action = (isset($HTTP_GET_VARS['bID'])) ? 'update_block' : 'insert_block';

	echo tep_draw_form('blocks', FILENAME_BLOCKS, 'type=' . $type . '&tPath=' . $tPath . (isset($HTTP_GET_VARS['bID']) ? '&bID=' . $HTTP_GET_VARS['bID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo (isset($HTTP_GET_VARS['bID'])) ? TEXT_INFO_HEADING_EDIT_BLOCK : TEXT_INFO_HEADING_NEW_BLOCK; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
<?php
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="1">
<?php
	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" width="250"><?php if ($i == 0) echo TEXT_BLOCKS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('blocks_name[' . $languages[$i]['id'] . ']', (isset($blocks_name[$languages[$i]['id']]) ? $blocks_name[$languages[$i]['id']] : tep_get_blocks_info($bInfo->blocks_id, $languages[$i]['id'])), 'size="35"'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
	if (DEBUG_MODE!='on' && tep_not_null($bInfo->blocks_filename) && file_exists(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename))) {
?>
	  <tr>
		<td colspan="2"" class="main"><?php echo TEXT_BLOCK_NOT_EDITABLE; ?></td>
	  </tr>
<?php
	} else {
	  $types_query = tep_db_query("select blocks_types_id, blocks_types_name, blocks_types_description, blocks_types_identificator, blocks_types_field, blocks_types_field_html, blocks_types_field_editor from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$tPath . "' limit 1");
	  $types = tep_db_fetch_array($types_query);
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php
		if ($i == 0) {
		  echo TEXT_BLOCKS_DESCRIPTION;
		  if (tep_not_null($types['blocks_types_description'])) echo '<br /><span class="smallText">' . $types['blocks_types_description'] . '</span>';
		  if ( ($types['blocks_types_field_html']=='0' && $types['blocks_types_field']!='input') || ($types['blocks_types_field']=='textarea_varchar') ) {
			echo '<br /><span class="smallText"><strong>';
			if ($types['blocks_types_field_html']=='0' && $types['blocks_types_field']!='input') echo TEXT_NO_HTML . '<br>';
			if ($types['blocks_types_field']=='textarea_varchar') echo TEXT_MAX_255;
			echo '</strong></span>';
		  }
		}
?></td>
            <td class="main"><?php
		echo tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name'], '', '', 'style="float: left; margin: 4px 4px 0px 0px;"');
		$field_name = 'blocks_description[' . $languages[$i]['id'] . ']';
		$field_value = stripslashes(isset($blocks_description[$languages[$i]['id']]) ? $blocks_description[$languages[$i]['id']] : tep_get_blocks_info($bInfo->blocks_id, $languages[$i]['id'], (($types['blocks_types_field']=='textarea_text' || $tPath=='0') ? 'blocks_description' : 'blocks_description_short')));
		if ($tPath == '0') {
		  $editor = new editor($field_name);
		  $editor->Value = $field_value;
		  $editor->Height = '200';
		  $editor->Create();
		} elseif ($types['blocks_types_field']=='textarea_varchar') {
		  echo tep_draw_textarea_field($field_name, 'soft', '55', '5', $field_value);
		} elseif ($types['blocks_types_field']=='textarea_text') {
		  if ($types['blocks_types_field_html']=='1' && $types['blocks_types_field_editor']=='1') {
			$editor = new editor($field_name);
			$editor->Value = $field_value;
			$editor->Height = '280';
			$editor->Create();
		  } else {
			echo tep_draw_textarea_field($field_name, 'soft', '90%', '12', $field_value);
		  }
		} else {
		  echo tep_draw_input_field($field_name, $field_value, 'size="35"');
		}
?></td>
          </tr>
<?php
	  }
	  if (DEBUG_MODE=='on' && $type=='static') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BLOCKS_FILENAME; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_file_field('blocks_filename') . (tep_not_null($bInfo->blocks_filename) ? '<br>' . tep_draw_separator('pixel_trans.gif', '18', '1') . '&nbsp;<small>' . (!file_exists(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename)) ? TEXT_FILE_NOT_FOUND . ' ' : '') . DIR_WS_CATALOG_BLOCKS . '<strong>' . basename($bInfo->blocks_filename) . '</strong></small>' : ''); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BLOCKS_FILENAME_CONTENT; ?></td>
            <td class="main"><?php
		  if (file_exists(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename)) && !is_writeable(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename))) {
			echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;<strong>' . ERROR_FILE_NO_WRITEABLE . '</strong>';
		  } else {
			echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;<textarea wrap="off" name="blocks_filename_contents" cols="100%" rows="20">' . htmlspecialchars((tep_not_null($bInfo->blocks_filename) && file_exists(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename))) ? implode('', file(DIR_FS_CATALOG_BLOCKS . basename($bInfo->blocks_filename))) : '') . '</textarea>' . ($action=='new_block' ? tep_draw_hidden_field('update_block_contents', '1') : '<br />' . tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('update_block_contents', '1', true) . TEXT_BLOCKS_FILENAME_CONTENT_REWRITE);
		  }
?></td>
          </tr>
<?php
		$available_types_array = array(array('id' => '', 'text' => TEXT_DEFAULT_SELECT));
		$move_types_query = tep_db_query("select blocks_types_move from " . TABLE_BLOCKS_TYPES . " where blocks_types_id = '" . (int)$tPath . "' limit 1");
		$move_types = tep_db_fetch_array($move_types_query);
		if (tep_not_null($move_types['blocks_types_move'])) {
		  $available_types_query = tep_db_query("select blocks_types_id, blocks_types_name from " . TABLE_BLOCKS_TYPES . " where blocks_types_id in ('" . implode("', '", array_map('tep_string_to_int', explode(',', $move_types['blocks_types_move']))) . "') and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_types_name");
		  while ($available_types = tep_db_fetch_array($available_types_query)) {
			$available_types_array[] = array('id' => $available_types['blocks_types_id'], 'text' => $available_types['blocks_types_name']);
		  }
		}
		if ((int)$tPath == 0) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
			<td class="main"><?php echo TEXT_BLOCKS_TEMPLATES; ?></td>
            <td class="main"><table border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;'; ?></td>
				<td class="main"><?php
		$i = 0;
		$templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by sort_order, default_status desc, templates_id");
		while ($templates_array = tep_db_fetch_array($templates_query)) {
		  echo ($i>0 ? '<br />' : '') . tep_draw_checkbox_field('templates[' . $i . ']', $templates_array['templates_id'], in_array($templates_array['templates_id'], $bInfo->templates)) . $templates_array['templates_name'];
		  $i ++;
		}
?></td>
			  </tr>
			</table></td>
          </tr>
<?php
		}
	  }
	  if (sizeof($available_types_array) > 1) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
			<td class="main"><?php echo TEXT_BLOCKS_MOVE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('blocks_types_id', $available_types_array); ?></td>
          </tr>
<?php
	  }
	}
	if ($type=='static') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BLOCKS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('blocks_status', '1', $bInfo->blocks_status=='1'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BLOCKS_DEFAULT_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('blocks_default_status', '1', $bInfo->blocks_default_status=='1', '', 'onclick="if (this.checked) blocks_remove.checked = false;"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BLOCKS_REMOVE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_checkbox_field('blocks_remove', '1', false, '', 'onclick="if (this.checked) blocks_default_status.checked = false;"'); ?></td>
          </tr>
<?php
	}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('sort_order', $bInfo->sort_order, 'size="3"'); ?></td>
          </tr>
<?php
//	if ($tPath=='0' && DEBUG_MODE=='on') {
	if (DEBUG_MODE=='on') {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BLOCKS_IDENTIFICATOR; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_input_field('blocks_identificator', $bInfo->blocks_identificator); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" width="250"><?php echo TEXT_BLOCKS_TYPE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '18', '12') . '&nbsp;' . tep_draw_pull_down_menu('blocks_type', $types_types, $bInfo->blocks_type); ?></td>
          </tr>
<?php
	} elseif ($bInfo->blocks_id > 0) {
	  echo tep_draw_hidden_field('blocks_identificator', $bInfo->blocks_identificator);
	}
?>
          <tr>
            <td width="250"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo tep_draw_hidden_field('date_added', (tep_not_null($bInfo->date_added) ? $bInfo->date_added : date('Y-m-d'))) . tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BLOCKS, 'type=' . $type . '&tPath=' . $tPath . (isset($HTTP_GET_VARS['bID']) ? '&bID=' . $HTTP_GET_VARS['bID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
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
<?php
	if (DEBUG_MODE=='on') {
?>
            <td class="pageHeading" align="right"><table border="0" cellspacing="0" cellpadding="1">
			  <tr>
				<?php
	  echo tep_draw_form('goto', FILENAME_BLOCKS, '', 'get');
	  reset($HTTP_GET_VARS);
	  while (list($k, $v) = each($HTTP_GET_VARS)) {
		if (!in_array($k, array(tep_session_name(), 'tPath', 'tID', 'bID', 'type'))) echo tep_draw_hidden_field($k, $v);
	  }
?>
				<td class="smallText"><?php echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('type', $types_styles, $type, 'onChange="this.form.submit();"');
?></td></form>
			  </tr>
			</table></td>
<?php
	}
?>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TYPES_BLOCKS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
	if ($tPath == 0) {
	  $rows = 0;
	  $types_query = tep_db_query("select * from " . TABLE_BLOCKS_TYPES . " where blocks_types_style = '" . tep_db_prepare_input($type) . "' and language_id = '" . (int)$languages_id . "' order by sort_order, blocks_types_name");
	  while ($types = tep_db_fetch_array($types_query)) {
		$types_count++;
		$rows++;

		if ((!isset($HTTP_GET_VARS['tID']) && !isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $types['blocks_types_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
		  $type_blocks_query = tep_db_query("select count(*) as blocks_count from " . TABLE_BLOCKS . " where blocks_types_id = '" . $types['blocks_types_id'] . "'");
		  $type_blocks = tep_db_fetch_array($type_blocks_query);

		  $types['templates'] = array();
		  $types_templates_query = tep_db_query("select templates_id from " . TABLE_TEMPLATES_TO_BLOCKS_TYPES . " where blocks_types_id = '" . $types['blocks_types_id'] . "'");
		  while ($types_templates_array = tep_db_fetch_array($types_templates_query)) {
			$types['templates'][$types_templates_array['templates_id']] = '1';
		  }

		  $tInfo_array = array_merge($types, $type_blocks);
		  $tInfo = new objectInfo($tInfo_array);
		}

		if (isset($tInfo) && is_object($tInfo) && ($types['blocks_types_id'] == $tInfo->blocks_types_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $types['blocks_types_id'] . '&type=' . $type) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $types['blocks_types_id'] . '&type=' . $type) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent" colspan="2"><?php echo ($type=='static' ? '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $types['blocks_types_id'] . '&type=' . $type) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '16', '16', 'style="margin: 2px 0 -2px 0;"') . '</a>&nbsp;' : '') . '[' . $types['sort_order'] . ']&nbsp;' . '<strong>' . $types['blocks_types_name'] . '</strong>' . (DEBUG_MODE=='on' ? ' [' . $types['blocks_types_identificator'] . ']' : ''); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tInfo) && is_object($tInfo) && ($types['blocks_types_id'] == $tInfo->blocks_types_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $types['blocks_types_id'] . '&type=' . $type) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
	}

	if ( ($type=='static' && $tPath=='0') || $tPath > 0) {
	  $blocks_count = 0;
	  $blocks_query = tep_db_query("select * from " . TABLE_BLOCKS . " where blocks_types_id = '" . (int)$tPath . "' and language_id = '" . (int)$languages_id . "'" . ((DEBUG_MODE=='off' && (int)$tPath==0) ? " and blocks_filename = ''" : "") . " order by sort_order, blocks_name");
	  while ($blocks = tep_db_fetch_array($blocks_query)) {
		$blocks_count++;
		$rows++;

    	if ( (!isset($HTTP_GET_VARS['bID']) && !isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $blocks['blocks_id']))) && !isset($bInfo) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
		  $bInfo_array = $blocks;
		  $bInfo = new objectInfo($bInfo_array);
		}

		if (isset($bInfo) && is_object($bInfo) && ($blocks['blocks_id'] == $bInfo->blocks_id) ) {
		  echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&type=' . $type . '&bID=' . $blocks['blocks_id']) . '\'">' . "\n";
		} else {
		  echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&type=' . $type . '&bID=' . $blocks['blocks_id']) . '\'">' . "\n";
		}
?>
                <td class="dataTableContent"><?php echo '[' . $blocks['sort_order'] . ']&nbsp;' . $blocks['blocks_name'] . ((DEBUG_MODE=='on' && tep_not_null($blocks['blocks_identificator'])) ? ' [' . $blocks['blocks_identificator'] . ']' : ''); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($blocks['blocks_status']=='1' ? tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BLOCKS, tep_get_all_get_params(array('action', 'flag', 'bID')) . 'action=setflag&flag=0&bID=' . $blocks['blocks_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>' : '<a href="' . tep_href_link(FILENAME_BLOCKS, tep_get_all_get_params(array('action', 'flag', 'bID')) . 'action=setflag&flag=1&bID=' . $blocks['blocks_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10)); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($bInfo) && is_object($bInfo) && ($blocks['blocks_id'] == $bInfo->blocks_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&tID=' . $tID . '&bID=' . $blocks['blocks_id'] . '&type=' . $type) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
	  }
	}
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td valign="top" class="smallText"><?php if ($tPath == 0) echo TEXT_TYPES . '&nbsp;' . $types_count . '<br>'; echo TEXT_BLOCKS . '&nbsp;' . $blocks_count; ?></td>
                    <td align="right" class="smallText"><?php if ($tPath > 0) echo '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $tPath . '&type=' . $type) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;'; if ($tPath == 0 && DEBUG_MODE=='on') echo '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&type=' . $type . '&action=new_type') . '">' . tep_image_button('button_new_type.gif', IMAGE_NEW_TYPE) . '</a>&nbsp;'; if ($tPath > 0 || (DEBUG_MODE == 'on' && $tPath == 0 && $type == 'static')) echo '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&type=' . $type . '&action=new_block') . '">' . tep_image_button('button_new_block.gif', IMAGE_NEW_BLOCK) . '</a>&nbsp;'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
	$heading = array();
	$contents = array();
	switch ($action) {
	  case 'new_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('newtype', FILENAME_BLOCKS, 'action=insert_type&type=' . $type, 'post'));
		$contents[] = array('text' => TEXT_NEW_TYPE_INTRO);

		$contents[] = array('text' => '<br>' . TEXT_TYPES_IDENTIFICATOR . '<br>' . tep_draw_input_field('types_identificator', $HTTP_POST_VARS['types_identificator'], 'size="20"'));

		$types_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('types_name[' . $languages[$i]['id'] . ']', $HTTP_POST_VARS['types_name'][$languages[$i]['id']], 'size="32"');
		}
		$contents[] = array('text' => '<br>' . TEXT_TYPES_NAME . $types_inputs_string);

		$types_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('types_description[' . $languages[$i]['id'] . ']', 'soft', '31', '3', $HTTP_POST_VARS['types_description'][$languages[$i]['id']]);
		}
		$contents[] = array('text' => '<br>' . TEXT_TYPES_DESCRIPTION . $types_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $HTTP_POST_VARS['sort_order'], 'size="3"'));

		$contents[] = array('text' => '<br>' . TEXT_TYPES_FIELD . '<br>' . tep_draw_pull_down_menu('types_field', $types_fields) . '<br>' . tep_draw_checkbox_field('types_field_html', '1', false) . TEXT_TYPES_FIELD_HTML. '<br>' . tep_draw_checkbox_field('types_field_editor', '1', false) . TEXT_TYPES_FIELD_EDITOR);

		if ($type=='static') {
		  $contents[] = array('text' => '<br>' . TEXT_TYPES_MOVE . '<br>' . tep_draw_pull_down_menu('types_move[]', $move_array, '', 'multiple="true" size="5"'));

		  $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('types_multiple', '1', true) . TEXT_TYPES_MULTIPLE);
		}

		$contents[] = array('text' => '<br>' . TEXT_TYPES_TYPE . '<br>' . tep_draw_pull_down_menu('types_type', $types_types));

		$contents[] = array('text' => '<br>' . TEXT_ALLOW_TEMPLATES);
		$templates_string = '';
		$templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by sort_order, default_status desc, templates_id");
		while ($templates_array = tep_db_fetch_array($templates_query)) {
		  $templates_string .= tep_draw_checkbox_field('templates[' . $templates_array['templates_id'] . ']', '1', false) . $templates_array['templates_name'] . '<br>' . "\n";
		}
		$contents[] = array('text' => $templates_string);

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_BLOCKS, 'type=' . $type) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'edit_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('types', FILENAME_BLOCKS, 'action=update_type&type=' . $type, 'post') . tep_draw_hidden_field('types_id', $tInfo->blocks_types_id));
		$contents[] = array('text' => TEXT_EDIT_INTRO);

		$contents[] = array('text' => '<br>' . TEXT_TYPES_IDENTIFICATOR . '<br>' . tep_draw_input_field('types_identificator', (isset($types_identificator) ? $types_identificator : $tInfo->blocks_types_identificator), 'size="20"'));

		$types_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('types_name[' . $languages[$i]['id'] . ']', (isset($types_name[$languages[$i]['id']]) ? $types_name[$languages[$i]['id']] : tep_get_blocks_types_info($tInfo->blocks_types_id, $languages[$i]['id'], 'blocks_types_name')), 'size="32"');
		}
		$contents[] = array('text' => '<br>' . TEXT_TYPES_NAME . $types_inputs_string);

		$types_inputs_string = '';
		$languages = tep_get_languages();
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
		  $types_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('types_description[' . $languages[$i]['id'] . ']', 'soft', '31', '3', (isset($types_description[$languages[$i]['id']]) ? $types_description[$languages[$i]['id']] : tep_get_blocks_types_info($tInfo->blocks_types_id, $languages[$i]['id'], 'blocks_types_description')));
		}
		$contents[] = array('text' => '<br>' . TEXT_TYPES_DESCRIPTION . $types_inputs_string);

		$contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', (isset($sort_order) ? $sort_order : $tInfo->sort_order), 'size="3"'));

		$contents[] = array('text' => '<br>' . TEXT_TYPES_FIELD . '<br>' . tep_draw_pull_down_menu('types_field', $types_fields, (isset($types_field) ? $types_field : $tInfo->blocks_types_field)) . '<br>' . tep_draw_checkbox_field('types_field_html', '1', $tInfo->blocks_types_field_html) . TEXT_TYPES_FIELD_HTML . '<br>' . tep_draw_checkbox_field('types_field_editor', '1', $tInfo->blocks_types_field_editor) . TEXT_TYPES_FIELD_EDITOR);

		if ($type=='static') {
		  $contents[] = array('text' => '<br>' . TEXT_TYPES_MOVE . '<br>' . tep_draw_pull_down_menu('types_move[]', $move_array, explode(',', $tInfo->blocks_types_move), 'multiple="true" size="5"'));

		  $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('types_multiple', '1', $tInfo->blocks_types_multiple=='1') . TEXT_TYPES_MULTIPLE);
		}

		$contents[] = array('text' => '<br>' . TEXT_TYPES_TYPE . '<br>' . tep_draw_pull_down_menu('types_type', $types_types, $tInfo->blocks_types_type));

		$contents[] = array('text' => '<br>' . TEXT_ALLOW_TEMPLATES);
		$templates_string = '';
		$templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by sort_order, default_status desc, templates_id");
		while ($templates_array = tep_db_fetch_array($templates_query)) {
		  $templates_string .= tep_draw_checkbox_field('templates[' . $templates_array['templates_id'] . ']', '1', in_array($templates_array['templates_id'], array_keys($tInfo->templates))) . $templates_array['templates_name'] . '<br>' . "\n";
		}
		$contents[] = array('text' => $templates_string);

		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $tInfo->blocks_types_id . '&type=' . $type) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'delete_type':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TYPE . '</strong>');

		$contents = array('form' => tep_draw_form('types', FILENAME_BLOCKS, 'action=delete_type_confirm&type=' . $type) . tep_draw_hidden_field('types_id', $tInfo->blocks_types_id));
		$contents[] = array('text' => TEXT_DELETE_TYPE_INTRO);
		$contents[] = array('text' => '<br><strong>' . $tInfo->blocks_types_name . '</strong>');
		if ($tInfo->blocks_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_BLOCKS, $tInfo->blocks_count));
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $tID . '&type=' . $type) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  case 'delete_block':
		$heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_BLOCK . '</strong>');

		$contents = array('form' => tep_draw_form('blocks', FILENAME_BLOCKS, 'action=delete_block_confirm&tPath=' . $tPath . '&type=' . $type) . tep_draw_hidden_field('blocks_id', $bInfo->blocks_id));
		$contents[] = array('text' => TEXT_DELETE_BLOCK_INTRO);
		$contents[] = array('text' => '<br><strong>' . $bInfo->blocks_name . '</strong>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&bID=' . $bInfo->blocks_id . '&type=' . $type) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	  default:
		if ($rows > 0) {
		  if (isset($tInfo) && is_object($tInfo)) { // type info box contents
			$heading[] = array('text' => '<strong>' . $tInfo->blocks_types_name . '</strong>');

			if (DEBUG_MODE=='on') {
			  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $tInfo->blocks_types_id . '&type=' . $type . '&action=edit_type') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_BLOCKS, 'tID=' . $tInfo->blocks_types_id . '&type=' . $type . '&action=delete_type') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

			  if ($type=='static' && tep_not_null($tInfo->blocks_types_type)) {
				$contents[] = array('text' => '<br>' . TEXT_TYPES_TYPE . '<br>' . $all_types[$tInfo->blocks_types_type]);
			  }

			  $templates_string = '';
			  $templates_query = tep_db_query("select templates_id, templates_name from " . TABLE_TEMPLATES . " where language_id = '" . (int)$languages_id . "' order by sort_order, default_status desc, templates_id");
			  while ($templates_array = tep_db_fetch_array($templates_query)) {
				$templates_string .= in_array($templates_array['templates_id'], array_keys($tInfo->templates)) ? '<br>' . $templates_array['templates_name'] : '';
			  }
			  $contents[] = array('text' => '<br>' . TEXT_ALLOW_TEMPLATES . $templates_string);
			}

			$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($tInfo->date_added));
			if (tep_not_null($tInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($tInfo->last_modified));
		  } elseif (isset($bInfo) && is_object($bInfo)) { // block info box contents
			$heading[] = array('text' => '<strong>' . $bInfo->blocks_name . '</strong>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&bID=' . $bInfo->blocks_id . '&type=' . $type . '&action=edit_block') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>' . ((DEBUG_MODE=='off' && $tPath==0) ? '' : ' <a href="' . tep_href_link(FILENAME_BLOCKS, 'tPath=' . $tPath . '&bID=' . $bInfo->blocks_id . '&type=' . $type . '&action=delete_block') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'));
			$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($bInfo->date_added));
			if (tep_not_null($bInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($bInfo->last_modified));
		  }
		} else { // create type/block info
		  $heading[] = array('text' => '<strong>' . EMPTY_TYPE . '</strong>');

		  $contents[] = array('text' => TEXT_NO_CHILD_TYPES_OR_BLOCKS);
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