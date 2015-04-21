<?php
use yii\helpers\ArrayHelper;
use yii\di\Container;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $showScriptName = true;

    protected function tearDown()
    {

        \Yii::$app->session->destroy();
        \Yii::$app = null;
        \Yii::$container = new Container();
        parent::tearDown();
    }

    protected function mockComponents($config = [])
    {
        $this->mockWebApplication([
            'components' => $config,
        ]);
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'language' => 'en',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__.'/../vendor/',
            'bootstrap' => ['localeUrls'],
            'components' => [
                'localeUrls' => [
                    'class' => 'codemix\localeurls\LocaleUrls',
                ],
                'request' => [
                    'cookieValidationKey' => '123456789abcdefg',
                    'isConsoleRequest' => false,
                    'hostInfo' => 'http://localhost',
                    'scriptFile' => __DIR__.'/index.php',
                    'scriptUrl' => '/index.php',
                ],
                'urlManager' => [
                    'class' => 'codemix\localeurls\UrlManager',
                    'showScriptName' => $this->showScriptName,
                ],
            ],
        ], $config));
    }
}
