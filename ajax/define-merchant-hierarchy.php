<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";


$public_address = $_POST["public_address"];
$hierarchy = $_POST["hierarchy"];
$auth_key = $_POST["auth_key"];


echo http_get_request("https://api.edeposite.info/define-merchant-hierarchy",[
    "public_address" => $public_address,
    "hierarchy" => $hierarchy,
    "auth_key" => $auth_key
]);

?>