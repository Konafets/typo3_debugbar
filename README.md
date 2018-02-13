## TYPO3 Debug Bar

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with TYPO3.
It uses several hooks to include the assets and itself into frontend output.
It bootstraps some Collectors to work with TYPO3 and implements a couple custom DataCollectors, specific for TYPO3.

Read [the documentation](http://phpdebugbar.com/docs/) for more configuration options.

![Screenshot](Documentation/Images/SQLView.png)

Note: Use the DebugBar only in development. It can slow the application down (because it has to gather data). So when experiencing slowness, try disabling some of the collectors.

This package includes some custom collectors:
 - MySqliCollector: Show all queries, including timing
 - Typo3Collector: Show the TYPO3 version, Locale and Environment

And the default collectors:
 - PhpInfoCollector
 - MessagesCollector
 - TimeDataCollector (With Application timing)
 - MemoryCollector
 - ExceptionsCollector

### Credits

The extension is heavily inspired by the [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar). Thanks for your good work [Barry vd. Heuvel](https://github.com/barryvdh).
I also copied the idea of a ServiceProvider from Laravel.

## Installation

Require this package with composer. It is recommended to only require the package for development.

```shell
composer require konafets/typo3_debugbar --dev
```

The profiler is enabled by default. You can override that in the config (`debugbar.enabled`) or by setting `DEBUGBAR_ENABLED` in your `.env`. See more options in `config/debugbar.php`
You can also set in your config if you want to include/exclude the vendor files also (FontAwesome, Highlight.js and jQuery). If you already use them in your site, set it to false.
You can also only display the js or css vendors, by setting it to 'js' or 'css'. (Highlight.js requires both css + js, so set to `true` for syntax highlighting)

## Usage

TODO