<?php
define('HEADING_TITLE', '�������');
define('HEADING_TITLE_SEARCH', '�����:');
define('HEADING_TITLE_SHOP', '�������:');

define('TEXT_ALL_SHOPS', '��� ��������');

define('TABLE_HEADING_FIRSTNAME', '���');
define('TABLE_HEADING_LASTNAME', '�������');
define('TABLE_HEADING_ACCOUNT_CREATED', '�����������������');
define('TABLE_HEADING_STATUS', '������');
define('TABLE_HEADING_INFO', '����');

define('TEXT_DATE_ACCOUNT_CREATED', '������ �������:');
define('TEXT_DATE_ACCOUNT_LAST_MODIFIED', '��������� ���������:');
define('TEXT_INFO_DATE_LAST_LOGON', '��������� ����:');
define('TEXT_INFO_NUMBER_OF_LOGONS', '���������� ������:');
define('TEXT_INFO_COUNTRY', '������:');
define('TEXT_INFO_NUMBER_OF_REVIEWS', '���������� �������:');
define('TEXT_DELETE_INTRO', '�� ������������� ������ ������� �������?');
define('TEXT_DELETE_REVIEWS', '������� %s �����(�)');
define('TEXT_INFO_HEADING_DELETE_CUSTOMER', '������� �������');
define('TYPE_BELOW', '������� ����');
define('PLEASE_SELECT', '�������� ���-�� ����');

define('ENTRY_CUSTOMER_STATUS', '�������� ������');

define('TEXT_DOWNLOAD_CUSTOMERS', '��������� ������ � ��������');

define('TEXT_YES', '��');
define('TEXT_NO', '���');

define('ENTRY_COMPANY', '�������� ��������:');
define('ENTRY_COMPANY_ERROR', '�� �� ������� �������� ��������.');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_COMPANY_TYPE_NAME', '��� ��������:');
define('ENTRY_COMPANY_TYPE_NAME_TEXT', '');
define('ENTRY_COMPANY_TAX_EXEMPT', '�������� ����������� �� ������ �������:');
define('ENTRY_COMPANY_TAX_EXEMPT_TEXT', '');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER', '���� ��, ����� ������������:');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER_TEXT', '');
define('ENTRY_COMPANY_FULL', '������ ������������ ��������:');
define('ENTRY_COMPANY_FULL_ERROR', '�� �� ������� ������ ������������ ��������.');
define('ENTRY_COMPANY_FULL_TEXT', '');
if (DOMAIN_ZONE=='ru') {
  define('ENTRY_COMPANY_INN', '���:');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���:');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='kz') {
  define('ENTRY_COMPANY_INN', '���:');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���:');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='by') {
  define('ENTRY_COMPANY_INN', '���:');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���:');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='ua') {
  define('ENTRY_COMPANY_INN', '���:');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '����/������:');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ��� ����/������');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'true');
} else {
  define('ENTRY_COMPANY_INN', '���:');
  define('ENTRY_COMPANY_INN_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', '���:');
  define('ENTRY_COMPANY_KPP_ERROR', '�� �� ������� ���');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'false');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
}
define('ENTRY_COMPANY_ADDRESS_CORPORATE', '����������� �����:');
define('ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_POST', '�������� �����:');
define('ENTRY_COMPANY_ADDRESS_POST_TEXT', '');
define('ENTRY_COMPANY_TELEPHONE', '�������:');
define('ENTRY_COMPANY_TELEPHONE_TEXT', '');
define('ENTRY_COMPANY_CORPORATE', '������������� ������');
define('ENTRY_COMPANY_FAX_TEXT', '');
?>