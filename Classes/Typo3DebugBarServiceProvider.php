<?php namespace Konafets\TYPO3DebugBar;

use Doctrine\DBAL\Logging\DebugStack;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Typo3DebugBarServiceProvider
{

    public function register()
    {
        GeneralUtility::setSingletonInstance(
            Typo3DebugBar::class,
            GeneralUtility::makeInstance(Typo3DebugBar::class)
        );
    }

    public function boot()
    {
        /** @var Typo3DebugBar $debugBar */
        $debugBar = GeneralUtility::makeInstance(Typo3DebugBar::class);
        if ($debugBar->isEnabled()) {
            $debugBar->boot();
        }
    }

    /**
     * @param array $params
     * @param PageRenderer $pageRenderer
     */
    public function addAssets(array $params, PageRenderer $pageRenderer)
    {
        /** @var Typo3DebugBar $debugBar */
        $debugBar = GeneralUtility::makeInstance(Typo3DebugBar::class);
        if ($debugBar->isEnabled()) {
            $debugBar->injectAssets($pageRenderer);
        }
    }

    /**
     * @param array $params
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function addDebugBar(array $params, TypoScriptFrontendController $typoScriptFrontendController)
    {
        /** @var Typo3DebugBar $debugBar */
        $debugBar = GeneralUtility::makeInstance(Typo3DebugBar::class);
        if ($debugBar->isEnabled()) {
            $debugBar->injectDebugBar($typoScriptFrontendController);
        }
    }
    
    public function injectDbalLoggerToDbConnection()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connectionNames = $connectionPool->getConnectionNames();
        $connection = $connectionPool->getConnectionByName($connectionNames[0]);
        $connection->getConfiguration()->setSQLLogger(new DebugStack());
    }
}
