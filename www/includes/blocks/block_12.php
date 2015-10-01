<?php
  if (defined('ADDITIONAL_DESCRIPTION') && tep_not_null(ADDITIONAL_DESCRIPTION)) {
?>
	<div class="page_description">
	  <div class="page_description_header"></div>
	  <div class="page_description_body"><?php echo ADDITIONAL_DESCRIPTION; ?></div>
	  <div class="page_description_footer"></div>
	</div>
<?php
  }
?>