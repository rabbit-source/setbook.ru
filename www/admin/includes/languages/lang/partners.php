<?php
if (tep_not_null($HTTP_GET_VARS['pPath'])) {
  define('HEADING_TITLE', '������ �������� "%s"');
} else {
  define('HEADING_TITLE', '��������');
}

define('TABLE_HEADING_PARTNERS', '��������');
define('TABLE_HEADING_STATUS', '������');
define('TABLE_HEADING_BALANCE', '������');
define('TABLE_HEADING_COMISSION', '��������');
define('TABLE_HEADING_ACTION', '��������');
define('TABLE_HEADING_INFO', '����');
define('TABLE_HEADING_BALANCE_DATE', '����');
define('TABLE_HEADING_BALANCE_SUM', '�����');
define('TABLE_HEADING_BALANCE_COMMENTS', '�����������');

define('TEXT_PARTNER_NAME', '��� ��������:');
define('TEXT_PARTNER_LOGIN', '�����:');
define('TEXT_PARTNER_PASSWORD', '������:');
define('TEXT_PARTNER_PASSWORD_CONFIRMATION', '��������� ������:');
define('TEXT_PARTNER_COMISSION', '������ ������� ��������:');
define('TEXT_PARTNER_EMAIL_ADDRESS', '���������� e-mail:');
define('TEXT_PARTNER_URL', '����� �����:');
define('TEXT_PARTNER_BANK', '���������� ���������:');
define('TEXT_PARTNER_TELEPHONE', '���������� �������:');
define('TEXT_DATE_OF_LAST_LOGON', '���� ���������� �����:');
define('TEXT_PARTNER_NUMBER_OF_LOGONS', '����� ������:');

define('TEXT_HEADING_EDIT_PARTNER', '������������� ������ ��������');
define('TEXT_EDIT_PARTNER_INTRO', '�������� ����������� ������');
define('TEXT_INFO_HEADING_BALANCE', '���������� � �����������');
define('TEXT_HEADING_NEW_BALANCE', '���������� ������ �����������');
define('TEXT_HEADING_EDIT_BALANCE', '��������� �����');
define('TEXT_HEADING_DELETE_BALANCE', '�������� �����������');
define('TEXT_HEADING_DELETE_PARTNER', '������� ��������');

define('TEXT_DATE_ADDED', '���� ����������:');
define('TEXT_LAST_MODIFIED', '��������� ���������:');

define('TEXT_NEW_BALANCE_INTRO', '��������� ��� ���� ��� ���������� ������ �����������');
define('TEXT_EDIT_BALANCE_INTRO', '�������� ������ �����������');
define('TEXT_DELETE_BALANCE_INTRO', '�� ������������� ������ ������� �����������?');
define('TEXT_DELETE_PARTNER_INTRO', '�� ������������� ������ ������� ����� ��������?');
define('TEXT_BALANCE_SUM', '�����:');
define('TEXT_BALANCE_COMMENTS', '�����������:');
?>