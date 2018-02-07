<?php

error_reporting(-1);
define('YII_ENABLE_ERROR_HANDLER', false);

define('YII_DEBUG', true);
define('YII_ENV_TEST', true);
define('YII2_LOCALEURLS_TEST', true);

// Some travis environments use phpunit > 6
$newClass = '\PHPUnit\Framework\TestCase';
$oldClass = '\PHPUnit_Framework_TestCase';
if (!class_exists($newClass) && class_exists($oldClass)) {
    class_alias($oldClass, $newClass);
}

// require composer autoloader if available
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/TestCase.php');
require(__DIR__ . '/TestUrlRule.php');
