<?php
// look in your $PATH_LOCALE/locale directory for available locales
// or type locale -a on the server.
// Examples:
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
//@setlocale(LC_ALL, 'ru_RU.CP1251');
@setlocale(LC_ALL, 'en_US.ISO_8859-1');

define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%B, %e %Y'); // this is used for strftime()
define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if (strpos($date, '.')) $date = preg_replace('/^(\d+).(\d+).(\d+)$/', '$2/$1/$3', $date);
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}

// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
define('LANGUAGE_CURRENCY', 'USD');

// Global entries for the <html> tag
define('HTML_PARAMS','dir="LTR" lang="ru"');

// charset for web pages and emails
define('CHARSET', 'windows-1251');

define('HOME_DOMAIN_INVITATION', '<p>Уважаемый посетитель! Если Вы хотите оформить заказ с доставкой в своей стране (<strong>%s</strong>), перейдите, пожалуйста, на региональный сайт SetBook: %s</p>');

define('HEADER_TITLE_SKYPE_BUTTON', 'Skype Us™!');
define('HEADER_TITLE_SEARCH', 'Search');
define('HEADER_TITLE_ADVANCED_SEARCH', 'Advanced search &raquo;');
define('HEADER_TITLE_ADVANCED_SEARCH_OWL', 'Advanced search');
define('HEADER_TITLE_KEYBOARD', '');
define('HEADER_TITLE_KEYBOARD_RU', 'click to open Russian keyboard');
define('HEADER_TITLE_KEYBOARD_RU_OWL', 'Russian keyboard');
define('HEADER_TITLE_KEYBOARD_UA', '');
define('HEADER_TITLE_KEYBOARD_CLOSE', 'close [x]');

define('HEADER_TITLE_PHONE_NUMBER', 'Call us:');

define('HEADER_TITLE_NOTE', 'Browse books');

define('HEADER_TITLE_ACCOUNT_LOGIN', 'Sign in');
define('HEADER_TITLE_ACCOUNT_REGISTER', 'Register');
define('HEADER_TITLE_ACCOUNT_LOGOFF', 'Logout [X]');
define('HEADER_TITLE_ACCOUNT_LOGOFF_OWL', 'Logout');
define('HEADER_TITLE_ACCOUNT', 'My account');
define('HEADER_TITLE_ACCOUNT_DISCOUNT', 'Personal discount:');

define('HEADER_TITLE_CALLBACK', 'Callback');
define('HEADER_TITLE_CALLBACK_DESCRIPTION', 'We\'ll call you on your <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'phone\');">phone</span> or <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'skype\');">Skype</span>');
define('HEADER_TITLE_CALLBACK_COUNTRY', 'Your&nbsp;country:');
define('HEADER_TITLE_CALLBACK_COUNTRY_CHANGE', 'edit');
define('HEADER_TITLE_CALLBACK_REGION_CODE', 'Area&nbsp;code:');
define('HEADER_TITLE_CALLBACK_PHONE_NUMBER', 'Your&nbsp;phone:');
define('HEADER_TITLE_CALLBACK_SKYPE_NUMBER', 'Skype:');
define('HEADER_TITLE_CALLBACK_ERROR_SKYPE', 'Warning! Please enter valid Skype number!');
define('HEADER_TITLE_CALLBACK_ERROR_COUNTRY', 'Warning! Please choose your country!');
define('HEADER_TITLE_CALLBACK_ERROR_REGION_CODE', 'Warning! Please enter your area code!');
define('HEADER_TITLE_CALLBACK_ERROR_PHONE', 'Warning! Please enter valid phone number!');

define('HEADER_TITLE_SHOPPING_CART', 'My shopping cart');
define('HEADER_TITLE_SHOPPING_CART_PRODUCTS', 'Products:');
define('HEADER_TITLE_SHOPPING_CART_PRODUCTS_OWL', 'Now in your cart %s items');
define('HEADER_TITLE_SHOPPING_CART_SUM', '');
define('HEADER_TITLE_SHOPPING_CART_EMPTY', 'empty');
define('HEADER_TITLE_SHOPPING_CART_EMPTY_OWL', 'Now in your cart 0 items');
define('HEADER_TITLE_SHOPPING_CART_CHECKOUT', 'CHECKOUT »');
define('HEADER_TITLE_POSTPONE_CART', 'My postponed products');
define('HEADER_TITLE_POSTPONE_CART_PRODUCTS', 'Postponed:');
define('HEADER_TITLE_FOREIGN_CART', 'Иностранные книги');
define('HEADER_TITLE_FOREIGN_CART_PRODUCTS', 'иностранные книги:');

define('LEFT_COLUMN_TITLE_SPECIALS', 'Sale, New, Best');
define('LEFT_COLUMN_TITLE_REVIEWS', 'Reviews');
define('LEFT_COLUMN_TITLE_FRAGMENTS', 'Books with previews');
define('LEFT_COLUMN_TITLE_NEWS', 'News');
define('LEFT_COLUMN_TITLE_NEWS_BY_DATE', 'By date');
define('LEFT_COLUMN_TITLE_NEWS_BY_CATEGORY', 'Actions, news, interviews');

define('LEFT_COLUMN_TITLE_SPECIALS', 'Special offers');
define('LEFT_COLUMN_TITLE_REVIEWS', 'Comments & reviews');
define('LEFT_COLUMN_TITLE_FRAGMENTS', 'Books with previews');
define('LEFT_COLUMN_TITLE_NEWS', 'News');

define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'Все товары этого производителя');
define('BOX_MANUFACTURER_INFO_HOMEPAGE', 'Сайт "%s"');

define('TEXT_RSS_SUBSCRIPTION', 'RSS subscription');

define('TEXT_MONTH_JANUARY', 'January');
define('TEXT_MONTH_FEBRUARY', 'February');
define('TEXT_MONTH_MARCH', 'March');
define('TEXT_MONTH_APRIL', 'April');
define('TEXT_MONTH_MAY', 'May');
define('TEXT_MONTH_JUNE', 'June');
define('TEXT_MONTH_JULY', 'july');
define('TEXT_MONTH_AUGUST', 'August');
define('TEXT_MONTH_SEPTEMBER', 'September');
define('TEXT_MONTH_OCTOBER', 'October');
define('TEXT_MONTH_NOVEMBER', 'November');
define('TEXT_MONTH_DECEMBER', 'December');

// text for gender
define('MALE', 'Male');
define('FEMALE', 'Female');
define('MALE_ADDRESS', 'Mr.');
define('FEMALE_ADDRESS', 'Ms.');

// text for date of birth example
define('DOB_FORMAT_STRING', 'mm/dd/yy');

define('TEXT_NO_NEWS', 'No news found');

// contact_us box text
define('ENTRY_CONTACT_US_TITLE', 'Feedback');
define('ENTRY_CONTACT_US', 'Contact information');
define('ENTRY_CONTACT_US_SUBJECT', 'Subject:');
define('ENTRY_CONTACT_US_NAME', 'Full name:');
define('ENTRY_CONTACT_US_EMAIL', 'E-mail address:');
define('ENTRY_CONTACT_US_PHONE_NUMBER', 'Phone number:');
define('ENTRY_CONTACT_US_IP_ADDRESS', 'IP-address:');
define('ENTRY_CONTACT_US_ENQUIRY', 'Enquiry:');
define('ENTRY_CONTACT_US_SUCCESS', 'Your message has been successfully sent.');
define('ENTRY_CONTACT_US_EMAIL_SUBJECT', 'Enquiry');
define('ENTRY_CONTACT_US_FEEDBACK_EMAIL_SUBJECT', 'Comments and Suggestions');

define('ENTRY_CAPTCHA_TITLE', 'Antispam:');
define('ENTRY_CAPTCHA_TEXT', 'enter the result of an arithmetic operation');

define('ENTRY_BLACKLIST_ORDER_ERROR', '<span class="errorText"> Unfortunately, you can\'t place an order on our website.</span>');
define('ENTRY_BLACKLIST_REQUEST_ERROR', '<span class="errorText">Unfortunately, you can\'t send an inquiry through our website.</span>');
define('ENTRY_BLACKLIST_BOARD_ERROR', '<span class="errorText">Unfortunately, you can\'t put an ad on our website.</span>');
define('ENTRY_BLACKLIST_REVIEW_ERROR', '<span class="errorText"> Unfortunately, you can\'t leave your comments on our website.</span>');
define('ENTRY_BLACKLIST_CONTACT_US_ERROR', '<span class="errorText">Unfortunately, you can\'t use our feedback form.</span>');

// pull down default text
define('PULL_DOWN_DEFAULT', '- Select -');
define('TYPE_BELOW', 'Type below');

// javascript messages
define('JS_ERROR', 'Ошибки при заполнении формы!\n\nИсправьте пожалуйста:\n\n');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* No payment method selected.\n');

define('JS_ERROR_SUBMITTED', 'Эта форма уже заполнена. Нажимайте Ok.');

define('ERROR_NO_SHIPPING_MODULE_SELECTED', 'Please select shipping method.');
define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Please select payment method.');

define('CATEGORY_COMPANY', 'Institution/Company details');
define('CATEGORY_PERSONAL', 'Personal details');
define('CATEGORY_ADDRESS', 'Address details');
define('CATEGORY_CONTACT', 'Contacts');
define('CATEGORY_OPTIONS', 'Subscription');
define('CATEGORY_PASSWORD', 'Password details');
define('CATEGORY_FEEDBACK', 'Feedback');

define('ENTRY_COMPANY', 'Institution/Company Name');
define('ENTRY_COMPANY_ERROR', 'Please specify company name.');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_COMPANY_FULL', 'Full company title');
define('ENTRY_COMPANY_FULL_ERROR', 'Please specify full company title.');
define('ENTRY_COMPANY_FULL_TEXT', '');

define('ENTRY_COMPANY_TYPE_NAME', 'Institution/Company Type');
define('ENTRY_COMPANY_TYPE_NAME_LIBRARY', 'Library');
define('ENTRY_COMPANY_TYPE_NAME_CORPORATION', 'Corporation');
define('ENTRY_COMPANY_TYPE_NAME_OTHER', 'Other');
define('ENTRY_COMPANY_TYPE_NAME_MIN_LENGTH', 'true');
define('ENTRY_COMPANY_TAX_EXEMPT', 'Tax-exempt');
define('ENTRY_COMPANY_TAX_EXEMPT_MIN_LENGTH', 'true');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER', 'If Yes, Tax Exempt Number');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER_MIN_LENGTH', 'false');

define('ENTRY_COMPANY_INN', 'ИНН');
define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
define('ENTRY_COMPANY_INN_TEXT', '');
define('ENTRY_COMPANY_KPP', 'КПП');
define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
define('ENTRY_COMPANY_KPP_TEXT', '');
define('ENTRY_COMPANY_BIK', 'БИК банка');
define('ENTRY_COMPANY_BIK_TEXT', '');
define('ENTRY_COMPANY_INN_MIN_LENGTH', 'false');
define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');

define('ENTRY_COMPANY_ADDRESS_CORPORATE', 'Corporate address');
define('ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_POST', 'Post address');
define('ENTRY_COMPANY_ADDRESS_POST_TEXT', '');
define('ENTRY_COMPANY_TELEPHONE', 'Telephone number');
define('ENTRY_COMPANY_TELEPHONE_TEXT', '');
define('ENTRY_COMPANY_FAX', 'Fax number');
define('ENTRY_COMPANY_FAX_TEXT', '');
define('ENTRY_COMPANY_BANK', 'Bank name');
define('ENTRY_COMPANY_BANK_TEXT', '');
define('ENTRY_COMPANY_RS', 'Account number');
define('ENTRY_COMPANY_RS_TEXT', '');

define('ENTRY_GENDER', 'Gender:');
define('ENTRY_GENDER_ERROR', 'Please select your gender');
define('ENTRY_GENDER_TEXT', '');
define('ENTRY_CUSTOMER_TYPE', 'Register as a');
define('ENTRY_CUSTOMER_TYPE_PRIVATE', 'Individual / Private Person');
define('ENTRY_CUSTOMER_TYPE_CORPORATE', 'Library/Institutional Representative');
define('ENTRY_FIRST_NAME', 'First name');
define('ENTRY_FIRST_NAME_ERROR', 'Please specify your first name.');
define('ENTRY_FIRST_NAME_TEXT', '');
define('ENTRY_MIDDLE_NAME', 'Middle name');
define('ENTRY_MIDDLE_NAME_ERROR', 'Please specify your middle name.');
define('ENTRY_MIDDLE_NAME_TEXT', '');
define('ENTRY_LAST_NAME', 'Last name');
define('ENTRY_LAST_NAME_ERROR', 'Please specify your last name.');
define('ENTRY_LAST_NAME_TEXT', '');
define('ENTRY_DOB', 'Date of birth');
define('ENTRY_DOB_ERROR', 'Please specify your date of birth.');
define('ENTRY_DOB_CHECK_ERROR', 'Your date of birth must be in this format (eg. 05/21/1970).');
define('ENTRY_DOB_TEXT', ' (eg. 05/21/1970)');
define('ENTRY_EMAIL_ADDRESS', 'E-mail address');
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Please specify e-mail address.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Your e-mail address does not appear to be valid - please make any necessary corrections.');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'Your e-mail address already exists in our records - please log in with the e-mail address or create an account with a different address.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '');
define('ENTRY_STREET_ADDRESS', 'Street address');
define('ENTRY_STREET_ADDRESS_ERROR', 'Please specify street address.');
define('ENTRY_STREET_ADDRESS_TEXT', '');
define('ENTRY_SUBURB', 'Suburb');
define('ENTRY_SUBURB_ERROR', 'Please specify suburb.');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POSTCODE_ERROR', 'Please specify postcode / ZIP.');
define('ENTRY_POSTCODE_ERROR_1', 'Your postcode / ZIP does not appear to be valid - please make any necessary corrections.');
define('ENTRY_POSTCODE_TEXT', '');
define('ENTRY_CITY', 'City');
define('ENTRY_CITY_ERROR', 'Please specify city.');
define('ENTRY_CITY_TEXT', '');
define('ENTRY_POSTCODE', 'Postcode / ZIP');
define('ENTRY_STATE', 'State / Province');
define('ENTRY_STATE_ERROR', 'Please specify state / province.');
define('ENTRY_STATE_ERROR_SELECT', 'Please select state / province.');
define('ENTRY_STATE_TEXT', '');
define('ENTRY_COUNTRY', 'Country');
define('ENTRY_COUNTRY_ERROR', 'Select country.');
define('ENTRY_COUNTRY_TEXT', '');
define('ENTRY_TELEPHONE_NUMBER', 'Telephone Number');
define('ENTRY_TELEPHONE_NUMBER_SHORT', 'Telephone');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Please specify telephone number.');
define('ENTRY_TELEPHONE_NUMBER_ERROR_1', 'Please specify international code of your telephone number.');
define('ENTRY_SELF_DELIVERY_ADDRESS_ERROR', 'Please select self-delivery office');
define('ENTRY_TELEPHONE_NUMBER_TEXT', '');
define('ENTRY_FAX_NUMBER', 'Additional telephone number');
define('ENTRY_FAX_NUMBER_ERROR', 'Please specify additional telephone number.');
define('ENTRY_FAX_NUMBER_TEXT', '');

define('ENTRY_DUMMY_EMAIL_ADDRESS', 'Please enter your email-address to receive notifications of your order fulfilment');
define('ENTRY_DUMMY_EMAIL_ADDRESS_ERROR', '');
define('ENTRY_DUMMY_EMAIL_ADDRESS_TEXT', '');

define('ENTRY_NEWSLETTER', '');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_NEWSLETTER_YES', 'Subscribe');
define('ENTRY_NEWSLETTER_NO', 'Unsubscribed');
define('ENTRY_WISHLIST', 'Please select');
define('ENTRY_NEWSLETTER_ERROR', '');
define('ENTRY_PASSWORD', 'Password');
define('ENTRY_REMEMBER_ME', 'remember me');
define('ENTRY_PASSWORD_ERROR', 'Your Password must contain a minimum of ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'The Password confirmation must match your Password.');
define('ENTRY_PASSWORD_TEXT', '');
define('ENTRY_PASSWORD_CONFIRMATION', 'Confirm password');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '');
define('ENTRY_PASSWORD_CURRENT', 'Current password');
define('ENTRY_PASSWORD_CURRENT_TEXT', '');
define('ENTRY_PASSWORD_CURRENT_ERROR', 'Your Password must contain a minimum of ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_NEW', 'New password:');
define('ENTRY_PASSWORD_NEW_TEXT', '');
define('ENTRY_PASSWORD_NEW_ERROR', 'Your new password musts contain at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' symbols.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'The Password confirmation must match your Password');
define('PASSWORD_HIDDEN', '--HIDDEN--');
define('ENTRY_AGREEMENT_ERROR', 'Вы не указали, что согласны с условиями договора.');
define('ENTRY_CAPTCHA_CHECK_ERROR', 'You\'ve entered invalid verification code');
define('ENTRY_FEEDBACK', 'We value your feedback. Please write any comments or suggestions you may have so we can improve your experience:');

define('FORM_REQUIRED_INFORMATION', '* Required information');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Result pages:');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS', 'Displaying <b>%d</b> - <b>%d</b> (of <b>%d</b>)');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS_PER_PAGE', 'Per page: %s');

define('PREVNEXT_TITLE_FIRST_PAGE', 'First Page');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'Previous Page');
define('PREVNEXT_TITLE_NEXT_PAGE', 'Next Page');
define('PREVNEXT_TITLE_LAST_PAGE', 'Last Page');
define('PREVNEXT_TITLE_PAGE_NO', 'Page %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Previous Set of %d Pages');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Next Set of %d Pages');
define('PREVNEXT_BUTTON_FIRST', '<');
define('PREVNEXT_BUTTON_PREV', '«');
define('PREVNEXT_BUTTON_NEXT', '»');
define('PREVNEXT_BUTTON_LAST', '>');

define('IMAGE_BUTTON_ADD', 'Add');
define('IMAGE_BUTTON_ADD_ADDRESS', 'Add address');
define('IMAGE_BUTTON_ADDRESS_BOOK', 'Address book');
define('IMAGE_BUTTON_BACK', 'Back');
define('IMAGE_BUTTON_BUY_NOW', 'Buy now');
define('IMAGE_BUTTON_CHANGE_ADDRESS', 'Change address');
define('IMAGE_BUTTON_CHECKOUT', 'Checkout');
define('IMAGE_BUTTON_CONFIRM_ORDER', 'Confirm order');
define('IMAGE_BUTTON_CONTINUE', 'Continue');
define('IMAGE_BUTTON_CONTINUE_SHOPPING', 'Continue shopping');
define('IMAGE_BUTTON_DELETE', 'Delete');
define('IMAGE_BUTTON_DETAILS', 'Details');
define('IMAGE_BUTTON_EDIT_ACCOUNT', 'Edit account');
define('IMAGE_BUTTON_HISTORY', 'Orders history');
define('IMAGE_BUTTON_LOGIN', 'Login');
define('IMAGE_BUTTON_IN_CART', 'Add to cart');
define('IMAGE_BUTTON_IN_CART2', 'In cart');
define('IMAGE_BUTTON_IN_CART3', 'View shopping cart');
define('IMAGE_BUTTON_IN_ORDER', 'Order');
define('IMAGE_BUTTON_IN_ORDER2', 'In order');
define('IMAGE_BUTTON_INSERT', 'Insert');
define('IMAGE_BUTTON_POSTPONE', 'Postpone');
define('IMAGE_BUTTON_POSTPONE2', 'Postponed');
define('IMAGE_BUTTON_POSTPONE3', 'View Your posponed products');
define('IMAGE_BUTTON_QUICK_SEARCH', 'Quick search');
define('IMAGE_BUTTON_QUICK_RESET', 'Reset');
define('IMAGE_BUTTON_REGISTER', 'Register');
define('IMAGE_BUTTON_RESET_CART', 'Reset cart');
define('IMAGE_BUTTON_SEND', 'Send');
define('IMAGE_BUTTON_SEARCH', 'Search');
define('IMAGE_BUTTON_UPDATE', 'Update');
define('IMAGE_BUTTON_UPDATE_CART', 'Update cart');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Write review');

define('SMALL_IMAGE_BUTTON_DELETE', 'Delete');
define('SMALL_IMAGE_BUTTON_EDIT', 'Edit');
define('SMALL_IMAGE_BUTTON_VIEW', 'View');

define('ICON_ARROW_RIGHT', 'More');
define('ICON_CART', 'In cart');
define('ICON_ERROR', 'Error');
define('ICON_SUCCESS', 'Success');
define('ICON_WARNING', 'Warning');

define('TEXT_SORT_PRODUCTS', 'Sort list ');
define('TEXT_DESCENDINGLY', 'descendingly');
define('TEXT_ASCENDINGLY', 'ascengingly');
define('TEXT_BY', ' by: ');
define('TEXT_SORT_PRODUCTS_SHORT', 'Sort by: ');
define('TEXT_PER_PAGE', 'Results per page:');

define('TEXT_FILTER_PRODUCTS_SHORT', 'Filter: ');
define('TEXT_FILTER_PRODUCTS_RESET', '[All products]');

define('TEXT_YES', 'Yes');
define('TEXT_NO', 'No');

define('TEXT_REVIEW_OF', 'review of');

define('TEXT_UNKNOWN_TAX_RATE', 'Unknown tax rate');

define('TEXT_REQUIRED', '<span class="errorText">Required</span>');

define('ERROR_TEP_MAIL', 'Ошибка: Невозможно отправить email через сервер SMTP. Проверьте, пожалуйста, Ваши установки php.ini и если необходимо, скорректируйте сервер SMTP.</b></font>');
define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Предупреждение: Не удалена директория установки магазина: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/install. Пожалуйста, удалите эту директорию для безопасности.');
define('WARNING_CONFIG_FILE_WRITEABLE', 'Предупреждение: Файл конфигурации доступен для записи: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php. Это - потенциальный риск безопасности - пожалуйста, установите необходимые права доступа к этому файлу.');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Предупреждение: директория сессий не существует: ' . tep_session_save_path() . '. Сессии не будут работать пока эта директория не будет создана.');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Предупреждение: Нет доступа к каталогу сессий: ' . tep_session_save_path() . '. Сессии не будут работать пока не установлены необходимые права доступа.');
define('WARNING_SESSION_AUTO_START', 'Предупреждение: опция session.auto_start включена - пожалуйста, выключите данную опцию в файле php.ini и перезапустите веб-сервер.');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Предупреждение: Директория отсутствует: ' . DIR_FS_DOWNLOAD . '. Создайте директорию.');

define('ENTRY_GUEST_ADD_TO_CART_ERROR', 'Чтобы иметь возможность добавлять товары в корзину, пожалуйста, авторизуйтесь или зарегистрируйтесь!');

define('TABLE_HEADING_IMAGE', '');
define('TABLE_HEADING_PICTURE', 'Pic');
define('TABLE_HEADING_NAME', 'Title');
define('TABLE_HEADING_DESCRIPTION', 'Description');
define('TABLE_HEADING_MODEL', 'ISBN');
define('TABLE_HEADING_PRODUCTS', 'Title');
define('TABLE_HEADING_MANUFACTURER', 'Publisher');
define('TABLE_HEADING_AUTHOR', 'Author');
define('TABLE_HEADING_YEAR', 'Year of publication');
define('TABLE_HEADING_TYPE', 'Type');
define('TABLE_HEADING_QUANTITY', 'Qty');
define('TABLE_HEADING_PRICE', 'Price');
define('TABLE_HEADING_SUM', 'Sum');
define('TABLE_HEADING_WEIGHT', 'Weight');
define('TABLE_HEADING_BUY_NOW', '<nobr>Buy now</nobr>');
define('TABLE_HEADING_SUBTOTAL', 'Subtotal');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_REMOVE', 'Remove');

define('TEXT_NO_PRODUCTS', 'No products found.');
define('TEXT_NO_PRODUCTS2', 'There are currently no products of this publisher.');
define('TEXT_NO_NEW_PRODUCTS', 'There are currently no new products.');
define('TEXT_NO_SPECIALS', 'There are currently no specials.');
define('TEXT_NO_SALES', 'There are currently no sales.');

define('TEXT_FROM', 'from');
define('TEXT_TO', 'to');

define('TEXT_PRODUCT_NOT_AVAILABLE', 'Sale starts on %s');
define('TEXT_PRODUCT_NOT_AVAILABLE_1', 'Not available now');
define('TEXT_PRODUCT_NOT_AVAILABLE_SHORT', 'Not available');
define('TEXT_PRODUCT_NOT_AVAILABLE_2', 'Available soon');
define('TEXT_NUMBER_OF_PRODUCTS', 'Qty: ');
define('TEXT_MANUFACTURER', 'Publisher:');
define('TEXT_MANUFACTURER_1', 'Manufacturer:');
define('TEXT_MODEL', 'ISBN:');
define('TEXT_MODEL_1', 'Code:');
define('TEXT_NAME', 'Title:');
define('TEXT_PRICE', 'Price:');
define('TEXT_CORPORATE_PRICE', 'Your price:');
define('TEXT_URL', 'Link:');
define('TEXT_GENRE', 'Genre:');
define('TEXT_LANGUAGE', 'Language:');
define('TEXT_SERIE', 'Serie:');
define('TEXT_AUTHOR', 'Author:');
define('TEXT_AUTHORS', 'Authors:');
define('TEXT_CODE', '');
//define('TEXT_CODE', 'Product ID for order by phone: <strong class="errorText">%s</strong>');
define('TEXT_YEAR', '');
define('TEXT_YEAR_FULL', 'Year:');
define('TEXT_PERIODICITY', 'Periodicity: %s issues per year');
define('TEXT_PERIODICITY_1', 'Periodicity: %s ussues per year');
define('TEXT_PERIODICITY_2', 'Periodicity: %s ussues per year');
define('TEXT_WEEK_1', '%sst week');
define('TEXT_WEEK_2', '%snd week');
define('TEXT_WEEK_3', '%srd week');
define('TEXT_WEEK', '%sth week');
define('TEXT_SUBSCRIBE_TO', 'Subscribe for');
define('TEXT_SUBSCRIBE_TO_SHORT', 'Subscription');
define('TEXT_SUBSCRIBE_TO_1_MONTH', '1 month');
define('TEXT_SUBSCRIBE_TO_3_MONTHES', '3 months');
define('TEXT_SUBSCRIBE_TO_HALF_A_YEAR', 'half-year');
define('TEXT_SUBSCRIBE_TO_YEAR', 'year');
define('TEXT_MONTHES', 'mon.');
define('TEXT_COVER', 'Cover:');
define('TEXT_FORMAT', 'Format:');
define('TEXT_ADDITIONAL_IMAGES_1', 'You can look through previews');
define('TEXT_WEIGHT', 'Weight:');
define('TEXT_WEIGHT_GRAMMS', 'g');
define('TEXT_WEIGHT_KILOGRAMMS', 'kg');
define('TEXT_PAGES_COUNT', 'Pages count:');
define('TEXT_COPIES', 'Copies:');
define('TEXT_QTY', 'Qty');
define('TEXT_AVAILABLE_IN', 'Дата передачи в службу доставки:');
define('TEXT_AVAILABLE_IN_FOREIGN', 'Срок доставки: %s дней.');
define('TEXT_BUY_NOW', 'Buy now');
define('TEXT_ALL_CATEGORY_PRODUCTS', 'View all products');
define('TEXT_ALL_CATEGORIES', '- All categories -');
define('TEXT_ALL_MANUFACTURERS', '-  All publishers -');
define('TEXT_ALL_SERIES', '- All series -');
define('TEXT_ALL_AUTHORS', '- All authors -');
define('TEXT_CUSTOMIZE_CATEGORY', 'Select category:');
define('TEXT_RESET_SORTING', '[Reset]');
define('TEXT_RESET_SORTING_TEXT', 'Reset sorting');
define('TEXT_CUSTOMIZE_KEYWORD', 'Search in listed products:');
define('TEXT_INPUT_KEYWORD', '');
define('TEXT_CLICK_TO_ENLARGE', 'Enlarge');
define('TEXT_CLOSE_WINDOW', 'Close window');
?>