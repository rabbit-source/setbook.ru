<?php
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'lock':
      case 'unlock':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);
        $status = (($action == 'lock') ? '1' : '0');

        tep_db_query("update " . TABLE_NEWSLETTERS . " set locked = '" . $status . "' where newsletters_id = '" . (int)$newsletter_id . "'");

        tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']));
        break;
      case 'insert':
      case 'update':
        if (isset($HTTP_POST_VARS['newsletter_id'])) $newsletter_id = tep_db_prepare_input($HTTP_POST_VARS['newsletter_id']);
        $newsletter_module = tep_db_prepare_input($HTTP_POST_VARS['module']);
        $title = tep_db_prepare_input($HTTP_POST_VARS['title']);
        $content = tep_db_prepare_input($HTTP_POST_VARS['content']);
        $orders = (int)$HTTP_POST_VARS['orders'];
        $site = (int)$HTTP_POST_VARS['site'];
        $city = addslashes($HTTP_POST_VARS['cityName']);
        $filter = true;

        $newsletter_error = false;
        if (empty($title)) {
          $messageStack->add(ERROR_NEWSLETTER_TITLE, 'error');
          $newsletter_error = true;
        }

        if (empty($module)) {
          $messageStack->add(ERROR_NEWSLETTER_MODULE, 'error');
          $newsletter_error = true;
        }

        if ($newsletter_error == false) {
          $sql_data_array = array('title' => $title,
                                  'content' => $content,
                                  'module' => $newsletter_module,
                                  'filter' => serialize(
                                  array('orders' => $orders,
	                                    'site' => $site,
	                                    'city' => $city)
                                  )
                                  );

          if ($action == 'insert') {
            $sql_data_array['date_added'] = 'now()';
            $sql_data_array['status'] = '0';
            $sql_data_array['locked'] = '0';
            
            tep_db_perform(TABLE_NEWSLETTERS, $sql_data_array);
            $newsletter_id = tep_db_insert_id();
          } elseif ($action == 'update') {
            tep_db_perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "newsletters_id = '" . (int)$newsletter_id . "'");
          }

          tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'nID=' . $newsletter_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);

        tep_db_query("delete from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$newsletter_id . "'");

        tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'delete':
      case 'new': if (!isset($HTTP_GET_VARS['nID'])) break;
      case 'send':
      case 'confirm_send':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);

        $check_query = tep_db_query("select locked from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$newsletter_id . "'");
        $check = tep_db_fetch_array($check_query);

        if ($check['locked'] < 1) {
          switch ($action) {
            case 'delete': $error = ERROR_REMOVE_UNLOCKED_NEWSLETTER; break;
            case 'new': $error = ERROR_EDIT_UNLOCKED_NEWSLETTER; break;
            case 'send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
            case 'confirm_send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
          }

          $messageStack->add_session($error, 'error');

          tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']));
        }
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>

<script type="text/javascript" src="./includes/jquery-ui/jquery-1.5.1.js"></script>
<script type="text/javascript" src="./includes/jquery-autocomplete/lib/jquery.bgiframe.min.js"></script>
<script type="text/javascript" src="./includes/jquery-autocomplete/lib/jquery.ajaxQueue.js"></script>
<script type="text/javascript" src="./includes/jquery-autocomplete/lib/thickbox-compressed.js"></script>
<script type="text/javascript" src="./includes/jquery-autocomplete/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="./includes/jquery-autocomplete/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="./includes/jquery-autocomplete/lib/thickbox.css" />
<link rel="stylesheet" href="./includes/jquery-ui/themes/base/jquery.ui.all.css">
<script src="./includes/jquery-ui/ui/jquery.ui.core.js"></script>
<script src="./includes/jquery-ui/ui/jquery.ui.widget.js"></script>
<script src="./includes/jquery-ui/ui/jquery.ui.datepicker.js"></script>
<link rel="stylesheet" href="../demos.css">
<script type="text/javascript">
$().ready(function() {
	$("#sdatepicker").datepicker({ dateFormat: 'yy-mm-dd' });
	$("#edatepicker").datepicker({ dateFormat: 'yy-mm-dd' });
	$("#autoCity").autocomplete("search.php?action=city", {
		width: 200,
		selectFirst: false
	});
	/*$("#autoCategory").autocomplete("search.php?action=category", {
		width: 200,
		selectFirst: false
	});
	$("#autoSeries").autocomplete("search.php?action=series", {
		width: 200,
		selectFirst: false
	});
	$("#autoAuthor").autocomplete("search.php?action=author", {
		width: 200,
		selectFirst: false
	});
	$("#autoPublisher").autocomplete("search.php?action=publisher", {
		width: 200,
		selectFirst: false
	});*/
	$(".autoComplite").result(function(event, data, formatted) {
		if (data)
			$(this).parent().find("input[type=hidden]").val(data[1]);
	});
});
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?><br>
            <a href="<?php echo tep_href_link(FILENAME_NEWSLETTERS, 'action=statistic'); ?>" style="text-decoration: underline;">
            	Статистика</a>&nbsp;&nbsp;&nbsp;
            <a href="<?php echo tep_href_link(FILENAME_NEWSLETTERS, 'action=subscriptions'); ?>" style="text-decoration: underline;">
            	Подписки</a>&nbsp;&nbsp;&nbsp;
            <a href="<?php echo tep_href_link(FILENAME_NEWSLETTERS, 'action=subscribe'); ?>" style="text-decoration: underline;">
            	Подписчики</a>
            </td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('title' => '',
                        'content' => '',
                        'module' => '',
                        'filter' => '');

    $nInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['nID'])) {
      $form_action = 'update';

      $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

      $newsletter_query = tep_db_query("select title, content, module, filter from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
      $newsletter = tep_db_fetch_array($newsletter_query);

      $nInfo->objectInfo($newsletter);
    } elseif ($HTTP_POST_VARS) {
      $nInfo->objectInfo($HTTP_POST_VARS);
    }

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $directory_array = array();
    if ($dir = dir(DIR_WS_MODULES . 'newsletters/')) {
      while ($file = $dir->read()) {
        if (!is_dir(DIR_WS_MODULES . 'newsletters/' . $file)) {
          if (substr($file, strrpos($file, '.')) == $file_extension) {
            $directory_array[] = $file;
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
      $modules_array[] = array('id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')), 'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')));
    }
    
    
    if($nInfo->filter !== '')
    {
    	$data = unserialize($nInfo->filter);
    	$orders = $data['orders'];
    	$site = $data['site'];
    	$city = $data['city'];
    	$filter = true;
    }
    if(isset($HTTP_GET_VARS['orders']) && $HTTP_GET_VARS['orders'] !== '')
	{ 
		$orders = (int)$HTTP_GET_VARS['orders'];
		$filter = true;
	}
	if(isset($HTTP_GET_VARS['site']) && (int)$HTTP_GET_VARS['site'] > 0) 
	{
		$site = (int)$HTTP_GET_VARS['site'];
		$filter = true;
	}
	if(isset($HTTP_GET_VARS['cityName']) && $HTTP_GET_VARS['cityName'] !== '') 
	{
		$city = htmlspecialchars($HTTP_GET_VARS['cityName']);
		$filter = true;
	}
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('newsletter', FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'action=' . $form_action); if ($form_action == 'update') echo tep_draw_hidden_field('newsletter_id', $nID); ?>
        <td><table border="0" cellspacing="0" cellpadding="2" width="80%">
          <tr>
            <td colspan="2">
            	<input type="hidden" name="module" value="newsletter">
            	<?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?>
            </td>
          </tr>
          <?php if($filter): ?>
          <tr>
            <td class="main" valign="top">Подписчики:</td>
            <td class="main">
            	<?php if($orders) echo '<b>Кол-во заказов:</b> от '.$orders; ?><br>
            	<?php if($site) 
            	{
            		$site_query = "SELECT shops_id as id, shops_url as name 
					FROM ".TABLE_SHOPS."
					WHERE shops_id = ".$site.";";
					$site_result = tep_db_query($site_query);
					$site_name = tep_db_fetch_array($site_result);
					$site_name = $site_name['name'];
            		echo '<b>Сайт:</b> '.$site_name;
            	} ?><br>
            	<?php if($city) echo '<b>Город:</b> '.$city; ?><br>
            	<br>
            	<input type="hidden" name="orders" value="<?php echo $orders; ?>">
            	<input type="hidden" name="site" value="<?php echo $site; ?>">
            	<input type="hidden" name="cityName" value="<?php echo $city; ?>">
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td class="main"><?php echo TEXT_NEWSLETTER_TITLE; ?></td>
            <td class="main">
            	<input type="text" name="title" value="<?php echo $nInfo->title; ?>" size="75">
            	<span class="fieldRequired">* Обязательно</span>
            </td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_NEWSLETTER_CONTENT; ?></td>
            <td class="main"><?php //echo tep_draw_textarea_field('content', 'soft', '100%', '20', $nInfo->content); 
				/*$editor = new editor('content');
				$editor->Value = $nInfo->content;
				$editor->Height = '380';
				$editor->Width = '100%';
				$editor->Create();*/
			?>
			<style type="text/css">@import url('./includes/steditor/SimpleTextEditor.css');</style>
			<script src="./includes/steditor/SimpleTextEditor.js"></script>
			<input type="hidden" id="content1" name="content" value="">
			<textarea id="content" name="content"><?php echo $nInfo->content; ?></textarea>
			    <script>
				    var ste = new SimpleTextEditor("content", "ste");
				    ste.charset = 'iso-8859-1';
				    ste.init();
				</script>
            </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="right">
            	<?php echo substr((($form_action == 'insert') ? tep_image_submit('button_save.gif', IMAGE_SAVE) : tep_image_submit('button_update.gif', IMAGE_UPDATE)), 0, -2).' onclick="ste.submit();">'. '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . (isset($HTTP_GET_VARS['nID']) ? 'nID=' . $HTTP_GET_VARS['nID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } elseif ($action == 'preview') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);
?>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
      <tr>
        <td><tt><?php echo nl2br($nInfo->content); ?></tt></td>
      </tr>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } elseif ($action == 'send') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . 'lang/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php if ($module->show_choose_audience) { echo $module->choose_audience(); } else { echo $module->confirm(); } ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . 'lang/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php echo $module->confirm(); ?></td>
      </tr>
<?php
  }elseif ($action == 'statistic') {
  
  ?>
      <tr>
        <td>
        
        
        
        
        
<br><br>        
<table border="0" cellspacing="0" cellpadding="0">
	<tr valign="top">
		<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
		<td>
			<table border="0" width="400" cellspacing="0" cellpadding="0" class="columnLeft">
				<tr>
					<td>
						<?php
						  require('../includes/subscribe.php');
						  $subscribe_statistic = new subscribe_statistic;
						  
						  $statistic = $subscribe_statistic->get_main_statistic();
						  
						  $statistic_contents = '';
						  $statistic_contents .= 'Клиентов: ' . $statistic['clients'] . '<br>';
						  $statistic_contents .= 'Подписчиков: ' . $statistic['subsribers'] . '<br>';
						  $statistic_contents .= 'Подписчиков на новинки: ' . $statistic['news_subsribers'].' ('.$statistic['count'].')<br><br>';
						  
						  $static = array('today' => array('Сегодня', date('Y-m-d')),
						  'yesterday' => array('Вчера', date('Y-m-d', time()-24*60*60)),
						  'week' => array('За неделю', array(date('Y-m-d', time()-7*24*60*60), date('Y-m-d'))),
						  'month' => array('За месяц', array(date('Y-m-d', time()-30*24*60*60), date('Y-m-d')))
						  );
						  foreach($static as $k => $v)
						  {
							  $statistic = $subscribe_statistic->get_main_statistic($v[1]);
							  $statistic_contents .= $v[0].':<br>';
							  $statistic_contents .= '<div style="margin-left:20px;">Подписчиков: ' . $statistic['subsribers'] . '</div>';
							  $statistic_contents .= '<div style="margin-left:20px;">Подписчиков на новинки: ' . $statistic['news_subsribers'].' ('.$statistic['count'].')</div><br>';
						  }
						  
						
						  $heading = array();
						  $contents = array();
						
						  $heading[] = array('params' => 'class="infoBoxHeading"',
						                     'text'  => 'Статистика');
						
						  $contents[] = array('params' => 'class="infoBoxContent"',
						                      'text'  => $statistic_contents);
						
						  $box = new box;
						  echo $box->menuBox($heading, $contents);
						?>
					</td>
				</tr>
			</table>
		</td>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '1'); ?></td>
		<td>
			<table border="0" width="400" cellspacing="0" cellpadding="0" class="columnLeft">
				<tr>
					<td>
						<?php
							$statistic_contents = '';
							
							foreach($subscribe_statistic->types_array as $tid => $type)
							{
								$popular = $subscribe_statistic->get_popular_subsribe($tid + 1);
								if(count($popular) > 0)
								{
									$statistic_contents .= $type.': <br>
									<table cellspacing="0" cellpadding="0">';
									foreach($popular as $k => $v)
									{
										$statistic_contents .= '<tr class="sub">
										<td width="20"></td>
										<td width="300"><a href="'.tep_href_link(FILENAME_NEWSLETTERS, 'action=subscriptions&tcid='.$v['type_id'].':'.$v['category_id']).'">'.$v['name'].'</a></td>  
										<td>'.$v['count'].'</td></tr>';
									}
									$statistic_contents .= '</table><br>';
								}
							}
							
							$heading = array();
							$contents = array();
							
							$heading[] = array('params' => 'class="infoBoxHeading"',
							                 'text'  => 'Популярные подписки');
							
							$contents[] = array('params' => 'class="infoBoxContent"',
							                  'text'  => $statistic_contents);
							
							$box = new box;
							echo $box->menuBox($heading, $contents);
						?>
						<script>
						  $(".sub").mouseover(function() {
						    	$(this).css("background-color","#F0F1F1");
						  }).mouseout(function() {
						    	$(this).css("background-color","");
						  });
						
						</script>
						</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
        
           
        
        </td>
      </tr>
<?php
  
  }elseif ($action == 'subscribe') {
    
?>
      <tr>
        <td>
        
        
        
<?php
	$filter = '';
	$filter_params = '';
	if(isset($HTTP_GET_VARS['orders']) && $HTTP_GET_VARS['orders'] !== '')
	{ 
		$orders = (int)$HTTP_GET_VARS['orders'];
		$heving = 'HAVING COUNT(o.orders_id) >= '.$orders;
		$filter_params .= 'orders='.$orders.'&';
	}
	else $heving = '';
	if(isset($HTTP_GET_VARS['site']) && (int)$HTTP_GET_VARS['site'] > 0) 
	{
		$site = (int)$HTTP_GET_VARS['site'];
		$filter .= 'AND t.shops_id = '.$site;
		$filter_params .= 'site='.$site.'&';
	}
	if(isset($HTTP_GET_VARS['cityName']) && $HTTP_GET_VARS['cityName'] !== '') 
	{
		$city = addslashes($HTTP_GET_VARS['city']);
		$filter .= ' AND b.entry_city LIKE "%'.$city.'%"';
		$filter_params .= 'city='.$city.'&';
	}
	if(isset($HTTP_GET_VARS['sdate']) && $HTTP_GET_VARS['sdate'] !== '') 
	{
		$sdate = addslashes($HTTP_GET_VARS['sdate']);
		$filter .= ' AND ci.customers_info_date_account_created >= "'.$sdate.'"';
	}
	if(isset($HTTP_GET_VARS['edate']) && $HTTP_GET_VARS['edate'] !== '') 
	{
		$edate = addslashes($HTTP_GET_VARS['edate']);
		$filter .= ' AND ci.customers_info_date_account_created <= "'.$edate.'"';
	}
  //Запрос подписчиков
    $query = "SELECT c.customers_id as user_id, 
	c.customers_email_address as email,
	c.customers_firstname as firstname,
	c.customers_lastname as lastname,
	ci.customers_info_date_account_created as date_created,
	b.entry_city as city,
	t.shops_url as shop,
	count(o.orders_id) as total_orders
    from " . TABLE_CUSTOMERS . " c 
    JOIN ".TABLE_CUSTOMERS_INFO." AS ci ON ci.customers_info_id = c.customers_id
    LEFT JOIN ".TABLE_ADDRESS_BOOK." AS b ON b.address_book_id = c.customers_default_address_id
    LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id
    LEFT JOIN ".TABLE_ORDERS." AS o ON o.customers_id = c.customers_id
    WHERE c.customers_newsletter = '1'
    ".$filter."
    GROUP BY c.customers_id
    ".$heving."
    ORDER BY date_created DESC
    ";
	//Включаем пагинатор
    $paginator = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $query, $newsletters_query_numrows, true);
    $result = tep_db_query($query);
?>        
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td colspan="9" align="right">
	<form action="newsletters.php" method="GET">
		<table border="0" width="1050" cellspacing="0" cellpadding="2">
			<tr>
				<td class="smallText" valign="top" width="170">
					Кол-во заказов: от <select name="orders">
							<?php
								for($i = 0;$i < 10; $i++)
								{
									echo '<option '.($i == $orders?'selected ':'').'value="'.$i.'">
										 '.$i.'
										 </option>';
								}
								
									
							?>
						  </select>
				</td>
				<td class="smallText" align="right" valign="top">
					Сайт: <select name="site">
							<option value="">Все сайты</option>
							<?php
								$site_query = "SELECT shops_id as id, shops_url as name 
								FROM ".TABLE_SHOPS.";";
								$site_result = tep_db_query($site_query);
								while ($c = tep_db_fetch_array($site_result))
								{
									echo '<option '.($c['id'] == $site?'selected ':'').'value="'.$c['id'].'">
										 '.str_replace('http://www.', '', $c['name']).'
										 </option>';
								}
								
									
							?>
						  </select>
				</td>
				<td class="smallText" align="right" valign="top">
					Город: <input type="text" name="cityName" value="<?php echo $city; ?>" id="autoCity" class="autoComplite" />
					<input type="hidden" name="city" value="">
				</td>
				<td class="smallText" align="right" valign="top">
					Дата регистрации: <input type="text" name="sdate" value="<?php echo $sdate; ?>" id="sdatepicker">
					– <input type="text" name="edate" value="<?php echo $edate; ?>" id="edatepicker">
					<input type="hidden" name="action" value="subscribe">
				</td>
				<!--td class="smallText" valign="top">
					Категория: <input type="text" id="autoCategory" class="autoComplite" />
					<input type="hidden" name="category" value="">
				</td>
				<td class="smallText" valign="top">
					Серия: <input type="text" id="autoSeries" class="autoComplite" />
					<input type="hidden" name="series" value="">
				</td>
				<td class="smallText" valign="top">
					Автор: <input type="text" id="autoAuthor" class="autoComplite" />
					<input type="hidden" name="author" value="">
				</td>
				<td class="smallText" valign="top">
					Издательство: <input type="text" id="autoPublisher" class="autoComplite" />
					<input type="hidden" name="publisher" value="">
				</td>
			</tr-->
			<tr>
				<td class="smallText" valign="top" align="right" colspan="7">
					<input type="submit" name="filter" value="Применить">
				</td>
			</tr>
		</table>
	</form>
</td>
</tr><tr>
<td colspan="9">
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td class="smallText" valign="top">
				<?php echo $paginator->display_count($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					$HTTP_GET_VARS['page'], 
					TEXT_DISPLAY_NUMBER_OF_RECORDS);
				?>
			</td>
		</tr>
	</table>
</td>
</tr>
  <tr>
    <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" width="150">Имя</td>
        <td class="dataTableHeadingContent" align="right" width="100">Кол-во заказов</td>
        <td class="dataTableHeadingContent" align="left" width="30"></td>
        <td class="dataTableHeadingContent" align="left">Сайт</td>
        <td class="dataTableHeadingContent" align="left">Город</td>
        <td class="dataTableHeadingContent" align="left">Email</td>
        <td class="dataTableHeadingContent" align="left">Зарегистрировался</td>
        <td class="dataTableHeadingContent" align="center">Инфо</td>
      </tr>
<?php
    //Строим таблицу подписчиков
    while ($s= tep_db_fetch_array($result)) {
    if ((!isset($HTTP_GET_VARS['uid']) || (isset($HTTP_GET_VARS['uid']) && ($HTTP_GET_VARS['uid'] == $s['user_id']))) && !isset($Info)) 
    {
    	$Info = new objectInfo($s);
    }

	if (isset($Info) && is_object($Info) && ($s['user_id'] == $Info->user_id) ): ?>
<tr id="defaultSelected" 
	class="dataTableRowSelected" 
	onmouseover="rowOverEffect(this)" 
	onmouseout="rowOutEffect(this)" 
	onclick="document.location.href='<?php 
		echo tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&uid=' . $s['user_id'] . '&action=subscribe'); 
	?>'">
	<?php else: ?>
	<tr class="dataTableRow" 
	onmouseover="rowOverEffect(this)" 
	onmouseout="rowOutEffect(this)" 
	onclick="document.location.href='<?php
		echo tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&uid=' . $s['user_id'] . '&action=subscribe'); 
	?>'">
<?php endif; ?>
	<td class="dataTableContent">
		<a href="<?php tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&sid=' . $s['user_id'] . '&action=preview'); ?>">
			<?php echo tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW); ?></a>
		&nbsp;<?php echo $s['firstname'].' '.$s['lastname']; ?>
	</td>
	<td class="dataTableContent" align="right">
		<?php echo ($s['total_orders']?$s['total_orders']:'-'); ?>
	</td>
	<td class="dataTableContent" align="right"> </td>
	<td class="dataTableContent" align="left">
		<?php echo ($s['shop']?str_replace('http://www.', '',$s['shop']):'-'); ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo ($s['city']?$s['city']:'-'); ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo $s['email']; ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo tep_date_short($s['date_created']); ?>
	</td>
	<td class="dataTableContent" align="right">
		<?php if (isset($Info) && is_object($Info) && ($s['user_id'] == $Info->user_id) ): ?> 
			 <?php echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); ?>
		<?php else: ?>
		<a href="<?php tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'].'&uid='.$s['user_id'].'&action=subscribe') ?>">
			<?php echo tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO); ?></a> 
		<?php endif; ?>
			&nbsp;
	</td>
</tr>
<?php
    }
?>
<tr>
<td colspan="9">
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td class="smallText" valign="top">
				<?php echo $paginator->display_count($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					$HTTP_GET_VARS['page'], 
					TEXT_DISPLAY_NUMBER_OF_RECORDS);
				?>
			</td>
			<td class="smallText" align="right">
				<?php echo $paginator->display_links($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					MAX_DISPLAY_PAGE_LINKS, 
					$HTTP_GET_VARS['page'],
					tep_get_all_get_params(
						array('page', 'info', 'x', 'y', 'uid')
					));
				?>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'action=new&'.$filter_params) . '">' . tep_image_button('button_new_newsletter.gif', IMAGE_NEW_NEWSLETTER) . '</a>'; ?>
			</td>
		</tr>
	</table>
</td>
</tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

      $contents = array('form' => tep_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $nInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($nInfo)) {
        $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send') . '">' . tep_image_button('button_send.gif', IMAGE_SEND) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock') . '">' . tep_image_button('button_unlock.gif', IMAGE_UNLOCK) . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock') . '">' . tep_image_button('button_lock.gif', IMAGE_LOCK) . '</a>');
        }
        $contents[] = array('text' => '<br>' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . tep_date_short($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . tep_date_short($nInfo->date_sent));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table>
        
        
        
        
        
        </td>
      </tr>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  }elseif ($action == 'subscriptions') {
    
?>
      <tr>
        <td>
        
        
        
<?php
	require('../includes/subscribe.php');
	$subscribe_statistic = new subscribe_statistic;
	$filter = '';
	$filter_params = '';
	$group = '';
	$order = 'ORDER BY date_created DESC';
	if(isset($HTTP_GET_VARS['orders']) && $HTTP_GET_VARS['orders'] !== '')
	{ 
		$orders = (int)$HTTP_GET_VARS['orders'];
		$heving = 'HAVING COUNT(o.orders_id) >= '.$orders;
		$filter_params .= 'orders='.$orders.'&';
	}
	else $heving = '';
	if(isset($HTTP_GET_VARS['site']) && (int)$HTTP_GET_VARS['site'] > 0) 
	{
		$site = (int)$HTTP_GET_VARS['site'];
		$filter .= 'AND t.shops_id = '.$site;
		$filter_params .= 'site='.$site.'&';
	}
	if(isset($HTTP_GET_VARS['cityName']) && $HTTP_GET_VARS['cityName'] !== '') 
	{
		$city = addslashes($HTTP_GET_VARS['city']);
		$filter .= ' AND b.entry_city LIKE "%'.$city.'%"';
		$filter_params .= 'city='.$city.'&';
	}
	if(isset($HTTP_GET_VARS['sdate']) && $HTTP_GET_VARS['sdate'] !== '') 
	{
		$sdate = addslashes($HTTP_GET_VARS['sdate']);
		$filter .= ' AND s.date_created >= "'.$sdate.'"';
	}
	if(isset($HTTP_GET_VARS['edate']) && $HTTP_GET_VARS['edate'] !== '') 
	{
		$edate = addslashes($HTTP_GET_VARS['edate']);
		$filter .= ' AND s.date_created <= "'.$edate.'"';
	}
	if(isset($HTTP_GET_VARS['group']) && $HTTP_GET_VARS['group'] !== '') 
	{
		if($HTTP_GET_VARS['group'] == 'subscribe')
		{
			$group = 'GROUP BY category_id, type_id';
			$g_count = true;
		}
		if($HTTP_GET_VARS['group'] == 'client')
		{
			$group = 'GROUP BY user_id';
			$u_count = true;
		}
		if($g_count || $u_count)
		{
			$order = 'ORDER BY count DESC';
			$count = 'COUNT(*) as count,';
		}
	}
	if(isset($HTTP_GET_VARS['uid']) && $HTTP_GET_VARS['uid'] !== '') 
	{
		$uid = (int)$HTTP_GET_VARS['uid'];
		$filter .= ' AND c.customers_id = "'.$uid.'"';
	}
	if(isset($HTTP_GET_VARS['tcid']) && $HTTP_GET_VARS['tcid'] !== '') 
	{
		$tcid_array = explode(':', $HTTP_GET_VARS['tcid']);
		$tid = (int)$tcid_array[0];
		$cid = (int)$tcid_array[1];
		$filter .= ' AND s.type_id = "'.$tid.'"';
		$filter .= ' AND s.category_id = "'.$cid.'"';
	}
  //Запрос подписок
    $query = "SELECT 
    ".$count."
    c.customers_id as user_id, 
	c.customers_firstname as firstname,
	c.customers_lastname as lastname,
	s.category_id as category_id,
	s.type_id as type_id,
	t.shops_url as shop,
	s.date_created as date_create
    FROM " . TABLE_CUSTOMERS . " c 
    JOIN subscribe AS s ON s.user_id = c.customers_id
    LEFT JOIN ".TABLE_SHOPS." AS t ON t.shops_id = c.shops_id
    WHERE 1
    ".$filter."
    ".$group."
    ".$order."
    ";
    
	//Включаем пагинатор
    $paginator = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $query, $newsletters_query_numrows, true);
    $result = tep_db_query($query);
?>        
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
<td colspan="9" align="right">
	<form action="newsletters.php" method="GET">
		<table border="0" width="650" cellspacing="0" cellpadding="2">
			<tr>
				<td class="smallText" align="right" valign="top">
					Сайт: <select name="site">
							<option value="">Все сайты</option>
							<?php
								$site_query = "SELECT shops_id as id, shops_url as name 
								FROM ".TABLE_SHOPS.";";
								$site_result = tep_db_query($site_query);
								while ($c = tep_db_fetch_array($site_result))
								{
									echo '<option '.($c['id'] == $site?'selected ':'').'value="'.$c['id'].'">
										 '.str_replace('http://www.', '', $c['name']).'
										 </option>';
								}
								
									
							?>
						  </select>
				</td>
				<td class="smallText" align="right" valign="top">
					Дата подписки: <input type="text" name="sdate" value="<?php echo $sdate; ?>" id="sdatepicker">
					– <input type="text" name="edate" value="<?php echo $edate; ?>" id="edatepicker">
					<input type="hidden" name="action" value="subscriptions">
				</td>
			</tr>
			<tr>
				<td class="smallText" valign="top" align="right" colspan="3">
					<div style="margin-left: 20px; float: left;">Группировать: 
					<input type="radio" name="group" value="all" <?php if(!isset($_GET['group']) || $_GET['group'] == 'all') echo 'checked';?>> без групировки
				   	<input type="radio" name="group" value="subscribe" <?php if($_GET['group'] == 'subscribe') echo 'checked';?>> по подписке
				   	<input type="radio" name="group" value="client" <?php if($_GET['group'] == 'client') echo 'checked';?>> по клиенту
					</div>
					<input type="submit" name="filter" value="Применить">
				</td>
			</tr>
		</table>
	</form>
</td>
</tr><tr>
<td colspan="9">
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td class="smallText" valign="top">
				<?php echo $paginator->display_count($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					$HTTP_GET_VARS['page'], 
					TEXT_DISPLAY_NUMBER_OF_RECORDS);
				?>
			</td>
		</tr>
	</table>
</td>
</tr>
  <tr>
    <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" width="300">Клиент</td>
        <td class="dataTableHeadingContent" align="left">Подписался</td>
        <td class="dataTableHeadingContent" align="left">Сайт</td>
        <td class="dataTableHeadingContent" align="left">Дата подписки</td>
        <td class="dataTableHeadingContent" align="center">Инфо</td>
      </tr>
<?php
    //Строим таблицу подписчиков
    while ($s= tep_db_fetch_array($result)) {
    if ((!isset($HTTP_GET_VARS['uid']) || (isset($HTTP_GET_VARS['uid']) && ($HTTP_GET_VARS['uid'] == $s['user_id']))) && !isset($Info)) 
    {
    	$Info = new objectInfo($s);
    }
?>
	<tr class="dataTableRow">

	<td class="dataTableContent">
		<?php echo ($g_count?'<a href="'.tep_href_link(FILENAME_NEWSLETTERS, 'action=subscriptions&tcid='.$s['type_id'].':'.$s['category_id']).'">'.$s['count'].'</a>':'<a href="/admin/orders.php?cID='.$s['user_id'].'">'.$s['firstname'].' '.$s['lastname'].'</a>'); ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo ($u_count?'<a href="'.tep_href_link(FILENAME_NEWSLETTERS, 'action=subscriptions&uid='.$s['user_id']).'">'.$s['count'].'</a>':$subscribe_statistic->types_array[$s['type_id']-1].' "'.$subscribe_statistic->get_name_detail($s['category_id'], $s['type_id']).'"'); ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo ($s['shop']?str_replace('http://www.', '',$s['shop']):'-'); ?>
	</td>
	<td class="dataTableContent" align="left">
		<?php echo tep_date_short($s['date_create']); ?>
	</td>
	<td class="dataTableContent" align="right">
		<?php if (isset($Info) && is_object($Info) && ($s['user_id'] == $Info->user_id) ): ?> 
			 <?php echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); ?>
		<?php else: ?>
		<a href="<?php tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'].'&uid='.$s['user_id'].'&action=subscriptions') ?>">
			<?php echo tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO); ?></a> 
		<?php endif; ?>
			&nbsp;
	</td>
</tr>
<?php
    }
?>
<tr>
<td colspan="9">
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td class="smallText" valign="top">
				<?php echo $paginator->display_count($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					$HTTP_GET_VARS['page'], 
					TEXT_DISPLAY_NUMBER_OF_RECORDS);
				?>
			</td>
			<td class="smallText" align="right">
				<?php echo $paginator->display_links($newsletters_query_numrows, 
					MAX_DISPLAY_SEARCH_RESULTS, 
					MAX_DISPLAY_PAGE_LINKS, 
					$HTTP_GET_VARS['page'],
					tep_get_all_get_params(
						array('page', 'info', 'x', 'y', 'uid')
					));
				?>
			</td>
		</tr>
	</table>
</td>
</tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

      $contents = array('form' => tep_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $nInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($nInfo)) {
        $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send') . '">' . tep_image_button('button_send.gif', IMAGE_SEND) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock') . '">' . tep_image_button('button_unlock.gif', IMAGE_UNLOCK) . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock') . '">' . tep_image_button('button_lock.gif', IMAGE_LOCK) . '</a>');
        }
        $contents[] = array('text' => '<br>' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . tep_date_short($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . tep_date_short($nInfo->date_sent));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table>
        
        
        
        
        
        </td>
      </tr>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } elseif ($action == 'send') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . 'lang/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php if ($module->show_choose_audience) { echo $module->choose_audience(); } else { echo $module->confirm(); } ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . 'lang/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php echo $module->confirm(); ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm_send') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select newsletters_id, title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . 'lang/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" valign="middle"><?php echo tep_image(DIR_WS_IMAGES . 'ani_send_email.gif', IMAGE_ANI_SEND_EMAIL); ?></td>
            <td class="main" valign="middle"><strong><?php //echo TEXT_PLEASE_WAIT; ?></strong></td>
          </tr>
        </table></td>
      </tr>
<?php
  tep_db_query("update " . TABLE_NEWSLETTERS . " set date_sent = now(), status = '1' where newsletters_id = '" . tep_db_input($nID) . "'");
  //tep_set_time_limit(0);
  //flush();
  //$module->send($nInfo->newsletters_id);
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><font color="#ff0000"><strong>Письма поставлены в очередь на отправку!<br></strong></font>
        Статус отправки доступен в списке писем.
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } else {
  $status = array('', 'В очереди на отправку', 'Рассылается', 'Отправлено', 'Ошибка отправки');
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWSLETTERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="center" width="300">Статус</td>
                <td class="dataTableHeadingContent" align="center">Состояние</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $newsletters_query_raw = "select newsletters_id, title, length(content) as content_length, module, date_added, date_sent, status, locked from " . TABLE_NEWSLETTERS . " order by date_added desc";
    $newsletters_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $newsletters_query_raw, $newsletters_query_numrows);
    $newsletters_query = tep_db_query($newsletters_query_raw);
    while ($newsletters = tep_db_fetch_array($newsletters_query)) {
    if ((!isset($HTTP_GET_VARS['nID']) || (isset($HTTP_GET_VARS['nID']) && ($HTTP_GET_VARS['nID'] == $newsletters['newsletters_id']))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
        $nInfo = new objectInfo($newsletters);
      }

      if (isset($nInfo) && is_object($nInfo) && ($newsletters['newsletters_id'] == $nInfo->newsletters_id) ) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id'] . '&action=preview') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $newsletters['title']; ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($newsletters['content_length']) . ' bytes'; ?></td>
                <td class="dataTableContent" align="center"><?php if ($newsletters['status'] >= 1 && (int)$newsletters['status'] !== 4) { echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK); } else { echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS); } echo ' '.$status[$newsletters['status']]; ?></td>
                <td class="dataTableContent" align="center"><?php if ($newsletters['locked'] > 0) { echo tep_image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED); } else { echo tep_image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED); } ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($nInfo) && is_object($nInfo) && ($newsletters['newsletters_id'] == $nInfo->newsletters_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $newsletters_split->display_count($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_RECORDS); ?></td>
                    <td class="smallText" align="right"><?php echo $newsletters_split->display_links($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'action=new') . '">' . tep_image_button('button_new_newsletter.gif', IMAGE_NEW_NEWSLETTER) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

      $contents = array('form' => tep_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><strong>' . $nInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($nInfo)) {
        $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send') . '">' . tep_image_button('button_send.gif', IMAGE_SEND) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock') . '">' . tep_image_button('button_unlock.gif', IMAGE_UNLOCK) . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock') . '">' . tep_image_button('button_lock.gif', IMAGE_LOCK) . '</a>');
        }
        $contents[] = array('text' => '<br>' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . tep_date_short($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . tep_date_short($nInfo->date_sent));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
