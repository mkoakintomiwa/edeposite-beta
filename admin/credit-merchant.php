<?php
/** Find document_root for web and cli */
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once "$document_root/settings.php";
$title = "Credit Merchant";
define("PUBLIC",true);
include_once "$assets/universal.php";
echo $universal->stdOut;

?>

<style></style>

<?php

$html_template = <<<EOF
<form id="credit-merchant-form" style="padding:50px" class="card  number-format-container">
    <h3 class="text-center mb-4">Credit Merchant</h3>
    <div class="input-field">
        <label>Merchant Public Address</label>
        <textarea name="public_address" class="materialize-textarea"></textarea>
    </div>


    <div class="input-field">
        <label>Token Amount</label>
        <input name="token" type="text" class="number-format">
    </div>


    <div class="input-field">
        <label>Auth Key</label>
        <textarea name="auth_key" class="materialize-textarea"></textarea>
    </div>

    <div class="centralize">
        <button id="ays-submit" class="btn btn-primary">SUBMIT</button>
    </div>
</form>
EOF;

echo "
<body>
    <div class='not-navbar grid-1-2-1'>
        <div class='content-left'></div>
        
        <div class='content-center'>
            ".html_from_template($html_template)."
        </div>

        <div class='content-right'></div>
    </div>
</body>
";
?>

<script>
	$("#ays-submit").on("click",function(e){e.preventDefault();Sp();var formdata=new FormData($("#credit-merchant-form").toHTMLFormElement());var public_address=formdata.get("public_address").toString().replace(/\s+/,'');formdata.set("public_address",public_address);var token=formdata.get("token").toString();_user(public_address).then((function(_user_){if(!_user_.panic){SpClose();ays('Credit Merchant',"\n\n            <div style=\'padding: 40px; padding-top: 0\'>\n                <table>\n                    <tr>\n                        <td>Public Address</td>\n                        <td>".concat(_user_.public_address,"</td>\n                    </tr>\n\n                    <tr>\n                        <td>Email Address</td>\n                        <td>").concat(_user_.email_address,"</td>\n                    </tr>\n\n\n                    <tr>\n                        <td>Phone Number</td>\n                        <td>").concat(_user_.phone_number,"</td>\n                    </tr>\n\n\n                    <tr>\n                        <td>Country</td>\n                        <td>").concat(_user_.country,"</td>\n                    </tr>\n\n\n                    <tr>\n                        <td>Token Balance</td>\n                        <td>").concat(numberFormat(_user_.merchant.token.toString()),"</td>\n                    </tr>\n\n                    <tr>\n                        <td>Token paid</td>\n                        <td>").concat(numberFormat(token),"</td>\n                    </tr>\n                </table>\n            </div>\n            "),this,function(){Sp();$.ajax({url:"".concat(ajax,"/credit-merchant.php"),method:"POST",data:formdata,contentType:false,processData:false,success:function(d){var response=jsonResponse(d);if(!response.panic){escapeOverlay();$("#credit-merchant-form").clearForm();Swal.fire({icon:"success",html:"Merchant successfully credited"});}else{Swal.fire({icon:"error",html:response.panic});}}});});}else{Swal.fire({icon:"error",html:_user_.panic});}}).bind(this));});
</script>