<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";
$response = [
    "error"=>null
];

$recaptcha_token = $_GET["recaptcha_token"];

$account_type = $_GET["account_type"];

$recaptcha = recaptcha_verify($recaptcha_token);

if (!isset($recaptcha->error)){
    if ($recaptcha->score >= 0.8){
        
        $public_address = $_GET["public_address"];

        $merchant_info_arg = base64_arg([
            "pub"=>$public_address
        ]);

        $response["data"] = json_decode(http_get_request("https://api.edeposite.info/create-merchant",[
            "pub"=>$public_address
        ]),true);

    }else{
        $response["error"] = "BOT_DETECTED";
    }
}else{
    $response["error"] = "RECAPTCHA_ERROR";
}

//echo json_encode(recaptcha_verify($recaptcha_token));

echo json_encode($response);

?>