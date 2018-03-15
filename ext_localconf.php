<?php

defined('TYPO3_MODE') or die('Access denied.');

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);

if (TYPO3_MODE === 'FE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['connectToDB'][] =
        $extensionPath . 'Classes/Typo3DebugBarServiceProvider.php:Konafets\\Typo3Debugbar\\Typo3DebugBarServiceProvider->injectDbalLoggerToDbConnection';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['postBeUser'][] =
        $extensionPath . 'Classes/Typo3DebugBarServiceProvider.php:Konafets\\Typo3Debugbar\\Typo3DebugBarServiceProvider->register';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['postBeUser'][] =
        $extensionPath . 'Classes/Typo3DebugBarServiceProvider.php:Konafets\\Typo3Debugbar\\Typo3DebugBarServiceProvider->boot';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
        $extensionPath . 'Classes/Typo3DebugBarServiceProvider.php:Konafets\\Typo3Debugbar\\Typo3DebugBarServiceProvider->addAssets';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe'][] =
        $extensionPath . 'Classes/Typo3DebugBarServiceProvider.php:Konafets\\Typo3Debugbar\\Typo3DebugBarServiceProvider->addDebugBar';
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\TimeTracker\TimeTracker::class] = ['className' => Konafets\Typo3Debugbar\Overrides\TimeTracker::class];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class] = ['className' => Konafets\Typo3Debugbar\Overrides\FrontendUserAuthentication::class];