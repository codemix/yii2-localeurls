Yii2 Locale URLs
================

Automatic locale/language management through URLs for Yii 2.

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
autodetected from the browser settings - but the user can still access
other languages simply by calling a URL with another language code.

The last requested language is also persisted in the user session and
in a cookie. So if the user tries to access your site without a language
code in the URL, he'll get redirected to the language he had used on
his last visit.

All the above (and more) is configurable of course.


## Installation

Install the package through [composer](http://getcomposer.org):

    composer require codemix/yii2-localeurls

You then need to configure two application components in your application
configuration:

```php
<?php
return [
    // ...
    'components' => [
        // ...

        'localeUrls' => [
            'class' => 'codemix\localeurls\LocaleUrls',

            // List all supported languages here
            'languages' => ['en_us', 'en', 'fr', 'de']
        ]

        // Override the urlManager component
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
        ]

        // ...
    ]
];
```

Now you're ready to use the extension.

> Note: You can still configure custom URL rules as usual. Just ignore any `language`
> parameter as it will get removed before parsing and added after creating a URL.

> Note 2: The language code will be removed from the
> [pathInfo](http://www.yiiframework.com/doc-2.0/yii-web-request.html#$pathInfo-detail).

## Mode of operation and configuration

### Creating URLs

All created URLs will contain the code of the current application language. So if the
language was detected to be `de` and you use:

```php
<?php $url = Url:to(['demo/action']) ?>
<?= Html::a('Click', ['demo/action']) ?>
```

you'll get URLs like

    /de/demo/action

To create a link to switch the application to a different language, you can
explicitely add the `language` URL parameter:

```php
<?= $url = Url:to(['demo/action', 'language'=>'fr']) ?>
<?= Html::a('Click', ['demo/action', 'language'=>'fr']) ?>
```

This will give you a URL like

    /fr/demo/action

> Note: The URLs may look different if you use custom URL rules. In this case
> the language parameter is always prepended/insterted to the final relative/absolute URL.

If for some reason you want to use a different name than `language` for that URL
parameter you can configure it through the `languageParam` option of the `urlManager`
component.

### Default Language

The default language is configured via the
[language](http://www.yiiframework.com/doc-2.0/yii-base-application.html#$language-detail)
parameter of your application configuration.

By default the URLs for the default language won't contain any language code. For example:

    /
    /some/page

If the site is accessed with URLs containing the default language code, the visitor gets
redirected to the URLs without language code. For example if default language is `fr`:

    /fr/            -> Redirect to /
    /fr/some/page   -> Redirect to /some/page

If `enableDefaultSuffix` is changed to `true` it's vice versa. Each language including
the default language now uses an explicit language code in the URL. URLs without
language code are no longer accessible:

    /fr
    /fr/some/page
    /               -> Redirect to /fr
    /some/page      -> Redirect to /fr/some/page

### Language Configuration

All languages including the default language must be configured in the `languages`
parameter of the `localeUrls` component. You should list more specific language
codes before the similar looking generic ones (i.e. 'en_us' before 'en'):

    'languages' => ['en_us','en_uk','en','fr','de_at','de'],

You can also use friendlier names in URLs, which are configured like so:

    'languages' => ['en','german'=>'de'],

```php
<?= Url:to(['demo/action', 'language'=>'de']) ?>
```

This will give you a URL like

    /german/demo/action

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

Persistence is enabled by default and can be disabled by setting `enablePersistence`
to `false` in the `localeUrls` component.

You can modify other persistence settings with:

 * `languageCookieDuration`: How long in seconds to store the language information in a cookie.
   Set to `false` to disable the cookie.
 * `languageCookieName`: The name of the language cookie. Default is `_language`.
 * `languageSessionKey`: The name of the language session key. Default is `_language`.

#### Reset To Default Language

You'll notice, that there's one problem, if `enableDefaultSuffix` is `false` (which
is the default) and the user has e.g. stored `de` as last language. How can we now
access the site in the default language? Because if we try `/` we'd be redirected 
to `/de/`.

The answer is simple: To create a reset URL, you explicitely include the language code
for the default language in the URL. For example if default language is `fr`:

```php
<?= Url:to(['demo/action', 'language'=>'fr']) ?>
```

    /fr/demo/action -> Redirect to /demo/action

In this case, `fr` will first be stored as last used language before the user is redirected.

### Language Detection

If a user visits your site for the first time and there's no language stored in session
or cookie (or persistence is turned off), then the language is detected from the visitor's
browser settings. If one of the preferred languages matches your language, it will be
used as application language (and also persisted if persistenc is enabled).

To disable this, you can set `enableLanguageDetection` to `false`. It's enabled by default.
