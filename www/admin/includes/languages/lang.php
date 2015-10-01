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

define('DEBUG_MODES_DISALLOW', 'Запретить:');
define('DEBUG_MODES_DISALLOW_CREATE', 'Создание подразделов');
define('DEBUG_MODES_DISALLOW_MOVE', 'Перемещение');
define('DEBUG_MODES_DISALLOW_EDIT', 'Редактирование');
define('DEBUG_MODES_DISALLOW_DELETE', 'Удаление');
define('DEBUG_MODES_DISALLOW_ALL', 'Все операции');

// page title
define('TITLE', 'Администрирование ' . STORE_NAME);

define('TEXT_CHOOSE', '- - Выберите - -');
define('TEXT_CHOOSE_TEMPLATE', 'Выберите шаблон страницы:');
define('TEXT_CHOOSE_BLOCKS', 'Выберите информационные блоки, которые будут отображаться на этой странице:');
define('TEXT_BLOCK_NOT_NEEDED', 'Не отображать этот блок');

// header text in includes/header.php
define('HEADER_TITLE_TOP', 'Администрирование');
define('HEADER_TITLE_SUPPORT_SITE', 'Сайт поддержки');
define('HEADER_TITLE_ONLINE_CATALOG', 'Каталог');
define('HEADER_TITLE_ADMINISTRATION', 'Администрация');

// text for gender
define('MALE', 'Мужчина');
define('FEMALE', 'Женщина');

// text for date of birth example
define('DOB_FORMAT_STRING', 'dd.mm.yyyy');

// content box
define('BOX_HEADING_CONTENT', 'Контент сайта');
define('BOX_CONTENT_SECTIONS', 'Разделы и статьи');
define('BOX_CONTENT_NEWS', 'Новости и анонсы');
define('BOX_CONTENT_BLOCKS', 'Блоки информации');
define('BOX_CONTENT_PAGES', 'Страницы сайта');
define('BOX_CONTENT_TEMPLATES', 'Шаблоны страниц');
define('BOX_CONTENT_REVIEWS', 'Отзывы');
define('BOX_CONTENT_MESSAGES', 'Сообщения на сайт');
define('BOX_CONTENT_BOARDS', 'Объявления');
define('BOX_CONTENT_BLACKLIST', 'Черный список');

// online catalog box
define('BOX_HEADING_CATALOG', 'Каталог');
define('BOX_CATALOG_CATEGORIES', 'Категории / Товары');
define('BOX_CATALOG_UPDATES', 'Групповые операции');
define('BOX_CATALOG_PARAMETERS', 'Типы товаров и параметры');
define('BOX_CATALOG_MANUFACTURERS', 'Издательства');
define('BOX_CATALOG_AUTHORS', 'Авторы');
define('BOX_CATALOG_SERIES', 'Серии');
define('BOX_CATALOG_SPECIALS', 'Спецпредложения');
define('BOX_CATALOG_EXPECTED', 'Ожидаемые товары');
define('BOX_CATALOG_EXPECTED_PRODUCTS', 'Ожидаемые товары');
define('BOX_CATALOG_FOREIGN_PRODUCTS', 'Иностранные книги');
define('BOX_CATALOG_UPLOAD', 'Загрузка прайс-листа');
define('BOX_CATALOG_LINKS', 'Каталог ссылок');

// orders box
define('BOX_HEADING_ORDERS', 'Заказы');
define('BOX_ORDERS_CUSTOMERS', 'Клиенты');
define('BOX_ORDERS_DISCOUNTS', 'Скидки');
define('BOX_ORDERS_ORDERS', 'Заказы');
define('BOX_ORDERS_ADVANCE_ORDERS', 'Иностранные товары');

// partners box
define('BOX_HEADING_PARTNERS', 'Партнеры');
define('BOX_PARTNERS_PARTNERS', 'Партнеры');

// configuration box
define('BOX_HEADING_CONFIGURATION', 'Настройки');
define('BOX_CONFIGURATION_SETTINGS', 'Настройки');
define('BOX_CONFIGURATION_USERS', 'Пользователи');

// modules box
define('BOX_HEADING_MODULES', 'Модули');
define('BOX_MODULES_PAYMENT', 'Оплата');
define('BOX_MODULES_PAYMENT_TO_GEOZONES', 'Оплата и геозоны');
define('BOX_MODULES_SHIPPING', 'Доставка');
define('BOX_MODULES_SHIPPING_TO_PAYMENT', 'Доставка к оплате');
define('BOX_MODULES_SHIPPING_TO_GEOZONES', 'Доставка и геозоны');
define('BOX_MODULES_ORDER_TOTAL', 'Заказ итого');

// localizaion box
define('BOX_HEADING_LOCALIZATION', 'Локализация');
define('BOX_LOCALIZATION_COUNTRIES', 'Страны');
define('BOX_LOCALIZATION_ZONES', 'Регионы');
define('BOX_LOCALIZATION_GEO_ZONES', 'Географические зоны');
define('BOX_LOCALIZATION_SHOPS', 'Магазины');
define('BOX_LOCALIZATION_SELF_DELIVERY', 'Пункты самовывоза');
define('BOX_LOCALIZATION_CURRENCIES', 'Валюты');
define('BOX_LOCALIZATION_LANGUAGES', 'Языки');
define('BOX_LOCALIZATION_SUBJECTS', 'Темы сообщений');
define('BOX_LOCALIZATION_ORDERS_STATUS', 'Статусы заказов');

// reports box
define('BOX_HEADING_REPORTS', 'Отчёты');
define('BOX_REPORTS_VIEWED', 'Просмотренные товары');
define('BOX_REPORTS_PURCHASED', 'Заказанные товары');
define('BOX_REPORTS_CUSTOMERS', 'Лучшие клиенты');

// tools box
define('BOX_HEADING_TOOLS', 'Инструменты');
define('BOX_TOOLS_BACKUP', 'Резервное копирование БД');
define('BOX_TOOLS_BANNERS', 'Менеджер баннеров');
define('BOX_TOOLS_FILE_MANAGER', 'Менеджер файлов');
define('BOX_TOOLS_MAIL', 'Послать e-mail');
define('BOX_TOOLS_NEWSLETTERS', 'Менеджер почтовых рассылок');
define('BOX_TOOLS_WHOS_ONLINE', 'Кто в онлайне');

// taxes box
define('BOX_HEADING_TAXES', 'Места / Налоги');
define('BOX_TAXES_TAX_CLASSES', 'Типы налогов');
define('BOX_TAXES_TAX_RATES', 'Ставки налогов');

// javascript messages
define('JS_ERROR', 'При заполнении формы Вы допустили ошибки!\nСделайте, пожалуйста, следующие исправления:\n\n');

define('JS_PRODUCTS_NAME', '* Для нового товара должно быть указано наименование\n');
define('JS_PRODUCTS_DESCRIPTION', '* Для нового товара должно быть указано описание\n');
define('JS_PRODUCTS_PRICE', '* Для нового товара должна быть указана цена\n');
define('JS_PRODUCTS_WEIGHT', '* Для нового товара должен быть указан вес\n');
define('JS_PRODUCTS_QUANTITY', '* Для нового товара должно быть указано количество\n');
define('JS_PRODUCTS_MODEL', '* Для нового товара должен быть указан код товара\n');
define('JS_PRODUCTS_IMAGE', '* Для нового товара должна быть картинка\n');

define('JS_SPECIALS_PRODUCTS_PRICE', '* Для этого товара должна быть установлена новая цена\n');

define('JS_GENDER', '* Поле «Пол» должно быть выбрано.\n');
define('JS_FIRST_NAME', '* Поле «Имя» должно быть заполнено.\n');
define('JS_LAST_NAME', '* Поле «Фамилия» должно быть заполнено.\n');
define('JS_DOB', '* Поле «День рождения» должно иметь формат: дд.мм.гггг.\n');
define('JS_EMAIL_ADDRESS', '* Поле «E-Mail адрес» должно быть заполнено.\n');
define('JS_ADDRESS', '* Поле «Адрес» должно быть заполнено.\n');
define('JS_POST_CODE', '* Поле «Индекс» должно ьыть заполнено.\n');
define('JS_CITY', '* Поле «Город» должно быть заполнено.\n');
define('JS_STATE', '* Поле «Регион» должно быть выбрано.\n');
define('JS_STATE_SELECT', '-- Выберите выше --');
define('JS_ZONE', '* Поле «Регион» должно соответствовать выбраной стране.');
define('JS_COUNTRY', '* Поле «Страна» дожно быть заполнено.\n');
define('JS_TELEPHONE', '* Поле «Телефон» должно быть заполнено.\n');
define('JS_FAX', '* Поле «Факс» должно быть заполнено.\n');
define('JS_PASSWORD', '* Поля «Пароль» и «Подтверждение» должны совпадать и содержать не менее ' . ENTRY_PASSWORD_MIN_LENGTH . ' символов.\n');

define('JS_ORDER_DOES_NOT_EXIST', 'Заказ номер %s не найден!');

define('CATEGORY_PERSONAL', 'Персональные данные');
define('CATEGORY_ADDRESS', 'Адрес');
define('CATEGORY_CONTACT', 'Для контакта');
define('CATEGORY_COMPANY', 'Информация о компании');
define('CATEGORY_OPTIONS', 'Рассылка');

define('ENTRY_GENDER', 'Пол:');
define('ENTRY_GENDER_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_FIRST_NAME', 'Имя:');
define('ENTRY_FIRST_NAME_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_LAST_NAME', 'Фамилия:');
define('ENTRY_LAST_NAME_ERROR', '&nbsp;<span class="errorText">обязательо</span>');
define('ENTRY_DATE_OF_BIRTH', 'Дата рождения:');
define('ENTRY_DATE_OF_BIRTH_ERROR', '&nbsp;<span class="errorText">(пример 21.05.1970)</span>');
define('ENTRY_EMAIL_ADDRESS', 'Email-адрес:');
define('ENTRY_IP_ADDRESS', 'IP-адрес:');
define('ENTRY_EMAIL_ADDRESS_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', '&nbsp;<span class="errorText">Вы ввели неверный email адрес!</span>');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', '&nbsp;<span class="errorText">Данный email адрес уже зарегистрирован!</span>');
define('ENTRY_DISCOUNT', 'Персональная скидка:');
define('ENTRY_DISCOUNT_TYPE', 'Тип скидки:');
define('ENTRY_DISCOUNT_TYPE_DISCOUNT', 'Минус от продажной цены сайта');
define('ENTRY_DISCOUNT_TYPE_PURCHASE', 'Плюс к закупочной цене');
define('ENTRY_COMPANY', 'Название компании:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_STREET_ADDRESS', 'Адрес:');
define('ENTRY_STREET_ADDRESS_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_SUBURB', 'Район:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_POST_CODE', 'Индекс:');
define('ENTRY_POST_CODE_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_CITY', 'Город:');
define('ENTRY_CITY_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_STATE', 'Регион:');
define('ENTRY_STATE_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_COUNTRY', 'Страна:');
define('ENTRY_COUNTRY_ERROR', '');
define('ENTRY_TELEPHONE_NUMBER', 'Телефон:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', '&nbsp;<span class="errorText">обязательно</span>');
define('ENTRY_FAX_NUMBER', 'Факс:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_NEWSLETTER', 'Получать рассылку:');
define('ENTRY_NEWSLETTER_YES', 'Подписан');
define('ENTRY_NEWSLETTER_NO', 'Не подписан');
define('ENTRY_NEWSLETTER_ERROR', '');

// images
define('IMAGE_ANI_SEND_EMAIL', 'Отправить e-mail');
define('IMAGE_BACK', 'Назад');
define('IMAGE_BACKUP', 'Создать бэкап');
define('IMAGE_CANCEL', 'Отменить');
define('IMAGE_CONFIRM', 'Подтвердить');
define('IMAGE_COPY', 'Копировать');
define('IMAGE_COPY_TO', 'Копировать в');
define('IMAGE_DETAILS', 'Подробнее');
define('IMAGE_DOWNLOAD', 'Выгрузить');
define('IMAGE_DELETE', 'Удалить');
define('IMAGE_EDIT', 'Изменить');
define('IMAGE_EMAIL', 'Email');
define('IMAGE_FILE_MANAGER', 'Менеджер файлов');
define('IMAGE_ICON_STATUS_GREEN', 'Активный');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', 'Активизировать');
define('IMAGE_ICON_STATUS_YELLOW', 'По умолчанию');
define('IMAGE_ICON_STATUS_YELLOW_LIGHT', 'Сделать по умолчанию');
define('IMAGE_ICON_STATUS_RED', 'Неактивный');
define('IMAGE_ICON_STATUS_RED_LIGHT', 'Сделать неактивным');
define('IMAGE_ICON_INFO', 'Информация');
define('IMAGE_INSERT', 'Добавить');
define('IMAGE_LOCK', 'Замок');
define('IMAGE_MODULE_INSTALL', 'Установить модуль');
define('IMAGE_MODULE_REMOVE', 'Удалить модуль');
define('IMAGE_MOVE', 'Переместить');
define('IMAGE_NEW_BANNER', 'Новый баннер');
define('IMAGE_NEW_CATEGORY', 'Новая категория');
define('IMAGE_NEW_LINK', 'Новая ссылка');
define('IMAGE_NEW_SECTION', 'Добавить раздел');
define('IMAGE_NEW_RECORD', 'Добавить запись');
define('IMAGE_NEW_TYPE', 'Добавить тип');
define('IMAGE_NEW_BLOCK', 'Добавить блок');
define('IMAGE_NEW_COUNTRY', 'Новая страна');
define('IMAGE_NEW_CURRENCY', 'Новая валюта');
define('IMAGE_NEW_FILE', 'Новый файл');
define('IMAGE_NEW_FOLDER', 'Новая папка');
define('IMAGE_NEW_LANGUAGE', 'Новый язык');
define('IMAGE_NEW_NEWSLETTER', 'Новое письмо новостей');
define('IMAGE_NEW_PRODUCT', 'Новый товар');
define('IMAGE_NEW_TAX_CLASS', 'Новый налог'); 
define('IMAGE_NEW_TAX_RATE', 'Новая ставка налога');
define('IMAGE_NEW_TAX_ZONE', 'Новая налоговая зона');
define('IMAGE_NEW_ZONE', 'Новая зона');
define('IMAGE_ORDERS', 'Заказы');
define('IMAGE_ORDERS_INVOICE', 'Счёт-фактура');
define('IMAGE_ORDERS_PACKINGSLIP', 'Накладная');
define('IMAGE_PREVIEW', 'Предпросмотр');
define('IMAGE_PACKINGSLIP', 'Карточка заказа');
define('IMAGE_RESTORE', 'Восстановить');
define('IMAGE_RESET', 'Сброс');
define('IMAGE_SAVE', 'Сохранить');
define('IMAGE_SEARCH', 'Искать');
define('IMAGE_SELECT', 'Выбрать');
define('IMAGE_SEND', 'Отправить');
define('IMAGE_SEND_EMAIL', 'Отправить e-mail');
define('IMAGE_UNLOCK', 'Разблокировать');
define('IMAGE_UPDATE', 'Обновить');
define('IMAGE_UPDATE_CURRENCIES', 'Скорректировать курсы валют');
define('IMAGE_UPLOAD', 'Загрузить');
define('IMAGE_UPLOAD_BACKUP', 'Загрузить из бэкап');

define('ICON_CROSS', 'Недействительно');
define('ICON_CURRENT_FOLDER', 'Текущая директория');
define('ICON_DELETE', 'Удалить');
define('ICON_ERROR', 'Ошибка');
define('ICON_FILE', 'Файл');
define('ICON_FILE_DOWNLOAD', 'Загрузка');
define('ICON_FOLDER', 'Папка');
define('ICON_LOCKED', 'Заблокировать');
define('ICON_PREVIOUS_LEVEL', 'Предыдущий уровень');
define('ICON_PREVIEW', 'Предпросмотр');
define('ICON_STATISTICS', 'Статистика');
define('ICON_SUCCESS', 'Выполнено');
define('ICON_TICK', 'Истина');
define('ICON_UNLOCKED', 'Разблокировать');
define('ICON_WARNING', 'ВНИМАНИЕ');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Страница %s из %d');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS', 'Показано <strong>%d</strong> - <strong>%d</strong> (всего записей: <strong>%d</strong>)');

define('PREVNEXT_BUTTON_PREV', 'Предыдущая');
define('PREVNEXT_BUTTON_NEXT', 'Следующая');

define('TEXT_DEFAULT', 'по умолчанию');
define('TEXT_SET_DEFAULT', 'Установить по умолчанию');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* Обязательно</span>');

define('TEXT_ACCESS_DENIED', '&nbsp;<font color="#ff0000"><strong>У Вас нет прав для доступа к этой странице!</strong></font>');
define('TEXT_OPERATION_DENIED', '&nbsp;У Вас нет прав для выполнения данной операции!');

define('ERROR_NO_DEFAULT_CURRENCY_DEFINED', 'Ошибка: К настоящему времени ни одна валюта не была установлена по умолчанию. Пожалуйста, установите одну из них в: Локализация -> Валюта');
define('ERROR_NO_DEFAULT_LANGUAGE_DEFINED', 'Ошибка: К настоящему времени ни один язык не установлен по умолчанию. Пожалуйста, установите в: Локализация -> Языки');

define('TEXT_CACHE_CATEGORIES', 'Бокс Категорий');
define('TEXT_CACHE_MANUFACTURERS', 'Бокс Авторов');
define('TEXT_CACHE_ALSO_PURCHASED', 'Также Модули Покупок'); 

define('TEXT_NONE', '--нет--');
define('TEXT_TOP', 'Начало');

define('TEXT_DEFAULT_SELECT', '--Выберите из списка--');
define('TEXT_NO_HTML', 'HTML-тэги не поддерживаются!');
define('TEXT_MAX_255', 'Максимум 255 символов!');

define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Ошибка: Каталог не существует.');
define('ERROR_DESTINATION_NOT_WRITEABLE', 'Ошибка: Каталог защищён от записи, установите необходимые права доступа.');
define('ERROR_FILE_NOT_SAVED', 'Ошибка: Файл не был загружен.');
define('ERROR_FILETYPE_NOT_ALLOWED', 'Ошибка: Нельзя закачивать файлы данного типа.');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Файл успешно загружен.');
define('WARNING_NO_FILE_UPLOADED', 'Новые файлы не загружены.');
define('WARNING_FILE_UPLOADS_DISABLED', 'Опция загрузки файлов отключена в конфигурационном файле php.ini.');

define('WARNING_IMAGES_LANGUAGES_DIRECTORY_NON_EXISTENT', 'Директория, в которую будут сохранятся иконки языков, не существует: ' . DIR_FS_CATALOG_IMAGES . 'languages/.');
define('WARNING_IMAGES_LANGUAGES_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую будут сохранятся иконки языков: ' . DIR_FS_CATALOG_IMAGES . 'languages/.');
define('WARNING_IMAGES_IMAGE_DIRECTORY_NON_EXISTENT', 'Директория, в которую пользователь будет сохранять рисунки, не существует: ' . DIR_FS_CATALOG_IMAGES . 'Image/.');
define('WARNING_IMAGES_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую пользователь будет сохранять рисунки: ' . DIR_FS_CATALOG_IMAGES . 'Image/.');
define('WARNING_IMAGES_FLASH_DIRECTORY_NON_EXISTENT', 'Директория, в которую пользователь будет сохранять флэш-ролики, не существует: ' . DIR_FS_CATALOG_IMAGES . 'Flash/.');
define('WARNING_IMAGES_FLASH_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую пользователь будет сохранять флэш-ролики: ' . DIR_FS_CATALOG_IMAGES . 'Flash/.');
define('WARNING_IMAGES_FILE_DIRECTORY_NON_EXISTENT', 'Директория, в которую пользователь будет сохранять файлы, не существует: ' . DIR_FS_CATALOG_IMAGES . 'File/.');
define('WARNING_IMAGES_FILE_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую пользователь будет сохранять файлы: ' . DIR_FS_CATALOG_IMAGES . 'File/.');
define('WARNING_IMAGES_MEDIA_DIRECTORY_NON_EXISTENT', 'Директория, в которую пользователь будет сохранять мультимедиа-файлы, не существует: ' . DIR_FS_CATALOG_IMAGES . 'Media/.');
define('WARNING_IMAGES_MEDIA_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую пользователь будет сохранять мультимедиа-файлы: ' . DIR_FS_CATALOG_IMAGES . 'Media/.');
define('WARNING_INCLUDES_TEMPLATES_DIRECTORY_NON_EXISTENT', 'Директория, в которую будут сохранятся шаблоны страниц, не существует: ' . DIR_FS_CATALOG_TEMPLATES . '.');
define('WARNING_INCLUDES_TEMPLATES_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую будут сохранятся шаблоны страниц: ' . DIR_FS_CATALOG_TEMPLATES . '.');
define('WARNING_INCLUDES_BLOCKS_DIRECTORY_NON_EXISTENT', 'Директория, в которую будут сохранятся файлы блоков информации, не существует: ' . DIR_FS_CATALOG_BLOCKS . '.');
define('WARNING_INCLUDES_BLOCKS_DIRECTORY_NOT_WRITEABLE', 'Нет доступа к директории, в которую будут сохранятся файлы блоков информации: ' . DIR_FS_CATALOG_BLOCKS . '.');
?>