<?php
if (tep_not_null($HTTP_GET_VARS['pPath'])) {
  define('HEADING_TITLE', 'Баланс партнера "%s"');
} else {
  define('HEADING_TITLE', 'Партнеры');
}

define('TABLE_HEADING_PARTNERS', 'Партнеры');
define('TABLE_HEADING_STATUS', 'Статус');
define('TABLE_HEADING_BALANCE', 'Баланс');
define('TABLE_HEADING_COMISSION', 'Комиссия');
define('TABLE_HEADING_ACTION', 'Действие');
define('TABLE_HEADING_INFO', 'Инфо');
define('TABLE_HEADING_BALANCE_DATE', 'Дата');
define('TABLE_HEADING_BALANCE_SUM', 'Сумма');
define('TABLE_HEADING_BALANCE_COMMENTS', 'Комментарий');

define('TEXT_PARTNER_NAME', 'Имя партнера:');
define('TEXT_PARTNER_LOGIN', 'Логин:');
define('TEXT_PARTNER_PASSWORD', 'Пароль:');
define('TEXT_PARTNER_PASSWORD_CONFIRMATION', 'Павторите пароль:');
define('TEXT_PARTNER_COMISSION', 'Размер текущей комиссии:');
define('TEXT_PARTNER_EMAIL_ADDRESS', 'Контактный e-mail:');
define('TEXT_PARTNER_URL', 'Адрес сайта:');
define('TEXT_PARTNER_BANK', 'Банковские реквизиты:');
define('TEXT_PARTNER_TELEPHONE', 'Контактный телефон:');
define('TEXT_DATE_OF_LAST_LOGON', 'Дата последнего входа:');
define('TEXT_PARTNER_NUMBER_OF_LOGONS', 'Всего входов:');

define('TEXT_HEADING_EDIT_PARTNER', 'Редактировать данных партнера');
define('TEXT_EDIT_PARTNER_INTRO', 'Измените необходимые данные');
define('TEXT_INFO_HEADING_BALANCE', 'Информация о поступлении');
define('TEXT_HEADING_NEW_BALANCE', 'Добавление нового поступления');
define('TEXT_HEADING_EDIT_BALANCE', 'Изменение суммы');
define('TEXT_HEADING_DELETE_BALANCE', 'Удаление поступления');
define('TEXT_HEADING_DELETE_PARTNER', 'Удалить партнера');

define('TEXT_DATE_ADDED', 'Дата добавления:');
define('TEXT_LAST_MODIFIED', 'Последнее изменение:');

define('TEXT_NEW_BALANCE_INTRO', 'Заполните все поля для добавления нового поступления');
define('TEXT_EDIT_BALANCE_INTRO', 'Измените данные поступления');
define('TEXT_DELETE_BALANCE_INTRO', 'Вы действительно хотите удалить поступления?');
define('TEXT_DELETE_PARTNER_INTRO', 'Вы действительно хотите удалить этого партнера?');
define('TEXT_BALANCE_SUM', 'Сумма:');
define('TEXT_BALANCE_COMMENTS', 'Комментарий:');
?>