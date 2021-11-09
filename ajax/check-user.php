<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once $assets."/db.php";
include_once $assets."/functions.php";

$public_address = trim($_GET["public_address"]);
$private_key = trim($_GET["private_key"]);

$r  = db_fetch("SELECT * FROM crypto_users WHERE public_address=?",[$public_address]);

$c = count($r);

if ($c===1){
    $user = (object) $r[0];
    $hashed_password = $user->password;
    if ($private_key===$user->private_key){
        $response = [
            "correct_public_address"=>true,
            "correct_private_key"=>true,
            "status"=>"correct"
        ];

    
    }else{

        $response = [
            "correct_public_address"=>true,
            "correct_private_key"=>false,
            "status"=>"wrong"
        ];
    }

}else{

    $response = [
        "correct_public_address"=>false,
        "correct_private_key"=>false,
        "status"=>"wrong"
    ];
}

echo json_encode($response);

exit();
?>