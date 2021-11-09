<?php
/** Find document_root for web and cli */
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once "$document_root/settings.php";
$title = "ICO Registration";
$theme_color = "#84c560";
define("PUBLIC",true);
include_once "$assets/universal.php";

$countries_select = html_selected("South Africa","name='country'",array_multiply($countries_in_world),true);

echo $universal->stdOut;

?>

<style>
	html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,dl,dt,dd,ol,nav ul,nav li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}ol,ul{list-style:none;margin:0px;padding:0px}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:"";content:none}table{border-collapse:collapse;border-spacing:0}a{text-decoration:none}.txt-rt{text-align:right}.txt-lt{text-align:left}.txt-center{text-align:center}.float-rt{float:right}.float-lt{float:left}.clear{clear:both}.pos-relative{position:relative}.pos-absolute{position:absolute}.vertical-base{vertical-align:baseline}.vertical-top{vertical-align:top}nav.vertical ul li{display:block}nav.horizontal ul li{display:inline-block}img{max-width:100%}body{background:#76b852;background:-webkit-linear-gradient(to top, #76b852, #8DC26F);background:-moz-linear-gradient(to top, #76b852, #8DC26F);background:-o-linear-gradient(to top, #76b852, #8DC26F);background:linear-gradient(to top, #76b852, #8DC26F);background-size:cover;background-attachment:fixed;font-family:"Roboto",sans-serif}h1{font-size:3em;text-align:center;color:#fff;font-weight:100;text-transform:capitalize;letter-spacing:4px;font-family:"Roboto",sans-serif}.main-w3layouts{padding:3em 0 1em}.main-agileinfo{width:35%;margin:3em auto;background:rgba(0,0,0,.18);background-size:cover}.agileits-top{padding:3em}input[type=text],input[type=email],input[type=password]{font-size:.9em;color:#fff;font-weight:100;width:94.5%;display:block;border:none;padding:.8em;border:solid 1px rgba(255,255,255,.37);-webkit-transition:all .3s cubic-bezier(0.64, 0.09, 0.08, 1);transition:all .3s cubic-bezier(0.64, 0.09, 0.08, 1);background:-webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, #fff 4%);background:linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, #fff 4%);background-position:-800px 0;background-size:100%;background-repeat:no-repeat;color:#fff;font-family:"Roboto",sans-serif}input.email,input.text.w3lpass{margin:2em 0}.text:focus,.text:valid{box-shadow:none;outline:none;background-position:0 0}.text:focus::-webkit-input-placeholder,.text:valid::-webkit-input-placeholder{color:rgba(255,255,255,.7);font-size:.9em;-webkit-transform:translateY(-30px);-moz-transform:translateY(-30px);-o-transform:translateY(-30px);-ms-transform:translateY(-30px);transform:translateY(-30px);visibility:visible !important}::-webkit-input-placeholder{color:#fff;font-weight:100}:-moz-placeholder{color:#fff}::-moz-placeholder{color:#fff}:-ms-input-placeholder{color:#fff}input[type=submit]{font-size:.9em;color:#fff;background:#76b852;outline:none;border:1px solid #76b852;cursor:pointer;padding:.9em;-webkit-appearance:none;width:100%;margin:2em 0;letter-spacing:4px}input[type=submit]:hover{-webkit-transition:.5s all;-moz-transition:.5s all;-o-transition:.5s all;-ms-transition:.5s all;transition:.5s all;background:#8dc26f}.agileits-top p{font-size:1em;color:#fff;text-align:center;letter-spacing:1px;font-weight:300}.agileits-top p a{color:#fff;-webkit-transition:.5s all;-moz-transition:.5s all;transition:.5s all;font-weight:400}.agileits-top p a:hover{color:#76b852}.wthree-text label{font-size:.9em;color:#fff;font-weight:200;cursor:pointer;position:relative}input.checkbox{background:#8dc26f;cursor:pointer;width:1.2em;height:1.2em}input.checkbox:before{content:"";position:absolute;width:1.2em;height:1.2em;background:inherit;cursor:pointer}input.checkbox:after{content:"";position:absolute;top:0px;left:0;z-index:1;width:1.2em;height:1.2em;border:1px solid #fff;-webkit-transition:.4s ease-in-out;-moz-transition:.4s ease-in-out;-o-transition:.4s ease-in-out;transition:.4s ease-in-out}input.checkbox:checked:after{-webkit-transform:rotate(-45deg);-moz-transform:rotate(-45deg);-o-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate(-45deg);height:.5rem;border-color:#fff;border-top-color:transparent;border-right-color:transparent}.anim input.checkbox:checked:after{-webkit-transform:rotate(-45deg);-moz-transform:rotate(-45deg);-o-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate(-45deg);height:.5rem;border-color:transparent;border-right-color:transparent;animation:.4s rippling .4s ease;animation-fill-mode:forwards}@keyframes rippling{50%{border-left-color:#fff}100%{border-bottom-color:#fff;border-left-color:#fff}}.colorlibcopy-agile{margin:2em 0 1em;text-align:center}.colorlibcopy-agile p{font-size:.9em;color:#fff;line-height:1.8em;letter-spacing:1px;font-weight:100}.colorlibcopy-agile p a{color:#fff;transition:.5s all;-webkit-transition:.5s all;-moz-transition:.5s all;-o-transition:.5s all;-ms-transition:.5s all}.colorlibcopy-agile p a:hover{color:#000}.wrapper{position:relative;overflow:hidden}.colorlib-bubbles{position:absolute;top:0;left:0;width:100%;height:100%;z-index:-1}.colorlib-bubbles li{position:absolute;list-style:none;display:block;width:40px;height:40px;background-color:rgba(255,255,255,.15);bottom:-160px;-webkit-animation:square 20s infinite;-moz-animation:square 250s infinite;-o-animation:square 20s infinite;-ms-animation:square 20s infinite;animation:square 20s infinite;-webkit-transition-timing-function:linear;-moz-transition-timing-function:linear;-o-transition-timing-function:linear;-ms-transition-timing-function:linear;transition-timing-function:linear;-webkit-border-radius:50%;-moz-border-radius:50%;-o-border-radius:50%;-ms-border-radius:50%;border-radius:50%}.colorlib-bubbles li:nth-child(1){left:10%}.colorlib-bubbles li:nth-child(2){left:20%;width:80px;height:80px;-webkit-animation-delay:2s;-moz-animation-delay:2s;-o-animation-delay:2s;-ms-animation-delay:2s;animation-delay:2s;-webkit-animation-duration:17s;-moz-animation-duration:17s;-o-animation-duration:17s;animation-duration:17s}.colorlib-bubbles li:nth-child(3){left:25%;-webkit-animation-delay:4s;-moz-animation-delay:4s;-o-animation-delay:4s;-ms-animation-delay:4s;animation-delay:4s}.colorlib-bubbles li:nth-child(4){left:40%;width:60px;height:60px;-webkit-animation-duration:22s;-moz-animation-duration:22s;-o-animation-duration:22s;-ms-animation-duration:22s;animation-duration:22s;background-color:rgba(255,255,255,.25)}.colorlib-bubbles li:nth-child(5){left:70%}.colorlib-bubbles li:nth-child(6){left:80%;width:120px;height:120px;-webkit-animation-delay:3s;-moz-animation-delay:3s;-o-animation-delay:3s;-ms-animation-delay:3s;animation-delay:3s;background-color:rgba(255,255,255,.2)}.colorlib-bubbles li:nth-child(7){left:32%;width:160px;height:160px;-webkit-animation-delay:7s;-moz-animation-delay:7s;-o-animation-delay:7s;-ms-animation-delay:7s;animation-delay:7s}.colorlib-bubbles li:nth-child(8){left:55%;width:20px;height:20px;-webkit-animation-delay:15s;-moz-animation-delay:15s;animation-delay:15s;-webkit-animation-duration:40s;-moz-animation-duration:40s;animation-duration:40s}.colorlib-bubbles li:nth-child(9){left:25%;width:10px;height:10px;-webkit-animation-delay:2s;animation-delay:2s;-webkit-animation-duration:40s;animation-duration:40s;background-color:rgba(255,255,255,.3)}.colorlib-bubbles li:nth-child(10){left:90%;width:160px;height:160px;-webkit-animation-delay:11s;animation-delay:11s}@-webkit-keyframes square{0%{-webkit-transform:translateY(0);-moz-transform:translateY(0);-o-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}100%{-webkit-transform:translateY(-700px) rotate(600deg);-moz-transform:translateY(-700px) rotate(600deg);-o-transform:translateY(-700px) rotate(600deg);-ms-transform:translateY(-700px) rotate(600deg);transform:translateY(-700px) rotate(600deg)}}@keyframes square{0%{-webkit-transform:translateY(0);-moz-transform:translateY(0);-o-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}100%{-webkit-transform:translateY(-700px) rotate(600deg);-moz-transform:translateY(-700px) rotate(600deg);-o-transform:translateY(-700px) rotate(600deg);-ms-transform:translateY(-700px) rotate(600deg);transform:translateY(-700px) rotate(600deg)}}@media(max-width: 1440px){input[type=text],input[type=email],input[type=password]{width:94%}}@media(max-width: 1366px){h1{font-size:2.6em}.agileits-top{padding:2.5em}.main-agileinfo{margin:2em auto}.main-agileinfo{width:36%}}@media(max-width: 1280px){.main-agileinfo{width:40%}}@media(max-width: 1080px){.main-agileinfo{width:46%}}@media(max-width: 1024px){.main-agileinfo{width:49%}}@media(max-width: 991px){h1{font-size:2.4em}.main-w3layouts{padding:2em 0 1em}}@media(max-width: 900px){.main-agileinfo{width:58%}input[type=text],input[type=email],input[type=password]{width:93%}}@media(max-width: 800px){h1{font-size:2.2em}}@media(max-width: 736px){.main-agileinfo{width:62%}}@media(max-width: 667px){.main-agileinfo{width:67%}}@media(max-width: 600px){.agileits-top{padding:2.2em}input.email,input.text.w3lpass{margin:1.5em 0}input[type=submit]{margin:2em 0}h1{font-size:2em;letter-spacing:3px}}@media(max-width: 568px){.main-agileinfo{width:75%}.colorlibcopy-agile p{padding:0 2em}}@media(max-width: 480px){h1{font-size:1.8em;letter-spacing:3px}.agileits-top{padding:1.8em}input[type=text],input[type=email],input[type=password]{width:91%}.agileits-top p{font-size:.9em}}@media(max-width: 414px){h1{font-size:1.8em;letter-spacing:2px}.main-agileinfo{width:85%;margin:1.5em auto}.text:focus,.text:valid{background-position:0 0px}.wthree-text ul li,.wthree-text ul li:nth-child(2){display:block;float:none}.wthree-text ul li:nth-child(2){margin-top:1.5em}input[type=submit]{margin:2em 0 1.5em;letter-spacing:3px}input[type=submit]{margin:2em 0 1.5em}.colorlibcopy-agile{margin:1em 0 1em}}@media(max-width: 384px){.main-agileinfo{width:88%}.colorlibcopy-agile p{padding:0 1em}}@media(max-width: 375px){.agileits-top p{letter-spacing:0px}}@media(max-width: 320px){.main-w3layouts{padding:1.5em 0 0}.agileits-top{padding:1.2em}.colorlibcopy-agile{margin:0 0 1em}input[type=text],input[type=email],input[type=password]{width:89.5%;font-size:.85em}h1{font-size:1.7em;letter-spacing:0px}.main-agileinfo{width:92%;margin:1em auto}.text:focus,.text:valid{background-position:0 0px}input[type=submit]{margin:1.5em 0;padding:.8em;font-size:.85em}.colorlibcopy-agile p{font-size:.85em}.wthree-text label{font-size:.85em}.main-w3layouts{padding:1em 0 0}}label{color:#fff !important;margin-bottom:0px}select{margin-top:10px !important}input,textarea{margin-bottom:40px !important;width:100% !important;border-bottom-color:#fff !important;color:#fff;background-color:transparent}#signup-button{margin-top:0px !important;border:1px solid #fff !important}.select-wrapper .caret{fill:#fff !important}.verify-email input{color:#000 !important;letter-spacing:10px;font-size:25px;text-align:center;border-bottom-color:teal !important;margin-bottom:10px !important}
</style>

<?php

$html_template = <<<EOF
<!--
Author: Colorlib
Author URL: https://colorlib.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="{{ portal_url }}/assets/images/edeposite-logo.png">
	<meta name="theme-color" content="#84c560">
	<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
	<!-- //Custom Theme files -->
	<!-- web font -->
	<link href="//fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,700,700i" rel="stylesheet">

	<!-- //web font -->
</head>

<body>
	<!-- main -->
	<div class="main-w3layouts wrapper">
		<div style="display: flex; justify-content:center; align-items: center; flex-direction:column;">
			<div>
				<a href="{{ portal_url }}">
					<img src="{{ organization_logo }}" style="width: 200px; height: 200px" >
				</a>
			</div>
			<h3 style="color: white; font-size: 30px;">Sign Up</h3>
		</div>

		<div class="main-agileinfo">
			<div class="agileits-top">
				<form id="ico-registration-form">
					
					<div class="user-form">

						<div class="input-field">
							<label>Email</label>
							<textarea class="text email materialize-textarea" type="email" name="email_address" ></textarea>
						</div>


						<div class="input-field">
							<label>Phone Number</label>
							<input class="text" type="text" name="phone_number" >
						</div>

						
						<div>
							<label>Country</label>
							{{ countries_select|raw }}
						</div>
					</div>


					<div class="merchant-form" style="display: none;">
						<div class="input-field">
							<label>Public Address</label>
							<textarea type="text" name="public_address" class="materialize-textarea"></textarea>
						</div>

					</div>

					<div>
						<label>Account Type</label>
						<select id="account-type" name="account_type">
							<option value="User">User</option>
							<option value="Merchant">Merchant</option>
						</select>
					</div>

					<script>
						$("select").formSelect();
					</script>

					<input id="signup-button" type="submit" value="SIGNUP">
				</form>
				
			</div>
		</div>
		<!-- copyright -->
		<div class="colorlibcopy-agile">
			<p>© 2020 {{ organization_name }}. All rights reserved</p>
		</div>
		<!-- //copyright -->
		<ul class="colorlib-bubbles">
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
		</ul>
	</div>
	<!-- //main -->
</body>
</html>
EOF;

echo html_from_template($html_template);
?>

<script>
	$("#account-type").on("change",function(){var $this=$(this);switch($this.val().toString()){case "User":$(".user-form").show();$(".merchant-form").hide();break;case "Merchant":$(".user-form").hide();$(".merchant-form").show();break;}});$("#signup-button").on("click",function(e){e.preventDefault();Sp();var formdata=$("#ico-registration-form").formJSON();var account_type=formdata["account_type"];switch(account_type){case "User":$.ajax({url:"".concat(ajax,"/send-email-verification.php"),data:formdata,success:function(d){var response=jsonResponse(d);var email_token=response.token;if(!response.error){Swal.fire({title:'Verify email address',html:"\n                            <div>\n                                <div class=\'verify-email\' style=\'margin-top:20px;\'>\n                                    <small style=\'text-align: center; font-size: 12px;\'>We sent you a verification code, please check your email</small>\n                                    <input id=\'email-code\' inputmode=\'numeric\'>\n                                </div>\n                                <div class=\'centralize\' style=\'margin-top:20px; margin-bottom: 10px;\'>\n                                    <button id=\'submit-request\' class=\'btn btn-primary\'>SUBMIT</button>\n                                </div>\n                                <div class=\'request-error\' style=\'color: red; font-size: 13px;\'></div>\n                            </div>\n                            ",showConfirmButton:false});$("#submit-request").on("click",function(e1){e1.preventDefault();$("#submit-request").addClass("fade");var extras={email_token:email_token};if(queryString("ref")){extras=Object.assign({referred_by:queryString("ref")},extras);}grecaptcha.ready(function(){grecaptcha.execute('6LeLp-cZAAAAADI1My5i20DwcBTd5zDRZhS7i_gd',{action:'submit'}).then(function(recaptcha_token){$.ajax({url:"".concat(ajax,"/sign-up.php"),data:Object.assign(formdata,extras,{email_code:$("#email-code").val().toString().trim(),recaptcha_token:recaptcha_token}),success:function(d1){$(".request-error").html("");$("#submit-request").removeClass("fade");var response1=jsonResponse(d1);console.log(response1);if(!response1.error){Swal.fire({icon:"success",html:"\n                                                    <div>\n                                                        <div>Congratulation! You successfully register as a user. We have sent your public address and private key to your email address.</div>\n                                                    </div>\n                                                    "});}else{var request_error;switch(response1.error){case "WRONG_EMAIL_CODE":request_error="Wrong verification code, check and try again!";break;case "RECAPTCHA_ERROR":request_error="We couldn't verify if you are an human or a robot, please try again";break;case "BOT_DETECTED":request_error="You must be a robot, if not please contact the System Administrators";break;}$(".request-error").html(request_error);}$("#ico-registration-form").clearForm();$("[name=\"account_type\"]").val("User");$("[name=\"account_type\"]").formSelect();}});});});});}}});break;case "Merchant":grecaptcha.ready(function(){grecaptcha.execute('6LeLp-cZAAAAADI1My5i20DwcBTd5zDRZhS7i_gd',{action:'submit'}).then(function(recaptcha_token){var public_address=formdata["public_address"];$.ajax({url:"".concat(ajax,"/merchant-sign-up.php"),data:Object.assign(formdata,{recaptcha_token:recaptcha_token}),success:function(d){$(".request-error").html("");$("#submit-request").removeClass("fade");var response=jsonResponse(d);if(!response.error){var data=response.data;switch(data.panic){case "Account creation successfully for testing mode only":Swal.fire({icon:"success",html:"\n                                            <div>\n                                                <div>Congratulation! You successfully register as a merchant with the public address <b>".concat(public_address,"</b></div>\n                                            </div>\n                                            ")});break;case "Merchant already exist":Swal.fire({icon:"error",html:"\n                                            <div>\n                                                <div>Merchant of public address <b>".concat(public_address,"</b> already exist</div>\n                                            </div>\n                                            ")});break;case "public address does not exist":Swal.fire({icon:"error",html:"\n                                            <div>\n                                                <div>Public address <b>".concat(public_address,"</b> does not exist</div>\n                                            </div>\n                                            ")});break;default:Swal.fire({icon:"error",html:"\n                                            <div>\n                                                <div>An error occurred, please try again</div>\n                                            </div>\n                                            "});break;}}else{var request_error;switch(response.error){case "WRONG_EMAIL_CODE":request_error="Wrong verification code, check and try again!";break;case "RECAPTCHA_ERROR":request_error="We couldn't verify if you are an human or a robot, please try again";break;case "BOT_DETECTED":request_error="You must be a robot, if not please contact the System Administrators";break;}$(".request-error").html(request_error);}$("#ico-registration-form").clearForm();$("[name=\"account_type\"]").val("Merchant");$("[name=\"account_type\"]").formSelect();}});});});break;}});
</script>