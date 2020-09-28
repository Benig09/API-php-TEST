<?php
include_once "ROUTER.php";
include_once "MyPDO.php";
include_once "ResultManager.php";
include_once "App.php";

$email=App::get(EMAIL);
$token=App::get(TOKEN);
if(empty($email)||empty($token)||!filter_var($email,FILTER_VALIDATE_EMAIL)){
    echo '<span style="color:red;text-align:center;">Link has problem. Try again.</span>';
    exit();
}
$conn=MyPDO::getInstance();
$query="SELECT * FROM Users WHERE Token=:token AND Email=:email;";
$select=$conn->prepare($query);
try {
    $select->execute(array(
        ":token"=>$token,
        ":email"=>$email
    ));
    if(MyPDO::getRowCount($select)==1){
        $user=$select->fetch();
        if($user["EmailConfirmed"]==0){
            $query="UPDATE Users set EmailConfirmed=1 WHERE Token=:token AND Email=:email;";
            $update=$conn->prepare($query);
            $update->execute(array(
                ":token"=>$token,
                ":email"=>$email
            ));
            echo '<span style="color:green;text-align:center;">Your email confirmed</span>';
        }else{
            echo '<span style="color:orange;text-align:center;">Your email is already confirmed</span>';
        }

    }else{
        echo '<span style="color:red;text-align:center;">Link has problem. Try again.</span>';
    }
}catch (PDOException $e){
    echo '<span style="color:red;text-align:center;">Link has problem. Try again.</span>';
}
?>

