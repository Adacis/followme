<?php
namespace OCA\Followme\Tests\Unit\Db;

use OCA\Followme\Db\FollowmeMapper;
use OCA\Followme\Db\FollowmeparamMapper;
use OCP\Security\ICrypto;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {
    private $db;
    protected function setUp(): void {
        $this->db = \OC::$server->getDatabaseConnection();
        $this->db->beginTransaction();
    }
    protected function tearDown(): void { $this->db->rollBack(); }
    public function testCrudDateBoundariesAndContributorStatistics(): void {
        $mapper = new FollowmeMapper($this->db);
        $id = $mapper->insertActu(strtotime('2026-12-31T23:59:59Z'), 'followme-test-author', 'https://example.org', 'A', 'News', 'Title');
        $nextId = $mapper->insertActu(strtotime('2027-01-01T00:00:00Z'), 'followme-test-author', 'https://example.org/next', 'B', 'Guide', 'Next');
        $stats = array_values(array_filter($mapper->getNbArticleByUser(2026), fn($row) => $row['utilisateur'] === 'followme-test-author'));
        $this->assertSame(1, $stats[0]['count']);
        $rows = $mapper->findAll([strtotime('2026-12-31T00:00:00Z'), strtotime('2026-12-31T23:59:59Z')]);
        $this->assertContains($id, array_map(fn($row) => (int)$row['id'], $rows));
        $this->assertNotContains($nextId, array_map(fn($row) => (int)$row['id'], $rows));
        $mapper->updateActu('1793000000', 'another-user', 'https://example.org/changed', 'Updated', 'Guide', 'Changed', $id);
        $article = $mapper->findArticleById($id);
        $this->assertSame('Changed', $article->getTitle()); $this->assertSame('followme-test-author', $article->getUtilisateur());
        $mapper->delActu($id);
        $this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class); $mapper->findArticleById($id);
    }
    public function testSettingsAreUniqueEncryptedAndNotExposed(): void {
        $mapper = new FollowmeparamMapper($this->db, \OC::$server->get(ICrypto::class));
        $mapper->insertParam('user', 'test-secret-not-a-credential', 'https://example.org', '', '', '', '');
        $mapper->insertParam('user2', '', 'https://example.org', '', '', '', '');
        $this->assertSame('test-secret-not-a-credential', $mapper->getParamByName('passwordWP'));
        $this->assertArrayNotHasKey('passwordWP', $mapper->publicSettings());
        $rows = array_values(array_filter($mapper->getParam(), fn($row) => $row['name'] === 'passwordWP'));
        $this->assertCount(1, $rows); $this->assertStringStartsWith('encrypted:v1:', $rows[0]['value']);
        $this->assertStringNotContainsString('test-secret-not-a-credential', $rows[0]['value']);
        $this->assertSame('user2', $mapper->getParamByName('userIdWP'));
    }
    public function testMigrationCanRetryWithoutReencryptingSecrets(): void {
        $crypto = \OC::$server->get(ICrypto::class);
        $mapper = new FollowmeparamMapper($this->db, $crypto);
        $mapper->insertParam('user', 'retry-test-secret', '', '', '', '', '');
        $before = $mapper->getParam();
        $migration = new \OCA\Followme\Migration\Version10300Date20261026120000($this->db, $crypto);
        $output = $this->createMock(\OCP\Migration\IOutput::class);
        $migration->preSchemaChange($output, static function () {}, []);
        $migration->preSchemaChange($output, static function () {}, []);
        $this->assertSame($before, $mapper->getParam());
        $this->assertSame('retry-test-secret', $mapper->getParamByName('passwordWP'));
    }

}
