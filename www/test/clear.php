<?php
$clear_type = $_GET['type'];
$files = glob('/tmp/lock_*');
foreach($files as $file)
{
	if(is_file($file))
	{
    	unlink($file);
	}
}

$files = glob('/tmp/dbtimes_*');
foreach($files as $file)
{
	if(is_file($file))
	{
		unlink($file);
	}
}

$files = glob('/tmp/log_ex_*');
foreach($files as $file)
{
	if(is_file($file))
	{
		unlink($file);
	}
}

if (($clear_type == 'all') || ($clear_type == 'orders'))
{
	$files = glob('/tmp/orders_*');
	foreach($files as $file)
	{
		if(is_file($file))
		{
    		unlink($file);
		}
	}
}
?>