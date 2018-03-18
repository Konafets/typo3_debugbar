<?php
declare(strict_types=1);
namespace Konafets\Typo3Debugbar;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class AssetsRenderer extends JavascriptRenderer
{
    const PATH_TO_STYLES = 'Resources/Public/Css';
    const PATH_TO_JAVASCRIPT = 'Resources/Public/JavaScript';
    const CUSTOM_CSS_STYLE_FILENAME = '/typo3_debugbar.css';

    /** @var string */
    protected $pathToCssAssetFile = '';

    /** @var string */
    protected $pathToJsAssetFile = '';

    /**
     * @param DebugBar $debugBar
     * @param null $baseUrl
     * @param null $basePath
     */
    public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);
        $extensionPath = ExtensionManagementUtility::extPath(Typo3DebugBar::EXTENSION_KEY);

        $this->pathToCssAssetFile = 'typo3temp/tx_typo3_debugbar_styles.css';
        $this->pathToJsAssetFile = 'typo3temp/tx_typo3_debugbar_javascript.js';

        $this->cssVendors['fontawesome'] = $extensionPath . 'Resources/Public/vendor/font-awesome/style.css';
        $this->cssFiles['typo3'] = $extensionPath . self::PATH_TO_STYLES . self::CUSTOM_CSS_STYLE_FILENAME;
    }

    /**
     * Renders the html to include needed assets
     *
     * Only useful if Assetic is not used
     *
     * @return string
     */
    public function renderHead()
    {
        $this->dumpCssAssets($this->pathToCssAssetFile);
        $this->dumpJsAssets($this->pathToJsAssetFile);

        $html = '';
        $html .= "<link href='/{$this->pathToCssAssetFile}' rel='stylesheet' type='text/css'>\n";
        $html .= "<script src='/{$this->pathToJsAssetFile}' type='text/javascript'></script>\n";

        if ($this->isJqueryNoConflictEnabled()) {
            $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }
}
