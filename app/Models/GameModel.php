<?php

//Cette classe expose tout un tas de méthode standard pour interagir avec la DB (extend Model)
//C'est cette classe qui me permet de faire les insert/update/find/findAll facilement

namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
	protected $DBGroup              = 'default';
	protected $table                = 'game';
	protected $primaryKey           = 'id';
	protected $useAutoIncrement     = true;
	protected $insertID             = 0;
	protected $returnType           = 'App\Entities\Game'; //Renverra automatiquement des objets Game
	protected $useSoftDelete        = false;
	protected $protectFields        = true;
	protected $allowedFields        = ['level', 'seed', 'player', 'time'];

	// Dates
	protected $useTimestamps        = false;
	protected $dateFormat           = 'datetime';
	protected $createdField         = 'created_at';
	protected $updatedField         = 'updated_at';
	protected $deletedField         = 'deleted_at';

	// Validation
	protected $validationRules      = [];
	protected $validationMessages   = [];
	protected $skipValidation       = false;
	protected $cleanValidationRules = true;

	// Callbacks
	protected $allowCallbacks       = true;
	protected $beforeInsert         = [];
	protected $afterInsert          = [];
	protected $beforeUpdate         = [];
	protected $afterUpdate          = [];
	protected $beforeFind           = [];
	protected $afterFind            = [];
	protected $beforeDelete         = [];
	protected $afterDelete          = [];
}
