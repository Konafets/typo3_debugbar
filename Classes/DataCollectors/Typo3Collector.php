<?php namespace Konafets\Typo3Debugbar\DataCollectors;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class EnvironmentCollector
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class Typo3Collector extends BaseCollector implements DataCollectorInterface, Renderable
{

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect()
    {
        return [
            'version' => VersionNumberUtility::getCurrentTypo3Version(),
            'environment' => $this->getEnvironmentInformation(),
            'locale' => $this->getLocale(),
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    public function getName()
    {
        return 'typo3';
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    public function getWidgets()
    {
        return [
            'version' => [
                'icon' => 'tag',
                'tooltip' => 'Version',
                'map' => 'typo3.version',
                'default' => '',
            ],
            'environment' => [
                'icon' => 'desktop',
                'tooltip' => 'Environment',
                'map' => 'typo3.environment',
                'default' => '',
            ],
            'locale' => [
                'icon' => 'flag',
                'tooltip' => 'Current locale',
                'map' => 'typo3.locale',
                'default' => '',
            ],
        ];
    }

    private function getEnvironmentInformation()
    {
        return $GLOBALS['_ENV']['TYPO3_CONTEXT'];
    }

    private function getLocale()
    {
        return $GLOBALS['TSFE']->config['config']['locale_all'];
    }
}
