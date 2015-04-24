<?php
namespace codemix\localeurls;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

/**
 * LocaleUrls
 *
 * This class automatically detects the language from request URLs and optionally persists it in a cookie.
 */
class LocaleUrls extends Component
{
    /**
     * @var array list of available language codes. Languages with country code should contain the country
     * in uppercase and come before the language without lanuge, like `'en-US', 'en', 'de-AT', 'de'`.
     * To support any country variant in the URL, a wildcard can be used like `de-*` which would accept
     * URL codes like `de-de`, `de-at`, and so on. A language can also be configured using an alias in the
     * form of `<url_code> => <language>` like `'at' => 'de-AT'`
     */
    public $languages = [];

    /**
     * @var bool whether to use a suffix for the default language. If this is `true`, then the default language
     * will always use a URL suffix e.g. `/fr`, and requests to `/` will be redirected. If this is `false`
     * then `/` will be used for the default language. Any request to `/fr` will then get redirected to `/`.
     * In this case the latter URL can also be used to "reset" the cookie value to the default language.
     * Default is `false`.
     */
    public $enableDefaultSuffix = false;

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
    public $enablePersistence = true;

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
     * if `$enablePersistence` is true. Set to `false` to disable the language cookie completely.
     * Default is 30 days.
     */
    public $languageCookieDuration = 2592000;

    /**
     * @var string the language that was initially set in the application configuration
     */
    protected $_defaultLanguage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->getRequest()->isConsoleRequest)
        {
            $this->_defaultLanguage = Yii::$app->language;
            $this->processRequest();
        }
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
     * Check the URL for a language parameter and update Request::pathInfo() if found.
     * Otherwhise try to detect language from persistent data or from browser settings.
     */
    protected function processRequest()
    {
        $request = Yii::$app->getRequest();
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
            if ($this->enablePersistence) {
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
            if (!$this->enableDefaultSuffix && $language===$this->_defaultLanguage) {
                $this->redirectToLanguage('');
            }
        } else {
            $language = null;
            if ($this->enablePersistence) {
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
                if (!$this->enableDefaultSuffix) {
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
        $request = Yii::$app->getRequest();

        // Examples:
        // 1) /baseurl/index.php/some/page?q=foo
        // 2) /baseurl/some/page?q=foo
        // 3)
        // 4) /some/page?q=foo

        if (Yii::$app->urlManager->showScriptName) {
            // 1) /baseurl/index.php
            // 2) /baseurl/index.php
            // 3) /index.php
            // 4) /index.php
            $redirectUrl = $request->getScriptUrl();
        } else {
            // 1) /baseurl
            // 2) /baseurl
            // 3)
            // 4)
            $redirectUrl = $request->getBaseUrl();
        }

        if ($language) {
            $redirectUrl .= '/'.$language;
        }

        // 1) some/page
        // 2) some/page
        // 3)
        // 4) some/page
        $pathInfo = $request->getPathInfo();
        if ($pathInfo) {
            $redirectUrl .= '/'.$pathInfo;
        }

        // 1) q=foo
        // 2) q=foo
        // 3)
        // 4) q=foo
        $queryString = $request->getQueryString();
        if ($queryString) {
            $redirectUrl .= '?'.$queryString;
        }

        Yii::$app->getResponse()->redirect($redirectUrl);
        if (YII_ENV_TEST) {
            throw new \yii\base\Exception($redirectUrl);
        } else {
            Yii::$app->end();
        }
    }
}
