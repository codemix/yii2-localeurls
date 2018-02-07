<?php
use yii\web\UrlRuleInterface;

class TestUrlRule implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'ruleclass/test') {
            $language = isset($params['slugLanguage']) ?
                $params['slugLanguage'] : Yii::$app->language;
            switch ($language) {
                case 'de':
                    return 'ruleclass-deutsch';
                case 'fr':
                    return 'ruleclass-francais';
                case 'en':
                case 'en-US':
                default:
                    return 'ruleclass-english';
            }
        }
        return false;
    }

    public function parseRequest($manager, $request)
    {
        $language = Yii::$app->language;
        $pathInfo = $request->pathInfo;
        if ($pathInfo === 'ruleclass-deutsch') {
            $slugLanguage = 'de';
        } elseif ($pathInfo === 'ruleclass-francais') {
            $slugLanguage = 'fr';
        } elseif ($pathInfo === 'ruleclass-english') {
            $slugLanguage = 'en';
        } else {
            return false;
        }

        if ($language === $slugLanguage) {
            return ['ruleclass/test', []];
        } else {
            // Redirect to correct slug language
            $url = ['/ruleclass/test', 'slugLanguage' => $language];
            Yii::$app->response->redirect($url);

            if (YII2_LOCALEURLS_TEST) {
                // Response::redirect($url) above will call `Url::to()` internally.
                // So to really test for the same final redirect URL here, we need
                // to call Url::to(), too.
                throw new \yii\base\Exception(\yii\helpers\Url::to($url));
            } else {
                Yii::$app->end();
            }
            return false;
        }
    }
}
