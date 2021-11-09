
<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";

var_dump($_POST);

$merchant_public_address = $_POST["merchant_public_address"];
$user_public_address = $_POST["user_public_address"];
$token = $_POST["token"];
$auth_key = "";


echo http_get_request("https://api.edeposite.info/make-transaction-to-merchant",[
    "merchant_public_address" => $merchant_public_address,
    "user_public_address" => $user_public_address,
    "token" => $token,
    "auth_key" => $auth_key
]);

?>