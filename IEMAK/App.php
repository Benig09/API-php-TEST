<?php
class App{
    //check method value
    public static function get($key){
        if(isset($_REQUEST[$key])){
            return $_REQUEST[$key];
        }
        return "-1";
    }
}