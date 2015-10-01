<?php
  if (tep_not_null($boxID)) {
?>
<style type="text/css">
  #<?php echo $boxID; ?>_content { visibility: hidden; height: 0px; }
</style>
<?php
  }
?>
	  <div<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '"' : ''); ?>>
		<div class="contentblock">
		  <div class="contentheader"><?php echo (tep_not_null($boxHeading) ? '<h2>' . $boxHeading . '</h2>' : ''); ?></div>
		  <div class="contentbody">
			<div class="description"<?php echo (tep_not_null($boxID) ? ' id="' . $boxID . '_content"' : ''); ?>>
<?php echo $boxContent; ?>
			</div>
		  </div>
		  <div class="contentfooter"></div>
		</div>
	  </div>
<?php
  if (tep_not_null($boxID)) {
?>
<script language="javascript" type="text/javascript"><!--
  jQuery('#<?php echo $boxID; ?>').ready(function() {
	setTimeout(function() {
	  document.getElementById('<?php echo $boxID; ?>_content').style.visibility = "visible";
	  document.getElementById('<?php echo $boxID; ?>_content').style.height = "auto";
	}, 300);
  });
//--></script>
<?php
  }
  $boxID = '';
  $boxHeading = '';
  $boxContent = '';
?>