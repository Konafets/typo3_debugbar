<?php namespace Konafets\TYPO3DebugBar\DataCollectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;
use Doctrine\DBAL\Logging\DebugStack;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MySqliCollector
 *
 * @package Konafets\TYPO3DebugBar\DataCollectors
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class MySqliCollector extends BaseCollector implements DataCollectorInterface, Renderable, AssetProvider
{

    const DEFAULT_CONNECTION = 'Default';

    /** @var bool */
    protected $renderSqlWithParams = false;

    /** @var DebugStack $sqlLogger */
    protected $sqlLogger;

    /** @var \TYPO3\CMS\Core\Database\Connection */
    protected $connection;

    /**
     * The constructor
     *
     * @param null $mysqli
     * @param TimeDataCollector|null $timeDataCollector
     */
    public function __construct($mysqli = null, TimeDataCollector $timeDataCollector = null)
    {
        parent::__construct();

        $this->connection = $this->getDefaultConnection();
        $this->sqlLogger = $this->getSqlLoggerFromDatabaseConfiguration();
    }

    /**
     * @return mixed
     */
    public static function getDefaultConnection()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connections = $connectionPool->getConnectionNames();
        $connectionName = self::DEFAULT_CONNECTION;

        if (! in_array($connectionName, $connections)) {
            list($connectionName) = $connections;
        }

        $connection = $connectionPool->getConnectionByName($connectionName);

        return $connection;
    }

    /**
     * Renders the SQL of traced statements with params embeded
     *
     * @param bool $enabled
     * @param string $quotationChar
     */
    public function setRenderSqlWithParams($enabled = true)
    {
        $this->renderSqlWithParams = $enabled;
    }

    /**
     * @return bool
     */
    public function isSqlRenderedWithParams()
    {
        return $this->renderSqlWithParams;
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

            if ($this->isSqlRenderedWithParams()) {
                $this->addParamsToSql($query['sql'], $query['params']);
                unset($query['params']);
            }

            $statements[] = [
                'sql' => $query['sql'],
                'params' => $query['params'],
                'duration' => $query['executionMS'],
                'duration_str' => $this->formatDuration($query['executionMS']),
                'connection' => $this->connection->getDatabase(),
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
        if ($this->connection === null) {
            $this->connection = $this->getDefaultConnection();
        }

        return $this->connection->getConfiguration()->getSQLLogger();
    }

    /**
     * @param string $sql
     * @param array $params
     */
    private function addParamsToSql(&$sql, array $params)
    {
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $param = $param[0];
            }

            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
            $sql = preg_replace($regex, $this->connection->quote($param), $sql, 1);
        }
    }

    /**
     * Returns an array with the following keys:
     *  - base_path
     *  - base_url
     *  - css: an array of filenames
     *  - js: an array of filenames
     *  - inline_css: an array map of content ID to inline CSS content (not including <style> tag)
     *  - inline_js: an array map of content ID to inline JS content (not including <script> tag)
     *  - inline_head: an array map of content ID to arbitrary inline HTML content (typically
     *        <style>/<script> tags); it must be embedded within the <head> element
     *
     * All keys are optional.
     *
     * Ideally, you should store static assets in filenames that are returned via the normal css/js
     * keys.  However, the inline asset elements are useful when integrating with 3rd-party
     * libraries that require static assets that are only available in an inline format.
     *
     * The inline content arrays require special string array keys:  the caller of this function
     * will use them to deduplicate content.  This is particularly useful if multiple instances of
     * the same asset provider are used.  Inline assets from all collectors are merged together into
     * the same array, so these content IDs effectively deduplicate the inline assets.
     *
     * @return array
     */
    function getAssets()
    {
        return [
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js',
        ];
    }
}
