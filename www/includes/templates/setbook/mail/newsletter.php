<?php 
$title_text = '�������';
include('top.php'); ?>
	<font face="Tahoma" size="2" color="#363636">
		������������, {{username}}!
	
		<br /><br />
		
		<?php echo $content; ?>
		
		<br /><br />
		� ���������,<br />
		������� ������� 
		<a href="<?php echo HTTP_SERVER; ?>" style="color:#FF6700;text-decoration:underline;">
			<font face="Tahoma" size="2" color="#FF6700">
				SetBook.ru
			</font>
		</a>
	</font>
	<br /><br /><br />
	�� ��������� �� �������� ��������
	<a href="<?php echo HTTP_SERVER; ?>" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">
			SetBook.ru</font></a>. 
	<br />
	���������� �� �������� ����� 
	<a href="<?php echo HTTP_SERVER; ?>/account_newsletters.php" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">����� �</font></a>						
<?php include('bottom.php'); ?>



