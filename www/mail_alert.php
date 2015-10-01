<?php
//Скрипт для рассылки сервисных сообщений подписчикам на новинки
//Не запускать!
exit;
set_time_limit(0);
chdir('/var/www/2009/');
if(count($argv) == 0) exit;
require('includes/application_top.php');
require('includes/mailru_api.php');
require('includes/subscribe.php');

$subscribe = new subscribe;
$mail = new Mailru_API;

$news = $subscribe->get_newsletters();

for($i = 0; $i < count($news); $i++)
{
	echo $news[$i]['title']."\n";
	//$users = $subscribe->get_subscribers(unserialize($news[$i]['filter']));
	
	$query = "SELECT s.user_id as user_id, 
		c.customers_email_address as email,
		c.customers_firstname as firstname,
		c.customers_lastname as lastname,
		t.shops_currency as currency
		FROM subscribe s
		JOIN ".TABLE_CUSTOMERS." AS c ON c.customers_id = s.user_id
		LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id	
		WHERE 1
		GROUP BY s.user_id;";
	
	$users = tep_db_query($query);
	
	$subscribe->set_template_param('username', '<tmpl_var name>');
	$subscribe->update_newsletters($news[$i]['id'], 2); //Ставим статус рассылается
	//Устанавливаем переменные для статистики
	$subscribe->key_data['type_id'] = 0; //Устанавливем статус письма(0 - обычная рассылка)
	$subscribe->key_data['id'] = $news[$i]['id']; //Идентификатор отправляемого письма
	$subscribe->key_data['date'] = time(); //Дата отправки
	$mail->init($news[$i]['title'], $subscribe->get_template_params($news[$i]['content']));
	while ($user = tep_db_fetch_array($users)) 
	{
		//if($subscribe->debug) print_r($user);
		$mail->set_tmpl_var('name', $user['firstname']);
		$mail->add_recipient($user['firstname'].' '.$user['lastname'], $user['email']);
		//echo ++$k." - add ".iconv('cp1251', 'utf-8', $user['firstname'])."	".$user['email']."\n";
	}
	$mail->send();
	if($mail->error) $subscribe->update_newsletters($news[$i]['id'], 4);
	else $subscribe->update_newsletters($news[$i]['id'], 3);
}
	
	
?>














