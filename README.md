Yii2 Locale URLs
================

[![Build Status](https://secure.travis-ci.org/codemix/yii2-localeurls.png)](http://travis-ci.org/codemix/yii2-localeurls)
[![Latest Stable Version](https://poser.pugx.org/codemix/yii2-localeurls/v/stable.svg)](https://packagist.org/packages/codemix/yii2-localeurls)
[![Total Downloads](https://poser.pugx.org/codemix/yii2-localeurls/downloads)](https://packagist.org/packages/codemix/yii2-localeurls)
[![Latest Unstable Version](https://poser.pugx.org/codemix/yii2-localeurls/v/unstable.svg)](https://packagist.org/packages/codemix/yii2-localeurls)
[![License](https://poser.pugx.org/codemix/yii2-localeurls/license.svg)](https://packagist.org/packages/codemix/yii2-localeurls)


Automatic locale/language management through URLs for Yii 2.

> **IMPORTANT:** If you upgraded from version 1.0.* you have to modify your configuration.
> Please check the section on [Upgrading](#upgrading) below.

## Features

With this extension you can use URLs that contain a language code like:

    /en/some/page
    /de/some/page
    http://www.example.com/en/some/page
    http://www.example.com/de/some/page

You can also configure friendly names if you want:

    http://www.example.com/english/some/page
    http://www.example.com/deutsch/some/page

The language code is automatically added whenever you create a URL, and
read back when a URL is parsed. For best user experience the language is
autodetected from the browser settings, if no language is used in the URL.
The user can still access other languages, though, simply by calling a URL
with another language code.

The last requested language is also persisted in the user session and
in a cookie. So if the user tries to access your site without a language
code in the URL, he'll get redirected to the language he had used on
his last visit.

All the above (and more) is configurable of course.


## Installation

Install the package through [composer](http://getcomposer.org):

    composer require codemix/yii2-localeurls

And then add this to your application configuration:

```php
<?php
return [

    // ...

    'components' => [
        // ...

        // Override the urlManager component
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',

            // List all supported languages here
            // Make sure, you include your app's default language.
            'languages' => ['en-US', 'en', 'fr', 'de', 'es-*'],
        ]

        // ...
    ]
];
```

Now you're ready to use the extension.

> Note: You can still configure custom URL rules as usual. Just ignore any `language` parameter
> in your URL rules as it will get removed before parsing and added after creating a URL.

> Note 2: The language code will be removed from the
> [pathInfo](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$pathInfo-detail).

## Mode of operation and configuration

### Creating URLs

All created URLs will contain the code of the current application language. So if the
language was detected to be `de` and you use:

```php
<?php $url = Url::to(['demo/action']) ?>
<?= Html::a('Click', ['demo/action']) ?>
```

you'll get URLs like

    /de/demo/action

To create a link to switch the application to a different language, you can
explicitly add the `language` URL parameter:

```php
<?= $url = Url::to(['demo/action', 'language' => 'fr']) ?>
<?= Html::a('Click', ['demo/action', 'language' => 'fr']) ?>
```

This will give you a URL like

    /fr/demo/action

> Note: The URLs may look different if you use custom URL rules. In this case
> the language parameter is always prepended/inserted to the final relative/absolute URL.

If for some reason you want to use a different name than `language` for that URL
parameter you can configure it through the `languageParam` option of the `urlManager`
component.

### Default Language

The default language is configured via the
[language](http://www.yiiframework.com/doc-2.0/yii-base-application.html#$language-detail)
parameter of your application configuration. You always have to include this
language in the `$languages` configuration (see below).

By default the URLs for the default language won't contain any language code. For example:

    /
    /some/page

If the site is accessed with URLs containing the default language code, the visitor gets
redirected to the URLs without language code. For example if default language is `fr`:

    /fr/            -> Redirect to /
    /fr/some/page   -> Redirect to /some/page

If `enableDefaultLanguageUrlCode` is changed to `true` it's vice versa. The default language
is now treated like any other configured language. Requests with URL that don't contain a
language code are no longer accessible:

    /fr
    /fr/some/page
    /               -> Redirect to /fr
    /some/page      -> Redirect to /fr/some/page

### Language Configuration

All languages **including the default language** must be configured in the `languages`
parameter of the `localeUrls` component:

    'languages' => ['en-US', 'en-UK', 'en', 'fr', 'de-AT', 'de'],

> **Note:** If you use country codes, they should always be configured in upper case letters
> as shown above. The URLs will still always use lowercase codes. If a URL with an uppercase
> code like `en-US` is used, the user will be redirected to the lowercase `en-us` variant.
> The application language will always use the correct `en-US` code. If you don't want to
> redirect URLs with lowercase country code, you can set the `keepUppercaseLanguageCode`
> option to `true`.

If you want your URL to optionally contain *any* country variant you can also use a wildcard pattern:

    'languages' => ['en-*', 'de-*'],

Now any URL that matches `en-??` or `de-??` could be used, like `en-us` or `de-at`.
URLs without a country code like `en` and `de` will also still work:

    /en/demo/action
    /en-us/demo/action
    /en-en/demo/action
    /de/demo/action
    /de-de/demo/action
    /de-at/demo/action

The URLs with a country code will set the full `ll-CC` code as Yii language whereas the
URLs with a language code only, will lead to `ll` as configured language.

> **Note:** You don't need this if all you want is a fallback of `de-AT` to `de` for
> languages detected from the browser settings. See the section on [Language Detection](#language-detection) below.

You can also use friendlier names or aliases in URLs, which are configured like so:

    'languages' => ['en', 'german' => 'de', 'br' => 'pt-BR'],

```php
<?= Url::to(['demo/action', 'language' => 'de']) ?>
```

This will give you URLs like

    /german/demo/action
    /br/demo/action

and set the respective language to `de` or `pt-PR` if matched.

### Persistence

The last language a visitor has used will be stored in the user session and in a cookie.
If the user visits your site again without a language code, he will get redirected
to the stored language.

For example, if the user first visits:

    /de/some/page

then after some time comes back to one of the following URLs:

    /some/page      -> Redirect to /de/some/page
    /               -> Redirect to /de/
    /dk/some/page

In the last case, `dk` will be stored as last language.

Persistence is enabled by default and can be disabled by setting `enableLanguagePersistence`
to `false` in the `localeUrls` component.

You can modify other persistence settings with:

 * `languageCookieDuration`: How long in seconds to store the language information in a cookie.
   Set to `false` to disable the cookie.
 * `languageCookieName`: The name of the language cookie. Default is `_language`.
 * `languageCookieOptions`: Other options to set on the language cookie.
 * `languageSessionKey`: The name of the language session key. Default is `_language`.
    Since 1.6.0 this can also be set to `false` to not use the session at all.

#### Reset To Default Language

You'll notice, that there's one problem, if `enableDefaultLanguageUrlCode` is `false` (which
is the default) and the user has e.g. stored `de` as last language. How can we now
access the site in the default language? Because if we try `/` we'd be redirected 
to `/de/`.

The answer is simple: To create a reset URL, you explicitly include the language code
for the default language in the URL. For example if default language is `fr`:

```php
<?= Url::to(['demo/action', 'language' => 'fr']) ?>
```

    /fr/demo/action -> Redirect to /demo/action

In this case, `fr` will first be stored as last used language before the user is redirected.

If you explicitely need to create a URL to the default language without any language code,
you can also pass an empty string as language:

```php
<?= Url::to(['demo/action', 'language' => '']) ?>
```

This will give you:

    /demo/action


#### Language Change Event

When persistence is enabled, the component will fire a `languageChanged` event
whenever the language stored in session or cookie changes. Here's an example
how this can be used to track user languages in the database:

```php
<?php

'urlManager' => [
    'class' => 'codemix\localeurls\UrlManager',
    'languages' => ['en', 'fr', 'de'],
    'on languageChanged' => `\app\components\User::onLanguageChanged',
]
```

The static class method in `User` could look like this:

```php
<?php
public static function onLanguageChanged($event)
{
    // $event->language: new language
    // $event->oldLanguage: old language

    // Save the current language to user record
    $user = Yii::$app->user;
    if (!$user->isGuest) {
        $user->identity->language = $event->language;
        $user->identity->save();
    }
}
```
> **Note:** A language may already have been selected before a user logs in or
> signs up. So you should also save or update the language in these cases.


### Language Detection

If a user visits your site for the first time and there's no language stored in session
or cookie (or persistence is turned off), then the language is detected from the visitor's
browser settings. If one of the preferred languages matches your language, it will be
used as application language (and also persisted if persistence is enabled).

To disable this, you can set `enableLanguageDetection` to `false`. It's enabled by default.

If the browser language contains a country code like `de-AT` and you only have `de` in your
`$languages` configuration, it will fall back to that language. Only if you've used a wildcard
like `de-*` or have explicitly configured `de-AT` or an alias like `'at' => 'de-AT'`, the
browser language including the country code will be used.

Let's look at an example configuration to better understand, how the `$languages` configuration
affects language detection and the created URLs.

```php
'languages' => [
  'en',
  'at' => 'de-AT',
  'de',
  'pt-*'
],
```

Now say a user visits your site for the first time. Depending on his browser settings, he will
be directed to different URLs.

Accept-Language Header              | Resulting URL code    | Resulting Yii language
------------------------------------|-----------------------|-----------------------
`en`, `en-us`, `en-US`, ...         | `/en`                 | `en`
`de-at`, `de-AT`                    | `/at`                 | `de-AT`
`de`, `de-de`, `de-DE`, `de-ch`, ...| `/de`                 | `de`
`pt-BR`, `pt-br`                    | `/pt-br`              | `pt-BR`
`pt-PT`, `pt-pt`                    | `/pt-pt`              | `pt-PT`
Any other `pt-CC` code              | `/pt-cc`              | `pt-CC`
`pt`                                | `/pt`                 | `pt`


#### Detection via GeoIP server module

Since 1.7.0 language can also be detected via the webserver's GeoIP module.
Note though that this only happens if no valid language was found in the
browser settings.

For this feature to work the related GeoIp module must already be installed and
it must provide the country code in a server variable in `$_SERVER`. You can
configure the key in `$geoIpServerVar`. The default is `HTTP_X_GEO_COUNTRY`.

To enable this feature, you have to provide a list of GeoIp country codes and
index them by the corresponding language that should be set:

```php
'geoIpLanguageCountries' => [
    'de' => ['DEU', 'AUT'],
    'pt' => ['PRT', 'BRA'],
],
```


### Excluding Routes / URLs

You may want to disable the language processing for some routes and URLs with the
`$ignoreLanguageUrlPatterns` option:

```php
<?php
    'ignoreLanguageUrlPatterns' => [
        // route pattern => url pattern
        '#^site/(login|register)#' => '#^(signin|signup)#',
        '#^api/#' => '#^api/#',
    ],
```

Both, keys and values are regular expressions. The keys are patterns that match routes
to exclude from language processing during *URL creation*, whereas the values are patterns
for [pathInfo](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$pathInfo-detail)
that should be excluded during *URL parsing*.

> Note: Keys and values don't necessarily have to relate to each other. It's just for
> convenience, that the configuration is combined into a single option.

## Example Language Selection Widget

There's no widget for language selection included, because there are simply too many options
for the markup and behavior of such a widget. But it's very easy to build. Here's the basic idea:

```php
<?php
use Yii;
use yii\bootstrap\Dropdown;

class LanguageDropdown extends Dropdown
{
    private static $_labels;

    private $_isError;

    public function init()
    {
        $route = Yii::$app->controller->route;
        $appLanguage = Yii::$app->language;
        $params = $_GET;
        $this->_isError = $route === Yii::$app->errorHandler->errorAction;

        array_unshift($params, '/' . $route);

        foreach (Yii::$app->urlManager->languages as $language) {
            $isWildcard = substr($language, -2) === '-*';
            if (
                $language === $appLanguage ||
                // Also check for wildcard language
                $isWildcard && substr($appLanguage, 0, 2) === substr($language, 0, 2)
            ) {
                continue;   // Exclude the current language
            }
            if ($isWildcard) {
                $language = substr($language, 0, 2);
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
            return parent::run();
        }
    }

    public static function label($code)
    {
        if (self::$_labels === null) {
            self::$_labels = [
                'de' => Yii::t('language', 'German'),
                'fr' => Yii::t('language', 'French'),
                'en' => Yii::t('language', 'English'),
            ];
        }

        return isset(self::$_labels[$code]) ? self::$_labels[$code] : null;
    }
}
```

## Upgrading

### Changes from 1.0.* to 1.1.*

If you upgrade from a 1.0.* version you'll have to modify your configuration. There no
longer is a `localeUrls` component now. Instead everything was merged into our custom
`urlManager` component. So you should move any configuration for the `localeUrls` component
into the `urlManager` component.

Two options also have been renamed for more clarity:

 * `enableDefaultSuffix` is now `enableDefaultLanguageUrlCode`
 * `enablePersistence` is now `enableLanguagePersistence`

So if your configuration looked like this before:

```php
<?php
return [
    'bootstrap' => ['localeUrls'],
    'components' => [
        'localeUrls' => [
            'languages' => ['en-US', 'en', 'fr', 'de', 'es-*'],
            'enableDefaultSuffix' => true,
            'enablePersistence' => false,
        ],
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
        ]
    ]
];
```

you should now change it to:

```php
<?php
return [
    'components' => [
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
            'languages' => ['en-US', 'en', 'fr', 'de', 'es-*'],
            'enableDefaultLanguageUrlCode' => true,
            'enableLanguagePersistence' => false,
        ]
    ]
];
```
