<?php
class Favorites{
    public function __construct($type)
    {
        if($type==USER){
            $action=App::get(ACTION);
            $userID=App::get(USER_ID);
            switch ($action){
                case ADD:
                    $templateID=App::get(TEMPLATE_ID);
                    $this->addFavorite($templateID,$userID);
                    break;
                case DELETE:
                    $favoritesID=App::get(FAVORITE_ID);
                    $this->deleteFavorite($favoritesID,$userID);
                    break;
                case GET_ALL:
                    $this->getAllFavorites($userID);
                    break;
            }
        }
    }

    function addFavorite($templateID,$userID){
        $conn=MyPDO::getInstance();
        $query="INSERT INTO Favorites(UserID,TemplateID) VALUES (:userID,:templateID)";
        $insert=$conn->prepare($query);
        try {
            $insert->execute(array(
               ":templateID"=>$templateID,
                ":userID"=>$userID
            ));
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
    function deleteFavorite($favoriteID,$userID){
        $conn=MyPDO::getInstance();
        $query="DELETE FROM Favorites WHERE UserID=:userID AND FavoriteID=:favoriteID";
        $delete=$conn->prepare($query);
        try {
            $delete->execute(array(
                ":favoriteID"=>$favoriteID,
                ":userID"=>$userID
            ));
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
    function getAllFavorites($userID){
        $conn=MyPDO::getInstance();
        $query="SELECT Favorites.TemplateID,Favorites.FavoriteID,Templates.TemplateName,Templates.CategoryID,Templates.CategoryName,
        Templates.tmpLink,Templates.LinkForShare,Templates.LinkForPrint FROM Favorites LEFT JOIN Templates ON Templates.TemplateID=Favorites.TemplateID WHERE Favorites.UserID=:userID";
        $select=$conn->prepare($query);
        $select->bindParam(":userID",$userID);
        try {
            $select->execute();
            $favorites=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($favorites,$response);
            }
            echo json_encode($favorites);
        }catch (PDOException $e){
            //ResultManager::showError(__CLASS__,__LINE__);
            echo $e->getMessage();
        }
    }
}