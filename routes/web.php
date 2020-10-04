<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function() {
    return array(
        'success'   =>  true,
        'response'  => [],
        'details'   => array(
            'uri' => '/'
        )
    ); 
});

$router->get('/key', function() {
    return \Illuminate\Support\Str::random(32);
});

$router->post('/authentification', 'AuthController@authentification');
$router->post('/allProduitActif', 'ProduitController@getAllProduit');
$router->post('/setTransacSell', 'TransacController@setTransacSell');
$router->post('/newUser', 'TaskController@sendMailNewUser');


// Default route if not matching any route
$router->get('{slug}', function() {
    return array(
        'success' => true,
        'response' => [],
        'details' => array(
            'uri' => '?'
        )
    );
});