<?php 
$title_text = 'Новинки';
include('top.php'); ?>
	<font face="Tahoma" size="3" color="#363636">
		Здравствуйте, {{username}}!
	</font>
	<br />
	Будьте в курсе последних событий и новинок на SetBook.ru!
	<br /><br />
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td align="center">
				<?php
				  if ($banner = tep_banner_exists('main_page', '', false)) echo tep_display_banner($banner);
				?>
			</td>
		</tr>
	</table>
	<br /><br />
	<a href="<?php
		$pages_type = array(FILENAME_CATEGORIES, FILENAME_SERIES, FILENAME_AUTHORS, FILENAME_MANUFACTURERS); 
		$params_type = array('cPath=', 'series_id=', 'authors_id=', 'manufacturers_id=');
		echo tep_href_link($pages_type[$this->type-1], $params_type[$this->type-1] . $category['id'] . '&view=new');
	?>" style="font-size: 17px;color:#FF6700;text-decoration:underline;">
		<font face="Tahoma" size="4" color="#FF6700">
			Новинки <?php $message_type = explode(':', TEXT_CATEGORY_TYPE); echo $message_type[$this->type-1].' '.$category['name']; ?> » 
		</font>
	</a>
	<br />
	<hr color="#cccccc" noshade size="1"/>
	<?php foreach($products as $k => $p): ?>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td width="80" valign="top">
				<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $p['id']); ?>">
					<img src="<?php echo HTTP_SERVER . '/images/thumbs/'.(!$p['image_exists']?'984/81/8319759484.jpg':$p['image']); ?>" border="0" width="80" height="110"  />
				</a>
			</td>
			<td valign="top">
				<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $p['id']); ?>" style="color:#000000;text-decoration:underline;">
					<font face="Tahoma" size="3" color="#000000">
						<?php echo $p['name']; ?>
					</font>
				</a>
				<br />
				<?php 
					if (mb_strlen($p['pdesc'], 'CP1251') > 100) 
					{
						  $short_description = strrev(mb_substr($p['pdesc'], 0, 120, 'CP1251'));
						  $short_description = mb_substr($short_description, strcspn($short_description, '":,.!?()'), mb_strlen($short_description, 'CP1251'), 'CP1251');
						  $short_description = trim(strrev($short_description));
						  if (in_array(mb_substr($short_description, -1, mb_strlen($short_description, 'CP1251'), 'CP1251'), array(':', '(', ')', ','))) 
						  	$short_description = mb_substr($short_description, 0, -1, 'CP1251') . '...';
					} 
					else $short_description = $p['pdesc'];
					
					echo $short_description;

				?>
				<?php if($p['pdesc'] !== ''): ?><br /><br /><?php endif; ?>
				<?php if($p['aid']): ?>
					Автор: 
					<a href="<?php echo tep_href_link(FILENAME_AUTHORS, 'authors_id=' . $p['aid']); ?>" style="color:#842201;text-decoration:underline;">
						<font face="Tahoma" color="#842201">
							<?php echo $p['author']; ?></font></a>,
				<?php endif; ?>
				<?php if($p['mid']): ?>
					Издательство: 
					<a href="<?php echo tep_href_link(FILENAME_MANUFACTURERS, 'manufacturers_id=' . $p['mid']); ?>" style="color:#842201;text-decoration:underline;">
						<font face="Tahoma" color="#842201">
							<?php echo $p['manufacturers']; ?></font></a><?php endif; ?><?php if($p['sid']): ?>,
					Серия: 
					<a href="<?php echo tep_href_link(FILENAME_SERIES, 'series_id=' . $p['sid']); ?>" style="color:#842201;text-decoration:underline;">
						<font face="Tahoma" color="#842201">
							<?php echo $p['series']; ?></font></a>
				<?php endif; ?>
				<br /><br />
			</td>
			<td width="100" valign="top">
				<table border="0" cellspacing="0" cellpadding="5" width="100%">
					<tr>
						<td align="center">
							<font face="Tahoma" size="4" color="#C43711">
								<?php echo '<tmpl_var cost'.$k.'>';//echo $currencies->display_price($p['products_price'], tep_get_tax_rate($p['products_tax_class_id']), 1, true, $user['currency']); ?>
							</font>
						</td>
					</tr>
					<tr>
						<td align="center">
							<a href="<?php echo HTTP_SERVER; ?>/?action=add_product&products_id=<?php echo $p['id']; ?>&link=mail">
								<img src="<?php echo HTTP_SERVER; ?>/includes/templates/setbook/images/buttons/button_in_cart.gif" border="0" width="82" height="20"  />
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr color="#cccccc" noshade size="1"/>
	<?php endforeach; ?>
	<br /><br />
	С уважением,<br />
	книжный магазин 
	<a href="<?php echo HTTP_SERVER; ?>" style="color:#FF6700;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#FF6700">
			SetBook.ru
		</font>
	</a>
	<br /><br /><br />
	Вы подписаны на рассылку о новинках каталога 
	<a href="<?php echo HTTP_SERVER; ?>" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">
			SetBook.ru</font></a>. 
	<br />
	Отказаться от тематической рассылки можно 
	<a href="<?php echo HTTP_SERVER; ?>/account_subscribe.php" style="color:#000000;text-decoration:underline;">
		<font face="Tahoma" size="2" color="#000000">здесь »</font></a>
							
<?php include('bottom.php'); ?>



