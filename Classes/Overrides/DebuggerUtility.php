<?php namespace Konafets\Typo3Debugbar\Overrides;

use DebugBar\DebugBarException;
use Konafets\Typo3Debugbar\Typo3DebugBar;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility as BaseDebuggerUtility;

class DebuggerUtility extends BaseDebuggerUtility
{

    public static function var_dump(
        $variable,
        $title = null,
        $maxDepth = 8,
        $plainText = false,
        $ansiColors = true,
        $return = false,
        $blacklistedClassNames = null,
        $blacklistedPropertyNames = null
    ) {
        $debugString = parent::var_dump($variable, $title, $maxDepth, false, $ansiColors, true, $blacklistedClassNames, $blacklistedPropertyNames);

        /** @var Typo3DebugBar $debugBar */
        $debugBar = GeneralUtility::makeInstance(Typo3DebugBar::class);
        $debugBar->var_dump($debugString);
    }

}
