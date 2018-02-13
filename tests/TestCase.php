<?php
use yii\helpers\ArrayHelper;
use yii\di\Container;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array UrlManager component configuration for each test method
     */
    protected $urlManager = [];

    /**
     * @var bool show script name configuration for each test method
     */
    protected $showScriptName = false;

    /**
     * @var string the base URL to use
     */
    protected $baseUrl = '';

    protected $_server;

    protected function setUp()
    {
        if ($this->_server === null) {
            // Keep initial $_SERVER content to restore after each
            // test in tearDown()
            $this->_server = $_SERVER;
        }
    }

    /**
     * Destroy Yii app singleton, DI container, session and cookies
     */
    protected function tearDown()
    {
        $_COOKIE = [];
        $_SERVER = $this->_server;
        if (isset(\Yii::$app)) {
            \Yii::$app->session->destroy();
            \Yii::$app = null;
            \Yii::$container = new Container();
        }
        $this->urlManager = [];
        parent::tearDown();
    }

    /**
     * Mock a HTTP request
     *
     * This will set all required variables in the PHP environment to mock a HTTP
     * request with the given URL. It will then initialize a Yii web app and let
     * it resolve the request.
     *
     * @param string $url the relative request URL
     * @param array $config optional configuration for the `request` application component
     */
    protected function mockRequest($url, $config = [])
    {
        $url = $this->prepareUrl($url);
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = $this->baseUrl.'/index.php';
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $this->baseUrl.'/index.php';
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $parts = explode('?', $url);
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($parts[1], $_GET);
        } else {
            $_GET = [];
        }
        if ($config!==[]) {
            $config = [
                'components' => [
                    'request' => $config,
                ],
            ];
        }
        $this->mockWebApplication($config);
        Yii::$app->request->resolve();
    }

    /**
     * Mock a web application with given config for urlManager component and let it resolve the request
     *
     * @param array $config for urlManager component
     */
    public function mockUrlManager($config = []) {
        $this->urlManager = $config;
    }

    /**
     * Expect a redirect exception
     *
     * @param string $url the redirect URL
     */
    protected function expectRedirect($url)
    {
        $url = $this->prepareUrl($url);
        $this->expectException('\yii\base\Exception');
        $this->expectExceptionMessageRegExp('#^' . $url . '$#');
    }

    /**
     * @param string $url
     * @return string the URL with scriptName and baseUrl applied if enabled
     */
    protected function prepareUrl($url)
    {
        if ($this->showScriptName) {
            $url = '/index.php' . $url;
        }
        return $this->baseUrl . $url;
    }

    /**
     * Mock a Yii web application
     *
     * This will create a new Yii application object and configure it with some default options.
     * For the `urlManager` component it will use the options that where set with `mockUrlManager()`.
     * Extra configuration passed via `$config` will override any of the above options.
     *
     * @param array $config application configuration
     * @param string $appClass default is `\yii\web\Application`
     */
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'language' => 'en',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__.'/../vendor/',
            'components' => [
                'request' => [
                    'enableCookieValidation' => false,
                    'isConsoleRequest' => false,
                    'hostInfo' => 'http://localhost',
                ],
                'urlManager' => ArrayHelper::merge([
                    'class' => 'codemix\localeurls\UrlManager',
                    'showScriptName' => $this->showScriptName,
                ], $this->urlManager),
            ],
        ], $config));
    }
}
