<?php
namespace Konafets\Typo3Debugbar\Composer;

use Composer\Script\Event;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Scripts executed on composer build time
 *
 * This file is taken from helhum/typo3_console. Thanks Helmut!
 */
class InstallerScripts
{
    /**
     * @param Event $event
     * @internal
     * @throws RuntimeException
     */
    public static function setVersion(Event $event)
    {
        $version = $event->getArguments()[0];
        if (!preg_match('/\d+\.\d+\.\d+/', $version)) {
            throw new RuntimeException('No valid version number provided!', 1468672604);
        }

        $extEmConfFile = __DIR__ . '/../../ext_emconf.php';
        $content = file_get_contents($extEmConfFile);
        $content = preg_replace('/(\'version\' => )\'\d+\.\d+\.\d+/', '$1\'' . $version, $content);
        file_put_contents($extEmConfFile, $content);
    }
}
