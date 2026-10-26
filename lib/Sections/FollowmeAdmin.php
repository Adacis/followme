<?php
namespace OCA\Followme\Sections;

use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class FollowmeAdmin implements IIconSection {
    public function __construct(private IURLGenerator $urls) {}
    public function getID() { return 'followme'; }
    public function getName() { return 'Followme'; }
    public function getPriority() { return 55; }
    public function getIcon() { return $this->urls->imagePath('followme', 'app.svg'); }
}
