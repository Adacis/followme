<?php
namespace OCA\Followme\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
    public const APP_ID = 'followme';
    public function __construct(array $urlParams = []) { parent::__construct(self::APP_ID, $urlParams); }
}
