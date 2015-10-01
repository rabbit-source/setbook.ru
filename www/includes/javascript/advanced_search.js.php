<script language="javascript" src="<?php echo DIR_WS_CATALOG . DIR_WS_JAVASCRIPT; ?>general.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript"><!--
function check_form() {
  var error_message = "<?php echo JS_ERROR; ?>";
  var error_found = false;
  var error_field;
  var keywords = document.advanced_search.keywords.value;
  var categories_id = document.advanced_search.categories_id.options[document.advanced_search.categories_id.selectedIndex].value;
  var products_types_id = document.advanced_search.products_types_id.options[document.advanced_search.products_types_id.selectedIndex].value;
  var manufacturers_id = document.advanced_search.manufacturers_id.options[document.advanced_search.manufacturers_id.selectedIndex].value;
  var pfrom = document.advanced_search.pfrom.value;
  var pto = document.advanced_search.pto.value;
  var pfrom_float;
  var pto_float;

  if ( ((keywords == '') || (keywords.length < 1)) && ((categories_id == '') || (categories_id.length < 1)) && ((products_types_id == '') || (products_types_id.length < 1)) && ((manufacturers_id == '') || (manufacturers_id.length < 1)) && ((pfrom == '') || (pfrom.length < 1)) && ((pto == '') || (pto.length < 1)) ) {
    error_message = error_message + '* <?php echo ERROR_AT_LEAST_ONE_INPUT; ?>\n';
    error_field = document.advanced_search.keywords;
    error_found = true;
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + '* <?php echo ERROR_PRICE_FROM_MUST_BE_NUM; ?>\n';
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + '* <?php echo ERROR_PRICE_TO_MUST_BE_NUM; ?>\n';
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + '* <?php echo ERROR_PRICE_TO_LESS_THAN_PRICE_FROM; ?>\n';
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  }

  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    return true;
  }
}
//--></script>
