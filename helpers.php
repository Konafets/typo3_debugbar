<?php
declare(strict_types=1);
use Konafets\Typo3Debugbar\Typo3DebugBar;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (! function_exists('debugbar')) {
    /**
     * Get the Debugbar instance
     *
     * @return Typo3DebugBar
     */
    function debugbar()
    {
        return GeneralUtility::makeInstance(Typo3DebugBar::class);
    }
}

if (! function_exists('debugbar_debug')) {
    /**
     * Adds one or more messages to the MessagesCollector
     *
     * @param mixed ...$value
     */
    function debugbar_debug($value)
    {
        $debugbar = debugbar();
        foreach (func_get_args() as $value) {
            $debugbar->addMessage($value, 'debug');
        }
    }
}

if (! function_exists('start_measure')) {
    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to start the measure
     * @param string $label Public name
     */
    function start_measure($name, $label = null)
    {
        debugbar()->startMeasure($name, $label);
    }
}

if (! function_exists('stop_measure')) {
    /**
     * Stop a measure
     *
     * @param string $name Internal name, used to stop the measure
     */
    function stop_measure($name)
    {
        debugbar()->stopMeasure($name);
    }
}

if (! function_exists('add_measure')) {
    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     * @throws \DebugBar\DebugBarException
     */
    function add_measure($label, $start, $end)
    {
        debugbar()->addMeasure($label, $start, $end);
    }
}

if (! function_exists('measure')) {
    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param \Closure $closure
     * @throws \DebugBar\DebugBarException
     */
    function measure($label, \Closure $closure)
    {
        debugbar()->measure($label, $closure);
    }
}
