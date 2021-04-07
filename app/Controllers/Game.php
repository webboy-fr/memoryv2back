<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\GameModel;


//TODO : mettre en lib
class Grid{

	private static $fruits = ['pomme', 'banane', 'orange', 'citronVert', 'cranberry', 'abricot', 'citron', 'fraise',
	'pommeVerte', 'peche', 'raisin', 'pasteque', 'prune', 'poire', 'cerise', 'framboise',
	'mangue', 'mirabelles'
	];

	static $level;


	static public function generate(Int $level = 0){

		self::$level = $level;
		self::$fruits = array_slice(self::$fruits, 0, self::getNbrItems()); 

		// print_r(self::$fruits);
		// exit();

		$seed = [];
		$i = 0;
		foreach(self::$fruits as $k => $fruit){
			//caseGrid.imgPosition = 0 - 100 * (randomFruit); //TODO Méthode spécialisées

			$position = 0 - (100 * $k);

			$seed[] = new Card($i, $fruit, $position);
			$i++;
			$seed[] = new Card($i, $fruit, $position);
			$i++;
		}

		shuffle($seed);
		return $seed;
	}

	static private function getNbrItems() {
		//maxFruits = 18;
		if(self::$level == 0) return 2;
		if(self::$level == 1) return 8;
		if(self::$level == 2) return 18;
	}

}


class Card{

	public Int $id;
	public String $name;
	public Bool $visible;
	public Int $position;

	public function __construct(Int $id, String $name, Int $position, Bool $visible = false){
		$this->id = $id;
		$this->name = $name;
		$this->visible = $visible;
		$this->position = $position;
	}
}



class Game extends ResourceController
{


	protected $modelName = 'App\Models\Game';
    protected $format    = 'json';

	


	public function create(String $level = '0'){

		
		$seed = Grid::generate($level);
		
		$gameModel = new GameModel();
		$gameModel->insert([
			'level' => $level,
			'seed' => json_encode($seed)
		]);

		$response = [
			'id' => $gameModel->getInsertID(),
			'grid' => $seed
		];


		return $this->respond($response);		
	}

		public function checkCardEven(){
			
			$gameModel = new GameModel();
			$gameId = $this->request->getVar('gameId');
			$game = $gameModel->find($gameId);
			$selectedCards = $this->request->getVar('selectedCards');			
			$seed = json_decode($game->seed);

			//Récupère les deux cartes selectionnées par l'utilsateur
			//TODO REFAIRE AVEC FONCTION NATIVE
			foreach($seed as $card){
				if($card->id == $selectedCards[0]->id){
					$firstCard = $card;
				} else if($card->id == $selectedCards[1]->id){
					$secondCard = $card;
				}
			}


			//Vérifie si les deux sont une paire			
			if($firstCard->name == $secondCard->name){
				$firstCard->visible = true;
				$secondCard->visible = true;
			} else {							
				$firstCard->visible = false;
				$secondCard->visible = false;
			}


			//Reformate les données de la grille pour avoir les cartes visible, ou pas
			foreach($seed as $card){
				if($card->id == $firstCard->id){
					$card = $firstCard;
				} else if($card->id == $secondCard->id){
					$card = $secondCard;
				}
			}

			//Sauve la nouvelle grille
			$gameModel->update($gameId, ['seed' => json_encode($seed)]);

			//Renvoit la nouvelle grille au front
			return $this->respond($seed);
		}


		public function update($gameId = null){			
			$player = $this->request->getVar('player');
			$time = $this->request->getVar('time');

			//Sauve le score
			$gameModel = new \App\Models\GameModel();
			$gameModel->update($gameId, [
				'player' => $player,
				'time' => $time
				]);

			
			
			$response = [
				'status'   => 200,
				'error'    => null,
				'messages' => [
					'success' => 'La partie a bien été sauvée'
				]
			];
			return $this->respond($response);

		}

		public function delete($gameId = null){			
			
			//Sauve le score
			$gameModel = new GameModel();
			$gameModel->delete($gameId);			
			
			$response = [
				'status'   => 200,
				'id' => $gameId,
				'error'    => null,
				'messages' => [
					'success' => 'La partie a bien été supprimée'
				]
			];
			return $this->respondDeleted($response);

		}


		public function scores(){
			$gameModel = new GameModel();

			$scores = $gameModel->select('player, level, time')			
			->where([				
				'player != ' => 'NULL'
				])
			->orderBy('time', 'ASC')			
			->findAll(5);

			$response = [];
			$response[0] = [];
			$response[1] = [];
			$response[2] = [];
			foreach($scores as $score){
				$response[$score->level][] = $score;
			}

			//Pas oublier de faire limite 3 ou autre chose
			return $this->respond($response);
		}


}
