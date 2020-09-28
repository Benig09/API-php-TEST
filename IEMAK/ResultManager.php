<?php

include_once "ROUTER.php";
class ResultManager{

    static function showError($message,$errorCode=-1){
        if($errorCode==-1){
            echo json_encode(
                array(ERROR=>$message));
        }else{
            echo json_encode(
                array(ERROR=>$message,
                    ERROR_CODE=>$errorCode
                ));
        }
        exit();
    }
    static function showResult($message){
        echo json_encode(array(RESULT=>$message));
    }
}