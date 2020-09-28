<?php
include_once 'MyPDO.php';
include_once 'ResultManager.php';
class Cities{
    public function __construct($type)
    {
        if($type==USER&&CheckLogin::check()){
            $this->getCities();
        }
    }
    function getCities(){
        $conn=MyPDO::getInstance();
        $query="SELECT * FROM Cities";
        $select=$conn->prepare($query);
        try {
            $select->execute();
            $cities=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($cities,$response[CITY_NAME]);
            }
            echo json_encode($cities);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
        }
    }
}