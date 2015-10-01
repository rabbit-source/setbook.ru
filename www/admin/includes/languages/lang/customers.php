<?php
define('HEADING_TITLE', 'Клиенты');
define('HEADING_TITLE_SEARCH', 'Поиск:');
define('HEADING_TITLE_SHOP', 'Магазин:');

define('TEXT_ALL_SHOPS', 'Все магазины');

define('TABLE_HEADING_FIRSTNAME', 'Имя');
define('TABLE_HEADING_LASTNAME', 'Фамилия');
define('TABLE_HEADING_ACCOUNT_CREATED', 'Зарегистрировался');
define('TABLE_HEADING_STATUS', 'Статус');
define('TABLE_HEADING_INFO', 'Инфо');

define('TEXT_DATE_ACCOUNT_CREATED', 'Запись создана:');
define('TEXT_DATE_ACCOUNT_LAST_MODIFIED', 'Последнее изменение:');
define('TEXT_INFO_DATE_LAST_LOGON', 'Последний вход:');
define('TEXT_INFO_NUMBER_OF_LOGONS', 'Количество входов:');
define('TEXT_INFO_COUNTRY', 'Страна:');
define('TEXT_INFO_NUMBER_OF_REVIEWS', 'Количество отзывов:');
define('TEXT_DELETE_INTRO', 'Вы действительно хотите удалить клиента?');
define('TEXT_DELETE_REVIEWS', 'Удалить %s отзыв(ы)');
define('TEXT_INFO_HEADING_DELETE_CUSTOMER', 'Удалить клиента');
define('TYPE_BELOW', 'Введите ниже');
define('PLEASE_SELECT', 'Выберите что-то одно');

define('ENTRY_CUSTOMER_STATUS', 'Надежный клиент');

define('TEXT_DOWNLOAD_CUSTOMERS', 'Выгрузить данные о клиентах');

define('TEXT_YES', 'Да');
define('TEXT_NO', 'Нет');

define('ENTRY_COMPANY', 'Название компании:');
define('ENTRY_COMPANY_ERROR', 'Вы не указали название компании.');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_COMPANY_TYPE_NAME', 'Тип компании:');
define('ENTRY_COMPANY_TYPE_NAME_TEXT', '');
define('ENTRY_COMPANY_TAX_EXEMPT', 'Компания освобождена от уплаты налогов:');
define('ENTRY_COMPANY_TAX_EXEMPT_TEXT', '');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER', 'Если да, номер освобождения:');
define('ENTRY_COMPANY_TAX_EXEMPT_NUMBER_TEXT', '');
define('ENTRY_COMPANY_FULL', 'Полное наименование компании:');
define('ENTRY_COMPANY_FULL_ERROR', 'Вы не указали полное наименование компании.');
define('ENTRY_COMPANY_FULL_TEXT', '');
if (DOMAIN_ZONE=='ru') {
  define('ENTRY_COMPANY_INN', 'ИНН:');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП:');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='kz') {
  define('ENTRY_COMPANY_INN', 'РНН:');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали РНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП:');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='by') {
  define('ENTRY_COMPANY_INN', 'УНП:');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали УНП');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП:');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
} elseif (DOMAIN_ZONE=='ua') {
  define('ENTRY_COMPANY_INN', 'ИНН:');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'ОКПО/ЕГРПОУ:');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали код ОКПО/ЕГРПОУ');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'true');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'true');
} else {
  define('ENTRY_COMPANY_INN', 'ИНН:');
  define('ENTRY_COMPANY_INN_ERROR', 'Вы не указали ИНН');
  define('ENTRY_COMPANY_INN_TEXT', '');
  define('ENTRY_COMPANY_KPP', 'КПП:');
  define('ENTRY_COMPANY_KPP_ERROR', 'Вы не указали КПП');
  define('ENTRY_COMPANY_KPP_TEXT', '');
  define('ENTRY_COMPANY_INN_MIN_LENGTH', 'false');
  define('ENTRY_COMPANY_KPP_MIN_LENGTH', 'false');
}
define('ENTRY_COMPANY_ADDRESS_CORPORATE', 'Юридический адрес:');
define('ENTRY_COMPANY_ADDRESS_CORPORATE_TEXT', '');
define('ENTRY_COMPANY_ADDRESS_POST', 'Почтовый адрес:');
define('ENTRY_COMPANY_ADDRESS_POST_TEXT', '');
define('ENTRY_COMPANY_TELEPHONE', 'Телефон:');
define('ENTRY_COMPANY_TELEPHONE_TEXT', '');
define('ENTRY_COMPANY_CORPORATE', 'Корпоративный клиент');
define('ENTRY_COMPANY_FAX_TEXT', '');
?>