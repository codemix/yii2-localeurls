<?php
namespace codemix\localeurls;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\UrlManager as BaseUrlManager;

/**
 * UrlManager
 *
 * An extension of yii\web\UrlManager that takes care of adding a language parameter to all
 * created URLs.
 */
class UrlManager extends BaseUrlManager
{
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
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->enablePrettyUrl) {
            throw new InvalidConfigException('Locale URL support requires enablePrettyUrl to be set to true.');
        }

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        $params = (array) $params;
        $localeUrls = Yii::$app->localeUrls;

        if (isset($params[$this->languageParam])) {
            $language = $params[$this->languageParam];
            unset($params[$this->languageParam]);
            $languageRequired = true;
        } else {
            $language = Yii::app()->language;
            $languageRequired = false;
        }

        $url = parent::createUrl($params);

        // Unless a language was explicitely specified in the parameters we can return a URL without any prefix
        // for the default language, if suffixes are disabled for the default language. In any other case we
        // always add the suffix, e.g. to create "reset" URLs that explicitely contain the default language.
        if (!$languageRequired && !$localeurls->enableDefaultSuffix && $language===$localeUrls->getDefaultLanguage()) {
            return  $url;
        } else {
            $key = array_search($language, $localeUrls->languages);
            $baseUrl = $this->getBaseUrl();
            $length = strlen($baseUrl);
            if (is_string($key)) {
                $language = $key;
            }
            return $length ?  substr_replace($url, "$baseUrl/$language", 1, $length) : "/$language$url";
        }
    }
}
