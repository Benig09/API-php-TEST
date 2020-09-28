<?php
class Events{
    public function __construct($type)
    {
        $action=App::get(ACTION);
        switch ($action){
            case ADD:
                if($type==USER) {
                    $pax=App::get(PAX);
                    $userID=App::get(USER_ID);
                    $templates=App::get(TEMPLATES);
                    $eventName=App::get(EVENT_NAME);
                    $this->addEvent($userID,$pax,$templates,$eventName);
                }
                break;
            case GET_ALL:
                $this->getAllEvents($type);
                break;
            case DELETE:
                if($type==USER){
                    $eventID=App::get(EVENT_ID);
                    $userID=App::get(USER_ID);
                    $this->deleteEvent($eventID,$userID);
                }
                break;
            case GET:
                $eventID=App::get(EVENT_ID);
                if($type==USER){
                    $userID=App::get(USER_ID);
                    $this->getEvent($userID,$eventID);
                }elseif ($type==OPERATOR){
                    $userID=App::get(RELATED_TO_USER_ID);
                    $this->getEvent($userID,$eventID);
                }
                break;
            case EDIT:
                break;
        }
    }
    private function addEvent($userID,$pax,$templates,$eventName){
        $temps=json_decode($templates,true);
        if(sizeof($temps)>0&&sizeof($temps)<3){
            $conn=MyPDO::getInstance();
            $connF=MyPDO::getInstance();
            $query="INSERT INTO Events(UserID,PAX,EventName) VALUES (:userID,:pax,:eventName)";
            $insertEvent=$conn->prepare($query);
            $insertEvent->execute(array(
                "userID"=>$userID,
                "pax"=>$pax,
                "eventName"=>$eventName
            ));
            $lastEventID=MyPDO::getLastID($conn);
            $query="INSERT INTO UsersTemplates(TemplateID,Title,MainText,Location,Date,EventID,tmpLink,LinkForPrint,LinkForShare)VALUES";
            $queryFavorite="INSERT IGNORE INTO Favorites(TemplateID,UserID)VALUES ";
            for ($i=0;$i<sizeof($temps);$i++){
                $queryFavorite.="(:templateID$i,:userID$i),";
                $query.="(:templateID$i,:title$i,:mainText$i,:location$i,:date$i,:eventID,:tmpLink$i,:linkForPrint$i,:linkForShare$i),";
            }
            $query=substr($query,0,-1);
            $queryFavorite=substr($queryFavorite,0,-1);
            $insertTemplate=$conn->prepare($query);
            $insertFavorite=$connF->prepare($queryFavorite);
            for($i=0;$i<sizeof($temps);$i++){
                $insertTemplate->bindParam(":templateID$i"  ,$temps[$i][TEMPLATE_ID]);
                $insertTemplate->bindParam(":title$i"       ,$temps[$i][TITLE]);
                $insertTemplate->bindParam(":mainText$i"    ,$temps[$i][MAIN_TEXT]);
                $insertTemplate->bindParam(":location$i"    ,$temps[$i][LOCATION]);
                $insertTemplate->bindParam(":eventID"       ,$lastEventID);
                $insertTemplate->bindParam(":tmpLink$i"  ,$temps[$i][TMP_LINK]);
                $insertTemplate->bindParam(":date$i"        ,$temps[$i][DATE]);
                $insertTemplate->bindParam(":linkForPrint$i"  ,$temps[$i][LINK_FOR_PRINT]);
                $insertTemplate->bindParam(":linkForShare$i"  ,$temps[$i][LINK_FOR_SHARE]);
                $insertFavorite->bindParam(":userID$i"      ,$userID);
                $insertFavorite->bindParam(":templateID$i"  ,$temps[$i][TEMPLATE_ID]);
            }
            try {
                $insertTemplate->execute();
                $insertFavorite->execute();
            }catch (PDOException $e){
                //ResultManager::showError(__CLASS__,__LINE__);
                echo $e->getMessage();
            }
            $query="Select * FROM UsersTemplates WHERE EventID=:eventID";
            $select=$conn->prepare($query);
            $select->execute(array(
                ":eventID"=>$lastEventID
            ));
            $temps=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                $result=array(
                    ID=>$response[ID],
                    TEMPLATE_ID=>$response[TEMPLATE_ID],
                    TITLE=>$response[TITLE],
                    MAIN_TEXT=>$response[MAIN_TEXT],
                    LOCATION=>$response[LOCATION],
                    DATE=>$response[DATE],
                    TMP_LINK=>$response[TMP_LINK],
                    LINK_FOR_SHARE=>$response[LINK_FOR_SHARE],
                    LINK_FOR_PRINT=>$response[LINK_FOR_PRINT],
                    EVENT_ID=>$response[EVENT_ID]
                );
                array_push($temps,$result);
            }
            echo json_encode($temps);
        }else{
            ResultManager::showResult("Min json array length is 1 and max is 2");
        }
    }
    function getAllEvents($type){
        $conn=MyPDO::getInstance();
        $userID=-1;
        if($type==USER){
            $userID=App::get(USER_ID);
        }elseif ($type==OPERATOR){
            $userID=App::get(RELATED_TO_USER_ID);
        }
        $query="SELECT * FROM Events WHERE UserID=:userID";
        $select=$conn->prepare($query);
        $select->bindParam(":userID",$userID);
        try {
            $select->execute();
            $result=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                $query="SELECT * FROM UsersTemplates WHERE EventID=:eventID";
                $selectTemplates=$conn->prepare($query);
                $selectTemplates->execute(array(
                   ":eventID"=>$response[EVENT_ID]
                ));
                $templates=array();
                while ($res=$selectTemplates->fetch(PDO::FETCH_ASSOC)){
                    array_push($templates,$res);
                }
                $event=array(
                    EVENT_ID=>$response[EVENT_ID],
                    EVENT_NAME=>$response[EVENT_NAME],
                    USER_ID=>$response[USER_ID],
                    INVITED_COUNT=>$response[INVITED_COUNT],
                    PAX=>$response[PAX],
                    ENTERED=>$response[ENTERED],
                    TEMPLATES=>$templates
                );
                array_push($result,$event);
            }
            echo json_encode($result);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
    function deleteEvent($eventID,$userID){
        $conn=MyPDO::getInstance();
        $query="DELETE FROM Events WHERE eventID=:eventID AND UserID=:userID";
        $delete=$conn->prepare($query);
        $delete->bindParam(":eventID",$eventID);
        $delete->bindParam(":userID",$userID);
        try {
            $delete->execute();
            if(MyPDO::getRowCount($delete)==1){
                ResultManager::showResult(SUCCESSFUL);
            }else{
                ResultManager::showResult(NOT_SUCCESSFUL);
            }
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
    function getEvent($userID,$eventID){
        $conn=MyPDO::getInstance();
        $query="SELECT Events.PAX,Events.InvitedCount,Events.EventID,Events.EventName,UsersTemplates.TemplateID,
        UsersTemplates.Title,UsersTemplates.ID,UsersTemplates.MainText,UsersTemplates.Location,UsersTemplates.Date,
        UsersTemplates.tmpLink,UsersTemplates.LinkForShare,UsersTemplates.LinkForPrint 
        From UsersTemplates LEFT JOIN Events ON UsersTemplates.EventID=Events.EventID WHERE Events.EventID=:eventID AND Events.UserID=:userID";
        $select=$conn->prepare($query);
        $select->bindParam(":userID",$userID);
        $select->bindParam(":eventID",$eventID);
        try {
            $select->execute();
            $temps=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($temps,$response);
            }
            echo json_encode($temps);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }

    }
}