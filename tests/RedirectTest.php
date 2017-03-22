<?php

use yii\helpers\Url;

class RedirectTest extends TestCase
{
    /**
     * @var array the set of test configurations to test. Each entry is an
     * array with this structure:
     *
     * ```php
     * [
     *     'urlManager' => [
     *         // UrlManager settings for this test
     *     ],
     *     'redirects' => [
     *         // List of expected redirecs in the form `$fromUrl => $to`.
     *         // $to can be:
     *         //   - a string with a URL that should be redirected to,
     *         //   - `false` if there should be no redirect
     *         //   - an array of individual request/session/cookie configurations
     *         //     of this form:
     *         //     [$to, 'request' => .., 'session' => ..., 'cookie' => ...]
     *     ],
     * ]
     * ```
     */
    public $testConfigs = [
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
            ],
            'redirects' => [
                // from => to
                '/en/site/page' => '/site/page',
                '/en' => '/',
                '/es-BO/site/page' => '/es-bo/site/page',
                '/wc-BB/site/page' => '/wc-bb/site/page',

                // Requests with params in session, cookie or request headers
                '/site/page' => [

                    // Acceptable languages in request
                    ['/de/site/page', 'request' => ['acceptableLanguages' => ['de']]],
                    ['/at/site/page', 'request' => ['acceptableLanguages' => ['de-at', 'de']]],
                    ['/wc/site/page', 'request' => ['acceptableLanguages' => ['wc']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-BO', 'es', 'en']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-bo', 'es', 'en']]],
                    ['/wc-at/site/page', 'request' => ['acceptableLanguages' => ['wc-AT', 'de', 'en']]],
                    ['/pt/site/page', 'request' => ['acceptableLanguages' => ['pt-br']]],
                    ['/alias/site/page', 'request' => ['acceptableLanguages' => ['fr']]],
                    // no redirect
                    [false, 'request' => ['acceptableLanguages' => ['en']]], // default language

                    // Language in session
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/at/site/page', 'session' => ['_language' => 'de-AT']],
                    ['/wc/site/page', 'session' => ['_language' => 'wc']],
                    ['/es-bo/site/page', 'session' => ['_language' => 'es-BO']],
                    ['/wc-at/site/page', 'session' => ['_language' => 'wc-AT']],
                    ['/pt/site/page', 'session' => ['_language' => 'pt']],
                    ['/alias/site/page', 'session' => ['_language' => 'fr']],

                    // Language in cookie
                    ['/de/site/page', 'cookie' => ['_language' => 'de']],
                    ['/at/site/page', 'cookie' => ['_language' => 'de-AT']],
                    ['/wc/site/page', 'cookie' => ['_language' => 'wc']],
                    ['/es-bo/site/page', 'cookie' => ['_language' => 'es-BO']],
                    ['/wc-at/site/page', 'cookie' => ['_language' => 'wc-AT']],
                    ['/pt/site/page', 'cookie' => ['_language' => 'pt']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                ],

                // Requests with other language in session, cookie or request headers
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                ],
            ],
        ],

        // Default language uses language code
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
            ],
            'redirects' => [
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                ],
                '/site/page' => [
                    ['/en/site/page', ],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                ],
                // Requests with other language in session, cookie or request headers
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                ],
            ],
        ],

        // Upper case language codes allowed in URL
        [
            'urlManager' => [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
                'keepUppercaseLanguageCode' => true,
            ],
            'redirects' => [
                '/es-BO/site/page' => false,
                '/site/page' => [
                    ['/en-US/site/page', 'session' => ['_language' => 'en-US']],
                    ['/en-US/site/page', 'cookie' => ['_language' => 'en-US']],
                ]
            ],
        ],

        // Ignore patterns
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
                'ignoreLanguageUrlPatterns' => [
                    '#not/used#' => '#^site/other#'
                ],
            ],
            'redirects' => [
                '/site/page' => '/en/site/page',
                '/site/other' => false,
            ],
        ],

        // Suffix URLs
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/'
            ],
            'redirects' => [
                '/en' => '/',
                '/en/site/page/' => '/site/page/',
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/'
            ],
            'redirects' => [
                '/' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                ],
                '/site/page/' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                ],
            ],
        ],

        // Normalizer with + w/o suffix
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
            ],
            'redirects' => [
                '' => '',
                '/site/page' => '/site/page/',
                '/site/page/' => false,

                '/de' => '/de/',    // normalizer
                '/de/' => false,

                '/de/site/login' => '/de/site/login/',  // normalizer
                '/de/site/login/' => false,

                '/en/site/login' => '/site/login/',     // normalizer
                '/en/site/login/' => '/site/login/',    // localeurls
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
            ],
            'redirects' => [
                '' => '',
                '/site/page/' => '/site/page',
                '/site/page' => false,

                '/de/' => '/de',    // normalizer
                '/de' => false,

                '/de/site/login/' => '/de/site/login',  // normalizer
                '/de/site/login' => false,

                '/en/site/login/' => '/site/login',     // normalizer
                '/en/site/login' => '/site/login',      // localeurls
            ],
        ],
        // Normalizer with default language code
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
            ],
            'redirects' => [
                '' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                ],
                '/site/page' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                ],
                '/en/site/page' => '/en/site/page/',
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
            ],
            'redirects' => [
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                ],
                '/site/page/' => [
                    ['/en/site/page'],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                ],
                '/en/site/page/' => '/en/site/page',
            ],
        ],
    ];

    public function testRedirects()
    {
        foreach ($this->testConfigs as $config) {
            $urlManager = isset($config['urlManager']) ? $config['urlManager'] : [];
            foreach ($config['redirects'] as $from => $to) {
                if (is_array($to)) {
                    foreach ($to as $params) {
                        $url = $params[0];
                        $request = isset($params['request']) ? $params['request'] : [];
                        $session = isset($params['session']) ? $params['session'] : [];
                        $cookie = isset($params['cookie']) ? $params['cookie'] : [];
                        $this->performRedirectTest($from, $url, $urlManager, $request, $session, $cookie);
                    }
                } else {
                    $this->performRedirectTest($from, $to, $urlManager);
                }
            }
        }
    }

    /**
     * Tests for a redirect
     *
     * @param string $from the request URL
     * @param mixed $to the expected redirect URL or a falsey value for no redirect
     * @param array $urlManager the urlManager configuration
     * @param array $request the configuration for the request component
     * @param array $session the session variables
     * @param array $cookie the cookies
     */
    public function performRedirectTest($from, $to, $urlManager, $request = [], $session = [], $cookie = [])
    {
        $this->tearDown();
        $this->mockUrlManager($urlManager);
        if ($session!==null) {
            @session_start();
            $_SESSION = $session;
        }
        if ($cookie!==null) {
            $_COOKIE = $cookie;
        }
        $configMessage = print_r([
            'from' => $from,
            'to' => $to,
            'urlManager' => $urlManager,
            'request' => $request,
            'session' => $session,
            'cookie' => $cookie,
        ], true);
        try {
            $this->mockRequest($from, $request);
            if ($to) {
                $this->fail("No redirect:\n$configMessage");
            }
        } catch (\yii\web\UrlNormalizerRedirectException $e) {
            $url = $e->url;
            if (is_array($url)) {
                if (isset($url[0])) {
                    // ensure the route is absolute
                    $url[0] = '/' . ltrim($url[0], '/');
                }
                $url += Yii::$app->request->getQueryParams();
            }
            $message = "UrlNormalizerRedirectException:\n$configMessage";
            $this->assertEquals($this->prepareUrl($to), Url::to($url, $e->scheme), $message);
        } catch (\yii\base\Exception $e) {
            $message = "Redirection:\n$configMessage";
            $this->assertEquals($this->prepareUrl($to), $e->getMessage(), $message);
        }
    }

}
