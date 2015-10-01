<?php
if (tep_not_null($HTTP_GET_VARS['pPath'])) {
  define('HEADING_TITLE', 'Редактирование фраз, используемых на странице "%s"');
} else {
  define('HEADING_TITLE', 'Страницы сайта');
}
define('HEADING_TITLE_1', '%s - &laquo;%s&raquo;');

define('TABLE_HEADING_PAGES', 'Страницы сайта');
define('TABLE_HEADING_ACTION', 'Действие');
define('TABLE_HEADING_INFO', 'Инфо');

define('TABLE_HEADING_TRANSLATION_DESCRIPTION', 'Описание');
define('TABLE_HEADING_TRANSLATION_KEY', 'Ключ');
define('TABLE_HEADING_TRANSLATION_VALUE', 'Текст фразы');

define('TEXT_HEADING_EDIT_PAGE', 'Редактировать содержание страницы');
define('TEXT_INFO_HEADING_TRANSLATION', 'Информация о фразе');
define('TEXT_HEADING_NEW_TRANSLATION', 'Добавление новой фразы (слова)');
define('TEXT_HEADING_EDIT_TRANSLATION', 'Редактирование фразы (слова)');
define('TEXT_HEADING_DELETE_TRANSLATION', 'Удаление фразы');

define('TEXT_PAGES', 'Страницы сайта:');
define('TEXT_PAGE_NAME', 'Название страницы:<br><small>(для навигационной строки)</small>');
define('TEXT_PAGE_ADDITIONAL_DESCRIPTION', 'Дополнительный текст над заголовком страницы:<br /><small>(необязательно)</small>');
define('TEXT_PAGE_DESCRIPTION', 'Текст на странице:<br /><small>(необязательно)</small>');
define('TEXT_PAGE_FILENAME', 'Файл страницы:');

define('TEXT_DATE_ADDED', 'Дата добавления:');
define('TEXT_LAST_MODIFIED', 'Последнее изменение:');

define('TEXT_NEW_TRANSLATION_INTRO', 'Заполните все поля для добавления новой фразы');
define('TEXT_EDIT_TRANSLATION_INTRO', 'Задайте новые значения фразы');
define('TEXT_DELETE_TRANSLATION_INTRO', 'Вы действительно хотите удалить фразу');
define('TEXT_TRANSLATION_DESCRIPTION', 'Описание фразы:');
define('TEXT_SORT_ORDER', 'Порядок вывода:');
define('TEXT_TRANSLATION_KEY', 'Ключ:');
define('TEXT_TRANSLATION_VALUE', 'Текст фразы:');

define('TEXT_SUCCESS_PAGES_ADDED', 'Добавлены новые страницы (%s)');
define('TEXT_SUCCESS_PAGES_DELETED', 'Удалены страницы сайта (%s)');

define('ERROR_EMPTY_TRANSLATION_KEY', 'Ошибка! Не указан ключ!');
define('ERROR_TRANSLATION_KEY_EXIST', 'Ошибка! Указанный ключ (%s) уже используется! Пожалуйста, выберите другой!');
?>