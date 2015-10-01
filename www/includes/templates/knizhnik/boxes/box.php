<?php
  $minimize_block = false;
  if ( (basename(SCRIPT_FILENAME)==FILENAME_DEFAULT && empty($sPath_array) && ($iName=='index' || $iName=='')) || ($blocks['sort_order']==0) || empty($boxID) ) {
  } else {
	$minimize_block = true;
  }
  if ($minimize_block) {
	if (strpos($boxHeading, '<a href')!==false) $boxHeading = '<h2>' . preg_replace('/<a href="([^"]+)">/', '<a href="$1" onclick="document.getElementById(\'bl' . $blocks['blocks_id'] . '\').style.display = (document.getElementById(\'bl' . $blocks['blocks_id'] . '\').style.display==\'none\' ? \'block\' : \'none\'); return false;">', $boxHeading) . '</h2>';
	else $boxHeading = '<h2 onclick="document.getElementById(\'bl' . $blocks['blocks_id'] . '\').style.display = (document.getElementById(\'bl' . $blocks['blocks_id'] . '\').style.display==\'none\' ? \'block\' : \'none\');" style="cursor: pointer;">' . $boxHeading . '</h2>';
  }
?>
	  <div<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '"' : ''); ?>>
		<div class="columnblock">
		  <div class="contentheader"><?php echo (tep_not_null($boxHeading) ? $boxHeading : ''); ?></div>
		  <div class="contentbody"<?php if ($minimize_block) echo ' style="display: none;"'; ?> id="bl<?php echo $blocks['blocks_id']; ?>">
			<div class="description"<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '_content"' : ''); ?>>
<?php echo $boxContent; ?>
			</div>
		  </div>
		  <div class="contentfooter"></div>
		</div>
	  </div>
<?php
  $boxID = '';
  $boxHeading = '';
  $boxContent = '';
?>