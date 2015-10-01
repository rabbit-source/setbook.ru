<?php
define('HEADING_TITLE', 'Новости и анонсы');

define('TABLE_HEADING_NEWS', 'Новости');
define('TABLE_HEADING_TYPES', 'Типы новостей');

define('TEXT_NEW_NEWS', 'Добавить новость');
define('TEXT_NEWS', 'Новостей:');
define('TEXT_NEWS_DATE_ADDED', 'Эта новость была добавлена %s.');

define('TEXT_EDIT_INTRO', 'Пожалуйста, внесите необходимые изменения');

define('TEXT_INFO_HEADING_DELETE_NEWS', 'Удалить новость');
define('TEXT_DELETE_NEWS_INTRO', 'Вы действительно хотите удалить эту новость?');

define('TEXT_NEWS_TYPE', 'Тип новости:');
define('TEXT_NEWS_STATUS', 'Статус новости:');
define('TEXT_NEWS_SHOPS', 'Разместить новость на сайтах:');
define('TEXT_NEWS_ACTIVE', 'Доступна');
define('TEXT_NEWS_NOT_ACTIVE', 'Не доступна');
define('TEXT_NEWS_DATE', 'Дата новости:');
define('TEXT_NEWS_NAME', 'Заголовок новости:');
define('TEXT_NEWS_IMAGE', 'Рисунок новости:');
define('TEXT_NEWS_DESCRIPTION', 'Содержание новости:');
define('TEXT_NEWS_CATEGORY', 'Рубрика/раздел/ключевое слово:');
define('TEXT_NEWS_CATEGORY_TEXT', 'Если нет в списке:');
define('TEXT_IMAGE_NONEXISTENT', 'ФОТО ОТСУТСТВУЕТ');
define('TEXT_NEWS_PRODUCTS', 'Список ID товаров, относящихся к новости:<br><small>(каждый с новой строки)</small>');

define('TEXT_NEWS_ACTIONS', 'Дополнительные настройки (только для акций)');
define('TEXT_NEWS_ACTIONS_EXPIRES_DATE', 'Срок действия акции до:');
define('TEXT_NEWS_ACTIONS_PRODUCTS_DISCOUNT', 'Скидка на указанные товары:');

define('TEXT_NEWS_ACTIONS_SPECIALS', 'Дополнительные спецпредложения в рамках акции:');
define('TEXT_NEWS_ACTIONS_SPECIALS_PRODUCTS', 'Список ID товаров-спецпредложений:');
define('TEXT_NEWS_ACTIONS_SPECIALS_DISCOUNT', 'скидка на спецпредложения:');
define('TEXT_NEWS_ACTIONS_SPECIALS_DISCOUNT_TEXT', '(укажите 100%, если хотите, чтобы спецпредложение стало подарком)');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS', 'условия предоставления спецпредложения:');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_SUM', 'стоимость заказа - не менее');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_SUM_CURRENCY', 'валюта: ');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_SUM_TEXT', '(если нет ограничений по сумме заказа, введите ноль)');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_PRESENCE', 'наличие в заказе перечисленных в списке товаров-спецпредложений:');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_PRESENCE_ANY', 'любого');
define('TEXT_NEWS_ACTIONS_SPECIALS_CONDITIONS_PRESENCE_ALL', 'всех');
define('TEXT_NEWS_ACTIONS_SPECIALS_ORDER', 'Из указанных товаров-спецпредложений позволить заказывать:');
define('TEXT_NEWS_ACTIONS_SPECIALS_ORDER_ANY', 'любые товары из списка');
define('TEXT_NEWS_ACTIONS_SPECIALS_ORDER_ONLY', 'только один товар из списка');
define('TEXT_NEWS_ACTIONS_SPECIALS_ADD', 'Добавлять товар-спецпредложение в корзину автоматически:');
define('TEXT_NEWS_ACTIONS_SPECIALS_ADD_TEXT', '(если соблюдены условия предоставления спецпредложения и в списке товаров-спецпредложений только одно наименование, этот товар будет автоматически добавлен в заказ клиента)');
define('TEXT_NEWS_ACTIONS_SPECIALS_ADD_AUTO', 'автоматически');
define('TEXT_NEWS_ACTIONS_SPECIALS_ADD_MANUAL', 'пользователем вручную');
//define('TEXT_NEWS_ACTIONS_SPECIALS', '');

define('TABLE_HEADING_DATE', 'Дата');
define('TABLE_HEADING_INFO', 'Инфо');
define('TABLE_HEADING_STATUS', 'Статус');

define('TEXT_DATE_ADDED', 'Дата добавления:');

define('TEXT_CHOOSE_YEAR', '- Год -');
define('TEXT_CHOOSE_MONTH', '- Месяц -');
define('TEXT_CHOOSE_TYPE', '- Тип новостей -');

$months_names = array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');

define('TEXT_INFO_HEADING_NEW_TYPE', 'Новый тип новостей');
define('TEXT_INFO_HEADING_EDIT_TYPE', 'Редактирование типа новостей');
define('TEXT_NEW_TYPE_INTRO', 'Заполните необходимые поля, чтобы добавить новый тип новостей');
define('TEXT_EDIT_TYPE_INTRO', 'Внесите необходимые изменения');
define('TEXT_EDIT_TYPES_NAME', 'Название типа новостей:');
define('TEXT_EDIT_TYPES_SHORT_DESCRIPTION', 'Краткое описание типа новостей:');
define('TEXT_EDIT_TYPES_DESCRIPTION', 'Полное описание типа новостей:');
define('TEXT_EDIT_TYPES_SORT_ORDER', 'Порядок вывода:');
define('TEXT_REWRITE_NAME', 'Задайте путь в каталоге:');

define('TEXT_INFO_DATE_ADDED', 'Дата добавления:');
define('TEXT_INFO_LAST_MODIFIED', 'Последнее изменение:');

define('TEXT_INFO_HEADING_DELETE_TYPE', 'Удаление типа новостей');
define('TEXT_INFO_DELETE_TYPE_INTRO', 'Вы действительно хотите удалить этот тип новостей?');

define('TEXT_INSTALL_NEWS', 'Установка модуля новостей');
define('TEXT_MAX_DISPLAY_NEWS', 'Макс. кол-во новостей в блоке "Новости и анонсы":');
define('CONFIG_NEWS_MAX_DISPLAY', 'Макс. кол-во новостей в блоке "Новости и анонсы"');
define('CONFIG_NEWS_MAX_DISPLAY_DESCRIPTION', 'Укажите кол-во новостей, которые одновременно смогут отображаться в блоке "Новости и анонсы"');
define('TEXT_MAX_DISPLAY_NEWS_RESULTS', 'Кол-во новостей на странице при постраничном выводе:');
define('CONFIG_NEWS_MAX_DISPLAY_RESULTS', 'Кол-во новостей на странице');
define('CONFIG_NEWS_MAX_DISPLAY_RESULTS_DESCRIPTION', 'Укажите кол-во новостей на странице при постраничном выводе списка новостей');
?>