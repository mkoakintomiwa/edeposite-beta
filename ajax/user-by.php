<?php
include_once $_SERVER['DOCUMENT_ROOT']."/settings.php";
include_once $assets."/db.php";
include_once $assets."/variables.php";
include_once $assets."/functions.php";


$context = $_GET["context"];
$value = $_GET["value"];

switch($context){
    case "username":
        $_user_ = db_fetch("SELECT * FROM users WHERE username LIKE ?",[$value]);
    break;

    case "staff_id":
        $_user_ = db_fetch("SELECT * FROM users WHERE staff_id = ?",[$value]);
    break;
}



$response = [];

if (count($_user_)>0){

    $_user = $_user_[0];

    $response += std_array(user($_user["uid"]));

    $response += [
        "error"=>null
    ];
}else{
    $response = [
        "error"=>strtoupper($context)."_NOT_VALID"
    ];
}

echo json_encode($response);
?>