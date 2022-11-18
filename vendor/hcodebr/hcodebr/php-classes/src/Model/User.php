<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{

    const SESSION = "User";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";

    public static function getFromSession()
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

            $user->setData($_SESSION[User::SESSION]);

        }

        return $user;

    }

    public static function checkLogin($inadmin = true)
    {

        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {
            //Não está logado.
            return false;
        } else {
            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){

            return true;

            } else if ($inadmin === false){
                return true;
            } else {
                return false;
            }
        }

    }

	public static function login($login, $password)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		)); 

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

	}

    public static function verifyLogin($inadmin = true)
    {
        if (!User::checkLogin($inadmin)) {
            if ($inadmin) {
                header("Location: /home/admin/login"); 
            } else {
                header("Location: /home/cart/login");
            }
            exit;
        }
    }

    public static function logout()
    {

        $_SESSION[User::SESSION] = NULL;
        header("Location: /home/admin/login");
        exit;
    }

    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM db_ecommerce.tb_users  as a  INNER JOIN db_ecommerce.tb_persons as b ON a.idperson = b.idperson ORDER BY b.desperson");

    }

    public function save()
    {
        $sql = new Sql();

        $query = "CALL db_ecommerce.sp_users_save('{$this->getdesperson()}', '{$this->getdeslogin()}', '{$this->getdespassword()}', '{$this->getdesemail()}', '{$this->getnrphone()}', '{$this->getinadmin()}')";

        try {
            $sql->execute($query);
            return true;
        } catch (Exception $e) {
           echo var_dump($e);
        }

    }

    public function get($iduser)
    {
 
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
        ":iduser"=>$iduser
        ));
    
        $data = $results[0];

        $data['desperson'] = utf8_encode($data['desperson']);
 
        $this->setData($data);

    }

    public function update()
    {
  
        $sql = new Sql();

        //  $query = "CALL db_ecommerce.sp_users_save('{$this->getiduser()}', '{$this->getdesperson()}', '{$this->getdeslogin()}', '{$this->getdespassword()}', '{$this->getdesemail()}', '{$this->getnrphone()}', '{$this->getinadmin()}')";

        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail,
        :nrphone, :inadmin)", array(
            ":iduser" => $this->getiduser(),
            ":desperson" => utf8_decode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($results[0]);


        // try {
        //     $sql->execute($query);
        //     return true;
        // } catch (Exception $e) {
        //    echo var_dump($e);
        // }
    }

    public function delete()
    {

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array (
            ":iduser"=>$this->getiduser()
        ));

    }

    public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

    public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

    public static function checkLoginExist($login)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
            ':deslogin'=>$login
        ]);

        return (count($results) > 0);

    }

    public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;

	}

    public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}



}
