<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
//$routes->setDefaultController('Game');
//$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Home::index');






//L'appli front n'est pas hébergé sur le même nom de domaine que l'appli back
//Donc mise en place de headers permettant de gérer le cross origin

//CORS
header('Access-Control-Allow-Origin: '.env('app.client')); //On autorise l'accès que au serveur du front
header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS"); //On définit toutes les méthodes HTTP autorisées
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header('Content-Type: application/json');

//PREFLIGHT CROSSDOMAIN
//Les requetes CORS ont une première requete de test du navigateur, qui utilise la méthode HTTP OPTIONS
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
	header('Access-Control-Allow-Origin: *'); //oops TODO
	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
	header("HTTP/1.1 200 OK");
	die();
}


//Définition des routes de l'api
$routes->get('game/create/(:num)', 'Game::create/$1'); //Créer une partie (GET)
$routes->put('game/(:num)', 'Game::update/$1'); //Enregistre une partie (PUT)
$routes->delete('game/(:num)', 'Game::delete/$1'); //Supprime une partie (DELETE)
$routes->post('game/checkCardEven', 'Game::checkCardEven'); //Check une paire (POST)
$routes->get('game/scores', 'Game::scores'); //Récupère les scores (GET)







/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
