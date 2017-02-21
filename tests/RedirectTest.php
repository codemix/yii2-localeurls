<?php

use yii\helpers\Url;

class RedirectTest extends TestCase
{
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
                    '/de/site/page' => ['request' => ['acceptableLanguages' => ['de']]],
                    '/at/site/page' => ['request' => ['acceptableLanguages' => ['de-at', 'de']]],
                    '/wc/site/page' => ['request' => ['acceptableLanguages' => ['wc']]],
                    '/es-bo/site/page' => ['request' => ['acceptableLanguages' => ['es-BO', 'es', 'en']]],
                    '/es-bo/site/page' => ['request' => ['acceptableLanguages' => ['es-bo', 'es', 'en']]],
                    '/wc-at/site/page' => ['request' => ['acceptableLanguages' => ['wc-AT', 'de', 'en']]],
                    '/pt/site/page' => ['request' => ['acceptableLanguages' => ['pt-br']]],
                    '/alias/site/page' => ['request' => ['acceptableLanguages' => ['fr']]],
                    // no redirect
                    false => ['request' => ['acceptableLanguages' => ['en']]], // default language

                    // Language in session
                    '/de/site/page' => ['session' => ['_language' => 'de']],
                    '/at/site/page' => ['session' => ['_language' => 'de-AT']],
                    '/wc/site/page' => ['session' => ['_language' => 'wc']],
                    '/es-bo/site/page' => ['session' => ['_language' => 'es-BO']],
                    '/wc-at/site/page' => ['session' => ['_language' => 'wc-AT']],
                    '/pt/site/page' => ['session' => ['_language' => 'pt']],
                    '/alias/site/page' => ['session' => ['_language' => 'fr']],

                    // Language in cookie
                    '/de/site/page' => ['cookie' => ['_language' => 'de']],
                    '/at/site/page' => ['cookie' => ['_language' => 'de-AT']],
                    '/wc/site/page' => ['cookie' => ['_language' => 'wc']],
                    '/es-bo/site/page' => ['cookie' => ['_language' => 'es-BO']],
                    '/wc-at/site/page' => ['cookie' => ['_language' => 'wc-AT']],
                    '/pt/site/page' => ['cookie' => ['_language' => 'pt']],
                    '/alias/site/page' => ['cookie' => ['_language' => 'fr']],
                ],
            ],
        ],

        // Default language uses language code
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
            ],
            'redirects' => [
                '/' => '/en',
                '/site/page' => '/en/site/page',
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
                    '/en-US/site/page' => ['session' => ['_language' => 'en-US']],
                    '/en-US/site/page' => ['cookie' => ['_language' => 'en-US']],
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
                'languages' => ['en-US', 'en', 'de'],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/'
            ],
            'redirects' => [
                '/' => '/en/',
                '/site/page/' => '/en/site/page/',
            ],
        ],
    ];

    public function testRedirects()
    {
        foreach ($this->testConfigs as $config) {
            $urlManager = isset($config['urlManager']) ? $config['urlManager'] : [];
            foreach ($config['redirects'] as $from => $to) {
                if (is_array($to)) {
                    foreach ($to as $url => $params) {
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
        try {
            $this->mockRequest($from, $request);
            if ($to) {
                $this->fail("No redirect for $from");
            }
        } catch (\yii\base\Exception $e) {
            $this->assertEquals($this->prepareUrl($to), $e->getMessage());
        }
    }

}
