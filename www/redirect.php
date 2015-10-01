<?php
  require('includes/application_top.php');

  switch ($HTTP_GET_VARS['action']) {
    case 'banner':
      $banner_query = tep_db_query("select banners_url from " . TABLE_BANNERS . " where banners_id = '" . (int)$HTTP_GET_VARS['goto'] . "'");
      if (tep_db_num_rows($banner_query)) {
        $banner = tep_db_fetch_array($banner_query);
        tep_update_banner_click_count($HTTP_GET_VARS['goto']);

        tep_redirect($banner['banners_url']);
      }
      break;

    case 'manufacturer':
      if (isset($HTTP_GET_VARS['manufacturers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
        $manufacturer_query = tep_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
        if (tep_db_num_rows($manufacturer_query)) {
// url exists in selected language
          $manufacturer = tep_db_fetch_array($manufacturer_query);

          if (tep_not_null($manufacturer['manufacturers_url'])) {
            tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");

            tep_redirect($manufacturer['manufacturers_url']);
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $manufacturer_query = tep_db_query("select languages_id, manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . DEFAULT_LANGUAGE_ID . "'");
          if (tep_db_num_rows($manufacturer_query)) {
            $manufacturer = tep_db_fetch_array($manufacturer_query);

            if (tep_not_null($manufacturer['manufacturers_url'])) {
              tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$manufacturer['languages_id'] . "'");

              tep_redirect($manufacturer['manufacturers_url']);
            }
          }
        }
      }
      break;

	default:
    case 'url':
      if (isset($HTTP_GET_VARS['goto']) && tep_not_null($HTTP_GET_VARS['goto'])) {
        tep_redirect('http://' . $HTTP_GET_VARS['goto']);
      }
      break;
  }

  tep_redirect(tep_href_link(FILENAME_DEFAULT));
?>