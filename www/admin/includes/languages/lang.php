<?php
// look in your $PATH_LOCALE/locale directory for available locales..
// on RedHat6.0 I used 'en_US'
// on FreeBSD 4.0 I use 'en_US.ISO_8859-1'
// this may not work under win32 environments..
@setlocale(LC_ALL, 'ru_RU.cp1251');

define('DATE_FORMAT_SHORT', '%d.%m.%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%e %B %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd.m.Y'); // this is used for date()
define('PHP_DATE_TIME_FORMAT', 'd.m.Y H:i:s'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('CALENDAR_DATE_FORMAT', 'dd.MM.yyyy');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
  }
}

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="ru"');

// charset for web pages and emails
define('CHARSET', 'windows-1251');

define('DEBUG_MODES_DISALLOW', '���������:');
define('DEBUG_MODES_DISALLOW_CREATE', '�������� �����������');
define('DEBUG_MODES_DISALLOW_MOVE', '�����������');
define('DEBUG_MODES_DISALLOW_EDIT', '��������������');
define('DEBUG_MODES_DISALLOW_DELETE', '��������');
define('DEBUG_MODES_DISALLOW_ALL', '��� ��������');

// page title
define('TITLE', '����������������� ' . STORE_NAME);

define('TEXT_CHOOSE', '- - �������� - -');
define('TEXT_CHOOSE_TEMPLATE', '�������� ������ ��������:');
define('TEXT_CHOOSE_BLOCKS', '�������� �������������� �����, ������� ����� ������������ �� ���� ��������:');
define('TEXT_BLOCK_NOT_NEEDED', '�� ���������� ���� ����');

// header text in includes/header.php
define('HEADER_TITLE_TOP', '�����������������');
define('HEADER_TITLE_SUPPORT_SITE', '���� ���������');
define('HEADER_TITLE_ONLINE_CATALOG', '�������');
define('HEADER_TITLE_ADMINISTRATION', '�������������');

// text for gender
define('MALE', '�������');
define('FEMALE', '�������');

// text for date of birth example
define('DOB_FORMAT_STRING', 'dd.mm.yyyy');

// content box
define('BOX_HEADING_CONTENT', '������� �����');
define('BOX_CONTENT_SECTIONS', '������� � ������');
define('BOX_CONTENT_NEWS', '������� � ������');
define('BOX_CONTENT_BLOCKS', '����� ����������');
define('BOX_CONTENT_PAGES', '�������� �����');
define('BOX_CONTENT_TEMPLATES', '������� �������');
define('BOX_CONTENT_REVIEWS', '������');
define('BOX_CONTENT_MESSAGES', '��������� �� ����');
define('BOX_CONTENT_BOARDS', '����������');
define('BOX_CONTENT_BLACKLIST', '������ ������');

// online catalog box
define('BOX_HEADING_CATALOG', '�������');
define('BOX_CATALOG_CATEGORIES', '��������� / ������');
define('BOX_CATALOG_UPDATES', '��������� ��������');
define('BOX_CATALOG_PARAMETERS', '���� ������� � ���������');
define('BOX_CATALOG_MANUFACTURERS', '������������');
define('BOX_CATALOG_AUTHORS', '������');
define('BOX_CATALOG_SERIES', '�����');
define('BOX_CATALOG_SPECIALS', '���������������');
define('BOX_CATALOG_EXPECTED', '��������� ������');
define('BOX_CATALOG_EXPECTED_PRODUCTS', '��������� ������');
define('BOX_CATALOG_FOREIGN_PRODUCTS', '����������� �����');
define('BOX_CATALOG_UPLOAD', '�������� �����-�����');
define('BOX_CATALOG_LINKS', '������� ������');

// orders box
define('BOX_HEADING_ORDERS', '������');
define('BOX_ORDERS_CUSTOMERS', '�������');
define('BOX_ORDERS_DISCOUNTS', '������');
define('BOX_ORDERS_ORDERS', '������');
define('BOX_ORDERS_ADVANCE_ORDERS', '����������� ������');

// partners box
define('BOX_HEADING_PARTNERS', '��������');
define('BOX_PARTNERS_PARTNERS', '��������');

// configuration box
define('BOX_HEADING_CONFIGURATION', '���������');
define('BOX_CONFIGURATION_SETTINGS', '���������');
define('BOX_CONFIGURATION_USERS', '������������');

// modules box
define('BOX_HEADING_MODULES', '������');
define('BOX_MODULES_PAYMENT', '������');
define('BOX_MODULES_PAYMENT_TO_GEOZONES', '������ � �������');
define('BOX_MODULES_SHIPPING', '��������');
define('BOX_MODULES_SHIPPING_TO_PAYMENT', '�������� � ������');
define('BOX_MODULES_SHIPPING_TO_GEOZONES', '�������� � �������');
define('BOX_MODULES_ORDER_TOTAL', '����� �����');

// localizaion box
define('BOX_HEADING_LOCALIZATION', '�����������');
define('BOX_LOCALIZATION_COUNTRIES', '������');
define('BOX_LOCALIZATION_ZONES', '�������');
define('BOX_LOCALIZATION_GEO_ZONES', '�������������� ����');
define('BOX_LOCALIZATION_SHOPS', '��������');
define('BOX_LOCALIZATION_SELF_DELIVERY', '������ ����������');
define('BOX_LOCALIZATION_CURRENCIES', '������');
define('BOX_LOCALIZATION_LANGUAGES', '�����');
define('BOX_LOCALIZATION_SUBJECTS', '���� ���������');
define('BOX_LOCALIZATION_ORDERS_STATUS', '������� �������');

// reports box
define('BOX_HEADING_REPORTS', '������');
define('BOX_REPORTS_VIEWED', '������������� ������');
define('BOX_REPORTS_PURCHASED', '���������� ������');
define('BOX_REPORTS_CUSTOMERS', '������ �������');

// tools box
define('BOX_HEADING_TOOLS', '�����������');
define('BOX_TOOLS_BACKUP', '��������� ����������� ��');
define('BOX_TOOLS_BANNERS', '�������� ��������');
define('BOX_TOOLS_FILE_MANAGER', '�������� ������');
define('BOX_TOOLS_MAIL', '������� e-mail');
define('BOX_TOOLS_NEWSLETTERS', '�������� �������� ��������');
define('BOX_TOOLS_WHOS_ONLINE', '��� � �������');

// taxes box
define('BOX_HEADING_TAXES', '����� / ������');
define('BOX_TAXES_TAX_CLASSES', '���� �������');
define('BOX_TAXES_TAX_RATES', '������ �������');

// javascript messages
define('JS_ERROR', '��� ���������� ����� �� ��������� ������!\n��������, ����������, ��������� �����������:\n\n');

define('JS_PRODUCTS_NAME', '* ��� ������ ������ ������ ���� ������� ������������\n');
define('JS_PRODUCTS_DESCRIPTION', '* ��� ������ ������ ������ ���� ������� ��������\n');
define('JS_PRODUCTS_PRICE', '* ��� ������ ������ ������ ���� ������� ����\n');
define('JS_PRODUCTS_WEIGHT', '* ��� ������ ������ ������ ���� ������ ���\n');
define('JS_PRODUCTS_QUANTITY', '* ��� ������ ������ ������ ���� ������� ����������\n');
define('JS_PRODUCTS_MODEL', '* ��� ������ ������ ������ ���� ������ ��� ������\n');
define('JS_PRODUCTS_IMAGE', '* ��� ������ ������ ������ ���� ��������\n');

define('JS_SPECIALS_PRODUCTS_PRICE', '* ��� ����� ������ ������ ���� ����������� ����� ����\n');

define('JS_GENDER', '* ���� ���� ������ ���� �������.\n');
define('JS_FIRST_NAME', '* ���� ����� ������ ���� ���������.\n');
define('JS_LAST_NAME', '* ���� ��������� ������ ���� ���������.\n');
define('JS_DOB', '* ���� ����� ��������� ������ ����� ������: ��.��.����.\n');
define('JS_EMAIL_ADDRESS', '* ���� �E-Mail ����� ������ ���� ���������.\n');
define('JS_ADDRESS', '* ���� ������ ������ ���� ���������.\n');
define('JS_POST_CODE', '* ���� ������� ������ ���� ���������.\n');
define('JS_CITY', '* ���� ������ ������ ���� ���������.\n');
define('JS_STATE', '* ���� ������� ������ ���� �������.\n');
define('JS_STATE_SELECT', '-- �������� ���� --');
define('JS_ZONE', '* ���� ������� ������ ��������������� �������� ������.');
define('JS_COUNTRY', '* ���� ������� ����� ���� ���������.\n');
define('JS_TELEPHONE', '* ���� �������� ������ ���� ���������.\n');
define('JS_FAX', '* ���� ����� ������ ���� ���������.\n');
define('JS_PASSWORD', '* ���� �������� � �������������� ������ ��������� � ��������� �� ����� ' . ENTRY_PASSWORD_MIN_LENGTH . ' ��������.\n');

define('JS_ORDER_DOES_NOT_EXIST', '����� ����� %s �� ������!');

define('CATEGORY_PERSONAL', '������������ ������');
define('CATEGORY_ADDRESS', '�����');
define('CATEGORY_CONTACT', '��� ��������');
define('CATEGORY_COMPANY', '���������� � ��������');
define('CATEGORY_OPTIONS', '��������');

define('ENTRY_GENDER', '���:');
define('ENTRY_GENDER_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_FIRST_NAME', '���:');
define('ENTRY_FIRST_NAME_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_LAST_NAME', '�������:');
define('ENTRY_LAST_NAME_ERROR', '&nbsp;<span class="errorText">����������</span>');
define('ENTRY_DATE_OF_BIRTH', '���� ��������:');
define('ENTRY_DATE_OF_BIRTH_ERROR', '&nbsp;<span class="errorText">(������ 21.05.1970)</span>');
define('ENTRY_EMAIL_ADDRESS', 'Email-�����:');
define('ENTRY_IP_ADDRESS', 'IP-�����:');
define('ENTRY_EMAIL_ADDRESS_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', '&nbsp;<span class="errorText">�� ����� �������� email �����!</span>');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', '&nbsp;<span class="errorText">������ email ����� ��� ���������������!</span>');
define('ENTRY_DISCOUNT', '������������ ������:');
define('ENTRY_DISCOUNT_TYPE', '��� ������:');
define('ENTRY_DISCOUNT_TYPE_DISCOUNT', '����� �� ��������� ���� �����');
define('ENTRY_DISCOUNT_TYPE_PURCHASE', '���� � ���������� ����');
define('ENTRY_COMPANY', '�������� ��������:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_STREET_ADDRESS', '�����:');
define('ENTRY_STREET_ADDRESS_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_SUBURB', '�����:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_POST_CODE', '������:');
define('ENTRY_POST_CODE_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_CITY', '�����:');
define('ENTRY_CITY_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_STATE', '������:');
define('ENTRY_STATE_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_COUNTRY', '������:');
define('ENTRY_COUNTRY_ERROR', '');
define('ENTRY_TELEPHONE_NUMBER', '�������:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', '&nbsp;<span class="errorText">�����������</span>');
define('ENTRY_FAX_NUMBER', '����:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_NEWSLETTER', '�������� ��������:');
define('ENTRY_NEWSLETTER_YES', '��������');
define('ENTRY_NEWSLETTER_NO', '�� ��������');
define('ENTRY_NEWSLETTER_ERROR', '');

// images
define('IMAGE_ANI_SEND_EMAIL', '��������� e-mail');
define('IMAGE_BACK', '�����');
define('IMAGE_BACKUP', '������� �����');
define('IMAGE_CANCEL', '��������');
define('IMAGE_CONFIRM', '�����������');
define('IMAGE_COPY', '����������');
define('IMAGE_COPY_TO', '���������� �');
define('IMAGE_DETAILS', '���������');
define('IMAGE_DOWNLOAD', '���������');
define('IMAGE_DELETE', '�������');
define('IMAGE_EDIT', '��������');
define('IMAGE_EMAIL', 'Email');
define('IMAGE_FILE_MANAGER', '�������� ������');
define('IMAGE_ICON_STATUS_GREEN', '��������');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', '��������������');
define('IMAGE_ICON_STATUS_YELLOW', '�� ���������');
define('IMAGE_ICON_STATUS_YELLOW_LIGHT', '������� �� ���������');
define('IMAGE_ICON_STATUS_RED', '����������');
define('IMAGE_ICON_STATUS_RED_LIGHT', '������� ����������');
define('IMAGE_ICON_INFO', '����������');
define('IMAGE_INSERT', '��������');
define('IMAGE_LOCK', '�����');
define('IMAGE_MODULE_INSTALL', '���������� ������');
define('IMAGE_MODULE_REMOVE', '������� ������');
define('IMAGE_MOVE', '�����������');
define('IMAGE_NEW_BANNER', '����� ������');
define('IMAGE_NEW_CATEGORY', '����� ���������');
define('IMAGE_NEW_LINK', '����� ������');
define('IMAGE_NEW_SECTION', '�������� ������');
define('IMAGE_NEW_RECORD', '�������� ������');
define('IMAGE_NEW_TYPE', '�������� ���');
define('IMAGE_NEW_BLOCK', '�������� ����');
define('IMAGE_NEW_COUNTRY', '����� ������');
define('IMAGE_NEW_CURRENCY', '����� ������');
define('IMAGE_NEW_FILE', '����� ����');
define('IMAGE_NEW_FOLDER', '����� �����');
define('IMAGE_NEW_LANGUAGE', '����� ����');
define('IMAGE_NEW_NEWSLETTER', '����� ������ ��������');
define('IMAGE_NEW_PRODUCT', '����� �����');
define('IMAGE_NEW_TAX_CLASS', '����� �����'); 
define('IMAGE_NEW_TAX_RATE', '����� ������ ������');
define('IMAGE_NEW_TAX_ZONE', '����� ��������� ����');
define('IMAGE_NEW_ZONE', '����� ����');
define('IMAGE_ORDERS', '������');
define('IMAGE_ORDERS_INVOICE', '����-�������');
define('IMAGE_ORDERS_PACKINGSLIP', '���������');
define('IMAGE_PREVIEW', '������������');
define('IMAGE_PACKINGSLIP', '�������� ������');
define('IMAGE_RESTORE', '������������');
define('IMAGE_RESET', '�����');
define('IMAGE_SAVE', '���������');
define('IMAGE_SEARCH', '������');
define('IMAGE_SELECT', '�������');
define('IMAGE_SEND', '���������');
define('IMAGE_SEND_EMAIL', '��������� e-mail');
define('IMAGE_UNLOCK', '��������������');
define('IMAGE_UPDATE', '��������');
define('IMAGE_UPDATE_CURRENCIES', '��������������� ����� �����');
define('IMAGE_UPLOAD', '���������');
define('IMAGE_UPLOAD_BACKUP', '��������� �� �����');

define('ICON_CROSS', '���������������');
define('ICON_CURRENT_FOLDER', '������� ����������');
define('ICON_DELETE', '�������');
define('ICON_ERROR', '������');
define('ICON_FILE', '����');
define('ICON_FILE_DOWNLOAD', '��������');
define('ICON_FOLDER', '�����');
define('ICON_LOCKED', '�������������');
define('ICON_PREVIOUS_LEVEL', '���������� �������');
define('ICON_PREVIEW', '������������');
define('ICON_STATISTICS', '����������');
define('ICON_SUCCESS', '���������');
define('ICON_TICK', '������');
define('ICON_UNLOCKED', '��������������');
define('ICON_WARNING', '��������');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', '�������� %s �� %d');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS', '�������� <strong>%d</strong> - <strong>%d</strong> (����� �������: <strong>%d</strong>)');

define('PREVNEXT_BUTTON_PREV', '����������');
define('PREVNEXT_BUTTON_NEXT', '���������');

define('TEXT_DEFAULT', '�� ���������');
define('TEXT_SET_DEFAULT', '���������� �� ���������');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* �����������</span>');

define('TEXT_ACCESS_DENIED', '&nbsp;<font color="#ff0000"><strong>� ��� ��� ���� ��� ������� � ���� ��������!</strong></font>');
define('TEXT_OPERATION_DENIED', '&nbsp;� ��� ��� ���� ��� ���������� ������ ��������!');

define('ERROR_NO_DEFAULT_CURRENCY_DEFINED', '������: � ���������� ������� �� ���� ������ �� ���� ����������� �� ���������. ����������, ���������� ���� �� ��� �: ����������� -> ������');
define('ERROR_NO_DEFAULT_LANGUAGE_DEFINED', '������: � ���������� ������� �� ���� ���� �� ���������� �� ���������. ����������, ���������� �: ����������� -> �����');

define('TEXT_CACHE_CATEGORIES', '���� ���������');
define('TEXT_CACHE_MANUFACTURERS', '���� �������');
define('TEXT_CACHE_ALSO_PURCHASED', '����� ������ �������'); 

define('TEXT_NONE', '--���--');
define('TEXT_TOP', '������');

define('TEXT_DEFAULT_SELECT', '--�������� �� ������--');
define('TEXT_NO_HTML', 'HTML-���� �� ��������������!');
define('TEXT_MAX_255', '�������� 255 ��������!');

define('ERROR_DESTINATION_DOES_NOT_EXIST', '������: ������� �� ����������.');
define('ERROR_DESTINATION_NOT_WRITEABLE', '������: ������� ������� �� ������, ���������� ����������� ����� �������.');
define('ERROR_FILE_NOT_SAVED', '������: ���� �� ��� ��������.');
define('ERROR_FILETYPE_NOT_ALLOWED', '������: ������ ���������� ����� ������� ����.');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', '���� ������� ��������.');
define('WARNING_NO_FILE_UPLOADED', '����� ����� �� ���������.');
define('WARNING_FILE_UPLOADS_DISABLED', '����� �������� ������ ��������� � ���������������� ����� php.ini.');

define('WARNING_IMAGES_LANGUAGES_DIRECTORY_NON_EXISTENT', '����������, � ������� ����� ���������� ������ ������, �� ����������: ' . DIR_FS_CATALOG_IMAGES . 'languages/.');
define('WARNING_IMAGES_LANGUAGES_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ����� ���������� ������ ������: ' . DIR_FS_CATALOG_IMAGES . 'languages/.');
define('WARNING_IMAGES_IMAGE_DIRECTORY_NON_EXISTENT', '����������, � ������� ������������ ����� ��������� �������, �� ����������: ' . DIR_FS_CATALOG_IMAGES . 'Image/.');
define('WARNING_IMAGES_IMAGE_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ������������ ����� ��������� �������: ' . DIR_FS_CATALOG_IMAGES . 'Image/.');
define('WARNING_IMAGES_FLASH_DIRECTORY_NON_EXISTENT', '����������, � ������� ������������ ����� ��������� ����-������, �� ����������: ' . DIR_FS_CATALOG_IMAGES . 'Flash/.');
define('WARNING_IMAGES_FLASH_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ������������ ����� ��������� ����-������: ' . DIR_FS_CATALOG_IMAGES . 'Flash/.');
define('WARNING_IMAGES_FILE_DIRECTORY_NON_EXISTENT', '����������, � ������� ������������ ����� ��������� �����, �� ����������: ' . DIR_FS_CATALOG_IMAGES . 'File/.');
define('WARNING_IMAGES_FILE_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ������������ ����� ��������� �����: ' . DIR_FS_CATALOG_IMAGES . 'File/.');
define('WARNING_IMAGES_MEDIA_DIRECTORY_NON_EXISTENT', '����������, � ������� ������������ ����� ��������� �����������-�����, �� ����������: ' . DIR_FS_CATALOG_IMAGES . 'Media/.');
define('WARNING_IMAGES_MEDIA_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ������������ ����� ��������� �����������-�����: ' . DIR_FS_CATALOG_IMAGES . 'Media/.');
define('WARNING_INCLUDES_TEMPLATES_DIRECTORY_NON_EXISTENT', '����������, � ������� ����� ���������� ������� �������, �� ����������: ' . DIR_FS_CATALOG_TEMPLATES . '.');
define('WARNING_INCLUDES_TEMPLATES_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ����� ���������� ������� �������: ' . DIR_FS_CATALOG_TEMPLATES . '.');
define('WARNING_INCLUDES_BLOCKS_DIRECTORY_NON_EXISTENT', '����������, � ������� ����� ���������� ����� ������ ����������, �� ����������: ' . DIR_FS_CATALOG_BLOCKS . '.');
define('WARNING_INCLUDES_BLOCKS_DIRECTORY_NOT_WRITEABLE', '��� ������� � ����������, � ������� ����� ���������� ����� ������ ����������: ' . DIR_FS_CATALOG_BLOCKS . '.');
?>