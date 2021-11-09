<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";

$email_address = $_GET["email_address"];

$response = [];

$token = random_characters(30);
$code = random_digits(6);

$mail = phpmailer();
$mail->Subject = "Verification code";

$message_body = "
<!DOCTYPE html>
<div>
    <div style='margin-bottom: 30px; text-align: center;'>
        <img src='$organization_logo' style='width: 200px; height: 200px' >
    </div>
    
    <p>Hello,</p>
    
    <p>Thank you for signing up for $organization_name</p>

    <p>Your 6-digit verification code is:</p>

    <p style='font-size: 25px; letter-spacing: 3px; text-align:center;'>$code</p>

    <p>Enter this verification code on the sign up page where you requested the code. This code is valid for 30 minutes.</p>

    <p>Welcome and thank you</p>
    <div>$organization_name</div>
</div>
";

$mail->Body = $message_body;

$mail->AltBody = innertext($message_body);
$mail->addAddress($email_address);
$mail->preSend();

Gmail::send($mail->getSentMIMEMessage());

row_action([
    "table_name"=>"email_verification",
    "columns"=>[
        "token"=>$token,
        "code"=>$code,
        "email"=>$email_address,
        "time"=>time()
    ]
])->insert();


echo json_encode([
    "token"=>$token,
    "error"=>null
]);

?>