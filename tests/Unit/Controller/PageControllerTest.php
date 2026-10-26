<?php
namespace OCA\Followme\Tests\Unit\Controller;

use OCA\Followme\Controller\AdminController;
use OCA\Followme\Controller\PageController;
use OCA\Followme\Db\FollowmeMapper;
use OCA\Followme\Db\FollowmeparamMapper;
use OCA\Followme\Service\IntegrationService;
use OCP\App\IAppManager;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class PageControllerTest extends TestCase {
    private function controller($mapper, $integrations, bool $admin = false): PageController {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('reader'); $user->method('getDisplayName')->willReturn('Reader');
        $users = $this->createMock(IUserSession::class); $users->method('getUser')->willReturn($user);
        $groups = $this->createMock(IGroupManager::class); $groups->method('isAdmin')->willReturn($admin);
        $apps = $this->createMock(IAppManager::class); $apps->method('getAppVersion')->with('followme')->willReturn('1.3.0');
        $urls = $this->createMock(IURLGenerator::class); $urls->method('linkToRoute')->willReturn('/settings/admin/followme');
        return new PageController('followme', $this->createMock(IRequest::class), $mapper, $users, $groups, $apps, $urls, $integrations);
    }
    public function testVersionAndAdminVisibilityComeFromNextcloud(): void {
        $controller = $this->controller($this->createMock(FollowmeMapper::class), $this->createMock(IntegrationService::class));
        $data = $controller->index()->getParams();
        $this->assertSame('1.3.0', $data['version']); $this->assertFalse($data['isAdmin']); $this->assertSame('Reader', $data['key']);
    }
    public function testNotificationFailureDoesNotLoseAnArticle(): void {
        $mapper = $this->createMock(FollowmeMapper::class); $mapper->expects($this->once())->method('insertActu')->willReturn(123);
        $integration = $this->createMock(IntegrationService::class); $integration->method('notify')->willThrowException(new \RuntimeException('offline'));
        $response = $this->controller($mapper, $integration)->insertActu('1793000000', 'https://example.org', 'Description', 'News', 'Title');
        $this->assertSame(201, $response->getStatus()); $this->assertSame(123, $response->getData()['id']); $this->assertNotEmpty($response->getData()['warning']);
    }
    public function testRejectsExecutableLinksAndInvalidPeriods(): void {
        $mapper = $this->createMock(FollowmeMapper::class); $mapper->expects($this->never())->method('insertActu');
        $controller = $this->controller($mapper, $this->createMock(IntegrationService::class));
        $this->assertSame(400, $controller->insertActu('1793000000', 'javascript:alert(1)', 'Text', 'News', 'Title')->getStatus());
        $this->assertSame(400, $controller->showActu('200', '100')->getStatus());
        $this->assertSame(400, $controller->getNbArticleByUser('2026 OR 1=1')->getStatus());
    }
    public function testAdminSettingsRejectNonHttpsAndDoNotWrite(): void {
        $settings = $this->createMock(FollowmeparamMapper::class); $settings->expects($this->never())->method('insertParam');
        $controller = new AdminController('followme', $this->createMock(IRequest::class), $settings, $this->createMock(IURLGenerator::class));
        $this->assertSame(400, $controller->save('', '', 'http://example.org')->getStatus());
    }
}
