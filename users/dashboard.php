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

?>

<?php
echo "
<!DOCTYPE !html>
<head>
	<base href='$portal_url/$package_rel_path/'>
	<meta name='theme-color' content='$theme_color'>
	<link rel='shortcut icon' href='$organization_logo'>
</head>
";

echo html_from_template(file_get_contents("$package/wallet.html"));

?>

<style>
	.crypto-sidenav{display:none}.organization-logo{width:100px;height:100px}@media(max-width: 992px){.public-address{max-width:90vw;overflow:auto}.organization-logo{width:70px;height:70px}}.crypto-menu-switches--handle{display:none}
</style>