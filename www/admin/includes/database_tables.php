<?php
// define the database table names used in the project
  define('TABLE_ADDRESS_BOOK', 'address_book');
  define('TABLE_ADDRESS_FORMAT', 'address_format');
  define('TABLE_ADVANCE_ORDERS', 'advance_orders');
  define('TABLE_ADVANCE_ORDERS_PRODUCTS', 'advance_orders_products');
  define('TABLE_ADVANCE_ORDERS_STATUS_HISTORY', 'advance_orders_status_history');
  define('TABLE_ADVERTISEMENTS', 'advertisements');
  define('TABLE_APPLICATIONS', 'applications');
  define('TABLE_ARCHIVE_ORDERS', 'archive_orders');
  define('TABLE_ARCHIVE_ORDERS_PRODUCTS', 'archive_orders_products');
  define('TABLE_ARCHIVE_ORDERS_PRODUCTS_DOWNLOAD', 'archive_orders_products_download');
  define('TABLE_ARCHIVE_ORDERS_STATUS_HISTORY', 'archive_orders_status_history');
  define('TABLE_ARCHIVE_ORDERS_TOTAL', 'archive_orders_total');
  define('TABLE_AUTHORS', 'authors');
  define('TABLE_BANNERS', 'banners');
  define('TABLE_BANNERS_CONDITIONS', 'banners_conditions');
  define('TABLE_BANNERS_GROUPS', 'banners_groups');
  define('TABLE_BANNERS_HISTORY', 'banners_history');
  define('TABLE_BLACKLIST', 'blacklist');
  define('TABLE_BLOCKS', 'blocks');
  define('TABLE_BLOCKS_TYPES', 'blocks_types');
  define('TABLE_BOARDS', 'boards');
  define('TABLE_BOARDS_CATEGORIES', 'boards_categories');
  define('TABLE_BOARDS_TYPES', 'boards_types');
  define('TABLE_CATEGORIES', 'categories');
  define('TABLE_CATEGORIES_DESCRIPTION', 'categories_description');
  define('TABLE_CATEGORIES_LINKED', 'categories_linked');
  define('TABLE_CITIES','cities');
  define('TABLE_CITIES_TO_GEO_ZONES','cities_to_geo_zones');
  define('TABLE_COMPANIES', 'companies');
  define('TABLE_CONFIGURATION', 'configuration');
  define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
  define('TABLE_COUNTRIES', 'countries');
  define('TABLE_CURRENCIES', 'currencies');
  define('TABLE_CUSTOMERS', 'customers');
  define('TABLE_CUSTOMERS_BASKET', 'customers_basket');
  define('TABLE_CUSTOMERS_POSTPONE_BASKET', 'customers_postpone_basket');
  define('TABLE_CUSTOMERS_INFO', 'customers_info');
  define('TABLE_CUSTOMERS_NOTIFICATIONS', 'customers_notifications');
  define('TABLE_DISCOUNTS', 'discounts');
  define('TABLE_DISCOUNTS_TO_CUSTOMERS', 'discounts_to_customers');
  define('TABLE_FOREIGN_PRODUCTS', 'foreign_products');
  define('TABLE_GEO_ZONES', 'geo_zones');
  define('TABLE_INFORMATION', 'information');
  define('TABLE_INFORMATION_TO_BLOCKS', 'information_to_blocks');
  define('TABLE_INFORMATION_TO_SECTIONS', 'information_to_sections');
  define('TABLE_LANGUAGES', 'languages');
  define('TABLE_MANUFACTURERS', 'manufacturers');
  define('TABLE_MANUFACTURERS_INFO', 'manufacturers_info');
  define('TABLE_MESSAGES', 'messages');
  define('TABLE_METATAGS', 'metatags');
  define('TABLE_NEWS', 'news');
  define('TABLE_NEWS_TYPES', 'news_types');
  define('TABLE_NEWSLETTERS', 'newsletters');
  define('TABLE_ORDERS', 'orders');
  define('TABLE_ORDERS_PRODUCTS', 'orders_products');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
  define('TABLE_ORDERS_PRODUCTS_VIEWED', 'orders_products_viewed');
  define('TABLE_ORDERS_STATUS', 'orders_status');
  define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
  define('TABLE_ORDERS_TOTAL', 'orders_total');
  define('TABLE_PAGES', 'pages');
  define('TABLE_PAGES_TRANSLATION', 'pages_translation');
  define('TABLE_PARTNERS', 'partners');
  define('TABLE_PARTNERS_STATISTICS', 'partners_statistics');
  define('TABLE_PARTNERS_BALANCE', 'partners_balance');
  define('TABLE_PAYMENT_TO_GEO_ZONES','payment_to_geo_zones');
  define('TABLE_PRODUCTS', 'products');
  define('TABLE_PRODUCTS_COVERS', 'products_covers');
  define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
  define('TABLE_PRODUCTS_FORMATS', 'products_formats');
  define('TABLE_PRODUCTS_IMAGES', 'products_images');
  define('TABLE_PRODUCTS_INFO', 'products_info');
  define('TABLE_PRODUCTS_LINKED', 'products_linked');
  define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
  define('TABLE_PRODUCTS_TO_INFORMATION', 'products_to_information');
  define('TABLE_PRODUCTS_TO_MODELS', 'products_to_models');
  define('TABLE_PRODUCTS_TO_SHOPS', 'products_to_shops');
  define('TABLE_PRODUCTS_TYPES', 'products_types');
  define('TABLE_PRODUCTS_TYPES_TO_PARAMETERS', 'products_types_to_parameters');
  define('TABLE_PRODUCTS_VIEWED', 'products_viewed');
  define('TABLE_REVIEWS', 'reviews');
  define('TABLE_REVIEWS_TYPES', 'reviews_types');
  define('TABLE_REVIEWS_TYPES', 'reviews_types');
  define('TABLE_SEARCH_KEYWORDS', 'search_keywords');
  define('TABLE_SEARCH_KEYWORDS_TO_PRODUCTS', 'search_keywords_to_products');
  define('TABLE_SECTIONS', 'sections');
  define('TABLE_SELF_DELIVERY', 'self_delivery');
  define('TABLE_SERIES', 'series');
  define('TABLE_SESSIONS', 'sessions');
  define('TABLE_SHIPPING_TO_CITIES','shipping_to_cities');
  define('TABLE_SHIPPING_TO_GEO_ZONES','shipping_to_geo_zones');
  define('TABLE_SHIPPING_TO_PAYMENT','shipping_to_payment');
  define('TABLE_SHOPS', 'shops');
  define('TABLE_SPECIALS', 'specials');
  define('TABLE_SPECIALS_CATEGORIES', 'specials_categories');
  define('TABLE_SPECIALS_TYPES', 'specials_types');
  define('TABLE_SUBJECTS', 'subjects');
  define('TABLE_SUPPLIERS', 'suppliers');
  define('TABLE_TAX_CLASS', 'tax_class');
  define('TABLE_TAX_RATES', 'tax_rates');
  define('TABLE_TEMPLATES', 'templates');
  define('TABLE_TEMPLATES_TO_BLOCKS', 'templates_to_blocks');
  define('TABLE_TEMPLATES_TO_BLOCKS_TYPES', 'templates_to_blocks_types');
  define('TABLE_TEMPLATES_TO_CONTENT', 'templates_to_content');
  define('TABLE_TEMP_PRODUCTS', 'temp_products');
  define('TABLE_TEMP_PRODUCTS_INFO', 'temp_products_info');
  define('TABLE_TEMP_SPECIALS', 'temp_specials');
  define('TABLE_USERS', 'users');
  define('TABLE_USERS_GROUPS', 'users_groups');
  define('TABLE_USERS_GROUPS_TO_CONTENT', 'users_groups_to_content');
  define('TABLE_WHOS_ONLINE', 'whos_online');
  define('TABLE_ZONES', 'zones');
  define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
?>