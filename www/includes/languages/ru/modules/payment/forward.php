<?php
  define('MODULE_PAYMENT_FORWARD_TEXT_TITLE', '���������� ��������');
  define('MODULE_PAYMENT_FORWARD_MAX_SUM_ERROR', '���� ������ ������ �������� ������ ��� ������� ���������� �� ����� %s');
  $forward_email_footer = '';
  if (DOMAIN_ZONE=='ua') $forward_email_footer = '��������: ��� ����� ����� ������������� �����������, ���� ���� ��������� �� ������ ��������� � ���� �� �������� �/��� ����������� ����� � ������� �Ш� ������� ����!';
  define('MODULE_PAYMENT_FORWARD_TEXT_EMAIL_FOOTER', $forward_email_footer);
?>