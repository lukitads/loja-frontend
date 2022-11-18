<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model
{

    
    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM db_ecommerce.tb_products ORDER BY desproduct");

    }

    public static function checkList($list)
    {

        foreach ($list as &$row){
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }

        return $list;

    }

    public function save()
    {

        $sql = new Sql();
        $query = "CALL db_ecommerce.sp_products_save(0, '{$this->getdesproduct()}', '{$this->getvlprice()}', '{$this->getvlwidth()}', '{$this->getvlheight()}', '{$this->getvllength()}', '{$this->getvlweight()}', '{$this->getdesurl()}')";

        /*echo var_dump($query);
        die();*/
        try {
            $sql->execute($query);
            return true;
        } catch(Exception $e) {
            echo var_dump($e);
        }

        /*$results = $sql->select("CALL db_ecommerce.sp_categories_save(:idcategory, :descategory", array(
            ":idcategory"=>$this->getidcategory(),
            "descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]);*/

    }

    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$idproduct
        ]);

        $this->setData($results[0]);

    }

    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array (
            ":idproduct"=>$this->getidproduct()
        ));
    }

    /*public static function updateFile()
    {

        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row){
            array_push($html, '<li><a href="/home/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }  */
    public function checkPhoto()
    {

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR .
        "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . 
        $this->getidproduct() . ".jpg"
        )) {
            $url =  "/resources/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            $url = "/resources/site/img/product.jpg";
        }

        return $this->setdesphoto($url);

    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;

    }

    public function setPhoto($file)
    {

        $extension = explode('.', $file['name']);
        $extension = end($extension);

        switch ($extension){
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
            break;

            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
            break;
            
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;

            
        }

        $destino = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR .
        "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . 
        $this->getidproduct() . ".jpg";

        imagejpeg($image, $destino);

        imagedestroy($image);

        $this->checkPhoto();

    }

    public function getFromURL($desurl)
    {

        $sql = new Sql();

        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            'desurl'=>$desurl
        ]);

        $this->setData($rows[0]);

    }

    public function getCategories()
    {

        $sql = new Sql();

        return $sql->select("
            SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory
            WHERE b.idproduct = :idproduct
        ", [
            ':idproduct'=>$this->getidproduct()
        ]);

    }
}