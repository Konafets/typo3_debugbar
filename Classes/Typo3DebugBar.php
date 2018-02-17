<?php namespace Konafets\TYPO3DebugBar;

use Konafets\TYPO3DebugBar\DataCollectors\AuthCollector;
use Konafets\TYPO3DebugBar\DataCollectors\InfoCollector;
use Konafets\TYPO3DebugBar\DataCollectors\MySqliCollector;
use Konafets\TYPO3DebugBar\DataCollectors\SessionCollector;
use Konafets\TYPO3DebugBar\DataCollectors\Typo3Collector;
use Konafets\TYPO3DebugBar\DataCollectors\TypoScriptCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use Exception;
use Konafets\TYPO3DebugBar\DataCollectors\VarDumpCollector;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Typo3DebugBar extends DebugBar implements SingletonInterface
{

    const EXTENSION_KEY = 'typo3_debugbar';
    const DEFAULT_CSS_STYLE_FILENAME = 'debugbar.css';
    const CUSTOM_CSS_STYLE_FILENAME = 'typo3_debugbar.css';
    const DEFAULT_JS_STYLE_FILENAME = 'debugbar.js';

    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $extensionPath = '';

    /** @var array */
    protected $extensionConfiguration;

    /** @var boolean */
    protected $booted = false;

    /** @var null|boolean */
    protected $enabled = null;

    /** @var JavascriptRenderer */
    protected $javascriptRenderer;

    /** @var string */
    protected $pathToCssResourceFolder = '';

    /** @var string */
    protected $pathToJsResourceFolder = '';

    /** @var array */
    protected $cssAssets = [];

    /** @var array */
    protected $jsAssets = [];

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
        $this->extensionPath = ExtensionManagementUtility::siteRelPath(self::EXTENSION_KEY);

        $this->pathToCssResourceFolder = $this->extensionPath . 'Resources/Public/Css/';
        $this->pathToJsResourceFolder = $this->extensionPath . 'Resources/Public/JavaScript/';
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

        $this->setDefaultAssets();

        $this->javascriptRenderer = $debugBar->getJavascriptRenderer();

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
                $this->addCollector(new MySqliCollector());
                $this->cssAssets[] = $this->pathToCssResourceFolder . 'sqlqueries/widget.css';
                $this->jsAssets[] = $this->pathToJsResourceFolder . 'sqlqueries/widget.js';
            } catch (DebugBarException $e) {
                $this->addThrowable(
                    new Exception('Can not add MySqliCollector to TYPO3 DebugBar:' . $e->getMessage(), $e->getCode(), $e)
                );
            }
        }

        if ($this->shouldCollect('vardump')) {
            try {
                $this->addCollector(new VarDumpCollector());
                $this->jsAssets[] = $this->pathToJsResourceFolder . 'generic/widget.js';
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

    public function shouldCollect($name)
    {
        return (bool) $this->extensionConfiguration[$name]['value'];
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

        $this->javascriptRenderer->dumpCssAssets($this->cssAssets[0]);
        $this->javascriptRenderer->dumpJsAssets($this->jsAssets[0]);

        foreach ($this->cssAssets as $cssAsset) {
            $pageRenderer->addHeaderData(sprintf('<link href="%s" rel="stylesheet" type="text/css">', $cssAsset));
        }

        foreach ($this->jsAssets as $jsAsset) {
            $pageRenderer->addHeaderData(sprintf('<script src="%s" type="text/javascript"></script>', $jsAsset));
        }
    }

    public function injectDebugBar(TypoScriptFrontendController $typoScriptFrontendController)
    {
        $typoScriptFrontendController->content = str_ireplace('</body>', $this->javascriptRenderer->render() . '</body>', $typoScriptFrontendController->content);
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

    private function setDefaultAssets()
    {
        $this->cssAssets = [
            0 => $this->pathToCssResourceFolder . self::DEFAULT_CSS_STYLE_FILENAME,
            1 => $this->pathToCssResourceFolder . self::CUSTOM_CSS_STYLE_FILENAME,
        ];

        $this->jsAssets = [
            0 => $this->pathToJsResourceFolder . self::DEFAULT_JS_STYLE_FILENAME,
        ];
    }

    private function isAdminLoggedIn()
    {
        if ($this->getBackendUser() !== null) {
            return $this->getBackendUser()->isAdmin();
        }

        return false;
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
            foreach($args as $arg) {
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
}
