<?php namespace Konafets\Typo3Debugbar\DataCollectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;

/**
 * Class SessionCollector
 */
class SessionCollector extends DataCollector implements DataCollectorInterface, Renderable
{

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        return $this->getSession();
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'session';
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
                'icon' => 'archive',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'session',
                "default" => '[]',
            ],
        ];
    }

    /**
     * @return mixed
     */
    private function getSession()
    {
        return $GLOBALS['TSFE']->fe_user->getSession();
    }
}
