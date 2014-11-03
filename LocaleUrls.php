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
     * @var array list of available language codes. More specific patterns should come first, e.g. 'en_us'
     * before 'en'. This can also contain mapping of <url_value> => <language>, e.g. 'english' => 'en'.
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
        $this->_defaultLanguage = Yii::$app->language;
        $this->processRequest();
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
        $languages = [];
        foreach ($this->languages as $k => $v) {
            $languages[] = is_string($k) ? $k : $v;
        }
        $pattern = implode('|', $languages);
        if (preg_match("#^($pattern)\b(/?)#", $pathInfo, $m)) {
            $pathInfo = mb_substr($pathInfo, mb_strlen($m[1].$m[2]));
            $language = $m[1];
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
                $url = $request->getBaseUrl().'/'.$pathInfo;
                $queryString = $request->getQueryString();
                if (!empty($queryString)) {
                    $url .= "?$queryString";
                }
                Yii::$app->getResponse()->redirect($url);
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

            $baseUrl = $request->getBaseUrl();
            $length = strlen($baseUrl);
            $url = rtrim($request->getUrl(), '/');
            $url = $length ? substr_replace($url, "/$language", $length+1, 0) : "/$language$url";
            Yii::$app->getResponse()->redirect($url);
        }
    }
}
