<?php

include_once "ROUTER.php";
include_once "MyPDO.php";

class Templates
{
    public function __construct($type)
    {
        $action=App::get(ACTION);
        switch ($action){
            case GET_ALL:
                if($type==USER){
                    $this->getAllTemplates();
                }

        }
    }
    private function getAllTemplates(){
        $conn=MyPDO::getInstance();
        $query="SELECT * FROM Templates";
        $select=$conn->prepare($query);
        try {
            $select->execute();
            $templates=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($templates,$response);
            }
            echo json_encode($templates);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
}