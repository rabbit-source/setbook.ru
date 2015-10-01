<?php
define('HEADING_TITLE', 'Разделы и страницы сайта');
define('HEADING_TITLE_SEARCH', 'Поиск:');
define('HEADING_TITLE_GOTO', 'Перейти в:');

define('DEBUG_MODES_DISALLOW', 'Запретить:');
define('DEBUG_MODES_DISALLOW_CREATE', 'Создание подразделов');
define('DEBUG_MODES_DISALLOW_MOVE', 'Перемещение');
define('DEBUG_MODES_DISALLOW_EDIT', 'Редактирование');
define('DEBUG_MODES_DISALLOW_DELETE', 'Удаление');
define('DEBUG_MODES_DISALLOW_ALL', 'Все операции');

define('WARNING_SECTION_CREATE_DISABLED', 'У Вас недостаточно прав для создания подразделов.');
define('WARNING_SECTION_EDIT_DISABLED', 'У Вас недостаточно прав для редактирование этого раздела.');
define('WARNING_SECTION_MOVE_DISABLED', 'У Вас недостаточно прав для перемещение этого раздела.');
define('WARNING_SECTION_DELETE_DISABLED', 'У Вас недостаточно прав для удаления этого раздела.');
define('WARNING_INFORMATION_CREATE_DISABLED', 'У Вас недостаточно прав для добавления новых статей в этот раздел.');
define('WARNING_INFORMATION_MOVE_DISABLED', 'У Вас недостаточно прав для перемещение статей этого раздела в другие разделы.');
define('WARNING_INFORMATION_EDIT_DISABLED', 'У Вас недостаточно прав для редактирования этой статьи.');
define('WARNING_INFORMATION_DELETE_DISABLED', 'У Вас недостаточно прав для удаления этой статьи.');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_SECTIONS_INFORMATIONS', 'Разделы / Страницы');
define('TABLE_HEADING_INFO', 'Инфо');
define('TABLE_HEADING_STATUS', 'Статус');

define('TEXT_NEW_INFORMATION', 'Новая страница в разделе &quot;%s&quot;');
define('TEXT_EDIT_INFORMATION', 'Редактирование страницы &quot;%s&quot; в разделе &quot;%s&quot;');
define('TEXT_SECTIONS', 'Разделы:');
define('TEXT_SUBSECTIONS', 'Подразделы:');
define('TEXT_INFORMATIONS', 'Статьи:');
define('TEXT_DATE_ADDED', 'Дата добавления:');
define('TEXT_LAST_MODIFIED', 'Последнее изменение:');
define('TEXT_NO_CHILD_SECTIONS_OR_INFORMATIONS', 'Пожалуйста добавьте новый раздел или страницу');
define('TEXT_INFORMATION_DATE_ADDED', 'Эта статья была добавлена %s.');

define('TEXT_EDIT_INTRO', 'Пожалуйста внесите необходимые изменения');
define('TEXT_EDIT_SECTIONS_NAME', 'Название раздела:');
define('TEXT_EDIT_SECTIONS_DESCRIPTION', 'Краткое описание раздела:');
define('TEXT_EDIT_SORT_ORDER', 'Порядок сортировки:');

define('TEXT_INFO_HEADING_NEW_SECTION', 'Новый раздел');
define('TEXT_INFO_HEADING_EDIT_SECTION', 'Редактировать раздел');
define('TEXT_INFO_HEADING_MOVE_SECTION', 'Переместить раздел');
define('TEXT_INFO_HEADING_DELETE_SECTION', 'Удалить раздел');
define('TEXT_INFO_HEADING_DELETE_INFORMATION', 'Удалить страницу');

define('TEXT_DELETE_SECTION_INTRO', 'Вы действительно хотите удалить этот раздел?');
define('TEXT_DELETE_INFORMATION_INTRO', 'Вы действительно хотите удалить эту страницу?');

define('TEXT_DELETE_WARNING_CHILDS', '<strong>ПРЕДУПРЕЖДЕНИЕ:</strong> К этому разделу привязано %s подразделов!');
define('TEXT_DELETE_WARNING_INFORMATIONS', '<strong>ПРЕДУПРЕЖДЕНИЕ:</strong> К этому разделу привязано %s статей!');

define('TEXT_NEW_SECTION_INTRO', 'Пожалуйста заполните следующие поля для добавления нового раздела');
define('TEXT_SECTIONS_NAME', 'Название раздела:');
define('TEXT_SECTIONS_DESCRIPTION', 'Краткое описание раздела:');
define('TEXT_SECTIONS_TEMPLATE', 'Выберите шаблон, который будет использоваться для страниц этого раздела:');
define('TEXT_SECTIONS_DEFAULT_INFORMATION', 'Выберите главную страницу раздела:');
define('TEXT_SORT_ORDER', 'Порядок сортировки:');
define('TEXT_SECTIONS_STATUS', 'Раздел активен');
define('TEXT_SECTIONS_LISTING_STATUS', 'Включить раздел в навигацию');
define('TEXT_SECTIONS_SITEMAP_STATUS', 'Включить раздел в карту сайта');
define('TEXT_REWRITE_NAME', 'URL (путь к странице):');
define('TEXT_MOVE_SECTIONS_INTRO', 'Выберите раздел, в который Вы хотите переместить <strong>%s</strong>');
define('TEXT_MOVE', 'Переместить <strong>%s</strong> в:');

define('TEXT_INFORMATION_STATUS', 'Показывать страницу на сайте:');
define('TEXT_INFORMATION_LISTING_STATUS', 'Включить страницу в навигацию сайта:');
define('TEXT_INFORMATION_AVAILABLE', 'Да');
define('TEXT_INFORMATION_NOT_AVAILABLE', 'Нет');
define('TEXT_INFORMATION_DEFAULT_STATUS', 'Сделать эту страницу главной в разделе &laquo;%s&raquo;');
define('TEXT_INFORMATION_SITEMAP_STATUS', 'Выводить страницу на карте сайта:');
define('TEXT_INFORMATION_SECTION', 'Разместить страницу в разделах:');
define('TEXT_INFORMATION_NAME', 'Название страницы:<br><span class="smallText">(используется в навигации на сайте)</span>');
define('TEXT_INFORMATION_DESCRIPTION', 'Текст на странице:');
define('TEXT_INFORMATION_REDIRECT', 'Перенаправлять на страницу (редирект):');

define('EMPTY_SECTION', 'Раздел пуст');

define('ERROR_INFORMATION_PATH_EXISTS', 'Ошибка! Указанный URL (путь к странице) уже существует!');
define('ERROR_SECTION_PATH_EXISTS', 'Ошибка! Указанный URL (путь к странице) уже существует, либо совпадает с именем существующей директории!');
define('ERROR_PATH_EMPTY', 'Ошибка! Не задан URL (путь к странице)!');
define('ERROR_PATH_ALREADY_EXISTS', 'Ошибка! В указанном разделе уже существует подраздел, имеющий такой же путь!');
define('ERROR_CANNOT_MOVE_SECTION_TO_PARENT', 'Ошибка: Раздел не может быть перемещен в свой подраздел!');

define('TEXT_CHOOSE', '- Выберите из списка -');
?>