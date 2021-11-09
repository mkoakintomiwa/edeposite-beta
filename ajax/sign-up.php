<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";
$response = [
    "error"=>null
];

$email_address = $_GET["email_address"];

$email_token = $_GET["email_token"];
$email_code = $_GET["email_code"];
$recaptcha_token = $_GET["recaptcha_token"];

$country = $_GET["country"];
$phone_number = $_GET["phone_number"];
$account_type = $_GET["account_type"];

$token = random_characters(30);
$code = random_digits(6);

$email_verified = verify_email($email_address, $email_code, $email_token);

if ($email_verified){
    $recaptcha = recaptcha_verify($recaptcha_token);

    if (!isset($recaptcha->error)){
        if ($recaptcha->score >= 0.2){

            switch($account_type){
                case "User":
                    $user_info = [
                        "email"=>$email_address,
                        "country"=>$country,
                        "phone_number"=>$phone_number
                    ];
    
                    if (isset($_GET["referred_by"])){
                        $user_info["referred_by"] = $_GET["referred_by"];
                    }else{
                        $user_info["referred_by"] = null;
                    }

    
                    $user_info_arg = base64_arg($user_info);
                    
                    $response["data"] = json_decode(http_get_request(encoded_url("https://api.edeposite.info/create-user",$user_info)),true);
    
                    $mail = phpmailer();
                    $mail->Subject = "Your public address and private key";
    
                    $message_body = "
                    <!DOCTYPE html>
                    <div>
                        <div style='margin-bottom: 30px; text-align: center;'>
                            <img src='$organization_logo' style='width: 200px; height: 200px' >
                        </div>
                        
                        <p>This message contains your public address and private key, please keep this information secret and safe.</p>
                        
                        <p style='font-weight: 500;'>
                            <div style='font-size: 16x;'>Public Address</div>
                            <div style='font-size: 20px;'>{$response["data"]["public_address"]}</div>
                        </p>
    
    
                        <p style='font-weight: 500;'>
                            <div style='font-size: 16x;'>Private Key</div>
                            <div style='font-size: 20px;'>{$response["data"]["private_key"]}</div>
                        </p>
    
                        <p>Note that this account will be deleted within 24hrs if not activated with a token</p>
    
                        <p>Regards</p>
                        <div>$organization_name</div>
                    </div>
                    ";
    
                    $mail->Body = $message_body;
    
                    $mail->AltBody = innertext($message_body);
                    $mail->addAddress($email_address);
                    $mail->preSend();
    
                    Gmail::send($mail->getSentMIMEMessage());
                break;

                case "Merchant":
                    $public_address = $_GET["public_address"];

                    $response["data"] = json_decode(http_get_request("https://api.edeposite.info/create-merchant/create-merchant",[
                        "public_address"=>$public_address
                    ]),true);
                break;
            }
                


        }else{
            $response["error"] = "BOT_DETECTED";
        }
    }else{
        $response["error"] = "RECAPTCHA_ERROR";
    }
}else{
    $response["error"] = "WRONG_EMAIL_CODE";
}

//echo json_encode(recaptcha_verify($recaptcha_token));

echo json_encode($response);

?>