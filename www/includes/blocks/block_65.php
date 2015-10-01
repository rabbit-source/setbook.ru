<?php
  $boxContent = '
  <form action="/subscribe.php" method="post">
  <b>E-mail</b>:<br> <input type="text" name="email" id="subscribe">
  <br />' . tep_image_submit('button_subscribe.gif', '', 'id="subscribe_button"') . '
  </form>';
  include(DIR_WS_TEMPLATES_BOXES . 'box.php');
?>