<?php
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#showActu', 'url' => '/showActu', 'verb' => 'POST'],
	   ['name' => 'page#postActuInterval', 'url' => '/postActuInterval', 'verb' => 'POST'],
	   ['name' => 'page#insertActu', 'url' => '/insertActu', 'verb' => 'POST'],
	   ['name' => 'page#delActu', 'url' => '/delActu', 'verb' => 'POST'],
	   ['name' => 'page#findCategorie', 'url' => '/findCategorie', 'verb' => 'GET'],
	   ['name' => 'page#updateActu', 'url' => '/updateActu', 'verb' => 'POST'],
	   ['name' => 'page#findActu', 'url' => '/findActu', 'verb' => 'GET'],
	   ['name' => 'page#getNbArticleByUser', 'url' => '/getNbArticleByUser', 'verb' => 'POST'],
	   ['name' => 'page#envoieWP', 'url' => '/envoieWP', 'verb' => 'POST'],
	   ['name' => 'admin#index', 'url' => '/param', 'verb' => 'GET'],
	   ['name' => 'admin#save', 'url' => '/param', 'verb' => 'POST'],
    ]
];