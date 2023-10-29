<?php

declare(strict_types=1);

namespace codemix\localeurls;

defined('YII2_LOCALEURLS_TEST') || define('YII2_LOCALEURLS_TEST', false);

use Yii;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\UrlNormalizerRedirectException;

use function array_map;
use function array_search;
use function array_shift;
use function array_unshift;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function property_exists;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function strlen;
use function strncmp;
use function strpos;
use function strtoupper;
use function substr;
use function substr_replace;
use function usort;

/**
 * This class extends Yii's UrlManager and adds features to detect the language from the URL or from browser settings
 * transparently. It also can persist the language in the user session and optionally in a cookie. It also adds the
 * language parameter to any created URL.
 */
class UrlManager extends \yii\web\UrlManager
{
    public const EVENT_LANGUAGE_CHANGED = 'languageChanged';

    /**
     * @var array list of available language codes. More specific patterns should come first, e.g. 'en_us' before 'en'.
     * This can also contain mapping of <url_value> => <language>, e.g. 'english' => 'en'.
     */
    public array $languages = [];

    /**
     * @var bool whether to enable locale URL specific features
     */
    public bool $enableLocaleUrls = true;

    /**
     * @var bool whether the default language should use an URL code like any other configured language.
     *
     * By default this is `false`, so for URLs without a language code the default language is assumed.
     * In addition any request to an URL that contains the default language code will be redirected to the same URL
     * without a language code. So if the default language is `fr` and a user requests `/fr/some/page` he gets
     * redirected to `/some/page`. This way the persistent language can be reset to the default language.
     *
     * If this is `true`, then an URL that does not contain any language code will be redirected to the same URL with
     * default language code. So if for example the default language is `fr`, then any request to `/some/page` will be
     * redirected to `/fr/some/page`.
     */
    public bool $enableDefaultLanguageUrlCode = false;

    /**
     * @var bool whether to detect the app language from the HTTP headers (i.e. browser settings).  Default is `true`.
     */
    public bool $enableLanguageDetection = true;

    /**
     * @var bool whether to store the detected language in session and (optionally) a cookie. If this is `true`
     * (default) and a returning user tries to access any URL without a language prefix, he'll be redirected to the
     * respective stored language URL (e.g. /some/page -> /fr/some/page).
     */
    public bool $enableLanguagePersistence = true;

    /**
     * @var bool whether to keep upper case language codes in URL. Default is `false` wich will e.g.  redirect `de-AT`
     * to `de-at`.
     */
    public bool $keepUppercaseLanguageCode = false;

    /**
     * @var bool|string the name of the session key that is used to store the language. If `false` no session is used.
     * Default is '_language'.
     */
    public string|bool $languageSessionKey = '_language';

    /**
     * @var string the name of the language cookie. Default is '_language'.
     */
    public string $languageCookieName = '_language';

    /**
     * @var int number of seconds how long the language information should be stored in cookie, if
     * `$enableLanguagePersistence` is true. Set to `false` to disable the language cookie completely.
     * Default is 30 days.
     */
    public int $languageCookieDuration = 2592000;

    /**
     * @var array configuration options for the language cookie. Note that `$languageCookieName` and
     * `$languageCookeDuration` will override any `name` and `expire` settings provided here.
     */
    public array $languageCookieOptions = [];

    /**
     * @var array list of route and URL regex patterns to ignore during language processing.
     * The keys of the array are patterns for routes, the values are patterns for URLs. Route patterns are checked
     * during URL creation. If a pattern matches, no language parameter will be added to the created URL.
     * URL patterns are checked during processing incoming requests. If a pattern matches, the language processing will
     * be skipped for that URL. Examples:
     *
     * ~~~php
     * [
     *     '#^site/(login|register)#' => '#^(login|register)#'
     *     '#^api/#' => '#^api/#',
     * ]
     * ~~~
     */
    public array $ignoreLanguageUrlPatterns = [];

    /**
     * @inheritdoc
     */
    public $enablePrettyUrl = true;

    /**
     * @var string if a parameter with this name is passed to any `createUrl()` method, the created URL will use the
     * language specified there. URLs created this way can be used to switch to a different language.
     * If no such parameter is used, the currently detected application language is used.
     */
    public string $languageParam = 'language';

    /**
     * @var string the key in $_SERVER that contains the detected GeoIP country.
     * Default is 'HTTP_X_GEO_COUNTRY' as used by mod_geoip in apache.
     */
    public string $geoIpServerVar = 'HTTP_X_GEO_COUNTRY';

    /**
     * @var array list of GeoIP countries indexed by corresponding language
     * code. The default is an empty list which disables GeoIP detection.
     * Example:
     *
     * ~~~php
     * [
     *     // Set app language to 'ru' for these GeoIp countries
     *     'ru' => ['RUS','AZE','ARM','BLR','KAZ','KGZ','MDA','TJK','TKM','UZB','UKR']
     *
     * ]
     * ~~~
     */
    public array $geoIpLanguageCountries = [];

    /**
     * @var int the HTTP status code. Default is 302.
     */
    public int $languageRedirectCode = 302;

    /**
     * @var string the language that was initially set in the application configuration.
     */
    protected string $_defaultLanguage;
    protected Request|null $_request = null;

    /**
     * @var bool whether locale URL was processed.
     */
    protected bool $_processed = false;

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException if `enableLocaleUrls` is true and `languages` is empty.
     */
    public function init(): void
    {
        if ($this->enableLocaleUrls && $this->languages && !$this->enablePrettyUrl) {
            throw new InvalidConfigException('Locale URL support requires enablePrettyUrl to be set to true.');
        }

        $this->_defaultLanguage = Yii::$app->language;
        parent::init();
    }

    /**
     * @return string the `language` option that was initially set in the application config file, before it was
     * modified by this component.
     */
    public function getDefaultLanguage(): string
    {
        return $this->_defaultLanguage;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws ExitException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function parseRequest($request): bool|array
    {
        if ($this->enableLocaleUrls && $this->languages) {
            $this->_request = $request;
            $process = true;

            if ($this->ignoreLanguageUrlPatterns) {
                $pathInfo = $request->getPathInfo();
                foreach ($this->ignoreLanguageUrlPatterns as $k => $pattern) {
                    if (preg_match($pattern, $pathInfo)) {
                        $message = "Ignore pattern '$pattern' matches '$pathInfo.' Skipping language processing.";
                        Yii::debug($message, __METHOD__);
                        $process = false;
                    }
                }
            }

            if ($process && !$this->_processed) {
                // Check if a normalizer wants to redirect
                $normalized = false;

                if (property_exists($this, 'normalizer') && $this->normalizer!==false) {
                    try {
                        parent::parseRequest($request);
                    } catch (UrlNormalizerRedirectException $e) {
                        $normalized = true;
                    }
                }

                $this->_processed = true;
                $this->processLocaleUrl($normalized);
            }
        }

        return parent::parseRequest($request);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function createUrl($params): array|string
    {
        if ($this->ignoreLanguageUrlPatterns) {
            $params = (array) $params;
            $route = trim($params[0], '/');

            foreach ($this->ignoreLanguageUrlPatterns as $pattern => $v) {
                if (preg_match($pattern, $route)) {
                    return parent::createUrl($params);
                }
            }
        }

        if ($this->enableLocaleUrls && $this->languages) {
            $params = (array) $params;
            $isLanguageGiven = isset($params[$this->languageParam]);
            $language = $isLanguageGiven ? $params[$this->languageParam] : Yii::$app->language;
            $isDefaultLanguage = $language === $this->getDefaultLanguage();

            if ($isLanguageGiven) {
                unset($params[$this->languageParam]);
            }

            $url = parent::createUrl($params);

            if (
                // Only add language if it's not empty and ...
                $language!=='' && (
                    // ... it's not the default language or default language uses URL code ...
                    !$isDefaultLanguage || $this->enableDefaultLanguageUrlCode ||

                    // ... or if a language is explicitely given, but only if
                    // either persistence or detection is enabled.  This way a
                    // "reset URL" can be created for the default language.
                    ($isLanguageGiven && ($this->enableLanguagePersistence || $this->enableLanguageDetection))
                )
            ) {
                $key = array_search($language, $this->languages, true);

                if (is_string($key)) {
                    $language = $key;
                }

                if (!$this->keepUppercaseLanguageCode) {
                    $language = strtolower($language);
                }

                // Calculate the position where the language code has to be inserted
                // depending on the showScriptName and baseUrl configuration:
                //
                //  - /foo/bar -> /de/foo/bar
                //  - /base/foo/bar -> /base/de/foo/bar
                //  - /index.php/foo/bar -> /index.php/de/foo/bar
                //  - /base/index.php/foo/bar -> /base/index.php/de/foo/bar
                //
                $prefix = $this->showScriptName ? $this->getScriptUrl() : $this->getBaseUrl();
                $insertPos = strlen($prefix);

                // Remove any trailing slashes for root URLs
                if ($this->suffix !== '/') {
                    if (count($params) === 1) {
                        // / -> ''
                        // /base/ -> /base
                        // /index.php/ -> /index.php
                        // /base/index.php/ -> /base/index.php
                        if ($url === $prefix . '/') {
                            $url = rtrim($url, '/');
                        }
                    } elseif (strncmp($url, $prefix . '/?', $insertPos + 2) === 0) {
                        // /?x=y -> ?x=y
                        // /base/?x=y -> /base?x=y
                        // /index.php/?x=y -> /index.php?x=y
                        // /base/index.php/?x=y -> /base/index.php?x=y
                        $url = substr_replace($url, '', $insertPos, 1);
                    }
                }

                // If we have an absolute URL the length of the host URL has to
                // be added:
                //
                //  - http://www.example.com
                //  - http://www.example.com?x=y
                //  - http://www.example.com/foo/bar
                //
                if (str_contains($url, '://')) {
                    // Host URL ends at first '/' or '?' after the schema
                    if (($pos = strpos($url, '/', 8))!==false || ($pos = strpos($url, '?', 8))!==false) {
                        $insertPos += $pos;
                    } else {
                        $insertPos += strlen($url);
                    }
                }

                if ($insertPos > 0) {
                    return substr_replace($url, '/' . $language, $insertPos, 0);
                }

                return '/' . $language . $url;
            }

            return $url;
        }

        return parent::createUrl($params);
    }

    /**
     * Checks for a language or locale parameter in the URL and rewrites the pathInfo if found.
     * If no parameter is found it will try to detect the language from persistent storage (session / cookie) or from
     * browser settings.
     *
     * @param bool $normalized whether a UrlNormalizer tried to redirect
     *
     * @throws InvalidConfigException
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws ExitException
     */
    protected function processLocaleUrl(bool $normalized): void
    {
        $pathInfo = $this->_request->getPathInfo();
        $parts = [];

        foreach ($this->languages as $k => $v) {
            $value = is_string($k) ? $k : $v;

            if (str_ends_with($value, '-*')) {
                $lng = substr($value, 0, -2);
                $parts[] = "$lng\-[a-z]{2,3}";
                $parts[] = $lng;
            } else {
                $parts[] = $value;
            }
        }

        // order by length to make longer patterns match before short patterns, e.g. put "en-GB" before "en"
        usort($parts, static function($a, $b) {
            $la = mb_strlen($a);
            $lb = mb_strlen($b);

            if ($la === $lb) {
                return 0;
            }

            return $la < $lb ? 1 : -1;
        });

        $pattern = implode('|', $parts);

        if (preg_match("#^($pattern)\b(/?)#i", $pathInfo, $m)) {
            $this->_request->setPathInfo(mb_substr($pathInfo, mb_strlen($m[1] . $m[2])));
            $code = $m[1];
            if (isset($this->languages[$code])) {
                // Replace alias with language code
                $language = $this->languages[$code];
            } else {
                // lowercase language, uppercase country
                [$language, $country] = $this->matchCode($code);

                if ($country!==null) {
                    if ($code === "$language-$country" && !$this->keepUppercaseLanguageCode) {
                        $this->redirectToLanguage(strtolower($code));   // Redirect ll-CC to ll-cc
                    } else {
                        $language = "$language-$country";
                    }
                }

                if ($language === null) {
                    $language = $code;
                }
            }

            Yii::$app->language = $language;
            Yii::debug("Language code found in URL. Setting application language to '$language'.", __METHOD__);

            if ($this->enableLanguagePersistence) {
                $this->persistLanguage($language);
            }

            // "Reset" case: We called e.g. /fr/demo/page so the persisted language was set back to "fr".
            // Now we can redirect to the URL without language prefix, if default prefixes are disabled.
            $reset = !$this->enableDefaultLanguageUrlCode && $language === $this->_defaultLanguage;

            if ($reset || $normalized) {
                $this->redirectToLanguage('');
            }
        } else {
            $language = null;

            if ($this->enableLanguagePersistence) {
                $language = $this->loadPersistedLanguage();
            }

            if ($language === null) {
                $language = $this->detectLanguage();
            }

            if ($language === null || $language === $this->_defaultLanguage) {
                if (!$this->enableDefaultLanguageUrlCode) {
                    return;
                }

                $language = $this->_defaultLanguage;
            }

            // #35: Only redirect if a valid language was found
            if ($this->matchCode($language) === [null, null]) {
                return;
            }

            $key = array_search($language, $this->languages, true);

            if ($key && is_string($key)) {
                $language = $key;
            }

            if (!$this->keepUppercaseLanguageCode) {
                $language = strtolower($language);
            }

            $this->redirectToLanguage($language);
        }
    }

    /**
     * @param string $language the language code to persist in session and cookie
     */
    protected function persistLanguage(string $language): void
    {
        if ($this->hasEventHandlers(self::EVENT_LANGUAGE_CHANGED)) {
            $oldLanguage = $this->loadPersistedLanguage();

            if ($oldLanguage !== $language) {
                Yii::debug("Triggering languageChanged event: $oldLanguage -> $language", __METHOD__);
                $this->trigger(self::EVENT_LANGUAGE_CHANGED, new LanguageChangedEvent([
                    'oldLanguage' => $oldLanguage,
                    'language' => $language,
                ]));
            }
        }

        if ($this->languageSessionKey !== false) {
            Yii::$app->getSession()[$this->languageSessionKey] = $language;
            Yii::debug("Persisting language '$language' in session.", __METHOD__);
        }

        if ($this->languageCookieDuration) {
            $cookie = new Cookie(
                array_merge(
                    ['httpOnly' => true],
                    $this->languageCookieOptions,
                    [
                        'name' => $this->languageCookieName,
                        'value' => $language,
                        'expire' => time() + $this->languageCookieDuration,
                    ],
                )
            );
            Yii::$app->getResponse()->getCookies()->add($cookie);
            Yii::debug("Persisting language '$language' in cookie.", __METHOD__);
        }
    }

    /**
     * @return string|null the persisted language code or null if none found
     */
    protected function loadPersistedLanguage(): string|null
    {
        $language = null;

        if ($this->languageSessionKey !== false) {
            $language = Yii::$app->session->get($this->languageSessionKey);
            $language!==null && Yii::debug("Found persisted language '$language' in session.", __METHOD__);
        }

        if ($language === null) {
            $language = $this->_request->getCookies()->getValue($this->languageCookieName);
            $language!==null && Yii::debug("Found persisted language '$language' in cookie.", __METHOD__);
        }

        return $language;
    }

    /**
     * @return string|null the language detected from request headers or via GeoIp module
     */
    protected function detectLanguage(): string|null
    {
        if ($this->enableLanguageDetection) {
            foreach ($this->_request->getAcceptableLanguages() as $acceptable) {
                [$language, $country] = $this->matchCode($acceptable);

                if ($language!==null) {
                    $language = $country === null ? $language : "$language-$country";
                    Yii::debug("Detected browser language '$language'.", __METHOD__);
                    return $language;
                }
            }
        }

        if (isset($_SERVER[$this->geoIpServerVar])) {
            foreach ($this->geoIpLanguageCountries as $key => $codes) {
                $country = $_SERVER[$this->geoIpServerVar];
                if (in_array($country, $codes, true)) {
                    Yii::debug("Detected GeoIp language '$key'.", __METHOD__);
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Tests whether the given code matches any of the configured languages.
     *
     * The return value is an array of the form `[$language, $country]`, where `$country` or both can be `null`.
     *
     * If the code is a single language code, and matches either
     *
     *  - an exact language as configured (ll)
     *  - a language with a country wildcard (ll-*)
     *
     * the code value will be returned as `$language`.
     *
     * If the code is of the form `ll-CC`, and matches either
     *
     *  - an exact language/country code as configured (ll-CC)
     *  - a language with a country wildcard (ll-*)
     *
     * `$country` well be set to the `CC` part of the configured language.
     *
     * If only the language part matches a configured language, only `$language`
     * will be set to that language.
     *
     * @param string $code the code to match
     * @return array of `[$language, $country]` where `$country` or both can be `null`
     */
    protected function matchCode(string $code): array
    {
        $hasDash = str_contains($code, '-');
        $lcCode = strtolower($code);
        $lcLanguages = array_map('strtolower', $this->languages);

        if (($key = array_search($lcCode, $lcLanguages, true)) === false) {
            if ($hasDash) {
                [$language, $country] = explode('-', $code, 2);
            } else {
                $language = $code;
                $country = null;
            }

            if (in_array($language . '-*', $this->languages, true)) {
                if ($hasDash) {
                    // TODO: Make wildcards work with script codes
                    // like `sr-Latn`
                    return [$language, strtoupper($country)];
                } else {
                    return [$language, null];
                }
            } elseif ($hasDash && in_array($language, $this->languages, true)) {
                return [$language, null];
            } else {
                return [null, null];
            }
        } else {
            $result = $this->languages[$key];
            return $hasDash ? explode('-', $result, 2) : [$result, null];
        }

        $language = $code;
        $country = null;
        $parts = explode('-', $code);

        if (count($parts) === 2) {
            $language = $parts[0];
            $country = strtoupper($parts[1]);
        }

        if (in_array($code, $this->languages, true)) {
            return [$language, $country];
        }

        if (
            ($country && in_array("$language-$country", $this->languages, true)) ||
            in_array("$language-*", $this->languages, true)
        ) {
            return [$language, $country];
        }

        if (in_array($language, $this->languages, true)) {
            return [$language, null];
        }

        return [null, null];
    }

    /**
     * Redirect to the current URL with given language code applied
     *
     * @param string $language the language code to add. Can also be empty to not add any language code.
     *
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws ExitException
     */
    protected function redirectToLanguage(string $language): void
    {
        try {
            $result = parent::parseRequest($this->_request);
        } catch (UrlNormalizerRedirectException $e) {
            if (is_array($e->url)) {
                $params = $e->url;
                $route = array_shift($params);
                $result = [$route, $params];
            } else {
                $result = [$e->url, []];
            }
        }

        if ($result === false) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        [$route, $params] = $result;

        if($language) {
            $params[$this->languageParam] = $language;
        }

        // See Yii Issues #8291 and #9161:
        $params += $this->_request->getQueryParams();
        array_unshift($params, $route);
        $url = $this->createUrl($params);

        // Required to prevent double slashes on generated URLs
        if ($this->suffix === '/' && $route === '' && count($params) === 1) {
            $url = rtrim($url, '/') . '/';
        }

        // Prevent redirects to same URL which could happen in certain
        // UrlNormalizer / custom rule combinations
        if ($url === $this->_request->url) {
            return;
        }

        Yii::debug("Redirecting to $url.", __METHOD__);
        Yii::$app->getResponse()->redirect($url, $this->languageRedirectCode);

        if (YII2_LOCALEURLS_TEST) {
            // Response::redirect($url) above will call `Url::to()` internally.
            // So to really test for the same final redirect URL here, we need
            // to call Url::to(), too.
            throw new Exception(Url::to($url));
        }

        Yii::$app->end();
    }
}
