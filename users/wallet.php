<?php
/** Find document_root for web and cli */
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once "$document_root/settings.php";
$theme_color = "#3d454c";
include_once "$assets/functions.php";
include_once "$assets/universal.php";

$package_rel_path = "assets/cryptum-html";
$package = "$document_root/$package_rel_path";

echo "
<!DOCTYPE !html>
<head>
	<base href='$portal_url/$package_rel_path/'>
	<meta name='theme-color' content='$theme_color'>
	<link rel='shortcut icon' href='$organization_logo'>
	<script
        src='https://code.jquery.com/jquery-3.5.1.min.js'
        integrity='sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0='
        crossorigin='anonymous'></script>
	$universal->head_script

	$notice_modals
</head>
";
?>

<style>
	.crypto-sidenav{display:none}.organization-logo{width:100px;height:100px}@media(max-width: 992px){.public-address{max-width:90vw;overflow:auto}.organization-logo{width:70px;height:70px}}.crypto-menu-switches--handle{display:none}#notice{background-color:rgba(0,0,0,.3);width:100vw;height:100vh;position:fixed;top:0;display:flex;justify-content:center;align-items:center;z-index:3;opacity:0}#notice-content{background-color:#fff;padding:10px;border-radius:10px;min-width:250px;width:max-content;max-width:90vw}#notice-header{font-size:90%;padding:10px 0vw;font-weight:800;text-align:center}.notice-body-prompt div{cursor:pointer;border-top:1px solid #f0f0f0;padding:10px 0px;text-align:center}.dismiss{color:red}.notice-body-prompt div:hover{font-weight:700}.show-notice{opacity:1 !important}.hide-notice{z-index:-2 !important;opacity:0 !important}#notice-head{text-align:center}#notice-body{min-width:300px}@media(min-width: 768px){#notice-body{max-width:75vw}}@media(max-width: 768px){#notice-body{max-width:90vw}}.dismiss-notice{cursor:pointer}.notice-padding{padding:5px 20px}.crypro-theme-gradient{background-image:none !important}.swal2-header{font-size:11px !important}
</style>

<?php

echo html_from_template(file_get_contents("$package/wallet.html"));

?>

<script>
	$("#send-token").on("click",function(e){e.preventDefault();Sp();var form=$("#send-token-form").toHTMLFormElement();var formdata=new FormData(form);formdata.append("merchant_public_address",user.public_address);var user_public_address=formdata.get("user_public_address").toString();var token=formdata.get("token").toString();_user(user_public_address).then((function(_user_){SpClose();ays('Send token to user',"\n\n        <div style=\'padding: 40px; padding-top: 0\'>\n            <table class=\'table\'>\n                <tr>\n                    <td>Public Address</td>\n                    <td>".concat(_user_.public_address,"</td>\n                </tr>\n\n                <tr>\n                    <td>Email Address</td>\n                    <td>").concat(_user_.email_address,"</td>\n                </tr>\n\n\n                <tr>\n                    <td>Phone Number</td>\n                    <td>").concat(_user_.phone_number,"</td>\n                </tr>\n\n\n                <tr>\n                    <td>Country</td>\n                    <td>").concat(_user_.country,"</td>\n                </tr>\n\n                <tr>\n                    <td>Token paid</td>\n                    <td>").concat(numberFormat(token),"</td>\n                </tr>\n            </table>\n        </div>\n        "),this,function(){Sp();$.ajax({url:"".concat(ajax,"/make-transaction.php"),method:"POST",data:formdata,contentType:false,processData:false,success:function(d){var response=jsonResponse(d);if(!response.panic){escapeOverlay();Swal.fire({icon:"success",html:"You have successfully transfered ".concat(token," token to ").concat(user_public_address)});window.location.reload();}else{Swal.fire({icon:"error",html:response.panic});}}});});}).bind(this));});$("#send-token-to-merchant").on("click",function(e){e.preventDefault();Sp();var form=$("#send-token-to-merchant-form").toHTMLFormElement();var formdata=new FormData(form);formdata.append("merchant_public_address",user.public_address);var user_public_address=formdata.get("user_public_address").toString();var token=formdata.get("token").toString();_user(user.public_address).then((function(_user_){user=_user_;_user(user_public_address).then((function(_user_1){SpClose();if(hierarchy_position(user.merchant.hierarchy)<hierarchy_position(_user_1.merchant.hierarchy)){ays('Send token to Merchant',"\n\n                <div style=\'padding: 40px; padding-top: 0\'>\n                    <table class=\'table\'>\n                        <tr>\n                            <td>Public Address</td>\n                            <td>".concat(_user_1.public_address,"</td>\n                        </tr>\n\n                        <tr>\n                            <td>Email Address</td>\n                            <td>").concat(_user_1.email_address,"</td>\n                        </tr>\n\n\n                        <tr>\n                            <td>Phone Number</td>\n                            <td>").concat(_user_1.phone_number,"</td>\n                        </tr>\n\n\n                        <tr>\n                            <td>Country</td>\n                            <td>").concat(_user_1.country,"</td>\n                        </tr>\n\n                        <tr>\n                            <td>Token paid</td>\n                            <td>").concat(numberFormat(token),"</td>\n                        </tr>\n                    </table>\n                </div>\n                "),this,function(){Sp();$.ajax({url:"".concat(ajax,"/make-transaction-to-merchant.php"),method:"POST",data:formdata,contentType:false,processData:false,success:function(d){var response=jsonResponse(d);console.log(d);if(!response.panic){escapeOverlay();Swal.fire({icon:"success",html:"You have successfully transfered ".concat(token," token to ").concat(user_public_address)});window.location.reload();}else{Swal.fire({icon:"error",html:response.panic});}}});});}else{Swal.fire({icon:"error",html:"Your transaction cannot be completed because your hierarchy is not higher"});}}).bind(this));}).bind(this));});function hierarchy_position(hierarchy_index){var _hierarchy_position;if(hierarchy_index===""){_hierarchy_position=Infinity;}else{_hierarchy_position=hierarchies[hierarchy_index]["position"];}return _hierarchy_position;}
</script>