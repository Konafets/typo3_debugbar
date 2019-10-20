<?php namespace Konafets\Typo3Debugbar\ViewHelpers;

use Konafets\Typo3Debugbar\Overrides\DebuggerUtility;
use Konafets\Typo3Debugbar\Typo3DebugBar;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class DebugbarViewHelper extends AbstractViewHelper
{
    /**
     * Displays the debug information in the PHP Debug Bar
     *
     * @param null|string $content
     */
    public function render($content = null)
    {
        if (is_null($content)) {
            $content = $this->renderChildren();
        }

        /** @var Typo3DebugBar $debugBar */
        $debugBar = GeneralUtility::makeInstance(Typo3DebugBar::class);
        if ($debugBar->isEnabled()) {
            DebuggerUtility::var_dump($content);
        }
    }
}
