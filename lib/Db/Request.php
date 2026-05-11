<?php
namespace OCA\Followme\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class Request {

    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function find($userId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from('accounts')
           ->where(
               $qb->expr()->eq('uid', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
           );

        $cursor = $qb->executeQuery();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        return $row;
    }

}