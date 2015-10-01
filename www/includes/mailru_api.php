<?php

class Mailru_API 
{
	var $send_packege = 1; //Отправлять
	var $status_loger = 1; //При отправке идет проверка статуса каждые 0,5 сек
	var $admin_email = 'snetgrom@gmail.com';//Почта на каторую приходят отчеты об ошибках
	var $packege_url = 'http://setbook.ru/packege/';
	var $packege_dir = 'packege/';
	var $sender_name = 'SetBook - news';
	var $sender_email = 'news@setbook.ru';
	var $tmpl_var;
	var $log_path = 'logs/mail_news_cron.log';
	var $key = "53616c7465645f5f1cb578e141af1db61d5983d87d8bb30b251706a3a9169527ed03fe898cf277ed8af8d5f392a24f8d";//d
	var $status = array(
	'E000' => 'Секретный ключ, указанный в параметрах операции, не опознан',
	'E001' => 'Не передан URL',
	'E002' => 'Не передан pack_id',
	'E003' => 'Тело пакета отсутствует',
	'E004' => 'Недостаточно трафика',
	'E005' => 'Нулевой баланс',
	'E013' => 'Не удалось распознать ответ',
	'S001' => 'Пакет не был найден по указанному URL',
	'S002' => 'Пакет загружен на сервер',
	'S004' => 'Пакет рассылается',
	'S005' => 'Пакет отправлен. Ожидаем удаление информации о пакете из системы.',
	'S006' => 'Во время отправки произошли ошибки',
	'S007' => 'Некорректный формат пакета',
	'S008' => 'Загрузка пакета длилась более 6 минут',
	'S009' => 'Пакет ожидает проверки валидности',
	'S010' => 'Идет проверка валидности пакета',
	'S011' => 'Пакет проверен',
	'S012' => 'Пакет поставлен в очередь на удаление',
	'S013' => 'Пакет удален. Рассылка сообщений прекращена.',
	'S015' => 'Количество адресатов превышает установленный лимит',
	'S018' => 'Пакет отправлен. Информация о пакете удалена.',
	);
	var $logger_load_next_status = array('S000',
	'S002',
	'S004',
	'S009',
	'S010',
	'S011'
	);
	var $logger_fatal_error_status = array('S001',
	'S006',
	'S007',
	'S008',
	'S015'
	);
	var $error = 0;
	function Mailru_API()
	{
		$this->packege_dir = DIR_FS_CATALOG.$this->packege_dir;
	}
	function init($subject, $html)
	{
		$this->count = 0;
		$this->error = 0;
		$this->pack_id = 0;
		$this->filename = date("d-m-Y-H-i").'.'.md5(mt_rand(0, 999999999).microtime()).'.xml';
		$this->log_file = fopen(DIR_FS_CATALOG.$this->log_path, 'a');
		$this->xml_file = fopen($this->packege_dir.$this->filename, 'w+');
		$this->write('<?xml version="1.0" ?>
		<list>
			<body>
				<Data>
					<![CDATA['.$html.']]>
				</Data> 
				<EmailFrom><![CDATA['.$this->sender_email.']]></EmailFrom> 
				<NameFrom><![CDATA['.$this->sender_name.']]></NameFrom> 
				<Subject><![CDATA['.$subject.']]></Subject>
			</body>
			<users>');
		
		//Удаляем старые пакеты
		$files = system('find '.$this->packege_dir.' -type f -mtime +1 -delete -print');
		if($files) $this->write_log("DELETE PACKEGES\n".$files."\n");
	}
	function write($xml)
	{
		fwrite($this->xml_file, iconv('cp1251', 'utf-8', $xml));
	}
	function add_recipient($name, $email)
	{
		$this->count++;
		$tmpl_vars = '';
		foreach($this->tmpl_var as $key => $value)
		{
			$tmpl_vars .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
		}
		$this->write('<user>
			<EmailTo><![CDATA['.$email.']]></EmailTo> 
			<NameTo><![CDATA['.$name.']]></NameTo> 
			'.$tmpl_vars.' 
		</user>');
	}
	function set_tmpl_var($key, $value)
	{
		$this->tmpl_var[$key] = $value;
	}
	function write_log($message)
	{
		$txt = date('d-m-Y H:i:s').'	'.($this->pack_id > 0?'['.$this->pack_id.'] ':'').$message."\r\n";
		fwrite($this->log_file, $txt);
		echo $txt;
	}
	function fatal_error($message)
	{
		$this->error = 1;
		$message = 'Пакет: '.$this->packege_url.$this->filename."\nPack_id: ".$this->pack_id."\n\n".$message;
		
		mail($this->admin_email, 'MailRuAPI - Fatal Error', $message);
	}
	function get_status($pack_id)
	{
		$xml_data = file_get_contents('http://api.content.mail.ru/lease/state/?key='.$this->key.'&pack_id='.$pack_id);
		preg_match_all("'<status>(.*?)</status>'si", $xml_data, $parse);
		if(isset($parse[1][0])) return $parse[1][0];
		return 'E013';
	}
	function loal_next_status($status)
	{
		if(in_array($status, $this->logger_load_next_status)) return true;
		return false;
	}
	function loger($pack_id)
	{
		$status = 'S000';
		while($this->loal_next_status($status))
		{
			$load_status = $this->get_status($pack_id);
			//echo 'get_status '.$pack_id."\n";
			if($status !== $load_status)
			{
				if($load_status[0] == 'E' || in_array($load_status, $this->logger_fatal_error_status) || !isset($this->status[$load_status]))
				{
					$this->write_log('STATUS ERROR '.$load_status.' '.$this->status[$load_status]);
					$this->fatal_error('При отправке произошла ошибка: '.$load_status.' '.$this->status[$load_status]);
				}
				else
				{
					$this->write_log('STATUS '.$load_status.' '.$this->status[$load_status]);
				}
				$status = $load_status;
			}
			sleep(2);
		}
		
	}
	function start()
	{
		$xml_data = file_get_contents('http://api.content.mail.ru/lease/newpack/?key='.$this->key.'&url='.$this->packege_url.$this->filename);
		preg_match_all("'<pack_id>(.*?)</pack_id>'si", $xml_data, $parse);
		if($parse[1][0] > 0) return (int)$parse[1][0];
		preg_match_all("'<status>(.*?)</status>'si", $xml_data, $parse);
		if(isset($parse[1][0])) return $parse[1][0];
		return 'E013';
	}
	function send()
	{
		$this->write('</users></list>');
		fclose($this->xml_file);
		if($this->send_packege)
		{
			$this->write_log('START '.$this->count.' '.Round(filesize($this->packege_dir.$this->filename)/1024, 0).'Kb '.$this->packege_url.$this->filename);
			$pack_id = $this->start();
			if(is_numeric($pack_id))
			{
				$this->pack_id = $pack_id;
				$this->write_log('STATUS OK '.$pack_id.' '.$this->packege_url.$this->filename);
				if($this->status_loger) $this->loger($pack_id);
				else $this->write_log('STATUS LOGER OFF');
			}
			else
			{
				$this->write_log('STATUS ERROR '.$pack_id.' '.$this->status[$pack_id].' '.$this->packege_url.$this->filename);
				$this->fatal_error('При отправке произошла ошибка: '.$pack_id.' '.$this->status[$pack_id]);
			}
			
			$this->write_log("STOP \r\n\r\n".$xmlurl);
		}
		
		fclose($this->log_file);
	}
}