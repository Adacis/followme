<?php
namespace OCA\Followme\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

Class FollowmeMapper extends QBMapper {

	public function __construct(IDbConnection $db) {
		parent::__construct($db, 'followme', Followme::class);
	}

	public function findArticleById($id){
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where('id = ?')
			->setParameter(0, $id);
		return $this->findEntity($qb);
	}

	/**
	 * Recherche de tous les articles dans la table en trie descendant.
	 * Avec un interval de temps
	 */
	public function findAllInterval($intervaldebut, $intervalfin) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where('date <= :intervalfin')
			->andwhere('date >= :intervaldebut')
			->setParameter('intervalfin',$intervalfin)
			->setParameter('intervaldebut',$intervaldebut)
			->orderBy('categorie', 'DESC', 'date', 'DESC');
		return $this->findEntities($qb);
	}

	/**
	 * Recherche des catégories qui ont déjà été mise en place.
	 */
	public function findCategorie() {
        $qb = $this->db->getQueryBuilder();

		$qb->select('categorie')
			->groupBy('categorie')
			->from($this->getTableName());

		$cursor = $qb->executeQuery();
		$array = array();
		foreach ( $cursor as $id => $value )
		{
			$array[] = $value;
		}

        return $array;
    }

	/**
	 * Insertion dans la base de données d'une actualité
	 */
	public function insertActu($date, $user_id, $lien, $description, $categorie, $title)
    {
		$qb = $this->db->getQueryBuilder();
		$qb->insert($this->getTableName())
		    ->setValue('date', '?')
		    ->setValue('utilisateur', '?')
		    ->setValue('lien', '?')
		    ->setValue('description', '?')
		    ->setValue('categorie', '?')
            ->setValue('title', '?')
            ->setParameter(0, $date)
		    ->setParameter(1, $user_id)
		    ->setParameter(2, $lien)
		    ->setParameter(3, $description)
		    ->setParameter(4, $categorie)
            ->setParameter(5, $title)
		;
		$qb->executeUpdate();
		return "ok";
	}

	/**
	 * Modification dans la base de données d'une actualité 
	 */
	public function updateActu($date, $user_id, $lien, $description, $categorie, $title, $idArticle)
    {
		$qb = $this->db->getQueryBuilder();
		$qb->update($this->getTableName(), 'u')
				->set('u.date', $qb->createNamedParameter($date))
			    ->set('u.lien', $qb->createNamedParameter($lien))
			    ->set('u.description', $qb->createNamedParameter($description))
			    ->set('u.categorie', $qb->createNamedParameter($categorie))
			    ->set('u.title', $qb->createNamedParameter($title))
				->where('u.id = :id')
				->setParameter('id', $idArticle);
		$qb->executeUpdate();
		return "ok";
	}

	/**
	 * Suppression d'une actualité
	 */
	public function delActu($id){
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
        	->where('id = :id')
        	->setParameter('id',$id);
		$qb->executeQuery();
		return "ok";
	}

	public function getNbArticleByUser($year){
		$sql = "SELECT t.utilisateur, YEAR(FROM_UNIXTIME(t.date)) as annee, COUNT(*) as count
				FROM (SELECT * 
						FROM ".'*PREFIX*'.$this->getTableName()." 
						WHERE YEAR(FROM_UNIXTIME(date)) = ?) t
				GROUP BY t.utilisateur
				ORDER BY count DESC";
		return $this->executionSqlParam($sql, $year);
	}

	public function findAll($param){
		$sql = "SELECT * 
				FROM ". '*PREFIX*' . $this->getTableName() ."
				WHERE date >= ?
				AND date <= ?
				ORDER BY date DESC;
				";

		return $this->executionSqlParam($sql, $param);
	}


	/**
	* Execution d'une requête sql
	*/
	private function executionSql($sql){
		$res = $this->db->prepare($sql);
		$res->executeQuery();
		$ret = $res->fetchall(2);
		return $ret;
	}

	/**
	* Execution d'une requête sql avec paramètre
	* param $string, $array
	*/
	private function executionSqlParam($sql, $param){
		$res = $this->db->prepare($sql);
		$res->executeQuery($param);
		$ret = $res->fetchall(2);
		return $ret;
	}

}