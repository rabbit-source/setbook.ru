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

define('HOME_DOMAIN_INVITATION', '<p>Уважаемый посетитель! Если Вы хотите оформить заказ с доставкой в своей стране (<strong>%s</strong>), перейдите, пожалуйста, на региональный сайт SetBook: %s</p>');

define('HEADER_TITLE_SKYPE_BUTTON', 'Вы можете связаться с нами по Skype™');
define('HEADER_TITLE_SEARCH', 'Искать');
define('HEADER_TITLE_ADVANCED_SEARCH', 'Расширенный поиск &raquo;');
define('HEADER_TITLE_KEYBOARD', 'клавиатура:');
define('HEADER_TITLE_KEYBOARD_RU', 'русская');
define('HEADER_TITLE_KEYBOARD_UA', 'украинская');
define('HEADER_TITLE_KEYBOARD_CLOSE', 'закрыть [x]');

define('HEADER_TITLE_PHONE_NUMBER', (strlen(STORE_OWNER_PHONE_NUMBER)>16 ? 'Тел.:' : 'Телефон:'));

define('HEADER_TITLE_ACCOUNT_LOGIN', 'Вход');
define('HEADER_TITLE_ACCOUNT_REGISTER', 'Регистрация');
define('HEADER_TITLE_ACCOUNT_LOGOFF', 'Выход [X]');
define('HEADER_TITLE_ACCOUNT', 'Личный кабинет');
define('HEADER_TITLE_ACCOUNT_DISCOUNT', 'Ваша скидка:');

define('HEADER_TITLE_CALLBACK', 'Обратный звонок');
define('HEADER_TITLE_CALLBACK_DESCRIPTION', 'Мы перезвоним вам на <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'phone\');">телефон</span> или <span style="cursor: pointer; border-bottom: 1px dotted black;" onclick="showCallbackForm(\'skype\');">skype</span>');
define('HEADER_TITLE_CALLBACK_COUNTRY', 'Страна:');
define('HEADER_TITLE_CALLBACK_COUNTRY_CHANGE', 'изменить');
define('HEADER_TITLE_CALLBACK_REGION_CODE', 'Код&nbsp;сети:');
define('HEADER_TITLE_CALLBACK_PHONE_NUMBER', 'Телефон:');
define('HEADER_TITLE_CALLBACK_SKYPE_NUMBER', 'Skype:');
define('HEADER_TITLE_CALLBACK_ERROR_SKYPE', 'Ошибка! Вы не указали свой логин Skype!');
define('HEADER_TITLE_CALLBACK_ERROR_COUNTRY', 'Ошибка! Вы не выбрали страну!');
define('HEADER_TITLE_CALLBACK_ERROR_REGION_CODE', 'Ошибка! Вы не указали код города/сети!');
define('HEADER_TITLE_CALLBACK_ERROR_PHONE', 'Ошибка! Вы не указали номер телефона!');

define('HEADER_TITLE_SHOPPING_CART', 'Ваша корзина');
define('HEADER_TITLE_SHOPPING_CART_PRODUCTS', 'товаров:');
define('HEADER_TITLE_SHOPPING_CART_SUM', 'на сумму');
define('HEADER_TITLE_SHOPPING_CART_EMPTY', 'товаров нет');
define('HEADER_TITLE_SHOPPING_CART_CHECKOUT', 'оформить заказ »');
define('HEADER_TITLE_POSTPONE_CART', 'Отложенные товары');
define('HEADER_TITLE_POSTPONE_CART_PRODUCTS', 'отложено товаров:');
define('HEADER_TITLE_FOREIGN_CART', 'Иностранные книги');
define('HEADER_TITLE_FOREIGN_CART_PRODUCTS', 'иностранные книги:');

define('LEFT_COLUMN_TITLE_SPECIALS', 'Распродажи, новинки, хиты');
define('LEFT_COLUMN_TITLE_REVIEWS', 'Отзывы и рецензии');
define('LEFT_COLUMN_TITLE_FRAGMENTS', 'Книги с отрывками');
define('LEFT_COLUMN_TITLE_HOLIDAY', '<span style="color: #FE0000;">Н</span>' .
									'<span style="color: #FC9506;">о</span>' .
									'<span style="color: #01CB33;">в</span>' .
									'<span style="color: #0299FE;">о</span>' .
									'<span style="color: #6432C9;">г</span>' .
									'<span style="color: #FE0000;">о</span>' .
									'<span style="color: #FC9506;">д</span>' .
									'<span style="color: #01CB33;">н</span>' .
									'<span style="color: #0299FE;">и</span>' .
									'<span style="color: #6432C9;">е</span> ' .
									'<span style="color: #FE0000;">п</span>' .
									'<span style="color: #FC9506;">р</span>' .
									'<span style="color: #01CB33;">е</span>' .
									'<span style="color: #0299FE;">д</span>' .
									'<span style="color: #6432C9;">л</span>' .
									'<span style="color: #FE0000;">о</span>' .
									'<span style="color: #FC9506;">ж</span>' .
									'<span style="color: #01CB33;">е</span>' .
									'<span style="color: #0299FE;">н</span>' .
									'<span style="color: #6432C9;">и</span>' .
									'<span style="color: #FE0000;">я</span>');
define('LEFT_COLUMN_TITLE_NEWS', 'Новости');
define('LEFT_COLUMN_TITLE_NEWS_BY_DATE', 'По дате');
define('LEFT_COLUMN_TITLE_NEWS_BY_CATEGORY', 'Акции, новости, интервью');

define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'Все товары этого производителя');
define('BOX_MANUFACTURER_INFO_HOMEPAGE', 'Сайт "%s"');

define('TEXT_RSS_SUBSCRIPTION', 'RSS-подписка');

define('TEXT_MONTH_JANUARY', 'Январь');
define('TEXT_MONTH_FEBRUARY', 'Февраль');
define('TEXT_MONTH_MARCH', 'Март');
define('TEXT_MONTH_APRIL', 'Апрель');
define('TEXT_MONTH_MAY', 'Май');
define('TEXT_MONTH_JUNE', 'Июнь');
define('TEXT_MONTH_JULY', 'Июль');
define('TEXT_MONTH_AUGUST', 'Август');
define('TEXT_MONTH_SEPTEMBER', 'Сентябрь');
define('TEXT_MONTH_OCTOBER', 'Октябрь');
define('TEXT_MONTH_NOVEMBER', 'Ноябрь');
define('TEXT_MONTH_DECEMBER', 'Декабрь');

// text for gender
define('MALE', 'Мужской');
define('FEMALE', 'Женский');
define('MALE_ADDRESS', 'Г-н');
define('FEMALE_ADDRESS', 'Г-жа');

// text for date of birth example
define('DOB_FORMAT_STRING', 'dd.mm.yy');

define('TEXT_NO_NEWS', 'За указанный период новостей нет');

// contact_us box text
define('ENTRY_CONTACT_US_TITLE', 'Обратная связь');
define('ENTRY_CONTACT_US', 'Ваши данные и сообщение');
define('ENTRY_CONTACT_US_SUBJECT', 'Выберите тему сообщения:');
define('ENTRY_CONTACT_US_NAME', 'Ваше имя:');
define('ENTRY_CONTACT_US_EMAIL', 'E-mail адрес:');
define('ENTRY_CONTACT_US_PHONE_NUMBER', 'Контактный телефон:');
define('ENTRY_CONTACT_US_IP_ADDRESS', 'IP-адрес отправителя:');
define('ENTRY_CONTACT_US_ENQUIRY', 'Сообщение:');
define('ENTRY_CONTACT_US_SUCCESS', 'Ваше сообщение было успешно отправлено в отдел обслуживания нашего магазина.');
define('ENTRY_CONTACT_US_EMAIL_SUBJECT', 'Сообщение с сайта');
define('ENTRY_CONTACT_US_FEEDBACK_EMAIL_SUBJECT', 'Комментарии/предложения при регистрации');

define('ENTRY_CAPTCHA_TITLE', 'Антиспам:');
define('ENTRY_CAPTCHA_TEXT', 'введите результат арифметической операции');

define('ENTRY_BLACKLIST_ORDER_ERROR', '<span class="errorText">К сожалению, вы не можете оформить заказ на нашем сайте.</span>');
define('ENTRY_BLACKLIST_REQUEST_ERROR', '<span class="errorText">К сожалению, вы не можете оставить заявку на нашем сайте.</span>');
define('ENTRY_BLACKLIST_BOARD_ERROR', '<span class="errorText">К сожалению, вы не можете размещать объявления на нашем сайте.</span>');
define('ENTRY_BLACKLIST_REVIEW_ERROR', '<span class="errorText">К сожалению, вы не можете оставлять отзывы на нашем сайте.</span>');
define('ENTRY_BLACKLIST_CONTACT_US_ERROR', '<span class="errorText">К сожалению, вы не можете воспользоваться формой обратной связи.</span>');

// request box text
define('ENTRY_REQUEST_FORM_TITLE', 'Запрос на поиск книги');
define('ENTRY_REQUEST_FORM_CONTACTS', 'Контактные данные');
define('ENTRY_REQUEST_FORM_TITLE_FOREIGN_PRODUCTS', 'Предварительная заявка на доставку товаров из-за границы');
define('ENTRY_REQUEST_FORM_TITLE_FOREIGN_BOOKS', 'Предварительный заказ иностранных книг');
define('ENTRY_REQUEST_FORM', 'Список книг для поиска');
define('ENTRY_REQUEST_FORM_PHONE_NUMBER', 'Контактный телефон:');
define('ENTRY_REQUEST_FORM_COMMENTS', 'Комментарий:');
define('ENTRY_REQUEST_FORM_NAME', 'Ваше имя:');
define('ENTRY_REQUEST_FORM_EMAIL', 'E-mail:');
define('ENTRY_REQUEST_FORM_ADDRESS', 'Адрес доставки:');
define('ENTRY_REQUEST_FORM_ADDRESS_TEXT', '(Для расчета стоимости доставки)');
define('ENTRY_REQUEST_FORM_PRODUCT_INFO', 'Информация о %s-м товаре');
define('ENTRY_REQUEST_FORM_BOOK_INFO', 'Информация о %s-й книге');
define('ENTRY_REQUEST_FORM_AUTHORIZATION_NEEDED', 'Услуга доступна только зарегистрированным пользователям. Пожалуйста, <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">авторизуйтесь</a> или <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">зарегистрируйтесь</a>! Регистрация проста, не требует подтверждения и займёт у вас не более минуты.');
define('ENTRY_REQUEST_FORM_PRODUCT_TITLE', 'Наименование товара:');
define('ENTRY_REQUEST_FORM_BOOK_TITLE', 'Название книги:');
define('ENTRY_REQUEST_FORM_PRODUCT_AUTHOR', 'Автор:');
define('ENTRY_REQUEST_FORM_PRODUCT_CODE', 'Код ТНВЭД (если знаете):');
define('ENTRY_REQUEST_FORM_PRODUCT_CODE_SHORT', 'Код ТНВЭД:');
define('ENTRY_REQUEST_FORM_PRODUCT_MODEL', 'Модель/артикул:');
define('ENTRY_REQUEST_FORM_BOOK_MODEL', 'ISBN:');
define('ENTRY_REQUEST_FORM_PRODUCT_MANUFACTURER', 'Производитель:');
define('ENTRY_REQUEST_FORM_BOOK_MANUFACTURER', 'Издательство:');
define('ENTRY_REQUEST_FORM_PRODUCT_YEAR', 'Год:');
define('ENTRY_REQUEST_FORM_PRODUCT_URL', 'Вы нашли этот товар на сайте (укажите ссылку):');
define('ENTRY_REQUEST_FORM_BOOK_URL', 'Вы нашли эту книгу на сайте (укажите ссылку на страницу):');
define('ENTRY_REQUEST_FORM_PRODUCT_URL_SHORT', 'Ссылка на сайт:');
define('ENTRY_REQUEST_FORM_PRODUCT_PRICE', 'Цена товара на сайте:');
define('ENTRY_REQUEST_FORM_BOOK_PRICE', 'Цена книги на сайте:');
define('ENTRY_REQUEST_FORM_PRODUCT_QTY', 'Кол-во:');
define('ENTRY_REQUEST_FORM_PRODUCT_EXISTS', 'Книга есть в каталоге под названием «%s» по цене %s');
define('ENTRY_REQUEST_FORM_SUCCESS', 'Ваш запрос был успешно отправлен в отдел обслуживания нашего магазина.');
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT', 'Запрос на поиск книги с сайта ' . STORE_NAME);
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_PRODUCTS', STORE_NAME . ' - Предварительная заявка на иностранные товары #%s');
define('ENTRY_REQUEST_FORM_EMAIL_SUBJECT_FOREIGN_BOOKS', STORE_NAME . ' - Предварительная заявка на иностранные книги #%s');
define('ENTRY_REQUEST_FORM_ERROR', 'Ошибка! Не заполнены обязательные поля!');
define('ENTRY_REQUEST_FORM_CURRENCY', '- Валюта - ');
define('ENTRY_REQUEST_FORM_CURRENCY_USD', 'Долларов');
define('ENTRY_REQUEST_FORM_CURRENCY_EUR', 'Евро');
define('ENTRY_REQUEST_FORM_CURRENCY_GBP', 'Фунтов');
define('ENTRY_REQUEST_FORM_CURRENCY_RUR', 'Рублей');

// corporate box text
define('ENTRY_CORPORATE_FORM_TITLE', 'Выберите действие');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_FILE', 'Загрузка подготовленного файла');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_TEXT', 'Загрузка подготовленного списка');
define('ENTRY_CORPORATE_FORM_CHOOSE_UPLOAD_OPTIONS', 'Опции загрузки');
define('ENTRY_CORPORATE_FORM_CHOOSE_DOWNLOAD', 'Формирование прайс-листа');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELDS', 'Выберите выгружаемые в файл поля:');
define('ENTRY_CORPORATE_FORM_CHOOSE_SPECIALS', 'Выберите спецпредложения:');
define('ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS', 'Укажите издательства:');
define('ENTRY_CORPORATE_FORM_CHOOSE_MANUFACTURERS_TEXT', 'Если вы хотите, чтобы в файле присутствовали книги только определенных издательств, перечислите их названия по одному в строке');
define('ENTRY_CORPORATE_FORM_CHOOSE_CATEGORIES', 'Выберите рубрики:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FILE', 'Выберите файл:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FILE_TEXT', 'нажмите кнопку «Обзор» ("Browse") и найдите файл, который вы подготовили для загрузки');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL', 'Номер столбца, в котором указан ISBN:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_MODEL_TEXT', 'введите порядковый номер колонки в файле, в которой указаны ISBN-коды книг');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY', 'Номер столбца, с заказываемым количеством:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_QTY_TEXT', 'введите порядковый номер колонки в файле, в которой указаны заказываемое количество книг (программа обрабатывает только те книги, у которых количество отлично от нуля)');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN', 'Введите коды ISBN книг вручную:');
define('ENTRY_CORPORATE_FORM_CHOOSE_FIELD_ISBN_TEXT', 'Вы можете использовать это поле ввода <span class="errorText">вместо загрузки файла</span>, для этого введите здесь ISBN-коды заказываемых книг (каждый код – с новой строки) и через пробел (или символ табуляции) – желаемое количество (в дальнейшем вы сможете его изменить в своей корзине)<br /><br />Пример заполнения:<pre>978-5-8475-0509-3	4' . "\n" . '978-5-17-050781-8	3' . "\n" . '978-5-17-059547-1	8' . "\n" . '978-5-699-31874-2	5</pre>');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT', 'Товары, которых нет в наличии:');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_SKIP', 'Пропустить');
define('ENTRY_CORPORATE_FORM_CHOOSE_ABSENT_POSTPONE', 'Поместить в &laquo;Отложенные товары&raquo;');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS', 'Выгрузить в файл:');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ACTIVE', 'Только книги, имеющиеся в наличии');
define('ENTRY_CORPORATE_FORM_CHOOSE_STATUS_ALL', 'Все книги');
define('ENTRY_CORPORATE_FORM_CHOOSE_ANOTHER_METHOD', 'Обращаем ваше внимание на то, что программа обработки может не распознать формат файла, в этом случае вы можете отправить его по <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">электронной почте</a> и наши менеджеры вручную внесут заказываемые позиции в вашу корзину, после чего свяжутся с вами и вы сможете оформить заказ.');
define('ENTRY_CORPORATE_FORM_PRODUCTS_CHOICE_ERROR', 'Ошибка! Не выбрано ни одного параметра для поиска!');
define('ENTRY_CORPORATE_FORM_PRODUCTS_FOUND_ERROR', 'Ошибка! Не найдено ни одного товара!');
define('ENTRY_CORPORATE_FORM_NO_DATA_UPLOADED_ERROR', 'Ошибка! Не загружены данные!');
define('ENTRY_CORPORATE_FORM_UNKNOWN_FILE_UPLOADED_ERROR', 'Ошибка! Неизвестный формат файла!');
define('ENTRY_CORPORATE_FORM_SUCCESS_SKIP', 'Файл успешно загружен! Обработано %s строк, добавлено в корзину %s позиций (общим количеством %s), пропущено (нет в наличии) %s, не найдено %s! Нажмите кнопку "Оформить заказ", находящуюся под списком товаров, чтобы приступить к оформлению заказа. Размер оптовой скидки вы увидите на странице подтверждения данных заказа.');
define('ENTRY_CORPORATE_FORM_SUCCESS_POSTPONE', 'Файл успешно загружен! Обработано %s строк, добавлено в корзину %s позиций (общим количеством %s), отложено (нет в наличии) %s, не найдено %s! Нажмите кнопку "Оформить заказ", находящуюся под списком товаров, чтобы приступить к оформлению заказа. Размер оптовой скидки вы увидите на странице подтверждения данных заказа.');
define('ENTRY_CORPORATE_FORM_NO_MODELS_ERROR', 'Ошибка! Файл обработан, но в указанном столбце не найдено ни одного ISBN-кода!');

// pull down default text
define('PULL_DOWN_DEFAULT', '- Выберите из списка -');
define('TYPE_BELOW', 'Введите ниже');

// javascript messages
define('JS_ERROR', 'Ошибки при заполнении формы!\n\nИсправьте пожалуйста:\n\n');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Выберите метод оплаты для Вашего заказа.\n');

define('JS_ERROR_SUBMITTED', 'Эта форма уже заполнена. Нажимайте Ok.');

define('ERROR_NO_SHIPPING_MODULE_SELECTED', 'Выберите, пожалуйста, способ доставки.');
define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Выберите, пожалуйста, метод оплаты заказа.');

define('CATEGORY_COMPANY', 'Сведения о компании');
define('CATEGORY_PERSONAL', 'Ваши персональные данные');
define('CATEGORY_ADDRESS', 'Адрес доставки');
define('CATEGORY_CONTACT', 'Контактные телефоны');
define('CATEGORY_OPTIONS', 'Рассылка');
define('CATEGORY_PASSWORD', 'Ваш пароль');
define('CATEGORY_FEEDBACK', 'Комментарий');

define('ENTRY_COMPANY', 'Название компании');
define('ENTRY_COMPANY_ERROR', 'Вы не указали название компании.');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_COMPANY_FULL', 'Полное наименование организации');
define('ENTRY_COMPANY_FULL_ERROR', 'Вы не указали полное наименование организации.');
define('ENTRY_COMPANY_FULL_TEXT', '');
if (DOMAIN_ZONE=='ru') {
  define('ENTRY_COMPANY_INN', 'ИНН');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', 'БИК банка');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='kz') {
  define('ENTRY_COMPANY_INN', 'РНН');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали РНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', 'Код банка (БИК)');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='by') {
  define('ENTRY_COMPANY_INN', 'УНП');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали УНП');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', 'Код банка (БИК)');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='ua') {
  define('ENTRY_COMPANY_INN', 'ИНН');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'ОКПО/ЕГРПОУ');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали код ОКПО/ЕГРПОУ');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', 'МФО банка');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'true');
} else {
  define('ENTRY_COMPANY_INN', 'ИНН');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_BIK', 'БИК банка');
  define('ENTRY_COMPANY_BIK_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'false');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
}
define('ENTRY_COMPANY_OGRN', 'ОГРН');
define('ENTRY_COMPANY_OGRN_TEXT', '');
define('ENTRY_COMPANY_OKPO', 'ОКПО');
define('ENTRY_COMPANY_OKPO_TEXT', '');
define('ENTRY_COMPANY_OKOGU', 'ОКОГУ');
define('ENTRY_COMPANY_OKOGU_TEXT', '');
define('ENTRY_COMPANY_OKATO', 'ОКАТО');
define('ENTRY_COMPANY_OKATO_TEXT', '');
define('ENTRY_COMPANY_OKVED', 'ОКВЭД');
define('ENTRY_COMPANY_OKVED_TEXT', '');
define('ENTRY_COMPANY_OKFS', 'ОКФС');
define('ENTRY_COMPANY_OKFS_TEXT', '');
define('ENTRY_COMPANY_OKOPF', 'ОКОПФ');
define('ENTRY_COMPANY_OKOPF_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_CORPORATE', 'Юридический адрес');
define('ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_POST', 'Адрес для корреспонденции');
define('ENTRY_COMPANY_ADDRESS_POST_TEXT', '');
define('ENTRY_COMPANY_TELEPHONE', 'Телефон');
define('ENTRY_COMPANY_TELEPHONE_TEXT', '');
define('ENTRY_COMPANY_FAX', 'Факс');
define('ENTRY_COMPANY_FAX_TEXT', '');
define('ENTRY_COMPANY_BANK', 'Банк');
define('ENTRY_COMPANY_BANK_TEXT', '');
define('ENTRY_COMPANY_RS', 'Расчетный счет');
define('ENTRY_COMPANY_RS_TEXT', '');
define('ENTRY_COMPANY_KS', 'Корреспондентский счет');
define('ENTRY_COMPANY_KS_TEXT', '');
define('ENTRY_COMPANY_GENERAL', 'Генеральный директор');
define('ENTRY_COMPANY_GENERAL_TEXT', '(фамилия и инициалы)');
define('ENTRY_COMPANY_FINANCIAL', 'Главный бухгалтер');
define('ENTRY_COMPANY_FINANCIAL_TEXT', '(фамилия и инициалы)');

define('ENTRY_GENDER', 'Пол:');
define('ENTRY_GENDER_ERROR', 'Вы не указали свой пол.');
define('ENTRY_GENDER_TEXT', '');
define('ENTRY_CUSTOMER_TYPE', 'Вы регистрируетесь как');
if (in_array(DOMAIN_ZONE, array('ru', 'by', 'ua', 'kz'))) {
  define('ENTRY_CUSTOMER_TYPE_PRIVATE', 'физическое лицо');
  define('ENTRY_CUSTOMER_TYPE_CORPORATE', 'юридическое лицо');
} else {
  define('ENTRY_CUSTOMER_TYPE_PRIVATE', 'частное лицо');
  define('ENTRY_CUSTOMER_TYPE_CORPORATE', 'представитель компании');
}
define('ENTRY_FIRST_NAME', 'Имя');
define('ENTRY_FIRST_NAME_ERROR', 'Вы не указали своё имя.');
define('ENTRY_FIRST_NAME_TEXT', '');
define('ENTRY_MIDDLE_NAME', 'Отчество');
define('ENTRY_MIDDLE_NAME_ERROR', 'Вы не указали своё отчество.');
define('ENTRY_MIDDLE_NAME_TEXT', '');
define('ENTRY_LAST_NAME', 'Фамилия');
define('ENTRY_LAST_NAME_ERROR', 'Вы не указали свою фамилию.');
define('ENTRY_LAST_NAME_TEXT', '');
define('ENTRY_DOB', 'Дата рождения');
define('ENTRY_DOB_ERROR', 'Вы не указали дату рождения.');
define('ENTRY_DOB_CHECK_ERROR', 'Неверный формат даты рождения (пример: 21.05.1970).');
define('ENTRY_DOB_TEXT', ' (в формате 21.05.1970)');
define('ENTRY_EMAIL_ADDRESS', 'E-mail');
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Вы не указали контактный e-mail.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Ваш e-mail адрес указан неправильно, попробуйте ещё раз.');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'Введённый Вами e-mail уже зарегистрирован в нашем магазине, попробуйте указать другой электронный адрес.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '');
define('ENTRY_STREET_ADDRESS', 'Точный адрес');
define('ENTRY_STREET_ADDRESS_ERROR', 'Вы не указали адрес.');
if (in_array(DOMAIN_ZONE, array('ru', 'by', 'ua', 'kz'))) {
  define('ENTRY_STREET_ADDRESS_TEXT', 'в формате: название улицы, номер дома, корпус дома (если есть), номер квартиры (если есть)<br />например: ул. Широкая, д.1, к.4, кв.77');
} else {
  define('ENTRY_STREET_ADDRESS_TEXT', '');
}
define('ENTRY_SUBURB', 'Район');
define('ENTRY_SUBURB_ERROR', 'Вы не указали район.');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POSTCODE_ERROR', 'Вы не указали почтовый индекс города.');
define('ENTRY_POSTCODE_ERROR_1', 'Указан несуществующий почтовый индекс.');
define('ENTRY_POSTCODE_TEXT', '');
define('ENTRY_CITY', 'Город / населённый пункт');
define('ENTRY_CITY_ERROR', 'Вы не указали город.');
define('ENTRY_CITY_TEXT', '');
if (DOMAIN_ZONE=='us') {
  define('ENTRY_POSTCODE', 'ZIP');
  define('ENTRY_STATE', 'Штат / провинция');
  define('ENTRY_STATE_ERROR', 'Вы не указали штат.');
 define('ENTRY_STATE_ERROR_SELECT', 'Выберите штат / провинцию.');
} else {
  define('ENTRY_POSTCODE', 'Почтовый индекс');
  define('ENTRY_STATE', 'Регион');
  define('ENTRY_STATE_ERROR', 'Вы не указали регион.');
  define('ENTRY_STATE_ERROR_SELECT', 'Выберите регион.');
}
define('ENTRY_STATE_TEXT', '');
define('ENTRY_COUNTRY', 'Страна');
define('ENTRY_COUNTRY_ERROR', 'Выберите страну.');
define('ENTRY_COUNTRY_TEXT', '<a href="' . tep_href_link('/delivery/international.html') . '">Вашей страны нет в этом списке?</a>');
define('ENTRY_TELEPHONE_NUMBER', 'Контактный телефон');
define('ENTRY_TELEPHONE_NUMBER_SHORT', 'Телефон');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Вы не указали номер контактного телефона.');
define('ENTRY_TELEPHONE_NUMBER_ERROR_1', 'Вы не указали междугородний код телефона.');
if (in_array(DOMAIN_ZONE, array('ua'))) {
  define('ENTRY_TELEPHONE_NUMBER_TEXT', 'в формате: 050-111-11-11');
} else {
  define('ENTRY_TELEPHONE_NUMBER_TEXT', '');
}

define('ENTRY_DUMMY_EMAIL_ADDRESS', 'Контактный email');
define('ENTRY_DUMMY_EMAIL_ADDRESS_ERROR', '');
define('ENTRY_DUMMY_EMAIL_ADDRESS_TEXT', 'Для информирования о ходе обработки заказа');

define('ENTRY_SELF_DELIVERY_ADDRESS_ERROR', 'Вы не выбрали пункт самовывоза.');
define('ENTRY_FAX_NUMBER', 'Дополнительный телефон');
define('ENTRY_FAX_NUMBER_ERROR', 'Вы не указали номер дополнительного телефона.');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER', 'Если хотите первыми узнавать о скидках, рекламных акциях, викторинах и новостях нашего магазина, то подпишитесь на информационную рассылку.');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_NEWSLETTER_YES', 'Да, я хочу подписаться на рассылку новостей');
define('ENTRY_NEWSLETTER_NO', 'Отказаться от подписки');
define('ENTRY_NEWSLETTER_ERROR', '');
define('ENTRY_WISHLIST', '');
define('ENTRY_PASSWORD', 'Пароль');
define('ENTRY_REMEMBER_ME', 'запомнить меня');
define('ENTRY_PASSWORD_ERROR', 'Ваш пароль должен содержать как минимум ' . ENTRY_PASSWORD_MIN_LENGTH . ' символов.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Поле «Подтвердите пароль» должно совпадать с полем «Пароль».');
define('ENTRY_PASSWORD_TEXT', '');
define('ENTRY_PASSWORD_CONFIRMATION', 'Подтвердите пароль');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '');
define('ENTRY_PASSWORD_CURRENT', 'Текущий пароль');
define('ENTRY_PASSWORD_CURRENT_TEXT', '');
define('ENTRY_PASSWORD_CURRENT_ERROR', 'Поле «Пароль» должно содержать как минимум ' . ENTRY_PASSWORD_MIN_LENGTH . ' символов.');
define('ENTRY_PASSWORD_NEW', 'Новый пароль:');
define('ENTRY_PASSWORD_NEW_TEXT', '');
define('ENTRY_PASSWORD_NEW_ERROR', 'Ваш новый пароль должен содержать как минимум ' . ENTRY_PASSWORD_MIN_LENGTH . ' символов.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'Поля «Подтвердите пароль» и «Новый пароль» должны совпадать.');
define('PASSWORD_HIDDEN', '--СКРЫТ--');
define('ENTRY_AGREEMENT_ERROR', 'Вы не указали, что согласны с условиями обслуживания.');
define('ENTRY_CAPTCHA_CHECK_ERROR', 'Введен неправильный проверочный код');
define('ENTRY_FEEDBACK', 'Вы можете оставить здесь свои комментарии или предложения, которые могли бы улучшить наш сервис:');

define('FORM_REQUIRED_INFORMATION', '* Обязательно для заполнения');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Страницы:');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS', 'Записи <b>%d</b> - <b>%d</b> (из <b>%d</b>)');
define('TEXT_DISPLAY_NUMBER_OF_RECORDS_PER_PAGE', 'Выводить по: %s');

define('PREVNEXT_TITLE_FIRST_PAGE', 'Первая страница');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'предыдущая');
define('PREVNEXT_TITLE_NEXT_PAGE', 'Следующая страница');
define('PREVNEXT_TITLE_LAST_PAGE', 'Последняя страница');
define('PREVNEXT_TITLE_PAGE_NO', 'Страница %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Предыдущие %d страниц');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Следующие %d страниц');
define('PREVNEXT_BUTTON_FIRST', '<');
define('PREVNEXT_BUTTON_PREV', '«');
define('PREVNEXT_BUTTON_NEXT', '»');
define('PREVNEXT_BUTTON_LAST', '>');

define('IMAGE_BUTTON_ADD', 'Добавить');
define('IMAGE_BUTTON_ADD_ADDRESS', 'Добавить адрес');
define('IMAGE_BUTTON_ADDRESS_BOOK', 'Адресная книга');
define('IMAGE_BUTTON_BACK', 'Назад');
define('IMAGE_BUTTON_BUY_NOW', 'Купить сейчас');
define('IMAGE_BUTTON_CALLBACK', 'Позвоните мне');
define('IMAGE_BUTTON_CHANGE_ADDRESS', 'Изменить адрес');
define('IMAGE_BUTTON_CHECKOUT', 'Оформить заказ');
define('IMAGE_BUTTON_CONFIRM_ORDER', 'Подтвердить заказ');
define('IMAGE_BUTTON_CONTINUE', 'Продолжить');
define('IMAGE_BUTTON_CONTINUE_SHOPPING', 'Продолжить покупки');
define('IMAGE_BUTTON_DELETE', 'Удалить');
define('IMAGE_BUTTON_DETAILS', 'Подробнее');
define('IMAGE_BUTTON_EDIT_ACCOUNT', 'Изменить учетные данные');
define('IMAGE_BUTTON_HISTORY', 'История заказов');
define('IMAGE_BUTTON_LOGIN', 'Войти');
define('IMAGE_BUTTON_IN_CART', 'В корзину');
define('IMAGE_BUTTON_IN_CART2', 'В корзине');
define('IMAGE_BUTTON_IN_CART3', 'Перейти в корзину');
define('IMAGE_BUTTON_IN_ORDER', 'Заказать');
define('IMAGE_BUTTON_IN_ORDER2', 'В заказе');
define('IMAGE_BUTTON_INSERT', 'Добавить');
define('IMAGE_BUTTON_PAY_FOR_ORDER', 'Оплатить заказ');
define('IMAGE_BUTTON_POSTPONE', 'Отложить');
define('IMAGE_BUTTON_POSTPONE2', 'Отложен');
define('IMAGE_BUTTON_POSTPONE3', 'Смотреть отложенные товары');
define('IMAGE_BUTTON_QUICK_SEARCH', 'Быстрый поиск');
define('IMAGE_BUTTON_QUICK_RESET', 'Сбросить');
define('IMAGE_BUTTON_REGISTER', 'Зарегистрироваться');
define('IMAGE_BUTTON_RESET_CART', 'Очистить корзину');
define('IMAGE_BUTTON_SEND', 'Отправить');
define('IMAGE_BUTTON_SEARCH', 'Искать');
define('IMAGE_BUTTON_UPDATE', 'Обновить');
define('IMAGE_BUTTON_UPDATE_CART', 'Пересчитать');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Добавить отзыв');

define('SMALL_IMAGE_BUTTON_DELETE', 'Удалить');
define('SMALL_IMAGE_BUTTON_EDIT', 'Изменить');
define('SMALL_IMAGE_BUTTON_VIEW', 'Смотреть');

define('ICON_ARROW_RIGHT', 'Перейти');
define('ICON_CART', 'В корзину');
define('ICON_ERROR', 'Ошибка');
define('ICON_SUCCESS', 'Выполнено');
define('ICON_WARNING', 'Внимание');

define('TEXT_SORT_PRODUCTS', 'Сортировать ');
define('TEXT_DESCENDINGLY', 'по убыванию');
define('TEXT_ASCENDINGLY', 'по возрастанию');
define('TEXT_BY', ' по полю: ');
define('TEXT_SORT_PRODUCTS_SHORT', 'Сортировка:');
define('TEXT_PER_PAGE', 'Товаров на странице:');

define('TEXT_FILTER_PRODUCTS_SHORT', 'Найти в списке:');
define('TEXT_FILTER_PRODUCTS_RESET', '[Сброс]');

define('TEXT_YES', 'Да');
define('TEXT_NO', 'Нет');

// votes box text
define('TEXT_REVIEW_BY', 'к %s');
define('TEXT_REVIEW_OF', 'отзыв/рецензия на');
define('TEXT_REVIEW_WORD_COUNT', '%s слова');
define('TEXT_REVIEW_RATING', 'Рейтинг: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Дата добавления: %s');
define('TEXT_NO_REVIEWS', '<p>К настоящему времени нет отзывов, Вы можете стать первым.</p>');
define('TEXT_REVIEW_VOTES_OF', '%s из %s');
define('TEXT_REVIEW_VOTE', 'Читали? Ваша оценка:');
define('TEXT_REVIEW_VOTED', 'Оценка посетителей (голосов: %s):');
define('TEXT_REVIEW_SUCCESS_VOTED', 'Ваш голос успешно учтен!');
define('TEXT_REVIEW_SUCCESS_ADDED', 'Ваш отзыв успешно добавлен!');
define('ENTRY_REVIEWS', 'Отзывы');
define('ENTRY_REVIEW_NAME', 'Ваше имя:');
define('ENTRY_REVIEW_EMAIL', 'E-mail:');
define('ENTRY_REVIEW_TEXT', 'Ваш отзыв о книге:');
define('ENTRY_REVIEW_STARS', 'Ваша оценка:');
define('ENTRY_REVIEW_NAME_ERROR', 'Вы не указали свое имя.');
define('ENTRY_REVIEW_EMAIL_ERROR', 'Вы не указали свой e-mail.');
define('REVIEW_TEXT_MIN_LENGTH', '30');
define('ENTRY_REVIEW_TEXT_ERROR', 'Длина текста Вашего отзыва должна быть не менее ' . REVIEW_TEXT_MIN_LENGTH . ' символов.');
define('TEXT_REVIEW_REGISTER', 'Отзывы могут оставлять только зарегистрированные пользователи. Пожалуйста, <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">авторизуйтесь</a> или <a href="' . tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL') . '">зарегистрируйтесь</a> (регистрация бесплатна и не  отнимет у Вас более минуты).');

define('JS_REVIEW_NAME', '* Вы не указали свое имя.\n');
define('JS_REVIEW_EMAIL', '* Вы не указали свой e-mail.\n');
define('JS_REVIEW_TEXT', '* Поле "Отзыв" должно содержать не менее ' . REVIEW_TEXT_MIN_LENGTH . ' символов.\n');
define('JS_REVIEW_RATING', '* Оцените, пожалуйста, продукт по пятибальной шкале.\n');

define('TEXT_UNKNOWN_TAX_RATE', 'Налоговая ставка неизвестна');

define('TEXT_REQUIRED', '<span class="errorText">Обязательно</span>');

define('ERROR_TEP_MAIL', 'Ошибка: Невозможно отправить email через сервер SMTP. Проверьте, пожалуйста, Ваши установки php.ini и если необходимо, скорректируйте сервер SMTP.</b></font>');
define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Предупреждение: Не удалена директория установки магазина: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/install. Пожалуйста, удалите эту директорию для безопасности.');
define('WARNING_CONFIG_FILE_WRITEABLE', 'Предупреждение: Файл конфигурации доступен для записи: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php. Это - потенциальный риск безопасности - пожалуйста, установите необходимые права доступа к этому файлу.');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Предупреждение: директория сессий не существует: ' . tep_session_save_path() . '. Сессии не будут работать пока эта директория не будет создана.');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Предупреждение: Нет доступа к каталогу сессий: ' . tep_session_save_path() . '. Сессии не будут работать пока не установлены необходимые права доступа.');
define('WARNING_SESSION_AUTO_START', 'Предупреждение: опция session.auto_start включена - пожалуйста, выключите данную опцию в файле php.ini и перезапустите веб-сервер.');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Предупреждение: Директория отсутствует: ' . DIR_FS_DOWNLOAD . '. Создайте директорию.');

define('TEXT_CCVAL_ERROR_INVALID_DATE', 'Вы указали неверную дату истечения срока действия кредитной карточки.<br />Попробуйте ещё раз.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'Вы указали неверный номер кредитной карточки.<br />Попробуйте ещё раз.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'Первые цифры Вашей кредитной карточки: %s<br />Если Вы указали номер своей кредитной карточки правильно, сообщаем Вам, что мы не принимаем к оплате данный тип кредитных карточек.<br />Если Вы указали номер кредитной карточки неверно, попробуйте ещё раз.');

define('ENTRY_GUEST_ADD_TO_CART_ERROR', 'Чтобы иметь возможность добавлять товары в корзину, пожалуйста, авторизуйтесь или зарегистрируйтесь!');

define('TABLE_HEADING_IMAGE', '');
define('TABLE_HEADING_PICTURE', 'Фото');
define('TABLE_HEADING_NAME', 'Наименование');
define('TABLE_HEADING_DESCRIPTION', 'Краткое описание');
define('TABLE_HEADING_MODEL', 'ISBN');
define('TABLE_HEADING_PRODUCTS', 'Наименование');
define('TABLE_HEADING_MANUFACTURER', 'Издатель');
define('TABLE_HEADING_MANUFACTURER_1', 'Производитель');
define('TABLE_HEADING_AUTHOR', 'Автор');
define('TABLE_HEADING_YEAR', 'Год');
define('TABLE_HEADING_TYPE', 'Тип товара');
define('TABLE_HEADING_QUANTITY', '<nobr>Кол-во</nobr>');
define('TABLE_HEADING_PRICE', 'Цена');
define('TABLE_HEADING_SUM', 'Сумма');
define('TABLE_HEADING_WEIGHT', 'Вес');
define('TABLE_HEADING_BUY_NOW', 'Заказ');
define('TABLE_HEADING_SUBTOTAL', 'Итого');
define('TABLE_HEADING_TOTAL', 'Сумма');
define('TABLE_HEADING_REMOVE', 'Удалить');

define('TEXT_NO_PRODUCTS', 'Нет ни одного товара в этом разделе.');
define('TEXT_NO_PRODUCTS2', 'Нет ни одного товара данного издателя.');
define('TEXT_NO_NEW_PRODUCTS', 'Сегодня новинок нет.');
define('TEXT_NO_SPECIALS', 'Сегодня нет специальных предложений.');
define('TEXT_NO_SALES', 'Сегодня нет распродаж.');

define('TEXT_FROM', 'от');
define('TEXT_TO', 'до');

define('TEXT_PRODUCT_NOT_AVAILABLE', 'Начало продаж: %s');
define('TEXT_PRODUCT_NOT_AVAILABLE_1', 'Сейчас нет в наличии');
define('TEXT_PRODUCT_NOT_AVAILABLE_SHORT', 'Нет в продаже');
define('TEXT_PRODUCT_NOT_AVAILABLE_1_TEXT', '<p>В настоящий момент книги нет ни на нашем складе, ни у наших поставщиков, но:</p>	<ol style="margin-left: 15px; padding: 0px;">	<li>В скором времени она может вновь появиться в продаже, узнать об этом вы сможете, отложив книгу и подписавшись на получение соответствующего уведомления;</li>	<li><a href="/contacts.html">Напишите нам</a> и мы попробуем заказать печать этой книги (от 1 экз.) или попытаемся найти ее, подключив все имеющиеся у нас ресурсы;</li>	<li>Вы можете <a href="/boards/buy/?action=new&products_id=%s">разместить объявление о покупке</a> этой книги на нашей доске объявлений;</li>	</ol>');
define('TEXT_PRODUCT_NOT_AVAILABLE_2', 'Скоро в продаже');
define('TEXT_NUMBER_OF_PRODUCTS', 'Кол-во: ');
define('TEXT_MANUFACTURER', 'Издательство:');
define('TEXT_MANUFACTURER_1', 'Производитель:');
define('TEXT_MODEL', 'ISBN:');
define('TEXT_MODEL_1', 'Артикул:');
define('TEXT_NAME', 'Наименование:');
define('TEXT_PRICE', 'Цена:');
define('TEXT_CORPORATE_PRICE', 'Ваша цена:');
define('TEXT_URL', 'Ссылка:');
define('TEXT_GENRE', 'Жанр:');
define('TEXT_LANGUAGE', 'Язык:');
define('TEXT_SERIE', 'Серия:');
define('TEXT_WARRANTY', 'Гарантия, месяцев:');
define('TEXT_AUTHOR', 'Автор:');
define('TEXT_AUTHORS', 'Авторы:');
define('TEXT_CODE', 'Код товара для заказа по телефону: <strong class="errorText">%s</strong>');
define('TEXT_YEAR', 'г.');
define('TEXT_YEAR_FULL', 'Год:');
define('TEXT_PERIODICITY', 'Периодичность: %s номеров в год');
define('TEXT_PERIODICITY_1', 'Периодичность: %s номер в год');
define('TEXT_PERIODICITY_2', 'Периодичность: %s номера в год');
define('TEXT_WEEK_1', '%s-я неделя');
define('TEXT_WEEK_2', '%s-я неделя');
define('TEXT_WEEK_3', '%s-я неделя');
define('TEXT_WEEK', '%s-я неделя');
define('TEXT_SUBSCRIBE_TO', 'Подписка на');
define('TEXT_SUBSCRIBE_TO_SHORT', 'Подписка');
define('TEXT_SUBSCRIBE_TO_1_MONTH', '1 месяц');
define('TEXT_SUBSCRIBE_TO_3_MONTHES', '3 месяца');
define('TEXT_SUBSCRIBE_TO_HALF_A_YEAR', 'полгода');
define('TEXT_SUBSCRIBE_TO_YEAR', 'год');
define('TEXT_MONTHES', 'мес.');
define('TEXT_COVER', 'Обложка:');
define('TEXT_FORMAT', 'Формат:');
define('TEXT_ADDITIONAL_IMAGES_1', 'Есть отрывки для ознакомления');
define('TEXT_WEIGHT', 'Вес:');
define('TEXT_WEIGHT_GRAMMS', 'г');
define('TEXT_WEIGHT_KILOGRAMMS', 'кг');
define('TEXT_PAGES_COUNT', 'Кол-во страниц:');
define('TEXT_COPIES', 'Тираж:');
define('TEXT_QTY', 'Количество');
define('TEXT_AVAILABLE_IN', 'Дата передачи в службу доставки:');
define('TEXT_AVAILABLE_IN_FOREIGN', 'Срок доставки: %s дней.');
define('TEXT_BUY_NOW', 'В корзину');
define('TEXT_ALL_CATEGORY_PRODUCTS', 'Все товары раздела');
define('TEXT_ALL_CATEGORIES', '- Все рубрики -');
define('TEXT_ALL_MANUFACTURERS', '-  Все издательства -');
define('TEXT_ALL_SERIES', '- Все серии -');
define('TEXT_ALL_AUTHORS', '- Все авторы -');
define('TEXT_CUSTOMIZE_CATEGORY', 'Выберите рубрику:');
define('TEXT_RESET_SORTING', '[Сброс]');
define('TEXT_RESET_SORTING_TEXT', 'Сбросить установленную сортировку');
define('TEXT_CUSTOMIZE_KEYWORD', 'Поиск по списку:');
define('TEXT_INPUT_KEYWORD', '');
define('TEXT_CLICK_TO_ENLARGE', 'Увеличить');
define('TEXT_CLOSE_WINDOW', 'Закрыть окно');

define('TEXT_CATEGORY_SUBSCRIBE', 'Подписаться на новинки');
define('TEXT_CATEGORY_SUBSCRIBE_ALT', 'Если Вам нравится %s, подпишитесь!');
define('TEXT_CATEGORY_UNSUBSCRIBE', 'Отписаться от новинок');
define('TEXT_CATEGORY_MESSAGE', 'Вы подписаны на новинки');
define('TEXT_CATEGORY_TYPE', 'раздела:серии:автора:издательства');
define('TEXT_CATEGORY_TYPE_ALT', 'тематика:серия:автор:издательство');
define('TEXT_CATEGORY_ERROR', 'Подписаться могут только зарегистрированные пользователи. Пожалуйста, <a href="%s"> авторизуйтесь</a> или <a href="%s">зарегистрируйтесь</a>.');

define('TEXT_SUBSCRIBE_SECTION', 'Новинки раздела каталога');
define('TEXT_SUBSCRIBE_SERIES', 'Новинки серии');
define('TEXT_SUBSCRIBE_AURHORS', 'Новинки от автора');
define('TEXT_SUBSCRIBE_MUNUFACTURERS', 'Новинки издательства');

define('SUCCESS_SUBSCRIBE', 'Изменения сохранены');
?>