<?php
declare(strict_types=1);

return (function () {
    $pharFile = __DIR__ . '/../../../Libraries/debugbar.phar';
    require 'phar://' . $pharFile . '/vendor/autoload.php';
})();
