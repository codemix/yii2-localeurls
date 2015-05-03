<?php
use yii\helpers\ArrayHelper;
use yii\di\Container;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array Request component configuration for each test method
     */
    protected $request = [];

    /**
     * @var bool show script name configuration for each test method
     */
    protected $showScriptName = false;

    /**
     * @var string the base URL to use
     */
    protected $baseUrl = '';

    /**
     * Destroy Yii app singleton, DI container, session and cookies
     */
    protected function tearDown()
    {
        $_COOKIE = [];
        \Yii::$app->session->destroy();
        \Yii::$app = null;
        \Yii::$container = new Container();
        $this->request = [];
        $this->localeUrls = [];
        parent::tearDown();
    }

    /**
     * Mock a HTTP request
     *
     * @param string $url the relative request URL
     * @param array $config configuration for the Request component
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
        }
        $this->request = $config;
    }

    /**
     * Mock a web application with given config for localeUrls component
     *
     * @param array $config for localeUrl component
     */
    public function mockLocaleUrl($config = []) {
        $this->mockWebApplication([
            'components' => [
                'localeUrls' => $config,
            ]
        ]);
    }

    /**
     * Expect a redirect exception
     *
     * @param string $url the redirect URL
     */
    protected function expectRedirect($url)
    {
        $url = $this->prepareUrl($url) ?: '/';
        $this->setExpectedExceptionRegExp('\yii\base\Exception', '#^' . $url . '$#');
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
                'request' => ArrayHelper::merge([
                    'enableCookieValidation' => false,
                    'isConsoleRequest' => false,
                    'hostInfo' => 'http://localhost',
                ], $this->request),
                'urlManager' => [
                    'class' => 'codemix\localeurls\UrlManager',
                    'showScriptName' => $this->showScriptName,
                ],
            ],
        ], $config));
    }
}
