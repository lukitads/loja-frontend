<?php

use \Hcode\PageAdmin;
use Hcode\Requisicao;

$app->get('/admin', function() {
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=> false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){
	
	$payload = [
		'login' => $_POST['login'],
		'password' => $_POST['password']
	];

	$url = 'http://localhost:8000/usuario/api/login';

	$requisicao = new Requisicao($payload, $url);
	$result = $requisicao->post();

	echo(json_encode($result));
});

$app->get('/admin/logout', function(){
	header("Location: /home/admin/login");
	exit;
});