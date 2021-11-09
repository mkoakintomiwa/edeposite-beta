<?php
@session_start();
$document_root = __DIR__;while(true){if (file_exists($document_root."/settings.json")){break;}else{$document_root=dirname($document_root);}}
include_once $document_root."/settings.php";
include_once "db.php";
include_once "variables.php";
include_once "functions.php";


if (!isset($_SESSION["public_address"]) && !(defined("IS_LOGIN_PAGE") && constant("IS_LOGIN_PAGE")) && !(defined("PUBLIC") && constant("PUBLIC"))){
	log_out();
}else{
    $user = user();
}


$quill = "$rel_dirname/assets/quill";

$links = "
<link rel='shortcut icon' href='$organization_logo'/>

<script
  src='https://code.jquery.com/jquery-3.5.1.min.js'
  integrity='sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0='
  crossorigin='anonymous'></script>

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css'>

<script src='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js'></script>

<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' integrity='sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm' crossorigin='anonymous'>

<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' integrity='sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q' crossorigin='anonymous'></script>

<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js' integrity='sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl' crossorigin='anonymous'></script>

<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>

<script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script>

<link href='https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i' rel='stylesheet'>

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css' integrity='sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==' crossorigin='anonymous' />

<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>

<link rel='stylesheet' href='https://unpkg.com/aos@next/dist/aos.css' />
  
<script src='https://unpkg.com/aos@next/dist/aos.js'></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.7.1/katex.min.js'></script>

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.7.1/katex.min.css'> 

<script src='https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js'></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.7.1/katex.min.js'></script>

<link rel='stylesheet' href='$quill/themes/snow.css' />

<script src='$quill/main/quill.min.js'></script>

<link rel='stylesheet' href='$rel_dirname/assets/svg-icons-animate.css'>

<script src='$rel_dirname/assets/tingle/dist/tingle.min.js' type='text/javascript'></script>

<script src='$rel_dirname/assets/lodash.min.js' type='text/javascript'></script>

<script src='$rel_dirname/assets/math-type.js' type='text/javascript'></script>

<script src='$rel_dirname/assets/math-type-extension.js' type='text/javascript'></script>

<script src='$rel_dirname/assets/moment/1.0.0/moment.min.js'></script>

<script src='$rel_dirname/assets/Chart.min.js'></script>

<link rel='stylesheet' href='$rel_dirname/assets/animate.css'>

<script src='https://www.google.com/recaptcha/api.js?render=6LeLp-cZAAAAADI1My5i20DwcBTd5zDRZhS7i_gd'></script>

";



$fonts = ['Montserrat','Open Sans','Abril+Fatface','Prompt','Special Elite','BioRhyme','Asap'];

foreach ($fonts as $font){
    $links.="<link href='https://fonts.googleapis.com/css?family=$font' rel='stylesheet'>";
}


$links = minify_html($links);

$head_script = "
<script>
    var ajax = '".$ajax."';
    var user = ".json_encode(user()).";
    var hierarchies = ".json_encode($hierarchies).";
    var hierarchy_indexes = ".json_encode($hierarchy_indexes).";
    var rel_dirname = '".$rel_dirname."';
    var image_icon = '".$image_icon."';
    var correct_mark = \"$correct_mark\";
    var wrong_mark = \"$wrong_mark\";
    var icons_rel_dir = \"$icons_rel_dir\";
    var upload_file_markup = \"".minify_html($upload_file_markup)."\";

    function addDialogItem(iterator,content){
        return \"".js_escape(add_dialog_item('"+iterator+"','"+content+"'))."\";
    };
</script>
";



$grids = [
    [1,2,1],
    [1,10,1],
    [1,5,1],
    [1,3,1],
    [1,1,1],
    [3,10,6],
    [1,1]
];

$gr = "";

foreach ($grids as $grid){
    $a="";
    $b="";

    foreach($grid as $g){
        $a.="-$g";
        $b.= " ".$g."fr ";
    }
    
    $gr .= "
		.grid$a{
			display:grid;
			grid-template-columns:$b;
		}
    ";
}



$head_style = "
<style>
	@media (min-width: 768px){ 
		$gr   
	}
</style>
";


$fonts = [
    //'Bernhard BdCn BT'=>'BernhardBoldCondensedBT.ttf',
    //'digital-7'=>'digital-7/digital-7.ttf'
];


$_fonts = "";
foreach($fonts as $font=>$path){
    $_fonts.="

    @font-face {
        font-family:'$font';
        src: url('$rel_dirname/assets/fonts/$path') format('truetype');
    }
    ";
}

$head_style = minify_css("
<style>
   $_fonts

    @font-face {
        font-family:'Bernhard BdCn BT';
        src: url('$rel_dirname/assets/fonts/BernhardBoldCondensedBT.ttf') format('truetype');
    }

    @media (min-width: 768px){ 
        $gr   
    }

    .drop-menu a:hover,  .sticky-table thead tr th, .sortable .default{
        background-color:$theme_color;
    }

    [type=\"checkbox\"].filled-in:checked+span:not(.lever):after{
        border:2px solid $theme_color;
        background-color:$theme_color;
    }


    iframe.report-card,.page-preloader,.iframe-wrapper iframe{
        background-image: url($rel_dirname/assets/images/bg-preloader.gif) !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: cover !important;
    }

    .input-group{
        border:0.5px solid $theme_color;
    }
</style>
");


$head_style .= file_get_contents($dirname."/assets/head_style.php");
$head_script .= file_get_contents($dirname."/assets/head_script.php");



if (!isset($title)){
    $title=$default_title;
}

$universal = [];

$universal['head_script'] = minify_js($head_script);

$universal['head_style'] = minify_css($head_style);

$head = "
<!DOCTYPE html>
<head itemscope='' itemtype='http://schema.org/WebPage' >
    <title>$title</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='theme-color' content='$theme_color'>
    $links
</head>

$universal[head_style]
";


$notice_modals = <<<EOF
<div id='notice' class='iframe-wrapper' style='display:none'>
    <div id='notice-content'>
        <div id='notice-header'></div>
        <div id='notice-body'></div>
    </div>
</div>

<div id='pp' class="progress">
    <div class="indeterminate"></div>
</div>
EOF;


$navbar.= $notice_modals;


$universal['stdOut'] = minify_html($head.$navbar).$universal['head_script'];
$universal = (object)$universal;

?>