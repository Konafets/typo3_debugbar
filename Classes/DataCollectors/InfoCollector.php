<?php namespace Konafets\TYPO3DebugBar\DataCollectors;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InfoCollector
 *
 * @package Konafets\TYPO3DebugBar\DataCollectors
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class InfoCollector extends BaseCollector
{

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        $output = [];
        $frontendController = $this->getTypoScriptFrontendController();
        if ($this->extGetFeAdminValue('cache', 'noCache')) {
            $theBytes = 0;
            $count = 0;
            if (!empty($frontendController->imagesOnPage)) {
                $tableArr[] = [$this->extGetLL('info_imagesOnPage'), count($frontendController->imagesOnPage), true];
                foreach ($GLOBALS['TSFE']->imagesOnPage as $file) {
                    $fs = @filesize($file);
                    $tableArr[] = [TAB . $file, GeneralUtility::formatSize($fs)];
                    $theBytes += $fs;
                    $count++;
                }
            }
            // Add an empty line
            $output[$this->extGetLL('info_imagesSize')] = GeneralUtility::formatSize($theBytes);
            $output[$this->extGetLL('info_DocumentSize')] = GeneralUtility::formatSize(strlen($frontendController->content));
            $output[''] = '';
        }

        $output[$this->extGetLL('info_id')] = $frontendController->id;
        $output[$this->extGetLL('info_type')] = $frontendController->type;
        $output[$this->extGetLL('info_groupList')] = $frontendController->gr_list;
        $output[$this->extGetLL('info_noCache')] = $this->extGetLL('info_noCache_' . ($frontendController->no_cache ? 'no' : 'yes'));
        $output[$this->extGetLL('info_countUserInt')] = \is_array($frontendController->config['INTincScript'])  ? \count($frontendController->config['INTincScript']) : 0;

        if (!empty($frontendController->fe_user->user['uid'])) {
            $output[$this->extGetLL('info_feuserName')] = htmlspecialchars($frontendController->fe_user->user['username']);
            $output[$this->extGetLL('info_feuserId')] = htmlspecialchars($frontendController->fe_user->user['uid']);
        }

        $output[$this->extGetLL('info_totalParsetime')] = $this->getTimeTracker()->getParseTime() . ' ms';

        return $output;
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'info';
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    function getWidgets()
    {
        $name = $this->getName();

        return [
            "$name" => [
                'icon' => 'info',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'info',
                "default" => '[]',
            ],
        ];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
