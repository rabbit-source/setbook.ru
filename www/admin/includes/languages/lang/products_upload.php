<?php
define('HEADING_TITLE_CATALOG', 'Загрузка каталога');
define('HEADING_TITLE_REPORT', 'Отчет о загрузке');

define('TEXT_SELECT_UPLOAD_TYPE', 'Выберите операцию:');
define('TEXT_UPLOAD_CATEGORIES', 'Обновление рубрикатора');
define('TEXT_UPLOAD_SERIES', 'Обновление серий');
define('TEXT_UPLOAD_AUTHORS', 'Обновление авторов');
define('TEXT_UPLOAD_MANUFACTURERS', 'Обновление издательств/производителей');
define('TEXT_UPLOAD_PRODUCTS', 'Обновление каталога книг');
define('TEXT_UPLOAD_OTHER_PRODUCTS', 'Обновление каталога некнижных товаров');
define('TEXT_UPDATE_IMAGES', 'Обновить рисунки');

define('TEXT_UPLOAD_LAST_MODIFIED', 'последнее обновление: %s');

define('TABLE_HEADING_MODEL', 'Код');
define('TABLE_HEADING_NAME', 'Наименование');
define('TABLE_HEADING_STATUS', 'Статус');

define('ERROR_UPLOAD_IN_PROCESS', 'В настоящий момент выполняется %s');
define('ERROR_NO_FILE_UPLOAD', "Ошибка! Файл не найден: %s!\n");
define('SUCCESS_RECORDS_UPDATED', "Загружалось записей: %s, обновлено: %s, добавлено новых: %s, не удалось добавить: %s!\n");
define('WARNING_UPDATE_IN_PROGRESS', 'Ошибка! Дождитесь окончания идущего в текущий момент обновления товарной базы.');

define('TEXT_PRODUCT_UPDATED', '<font color="#00C600">обновлен</font>');
define('TEXT_PRODUCT_ADDED', '<font color="#0000ff">добавлен</font>');
define('TEXT_PRODUCT_NOT_ADDED', '<font color="#C61300">не добавлен</font>');

define('EMAIL_NOTIFICATION_SEPARATOR', '<font color="#A7A7A7">––––––––––––––––––––––––––––––––––––––––––––––––––</font>');

###########################################################################################

define('EMAIL_NOTIFICATION_SUBJECT_1', '%s - уведомление о поступлении книги в продажу');
define('EMAIL_NOTIFICATION_SUBJECT_2', '%s - уведомление о снижении цены на книгу');
define('EMAIL_NOTIFICATION_BODY_1', 'Здравствуйте, %s!

Рады сообщить Вам, что в продажу поступила книга «<a href="{{product_link}}">%s</a>».

Чтобы перейти на страницу товара, воспользуйтесь приведённой ниже ссылкой:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_BODY_2', 'Здравствуйте, %s!

Рады сообщить Вам о снижении цены на книгу «<a href="{{product_link}}">%s</a>».

Чтобы перейти на страницу товара, воспользуйтесь приведённой ниже ссылкой:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_WARNING_1', '<small>Вы получили это письмо, так как подписаны на получение уведомления о поступлении данного товара в продажу в интернет-магазине %s. Данное оповещение носит разовый характер.</small>');
define('EMAIL_NOTIFICATION_WARNING_2', '<small>Вы получили это письмо, так как подписаны на получение уведомления о снижении цены на данный товар в интернет-магазине %s. Данное оповещение носит разовый характер.</small>');

###########################################################################################

define('EMAIL_NOTIFICATION_SUBJECT_EN_1', '%s - notification of reception of book on sale');
define('EMAIL_NOTIFICATION_SUBJECT_EN_2', '%s - notification about a decrease in the price of the book');
define('EMAIL_NOTIFICATION_BODY_EN_1', 'Dear %s!

We are pleased to inform you that the book &quot;<a href="{{product_link}}">%s</a>&quot; is on sale.

To go to the product page, please use the link below:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_BODY_EN_2', 'Dear %s!

We are pleased to inform you about a decrease in the price on the book &quot;<a href="{{product_link}}">%s</a>&quot;.

To go to the product page, please use the link below:
<a href="{{product_link}}">{{product_link}}</a>');
define('EMAIL_NOTIFICATION_WARNING_EN_1', '<small>You got this letter because you have subscribed on receiving notifications about the receiption of this product on sale in %s. This alert is a one-off nature.</small>');
define('EMAIL_NOTIFICATION_WARNING_EN_2', '<small>You got this letter because you have subscribed on receiving notifications about a decrease in the price of this product in %s. This alert is a one-off nature.</small>');
?>
