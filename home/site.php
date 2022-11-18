<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get('/', function() {

	$products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

$app->get("/categories/:idcategory", function ($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) {
		array_push($pages, [
			'link'=>'/home/categories/' . $category->getidcategory() . '?page='.$i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category' => $category->getValues(),
		'products' => $pagination ['data'],
		'pages' => $pages
	]);

});

$app->get("/products/:desurl", function($desurl){
	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=> $product->getCategories()
	]);
});

$app->get("/cart", function(){

	require_once "functions.php";
	
	// var_dump(getUserName());
	// die();
	$cart = Cart::getFromSession();
	
	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

$app->get("/cart/:idproduct/add", function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++){
		$cart->addProduct($product);
	}
	
	header("Location: /home/cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /home/cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /home/cart");
	exit;
});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /home/cart");
	exit;
});

$app->get("/cart/checkout/", function(){

	User::verifyLogin(false);

	$address = new Address();

	$cart = Cart::getFromSession();

	if(isset($_GET['zipcode'])) {

		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();
	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});

// $app->post("/cart/checkout", function(){

// });

$app->get("/cart/login", function(){
	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'',
		'email'=>'', 'phone'=>'']
	]);
});

$app->post("/cart/login", function(){

	try{
		User::login($_POST['login'], $_POST['password']);
	} catch(Exception $e) {
		User::setError($e->getMessage());
	}

	header("Location: /home/cart/checkout");
	exit;
});



$app->post("/cart/login/register", function(){

	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /home/cart/login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email'] == '') {
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /home/cart/login");
		exit;
	}

	if(!isset($_POST['password']) || $_POST['password'] == '') {
		User::setErrorRegister("Digite uma senha.");
		header("Location: /home/cart/login");
		exit;
	}

	if(User::checkLoginExist($_POST['email']) === true) {
		User::setErrorRegister("Este endereço de e-mail já está cadastrado.");
		header("Location: /home/cart/login");
		exit;
	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $POST['password']);

	header("Location: /home/cart/checkout");
	exit;
});

$app->get("/cart/checkout/profile", function (){
	User::verifyLogin(false);

	$page = new Page();

	$user = User::getFromSession();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

$app->post("/cart/checkout/profile", function(){

	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome.");
		header("Location: /home/cart/checkout/profile");
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu e-mail");
		header("Location: /home/cart/checkout/profile");
		exit;
	}

	$user = User::getFromSession();

	if($_POST['desemail'] !== $user->getdesemail()) {
		if(User::checkLoginExist($_POST['desemail']) === true) {
			User::setError("Esse endereço de e-mail já está cadastrado.");
			header("Location: /home/cart/checkout/profile");
			exit;
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados alterados com sucesso.");

	header("Location: /home/cart/checkout/profile");
	exit;
});
