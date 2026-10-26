<?php
namespace OCA\Followme\Db;

use OCP\IDBConnection;
use OCP\Security\ICrypto;

class FollowmeparamMapper {
    public const KEYS = ['urlWP', 'userIdWP', 'passwordWP', 'urlRC', 'userIdRC', 'passwordRC', 'roomRC'];
    private const ENCRYPTED = 'encrypted:v1:';

    public function __construct(private IDBConnection $db, private ICrypto $crypto) {}

    public function getParam(): array {
        $qb = $this->db->getQueryBuilder();
        return $qb->select('name', 'value')->from('followme_parameter')->orderBy('id', 'ASC')->executeQuery()->fetchAllAssociative();
    }

    public function getParamByName($name): string {
        $value = '';
        foreach ($this->getParam() as $row) {
            if ($row['name'] === $name) { $value = $row['value']; }
        }
        if (str_starts_with($value, self::ENCRYPTED)) {
            return $this->crypto->decrypt(substr($value, strlen(self::ENCRYPTED)));
        }
        return $value;
    }

    public function publicSettings(): array {
        $values = [];
        foreach (self::KEYS as $key) {
            if (!str_starts_with($key, 'password')) { $values[$key] = $this->getParamByName($key); }
        }
        return $values;
    }

    public function insertParam($userIdWP, $passwordWP, $urlWP, $userIdRC, $passwordRC, $urlRC, $roomRC): void {
        $values = compact('userIdWP', 'passwordWP', 'urlWP', 'userIdRC', 'passwordRC', 'urlRC', 'roomRC');
        $this->db->beginTransaction();
        try {
            foreach ($values as $key => $value) {
                if (str_starts_with($key, 'password')) {
                    if ($value === '') { continue; } // Empty password fields retain the existing secret.
                    $value = self::ENCRYPTED . $this->crypto->encrypt($value);
                }
                $qb = $this->db->getQueryBuilder();
                $qb->delete('followme_parameter')->where($qb->expr()->eq('name', $qb->createNamedParameter($key)))->executeStatement();
                $qb = $this->db->getQueryBuilder();
                $qb->insert('followme_parameter')->values(['name' => $qb->createNamedParameter($key), 'value' => $qb->createNamedParameter($value)])->executeStatement();
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
