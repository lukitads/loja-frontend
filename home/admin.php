<?php

use \Hcode\PageAdmin;

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
	$postdata = http_build_query(
		array(
			'var1' => 'some content',
			'var2' => 'doh'
		)
	);
	
	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		)
	);
	
	$context = stream_context_create($opts);
	
	$result = file_get_contents('http://localhost:8000/usuario/api/login', false, $context);
	
	echo(json_encode($result));
});

$app->get('/admin/logout', function(){
	header("Location: /home/admin/login");
	exit;
});