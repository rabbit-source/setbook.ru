<?php
// адрес сайта (не ставьте на конце "/" !!!)
  define('HTTP_SERVER', 'http://www.setbook.ru');
  define('HTTPS_SERVER', 'https://www.setbook.ru');

// Путь к директории, в которой будут находиться скрипты, относительно корня сайта (должен начинаться и заканчиваться символом "/")
  define('DIR_WS_CATALOG', '/');

// абсолютный путь к директории на сервере, в которой расположены скрипты сайта
  define('DIR_FS_CATALOG', '/var/www/2009/');

// виртуальная директория, в которой будет находиться интернет-магазин, относительно DIR_WS_CATALOG (см. выше)
  define('DIR_WS_ONLINE_STORE', 'books/');

// путь к директории, в которой будет находиться раздел администрирования, относительно DIR_WS_CATALOG (см. выше)
  define('DIR_WS_ADMIN_PART', 'admin/');

// параметры соединения с базой данных
  define('DB_SERVER', '192.168.0.9'); // адрес сервера MySQL
  define('DB_SERVER_USERNAME', 'setbook2009'); // имя пользователя БД
  define('DB_SERVER_PASSWORD', 'Stbkr2009'); // пароль пользователя
  define('DB_DATABASE', 'setbook_ru'); // имя базы данных

// Cookie и сессии
  define('HTTP_COOKIE_DOMAIN', 'www.setbook.ru'); // домен файлов cookie (обычно совпадает с именем домена)
  define('HTTP_COOKIE_PATH', '/'); // путь файлов cookie (обычно - корень сайта [DIR_WS_CATALOG])
  define('SESSION_WRITE_DIRECTORY', '/tmp'); // абсолютный путь к директории, в которой будут храниться сессионные файлы

// абсолютный путь к файлу с паролями для доступа в раздел администрирования, не забудьте прописать его в файле admin/.htaccess !!!
  define('HTPASSWD_FILENAME', '/var/www/2009/admin/.htpasswd');

// абсолютный путь к папке, в которую будут выгружаться данные клиентов и заказы
  define('UPLOAD_DIR', '/var/www/2009/admin/upload/');
?>