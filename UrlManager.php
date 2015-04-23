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
        if ($this->enableLocaleUrls) {
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
        if ($this->enableLocaleUrls) {
            $this->processLocaleUrl($request);
        }
        return parent::parseRequest($request);
    }

    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        if ($this->enableLocaleUrls) {
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
            if (!$languageRequired && !$this->enableDefaultSuffix && $language===$this->getDefaultLanguage()) {
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
            $pathInfo = mb_substr($pathInfo, mb_strlen($m[1].$m[2]));
            if (isset($this->languages[$m[1]])) {
                // Replace alias with language code
                $language = $this->languages[$m[1]];
            } elseif (preg_match('/^(..)\-(...?)$/',$m[1], $lm)) {
                // Convert wildcard country to uppercase (de-at -> de-AT)
                $language = $lm[1].'-'.strtoupper($lm[2]);

                // Redirect de-AT to de-at
                if ($language===$m[1]) {
                    $this->redirectToLanguage($lm[1].'-'.strtolower($lm[2]), '/'.$m[1]);
                }
            } else {
                $language = $m[1];
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
                $this->redirectToLanguage('', '/'.$this->_defaultLanguage);
            }
            $request->setPathInfo($pathInfo);
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
                    if (in_array($acceptable, $this->languages)) {
                        $language = $acceptable;
                        break;
                    } else {
                        $parts = explode('-', $acceptable);
                        if (count($parts)===2) {
                            // Some browsers send 'de-de' instead of 'de-DE'
                            $acceptable = "$parts[0]-".strtoupper($parts[1]);
                            if (in_array($acceptable, $this->languages)) {
                                $language = $acceptable;
                                break;
                            }
                            // Finally also try 'de' if 'de-DE' is not found
                            if (in_array($parts[0], $this->languages)) {
                                $language = $parts[0];
                                break;
                            }
                        }
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
     * Redirect to the current URL with given language code applied
     *
     * @param string $language the language code to add. Can also be empty to not add any language code.
     * @param string|null $remove the language code to remove from the pathInfo
     */
    protected function redirectToLanguage($language, $remove = null)
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
            if ($remove) {
                $redirectUrl .= '/'.substr($pathInfo, strlen($remove));
            } else {
                $redirectUrl .= '/'.$pathInfo;
            }
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
            throw new \yii\base\Exception($redirectUrl);
        } else {
            Yii::$app->end();
        }
    }
}
