<?php
define('HEADING_TITLE_CATALOG', '�������� ��������');
define('HEADING_TITLE_REPORT', '����� � ��������');

define('TEXT_SELECT_UPLOAD_TYPE', '�������� ��������:');
define('TEXT_UPLOAD_CATEGORIES', '���������� �����������');
define('TEXT_UPLOAD_SERIES', '���������� �����');
define('TEXT_UPLOAD_AUTHORS', '���������� �������');
define('TEXT_UPLOAD_MANUFACTURERS', '���������� �����������/��������������');
define('TEXT_UPLOAD_PRODUCTS', '���������� �������� ����');
define('TEXT_UPLOAD_OTHER_PRODUCTS', '���������� �������� ��������� �������');
define('TEXT_UPDATE_IMAGES', '�������� �������');

define('TEXT_UPLOAD_LAST_MODIFIED', '��������� ����������: %s');

define('TABLE_HEADING_MODEL', '���');
define('TABLE_HEADING_NAME', '������������');
define('TABLE_HEADING_STATUS', '������');

define('ERROR_UPLOAD_IN_PROCESS', '� ��������� ������ ����������� %s');
define('ERROR_NO_FILE_UPLOAD', "������! ���� �� ������: %s!\n");
define('SUCCESS_RECORDS_UPDATED', "����������� �������: %s, ���������: %s, ��������� �����: %s, �� ������� ��������: %s!\n");
define('WARNING_UPDATE_IN_PROGRESS', '������! ��������� ��������� ������� � ������� ������ ���������� �������� ����.');

define('TEXT_PRODUCT_UPDATED', '<font color="#00C600">��������</font>');
define('TEXT_PRODUCT_ADDED', '<font color="#0000ff">��������</font>');
define('TEXT_PRODUCT_NOT_ADDED', '<font color="#C61300">�� ��������</font>');

define('EMAIL_NOTIFICATION_SEPARATOR', '<font color="#A7A7A7">��������������������������������������������������</font>');

###########################################################################################

define('EMAIL_NOTIFICATION_SUBJECT_1', '%s - ����������� � ����������� ����� � �������');
define('EMAIL_NOTIFICATION_SUBJECT_2', '%s - ����������� � �������� ���� �� �����');
define('EMAIL_NOTIFICATION_BODY_1', '������������, %s!

���� �������� ���, ��� � ������� ��������� ����� �<a href="{{product_link}}">%s</a>�.

����� ������� �� �������� ������, �������������� ���������� ���� �������:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_BODY_2', '������������, %s!

���� �������� ��� � �������� ���� �� ����� �<a href="{{product_link}}">%s</a>�.

����� ������� �� �������� ������, �������������� ���������� ���� �������:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_WARNING_1', '<small>�� �������� ��� ������, ��� ��� ��������� �� ��������� ����������� � ����������� ������� ������ � ������� � ��������-�������� %s. ������ ���������� ����� ������� ��������.</small>');
define('EMAIL_NOTIFICATION_WARNING_2', '<small>�� �������� ��� ������, ��� ��� ��������� �� ��������� ����������� � �������� ���� �� ������ ����� � ��������-�������� %s. ������ ���������� ����� ������� ��������.</small>');

###########################################################################################

define('EMAIL_NOTIFICATION_SUBJECT_EN_1', '%s - notification of reception of book on sale');
define('EMAIL_NOTIFICATION_SUBJECT_EN_2', '%s - notification about a decrease in the price of the book');
define('EMAIL_NOTIFICATION_BODY_EN_1', 'Dear %s!

We are pleased to inform you that the book &quot;<a href="{{product_link}}">%s</a>&quot; is on sale.

To go to the product page, please use the link below:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_BODY_EN_2', 'Dear %s!

We are pleased to inform you about a decrease in the price on the book &quot;<a href="{{product_link}}">%s</a>&quot;.

To go to the product page, please use the link below:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_WARNING_EN_1', '<small>You got this letter because you have subscribed on receiving notifications about the receiption of this product on sale in %s. This alert is a one-off nature.</small>');
define('EMAIL_NOTIFICATION_WARNING_EN_2', '<small>You got this letter because you have subscribed on receiving notifications about a decrease in the price of this product in %s. This alert is a one-off nature.</small>');
?>
