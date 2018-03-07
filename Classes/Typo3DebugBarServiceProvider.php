<?php namespace Konafets\TYPO3DebugBar;

use Doctrine\DBAL\Logging\DebugStack;
use Konafets\TYPO3DebugBar\DataCollectors\MySqliCollector;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Typo3DebugBarServiceProvider
{

    public function register(array $params)
    {
        GeneralUtility::setSingletonInstance(
            Typo3DebugBar::class,
            GeneralUtility::makeInstance(Typo3DebugBar::class, $params['BE_USER'])
        );
    }

    /**
     * @throws \DebugBar\DebugBarException
     */
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

    /**
     * This method will be called via "connectToDB" hook when TYPO3 connects to the database.
     */
    public function injectDbalLoggerToDbConnection()
    {
        $connection = MySqliCollector::getDefaultConnection();
        $connection->getConfiguration()->setSQLLogger(new DebugStack());
    }
}
