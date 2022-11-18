<?php

use \Hcode\Model\User;

function formatPrice(float $vlprice)
{

    return number_format($vlprice, 2, ",", ".");

}

function checkLogin($inadmin = true)
{
    User::checkLogin($inadmin);
}
// essa função serve para pegar o nome de usuário da sessão e renderizar na view do site.
function getUserName()
{

    $user = User::getFromSession();

    return $user->getEu();

}