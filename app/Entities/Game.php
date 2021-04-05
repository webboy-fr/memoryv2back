<?php

namespace App\Entities;

use CodeIgniter\Entity;

class Game extends Entity
{
	protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];
}
