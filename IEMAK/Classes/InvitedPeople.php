<?php
include_once "ROUTER.php";
class InvitedPeople{
    public function __construct($type)
    {
        $action=App::get(ACTION);
        if($type==USER){
            switch ($action){
                case ADD:{
                    $hostID=App::get(USER_ID);
                    $name=App::get(NAME);
                    $eventID=App::get(EVENT_ID);
                    $templateID=App::get(TEMPLATE_ID);
                    $share=App::get(SHARE);
                    $print=App::get(PRINT_);
                    $this->addInvitedPerson($hostID,$name,$eventID,$templateID,$share,$print);
                }
                    break;
                case DELETE:{
                    $hostID=App::get(USER_ID);
                    $eventID=App::get(EVENT_ID);
                    $invitedUserID=App::get(INVITED_USER_ID);
                    $this->deleteInvitedPerson($hostID,$eventID,$invitedUserID);
                }
                    break;
                case GET_ALL:
                    $hostID=App::get(USER_ID);
                    $eventID=App::get(EVENT_ID);
                    $this->getInvitedPeople($hostID,$eventID);
                    break;
                case EDIT:
                    $hostID=App::get(USER_ID);
                    $eventID=App::get(EVENT_ID);
                    $invitedUserID=App::get(INVITED_USER_ID);
                    $share=App::get(SHARE);
                    $print=App::get(PRINT_);
                    $name=App::get(NAME);
                    $qrEnabled=App::get(QR_ENABLED);
                    $entered=App::get(ENTERED);
                    $this->editInvitedPerson($hostID,$name,$eventID,$share,$print,$entered,$qrEnabled,$invitedUserID);
                    break;
            }
        }
        if($type==OPERATOR){
            switch ($action){
                case EDIT:
                    $eventID=App::get(EVENT_ID);
                    $invitedUserID=App::get(INVITED_USER_ID);
                    $hostID=App::get(RELATED_TO_USER_ID);
                    $entered=App::get(ENTERED);
                    $this->setEntered($entered,$hostID,$invitedUserID,$eventID);
                    break;
            }
        }
    }

    function addInvitedPerson($hostID,$name,$eventID,$templateID,$share,$print){
        $conn=MyPDO::getInstance();
        $query="SELECT PAX,InvitedCount FROM Events WHERE EventID=:eventID AND UserID=:userID";
        $selectEvent=$conn->prepare($query);
        try {
            $selectEvent->execute(array(
                ":eventID"=>$eventID,
                ":userID"=>$hostID
            ));
            if(MyPDO::getRowCount($selectEvent)==1){
                $response=$selectEvent->fetch();
                if($response[INVITED_COUNT]<$response[PAX]){
                    $query="INSERT INTO InvitedPeople(EventID,HostID,Name,TemplateID,Share,Print) VALUES (:eventID,:hostID,:name,:templateID,:share,:print)";
                    $insert=$conn->prepare($query);
                    $share=filter_var($share,FILTER_VALIDATE_BOOLEAN);
                    $print=filter_var($print,FILTER_VALIDATE_BOOLEAN);
                    $share=($share?1:0);
                    $print=($print?1:0);
                    $insert->execute(array(
                        ":name"=>$name,
                        ":templateID"=>$templateID,
                        ":share"=>$share,
                        ":print"=>$print,
                        ":eventID"=>$eventID,
                        ":hostID"=>$hostID
                    ));
                    if(MyPDO::getRowCount($insert)==1){
                        $query="UPDATE Events SET InvitedCount=InvitedCount+1 WHERE EventID=:eventID";
                        $updateCounter=$conn->prepare($query);
                        $updateCounter->execute(array(":eventID"=>$eventID));
                        ResultManager::showResult(SUCCESSFUL);
                    }else{
                        ResultManager::showResult(NOT_SUCCESSFUL);
                    }
                }else{
                    echo "Invited people are more than PAX";
                }
            }
            else{
                echo "Logout";
            }
        }catch (PDOException $e){
            //ResultManager::showError(__CLASS__,__LINE__);
            echo $e->getMessage();
        }
    }
    function deleteInvitedPerson($hostID,$eventID,$invitedUserID){
        $conn=MyPDO::getInstance();
        $query="SELECT PAX,InvitedCount FROM Events WHERE EventID=:eventID AND UserID=:userID";
        $selectEvent=$conn->prepare($query);
        try {
            $selectEvent->execute(array(
                ":eventID"=>$eventID,
                ":userID"=>$hostID
            ));
            if(MyPDO::getRowCount($selectEvent)==1){
                $response=$selectEvent->fetch();
                if($response[INVITED_COUNT]>0){
                    $query="DELETE FROM InvitedPeople WHERE InvitedUserID=:invitedUserID AND EventID=:eventID AND HostID=:hostID";
                    $delete=$conn->prepare($query);
                    $delete->execute(array(
                        ":eventID"=>$eventID,
                        ":hostID"=>$hostID,
                        ":invitedUserID"=>$invitedUserID
                    ));
                    if(MyPDO::getRowCount($delete)==1){
                        $query="UPDATE Events SET InvitedCount=InvitedCount-1 WHERE EventID=:eventID";
                        $updateCounter=$conn->prepare($query);
                        $updateCounter->execute(array(":eventID"=>$eventID));
                        ResultManager::showResult(SUCCESSFUL);
                    }else{
                        ResultManager::showResult(NOT_SUCCESSFUL);
                    }
                }else{
                    echo "Invited people are more than PAX";
                }
            }
            else{
                echo "Logout";
            }
        }catch (PDOException $e){
            //ResultManager::showError(__CLASS__,__LINE__);
            echo $e->getMessage();
        }
    }
    function getInvitedPeople($hostID,$eventID){
        $conn=MyPDO::getInstance();
        $query="SELECT * FROM InvitedPeople WHERE EventID=:eventID AND HostID=:hostID;";
        $select=$conn->prepare($query);
        try {
            $select->execute(array(
                ":eventID"=>$eventID,
                ":hostID"=>$hostID
            ));
            $invitedPeople=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($invitedPeople,
                    array(
                    INVITED_USER_ID=>$response[INVITED_USER_ID],
                    NAME=>$response[NAME],
                    ENTERED=>($response[ENTERED]==0?false:true),
                    TEMPLATE_ID=>$response[TEMPLATE_ID],
                    SHARE=>($response[SHARE]==0?false:true),
                    PRINT_=>($response[PRINT_]==0?false:true),
                    QR_ENABLED=>($response[QR_ENABLED]==0?false:true),
                ));
            }
            echo json_encode($invitedPeople);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }

    function editInvitedPerson($hostID, $name, $eventID, $share, $print, $entered, $qrEnabled, $invitedUserID){
        if($name!=-1||$share!=-1||$print!=-1||$entered!=-1||$qrEnabled!=-1){
            $conn=MyPDO::getInstance();
            $query="UPDATE InvitedPeople SET ";
            if($name!=-1){
                $query.="Name='$name',";
            }
            if($share!=-1){
                $share=filter_var($share,FILTER_VALIDATE_BOOLEAN);
                $share=($share?1:0);
                $query.="Share=$share,";
            }
            if($print!=-1){
                $print=filter_var($print,FILTER_VALIDATE_BOOLEAN);
                $print=($print?1:0);
                $query.="Print=$print,";
            }
            if($entered!=-1){
                $entered=filter_var($entered,FILTER_VALIDATE_BOOLEAN);
                $entered=($entered?1:0);
                $query.="Entered=$entered,";
            }
            if($qrEnabled!=-1){
                $qrEnabled=filter_var($qrEnabled,FILTER_VALIDATE_BOOLEAN);
                $qrEnabled=($qrEnabled?1:0);
                $query.="qrEnabled=$qrEnabled,";
            }
            $query=substr($query,0,-1);
            $query.=" WHERE HostID=:hostID AND EventID=:eventID AND InvitedUserID=:invitedUserID;";
            $update=$conn->prepare($query);
            print_r($update);
            echo MyPDO::getRowCount($update);
            $update=$conn->prepare($query);
            $update->bindParam(":hostID",$hostID);
            $update->bindParam(":eventID",$eventID);
            $update->bindParam(":invitedUserID",$invitedUserID);
            try {
                $update->execute();
                if(MyPDO::getRowCount($update)==1){
                    ResultManager::showResult(SUCCESSFUL);
                }else{
                    ResultManager::showResult(NOT_SUCCESSFUL);
                }
            }catch (PDOException $e){
                ResultManager::showError(__CLASS__,__LINE__);
                //echo $e->getMessage();
            }
        }
    }
    function setEntered($entered,$hostID,$invitedUserID,$eventID){
        $entered=filter_var($entered,FILTER_VALIDATE_BOOLEAN);
        if($entered){
            $entered=1;
        }
        $conn=MyPDO::getInstance();
        $query="SELECT * FROM InvitedPeople WHERE InvitedUserID=:invitedUserID AND HostID=:hostID AND EventID=:eventID";
        $select=$conn->prepare($query);
        $select->bindParam(":invitedUserID",$invitedUserID);
        $select->bindParam(":hostID",$hostID);
        $select->bindParam(":eventID",$eventID);
        try {
            $select->execute();
            if(MyPDO::getRowCount($select)==1){
                $response=$select->fetch(PDO::FETCH_ASSOC);
                if($response[ENTERED]==1){
                    $res=array(
                        RESULT=>"Already entered",
                        INVITED_PERSON=>$response
                    );
                    echo json_encode($res);
                }elseif ($response[ENTERED]==0){
                    $res=array(
                        RESULT=>"Entered",
                        INVITED_PERSON=>$response
                    );
                    echo json_encode($res);
                    if($entered==1){
                        $query="UPDATE InvitedPeople SET Entered=:entered WHERE InvitedUserID=:invitedUserID AND HostID=:hostID AND EventID=:eventID";
                        $update=$conn->prepare($query);
                        $update->bindParam(":invitedUserID",$invitedUserID);
                        $update->bindParam(":hostID",$hostID);
                        $update->bindParam(":eventID",$eventID);
                        $update->bindParam(":entered",$entered);
                        $update->execute();
                        if(MyPDO::getRowCount($update)==1){
                            $query="UPDATE Events SET Entered=Entered+1 WHERE UserID=:hostID AND EventID=:eventID";
                            $update=$conn->prepare($query);
                            $update->bindParam(":hostID",$hostID);
                            $update->bindParam(":eventID",$eventID);
                            $update->execute();
                        }
                    }
                }
            }else{
                ResultManager::showResult(NOT_SUCCESSFUL);
            }
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }

    }
}