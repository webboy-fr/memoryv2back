<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\GameModel;


//TODO : mettre en lib CI4 (pas le temps :( )
class Grid{

	private static $fruits = ['pomme', 'banane', 'orange', 'citronVert', 'cranberry', 'abricot', 'citron', 'fraise',
	'pommeVerte', 'peche', 'raisin', 'pasteque', 'prune', 'poire', 'cerise', 'framboise',
	'mangue', 'mirabelles'
	]; //Notre tableau de référence

	static $level; //Niveau de difficulté (sert à définir la taille de la grille)


	/**
	 * Cette méthode créer un tableau sur la base de $fruits et de $level
	 */
	static public function generate(Int $level = 0){

		self::$level = $level;
		self::$fruits = array_slice(self::$fruits, 0, self::getNbrItems()); 

		$seed = []; //Contiendra la seed "unique" d'un nouveau jeu
		$i = 0;
		foreach(self::$fruits as $k => $fruit){
			$position = 0 - (100 * $k); //Position pour le sprite
			$seed[] = new Card($i, $fruit, $position); //On crée une carte
			$i++;
			$seed[] = new Card($i, $fruit, $position); //Les deux font la paire
			$i++;
		}

		shuffle($seed); //Et on mélange tout ça
		return $seed; //On retourne le tableau mélangé aléatoiremnt
	}

	/**
	 * Retourne la taille de la grille en fonction du niveau
	 */
	static private function getNbrItems() {		
		if(self::$level == 0) return 2;
		if(self::$level == 1) return 8;
		if(self::$level == 2) return 18;
	}

}

//TODO : mettre en lib CI4 (pas le temps :( )
class Card{

	//Propriétés permettant de définir une carte
	public Int $id; //Id autoincrement
	public String $name; //Nom du fruit
	public Bool $visible; //Visible ou pas 
	public Int $position; //Position pour sprite CSS basé sur l'index du fruit dans le tableau


	/**
	 * Constructeur pour créer un nouvel objet carte
	 */
	public function __construct(Int $id, String $name, Int $position, Bool $visible = false){
		$this->id = $id;
		$this->name = $name;
		$this->visible = $visible;
		$this->position = $position;
	}
}


/**
 * Cette classe va gérer les échange avec le client
 * Elle hérite de la classe RessourceController proposé par le framework pouir gérer des routes d'api
 */
class Game extends ResourceController
{


	protected $modelName = 'App\Models\Game';
    protected $format    = 'json'; //Permet de définir automatiquement le MIME type retourné

	

	/**
	 * Créer une nouvelle partie
	 */
	public function create(String $level = '0'){

		
		$seed = Grid::generate($level); //Génère une nouvelle grille aléatoire
		
		$gameModel = new GameModel(); //On load le model (échange avec la DB)
		$gameModel->insert([ //On insert les données de la nouvelle partie
			'level' => $level,
			'seed' => json_encode($seed) //La seed de chaque partie est stocké au format chaine JSON
		]);

		$response = [ //On construit la réponse pour le client
			'id' => $gameModel->getInsertID(),
			'grid' => $seed
		];


		return $this->respond($response); //On renvoit le json au client
	}


	/**
	 * Permet de savoir si deux cartes forment une paire, ou pas
	 */
	public function checkCardEven(){
		
		$gameModel = new GameModel();
		$gameId = $this->request->getVar('gameId'); //On récupère l'id de la partie
		$game = $gameModel->find($gameId); //On charge la partie en cours
		$selectedCards = $this->request->getVar('selectedCards'); //On récupère les deux cartes choisies par l'user			
		$seed = json_decode($game->seed); //On récupère la seed de la partie en cours

		//Récupère les deux cartes de la seed selectionnées par l'utilsateur
		//TODO REFAIRE AVEC FONCTION NATIVE :( pas le temps
		foreach($seed as $card){
			if($card->id == $selectedCards[0]->id){
				$firstCard = $card;
			} else if($card->id == $selectedCards[1]->id){
				$secondCard = $card;
			}
		}


		//Vérifie si les deux sont une paire			
		if($firstCard->name == $secondCard->name){ //C'est une paire
			$firstCard->visible = true;
			$secondCard->visible = true;
		} else { // C'est pas une paire
			$firstCard->visible = false;
			$secondCard->visible = false;
		}


		//Reformate les données de la grille pour avoir les cartes sélectionnées visible, ou pas
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
		return $this->respond($seed);  //respond renverra automatiquement du JSON
	}

	/**
	 * Permet d'enregistrer le nom du joueur et le temps de la partie
	 */
	public function update($gameId = null){			
		$player = $this->request->getVar('player');
		$time = $this->request->getVar('time');

		//TODO : vérifier que le pseudo n'existe pas deja (pas le temps)

		//Sauve le score
		$gameModel = new GameModel();
		$gameModel->update($gameId, [
			'player' => $player,
			'time' => $time
			]);

		
		//Renvoit une réponse plus étoffé que les précédent, avec statut HTTP (en cours)
		$response = [
			'status'   => 200,
			'error'    => null,
			'messages' => [
				'success' => 'La partie a bien été sauvée'
			]
		];
		return $this->respond($response);

	}

	/**
	 * Permet de supprimer une partie si l'user ne souhaite pas enregistrer le score
	 */
	public function delete($gameId = null){			
		
		//Supprime la partie
		$gameModel = new GameModel();
		$gameModel->delete($gameId);			
		
		$response = [ //Réponse de l'API (en cours de dev)
			'status'   => 200,
			'id' => $gameId,
			'error'    => null,
			'messages' => [
				'success' => 'La partie a bien été supprimée'
			]
		];
		return $this->respondDeleted($response);

	}

	/**
	 * Permet de récupérer tous les scores déjà enregistrer lpour le tableau de scores
	 */
	public function scores(){
		$gameModel = new GameModel();

		$scores = $gameModel->select('player, level, time') //On récupère les 5 meilleurs scores pour chaque niveau
		->where([				
			'player != ' => 'NULL' // Et seulement les parties qui ont un noueur enregistré
			])
		->orderBy('time', 'ASC')			
		->findAll(5); 

		//On stocke tous les scores dans un seul tableau (plus pratique pour le front)
		$response = [];
		$response[0] = [];
		$response[1] = [];
		$response[2] = [];
		foreach($scores as $score){
			$response[$score->level][] = $score;
		}

		
		return $this->respond($response);
	}

}
