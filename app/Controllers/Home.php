<?php

namespace App\Controllers;


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



class Home extends BaseController
{

	public function newGame(String $level = '0'){

		$seed = Grid::generate($level);
		
		$gameModel = new \App\Models\GameModel();
		$gameModel->insert([
			'level' => $level,
			'seed' => json_encode($seed)
		]);

		$response = [
			'id' => $gameModel->getInsertID(),
			'grid' => $seed
		];


		return $this->response->setJSON($response);		
	}

		public function checkEven(){
			
			$gameModel = new \App\Models\GameModel();
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
			return $this->response->setJSON($seed);
		}


		public function save(){
			$gameId = $this->request->getVar('gameId');
			$player = $this->request->getVar('player');
			$time = $this->request->getVar('time');

			//Sauve le score
			$gameModel = new \App\Models\GameModel();
			$gameModel->update($gameId, [
				'player' => $player,
				'time' => $time
				]);

			return $this->response->setJSON(['ok']);

		}

		public function scores($level = ''){
			$gameModel = new \App\Models\GameModel();

			$scores = $gameModel->select('player, time')			
			->where([
				'level' => $level,
				'player != ' => 'NULL'
				])
			->orderBy('time', 'ASC')			
			->findAll(5);

			

			//Pas oublier de faire limite 3 ou autre chose
			return $this->response->setJSON($scores);
		}


}
