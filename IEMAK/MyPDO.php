<?php

include_once "Config.php";
include_once "ResultManager.php";

class MyPDO extends PDO{
    public static $instance = null;
    public function __construct()
    {
        try {
            parent::__construct("mysql:host=".HOST_NAME.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
            self::$instance=$this;
            self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            self::$instance->exec("set names utf8");
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
        }
    }
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }
    public static function getInstance(){
        try {
            if(self::$instance==null){
                self::$instance=new PDO("mysql:host=".HOST_NAME.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                self::$instance->exec("set names utf8");
            }
        }catch (PDOException $e){
            /*ResultManager::showError(__CLASS__,__LINE__);*/
            echo $e->getMessage();
        }
        return self::$instance;
    }
    public static function getRowCount($stmt){
        return $stmt->rowCount();
    }
    public static function getLastID($conn){
        return $conn->lastInsertId();
    }
}