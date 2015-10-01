<?php
  require('includes/application_top.php');

// Ограничение размера данных доставаемых за одно обращения к БД (в мегабайтах)
// Нужно для ограничения количества памяти пожираемой сервером при дампе очень объемных таблиц
  define('LIMIT', 1);
// Кодировка соединения с MySQL
// auto - автоматический выбор (устанавливается кодировка таблицы), cp1251 - windows-1251, и т.п.
  define('DB_CHARSET', 'auto');
// Кодировка соединения с MySQL при восстановлении
// На случай переноса со старых версий MySQL (до 4.1), у которых не указана кодировка таблиц в дампе
// При добавлении 'forced->', к примеру 'forced->cp1251', кодировка таблиц при восстановлении будет принудительно заменена на cp1251
// Можно также указывать сравнение нужное к примеру 'cp1251_general_ci' или 'forced->cp1251_general_ci'
  define('RESTORE_CHARSET', 'cp1251');
// Типы таблиц у которых сохраняется только структура, разделенные запятой
  define('ONLY_CREATE', 'MRG_MyISAM,MERGE,HEAP,MEMORY');

// Дальше ничего редактировать не нужно

  tep_set_time_limit(0);

  if (function_exists("ob_implicit_flush")) ob_implicit_flush();

  if (!function_exists('tep_db_fetch_row')) {
	function tep_db_fetch_row($db_query) {
	  return mysql_fetch_row($db_query);
	}
  }

  $dump = new dumper();

  class dumper {
	function dumper() {
	  $this->SET['comp_method'] = '';
	  $this->SET['comp_level']  = 5;
	  $this->backup_file = $this->restore_file = '';
	  $this->comp_methods = array();

// Версия MySQL вида 40101
	  preg_match("/^(\d+)\.(\d+)\.(\d+)/", mysql_get_server_info(), $m);
	  $this->mysql_version = sprintf("%d%02d%02d", $m[1], $m[2], $m[3]);

	  $this->only_create = explode(',', ONLY_CREATE);
	  $this->forced_charset  = false;
	  $this->restore_charset = $this->restore_collate = '';
	  if (preg_match("/^(forced->)?(([a-z0-9]+)(\_\w+)?)$/", RESTORE_CHARSET, $matches)) {
		$this->forced_charset  = $matches[1] == 'forced->';
		$this->restore_charset = $matches[3];
		$this->restore_collate = !empty($matches[4]) ? ' COLLATE ' . $matches[2] : '';
	  }

	  $this->comp_methods[] = array('id' => '0', 'text' => TEXT_INFO_USE_NO_COMPRESSION);
	  if (function_exists("gzopen")) {
		$this->comp_methods[] = array('id' => 'gz', 'text' => TEXT_INFO_USE_GZIP);
	  }
	  if (function_exists("bzopen")) {
		$this->comp_methods[] = array('id' => 'bz2', 'text' => TEXT_INFO_USE_BZIP2);
	  }
	}

	function backup() {
	  global $HTTP_POST_VARS;

	  $this->SET['comp_method'] = (tep_not_null($HTTP_POST_VARS['compress']) && $HTTP_POST_VARS['compress']!='0') ? intval($HTTP_POST_VARS['compress']) : '';
	  $this->SET['comp_level'] = 5;

	  $tables = array();
	  $result = tep_db_query("SHOW TABLES");
	  $all = 0;
	  while ($row = tep_db_fetch_row($result)) {
		$tables[] = $row[0];
	  }

	  $tabs = count($tables);
// Определение размеров таблиц
	  $result = tep_db_query("SHOW TABLE STATUS");
	  $tabinfo = array();
	  $tab_charset = array();
	  $tab_type = array();
	  $tabinfo[0] = 0;
	  $info = '';
	  while ($item = tep_db_fetch_array($result)) {
		if (in_array($item['Name'], $tables)) {
		  $item['Rows'] = empty($item['Rows']) ? 0 : $item['Rows'];
		  $tabinfo[0] += $item['Rows'];
		  $tabinfo[$item['Name']] = $item['Rows'];
		  $tabsize[$item['Name']] = 1 + round(LIMIT * 1048576 / ($item['Avg_row_length'] + 1));
		  if ($item['Rows']) $info .= "|" . $item['Rows'];
		  if (!empty($item['Collation']) && preg_match("/^([a-z0-9]+)_/i", $item['Collation'], $m)) {
			$tab_charset[$item['Name']] = $m[1];
		  }
		  $tab_type[$item['Name']] = isset($item['Engine']) ? $item['Engine'] : $item['Type'];
		}
	  }
	  $show = 10 + $tabinfo[0] / 50;
	  $info = $tabinfo[0] . $info;
	  $this->backup_file = DIR_FS_BACKUP . 'db_' . DB_DATABASE . '-' . date("YmdHis") . '.sql';
	  $fp = $this->fn_open($this->backup_file, "w");
	  $this->fn_write($fp, '#SKD101|{' . DB_DATABASE . '|' . $tabs . '|' . date("Y.m.d H:i:s") . '|' . $info . "\n\n");
	  $t = 0;
	  $result = tep_db_query("SET SQL_QUOTE_SHOW_CREATE = 1");
// Кодировка соединения по умолчанию
	  if ($this->mysql_version > 40101 && DB_CHARSET != 'auto') {
		tep_db_query("SET NAMES '" . DB_CHARSET . "'");
		$last_charset = DB_CHARSET;
	  } else {
		$last_charset = '';
	  }
	  if (!is_array($tables)) $tables = array();
	  reset($tables);
	  while (list(, $table) = each($tables)) {
// Выставляем кодировку соединения соответствующую кодировке таблицы
		if ($this->mysql_version > 40101 && $tab_charset[$table] != $last_charset) {
		  if (DB_CHARSET == 'auto') {
			tep_db_query("SET NAMES '" . $tab_charset[$table] . "'");
			$last_charset = $tab_charset[$table];
		  }
		}
// Создание таблицы
		$result = tep_db_query("SHOW CREATE TABLE `{$table}`");
		$tab = tep_db_fetch_row($result);
		$tab = preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|COLLATE=\w+|character set \w+|collate \w+)/i', '/*!40101 \\1 */', $tab);
		$this->fn_write($fp, "DROP TABLE IF EXISTS `{$table}`;\n{$tab[1]};\n\n");
// Проверяем нужно ли дампить данные
		if (in_array($tab_type[$table], $this->only_create)) {
		  continue;
		}
// Опредеделяем типы столбцов
		$NumericColumn = array();
		$result = tep_db_query("SHOW COLUMNS FROM `{$table}`");
		$field = 0;
		while ($col = tep_db_fetch_row($result)) {
		  $NumericColumn[$field++] = preg_match("/^(\w*int|year)/", $col[1]) ? 1 : 0;
		}
		$fields = $field;
		$from = 0;
		$limit = $tabsize[$table];
		$limit2 = round($limit / 3);
		$i = 0;
		while (($result = tep_db_query("SELECT * FROM `{$table}` LIMIT {$from}, {$limit}")) && ($total = tep_db_num_rows($result))) {
		  if ($i==0) $this->fn_write($fp, "INSERT INTO `{$table}` VALUES");
		  while ($row = tep_db_fetch_row($result)) {
			$i++;
			$t++;

			for ($k = 0; $k < $fields; $k++) {
			  if ($NumericColumn[$k]) $row[$k] = isset($row[$k]) ? $row[$k] : "NULL";
			  else $row[$k] = isset($row[$k]) ? "'" . mysql_escape_string($row[$k]) . "'" : "NULL";
			  $row[$k] = str_replace(array('\\\"', '\"', "\\\'"), array('"', '"', "\'"), $row[$k]);
			}

			$this->fn_write($fp, ($i == 1 ? "" : ",") . "\n(" . implode(", ", $row) . ")");
		  }
		  tep_db_free_result($result);
		  if ($total < $limit) {
			break;
		  }
		  $from += $limit;
		}

		if ($total > 0) $this->fn_write($fp, ";\n\n");
	  }
	  $this->fn_close($fp);
	}

	function restore() {
	  global $HTTP_POST_VARS, $HTTP_GET_VARS;

// Определение формата файла
	  if (preg_match("/^(.+?\.sql)(\.(bz2|gz))?$/", $this->restore_file, $matches)) {
		if (isset($matches[3]) && $matches[3] == 'bz2') {
		  $this->SET['comp_method'] = 'bz2';
		} elseif (isset($matches[2]) &&$matches[3] == 'gz') {
		  $this->SET['comp_method'] = 'gz';
		} else {
		  $this->SET['comp_method'] = '';
		}
		$this->restore_file = $matches[1];
	  }
	  $fp = $this->fn_open($this->restore_file, "r");
	  $this->file_cache = $sql = $table = $insert = '';
	  $is_skd = $query_len = $execute = $q =$t = $i = 0;
	  $limit = 300;
	  $index = 4;
	  $tabs = 0;
	  $cache = '';
	  $info = array();

// Установка кодировки соединения
	  if ($this->mysql_version > 40101 && (DB_CHARSET != 'auto' || $this->forced_charset)) { // Кодировка по умолчанию, если в дампе не указана кодировка
		tep_db_query("SET NAMES '" . $this->restore_charset . "'");
		$last_charset = $this->restore_charset;
	  } else {
		$last_charset = '';
	  }
	  $last_showed = '';
	  while (($str = $this->fn_read_str($fp)) !== false){
		if (empty($str) || preg_match("/^(#|--)/", $str)) {
		  if (!$is_skd && preg_match("/^#SKD101\|/", $str)) {
			$info = explode("|", $str);
			$is_skd = 1;
		  }
		  continue;
		}
		$query_len += strlen($str);

		if (!$insert && preg_match("/^(INSERT INTO `?([^` ]+)`? .*?VALUES)(.*)$/i", $str, $m)) {
		  if ($table != $m[2]) {
			$table = $m[2];
			$tabs++;
			$last_showed = $table;
			$i = 0;
		  }
		  $insert = $m[1] . ' ';
		  $sql .= $m[3];
		  $index++;
		  $info[$index] = isset($info[$index]) ? $info[$index] : 0;
		  $limit = round($info[$index] / 20);
		  $limit = $limit < 300 ? 300 : $limit;
		} else {
		  $sql .= $str;
		  if ($insert) {
			$i++;
			$t++;
		  }
		}

		if (!$insert && preg_match("/^CREATE TABLE (IF NOT EXISTS )?`?([^` ]+)`?/i", $str, $m) && $table != $m[2]){
		  $table = $m[2];
		  $insert = '';
		  $tabs++;
		  $is_create = true;
		  $i = 0;
		}
		if ($sql) {
		  if (preg_match("/;$/", $str)) {
			$sql = rtrim($insert . $sql, ";");
			if (empty($insert)) {
			  if ($this->mysql_version < 40101) {
				$sql = preg_replace("/ENGINE\s?=/", "type=", $sql);
			  } elseif (preg_match("/CREATE TABLE/i", $sql)) {
// Выставляем кодировку соединения
				if (preg_match("/(CHARACTER SET|CHARSET)[=\s]+(\w+)/i", $sql, $charset)) {
				  if (!$this->forced_charset && $charset[2] != $last_charset) {
					if (DB_CHARSET == 'auto') {
					  tep_db_query("SET NAMES '" . $charset[2] . "'");
					  $last_charset = $charset[2];
					}
				  }
// Меняем кодировку если указано форсировать кодировку
				  if ($this->forced_charset) {
					$sql = preg_replace("/(\/\*!\d+\s)?((COLLATE)[=\s]+)\w+(\s+\*\/)?/i", '', $sql);
					$sql = preg_replace("/((CHARACTER SET|CHARSET)[=\s]+)\w+/i", "\\1" . $this->restore_charset . $this->restore_collate, $sql);
				  }
				} elseif (DB_CHARSET == 'auto') { // Вставляем кодировку для таблиц, если она не указана и установлена auto кодировка
				  $sql .= ' DEFAULT CHARSET=' . $this->restore_charset . $this->restore_collate;
				  if ($this->restore_charset != $last_charset) {
					tep_db_query("SET NAMES '" . $this->restore_charset . "'");
					$last_charset = $this->restore_charset;
				  }
				}
			  }
			  if ($last_showed != $table) $last_showed = $table;
			} elseif ($this->mysql_version > 40101 && empty($last_charset)) { // Устанавливаем кодировку на случай если отсутствует CREATE TABLE
			  tep_db_query("SET $this->restore_charset '" . $this->restore_charset . "'");
			  $last_charset = $this->restore_charset;
			}
			$insert = '';
			$execute = 1;
		  }
		  if ($query_len >= 65536 && preg_match("/,$/", $str)) {
			$sql = rtrim($insert . $sql, ",");
			$execute = 1;
		  }
		  if ($execute) {
			$q++;
			tep_db_query($sql);
			$sql = '';
			$query_len = 0;
			$execute = 0;
		  }
		}
	  }

	  $this->fn_close($fp);
	}

	function fn_open($name, $mode){
	  if ($this->SET['comp_method'] == 'gz') {
		$this->filename = $name . '.gz';
		return gzopen($this->filename, "{$mode}b{$this->SET['comp_level']}");
	  } elseif ($this->SET['comp_method'] == 'bz2') {
		$this->filename = $name . '.bz2';
		return bzopen($this->filename, "{$mode}b{$this->SET['comp_level']}");
	  } else {
		$this->filename = $name;
		return fopen($this->filename, "{$mode}b");
	  }
	}

	function fn_write($fp, $str){
	  if ($this->SET['comp_method'] == 'gz') {
		gzwrite($fp, $str);
	  } elseif ($this->SET['comp_method'] == 'bz2') {
		bzwrite($fp, $str);
	  } else {
		fwrite($fp, $str);
	  }
	}

	function fn_read($fp){
	  if ($this->SET['comp_method'] == 'gz') {
		return gzread($fp, 4096);
	  } elseif ($this->SET['comp_method'] == 'bz2') {
		return bzread($fp, 4096);
	  } else {
		return fread($fp, 4096);
	  }
	}

	function fn_read_str($fp){
	  $string = '';
	  $this->file_cache = ltrim($this->file_cache);
	  $pos = strpos($this->file_cache, "\n", 0);
	  if ($pos < 1) {
		while (!$string && ($str = $this->fn_read($fp))){
		  $pos = strpos($str, "\n", 0);
		  if ($pos === false) {
			$this->file_cache .= $str;
		  } else {
			$string = $this->file_cache . substr($str, 0, $pos);
			$this->file_cache = substr($str, $pos + 1);
		  }
		}
		if (!$str) {
		  if ($this->file_cache) {
			$string = $this->file_cache;
			$this->file_cache = '';
			return trim($string);
		  }
		  return false;
		}
	  } else {
  		$string = substr($this->file_cache, 0, $pos);
  		$this->file_cache = substr($this->file_cache, $pos + 1);
	  }
	  return trim($string);
	}

	function fn_close($fp){
	  if ($this->SET['comp_method'] == 'gz') {
		gzclose($fp);
	  } elseif ($this->SET['comp_method'] == 'bz2') {
		bzclose($fp);
	  } else {
		fclose($fp);
	  }
	  @chmod(DIR_FS_BACKUP . $this->filename, 0666);
	}
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  if (DEBUG_MODE!='on' && ($action=='restorenow' || $action=='restorelocalnow') ) $action = '';

  if (tep_not_null($action)) {
    switch ($action) {
      case 'forget':
        $messageStack->add_session(SUCCESS_LAST_RESTORE_CLEARED, 'success');

        tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      case 'backupnow':
		$dump->backup();

        if (isset($HTTP_POST_VARS['download']) && ($HTTP_POST_VARS['download'] == 'yes')) {
          header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=' . basename($dump->backup_file));

          readfile($dump->backup_file);
          unlink($dump->backup_file);

          exit;
        } else {
          $messageStack->add_session(SUCCESS_DATABASE_SAVED, 'success');
        }

        tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      case 'restorenow':
      case 'restorelocalnow':
        if ($action == 'restorenow') {
          if (file_exists(DIR_FS_BACKUP . basename($HTTP_GET_VARS['file']))) {
            $dump->restore_file = DIR_FS_BACKUP . basename($HTTP_GET_VARS['file']);
			$dump->restore();
          }
        } elseif ($action == 'restorelocalnow') {
		  if (is_uploaded_file($_FILES['sql_file']['tmp_name'])) {
			$dump->restore_file = $_FILES['sql_file']['tmp_name'];
			$dump->restore();
          }
        }

		$messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');

        tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      case 'download':
        $extension = substr($HTTP_GET_VARS['file'], -3);

        if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'sql') ) {
          if ($fp = fopen(DIR_FS_BACKUP . basename($HTTP_GET_VARS['file']), 'rb')) {
            $buffer = fread($fp, filesize(DIR_FS_BACKUP . basename($HTTP_GET_VARS['file'])));
            fclose($fp);

            header('Content-type: application/x-octet-stream');
            header('Content-disposition: attachment; filename=' . basename($HTTP_GET_VARS['file']));

            echo $buffer;

            die();
          }
        } else {
          $messageStack->add(ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE, 'error');
        }
        break;
      case 'deleteconfirm':
        if (strstr($HTTP_GET_VARS['file'], '..')) tep_redirect(tep_href_link(FILENAME_BACKUP));

        tep_remove(DIR_FS_BACKUP . '/' . basename($HTTP_GET_VARS['file']));

        if (!$tep_remove_error) {
          $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');

          tep_redirect(tep_href_link(FILENAME_BACKUP));
        }
        break;
    }
  }

// check if the backup directory exists
  $dir_ok = false;
  if (is_dir(DIR_FS_BACKUP)) {
    if (is_writeable(DIR_FS_BACKUP)) {
      $dir_ok = true;
    } else {
      $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
    }
  } else {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TITLE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_FILE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_FILE_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $allowed_databases_array = array();
  $dbs_query = tep_db_query("select shops_database from " . TABLE_SHOPS . " where shops_database <> ''" . (sizeof($allowed_shops_array)>0 ? " and shops_id in ('" . implode("', '", $allowed_shops_array) . "')" : "") . " order by sort_order");
  while ($dbs = tep_db_fetch_array($dbs_query)) {
	$allowed_databases_array[] = $dbs['shops_database'];
  }
  if ($dir_ok == true) {
    $dir = dir(DIR_FS_BACKUP);
    $contents = array();
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_BACKUP . $file)) {
		$dbname = substr($file, 0, strpos($file, '.'));
		$dbname = preg_replace('/^db_(.*)\-\d{14}$/', '$1', $dbname);
		if (sizeof($allowed_shops_array) > 0 && !in_array($dbname, $allowed_databases_array)) {
		} else {
		  $contents[date('Y-m-d H:i:s', filemtime(DIR_FS_BACKUP . $file))] = $file;
		}
      }
    }
	reset($contents);
    krsort($contents);

	while (list(, $entry) = each($contents)) {
      $check = 0;

      if ((!isset($HTTP_GET_VARS['file']) || (isset($HTTP_GET_VARS['file']) && ($HTTP_GET_VARS['file'] == $entry))) && !isset($buInfo) && ($action != 'backup') && ($action != 'restorelocal')) {
        $file_array['file'] = $entry;
        $file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry));
        $file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';
        switch (substr($entry, -3)) {
          case 'zip': $file_array['compression'] = 'ZIP'; break;
          case '.gz': $file_array['compression'] = 'GZIP'; break;
          default: $file_array['compression'] = TEXT_NO_EXTENSION; break;
        }

        $buInfo = new objectInfo($file_array);
      }

      if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        $onclick_link = 'file=' . $buInfo->file . '&action=restore';
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        $onclick_link = 'file=' . $entry;
      }
?>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'action=download&file=' . $entry) . '">' . tep_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD) . '</a>&nbsp;' . $entry; ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry)); ?></td>
                <td class="dataTableContent" align="right" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo number_format(filesize(DIR_FS_BACKUP . $entry)); ?> bytes</td>
                <td class="dataTableContent" align="right"><?php if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $entry) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
    $dir->close();
  }
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP; ?></td>
                <td align="right" class="smallText"><?php if ( ($action != 'backup') && (isset($dir)) ) echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'action=backup') . '">' . tep_image_button('button_backup.gif', IMAGE_BACKUP) . '</a>'; if ( ($action != 'restorelocal') && isset($dir) && DEBUG_MODE=='on') echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP, 'action=restorelocal') . '">' . tep_image_button('button_upload_backup.gif', IMAGE_UPLOAD_BACKUP) . '</a>'; ?></td>
              </tr>
<?php
  if (defined('DB_LAST_RESTORE')) {
?>
              <tr>
                <td class="smallText" colspan="4"><?php echo TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' <a href="' . tep_href_link(FILENAME_BACKUP, 'action=forget') . '">' . TEXT_FORGET . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'backup':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_BACKUP . '</strong>');

      $contents = array('form' => tep_draw_form('backup', FILENAME_BACKUP, 'action=backupnow'));
      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);

	  $temp_string = '';
	  reset($dump->comp_methods);
	  while (list(, $method) = each($dump->comp_methods)) {
		$temp_string .= '<br>' . tep_draw_radio_field('compress', $method['id'], $method['id']=='0') . $method['text'];
	  }
	  $contents[] = array('text' => '<br>' . $temp_string);

      if ($dir_ok == true) {
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('download', 'yes') . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      } else {
        $contents[] = array('text' => '<br>' . tep_draw_radio_field('download', 'yes', true) . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_backup.gif', IMAGE_BACKUP) . '&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restore':
      $heading[] = array('text' => '<strong>' . $buInfo->date . '</strong>');

      $contents[] = array('text' => tep_break_string(sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''), 35, ' '));
      $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restorenow') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restorelocal':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</strong>');

      $contents = array('form' => tep_draw_form('restore', FILENAME_BACKUP, 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br><br>' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('text' => '<br>' . tep_draw_file_field('sql_file'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_restore.gif', IMAGE_RESTORE) . '&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . $buInfo->date . '</strong>');

      $contents = array('form' => tep_draw_form('delete', FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $buInfo->file . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($buInfo) && is_object($buInfo)) {
        $heading[] = array('text' => '<strong>' . $buInfo->date . '</strong>');

        $contents[] = array('align' => 'center', 'text' => (DEBUG_MODE=='on' ? '<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restore') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a> ' : '') . '<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE . ' ' . $buInfo->date);
        $contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COMPRESSION . ' ' . $buInfo->compression);
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
    </table></td>
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