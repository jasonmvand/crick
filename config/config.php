<?php

namespace Config;

define(__NAMESPACE__ . '\\STORE_ID', 0);

define(__NAMESPACE__ . '\\HOST_NAME', '');
define(__NAMESPACE__ . '\\HOST_SUBDIRECTORY', '');
define(__NAMESPACE__ . '\\HTTP_HOST', 'http://' . \Config\HOST_NAME . \Config\HOST_SUBDIRECTORY);
define(__NAMESPACE__ . '\\HTTPS_HOST', 'https://' . \Config\HOST_NAME . \Config\HOST_SUBDIRECTORY);

define(__NAMESPACE__ . '\\DIR_CACHE', '');
define(__NAMESPACE__ . '\\DIR_CONFIG', \Config\DIR_APPLICATION . 'config/');
define(__NAMESPACE__ . '\\DIR_CONTROLLER', \Config\DIR_APPLICATION . 'controller/');
define(__NAMESPACE__ . '\\DIR_LANGUAGE', \Config\DIR_APPLICATION . 'language/');
define(__NAMESPACE__ . '\\DIR_LIBRARY', '');
define(__NAMESPACE__ . '\\DIR_LIBRARY_LOCAL', \Config\DIR_APPLICATION . 'library/');
define(__NAMESPACE__ . '\\DIR_MODEL', \Config\DIR_APPLICATION . 'model/');
define(__NAMESPACE__ . '\\DIR_VIEW', \Config\DIR_APPLICATION . 'view/');
define(__NAMESPACE__ . '\\DIR_TEMPLATE', \Config\DIR_APPLICATION . 'template/');

define(__NAMESPACE__ . '\\TIMESTAMP', time());
define(__NAMESPACE__ . '\\TIMEZONE', 'America/Indiana/Indianapolis');
define(__NAMESPACE__ . '\\DEFAULT_CONTROLLER', 'home');
