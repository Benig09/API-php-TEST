<?php

include_once "ROUTER.php";
include_once "MyPDO.php";


class Users
{
    public function __construct($system=false)
    {
        if($system){
            return;
        }
        $action=App::get(ACTION);
        $email=App::get(EMAIL);
        switch ($action){
            case REGISTER:
                $password=App::get(PASSWORD);
                $name=App::get(NAME);
                $phoneNumber=App::get(PHONE_NUMBER);
                $city=App::get(CITY_NAME);
                $this->register($name,$email,$phoneNumber,$password,$city);
                break;
            case LOGIN:
                $password=App::get(PASSWORD);
                $this->login($email,$password);
                break;

        }
    }
    private function register($name, $email, $phoneNumber, $password, $city){
        if(empty($email)||empty($password)||empty($phoneNumber)||empty($city)||!filter_var($email,FILTER_VALIDATE_EMAIL)){
            ResultManager::showError(__CLASS__,__LINE__);
        }

        $longerPass=sha1(microtime().md5(microtime()));
        $session=sha1(microtime().md5(microtime()));

        function getToken($len=32){
            return substr(md5(openssl_random_pseudo_bytes(20)),-$len);
        }

        $token=getToken(10);
        $conn=MyPDO::getInstance();
        $query="INSERT INTO Users (Name,Email,Password,LongerPass,Session,Token,PhoneNumber,CityName) VALUES (:name,:email,SHA1(CONCAT(:longerPass, :password)),:longerPass,:session,:token,:phoneNumber,:city);";
        $insert=$conn->prepare($query);
        try {
            $insert->execute(array(
                'name'=>$name,
                'email'=>$email,
                'password'=>$password,
                'token'=>$token,
                'longerPass'=>$longerPass,
                'session'=>$session,
                'phoneNumber'=>$phoneNumber,
                'city'=>$city
            ));
            /*$to=$email;
            $subject="Email Verification";
            $message="Activate Link: <a href='http://Domain/IEMAK/verification.php?Action=VerifyAsUser&Email=$email&Token=$token'>Click to register</a>";
            $headers="From: IEMAK@Domain.org";
            $headers .="\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            mail($to,$subject,$message,$headers);*/
            ResultManager::showResult(SUCCESSFUL);
        }catch (Exception $e){
            if($e->getCode()==23000&&strpos($e->getMessage(),"Duplicate")){
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
        $query="SELECT Session,UserID,PhoneNumber,CityName,Name,Email,EmailConfirmed,Active FROM Users WHERE Email=:email AND Password=SHA1(CONCAT(LongerPass, :password))";
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
                        USER_ID=>$result[USER_ID],
                        SESSION=>$result[SESSION],
                        CITY_NAME=>$result[CITY_NAME],
                        PHONE_NUMBER=>$result[PHONE_NUMBER],
                        EMAIL=>$result[EMAIL],
                        NAME=>$result[NAME]
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

    public function checkLogin($userID,$session){
        $conn=MyPDO::getInstance();
        $query="Select * FROM Users WHERE UserID=:userID AND Session=:session;";
        $select=$conn->prepare($query);
        try {
            $select->execute(array(
                ":userID"=>$userID,
                ":session"=>$session
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


}