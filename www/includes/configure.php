<?php
// ����� ����� (�� ������� �� ����� "/" !!!)
  define('HTTP_SERVER', 'http://www.setbook.ru');
  define('HTTPS_SERVER', 'https://www.setbook.ru');

// ���� � ����������, � ������� ����� ���������� �������, ������������ ����� ����� (������ ���������� � ������������� �������� "/")
  define('DIR_WS_CATALOG', '/');

// ���������� ���� � ���������� �� �������, � ������� ����������� ������� �����
  define('DIR_FS_CATALOG', '/var/www/2009/');

// ����������� ����������, � ������� ����� ���������� ��������-�������, ������������ DIR_WS_CATALOG (��. ����)
  define('DIR_WS_ONLINE_STORE', 'books/');

// ���� � ����������, � ������� ����� ���������� ������ �����������������, ������������ DIR_WS_CATALOG (��. ����)
  define('DIR_WS_ADMIN_PART', 'admin/');

// ��������� ���������� � ����� ������
  define('DB_SERVER', '192.168.0.9'); // ����� ������� MySQL
  define('DB_SERVER_USERNAME', 'setbook2009'); // ��� ������������ ��
  define('DB_SERVER_PASSWORD', 'Stbkr2009'); // ������ ������������
  define('DB_DATABASE', 'setbook_ru'); // ��� ���� ������

// Cookie � ������
  define('HTTP_COOKIE_DOMAIN', 'www.setbook.ru'); // ����� ������ cookie (������ ��������� � ������ ������)
  define('HTTP_COOKIE_PATH', '/'); // ���� ������ cookie (������ - ������ ����� [DIR_WS_CATALOG])
  define('SESSION_WRITE_DIRECTORY', '/tmp'); // ���������� ���� � ����������, � ������� ����� ��������� ���������� �����

// ���������� ���� � ����� � �������� ��� ������� � ������ �����������������, �� �������� ��������� ��� � ����� admin/.htaccess !!!
  define('HTPASSWD_FILENAME', '/var/www/2009/admin/.htpasswd');

// ���������� ���� � �����, � ������� ����� ����������� ������ �������� � ������
  define('UPLOAD_DIR', '/var/www/2009/admin/upload/');
?>