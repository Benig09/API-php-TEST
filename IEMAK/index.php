<?php

include_once "ROUTER.php";
include_once "Classes/Users.php";
include_once "App.php";
include_once "Classes/Cities.php";
include_once "Classes/Events.php";
include_once "Classes/InvitedPeople.php";
include_once "Classes/Favorites.php";
include_once "Classes/Operators.php";
include_once "CheckLogin.php";
include_once "Classes/Counter.php";
include_once "Classes/Templates.php";


$route=App::get(ROUTE);
$type=App::get(TYPE);
switch ($route){
    case USERS:
        $users=new Users();
        break;
    case OPERATORS:
        $users=new Operators();
        break;
    case CITIES:
        $cities=new Cities($type);
        break;
    case EVENTS:
        if(CheckLogin::check()){
            $events=new Events($type);
        }
        break;
    case COUNTER:
        if(CheckLogin::check()){
            $events=new Counter($type);
        }
        break;
    case INVITED_PEOPLE:
        if (CheckLogin::check()){
            $invitedPeople=new InvitedPeople($type);
        }
        break;
    case TEMPLATES:
        if (CheckLogin::check()){
            $invitedPeople=new Templates($type);
        }
        break;
    case FAVORITES:
        if(CheckLogin::check()){
            $favorites=new Favorites($type);
        }
        break;
    default:
        ResultManager::showError("There is no valid route",__LINE__);
}
