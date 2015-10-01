<?php
  echo $page['pages_description'];
if (isset($HTTP_POST_VARS['action'])):
    
    //$messageStack->add_session('header', SUCCESS_SUBSCRIBE, 'success');

    //tep_redirect(tep_href_link('subscribe.php', '', 'SSL'));
    echo '<div class="messageBox"><div class="messageStackSuccess">Заявка на подписку принята. Спасибо!</div></div>';
    
  
  
  else:
  ?>
  <div class="page_description">
	  <div class="page_description_header"></div>
	  <div class="page_description_body">
	  <h2>Ваш e-mail:</h2>
	  <form action="/subscribe.php" method="post">
	  <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>" id="big_subscribe">
	  </div>
	  <div class="page_description_footer"></div>
	</div>
	
	<fieldset>
	<legend>Подписка на новинки</legend>
		<div>
				<?php echo tep_draw_checkbox_field('subscribe_['.$val['id'].']', '1', 1); ?>
				<!--a href="<?php echo tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $val['id']); ?>"-->
					Новости и акции магазина SetBook
				<!--/a-->
			</div>
		<?php 
			/*$result = tep_db_query("
			SELECT c.categories_id AS id, t.products_types_name AS tname, cd.categories_name AS name FROM categories c
			JOIN categories_description AS cd ON cd.categories_id = c.categories_id
			RIGHT JOIN products_types AS t ON t.products_types_id = c.products_types_id
			AND cd.language_id = 2
			AND t.language_id = 2
			AND c.products_types_id = 1
			AND c.parent_id = 0
			;");*/
			$result = tep_db_query("
			SELECT c.categories_id AS id, cd.categories_name AS name FROM categories c
			JOIN categories_description AS cd ON cd.categories_id = c.categories_id
			AND cd.language_id = 2
			AND c.products_types_id = 1
			AND c.parent_id = 0
			;");
		
			while($val = tep_db_fetch_array($result)): ?>
			<div>
				<?php echo tep_draw_checkbox_field('subscribe_['.$val['id'].']', '1', 0); ?>
				<!--a href="<?php echo tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $val['id']); ?>"-->
					<?php echo $val['tname'].''.$val['name']; ?>
				<!--/a-->
			</div>
		<?php endwhile; ?>
	</fieldset>
	<br>
	<input type="hidden" name="action" value="true">
	<input border="0" type="image" src="includes/templates/setbook/images/buttons/subscribe.gif" id="big_subscribe_button">
	</form>
	<?php endif; ?>