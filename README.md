## TYPO3 Debug Bar

[![Latest Stable Version](https://poser.pugx.org/konafets/typo3_debugbar/v/stable)](https://packagist.org/packages/konafets/typo3_debugbar) [![Total Downloads](https://poser.pugx.org/konafets/typo3_debugbar/downloads)](https://packagist.org/packages/konafets/typo3_debugbar) [![Latest Unstable Version](https://poser.pugx.org/konafets/typo3_debugbar/v/unstable)](https://packagist.org/packages/konafets/typo3_debugbar) [![License](https://poser.pugx.org/konafets/typo3_debugbar/license)](https://packagist.org/packages/konafets/typo3_debugbar)

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with TYPO3.
It uses several hooks to include the assets and itself into frontend output.
It bootstraps some Collectors to work with TYPO3 and implements a couple custom DataCollectors, specific for TYPO3.

Read [the documentation](http://phpdebugbar.com/docs/) for more configuration options.

![Screenshot](https://raw.githubusercontent.com/konafets/typo3_debugbar/develop/Documentation/Images/SQLView.png)

Note: Use the DebugBar only in development. It can slow the application down (because it has to gather data). So when experiencing slowness, try disabling some of the collectors.

The extension comes with the default collectors:

 - PhpInfoCollector: Show the PHP version 
 - MessagesCollector: Collects messages from within the Application and pushing them to the DebugBar
 - TimeDataCollector: Here you can start and stop a timer. Default it times the Application. More in the Usage section
 - MemoryCollector: Show the Memory usage
 - ExceptionsCollector: Collects exceptions from withing the Application and pushing them to the DebugBar

And includes some custom collectors:

 - InfoCollector: Show the same information like the Info pane of the Admin Panel
 - MySqliCollector: Show all queries, including timing and the values of prepared statements
 - Typo3Collector: Show the TYPO3 version, Locale and Environment
 - AuthCollector: Show the username of the logged-in backend user
 - SessionCollector: Show session data

### Credits

The extension is heavily inspired by the [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar). Thanks for your good work [Barry vd. Heuvel](https://github.com/barryvdh).
I also copied the idea of a ServiceProvider from Laravel.

## Installation

Require this package with composer. It is recommended to only require the package for development.

```shell
composer require konafets/typo3_debugbar --dev
```

Afterwards you need to activate the extension in the Extension Manager. The DebugBar is enabled by default and will be displayed to you if you are logged into the backend as an administrator.  

## Usage

The extension is configurable through the Extension Manager. There you can enable/disable the DebugBar as well as the DataCollectors.

![Configuration](https://raw.githubusercontent.com/konafets/typo3_debugbar/develop/Documentation/Images/Configure.png)

### Database

This pane shows all issued queries of the *default* connection against the database. To see the values of a prepared statements, click on the statement. 

![DatabasePane](https://raw.githubusercontent.com/konafets/typo3_debugbar/develop/Documentation/Images/DatabasePane.gif)

The extension uses the *connectToDB* hook to inject `Doctrine\DBAL\Logging\DebugStack` as a logger to the connection. At the end of the rendering process it retrieves the Logger and shows the logged queries.
Its important to understand, that the extension adds `Doctrine\DBAL\Logging\DebugStack` in any case, even if its not shown in the frontend. 
This is due to log *all* queries from the very beginning ... but at that point the BE User is not initialized yet and its unclear if the DebugBar is enabled or not. Classical *Chicken-and-egg* problem.

## Lifecycle

As mentioned above the extension uses hooks. The following figure shows the usage during a request life cycle. 

![DatabasePane](Documentation/Images/LifeCycle.svg)   


