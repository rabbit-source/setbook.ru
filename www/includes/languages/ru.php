<?php
// look in your $PATH_LOCALE/locale directory for available locales
// or type locale -a on the server.
// Examples:
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
//@setlocale(LC_ALL, 'ru_RU.CP1251');
@setlocale(LC_ALL, 'ru_RU.cp1251');

define('DATE_FORMAT_SHORT', '%d.%m.%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%e %B %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd.m.Y'); // this is used for date()
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
define('LANGUAGE_CURRENCY', 'RUB');

// Global entries for the <html> tag
define('HTML_PARAMS','dir="LTR" lang="ru"');

// charset for web pages and emails
define('CHARSET', 'windows-1251');

define('HOME_DOMAIN_INVITATION', '<p>��������� ����������! ���� �� ������ �������� ����� � ��������� � ����� ������ (<strong>%s</strong>), ���������, ����������, �� ������������ ���� SetBook: %s</p>');

define('HEADER_TITLE_SKYPE_BUTTON', '�� ������ ��������� � ���� �� Skype�');
define('HEADER_TITLE_SEARCH', '������');
define('HEADER_TITLE_ADVANCED_SEARCH', '����������� ����� &raquo;');
define('HEADER_TITLE_KEYBOARD', '����������:');
define('HEADER_TITLE_KEYBOARD_RU', '�������');
define('HEADER_TITLE_KEYBOARD_UA', '����������');
define('HEADER_TITLE_KEYBOARD_CLOSE', '������� [x]');

define('HEADER_TITLE_PHONE_NUMBER', (strlen(STORE_OWNER_PHONE_NUMBER)>16 ? '���.:' : '�������:'));

define('HEADER_TITLE_ACCOUNT_LOGIN', '����');
define('HEADER_TITLE_ACCOUNT_REGISTER', '�����������');
define('HEADER_TITLE_ACCOUNT_LOGOFF', '����� [X]');
define('HEADER_TITLE_ACCOUNT', '������ �������');
define('HEADER_TITLE_ACCOUNT_DISCOUNT', '���� ������:');

define('HEADER_TITLE_CALLBACK', '�������� ������');
define('HEADER_TITLE_CALLBACK_DESCRIPTION', '�� ���������� ��� �� <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'phone\');">�������</span> ��� <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'skype\');">skype</span>');
define('HEADER_TITLE_CALLBACK_COUNTRY', '������:');
define('HEADER_TITLE_CALLBACK_COUNTRY_CHANGE', '��������');
define('HEADER_TITLE_CALLBACK_REGION_CODE', '���&nbsp;����:');
define('HEADER_TITLE_CALLBACK_PHONE_NUMBER', '�������:');
define('HEADER_TITLE_CALLBACK_SKYPE_NUMBER', 'Skype:');
define('HEADER_TITLE_CALLBACK_ERROR_SKYPE', '������! �� �� ������� ���� ����� Skype!');
define('HEADER_TITLE_CALLBACK_ERROR_COUNTRY', '������! �� �� ������� ������!');
define('HEADER_TITLE_CALLBACK_ERROR_REGION_CODE', '������! �� �� ������� ��� ������/����!');
define('HEADER_TITLE_CALLBACK_ERROR_PHONE', '������! �� �� ������� ����� ��������!');

define('HEADER_TITLE_SHOPPING_CART', '���� �������');
define('HEADER_TITLE_SHOPPING_CART_PRODUCTS', '�������:');
define('HEADER_TITLE_SHOPPING_CART_SUM', '�� �����');
define('HEADER_TITLE_SHOPPING_CART_EMPTY', '������� ���');
define('HEADER_TITLE_SHOPPING_CART_CHECKOUT', '�������� ����� �');
define('HEADER_TITLE_POSTPONE_CART', '���������� ������');
define('HEADER_TITLE_POSTPONE_CART_PRODUCTS', '�������� �������:');
define('HEADER_TITLE_FOREIGN_CART', '����������� �����');
define('HEADER_TITLE_FOREIGN_CART_PRODUCTS', '����������� �����:');

define('LEFT_COLUMN_TITLE_SPECIALS', '����������, �������, ����');
define('LEFT_COLUMN_TITLE_REVIEWS', '������ � ��������');
define('LEFT_COLUMN_TITLE_FRAGMENTS', '����� � ���������');
define('LEFT_COLUMN_TITLE_HOLIDAY', '<span style="color: #FE0000;">�</span>' .
									'<span style="color: #FC9506;">�</span>' .
									'<span style="color: #01CB33;">�</span>' .
									'<span style="color: #0299FE;">�</span>' .
									'<span style="color: #6432C9;">�</span>' .
									'<span style="color: #FE0000;">�</span>' .
									'<span style="color: #FC9506;">�</span>' .
									'<span style="color: #01CB33;">�</span>' .
									'<span style="color: #0299FE;">�</span>' .
									'<span style="color: #6432C9;">�</span> ' .
									'<span style="color: #FE0000;">�</span>' .
									'<span style="color: #FC9506;">�</span>' .
									'<span style="color: #01CB33;">�</span>' .
									'<span style="color: #0299FE;">�</span>' .
									'<span style="color: #6432C9;">�</span>' .
									'<span style="color: #FE0000;">�</span>' .
									'<span style="color: #FC9506;">�</span>' .
									'<span style="color: #01CB33;">�</span>' .
									'<span style="color: #0299FE;">�</span>' .
									'<span style="color: #6432C9;">�</span>' .
									'<span style="color: #FE0000;">�</span>');
define('LEFT_COLUMN_TITLE_NEWS', '�������');
define('LEFT_COLUMN_TITLE_NEWS_BY_DATE', '�� ����');
define('LEFT_COLUMN_TITLE_NEWS_BY_CATEGORY', '�����, �������, ��������');

define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', '��� ������ ����� �������������');
define('BOX_MANUFACTURER_INFO_HOMEPAGE', '���� "%s"');

define('TEXT_RSS_SUBSCRIPTION', 'RSS-��������');

define('TEXT_MONTH_JANUARY', '������');
define('TEXT_MONTH_FEBRUARY', '�������');
define('TEXT_MONTH_MARCH', '����');
define('TEXT_MONTH_APRIL', '������');
define('TEXT_MONTH_MAY', '���');
define('TEXT_MONTH_JUNE', '����');
define('TEXT_MONTH_JULY', '����');
define('TEXT_MONTH_AUGUST', '������');
define('TEXT_MONTH_SEPTEMBER', '��������');
define('TEXT_MONTH_OCTOBER', '�������');
define('TEXT_MONTH_NOVEMBER', '������');
define('TEXT_MONTH_DECEMBER', '�������');

// text for gender
define('MALE', '�������');
define('FEMALE', '�������');
define('MALE_ADDRESS', '�-�');
define('FEMALE_ADDRESS', '�-��');

// text for date of birth example
define('DOB_FORMAT_STRING', 'dd.mm.yy');

define('TEXT_NO_NEWS', '�� ��������� ������ �������� ���');

// contact_us box text
define('ENTRY_CONTACT_US_TITLE', '�������� �����');
define('ENTRY_CONTACT_US', '���� ������ � ���������');
define('ENTRY_CONTACT_US_SUBJECT', '�������� ���� ���������:');
define('ENTRY_CONTACT_US_NAME', '���� ���:');
define('ENTRY_CONTACT_US_EMAIL', 'E-mail �����:');
define('ENTRY_CONTACT_US_PHONE_NUMBER', '���������� �������:');
define('ENTRY_CONTACT_US_IP_ADDRESS', 'IP-����� �����������:');
define('ENTRY_CONTACT_US_ENQUIRY', '���������:');
define('ENTRY_CONTACT_US_SUCCESS', '���� ��������� ���� ������� ���������� � ����� ������������ ������ ��������.');
define('ENTRY_CONTACT_US_EMAIL_SUBJECT', '��������� � �����');
define('ENTRY_CONTACT_US_FEEDBACK_EMAIL_SUBJECT', '�����������/����������� ��� �����������');

define('ENTRY_CAPTCHA_TITLE', '��������:');
define('ENTRY_CAPTCHA_TEXT', '������� ��������� �������������� ��������');

define('ENTRY_BLACKLIST_ORDER_ERROR', '<span class="errorText">� ���������, �� �� ������ �������� ����� �� ����� �����.</span>');
define('ENTRY_BLACKLIST_REQUEST_ERROR', '<span class="errorText">� ���������, �� �� ������ �������� ������ �� ����� �����.</span>');
define('ENTRY_BLACKLIST_BOARD_ERROR', '<span class="errorText">� ���������, �� �� ������ ��������� ���������� �� ����� �����.</span>');
define('ENTRY_BLACKLIST_REVIEW_ERROR', '<span class="errorText">� ���������, �� �� ������ ��������� ������ �� ����� �����.</span>');
define('ENTRY_BLACKLIST_CONTACT_US_ERROR', '<span class="errorText">� ���������, �� �� ������ ��������������� ������ �������� �����.</span>');

// request box text
define('ENTRY_REQUEST_FORM_TITLE', '������ �� ����� �����');
define('ENTRY_REQUEST_FORM_CONTACTS', '���������� ������');
define('ENTRY_REQUEST_FORM_TITLE_FOREIGN_PRODUCTS', '��������������� ������ �� �������� ������� ��-�� �������');
define('ENTRY_REQUEST_FORM_TITLE_FOREIGN_BOOKS', '��������������� ����� ����������� ����');
define('ENTRY_REQUEST_FORM', '������ ���� ��� ������');
define('ENTRY_REQUEST_FORM_PHONE_NUMBER', '���������� �������:');
define('ENTRY_REQUEST_FORM_COMMENTS', '�����������:');
define('ENTRY_REQUEST_FORM_NAME', '���� ���:');
define('ENTRY_REQUEST_FORM_EMAIL', 'E-mail:');
define('ENTRY_REQUEST_FORM_ADDRESS', '����� ��������:');
define('ENTRY_REQUEST_FORM_ADDRESS_TEXT', '(��� ������� ��������� ��������)');
define('ENTRY_REQUEST_FORM_PRODUCT_INFO', '���������� � %s-� ������');
define('ENTRY_REQUEST_FORM_BOOK_INFO', '���������� � %s-� �����');
define('ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED', '������ �������� ������ ������������������ �������������. ����������, <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">�������������</a> ��� <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">�����������������</a>! ����������� ������, �� ������� ������������� � ����� � ��� �� ����� ������.');
define('ENTRY_REQUEST_FORM_PRODUCT_TITLE', '������������ ������:');
define('ENTRY_REQUEST_FORM_BOOK_TITLE', '�������� �����:');
define('ENTRY_REQUEST_FORM_PRODUCT_AUTHOR', '�����:');
define('ENTRY_REQUEST_FORM_PRODUCT_CODE', '��� ����� (���� ������):');
define('ENTRY_REQUEST_FORM_PRODUCT_CODE_SHORT', '��� �����:');
define('ENTRY_REQUEST_FORM_PRODUCT_MODEL', '������/�������:');
define('ENTRY_REQUEST_FORM_BOOK_MODEL', 'ISBN:');
define('ENTRY_REQUEST_FORM_PRODUCT_MANUFACTURER', '�������������:');
define('ENTRY_REQUEST_FORM_BOOK_MANUFACTURER', '������������:');
define('ENTRY_REQUEST_FORM_PRODUCT_YEAR', '���:');
define('ENTRY_REQUEST_FORM_PRODUCT_URL', '�� ����� ���� ����� �� ����� (������� ������):');
define('ENTRY_REQUEST_FORM_BOOK_URL', '�� ����� ��� ����� �� ����� (������� ������ �� ��������):');
define('ENTRY_REQUEST_FORM_PRODUCT_URL_SHORT', '������ �� ����:');
define('ENTRY_REQUEST_FORM_PRODUCT_PRICE', '���� ������ �� �����:');
define('ENTRY_REQUEST_FORM_BOOK_PRICE', '���� ����� �� �����:');
define('ENTRY_REQUEST_FORM_PRODUCT_QTY', '���-��:');
define('ENTRY_REQUEST_FORM_PRODUCT_EXISTS', '����� ���� � �������� ��� ��������� �%s� �� ���� %s');
define('ENTRY_REQUEST_FORM_SUCCESS', '��� ������ ��� ������� ��������� � ����� ������������ ������ ��������.');
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT', '������ �� ����� ����� � ����� ' . STORE_NAME);
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_PRODUCTS', STORE_NAME . ' - ��������������� ������ �� ����������� ������ #%s');
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_BOOKS', STORE_NAME . ' - ��������������� ������ �� ����������� ����� #%s');
define('ENTRY_REQUEST_FORM_ERROR', '������! �� ��������� ������������ ����!');
define('ENTRY_REQUEST_FORM_CURRENCY', '- ������ - ');
define('ENTRY_REQUEST_FORM_CURRENCY_USD', '��������');
define('ENTRY_REQUEST_FORM_CURRENCY_EUR', '����');
define('ENTRY_REQUEST_FORM_CURRENCY_GBP', '������');
define('ENTRY_REQUEST_FORM_CURRENCY_RUR', '������');

// corporate box text
define('ENTRY_CORPORATE_FORM_TITLE', '�������� ��������');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_FILE', '�������� ��������������� �����');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_TEXT', '�������� ��������������� ������');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_OPTIONS', '����� ��������');
define('ENTRY_CORPORATE_FORM_CHOOSE_DOWNLOAD', '������������ �����-�����');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELDS', '�������� ����������� � ���� ����:');
define('ENTRY_CORPORATE_FORM_CHOOSE_SPECIALS', '�������� ���������������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS', '������� ������������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS_TEXT', '���� �� ������, ����� � ����� �������������� ����� ������ ������������ �����������, ����������� �� �������� �� ������ � ������');
define('ENTRY_CORPORATE_FORM_CHOOSE_CATEGORIES', '�������� �������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FILE', '�������� ����:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FILE_TEXT', '������� ������ ������ ("Browse") � ������� ����, ������� �� ����������� ��� ��������');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL', '����� �������, � ������� ������ ISBN:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL_TEXT', '������� ���������� ����� ������� � �����, � ������� ������� ISBN-���� ����');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY', '����� �������, � ������������ �����������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY_TEXT', '������� ���������� ����� ������� � �����, � ������� ������� ������������ ���������� ���� (��������� ������������ ������ �� �����, � ������� ���������� ������� �� ����)');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN', '������� ���� ISBN ���� �������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN_TEXT', '�� ������ ������������ ��� ���� ����� <span class="errorText">������ �������� �����</span>, ��� ����� ������� ����� ISBN-���� ������������ ���� (������ ��� � � ����� ������) � ����� ������ (��� ������ ���������) � �������� ���������� (� ���������� �� ������� ��� �������� � ����� �������)<br /><br />������ ����������:<pre>978-5-8475-0509-3	4' . "\n" . '978-5-17-050781-8	3' . "\n" . '978-5-17-059547-1	8' . "\n" . '978-5-699-31874-2	5</pre>');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT', '������, ������� ��� � �������:');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_SKIP', '����������');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_POSTPONE', '��������� � &laquo;���������� ������&raquo;');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS', '��������� � ����:');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ACTIVE', '������ �����, ��������� � �������');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ALL', '��� �����');
define('ENTRY_CORPORATE_FORM_CHOOSE_ANOTHER_METHOD', '�������� ���� �������� �� ��, ��� ��������� ��������� ����� �� ���������� ������ �����, � ���� ������ �� ������ ��������� ��� �� <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">����������� �����</a> � ���� ��������� ������� ������ ������������ ������� � ���� �������, ����� ���� �������� � ���� � �� ������� �������� �����.');
define('ENTRY_CORPORATE_FORM_PRODUCTS_CHOICE_ERROR', '������! �� ������� �� ������ ��������� ��� ������!');
define('ENTRY_CORPORATE_FORM_PRODUCTS_FOUND_ERROR', '������! �� ������� �� ������ ������!');
define('ENTRY_CORPORATE_FORM_NO_DATA_UPLOADED_ERROR', '������! �� ��������� ������!');
define('ENTRY_CORPORATE_FORM_UNKNOWN_FILE_UPLOADED_ERROR', '������! ����������� ������ �����!');
define('ENTRY_CORPORATE_FORM_SUCCESS_SKIP', '���� ������� ��������! ���������� %s �����, ��������� � ������� %s ������� (����� ����������� %s), ��������� (��� � �������) %s, �� ������� %s! ������� ������ "�������� �����", ����������� ��� ������� �������, ����� ���������� � ���������� ������. ������ ������� ������ �� ������� �� �������� ������������� ������ ������.');
define('ENTRY_CORPORATE_FORM_SUCCESS_POSTPONE', '���� ������� ��������! ���������� %s �����, ��������� � ������� %s ������� (����� ����������� %s), �������� (��� � �������) %s, �� ������� %s! ������� ������ "�������� �����", ����������� ��� ������� �������, ����� ���������� � ���������� ������. ������ ������� ������ �� ������� �� �������� ������������� ������ ������.');
define('ENTRY_CORPORATE_FORM_NO_MODELS_ERROR', '������! ���� ���������, �� � ��������� ������� �� ������� �� ������ ISBN-����!');

// pull down default text
define('PULL_DOWN_DEFAULT', '- �������� �� ������ -');
define('TYPE_BELOW', '������� ����');

// javascript messages
define('JS_ERROR', '������ ��� ���������� �����!\n\n��������� ����������:\n\n');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* �������� ����� ������ ��� ������ ������.\n');

define('JS_ERROR_SUBMITTED', '��� ����� ��� ���������. ��������� Ok.');

define('ERROR_NO_SHIPPING_MODULE_SELECTED', '��������, ����������, ������ ��������.');
define('ERROR_NO_PAYMENT_MODULE_SELECTED', '��������, ����������, ����� ������ ������.');

define('CATEGORY_COMPANY', '�������� � ��������');
define('CATEGORY_PERSONAL', '���� ������������ ������');
define('CATEGORY_ADDRESS', '����� ��������');
define('CATEGORY_CONTACT', '���������� ��������');
define('CATEGORY_OPTIONS', '��������');
define('CATEGORY_PASSWORD', '��� ������');
define('CATEGORY_FEEDBACK', '�����������');

define('ENTRY_COMPANY', '�������� ��������');
define('ENTRY_COMPANY_ERROR', '�� �� ������� �������� ��������.');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_COMPANY_FULL', '������ ������������ �����������');
define('ENTRY_COMPANY_FULL_ERROR', '�� �� ������� ������ ������������ �����������.');
define('ENTRY_COMPANY_FULL_TEXT', '');
if (DOMAIN_ZONE=='ru') {
  define('ENTRY_COMPANY_INN', '���');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', '��� �����');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='kz') {
  define('ENTRY_COMPANY_INN', '���');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', '��� ����� (���)');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='by') {
  define('ENTRY_COMPANY_INN', '���');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', '��� ����� (���)');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='ua') {
  define('ENTRY_COMPANY_INN', '���');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '����/������');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ��� ����/������');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', '��� �����');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'true');
} else {
  define('ENTRY_COMPANY_INN', '���');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', '��� �����');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'false');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
}
define('ENTRY_COMPANY_OGRN', '����');
define('ENTRY_COMPANY_OGRN_TEXT', '');
define('ENTRY_COMPANY_OKPO', '����');
define('ENTRY_COMPANY_OKPO_TEXT', '');
define('ENTRY_COMPANY_OKOGU', '�����');
define('ENTRY_COMPANY_OKOGU_TEXT', '');
define('ENTRY_COMPANY_OKATO', '�����');
define('ENTRY_COMPANY_OKATO_TEXT', '');
define('ENTRY_COMPANY_OKVED', '�����');
define('ENTRY_COMPANY_OKVED_TEXT', '');
define('ENTRY_COMPANY_OKFS', '����');
define('ENTRY_COMPANY_OKFS_TEXT', '');
define('ENTRY_COMPANY_OKOPF', '�����');
define('ENTRY_COMPANY_OKOPF_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_CORPORATE', '����������� �����');
define('ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_POST', '����� ��� ���������������');
define('ENTRY_COMPANY_ADDRESS_POST_TEXT', '');
define('ENTRY_COMPANY_TELEPHONE', '�������');
define('ENTRY_COMPANY_TELEPHONE_TEXT', '');
define('ENTRY_COMPANY_FAX', '����');
define('ENTRY_COMPANY_FAX_TEXT', '');
define('ENTRY_COMPANY_BANK', '����');
define('ENTRY_COMPANY_BANK_TEXT', '');
define('ENTRY_COMPANY_RS', '��������� ����');
define('ENTRY_COMPANY_RS_TEXT', '');
define('ENTRY_COMPANY_KS', '����������������� ����');
define('ENTRY_COMPANY_KS_TEXT', '');
define('ENTRY_COMPANY_GENERAL', '����������� ��������');
define('ENTRY_COMPANY_GENERAL_TEXT', '(������� � ��������)');
define('ENTRY_COMPANY_FINANCIAL', '������� ���������');
define('ENTRY_COMPANY_FINANCIAL_TEXT', '(������� � ��������)');

define('ENTRY_GENDER', '���:');
define('ENTRY_GENDER_ERROR', '�� �� ������� ���� ���.');
define('ENTRY_GENDER_TEXT', '');
define('ENTRY_CUSTOMER_TYPE', '�� ��������������� ���');
if (in_array(DOMAIN_ZONE, array('ru', 'by', 'ua', 'kz'))) {
  define('ENTRY_CUSTOMER_TYPE_PRIVATE', '���������� ����');
  define('ENTRY_CUSTOMER_TYPE_CORPORATE', '����������� ����');
} else {
  define('ENTRY_CUSTOMER_TYPE_PRIVATE', '������� ����');
  define('ENTRY_CUSTOMER_TYPE_CORPORATE', '������������� ��������');
}
define('ENTRY_FIRST_NAME', '���');
define('ENTRY_FIRST_NAME_ERROR', '�� �� ������� ��� ���.');
define('ENTRY_FIRST_NAME_TEXT', '');
define('ENTRY_MIDDLE_NAME', '��������');
define('ENTRY_MIDDLE_NAME_ERROR', '�� �� ������� ��� ��������.');
define('ENTRY_MIDDLE_NAME_TEXT', '');
define('ENTRY_LAST_NAME', '�������');
define('ENTRY_LAST_NAME_ERROR', '�� �� ������� ���� �������.');
define('ENTRY_LAST_NAME_TEXT', '');
define('ENTRY_DOB', '���� ��������');
define('ENTRY_DOB_ERROR', '�� �� ������� ���� ��������.');
define('ENTRY_DOB_CHECK_ERROR', '�������� ������ ���� �������� (������: 21.05.1970).');
define('ENTRY_DOB_TEXT', ' (� ������� 21.05.1970)');
define('ENTRY_EMAIL_ADDRESS', 'E-mail');
define('ENTRY_EMAIL_ADDRESS_ERROR', '�� �� ������� ���������� e-mail.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', '��� e-mail ����� ������ �����������, ���������� ��� ���.');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', '�������� ���� e-mail ��� ��������������� � ����� ��������, ���������� ������� ������ ����������� �����.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '');
define('ENTRY_STREET_ADDRESS', '������ �����');
define('ENTRY_STREET_ADDRESS_ERROR', '�� �� ������� �����.');
if (in_array(DOMAIN_ZONE, array('ru', 'by', 'ua', 'kz'))) {
  define('ENTRY_STREET_ADDRESS_TEXT', '� �������: �������� �����, ����� ����, ������ ���� (���� ����), ����� �������� (���� ����)<br />��������: ��. �������, �.1, �.4, ��.77');
} else {
  define('ENTRY_STREET_ADDRESS_TEXT', '');
}
define('ENTRY_SUBURB', '�����');
define('ENTRY_SUBURB_ERROR', '�� �� ������� �����.');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POSTCODE_ERROR', '�� �� ������� �������� ������ ������.');
define('ENTRY_POSTCODE_ERROR_1', '������ �������������� �������� ������.');
define('ENTRY_POSTCODE_TEXT', '');
define('ENTRY_CITY', '����� / ��������� �����');
define('ENTRY_CITY_ERROR', '�� �� ������� �����.');
define('ENTRY_CITY_TEXT', '');
if (DOMAIN_ZONE=='us') {
  define('ENTRY_POSTCODE', 'ZIP');
  define('ENTRY_STATE', '���� / ���������');
  define('ENTRY_STATE_ERROR', '�� �� ������� ����.');
 define('ENTRY_STATE_ERROR_SELECT', '�������� ���� / ���������.');
} else {
  define('ENTRY_POSTCODE', '�������� ������');
  define('ENTRY_STATE', '������');
  define('ENTRY_STATE_ERROR', '�� �� ������� ������.');
  define('ENTRY_STATE_ERROR_SELECT', '�������� ������.');
}
define('ENTRY_STATE_TEXT', '');
define('ENTRY_COUNTRY', '������');
define('ENTRY_COUNTRY_ERROR', '�������� ������.');
define('ENTRY_COUNTRY_TEXT', '<a href="' . tep_href_link('/delivery/international.html') . '">����� ������ ��� � ���� ������?</a>');
define('ENTRY_TELEPHONE_NUMBER', '���������� �������');
define('ENTRY_TELEPHONE_NUMBER_SHORT', '�������');
define('ENTRY_TELEPHONE_NUMBER_ERROR', '�� �� ������� ����� ����������� ��������.');
define('ENTRY_TELEPHONE_NUMBER_ERROR_1', '�� �� ������� ������������� ��� ��������.');
if (in_array(DOMAIN_ZONE, array('ua'))) {
  define('ENTRY_TELEPHONE_NUMBER_TEXT', '� �������: 050-111-11-11');
} else {
  define('ENTRY_TELEPHONE_NUMBER_TEXT', '');
}

define('ENTRY_DUMMY_EMAIL_ADDRESS', '���������� email');
define('ENTRY_DUMMY_EMAIL_ADDRESS_ERROR', '');
define('ENTRY_DUMMY_EMAIL_ADDRESS_TEXT', '��� �������������� � ���� ��������� ������');

define('ENTRY_SELF_DELIVERY_ADDRESS_ERROR', '�� �� ������� ����� ����������.');
define('ENTRY_FAX_NUMBER', '�������������� �������');
define('ENTRY_FAX_NUMBER_ERROR', '�� �� ������� ����� ��������������� ��������.');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER', '���� ������ ������� �������� � �������, ��������� ������, ���������� � �������� ������ ��������, �� ����������� �� �������������� ��������.');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_NEWSLETTER_YES', '��, � ���� ����������� �� �������� ��������');
define('ENTRY_NEWSLETTER_NO', '���������� �� ��������');
define('ENTRY_NEWSLETTER_ERROR', '');
define('ENTRY_WISHLIST', '');
define('ENTRY_PASSWORD', '������');
define('ENTRY_REMEMBER_ME', '��������� ����');
define('ENTRY_PASSWORD_ERROR', '��� ������ ������ ��������� ��� ������� ' . ENTRY_PASSWORD_MIN_LENGTH . ' ��������.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', '���� ������������ ������� ������ ��������� � ����� ��������.');
define('ENTRY_PASSWORD_TEXT', '');
define('ENTRY_PASSWORD_CONFIRMATION', '����������� ������');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '');
define('ENTRY_PASSWORD_CURRENT', '������� ������');
define('ENTRY_PASSWORD_CURRENT_TEXT', '');
define('ENTRY_PASSWORD_CURRENT_ERROR', '���� �������� ������ ��������� ��� ������� ' . ENTRY_PASSWORD_MIN_LENGTH . ' ��������.');
define('ENTRY_PASSWORD_NEW', '����� ������:');
define('ENTRY_PASSWORD_NEW_TEXT', '');
define('ENTRY_PASSWORD_NEW_ERROR', '��� ����� ������ ������ ��������� ��� ������� ' . ENTRY_PASSWORD_MIN_LENGTH . ' ��������.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', '���� ������������ ������� � ������ ������� ������ ���������.');
define('PASSWORD_HIDDEN', '--�����--');
define('ENTRY_AGREEMENT_ERROR', '�� �� �������, ��� �������� � ��������� ������������.');
define('ENTRY_CAPTCHA_CHECK_ERROR', '������ ������������ ����������� ���');
define('ENTRY_FEEDBACK', '�� ������ �������� ����� ���� ����������� ��� �����������, ������� ����� �� �������� ��� ������:');

define('FORM_REQUIRED_INFORMATION', '* ����������� ��� ����������');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', '��������:');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS', '������ <b>%d</b> - <b>%d</b> (�� <b>%d</b>)');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS_PER_PAGE', '�������� ��: %s');

define('PREVNEXT_TITLE_FIRST_PAGE', '������ ��������');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', '����������');
define('PREVNEXT_TITLE_NEXT_PAGE', '��������� ��������');
define('PREVNEXT_TITLE_LAST_PAGE', '��������� ��������');
define('PREVNEXT_TITLE_PAGE_NO', '�������� %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', '���������� %d �������');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', '��������� %d �������');
define('PREVNEXT_BUTTON_FIRST', '<');
define('PREVNEXT_BUTTON_PREV', '�');
define('PREVNEXT_BUTTON_NEXT', '�');
define('PREVNEXT_BUTTON_LAST', '>');

define('IMAGE_BUTTON_ADD', '��������');
define('IMAGE_BUTTON_ADD_ADDRESS', '�������� �����');
define('IMAGE_BUTTON_ADDRESS_BOOK', '�������� �����');
define('IMAGE_BUTTON_BACK', '�����');
define('IMAGE_BUTTON_BUY_NOW', '������ ������');
define('IMAGE_BUTTON_CALLBACK', '��������� ���');
define('IMAGE_BUTTON_CHANGE_ADDRESS', '�������� �����');
define('IMAGE_BUTTON_CHECKOUT', '�������� �����');
define('IMAGE_BUTTON_CONFIRM_ORDER', '����������� �����');
define('IMAGE_BUTTON_CONTINUE', '����������');
define('IMAGE_BUTTON_CONTINUE_SHOPPING', '���������� �������');
define('IMAGE_BUTTON_DELETE', '�������');
define('IMAGE_BUTTON_DETAILS', '���������');
define('IMAGE_BUTTON_EDIT_ACCOUNT', '�������� ������� ������');
define('IMAGE_BUTTON_HISTORY', '������� �������');
define('IMAGE_BUTTON_LOGIN', '�����');
define('IMAGE_BUTTON_IN_CART', '� �������');
define('IMAGE_BUTTON_IN_CART2', '� �������');
define('IMAGE_BUTTON_IN_CART3', '������� � �������');
define('IMAGE_BUTTON_IN_ORDER', '��������');
define('IMAGE_BUTTON_IN_ORDER2', '� ������');
define('IMAGE_BUTTON_INSERT', '��������');
define('IMAGE_BUTTON_PAY_FOR_ORDER', '�������� �����');
define('IMAGE_BUTTON_POSTPONE', '��������');
define('IMAGE_BUTTON_POSTPONE2', '�������');
define('IMAGE_BUTTON_POSTPONE3', '�������� ���������� ������');
define('IMAGE_BUTTON_QUICK_SEARCH', '������� �����');
define('IMAGE_BUTTON_QUICK_RESET', '��������');
define('IMAGE_BUTTON_REGISTER', '������������������');
define('IMAGE_BUTTON_RESET_CART', '�������� �������');
define('IMAGE_BUTTON_SEND', '���������');
define('IMAGE_BUTTON_SEARCH', '������');
define('IMAGE_BUTTON_UPDATE', '��������');
define('IMAGE_BUTTON_UPDATE_CART', '�����������');
define('IMAGE_BUTTON_WRITE_REVIEW', '�������� �����');

define('SMALL_IMAGE_BUTTON_DELETE', '�������');
define('SMALL_IMAGE_BUTTON_EDIT', '��������');
define('SMALL_IMAGE_BUTTON_VIEW', '��������');

define('ICON_ARROW_RIGHT', '�������');
define('ICON_CART', '� �������');
define('ICON_ERROR', '������');
define('ICON_SUCCESS', '���������');
define('ICON_WARNING', '��������');

define('TEXT_SORT_PRODUCTS', '����������� ');
define('TEXT_DESCENDINGLY', '�� ��������');
define('TEXT_ASCENDINGLY', '�� �����������');
define('TEXT_BY', ' �� ����: ');
define('TEXT_SORT_PRODUCTS_SHORT', '����������:');
define('TEXT_PER_PAGE', '������� �� ��������:');

define('TEXT_FILTER_PRODUCTS_SHORT', '����� � ������:');
define('TEXT_FILTER_PRODUCTS_RESET', '[�����]');

define('TEXT_YES', '��');
define('TEXT_NO', '���');

// votes box text
define('TEXT_REVIEW_BY', '� %s');
define('TEXT_REVIEW_OF', '�����/�������� ��');
define('TEXT_REVIEW_WORD_COUNT', '%s �����');
define('TEXT_REVIEW_RATING', '�������: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', '���� ����������: %s');
define('TEXT_NO_REVIEWS', '<p>� ���������� ������� ��� �������, �� ������ ����� ������.</p>');
define('TEXT_REVIEW_VOTES_OF', '%s �� %s');
define('TEXT_REVIEW_VOTE', '������? ���� ������:');
define('TEXT_REVIEW_VOTED', '������ ����������� (�������: %s):');
define('TEXT_REVIEW_SUCCESS_VOTED', '��� ����� ������� �����!');
define('TEXT_REVIEW_SUCCESS_ADDED', '��� ����� ������� ��������!');
define('ENTRY_REVIEWS', '������');
define('ENTRY_REVIEW_NAME', '���� ���:');
define('ENTRY_REVIEW_EMAIL', 'E-mail:');
define('ENTRY_REVIEW_TEXT', '��� ����� � �����:');
define('ENTRY_REVIEW_STARS', '���� ������:');
define('ENTRY_REVIEW_NAME_ERROR', '�� �� ������� ���� ���.');
define('ENTRY_REVIEW_EMAIL_ERROR', '�� �� ������� ���� e-mail.');
define('REVIEW_TEXT_MIN_LENGTH', '30');
define('ENTRY_REVIEW_TEXT_ERROR', '����� ������ ������ ������ ������ ���� �� ����� ' . REVIEW_TEXT_MIN_LENGTH . ' ��������.');
define('TEXT_REVIEW_REGISTER', '������ ����� ��������� ������ ������������������ ������������. ����������, <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">�������������</a> ��� <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">�����������������</a> (����������� ��������� � ��  ������� � ��� ����� ������).');

define('JS_REVIEW_NAME', '* �� �� ������� ���� ���.\n');
define('JS_REVIEW_EMAIL', '* �� �� ������� ���� e-mail.\n');
define('JS_REVIEW_TEXT', '* ���� "�����" ������ ��������� �� ����� ' . REVIEW_TEXT_MIN_LENGTH . ' ��������.\n');
define('JS_REVIEW_RATING', '* �������, ����������, ������� �� ����������� �����.\n');

define('TEXT_UNKNOWN_TAX_RATE', '��������� ������ ����������');

define('TEXT_REQUIRED', '<span class="errorText">�����������</span>');

define('ERROR_TEP_MAIL', '������: ���������� ��������� email ����� ������ SMTP. ���������, ����������, ���� ��������� php.ini � ���� ����������, �������������� ������ SMTP.</b></font>');
define('WARNING_INSTALL_DIRECTORY_EXISTS', '��������������: �� ������� ���������� ��������� ��������: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/install. ����������, ������� ��� ���������� ��� ������������.');
define('WARNING_CONFIG_FILE_WRITEABLE', '��������������: ���� ������������ �������� ��� ������: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php. ��� - ������������� ���� ������������ - ����������, ���������� ����������� ����� ������� � ����� �����.');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', '��������������: ���������� ������ �� ����������: ' . tep_session_save_path() . '. ������ �� ����� �������� ���� ��� ���������� �� ����� �������.');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', '��������������: ��� ������� � �������� ������: ' . tep_session_save_path() . '. ������ �� ����� �������� ���� �� ����������� ����������� ����� �������.');
define('WARNING_SESSION_AUTO_START', '��������������: ����� session.auto_start �������� - ����������, ��������� ������ ����� � ����� php.ini � ������������� ���-������.');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', '��������������: ���������� �����������: ' . DIR_FS_DOWNLOAD . '. �������� ����������.');

define('TEXT_CCVAL_ERROR_INVALID_DATE', '�� ������� �������� ���� ��������� ����� �������� ��������� ��������.<br />���������� ��� ���.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', '�� ������� �������� ����� ��������� ��������.<br />���������� ��� ���.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', '������ ����� ����� ��������� ��������: %s<br />���� �� ������� ����� ����� ��������� �������� ���������, �������� ���, ��� �� �� ��������� � ������ ������ ��� ��������� ��������.<br />���� �� ������� ����� ��������� �������� �������, ���������� ��� ���.');

define('ENTRY_GUEST_ADD_TO_CART_ERROR', '����� ����� ����������� ��������� ������ � �������, ����������, ������������� ��� �����������������!');

define('TABLE_HEADING_IMAGE', '');
define('TABLE_HEADING_PICTURE', '����');
define('TABLE_HEADING_NAME', '������������');
define('TABLE_HEADING_DESCRIPTION', '������� ��������');
define('TABLE_HEADING_MODEL', 'ISBN');
define('TABLE_HEADING_PRODUCTS', '������������');
define('TABLE_HEADING_MANUFACTURER', '��������');
define('TABLE_HEADING_MANUFACTURER_1', '�������������');
define('TABLE_HEADING_AUTHOR', '�����');
define('TABLE_HEADING_YEAR', '���');
define('TABLE_HEADING_TYPE', '��� ������');
define('TABLE_HEADING_QUANTITY', '<nobr>���-��</nobr>');
define('TABLE_HEADING_PRICE', '����');
define('TABLE_HEADING_SUM', '�����');
define('TABLE_HEADING_WEIGHT', '���');
define('TABLE_HEADING_BUY_NOW', '�����');
define('TABLE_HEADING_SUBTOTAL', '�����');
define('TABLE_HEADING_TOTAL', '�����');
define('TABLE_HEADING_REMOVE', '�������');

define('TEXT_NO_PRODUCTS', '��� �� ������ ������ � ���� �������.');
define('TEXT_NO_PRODUCTS2', '��� �� ������ ������ ������� ��������.');
define('TEXT_NO_NEW_PRODUCTS', '������� ������� ���.');
define('TEXT_NO_SPECIALS', '������� ��� ����������� �����������.');
define('TEXT_NO_SALES', '������� ��� ���������.');

define('TEXT_FROM', '��');
define('TEXT_TO', '��');

define('TEXT_PRODUCT_NOT_AVAILABLE', '������ ������: %s');
define('TEXT_PRODUCT_NOT_AVAILABLE_1', '������ ��� � �������');
define('TEXT_PRODUCT_NOT_AVAILABLE_SHORT', '��� � �������');
define('TEXT_PRODUCT_NOT_AVAILABLE_1_TEXT', '<p>� ��������� ������ ����� ��� �� �� ����� ������, �� � ����� �����������, ��:</p>	<ol style="margin-left: 15px; padding: 0px;">	<li>� ������ ������� ��� ����� ����� ��������� � �������, ������ �� ���� �� �������, ������� ����� � ������������ �� ��������� ���������������� �����������;</li>	<li><a href="/contacts.html">�������� ���</a> � �� ��������� �������� ������ ���� ����� (�� 1 ���.) ��� ���������� ����� ��, ��������� ��� ��������� � ��� �������;</li>	<li>�� ������ <a href="/boards/buy/?action=new&products_id=%s">���������� ���������� � �������</a> ���� ����� �� ����� ����� ����������;</li>	</ol>');
define('TEXT_PRODUCT_NOT_AVAILABLE_2', '����� � �������');
define('TEXT_NUMBER_OF_PRODUCTS', '���-��: ');
define('TEXT_MANUFACTURER', '������������:');
define('TEXT_MANUFACTURER_1', '�������������:');
define('TEXT_MODEL', 'ISBN:');
define('TEXT_MODEL_1', '�������:');
define('TEXT_NAME', '������������:');
define('TEXT_PRICE', '����:');
define('TEXT_CORPORATE_PRICE', '���� ����:');
define('TEXT_URL', '������:');
define('TEXT_GENRE', '����:');
define('TEXT_LANGUAGE', '����:');
define('TEXT_SERIE', '�����:');
define('TEXT_WARRANTY', '��������, �������:');
define('TEXT_AUTHOR', '�����:');
define('TEXT_AUTHORS', '������:');
define('TEXT_CODE', '��� ������ ��� ������ �� ��������: <strong class="errorText">%s</strong>');
define('TEXT_YEAR', '�.');
define('TEXT_YEAR_FULL', '���:');
define('TEXT_PERIODICITY', '�������������: %s ������� � ���');
define('TEXT_PERIODICITY_1', '�������������: %s ����� � ���');
define('TEXT_PERIODICITY_2', '�������������: %s ������ � ���');
define('TEXT_WEEK_1', '%s-� ������');
define('TEXT_WEEK_2', '%s-� ������');
define('TEXT_WEEK_3', '%s-� ������');
define('TEXT_WEEK', '%s-� ������');
define('TEXT_SUBSCRIBE_TO', '�������� ��');
define('TEXT_SUBSCRIBE_TO_SHORT', '��������');
define('TEXT_SUBSCRIBE_TO_1_MONTH', '1 �����');
define('TEXT_SUBSCRIBE_TO_3_MONTHES', '3 ������');
define('TEXT_SUBSCRIBE_TO_HALF_A_YEAR', '�������');
define('TEXT_SUBSCRIBE_TO_YEAR', '���');
define('TEXT_MONTHES', '���.');
define('TEXT_COVER', '�������:');
define('TEXT_FORMAT', '������:');
define('TEXT_ADDITIONAL_IMAGES_1', '���� ������� ��� ������������');
define('TEXT_WEIGHT', '���:');
define('TEXT_WEIGHT_GRAMMS', '�');
define('TEXT_WEIGHT_KILOGRAMMS', '��');
define('TEXT_PAGES_COUNT', '���-�� �������:');
define('TEXT_COPIES', '�����:');
define('TEXT_QTY', '����������');
define('TEXT_AVAILABLE_IN', '���� �������� � ������ ��������:');
define('TEXT_AVAILABLE_IN_FOREIGN', '���� ��������: %s ����.');
define('TEXT_BUY_NOW', '� �������');
define('TEXT_ALL_CATEGORY_PRODUCTS', '��� ������ �������');
define('TEXT_ALL_CATEGORIES', '- ��� ������� -');
define('TEXT_ALL_MANUFACTURERS', '-  ��� ������������ -');
define('TEXT_ALL_SERIES', '- ��� ����� -');
define('TEXT_ALL_AUTHORS', '- ��� ������ -');
define('TEXT_CUSTOMIZE_CATEGORY', '�������� �������:');
define('TEXT_RESET_SORTING', '[�����]');
define('TEXT_RESET_SORTING_TEXT', '�������� ������������� ����������');
define('TEXT_CUSTOMIZE_KEYWORD', '����� �� ������:');
define('TEXT_INPUT_KEYWORD', '');
define('TEXT_CLICK_TO_ENLARGE', '���������');
define('TEXT_CLOSE_WINDOW', '������� ����');

define('TEXT_CATEGORY_SUBSCRIBE', '����������� �� �������');
define('TEXT_CATEGORY_SUBSCRIBE_ALT', '���� ��� �������� %s, �����������!');
define('TEXT_CATEGORY_UNSUBSCRIBE', '���������� �� �������');
define('TEXT_CATEGORY_MESSAGE', '�� ��������� �� �������');
define('TEXT_CATEGORY_TYPE', '�������:�����:������:������������');
define('TEXT_CATEGORY_TYPE_ALT', '��������:�����:�����:������������');
define('TEXT_CATEGORY_ERROR', '����������� ����� ������ ������������������ ������������. ����������, <a href="%s"> �������������</a> ��� <a href="%s">�����������������</a>.');

define('TEXT_SUBSCRIBE_SECTION', '������� ������� ��������');
define('TEXT_SUBSCRIBE_SERIES', '������� �����');
define('TEXT_SUBSCRIBE_AURHORS', '������� �� ������');
define('TEXT_SUBSCRIBE_MUNUFACTURERS', '������� ������������');

define('SUCCESS_SUBSCRIBE', '��������� ���������');
?>