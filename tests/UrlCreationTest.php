<?php

use yii\helpers\Url;

class UrlCreationTest extends TestCase
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
     *     'urls' => [
     *         // Key is a request URL
     *         '/some/request/url' => [
     *             // The URLs to create during this request indexed by the
     *             // expected result. If expected URL starts with
     *             // 'http://localhost' the URL is created as absolute URL.
     *             '/expected/url' => ['some/route', 'param1'=> 'value1'],
     *             ...
     *         ],
     *     ],
     * ]
     * ```
     */
    public $testConfigs = [
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'french' => 'fr', 'de'],
                'rules' => [
                    '' => 'site/index',
                    '/foo/<term:.+>/bar' => 'slug/action',
                    '/important/<term:.+>/bar' => 'important/action',
                    'http://www.example.com/foo/<term:.+>/bar' => 'slug/other',
                ],
                'ignoreLanguageUrlPatterns' => [
                    '#^ignored/.*#' => '#not/used#',
                    '#^important/.*#' => '#not/used#',
                ],
            ],
            'urls' => [
                // No language code in request
                '/site/page' => [
                    '/demo/action' => ['/demo/action'],
                    '/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/foo/baz/bar' => ['/slug/action', 'term' => 'baz'],
                    '/foo/baz/bar?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                ],
                // Language code in request
                '/de/site/page' => [
                    // Request language
                    '/de' => ['/site/index'],
                    '/de?x=y' => ['/site/index', 'x' => 'y'],
                    '/de/demo/action' => ['/demo/action'],
                    '/de/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/de/foo/baz/bar' => ['/slug/action', 'term' => 'baz'],
                    '/de/foo/baz/bar?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/de' => ['/site/index'],
                    'http://localhost/de?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/de/demo/action' => ['/demo/action'],
                    'http://localhost/de/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/de/foo/baz/bar' => ['/slug/action', 'term' => 'baz'],
                    'http://localhost/de/foo/baz/bar?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                    'http://www.example.com/de/foo/baz/bar' => ['/slug/other', 'term' => 'baz'],
                    'http://www.example.com/de/foo/baz/bar?x=y' => ['/slug/other', 'term' => 'baz', 'x' => 'y'],

                    // Other language
                    '/en' => ['/', 'language' => 'en'],
                    '/en/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/en-us' => ['/', 'language' => 'en-US'],
                    '/en-us/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    '/en-us/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/foo/baz/bar' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/en-us' => ['/', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/foo/baz/bar' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://www.example.com/en-us/foo/baz/bar' => ['/slug/other', 'language' => 'en-US', 'term' => 'baz'],
                    'http://www.example.com/en-us/foo/baz/bar?x=y' => ['/slug/other', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    // Aliased language
                    '/french/demo/action' => ['/demo/action', 'language' => 'fr'],
                    '/french/demo/action?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    '/french/foo/baz/bar' => ['/slug/action', 'language' => 'fr', 'term' => 'baz'],
                    '/french/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    // No language code added for ignored patterns
                    '/ignored/action' => ['/ignored/action'],
                    '/ignored/action?x=y' => ['/ignored/action', 'x' => 'y'],
                    '/important/baz/bar' => ['/important/action', 'term' => 'baz'],
                    '/important/baz/bar?x=y' => ['/important/action', 'term' => 'baz', 'x' => 'y'],

                    // No language
                    '/' => ['/site/index', 'language' => ''],
                    '/demo/action' => ['/demo/action', 'language' => ''],
                    'http://localhost/' => ['/site/index', 'language' => ''],
                ],
            ]
        ],

        // Trailing slashes
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'french' => 'fr', 'de'],
                'suffix' => '/',
                'rules' => [
                    '' => 'site/index',
                    '/foo/<term:.+>/bar' => 'slug/action',
                    '/important/<term:.+>/bar' => 'important/action',
                    'http://www.example.com/foo/<term:.+>/bar' => 'slug/other',
                ],
                'ignoreLanguageUrlPatterns' => [
                    '#^ignored/.*#' => '#not/used#',
                    '#^important/.*#' => '#not/used#',
                ],
            ],
            'urls' => [
                // No language code in request
                '/site/page/' => [
                    '/demo/action/' => ['/demo/action'],
                    '/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    '/foo/baz/bar/' => ['/slug/action', 'term' => 'baz'],
                    '/foo/baz/bar/?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                ],
                // Language code in request
                '/de/site/page/' => [
                    // Request language
                    '/de/' => ['/site/index'],
                    '/de/?x=y' => ['/site/index', 'x' => 'y'],
                    '/de/demo/action/' => ['/demo/action'],
                    '/de/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    '/de/foo/baz/bar/' => ['/slug/action', 'term' => 'baz'],
                    '/de/foo/baz/bar/?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/de/' => ['/site/index'],
                    'http://localhost/de/?x=y' => ['/site/index', 'x' => 'y'],
                    'http://localhost/de/demo/action/' => ['/demo/action'],
                    'http://localhost/de/demo/action/?x=y' => ['/demo/action', 'x' => 'y'],
                    'http://localhost/de/foo/baz/bar/' => ['/slug/action', 'term' => 'baz'],
                    'http://localhost/de/foo/baz/bar/?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                    'http://www.example.com/de/foo/baz/bar/' => ['/slug/other', 'term' => 'baz'],
                    'http://www.example.com/de/foo/baz/bar/?x=y' => ['/slug/other', 'term' => 'baz', 'x' => 'y'],

                    // Other language
                    '/en/' => ['/', 'language' => 'en'],
                    '/en/demo/action/' => ['/demo/action', 'language' => 'en'],
                    '/en-us/' => ['/', 'language' => 'en-US'],
                    '/en-us/demo/action/' => ['/demo/action', 'language' => 'en-US'],
                    '/en-us/demo/action/?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-us/foo/baz/bar/' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-us/foo/baz/bar/?x=y' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://localhost/en-us/' => ['/', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action/' => ['/demo/action', 'language' => 'en-US'],
                    'http://localhost/en-us/demo/action/?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    'http://localhost/en-us/foo/baz/bar/' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz'],
                    'http://localhost/en-us/foo/baz/bar/?x=y' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                    'http://www.example.com/en-us/foo/baz/bar/' => ['/slug/other', 'language' => 'en-US', 'term' => 'baz'],
                    'http://www.example.com/en-us/foo/baz/bar/?x=y' => ['/slug/other', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],

                    // Aliased language
                    '/french/demo/action/' => ['/demo/action', 'language' => 'fr'],
                    '/french/demo/action/?x=y' => ['/demo/action', 'language' => 'fr', 'x' => 'y'],
                    '/french/foo/baz/bar/' => ['/slug/action', 'language' => 'fr', 'term' => 'baz'],
                    '/french/foo/baz/bar/?x=y' => ['/slug/action', 'language' => 'fr', 'term' => 'baz', 'x' => 'y'],

                    // No language code added for ignored patterns
                    '/ignored/action/' => ['/ignored/action'],
                    '/ignored/action/?x=y' => ['/ignored/action', 'x' => 'y'],
                    '/important/baz/bar/' => ['/important/action', 'term' => 'baz'],
                    '/important/baz/bar/?x=y' => ['/important/action', 'term' => 'baz', 'x' => 'y'],

                    // No language
                    '/' => ['/site/index', 'language' => ''],
                    '/demo/action/' => ['/demo/action', 'language' => ''],
                    'http://localhost/' => ['/site/index', 'language' => ''],
                ],
            ]
        ],

        // Keep Upper case
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'keepUppercaseLanguageCode' => true,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/en-US/site/page' => [
                    '/en-US/demo/action' => ['/demo/action'],
                    '/en-US/demo/action?x=y' => ['/demo/action', 'x' => 'y'],
                    '/en-US/foo/baz/bar' => ['/slug/action', 'term' => 'baz'],
                    '/en-US/foo/baz/bar?x=y' => ['/slug/action', 'term' => 'baz', 'x' => 'y'],
                ],
                '/de/site/page' => [
                    '/en-US/demo/action' => ['/demo/action', 'language' => 'en-US'],
                    '/en-US/demo/action?x=y' => ['/demo/action', 'language' => 'en-US', 'x' => 'y'],
                    '/en-US/foo/baz/bar' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz'],
                    '/en-US/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en-US', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],

        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'rules' => [
                    'http://www.example.com' => 'site/index',
                ],
            ],
            'urls' => [
                '/de/site/page' => [
                    // false forces creation as relative URL
                    'http://www.example.com/de' => [false, '/site/index'],
                ],
            ],
        ],

        // Persistence disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguagePersistence' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/en' => ['/', 'language' => 'en'],
                    '/en/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/en/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/en/foo/baz/bar' => ['/slug/action', 'language' => 'en', 'term' => 'baz'],
                    '/en/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],
        // Detection disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguageDetection' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/en' => ['/', 'language' => 'en'],
                    '/en/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/en/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/en/foo/baz/bar' => ['/slug/action', 'language' => 'en', 'term' => 'baz'],
                    '/en/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],
        // Persistence and detection disabled
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'enableLanguageDetection' => false,
                'enableLanguagePersistence' => false,
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/de/site/page/' => [
                    '/' => ['/', 'language' => 'en'],
                    '/demo/action' => ['/demo/action', 'language' => 'en'],
                    '/demo/action?x=y' => ['/demo/action', 'language' => 'en', 'x' => 'y'],
                    '/foo/baz/bar' => ['/slug/action', 'language' => 'en', 'term' => 'baz'],
                    '/foo/baz/bar?x=y' => ['/slug/action', 'language' => 'en', 'term' => 'baz', 'x' => 'y'],
                ],
            ],
        ],


        // Locale URLs disabled
        [
            'urlManager' => [
                'enableLocaleUrls' => false,
                'languages' => ['en-US', 'en', 'de'],
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/site/page' => [
                    '/demo/action?language=de' => ['/demo/action', 'language' => 'de'],
                    '/foo/baz/bar?language=de' => ['/slug/action', 'language' => 'de', 'term' => 'baz'],
                ],
            ]
        ],
        [
            'urlManager' => [
                'languages' => [],
                'rules' => [
                    '/foo/<term:.+>/bar' => 'slug/action',
                ],
            ],
            'urls' => [
                '/site/page' => [
                    '/demo/action?language=de' => ['/demo/action', 'language' => 'de'],
                    '/foo/baz/bar?language=de' => ['/slug/action', 'language' => 'de', 'term' => 'baz'],
                ],
            ]
        ],

    ];

    public function testUrlCreation()
    {
        foreach ($this->testConfigs as $config) {
            $urlManager = isset($config['urlManager']) ? $config['urlManager'] : [];
            foreach ($config['urls'] as $requestUrl => $routes) {
                $this->performUrlCreationTest($requestUrl, $urlManager, $routes);
            }
        }
    }

    /**
     * Tests URL creation during a specific request
     *
     * @param array $requestUrl the requested URL
     * @param array $urlManager the urlManager configuration
     * @param array $routes to create URL for indexed by the expected URL
     */
    public function performUrlCreationTest($requestUrl, $urlManager, $routes)
    {
        $this->tearDown();
        $this->mockUrlManager($urlManager);
        $this->mockRequest($requestUrl);
        foreach ($routes as $url => $route) {
            if (preg_match('#^(https?)://([^/]*)(.*)#', $url, $matches)) {
                $schema = $matches[1];
                $host = $matches[2];
                $relativeUrl = $matches[3];
                if ($route[0]===false) {
                    array_shift($route);
                    $this->assertEquals($schema . '://' . $host . $this->prepareUrl($relativeUrl), Url::to($route));
                } else {
                    $this->assertEquals($schema . '://' . $host . $this->prepareUrl($relativeUrl), Url::to($route, $schema));
                }
            } else {
                $this->assertEquals($this->prepareUrl($url), Url::to($route));
            }
        }
    }
}
