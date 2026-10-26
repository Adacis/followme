<?php
namespace OCA\Followme\Settings;

use OCA\Followme\Db\FollowmeparamMapper;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class FollowmeAdmin implements ISettings {
    public function __construct(private FollowmeparamMapper $settings) {}
    public function getForm() { return new TemplateResponse('followme', 'settings/admin', $this->settings->publicSettings()); }
    public function getSection() { return 'followme'; }
    public function getPriority() { return 55; }
}
