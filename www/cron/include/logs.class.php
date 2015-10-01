<?php

class logs
{
	function __construct($file)
	{
		$this->log_file = fopen($file, 'a');
	}
	function write($message)
	{
		$txt = date('[d-m-Y H:i:s]').'	'.$message."\r\n";
		fwrite($this->log_file, $txt);
		echo $txt;
	}
	function __destruct()
	{
		fclose($this->log_file);
	}
}

?>