<?php
namespace codemix\localeurls;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\Cookie;
use yii\web\UrlManager as BaseUrlManager;

/**
 * UrlManager
 *
 * This class extends Yii's UrlManager and adds features to detect the language from the URL
 * or from browser settings transparently. It also can persist the language in the user session
 * and optionally in a cookie. It also adds the language parameter to any created URL.
 */
class UrlManager extends BaseUrlManager
{
    /**
     * @var array list of available language codes. More specific patterns should come first, e.g. 'en_us'
     * before 'en'. This can also contain mapping of <url_value> => <language>, e.g. 'english' => 'en'.
     */
    public $languages = [];

    /**
     * @var bool whether to enable locale URL specific features
     */
    public $enableLocaleUrls = true;

    /**
     * @var bool whether the default language should use an URL code like any other configured language.
     *
     * By default this is `false`, so for URLs without a language code the default language is assumed.
     * In addition any request to an URL that contains the default language code will be redirected to
     * the same URL without a language code. So if the default language is `fr` and a user requests
     * `/fr/some/page` he gets redirected to `/some/page`. This way the persistet language can be reset
     * to the default language.
     *
     * If this is `true`, then an URL that does not contain any language code will be redirected to the
     * same URL with default language code. So if for example the default language is `fr`, then
     * any request to `/some/page` will be redirected to `/fr/some/page`.
     *
     */
    public $enableDefaultLanguageUrlCode = false;

    /**
     * @var bool whether to detect the app language from the HTTP headers (i.e. browser settings).
     * Default is `true`.
     */
    public $enableLanguageDetection = true;

    /**
     * @var bool whether to store the detected language in session and (optionally) a cookie. If this
     * is `true` (default) and a returning user tries to access any URL without a language prefix,
     * he'll be redirected to the respective stored language URL (e.g. /some/page -> /fr/some/page).
     */
    public $enableLanguagePersistence = true;

    /**
     * @var string the name of the session key that is used to store the language. Default is '_language'.
     */
    public $languageSessionKey = '_language';

    /**
     * @var string the name of the language cookie. Default is '_language'.
     */
    public $languageCookieName = '_language';

    /**
     * @var int number of seconds how long the language information should be stored in cookie,
     * if `$enableLanguagePersistence` is true. Set to `false` to disable the language cookie completely.
     * Default is 30 days.
     */
    public $languageCookieDuration = 2592000;

    /**
     * @var array list of route and URL regex patterns to ignore during language processing. The keys
     * of the array are patterns for routes, the values are patterns for URLs. Route patterns are checked
     * during URL creation. If a pattern matches, no language parameter will be added to the created URL.
     * URL patterns are checked during processing incoming requests. If a pattern matches, the language
     * processing will be skipped for that URL. Examples:
     *
     * ~~~php
     * [
     *     '#^site/(login|register)#' => '#^(login|register)#'
     *     '#^api/#' => '#^api/#',
     * ]
     * ~~~
     */
    public $ignoreLanguageUrlPatterns = [];

    /**
     * @var string the language that was initially set in the application configuration
     */
    protected $_defaultLanguage;

    /**
     * @inheritdoc
     */
    public $enablePrettyUrl = true;

    /**
     * @var string if a parameter with this name is passed to any `createUrl()` method, the created URL
     * will use the language specified there. URLs created this way can be used to switch to a different
     * language. If no such parameter is used, the currently detected application language is used.
     */
    public $languageParam = 'language';

    /**
     * @var \yii\web\Request
     */
    protected $_request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->enableLocaleUrls && $this->languages) {
            if (!$this->enablePrettyUrl) {
                throw new InvalidConfigException('Locale URL support requires enablePrettyUrl to be set to true.');
            }
        }
        $this->_defaultLanguage = Yii::$app->language;
        return parent::init();
    }

    /**
     * @return string the `language` option that was initially set in the application config file,
     * before it was modified by this component.
     */
    public function getDefaultLanguage()
    {
        return $this->_defaultLanguage;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($request)
    {
        if ($this->enableLocaleUrls && $this->languages) {
            $process = true;
            if ($this->ignoreLanguageUrlPatterns) {
                $pathInfo = $request->getPathInfo();
                foreach ($this->ignoreLanguageUrlPatterns as $k => $pattern) {
                    if (preg_match($pattern, $pathInfo)) {
                        $process = false;
                    }
                }
            }
            if ($process) {
                $this->processLocaleUrl($request);
            }
        }
        return parent::parseRequest($request);
    }

    /**
     * @inheritdoc
     */
    public function createUrl($params)
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

            if (isset($params[$this->languageParam])) {
                $language = $params[$this->languageParam];
                unset($params[$this->languageParam]);
                $languageRequired = true;
            } else {
                $language = Yii::$app->language;
                $languageRequired = false;
            }

            $url = parent::createUrl($params);

            // Unless a language was explicitely specified in the parameters we can return a URL without any prefix
            // for the default language, if suffixes are disabled for the default language. In any other case we
            // always add the suffix, e.g. to create "reset" URLs that explicitely contain the default language.
            if (!$languageRequired && !$this->enableDefaultLanguageUrlCode && $language===$this->getDefaultLanguage()) {
                return  $url;
            } else {
                $key = array_search($language, $this->languages);
                $base = $this->showScriptName ? $this->getScriptUrl() : $this->getBaseUrl();
                $length = strlen($base);
                if (is_string($key)) {
                    $language = $key;
                }
                return $length ? substr_replace($url, "$base/$language", 0, $length) : "/$language$url";
            }
        } else {
            return parent::createUrl($params);
        }
    }

    /**
     * Checks for a language or locale parameter in the URL and rewrites the pathInfo if found.
     * If no parameter is found it will try to detect the language from persistent storage (session /
     * cookie) or from browser settings.
     *
     * @var \yii\web\Request $request
     */
    protected function processLocaleUrl($request)
    {
        $this->_request = $request;
        $pathInfo = $request->getPathInfo();
        $parts = [];
        foreach ($this->languages as $k => $v) {
            $value = is_string($k) ? $k : $v;
            if (substr($value, -2)==='-*') {
                $lng = substr($value, 0, -2);
                $parts[] = "$lng\-[a-z]{2,3}";
                $parts[] = $lng;
            } else {
                $parts[] = $value;
            }
        }
        $pattern = implode('|', $parts);
        if (preg_match("#^($pattern)\b(/?)#i", $pathInfo, $m)) {
            $request->setPathInfo(mb_substr($pathInfo, mb_strlen($m[1].$m[2])));
            $code = $m[1];
            if (isset($this->languages[$code])) {
                // Replace alias with language code
                $language = $this->languages[$code];
            } else {
                list($language,$country) = $this->matchCode($code);
                if ($country!==null) {
                    if ($code==="$language-$country") {
                        $this->redirectToLanguage(strtolower($code));
                    } else {
                        $language = "$language-$country";
                    }
                }
                if ($language===null) {
                    $language = $code;
                }
            }
            Yii::$app->language = $language;
            if ($this->enableLanguagePersistence) {
                Yii::$app->session[$this->languageSessionKey] = $language;
                if ($this->languageCookieDuration) {
                    $cookie = new Cookie([
                        'name' => $this->languageCookieName,
                        'httpOnly' => true
                    ]);
                    $cookie->value = $language;
                    $cookie->expire = time() + (int) $this->languageCookieDuration;
                    Yii::$app->getResponse()->getCookies()->add($cookie);
                }
            }

            // "Reset" case: We called e.g. /fr/demo/page so the persisted language was set back to "fr".
            // Now we can redirect to the URL without language prefix, if default prefixes are disabled.
            if (!$this->enableDefaultLanguageUrlCode && $language===$this->_defaultLanguage) {
                $this->redirectToLanguage('');
            }
        } else {
            $language = null;
            if ($this->enableLanguagePersistence) {
                $language = Yii::$app->session->get($this->languageSessionKey);
                if ($language===null) {
                    $language = $request->getCookies()->get($this->languageCookieName);
                }
            }
            if ($language===null && $this->enableLanguageDetection) {
                foreach ($request->getAcceptableLanguages() as $acceptable) {
                    list($language,$country) = $this->matchCode($acceptable);
                    if ($language!==null) {
                        $language = $country===null ? $language : "$language-$country";
                        break;
                    }
                }
            }
            if ($language===null || $language===$this->_defaultLanguage) {
                if (!$this->enableDefaultLanguageUrlCode) {
                    return;
                } else {
                    $language = $this->_defaultLanguage;
                }
            }

            $key = array_search($language, $this->languages);
            if ($key && is_string($key)) {
                $language = $key;
            }
            $this->redirectToLanguage(strtolower($language));
        }
    }

    /**
     * Tests whether the given code matches any of the configured languages.
     *
     * If the code is a single language code, and matches either
     *
     *  - an exact language as configured (ll)
     *  - a language with a country wildcard (ll-*)
     *
     * this language code is returned.
     *
     * If the code also contains a country code, and matches either
     *
     *  - an exact language/country code as configured (ll-CC)
     *  - a language with a country wildcard (ll-*)
     *
     * the code with uppercase country is returned. If only the language part matches
     * a configured language, that language is returned.
     *
     * @param string $code the code to match
     * @return array of [language, country] where both can be null if no match
     */
    protected function matchCode($code)
    {
        $language = $code;
        $country = null;
        $parts = explode('-', $code);
        if (count($parts)===2) {
            $language = $parts[0];
            $country = strtoupper($parts[1]);
        }

        if (in_array($code, $this->languages)) {
            return [$language, $country];
        } elseif (
            $country && in_array("$language-$country", $this->languages) ||
            in_array("$language-*", $this->languages)
        ) {
            return [$language, $country];
        } elseif (in_array($language, $this->languages)) {
            return [$language, null];
        } else {
            return [null, null];
        }
    }

    /**
     * Redirect to the current URL with given language code applied
     *
     * @param string $language the language code to add. Can also be empty to not add any language code.
     */
    protected function redirectToLanguage($language)
    {
        // Examples:
        // 1) /baseurl/index.php/some/page?q=foo
        // 2) /baseurl/some/page?q=foo
        // 3)
        // 4) /some/page?q=foo

        if ($this->showScriptName) {
            // 1) /baseurl/index.php
            // 2) /baseurl/index.php
            // 3) /index.php
            // 4) /index.php
            $redirectUrl = $this->_request->getScriptUrl();
        } else {
            // 1) /baseurl
            // 2) /baseurl
            // 3)
            // 4)
            $redirectUrl = $this->_request->getBaseUrl();
        }

        if ($language) {
            $redirectUrl .= '/'.$language;
        }

        // 1) some/page
        // 2) some/page
        // 3)
        // 4) some/page
        $pathInfo = $this->_request->getPathInfo();
        if ($pathInfo) {
            $redirectUrl .= '/'.$pathInfo;
        }

        if ($redirectUrl === '') {
            $redirectUrl = '/';
        }

        // 1) q=foo
        // 2) q=foo
        // 3)
        // 4) q=foo
        $queryString = $this->_request->getQueryString();
        if ($queryString) {
            $redirectUrl .= '?'.$queryString;
        }

        Yii::$app->getResponse()->redirect($redirectUrl);
        if (YII_ENV_TEST) {
            throw new \yii\base\Exception(\yii\helpers\Url::to($redirectUrl));
        } else {
            Yii::$app->end();
        }
    }
}
