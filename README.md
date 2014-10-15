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

Then you need to configure two application components in your application
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
<?= $url = Url:to(['demo/action']) ?>
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
parameter you can configure it through the `languageParam` option of the `UrlManager`
component.

### Default Language

The default language is the language that you configured as `language` parameter
in your application configuration. Depending on the `enableDefaultSuffix` option
of the `localeUrls` component, you can choose between two behaviors.

By default (`'enableDefaultSuffix' => false`) the default language will not use
a language suffix in your URLs. So for example if your default language is `fr`,
you'll get the following URLs:

    /               -> Homepage with default language
    /some/page      -> Some page with default language
    /fr/            -> Redirect to /
    /fr/some/page   -> Redirect to /some/page
    /de/some/page   -> Some page with language 'de'

If you set `enableDefaultSuffix` to `true`, you can't access URLs without language
parameter anymore:

    /               -> Redirect to /fr/
    /some/page      -> Redirect to /fr/some/page
    /fr/            -> Homepage with default language
    /fr/some/page   -> Some page with default language

### Persistence

The last language of a visitor will be stored in the user session and in a cookie.
If the user visits your site again without a language code, he will get redirected
to the stored language.

For example, the user first visits:

    /de/some/page

Then after some time comes back to one of the following URLs:

    /some/page      -> Redirect to /de/some/page
    /               -> Redirect to /de/
    /dk/some/page   -> Some page with language 'dk'

In the last case, `dk` will be stored as last language.

You'll notice, that there's one problem, if `enableDefaultSuffix` is `false` (which
is the default) and the user has e.g. stored `de` as last language. How can we now
access the site in the default language? Because if we try `/` we'd be redirected 
to `/de/`. The answer is simple: Explicitely include the language code for the default
language in the URL. For example if default language is `fr`:

    /fr/            -> Redirect to /

In this case, `fr` will first be stored as last language before the user is redirected.
If `enableDefaultSuffix` is `false` and the user 

`enablePersistence` is by default set to `true` in the `localeUrls` component.

You can modify the persistence settings with:

 * `languageCookieDuration`: How long to store the language information in a cookie.
   Set to `false` to disable the cookie.
 * `languageCookieName`: The name of the language cookie. Default is `_language`.
 * `languageSessionKey`: The name of the language session key. Default is `_language`.

### Language Detection

If a user visits your site for the first time and there's no language stored in session
or cookie (or persistence is turned off), then the language is detected from the visitor's
browser settings. If one of the preferred languages matches your language, it will be
used as application language (and also persisted if persistenc is enabled).

To disable this, you can set `enableLanguageDetection` to `false`. It's enabled by default.
