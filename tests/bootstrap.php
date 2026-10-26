<?php
$root = getenv('NEXTCLOUD_ROOT') ?: dirname(__DIR__, 3);
require_once $root . '/lib/base.php';
require_once __DIR__ . '/../vendor/autoload.php';
\OC_App::loadApp('followme');
