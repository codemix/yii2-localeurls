<?php
/**
 * Created by PhpStorm.
 * User: artem_000
 * Date: 9/3/2015
 * Time: 12:43 PM
 */

namespace meshzp\localeurls;

use Yii;
use yii\bootstrap\Dropdown;

class LangSelector extends Dropdown
{

    private static $_labels;

    private static $_currentLanguageLabel;

    private $_isError;

    public function init()
    {
        $route = Yii::$app->controller->route;
        $appLanguage = Yii::$app->language;
        $params = $_GET;
        $this->_isError = $route === Yii::$app->errorHandler->errorAction;

        array_unshift($params, '/'.$route);

        foreach (Yii::$app->urlManager->languages as $language) {
            $isWildcard = substr($language, -2)==='-*';
            if (
                $language===$appLanguage ||
                // Also check for wildcard language
                $isWildcard && substr($appLanguage,0,2)===substr($language,0,2)
            ) {
                self::$_currentLanguageLabel = $language;
                continue;   // Exclude the current language
            }
            if ($isWildcard) {
                $language = substr($language,0,2);
            }
            $params['language'] = $language;
            $this->items[] = [
                'label' => self::label($language),
                'url' => $params,
            ];
        }
        parent::init();
    }

    public function run()
    {
        // Only show this widget if we're not on the error page
        if ($this->_isError) {
            return '';
        } else {

            $html = '<li class="dropdown"><a href="#" data-toggle="dropdown" class="dropdown-toggle">'.PHP_EOL.
                self::label(self::$_currentLanguageLabel).'<b class="caret"></b></a>'.PHP_EOL;
            $html .= parent::run();
            $html .= '</li>';
            return $html;
        }
    }

    public static function label($code)
    {
        if (self::$_labels===null) {
            self::$_labels = [
                'ru-RU' => Yii::t(Yii::$app->urlManager->translateCategory, 'Русский'),
                'en-US' => Yii::t(Yii::$app->urlManager->translateCategory, 'Английский'),
            ];
        }

        return isset(self::$_labels[$code]) ? self::$_labels[$code] : null;
    }

}