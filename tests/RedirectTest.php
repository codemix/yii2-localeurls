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
     *         //   - an array with the expected redirect URL as first element
     *         //     and further parameters to set:
     *         //     [
     *         //       $to,               // redirect URL as string
     *         //       'request' => ... , // request properties
     *         //       'session' => ... , // session data
     *         //       'cookie' => ... ,  // cookie data
     *         //       'server' => ... ,  // $_SERVER data
     *         //     ]
     *     ],
     * ]
     * ```
     */
    public $testConfigs = [

        // No URL code for default language
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'class' => 'TestUrlRule',
                    ],
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                // Default language in URL
                '/en/site/page' => '/site/page',
                '/en' => '/',

                // No code in URL but params in session, cookie or headers
                '/site/page' => [

                    // Country in GeoIp server var
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                    ['/at/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'AUT']],
                    ['/alias/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],

                    // Acceptable languages in request
                    ['/de/site/page', 'request' => ['acceptableLanguages' => ['de']]],
                    ['/at/site/page', 'request' => ['acceptableLanguages' => ['de-at', 'de']]],
                    ['/wc/site/page', 'request' => ['acceptableLanguages' => ['wc']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-BO', 'es', 'en']]],
                    ['/es-bo/site/page', 'request' => ['acceptableLanguages' => ['es-bo', 'es', 'en']]],
                    ['/wc-at/site/page', 'request' => ['acceptableLanguages' => ['wc-AT', 'de', 'en']]],
                    ['/pt/site/page', 'request' => ['acceptableLanguages' => ['pt-br']]],
                    ['/alias/site/page', 'request' => ['acceptableLanguages' => ['fr']]],

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

                    // Default language in GeoIp/cookie/session/header
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'session' => ['_language' => 'en']],
                    [false, 'cookie' => ['_language' => 'en']],

                    // Session precedes cookie precedes header precedes GeoIp
                    ['/de/site/page',
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                    ['/alias/site/page',
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                    ['/pt/site/page',
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'USA'],
                    ],
                ],

                // Code in URL different from language in session, cookie, headers or GeoIp
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],

                // Lowercase conversion
                '/es-BO/site/page' => [
                    ['/es-bo/site/page'],
                    ['/es-bo/site/page', 'session' => ['_language' => 'de']],
                    ['/es-bo/site/page', 'cookie' => ['_language' => 'de']],
                ],
                '/wc-BB/site/page' => [
                    ['/wc-bb/site/page'],
                    ['/wc-bb/site/page', 'session' => ['_language' => 'de']],
                    ['/wc-bb/site/page', 'cookie' => ['_language' => 'de']],
                ],

                // Custom URL rule
                '/custom' => false,
                '/en/custom' => '/custom',
                '/de/custom' => false,
                '/slash/' => false,
                '/en/slash/' => '/slash/',
                '/de/slash/' => false,

                // Params
                '/en?a=b' => '/?a=b',
                '/en/site/page?a=b' => '/site/page?a=b',
                '/en/custom?a=b' => '/custom?a=b',
                '/en/slash/?a=b' => '/slash/?a=b',
                '/site/page?a=b' => [
                    ['/de/site/page?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value' => false,
                '/en/slug/value' => '/slug/value',
                //'/ruleclass-test-url' => false,
                //'/en/ruleclass-test-url' => '/ruleclass-test-url',
            ],
        ],

        // URL code for default language
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableDefaultLanguageUrlCode' => true,
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'class' => 'TestUrlRule',
                    ],
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                // No code in URL
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                    ['/de', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page' => [
                    ['/en/site/page', ],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],

                // Lang requests with different language in session, cookie, headers or GeoIp
                '/en/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],
                '/de/site/page' => [
                    [false, 'session' => ['_language' => 'wc']],
                    [false, 'cookie' => ['_language' => 'wc']],
                    [false, 'request' => ['acceptableLanguages' => ['en']]],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'USA']],
                ],

                // Custom URL rule
                '/custom' => '/en/custom',
                '/en/custom' => false,
                '/de/custom' => false,
                '/slash/' => '/en/slash/',
                '/en/slash/' => false,
                '/de/slash/' => false,

                // Params
                '/?a=b' => '/en?a=b',
                '/site/page?a=b' => '/en/site/page?a=b',
                '/custom?a=b' => '/en/custom?a=b',
                '/slash/?a=b' => '/en/slash/?a=b',
                '/site/page?a=b' => [
                    ['/de/site/page?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value' => '/en/slug/value',
                '/en/slug/value' => false,
                '/ruleclass-english' => '/en/ruleclass-english',
                '/en/ruleclass-deutsch' => '/en/ruleclass-english',
                '/en/ruleclass-english' => false,
            ],
        ],

        // Upper case language codes allowed in URL
        [
            'urlManager' => [
                'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
                'keepUppercaseLanguageCode' => true,
            ],
            'redirects' => [
                // No code in URL
                '/site/page' => [
                    ['/en-US/site/page', 'session' => ['_language' => 'en-US']],
                    ['/en-US/site/page', 'cookie' => ['_language' => 'en-US']],
                ],
                // Upper case code in URL
                '/es-BO/site/page' => false,
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

        // No persistence / detection
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'enableLanguageDetection' => false,
                'enableLanguagePersistence' => false,
            ],
            'redirects' => [
                '/' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU'],
                    ],
                ],
                '/site/page' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'de'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU'],
                    ],
                ],
                '/de' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'en'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA'],
                    ],
                ],
                '/de/site/page' => [
                    [false],
                    [false,
                        'session' => ['_language' => 'en'],
                        'cookie' => ['_language' => 'fr'],
                        'request' => ['acceptableLanguages' => ['pt']],
                        'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA'],
                    ],
                ],
                '/en' => '/',
                '/en/site/page' => '/site/page',
            ],
        ],


        // Suffix in UrlManager, with + w/o URL code for default language
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '/en' => '/',
                '/en/site/page/' => '/site/page/',
                '/site/page/' => false,
                '/de/site/page/' => false,

                // Custom URL rule
                '/custom/' => false,
                '/en/custom/' => '/custom/',
                '/de/custom/' => false,
                '/noslash' => false,
                '/en/noslash' => '/noslash',
                '/de/noslash' => false,

                // Params
                '/en?a=b' => '/?a=b',
                '/en/site/page/?a=b' => '/site/page/?a=b',
                '/en/custom/?a=b' => '/custom/?a=b',
                '/en/noslash?a=b' => '/noslash?a=b',
                '/site/page/?a=b' => [
                    ['/de/site/page/?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value/' => false,
                '/en/slug/value/' => '/slug/value/',
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '/' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                    ['/de/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page/' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page/' => [
                    [false],
                    [false, 'session' => ['_language' => 'de']],
                    [false, 'cookie' => ['_language' => 'fr']],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],
                ],
                '/pt/site/page/' => [
                    [false],
                    [false, 'session' => ['_language' => 'de']],
                    [false, 'cookie' => ['_language' => 'fr']],
                    [false, 'server' => ['HTTP_X_GEO_COUNTRY' => 'FRA']],
                ],

                // Custom URL rule
                '/custom/' => '/en/custom/',
                '/en/custom/' => false,
                '/de/custom/' => false,
                '/noslash' => '/en/noslash',
                '/en/noslash' => false,
                '/de/noslash' => false,

                // Params
                '/?a=b' => '/en/?a=b',
                '/site/page/?a=b' => '/en/site/page/?a=b',
                '/custom/?a=b' => '/en/custom/?a=b',
                '/noslash?a=b' => '/en/noslash?a=b',
                '/site/page/?a=b' => [
                    ['/de/site/page/?a=b', 'request' => ['acceptableLanguages' => ['de']]],
                ],
                '/slug/value/' => '/en/slug/value/',
                '/en/slug/value/' => false,
            ],
        ],

        // Normalizer with + w/o suffix, no URL code for default language
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'suffix' => '/',
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
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

                // Custom URL rule
                '/custom' => '/custom/',
                '/custom/' => false,
                '/en/custom' => '/custom/',
                '/en/custom/' => '/custom/',
                '/de/custom' => '/de/custom/',
                '/de/custom/' => false,
                '/noslash' => false,
                '/noslash/' => '/noslash',
                '/en/noslash' => '/noslash',
                '/en/noslash/' => '/noslash',
                '/de/noslash' => false,
                '/de/noslash/' => '/de/noslash',

                // Params
                '/site/page?a=b' => '/site/page/?a=b',
                '/de?a=b' => '/de/?a=b',
                '/de/site/login?a=b' => '/de/site/login/?a=b',
                '/en/site/login?a=b' => '/site/login/?a=b',
                '/en/site/login/?a=b' => '/site/login/?a=b',
                '/custom?a=b' => '/custom/?a=b',
                '/en/custom?a=b' => '/custom/?a=b',
                '/en/custom/?a=b' => '/custom/?a=b',
                '/de/custom?a=b' => '/de/custom/?a=b',
                '/noslash/?a=b' => '/noslash?a=b',
                '/en/noslash?a=b' => '/noslash?a=b',
                '/en/noslash/?a=b' => '/noslash?a=b',
                '/de/noslash/?a=b' => '/de/noslash?a=b',
                '/slug/value' => '/slug/value/',
                '/en/slug/value' => '/slug/value/',
                '/de/slug/value' => '/de/slug/value/',
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de'],
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
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

                // Custom URL rule
                '/custom' => false,
                '/custom/' => '/custom',
                '/en/custom' => '/custom',
                '/en/custom/' => '/custom',
                '/de/custom' => false,
                '/de/custom/' => '/de/custom',
                '/slash' => '/slash/',
                '/slash/' => false,
                '/en/slash' => '/slash/',
                '/en/slash/' => '/slash/',
                '/de/slash' => '/de/slash/',
                '/de/slash/' => false,

                // Params
                '/site/page/?a=b' => '/site/page?a=b',
                '/de/?a=b' => '/de?a=b',    // normalizer
                '/de/site/login/?a=b' => '/de/site/login?a=b',  // normalizer
                '/en/site/login/?a=b' => '/site/login?a=b',     // normalizer
                '/en/site/login?a=b' => '/site/login?a=b',      // localeurls
                '/custom/?a=b' => '/custom?a=b',
                '/en/custom?a=b' => '/custom?a=b',
                '/en/custom/?a=b' => '/custom?a=b',
                '/de/custom/?a=b' => '/de/custom?a=b',
                '/slash?a=b' => '/slash/?a=b',
                '/en/slash?a=b' => '/slash/?a=b',
                '/en/slash/?a=b' => '/slash/?a=b',
                '/de/slash?a=b' => '/de/slash/?a=b',
                '/slug/value/' => '/slug/value',
                '/en/slug/value/' => '/slug/value',
                '/de/slug/value/' => '/de/slug/value',
            ],
        ],

        // Normalizer with + w/o suffix, with URL code for default language
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'suffix' => '/',
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/noslash',
                        'route' => 'test/slash',
                        'suffix' => '',
                    ],
                ],
            ],
            'redirects' => [
                '' => [
                    ['/en/'],    // default language
                    ['/de/', 'session' => ['_language' => 'de']],
                    ['/alias/', 'cookie' => ['_language' => 'fr']],
                    ['/de/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page' => [
                    ['/en/site/page/'],  // default language
                    ['/de/site/page/', 'session' => ['_language' => 'de']],
                    ['/alias/site/page/', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page/', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page' => '/en/site/page/',

                // Custom URL rule
                '/custom' => '/en/custom/',
                '/custom/' => '/en/custom/',
                '/en/custom' => '/en/custom/',
                '/en/custom/' => false,
                '/de/custom' => '/de/custom/',
                '/de/custom/' => false,
                '/noslash' => '/en/noslash',
                '/noslash/' => '/en/noslash',
                '/en/noslash' => false,
                '/en/noslash/' => '/en/noslash',
                '/de/noslash' => false,
                '/de/noslash/' => '/de/noslash',

                // Params
                '?a=b' => '/en/?a=b',
                '/site/page?a=b' => '/en/site/page/?a=b',
                '/custom?a=b' => '/en/custom/?a=b',
                '/custom/?a=b' => '/en/custom/?a=b',
                '/en/custom?a=b' => '/en/custom/?a=b',
                '/de/custom?a=b' => '/de/custom/?a=b',
                '/noslash?a=b' => '/en/noslash?a=b',
                '/noslash/?a=b' => '/en/noslash?a=b',
                '/en/noslash/?a=b' => '/en/noslash?a=b',
                '/de/noslash/?a=b' => '/de/noslash?a=b',
                '/slug/value' => '/en/slug/value/',
                '/en/slug/value' => '/en/slug/value/',
                '/de/slug/value' => '/de/slug/value/',
            ],
        ],
        [
            'urlManager' => [
                'languages' => ['en-US', 'en', 'de', 'pt', 'at' => 'de-AT', 'alias' => 'fr', 'es-BO', 'wc-*'],
                'geoIpLanguageCountries' => [
                    'de' => ['DEU'],
                    'de-AT' => ['AUT'],
                    'fr' => ['FRA'],
                    'en' => ['USA', 'GBR'],
                ],
                'enableDefaultLanguageUrlCode' => true,
                'normalizer' => [
                    'class' => '\yii\web\UrlNormalizer',
                ],
                'rules' => [
                    '/custom' => 'test/action',
                    '/slug/<name>' => 'test/slug',
                    [
                        'pattern' => '/slash',
                        'route' => 'test/slash',
                        'suffix' => '/',
                    ],
                ],
            ],
            'redirects' => [
                '/' => [
                    ['/en'],    // default language
                    ['/de', 'session' => ['_language' => 'de']],
                    ['/alias', 'cookie' => ['_language' => 'fr']],
                    ['/de', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/site/page/' => [
                    ['/en/site/page'],  // default language
                    ['/de/site/page', 'session' => ['_language' => 'de']],
                    ['/alias/site/page', 'cookie' => ['_language' => 'fr']],
                    ['/de/site/page', 'server' => ['HTTP_X_GEO_COUNTRY' => 'DEU']],
                ],
                '/en/site/page/' => '/en/site/page',

                // Custom URL rule
                '/custom' => '/en/custom',
                '/custom/' => '/en/custom',
                '/en/custom' => false,
                '/en/custom/' => '/en/custom',
                '/de/custom' => false,
                '/de/custom/' => '/de/custom',
                '/slash' => '/en/slash/',
                '/slash/' => '/en/slash/',
                '/en/slash' => '/en/slash/',
                '/en/slash/' => false,
                '/de/slash' => '/de/slash/',
                '/de/slash/' => false,

                // Params
                '/?a=b' => '/en?a=b',
                '/site/page/?a=b' => '/en/site/page?a=b',
                '/en/site/page/?a=b' => '/en/site/page?a=b',
                '/custom?a=b' => '/en/custom?a=b',
                '/custom/?a=b' => '/en/custom?a=b',
                '/de/custom/?a=b' => '/de/custom?a=b',
                '/slash?a=b' => '/en/slash/?a=b',
                '/slash/?a=b' => '/en/slash/?a=b',
                '/en/slash?a=b' => '/en/slash/?a=b',
                '/de/slash?a=b' => '/de/slash/?a=b',
                '/slug/value/' => '/en/slug/value',
                '/en/slug/value/' => '/en/slug/value',
                '/de/slug/value/' => '/de/slug/value',
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
                        $server = isset($params['server']) ? $params['server'] : [];
                        $this->performRedirectTest($from, $url, $urlManager, $request, $session, $cookie, $server);
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
    public function performRedirectTest($from, $to, $urlManager, $request = [], $session = [], $cookie = [], $server = [])
    {
        $this->tearDown();
        $this->mockUrlManager($urlManager);
        if (!empty($session)) {
            @session_start();
            $_SESSION = $session;
        }
        if (!empty($cookie)) {
            $_COOKIE = $cookie;
        }
        if (!empty($server)) {
            foreach ($server as $key => $value) {
                $_SERVER[$key] = $value;
            }
        }
        $configMessage = print_r([
            'from' => $from,
            'to' => $to,
            'urlManager' => $urlManager,
            'request' => $request,
            'session' => $session,
            'cookie' => $cookie,
            'server' => $server,
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
