<?php
	function tep_lock_log($log)
	{
		$log_file = @fopen(DIR_FS_CATALOG . 'logs/locks_ ' . date('Ymd') . '.txt', 'a');
		if ($log_file)
		{
			if (!flock($log_file, LOCK_EX))
			{
				fclose($log_file);
			}
				
			fwrite($log_file, '' . date('Y-m-d h:i:s') . ' ' . $log . '[' . $_SERVER['REQUEST_URI'] . ']' . PHP_EOL);
			fclose($log_file);
		}
	}

	function tep_lock_release($lock_name)
	{
		global $fsessionlock;
	
		//echo "<pre>releasing lock:" . microtime(true) . "</pre>";
		tep_lock_log('try release lock for ' . $lock_name . ' (' . $_SERVER["REQUEST_URI"] . ')');
		if ((isset($fsessionlock)) && ($fsessionlock != NULL))
		{
			tep_lock_log('releasing lock for  ' . $lock_name . ' (' . $_SERVER["REQUEST_URI"] . ')');
			//fwrite($fsessionlock, $lock_value);
			flock($fsessionlock, LOCK_UN);
			fclose($fsessionlock);
			
			if (file_exists(SESSION_WRITE_DIRECTORY . '/lock_' . $lock_name))
			{
				unlink(SESSION_WRITE_DIRECTORY . '/lock_' . $lock_name);
			}
			
			$fsessionlock = NULL;
			unset($fsessionlock);
		}
		else
		{
			tep_lock_log('lock is already released for ' . $lock_name . ' (isset=' . isset($fsessionlock) . ' $fsessionlock=' . $fsessionlock . ') ');
		}
	
		tep_lock_log('released lock for ' . $lock_name  . ' (' . $_SERVER["REQUEST_URI"] . ')');
	}
	
	function tep_lock_acquire($lock_name)
	{
		global $fsessionlock;

		tep_lock_log('acquire lock for ' . $lock_name . ' (' . $_SERVER["REQUEST_URI"] . ')');

		// Try to create a session_lock file
		$fsessionlock = fopen(SESSION_WRITE_DIRECTORY . '/lock_' . $lock_name, 'w+');
		if ($fsessionlock == FALSE)
		{
			tep_lock_log('error opening file ' . SESSION_WRITE_DIRECTORY . '/lock_' . $lock_name);
			return FALSE;
		}
		
		if (!flock($fsessionlock, LOCK_EX))
		{
			tep_lock_log('error locking file ' . SESSION_WRITE_DIRECTORY . '/lock_' . $lock_name);
			fclose($fsessionlock);
			unset($fsessionlock);
		}
	
		register_shutdown_function('tep_lock_release', $lock_name);
		tep_lock_log('lock aquired for '. $lock_name . ' (' . $_SERVER["REQUEST_URI"]) . ')';
		
		return TRUE;
	}

	function tep_order_log($orderid, $log)
	{
		$order_log_file = @fopen(SESSION_WRITE_DIRECTORY . '/orders_' . date('Ymd') . '.txt', 'a');
		if ($order_log_file)
		{
			fwrite($order_log_file, '[' . date('h:i:s') . '] (id=' . $orderid . ') ' . $log . PHP_EOL);
			fclose($order_log_file);
		}
	}

	function tep_get_memory()
	{
		foreach(file('/proc/meminfo') as $ri)
		{
			$m[strtok($ri, ':')] = strtok('');
		}

		return	'Percent: ' . (round(($m['MemFree']) / $m['MemTotal'] * 100)) . ' ' .	
				'MemFree: ' . $m['MemFree'] . ' ' . 
				'Buffers: ' . $m['Buffers'] . ' ' . 
				'Cached: ' . $m['Cached'] . ' ' .
				'MemTotal: ' . $m['MemTotal'];
	}
	
	function tep_log_ex($log)
	{
		$log_ex_file = @fopen(SESSION_WRITE_DIRECTORY . '/log_ex_' . date('Ymd') . '.txt', 'a');
		if ($log_ex_file)
		{
			fwrite($log_ex_file, '[' . date('h:i:s') . '] ' . $log . PHP_EOL);
			fclose($log_ex_file);
		}
	}
	
?>
