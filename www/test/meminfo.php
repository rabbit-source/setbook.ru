<?php
/*	if ($_SERVER['USE_DEBUG_CONFIGURATION'] != 1)
	{
		require('../includes/configure.php');
	}
	else
	{
		require('../includes/configure_debug.php');
	}

	include(DIR_WS_FUNCTIONS . 'locks.php');*/

function tep_get_memory()
{
	foreach(file('/proc/meminfo') as $ri)
	{
		$m[strtok($ri, ':')] = strtok('');
		//echo $ri . ': ' . strtok('') . '<br/>';
	}

	return	'Percent:  ' . (round(($m['MemFree']) / $m['MemTotal'] * 100)) . '\r\n' .	
			'MemFree:  ' . $m['MemFree'] . '\r\n' . 
			'Buffers:  ' . $m['Buffers'] . '\r\n' . 
			'Cached:   ' . $m['Cached'] . '\r\n' .
			'MemTotal: ' . $m['MemTotal'] . '\r\n';
}

	echo str_replace('\r\n', '<br/>', tep_get_memory());
?>