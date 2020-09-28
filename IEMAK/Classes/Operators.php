<?php
include_once "MyPDO.php";
include_once "ROUTER.php";
include_once "CheckLogin.php";



class Operators{
    public function __construct($system=false)
    {
        if($system){
            return;
        }
        $action=App::get(ACTION);
        $email=App::get(EMAIL);
        switch ($action){
            case REGISTER:
                if (CheckLogin::check()){
                    $password=App::get(PASSWORD);
                    $relatedToUserID=App::get(USER_ID);
                    $name=App::get(NAME);
                    $this->register($name,$email,$password,$relatedToUserID);
                }
                break;
            case GET_ALL:
                if(CheckLogin::check()){
                    $relatedToUserID=App::get(USER_ID);
                    $this->getAllOperators($relatedToUserID);
                }
                break;
            case LOGIN:
                $password=App::get(PASSWORD);
                $this->login($email,$password);
                break;
            case DELETE:
                if(CheckLogin::check()){
                    $relatedToUserID=App::get(USER_ID);
                    $operatorID=App::get(OPERATOR_ID);
                    $this->deleteOperator($relatedToUserID,$operatorID);
                }
                break;
            case EDIT:
                break;
        }
    }
    private function register($name, $email, $password, $relatedToUserID){
        if(empty($email)||empty($password)||empty($relatedToUserID)||!filter_var($email,FILTER_VALIDATE_EMAIL)){
            ResultManager::showError(__CLASS__,__LINE__);
        }

        $longerPass=sha1(microtime().md5(microtime()));
        $session=sha1(microtime().md5(microtime()));

        function getToken($len=32){
            return substr(md5(openssl_random_pseudo_bytes(20)),-$len);
        }

        $token=getToken(10);
        $conn=MyPDO::getInstance();
        $query="INSERT INTO Operators (Name,Email,Password,LongerPass,Session,Token,RelatedToUserID) VALUES (:name,:email,SHA1(CONCAT(:longerPass, :password)),:longerPass,:session,:token,:relatedToUserID);";
        $insert=$conn->prepare($query);
        try {
            $insert->execute(array(
                'name'=>$name,
                'email'=>$email,
                'password'=>$password,
                'token'=>$token,
                'longerPass'=>$longerPass,
                'session'=>$session,
                'relatedToUserID'=>$relatedToUserID
            ));
            /*$to=$email;
            $subject="Email Verification";
            $message="Activate Link: <a href='http://Domain/IEMAK/verification.php?Action=VerifyAsOperator&Email=$email&Token=$token'>Click to register</a>";
            $headers="From: IEMAK@Domain.org";
            $headers .="\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            mail($to,$subject,$message,$headers);*/
            ResultManager::showResult(SUCCESSFUL);
        }catch (Exception $e){
            if($e->getCode()==23000){
                ResultManager::showError(DUPLICATE);
                //echo $e->getMessage();
            }else{
                ResultManager::showError(__CLASS__,__LINE__);
                //echo $e->getMessage();
            }
        }
    }


    private function login($email, $password){
        $conn=MyPDO::getInstance();
        $query="SELECT Session,UserID,Name,Email,EmailConfirmed,Active,RelatedToUserID FROM Operators WHERE Email=:email AND Password=SHA1(CONCAT(LongerPass, :password))";
        $select=$conn->prepare($query);
        try {
            $select->execute(array(
                ":email"=>$email,
                ":password"=>$password
            ));
            if(MyPDO::getRowCount($select)==1){
                $result=$select->fetch();
                if($result[ACTIVE]!=0&&$result[EMAIL_CONFIRMED]!=0){
                    echo json_encode(array(
                        TYPE=>OPERATOR,
                        USER_ID=>$result[USER_ID],
                        SESSION=>$result[SESSION],
                        NAME=>$result[NAME],
                        EMAIL=>$result[EMAIL],
                        RELATED_TO_USER_ID=>$result[RELATED_TO_USER_ID]
                    ));
                }else {
                    echo json_encode(array(
                        ACTIVE=>($result[ACTIVE]==0?false:true),
                        EMAIL_CONFIRMED=>($result[EMAIL_CONFIRMED]==0?false:true)
                    ));
                }
            }
            else{
                echo json_encode(array(RESULT=>false));
            }
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }

    public function checkLogin($userID,$session,$relatedToUserID){
        $conn=MyPDO::getInstance();
        $query="Select * FROM Operators WHERE UserID=:userID AND Session=:session AND RelatedToUserID=:relatedToUserID;";
        $select=$conn->prepare($query);
        try {
            $select->execute(array(
                ":userID"=>$userID,
                ":session"=>$session,
                ":relatedToUserID"=>$relatedToUserID
            ));
            if(MyPDO::getRowCount($select)){
                return true;
            }else{
                return "Logout";
            }
        }catch (PDOException $e){
            return __CLASS__;
        }
    }
    function getAllOperators($userID){
        $conn=MyPDO::getInstance();
        $query="SELECT Name,Email,UserID FROM Operators WHERE RelatedToUserID=:userID";
        $select=$conn->prepare($query);
        $select->bindParam(":userID",$userID);
        try {
            $select->execute();
            $operators=array();
            while ($response=$select->fetch(PDO::FETCH_ASSOC)){
                array_push($operators,$response);
            }
            echo json_encode($operators);
        }catch (PDOException $e){
            ResultManager::showError(__CLASS__,__LINE__);
            //echo $e->getMessage();
        }
    }
    function deleteOperator($userID,$operatorID){
        $conn=MyPDO::getInstance();
        $query="DELETE FROM Operators WHERE RelatedToUserID=:userID AND UserID=:operatorID";
        $delete=$conn->prepare($query);
        $delete->bindParam(":userID",$userID);
        $delete->bindParam(":operatorID",$operatorID);
        try {
            $delete->execute();
            if (MyPDO::getRowCount($delete)==1){
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