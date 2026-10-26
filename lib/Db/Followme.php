<?php
namespace OCA\Followme\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Followme extends Entity implements JsonSerializable {
	
	protected $date;
	protected $utilisateur;
	protected $lien;
	protected $description;
	protected $categorie;
    protected $title;

    public function __construct() {
        $this->addType('date', 'integer');
    }

	public function jsonSerialize(){
		return[
			'id' => $this->id,
			'date' => $this->date,
			'utilisateur' => $this->utilisateur,
			'lien' => $this->lien,
			'description' => $this->description,
			'categorie' => $this->categorie,
            'title' => $this->title,
		];
	}
}
