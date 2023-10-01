<?php
/* * * * * * * * * * * * * * * * * * * * * * * *
 * Подключение и Запуск основного функционала  *
 * * * * * * * * * * * * * * * * * * * * * * * */

namespace AxelotTP;


require __DIR__ . '/functions/Site.php';
require __DIR__ . '/functions/Helper.php';
require __DIR__ . '/functions/functions-main.php';
require __DIR__ . '/functions/functions-other.php';
require __DIR__ . '/functions/dev/module.php';

ct()->hooks();
cth()->hooks();

/* * * * * * * * * * * * * * * *
 * Подключение Запуск модулей  *
 * * * * * * * * * * * * * * * */
require __DIR__ . '/functions/Routes.php';
new Routes();

require __DIR__ . '/functions/Assets.php';
new Assets();

require __DIR__ . '/functions/acf/ACF.php';
new ACF();

require __DIR__ . '/functions/other/module.php';
require __DIR__ . '/functions/front-page/module.php';
