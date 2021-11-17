
<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";


$merchant_public_address = $_POST["merchant_public_address"];
$user_public_address = $_POST["user_public_address"];
$token = $_POST["token"];
$auth_key = "";

$transaction_response = http_get_request("https://api.edeposite.info/make-transaction",[
    "merchant_pub" => $merchant_public_address,
    "user_pub" => $user_public_address,
    "token" => $token,
    "auth_key" => $auth_key
]);

echo $transaction_response;

$transaction = json_decode($transaction_response,true);

if ($transaction["panic"] === null){
    $alert = new TransactionEmailAlert($merchant_public_address,$user_public_address);

    $alert->setToken($token)
        ->setDate(date("jS F, Y",$transaction["created_at"]))
        ->setTime(date("g:ia",$transaction["created_at"]))
        ->setTranscationId($transaction["transaction_id"])
        ->send();
}
?>