<?php namespace Konafets\Typo3Debugbar\DataCollectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BaseCollector
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 */
abstract class BaseCollector extends DataCollector implements Renderable
{

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_tsfe.xlf');
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Backend\FrontendBackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
    }

    /**
     * Translate given key
     *
     * @param string $key Key for a label in the $LOCAL_LANG array of "sysext/lang/Resources/Private/Language/locallang_tsfe.xlf
     * @param bool $convertWithHtmlspecialchars If TRUE the language-label will be sent through htmlspecialchars
     * @return string The value for the $key
     */
    protected function extGetLL($key, $convertWithHtmlspecialchars = true)
    {
        $labelStr = $this->getLanguageService()->getLL($key);
        if ($convertWithHtmlspecialchars) {
            $labelStr = htmlspecialchars($labelStr);
        }
        return $labelStr;
    }

    /**
     * @return TimeTracker
     */
    protected function getTimeTracker()
    {
        return GeneralUtility::makeInstance(TimeTracker::class);
    }

    /**
     * Returns the value for an Admin Panel setting.
     *
     * @param string $sectionName Module key
     * @param string $val Setting key
     * @return mixed The setting value
     */
    public function extGetFeAdminValue($sectionName, $val = '')
    {
        $beUser = $this->getBackendUser();

        // Override all settings with user TSconfig
        if ($val && isset($beUser->extAdminConfig['override.'][$sectionName . '.'][$val])) {
            return $beUser->extAdminConfig['override.'][$sectionName . '.'][$val];
        }
        if (!$val && isset($beUser->extAdminConfig['override.'][$sectionName])) {
            return $beUser->extAdminConfig['override.'][$sectionName];
        }

        $returnValue = $val ? $beUser->uc['TSFE_adminConfig'][$sectionName . '_' . $val] : 1;

        // Exception for preview
        return !$val ? true : $returnValue;
    }
}
