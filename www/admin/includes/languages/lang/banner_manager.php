<?php
define('HEADING_TITLE', 'Менеджер баннеров');

define('TABLE_HEADING_BANNERS', 'Баннеры');
define('TABLE_HEADING_GROUPS', 'Группы баннеров');
define('TABLE_HEADING_WEIGHT', '"Вес"');
define('TABLE_HEADING_STATISTICS', 'Показы / Клики (CTR)');
define('TABLE_HEADING_STATUS', 'Статус');
define('TABLE_HEADING_INFO', 'Инфо');

define('TEXT_INFO_HEADING_EDIT_GROUP', 'Редактирование группы');
define('TEXT_INFO_HEADING_NEW_GROUP', 'Новая группа баннеров');
define('TEXT_INFO_HEADING_DELETE_GROUP', 'Удалить группу');
define('TEXT_EDIT_GROUP_INTRO', 'Пожалуйста, внесите необходимые изменения');
define('TEXT_NEW_GROUP_INTRO', 'Пожалуйста, заполните поля для добавления новой группы');
define('TEXT_DELETE_GROUP_INTRO', 'Вы действительно хотите удалить эту группу баннеров?');

define('TEXT_INFO_DELETE_INTRO', 'Вы действительно хотите удалить этот баннер?');
define('TEXT_INFO_DELETE_IMAGE', 'Удалить баннер');

define('TEXT_GROUP_NAME', 'Название группы:');
define('TEXT_GROUP_PATH', 'Идентификатор группы:');
define('TEXT_BANNER_GROUP_CONDITIONS', 'Условия показа баннеров этой группы:');
define('TEXT_CONDITION_N', 'Условие #%s');
define('TEXT_PAGES_TYPE_VIRTUAL', 'Виртуальный адрес страницы');
define('TEXT_PAGES_TYPE_PHYSICAL', 'Физический адрес страницы');
define('TEXT_PAGES_CONSIST_MATCH', 'Совпадает с');
define('TEXT_PAGES_CONSIST_NOT_MATCH', 'Не совпадает с');
define('TEXT_PAGES_CONSIST_BEGIN', 'Начинается с');
define('TEXT_PAGES_CONSIST_NOT_BEGIN', 'Не начинается с');
define('TEXT_PAGES_CONSIST_CONTAIN', 'Содержит');
define('TEXT_PAGES_CONSIST_NOT_CONTAIN', 'Не содержит');
define('TEXT_PAGES_CONSIST_END', 'Заканчивается на');
define('TEXT_PAGES_CONSIST_NOT_END', 'Не заканчивается на');
define('TEXT_CONDITION_EQUATION', 'Формула применения условий:');
define('TEXT_CONDITION_EQUATION_TEXT', 'Используйте скобки "(" и ")" для группировки условий, символы "|" и "&" обозначают логические операторы "ИЛИ" и "И" соответственно.<br>Пример: Формула "(1 <strong>|</strong> 2) <strong>&</strong> 3" будет означать, что для того, чтобы баннер был показан, должно быть выполнено 1-е <strong>или</strong> 2-е условие <strong>и</strong> выполнено 3-е условие');

define('TEXT_BANNERS_TITLE', 'Название баннера:');
define('TEXT_BANNERS_URL', 'URL баннера:');
define('TEXT_BANNERS_GROUP', 'Группа баннера:');
define('TEXT_BANNERS_NEW_GROUP', ', выберите группу или создайте новую ниже');
define('TEXT_BANNERS_IMAGE', 'Баннер:');
define('TEXT_BANNERS_IMAGE_LOCAL', ', или введите ссылку на файл ниже');
define('TEXT_BANNERS_IMAGE_TARGET', 'Баннер (сохранить как):');
define('TEXT_BANNERS_HTML_TEXT', 'HTML Код:');
define('TEXT_BANNERS_EXPIRES_ON', 'Должен показываться до:');
define('TEXT_BANNERS_OR_AT', ', или лимит');
define('TEXT_BANNERS_IMPRESSIONS', 'показов/кликов.');
define('TEXT_BANNERS_SCHEDULED_AT', 'Должен показываться с:');
define('TEXT_BANNERS_WEIGHT', '&laquo;Вес&raquo; баннера:');
define('TEXT_BANNERS_WEIGHT_TEXT', '<small>Кол-во показов баннера из 10</small>');
define('TEXT_BANNERS_CONDITIONS', 'Условия показа баннера:');
define('TEXT_BANNERS_GROUP_CONDITIONS', 'Такие же, как в настройках группы');
define('TEXT_BANNERS_OTHER_CONDITIONS', 'Другие условия');
define('TEXT_BANNERS_BANNER_NOTE', '<strong>Примечание:</strong><ul><li>Используйте для баннера только изображение или HTML Код, но не одновременно оба способа.</li><li>HTML Код имеет приоритет над изображением</li></ul>');
define('TEXT_BANNERS_INSERT_NOTE', '<strong>Информация о загрузке баннера:</strong><ul><li>Директория, в которую загружаются баннеры должна иметь соответствующие права доступа!</li><li>Не заполняйте область \'Сохранить Как\' если Вы не загружаете изображение на сервер (т.е., Вы используете баннер с локального диска).</li><li>Директория, указанная в поле \'Сохранить Как\' должна быть создана на сервере и должна заканчиваться косой чертой (например, banners/).</li></ul>');
define('TEXT_BANNERS_EXPIRCY_NOTE', '<strong>Информация о показе баннера:</strong><ul><li>Только одно из полей "Должен показываться до" или "Должен показываться с" должно быть заполнено, т.е. 2 поля одновременно заполнены быть не могут</li><li>Если баннер должен показываться постоянно, просто оставьте эти поля пустыми</li></ul>');
define('TEXT_BANNERS_SCHEDULE_NOTE', '<strong>Информация о поле "Должен показываться с":</strong><ul><li>Если Вы установили дату в этом поле, то баннер будет показываться с той даты, которую Вы указали.</li><li>Все баннеры, у которых заполнено поле "Должен показываться с" по умолчанию выключены, после того как наступит указанная дата, баннер будет активен.</li></ul>');
define('TEXT_BANNERS_CONDITIONS_NOTE', '<strong>Информация о поле "Формула применения условий":</strong><ul><li>Используйте скобки "(" и ")" для группировки условий, символы "|" и "&" обозначают логические операторы "ИЛИ" и "И" соответственно.</li><li>Пример: Формула "(1 <strong>|</strong> 2) <strong>&</strong> 3" будет означать, что для того, чтобы баннер был показан, должно быть выполнено 1-е <strong>или</strong> 2-е условие <strong>и</strong> выполнено 3-е условие</li></ul>');

define('TEXT_CONDITION_OR', '<strong>ИЛИ</strong>');
define('TEXT_CONDITION_AND', '<strong>И</strong>');

define('TEXT_DATE_ADDED', 'Дата добавления:');
define('TEXT_LAST_MODIFIED', 'Последнее изменение:');
define('TEXT_BANNERS_SCHEDULED_AT_DATE', 'Будет показан с: <strong>%s</strong>');
define('TEXT_BANNERS_EXPIRES_AT_DATE', 'Показывается до: <strong>%s</strong>');
define('TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS', 'Осталось: <strong>%s</strong> показов');
define('TEXT_BANNERS_STATUS_CHANGE', 'Изменение статуса: %s');

define('TEXT_BANNERS_DATA', 'Д<br>А<br>Т<br>А');
define('TEXT_BANNERS_LAST_3_DAYS', 'Последние 3 дня');
define('TEXT_BANNERS_BANNER_VIEWS', 'Показы');
define('TEXT_BANNERS_BANNER_CLICKS', 'Клики');

define('TEXT_DELETE_WARNING_BANNERS', '<strong>ВНИМАНИЕ:</strong> Есть еще %s баннеров, связанных с этой группой!');

define('SUCCESS_BANNER_INSERTED', 'Выполнено: Баннер добавлен.');
define('SUCCESS_BANNER_UPDATED', 'Выполнено: Баннер изменён.');
define('SUCCESS_BANNER_REMOVED', 'Выполнено: Баннер удалён.');
define('SUCCESS_BANNER_STATUS_UPDATED', 'Выполнено: Статус баннера изменён.');

define('ERROR_BANNER_TITLE_REQUIRED', 'Ошибка: Введите название баннера.');
define('ERROR_BANNER_GROUP_REQUIRED', 'Ошибка: Введите группу баннера.');
define('ERROR_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'Ошибка: Указанная директория отсутствует: %s');
define('ERROR_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Ошибка: Директория имеет неверные права доступа: %s');
define('ERROR_IMAGE_DOES_NOT_EXIST', 'Ошибка: Баннер отсутствует.');
define('ERROR_IMAGE_IS_NOT_WRITEABLE', 'Ошибка: Баннер не может быть удалён.');
define('ERROR_UNKNOWN_STATUS_FLAG', 'Ошибка: Неизвестный статус.');

define('ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST', 'Ошибка: Директория для баннеров отсутствует. Создайте поддиректорию \'graphs\' в директории \'images\'.');
define('ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE', 'Ошибка: Директория имеет неверные права доступа.');
?>