<?php namespace Ait\TYPO3DebugBar\DataCollectors;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;
use Doctrine\DBAL\Logging\DebugStack;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MySqliCollector
 *
 * @package Ait\TYPO3DebugBar\DataCollectors
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class MySqliCollector extends BaseCollector implements DataCollectorInterface, Renderable
{

    protected $connections = [];

    protected $renderSqlWithParams = false;

    protected $sqlQuotationChar = '<>';

    /** @var DebugStack $sqlLogger */
    protected $sqlLogger;

    /**
     * The constructor
     *
     * @param null $mysqli
     * @param TimeDataCollector|null $timeDataCollector
     */
    public function __construct($mysqli = null, TimeDataCollector $timeDataCollector = null)
    {
        parent::__construct();

        $this->sqlLogger = $this->getSqlLoggerFromDatabaseConfiguration();
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        $totalTime = 0;
        $queries = $this->sqlLogger->queries;
        $statements = [];

        foreach ($queries as $query) {
            $totalTime += $query['executionMS'];

            $statements[] = [
                'sql' => $query['sql'],
                'params' => $query['params'],
                'duration' => $query['executionMS'],
                'duration_str' => $this->formatDuration($query['executionMS']),
                'connection' => key($this->connections),
            ];
        }

        $data = [
            'nb_statements' => count($queries),
            'nb_failed_statements' => 0,
            'accumulated_duration' => $totalTime,
            'accumulated_duration_str' => $this->formatDuration($totalTime),
            'statements' => $statements,
        ];

        return $data;
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'mysqli';
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
            'database' => [
                'icon' => 'database',
                'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map' => 'mysqli',
                'default' => '[]',
            ],
            'database:badge' => [
                'map' => 'mysqli.nb_statements',
                'default' => 0,
            ],
        ];
    }

    private function getSqlLoggerFromDatabaseConfiguration()
    {
        $this->getConnections();

        return $this->connections['Default']->getConfiguration()->getSQLLogger();
    }

    private function getConnections()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        foreach ($connectionPool->getConnectionNames() as $connectionName) {
            $this->connections[$connectionName] = $connectionPool->getConnectionByName($connectionName);
        }
    }
}
