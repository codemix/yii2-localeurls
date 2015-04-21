<?php

error_reporting(-1);
define('YII_ENABLE_ERROR_HANDLER', false);

define('YII_DEBUG', true);
define('YII_ENV_TEST', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
// require composer autoloader if available
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/TestCase.php');
