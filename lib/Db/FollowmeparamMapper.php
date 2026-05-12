<?php
namespace OCA\Followme\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

/**
 * Interrogation de la table followme dans la base de données générale de nextcloud
 */
Class FollowmeparamMapper extends QBMapper {

	public function __construct(IDbConnection $db) {
		parent::__construct($db, 'followme_parameter', Followme::class);
	}

	public function insertParam($userIdWP, $passwordWP, $urlWP, $userIdRC, $passwordRC, $urlRC, $roomRC){
		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'userIdWP'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('userIdWP', ?);";
		$this->insertSqlParam($sql, array($userIdWP));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'passwordWP'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('passwordWP', ?);";
		$this->insertSqlParam($sql, array($passwordWP));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'userIdRC'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('userIdRC', ?);";
		$this->insertSqlParam($sql, array($userIdRC));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'passwordRC'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('passwordRC', ?);";
		$this->insertSqlParam($sql, array($passwordRC));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'urlWP'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('urlWP', ?);";
		$this->insertSqlParam($sql, array($urlWP));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'urlRC'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('urlRC', ?);";
		$this->insertSqlParam($sql, array($urlRC));

		$sql = "DELETE FROM ".'*PREFIX*'.$this->getTableName()." 
				WHERE ((`name` = 'roomRC'));
				INSERT INTO `oc_followme_parameter` (`name`, `value`)
				VALUES ('roomRC', ?);";
		$this->insertSqlParam($sql, array($roomRC));
	}

	public function getParam(){
		$sql = "SELECT * FROM ".'*PREFIX*'.$this->getTableName();
		return $this->executionSql($sql);
	}


	public function getParamByName($name){
		$sql = "SELECT * FROM ".'*PREFIX*'.$this->getTableName()." WHERE name = ?";
		return $this->executionSqlParam($sql, array($name))[0]['value'];
	}

	/**
	*Execution d'une requête sql
	*/
	private function executionSql($sql){
		$res = $this->db->prepare($sql);
		$res->execute();
		$ret = $res->fetchall(2);
		return $ret;
	}

	/**
	*Execution d'une requête sql avec paramètre
	*param $string, $array
	*/
	private function executionSqlParam($sql, $param){
		$res = $this->db->prepare($sql);
		$res->execute($param);
		$ret = $res->fetchall(2);
		return $ret;
	}

	/**
	*Insert SQL
	*/
	private function insertSqlParam($sql, $param){
		$res = $this->db->prepare($sql);
		$res->execute($param);
	}

}