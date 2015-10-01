<?php
  $max_description_length = 300;
  reset($rss_items);
  while (list(, $rss_item) = each($rss_items)) {
	$rss_description = $rss_item['description'];
	$rss_description = str_replace('<br />', "\n", $rss_description);
	$rss_description = str_replace('<p>', '', $rss_description);
	$rss_description = str_replace('</p>', "\n\n", $rss_description);
	$rss_description = str_replace('href="/', 'href="' . HTTP_SERVER . '/', $rss_description);
	while (strpos($rss_description, "\n\n")!==false) $rss_description = trim(str_replace("\n\n", "\n", $rss_description));
	$pieces = explode("\n", $rss_description);
	$rss_short_description = '';
	reset($pieces);
	while (list(, $piece) = each($pieces)) {
	  if (strlen($rss_short_description) > $max_description_length) break;
	  else $rss_short_description .= $piece . "\n";
	}
	$rss_short_description = nl2br(trim($rss_item['subtitle'] . "\n" . $rss_short_description));
	if (tep_not_null($rss_item['image']) && file_exists(DIR_FS_CATALOG . 'images/' . $rss_item['image'])) {
	  $rss_short_description = tep_image(HTTP_SERVER . DIR_WS_IMAGES . $rss_item['image'], $rss_item['title'], '', '', 'align="left" hspace="5"') . $rss_short_description . '<br clear="right" />';
	}
?>
  <item>
	<title><?php echo $rss_item['title']; ?></title>
	<link><?php echo $rss_item['link']; ?></link>
	<description><![CDATA[<?php echo $rss_short_description; ?>]]></description>
	<pubDate><?php echo $rss_item['date']; ?></pubDate>
	<guid isPermaLink="false"><?php echo $rss_item['link']; ?></guid>
  </item>
<?php
  }
?>