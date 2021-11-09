<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";


$public_address = $_POST["public_address"];
$token = $_POST["token"];
$auth_key = $_POST["auth_key"];

echo http_get_request("https://api.edeposite.info/credit-merchant",[
    "public_address" => $public_address,
    "token" => $token,
    "auth_key" => $auth_key
]);

?>