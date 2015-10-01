<?php
if ($type=='dynamic') {
  define('HEADING_TITLE', 'Динамические блоки информации');
} else {
  define('HEADING_TITLE', 'Статические блоки информации');
}
define('TEXT_TYPES_STYLE_STATIC', 'Статические блоки');
define('TEXT_TYPES_STYLE_DYNAMIC', 'Динамические разделы');
define('TEXT_TYPES_STYLE_MANUFACTURERS', 'Производители');
define('TEXT_TYPES_STYLE_INFORMATION', 'Информационные статьи');
define('TEXT_TYPES_STYLE_CATEGORIES', 'Категории товаров');
define('TEXT_TYPES_STYLE_PRODUCTS', 'Товары');
define('TEXT_TYPES_STYLE_NEWS', 'Новости и анонсы');
define('TEXT_TYPES_STYLE_PAGES', 'Страницы сайта');
define('TEXT_TYPES_STYLE_SERIES', 'Серии товаров');
define('TEXT_TYPES_STYLE_SPECIALS', 'Спецпредложения');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_TYPES_BLOCKS', 'Типы блоков / Блоки информации');
define('TABLE_HEADING_STATUS', 'Статус');
define('TABLE_HEADING_ACTION', 'Действие');
define('HEADING_TITLE_GOTO', 'Смотреть:');

define('TEXT_NEW_BLOCK', 'Новый блок информации');
define('TEXT_TYPES', 'Типов:');
define('TEXT_BLOCKS', 'Блоков:');
define('TEXT_DATE_ADDED', 'Дата добавления:');
define('TEXT_LAST_MODIFIED', 'Последнее изменение:');
define('TEXT_NO_CHILD_TYPES_OR_BLOCKS', 'Добавьте, пожалуйста, новый тип блоков или новый блок информации');
define('TEXT_BLOCK_DATE_ADDED', 'Блок был добавлен %s.');
define('TEXT_ALLOW_TEMPLATES', 'Этот тип блоков доступен в следующих шаблонах:');

define('TEXT_EDIT_INTRO', 'Пожалуйста, внесите необходимые изменения');
define('TEXT_EDIT_TYPES_NAME', 'Название типа блоков:');

define('TEXT_INFO_HEADING_NEW_TYPE', 'Новый тип блоков');
define('TEXT_INFO_HEADING_EDIT_TYPE', 'Изменить тип блоков');
define('TEXT_INFO_HEADING_DELETE_TYPE', 'Удалить тип блоков');
define('TEXT_INFO_HEADING_NEW_BLOCK', 'Новый блок информации');
define('TEXT_INFO_HEADING_EDIT_BLOCK', 'Изменить блок информации');
define('TEXT_INFO_HEADING_DELETE_BLOCK', 'Удалить блок информации');

define('TEXT_DELETE_TYPE_INTRO', 'Вы действительно хотите удалить этот тип блоков?');
define('TEXT_DELETE_BLOCK_INTRO', 'Вы действительно хотите удалить этот блок информации?');

define('TEXT_DELETE_WARNING_BLOCKS', '<strong>ВНИМАНИЕ:</strong> Есть еще %s блоков информации, связанных с этим типом!');

define('TEXT_NEW_TYPE_INTRO', 'Пожалуйста, заполните следующую информацию для добавления нового типа блоков');
define('TEXT_TYPES_IDENTIFICATOR', 'Уникальный идентификатор типа:<br>(только цифры и латинские буквы)');
define('TEXT_TYPES_NAME', 'Название типа:');
define('TEXT_TYPES_DESCRIPTION', 'Краткое описание типа блоков:');
define('TEXT_TYPES_FIELD', 'Тип поля ввода для блоков этого типа:');
define('TEXT_TYPES_FIELD_HTML', 'Разрешить html-тэги (только для поля типа &lt;textarea&gt;)');
define('TEXT_TYPES_FIELD_EDITOR', 'Использовать редактор HTML');
define('TEXT_TYPES_MOVE', 'Разрешить перемещать блоки этого типа в:');
define('TEXT_TYPES_MULTIPLE', 'Разрешить включать несколько блоков этого типа на странице');
define('TEXT_TYPES_TYPE', 'Этот тип блоков доступен только для следующего вида контента:');
define('TEXT_SORT_ORDER', 'Порядок вывода');

define('TEXT_NEW_BLOCK_INTRO', 'Пожалуйста, заполните следующую информацию для добавления нового блока информации');
define('TEXT_BLOCKS_NAME', 'Название блока:');
define('TEXT_BLOCKS_DESCRIPTION', 'Содержимое блока:');
define('TEXT_BLOCKS_FILENAME', 'Либо загрузите файл с PHP-кодом:');
define('TEXT_BLOCKS_FILENAME_CONTENT', 'Либо отредактируйте PHP-код блока:<br><small>Очистите содержимое, если хотите <br>удалить файл блока</small>');
define('TEXT_BLOCKS_FILENAME_CONTENT_REWRITE', 'Перезаписать PHP-код блока');
define('TEXT_SORT_ORDER', 'Порядок вывода:');
define('TEXT_BLOCKS_DEFAULT_STATUS', 'Присутствие этого блока обязательно:');
define('TEXT_BLOCKS_STATUS', 'Показывать блок на сайте:');
define('TEXT_BLOCKS_REMOVE', 'Удалить этот блок со всех страниц:');
define('TEXT_BLOCKS_TEMPLATES', 'Этот блок используется в шаблонах:');
define('TEXT_BLOCKS_MOVE', 'Переместить этот блок в:');
define('TEXT_BLOCKS_IDENTIFICATOR', 'Уникальный идентификатор блока:<br><span class="smallText">(только цифры и латинские буквы)</span>');
define('TEXT_BLOCKS_TYPE', 'Этот блок доступен только для следующего вида контента:');

define('EMPTY_TYPE', 'В этом типе блоков нет ни одного блока');
define('TEXT_BLOCK_NOT_EDITABLE', 'В этом блоке используется программный код. Редактирование блока невозможно.');

define('TEXT_FIELD_INPUT', '<input> (до 255 символов)');
define('TEXT_FIELD_TEXTAREA_VARCHAR', '<textarea> (до 255 символов)');
define('TEXT_FIELD_TEXTAREA_TEXT', '<textarea> (более 255 символов)');

define('TEXT_TYPES_STYLE_STATIC', 'Статические блоки');
define('TEXT_TYPES_STYLE_SECTIONS', 'Информационные разделы');
define('TEXT_TYPES_STYLE_INFORMATION', 'Информационные статьи');
define('TEXT_TYPES_STYLE_CATEGORIES', 'Категории товаров');
define('TEXT_TYPES_STYLE_PRODUCTS', 'Товары');
define('TEXT_TYPES_STYLE_PAGES', 'Страницы сайта');

define('ERROR_BLOCK_EXIST', 'Ошибка: Блок с идентификатором "%s" уже существует!');
define('ERROR_EMPTY_BLOCK_TYPE', 'Ошибка: Не выбран тип блоков!');
define('ERROR_EMPTY_TYPE', 'Ошибка: Не указан уникальный идентификатор!');
define('ERROR_FILE_NO_WRITEABLE', 'Ошибка: Файл недоступен для записи! Пожалуйста, установите нужные права доступа!');
define('TEXT_FILE_NOT_FOUND', 'Файл не найден:');
?>