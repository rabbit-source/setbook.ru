<?php
  echo $page['pages_description'];

  if (basename($origin_href)==FILENAME_CHECKOUT_SHIPPING) echo '<p>' . TEXT_ACCOUNT_CONTINUE . '</p>';
?>
	<div class="buttons">
	  <div style="text-align: right;"><?php echo '<a href="' . $origin_href . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
	</div>