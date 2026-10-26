<?php
namespace OCA\Followme\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class FollowmeMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'followme', Followme::class);
    }

    public function findArticleById($id) {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter((int)$id)));
        return $this->findEntity($qb);
    }

    public function findAll($interval) {
        $qb = $this->intervalQuery((int)$interval[0], (int)$interval[1]);
        $qb->orderBy('date', 'DESC')->addOrderBy('id', 'DESC');
        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function findAllInterval($start, $end) {
        $qb = $this->intervalQuery((int)$start, (int)$end);
        $qb->orderBy('categorie', 'ASC')->addOrderBy('date', 'DESC');
        return $qb->executeQuery()->fetchAllAssociative();
    }

    private function intervalQuery(int $start, int $end) {
        $qb = $this->db->getQueryBuilder();
        return $qb->select('*')->from($this->getTableName())
            ->where($qb->expr()->gte('date', $qb->createNamedParameter($start)))
            ->andWhere($qb->expr()->lte('date', $qb->createNamedParameter($end)));
    }

    public function findCategorie() {
        $qb = $this->db->getQueryBuilder();
        return $qb->select('categorie')->from($this->getTableName())
            ->groupBy('categorie')->orderBy('categorie', 'ASC')->executeQuery()->fetchAllAssociative();
    }

    public function insertActu($date, $author, $link, $description, $category, $title) {
        $qb = $this->db->getQueryBuilder();
        $qb->insert($this->getTableName());
        foreach (['date' => (int)$date, 'utilisateur' => $author, 'lien' => $link,
                  'description' => $description, 'categorie' => $category, 'title' => $title] as $name => $value) {
            $qb->setValue($name, $qb->createNamedParameter($value));
        }
        $qb->executeStatement();
        return (int)$qb->getLastInsertId();
    }

    public function updateActu($date, $author, $link, $description, $category, $title, $id) {
        $this->findArticleById($id);
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName());
        // Preserve the original contributor when another user edits an article.
        foreach (['date' => (int)$date, 'lien' => $link, 'description' => $description,
                  'categorie' => $category, 'title' => $title] as $name => $value) {
            $qb->set($name, $qb->createNamedParameter($value));
        }
        $qb->where($qb->expr()->eq('id', $qb->createNamedParameter((int)$id)))->executeStatement();
        return 'ok';
    }

    public function delActu($id) {
        $this->findArticleById($id);
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())->where($qb->expr()->eq('id', $qb->createNamedParameter((int)$id)))->executeStatement();
        return 'ok';
    }

    public function getNbArticleByUser($year) {
        $year = (int)(is_array($year) ? $year[0] : $year);
        $start = new \DateTimeImmutable($year . '-01-01T00:00:00Z');
        $qb = $this->intervalQuery($start->getTimestamp(), $start->modify('+1 year')->getTimestamp() - 1);
        $qb->select('utilisateur')->selectAlias($qb->func()->count('*'), 'count')
            ->groupBy('utilisateur')->orderBy('count', 'DESC')->addOrderBy('utilisateur', 'ASC');
        return array_map(static function (array $row) use ($year): array {
            return ['utilisateur' => $row['utilisateur'], 'annee' => $year, 'count' => (int)$row['count']];
        }, $qb->executeQuery()->fetchAllAssociative());
    }
}
