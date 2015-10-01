<?php 
$title_text = 'Новости';
include('top.php'); ?>
	<font face="Tahoma" size="2" color="#363636">
		Здравствуйте, {{username}}!
	
		<br /><br />
		
		<?php echo $content; ?>
		
		<br /><br />
		С уважением,<br />
		Книжный магазин 
		<a href="<?php echo HTTP_SERVER; ?>" style="color:#FF6700;text-decoration:underline;">
			<font face="Tahoma" size="2" color="#FF6700">
				SetBook.ru
			</font>
		</a>
	</font>
	<br /><br /><br />
	Вы подписаны на рассылку новостей
	<a href="<?php echo HTTP_SERVER; ?>" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">
			SetBook.ru</font></a>. 
	<br />
	Отказаться от рассылки можно 
	<a href="<?php echo HTTP_SERVER; ?>/account_newsletters.php" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">здесь »</font></a>						
<?php include('bottom.php'); ?>



