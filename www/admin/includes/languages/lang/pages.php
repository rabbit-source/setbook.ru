<?php
if (tep_not_null($HTTP_GET_VARS['pPath'])) {
  define('HEADING_TITLE', '�������������� ����, ������������ �� �������� "%s"');
} else {
  define('HEADING_TITLE', '�������� �����');
}
define('HEADING_TITLE_1', '%s - &laquo;%s&raquo;');

define('TABLE_HEADING_PAGES', '�������� �����');
define('TABLE_HEADING_ACTION', '��������');
define('TABLE_HEADING_INFO', '����');

define('TABLE_HEADING_TRANSLATION_DESCRIPTION', '��������');
define('TABLE_HEADING_TRANSLATION_KEY', '����');
define('TABLE_HEADING_TRANSLATION_VALUE', '����� �����');

define('TEXT_HEADING_EDIT_PAGE', '������������� ���������� ��������');
define('TEXT_INFO_HEADING_TRANSLATION', '���������� � �����');
define('TEXT_HEADING_NEW_TRANSLATION', '���������� ����� ����� (�����)');
define('TEXT_HEADING_EDIT_TRANSLATION', '�������������� ����� (�����)');
define('TEXT_HEADING_DELETE_TRANSLATION', '�������� �����');

define('TEXT_PAGES', '�������� �����:');
define('TEXT_PAGE_NAME', '�������� ��������:<br><small>(��� ������������� ������)</small>');
define('TEXT_PAGE_ADDITIONAL_DESCRIPTION', '�������������� ����� ��� ���������� ��������:<br /><small>(�������������)</small>');
define('TEXT_PAGE_DESCRIPTION', '����� �� ��������:<br /><small>(�������������)</small>');
define('TEXT_PAGE_FILENAME', '���� ��������:');

define('TEXT_DATE_ADDED', '���� ����������:');
define('TEXT_LAST_MODIFIED', '��������� ���������:');

define('TEXT_NEW_TRANSLATION_INTRO', '��������� ��� ���� ��� ���������� ����� �����');
define('TEXT_EDIT_TRANSLATION_INTRO', '������� ����� �������� �����');
define('TEXT_DELETE_TRANSLATION_INTRO', '�� ������������� ������ ������� �����');
define('TEXT_TRANSLATION_DESCRIPTION', '�������� �����:');
define('TEXT_SORT_ORDER', '������� ������:');
define('TEXT_TRANSLATION_KEY', '����:');
define('TEXT_TRANSLATION_VALUE', '����� �����:');

define('TEXT_SUCCESS_PAGES_ADDED', '��������� ����� �������� (%s)');
define('TEXT_SUCCESS_PAGES_DELETED', '������� �������� ����� (%s)');

define('ERROR_EMPTY_TRANSLATION_KEY', '������! �� ������ ����!');
define('ERROR_TRANSLATION_KEY_EXIST', '������! ��������� ���� (%s) ��� ������������! ����������, �������� ������!');
?>