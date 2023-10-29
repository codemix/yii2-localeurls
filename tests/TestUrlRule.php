<?php

declare(strict_types=1);

namespace tests;

use Yii;
use yii\web\UrlRuleInterface;

final class TestUrlRule implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'ruleclass/test') {
            $language = $params['slugLanguage'] ?? Yii::$app->language;
            return match ($language) {
                'de' => 'ruleclass-deutsch',
                'fr' => 'ruleclass-francais',
                default => 'ruleclass-english',
            };
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
