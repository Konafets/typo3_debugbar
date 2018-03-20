<?php namespace Konafets\Typo3Debugbar;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use Exception;
use Konafets\Typo3Debugbar\DataCollectors\AuthCollector;
use Konafets\Typo3Debugbar\DataCollectors\InfoCollector;
use Konafets\Typo3Debugbar\DataCollectors\MySqliCollector;
use Konafets\Typo3Debugbar\DataCollectors\SessionCollector;
use Konafets\Typo3Debugbar\DataCollectors\Typo3Collector;
use Konafets\Typo3Debugbar\DataCollectors\VarDumpCollector;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Debugbar subclass
 *
 * @method void emergency($message)
 * @method void alert($message)
 * @method void criteria($message)
 * @method void error($message)
 * @method void warning($message)
 * @method void notice($message)
 * @method void info($message)
 * @method void debug($message)
 * @method void log($message)
 */
class Typo3DebugBar extends DebugBar implements SingletonInterface
{

    const EXTENSION_KEY = 'typo3_debugbar';

    /** @var ObjectManager */
    protected $objectManager;

    /** @var array */
    protected $extensionConfiguration;

    /** @var bool */
    protected $booted = false;

    /** @var null|bool */
    protected $enabled = null;

    /** @var FrontendBackendUserAuthentication */
    protected $backendUser;

    /**
     * @param FrontendBackendUserAuthentication|null $backendUser
     */
    public function __construct(FrontendBackendUserAuthentication $backendUser = null)
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->extensionConfiguration = $this->objectManager
                                                ->get(ConfigurationUtility::class)
                                                ->getCurrentConfiguration(self::EXTENSION_KEY);
        $this->backendUser = $backendUser;
    }

    /**
     * @throws DebugBarException
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        /** @var DebugBar $debugBar */
        $debugBar = $this;

        if ($this->shouldCollect('info')) {
            try {
                $this->addCollector(new InfoCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add InfoCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('phpinfo')) {
            try {
                $this->addCollector(new PhpInfoCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add PhpInfoCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('messages')) {
            try {
                $this->addCollector(new MessagesCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add MessagesCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('time')) {
            try {
                $this->addCollector(new TimeDataCollector());

                $debugBar->startMeasure('application', 'Application');
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add TimeDataCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('memory')) {
            try {
                $this->addCollector(new MemoryCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add MemoryCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('exceptions')) {
            try {
                $exceptionCollector = new ExceptionsCollector();
                $exceptionCollector->setChainExceptions(true);
                $this->addCollector($exceptionCollector);
            } catch (\Exception $e) {
            }
        }

        if ($this->shouldCollect('auth')) {
            try {
                $this->addCollector(new AuthCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add AuthCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('session')) {
            try {
                $this->addCollector(new SessionCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add SessionCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('typo3')) {
            try {
                $this->addCollector(new Typo3Collector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add Typo3Collector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('db')) {
            try {
                $mySqliCollector = new MySqliCollector();
                $mySqliCollector->setRenderSqlWithParams($this->hasRenderWithParams());
                $this->addCollector($mySqliCollector);
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add MySqliCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('vardump')) {
            try {
                $this->addCollector(new VarDumpCollector());
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add VarDumpCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        $this->booted = true;
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     * @throws \DebugBar\DebugBarException
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stops a measure
     *
     * @param string $name
     * @throws \DebugBar\DebugBarException
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            try {
                $collector->stopMeasure($name);
            } catch (\Exception $e) {
                //  $this->addThrowable($e);
            }
        }
    }

    /**
     * Adds a measure
     *
     * @param $label
     * @param $start
     * @param $end
     * @throws DebugBarException
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param $label
     * @param \Closure $closure
     * @throws DebugBarException
     */
    public function measure($label, \Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        }
    }

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * @param Exception $e
     * @throws DebugBarException
     */
    public function addThrowable($e)
    {
        if ($this->hasCollector('exceptions')) {
            /** @var \DebugBar\DataCollector\ExceptionsCollector $collector */
            $collector = $this->getCollector('exceptions');
            $collector->addThrowable($e);
        }
    }

    public function shouldCollect($name)
    {
        return (bool) $this->extensionConfiguration[$name]['value'];
    }

    public function hasRenderWithParams()
    {
        return (bool) $this->extensionConfiguration['with_params']['value'];
    }

    public function isEnabled()
    {
        if ($this->enabled === null) {
            $isEnabled = (bool) $this->extensionConfiguration['enabled']['value'];

            $this->enabled = $this->isFrontendMode() && $this->isAdminLoggedIn() && $isEnabled;
        }

        return $this->enabled;
    }

    /**
     * @param PageRenderer $pageRenderer
     */
    public function injectAssets(PageRenderer $pageRenderer)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $head = $this->getAssetRenderer()->renderHead();
        $pageRenderer->addHeaderData($head);
    }

    public function injectDebugBar(TypoScriptFrontendController $typoScriptFrontendController)
    {
        $typoScriptFrontendController->content = str_ireplace('</body>', $this->getAssetRenderer()->render() . '</body>', $typoScriptFrontendController->content);
    }

    /**
     * @return bool
     */
    private function isFrontendMode()
    {
        return TYPO3_MODE === 'FE';
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Backend\FrontendBackendUserAuthentication
     */
    private function getBackendUser()
    {
        return $this->backendUser;
    }

    private function isAdminLoggedIn()
    {
        if ($this->getBackendUser() !== null) {
            return $this->getBackendUser()->isAdmin();
        }

        return false;
    }

    /**
     * Magic calls for adding messages
     *
     * @param string $method
     * @param array $args
     * @return mixed|void
     * @throws DebugBarException
     */
    public function __call($method, $args)
    {
        $messageLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'];
        if (in_array($method, $messageLevels)) {
            foreach ($args as $arg) {
                $this->addMessage($arg, $method);
            }
        }
    }

    /**
     * Adds a message to the MessagesCollector
     *
     * A message can be anything from an object to a string
     *
     * @param mixed $message
     * @param string $label
     * @throws DebugBarException
     */
    public function addMessage($message, $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var \DebugBar\DataCollector\MessagesCollector $collector */
            $collector = $this->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }

    /**
     * Adds an item to the VarDumpCollector
     *
     * @param mixed $item
     * @throws DebugBarException
     */
    public function var_dump($item)
    {
        if ($this->hasCollector('vardump')) {
            /** @var VarDumpCollector $collector */
            $collector = $this->getCollector('vardump');
            $collector->addVarDump($item);
        }
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string $baseUrl
     * @param string $basePath
     * @return AssetsRenderer
     */
    public function getAssetRenderer($baseUrl = null, $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new AssetsRenderer($this, $baseUrl, $basePath);
        }

        return $this->jsRenderer;
    }
}
