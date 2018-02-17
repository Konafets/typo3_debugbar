<?php namespace Konafets\TYPO3DebugBar\DataCollectors;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Class VarDumpCollector
 *
 * @package Konafets\TYPO3DebugBar\DataCollectors
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class VarDumpCollector extends MessagesCollector implements DataCollectorInterface, Renderable
{

    protected $varDumpItem = [];

    /**
     * The constructor
     *
     * @param string $name
     */
    public function __construct($name = 'vardump')
    {
        parent::__construct($name);
    }


    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        return $this->getVarDumpItems();
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'vardump';
    }

    function addVarDump($item)
    {
        $this->varDumpItem[] = $item;
    }

    public function getVarDumpItems()
    {
        return $this->varDumpItem;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    function getWidgets()
    {
        return [
            'vardump' => [
                'widget' => 'PhpDebugBar.Widgets.TYPO3GenericWidget',
                'map' => 'vardump',
                'default' => '',
            ],
        ];
    }
}
