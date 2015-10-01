<script language="javascript" type="text/javascript"><!--
  function checkBoardForm(checkedType) {
	var error = false;
	var error_message = "";

	if (document.boards.customers_name.value == "") {
	  error_message = error_message + "<?php echo BOARDS_ERROR_NAME; ?>\n";
	  error = true;
	}

	if (document.boards.customers_email_address.value == "") {
	  error_message = error_message + "<?php echo BOARDS_ERROR_EMAIL_ADDRESS; ?>\n";
	  error = true;
	}

	if (checkedType=='adv') {
	  if (document.boards.customers_country.value == "") {
		error_message = error_message + "<?php echo BOARDS_ERROR_COUNTRY; ?>\n";
		error = true;
	  }

	  if (document.boards.customers_state.value == "") {
		error_message = error_message + "<?php echo BOARDS_ERROR_STATE; ?>\n";
		error = true;
	  }

	  if (document.boards.customers_city.value == "") {
		error_message = error_message + "<?php echo BOARDS_ERROR_CITY; ?>\n";
		error = true;
	  }

	  if (document.boards.boards_name.value == '') {
		error_message = error_message + "<?php echo BOARDS_ERROR_TITLE; ?>\n";
		error = true;
	  }

	  if (document.boards.boards_price.value == '') {
		error_message = error_message + "<?php echo BOARDS_ERROR_PRICE; ?>\n";
		error = true;
	  }
	} else if (checkedType=='app') {
	  if (document.boards.boards_description.value == '') {
		error_message = error_message + "<?php echo BOARDS_ERROR_COMMENTS; ?>\n";
		error = true;
	  }
	}

	if (error == true) {
	  alert(error_message);
	  return false;
	} else {
	  return true;
	}
  }
//--></script>