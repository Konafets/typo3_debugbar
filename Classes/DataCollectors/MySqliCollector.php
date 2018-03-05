<?php namespace Konafets\TYPO3DebugBar\DataCollectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;
use Doctrine\DBAL\Logging\DebugStack;
use Konafets\TYPO3DebugBar\AssetsRenderer;
use Konafets\TYPO3DebugBar\Typo3DebugBar;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MySqliCollector
 *
 * @package Konafets\TYPO3DebugBar\DataCollectors
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class MySqliCollector extends BaseCollector implements DataCollectorInterface, Renderable, AssetProvider
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
        $path = ExtensionManagementUtility::extPath(Typo3DebugBar::EXTENSION_KEY);

        return [
            'css' => $path . AssetsRenderer::PATH_TO_STYLES . '/sqlqueries/widget.css',
            'js' => $path . AssetsRenderer::PATH_TO_JAVASCRIPT . '/sqlqueries/widget.js',
        ];
    }
}
