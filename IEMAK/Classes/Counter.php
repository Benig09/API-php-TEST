<?php

include_once "ROUTER.php";
include_once "MyPDO.php";
class Counter
{
    public function __construct($type)
    {
        if($type==USER){
            $userID=App::get(USER_ID);
            $this->getCount($userID);
        }
    }
    private function getCount($userID){
        $conn=MyPDO::getInstance();
        $query="SELECT * FROM Events WHERE UserID=:userID";
        $selectEvents=$conn->prepare($query);
        $query="SELECT * FROM Favorites WHERE UserID=:userID";
        $selectFavorites=$conn->prepare($query);
        try {
            $selectEvents->execute(array(":userID"=>$userID));
            $selectFavorites->execute(array(":userID"=>$userID));
            $eventsCount=MyPDO::getRowCount($selectEvents);
            $favoritesCount=MyPDO::getRowCount($selectFavorites);
            echo json_encode(array(
                FAVORITES=>$favoritesCount,
                EVENTS=>$eventsCount
            ));

        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
}