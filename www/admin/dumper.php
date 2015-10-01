<?php
  require('includes/application_top.php');

// Максимальное время выполнения скрипта в секундах
// 0 - без ограничений
  define('TIME_LIMIT', 600);
// Ограничение размера данных доставаемых за одно обращения к БД (в мегабайтах)
// Нужно для ограничения количества памяти пожираемой сервером при дампе очень объемных таблиц
  define('LIMIT', 1);
// Кодировка соединения с MySQL
// auto - автоматический выбор (устанавливается кодировка таблицы), cp1251 - windows-1251, и т.п.
  define('DB_CHARSET', 'auto');
// Кодировка соединения с MySQL при восстановлении
// На случай переноса со старых версий MySQL (до 4.1), у которых не указана кодировка таблиц в дампе
// При добавлении 'forced->', к примеру 'forced->cp1251', кодировка таблиц при восстановлении будет принудительно заменена на cp1251
// Можно также указывать сравнение нужное к примеру 'cp1251_ukrainian_ci' или 'forced->cp1251_ukrainian_ci'
  define('RESTORE_CHARSET', 'cp1251');
// Типы таблиц у которых сохраняется только структура, разделенные запятой
  define('ONLY_CREATE', 'MRG_MyISAM,MERGE,HEAP,MEMORY');

// Дальше ничего редактировать не нужно

  tep_set_time_limit(TIME_LIMIT);

  if (function_exists("ob_implicit_flush")) ob_implicit_flush();

  if (!function_exists('tep_db_fetch_row')) {
	function tep_db_fetch_row($db_query) {
	  return mysql_fetch_row($db_query);
	}
  }

  $dump = new dumper();

  $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
  switch ($action) {
	case 'backup':
	  $dump->backup();
	  break;
	case 'restore':
	  $dump->restore();
	  break;
  }

  class dumper {
	function dumper() {
	  $this->SET['comp_method'] = '';
	  $this->SET['comp_level']  = 5;
	  $this->SET['last_db_restore'] = '';

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

	  $this->comp_methods[] = array('id' => '', 'text' => TEXT_INFO_USE_NO_COMPRESSION);
	  if (function_exists("gzopen")) {
		$this->comp_methods[] = array('id' => 'gz', 'text' => TEXT_INFO_USE_GZIP);
	  }
	  if (function_exists("bzopen")) {
		$this->comp_methods[] = array('id' => 'bz2', 'text' => TEXT_INFO_USE_BZIP2);
	  }
	}

	function backup() {
	  global $HTTP_POST_VARS;

	  $this->SET['comp_method'] = isset($HTTP_POST_VARS['comp_method']) ? intval($HTTP_POST_VARS['comp_method']) : '';
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
	  $name = 'db_' . DB_DATABASE . '-' . date("YmdHis");
	  $fp = $this->fn_open($name, "w");
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

	  return $name;
	}

	function restore() {
	  global $HTTP_POST_VARS, $HTTP_GET_VARS;

	  $this->SET['last_db_restore'] = isset($HTTP_POST_VARS['db_restore']) ? $HTTP_POST_VARS['db_restore'] : '';
	  $file = isset($HTTP_GET_VARS['file']) ? $HTTP_GET_VARS['file'] : '';

// Определение формата файла
	  if (preg_match("/^(.+?)\.sql(\.(bz2|gz))?$/", $file, $matches)) {
		if (isset($matches[3]) && $matches[3] == 'bz2') {
		  $this->SET['comp_method'] = 'bz2';
		} elseif (isset($matches[2]) &&$matches[3] == 'gz') {
		  $this->SET['comp_method'] = 'gz';
		} else {
		  $this->SET['comp_method'] = '';
		}
		$file = $matches[1];
	  }
	  $fp = $this->fn_open($file, "r");
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
		$this->filename = "{$name}.sql.gz";
		return gzopen(DIR_FS_BACKUP . $this->filename, "{$mode}b{$this->SET['comp_level']}");
	  } elseif ($this->SET['comp_method'] == 'bz2') {
		$this->filename = "{$name}.sql.bz2";
		return bzopen(DIR_FS_BACKUP . $this->filename, "{$mode}b{$this->SET['comp_level']}");
	  } else {
		$this->filename = "{$name}.sql";
		return fopen(DIR_FS_BACKUP . $this->filename, "{$mode}b");
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
?>

<form name="skb" method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
  <fieldset onClick="document.skb.action[0].checked = 1;">
	<legend>
	  <input type="radio" name="action" value="backup" />
	  Backup / Создание резервной копии БД
	</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="2">
	  <tr>
		<td width="35%">Метод сжатия:</td>
		<td width="65%"><?php echo tep_draw_pull_down_menu('comp_method', $dump->comp_methods); ?></td>
	  </tr>
	</table>
  </fieldset>
  <fieldset onClick="document.skb.action[1].checked = 1;">
	<legend>
	  <input type="radio" name="action" value="restore" />
	  Restore / Восстановление БД из резервной копии
	</legend>
	<table width=100% border=0 cellspacing=0 cellpadding=2>
	  <tr>
		<td>БД:</td>
		<td><select name="db_restore">
		<?php echo $dump->vars['db_restore']; ?>
		</select></td>
	  </tr>
	  <tr>
		<td width="35%">Файл:</td>
		<td width="65%"><select name="file">
		<?php echo $dump->vars['files']; ?>
		</select></td>
	  </tr>
	</table>
  </fieldset>
  <input type="submit" value="&nbsp; &nbsp; GO &nbsp; &nbsp;" />
</form>
