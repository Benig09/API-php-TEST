<?php
class CheckLogin
{
    public function __construct()
    {

    }
    static function check(){
        $type=App::get(TYPE);
        $userID=App::get(USER_ID);
        $session=App::get(SESSION);
        if($type==OPERATOR){
            $operators=new Operators(true);
            $relatedToUserID=App::get(RELATED_TO_USER_ID);
            if (is_bool($operators->checkLogin($userID,$session,$relatedToUserID))){
                return $operators->checkLogin($userID,$session,$relatedToUserID);
            }else{
                echo $operators->checkLogin($userID,$session,$relatedToUserID);
                return false;
            }
        }elseif ($type==USER){
            $users=new Users(true);
            if(is_bool($users->checkLogin($userID,$session))){
                return $users->checkLogin($userID,$session);
            }else{
                echo $users->checkLogin($userID,$session);
                return false;
            }
        }else{
            echo "Type is incorrect";
            return false;
        }
    }
}