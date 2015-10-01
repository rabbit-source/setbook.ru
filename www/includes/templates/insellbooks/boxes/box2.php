	  <div<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '"' : ''); ?>>
		<div class="columnblock">
		  <div class="contentheader_red"><?php echo (tep_not_null($boxHeading) ? '<h2>' . $boxHeading . '</h2>' : ''); ?></div>
		  <div class="contentbody_red">
			<div class="description"<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '_content"' : ''); ?>>
<?php echo $boxContent; ?>
			</div>
		  </div>
		  <div class="contentfooter_red"></div>
		</div>
	  </div>
<?php
  $boxID = '';
  $boxHeading = '';
  $boxContent = '';
?>